/* eslint-disable no-underscore-dangle */

import { type JsonObject } from 'main.core';
import { $applyNodeReplacement, type EditorConfig, type NodeKey } from 'ui.lexical.core';
import { AutoLinkNode, type LinkAttributes } from 'ui.lexical.link';

export class CustomAutoLinkNode extends AutoLinkNode
{
	constructor(url: string = '', attributes: LinkAttributes = {}, key: NodeKey = null)
	{
		super(url, attributes, key);
	}

	static getType(): string
	{
		return 'custom-autolink';
	}

	static clone(node: CustomAutoLinkNode): CustomAutoLinkNode
	{
		return new CustomAutoLinkNode(
			node.__url,
			{
				isUnlinked: node.__isUnlinked,
				rel: node.__rel,
				target: node.__target,
				title: node.__title,
			},
			node.__key,
		);
	}

	static importJSON(serializedLinkNode): AutoLinkNode
	{
		return super.importJSON(serializedLinkNode);
	}

	exportJSON(): JsonObject
	{
		return {
			...super.exportJSON(),
			type: 'custom-autolink',
			version: 1,
		};
	}

	createDOM(config: EditorConfig): HTMLElement
	{
		const element = super.createDOM(config);

		element.setAttribute('data-slider-ignore-autobinding', 'true');

		return element;
	}
}

export function $createCustomAutoLinkNode(url: string = '', attributes: LinkAttributes = {}): CustomAutoLinkNode
{
	return $applyNodeReplacement(new CustomAutoLinkNode(url, attributes));
}
