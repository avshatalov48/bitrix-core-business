import { EventEmitter, BaseEvent } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';

import { Controller, State as CallState } from 'im.call';
import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { MessengerSlider } from 'im.v2.lib.slider';
import { ChatType, RecentCallStatus, Layout, EventType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { PromoManager } from 'im.v2.lib.promo';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';

import { BetaCallService } from './classes/beta-call-service';
import { openCallUserSelector } from './functions/open-call-user-selector';

import 'im_call_compatible';

import type { ImModelChat, ImModelUser } from 'im.v2.model';

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

	createBetaCallRoom(chatId: number)
	{
		BetaCallService.createRoom(chatId);
	}

	startCall(dialogId: string, withVideo: boolean = true)
	{
		Logger.warn('CallManager: startCall', dialogId, withVideo);
		if (this.#isUser(dialogId))
		{
			this.#prepareUserCall(dialogId);
		}
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
		if (!this.#controller.hasActiveCall() || !this.#controller.hasVisibleCall())
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

	getCurrentCall(): boolean
	{
		if (!this.#controller.hasActiveCall())
		{
			return false;
		}

		return this.#controller.currentCall;
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

	toggleDebugFlag(debug)
	{
		if (!this.#controller)
		{
			return;
		}

		this.#controller.debug = debug;
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
				openHistory: (dialogId) => {
					return Messenger.openChat(dialogId);
				},
				openSettings: () => {
					return Messenger.openSettings();
				},
				openHelpArticle: () => {}, // TODO
				getContainer: () => document.querySelector(`.${CallManager.viewContainerClass}`),
				getMessageCount: () => this.#store.getters['counters/getTotalChatCounter'],
				getCurrentDialogId: () => this.#getCurrentDialogId(),
				isPromoRequired: (promoCode: string) => {
					return PromoManager.getInstance().needToShow(promoCode);
				},
				repeatSound: (soundType, timeout, force) => {
					SoundNotificationManager.getInstance().playLoop(soundType, timeout, force);
				},
				stopRepeatSound: (soundType) => {
					SoundNotificationManager.getInstance().stop(soundType);
				},
				showUserSelector: openCallUserSelector,
			},
			events: {
				[Controller.Events.onPromoViewed]: (event) => {
					const { code } = event.getData();
					PromoManager.getInstance().markAsWatched(code);
				},
				[Controller.Events.onOpenVideoConference]: (event) => {
					const { dialogId: chatId } = event.getData();
					const dialog: ImModelChat = Core.getStore().getters['chats/get'](`chat${chatId}`, true);

					return Messenger.openConference({ code: dialog.public?.code });
				},
			},
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
		const { call } = event.getData()[0];
		call.addEventListener(BX.Call.Event.onJoin, this.#onCallJoin.bind(this));
		call.addEventListener(BX.Call.Event.onLeave, this.#onCallLeave.bind(this));
		call.addEventListener(BX.Call.Event.onDestroy, this.#onCallDestroy.bind(this));

		const state = (
			call.state === CallState.Connected || call.state === CallState.Proceeding
				? RecentCallStatus.joined
				: RecentCallStatus.waiting
		);

		this.#store.dispatch('recent/calls/addActiveCall', {
			dialogId: call.associatedEntity.id,
			name: call.associatedEntity.name,
			call,
			state,
		});
	}

	#onCallJoin(event)
	{
		this.#store.dispatch('recent/calls/updateActiveCall', {
			dialogId: event.call.associatedEntity.id,
			fields: {
				state: RecentCallStatus.joined,
			},
		});
	}

	#onCallLeave(event)
	{
		this.#store.dispatch('recent/calls/updateActiveCall', {
			dialogId: event.call.associatedEntity.id,
			fields: {
				state: RecentCallStatus.waiting,
			},
		});
	}

	#onCallDestroy(event)
	{
		const dialogId = event.call.associatedEntity.id;
		const currentCall = this.#store.getters['recent/calls/getCallByDialog'](dialogId);

		if (currentCall?.call.id === event.call.id)
		{
			this.#store.dispatch('recent/calls/deleteActiveCall', {
				dialogId,
			});
		}
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
		const callSupported = this.#checkCallSupport(dialogId);
		const hasCurrentCall = this.#store.getters['recent/calls/hasActiveCall'](dialogId);

		return callSupported && !hasCurrentCall;
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
		const dialog = this.#store.getters['chats/get'](dialogId);
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

	#isUser(dialogId: string): boolean
	{
		const dialog: ImModelChat = this.#store.getters['chats/get'](dialogId);
		return dialog?.type === ChatType.user;
	}

	#prepareUserCall(dialogId: string)
	{
		const currentUserId = Core.getUserId();
		const currentUser: ImModelUser = Core.getStore().getters['users/get'](currentUserId);
		const currentCompanion: ImModelUser = Core.getStore().getters['users/get'](dialogId);

		this.#controller.prepareUserCall({
			dialogId,
			user: currentCompanion.id,
			userData: {
				[currentUserId]: currentUser,
				[currentCompanion.id]: currentCompanion,
			}
		});
	}
	// endregion call events
}