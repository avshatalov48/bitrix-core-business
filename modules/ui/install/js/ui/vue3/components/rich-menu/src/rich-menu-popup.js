import { type PopupOptions } from 'main.popup';
import { Popup } from 'ui.vue3.components.popup';
import { RichMenu } from './rich-menu';

const defaultPopupOptions: PopupOptions = Object.freeze({
	width: 275,
	padding: 0,
	closeIcon: false,
	autoHide: true,
	closeByEsc: true,
	animation: 'fading',
	contentBorderRadius: '10px',
});

export const RichMenuPopup = {
	name: 'RichMenuPopup',
	emits: ['close'],
	components: { Popup, RichMenu },
	props: {
		popupOptions: {
			/** @type PopupOptions */
			type: Object,
			default: {},
		},
	},
	computed: {
		allOptions(): PopupOptions
		{
			return {
				...defaultPopupOptions,
				...this.popupOptions,
			};
		},
	},
	template: `
		<Popup @close="$emit('close')" :options="allOptions">
			<RichMenu v-bind="$attrs">
				<template #header>
					<slot name="header"></slot>
				</template>
				<slot></slot>
				<template #footer>
					<slot name="footer"></slot>
				</template>
			</RichMenu>
		</Popup>
	`,
};
