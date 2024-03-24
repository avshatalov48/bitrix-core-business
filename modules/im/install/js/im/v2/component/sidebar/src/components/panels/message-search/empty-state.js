import './css/empty-state.css';

// @vue/component
export const EmptyState = {
	name: 'EmptyState',
	computed:
	{
		title(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND');
		},
		subTitle(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MESSAGE_SEARCH_NOT_FOUND_DESCRIPTION');
		},
	},
	template: `
		<div class="bx-im-message-search-empty-state__container bx-im-message-search-empty-state__scope">
			<div class="bx-im-message-search-empty-state__icon"></div>
			<div class="bx-im-message-search-empty-state__title">
				{{ title }}
			</div>
			<div class="bx-im-message-search-empty-state__subtitle">
				{{ subTitle }}
			</div>
		</div>
	`,
};
