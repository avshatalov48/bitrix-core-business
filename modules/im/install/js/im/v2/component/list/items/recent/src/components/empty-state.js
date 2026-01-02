import { ChatButton, ButtonSize, ButtonColor } from 'im.v2.component.elements.button';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { InviteManager } from 'im.v2.lib.invite';
import { RecentEmptyState } from 'im.v2.component.list.items.elements.empty-state';

// @vue/component
export const EmptyState = {
	name: 'EmptyState',
	components: { ChatButton, RecentEmptyState },
	computed: {
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		canInviteUsers(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.intranetInviteAvailable);
		},
	},
	methods: {
		onInviteUsersClick(): void
		{
			InviteManager.openInviteSlider();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<RecentEmptyState
			:title="loc('IM_LIST_RECENT_EMPTY_STATE_TITLE')"
			:subtitle="loc('IM_LIST_RECENT_EMPTY_STATE_SUBTITLE')"
		>
			<ChatButton
				v-if="canInviteUsers"
				:size="ButtonSize.L"
				:isRounded="true"
				:text="loc('IM_LIST_RECENT_EMPTY_STATE_INVITE_USERS')"
				@click="onInviteUsersClick"
			/>
		</RecentEmptyState>
	`,
};
