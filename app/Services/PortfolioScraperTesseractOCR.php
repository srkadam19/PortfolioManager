<?php
namespace App\Services;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PortfolioScraperTesseractOCR
{
    public function scrapePortfolio($url)
    {
        // Launch headless browser with Panther
        $client = Client::createChromeClient(
            base_path('drivers/chromedriver.exe'),
            null,
            ['--window-size=1920,1080']
        );

        $crawler = $client->request('GET', $url);
        $client->executeScript('window.scrollTo(0, document.body.scrollHeight);');
        sleep(50); // Wait for full rendering

        // Take screenshot
        $screenshotPath = storage_path('app/public/screenshot.png');
        // $client->takeScreenshot($screenshotPath);
        $client->executeScript("document.body.style.zoom = '1'");
        file_put_contents($screenshotPath, $client->takeScreenshot());

        // OCR using Tesseract
        $ocrText = (new TesseractOCR($screenshotPath))
            ->executable('C:\\Program Files\\Tesseract-OCR\\tesseract.exe')
            ->lang('eng')
            ->run();
        logger()->info('OCR TEXT: ' . $ocrText);
        // Extract structured data from OCR text
        $name = $this->matchText('/Sonu Choudhary/i', $ocrText, 'Unknown');
        $email = $this->matchText('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $ocrText);
        $location = 'India'; // Static or parse from $ocrText
        $profileImage = null; // Not extractable via OCR

        $videos = $this->extractVideoLinks($ocrText);
        $skills = $this->extractSkillsFromText($ocrText);
        $sections = $this->extractParagraphs($ocrText);
        $social_links = $this->extractSocialLinks($ocrText);

        // Now save to database as you do in your controller...

        return response()->json(compact(
            'name', 'email', 'location', 'profileImage', 'social_links', 'sections', 'videos', 'skills'
        ), 200);
    }

    # === 4. ADD TEXT MATCHING HELPERS IN CONTROLLER === #

    private function matchText(string $pattern, string $text, $default = null): ?string
    {
        preg_match($pattern, $text, $matches);
        return $matches[0] ?? $default;
    }

    private function extractVideoLinks(string $text): array
    {
        preg_match_all('/https?:\/\/(?:www\.)?(youtube\.com|youtu\.be)\/[a-zA-Z0-9\-?=&#._]+/', $text, $matches);
        return array_map(fn($url) => [
            'url' => $url,
            'title' => null,
            'description' => null,
            'thumbnail' => null
        ], array_unique($matches[0]));
    }

    private function extractSkillsFromText(string $text): array
    {
        $keywords = ['video editing', 'youtube', 'final cut', 'premiere', 'after effects'];
        preg_match_all('/\b(' . implode('|', array_map('preg_quote', $keywords)) . ')\b/i', $text, $matches);
        return array_unique($matches[0]);
    }

    private function extractParagraphs(string $text): array
    {
        $lines = explode("\n", $text);
        $paragraphs = array_filter(array_map('trim', $lines), fn($line) => strlen($line) > 50);
        return array_map(fn($p) => ['title' => null, 'content' => $p], $paragraphs);
    }

    private function extractSocialLinks(string $text): array
    {
        preg_match_all('/https?:\/\/(linkedin|instagram|twitter|facebook)\.com\/[\w\-._]+/i', $text, $matches);
        return array_unique($matches[0]);
    }
}