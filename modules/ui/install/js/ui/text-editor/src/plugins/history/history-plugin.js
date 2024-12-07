import { Browser, Loc } from 'main.core';
import Button from '../../toolbar/button';
import BasePlugin from '../base-plugin';

import { type TextEditor } from '../../text-editor';

import { registerHistory, createEmptyHistoryState } from 'ui.lexical.history';

import {
	UNDO_COMMAND,
	REDO_COMMAND,
	CAN_UNDO_COMMAND,
	CAN_REDO_COMMAND,
	COMMAND_PRIORITY_CRITICAL,
} from 'ui.lexical.core';

export class HistoryPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		const historyState = createEmptyHistoryState();
		this.cleanUpRegister(
			registerHistory(editor.getLexicalEditor(), historyState, 1000),
		);

		this.#registerComponents();
	}

	static getName(): string
	{
		return 'History';
	}

	#registerComponents(): void
	{
		let canUndo = false;
		this.getEditor().getComponentRegistry().register('undo', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --undo"></span>');
			button.setDisabled(!canUndo);
			button.setTooltip(
				Loc.getMessage('TEXT_EDITOR_BTN_UNDO', { '#keystroke#': Browser.isMac() ? '⌘Z' : 'Ctrl+Z' }),
			);
			button.subscribe('onClick', (): void => {
				this.getEditor().dispatchCommand(UNDO_COMMAND);
			});

			button.setDisableCallback(() => {
				return !canUndo || !this.getEditor().isEditable();
			});

			this.getEditor().registerCommand(
				CAN_UNDO_COMMAND,
				(payload) => {
					canUndo = payload;
					button.setDisabled(!canUndo);

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			);

			return button;
		});

		let canRedo = false;
		this.getEditor().getComponentRegistry().register('redo', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --redo"></span>');
			button.setDisabled(!canRedo);
			button.setTooltip(
				Loc.getMessage('TEXT_EDITOR_BTN_REDO', { '#keystroke#': Browser.isMac() ? '⌘⇧Z' : 'Ctrl+Y' }),
			);
			button.subscribe('onClick', (): void => {
				this.getEditor().dispatchCommand(REDO_COMMAND);
			});

			button.setDisableCallback(() => {
				return !canRedo || !this.getEditor().isEditable();
			});

			this.getEditor().registerCommand(
				CAN_REDO_COMMAND,
				(payload) => {
					canRedo = payload;
					button.setDisabled(!canRedo);

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			);

			return button;
		});
	}
}
