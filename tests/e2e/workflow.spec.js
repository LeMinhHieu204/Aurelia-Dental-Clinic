import { test, expect } from '@playwright/test';

const browserEmail = 'browser-e2e@example.invalid';

test('synthetic appointment submission appears in the admin workflow', async ({ page }, testInfo) => {
  test.skip(!process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD, 'Set E2E_ADMIN_EMAIL and E2E_ADMIN_PASSWORD for authenticated workflow coverage.');
  test.skip(testInfo.project.name !== 'desktop', 'Workflow coverage runs once on desktop Chromium.');

  await page.goto('/book-appointment');
  const requestedDate = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
  await page.getByLabel(/full name/i).fill('Browser Workflow Patient');
  await page.getByLabel(/email address/i).fill(browserEmail);
  await page.getByLabel(/phone number/i).fill('+1 555 0197');
  await page.getByLabel(/preferred location/i).selectOption({ index: 1 });
  await page.getByLabel(/preferred date/i).fill(requestedDate);
  await page.getByLabel(/treatment interest/i).selectOption({ index: 1 });
  await page.getByLabel(/privacy acknowledgement|agree to be contacted/i).check();
  await page.getByRole('button', { name: /request appointment/i }).click();

  await expect(page).toHaveURL(/book-appointment/);
  const confirmation = await page.locator('body').innerText();
  const reference = confirmation.match(/AUR-\d{4}-[A-Z0-9]{6}/)?.[0];
  expect(reference, 'confirmation reference').toBeTruthy();

  await page.goto('/admin/login');
  await page.getByLabel(/email/i).fill(process.env.E2E_ADMIN_EMAIL);
  await page.getByLabel(/password/i).fill(process.env.E2E_ADMIN_PASSWORD);
  await page.getByRole('button', { name: /sign in/i }).click();
  await page.getByRole('link', { name: 'Appointments', exact: true }).click();
  await page.getByLabel('Search').fill(browserEmail);
  await page.getByRole('button', { name: /filter/i }).click();
  await expect(page.getByRole('link', { name: reference, exact: true })).toBeVisible();
  await page.getByRole('link', { name: reference, exact: true }).click();
  await expect(page.getByRole('heading', { name: reference })).toBeVisible();
  await page.getByLabel('Status').selectOption('CONTACTED');
  await page.getByRole('button', { name: /save status/i }).click();
  await expect(page.getByText(/appointment status updated/i)).toBeVisible();
});
