import { Tag } from 'main.core';
import {
	NodeFormatter,
	type NodeFormatterOptions,
	type BeforeConvertCallbackOptions,
	type AfterCallbackOptions,
} from 'ui.bbcode.formatter';

import { normalizeTextNodes } from '../../helpers/normalize-text-nodes';
import { type BBCodeNode } from 'ui.bbcode.model';

export class RootNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: '#root',
			convert(): DocumentFragment {
				return document.createDocumentFragment();
			},
			before({ node }: BeforeConvertCallbackOptions): BBCodeNode {
				return normalizeTextNodes(node);
			},
			after({ element, formatter }: AfterCallbackOptions): HTMLElement {
				const mode = formatter.getContainerMode();
				if (mode === 'void' || mode === 'collapsed')
				{
					const container = Tag.render`<div class="ui-typography-container --${mode}"></div>`;
					container.appendChild(element);

					return container;
				}

				return element;
			},
			...options,
		});
	}
}
