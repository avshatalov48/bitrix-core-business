/* eslint-disable no-underscore-dangle */

import { Loc, Type } from 'main.core';
import { CodeParser, type CodeToken } from 'ui.code-parser';
import { $insertDataTransferForPlainText } from 'ui.lexical.clipboard';

import {
	KEY_TAB_COMMAND,
	INSERT_TAB_COMMAND,
	FORMAT_TEXT_COMMAND,
	PASTE_COMMAND,
	COMMAND_PRIORITY_LOW,
	COMMAND_PRIORITY_NORMAL,
	COMMAND_PRIORITY_HIGH,
	INDENT_CONTENT_COMMAND,
	OUTDENT_CONTENT_COMMAND,
	COMMAND_PRIORITY_EDITOR,
	TextNode,
	$createTabNode,
	$insertNodes,
	$getSelection,
	$isRangeSelection,
	$isTabNode,
	$isLineBreakNode,
	$getNodeByKey,
	$createLineBreakNode,
	$createTextNode,
	$isTextNode,
	createCommand,
	type RangeSelection,
	type LexicalCommand,
	type LineBreakNode,
	type TabNode,
	type LexicalNode,
	type NodeKey,
} from 'ui.lexical.core';

import { $setBlocksType } from 'ui.lexical.selection';
import { $findMatchingParent, $insertNodeToNearestRoot } from 'ui.lexical.utils';

import { trimLineBreaks } from '../../bbcode';
import { getSelectedNode } from '../../helpers/get-selected-node';

import BasePlugin from '../base-plugin';
import Button from '../../toolbar/button';
import { FORMAT_PARAGRAPH_COMMAND } from '../paragraph';
import { CodeNode, $isCodeNode, $createCodeNode } from './code-node';
import { CodeTokenNode, $isCodeTokenNode, $createCodeTokenNode } from './code-token-node';

import { type TextEditor } from '../../text-editor';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';
import type { BBCodeElementNode } from 'ui.bbcode.model';
import type {
	BBCodeConversion,
	BBCodeConversionFn,
	BBCodeExportConversion,
	BBCodeExportOutput,
	BBCodeImportConversion,
} from '../../bbcode';

export type InsertCodePayload = {
	content?: string,
};

export const FORMAT_CODE_COMMAND: LexicalCommand = createCommand('FORMAT_CODE_COMMAND');
export const INSERT_CODE_COMMAND: LexicalCommand<InsertCodePayload> = createCommand('INSERT_CODE_COMMAND');

