/*
eslint-disable no-underscore-dangle,
@bitrix24/bitrix24-rules/no-pseudo-private,
@bitrix24/bitrix24-rules/no-native-dom-methods
*/

import { Tag, Runtime, Event } from 'main.core';
import { Popup } from 'main.popup';
import { DIALOG_VISIBILITY_COMMAND, HIDE_DIALOG_COMMAND } from '../../commands';
import { UNFORMATTED } from '../../constants';
import { $adjustDialogPosition, clearDialogPosition } from '../../helpers/adjust-dialog-position';
import { getSelectedNode } from '../../helpers/get-selected-node';

import Toolbar from '../../toolbar/toolbar';

import {
	$isTextNode,
	$getSelection,
	$isRangeSelection,
	SELECTION_CHANGE_COMMAND,
	COMMAND_PRIORITY_CRITICAL,
	COMMAND_PRIORITY_LOW,
	type RangeSelection,
} from 'ui.lexical.core';

import { $isLinkNode } from 'ui.lexical.link';
import { $findMatchingParent, mergeRegister } from 'ui.lexical.utils';
import { TextEditorLexicalNode } from '../../types/text-editor-lexical-node';

import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';
import type { CopilotPlugin } from '../copilot';

export class FloatingToolbarPlugin extends BasePlugin
{
	#popup: Popup = null;
	#toolbar: Toolbar = null;
	#showDebounced: Function = null;
	#onEditorScroll: Function = this.#handleEditorScroll.bind(this);

	constructor(editor: TextEditor)
	{
		super(editor);

		this.#showDebounced = Runtime.debounce(() => {
			this.getEditor().update(() => {
				if (this.#shouldShowDialog())
				{
					this.#show();
				}
			});
		}, 700);
	}

	static getName(): string
	{
		return 'FloatingToolbar';
	}

	afterInit(): void
	{
		const toolbarOptions = this.getEditor().getOption('floatingToolbar', []);
		this.#toolbar = new Toolbar(this.getEditor(), toolbarOptions);
		if (!this.#toolbar.isEmpty())
		{
			this.cleanUpRegister(
				this.#registerListeners(),
			);
		}
	}

	#registerListeners(): () => void
	{
		return mergeRegister(
			this.getEditor().registerCommand(
				SELECTION_CHANGE_COMMAND,
				() => {
					this.update();

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),
			this.getEditor().registerUpdateListener(({ editorState }) => {
				editorState.read(() => {
					this.update();
				});
			}),
			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(): boolean => {
					this.hide();

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	update(): void
	{
		if (this.#shouldShowDialog())
		{
			if (this.getPopup().isShown())
			{
				this.#show();
			}
			else
			{
				this.#showDebounced();
			}
		}
		else
		{
			this.getPopup().close();
		}
	}

	#show()
	{
		this.getPopup().show();
		clearDialogPosition(this.getPopup());
		this.#adjustDialogPosition();
	}

	#adjustDialogPosition(): boolean
	{
		return $adjustDialogPosition(
			this.getPopup(),
			this.getEditor(),
			this.#initDialogPosition,
		);
	}

	#initDialogPosition(selectionPosition: Object): string
	{
		const { isBackward, isMultiline } = selectionPosition;

		return isBackward || !isMultiline ? 'top' : 'bottom';
	}

	#handleEditorScroll(): void
	{
		this.getEditor().update(() => {
			this.#adjustDialogPosition();
		});
	}

	#shouldShowDialog(): boolean
	{
		if (this.getEditor().isComposing() || !this.getEditor().isEditable())
		{
			return false;
		}

		const selection: RangeSelection = $getSelection();
		if (!$isRangeSelection(selection) || selection.isCollapsed())
		{
			return false;
		}

		const nativeSelection = window.getSelection();
		if (nativeSelection === null || nativeSelection.isCollapsed)
		{
			return false;
		}

		const scrollerContainer = this.getEditor().getScrollerContainer();
		if (!scrollerContainer.contains(nativeSelection.anchorNode))
		{
			return false;
		}

		const $isUnformatted = $findMatchingParent(
			selection.anchor.getNode(),
			(node: TextEditorLexicalNode) => {
				return (node.__flags & UNFORMATTED) !== 0;
			},
		);

		if ($isUnformatted || selection.getTextContent() === '')
		{
			return false;
		}

		const rawTextContent = selection.getTextContent().replaceAll('\n', '');
		if (!selection.isCollapsed() && rawTextContent === '')
		{
			return false;
		}

		const node = getSelectedNode(selection);
		const parent = node.getParent();
		if ($isLinkNode(parent) || $isLinkNode(node))
		{
			return false;
		}

		const isSomeDialogVisible = this.getEditor().dispatchCommand(DIALOG_VISIBILITY_COMMAND);
		if (isSomeDialogVisible)
		{
			return false;
		}

		return $isTextNode(node);
	}

	getPopup(): Popup
	{
		if (this.#popup === null)
		{
			const container = Tag.render`<div class="ui-text-editor-floating-toolbar"></div>`;
			this.#popup = new Popup({
				closeByEsc: true,
				// for an embedded popup: document.body -> this.getEditor().getScrollerContainer()
				targetContainer: document.body,
				autoHide: true,
				content: container,
				autoHideHandler: (event: MouseEvent): boolean => {
					let collapsed = true;
					const nativeSelection = window.getSelection();
					if (nativeSelection.isCollapsed)
					{
						return true;
					}

					this.getEditor().update(() => {
						const selection: RangeSelection = $getSelection();
						collapsed = selection === null || selection.isCollapsed();
					});

					return collapsed;
				},
				events: {
					onShow: () => {
						if (this.#adjustDialogPosition())
						{
							Event.bind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
						}
					},
					onClose: () => {
						Event.unbind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
						clearDialogPosition(this.getPopup());
					},
				},
			});

			this.#toolbar.renderTo(container);
		}

		return this.#popup;
	}

	hide()
	{
		if (this.#popup === null)
		{
			return;
		}

		this.getPopup().close();
	}

	destroy(): void
	{
		super.destroy();

		if (this.#popup !== null)
		{
			this.#popup.destroy();
			this.#popup = null;
		}

		this.#toolbar.destroy();
		this.#toolbar = null;
	}
}
