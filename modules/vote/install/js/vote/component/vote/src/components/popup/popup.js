import { PopupOptions } from 'main.popup';

import { Popup } from 'ui.vue3.components.popup';
import { getMessage } from '../helpers/helpers';

import './style.css';

// @vue/component
export const VotePopup = {
	name: 'VotePopup',
	components: { Popup },
	emits: ['confirm', 'cancel'],
	computed:
	{
		getMessage: () => getMessage,
		popupOptions(): PopupOptions
		{
			return {
				width: 374,
				className: 'vote-display__popup',
			};
		},
	},
	template: `
		<Popup
			:options="popupOptions" @close="$emit('cancel')"
			id="vote-display-popup"
		>
			<div class="vote-display__popup-content">
				<div class="vote-display__popup-title">
					{{ getMessage('VOTE_POPUP_TITLE') }}
				</div>
				<div class="vote-display__popup-text">
					{{ getMessage('VOTE_POPUP_TEXT') }}
				</div>
			</div>
			<div class="vote-display__popup-footer">
				<button class="vote-display__popup-btn --complete" @click="$emit('confirm')">
					{{ getMessage('VOTE_POPUP_BTN_COMPLETE') }}
				</button>
				<button class="vote-display__popup-btn --cancel" @click="$emit('cancel')">
					{{ getMessage('VOTE_POPUP_BTN_CANCEL') }}
				</button>
			</div>
		</Popup>
	`,
};
