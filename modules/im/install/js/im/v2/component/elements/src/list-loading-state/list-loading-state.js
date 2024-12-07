import './css/list-loading-state.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const ListLoadingState = {
	name: 'ListLoadingState',
	data(): JsonObject
	{
		return {};
	},
	template: `
		<div class="bx-im-list-loading-state__container"></div>
	`,
};
