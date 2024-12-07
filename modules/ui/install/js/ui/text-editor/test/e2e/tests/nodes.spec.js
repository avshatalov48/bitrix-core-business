const { test, expect } = require('@playwright/test');
const { focusEditor, initializeTest, assertHTML, IS_MAC } = require('./utils');
const {
	paragraph,
	text,
	bold,
	italic,
	br,
	quote,
	mention,
	code,
	keyword,
	parentheses,
	brace,
	word,
	comment,
	string,
	semicolon,
	table,
	tr,
	th,
	td,
	spoiler,
	image,
	ul,
	li,
	strike,
	video,
} = require('./html');

test.describe('Nodes', () => {
	test.beforeEach(async ({ page }) => initializeTest({ page }));
	test('Blockquote', async ({ page }) => {
		await focusEditor(page);
		await page.evaluate(() => {
			window.textEditor.setText(
				'[QUOTE][USER=1]Matthew Mcconaughey[/USER] wrote:\n'
				+ 'Hi! [b]What\'s up?[/b]\n'
				+ '[/QUOTE]',
			);
		});

		await assertHTML(
			page,
			quote(
				paragraph(
					mention('Matthew Mcconaughey')
					+ text(' wrote:')
					+ br()
					+ text('Hi! ')
					+ bold('What\'s up?'),
				),
			) + paragraph(),
		);

		await page.evaluate(() => {
			window.textEditor.setText(
				'[QUOTE][USER=1]Matthew Mcconaughey[/USER] wrote:\n'
				+ 'Hi! [b]What\'s up?[/b]\n'
				+ '[QUOTE][USER=56]Woody Harrelson[/USER] wrote:\n'
				+ 'Hi! [i]What\'s up?[/i][/QUOTE]\n'
				+ '[/QUOTE]',
			);
		});

		await assertHTML(
			page,
			quote(
				paragraph(
					mention('Matthew Mcconaughey')
					+ text(' wrote:')
					+ br()
					+ text('Hi! ')
					+ bold('What\'s up?'),
				)
				+ quote(
					paragraph(
						mention('Woody Harrelson')
						+ text(' wrote:')
						+ br()
						+ text('Hi! ')
						+ italic('What\'s up?'),
					),
				),
			) + paragraph(),
		);

		if (IS_MAC)
		{
			await expect(page).toHaveScreenshot('blockquote.png');
		}
	});

	test('Code Block', async ({ page }) => {
		await focusEditor(page);
		await page.evaluate(() => {
			window.textEditor.setText(
				'[code]\n'
				+ 'function dooFoo()\n'
				+ '{\n'
				+ '\tif (true)\n'
				+ '\t{\n'
				+ '\t\t// just echo\n'
				+ '\t\techo \'Welcome to my house!\'; # oh my\n'
				+ '\t}\n'
				+ '\n'
				+ '\treturn false;\n'
				+ '}\n'
				+ '[/code]',
			);
		});

		await assertHTML(
			page,
			code(
				keyword('function') + word(' dooFoo', 'word') + parentheses('(') + parentheses(')') + br()
				+ brace('{') + br()
				+ text('\t')
					+ keyword('if') + text(' ') + parentheses('(') + keyword('true') + parentheses(')') + br()
				+ text('\t') + brace('{') + br()
				+ text('\t') + text('\t') + comment('// just echo') + br()
				+ text('\t') + text('\t')
					+ keyword('echo') + text(' ') + string('\'Welcome to my house!\'') + semicolon() + text(' ') + comment('# oh my') + br()
				+ text('\t') + brace('}') + br() + br()
				+ text('\t') + keyword('return') + text(' ') + keyword('false') + semicolon() + br()
				+ brace('}'),

			) + paragraph(),
			{ preserveTab: true },
		);

		if (IS_MAC)
		{
			await expect(page).toHaveScreenshot('code-block.png');
		}
	});

	test('Table', async ({ page }) => {
		await focusEditor(page);
		await page.evaluate(() => {
			window.textEditor.setText(
				'[table]\n'
				+ '[tr]\n'
				+ '[th]Name[/th]\n'
				+ '[th]Country[/th]\n'
				+ '[th]Age[/th]\n'
				+ '[/tr]\n'
				+ '[tr]\n'
				+ '[td]Paul[/td]\n'
				+ '[td]UK[/td]\n'
				+ '[td]48[/td]\n'
				+ '[/tr]\n'
				+ '[tr]\n'
				+ '[td]Louisa[/td]\n'
				+ '[td]Germany[/td]\n'
				+ '[td]24[/td]\n'
				+ '[/tr]\n'
				+ '[/table]',
			);
		});

		await assertHTML(
			page,
			table(
				tr(
					th(paragraph('Name')) + th(paragraph('Country')) + th(paragraph('Age')),
				)
				+ tr(
					td(paragraph('Paul')) + td(paragraph('UK')) + td(paragraph('48')),
				)
				+ tr(
					td(paragraph('Louisa')) + td(paragraph('Germany')) + td(paragraph('24')),
				),
			) + paragraph(),
		);

		if (IS_MAC)
		{
			await expect(page).toHaveScreenshot('table.png');
		}
	});

	test('Spoiler', async ({ page }) => {
		await focusEditor(page);
		await page.evaluate(() => {
			window.textEditor.setText(
				'[spoiler=Spoiler]\n'
				+ '[img width=100 height=150]https://i.pinimg.com/564x/3d/d8/3f/3dd83fc6cfce54d3ad2bcc992cd5ed18.jpg[/img]\n'
				+ '[/spoiler]',
			);
		});

		await assertHTML(
			page,
			spoiler(paragraph(image() + br()), false) + paragraph(),
			{ ignoreInlineStyles: true },
		);

		if (IS_MAC)
		{
			await expect(page).toHaveScreenshot('spoiler.png');
		}
	});

	test('List', async ({ page }) => {
		await focusEditor(page);
		await page.evaluate(() => {
			window.textEditor.setText(
				'[LIST]\n'
				+ '\t[*][s]One[/s]\n'
				+ '\t[*]T[b]wo[/b]\n'
				+ '\t[*]Three\n'
				+ '[/LIST]',
			);
		});

		await assertHTML(
			page,
			ul(
				li(strike('One'), 1)
				+ li(text('T') + bold('wo'), 2)
				+ li('Three', 3),
			) + paragraph(),
		);

		if (IS_MAC)
		{
			await expect(page).toHaveScreenshot('list.png');
		}
	});

	test('Video', async ({ page }) => {
		await focusEditor(page);
		await page.evaluate(() => {
			window.textEditor.setText(
				'[video]https://video.1c-bitrix.ru/bitrix24/themes/video-rain/rain3.mp4[/video]',
			);
		});

		await assertHTML(
			page,
			paragraph(video() + br()),
			{ ignoreInlineStyles: true },
		);
	});
});
