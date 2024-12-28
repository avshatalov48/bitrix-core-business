import { Tag, Text, Type } from 'main.core';
import { Popup as MainPopup, type PopupOptions } from 'main.popup';

export const Popup = {
	name: 'Popup',
	emits: ['close'],
	props: {
		options: {
			/** @type PopupOptions */
			type: Object,
			default: {},
		},
	},
	data(): Object {
		return {
			isPopupShown: false,
			popupContentId: `ui-vue3-popup-${Text.getRandom()}`,
		};
	},
	popup: null,
	mounted()
	{
		const eventsFromOptions: PopupOptions['events'] = this.options.events ?? {};

		this.popup = new MainPopup({
			...this.options,
			cacheable: false,
			content: Tag.render`<div id="${this.popupContentId}"></div>`,
			events: {
				...eventsFromOptions,
				onPopupShow: (...args) => {
					// WARNING! Teleport should always be mounted AFTER the target node is rendered in DOM
					this.isPopupShown = true;

					// adjust position on page after vue has rendered popup content
					void this.$nextTick(() => {
						this.popup.adjustPosition();
					});

					if (Type.isFunction(eventsFromOptions.onPopupShow))
					{
						eventsFromOptions.onPopupShow(...args);
					}
				},
				onPopupAfterClose: (...args) => {
					this.isPopupShown = false;
					this.$emit('close');

					if (Type.isFunction(eventsFromOptions.onPopupAfterClose))
					{
						eventsFromOptions.onPopupAfterClose(...args);
					}
				},
			},
		});

		this.popup.show();
	},
	beforeUnmount()
	{
		this.popup?.close();
	},
	template: `
		<Teleport v-if="isPopupShown" :to="'#' + popupContentId">
			<slot/>
		</Teleport>
	`,
};
