const { test } = require('@playwright/test');
const {
	focusEditor,
	initializeTest,
	waitForSelector,
	assertHTML,
	assertSelection,
	pressToggleBold,
	repeat,
} = require('./utils');

const { paragraph, hashtag, text } = require('./html');
const { moveLeft, moveToEditorBeginning, deleteNextWord } = require('./keyboard');
const { defaultTheme } = require('../../../src/themes/default-theme');

test.describe('Hashtags', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Can handle a single hashtag', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('#yolo');

		await waitForSelector(page, `.${defaultTheme.hashtag}`);

		await assertHTML(page, paragraph(hashtag('#yolo')));
		await assertSelection(page, {
			anchorOffset: 5,
			anchorPath: [0, 0, 0],
			focusOffset: 5,
			focusPath: [0, 0, 0],
		});

		await page.keyboard.press('Backspace');
		await page.keyboard.type('once');

		await assertHTML(page, paragraph(hashtag('#yolonce')));
		await assertSelection(page, {
			anchorOffset: 8,
			anchorPath: [0, 0, 0],
			focusOffset: 8,
			focusPath: [0, 0, 0],
		});

		await moveLeft(page, 10);
		await page.keyboard.press('Delete');

		await assertHTML(page, paragraph(text('yolonce')));
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0, 0, 0],
			focusOffset: 0,
			focusPath: [0, 0, 0],
		});
	});

	test('Can handle adjacent hashtags', async ({ page, browserName }) => {
		await focusEditor(page);
		await page.keyboard.type('#hello world');

		await waitForSelector(page, `.${defaultTheme.hashtag}`);

		await assertHTML(page, paragraph(hashtag('#hello') + text(' world')));
		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 1, 0],
			focusOffset: 6,
			focusPath: [0, 1, 0],
		});

		await moveLeft(page, 5);
		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0, 1, 0],
			focusOffset: 1,
			focusPath: [0, 1, 0],
		});

		await page.keyboard.press('Backspace');
		await assertHTML(page, paragraph(hashtag('#helloworld')));
		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});

		await page.keyboard.press('Space');
		await assertHTML(page, paragraph(hashtag('#hello') + text(' world')));
		await assertSelection(page, {
			anchorOffset: 1,
			anchorPath: [0, 1, 0],
			focusOffset: 1,
			focusPath: [0, 1, 0],
		});

		await moveLeft(page);
		if (browserName === 'firefox')
		{
			await assertSelection(page, {
				anchorOffset: 0,
				anchorPath: [0, 1, 0],
				focusOffset: 0,
				focusPath: [0, 1, 0],
			});
		}
		else
		{
			await assertSelection(page, {
				anchorOffset: 6,
				anchorPath: [0, 0, 0],
				focusOffset: 6,
				focusPath: [0, 0, 0],
			});
		}

		await page.keyboard.press('Delete');
		await assertHTML(page, paragraph(hashtag('#helloworld')));
		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});
	});

	test('Can insert many hashtags mixed with text and delete them all correctly', async ({
		page,
	}) => {
		await focusEditor(page);
		await page.keyboard.type(
			'#hello world foo #lol #lol asdasd #lol test this #asdas #asdas lasdasd asdasd',
		);

		await waitForSelector(page, `.${defaultTheme.hashtag}`);

		await assertHTML(page, paragraph(
			hashtag('#hello')
			+ text(' world foo ')
			+ hashtag('#lol')
			+ text(' ')
			+ hashtag('#lol')
			+ text(' asdasd ')
			+ hashtag('#lol')
			+ text(' test this ')
			+ hashtag('#asdas')
			+ text(' ')
			+ hashtag('#asdas')
			+ text(' lasdasd asdasd')
			,
		));

		await assertSelection(page, {
			anchorOffset: 15,
			anchorPath: [0, 11, 0],
			focusOffset: 15,
			focusPath: [0, 11, 0],
		});

		await moveToEditorBeginning(page);

		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0, 0, 0],
			focusOffset: 0,
			focusPath: [0, 0, 0],
		});

		await repeat(20, async () => {
			await deleteNextWord(page);
		});

		await assertHTML(page, paragraph());
		await assertSelection(page, {
			anchorOffset: 0,
			anchorPath: [0],
			focusOffset: 0,
			focusPath: [0],
		});
	});

	test('Hashtag inherits format', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello ');
		await pressToggleBold(page);
		await page.keyboard.type('#world');

		await assertHTML(
			page,
			paragraph(
				`${text('Hello ')}<strong class="${defaultTheme.text.bold} ${defaultTheme.hashtag}" data-lexical-text="true">#world</strong>`
			),
		);
	});

	test('Should not break with multiple leading "#"', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('#hello');

		await waitForSelector(page, `.${defaultTheme.hashtag}`);
		await assertHTML(page, paragraph(hashtag('#hello')));

		await assertSelection(page, {
			anchorOffset: 6,
			anchorPath: [0, 0, 0],
			focusOffset: 6,
			focusPath: [0, 0, 0],
		});

		await moveToEditorBeginning(page);
		await page.keyboard.type('#');

		await assertHTML(page, paragraph(text('#') + hashtag('#hello')));
	});

	test('Should not break while skipping invalid hashtags', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('#hello');

		await page.keyboard.press('Space');

		await page.keyboard.type('#world');
		await page.keyboard.type('#invalid');

		await page.keyboard.press('Space');
		await page.keyboard.type('#next');

		await waitForSelector(page, `.${defaultTheme.hashtag}`);

		await assertHTML(page, paragraph(
			hashtag('#hello') + text(' ') + hashtag('#world#invalid') + text(' ') + hashtag('#next'),
		));
	});
});
