;(function ()
{
	'use strict';

	BX.namespace('BX.Translate');
	if (BX.Translate.Editor)
	{
		return;
	}

	var Editor = function ()
	{
		this.id = '';
		this.tabId = '';
		this.filePath = '';
		this.linkBack = '';
		this.editLink = '';
		this.messages = {};
		this.viewMode = '';
		this.viewModeMenu = [];
		this.extraMenu = [];
		this.deleteMenu = [];
		this.mode = '';

		/** @var {String} */
		this.controller = '';
		/** @var {String} */
		this.action = '';
		/** @var {Object} */
		this.params = {};
		/** @var {XMLHttpRequest} */
		this.xhr = null;
	};

	Editor.prototype.VIEW_MODE = {
		ShowAll: "ShowAll",
		Untranslated: "Untranslated",
		SourceEdit: "SourceEdit",
		SourceView: "SourceView"
	};

	Editor.prototype.STATES = {
		intermediate: "INTERMEDIATE",
		running: "RUNNING",
		error: "ERROR"
	};

	Editor.prototype.STYLES = {
		editor: "translate-edit",
		fade: "translate-edit-fade",
		editRow: "translate-edit-row",
		editRowPhrase: "phrase",
		editRowHighlight: "highlight",
		editArea: "translate-edit-area",
		editable: "editable",
		eraser: "eraser",
		eraserLabel: "eraser-label",
		errorLess: "error-less",
		errorMore: "error-more",
		menuItemChecked: "menu-popup-item-accept",
		delete: "delete"
	};

	/**
	 * @param {Object} param
	 * @param {String} [param.id]
	 * @param {String} [param.tabId]
	 * @param {String} [param.filePath]
	 * @param {String} [param.editLink]
	 * @param {String} [param.linkBack]
	 * @param {String} [param.controller]
	 * @param {Object} [param.param]
	 * @param {String} [param.viewMode]
	 * @param {String} [param.mode]
	 * @param {Object} [param.messages]
	 * @param {Array} [param.viewModeMenu]
	 * @param {Array} [param.extraMenu]
	 * @param {Array} [param.deleteMenu]
	 * @param {String} [param.highlightPhrase]
	 * @constructor
	 */
	Editor.prototype.init = function (param)
	{
		param = param || {};

		if (!BX.type.isNotEmptyString(param.tabId))
			throw "BX.Translate.Editor: 'tabId' parameter missing.";

		if (!BX.type.isNotEmptyString(param.filePath))
			throw "BX.Translate.Editor: 'filePath' parameter missing.";

		if (!BX.type.isNotEmptyString(param.editLink))
			throw "BX.Translate.Editor: 'editLink' parameter missing.";

		if (!BX.type.isNotEmptyString(param.linkBack))
			throw "BX.Translate.Editor: 'linkBack' parameter missing.";

		if (BX.type.isNotEmptyString(param.mode))
		{
			this.mode = param.mode;
		}

		this.filePath = param.filePath;
		this.editLink = param.editLink;
		this.linkBack = param.linkBack;
		this.tabId = param.tabId;
		this.id = param.id || 'bx-translate-editor-' + this.tabId;

		if (!BX.type.isNotEmptyString(param.controller))
		{
			throw "BX.Translate.Editor: Could not find ajax controller endpoint.";
		}
		this.controller = param.controller;

		this.param = param.param || {};

		if (!BX.type.isNotEmptyString(param.viewMode))
		{
			this.viewMode = param.viewMode;
		}
		else
		{
			this.viewMode = this.VIEW_MODE.ShowAll;
		}

		this.viewModeMenu = param.viewModeMenu;
		this.extraMenu = param.extraMenu;
		this.deleteMenu = param.deleteMenu;


		if (BX.type.isPlainObject(param.messages))
		{
			this.messages = param.messages;
		}

		if (BX.type.isPlainObject(param.messages))
		{
			this.messages = param.messages;
		}


		var nodeViewMode = BX('bx-translate-mode-menu-view-anchor');
		if(nodeViewMode)
		{
			BX.bind(nodeViewMode, 'click', BX.proxy(this.showViewModeMenu, this));
		}

		var nodeExtraMenu = BX('bx-translate-extra-menu-anchor');
		if(nodeExtraMenu)
		{
			BX.bind(nodeExtraMenu, 'click', BX.proxy(this.showExtraMenu, this));
		}

		if (this.getForm())
		{
			var nodes = this.getForm().querySelectorAll('.' + this.STYLES.editable);
			if (nodes)
			{
				nodes.forEach(BX.proxy(function (node) {
					BX.bind(node, 'focus', BX.delegate(this.showEditArea, this));
				}, this));
			}

			var nodeDeleteMenu = BX('bx-translate-delete-menu-anchor');
			if (nodeDeleteMenu)
			{
				BX.bind(nodeDeleteMenu, 'click', BX.proxy(this.showDeleteMenu, this));
			}

			nodes = this.getForm().querySelectorAll('.' + this.STYLES.eraserLabel);
			if (nodes)
			{
				nodes.forEach(BX.proxy(function (node) {
					BX.bind(node, 'keydown', function (ev) {
						/** @var {Element} node */
						var node = ev.currentTarget;
						var key = (ev.keyCode ? ev.keyCode : (ev.which ? ev.which : null));
						if (!!node && key === 32)
						{//space
							ev.stopPropagation();
							ev.preventDefault();

							if (document.createEvent)
							{
								var evt = document.createEvent('MouseEvents');
								evt.initEvent('click', true, false);
								node.dispatchEvent(evt);
							}
							else if (document.createEventObject)
							{
								node.fireEvent('onclick');
							}
							else if (typeof node.onclick == 'function')
							{
								node.onclick();
							}
							return false;
						}
						return true;
					});
				}, this));
			}

			nodes = this.getForm().querySelectorAll('.' + this.STYLES.eraser);
			if (nodes)
			{
				nodes.forEach(BX.proxy(function (node) {
					BX.bind(node, 'change', BX.proxy(function (ev) {
						/** @var {Element} node */
						var node = ev.currentTarget,
							code = BX.data(node, "code"),
							checked = node.checked;

						var nodes = this.getForm().querySelectorAll("." + this.STYLES.editRow + "." + this.STYLES.editRowPhrase + "[rel='" + code + "']");
						if (nodes)
						{
							nodes.forEach(BX.proxy(function (row) {
								if (checked)
								{
									BX.addClass(row, this.STYLES.delete);
								}
								else
								{
									BX.removeClass(row, this.STYLES.delete);
								}

							}, this));
						}

						return true;
					}, this));
				}, this));
			}
		}

		BX.addCustomEvent('BX.Translate.Process.BeforeRequestStart', BX.delegate(function(process, params){
			/** @type {BX.Translate.Process} process */
			if (process instanceof BX.Translate.Process)
			{
				process.setParam('file', this.getCurrentPath());
				process.method = 'POST';
			}
			if (params instanceof FormData)
			{
				params.append('file', this.getCurrentPath());
				params.append('tabId', this.tabId);
				params.append('AJAX_CALL', 'Y');
				if(this.mode == 'admin')
				{
					params.append('admin_section', 'Y');
					params.append('lang', BX.message('LANGUAGE_ID'));
				}
			}
			else
			{
				params['file'] = this.getCurrentPath();
				params['tabId'] = this.tabId;
				params['AJAX_CALL'] = 'Y';
				if(this.mode == 'admin')
				{
					params['admin_section'] = 'Y';
					params['lang'] = BX.message('LANGUAGE_ID');
				}
			}
		}, this));

		var highlightPhrase = '';
		if (BX.type.isNotEmptyString(param.highlightPhrase))
		{
			highlightPhrase = param.highlightPhrase;
		}
		else if (BX.type.isNotEmptyString(window.location.hash))
		{
			highlightPhrase = window.location.hash.replace(/#/g, '');
		}

		if (highlightPhrase != '')
		{
			var phraseCode = highlightPhrase.replace(/[^a-z1-9_]+/ig, ''),
				anchor = this.getForm().querySelector("[name='" + highlightPhrase + "']");
			if (anchor)
			{
				anchor.scrollIntoView({ behavior: 'smooth' , block : 'start'});
			}
			nodes = this.getForm().querySelectorAll("." + this.STYLES.editRow + "." + this.STYLES.editRowPhrase + "[rel='" + phraseCode + "']");
			if (nodes)
			{
				nodes.forEach(BX.proxy(function (row) { BX.addClass(row, this.STYLES.editRowHighlight); }, this));
			}
		}

	};



	/**
	 * @param {String} mode
	 */
	Editor.prototype.toggleDelete = function (mode)
	{
		var classType, hasBeenChecked, stateToSet,
			//message, nodeDeleteMenu = BX('bx-translate-delete-menu-anchor'),
			inx, item, items = deleteMenuPopup.getMenuItems();

		hasBeenChecked = false;
		for (inx in items)
		{
			if(!items.hasOwnProperty(inx)) continue;
			item = items[inx];

			if (item.id == 'translate-delete-' + mode)
			{
				hasBeenChecked = BX.hasClass(item.layout.item, this.STYLES.menuItemChecked);
				if (hasBeenChecked)
				{
					BX.removeClass(item.layout.item, this.STYLES.menuItemChecked);
				}
				else
				{
					BX.addClass(item.layout.item, this.STYLES.menuItemChecked);
				}
			}
			else
			{
				BX.removeClass(item.layout.item, this.STYLES.menuItemChecked);
			}
		}

		classType = '';
		if (hasBeenChecked)
		{
			//message = this.getMessage('Delete');
			stateToSet = false;
		}
		else if (mode == 'all')
		{
			//message = this.getMessage('DeleteAll');
			stateToSet = true;
		}
		else if (mode == 'ethalon')
		{
			//message = this.getMessage('DeleteEthalon');
			classType = this.STYLES.errorMore;
			stateToSet = true;
		}

		/*if(nodeDeleteMenu)
		{
			nodeDeleteMenu.firstChild.innerHTML = message;
		}*/

		var nodes = this.getForm().querySelectorAll('input.' + this.STYLES.eraser);
		if(nodes)
		{
			nodes.forEach(BX.proxy(function(node)
			{
				if (classType != '')
				{
					if (BX.hasClass(node, classType))
					{
						node.checked = stateToSet;
					}
					else
					{
						node.checked = false;
					}
				}
				else
				{
					node.checked = stateToSet;
				}

				if (document.createEvent) {
					var evt = document.createEvent('HTMLEvents');
					evt.initEvent('change', true, false);
					node.dispatchEvent(evt);
				} else if (document.createEventObject) {
					node.fireEvent('onchange') ;
				} else if (typeof node.onchange == 'function') {
					node.onchange();
				}

			}, this));
		}

		if (deleteMenuPopup)
		{
			deleteMenuPopup.close();
		}
	};



	/**
	 * @param {Event} event
	 */
	Editor.prototype.showEditArea = function (event)
	{
		/** @var {Element} node */
		var node = event.currentTarget;
		if(node)
		{
			var
				fldName = BX.data(node, 'fld'),
				langId = BX.data(node, 'lng'),
				length = parseInt(BX.data(node, 'length')),
				lineCnt = parseInt(BX.data(node, 'lines')),
				phraseId = BX.data(node, 'code');

			lineCnt = (lineCnt ? lineCnt : 1);
			lineCnt ++;
			if (length > 100 && Math.round(length / 100) > lineCnt)
			{
				lineCnt = Math.round(length / 100) + 1;
			}
			if (lineCnt > 10)
			{
				lineCnt = 10;
			}

			var textArea = BX.create(
				"TEXTAREA",
				{
					attrs: {
						className: this.STYLES.editArea,
						cols: 60,
						rows: lineCnt,
						name: fldName,
						tabIndex: node.tabIndex,
						value: node.innerText
					},
					text: "",
					dataset: {
						fld: fldName,
						lng: langId,
						code: phraseId
					}
				}
			);
			var holder = BX.create(
				"DIV",
				{
					attrs: {
						className: "value edit"
					},
					children: [
						textArea
					]
				}
			);


			BX.insertAfter(holder, node);
			BX.hide(node);

			textArea.value = node.innerText;
			textArea.focus();

			node.removeAttribute('tabIndex');
		}
	};

	/**
	 */
	Editor.prototype.cancel = function ()
	{
		if (this.getForm())
		{
			var nodes = this.getForm().querySelectorAll('.' + this.STYLES.editArea);
			if (BX.type.isArray(nodes))
			{
				nodes.forEach(function (node) {
					BX.remove(node.parentNode);
				});
			}

			nodes = this.getForm().querySelectorAll('.' + this.STYLES.editable);
			if (BX.type.isArray(nodes))
			{
				nodes.forEach(function (node) {
					BX.show(node);
				});
			}
		}

		this.fade();

		window.location = this.linkBack;
	};

	/**
	 */
	Editor.prototype.save = function (redirectToList)
	{
		if(this.getState() === this.STATES.running)
		{
			return;
		}

		this.setState(this.STATES.running);
		this.setAction('save');

		var actionData = BX.clone(this.params);

		actionData['file'] = this.getCurrentPath();
		actionData['tabId'] = this.tabId;
		actionData['AJAX_CALL'] = 'Y';
		if(this.mode == 'admin')
		{
			actionData['admin_section'] = 'Y';
			actionData['lang'] = BX.message('LANGUAGE_ID');
		}

		actionData['KEYS'] = [];
		actionData['DROP'] = [];
		actionData['LANGS'] = [];

		var form = this.getForm();
		for(var i = 0, n = form.elements.length, phraseId, landId; i < n; i++)
		{
			/** @var {Element} el */
			var el = form.elements[i];
			if (el.disabled) continue;

			switch(el.type.toLowerCase())
			{
				case 'textarea':
					if (el.className.indexOf(this.STYLES.editArea) < 0)
					{
						break;
					}

					phraseId = BX.data(el, 'code');
					if(actionData['KEYS'].indexOf(phraseId) === -1)
					{
						actionData['KEYS'].push(phraseId);
					}

					landId = BX.data(el, 'lng');
					if(actionData['LANGS'].indexOf(landId) === -1)
					{
						actionData['LANGS'].push(landId);
					}

					actionData[el.name] = el.value;

					break;


				case 'checkbox':
					if (el.checked)
					{
						phraseId = BX.data(el, 'code');
						if(actionData['DROP'].indexOf(phraseId) === -1)
						{
							actionData['DROP'].push(phraseId);
						}
					}
					break;

				default:
			}
		}

		this.fade();

		BX.ajax.runAction
		(
			this.getController() + '.' + this.getAction(),
			{
				data: actionData,
				method: 'POST',
				onrequeststart: BX.delegate(this.onRequestStart, this)
			}
		)
		.then(
			BX.delegate(this.onRequestSuccess, this),
			BX.delegate(this.onRequestFailure, this)
		)
		.then(
			BX.delegate(function (result) {
				if (redirectToList === true)
				{
					window.location = this.linkBack;
				}
				else
				{
					window.location = this.editLink;
				}
			}, this)
		);
	};

	/**
	 */
	Editor.prototype.saveSource = function (redirectToList)
	{
		if(this.getState() === this.STATES.running)
		{
			return;
		}

		this.setState(this.STATES.running);
		this.setAction('savesource');

		var actionData = BX.clone(this.params);

		actionData['file'] = this.getCurrentPath();
		actionData['tabId'] = this.tabId;
		actionData['AJAX_CALL'] = 'Y';
		if(this.mode == 'admin')
		{
			actionData['admin_section'] = 'Y';
			actionData['lang'] = BX.message('LANGUAGE_ID');
		}

		actionData['LANGS'] = [];

		var form = this.getForm();
		for(var i = 0, n = form.elements.length, phraseId, landId; i < n; i++)
		{
			/** @var {Element} el */
			var el = form.elements[i];
			if (el.disabled) continue;

			switch(el.type.toLowerCase())
			{
				case 'textarea':
					if (el.className.indexOf(this.STYLES.editArea) < 0)
					{
						break;
					}

					landId = BX.data(el, 'lng');
					if(actionData['LANGS'].indexOf(landId) === -1)
					{
						actionData['LANGS'].push(landId);
					}

					actionData[el.name] = el.value;

					break;

				default:
			}
		}

		this.fade();

		BX.ajax.runAction
		(
			this.getController() + '.' + this.getAction(),
			{
				data: actionData,
				method: 'POST',
				onrequeststart: BX.delegate(this.onRequestStart, this)
			}
		)
		.then(
			BX.delegate(this.onRequestSuccess, this),
			BX.delegate(this.onRequestFailure, this)
		)
		.then(
			BX.delegate(function (result) {
				if (redirectToList === true)
				{
					window.location = this.linkBack;
				}
				else
				{
					window.location = this.editLink;
				}
			}, this)
		);
	};

	/**
	 * @param {XMLHttpRequest} xhr
	 */
	Editor.prototype.onRequestStart = function(xhr)
	{
		this.xhr = xhr;
	};


	/**
	 * @param {Object} result
	 * @private
	 */
	Editor.prototype.onRequestSuccess = function (result)
	{
		this.xhr = null;

		if (!result)
		{
			BX.UI.Notification.Center.notify({
				content: this.getMessage("RequestError")
			});
			this.setState(this.STATES.error);
			return;
		}

		if (BX.type.isArray(result["errors"]) && result["errors"].length > 0)
		{
			var lastError = result["errors"][result["errors"].length - 1];
			BX.UI.Notification.Center.notify({
				content: lastError.message
			});
			this.setState(this.STATES.error);
			return;
		}

		var data = result["data"];

		var summary = BX.type.isNotEmptyString(data["SUMMARY"]) ? data["SUMMARY"] : "";
		if (summary !== "")
		{
			BX.UI.Notification.Center.notify({
				content: summary
			});
		}
		else
		{
			BX.UI.Notification.Center.notify({
				content: this.getMessage("RequestCompleted")
			});
		}

		this.setState(this.STATES.intermediate);

		return result;
	};

	/**
	 * @param {Object} result
	 */
	Editor.prototype.onRequestFailure = function (result)
	{
		this.unFade();

		if (BX.type.isArray(result["errors"]) && result["errors"].length > 0)
		{
			var lastError = result["errors"][result["errors"].length - 1];
			BX.UI.Notification.Center.notify({
				content: lastError.message
			});
		}
		else
		{
			BX.UI.Notification.Center.notify({
				content: this.getMessage("RequestError")
			});
		}

		this.xhr = null;

		this.setState(this.STATES.error);

		// buttons
		var buttonNodes = BX("ui-button-panel").querySelectorAll(".ui-btn-wait");
		if(buttonNodes)
		{
			buttonNodes.forEach(function(node){
				BX.removeClass(node, "ui-btn-wait");
			});
		}
	};


	/**
	 * @param {String} state
	 */
	Editor.prototype.setState = function (state)
	{
		if (this.state === state)
		{
			return;
		}

		this.state = state;
	};

	Editor.prototype.getState = function () {
		return this.state;
	};
	Editor.prototype.getController = function () {
		return this.controller;
	};
	Editor.prototype.setController = function (controller) {
		this.controller = controller;
	};
	Editor.prototype.getAction = function () {
		return this.action;
	};
	Editor.prototype.setAction = function (action) {
		this.action = action;
	};
	Editor.prototype.getId = function () {
		return this.id;
	};
	Editor.prototype.setId = function (id) {
		this.id = id;
	};
	Editor.prototype.getParams = function () {
		return this.params;
	};
	Editor.prototype.getParam = function (key) {
		return this.params[key] ? this.params[key] : null;
	};
	Editor.prototype.setParam = function (key, value) {
		this.params[key] = value;
	};


	/**
	 * @return {Element}
	 */
	Editor.prototype.getForm = function ()
	{
		return BX(this.getId());
	};

	/**
	 * @return {Element}
	 */
	Editor.prototype.getContainer = function ()
	{
		return this.getForm().querySelector("." + this.STYLES.editor);
	};

	/**
	 * @return {String}
	 */
	Editor.prototype.getMessage = function (name)
	{
		return BX.type.isNotEmptyString(this.messages[name]) ? this.messages[name] : "";
	};

	/**
	 * @return {String}
	 */
	Editor.prototype.getCurrentPath = function ()
	{
		return this.filePath;
	};

	//endregion


	//region Fade
	var loader;

	Editor.prototype.fade = function()
	{
		if(!(loader instanceof BX.Loader))
		{
			loader = new BX.Loader({
				target: this.getForm()
			});
		}

		BX.addClass(this.getContainer(), this.STYLES.fade);
		loader.show();
		BX.onCustomEvent('Grid::disabled', [this]);
	};

	Editor.prototype.unFade = function()
	{
		BX.removeClass(this.getContainer(), this.STYLES.fade);
		loader.hide();
	};

	//endregion


	//region Menu

	/** @type {BX.PopupMenuWindow} modeViewPopup */
	var modeViewPopup;

	Editor.prototype.showViewModeMenu = function (event)
	{
		var node = event.currentTarget;
		if (!modeViewPopup)
		{
			modeViewPopup = new BX.PopupMenuWindow(
				'translate-view-mode-menu',
				node,
				this.viewModeMenu,
				{autoHide: true, autoClose: true, closeByEsc: true}
			);
		}

		modeViewPopup.bindElement = node;
		modeViewPopup.show();
	};


	/** @type {BX.PopupMenuWindow} extraMenuPopup */
	var extraMenuPopup;

	Editor.prototype.showExtraMenu = function (event)
	{
		var node = event.currentTarget;
		if (!extraMenuPopup)
		{
			extraMenuPopup = new BX.PopupMenuWindow(
				'translate-extra-menu',
				node,
				this.extraMenu,
				{autoHide: true, autoClose: true, closeByEsc: true}
			);
		}

		extraMenuPopup.bindElement = node;
		extraMenuPopup.show();
	};

	/** @type {BX.PopupMenuWindow} deleteMenuPopup */
	var deleteMenuPopup;

	Editor.prototype.showDeleteMenu = function (event)
	{
		var node = event.currentTarget;
		if (!deleteMenuPopup)
		{
			deleteMenuPopup = new BX.PopupMenuWindow(
				'translate-delete-menu',
				node,
				this.deleteMenu,
				{
					autoHide: true,
					autoClose: true,
					closeByEsc: true
				}
			);
		}

		deleteMenuPopup.bindElement = node;
		deleteMenuPopup.show();
	};

	//endregion


	BX.Translate.Editor = new Editor();

})(window);