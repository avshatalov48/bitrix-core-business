import { ChatType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { ChannelManager } from 'im.v2.lib.channel';

import 'ui.fonts.opensans';
import './avatar.css';

import type { ImModelUser, ImModelChat } from 'im.v2.model';

export const AvatarSize = Object.freeze({
	XXS: 'XXS',
	XS: 'XS',
	S: 'S',
	M: 'M',
	L: 'L',
	XL: 'XL',
	XXL: 'XXL',
	XXXL: 'XXXL',
});

// @vue/component
export const Avatar = {
	name: 'MessengerAvatar',
	props: {
		dialogId: {
			type: [String, Number],
			default: 0,
		},
		customSource: {
			type: String,
			default: '',
		},
		size: {
			type: String,
			default: AvatarSize.M,
		},
		withAvatarLetters: {
			type: Boolean,
			default: true,
		},
		withSpecialTypes: {
			type: Boolean,
			default: true,
		},
		withSpecialTypeIcon: {
			type: Boolean,
			default: true,
		},
		withTooltip: {
			type: Boolean,
			default: true,
		},
	},
	data(): Object
	{
		return {
			imageLoadError: false,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isBot(): boolean
		{
			if (this.isUser)
			{
				return this.user.bot;
			}

			return false;
		},
		isChannel(): boolean
		{
			return ChannelManager.isChannel(this.dialogId);
		},
		isSpecialType(): boolean
		{
			const commonTypes = [ChatType.user, ChatType.chat, ChatType.open];

			return !commonTypes.includes(this.dialog.type);
		},
		containerTitle(): string
		{
			if (!this.withTooltip)
			{
				return '';
			}

			return this.dialog.name;
		},
		containerClasses(): string[]
		{
			const classes = [`--size-${this.size.toLowerCase()}`];
			if (this.withSpecialTypes && this.isSpecialType)
			{
				classes.push('--special');
			}
			const typeClass = ChatType[this.dialog.type] ? `--${this.dialog.type}` : '--default';
			classes.push(typeClass);

			return classes;
		},
		backgroundColorStyle(): {backgroundColor: string}
		{
			return { backgroundColor: this.dialog.color };
		},
		avatarText(): string
		{
			if (!this.showAvatarLetters || !this.isEnoughSizeForText)
			{
				return '';
			}

			return Utils.text.getFirstLetters(this.dialog.name);
		},
		showAvatarLetters(): boolean
		{
			const SPECIAL_TYPES_WITH_LETTERS = [ChatType.openChannel, ChatType.channel];
			if (SPECIAL_TYPES_WITH_LETTERS.includes(this.dialog.type))
			{
				return true;
			}

			return !this.isSpecialType;
		},
		showSpecialTypeIcon(): boolean
		{
			if (!this.withSpecialTypes || !this.withSpecialTypeIcon || this.isChannel)
			{
				return false;
			}

			return this.isSpecialType;
		},
		isEnoughSizeForText(): boolean
		{
			const avatarSizesWithText = [AvatarSize.M, AvatarSize.L, AvatarSize.XL, AvatarSize.XXL, AvatarSize.XXXL];

			return avatarSizesWithText.includes(this.size.toUpperCase());
		},
		avatarUrl(): string
		{
			return this.customSource.length > 0 ? this.customSource : this.dialog.avatar;
		},
		hasImage(): boolean
		{
			return this.avatarUrl && !this.imageLoadError;
		},
	},
	watch:
	{
		avatarUrl()
		{
			this.imageLoadError = false;
		},
	},
	methods:
	{
		onImageLoadError()
		{
			this.imageLoadError = true;
		},
	},
	template: `
		<div :title="containerTitle" :class="containerClasses" class="bx-im-avatar__scope bx-im-avatar__container">
			<!-- Avatar -->
			<template v-if="hasImage">
				<img :src="avatarUrl" :alt="dialog.name" class="bx-im-avatar__content --image" @error="onImageLoadError" draggable="false"/>
				<div v-if="showSpecialTypeIcon" :style="backgroundColorStyle" class="bx-im-avatar__special-type_icon"></div>
			</template>
			<div v-else-if="withAvatarLetters && avatarText" :style="backgroundColorStyle" class="bx-im-avatar__content --text">
				{{ avatarText }}
			</div>
			<div v-else :style="backgroundColorStyle" class="bx-im-avatar__content bx-im-avatar__icon"></div>
		</div>
	`,
};
