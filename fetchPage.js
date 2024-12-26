const puppeteer = require('puppeteer');

(async () => {
    const url = process.argv[2];
    const browser = await puppeteer.launch({
        headless: true, // Set to false if you want to see the browser in action
    });

    const page = await browser.newPage();

    // Set user agent to mimic a real browser
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

    let mp4Found = false;

    // Listen to network responses
    page.on('response', async (response) => {
        const url = response.url();
        // Check if the URL is an mp4 file
        if (url.includes('.mp4')) {
            console.log(url);
            mp4Found = true;
            //await browser.close();
            process.exit(0); // Exit the process after finding the first MP4 URL
        }
    });

    // Navigate to IMDb
    await page.goto(url, { waitUntil: 'networkidle2' });

    // Close the browser if no MP4 URL is found
    if (!mp4Found) {
        await browser.close();
        process.exit(1); // Exit with a non-zero code to indicate no MP4 URL was found
    }
})();