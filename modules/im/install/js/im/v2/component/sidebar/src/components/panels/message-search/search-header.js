import { SearchInput } from 'im.v2.component.elements';

import './css/search-header.css';

// @vue/component
export const SearchHeader = {
	name: 'SearchHeader',
	components: { SearchInput },
	props:
	{
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['back', 'changeQuery'],
	template: `
		<div class="bx-im-sidebar-search-header__container bx-im-sidebar-search-header__scope">
			<div class="bx-im-sidebar-search-header__title-container">
				<button
					:class="{'bx-im-messenger__cross-icon': !secondLevel, 'bx-im-sidebar__back-icon': secondLevel}"
					@click="$emit('back')"
				></button>
				<SearchInput
					:placeholder="$Bitrix.Loc.getMessage('IM_SIDEBAR_SEARCH_MESSAGE_PLACEHOLDER')"
					:withIcon="false"
					:delayForFocusOnStart="300"
					@queryChange="$emit('changeQuery', $event)"
					class="bx-im-sidebar-search-header__input"
				/>
			</div>
		</div>
	`,
};
