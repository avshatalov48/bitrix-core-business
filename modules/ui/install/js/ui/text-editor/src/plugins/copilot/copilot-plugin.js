import { Runtime, Type, Dom, Tag, Loc, Event } from 'main.core';
import type { BaseEvent } from 'main.core.events';
import type { Copilot, CopilotOptions } from 'ai.copilot';
import { DIALOG_VISIBILITY_COMMAND, HIDE_DIALOG_COMMAND } from '../../commands';
import { $createNodesFromText } from '../../helpers/create-nodes-from-text';
import { $getSelectionPosition } from '../../helpers/get-selection-position';

import {
	$getSelection,
	$isRangeSelection,
	$isRootNode,
	$isTextNode,
	createCommand,
	$getRoot,
	$setSelection,
	$isParagraphNode,
	$createParagraphNode,
	COMMAND_PRIORITY_EDITOR,
	COMMAND_PRIORITY_LOW,
	type LexicalCommand,
	type PointType,
	type TextNode,
	type ElementNode,
	type RangeSelection,
} from 'ui.lexical.core';

import Button from '../../toolbar/button';
import BasePlugin from '../base-plugin';

import { type TextEditor } from '../../text-editor';

import './copilot.css';
import { CustomParagraphNode } from '../paragraph/custom-paragraph-node';

export const INSERT_COPILOT_DIALOG_COMMAND: LexicalCommand = createCommand('INSERT_COPILOT_DIALOG_COMMAND');

const CopilotStatus = {
	INIT: 'init',
	LOADING: 'loading',
	LOADED: 'loaded',
};

export class CopilotPlugin extends BasePlugin
{
	#copilot: Copilot = null;
	#copilotStatus: boolean = CopilotStatus.INIT;
	#copilotOptions: CopilotOptions = null;
	#targetParagraph: HTMLParagraphElement = null;
	#lastSelection: RangeSelection = null;
	#onEditorScroll: Function = this.#handleEditorScroll.bind(this);
	#triggerBySpace: boolean = false;

