/**
 * Bitrix Messenger
 * Message Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './message.css';
import 'im.component.message.body';

import {DialoguesModel, MessagesModel} from 'im.model';
import {Vue} from "ui.vue";
import {MessageType} from "im.const";
import {Utils} from "im.utils";
import {Animation} from "im.tools.animation";

Vue.component('bx-messenger-message',
{
	/**
	 * @emits 'clickByUserName' {user: object, event: MouseEvent}
	 * @emits 'clickByUploadCancel' {file: object, event: MouseEvent}
	 * @emits 'clickByKeyboardButton' {message: object, action: string, params: Object}
	 * @emits 'clickByChatTeaser' {message: object, event: MouseEvent}
	 * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	 * @emits 'clickByMessageRetry' {message: object, event: MouseEvent}
	 * @emits 'setMessageReaction' {message: object, reaction: object}
	 * @emits 'openMessageReactionList' {message: object, values: object}
	 * @emits 'dragMessage' {result: boolean, event: MouseEvent}
	 * @emits 'quoteMessage' {message: object}
	 */
	props:
	{
		userId: { default: 0 },
		dialogId: { default: 0 },
		chatId: { default: 0 },
		enableReactions: { default: true },
		enableDateActions: { default: true },
		enableCreateContent: { default: true },
		enableGestureQuote: { default: true },
		enableGestureQuoteFromRight: { default: true },
		enableGestureMenu: { default: false },
		showAvatar: { default: true },
		showMenu: { default: true },
		showName: { default: true },
		showLargeFont: { default: true },
		capturedMoveEvent: { default: null },
		referenceContentClassName: { default: ''},
		referenceContentBodyClassName: { default: ''},
		referenceContentNameClassName: { default: ''},
		dialog: {
			type: Object,
			default: DialoguesModel.create().getElementState
		},
		message: {
			type: Object,
			default: MessagesModel.create().getElementState
		},
	},
	data()
	{
		return {
			componentBodyId: 'bx-messenger-message-body',
			drag: false,
			dragWidth: 0,
			dragPosition: 0,
			dragIconShowLeft: false,
			dragIconShowRight: false,
		}
	},
	created()
	{
		this.dragStartPositionX = 0;
		this.dragStartPositionY = 0;
		this.dragMovePositionX = 0;
		this.dragMovePositionY = 0;
	},
	beforeDestroy()
	{
		clearTimeout(this.dragStartTimeout1);
		clearTimeout(this.dragStartTimeout2);

		if (this.dragBackAnimation)
		{
			Animation.cancel(this.dragBackAnimation);
		}
	},
	methods:
	{
		clickByAvatar(event)
		{
			this.$emit('clickByUserName', event)
		},
		clickByUserName(event)
		{
			if (this.showAvatar && Utils.platform.isMobile())
			{
				return false;
			}

			this.$emit('clickByUserName', event)
		},
		clickByUploadCancel(event)
		{
			this.$emit('clickByUploadCancel', event)
		},
		clickByKeyboardButton(event)
		{
			this.$emit('clickByKeyboardButton', event)
		},
		clickByChatTeaser(event)
		{
			this.$emit('clickByChatTeaser', event)
		},
		clickByMessageMenu(event)
		{
			this.$emit('clickByMessageMenu', event)
		},
		clickByMessageRetry(event)
		{
			this.$emit('clickByMessageRetry', event)
		},
		setMessageReaction(event)
		{
			this.$emit('setMessageReaction', event)
		},
		openMessageReactionList(event)
		{
			this.$emit('openMessageReactionList', event)
		},
		gestureRouter(eventName, event)
		{
			this.gestureQuote(eventName, event);
			this.gestureMenu(eventName, event);
		},
		gestureMenu(eventName, event)
		{
			if (!this.enableGestureMenu)
			{
				return;
			}

			if (eventName === 'touchstart')
			{
				this.gestureMenuStarted = true;
				this.gestureMenuPreventTouchEnd = false;
				if (event.target.tagName === "A")
				{
					return false;
				}

				this.gestureMenuStartPosition = {
					x: event.changedTouches[0].clientX,
					y: event.changedTouches[0].clientY
				};

				this.gestureMenuTimeout = setTimeout(() => {
					this.gestureMenuPreventTouchEnd = true;
					this.$emit('clickByMessageMenu', {message: this.message, event});
				}, 500);
			}
			else if (eventName === 'touchmove')
			{
				if (!this.gestureMenuStarted)
				{
					return false;
				}

				if (
					Math.abs(this.gestureMenuStartPosition.x - event.changedTouches[0].clientX) >= 10
					|| Math.abs(this.gestureMenuStartPosition.y - event.changedTouches[0].clientY) >= 10
				)
				{
					this.gestureMenuStarted = false;
					clearTimeout(this.gestureMenuTimeout);
				}
			}
			else if (eventName === 'touchend')
			{
				if (!this.gestureMenuStarted)
				{
					return false;
				}

				this.gestureMenuStarted = false;
				clearTimeout(this.gestureMenuTimeout);
				if (this.gestureMenuPreventTouchEnd)
				{
					event.preventDefault();
				}
			}
		},
		gestureQuote(eventName, event)
		{
			let target = Utils.browser.findParent(event.target, 'bx-im-message') || event.target;
			if (!this.enableGestureQuote || Utils.platform.isAndroid())
			{
				return;
			}

			const fromRight = this.enableGestureQuoteFromRight;

			const layerX = target.getBoundingClientRect().left + event.layerX;
			const layerY = target.getBoundingClientRect().top + event.layerY;

			if (eventName === 'touchstart')
			{
				this.dragCheck = true;

				this.dragStartInitialX = target.getBoundingClientRect().left;
				this.dragStartInitialY = target.getBoundingClientRect().top;
				this.dragStartPositionX = layerX;
				this.dragStartPositionY = layerY;
				this.dragMovePositionX = null;
				this.dragMovePositionY = null;

				clearTimeout(this.dragStartTimeout1);
				clearTimeout(this.dragStartTimeout2);
				this.dragStartTimeout1 = setTimeout(() => {
					if (this.dragMovePositionX !== null)
					{
						if (Math.abs(this.dragStartPositionY - this.dragMovePositionY) >= 10)
						{
							this.dragCheck = false;
						}
					}
				}, 29);

				this.dragStartTimeout2 = setTimeout(() => {
					this.dragCheck = false;

					if (Math.abs(this.dragStartPositionY - this.dragMovePositionY) >= 10)
					{
						return;
					}

					if (this.dragMovePositionX === null)
					{
						return;
					}
					else if (fromRight && this.dragStartPositionX - this.dragMovePositionX < 9)
					{
						return;
					}
					else if (!fromRight && this.dragStartPositionX - this.dragMovePositionX > 9)
					{
						return;
					}

					Animation.cancel(this.dragBackAnimation);

					this.drag = true;

					this.$emit('dragMessage', {result: this.drag, event});
					this.dragWidth = this.$refs.body.offsetWidth;

				}, 80);
			}
			else if (eventName === 'touchmove')
			{
				if (this.drag || !this.dragCheck)
				{
					return false;
				}

				this.dragMovePositionX = layerX;
				this.dragMovePositionY = layerY;
			}
			else if (eventName === 'touchend')
			{
				clearTimeout(this.dragStartTimeout1);
				clearTimeout(this.dragStartTimeout2);

				this.dragCheck = false;

				if (!this.drag)
				{
					this.dragIconShowLeft = false;
					this.dragIconShowRight = false;

					return;
				}

				Animation.cancel(this.dragBackAnimation);

				this.drag = false;
				this.$emit('dragMessage', {result: this.drag, event});

				if (
					this.enableGestureQuoteFromRight && this.dragIconShowRight && this.dragPosition !== 0
					|| !this.enableGestureQuoteFromRight && this.dragIconShowLeft && this.dragPosition !== this.dragStartInitialX
				)
				{
					if (Utils.platform.isBitrixMobile())
					{
						setTimeout(() => app.exec("callVibration"), 200);
					}
					this.$emit('quoteMessage', {message: this.message});
				}

				this.dragIconShowLeft = false;
				this.dragIconShowRight = false;

				this.dragBackAnimation = Animation.start({
					start: this.dragPosition,
					end: this.dragStartInitialX,
					increment: 20,
					duration:  300,

					element: this,
					elementProperty: 'dragPosition',

					callback: () => {
						this.dragLayerPosition = undefined;
						this.dragWidth = 0;
						this.dragPosition = 0;
					},
				});
			}
		},
	},
	watch:
	{
		capturedMoveEvent(event)
		{
			if (!this.drag || !event)
			{
				return;
			}

			let target = Utils.browser.findParent(event.target, 'bx-im-message') || event.target;

			const layerX = target.getBoundingClientRect().left + event.layerX;

			if (typeof this.dragLayerPosition === 'undefined')
			{
				this.dragLayerPosition = layerX;
			}

			const movementX = this.dragLayerPosition - layerX;

			this.dragLayerPosition = layerX;

			this.dragPosition = this.dragPosition - movementX;

			if (this.enableGestureQuoteFromRight)
			{
				const dragPositionMax = (this.showAvatar? 30: 0) + 45;
				const dragPositionIcon = (this.showAvatar? 30: 30);

				if (this.dragPosition < -dragPositionMax)
				{
					this.dragPosition = -dragPositionMax;
				}
				else if (this.dragPosition < -dragPositionIcon)
				{
					if (!this.dragIconShowRight)
					{
						this.dragIconShowRight = true;
					}
				}
				else if (this.dragPosition >= 0)
				{
					this.dragPosition = 0;
				}
			}
			else
			{
				const dragPositionMax = 60;
				const dragPositionIcon = 40;

				if (this.dragPosition <= this.dragStartInitialX)
				{
					this.dragPosition = this.dragStartInitialX;
				}
				else if (this.dragPosition >= dragPositionMax)
				{
					this.dragPosition = dragPositionMax;
				}
				else if (this.dragPosition >= dragPositionIcon)
				{
					if (!this.dragIconShowLeft)
					{
						this.dragIconShowLeft = true;
					}
				}
			}
		}
	},
	computed:
	{
		MessageType: () => MessageType,

		type()
		{
			if (this.message.system || this.message.authorId == 0)
			{
				return MessageType.system;
			}
			else if (this.message.authorId === -1 || this.message.authorId == this.userId)
			{
				return MessageType.self;
			}
			else
			{
				return MessageType.opponent;
			}
		},

		localize()
		{
			let localize = Vue.getFilteredPhrases('IM_MESSENGER_MESSAGE_', this.$root.$bitrixMessages);

			return Object.freeze(
				Object.assign({}, localize, {
					'IM_MESSENGER_MESSAGE_MENU_TITLE': localize.IM_MESSENGER_MESSAGE_MENU_TITLE.replace('#SHORTCUT#', Utils.platform.isMac()? 'CMD':'CTRL')
				})
			);
		},

		userData()
		{
			return this.$store.getters['users/get'](this.message.authorId, true);
		},

		userAvatar()
		{
			if (this.message.params.AVATAR)
			{
				return `url('${this.message.params.AVATAR}')`;
			}
			if (this.userData.avatar)
			{
				return `url('${this.userData.avatar}')`;
			}
			return '';
		},

		filesData()
		{
			let files = this.$store.getters['files/getList'](this.chatId);
			return files? files: {};
		},

		isEdited()
		{
			return this.message.params.IS_EDITED === 'Y';
		},

		isDeleted()
		{
			return this.message.params.IS_DELETED === 'Y';
		},

		isLargeFont()
		{
			return this.showLargeFont && this.message.params.LARGE_FONT === 'Y';
		},
	},
	template: `
		<div :class="['bx-im-message', {
				'bx-im-message-without-menu': !showMenu,
				'bx-im-message-without-avatar': !showAvatar,
				'bx-im-message-type-system': type === MessageType.system,
				'bx-im-message-type-self': type === MessageType.self,
				'bx-im-message-type-other': type !== MessageType.self,
				'bx-im-message-type-opponent': type === MessageType.opponent,
				'bx-im-message-status-error': message.error,
				'bx-im-message-status-unread': message.unread,
				'bx-im-message-status-blink': message.blink,
				'bx-im-message-status-edited': isEdited,
				'bx-im-message-status-deleted': isDeleted,
				'bx-im-message-large-font': isLargeFont,
			}]" 
			@touchstart="gestureRouter('touchstart', $event)"
			@touchmove="gestureRouter('touchmove', $event)"
			@touchend="gestureRouter('touchend', $event)"
			ref="body"
			:style="{
				width: dragWidth > 0? dragWidth+'px': '', 
				marginLeft: (enableGestureQuoteFromRight && dragPosition < 0) || (!enableGestureQuoteFromRight && dragPosition > 0)? dragPosition+'px': '',
			}"
		>
			<template v-if="type === MessageType.self">
				<template v-if="dragIconShowRight">
					<div class="bx-im-message-reply bx-im-message-reply-right">
						<div class="bx-im-message-reply-icon"></div>
					</div>
				</template> 
				<div class="bx-im-message-box">
					<component :is="componentBodyId"
						:userId="userId" 
						:dialogId="dialogId"
						:chatId="chatId"
						:messageType="type"
						:dialog="dialog"
						:message="message"
						:user="userData"
						:files="filesData"
						:showAvatar="showAvatar"
						:showName="showName"
						:enableReactions="enableReactions"
						:referenceContentBodyClassName="referenceContentBodyClassName"
						:referenceContentNameClassName="referenceContentNameClassName"
						@clickByUserName="clickByUserName"
						@clickByUploadCancel="clickByUploadCancel"
						@clickByKeyboardButton="clickByKeyboardButton"
						@clickByChatTeaser="clickByChatTeaser"
						@setReaction="setMessageReaction"
						@openReactionList="openMessageReactionList"
					/>
				</div>
				<div class="bx-im-message-box-status">
					<template v-if="message.sending">
						<div class="bx-im-message-sending"></div>
					</template>
					<transition name="bx-im-message-status-retry">
						<template v-if="!message.sending && message.error && message.retry">
							<div class="bx-im-message-status-retry" :title="localize.IM_MESSENGER_MESSAGE_RETRY_TITLE" @click="clickByMessageRetry({message: message, event: $event})">
								<span class="bx-im-message-retry-icon"></span>
							</div>
						</template>
					</transition>
					<template v-if="showMenu && !message.sending && !message.error">
						<div class="bx-im-message-status-menu" :title="localize.IM_MESSENGER_MESSAGE_MENU_TITLE" @click="clickByMessageMenu({message: message, event: $event})">
							<span class="bx-im-message-menu-icon"></span>
						</div>
					</template> 
				</div>
				<template v-if="dragIconShowLeft">
					<div class="bx-im-message-reply bx-im-message-reply-left">
						<div class="bx-im-message-reply-icon"></div>
					</div>
				</template> 
			</template>
			<template v-else-if="type !== MessageType.self">
				<template v-if="dragIconShowLeft">
					<div class="bx-im-message-reply bx-im-message-reply-left">
						<div class="bx-im-message-reply-icon"></div>
					</div>
				</template> 
				<template v-if="type === MessageType.opponent">
					<div v-if="showAvatar" class="bx-im-message-avatar" @click="clickByAvatar({user: userData, event: $event})">
						<div :class="['bx-im-message-avatar-image', {
								'bx-im-message-avatar-image-default': !userData.avatar
							}]"
							:style="{
								backgroundColor: !userData.avatar? userData.color: '', 
								backgroundImage: userAvatar
							}" 
							:title="userData.name"
						></div>	
					</div>
				</template>
				<div class="bx-im-message-box">
					<component :is="componentBodyId"
						:userId="userId" 
						:dialogId="dialogId"
						:chatId="chatId"
						:messageType="type"
						:message="message"
						:user="userData"
						:files="filesData"
						:showAvatar="showAvatar"
						:showName="showName"
						:enableReactions="enableReactions"
						:referenceContentBodyClassName="referenceContentBodyClassName"
						:referenceContentNameClassName="referenceContentNameClassName"
						@clickByUserName="clickByUserName"
						@clickByUploadCancel="clickByUploadCancel"
						@clickByKeyboardButton="clickByKeyboardButton"
						@clickByChatTeaser="clickByChatTeaser"
						@setReaction="setMessageReaction"
						@openReactionList="openMessageReactionList"
					/>
				</div>
				<div v-if="showMenu"  class="bx-im-message-menu" :title="localize.IM_MESSENGER_MESSAGE_MENU_TITLE" @click="clickByMessageMenu({message: message, event: $event})">
					<span class="bx-im-message-menu-icon"></span>
				</div>	
				<template v-if="dragIconShowRight">
					<div class="bx-im-message-reply bx-im-message-reply-right">
						<div class="bx-im-message-reply-icon"></div>
					</div>
				</template> 
			</template>
		</div>
	`
});