import {Dom, Text, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Popup} from 'main.popup';

import {DesktopApi} from 'im.v2.lib.desktop-api';

import Util from '../util';

const Events = {
	onClose: 'onClose',
	onDestroy: 'onDestroy',
	onButtonClick: 'onButtonClick',
};

const InternalEvents = {
	setHasCamera: "CallNotification::setHasCamera",
	contentReady: "CallNotification::contentReady",
	onButtonClick: "CallNotification::onButtonClick",
}

export type IncomingNotificationParams = {
	callerName: string,
	callerAvatar: string,
	callerType: string,
	callerColor: string,
	video: boolean,
	hasCamera: boolean,
	zIndex: number,
	onClose: () => void,
	onDestroy: () => void,
	onButtonClick: () => void,
}

export class IncomingNotification extends EventEmitter
{
	static Events = Events;

	constructor(config: IncomingNotificationParams)
	{
		super();
		this.setEventNamespace('BX.Call.IncomingNotification')

		this.popup = null;
		this.window = null;

		this.callerAvatar = Type.isStringFilled(config.callerAvatar) ? config.callerAvatar : "";
		if (Util.isAvatarBlank(this.callerAvatar))
		{
			this.callerAvatar = "";
		}

		this.callerName = config.callerName;
		this.callerType = config.callerType;
		this.callerColor = config.callerColor;
		this.video = config.video;
		this.hasCamera = config.hasCamera === true;
		this.zIndex = config.zIndex;
		this.contentReady = false;
		this.postponedEvents = [];

		this.#subscribeEvents(config);
		if (DesktopApi.isDesktop())
		{
			this.onButtonClickHandler = this.#onButtonClick.bind(this);
			this.onContentReadyHandler = this.#onContentReady.bind(this);
			DesktopApi.subscribe(InternalEvents.onButtonClick, this.onButtonClickHandler);
			DesktopApi.subscribe(InternalEvents.contentReady, this.onContentReadyHandler);
		}
	};

	#subscribeEvents(config)
	{
		const eventKeys = Object.keys(Events);
		for (let eventName of eventKeys)
		{
			if (Type.isFunction(config[eventName]))
			{
				this.subscribe(Events[eventName], config[eventName])
			}
		}
	}

	show()
	{
		console.log('incoming notification : SHOW');
		if (DesktopApi.isDesktop())
		{
			console.log('incoming notification : ISDESKTOP');
			const params = {
				video: this.video,
				hasCamera: this.hasCamera,
				callerAvatar: this.callerAvatar,
				callerName: this.callerName,
				callerType: this.callerType,
				callerColor: this.callerColor
			};

			if (this.window)
			{
				this.window.BXDesktopWindow.ExecuteCommand("show");
			}
			else
			{
				const js = `
					window.callNotification = new BX.Call.IncomingNotificationContent(${JSON.stringify(params)});
					window.callNotification.showInDesktop();
				`;
				const htmlContent = DesktopApi.prepareHtml("", js);
				this.window = DesktopApi.createTopmostWindow(htmlContent);
			}
		}
		else
		{
			console.log('incoming notification : ISNOTDESKTOP');
			this.content = new IncomingNotificationContent({
				video: this.video,
				hasCamera: this.hasCamera,
				callerAvatar: this.callerAvatar,
				callerName: this.callerName,
				callerType: this.callerType,
				callerColor: this.callerColor,
				onClose: () => this.emit(Events.onClose),
				onDestroy: () => this.emit(Events.onDestroy),
				onButtonClick: (e) => this.emit(Events.onButtonClick, Object.assign({}, e.data)),
			});
			this.createPopup(this.content.render());
			this.popup.show();
		}
	};

