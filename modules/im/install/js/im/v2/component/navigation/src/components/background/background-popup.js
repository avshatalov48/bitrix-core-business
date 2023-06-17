import {MessengerPopup} from 'im.v2.component.elements';

import {BackgroundContent} from './background-content';

const POPUP_ID = 'im-background-select-popup';

// @vue/component
export const BackgroundPopup = {
	name: 'BackgroundPopup',
	components: {MessengerPopup, BackgroundContent},
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
			<BackgroundContent @close="$emit('close')" />
		</MessengerPopup>
	`
};