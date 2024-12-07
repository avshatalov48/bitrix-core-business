const { test } = require('@playwright/test');
const {
	focusEditor,
	initializeTest,
	assertHTML,
	insertSampleImage,
	assertSelection,
	click, waitForSelector,
} = require('./utils');

const { moveLeft, selectAll, pressBackspace } = require('./keyboard');
const { paragraph, image, br, text } = require('./html');
const { defaultTheme } = require('../../../src/themes/default-theme');

test.describe('Images', () => {
	test('Can create a decorator and move selection around it', async ({ page }) => {
		await initializeTest({ page });
		await focusEditor(page);
		await insertSampleImage(page);

		await assertHTML(
			page,
			paragraph(image() + br()),
			{ ignoreInlineStyles: true },
		);

		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0],
			focusOffset: 1,
			focusPath: [0],
		});

		await focusEditor(page);
		await page.keyboard.press('ArrowLeft');
		await page.keyboard.press('ArrowLeft');
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});

		await page.keyboard.press('ArrowRight');
		await page.keyboard.press('ArrowRight');
		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0],
			focusOffset: 1,
			focusPath: [0],
		});

		await page.keyboard.press('ArrowRight');
		await page.keyboard.press('ArrowRight');
		await page.keyboard.press('Backspace');

		await assertHTML(page, paragraph());

		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});

		await insertSampleImage(page);
		await click(page, `.${defaultTheme.image.img}`);

		await assertHTML(
			page,
			paragraph(image(true) + br()),
			{ ignoreInlineStyles: true },
		);

		await page.keyboard.press('Backspace');

		await assertHTML(page, paragraph());

		await focusEditor(page);
		await insertSampleImage(page);

		await assertHTML(
			page,
			paragraph(image() + br()),
			{ ignoreInlineStyles: true },
		);

		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0],
			focusOffset: 1,
			focusPath: [0],
		});

		await page.keyboard.press('ArrowLeft');
		await page.keyboard.press('ArrowLeft');

		await page.keyboard.press('Delete');

		await assertHTML(page, paragraph());

		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});
	});

	test('Can add images and delete them correctly', async ({ page }) => {
		await initializeTest({ page });
		await focusEditor(page);
		await insertSampleImage(page);
		await insertSampleImage(page);

		await focusEditor(page);
		await moveLeft(page, 4);

		await assertHTML(
			page,
			paragraph(image() + image() + br()),
			{ ignoreInlineStyles: true },
		);

		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});

		await page.keyboard.press('Delete');
		await assertHTML(
			page,
			paragraph(image() + br()),
			{ ignoreInlineStyles: true },
		);

		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});

		await page.keyboard.press('Delete');
		await assertHTML(page, paragraph());
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});

		await page.keyboard.type('Test');
		await insertSampleImage(page);
		await insertSampleImage(page);

		await focusEditor(page);
		await moveLeft(page, 4);

		await assertHTML(
			page,
			paragraph(text('Test') + image() + image() + br()),
			{ ignoreInlineStyles: true },
		);

		await assertSelection(page, {
			anchorOffset: 4,
			anchorPath: [0, 0, 0],
			focusOffset: 4,
			focusPath: [0, 0, 0],
		});
		await page.keyboard.press('Delete');

		await assertHTML(
			page,
			paragraph(text('Test') + image() + br()),
			{ ignoreInlineStyles: true },
		);

		await assertSelection(page, {
			anchorOffset: 4,
			anchorPath: [0, 0, 0],
			focusOffset: 4,
			focusPath: [0, 0, 0],
		});
	});

	test('Node selection: can select multiple image nodes and replace them with a new image', async ({ page }) => {
		await initializeTest({ page });
		await focusEditor(page);

		await page.keyboard.type('text1');
		await page.keyboard.press('Enter');
		await insertSampleImage(page);
		await page.keyboard.press('Enter');
		await page.keyboard.type('text2');
		await page.keyboard.press('Enter');
		await insertSampleImage(page);
		await page.keyboard.press('Enter');
		await page.keyboard.type('text3');

		await assertHTML(
			page,
			paragraph(text('text1') + br() + image() + br() + text('text2') + br() + image() + br() + text('text3')),
			{ ignoreInlineStyles: true },
		);
	});

	test('Can select all and delete everything', async ({ page }) => {
		await initializeTest({ page, demoContent: true });
		await focusEditor(page);

		await waitForSelector(page, `.${defaultTheme.image.img}`);

		await selectAll(page);
		await pressBackspace(page);

		await assertHTML(page, paragraph());
	});
});
