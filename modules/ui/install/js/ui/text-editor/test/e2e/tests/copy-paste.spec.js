const { test } = require('@playwright/test');
const {
	focusEditor,
	initializeTest,
	assertHTML,
	assertSelection,
	copyToClipboard,
	pasteFromClipboard,
	IS_LINUX,
	toggleLink,
} = require('./utils');

const { paragraph, text, br, hashtag, link, bold, italic, code, codeToken } = require('./html');
const { selectAll, moveToPrevWord } = require('./keyboard');

['line-break', 'paragraph', 'mixed'].forEach((newLineMode) => {
	test.describe(`Copy & Paste (${newLineMode} mode)`, () => {
		const editorOptions = { newLineMode };
		test.beforeEach(async ({ page }) => initializeTest({ page, editorOptions }));

		const isRichText = newLineMode === 'paragraph' || newLineMode === 'mixed';

		test('Basic copy + paste', async ({ page, browserName, context }) => {
			await focusEditor(page);

			// Add paragraph
			await page.keyboard.type('Copy + pasting?');
			await page.keyboard.press('Enter');
			await page.keyboard.press('Enter');
			if (newLineMode === 'mixed')
			{
				await page.keyboard.press('Enter');
				await page.keyboard.press('Enter');
			}

			await page.keyboard.type('Sounds good!');
			if (isRichText)
			{
				await assertHTML(page, paragraph('Copy + pasting?') + paragraph() + paragraph('Sounds good!'));
				await assertSelection(page, {
					anchorOffset: 12,
					anchorPath: [2, 0, 0],
					focusOffset: 12,
					focusPath: [2, 0, 0],
				});
			}
			else
			{
				await assertHTML(page, paragraph(text('Copy + pasting?') + br() + br() + text('Sounds good!')));
				await assertSelection(page, {
					anchorOffset: 12,
					anchorPath: [0, 3, 0],
					focusOffset: 12,
					focusPath: [0, 3, 0],
				});
			}

			// Select all the text
			await selectAll(page);
			if (isRichText)
			{
				await assertHTML(page, paragraph('Copy + pasting?') + paragraph() + paragraph('Sounds good!'));
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [],
						focusOffset: 3,
						focusPath: [],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [0, 0, 0],
						focusOffset: 12,
						focusPath: [2, 0, 0],
					});
				}
			}
			else
			{
				await assertHTML(page, paragraph(text('Copy + pasting?') + br() + br() + text('Sounds good!')));
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [],
						focusOffset: 1,
						focusPath: [],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [0, 0, 0],
						focusOffset: 12,
						focusPath: [0, 3, 0],
					});
				}
			}

			// Copy all the text
			const clipboard = await copyToClipboard(page);

			if (isRichText)
			{
				await assertHTML(page, paragraph('Copy + pasting?') + paragraph() + paragraph('Sounds good!'));
			}
			else
			{
				await assertHTML(page, paragraph(text('Copy + pasting?') + br() + br() + text('Sounds good!')));
			}

			await page.pause();

			// Paste after
			await page.keyboard.press('ArrowRight');
			await pasteFromClipboard(page, clipboard);

			await page.pause();
			if (isRichText)
			{
				await assertHTML(
					page,
					paragraph('Copy + pasting?')
					+ paragraph()
					+ paragraph('Sounds good!Copy + pasting?')
					+ paragraph()
					+ paragraph('Sounds good!'),
				);

				await assertSelection(page, {
					anchorOffset: 12,
					anchorPath: [4, 0, 0],
					focusOffset: 12,
					focusPath: [4, 0, 0],
				});
			}
			else
			{
				await assertHTML(
					page,
					paragraph(
						text('Copy + pasting?')
						+ br()
						+ br()
						+ text('Sounds good!Copy + pasting?')
						+ br()
						+ br()
						+ text('Sounds good!'),
					),
				);

				await assertSelection(page, {
					anchorOffset: 12,
					anchorPath: [0, 6, 0],
					focusOffset: 12,
					focusPath: [0, 6, 0],
				});
			}
		});

		test('Copy and paste between sections', async ({ page, browserName }) => {
			await focusEditor(page);
			await page.keyboard.type('Hello world #foobar test #foobar2 when #not');
			await page.keyboard.press('Enter');
			if (newLineMode === 'mixed')
			{
				await page.keyboard.press('Enter');
			}

			await page.keyboard.type('Next #line of #text test #foo');

			if (isRichText)
			{
				await assertHTML(
					page,
					paragraph(
						text('Hello world ')
						+ hashtag('#foobar')
						+ text(' test ')
						+ hashtag('#foobar2')
						+ text(' when ')
						+ hashtag('#not'),
					)
					+ paragraph(
						text('Next ')
						+ hashtag('#line')
						+ text(' of ')
						+ hashtag('#text')
						+ text(' test ')
						+ hashtag('#foo'),
					),
				);

				await assertSelection(page, {
					anchorOffset: 4,
					anchorPath: [1, 5, 0],
					focusOffset: 4,
					focusPath: [1, 5, 0],
				});
			}
			else
			{
				await assertHTML(
					page,
					paragraph(
						text('Hello world ')
						+ hashtag('#foobar')
						+ text(' test ')
						+ hashtag('#foobar2')
						+ text(' when ')
						+ hashtag('#not')
						+ br()
						+ text('Next ')
						+ hashtag('#line')
						+ text(' of ')
						+ hashtag('#text')
						+ text(' test ')
						+ hashtag('#foo'),
					),
				);

				await assertSelection(page, {
					anchorOffset: 4,
					anchorPath: [0, 12, 0],
					focusOffset: 4,
					focusPath: [0, 12, 0],
				});
			}

			// Select all the content
			await selectAll(page);

			if (isRichText)
			{
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [],
						focusOffset: 2,
						focusPath: [],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [0, 0, 0],
						focusOffset: 4,
						focusPath: [1, 5, 0],
					});
				}
			}
			else
			{
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [],
						focusOffset: 1,
						focusPath: [],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [0, 0, 0],
						focusOffset: 4,
						focusPath: [0, 12, 0],
					});
				}
			}

			// Copy all the text
			let clipboard = await copyToClipboard(page);
			await page.keyboard.press('Delete');
			// Paste the content
			await pasteFromClipboard(page, clipboard);

			if (isRichText)
			{
				await assertHTML(
					page,
					paragraph(
						text('Hello world ')
						+ hashtag('#foobar')
						+ text(' test ')
						+ hashtag('#foobar2')
						+ text(' when ')
						+ hashtag('#not'),
					)
					+ paragraph(
						text('Next ')
						+ hashtag('#line')
						+ text(' of ')
						+ hashtag('#text')
						+ text(' test ')
						+ hashtag('#foo'),
					),
				);

				await assertSelection(page, {
					anchorOffset: 4,
					anchorPath: [1, 5, 0],
					focusOffset: 4,
					focusPath: [1, 5, 0],
				});
			}
			else
			{
				await assertHTML(
					page,
					paragraph(
						text('Hello world ')
						+ hashtag('#foobar')
						+ text(' test ')
						+ hashtag('#foobar2')
						+ text(' when ')
						+ hashtag('#not')
						+ br()
						+ text('Next ')
						+ hashtag('#line')
						+ text(' of ')
						+ hashtag('#text')
						+ text(' test ')
						+ hashtag('#foo'),
					),
				);

				await assertSelection(page, {
					anchorOffset: 4,
					anchorPath: [0, 12, 0],
					focusOffset: 4,
					focusPath: [0, 12, 0],
				});
			}

			await moveToPrevWord(page);
			await page.keyboard.down('Shift');
			await page.keyboard.press('ArrowUp');
			await moveToPrevWord(page);
			// Once more for linux on Chromium
			if (IS_LINUX && browserName === 'chromium')
			{
				await moveToPrevWord(page);
			}

			await page.keyboard.up('Shift');

			if (isRichText)
			{
				await assertSelection(page, {
					anchorOffset: 1,
					anchorPath: [1, 5, 0],
					focusOffset: 1,
					focusPath: [0, 2, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 1,
					anchorPath: [0, 12, 0],
					focusOffset: 1,
					focusPath: [0, 2, 0],
				});
			}

			// Copy selected text
			clipboard = await copyToClipboard(page);
			await page.keyboard.press('Delete');
			// Paste the content
			await pasteFromClipboard(page, clipboard);

			if (isRichText)
			{
				await assertHTML(
					page,
					paragraph(
						text('Hello world ')
						+ hashtag('#foobar')
						+ text(' test ')
						+ hashtag('#foobar2')
						+ text(' when ')
						+ hashtag('#not'),
					)
					+ paragraph(
						text('Next ')
						+ hashtag('#line')
						+ text(' of ')
						+ hashtag('#text')
						+ text(' test ')
						+ hashtag('#foo'),
					),
				);

				await assertSelection(page, {
					anchorOffset: 1,
					anchorPath: [1, 5, 0],
					focusOffset: 1,
					focusPath: [1, 5, 0],
				});
			}
			else
			{
				await assertHTML(
					page,
					paragraph(
						text('Hello world ')
						+ hashtag('#foobar')
						+ text(' test ')
						+ hashtag('#foobar2')
						+ text(' when ')
						+ hashtag('#not')
						+ br()
						+ text('Next ')
						+ hashtag('#line')
						+ text(' of ')
						+ hashtag('#text')
						+ text(' test ')
						+ hashtag('#foo'),
					),
				);

				await assertSelection(page, {
					anchorOffset: 1,
					anchorPath: [0, 12, 0],
					focusOffset: 1,
					focusPath: [0, 12, 0],
				});
			}

			// Select all the content
			await selectAll(page);

			if (isRichText)
			{
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [],
						focusOffset: 2,
						focusPath: [],
					});
				}
				else
				{
					if (browserName === 'firefox')
					{
						await assertSelection(page, {
							anchorOffset: 0,
							anchorPath: [0, 0, 0],
							focusOffset: 3,
							focusPath: [1, 5, 0],
						});
					}
					else
					{
						await assertSelection(page, {
							anchorOffset: 0,
							anchorPath: [0, 0, 0],
							focusOffset: 4,
							focusPath: [1, 5, 0],
						});
					}
				}
			}
			else
			{
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [],
						focusOffset: 1,
						focusPath: [],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [0, 0, 0],
						focusOffset: 4,
						focusPath: [0, 12, 0],
					});
				}
			}

			await page.keyboard.press('Delete');
			await assertHTML(page, paragraph());
			await assertSelection(page, {
				anchorOffset: 0,
				anchorPath: [0],
				focusOffset: 0,
				focusPath: [0],
			});
		});
	});
});

