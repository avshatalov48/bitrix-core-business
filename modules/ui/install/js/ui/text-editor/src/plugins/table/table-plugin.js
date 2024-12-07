/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
import { Type, Text, Loc } from 'main.core';
import type { BaseEvent } from 'main.core.events';
import type { BBCodeElementNode } from 'ui.bbcode.model';
import {
	$normalizeTextNodes,
	shouldWrapInParagraph,
	type BBCodeExportConversion,
	type BBCodeConversion,
	type BBCodeConversionFn,
	type BBCodeExportOutput,
	type BBCodeImportConversion,
} from '../../bbcode';

import { DIALOG_VISIBILITY_COMMAND, HIDE_DIALOG_COMMAND } from '../../commands';
import BasePlugin from '../base-plugin';
import Button from '../../toolbar/button';

import {
	$isTextNode,
	createCommand,
	$getNodeByKey,
	$createParagraphNode,
	COMMAND_PRIORITY_LOW,
	COMMAND_PRIORITY_EDITOR,
	type NodeKey,
	type LexicalCommand,
	type LexicalNode,
	type ElementNode,
} from 'ui.lexical.core';

import {
	TableNode,
	TableCellNode,
	TableRowNode,
	$isTableNode,
	$createTableNode,
	$createTableCellNode,
	$createTableRowNode,
	$createTableNodeWithDimensions,
	applyTableHandlers,
	TableCellHeaderStates,
	INSERT_TABLE_COMMAND,
	TableSelection,
} from 'ui.lexical.table';

import { $insertNodeToNearestRoot } from 'ui.lexical.utils';

import { type TextEditor } from '../../text-editor';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

import TableDialog from './table-dialog';

export const INSERT_TABLE_DIALOG_COMMAND: LexicalCommand = createCommand('INSERT_TABLE_DIALOG_COMMAND');

export class TablePlugin extends BasePlugin
{
	#tableDialog: TableDialog = null;

	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerCommands();
		this.#registerListeners();
		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Table';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [
			TableNode,
			TableCellNode,
			TableRowNode,
		];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			table: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: $createTableNode(),
					};
				},
				priority: 0,
			}),
			tr: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: $createTableRowNode(),
					};
				},
				priority: 0,
			}),
			td: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: $createTableCellNode(),
						after: (childLexicalNodes: Array<LexicalNode>): Array<LexicalNode> => {
							return $normalizeTextNodes(childLexicalNodes);
						},
					};
				},
				priority: 0,
			}),
			th: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					return {
						node: $createTableCellNode(TableCellHeaderStates.ROW),
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
			table: (): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: 'table' }),
				};
			},
			tablerow: (): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: 'tr' }),
				};
			},
			tablecell: (node: TableCellNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: node.hasHeader() ? 'th' : 'td' }),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [
				{
					nodeClass: TableNode,
					validate: ((tableNode: TableNode) => {
						if (tableNode.getChildrenSize() === 0)
						{
							tableNode.remove();

							return true;
						}

						return false;
					}),
				},
				{
					nodeClass: TableCellNode,
					validate: ((tableCellNode: TableCellNode) => {
						tableCellNode.getChildren().forEach((child: LexicalNode | ElementNode) => {
							if (shouldWrapInParagraph(child))
							{
								const paragraph = $createParagraphNode();
								child.replace(paragraph);
								paragraph.append(child);
							}
						});

						return false;
					}),
				},
			],
			bbcodeMap: {
				table: 'table',
				tablerow: 'tr',
				tablecell: 'td',
			},
		};
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('table', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --table-editor"></span>');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_TABLE'));
			button.subscribe('onClick', (): void => {
				this.getEditor().dispatchCommand(INSERT_TABLE_DIALOG_COMMAND, {
					targetNode: button.getContainer(),
				});
			});

			return button;
		});
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_TABLE_COMMAND,
				({ columns, rows }) => {
					const rowCount = Math.max(1, Text.toNumber(rows));
					const columnCount = Math.max(1, Text.toNumber(columns));
					const tableNode = $createTableNodeWithDimensions(rowCount, columnCount, false);
					$insertNodeToNearestRoot(tableNode);

					const firstDescendant: LexicalNode = tableNode.getFirstDescendant();
					if ($isTextNode(firstDescendant))
					{
						firstDescendant.select();
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
			this.getEditor().registerCommand(
				INSERT_TABLE_DIALOG_COMMAND,
				(payload): boolean => {
					if (!Type.isPlainObject(payload) || !Type.isElementNode(payload.targetNode))
					{
						return false;
					}

					if (this.#tableDialog !== null)
					{
						if (this.#tableDialog.getTargetNode() === payload.targetNode)
						{
							this.#tableDialog.show();

							return true;
						}

						this.#tableDialog.destroy();
					}

					this.#tableDialog = new TableDialog({
						targetNode: payload.targetNode,
						events: {
							onSelect: (event: BaseEvent) => {
								this.getEditor().dispatchCommand(INSERT_TABLE_COMMAND, event.getData());
								this.#tableDialog.hide();
							},
							onDestroy: () => {
								this.#tableDialog = null;
							},
						},
					});

					this.#tableDialog.show();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(): boolean => {
					if (this.#tableDialog !== null)
					{
						this.#tableDialog.hide();
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DIALOG_VISIBILITY_COMMAND,
				(): boolean => {
					return this.#tableDialog !== null && this.#tableDialog.isShown();
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#registerListeners(): void
	{
		const tableSelections: Map<NodeKey, TableSelection> = new Map();
		const initializeTableNode = (tableNode: TableNode) => {
			const nodeKey = tableNode.getKey();
			const tableElement = this.getEditor().getElementByKey(nodeKey);
			if (tableElement && !tableSelections.has(nodeKey))
			{
				const tableSelection = applyTableHandlers(
					tableNode,
					tableElement,
					this.getLexicalEditor(),
					true,
				);

				tableSelections.set(nodeKey, tableSelection);
			}
		};

		this.cleanUpRegister(
			this.getEditor().registerMutationListener(
				TableNode,
				(nodeMutations) => {
					for (const [nodeKey, mutation] of nodeMutations)
					{
						if (mutation === 'created')
						{
							this.getEditor().getEditorState().read(() => {
								const tableNode = $getNodeByKey(nodeKey);
								if ($isTableNode(tableNode))
								{
									initializeTableNode(tableNode);
								}
							});
						}
						else if (mutation === 'destroyed')
						{
							const tableSelection = tableSelections.get(nodeKey);
							if (tableSelection !== undefined)
							{
								tableSelection.removeListeners();
								tableSelections.delete(nodeKey);
							}
						}
					}
				},
			),
		);
	}

	destroy(): void
	{
		super.destroy();

		if (this.#tableDialog !== null)
		{
			this.#tableDialog.destroy();
		}
	}
}
