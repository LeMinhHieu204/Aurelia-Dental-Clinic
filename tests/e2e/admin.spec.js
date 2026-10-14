import { test, expect } from '@playwright/test';

test('admin login and operational navigation work with an isolated browser fixture', async ({ page }, testInfo) => {
  test.skip(!process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD, 'Set E2E_ADMIN_EMAIL and E2E_ADMIN_PASSWORD for authenticated browser coverage.');
  test.skip(testInfo.project.name !== 'desktop', 'Authenticated coverage runs once on desktop.');

  await page.goto('/admin/login');
  await page.getByLabel(/email/i).fill(process.env.E2E_ADMIN_EMAIL);
  await page.getByLabel(/password/i).fill(process.env.E2E_ADMIN_PASSWORD);
  await page.getByRole('button', { name: /sign in/i }).click();
  await expect(page).toHaveURL(/\/admin$/);
  await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible();
  await page.screenshot({ path: 'docs/screenshots/phase-3/admin-dashboard-1440x900.png', fullPage: true });

  await page.getByRole('link', { name: 'Calendar', exact: true }).click();
  await expect(page).toHaveURL(/\/admin\/calendar/);
  await expect(page.getByRole('heading', { name: /calendar/i })).toBeVisible();
  await page.screenshot({ path: 'docs/screenshots/phase-3/admin-calendar-1440x900.png', fullPage: true });

  await page.getByRole('link', { name: 'Content', exact: true }).click();
  await expect(page).toHaveURL(/\/admin\/content/);
  await expect(page.getByRole('heading', { name: /website content/i })).toBeVisible();
  await page.screenshot({ path: 'docs/screenshots/phase-3/admin-content-1440x900.png', fullPage: true });
});
