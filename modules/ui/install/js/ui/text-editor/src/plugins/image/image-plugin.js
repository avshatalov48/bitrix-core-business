import { Event, Loc, Type } from 'main.core';
import type { BBCodeElementNode } from 'ui.bbcode.model';

import {
	type BBCodeConversion,
	type BBCodeImportConversion,
	type BBCodeExportConversion,
	type BBCodeExportOutput,
	type BBCodeConversionFn,
} from '../../bbcode';

import { DIALOG_VISIBILITY_COMMAND, HIDE_DIALOG_COMMAND } from '../../commands';
import { $adjustDialogPosition } from '../../helpers/adjust-dialog-position';
import {
	createCommand,
	$createTextNode,
	$insertNodes,
	$isRootOrShadowRoot,
	$createParagraphNode,
	$getSelection,
	$setSelection,
	$isRangeSelection,
	COMMAND_PRIORITY_EDITOR,
	COMMAND_PRIORITY_LOW,
	type LexicalNode,
	type LexicalCommand,
	type RangeSelection,
} from 'ui.lexical.core';

import { $wrapNodeInElement, mergeRegister } from 'ui.lexical.utils';
import { registerDraggableNode } from '../../helpers/register-draggable-node';
import { validateImageUrl } from '../../helpers/validate-image-url';

import Button from '../../toolbar/button';
import BasePlugin from '../base-plugin';
import ImageDialog from './image-dialog';
import { $createImageNode, ImageNode, type ImagePayload } from './image-node';

import { type TextEditor } from '../../text-editor';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

import './image-plugin.css';

export type InsertImagePayload = Readonly<ImagePayload>;
export const INSERT_IMAGE_COMMAND: LexicalCommand<InsertImagePayload> = createCommand('INSERT_IMAGE_COMMAND');
export const INSERT_IMAGE_DIALOG_COMMAND: LexicalCommand = createCommand('INSERT_IMAGE_DIALOG_COMMAND');

export class ImagePlugin extends BasePlugin
{
	#imageDialog: ImageDialog = null;
	#onEditorScroll: Function = this.#handleEditorScroll.bind(this);
	#lastSelection: RangeSelection = null;

	constructor(editor: TextEditor)
	{
		super(editor);

		this.cleanUpRegister(
			this.#registerCommands(),
			registerDraggableNode(
				this.getEditor(),
				ImageNode,
				(data) => {
					this.getEditor().dispatchCommand(INSERT_IMAGE_COMMAND, data);
				},
			),
		);

		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Image';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [ImageNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			img: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					// [img]{url}[/img]
					// [img width={width} height={height}]{url}[/img]
					const src = node.getContent().trim();
					const width = Number(node.getAttribute('width'));
					const height = Number(node.getAttribute('height'));

					if (validateImageUrl(src))
					{
						return {
							node: $createImageNode({ src, width, height }),
						};
					}

					return {
						node: $createTextNode(node.toString()),
					};
				},
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			image: (lexicalNode: ImageNode): BBCodeExportOutput => {
				const attributes = {};
				const width = lexicalNode.getWidth();
				const height = lexicalNode.getHeight();
				if (Type.isNumber(width) && Type.isNumber(height))
				{
					attributes.width = width;
					attributes.height = height;
				}

				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({
						name: 'img',
						inline: true,
						attributes,
					}),
					after: (elementNode: BBCodeElementNode) => {
						elementNode.setChildren([scheme.createText(lexicalNode.getSrc())]);
					},
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: ImageNode,
			}],
			bbcodeMap: {
				image: 'img',
			},
		};
	}

	#registerCommands(): () => void
	{
		return mergeRegister(
			this.getEditor().registerCommand(
				INSERT_IMAGE_COMMAND,
				(payload: InsertImagePayload) => {
					if (!validateImageUrl(payload?.src))
					{
						return false;
					}

					const imageNode = $createImageNode(payload);
					$insertNodes([imageNode]);
					if ($isRootOrShadowRoot(imageNode.getParentOrThrow()))
					{
						$wrapNodeInElement(imageNode, $createParagraphNode).selectEnd();
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
			this.getEditor().registerCommand(
				INSERT_IMAGE_DIALOG_COMMAND,
				(): boolean => {
					const selection: RangeSelection = $getSelection();
					if (!$isRangeSelection(selection))
					{
						return false;
					}

					this.#lastSelection = selection.clone();
					if (this.#imageDialog !== null)
					{
						this.#imageDialog.destroy();
					}

					this.getEditor().dispatchCommand(HIDE_DIALOG_COMMAND);

					this.#imageDialog = new ImageDialog({
						// for an embedded popup: document.body -> this.getEditor().getScrollerContainer()
						targetContainer: document.body,
						events: {
							onSave: () => {
								const url = this.#imageDialog.getImageUrl();
								if (!Type.isStringFilled(url))
								{
									this.#imageDialog.hide();

									return;
								}

								this.getEditor().dispatchCommand(INSERT_IMAGE_COMMAND, { src: url });

								this.#imageDialog.hide();
							},
							onCancel: () => {
								this.#imageDialog.hide();
							},
							onClose: () => {
								this.#handleDialogDestroy();
							},
							onDestroy: () => {
								this.#handleDialogDestroy();
							},
							onShow: () => {
								if ($adjustDialogPosition(this.#imageDialog.getPopup(), this.getEditor()))
								{
									Event.bind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
									this.getEditor().highlightSelection();
								}
							},
							onAfterShow: () => {
								this.#imageDialog.getUrlTextBox().focus();
							},
						},
					});

					this.#imageDialog.show();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(): boolean => {
					if (this.#imageDialog !== null)
					{
						this.#imageDialog.destroy();
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DIALOG_VISIBILITY_COMMAND,
				(): boolean => {
					return this.#imageDialog !== null && this.#imageDialog.isShown();
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#restoreSelection(): boolean
	{
		const selection = $getSelection();
		if (!$isRangeSelection(selection) && this.#lastSelection !== null)
		{
			$setSelection(this.#lastSelection);
			this.#lastSelection = null;

			return true;
		}

		return false;
	}

	#handleDialogDestroy(): void
	{
		this.#imageDialog = null;
		Event.unbind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
		this.getEditor().resetHighlightSelection();

		this.getEditor().update(() => {
			if (!this.#restoreSelection())
			{
				this.getEditor().focus();
			}
		});
	}

	#handleEditorScroll(): void
	{
		this.getEditor().update(() => {
			$adjustDialogPosition(this.#imageDialog.getPopup(), this.getEditor());
		});
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('image', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --incert-image"></span>');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_IMAGE'));
			button.disableInsideUnformatted();
			button.subscribe('onClick', (): void => {
				if (this.#imageDialog !== null && this.#imageDialog.isShown())
				{
					return;
				}

				this.getEditor().focus(() => {
					this.getEditor().dispatchCommand(INSERT_IMAGE_DIALOG_COMMAND);
				});
			});

			return button;
		});
	}

	destroy(): void
	{
		super.destroy();

		if (this.#imageDialog !== null)
		{
			this.#imageDialog.destroy();
		}
	}
}
