import { PopupOptions } from 'main.popup';

import { MessengerPopup } from 'im.v2.component.elements';

import { AddToChatContent } from './add-to-chat-content';

const POPUP_ID = 'im-add-to-chat-popup';

// @vue/component
export const AddToChat = {
	name: 'AddToChat',
	components: { MessengerPopup, AddToChatContent },
	props:
	{
		showPopup: {
			type: Boolean,
			required: true,
		},
		bindElement: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		popupConfig: {
			type: Object,
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
				titleBar: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MEMBERS_TITLE'),
				closeIcon: true,
				bindElement: this.bindElement,
				offsetTop: this.popupConfig.offsetTop,
				offsetLeft: this.popupConfig.offsetLeft,
				padding: 0,
				contentPadding: 0,
				contentBackground: '#fff',
				className: 'bx-im-entity-selector-add-to-chat__scope',
			};
		},
	},
	template: `
		<MessengerPopup
			v-if="showPopup"
			v-slot="{enableAutoHide, disableAutoHide}"
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<AddToChatContent :dialogId="dialogId" @close="$emit('close')"/>
		</MessengerPopup>
	`,
};
