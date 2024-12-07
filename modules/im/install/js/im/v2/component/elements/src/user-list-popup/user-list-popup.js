import { UserListContent } from './user-list-content';
import { MessengerPopup } from '../popup/popup';

import type { PopupOptions } from 'main.popup';

const POPUP_ID = 'im-user-list-popup';

// @vue/component
export const UserListPopup = {
	name: 'UserListPopup',
	components: { MessengerPopup, UserListContent },
	props:
	{
		showPopup: {
			type: Boolean,
			required: true,
		},
		id: {
			type: String,
			required: false,
			default: POPUP_ID,
		},
		bindElement: {
			type: Object,
			required: true,
		},
		userIds: {
			type: Array,
			required: true,
		},
		contextDialogId: {
			type: String,
			required: false,
			default: '',
		},
		withAngle: {
			type: Boolean,
			required: false,
			default: true,
		},
		loading: {
			type: Boolean,
			required: false,
			default: false,
		},
		forceTop: {
			type: Boolean,
			required: false,
			default: false,
		},
		offsetLeft: {
			type: Number,
			required: false,
			default: 0,
		},
	},
	emits: ['close'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			const config = {
				bindElement: this.bindElement,
				targetContainer: document.body,
				offsetTop: 4,
				offsetLeft: this.offsetLeft,
				padding: 0,
				angle: this.withAngle,
			};

			if (this.forceTop)
			{
				config.bindOptions = { position: 'top' };
			}

			return config;
		},
	},
	template: `
		<MessengerPopup
			v-if="showPopup"
			v-slot="{adjustPosition}"
			:config="config"
			@close="$emit('close')"
			:id="id"
		>
			<UserListContent 
				:userIds="userIds"
				:contextDialogId="contextDialogId"
				:loading="loading" 
				:adjustPopupFunction="adjustPosition"
			/>
		</MessengerPopup>
	`,
};
