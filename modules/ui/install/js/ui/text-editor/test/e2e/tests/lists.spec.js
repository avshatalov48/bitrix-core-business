const {
	test,
	focusEditor,
	initializeTest,
	assertHTML,
	clickToolbarButton,
	toggleLink,
} = require('./utils');

const { selectCharacters, moveToEditorEnd, moveLeft, moveToParagraphEnd, selectAll } = require('./keyboard');
const { paragraph, ul, ol, li, br, text, link } = require('./html');

async function toggleBulletList(page)
{
	await clickToolbarButton(page, 'bulleted-list');
}

async function toggleNumberedList(page)
{
	await clickToolbarButton(page, 'numbered-list');
}

test.describe.parallel('Nested List', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));

	test('Can create a list', async ({ page }) => {
		await focusEditor(page);

		const itemText = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam venenatis risus ac cursus efficitur. Cras efficitur magna odio, lacinia posuere mauris placerat in. Etiam eu congue nisl. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Nulla vulputate justo id eros convallis, vel pellentesque orci hendrerit. Pellentesque accumsan molestie eros, vitae tempor nisl semper sit amet. Sed vulputate leo dolor, et bibendum quam feugiat eget. Praesent vestibulum libero sed enim ornare, in consequat dui posuere. Maecenas ornare vestibulum felis, non elementum urna imperdiet sit amet.';
		await page.keyboard.type(itemText);
		await toggleBulletList(page);

		await moveToEditorEnd(page);
		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');

		await assertHTML(
			page,
			ul(li(itemText, 1)) + paragraph() + paragraph(),
		);
	});

	test('Can indent/outdent mutliple list nodes in a list with multiple levels of indentation', async ({ page }) => {
		await focusEditor(page);

		await toggleBulletList(page);

		await assertHTML(
			page,
			ul(li(br(), 1)) + paragraph(),
		);

		await page.keyboard.type('Hello');
		await page.keyboard.press('Enter');
		await page.keyboard.type('world');

		await assertHTML(
			page,
			ul(li('Hello', 1) + li('world', 2)) + paragraph(),
		);
	});

	test('Can create a list and then toggle it back to original state.', async ({ page }) => {
		await focusEditor(page);

		await assertHTML(
			page,
			paragraph(),
		);

		await page.keyboard.type('Hello');

		await toggleBulletList(page);

		await assertHTML(
			page,
			ul(li('Hello', 1)) + paragraph(),
		);

		await toggleBulletList(page);

		await assertHTML(
			page,
			paragraph('Hello') + paragraph(),
		);

		// await page.keyboard.press('Enter');
		// await page.keyboard.type('from');
		// await page.keyboard.press('Enter');
		// await page.keyboard.type('the');
		// await page.keyboard.press('Enter');
		// await page.keyboard.type('other');
		// await page.keyboard.press('Enter');
		// await page.keyboard.type('side');
		//
		// await assertHTML(
		// 	page,
		// 	`
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">Hello</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">from</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">the</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">other</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">side</span>
		// 		</p>
		// 	`,
		// );
		//
		// await selectAll(page);
		//
		// await toggleBulletList(page);
		//
		// await assertHTML(
		// 	page,
		// 	'<ul class="ui-text-editor__ul"><li class="ui-text-editor__listItem" value="1"><span data-lexical-text="true">Hello</span></li><li class="ui-text-editor__listItem" value="2"><span data-lexical-text="true">from</span></li><li class="ui-text-editor__listItem" value="3"><span data-lexical-text="true">the</span></li><li class="ui-text-editor__listItem" value="4"><span data-lexical-text="true">other</span></li><li class="ui-text-editor__listItem" value="5"><span data-lexical-text="true">side</span></li></ul>',
		// );
		//
		// await toggleBulletList(page);
		//
		// await assertHTML(
		// 	page,
		// 	`
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">Hello</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">from</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">the</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">other</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph"
		// 			dir="ltr"
		// 		>
		// 			<span data-lexical-text="true">side</span>
		// 		</p>
		// 	`,
		// );
		//
		// // works for an indented list
		//
		// await toggleBulletList(page);
		//
		// await clickIndentButton(page, 3);
		//
		// await assertHTML(
		// 	page,
		// 	'<ul class="ui-text-editor__ul"><li class="ui-text-editor__listItem PlaygroundEditorTheme__nestedListItem" value="1"><ul class="ui-text-editor__ul"><li class="ui-text-editor__listItem PlaygroundEditorTheme__nestedListItem" value="1"><ul class="ui-text-editor__ul"><li class="ui-text-editor__listItem PlaygroundEditorTheme__nestedListItem" value="1"><ul class="ui-text-editor__ul"><li class="ui-text-editor__listItem" value="1"><span data-lexical-text="true">Hello</span></li><li class="ui-text-editor__listItem" value="2"><span data-lexical-text="true">from</span></li><li class="ui-text-editor__listItem" value="3"><span data-lexical-text="true">the</span></li><li class="ui-text-editor__listItem" value="4"><span data-lexical-text="true">other</span></li><li class="ui-text-editor__listItem" value="5"><span data-lexical-text="true">side</span></li></ul></li></ul></li></ul></li></ul>',
		// );
		//
		// await toggleBulletList(page);
		//
		// await assertHTML(
		// 	page,
		// 	`
		// 		<p
		// 			class="ui-text-editor__paragraph PlaygroundEditorTheme__indent PlaygroundEditorTheme__ltr"
		// 			dir="ltr"
		// 			style="padding-inline-start: calc(120px)"
		// 		>
		// 			<span data-lexical-text="true">Hello</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph PlaygroundEditorTheme__indent PlaygroundEditorTheme__ltr"
		// 			dir="ltr"
		// 			style="padding-inline-start: calc(120px)"
		// 		>
		// 			<span data-lexical-text="true">from</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph PlaygroundEditorTheme__indent PlaygroundEditorTheme__ltr"
		// 			dir="ltr"
		// 			style="padding-inline-start: calc(120px)"
		// 		>
		// 			<span data-lexical-text="true">the</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph PlaygroundEditorTheme__indent PlaygroundEditorTheme__ltr"
		// 			dir="ltr"
		// 			style="padding-inline-start: calc(120px)"
		// 		>
		// 			<span data-lexical-text="true">other</span>
		// 		</p>
		// 		<p
		// 			class="ui-text-editor__paragraph PlaygroundEditorTheme__indent PlaygroundEditorTheme__ltr"
		// 			dir="ltr"
		// 			style="padding-inline-start: calc(120px)"
		// 		>
		// 			<span data-lexical-text="true">side</span>
		// 		</p>
		// 	`,
		// );
	});

	test('Can create a list containing inline blocks and then toggle it back to original state.', async ({ page }) => {
		await focusEditor(page);

		await assertHTML(
			page,
			paragraph(),
		);

		await page.keyboard.type('One two three');

		await assertHTML(
			page,
			paragraph('One two three'),
		);

		await moveLeft(page, 6);
		await selectCharacters(page, 'left', 3);

		// link
		await toggleLink(page, 'https://yandex.ru');

		await assertHTML(
			page,
			paragraph(text('One ') + link('two', 'https://yandex.ru') + text(' three')),
		);

		// move to end of paragraph to close the floating link bar
		await moveToParagraphEnd(page);

		await toggleBulletList(page);

		await assertHTML(
			page,
			ul(li(text('One ') + link('two', 'https://yandex.ru') + text(' three'), 1)) + paragraph(),
		);

		await toggleBulletList(page);

		await assertHTML(
			page,
			paragraph(text('One ') + link('two', 'https://yandex.ru') + text(' three')) + paragraph(),
		);
	});

	test('Can create mutliple bullet lists and then toggle off the list.', async ({ page }) => {
		await focusEditor(page);

		await assertHTML(page, paragraph());

		await page.keyboard.type('Hello');

		await toggleBulletList(page);

		await page.keyboard.press('Enter');
		await page.keyboard.type('from');

		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');

		await page.keyboard.type('the');

		await page.keyboard.press('Enter');
		await page.keyboard.press('Enter');

		await page.keyboard.type('other');

		await toggleBulletList(page);

		await page.keyboard.press('Enter');
		await page.keyboard.type('side');

		await assertHTML(
			page,
			ul(li('Hello', 1) + li('from', 2))
			+ paragraph(br() + text('the'))
			+ ul(li('other', 1) + li('side', 2))
			+ paragraph(),
		);

		await selectAll(page);

		await toggleBulletList(page);

		await assertHTML(
			page,
			paragraph('Hello')
			+ paragraph('from')
			+ paragraph(br() + text('the'))
			+ paragraph('other')
			+ paragraph('side')
			+ paragraph(),
		);
	});

	test('Can create an unordered list and convert it to an ordered list ', async ({ page }) => {
		await focusEditor(page);

		await toggleBulletList(page);

		await assertHTML(
			page,
			ul(li(br(), 1)) + paragraph(),
		);

		await toggleNumberedList(page);

		await assertHTML(
			page,
			ol(li(br(), 1)) + paragraph(),
		);

		await toggleBulletList(page);

		await assertHTML(
			page,
			ul(li(br(), 1)) + paragraph(),
		);
	});

	test('Can create a single item unordered list with text and convert it to an ordered list ', async ({ page }) => {
		await focusEditor(page);

		await toggleBulletList(page);

		await page.keyboard.type('Hello');

		await toggleNumberedList(page);

		await assertHTML(
			page,
			ol(li('Hello', 1)) + paragraph(),
		);

		await toggleBulletList(page);

		await assertHTML(
			page,
			ul(li('Hello', 1)) + paragraph(),
		);
	});

	test('Can create a multi-line unordered list and convert it to an ordered list', async ({ page }) => {
		await focusEditor(page);

		await toggleBulletList(page);

		await page.keyboard.type('Hello');
		await page.keyboard.press('Enter');
		await page.keyboard.type('from');
		await page.keyboard.press('Enter');
		await page.keyboard.type('the');
		await page.keyboard.press('Enter');
		await page.keyboard.type('other');
		await page.keyboard.press('Enter');
		await page.keyboard.type('side');

		await assertHTML(
			page,
			ul(
				li('Hello', 1)
				+ li('from', 2)
				+ li('the', 3)
				+ li('other', 4)
				+ li('side', 5),
			)
			+ paragraph(),
		);

		await toggleNumberedList(page);

		await assertHTML(
			page,
			ol(
				li('Hello', 1)
				+ li('from', 2)
				+ li('the', 3)
				+ li('other', 4)
				+ li('side', 5),
			)
			+ paragraph(),
		);

		await toggleBulletList(page);

		await assertHTML(
			page,
			ul(
				li('Hello', 1)
				+ li('from', 2)
				+ li('the', 3)
				+ li('other', 4)
				+ li('side', 5),
			)
			+ paragraph(),
		);
	});
});
