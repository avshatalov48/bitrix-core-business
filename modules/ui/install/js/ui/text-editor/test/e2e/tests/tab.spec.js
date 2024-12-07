const { test } = require('@playwright/test');
const { focusEditor, assertHTML, initializeTest, insertCodeBlock } = require('./utils');
const { paragraph, text, code, codeToken } = require('./html');

test.describe('Tab', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Cannot tab inside editor', async ({ page }) => {
		await focusEditor(page);

		await assertHTML(page, paragraph());

		await page.keyboard.press('Tab');
		await page.keyboard.press('Tab');

		await assertHTML(page, paragraph());
	});

	test('Can tab inside code block', async ({ page }) => {
		await focusEditor(page);

		await assertHTML(page, paragraph());

		await insertCodeBlock(page);

		await page.keyboard.press('Tab');
		await page.keyboard.press('Tab');
		await page.keyboard.type('function');

		await assertHTML(
			page,
			code(text('\t') + text('\t') + codeToken('function')) + paragraph(),
			{ preserveTab: true },
		);
	});
});