	createPopup(content)
	{
		this.popup = new Popup({
			id: "bx-messenger-call-notify",
			bindElement: null,
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
				onPopupClose: () => this.emit(Events.onClose),
				onPopupDestroy: () => this.popup = null,
			}
		});
	};

	setHasCamera(hasCamera)
	{
		if (this.window)
		{
			// desktop; send event to the window
			if (this.contentReady)
			{
				DesktopApi.emit(InternalEvents.setHasCamera, [hasCamera]);
			}
			else
			{
				this.postponedEvents.push({
					name: InternalEvents.setHasCamera,
					params: [hasCamera]
				})
			}
		}
		else if (this.content)
		{
			this.content.setHasCamera(hasCamera)
		}
	};

	sendPostponedEvents()
	{
		this.postponedEvents.forEach((event) =>
		{
			DesktopApi.emit(event.name, event.params);
		})
		this.postponedEvents = [];
	}

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
		this.emit(Events.onClose);
	};

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
		if (this.content)
		{
			this.content.destroy();
			this.content = null;
		}

		if (DesktopApi.isDesktop())
		{
			DesktopApi.unsubscribe(InternalEvents.onButtonClick, this.onButtonClickHandler);
			DesktopApi.unsubscribe(InternalEvents.contentReady, this.onContentReadyHandler);
		}
		this.emit(Events.onDestroy);

		this.unsubscribeAll(Events.onButtonClick);
		this.unsubscribeAll(Events.onClick);
		this.unsubscribeAll(Events.onDestroy);
	};

	#onButtonClick(event)
	{
		this.emit(Events.onButtonClick, event);
	}

	#onContentReady()
	{
		this.contentReady = true;
		this.sendPostponedEvents();
	}
}

export class IncomingNotificationContent extends EventEmitter
{
	constructor(config)
	{
		super();
		this.setEventNamespace('BX.Call.IncomingNotificationContent');

		this.video = !!config.video;
		this.hasCamera = !!config.hasCamera;
		this.callerAvatar = config.callerAvatar || '';
		this.callerName = config.callerName || BX.message('IM_M_CALL_VIDEO_HD');
		this.callerType = config.callerType || 'chat';
		this.callerColor = config.callerColor || '';

		this.elements = {
			root: null,
			avatar: null,
			buttons: {
				answerVideo: null
			}
		};

		this.#subscribeEvents(config)
		if (DesktopApi.isDesktop())
		{
			this.onHasCameraHandler = this.#onHasCamera.bind(this);
			DesktopApi.subscribe(InternalEvents.setHasCamera, this.onHasCameraHandler);
			DesktopApi.emitToMainWindow(InternalEvents.contentReady, []);
		}
	};

	#subscribeEvents(config)
	{
		const eventKeys = Object.keys(Events);
		for (let eventName of eventKeys)
		{
			if (Type.isFunction(config[eventName]))
			{
				this.subscribe(Events[eventName], config[eventName])
			}
		}
	}

