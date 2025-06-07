<?php
namespace App\Services;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class PortfolioScraper
{
    /*public function scrapePortfolio(string $url): array
    {
        $client = Client::createChromeClient(
        base_path('drivers/chromedriver.exe'),
        null,
        ['--headless', '--disable-gpu', '--window-size=1920,1080']
        );

        $crawler = $client->request('GET', $url);

        // Wait for JS to render (important for Canva)
        // $client->waitFor('.theme'); // Or use something more specific
        sleep(50);

        // sleep(3); // <-- Ensure content is fully loaded (Canva loads slowly)

        $html = $crawler->html(); // Get fully rendered HTML

        return $this->extractFromRenderedHTML($html);
    }

    private function extractFromRenderedHTML(string $html): array
    {
        $name = $this->matchText('/sONU CHOUDHARY\\nVIDEO EDITOR\\n/i', $html, 'Sonu Choudhary');
        $bio = $this->matchText('/I specialize in (.+?)5 million organic views/i', $html);
        $location = 'India'; // Hardcoded or extracted from elsewhere

        preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}/i', $html, $emailMatches);
        $email = $emailMatches[0] ?? null;

        // Extract first image
        preg_match('/<img[^>]+src=[\"\\\']?([^\"\\\'>]+)[\"\\\']?/i', $html, $imageMatches);
        $profileImage = $imageMatches[1] ?? null;

        $videos = $this->extractVideosFromHTML($html);
        $skills = $this->extractSkillsFromHTML($html);
        $sections = $this->extractSectionsFromHTML($html);
        $social_links = $this->extractSocialLinks($html);

        return compact('name', 'email', 'location', 'profileImage', 'social_links', 'sections', 'videos', 'skills');
    }

    private function extractSkillsFromHTML(string $html): array
    {
        $skills = ['video editing', 'youtube', 'premiere', 'final cut', 'after effects', 'motion graphics'];
        preg_match_all('/\\b(' . implode('|', array_map('preg_quote', $skills)) . ')\\b/i', $html, $matches);
        return array_unique($matches[0]);
    }

    private function extractSocialLinks(string $html): array
    {
        preg_match_all('/https?:\\/\\/(www\\.)?(linkedin|instagram|youtube|twitter)\\.[^\\s"\'<>]+/', $html, $matches);
        return array_unique($matches[0]);
    }

    private function extractSectionsFromHTML(string $html): array
    {
        preg_match_all('/>\\s*([A-Z][^<]{50,300})\\s*</', $html, $matches);
        return array_map(fn($s) => ['title' => null, 'content' => trim($s)], $matches[1]);
    }

    private function matchText(string $pattern, string $html, string $default = null): ?string
    {
        preg_match($pattern, $html, $matches);
        return $matches[0] ?? $default;
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

    private function extractSections(Crawler $crawler): array
    {
        $html = $crawler->html();
        preg_match_all('/[A-Z][^.?!]{50,500}[.?!]/', strip_tags($html), $matches);
        return array_map(fn($s) => ['title' => null, 'content' => $s], $matches[0]);
    }
    private function extractVideosFromHTML($html): array
    {
        preg_match_all('/https?:\\/\\/(?:www\\.)?(youtube\\.com|youtu\\.be)\\/[a-zA-Z0-9\\-?=&#._]+/', $html, $matches);
        $urls = array_unique($matches[0]);

        return array_map(fn($url) => [
            'url' => $url,
            'title' => null,
            'description' => null,
            'thumbnail' => null,
        ], $urls);
    }

    private function extractVideos(Crawler $crawler): array
    {
        $videos = [];

        // Native video tags
        if ($crawler->filter('video')->count()) {
            $videos = array_merge($videos, $crawler->filter('video')->each(function ($n) {
                return [
                    'url' => $n->attr('src'),
                    'title' => $n->attr('title') ?? 'Untitled Video',
                    'description' => null,
                    'thumbnail' => $n->attr('poster') ?? null,
                ];
            }));
        }

        // Iframes (YouTube, Vimeo)
        if ($crawler->filter('iframe')->count()) {
            foreach ($crawler->filter('iframe') as $node) {
                $src = $node->getAttribute('src');
                if (preg_match('/(youtube|vimeo)/i', $src)) {
                    $videos[] = [
                        'url' => $src,
                        'title' => 'Embedded Video',
                        'description' => null,
                        'thumbnail' => null
                    ];
                }
            }
        }

        return $videos;
    }


    private function extractSkills(Crawler $crawler): array
    {
        $html = $crawler->html(); // Convert Crawler to string
        preg_match_all('/\b(video editing|youtube|final cut|premiere|after effects|motion graphics)\b/i', $html, $matches);
        return array_unique(array_map('trim', $matches[0]));
    }
    */


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