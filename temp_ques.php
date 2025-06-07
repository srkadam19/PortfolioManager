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

working urls
http://portfoliomanager.local/portfolio/sonu-choudhary
https://designshack.net/articles/inspiration/personal-portfolio-websites/
