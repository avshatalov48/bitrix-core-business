import {Event} from 'main.core';
import {Main} from 'landing.main';
import {PageObject} from 'landing.pageobject';
import {RESOLVED, PENDING} from './internal/constants';
import registerBaseCommands from './internal/register-base-commands';
import removePageHistory from './internal/remove-page-history';
import loadStack from './internal/load-stack';
import clear from './internal/clear';
import onUpdate from './internal/on-update';
import onInit from './internal/on-init';
import Command from './history-command';
import Entry from './history-entry';
import Highlight from './history-highlight';    // not delete - just for export

import './css/style.css';

/**
 * Implements interface for works with landing history
 * Implements singleton pattern use as BX.Landing.History.getInstance()
 * @memberOf BX.Landing
 */
export class History
{
	static TYPE_LANDING = 'L';
	static TYPE_DESIGNER_BLOCK = 'D';

	/**
	 * Stack of action commands. Key - is step, value - is a command name
	 */
	stack: {[number]: string};

	/**
	 * Lenght of stack
	 */
	stackCount: number;

	/**
	 * Key - is step, value - is a Command object
	 */
	commands: {[number]: Command};

	/**
	 * From 1 to X. 0 - is state without any history
	 * @type {number}
	 */
	step: number;
	commandState: string;
	landingId: number;
	designerBlockId: ?number = null;

	constructor()
	{
		this.type = History.TYPE_LANDING;
		this.stack = {};
		this.stackCount = 0;
		this.step = 0;
		this.commands = {};
		this.commandState = RESOLVED;
		this.onStorage = this.onStorage.bind(this);

		try
		{
			this.landingId = Main.getInstance().id;
		}
		catch (err)
		{
			this.landingId = -1;
		}

		Event.bind(window, 'storage', this.onStorage);

		registerBaseCommands(this)
			.then(loadStack)
			.then(onInit);
	}

	static Command = Command;
	static Entry = Entry;
	static Highlight = Highlight; // not delete - just for export

	static getInstance(): History
	{
		const rootWindow = PageObject.getRootWindow();
		if (!rootWindow.BX.Landing.History.instance)
		{
			rootWindow.BX.Landing.History.instance = new BX.Landing.History();
		}

		return rootWindow.BX.Landing.History.instance;
	}

	/**
	 * Set special type for designer block
	 * @param blockId
	 * @return {Promise<BX.Landing.History>|*}
	 */
	setTypeDesignerBlock(blockId: number): Promise<History>
	{
		this.type = History.TYPE_DESIGNER_BLOCK;
		this.designerBlockId = blockId;

		return loadStack(this);
	}

	getLoadBackendActionName(): string
	{
		if (this.type === History.TYPE_DESIGNER_BLOCK)
		{
			return "History::getForDesignerBlock";
		}

		return "History::getForLanding";
	}

	getLoadBackendParams(): string
	{
		if (this.type === History.TYPE_DESIGNER_BLOCK)
		{
			return {blockId: this.designerBlockId};
		}

		return {lid: this.landingId};
	}

	getUndoBackendActionName(): string
	{
		if (this.type === History.TYPE_DESIGNER_BLOCK)
		{
			return "History::undoDesignerBlock";
		}

		return "History::undoLanding";
	}

	beforeUndo(): Promise
	{
		const step = this.step;
		if (
			this.stack[step]
			&& this.commands[this.stack[step]]
		)
		{
			const command = this.commands[this.stack[step]];

			return command.onBeforeCommand();
		}

		return Promise.resolve();
	}

	getRedoBackendActionName(): string
	{
		if (this.type === History.TYPE_DESIGNER_BLOCK)
		{
			return "History::redoDesignerBlock";
		}

		return "History::redoLanding";
	}

	beforeRedo(): Promise
	{
		const step = this.step + 1;
		if (
			this.stack[step]
			&& this.commands[this.stack[step]]
		)
		{
			const command = this.commands[this.stack[step]];

			return command.onBeforeCommand();
		}

		return Promise.resolve();
	}

	getBackendActionParams(): string
	{
		if (
			this.type === History.TYPE_DESIGNER_BLOCK
			&& this.designerBlockId
		)
		{
			return {
				blockId: this.designerBlockId,
			};
		}

		return {
			lid: this.landingId,
		};
	}

