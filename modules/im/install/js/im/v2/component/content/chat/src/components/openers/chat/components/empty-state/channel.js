import { BaseEmptyState } from './base';

// @vue/component
export const ChannelEmptyState = {
	name: 'ChannelEmptyState',
	components: { BaseEmptyState },
	computed:
	{
		text(): string
		{
			return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_V3');
		},
		subtext(): string
		{
			return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_SUBTITLE');
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<BaseEmptyState :text="text" :subtext="subtext" />
	`,
};
