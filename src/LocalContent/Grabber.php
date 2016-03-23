<?php
namespace Rakshazi\LocalContent;

/**
 * Wrapper on picoFeed grabber
 * @see \PicoFeed\Reader\Reader
 */
class Grabber
{
    /** @var \PicoFeed\Reader\Reader */
    protected $reader = null;

    /**
     * Construct grabber
     *
     * @param string $rules_dir Grabber rules dir
     * @param string $useragent Grabber useragent
     */
    public function __construct($rules_dir, $useragent)
    {
        $picoConfig = new \PicoFeed\Config\Config;
        $picoConfig->setGrabberRulesFolder($rules_dir);
        $picoConfig->setClientUserAgent($useragent);
        $this->reader = new \PicoFeed\Reader\Reader($picoConfig);
    }

    /**
     * Enable logging for grabber
     *
     * @return void
     */
    public function enableDebug()
    {
        \PicoFeed\Logging\Logger::enable();
    }

    /**
     * Return debug messages
     *
     * @return array
     */
    public function getDebugInfo()
    {
        return \PicoFeed\Logging\Logger::getMessages();
    }

    /**
     * Parse and grab rss/atom feed url
     *
     * @param string $url Feed url
     *
     * @return \PicoFeed\Parser\Feed
     */
    public function get($url)
    {
        $resource = $this->reader->download($url);
        $parser = $this->reader->getParser($resource->getUrl(), $resource->getContent(), $resource->getEncoding());
        $parser->enableContentGrabber();
        $feed = $parser->execute();

        return $feed;
    }
}
