import { test, expect } from '@playwright/test';

test.describe('Project Management', () => {
    // Login before each test
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Email address').fill('test@example.com');
        await page.getByLabel('Password').fill('password');
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL(/dashboard/);
    });

    test('can navigate to projects list', async ({ page }) => {
        await page.getByRole('link', { name: 'Projects' }).click();

        await expect(page).toHaveURL(/projects/);
        await expect(page.getByRole('heading', { name: /Projects/i })).toBeVisible();
    });

    test('can create a new project', async ({ page }) => {
        await page.goto('/app/projects');

        // Click create button
        await page.getByRole('link', { name: /New Project|Create/i }).click();

        await expect(page).toHaveURL(/projects\/create/);

        // Fill project form
        const projectName = `Test Project ${Date.now()}`;
        await page.getByLabel(/Project Name|Name/i).fill(projectName);
        await page.getByLabel(/Description/i).fill('E2E test project description');

        // Submit form
        await page.getByRole('button', { name: /Create|Save/i }).click();

        // Should redirect to project page
        await page.waitForURL(/projects\/\d+/);
        await expect(page.getByRole('heading', { name: projectName })).toBeVisible();
    });

    test('can view project details tabs', async ({ page }) => {
        await page.goto('/app/projects');

        // Click on first project (if exists)
        const projectCard = page.locator('[data-testid="project-card"]').first();
        if (await projectCard.isVisible()) {
            await projectCard.click();

            // Check tabs are visible
            await expect(page.getByRole('link', { name: 'Overview' })).toBeVisible();
            await expect(page.getByRole('link', { name: 'Tasks' })).toBeVisible();
            await expect(page.getByRole('link', { name: 'Issues' })).toBeVisible();
            await expect(page.getByRole('link', { name: 'Costs' })).toBeVisible();
        }
    });

    test('can navigate between project tabs', async ({ page }) => {
        await page.goto('/app/projects');

        const projectLink = page.locator('a[href*="/projects/"]').first();
        if (await projectLink.isVisible()) {
            await projectLink.click();
            await page.waitForURL(/projects\/\d+/);

            // Navigate to Tasks tab
            await page.getByRole('link', { name: 'Tasks' }).click();
            await expect(page).toHaveURL(/tasks/);

            // Navigate to Issues tab
            await page.getByRole('link', { name: 'Issues' }).click();
            await expect(page).toHaveURL(/issues/);

            // Navigate to Costs tab
            await page.getByRole('link', { name: 'Costs' }).click();
            await expect(page).toHaveURL(/costs/);
        }
    });
});