	constructor(editor: TextEditor)
	{
		super(editor);

		this.#copilotOptions = editor.getOption('copilot.copilotOptions');
		if (Type.isPlainObject(this.#copilotOptions))
		{
			this.#registerListeners();
			this.#registerComponents();
		}
	}

	static getName(): string
	{
		return 'Copilot';
	}

	#registerListeners(): void
	{
		this.#triggerBySpace = this.getEditor().getOption('copilot.triggerBySpace', false);

		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_COPILOT_DIALOG_COMMAND,
				(payload): boolean => {
					const options = Type.isPlainObject(payload) ? payload : {};
					this.show(options);

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(): boolean => {
					this.#hide();

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DIALOG_VISIBILITY_COMMAND,
				(): boolean => {
					return this.isCopilotShown();
				},
				COMMAND_PRIORITY_LOW,
			),
			this.#triggerBySpace ? this.#registerParagraphNodeTransform() : () => {},
		);
	}

	#registerParagraphNodeTransform(): () => void
	{
		return this.getEditor().registerNodeTransform(CustomParagraphNode, (node: CustomParagraphNode) => {
			if (node.getChildrenSize() !== 1 || !$isRootNode(node.getParent()))
			{
				return;
			}

			if (!$isTextNode(node.getFirstChild()) || node.getFirstChild().getTextContent() !== ' ')
			{
				this.#resetLoader();

				return;
			}

			const selection: RangeSelection = $getSelection();
			if (!$isRangeSelection(selection) || !selection.isCollapsed())
			{
				return;
			}

			const anchorNode = selection.anchor.getNode();
			if (anchorNode !== node.getFirstChild())
			{
				return;
			}

			if (!this.isCopilotLoaded() && !this.isCopilotLoading())
			{
				this.#resetLoader();
				this.#targetParagraph = this.getEditor().getElementByKey(node.getKey());
				if (this.#targetParagraph)
				{
					Dom.addClass(this.#targetParagraph, 'ui-text-editor-loading-ellipsis');
				}
			}

			node.getFirstChild().remove();
			node.select();
			this.show({
				onShow: () => this.#resetLoader(),
				onError: () => this.#resetLoader(),
			});
		});
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('copilot', (): Button => {
			const button: Button = new Button();
			const copilotIconClass = '--copilot-ai';
			const refreshIconClass = '--refresh-5 ui-text-editor-copilot-loading';
			const icon = Tag.render`
				<span class="ui-icon-set ${copilotIconClass}" style="--ui-icon-set__icon-color: #8e52ec"></span>
			`;
			button.setContent(icon);
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_COPILOT'));
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();

				if (this.isCopilotLoading())
				{
					return;
				}

				const resetRefresh = () => {
					if (!Dom.hasClass(icon, copilotIconClass))
					{
						Dom.removeClass(icon, refreshIconClass);
						Dom.addClass(icon, copilotIconClass);
					}
				};

				this.getEditor().dispatchCommand(
					INSERT_COPILOT_DIALOG_COMMAND,
					{
						onShow: resetRefresh,
						onError: resetRefresh,
					},
				);

				if (!this.isCopilotLoaded())
				{
					setTimeout(() => {
						if (!this.isCopilotLoaded())
						{
							Dom.removeClass(icon, copilotIconClass);
							Dom.addClass(icon, refreshIconClass);
						}
					}, 500);
				}
			});

			return button;
		});
	}

	shouldTriggerBySpace(): boolean
	{
		return this.#triggerBySpace;
	}

	isCopilotLoaded(): boolean
	{
		return this.#copilotStatus === CopilotStatus.LOADED;
	}

	isCopilotLoading(): boolean
	{
		return this.#copilotStatus === CopilotStatus.LOADING;
	}

	isCopilotShown(): boolean
	{
		return this.#copilot !== null && this.#copilot.isShown();
	}

	show({ onShow, onError } = {})
	{
		if (this.isCopilotLoaded())
		{
			this.#show({ onShow });
		}
		else if (!this.isCopilotLoading())
		{
			this.#createCopilot()
				.then(() => {
					this.#show({ onShow });
				}).catch(() => {
					if (Type.isFunction(onError))
					{
						onError();
					}
				})
			;
		}
	}

	#show({ onShow } = {})
	{
		this.getEditor().update(() => {
			const selection: RangeSelection = $getSelection();
			if (!$isRangeSelection(selection) || !this.getEditor().isEditable())
			{
				return;
			}

			this.getEditor().dispatchCommand(HIDE_DIALOG_COMMAND);

			const selectionText = selection.getTextContent();
			const editorPosition = Dom.getPosition(this.getEditor().getScrollerContainer());
			const width = Math.min(editorPosition.width, 600);

			this.#lastSelection = selection.clone();

			const selectedText = selectionText.trim();
			if (selectedText.length > 0)
			{
				this.#copilot.setSelectedText(selectedText);
			}
			else
			{
				const wholeText = $getRoot().getTextContent().trim();
				if (wholeText.length > 0)
				{
					this.#copilot.setContext(wholeText);
				}
			}

			this.#copilot.show({ width });

			this.#adjustDialogPosition();

			Event.bind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);

			if (!selection.isCollapsed())
			{
				this.getEditor().highlightSelection();
			}

			if (Type.isFunction(onShow))
			{
				onShow();
			}
		});
	}

	#hide()
	{
		if (this.isCopilotLoaded() && this.#copilot.isShown())
		{
			this.#copilot.hide();
		}
	}

	#createCopilot(): Promise
	{
		if (this.isDestroyed())
		{
			return Promise.reject(new Error('Copilot plugin was destroyed.'));
		}

		this.#copilotStatus = CopilotStatus.LOADING;

		return new Promise((resolve, reject) => {
			Runtime.loadExtension('ai.copilot')
				.then(({ Copilot, CopilotEvents }) => {
					if (this.isDestroyed())
					{
						reject(new Error('Copilot plugin was destroyed.'));

						return;
					}

					this.#copilot = new Copilot({
						showResultInCopilot: true,
						...this.#copilotOptions,
						autoHide: true,
					});

					this.#copilot.subscribe(CopilotEvents.FINISH_INIT, () => {
						if (this.isDestroyed())
						{
							reject(new Error('Copilot plugin was destroyed.'));

							return;
						}

						this.#copilotStatus = CopilotStatus.LOADED;
						resolve();
					});

					this.#copilot.subscribe(CopilotEvents.TEXT_SAVE, this.#handleCopilotSave.bind(this));
					this.#copilot.subscribe(CopilotEvents.TEXT_PLACE_BELOW, this.#handleCopilotAddBelow.bind(this));
					this.#copilot.subscribe(CopilotEvents.HIDE, this.#handleCopilotHide.bind(this));

					this.#copilot.init();
				})
				.catch(() => {
					reject();
				})
			;
		});
	}

	#resetLoader(): void
	{
		if (this.#targetParagraph)
		{
			Dom.removeClass(this.#targetParagraph, 'ui-text-editor-loading-ellipsis');
		}
	}

	// #handleCopilotResult(event: BaseEvent): void
	// {
	// 	console.log('#handleCopilotResult', event.getData());
	// 	const { result } = event.getData();
	// 	this.getEditor().update(
	// 		() => {
	// 			this.#targetParagraph.clear();
	// 			this.#targetParagraph.append($createTextNode(result));
	// 		},
	// 		{
	// 			onUpdate: () => {
	// 				const targetNode: HTMLElement = this.getEditor().getElementByKey(this.#targetParagraph.getKey());
	// 				this.#copilot.adjustPosition(targetNode);
	// 			},
	// 		},
	// 	);
	// }

	#adjustDialogPosition(): void
	{
		this.getEditor().update(() => {
			this.#restoreSelection();

			const selectionPosition = $getSelectionPosition(this.getEditor(), $getSelection(), document.body);
			if (selectionPosition === null)
			{
				return;
			}

			const { top, left, bottom } = selectionPosition;
			const scrollerRect: DOMRect = Dom.getPosition(this.getEditor().getScrollerContainer());
			const popupWidth = Math.min(scrollerRect.width, 600);

			let offsetLeft = popupWidth / 2;
			if (left - offsetLeft < scrollerRect.left)
			{
				// Left boundary
				const overflow = scrollerRect.left - (left - offsetLeft);
				offsetLeft -= overflow + 16;
			}
			else if (scrollerRect.right < (left + popupWidth - offsetLeft))
			{
				// Right boundary
				offsetLeft += (left + popupWidth - offsetLeft) - scrollerRect.right + 16;
			}

			if (bottom < scrollerRect.top || top > scrollerRect.bottom)
			{
				this.#copilot.adjust({ hide: true });
			}
			else
			{
				this.#copilot.adjust({
					hide: false,
					position: { left: left - offsetLeft, top: bottom },
				});
			}
		});
	}

	#handleEditorScroll(): void
	{
		this.#adjustDialogPosition();
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

	#handleCopilotSave(event: BaseEvent): void
	{
		const { result } = event.getData();
		this.getEditor().update(() => {
			this.#restoreSelection();

			const selection: RangeSelection = $getSelection();
			if ($isRangeSelection(selection))
			{
				selection.insertRawText(result);
			}

			this.#hide();
		});
	}

	#handleCopilotAddBelow(event: BaseEvent): void
	{
		const { result } = event.getData();
		this.getEditor().update(() => {
			this.#restoreSelection();

			const selection: RangeSelection = $getSelection();
			if ($isRangeSelection(selection))
			{
				const focus: PointType = selection.focus;
				const focusNode: TextNode | ElementNode = focus.getNode();
				if (!selection.isCollapsed())
				{
					focusNode.selectEnd();
				}

				const parentNode: ElementNode = focusNode.getParent();
				if ($isParagraphNode(parentNode))
				{
					const paragraph = $createParagraphNode();
					paragraph.append(...$createNodesFromText(result));
					parentNode.insertAfter(paragraph);
				}
				else
				{
					selection.insertLineBreak();
					selection.insertRawText(result);
				}
			}

			this.#hide();
		});
	}

	#handleCopilotHide(): void
	{
		Event.unbind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
		this.getEditor().resetHighlightSelection();
		this.getEditor().update(() => {
			if (!this.#restoreSelection())
			{
				this.getEditor().focus();
			}
		});
	}

	destroy(): void
	{
		super.destroy();

		if (this.#copilot !== null)
		{
			this.#copilot.hide();
			this.#copilot = null;
		}
	}
}
