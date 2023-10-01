import { AstProcessor } from '../../src/ast-processor';
import { Parser } from 'ui.bbcode.parser';

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

describe('ui.bbcode.ast-processor', () => {
	it('AstProcessor.flattenAst', async () => {
		const bbcode = stripIndent(`
			Test text
			[table]
				[tr]
					[td]Row1, cell1[/td]
					[td]Row1, cell2[/td]
				[/tr]
				[tr]
					[td]Row2, cell1[/td]
					[td]Row2, cell2[/td]
				[/tr]
			[/table]
		`);

		const parser = new Parser();
		const ast = parser.parse(bbcode);
		const flattenedAst = AstProcessor.flattenAst(ast);

		assert.ok(flattenedAst.at(0).getContent() === 'Test text');
		assert.ok(flattenedAst.at(1).getContent() === '\n');
		assert.ok(flattenedAst.at(2).getName() === 'table');
		assert.ok(flattenedAst.at(3).getContent() === '\n');
		assert.ok(flattenedAst.at(4).getContent() === '\t');
		assert.ok(flattenedAst.at(5).getName() === 'tr');
		assert.ok(flattenedAst.at(6).getContent() === '\n');
		assert.ok(flattenedAst.at(7).getContent() === '\t');
		assert.ok(flattenedAst.at(8).getName() === 'tr');
		assert.ok(flattenedAst.at(9).getContent() === '\n');
		assert.ok(flattenedAst.at(10).getContent() === '\n');
		assert.ok(flattenedAst.at(11).getContent() === '\t\t');
		assert.ok(flattenedAst.at(12).getName() === 'td');
		assert.ok(flattenedAst.at(13).getContent() === '\n');
		assert.ok(flattenedAst.at(14).getContent() === '\t\t');
		assert.ok(flattenedAst.at(15).getName() === 'td');
		assert.ok(flattenedAst.at(16).getContent() === '\n');
		assert.ok(flattenedAst.at(17).getContent() === '\t');
		assert.ok(flattenedAst.at(18).getContent() === 'Row1, cell1');
		assert.ok(flattenedAst.at(19).getContent() === 'Row1, cell2');
		assert.ok(flattenedAst.at(20).getContent() === '\n');
		assert.ok(flattenedAst.at(21).getContent() === '\t\t');
		assert.ok(flattenedAst.at(22).getName() === 'td');
		assert.ok(flattenedAst.at(23).getContent() === '\n');
		assert.ok(flattenedAst.at(24).getContent() === '\t\t');
		assert.ok(flattenedAst.at(25).getName() === 'td');
		assert.ok(flattenedAst.at(26).getContent() === '\n');
		assert.ok(flattenedAst.at(27).getContent() === '\t');
		assert.ok(flattenedAst.at(28).getContent() === 'Row2, cell1');
		assert.ok(flattenedAst.at(29).getContent() === 'Row2, cell2');
	});

	it('AstProcessor.parseSelector', async () => {
		const sourceSelector = 'Node[name="table"] > Node[name="tr"]';
		const parsedSelector = AstProcessor.parseSelector(sourceSelector);

		assert.deepEqual(parsedSelector.at(0), {nodeName: 'Node', props: [['name', 'table']]});
		assert.deepEqual(parsedSelector.at(1), '>');
		assert.deepEqual(parsedSelector.at(2), {nodeName: 'Node', props: [['name', 'tr']]});
	});

	it('AstProcessor.findElements', async () => {
		const bbcode = stripIndent(`
			Test text
			[table]
				[tr]
					[td]Row1, cell1[/td]
					[td]Row1, cell2[/td]
				[/tr]
				[tr]
					[td]Row2, cell1[/td]
					[td]Row2, cell2[/td]
				[/tr]
			[/table]
		`);

		const parser = new Parser();
		const ast = parser.parse(bbcode);

		const allTextNodes = AstProcessor.findElements(ast, 'TextNode');
		assert.ok(allTextNodes.length === 13);
		assert.ok(allTextNodes.every((node) => node.constructor.name === 'TextNode'));

		const allTrNodes = AstProcessor.findElements(ast, 'Node[name="tr"]');
		assert.ok(allTrNodes.length === 2);
		assert.ok(allTrNodes.every((node) => node.constructor.name === 'Node' && node.getName() === 'tr'));

		const allTrNodes2 = AstProcessor.findElements(ast, 'Node[name="table"] > Node[name="tr"]');
		assert.ok(allTrNodes2.length === 2);
		assert.ok(allTrNodes2.every((node) => node.constructor.name === 'Node' && node.getName() === 'tr'));
	});
});
