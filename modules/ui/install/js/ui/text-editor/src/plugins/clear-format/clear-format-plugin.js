import { Loc } from 'main.core';

import {
	$getSelection,
	$isRangeSelection,
	$isTextNode,
	createCommand,
	COMMAND_PRIORITY_EDITOR,
	type LexicalCommand,
	type BaseSelection,
	type TextNode,
	type LexicalNode,
} from 'ui.lexical.core';

import { $isTableSelection } from 'ui.lexical.table';
import { $getNearestBlockElementAncestorOrThrow } from 'ui.lexical.utils';

import Button from '../../toolbar/button';
import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';

export const CLEAR_FORMATTING_COMMAND: LexicalCommand = createCommand('CLEAR_FORMATTING_COMMAND');

export class ClearFormatPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerCommands();
		this.#registerComponents();
	}

	static getName(): string
	{
		return 'ClearFormat';
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				CLEAR_FORMATTING_COMMAND,
				() => {
					const selection: BaseSelection = $getSelection();
					if (!$isRangeSelection(selection) && !$isTableSelection(selection))
					{
						return false;
					}

					const anchor = selection.anchor;
					const focus = selection.focus;
					const nodes = selection.getNodes();
					const extractedNodes = selection.extract();

					if (anchor.key === focus.key && anchor.offset === focus.offset)
					{
						return false;
					}

					nodes.forEach((node: LexicalNode, idx) => {
						// We split the first and last node by the selection
						// So that we don't format unselected text inside those nodes
						if ($isTextNode(node))
						{
							// Use a separate variable to ensure TS does not lose the refinement
							let textNode: TextNode = node;
							if (idx === 0 && anchor.offset !== 0)
							{
								textNode = textNode.splitText(anchor.offset)[1] || textNode;
							}
							if (idx === nodes.length - 1)
							{
								textNode = textNode.splitText(focus.offset)[0] || textNode;
							}
							/**
							 * If the selected text has one format applied
							 * selecting a portion of the text, could
							 * clear the format to the wrong portion of the text.
							 *
							 * The cleared text is based on the length of the selected text.
							 */
							// We need this in case the selected text only has one format
							const extractedTextNode = extractedNodes[0];
							if (nodes.length === 1 && $isTextNode(extractedTextNode))
							{
								textNode = extractedTextNode;
							}

							if (textNode.__style !== '')
							{
								textNode.setStyle('');
							}

							if (textNode.__format !== 0)
							{
								textNode.setFormat(0);
								$getNearestBlockElementAncestorOrThrow(textNode).setFormat('');
							}
						}
						/* else if ($isHeadingNode(node) || $isQuoteNode(node))
						{
							node.replace($createParagraphNode(), true);
						} */
					});

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
		);
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('clear-format', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --remove-formatting"></span>');
			button.disableInsideUnformatted();
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_CLEAR_FORMATTING'));
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					this.getEditor().dispatchCommand(CLEAR_FORMATTING_COMMAND);
				});
			});

			return button;
		});
	}
}
