const puppeteer = require('puppeteer');

(async () => {
    const url = process.argv[2]; // Get the URL from the command line arguments
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.goto(url, { waitUntil: 'networkidle2' }); // Wait until the network is idle
    const content = await page.content(); // Get the page content
    console.log(content); // Output the content to the console
    await browser.close();
})();