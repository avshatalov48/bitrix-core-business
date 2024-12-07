import { Popup } from 'main.popup';
import { Type } from 'main.core';

import type { UploaderError } from 'ui.uploader.core';
import type { BitrixVueComponentProps } from 'ui.vue3';

/**
 * @memberof BX.UI.Uploader
 */
export const ErrorPopup: BitrixVueComponentProps = {
	props: {
		error: {
			type: [Object, String],
		},
		alignArrow: {
			type: Boolean,
			default: true,
		},
		popupOptions: {
			type: Object,
			default(): {}
			{
				return {};
			},
		},
	},
	emits: ['onDestroy'],
	watch: {
		error(newValue): void
		{
			if (this.errorPopup)
			{
				this.errorPopup.destroy();
			}

			this.errorPopup = this.createPopup(newValue);
			this.errorPopup.show();
		},
	},
	created(): void
	{
		this.errorPopup = null;
	},
	mounted(): void
	{
		if (this.error)
		{
			this.errorPopup = this.createPopup(this.error);
			this.errorPopup.show();
		}
	},
	beforeUnmount(): void
	{
		if (this.errorPopup)
		{
			this.errorPopup.destroy();
			this.errorPopup = null;
		}
	},
	methods: {
		createContent(error: UploaderError | string): string
		{
			if (Type.isStringFilled(error))
			{
				return error;
			}
			else if (Type.isObject(error))
			{
				return error.message + '<br>' + error.description;
			}

			return '';
		},

		createPopup(error: UploaderError | string): Popup
		{
			const content = this.createContent(error);
			let defaultOptions;
			if (this.alignArrow && Type.isElementNode(this.popupOptions.bindElement))
			{
				const targetNode = this.popupOptions.bindElement;
				const targetNodeWidth = targetNode.offsetWidth;

				defaultOptions = {
					cacheable: false,
					animation: 'fading-slide',
					content,
					// minWidth: 300,
					events: {
						onDestroy: () => {
							this.$emit('onDestroy', error);
							this.errorPopup = null;
						},
						onShow: function(event) {
							const popup = event.getTarget();
							popup.getPopupContainer().style.display = 'block';

							const popupWidth = popup.getPopupContainer().offsetWidth;
							const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
							const angleShift = Popup.getOption('angleLeftOffset') - Popup.getOption('angleMinTop');

							popup.setAngle({ offset: popupWidth / 2 - angleShift });
							popup.setOffset({ offsetLeft: offsetLeft + Popup.getOption('angleLeftOffset') });
						},
					},
				};
			}
			else
			{
				defaultOptions = {
					cacheable: false,
					animation: 'fading-slide',
					content,
					events: {
						onDestroy: () => {
							this.$emit('onDestroy', error);
							this.errorPopup = null;
						},
					}
				};
			}

			const options = Object.assign({}, defaultOptions, this.popupOptions);

			return new Popup(options);
		},
	},
	template: '<span></span>',
};
