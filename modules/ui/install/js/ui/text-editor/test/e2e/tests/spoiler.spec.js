const { test } = require('@playwright/test');
const { focusEditor, initializeTest, assertHTML, insertSpoiler } = require('./utils');
const { paragraph, spoiler } = require('./html');

test.describe('Spoiler', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Can insert a spoiler', async ({ page }) => {
		await focusEditor(page);
		await assertHTML(page, paragraph());

		await insertSpoiler(page);

		await assertHTML(page, spoiler() + paragraph());
	});

	test('Can toggle content into a spoiler', async ({ page }) => {
		await focusEditor(page);

		const content = 'Возникновение ковалентных связей объясняется тем, что спирт тягуч. Сворачивание проникает белый пушистый осадок. Мембрана, на первый взгляд, ковалентно затрудняет возбужденный серный эфир, что получается при взаимодействии с нелетучими кислотными оксидами.';
		await page.keyboard.type(content);

		await assertHTML(page, paragraph(content));

		await insertSpoiler(page);

		await assertHTML(page, spoiler(paragraph(content)) + paragraph());

		await page.locator('summary').click();

		await assertHTML(page, spoiler(paragraph(content), false) + paragraph());
	});
});
