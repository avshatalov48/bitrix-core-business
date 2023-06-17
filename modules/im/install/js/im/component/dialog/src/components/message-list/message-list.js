import 'ui.design-tokens';

import {BitrixVue} from 'ui.vue';
import {Vuex} from 'ui.vue.vuex';
import 'im.view.message';
import {
	DeviceType,
	DialogReferenceClassName,
	DialogType,
	DialogTemplateType as TemplateType,
	RestMethod,
	RestMethodHandler, EventType
} from "im.const";
import {Utils as MessengerUtils} from "im.lib.utils";
import {Animation} from "im.lib.animation";
import {Logger} from "im.lib.logger";
import 'main.polyfill.intersectionobserver';

import {EventEmitter} from 'main.core.events';

import './message-list.css';
import {ObserverType, RequestMode, DateFormat} from "./message-list-const";
import {Placeholder1} from './placeholders/placeholder-1';
import {Placeholder2} from './placeholders/placeholder-2';
import {Placeholder3} from './placeholders/placeholder-3';

const MessageList = {
	/**
	 * @emits EventType.dialog.readMessage
	 * @emits EventType.dialog.clickOnDialog
	 * @emits EventType.dialog.clickOnCommand
	 * @emits EventType.dialog.clickOnMention
	 * @emits EventType.dialog.clickOnReadList
	 */
	props:
	{
		userId: { type: Number, default: 0 },
		dialogId: { type: String, default: "0" },
		messageLimit: { type: Number, default: 50 },
		enableReadMessages: { type: Boolean, default: true },
		enableReactions: { type: Boolean, default: true },
		enableDateActions: { type: Boolean, default: true },
		enableCreateContent: { type: Boolean, default: true },
		enableGestureQuote: { type: Boolean, default: true },
		enableGestureQuoteFromRight: { type: Boolean, default: true },
		enableGestureMenu: { type: Boolean, default: false },
		showMessageUserName: { type: Boolean, default: true },
		showMessageAvatar: { type: Boolean, default: true },
		showMessageMenu: { type: Boolean, default: true },
	},
	components: {Placeholder1, Placeholder2, Placeholder3},
	data()
	{
		return {
			messagesSet: false,
			scrollAnimating: false,
			showScrollButton: false,
			captureMove: false,
			capturedMoveEvent: null,
			lastMessageId: null,

			isRequestingHistory: false,
			historyPagesRequested: 0,
			stopHistoryLoading: false,
			isRequestingUnread: false,
			unreadPagesRequested: 0,
			placeholderCount: 0,
			pagesLoaded: 0
		}
	},
	created()
	{
		Logger.warn('MessageList component is created');
		this.initParams();
		this.initEvents();
	},
	beforeDestroy()
	{
		this.observers = {};
		clearTimeout(this.scrollButtonShowTimeout);
		this.clearEvents();
	},
	mounted()
	{
		this.windowFocused = MessengerUtils.platform.isBitrixMobile()? true: document.hasFocus();
		this.getMessageIdsForPagination();
		this.scrollOnStart();
	},
	watch:
	{
		// after each dialog switch (without switching to loading state)
		// we reset messagesSet flag and run scroll on start routine
		dialogId(newValue, oldValue)
		{
			Logger.warn('new dialogId in message-list', newValue);
			this.messagesSet = false;
			this.$nextTick(() => {
				this.scrollOnStart();
			});
		}
	},
	computed:
	{
		TemplateType: () => TemplateType,
		ObserverType: () => ObserverType,
		DialogReferenceClassName: () => DialogReferenceClassName,
		localize()
		{
			return BitrixVue.getFilteredPhrases('IM_MESSENGER_DIALOG_', this);
		},
		dialog()
		{
			const dialog = this.$store.getters['dialogues/get'](this.dialogId);

			return dialog? dialog: this.$store.getters['dialogues/getBlank']();
		},
		chatId()
		{
			if (this.application)
			{
				return this.application.dialog.chatId;
			}
		},
		collection()
		{
			return this.$store.getters['messages/get'](this.chatId);
		},
		formattedCollection()
		{
			this.lastMessageId = 0; //used in readed status
			this.lastMessageAuthorId = 0; //used in readed status
			this.firstUnreadMessageId = 0;

			let lastAuthorId = 0; //used for delimeters
			const dateGroups = {}; //date grouping nodes
			const collection = []; //array to return

			this.collection.forEach(element =>
			{
				if (this.messagesSet && (this.lastHistoryMessageId === null || this.lastHistoryMessageId > element.id))
				{
					Logger.warn('setting new lastHistoryMessageId', element.id);
					this.lastHistoryMessageId = element.id;
				}

				this.lastMessageId = element.id;

				let group = this.getDateGroup(element.date);
				if (!dateGroups[group.title])
				{
					dateGroups[group.title] = group.id;
					collection.push(this.getDateGroupBlock(group.id, group.title));
				}
				else if (lastAuthorId !== element.authorId)
				{
					collection.push(this.getDelimiterBlock(element.id));
				}

				if (element.unread && !this.firstUnreadMessageId)
				{
					this.firstUnreadMessageId = element.id;
				}

				collection.push(element);
				lastAuthorId = element.authorId;
			});

			//remembering author of last message - used in readed status
			this.lastMessageAuthorId = lastAuthorId;

			return collection;
		},
		writingStatusText()
		{
			clearTimeout(this.scrollToTimeout);

			if (this.dialog.writingList.length === 0)
			{
				return '';
			}

			//scroll to bottom
			if (!this.scrollChangedByUser && !this.showScrollButton)
			{
				this.scrollToTimeout = setTimeout(() => this.animatedScrollToPosition({duration: 500}), 300);
			}

			const text = this.dialog.writingList.map(element => element.userName).join(', ');

			return this.localize['IM_MESSENGER_DIALOG_WRITES_MESSAGE'].replace('#USER#', text);
		},
		statusReaded()
		{
			clearTimeout(this.scrollToTimeout);

			if (this.dialog.readedList.length === 0)
			{
				return '';
			}

			let text = '';

			if (this.dialog.type === DialogType.private)
			{
				const record = this.dialog.readedList[0];
				if (
					record.messageId === this.lastMessageId
					&& record.userId !== this.lastMessageAuthorId
				)
				{
					const dateFormat = this.getDateFormat(DateFormat.readedTitle);
					const formattedDate = this.getDateObject().format(dateFormat, record.date)
					text = this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_USER'].replace('#DATE#', formattedDate);
				}
			}
			else
			{
				const readedList = this.dialog.readedList.filter(record => {
					return record.messageId === this.lastMessageId && record.userId !== this.lastMessageAuthorId;
				});
				if (readedList.length === 1)
				{
					text = this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT'].replace(
						'#USERS#', readedList[0].userName
					);
				}
				else if (readedList.length > 1)
				{
					text = this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT'].replace(
						'#USERS#',
						this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT_PLURAL']
							.replace('#USER#', readedList[0].userName)
							.replace('#COUNT#', readedList.length-1)
							.replace('[LINK]', '')
							.replace('[/LINK]', '')
					);
				}
			}

			if (!text)
			{
				return '';
			}

			//scroll to bottom
			if (!this.scrollChangedByUser && !this.showScrollButton)
			{
				this.scrollToTimeout = setTimeout(() => this.animatedScrollToPosition({duration: 500}), 300);
			}

			return text;
		},
		unreadCounter()
		{
			return this.dialog.counter > 99? 999: this.dialog.counter;
		},
		formattedUnreadCounter()
		{
			return this.unreadCounter > 99 ? '99+': this.unreadCounter;
		},
		scrollBlocked()
		{
			if (this.application.device.type !== DeviceType.mobile)
			{
				return false;
			}

			return this.scrollAnimating || this.captureMove;
		},
		isDarkBackground()
		{
			return this.application.options.darkBackground;
		},
		isMobile()
		{
			return this.application.device.type === DeviceType.mobile;
		},
		//new
		isRequestingData()
		{
			return this.isRequestingHistory || this.isRequestingUnread;
		},
		remainingHistoryPages()
		{
			return Math.ceil((this.dialog.messageCount - this.collection.length) / this.historyMessageLimit);
		},
		remainingUnreadPages()
		{
			// we dont use unread counter now - we reverted unread counter to be max at 99, so we dont know actual counter

			if (this.isLastIdInCollection)
			{
				return 0;
			}

			return Math.ceil((this.dialog.messageCount - this.collection.length) / this.unreadMessageLimit);
		},
		unreadInCollection()
		{
			return this.collection.filter(item => {
				return item.unread === true;
			});
		},
		isLastIdInCollection()
		{
			return this.collection.map(message => message.id).includes(this.dialog.lastMessageId);
		},
		showStatusPlaceholder()
		{
			return !this.writingStatusText && !this.statusReaded;
		},
		bodyClasses()
		{
			return [DialogReferenceClassName.listBody, {
				'bx-im-dialog-list-scroll-blocked': this.scrollBlocked,
				'bx-im-dialog-dark-background': this.isDarkBackground,
				'bx-im-dialog-mobile': this.isMobile,
			}];
		},
		...Vuex.mapState({
			application: state => state.application,
		})
	},
	methods:
	{
		/* region 01. Init and destroy */
		initParams()
		{
			this.placeholdersComposition = this.getPlaceholdersComposition();
			this.historyMessageLimit = 50;
			this.unreadMessageLimit = 50;
			this.showScrollButton = this.unreadCounter > 0;

			this.scrollingDownThreshold = 1000;
			this.scrollingUpThreshold = 1000;
			this.messageScrollOffset = 20;

			this.lastScroll = 0;
			this.scrollChangedByUser = false;
			this.scrollButtonDiff = 100;
			this.scrollButtonShowTimeout = null;
			this.scrollPositionChangeTime = new Date().getTime();
			this.lastRequestTime = new Date().getTime();

			this.observers = {};

			this.lastAuthorId = 0;
			this.lastHistoryMessageId = null;
			this.firstUnreadMessageId = null;
			this.lastUnreadMessageId = null;
			this.dateFormatFunction = null;
			this.cachedDateGroups = {};

			this.readMessageQueue = [];
			this.readMessageTarget = {};
			this.readVisibleMessagesDelayed = MessengerUtils.debounce(this.readVisibleMessages, 50, this);
			this.requestHistoryDelayed = MessengerUtils.debounce(this.requestHistory, 50, this);
		},
		initEvents()
		{
			EventEmitter.subscribe(EventType.dialog.scrollOnStart, this.onScrollOnStart);
			EventEmitter.subscribe(EventType.dialog.scrollToBottom, this.onScrollToBottom);
			EventEmitter.subscribe(EventType.dialog.readVisibleMessages, this.onReadVisibleMessages);
			EventEmitter.subscribe(EventType.dialog.newMessage, this.onNewMessage);
			EventEmitter.subscribe(EventType.dialog.requestUnread, this.onExternalUnreadRequest);
			EventEmitter.subscribe(EventType.dialog.messagesSet, this.onMessagesSet);
			EventEmitter.subscribe(EventType.dialog.beforeMobileKeyboard, this.onBeforeMobileKeyboard);

			window.addEventListener("orientationchange", this.onOrientationChange);
			window.addEventListener('focus', this.onWindowFocus);
			window.addEventListener('blur', this.onWindowBlur);

			BitrixVue.event.$on('bitrixmobile:controller:focus', this.onWindowFocus);
			BitrixVue.event.$on('bitrixmobile:controller:blur', this.onWindowBlur);
		},
		clearEvents()
		{
			EventEmitter.unsubscribe(EventType.dialog.scrollOnStart, this.onScrollOnStart);
			EventEmitter.unsubscribe(EventType.dialog.scrollToBottom, this.onScrollToBottom);
			EventEmitter.unsubscribe(EventType.dialog.readVisibleMessages, this.onReadVisibleMessages);
			EventEmitter.unsubscribe(EventType.dialog.newMessage, this.onNewMessage);
			EventEmitter.unsubscribe(EventType.dialog.requestUnread, this.onExternalUnreadRequest);
			EventEmitter.unsubscribe(EventType.dialog.messagesSet, this.onMessagesSet);
			EventEmitter.unsubscribe(EventType.dialog.beforeMobileKeyboard, this.onBeforeMobileKeyboard);

			window.removeEventListener("orientationchange", this.onOrientationChange);
			window.removeEventListener('focus', this.onWindowFocus);
			window.removeEventListener('blur', this.onWindowBlur);

			BitrixVue.event.$off('bitrixmobile:controller:focus', this.onWindowFocus);
			BitrixVue.event.$off('bitrixmobile:controller:blur', this.onWindowBlur);
		},
		/* endregion 01. Init and destroy */

		/* region 02. Event handlers */
		onDialogClick(event)
		{
			if (BitrixVue.testNode(event.target, {className: 'bx-im-message-command'}))
			{
				this.onCommandClick(event);
			}
			else if (BitrixVue.testNode(event.target, {className: 'bx-im-mention'}))
			{
				this.onMentionClick(event);
			}

			this.windowFocused = true;
			EventEmitter.emit(EventType.dialog.clickOnDialog, {event});
		},
		onDialogMove(event)
		{
			if (!this.captureMove)
			{
				return;
			}

			this.capturedMoveEvent = event;
		},
		onCommandClick(event)
		{
			let value = '';

			if (
				event.target.dataset.entity === 'send'
				|| event.target.dataset.entity === 'put'
			)
			{
				value = event.target.nextSibling.innerHTML;
			}
			else if (event.target.dataset.entity === 'call')
			{
				value = event.target.dataset.command;
			}

			EventEmitter.emit(EventType.dialog.clickOnCommand, {type: event.target.dataset.entity, value, event});
		},
		onMentionClick(event)
		{
			EventEmitter.emit(EventType.dialog.clickOnMention, {
				type: event.target.dataset.type,
				value: event.target.dataset.value,
				event
			});
		},
		onOrientationChange()
		{
			clearTimeout(this.scrollToTimeout);

			if (this.application.device.type !== DeviceType.mobile)
			{
				return false;
			}

			Logger.log('Orientation changed');

			if (!this.scrollChangedByUser)
			{
				this.scrollToTimeout = setTimeout(() => this.scrollToBottom({force: true}), 300);
			}
		},
		onWindowFocus()
		{
			this.windowFocused = true;
			this.readVisibleMessages();

			return true;
		},
		onWindowBlur()
		{
			this.windowFocused = false;
		},
		onScrollToBottom({data: event = {chatId: 0, force: false, cancelIfScrollChange: false, duration: null}} = {})
		{
			if (event.chatId !== this.chatId)
			{
				return false;
			}

			Logger.warn('onScrollToBottom', event);
			event.force = event.force === true;
			event.cancelIfScrollChange = event.cancelIfScrollChange === true;

			if (this.firstUnreadMessageId)
			{
				Logger.warn('Dialog.onScrollToBottom: canceled - unread messages');
				return false;
			}

			if (event.cancelIfScrollChange && this.scrollChangedByUser && this.scrollBeforeMobileKeyboard)
			{
				const body = this.$refs.body;
				this.scrollAfterMobileKeyboard = body.scrollHeight - body.scrollTop - body.clientHeight;
				const scrollDiff = this.scrollAfterMobileKeyboard - this.scrollBeforeMobileKeyboard;
				this.animatedScrollToPosition({start: body.scrollTop, end: body.scrollTop + scrollDiff});

				return true;
			}

			this.scrollToBottom(event);

			return true;
		},
		onReadVisibleMessages({data: event = {chatId: 0}} = {})
		{
			if (event.chatId !== this.chatId)
			{
				return false;
			}
			Logger.warn('onReadVisibleMessages');

			this.readVisibleMessagesDelayed();

			return true;
		},
		onClickOnReadList(event)
		{
			const readedList = this.dialog.readedList.filter(record => {
				return record.messageId === this.lastMessageId && record.userId !== this.lastMessageAuthorId;
			});
			EventEmitter.emit(EventType.dialog.clickOnReadList, {list: readedList, event});
		},
		onDragMessage(event)
		{
			if (!this.windowFocused)
			{
				return false;
			}
			this.captureMove = event.result;

			if (!event.result)
			{
				this.capturedMoveEvent = null;
			}
		},
		onScroll(event)
		{
			if (this.isScrolling)
			{
				return false;
			}

			clearTimeout(this.scrollToTimeout);

			this.currentScroll = event.target.scrollTop;
			const isScrollingDown = this.lastScroll < this.currentScroll;
			const isScrollingUp = !isScrollingDown;

			if (isScrollingUp && this.scrollButtonClicked)
			{
				Logger.warn('scrollUp - reset scroll button clicks');
				this.scrollButtonClicked = false;
			}

			const leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
			if (this.currentScroll > 0 && isScrollingDown && leftSpaceBottom < this.scrollingDownThreshold)
			{
				this.onScrollDown();
			}
			else if (isScrollingUp && this.currentScroll <= this.scrollingUpThreshold)
			{
				this.onScrollUp();
			}

			//remember current scroll to compare with new ones
			this.lastScroll = this.currentScroll;
			this.scrollPositionChangeTime = new Date().getTime();
			//show or hide scroll button
			this.manageScrollButton(event);
		},
		onScrollDown()
		{
			if (!this.messagesSet || this.isLastIdInCollection)
			{
				return false;
			}
			// Logger.warn('---');
			// Logger.warn('Want to load unread');
			// Logger.warn('this.isRequestingData', this.isRequestingData);
			// Logger.warn('this.unreadPagesRequested', this.unreadPagesRequested);
			// Logger.warn('this.remainingUnreadPages', this.remainingUnreadPages);
			if (this.isRequestingData && this.remainingUnreadPages > 0)
			{
				this.drawPlaceholders(RequestMode.unread).then(() => {
					this.unreadPagesRequested += 1;
					Logger.warn('Already loading! Draw placeholders and add request, total - ', this.unreadPagesRequested);
				});
			}
			else if (!this.isRequestingData && this.remainingUnreadPages > 0)
			{
				Logger.warn('Starting new unread request');
				this.isRequestingUnread = true;

				this.drawPlaceholders(RequestMode.unread).then(() => {
					this.requestUnread();
				});
			}
		},
		onScrollUp()
		{
			if (!this.messagesSet || this.stopHistoryLoading)
			{
				return false;
			}

			this.projectedPagesToLoad = 1

			//draw 3 sets of placeholders if we are close to top of container
			if (!this.isMobile && this.$refs.body.scrollTop < this.$refs.body.scrollHeight / 4)
			{
				this.projectedPagesToLoad = 3;
			}

			// Logger.warn('---');
			// Logger.warn('Want to load history');
			// Logger.warn('this.isRequestingData', this.isRequestingData);
			// Logger.warn('this.historyPagesRequested', this.historyPagesRequested);
			// Logger.warn('this.remainingHistoryPages', this.remainingHistoryPages);
			if (this.isRequestingData && this.remainingHistoryPages > 0)
			{
				const currentBodyHeight = this.$refs.body.scrollHeight;
				this.drawPlaceholders(RequestMode.history, this.projectedPagesToLoad).then(() => {
					if (!this.isOverflowAnchorSupported())
					{
						this.enableUserScroll();
					}
					this.historyPagesRequested += this.projectedPagesToLoad;
					Logger.warn('Already loading! Draw placeholders and add request, total - ', this.historyPagesRequested);
				});
				if (!this.isOverflowAnchorSupported())
				{
					Logger.warn('Disabling user scroll');
					this.$nextTick(() => {
						const heightDifference = this.$refs.body.scrollHeight - currentBodyHeight;
						this.disableUserScroll();
						this.forceScrollToPosition(this.$refs.body.scrollTop + heightDifference);
					});
				}
			}
			else if (!this.isRequestingData && this.remainingHistoryPages > 0)
			{
				Logger.warn('Starting new history request');
				this.isRequestingHistory = true;

				const currentBodyHeight = this.$refs.body.scrollHeight;
				this.drawPlaceholders(RequestMode.history, this.projectedPagesToLoad).then(() => {
					this.historyPagesRequested = this.projectedPagesToLoad - 1;
					if (!this.isOverflowAnchorSupported())
					{
						this.enableUserScroll();
					}
					this.requestHistory();
				});
				//will run right after drawing placeholders, before .then()
				if (!this.isOverflowAnchorSupported())
				{
					Logger.warn('Disabling user scroll');
					this.$nextTick(() => {
						const heightDifference = this.$refs.body.scrollHeight - currentBodyHeight;
						this.disableUserScroll();
						this.forceScrollToPosition(this.$refs.body.scrollTop + heightDifference);
					});
				}
			}
		},
		//TODO: move
		isOverflowAnchorSupported()
		{
			return !MessengerUtils.platform.isBitrixMobile()
				&& !MessengerUtils.browser.isIe()
				&& !MessengerUtils.browser.isSafari()
				&& !MessengerUtils.browser.isSafariBased();
		},
		disableUserScroll()
		{
			this.$refs.body.classList.add('bx-im-dialog-list-scroll-blocked');
		},
		enableUserScroll()
		{
			this.$refs.body.classList.remove('bx-im-dialog-list-scroll-blocked');
		},
		onScrollButtonClick()
		{
			Logger.warn('Scroll button click', this.scrollButtonClicked);
			// TODO: now we just do nothing if button was clicked during data request (history or unread)
			if (this.isRequestingData)
			{
				return false;
			}

			//we dont have unread - just scroll to bottom
			if (this.unreadCounter === 0)
			{
				this.scrollToBottom();

				return true;
			}

			//it's a second click on button - scroll to last page if we have one
			if (this.scrollButtonClicked && this.remainingUnreadPages > 0)
			{
				Logger.warn('Second click on scroll button');
				this.scrollToLastPage();

				return true;
			}

			//it's a first click - just set the flag and move on
			this.scrollButtonClicked = true;
			this.scrollToBottom();
		},
		onNewMessage({data: {chatId, messageId}})
		{
			if (chatId !== this.chatId)
			{
				return false;
			}
			Logger.warn('Received new message from pull', messageId);
			if (this.showScrollButton)
			{
				return false;
			}
			this.$nextTick(() => {
				//non-focus handling
				if (!this.windowFocused)
				{
					const availableScrollHeight = this.$refs['body'].scrollHeight - this.$refs['body'].clientHeight;
					if (this.currentScroll < availableScrollHeight)
					{
						//show scroll button when out of focus and all visible space is filled with unread messaages already
						this.showScrollButton = true;
					}

					this.scrollToFirstUnreadMessage();

					return true;
				}

				//big message handling
				const messageElement = this.getElementById(messageId);
				if (!messageElement)
				{
					return false;
				}
				//if big message - scroll to top of it
				const body = this.$refs.body;
				if (messageElement.clientHeight > body.clientHeight)
				{
					this.scrollToMessage({messageId});

					return true;
				}
				//else - scroll to bottom
				this.animatedScrollToPosition();
			});
		},
		onMessagesSet({data: event})
		{
			if (event.chatId !== this.chatId)
			{
				return false;
			}

			if (this.messagesSet === true)
			{
				Logger.warn('messages are already set');
				return false;
			}

			Logger.warn('onMessagesSet', event.chatId);
			this.messagesSet = true;
			let force = false;
			//if we are in top half of container - force scroll to first unread, else - animated scroll
			if (this.$refs.body.scrollTop < this.$refs.body.scrollHeight / 2)
			{
				force = true;
			}
			this.scrollToBottom({force, cancelIfScrollChange: false});
		},
		onBeforeMobileKeyboard({data: event})
		{
			const body = this.$refs.body;
			this.scrollBeforeMobileKeyboard = body.scrollHeight - body.scrollTop - body.clientHeight;
		},
		onExternalUnreadRequest({data: event = {chatId: 0}} = {})
		{
			if (event.chatId !== this.chatId)
			{
				return false;
			}

			Logger.warn('onExternalUnreadRequest');
			this.isRequestingUnread = true;

			this.drawPlaceholders(RequestMode.unread).then(() => {
				return this.requestUnread();
			});

			this.externalUnreadRequestResolve = null;

			return new Promise((resolve, reject) => {
				this.externalUnreadRequestResolve = resolve;
			});
		},
		onScrollOnStart({data: event})
		{
			if (event.chatId !== this.chatId)
			{
				return false;
			}
			this.scrollOnStart({force: false});
		},
		/* endregion 02. Event handlers */

		/* region 03. Scrolling */
		scrollOnStart({force = true} = {})
		{
			Logger.warn('scrolling on start of dialog');
			const unreadId = this.getFirstUnreadMessage();
			if (unreadId)
			{
				this.scrollToFirstUnreadMessage(unreadId, force)
			}
			else
			{
				const body = this.$refs.body;
				this.forceScrollToPosition(body.scrollHeight - body.clientHeight);
			}
		},
		//scroll to first unread if counter > 0, else scroll to bottom
		scrollToBottom({force = false, cancelIfScrollChange = false, duration = null} = {})
		{
			Logger.warn('scroll to bottom', force, cancelIfScrollChange, duration);
			if (cancelIfScrollChange && this.scrollChangedByUser)
			{
				return false;
			}

			const body = this.$refs.body;

			//scroll to first unread message if there are unread messages
			if (this.dialog.counter > 0)
			{
				const scrollToMessageId = this.dialog.counter > 1 && this.firstUnreadMessageId? this.firstUnreadMessageId: this.lastMessageId;
				this.scrollToFirstUnreadMessage(scrollToMessageId, force);

				return true;
			}

			//hide scroll button because we will scroll to bottom
			this.showScrollButton = false;

			//without animation
			if (force)
			{
				this.forceScrollToPosition(body.scrollHeight - body.clientHeight);
			}
			//with animation
			else
			{
				const scrollParams = {};
				if (duration)
				{
					scrollParams.duration = duration;
				}
				this.animatedScrollToPosition({ ...scrollParams });
			}
		},
		scrollToFirstUnreadMessage(unreadId = null, force = false)
		{
			Logger.warn('scroll to first unread');

			let element = false;
			if (unreadId !== null)
			{
				element = this.getElementById(unreadId);
			}
			if (!element)
			{
				unreadId = this.getFirstUnreadMessage();
			}

			this.scrollToMessage({messageId: unreadId, force});
		},
		//scroll to message - can be set at the top or at the bottom of screen
		scrollToMessage({messageId = 0, force = false, stickToTop = true})
		{
			Logger.warn('scroll to message');
			const body = this.$refs.body;
			const element = this.getElementById(messageId);

			let end = 0;
			if (!element)
			{
				//if no element found in DOM - scroll to top
				if (stickToTop)
				{
					end = 10;
				}
				//if no element and stickToTop = false - scroll to bottom
				else
				{
					end = body.scrollHeight - body.clientHeight;
				}
			}
			else if (stickToTop)
			{
				//message will be at the top of screen (+little offset)
				end = element.offsetTop - (this.messageScrollOffset / 2);
			}
			else
			{
				//message will be at the bottom of screen (+little offset)
				end = element.offsetTop + element.offsetHeight - body.clientHeight + (this.messageScrollOffset / 2);
			}

			if (force)
			{
				this.forceScrollToPosition(end);
			}
			else
			{
				this.animatedScrollToPosition({end});
			}

			return true;
		},
		forceScrollToPosition(position)
		{
			Logger.warn('Force scroll to position - ', position);
			let body = this.$refs.body;
			if (!body)
			{
				return false;
			}

			if (this.animateScrollId)
			{
				Animation.cancel(this.animateScrollId);
				this.scrollAnimating = false;
				this.animateScrollId = null;
			}

			body.scrollTop = position;
		},
		//scroll to provided position with animation, by default - to the bottom
		animatedScrollToPosition(params = {})
		{
			Logger.warn('Animated scroll to - ', params);
			if (this.animateScrollId)
			{
				Animation.cancel(this.animateScrollId);
				this.scrollAnimating = false;
			}
			if (typeof params === 'function')
			{
				params = {callback: params};
			}

			const body = this.$refs.body;
			if (!body)
			{
				if (params.callback && typeof params.callback === 'function')
				{
					params.callback();
				}
				this.animateScrollId = null;
				this.scrollAnimating = false;

				return true;
			}

			if (
				MessengerUtils.platform.isIos() && (
					MessengerUtils.platform.getIosVersion() > 12
					&& MessengerUtils.platform.getIosVersion() < 13.2
				)
			)
			{
				body.scrollTop = body.scrollHeight - body.clientHeight;

				return true;
			}

			let {
				start = body.scrollTop,
				end = body.scrollHeight - body.clientHeight,
				increment = 20,
				callback,
				duration = 500
			} = params;

			const container = this.$refs.container;
			if (container && (end - start) > container.offsetHeight * 3)
			{
				start = end - container.offsetHeight * 3;
				Logger.warn('Dialog.animatedScroll: Scroll trajectory has been reduced');
			}

			this.scrollAnimating = true;
			Logger.warn('Dialog.animatedScroll: User scroll blocked while scrolling');

			this.animateScrollId = Animation.start({
				start,
				end,
				increment,
				duration,

				element: body,
				elementProperty: 'scrollTop',

				callback: () =>
				{
					this.animateScrollId = null;
					this.scrollAnimating = false;
					if (callback && typeof callback === 'function')
					{
						callback();
					}
				},
			});
		},
		/* endregion 03. Scrolling */

		/* region 04. Placeholders */
		drawPlaceholders(requestMode, pagesCount = 1)
		{
			const limit = requestMode === RequestMode.history? this.historyMessageLimit: this.unreadMessageLimit;
			const placeholders = this.generatePlaceholders(limit, pagesCount);

			return this.$store.dispatch('messages/addPlaceholders', {placeholders, requestMode});
		},
		generatePlaceholders(amount, pagesCount)
		{
			const placeholders = [];

			for (let i = 0; i < pagesCount; i++)
			{
				for (let j = 0; j < this.placeholdersComposition.length; j++)
				{
					placeholders.push({
						id: `placeholder${this.placeholderCount}`,
						chatId: this.chatId,
						templateType: TemplateType.placeholder,
						placeholderType: this.placeholdersComposition[j],
						unread: false
					});
					this.placeholderCount++;
				}
			}


			return placeholders;
		},
		getPlaceholdersComposition()
		{
			//randomize set of placeholder types (sums up to ~2400px height)
			//placeholder1 x8, placeholder2 x6, placeholder3 x8
			return [1,1,1,1,1,1,1,1,2,2,2,2,2,2,3,3,3,3,3,3,3,3].sort(() => {
				return 0.5 - Math.random();
			});
		},
		/* endregion 04. Placeholders */

		/* region 05. History request */
		requestHistory()
		{
			return this.$Bitrix.RestClient.get().callMethod(RestMethod.imDialogMessagesGet, {
				chat_id: this.chatId,
				last_id: this.lastHistoryMessageId,
				limit: this.historyMessageLimit,
				convert_text: 'Y'
			}).then(result => {
				const newMessages = result.data().messages;
				if (newMessages.length > 0)
				{
					this.lastHistoryMessageId = newMessages[newMessages.length - 1].id;
				}

				if (newMessages.length < this.historyMessageLimit)
				{
					this.stopHistoryLoading = true;
				}

				//files and users
				this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imDialogMessagesGet, result);

				return new Promise((resolve, reject) => {
					const currentBodyHeight = this.$refs.body.scrollHeight;
					this.$store.dispatch('messages/updatePlaceholders', {
						chatId: this.chatId,
						data: newMessages,
						firstMessage: this.pagesLoaded * this.placeholdersComposition.length,
						amount: this.placeholdersComposition.length
					}).then(() => {
						if (!this.isOverflowAnchorSupported())
						{
							this.enableUserScroll();
						}
						resolve();
					});
					if (!this.isOverflowAnchorSupported())
					{
						Logger.warn('Disabling user scroll in updating placeholders');
						this.$nextTick(() => {
							const heightDifference = this.$refs.body.scrollHeight - currentBodyHeight;
							this.disableUserScroll();
							this.forceScrollToPosition(this.$refs.body.scrollTop + heightDifference);
						});
					}
				});
			}).then(() => {
				this.pagesLoaded += 1;
				Logger.warn('History page loaded. Total loaded - ', this.pagesLoaded);

				return this.onAfterHistoryRequest();
			}).catch(result => {
				Logger.warn('Request history error', result);
			});
		},
		onAfterHistoryRequest()
		{
			Logger.warn('onAfterHistoryRequest');
			if (this.stopHistoryLoading)
			{
				Logger.warn('stopHistoryLoading, deleting all delayed requests');
				this.historyPagesRequested = 0;
			}

			if (this.historyPagesRequested > 0)
			{
				Logger.warn('We have delayed requests -', this.historyPagesRequested);
				this.historyPagesRequested--;

				return this.requestHistory();
			}
			else if (this.$refs.body.scrollTop <= this.scrollingUpThreshold && this.remainingHistoryPages > 0)
			{
				Logger.warn('currentScroll <= scrollingUpThreshold, requesting next page and scrolling');

				return this.drawPlaceholders(RequestMode.history).then((firstPlaceholderId) => {
					this.scrollToMessage({messageId: firstPlaceholderId, force: true, stickToTop: false});

					return this.requestHistory();
				});
			}
			else
			{
				Logger.warn('No more delayed requests, clearing placeholders');
				this.$store.dispatch('messages/clearPlaceholders', {chatId: this.chatId});
				this.isRequestingHistory = false;

				return true;
			}
		},
		/* endregion 05. History request */

		/* region 06. Unread request */
		prepareUnreadRequestParams()
		{
			return {
				[RestMethodHandler.imDialogRead]: [RestMethod.imDialogRead, {
					dialog_id: this.dialogId,
					message_id: this.lastUnreadMessageId
				}],
				[RestMethodHandler.imChatGet]: [RestMethod.imChatGet, {
					dialog_id: this.dialogId
				}],
				[RestMethodHandler.imDialogMessagesGetUnread]: [RestMethod.imDialogMessagesGet, {
					chat_id: this.chatId,
					first_id: this.lastUnreadMessageId,
					limit: this.unreadMessageLimit,
					convert_text: 'Y'
				}]
			};
		},
		requestUnread()
		{
			if (!this.lastUnreadMessageId)
			{
				this.lastUnreadMessageId = this.$store.getters['messages/getLastId'](this.chatId);
			}
			if (!this.lastUnreadMessageId)
			{
				return false;
			}

			EventEmitter.emitAsync(EventType.dialog.readMessage, {
				id: this.lastUnreadMessageId,
				skipTimer: true,
				skipAjax: true
			}).then(() => {
				this.$Bitrix.RestClient.get().callBatch(
					this.prepareUnreadRequestParams(),
					response => this.onUnreadRequest(response)
				);
			});
		},
		onUnreadRequest(response)
		{
			if (!response)
			{
				Logger.warn('Unread request: callBatch error');

				return false;
			}

			const chatGetResult = response[RestMethodHandler.imChatGet];
			if (chatGetResult.error())
			{
				Logger.warn('Unread request: imChatGet error', chatGetResult.error());

				return false;
			}
			this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imChatGet, chatGetResult);

			const dialogMessageUnread = response[RestMethodHandler.imDialogMessagesGetUnread];
			if (dialogMessageUnread.error())
			{
				Logger.warn('Unread request: imDialogMessagesGetUnread error', dialogMessageUnread.error());

				return false;
			}

			const newMessages = dialogMessageUnread.data().messages;
			if (newMessages.length > 0)
			{
				this.lastUnreadMessageId = newMessages[newMessages.length - 1].id;
			}

			this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imDialogMessagesGetUnread, dialogMessageUnread);
			this.$store.dispatch('messages/updatePlaceholders', {
				chatId: this.chatId,
				data: newMessages,
				firstMessage: this.pagesLoaded * this.placeholdersComposition.length,
				amount: this.placeholdersComposition.length
			}).then(() => {
				this.pagesLoaded += 1;
				Logger.warn('Unread page loaded. Total loaded - ', this.pagesLoaded);

				return this.onAfterUnreadRequest();
			}).catch(result => {
				Logger.warn('Unread history error', result);
			});
		},
		onAfterUnreadRequest()
		{
			if (this.unreadPagesRequested > 0)
			{
				Logger.warn('We have delayed requests -', this.unreadPagesRequested);
				this.unreadPagesRequested--;

				return this.requestUnread();
			}
			else
			{
				Logger.warn('No more delayed requests, clearing placeholders');
				this.$store.dispatch('messages/clearPlaceholders', {chatId: this.chatId});
				this.isRequestingUnread = false;

				if (this.externalUnreadRequestResolve)
				{
					this.externalUnreadRequestResolve();
				}

				return true;
			}
		},
		/* endregion 06. Unread request */

		/* region 07. Last page request */
		scrollToLastPage()
		{
			Logger.warn('Load last page');
			//draw placeholders at the bottom
			this.drawPlaceholders(RequestMode.unread).then(() => {
				//block unread and history requests
				this.isScrolling = true;
				this.animatedScrollToPosition({
					callback: () => this.onScrollToLastPage()
				});
			});
		},
		onScrollToLastPage()
		{
			//hide scroll button
			this.showScrollButton = false;
			//set counter to 0
			this.$store.dispatch('dialogues/update', {
				dialogId: this.dialogId,
				fields: {
					counter: 0
				}
			});
			//clear all messages except placeholders
			this.$store.dispatch('messages/clear', {chatId: this.chatId, keepPlaceholders: true});
			//call batch - imDialogRead, imChatGet, imDialogMessagesGet
			this.$Bitrix.RestClient.get().callBatch(
				this.prepareLastPageRequestParams(),
				response => this.onLastPageRequest(response)
			);
		},
		prepareLastPageRequestParams()
		{
			return {
				[RestMethodHandler.imDialogRead]: [RestMethod.imDialogRead, {
					dialog_id: this.dialogId
				}],
				[RestMethodHandler.imChatGet]: [RestMethod.imChatGet, {
					dialog_id: this.dialogId
				}],
				[RestMethodHandler.imDialogMessagesGet]: [RestMethod.imDialogMessagesGet, {
					chat_id: this.chatId,
					limit: this.unreadMessageLimit,
					convert_text: 'Y'
				}]
			};
		},
		onLastPageRequest(response)
		{
			if (!response)
			{
				Logger.warn('Last page request: callBatch error');
				return false;
			}

			//imChatGet handle
			const chatGetResult = response[RestMethodHandler.imChatGet];
			if (chatGetResult.error())
			{
				Logger.warn('Last page request: imChatGet error', chatGetResult.error());
				return false;
			}
			this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imChatGet, chatGetResult);

			//imDialogMessagesGet handle
			const lastPageMessages = response[RestMethodHandler.imDialogMessagesGet];
			if (lastPageMessages.error())
			{
				Logger.warn('Last page request: imDialogMessagesGet error', lastPageMessages.error());
				return false;
			}

			const newMessages = lastPageMessages.data().messages.reverse();
			//handle files and users
			this.$Bitrix.Data.get('controller').executeRestAnswer(RestMethodHandler.imDialogMessagesGet, lastPageMessages);
			//update placeholders to real messages
			this.$store.dispatch('messages/updatePlaceholders', {
				chatId: this.chatId,
				data: newMessages,
				firstMessage: this.pagesLoaded * this.placeholdersComposition.length,
				amount: this.placeholdersComposition.length
			}).then(() => {
				//get id for history requests and increase pages counter to count placeholders on next requests
				this.lastHistoryMessageId = this.collection[0].id;
				this.pagesLoaded += 1;

				//clear remaining placeholders
				return this.$store.dispatch('messages/clearPlaceholders', {chatId: this.chatId});
			}).then(() => {
				this.scrollToBottom({force: true});
				//enable history requests on scroll up
				this.stopHistoryLoading = false;
				this.isScrolling = false;
			}).catch(result => {
				Logger.warn('Unread history error', result);
			});
		},
		/* endregion 07. Last page request */

		/* region 08. Read messages */
		readVisibleMessages()
		{
			if (!this.windowFocused || !this.messagesSet)
			{
				Logger.warn('reading is disabled!');

				return false;
			}

			//need to filter that way to empty array after async method on every element was completed
			this.readMessageQueue = this.readMessageQueue.filter(messageId =>
			{
				if (this.readMessageTarget[messageId])
				{
					if (this.observers[ObserverType.read])
					{
						this.observers[ObserverType.read].unobserve(this.readMessageTarget[messageId]);
					}
					delete this.readMessageTarget[messageId];
				}

				this.requestReadVisibleMessages(messageId);

				return false;
			});
		},
		requestReadVisibleMessages(messageId)
		{
			EventEmitter.emit(EventType.dialog.readMessage, {id: messageId});
		},
		/* endregion 08. Read messages */

		/* region 09. Helpers */
		getMessageIdsForPagination()
		{
			// console.warn('this.collection.length', this.collection.length);
			// if (this.collection.length > 0)
			// {
			// 	console.warn('this.collection.length', this.collection[0].id);
			// 	this.lastHistoryMessageId = this.collection[0].id;
			// }
			//
			if (this.unreadInCollection.length > 0)
			{
				this.lastUnreadMessageId = this.unreadInCollection[this.unreadInCollection.length - 1].id;
			}
		},
		getFirstUnreadMessage()
		{
			let unreadId = null;

			for (let index = this.collection.length-1; index >= 0; index--)
			{
				if (!this.collection[index].unread)
				{
					break;
				}

				unreadId = this.collection[index].id;
			}

			return unreadId;
		},
		manageScrollButton(event)
		{
			const availableScrollHeight = event.target.scrollHeight - event.target.clientHeight;
			this.scrollChangedByUser = this.currentScroll + this.scrollButtonDiff < availableScrollHeight;

			clearTimeout(this.scrollButtonShowTimeout);
			this.scrollButtonShowTimeout = setTimeout(() =>
			{
				if (this.scrollChangedByUser)
				{
					//if user scroll and there is no scroll button - show it
					if (!this.showScrollButton)
					{
						this.showScrollButton = true;
					}
				}
				else
				{
					//if not user scroll, there was scroll button and no more unread to load - hide it
					if (this.showScrollButton && this.remainingUnreadPages === 0)
					{
						this.showScrollButton = false;
					}
				}
			}, 200);

			//if we are at the bottom
			if (event.target.scrollTop === event.target.scrollHeight - event.target.offsetHeight)
			{
				clearTimeout(this.scrollButtonShowTimeout);

				if (this.showScrollButton && this.remainingUnreadPages === 0)
				{
					this.showScrollButton = false;
				}
			}
		},
		getDateObject()
		{
			if (this.dateFormatFunction)
			{
				return this.dateFormatFunction;
			}

			this.dateFormatFunction = Object.create(BX.Main.Date);
			this.dateFormatFunction._getMessage = (phrase) => this.$Bitrix.Loc.getMessage(phrase);

			return this.dateFormatFunction;
		},
		getDateGroup(date)
		{
			const id = date.toJSON().slice(0,10);
			if (this.cachedDateGroups[id])
			{
				return this.cachedDateGroups[id];
			}

			const dateFormat = this.getDateFormat(DateFormat.groupTitle);

			this.cachedDateGroups[id] = {
				id,
				title: this.getDateObject().format(dateFormat, date)
			};

			return this.cachedDateGroups[id];
		},
		getDateFormat(type)
		{
			return MessengerUtils.date.getFormatType(
				BX.Messenger.Const.DateFormat[type],
				this.$Bitrix.Loc.getMessages()
			);
		},
		getDateGroupBlock(id = 0, text = '')
		{
			return {
				templateId: 'group'+id,
				templateType: TemplateType.group,
				text: text
			};
		},
		getDelimiterBlock(id = 0)
		{
			return {
				templateId: 'delimiter'+id,
				templateType: TemplateType.delimiter
			};
		},
		getObserver(config)
		{
			if (
				typeof window.IntersectionObserver === 'undefined'
				|| config.type === ObserverType.none
			)
			{
				return {
					observe: () => {},
					unobserve: () => {}
				};
			}

			let observerCallback, observerOptions;

			observerCallback = (entries) => {
				entries.forEach(entry => {
					let sendReadEvent = false;
					if (entry.isIntersecting)
					{
						//on windows with interface scaling intersectionRatio will never be 1
						if (entry.intersectionRatio >= 0.99)
						{
							sendReadEvent = true;
						}
						else if (
							entry.intersectionRatio > 0
							&& entry.rootBounds.height < entry.boundingClientRect.height + 20
							&& entry.intersectionRect.height > entry.rootBounds.height / 2
						)
						{
							sendReadEvent = true;
						}
					}

					if (sendReadEvent)
					{
						this.readMessageQueue.push(entry.target.dataset.messageId);
						this.readMessageTarget[entry.target.dataset.messageId] = entry.target;
					}
					else
					{
						this.readMessageQueue = this.readMessageQueue.filter(messageId => messageId !== entry.target.dataset.messageId);
						delete this.readMessageTarget[entry.target.dataset.messageId];
					}

					if (this.enableReadMessages)
					{
						this.readVisibleMessagesDelayed();
					}
				});
			};

			observerOptions = {
				root: this.$refs.body,
				threshold: new Array(101).fill(0).map((zero, index) => index * 0.01)
			};

			return new IntersectionObserver(observerCallback, observerOptions);
		},
		getElementClass(elementId)
		{
			const classWithId = DialogReferenceClassName.listItem + '-' + elementId;

			return ['bx-im-dialog-list-item', DialogReferenceClassName.listItem, classWithId];
		},
		getElementById(elementId)
		{
			const body = this.$refs.body;
			const className = DialogReferenceClassName.listItem + '-' + elementId;

			return body.getElementsByClassName(className)[0];
		},
		getPlaceholderClass(elementId)
		{
			const classWithId = DialogReferenceClassName.listItem + '-' + elementId;

			return ['im-skeleton-item', 'im-skeleton-item-1', 'im-skeleton-item--sm', classWithId];
		},
		/* endregion 09. Helpers */
	},

	directives:
	{
		'bx-im-directive-dialog-observer':
			{
				inserted(element, bindings, vnode)
				{
					if (bindings.value === ObserverType.none)
					{
						return false;
					}

					if (!vnode.context.observers[bindings.value])
					{
						vnode.context.observers[bindings.value] = vnode.context.getObserver({
							type: bindings.value
						});
					}
					vnode.context.observers[bindings.value].observe(element);

					return true;
				},
				unbind(element, bindings, vnode)
				{
					if (bindings.value === ObserverType.none)
					{
						return true;
					}

					if (vnode.context.observers[bindings.value])
					{
						vnode.context.observers[bindings.value].unobserve(element);
					}

					return true;
				}
			},
	},
	// language=Vue
	template: `
	<div class="bx-im-dialog" @click="onDialogClick" @touchmove="onDialogMove" ref="container">
		<div :class="bodyClasses" @scroll.passive="onScroll" ref="body">
			<!-- Main elements loop -->
			<template v-for="(element, index) in formattedCollection">
				<!-- Message -->
				<template v-if="element.templateType === TemplateType.message">
					<div
						:class="getElementClass(element.id)"
						:data-message-id="element.id"
						:data-template-id="element.templateId"
						:data-type="element.templateType" 
						:key="element.templateId"
						v-bx-im-directive-dialog-observer="element.unread? ObserverType.read: ObserverType.none"
					>
						<component :is="element.params.COMPONENT_ID"
							:userId="userId" 
							:dialogId="dialogId"
							:chatId="chatId"
							:message="element"
							:enableReactions="enableReactions"
							:enableDateActions="enableDateActions"
							:enableCreateContent="showMessageMenu"
							:enableGestureQuote="enableGestureQuote"
							:enableGestureQuoteFromRight="enableGestureQuoteFromRight"
							:enableGestureMenu="enableGestureMenu"
							:showName="showMessageUserName"
							:showAvatar="showMessageAvatar"
							:showMenu="showMessageMenu"
							:capturedMoveEvent="capturedMoveEvent"
							:referenceContentClassName="DialogReferenceClassName.listItem"
							:referenceContentBodyClassName="DialogReferenceClassName.listItemBody"
							:referenceContentNameClassName="DialogReferenceClassName.listItemName"
							@dragMessage="onDragMessage"
						/>
					</div>
				</template>
				<!-- Date groups -->
				<template v-else-if="element.templateType === TemplateType.group">
					<div class="bx-im-dialog-group" :data-template-id="element.templateId" :data-type="element.templateType" :key="element.templateId">
						<div class="bx-im-dialog-group-date">{{ element.text }}</div>
					</div>
				</template>
				<!-- Delimiters -->
				<template v-else-if="element.templateType === TemplateType.delimiter">
					<div class="bx-im-dialog-delimiter" :data-template-id="element.templateId" :data-type="element.templateType" :key="element.templateId"></div>
				</template>
				<!-- Placeholders -->
				<template v-else-if="element.templateType === TemplateType.placeholder">
					<component :is="'Placeholder'+element.placeholderType" :element="element"/>
				</template>
			</template>
			<!-- Writing and readed statuses -->
			<transition name="bx-im-dialog-status">
				<template v-if="writingStatusText">
					<div class="bx-im-dialog-status">
						<span class="bx-im-dialog-status-writing"></span>
						{{ writingStatusText }}
					</div>
				</template>
				<template v-else-if="statusReaded">
					<div class="bx-im-dialog-status" @click="onClickOnReadList">
						{{ statusReaded }}
					</div>
				</template>
			</transition>
			<div v-if="showStatusPlaceholder" class="bx-im-dialog-status-placeholder"></div>
		</div>
		<!-- Scroll button -->
		<transition name="bx-im-dialog-scroll-button">
			<div v-show="showScrollButton || (unreadCounter > 0 && !isLastIdInCollection)" class="bx-im-dialog-scroll-button-box" @click="onScrollButtonClick">
				<div class="bx-im-dialog-scroll-button">
					<div v-show="unreadCounter" class="bx-im-dialog-scroll-button-counter">
						<div class="bx-im-dialog-scroll-button-counter-digit">{{ formattedUnreadCounter }}</div>
					</div>
					<div class="bx-im-dialog-scroll-button-arrow"></div>
				</div>
			</div>
		</transition>
	</div>
`
};

export {MessageList};