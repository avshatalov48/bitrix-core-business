import { Text } from 'main.core';

import './css/detail-empty-state.css';

// @vue/component
export const DetailEmptyState = {
	name: 'DetailEmptyState',
	props: {
		title: {
			type: String,
			required: true,
		},
		iconType: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		iconClass(): string
		{
			return `--${Text.toKebabCase(this.iconType)}`;
		},
	},
	template: `
		<div class="bx-im-sidebar-detail-empty-state__container bx-im-sidebar-detail-empty-state__scope">
			<span class="bx-im-sidebar-detail-empty-state__icon" :class="[iconClass]"></span>
			<span class="bx-im-sidebar-detail-empty-state__text">{{ title }}</span>
		</div>
	`,
};
