const {
	test,
	focusEditor,
	initializeTest,
	assertSelection,
	IS_WINDOWS,
} = require('./utils');

const {
	toggleBold,
	selectCharacters,
	moveToEditorEnd,
	moveToLineBeginning,
	moveToLineEnd,
	moveToEditorBeginning,
	moveToNextWord,
	moveToPrevWord,
	moveToParagraphBeginning,
	moveToParagraphEnd,
} = require('./keyboard');

async function typeParagraphs(page, newLineMode)
{
	await focusEditor(page);
	await page.keyboard.type(
		'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
	);
	await page.keyboard.press('Enter');
	if (newLineMode === 'mixed')
	{
		await page.keyboard.press('Enter');
	}

	await page.keyboard.type(
		'It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. ',
	);
	await page.keyboard.press('Enter');
	if (newLineMode === 'mixed')
	{
		await page.keyboard.press('Enter');
	}

	await page.keyboard.type(
		'It was popularised in the 1960s with the release of Letraset sheets containing lorem ipsum passages.',
	);
}

['line-break', 'paragraph', 'mixed'].forEach((newLineMode) => {
	test.describe(`Keyboard Navigation (${newLineMode})`, () => {
		const editorOptions = { newLineMode };
		test.beforeEach(async ({ page }) => initializeTest({ page, editorOptions }));

		const isRichText = newLineMode === 'paragraph' || newLineMode === 'mixed';

		test('can type several paragraphs', async ({ page }) => {
			await typeParagraphs(page, newLineMode);
			if (isRichText)
			{
				await assertSelection(page, {
					anchorOffset: 100,
					anchorPath: [2, 0, 0],
					focusOffset: 100,
					focusPath: [2, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 100,
					anchorPath: [0, 4, 0],
					focusOffset: 100,
					focusPath: [0, 4, 0],
				});
			}
		});

		test(
			'can move to the beginning of the current line, then back to the end of the current line',
			async ({ page }) => {
				await typeParagraphs(page, newLineMode);
				await moveToLineBeginning(page);
				if (isRichText)
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [2, 0, 0],
						focusOffset: 0,
						focusPath: [2, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 0,
						anchorPath: [0, 4, 0],
						focusOffset: 0,
						focusPath: [0, 4, 0],
					});
				}
				await moveToLineEnd(page);
				if (isRichText)
				{
					await assertSelection(page, {
						anchorOffset: 100,
						anchorPath: [2, 0, 0],
						focusOffset: 100,
						focusPath: [2, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 100,
						anchorPath: [0, 4, 0],
						focusOffset: 100,
						focusPath: [0, 4, 0],
					});
				}
			},
		);

		test('can move to the top of the editor', async ({ page }) => {
			await typeParagraphs(page, newLineMode);
			await moveToEditorBeginning(page);
			await assertSelection(page, {
				anchorOffset: 0,
				anchorPath: [0, 0, 0],
				focusOffset: 0,
				focusPath: [0, 0, 0],
			});
		});

		test('can move one word to the right', async ({ page, browserName }) => {
			await typeParagraphs(page, newLineMode);
			await moveToEditorBeginning(page);
			await moveToNextWord(page);
			if (browserName === 'firefox')
			{
				if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 6,
						anchorPath: [0, 0, 0],
						focusOffset: 6,
						focusPath: [0, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 5,
						anchorPath: [0, 0, 0],
						focusOffset: 5,
						focusPath: [0, 0, 0],
					});
				}
			}
			else if (!IS_WINDOWS)
			{
				await assertSelection(page, {
					anchorOffset: 5,
					anchorPath: [0, 0, 0],
					focusOffset: 5,
					focusPath: [0, 0, 0],
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
		});

		test('can move to the beginning of the previous word', async ({
			page,
			browserName,
		}) => {
			await typeParagraphs(page, newLineMode);
			await moveToPrevWord(page);
			// Chrome stops words on punctuation, so we need to trigger
			// the left arrow key one more time.
			if (browserName === 'chromium')
			{
				await moveToPrevWord(page);
			}

			if (isRichText)
			{
				await assertSelection(page, {
					anchorOffset: 91,
					anchorPath: [2, 0, 0],
					focusOffset: 91,
					focusPath: [2, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 91,
					anchorPath: [0, 4, 0],
					focusOffset: 91,
					focusPath: [0, 4, 0],
				});
			}
			await moveToPrevWord(page);
			if (isRichText)
			{
				await assertSelection(page, {
					anchorOffset: 85,
					anchorPath: [2, 0, 0],
					focusOffset: 85,
					focusPath: [2, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 85,
					anchorPath: [0, 4, 0],
					focusOffset: 85,
					focusPath: [0, 4, 0],
				});
			}
		});

		test('can move to the bottom of the editor', async ({ page }) => {
			await typeParagraphs(page, newLineMode);
			await moveToEditorBeginning(page);
			await moveToEditorEnd(page);
			if (isRichText)
			{
				await assertSelection(page, {
					anchorOffset: 100,
					anchorPath: [2, 0, 0],
					focusOffset: 100,
					focusPath: [2, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 100,
					anchorPath: [0, 4, 0],
					focusOffset: 100,
					focusPath: [0, 4, 0],
				});
			}
		});

		test('can move to the beginning of the current paragraph', async ({
			page,
		}) => {
			await typeParagraphs(page, newLineMode);
			await moveToParagraphBeginning(page);
			if (isRichText)
			{
				await assertSelection(page, {
					anchorOffset: 0,
					anchorPath: [2, 0, 0],
					focusOffset: 0,
					focusPath: [2, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 0,
					anchorPath: [0, 4, 0],
					focusOffset: 0,
					focusPath: [0, 4, 0],
				});
			}
		});

		test('can move to the top of the editor, then to the bottom of the current paragraph', async ({ page }) => {
			await typeParagraphs(page, newLineMode);
			await moveToEditorBeginning(page);
			await moveToParagraphEnd(page);
			await assertSelection(page, {
				// Due to text rendering it can be in this range of offsets
				anchorOffset: [65, 74],

				anchorPath: [0, 0, 0],
				// Due to text rendering it can be in this range of offsets
				focusOffset: [65, 74],

				focusPath: [0, 0, 0],
			});
		});

		test('can navigate through the plain text word by word', async ({ page, browserName }) => {
			await focusEditor(page);
			// type sample text
			await page.keyboard.type('  123 abc 456  def  ');
			await assertSelection(page, {
				anchorOffset: 20,
				anchorPath: [0, 0, 0],
				focusOffset: 20,
				focusPath: [0, 0, 0],
			});
			// navigate through the text
			// 1 left
			await moveToPrevWord(page);
			await assertSelection(page, {
				anchorOffset: 15,
				anchorPath: [0, 0, 0],
				focusOffset: 15,
				focusPath: [0, 0, 0],
			});
			// 2 left
			await moveToPrevWord(page);
			await assertSelection(page, {
				anchorOffset: 10,
				anchorPath: [0, 0, 0],
				focusOffset: 10,
				focusPath: [0, 0, 0],
			});
			// 3 left
			await moveToPrevWord(page);
			await assertSelection(page, {
				anchorOffset: 6,
				anchorPath: [0, 0, 0],
				focusOffset: 6,
				focusPath: [0, 0, 0],
			});
			// 4 left
			await moveToPrevWord(page);
			await assertSelection(page, {
				anchorOffset: 2,
				anchorPath: [0, 0, 0],
				focusOffset: 2,
				focusPath: [0, 0, 0],
			});
			// 5 left
			await moveToPrevWord(page);
			await assertSelection(page, {
				anchorOffset: 0,
				anchorPath: [0, 0, 0],
				focusOffset: 0,
				focusPath: [0, 0, 0],
			});
			// 1 right
			await moveToNextWord(page);
			if (browserName === 'firefox')
			{
				if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 2,
						anchorPath: [0, 0, 0],
						focusOffset: 2,
						focusPath: [0, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 5,
						anchorPath: [0, 0, 0],
						focusOffset: 5,
						focusPath: [0, 0, 0],
					});
				}
			}
			else if (!IS_WINDOWS)
			{
				await assertSelection(page, {
					anchorOffset: 5,
					anchorPath: [0, 0, 0],
					focusOffset: 5,
					focusPath: [0, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 2,
					anchorPath: [0, 0, 0],
					focusOffset: 2,
					focusPath: [0, 0, 0],
				});
			}
			// 2 right
			await moveToNextWord(page);
			if (browserName === 'firefox')
			{
				if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 6,
						anchorPath: [0, 0, 0],
						focusOffset: 6,
						focusPath: [0, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 9,
						anchorPath: [0, 0, 0],
						focusOffset: 9,
						focusPath: [0, 0, 0],
					});
				}
			}
			else if (!IS_WINDOWS)
			{
				await assertSelection(page, {
					anchorOffset: 9,
					anchorPath: [0, 0, 0],
					focusOffset: 9,
					focusPath: [0, 0, 0],
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
			// 3 right
			await moveToNextWord(page);
			if (browserName === 'firefox')
			{
				if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 10,
						anchorPath: [0, 0, 0],
						focusOffset: 10,
						focusPath: [0, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 13,
						anchorPath: [0, 0, 0],
						focusOffset: 13,
						focusPath: [0, 0, 0],
					});
				}
			}
			else if (!IS_WINDOWS)
			{
				await assertSelection(page, {
					anchorOffset: 13,
					anchorPath: [0, 0, 0],
					focusOffset: 13,
					focusPath: [0, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 10,
					anchorPath: [0, 0, 0],
					focusOffset: 10,
					focusPath: [0, 0, 0],
				});
			}
			// 4 right
			await moveToNextWord(page);
			if (browserName === 'firefox')
			{
				if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 15,
						anchorPath: [0, 0, 0],
						focusOffset: 15,
						focusPath: [0, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 18,
						anchorPath: [0, 0, 0],
						focusOffset: 18,
						focusPath: [0, 0, 0],
					});
				}
			}
			else if (!IS_WINDOWS)
			{
				await assertSelection(page, {
					anchorOffset: 18,
					anchorPath: [0, 0, 0],
					focusOffset: 18,
					focusPath: [0, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 15,
					anchorPath: [0, 0, 0],
					focusOffset: 15,
					focusPath: [0, 0, 0],
				});
			}
			// 5 right
			await moveToNextWord(page);
			if (!IS_WINDOWS || browserName === 'firefox')
			{
				await assertSelection(page, {
					anchorOffset: 20,
					anchorPath: [0, 0, 0],
					focusOffset: 20,
					focusPath: [0, 0, 0],
				});
			}
			else
			{
				await assertSelection(page, {
					anchorOffset: 18,
					anchorPath: [0, 0, 0],
					focusOffset: 18,
					focusPath: [0, 0, 0],
				});

				// 6 right
				await moveToNextWord(page);
				await assertSelection(page, {
					anchorOffset: 20,
					anchorPath: [0, 0, 0],
					focusOffset: 20,
					focusPath: [0, 0, 0],
				});
			}
		});

		test('can navigate through the formatted text word by word', async ({ page, browserName }) => {
			await focusEditor(page);
			// type sample text
			await page.keyboard.type('  123 abc 456  def  ');
			await assertSelection(page, {
				anchorOffset: 20,
				anchorPath: [0, 0, 0],
				focusOffset: 20,
				focusPath: [0, 0, 0],
			});
			// This test relies on rich text formatting
			if (isRichText)
			{
				// select "de" and make it bold
				await moveToPrevWord(page);
				await selectCharacters(page, 'right', 2);
				await toggleBold(page);
				// select "ab" and make it bold
				await moveToPrevWord(page);
				await moveToPrevWord(page);
				await moveToPrevWord(page);
				await selectCharacters(page, 'right', 2);
				await toggleBold(page);
				await moveToLineEnd(page);
				await assertSelection(page, {
					anchorOffset: 3,
					anchorPath: [0, 4, 0],
					focusOffset: 3,
					focusPath: [0, 4, 0],
				});

				// navigate through the text
				// 1 left
				await moveToPrevWord(page);
				if (browserName === 'webkit')
				{
					await assertSelection(page, {
						anchorOffset: 7,
						anchorPath: [0, 2, 0],
						focusOffset: 7,
						focusPath: [0, 2, 0],
					});
				}
				else if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 7,
						anchorPath: [0, 2, 0],
						focusOffset: 7,
						focusPath: [0, 2, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 7,
						anchorPath: [0, 2, 0],
						focusOffset: 7,
						focusPath: [0, 2, 0],
					});
				}
				// 2 left
				await moveToPrevWord(page);
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 2,
						anchorPath: [0, 2, 0],
						focusOffset: 2,
						focusPath: [0, 2, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 2,
						anchorPath: [0, 2, 0],
						focusOffset: 2,
						focusPath: [0, 2, 0],
					});
				}
				// 3 left
				await moveToPrevWord(page);
				if (browserName === 'firefox')
				{
					await assertSelection(page, {
						anchorOffset: 6,
						anchorPath: [0, 0, 0],
						focusOffset: 6,
						focusPath: [0, 0, 0],
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
				// 4 left
				await moveToPrevWord(page);
				await assertSelection(page, {
					anchorOffset: 2,
					anchorPath: [0, 0, 0],
					focusOffset: 2,
					focusPath: [0, 0, 0],
				});
				// 5 left
				await moveToPrevWord(page);
				await assertSelection(page, {
					anchorOffset: 0,
					anchorPath: [0, 0, 0],
					focusOffset: 0,
					focusPath: [0, 0, 0],
				});
				// 1 right
				await moveToNextWord(page);
				if (IS_WINDOWS && browserName === 'chromium')
				{
					await assertSelection(page, {
						anchorOffset: 2,
						anchorPath: [0, 0, 0],
						focusOffset: 2,
						focusPath: [0, 0, 0],
					});
				}
				else if (browserName === 'firefox' && IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 2,
						anchorPath: [0, 0, 0],
						focusOffset: 2,
						focusPath: [0, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 5,
						anchorPath: [0, 0, 0],
						focusOffset: 5,
						focusPath: [0, 0, 0],
					});
				}
				// 2 right
				await moveToNextWord(page);
				if (browserName === 'webkit')
				{
					await assertSelection(page, {
						anchorOffset: 1,
						anchorPath: [0, 2, 0],
						focusOffset: 1,
						focusPath: [0, 2, 0],
					});
				}
				else if (browserName === 'firefox')
				{
					if (IS_WINDOWS)
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
							anchorOffset: 1,
							anchorPath: [0, 2, 0],
							focusOffset: 1,
							focusPath: [0, 2, 0],
						});
					}
				}
				else if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 6,
						anchorPath: [0, 0, 0],
						focusOffset: 6,
						focusPath: [0, 0, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 1,
						anchorPath: [0, 2, 0],
						focusOffset: 1,
						focusPath: [0, 2, 0],
					});
				}

				// 3 right
				await moveToNextWord(page);
				if (browserName === 'webkit')
				{
					await assertSelection(page, {
						anchorOffset: 5,
						anchorPath: [0, 2, 0],
						focusOffset: 5,
						focusPath: [0, 2, 0],
					});
				}
				else if (browserName === 'firefox')
				{
					if (IS_WINDOWS)
					{
						await assertSelection(page, {
							anchorOffset: 2,
							anchorPath: [0, 2, 0],
							focusOffset: 2,
							focusPath: [0, 2, 0],
						});
					}
					else
					{
						await assertSelection(page, {
							anchorOffset: 5,
							anchorPath: [0, 2, 0],
							focusOffset: 5,
							focusPath: [0, 2, 0],
						});
					}
				}
				else if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 2,
						anchorPath: [0, 2, 0],
						focusOffset: 2,
						focusPath: [0, 2, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 5,
						anchorPath: [0, 2, 0],
						focusOffset: 5,
						focusPath: [0, 2, 0],
					});
				}
				// 4 right
				await moveToNextWord(page);
				if (browserName === 'webkit')
				{
					await assertSelection(page, {
						anchorOffset: 1,
						anchorPath: [0, 4, 0],
						focusOffset: 1,
						focusPath: [0, 4, 0],
					});
				}
				else if (browserName === 'firefox')
				{
					if (IS_WINDOWS)
					{
						await assertSelection(page, {
							anchorOffset: 0,
							anchorPath: [0, 3, 0],
							focusOffset: 0,
							focusPath: [0, 3, 0],
						});
					}
					else
					{
						await assertSelection(page, {
							anchorOffset: 1,
							anchorPath: [0, 4, 0],
							focusOffset: 1,
							focusPath: [0, 4, 0],
						});
					}
				}
				else if (IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 7,
						anchorPath: [0, 2, 0],
						focusOffset: 7,
						focusPath: [0, 2, 0],
					});
				}
				else
				{
					await assertSelection(page, {
						anchorOffset: 1,
						anchorPath: [0, 4, 0],
						focusOffset: 1,
						focusPath: [0, 4, 0],
					});
				}
				// 5 right
				await moveToNextWord(page);
				if (browserName === 'webkit')
				{
					await assertSelection(page, {
						anchorOffset: 3,
						anchorPath: [0, 4, 0],
						focusOffset: 3,
						focusPath: [0, 4, 0],
					});
				}
				else if (!IS_WINDOWS || browserName === 'firefox')
				{
					if (browserName === 'firefox')
					{
						if (IS_WINDOWS)
						{
							await assertSelection(page, {
								anchorOffset: 3,
								anchorPath: [0, 4, 0],
								focusOffset: 3,
								focusPath: [0, 4, 0],
							});
						}
						else
						{
							await assertSelection(page, {
								anchorOffset: 3,
								anchorPath: [0, 4, 0],
								focusOffset: 3,
								focusPath: [0, 4, 0],
							});
						}
					}
					else
					{
						await assertSelection(page, {
							anchorOffset: 3,
							anchorPath: [0, 4, 0],
							focusOffset: 3,
							focusPath: [0, 4, 0],
						});
					}
				}
				else if (!IS_WINDOWS)
				{
					await assertSelection(page, {
						anchorOffset: 1,
						anchorPath: [0, 4, 0],
						focusOffset: 1,
						focusPath: [0, 4, 0],
					});
					// 6 right
					await moveToNextWord(page);
					await assertSelection(page, {
						anchorOffset: 3,
						anchorPath: [0, 4, 0],
						focusOffset: 3,
						focusPath: [0, 4, 0],
					});
				}
				else
				{
					// 6 right
					await moveToNextWord(page);
					await assertSelection(page, {
						anchorOffset: 3,
						anchorPath: [0, 4, 0],
						focusOffset: 3,
						focusPath: [0, 4, 0],
					});
				}
			}
		});
	});
});
