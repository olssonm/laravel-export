<?php

namespace Spatie\Export\Jobs;

use Illuminate\Contracts\Routing\UrlGenerator;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Export\Crawler\Observer;
use Spatie\Export\Destination;

class CrawlSite
{
    public function handle(UrlGenerator $urlGenerator, Destination $destination)
    {
        $entry = $urlGenerator->to('/');

        Crawler::create()
            ->setCrawlObserver(new Observer($entry, $destination))
            ->setCrawlProfile(new CrawlInternalUrls($entry))
            ->startCrawling($entry);
    }
}
