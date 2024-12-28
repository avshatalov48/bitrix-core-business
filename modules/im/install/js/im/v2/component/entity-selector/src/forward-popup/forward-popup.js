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
		messagesIds: {
			type: Array,
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
				titleBar: this.popupTitle,
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
		popupTitle(): string
		{
			return this.messagesIds.length > 1
				? this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_FORWARD_TITLE_SEVERAL_MESSAGES')
				: this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_FORWARD_TITLE');
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
			:id="POPUP_ID"
			:config="config"
			@close="$emit('close')"
		>
			<ForwardContent :messagesIds="messagesIds" @close="$emit('close')" />
		</MessengerPopup>
	`,
};