test.describe('Copy & Paste', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Copy and paste an inline element into a leaf node', async ({ page }) => {
		await focusEditor(page);
		await page.keyboard.type('Hello');
		await selectAll(page);

		await toggleLink(page, 'https://example.com');

		await page.keyboard.press('ArrowRight');
		await page.keyboard.press('Space');
		await page.keyboard.type('World');

		await selectAll(page);

		const clipboard = await copyToClipboard(page);

		await page.keyboard.press('ArrowRight');

		await pasteFromClipboard(page, clipboard);

		await assertHTML(
			page,
			paragraph(
				link(text('Hello'))
				+ text(' World')
				+ link(text('Hello'))
				+ text(' World'),
			),
		);
	});
});

test.describe('HTML Copy & Paste', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Copy and paste multi line html with extra newlines', async ({ page }) => {
		await focusEditor(page);
		await pasteFromClipboard(page, {
			'text/html':
				'<p>Hello\n</p>\n\n<p>\n\nWorld\n\n</p>\n\n<p>Hello\n\n   World   \n\n!\n\n</p><p>Hello <b>World</b> <i>!</i></p>',
		});

		await assertHTML(
			page,
			paragraph('Hello')
			+ paragraph('World')
			+ paragraph('Hello World !')
			+ paragraph(text('Hello ') + bold('World') + text(' ') + italic('!')),
		);
	});

	test('Copy and paste a code block with BR', async ({ page }) => {
		await focusEditor(page);

		const clipboard = {
			'text/html': '<meta charset=\'utf-8\'><p class="x1f6kntn x1fcty0u x16h55sf x12nagc xdj266r" dir="ltr"><span>Code block</span></p><code class="x1f6kntn x1fcty0u x16h55sf x1xmf6yo x1e56ztr x1q8sqs3 xeq4nuv x1lliihq xz9dl7a xn6708d xsag5q8 x1ye3gou" spellcheck="false" data-language="javascript" data-highlight-language="javascript"><span class="xuc5kci">function</span><span> </span><span class="xu88d7e">foo</span><span class="x1noocy9">(</span><span class="x1noocy9">)</span><span> </span><span class="x1noocy9">{</span><br><span>  </span><span class="xuc5kci">return</span><span> </span><span class="x180nigk">\'Hey there\'</span><span class="x1noocy9">;</span><br><span class="x1noocy9">}</span></code><p class="x1f6kntn x1fcty0u x16h55sf x12nagc xdj266r" dir="ltr"><span>--end--</span></p>',
		};

		await pasteFromClipboard(page, clipboard);

		await assertHTML(
			page,
			paragraph('Code block')
			+ code(
				codeToken('function')
				+ codeToken(' foo', 'word')
				+ codeToken('(', 'parentheses')
				+ codeToken(')', 'parentheses')
				+ text(' ')
				+ codeToken('{', 'brace')
				+ br()
				+ codeToken('return')
				+ text(' ')
				+ codeToken('\'Hey there\'', 'string')
				+ codeToken(';', 'semicolon')
				+ br()
				+ codeToken('}', 'brace'),
			)
			+ paragraph('--end--'),
		);
	});
});

