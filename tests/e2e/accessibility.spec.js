import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const publicPages = [
  ['home', '/'],
  ['treatments', '/treatments'],
  ['doctors', '/doctors'],
  ['reviews', '/reviews'],
  ['contact', '/contact'],
  ['booking', '/book-appointment'],
];

test('public pages have no automated WCAG 2 A/AA violations', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop', 'Accessibility audit runs once on desktop Chromium.');
  test.setTimeout(120_000);

  for (const [name, path] of publicPages) {
    await page.goto(path);
    await page.waitForLoadState('networkidle').catch(() => {});
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
      .analyze();
    expect(results.violations, `${name} accessibility violations`).toEqual([]);
  }
});

test('keyboard focus advances through the public booking form', async ({ page }, testInfo) => {
  test.skip(testInfo.project.name !== 'desktop', 'Keyboard audit runs once on desktop Chromium.');
  await page.goto('/book-appointment');
  await page.getByLabel(/full name/i).focus();
  await expect(page.getByLabel(/full name/i)).toBeFocused();
  await page.keyboard.press('Tab');
  await expect(page.getByLabel(/email address/i)).toBeFocused();
  await page.keyboard.press('Tab');
  await expect(page.getByLabel(/phone number/i)).toBeFocused();
});
