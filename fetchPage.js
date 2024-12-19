const puppeteer = require('puppeteer');

(async () => {
    const url = process.argv[2];
    const browser = await puppeteer.launch({
        headless: true, // Set to false if you want to see the browser in action
    });

    const page = await browser.newPage();

    // Set user agent to mimic a real browser
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36');

    // Navigate to IMDb
    await page.goto(url, { waitUntil: 'networkidle2' });

    // Extract all video tags
    const videoTags = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('video')).map(video => video.src);
    });

    console.log(JSON.stringify(videoTags));

    await browser.close();
})();