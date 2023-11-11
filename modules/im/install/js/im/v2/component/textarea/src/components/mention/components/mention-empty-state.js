import '../css/mention-empty-state.css';

// @vue/component
export const MentionEmptyState = {
	name: 'MentionEmptyState',
	template: `
		<div class="bx-im-mention-empty-state__scope bx-im-mention-empty-state__container">
			<span class="bx-im-mention-empty-state__icon"></span>
			<span class="bx-im-mention-empty-state__title">
				{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_EMPTY_STATE') }}
			</span>
		</div>
	`,
};
