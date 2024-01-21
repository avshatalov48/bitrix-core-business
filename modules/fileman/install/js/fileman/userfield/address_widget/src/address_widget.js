import { Address as AddressEntity } from 'location.core';
import { Reflection, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Edit } from './view/edit';

import { View } from './view/view';

class AddressField
{
	static VIEW_MODE = 'view';
	static EDIT_MODE = 'edit';

	#mode = AddressField.VIEW_MODE;
	#wrapper: Element;
	#addresses: [];
	#isMultiple: boolean = false;
	#showMap: boolean = true;

	#fieldConfig = {};
	#additionalProperties = {};

	static init(params: Object)
	{
		const mode = params.mode;
		const wrapper = document.getElementById(params.wrapperId);
		if (!wrapper)
		{
			return;
		}

		let addresses = [];
		const addressData = params.addressData;
		addressData.forEach((addressFields) => {
			if (Type.isObject(addressFields))
			{
				addresses.push(new AddressEntity(addressFields));
			}
		});

		const showMap = params.showMap ?? true;
		let addressFieldParams = {
			addresses: addresses,
			wrapper: wrapper,
			mode: mode,
			fieldConfig: {
				fieldName: params.fieldName,
				fieldFormName: params.fieldFormName,
			},
			isMultiple: params.isMultiple,
			showMap,
		};

		if (params.additionalProperties)
		{
			addressFieldParams.additionalProperties = params.additionalProperties;
		}

		const addressField = new AddressField(addressFieldParams);
		addressField.layout();

		EventEmitter.emit(this, 'BX.Fileman.UserField.AddressField:onInitiated', addressFieldParams);
	}

	constructor(params: Object)
	{
		this.#mode = params.mode;
		this.#wrapper = params.wrapper;
		this.#addresses = params.addresses;
		this.#fieldConfig = params.fieldConfig;
		this.#isMultiple = params.isMultiple;
		this.#showMap = params.showMap;
		if (params.additionalProperties)
		{
			this.#additionalProperties = params.additionalProperties;
		}
	}

	layout()
	{
		/** @type BaseView */
		let view = null;

		if (this.#mode === AddressField.VIEW_MODE)
		{
			view = new View({
				wrapper: this.#wrapper,
				addresses: this.#addresses,
			});
		}

		if (this.#mode === AddressField.EDIT_MODE)
		{
			view = new Edit({
				wrapper: this.#wrapper,
				fieldName: this.#fieldConfig.fieldName,
				fieldFormName: this.#fieldConfig.fieldFormName,
				addresses: this.#addresses,
				isMultiple: this.#isMultiple,
				compactMode: this.#additionalProperties.compactMode ?? false,
				showMap: this.#showMap,
			});
		}

		if (view)
		{
			view.layout()
		}
	}
}

const namespace = Reflection.namespace('BX.Fileman.UserField');
namespace.AddressField = AddressField;
