import { ChatType } from 'im.v2.const';

import { ParamsByChatType, CrmLinkTextByEntity } from './const/chat-type-params';

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
		entityId(): string
		{
			return this.dialog.entityLink.id;
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
			return ParamsByChatType[this.dialog.type]?.className ?? '';
		},
		linkText(): string
		{
			if (this.dialog.type === ChatType.crm)
			{
				return this.getCrmLinkText();
			}

			return ParamsByChatType[this.dialog.type]?.loc ?? 'Open entity';
		},
	},
	methods:
	{
		getCrmLinkText(): string
		{
			const [entityType] = this.entityId.split('|');
			if (!entityType)
			{
				return '';
			}

			return CrmLinkTextByEntity[entityType];
		},
	},
	template: `
		<a :href="entityUrl" class="bx-im-chat-header-entity-link__container" :class="containerClassName">
			<div class="bx-im-chat-header-entity-link__icon"></div>
			<div class="bx-im-chat-header-entity-link__text">{{ linkText }}</div>
			<div class="bx-im-chat-header-entity-link__arrow"></div>
		</a>
	`,
};
