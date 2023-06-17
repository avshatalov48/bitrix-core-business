import {MessengerPopup} from 'im.v2.component.elements';

import {UserSettingsContent} from './user-settings-content';

const POPUP_ID = 'im-user-settings-popup';

// @vue/component
export const UserSettingsPopup = {
	name: 'UserSettingsPopup',
	components: {MessengerPopup, UserSettingsContent},
	props:
	{
		bindElement: {
			type: Object,
			required: true
		}
	},
	emits: ['close'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config()
		{
			return {
				width: 313,
				bindElement: this.bindElement,
				offsetTop: 4,
				padding: 0,
			};
		}
	},
	template: `
		<MessengerPopup
			v-slot="{enableAutoHide, disableAutoHide}"
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<UserSettingsContent @closePopup="$emit('close')" @enableAutoHide="enableAutoHide" @disableAutoHide="disableAutoHide" />
		</MessengerPopup>
	`
};