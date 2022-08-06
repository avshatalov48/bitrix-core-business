import {Dom, Event, Tag, Loc} from "main.core";
import {Address as AddressEntity} from "location.core";
import {BaseView} from './baseview';

import './css/style.css';
import {EditEntry} from "./editentry";
import {EventEmitter} from "main.core.events";

export class Edit extends BaseView
{
	#fieldName = '';
	#fieldFormName = '';
	#addresses: AddressEntity[] = [];
	#inputsWrapper: Element = null;
	#isMultiple = false;
	#isCompactMode = false;
	#showMap = true;
	#inputs: EditEntry[] = [];

	constructor(params: Object)
	{
		super(params);
		this.#fieldName = params.fieldName;
		this.#fieldFormName = params.fieldFormName;
		this.#addresses = params.addresses;
		this.#isMultiple = params.isMultiple;
		this.#isCompactMode = params.compactMode;
		this.#showMap = params.showMap;
	}

	layout(): Element
	{
		const layout = Tag.render`<div class="address-edit-wrapper"></div>`;

		const inputsWrapper = Tag.render`<div class="address-inputs-wrapper"></div>`;

		if (this.#addresses.length > 0)
		{
			this.#addresses.forEach((address) => {
				const input = this.createInputForAddress(address);
				Dom.append(input.layout(), inputsWrapper);
			});
		}
		else
		{
			const input = this.createInputForAddress();
			Dom.append(input.layout(), inputsWrapper);
		}

		Dom.append(inputsWrapper, layout);
		this.#inputsWrapper = inputsWrapper;

		if (this.#isMultiple)
		{
			const addInputElement = Tag.render`<input type="button" value="${Loc.getMessage('ADDRESS_USERFIELD_ADD_INPUT')}" />`;
			Event.bind(addInputElement, 'click', this.addInput.bind(this));
			Dom.append(addInputElement, layout);
		}

		Dom.append(layout, this.getWrapper());

		return this.getWrapper();
	}

	addInput()
	{
		if (!this.#inputsWrapper)
		{
			return;
		}

		const input = this.createInputForAddress();
		Dom.append(input.layout(), this.#inputsWrapper);
	}

	createInputForAddress(address: AddressEntity): EditEntry
	{
		const entry = new EditEntry({
			wrapper: this.getWrapper(),
			address: address,
			fieldName: this.#fieldName,
			fieldFormName: this.#fieldFormName,
			enableRemoveButton: this.#isMultiple,
			initialAddressId: parseInt(address?.id) ?? null,
			isCompactMode: this.#isCompactMode,
			showMap: this.#showMap,
		});
		EventEmitter.subscribe(entry, EditEntry.onRemoveInputButtonClickedEvent, this.removeInput.bind(this, entry));
		this.#inputs.push(entry);

		return entry;
	}

	removeInput(input: EditEntry)
	{
		const activeInputsCount = this.#inputs.filter((input) => {return !input.isDestroyed()}).length;

		if (activeInputsCount > 1)
		{
			input.destroy();
		}
	}
}
