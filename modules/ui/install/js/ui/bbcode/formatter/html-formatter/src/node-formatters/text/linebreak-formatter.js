import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';

export class LinebreakNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {}) {
		super({
			name: '#linebreak',
			convert({ node }: ConvertCallbackOptions): Text | HTMLBRElement {
				return document.createElement('br');
			},
			...options,
		});
	}
}
