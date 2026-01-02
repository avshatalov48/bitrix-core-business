import { MessengerPopup } from 'im.v2.component.elements.popup';

import { AIModelPopupContent } from './ai-model-popup-content';

import type { PopupOptions } from 'main.popup';

import './css/ai-model-popup.css';

const POPUP_ID = 'im-ai-model-popup';

// @vue/component
export const AIModelPopup = {
	name: 'AIModelPopup',
	components: { MessengerPopup, AIModelPopupContent },
	props:
	{
		bindElement: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				width: 224,
				bindElement: this.bindElement,
				offsetTop: 2,
				offsetLeft: 0,
				fixed: true,
				bindOptions: {
					position: 'bottom',
				},
				className: 'bx-im-ai-model-popup__scope',
			};
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
		>
			<AIModelPopupContent
				:dialogId="dialogId"
				@close="$emit('close')"
			/>
		</MessengerPopup>
	`,
};
