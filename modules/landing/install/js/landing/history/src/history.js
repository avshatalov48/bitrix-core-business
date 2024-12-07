import {Main} from 'landing.main';
import {PageObject} from 'landing.pageobject';
import {Backend} from 'landing.backend';
import {RESOLVED, PENDING, HISTORY_TYPES} from './internal/constants';
import registerBaseCommands from './internal/register-base-commands';
import removePageHistory from './internal/remove-page-history';
import clear from './internal/clear';
import onUpdate from './internal/on-update';
import onInit from './internal/on-init';
import Command from './history-command';
import Entry from './history-entry';
import Stack from './stack';
import Highlight from './history-highlight';    // not delete - just for export

import './css/style.css';

/**
 * Implements interface for works with landing history
 * Implements singleton pattern use as BX.Landing.History.getInstance()
 * @memberOf BX.Landing
 */
export class History
{
	/**
	 * Stack of action commands
	 */
	stack: ?Stack = null;

	/**
	 * Key - command name, value - a Command object
	 */
	commands: {[string]: Command} = {};

	/**
	 * If command now running - set to PENDING
	 * @type {string}
	 */
	commandState: string = RESOLVED;

	/**
	 * Type of current entity
	 * @type {string}
	 */
	entityType: string = HISTORY_TYPES.landing;

	/**
	 * Landing or Block ID in relation to type
	 * @type {number}
	 */
	entityId: number;

	constructor()
	{
		try
		{
			this.entityId = Main.getInstance().id;
		}
		catch (err)
		{
			this.entityId = -1;
		}

		this.stack = new Stack(this.entityId);
		this.stack.init()
			.then(() => {
				return registerBaseCommands(this)
			})
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
	 * Set special type for designer block history
	 * @param blockId
	 * @return {Promise<BX.Landing.History>|*}
	 */
	setTypeDesignerBlock(blockId: number): Promise<History>
	{
		this.entityType = HISTORY_TYPES.designerBlock;
		this.entityId = blockId;

		return this.stack.setTypeDesignerBlock(blockId)
			.then(() => {
				return this;
			})
	}

	getEntityId(): number
	{
		return this.entityId;
	}

	beforeUndo(): Promise
	{
		const commandName = this.stack.getCommandName();
		if (commandName && this.commands[commandName])
		{
			const command = this.commands[commandName];

			return command.onBeforeCommand();
		}

		return Promise.resolve();
	}

	beforeRedo(): Promise
	{
		const commandName = this.stack.getCommandName(false);
		if (commandName && this.commands[commandName])
		{
			const command = this.commands[commandName];

			return command.onBeforeCommand();
		}

		return Promise.resolve();
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
					return Backend.getInstance()
						.action(
							this.getBackendActionName(true),
							this.getBackendActionParams(true),
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

						return this.runCommand(entry);
					}

					return Promise.reject();
				})
				.then(() => {
					return this.offset();
				})
				.then(onUpdate)
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
					return Backend.getInstance()
						.action(
							this.getBackendActionName(false),
							this.getBackendActionParams(false),
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

						return this.runCommand(entry);
					}

					return Promise.reject();
				})
				.then(() => {
					return this.offset(false);
				})
				.then(onUpdate)
			;
		}

		return Promise.resolve(this);
	}

	/**
	 * Get name for backend action
	 * @param {boolean} undo - true, if need undo, false for redo
	 * @return {string}
	 */
	getBackendActionName(undo: boolean = true): string
	{
		if (this.entityType === HISTORY_TYPES.designerBlock)
		{
			return undo ? 'History::undoDesignerBlock' : 'History::redoDesignerBlock';
		}

		return undo ? 'History::undoLanding' : 'History::redoLanding';
	}

	/**
	 * Get id for entity for backend action
	 * @param {boolean} undo - true, if need undo, false for redo
	 * @return {string}
	 */
	getBackendActionParams(undo: boolean = true): string
	{
		if (this.entityType === HISTORY_TYPES.designerBlock)
		{
			return {
				blockId: this.entityId,
			};
		}

		return {
			lid: this.stack.getCommandEntityId(undo),
		};
	}

	runCommand(entry: Entry)
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
					.catch(err => {
						console.error(`History error in command ${command.id}.`, err);
						this.commandState = RESOLVED;

						return this;
					});
			}
		}
	}

	offset(undo: boolean = true): Promise<History>
	{
		if (this.commandState === PENDING)
		{
			return Promise.resolve(this);
		}

		return this.stack.offset(undo)
			.then(() => {
				return this;
			});
	}

	/**
	 * Check that there are actions to undo
	 * @returns {boolean}
	 */
	canUndo()
	{
		return (
			this.commandState !== PENDING
			&& this.stack.canUndo()
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
			&& this.stack.canRedo()
		);
	}


	/**
	 * Adds entry to history stack
	 */
	push(): Promise<History>
	{
		return this.stack.push()
			.then(() => {
				return onUpdate(this);
			})
		;
	}


	/**
	 * Registers unique history command
	 * @param {Command} command
	 */
	registerCommand(command: Command)
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
}