	/**
	 * Applies preview history entry
	 * @return {Promise}
	 */
	undo(): Promise
	{
		if (this.canUndo())
		{
			this.commandState = PENDING;
			return this.beforeUndo()
				.then(() => {
					return BX.Landing.Backend.getInstance()
						.action(
							this.getUndoBackendActionName(),
							this.getBackendActionParams(),
						)
				})
				.then(command => {
					if (command)
					{
						const params = command.params;
						const entry = new Entry({
							block: params.block,
							selector: params.selector,
							command: command.command,
							params: params,
						});

						return this.runCommand(entry, -1);
					}

					return Promise.reject();
				})
				.then(res => {
					return this.offset(-1).then(onUpdate);
				})
			;
		}

		return Promise.resolve(this);
	}


	/**
	 * Applies preview next history entry
	 * @return {Promise}
	 */
	redo(): Promise
	{
		if (this.canRedo())
		{
			this.commandState = PENDING;
			return this.beforeRedo()
				.then(() => {
					return BX.Landing.Backend.getInstance()
						.action(
							this.getRedoBackendActionName(),
							this.getBackendActionParams(),
						)
				})
				.then(command => {
					if (command)
					{
						const params = command.params;
						const entry = new Entry({
							block: params.block,
							selector: params.selector,
							command: command.command,
							params: params,
						});

						return this.runCommand(entry, 1);
					}

					return Promise.reject();
				})
				.then(res => {
					return this.offset(1).then(onUpdate);
				})
			;
		}

		return Promise.resolve(this);
	}

	runCommand(entry: Entry, offsetValue: number)
	{
		if (entry)
		{
			const command = this.commands[entry.command];
			if (command)
			{
				this.commandState = PENDING;

				return command.command(entry)
					.then(() => {
						this.commandState = RESOLVED;

						return this;
					})
					.catch(() => {
						this.commandState = RESOLVED;

						return this;
					});
			}
		}
	}

	offset(offsetValue: number): Promise<History>
	{
		if (this.commandState === PENDING)
		{
			return Promise.resolve(this);
		}

		let step = this.step + offsetValue;

		if (step >= 0 && step <= this.stackCount)
		{
			this.step = step;
		}

		return Promise.resolve(this);
	}

	/**
	 * Check that there are actions to undo
	 * @returns {boolean}
	 */
	canUndo()
	{
		return (
			this.commandState !== PENDING
			&& (this.step > 0 && this.stackCount > 0 && this.step <= this.stackCount)
		);
	}


	/**
	 * Check that there are actions to redo
	 * @returns {boolean}
	 */
	canRedo()
	{
		return (
			this.commandState !== PENDING
			&& (this.step < this.stackCount && this.step >= 0)
		);
	}


	/**
	 * Adds entry to history stack
	 * @param {BX.Landing.History.Entry} entry
	 */
	push(): Promise<History>
	{
		if (this.step < this.stackCount)
		{
			this.stackCount = this.step;
		}

		this.step++;
		this.stackCount++;

		return new Promise(resolve => {
			setTimeout(resolve, 400);
		})
			.then(() => {return loadStack(this)})
			.then(onUpdate)
		;
	}


	/**
	 * Registers unique history command
	 * @param {Command} command
	 */
	registerCommand(command)
	{
		if (command instanceof Command)
		{
			this.commands[command.id] = command;
		}
	}


	/**
	 * Removes page history from storage
	 * @param {int} pageId
	 * @return {Promise<BX.Landing.History>}
	 */
	removePageHistory(pageId)
	{
		// todo: publication clear method
		return removePageHistory(pageId, this)
			.then((history) => {
				let currentPageId;

				try
				{
					currentPageId = BX.Landing.Main.getInstance().id;
				}
				catch (err)
				{
					currentPageId = -1;
				}

				if (currentPageId === pageId)
				{
					return clear(history);
				}

				return Promise.reject();
			})
			.then(onUpdate)
			.catch(() => {});
	}


	/**
	 * Handles storage event
	 * @param {StorageEvent} event
	 */
	onStorage(event)
	{
		if (event.key === null)
		{
			if (!window.localStorage.history)
			{
				clear(this).then(onUpdate);
			}
		}
	}
}