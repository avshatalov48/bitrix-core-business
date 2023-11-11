import { MessengerPopup } from 'im.v2.component.elements';

import { MentionPopupContent } from './components/mention-popup-content';

import './css/mention-popup.css';

import type { PopupOptions } from 'main.popup';

const POPUP_ID = 'im-mention-popup';

// @vue/component
export const MentionPopup = {
	name: 'MentionPopup',
	components: { MessengerPopup, MentionPopupContent },
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
		query: {
			type: String,
			default: '',
		},
	},
	emits: ['close'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				width: 320,
				padding: 0,
				bindElement: this.bindElement,
				offsetTop: 14,
				offsetLeft: -37,
				bindOptions: {
					position: 'top',
				},
				className: 'bx-im-mention-popup__scope',
			};
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close');"
			:id="POPUP_ID"
			v-slot="{adjustPosition}"
		>
			<MentionPopupContent 
				:dialogId="dialogId"
				:query="query"
				@close="$emit('close');"
				@adjustPosition="adjustPosition()"
			/>
		</MessengerPopup>
	`,
};
