import type { LexicalEditor, LexicalNode, LexicalNodeReplacement } from 'ui.lexical.core';
import { mergeRegister } from 'ui.lexical.utils';
import { BBCodeExportConversion, BBCodeImportConversion } from '../bbcode';
import { type TextEditor } from '../text-editor';
import type { SchemeValidationOptions } from '../types/scheme-validation-options';

export interface PluginStaticMembers {
	getName(): string;
	getNodes(editor: TextEditor): Array<Class<LexicalNode>>;
}

export interface PluginInterface {}
export type PluginClassConstructor = (editor: TextEditor) => PluginInterface;
export type PluginConstructor = PluginClassConstructor & PluginStaticMembers;

/**
 * @memberof BX.UI.TextEditor
 */
export default class BasePlugin implements PluginStaticMembers
{
	#textEditor: TextEditor = null;
	#destroyed: boolean = false;
	#removeListeners: Function = () => {};

	constructor(textEditor: TextEditor)
	{
		this.#textEditor = textEditor;
	}

	static getName(): string
	{
		throw new Error('getName must be implemented in a child class');
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode> | LexicalNodeReplacement>
	{
		return [];
	}

	importBBCode(): BBCodeImportConversion | null
	{
		return null;
	}

	exportBBCode(): BBCodeExportConversion | null
	{
		return null;
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return null;
	}

	afterInit(): void
	{
		// you can override this method
	}

	getName(): string
	{
		return this.constructor.getName();
	}

	getEditor(): TextEditor
	{
		return this.#textEditor;
	}

	getLexicalEditor(): LexicalEditor
	{
		return this.#textEditor.getLexicalEditor();
	}

	cleanUpRegister(...func: Array<Function>): void
	{
		this.#removeListeners = mergeRegister(
			this.#removeListeners,
			...func,
		);
	}

	isDestroyed(): boolean
	{
		return this.#destroyed;
	}

	destroy(): void
	{
		this.#destroyed = true;
		this.#removeListeners();
		this.#removeListeners = null;
	}
}
