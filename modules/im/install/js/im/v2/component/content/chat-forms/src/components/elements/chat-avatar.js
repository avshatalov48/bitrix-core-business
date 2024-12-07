import { Type } from 'main.core';

import { ChatService } from 'im.v2.provider.service';

import './css/chat-avatar.css';

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
		existingAvatarUrl: {
			type: String,
			default: '',
		},
		chatTitle: {
			type: String,
			required: true,
		},
		squared: {
			type: Boolean,
			required: false,
			default: false,
		},
	},
	emits: ['avatarChange'],
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
		avatarToShow(): string | null
		{
			return this.preparedAvatar || this.existingAvatarUrl;
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
		<div 
			class="bx-im-chat-forms-chat-avatar__container" 
			:class="{'--squared': squared}" 
			@click="onAvatarChangeClick"
		>
			<img 
				v-if="avatarToShow" 
				:src="avatarToShow" 
				:alt="chatTitle" 
				class="bx-im-chat-forms-chat-avatar__image" 
			/>
		</div>
		<input 
			type="file" 
			@change="onAvatarSelect" 
			accept="image/*" 
			class="bx-im-chat-forms-chat-avatar__input" 
			ref="avatarInput"
		>
	`,
};
