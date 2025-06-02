<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PortfolioScraperTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function testExample(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel');
        });
    }

    public function testScrapePortfolio()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://sonuchoudhary.my.canva.site/portfolio')
                    ->waitFor('.portfolio-content')
                    ->assertSee('Sonu')
                    ->extractData();
        });
    }
}