test.describe('Text Copy & Paste', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Copy and paste multi line text', async ({ page }) => {
		await focusEditor(page);
		await pasteFromClipboard(page, {
			'text/plain': '111\n222\n333',
		});

		await page.pause();

		await assertHTML(
			page,
			paragraph(text('111') + br() + text('222') + br() + text('333')),
		);
	});

	test('Copy and paste multi line text with extra newlines', async ({ page }) => {
		await focusEditor(page);
		await pasteFromClipboard(page, {
			'text/plain': '111\n\n222\n\n333',
		});

		await page.pause();

		await assertHTML(
			page,
			paragraph('111') + paragraph('222') + paragraph('333'),
		);
	});

	test('Copy and paste multi line text with a mix of newlines', async ({ page }) => {
		await focusEditor(page);
		await pasteFromClipboard(page, {
			'text/plain': '\none\ntwo\n\nthree\n\n\nfour',
		});

		await page.pause();

		await assertHTML(
			page,
			paragraph(br() + text('one') + br() + text('two')) + paragraph('three') + paragraph(br() + text('four')),
		);
	});

	test('Copy and paste multi line text with a mix of Windows newlines', async ({ page }) => {
		await focusEditor(page);
		await pasteFromClipboard(page, {
			'text/plain': '\r\none\r\ntwo\r\n\r\nthree\r\n\r\n\r\nfour',
		});

		await page.pause();

		await assertHTML(
			page,
			paragraph(br() + text('one') + br() + text('two')) + paragraph('three') + paragraph(br() + text('four')),
		);
	});
});
