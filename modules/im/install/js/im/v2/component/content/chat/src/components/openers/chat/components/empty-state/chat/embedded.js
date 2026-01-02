import { Analytics } from 'im.v2.lib.analytics';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { InviteManager } from 'im.v2.lib.invite';

import './css/embedded.css';

// @vue/component
export const EmbeddedChatEmptyState = {
	name: 'EmbeddedChatEmptyState',
	computed: {
		canInviteUsers(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.intranetInviteAvailable);
		},
	},
	methods: {
		onInviteActionClick(): void
		{
			const analyticsContext = Analytics.getInstance().sliderInvite.getEmptyStateContext();
			InviteManager.openInviteSlider(analyticsContext);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-embedded-chat-start__container">
			<div class="bx-im-embedded-chat-start__image"></div>
			<div class="bx-im-embedded-chat-start__title">
				{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_SUBTITLE') }}
			</div>
			<template v-if="canInviteUsers">
				<div class="bx-im-embedded-chat-start__separator_text">
					{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_SEPARATOR_TEXT') }}
				</div>
				<div @click="onInviteActionClick" class="bx-im-embedded-chat-start__invite">
					{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_INVITE_ACTION') }}
				</div>
			</template>
		</div>
	`,
};
