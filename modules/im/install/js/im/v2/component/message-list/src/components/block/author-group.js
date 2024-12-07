import { MessageAvatar, AvatarSize } from 'im.v2.component.elements';

import type { JsonObject } from 'main.core';
import type { AuthorGroupItem } from '../../classes/collection-manager/collection-manager';

// @vue/component
export const AuthorGroup = {
	name: 'AuthorGroup',
	components: { MessageAvatar },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		contextDialogId: {
			type: String,
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
		firstMessageIdInAuthorGroup(): number
		{
			// this is potentially dangerous.
			// for now, we always have the same avatar in one authorGroup
			// in future it can be different: several support answers (with different avatars) in one authorGroup
			return this.authorGroup.messages[0].id;
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
				<MessageAvatar
					:messageId="firstMessageIdInAuthorGroup"
					:authorId="authorGroup.avatar.avatarId"
					:size="AvatarSize.L"
					@click="onAvatarClick"
				/>
			</div>
			<div class="bx-im-message-list__content">
				<template v-for="(message, index) in authorGroup.messages">
					<slot name="message" :message="message" :index="index"></slot>
				</template>
			</div>
		</div>
	`,
};
