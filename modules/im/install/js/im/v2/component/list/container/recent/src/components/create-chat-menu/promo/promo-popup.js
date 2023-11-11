import { MessengerPopup } from 'im.v2.component.elements';

import type { PopupOptions } from 'main.popup';

const POPUP_ID = 'im-create-chat-promo-popup';

// @vue/component
export const PromoPopup = {
	name: 'PromoPopup',
	components: { MessengerPopup },
	emits: ['close'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				width: 492,
				padding: 0,
				overlay: true,
				autoHide: false,
				closeByEsc: false,
			};
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<slot></slot>
		</MessengerPopup>
	`,
};
