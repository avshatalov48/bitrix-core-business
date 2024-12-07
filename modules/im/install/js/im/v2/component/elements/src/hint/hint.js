import { Hint } from 'ui.vue3.components.hint';

import type { PopupOptions } from 'main.popup';

// @vue/component
export const ChatHint = {
	name: 'ChatHint',
	components: { Hint },
	props:
	{
		text: {
			type: String,
			required: false,
			default: '',
		},
		html: {
			type: String,
			required: false,
			default: '',
		},
		popupOptions: {
			type: Object,
			required: false,
			default(): {} {
				return {};
			},
		},
	},
	computed:
	{
		preparedPopupOptions(): PopupOptions
		{
			return {
				targetContainer: document.body,
				...this.popupOptions,
			};
		},
	},
	template: `
		<Hint :text="text" :html="html" :popupOptions="preparedPopupOptions" />
	`,
};
