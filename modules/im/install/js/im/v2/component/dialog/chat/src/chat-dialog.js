import { Runtime, Event, Dom } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { PopupManager } from 'main.popup';
import { PullStatus } from 'pull.vue3.status';

import { Analytics } from 'im.v2.lib.analytics';
import { MessageList } from 'im.v2.component.message-list';
import { ForwardPopup } from 'im.v2.component.entity-selector';
import { Logger } from 'im.v2.lib.logger';
import { CallManager } from 'im.v2.lib.call';
import { LayoutManager } from 'im.v2.lib.layout';
import { PermissionManager } from 'im.v2.lib.permission';
import { AccessManager } from 'im.v2.lib.access';
import { FeatureManager } from 'im.v2.lib.feature';
import { MessageService } from 'im.v2.provider.service.message';
import { ChatService } from 'im.v2.provider.service.chat';
import {
	DialogBlockType as BlockType,
	EventType,
	PopupType,
	DialogScrollThreshold,
	UserRole,
	ActionByRole,
	ErrorCode,
	AnchorType,
} from 'im.v2.const';

import { AnchorService } from './classes/anchor-service';
import { ScrollManager } from './classes/scroll-manager';
import { PullWatchManager } from './classes/pull-watch-manager';
import { VisibleMessagesManager } from './classes/visible-messages-manager';
import { findUniqueNumbers } from './helpers/find-unique-numbers';
import { sequentialize } from './helpers/sequentialize';

import { PinnedMessages } from './components/pinned/pinned-messages';
import { QuoteButton } from './components/quote-button';
import { FloatButtons } from './components/float-buttons';

import './css/chat-dialog.css';
import './css/float-button.css';

import type { ImModelMessage, ImModelChat, ImModelLayout } from 'im.v2.model';
import type { ScrollToBottomEvent } from 'im.v2.const';

export { ScrollManager } from './classes/scroll-manager';
export { PinnedMessages } from './components/pinned/pinned-messages';
export { FloatButton, FloatButtonIcon, FloatButtonColor } from './components/float-button';

