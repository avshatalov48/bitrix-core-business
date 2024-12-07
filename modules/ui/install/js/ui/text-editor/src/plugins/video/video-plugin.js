import { Type, Loc, Event } from 'main.core';
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
	$getSelection,
	$setSelection,
	$isRangeSelection,
	COMMAND_PRIORITY_EDITOR,
	COMMAND_PRIORITY_LOW,
	type LexicalNode,
	type LexicalCommand,
	type RangeSelection,
} from 'ui.lexical.core';

import { $insertNodeToNearestRoot } from 'ui.lexical.utils';

import Button from '../../toolbar/button';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';
import BasePlugin from '../base-plugin';
import VideoDialog from './video-dialog';

import { sanitizeUrl } from '../../helpers/sanitize-url';
import { validateVideoUrl } from '../../helpers/validate-video-url';

import { $createVideoNode, VideoNode, type VideoPayload } from './video-node';

import { type TextEditor } from '../../text-editor';

import './video-plugin.css';
import { VideoService } from 'ui.video-service';

export type InsertVideoPayload = Readonly<VideoPayload>;

/** @memberof BX.UI.TextEditor.Plugins.Video */
export const INSERT_VIDEO_COMMAND: LexicalCommand<InsertVideoPayload> = createCommand('INSERT_VIDEO_COMMAND');

/** @memberof BX.UI.TextEditor.Plugins.Video */
export const INSERT_VIDEO_DIALOG_COMMAND: LexicalCommand = createCommand('INSERT_VIDEO_DIALOG_COMMAND');

export class VideoPlugin extends BasePlugin
{
	#videoDialog: VideoDialog = null;
	#onEditorScroll: Function = this.#handleEditorScroll.bind(this);
	#lastSelection: RangeSelection = null;

	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerCommands();
		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Video';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [VideoNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			video: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					// [video type={type} width={width} height={height}]{url}[/video]
					const src = node.getContent().trim();
					const width = Number(node.getAttribute('width'));
					const height = Number(node.getAttribute('height'));
					if (validateVideoUrl(src))
					{
						return {
							node: $createVideoNode({ src: sanitizeUrl(src), width, height }),
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
			video: (lexicalNode: VideoNode): BBCodeExportOutput => {
				const attributes = {};
				const width = lexicalNode.getWidth();
				const height = lexicalNode.getHeight();
				if (Type.isNumber(width) && Type.isNumber(height))
				{
					attributes.width = width;
					attributes.height = height;
				}

				const provider = lexicalNode.getProvider();
				if (Type.isStringFilled(provider))
				{
					attributes.type = provider;
				}

				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({
						name: 'video',
						inline: false,
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
				nodeClass: VideoNode,
			}],
			bbcodeMap: {
				video: 'video',
			},
		};
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_VIDEO_COMMAND,
				(payload) => {
					if (Type.isPlainObject(payload) && validateVideoUrl(payload.src))
					{
						const videoNode = $createVideoNode({
							src: VideoService.getEmbeddedUrl(payload.src) || payload.src,
							width: payload.width,
							height: payload.height,
						});

						$insertNodeToNearestRoot(videoNode);

						return true;
					}

					return false;
				},
				COMMAND_PRIORITY_EDITOR,
			),

			this.getEditor().registerCommand(
				INSERT_VIDEO_DIALOG_COMMAND,
				(): boolean => {
					const selection: RangeSelection = $getSelection();
					if (!$isRangeSelection(selection))
					{
						return false;
					}

					this.#lastSelection = selection.clone();
					if (this.#videoDialog !== null)
					{
						this.#videoDialog.destroy();
					}

					this.getEditor().dispatchCommand(HIDE_DIALOG_COMMAND);

					this.#videoDialog = new VideoDialog({
						// for an embedded popup: document.body -> this.getEditor().getScrollerContainer()
						targetContainer: document.body,
						events: {
							onSave: () => {
								const url = this.#videoDialog.getVideoUrl();
								if (!Type.isStringFilled(url))
								{
									this.#videoDialog.hide();

									return;
								}

								if (!validateVideoUrl(url))
								{
									this.#videoDialog.showError(Loc.getMessage('TEXT_EDITOR_INVALID_URL'));

									return;
								}

								this.getEditor().dispatchCommand(INSERT_VIDEO_COMMAND, { src: url });

								this.#videoDialog.hide();
							},
							onInput: () => {
								this.#videoDialog.clearError();
							},
							onCancel: () => {
								this.#videoDialog.hide();
							},
							onShow: () => {
								if ($adjustDialogPosition(this.#videoDialog.getPopup(), this.getEditor()))
								{
									Event.bind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
									this.getEditor().highlightSelection();
								}
							},
							onClose: () => {
								this.#handleDialogDestroy();
							},
							onDestroy: () => {
								this.#handleDialogDestroy();
							},
						},
					});
					this.#videoDialog.show();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),

			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(): boolean => {
					if (this.#videoDialog !== null)
					{
						this.#videoDialog.hide();
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DIALOG_VISIBILITY_COMMAND,
				(): boolean => {
					return this.#videoDialog !== null && this.#videoDialog.isShown();
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
		this.#videoDialog = null;
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
			$adjustDialogPosition(this.#videoDialog.getPopup(), this.getEditor());
		});
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('video', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --insert-video"></span>');
			button.disableInsideUnformatted();
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_VIDEO'));
			button.subscribe('onClick', (): void => {
				if (this.#videoDialog !== null && this.#videoDialog.isShown())
				{
					return;
				}

				this.getEditor().focus(() => {
					this.getEditor().dispatchCommand(INSERT_VIDEO_DIALOG_COMMAND);
				});
			});

			return button;
		});
	}

	destroy(): void
	{
		super.destroy();

		if (this.#videoDialog !== null)
		{
			this.#videoDialog.destroy();
		}
	}
}
