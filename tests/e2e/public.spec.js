import { test, expect } from '@playwright/test';

test.describe('Aurelia public website', () => {
  test('homepage and primary navigation render', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Aurelia Dental Clinic/);
    await expect(page.getByRole('link', { name: /Book Appointment/i }).first()).toBeVisible();
    await page.getByRole('link', { name: 'Treatments', exact: true }).first().click();
    await expect(page).toHaveURL(/\/treatments$/);
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
  });

  test('mobile navigation opens and closes', async ({ page }, testInfo) => {
    test.skip(testInfo.project.name !== 'mobile', 'The drawer interaction is covered by the mobile project.');
    await page.goto('/');
    await page.getByRole('button', { name: /open menu/i }).click();
    await expect(page.getByRole('navigation', { name: 'Primary navigation' })).toHaveClass(/open/);
    await page.getByRole('button', { name: /close menu/i }).click();
    await expect(page.getByRole('navigation', { name: 'Primary navigation' })).not.toHaveClass(/open/);
  });

  test('contact and booking forms expose required controls', async ({ page }) => {
    await page.goto('/contact');
    await expect(page.getByRole('heading', { name: /conversation/i })).toBeVisible();
    await expect(page.locator('input[name="website"]')).toHaveCount(1);
    await page.goto('/book-appointment');
    await expect(page.locator('input[name="privacy_acknowledgement"]')).toHaveCount(1);
    await expect(page.locator('input[name="full_name"]')).toBeVisible();
  });

  test('admin area rejects unauthenticated access', async ({ page }) => {
    await page.goto('/admin');
    await expect(page).toHaveURL(/\/admin\/login$/);
  });
});
