import { Loc } from 'main.core';
import type { BBCodeElementNode } from 'ui.bbcode.model';
import { $findMatchingParent } from 'ui.lexical.utils';

import Button from '../../toolbar/button';
import BasePlugin from '../base-plugin';

import {
	COMMAND_PRIORITY_LOW,
	INSERT_PARAGRAPH_COMMAND,
	INDENT_CONTENT_COMMAND,
	COMMAND_PRIORITY_CRITICAL,
	$getSelection,
	$isRangeSelection,
	$isElementNode,
	type RangeSelection,
	type ElementNode,
	type LexicalNode,
} from 'ui.lexical.core';

import {
	INSERT_ORDERED_LIST_COMMAND,
	INSERT_UNORDERED_LIST_COMMAND,
	REMOVE_LIST_COMMAND,
	insertList,
	removeList,
	$handleListInsertParagraph,
	$isListItemNode,
	$isListNode,
	$getListDepth,
	$createListItemNode,
	$createListNode,
	ListNode,
	ListItemNode,
} from 'ui.lexical.list';

import { type TextEditor } from '../../text-editor';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';
import type {
	BBCodeConversion,
	BBCodeImportConversion,
	BBCodeConversionFn,
	BBCodeExportOutput,
	BBCodeExportConversion,
} from '../../bbcode';

export class ListPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerListeners();
		this.#registerComponents();
	}

	static getName(): string
	{
		return 'List';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [ListNode, ListItemNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			list: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: node.getValue() === '1' ? $createListNode('number', 1) : $createListNode('bullet'),
						// after: (childLexicalNodes: Array<LexicalNode>): Array<LexicalNode> => {
						// 	const normalizedListItems: Array<ListItemNode> = [];
						// 	for (const node of childLexicalNodes)
						// 	{
						// 		if ($isListItemNode(node))
						// 		{
						// 			normalizedListItems.push(node);
						// 			const children = node.getChildren();
						// 			if (children.length > 1)
						// 			{
						// 				children.forEach((child) => {
						// 					if ($isListNode(child))
						// 					{
						// 						normalizedListItems.push(this.#wrapInListItem(child));
						// 					}
						// 				});
						// 			}
						// 		}
						// 		else
						// 		{
						// 			normalizedListItems.push(this.#wrapInListItem(node));
						// 		}
						// 	}
						//
						// 	return normalizedListItems;
						// },
					};
				},
				priority: 0,
			}),
			'*': (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: $createListItemNode(),
					};
				},
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			list: (lexicalNode: ListNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();
				const node = scheme.createElement({ name: 'list' });
				if (lexicalNode.getListType() === 'number')
				{
					node.setValue('1');
				}

				return {
					node,
				};
			},
			listitem: (lexicalNode: LexicalNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: '*' }),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: ListNode,
			}],
			bbcodeMap: {
				list: 'list',
				listitem: '*',
			},
		};
	}

	// static #wrapInListItem(node: LexicalNode): ListItemNode
	// {
	// 	const listItemWrapper = $createListItemNode();
	//
	// 	return listItemWrapper.append(node);
	// }

	#registerListeners(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_ORDERED_LIST_COMMAND,
				() => {
					insertList(this.getLexicalEditor(), 'number');

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				INSERT_UNORDERED_LIST_COMMAND,
				() => {
					insertList(this.getLexicalEditor(), 'bullet');

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				REMOVE_LIST_COMMAND,
				() => {
					removeList(this.getLexicalEditor());

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				INSERT_PARAGRAPH_COMMAND,
				(): boolean => {
					return $handleListInsertParagraph();
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				INDENT_CONTENT_COMMAND,
				() => !this.#isIndentPermitted(1),
				COMMAND_PRIORITY_CRITICAL,
			),
		);
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('bulleted-list', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --bulleted-list"></span>');
			button.setBlockType('bullet');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_BULLETED_LIST'));
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					if (button.isActive())
					{
						this.getEditor().dispatchCommand(REMOVE_LIST_COMMAND);
					}
					else
					{
						this.getEditor().dispatchCommand(INSERT_UNORDERED_LIST_COMMAND);
					}
				});
			});

			return button;
		});

		this.getEditor().getComponentRegistry().register('numbered-list', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --numbered-list"></span>');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_NUMBERED_LIST'));
			button.setBlockType('number');
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					if (button.isActive())
					{
						this.getEditor().dispatchCommand(REMOVE_LIST_COMMAND);
					}
					else
					{
						this.getEditor().dispatchCommand(INSERT_ORDERED_LIST_COMMAND);
					}
				});
			});

			return button;
		});
	}

	#isIndentPermitted(maxDepth: number): boolean
	{
		const selection: RangeSelection = $getSelection();
		if (!$isRangeSelection(selection))
		{
			return false;
		}

		const elementNodesInSelection: Set<ElementNode> = this.#getElementNodesInSelection(selection);
		let totalDepth = 0;
		for (const elementNode of elementNodesInSelection)
		{
			if ($isListNode(elementNode))
			{
				totalDepth = Math.max($getListDepth(elementNode) + 1, totalDepth);
			}
			else if ($isListItemNode(elementNode))
			{
				const parent = elementNode.getParent();
				if (!$isListNode(parent))
				{
					throw new Error('TextEditor: A ListItemNode must have a ListNode for a parent.');
				}

				totalDepth = Math.max($getListDepth(parent) + 1, totalDepth);
			}
		}

		return totalDepth <= maxDepth;
	}

	#getElementNodesInSelection(selection: RangeSelection): Set<ElementNode>
	{
		const nodesInSelection = selection.getNodes();
		const predicate = (node: ElementNode) => $isElementNode(node) && !node.isInline();

		if (nodesInSelection.length === 0)
		{
			return new Set([
				$findMatchingParent(selection.anchor.getNode(), predicate),
				$findMatchingParent(selection.focus.getNode(), predicate),
			]);
		}

		return new Set(
			nodesInSelection.map((n) => ($isElementNode(n) ? n : $findMatchingParent(n, predicate))),
		);
	}
}
