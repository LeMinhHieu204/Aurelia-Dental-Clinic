import { test, expect } from '@playwright/test';

const allViewports = [
  [320, 720],
  [375, 812],
  [390, 844],
  [768, 1024],
  [1024, 768],
  [1440, 900],
  [1920, 1080],
];

const pages = [
  ['home', '/'],
  ['treatments', '/treatments'],
  ['doctors', '/doctors'],
  ['reviews', '/reviews'],
  ['contact', '/contact'],
  ['booking', '/book-appointment'],
];

test('captures responsive public surfaces and checks for horizontal overflow', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop', 'Visual capture runs once using the explicit viewport matrix.');
  test.setTimeout(120_000);

  for (const [width, height] of allViewports) {
    await page.setViewportSize({ width, height });
    const capturePages = width === 1440 || width === 390 ? pages : pages.slice(0, 1);

    for (const [name, path] of capturePages) {
      await page.goto(path);
      await page.waitForLoadState('networkidle').catch(() => {});
      await page.evaluate(() => document.querySelectorAll('img[loading="lazy"]').forEach((image) => image.loading = 'eager'));
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await page.waitForTimeout(300);
      await page.evaluate(() => window.scrollTo(0, 0));
      const brokenImages = await page.evaluate(() => Array.from(document.images).filter((image) => !image.complete || image.naturalWidth === 0).map((image) => image.alt || image.src));
      expect(brokenImages, name + ' at ' + width + 'px has broken images').toEqual([]);
      const dimensions = await page.evaluate(() => ({
        scrollWidth: document.documentElement.scrollWidth,
        viewport: window.innerWidth,
        offending: Array.from(document.querySelectorAll('*')).map((element) => {
          const rect = element.getBoundingClientRect();
          return { tag: element.tagName, id: element.id, className: String(element.className), right: Math.round(rect.right), width: Math.round(rect.width) };
        }).filter((element) => element.right > window.innerWidth + 1).sort((a, b) => b.right - a.right).slice(0, 5),
      }));
      expect(dimensions.scrollWidth, name + ' at ' + width + 'px has horizontal overflow: ' + JSON.stringify(dimensions.offending)).toBeLessThanOrEqual(dimensions.viewport + 1);
      await page.screenshot({
        path: 'docs/screenshots/phase-3/' + name + '-' + width + 'x' + height + '.png',
        fullPage: true,
      });
    }
  }
});
