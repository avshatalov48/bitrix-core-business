const { test, expect } = require('@playwright/test');
const {
	IS_MAC,
	focusEditor,
	initializeTest,
	pasteFromClipboard,
	assertHTML,
	sleep,
	clickToolbarButton,
	keyDownCtrlOrMeta,
	keyUpCtrlOrMeta,
	insertTable,
	pressToggleBold,
	assertSelection,
	insertSampleImage,
	insertYoutubeVideo,
} = require('./utils');

const {
	moveToLineBeginning,
	moveRight,
	pressShiftEnter,
	deleteBackward,
	moveLeft,
	selectAll,
	moveToPrevWord,
	selectPrevWord,
} = require('./keyboard');
const { paragraph, bold, code, word, text, br } = require('./html');

test.describe.parallel('Selection', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('does not focus the editor on load', async ({ page }) => {
		const editorHasFocus = async () => {
			return page.evaluate(() => {
				const editorElement = document.querySelector('[data-lexical-editor="true"]');

				return document.activeElement === editorElement;
			});
		};

		expect(await editorHasFocus()).toEqual(false);
		await sleep(500);
		expect(await editorHasFocus()).toEqual(false);
	});

	test('can wrap post-linebreak nodes into new element', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Line1');
		await pressShiftEnter(page);
		await page.keyboard.type('Line2');
		await page.keyboard.down('Shift');
		await moveToLineBeginning(page);
		await page.keyboard.up('Shift');

		await clickToolbarButton(page, 'enclose-text-in-code-tag');
		await assertHTML(
			page,
			paragraph('Line1') + code(word('Line2')) + paragraph(),
		);
	});

	test('can delete text by line with CMD+delete', async ({ page }) => {
		test.skip(!IS_MAC);
		await focusEditor(page);
		await page.keyboard.type('One');
		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');
		await page.keyboard.type('Two');
		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');
		await page.keyboard.type('Three');

		const deleteLine = async () => {
			await keyDownCtrlOrMeta(page);
			await page.keyboard.press('Backspace');
			await keyUpCtrlOrMeta(page);
		};

		const lines = [
			paragraph('One'),
			paragraph('Two'),
			paragraph('Three'),
		];

		await assertHTML(page, lines.join(''));
		await deleteLine();
		await assertHTML(page, lines.slice(0, 2).join(''));
		await deleteLine();
		await assertHTML(page, lines.slice(0, 1).join(''));
		await deleteLine();
		await assertHTML(page, paragraph());
	});

	// test('can delete line which ends with element with CMD+delete', async ({ page }) => {
	// 	test.skip(!IS_MAC);
	//
	// 	await focusEditor(page);
	// 	await page.keyboard.type('One');
	// 	await page.keyboard.press('Enter');
	// 	await page.keyboard.press('Enter');
	// 	await page.keyboard.type('Two');
	//
	// 	await insertSampleImage(page);
	//
	// 	const deleteLine = async ()=> {
	// 		await keyDownCtrlOrMeta(page);
	// 		await page.keyboard.press('Backspace');
	// 		await keyUpCtrlOrMeta(page);
	// 	};
	//
	// 	await deleteLine();
	//
	// 	await assertHTML(page, paragraph('One'));
	// 	await deleteLine();
	// 	await assertHTML(page, paragraph());
	// });

	test('Can insert inline element within text and put selection after it', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello world');
		await moveToPrevWord(page);
		await pasteFromClipboard(page, {
			'text/html': '<a href="https://test.com">link</a>',
		});
		await sleep(3000);
		await assertSelection(page, {
			anchorOffset: 4,
			anchorPath: [0, 1, 0, 0],
			focusOffset: 4,
			focusPath: [0, 1, 0, 0],
		});
	});

	test('Can delete at boundary', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('aaa');
		await page.keyboard.press('Enter');
		await page.keyboard.type('b');
		await page.keyboard.press('Enter');
		await page.keyboard.type('c');

		await page.keyboard.down('Shift');
		await moveLeft(page, 3);
		await page.keyboard.up('Shift');
		await page.keyboard.press('Delete');
		await assertHTML(
			page,
			paragraph(text('aaa') + br(2)),
		);

		await page.keyboard.down('Shift');
		await moveLeft(page, 1);
		await page.keyboard.up('Shift');
		await page.keyboard.press('Delete');
		await assertHTML(
			page,
			paragraph(text('aaa')),
		);
	});

	test('Can select all with node selection', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Text before');
		await insertSampleImage(page);
		await page.keyboard.type('Text after');
		await selectAll(page);
		await deleteBackward(page);
		await assertHTML(page, paragraph());
	});

	test('Can delete block elements', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('A');
		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');
		await page.keyboard.type('b');
		await assertHTML(page, paragraph('A') + paragraph('b'));
		await moveLeft(page, 2);

		await deleteBackward(page);
		await assertHTML(page, paragraph() + paragraph('b'));

		await deleteBackward(page);
		await assertHTML(page, paragraph('b'));
	});

	test('Select all from Node selection', async ({ page }) => {
		await focusEditor(page);
		await insertYoutubeVideo(page);
		await page.keyboard.type('abcdefg');
		await moveLeft(page, 'abcdefg'.length + 1);

		await selectAll(page);
		await page.keyboard.press('Backspace');

		await assertHTML(page, paragraph());
	});

	test('Select all (DecoratorNode at start)', async ({ page }) => {
		await focusEditor(page);
		await insertYoutubeVideo(page);
		// Delete empty paragraph in front
		await moveLeft(page, 2);
		await page.keyboard.press('Backspace');
		await moveRight(page, 2);
		await page.keyboard.type('abcdefg');

		await selectAll(page);
		await page.keyboard.press('Backspace');
		await assertHTML(page, paragraph());
	});

	test('Can delete table node present at the end', async ({ page }) => {
		await focusEditor(page);
		await insertTable(page, 1, 2);
		await page.keyboard.press('ArrowDown');
		await page.keyboard.down('Shift');
		await page.keyboard.press('ArrowUp');
		await page.keyboard.up('Shift');
		await page.keyboard.press('Backspace');
		await assertHTML(page, paragraph());
	});

	// test('Can persist the text format from the paragraph', async ({ page }) => {
	// 	await focusEditor(page);
	// 	await pressToggleBold(page);
	// 	await page.keyboard.type('Line1');
	// 	await page.keyboard.press('Enter');
	// 	await page.keyboard.press('Enter');
	// 	await page.keyboard.type('Line2');
	// 	await page.keyboard.press('ArrowUp');
	// 	await page.keyboard.type('Line3');
	// 	await assertHTML(
	// 		page,
	// 		paragraph(bold('Line 1')) + paragraph(bold('Line 2')),
	// 	);
	// });

	// test('toggle format at the start of paragraph to a different format persists the format', async ({ page }) => {
	// 	await focusEditor(page);
	// 	await pressToggleBold(page);
	// 	await page.keyboard.type('Line1');
	// 	await page.keyboard.press('Enter');
	// 	await page.keyboard.press('Enter');
	// 	await page.keyboard.press('Enter');
	// 	await pressToggleItalic(page);
	// 	await page.keyboard.type('Line2');
	// 	await page.keyboard.press('ArrowUp');
	// 	await pressToggleBold(page);
	// 	await page.keyboard.type('Line3');
	// 	await assertHTML(
	// 		page,
	// 		paragraph(),
	// 	);
	// });

	test('formatting is persisted after deleting all nodes from the paragraph node', async ({ page }) => {
		await focusEditor(page);
		await pressToggleBold(page);
		await page.keyboard.type('Line1');
		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');
		await pressToggleBold(page);
		await page.keyboard.type('Line2');
		await selectPrevWord(page);
		await page.keyboard.press('Backspace');
		await page.keyboard.type('Line3');

		await assertHTML(page, paragraph(bold('Line1')) + paragraph(bold('Line3')));
	});
});
