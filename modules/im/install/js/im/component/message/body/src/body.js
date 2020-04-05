/**
 * Bitrix Messenger
 * Message Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import './body.css';
import {Vue} from "ui.vue";
import {MessagesModel, UsersModel} from 'im.model';
import 'im.component.element.file';

const BX = window.BX;

const MessageType = Object.freeze({
	self: 'self',
	opponent: 'opponent',
	system: 'system',
});

const ContentType = Object.freeze({
	default: 'default',
	image: 'image',
	video: 'video',
	richLink: 'richLink',
});

Vue.component('bx-messenger-message-body',
{
	/**
	 * @emits 'clickByUserName' {user: object, event: MouseEvent}
	 */
	props:
	{
		userId: { default: 0 },
		dialogId: { default: 0 },
		chatId: { default: 0 },
		messageType: { default: MessageType.self },
		message: {
			type: Object,
			default: MessagesModel.create().getElementStore
		},
		user: {
			type: Object,
			default: UsersModel.create().getElementStore
		},
		files: {
			type: Object,
			default: {}
		},
		enableEmotions: { default: true },
		showName: { default: true },
		showAvatar: { default: true },
		referenceContentBodyClassName: { default: ''},
	},
	created()
	{
		this.dateFormatFunction = null;
		this.cacheFormatDate = {};
	},
	methods:
	{
		clickByUserName(user, event)
		{
			this.$emit('clickByUserName', {user, event})
		},
		formatDate(date)
		{
			const id = date.toJSON().slice(0, 10);

			if (this.cacheFormatDate[id])
			{
				return this.cacheFormatDate[id];
			}

			let dateFormat = BX.Messenger.Utils.getDateFormatType(
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

				for (let file of this.filesData)
				{
					if (file.type == 'image')
					{
						if (onlyVideo)
						{
							onlyVideo = false;
							break;
						}
						onlyImage = true;
					}
					else if (false && file.type == 'video')
					{
						if (onlyImage)
						{
							onlyImage = false;
							break;
						}
						onlyVideo = true;
					}
					else
					{
						onlyImage = false;
						onlyVideo = false;
						break;
					}
				}

				if (onlyImage)
				{
					return ContentType.image;
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

		isEdited()
		{
			return this.message.params.IS_EDITED == 'Y';
		},

		isDeleted()
		{
			return this.message.params.IS_DELETED == 'Y';
		},

		filesData()
		{
			let files = [];

			if (!this.message.params.FILE_ID || this.message.params.FILE_ID.length <= 0)
			{
				return files;
			}

			this.message.params.FILE_ID.forEach(fileId => {
				if (this.files[fileId])
				{
					files.push(this.files[fileId]);
				}
			});

			return files;
		}
	},
	template: `
		<div class="bx-im-message-content-wrap">
			<template v-if="contentType == ContentType.default">
				<div class="bx-im-message-content">
					<span class="bx-im-message-content-box">
						<template v-if="showName && messageType == MessageType.opponent">
							<div class="bx-im-message-content-name" :style="{color: user.color}" @click="clickByUserName(user, $event)">{{!showAvatar? user.name: (user.firstName? user.firstName: user.name)}}</div>
						</template>
						<div :class="['bx-im-message-content-body', referenceContentBodyClassName]">
							<template v-for="file in filesData">
								<bx-messenger-element-file :file="file"/>
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
				</div>
				<!-- keyboard -->
			</template>
			<template v-else-if="contentType == ContentType.richLink">
				<!-- richLink type markup -->
			</template>
			<template v-else-if="contentType == ContentType.image || contentType == ContentType.video">
				<div class="bx-im-message-content bx-im-message-content-fit">
					<span class="bx-im-message-content-box">
						<template v-if="showName && messageType == MessageType.opponent">
							<div class="bx-im-message-content-name" :style="{color: user.color}" @click="clickByUserName(user, $event)">{{!showAvatar? user.name: (user.firstName? user.firstName: user.name)}}</div>
						</template>
						<div :class="['bx-im-message-content-body', referenceContentBodyClassName]">
							<template v-if="contentType == ContentType.image">
								<template v-for="file in filesData">
									<bx-messenger-element-file-image :file="file"/>
								</template>
							</template>
							<template v-else-if="contentType == ContentType.video">
								<template v-for="file in filesData">
									<bx-messenger-element-file-video :file="file"/>
								</template>
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
				</div>
				<!-- keyboard -->
			</template>
		</div>
	`
});

/*
	<span class="bx-messenger-content-item-like bx-messenger-content-like-digit-off">
		<span>&nbsp;</span>
		<span class="bx-messenger-content-like-digit"></span>
		<span data-messageid="28571160" class="bx-messenger-content-like-button">{{localize.IM_MESSENGER_MESSAGE_LIKE}}</span>
	</span>
 */