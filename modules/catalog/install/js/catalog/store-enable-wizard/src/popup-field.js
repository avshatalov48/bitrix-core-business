import { Extension } from 'main.core';
import { Popup } from './popup';

import 'ui.buttons';

const PopupField = {
	props: {
		isLoading: {
			type: Boolean,
		},
		isShown: {
			type: Boolean,
		},
		primaryButtonText: {
			type: String,
		},
		title: {
			type: String,
		},
		texts: {
			type: Array,
		},
	},
	created()
	{
		this.popup = new Popup({
			helpCode: this.getSetting('availableModes').length > 1 ? '20233748' : '15992592',
			title: this.title,
			texts: this.texts,
			primaryButtonText: this.primaryButtonText,
			secondaryButtonText: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_CANCEL'),
			events: {
				onPrimaryClick: () => this.$emit('enable'),
				onSecondaryClick: () => this.popup.show(false),
				onClose: () => this.$emit('cancel'),
			},
		});
	},
	methods: {
		getSetting(name)
		{
			return Extension.getSettings('catalog.store-enable-wizard').get(name);
		},
	},
	watch: {
		isLoading(newValue)
		{
			this.popup.load(newValue);
		},
		isShown(newValue)
		{
			this.popup.show(newValue);
		},
	},
	template: '',
};

export {
	PopupField,
};
