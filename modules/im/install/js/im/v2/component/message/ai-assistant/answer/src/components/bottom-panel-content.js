import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';
import { RichLoc } from 'ui.vue3.components.rich-loc';

import { Utils } from 'im.v2.lib.utils';
import { openHelpdeskArticle } from 'im.v2.lib.helpdesk';
import { Notifier } from 'im.v2.lib.notifier';
import { Color } from 'im.v2.const';
import { MessageStatus } from 'im.v2.component.message.elements';

import type { ImModelMessage } from 'im.v2.model';

const ICON_SIZE = 18;

// @vue/component
export const BottomPanelContent = {
	name: 'BottomPanelContent',
	components: { BIcon, RichLoc, MessageStatus },
	props: {
		item: {
			type: Object,
			required: true,
		},
	},
	computed: {
		Color: () => Color,
		ICON_SIZE: () => ICON_SIZE,
		OutlineIcons: () => OutlineIcons,
		message(): ImModelMessage
		{
			return this.item;
		},
	},
	methods: {
		async onCopyClick()
		{
			await Utils.text.copyToClipboard(this.message.text);
			Notifier.onCopyTextComplete();
		},
		onWarningDetailsClick()
		{
			const ARTICLE_CODE = '25754438';
			openHelpdeskArticle(ARTICLE_CODE);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-message-ai-assistant-answer__bottom-panel">
			<BIcon
				:name="OutlineIcons.COPY"
				:color="Color.accentSoftBorderBlue"
				:hoverable="true"
				:title="loc('IM_MESSAGE_AI_ASSISTANT_ANSWER_ACTION_COPY')"
				:size="ICON_SIZE"
				@click="onCopyClick"
				class="bx-im-message-ai-assistant-answer__copy_icon"
			/>
			<span class="bx-im-message-ai-assistant-answer__warning">
				<RichLoc
					:text="loc('IM_MESSAGE_AI_ASSISTANT_ANSWER_WARNING')"
					placeholder="[url]"
				>
					<template #url="{ text }">
						<span class="bx-im-message-ai-assistant-answer__warning_link" @click="onWarningDetailsClick">
							{{ text }}
						</span>
					</template>
				</RichLoc>
			</span>
			<div class="bx-im-message-ai-assistant-answer__status-container">
				<MessageStatus :item="message"/>
			</div>
		</div>
	`,
};
