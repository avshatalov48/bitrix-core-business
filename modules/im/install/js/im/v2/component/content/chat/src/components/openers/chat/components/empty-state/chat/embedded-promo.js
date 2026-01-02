import { Loc } from 'main.core';

import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { PromoId } from 'im.v2.const';
import { PromoManager } from 'im.v2.lib.promo';

import 'im.v2.lib.invite';

import './css/embedded-promo.css';

const SHOWCASE_TOOLS_PHRASES = [
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CHATS'),
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CALLS'),
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CHANNELS'),
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_DOCS'),
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_TASKS'),
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CALENDAR'),
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_FILES'),
	Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_BOARDS'),
];

// @vue/component
export const EmbeddedChatPromoEmptyState = {
	name: 'EmbeddedChatPromoEmptyState',
	computed: {
		SHOWCASE_TOOLS_PHRASES: () => SHOWCASE_TOOLS_PHRASES,
		canInviteUsers(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.intranetInviteAvailable);
		},
		preparedTitle(): string
		{
			return Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TITLE', {
				'[color]': '<span class="--brand-accent-with-icon">',
				'[/color]': '</span>',
			});
		},
		preparedInviteText(): string
		{
			const openInviteSliderHandler = 'BX.Messenger.v2.Lib.InviteManager.openInviteSlider()';

			return Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_INVITE_MSGVER_2', {
				'[action]': `<span class="bx-im-embedded-promo-start__invite-action" onclick="${openInviteSliderHandler}">`,
				'[/action]': '</span>',
			});
		},
	},
	mounted(): void
	{
		void PromoManager.getInstance().markAsWatched(PromoId.embeddedChatEmptyState);
	},
	methods: {
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-embedded-promo-start__container">
			<div class="bx-im-embedded-promo-start__title" v-html="preparedTitle"></div>
			<div class="bx-im-embedded-promo-start__tools">
				<div
					v-for="tool in SHOWCASE_TOOLS_PHRASES"
					:key="tool"
					class="bx-im-embedded-promo-start__tools_item"
				>{{ tool }}</div>
			</div>
			<div class="bx-im-embedded-promo-start__content">
				<div class="bx-im-embedded-promo-start__features">
					<div class="bx-im-embedded-promo-start__features_content">
						<div class="bx-im-embedded-promo-start__feature_item">
							<div class="bx-im-embedded-promo-start__feature_icon --people"></div>
							<div class="bx-im-embedded-promo-start__feature_text">{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_EASE') }}</div>
						</div>
						<div class="bx-im-embedded-promo-start__feature_item">
							<div class="bx-im-embedded-promo-start__feature_icon --shield"></div>
							<div class="bx-im-embedded-promo-start__feature_text">{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_SAFETY') }}</div>
						</div>
						<div class="bx-im-embedded-promo-start__feature_item">
							<div class="bx-im-embedded-promo-start__feature_icon --ai"></div>
							<div class="bx-im-embedded-promo-start__feature_text">{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_AI') }}</div>
						</div>
					</div>
					<div v-if="canInviteUsers" v-html="preparedInviteText" class="bx-im-embedded-promo-start__invite_item"></div>
				</div>
				<div class="bx-im-embedded-promo-start__image"></div>
			</div>
		</div>
	`,
};
