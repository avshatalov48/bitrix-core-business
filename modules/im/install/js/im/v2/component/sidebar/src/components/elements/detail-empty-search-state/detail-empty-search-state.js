import './css/detail-empty-search-state.css';

// @vue/component
export const DetailEmptySearchState = {
	name: 'DetailEmptySearchState',
	props: {
		title: {
			type: String,
			required: true,
		},
		subTitle: {
			type: String,
			required: false,
			default: '',
		},
	},
	template: `
		<div class="bx-im-detail-empty-search-state__container">
			<div class="bx-im-detail-empty-search-state__icon"></div>
			<div class="bx-im-detail-empty-search-state__title">
				{{ title }}
			</div>
			<div class="bx-im-detail-empty-search-state__subtitle">
				{{ subTitle }}
			</div>
		</div>
	`,
};
