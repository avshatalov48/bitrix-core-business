;(function ()
{
	'use strict';

	BX.namespace('BX.Translate');

	if (BX.Translate.Process)
	{
		return;
	}

	/**
	 * Long running process.
	 *
	 * @event BX.Translate.Process.StateChanged
	 * @event BX.Translate.Process.BeforeRequestStart
	 *
	 * @constructor
	 */
	BX.Translate.Process = function()
	{
		/** @var {String} */
		this.id = '';

		this.settings = {};

		/** @var {String} */
		this.controller = '';
		this.controllerDefault = '';
		/** @var {String} */
		this.action = '';
		/** @var {String} */
		this.method = 'POST';
		/** @var {Object} */
		this.params = {};

		/** @var {XMLHttpRequest} */
		this.xhr = null;


		/** @var {Object} */
		this.option = {};

		this.state = this.STATES.intermediate;
		/** @var {Boolean} */
		this.isRequestRunning = false;

		/** @var {Object} */
		this.messages = {};

		/** @var {BX.Translate.ProcessDialog} */
		this.dialog = null;

		/** @var {Array} */
		this.queue = [];
		this.currentStep = -1;

		this.sToken = "";
		this.cToken = "";
		this.token = "";

		/** @var {function} */
		this.handlers = {};
	};

	BX.Translate.Process.prototype = {

		STATES: {
			intermediate: "INTERMEDIATE",
			running: "RUNNING",
			completed: "COMPLETED",
			stopped: "STOPPED",
			error: "ERROR",
			canceling: "CANCELING"
		},

		STATUSES: {
			progress: "PROGRESS",
			completed: "COMPLETED"
		},

		/**
		 * @param {Object} settings
		 * @param {String} [settings.id]
		 * @param {String} [settings.controller]
		 * @param {String} [settings.controllerDefault]
		 * @param {String} [settings.method]
		 * @param {String} [settings.action]
		 * @param {Object} [settings.params]
		 * @param {Object} [settings.messages]
		 *
		 * @param {Object} [settings.handlers]
		 * @param {function} [settings.handlers.StateChanged]
		 * @param {function} [settings.handlers.RequestStart]
		 * @param {function} [settings.handlers.RequestStop]
		 * @param {function} [settings.handlers.RequestFinalize]
		 *
		 * @param {Array} [settings.optionsFields]
		 * @param {String} [settings.optionsFields.name]
		 * @param {String} [settings.optionsFields.type]
		 * @param {String} [settings.optionsFields.title]
		 * @param {String} [settings.optionsFields.value]
		 *
		 * @constructor
		 */
		init: function (settings)
		{
			this.settings = settings ? settings : {};

			this.id = BX.type.isNotEmptyString(this.settings.id) ?
				this.settings.id : "TranslateProcess_" + Math.random().toString().substring(2);


			this.controller = this.getSetting('controller', '');
			if (!BX.type.isNotEmptyString(this.controller))
			{
				throw "BX.Translate.Process: Could not find ajax controller endpoint.";
			}
			this.controllerDefault = this.controller;

			this.method = this.getSetting("method", "POST");

			this.action = this.getSetting("action", "");
			if (!BX.type.isNotEmptyString(this.action))
			{
				this.action = '';
			}

			this.queue = this.getSetting("queue");
			if (!BX.type.isArray(this.queue))
			{
				this.queue = [];
			}

			this.params = this.getSetting("params");
			if (!this.params)
			{
				this.params = {};
			}

			this.messages = this.getSetting("messages");
			if (!this.messages)
			{
				this.messages = {};
			}

			var optionsFields = this.getSetting("optionsFields");
			if (!optionsFields)
			{
				this.setSetting("optionsFields", {});
			}

			this.handlers = this.getSetting("handlers");
			if (!this.handlers)
			{
				this.handlers = {};
			}

			this.sToken = this.getSetting("sToken", "");
			if (!BX.type.isNotEmptyString(this.sToken))
			{
				throw "BX.Translate.Process: parameter 'sToken' is not found.";
			}
			this.refreshToken();
		},

		refreshToken: function ()
		{
			this.cToken = "c" + Date.now();
			this.token = this.sToken + this.cToken;
			this.setParam("PROCESS_TOKEN", this.token);
			return this;
		},

		addQueueItem: function (item)
		{
			this.queue.push(item);
			return this;
		},
		getQueueLength: function ()
		{
			return this.queue.length;
		},

		/**
		 * @param {string} optionName
		 * @param {string} val
		 */
		setOptionFieldValue: function (optionName, val)
		{
			var optionsFields = this.getSetting('optionsFields');
			if (optionsFields[optionName])
			{
				optionsFields[optionName].value = val;
			}
			return this;
		},


		saveOptionFieldValues: function(values)
		{
			var valuesToStore = {};
			if ("sessionStorage" in window)
			{
				var option, optionName,
					optionsFields = this.getSetting('optionsFields');
				for (optionName in optionsFields)
				{
					if (optionsFields.hasOwnProperty(optionName))
					{
						option = optionsFields[optionName];
						switch (option["type"])
						{
							case "checkbox":
							case "select":
							case "radio":
								if (optionName in values)
								{
									valuesToStore[optionName] = values[optionName];
								}
								break;
						}
					}
				}
			}
			if (BX.type.isNotEmptyObject(valuesToStore))
			{
				window.sessionStorage.setItem("bx.translate.options." + this.getId(), JSON.stringify(valuesToStore));
			}
		},

		loadOptionFieldValues: function()
		{
			var values = {};
			if ("sessionStorage" in window)
			{
				values = JSON.parse(window.sessionStorage.getItem("bx.translate.options." + this.getId()));
				if (!BX.type.isPlainObject(values))
				{
					values = {};
				}
			}
			return values;
		},

		/**
		 * @return {BX.Translate.ProcessDialog}
		 */
		getDialog: function ()
		{
			if (!(this.dialog instanceof BX.Translate.ProcessDialog))
			{
				this.dialog = BX.Translate.ProcessDialogManager.create({
					id: this.id,
					optionsFields: this.getSetting('optionsFields', {}),
					optionsFieldsValue: this.loadOptionFieldValues(),
					messages: {
						title: this.getMessage("DialogTitle"),
						summary: this.getMessage("DialogSummary"),
						startButton: this.getMessage("DialogStartButton"),
						stopButton: this.getMessage("DialogStopButton"),
						closeButton: this.getMessage("DialogCloseButton"),
						downloadButton: this.getMessage("DialogExportDownloadButton"),
						clearButton: this.getMessage("DialogExportClearButton")
					},
					showButtons: this.getSetting("showButtons"),
					handlers: {
						start: BX.delegate(this.start, this),
						stop: BX.delegate(this.stop, this),
						dialogShown: (typeof(this.handlers.dialogShown) == 'function' ? this.handlers.dialogShown : null),
						dialogClosed: (typeof(this.handlers.dialogClosed) == 'function' ? this.handlers.dialogClosed : null)
					}
				});
			}

			return this.dialog;
		},

		showDialog: function ()
		{
			this.getDialog()
				.setSetting("optionsFieldsValue", this.loadOptionFieldValues())
				.show();

			if (!this.isRequestRunning)
			{
				this.setState(this.STATES.intermediate);
			}

			return this;
		},

		closeDialog: function ()
		{
			if (this.isRequestRunning)
			{
				this.stop();
			}
			this.getDialog().close();

			return this;
		},

		destroy: function ()
		{
			if (this.dialog instanceof BX.Translate.ProcessDialog)
			{
				this.dialog.close();
				BX.Translate.ProcessDialogManager.delete(this.dialog.getId());
				this.dialog = null;
			}

			if(this.xhr instanceof XMLHttpRequest)
			{
				try
				{
					this.xhr.abort();
				}
				catch (e){}
				this.xhr = null;
			}
		},

		getId: function ()
		{
			return this.id;
		},
		getSetting: function (name, defaultVal)
		{
			return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultVal;
		},
		setSetting: function (name, val)
		{
			this.settings[name] = val;
			return this;
		},
		getMessage: function (name, placeholders)
		{
			var ret = '';
			placeholders = placeholders || {};
			if (BX.type.isNotEmptyString(this.messages[name]))
			{
				ret = this.messages[name];
				if (BX.type.isPlainObject(placeholders))
				{
					for (var key in placeholders)
					{
						if (placeholders.hasOwnProperty(key))
						{
							ret = ret.replace('#'+key+'#', placeholders[key]);
						}
					}
				}
			}
			return ret;
		},
		getState: function () {
			return this.state;
		},
		getController: function ()
		{
			return this.controller;
		},
		setController: function (controller)
		{
			this.controller = controller;
			return this;
		},
		getAction: function ()
		{
			return this.action;
		},
		setAction: function (action)
		{
			this.action = action;
			return this;
		},
		getParams: function ()
		{
			return this.params;
		},
		setParams: function (params)
		{
			this.params = params;
			return this;
		},
		getParam: function (key)
		{
			return this.params[key] ? this.params[key] : null;
		},
		setParam: function (key, value)
		{
			this.params[key] = value;
			return this;
		},

		/**
		 * @param {string} type Event type.
		 * @param {Array} args Arguments.
		 * @return void
		 */
		callHandler: function (type, args)
		{
			if (typeof(this.handlers[type]) == 'function')
			{
				this.handlers[type].apply(this, args);
			}
		},
		/**
		 * @param {string} type Event type.
		 * @param {function} handler Function.
		 * @return void
		 */
		setHandler: function (type, handler)
		{
			if (typeof(handler) == 'function')
			{
				this.handlers[type] = handler;
			}
			return this;
		},

		callAction: function(action)
		{
			this.setAction(action);
			this.refreshToken();
			this.startRequest();
		},

		start: function (startStep)
		{
			this.refreshToken();

			startStep = startStep || 1;
			if (
				this.state === this.STATES.intermediate ||
				this.state === this.STATES.stopped ||
				this.state === this.STATES.completed
			)
			{
				if(!this.getDialog().checkOptions())
				{
					return;
				}

				this.getDialog().setError("").setWarning("");

				if (this.getQueueLength() > 0)
				{
					this.currentStep = 0;
					if (startStep > 1)
					{
						this.currentStep = startStep - 1;
					}

					if (BX.type.isNotEmptyString(this.queue[this.currentStep].controller))
					{
						this.setController(this.queue[this.currentStep].controller);
					}

					if (!BX.type.isNotEmptyString(this.queue[this.currentStep].action))
					{
						throw "BX.Translate.Process: Could not find controller action at the queue position.";
					}
					this.setAction(this.queue[this.currentStep].action);

					this.startRequest();

					this.getDialog().setSummary(this.queue[this.currentStep].title);
				}
				else
				{
					this.startRequest();
				}
			}
			return this;
		},

		stop: function ()
		{
			if (this.state === this.STATES.running)
			{
				this.stopRequest();
				this.currentStep = -1;
			}
			return this;
		},

		/**
		 * @param {String} state
		 * @param {Object} result
		 */
		setState: function (state, result)
		{
			if (this.state === state)
			{
				return this;
			}

			result = result ? result : {};

			this.state = state;
			if (state === this.STATES.intermediate || state === this.STATES.stopped)
			{
				this.getDialog().lockButton("start", false);
				this.getDialog().lockButton("stop", true);
				this.getDialog().showButton("close", true);
			}
			else if (state === this.STATES.running)
			{
				this.getDialog().lockButton("start", true);
				this.getDialog().lockButton("stop", false);
				this.getDialog().showButton("close", false);
			}
			else if (state === this.STATES.canceling)
			{
				this.getDialog().lockButton("start", true);
				this.getDialog().lockButton("stop", true);
				this.getDialog().showButton("close", false);
				this.getDialog().hideProgressBar();
			}
			else if (state === this.STATES.error)
			{
				this.getDialog().lockButton("start", true);
				this.getDialog().lockButton("stop", true);
				this.getDialog().showButton("close", true);
			}
			else if (state === this.STATES.completed)
			{
				this.getDialog().lockButton("start", true);
				this.getDialog().lockButton("stop", true);
				this.getDialog().showButton("close", true);
				this.getDialog().hideProgressBar();
			}

			this.callHandler('StateChanged', [state, result]);

			BX.onCustomEvent(this, 'BX.Translate.Process.StateChanged', [this, state, result]);

			return this;
		},

		startRequest: function ()
		{
			if (this.isRequestRunning || this.state === this.STATES.canceling)
			{
				return;
			}
			this.isRequestRunning = true;

			var actionData = new FormData();

			function appendData(data)
			{
				if (BX.type.isNotEmptyObject(data))
				{
					for(var name in data)
					{
						if (data.hasOwnProperty(name))
						{
							actionData.append(name, data[name])
						}
					}
				}
			}

			appendData(this.params);
			appendData(this.queue[this.currentStep].params);

			var initialOptions = this.getDialog().getOptions();
			if (BX.type.isNotEmptyObject(initialOptions))
			{
				appendData(initialOptions);
				this.option = initialOptions;
				this.saveOptionFieldValues(initialOptions);
			}
			else if (BX.type.isNotEmptyObject(this.option))
			{
				for (var k in this.option)
				{
					if (this.option.hasOwnProperty(k))
					{
						// don't repeat file uploading
						if (this.option[k] instanceof File)
						{
							delete (this.option[k]);
						}
					}
				}
				appendData(this.option);
			}

			this.setState(this.STATES.running);

			this.callHandler('RequestStart', [actionData]);

			BX.onCustomEvent(this, 'BX.Translate.Process.BeforeRequestStart', [this, actionData]);

			BX.ajax.runAction
			(
				this.controller + '.' + this.getAction(),
				{
					data: actionData,
					method: this.method,
					onrequeststart: BX.delegate(this.onRequestStart, this)
				}
			)
			.then(
				BX.delegate(this.onRequestSuccess, this),
				BX.delegate(this.onRequestFailure, this)
			);
		},

		stopRequest: function ()
		{
			if (this.state === this.STATES.canceling)
			{
				return;
			}

			this.setState(this.STATES.canceling);

			if(this.xhr instanceof XMLHttpRequest)
			{
				try
				{
					this.xhr.abort();
				}
				catch (e){}
			}

			var actionData = BX.clone(this.params);

			actionData.cancelingAction = this.getAction();

			this.getDialog().setSummary(this.getMessage("RequestCanceling"));
			this.setController(this.controllerDefault);

			this.callHandler('RequestStop', [actionData]);

			BX.onCustomEvent(this, 'BX.Translate.Process.BeforeRequestStart', [this, actionData]);

			BX.ajax.runAction
			(
				this.controller + '.cancel',
				{
					data: actionData,
					method: this.method,
					onrequeststart: BX.delegate(this.onRequestStart, this)
				}
			)
			.then(
				BX.delegate(this.onRequestSuccess, this),
				BX.delegate(this.onRequestFailure, this)
			);
		},

		finalizeRequest: function ()
		{
			if (this.state === this.STATES.canceling)
			{
				return;
			}

			var actionData = BX.clone(this.params);

			this.setController(this.controllerDefault);

			this.callHandler('RequestFinalize', [actionData]);

			BX.onCustomEvent(this, 'BX.Translate.Process.BeforeRequestStart', [this, actionData]);

			BX.ajax.runAction
			(
				this.controller + '.finalize',
				{
					data: actionData,
					method: this.method,
					onrequeststart: BX.delegate(this.onRequestStart, this)
				}
			);
		},

		/**
		 * @param {XMLHttpRequest} xhr
		 */
		onRequestStart: function(xhr)
		{
			this.xhr = xhr;
		},

		/**
		 * @param {Object} result
		 * @private
		 */
		onRequestSuccess: function (result)
		{
			this.isRequestRunning = false;
			this.xhr = null;

			if (!result)
			{
				this.getDialog()
					.setError(this.getMessage("RequestError"));

				this.setState(this.STATES.error);

				return;
			}

			if (BX.type.isArray(result["errors"]) && result["errors"].length > 0)
			{
				var errors = result["errors"].slice(-10), errMessages = [];
				errors.forEach(function (err) {
					errMessages.push(err.message);
				});

				this.getDialog()
					.setError(errMessages.join("<br>"), true);

				this.setState(this.STATES.error);

				return;
			}

			result = result["data"];

			var status = BX.type.isNotEmptyString(result["STATUS"]) ? result["STATUS"] : "";
			var summary = BX.type.isNotEmptyString(result["SUMMARY"]) ? result["SUMMARY"] : "";
			var processedItems = BX.type.isNumber(result["PROCESSED_ITEMS"]) ? result["PROCESSED_ITEMS"] : 0;
			var totalItems = BX.type.isNumber(result["TOTAL_ITEMS"]) ? result["TOTAL_ITEMS"] : 0;
			var finalize = BX.type.isNotEmptyString(result["FINALIZE"]);

			var warning = BX.type.isNotEmptyString(result["WARNING"]) ? result["WARNING"] : "";
			this.getDialog().setWarning(warning);

			if (status === this.STATUSES.progress || status === this.STATUSES.completed)
			{
				if (totalItems > 0)
				{
					if (this.queue[this.currentStep].progressBarTitle)
					{
						this.getDialog()
							.setProgressBar(totalItems, processedItems, this.queue[this.currentStep].progressBarTitle);
					}
					else
					{
						this.getDialog()
							.setProgressBar(totalItems, processedItems);
					}
				}
				else
				{
					this.getDialog().hideProgressBar();
				}
			}

			if (status === this.STATUSES.progress)
			{
				if (summary !== "")
				{
					this.getDialog().setSummary(summary, true);
				}

				if (this.state === this.STATES.canceling)
				{
					this.setState(this.STATES.stopped);
				}
				else
				{
					var nextController = BX.type.isNotEmptyString(result["NEXT_CONTROLLER"]) ? result["NEXT_CONTROLLER"] : "";
					if (nextController !== "")
					{
						this.setController(nextController);
					}
					else if (BX.type.isNotEmptyString(this.queue[this.currentStep].controller))
					{
						this.setController(this.queue[this.currentStep].controller);
					}
					else
					{
						this.setController(this.controllerDefault);
					}

					var nextAction = BX.type.isNotEmptyString(result["NEXT_ACTION"]) ? result["NEXT_ACTION"] : "";
					if (nextAction !== "")
					{
						this.setAction(nextAction);
					}

					window.setTimeout(
						BX.delegate(this.startRequest, this),
						100
					);
				}
				return;
			}

			if (this.state === this.STATES.canceling)
			{
				this.getDialog().setSummary(this.getMessage("RequestCanceled"));
				this.setState(this.STATES.completed);
			}
			else if (status === this.STATUSES.completed)
			{
				if (this.getQueueLength() > 0 && this.currentStep + 1 < this.getQueueLength())
				{
					// next
					this.currentStep ++;

					if (BX.type.isNotEmptyString(this.queue[this.currentStep].controller))
					{
						this.setController(this.queue[this.currentStep].controller);
					}
					else
					{
						this.setController(this.controllerDefault);
					}

					if (!BX.type.isNotEmptyString(this.queue[this.currentStep].action))
					{
						throw "BX.Translate.Process: Could not find controller action at the queue position.";
					}

					if ('finalize' in this.queue[this.currentStep])
					{
						finalize = true;
						this.setAction(this.queue[this.currentStep].action);
					}
					else
					{
						this.setAction(this.queue[this.currentStep].action);

						this.getDialog().setSummary(this.queue[this.currentStep].title);

						window.setTimeout(
							BX.delegate(this.startRequest, this),
							100
						);
						return;
					}
				}

				if (summary !== "")
				{
					this.getDialog().setSummary(summary, true);
				}
				else
				{
					this.getDialog().setSummary(this.getMessage("RequestCompleted"));
				}

				if (BX.type.isNotEmptyString(result["DOWNLOAD_LINK"]))
				{
					this.getDialog().setDownloadButtons(
						result["DOWNLOAD_LINK"],
						result["FILE_NAME"],
						BX.delegate(function(){this.callAction('purge');}, this)
					);
				}

				this.setState(this.STATES.completed, result);

				if (finalize)
				{
					window.setTimeout(
						BX.delegate(this.finalizeRequest, this),
						100
					);
				}
			}
			else
			{
				this.getDialog().setSummary("").setError(this.getMessage("RequestError"));
				this.setState(this.STATES.error);
			}
		},

		/**
		 * @param {Object} result
		 */
		onRequestFailure: function (result)
		{
			// check manual aborting
			if (this.state === this.STATES.canceling)
			{
				return;
			}
			// check non auth
			if (
				BX.type.isPlainObject(result) &&
				("data" in result) && BX.type.isPlainObject(result.data) &&
				("ajaxRejectData" in result.data) && BX.type.isPlainObject(result.data.ajaxRejectData) &&
				("reason" in result.data.ajaxRejectData) && (result.data.ajaxRejectData.reason === "status") &&
				("data" in result.data.ajaxRejectData) && (result.data.ajaxRejectData.data === 401)
			)
			{
				this.getDialog()
					.setError(this.getMessage("AuthError"));
			}
			// check errors
			else if (
				BX.type.isPlainObject(result) &&
				("errors" in result) &&
				BX.type.isArray(result.errors) &&
				result.errors.length > 0
			)
			{
				// ignoring error of manual aborting
				if (this.state === this.STATES.canceling)
				{
					var abortingState = false;
					result.errors.forEach(function (err) {
						if (err.code === 'NETWORK_ERROR')
						{
							abortingState = true;
						}
					});
					if (abortingState)
					{
						return;
					}
				}

				var errors = result.errors.slice(-10), errMessages = [];
				errors.forEach(function (err) {
					errMessages.push(err.message);
				});

				this.getDialog()
					.setError(errMessages.join("<br>"), true);
			}
			else
			{
				this.getDialog()
					.setError(this.getMessage("RequestError"));
			}

			this.isRequestRunning = false;
			this.xhr = null;
			this.currentStep = -1;

			this.setState(this.STATES.error);
		}
	};


	/**
	 * Process manager.
	 */
	if(typeof(BX.Translate.ProcessManager) == "undefined")
	{
		BX.Translate.ProcessManager = {};
	}
	if(typeof(BX.Translate.ProcessManager.items) == "undefined")
	{
		BX.Translate.ProcessManager.items = {};
	}

	/** @return {BX.Translate.Process} */
	BX.Translate.ProcessManager.create = function(settings)
	{
		var process = new BX.Translate.Process();
		process.init(settings);
		BX.Translate.ProcessManager.items[process.getId()] = process;
		return process;
	};

	BX.Translate.ProcessManager.delete = function(id)
	{
		if (BX.Translate.ProcessManager.items.hasOwnProperty(id))
		{
			BX.Translate.ProcessManager.items[id].destroy();
			delete BX.Translate.ProcessManager.items[id];
		}
	};

	/** @return {BX.Translate.Process} */
	BX.Translate.ProcessManager.getInstance = function(id)
	{
		return BX.Translate.ProcessManager.items[id] ? BX.Translate.ProcessManager.items[id] : null;
	};

})();


