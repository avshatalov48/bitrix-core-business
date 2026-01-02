import './open-search-btn.css';
import { BIcon, Outline } from 'ui.icon-set.api.vue';

type OpenSearchBtnSetup = {
	iconSet: {...};
};

// @vue/component
export const OpenSearchBtn = {
	name: 'open-search-btn',
	components: {
		BIcon,
	},
	setup(): OpenSearchBtnSetup
	{
		return {
			iconSet: Outline,
		};
	},
	template: `
		<button class="ui-block-diagram-open-search-btn">
			<BIcon
				:name="iconSet.SEARCH"
				:size="24"
				class="ui-block-diagram-open-search-btn__icon"
			/>
		</button>
	`,
};
