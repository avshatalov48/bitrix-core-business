import { Core } from 'im.v2.application.core';
import { openHelpdeskArticle } from 'im.v2.lib.helpdesk';

import { CopilotAiModelService } from './classes/copilot-ai-model-service';
import { AiModelItem } from './ai-model-item';

import type { ImModelCopilotAIModel, ImModelUser } from 'im.v2.model';

const SETTINGS_PAGE = '/settings/configs/?page=ai';
const MARKET_PAGE = '/market/collection/ai_provider_partner_crm/';

// @vue/component
export const AIModelPopupContent = {
	name: 'AIModelPopupContent',
	components: { AiModelItem },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		isAdmin(): boolean
		{
			const user: ImModelUser = Core.getStore().getters['users/get'](Core.getUserId());

			return user.isAdmin;
		},
		selectedAIModelCode(): string
		{
			return this.$store.getters['copilot/chats/getAIModel'](this.dialogId).code;
		},
		aiModelsItems(): ImModelCopilotAIModel[]
		{
			return Core.getStore().getters['copilot/getAIModels'];
		},
	},
	methods:
	{
		openSettings()
		{
			BX.SidePanel.Instance.open(`${window.location.origin}${SETTINGS_PAGE}`);
		},
		openMarket()
		{
			BX.SidePanel.Instance.open(`${window.location.origin}${MARKET_PAGE}`);
		},
		openHelpCenter()
		{
			const ARTICLE_CODE = '20267044';
			openHelpdeskArticle(ARTICLE_CODE);
		},
		isSelectedAIModel(aiModelCode: string): boolean
		{
			return this.selectedAIModelCode === aiModelCode;
		},
		async selectAIModel(aiModelCode: string): void
		{
			const service = new CopilotAiModelService();
			void service.updateAIModel({ dialogId: this.dialogId, aiModelCode });
			this.$emit('close');
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-ai-model-popup-content__container" ref="ai-model-content">
			<AiModelItem
				v-for="model in aiModelsItems"
				:key="model.name"
				:text="model.name"
				:selected="isSelectedAIModel(model.code)"
				@click="selectAIModel(model.code)"
			/>
			<template v-if="isAdmin">
				<div class="bx-im-ai-model-popup-content__separator"></div>
				<AiModelItem
					:text="loc('IM_SIDEBAR_AI_MODEL_POPUP_MARKET')"
					@click="openMarket"
				/>
				<AiModelItem
					:text="loc('IM_SIDEBAR_AI_MODEL_POPUP_SETTINGS')"
					@click="openSettings"
				/>
			</template>
			<div class="bx-im-ai-model-popup-content__separator"></div>
			<AiModelItem
				:text="loc('IM_SIDEBAR_AI_MODEL_POPUP_HELP')"
				icon="info"
				class="bx-im-ai-model-popup-content__item_info"
				@click="openHelpCenter"
			/>
		</div>
	`,
};
