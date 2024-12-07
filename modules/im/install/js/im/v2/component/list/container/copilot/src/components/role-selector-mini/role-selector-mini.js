import { MessengerPopup } from 'im.v2.component.elements';

import { RoleSelectorMiniContent } from './components/role-selector-mini-content';

import type { PopupOptions } from 'main.popup';

const POPUP_ID = 'im-role-selector-mini-popup';

// @vue/component
export const RoleSelectorMini = {
	name: 'RoleSelectorMini',
	components: { MessengerPopup, RoleSelectorMiniContent },
	props:
	{
		bindElement: {
			type: Object,
			required: true,
		},
	},
	emits: ['close', 'selectedRole', 'openMainSelector'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				width: 294,
				closeIcon: false,
				bindElement: this.bindElement,
				offsetTop: 0,
				offsetLeft: 0,
				padding: 0,
				contentPadding: 0,
				contentBackground: '#fff',
			};
		},
	},
	template: `
		<MessengerPopup
			v-slot="{enableAutoHide, disableAutoHide}"
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<RoleSelectorMiniContent
				@selectedRole="$emit('selectedRole', $event)"
				@openMainSelector="$emit('openMainSelector')"
				@close="$emit('close')"
			/>
		</MessengerPopup>
	`,
};
