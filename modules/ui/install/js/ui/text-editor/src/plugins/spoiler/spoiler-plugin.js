/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
import { Loc, Type } from 'main.core';
import type { BBCodeElementNode } from 'ui.bbcode.model';
import {
	$normalizeTextNodes,
	shouldWrapInParagraph,
	type BBCodeConversion,
	type BBCodeConversionFn,
	type BBCodeExportOutput,
	type BBCodeImportConversion,
	type BBCodeExportConversion,
} from '../../bbcode';

import { $findMatchingParent } from 'ui.lexical.utils';
import { $getAncestor } from '../../helpers/get-ancestor';
import { $isBlockNode } from '../../helpers/is-block-node';

import { $createSpoilerContentNode, $isSpoilerContentNode, SpoilerContentNode } from './spoiler-content-node';
import { $createSpoiler, $createSpoilerNode, $isSpoilerNode, SpoilerNode } from './spoiler-node';
import { $createSpoilerTitleNode, $isSpoilerTitleNode, $removeSpoiler, SpoilerTitleNode } from './spoiler-title-node';

import BasePlugin from '../base-plugin';
import Button from '../../toolbar/button';

import {
	$createParagraphNode,
	$getPreviousSelection,
	$getSelection,
	$isElementNode,
	$isDecoratorNode,
	$isRangeSelection,
	$setSelection,
	createCommand,
	$isRootOrShadowRoot,
	$createTextNode,
	COMMAND_PRIORITY_LOW,
	COMMAND_PRIORITY_NORMAL,
	INSERT_PARAGRAPH_COMMAND,
	DELETE_CHARACTER_COMMAND,
	PASTE_COMMAND,
	KEY_ENTER_COMMAND,
	type ElementNode,
	type LexicalNode,
	type RangeSelection,
} from 'ui.lexical.core';

import { $insertDataTransferForPlainText } from 'ui.lexical.clipboard';

import { type TextEditor } from '../../text-editor';
import { type SchemeValidationOptions } from '../../types/scheme-validation-options';

import { $createSpoilerTitleTextNode, $isSpoilerTitleTextNode, SpoilerTitleTextNode } from './spoiler-title-text-node';

export const INSERT_SPOILER_COMMAND = createCommand('INSERT_SPOILER_COMMAND');
export const REMOVE_SPOILER_COMMAND = createCommand('REMOVE_SPOILER_COMMAND');

