const { test } = require('@playwright/test');
const { focusEditor, initializeTest, assertHTML } = require('./utils');
const { paragraph, text, emoji, br } = require('./html');

test.describe('Emoji', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Can handle a single emoji', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('This is an emoji :)');

		await assertHTML(
			page,
			paragraph(text('This is an emoji ') + emoji(':)') + br()),
			{ ignoreInlineStyles: true },
		);

		await page.keyboard.press('Backspace');

		await assertHTML(
			page,
			paragraph(text('This is an emoji ')),
		);

		await page.keyboard.type(':)');

		await assertHTML(
			page,
			paragraph(text('This is an emoji ') + emoji(':)') + br()),
			{ ignoreInlineStyles: true },
		);
	});

	test('Can enter multiple emoticons', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type(':) :) :-D :{}');

		await assertHTML(
			page,
			paragraph(emoji(':)') + text(' ') + emoji(':)') + text(' ') + emoji(':-D') + text(' ') + emoji(':{}') + br()),
			{ ignoreInlineStyles: true },
		);
	});
});
