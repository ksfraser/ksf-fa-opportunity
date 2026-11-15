import { test, expect } from '@playwright/test';

test.describe('ksf_FA_Opportunity Opportunity Module', () => {
  test('should load opportunity list page', async ({ page }) => {
    await page.goto('/modules/ksf_FA_Opportunity/pages/opportunities.php');
    await expect(page.locator('h1, h2')).toContainText(/Opportunity/i);
  });
});
