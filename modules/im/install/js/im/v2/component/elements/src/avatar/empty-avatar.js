import { Type } from 'main.core';

import { Color } from 'im.v2.const';

import { AvatarSize } from './components/base/avatar';
import { AvatarType, BaseUiAvatar } from './components/base/base-ui-avatar';

import './css/empty-avatar.css';

import type { JsonObject } from 'main.core';

export const EmptyAvatarType = Object.freeze({
	default: 'default',
	squared: 'squared',
	collab: 'collab',
});

const COLLAB_EMPTY_AVATAR_URL = '/bitrix/js/im/v2/component/elements/src/avatar/components/base/css/images/camera.png';

// @vue/component
export const EmptyAvatar = {
	name: 'EmptyAvatar',
	components: { BaseUiAvatar },
	props: {
		url: {
			type: String,
			default: '',
		},
		title: {
			type: String,
			default: '',
		},
		type: {
			type: String,
			default: EmptyAvatarType.default,
		},
		size: {
			type: String,
			default: AvatarSize.M,
		},
	},
	data(): JsonObject
	{
		return {
			imageLoadError: false,
		};
	},
	computed: {
		AvatarSize: () => AvatarSize,
		AvatarType: () => AvatarType,
		Color: () => Color,
		isSquared(): boolean
		{
			return this.type === EmptyAvatarType.squared;
		},
		isCollabType(): boolean
		{
			return this.type === EmptyAvatarType.collab;
		},
		collabEmptyAvatarUrl(): string
		{
			if (!Type.isStringFilled(this.url))
			{
				return COLLAB_EMPTY_AVATAR_URL;
			}

			return this.url;
		},
		containerClasses(): string[]
		{
			const classes = [`--size-${this.size.toLowerCase()}`];
			if (this.isSquared)
			{
				classes.push('--squared');
			}

			return classes;
		},
	},
	template: `
		<BaseUiAvatar
			:type="AvatarType.collab"
			v-if="isCollabType" 
			:url="collabEmptyAvatarUrl" 
			:size="size"
			:title="title"
			:backgroundColor="Color.collab10"
		/>
		<div v-else class="bx-im-empty-avatar__container" :class="containerClasses">
			<div v-if="!url" class="bx-im-empty-avatar__avatar --default"></div>
			<img v-else class="bx-im-empty-avatar__avatar --image" :src="url" :alt="title"/>
		</div>
	`,
};
