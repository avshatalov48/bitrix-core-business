/**
 * @requires module:mobilelib
 * @module mobileapp
 */
;
(function ()
{

	if (window.app) return;
	/*
	 * Event list:
	 * onOpenPageAfter
	 * onOpenPageBefore
	 * onHidePageAfter
	 * onHidePageBefore
	 * UIApplicationDidBecomeActiveNotification
	 * onInternetStatusChange
	 * onOpenPush
	 * onKeyboardWillShow
	 * onKeyboardWillHide
	 * onKeyboardDidHide
	 * onKeyboardDidShow
	 */

	/**
	 * Base of Cordova Plugin
	 * @param name
	 * @constructor
	 */
	window.BXCordovaPlugin = function (name, sync, convertBoolean)
	{
		this.pluginName = name;
		this.useSyncPlugin = (sync == true);
		this.callbackIndex = 0;
		this.callbacks = {};
		this.callbackIndex = 0;
		this.dataBrigePath = (typeof mobileSiteDir == "undefined"?"/": mobileSiteDir) + "mobile/";
		this.available = false;
		this.convertBoolean = (typeof convertBoolean == "undefined" ? true: convertBoolean);
		this.platform = null;
		this.apiVersion = 0;
		this.db = null;
		var _that = this;

		document.addEventListener("deviceready", function ()
		{

			_that.available = true;
		}, false);
	};

	BXCordovaPlugin.prototype.RegisterCallBack = function (func)
	{

		if ((typeof func) === "function")
		{
			this.callbackIndex++;
			this.callbacks[this.callbackIndex] = func;
			return this.callbackIndex;

		}

		return false;
	};

	BXCordovaPlugin.prototype.CallBackExecute = function (index, result)
	{
		//execute callback by register index
		if (this.callbacks[index] && (typeof this.callbacks[index]) === "function")
		{
			this.callbacks[index](result);
		}
	};

	BXCordovaPlugin.prototype.prepareParams = function (params, convertBoolean)
	{
		//prepare params
		var convertBooleanFlag = true;
		if((typeof convertBoolean) == "boolean")
		{
			convertBooleanFlag = convertBoolean;
		}


		if (typeof(params) == "object")
		{
			for (var key in params)
			{
				if (typeof(params[key]) == "object")
					params[key] = this.prepareParams(params[key], convertBoolean);
				if (typeof(params[key]) == "function")
					params[key] = this.RegisterCallBack(params[key]);
				else if(convertBooleanFlag)
				{
					if (params[key] === true)
						params[key] = "YES";
					else if (params[key] === false)
						params[key] = "NO";
				}

			}
		}
		else
		{
			if (typeof(params) == "function")
				params = this.RegisterCallBack(params);
			else if (convertBooleanFlag)
			{
				if (params === true)
					params = "YES";
				else if (params === false)
					params = "NO";
			}
		}

		return params;
	};

	BXCordovaPlugin.prototype.clone = function(obj, copyObject)
	{
		var _obj, i, l;

		if (copyObject !== false)
			copyObject = true;

		if (obj === null)
			return null;

		if (typeof obj == 'object')
		{
			if (Object.prototype.toString.call(obj) == "[object Array]")
			{
				_obj = [];
				for (i = 0, l = obj.length; i < l; i++)
				{
					if (typeof obj[i] == "object" && copyObject)
						_obj[i] = this.clone(obj[i], copyObject);
					else
						_obj[i] = obj[i];
				}
			}
			else
			{
				_obj = {};

				for (i in obj)
				{
					if (typeof obj[i] == "object" && copyObject)
						_obj[i] = this.clone(obj[i], copyObject);
					else
						_obj[i] = obj[i];
				}
			}
		}
		else
		{
			_obj = obj;
		}

		return _obj;
	};

	BXCordovaPlugin.prototype.exec = function (funcName, params, convertBoolean)
	{
		var pluginParams = {};

		if(typeof convertBoolean == "undefined")
		{
			convertBoolean = this.convertBoolean;
		}

		if (!this.available)
		{
			document.addEventListener("deviceready", BX.proxy(function ()
			{
				this.exec(funcName, params, convertBoolean);
			}, this), false);
			return false;
		}


		if (typeof(params) != "undefined")
		{
			pluginParams = this.clone(params, true);
			pluginParams = this.prepareParams(pluginParams, convertBoolean);

			if (typeof(pluginParams) == "object")
				pluginParams = JSON.stringify(pluginParams);
		}
		else
		{
			pluginParams = "{}";
		}


		if(window.syncPlugin && this.useSyncPlugin)
		{
			window.syncPlugin.execute(funcName, pluginParams);
			return;
		}

		if (device.platform.toUpperCase() == "ANDROID" || device.cordova > '2.0.0')
		{
			return Cordova.exec(null, null, this.pluginName, funcName, [pluginParams]);
		}
		else
		{
			return Cordova.exec(this.pluginName + "." + funcName, pluginParams);
		}

	};


	/**
	 * BitrixMobile
	 * @constructor
	 */

	var app = new BXCordovaPlugin("BitrixMobile", true);
	window.app = app;

	document.addEventListener("DOMContentLoaded", function(){
		app.db = BX.dataBase.create({
			name: "Bitrix Base",
			displayName: "Bitrix Base",
			capacity: 1024 * 1024 * 4,
			version: "1.2"
		});
	});


	//#############################
	//#####--api version 12--#######
	//#############################
	/**
	 * Available actions - "show", "add"
	 * @param action
	 * @param params
	 */

	app.notificationBar = function (action, params)
	{
		this.exec("notificationBar", {"action": action, "params": params});
	};

	//#############################
	//#####--api version 10--######
	//#############################
	/**
	 * Available actions - "show", "create"
	 * @param action
	 * @param params
	 */
	app.actionSheet = function(action, params)
	{
		this.exec("actionSheet",{"action":action, "params": params});
	};

	/**
	 * Available actions - "show", "hide","setParams"
	 * @param action
	 * @param params
	 */
	app.titleAction = function(action, params)
	{
		this.exec("titleAction",{"action":action, "params": params});
	};

	/**
	 * Available actions
	 * 	"start" - starts refresh
	 * 	"stop" - stop refresh with 1 sec delay
	 * 	"setParams" - sets params
	 *  Available keys in the params object:
	 *    enable - enable/disable control
	 *    callback - js-callback which will be executed as soon as the refresh action has done
	 *  button_name - title of send button
	 *  useImageButton - bool, if true send button will be shown as an image
	 *  instead of standard send button
	 *  plusAction - js-callback for "+" button
	 *  action - js-callback, example:
	 *
	 * @param action
	 * @param params
	 */
	app.refresh = function (action, params)
	{
		this.exec("refreshAction", {"action": action, "params": params});
	};

	/**
	 * Available actions:
	 * 		"show" - shows text panel
	 * 		"hide" - hides text panel
	 * 		"clear" - clears text
	 * 		"focus" - makes text panel focused and shows keyboard
	 * 		"setParams" - sets params which were passed as a second argument
	 * Available keys in params object:
	 * 	placeholder - text placeholder
	 * 	text - text in input field
	 *  button_name - title of send button
	 *  useImageButton - bool, if true send button will be shown as an image
	 *  instead of standard send button
	 *  plusAction - js-callback for "+" button
	 *  action - js-callback, example:
	 *  			function(text)
	 *              {
	 *					app.textPanelAction("clear");
 	 *					alert(text);
 	 *				},
	 * @param action
	 * @param params
	 */
	app.textPanelAction = function (action, params)
	{
		this.exec("textPanelAction", {"action": action, "params": params});
	};

	//#############################
	//#####--api version 9--#######
	//#############################


	app.showSlidingPanel = function (params)
	{
		return this.exec("showSlidingPanel", params);
	};

	app.changeAccount = function ()
	{
		return this.exec("changeAccount", {});
	};

	//#############################
	//#####--api version 7--#######
	//#############################

	/**
	 * Shows cached documents.
	 * It may be used to deletion of unused documents
	 * to make up more free space on the disc
	 * @param params
	 * @returns {*}
	 */
	app.showDocumentsCache = function (params)
	{
		return this.exec("showDocumentsCache", params);
	};
	/**
	 * Shows additional white panel under navigation bar.
	 * @param params - The parameters
	 * @param params.buttons - The dictionary of buttons on the panel
	 * @param params.hidden_buttons_panel - The parameter control by visibility of the panel
	 * while scrolling down. true - by default
	 * @deprecated
	 * @returns {*}
	 */
	app.showButtonPanel = function (params)
	{
		return this.exec("showButtonPanel", params);
	};
	/**
	 * Hides additional white panel under navigation bar.
	 * @param params - The parameters
	 * @returns {*}
	 */
	app.hideSlidingPanel = app.hideButtonPanel = function (params)
	{
		return this.exec("hideSlidingPanel", params);
	};


	/**
	 * Shows dialog of choosing of the values
	 * @param params - The parameters
	 * @param params.callback - The handler
	 * @param params.values - The array of values. For example - ["apple","google","bitrix"]
	 * @param params.default_value - The selected item by default. For example - "bitrix"
	 * @param params.multiselect - It enables to set multiple choice mode. false - by default
	 *
	 * @returns {*}
	 */
	app.showSelectPicker = function (params)
	{
		return this.exec("showSelectPicker", params);
	};
	/**
	 * Hides dialog of choosing of the values
	 * @param params - The parameters
	 * @returns {*}
	 */
	app.hideSelectPicker = function (params)
	{
		return this.exec("hideSelectPicker", params);
	};
	/**
	 * Shows badge with the number on the button
	 * @param params
	 * @returns {*}
	 */
	app.updateButtonBadge = function (params)
	{
		return this.exec("updateButtonBadge", params);
	};

	//#############################
	//#####--api version 6--#######
	//#############################

	/**
	 * Opens barcode scanner
	 *
	 * @example
	 * app.openBarCodeScanner({
 *     callback:function(data){
 *          //handle data (example of the data  - {type:"SSD", canceled:0, text:"8293473200"})
 *     }
 * })
	 * @param params The parameters
	 * @param params.callback The handler
	 *
	 * @returns {*}
	 */
	app.openBarCodeScanner = function (params)
	{
		return this.exec("openBarCodeScanner", params);
	};

	/**
	 * Shows photo controller
	 * @example
	 * <pre>
	 *     app.openPhotos({
 *        "photos":[
 *            {
 *                "url":"http://mysite.com/sample.jpg",
 *                "description": "description text"
 *            },
 *            {
 *                "url":"/sample2.jpg",
 *                "description": "description text 2"
 *            }
 *            ...
 *       ]
 *  });
	 *  </pre>
	 * @param params The parameters
	 * @param params.photos The array of photos
	 *
	 * @returns {*}
	 */
	app.openPhotos = function (params)
	{
		return this.exec("openPhotos", params);
	};

	/**
	 * Removes all application controller cache (iOS)
	 * @param params The parameters. Empty yet.
	 * @returns {*}
	 */
	app.removeAllCache = function (params)
	{
		return this.exec("removeAllCache", params);
	};

	/**
	 * Add the page with passed url address to navigation stack
	 * @param params  The parameters
	 * @param params.url The page url
	 * @param [params.data] The data that will be saved for the page. Use getPageParams() to get stored data.
	 * @param [params.title] The title that will be placed in the center in navigation bar
	 * @param [params.unique] The unique flag for the page. false by default.
	 * @param [params.cache] The unique flag for the page. false by default.
	 * @returns {*}
	 */
	app.loadPageBlank = function (params)
	{
		return this.exec("openNewPage", params);
	};


	/**
	 * Loads the page as the first page in navigation chain.
	 * @param params The parameters
	 * @param params.url The absolute path of the page or url (http://example.com)
	 * @param [params.page_id] Identifier of the page, if this parameter will defined the page will be cached.
	 * @param [params.title] The title that will placed in the center of navigation bar
	 * @returns {*}
	 */
	app.loadPageStart = function (params)
	{
		return this.exec("loadPage", params);
	};

	/**
	 * shows confirm alert
	 * @param params
	 */
	app.confirm = function (params)
	{
		if (!this.available)
		{
			document.addEventListener("deviceready", BX.proxy(function ()
			{
				this.confirm(params)
			}, this), false);
			return;
		}

		var confirmData = {
			callback: function ()
			{
			},
			title: "",
			text: "",
			buttons: "OK"
		};
		if (params)
		{
			if (params.title)
				confirmData.title = params.title;
			if (params.buttons && params.buttons.length > 0)
			{
				confirmData.buttons = "";
				for (var i = 0; i < params.buttons.length; i++)
				{
					if (confirmData.buttons.length > 0)
					{
						confirmData.buttons += "," + params.buttons[i];
					}
					else
						confirmData.buttons = params.buttons[i];
				}
			}
			confirmData.accept = params.accept;

			if (params.text)
				confirmData.text = params.text;
			if (params.callback && typeof(params.callback) == "function")
				confirmData.callback = params.callback;
		}

		navigator.notification.confirm(
			confirmData.text,
			confirmData.callback,
			confirmData.title,
			confirmData.buttons
		);

	};
	/**
	 * shows alert with custom title
	 * @param params
	 */
	app.alert = function (params)
	{

		if (!this.available)
		{
			document.addEventListener("deviceready", BX.proxy(function ()
			{
				this.alert(params)
			}, this), false);
			return;
		}


		var alertData = {
			callback: function ()
			{
			},
			title: "",
			button: "",
			text: ""
		};

		if (typeof params == "object")
		{
			if (params.title)
				alertData.title = params.title;
			if (params.button)
				alertData.button = params.button;
			if (params.text)
				alertData.text = params.text;
			if (params.callback && typeof(params.callback) == "function")
				alertData.callback = params.callback;
		}
		else
		{
			alertData.text = params;
		}

		navigator.notification.alert(
			alertData.text,
			alertData.callback,
			alertData.title,
			alertData.button
		);

	};

	/**
	 * opens left slider
	 * @returns {*}
	 */
	app.openLeft = function ()
	{
		return this.exec("openMenu");
	};

	/**
	 * sets title of the current page
	 * @param params
	 * title - text title
	 * @returns {*}
	 */
	app.setPageTitle = function (params)
	{
		return this.exec("setPageTitle", params);
	};

	//#############################
	//#####--api version 5--#######
	//#############################
	/**
	 * removes cache of table by id
	 * in next time a table appear it will be reloaded
	 * @param tableId
	 * @returns {*}
	 */
	app.removeTableCache = function (tableId)
	{
		return this.exec("removeTableCache", {"table_id": tableId});
	};

	/** shows native datetime picker
	 * @param params
	 * @param params.format {string} date's format
	 * @param params.type {string} "datetime"|"time"|"date"
	 * @param params.callback {string}  The handler on date select event
	 * @returns {*}
	 */
	app.showDatePicker = function (params)
	{
		return this.exec("showDatePicker", params);
	};

	/**
	 * hides native datetime picker
	 * @returns {*}
	 */
	app.hideDatePicker = function ()
	{

		return this.exec("hideDatePicker");
	};

	//#############################
	//#####--api version 4--#######
	//#############################
	/**
	 * @deprecated
	 * Shows native input panel
	 * @param params
	 * @param {string} params.placeholder  Text for the placeholder
	 * @param {string} params.button_name  Label of the button
	 * @param {function} params.action Onclick-handler for the button
	 * @example
	 * app.showInput({
 *				placeholder:"New message...",
 *				button_name:"Send",
 *				action:function(text)
 *				{
 *					app.clearInput();
 *					alert(text);
 *				},
 *			});
	 * @returns {*}
	 */
	app.showInput = function (params)
	{
		return this.exec("showInput", params);
	};

	/**
	 * @deprecated
	 * use it to disable with activity indicator or enable button
	 * @param {boolean} loading_status
	 * @returns {*}
	 */
	app.showInputLoading = function (loading_status)
	{
		if (loading_status && loading_status !== true)
			loading_status = false;
		return this.exec("showInputLoading", {"status": loading_status});

	};

	/**
	 * Clears native input
	 * @deprecated
	 * @returns {*}
	 */
	app.clearInput = function ()
	{
		return this.exec("clearInput");
	};

	/**
	 * hides native input
	 * @returns {*}
	 */
	app.hideInput = function ()
	{
		return this.exec("hideInput");
	};

//#############################
//#####--api version 3--#######
//#############################

	/**
	 * reloads page
	 * @param params
	 */
	app.reload = function (params)
	{
		var params = params || {url: document.location.href};

		if (window.platform == 'android')
		{
			this.exec('reload', params);
		}
		else
		{
			document.location.href = params.url;
		}
	};

	/**
	 * makes flip-screen effect
	 * @returns {*}
	 */
	app.flipScreen = function ()
	{
		return this.exec("flipScreen");
	};

	/**
	 * removes buttons of the page
	 * @deprecated
	 * @param params
	 * @param {string} params.position Position of button
	 * @returns {*}
	 */
	app.removeButtons = function (params)
	{
		return this.exec("removeButtons", params);
	};

	/**
	 *
	 * @param {object} params Settings of the table
	 * @param {string} params.url The url to download json-data
	 * @param {string} [params.table_id] The identifier of the table
	 * @param {boolean} [params.isroot] If true the table will be opened as first screen
	 * @param {object} [params.TABLE_SETTINGS]  Start settings of the table, it can be overwritten after download json data
	 * @param {object} [params.table_settings]  Start settings of the table, it can be overwritten after download json data
	 * @description TABLE_SETTINGS
	 *     callback: handler on ok-button tap action, it works only when 'markmode' is true
	 *     markmode: set it true to turn on mark mode, false - by default
	 *     modal: if true your table will be opened in modal dialog, false - by default
	 *     multiple: it works if 'markmode' is true, set it false to turn off multiple selection
	 *     okname - name of ok button
	 *     cancelname - name of cancel button
	 *     showtitle: true - to make title visible, false - by default
	 *     alphabet_index: if true - table will be divided on alphabetical sections
	 *     selected: this is a start selected data in a table, for example {users:[1,2,3,4],groups:[1,2,3]}
	 *     button:{
 	*                name: "name",
 	*                type: "plus",
 	*                callback:function(){
 	*                    //your code
 	*                }
 	*     };
	 * @returns {*}
	 */
	app.openBXTable = function (params)
	{
		if (typeof(params.table_settings) != "undefined")
		{
			params.TABLE_SETTINGS = params.table_settings;
			delete params.table_settings;
		}
		if (params.TABLE_SETTINGS.markmode && params.TABLE_SETTINGS.markmode == true)
		{
			if (params.TABLE_SETTINGS.callback && typeof(params.TABLE_SETTINGS.callback) == "function")
			{
				var insertCallback = params.TABLE_SETTINGS.callback;
				params.TABLE_SETTINGS.callback = function (data)
				{
					insertCallback(data);
				}
			}
		}

		if(typeof params.TABLE_SETTINGS.modal !== "undefined")
		{
			params.modal = params.TABLE_SETTINGS.modal;
		}

		if(typeof params.TABLE_SETTINGS.name !== "undefined")
		{
			params.TABLE_SETTINGS.showtitle = true;
		}

		return this.exec("openBXTable", params);
	};

	/**
	 * Open document in separated window
	 * @deprecated
	 * @param params
	 * @param {string} params.url  The document url
	 * @example
	 * app.openDocument({"url":"/upload/123.doc"});
	 * @returns {*}
	 */
	app.openDocument = function (params)
	{
		return this.exec("openDocument", params);
	};

	/**
	 * Shows the small loader in the center of the screen
	 * The loader will be automatically hided when "back" button pressed
	 * @param params - settings
	 * @param params.text The text of the loader
	 * @returns {*}
	 */
	app.showPopupLoader = function (params)
	{
		return this.exec("showPopupLoader", params);
	};

	/**
	 * Hides the small loader
	 * @param params The parameters
	 * @returns {*}
	 */
	app.hidePopupLoader = function (params)
	{
		return this.exec("hidePopupLoader", params);
	};

	/**
	 * Changes the parameters of the current page, that can be getted by getPageParams()
	 * @param params - The parameters
	 * @param params.data any mixed data
	 * @param {function} params.callback The callback-handler
	 * @returns {*}
	 */
	app.changeCurPageParams = function (params)
	{
		return this.exec("changeCurPageParams", params);
	};

	/**
	 * Gets the parameters of the page
	 * @param params The parameters
	 * @param {function} params.callback The handler
	 * @returns {*}
	 */
	app.getPageParams = function (params)
	{

		if (!this.enableInVersion(3))
			return false;

		return this.exec("getPageParams", params);
	};

	/**
	 * Creates the ontext menu of the page
	 * @example
	 * Parameters example:
	 * <pre>
	 *params =
	 *{
	*   			items:[
	*				{
	*					name:"Post message",
	*					action:function() { postMessage();},
	*					image: "/upload/post_message_icon.phg"
	*				},
	*				{
	*					name:"To Bitrix!",
	*					url:"http://bitrix.ru",
	*					icon: 'settings'
	*				}
	*			]
	 *}
	 *
	 * </pre>
	 * @param params - the set of options
	 * @config {array} items - array of menu items
	 * @config {bool} useNavigationBarColor - color of navigation bar will be apply
	 * as a background color for the page menu. false by default
	 * @returns {*}
	 */
	app.menuCreate = function (params)
	{
		return this.exec("menuCreate", params);
	};

	/**
	 * Shows the context menu
	 * @returns {*}
	 */
	app.menuShow = function ()
	{
		return this.exec("menuShow");
	};

	/**
	 * Hides the context menu
	 * @returns {*}
	 */
	app.menuHide = function ()
	{
		return this.exec("menuHide");
	};

//#############################
//#####--api version 2--#######
//#############################

	/**
	 * Checks if it's required application version or not
	 * @param ver The version of API
	 * @param [strict]
	 * @returns {boolean}
	 */
	app.enableInVersion = function (ver, strict)
	{
		if(this.apiVersion == 0)
		{
			try
			{
				if(typeof (BXMobileAppContext) != "undefined" && typeof (BXMobileAppContext.getApiVersion) == "function")
				{
					this.apiVersion = BXMobileAppContext.getApiVersion();
				}
				else if(typeof(appVersion) != "undefined")
				{
					this.apiVersion = appVersion;
				}

			} catch (e)
			{
				//do nothing
			}
		}

		return (typeof(strict)!="undefined" && strict == true)
					? (parseInt(this.apiVersion) == parseInt(ver))
					: (parseInt(this.apiVersion) >= parseInt(ver));
	};


	/**
	 * Checks if the page is visible in this moment
	 * @param params The parameters
	 * @param params.callback The handler
	 * @returns {*}
	 */
	app.checkOpenStatus = function (params)
	{
		return this.exec("checkOpenStatus", params);
	};

	app.asyncRequest = function (params)
	{
		//native asyncRequest
		//params.url
		return this.exec("asyncRequest", params);
	};

//#############################
//#####--api version 1--#######
//#############################

	/**
	 * Opens url in external browser
	 * @param url
	 * @returns {*}
	 */
	app.openUrl = function (url)
	{
		//open url in external browser
		return this.exec("openUrl", url);
	};

	/**
	 * Register a callback
	 * @param {function} func The callback function
	 * @returns {number}
	 * @constructor
	 */
	app.RegisterCallBack = function (func)
	{
		if (typeof(func) == "function")
		{
			this.callbackIndex++;

			this.callbacks["callback" + this.callbackIndex] = func;

			return this.callbackIndex;
		}

	};

	/**
	 * Execute registered callback function by index
	 * @param index The index of callback function
	 * @param result The parameters that will be passed to callback as a first argument
	 * @constructor
	 */
	app.CallBackExecute = function (index, result)
	{
		if (this.callbacks["callback" + index] && (typeof this.callbacks["callback" + index]) === "function")
		{
			this.callbacks["callback" + index](result);
		}
	};

	/**
	 * Generates the javascript-event
	 * that can be caught by any application browsers
	 * except current browser
	 * @deprecated
	 * @param eventName
	 * @param params
	 * @param where
	 * @returns {*|Array|{index: number, input: string}}
	 * @param needPrepare
	 */
	app.onCustomEvent = function (eventName, params, where, needPrepare)
	{
		if(typeof needPrepare == "undefined")
		{
			needPrepare = true;
		}

		if (!this.available)
		{
			document.addEventListener("deviceready", BX.delegate(function ()
			{
				this.onCustomEvent(eventName, params, where, needPrepare);
			}, this), false);

			return;
		}
		if(needPrepare)
			params = this.prepareParams(params);

		if (typeof(params) == "object")
			params = JSON.stringify(params);

		if (device.platform.toUpperCase() == "ANDROID" || device.cordova > '2.0.0')
		{
			var params_pre = {
				"eventName": eventName,
				"params": params
			};
			return Cordova.exec(null, null, "BitrixMobile", "onCustomEvent", [params_pre]);
		}
		else
		{
			return Cordova.exec("BitrixMobile.onCustomEvent", eventName, params, where);
		}
	};

	/**
	 * Gets javascript variable from current and left
	 * @param params The parameters
	 * @param params.callback The handler
	 * @param params.var The variable's name
	 * @param params.from The browser ("left"|"current")
	 * @returns {*}
	 */
	app.getVar = function (params)
	{
		return this.exec("getVar", params);
	};

	/**
	 *
	 * @param variable
	 * @param key
	 * @returns {*}
	 */
	app.passVar = function (variable, key)
	{

		try
		{
			evalVar = window[variable];
			if (!evalVar)
				evalVar = "empty"
		}
		catch (e)
		{
			evalVar = ""
		}

		if (evalVar)
		{

			if (typeof(evalVar) == "object")
				evalVar = JSON.stringify(evalVar);

			if (platform.toUpperCase() == "ANDROID")
			{

				key = key || false;
				if (key)
					Bitrix24Android.receiveStringValue(JSON.stringify({variable: evalVar, key: key}));
				else
					Bitrix24Android.receiveStringValue(evalVar);
			} else
			{
				return evalVar;
			}
		}
	};


	/**
	 * Opens the camera/albums dialog
	 * @param options The parameters
	 * @param options.source  0 - albums, 1 - camera
	 * @param options.callback The event handler that will be fired when the photo will have selected. Photo will be passed into the callback in base64 as a first parameter.
	 */
	app.takePhoto = function (options)
	{
		if (!this.available)
		{
			document.addEventListener("deviceready", BX.proxy(function ()
			{
				this.takePhoto(options);
			}, this), false);
			return;
		}

		if (!options.callback)
			options.callback = function ()
			{
			};
		if (!options.fail)
			options.fail = function ()
			{
			};

		var params = {
			quality: (options.quality || (this.enableInVersion(2) ? 40 : 10)),
			correctOrientation: (options.correctOrientation || false),
			targetWidth: (options.targetWidth || false),
			targetHeight: (options.targetHeight || false),
			sourceType: ((typeof options.source != "undefined") ? options.source : 0),
			mediaType: ((typeof options.mediaType != "undefined") ? options.mediaType : 0),
			allowEdit: ((typeof options.allowEdit != "undefined") ? options.allowEdit : false),
			saveToPhotoAlbum: ((typeof options.saveToPhotoAlbum != "undefined") ? options.saveToPhotoAlbum : false)
		};

		if (options.destinationType !== undefined)
			params.destinationType = options.destinationType;
		navigator.camera.getPicture(options.callback, options.fail, params);


	};
	/**
	 * Opens left screen of the slider
	 * @deprecated It is deprecated. Use BitrixMobile.openLeft.
	 * @see BitrixMobile.openLeft
	 * @returns {*}
	 */
	app.openMenu = function ()
	{
		return this.exec("openMenu");
	};

	/**
	 * Opens page in modal dialog
	 * @param options The parameters
	 * @param options.url The page url
	 * @returns {*}
	 */
	app.showModalDialog = function (options)
	{
		//"cache" flag must be false by default
		// for modal pages to save backward compatibility
		if(typeof(options["cache"]) == "undefined")
		{
			options["cache"] = false;
		}

		return this.exec("showModalDialog", options);
	};

	/**
	 * Closes current modal dialog
	 * @param options
	 * @returns {*}
	 */
	app.closeModalDialog = function (options)
	{
		return this.exec("closeModalDialog", options);
	};

	/**
	 * Closes current controller
	 * @param [params] The parameters
	 * @param {boolean} [params.drop] It works on <b>Android</b> only. <u>true</u> - the controller will be dropped after it has disappeared, <u>false</u> - the controller will not be dropped after it has disappeared.
	 * @returns {*}
	 */
	app.closeController = function (params)
	{
		return this.exec("closeController", params);
	};

	/**
	 * Adds buttons to the navigation panel.
	 * @param buttons The parameters
	 * @param buttons.callback The onclick handler
	 * @param buttons.type  The type of the button (plus|back|refresh|right_text|back_text|users|cart)
	 * @param buttons.name The name of the button
	 * @param buttons.bar_type The panel type ("toolbar"|"navbar")
	 * @param buttons.position The position of the button ("left"|"right")
	 * @returns {*}
	 */
	app.addButtons = function (buttons)
	{
		return this.exec("addButtons", buttons);
	};

	/**
	 * Opens the center of the slider
	 * @returns {*}
	 */
	app.openContent = function ()
	{
		return this.exec("openContent");
	};

	/**
	 * Opens the left side of the slider
	 * @deprecated Use closeLeft()
	 * @returns {*}
	 */
	app.closeMenu = function ()
	{
		return this.exec("closeMenu");
	};

	/**
	 * Opens the page as the first page in the navigation stack
	 * @deprecated Use loadStartPage(params).
	 * @param url
	 * @param page_id
	 * @returns {*}
	 */
	app.loadPage = function (url, page_id)
	{
		//open page from menu
		if (this.enableInVersion(2) && page_id)
		{
			params = {
				url: url,
				page_id: page_id
			};
			return this.exec("loadPage", params);
		}

		return this.exec("loadPage", url);
	};

	/**
	 * Sets identifier of the page
	 * @param pageID
	 * @returns {*}
	 */
	app.setPageID = function (pageID)
	{
		return this.exec("setPageID", pageID);
	};

	/**
	 * Opens the new page with slider effect
	 * @deprecated Use loadPageBlank(params)
	 * @param url
	 * @param data
	 * @param title
	 * @returns {*}
	 */
	app.openNewPage = function (url, data, title)
	{

		if (this.enableInVersion(3))
		{
			var params = {
				url: url,
				data: data,
				title: title
			};

			return this.exec("openNewPage", params);
		}
		else
			return this.exec("openNewPage", url);
	};

	/**
	 * Loads the page into the left side of the slider using the url
	 * @deprecated
	 * @param url
	 * @returns {*}
	 */
	app.loadMenu = function (url)
	{
		return this.exec("loadMenu", url);
	};

	/**
	 * Opens the list
	 * @deprecated Use openBXTable();
	 * @returns {*}
	 * @param params
	 */
	app.openTable = function (params)
	{
		if (params.markmode && params.markmode == true)
		{
			if (params.callback && typeof(params.callback) == "function")
			{
				if (!(params.skipSpecialChars && params.skipSpecialChars === true))
				{
					var insertCallback = params.callback;

					params.callback = function (data)
					{
						insertCallback(BitrixMobile.Utils.htmlspecialchars(data));
					}
				}
			}
		}
		return this.exec("openTable", params);
	};

	/**
	 * @deprecated Use openBXTable()
	 *  <b>PLEASE, DO NOT USE IT!!!!</b>
	 * It is simple wrapper of openBXTable()
	 * @see BitrixMobile.openBXTable
	 * @param options The parameter.
	 * @returns {*}
	 */
	app.openUserList = function (options)
	{
		return this.exec("openUserList", options);
	};

	app.addUserListButton = function (options)
	{
		//open table controller
		//options.url
		return this.exec("addUserListButton", options);
	};

	app.pullDown = function (params)
	{
		//on|off pull down action on the current page
		//params.pulltext, params.downtext, params.loadtext
		//params.callback - action on pull-down-refresh
		//params.enable - true|false

		var _params = BX.clone(params);

		if(
			typeof _params.backgroundColor == "undefined"
			|| !BX.type.isNotEmptyString(_params.backgroundColor)
		)
		{
			var bodySelector = null;

			try
			{
				bodySelector = (document.body.className != null && document.body.className.length>0
						? document.querySelector("."+document.body.className)
						: null
				);
			}
			catch (e)
			{
				//do nothing
			}

			if(bodySelector != null)
			{
				var bodyStyles = getComputedStyle(bodySelector);
				var rgb2hex = function(rgb){
					rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
					return (rgb && rgb.length === 4) ? "#" +
						("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
						("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
						("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
				};
				var color  = rgb2hex(bodyStyles.backgroundColor);
				if(color != "#000000")
					_params.backgroundColor = color;
				else
					_params.backgroundColor = "#ffffff";
			}
		}

		return this.exec("pullDown", _params);
	};
	/**
	 * @deprecated
	 * @returns {*}
	 */
	app.pullDownLoadingStop = function ()
	{
		return this.exec("pullDownLoadingStop");
	};

	/**
	 * Enables or disables scroll ability of the current page
	 * @deprecated
	 * @param enable_status The scroll ability status
	 * @returns {*}
	 */
	app.enableScroll = function (enable_status)
	{
		//enable|disable scroll on the current page
		var enable_status = enable_status || false;
		return this.exec("enableScroll", enable_status);
	};

	/**
	 * Enables or disables firing events of  hiding/showing  of soft keyboard
	 * @deprecated
	 * @param enable_status
	 * @returns {*}
	 */
	app.enableCaptureKeyboard = function (enable_status)
	{
		//enable|disable capture keyboard event on the current page
		var enable_status = enable_status || false;
		return this.exec("enableCaptureKeyboard", enable_status);
	};

	/**
	 * Enables or disables the ability of automatic showing/hiding of the loading screen at the current page
	 * when it has started or has finished loading process
	 * @deprecated
	 * @param enable_status The ability status
	 * @returns {*}
	 */
	app.enableLoadingScreen = function (enable_status)
	{
		//enable|disable autoloading screen on the current page
		var enable_status = enable_status || false;
		return this.exec("enableLoadingScreen", enable_status);
	};


	/**
	 *@deprecated
	 * Shows the loading screen at the page
	 * @returns {*}
	 */
	app.showLoadingScreen = function ()
	{
		//show loading screen
		return this.exec("showLoadingScreen");
	};

	/**
	 * Hides the loadding screen at the page
	 * @deprecated
	 * @returns {*}
	 */
	app.hideLoadingScreen = function ()
	{
		//hide loading screen
		return this.exec("hideLoadingScreen");
	};


	/**
	 * Sets visibility status of the navigation bar
	 * @deprecated
	 * @param {boolean} visible The visibility status
	 * @returns {*}
	 */
	app.visibleNavigationBar = function (visible)
	{
		//visibility status of the native navigation bar
		var visible = visible || false;
		return this.exec("visibleNavigationBar", visible);
	};

	/**
	 * Sets visibility status of the bottom bar
	 * @deprecated
	 * @param {boolean} visible The visibility status
	 * @returns {*}
	 */
	app.visibleToolBar = function (visible)
	{
		//visibility status of toolbar at the bottom
		var visible = visible || false;
		return this.exec("visibleToolBar", visible);
	};

	/**
	 * @deprecated
	 * @param enable
	 * @returns {*}
	 */
	app.enableSliderMenu = function (enable)
	{
		//lock|unlock slider menu
		var enable = enable || false;
		return this.exec("enableSliderMenu", enable);
	};

	app.enableRight = function (enable)
	{
		//lock|unlock slider menu
		var enable = enable || false;
		return this.exec("enableRight", enable);
	};

	/**
	 * @deprecated
	 * @param counters
	 * @returns {*}
	 */
	app.setCounters = function (counters)
	{
		//set counters values on the navigation bar
		//counters.messages,counters.notifications
		return this.exec("setCounters", counters);
	};

	/**
	 * @deprecated
	 * @param number
	 * @returns {*}
	 */
	app.setBadge = function (number)
	{
		//application's badge number on the dashboard
		return this.exec("setBadge", number);
	};

	/**
	 * @deprecated
	 * @param pagename
	 * @returns {*}
	 */
	app.refreshPanelPage = function (pagename)
	{
		//set counters values on the navigation bar
		//counters.messages,counters.notifications

		if (!pagename)
			pagename = "";
		var options = {
			page: pagename
		};
		return this.exec("refreshPanelPage", options);
	};


	/**
	 * Sets page urls for the notify popup window and the messages popup window
	 * @deprecated
	 * @param pages
	 * @returns {*}
	 */
	app.setPanelPages = function (pages)
	{
		//pages for notify panel
		//pages.messages_page, pages.notifications_page,
		//pages.messages_open_empty, pages.notifications_open_empty
		return this.exec("setPanelPages", pages);
	};

	/**
	 * Gets the token from the current device. You may use the token to send push-notifications to the device.
	 * @returns {*}
	 */
	app.getToken = function ()
	{
		//get device token
		var dt = "APPLE";
		if (platform != "ios")
			dt = "GOOGLE";
		var params = {
			callback: function (token)
			{
				BX.proxy(
					BX.ajax.post(
						app.dataBrigePath,
						{
							mobile_action: "save_device_token",
							device_name: (typeof device.name == "undefined"? device.model: device.name),
							uuid: device.uuid,
							device_token: token,
							device_type: dt,
							sessid: BX.bitrix_sessid()
						},
						function (data)
						{
						}), this);
			}
		};

		return this.exec("getToken", params);
	};

	/**
	 * Executes a request by the check_url with Basic Authorization header
	 * @param params The parameters
	 * @param params.success The success javascript handler
	 * @param params.check_url The check url
	 * @returns {*}
	 * @constructor
	 */
	app.BasicAuth = function (params)
	{
		//basic authorization
		//params.success, params.check_url
		params = params || {};

		var userSuccessCallback = (params.success && typeof(params.success) == "function")
			? params.success
			: function ()
		{
		};
		var userFailCallback = (params.failture && typeof(params.failture) == "function")
			? params.failture
			: function ()
		{
		};

		var authParams = {
			check_url: params.check_url,
			success: function (data)
			{
				if (typeof data != "object")
				{
					try
					{
						data = JSON.parse(data);
					}
					catch (e)
					{
						data = {"status": "failed"}
					}
				}

				if (data.status == "success" && data.sessid_md5)
				{
					if (BX.message.bitrix_sessid != data.sessid_md5)
					{
						BX.message.bitrix_sessid = data.sessid_md5;
						app.onCustomEvent("onSessIdChanged", {sessid: data.sessid_md5});
					}

				}

				userSuccessCallback(data);
			},
			failture: function (data)
			{
				if (data.status == "failed")
					app.exec("showAuthForm");
				else
					userFailCallback(data);
			}

		};

		return this.exec("BasicAuth", authParams);
	};

	/**
	 * Logout
	 * @deprecated DO NOT USE IT ANY MORE!!!!
	 * @see BitrixMobile#asyncRequest
	 * @see BitrixMobile#showAuthForm
	 * @returns {*}
	 */
	app.logOut = function ()
	{
		//logout
		//request to mobile.data with mobile_action=logout
		if (this.enableInVersion(2))
		{
			this.asyncRequest({url: this.dataBrigePath + "?mobile_action=logout&uuid=" + device.uuid});
			return this.exec("showAuthForm");
		}

		var xhr = new XMLHttpRequest();
		xhr.open("GET", this.dataBrigePath + "?mobile_action=logout&uuid=" + device.uuid, true);
		xhr.onreadystatechange = function ()
		{
			if (xhr.readyState == 4 && xhr.status == "200")
			{
				return app.exec("showAuthForm");
			}

		};
		xhr.send(null);
	};
	/**
	 * Get location data
	 * @param options
	 */
	app.getCurrentLocation = function (options)
	{

		if (!this.available)
		{
			document.addEventListener("deviceready", BX.proxy(function ()
			{
				this.getCurrentLocation(options);
			}, this), false);
			return;
		}
		//get geolocation data
		var geolocationSuccess;
		var geolocationError;
		if (options)
		{
			geolocationSuccess = options.onsuccess;
			geolocationError = options.onerror;
		}
		navigator.geolocation.getCurrentPosition(
			geolocationSuccess, geolocationError);
	};

	app.setVibrate = function (ms)
	{
		// vibrate (ms)
		ms = ms || 500;
		navigator.notification.vibrate(parseInt(ms));
	};

	app.bindloadPageBlank = function ()
	{
		//Hack for Android Platform
		document.addEventListener(
			"DOMContentLoaded",
			function ()
			{
				document.body.addEventListener(
					"click",
					function (e)
					{
						var intentLink = null;
						var hash = "__bx_android_click_detect__";
						if (e.target.tagName.toUpperCase() == "A")
							intentLink = e.target;
						else
							intentLink = BX.findParent(e.target, {tagName: "A"}, 10);

						if (intentLink && intentLink.href && intentLink.href.length > 0)
						{
							if (intentLink.href.indexOf(hash) == -1 && intentLink.href.indexOf("javascript") != 0)
							{
								if (intentLink.href.indexOf("#") == -1)
									intentLink.href += "#" + hash;
								else
									intentLink.href += "&" + hash;
							}

						}

					},
					false
				);
			},
			false
		);

	};

	BitrixMobile = {};
	BitrixMobile.Utils = {

		autoResizeForm: function (textarea, pageContainer, maxHeight)
		{
			if (!textarea || !pageContainer)
				return;

			var formContainer = textarea.parentNode;
			maxHeight = maxHeight || 126;

			var origTextareaHeight = (textarea.ownerDocument || document).defaultView.getComputedStyle(textarea, null).getPropertyValue("height");
			var origFormContainerHeight = (formContainer.ownerDocument || document).defaultView.getComputedStyle(formContainer, null).getPropertyValue("height");

			origTextareaHeight = parseInt(origTextareaHeight); //23
			origFormContainerHeight = parseInt(origFormContainerHeight); //51
			textarea.setAttribute("data-orig-height", origTextareaHeight);
			formContainer.setAttribute("data-orig-height", origFormContainerHeight);

			var currentTextareaHeight = origTextareaHeight;
			var hiddenTextarea = document.createElement("textarea");
			hiddenTextarea.className = "send-message-input";
			hiddenTextarea.style.height = currentTextareaHeight + "px";
			hiddenTextarea.style.visibility = "hidden";
			hiddenTextarea.style.position = "absolute";
			hiddenTextarea.style.left = "-300px";

			document.body.appendChild(hiddenTextarea);

			textarea.addEventListener("change", resize, false);
			textarea.addEventListener("cut", resizeDelay, false);
			textarea.addEventListener("paste", resizeDelay, false);
			textarea.addEventListener("drop", resizeDelay, false);
			textarea.addEventListener("keyup", resize, false);

			if (window.platform == "android")
				textarea.addEventListener("keydown", resizeDelay, false);

			function resize()
			{
				hiddenTextarea.value = textarea.value;
				var scrollHeight = hiddenTextarea.scrollHeight;
				if (scrollHeight > maxHeight)
					scrollHeight = maxHeight;

				if (currentTextareaHeight != scrollHeight)
				{
					currentTextareaHeight = scrollHeight;
					textarea.style.height = scrollHeight + "px";
					formContainer.style.height = origFormContainerHeight + (scrollHeight - origTextareaHeight) + "px";
					pageContainer.style.bottom = origFormContainerHeight + (scrollHeight - origTextareaHeight) + "px";

					if (window.platform == "android")
						window.scrollTo(0, document.documentElement.scrollHeight);
				}
			}

			function resizeDelay()
			{
				setTimeout(resize, 0);
			}

		},

		resetAutoResize: function (textarea, pageContainer)
		{

			if (!textarea || !pageContainer)
				return;

			var formContainer = textarea.parentNode;

			var origTextareaHeight = textarea.getAttribute("data-orig-height");
			var origFormContainerHeight = formContainer.getAttribute("data-orig-height");

			textarea.style.height = origTextareaHeight + "px";
			formContainer.style.height = origFormContainerHeight + "px";
			pageContainer.style.bottom = origFormContainerHeight + "px";
		},

		showHiddenImages: function ()
		{
			var images = document.getElementsByTagName("img");
			for (var i = 0; i < images.length; i++)
			{
				var image = images[i];
				var realImage = image.getAttribute("data-src");
				if (!realImage)
					continue;

				if (BitrixMobile.Utils.isElementVisibleOnScreen(image))
				{
					image.src = realImage;
					image.setAttribute("data-src", "");
				}
			}
		},

		isElementVisibleOnScreen: function (element)
		{
			var coords = BitrixMobile.Utils.getElementCoords(element);

			var windowTop = window.pageYOffset || document.documentElement.scrollTop;
			var windowBottom = windowTop + document.documentElement.clientHeight;

			coords.bottom = coords.top + element.offsetHeight;

			var topVisible = coords.top > windowTop && coords.top < windowBottom;
			var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

			return topVisible || bottomVisible;
		},

		isElementVisibleOn2Screens: function (element)
		{
			var coords = BitrixMobile.Utils.getElementCoords(element);

			var windowHeight = document.documentElement.clientHeight;
			var windowTop = window.pageYOffset || document.documentElement.scrollTop;
			var windowBottom = windowTop + windowHeight;

			coords.bottom = coords.top + element.offsetHeight;

			windowTop -= windowHeight;
			windowBottom += windowHeight;

			var topVisible = coords.top > windowTop && coords.top < windowBottom;
			var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

			return topVisible || bottomVisible;

		},

		getElementCoords: function (element)
		{
			var box = element.getBoundingClientRect();

			return {
				originTop: box.top,
				originLeft: box.left,
				top: box.top + window.pageYOffset,
				left: box.left + window.pageXOffset
			};
		},

		htmlspecialchars: function (variable)
		{
			if (BX.type.isString(variable))
				return variable.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

			if (BX.type.isArray(variable))
			{
				for (var i = 0; i < variable.length; i++)
				{
					variable[i] = BitrixMobile.Utils.htmlspecialchars(variable[i]);
				}
			}
			else if (typeof(variable) == "object" && variable != null)
			{

				var obj = {};
				for (var key in variable)
					obj[key] = BitrixMobile.Utils.htmlspecialchars(variable[key]);
				variable = obj;
			}

			return variable;

		}
	};


	BitrixMobile.fastClick = {
		bindDelegate:function(elem, isTarget, handler)
		{
			if(typeof window.BX != "undefined")
			{
				var h = BX.delegateEvent(isTarget, handler);
				new FastButton(elem, h, true);
			}
			else
			{
				document.addEventListener('DOMContentLoaded', function ()
				{
					BitrixMobile.fastClick.bindDelegate(elem, isTarget, handler)
				});

			}
		},
		bind:function(elem, handler)
		{
			new FastButton(elem, handler, true);
		}

	};

	BitrixMobile.LazyLoad = {

		images: [],

		status: {
			hidden: -2,
			error: -1,
			"undefined": 0,
			inited: 1,
			loaded: 2
		},

		types: {
			image: 1,
			background: 2
		},

		clearImages: function ()
		{
			this.images = [];
		},

		showImages: function (checkOwnVisibility)
		{
			checkOwnVisibility = checkOwnVisibility === false ? false : true;
			for (var i = 0, length = this.images.length; i < length; i++)
			{
				var image = this.images[i];
				if (image.status == this.status.undefined)
				{
					this._initImage(image);
				}

				if (image.status !== this.status.inited)
				{
					continue;
				}

				if (!image.node || !image.node.parentNode)
				{
					image.node = null;
					image.status = BitrixMobile.LazyLoad.status.error;
					continue;
				}

				var isImageVisible = true;
				if (checkOwnVisibility && image.func)
				{
					isImageVisible = image.func(image);
				}

				if (isImageVisible === true && BitrixMobile.Utils.isElementVisibleOn2Screens(image.node))
				{
					if (image.type == BitrixMobile.LazyLoad.types.image)
					{
						image.node.src = image.src;
					}
					else
					{
						image.node.style.backgroundImage = "url('" + image.src + "')";
					}

					image.node.setAttribute("data-src", "");
					image.status = this.status.loaded;
					image.node.onload = function() {
						BX.onCustomEvent('BX.LazyLoad:ImageLoaded', [ this ]);
					};
				}
			}
		},

		registerImage: function (id, isImageVisibleCallback)
		{
			if (BX.type.isNotEmptyString(id))
			{
				this.images.push({
					id: id,
					node: null,
					src: null,
					type: null,
					func: BX.type.isFunction(isImageVisibleCallback) ? isImageVisibleCallback : null,
					status: this.status.undefined
				});
			}
		},

		registerImages: function (ids, isImageVisibleCallback)
		{
			if (BX.type.isArray(ids))
			{
				for (var i = 0, length = ids.length; i < length; i++)
				{
					this.registerImage(ids[i], isImageVisibleCallback);
				}
			}
		},

		_initImage: function (image)
		{
			image.status = this.status.error;
			var node = BX(image.id);
			if (node)
			{
				var src = node.getAttribute("data-src");
				if (BX.type.isNotEmptyString(src))
				{
					image.node = node;
					image.src = src;
					image.status = this.status.inited;
					image.type = image.node.tagName.toLowerCase() == "img" ?
						BitrixMobile.LazyLoad.types.image :
						BitrixMobile.LazyLoad.types.background;
				}
			}
		},

		getImageById: function (id)
		{
			for (var i = 0, length = this.images.length; i < length; i++)
			{
				if (this.images[i].id == id)
				{
					return this.images[i];
				}
			}

			return null;
		},

		removeImage: function (id)
		{
			for (var i = 0, length = this.images.length; i < length; i++)
			{
				if (this.images[i].id == id)
				{
					this.images = BX.util.deleteFromArray(this.images, i);
					break;
				}
			}

		},

		onScroll: function ()
		{
			BitrixMobile.LazyLoad.showImages();
		}

	};


	window.BitrixAnimation = {

		animate: function (options)
		{
			if (!options || !options.start || !options.finish ||
				typeof(options.start) != "object" || typeof(options.finish) != "object"
			)
				return null;

			for (var propName in options.start)
			{
				if (!options.finish[propName])
				{
					delete options.start[propName];
				}
			}

			options.progress = function (progress)
			{
				var state = {};
				for (var propName in this.start)
					state[propName] = Math.round(this.start[propName] + (this.finish[propName] - this.start[propName]) * progress);

				if (this.step)
					this.step(state);
			};

			return BitrixAnimation.animateProgress(options);
		},

		animateProgress: function (options)
		{
			var start = new Date();
			var delta = options.transition || BitrixAnimation.transitions.linear;
			var duration = options.duration || 1000;

			var timer = setInterval(function ()
			{

				var progress = (new Date() - start) / duration;
				if (progress > 1)
					progress = 1;

				options.progress(delta(progress));

				if (progress == 1)
				{
					clearInterval(timer);
					options.complete && options.complete();
				}

			}, options.delay || 13);

			return timer;
		},

		makeEaseInOut: function (delta)
		{
			return function (progress)
			{
				if (progress < 0.5)
					return delta(2 * progress) / 2;
				else
					return (2 - delta(2 * (1 - progress))) / 2;
			}
		},

		makeEaseOut: function (delta)
		{
			return function (progress)
			{
				return 1 - delta(1 - progress);
			};
		},

		transitions: {

			linear: function (progress)
			{
				return progress;
			},

			elastic: function (progress)
			{
				return Math.pow(2, 10 * (progress - 1)) * Math.cos(20 * Math.PI * 1.5 / 3 * progress);
			},

			quad: function (progress)
			{
				return Math.pow(progress, 2);
			},

			cubic: function (progress)
			{
				return Math.pow(progress, 3);
			},

			quart: function (progress)
			{
				return Math.pow(progress, 4);
			},

			quint: function (progress)
			{
				return Math.pow(progress, 5);
			},

			circ: function (progress)
			{
				return 1 - Math.sin(Math.acos(progress));
			},

			back: function (progress)
			{
				return Math.pow(progress, 2) * ((1.5 + 1) * progress - 1.5);
			},

			bounce: function (progress)
			{
				for (var a = 0, b = 1; 1; a += b, b /= 2)
				{
					if (progress >= (7 - 4 * a) / 11)
					{
						return -Math.pow((11 - 6 * a - 11 * progress) / 4, 2) + Math.pow(b, 2);
					}
				}
			}
		}
	};

//Events' handlers

	document.addEventListener('DOMContentLoaded', function ()
	{
		//if we are using framecache+appcache we should to refresh server-depended lang variables
		BX.addCustomEvent("onFrameDataReceived", function (data)
			{
				if (data.lang)
					app.onCustomEvent("onServerLangReceived", data.lang);

			}
		);

		BX.addCustomEvent("onServerLangReceived", function (lang)
			{

				if (lang)
				{
					for (var k in lang)
					{
						BX.message[k] = lang[k];
					}
				}

			}
		);
	}, false);

	document.addEventListener("deviceready", function ()
	{
		if(typeof (BXMobileAppContext) != "undefined")
		{

			BX.addCustomEvent("onAppPaused", function ()
				{
					BXMobileAppContext.active = false;
				}
			);

			BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", function ()
				{
					BXMobileAppContext.active = true;
				}
			);

			BXMobileAppContext.isAppActive = function()
			{
				if(typeof (this.active) == "undefined" || !app.enableInVersion(16))
				{
					this.active = !BXMobileAppContext.isBackground();
				}

				return this.active;
			}
		}

		app.available = true;

		BX.addCustomEvent("onSessIdChanged", function (data)
			{
				BX.message.bitrix_sessid = data.sessid;
			}
		);

		BXMobileApp.addCustomEvent('onPageParamsChangedLegacy', function (params)
		{
			if (params.url != location.pathname+location.search)
				return false;

			BXMobileApp.UI.Page.params.set({data: params.data});
			BX.onCustomEvent('onPageParamsChanged', [params.data]);

			return true;
		});
	}, false);

	BX.mobileAjax = function (config)
	{
		console.warn("AJAX",config);
		var promise = new Promise(function (resolve, reject)
		{
			"use strict";

			config.onsuccess = (config.onsuccess ? config.onsuccess : function(){});
			config.xhr = new BMXMLHttpRequest();
			config.xhr.setRequestHeader("User-Agent", "Bitrix24/Janative");
			if(!config["method"])
				config["method"] = "GET";

			if (config.headers)
			{
				if(BX.type.isArray(config.headers))
				{
					config.headers.forEach(function(element){
						config.xhr.setRequestHeader(element.name, element.value);
					});
				}
				else
				{
					Object.keys(config.headers).forEach(function (headerName){
						config.xhr.setRequestHeader(headerName, config.headers[headerName])
					});
				}
			}

			if(config.files)
			{
				config.xhr.files = config.files;
			}

			if(typeof config.prepareData !== "undefined")
			{
				config.xhr.prepareData = config.prepareData;
			}
			else
			{
				config.xhr.prepareData = true;
			}

			if (config.timeout)
			{
				config.xhr.timeout = config.timeout;
			}
			if(BX.mobileAjax.debug)
			{
				console.log("Ajax request: "+ config.url);
			}
			config.xhr.open(config["method"], config["url"]);

			config.xhr.onreadystatechange = function ()
			{
				if (config.xhr.readyState === 4)
				{
					var isSuccess = BX.mobileAjax.xhrSuccess(config.xhr);
					console.log(config.xhr);
					if (isSuccess)
					{
						if (config.dataType && config.dataType === "json")
						{
							try {
								var json = BX.parseJSON(config.xhr.responseText);

								if(BX.mobileAjax.debug)
								{
									console.log("Ajax success: "+ config.xhr);
								}

								config.onsuccess(json);
								resolve(json);

							}
							catch (e)
							{
								var argument = {
									error: e,
									xhr: config.xhr
								};

								if(BX.mobileAjax.debug)
								{
									console.log("Ajax fail: ", argument);
								}

								if(typeof config.onfailure === "function")
								{
									config.onfailure(argument.error, argument.xhr);
								}
								else
								{
									reject(argument)
								}


							}
						}
						else {

							if(BX.mobileAjax.debug)
							{
								console.log("Ajax success: "+ config.xhr);
							}

							config.onsuccess(config.xhr.responseText);
							resolve(config.xhr.responseText);
						}

					}
					else {
						var argument = {
							error: new Error("XMLHTTPRequest error status", config.xhr.status),
							xhr: config.xhr
						};

						if(BX.mobileAjax.debug)
						{
							console.log("Ajax fail: ", argument);
						}

						if(typeof config.onfailure === "function")
						{
							config.onfailure(argument.error, argument.xhr);
						}
						else
						{
							reject(argument)
						}
					}
				}

			};
			BX.mobileAjax.instances[config.xhr.getUniqueId()] = config.xhr;
			config.xhr.send(config["data"]);

		});

		//to avoid exception which will be thrown if catch-handler will be not defined
		promise.catch(function(){});

		return promise;
	};

	BX.mobileAjax.debug = false;
	BX.mobileAjax.instances = {};
	BX.mobileAjax.xhrSuccess = function (xhr)
	{
		return (xhr.status >= 200 && xhr.status < 300) || xhr.status === 304 || xhr.status === 1223
	};

	BX.mobileAjax.prepareData = function (originalData, prefix)
	{
		var data = '';
		if (null !== originalData)
		{
			for (var paramName in originalData)
			{
				if (originalData.hasOwnProperty(paramName))
				{
					if (data.length > 0)
						data += '&';
					var name = encodeURIComponent(paramName);
					if (prefix)
						name = prefix + '[' + name + ']';
					if (typeof originalData[paramName] === 'object')
						data += BX.mobileAjax.prepareData(originalData[paramName], name);
					else
						data += name + '=' + encodeURIComponent(originalData[paramName]);
				}
			}
		}
		return data;
	};

	BX.mobileAjax.onreadystatechange = function(data){
		var id = data["id"];
		if(BX.mobileAjax.instances[id])
		{
			if (data["readyState"])
			{
				BX.mobileAjax.instances[id].readyState = data["readyState"];
			}

			if(data["readyState"] === 4)
			{
				BX.mobileAjax.instances[id].responseText = data["responseText"];
				console.timeEnd(data["id"]);
			}

			if(data["statusCode"])
			{
				BX.mobileAjax.instances[id].status = data["statusCode"];
			}
		}
		if(typeof(BX.mobileAjax.instances[id]["onreadystatechange"]) === "function")
			BX.mobileAjax.instances[id]["onreadystatechange"].call(BX.mobileAjax.instances[id],[]);

	};
	BX.mobileAjax.onload = function(data){
		var id = data["id"];
		if(BX.mobileAjax.instances[id] && BX.mobileAjax.instances[id]["onload"])
			BX.mobileAjax.instances[id]["onload"].call(BX.mobileAjax.instances[id],[data]);
	};
	BX.mobileAjax.onerror = function(data) {
		var id = data["id"];
		if(BX.mobileAjax.instances[id] && BX.mobileAjax.instances[id]["onerror"])
			BX.mobileAjax.instances[id]["onerror"].call(BX.mobileAjax.instances[id],[data.error])
	};
	BX.mobileAjax.send = function(object, data)
	{
		BX.mobileAjax.instances[object.getUniqueId()] = object;
		data["id"] = object.getUniqueId();
		console.time(data["id"]);
		Object.keys(BX.mobileAjax.preregistredCallbacks).forEach(function(event){ data[event] = BX.mobileAjax.preregistredCallbacks[event]});
		app.exec("sendAjax", data, false)
	};

	BX.mobileAjax.abort = function(object, data)
	{
		data["id"] = object.getUniqueId();
		app.exec("abortAjax", data, false)
	};

	BX.mobileAjax.registerCallbacks = function(){
		BX.mobileAjax.preregistredCallbacks = {
			onreadystatechange: app.RegisterCallBack(BX.mobileAjax.onreadystatechange),
			onload: app.RegisterCallBack(BX.mobileAjax.onload),
			onerror: app.RegisterCallBack(BX.mobileAjax.onerror),
		}

	};

	BX.mobileAjax.registerCallbacks();

	window.BMXMLHttpRequest = function(){
		this.id = "ajaxId"+Math.random();
		this.headers = {};
		this.files = [];
		this.prepareData = false;
	};
	BMXMLHttpRequest.prototype = {
		open:function(method, url){
			this.method = method;
			this.url = url;
		},
		setRequestHeader:function(name, value)
		{
			this.headers[name] = value;
		},
		send:function(requestBody){

			// if(requestBody instanceof FormData)
			// {
			// 	var object = {};
			// 	for(var pair of requestBody.entries()) {
			// 		console.log(pair);
			// 		object[pair[0]] = pair[1];
			// 	}
			//
			// 	requestBody = object;
			// }

			if(this.prepareData === true)
			{
				if(typeof requestBody === "object")
					requestBody = BX.mobileAjax.prepareData(requestBody);
			}

			var data = {
				headers: this.headers,
				body: requestBody,
				method: this.method,
				url: this.url,
				prepareData: this.prepareData,
				files: this.files
			};


			BX.mobileAjax.send(this, data);
		},
		abort:function () {
			BX.mobileAjax.abort(this, {});
		},
		onreadystatechange:null,
		onload:null,
		onerror:null,
		getUniqueId:function(){
			return this.id;
		}
	};

	BMAjaxWrapper = null;
	BX.ready(function() {

		if (BX.type.isUndefined(BX.Mobile))
		{
			return;
		}

		/**
		 * @deprecated since version 2.0
		 */
		window.MobileAjaxWrapper = function ()
		{
			this.instance = new BX.Mobile.Ajax();
		};

		MobileAjaxWrapper.prototype.Wrap = function (params)
		{
			var result = this.instance.instanceWrap(params);
			this.xhr = this.instance.xhr;
			return result;
		};

		MobileAjaxWrapper.prototype.runComponentAction = function(component, action, config, callbacks)
		{
			return this.instance.instanceRunComponentAction(component, action, config, callbacks);
		};

		MobileAjaxWrapper.prototype.runAction = function(action, config, callbacks)
		{
			return this.instance.instanceRunAction(action, config, callbacks);
		};

		MobileAjaxWrapper.prototype.OfflineAlert = function (callback)
		{
			navigator.notification.alert(BX.message('MobileAppOfflineMessage'), (callback || BX.DoNothing), BX.message('MobileAppOfflineTitle'));
		};

		BMAjaxWrapper = new MobileAjaxWrapper;
	});

	MobileNetworkStatus = function ()
	{
		this.offline = null;

		var _this = this;

		document.addEventListener("offline", function()
		{
			_this.offline = true;
		}, false);

		document.addEventListener("online", function()
		{
			_this.offline = false;
		}, false);

		document.addEventListener('DOMContentLoaded', function()
		{
			BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", function(params)
			{
				var networkState = navigator.network.connection.type;
				_this.offline = (networkState == Connection.UNKNOWN || networkState == Connection.NONE);
			});
		}, false);
	};

	BMNetworkStatus = new MobileNetworkStatus;

})();

(function ()
{
	function addListener(el, type, listener, useCapture)
	{
		if (el.addEventListener)
		{
			el.addEventListener(type, listener, useCapture);
			return {
				destroy: function ()
				{
					el.removeEventListener(type, listener, useCapture);
				}
			};
		} else
		{
			var handler = function (e)
			{
				listener.handleEvent(window.event, listener);
			}
			el.attachEvent('on' + type, handler);

			return {
				destroy: function ()
				{
					el.detachEvent('on' + type, handler);
				}
			};
		}
	}

	var isTouch = true;

	/* Construct the FastButton with a reference to the element and click handler. */
	this.FastButton = function (element, handler, useCapture)
	{
		// collect functions to call to cleanup events
		this.events = [];
		this.touchEvents = [];
		this.element = element;
		this.handler = handler;
		this.useCapture = useCapture;
		if (isTouch)
			this.events.push(addListener(element, 'touchstart', this, this.useCapture));
		this.events.push(addListener(element, 'click', this, this.useCapture));
	};

	/* Remove event handling when no longer needed for this button */
	this.FastButton.prototype.destroy = function ()
	{
		for (i = this.events.length - 1; i >= 0; i -= 1)
			this.events[i].destroy();
		this.events = this.touchEvents = this.element = this.handler = this.fastButton = null;
	};

	/* acts as an event dispatcher */
	this.FastButton.prototype.handleEvent = function (event)
	{
		switch (event.type)
		{
			case 'touchstart':
				this.onTouchStart(event);
				break;
			case 'touchmove':
				this.onTouchMove(event);
				break;
			case 'touchend':
				this.onClick(event);
				break;
			case 'click':
				this.onClick(event);
				break;
		}
	};


	this.FastButton.prototype.onTouchStart = function (event)
	{
		event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
		this.touchEvents.push(addListener(this.element, 'touchend', this, this.useCapture));
		this.touchEvents.push(addListener(document.body, 'touchmove', this, this.useCapture));
		this.startX = event.touches[0].clientX;
		this.startY = event.touches[0].clientY;
	};


	this.FastButton.prototype.onTouchMove = function (event)
	{
		if (Math.abs(event.touches[0].clientX - this.startX) > 10 || Math.abs(event.touches[0].clientY - this.startY) > 10)
		{
			this.reset(); //if he did, then cancel the touch event
		}
	};


	this.FastButton.prototype.onClick = function (event)
	{
		this.reset();
		var result = this.handler.call(this.element, event);

		if (result !== null)
		{
			event.preventDefault();
			event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
		}

		if (event.type == 'touchend')
			clickbuster.preventGhostClick(this.startX, this.startY);
		return result;
	};

	this.FastButton.prototype.reset = function ()
	{
		for (i = this.touchEvents.length - 1; i >= 0; i -= 1)
			this.touchEvents[i].destroy();
		this.touchEvents = [];
	};

	this.clickbuster = function ()
	{
	}

	this.clickbuster.preventGhostClick = function (x, y)
	{
		clickbuster.coordinates.push(x, y);
		window.setTimeout(clickbuster.pop, 2500);
	};

	this.clickbuster.pop = function ()
	{
		clickbuster.coordinates.splice(0, 2);
	};


	this.clickbuster.onClick = function (event)
	{
		for (var i = 0; i < clickbuster.coordinates.length; i += 2)
		{
			var x = clickbuster.coordinates[i];
			var y = clickbuster.coordinates[i + 1];
			if (Math.abs(event.clientX - x) < 25 && Math.abs(event.clientY - y) < 25)
			{
				event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
				event.preventDefault ? event.preventDefault() : (event.returnValue = false);
			}
		}
	};

	if (isTouch)
	{
		document.addEventListener('click', clickbuster.onClick, true);
		clickbuster.coordinates = [];
	}
})(this);


function ReadyDevice(func)
{
	if(app.available == true && typeof(func) == "function")
	{
		func();
	}
	else
	{
		document.addEventListener("deviceready", func, false);
	}

}
