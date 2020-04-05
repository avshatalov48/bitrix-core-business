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
import {MessagesModel} from 'im.model';
import {Vue} from "ui.vue";

const MessageType = Object.freeze({
	self: 'self',
	opponent: 'opponent',
	system: 'system',
});

Vue.component('bx-messenger-message',
{
	/**
	 * @emits 'clickByUserName' {user: object, event: MouseEvent}
	 * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	 * @emits 'clickByMessageRetry' {message: object, event: MouseEvent}
	 */
	props:
	{
		userId: { default: 0 },
		dialogId: { default: 0 },
		chatId: { default: 0 },
		enableEmotions: { default: true },
		enableDateActions: { default: true },
		enableCreateContent: { default: true },
		showAvatar: { default: true },
		showMenu: { default: true },
		showName: { default: true },
		referenceContentClassName: { default: ''},
		referenceContentBodyClassName: { default: ''},
		message: {
			type: Object,
			default: MessagesModel.create().getElementStore
		},
	},
	data()
	{
		return {
			componentBodyId: 'bx-messenger-message-body'
		}
	},
	methods:
	{
		clickByUserName(event)
		{
			this.$emit('clickByUserName', event)
		},
		clickByMessageMenu(message, event)
		{
			this.$emit('clickByMessageMenu', {message, event})
		},
		clickByMessageRetry(message, event)
		{
			this.$emit('clickByMessageRetry', {message, event})
		},
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
					'IM_MESSENGER_MESSAGE_MENU_TITLE': localize.IM_MESSENGER_MESSAGE_MENU_TITLE.replace('#SHORTCUT#', BX.Messenger.Utils.platform.isMac()? 'CMD':'CTRL')
				})
			);
		},

		userData()
		{
			let user = this.$store.getters['users/get'](this.message.authorId);
			return user? user: this.$store.getters['users/getBlank']();
		},

		filesData()
		{
			let files = this.$store.getters['files/getObject'](this.chatId);
			return files? files: {};
		},

		isEdited()
		{
			return this.message.params.IS_EDITED == 'Y';
		},

		isDeleted()
		{
			return this.message.params.IS_DELETED == 'Y';
		}
	},
	template: `
		<div :class="['bx-im-message', {
			'bx-im-message-without-menu': !showMenu,
			'bx-im-message-without-avatar': !showAvatar,
			'bx-im-message-type-system': type == MessageType.system,
			'bx-im-message-type-self': type == MessageType.self,
			'bx-im-message-type-opponent': type == MessageType.opponent,
			'bx-im-message-status-error': message.error,
			'bx-im-message-status-unread': message.unread,
			'bx-im-message-status-blink': message.blink,
			'bx-im-message-status-edited': isEdited,
			'bx-im-message-status-deleted': isDeleted,
		}]">
			<template v-if="type == MessageType.opponent">
				<div v-if="showAvatar" class="bx-im-message-avatar" @click="clickByUserName(userData, $event)">
					<div :class="['bx-im-message-avatar-image', {
							'bx-im-message-avatar-image-default': !userData.avatar
						}]"
						:style="{
							backgroundColor: !userData.avatar? userData.color: '', 
							backgroundImage: userData.avatar? 'url('+userData.avatar+')': ''
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
					:enableEmotions="enableEmotions"
					:referenceContentBodyClassName="referenceContentBodyClassName"
					@clickByUserName="clickByUserName"
				/>
			</div>	
			<template v-if="type == MessageType.self">
				<div class="bx-im-message-box-status">
					<transition name="bx-im-message-sending">
						<template v-if="message.sending">
							<div class="bx-im-message-sending"></div>
						</template>
					</transition>
					<transition name="bx-im-message-status-retry">
						<template v-if="!message.sending && message.error">
							<div class="bx-im-message-status-retry" :title="localize.IM_MESSENGER_MESSAGE_RETRY_TITLE" @click="clickByMessageRetry(message, $event)">
								<span class="bx-im-message-retry-icon"></span>
							</div>
						</template>
					</transition>
					<template v-if="showMenu && !message.sending && !message.error">
						<div class="bx-im-message-status-menu" :title="localize.IM_MESSENGER_MESSAGE_MENU_TITLE" @click="clickByMessageMenu(message, $event)">
							<span class="bx-im-message-menu-icon"></span>
						</div>
					</template> 
				</div>
			</template> 
			<template v-else-if="showMenu">
				<div class="bx-im-message-menu" :title="localize.IM_MESSENGER_MESSAGE_MENU_TITLE" @click="clickByMessageMenu(message, $event)">
					<span class="bx-im-message-menu-icon"></span>
				</div>
			</template> 
		</div>
	`
});