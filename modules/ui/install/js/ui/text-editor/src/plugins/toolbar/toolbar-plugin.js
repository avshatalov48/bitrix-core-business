import { Dom } from 'main.core';
import {
	COMMAND_PRIORITY_LOW,
	createCommand,
	type LexicalCommand,
} from 'ui.lexical.core';

import Toolbar from '../../toolbar/toolbar';
import BasePlugin from '../base-plugin';

export const TOGGLE_TOOLBAR_COMMAND: LexicalCommand<void> = createCommand('TOGGLE_TOOLBAR_COMMAND');
export const SHOW_TOOLBAR_COMMAND: LexicalCommand<void> = createCommand('SHOW_TOOLBAR_COMMAND');
export const HIDE_TOOLBAR_COMMAND: LexicalCommand<void> = createCommand('HIDE_TOOLBAR_COMMAND');

export class ToolbarPlugin extends BasePlugin
{
	#toolbar: Toolbar = null;

	static getName(): string
	{
		return 'Toolbar';
	}

	getToolbar(): Toolbar
	{
		return this.#toolbar;
	}

	isRendered(): boolean
	{
		return this.#toolbar !== null && this.#toolbar.isRendered();
	}

	show(): void
	{
		if (this.isRendered())
		{
			Dom.removeClass(this.getEditor().getToolbarContainer(), '--hidden');
		}
	}

	hide(): void
	{
		if (this.isRendered())
		{
			Dom.addClass(this.getEditor().getToolbarContainer(), '--hidden');
		}
	}

	isShown(): boolean
	{
		return this.isRendered() && !Dom.hasClass(this.getEditor().getToolbarContainer(), '--hidden');
	}

	toggle(): void
	{
		if (this.isShown())
		{
			this.hide();
		}
		else
		{
			this.show();
		}
	}

	afterInit(): void
	{
		this.#toolbar = new Toolbar(this.getEditor(), this.getEditor().getOption('toolbar'));
		if (!this.#toolbar.isEmpty())
		{
			this.#registerCommands();

			this.#toolbar.renderTo(this.getEditor().getToolbarContainer());
			const hideToolbar = this.getEditor().getOption('hideToolbar', false);
			if (hideToolbar)
			{
				this.hide();
			}
		}
	}

	destroy(): void
	{
		super.destroy();
		this.#toolbar.destroy();
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				TOGGLE_TOOLBAR_COMMAND,
				(): boolean => {
					this.toggle();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				SHOW_TOOLBAR_COMMAND,
				(): boolean => {
					this.show();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				HIDE_TOOLBAR_COMMAND,
				(): boolean => {
					this.hide();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}
}
