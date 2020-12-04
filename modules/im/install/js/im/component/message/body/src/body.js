/**
 * Bitrix Messenger
 * Message Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './body.css';
import 'im.component.element.media';
import 'im.component.element.attach';
import 'im.component.element.keyboard';
import 'im.component.element.chatteaser';
import 'ui.vue.components.reaction';

import {Vue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import {DialoguesModel, FilesModel, MessagesModel, UsersModel} from 'im.model';
import {DialogType, MessageType} from "im.const";
import {Utils} from "im.utils";

const BX = window.BX;

const ContentType = Object.freeze({
	default: 'default',
	progress: 'progress',
	image: 'image',
	audio: 'audio',
	video: 'video',
	richLink: 'richLink',
});

Vue.component('bx-messenger-message-body',
{
	/**
	 * @emits 'clickByUserName' {user: object, event: MouseEvent}
	 * @emits 'clickByUploadCancel' {file: object, event: MouseEvent}
	 * @emits 'clickByChatTeaser' {params: object, event: MouseEvent}
	 * @emits 'clickByKeyboardButton' {message: object, action: string, params: Object}
	 * @emits 'setReaction' {message: object, reaction: object}
	 * @emits 'openReactionList' {message: object, values: object}
	 */
	props:
	{
		userId: { default: 0 },
		dialogId: { default: '0' },
		chatId: { default: 0 },
		messageType: { default: MessageType.self },
		message: {
			type: Object,
			default: MessagesModel.create().getElementState
		},
		user: {
			type: Object,
			default: UsersModel.create().getElementState
		},
		dialog: {
			type: Object,
			default: DialoguesModel.create().getElementState
		},
		files: {
			type: Object,
			default: {}
		},
		enableReactions: { default: true },
		showName: { default: true },
		showAvatar: { default: true },
		referenceContentBodyClassName: { default: ''},
		referenceContentNameClassName: { default: ''},
	},
	created()
	{
		this.dateFormatFunction = null;
		this.cacheFormatDate = {};
	},
	methods:
	{
		clickByUserName(event)
		{
			this.$emit('clickByUserName', event)
		},
		clickByUploadCancel(event)
		{
			this.$emit('clickByUploadCancel', event)
		},
		clickByChatTeaser(event)
		{
			this.$emit('clickByChatTeaser', {message: event.message, event});
		},
		clickByKeyboardButton(event)
		{
			this.$emit('clickByKeyboardButton', {message: event.message, ...event.event});
		},
		setReaction(event)
		{
			this.$emit('setReaction', event)
		},
		openReactionList(event)
		{
			this.$emit('openReactionList', event)
		},
		formatDate(date)
		{
			const id = date.toJSON().slice(0, 10);

			if (this.cacheFormatDate[id])
			{
				return this.cacheFormatDate[id];
			}

			let dateFormat = Utils.date.getFormatType(
				BX.Messenger.Const.DateFormat.message,
				this.$root.$bitrixMessages
			);

			this.cacheFormatDate[id] = this._getDateFormat().format(dateFormat, date);

			return this.cacheFormatDate[id];
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
	},
	computed:
	{
		MessageType: () => MessageType,
		ContentType: () => ContentType,

		contentType()
		{
			if (this.filesData.length > 0)
			{
				let onlyImage = false;
				let onlyVideo = false;
				let onlyAudio = false;
				let inProgress = false;

				for (let file of this.filesData)
				{
					if (file.progress < 0)
					{
						inProgress = true;
						break;
					}
					else if (file.type === 'audio')
					{
						if (onlyVideo || onlyImage)
						{
							onlyImage = false;
							onlyVideo = false;
							break;
						}
						onlyAudio = true;
					}
					else if (file.type === 'image' && file.image)
					{
						if (onlyVideo || onlyAudio)
						{
							onlyAudio = false;
							onlyVideo = false;
							break;
						}
						onlyImage = true;
					}
					else if (file.type === 'video')
					{
						if (onlyImage || onlyAudio)
						{
							onlyAudio = false;
							onlyImage = false;
							break;
						}
						onlyVideo = true;
					}
					else
					{
						onlyAudio = false;
						onlyImage = false;
						onlyVideo = false;
						break;
					}
				}

				if (inProgress)
				{
					return ContentType.progress;
				}
				else if (onlyImage)
				{
					return ContentType.image;
				}
				else if (onlyAudio)
				{
					return ContentType.audio;
				}
				else if (onlyVideo)
				{
					return ContentType.video;
				}
			}

			return ContentType.default;
		},

		localize()
		{
			return Vue.getFilteredPhrases('IM_MESSENGER_MESSAGE_', this.$root.$bitrixMessages);
		},

		formattedDate()
		{
			return this.formatDate(this.message.date);
		},

		messageText()
		{
			if (this.isDeleted)
			{
				return this.localize.IM_MESSENGER_MESSAGE_DELETED;
			}

			return this.message.textConverted;
		},

		messageAttach()
		{
			return this.message.params.ATTACH;
		},

		messageReactions()
		{
			return this.message.params.REACTION || {};
		},

		isEdited()
		{
			return this.message.params.IS_EDITED === 'Y';
		},

		isDeleted()
		{
			return this.message.params.IS_DELETED === 'Y';
		},

		chatColor()
		{
			return this.dialog.type !== DialogType.private? this.dialog.color: this.user.color;
		},

		filesData()
		{
			let files = [];

			if (!this.message.params.FILE_ID || this.message.params.FILE_ID.length <= 0)
			{
				return files;
			}

			this.message.params.FILE_ID.forEach(fileId => {
				if (!fileId)
				{
					return false;
				}

				let file = this.$store.getters['files/get'](this.chatId, fileId, true);
				if (!file)
				{
					this.$store.commit('files/set', {data: [
						this.$store.getters['files/getBlank']({id: fileId, chatId: this.chatId})
					]});
					file = this.$store.getters['files/get'](this.chatId, fileId, true);
				}
				if (file)
				{
					files.push(file);
				}
			});

			return files;
		},

		keyboardButtons()
		{
			let result = false;

			if (!this.message.params.KEYBOARD || this.message.params.KEYBOARD === 'N')
			{
				return result;
			}

			return this.message.params.KEYBOARD;
		},
		chatTeaser()
		{
			if (
				typeof this.message.params.CHAT_ID === 'undefined'
				|| typeof this.message.params.CHAT_LAST_DATE === 'undefined'
				|| typeof this.message.params.CHAT_MESSAGE === 'undefined'
			)
			{
				return false;
			}

			return {
				messageCounter: this.message.params.CHAT_MESSAGE,
				messageLastDate: this.message.params.CHAT_LAST_DATE,
				languageId: this.application.common.languageId
			};
		},

		userName()
		{
			if (this.message.params.NAME)
			{
				return this.message.params.NAME;
			}

			if (!this.showAvatar)
			{
				return this.user.name;
			}
			else
			{
				return this.user.firstName ? this.user.firstName : this.user.name;
			}
		},

		...Vuex.mapState({
			application: state => state.application,
		})
	},
	template: `
		<div class="bx-im-message-content-wrap">
			<template v-if="contentType == ContentType.default || contentType == ContentType.audio || contentType == ContentType.progress">
				<div class="bx-im-message-content">
					<span class="bx-im-message-content-box">
						<template v-if="showName && messageType == MessageType.opponent">
							<div :class="['bx-im-message-content-name', referenceContentNameClassName]" :style="{color: user.color}" @click="clickByUserName({user: user, event: $event})">{{userName}}</div>
						</template>
						<div :class="['bx-im-message-content-body', referenceContentBodyClassName]">
							<template v-if="contentType == ContentType.audio">
								<bx-messenger-element-file-audio v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId" @uploadCancel="clickByUploadCancel"/>
							</template>
							<template v-else>
								<bx-messenger-element-file v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId" @uploadCancel="clickByUploadCancel"/>
							</template>
							<div :class="['bx-im-message-content-body-wrap', {
								'bx-im-message-content-body-with-text': messageText.length > 0,
								'bx-im-message-content-body-without-text': messageText.length <= 0,
							}]">
								<template v-if="messageText">
									<span class="bx-im-message-content-text" v-html="messageText"></span>
								</template>
								<template v-for="(config, id) in messageAttach">
									<bx-messenger-element-attach :baseColor="chatColor" :config="config" :key="id"/>
								</template>
								<span class="bx-im-message-content-params">
									<span class="bx-im-message-content-date">{{formattedDate}}</span>
								</span>
							</div>
						</div>
					</span>
					<div v-if="!message.push && enableReactions && message.authorId" class="bx-im-message-content-reaction">
						<bx-reaction :values="messageReactions" :userId="userId" :openList="false" @set="setReaction({message: message, reaction: $event})" @list="openReactionList({message: message, values: $event.values})"/>
					</div>
				</div>
			</template>
			<template v-else-if="contentType == ContentType.richLink">
				<!-- richLink type markup -->
			</template>
			<template v-else-if="contentType == ContentType.image || contentType == ContentType.video">
				<div class="bx-im-message-content bx-im-message-content-fit">
					<span class="bx-im-message-content-box">
						<template v-if="showName && messageType == MessageType.opponent">
							<div :class="['bx-im-message-content-name', referenceContentNameClassName]" :style="{color: user.color}" @click="clickByUserName({user: user, event: $event})">{{!showAvatar? user.name: (user.firstName? user.firstName: user.name)}}</div>
						</template>
						<div :class="['bx-im-message-content-body', referenceContentBodyClassName]">
							<template v-if="contentType == ContentType.image">
								<bx-messenger-element-file-image v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId" @uploadCancel="clickByUploadCancel"/>
							</template>
							<template v-else-if="contentType == ContentType.video">
								<bx-messenger-element-file-video v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId" @uploadCancel="clickByUploadCancel"/>
							</template>
							<div :class="['bx-im-message-content-body-wrap', {
								'bx-im-message-content-body-with-text': messageText.length > 0,
								'bx-im-message-content-body-without-text': messageText.length <= 0,
							}]">
								<template v-if="messageText">
									<span class="bx-im-message-content-text" v-html="messageText"></span>
								</template>
								<span class="bx-im-message-content-params">
									<span class="bx-im-message-content-date">{{formattedDate}}</span>
								</span>
							</div>
						</div>
					</span>
					<div v-if="!message.push && enableReactions && message.authorId" class="bx-im-message-content-reaction">
						<bx-reaction :values="messageReactions" :userId="userId" :openList="false" @set="setReaction({message: message, reaction: $event})" @list="openReactionList({message: message, values: $event.values})"/>
					</div>
				</div>
			</template>
			<template v-if="keyboardButtons">
				<bx-messenger-element-keyboard :buttons="keyboardButtons" :messageId="message.id" :userId="userId" :dialogId="dialogId" @click="clickByKeyboardButton({message: message, event: $event})"/>
			</template>
			<template v-if="chatTeaser">
				<bx-messenger-element-chat-teaser :messageCounter="chatTeaser.messageCounter" :messageLastDate="chatTeaser.messageLastDate" :languageId="chatTeaser.languageId" @click="clickByChatTeaser({message: message, event: $event})"/>
			</template>
		</div>
	`
});