export class CodePlugin extends BasePlugin
{
	#nodesCurrentlyHighlighting = new Set();
	#codeParser = new CodeParser();

	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerCommands();
		this.#registerComponents();
		this.#registerListeners();
	}

	static getName(): string
	{
		return 'Code';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [CodeNode, CodeTokenNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			code: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: $createCodeNode(),
						after: (childLexicalNodes: Array<LexicalNode>): Array<LexicalNode> => {
							const childNodes = trimLineBreaks(childLexicalNodes);
							const content = childNodes.map(
								(childNode: LexicalNode) => childNode.getTextContent(),
							).join('');

							// return getCodeTokenNodes(parse(content));
							return [$createTextNode(content)];
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
			code: (lexicalNode: LexicalNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: 'code' }),
				};
			},
			'code-token': (lexicalNode: LexicalNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createText({
						content: lexicalNode.getTextContent(),
						encode: false,
					}),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: CodeNode,
			}],
			bbcodeMap: {
				code: 'code',
			},
		};
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('code', () => {
			const button = new Button();
			button.setContent('<span class="ui-icon-set --enclose-text-in-code-tag"></span>');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_CODE'));
			button.setBlockType('code');
			button.subscribe('onClick', () => {
				this.getEditor().focus();
				this.getEditor().update(() => {
					if (button.isActive())
					{
						this.getEditor().dispatchCommand(FORMAT_PARAGRAPH_COMMAND);
					}
					else
					{
						this.getEditor().dispatchCommand(FORMAT_CODE_COMMAND);
					}
				});
			});

			return button;
		});
	}

	#registerListeners(): void
	{
		const handleTextNodeTransform = this.#handleTextNodeTransform.bind(this);

		this.cleanUpRegister(
			// Prevent formatting
			this.getEditor().registerNodeTransform(CodeNode, this.#handleCodeNodeTransform.bind(this)),
			this.getEditor().registerNodeTransform(TextNode, handleTextNodeTransform),
			this.getEditor().registerNodeTransform(CodeTokenNode, handleTextNodeTransform),
			this.getEditor().registerCommand(
				FORMAT_TEXT_COMMAND,
				() => {
					const selection: RangeSelection = $getSelection();
					if (!$isRangeSelection(selection))
					{
						return false;
					}

					const node = getSelectedNode(selection);
					// const parent = node.getParent();

					return $isCodeTokenNode(node) || $isCodeNode(node);
				},
				COMMAND_PRIORITY_HIGH,
			),
			this.getEditor().registerCommand(
				KEY_TAB_COMMAND,
				(event) => {
					const command = this.#handleTab(event.shiftKey);
					if (command === null)
					{
						return false;
					}

					event.preventDefault();
					this.getEditor().dispatchCommand(command);

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				INSERT_TAB_COMMAND,
				() => {
					const selection = $getSelection();
					if (!$isSelectionInCode(selection))
					{
						return false;
					}
					$insertNodes([$createTabNode()]);

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				INDENT_CONTENT_COMMAND,
				(payload): boolean => this.#handleMultilineIndent(INDENT_CONTENT_COMMAND),
				COMMAND_PRIORITY_NORMAL,
			),
			this.getEditor().registerCommand(
				OUTDENT_CONTENT_COMMAND,
				(payload): boolean => this.#handleMultilineIndent(OUTDENT_CONTENT_COMMAND),
				COMMAND_PRIORITY_NORMAL,
			),
			this.getEditor().registerCommand(
				PASTE_COMMAND,
				(event) => {
					const selection: RangeSelection = $getSelection();
					if (
						!$isRangeSelection(selection)
						|| !(event instanceof ClipboardEvent)
						|| event.clipboardData === null
					)
					{
						return false;
					}

					const codeNode: CodeNode = $findMatchingParent(
						selection.anchor.getNode(),
						(node: LexicalNode) => $isCodeNode(node),
					);

					if (codeNode)
					{
						$insertDataTransferForPlainText(event.clipboardData, selection);

						return true;
					}

					return false;
				},
				COMMAND_PRIORITY_NORMAL,
			),
		);
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_CODE_COMMAND,
				(payload: InsertCodePayload) => {
					const codeNode = $createCodeNode();
					if (Type.isPlainObject(payload) && Type.isStringFilled(payload.content))
					{
						const tokenNodes = getCodeTokenNodes(this.#codeParser.parse(payload.content));
						codeNode.append(...tokenNodes);
						$insertNodeToNearestRoot(codeNode);
					}
					else
					{
						$insertNodeToNearestRoot(codeNode);
						codeNode.selectEnd();
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
			this.getEditor().registerCommand(
				FORMAT_CODE_COMMAND,
				(): boolean => {
					const selection: RangeSelection = $getSelection();
					if ($isRangeSelection(selection))
					{
						if (selection.isCollapsed())
						{
							$setBlocksType(selection, () => $createCodeNode());
						}
						else
						{
							const textContent = selection.getTextContent();
							const codeNode = $createCodeNode();
							selection.insertNodes([codeNode]);

							const newSelection: RangeSelection = $getSelection();
							if ($isRangeSelection(newSelection))
							{
								newSelection.insertRawText(textContent);
							}
						}
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
		);
	}

	#handleCodeNodeTransform(node: CodeNode): void
	{
		const nodeKey = node.getKey();
		if (this.#nodesCurrentlyHighlighting.has(nodeKey))
		{
			return;
		}

		this.#nodesCurrentlyHighlighting.add(nodeKey);

		// Using nested update call to pass `skipTransforms` since we don't want
		// each individual code-token node to be transformed again as it's already
		// in its final state
		this.getEditor().update(
			() => {
				updateAndRetainSelection(nodeKey, () => {
					const currentNode = $getNodeByKey(nodeKey);

					if (!$isCodeNode(currentNode) || !currentNode.isAttached())
					{
						return false;
					}
					const code = currentNode.getTextContent();
					const codeTokenNodes = getCodeTokenNodes(this.#codeParser.parse(code));
					const diffRange = getDiffRange(currentNode.getChildren(), codeTokenNodes);

					const { from, to, nodesForReplacement } = diffRange;
					if (from !== to || nodesForReplacement.length > 0)
					{
						node.splice(from, to - from, nodesForReplacement);

						return true;
					}

					return false;
				});
			},
			{
				onUpdate: () => {
					this.#nodesCurrentlyHighlighting.delete(nodeKey);
				},
				skipTransforms: true,
			},
		);
	}

	#handleTextNodeTransform(node: TextNode)
	{
		// Since CodeNode has flat children structure we only need to check
		// if node's parent is a code node and run highlighting if so
		const parentNode = node.getParent();
		if ($isCodeNode(parentNode))
		{
			this.#handleCodeNodeTransform(parentNode);
		}
		else if ($isCodeTokenNode(node))
		{
			// When code block converted into paragraph or other element
			// code token nodes converted back to normal text
			node.replace($createTextNode(node.__text));
		}
	}

	#handleTab(shiftKey: boolean): null | LexicalCommand<void>
	{
		const selection = $getSelection();
		if (!$isRangeSelection(selection) || !$isSelectionInCode(selection))
		{
			return null;
		}

		const indentOrOutdent = shiftKey ? OUTDENT_CONTENT_COMMAND : INDENT_CONTENT_COMMAND;
		const tabOrOutdent = shiftKey ? OUTDENT_CONTENT_COMMAND : INSERT_TAB_COMMAND;

		// 1. If multiple lines selected: indent/outdent
		const codeLines = $getCodeLines(selection);
		if (codeLines.length > 1)
		{
			return indentOrOutdent;
		}

		// 2. If entire line selected: indent/outdent
		const selectionNodes = selection.getNodes();
		const firstNode = selectionNodes[0];
		if ($isCodeNode(firstNode))
		{
			return indentOrOutdent;
		}

		const firstOfLine = getFirstCodeNodeOfLine(firstNode);
		const lastOfLine = getLastCodeNodeOfLine(firstNode);
		const anchor = selection.anchor;
		const focus = selection.focus;
		let selectionFirst = null;
		let selectionLast = null;
		if (focus.isBefore(anchor))
		{
			selectionFirst = focus;
			selectionLast = anchor;
		}
		else
		{
			selectionFirst = anchor;
			selectionLast = focus;
		}

		if (
			firstOfLine !== null
			&& lastOfLine !== null
			&& selectionFirst.key === firstOfLine.getKey()
			&& selectionFirst.offset === 0
			&& selectionLast.key === lastOfLine.getKey()
			&& selectionLast.offset === lastOfLine.getTextContentSize()
		)
		{
			return indentOrOutdent;
		}

		// 3. Else: tab/outdent
		return tabOrOutdent;
	}

	#handleMultilineIndent(type: LexicalCommand): boolean
	{
		const selection = $getSelection();
		if (!$isRangeSelection(selection) || !$isSelectionInCode(selection))
		{
			return false;
		}

		const codeLines = $getCodeLines(selection);
		const codeLinesLength = codeLines.length;
		// Multiple lines selection
		if (codeLines.length > 1)
		{
			for (let i = 0; i < codeLinesLength; i++)
			{
				const line = codeLines[i];
				if (line.length > 0)
				{
					let firstOfLine: null | CodeTokenNode | TabNode | LineBreakNode = line[0];
					// First and last lines might not be complete
					if (i === 0)
					{
						firstOfLine = getFirstCodeNodeOfLine(firstOfLine);
					}

					if (firstOfLine !== null)
					{
						if (type === INDENT_CONTENT_COMMAND)
						{
							// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
							firstOfLine.insertBefore($createTabNode());
						}
						else if ($isTabNode(firstOfLine))
						{
							firstOfLine.remove();
						}
					}
				}
			}

			return true;
		}

		// Just one line
		const selectionNodes = selection.getNodes();
		const firstNode = selectionNodes[0];
		if ($isCodeNode(firstNode))
		{
			// CodeNode is empty
			if (type === INDENT_CONTENT_COMMAND)
			{
				selection.insertNodes([$createTabNode()]);
			}

			return true;
		}

		const firstOfLine = getFirstCodeNodeOfLine(firstNode);
		if (type === INDENT_CONTENT_COMMAND)
		{
			if ($isLineBreakNode(firstOfLine))
			{
				firstOfLine.insertAfter($createTabNode());
			}
			else
			{
				// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
				firstOfLine.insertBefore($createTabNode());
			}
		}
		else if ($isTabNode(firstOfLine))
		{
			firstOfLine.remove();
		}

		return true;
	}
}

function $isSelectionInCode(selection: null | RangeSelection): boolean
{
	if (!$isRangeSelection(selection))
	{
		return false;
	}
	const anchorNode = selection.anchor.getNode();
	const focusNode = selection.focus.getNode();
	if (anchorNode.is(focusNode) && $isCodeNode(anchorNode))
	{
		return true;
	}

	const anchorParent = anchorNode.getParent();

	return $isCodeNode(anchorParent) && anchorParent.is(focusNode.getParent());
}

function $getCodeLines(selection: RangeSelection): Array<Array<CodeTokenNode | TabNode>>
{
	const nodes = selection.getNodes();
	const lines: Array<Array<CodeTokenNode | TabNode>> = [[]];
	if (nodes.length === 1 && $isCodeNode(nodes[0]))
	{
		return lines;
	}

	let lastLine: Array<CodeTokenNode | TabNode> = lines[0];
	for (const [i, node] of nodes.entries())
	{
		if ($isLineBreakNode(node))
		{
			if (i !== 0 && lastLine.length > 0)
			{
				lastLine = [];
				lines.push(lastLine);
			}
		}
		else
		{
			lastLine.push(node);
		}
	}

	return lines;
}

export function getFirstCodeNodeOfLine(
	anchor: CodeTokenNode | TabNode | LineBreakNode,
): null | CodeTokenNode | TabNode | LineBreakNode
{
	let previousNode = anchor;
	let node: null | LexicalNode = anchor;
	while ($isCodeTokenNode(node) || $isTabNode(node))
	{
		previousNode = node;
		node = node.getPreviousSibling();
	}

	return previousNode;
}

export function getLastCodeNodeOfLine(
	anchor: CodeTokenNode | TabNode | LineBreakNode,
): CodeTokenNode | TabNode | LineBreakNode
{
	let nextNode = anchor;
	let node: null | LexicalNode = anchor;
	while ($isCodeTokenNode(node) || $isTabNode(node))
	{
		nextNode = node;
		node = node.getNextSibling();
	}

	return nextNode;
}

type DiffRange = {
	from: number;
	nodesForReplacement: Array<LexicalNode>;
	to: number;
};

// Finds minimal diff range between two nodes lists. It returns from/to range boundaries of prevNodes
// that needs to be replaced with `nodes` (subset of nextNodes) to make prevNodes equal to nextNodes.
function getDiffRange(prevNodes: LexicalNode[], nextNodes: LexicalNode[]): DiffRange
{
	let leadingMatch = 0;
	while (leadingMatch < prevNodes.length)
	{
		if (!isEqual(prevNodes[leadingMatch], nextNodes[leadingMatch]))
		{
			break;
		}
		leadingMatch++;
	}

	const prevNodesLength: number = prevNodes.length;
	const nextNodesLength: number = nextNodes.length;
	const maxTrailingMatch: number = Math.min(prevNodesLength, nextNodesLength) - leadingMatch;

	let trailingMatch = 0;
	while (trailingMatch < maxTrailingMatch)
	{
		trailingMatch++;
		if (!isEqual(prevNodes[prevNodesLength - trailingMatch], nextNodes[nextNodesLength - trailingMatch]))
		{
			trailingMatch--;
			break;
		}
	}

	const from: number = leadingMatch;
	const to: number = prevNodesLength - trailingMatch;
	const nodesForReplacement: LexicalNode[] = nextNodes.slice(
		leadingMatch,
		nextNodesLength - trailingMatch,
	);

	return {
		from,
		nodesForReplacement,
		to,
	};
}

function isEqual(nodeA: LexicalNode, nodeB: LexicalNode): boolean
{
	// Only checking for code token nodes, tabs and linebreaks. If it's regular text node
	// returning false so that it's transformed into code token node
	return (
		(
			$isCodeTokenNode(nodeA)
			&& $isCodeTokenNode(nodeB)
			&& nodeA.__text === nodeB.__text
			&& nodeA.__highlightType === nodeB.__highlightType
		)
		|| ($isTabNode(nodeA) && $isTabNode(nodeB))
		|| ($isLineBreakNode(nodeA) && $isLineBreakNode(nodeB))
	);
}

function getCodeTokenNodes(tokens: Array<CodeToken>): LexicalNode[]
{
	const nodes: LexicalNode[] = [];
	tokens.forEach((token: CodeToken): void => {
		const partials: string[] = token.content.split(/([\t\n])/);
		const partialsLength: number = partials.length;
		for (let i = 0; i < partialsLength; i++)
		{
			const part: string = partials[i];
			if (part === '\n' || part === '\r\n')
			{
				nodes.push($createLineBreakNode());
			}
			else if (part === '\t')
			{
				nodes.push($createTabNode());
			}
			else if (part.length > 0)
			{
				nodes.push($createCodeTokenNode(part, token.type));
			}
		}
	});

	return nodes;
}

// Wrapping update function into selection retainer, that tries to keep cursor at the same
// position as before.
function updateAndRetainSelection(nodeKey: NodeKey, updateFn: () => boolean): void
{
	const node: LexicalNode | null = $getNodeByKey(nodeKey);
	if (!$isCodeNode(node) || !node.isAttached())
	{
		return;
	}

	// If it's not range selection (or null selection) there's no need to change it,
	// but we can still run highlighting logic
	const selection: RangeSelection = $getSelection();
	if (!$isRangeSelection(selection))
	{
		updateFn();

		return;
	}

	const anchor = selection.anchor;
	const anchorOffset: number = anchor.offset;
	const isNewLineAnchor: boolean = (
		anchor.type === 'element'
		&& $isLineBreakNode(node.getChildAtIndex(anchor.offset - 1))
	);

	// Calculating previous text offset (all text node prior to anchor + anchor own text offset)
	let textOffset = 0;
	if (!isNewLineAnchor)
	{
		const anchorNode = anchor.getNode();
		textOffset = (
			anchorOffset
			+ anchorNode.getPreviousSiblings().reduce((offset, _node) => {
				return offset + _node.getTextContentSize();
			}, 0)
		);
	}

	const hasChanges: boolean = updateFn();
	if (!hasChanges)
	{
		return;
	}

	// Non-text anchors only happen for line breaks, otherwise
	// selection will be within text node (code token node)
	if (isNewLineAnchor)
	{
		anchor.getNode().select(anchorOffset, anchorOffset);

		return;
	}

	// If it was non-element anchor then we walk through child nodes
	// and looking for a position of original text offset
	node.getChildren().some((child) => {
		const isText: boolean = $isTextNode(child);
		if (isText || $isLineBreakNode(child))
		{
			const textContentSize = child.getTextContentSize();
			if (isText && textContentSize >= textOffset)
			{
				child.select(textOffset, textOffset);

				return true;
			}
			textOffset -= textContentSize;
		}

		return false;
	});
}
