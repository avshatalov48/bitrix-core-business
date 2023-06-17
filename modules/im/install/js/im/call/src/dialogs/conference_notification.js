import {Dom, Text, Type} from 'main.core';
import {Popup} from 'main.popup';
import Util from '../util'

const Events = {
	onButtonClick: "ConferenceNotification::onButtonClick"
};

/**
 *
 * @param {Object} config
 * @param {string} config.callerName
 * @param {string} config.callerAvatar
 * @param {number} config.zIndex
 * @param {function} config.onClose
 * @param {function} config.onDestroy
 * @param {function} config.onButtonClick
 * @constructor
 */
export class ConferenceNotifications
{
	constructor(config)
	{
		this.popup = null;
		this.window = null;

		this.callerAvatar = Type.isStringFilled(config.callerAvatar) ? config.callerAvatar : "";
		this.zIndex = config.zIndex;
		if (Util.isAvatarBlank(this.callerAvatar))
		{
			this.callerAvatar = "";
		}

		this.callerName = config.callerName;
		this.callerColor = config.callerColor;

		this.callbacks = {
			onClose: Type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
			onDestroy: Type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
			onButtonClick: Type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
		};

		this._onContentButtonClickHandler = this._onContentButtonClick.bind(this);
		if (BX.desktop)
		{
			BX.desktop.addCustomEvent(Events.onButtonClick, this._onContentButtonClickHandler);
		}
	}
	;

	show()
	{
		if (BX.desktop)
		{
			var params = {
				callerAvatar: this.callerAvatar,
				callerName: this.callerName,
				callerColor: this.callerColor
			};

			if (this.window)
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
			this.content = new NotificationConferenceContent({
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
	}
	;

	createPopup(content)
	{
		this.popup = new Popup({
			id: "bx-messenger-call-notify",
			targetContainer: document.body,
			content: content,
			closeIcon: false,
			noAllPaddings: true,
			zIndex: this.zIndex,
			offsetLeft: 0,
			offsetTop: 0,
			closeByEsc: false,
			draggable: {restrict: false},
			overlay: {backgroundColor: 'black', opacity: 30},
			events: {
				onPopupClose: function ()
				{
					this.callbacks.onClose();
				}.bind(this),
				onPopupDestroy: function ()
				{
					this.popup = null;
				}.bind(this)
			}
		});
	}
	;

	close()
	{
		if (this.popup)
		{
			this.popup.close();
		}
		if (this.window)
		{
			this.window.BXDesktopWindow.ExecuteCommand("hide");
		}
		this.callbacks.onClose();
	}
	;

	destroy()
	{
		if (this.popup)
		{
			this.popup.destroy();
			this.popup = null;
		}
		if (this.window)
		{
			this.window.BXDesktopWindow.ExecuteCommand("close");
			this.window = null;
		}

		if (BX.desktop)
		{
			BX.desktop.removeCustomEvents(Events.onButtonClick);
		}
		this.callbacks.onDestroy();
	}
	;

	_onContentButtonClick(e)
	{
		this.callbacks.onButtonClick(e);
	}
	;
}

export class NotificationConferenceContent
{
	constructor(config)
	{
		this.callerAvatar = config.callerAvatar || '';
		this.callerName = config.callerName || BX.message('IM_CL_USER');
		this.callerColor = config.callerColor || '#525252';

		this.elements = {
			root: null,
			avatar: null
		};

		this.callbacks = {
			onClose: Type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
			onDestroy: Type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
			onButtonClick: Type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing
		};
	};

	render()
	{
		var backgroundImage = this.callerAvatar || '/bitrix/js/im/images/default-call-background.png';
		var avatarImageStyles;
		if (this.callerAvatar)
		{
			avatarImageStyles = {
				backgroundImage: "url('" + this.callerAvatar + "')",
				backgroundColor: '#fff',
				backgroundSize: 'cover',
			}
		}
		else
		{
			avatarImageStyles = {
				backgroundImage: "url('" + (this.callerAvatar || "/bitrix/js/im/images/default-avatar-videoconf-big.png") + "')",
				backgroundColor: this.callerColor,
				backgroundSize: '80px',
				backgroundRepeat: 'no-repeat',
				backgroundPosition: 'center center',
			}
		}

		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-call-window"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-call-window-background"},
					style: {
						backgroundImage: 'url(' + backgroundImage + ')'
					},
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-call-window-background-blur"}
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-call-window-background-gradient"},
					style: {
						backgroundImage: "url('/bitrix/js/im/images/call-background-gradient.png')"
					}
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-call-window-bottom-background"}
				}),
				Dom.create("div", {
					props: {className: "bx-messenger-call-window-body"},
					children: [
						Dom.create("div", {
							props: {className: "bx-messenger-call-window-top"},
							children: [
								Dom.create("div", {
									props: {className: "bx-messenger-call-window-photo"},
									children: [
										Dom.create("div", {
											props: {className: "bx-messenger-call-window-photo-left"},
											children: [
												this.elements.avatar = Dom.create("div", {
													props: {className: "bx-messenger-call-window-photo-block"},
													style: avatarImageStyles,
												}),
											]
										}),
									]
								}),
								Dom.create("div", {
									props: {className: "bx-messenger-call-window-title"},
									children: [
										Dom.create("div", {
											props: {className: "bx-messenger-call-window-title-block"},
											children: [
												Dom.create("div", {
													props: {className: "bx-messenger-call-overlay-title-caller-prefix"},
													text: BX.message("IM_M_VIDEO_CALL_FROM")
												}),
												Dom.create("div", {
													text: Text.encode(this.callerName),
													props: {className: "bx-messenger-call-overlay-title-caller"}
												})
											]
										}),
									]
								}),
							]
						}),
						Dom.create("div", {
							props: {className: "bx-messenger-call-window-bottom"},
							children: [
								Dom.create("div", {
									props: {className: "bx-messenger-call-window-buttons"},
									children: [
										Dom.create("div", {
											props: {className: "bx-messenger-call-window-buttons-block"},
											children: [
												Dom.create("div", {
													props: {className: "bx-messenger-call-window-button"},
													children: [
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-camera"}
														}),
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-text"},
															text: BX.message("IM_M_CALL_BTN_ANSWER_CONFERENCE"),
														}),
													],
													events: {click: this._onAnswerConferenceButtonClick.bind(this)}
												}),
												Dom.create("div", {
													props: {className: "bx-messenger-call-window-button bx-messenger-call-window-button-danger"},
													children: [
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-phone-down"}
														}),
														Dom.create("div", {
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

	showInDesktop()
	{
		this.render();
		document.body.appendChild(this.elements.root);
		BX.desktop.setWindowPosition({X: STP_CENTER, Y: STP_VCENTER, Width: 351, Height: 510});
	};

	_onAnswerConferenceButtonClick(e)
	{
		if (BX.desktop)
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

	_onSkipConferenceButtonClick(e)
	{
		if (BX.desktop)
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

}