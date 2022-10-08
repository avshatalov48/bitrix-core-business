import {Event} from 'main.core';
import {Location, LocationRepository, ControlMode, MapBase, ErrorPublisher} from 'location.core';

/**
 * Class for the autocomplete locations and addresses inputs
 */
export default class Map extends MapBase
{
	static #onChangedEvent = 'onChanged';

	/** {string} */
	#languageId;
	/** {google.maps.Map} */
	#googleMap;
	/** {GoogleSource} */
	#googleSource;
	/** {number} */
	#zoom;
	/** {google.maps.Marker} */
	#locationMarker;
	/** {ControlMode} */
	#mode;
	/** Location */
	#location;
	#geocoder;
	#locationRepository;
	#timerId = null;
	#isUpdating = false;
	#changeDelay;
	#loaderPromise = null;

	constructor(props)
	{
		super(props);
		this.#languageId = props.languageId;
		this.#googleSource = props.googleSource;
		this.#locationRepository = props.locationRepository || new LocationRepository();
		this.#changeDelay = props.changeDelay || 700;
	}

	render(props: object)
	{
		this.#loaderPromise = this.#googleSource.loaderPromise.then(() => {
			this.#initGoogleMap(props);
		});

		return this.#loaderPromise;
	}

	get loaderPromise()
	{
		return this.#loaderPromise;
	}

	set mode(mode: string)
	{
		this.#mode = mode;

		if(this.#locationMarker)
		{
			this.#locationMarker.setDraggable(mode === ControlMode.edit);
		}
	}

	#convertLocationToPosition(location: ?Location): Object
	{
		if(!location)
		{
			return null;
		}

		if(typeof google === 'undefined' || typeof google.maps === 'undefined')
		{
			return null;
		}

		return new google.maps.LatLng(location.latitude, location.longitude);
	}

	#adjustZoom(): void
	{
		if(!this.#location)
		{
			return;
		}

		let zoom = Map.#chooseZoomByLocation(this.#location);

		if(zoom !== null && zoom !== this.#zoom)
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

		if(this.#googleMap)
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
				else if(status === 'ZERO_RESULTS')
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

			if(placeId)
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
		let position = this.#convertLocationToPosition(location);

		if(position)
		{
			if(this.#locationMarker)
			{
				this.#isUpdating = true;
				this.#locationMarker.setPosition(position);
				this.#isUpdating = false;
			}

			if(this.#googleMap)
			{
				if(!this.#locationMarker.getMap())
				{
					this.#locationMarker.setMap(this.#googleMap);
				}

				this.#googleMap.panTo(position);
			}
		}
		else
		{
			if(this.#locationMarker)
			{
				this.#locationMarker.setMap(null);
			}
		}

		this.#adjustZoom();
	}

	get location(): Location
	{
		return this.#location;
	}

	onLocationChangedEventSubscribe(listener: function): void
	{
		this.subscribe(Map.#onChangedEvent, listener);
	}

	#emitOnLocationChangedEvent(location: ?Location)
	{
		if(this.#mode === ControlMode.edit)
		{
			this.emit(Map.#onChangedEvent, { location: location	});
		}
	}

	#onMarkerUpdatePosition()
	{
		if(!this.#isUpdating && this.#mode === ControlMode.edit)
		{
			this.#createTimer(this.#locationMarker.getPosition());
		}
	}

	#createTimer(position)
	{
		if(this.#timerId !== null)
		{
			clearTimeout(this.#timerId);
		}

		this.#timerId = setTimeout(() => {
				this.#timerId = null;
				this.#googleMap.panTo(position);
				this.#fulfillOnChangedEvent(position);
			},
			this.#changeDelay
		);
	}

	#fulfillOnChangedEvent(position)
	{
		this.#getPositionToLocationPromise(position)
		.then(this.#emitOnLocationChangedEvent.bind(this))
		.catch((response) => {
			ErrorPublisher.getInstance().notify(response.errors);
		});
	}

	#onMapClick(position)
	{
		if(this.#mode === ControlMode.edit)
		{
			if(!this.#locationMarker.getMap)
			{
				this.#locationMarker.setMap(this.#googleMap);
			}

			this.#locationMarker.setPosition(position);
			this.#createTimer(position);
		}
	}

	#initGoogleMap(props): void
	{
		this.#mode = props.mode;
		this.#location = props.location || null;

		if(typeof google === 'undefined' || typeof google.maps.Map === 'undefined')
		{
			throw new Error('google.maps.Map must be defined');
		}

		let position = this.#convertLocationToPosition(this.#location);

		let mapProps = {
			gestureHandling: 'greedy',
			disableDefaultUI: true,
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.TOP_LEFT
			}
		};

		let zoom = Map.#chooseZoomByLocation(this.#location);

		if(zoom)
		{
			mapProps.zoom = zoom;
		}

		if(position)
		{
			mapProps.center = position;
		}

		this.#googleMap = new google.maps.Map(
			props.mapContainer,
			mapProps
		);

		this.#googleMap.addListener('click', (e) => {
			this.#onMapClick(e.latLng);
		});

		if(typeof google.maps.Marker === 'undefined')
		{
			throw new Error('google.maps.Marker must be defined');
		}

		this.#locationMarker = new google.maps.Marker({
			position: position,
			map: this.#googleMap,
			draggable: this.#mode === ControlMode.edit
		});

		this.#locationMarker.addListener('position_changed', () => {
			this.#onMarkerUpdatePosition();
		});

		if(typeof google.maps.Geocoder === 'undefined')
		{
			throw new Error('google.maps.Geocoder must be defined');
		}

		this.#geocoder = new google.maps.Geocoder;
	}

	static #chooseZoomByLocation(location: ?Location): number
	{
		let result = 18;

		if(location)
		{
			let locationType = location.type;

			if(locationType > 0)
			{
				if(locationType < 100)
					result = 1;
				else if(locationType === 100)
					result = 4;
				else if(locationType <= 200)
					result = 6;
				else if(locationType <= 300)
					result = 11;
				else if(locationType <= 340)
					result = 16;
				else if(locationType > 340)
					result = 18;
			}
		}

		return result;
	}

	get googleMap()
	{
		return this.#googleMap;
	}

	destroy()
	{
		Event.unbindAll(this);
		this.#googleMap = null;
		this.#locationMarker = null;
		this.#geocoder = null;
		this.#timerId = null;
		this.#loaderPromise = null;
		super.destroy();
	}
}