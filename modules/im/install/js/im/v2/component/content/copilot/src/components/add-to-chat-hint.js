import { PopupOptions } from 'main.popup';

import { MessengerPopup } from 'im.v2.component.elements';

import '../css/add-to-chat-hint.css';

const POPUP_ID = 'im-add-to-chat-hint-popup';

// @vue/component
export const AddToChatHint = {
	name: 'AddToChatHint',
	components: { MessengerPopup },
	props:
	{
		bindElement: {
			type: Object,
			required: true,
		},
	},
	emits: ['close', 'hide'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				darkMode: true,
				bindElement: this.bindElement,
				angle: true,
				width: 346,
				closeIcon: true,
				offsetLeft: 8,
				className: 'bx-im-copilot-add-to-chat-hint__scope',
				contentBorderRadius: 0,
			};
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<MessengerPopup
			v-slot="{enableAutoHide, disableAutoHide}"
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<div class="bx-im-copilot-add-to-chat-hint__title">
				{{ loc('IM_CONTENT_COPILOT_ADD_TO_CHAT_HINT_TITLE') }}
			</div>
			<br />
			<div class="bx-im-copilot-add-to-chat-hint__description">
				{{ loc('IM_CONTENT_COPILOT_ADD_TO_CHAT_HINT_DESCRIPTION') }}
			</div>
			<br />
			<button class="bx-im-copilot-add-to-chat-hint__hide" @click="$emit('hide')">
				{{ loc('IM_CONTENT_COPILOT_ADD_TO_CHAT_HINT_HIDE') }}
			</button>
		</MessengerPopup>
	`,
};
