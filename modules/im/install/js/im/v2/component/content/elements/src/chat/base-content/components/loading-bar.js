import '../css/loading-bar.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const LoadingBar = {
	name: 'LoadingBar',
	data(): JsonObject
	{
		return {};
	},
	template: `
		<div class="bx-im-content-chat__loading-bar"></div>
	`,
};
