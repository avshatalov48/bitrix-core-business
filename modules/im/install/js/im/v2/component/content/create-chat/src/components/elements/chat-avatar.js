import { Type } from 'main.core';

import { ChatService } from 'im.v2.provider.service';

import type { JsonObject } from 'main.core';

// @vue/component
export const ChatAvatar = {
	name: 'ChatAvatar',
	props:
	{
		avatarFile: {
			required: true,
			validator(value): boolean
			{
				return (value instanceof File) || Type.isNull(value);
			},
		},
		chatTitle: {
			type: String,
			required: true,
		},
	},
	emits: ['avatarChange'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		preparedAvatar(): string | null
		{
			if (!this.avatarFile)
			{
				return null;
			}

			return URL.createObjectURL(this.avatarFile);
		},
	},
	methods:
	{
		onAvatarChangeClick()
		{
			this.$refs.avatarInput.click();
		},
		async onAvatarSelect(event: Event)
		{
			const input: HTMLInputElement = event.target;
			const file: File = input.files[0];
			if (!file)
			{
				return;
			}

			const preparedAvatar = await this.getChatService().prepareAvatar(file);
			if (!preparedAvatar)
			{
				return;
			}

			this.$emit('avatarChange', preparedAvatar);
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
	},
	template: `
		<div class="bx-im-content-create-chat__avatar_container" @click="onAvatarChangeClick">
			<img v-if="preparedAvatar" class="bx-im-content-create-chat__avatar_image" :src="preparedAvatar" :alt="chatTitle" />
		</div>
		<input type="file" @change="onAvatarSelect" accept="image/*" class="bx-im-content-create-chat__avatar_input" ref="avatarInput">
	`,
};
