import { Avatar, AvatarSize } from 'im.v2.component.elements';

import type { JsonObject } from 'main.core';

import type { AuthorGroupItem } from '../../classes/collection-manager';

// @vue/component
export const AuthorGroup = {
	name: 'AuthorGroup',
	components: { Avatar },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	emits: ['avatarClick'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		authorGroup(): AuthorGroupItem
		{
			return this.item;
		},
	},
	methods:
	{
		onAvatarClick(event: PointerEvent): void
		{
			this.$emit('avatarClick', {
				dialogId: this.authorGroup.avatar.avatarId,
				$event: event,
			});
		},
	},
	template: `
		<div class="bx-im-message-list-author-group__container" :class="'--' + authorGroup.messageType">
			<div v-if="authorGroup.avatar.isNeeded" class="bx-im-message-list-author-group__avatar">
				<Avatar
					:dialogId="authorGroup.avatar.avatarId"
					:size="AvatarSize.L"
					@click="onAvatarClick"
				/>
			</div>
			<div class="bx-im-message-list__content">
				<template v-for="(message, index) in authorGroup.items">
					<slot name="message" :message="message" :index="index"></slot>
				</template>
			</div>
		</div>
	`,
};
