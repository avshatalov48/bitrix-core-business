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
import 'im.component.message';

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

const ReferenceClassName = Object.freeze({
	listItem: 'bx-im-dialog-list-item-reference',
	listItemBody: 'bx-im-dialog-list-item-content-reference',
	listUnreadLoader: 'bx-im-dialog-list-unread-loader-reference',
});

Vue.component('bx-messenger-dialog',
{
	/**
	 * @emits 'requestHistory' {lastId: number, limit: number}
	 * @emits 'requestUnread' {lastId: number, limit: number}
	 * @emits 'readMessage' {id: number}
	 * @emits 'click' {event: MouseEvent}
	 * @emits 'clickByUserName' {userData: object, event: MouseEvent}
	 * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	 * @emits 'clickByCommand' {type: string, value: string, event: MouseEvent}
	 */

	/**
	 * @listens props.listenEventScrollToBottom {force:boolean} (global|application) -- scroll dialog to bottom, see more in methods.onScrollToBottom()
	 * @listens props.listenEventRequestHistory {count:number} (application)
	 * @listens props.listenEventRequestUnread {count:number} (application)
	 */
	props:
	{
		userId: { default: 0 },
		dialogId: { default: 0 },
		chatId: { default: 0 },
		messageLimit: { default: 20 },
		listenEventScrollToBottom: { default: '' },
		listenEventRequestHistory: { default: '' },
		listenEventRequestUnread: { default: '' },
		enableEmotions: { default: true },
		enableDateActions: { default: true },
		enableCreateContent: { default: true },
		showMessageAvatar: { default: true },
		showMessageMenu: { default: true },
	},
	data()
	{
		return {
			showScrollButton: false,
			messageShowCount: 0,
			messageExtraCount: 0,
			unreadLoaderShow: false,
			unreadLoaderBlocked: false,
			historyLoaderBlocked: false,
			historyLoaderShow: false,
			startMessageLimit: 0,
			TemplateType: TemplateType,
			ObserverType: ObserverType,
			ReferenceClassName: ReferenceClassName,
		}
	},
	created()
	{
		this.scrollIsChanged = false;
		this.scrollBlocked = false;
		this.scrollButtonDiff = 30;
		this.scrollButtonShowTimeout = null;
		this.scrollPosition = 0;
		this.scrollPositionChangeTime = new Date().getTime();

		this.observers = {};

		this.requestHistoryInterval = null;
		this.requestUnreadInterval = null;

		this.lastAuthorId = 0;
		this.firstMessageId = null;
		this.firstUnreadMessageId = null;
		this.lastMessageId = null;
		this.dateFormatFunction = null;
		this.cacheGroupTitle = {};

		this.waitLoadHistory = false;
		this.waitLoadUnread = false;

		this.readMessageQueue = [];

		this.unreadLoaderBlocked = this.dialog.counter === 0;
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

		window.addEventListener('focus', this.onWindowFocus);
		window.addEventListener('blur', this.onWindowBlur);
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

		window.removeEventListener('focus', this.onWindowFocus);
		window.removeEventListener('blur', this.onWindowBlur);
	},
	mounted()
	{
		let body = this.$refs.body;
		let unreadId = this.dialog.unreadId;

		if (unreadId)
		{
			Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true)
		}
		else
		{
			body.scrollTop = body.scrollHeight - body.offsetHeight;
		}

		this.windowFocused = document.hasFocus();
	},
	beforeUpdate()
	{
		let body = this.$refs.body;

		if (this.scrollBlocked)
		{
			this.scrollIsChanged = false;
		}
		else
		{
			this.scrollIsChanged = body.scrollTop + this.scrollButtonDiff >= body.scrollHeight - body.offsetHeight;

			if (!this.scrollIsChanged && !this.showScrollButton && this.unreadCounter > 1)
			{
				this.showScrollButton = true;
			}
		}
	},
	updated()
	{
		if (!this.scrollIsChanged)
		{
			return;
		}

		this.$nextTick(() =>
		{
			let body = this.$refs.body;

			if (this.scrollIsChanged)
			{
				if (
					!this.windowFocused
					&& this.unreadCounter > 0
					&& !this.showScrollButton
				)
				{
					Utils.scrollToFirstUnreadMessage(this, this.collection, this.firstUnreadMessageId);

					return;
				}

				this.scrollTo(() =>
				{
					clearTimeout(this.scrollButtonShowTimeout);
					if (this.showScrollButton && this.windowFocused)
					{
						this.showScrollButton = false;
					}
				})
			}
		});
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
		collection()
		{
			return this.$store.getters['messages/get'](this.chatId);
		},
		elementsWithLimit()
		{
			let start = this.collection.length - (this.messageExtraCount + this.messageLimit);
			if (!this.historyLoaderShow || start < 0)
			{
				start = 0;
			}

			let collection = [];
			let lastAuthorId = 0;
			let groupNode = {};

			let slicedCollection = start == 0? this.collection: this.collection.slice(start, this.collection.length);

			this.messageShowCount = slicedCollection.length;

			if (this.messageShowCount > 0)
			{
				this.firstMessageId = slicedCollection[0].id;
				this.lastMessageId = slicedCollection[slicedCollection.length-1].id;
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

			this.firstUnreadMessageId = 0;
			let unreadCountInSlicedCollection = 0;
			slicedCollection.forEach(element =>
			{
				let group = this._groupTitle(element.date);
				if (!groupNode[group.id])
				{
					groupNode[group.id] = true;
					collection.push(Blocks.getGroup(group.id, group.title));
				}
				else if (lastAuthorId != element.authorId)
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

			if (
				this.dialog.unreadLastId > this.lastMessageId
				&& this.unreadLoaderBlocked === false
			)
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
			if (this.dialog.writingList.length == 0)
				return '';

			let users = this.dialog.writingList.map(element => element.userName);

			return this.localize.IM_MESSENGER_DIALOG_WRITES_MESSAGE.replace(
				'#USER#', users.join(', ')
			);
		},
		statusReaded()
		{
			return false;
		},
		unreadCounter()
		{
			return this.dialog.counter > 999? 999: this.dialog.counter;
		},
	},
	methods:
	{
		onDialogClick(event)
		{
			if (Vue.testNode(event.target, {className: 'bx-im-message-command'}))
			{
				this.onCommandClick(event);
			}

			this.windowFocused = true;
			this.$emit('click', {event});
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
		onScroll(event)
		{
			this.scrollPosition = event.target.scrollTop;
			this.scrollPositionChangeTime = new Date().getTime();

			clearTimeout(this.scrollButtonShowTimeout);
			this.scrollButtonShowTimeout = setTimeout(() =>
			{
				if (event.target.scrollTop + this.scrollButtonDiff >= event.target.scrollHeight - event.target.offsetHeight)
				{
					if (this.showScrollButton && !this.unreadLoaderShow && this.windowFocused)
					{
						this.showScrollButton = false;
					}
				}
				else
				{
					if (!this.showScrollButton)
					{
						this.showScrollButton = true;
					}
				}
			}, 200);

			if (event.target.scrollTop == event.target.scrollHeight - event.target.offsetHeight)
			{
				clearTimeout(this.scrollButtonShowTimeout);
				if (this.showScrollButton && !this.unreadLoaderShow && this.windowFocused)
				{
					this.showScrollButton = false;
				}
			}
		},
		scrollToBottom(force = false)
		{
			let body = this.$refs.body;

			if (this.dialog.counter > 0)
			{
				let scrollToMessageId = this.dialog.counter > 1? this.firstUnreadMessageId: this.lastMessageId;
				Utils.scrollToFirstUnreadMessage(this, this.collection, scrollToMessageId);

				if (this.dialog.counter < this.startMessageLimit)
				{
					this.messageExtraCount = 0;
					this.historyLoaderShow = true;
					this.historyLoaderBlocked = false;
				}

				return true;
			}

			this.showScrollButton = false;

			if (force)
			{
				body.scrollTop = body.scrollHeight - body.offsetHeight;
				this.messageExtraCount = 0;
				this.historyLoaderShow = true;
				this.historyLoaderBlocked = false;
			}
			else
			{
				this.scrollTo(() => {
					this.messageExtraCount = 0;
					this.historyLoaderShow = true;
					this.historyLoaderBlocked = false;
				});
			}
		},
		scrollTo(params)
		{
			let body = this.$refs.body;

			if (typeof params === 'function')
			{
				params = {callback: params};
			}
			if (!body)
			{
				if (params.callback && typeof params.callback === 'function')
				{
					params.callback();
				}
				return true;
			}

			let {
				start = body.scrollTop,
				end = body.scrollHeight - body.offsetHeight,
				increment = 20,
				callback,
				duration = 300
			} = params;

			let diff = end - start;
			let currentPosition = 0;

			const easeInOutQuad = function (current, start, diff, duration)
			{
				current /= duration/2;

				if (current < 1)
				{
					return diff / 2 * current * current + start;
				}

				current--;

				return -diff/2 * (current*(current-2) - 1) + start;
			};

			const requestFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function( callback ){ window.setTimeout(callback, 1000 / 60); };

			const animateScroll = () =>
			{
				currentPosition += increment;

				this.$refs.body.scrollTop = easeInOutQuad(currentPosition, start, diff, duration);

				if (currentPosition < duration)
				{
					requestFrame(animateScroll);
				}
				else
				{
					if (callback && typeof callback === 'function')
					{
						callback();
					}
				}
			};

			animateScroll();
		},
		onScrollToBottom(event = {})
		{
			event.force = event.force === true;

			this.scrollToBottom(event.force);

			return true;
		},
		onWindowFocus(event = {})
		{
			this.windowFocused = true;

			this.readMessageQueue = this.readMessageQueue.map(messageId => {
				this.requestReadMessage(messageId);
				return false;
			});
		},
		onWindowBlur(event = {})
		{
			this.windowFocused = false;
		},
		requestHistoryDelayed()
		{
			if (this.requestHistoryInterval)
			{
				BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestHistoryDelayed: skipped');
				return false;
			}

			if (
				this.scrollPositionChangeTime + 100 < new Date().getTime()
				&& this.$refs.body.scrollTop >= 0
			)
			{
				clearInterval(this.requestHistoryInterval);
				this.requestHistoryInterval = null;
				this.requestHistory();
				return true;
			}

			clearInterval(this.requestHistoryInterval);
			this.requestHistoryInterval = setInterval(() => {
				if (
					this.scrollPositionChangeTime + 100 < new Date().getTime()
					&& this.$refs.body.scrollTop >= 0
				)
				{
					clearInterval(this.requestHistoryInterval);
					this.requestHistoryInterval = null;
					this.requestHistory();
					return true;
				}
			}, 50);

			return true;
		},
		requestHistory()
		{
			if (this.waitLoadHistory)
			{
				BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestHistory: waitLoadHistory not empty');
				return false;
			}

			this.waitLoadHistory = true;

			let length = this.collection.length;
			let messageShowCount = this.messageShowCount;
			if (length > messageShowCount)
			{
				let element = this.$refs.body.getElementsByClassName(ReferenceClassName.listItem)[0];

				this.messageExtraCount += this.messageLimit;
				Utils.scrollToElementAfterLoadHistory(this, element);

				return true;
			}

			this.$emit('requestHistory', {lastId: this.firstMessageId});
		},
		requestUnreadDelayed()
		{
			if (this.requestUnreadInterval)
			{
				BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestUnreadDelayed: skipped');
				return false;
			}

			let body = this.$refs.body;

			if (
				this.scrollPositionChangeTime + 100 < new Date().getTime()
				&& body.scrollTop <= body.scrollHeight - body.offsetHeight
			)
			{
				clearInterval(this.requestUnreadInterval);
				this.requestUnreadInterval = null;
				this.requestUnread();
				return true;
			}

			clearInterval(this.requestUnreadInterval);
			this.requestUnreadInterval = setInterval(() => {
				if (
					this.scrollPositionChangeTime + 100 < new Date().getTime()
					&& body.scrollTop <= body.scrollHeight - body.offsetHeight
				)
				{
					clearInterval(this.requestUnreadInterval);
					this.requestUnreadInterval = null;
					this.requestUnread();
					return true;
				}
			}, 50);

			return true;
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
				this.messageExtraCount += event.count;
			}

			if (this.historyLoaderBlocked)
			{
				this.historyLoaderShow = false;
			}

			let element = this.$refs.body.getElementsByClassName(ReferenceClassName.listItem)[0];

			if (event.count > 0)
			{
				Utils.scrollToElementAfterLoadHistory(this, element);
			}
			else if (event.error)
			{
				element.scrollIntoView(true);
				this.waitLoadHistory = false;
			}
			else
			{
				this.$refs.body.scrollTop = 0;
				this.waitLoadHistory = false;
			}

			return true;
		},
		requestUnread()
		{
			if (this.waitLoadUnread)
			{
				BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestUnread: waitLoadUnread not empty');
				return false;
			}

			this.waitLoadUnread = true;

			this.$emit('requestUnread', {lastId: this.lastMessageId});
		},
		onRequestUnreadAnswer(event = {})
		{
			if (event.error)
			{
				this.historyLoaderBlocked = false;
			}
			else
			{
				this.unreadLoaderBlocked = event.count < this.startMessageLimit;
				this.messageExtraCount += event.count;
			}

			if (this.unreadLoaderBlocked)
			{
				this.unreadLoaderShow = false;
			}

			let body = this.$refs.body;
			if (event.count > 0)
			{
				Utils.scrollToElementAfterLoadUnread(this);
			}
			else if (event.error)
			{
				let element = this.$refs.body.getElementsByClassName(ReferenceClassName.listUnreadLoader)[0];
				if (element)
				{
					body.scrollTop = body.scrollTop - element.offsetHeight*2;
				}
				else
				{
					body.scrollTop = body.scrollHeight - body.offsetHeight;
				}
				this.waitLoadUnread = false;
			}
			else
			{
				body.scrollTop = body.scrollHeight - body.offsetHeight;
				this.waitLoadUnread = false;
			}

			return true;
		},
		readMessage(messageId)
		{
			if (this.windowFocused)
			{
				this.$emit('readMessage', {id: messageId});
			}
			else
			{
				this.readMessageQueue.push(messageId);
			}
		},
		requestReadMessage(messageId)
		{
			this.$emit('readMessage', {id: messageId});
		},

		onClickByUserName(event)
		{
			this.$emit('clickByUserName', event)
		},

		onClickByMessageMenu(event)
		{
			this.$emit('clickByMessageMenu', event)
		},

		onClickByMessageRetry(event)
		{
			this.$emit('clickByMessageRetry', event)
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

			let dateFormat = BX.Messenger.Utils.getDateFormatType(
				BX.Messenger.Const.DateFormat.groupTitle,
				this.$root.$bitrixMessages
			);

			this.cacheGroupTitle[id] = this._getDateFormat().format(dateFormat, date);

			return {
				id: id,
				title: this.cacheGroupTitle[id]
			};
		},
	},

	directives:
	{
		'bx-messenger-dialog-observer':
		{
			inserted(element, bindings, vnode)
			{
				if (bindings.value == ObserverType.none)
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
				if (bindings.value == ObserverType.none)
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
		<div class="bx-im-dialog" @click="onDialogClick">	
			<div class="bx-im-dialog-list" @scroll.passive="onScroll" ref="body">
				<template v-if="historyLoaderShow">
					<div class="bx-im-dialog-load-more bx-im-dialog-load-more-history" v-bx-messenger-dialog-observer="ObserverType.history">
						<span class="bx-im-dialog-load-more-text">{{ localize.IM_MESSENGER_DIALOG_LOAD_MESSAGES }}</span>
					</div>
				</template>
				<transition-group tag="div" class="bx-im-dialog-list-box" name="bx-im-dialog-message-animation" >
					<template v-for="element in elementsWithLimit">
						<template v-if="element.templateType == TemplateType.message">
							<div :class="['bx-im-dialog-list-item', ReferenceClassName.listItem, ReferenceClassName.listItem+'-'+element.id]" :data-message-id="element.id" :key="element.templateId" v-bx-messenger-dialog-observer="element.unread? ObserverType.read: ObserverType.none">			
								<component :is="element.params.COMPONENT_ID"
									:userId="userId" 
									:dialogId="dialogId"
									:chatId="chatId"
									:message="element"
									:enableEmotions="enableEmotions"
									:enableDateActions="enableDateActions"
									:enableCreateContent="showMessageMenu"
									:showAvatar="showMessageAvatar"
									:showMenu="showMessageMenu"
									:referenceContentClassName="ReferenceClassName.listItem"
									:referenceContentBodyClassName="ReferenceClassName.listItemBody"
									@clickByUserName="onClickByUserName"
									@clickByMessageMenu="onClickByMessageMenu"
									@clickByMessageRetry="onClickByMessageRetry"
								/>
							</div>
						</template>
						<template v-else-if="element.templateType == TemplateType.group">
							<div class="bx-im-dialog-group" :key="element.templateId">
								<div class="bx-im-dialog-group-date">{{ element.text }}</div>
							</div>
						</template>
						<template v-else-if="element.templateType == TemplateType.delimiter">
							<div class="bx-im-dialog-delimiter" :key="element.templateId" ></div>
						</template>
					</template>
				</transition-group>
				<template v-if="unreadLoaderShow">
					<div :class="['bx-im-dialog-load-more', 'bx-im-dialog-load-more-unread', ReferenceClassName.listUnreadLoader]" v-bx-messenger-dialog-observer="ObserverType.unread">
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
						<div class="bx-im-dialog-status">
							<span class="bx-im-dialog-status-readed"></span>
							{{ statusReaded }}
						</div>
					</template>
				</transition>
			</div>
			<transition name="bx-im-dialog-scroll-button">
				<div v-show="showScrollButton || unreadLoaderShow && unreadCounter" class="bx-im-dialog-scroll-button" @click="scrollToBottom()">
					<div v-show="unreadCounter" class="bx-im-dialog-scroll-button-counter">
						<div class="bx-im-dialog-scroll-button-counter-digit">{{unreadCounter}}</div>
					</div>
					<div class="bx-im-dialog-scroll-button-arrow"></div>
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

	scrollToFirstUnreadMessage(context, collection, unreadId = null, force = false)
	{
		let body = context.$refs.body;

		let element = false;
		if (unreadId !== null)
		{
			element = body.getElementsByClassName(ReferenceClassName.listItem+'-'+unreadId)[0];
		}
		if (!element)
		{
			for (let index = collection.length-1; index >= 0; index--)
			{
				if (!collection[index].unread)
				{
					break;
				}

				unreadId = collection[index].id;
			}
			element = body.getElementsByClassName(ReferenceClassName.listItem+'-'+unreadId)[0];
		}

		let end = 0;
		if (element)
		{
			end = element.offsetTop - 20;
		}
		else
		{
			end = body.scrollHeight - body.offsetHeight;
		}

		if (force)
		{
			body.scrollTop = end;
		}
		else
		{
			context.scrollTo({end});
		}
	},

	scrollToElementAfterLoadHistory(context, element)
	{
		if (!element)
		{
			context.waitLoadHistory = false;
			return false;
		}

		let elementBody = element.getElementsByClassName(ReferenceClassName.listItemBody)[0];
		if (elementBody)
		{
			element = elementBody;
		}

		let previousOffsetTop = element.offsetTop;

		context.$nextTick(() => {
			if (!element)
			{
				return false;
			}

			context.$refs.body.scrollTop = element.offsetTop - previousOffsetTop;

			context.waitLoadHistory = false;
		});
	},

	scrollToElementAfterLoadUnread(context)
	{
		context.scrollBlocked = true;
		context.showScrollButton = true;

		context.$nextTick(() => {
			context.scrollBlocked = false;
			context.waitLoadUnread = false;
		});
	},

	getMessageLoaderObserver(config)
	{
		if (
			typeof window.IntersectionObserver === 'undefined'
			|| config.value == ObserverType.none
		)
		{
			return {
				observe: () => {},
				unobserve: () => {}
			};
		}

		let observerCallback, observerOptions;

		if (config.type == ObserverType.read)
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
							&& entry.intersectionRect.height > entry.rootBounds.height - 20
						)
						{
							sendReadEvent = true;
						}
					}
					if (sendReadEvent)
					{
						config.context.readMessage(entry.target.dataset.messageId);
						config.context.observers[config.type].unobserve(entry.target);
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
					if (entry.isIntersecting && entry.intersectionRatio > 0)
					{
						if (config.type == ObserverType.unread)
						{
							config.context.requestUnreadDelayed();
						}
						else
						{
							config.context.requestHistoryDelayed();
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
