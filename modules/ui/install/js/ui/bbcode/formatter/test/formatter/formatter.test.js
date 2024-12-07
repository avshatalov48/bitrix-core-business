import { BBCodeParser } from 'ui.bbcode.parser';
import { BBCodeNewLineNode, BBCodeNode, BBCodeTextNode } from 'ui.bbcode.model';
import { BBCodeFormatter } from '../../src/formatter';

xdescribe('Formatter', () => {
	it('Should work', () => {
		const bbcode = `text [p]paragraph [b]bold[/b][/p]`;
		const parser = new BBCodeParser();
		const ast = parser.parse(bbcode);

		const formatter = new BBCodeFormatter({
			formatters: [
				{
					name: '#root',
					convert() {
						return document.createDocumentFragment();
					},
				},
				{
					name: '#text',
					convert(node: BBCodeTextNode) {
						return document.createTextNode(node.getContent());
					},
				},
				{
					name: '#linebreak',
					convert(node: BBCodeNewLineNode) {
						return document.createTextNode(node.getContent());
					},
				},
				{
					name: 'p',
					convert(node: BBCodeNode) {
						return document.createElement(node.getName());
					},
				},
				{
					name: 'b',
					convert(node: BBCodeNode) {
						return document.createElement(node.getName());
					},
				},
			],
		});
		const result = formatter.format(ast);

		console.log(result);
	});
});
