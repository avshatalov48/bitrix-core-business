import { Parser } from '../../src/parser';

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

describe('Parser', () => {
	it('Parse text with base-formatting', async () => {
		const bbcode = stripIndent(`
			Test text [b]bold[/b], [i]italic[/i], [u]underline[/u], [s]strike[/s]
			[img width=20 height=20]/path/to/image.png[/img]
			[url=https://bitrix24.com]Bitrix24[/url]
		`);

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		assert.ok(ast.constructor.name === 'RootNode');
		assert.ok(ast.getChildren().length === 12);

		assert.ok(ast.getChildren().at(0).constructor.name === 'TextNode');
		assert.ok(ast.getChildren().at(0).getContent() === 'Test text ');

		assert.ok(ast.getChildren().at(1).constructor.name === 'Node');
		assert.ok(ast.getChildren().at(1).getName() === 'b');
		assert.ok(ast.getChildren().at(1).getChildren().at(0).getContent() === 'bold');

		assert.ok(ast.getChildren().at(2).constructor.name === 'TextNode');
		assert.ok(ast.getChildren().at(2).getContent() === ', ');

		assert.ok(ast.getChildren().at(3).constructor.name === 'Node');
		assert.ok(ast.getChildren().at(3).getName() === 'i');
		assert.ok(ast.getChildren().at(3).getChildren().at(0).getContent() === 'italic');

		assert.ok(ast.getChildren().at(4).constructor.name === 'TextNode');
		assert.ok(ast.getChildren().at(4).getContent() === ', ');

		assert.ok(ast.getChildren().at(5).constructor.name === 'Node');
		assert.ok(ast.getChildren().at(5).getName() === 'u');
		assert.ok(ast.getChildren().at(5).getChildren().at(0).getContent() === 'underline');

		assert.ok(ast.getChildren().at(6).constructor.name === 'TextNode');
		assert.ok(ast.getChildren().at(6).getContent() === ', ');

		assert.ok(ast.getChildren().at(7).constructor.name === 'Node');
		assert.ok(ast.getChildren().at(7).getName() === 's');
		assert.ok(ast.getChildren().at(7).getChildren().at(0).getContent() === 'strike');

		assert.ok(ast.getChildren().at(8).constructor.name === 'NewLineNode');
		assert.ok(ast.getChildren().at(8).getContent() === '\n');

		assert.ok(ast.getChildren().at(9).constructor.name === 'Node');
		assert.ok(ast.getChildren().at(9).getName() === 'img');
		assert.deepEqual(ast.getChildren().at(9).getAttributes(), {width: '20', height: '20'});
		assert.ok(ast.getChildren().at(9).getChildren().at(0).getContent() === '/path/to/image.png');

		assert.ok(ast.getChildren().at(10).constructor.name === 'NewLineNode');
		assert.ok(ast.getChildren().at(10).getContent() === '\n');

		assert.ok(ast.getChildren().at(11).constructor.name === 'Node');
		assert.ok(ast.getChildren().at(11).getName() === 'url');
		assert.ok(ast.getChildren().at(11).getValue(), 'https://bitrix24.com');
		assert.ok(ast.getChildren().at(11).getChildren().at(0).getContent() === 'Bitrix24');
	});
});