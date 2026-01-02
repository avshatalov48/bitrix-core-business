import './css/empty-state.css';

// @vue/component
export const RecentEmptyState = {
	name: 'RecentEmptyState',
	props: {
		title: {
			type: String,
			required: true,
		},
		subtitle: {
			type: String,
			required: false,
			default: '',
		},
	},
	template: `
		<div class="bx-im-list-recent-empty-state__container">
			<div class="bx-im-list-recent-empty-state__image"></div>
			<div class="bx-im-list-recent-empty-state__title">{{ title }}</div>
			<div v-if="subtitle" class="bx-im-list-recent-empty-state__subtitle">{{ subtitle }}</div>
			<slot></slot>
		</div>
	`,
};
