;(function () {
	BX.namespace('BX.Call');

	if (BX.Call.FloatingScreenShare)
	{
		return;
	}

	var Events = {
		onBackToCallClick: "FloatingScreenshare::onBackToCallClick",
		onStopSharingClick: "FloatingScreenshare::onStopSharingClick",
		onChangeScreenClick: "FloatingScreenshare::onChangeScreenClick"
	};

	var POPUP_WIDTH = 291;
	var POPUP_HEIGHT = 81;
	var POPUP_OFFSET_X = 80;
	var POPUP_OFFSET_Y = 80;

	/**
	 *
	 * @param {object} config
	 * @constructor
	 */
	BX.Call.FloatingScreenShare = function (config)
	{
		if(typeof(config) !== "object")
		{
			config = {};
		}

		this.desktop = config.desktop || BX.desktop;
		this.darkMode = config.darkMode || false;

		this.window = null;
		this.sharedWindowX = null;
		this.sharedWindowY = null;
		this.sharedWindowHeight = null;
		this.sharedWindowWidth = null;
		this.title = '';
		this.app = '';

		this.screens = [];
		this.screenToUse = null;

		this.callbacks = {
			onBackToCallClick: BX.type.isFunction(config.onBackToCallClick) ? config.onBackToCallClick : BX.DoNothing,
			onStopSharingClick: BX.type.isFunction(config.onStopSharingClick) ? config.onStopSharingClick : BX.DoNothing,
			onChangeScreenClick: BX.type.isFunction(config.onChangeScreenClick) ? config.onChangeScreenClick : BX.DoNothing,
		};

		this._onBackToCallClickHandler = this._onBackToCallClick.bind(this);
		this._onStopSharingClickHandler = this._onStopSharingClick.bind(this);
		this._onChangeScreenClickHandler = this._onChangeScreenClick.bind(this);
		this.bindEventHandlers();
	};

	BX.Call.FloatingScreenShare.prototype =
		{
			bindEventHandlers: function ()
			{
				this.desktop.addCustomEvent(Events.onBackToCallClick, this._onBackToCallClickHandler);
				this.desktop.addCustomEvent(Events.onStopSharingClick, this._onStopSharingClickHandler);
				this.desktop.addCustomEvent(Events.onChangeScreenClick, this._onChangeScreenClickHandler);
			},

			saveExistingScreens: function() {
				return new Promise(function(resolve, reject)
				{
					if (this.screens.length > 0)
					{
						return resolve();
					}

					BXDesktopSystem.ListScreenMedia(function(result)
					{
						result.forEach(function(item){
							if (item.id.slice(0, 6) === 'screen')
							{
								this.screens.push({
									id: item.id,
									x: item.x,
									y: item.y,
									width: item.width,
									height: item.height
								});
							}
						}, this);

						return resolve();
					}.bind(this));
				}.bind(this));
			},

			_onBackToCallClick: function()
			{
				this.callbacks.onBackToCallClick();
			},

			_onStopSharingClick: function()
			{
				this.close();
				this.callbacks.onStopSharingClick();
			},

			_onChangeScreenClick: function()
			{
				this.callbacks.onChangeScreenClick();
			},

			setSharingData: function(data)
			{
				return this.saveExistingScreens().then(function()
				{
					this.sharedWindowX = data.x + 10;
					this.sharedWindowY = data.y + 10;
					this.sharedWindowWidth = data.width;
					this.sharedWindowHeight = data.height;
					this.title = data.title;
					this.app = data.app;

					for (var i = 0; i < this.screens.length; i++)
					{
						if (
							this.sharedWindowX >= this.screens[i].x &&
							this.sharedWindowX <= (this.screens[i].x + this.screens[i].width) &&
							this.sharedWindowY >= this.screens[i].y &&
							this.sharedWindowY <= (this.screens[i].y + this.screens[i].height)
						)
						{
							this.screenToUse = this.screens[i];
							break;
						}
					}

					if (!this.screenToUse && this.screens.length > 0)
					{
						this.screenToUse = this.screens[0];
					}
				}.bind(this)).catch(function(error)
				{
					console.log('save existing screens error', error);
				}.bind(this));
			},

			show: function ()
			{
				if (!this.desktop)
				{
					return;
				}

				if(this.window)
				{
					this.window.BXDesktopWindow.ExecuteCommand("show");
				}
				else
				{
					var params = {
						title: this.title,
						app: this.app,
						sharedWindowX: this.sharedWindowX,
						sharedWindowY: this.sharedWindowY,
						sharedWindowWidth: this.sharedWindowWidth,
						sharedWindowHeight: this.sharedWindowHeight,
						screenToUse: this.screenToUse,
						darkMode: this.darkMode
					};

					this.window = BXDesktopSystem.ExecuteCommand(
						'topmost.show.html',
						this.desktop.getHtmlPage(
							"",
							"window.FSSC = new BX.Call.FloatingScreenShareContent(" + JSON.stringify(params) + ");"
						)
					);
				}
			},

			hide: function ()
			{
				if (!this.window || !this.window.document)
				{
					return false;
				}

				this.window.BXDesktopWindow.ExecuteCommand("hide");
			},

			close: function ()
			{
				if (!this.window || !this.window.document)
				{
					return false;
				}

				this.window.BXDesktopWindow.ExecuteCommand("close");
				this.window = null;
				this.visible = false;
			},

			destroy: function ()
			{
				if(this.window)
				{
					this.window.BXDesktopWindow.ExecuteCommand("close");
					this.window = null;
				}

				this.desktop.removeCustomEvents(Events.onBackToCallClick);
				this.desktop.removeCustomEvents(Events.onStopSharingClick);
				this.desktop.removeCustomEvents(Events.onChangeScreenClick);
			}
		};

	BX.Call.FloatingScreenShareContent = function (config)
	{
		this.title = config.title || '';
		this.app = config.app || '';
		this.sharedWindowX = config.sharedWindowX || 0;
		this.sharedWindowY = config.sharedWindowY || 0;
		this.sharedWindowHeight = config.sharedWindowHeight || 0;
		this.sharedWindowWidth = config.sharedWindowWidth || 0;
		this.screenToUse = config.screenToUse || null;
		this.darkMode = config.darkMode || false;

		this.elements = {
			container: null
		};

		this.render();
		this.adjustWindow(POPUP_WIDTH, POPUP_HEIGHT);
	};

	BX.Call.FloatingScreenShareContent.prototype = {
		render: function () {
			var title;
			if (this.app)
			{
				title = this.app + ' - ' + this.title;
			}
			else
			{
				title = this.title;
			}

			this.elements.container = BX.create("div", {
				props: {className: 'bx-messenger-call-floating-screenshare-wrap' + (this.darkMode ? ' dark-mode' : '')},
				children: [
					BX.create("div", {
						props: {className: 'bx-messenger-call-floating-screenshare-top'},
						children: [
							BX.create("div", {
								props: {className: 'bx-messenger-call-floating-screenshare-top-icon'}
							}),
							BX.create("div", {
								props: {className: 'bx-messenger-call-floating-screenshare-top-text', title: title},
								text: title
							}),
						]
					}),
					BX.create("div", {
						props: {className: 'bx-messenger-call-floating-screenshare-bottom'},
						children: [
							BX.create("div", {
								props: {className: 'bx-messenger-call-floating-screenshare-bottom-left'},
								children: [
									BX.create("div", {
										props: {className: 'bx-messenger-call-floating-screenshare-back-icon'}
									}),
									BX.create("div", {
										props: {className: 'bx-messenger-call-floating-screenshare-back-text'},
										text: BX.message('IM_M_CALL_SCREENSHARE_BACK_TO_CALL')
									})
								],
								events: {
									click: this.onBackToCallClick.bind(this)
								}
							}),
							BX.create("div", {
								props: {className: 'bx-messenger-call-floating-screenshare-bottom-center'},
								children: [
									BX.create("div", {
										props: {className: 'bx-messenger-call-floating-screenshare-change-screen-icon'}
									}),
									BX.create("div", {
										props: {className: 'bx-messenger-call-floating-screenshare-change-screen-text'},
										text: BX.message('IM_M_CALL_SCREENSHARE_CHANGE_SCREEN')
									})
								],
								events: {
									click: this.onChangeScreenClick.bind(this)
								}
							}),
							BX.create("div", {
								props: {className: 'bx-messenger-call-floating-screenshare-bottom-right'},
								children: [
									BX.create("div", {
										props: {className: 'bx-messenger-call-floating-screenshare-stop-icon'}
									}),
									BX.create("div", {
										props: {className: 'bx-messenger-call-floating-screenshare-stop-text'},
										text: BX.message('IM_M_CALL_SCREENSHARE_STOP')
									}),
								],
								events: {
									click: this.onStopSharingClick.bind(this)
								}
							})
						]
					})
				]
			});

			document.body.appendChild(this.elements.container);
			document.body.classList.add('bx-messenger-call-floating-screenshare');
		},

		onBackToCallClick: function()
		{
			this.dispatchEvent(Events.onBackToCallClick, []);
		},

		onChangeScreenClick: function()
		{
			this.dispatchEvent(Events.onChangeScreenClick, []);
		},

		onStopSharingClick: function()
		{
			this.dispatchEvent(Events.onStopSharingClick, []);
		},

		adjustWindow: function (width, height)
		{
			if (!this.screenToUse)
			{
				return;
			}

			var blockOffset = 22;
			var popupPadding = 22;
			var leftBlockWidth = document.querySelector('.bx-messenger-call-floating-screenshare-bottom-left').scrollWidth;
			var centerBlockWidth = document.querySelector('.bx-messenger-call-floating-screenshare-bottom-center').scrollWidth;
			var rightBlockWidth = document.querySelector('.bx-messenger-call-floating-screenshare-bottom-right').scrollWidth;
			var fullWidth = leftBlockWidth + centerBlockWidth + rightBlockWidth + (2*blockOffset) + (2*popupPadding);
			if (fullWidth > POPUP_WIDTH)
			{
				width = fullWidth;
			}

			this.elements.container.style.width = width +"px";
			this.elements.container.style.height = height +"px";

			BXDesktopWindow.SetProperty("minClientSize",{Width: width, Height: height});
			BXDesktopWindow.SetProperty("resizable", false);
			BXDesktopWindow.SetProperty("closable", false);
			BXDesktopWindow.SetProperty("title", BX.message('IM_M_CALL_SCREENSHARE_TITLE'));

			BXDesktopWindow.SetProperty("position", {
				X: this.screenToUse.x + this.screenToUse.width - width - POPUP_OFFSET_X,
				Y: this.screenToUse.y + POPUP_OFFSET_Y,
				Width: width,
				Height: height,
				Mode: STP_FRONT
			});
		},

		dispatchEvent: function(name, params)
		{
			var convertedParams = {};
			for (var i = 0; i < params.length; i++)
			{
				convertedParams[i] = params[i];
			}

			var mainWindow = opener? opener: top;
			mainWindow.BXWindows.forEach(function(windowItem){
				if (
					windowItem &&
					windowItem.name !== '' &&
					windowItem.BXDesktopWindow &&
					windowItem.BXDesktopWindow.DispatchCustomEvent
				)
				{
					windowItem.BXDesktopWindow.DispatchCustomEvent(name, convertedParams);
				}
			}, this);
			mainWindow.BXDesktopWindow.DispatchCustomEvent(name, convertedParams);
		}
	};

})();