// @vue/component
export const ChatDialog = {
	name: 'ChatDialog',
	components: {
		MessageList,
		PinnedMessages,
		QuoteButton,
		FloatButtons,
		PullStatus,
		ForwardPopup,
	},
	props: {
		dialogId: {
			type: String,
			default: '',
		},
		saveScrollOnExit: {
			type: Boolean,
			default: true,
		},
		reloadOnExit: {
			type: Boolean,
			default: true,
		},
		clearOnExit: {
			type: Boolean,
			default: false,
		},
	},
	data(): Object
	{
		return {
			forwardPopup: {
				show: false,
				messagesIds: [],
			},
			contextMode: {
				active: false,
				messageIsLoaded: false,
			},
			isScrolledUp: false,
			windowFocused: false,
			showQuoteButton: false,
			isJumpingToAnchor: false,
			messagesToRead: new Set(),
			containerHeight: 0,
		};
	},
	computed:
	{
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		messageCollection(): ImModelMessage[]
		{
			return this.$store.getters['messages/getByChatId'](this.dialog.chatId);
		},
		pinnedMessages(): ImModelMessage[]
		{
			return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId);
		},
		isOpened(): boolean
		{
			const openedDialogId = this.$store.getters['application/getLayout'].entityId;

			return this.dialogId === openedDialogId;
		},
		isGuest(): boolean
		{
			return this.dialog.role === UserRole.guest;
		},
		debouncedScrollHandler(): Function
		{
			const SCROLLING_DEBOUNCE_DELAY = 100;

			return Runtime.debounce(this.getScrollManager().onScroll, SCROLLING_DEBOUNCE_DELAY, this.getScrollManager());
		},
		debouncedReadHandler(): Function
		{
			const READING_DEBOUNCE_DELAY = 50;

			return Runtime.debounce(this.readQueuedMessages, READING_DEBOUNCE_DELAY, this);
		},
		sequentiallyHighlightMessageHandler(): Function
		{
			return sequentialize(this.highlightMessage, 300, this);
		},
		showScrollButton(): boolean
		{
			return this.isScrolledUp || this.dialog.hasNextPage;
		},
		anchorMessages(): number[]
		{
			return this.$store.getters['messages/anchors/getChatMessageIdsWithAnchors'](this.dialog.chatId);
		},
		hasCommentsOnTop(): boolean
		{
			return this.$store.getters['messages/comments/areOpenedForChannel'](this.dialogId);
		},
	},
	watch:
	{
		anchorMessages(newValue: number[], oldValue: number[])
		{
			const newMessageIdsWithAnchor = findUniqueNumbers(newValue, oldValue);
			const visibleMessageIds = this.getVisibleMessagesManager().getVisibleMessages();

			newMessageIdsWithAnchor.forEach((messageId) => {
				if (visibleMessageIds.includes(messageId))
				{
					this.getAnchorService().debouncedReadMessageAnchors(messageId);
				}
			});
		},
		dialogInited(newValue: boolean, oldValue: boolean)
		{
			if (!newValue || oldValue)
			{
				return;
			}
			// first opening
			this.getPullWatchManager().subscribe();
			this.onChatInited();
		},
		hasCommentsOnTop: {
			handler(newValue: boolean)
			{
				const commentsWereClosed = newValue === false;
				if (!commentsWereClosed)
				{
					return;
				}

				this.readVisibleMessages();
			},
			flush: 'post',
		},
	},
	created()
	{
		Logger.warn('Dialog: Chat created', this.dialogId);
		this.initContextMode();
	},
	async mounted()
	{
		await this.$nextTick();
		this.containerHeight = this.$refs.container.clientHeight;

		this.getScrollManager().setContainer(this.getContainer());
		if (this.dialogInited)
		{
			// second+ opening
			this.getPullWatchManager().subscribe();
			this.onChatInited();
		}
		// there are P&P messages
		else if (!this.dialogInited && this.messageCollection.length > 0)
		{
			this.scrollOnStart();
		}

		this.windowFocused = document.hasFocus();

		this.subscribeToEvents();
	},
	beforeUnmount()
	{
		this.unsubscribeFromEvents();
		if (this.dialogInited)
		{
			this.saveScrollPosition();
			void this.handleMessagesOnExit();
		}
		this.getPullWatchManager().unsubscribe();
		this.closeDialogPopups();
		this.forwardPopup.show = false;
		this.readAllAnchors();
	},
	methods:
	{
		async scrollOnStart(): void
		{
			await this.$nextTick();

			// we loaded chat with context
			if (this.contextMode.active && this.contextMode.messageIsLoaded)
			{
				this.getScrollManager().scrollToMessage(this.layout.contextId);
				void this.$nextTick(() => {
					this.highlightMessage(this.layout.contextId);
				});

				return;
			}

			// chat was loaded before
			if (this.contextMode.active && !this.contextMode.messageIsLoaded)
			{
				this.goToMessageContext(this.layout.contextId);

				return;
			}

			// marked message
			if (this.dialog.markedId)
			{
				this.getScrollManager().scrollToMessage(BlockType.newMessages);

				return;
			}

			// saved position
			if (this.dialog.savedPositionMessageId && !this.isGuest)
			{
				Logger.warn('Dialog: saved scroll position, scrolling to', this.dialog.savedPositionMessageId);
				this.getScrollManager().scrollToMessage(this.dialog.savedPositionMessageId, { withDateOffset: false });

				return;
			}

			const lastReadId = this.$store.getters['chats/getLastReadId'](this.dialogId);
			const isLastMessageId = lastReadId === this.dialog.lastMessageId;
			// unread messages and read messages before them
			if (lastReadId > 0 && !isLastMessageId)
			{
				Logger.warn('Dialog: scroll to "New messages" mark, lastReadId -', lastReadId, 'lastMessageId', this.dialog.lastMessageId);
				this.getScrollManager().scrollToMessage(BlockType.newMessages);

				return;
			}

			// new chat, unread messages without read messages before them
			const hasUnread = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
			if (lastReadId === 0 || hasUnread)
			{
				this.getScrollManager().setStartScrollNeeded(false);
				Logger.warn('Dialog: dont scroll, hasUnread -', hasUnread, 'lastReadId', lastReadId);

				return;
			}

			// no unread messages
			this.getScrollManager().scrollToBottom();
		},
		showLoadingBar(): void
		{
			EventEmitter.emit(EventType.dialog.showLoadingBar, { dialogId: this.dialogId });
		},
		hideLoadingBar(): void
		{
			EventEmitter.emit(EventType.dialog.hideLoadingBar, { dialogId: this.dialogId });
		},
		async goToMessageContext(messageId: number, params: { position: string } = {}): void
		{
			const { position = ScrollManager.scrollPosition.messageTop } = params;
			const hasMessage = this.$store.getters['messages/hasMessage']({
				chatId: this.dialog.chatId,
				messageId,
			});
			if (hasMessage)
			{
				Logger.warn('Dialog: we have this message, scrolling to it', messageId);

				await this.getScrollManager().animatedScrollToMessage(messageId, { position });
				this.highlightMessage(messageId);

				return;
			}

			const { hasAccess, errorCode } = await AccessManager.checkMessageAccess(messageId);
			if (!hasAccess && errorCode === ErrorCode.message.accessDeniedByTariff)
			{
				Analytics.getInstance().historyLimit.onGoToContextLimitExceeded({ dialogId: this.dialogId });
				FeatureManager.chatHistory.openFeatureSlider();

				return;
			}

			this.showLoadingBar();
			await this.getMessageService().loadContext(messageId);
			await this.$nextTick();
			this.hideLoadingBar();
			this.getScrollManager().scrollToMessage(messageId, { position });
			await this.$nextTick();
			this.highlightMessage(messageId);
		},
		highlightMessage(messageId: number)
		{
			const HIGHLIGHT_CLASS = 'bx-im-dialog-chat__highlighted-message';
			const HIGHLIGHT_DURATION = 2000;

			const message = this.getScrollManager().getDomElementById(messageId);
			if (!message)
			{
				return;
			}

			Dom.addClass(message, HIGHLIGHT_CLASS);
			setTimeout(() => {
				Dom.removeClass(message, HIGHLIGHT_CLASS);
			}, HIGHLIGHT_DURATION);
		},
		saveScrollPosition()
		{
			if (!this.saveScrollOnExit)
			{
				return;
			}
			let savedPositionMessageId = this.getVisibleMessagesManager().getFirstMessageId();
			if (this.getScrollManager().isAroundBottom())
			{
				savedPositionMessageId = 0;
			}
			this.$store.dispatch('chats/update', {
				dialogId: this.dialogId,
				fields: { savedPositionMessageId },
			});
		},
		async handleMessagesOnExit()
		{
			if (this.clearOnExit)
			{
				void this.getChatService().clearChat(this.dialogId);

				return;
			}

			await this.getChatService().readChatQueuedMessages(this.dialog.chatId);

			if (this.reloadOnExit)
			{
				const LOAD_MESSAGES_ON_EXIT_DELAY = 200;
				setTimeout(async () => {
					this.getMessageService().reloadMessageList();
				}, LOAD_MESSAGES_ON_EXIT_DELAY);
			}
		},
		/* region Reading */
		readQueuedMessages(): void
		{
			if (!this.messagesCanBeRead())
			{
				return;
			}

			[...this.messagesToRead].forEach((messageId) => {
				this.getChatService().readMessage(this.dialog.chatId, messageId);
				this.messagesToRead.delete(messageId);
			});
		},
		readVisibleMessages(): void
		{
			if (!this.messagesCanBeRead())
			{
				return;
			}

			const visibleMessages = this.getVisibleMessagesManager().getVisibleMessages();
			visibleMessages.forEach((messageId) => {
				const message: ImModelMessage = this.$store.getters['messages/getById'](messageId);
				if (!message || message.viewed)
				{
					return;
				}

				this.getChatService().readMessage(this.dialog.chatId, messageId);
			});
		},
		readAllAnchors(): void
		{
			if (this.$store.getters['messages/anchors/isChatHasAnchors'](this.dialog.chatId))
			{
				this.getAnchorService().readChatAnchors(this.dialog.chatId);
			}
		},
		messagesCanBeRead(): boolean
		{
			if (!this.dialogInited || !this.isChatVisible())
			{
				return false;
			}

			const permissionManager = PermissionManager.getInstance();

			return permissionManager.canPerformActionByRole(ActionByRole.readMessage, this.dialogId);
		},
		/* endregion Reading */
		/* region Event handlers */
		onChatInited()
		{
			this.scrollOnStart();
			this.readVisibleMessages();

			void this.$nextTick(() => {
				this.getChatService().clearDialogMark(this.dialogId);
			});

			EventEmitter.emit(EventType.dialog.onDialogInited, { dialogId: this.dialogId });
		},
		async onScrollTriggerUp()
		{
			if (!this.dialogInited || !this.getContainer())
			{
				return;
			}

			Logger.warn('Dialog: scroll triggered UP');
			const container = this.getContainer();
			const oldHeight = container.scrollHeight - container.clientHeight;

			// Insert messages if there are some
			if (this.getMessageService().hasPreparedHistoryMessages())
			{
				await this.getMessageService().drawPreparedHistoryMessages();
				this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);

				return;
			}

			// check if already loading or no more history
			if (this.getMessageService().isLoading() || !this.dialog.hasPrevPage)
			{
				return;
			}

			// Load messages and save them
			this.showLoadingBar();
			await this.getMessageService().loadHistory();
			this.hideLoadingBar();
			// Messages loaded and we are at the top
			if (this.getScrollManager().isAtTheTop())
			{
				Logger.warn('Dialog: we are at the top after history request, inserting messages');
				await this.getMessageService().drawPreparedHistoryMessages();
				this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
			}
		},
		async onScrollTriggerDown()
		{
			if (!this.dialogInited || !this.getContainer())
			{
				return;
			}

			Logger.warn('Dialog: scroll triggered DOWN');
			// Insert messages if there are some
			if (this.getMessageService().hasPreparedUnreadMessages())
			{
				await this.getMessageService().drawPreparedUnreadMessages();

				return;
			}

			// check if already loading or no more history
			if (this.getMessageService().isLoading() || !this.dialog.hasNextPage)
			{
				return;
			}

			// Load messages and save them
			this.showLoadingBar();
			await this.getMessageService().loadUnread();
			this.hideLoadingBar();
			// Messages loaded and we are at the bottom
			if (this.getScrollManager().isAroundBottom())
			{
				Logger.warn('Dialog: we are at the bottom after unread request, inserting messages');
				await this.getMessageService().drawPreparedUnreadMessages();
				this.getScrollManager().checkIfChatIsScrolledUp();
			}
		},
		async onScrollToBottom(event: BaseEvent<ScrollToBottomEvent>)
		{
			const { chatId, threshold = DialogScrollThreshold.halfScreenUp, animation = true } = event.getData();
			if (this.dialog.chatId !== chatId)
			{
				return;
			}

			if (!this.windowFocused || this.hasVisibleCall())
			{
				const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
				if (firstUnreadId)
				{
					await this.$nextTick();
					this.getScrollManager().scrollToMessage(firstUnreadId);

					return;
				}
			}

			Logger.warn('Dialog: scroll to bottom', chatId, threshold);
			if (threshold === DialogScrollThreshold.halfScreenUp && this.isScrolledUp)
			{
				return;
			}

			if (threshold === DialogScrollThreshold.nearTheBottom && !this.getScrollManager().isAroundBottom())
			{
				return;
			}

			await this.$nextTick();
			if (animation)
			{
				this.getScrollManager().animatedScrollToBottom();

				return;
			}

			this.getScrollManager().scrollToBottom();
		},
		onGoToMessageContext(event: BaseEvent)
		{
			const { dialogId, messageId } = event.getData();
			if (this.dialog.dialogId !== dialogId)
			{
				return;
			}

			this.goToMessageContext(messageId);
		},
		onPinnedMessageClick(messageId: number)
		{
			this.goToMessageContext(messageId);
		},
		onPinnedMessageUnpin(messageId: number)
		{
			this.getMessageService().unpinMessage(this.dialog.chatId, messageId);
			Analytics.getInstance().messagePins.onUnpin({ dialogId: this.dialogId });
		},
		onScroll(event: Event)
		{
			this.closeDialogPopups();
			this.debouncedScrollHandler(event);
		},
		async onScrollButtonClick()
		{
			if (this.getScrollManager().scrollButtonClicked)
			{
				void this.handleSecondScrollButtonClick();

				return;
			}

			this.getScrollManager().scrollButtonClicked = true;
			if (this.dialog.counter === 0)
			{
				this.showLoadingBar();
				await this.getMessageService().loadInitialMessages();
				this.hideLoadingBar();
				this.getScrollManager().scrollToBottom();

				return;
			}

			const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
			if (!firstUnreadId)
			{
				this.showLoadingBar();
				await this.getMessageService().loadInitialMessages();
				this.hideLoadingBar();
				await this.getScrollManager().animatedScrollToMessage(firstUnreadId);
			}

			await this.getScrollManager().animatedScrollToMessage(firstUnreadId);
		},
		async onMentionsButtonClick(): void
		{
			if (this.isJumpingToAnchor)
			{
				return;
			}

			this.isJumpingToAnchor = true;
			await this.goToNearestMessageWithAnchor(AnchorType.mention);
			this.isJumpingToAnchor = false;
		},
		async onReactionsButtonClick(): void
		{
			if (this.isJumpingToAnchor)
			{
				return;
			}

			this.isJumpingToAnchor = true;
			await this.goToNearestMessageWithAnchor(AnchorType.reaction);
			this.isJumpingToAnchor = false;
		},
		async goToNearestMessageWithAnchor(anchorType: string)
		{
			const nextMessage: ?number = this.$store.getters['messages/anchors/getNextMessageIdWithAnchorType'](
				this.dialog.chatId,
				anchorType,
			);

			if (nextMessage)
			{
				await this.goToMessageContext(nextMessage, { position: ScrollManager.scrollPosition.messageTop });
			}
		},
		onWindowFocus()
		{
			this.windowFocused = true;
			this.readVisibleMessages();
		},
		onWindowBlur()
		{
			this.windowFocused = false;
		},
		onCallFold()
		{
			const callDialogId = CallManager.getInstance().getCurrentCallDialogId();
			if (callDialogId !== this.dialogId)
			{
				return;
			}
			this.readVisibleMessages();
		},
		async onShowQuoteButton(event: BaseEvent<{ message: ImModelMessage, event: MouseEvent }>)
		{
			const { message, event: $event } = event.getData();
			const permissionManager = PermissionManager.getInstance();
			if (!permissionManager.canPerformActionByRole(ActionByRole.send, this.dialogId))
			{
				return;
			}
			this.showQuoteButton = true;
			await this.$nextTick();
			this.$refs.quoteButton.onMessageMouseUp(message, $event);
		},
		async handleSecondScrollButtonClick()
		{
			this.getScrollManager().scrollButtonClicked = false;
			if (this.dialog.hasNextPage)
			{
				this.showLoadingBar();
				await this.getMessageService().loadContext(this.dialog.lastMessageId);
				this.hideLoadingBar();

				EventEmitter.emit(EventType.dialog.scrollToBottom, {
					chatId: this.dialog.chatId,
				});

				return;
			}

			void this.getScrollManager().animatedScrollToMessage(this.dialog.lastMessageId, { withDateOffset: false });
		},
		onShowForwardPopup(event: BaseEvent)
		{
			const { messagesIds } = event.getData();
			this.forwardPopup.messagesIds = messagesIds;
			this.forwardPopup.show = true;
		},
		onCloseForwardPopup()
		{
			this.forwardPopup.messagesIds = [];
			this.forwardPopup.show = false;
		},
		onMessageIsVisible(event: BaseEvent<{ messageId: number, dialogId: string }>)
		{
			const { messageId, dialogId } = event.getData();
			if (dialogId !== this.dialogId)
			{
				return;
			}

			this.getVisibleMessagesManager().setMessageAsVisible(messageId);

			if (this.isChatVisible() === false)
			{
				return;
			}

			if (this.$store.getters['messages/anchors/isMessageHasAnchors'](messageId))
			{
				this.readAnchorsIfMessageVisibleLongEnough(messageId);
			}

			const message: ImModelMessage = this.$store.getters['messages/getById'](messageId);
			if (!message.viewed)
			{
				this.messagesToRead.add(messageId);
				this.debouncedReadHandler();
			}
		},
		readAnchorsIfMessageVisibleLongEnough(messageId: number)
		{
			const messageVisibilityTimeThreshold = 200;

			if (this.getScrollManager().isScrolling)
			{
				this.readMessageAnchorsAfterVisibilityThreshold(messageId, messageVisibilityTimeThreshold);
			}
			else
			{
				this.getAnchorService().debouncedReadMessageAnchors(messageId);
			}
		},
		readMessageAnchorsAfterVisibilityThreshold(messageId: number, messageVisibilityTimeThreshold: number)
		{
			setTimeout(() => {
				if (this.getVisibleMessagesManager().getVisibleMessages().includes(messageId))
				{
					this.sequentiallyHighlightMessageHandler(messageId);
					this.getAnchorService().debouncedReadMessageAnchors(messageId);
				}
			}, messageVisibilityTimeThreshold);
		},
		onMessageIsNotVisible(event: BaseEvent<{ messageId: number, dialogId: string }>)
		{
			const { messageId, dialogId } = event.getData();
			if (dialogId !== this.dialogId)
			{
				return;
			}
			this.getVisibleMessagesManager().setMessageAsNotVisible(messageId);
		},
		/* endregion Event handlers */
		/* region Init methods */
		initContextMode()
		{
			const layoutManager = LayoutManager.getInstance();
			if (!layoutManager.isChatContextAvailable(this.dialogId))
			{
				return;
			}

			this.contextMode.active = true;
			// chat was loaded before, we didn't load context specifically
			// if chat wasn't loaded before - we load it with context
			this.contextMode.messageIsLoaded = !this.dialogInited;
		},
		getMessageService(): MessageService
		{
			if (!this.messageService)
			{
				this.messageService = new MessageService({ chatId: this.dialog.chatId });
			}

			return this.messageService;
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		getAnchorService(): AnchorService
		{
			if (!this.anchorService)
			{
				this.anchorService = new AnchorService();
			}

			return this.anchorService;
		},
		getScrollManager(): ScrollManager
		{
			if (!this.scrollManager)
			{
				this.scrollManager = new ScrollManager();
				this.scrollManager.subscribe(ScrollManager.events.onScrollTriggerUp, this.onScrollTriggerUp);
				this.scrollManager.subscribe(ScrollManager.events.onScrollTriggerDown, this.onScrollTriggerDown);
				this.scrollManager.subscribe(ScrollManager.events.onScrollThresholdPass, (event: BaseEvent<boolean>) => {
					this.isScrolledUp = event.getData();
				});
			}

			return this.scrollManager;
		},
		getPullWatchManager(): PullWatchManager
		{
			if (!this.pullWatchManager)
			{
				this.pullWatchManager = new PullWatchManager(this.dialogId);
			}

			return this.pullWatchManager;
		},
		getVisibleMessagesManager(): VisibleMessagesManager
		{
			if (!this.visibleMessagesManager)
			{
				this.visibleMessagesManager = new VisibleMessagesManager();
			}

			return this.visibleMessagesManager;
		},
		/* endregion Init methods */
		isChatVisible(): boolean
		{
			return this.windowFocused && !this.hasVisibleCall() && !this.hasCommentsOnTop;
		},
		hasVisibleCall(): boolean
		{
			return CallManager.getInstance().hasVisibleCall();
		},
		closeDialogPopups()
		{
			this.showQuoteButton = false;
			PopupManager.getPopupById(PopupType.dialogAvatarMenu)?.close();
			PopupManager.getPopupById(PopupType.dialogMessageMenu)?.close();
			PopupManager.getPopupById(PopupType.dialogReactionUsers)?.close();
			PopupManager.getPopupById(PopupType.dialogReadUsers)?.close();
			PopupManager.getPopupById(PopupType.messageBaseFileMenu)?.close();
		},
		subscribeToEvents()
		{
			EventEmitter.subscribe(EventType.dialog.scrollToBottom, this.onScrollToBottom);
			EventEmitter.subscribe(EventType.dialog.goToMessageContext, this.onGoToMessageContext);
			EventEmitter.subscribe(EventType.call.onFold, this.onCallFold);
			EventEmitter.subscribe(EventType.dialog.showForwardPopup, this.onShowForwardPopup);
			EventEmitter.subscribe(EventType.dialog.showQuoteButton, this.onShowQuoteButton);
			EventEmitter.subscribe(EventType.dialog.onMessageIsVisible, this.onMessageIsVisible);
			EventEmitter.subscribe(EventType.dialog.onMessageIsNotVisible, this.onMessageIsNotVisible);

			Event.bind(window, 'focus', this.onWindowFocus);
			Event.bind(window, 'blur', this.onWindowBlur);
		},
		unsubscribeFromEvents()
		{
			EventEmitter.unsubscribe(EventType.dialog.scrollToBottom, this.onScrollToBottom);
			EventEmitter.unsubscribe(EventType.dialog.goToMessageContext, this.onGoToMessageContext);
			EventEmitter.unsubscribe(EventType.call.onFold, this.onCallFold);
			EventEmitter.unsubscribe(EventType.dialog.showForwardPopup, this.onShowForwardPopup);
			EventEmitter.unsubscribe(EventType.dialog.showQuoteButton, this.onShowQuoteButton);
			EventEmitter.unsubscribe(EventType.dialog.onMessageIsVisible, this.onMessageIsVisible);
			EventEmitter.unsubscribe(EventType.dialog.onMessageIsNotVisible, this.onMessageIsNotVisible);

			Event.unbind(window, 'focus', this.onWindowFocus);
			Event.unbind(window, 'blur', this.onWindowBlur);
		},
		getContainer(): ?HTMLElement
		{
			return this.$refs.container;
		},
	},
	template: `
		<div class="bx-im-dialog-chat__block bx-im-dialog-chat__scope">
			<!-- Top -->
			<slot name="pinned-panel">
				<PinnedMessages
					v-if="pinnedMessages.length > 0"
					:dialogId="dialogId"
					:messages="pinnedMessages"
					@messageClick="onPinnedMessageClick"
					@messageUnpin="onPinnedMessageUnpin"
				/>
			</slot>
			<PullStatus/>
			<!-- Message list -->
			<div @scroll="onScroll" class="bx-im-dialog-chat__scroll-container" ref="container">
				<slot name="message-list">
					<MessageList :dialogId="dialogId" :containerHeight="containerHeight" />
				</slot>
			</div>
			<FloatButtons
				:dialogId="dialogId"
				:isScrolledUp="isScrolledUp"
				@scrollButtonClick="onScrollButtonClick"
				@reactionsButtonClick="onReactionsButtonClick"
				@mentionsButtonClick="onMentionsButtonClick"
			>
				<template #additional-float-button><slot name="additional-float-button" /></template>
			</FloatButtons>
			<!-- Absolute elements -->
			<ForwardPopup
				v-if="forwardPopup.show"
				:messagesIds="forwardPopup.messagesIds"
				:dialogId="dialogId"
				@close="onCloseForwardPopup"
			/>
			<Transition name="fade-up">
				<QuoteButton
					v-if="showQuoteButton"
					:dialogId="dialogId"
					@close="showQuoteButton = false" 
					class="bx-im-message-base__quote-button"
					ref="quoteButton"
				/>
			</Transition>
		</div>
	`,
};
