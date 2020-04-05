/**
 * Bitrix Messenger
 * Dialog Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './dialog.css';
import 'main.polyfill.intersectionobserver';
import {Vue} from 'ui.vue';
import {Vuex} from 'ui.vue.vuex';
import 'im.component.message';
import {DeviceType, MutationType, DialogReferenceClassName, DialogType} from "im.const";
import {Utils as MessengerUtils} from "im.utils";
import {Animation} from "im.tools.animation";

const TemplateType = Object.freeze({
	message: 'message',
	delimiter: 'delimiter',
	group: 'group',
	historyLoader: 'historyLoader',
	unreadLoader: 'unreadLoader',
	button: 'button',
});

const ObserverType = Object.freeze({
	history: 'history',
	unread: 'unread',
	read: 'read',
	none: 'none',
});

const LoadButtonTypes = Object.freeze({
	before: 'before',
	after: 'after'
});

const AnimationType = Object.freeze({
	none: 'none',
	mixed: 'mixed',
	enter: 'enter',
	leave: 'leave',
});

Vue.component('bx-messenger-dialog',
{
	/**
	 * @emits 'requestHistory' {lastId: number, limit: number}
	 * @emits 'requestUnread' {lastId: number, limit: number}
	 * @emits 'readMessage' {id: number}
	 * @emits 'quoteMessage' {message: object}
	 * @emits 'click' {event: MouseEvent}
	 * @emits 'clickByUserName' {user: object, event: MouseEvent}
	 * @emits 'clickByUploadCancel' {file: object, event: MouseEvent}
	 * @emits 'clickByKeyboardButton' {message: object, action: string, params: Object}
	 * @emits 'clickByChatTeaser' {message: object, event: MouseEvent}
	 * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	 * @emits 'clickByCommand' {type: string, value: string, event: MouseEvent}
	 * @emits 'clickByMention' {type: string, value: string, event: MouseEvent}
	 * @emits 'clickByMessageRetry' {message: object, event: MouseEvent}
	 * @emits 'clickByReadedList' {list: array, event: MouseEvent}
	 * @emits 'setMessageReaction' {message: object, reaction: object}
	 * @emits 'openMessageReactionList' {message: object, values: object}
	 */

	/**
	 * @listens props.listenEventScrollToBottom {force:boolean, cancelIfScrollChange:boolean} (global|application) -- scroll dialog to bottom, see more in methods.onScrollToBottom()
	 * @listens props.listenEventRequestHistory {count:number} (application)
	 * @listens props.listenEventRequestUnread {count:number} (application)
	 * @listens props.listenEventSendReadMessages {} (application)
	 */
	props:
	{
		userId: { default: 0 },
		dialogId: { default: 0 },
		chatId: { default: 0 },
		messageLimit: { default: 20 },
		messageExtraCount: { default: 0 },
		listenEventScrollToBottom: { default: '' },
		listenEventRequestHistory: { default: '' },
		listenEventRequestUnread: { default: '' },
		listenEventSendReadMessages: { default: '' },
		enableReadMessages: { default: true },
		enableReactions: { default: true },
		enableDateActions: { default: true },
		enableCreateContent: { default: true },
		enableGestureQuote: { default: true },
		enableGestureQuoteFromRight: { default: true },
		enableGestureMenu: { default: false },
		showMessageUserName: { default: true },
		showMessageAvatar: { default: true },
		showMessageMenu: { default: true },
	},
	data()
	{
		return {
			scrollAnimating: false,
			showScrollButton: false,
			messageShowCount: 0,
			unreadLoaderShow: false,
			historyLoaderBlocked: false,
			historyLoaderShow: true,
			startMessageLimit: 0,
			templateMessageScrollOffset: 20,
			templateMessageWithNameDifferent: 29, // name block + padding top
			TemplateType: TemplateType,
			ObserverType: ObserverType,
			DialogReferenceClassName: DialogReferenceClassName,
			captureMove: false,
			capturedMoveEvent: null,
			lastMessageId: null,
			maxMessageId: null,
		}
	},
	created()
	{
		this.showScrollButton = this.unreadCounter > 0;

		this.scrollChangedByUser = false;
		this.scrollButtonDiff = 100;
		this.scrollButtonShowTimeout = null;
		this.scrollPosition = 0;
		this.scrollPositionChangeTime = new Date().getTime();

		this.animationScrollHeightStart = 0;
		this.animationScrollHeightEnd = 0;
		this.animationScrollTop = 0;
		this.animationScrollChange = 0;
		this.animationScrollLastUserId = 0;
		this.animationType = AnimationType.none;
		this.animationCollection = [];
		this.animationCollectionOffset = {};
		this.animationLastElementBeforeStart = 0;

		this.observers = {};

		this.requestHistoryInterval = null;
		this.requestUnreadInterval = null;

		this.lastAuthorId = 0;
		this.firstMessageId = null;
		this.firstUnreadMessageId = null;
		this.dateFormatFunction = null;
		this.cacheGroupTitle = {};

		this.waitLoadHistory = false;
		this.waitLoadUnread = false;
		this.skipUnreadScroll = false;

		this.readMessageQueue = [];
		this.readMessageTarget = {};
		this.readMessageDelayed = MessengerUtils.debounce(this.readMessage, 50, this);

		this.requestHistoryBlockIntersect = false;
		this.requestHistoryDelayed = MessengerUtils.debounce(this.requestHistory, 50, this);

		this.requestUnreadBlockIntersect = false;
		this.requestUnreadDelayed = MessengerUtils.debounce(this.requestUnread, 50, this);

		this.startMessageLimit = this.messageLimit;

		if (this.listenEventScrollToBottom)
		{
			Vue.event.$on(this.listenEventScrollToBottom, this.onScrollToBottom);
			this.$root.$on(this.listenEventScrollToBottom, this.onScrollToBottom);
		}
		if (this.listenEventRequestHistory)
		{
			this.$root.$on(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
		}
		if (this.listenEventRequestUnread)
		{
			this.$root.$on(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
		}
		if (this.listenEventSendReadMessages)
		{
			this.$root.$on(this.listenEventSendReadMessages, this.onSendReadMessages);
		}

		window.addEventListener("orientationchange", this.onOrientationChange);
		window.addEventListener('focus', this.onWindowFocus);
		window.addEventListener('blur', this.onWindowBlur);

		Vue.event.$on('bitrixmobile:controller:focus', this.onWindowFocus);
		Vue.event.$on('bitrixmobile:controller:blur', this.onWindowBlur);
	},
	beforeDestroy()
	{
		this.observers = {};

		clearTimeout(this.scrollButtonShowTimeout);
		clearInterval(this.requestHistoryInterval);
		clearInterval(this.requestUnreadInterval);

		if (this.listenEventScrollToBottom)
		{
			Vue.event.$off(this.listenEventScrollToBottom, this.onScrollToBottom);
			this.$root.$off(this.listenEventScrollToBottom, this.onScrollToBottom);
		}
		if (this.listenEventRequestHistory)
		{
			this.$root.$off(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
		}
		if (this.listenEventRequestUnread)
		{
			this.$root.$off(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
		}
		if (this.listenEventSendReadMessages)
		{
			this.$root.$off(this.listenEventSendReadMessages, this.onSendReadMessages);
		}

		window.removeEventListener("orientationchange", this.onOrientationChange);
		window.removeEventListener('focus', this.onWindowFocus);
		window.removeEventListener('blur', this.onWindowBlur);

		Vue.event.$off('bitrixmobile:controller:focus', this.onWindowFocus);
		Vue.event.$off('bitrixmobile:controller:blur', this.onWindowBlur);
	},
	mounted()
	{
		let unreadId = Utils.getFirstUnreadMessage(this.collection);
		if (unreadId)
		{
			Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true)
		}
		else
		{
			let body = this.$refs.body;
			Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);
		}
		this.windowFocused = MessengerUtils.platform.isBitrixMobile()? true: document.hasFocus();
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('IM_MESSENGER_DIALOG_', this.$root.$bitrixMessages);
		},
		dialog()
		{
			let dialog = this.$store.getters['dialogues/get'](this.dialogId);
			return dialog? dialog: this.$store.getters['dialogues/getBlank']();
		},
		collectionMutationType()
		{
			return this.$store.getters['messages/getMutationType'](this.chatId);
		},
		collection()
		{
			return this.$store.getters['messages/get'](this.chatId);
		},
		elementsWithLimit()
		{
			let unreadCount = this.collection.filter(element => element.unread).length;
			let showLimit = this.messageExtraCount + this.messageLimit * 2;
			if (unreadCount > showLimit)
			{
				showLimit = unreadCount;
			}

			let start = this.collection.length - showLimit;
			if (!this.historyLoaderShow || start < 0)
			{
				start = 0;
			}

			let slicedCollection = start === 0? this.collection: this.collection.slice(start, this.collection.length);
			this.messageShowCount = slicedCollection.length;

			this.firstMessageId = null;
			this.lastMessageId = 0;
			this.maxMessageId = 0;
			this.lastMessageAuthorId = 0;

			let collection = [];
			let lastAuthorId = 0;
			let groupNode = {};

			this.firstUnreadMessageId = 0;
			let unreadCountInSlicedCollection = 0;

			if (this.messageShowCount > 0)
			{
				slicedCollection.forEach(element =>
				{
					if (this.firstMessageId === null || this.firstMessageId > element.id)
					{
						this.firstMessageId = element.id;
					}

					if (this.maxMessageId < element.id)
					{
						this.maxMessageId = element.id;
					}

					this.lastMessageId = element.id;

					let group = this._groupTitle(element.date);
					if (!groupNode[group.title])
					{
						groupNode[group.title] = group.id;
						collection.push(Blocks.getGroup(group.id, group.title));
					}
					else if (lastAuthorId !== element.authorId)
					{
						collection.push(Blocks.getDelimiter(element.id));
					}

					collection.push(element);

					lastAuthorId = element.authorId;

					if (element.unread)
					{
						if (!this.firstUnreadMessageId)
						{
							this.firstUnreadMessageId = element.id;
						}
						unreadCountInSlicedCollection++;
					}
				});

				this.lastMessageAuthorId = lastAuthorId;
			}
			else
			{
				this.firstMessageId = 0;
			}

			if (
				this.collection.length >= this.messageLimit
				&& this.collection.length >= this.messageShowCount
				&& this.historyLoaderBlocked === false
			)
			{
				this.historyLoaderShow = true;
			}
			else
			{
				this.historyLoaderShow = false;
			}

			if (this.dialog.unreadLastId > this.maxMessageId)
			{
				this.unreadLoaderShow = true;
			}
			else
			{
				this.unreadLoaderShow = false;
			}

			return collection;
		},
		statusWriting()
		{
			clearTimeout(this.scrollToTimeout);

			if (this.dialog.writingList.length === 0)
			{
				return '';
			}

			if (!this.scrollChangedByUser && !this.showScrollButton)
			{
				this.scrollToTimeout = setTimeout(() => this.scrollTo({duration: 500}), 300);
			}

			return this.localize.IM_MESSENGER_DIALOG_WRITES_MESSAGE.replace(
				'#USER#', this.dialog.writingList.map(element => element.userName).join(', ')
			);
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
				let record = this.dialog.readedList[0];
				if (
					record.messageId === this.lastMessageId
					&& record.userId !== this.lastMessageAuthorId
				)
				{
					let dateFormat = MessengerUtils.date.getFormatType(
						BX.Messenger.Const.DateFormat.readedTitle,
						this.$root.$bitrixMessages
					);

					text = this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_USER.replace(
						'#DATE#', this._getDateFormat().format(dateFormat, record.date)
					);
				}
			}
			else
			{
				let readedList = this.dialog.readedList.filter(record => record.messageId === this.lastMessageId && record.userId !== this.lastMessageAuthorId);
				if (readedList.length === 1)
				{
					text = this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT.replace(
						'#USERS#', readedList[0].userName
					);
				}
				else if (readedList.length > 1)
				{
					text = this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT.replace(
						'#USERS#',
						this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT_PLURAL
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

			if (!this.scrollChangedByUser && !this.showScrollButton)
			{
				this.scrollToTimeout = setTimeout(() => this.scrollTo({duration: 500}), 300);
			}

			return text;
		},
		unreadCounter()
		{
			return this.dialog.counter > 999? 999: this.dialog.counter;
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

		AnimationType: () => AnimationType,

		...Vuex.mapState({
			application: state => state.application,
		})
	},
	methods:
	{
		onDialogClick(event)
		{
			if (Vue.testNode(event.target, {className: 'bx-im-message-command'}))
			{
				this.onCommandClick(event);
			}
			else if (Vue.testNode(event.target, {className: 'bx-im-mention'}))
			{
				this.onMentionClick(event);
			}

			this.windowFocused = true;
			this.$emit('click', {event});
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

			this.$emit('clickByCommand', {type: event.target.dataset.entity, value, event});
		},
		onMentionClick(event)
		{
			this.$emit('clickByMention', {type: event.target.dataset.type, value: event.target.dataset.value, event});
		},
		onScroll(event)
		{
			clearTimeout(this.scrollToTimeout);

			this.scrollPosition = event.target.scrollTop;
			this.scrollPositionChangeTime = new Date().getTime();

			this.scrollChangedByUser = !(event.target.scrollTop + this.scrollButtonDiff >= event.target.scrollHeight - event.target.clientHeight);

			clearTimeout(this.scrollButtonShowTimeout);
			this.scrollButtonShowTimeout = setTimeout(() =>
			{
				if (this.scrollChangedByUser)
				{
					if (!this.showScrollButton)
					{
						this.showScrollButton = true;
					}
				}
				else
				{
					if (this.showScrollButton && !this.unreadLoaderShow)
					{
						this.showScrollButton = false;
					}
				}
			}, 200);

			if (event.target.scrollTop === event.target.scrollHeight - event.target.offsetHeight)
			{
				clearTimeout(this.scrollButtonShowTimeout);

				if (this.showScrollButton && !this.unreadLoaderShow)
				{
					this.showScrollButton = false;
				}
			}
		},

		scrollToBottom(params = {})
		{
			let {
				force = false,
				cancelIfScrollChange = false,
				duration = null
			} = params;

			if (cancelIfScrollChange && this.scrollChangedByUser)
			{
				return false;
			}

			let body = this.$refs.body;

			if (this.dialog.counter > 0)
			{
				let scrollToMessageId = this.dialog.counter > 1 && this.firstUnreadMessageId? this.firstUnreadMessageId: this.lastMessageId;
				Utils.scrollToFirstUnreadMessage(this, this.collection, scrollToMessageId);

				if (this.dialog.counter < this.startMessageLimit)
				{
					this.historyLoaderShow = true;
					this.historyLoaderBlocked = false;
				}

				return true;
			}

			this.showScrollButton = false;

			if (force)
			{
				Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);

				if (this.messageExtraCount)
				{
					this.$store.commit('application/clearDialogExtraCount');
				}
				this.historyLoaderShow = true;
				this.historyLoaderBlocked = false;
			}
			else
			{
				let scrollParams = {};
				if (duration)
				{
					scrollParams.duration = duration;
				}
				this.scrollTo({
					callback: () => {
						if (this.messageExtraCount)
						{
							this.$store.commit('application/clearDialogExtraCount');
						}
						this.historyLoaderShow = true;
						this.historyLoaderBlocked = false;
					},
					...scrollParams
				});
			}
		},

		scrollTo(params = {})
		{
			if (this.animateScrollId)
			{
				Animation.cancel(this.animateScrollId);
				this.scrollAnimating = false;
			}
			if (typeof params === 'function')
			{
				params = {callback: params};
			}

			let body = this.$refs.body;
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

			let container = this.$refs.container;

			if (container && (end - start) > container.offsetHeight * 3)
			{
				start = end - container.offsetHeight * 3;
				console.warn('Scroll trajectory has been reduced');
			}

			this.scrollAnimating = true;
			console.warn('User scroll blocked while scrolling');

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
		onScrollToBottom(event = {})
		{
			event.force = event.force === true;
			event.cancelIfScrollChange = event.cancelIfScrollChange === true;

			if (this.firstUnreadMessageId)
			{
				console.warn('onScrollToBottom canceled - unread messages');
				return false;
			}

			this.scrollToBottom(event);

			return true;
		},
		onOrientationChange(event = {})
		{
			clearTimeout(this.scrollToTimeout);

			if (this.application.device.type !== DeviceType.mobile)
			{
				return false;
			}

			console.log('Orientation changed');

			if (!this.scrollChangedByUser)
			{
				this.scrollToTimeout = setTimeout(() => this.scrollToBottom({force: true}), 300);
			}
		},
		onWindowFocus(event = {})
		{
			this.windowFocused = true;
			this.readMessage();

			return true;
		},
		onWindowBlur(event = {})
		{
			this.windowFocused = false;
		},
		requestHistory()
		{
			if (!this.requestHistoryBlockIntersect)
			{
				return false;
			}

			if (this.waitLoadHistory || !this.windowFocused || this.animateScrollId)
			{
				this.requestHistoryDelayed();
				return false;
			}

			if (
				this.scrollPositionChangeTime + 100 > new Date().getTime()
			//	|| this.$refs.body.scrollTop < 0
			)
			{
				this.requestHistoryDelayed();
				return true;
			}

			this.waitLoadHistory = true;

			clearTimeout(this.waitLoadHistoryTimeout);
			this.waitLoadHistoryTimeout = setTimeout(() => {
				this.waitLoadHistory = false;
			}, 10000);

			let length = this.collection.length;
			let messageShowCount = this.messageShowCount;
			if (length > messageShowCount)
			{
				let element = this.$refs.body.getElementsByClassName(DialogReferenceClassName.listItem)[0];

				this.$store.commit('application/increaseDialogExtraCount', {count: this.startMessageLimit});
				Utils.scrollToElementAfterLoadHistory(this, element);

				return true;
			}

			this.$emit('requestHistory', {lastId: this.firstMessageId});
		},
		requestUnread()
		{
			if (!this.requestUnreadBlockIntersect)
			{
				return false;
			}

			if (this.waitLoadUnread || !this.windowFocused || this.animateScrollId)
			{
				this.requestUnreadDelayed();
				return false;
			}

			if (
				this.scrollPositionChangeTime + 10 > new Date().getTime()
				//|| this.$refs.body.scrollTop > this.$refs.body.scrollHeight - this.$refs.body.clientHeight
			)
			{
				this.requestUnreadDelayed();
				return true;
			}

			this.waitLoadUnread = true;
			this.skipUnreadScroll = true;

			this.$emit('requestUnread', {lastId: this.lastMessageId});
		},
		onRequestHistoryAnswer(event = {})
		{
			if (event.error)
			{
				this.historyLoaderBlocked = false;
			}
			else
			{
				this.historyLoaderBlocked = event.count < this.startMessageLimit;
				this.$store.commit('application/increaseDialogExtraCount', {count: event.count});
			}

			if (this.historyLoaderBlocked)
			{
				this.historyLoaderShow = false;
			}

			let element = this.$refs.body.getElementsByClassName(DialogReferenceClassName.listItem)[0];

			if (event.count > 0)
			{
				if (element)
				{
					Utils.scrollToElementAfterLoadHistory(this, element);
				}
			}
			else if (event.error)
			{
				element.scrollIntoView(true);
			}
			else
			{
				Utils.scrollToPosition(this, 0);
			}

			clearTimeout(this.waitLoadHistoryTimeout);
			this.waitLoadHistoryTimeout = setTimeout(() => {
				this.waitLoadHistory = false;
			}, 1000);

			return true;
		},
		onRequestUnreadAnswer(event = {})
		{
			if (event.error)
			{
				this.historyLoaderBlocked = false;
			}
			else
			{
				if (event.count < this.startMessageLimit)
				{
					this.unreadLoaderShow = false;
				}
				this.$store.commit('application/increaseDialogExtraCount', {count: event.count});
			}

			let body = this.$refs.body;
			if (event.count > 0)
			{
			}
			else if (event.error)
			{
				let element = this.$refs.body.getElementsByClassName(DialogReferenceClassName.listUnreadLoader)[0];
				if (element)
				{
					Utils.scrollToPosition(this, body.scrollTop - element.offsetHeight*2);
				}
				else
				{
					Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);
				}
			}
			else
			{
				Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);
			}

			setTimeout(() => this.waitLoadUnread = false, 1000);

			return true;
		},
		onSendReadMessages(event = {})
		{
			this.readMessageDelayed();

			return true;
		},
		readMessage()
		{
			if (!this.windowFocused)
			{
				return false;
			}

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

				this.requestReadMessage(messageId);
				return false;
			});
		},
		requestReadMessage(messageId)
		{
			this.$emit('readMessage', {id: messageId});
		},

		onClickByUserName(event)
		{
			if (!this.windowFocused)
			{
				return false;
			}
			this.$emit('clickByUserName', event)
		},

		onClickByUploadCancel(event)
		{
			if (!this.windowFocused)
			{
				return false;
			}
			this.$emit('clickByUploadCancel', event)
		},

		onClickByKeyboardButton(event)
		{
			if (!this.windowFocused)
			{
				return false;
			}
			this.$emit('clickByKeyboardButton', event)
		},

		onClickByChatTeaser(event)
		{
			this.$emit('clickByChatTeaser', event)
		},

		onClickByMessageMenu(event)
		{
			if (!this.windowFocused)
			{
				return false;
			}
			this.$emit('clickByMessageMenu', event)
		},

		onClickByMessageRetry(event)
		{
			if (!this.windowFocused)
			{
				return false;
			}
			this.$emit('clickByMessageRetry', event)
		},

		onClickByReadedList(event)
		{
			const readedList = this.dialog.readedList.filter(record => record.messageId === this.lastMessageId && record.userId !== this.lastMessageAuthorId);
			this.$emit('clickByReadedList', {list: readedList, event})
		},

		onMessageReactionSet(event)
		{
			this.$emit('setMessageReaction', event)
		},

		onMessageReactionListOpen(event)
		{
			this.$emit('openMessageReactionList', event)
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

		onQuoteMessage(event)
		{
			if (!this.windowFocused)
			{
				return false;
			}
			this.$emit('quoteMessage', event)
		},

		_getDateFormat()
		{
			if (this.dateFormatFunction)
			{
				return this.dateFormatFunction;
			}

			this.dateFormatFunction = Object.create(BX.Main.Date);
			if (this.$root.$bitrixMessages)
			{
				this.dateFormatFunction._getMessage = (phrase) => this.$root.$bitrixMessages[phrase];
			}

			return this.dateFormatFunction;
		},
		_groupTitle(date)
		{
			const id = Utils.getDateFormat(date);
			if (this.cacheGroupTitle[id])
			{
				return {
					id: id,
					title: this.cacheGroupTitle[id]
				};
			}

			let dateFormat = MessengerUtils.date.getFormatType(
				BX.Messenger.Const.DateFormat.groupTitle,
				this.$root.$bitrixMessages
			);

			this.cacheGroupTitle[id] = this._getDateFormat().format(dateFormat, date);

			return {
				id: id,
				title: this.cacheGroupTitle[id]
			};
		},

		animationTrigger(type, start, element)
		{
			let templateId = element.dataset.templateId;
			let templateType = element.dataset.type;
			let body = this.$refs.body;

			if (!body || !templateId)
			{
				return false;
			}

			if (start)
			{
				if (!this.animationScrollHeightStart)
				{
					this.animationScrollHeightStart = body.scrollHeight;
					this.animationScrollHeightEnd = body.scrollHeight;
					this.animationScrollTop = body.scrollTop;
					this.animationScrollChange = 0;

					clearTimeout(this.scrollToTimeout);
					this.scrollChangedByUser = !(body.scrollTop + this.scrollButtonDiff >= body.scrollHeight - body.clientHeight);

					if (this.scrollChangedByUser && !this.showScrollButton && this.unreadCounter > 1)
					{
						this.showScrollButton = true;
					}
				}
			}
			else
			{
				this.animationScrollHeightEnd = body.scrollHeight;
			}

			if (
				!this.collectionMutationType.applied
				&& this.collectionMutationType.initialType !== MutationType.set
			)
			{
				if (start)
				{
					this.animationCollection.push(templateId);
				}
				else
				{
					this.animationCollection = this.animationCollection.filter(id => {
						delete this.animationCollectionOffset[templateId];
						return id !== templateId;
					});
				}
				this.animationStart();
				return false;
			}

			if (
				!this.collectionMutationType.applied
				&& this.collectionMutationType.initialType === MutationType.set
				&& this.collectionMutationType.appliedType === MutationType.setBefore
			)
			{
				let unreadId = Utils.getFirstUnreadMessage(this.collection);
				if (unreadId)
				{
					Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true);
					return false;
				}

				Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);

				if (start)
				{
					this.animationCollection.push(templateId);
				}
				else
				{
					this.animationCollection = this.animationCollection.filter(id => {
						delete this.animationCollectionOffset[templateId];
						return id !== templateId;
					});
				}

				this.animationStart();
				return false;
			}

			if (start)
			{
				if (type === AnimationType.leave)
				{
					this.animationCollectionOffset[templateId] = element.offsetHeight;
				}

				if (this.animationType === AnimationType.none)
				{
					this.animationType = type;
				}
				else if (this.animationType !== type)
				{
					this.animationType = AnimationType.mixed;
				}

				this.animationCollection.push(templateId);
			}
			else
			{
				if (type === AnimationType.enter)
				{
					let offset = element.offsetHeight;

					this.animationScrollChange += offset;
					body.scrollTop += offset;
				}
				else if (type === AnimationType.leave)
				{
					let offset = this.animationCollectionOffset[templateId]? this.animationCollectionOffset[templateId]: 0;
					this.animationScrollChange -= offset;
					body.scrollTop -= offset;

					this.animationScrollLastIsDelimeter = templateType !== TemplateType.message;
				}

				this.animationCollection = this.animationCollection.filter(id => {
					delete this.animationCollectionOffset[templateId];
					return id !== templateId;
				});
			}

			this.animationStart();
		},

		animationStart()
		{
			if (this.animationCollection.length > 0)
			{
				return false;
			}

			let body = this.$refs.body;

			if (this.animationType === AnimationType.leave)
			{
				let newScrollPosition = 0;

				// fix for chrome dom rendering: while delete node, scroll change immediately
				if (body.scrollTop !== this.animationScrollTop + this.animationScrollChange)
				{
					newScrollPosition = this.animationScrollTop + this.animationScrollChange
				}
				else
				{
					newScrollPosition = body.scrollTop;
				}

				// fix position if last element the same type of new element
				if (!this.animationScrollLastIsDelimeter)
				{
					newScrollPosition += this.templateMessageWithNameDifferent;
				}

				if (newScrollPosition !== body.scrollTop)
				{
					Utils.scrollToPosition(this, newScrollPosition);
				}
			}
			else if (this.animationType === AnimationType.mixed)
			{
				let unreadId = Utils.getFirstUnreadMessage(this.collection);
				if (unreadId)
				{
					Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true);
				}
			}

			this.animationType = AnimationType.none;
			this.animationScrollHeightStart = 0;
			this.animationScrollHeightEnd = 0;
			this.animationScrollTop = 0;
			this.animationScrollChange = 0;

			if (Utils.scrollByMutationType(this))
			{
				return false;
			}

			if (this.scrollChangedByUser)
			{
				console.warn('Animation canceled: scroll changed by user');
				return false;
			}

			if (this.unreadCounter > 0 && this.firstUnreadMessageId)
			{
				if (this.skipUnreadScroll)
				{
					this.skipUnreadScroll = false;
					return;
				}

				Utils.scrollToFirstUnreadMessage(this, this.collection, this.firstUnreadMessageId);
				return;
			}

			this.scrollTo(() =>
			{
				if (this.unreadCounter <= 0 && this.messageExtraCount)
				{
					this.$store.commit('application/clearDialogExtraCount');
				}
			});
		},
	},

	directives:
	{
		'bx-messenger-dialog-observer':
		{
			inserted(element, bindings, vnode)
			{
				if (bindings.value === ObserverType.none)
				{
					return false;
				}

				if (!vnode.context.observers[bindings.value])
				{
					vnode.context.observers[bindings.value] = Utils.getMessageLoaderObserver({
						type: bindings.value,
						context: vnode.context
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

	template: `
		<div class="bx-im-dialog" @click="onDialogClick" @touchmove="onDialogMove" ref="container">	
			<div :class="[DialogReferenceClassName.listBody, {
				'bx-im-dialog-list-scroll-blocked': scrollBlocked, 
				'bx-im-dialog-dark-background': isDarkBackground,
				'bx-im-dialog-mobile': isMobile,
			}]" @scroll.passive="onScroll" ref="body">
				<template v-if="historyLoaderShow">
					<div class="bx-im-dialog-load-more bx-im-dialog-load-more-history" v-bx-messenger-dialog-observer="ObserverType.history">
						<span class="bx-im-dialog-load-more-text">{{ localize.IM_MESSENGER_DIALOG_LOAD_MESSAGES }}</span>
					</div>
				</template>
				<transition-group 
					tag="div" class="bx-im-dialog-list-box" name="bx-im-dialog-message-animation" 
					@before-enter="animationTrigger(AnimationType.enter, true, $event)" 
					@after-enter="animationTrigger(AnimationType.enter, false, $event)" 
					@before-leave="animationTrigger(AnimationType.leave, true, $event)" 
					@after-leave="animationTrigger(AnimationType.leave, false, $event)"
				>
					<template v-for="element in elementsWithLimit">
						<template v-if="element.templateType == TemplateType.message">
							<div :class="['bx-im-dialog-list-item', DialogReferenceClassName.listItem, DialogReferenceClassName.listItem+'-'+element.id]" :data-message-id="element.id" :data-template-id="element.templateId" :data-type="element.templateType" :key="element.templateId" v-bx-messenger-dialog-observer="element.unread? ObserverType.read: ObserverType.none">			
								<component :is="element.params.COMPONENT_ID"
									:userId="userId" 
									:dialogId="dialogId"
									:chatId="chatId"
									:dialog="dialog"
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
									@clickByUserName="onClickByUserName"
									@clickByUploadCancel="onClickByUploadCancel"
									@clickByKeyboardButton="onClickByKeyboardButton"
									@clickByChatTeaser="onClickByChatTeaser"
									@clickByMessageMenu="onClickByMessageMenu"
									@clickByMessageRetry="onClickByMessageRetry"
									@setMessageReaction="onMessageReactionSet"
									@openMessageReactionList="onMessageReactionListOpen"
									@dragMessage="onDragMessage"
									@quoteMessage="onQuoteMessage"
								/>
							</div>
						</template>
						<template v-else-if="element.templateType == TemplateType.group">
							<div class="bx-im-dialog-group" :data-template-id="element.templateId" :data-type="element.templateType" :key="element.templateId">
								<div class="bx-im-dialog-group-date">{{ element.text }}</div>
							</div>
						</template>
						<template v-else-if="element.templateType == TemplateType.delimiter">
							<div class="bx-im-dialog-delimiter" :data-template-id="element.templateId" :data-type="element.templateType" :key="element.templateId"></div>
						</template>
					</template>
				</transition-group>
				<template v-if="unreadLoaderShow">
					<div :class="['bx-im-dialog-load-more', 'bx-im-dialog-load-more-unread', DialogReferenceClassName.listUnreadLoader]" v-bx-messenger-dialog-observer="ObserverType.unread">
						<span class="bx-im-dialog-load-more-text">{{ localize.IM_MESSENGER_DIALOG_LOAD_MESSAGES }}</span>
					</div>
				</template>
				<transition name="bx-im-dialog-status">
					<template v-if="statusWriting">
						<div class="bx-im-dialog-status">
							<span class="bx-im-dialog-status-writing"></span>
							{{ statusWriting }}
						</div>
					</template>
					<template v-else-if="statusReaded">
						<div class="bx-im-dialog-status" @click="onClickByReadedList">
							{{ statusReaded }}
						</div>
					</template>
				</transition>
			</div>
			<transition name="bx-im-dialog-scroll-button">
				<div v-show="showScrollButton || unreadLoaderShow && unreadCounter" class="bx-im-dialog-scroll-button-box" @click="scrollToBottom()">
					<div class="bx-im-dialog-scroll-button">
						<div v-show="unreadCounter" class="bx-im-dialog-scroll-button-counter">
							<div class="bx-im-dialog-scroll-button-counter-digit">{{unreadCounter}}</div>
						</div>
						<div class="bx-im-dialog-scroll-button-arrow"></div>
					</div>
				</div>
			</transition>
		</div>
	`
});

const Utils = {
	getDateFormat(date)
	{
		return date.toJSON().slice(0,10);
	},

	scrollToMessage(context, collection, messageId = 0, force = false, stickToTop = true)
	{
		let body = context.$refs.body;

		let element = body.getElementsByClassName(DialogReferenceClassName.listItem+'-'+messageId)[0];

		let end = 0;
		if (!element)
		{
			if (stickToTop)
			{
				end = 10;
			}
			else
			{
				end = body.scrollHeight - body.clientHeight;
			}
		}
		else if (stickToTop)
		{
			end = element.offsetTop - (context.templateMessageScrollOffset/2);
		}
		else
		{
			end = element.offsetTop + element.offsetHeight - body.clientHeight + (context.templateMessageScrollOffset/2);
		}

		if (force)
		{
			this.scrollToPosition(context, end);
		}
		else
		{
			context.scrollTo({end});
		}

		return true;
	},

	getFirstUnreadMessage(collection)
	{
		let unreadId = null;

		for (let index = collection.length-1; index >= 0; index--)
		{
			if (!collection[index].unread)
			{
				break;
			}

			unreadId = collection[index].id;
		}

		return unreadId;
	},

	scrollToPosition(context, position)
	{
		let body = context.$refs.body;
		if (!body)
		{
			return false;
		}

		if (context.animateScrollId)
		{
			Animation.cancel(context.animateScrollId);
			this.scrollAnimating = false;
			context.animateScrollId = null;
		}

		body.scrollTop = position;
	},

	scrollByMutationType(context)
	{
		if (
			context.collectionMutationType.applied
			|| context.collectionMutationType.initialType !== MutationType.set)
		{
			return false;
		}

		context.$store.dispatch('messages/applyMutationType', {chatId: context.chatId});

		if (context.collectionMutationType.appliedType === MutationType.setBefore)
		{
			let body = context.$refs.body;
			this.scrollToPosition(context, body.scrollHeight - body.clientHeight);

			return true;
		}

		if (context.collectionMutationType.scrollMessageId > 0)
		{
			let unreadId = Utils.getFirstUnreadMessage(context.collection);
			let toMessageId = context.collectionMutationType.scrollMessageId;
			let force = !context.collectionMutationType.scrollStickToTop;
			let stickToTop = context.collectionMutationType.scrollStickToTop;

			if (unreadId && toMessageId > unreadId)
			{
				stickToTop = true;
				force = true;
				toMessageId = unreadId;
				unreadId = null;
			}

			Utils.scrollToMessage(context, context.collection, toMessageId, force, stickToTop);

			if (unreadId)
			{
				Utils.scrollToMessage(context, context.collection, unreadId);
				return true;
			}
		}

		return false;
	},

	scrollToFirstUnreadMessage(context, collection, unreadId = null, force = false)
	{
		let body = context.$refs.body;

		let element = false;
		if (unreadId !== null)
		{
			element = body.getElementsByClassName(DialogReferenceClassName.listItem+'-'+unreadId)[0];
		}
		if (!element)
		{
			unreadId = this.getFirstUnreadMessage(collection);
		}

		this.scrollToMessage(context, collection, unreadId, force);
	},

	scrollToElementAfterLoadHistory(context, element)
	{
		let elementBody = element.getElementsByClassName(DialogReferenceClassName.listItemBody)[0];
		if (elementBody)
		{
			element = elementBody;
		}

		let previousOffsetTop = element.getBoundingClientRect().top;

		context.$nextTick(() =>
		{
			clearTimeout(context.waitLoadHistoryTimeout);
			context.waitLoadHistoryTimeout = setTimeout(() => {
				context.waitLoadHistory = false;
			}, 1000);

			if (!element)
			{
				return false;
			}

			this.scrollToPosition(context, element.getBoundingClientRect().top - previousOffsetTop);
		});
	},

	scrollToElementAfterLoadUnread(context, firstMessageId = 0)
	{
		context.showScrollButton = true;

		if (firstMessageId)
		{
			this.scrollToMessage(context, context.collection, firstMessageId, false, false);
		}
	},

	getMessageLoaderObserver(config)
	{
		if (
			typeof window.IntersectionObserver === 'undefined'
			|| config.value === ObserverType.none
		)
		{
			return {
				observe: () => {},
				unobserve: () => {}
			};
		}

		let observerCallback, observerOptions;

		if (config.type === ObserverType.read)
		{
			observerCallback = function (entries, observer)
			{
				entries.forEach(function(entry)
				{
					let sendReadEvent = false;
					if (entry.isIntersecting)
					{
						if (entry.intersectionRatio >= 1)
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
						config.context.readMessageQueue.push(entry.target.dataset.messageId);
						config.context.readMessageTarget[entry.target.dataset.messageId] = entry.target;
					}
					else
					{
						config.context.readMessageQueue = config.context.readMessageQueue.filter(messageId => messageId !== entry.target.dataset.messageId);
						delete config.context.readMessageTarget[entry.target.dataset.messageId];
					}

					if (config.context.enableReadMessages)
					{
						config.context.readMessageDelayed();
					}

				});
			};
			observerOptions = {
				root: config.context.$refs.body,
				threshold: new Array(101).fill(0).map((zero, index) => index * 0.01)
			};
		}
		else
		{
			observerCallback = function (entries, observer)
			{
				entries.forEach(function(entry)
				{
					if (entry.isIntersecting)
					{
						if (config.type === ObserverType.unread)
						{
							config.context.requestUnreadBlockIntersect = true;
							config.context.requestUnreadDelayed();
						}
						else
						{
							config.context.requestHistoryBlockIntersect = true;
							config.context.requestHistoryDelayed();
						}
					}
					else
					{
						if (config.type === ObserverType.unread)
						{
							config.context.requestUnreadBlockIntersect = false;
						}
						else
						{
							config.context.requestHistoryBlockIntersect = false;
						}
					}
				});
			};
			observerOptions = {
				root: config.context.$refs.body,
				threshold: [0, 0.01, 0.99, 1]
			};
		}

		return new IntersectionObserver(observerCallback, observerOptions);
	}
};

const Blocks = {
	getDelimiter(id = 0)
	{
		return {
			templateId: 'delimiter'+id,
			templateType: TemplateType.delimiter
		};
	},
	getGroup(id = 0, text = '')
	{
		return {
			templateId: 'group'+id,
			templateType: TemplateType.group,
			text: text
		};
	},
	getHistoryLoader()
	{
		return {
			templateId: 'historyLoader',
			templateType: TemplateType.historyLoader,
		};
	},
	getUnreadLoader()
	{
		return {
			templateId: 'unreadLoader',
			templateType: TemplateType.unreadLoader,
		};
	},
	getLoadButton(id = 0, text = '', type = LoadButtonTypes.before)
	{
		return {
			templateId: 'loadButton'+id+type,
			templateType: TemplateType.button,
			text: text,
			type: type,
			messageId: id
		};
	}
};
