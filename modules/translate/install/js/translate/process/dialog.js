;(function ()
{
	'use strict';

	BX.namespace('BX.Translate');

	if (BX.Translate.ProcessDialog)
	{
		return;
	}

	/**
	 * UI process dialog
	 *
	 * @event BX.Translate.ProcessDialog.Shown
	 * @event BX.Translate.ProcessDialog.Closed
	 * @event BX.Translate.ProcessDialog.Start
	 * @event BX.Translate.ProcessDialog.Stop
	 * @event BX.Translate.ProcessDialog.Closed
	 *
	 */
	BX.Translate.ProcessDialog = function()
	{
		/** @var {String} */
		this.id = '';

		this.settings = {};

		/** @var {Element} */
		this.optionsFieldsBlock = null;

		/** @var {BX.PopupWindow} */
		this.popupWindow = null;
		/** @var {Boolean} */
		this.isShown = false;

		this.buttons = {};

		/** @var {Element} */
		this.summaryBlock = null;

		/** @var {BX.UI.Alert} */
		this.error = null;
		/** @var {Element} */
		this.errorBlock = null;

		/** @var {Element} */
		this.warningBlock = null;

		/** @var {BX.UI.ProgressBar} */
		this.progressBar = null;
		/** @var {Element} */
		this.progressBarBlock = null;

		this.messages = {};

		/** @var {function} */
		this.handlers = {};
	};

	/**
	 * @param {Object} settings
	 * @param {string} [settings.id]
	 * @param {function} [settings.start]
	 * @param {function} [settings.stop]
	 * @param {array} [settings.messages]
	 * @param {string} [settings.messages.title]
	 * @param {string} [settings.messages.summary]
	 * @param {string} [settings.messages.startButton]
	 * @param {string} [settings.messages.stopButton]
	 * @param {string} [settings.messages.closeButton]
	 * @param {array} [settings.optionsFields]
	 * @param {Object} [settings.optionsFieldsValue]
	 * @param {string} [settings.optionsFields.name]
	 * @param {string} [settings.optionsFields.type]
	 * @param {string} [settings.optionsFields.title]
	 * @param {string} [settings.optionsFields.value]
	 * @param {Object} [settings.showButtons]
	 * @param {boolean} [settings.showButtons.start]
	 * @param {boolean} [settings.showButtons.stop]
	 * @param {boolean} [settings.showButtons.close]
	 * @param {Object} [settings.handlers]
	 * @param {function} [settings.handlers.start]
	 * @param {function} [settings.handlers.stop]
	 * @param {function} [settings.handlers.dialogShown]
	 * @param {function} [settings.handlers.dialogClosed]
	 *
	 * @constructor
	 */
	BX.Translate.ProcessDialog.prototype = {

		STYLES: {
			dialogProcessWindow: "bx-translate-dialog-process",
			dialogProcessPopup: "bx-translate-dialog-process-popup",
			dialogProcessSummary: "bx-translate-dialog-process-summary",
			dialogProcessProgressbar: "bx-translate-dialog-process-progressbar",
			dialogProcessOptions: "bx-translate-dialog-process-options",
			dialogProcessOptionsTitle: "bx-translate-dialog-process-options-title",
			dialogProcessOptionsInput: "bx-translate-dialog-process-options-input",
			dialogProcessOptionsObligatory: "ui-alert ui-alert-xs ui-alert-warning",
			dialogProcessOptionText: "bx-translate-dialog-process-option-text",
			dialogButtonAccept: "popup-window-button-accept",
			dialogButtonDisable: "popup-window-button-disable",
			dialogButtonCancel: "popup-window-button-link-cancel",
			dialogButtonDownload: "ui-btn ui-btn-sm ui-btn-success ui-btn-icon-download",
			dialogButtonRemove: "ui-btn ui-btn-sm ui-btn-default ui-btn-icon-remove"
		},

		init: function (settings)
		{
			this.settings = settings ? settings : {};

			this.id = BX.type.isNotEmptyString(this.settings.id) ?
				this.settings.id : "TranslateProcessDialog_" + Math.random().toString().substring(2);


			this.messages = this.getSetting("messages");
			if (!this.messages)
			{
				this.messages = {};
			}

			var optionsFields = this.getSetting("optionsFields");
			if (!optionsFields)
			{
				this.setSetting("optionsFields",{});
			}
			var optionsFieldsValue = this.getSetting("optionsFieldsValue");
			if (!optionsFieldsValue)
			{
				this.setSetting("optionsFieldsValue",{});
			}

			var showButtons = this.getSetting("showButtons");
			if (!showButtons)
			{
				this.setSetting("showButtons", {"start":true, "stop":true, "close":true});
			}

			if (typeof (BX.UI) != "undefined" && typeof (BX.UI.ProgressBar) != "undefined")
			{
				this.progressBar = new BX.UI.ProgressBar({
					statusType: BX.UI.ProgressBar.Status.COUNTER,
					size: BX.UI.ProgressBar.Size.LARGE,
					fill: true
				});
			}

			this.handlers = this.getSetting("handlers");
			if (!this.handlers)
			{
				this.handlers = {};
			}

			if (BX.type.isArray(settings.styles))
			{
				this.STYLES = BX.mergeEx(this.STYLES, settings.styles);
			}
		},

		destroy: function ()
		{
			if (this.popupWindow)
			{
				this.popupWindow.destroy();
				this.popupWindow = null;
			}
		},

		getId: function () {
			return this.id;
		},

		getSetting: function (name, defaultVal) {
			return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultVal;
		},

		/**
		 * @param name
		 * @param val
		 * @returns {BX.Translate.ProcessDialog}
		 */
		setSetting: function (name, val) {
			this.settings[name] = val;
			return this;
		},

		getMessage: function (name)
		{
			return this.messages && this.messages.hasOwnProperty(name) ? this.messages[name] : "";
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
		 * @return {BX.Translate.ProcessDialog}
		 */
		setHandler: function (type, handler)
		{
			if (typeof(handler) == 'function')
			{
				this.handlers[type] = handler;
			}
			return this;
		},

		/**
		 * @return {BX.Translate.ProcessDialog}
		 */
		show: function ()
		{
			if (this.isShown)
			{
				return;
			}

			this.error = new BX.UI.Alert({
				color: BX.UI.Alert.Color.DANGER,
				icon: BX.UI.Alert.Icon.DANGER,
				size: BX.UI.Alert.Size.SMALL
			});

			this.warning = new BX.UI.Alert({
				color: BX.UI.Alert.Color.WARNING,
				icon: BX.UI.Alert.Icon.WARNING,
				size: BX.UI.Alert.Size.SMALL
			});

			this.popupWindow = BX.PopupWindowManager.create(
				this.id.toLowerCase(),
				null,
				{
					className: this.STYLES.dialogProcessWindow,
					autoHide: false,
					bindOptions: {forceBindPosition: false},
					buttons: this.prepareDialogButtons(),
					closeByEsc: false,
					closeIcon: false,
					content: this.prepareDialogContent(),
					draggable: true,
					events: {
						onPopupClose: BX.delegate(this.onDialogClose, this)
					},
					offsetLeft: 0,
					offsetTop: 0,
					titleBar: this.getMessage("title"),
					overlay: true,
					resizable: true
				}
			);
			if (!this.popupWindow.isShown())
			{
				this.popupWindow.show();
			}

			this.isShown = this.popupWindow.isShown();

			if (this.isShown)
			{
				this.callHandler('dialogShown');
				BX.onCustomEvent(this, 'BX.Translate.ProcessDialog.Shown', [this]);
			}
			return this;
		},

		close: function ()
		{
			if (!this.isShown)
			{
				return;
			}
			if (this.popupWindow)
			{
				this.popupWindow.close();
			}
			this.isShown = false;

			this.callHandler('dialogClosed');
			BX.onCustomEvent(this, 'BX.Translate.ProcessDialog.Closed', [this]);
		},

		start: function ()
		{
			this.callHandler('start');
			BX.onCustomEvent(this, 'BX.Translate.ProcessDialog.Start', [this]);
		},

		stop: function ()
		{
			this.callHandler('stop');
			BX.onCustomEvent(this, 'BX.Translate.ProcessDialog.Stop', [this]);
		},

		prepareDialogContent: function ()
		{
			var summary = this.getMessage("summary");
			this.summaryBlock = BX.create(
				"DIV",
				{
					attrs: {className: this.STYLES.dialogProcessSummary},
					html: summary
				}
			);

			this.errorBlock = this.error.getContainer();
			this.warningBlock = this.warning.getContainer();
			this.errorBlock.style.display = "none";
			this.warningBlock.style.display = "none";

			if (this.progressBar)
			{
				this.progressBarBlock = BX.create(
					"DIV",
					{
						attrs: {className: this.STYLES.dialogProcessProgressbar},
						style: {display: "none"},
						children: [this.progressBar.getContainer()]
					}
				);
			}

			if (this.optionsFieldsBlock === null)
			{
				this.optionsFieldsBlock = BX.create(
					"DIV", {attrs: {className: this.STYLES.dialogProcessOptions}}
				);
			}
			else
			{
				BX.clean(this.optionsFieldsBlock);
			}

			var option, optionsFields, optionsFieldsValue, optionName, optionValue, optionBlock, optionId,
				alertId, numberOfOptions = 0, selAttrs, itemId, itemsList, children, selected;

			optionsFields = this.getSetting('optionsFields', {});
			optionsFieldsValue = this.getSetting('optionsFieldsValue', {});

			for (optionName in optionsFields)
			{
				if (optionsFields.hasOwnProperty(optionName))
				{
					option = optionsFields[optionName];
					optionValue = optionsFieldsValue[optionName] ? optionsFieldsValue[optionName] : null;

					if (BX.type.isPlainObject(option)
						&& option.hasOwnProperty("name")
						&& option.hasOwnProperty("type")
						&& option.hasOwnProperty("title"))
					{
						selAttrs = optionBlock = null;
						optionId = this.id + "_opt_" + optionName;
						alertId = this.id + "_alert_" + optionName;

						switch (option["type"])
						{
							case "text":
								selAttrs = {
									id: optionId,
									name: optionName,
									rows: (option["size"] ? option["size"] : 10),
									cols: 50,
									value: (option["value"] ? option["value"] : '')
								};

								children = [];
								children.push(BX.create(
									"DIV",
									{
										attrs: {className: this.STYLES.dialogProcessOptionsTitle},
										children: [
											BX.create(
												"LABEL",
												{
													attrs: {for: optionId},
													text: option["title"]
												}
											)
										]
									}
								));
								var ta = BX.create("TEXTAREA", {attrs: selAttrs});
								if (option["value"])
								{
									ta.value = option["value"];
								}

								children.push(BX.create(
									"DIV",
									{
										attrs: {className: this.STYLES.dialogProcessOptionsInput},
										children: [
											ta
										]
									}
								));
								if (option["obligatory"] === true)
								{
									children.push(BX.create(
										"DIV",
										{
											attrs: {
												id: alertId,
												className: this.STYLES.dialogProcessOptionsObligatory
											},
											style: {display: "none"},
											children: [
												BX.create("SPAN", {attrs: {className: "ui-alert-message"}, text: option["emptyMessage"]})
											]
										}
									));
								}

								optionBlock = BX.create("DIV", {children: children, attrs: {className: this.STYLES.dialogProcessOptionText}});
								break;

							case "file":
								selAttrs = {
									id: optionId,
									type: option["type"],
									name: optionName
								};

								children = [];
								children.push(BX.create(
									"DIV",
									{
										attrs: {className: this.STYLES.dialogProcessOptionsTitle},
										children: [
											BX.create(
												"LABEL",
												{
													attrs: {for: optionId},
													text: option["title"]
												}
											)
										]
									}
								));
								children.push(BX.create(
									"DIV",
									{
										attrs: {className: this.STYLES.dialogProcessOptionsInput},
										children: [
											BX.create("INPUT", {attrs: selAttrs})
										]
									}
								));
								if (option["obligatory"] === true)
								{
									children.push(BX.create(
										"DIV",
										{
											attrs: {
												id: alertId,
												className: this.STYLES.dialogProcessOptionsObligatory
											},
											style: {display: "none"},
											children: [
												BX.create("SPAN", {attrs: {className: "ui-alert-message"}, text: option["emptyMessage"]})
											]
										}
									));
								}

								optionBlock = BX.create("DIV", {children: children, attrs: {className: this.STYLES.dialogProcessOptionFile}});
								break;

							case "checkbox":
								selAttrs = {
									id: optionId,
									type: option["type"],
									name: optionName,
									value: "Y"
								};
								if (option["value"] && (option["value"] !== "Y" && option["value"] !== "N"))
								{
									selAttrs["value"] = option["value"];
								}

								if (
									optionValue === 'Y' ||
									(optionValue === null && option["value"] === 'Y') ||
									(optionValue !== null && option["value"] !== 'N' && option["value"] === optionValue)
								)
								{
									selAttrs["checked"] = "checked";
								}

								optionBlock = BX.create(
									"DIV",
									{
										children: [
											BX.create(
												"DIV",
												{
													attrs: {className: this.STYLES.dialogProcessOptionsTitle},
													children: [
														BX.create(
															"LABEL",
															{
																attrs: {for: optionId},
																text: option["title"]
															}
														)
													]
												}
											),
											BX.create(
												"DIV",
												{
													attrs: {className: this.STYLES.dialogProcessOptionsInput},
													children: [
														BX.create("INPUT", {attrs: selAttrs})
													]
												}
											)
										],
										attrs: {className: this.STYLES.dialogProcessOptionCheckbox}
									}
								);
								break;

							case "select":
								selAttrs = {
									id: optionId,
									name: optionName
								};
								if (option["multiple"] === 'Y')
								{
									selAttrs["multiple"] = "Y";
									if (option["size"])
									{
										selAttrs["size"] = option["size"];
									}
								}

								itemsList = [];
								for (itemId in option.list)
								{
									if (option.list.hasOwnProperty(itemId))
									{
										if (option["multiple"] === 'Y')
										{
											selected =
												(BX.type.isArray(optionValue) && (optionValue.indexOf(itemId) !== -1)) ||
												(optionValue === null && BX.type.isArray(option["value"]) && (option["value"].indexOf(itemId) !== -1));
										}
										else
										{
											selected =
												(itemId === optionValue) ||
												(optionValue === null && itemId === option["value"]);
										}
										itemsList.push(BX.create(
											"OPTION",
											{
												attrs: {
													value: itemId,
													selected: selected
												},
												text: option.list[itemId]
											}
										));
									}
								}

								children = [];
								children.push(BX.create(
									"DIV",
									{
										text: option["title"],
										attrs: {className: this.STYLES.dialogProcessOptionsTitle}
									}
								));
								children.push(BX.create(
									"DIV",
									{
										attrs: {className: this.STYLES.dialogProcessOptionsInput},
										children: [
											BX.create(
												"SELECT",
												{
													attrs: selAttrs,
													children: itemsList
												}
											)
										]
									}
								));
								if (option["obligatory"] === true)
								{
									children.push(BX.create(
										"DIV",
										{
											attrs: {
												id: alertId,
												className: this.STYLES.dialogProcessOptionsObligatory
											},
											style: {display: "none"},
											children: [
												BX.create("SPAN", {attrs: {className: "ui-alert-message"}, text: option["emptyMessage"]})
											]
										}
									));
								}

								optionBlock = BX.create("DIV", {children: children, attrs: {className: this.STYLES.dialogProcessOptionSelect}});
								break;

							case "radio":
								selAttrs = {
									name: optionName,
									type: option["type"]
								};

								itemsList = [];
								for (itemId in option.list)
								{
									if (option.list.hasOwnProperty(itemId))
									{
										selAttrs.value = itemId;
										selAttrs.checked =
											(itemId === optionValue) ||
											(optionValue === null && itemId === option["value"]);

										itemsList.push(BX.create(
											"LABEL",
											{
												children: [
													BX.create(
														"INPUT",
														{
															attrs: selAttrs
														}
													),
													option.list[itemId]
												]
											}
										));
									}
								}

								children = [];
								children.push(BX.create(
									"DIV",
									{
										text: option["title"],
										attrs: {className: this.STYLES.dialogProcessOptionsTitle},
									}
								));
								children.push(BX.create(
									"DIV",
									{
										attrs: {className: this.STYLES.dialogProcessOptionsInput, id: optionId},
										children: itemsList
									}
								));
								if (option["obligatory"] === true)
								{
									children.push(BX.create(
										"DIV",
										{
											attrs: {
												id: alertId,
												className: this.STYLES.dialogProcessOptionsObligatory
											},
											style: {display: "none"},
											children: [
												BX.create("SPAN", {attrs: {className: "ui-alert-message"}, text: option["emptyMessage"]})
											]
										}
									));
								}

								optionBlock = BX.create("DIV", {children: children, attrs: {className: this.STYLES.dialogProcessOptionRadio}});
								break;
						}
						if (optionBlock !== null)
						{
							this.optionsFieldsBlock.appendChild(optionBlock);
							numberOfOptions++;
						}
					}
				}
			}

			var summaryElements = [this.summaryBlock, this.warningBlock, this.errorBlock];

			if (this.progressBarBlock)
				summaryElements.push(this.progressBarBlock);

			if (this.optionsFieldsBlock)
				summaryElements.push(this.optionsFieldsBlock);

			return BX.create(
				"DIV",
				{
					attrs: {className: this.STYLES.dialogProcessPopup},
					children: summaryElements
				}
			);
		},

		/**
		 * @return {BX.PopupWindowButton[]}
		 */
		prepareDialogButtons: function ()
		{
			var ret = [], showButtons = this.getSetting("showButtons");
			this.buttons = {};

			if (showButtons.start)
			{
				var startButtonText = this.getMessage("startButton");
				this.buttons["start"] = new BX.PopupWindowButton({
					text: startButtonText !== "" ? startButtonText : "Start",
					className: this.STYLES.dialogButtonAccept,
					events:
						{
							click: BX.delegate(this.handleStartButtonClick, this)
						}
				});
				ret.push(this.buttons["start"]);
			}

			if (showButtons.stop)
			{
				var stopButtonText = this.getMessage("stopButton");
				this.buttons["stop"] = new BX.PopupWindowButton({
					text: stopButtonText !== "" ? stopButtonText : "Stop",
					className: this.STYLES.dialogButtonDisable,
					events:
						{
							click: BX.delegate(this.handleStopButtonClick, this)
						}
				});
				ret.push(this.buttons["stop"]);
			}

			if (showButtons.close)
			{
				var closeButtonText = this.getMessage("closeButton");
				this.buttons["close"] = new BX.PopupWindowButtonLink({
					text: closeButtonText !== "" ? closeButtonText : "Close",
					className: this.STYLES.dialogButtonCancel,
					events:
						{
							click: BX.delegate(this.handleCloseButtonClick, this)
						}
				});
				ret.push(this.buttons["close"]);
			}

			return ret;
		},

		/**
		 * @param {String} bid
		 * @return {BX.PopupWindowButton}|{null}
		 */
		getButton: function(bid)
		{
			return typeof (this.buttons[bid]) !== "undefined" ? this.buttons[bid] : null;
		},

		onDialogClose: function (e)
		{
			if (this.popupWindow)
			{
				this.popupWindow.destroy();
				this.popupWindow = null;
			}

			this.buttons = {};
			this.summaryBlock = null;

			this.isShown = false;

			this.callHandler('dialogClosed');
			BX.onCustomEvent(this, 'BX.Translate.ProcessDialog.Closed', [this]);
		},

		handleStartButtonClick: function ()
		{
			var btn = this.getButton("start");
			if (btn)
			{
				var wasDisabled = BX.data(btn.buttonNode, 'disabled');
				if (wasDisabled === true)
				{
					return;
				}
			}

			this.start();
		},

		handleStopButtonClick: function ()
		{
			var btn = this.getButton("stop");
			if (btn)
			{
				var wasDisabled = BX.data(btn.buttonNode, 'disabled');
				if (wasDisabled === true)
				{
					return;
				}
			}

			this.stop();
		},

		handleCloseButtonClick: function ()
		{
			this.popupWindow.close();
		},

		/**
		 * @param {String} bid
		 * @param {Boolean} lock
		 * @return self
		 */
		lockButton: function (bid, lock)
		{
			var btn = this.getButton(bid);
			if (!btn)
			{
				return;
			}

			if (!!lock)
			{
				BX.removeClass(btn.buttonNode, this.STYLES.dialogButtonAccept);
				BX.addClass(btn.buttonNode, this.STYLES.dialogButtonDisable);
				btn.buttonNode.disabled = true;
				BX.data(btn.buttonNode, 'disabled', true);
			}
			else
			{
				BX.removeClass(btn.buttonNode, this.STYLES.dialogButtonDisable);
				BX.addClass(btn.buttonNode, this.STYLES.dialogButtonAccept);
				btn.buttonNode.disabled = false;
				BX.data(btn.buttonNode, 'disabled', false);
			}

			return this;
		},

		/**
		 * @param {String} bid
		 * @param {Boolean} show
		 * @return self
		 */
		showButton: function (bid, show)
		{
			var btn = this.getButton(bid);
			if (btn)
			{
				btn.buttonNode.style.display = !!show ? "" : "none";
			}
			return this;
		},

		/**
		 * @param {string} content
		 * @param {bool} isHtml
		 * @return self
		 */
		setSummary: function (content, isHtml)
		{
			if (this.optionsFieldsBlock)
			{
				BX.clean(this.optionsFieldsBlock);
			}
			if (BX.type.isNotEmptyString(content))
			{
				if (this.summaryBlock)
				{
					if (!!isHtml)
						this.summaryBlock.innerHTML = content;
					else
						this.summaryBlock.innerHTML = BX.util.htmlspecialchars(content);

					this.summaryBlock.style.display = "block";
				}
			}
			else
			{
				this.summaryBlock.innerHTML = "";
				this.summaryBlock.style.display = "none";
			}
			return this;
		},

		/**
		 * @param {string} content
		 * @param {bool} isHtml
		 * @return self
		 */
		setError: function (content, isHtml)
		{
			if (BX.type.isNotEmptyString(content))
			{
				if (this.progressBar)
				{
					this.progressBar.setColor(BX.UI.ProgressBar.Color.DANGER);
				}

				if (!!isHtml)
					this.error.setText(content);
				else
					this.error.setText(BX.util.htmlspecialchars(content));

				this.errorBlock.style.display = "flex";
			}
			else
			{
				if (this.errorBlock)
				{
					this.error.setText("");
					this.errorBlock.style.display = "none";
				}
			}
			return this;
		},

		/**
		 * @param {string} content
		 * @param {bool} isHtml
		 * @return self
		 */
		setWarning: function (content, isHtml)
		{
			if (BX.type.isNotEmptyString(content))
			{
				if (!!isHtml)
					this.warning.setText(content);
				else
					this.warning.setText(BX.util.htmlspecialchars(content));

				this.warningBlock.style.display = "flex";
			}
			else
			{
				if (this.warningBlock)
				{
					this.warning.setText("");
					this.warningBlock.style.display = "none";
				}
			}
			return this;
		},

		/**
		 * @param {String} downloadLink
		 * @param {String} fileName
		 * @param {function} purgeHandler
		 * @return self
		 */
		setDownloadButtons: function (downloadLink, fileName, purgeHandler)
		{
			BX.clean(this.optionsFieldsBlock);

			if (downloadLink)
			{
				var downloadButtonText = this.getMessage("downloadButton");
				var downloadButton = BX.create(
					"A",
					{
						text: (downloadButtonText !== "" ? downloadButtonText : "Download"),
						props: {
							href: downloadLink,
							download: fileName
						},
						attrs: {className: this.STYLES.dialogButtonDownload}
					}
				);
				this.optionsFieldsBlock.appendChild(downloadButton);
			}

			if (typeof(purgeHandler) == 'function')
			{
				var clearButtonText = this.getMessage("clearButton");
				var clearButton = BX.create(
					"BUTTON",
					{
						text: (clearButtonText !== "" ? clearButtonText : "Drop"),
						attrs: {className: this.STYLES.dialogButtonRemove},
						events: {click: purgeHandler}
					}
				);
				this.optionsFieldsBlock.appendChild(clearButton);
			}
			return this;
		},

		/**
		 * @param {Number} totalItems
		 * @param {Number} processedItems
		 * @param {string} textBefore
		 * @return self
		 */
		setProgressBar: function (totalItems, processedItems, textBefore)
		{
			if (this.progressBar)
			{
				if (BX.type.isNumber(processedItems) && BX.type.isNumber(totalItems) && totalItems > 0)
				{
					BX.show(this.progressBarBlock);
					this.progressBar.setColor(BX.UI.ProgressBar.Color.PRIMARY);
					this.progressBar.setMaxValue(totalItems);
					textBefore = textBefore || "";
					this.progressBar.setTextBefore(textBefore);
					this.progressBar.update(processedItems);
				}
				else
				{
					BX.hide(this.progressBarBlock);
				}
			}
			return this;
		},
		/**
		 * @return self
		 */
		hideProgressBar: function ()
		{
			if (this.progressBar)
			{
				BX.hide(this.progressBarBlock);
			}
			return this;
		},

		/**
		 * @return {Object}
		 */
		getOptions: function ()
		{
			var initialOptions = {};
			if (this.optionsFieldsBlock)
			{
				var option, optionsFields, optionName, optionId, optionElement, optionElements, optionValue, optionDiv, optionValueIsSet, k;
				optionsFields = this.getSetting('optionsFields', {});
				for (optionName in optionsFields)
				{
					if (optionsFields.hasOwnProperty(optionName))
					{
						option = optionsFields[optionName];
						if (BX.type.isPlainObject(option)
							&& option.hasOwnProperty("name")
							&& option.hasOwnProperty("type")
							&& option.hasOwnProperty("title"))
						{
							optionValueIsSet = false;
							optionId = this.id + "_opt_" + optionName;

							switch (option["type"])
							{
								case "text":
									/** @var {Element} optionElement */
									optionElement = BX(optionId);
									if (optionElement)
									{
										if(typeof(optionElement.value) != "undefined")
										{
											initialOptions[optionName] = optionElement.value;
										}
									}
									break;

								case "file":
									/** @var {Element} optionElement */
									optionElement = BX(optionId);
									if (optionElement)
									{
										if(typeof(optionElement.files[0]) != "undefined")
										{
											initialOptions[optionName] = optionElement.files[0];
										}
									}
									break;

								case "radio":
									/** @var {Element} optionElement */
									optionDiv = BX(optionId);
									if (optionDiv)
									{
										optionElements = optionDiv.querySelectorAll("input[type=radio]");
										if (optionElements)
										{
											for (k = 0; k < optionElements.length; k++)
											{
												if (optionElements[k].checked)
												{
													optionValue = optionElements[k].value;
													optionValueIsSet = true;
													break;
												}
											}
										}
									}
									break;

								case "checkbox":
									optionElement = BX(optionId);
									if (optionElement)
									{
										if (optionElement.value)
										{
											optionValue = (optionElement.checked) ? optionElement.value : "";
										}
										else
										{
											optionValue = (optionElement.checked) ? "Y" : "N";
										}
										optionValueIsSet = true;
									}
									break;

								case "select":
									optionElement = BX(optionId);
									if (optionElement)
									{
										if (option.multiple === 'Y')
										{
											optionValue = [];
											for (k = 0; k < optionElement.options.length; k++)
											{
												if (optionElement.options[k].selected)
												{
													optionValue.push(optionElement.options[k].value);
													optionValueIsSet = true;
												}
											}
										}
										else
										{
											optionValue = optionElement.value;
											optionValueIsSet = true;
										}
									}
									break;
							}
							if (optionValueIsSet)
							{
								initialOptions[optionName] = optionValue;
							}
						}
						option = null;
						optionDiv = null;
						optionValue = null;
						optionElement = null;
						optionElements = null;
					}
				}
			}

			return initialOptions;
		},

		/**
		 * @param {String} optionName
		 * @return {Element}
		 */
		getOption: function (optionName)
		{
			/** @var {Element} optionElement */
			var optionElement;
			if (this.optionsFieldsBlock)
			{
				var option, optionId, optionDiv, optionsFields;
				optionsFields = this.getSetting('optionsFields', {});
				option = optionsFields[optionName];
				if (BX.type.isPlainObject(option)
					&& option.hasOwnProperty("name")
					&& option.hasOwnProperty("type")
					&& option.hasOwnProperty("title"))
				{
					optionId = this.id + "_opt_" + optionName;

					switch (option["type"])
					{
						case "file":
						case "checkbox":
						case "select":
							optionElement = BX(optionId);
							break;

						case "radio":
							/** @var {Element} optionElement */
							optionDiv = BX(optionId);
							if (optionDiv)
							{
								optionElement = optionDiv.querySelectorAll("input[type=radio]");
							}
							break;
					}
				}
			}

			return optionElement;
		},


		/**
		 * @return {boolean}
		 */
		checkOptions: function ()
		{
			var checked = true;
			if (this.optionsFieldsBlock)
			{
				var option, optionsFields, optionName, optionId, alertId, optionElement, optionElements, optionDiv, optionValueIsSet, k;
				optionsFields = this.getSetting('optionsFields', {});
				for (optionName in optionsFields)
				{
					if (optionsFields.hasOwnProperty(optionName))
					{
						option = optionsFields[optionName];
						if (BX.type.isPlainObject(option)
							&& option.hasOwnProperty("name")
							&& option.hasOwnProperty("type")
							&& option.hasOwnProperty("title"))
						{
							optionValueIsSet = false;
							optionId = this.id + "_opt_" + optionName;
							alertId = this.id + "_alert_" + optionName;

							switch (option["type"])
							{
								case "file":
									/** @var {Element} optionElement */
									optionElement = BX(optionId);
									if (optionElement && option["obligatory"] === true)
									{
										if(typeof(optionElement.files[0]) == "undefined")
										{
											checked = false;
											BX.show(BX(alertId));
										}
										else
										{
											BX.hide(BX(alertId));
										}
									}
									break;

								case "radio":
									/** @var {Element} optionElement */
									optionDiv = BX(optionId);
									if (optionDiv && option["obligatory"] === true)
									{
										optionElements = optionDiv.querySelectorAll("input[type=radio]");
										if (optionElements)
										{
											for (k = 0; k < optionElements.length; k++)
											{
												if (optionElements[k].checked)
												{
													optionValueIsSet = true;
													break;
												}
											}
										}
										if(!optionValueIsSet)
										{
											checked = false;
										}
									}
									break;


								case "select":
									optionElement = BX(optionId);
									if (optionElement && option["obligatory"] === true)
									{
										if (option.multiple === 'Y')
										{
											for (k = 0; k < optionElement.options.length; k++)
											{
												if (optionElement.options[k].selected)
												{
													optionValueIsSet = true;
												}
											}
										}
										else
										{
											optionValueIsSet = true;
										}
										if(!optionValueIsSet)
										{
											checked = false;
										}
									}
									break;
							}
						}
						option = null;
						optionDiv = null;
						optionElement = null;
						optionElements = null;
					}
				}
			}

			return checked;
		}
	};



	/**
	 * Process UI dialog manager.
	 */
	if(typeof(BX.Translate.ProcessDialogManager) == "undefined")
	{
		BX.Translate.ProcessDialogManager = {};
	}
	if(typeof(BX.Translate.ProcessDialogManager.items) == "undefined")
	{
		BX.Translate.ProcessDialogManager.items = {};
	}

	/** @return {BX.Translate.ProcessDialog} */
	BX.Translate.ProcessDialogManager.create = function(settings)
	{
		var self = new BX.Translate.ProcessDialog();
		self.init(settings);
		BX.Translate.ProcessDialogManager.items[self.getId()] = self;
		return self;
	};

	BX.Translate.ProcessDialogManager.delete = function(id)
	{
		if (BX.Translate.ProcessDialogManager.items.hasOwnProperty(id))
		{
			BX.Translate.ProcessDialogManager.items[id].destroy();
			delete BX.Translate.ProcessDialogManager.items[id];
		}
	};

	/** @return {BX.Translate.ProcessDialog} */
	BX.Translate.ProcessDialogManager.getInstance = function(id)
	{
		return BX.Translate.ProcessDialogManager.items[id] ? BX.Translate.ProcessDialogManager.items[id] : null;
	};

})();