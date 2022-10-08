import {Event, Dom, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Format, Address, ControlMode} from 'location.core';
import Field from './field';

export type FieldsConstructorType = {
	addressFormat: Format
}

export type FieldsRenderType = {
	address: Address,
	mode: string, // ControlMode
	container: Element
}

export default class Fields extends EventEmitter
{
	static #onAddressChangedEvent = 'onAddressChanged';
	static #onStateChangedEvent = 'onStateChanged';

	#address;
	#addressFormat;
	#mode;
	#fields = [];
	#languageId;
	#container;
	#state;

	constructor(props: FieldsConstructorType)
	{
		super(props);

		this.setEventNamespace('BX.Location.Widget.Fields');

		if(!(props.addressFormat instanceof Format))
		{
			BX.debug('addressFormat must be instance of Format');
		}

		this.#addressFormat = props.addressFormat;
		this.#languageId = props.languageId;
		this.#initFields();
	}

	#initFields()
	{
		for (let type in this.#addressFormat.fieldCollection.fields)
		{
			if(!this.#addressFormat.fieldCollection.fields.hasOwnProperty(type))
			{
				continue;
			}

			let formatField = this.#addressFormat.fieldCollection.fields[type];

			let field = new Field({
				title: formatField.name,
				type: formatField.type,
				sort: formatField.sort
			});

			field.subscribeOnValueChangedEvent((event) => {
				this.#onFieldChanged(field);
			});

			field.subscribeOnStateChangedEvent((event) => {
				let data = event.getData();
				this.#setState(data.state);
			});

			this.#fields.push(field)
		}

		this.#fields.sort((a, b) => {
			return a.sort - b.sort;
		});
	}

	render(props: FieldsRenderType): void
	{
		if(props.address && !(props.address instanceof Address))
		{
			BX.debug('props.address must be instance of Address');
		}

		this.#address = props.address;

		if(!ControlMode.isValid(props.mode))
		{
			BX.debug('props.mode must be valid ControlMode');
		}

		this.#mode = props.mode;

		if(!Type.isDomNode(props.container))
		{
			BX.debug('props.container must be dom node');
		}

		this.#container = props.container;

		for(let field of this.#fields)
		{
			let value = this.#address ? this.#address.getFieldValue(field.type) : '';

			if(this.#mode === ControlMode.view && !value)
			{
				continue;
			}

			let item = field.render({
				value: value,
				mode: this.#mode
			});

			this.#container.appendChild(item);
		}
	}

	#onFieldChanged(field: Field)
	{
		if(!this.#address)
		{
			this.#address = new Address({
				languageId: this.#languageId
			});
		}

		this.#address.setFieldValue(field.type, field.value);

		this.emit(Fields.#onAddressChangedEvent, {
			address: this.#address,
			changedField: field
		});
	}

	set address(address: ?Address)
	{
		if(address && !(address instanceof Address))
		{
			BX.debug('address must be instance of Address');
		}

		this.#address = address;

		for(let field of this.#fields)
		{
			field.value = this.#address ? this.#address.getFieldValue(field.type) : '';
		}
	}

	subscribeOnAddressChangedEvent(listener: Function): void
	{
		this.subscribe(Fields.#onAddressChangedEvent, listener);
	}

	destroy()
	{
		Event.unbindAll(this);

		for(let field of this.#fields)
		{
			field.destroy();
		}

		Dom.clean(this.#container);
	}

	get state()
	{
		return this.#state;
	}

	#setState(state: string)
	{
		this.#state = state;
		this.emit(Fields.#onStateChangedEvent, {state: this.#state});
	}

	subscribeOnStateChangedEvent(listener: Function): void
	{
		this.subscribe(Fields.#onStateChangedEvent, listener);
	}
}