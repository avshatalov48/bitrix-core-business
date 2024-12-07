import { Dom, Type, type JsonObject, type JsonValue } from 'main.core';

import {
	$getSelection,
	$isNodeSelection,
	$getNodeByKey,
	COMMAND_PRIORITY_LOW,
	CLICK_COMMAND,
	KEY_DELETE_COMMAND,
	KEY_BACKSPACE_COMMAND,
} from 'ui.lexical.core';

import { mergeRegister } from 'ui.lexical.utils';
import { createNodeSelection } from './helpers/create-node-selection';

import { type TextEditor } from './text-editor';
import { type DecoratorComponentOptions } from './types/decorator-component-options';

export default class DecoratorComponent
{
	#textEditor: TextEditor = null;
	#target: HTMLElement = null;
	#nodeKey: string = null;
	#options: JsonObject = {};
	#nodeSelection = null;
	#unregisterCommands: Function = null;

	constructor(componentOptions: DecoratorComponentOptions)
	{
		const { textEditor, target, nodeKey, options } = componentOptions;

		this.#textEditor = textEditor;
		this.#target = target;
		this.#nodeKey = nodeKey;
		this.#options = options;

		this.#nodeSelection = createNodeSelection(this.getEditor(), this.getNodeKey());
		this.#nodeSelection.onSelect((selected: boolean) => {
			if (selected)
			{
				Dom.addClass(this.getTarget(), '--selected');
			}
			else
			{
				Dom.removeClass(this.getTarget(), '--selected');
			}
		});

		this.#unregisterCommands = this.#registerCommands();
	}

	update(options: JsonObject): void
	{
		// update
	}

	destroy(): void
	{
		this.#nodeSelection.dispose();
		this.#unregisterCommands();
	}

	getEditor(): TextEditor
	{
		return this.#textEditor;
	}

	getNodeKey(): string
	{
		return this.#nodeKey;
	}

	getTarget(): HTMLElement
	{
		return this.#target;
	}

	getNodeSelection()
	{
		return this.#nodeSelection;
	}

	isSelected(): boolean
	{
		return this.#nodeSelection.isSelected();
	}

	setSelected(selected: boolean)
	{
		this.#nodeSelection.setSelected(selected);
	}

	getOptions(): JsonObject
	{
		return this.#options;
	}

	getOption(option: string, defaultValue?: JsonValue): JsonValue
	{
		if (!Type.isUndefined(this.#options[option]))
		{
			return this.#options[option];
		}

		if (!Type.isUndefined(defaultValue))
		{
			return defaultValue;
		}

		return null;
	}

	#registerCommands(): Function
	{
		return mergeRegister(
			this.getEditor().registerCommand(
				CLICK_COMMAND,
				(event: MouseEvent) => {
					if (this.getTarget().contains(event.target))
					{
						if (event.shiftKey)
						{
							this.#nodeSelection.setSelected(!this.#nodeSelection.isSelected());
						}
						else
						{
							this.#nodeSelection.clearSelection();
							this.#nodeSelection.setSelected(true);
						}

						return true;
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				KEY_DELETE_COMMAND,
				this.#handleDelete.bind(this),
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				KEY_BACKSPACE_COMMAND,
				this.#handleDelete.bind(this),
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#handleDelete(event: KeyboardEvent): boolean
	{
		if (this.#nodeSelection.isSelected() && $isNodeSelection($getSelection()))
		{
			event.preventDefault();

			const node = $getNodeByKey(this.getNodeKey());
			this.#nodeSelection.setSelected(false);
			if (node)
			{
				node.remove();

				return true;
			}
		}

		return false;
	}
}
