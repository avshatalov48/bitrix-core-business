import {Runtime, Event, Dom} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {PopupManager} from 'main.popup';

import {Core} from 'im.v2.application.core';
import {BaseMessage} from 'im.v2.component.message.base';
import {ChatCreationMessage} from 'im.v2.component.message.chat-creation';
import {Avatar, AvatarSize, ChatInfoPopup} from 'im.v2.component.elements';
import {Logger} from 'im.v2.lib.logger';
import {CallManager} from 'im.v2.lib.call';
import {Utils} from 'im.v2.lib.utils';
import {MessageService, ChatService} from 'im.v2.provider.service';
import {DialogBlockType as BlockType, EventType, PopupType, DialogScrollThreshold} from 'im.v2.const';

import {ScrollManager} from './classes/scroll-manager';
import {CollectionManager} from './classes/collection-manager';
import {MessageMenu} from './classes/message-menu';
import {AvatarMenu} from './classes/avatar-menu';
import {ObserverManager} from './classes/observer-manager';
import {QuoteManager} from './classes/quote-manager';
import {NewMessagesBlock} from './components/block/new-messages';
import {MarkedMessagesBlock} from './components/block/marked-messages';
import {DateGroupTitle} from './components/block/date-group';
import {PinnedMessages} from './components/pinned/pinned-messages';
import {DialogStatus} from './components/dialog-status';
import {DialogLoader} from './components/dialog-loader';
import './css/chat-dialog.css';

import type {ImModelMessage, ImModelDialog, ImModelLayout} from 'im.v2.model';
import type {ScrollToBottomEvent} from 'im.v2.const';
import type {FormattedCollectionItem} from './classes/collection-manager';

const FLOATING_DATE_OFFSET = 52;
const LOAD_MESSAGE_ON_EXIT_DELAY = 200;

