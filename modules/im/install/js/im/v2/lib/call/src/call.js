import {EventEmitter, BaseEvent} from 'main.core.events';
import {Store} from 'ui.vue3.vuex';

import {Controller} from 'im.call';
import {Messenger} from 'im.public';
import {Core} from 'im.v2.application.core';
import {MessengerSlider} from 'im.v2.lib.slider';
import {ChatOption, DialogType, RecentCallStatus, Layout, EventType, SoundType} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';
import {SoundNotificationManager} from 'im.v2.lib.sound-notification';

import 'im_call_compatible';

export class CallManager
{
	static instance: CallManager;
	static viewContainerClass: string = 'bx-im-messenger__call_container';

	#controller: Controller;
	#store: Store;

	static getInstance(): CallManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	static init()
	{
		CallManager.getInstance();
	}

	constructor()
	{
		this.#store = Core.getStore();
		this.#controller = this.#getController();

		this.#subscribeToEvents();
	}

	startCall(dialogId: string, withVideo: boolean = true)
	{
		Logger.warn('CallManager: startCall', dialogId, withVideo);
		this.#controller.startCall(dialogId, withVideo);
	}

	joinCall(callId: string, withVideo: boolean = true)
	{
		Logger.warn('CallManager: joinCall', callId, withVideo);
		this.#controller.joinCall(callId, withVideo);
	}

	leaveCurrentCall()
	{
		Logger.warn('CallManager: leaveCurrentCall');
		this.#controller.leaveCurrentCall();
	}

	foldCurrentCall()
	{
		if (!this.#controller.hasActiveCall())
		{
			return;
		}

		this.#controller.fold();
	}

	unfoldCurrentCall()
	{
		if (!this.#controller.hasActiveCall())
		{
			return;
		}

		this.#controller.unfold();
	}

	getCurrentCallDialogId(): string
	{
		if (!this.#controller.hasActiveCall())
		{
			return '';
		}

		return this.#controller.currentCall.associatedEntity.id;
	}

	hasCurrentCall(): boolean
	{
		return this.#controller.hasActiveCall();
	}

	hasCurrentScreenSharing(): boolean
	{
		if (!this.#controller.hasActiveCall())
		{
			return false;
		}

		return this.#controller.currentCall.isScreenSharingStarted();
	}

	hasVisibleCall(): boolean
	{
		if (!this.#controller.hasActiveCall())
		{
			return false;
		}

		return this.#controller.hasVisibleCall();
	}

	startTest()
	{
		this.#controller.test();
	}

	#getController(): Controller
	{
		return new Controller({
			init: true,
			language: Core.getLanguageId(),
			messengerFacade: {
				getDefaultZIndex: () => MessengerSlider.getInstance().getZIndex(),
				isMessengerOpen: () => MessengerSlider.getInstance().isOpened(),
				isSliderFocused: () => MessengerSlider.getInstance().isFocused(),
				isThemeDark: () => false,
				openMessenger: (dialogId) => {
					return Messenger.openChat(dialogId);
				},
				openHistory: () => {},
				openSettings: () => {}, // TODO
				openHelpArticle: () => {}, // TODO
				getContainer: () => document.querySelector(`.${CallManager.viewContainerClass}`),
				getMessageCount: () => this.#store.getters['recent/getTotalCounter'],
				getCurrentDialogId: () => this.#getCurrentDialogId(),
				isPromoRequired: () => false,
				repeatSound: (soundType, timeout, force) => {
					SoundNotificationManager.getInstance().playLoop(soundType, timeout, force);
				},
				stopRepeatSound: (soundType) => {
					SoundNotificationManager.getInstance().stop(soundType);
				}
			},
			events: {}
		});
	}

	// region call events
	#subscribeToEvents()
	{
		EventEmitter.subscribe(EventType.layout.onOpenChat, this.#onOpenChat.bind(this));
		EventEmitter.subscribe(EventType.layout.onOpenNotifications, this.foldCurrentCall.bind(this));

		EventEmitter.subscribe('CallEvents::callCreated', this.#onCallCreated.bind(this));
	}

	#onCallCreated(event)
	{
		const {call} = event.getData()[0];
		call.addEventListener(BX.Call.Event.onJoin, this.#onCallJoin.bind(this));
		call.addEventListener(BX.Call.Event.onLeave, this.#onCallLeave.bind(this));
		call.addEventListener(BX.Call.Event.onDestroy, this.#onCallDestroy.bind(this));

		this.#store.dispatch('recent/calls/addActiveCall', {
			dialogId: call.associatedEntity.id,
			name: call.associatedEntity.name,
			call: call,
			state: RecentCallStatus.waiting
		});
	}

	#onCallJoin(event)
	{
		this.#store.dispatch('recent/calls/updateActiveCall', {
			dialogId: event.call.associatedEntity.id,
			fields: {
				state: RecentCallStatus.joined
			}
		});
	}

	#onCallLeave(event)
	{
		this.#store.dispatch('recent/calls/updateActiveCall', {
			dialogId: event.call.associatedEntity.id,
			fields: {
				state: RecentCallStatus.waiting
			}
		});
	}

	#onCallDestroy(event)
	{
		this.#store.dispatch('recent/calls/deleteActiveCall', {
			dialogId: event.call.associatedEntity.id
		});
	}

	#onOpenChat(event: BaseEvent<{dialogId: string}>)
	{
		const callDialogId = this.getCurrentCallDialogId();
		const openedChat = event.getData().dialogId;
		if (callDialogId === openedChat)
		{
			return;
		}

		this.foldCurrentCall();
	}

	chatCanBeCalled(dialogId: string): boolean
	{
		const dialog = this.#store.getters['dialogues/get'](dialogId);
		if (!dialog)
		{
			return false;
		}

		const isChat = dialog.type !== DialogType.user;
		const callAllowed = this.#store.getters['dialogues/getChatOption'](dialog.type, ChatOption.call);
		if (isChat && !callAllowed)
		{
			return false;
		}

		const callSupported = this.#checkCallSupport(dialogId);
		const isAnnouncement = dialog.type === DialogType.announcement;
		const isExternalTelephonyCall = dialog.type === DialogType.call;
		const hasCurrentCall = this.hasCurrentCall();

		return callSupported && !isAnnouncement && !isExternalTelephonyCall && !hasCurrentCall;
	}

	#checkCallSupport(dialogId: string): boolean
	{
		if (!this.#pushServerIsActive() || !BX.Call.Util.isWebRTCSupported())
		{
			return false;
		}

		const userId = Number.parseInt(dialogId, 10);

		return userId > 0 ? this.#checkUserCallSupport(userId) : this.#checkChatCallSupport(dialogId);
	}

	#checkUserCallSupport(userId: number): boolean
	{
		const user = this.#store.getters['users/get'](userId);

		return (
			user
			&& user.status !== 'guest'
			&& !user.bot
			&& !user.network
			&& user.id !== Core.getUserId()
			&& !!user.lastActivityDate
		);
	}

	#checkChatCallSupport(dialogId: string): boolean
	{
		const dialog = this.#store.getters['dialogues/get'](dialogId);
		if (!dialog)
		{
			return false;
		}

		const {userCounter} = dialog;

		return userCounter > 1 && userCounter <= BX.Call.Util.getUserLimit();
	}

	#pushServerIsActive(): boolean
	{
		return true;
	}

	#getCurrentDialogId(): string
	{
		const layout = this.#store.getters['application/getLayout'];
		if (layout.name !== Layout.chat.name)
		{
			return '';
		}

		return layout.entityId;
	}
	// endregion call events
}