import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
    test('login page loads correctly', async ({ page }) => {
        await page.goto('/login');

        // Check page title
        await expect(page).toHaveTitle(/Log in/);

        // Check form elements
        await expect(page.getByLabel('Email address')).toBeVisible();
        await expect(page.getByLabel('Password')).toBeVisible();
        await expect(page.getByRole('button', { name: /Sign in/i })).toBeVisible();
    });

    test('shows validation errors for empty form', async ({ page }) => {
        await page.goto('/login');

        // Submit empty form
        await page.getByRole('button', { name: /Sign in/i }).click();

        // Check for validation (HTML5 or custom)
        const emailInput = page.getByLabel('Email address');
        await expect(emailInput).toHaveAttribute('required', '');
    });

    test('can navigate to register page', async ({ page }) => {
        await page.goto('/login');

        await page.getByRole('link', { name: /Sign up for free/i }).click();

        await expect(page).toHaveURL(/register/);
        await expect(page.getByRole('heading', { name: /Create your account/i })).toBeVisible();
    });

    test('can navigate to forgot password', async ({ page }) => {
        await page.goto('/login');

        await page.getByRole('link', { name: /Forgot password/i }).click();

        await expect(page).toHaveURL(/forgot-password/);
    });

    test('successful login redirects to dashboard', async ({ page }) => {
        // This test requires seeded test user credentials
        await page.goto('/login');

        await page.getByLabel('Email address').fill('test@example.com');
        await page.getByLabel('Password').fill('password');
        await page.getByRole('button', { name: /Sign in/i }).click();

        // Should redirect to dashboard after successful login
        await page.waitForURL(/dashboard/, { timeout: 10000 });
        await expect(page.getByRole('heading', { name: /Good/ })).toBeVisible();
    });

    test('logout works correctly', async ({ page }) => {
        // Login first
        await page.goto('/login');
        await page.getByLabel('Email address').fill('test@example.com');
        await page.getByLabel('Password').fill('password');
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL(/dashboard/);

        // Logout via user menu
        await page.getByRole('button', { name: /user menu|account/i }).click();
        await page.getByRole('button', { name: /Log Out/i }).click();

        // Should be back at login
        await expect(page).toHaveURL(/login/);
    });
});
