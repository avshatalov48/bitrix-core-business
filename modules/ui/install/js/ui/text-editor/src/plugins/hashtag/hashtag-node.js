/* eslint-disable no-underscore-dangle */
import { Dom, Type } from 'main.core';

import {
	TextNode,
	$applyNodeReplacement,
	type EditorConfig,
	type LexicalNode,
	type NodeKey,
	type SerializedTextNode,
} from 'ui.lexical.core';

export class HashtagNode extends TextNode
{
	static getType(): string
	{
		return 'hashtag';
	}

	static clone(node: HashtagNode): HashtagNode
	{
		return new HashtagNode(node.__text, node.__key);
	}

	constructor(text: string, key?: NodeKey)
	{
		super(text, key);
	}

	createDOM(config: EditorConfig): HTMLElement
	{
		const element = super.createDOM(config);
		if (Type.isStringFilled(config?.theme?.hashtag))
		{
			Dom.addClass(element, config.theme.hashtag);
		}

		return element;
	}

	static importJSON(serializedNode: SerializedTextNode): HashtagNode
	{
		const node = $createHashtagNode(serializedNode.text);
		node.setFormat(serializedNode.format);
		node.setDetail(serializedNode.detail);
		node.setMode(serializedNode.mode);
		node.setStyle(serializedNode.style);

		return node;
	}

	exportJSON(): SerializedTextNode
	{
		return {
			...super.exportJSON(),
			type: 'hashtag',
		};
	}

	canInsertTextBefore(): boolean
	{
		return false;
	}

	isTextEntity(): true
	{
		return true;
	}
}

export function $createHashtagNode(text = ''): HashtagNode
{
	return $applyNodeReplacement(new HashtagNode(text));
}

export function $isHashtagNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof HashtagNode;
}
