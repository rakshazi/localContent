<?php
namespace Rakshazi\LocalContent;

/**
 * Storage class
 */
class Storage
{
    protected $media_dir = null;

    /**
     * Constructor
     *
     * @param string $media_dir
     */
    public function __construct($media_dir)
    {
        $this->media_dir = $media_dir;
    }

    /**
     * Return object of selected table
     *
     * @param string $table Table name
     *
     * @return \Lazer\Classes\Database
     */
    public function get($table = 'posts')
    {
        if (!file_exists(LAZER_DATA_PATH . 'posts.data.json')) {
            $this->createPostsTable();
        }
        return \Lazer\Classes\Database::table($table);
    }

    /**
     * Create posts table
     *
     * @return void
     */
    public function createPostsTable()
    {
        \Lazer\Classes\Database::create('posts', array(
            'id' => 'integer',
            'hash_id' => 'string',
            'title' => 'string',
            'source' => 'string',
            'author' => 'string',
            'category' => 'string',
            'content' => 'string',
            'published' => 'integer',
            'enclosure_url' => 'string',
            'enclosure_type' => 'string',
        ));
    }

    /**
     * Load post by hash_id
     *
     * @param string $hash_id Post's hash id
     *
     * @return \Lazer\Classes\Database
     */
    public function load($hash_id)
    {
        return $this->loadAll(array('hash_id','=',$hash_id));
    }

    /**
     * Load all posts
     *
     * @param null|array $where  If you want to filter posts, just use ->where('field','=','value')
     * @param null|int   $limit  Limit of posts to load. Default: no limit (null)
     * @param int        $offset Offset. Default: 0
     *
     * @return \Lazer\Classes\Database
     */
    public function loadAll($where = null, $limit = null, $offset = 0)
    {
        $model = $this->get('posts');
        if ($limit) {
            $model->limit($limit, $offset);
        }

        if ($where) {
            $model->where($where[0], $where[1], $where[2]);
        }

        return $model->orderBy('published', 'DESC')->findAll();
    }

    /**
     * Add all feed items to database and save images
     *
     * @param array $items
     * @param string $category
     */
    public function addAll($items, $category)
    {
        usort($items, function (\PicoFeed\Parser\Item $itemA, \PicoFeed\Parser\Item $itemB) {
            return ($itemA->getDate() > $itemB->getDate()) ? -1 : 1;
        });
        foreach ($items as $item) {
            if ($this->load($item->getId())->count() == 0) {
                $images = $this->downloadImages($item->getContent(), $item->getId());
                $item = $this->replaceImagesSrc($item, $images);
                $this->add($item, $category);
            }
        }
    }

    /**
     * Add new post to database
     *
     * @param \PicoFeed\Parser\Item $item     Feed item
     * @param string                $category Post's category,eg: development
     */
    public function add(\PicoFeed\Parser\Item $item, $category)
    {
        $row = $this->get('posts');
        $row->hash_id = $item->getId();
        $row->published = strtotime($item->getDate()->format("Y-m-d H:i:s"));
        $row->title = $item->getTitle();
        $row->source = $item->getUrl();
        $row->author = $item->getAuthor();
        $row->category = $category;
        $row->content = $item->getContent();
        $row->enclosure_url = $item->getEnclosureUrl();
        $row->enclosure_type = $item->getEnclosureType();
        $row->save();
    }

    /**
     * Get all images urls from item content and save them to local media folder
     *
     * @param string $html      Feed item html content
     * @param string $hash_id   Feed item id hash
     *
     * @return array Array of image urls. Structure: array(0 => array('remote' => 'IMAGE_REMOTE_URL', 'local' => 'IMAGE_LOCAL_PATH'))
     */
    protected function downloadImages($html, $hash_id)
    {
        $images = array();
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query("//img");
        foreach ($nodes as $node) {
            if ($node && strpos($node->attributes->getNamedItem('src')->nodeValue, 'mc.yandex.ru') === false) {
                $images[] = array(
                    'remote' => $node->attributes->getNamedItem('src')->nodeValue,
                    'local' => $this->saveImage($node->attributes->getNamedItem('src')->nodeValue, $hash_id),
                );
            }
        }

        return $images;
    }

    /**
     * Save image to media folder and return relative url to it
     *
     * @param string $url     Image URL
     * @param string $hash_id feed item id hash
     *
     * @return string
     */
    protected function saveImage($url, $hash_id)
    {
        $path = $this->media_dir . $hash_id . '-' . uniqid() . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        copy($url, $path);

        return $path;
    }

    /**
     * Replace images in feed content to local urls
     *
     * @param \PicoFeed\Parser\Item $item   Feed item
     * @param array                 $images images array
     *
     * @return \PicoFeed\Parser\Item
     */
    protected function replaceImagesSrc(\PicoFeed\Parser\Item $item, $images = array())
    {
        foreach ($images as $img) {
            $item->content = str_replace($img['remote'], $img['local'], $item->content);
        }

        return $item;
    }
}
