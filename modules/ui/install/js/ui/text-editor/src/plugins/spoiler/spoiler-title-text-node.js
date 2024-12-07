/* eslint-disable no-underscore-dangle */

import {
	TextNode,
	$applyNodeReplacement,
	type EditorConfig,
	type LexicalNode,
	type SerializedTextNode,
} from 'ui.lexical.core';

export class SpoilerTitleTextNode extends TextNode
{
	static getType(): string
	{
		return 'spoiler-title-text';
	}

	static clone(node: SpoilerTitleTextNode): SpoilerTitleTextNode
	{
		return new SpoilerTitleTextNode(node.__text, node.__key);
	}

	createDOM(config: EditorConfig): HTMLElement
	{
		return super.createDOM(config);
	}

	static importJSON(serializedNode: SerializedTextNode): SpoilerTitleTextNode
	{
		return $createSpoilerTitleTextNode(serializedNode.text);
	}

	exportJSON(): SerializedTextNode
	{
		return {
			...super.exportJSON(),
			type: 'spoiler-title-text',
		};
	}
}

export function $createSpoilerTitleTextNode(text = ''): SpoilerTitleTextNode
{
	return $applyNodeReplacement(new SpoilerTitleTextNode(text));
}

export function $isSpoilerTitleTextNode(node: LexicalNode | null | undefined): boolean
{
	return node instanceof SpoilerTitleTextNode;
}
