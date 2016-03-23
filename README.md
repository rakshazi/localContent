# LocalContent

This library downloads content of rss/atom feeds (include images) to local files and allows
you to use them as offline copy of preferred sites.

# Installation

```
composer require rakshazi/local-content:dev-master
```

# Usage

```php
<?php
require 'vendor/autoload.php';

/**
 * Array of feeds.
 * Structure: array('FEED_URL' => 'FEED_CATEGORY');
 */
$feeds = array('https://github.com/fguillot/picoFeed/commits/master.atom' => 'development');
//Grabber User-Agent. You can use default
$useragent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36';
/**
 * Grabber rules.
 * @link https://github.com/fguillot/picoFeed/blob/master/docs/grabber.markdown#how-to-write-a-grabber-rules-file
 */
$rules_dir = '/storage/rules/';
//here will be saved images
$media_dir = '/storage/media/';
//here will be saved content from feeds in JSON db
$database_dir = '/storage/database/';
//timezone for posts, you can use default.
$timezone = 'UTC';
//Init LocalContent object
$lc = new \Rakshazi\LocalContent;
//Configure it
$lc
->setRulesDir($rules_dir)
->setMediaDir($media_dir)
->setDatabaseDir($database_dir)
->setTimezone($timezone);
//Download content!
$lc->download();
```
