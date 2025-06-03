<?php
namespace App\Services;

use Symfony\Component\Panther\Client;

class PortfolioScraper
{
    public function scrapePortfolio($url)
    {
        // $client = Client::createChromeClient();
        // $client = Client::createChromeClient('C:/chromedriver/chromedriver.exe', ['C:/chromedriver/chromedriver.exe']);
        // $client = Client::createChromeClient('C:/chromedriver/chromedriver.exe'); //working
        $client = Client::createChromeClient(base_path('drivers/chromedriver.exe'));
        $crawler = $client->request('GET', $url);

        // Check if elements exist before scraping
        $name = $crawler->filter('h1')->count() > 0 ? $crawler->filter('h1')->text() : 'Name Not Found';
        $sections = $crawler->filter('.section')->count() > 0
                    ? $crawler->filter('.section')->each(fn($node) => $node->text())
                    : [];
        $videos = $crawler->filter('video')->count() > 0
                  ? $crawler->filter('video')->each(fn($node) => $node->attr('src'))
                  : [];
        return compact('name', 'sections', 'videos');

    }
}