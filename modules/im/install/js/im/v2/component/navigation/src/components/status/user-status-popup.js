import {MessengerPopup} from 'im.v2.component.elements';

import {UserStatusContent} from './user-status-content';

const POPUP_ID = 'im-user-status-popup';

// @vue/component
export const UserStatusPopup = {
	name: 'UserStatusPopup',
	components: {MessengerPopup, UserStatusContent},
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
				width: 190,
				bindElement: this.bindElement,
				offsetTop: 4,
				padding: 0,
			};
		}
	},
	template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<UserStatusContent @close="$emit('close')" />
		</MessengerPopup>
	`
};