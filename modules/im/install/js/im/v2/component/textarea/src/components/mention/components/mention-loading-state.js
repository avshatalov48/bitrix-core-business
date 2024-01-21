import { Spinner, SpinnerSize, SpinnerColor } from 'im.v2.component.elements';

import '../css/mention-loading-state.css';

// @vue/component
export const MentionLoadingState = {
	name: 'MentionLoadingState',
	components: { Spinner },
	computed:
	{
		SpinnerSize: () => SpinnerSize,
		SpinnerColor: () => SpinnerColor,
	},
	template: `
		<div class="bx-im-mention-loading-state__scope bx-im-mention-loading-state__container">
			<div class="bx-im-mention-loading-state__loader">
				<Spinner :size="SpinnerSize.XXS" :color="SpinnerColor.grey"/>
			</div>
			<span class="bx-im-mention-loading-state__title">
				{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_LOADING_STATE') }}
			</span>
		</div>
	`,
};
