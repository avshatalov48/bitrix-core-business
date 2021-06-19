import {Event} from 'main.core';
import {PageObject} from 'landing.pageobject';
import {UNDO, REDO, INIT, RESOLVED, MAX_ENTRIES_COUNT} from './internal/constants';
import registerBaseCommands from './internal/register-base-commands';
import removePageHistory from './internal/remove-page-history';
import loadStack from './internal/load-stack';
import saveStack from './internal/save-stack';
import fetchEntities from './internal/fetch-entities';
import removeEntities from './internal/remove-entities';
import offset from './internal/offset';
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
import updateBlockState from './action/update-block-state';
import updateContent from './action/update-content';

/**
 * Implements interface for works with landing history
 * Implements singleton pattern use as BX.Landing.History.getInstance()
 * @memberOf BX.Landing
 */
export class History
{
	constructor()
	{
		this.stack = [];
		this.commands = {};
		this.position = -1;
		this.state = INIT;
		this.commandState = RESOLVED;
		this.onStorage = this.onStorage.bind(this);

		Event.bind(window, 'storage', this.onStorage);

		registerBaseCommands(this)
			.then(loadStack)
			.then(saveStack)
			.then(onInit);
	}

	static Command = Command;
	static Entry = Entry;
	static Highlight = Highlight;
	static Action = {
		editText,
		editEmbed,
		editMap,
		editImage,
		editIcon,
		editLink,
		sortBlock,
		addBlock,
		removeBlock,
		addCard,
		removeCard,
		editStyle,
		updateBlockState,
		addNode,
		removeNode,
		updateContent
	};

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
	 * Applies preview history entry
	 * @return {Promise}
	 */
	undo()
	{
		if (this.canUndo())
		{
			return offset(this, -1).then(saveStack).then(onUpdate);
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
			return offset(this, 1).then(saveStack).then(onUpdate);
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
			(this.position > 0 && this.state === REDO)
			|| (this.position > 0 && this.state === UNDO)
			|| (this.position === 0 && this.state !== UNDO)
		);
	}


	/**
	 * Check that there are actions to redo
	 * @returns {boolean}
	 */
	canRedo()
	{
		return (
			(this.position < this.stack.length - 1 && this.state !== INIT)
			|| (this.position !== -1 && this.position === this.stack.length - 1 && this.state !== REDO)
		);
	}


	/**
	 * Adds entry to history stack
	 * @param {BX.Landing.History.Entry} entry
	 */
	push(entry)
	{
		let startIndex = this.position + 1;
		const deleteCount = this.stack.length;

		if (this.state === UNDO)
		{
			startIndex -= 1;
		}

		const deletedEntries = this.stack.splice(startIndex, deleteCount, entry);

		if (this.stack.length > MAX_ENTRIES_COUNT)
		{
			deletedEntries.push(this.stack.shift());
		}

		if (deletedEntries.length)
		{
			void this.onNewBranch(deletedEntries);
		}

		this.position = this.stack.length - 1;
		this.state = REDO;
		saveStack(this).then(onUpdate);
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