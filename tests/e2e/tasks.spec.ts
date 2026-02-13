import { test, expect } from '@playwright/test';

test.describe('Task Management', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Email address').fill('test@example.com');
        await page.getByLabel('Password').fill('password');
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL(/dashboard/);
    });

    test('can view tasks in a project', async ({ page }) => {
        await page.goto('/app/projects');

        const projectLink = page.locator('a[href*="/projects/"]').first();
        if (await projectLink.isVisible()) {
            await projectLink.click();
            await page.waitForURL(/projects\/\d+/);

            await page.getByRole('link', { name: 'Tasks' }).click();
            await expect(page).toHaveURL(/tasks/);
        }
    });

    test('can create a new task', async ({ page }) => {
        // Navigate to a project's tasks page
        await page.goto('/app/projects');

        const projectLink = page.locator('a[href*="/projects/"]').first();
        if (await projectLink.isVisible()) {
            await projectLink.click();
            await page.getByRole('link', { name: 'Tasks' }).click();

            // Click add task button
            const addButton = page.getByRole('link', { name: /Add Task|New Task|Create/i });
            if (await addButton.isVisible()) {
                await addButton.click();

                // Fill task form
                const taskTitle = `Test Task ${Date.now()}`;
                await page.getByLabel(/Title|Name/i).first().fill(taskTitle);

                // Submit
                await page.getByRole('button', { name: /Create|Save|Add/i }).click();

                // Verify task appears
                await expect(page.getByText(taskTitle)).toBeVisible();
            }
        }
    });
});

test.describe('Issue Management', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Email address').fill('test@example.com');
        await page.getByLabel('Password').fill('password');
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL(/dashboard/);
    });

    test('can view issues in a project', async ({ page }) => {
        await page.goto('/app/projects');

        const projectLink = page.locator('a[href*="/projects/"]').first();
        if (await projectLink.isVisible()) {
            await projectLink.click();
            await page.waitForURL(/projects\/\d+/);

            await page.getByRole('link', { name: 'Issues' }).click();
            await expect(page).toHaveURL(/issues/);
        }
    });
});

test.describe('Cost Tracking', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.getByLabel('Email address').fill('test@example.com');
        await page.getByLabel('Password').fill('password');
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL(/dashboard/);
    });

    test('can view costs in a project', async ({ page }) => {
        await page.goto('/app/projects');

        const projectLink = page.locator('a[href*="/projects/"]').first();
        if (await projectLink.isVisible()) {
            await projectLink.click();
            await page.waitForURL(/projects\/\d+/);

            await page.getByRole('link', { name: 'Costs' }).click();
            await expect(page).toHaveURL(/costs/);
        }
    });
});
