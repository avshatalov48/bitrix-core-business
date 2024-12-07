import { DefaultBBCodeScheme } from 'ui.bbcode.model';
import { HtmlFormatter } from '../../src/html-formatter';
import {
	paragraph,
	bold,
	italic,
	underline,
	strike,
	table,
	tr,
	td,
	th,
	ul,
	li,
	ol,
	quote,
	link,
	autolink,
	br,
	user,
	department,
	project,
	img,
} from './html';

const scheme = new DefaultBBCodeScheme();

const toHTML = (fragment: DocumentFragment): string => {
	const div = document.createElement('div');
	div.appendChild(fragment);

	return div.innerHTML;
};

describe('HtmlFormatter', () => {
	let formatter = null;

	before(() => {
		formatter = new HtmlFormatter();
	});

	describe('HTMLFormatter.format', () => {
		it('should return a fragment', () => {
			const root = scheme.createRoot();
			const result = formatter.format({
				source: root,
			});

			assert.ok(result instanceof window.DocumentFragment);
		});

		it('should return a fragment #2', () => {
			const bbcode = '';
			const result = formatter.format({
				source: bbcode,
			});

			assert.ok(result instanceof window.DocumentFragment);
		});

		it('should return a fragment #3', () => {
			const bbcode = '[b]test[/b]';
			const result = formatter.format({
				source: bbcode,
			});

			assert.ok(result instanceof window.DocumentFragment);
		});

		it('should throws if passed invalid options', () => {
			assert.throws(() => {
				formatter.format();
			});

			assert.throws(() => {
				formatter.format(null);
			});

			assert.throws(() => {
				formatter.format('test');
			});

			assert.throws(() => {
				formatter.format({});
			});
		});

		it('should does not throws if passed source a string', () => {
			assert.doesNotThrow(() => {
				formatter.format({
					source: '',
				});
			});
		});

		it('should does not throws if passed source a AST node', () => {
			assert.doesNotThrow(() => {
				formatter.format({
					source: scheme.createRoot(),
				});
			});

			assert.doesNotThrow(() => {
				formatter.format({
					source: scheme.createElement({
						name: 'b',
					}),
				});
			});
		});
	});

	describe('Text', () => {
		it('should convert text to text DOM node', () => {
			const bbcode = 'test text';
			const result = formatter.format({
				source: bbcode,
			});

			assert.ok(result.childNodes[0] instanceof window.HTMLParagraphElement);
		});

		it('should convert bbcode text node to text DOM node', () => {
			const rootNode = scheme.createRoot({
				children: [
					scheme.createText('test text'),
				],
			});

			const result = formatter.format({
				source: rootNode,
			});

			assert.ok(result.childNodes[0] instanceof window.HTMLParagraphElement);
		});
	});

	describe('Linebreak', () => {
		it('should convert single linebreak to text node', () => {
			const bbcode = '\n';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph());
		});

		it('should convert two linebreaks to br element', () => {
			const bbcode = '\n\n';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph());
		});
	});

	describe('B, U, I, S', () => {
		it('should format b tag', () => {
			const bbcode = 'text [b]bold[/b]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph('text ' + bold('bold')));
		});

		it('should format u tag', () => {
			const bbcode = 'text [u]underline[/u]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph('text ' + underline('underline')));
		});

		it('should format i tag', () => {
			const bbcode = 'text [i]italic[/i]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph('text ' + italic('italic')));
		});

		it('should format s tag', () => {
			const bbcode = 'text [s]strikethrough[/s]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph('text ' + strike('strikethrough')));
		});
	});

	describe('Paragraph', () => {
		it('should format p tag to paragraph element', () => {
			const bbcode = '[p]paragraph[/p]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph('paragraph'));
		});

		it('should format p node to paragraph element', () => {
			const ast = scheme.createRoot({
				children: [
					scheme.createElement({
						name: 'p',
						children: [
							scheme.createText('paragraph'),
						],
					}),
				],
			});
			const result = formatter.format({
				source: ast,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph('paragraph'));
		});
	});

	describe('Span', () => {
		it('should skip span tag', () => {
			const bbcode = '[span]span[/span]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph('span'));
		});
	});

	describe('Table', () => {
		it('should convert table tag to table element', () => {
			const bbcode = '[table][tr][th]head1[/th][/tr][tr][td]data1[/td][/tr][/table]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, table(tr(th(paragraph('head1'))) + tr(td(paragraph('data1')))));
		});

		it('should convert table node to table element', () => {
			const ast = scheme.createElement({
				name: 'table',
				children: [
					scheme.createElement({
						name: 'tr',
						children: [
							scheme.createElement({
								name: 'th',
								children: [
									scheme.createText('head1'),
								],
							}),
						],
					}),
					scheme.createElement({
						name: 'tr',
						children: [
							scheme.createElement({
								name: 'td',
								children: [
									scheme.createText('data1'),
								],
							}),
						],
					}),
				],
			});
			const result = formatter.format({
				source: ast,
			});

			const html = toHTML(result);

			assert.deepEqual(html, table(tr(th(paragraph('head1'))) + tr(td(paragraph('data1')))));
		});
	});

	describe('List', () => {
		it('should convert list > * tags to ul > li elements', () => {
			const bbcode = '[list][*]item1[*]item2[/list]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, ul(li('item1') + li('item2')));
		});

		it('should convert list > * nodes to ul > li elements', () => {
			const ast = scheme.createElement({
				name: 'list',
				children: [
					scheme.createElement({
						name: '*',
						children: [
							scheme.createText('item1'),
						],
					}),
					scheme.createElement({
						name: '*',
						children: [
							scheme.createText('item2'),
						],
					}),
				],
			});
			const result = formatter.format({
				source: ast,
			});

			const html = toHTML(result);

			assert.deepEqual(html, ul(li('item1') + li('item2')));
		});
	});

	describe('List=1', () => {
		it('should convert list=1 > * tags to ol > li elements', () => {
			const bbcode = '[list=1][*]item1[*]item2[/list]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, ol(li('item1') + li('item2')));
		});

		it('should convert list=1 > * nodes to ol > li elements', () => {
			const ast = scheme.createElement({
				name: 'list',
				value: '1',
				children: [
					scheme.createElement({
						name: '*',
						children: [
							scheme.createText('item1'),
						],
					}),
					scheme.createElement({
						name: '*',
						children: [
							scheme.createText('item2'),
						],
					}),
				],
			});
			const result = formatter.format({
				source: ast,
			});

			const html = toHTML(result);

			assert.deepEqual(html, ol(li('item1') + li('item2')));
		});
	});

	describe('Quote', () => {
		it('should convert quote tag to blockquote element', () => {
			const bbcode = '[quote]quote[/quote]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, quote(paragraph('quote')));
		});

		it('should convert quote node to blockquote element', () => {
			const ast = scheme.createElement({
				name: 'quote',
				children: [
					scheme.createText('quote'),
				],
			});
			const result = formatter.format({
				source: ast,
			});

			const html = toHTML(result);

			assert.deepEqual(html, quote(paragraph('quote')));
		});
	});

	xdescribe('Code', () => {
		it('should convert code tag to pre > span elements', () => {
			const bbcode = '[code]console.log({});[/code]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(
				html,
				'<pre class="ui-formatter-code-block"><span class="ui-formatter-code-line">console.log({});</span></pre>',
			);
		});

		it('should convert code node to pre > span elements', () => {
			const ast = scheme.createElement({
				name: 'code',
				children: [
					scheme.createText('console.log({});'),
				],
			});
			const result = formatter.format({
				source: ast,
			});

			const html = toHTML(result);

			assert.deepEqual(
				html,
				'<pre class="ui-formatter-code-block"><span class="ui-formatter-code-line">console.log({});</span></pre>',
			);
		});
	});

	describe('Url', () => {
		it('should convert url tag to anchor element', () => {
			const bbcode = '[url]https://bitrix24.com[/url]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph(autolink('https://bitrix24.com')));
		});

		it('should convert url tag with value to anchor element', () => {
			const bbcode = '[url=https://bitrix24.com]test[/url]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph(link('test', 'https://bitrix24.com')));
		});

		it('should convert url with formatted text', () => {
			const bbcode = '[url=https://bitrix24.com]text [b]bold[/b][/url]';
			const result = formatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(html, paragraph(link('text ' + bold('bold'), 'https://bitrix24.com')));
		});

		it('should works with img in url', () => {
		    const bbcode = '[url=https://www.bitrix.com][img]https://bitrix.com/img.png[/img][/url]';
			const customFormatter = new HtmlFormatter({
				linkSettings: {
					shortLink: {
						enabled: true,
						maxLength: 3,
						lastFragmentLength: 2,
					},
				},
			});
			const result = customFormatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(
				html,
				paragraph(link(img('https://bitrix.com/img.png'), 'https://www.bitrix.com'))
			);
		});
	});

	describe('Mention', () => {
		it('should format valid user tag to a anchor element', () => {
			const bbcode = '[user=1]test user[/user]';
			const customFormatter = new HtmlFormatter({
				mention: {
					urlTemplate: {
						user: '/test/user/path/#ID#/',
						project: '/test/project/path/#group_id#/',
						department: '/test/department/path/#ID#/',
					},
				},
			});
			const result = customFormatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(
				html,
				paragraph(user('/test/user/path/1/', 1, 'test user')),
			);
		});

		it('should format valid project tag to a anchor element', () => {
			const bbcode = '[project=1]test project[/project]';
			const customFormatter = new HtmlFormatter({
				mention: {
					urlTemplate: {
						user: '/test/user/path/#ID#/',
						project: '/test/project/path/#group_id#/',
						department: '/test/department/path/#ID#/',
					},
				},
			});
			const result = customFormatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(
				html,
				paragraph(project('/test/project/path/1/', 1, 'test project')),
			);
		});

		it('should format valid department tag to a anchor element', () => {
			const bbcode = '[department=1]test department[/department]';
			const customFormatter = new HtmlFormatter({
				mention: {
					urlTemplate: {
						user: '/test/user/path/#ID#/',
						project: '/test/project/path/#group_id#/',
						department: '/test/department/path/#ID#/',
					},
				},
			});
			const result = customFormatter.format({
				source: bbcode,
			});

			const html = toHTML(result);

			assert.deepEqual(
				html,
				paragraph(department('/test/department/path/1/', 1, 'test department')),
			);
		});
	});
});
