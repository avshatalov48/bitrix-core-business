import {ChatInfoContent} from './chat-info-content';
import {MessengerPopup} from '../popup/popup';

const POPUP_ID = 'im-chat-info-popup';

// @vue/component
export const ChatInfoPopup = {
	name: 'ChatInfoPopup',
	components: {MessengerPopup, ChatInfoContent},
	props:
	{
		showPopup: {
			type: Boolean,
			required: true
		},
		bindElement: {
			type: Object,
			required: true
		},
		dialogId: {
			type: String,
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
				minWidth: 313,
				height: 134,
				bindElement: this.bindElement,
				targetContainer: document.body,
				offsetTop: 0,
				padding: 16,
				angle: true
			};
		}
	},
	template: `
		<MessengerPopup
			v-if="showPopup" 
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<ChatInfoContent :dialogId="dialogId"/>
		</MessengerPopup>
	`
};