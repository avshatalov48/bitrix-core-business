;(function()
{
	BX.namespace("BX.Call");

	if(BX.Call.Notification)
	{
		return;
	}

	var Events = {
		onButtonClick: "CallNotification::onButtonClick"
	};

	/**
	 *
	 * @param {Object} config
	 * @param {string} config.callerName
	 * @param {string} config.callerAvatar
	 * @param {bool} config.video
	 * @param {bool} config.hasCamera
	 * @param {function} config.onClose
	 * @param {function} config.onDestroy
	 * @param {function} config.onButtonClick
	 * @constructor
	 */
	BX.Call.Notification = function(config)
	{
		this.popup = null;
		this.window = null;

		this.callerAvatar = BX.type.isNotEmptyString(config.callerAvatar) ? config.callerAvatar : "";
		if(this.callerAvatar == "/bitrix/js/im/images/blank.gif")
		{
			this.callerAvatar = "";
		}

		this.callerName = config.callerName;
		this.video = config.video;
		this.hasCamera = config.hasCamera == true;

		this.callbacks = {
			onClose: BX.type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
			onDestroy: BX.type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
			onButtonClick: BX.type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
		};

		this._onContentButtonClickHandler = this._onContentButtonClick.bind(this);
		if(BX.desktop)
		{
			BX.desktop.addCustomEvent(Events.onButtonClick, this._onContentButtonClickHandler);
		}
	};

	BX.Call.Notification.prototype.show = function()
	{
		if (BX.desktop)
		{
			var params = {
				video: this.video,
				hasCamera: this.hasCamera,
				callerAvatar: this.callerAvatar,
				callerName: this.callerName
			};

			if(this.window)
			{
				this.window.BXDesktopWindow.ExecuteCommand("show");
			}
			else
			{
				this.window = BXDesktopSystem.ExecuteCommand(
					'topmost.show.html',
					BX.desktop.getHtmlPage("", "window.callNotification = new BX.Call.NotificationContent(" + JSON.stringify(params) + "); window.callNotification.showInDesktop();")
				);
			}
		}
		else
		{
			this.content = new BX.Call.NotificationContent({
				video: this.video,
				hasCamera: this.hasCamera,
				callerAvatar: this.callerAvatar,
				callerName: this.callerName,
				onClose: this.callbacks.onClose,
				onDestroy: this.callbacks.onDestroy,
				onButtonClick: this.callbacks.onButtonClick
			});
			this.createPopup(this.content.render());
			this.popup.show();
		}
	};

	BX.Call.Notification.prototype.createPopup = function(content)
	{
		this.popup = new BX.PopupWindow("bx-messenger-call-notify", null, {
			content: content,
			closeIcon: false,
			noAllPaddings: true,
			zIndex: BX.MessengerCommon.getDefaultZIndex() + 200,
			offsetLeft: 0,
			offsetTop: 0,
			closeByEsc: false,
			draggable: {restrict: false},
			overlay: {backgroundColor: 'black', opacity: 30},
			events: {
				onPopupClose: function()
				{
					this.callbacks.onClose();
				}.bind(this),
				onPopupDestroy: function()
				{
					this.popup = null;
				}.bind(this)
			}
		});
	};

	BX.Call.Notification.prototype.close = function()
	{
		if(this.popup)
		{
			this.popup.close();
		}
		if(this.window)
		{
			this.window.BXDesktopWindow.ExecuteCommand("hide");
		}
		this.callbacks.onClose();
	};

	BX.Call.Notification.prototype.destroy = function()
	{
		if(this.popup)
		{
			this.popup.destroy();
			this.popup = null;
		}
		if(this.window)
		{
			this.window.BXDesktopWindow.ExecuteCommand("close");
			this.window = null;
		}

		if(BX.desktop)
		{
			BX.desktop.removeCustomEvents(Events.onButtonClick);
		}
		this.callbacks.onDestroy();
	};

	BX.Call.Notification.prototype._onContentButtonClick = function(e)
	{
		this.callbacks.onButtonClick(e);
	};

	BX.Call.NotificationContent = function(config)
	{
		this.video = config.video;
		this.hasCamera = config.hasCamera;
		this.callerAvatar = config.callerAvatar;
		this.callerName = config.callerName;

		this.elements = {
			root: null,
			avatar: null
		};

		this.callbacks = {
			onClose: BX.type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
			onDestroy: BX.type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
			onButtonClick: BX.type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
		};
	};

	BX.Call.NotificationContent.prototype.render = function()
	{
		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-call-window"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-call-window-body"},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-call-window-photo"},
							children: [
								BX.create("div", {
									props: {className: "bx-messenger-call-window-photo-left"},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-call-window-photo-block"},
											children: [
												this.elements.avatar = BX.create("img", {
													props: {
														className: "bx-messenger-call-window-overlay-photo-img",
														src: this.callerAvatar || "/bitrix/js/im/images/hidef-avatar-v3.png"
													},
													style: {
														backgroundColor: "#df532d"
													}
												}),
											]
										}),
									]
								}),
							]
						}),
						BX.create("div", {
							props: {className: "bx-messenger-call-window-info"},
							children: [
								BX.create("div", {
									props: {className: "bx-messenger-call-window-title"},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-call-window-title-block"},
											children: [
												document.createTextNode(this.video ? BX.message("IM_M_VIDEO_CALL_FROM") : BX.message("IM_M_CALL_FROM")),
												BX.create("span", {
													props: {className: "bx-messenger-call-overlay-title-caller"},
													text: BX.util.htmlspecialcharsback(this.callerName)
												})
											]
										}),
									]
								}),
								BX.create("div", {
									props: {className: "bx-messenger-call-window-buttons"},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-call-window-buttons-block"},
											children: [
												BX.create("button", {
													props: {className: "ui-btn ui-btn-sm ui-btn-round ui-btn-primary-dark ui-btn-icon-camera bx-messenger-call-window-button" + (!this.hasCamera ? " ui-btn-disabled" : "")},
													text: BX.message("IM_M_CALL_BTN_ANSWER_VIDEO"),
													events: {click: this._onAnswerWithVideoButtonClick.bind(this)}
												}),
												BX.create("button", {
													props: {className: "ui-btn ui-btn-sm ui-btn-round ui-btn-primary-dark ui-btn-icon-phone-up bx-messenger-call-window-button"},
													text: BX.message("IM_M_CALL_BTN_ANSWER"),
													events: {click: this._onAnswerButtonClick.bind(this)}
												}),
												BX.create("button", {
													props: {className: "ui-btn ui-btn-sm ui-btn-round ui-btn-danger-dark ui-btn-icon-phone-down"},
													text: BX.message("IM_M_CALL_BTN_DECLINE"),
													events: {click: this._onDeclineButtonClick.bind(this)}
												}),
											]
										}),
									]
								}),
							]
						}),
					]
				})
			]
		});

		return this.elements.root;
	};

	BX.Call.NotificationContent.prototype.showInDesktop = function()
	{
		this.render();
		document.body.appendChild(this.elements.root);
		BX.desktop.setWindowPosition({X:STP_CENTER, Y:STP_VCENTER, Width: 635, Height: 125});
	};

	BX.Call.NotificationContent.prototype._onAnswerButtonClick = function(e)
	{
		if(BX.desktop)
		{
			BX.desktop.onCustomEvent("main", Events.onButtonClick, [{
				button: 'answer',
				video: false
			}]);
		}
		else
		{
			this.callbacks.onButtonClick({
				button: 'answer',
				video: false
			});
		}
	};

	BX.Call.NotificationContent.prototype._onAnswerWithVideoButtonClick = function(e)
	{
		if(!this.hasCamera)
		{
			return;
		}
		if(BX.desktop)
		{
			BX.desktop.onCustomEvent("main", Events.onButtonClick, [{
				button: 'answer',
				video: true
			}]);
		}
		else
		{
			this.callbacks.onButtonClick({
				button: 'answer',
				video: true
			});
		}
	};

	BX.Call.NotificationContent.prototype._onDeclineButtonClick = function(e)
	{
		if(BX.desktop)
		{
			BX.desktop.onCustomEvent("main", Events.onButtonClick, [{
				button: 'decline',
			}]);
		}
		else
		{
			this.callbacks.onButtonClick({
				button: 'decline'
			});
		}
	};

})();