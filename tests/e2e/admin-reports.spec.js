import { test, expect } from '@playwright/test';

test.describe('Admin Module & Reports Export (E2E)', () => {
  test('Admin can access Reports dashboard and verify 3 export actions', async ({ page }) => {
    // 1. Admin Login
    await page.goto('/login');
    await page.fill('#email', 'admin@barangay.local');
    await page.fill('#password', 'Admin@12345');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/dashboard');

    // 2. Navigate to Reports Dashboard
    await page.goto('/admin/reports');
    await expect(page.locator('h1')).toContainText('Reports & Analytics');

    // 3. Verify the 3 dedicated export actions conform to project standards
    const pdfExportBtn = page.locator('a:has-text("Export PDF")');
    await expect(pdfExportBtn).toBeVisible();
    const pdfHref = await pdfExportBtn.getAttribute('href');
    expect(pdfHref).toContain('mode=pdf');

    const printBtn = page.locator('a:has-text("Print")');
    await expect(printBtn).toBeVisible();
    const printHref = await printBtn.getAttribute('href');
    expect(printHref).toContain('autoprint=1');

    const csvExportBtn = page.locator('a:has-text("Export CSV")');
    await expect(csvExportBtn).toBeVisible();
    const csvHref = await csvExportBtn.getAttribute('href');
    expect(csvHref).toContain('/admin/reports/export');

    // 4. Verify summary analytics cards render
    await expect(page.locator('.stats')).toBeVisible();
    await expect(page.locator('.stat').first()).toBeVisible();
  });
});
