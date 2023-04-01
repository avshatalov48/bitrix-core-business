import {Event} from 'main.core';
import {PageObject} from 'landing.pageobject';
import {RESOLVED, PENDING} from './internal/constants';
import registerBaseCommands from './internal/register-base-commands';
import removePageHistory from './internal/remove-page-history';
import loadStack from './internal/load-stack';
import fetchEntities from './internal/fetch-entities';
import removeEntities from './internal/remove-entities';
import clear from './internal/clear';
import onUpdate from './internal/on-update';
import onInit from './internal/on-init';
import Command from './history-command';
import Entry from './history-entry';
import Highlight from './history-highlight';
import editText from './action/edit-text';
import editEmbed from './action/edit-embed';
import editMap from './action/edit-map';
import editImage from './action/edit-image';
import editIcon from './action/edit-icon';
import editLink from './action/edit-link';
import sortBlock from './action/sort-block';
import addBlock from './action/add-block';
import removeBlock from './action/remove-block';
import addCard from './action/add-card';
import removeCard from './action/remove-card';
import addNode from './action/add-node';
import removeNode from './action/remove-node';
import editStyle from './action/edit-style';
import updateContent from './action/update-content';
import {Main} from 'landing.main';

/**
 * Implements interface for works with landing history
 * Implements singleton pattern use as BX.Landing.History.getInstance()
 * @memberOf BX.Landing
 */
export class History
{
	static TYPE_LANDING = 'L';
	static TYPE_DESIGNER_BLOCK = 'D';

	stack: number;
	commands: {};
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
		this.stack = 0;
		this.commands = {};
		this.step = 0;
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
	static Highlight = Highlight;
	// todo: need?
	// static Action = {
	// 	editText,
	// 	editEmbed,
	// 	editMap,
	// 	editImage,
	// 	editIcon,
	// 	editLink,
	// 	sortBlock,
	// 	addBlock,
	// 	removeBlock,
	// 	addCard,
	// 	removeCard,
	// 	editStyle,
	// 	addNode,
	// 	removeNode,
	// 	updateContent
	// };

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

	getUndoAction(): string
	{
		if (this.type === History.TYPE_DESIGNER_BLOCK)
		{
			return "History::undoDesignerBlock";
		}

		return "History::undoLanding";
	}

	getRedoAction(): string
	{
		if (this.type === History.TYPE_DESIGNER_BLOCK)
		{
			return "History::redoDesignerBlock";
		}

		return "History::redoLanding";
	}

	getActionParams(): string
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
	undo()
	{
		if (this.canUndo())
		{
			return BX.Landing.Backend.getInstance()
				.action(
					this.getUndoAction(),
					this.getActionParams(),
				)
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
		}

		return Promise.resolve(this);
	}


	/**
	 * Applies preview next history entry
	 * @return {Promise}
	 */
	redo()
	{
		if (this.canRedo())
		{
			return BX.Landing.Backend.getInstance()
				.action(
					this.getRedoAction(),
					this.getActionParams(),
				)
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
		}

		return Promise.resolve(this);
	}

	offset(offsetValue: number): Promise<History>
	{
		if (this.commandState === PENDING)
		{
			return Promise.resolve(this);
		}

		let step = this.step + offsetValue;

		if (step >= 0 && step <= this.stack)
		{
			this.step = step;
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
						// todo: how check and process error
						return this.offset(offsetValue);
					});
			}
		}
	}


	/**
	 * Check that there are actions to undo
	 * @returns {boolean}
	 */
	canUndo()
	{
		return (
			this.commandState !== PENDING
			&& (this.step > 0 && this.stack > 0 && this.step <= this.stack)
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
			&& (this.step < this.stack && this.step >= 0)
		);
	}


	/**
	 * Adds entry to history stack
	 * @param {BX.Landing.History.Entry} entry
	 */
	push(entry)
	{
		if (this.step < this.stack)
		{
			this.stack = this.step;
		}

		this.step++;
		this.stack++;

		onUpdate(this);
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


	/**
	 * Handles new branch events
	 * @param {BX.Landing.History.Entry[]} entries
	 * @return {Promise<History>}
	 */
	onNewBranch(entries)
	{
		return fetchEntities(entries, this)
			.then((entities) => {
				return removeEntities(entities, this);
			});
	}
}