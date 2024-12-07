import { ParamsByLinkType } from './const/chat-type-params';

import './css/entity-link.css';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

// @vue/component
export const EntityLink = {
	name: 'EntityLink',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		entityType(): string
		{
			return this.dialog.entityLink.type;
		},
		entityUrl(): string
		{
			return this.dialog.entityLink.url;
		},
		containerClassName(): string
		{
			return ParamsByLinkType[this.entityType]?.className ?? '';
		},
		linkText(): string
		{
			return ParamsByLinkType[this.entityType]?.loc ?? 'Open entity';
		},
	},
	template: `
		<a :href="entityUrl" class="bx-im-chat-header-entity-link__container" :class="containerClassName" target="_blank">
			<div class="bx-im-chat-header-entity-link__icon"></div>
			<div class="bx-im-chat-header-entity-link__text">{{ linkText }}</div>
			<div class="bx-im-chat-header-entity-link__arrow"></div>
		</a>
	`,
};
