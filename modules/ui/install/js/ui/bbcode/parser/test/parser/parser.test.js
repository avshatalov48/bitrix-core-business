import { Parser } from '../../src/parser';
import { Node } from 'ui.bbcode.model';

const stripIndent = (source) => {
	const lines = source.split('\n').slice(1, -1);
	const minIndent = Math.min(
		...lines.map((line) => {
			return line.split('\t').length -1;
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
	it('Parse text with base-formatting', function() {
		const bbcode = stripIndent(`
			Test text [b]bold[/b], [i]italic[/i], [u]underline[/u], [s]strike[/s]
			[img width=20 height=20]/path/to/image.png[/img]
			[url=https://bitrix24.com]Bitrix24[/url]
		`);

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getType() === Node.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 12);

		assert.ok(ast.getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'Test text ');

		assert.ok(ast.getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(1).getName() === 'b');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getContent() === 'bold');

		assert.ok(ast.getChildren().at(2).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === ', ');

		assert.ok(ast.getChildren().at(3).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(3).getName() === 'i');
		assert.ok(ast.getChildren().at(3).getChildren().at(0).getContent() === 'italic');

		assert.ok(ast.getChildren().at(4).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(4).getContent() === ', ');

		assert.ok(ast.getChildren().at(5).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(5).getName() === 'u');
		assert.ok(ast.getChildren().at(5).getChildren().at(0).getContent() === 'underline');

		assert.ok(ast.getChildren().at(6).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(6).getContent() === ', ');

		assert.ok(ast.getChildren().at(7).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(7).getName() === 's');
		assert.ok(ast.getChildren().at(7).getChildren().at(0).getContent() === 'strike');

		assert.ok(ast.getChildren().at(8).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(8).getContent() === '\n');

		assert.ok(ast.getChildren().at(9).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(9).getName() === 'img');
		assert.deepEqual(ast.getChildren().at(9).getAttributes(), {width: '20', height: '20'});
		assert.ok(ast.getChildren().at(9).getChildren().at(0).getContent() === '/path/to/image.png');

		assert.ok(ast.getChildren().at(10).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(10).getContent() === '\n');

		assert.ok(ast.getChildren().at(11).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(11).getName() === 'url');
		assert.ok(ast.getChildren().at(11).getValue(), 'https://bitrix24.com');
		assert.ok(ast.getChildren().at(11).getChildren().at(0).getContent() === 'Bitrix24');
	});

	it('should recognize line breaks', function() {
		const bbcode = 'ABC \n DEF \n [b]bold \n bold[/b] \n [i]italic[/i]';
		const parser = new Parser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getType() === Node.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 10);

		assert.ok(ast.getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'ABC ');

		assert.ok(ast.getChildren().at(1).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getContent() === '\n');

		assert.ok(ast.getChildren().at(2).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === ' DEF ');

		assert.ok(ast.getChildren().at(3).getType() === Node.TEXT_NODE);

		assert.ok(ast.getChildren().at(4).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(4).getContent() === ' ');

		assert.ok(ast.getChildren().at(5).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(5).getName() === 'b');
		assert.ok(ast.getChildren().at(5).getChildrenCount() === 3);
		assert.ok(ast.getChildren().at(5).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getChildren().at(0).getContent() === 'bold ');
		assert.ok(ast.getChildren().at(5).getChildren().at(1).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getChildren().at(2).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getChildren().at(2).getContent() === ' bold');

		assert.ok(ast.getChildren().at(6).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(6).getContent() === ' ');

		assert.ok(ast.getChildren().at(7).getType() === Node.TEXT_NODE);

		assert.ok(ast.getChildren().at(8).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(8).getContent() === ' ');

		assert.ok(ast.getChildren().at(9).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(9).getName() === 'i');
		assert.ok(ast.getChildren().at(9).getChildrenCount() === 1);
		assert.ok(ast.getChildren().at(9).getChildren().at(0).getContent() === 'italic');
	});

	it('should recognize line breaks at the start', function() {
		let bbcode = '\n';
		let parser = new Parser();
		let ast = parser.parse(bbcode);
		assert.ok(ast.getType() === Node.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 1);
		assert.ok(ast.getChildren().at(0).getType() === Node.TEXT_NODE);

		bbcode = '\n ABC';
		parser = new Parser();
		ast = parser.parse(bbcode);
		assert.ok(ast.getType() === Node.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 2);
		assert.ok(ast.getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getContent() === ' ABC');

		bbcode = '\n\nABC';
		parser = new Parser();
		ast = parser.parse(bbcode);
		assert.ok(ast.getType() === Node.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 3);
		assert.ok(ast.getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === 'ABC');
	});

	it('should recognize line breaks at the end', function() {
		let bbcode = 'ABC\n';
		let parser = new Parser();
		let ast = parser.parse(bbcode);
		assert.ok(ast.getType() === Node.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 2);
		assert.ok(ast.getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'ABC');
		assert.ok(ast.getChildren().at(1).getType() === Node.TEXT_NODE);

		bbcode = 'ABC\n\n';
		parser = new Parser();
		ast = parser.parse(bbcode);
		assert.ok(ast.getType() === Node.ROOT_NODE);
		assert.ok(ast.getChildrenCount() === 3);
		assert.ok(ast.getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(0).getContent() === 'ABC');
		assert.ok(ast.getChildren().at(1).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getType() === Node.TEXT_NODE);
	});


	it('should parse text up to the end', function() {
		let bbcode = 'A\n'
			+ 'B\n'
			+ '\n'
			+ '[code]\n'
			+ '\n'
			+ 'A\n'
			+ 'B'
		;

		let parser = new Parser();
		let ast = parser.parse(bbcode);

		assert.ok(ast.getChildren().at(-1).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(-1).getContent() === 'B');
	});

	it('should have node types', function() {
		let bbcode = 'Test text [b]bold[/b]\n';

		let parser = new Parser();
		let root = parser.parse(bbcode);

		assert.ok(root.getType() === Node.ROOT_NODE);
		assert.ok(root.getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(root.getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(root.getChildren().at(1).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(root.getChildren().at(2).getType() === Node.TEXT_NODE);
	});

	it('should works with [code]', function() {
		const bbcode = stripIndent(`
			[code]
				[b]Bold[/b]
				function Test(...args)
				{
					console.log(...args);
				}
			[/code]
		`);

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		assert.deepEqual(ast.toString(), `${bbcode}`);
	});

	it('should parses multi-level lists', function() {
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

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		const listNode = ast.getChildren().at(0);

		assert.ok(listNode.getType() === Node.ELEMENT_NODE);
		assert.ok(listNode.getChildrenCount() === 3);
		assert.ok(listNode.getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(2).getType() === Node.ELEMENT_NODE);

		const subList = listNode.getChildren().at(0).getChildren().at(-2);
		assert.ok(subList.getType() === Node.ELEMENT_NODE);
		assert.ok(subList.getChildrenCount() === 2);
		assert.ok(subList.getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(subList.getChildren().at(1).getType() === Node.ELEMENT_NODE);
	});

	it('should works with tags in list item', function() {
		const bbcode = stripIndent(`
			[list]
				[*]Item [b]bold[/b] 1
				[*]Item 2 [i]italic[/i]
				[*]Item 3
			[/list]
		`);

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		const listNode = ast.getChildren().at(0);
		assert.ok(listNode.getType() === Node.ELEMENT_NODE);
		assert.ok(listNode.getChildrenCount() === 3);
		assert.ok(listNode.getChildren().at(0).getType() === Node.ELEMENT_NODE);

		assert.ok(listNode.getChildren().at(0).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(listNode.getChildren().at(0).getChildren().at(0).getContent() === 'Item ');
		assert.ok(listNode.getChildren().at(0).getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(0).getChildren().at(1).getName() === 'b');
		assert.ok(listNode.getChildren().at(0).getChildren().at(1).getContent() === 'bold');
		assert.ok(listNode.getChildren().at(0).getChildren().at(2).getType() === Node.TEXT_NODE);
		assert.ok(listNode.getChildren().at(0).getChildren().at(2).getContent() === ' 1');

		assert.ok(listNode.getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(listNode.getChildren().at(1).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(listNode.getChildren().at(1).getChildren().at(0).getContent() === 'Item 2 ');
		assert.ok(listNode.getChildren().at(1).getChildren().at(1).getName() === 'i');
		assert.ok(listNode.getChildren().at(1).getChildren().at(1).getContent() === 'italic');

		assert.ok(listNode.getChildren().at(2).getType() === Node.ELEMENT_NODE, 'Invalid list item node');
		assert.ok(listNode.getChildren().at(2).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(listNode.getChildren().at(2).getChildren().at(0).getContent() === 'Item 3');
	});

	it('parseText', function() {
		const parser = new Parser();
		const parent = new Node();
		const source = '\n\t\ttest\nnewline';

		const result = parser.parseText(source, parent);

		assert.ok(result.at(0).getType() === Node.TEXT_NODE);
		assert.ok(result.at(0).getContent() === '\n');

		assert.ok(result.at(1).getType() === Node.TEXT_NODE);
		assert.ok(result.at(1).getContent() === '\t');

		assert.ok(result.at(2).getType() === Node.TEXT_NODE);
		assert.ok(result.at(2).getContent() === '\t');

		assert.ok(result.at(3).getType() === Node.TEXT_NODE);
		assert.ok(result.at(3).getContent() === 'test');

		assert.ok(result.at(4).getType() === Node.TEXT_NODE);
		assert.ok(result.at(4).getContent() === '\n');

		assert.ok(result.at(5).getType() === Node.TEXT_NODE);
		assert.ok(result.at(5).getContent() === 'newline');
	});

	it('should works with table', function() {
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

		const parser = new Parser();
		const ast = parser.parse(bbcode);
		const table = ast.getChildren().at(0);

		assert.ok(table.getType() === Node.ELEMENT_NODE);
		assert.ok(table.getName() === 'table');
		assert.ok(table.getChildrenCount() === 2);

		const tr1 = table.getChildren().at(0);
		assert.ok(tr1.getType() === Node.ELEMENT_NODE);
		assert.ok(tr1.getName() === 'tr');
		assert.ok(tr1.getChildrenCount() === 3);
		assert.ok(tr1.getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(0).getName() === 'td');
		assert.ok(tr1.getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getName() === 'b');
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(tr1.getChildren().at(0).getChildren().at(0).getChildren().at(0).getContent() === 'Head cell 1');

		assert.ok(tr1.getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(1).getName() === 'td');
		assert.ok(tr1.getChildren().at(1).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getName() === 'i');
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(tr1.getChildren().at(1).getChildren().at(0).getChildren().at(0).getContent() === 'Head cell 2');

		assert.ok(tr1.getChildren().at(2).getType() === Node.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(2).getName() === 'td');
		assert.ok(tr1.getChildren().at(2).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getName() === 's');
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(tr1.getChildren().at(2).getChildren().at(0).getChildren().at(0).getContent() === 'Head cell 3');

		const tr2 = table.getChildren().at(1);
		assert.ok(tr2.getType() === Node.ELEMENT_NODE);
		assert.ok(tr2.getName() === 'tr');
		assert.ok(tr2.getChildrenCount() === 3);
		assert.ok(tr2.getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(tr2.getChildren().at(0).getName() === 'td');
		assert.ok(tr2.getChildren().at(0).getChildrenCount() === 1);
		assert.ok(tr2.getChildren().at(0).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(tr2.getChildren().at(0).getChildren().at(0).getContent() === 'Body cell 1/1');

		assert.ok(tr2.getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(tr2.getChildren().at(1).getName() === 'td');
		assert.ok(tr2.getChildren().at(1).getChildrenCount() === 1);
		assert.ok(tr2.getChildren().at(1).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(tr2.getChildren().at(1).getChildren().at(0).getContent() === 'Body cell 1/2');

		assert.ok(tr2.getChildren().at(2).getType() === Node.ELEMENT_NODE);
		assert.ok(tr2.getChildren().at(2).getName() === 'td');
		assert.ok(tr2.getChildren().at(2).getChildrenCount() === 1);
		assert.ok(tr2.getChildren().at(2).getChildren().at(0).getType() === Node.TEXT_NODE);
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

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildrenCount() === 1);

		const list = ast.getChildren().at(0);
		assert.ok(list.getType() === Node.ELEMENT_NODE);
		assert.ok(list.getName() === 'list');
		assert.ok(list.getChildrenCount() === 3);
		assert.ok(list.getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(list.getChildren().at(0).getName() === '*');
		assert.ok(list.getChildren().at(0).getChildrenCount() === 4);
		assert.ok(list.getChildren().at(0).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(list.getChildren().at(0).getChildren().at(0).getContent() === 'List item ');
		assert.ok(list.getChildren().at(0).getChildren().at(1).getType() === Node.ELEMENT_NODE);
		assert.ok(list.getChildren().at(0).getChildren().at(1).getName() === 'b');
		assert.ok(list.getChildren().at(0).getChildren().at(1).getChildrenCount() === 1);
		assert.ok(list.getChildren().at(0).getChildren().at(1).getChildren().at(0).getContent() === 'bold');
		assert.ok(list.getChildren().at(0).getChildren().at(2).getType() === Node.TEXT_NODE);
		assert.ok(list.getChildren().at(0).getChildren().at(2).getContent() === ' #1');
		assert.ok(list.getChildren().at(0).getChildren().at(3).getType() === Node.TEXT_NODE);
		assert.ok(list.getChildren().at(0).getChildren().at(3).getContent() === '\n');
	});

	it('Should works with [disk] tag', () => {
		const bbcode = stripIndent(`
			[disk file id=11] First line text
			[b]bold[/b][disk file id=22]
			[p][disk file id=33][/p]
		`);

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(0).getName() === 'disk');
		assert.ok(ast.getChildren().at(0).isVoid() === true);
		assert.deepEqual(ast.getChildren().at(0).getAttributes(), {file: '', id: '11'});
		assert.ok(ast.getChildren().at(1).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(1).getContent() === ' First line text');
		assert.ok(ast.getChildren().at(2).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(2).getContent() === '\n');
		assert.ok(ast.getChildren().at(3).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(3).getName() === 'b');
		assert.ok(ast.getChildren().at(3).getChildrenCount() === 1);
		assert.ok(ast.getChildren().at(3).getChildren().at(0).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(3).getChildren().at(0).getContent() === 'bold');
		assert.ok(ast.getChildren().at(4).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(4).getName() === 'disk');
		assert.ok(ast.getChildren().at(4).isVoid() === true);
		assert.deepEqual(ast.getChildren().at(4).getAttributes(), {file: '', id: '22'});
		assert.ok(ast.getChildren().at(5).getType() === Node.TEXT_NODE);
		assert.ok(ast.getChildren().at(5).getContent() === '\n');
		assert.ok(ast.getChildren().at(6).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(6).getName() === 'p');
		assert.ok(ast.getChildren().at(6).getChildren().at(0).getType() === Node.ELEMENT_NODE);
		assert.ok(ast.getChildren().at(6).getChildren().at(0).getName() === 'disk');
	});

	it('Should format code block', () => {
		const bbcode = '[code]Use code tag for [b]code[/b][/code]';
		const parser = new Parser();
		const ast = parser.parse(bbcode);

		const result = '[code]\nUse code tag for [b]code[/b]\n[/code]';

		assert.deepEqual(ast.toString(), result);
	});

	it('Should format list block', () => {
		const bbcode = '[list][*]One[*]Two[*]Three[/list]';
		const parser = new Parser();
		const ast = parser.parse(bbcode);
		const result = '[list]\n[*]One\n[*]Two\n[*]Three\n[/list]';

		assert.ok(ast.toString(), result);
	});

	it('One line break must be added between two block elements', () => {
		const bbcode = `[p]\ntext\n[/p][list]\n[*]one\n[/list]`;
		const parser = new Parser();
		const ast = parser.parse(bbcode);
		const result = `[p]\ntext\n[/p]\n[list]\n[*]one\n[/list]`;

		assert.equal(ast.toString(), result);
	});

	it('One line break must be added between text and block element', () => {
		const bbcode = `Any text[p]\ntext\n[/p]`;
		const parser = new Parser();
		const ast = parser.parse(bbcode);
		const result = `Any text\n[p]\ntext\n[/p]`;

		assert.equal(ast.toString(), result);
	});

	it('One line break should be added after the block element if there is text behind it', () => {
		const bbcode = `[p]\ntext\n[/p]Any text`;
		const parser = new Parser();
		const ast = parser.parse(bbcode);
		const result = `[p]\ntext\n[/p]\nAny text`;

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

		const parser = new Parser();
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

	it('An invalid descendant must be added to a higher level (#2)', () => {
		const bbcode = stripIndent(`
			[table]
				[tr]
					[td]cell1[table][/table][/td]
					[td]cell2[/td]
				[/tr]
			[/table]
		`);

		const parser = new Parser();
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
});
