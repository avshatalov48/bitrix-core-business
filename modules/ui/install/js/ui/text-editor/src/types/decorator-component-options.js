import type { JsonObject } from 'main.core';
import type { TextEditor } from 'ui.text-editor';

export type DecoratorComponentOptions = {
	textEditor: TextEditor,
	nodeKey: string,
	target: HTMLElement,
	options: JsonObject,
};
