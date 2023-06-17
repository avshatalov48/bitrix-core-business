import {Core} from 'im.v2.application.core';
import {DialogType, UserStatus as UserStatusType} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

import {UserStatus, UserStatusSize} from '../user-status/user-status';

import 'ui.fonts.opensans';
import './avatar.css';

import type {ImModelUser, ImModelDialog} from 'im.v2.model';

export const AvatarSize = Object.freeze({
	XS: 'XS',
	S: 'S',
	M: 'M',
	L: 'L',
	XL: 'XL',
	XXL: 'XXL',
	XXXL: 'XXXL'
});

// @vue/component
export const Avatar = {
	name: 'MessengerAvatar',
	components: {UserStatus},
	props: {
		dialogId: {
			type: [String, Number],
			default: 0
		},
		size: {
			type: String,
			default: AvatarSize.M
		},
		withAvatarLetters: {
			type: Boolean,
			default: true
		},
		withStatus: {
			type: Boolean,
			default: true
		},
		withSpecialTypes: {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			imageLoadError: false,
		};
	},
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
		},
		isBot(): boolean
		{
			if (this.isUser)
			{
				return this.user.bot;
			}

			return false;
		},
		isSpecialType(): boolean
		{
			const commonTypes = [DialogType.user, DialogType.chat, DialogType.open];

			return !commonTypes.includes(this.dialog.type);
		},
		containerClasses(): string[]
		{
			const classes = [`--size-${this.size.toLowerCase()}`];
			if (this.withSpecialTypes && this.isSpecialType)
			{
				classes.push('--special');
			}
			const typeClass = DialogType[this.dialog.type] ? `--${this.dialog.type}` : '--default';
			classes.push(typeClass);

			return classes;
		},
		backgroundColorStyle(): {backgroundColor: string}
		{
			return {backgroundColor: this.dialog.color};
		},
		avatarText(): string
		{
			if (this.isSpecialType || !this.isEnoughSizeForText)
			{
				return '';
			}

			return Utils.text.getFirstLetters(this.dialog.name);
		},
		userStatusIcon(): string
		{
			if (!this.isUser || this.isBot || this.user.id === Core.getUserId() || !this.isEnoughSizeForStatus)
			{
				return '';
			}

			const status = this.$store.getters['users/getStatus'](this.dialogId);
			if (status && status !== UserStatusType.online)
			{
				return status;
			}

			return '';
		},
		userStatusSize(): string
		{
			// avatar size: status size
			const sizesMap = {
				[AvatarSize.M]: UserStatusSize.S,
				[AvatarSize.L]: UserStatusSize.M,
				[AvatarSize.XL]: UserStatusSize.L,
				[AvatarSize.XXL]: UserStatusSize.XL,
				[AvatarSize.XXXL]: UserStatusSize.XXL
			};

			return sizesMap[this.size];
		},
		isEnoughSizeForText(): boolean
		{
			const avatarSizesWithText = [AvatarSize.L, AvatarSize.XL, AvatarSize.XXL, AvatarSize.XXXL];

			return avatarSizesWithText.includes(this.size.toUpperCase());
		},
		isEnoughSizeForStatus(): boolean
		{
			const avatarSizesWithText = [AvatarSize.M, AvatarSize.L, AvatarSize.XL, AvatarSize.XXL, AvatarSize.XXXL];

			return avatarSizesWithText.includes(this.size.toUpperCase());
		},
		avatarUrl(): string
		{
			return this.dialog.avatar;
		},
		hasImage(): boolean
		{
			return this.avatarUrl && !this.imageLoadError;
		}
	},
	watch:
	{
		avatarUrl()
		{
			this.imageLoadError = false;
		}
	},
	methods:
	{
		onImageLoadError()
		{
			this.imageLoadError = true;
		}
	},
	template: `
		<div :title="dialog.name" :class="containerClasses" class="bx-im-avatar__scope bx-im-avatar__container">
			<!-- Avatar -->
			<template v-if="hasImage">
				<img :src="avatarUrl" :alt="dialog.name" class="bx-im-avatar__content --image" @error="onImageLoadError"/>
				<div v-if="withSpecialTypes && isSpecialType" :style="backgroundColorStyle" class="bx-im-avatar__special-type_icon"></div>
			</template>
			<div v-else-if="withAvatarLetters && avatarText" :style="backgroundColorStyle" class="bx-im-avatar__content --text">
				{{ avatarText }}
			</div>
			<div v-else :style="backgroundColorStyle" class="bx-im-avatar__content bx-im-avatar__icon"></div>
			<!-- Status icons -->
			<div v-if="withStatus && userStatusIcon" class="bx-im-avatar__status-icon">
				<UserStatus :status="userStatusIcon" :size="userStatusSize" />
			</div>
		</div>
	`
};