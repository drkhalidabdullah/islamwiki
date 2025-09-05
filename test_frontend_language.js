const puppeteer = require('puppeteer');

async function testLanguageSwitching() {
    console.log('=== TESTING FRONTEND LANGUAGE SWITCHING ===');
    
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    
    try {
        // Navigate to the application
        console.log('1. Navigating to application...');
        await page.goto('http://localhost', { waitUntil: 'networkidle0' });
        
        // Wait for React to load
        await page.waitForSelector('header', { timeout: 10000 });
        console.log('2. Application loaded successfully');
        
        // Check initial language
        console.log('3. Checking initial language...');
        const initialLanguage = await page.evaluate(() => {
            return document.documentElement.lang || 'unknown';
        });
        console.log('Initial language:', initialLanguage);
        
        // Look for language switcher
        console.log('4. Looking for language switcher...');
        const languageSwitcher = await page.$('[data-testid="language-switcher"], .language-switcher, button[class*="language"]');
        if (languageSwitcher) {
            console.log('Language switcher found');
        } else {
            console.log('Language switcher not found - checking all buttons...');
            const buttons = await page.$$('button');
            console.log('Found', buttons.length, 'buttons');
        }
        
        // Check for any language-related elements
        const languageElements = await page.evaluate(() => {
            const elements = [];
            document.querySelectorAll('*').forEach(el => {
                if (el.textContent && (el.textContent.includes('English') || el.textContent.includes('العربية') || el.textContent.includes('Français'))) {
                    elements.push({
                        tag: el.tagName,
                        text: el.textContent.trim(),
                        className: el.className
                    });
                }
            });
            return elements;
        });
        
        console.log('5. Language-related elements found:', languageElements.length);
        languageElements.forEach(el => {
            console.log('  -', el.tag, ':', el.text.substring(0, 50));
        });
        
    } catch (error) {
        console.error('Error during testing:', error.message);
    } finally {
        await browser.close();
    }
}

testLanguageSwitching().catch(console.error);
