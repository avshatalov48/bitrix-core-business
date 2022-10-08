import {
	Tag,
	Type,
	Dom,
	Event,
	Loc
} from 'main.core';
import {
	Address,
	Format,
	Location,
	ControlMode,
	MapBase,
	GeocodingServiceBase,
	AddressStringConverter,
	LocationType
} from 'location.core';
import {EventEmitter} from 'main.core.events';
import AddressString from './addressstring';
import AddressApplier from './addressapplier';
import Popup from './popup';

import 'ui.design-tokens';
import './css/mappopup.css';

export default class MapPopup extends EventEmitter
{
	static #onChangedEvent = 'onChanged';
	static #onMouseOverEvent = 'onMouseOver';
	static #onMouseOutEvent = 'onMouseOut';
	static #onShowedEvent = 'onShow';
	static #onClosedEvent = 'onClose';

	#map;
	#mode;
	#address;
	#popup;
	#addressString;
	#addressApplier;
	#addressFormat;
	#gallery;
	#locationRepository;
	#isMapRendered = false;
	#mapInnerContainer;
	#geocodingService;
	#contentWrapper;
	#userLocationPoint;

	constructor(props)
	{
		super(props);
		this.setEventNamespace('BX.Location.Widget.MapPopup');

		if (!(props.map instanceof MapBase))
		{
			BX.debug('map must be instance of Map');
		}

		this.#map = props.map;

		if (props.geocodingService instanceof GeocodingServiceBase)
		{
			this.#geocodingService = props.geocodingService;
		}

		this.#map.onLocationChangedEventSubscribe(this.#onLocationChanged.bind(this));

		if (!(props.popup instanceof Popup))
		{
			BX.debug('popup must be instance of Popup');
		}

		this.#popup = props.popup;

		if (!(props.addressFormat instanceof Format))
		{
			BX.debug('addressFormat must be instance of Format');
		}

		this.#addressFormat = props.addressFormat;

		this.#addressString = new AddressString({
			addressFormat: this.#addressFormat
		});
		this.#createAddressApplier();

		if (props.gallery)
		{
			this.#gallery = props.gallery;
		}

		this.#locationRepository = props.locationRepository;
		this.#userLocationPoint = props.userLocationPoint;
	}

	#createAddressApplier()
	{
		this.#addressApplier = new AddressApplier(
			{
				propsData: {
					address: this.#address,
					addressFormat: this.#addressFormat,
					isHidden: true,
				}
			}
		);
		this.#addressApplier.$mount();
		this.#addressApplier.$on('apply', (event) => {
			const prevAddress = event.address;

			this.#address = prevAddress;
			this.#addressString.address = prevAddress;
			this.#addressApplier.$props.isHidden = true;

			this.emit(
				MapPopup.#onChangedEvent,
				{address: prevAddress}
			);
		});
	}

	#onLocationChanged(event: Event)
	{
		const data = event.getData();
		const location = data.location;
		const address = location.toAddress();

		if (!this.#address)
		{
			this.#address = address;
			this.#addressString.address = address;
			this.emit(
				MapPopup.#onChangedEvent,
				{address: address}
			);
		}
		else if (address.fieldCollection.isEqual(this.#address.fieldCollection, LocationType.ADDRESS_LINE_1))
		{
			this.#address.latitude = address.latitude;
			this.#address.longitude = address.longitude;

			if (this.#address.location)
			{
				this.#address.location.latitude = address.latitude;
				this.#address.location.longitude = address.longitude;
			}

			this.emit(
				MapPopup.#onChangedEvent,
				{address: this.#address}
			);

			this.#addressApplier.$props.isHidden = true;
		}
		else
		{
			this.#addressString.address = address;
			this.#addressApplier.$props.address = address;
			this.#addressApplier.$props.isHidden = false;
		}

		if (this.#gallery)
		{
			this.#gallery.location = location;
		}
	}

	render(props: object): void
	{
		this.#address = props.address;
		this.#mode = props.mode;
		this.#isMapRendered = false;
		this.#mapInnerContainer = Tag.render`<div class="location-map-inner"></div>`;
		this.#renderPopup(props.bindElement, this.#mapInnerContainer);
	}

	#renderPopup(bindElement: Element, mapInnerContainer: Element): Popup
	{
		let gallery = '';

		if (this.#gallery)
		{
			gallery = this.#gallery.render();
		}

		const thirdPartyWarningNode = Tag.render`
			<div class="location-map-address-third-party-warning">
				${Loc.getMessage('LOCATION_WIDGET_THIRD_PARTY_WARNING')}
			</div>
		`;

		this.#contentWrapper = Tag.render`
			<div class="location-map-wrapper">
				<div class="location-map-container">
					${mapInnerContainer}
					${gallery}
				</div>
				${this.#mode === ControlMode.edit ? this.#addressString.render({address: this.#address}) : ''}
				${thirdPartyWarningNode}
				${this.#mode === ControlMode.edit ? this.#addressApplier.$el : ''}
			</div>`;

		Event.bind(this.#contentWrapper, 'click', (e) => e.stopPropagation());
		Event.bind(this.#contentWrapper, 'mouseover', (e) => this.emit(MapPopup.#onMouseOverEvent, e));
		Event.bind(this.#contentWrapper, 'mouseout', (e) => this.emit(MapPopup.#onMouseOutEvent, e));
		this.bindElement = bindElement;
		this.#popup.setContent(this.#contentWrapper);
	}

	get bindElement()
	{
		return this.#popup.getBindElement();
	}

	set bindElement(bindElement: Element)
	{
		if (Type.isDomNode(bindElement))
		{
			this.#popup.setBindElement(bindElement);
		}
		else
		{
			BX.debug('bindElement must be type of dom node');
		}
	}

	set address(address: ?Address): void
	{
		this.#address = address;
		this.#addressString.address = address;

		this.#convertAddressToLocation(address)
			.then((location) => {
				this.#setLocationInternal(location);
			});
	}

	#extractLatLon(address: Address): ?Array
	{
		let result = null;
		let lat;
		let lon;

		if (address.latitude && address.longitude)
		{
			lat = address.latitude;
			lon = address.longitude;
		}
		else if (address.location
			&& address.location.latitude
			&& address.location.longitude
		)
		{
			lat = address.location.latitude;
			lon = address.location.longitude;
		}

		if (lat && lat !== '0' && lon && lon !== '0')
		{
			result = [lat, lon];
		}

		return result;
	}

	#convertAddressToLocation(address: ?Address, useUserLocation: boolean = false): Promise<?Location>
	{
		return new Promise((resolve) => {
			if (useUserLocation)
			{
				resolve(
					this.#userLocationPoint && this.#mode !== ControlMode.view
						? new Location({
							latitude: this.#userLocationPoint.latitude,
							longitude: this.#userLocationPoint.longitude
						})
						: null
				);
				return;
			}

			if (address)
			{
				const latLon = this.#extractLatLon(address);

				if (latLon)
				{
					resolve(new Location({
						latitude: latLon[0],
						longitude: latLon[1],
						type: address.getType()
					}));
					return;
				}
			}

			resolve(null);
		});
	}

	#setLocationInternal(location: ?Location): void
	{
		if (this.#map)
		{
			this.#map.location = location;
		}

		if (this.#gallery)
		{
			this.#gallery.location = location;
		}
	}

	set mode(mode: string): void
	{
		this.#mode = mode;
		this.#map.mode = mode;
	}

	#renderMap({location})
	{
		return this.#map.render({
			mapContainer: this.#mapInnerContainer,
			location: location,
			mode: this.#mode
		});
	}

	show(useUserLocation: boolean = false): void
	{
		this.#convertAddressToLocation(this.#address, useUserLocation)
			.then((location) => {
				if (!location)
				{
					return;
				}

				this.#popup.show();

				if (!this.#isMapRendered)
				{
					this.#renderMap({location})
						.then(() => {
							if (this.#gallery)
							{
								this.#gallery.location = location;
							}
							this.emit(MapPopup.#onShowedEvent);
							this.#map.onMapShow();
						});

					this.#isMapRendered = true;
				}
				else
				{
					this.#map.location = location;

					if (this.#gallery)
					{
						this.#gallery.location = location;
					}

					this.emit(MapPopup.#onShowedEvent);
					this.#map.onMapShow();
				}
			});
	}

	isShown(): boolean
	{
		return this.#popup.isShown();
	}

	close(): void
	{
		this.#popup.close();
		this.#addressApplier.$props.isHidden = true;
		this.emit(MapPopup.#onClosedEvent);
	}

	onChangedEventSubscribe(listener: Function): void
	{
		this.subscribe(MapPopup.#onChangedEvent, listener);
	}

	onMouseOverSubscribe(listener: Function): void
	{
		this.subscribe(MapPopup.#onMouseOverEvent, listener);
	}

	onMouseOutSubscribe(listener: Function): void
	{
		this.subscribe(MapPopup.#onMouseOutEvent, listener);
	}

	subscribeOnShowedEvent(listener: Function): void
	{
		this.subscribe(MapPopup.#onShowedEvent, listener);
	}

	subscribeOnClosedEvent(listener: Function): void
	{
		this.subscribe(MapPopup.#onClosedEvent, listener);
	}

	destroy()
	{
		this.#map = null;
		this.#gallery = null;
		this.#addressString = null;
		this.#addressApplier = null;

		this.#popup.destroy();
		this.#popup = null;
		Dom.remove(this.#contentWrapper);
		this.#contentWrapper = null;
		Event.unbindAll(this);
	}
}
