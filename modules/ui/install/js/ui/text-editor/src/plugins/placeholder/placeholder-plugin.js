import { Type, Dom, Tag, Text, Loc } from 'main.core';
import BasePlugin from '../base-plugin';

import {
	$getSelection,
	$isRangeSelection,
	$isRootNode,
	$isParagraphNode,
	SELECTION_CHANGE_COMMAND,
	COMMAND_PRIORITY_CRITICAL,
	BLUR_COMMAND,
	PASTE_COMMAND,
	type RangeSelection,
	type ParagraphNode,
} from 'ui.lexical.core';

import { $canShowPlaceholder } from 'ui.lexical.text';

import type { CopilotPlugin } from '../copilot';
import type { MentionPlugin } from '../mention';

import './placeholder.css';

export class PlaceholderPlugin extends BasePlugin
{
	#placeholder: string = null;
	#placeholderNode: HTMLElement = null;
	#paragraphPlaceholder: string = null;

	afterInit()
	{
		const placeholder = this.getEditor().getOption('placeholder');
		if (Type.isStringFilled(placeholder))
		{
			this.#placeholder = placeholder;
			this.#placeholderNode = Tag.render`
				<div class="ui-text-editor-placeholder">${Text.encode(this.#placeholder)}</div>
			`;

			Dom.append(this.#placeholderNode, this.getEditor().getScrollerContainer());

			this.#registerPlaceholderListeners();
		}

		let paragraphPlaceholder = this.getEditor().getOption('paragraphPlaceholder');
		if (Type.isStringFilled(paragraphPlaceholder))
		{
			if (paragraphPlaceholder === 'auto')
			{
				const copilotPlugin: CopilotPlugin = this.getEditor().getPlugin('Copilot');
				const copilotEnabled = copilotPlugin !== null && copilotPlugin.shouldTriggerBySpace();
				const mentionPlugin: MentionPlugin = this.getEditor().getPlugin('Mention');
				const mentionEnabled = mentionPlugin !== null && mentionPlugin.shouldTriggerByAtSign();
				if (copilotEnabled && mentionEnabled)
				{
					paragraphPlaceholder = Loc.getMessage('TEXT_EDITOR_PLACEHOLDER_MENTION_COPILOT');
				}
				else if (copilotEnabled)
				{
					paragraphPlaceholder = Loc.getMessage('TEXT_EDITOR_PLACEHOLDER_COPILOT');
				}
				else if (mentionEnabled)
				{
					paragraphPlaceholder = Loc.getMessage('TEXT_EDITOR_PLACEHOLDER_MENTION');
				}
			}

			if (paragraphPlaceholder !== 'auto')
			{
				this.#paragraphPlaceholder = paragraphPlaceholder;
				this.#registerParagraphListeners();
			}
		}
	}

	static getName(): string
	{
		return 'Placeholder';
	}

	#registerPlaceholderListeners(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerUpdateListener(() => {
				this.getEditor().getEditorState().read(() => {
					this.#togglePlaceholder();
				});
			}),
		);
	}

	#togglePlaceholder(): void
	{
		if (this.#placeholder === null)
		{
			return;
		}

		let canShowPlaceholder = $canShowPlaceholder(this.getLexicalEditor().isComposing());
		if (canShowPlaceholder && this.#paragraphPlaceholder !== null && this.#hasFocus())
		{
			canShowPlaceholder = false;
		}

		if (canShowPlaceholder)
		{
			Dom.addClass(this.#placeholderNode, '--shown');
		}
		else
		{
			Dom.removeClass(this.#placeholderNode, '--shown');
		}
	}

	#hasFocus(): boolean
	{
		const activeElement = document.activeElement;
		const rootElement = this.getEditor().getRootElement();

		return rootElement !== null && activeElement !== null && rootElement.contains(activeElement);
	}

	#hidePlaceholder(): void
	{
		if (this.#placeholderNode !== null)
		{
			Dom.removeClass(this.#placeholderNode, '--shown');
		}
	}

	#registerParagraphListeners(): void
	{
		let lastEmptyParagraph: ParagraphNode = null;
		const resetParagraphPlaceholder = () => {
			if (lastEmptyParagraph)
			{
				const htmlElement: HTMLParagraphElement | null = this.getEditor().getElementByKey(lastEmptyParagraph.getKey());
				if (htmlElement)
				{
					delete htmlElement.dataset.placeholder;
				}
			}
		};

		this.cleanUpRegister(
			this.getEditor().registerCommand(
				SELECTION_CHANGE_COMMAND,
				() => {
					if (!this.getEditor().isEditable())
					{
						return false;
					}

					const selection: RangeSelection = $getSelection();
					let currentParagraph: ParagraphNode | null = null;
					if ($isRangeSelection(selection) && selection.isCollapsed())
					{
						const node = selection.anchor.getNode();
						if ($isParagraphNode(node) && $isRootNode(node.getParent()) && node.isEmpty())
						{
							const htmlElement: HTMLParagraphElement | null = this.getEditor().getElementByKey(node.getKey());
							if (htmlElement && this.#hasFocus())
							{
								htmlElement.dataset.placeholder = this.#paragraphPlaceholder;
								currentParagraph = node;
								this.#hidePlaceholder();
							}
						}
					}

					if (lastEmptyParagraph && lastEmptyParagraph !== currentParagraph)
					{
						resetParagraphPlaceholder();
					}

					lastEmptyParagraph = currentParagraph;

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),
			this.getEditor().registerCommand(
				PASTE_COMMAND,
				(): boolean => {
					resetParagraphPlaceholder();

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),
			// this.getEditor().registerCommand(
			// 	FOCUS_COMMAND,
			// 	(): boolean => {
			// 		resetParagraphPlaceholder();
			//
			// 		return false;
			// 	},
			// 	COMMAND_PRIORITY_CRITICAL,
			// ),
			this.getEditor().registerCommand(
				BLUR_COMMAND,
				(): boolean => {
					resetParagraphPlaceholder();
					this.#togglePlaceholder();

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),
		);
	}
}
