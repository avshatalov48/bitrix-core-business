;(function()
{
	BX.namespace("BX.Call");

	if(BX.Call.NotificationConference)
	{
		return;
	}

	var Events = {
		onButtonClick: "ConferenceNotification::onButtonClick"
	};

	/**
	 *
	 * @param {Object} config
	 * @param {string} config.callerName
	 * @param {string} config.callerAvatar
	 * @param {function} config.onClose
	 * @param {function} config.onDestroy
	 * @param {function} config.onButtonClick
	 * @constructor
	 */
	BX.Call.NotificationConference = function(config)
	{
		this.popup = null;
		this.window = null;

		this.callerAvatar = BX.type.isNotEmptyString(config.callerAvatar) ? config.callerAvatar : "";
		if(this.callerAvatar == "/bitrix/js/im/images/blank.gif")
		{
			this.callerAvatar = "";
		}

		this.callerName = config.callerName;
		this.callerColor = config.callerColor;

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

	BX.Call.NotificationConference.prototype.show = function()
	{
		if (BX.desktop)
		{
			var params = {
				callerAvatar: this.callerAvatar,
				callerName: this.callerName,
				callerColor: this.callerColor
			};

			if(this.window)
			{
				this.window.BXDesktopWindow.ExecuteCommand("show");
			}
			else
			{
				this.window = BXDesktopSystem.ExecuteCommand(
					'topmost.show.html',
					BX.desktop.getHtmlPage("", "window.conferenceNotification = new BX.Call.NotificationConferenceContent(" + JSON.stringify(params) + "); window.conferenceNotification.showInDesktop();")
				);
			}
		}
		else
		{
			this.content = new BX.Call.NotificationConferenceContent({
				callerAvatar: this.callerAvatar,
				callerName: this.callerName,
				callerColor: this.callerColor,
				onClose: this.callbacks.onClose,
				onDestroy: this.callbacks.onDestroy,
				onButtonClick: this.callbacks.onButtonClick
			});
			this.createPopup(this.content.render());
			this.popup.show();
		}
	};

	BX.Call.NotificationConference.prototype.createPopup = function(content)
	{
		this.popup = new BX.PopupWindow("bx-messenger-call-notify", null, {
			targetContainer: document.body,
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

	BX.Call.NotificationConference.prototype.close = function()
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

	BX.Call.NotificationConference.prototype.destroy = function()
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

	BX.Call.NotificationConference.prototype._onContentButtonClick = function(e)
	{
		this.callbacks.onButtonClick(e);
	};

	BX.Call.NotificationConferenceContent = function(config)
	{
		this.callerAvatar = config.callerAvatar || '';
		this.callerName = config.callerName || BX.message('IM_CL_USER');
		this.callerColor = config.callerColor || '#525252';

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

	BX.Call.NotificationConferenceContent.prototype.render = function()
	{
		var backgroundImage = this.callerAvatar || '/bitrix/js/im/images/default-call-background.png';
		var avatarImageStyles;
		if (this.callerAvatar)
		{
			avatarImageStyles = {
				backgroundImage: "url('"+this.callerAvatar+"')",
				backgroundColor: '#fff',
				backgroundSize: 'cover',
			}
		}
		else
		{
			avatarImageStyles = {
				backgroundImage: "url('"+(this.callerAvatar || "/bitrix/js/im/images/default-avatar-videoconf-big.png")+"')",
				backgroundColor: this.callerColor,
				backgroundSize: '80px',
    			backgroundRepeat: 'no-repeat',
    			backgroundPosition: 'center center',
			}
		}

		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-call-window"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-call-window-background"},
					style: {
						backgroundImage: 'url(' + backgroundImage + ')'
					},
				}),
				BX.create("div", {
					props: {className: "bx-messenger-call-window-background-blur"}
				}),
				BX.create("div", {
					props: {className: "bx-messenger-call-window-background-gradient"},
					style: {
						backgroundImage: "url('/bitrix/js/im/images/call-background-gradient.png')"
					}
				}),
				BX.create("div", {
					props: {className: "bx-messenger-call-window-bottom-background"}
				}),
				BX.create("div", {
					props: {className: "bx-messenger-call-window-body"},
					children: [
						BX.create("div", {
							props: { className: "bx-messenger-call-window-top" },
							children: [
								BX.create("div", {
									props: {className: "bx-messenger-call-window-photo"},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-call-window-photo-left"},
											children: [
												this.elements.avatar = BX.create("div", {
													props: {className: "bx-messenger-call-window-photo-block"},
													style: avatarImageStyles,
												}),
											]
										}),
									]
								}),
								BX.create("div", {
									props: {className: "bx-messenger-call-window-title"},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-call-window-title-block"},
											children: [
												BX.create("div", {
													props: {className: "bx-messenger-call-overlay-title-caller-prefix"},
													text: BX.message("IM_M_VIDEO_CALL_FROM")
												}),
												BX.create("div", {
													props: {className: "bx-messenger-call-overlay-title-caller"},
													text: BX.util.htmlspecialcharsback(this.callerName)
												})
											]
										}),
									]
								}),
							]
						}),
						BX.create("div", {
							props: { className: "bx-messenger-call-window-bottom" },
							children: [
								BX.create("div", {
									props: {className: "bx-messenger-call-window-buttons"},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-call-window-buttons-block"},
											children: [
												BX.create("div", {
													props: {className: "bx-messenger-call-window-button"},
													children: [
														BX.create("div", {
															props: {className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-camera"}
														}),
														BX.create("div", {
															props: {className: "bx-messenger-call-window-button-text"},
															text: BX.message("IM_M_CALL_BTN_ANSWER_CONFERENCE"),
														}),
													],
													events: {click: this._onAnswerConferenceButtonClick.bind(this)}
												}),
												BX.create("div", {
													props: {className: "bx-messenger-call-window-button bx-messenger-call-window-button-danger"},
													children: [
														BX.create("div", {
															props: {className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-phone-down"}
														}),
														BX.create("div", {
															props: {className: "bx-messenger-call-window-button-text"},
															text: BX.message("IM_M_CALL_BTN_SKIP_CONFERENCE"),
														}),
													],
													events: {click: this._onSkipConferenceButtonClick.bind(this)}
												}),
											]
										}),
									]
								}),
							]
						})
					]
				})
			]
		});

		return this.elements.root;
	};

	BX.Call.NotificationConferenceContent.prototype.showInDesktop = function()
	{
		this.render();
		document.body.appendChild(this.elements.root);
		BX.desktop.setWindowPosition({X:STP_CENTER, Y:STP_VCENTER, Width: 351, Height: 510});
	};

	BX.Call.NotificationConferenceContent.prototype._onAnswerConferenceButtonClick = function(e)
	{
		if(BX.desktop)
		{
			BXDesktopWindow.ExecuteCommand("close");
			BX.desktop.onCustomEvent("main", Events.onButtonClick, [{
				button: 'answerConference',
			}]);
		}
		else
		{
			this.callbacks.onButtonClick({
				button: 'answerConference',
			});
		}
	};

	BX.Call.NotificationConferenceContent.prototype._onSkipConferenceButtonClick = function(e)
	{
		if(BX.desktop)
		{
			BXDesktopWindow.ExecuteCommand("close");
			BX.desktop.onCustomEvent("main", Events.onButtonClick, [{
				button: 'skipConference',
			}]);
		}
		else
		{
			this.callbacks.onButtonClick({
				button: 'skipConference'
			});
		}
	};

})();