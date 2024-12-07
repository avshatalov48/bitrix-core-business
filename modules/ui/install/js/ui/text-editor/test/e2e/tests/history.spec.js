const { test } = require('@playwright/test');
const { initializeTest, focusEditor, sleep, assertHTML, assertSelection } = require('./utils');
const { moveLeft, redo, undo, pressBackspace, toggleBold } = require('./keyboard');
const { paragraph, bold, text } = require('./html');

test.describe('History', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page, editorOptions: { newLineMode: 'paragraph' } }));
	test('Can type two paragraphs of text and correctly undo and redo', async ({ page }) => {
		await focusEditor(page);

		await page.keyboard.type('hello');
		await sleep(1050);
		await page.keyboard.type(' world');
		await page.keyboard.press('Enter');
		await page.keyboard.type('hello world again');
		await moveLeft(page, 6);
		await page.keyboard.type(', again and');

		await assertHTML(page, paragraph('hello world') + paragraph('hello world, again and again'));
		await assertSelection(page, {
			anchorOffset: 22,
			anchorPath: [1, 0, 0],
			focusOffset: 22,
			focusPath: [1, 0, 0],
		});

		await undo(page);

		await assertHTML(page, paragraph('hello world') + paragraph('hello world again'));
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [1, 0, 0],
			focusOffset: 11,
			focusPath: [1, 0, 0],
		});

		await undo(page);

		await assertHTML(page, paragraph('hello world') + paragraph());

		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [1],
			focusOffset: 0,
			focusPath: [1],
		});

		await undo(page);

		await assertHTML(page, paragraph('hello world'));
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 11,
			focusPath: [0, 0, 0],
		});

		await undo(page);

		await assertHTML(page, paragraph('hello'));
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 0, 0],
			focusOffset: 5,
			focusPath: [0, 0, 0],
		});

		await undo(page);

		await assertHTML(page, paragraph());
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});

		await redo(page);

		await assertHTML(page, paragraph('hello'));
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 0, 0],
			focusOffset: 5,
			focusPath: [0, 0, 0],
		});

		await redo(page);

		await assertHTML(page, paragraph('hello world'));
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [0, 0, 0],
			focusOffset: 11,
			focusPath: [0, 0, 0],
		});

		await redo(page);

		await assertHTML(page, paragraph('hello world') + paragraph());
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [1],
			focusOffset: 0,
			focusPath: [1],
		});

		await redo(page);

		await assertHTML(page, paragraph('hello world') + paragraph('hello world again'));
		await assertSelection(page, {
			anchorOffset: 11,
			anchorPath: [1, 0, 0],
			focusOffset: 11,
			focusPath: [1, 0, 0],
		});

		await redo(page);

		await assertHTML(page, paragraph('hello world') + paragraph('hello world, again and again'));
		await assertSelection(page, {
			anchorOffset: 22,
			anchorPath: [1, 0, 0],
			focusOffset: 22,
			focusPath: [1, 0, 0],
		});

		await pressBackspace(page, 4);

		await assertHTML(page, paragraph('hello world') + paragraph('hello world, again again'));
		await assertSelection(page, {
			anchorOffset: 18,
			anchorPath: [1, 0, 0],
			focusOffset: 18,
			focusPath: [1, 0, 0],
		});

		await undo(page);

		await assertHTML(page, paragraph('hello world') + paragraph('hello world, again and again'));
		await assertSelection(page, {
			anchorOffset: 22,
			anchorPath: [1, 0, 0],
			focusOffset: 22,
			focusPath: [1, 0, 0],
		});
	});

	test('Can coalesce when switching inline styles', async ({ page }) => {
		await focusEditor(page);
		await toggleBold(page);
		await page.keyboard.type('foo');
		await toggleBold(page);
		await page.keyboard.type('bar');
		await toggleBold(page);
		await page.keyboard.type('baz');

		const step1HTML = paragraph(bold('foo') + text('bar') + bold('baz'));
		const step2HTML = paragraph(bold('foo') + text('bar'));
		const step3HTML = paragraph(bold('foo'));
		const step4HTML = paragraph();

		await assertHTML(page, step1HTML);
		await undo(page);
		await assertHTML(page, step2HTML);
		await undo(page);
		await assertHTML(page, step3HTML);
		await undo(page);
		await assertHTML(page, step4HTML);
		await redo(page);
		await assertHTML(page, step3HTML);
		await redo(page);
		await assertHTML(page, step2HTML);
		await redo(page);
		await assertHTML(page, step1HTML);
	});
});
