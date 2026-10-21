import { test, expect } from '@playwright/test';

test.describe('Resident Module & Complaint Submission (E2E)', () => {
  test('Resident can log in, interact with zero-delay datetime picker, and file a complaint', async ({ page }) => {
    // 1. Login as registered Resident
    await page.goto('/login');
    await page.fill('#email', 'carmela.reyes@resident.local');
    await page.fill('#password', 'Resident@12345');
    await page.click('button[type="submit"]');

    await page.waitForURL('**/dashboard');
    await expect(page.locator('h1')).toContainText('Hello, Carmela');

    // 2. Navigate directly to Complaint Filing Form
    await page.goto('/complaints/create');
    await expect(page.locator('h1')).toContainText('File a Complaint');

    // 3. Fill Required Complaint Form Inputs
    await page.locator('#report_type_id').selectOption({ index: 1 });
    await page.locator('#category_id').selectOption({ index: 1 });
    await page.fill('#title', 'E2E Automated UI Noise Disturbance Report');
    await page.fill(
      '#description',
      'This is an automated end-to-end test verification to ensure the complaint lifecycle and split datetime picker function seamlessly.'
    );
    await page.locator('#area_id').selectOption({ index: 1 });

    // 4. Test Zero-Delay Split Datetime Picker Shortcut Chip
    const justNowChip = page.locator('[data-dtp-chip][data-action="now"]');
    await expect(justNowChip).toBeVisible();
    await justNowChip.click();

    // Verify hidden ISO value is automatically synced and valid
    const hiddenIsoValue = await page.locator('#incident_date').inputValue();
    expect(hiddenIsoValue).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/);

    // 5. Submit the Complaint via the primary form button
    const submitBtn = page.getByRole('button', { name: 'Submit report' });
    await submitBtn.click();

    // Confirm Modal
    const modalOk = page.locator('#confirmModal [data-modal-ok]');
    await expect(modalOk).toBeVisible({ timeout: 2000 });
    await modalOk.click();

    // 6. Verify Redirected to Tracking / Complaint Details
    await page.waitForURL(/\/complaints\/BRGY-\d{4}-\d{5}/);
    await expect(page.locator('body')).toContainText('E2E Automated UI Noise Disturbance Report');
    await expect(page.locator('body')).toContainText('Pending');
  });
});
