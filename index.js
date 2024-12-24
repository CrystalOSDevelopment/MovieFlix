const puppeteer = require('puppeteer');

// Get the URL passed as a command-line argument
const urlToVisit = process.argv[2];

if (!urlToVisit) {
    console.log('Error: No URL provided');
    process.exit(1);
}

(async () => {
    // Launch the browser
    const browser = await puppeteer.launch();
    const page = await browser.newPage();

    // Listen to network responses
    page.on('response', async (response) => {
        const url = response.url();
        // Check if the URL is an m3u8 file
        if (url.includes('master.m3u8') || url.includes('indavideo.hu') || url.includes('videa.hu')) {
            console.log(url);
        }
    });

    // Navigate to the provided URL
    await page.goto(urlToVisit);

    // Close the browser
    await browser.close();
})();