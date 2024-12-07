const { test, expect } = require('@playwright/test');
const { focusEditor, initializeTest, click, assertHTML, assertSelection, insertSampleImage } = require('./utils');
const {
	moveToLineBeginning,
	moveToLineEnd,
	moveRight,
	toggleBold,
	toggleItalic,
	moveLeft,
	selectCharacters,
	toggleUnderline,
} = require('./keyboard');
const { paragraph, text, bold, br, italic, underline, underlineStrike, boldItalic, image } = require('./html');

test.describe.parallel('TextFormatting', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Can create bold text using the shortcut', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello');
		await toggleBold(page);
		await page.keyboard.type(' World');
		await assertHTML(
			page,
			paragraph(text('Hello') + bold(' World')),
		);

		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 1, 0],
			focusOffset: 6,
			focusPath: [0, 1, 0],
		});

		await toggleBold(page);
		await page.keyboard.type('!');
		await assertHTML(
			page,
			paragraph(text('Hello') + bold(' World') + text('!')),
		);

		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0, 2, 0],
			focusOffset: 1,
			focusPath: [0, 2, 0],
		});
	});

	test('Can create italic text using the shortcut', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello');
		await toggleItalic(page);
		await page.keyboard.type(' World');
		await assertHTML(
			page,
			paragraph(text('Hello') + italic(' World')),
		);
		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 1, 0],
			focusOffset: 6,
			focusPath: [0, 1, 0],
		});

		await toggleItalic(page);
		await page.keyboard.type('!');

		await assertHTML(
			page,
			paragraph(text('Hello') + italic(' World') + text('!')),
		);

		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0, 2, 0],
			focusOffset: 1,
			focusPath: [0, 2, 0],
		});
	});

	test('Can select text and boldify it with the shortcut', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello world!');
		await moveLeft(page);
		await selectCharacters(page, 'left', 5);
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});

		await toggleBold(page);
		await assertHTML(
			page,
			paragraph(text('Hello ') + bold('world') + text('!')),
		);

		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 1, 0],
			focusOffset: 0,
			focusPath: [0, 1, 0],
		});

		await toggleBold(page);
		await assertHTML(
			page,
			paragraph('Hello world!'),
		);

		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});
	});

	test('Can select text and italicify it with the shortcut', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello world!');
		await moveLeft(page);
		await selectCharacters(page, 'left', 5);
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});

		await toggleItalic(page);
		await assertHTML(
			page,
			paragraph(text('Hello ') + italic('world') + text('!')),
		);
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 1, 0],
			focusOffset: 0,
			focusPath: [0, 1, 0],
		});

		await toggleItalic(page);
		await assertHTML(
			page,
			paragraph('Hello world!'),
		);
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});
	});

	test('Can select text and underline+strikethrough', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello world!');
		await moveLeft(page);
		await selectCharacters(page, 'left', 5);
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});

		await toggleUnderline(page);
		await assertHTML(
			page,
			paragraph(text('Hello ') + underline('world') + text('!')),
		);
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 1, 0],
			focusOffset: 0,
			focusPath: [0, 1, 0],
		});

		await toggleUnderline(page);
		await assertHTML(
			page,
			paragraph('Hello world!'),
		);
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});

		await toggleUnderline(page);

		await click(page, '.ui-text-editor-toolbar-button:has(> .--strikethrough)');

		await assertHTML(
			page,
			paragraph(text('Hello ') + underlineStrike('world') + text('!')),
		);
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 1, 0],
			focusOffset: 0,
			focusPath: [0, 1, 0],
		});

		await click(page, '.ui-text-editor-toolbar-button:has(> .--strikethrough)');

		await assertHTML(
			page,
			paragraph(text('Hello ') + underline('world') + text('!')),
		);
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 1, 0],
			focusOffset: 0,
			focusPath: [0, 1, 0],
		});
	});

	test('Can select multiple text parts and format them with shortcuts', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello world!');
		await moveLeft(page);
		await selectCharacters(page, 'left', 5);
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});

		await toggleBold(page);
		await assertHTML(
			page,
			paragraph(text('Hello ') + bold('world') + text('!')),
		);
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 1, 0],
			focusOffset: 0,
			focusPath: [0, 1, 0],
		});

		await moveLeft(page);
		await moveRight(page);
		await selectCharacters(page, 'right', 2);
		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0, 1, 0],
			focusOffset: 3,
			focusPath: [0, 1, 0],
		});

		await toggleItalic(page);
		await assertHTML(
			page,
			paragraph(text('Hello ') + bold('w') + boldItalic('or') + bold('ld') + text('!')),
		);
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0, 2, 0],
			focusOffset: 2,
			focusPath: [0, 2, 0],
		});

		await toggleBold(page);
		await assertHTML(
			page,
			paragraph(text('Hello ') + bold('w') + italic('or') + bold('ld') + text('!')),
		);
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0, 2, 0],
			focusOffset: 2,
			focusPath: [0, 2, 0],
		});

		await moveLeft(page, 2);
		await selectCharacters(page, 'right', 5);

		await toggleBold(page);
		await assertHTML(
			page,
			paragraph(text('Hello w') + italic('or') + text('ld!')),
		);
		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 0, 0],
			focusOffset: 2,
			focusPath: [0, 2, 0],
		});

		await toggleItalic(page);
		await assertHTML(
			page,
			paragraph(text('Hello ') + italic('world') + text('!')),
		);

		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0, 1, 0],
			focusOffset: 5,
			focusPath: [0, 1, 0],
		});

		await toggleItalic(page);
		await assertHTML(
			page,
			paragraph('Hello world!'),
		);
		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 0, 0],
			focusOffset: 11,
			focusPath: [0, 0, 0],
		});
	});

	test('Can insert range of formatted text and select part and replace with character', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('123');

		await toggleBold(page);

		await page.keyboard.type('456');

		await toggleBold(page);

		await page.keyboard.type('789');

		await page.keyboard.down('Shift');
		await page.keyboard.press('Enter');
		await page.keyboard.up('Shift');

		await page.keyboard.type('abc');

		await toggleBold(page);

		await page.keyboard.type('def');

		await toggleBold(page);

		await page.keyboard.type('ghi');

		await assertHTML(
			page,
			paragraph(text('123') + bold('456') + text('789') + br() + text('abc') + bold('def') + text('ghi')),
		);

		await assertSelection(page, {
			anchorOffset: 3,
			anchorPath: [0, 6, 0],
			focusOffset: 3,
			focusPath: [0, 6, 0],
		});

		await page.keyboard.press('ArrowUp');
		await moveToLineBeginning(page);

		await moveRight(page, 2);

		await page.keyboard.down('Shift');
		await page.keyboard.press('ArrowDown');
		await moveRight(page, 8);
		await page.keyboard.down('Shift');

		await assertSelection(page, {
			anchorOffset: 2,
			anchorPath: [0, 0, 0],
			focusOffset: 3,
			focusPath: [0, 6, 0],
		});

		await page.keyboard.type('z');

		await assertHTML(
			page,
			paragraph('12z'),
		);

		await assertSelection(page, {
			anchorOffset: 3,
			anchorPath: [0, 0, 0],
			focusOffset: 3,
			focusPath: [0, 0, 0],
		});
	});

	test('Can format backwards when at first text node boundary', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('123456');

		await moveLeft(page, 3);
		await page.keyboard.down('Shift');
		await moveLeft(page, 3);
		await page.keyboard.up('Shift');
		await toggleBold(page);

		await moveToLineEnd(page);
		await page.keyboard.down('Shift');
		await moveLeft(page, 4);
		await page.keyboard.up('Shift');
		await toggleBold(page);

		await assertHTML(
			page,
			paragraph(bold('12') + text('3456')),
		);

		// Toggle once more
		await toggleBold(page);

		await assertHTML(
			page,
			paragraph(bold('123456')),
		);
	});

	test('The active state of the button in the toolbar should to be displayed correctly', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('A');
		await page.keyboard.press('Enter');
		await page.keyboard.type('B');
		await selectCharacters(page, 'left', 3);
		await toggleBold(page);
		await toggleItalic(page);

		const isButtonActiveStatusDisplayedCorrectly = await page.evaluate(() => {
			const isToolbarBoldButtonActive = Boolean(document.querySelector(
				'.ui-text-editor-toolbar .ui-text-editor-toolbar-button.--active:has(> .--bold)',
			));
			const isToolbarItalicButtonActive = Boolean(document.querySelector(
				'.ui-text-editor-toolbar .ui-text-editor-toolbar-button.--active:has(> .--italic)',
			));

			return isToolbarBoldButtonActive && isToolbarItalicButtonActive;
		});

		expect(isButtonActiveStatusDisplayedCorrectly).toBe(true);
	});

	test('Can toggle format when selecting a TextNode edge followed by a non TextNode', async ({ page }) => {
		await focusEditor(page);

		await page.keyboard.type('A');
		await insertSampleImage(page);
		await page.keyboard.type('BC');

		await moveLeft(page, 1);
		await selectCharacters(page, 'left', 2);

		await assertHTML(
			page,
			paragraph(text('A') + image(true) + text('BC')),
			{ ignoreInlineStyles: true },
		);

		await toggleBold(page);
		await assertHTML(
			page,
			paragraph(text('A') + image() + bold('B') + text('C')),
			{ ignoreInlineStyles: true },
		);
		await toggleBold(page);
		await assertHTML(
			page,
			paragraph(text('A') + image() + text('BC')),
			{ ignoreInlineStyles: true },
		);
	});

	test('Multiline selection format ignores new lines', async ({ page }) => {
		await focusEditor(page);

		await page.keyboard.type('Fist');
		await page.keyboard.press('Enter');
		await toggleUnderline(page);
		await page.keyboard.type('Second');
		await page.pause();

		await moveLeft(page, 'Second'.length + 1);
		await page.pause();
		await selectCharacters(page, 'right', 'Second'.length + 1);
		await page.pause();

		await expect(page.locator('.ui-text-editor-toolbar-button:has(> .--underline)')).toHaveClass(/--active/);
	});
});
