/* eslint-disable no-underscore-dangle */

import { type JsonObject } from 'main.core';
import { $applyNodeReplacement, type EditorConfig, type NodeKey } from 'ui.lexical.core';
import { LinkNode, type LinkAttributes } from 'ui.lexical.link';

export class CustomLinkNode extends LinkNode
{
	constructor(url: string = '', attributes: LinkAttributes = {}, key: NodeKey = null)
	{
		super(url, attributes, key);
	}

	static getType(): string
	{
		return 'custom-link';
	}

	static clone(node: CustomLinkNode): CustomLinkNode
	{
		return new CustomLinkNode(
			node.__url,
			{
				rel: node.__rel,
				target: node.__target,
				title: node.__title,
			},
			node.__key,
		);
	}

	static importJSON(serializedLinkNode): LinkNode
	{
		return super.importJSON(serializedLinkNode);
	}

	exportJSON(): JsonObject
	{
		return {
			...super.exportJSON(),
			type: 'custom-link',
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

export function $createCustomLinkNode(url: string = '', attributes: LinkAttributes = {}): CustomLinkNode
{
	return $applyNodeReplacement(new CustomLinkNode(url, attributes));
}
