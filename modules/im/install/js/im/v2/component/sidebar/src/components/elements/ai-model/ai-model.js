import { AIModelPopup } from './ai-model-popup';

import './css/ai-model.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const AIModel = {
	name: 'AIModel',
	components: { AIModelPopup },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showAIModelPopup: false,
		};
	},
	computed:
	{
		currentAIModelName(): string
		{
			return this.$store.getters['copilot/chats/getAIModel'](this.dialogId).name;
		},
	},
	methods:
	{
		toggleAIModelPopup(): void
		{
			this.showAIModelPopup = !this.showAIModelPopup;
		},
		closeAIModelPopup(): void
		{
			this.showAIModelPopup = false;
		},
	},
	template: `
		<div class="bx-im-sidebar-ai-model__container" @click="toggleAIModelPopup" ref="change-ai-model">
			<div class="--line-clamp-2">
				{{ currentAIModelName }}
			</div>
			<div class="bx-im-sidebar-ai-model__arrow-icon"></div>
		</div>
		<AIModelPopup
			v-if="showAIModelPopup"
			:dialogId="dialogId"
			:bindElement="$refs['change-ai-model']"
			@close="closeAIModelPopup"
		/>
	`,
};
