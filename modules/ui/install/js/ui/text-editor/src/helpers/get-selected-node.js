import { type ElementNode, type RangeSelection, type TextNode } from 'ui.lexical.core';
import { $isAtNodeEnd } from 'ui.lexical.selection';

export function getSelectedNode(selection: RangeSelection): TextNode | ElementNode
{
	const anchor = selection.anchor;
	const focus = selection.focus;
	const anchorNode = selection.anchor.getNode();
	const focusNode = selection.focus.getNode();
	if (anchorNode === focusNode)
	{
		return anchorNode;
	}

	const isBackward = selection.isBackward();
	if (isBackward)
	{
		return $isAtNodeEnd(focus) ? anchorNode : focusNode;
	}

	return $isAtNodeEnd(anchor) ? anchorNode : focusNode;
}
