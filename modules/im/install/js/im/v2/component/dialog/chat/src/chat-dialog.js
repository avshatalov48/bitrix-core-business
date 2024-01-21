import { Runtime, Event, Dom } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { PopupManager } from 'main.popup';
import { PullStatus } from 'pull.vue3.status';

import { MessageList } from 'im.v2.component.message-list';
import { ForwardPopup } from 'im.v2.component.entity-selector';
import { Logger } from 'im.v2.lib.logger';
import { CallManager } from 'im.v2.lib.call';
import { MessageService, ChatService } from 'im.v2.provider.service';
import {
	DialogBlockType as BlockType,
	EventType,
	PopupType,
	DialogScrollThreshold,
	UserRole,
} from 'im.v2.const';

import { ScrollManager } from './classes/scroll-manager';
import { ObserverManager } from './classes/observer-manager';
import { PullWatchManager } from './classes/pull-watch-manager';
import { PinnedMessages } from './components/pinned/pinned-messages';
import { QuoteButton } from './components/quote-button';
import './css/chat-dialog.css';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { ImModelMessage, ImModelChat, ImModelLayout } from 'im.v2.model';
import type { ScrollToBottomEvent } from 'im.v2.const';

const FLOATING_DATE_OFFSET = 52;
const LOAD_MESSAGE_ON_EXIT_DELAY = 200;

