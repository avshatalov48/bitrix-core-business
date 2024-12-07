/* eslint-disable no-underscore-dangle */

import { $isElementNode } from 'ui.lexical.core';
import {
	$getRoot,
	$getSelection,
	$isNodeSelection,
	$isRangeSelection,
	$isTextNode,
	type LexicalNode,
	type BaseSelection,
	type LexicalEditor,
} from 'ui.lexical.core';

import { $isTableSelection } from 'ui.lexical.table';
import { NON_SINGLE_WIDTH_CHARS_REGEX, SYMBOLS } from './constants';
import { printNode } from './print-node';
import { printNodeSelection } from './print-node-selection';
import { printRangeSelection } from './print-range-selection';
import { printTableSelection } from './print-table-selection';
import { visitTree } from './visit-tree';

import { type TextEditor } from '../text-editor';

export function generateContent(editor: TextEditor | LexicalEditor): string
{
	const editorState = editor.getEditorState();

	// if (exportDOM)
	// {
	// 	let htmlString = '';
	// 	editorState.read(() => {
	// 		htmlString = printPrettyHTML($generateHtmlFromNodes(editor));
	// 	});
	// 	return htmlString;
	// }

	let res = ' root\n';

	const selectionString = editorState.read(() => {
		const selection = $getSelection();
		visitTree($getRoot(), (node: LexicalNode, indent: Array<string>) => {
			const nodeKey = node.getKey();
			const nodeKeyDisplay = `(${nodeKey})`;
			const typeDisplay = node.getType() || '';
			const isSelected = node.isSelected();

			res += `${isSelected ? SYMBOLS.selectedLine : ' '} ${indent.join(
				' ',
			)} ${nodeKeyDisplay} ${typeDisplay} ${printNode(node)}\n`;

			res += printSelectedCharsLine({
				indent,
				isSelected,
				node,
				nodeKeyDisplay,
				selection,
				typeDisplay,
			});
		});

		if (selection === null)
		{
			return ': null';
		}

		if ($isRangeSelection(selection))
		{
			return printRangeSelection(selection);
		}

		if ($isTableSelection(selection))
		{
			return printTableSelection(selection);
		}

		return printNodeSelection(selection);
	});

	res += `\n selection${selectionString}`;

	return res;
}

function printSelectedCharsLine({
	indent,
	isSelected,
	node,
	nodeKeyDisplay,
	selection,
	typeDisplay,
}: {
	indent: Array<string>;
	isSelected: boolean;
	node: LexicalNode;
	nodeKeyDisplay: string;
	selection: BaseSelection | null;
	typeDisplay: string;
}): string
{
	// No selection or node is not selected.
	if (
		!$isTextNode(node)
		|| !$isRangeSelection(selection)
		|| !isSelected
		|| $isElementNode(node)
	)
	{
		return '';
	}

	// No selected characters.
	const anchor = selection.anchor;
	const focus = selection.focus;

	if (
		node.getTextContent() === ''
		|| (anchor.getNode() === selection.focus.getNode()
			&& anchor.offset === focus.offset)
	)
	{
		return '';
	}

	const [start, end] = $getSelectionStartEnd(node, selection);

	if (start === end)
	{
		return '';
	}

	const selectionLastIndent = (
		indent[indent.length - 1] === SYMBOLS.hasNextSibling
			? SYMBOLS.ancestorHasNextSibling
			: SYMBOLS.ancestorIsLastChild
	);

	const indentionChars = [...indent.slice(0, -1), selectionLastIndent];
	const unselectedChars = Array.from({ length: start + 1 }).fill(' ');
	const selectedChars = Array.from({ length: end - start }).fill(SYMBOLS.selectedChar);
	const paddingLength = typeDisplay.length + 3; // 2 for the spaces around + 1 for the double quote.
	const nodePrintSpaces = Array.from({ length: nodeKeyDisplay.length + paddingLength }).fill(' ');

	return (
		`${[
			SYMBOLS.selectedLine,
			indentionChars.join(' '),
			[...nodePrintSpaces, ...unselectedChars, ...selectedChars].join(''),
		].join(' ')}\n`
	);
}

function $getSelectionStartEnd(node: LexicalNode, selection: BaseSelection): [number, number]
{
	const anchorAndFocus = selection.getStartEndPoints();
	if ($isNodeSelection(selection) || anchorAndFocus === null)
	{
		return [-1, -1];
	}

	const [anchor, focus] = anchorAndFocus;
	const textContent = node.getTextContent();
	const textLength = textContent.length;

	let start = -1;
	let end = -1;

	// Only one node is being selected.
	if (anchor.type === 'text' && focus.type === 'text')
	{
		const anchorNode = anchor.getNode();
		const focusNode = focus.getNode();

		if (
			anchorNode === focusNode
			&& node === anchorNode
			&& anchor.offset !== focus.offset
		)
		{
			[start, end] = (
				anchor.offset < focus.offset
					? [anchor.offset, focus.offset]
					: [focus.offset, anchor.offset]
			);
		}
		else if (node === anchorNode)
		{
			[start, end] = anchorNode.isBefore(focusNode)
				? [anchor.offset, textLength]
				: [0, anchor.offset];
		}
		else if (node === focusNode)
		{
			[start, end] = focusNode.isBefore(anchorNode)
				? [focus.offset, textLength]
				: [0, focus.offset];
		}
		else
		{
			// Node is within selection but not the anchor nor focus.
			[start, end] = [0, textLength];
		}
	}

	// Account for non-single width characters.
	const numNonSingleWidthCharBeforeSelection = (
		textContent.slice(0, start).match(NON_SINGLE_WIDTH_CHARS_REGEX) || []
	).length;
	const numNonSingleWidthCharInSelection = (
		textContent.slice(start, end).match(NON_SINGLE_WIDTH_CHARS_REGEX) || []
	).length;

	return [
		start + numNonSingleWidthCharBeforeSelection,
		end + numNonSingleWidthCharBeforeSelection + numNonSingleWidthCharInSelection,
	];
}
