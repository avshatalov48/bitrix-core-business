import { PopupOptions } from 'main.popup';

import { MessengerPopup } from 'im.v2.component.elements';

import { ForwardContent } from './forward-content';

const POPUP_ID = 'im-forward-popup';

// @vue/component
export const ForwardPopup = {
	name: 'ForwardPopup',
	components: { MessengerPopup, ForwardContent },
	props:
	{
		showPopup: {
			type: Boolean,
			required: true,
		},
		messageId: {
			type: [Number, String],
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
				titleBar: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_FORWARD_TITLE'),
				closeIcon: true,
				targetContainer: document.body,
				fixed: true,
				draggable: true,
				padding: 0,
				autoHide: false,
				contentPadding: 0,
				contentBackground: '#fff',
				className: 'bx-im-entity-selector-forward__scope',
			};
		},
	},
	template: `
		<MessengerPopup
			v-if="showPopup"
			:id="POPUP_ID"
			:config="config"
			@close="$emit('close')"
		>
			<ForwardContent :messageId="messageId" @close="$emit('close')" />
		</MessengerPopup>
	`,
};
