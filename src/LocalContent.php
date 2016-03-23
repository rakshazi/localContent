<?php
namespace Rakshazi;

/**
 * This library downloads content of rss/atom feeds (include images) to local files and allows
 * you to use them as offline copy of preferred sites.
 *
 * Usage:
 * <code>
 * <?php
 * require 'vendor/autoload.php';
 *
 * //array of feeds
 * $feeds = array('https://github.com/fguillot/picoFeed/commits/master.atom' => 'development');
 * //Grabber User-Agent. You can use default
 * $useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36';
 * //grabber rules
 * $rules_dir = '/storage/rules/';
 * //here will be saved images
 * $media_dir = '/storage/media/';
 * //here will be saved content from feeds in JSON db
 * $database_dir = '/storage/database/';
 * //timezone for posts, you can use default.
 * $timezone = 'UTC';
 * //Init LocalContent object
 * $lc = new \Rakshazi\LocalContent;
 * //Configure it
 * $lc
 * ->setRulesDir($rules_dir)
 * ->setMediaDir($media_dir)
 * ->setDatabaseDir($database_dir)
 * ->setTimezone($timezone);
 * //Download content!
 * $lc->download();
 * </code>
 */
class LocalContent
{
    protected $user_agent, $feeds, $rules_dir, $media_dir = null;

    /**
     * Set user agent for grabber.
     * Default: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36
     *
     * @param string $user_agent
     *
     * @return \Rakshazi\LocalContent
     */
    public function setUserAgent($user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36')
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    /**
     * Set array of rss/atom feeds to grab.
     *
     * @param array $feeds Structure: array('FEED_URL','FEED_CATEGORY')
     *
     * @return \Rakshazi\LocalContent
     */
    public function setFeeds($feeds)
    {
        $this->feeds = $array;

        return $this;
    }

    /**
     * Set rules dir for grabber.
     *
     * @link https://github.com/fguillot/picoFeed/blob/master/docs/grabber.markdown#how-to-write-a-grabber-rules-file
     *
     * @param string $dir
     *
     * @return \Rakshazi\LocalContent
     */
    public function setRulesDir($dir)
    {
        $this->rules_dir = $dir;

        return $this;
    }

    /**
     * Set media dir for downloaded images
     *
     * @param string $dir
     *
     * @return \Rakshazi\LocalContent
     */
    public function setMediaDir($dir)
    {
        $this->media_dir = $dir;

        return $this;
    }

    /**
     * Set database dir where JSON db files will be placed
     *
     * @param string $dir
     *
     * @return \Rakshazi\LocalContent
     */
    public function setDatabaseDir($dir)
    {
        define('LAZER_DATA_PATH', $dir);

        return $this;
    }

    /**
     * Set timezone.
     * Default: UTC
     *
     * @param string $timezone
     *
     * @return \Rakshazi\LocalContent
     */
    public function setTimezone($timezone = 'UTC')
    {
        date_default_timezone_set($timezone);

        return $this;
    }

    /**
     * Download and grab content from RSS/Atom feeds
     *
     * @return void
     */
    public function download()
    {
        $grabber = new \Rakshazi\LocalContent\Grabber($this->rules_dir, $this->useragent);
        $storage = new \Rakshazi\LocalContent\Storage($this->media_dir);

        foreach($this->feeds as $url => $category) {
            $feed = $grabber->get($url);
            $storage->addAll($feed->getItems(), $category);
        }
    }
}