// @vue/component
export const ChatDialog = {
	name: 'ChatDialog',
	components: {
		Avatar,
		BaseMessage,
		ChatCreationMessage,
		PinnedMessages,
		NewMessagesBlock,
		MarkedMessagesBlock,
		DateGroupTitle,
		ChatInfoPopup,
		DialogStatus,
		DialogLoader
	},
	directives: {
		'message-observer': {
			mounted(element, binding)
			{
				binding.instance.observerManager.observeMessage(element);
			},
			beforeUnmount(element, binding)
			{
				binding.instance.observerManager.unobserveMessage(element);
			}
		}
	},
	props: {
		dialogId: {
			type: String,
			default: ''
		},
		textareaHeight: {
			type: Number,
			default: 0
		}
	},
	data()
	{
		return {
			messageMenuIsActiveForId: 0,
			chatInfoPopup: {
				element: null,
				dialogId: 0,
				show: false
			},
			contextMode: {
				active: false,
				messageIsLoaded: false
			},
			initialScrollCompleted: false,
			isScrolledUp: false,
			windowFocused: false
		};
	},
	computed:
	{
		BlockType: () => BlockType,
		AvatarSize: () => AvatarSize,
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		formattedCollection(): FormattedCollectionItem[]
		{
			if (!this.dialogInited && this.messageCollection.length === 0)
			{
				return [];
			}

			return this.getCollectionManager().formatMessageCollection(this.messageCollection);
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

			return `${this.dialog.counter}`;
		},
		showDialogStatus(): boolean
		{
			return this.messageCollection.some((message) => {
				return message.id === this.dialog.lastMessageId;
			});
		}
	},
	watch:
	{
		dialogInited(newValue, oldValue)
		{
			if (!newValue || oldValue)
			{
				return false;
			}
			// first opening
			this.onChatInited();
		},
		textareaHeight()
		{
			if (this.isScrolledUp || !this.dialogInited)
			{
				return;
			}

			this.$nextTick(() => {
				this.getScrollManager().scrollToBottom();
			});
		}
	},
	created()
	{
		Logger.warn('Dialog: Chat created', this.dialogId);
		this.getCollectionManager();

		this.initContextMenu();
		this.initObserverManager();

		this.initContextMode();
	},
	mounted()
	{
		this.getScrollManager().setContainer(this.getContainer());
		if (this.dialogInited)
		{
			// second+ opening
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
		this.closeMessageMenu();
		this.unsubscribeFromEvents();
		if (this.dialogInited)
		{
			this.saveScrollPosition();
			this.loadMessagesOnExit();
		}
	},
	methods:
	{
		readVisibleMessages()
		{
			if (!this.dialogInited || !this.windowFocused || this.hasVisibleCall())
			{
				return;
			}

			this.getObserverManager().getMessagesToRead().forEach(messageId => {
				this.getChatService().readMessage(this.dialog.chatId, messageId);
				this.getObserverManager().onReadMessage(messageId);
			});
		},
		scrollOnStart()
		{
			this.$nextTick(() => {
				// we loaded chat with context
				if (this.contextMode.active && this.contextMode.messageIsLoaded)
				{
					this.getScrollManager().scrollToMessage(this.layout.contextId, -FLOATING_DATE_OFFSET);
					this.$nextTick(() => {
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
				else if (this.$store.getters['dialogues/getLastReadId'](this.dialogId))
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
		goToMessageContext(messageId: number): Promise
		{
			const hasMessage = this.$store.getters['messages/hasMessage']({
				chatId: this.dialog.chatId,
				messageId: messageId
			});
			if (hasMessage)
			{
				Logger.warn('Dialog: we have this message, scrolling to it', messageId);
				return this.getScrollManager().animatedScrollToMessage(messageId, -FLOATING_DATE_OFFSET)
					.then(() => {
						this.highlightMessage(messageId);
						return true;
					});
			}

			return this.getMessageService().loadContext(messageId)
				.then(() => {
					return this.$nextTick();
				})
				.then(() => {
					this.getScrollManager().scrollToMessage(messageId, -FLOATING_DATE_OFFSET);
					return this.$nextTick();
				})
				.then(() => {
					this.highlightMessage(messageId);
					return true;
				})
				.catch(error => {
					console.error('goToMessageContext error', error);
				});
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
			this.$store.dispatch('dialogues/update', {
				dialogId: this.dialogId,
				fields: {
					savedPositionMessageId
				}
			});
		},
		loadMessagesOnExit()
		{
			setTimeout(() => {
				this.getMessageService().reloadMessageList();
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
		initContextMenu()
		{
			this.messageMenu = new MessageMenu();
			this.messageMenu.subscribe(MessageMenu.events.onCloseMenu, () => {
				this.messageMenuIsActiveForId = 0;
			});

			this.avatarMenu = new AvatarMenu();
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
		getCollectionManager(): CollectionManager
		{
			if (!this.collectionManager)
			{
				this.collectionManager = new CollectionManager(this.dialogId);
			}

			return this.collectionManager;
		},
		getMessageService(): MessageService
		{
			if (!this.messageService)
			{
				this.messageService = new MessageService({chatId: this.dialog.chatId});
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
		/* endregion Init methods */
		/* region Event handlers */
		onChatInited()
		{
			if (!this.dialog.loading)
			{
				this.scrollOnStart();
				this.debouncedReadHandler();
				this.getObserverManager().setDialogInited(true);
			}

			this.$nextTick(() => {
				this.getChatService().clearDialogMark(this.dialogId);
			});

			EventEmitter.emit(EventType.dialog.onDialogInited, {dialogId: this.dialogId});
		},
		onScrollTriggerUp()
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
				return this.getMessageService().drawPreparedHistoryMessages().then(() => {
					this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
				});
			}

			// check if already loading or no more history
			if (this.getMessageService().isLoading() || !this.dialog.hasPrevPage)
			{
				return false;
			}

			// Load messages and save them
			this.getMessageService().loadHistory().then(() => {
				// Messages loaded and we are at the top
				if (this.getScrollManager().isAtTheTop())
				{
					Logger.warn('Dialog: we are at the top after history request, inserting messages');
					this.getMessageService().drawPreparedHistoryMessages().then(() => {
						this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
					});
				}
			});
		},
		onScrollTriggerDown()
		{
			if (!this.dialogInited || !this.getContainer())
			{
				return;
			}

			Logger.warn('Dialog: scroll triggered DOWN');
			// Insert messages if there are some
			if (this.getMessageService().hasPreparedUnreadMessages())
			{
				return this.getMessageService().drawPreparedUnreadMessages();
			}

			// check if already loading or no more history
			if (this.getMessageService().isLoading() || !this.dialog.hasNextPage)
			{
				return false;
			}

			// Load messages and save them
			this.getMessageService().loadUnread().then(() => {
				// Messages loaded and we are at the bottom
				if (this.getScrollManager().isAroundBottom())
				{
					Logger.warn('Dialog: we are at the bottom after unread request, inserting messages');
					this.getMessageService().drawPreparedUnreadMessages().then(() => {
						this.getScrollManager().checkIfChatIsScrolledUp();
					});
				}
			});
		},
		onScrollToBottom(event: BaseEvent<ScrollToBottomEvent>)
		{
			const {chatId, threshold = DialogScrollThreshold.halfScreenUp} = event.getData();
			if (this.dialog.chatId !== chatId)
			{
				return;
			}

			if (!this.windowFocused || this.hasVisibleCall())
			{
				const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
				this.$nextTick(() => {
					this.getScrollManager().scrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
				});
				return;
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

			this.$nextTick(() => {
				this.getScrollManager().animatedScrollToBottom();
			});
		},
		onGoToMessageContext(event: BaseEvent)
		{
			const {dialogId, messageId} = event.getData();
			if (this.dialog.dialogId !== dialogId)
			{
				return;
			}

			this.goToMessageContext(messageId);
		},
		onOpenChatInfo(event: BaseEvent)
		{
			const {dialogId, event: $event} = event.getData();
			this.chatInfoPopup.element = $event.target;
			this.chatInfoPopup.dialogId = dialogId;
			this.chatInfoPopup.show = true;
		},
		onPinnedMessageClick(messageId: number)
		{
			this.goToMessageContext(messageId);
		},
		onPinnedMessageUnpin(messageId: number)
		{
			this.getMessageService().unpinMessage(this.dialog.chatId, messageId);
		},
		onMessageContextMenuClick(event: {message: ImModelMessage, $event: PointerEvent})
		{
			const context = {dialogId: this.dialogId, ...event.message};
			this.messageMenu.openMenu(context, event.$event.currentTarget);
			this.messageMenuIsActiveForId = event.message.id;
		},
		onMessageQuote(event: {message: ImModelMessage})
		{
			const {message} = event;
			QuoteManager.sendQuoteEvent(message);
		},
		onScroll(event: Event)
		{
			this.closeDialogPopups();
			this.debouncedScrollHandler(event);
		},
		onScrollButtonClick()
		{
			if (this.getScrollManager().scrollButtonClicked)
			{
				this.handleSecondScrollButtonClick();
				return;
			}

			this.getScrollManager().scrollButtonClicked = true;
			if (this.dialog.counter === 0)
			{
				this.getMessageService().loadInitialMessages().then(() => {
					this.getScrollManager().scrollToBottom();
				});
				return;
			}

			const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
			if (!firstUnreadId)
			{
				this.getMessageService().loadInitialMessages().then(() => {
					this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
				});
			}

			this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
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
		onAvatarClick(dialogId: string, event: PointerEvent)
		{
			const user = this.$store.getters['users/get'](dialogId);
			const userId = Number.parseInt(dialogId, 10);
			if (!user || Core.getUserId() === userId)
			{
				return;
			}

			if (Utils.key.isAltOrOption(event))
			{
				EventEmitter.emit(EventType.textarea.insertMention, {
					mentionText: user.name,
					mentionReplacement: Utils.user.getMentionBbCode(user.id, user.name)
				});

				return;
			}

			this.avatarMenu.openMenu({user, dialog: this.dialog}, event.currentTarget);
		},
		handleSecondScrollButtonClick()
		{
			this.getScrollManager().scrollButtonClicked = false;
			if (this.dialog.hasNextPage)
			{
				this.getMessageService().loadContext(this.dialog.lastMessageId).then(() => {
					EventEmitter.emit(EventType.dialog.scrollToBottom, {
						chatId: this.dialog.chatId
					});
				}).catch(error => {
					console.error('ChatDialog: scroll to chat end loadContext error', error);
				});

				return;
			}

			this.getScrollManager().animatedScrollToMessage(this.dialog.lastMessageId);
		},
		/* endregion Event handlers */
		hasVisibleCall()
		{
			return CallManager.getInstance().hasVisibleCall();
		},
		closeDialogPopups()
		{
			this.closeMessageMenu();
			this.chatInfoPopup.show = false;
			this.avatarMenu.close();
			PopupManager.getPopupById(PopupType.dialogReactionUsers)?.close();
			PopupManager.getPopupById(PopupType.dialogReadUsers)?.close();
		},
		closeMessageMenu()
		{
			this.messageMenu.close();
			this.messageMenuIsActiveForId = 0;
		},
		subscribeToEvents()
		{
			EventEmitter.subscribe(EventType.dialog.scrollToBottom, this.onScrollToBottom);
			EventEmitter.subscribe(EventType.dialog.goToMessageContext, this.onGoToMessageContext);
			EventEmitter.subscribe(EventType.mention.openChatInfo, this.onOpenChatInfo);

			Event.bind(window, 'focus', this.onWindowFocus);
			Event.bind(window, 'blur', this.onWindowBlur);
		},
		unsubscribeFromEvents()
		{
			EventEmitter.unsubscribe(EventType.dialog.scrollToBottom, this.onScrollToBottom);
			EventEmitter.unsubscribe(EventType.dialog.goToMessageContext, this.onGoToMessageContext);
			EventEmitter.unsubscribe(EventType.mention.openChatInfo, this.onOpenChatInfo);

			Event.unbind(window, 'focus', this.onWindowFocus);
			Event.unbind(window, 'blur', this.onWindowBlur);
		},
		getContainer(): ?HTMLElement
		{
			return this.$refs['container'];
		}
	},
	template: `
		<div class="bx-im-dialog-chat__block bx-im-dialog-chat__scope">
			<PinnedMessages
				v-if="pinnedMessages.length > 0"
				:messages="pinnedMessages"
				@messageClick="onPinnedMessageClick"
				@messageUnpin="onPinnedMessageUnpin"
			/>
			<div @scroll="onScroll" class="bx-im-dialog-chat__scroll-container" ref="container">
				<div class="bx-im-dialog-chat__content">
					<!-- Loader -->
					<DialogLoader v-if="!dialogInited" :fullHeight="formattedCollection.length === 0" />
					<!-- Date groups -->
					<div v-for="dateGroup in formattedCollection" :key="dateGroup.date.id" class="bx-im-dialog-chat__date-group_container">
						<!-- Date mark -->
						<DateGroupTitle :title="dateGroup.date.title" />
						<!-- Single date group -->
						<template v-for="dateGroupItem in dateGroup.items">
							<!-- 'New messages' mark -->
							<MarkedMessagesBlock v-if="dateGroupItem.type === BlockType.markedMessages" data-id="newMessages" />
							<NewMessagesBlock v-else-if="dateGroupItem.type === BlockType.newMessages" data-id="newMessages" />
							<!-- Author group -->
							<div v-else-if="dateGroupItem.type === BlockType.authorGroup" :class="'--' + dateGroupItem.messageType" class="bx-im-dialog-chat__author-group_container">
								<!-- Author group avatar -->
								<div v-if="dateGroupItem.avatar.isNeeded" class="bx-im-dialog-chat__author-group_avatar">
									<Avatar
										:dialogId="dateGroupItem.avatar.avatarId"
										:size="AvatarSize.L"
										:withStatus="false"
										@click="onAvatarClick(dateGroupItem.avatar.avatarId, $event)"
									/>
								</div>
								<!-- Messages -->
								<div class="bx-im-dialog-chat__messages_container">
									<component
										v-for="(message, index) in dateGroupItem.items"
										v-message-observer
										:is="message.componentId"
										:withTitle="index === 0"
										:item="message"
										:dialogId="dialogId"
										:key="message.id"
										:menuIsActiveForId="messageMenuIsActiveForId"
										:withAvatar="dateGroupItem.avatar.isNeeded"
										:data-viewed="message.viewed"
										@contextMenuClick="onMessageContextMenuClick"
										@quoteMessage="onMessageQuote"
									>
									</component>
								</div>
							</div>
						</template>
					</div>
					<DialogStatus v-if="showDialogStatus" :dialogId="dialogId" />
				</div>
			</div>
			<Transition name="scroll-button-transition">
				<div v-if="isScrolledUp" @click="onScrollButtonClick" class="bx-im-dialog-chat__scroll-button">
					<div v-if="dialog.counter" class="bx-im-dialog-chat__scroll-button_counter">{{ formattedCounter }}</div>
				</div>
			</Transition>
			<ChatInfoPopup
				v-if="chatInfoPopup.show" 
				:dialogId="chatInfoPopup.dialogId"
				:bindElement="chatInfoPopup.element"
				:showPopup="chatInfoPopup.show"
				@close="chatInfoPopup.show = false"
			/>
		</div>
	`
};