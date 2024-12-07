import { AstProcessor } from '../../src/ast-processor';
import { BBCodeParser } from 'ui.bbcode.parser';

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
	it('AstProcessor.flattenAst', () => {
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

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);
		const flattenedAst = AstProcessor.flattenAst(ast);

		assert.ok(flattenedAst.at(0).getContent() === 'Test text');
		assert.ok(flattenedAst.at(1).getName() === '#linebreak');
		assert.ok(flattenedAst.at(2).getName() === 'table');
		assert.ok(flattenedAst.at(3).getName() === 'tr');
		assert.ok(flattenedAst.at(4).getName() === 'tr');
		assert.ok(flattenedAst.at(5).getName() === 'td');
		assert.ok(flattenedAst.at(6).getName() === 'td');
		assert.ok(flattenedAst.at(7).getContent() === 'Row1, cell1');
		assert.ok(flattenedAst.at(8).getContent() === 'Row1, cell2');
		assert.ok(flattenedAst.at(9).getName() === 'td');
		assert.ok(flattenedAst.at(10).getName() === 'td');
		assert.ok(flattenedAst.at(11).getContent() === 'Row2, cell1');
		assert.ok(flattenedAst.at(12).getContent() === 'Row2, cell2');
	});

	it('AstProcessor.parseSelector', () => {
		const sourceSelector = 'BBCodeElementNode[name="table"] > BBCodeElementNode[name="tr"]';
		const parsedSelector = AstProcessor.parseSelector(sourceSelector);

		assert.deepEqual(parsedSelector.at(0), {nodeName: 'BBCodeElementNode', props: [['name', 'table']]});
		assert.deepEqual(parsedSelector.at(1), '>');
		assert.deepEqual(parsedSelector.at(2), {nodeName: 'BBCodeElementNode', props: [['name', 'tr']]});
	});

	it('AstProcessor.findElements', () => {
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

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		const allTextNodes = AstProcessor.findElements(ast, 'BBCodeTextNode');
		assert.ok(allTextNodes.length === 5);
		assert.ok(allTextNodes.every((node) => node.constructor.name === 'BBCodeTextNode'));

		const allTrNodes = AstProcessor.findElements(ast, 'BBCodeElementNode[name="tr"]');
		assert.ok(allTrNodes.length === 2);
		assert.ok(allTrNodes.every((node) => node.constructor.name === 'BBCodeElementNode' && node.getName() === 'tr'));

		const allTrNodes2 = AstProcessor.findElements(ast, 'BBCodeElementNode[name="table"] > BBCodeElementNode[name="tr"]');
		assert.ok(allTrNodes2.length === 2);
		assert.ok(allTrNodes2.every((node) => node.constructor.name === 'BBCodeElementNode' && node.getName() === 'tr'));
	});

	it('AstProcessor.findElements', () => {
		const bbcode = stripIndent(`
			[code]
			aaa
			[/code]
			
			[code=1]
			Text
			[code=2]
		`);

		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		const allCodeNodes = AstProcessor.findElements(ast, 'BBCodeElementNode[name="code"]');

		assert.ok(allCodeNodes.length === 1);
		assert.ok(allCodeNodes.every((node) => node.constructor.name === 'BBCodeElementNode' && node.getName() === 'code'));
	});
});
