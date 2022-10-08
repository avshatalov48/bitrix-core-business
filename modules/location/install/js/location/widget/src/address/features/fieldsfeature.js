import {Type} from 'main.core';
import {Address, ControlMode} from 'location.core';
import BaseFeature from './basefeature';
import Fields from '../../fields/fields';

/**
 * Fields widget feature
 */
export default class FieldsFeature extends BaseFeature
{
	#fields;
	#addressWidget = null;

	constructor(props)
	{
		super(props);

		if(!(props.fields instanceof Fields))
		{
			BX.debug('props.Fields must be instance of Fields');
		}

		this.#fields = props.fields;

		this.#fields.subscribeOnAddressChangedEvent(
			(event) => {
				let data = event.getData();
				this.#addressWidget.setAddressByFeature(data.address, this);
			});

		this.#fields.subscribeOnStateChangedEvent(
			(event) => {
				let data = event.getData();
				this.#addressWidget.setStateByFeature(data.state);
			});
	}

	render(props): void
	{
		if(this.#addressWidget.mode === ControlMode.edit)
		{
			if (!Type.isDomNode(props.fieldsContainer))
			{
				BX.debug('props.fieldsContainer  must be instance of Element');
			}

			this.#fields.render({
				address: this.#addressWidget.address,
				mode: this.#addressWidget.mode,
				container: props.fieldsContainer
			});
		}
	}

	setAddressWidget(addressWidget)
	{
		this.#addressWidget = addressWidget;
	}

	setAddress(address: Address): void
	{
		this.#fields.address = address;
	}

	setMode(mode: string): void
	{
		this.#fields.mode = mode;
	}

	destroy(): void
	{
		this.#fields.destroy();
		this.#fields = null;
	}
}