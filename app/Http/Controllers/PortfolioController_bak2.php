<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Talent;
use App\Models\Employer;
use App\Models\Project;
use App\Models\Sections;
use App\Models\Skills;
use App\Models\Video;
use App\Services\PortfolioScraper;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PortfolioController extends Controller
{
    public function scrapeWithPuppeteer(Request $request)
    {
        $url = $request->input('url');

        $process = new Process([
            'node',
            base_path('drivers/puppeteer-scraper.cjs'),
            $url
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        info($output);
        dd($output);
        // Example parsing
        $name = $this->matchText('/Sonu Choudhary/i', $output, 'Unknown');
        $email = $this->matchText('/[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}/i', $output);
        $videos = $this->extractVideoLinks($output);
        $skills = $this->extractSkillsFromText($output);

        return response()->json(compact('name', 'email', 'videos', 'skills'));
    }

    public function scrapeByUsername($username)
    {
        $url = "https://sonuchoudhary.my.canva.site/portfolio/" . $username;
        return $this->scrapeWithPuppeteer(new Request(['url' => $url]));
    }

    public function matchText(string $pattern, string $text, $default = null): ?string
    {
        preg_match($pattern, $text, $matches);
        return $matches[0] ?? $default;
    }

    public function extractVideoLinks(string $text): array
    {
        preg_match_all('/https?:\/\/(?:www\.)?(youtube\.com|youtu\.be)\/[a-zA-Z0-9\-?=&#._]+/', $text, $matches);
        return array_map(fn($url) => [
            'url' => $url,
            'title' => null,
            'description' => null,
            'thumbnail' => null
        ], array_unique($matches[0]));
    }

    public function extractSkillsFromText(string $text): array
    {
        $skills = ['video editing', 'youtube', 'final cut pro', 'premiere', 'after effects', 'motion graphics', 'color grading'];
        preg_match_all('/\b(' . implode('|', array_map('preg_quote', $skills)) . ')\b/i', $text, $matches);
        return array_unique(array_map('ucwords', $matches[0]));
    }

    public function extractParagraphs(string $text): array
    {
        $lines = explode("\n", $text);
        $paragraphs = array_filter(array_map('trim', $lines), fn($line) => strlen($line) > 40);
        return array_map(fn($p) => ['title' => null, 'content' => $p], $paragraphs);
    }

    public function extractSocialLinks(string $text): array
    {
        preg_match_all('/https?:\/\/(linkedin|instagram|twitter|facebook)\.com\/[\w\-._]+/i', $text, $matches);
        return array_unique($matches[0]);
    }
}