// @vue/component
export const ChatDialog = {
	name: 'ChatDialog',
	components: {
		MessageList,
		PinnedMessages,
		QuoteButton,
		PullStatus,
		ForwardPopup,
	},
	props: {
		dialogId: {
			type: String,
			default: '',
		},
		textareaHeight: {
			type: Number,
			default: 0,
		},
	},
	data(): Object
	{
		return {
			forwardPopup: {
				show: false,
				messageId: 0,
			},
			contextMode: {
				active: false,
				messageIsLoaded: false,
			},
			initialScrollCompleted: false,
			isScrolledUp: false,
			windowFocused: false,
			showQuoteButton: false,
			selectedText: null,
			quoteButtonStyles: {},
			quoteButtonMessage: 0,
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
			return this.$store.getters['messages/get'](this.dialog.chatId);
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
			const SCROLLING_DEBOUNCE_DELAY = 200;

			return Runtime.debounce(this.getScrollManager().onScroll, SCROLLING_DEBOUNCE_DELAY, this.getScrollManager());
		},
		debouncedReadHandler(): Function
		{
			return Runtime.debounce(this.readVisibleMessages, 50, this);
		},
		formattedCounter(): string
		{
			if (this.dialog.counter === 0)
			{
				return '';
			}

			if (this.dialog.counter > 99)
			{
				return '99+';
			}

			return String(this.dialog.counter);
		},
		messageListComponent(): BitrixVueComponentProps
		{
			return MessageList;
		},
		showScrollButton(): boolean
		{
			return this.isScrolledUp || this.dialog.hasNextPage;
		},
	},
	watch:
	{
		dialogInited(newValue, oldValue)
		{
			if (!newValue || oldValue)
			{
				return;
			}
			// first opening
			this.getPullWatchManager().onChatLoad();
			this.onChatInited();
		},
		textareaHeight()
		{
			if (this.isScrolledUp || !this.dialogInited)
			{
				return;
			}

			void this.$nextTick(() => {
				this.getScrollManager().scrollToBottom();
			});
		},
	},
	created()
	{
		Logger.warn('Dialog: Chat created', this.dialogId);
		this.initObserverManager();
		this.initContextMode();
	},
	mounted()
	{
		this.getScrollManager().setContainer(this.getContainer());
		if (this.dialogInited)
		{
			// second+ opening
			this.getPullWatchManager().onLoadedChatEnter();
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
			this.loadMessagesOnExit();
		}
		this.getPullWatchManager().onChatExit();
		this.closeDialogPopups();
		this.forwardPopup.show = false;
	},
	methods:
	{
		readVisibleMessages()
		{
			if (!this.dialogInited || !this.windowFocused || this.hasVisibleCall() || this.isGuest)
			{
				return;
			}

			this.getObserverManager().getMessagesToRead().forEach((messageId) => {
				this.getChatService().readMessage(this.dialog.chatId, messageId);
				this.getObserverManager().onReadMessage(messageId);
			});
		},
		scrollOnStart()
		{
			void this.$nextTick(() => {
				// we loaded chat with context
				if (this.contextMode.active && this.contextMode.messageIsLoaded)
				{
					this.getScrollManager().scrollToMessage(this.layout.contextId, -FLOATING_DATE_OFFSET);
					void this.$nextTick(() => {
						this.highlightMessage(this.layout.contextId);
					});
				}
				// chat was loaded before
				else if (this.contextMode.active && !this.contextMode.messageIsLoaded)
				{
					this.goToMessageContext(this.layout.contextId);
				}
				// marked message
				else if (this.dialog.markedId)
				{
					this.getScrollManager().scrollToMessage(BlockType.newMessages, -FLOATING_DATE_OFFSET);
				}
				// saved position
				else if (this.dialog.savedPositionMessageId)
				{
					Logger.warn('Dialog: saved scroll position, scrolling to', this.dialog.savedPositionMessageId);
					this.getScrollManager().scrollToMessage(this.dialog.savedPositionMessageId);
				}
				// unread message
				else if (this.$store.getters['chats/getLastReadId'](this.dialogId))
				{
					this.getScrollManager().scrollToMessage(BlockType.newMessages, -FLOATING_DATE_OFFSET);
				}
				// new chat with unread messages
				else if (this.$store.getters['messages/getFirstUnread'](this.dialog.chatId))
				{
					Logger.warn('Dialog: new chat with unread messages, dont scroll');
				}
				else
				{
					this.getScrollManager().scrollToBottom();
				}
			});
		},
		async goToMessageContext(messageId: number): void
		{
			const hasMessage = this.$store.getters['messages/hasMessage']({
				chatId: this.dialog.chatId,
				messageId,
			});
			if (hasMessage)
			{
				Logger.warn('Dialog: we have this message, scrolling to it', messageId);

				await this.getScrollManager().animatedScrollToMessage(messageId, -FLOATING_DATE_OFFSET);
				this.highlightMessage(messageId);

				return;
			}

			await this.getMessageService().loadContext(messageId)
				.catch((error) => {
					Logger.error('goToMessageContext error', error);
				});
			await this.$nextTick();
			this.getScrollManager().scrollToMessage(messageId, -FLOATING_DATE_OFFSET);
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
			let savedPositionMessageId = this.getObserverManager().getFirstVisibleMessage();
			if (this.getScrollManager().isAroundBottom())
			{
				savedPositionMessageId = 0;
			}
			this.$store.dispatch('chats/update', {
				dialogId: this.dialogId,
				fields: {
					savedPositionMessageId,
				},
			});
		},
		loadMessagesOnExit()
		{
			setTimeout(() => {
				void this.getMessageService().reloadMessageList();
			}, LOAD_MESSAGE_ON_EXIT_DELAY);
		},
		/* region Init methods */
		initContextMode()
		{
			if (!this.layout.contextId)
			{
				return;
			}

			this.contextMode.active = true;
			// chat was loaded before, we didn't load context specifically
			// if chat wasn't loaded before - we load it with context
			this.contextMode.messageIsLoaded = !this.dialogInited;
		},
		initObserverManager()
		{
			this.observerManager = new ObserverManager();
			this.observerManager.subscribe(ObserverManager.events.onMessageIsVisible, () => {
				this.debouncedReadHandler();
			});
		},
		getObserverManager(): ObserverManager
		{
			return this.observerManager;
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
		/* endregion Init methods */
		/* region Event handlers */
		onChatInited()
		{
			if (!this.dialog.loading)
			{
				this.scrollOnStart();
				this.readVisibleMessages();
				this.getObserverManager().setDialogInited(true);
			}

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
			await this.getMessageService().loadHistory();
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
			await this.getMessageService().loadUnread();
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
					this.getScrollManager().scrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);

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
				this.handleSecondScrollButtonClick();

				return;
			}

			this.getScrollManager().scrollButtonClicked = true;
			if (this.dialog.counter === 0)
			{
				await this.getMessageService().loadInitialMessages();
				this.getScrollManager().scrollToBottom();

				return;
			}

			const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
			if (!firstUnreadId)
			{
				await this.getMessageService().loadInitialMessages();
				await this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
			}

			await this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
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
		onChatClick(event: PointerEvent)
		{
			if (this.isGuest)
			{
				event.stopPropagation();
			}
		},
		async onShowQuoteButton(message: ImModelMessage, event: MouseEvent)
		{
			this.showQuoteButton = true;
			await this.$nextTick();
			this.$refs.quoteButton.onMessageMouseUp(message, event);
		},
		handleSecondScrollButtonClick()
		{
			this.getScrollManager().scrollButtonClicked = false;
			if (this.dialog.hasNextPage)
			{
				this.getMessageService().loadContext(this.dialog.lastMessageId).then(() => {
					EventEmitter.emit(EventType.dialog.scrollToBottom, {
						chatId: this.dialog.chatId,
					});
				}).catch((error) => {
					Logger.error('ChatDialog: scroll to chat end loadContext error', error);
				});

				return;
			}

			void this.getScrollManager().animatedScrollToMessage(this.dialog.lastMessageId);
		},
		/* endregion Event handlers */
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

			Event.bind(window, 'focus', this.onWindowFocus);
			Event.bind(window, 'blur', this.onWindowBlur);
		},
		unsubscribeFromEvents()
		{
			EventEmitter.unsubscribe(EventType.dialog.scrollToBottom, this.onScrollToBottom);
			EventEmitter.unsubscribe(EventType.dialog.goToMessageContext, this.onGoToMessageContext);
			EventEmitter.unsubscribe(EventType.call.onFold, this.onCallFold);
			EventEmitter.unsubscribe(EventType.dialog.showForwardPopup, this.onShowForwardPopup);

			Event.unbind(window, 'focus', this.onWindowFocus);
			Event.unbind(window, 'blur', this.onWindowBlur);
		},
		getContainer(): ?HTMLElement
		{
			return this.$refs.container;
		},
		onShowForwardPopup(event: BaseEvent)
		{
			const { messageId } = event.getData();
			this.forwardPopup.messageId = messageId;
			this.forwardPopup.show = true;
		},
		onCloseForwardPopup()
		{
			this.forwardPopup.messageId = 0;
			this.forwardPopup.show = false;
		},
	},
	template: `
		<div class="bx-im-dialog-chat__block bx-im-dialog-chat__scope">
			<PinnedMessages
				v-if="pinnedMessages.length > 0"
				:messages="pinnedMessages"
				@messageClick="onPinnedMessageClick"
				@messageUnpin="onPinnedMessageUnpin"
			/>
			<PullStatus/>
			<div @scroll="onScroll" @click.capture="onChatClick" class="bx-im-dialog-chat__scroll-container" ref="container">
				<component
					:is="messageListComponent"
					:dialogId="dialogId"
					:messages="messageCollection"
					:observer="getObserverManager()"
					@readMessages="debouncedReadHandler"
					@showQuoteButton="onShowQuoteButton"
				/>
			</div>
			<Transition name="scroll-button-transition">
				<div v-if="showScrollButton" @click="onScrollButtonClick" class="bx-im-dialog-chat__scroll-button">
					<div v-if="dialog.counter" class="bx-im-dialog-chat__scroll-button_counter">{{ formattedCounter }}</div>
				</div>
			</Transition>
			<ForwardPopup
				:showPopup="forwardPopup.show"
				:messageId="forwardPopup.messageId"
				@close="onCloseForwardPopup"
			/>
			<Transition name="fade-up">
				<QuoteButton 
					v-if="showQuoteButton" 
					ref="quoteButton"
					@close="showQuoteButton = false" 
					class="bx-im-message-base__quote-button" 
				/>
			</Transition>
		</div>
	`,
};
