import { BBCodeParser } from '../../src/parser';
import { BBCodeNode } from 'ui.bbcode.model';

const stripIndent = (source) => {
	const lines = source.split('\n').slice(1, -1);
	const minIndent = Math.min(
		...lines.map((line) => {
			return line.split('\t').length - 1;
		}),
	);

	if (minIndent === 0)
	{
		return source;
	}

	const regex = new RegExp(`^\t{${minIndent}}`, 'gm');

	return lines.join('\n').replace(regex, '');
};

describe('ui.bbcode.parser/Parser', () => {
	it('Parse text with base-formatting', () => {
		const bbcode = stripIndent(`
			Test text [b]bold[/b], [i]italic[/i], [u]underline[/u], [s]strike[/s]
			[img width=20 height=20]/path/to/image.png[/img]
			[url=https://bitrix24.com]Bitrix24[/url]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 12);

		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'Test text ');

		assert.ok(ast.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(1).getName() === 'b');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getContent() === 'bold');

		assert.ok(ast.getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === ', ');

		assert.ok(ast.getChildren().at(3).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(3).getName() === 'i');
		assert.ok(ast.getChildren().at(3).getChildren().at(0).getContent() === 'italic');

		assert.ok(ast.getChildren().at(4).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(4).getContent() === ', ');

		assert.ok(ast.getChildren().at(5).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(5).getName() === 'u');
		assert.ok(ast.getChildren().at(5).getChildren().at(0).getContent() === 'underline');

		assert.ok(ast.getChildren().at(6).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(6).getContent() === ', ');

		assert.ok(ast.getChildren().at(7).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(7).getName() === 's');
		assert.ok(ast.getChildren().at(7).getChildren().at(0).getContent() === 'strike');

		assert.ok(ast.getChildren().at(8).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(8).getContent() === '\n');

		assert.ok(ast.getChildren().at(9).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(9).getName() === 'img');
		assert.deepEqual(ast.getChildren().at(9).getAttributes(), { width: '20', height: '20' });
		assert.ok(ast.getChildren().at(9).getChildren().at(0).getContent() === '/path/to/image.png');

		assert.ok(ast.getChildren().at(10).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(10).getContent() === '\n');

		assert.ok(ast.getChildren().at(11).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(11).getName() === 'url');
		assert.ok(ast.getChildren().at(11).getValue(), 'https://bitrix24.com');
		assert.ok(ast.getChildren().at(11).getChildren().at(0).getContent() === 'Bitrix24');
	});

	it('should recognize line breaks', () => {
		const bbcode = 'ABC \n DEF \n [b]bold \n bold[/b] \n [i]italic[/i]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 10);

		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'ABC ');

		assert.ok(ast.getChildren().at(1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getContent() === '\n');

		assert.ok(ast.getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === ' DEF ');

		assert.ok(ast.getChildren().at(3).getType() === BBCodeNode.TEXT_NODE);

		assert.ok(ast.getChildren().at(4).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(4).getContent() === ' ');

		assert.ok(ast.getChildren().at(5).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(5).getName() === 'b');
		assert.ok(ast.getChildren().at(5).getChildrenCount() === 3);
		assert.ok(ast.getChildren().at(5).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getChildren().at(0).getContent() === 'bold ');
		assert.ok(ast.getChildren().at(5).getChildren().at(1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getChildren().at(2).getContent() === ' bold');

		assert.ok(ast.getChildren().at(6).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(6).getContent() === ' ');

		assert.ok(ast.getChildren().at(7).getType() === BBCodeNode.TEXT_NODE);

		assert.ok(ast.getChildren().at(8).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(8).getContent() === ' ');

		assert.ok(ast.getChildren().at(9).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(9).getName() === 'i');
		assert.ok(ast.getChildren().at(9).getChildrenCount() === 1);
		assert.ok(ast.getChildren().at(9).getChildren().at(0).getContent() === 'italic');
	});

	it('should recognize line breaks at the start', () => {
		let bbcode = '\n';
		let parser = new BBCodeParser();
		let ast = parser.parse(bbcode);
		assert.ok(ast.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 1);
		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);

		bbcode = '\n ABC';
		parser = new BBCodeParser();
		ast = parser.parse(bbcode);
		assert.ok(ast.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 2);
		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getContent() === ' ABC');

		bbcode = '\n\nABC';
		parser = new BBCodeParser();
		ast = parser.parse(bbcode);
		assert.ok(ast.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 3);
		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === 'ABC');
	});

	it('should recognize line breaks at the end', () => {
		let bbcode = 'ABC\n';
		let parser = new BBCodeParser();
		let ast = parser.parse(bbcode);
		assert.ok(ast.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 2);
		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'ABC');
		assert.ok(ast.getChildren().at(1).getType() === BBCodeNode.TEXT_NODE);

		bbcode = 'ABC\n\n';
		parser = new BBCodeParser();
		ast = parser.parse(bbcode);
		assert.ok(ast.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 3);
		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'ABC');
		assert.ok(ast.getChildren().at(1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
	});

	it('should parse text up to the end', () => {
		const bbcode = 'A\n'
			+ 'B\n'
			+ '\n'
			+ '[code]\n'
			+ '\n'
			+ 'C\n'
			+ 'D'
		;

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildren().at(-1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(-1).getContent() === 'D');
	});

	it('should have node types', () => {
		const bbcode = 'Test text [b]bold[/b]\n';

		const parser = new BBCodeParser();
		const root = parser.parse(bbcode);

		assert.ok(root.getType() === BBCodeNode.ROOT_NODE);
		assert.ok(root.getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(root.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(root.getChildren().at(1).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(root.getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
	});

	it('should works with [code]', () => {
		const bbcode = stripIndent(`
			[code]
				[b]Bold[/b]
				function Test(...args)
				{
					console.log(...args);
				}
			[/code]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.deepEqual(ast.toString({ encode: false }), bbcode);
	});

	xit('should parses multi-level lists', () => {
		const bbcode = stripIndent(`
			[list]
				[*]Item #1
					[list]
						[*]Sub list item #1
						[*]Sub list item #2
					[/list]
				[*]Item #2
				[*]Item #3
			[/list]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		const listNode = ast.getChildren().at(0);

		assert.ok(listNode.getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(listNode.getChildrenCount() === 3);
		assert.ok(listNode.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(2).getType() === BBCodeNode.ELEMENT_NODE);

		const subList = listNode.getChildren().at(0).getChildren().at(-1);
		assert.ok(subList.getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(subList.getChildrenCount() === 2);
		assert.ok(subList.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(subList.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
	});

	it('should works with tags in list item', () => {
		const bbcode = stripIndent(`
			[list][*]Item 1[b]bold[/b] 1[*]Item 2 [i]italic[/i][*]Item 3[/list]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		const listNode = ast.getChildren().at(0);
		assert.ok(listNode.getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(listNode.getChildrenCount() === 3);
		assert.ok(listNode.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);

		assert.ok(listNode.getChildren().at(0).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(listNode.getChildren().at(0).getChildren().at(0).getContent() === 'Item 1');
		assert.ok(listNode.getChildren().at(0).getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(0).getChildren().at(1).getName() === 'b');
		assert.ok(listNode.getChildren().at(0).getChildren().at(1).getContent() === 'bold');
		assert.ok(listNode.getChildren().at(0).getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(listNode.getChildren().at(0).getChildren().at(2).getContent() === ' 1');

		assert.ok(listNode.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(1).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(listNode.getChildren().at(1).getChildren().at(0).getContent() === 'Item 2 ');
		assert.ok(listNode.getChildren().at(1).getChildren().at(1).getName() === 'i');
		assert.ok(listNode.getChildren().at(1).getChildren().at(1).getContent() === 'italic');

		assert.ok(listNode.getChildren().at(2).getType() === BBCodeNode.ELEMENT_NODE, 'Invalid list item node');
		assert.ok(listNode.getChildren().at(2).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(listNode.getChildren().at(2).getChildren().at(0).getContent() === 'Item 3');
	});

	it('parseText', () => {
		const parser = new BBCodeParser();
		const parent = new BBCodeNode();
		const source = '\n\t\ttest\nnewline';

		const result = parser.parseText(source, parent);

		assert.ok(result.at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(result.at(0).getContent() === '\n');

		assert.ok(result.at(1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(result.at(1).getContent() === '\t');

		assert.ok(result.at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(result.at(2).getContent() === '\t');

		assert.ok(result.at(3).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(result.at(3).getContent() === 'test');

		assert.ok(result.at(4).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(result.at(4).getContent() === '\n');

		assert.ok(result.at(5).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(result.at(5).getContent() === 'newline');
	});

	it('should works with table', () => {
		const bbcode = stripIndent(`
			[table]
				[tr]
					[td][b]Head cell 1[/b][/td]
					[td][i]Head cell 2[/i][/td]
					[td][s]Head cell 3[/s][/td]
				[/tr]
				[tr]
					[td]Body cell 1/1[/td]
					[td]Body cell 1/2[/td]
					[td]Body cell 1/3[/td]
				[/tr]
			[/table]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);
		const table = ast.getChildren().at(0);

		assert.ok(table.getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(table.getName() === 'table');
		assert.ok(table.getChildrenCount() === 2);

		const tr1 = table.getChildren().at(0);
		assert.ok(tr1.getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr1.getName() === 'tr');
		assert.ok(tr1.getChildrenCount() === 3);
		assert.ok(tr1.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(0).getName() === 'td');
		assert.ok(tr1.getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getName() === 'b');
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getChildren().at(0).getContent() === 'Head cell 1');

		assert.ok(tr1.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(1).getName() === 'td');
		assert.ok(tr1.getChildren().at(1).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getName() === 'i');
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getChildren().at(0).getContent() === 'Head cell 2');

		assert.ok(tr1.getChildren().at(2).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(2).getName() === 'td');
		assert.ok(tr1.getChildren().at(2).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getName() === 's');
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getChildren().at(0).getContent() === 'Head cell 3');

		const tr2 = table.getChildren().at(1);
		assert.ok(tr2.getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr2.getName() === 'tr');
		assert.ok(tr2.getChildrenCount() === 3);
		assert.ok(tr2.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr2.getChildren().at(0).getName() === 'td');
		assert.ok(tr2.getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr2.getChildren().at(0).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(tr2.getChildren().at(0).getChildren().at(0).getContent() === 'Body cell 1/1');

		assert.ok(tr2.getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr2.getChildren().at(1).getName() === 'td');
		assert.ok(tr2.getChildren().at(1).getChildrenCount() === 1);
		assert.ok(tr2.getChildren().at(1).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(tr2.getChildren().at(1).getChildren().at(0).getContent() === 'Body cell 1/2');

		assert.ok(tr2.getChildren().at(2).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(tr2.getChildren().at(2).getName() === 'td');
		assert.ok(tr2.getChildren().at(2).getChildrenCount() === 1);
		assert.ok(tr2.getChildren().at(2).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(tr2.getChildren().at(2).getChildren().at(0).getContent() === 'Body cell 1/3');
	});

	it('Should works with formatting in list item content', () => {
		const bbcode = stripIndent(`
			[list]
				[*]List item [b]bold[/b] #1
				[*]List item #2
				[*]List item #3
			[/list]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildrenCount() === 1);

		const list = ast.getChildren().at(0);
		assert.ok(list.getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(list.getName() === 'list');
		assert.ok(list.getChildrenCount() === 3);
		assert.ok(list.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(list.getChildren().at(0).getName() === '*');
		assert.ok(list.getChildren().at(0).getChildrenCount() === 3);
		assert.ok(list.getChildren().at(0).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(list.getChildren().at(0).getChildren().at(0).getContent() === 'List item ');
		assert.ok(list.getChildren().at(0).getChildren().at(1).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(list.getChildren().at(0).getChildren().at(1).getName() === 'b');
		assert.ok(list.getChildren().at(0).getChildren().at(1).getChildrenCount() === 1);
		assert.ok(list.getChildren().at(0).getChildren().at(1).getChildren().at(0).getContent() === 'bold');
		assert.ok(list.getChildren().at(0).getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(list.getChildren().at(0).getChildren().at(2).getContent() === ' #1');
	});

	it('Should works with [disk] tag', () => {
		const bbcode = stripIndent(`
			[disk file id=11] First line text
			[b]bold[/b][disk file id=22]
			[p][disk file id=33][/p]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(0).getName() === 'disk');
		assert.ok(ast.getChildren().at(0).isVoid() === true);
		assert.deepEqual(ast.getChildren().at(0).getAttributes(), { file: '', id: '11' });
		assert.ok(ast.getChildren().at(1).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getContent() === ' First line text');
		assert.ok(ast.getChildren().at(2).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === '\n');
		assert.ok(ast.getChildren().at(3).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(3).getName() === 'b');
		assert.ok(ast.getChildren().at(3).getChildrenCount() === 1);
		assert.ok(ast.getChildren().at(3).getChildren().at(0).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(3).getChildren().at(0).getContent() === 'bold');
		assert.ok(ast.getChildren().at(4).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(4).getName() === 'disk');
		assert.ok(ast.getChildren().at(4).isVoid() === true);
		assert.deepEqual(ast.getChildren().at(4).getAttributes(), { file: '', id: '22' });
		assert.ok(ast.getChildren().at(5).getType() === BBCodeNode.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getContent() === '\n');
		assert.ok(ast.getChildren().at(6).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(6).getName() === 'p');
		assert.ok(ast.getChildren().at(6).getChildren().at(0).getType() === BBCodeNode.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(6).getChildren().at(0).getName() === 'disk');
	});

	it('Should format code block', () => {
		const bbcode = '[code]Use code tag for [b]code[/b][/code]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		const result = '[code]\nUse code tag for [b]code[/b]\n[/code]';

		assert.deepEqual(ast.toString({ encode: false }), result);
	});

	it('Should format list block', () => {
		const bbcode = '[list][*]One[*]Two[*]Three[/list]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);
		const result = '[list]\n[*]One\n[*]Two\n[*]Three\n[/list]';

		assert.ok(ast.toString(), result);
	});

	it('One line break must be added between two block elements', () => {
		const bbcode = '[p]\ntext\n[/p][list]\n[*]one\n[/list]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);
		const result = '[p]text\n[/p]\n[list]\n[*]one\n[/list]';
		assert.equal(ast.toString(), result);
	});

	it('One line break must be added between text and block element', () => {
		const bbcode = 'Any text[p]\ntext\n[/p]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);
		const result = 'Any text\n[p]text\n[/p]';

		assert.equal(ast.toString(), result);
	});

	it('One line break should be added after the block element if there is text behind it', () => {
		const bbcode = '[p]\ntext\n[/p]Any text';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);
		const result = '[p]text\n[/p]Any text';

		assert.equal(ast.toString(), result);
	});

	it('An invalid descendant must be added to a higher level', () => {
		const bbcode = stripIndent(`
			[table]
				[b]Bold[/b]
				[tr]
					[td]cell1[/td]
					[td]cell2[/td]
				[/tr]
			[/table]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildrenCount() === 2);
		assert.ok(ast.getChildren().at(0).getName() === 'b');
		assert.ok(ast.getChildren().at(0).getChildren().at(0).getName() === '#text');
		assert.ok(ast.getChildren().at(0).getChildren().at(0).getContent() === 'Bold');

		assert.ok(ast.getChildren().at(1).getName() === 'table');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getName() === 'tr');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getChildren().at(0).getName() === 'td');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getChildren().at(1).getName() === 'td');
	});

	xit('An invalid descendant must be added to a higher level (#2)', () => {
		const bbcode = stripIndent(`
			[table]
				[tr]
					[td]cell1[table][/table][/td]
					[td]cell2[/td]
				[/tr]
			[/table]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildrenCount() === 2);
		assert.ok(ast.getChildren().at(0).getName() === 'table');
		assert.ok(ast.getChildren().at(0).getChildrenCount() === 0);

		assert.ok(ast.getChildren().at(1).getName() === 'table');
		assert.ok(ast.getChildren().at(1).getChildrenCount() === 1);
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getName() === 'tr');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getChildrenCount() === 2);
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getChildren().at(0).getName() === 'td');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getChildren().at(1).getName() === 'td');
	});

	it('Should parse value with spaces', () => {
		const bbcode = '[b=test any text]content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getValue() === 'test any text');
	});

	it('Should parse value with single quotes', () => {
		const bbcode = '[b=\'test any text\']content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getValue() === 'test any text');
	});

	it('Should parse value with double quotes', () => {
		const bbcode = '[b="test any text"]content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getValue() === 'test any text');
	});

	it('Should parse value if passed one leading quote', () => {
		const bbcode = '[b="test any text]content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getValue() === '"test any text');
	});

	it('Should parse value if passed one final quote', () => {
		const bbcode = '[b=test any text"]content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getValue() === 'test any text"');
	});

	it('Should parse value if passed text with quotes', () => {
		const bbcode = '[b=test \'any" text]content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getValue() === 'test \'any" text');
	});

	it('Should parse attributes as value if passed value and attributes', () => {
		const bbcode = '[b=test text attr=111]content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getValue() === 'test text attr=111');
	});

	it('Should parse attributes with single quotes', () => {
		const bbcode = '[b attr1=\'val1\' attr2=\'val2\']content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getAttribute('attr1') === 'val1');
		assert.ok(ast.getFirstChild().getAttribute('attr2') === 'val2');
	});

	it('Should parse attributes with double quotes', () => {
		const bbcode = '[b attr1="val1" attr2="val2"]content[/b]';
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getFirstChild().getName() === 'b');
		assert.ok(ast.getFirstChild().getAttribute('attr1') === 'val1');
		assert.ok(ast.getFirstChild().getAttribute('attr2') === 'val2');
	});

	it('[p] > [list] (hoisting)', () => {
		const bbcode = stripIndent(`
			[p]
			1
			2
			
			[LIST=1]
				[*][s]One[/s]
				[*]T[b]wo[/b]
				[*]Three
			[/LIST]
			
			[LIST]
			[*]One
			[u]One-One[/u]
			[*]Two
			[*]Three
			[/LIST]
			
			3
			4
			[/p]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.deepEqual(
			ast.toString(),
			'[list=1]\n'
			+ '[*][s]One[/s][*]T[b]wo[/b][*]Three\n'
			+ '[/list]\n'
			+ '[list]\n'
			+ '[*]One\n'
			+ '[u]One-One[/u][*]Two[*]Three\n'
			+ '[/list]\n'
			+ '[p]'
			+ '1\n'
			+ '2\n'
			+ '\n'
			+ '\n'
			+ '\n'
			+ '\n'
			+ '\n'
			+ '3\n'
			+ '4\n'
			+ '[/p]',
		);
	});

	it('should convert deprecated tags #1', () => {
		const bbcode = stripIndent(`
			[left]left[/left]
			[center]center[/center]
			[right]right[/right]
			[justify]justify[/justify]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.deepEqual(ast.toString(), '[p]left\n[/p]\n[p]center\n[/p]\n[p]right\n[/p]\n[p]justify\n[/p]');
	});

	it('should convert deprecated tags #2', () => {
		const bbcode = stripIndent(`
			[background]bg[/background]
			[color]color[/color]
			[size]size[/size]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.deepEqual(ast.toString(), '[b]bg[/b]\n[b]color[/b]\n[b]size[/b]');
	});

	it('tag value with special chars', () => {
		const bbcode = stripIndent(`
			[url=https://ya.ru?prop[]=222]test[/url]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.equal(ast.getChildrenCount(), 1);
		assert.equal(ast.getFirstChild().getName(), 'url');
		assert.equal(ast.getFirstChild().getValue(), 'https://ya.ru?prop[');
	});

	xit('should work with invalid bbcode #1', () => {
		const bbcode = stripIndent(`
			[p][b]test[b][/p]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildren().at(0).getChildren().at(0).getContent() === '[b]');
		assert.ok(ast.getChildren().at(0).getChildren().at(1).getContent() === 'test');
		assert.ok(ast.getChildren().at(0).getChildren().at(2).getContent() === '[b]');
	});

	xit('should work with invalid bbcode #2', () => {
		const bbcode = stripIndent(`
			[p]test[/p][/p][/b]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildren().at(0).getName() === 'p');
		assert.ok(ast.getChildren().at(0).getChildren().at(0).getContent() === 'test');
		assert.ok(ast.getChildren().at(1).getContent() === '[/p]');
		assert.ok(ast.getChildren().at(2).getContent() === '[/b]');
	});

	xit('should work with invalid bbcode #3', () => {
		const bbcode = stripIndent(`
			[code]test[/quote]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildren().at(0).getContent() === '[code]');
		assert.ok(ast.getChildren().at(1).getContent() === 'test');
		assert.ok(ast.getChildren().at(2).getContent() === '[/quote]');
	});

	it('should works with code in code', () => {
		const bbcode = stripIndent(`
			[code]
			test
			[code]
			code
			[/code]
			[/code]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.equal(ast.toString({ encode: false }), bbcode);
	});

	describe('Encoding/decoding', () => {
		xit('should decode source bbcode', () => {
			const bbcode = stripIndent(`
				[p]&#91;&#93;[/p]
				&#91;&#93;
				&amp;#91;&amp;#93;
				&#39;&quot;
				&lt;&gt;
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.equal(
				ast.getChildren().at(0).getChildren().at(0).getContent(),
				'[]',
			);

			assert.equal(
				ast.getChildren().at(2).getContent(),
				'[]',
			);

			assert.equal(
				ast.getChildren().at(4).getContent(),
				'&#91;&#93;',
			);

			assert.equal(
				ast.getChildren().at(6).getContent(),
				'\'"',
			);

			assert.equal(
				ast.getChildren().at(8).getContent(),
				'<>',
			);
		});

		describe('List', () => {
			it('should works with spaces before items', () => {
				const bbcode = '[list]\n   [*]Item1\n   \t[*]Item2\n[/list]';

				const parser = new BBCodeParser();
				const ast = parser.parse(bbcode);

				assert.ok(ast.getChildrenCount() === 1);
				assert.ok(ast.getFirstChild().getName() === 'list');
				assert.ok(ast.getFirstChild().getChildrenCount() === 2);
				assert.ok(ast.getFirstChild().getFirstChild().getChildrenCount() === 3);
				assert.ok(ast.getFirstChild().getFirstChild().getChildren().at(0).getName() === '#text');
				assert.ok(ast.getFirstChild().getFirstChild().getChildren().at(1).getName() === '#linebreak');
				assert.ok(ast.getFirstChild().getFirstChild().getChildren().at(2).getName() === '#text');

				assert.ok(ast.getFirstChild().getLastChild().getChildrenCount() === 1);
				assert.ok(ast.getFirstChild().getLastChild().getChildren().at(0).getName() === '#text');
			});
		});
	});

	describe('BUGS', () => {
		it('0190706 ', () => {
			const bbcode = stripIndent(`
				[quote]
				[list]
				[*][b]bold1[/b] text1[*][b]bold2[/b] text2[*][b]bold3[/b] text3
				[/list][/quote]
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(bbcode, ast.toString());
		});

		it('0190838', () => {
			const bbcode = stripIndent(`
				[b]
					[U]
						[LIST]
							[*][s]111[/s]
							[*][s]222[/s]
						[/LIST]
					[/U]
				[/b]
				[LIST]
					[*]АААААААААААААААААА
				[/LIST]
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			const resultBbcode = stripIndent(`
				[list]
				[*][s]111[/s][*][s]222[/s]
				[/list]
				[b]
				[u]
				
				[/u]
				[/b]
				[list]
				[*]АААААААААААААААААА
				[/list]
			`);

			assert.deepEqual(ast.toString(), resultBbcode);
		});

		it('0190838 #2', () => {
			const bbcode = stripIndent(`
				[size=999]
					[table]
						[tr]
							[td]11[/td]
							[td]22[/td]
						[/tr]
					[/table]
				[/size]
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.ok(ast.getFirstChild().getName() === 'table');
			assert.ok(ast.getLastChild().getName() === 'b');
		});

		it('202706', () => {
			const bbcode = ''
				+ '[list]\n'
					+ '[*]Item1 [b]bold[/b] [u=1]underline[/u][s]strike[/s] text'
					+ '[*]Item2 [i]i[/i] text [b]b[/b]'
				+ '\n[/list]'
			+ '';

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.toString(),
				bbcode,
			);
		});

		it('203089', () => {
			const bbcode = ''
				+ '[list]\n'
				+ '[*]Item0 [b]b0[/b] [b]b02[/b] [url=https://ya.ru]url[/url] text0'
				+ '[*]Item1 [i]i1[/i] [i]i12[/i] text1'
				+ '\n[/list]'
				+ '';

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.toString(),
				bbcode,
			);
		});
	});

	describe('Parse links', () => {
		it('should parse links from plain text', () => {
			const bbcode = stripIndent(`
				text1 https://bitrix.com text2 https://bitrix24.com
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.toString(),
				'text1 [url=https://bitrix.com]https://bitrix.com[/url] text2 [url=https://bitrix24.com]https://bitrix24.com[/url]',
			);
		});

		it('should parse links from formatted text', () => {
			const bbcode = stripIndent(`
				[b]text1 https://bitrix.com[/b] text2 [i]https://bitrix24.com[/i]
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.toString(),
				'[b]text1 [url=https://bitrix.com]https://bitrix.com[/url][/b] text2 [i][url=https://bitrix24.com]https://bitrix24.com[/url][/i]',
			);
		});

		it('should not parse links in url tag', () => {
			const bbcode = stripIndent(`
				[url]https://bitrix.com[/url]
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.toString(),
				'[url]https://bitrix.com[/url]',
			);
		});

		it('should not parse links in img tag', () => {
			const bbcode = stripIndent(`
				[img]https://bitrix.com[/img]
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.toString(),
				'[img]https://bitrix.com[/img]',
			);
		});

		it('should parse link starts with www', () => {
			const bbcode = stripIndent(`
				text1 www.bitrix.com text2
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.toString(),
				'text1 [url=https://www.bitrix.com]www.bitrix.com[/url] text2',
			);
		});

		it('should change http scheme to https', () => {
			const bbcode = 'http://bitrix.com';
			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.equal(
				ast.toString(),
				'[url=https://bitrix.com]http://bitrix.com[/url]',
			);
		});

		it('should parse short links with valid TLD', () => {
			const bbcode = stripIndent(`
				bitrix.com
				bitrix.youtube
				bitrix.com.de
				www.bitrix.com
				www.bitrix.youtube
				www.bitrix.com.de
			`);
			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.equal(
				ast.getChildren()[0].toString(),
				'[url=https://bitrix.com]bitrix.com[/url]',
			);

			assert.equal(
				ast.getChildren()[2].toString(),
				'[url=https://bitrix.youtube]bitrix.youtube[/url]',
			);

			assert.equal(
				ast.getChildren()[4].toString(),
				'[url=https://bitrix.com.de]bitrix.com.de[/url]',
			);

			assert.equal(
				ast.getChildren()[6].toString(),
				'[url=https://www.bitrix.com]www.bitrix.com[/url]',
			);

			assert.equal(
				ast.getChildren()[8].toString(),
				'[url=https://www.bitrix.youtube]www.bitrix.youtube[/url]',
			);

			assert.equal(
				ast.getChildren()[10].toString(),
				'[url=https://www.bitrix.com.de]www.bitrix.com.de[/url]',
			);
		});

		it('should not parse links with invalid TLD', () => {
			const bbcode = stripIndent(`
				bitrix.comp
				bitrix.rus
				bitrix.bitrix
			`);
			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.equal(
				ast.getChildren()[0].toString(),
				'bitrix.comp',
			);

			assert.equal(
				ast.getChildren()[2].toString(),
				'bitrix.rus',
			);

			assert.equal(
				ast.getChildren()[4].toString(),
				'bitrix.bitrix',
			);
		});

		it('should parse emails', () => {
			const bbcode = stripIndent(`
				mymail@bitrix.com
				i@vovkabelov.ru
			`);

			const parser = new BBCodeParser();
			const ast = parser.parse(bbcode);

			assert.deepEqual(
				ast.getChildren().at(0).toString(),
				'[url=mailto:mymail@bitrix.com]mymail@bitrix.com[/url]',
			);

			assert.deepEqual(
				ast.getChildren().at(2).toString(),
				'[url=mailto:i@vovkabelov.ru]i@vovkabelov.ru[/url]',
			);
		});
	});

	it('should works with img in url', () => {
		const bbcode = stripIndent(`
			[url=https://www.bitrix.com][img]https://bitrix.com/img.png[/img][/url]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.deepEqual(ast.toString(), bbcode);
	});

	it('should not decode square brackets in url', () => {
	    const bbcode = '[url]https://bitrix24.com?test&#91;aa&#93;=11&&#91;&#93;=bb[/url]';

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		assert.deepEqual(ast.toString(), bbcode);
	});
});
