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
use App\Services\PortfolioScraperTesseractOCR;

class PortfolioController extends Controller
{
    public function store(Request $request)
    {
        $scraper = new PortfolioScraperTesseractOCR();
        $data = $scraper->scrapePortfolio($request->url);
        info($data);
        dd($data);
        $scraper = new PortfolioScraper();
        $data = $scraper->scrapePortfolio($request->url);
        info($data);
        $talent = Talent::create([
            'username' => $request->username,
            'name' => $data['name'],
            'email' => $data['email'],
            'location' => $data['location'],
            'profile_image' => $data['profileImage'],
            'social_links' => json_encode($data['social_links']),
            'bio' => implode(", ", array_column($data['sections'], 'content')),
        ]);

        foreach ($data['sections'] as $section) {
            Sections::create([
                'talent_id' => $talent->id,
                'title' => $section['title'],
                'content' => $section['content']
            ]);
        }

        foreach ($data['skills'] as $skill) {
            Skills::create([
                'talent_id' => $talent->id,
                'name' => $skill
            ]);
        }

        $employer = Employer::firstOrCreate([
            'name' => 'Unknown',
            'talent_id' => $talent->id
        ]);

        foreach ($data['videos'] as $video) {
            Video::create([
                'url' => $video['url'],
                'title' => $video['title'],
                'description' => $video['description'],
                'thumbnail' => $video['thumbnail'],
                'employer_id' => $employer->id
            ]);
        }

        return response()->json(['talent' => $talent], 201);
        // $scraper = new PortfolioScraper();
        // $data = $scraper->scrapePortfolio($request->url);
        // info($data);
        // $talent = Talent::create([
        //     'username' => $request->username,
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'location' => $data['location'],
        //     'profile_image' => $data['profileImage'],
        //     'social_links' => json_encode($data['social_links']),
        //     'bio' => implode(", ", array_column($data['sections'], 'content'))
        // ]);


        // $employer = Employer::firstOrCreate([
        //     'name' => 'Unknown',
        //     'talent_id' => $talent->id
        // ]);
        // foreach ($data['videos'] as $video) {
        //     Video::create([
        //         'url' => $video['url'],
        //         'title' => $video['title'],
        //         'description' => $video['description'],
        //         'thumbnail' => $video['thumbnail'],
        //         'employer_id' => $employer->id
        //     ]);
        // }

        // return response()->json(['talent' => $talent], 201);


        // $scraper = new PortfolioScraper();
        // $data = $scraper->scrapePortfolio($request->url);

        // $talent = Talent::create([
        //     'username' => $request->username,
        //     'name' => $data['name'],
        //     'bio' => implode(", ", $data['sections'])
        // ]);

        // foreach ($data['videos'] as $videoUrl) {
        //     info($talent->id);
        //     $employer = Employer::firstOrCreate(['name' => 'Unknown', 'talent_id' => $talent->id]);
        //     Video::create(['url' => $videoUrl, 'employer_id' => $employer->id]);
        // }


        // $talent = Talent::create([
        //     'username' => $request->username,
        //     'name' => $data['name'],
        //     'bio' => implode(", ", $data['sections']),
        // ]);

        // foreach ($data['videos'] as $videoUrl) {
        //     $employer = Employer::firstOrCreate(['name' => 'Unknown', 'talent_id' => $talent->id]);
        //     Video::create(['url' => $videoUrl, 'employer_id' => $employer->id]);
        // }


        // $talent = Talent::create([
        //     'username' => $request->username,
        //     'name' => $data['name'],
        //     'email' => $data['email'] ?? null,
        //     'phone' => $data['phone'] ?? null,
        //     'location' => $data['location'] ?? null,
        //     'profile_image_url' => $data['profileImage'] ?? null,
        //     'bio' => implode(', ', $data['sections']),
        // ]);
        // info('projects');
        // info($data['projects']);
        // foreach ($data['projects'] as $projectData) {
        //     $employer = Employer::firstOrCreate([
        //         'name' => 'Unknown',
        //         'talent_id' => $talent->id
        //     ]);

        //     $project = Project::create([
        //         'title' => $projectData['title'],
        //         'description' => $projectData['description'],
        //         'employer_id' => $employer->id,
        //     ]);

        //     foreach ($projectData['videos'] as $videoUrl) {
        //         Video::create([
        //             'url' => $videoUrl,
        //             'employer_id' => $employer->id,
        //             'project_id' => $project->id,
        //         ]);
        //     }
        // }
        // return response()->json("success", 201);
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