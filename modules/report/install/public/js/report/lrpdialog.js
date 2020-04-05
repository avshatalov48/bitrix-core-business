BX.namespace("BX.Report");

if(typeof(BX.Report.LongRunningProcessState) === "undefined")
{
	BX.Report.LongRunningProcessState =
		{
			intermediate: 0,
			running: 1,
			completed: 2,
			stoped: 3,
			error: 4
		};
}

if(typeof(BX.Report.LongRunningProcessDialog) === "undefined")
{
	BX.Report.LongRunningProcessDialog = function()
	{
		this._id = "";
		this._settings = {};
		this._serviceUrl = "";
		this._params = {};
		this._initialOptions = {};
		this._dlg = null;
		this._buttons = {};
		this._summary = null;
		this._initialOptionsBlock = null;
		this._isSummaryHtml = false;
		this._isShown = false;
		this._state = BX.Report.LongRunningProcessState.intermediate;
		this._cancelRequest = false;
		this._requestIsRunning = false;
	};
	BX.Report.LongRunningProcessDialog.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = BX.type.isNotEmptyString(id) ?
					id : "report_long_run_proc_" + Math.random().toString().substring(2);

				this._settings = settings ? settings : {};

				this._serviceUrl = this.getSetting("serviceUrl", "");
				if(!BX.type.isNotEmptyString(this._serviceUrl))
				{
					throw "BX.Report.LongRunningProcessDialog: Could not find service url.";
				}

				this._action = this.getSetting("action", "");
				if(!BX.type.isNotEmptyString(this._action))
				{
					throw "BX.Report.LongRunningProcessDialog: Could not find action.";
				}

				this._params = this.getSetting("params");
				if(!this._params)
				{
					this._params = {};
				}

				this._initialOptions = this.getSetting("initialOptions");
				if(!this._initialOptions)
				{
					this._initialOptions = {};
				}

				this._isSummaryHtml = !!(this.getSetting("isSummaryHtml", false));
			},
			getId: function()
			{
				return this._id;
			},
			getSetting: function (name, defaultval)
			{
				return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
			},
			setSetting: function (name, val)
			{
				this._settings[name] = val;
			},
			getMessage: function(name)
			{
				var result = "";

				if (BX.Report.LongRunningProcessDialog.messages
					&& BX.Report.LongRunningProcessDialog.messages.hasOwnProperty(name))
				{
					result = BX.Report.LongRunningProcessDialog.messages[name];
				}

				return result;
			},
			getState: function()
			{
				return this._state;
			},
			getServiceUrl: function()
			{
				return this._serviceUrl;
			},
			getAction: function()
			{
				return this._action;
			},
			getParams: function()
			{
				return this._params;
			},
			show: function()
			{
				if(this._isShown)
				{
					return;
				}

				this._dlg = BX.PopupWindowManager.create(
					this._id.toLowerCase(),
					this._anchor,
					{
						className: "bx-report-dialog-wrap bx-report-dialog-long-run-proc",
						autoHide: false,
						bindOptions: { forceBindPosition: false },
						buttons: this._prepareDialogButtons(),
						//className: "",
						closeByEsc: false,
						closeIcon: false,
						content: this._prepareDialogContent(),
						draggable: true,
						events: { onPopupClose: BX.delegate(this._onDialogClose, this) },
						offsetLeft: 0,
						offsetTop: 0,
						titleBar: this.getSetting("title", "")
					}
				);
				if(!this._dlg.isShown())
				{
					this._dlg.show();
				}
				this._isShown = this._dlg.isShown();
			},
			close: function()
			{
				if(!this._isShown)
				{
					return;
				}

				if(this._dlg)
				{
					this._dlg.close();
				}
				this._isShown = false;
			},
			start: function()
			{
				if(this._state === BX.Report.LongRunningProcessState.intermediate
					|| this._state === BX.Report.LongRunningProcessState.stoped)
				{
					this._startRequest();
				}
			},
			stop: function()
			{
				if(this._state === BX.Report.LongRunningProcessState.running)
				{
					this._cancelRequest = true;
				}
			},
			_prepareDialogContent: function()
			{
				var summary = this.getSetting("summary", "");
				var summaryData = {
					attrs: { className: "bx-report-dialog-long-run-proc-summary" }
				};
				if (this._isSummaryHtml)
				{
					summaryData["html"] = summary;
				}
				else
				{
					summaryData["text"] = summary;
				}
				this._summary = BX.create(
					"DIV",
					summaryData
				);

				var option, optionName, optionBlock, optionId, numberOfOptions = 0;
				for (optionName in this._initialOptions)
				{
					if (this._initialOptions.hasOwnProperty(optionName))
					{
						option = this._initialOptions[optionName];
						if (BX.type.isPlainObject(option)
							&& option.hasOwnProperty("name")
							&& option.hasOwnProperty("type")
							&& option.hasOwnProperty("title")
							&& option.hasOwnProperty("value"))
						{
							optionBlock = null;
							switch (option["type"])
							{
								case "checkbox":
									optionId = this._id + "_opt_" + optionName;
									var checkboxAttrs = {
										id: optionId,
										type: option["type"],
										name: optionName
									};
									if (option["value"] === 'Y')
										checkboxAttrs["checked"] = "checked";
									optionBlock = BX.create(
										"DIV",
										{
											children: [
												BX.create(
													"SPAN",
													{
														children: [
															BX.create("INPUT", {attrs: checkboxAttrs}),
															BX.create(
																"LABEL",
																{
																	attrs: { for: optionId },
																	text: option["title"]
																}
															)
														]
													}
												)
											]
										}
									);
									checkboxAttrs = null;
									break;
							}
							if (optionBlock !== null)
							{
								if (this._initialOptionsBlock === null)
								{
									this._initialOptionsBlock = BX.create(
										"DIV", { attrs: { className: "bx-report-dialog-long-run-proc-options" } }
									);
								}
								this._initialOptionsBlock.appendChild(optionBlock);
								numberOfOptions++;
							}
						}
					}
				}

				var summaryElements = [this._summary];
				if (this._initialOptionsBlock)
					summaryElements.push(this._initialOptionsBlock);

				return BX.create(
					"DIV",
					{
						attrs: { className: "bx-report-dialog-long-run-proc-popup" },
						children: summaryElements
					}
				);
			},
			_prepareDialogButtons: function()
			{
				this._buttons = {};

				var startButtonText = this.getMessage("startButton");
				this._buttons["start"] = new BX.PopupWindowButton(
					{
						text: startButtonText !== "" ? startButtonText : "Start",
						className: "popup-window-button-accept",
						events:
							{
								click : BX.delegate(this._handleStartButtonClick, this)
							}
					}
				);

				var stopButtonText = this.getMessage("stopButton");
				this._buttons["stop"] = new BX.PopupWindowButton(
					{
						text: stopButtonText !== "" ? stopButtonText : "Stop",
						className: "popup-window-button-disable",
						events:
							{
								click : BX.delegate(this._handleStopButtonClick, this)
							}
					}
				);

				var closeButtonText = this.getMessage("closeButton");
				this._buttons["close"] = new BX.PopupWindowButtonLink(
					{
						text: closeButtonText !== "" ? closeButtonText : "Close",
						className: "popup-window-button-link-cancel",
						events:
							{
								click : BX.delegate(this._handleCloseButtonClick, this)
							}
					}
				);

				return [ this._buttons["start"], this._buttons["stop"], this._buttons["close"] ];
			},
			_onDialogClose: function(e)
			{
				if(this._dlg)
				{
					this._dlg.destroy();
					this._dlg = null;
				}

				this._setState(BX.Report.LongRunningProcessState.intermediate);
				this._buttons = {};
				this._summary = null;

				this._isShown = false;

				BX.onCustomEvent(this, 'ON_CLOSE', [this]);
			},
			_handleStartButtonClick: function()
			{
				this.start();
			},
			_handleStopButtonClick: function()
			{
				this.stop();
			},
			_handleCloseButtonClick: function()
			{
				if(this._state !== BX.Report.LongRunningProcessState.running)
				{
					this._dlg.close();
				}
			},
			_lockButton: function(bid, lock)
			{
				var btn = typeof(this._buttons[bid]) !== "undefined" ? this._buttons[bid] : null;
				if(!btn)
				{
					return;
				}

				if(!!lock)
				{
					BX.removeClass(btn.buttonNode, "popup-window-button-accept");
					BX.addClass(btn.buttonNode, "popup-window-button-disable");
				}
				else
				{
					BX.removeClass(btn.buttonNode, "popup-window-button-disable");
					BX.addClass(btn.buttonNode, "popup-window-button-accept");
				}
			},
			_showButton: function(bid, show)
			{
				var btn = typeof(this._buttons[bid]) !== "undefined" ? this._buttons[bid] : null;
				if(btn)
				{
					btn.buttonNode.style.display = !!show ? "" : "none";
				}
			},
			_setSummary: function(content, isHtml)
			{
				if (this._initialOptionsBlock)
				{
					BX.remove(this._initialOptionsBlock);
					this._initialOptionsBlock = null;
				}
				isHtml = !!isHtml;
				if(this._summary)
				{
					if (isHtml)
						this._summary.innerHTML = content;
					else
						this._summary.innerHTML = BX.util.htmlspecialchars(content);
				}
			},
			_setState: function(state)
			{
				if(this._state === state)
				{
					return;
				}

				this._state = state;
				if(state === BX.Report.LongRunningProcessState.intermediate
					|| state === BX.Report.LongRunningProcessState.stoped)
				{
					this._lockButton("start", false);
					this._lockButton("stop", true);
					this._showButton("close", true);
				}
				else if(state === BX.Report.LongRunningProcessState.running)
				{
					this._lockButton("start", true);
					this._lockButton("stop", false);
					this._showButton("close", false);
				}
				else if(state === BX.Report.LongRunningProcessState.completed
					|| state === BX.Report.LongRunningProcessState.error)
				{
					this._lockButton("start", true);
					this._lockButton("stop", true);
					this._showButton("close", true);
				}

				BX.onCustomEvent(this, 'ON_STATE_CHANGE', [this]);
			},
			_startRequest: function()
			{
				if(this._requestIsRunning)
				{
					return;
				}
				this._requestIsRunning = true;

				this._setState(BX.Report.LongRunningProcessState.running);

				var actionData = {
					"ACTION" : this._action,
					"PARAMS": this._params
				};

				if (this._initialOptionsBlock)
				{
					var initialOptions = {};
					var numberOfOptions = 0;
					var option, optionName, optionId, optionElement, optionValue, optionValueIsSet;
					for (optionName in this._initialOptions)
					{
						if (this._initialOptions.hasOwnProperty(optionName))
						{
							option = this._initialOptions[optionName];
							if (BX.type.isPlainObject(option)
								&& option.hasOwnProperty("name")
								&& option.hasOwnProperty("type")
								&& option.hasOwnProperty("title")
								&& option.hasOwnProperty("value"))
							{
								optionValueIsSet = false;
								switch (option["type"])
								{
									case "checkbox":
										optionId = this._id + "_opt_" + optionName;
										optionElement = BX(optionId);
										if (optionElement)
										{
											optionValue = (optionElement.checked) ? "Y" : "N";
											optionValueIsSet = true;
										}
										break;
								}
								if (optionValueIsSet)
								{
									initialOptions[optionName] = optionValue;
									numberOfOptions++;
								}
							}
						}
					}
					if (numberOfOptions > 0)
					{
						actionData["INITIAL_OPTIONS"] = initialOptions;
					}
				}

				BX.ajax(
					{
						url: this._serviceUrl,
						method: "POST",
						dataType: "json",
						data: actionData,
						onsuccess: BX.delegate(this._onRequestSuccsess, this),
						onfailure: BX.delegate(this._onRequestFailure, this)
					}
				);
			},
			_onRequestSuccsess: function(result)
			{
				this._requestIsRunning = false;

				if(!result)
				{
					this._setSummary(this.getMessage("requestError"));
					this._setState(BX.Report.LongRunningProcessState.error);
					return;
				}

				if(BX.type.isNotEmptyString(result["ERROR"]))
				{
					this._setState(BX.Report.LongRunningProcessState.error);
					this._setSummary(result["ERROR"]);
					return;
				}

				var status = BX.type.isNotEmptyString(result["STATUS"]) ? result["STATUS"] : "";
				var summary = BX.type.isNotEmptyString(result["SUMMARY"]) ? result["SUMMARY"] : "";
				var isHtmlSummary = false;
				if (!BX.type.isNotEmptyString(summary))
				{
					summary = BX.type.isNotEmptyString(result["SUMMARY_HTML"]) ? result["SUMMARY_HTML"] : "";
					isHtmlSummary = true;
				}
				if(status === "PROGRESS")
				{
					if(summary !== "")
					{
						this._setSummary(summary, isHtmlSummary);
					}

					if(this._cancelRequest)
					{
						this._setState(BX.Report.LongRunningProcessState.stoped);
						this._cancelRequest = false;
					}
					else
					{
						window.setTimeout(
							BX.delegate(this._startRequest, this),
							100
						);
					}
					return;
				}

				if(status === "NOT_REQUIRED" || status === "COMPLETED")
				{
					this._setState(BX.Report.LongRunningProcessState.completed);
					if(summary !== "")
					{
						this._setSummary(summary, isHtmlSummary);
					}
				}
				else
				{
					this._setSummary(this.getMessage("requestError"));
					this._setState(BX.Report.LongRunningProcessState.error);
				}

				if(this._cancelRequest)
				{
					this._cancelRequest = false;
				}
			},
			_onRequestFailure: function(result)
			{
				this._requestIsRunning = false;

				this._setSummary(this.getMessage("requestError"));
				this._setState(BX.Report.LongRunningProcessState.error);
			}
		};
	if(typeof(BX.Report.LongRunningProcessDialog.messages) === "undefined")
	{
		BX.Report.LongRunningProcessDialog.messages = {};
	}
	BX.Report.LongRunningProcessDialog.items = {};
	BX.Report.LongRunningProcessDialog.create = function(id, settings)
	{
		var self = new BX.Report.LongRunningProcessDialog();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}