	render()
	{
		const backgroundImage = this.callerAvatar || '/bitrix/js/im/images/default-call-background.png';
		let callerPrefix;

		if (this.video)
		{
			if (this.callerType === 'private')
			{
				callerPrefix = BX.message("IM_M_VIDEO_CALL_FROM");
			}
			else
			{
				callerPrefix = BX.message("IM_M_VIDEO_CALL_FROM_CHAT");
			}
		}
		else
		{
			if (this.callerType === 'private')
			{
				callerPrefix = BX.message("IM_M_CALL_FROM");
			}
			else
			{
				callerPrefix = BX.message("IM_M_CALL_FROM_CHAT");
			}
		}

		let avatarClass = '';
		let avatarImageClass = '';
		let avatarImageStyles;

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
			const callerType = this.callerType === 'private' ? 'user' : this.callerType;

			avatarClass = 'bx-messenger-panel-avatar-' + callerType;
			avatarImageStyles = {
				backgroundColor: this.callerColor || '#525252',
				backgroundSize: '40px',
				backgroundPosition: 'center center',
			}
			avatarImageClass = 'bx-messenger-panel-avatar-img-default';
		}

		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-call-window"},
			children: [
				Dom.create("div", {
					props: {className: "bx-messenger-call-window-background"},
					style: {
						backgroundImage: "url('" + backgroundImage + "')"
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
											props: {
												className: "bx-messenger-call-window-photo-left " + avatarClass
											},
											children: [
												this.elements.avatar = Dom.create("div", {
													props: {
														className: "bx-messenger-call-window-photo-block " + avatarImageClass
													},
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
													text: callerPrefix
												}),
												Dom.create("div", {
													props: {className: "bx-messenger-call-overlay-title-caller"},
													text: Text.decode(this.callerName)
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
												this.elements.buttons.answerVideo = Dom.create("div", {
													props: {className: "bx-messenger-call-window-button" + (!this.hasCamera ? " bx-messenger-call-window-button-disabled" : "")},
													children: [
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-camera"}
														}),
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-text"},
															text: BX.message("IM_M_CALL_BTN_ANSWER_VIDEO"),
														}),
													],
													events: {click: this.#onAnswerWithVideoButtonClick.bind(this)}
												}),
												Dom.create("div", {
													props: {className: "bx-messenger-call-window-button"},
													children: [
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-phone-up"}
														}),
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-text"},
															text: BX.message("IM_M_CALL_BTN_ANSWER"),
														}),
													],
													events: {click: this.#onAnswerButtonClick.bind(this)}
												}),
												Dom.create("div", {
													props: {className: "bx-messenger-call-window-button bx-messenger-call-window-button-danger"},
													children: [
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-icon bx-messenger-call-window-button-icon-phone-down"}
														}),
														Dom.create("div", {
															props: {className: "bx-messenger-call-window-button-text"},
															text: BX.message("IM_M_CALL_BTN_DECLINE"),
														}),
													],
													events: {click: this.#onDeclineButtonClick.bind(this)}
												})
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

	showInDesktop()
	{
		// Workaround to prevent incoming call window from hanging.
		// Without it, there is a possible scenario, when BXDesktopWindow.ExecuteCommand("close") is executed too early
		// (if invite window is closed before appearing), which leads to hanging of the window
		if (window.opener.BXIM?.callController && !window.opener.BXIM.callController.callNotification)
		{
			BXDesktopWindow.ExecuteCommand("close");
			return;
		}

		this.render();
		document.body.appendChild(this.elements.root);
		DesktopApi.setWindowPosition({
			x: STP_CENTER,
			y: STP_VCENTER,
			width: 351,
			height: 510
		});
	};

	setHasCamera(hasCamera)
	{
		this.hasCamera = !!hasCamera;
		if (this.elements.buttons.answerVideo)
		{
			this.elements.buttons.answerVideo.classList.toggle("bx-messenger-call-window-button-disabled", !this.hasCamera);
		}
	};

	#onHasCamera()
	{

	}

	#onAnswerButtonClick()
	{
		if (DesktopApi.isDesktop())
		{
			DesktopApi.closeWindow();
			DesktopApi.emitToMainWindow(InternalEvents.onButtonClick, [{
				button: 'answer',
				video: false
			}]);
		}
		else
		{
			this.emit(Events.onButtonClick, {
				button: 'answer',
				video: false
			});
		}
	};

	#onAnswerWithVideoButtonClick()
	{
		if (!this.hasCamera)
		{
			return;
		}
		if (DesktopApi.isDesktop())
		{
			DesktopApi.closeWindow();
			DesktopApi.emitToMainWindow(InternalEvents.onButtonClick, [{
				button: 'answer',
				video: true
			}]);
		}
		else
		{
			this.emit(Events.onButtonClick, {
				button: 'answer',
				video: true
			});
		}
	};

	#onDeclineButtonClick()
	{
		if (DesktopApi.isDesktop())
		{
			DesktopApi.closeWindow();
			DesktopApi.emitToMainWindow(InternalEvents.onButtonClick, [{
				button: 'decline'
			}]);
		}
		else
		{
			this.emit(Events.onButtonClick, {
				button: 'decline'
			});
		}
	};

	destroy()
	{
		if (DesktopApi.isDesktop())
		{
			DesktopApi.unsubscribe(InternalEvents.setHasCamera, this.onHasCameraHandler);
		}
		this.unsubscribeAll(Events.onButtonClick);
		this.unsubscribeAll(Events.onClick);
		this.unsubscribeAll(Events.onDestroy);
	}
}