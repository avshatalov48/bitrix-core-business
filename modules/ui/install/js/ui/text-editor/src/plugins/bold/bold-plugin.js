import { Loc, Browser } from 'main.core';
import { type TextNode } from 'ui.lexical.core';

import {
	FORMAT_TEXT_COMMAND,
} from 'ui.lexical.core';

import Button from '../../toolbar/button';
import BasePlugin from '../base-plugin';

import {
	convertTextFormatElement,
	wrapNodeWith,
	type BBCodeExportConversion,
	type BBCodeConversion,
	type BBCodeImportConversion,
} from '../../bbcode';

import { type TextEditor } from '../../text-editor';

export class BoldPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Bold';
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			b: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
			color: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
			background: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
			size: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			'text:bold': (lexicalNode: TextNode, node: Node): Node | null => {
				if (lexicalNode.hasFormat('bold'))
				{
					return wrapNodeWith(node, 'b', this.getEditor());
				}

				return null;
			},
		};
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('bold', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --bold"></span>');
			button.setFormat('bold');
			button.disableInsideUnformatted();
			button.setTooltip(
				Loc.getMessage('TEXT_EDITOR_BTN_BOLD', { '#keystroke#': Browser.isMac() ? '⌘B' : 'Ctrl+B' }),
			);
			button.subscribe('onClick', (): void => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					this.getEditor().dispatchCommand(FORMAT_TEXT_COMMAND, 'bold');
				});
			});

			return button;
		});
	}
}
