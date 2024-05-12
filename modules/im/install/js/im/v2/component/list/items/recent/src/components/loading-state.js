import '../css/loading-state.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const LoadingState = {
	name: 'LoadingState',
	data(): JsonObject
	{
		return {};
	},
	template: `
		<div class="bx-im-list-recent-loading-state__container"></div>
	`,
};
