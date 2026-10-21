import { test, expect } from '@playwright/test';

test.describe('Authentication & Navigation Guardrails (E2E)', () => {
  test('Landing page serves strict anti-cache headers and public navigation', async ({ page }) => {
    const response = await page.goto('/');
    expect(response?.status()).toBe(200);

    // Verify system-wide anti-cache headers
    const cacheControl = response?.headers()['cache-control'] || '';
    expect(cacheControl).toContain('no-cache');
    expect(cacheControl).toContain('no-store');

    // Verify branding element
    await expect(page.locator('body')).toContainText('Brgy. Banlic');
  });

  test('Admin login, back-button guardrail, and secure sign out', async ({ page }) => {
    // 1. Visit Login page
    await page.goto('/login');
    await expect(page.locator('h2')).toContainText('Welcome back');

    // 2. Perform Admin Authentication
    await page.fill('#email', 'admin@barangay.local');
    await page.fill('#password', 'Admin@12345');
    await page.click('button[type="submit"]');

    // 3. Verify redirected to Admin Dashboard
    await page.waitForURL('**/admin/dashboard');
    await expect(page.locator('h1')).toContainText('Dashboard');

    // 4. Back-button trap / bfcache test
    await page.goBack();
    await page.waitForTimeout(400);
    expect(page.url()).not.toContain('/login');
    await expect(page.locator('h1')).toContainText('Dashboard');

    // 5. Open user dropdown in topbar
    const userDropdownBtn = page.locator('.topbar .user-btn');
    await userDropdownBtn.click();

    // 6. Click Logout button inside topbar dropdown
    const logoutBtn = page.locator('.topbar form[action*="logout"] button[type="submit"]');
    await logoutBtn.click();

    // 7. Click confirm on confirmation modal
    const modalOk = page.locator('#confirmModal [data-modal-ok]');
    await expect(modalOk).toBeVisible({ timeout: 2000 });
    await modalOk.click();

    // 8. Verify redirected to public landing page and authenticated session is terminated
    await page.waitForURL('http://127.0.0.1:8000/');
    expect(page.url()).not.toContain('/admin/dashboard');
    await expect(page.locator('#pubnav')).toContainText('Login');
  });
});
