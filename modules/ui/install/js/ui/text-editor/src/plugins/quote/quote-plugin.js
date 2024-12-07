import { Loc, Type } from 'main.core';

import type { BBCodeElementNode } from 'ui.bbcode.model';

import {
	$getSelection,
	$isRangeSelection,
	$createParagraphNode,
	createCommand,
	COMMAND_PRIORITY_LOW,
	type LexicalNode,
	type RangeSelection,
	type ElementNode,
	type LexicalCommand,
} from 'ui.lexical.core';

import { $findMatchingParent, $insertNodeToNearestRoot } from 'ui.lexical.utils';

import {
	$importFromBBCode,
	$normalizeTextNodes,
	shouldWrapInParagraph,
	type BBCodeConversion,
	type BBCodeConversionFn,
	type BBCodeExportOutput,
	type BBCodeImportConversion,
	type BBCodeExportConversion,
} from '../../bbcode';

import { NewLineMode } from '../../constants';
import { $wrapNodes } from '../../helpers/wrap-nodes';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

import BasePlugin from '../base-plugin';
import Button from '../../toolbar/button';
import { $createQuoteNode, $isQuoteNode, $removeQuote, QuoteNode } from './quote-node';

import { type TextEditor } from '../../text-editor';

export type InsertQuotePayload = {
	content?: string,
};

/** @memberof BX.UI.TextEditor.Plugins.Quote */
export const INSERT_QUOTE_COMMAND: LexicalCommand<InsertQuotePayload> = createCommand('INSERT_QUOTE_COMMAND');

/** @memberof BX.UI.TextEditor.Plugins.Quote */
export const FORMAT_QUOTE_COMMAND: LexicalCommand = createCommand('FORMAT_QUOTE_COMMAND');

/** @memberof BX.UI.TextEditor.Plugins.Quote */
export const REMOVE_QUOTE_COMMAND: LexicalCommand = createCommand('REMOVE_QUOTE_COMMAND');

export class QuotePlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerCommands();
		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Quote';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [QuoteNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			quote: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: $createQuoteNode(),
						after: (childLexicalNodes: Array<LexicalNode>): Array<LexicalNode> => {
							return $normalizeTextNodes(childLexicalNodes);
						},
					};
				},
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			quote: (lexicalNode: LexicalNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: 'quote' }),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: QuoteNode,
				validate: ((quoteNode: QuoteNode) => {
					let prevParagraph = null;
					quoteNode.getChildren().forEach((child: LexicalNode | ElementNode) => {
						if (shouldWrapInParagraph(child))
						{
							if (prevParagraph === null)
							{
								const paragraph = $createParagraphNode();
								child.replace(paragraph);
								paragraph.append(child);
								prevParagraph = paragraph;
							}
							else
							{
								prevParagraph.append(child);
							}
						}
						else
						{
							prevParagraph = null;
						}
					});

					return false;
				}),
			}],
			bbcodeMap: {
				quote: 'quote',
			},
		};
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_QUOTE_COMMAND,
				(payload) => {
					const quoteNode = $createQuoteNode();
					if (Type.isPlainObject(payload) && Type.isStringFilled(payload.content))
					{
						const nodes = $importFromBBCode(payload.content, this.getEditor(), false);
						quoteNode.append(...$normalizeTextNodes(nodes));
						$insertNodeToNearestRoot(quoteNode);
					}
					else
					{
						quoteNode.append($createParagraphNode());
						$insertNodeToNearestRoot(quoteNode);
					}

					quoteNode.selectStart();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				FORMAT_QUOTE_COMMAND,
				() => {
					const selection: RangeSelection = $getSelection();
					if ($isRangeSelection(selection))
					{
						const quoteNode = $createQuoteNode();
						$wrapNodes(selection, () => quoteNode);

						if (quoteNode.isEmpty())
						{
							quoteNode.append($createParagraphNode());
						}

						quoteNode.selectStart();
					}

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				REMOVE_QUOTE_COMMAND,
				() => {
					const selection: RangeSelection = $getSelection();
					if (!$isRangeSelection(selection))
					{
						return false;
					}

					let quoteNode = $findMatchingParent(selection.anchor.getNode(), $isQuoteNode);
					if (!quoteNode)
					{
						quoteNode = $findMatchingParent(selection.focus.getNode(), $isQuoteNode);
					}

					$removeQuote(quoteNode);

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('quote', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --quote"></span>');
			button.setBlockType('quote');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_QUOTE'));
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					if (button.isActive())
					{
						this.getEditor().dispatchCommand(REMOVE_QUOTE_COMMAND);
					}
					else if (this.getEditor().getNewLineMode() === NewLineMode.LINE_BREAK)
					{
						this.getEditor().dispatchCommand(INSERT_QUOTE_COMMAND);
					}
					else
					{
						this.getEditor().dispatchCommand(FORMAT_QUOTE_COMMAND);
					}
				});
			});

			return button;
		});
	}
}
