<?php
namespace App\Services;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class PortfolioScraper
{
    public function scrapePortfolio(string $url): array
    {
        $client = Client::createChromeClient(base_path('drivers/chromedriver.exe'));
        $crawler = $client->request('GET', $url);
        $name = $this->getFirstAvailableText($crawler, ['h1', 'h2', '.name']);
        $email = $this->extractEmail($crawler->html());
        $location = $this->guessLocation($crawler->html());
        $profileImage = $crawler->filter('img')->count() > 0 ? $crawler->filter('img')->first()->attr('src') : null;

        $social_links = $crawler->filter('a')->each(function ($node) {
            $href = $node->attr('href');
            if (preg_match('/linkedin|instagram|youtube|twitter/i', $href)) {
                return $href;
            }
            return null;
        });
        $social_links = array_filter($social_links);

        // Sections
        $sections = $crawler->filter('*')->each(function (Crawler $node) {
            $text = trim($node->text());
            return strlen($text) > 50 && strlen($text) < 1000 ? ['title' => null, 'content' => $text] : null;
        });
        $sections = array_filter($sections);

        // Videos
        $videos = $this->extractVideos($crawler);

        return compact('name', 'email', 'location', 'profileImage', 'social_links', 'sections', 'videos');
    }
    private function getFirstAvailableText(Crawler $crawler, array $selectors): string
    {
        foreach ($selectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $text = trim($crawler->filter($selector)->text());
                if (!empty($text)) return $text;
            }
        }
        return 'Name Not Found';
    }
    private function extractEmail($html)
    {
        preg_match("/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i", $html, $matches);
        return $matches[0] ?? null;
    }

    private function guessLocation($html)
    {
        preg_match("/(mumbai|delhi|bangalore|london|new york|tokyo)/i", $html, $matches);
        return $matches[0] ?? null;
    }

    private function extractVideos(Crawler $crawler)
    {
        $videos = [];

        if ($crawler->filter('video')->count()) {
            $videos = array_merge($videos, $crawler->filter('video')->each(fn($n) => [
                'url' => $n->attr('src'),
                'title' => $n->attr('title') ?? null,
                'description' => null,
                'thumbnail' => null
            ]));
        }

        if ($crawler->filter('iframe')->count()) {
            $iframes = $crawler->filter('iframe')->each(fn($n) => $n->attr('src'));
            foreach ($iframes as $src) {
                if (strpos($src, 'youtube.com') || strpos($src, 'vimeo.com')) {
                    $videos[] = ['url' => $src, 'title' => null, 'description' => null, 'thumbnail' => null];
                }
            }
        }

        return $videos;
    }

    /*3
    public function scrapePortfolio(string $url): array
    {
        $client = Client::createChromeClient(base_path('drivers/chromedriver.exe'));
        $crawler = $client->request('GET', $url);

        // 1. Get name
        $name = $this->getFirstAvailableText($crawler, ['h1', 'h2', '.name', '#name']);

        // 2. Get bio/sections
        $sections = $crawler->filter('*')->each(function (Crawler $node) {
            $text = trim($node->text());
            return strlen($text) > 50 && strlen($text) < 1000 ? $text : null;
        });
        $sections = array_filter($sections);

        // 3. Get video URLs
        $videos = [];

        // Direct <video> tags
        if ($crawler->filter('video')->count()) {
            $videos = array_merge($videos, $crawler->filter('video')->each(
                fn($node) => $node->attr('src')
            ));
        }

        // YouTube or Vimeo embedded iframes
        if ($crawler->filter('iframe')->count()) {
            $iframes = $crawler->filter('iframe')->each(fn($node) => $node->attr('src'));
            foreach ($iframes as $src) {
                if (strpos($src, 'youtube.com') !== false || strpos($src, 'vimeo.com') !== false) {
                    $videos[] = $src;
                }
            }
        }

        $videos = array_filter($videos); // remove nulls
        $videos = array_unique($videos); // dedupe

        return compact('name', 'sections', 'videos');
    }

    private function getFirstAvailableText(Crawler $crawler, array $selectors): string
    {
        foreach ($selectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $text = trim($crawler->filter($selector)->text());
                if (!empty($text)) return $text;
            }
        }
        return 'Name Not Found';
    }
    */
    // public function scrapePortfolio($url)
    // {
        // $client = Client::createChromeClient();
        // $client = Client::createChromeClient('C:/chromedriver/chromedriver.exe', ['C:/chromedriver/chromedriver.exe']);
        // $client = Client::createChromeClient('C:/chromedriver/chromedriver.exe'); //working
        // $client = Client::createChromeClient(base_path('drivers/chromedriver.exe'));
        // $crawler = $client->request('GET', $url);

        // Check if elements exist before scraping
        // $name = $crawler->filter('h1')->count() > 0 ? $crawler->filter('h1')->text() : 'Name Not Found';
        // $sections = $crawler->filter('.section')->count() > 0
        //             ? $crawler->filter('.section')->each(fn($node) => $node->text())
        //             : [];
        // $videos = $crawler->filter('video')->count() > 0
        //           ? $crawler->filter('video')->each(fn($node) => $node->attr('src'))
        //           : [];
        // return compact('name', 'sections', 'videos');

        /*2
        $name = $crawler->filter('h1')->count() > 0 ? $crawler->filter('h1')->text() : null;
        $email = $crawler->filter('a[href^="mailto:"]')->count() > 0
            ? str_replace('mailto:', '', $crawler->filter('a[href^="mailto:"]')->attr('href'))
            : null;
        $phone = $crawler->filter('a[href^="tel:"]')->count() > 0
            ? str_replace('tel:', '', $crawler->filter('a[href^="tel:"]')->attr('href'))
            : null;
        $location = $crawler->filter('.location')->count() > 0 ? $crawler->filter('.location')->text() : null;
        $profileImage = $crawler->filter('img')->first()->attr('src') ?? null;
        $sections = $crawler->filter('.section')->each(fn($node) => $node->text());
        $videos = $crawler->filter('video')->each(fn($node) => $node->attr('src'));
        $projects = $crawler->filter('.project')->each(function ($node) {
            return [
                'title' => $node->filter('h2')->text(),
                'description' => $node->filter('p')->text(),
                'videos' => $node->filter('video')->each(fn($v) => $v->attr('src')),
            ];
        });

        return compact('name', 'email', 'phone', 'location', 'profileImage', 'sections', 'videos', 'projects');
        */

    // }
}