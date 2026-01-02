import { AutoDeleteDelay } from 'im.v2.const';
import { AutoDeleteManager } from 'im.v2.lib.auto-delete';
import { MessengerPopup } from 'im.v2.component.elements.popup';

import { RadioGroup } from './components/radio-group/radio-group';

import './css/auto-delete-popup.css';

import type { PopupOptions } from 'main.popup';

type AutoDeleteDelayOption = {
	value: number;
	text: string;
	selected: boolean;
};

const POPUP_ID = 'auto-delete-choose-delay-popup';

// @vue/component
export const AutoDeletePopup = {
	name: 'AutoDeletePopup',
	components: { MessengerPopup, RadioGroup },
	props:
	{
		autoDeleteDelay: {
			type: Number,
			default: 0,
		},
	},
	emits: ['close', 'autoDeleteDelayChange'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				overlay: true,
				height: 342,
				width: 400,
				titleBar: this.loc('IM_ELEMENTS_AUTO_DELETE_POPUP_TITLE'),
				closeIcon: true,
				targetContainer: document.body,
				fixed: true,
				padding: 0,
				autoHide: true,
				contentPadding: 0,
				contentBackground: '#fff',
				className: 'bx-im-auto-delete-popup__scope',
			};
		},
		options(): AutoDeleteDelayOption[]
		{
			return Object.values(AutoDeleteDelay).map((delayValue) => ({
				value: delayValue,
				text: AutoDeleteManager.getStatusText(delayValue),
				selected: this.autoDeleteDelay === delayValue,
			}));
		},
	},
	methods:
	{
		onAutoDeleteDelayChange(delay: number): void
		{
			this.$emit('autoDeleteDelayChange', delay);
			this.$emit('close');
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
			@close="$emit('close')"
		>
			<div class="bx-im-auto-delete-popup__container">
				<div class="bx-im-auto-delete-popup__info">
					{{ this.loc('IM_ELEMENTS_AUTO_DELETE_POPUP_INFO') }}
				</div>
				<RadioGroup :items="options" @change="onAutoDeleteDelayChange" />
			</div>
		</MessengerPopup>
	`,
};
