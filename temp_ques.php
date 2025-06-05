### Part 1 — Portfolio link ingestion and structured profile API

- Accept and process an external portfolio link:

    You may use this [example portfolio link](https://sonuchoudhary.my.canva.site/portfolio) from Sonu for your demo.

    > Bonus: If you can make your system flexible enough to support any portfolio layout (not just this one), that's a big plus. Here is [another example](https://dellinzhang.com/video-edit) of a talent’s personal website.
    >
- Scrape the site to extract structured information (e.g., names, sections, videos).
- Store the extracted data in a SQL database
- Create 3 API endpoints for frontend consumption:
    - Retrieve the talent’s profile via a unique username and allow the frontend to display the profile in a structured format under two sections:
        - **Basic Info**
        - **Employers/Clients**
            - Under each employer/client, show related **videos** from the portfolio.
    - Edit each section of the talent’s profile
    - Delete the talent’s profile
- **NOTE**: The API endpoints are NOT required to have user authentication, as building a login function is not part of this assignment. However, you are encouraged to implement basic security measures such as API key protection and follow good API practices like rate limiting.

For the UI direction, you may reference Rikard’s profile layout as an example of how we currently display talent portfolios on Roster [here](https://app.joinroster.co/Rikard). You’re welcome to take inspiration from it, but you’re not required to follow it exactly — feel free to improve or rethink the structure based on your approach.

using laravel i have created a project and have used laravel symfony panther package
below is my table schema for above question
Schema::create('talents', function (Blueprint $table) {
    $table->id();
    $table->string('username')->unique();
    $table->string('name')->nullable();
    $table->text('bio')->nullable();
    $table->timestamps();
});

Schema::create('employers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('talent_id')->constrained('talents')->onDelete('cascade');
    $table->timestamps();
});

Schema::create('videos', function (Blueprint $table) {
    $table->id();
    $table->string('url');
    $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
    $table->timestamps();
});

below is my controller function for web scrapping using url
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

and below is my porfolioScrapper service
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


I want you to give me code for storing more data into tables, you can introduce new table or can add new columns in existing table and need similar changes in code as well.
give me step by step all details


### **Part 2 — Fast semantic search for finding the right talent**

**Background**

Roster aims to help employers find the best creative talent, fast. We want to go beyond keyword filters and match creators based on *semantic meaning* — e.g., if an employer searches for "a video editor with 5 years of experience editing YouTube videos for doctors", we want our system to surface [Sonu](https://sonuchoudhary.my.canva.site/portfolio) because his profile description implies that fit.

**Your Task**

Design and implement a basic version of a **semantic search system** for thousands of talent profiles that can:

1. **Extract semantic embeddings** from those profiles using an LLM or embedding model.
2. **Enable fast (almost instant) matching** between a user’s query (natural language) and relevant talent.

**NOTE**: Part 2 is intentionally left open-ended to encourage you to propose your own solutions.


working urls
http://portfoliomanager.local/portfolio/sonu-choudhary
https://designshack.net/articles/inspiration/personal-portfolio-websites/
https://romain-reglade-mnpu.format.com
