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

export class ItalicPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Italic';
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			i: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			'text:italic': (lexicalNode: TextNode, node: Node): Node | null => {
				if (lexicalNode.hasFormat('italic'))
				{
					return wrapNodeWith(node, 'i', this.getEditor());
				}

				return null;
			},
		};
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('italic', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --italic"></span>');
			button.setFormat('italic');
			button.disableInsideUnformatted();
			button.setTooltip(
				Loc.getMessage('TEXT_EDITOR_BTN_ITALIC', { '#keystroke#': Browser.isMac() ? '⌘I' : 'Ctrl+I' }),
			);
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					this.getEditor().dispatchCommand(FORMAT_TEXT_COMMAND, 'italic');
				});
			});

			return button;
		});
	}
}
