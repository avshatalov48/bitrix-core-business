import { NodeFormatter, type NodeFormatterOptions, type ConvertCallbackOptions } from 'ui.bbcode.formatter';
import { type BBCodeNode } from 'ui.bbcode.model';
import { type Smiley, SmileyManager, SmileyParser } from 'ui.smiley';
import { findParent } from '../../helpers/find-parent';

export class TextNodeFormatter extends NodeFormatter
{
	#smileyParser: SmileyParser = null;

	constructor(options: NodeFormatterOptions = {}) {
		super({
			name: '#text',
			convert({ node }: ConvertCallbackOptions): Text {
				const text = node.toString({ encode: false });
				if (findParent(node, (parent: BBCodeNode) => parent.getName() === 'code'))
				{
					return document.createTextNode(text);
				}

				const splits: Array<{ start: number, end: number }> = this.#smileyParser.parse(text);
				if (splits.length === 0)
				{
					return document.createTextNode(text);
				}

				// console.log('splits', splits);

				const fragment = document.createDocumentFragment();
				let currentIndex = 0;
				for (const split of splits)
				{
					if (currentIndex < split.start)
					{
						const chunk = document.createTextNode(text.slice(currentIndex, split.start));
						fragment.appendChild(chunk);
					}

					const typing = text.slice(split.start, split.end);
					const smiley = SmileyManager.get(typing);
					if (smiley === null)
					{
						fragment.appendChild(document.createTextNode(typing));
					}
					else
					{
						fragment.appendChild(this.createImg(smiley));
					}

					currentIndex = split.end;
				}

				if (currentIndex < text.length)
				{
					const tail = document.createTextNode(text.slice(currentIndex));
					fragment.appendChild(tail);
				}

				return fragment;
			},
			...options,
		});

		this.#smileyParser = new SmileyParser(SmileyManager.getAll());
	}

	createImg(smiley: Smiley): HTMLImageElement
	{
		const img: HTMLImageElement = document.createElement('img');
		img.src = encodeURI(smiley.getImage());
		img.className = 'ui-typography-smiley';
		img.alt = smiley.getTyping();
		if (smiley.getWidth() > 0 && smiley.getHeight() > 0)
		{
			img.width = smiley.getWidth();
			img.height = smiley.getHeight();
		}

		return img;
	}
}
