const { test } = require('@playwright/test');
const { focusEditor, initializeTest, assertHTML, toggleCodeBlock } = require('./utils');
const { paragraph, text, code, codeToken, br } = require('./html');

test.describe('Code', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Can select multiple paragraphs and convert to code block', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('function()');
		await page.keyboard.press('Enter');
		await page.keyboard.type('{');
		await page.keyboard.press('Enter');
		await page.keyboard.type('}');

		await assertHTML(
			page,
			paragraph(text('function()') + br() + text('{') + br() + text('}')),
		);

		await toggleCodeBlock(page);

		await assertHTML(
			page,
			code(
				codeToken('function')
				+ codeToken('(', 'parentheses')
				+ codeToken(')', 'parentheses')
				+ br()
				+ codeToken('{', 'brace')
				+ br()
				+ codeToken('}', 'brace'),
			) + paragraph(),
		);
	});
});
