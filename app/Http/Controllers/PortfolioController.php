<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Talent;
use App\Models\Employer;
use App\Models\Video;
use App\Services\PortfolioScraper;

class PortfolioController extends Controller
{
    public function store(Request $request)
    {
        $scraper = new PortfolioScraper();
        $data = $scraper->scrapePortfolio($request->url);

        $talent = Talent::create([
            'username' => $request->username,
            'name' => $data['name'],
            'bio' => implode(", ", $data['sections'])
        ]);

        foreach ($data['videos'] as $videoUrl) {
            info($talent->id);
            $employer = Employer::firstOrCreate(['name' => 'Unknown', 'talent_id' => $talent->id]);
            Video::create(['url' => $videoUrl, 'employer_id' => $employer->id]);
        }

        return response()->json($talent, 201);
    }

    public function show($username)
    {
        $talent = Talent::where('username', $username)->with('employers.videos')->first();
        return response()->json($talent);
    }

    public function update(Request $request, $username)
    {
        $talent = Talent::where('username', $username)->first();
        $talent->update($request->all());
        return response()->json($talent);
    }

    public function destroy($username)
    {
        Talent::where('username', $username)->delete();
        return response()->json(['message' => 'Profile deleted']);
    }
}