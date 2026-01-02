import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Extension } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';
import { Controller, State as CallState, EngineLegacy } from 'call.core';
import { CallEngine } from 'call.core.engine';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { MessengerSlider } from 'im.v2.lib.slider';
import { ChatType, RecentCallStatus, Layout, EventType, UserType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { PromoManager } from 'im.v2.lib.promo';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';
import { RestClient } from 'rest.client';

import { BetaCallService } from './classes/beta-call-service';
import { openCallUserSelector } from './functions/open-call-user-selector';

import 'im_call_compatible';

import type { JsonObject } from 'main.core';
import type { Call, CallAssociatedEntity, ImModelChat, ImModelUser } from 'im.v2.model';
import type { OnLayoutChangeEvent } from 'im.v2.const';

export class CallManager
{
	static instance: CallManager;
	static viewContainerClass: string = 'bx-im-messenger__call_container';

	#controller: Controller;
	#store: Store;
	#restClient: RestClient;

	#openChatActionByChatType = {
		[ChatType.taskComments]: (dialogId: string) => {
			if (Messenger.isEmbeddedMode() || Messenger.isMessengerSliderOpened())
			{
				return Messenger.openTaskComments(dialogId);
			}

			return Promise.resolve();
		},
	};

	#onCallJoinHandler: Function;
	#onCallLeaveHandler: Function;
	#onCallDestroyHandler: Function;

	static getInstance(): CallManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
		if (this.isAvailable())
		{
			this.#controller = this.#getController();
		}

		this.#subscribeToEvents();

		this.#onCallJoinHandler = this.#onCallJoin.bind(this);
		this.#onCallLeaveHandler = this.#onCallLeave.bind(this);
		this.#onCallDestroyHandler = this.#onCallDestroy.bind(this);
	}

	isAvailable(): Boolean
	{
		const { callInstalled } = Extension.getSettings('im.v2.lib.call');

		return callInstalled === true;
	}

	// TODO: add to a new file after separating call and im
	async sendBroadcastRequest(callId): Promise<boolean[]> {
		if (!this.isAvailable())
		{
			return Promise.resolve([])
		}

		if (!DesktopApi.isDesktop())
		{
			return Promise.resolve([])
		}

		return await this.#controller.callMultiBroadcastClient.broadcastRequest(callId, { timeout: 100 });
	}

	createBetaCallRoom(chatId: number)
	{
		if (!this.isAvailable())
		{
			return;
		}

		BetaCallService.createRoom(chatId);
	}

	startCall(dialogId: string, withVideo: boolean = true, callToken: string = '')
	{
		if (!this.isAvailable())
		{
			return;
		}

		Logger.warn('CallManager: startCall', dialogId, withVideo);

		this.#prepareCall(dialogId);
		this.#getChatInfo(dialogId).then((chatInfo) => {
			this.#controller.startCall(dialogId, withVideo, chatInfo);
		});
	}

	joinCall(callId: string, callUuid: string, dialogId: string, withVideo: boolean = true)
	{
		if (!this.isAvailable())
		{
			return;
		}

		Logger.warn('CallManager: joinCall', callId, callUuid, withVideo);

		this.#prepareCall(dialogId);
		this.#getChatInfo(dialogId).then((chatInfo) => {
			this.#controller.joinCall(callId, callUuid, withVideo, { chatInfo });
		});
	}

	leaveCurrentCall()
	{
		if (!this.isAvailable())
		{
			return;
		}

		Logger.warn('CallManager: leaveCurrentCall');
		this.#controller.leaveCurrentCall();
	}

	onAnswerButtonClick(mediaParams: JsonObject, callParams: JsonObject)
	{
		if (!this.isAvailable())
		{
			return;
		}

		this.#controller.onAnswerButtonClick(mediaParams, callParams);
	}

	onJoinFromRecentItem()
	{
		this.#controller.closeCallNotification();
	}

	deleteRecentCall(dialogId: string)
	{
		this.#store.dispatch('recent/calls/deleteActiveCall', {
			dialogId,
		});
	}

	foldCurrentCall()
	{
		if (!this.isAvailable() || !this.#controller.hasActiveCall() || !this.#controller.hasVisibleCall())
		{
			return;
		}

		this.#controller.fold();
	}

	unfoldCurrentCall()
	{
		if (!this.isAvailable() || !this.#controller.hasActiveCall())
		{
			return;
		}

		this.#controller.unfold();
	}

	getCurrentCallDialogId(): string
	{
		if (!this.isAvailable() || !this.#controller.hasActiveCall())
		{
			return '';
		}

		return this.#controller?.currentCall?.associatedEntity.id;
	}

	getCurrentCall(): boolean
	{
		if (!this.isAvailable() || !this.#controller.hasActiveCall())
		{
			return false;
		}

		return this.#controller.currentCall;
	}

	getCurrentUser(): ImModelUser
	{
		const currentUserId = Core.getUserId();
		return Core.getStore().getters['users/get'](currentUserId);
	}

	hasCurrentCall(): boolean
	{
		if (!this.isAvailable())
		{
			return false;
		}

		return this.#controller.hasActiveCall();
	}

	hasCurrentScreenSharing(): boolean
	{
		if (!this.isAvailable() || !this.#controller.hasActiveCall())
		{
			return false;
		}

		return this.#controller.currentCall.isScreenSharingStarted();
	}

	hasVisibleCall(): boolean
	{
		if (!this.isAvailable() || !this.#controller.hasActiveCall())
		{
			return false;
		}

		return this.#controller.hasVisibleCall();
	}

	startTest()
	{
		if (!this.isAvailable())
		{
			return;
		}

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

	chatCanBeCalled(dialogId: string): boolean
	{
		if (!this.isAvailable())
		{
			return false;
		}

		const callSupported = this.#checkCallSupport(dialogId);
		const hasCurrentCall = this.#store.getters['recent/calls/hasActiveCall'](dialogId);

		return callSupported && !hasCurrentCall;
	}

	hasActiveCurrentCall(dialogId: string): boolean
	{
		return this.#store.getters['recent/calls/hasActiveCall'](dialogId)
			&& this.getCurrentCallDialogId() === dialogId;
	}

	hasActiveAnotherCall(dialogId: string): boolean
	{
		return this.#store.getters['recent/calls/hasActiveCall']()
			&& !this.hasActiveCurrentCall(dialogId);
	}

	getCallUserLimit(): number
	{
		// TODO: add to 597100
		if (!this.isAvailable())
		{
			return 0;
		}

		return BX.Call.Util.getUserLimit();
	}

	isChatUserLimitExceeded(dialogId: string): boolean
	{
		return this.#getChatUserCounter(dialogId) > this.getCallUserLimit();
	}

	updateRecentCallsList(activeCalls): void
	{
		const recentCalls = this.#store.getters['recent/calls/get'];

		const activeCallsMap = new Map(Object.values(activeCalls).map((call) => [call.ID, call]));

		activeCallsMap.forEach((call) => {
			const instantiatedCall = this.#controller.isLegacyCall(call.PROVIDER, call.SCHEME)
				? EngineLegacy.instantiateCall(call, call.USERS, call.LOG_TOKEN, call.CONNECTION_DATA, call.USER_DATA)
				: CallEngine.instantiateCall(call, call.CALL_TOKEN, call.LOG_TOKEN, call.USER_DATA);
			this.#subscribeToCallEvents(instantiatedCall);
		});

		recentCalls
			.filter((oldCall) => !activeCallsMap.has(oldCall.call.id))
			.forEach((oldCall) => {
				this.#store.dispatch('recent/calls/deleteActiveCall', {
					dialogId: oldCall.dialogId,
				});
			});
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
					const dialog: ImModelChat = this.#getDialog(dialogId);
					if (dialog.type in this.#openChatActionByChatType)
					{
						return this.#openChatActionByChatType[dialog.type](dialogId);
					}

					return Messenger.openChat(dialogId);
				},
				openHistory: (dialogId) => {
					return Messenger.openChat(dialogId);
				},
				openSettings: () => {
					return Messenger.openSettings();
				},
				openHelpArticle: () => {}, // TODO
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
		EventEmitter.subscribe(EventType.layout.onLayoutChange, this.#onOpenChat.bind(this));
		EventEmitter.subscribe(EventType.layout.onOpenNotifications, this.foldCurrentCall.bind(this));
		EventEmitter.subscribe(EventType.call.onJoinFromRecentItem, this.onJoinFromRecentItem.bind(this));

		EventEmitter.subscribe('CallEvents::callCreated', this.#onCallCreated.bind(this));
	}

	#subscribeToCallEvents(call: Call)
	{
		call.addEventListener(BX.Call.Event.onJoin, this.#onCallJoinHandler);
		call.addEventListener(BX.Call.Event.onLeave, this.#onCallLeaveHandler);
		call.addEventListener(BX.Call.Event.onDestroy, this.#onCallDestroyHandler);
	}

	#unsubscribeFromCallEvents(call: Call)
	{
		call.removeEventListener(BX.Call.Event.onJoin, this.#onCallJoinHandler);
		call.removeEventListener(BX.Call.Event.onLeave, this.#onCallLeaveHandler);
		call.removeEventListener(BX.Call.Event.onDestroy, this.#onCallDestroyHandler);
	}

	#onCallCreated(event)
	{
		const { call } = event.getData()[0];
		const currentCall = this.#store.getters['recent/calls/getCallByDialog'](call.associatedEntity.id);
		const isNewCall = currentCall?.call.uuid !== call.uuid;

		const state = (
			call.state === CallState.Connected || call.state === CallState.Proceeding
				? RecentCallStatus.joined
				: RecentCallStatus.waiting
		);

		if (isNewCall)
		{
			if (currentCall)
			{
				this.#unsubscribeFromCallEvents(currentCall.call);
			}

			this.#subscribeToCallEvents(call);
			this.#store.dispatch('recent/calls/addActiveCall', {
				dialogId: call.associatedEntity.id,
				name: call.associatedEntity.name,
				call,
				state,
			});
			return;
		}

		this.#store.dispatch('recent/calls/updateActiveCall', {
			dialogId: call.associatedEntity.id,
			fields: {
				name: call.associatedEntity.name,
				state,
				call,
			},
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

		if (currentCall)
		{
			this.#unsubscribeFromCallEvents(currentCall.call);
		}

		if (currentCall?.call.uuid === event.call.uuid)
		{
			this.#store.dispatch('recent/calls/deleteActiveCall', {
				dialogId,
			});
		}
	}

	#onOpenChat(event: BaseEvent<OnLayoutChangeEvent>)
	{
		const callDialogId = this.getCurrentCallDialogId();
		const openedChat = event.getData().to.entityId;
		if (callDialogId === openedChat)
		{
			return;
		}

		this.foldCurrentCall();
	}

	isConference(dialogId: string): boolean
	{
		const dialog: ImModelChat = this.#store.getters['chats/get'](dialogId);

		return dialog.type === ChatType.videoconf;
	}

	#checkCallSupport(dialogId: string): boolean
	{
		if (!this.#pushServerIsActive() || !BX.Call.Util.isWebRTCSupported())
		{
			return false;
		}

		const userId = Number(dialogId);

		return userId > 0 ? this.#checkUserCallSupport(userId) : this.#checkChatCallSupport(dialogId);
	}

	#checkUserCallSupport(userId: number): boolean
	{
		const user = this.#store.getters['users/get'](userId);
		const isBot = user.type === UserType.bot;

		return (
			user
			&& user.status !== 'guest'
			&& !isBot
			&& !user.network
			&& user.id !== Core.getUserId()
			&& Boolean(user.lastActivityDate)
		);
	}

	#checkChatCallSupport(dialogId: string): boolean
	{
		const userCounter = this.#getChatUserCounter(dialogId);

		return (userCounter > 1 || this.isConference(dialogId)) && userCounter <= this.getCallUserLimit();
	}

	#pushServerIsActive(): boolean
	{
		return true;
	}

	#getCurrentDialogId(): string
	{
		const layout = this.#store.getters['application/getLayout'];
		if (layout.name !== Layout.chat)
		{
			return '';
		}

		return layout.entityId;
	}

	#getDialog(dialogId: string): ImModelChat
	{
		return this.#store.getters['chats/get'](dialogId);
	}

	#getChatId(dialogId: string): ?number
	{
		return this.#getDialog(dialogId)?.chatId;
	}

	#isUser(dialogId: string): boolean
	{
		const dialog: ImModelChat = this.#getDialog(dialogId);

		return dialog?.type === ChatType.user;
	}

	#getChatInfo(dialogId: string): Promise<CallAssociatedEntity>
	{
		const chatInfo = this.#store.getters['chats/get'](dialogId, true);

		if (chatInfo.chatId === 0)
		{
			return Messenger.openChat(dialogId).then(() => {
				const updatedChatInfo = this.#store.getters['chats/get'](dialogId, true);

				return this.#prepareChatInfo(updatedChatInfo);
			}).catch((error) => {
				Logger.error('Open chat error', error);

				return this.#prepareChatInfo(chatInfo);
			});
		}

		return new Promise((resolve) => {
			resolve(this.#prepareChatInfo(chatInfo));
		});
	}

	#prepareChatInfo(chatInfo: CallAssociatedEntity): CallAssociatedEntity
	{
		return {
			advanced: {
				chatType: chatInfo.type,
				entityType: chatInfo.entityType,
				entityId: chatInfo.entityId,
				entityData1: chatInfo.entityData1,
				entityData2: chatInfo.entityData2,
				entityData3: chatInfo.entityData3,
			},
			id: chatInfo.dialogId,
			chatId: chatInfo.chatId,
			name: chatInfo.name,
			avatar: chatInfo.avatar || '/bitrix/js/im/images/blank.gif',
			avatarColor: chatInfo.color,
			type: 'chat',
			userCounter: chatInfo.userCounter,
		};
	}

	#prepareCall(dialogId: string)
	{
		if (!this.isAvailable())
		{
			return;
		}

		const currentUserId = Core.getUserId();
		const currentUser: ImModelUser = Core.getStore().getters['users/get'](currentUserId);

		const callData = {
			dialogId,
			userData: {
				[currentUserId]: currentUser,
			},
		};

		if (this.#isUser(dialogId))
		{
			const currentCompanion: ImModelUser = Core.getStore().getters['users/get'](dialogId);
			callData['user'] = currentCompanion.id;
			callData['userData'][currentCompanion.id] = currentCompanion;
		}

		this.#controller.prepareCall(callData);
	}

	#getChatUserCounter(dialogId: string): number
	{
		const { userCounter } = this.#store.getters['chats/get'](dialogId, true);

		return userCounter;
	}
	// endregion call events
}