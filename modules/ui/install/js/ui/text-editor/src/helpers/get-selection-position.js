import {
	$isTextNode,
	$isRangeSelection,
	type RangeSelection,
} from 'ui.lexical.core';

import { type TextEditor } from '../text-editor';

export function $getSelectionPosition(
	editor: TextEditor,
	selection: RangeSelection,
	scrollerContainer: HTMLElement,
)
{
	// const range: Range = window.getSelection().getRangeAt(0);
	const range: Range = createRange(selection, editor);
	if (range === null)
	{
		return null;
	}

	const rangeRects = range.getClientRects();
	const isMultiline = rangeRects.length > 1;
	const isBackward = selection.isBackward();
	let rangeRect = isBackward ? rangeRects[0] : rangeRects[rangeRects.length - 1];

	if (selection.isCollapsed() && (!rangeRect || (rangeRect.left === 0 && rangeRect.top === 0)))
	{
		let anchorNode = editor.getElementByKey(selection.anchor.key);
		let anchorOffset = selection.anchor.offset;
		if (anchorNode === null)
		{
			anchorNode = range.startContainer;
			anchorOffset = range.startOffset;
		}

		const targetNode = anchorNode.childNodes[anchorOffset] || anchorNode;

		const position = targetNode.getBoundingClientRect();
		rangeRect = new DOMRect(
			position.left,
			position.top,
			1,
			position.height,
		);
	}

	if (!rangeRect)
	{
		return null;
	}

	const verticalGap = 10;

	const isBodyContainer = scrollerContainer === document.body;
	const scrollLeft = isBodyContainer ? window.pageXOffset : scrollerContainer.scrollLeft;
	const scrollTop = isBodyContainer ? window.pageYOffset : scrollerContainer.scrollTop;

	let left = (isBackward ? rangeRect.left : rangeRect.right) + scrollLeft;
	let top = rangeRect.top + scrollTop;
	let bottom = rangeRect.bottom + scrollTop + verticalGap;

	if (!isBodyContainer)
	{
		const scrollerRect = scrollerContainer.getBoundingClientRect();
		top -= scrollerRect.top;
		left -= scrollerRect.left;
		bottom -= scrollerRect.top;
	}

	return {
		left,
		top,
		bottom,
		isBackward,
		isMultiline,
	};
}

function createRange(selection: RangeSelection, editor: TextEditor): Range | null
{
	if (!$isRangeSelection(selection))
	{
		return null;
	}

	const range = document.createRange();
	const anchorNode = selection.anchor.getNode();
	const focusNode = selection.focus.getNode();

	const anchorKey = anchorNode.getKey();
	const focusKey = focusNode.getKey();

	let anchorDOM: Node | Text | null = editor.getElementByKey(anchorKey);
	let focusDOM: Node | Text | null = editor.getElementByKey(focusKey);
	let anchorOffset = selection.anchor.offset;
	let focusOffset = selection.focus.offset;

	if ($isTextNode(anchorNode))
	{
		anchorDOM = getDOMTextNode(anchorDOM);
	}

	if ($isTextNode(focusNode))
	{
		focusDOM = getDOMTextNode(focusDOM);
	}

	if (anchorDOM === null || focusDOM === null)
	{
		return null;
	}

	if (anchorDOM.nodeName === 'BR')
	{
		[anchorDOM, anchorOffset] = getDOMIndexWithinParent(anchorDOM);
	}

	if (focusDOM.nodeName === 'BR')
	{
		[focusDOM, focusOffset] = getDOMIndexWithinParent(focusDOM);
	}

	const firstChild = anchorDOM.firstChild;

	if (
		anchorDOM === focusDOM
		&& firstChild !== null
		&& firstChild.nodeName === 'BR'
		&& anchorOffset === 0
		&& focusOffset === 0
	)
	{
		focusOffset = 1;
	}

	try
	{
		range.setStart(anchorDOM, anchorOffset);
		range.setEnd(focusDOM, focusOffset);
	}
	catch
	{
		return null;
	}

	if (range.collapsed && (anchorOffset !== focusOffset || anchorKey !== focusKey))
	{
		// Range is backwards, we need to reverse it
		range.setStart(focusDOM, focusOffset);
		range.setEnd(anchorDOM, anchorOffset);
	}

	return range;
}

function getDOMTextNode(element: Node | null): Text | null
{
	let node = element;
	while (node !== null)
	{
		if (node.nodeType === Node.TEXT_NODE)
		{
			return node;
		}

		node = node.firstChild;
	}

	return null;
}

function getDOMIndexWithinParent(node: ChildNode): [ParentNode, number]
{
	const parent = node.parentNode;
	if (parent === null)
	{
		throw new Error('Should never happen');
	}

	return [parent, [...parent.childNodes].indexOf(node)];
}
