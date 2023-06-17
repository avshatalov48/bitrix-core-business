import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Reactions} from 'ui.vue3.components.reactions';

import {Core} from 'im.v2.application.core';
import {Utils} from 'im.v2.lib.utils';
import {Parser} from 'im.v2.lib.parser';
import {DialogType, EventType} from 'im.v2.const';
import {Attach, Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import {ReactionSelector, ReactionList} from 'im.v2.component.message.reaction';
import {DateFormatter, DateCode} from 'im.v2.lib.date-formatter';

import {Media} from './components/media';
import {OwnMessageStatus} from './components/own-message-status';
import {DeletedMessage} from './components/deleted-message';

import './css/base-message.css';

import type {ImModelMessage, ImModelUser, ImModelDialog} from 'im.v2.model';

// @vue/component
export const BaseMessage = {
	name: 'BaseMessage',
	components: {Attach, Avatar, ChatTitle, Reactions, Media, OwnMessageStatus, DeletedMessage, ReactionSelector, ReactionList},
	props: {
		item: {
			type: Object,
			required: true
		},
		withAvatar: {
			type: Boolean,
			required: true
		},
		withTitle: {
			type: Boolean,
			default: true
		},
		menuIsActiveForId: {
			type: Number,
			default: 0
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	emits: ['contextMenuClick', 'quoteMessage'],
	data()
	{
		return {};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		message(): ImModelMessage
		{
			return this.item;
		},
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.message.authorId, true);
		},
		dialogColor(): string
		{
			return this.dialog.type !== DialogType.private? this.dialog.color: this.user.color;
		},
		authorDialogId(): string
		{
			if (this.message.authorId)
			{
				return this.message.authorId.toString();
			}

			return this.dialogId;
		},
		isSystemMessage(): boolean
		{
			return this.message.authorId === 0;
		},
		isSelfMessage(): boolean
		{
			return this.message.authorId === Core.getUserId();
		},
		isOpponentMessage(): boolean
		{
			return !this.isSystemMessage && !this.isSelfMessage;
		},
		showTitle(): boolean
		{
			return this.withTitle && !this.isSystemMessage && !this.isSelfMessage;
		},
		canSetReactions(): boolean
		{
			return Type.isNumber(this.message.id);
		},
		containerClasses(): Object
		{
			return {
				'--system': this.isSystemMessage,
				'--self': this.isSelfMessage,
				'--opponent': this.isOpponentMessage,
				'--with-avatar': this.withAvatar
			};
		},
		formattedText(): string
		{
			return Parser.decodeMessage(this.message);
		},
		formattedDate(): string
		{
			return DateFormatter.formatByCode(this.message.date, DateCode.shortTimeFormat);
		},
		menuTitle(): string
		{
			return this.loc(
				'IM_MESSENGER_MESSAGE_MENU_TITLE',
				{'#SHORTCUT#': Utils.platform.isMac()? 'CMD':'CTRL'}
			);
		}
	},
	methods: {
		setReaction(message, reaction)
		{
			console.warn('setReaction', message, reaction);
		},
		openReactionList(message, values)
		{
			console.warn('openReactionList', message, values);
		},
		onMenuClick(event: PointerEvent)
		{
			if (Utils.key.isCmdOrCtrl(event))
			{
				this.$emit('quoteMessage', {message: this.message});
				return;
			}
			this.$emit('contextMenuClick', {message: this.message, $event: event});
		},
		onContainerClick(event: PointerEvent)
		{
			Parser.executeClickEvent(event);
		},
		onAuthorNameClick()
		{
			const authorId = Number.parseInt(this.authorDialogId, 10);
			if (authorId === Core.getUserId())
			{
				return;
			}

			EventEmitter.emit(EventType.textarea.insertMention, {
				mentionText: this.user.name,
				mentionReplacement: Utils.user.getMentionBbCode(this.user.id, this.user.name)
			});
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		}
	},
	template: `
		<div :class="containerClasses" :data-id="message.id" class="bx-im-message-base__scope bx-im-message-base__container" @click="onContainerClick">
			<div class="bx-im-message-base__body">
				<div @click="onAuthorNameClick" v-if="showTitle" class="bx-im-message-base__name">
					<ChatTitle :dialogId="authorDialogId" :onlyFirstName="true" :showItsYou="false" :withColor="true" :withLeftIcon="false" />
				</div>
				<Media :item="message" />
				<DeletedMessage v-if="message.isDeleted" />
				<div v-else class="bx-im-message-base__text" v-html="formattedText"></div>
				<div v-for="config in message.attach" :key="config.ID" class="bx-im-message-base__attach-wrap">
					<Attach :baseColor="dialogColor" :config="config"/>
				</div>
				
				<div class="bx-im-message-base__bottom-container">
					<ReactionList v-if="canSetReactions" :messageId="message.id" />
					<div class="bx-im-message-base__bottom-container_right">
						<div v-if="message.isEdited && !message.isDeleted" class="bx-im-message-base__edit-mark">
							{{ loc('IM_MESSENGER_MESSAGE_EDITED') }}
						</div>
						<div class="bx-im-message-base__date">{{ formattedDate }}</div>
						<OwnMessageStatus v-if="isSelfMessage" :item="message" />	
					</div>
				</div>
				<div class="bx-im-message-base__reactions-container">
					<ReactionSelector v-if="canSetReactions" :messageId="message.id" />
				</div>
			</div>
			<div class="bx-im-message-base__actions">
				<div :title="menuTitle" @click="onMenuClick" :class="{'--active': menuIsActiveForId === message.id}" class="bx-im-message-base__menu"></div>
			</div>
		</div>
	`
};