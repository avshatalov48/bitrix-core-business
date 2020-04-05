;(function() {
	"use strict";

	BX.namespace("BX.Landing");

	var UNDO = "undo";
	var REDO = "redo";
	var INIT = "init";
	var RESOLVED = "resolved";
	var PENDING = "pending";

	var MAX_ENTRIES_COUNT = 100;

	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var bind = BX.Landing.Utils.bind;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;


	/**
	 * Implements interface for works with landing history
	 * Implements singleton pattern use as BX.Landing.History.getInstance()
	 * @constructor
	 */
	BX.Landing.History = function()
	{
		this.stack = [];
		this.commands = {};
		this.position = -1;
		this.state = INIT;
		this.commandState = RESOLVED;
		this.onStorage = this.onStorage.bind(this);

		bind(window, "storage", this.onStorage);

		registerBaseCommands(this)
			.then(load)
			.then(save)
			.then(onInit);
	};


	/**
	 * Stores instance of BX.Landing.History
	 * @type {BX.Landing.History}
	 */
	BX.Landing.History.instance = null;


	/**
	 * Gets history instance
	 * @returns {BX.Landing.History}
	 */
	BX.Landing.History.getInstance = function()
	{
		if (!top.BX.Landing.History.instance)
		{
			top.BX.Landing.History.instance = new BX.Landing.History();
		}

		return top.BX.Landing.History.instance;
	};


	/**
	 * Registers base internal commands
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function registerBaseCommands(history)
	{
		history.registerCommand(
			new BX.Landing.History.Command({
				id: "editText",
				undo: BX.Landing.History.Action.editText.bind(null, UNDO),
				redo: BX.Landing.History.Action.editText.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "editEmbed",
				undo: BX.Landing.History.Action.editEmbed.bind(null, UNDO),
				redo: BX.Landing.History.Action.editEmbed.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "editMap",
				undo: BX.Landing.History.Action.editMap.bind(null, UNDO),
				redo: BX.Landing.History.Action.editMap.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "editImage",
				undo: BX.Landing.History.Action.editImage.bind(null, UNDO),
				redo: BX.Landing.History.Action.editImage.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "editIcon",
				undo: BX.Landing.History.Action.editIcon.bind(null, UNDO),
				redo: BX.Landing.History.Action.editIcon.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "editLink",
				undo: BX.Landing.History.Action.editLink.bind(null, UNDO),
				redo: BX.Landing.History.Action.editLink.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "sortBlock",
				undo: BX.Landing.History.Action.sortBlock.bind(null, UNDO),
				redo: BX.Landing.History.Action.sortBlock.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "addBlock",
				undo: BX.Landing.History.Action.removeBlock.bind(null, UNDO),
				redo: BX.Landing.History.Action.addBlock.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "removeBlock",
				undo: BX.Landing.History.Action.addBlock.bind(null, UNDO),
				redo: BX.Landing.History.Action.removeBlock.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "updateStyle",
				undo: BX.Landing.History.Action.editStyle.bind(null, UNDO),
				redo: BX.Landing.History.Action.editStyle.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "addCard",
				undo: BX.Landing.History.Action.removeCard.bind(null, UNDO),
				redo: BX.Landing.History.Action.addCard.bind(null, REDO)
			})
		);

		history.registerCommand(
			new BX.Landing.History.Command({
				id: "removeCard",
				undo: BX.Landing.History.Action.addCard.bind(null, UNDO),
				redo: BX.Landing.History.Action.removeCard.bind(null, REDO)
			})
		);

		return Promise.resolve(history);
	}


	/**
	 * Parses json string
	 * @param {string} str
	 * @return {Promise<?Object|array>}
	 */
	function asyncParse(str)
	{
		return new Promise(function(resolve) {
			var worker = new Worker(
				"/bitrix/js/landing/history/worker/json-parse-worker.js"
			);
			worker.postMessage(str);
			worker.addEventListener("message", function(event) {
				resolve(event.data);
			});
		});
	}


	/**
	 * Serializes object
	 * @param {Object|array} obj
	 * @return {Promise<?String>}
	 */
	function asyncStringify(obj)
	{
		return new Promise(function(resolve) {
			var worker = new Worker(
				"/bitrix/js/landing/history/worker/json-stringify-worker.js"
			);
			worker.postMessage(obj);
			worker.addEventListener("message", function(event) {
				resolve(event.data);
			});
		});
	}


	/**
	 * Loads history from storage
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function load(history)
	{
		var currentPageId;

		try
		{
			currentPageId = BX.Landing.Main.getInstance().id;
		}
		catch(err)
		{
			currentPageId = -1;
		}

		return asyncParse(window.localStorage.history)
			.then(function(historyData) {
				return (isPlainObject(historyData) && currentPageId in historyData) ? historyData[currentPageId] : Promise.reject();
			})
			.then(function(landingData) {
				Object.keys(landingData.stack).forEach(function(key, index) {
					history.stack.push(new BX.Landing.History.Entry(landingData.stack[key]));

					if (index >= MAX_ENTRIES_COUNT)
					{
						history.stack.shift();
					}
				});

				history.position = Math.min(parseInt(landingData.position), history.stack.length-1);
				history.state = landingData.state;
				return history;
			})
			.catch(function() {
				return history;
			})
	}


	/**
	 * Saves history to storage
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function save(history)
	{
		var currentPageId;

		try
		{
			currentPageId = BX.Landing.Main.getInstance().id;
		}
		catch(err)
		{
			currentPageId = -1;
		}

		return asyncParse(window.localStorage.history)
			.then(function(historyData) {
				return isPlainObject(historyData) ? historyData : {};
			})
			.then(function(all) {
				all[currentPageId] = {};
				all[currentPageId].stack = history.stack;
				all[currentPageId].position = history.position;
				all[currentPageId].state = history.state;
				return all;
			})
			.then(asyncStringify)
			.then(function(allString) {
				window.localStorage.history = allString;
				return history;
			});
	}


	/**
	 * Clears history stack
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function clear(history)
	{
		history.stack = [];
		history.position = -1;
		history.state = INIT;
		history.commandState = RESOLVED;
		return Promise.resolve(history);
	}


	/**
	 * Removes page history from storage
	 * @param {int} pageId
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function removePageHistory(pageId, history)
	{
		return asyncParse(window.localStorage.history)
			.then(function(historyData) {
				return isPlainObject(historyData) ? historyData : {};
			})
			.then(function(all) {
				if (pageId in all)
				{
					delete all[pageId];
				}

				return all;
			})
			.then(asyncStringify)
			.then(function(allString) {
				window.localStorage.history = allString;
				return history;
			});
	}


	/**
	 * Offsets history by offset length
	 * @param {BX.Landing.History} history
	 * @param {Integer} offset
	 */
	function offset(history, offset)
	{
		if (history.commandState === PENDING)
		{
			return Promise.resolve(history);
		}

		var position = history.position + offset;
		var state = history.state;

		if (offset < 0 && history.state !== UNDO)
		{
			position += 1;
			state = UNDO;
		}

		if (offset > 0 && history.state !== REDO)
		{
			position -= 1;
			state = REDO;
		}

		if (position <= history.stack.length-1 && position >= 0)
		{
			history.position = position;
			history.state = state;

			var entry = history.stack[position];

			if (entry)
			{
				var command = history.commands[entry.command];

				if (command)
				{
					history.commandState = PENDING;

					return command[state](entry)
						.then(function() {
							history.commandState = RESOLVED;
							return history;
						})
						.catch(function() {
							history.commandState = RESOLVED;
							return history[state === UNDO ? "undo" : "redo"]();
						});
				}
			}
		}

		return Promise.resolve(history);
	}


	/**
	 * Calls on init history object
	 * @param history
	 * @return {Promise<BX.Landing.History>}
	 */
	function onInit(history)
	{
		fireCustomEvent(top.window, "BX.Landing.History:init", [history]);
		return Promise.resolve(history);
	}


	/**
	 * Calls on update history stack
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function onUpdate(history)
	{
		fireCustomEvent(top.window, "BX.Landing.History:update", [history]);
		return Promise.resolve(history);
	}


	/**
	 * Calls on history actualize event
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function onActualize(history)
	{
		fireCustomEvent(top.window, "BX.Landing.History:actualize", [history]);
		return Promise.resolve(history);
	}


	/**
	 * Calls on history creates new branch
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function onNewBranch(history)
	{
		fireCustomEvent(top.window, "BX.Landing.History:newBranch", [history]);
		return Promise.resolve(history);
	}


	/**
	 * Makes request with removed entities
	 * @param {{
	 * 		blocks: int[],
	 * 		images: {block: int, id: int}[]
	 * 	}} entities
	 * @param {BX.Landing.History} history
	 * @return {Promise<BX.Landing.History>}
	 */
	function removeEntities(entities, history)
	{
		// if (entities.blocks.length || entities.images.length)
		// {
		// 	return BX.Landing.Backend.getInstance().action("Landing::removeEntities", {data: entities})
		// 		.then(function() {
		// 			return onNewBranch(history);
		// 		})
		// 		.then(onUpdate);
		// }

		return Promise.resolve(history);
	}


	/**
	 * Fetches entities from entries
	 * @param {BX.Landing.History.Entry[]} items
	 * @param {BX.Landing.History} history
	 * @return {Promise}
	 */
	function fetchEntities(items, history)
	{
		var entities = {blocks: [], images: []};

		items.forEach(function(item) {
			if (item.command === "addBlock")
			{
				entities.blocks.push(item.block);
			}

			if (item.command === "editImage")
			{
				entities.images.push({block: item.block, id: item.redo.id});
			}
		});

		return Promise.resolve(entities);
	}


	BX.Landing.History.prototype = {
		/**
		 * Applies preview history entry
		 * @return {Promise}
		 */
		undo: function()
		{
			if (this.canUndo())
			{
				return offset(this, -1).then(save).then(onUpdate);
			}

			return Promise.resolve(this);
		},


		/**
		 * Applies preview next history entry
		 * @return {Promise}
		 */
		redo: function()
		{
			if (this.canRedo())
			{
				return offset(this, 1).then(save).then(onUpdate)
			}

			return Promise.resolve(this);
		},


		/**
		 * Check that there are actions to undo
		 * @returns {boolean}
		 */
		canUndo: function()
		{
			return (
				(this.position > 0 && this.state === REDO) ||
				(this.position > 0 && this.state === UNDO) ||
				(this.position === 0 && this.state !== UNDO)
			);
		},


		/**
		 * Check that there are actions to redo
		 * @returns {boolean}
		 */
		canRedo: function()
		{
			return (
				(this.position < this.stack.length-1 && this.state !== INIT) ||
				(this.position !== -1 && this.position === this.stack.length-1 && this.state !== REDO)
			);
		},


		/**
		 * Adds entry to history stack
		 * @param {BX.Landing.History.Entry} entry
		 */
		push: function(entry)
		{
			var startIndex = this.position+1;
			var deleteCount = this.stack.length;

			if (this.state === UNDO)
			{
				startIndex -= 1;
			}

			var deletedEntries = this.stack.splice(startIndex, deleteCount, entry);

			if (this.stack.length > MAX_ENTRIES_COUNT)
			{
				deletedEntries.push(this.stack.shift());
			}

			if (deletedEntries.length)
			{
				this.onNewBranch(deletedEntries);
			}

			this.position = this.stack.length-1;
			this.state = REDO;
			save(this).then(onUpdate);
		},


		/**
		 * Registers unique history command
		 * @param {BX.Landing.History.Command} command
		 */
		registerCommand: function(command)
		{
			if (command instanceof BX.Landing.History.Command)
			{
				this.commands[command.id] = command;
			}
		},


		/**
		 * Removes page history from storage
		 * @param {int} pageId
		 * @return {Promise<BX.Landing.History>}
		 */
		removePageHistory: function(pageId)
		{
			return removePageHistory(pageId, this)
				.then(function(history) {
					var currentPageId;

					try
					{
						currentPageId = BX.Landing.Main.getInstance().id;
					}
					catch(err)
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
				.catch(function() {})
		},


		/**
		 * Handles storage event
		 * @param {StorageEvent} event
		 */
		onStorage: function(event)
		{
			if (event.key === null)
			{
				if (!window.localStorage.history)
				{
					clear(this).then(onUpdate);
				}
			}
		},


		/**
		 * Handles new branch events
		 * @param {BX.Landing.History.Entry[]} entries
		 * @return {Promise<BX.Landing.History>}
		 */
		onNewBranch: function(entries)
		{
			return fetchEntities(entries, this)
				.then(function(entities) {
					return removeEntities(entities, this);
				}.bind(this));
		}
	};
})();
