import {
	Event,
	Text,
	Tag,
	Dom,
} from 'main.core';
import {
	Location,
	LocationRepository,
	ControlMode,
	MapBase,
	ErrorPublisher
} from 'location.core';

/**
 * Class for the autocomplete locations and addresses inputs
 */
export default class Map extends MapBase
{
	static #onChangedEvent = 'onChanged';
	static #onStartChanging = 'onStartChanging';
	static #onEndChanging = 'onEndChanging';
	static #onMapViewChanged = 'onMapViewChanged';

	/** {string} */
	#languageId;
	/** {google.maps.Map} */
	#googleMap;
	/** {GoogleSource} */
	#googleSource;
	#markerNode;
	/** {google.maps.Marker} */
	#locationMarker;

	/** {number} */
	#zoom;
	/** {ControlMode} */
	#mode;
	/** Location */
	#location;
	#geocoder;
	#locationRepository;
	#timerId = null;
	#changeDelay;
	#loaderPromise = null;
	#isMapChanging = false;

	constructor(props)
	{
		super(props);
		this.#languageId = props.languageId;
		this.#googleSource = props.googleSource;
		this.#locationRepository = props.locationRepository || new LocationRepository();
		this.#changeDelay = props.changeDelay || 700;
	}

	render(props: Object): Promise
	{
		this.#loaderPromise = this.#googleSource.loaderPromise.then(() => {
			this.#initGoogleMap(props);
		});

		return this.#loaderPromise;
	}

	get loaderPromise(): Promise
	{
		return this.#loaderPromise;
	}

	set mode(mode: string)
	{
		this.#mode = mode;
	}

	#convertLocationToPosition(location: ?Location): Object
	{
		if (!location)
		{
			return null;
		}

		if (typeof google === 'undefined' || typeof google.maps === 'undefined')
		{
			return null;
		}

		return new google.maps.LatLng(location.latitude, location.longitude);
	}

	#adjustZoom(): void
	{
		if (!this.#location)
		{
			return;
		}

		const zoom = Map.getZoomByLocation(this.#location);
		if (zoom !== null && zoom !== this.#zoom)
		{
			this.zoom = zoom;
		}
	}

	get zoom(): number
	{
		return this.#zoom;
	}

	set zoom(zoom: number): void
	{
		this.#zoom = zoom;

		if (this.#googleMap)
		{
			this.#googleMap.setZoom(zoom);
		}
	}

	#getPositionToLocationPromise(position): Promise
	{
		return new Promise( (resolve) => {
			this.#geocoder.geocode({'location': position}, (results, status)  => {
				if (status === 'OK' && results[0])
				{
					resolve(results[0].place_id);
				}
				else if (status === 'ZERO_RESULTS')
				{
					resolve('');
				}
				else
				{
					throw Error('Geocoder failed due to: ' + status);
				}
			});
		})
		.then((placeId) => {
			let result;

			if (placeId)
			{
				result = this.#locationRepository.findByExternalId(
					placeId,
					this.#googleSource.sourceCode,
					this.#languageId
				);
			}
			else
			{
				result = new Promise((resolve) => {
					resolve(null);
				});
			}

			return result;
		});
	}

	set location(location: Location)
	{
		this.#location = location;
		const position = this.#convertLocationToPosition(location);

		if (position && this.#googleMap)
		{
			this.#googleMap.panTo(position);
		}

		this.#adjustZoom();
	}

	panTo(latitude: string, longitude: string): void
	{
		if (
			typeof google !== 'undefined'
			&& typeof google.maps !== 'undefined'
			&& this.#googleMap
		)
		{
			this.#googleMap.panTo(new google.maps.LatLng(latitude, longitude));

			this.#adjustZoom();
		}
	}

	get location(): Location
	{
		return this.#location;
	}

	onLocationChangedEventSubscribe(listener: function): void
	{
		this.subscribe(Map.#onChangedEvent, listener);
	}

	onStartChangingSubscribe(listener: function): void
	{
		this.subscribe(Map.#onStartChanging, listener);
	}

	onEndChangingSubscribe(listener: function): void
	{
		this.subscribe(Map.#onEndChanging, listener);
	}

	onMapViewChangedSubscribe(listener: function): void
	{
		this.subscribe(Map.#onMapViewChanged, listener);
	}

	#emitOnLocationChangedEvent(location: ?Location)
	{
		if (this.#mode === ControlMode.edit)
		{
			this.emit(Map.#onChangedEvent, { location: location	});
		}
	}

	#createTimer()
	{
		if (this.#timerId !== null)
		{
			clearTimeout(this.#timerId);
		}

		this.#timerId = setTimeout(
			() => {
				const requestId = Text.getRandom();
				this.emit(Map.#onStartChanging, { requestId });

				this.#timerId = null;
				const position = this.#googleMap.getCenter();
				this.#fulfillOnChangedEvent(position, requestId);
			},
			this.#changeDelay
		);
	}

	#fulfillOnChangedEvent(position, requestId)
	{
		this.#getPositionToLocationPromise(position)
			.then((location) => {
				this.emit(Map.#onEndChanging, { requestId });
				this.#emitOnLocationChangedEvent(location);
			})
			.catch((response) => {
				this.emit(Map.#onEndChanging, { requestId });
				ErrorPublisher.getInstance().notify(response.errors);
			});
	}

	#onDrag()
	{
		if (this.#timerId !== null)
		{
			clearTimeout(this.#timerId);
		}
	}

	#onDragStart()
	{
		this.#onMapChanging();
		this.emit(Map.#onMapViewChanged);
	}

	#onZoomChanged()
	{
		this.#onMapChanging();
		this.emit(Map.#onMapViewChanged);
	}

	#onMapChanging()
	{
		if (this.#mode === ControlMode.edit)
		{
			this.#isMapChanging = true;

			Dom.addClass(this.#markerNode, 'location-map-mobile-center-marker-up');
		}
	}

	#onIdle()
	{
		if (this.#mode === ControlMode.edit)
		{
			if (this.#isMapChanging === false)
			{
				return;
			}

			const upClass = 'location-map-mobile-center-marker-up';
			if (Dom.hasClass(this.#markerNode, upClass))
			{
				Dom.removeClass(this.#markerNode, upClass);
			}

			this.#createTimer();
			this.#isMapChanging = false;
		}
	}

	#initGoogleMap(props): void
	{
		this.#mode = props.mode;
		this.#location = props.location || null;

		if (typeof google === 'undefined' || typeof google.maps.Map === 'undefined')
		{
			throw new Error('google.maps.Map must be defined');
		}

		const position = this.#convertLocationToPosition(this.#location);

		const mapProps = {
			gestureHandling: 'greedy',
			disableDefaultUI: true,
			zoomControl: BX.prop.getBoolean(props, 'zoomControl', true),
			zoomControlOptions: {
				position: google.maps.ControlPosition.TOP_LEFT
			}
		};

		const zoom = Map.getZoomByLocation(this.#location);
		if (zoom)
		{
			mapProps.zoom = zoom;
		}

		if (position)
		{
			mapProps.center = position;
		}

		this.#googleMap = new google.maps.Map(
			props.mapContainer,
			mapProps
		);
		if (this.#mode === ControlMode.edit)
		{
			this.#markerNode = Tag.render`<div class="location-map-mobile-center-marker"></div>`;
			this.#googleMap.getDiv().appendChild(this.#markerNode);
		}
		else
		{
			this.#locationMarker = new google.maps.Marker({
				position: position,
				map: this.#googleMap,
				draggable: false,
				icon: '/bitrix/js/location/css/image/marker.png',
			});
		}

		this.#googleMap.addListener('dragstart', () => this.#onDragStart());
		this.#googleMap.addListener('idle', () => this.#onIdle());
		this.#googleMap.addListener('drag', () => this.#onDrag());
		this.#googleMap.addListener('zoom_changed', () => this.#onZoomChanged());
		if (typeof google.maps.Geocoder === 'undefined')
		{
			throw new Error('google.maps.Geocoder must be defined');
		}

		this.#geocoder = new google.maps.Geocoder;

		if (props.searchOnRender)
		{
			this.#createTimer();
		}
	}

	get googleMap(): ?Object
	{
		return this.#googleMap;
	}

	destroy()
	{
		Event.unbindAll(this);
		this.#googleMap = null;
		this.#geocoder = null;
		this.#timerId = null;
		this.#loaderPromise = null;
		super.destroy();
	}
}