export class SpoilerPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerNodeTransforms();
		this.#registerCommands();
		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Spoiler';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [
			SpoilerNode,
			SpoilerTitleNode,
			SpoilerContentNode,
			SpoilerTitleTextNode,
		];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			spoiler: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					const title: string = (
						Type.isStringFilled(node.getValue())
							? trimSpoilerTitle(node.getValue())
							: Loc.getMessage('TEXT_EDITOR_SPOILER_TITLE')
					);

					return {
						node: $createSpoilerNode(false),
						after: (childLexicalNodes: Array<LexicalNode>): Array<LexicalNode> => {
							return [
								$createSpoilerTitleNode().append($createSpoilerTitleTextNode(title)),
								$createSpoilerContentNode().append(...$normalizeTextNodes(childLexicalNodes)),
							];
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
			spoiler: (spoilerNode: SpoilerNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();
				const titleNode = spoilerNode.getChildren()[0];
				const title = trimSpoilerTitle(titleNode.getTextContent());
				const value = title === Loc.getMessage('TEXT_EDITOR_SPOILER_TITLE') ? '' : title;

				return {
					node: scheme.createElement({ name: 'spoiler', value }),
				};
			},
			'spoiler-title': (node: SpoilerTitleNode): BBCodeExportOutput => {
				return {
					node: null,
				};
			},
			'spoiler-content': (node: SpoilerContentNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createFragment(),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [
				{
					nodeClass: SpoilerNode,
				},
				{
					nodeClass: SpoilerContentNode,
					validate: ((contentNode: SpoilerContentNode) => {
						contentNode.getChildren().forEach((child: LexicalNode | ElementNode) => {
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
				spoiler: 'spoiler',
				'spoiler-content': 'spoiler',
			},
		};
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('spoiler', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --insert-spoiler"></span>');
			button.setBlockType('spoiler');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_SPOILER'));
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					if (button.isActive())
					{
						this.getEditor().dispatchCommand(REMOVE_SPOILER_COMMAND);
					}
					else
					{
						this.getEditor().dispatchCommand(INSERT_SPOILER_COMMAND);
					}
				});
			});

			return button;
		});
	}

	#registerCommands(): () => void
	{
		this.cleanUpRegister(
			// This handles the case when container is collapsed and we delete its previous sibling
			// into it, it would cause collapsed content deleted (since it's display: none, and selection
			// swallows it when deletes single char). Instead we expand container, which is although
			// not perfect, but avoids bigger problem
			this.getEditor().registerCommand(
				DELETE_CHARACTER_COMMAND,
				this.#handleDeleteCharacter.bind(this),
				COMMAND_PRIORITY_LOW,
			),

			this.getEditor().registerCommand(
				KEY_ENTER_COMMAND,
				this.#handleEnter.bind(this),
				COMMAND_PRIORITY_NORMAL,
			),

			this.getEditor().registerCommand(
				INSERT_PARAGRAPH_COMMAND,
				(event: KeyboardEvent) => {
					const selection: RangeSelection = $getSelection();
					if ($isRangeSelection(selection))
					{
						const spoilerTitleNode: SpoilerTitleNode = $findMatchingParent(
							selection.anchor.getNode(),
							(node: LexicalNode) => $isSpoilerTitleNode(node),
						);

						if (spoilerTitleNode)
						{
							const newBlock: ElementNode = spoilerTitleNode.insertNewAfter(selection);
							if (newBlock)
							{
								newBlock.selectStart();
							}

							return true;
						}
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),

			this.getEditor().registerCommand(
				PASTE_COMMAND,
				this.#handlePaste.bind(this),
				COMMAND_PRIORITY_NORMAL,
			),

			this.getEditor().registerCommand(
				INSERT_SPOILER_COMMAND,
				(payload) => {
					this.getEditor().update(() => {
						const title = Type.isPlainObject(payload) && Type.isStringFilled(payload.title) ? payload.title : undefined;
						const selection = $getSelection();
						const spoiler = insertSpoiler(selection, title);

						spoiler.getTitleNode().select();
					});

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),

			this.getEditor().registerCommand(
				REMOVE_SPOILER_COMMAND,
				() => {
					this.getEditor().update(() => {
						const selection: RangeSelection = $getSelection();
						if (!$isRangeSelection(selection))
						{
							return;
						}

						let spoilerNode = $findMatchingParent(selection.anchor.getNode(), $isSpoilerNode);
						if (!spoilerNode)
						{
							spoilerNode = $findMatchingParent(selection.focus.getNode(), $isSpoilerNode);
						}

						$removeSpoiler(spoilerNode);
					});

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#registerNodeTransforms(): void
	{
		this.cleanUpRegister(
			// Structure enforcing transformers for each node type. In case nesting structure is not
			// "Container > Title + Content" it'll unwrap nodes and convert it back
			// to regular content.
			this.getEditor().registerNodeTransform(SpoilerNode, (node) => {
				const children = node.getChildren();
				if (children.length !== 2 || !$isSpoilerTitleNode(children[0]) || !$isSpoilerContentNode(children[1]))
				{
					for (const child of children)
					{
						if ($isElementNode(child) || $isDecoratorNode(child))
						{
							node.insertBefore(child);
						}
						else
						{
							node.insertBefore($createParagraphNode().append(child));
						}
					}

					node.remove();
				}
			}),

			this.getEditor().registerNodeTransform(SpoilerTitleNode, (node: SpoilerTitleNode) => {
				const parent: ElementNode = node.getParent();
				if (!$isSpoilerNode(parent))
				{
					node.replace($createParagraphNode().append(...node.getChildren()));
				}
				else if (
					(node.getChildrenSize() === 1 && !$isSpoilerTitleTextNode(node.getFirstChild()))
					|| node.getChildrenSize() > 1
				)
				{
					$setSelection(null);
					const textContent = trimSpoilerTitle(node.getTextContent());
					node.clear();
					node.append($createSpoilerTitleTextNode(textContent));
					node.select();
				}
			}),

			this.getEditor().registerNodeTransform(SpoilerTitleTextNode, (node: SpoilerTitleNode) => {
				const parent: ElementNode = node.getParent();
				if (!$isSpoilerTitleNode(parent))
				{
					node.replace($createParagraphNode().append($createTextNode(node.getTextContent())));
				}
			}),

			this.getEditor().registerNodeTransform(SpoilerContentNode, (node) => {
				const parent = node.getParent();
				if (!$isSpoilerNode(parent))
				{
					const children = node.getChildren();
					for (const child of children)
					{
						if ($isElementNode(child) || $isDecoratorNode(child))
						{
							node.insertBefore(child);
						}
						else
						{
							node.insertBefore($createParagraphNode().append(child));
						}
					}

					node.remove();
				}
			}),
		);
	}

	#handleDeleteCharacter(): boolean
	{
		const selection: RangeSelection = $getSelection();
		if (!$isRangeSelection(selection) || !selection.isCollapsed() || selection.anchor.offset !== 0)
		{
			return false;
		}

		const anchorNode = selection.anchor.getNode();
		const topLevelElement = anchorNode.getTopLevelElement();
		if (topLevelElement === null)
		{
			return false;
		}

		const container: SpoilerNode = topLevelElement.getPreviousSibling();
		if (!$isSpoilerNode(container) || container.getOpen())
		{
			return false;
		}

		container.setOpen(true);

		return true;
	}

	#handleEnter(event: KeyboardEvent): boolean
	{
		if (event && (event.ctrlKey || event.metaKey))
		{
			// Handling CMD+Enter to toggle spoiler element collapsed state
			const selection: RangeSelection = $getPreviousSelection();
			if ($isRangeSelection(selection) && selection.isCollapsed())
			{
				const parent = $findMatchingParent(
					selection.anchor.getNode(),
					(node) => $isElementNode(node) && !node.isInline(),
				);

				if ($isSpoilerTitleNode(parent))
				{
					const container: SpoilerNode = parent.getParent();
					if ($isSpoilerNode(container))
					{
						container.toggleOpen();
						$setSelection(selection.clone());

						return true;
					}
				}
			}
		}

		return false;
	}

	#handlePaste(event: ClipboardEvent): boolean
	{
		const selection: RangeSelection = $getSelection();
		if (
			!$isRangeSelection(selection)
			|| !(event instanceof ClipboardEvent)
			|| event.clipboardData === null
		)
		{
			return false;
		}

		const spoilerTitleNode: SpoilerTitleNode = $findMatchingParent(
			selection.anchor.getNode(),
			(node: LexicalNode) => $isSpoilerTitleNode(node),
		);

		if (spoilerTitleNode)
		{
			$insertDataTransferForPlainText(event.clipboardData, selection);

			return true;
		}

		return false;
	}
}

export function insertSpoiler(selection: RangeSelection, title?: string)
{
	if (!$isRangeSelection(selection))
	{
		return null;
	}

	const anchor = selection.anchor;
	const anchorNode: ElementNode = anchor.getNode();
	const spoiler: SpoilerNode = $createSpoiler(true, title);
	if ($isRootOrShadowRoot(anchorNode))
	{
		const firstChild = anchorNode.getFirstChild();
		if (firstChild)
		{
			firstChild.replace(spoiler, true);
		}
		else
		{
			anchorNode.append(spoiler);
		}

		return spoiler;
	}

	const handled = new Set();
	const nodes: LexicalNode[] = selection.getNodes();
	const firstSelectedBlock = $getAncestor(selection.anchor.getNode(), $isBlockNode);
	if (firstSelectedBlock && !nodes.includes(firstSelectedBlock))
	{
		nodes.unshift(firstSelectedBlock);
	}

	handled.add(spoiler.getKey());
	handled.add(spoiler.getTitleNode().getKey());
	handled.add(spoiler.getContentNode().getKey());

	let firstNode = true;
	for (const node of nodes)
	{
		if (!$isBlockNode(node) || handled.has(node.getKey()))
		{
			continue;
		}

		const isParentHandled = $getAncestor(
			node.getParent(),
			(parentNode: LexicalNode): boolean => handled.has(parentNode.getKey()),
		);

		if (isParentHandled)
		{
			continue;
		}

		if (firstNode)
		{
			firstNode = false;
			node.replace(spoiler);
			spoiler.getContentNode().append(node);
		}
		else
		{
			spoiler.getContentNode().append(node);
		}

		// let parent: ElementNode = node.getParent();
		// while (parent !== null)
		// {
		// 	const parentKey = parent.getKey();
		// 	const nextParent: ElementNode = parent.getParent();
		// 	if ($isRootOrShadowRoot(nextParent) && !handled.has(parentKey))
		// 	{
		// 		handled.add(parentKey);
		// 		createSpoilerOrMerge(parent);
		//
		// 		break;
		// 	}
		//
		// 	parent = nextParent;
		// }

		handled.add(node.getKey());
	}

	return spoiler;
}

export function trimSpoilerTitle(title: string): string
{
	return title.trim()
		.replaceAll(/\r?\n|\t/gm, '')
		.replace('\r', '')
		.replaceAll(/\s+/g, ' ')
	;
}
