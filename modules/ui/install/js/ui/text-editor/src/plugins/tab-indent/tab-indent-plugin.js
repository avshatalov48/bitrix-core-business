import {
	KEY_TAB_COMMAND,
	INDENT_CONTENT_COMMAND,
	OUTDENT_CONTENT_COMMAND,
	COMMAND_PRIORITY_EDITOR,
	COMMAND_PRIORITY_LOW,
	$getSelection,
	$isElementNode,
	$isRangeSelection,
	type RangeSelection,
	type ElementNode,
} from 'ui.lexical.core';

import { $findMatchingParent } from 'ui.lexical.utils';
import { $isListItemNode } from 'ui.lexical.list';

import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';

export class TabIndentPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerListeners();
	}

	static getName(): string
	{
		return 'TabIndent';
	}

	#registerListeners(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				KEY_TAB_COMMAND,
				(event): boolean => {
					const selection: RangeSelection = $getSelection();
					if (!$isRangeSelection(selection))
					{
						return false;
					}

					event.preventDefault();

					return this.getEditor().dispatchCommand(
						event.shiftKey ? OUTDENT_CONTENT_COMMAND : INDENT_CONTENT_COMMAND,
					);
				},
				COMMAND_PRIORITY_EDITOR,
			),

			// Turn off RichText built-in indents
			this.getEditor().registerCommand(
				INDENT_CONTENT_COMMAND,
				(event): boolean => {
					const selection = $getSelection();

					return !$isSelectionInList(selection);
				},
				COMMAND_PRIORITY_LOW,
			),

			this.getEditor().registerCommand(
				OUTDENT_CONTENT_COMMAND,
				(event): boolean => {
					const selection = $getSelection();

					return !$isSelectionInList(selection);
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}
}

function $isSelectionInList(selection: null | RangeSelection): boolean
{
	if (!$isRangeSelection(selection))
	{
		return false;
	}

	const isBackward: boolean = selection.isBackward();
	const firstPoint = isBackward ? selection.focus : selection.anchor;
	const firstNode = firstPoint.getNode();

	if ($isListItemNode(firstNode) && firstPoint.offset === 0)
	{
		return true;
	}

	const parentNode = $findMatchingParent(
		firstNode,
		(node: ElementNode) => $isElementNode(node) && !node.isInline(),
	);

	return $isListItemNode(parentNode) && firstPoint.offset === 0;
}
