
import { BBCodeNode, type BBCodeElementNode } from 'ui.bbcode.model';
import { $insertDataTransferForPlainText } from 'ui.lexical.clipboard';
import type { ElementNode } from 'ui.lexical.core';
import {
	RootNode,
	ParagraphNode,
	$createParagraphNode,
	$isParagraphNode,
	createCommand,
	$getSelection,
	$isRangeSelection,
	COMMAND_PRIORITY_EDITOR,
	COMMAND_PRIORITY_LOW,
	KEY_ARROW_UP_COMMAND,
	KEY_ARROW_LEFT_COMMAND,
	KEY_ARROW_DOWN_COMMAND,
	KEY_ARROW_RIGHT_COMMAND,
	PASTE_COMMAND,
	type LexicalNode,
	type RangeSelection,
	type LexicalCommand,
	type LexicalNodeReplacement,
} from 'ui.lexical.core';

import { $setBlocksType } from 'ui.lexical.selection';
import { $findMatchingParent } from 'ui.lexical.utils';
import { trimLineBreaks } from '../../bbcode';

import type {
	BBCodeConversion,
	BBCodeConversionFn,
	BBCodeConversionOutput,
	BBCodeExportConversion,
	BBCodeExportOutput,
	BBCodeImportConversion,
} from '../../bbcode';
import { NewLineMode } from '../../constants';
import { wrapTextInParagraph } from '../../helpers/wrap-text-in-paragraph';

import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

import BasePlugin from '../base-plugin';

import { $isCodeNode } from '../code';
import { $isQuoteNode } from '../quote';
import { $isSpoilerNode } from '../spoiler';
import { CustomParagraphNode } from './custom-paragraph-node';

import { type TextEditor } from '../../text-editor';

import './paragraph.css';

/** @memberof BX.UI.TextEditor.Plugins.Paragraph */
export const FORMAT_PARAGRAPH_COMMAND: LexicalCommand = createCommand('FORMAT_PARAGRAPH_COMMAND');

