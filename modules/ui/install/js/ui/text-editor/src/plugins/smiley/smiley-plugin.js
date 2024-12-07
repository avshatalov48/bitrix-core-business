/* eslint-disable no-underscore-dangle */
import { Loc, Type } from 'main.core';
import { type BaseEvent } from 'main.core.events';

import {
	TextNode,
	createCommand,
	$insertNodes,
	$createTextNode,
	$createParagraphNode,
	$isRootOrShadowRoot,
	COMMAND_PRIORITY_EDITOR,
	COMMAND_PRIORITY_LOW,
	type LexicalCommand,
	type LexicalNode,
} from 'ui.lexical.core';

import { SmileyParser, SmileyManager } from 'ui.smiley';

import { $findMatchingParent, $wrapNodeInElement } from 'ui.lexical.utils';
import { DIALOG_VISIBILITY_COMMAND, HIDE_DIALOG_COMMAND } from '../../commands';
import { UNFORMATTED } from '../../constants';
import { TextEditorLexicalNode } from '../../types/text-editor-lexical-node';

import { $createSmileyNode, SmileyNode } from './smiley-node';
import Button from '../../toolbar/button';
import { SmileyDialog } from './smiley-dialog';
import BasePlugin from '../base-plugin';

import type {
	BBCodeExportOutput,
	BBCodeImportConversion,
	BBCodeExportConversion,
} from '../../bbcode';

import { type TextEditor } from '../../text-editor';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

type InsertSmileyPayload = string;

export const INSERT_SMILEY_COMMAND: LexicalCommand<InsertSmileyPayload> = createCommand('INSERT_SMILEY_COMMAND');
export const INSERT_SMILEY_DIALOG_COMMAND: LexicalCommand = createCommand('INSERT_SMILEY_DIALOG_COMMAND');

export class SmileyPlugin extends BasePlugin
{
	#smileyParser: SmileyParser = null;
	#smileyDialog: SmileyDialog = null;

	constructor(editor: TextEditor)
	{
		super(editor);

		if (SmileyManager.getSize() > 0)
		{
			this.#smileyParser = new SmileyParser(SmileyManager.getAll());
			this.#registerListeners();
			this.#registerInsertSmileyCommand();
			this.#registerComponents();
		}
	}

	static getName(): string
	{
		return 'Smiley';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [SmileyNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return null;
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			smiley: (lexicalNode: SmileyNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createText(lexicalNode.getTyping()),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			bbcodeMap: {
				smiley: '#text',
			},
		};
	}

	#registerListeners(): void
	{
		const handledTextNodes = new Set();

		this.cleanUpRegister(
			this.getEditor().registerNodeTransform(TextNode, (node: TextNode) => {
				if (!node.isSimpleText() || handledTextNodes.has(node.getKey()))
				{
					return;
				}

				const $isUnformatted = $findMatchingParent(
					node,
					(parentNode: TextEditorLexicalNode) => {
						return (parentNode.__flags & UNFORMATTED) !== 0;
					},
				);

				if ($isUnformatted)
				{
					return;
				}

				const splits = this.#smileyParser.parse(node.getTextContent());
				if (splits.length > 0)
				{
					const splitOffsets = splits.reduce((acc, smiley) => {
						acc.push(smiley.start, smiley.end);

						return acc;
					}, []);

					const textNodes = node.splitText(...splitOffsets);
					// console.log("textNodes", splitOffsets, textNodes);

					for (const textNode of textNodes)
					{
						const smiley = SmileyManager.get(textNode.getTextContent()) || null;
						if (smiley)
						{
							// console.log('replace');
							const smileyNode = $createSmileyNode(
								smiley.getImage(),
								smiley.getTyping(),
								smiley.getWidth(),
								smiley.getHeight(),
							);

							textNode.replace(smileyNode);
							// smileyNode.selectNext(0, 0);
						}
						else
						{
							handledTextNodes.add(textNode.getKey());
						}
					}
				}
			}),

			this.getEditor().registerUpdateListener(() => {
				handledTextNodes.clear();
			}),

			// Workaround for a disappearing cursor in FireFox and Safari.
			// Lexical always sets contentEditable = 'false' for all decorator nodes.
			this.getEditor().registerMutationListener(
				SmileyNode,
				(nodeMutations) => {
					for (const [nodeKey, mutation] of nodeMutations)
					{
						if (mutation === 'created')
						{
							const dom = this.getEditor().getElementByKey(nodeKey);
							dom.contentEditable = true;
						}
					}
				},
			),
			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(): boolean => {
					if (this.#smileyDialog !== null)
					{
						this.#smileyDialog.hide();
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DIALOG_VISIBILITY_COMMAND,
				(): boolean => {
					return this.#smileyDialog !== null && this.#smileyDialog.isShown();
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#registerInsertSmileyCommand(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_SMILEY_COMMAND,
				(payload) => {
					const smiley = SmileyManager.get(payload) || null;
					if (!smiley)
					{
						return false;
					}

					const smileyNode = $createSmileyNode(
						smiley.getImage(),
						smiley.getTyping(),
						smiley.getWidth(),
						smiley.getHeight(),
					);

					$insertNodes([$createTextNode(' '), smileyNode, $createTextNode(' ')]);
					if ($isRootOrShadowRoot(smileyNode.getParentOrThrow()))
					{
						$wrapNodeInElement(smileyNode, $createParagraphNode).selectEnd();
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
			this.getEditor().registerCommand(
				INSERT_SMILEY_DIALOG_COMMAND,
				(payload): boolean => {
					if (!Type.isPlainObject(payload) || !Type.isElementNode(payload.targetNode))
					{
						return false;
					}

					if (this.#smileyDialog !== null)
					{
						if (this.#smileyDialog.getTargetNode() === payload.targetNode)
						{
							this.#smileyDialog.show();

							return true;
						}

						this.#smileyDialog.destroy();
					}

					this.#smileyDialog = new SmileyDialog({
						targetNode: payload.targetNode,
						events: {
							onSelect: (event: BaseEvent) => {
								this.getEditor().dispatchCommand(INSERT_SMILEY_COMMAND, event.getData().smiley);
								this.#smileyDialog.hide();
							},
							onDestroy: () => {
								this.#smileyDialog = null;
							},
						},
					});

					this.#smileyDialog.show();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('smileys', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --insert-emoji"></span>');
			button.disableInsideUnformatted();
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_SMILEYS'));
			button.subscribe('onClick', (): void => {
				this.getEditor().update((): void => {
					this.getEditor().dispatchCommand(INSERT_SMILEY_DIALOG_COMMAND, {
						targetNode: button.getContainer(),
					});
				});
			});

			return button;
		});
	}

	destroy(): void
	{
		super.destroy();

		if (this.#smileyDialog !== null)
		{
			this.#smileyDialog.destroy();
		}
	}
}
