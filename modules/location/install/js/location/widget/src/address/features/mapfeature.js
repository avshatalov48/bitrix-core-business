import {Type} from 'main.core';
import {Address} from 'location.core';
import MapPopup from '../../mappopup/mappopup';
import BaseFeature from './basefeature';
import State from '../../state';

/**
 * Complex address widget
 */
export default class MapFeature extends BaseFeature
{
	#map = null;

	#mapBindElement = null;
	#addressWidget = null;

	constructor(props)
	{
		super(props);

		if(!(props.map instanceof MapPopup))
		{
			BX.debug('props.map must be instance of MapPopup');
		}

		this.#map = props.map;

		this.#map.onChangedEventSubscribe(
			(event) => {
				const data = event.getData();
				this.#addressWidget.setAddressByFeature(data.address, this);
			});
	}

	showMap(useUserLocation: boolean = false): void
	{
		if(!this.#map.isShown())
		{
			this.#map.show(useUserLocation);
		}
	}

	closeMap(): void
	{
		if(this.#map.isShown())
		{
			this.#map.close();
		}

		this.#map.bindelement = this.#mapBindElement;
	}

	resetView(): void
	{
		this.closeMap();
	}

	/**
	 * Render Widget
	 * @param {Object} props
	 */
	render(props: {}): void
	{
		if(!Type.isDomNode(props.mapBindElement))
		{
			BX.debug('props.mapBindElement  must be instance of Element');
		}

		this.#mapBindElement = props.mapBindElement;

		this.#map.render({
			bindElement: props.mapBindElement,
			address: this.#addressWidget.address,
			mode: this.#addressWidget.mode,
		});
	}

	setAddress(address: ?Address): void
	{
		if(this.addressWidget.state === State.DATA_INPUTTING)
		{
			return;
		}

		this.#map.address = address;
	}

	setAddressWidget(addressWidget): void
	{
		this.#addressWidget = addressWidget;
	}

	setMode(mode: string): void
	{
		this.#map.mode = mode;
	}

	destroy(): void
	{
		this.#map.destroy();
		this.#map = null;
	}

	get map(): MapPopup
	{
		return this.#map;
	}

	get addressWidget(): Address
	{
		return this.#addressWidget;
	}

	get mapBindElement(): Element
	{
		return this.#mapBindElement;
	}
}