export class ParagraphPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerCommands();
		this.#registerListeners();
	}

	static getName(): string
	{
		return 'Paragraph';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode> | LexicalNodeReplacement>
	{
		return [
			CustomParagraphNode,
			{
				replace: ParagraphNode,
				with: (node: ParagraphNode) => {
					return new CustomParagraphNode(editor.getNewLineMode());
				},
				withClass: CustomParagraphNode,
			},
		];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			p: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => convertParagraphNode(node),
				priority: 0,
			}),
			left: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => convertParagraphNode(node),
				priority: 0,
			}),
			right: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => convertParagraphNode(node),
				priority: 0,
			}),
			center: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => convertParagraphNode(node),
				priority: 0,
			}),
			justify: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => convertParagraphNode(node),
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			paragraph: (lexicalNode: LexicalNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: 'p' }),
				};
			},
			'custom-paragraph': (lexicalNode: LexicalNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: 'p' }),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: CustomParagraphNode,
			}],
			bbcodeMap: {
				root: '#root',
				tab: '#tab',
				text: '#text',
				paragraph: 'p',
				'custom-paragraph': 'p',
				linebreak: '#linebreak',
			},
		};
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				FORMAT_PARAGRAPH_COMMAND,
				() => {
					const selection: RangeSelection = $getSelection();
					if ($isRangeSelection(selection))
					{
						$setBlocksType(selection, () => $createParagraphNode());
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
		);
	}

	#registerListeners(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerNodeTransform(RootNode, (root: RootNode) => {
				const lastChild = root.getLastChild();
				if (!$isParagraphNode(lastChild))
				{
					root.append($createParagraphNode());
				}
			}),

			// When a block node is the first child pressing up/left arrow will insert paragraph
			// above it to allow adding more content. It's similar what $insertBlockNode
			// (mainly for decorators), except it'll always be possible to continue adding
			// new content even if leading paragraph is accidentally deleted
			this.getEditor().registerCommand(
				KEY_ARROW_UP_COMMAND,
				this.#handleEscapeUp.bind(this),
				COMMAND_PRIORITY_LOW,
			),

			this.getEditor().registerCommand(
				KEY_ARROW_LEFT_COMMAND,
				this.#handleEscapeUp.bind(this),
				COMMAND_PRIORITY_LOW,
			),

			// When a block node is the last child pressing down/right arrow will insert paragraph
			// below it to allow adding more content. It's similar what $insertBlockNode
			// (mainly for decorators), except it'll always be possible to continue adding
			// new content even if trailing paragraph is accidentally deleted
			this.getEditor().registerCommand(
				KEY_ARROW_DOWN_COMMAND,
				this.#handleEscapeDown.bind(this),
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				KEY_ARROW_RIGHT_COMMAND,
				this.#handleEscapeDown.bind(this),
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				PASTE_COMMAND,
				this.#handlePaste.bind(this),
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#isBlockNode(node: LexicalNode | null | undefined): boolean
	{
		return $isQuoteNode(node) || $isCodeNode(node) || $isSpoilerNode(node);
	}

	#handlePaste(event): boolean
	{
		if (this.getEditor().getNewLineMode() === NewLineMode.PARAGRAPH)
		{
			// use a build-in algorithm (Rich Text Plugin)
			return false;
		}

		if (this.getEditor().getNewLineMode() === NewLineMode.LINE_BREAK)
		{
			event.preventDefault();
			this.getEditor().update(
				() => {
					const selection = $getSelection();
					const { clipboardData } = event;
					if (clipboardData !== null && $isRangeSelection(selection))
					{
						$insertDataTransferForPlainText(clipboardData, selection);
					}
				},
				{
					tag: 'paste',
				},
			);

			return true;
		}

		// Mixed Mode
		const clipboardData: DataTransfer = event.clipboardData;
		if (
			!clipboardData
			|| clipboardData.items.length !== 1
			|| (clipboardData.items[0].type !== 'text/plain' && clipboardData.items[0].type !== 'text/uri-list')
		)
		{
			return false;
		}

		const text = clipboardData.getData('text/plain') || clipboardData.getData('text/uri-list');
		const hasLineBreaks = /\n/.test(text);
		if (!hasLineBreaks)
		{
			return false;
		}

		event.preventDefault();
		event.stopPropagation();

		const html = wrapTextInParagraph(text);
		const dataTransfer = new DataTransfer();
		dataTransfer.setData('text/plain', clipboardData.getData('text/plain'));
		dataTransfer.setData('text/html', html);
		const pasteEvent = new ClipboardEvent('paste', {
			clipboardData: dataTransfer,
			bubbles: true,
			cancelable: true,
		});

		if (pasteEvent.clipboardData.items.length === 0)
		{
			// Firefox
			pasteEvent.clipboardData.setData('text/plain', clipboardData.getData('text/plain'));
			pasteEvent.clipboardData.setData('text/html', html);
		}

		this.getEditor().getEditableContainer().dispatchEvent(pasteEvent);

		return true;
	}

	#handleEscapeUp(): boolean
	{
		const selection: RangeSelection = $getSelection();
		if ($isRangeSelection(selection) && selection.isCollapsed() && selection.anchor.offset === 0)
		{
			const container: ElementNode = $findMatchingParent(selection.anchor.getNode(), this.#isBlockNode);
			if (this.#isBlockNode(container))
			{
				const parent: ElementNode = container.getParent();
				if (
					parent !== null
					&& parent.getFirstChild() === container
					&& (
						selection.anchor.key === container.getFirstDescendant()?.getKey()
						|| selection.anchor.key === container.getKey()
					)
				)
				{
					container.insertBefore($createParagraphNode());
				}
			}
		}

		return false;
	}

	#handleEscapeDown(): boolean
	{
		const selection: RangeSelection = $getSelection();
		if ($isRangeSelection(selection) && selection.isCollapsed())
		{
			const container: ElementNode = $findMatchingParent(selection.anchor.getNode(), this.#isBlockNode);
			if (this.#isBlockNode(container))
			{
				const parent: ElementNode = container.getParent();
				if (parent !== null && parent.getLastChild() === container)
				{
					const firstDescendant = container.getFirstDescendant();
					const lastDescendant = container.getLastDescendant();
					if (
						(
							lastDescendant !== null
							&& selection.anchor.key === lastDescendant.getKey()
							&& selection.anchor.offset === lastDescendant.getTextContentSize()
						) || (
							firstDescendant !== null
							&& selection.anchor.key === firstDescendant.getKey()
							&& selection.anchor.offset === firstDescendant.getTextContentSize()
						) || (
							selection.anchor.key === container.getKey()
							&& selection.anchor.offset === container.getTextContentSize()
						)
					)
					{
						container.insertAfter($createParagraphNode());
					}
				}
			}
		}

		return false;
	}
}

function convertParagraphNode(bbcodeNode: BBCodeNode): BBCodeConversionOutput
{
	return {
		node: $createParagraphNode(),
		after: (childLexicalNodes: Array<LexicalNode>): Array<LexicalNode> => {
			return trimLineBreaks(childLexicalNodes);
		},
	};
}
