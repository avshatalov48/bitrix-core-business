import { Browser, Loc } from 'main.core';

import {
	wrapNodeWith,
	convertTextFormatElement,
	type BBCodeConversion,
	type BBCodeImportConversion,
	type BBCodeExportConversion,
} from '../../bbcode';

import Button from '../../toolbar/button';

import {
	FORMAT_TEXT_COMMAND,
	type TextNode,
} from 'ui.lexical.core';

import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';

export class UnderlinePlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Underline';
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			u: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			'text:underline': (lexicalNode: TextNode, node: Node): Node | null => {
				if (lexicalNode.hasFormat('underline'))
				{
					return wrapNodeWith(node, 'u', this.getEditor());
				}

				return null;
			},
		};
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('underline', () => {
			const button = new Button();
			button.setContent('<span class="ui-icon-set --underline"></span>');
			button.setFormat('underline');
			button.disableInsideUnformatted();
			button.setTooltip(
				Loc.getMessage('TEXT_EDITOR_BTN_UNDERLINE', { '#keystroke#': Browser.isMac() ? '⌘U' : 'Ctrl+U' }),
			);
			button.subscribe('onClick', () => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					this.getEditor().dispatchCommand(FORMAT_TEXT_COMMAND, 'underline');
				});
			});

			return button;
		});
	}
}
