import { Browser, Loc } from 'main.core';

import {
	convertTextFormatElement,
	wrapNodeWith,
	type BBCodeConversion,
	type BBCodeImportConversion,
	type BBCodeExportConversion,
} from '../../bbcode';

import Button from '../../toolbar/button';

import {
	COMMAND_PRIORITY_NORMAL,
	FORMAT_TEXT_COMMAND,
	KEY_MODIFIER_COMMAND,
	type TextNode,
} from 'ui.lexical.core';

import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';

export class StrikethroughPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerComponents();
		this.#registerKeyModifierCommand();
	}

	static getName(): string
	{
		return 'Strikethrough';
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			s: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
			del: (): BBCodeConversion => ({
				conversion: convertTextFormatElement,
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			'text:strikethrough': (lexicalNode: TextNode, node: Node): Node | null => {
				if (lexicalNode.hasFormat('strikethrough'))
				{
					return wrapNodeWith(node, 's', this.getEditor());
				}

				return null;
			},
		};
	}

	#registerKeyModifierCommand(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				KEY_MODIFIER_COMMAND,
				(payload) => {
					const event: KeyboardEvent = payload;
					const { code, ctrlKey, metaKey, shiftKey } = event;
					if (code === 'KeyX' && (ctrlKey || metaKey) && shiftKey)
					{
						event.preventDefault();
						this.getEditor().dispatchCommand(FORMAT_TEXT_COMMAND, 'strikethrough');

						return true;
					}

					return false;
				},
				COMMAND_PRIORITY_NORMAL,
			),
		);
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('strikethrough', () => {
			const button = new Button();
			button.setContent('<span class="ui-icon-set --strikethrough"></span>');
			button.setFormat('strikethrough');
			button.disableInsideUnformatted();
			button.setTooltip(
				Loc.getMessage('TEXT_EDITOR_BTN_STRIKETHROUGH', { '#keystroke#': Browser.isMac() ? '⌘⇧X' : 'Ctrl+Shift+X' }),
			);
			button.subscribe('onClick', () => {
				this.getEditor().focus();
				this.getEditor().update((): void => {
					this.getEditor().dispatchCommand(FORMAT_TEXT_COMMAND, 'strikethrough');
				});
			});

			return button;
		});
	}
}
