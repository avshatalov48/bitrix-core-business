/**
 * Bitrix Messenger
 * Message Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import './body.css';
import 'im.view.element.media';
import 'im.view.element.attach';
import 'im.view.element.keyboard';
import 'im.view.element.chatteaser';
import 'ui.vue.components.reaction';

import {BitrixVue} from "ui.vue";
import {Vuex} from "ui.vue.vuex";
import {MessagesModel} from 'im.model';
import {DialogType, MessageType, EventType} from "im.const";
import {Utils} from "im.lib.utils";

import {Text} from 'main.core';
import {EventEmitter} from 'main.core.events';

const BX = window.BX;

const ContentType = Object.freeze({
	default: 'default',
	progress: 'progress',
	image: 'image',
	audio: 'audio',
	video: 'video',
	richLink: 'richLink',
});

BitrixVue.component('bx-im-view-message-body',
{
	/**
	 * @emits EventType.dialog.clickOnChatTeaser {message: object, event: MouseEvent}
	 * @emits EventType.dialog.clickOnKeyboardButton {message: object, action: string, params: Object}
	 * @emits EventType.dialog.setMessageReaction {message: object, reaction: object}
	 * @emits EventType.dialog.openMessageReactionList {message: object, values: object}
	 * @emits EventType.dialog.clickOnUserName {user: object, event: MouseEvent}
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
			if (this.showAvatar && Utils.platform.isMobile())
			{
				return false;
			}

			EventEmitter.emit(EventType.dialog.clickOnUserName, event);
		},
		clickByChatTeaser(event)
		{
			EventEmitter.emit(EventType.dialog.clickOnChatTeaser, {message: event.message, event: event.event});
		},
		clickByKeyboardButton(event)
		{
			EventEmitter.emit(EventType.dialog.clickOnKeyboardButton, {message: event.message, ...event.event});
		},
		setReaction(event)
		{
			EventEmitter.emit(EventType.dialog.setMessageReaction, event);
		},
		openReactionList(event)
		{
			EventEmitter.emit(EventType.dialog.openMessageReactionList, event);
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
				this.$Bitrix.Loc.getMessages()
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
			this.dateFormatFunction._getMessage = (phrase) => this.$Bitrix.Loc.getMessage(phrase);

			return this.dateFormatFunction;
		},
		isDesktop()
		{
			return Utils.platform.isBitrixDesktop();
		},
		getDesktopVersion()
		{
			return Utils.platform.getDesktopVersion();
		},
		isMobile()
		{
			return Utils.platform.isBitrixMobile();
		}
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

		formattedDate()
		{
			return this.formatDate(this.message.date);
		},

		messageText()
		{
			if (this.isDeleted)
			{
				return this.$Bitrix.Loc.getMessage('IM_MESSENGER_MESSAGE_DELETED');
			}

			let message = this.message.textConverted? this.message.textConverted: Utils.text.decode(this.message.text);
			let messageParams = this.message.params;

			if (
				typeof messageParams.LINK_ACTIVE !== 'undefined'
				&& messageParams.LINK_ACTIVE.length > 0
				&& !messageParams.LINK_ACTIVE.includes(this.userId)
			)
			{
				message = message.replace(/<a.*?href="([^"]*)".*?>(.*?)<\/a>/gi, '$2');
			}

			return message;
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
		dialog()
		{
			const dialog = this.$store.getters['dialogues/get'](this.dialogId);

			return dialog? dialog: this.$store.getters['dialogues/getBlank']();
		},
		user()
		{
			return this.$store.getters['users/get'](this.message.authorId, true);
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
				return Text.decode(this.message.params.NAME);
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

		userColor()
		{
			if (this.user.extranet)
			{
				return "#CA7B00";
			}

			return this.user.color;
		},

		...Vuex.mapState({
			application: state => state.application,
		})
	},
	// language=Vue
	template: `
		<div class="bx-im-message-content-wrap">
			<template v-if="contentType == ContentType.default || contentType == ContentType.audio || contentType == ContentType.progress || (contentType !== ContentType.image && isDesktop() && getDesktopVersion() < 47)">
				<div class="bx-im-message-content">
					<span class="bx-im-message-content-box">
						<div class="bx-im-message-content-name-wrap">
							<template v-if="showName && user.extranet && messageType == MessageType.opponent">
								<div class="bx-im-message-extranet-icon"></div>
							</template>
							<template v-if="showName && messageType == MessageType.opponent">
								<div :class="['bx-im-message-content-name', referenceContentNameClassName]" :style="{color: userColor}" @click="clickByUserName({user: user, event: $event})">{{userName}}</div>
							</template>
						</div>
						<div :class="['bx-im-message-content-body', referenceContentBodyClassName]">
							<template v-if="(contentType == ContentType.audio) && (!isDesktop() || (isDesktop() && getDesktopVersion() > 43))">
								<bx-im-view-element-file-audio v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId"/>
							</template>
							<template v-else>
								<bx-im-view-element-file v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId"/>
							</template>
							<div :class="['bx-im-message-content-body-wrap', {
								'bx-im-message-content-body-with-text': messageText.length > 0,
								'bx-im-message-content-body-without-text': messageText.length <= 0,
							}]">
								<template v-if="messageText">
									<span class="bx-im-message-content-text" v-html="messageText"></span>
								</template>
								<template v-for="(config, id) in messageAttach">
									<bx-im-view-element-attach :baseColor="chatColor" :config="config" :key="id"/>
								</template>
								<span class="bx-im-message-content-params">
									<span class="bx-im-message-content-date">{{formattedDate}}</span>
								</span>
							</div>
						</div>
					</span>
					<div v-if="!message.push && enableReactions && message.authorId" class="bx-im-message-content-reaction">
						<bx-reaction :id="'message'+message.id" :values="messageReactions" :userId="userId" :openList="false" @set="setReaction({message: message, reaction: $event})" @list="openReactionList({message: message, values: $event.values})"/>
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
								<bx-im-view-element-file-image v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId"/>
							</template>
							<template v-else-if="contentType == ContentType.video">
								<bx-im-view-element-file-video v-for="file in filesData" :messageType="messageType" :file="file" :key="file.templateId"/>
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
						<bx-reaction :id="'message'+message.id" :values="messageReactions" :userId="userId" :openList="false" @set="setReaction({message: message, reaction: $event})" @list="openReactionList({message: message, values: $event.values})"/>
					</div>
				</div>
			</template>
			<template v-if="keyboardButtons">
				<bx-im-view-element-keyboard :buttons="keyboardButtons" :messageId="message.id" :userId="userId" :dialogId="dialogId" @click="clickByKeyboardButton({message: message, event: $event})"/>
			</template>
			<template v-if="chatTeaser">
				<bx-im-view-element-chat-teaser :messageCounter="chatTeaser.messageCounter" :messageLastDate="chatTeaser.messageLastDate" :languageId="chatTeaser.languageId" @click="clickByChatTeaser({message: message, event: $event})"/>
			</template>
		</div>
	`
});