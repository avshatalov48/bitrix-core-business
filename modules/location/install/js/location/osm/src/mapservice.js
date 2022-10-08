import {Event, Tag} from 'main.core';
import {Location, MapBase, ControlMode, Point, ErrorPublisher, LocationType} from 'location.core';
import {Leaflet} from '../leaflet/src/leaflet';
import OSM from './osm';
import './css/mapservice.css';

/**
 * Class for the autocomplete locations and addresses inputs
 */
export default class MapService extends MapBase
{
	static #onChangedEvent = 'onChanged';

	/** {number} */
	#zoom;
	/** {ControlMode} */
	#mode;
	/** {?Location} */
	#location;
	/** {String} */
	#languageId;
	/** {String} */
	#sourceLanguageId;
	/** {Leaflet.map} */
	#map;
	/** {Leaflet.marker} */
	#marker;
	/** {GeocodingService} */
	#geocodingService;

	#isUpdating = false;
	#timerId;

	#changeDelay = 700;
	#tileUrlTemplate;
	#attribution = '<a href="https://leafletjs.com" title="A JS library for interactive maps" target="_blank">Leaflet</a> | Map data &copy; <a href="https://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors';

	#mapFactoryMethod;
	#markerFactoryMethod;
	#tileLayerFactoryMethod;
	#locationRepository;
	#isResizeInvalidated = false;

	constructor(props)
	{
		super(props);

		this.#languageId = props.languageId;
		this.#sourceLanguageId = props.sourceLanguageId;
		this.#geocodingService = props.geocodingService;
		this.#mapFactoryMethod = props.mapFactoryMethod;
		this.#markerFactoryMethod = props.markerFactoryMethod;
		this.#tileLayerFactoryMethod = props.tileLayerFactoryMethod;
		this.#tileUrlTemplate = `${props.mapServiceUrl}/hot/en/{z}/{x}/{y}.png`;
		this.#locationRepository = props.locationRepository;
	}

	set mode(mode: string): void
	{
		this.#mode = mode;

		if (this.#marker)
		{
			this.#marker.draggable = mode === ControlMode.edit;
		}
	}

	get map()
	{
		return this.#map;
	}

	set map(map)
	{
		this.#map = map;
	}

	get mode()
	{
		return this.#mode;
	}

	get marker()
	{
		return this.#marker;
	}

	set marker(marker)
	{
		this.#marker = marker;
	}

	get zoom(): number
	{
		return this.#zoom;
	}

	set zoom(zoom: number): void
	{
		this.#zoom = zoom;

		if (this.#map)
		{
			this.#map.setZoom(zoom);
		}
	}

	set location(location: ?Location): void
	{
		this.#location = location;

		if (location)
		{
			if (this.#marker)
			{
				this.#isUpdating = true;
				this.#marker.setLatLng([location.latitude, location.longitude]);
				this.#isUpdating = false;
			}

			if (this.#map)
			{
				if (!this.#map.hasLayer(this.#marker))
				{
					this.#marker.addTo(this.#map);
				}

				this.#map.panTo([location.latitude, location.longitude]);
			}
		}
		else if (this.#marker)
		{
			this.#marker.remove();
		}

		this.#adjustZoom();
	}

	#adjustZoom(): void
	{
		if (!this.#location)
		{
			return;
		}

		const zoom = MapService.#chooseZoomByLocation(this.#location);

		if (zoom !== null && zoom !== this.#zoom)
		{
			this.zoom = zoom;
		}
	}

	// todo: Location types
	static #chooseZoomByLocation(location: ?Location): number
	{
		let result = 18;

		if (location)
		{
			let locationType = location.type;

			if (locationType > LocationType.UNKNOWN)
			{
				if (locationType < LocationType.COUNTRY)
					result = 1;
				else if (locationType === LocationType.COUNTRY)
					result = 4;
				else if (locationType <= LocationType.ADM_LEVEL_1)
					result = 6;
				else if (locationType <= LocationType.LOCALITY)
					result = 11;
				else if (locationType <= LocationType.STREET)
					result = 16;
				else if (locationType > LocationType.STREET)
					result = 18;
			}
		}

		return result;
	}

	get location(): Location
	{
		return this.#location;
	}

	onLocationChangedEventSubscribe(listener: function): void
	{
		this.subscribe(MapService.#onChangedEvent, listener);
	}

	#onMapClick(lat: string, lng: string): void
	{
		if (this.#mode === ControlMode.edit)
		{
			if (!this.#map.hasLayer(this.#marker))
			{
				this.#marker.addTo(this.#map);
			}

			this.#marker.setLatLng([lat, lng]);
			this.#createTimer(lat, lng);
		}
	}

	#createTimer(lat: string, lng: string): void
	{
		if (this.#timerId !== null)
		{
			clearTimeout(this.#timerId);
		}

		this.#timerId = setTimeout(() => {
				this.#timerId = null;
				this.#map.panTo([lat, lng]);
				const point = new Point(lat, lng);

				this.#geocodingService.reverse(point, this.#getReverseZoom())
					.then(
						(location) => {
							let result;

							if (location)
							{
								result = this.#locationRepository.findByExternalId(
									location.externalId,
									OSM.code,
									this.#languageId
								).then((foundLocation) => {
									/**
									 * Use marker coordinates
									 */
									if (foundLocation)
									{
										foundLocation.longitude = point.longitude;
										if (foundLocation.address)
										{
											foundLocation.address.longitude = point.longitude;
										}

										foundLocation.latitude = point.latitude;
										if (foundLocation.address)
										{
											foundLocation.address.latitude = point.latitude;
										}
									}

									return foundLocation;
								});
							}
							else
							{
								result = new Promise((resolve) => {
									resolve(null);
								});
							}

							return result;
						}
					)
					.then(
						this.#emitOnLocationChangedEvent.bind(this)
					)
					.catch((response) => {
						ErrorPublisher.getInstance().notify(response.errors);
					});
			},
			this.#changeDelay
		);
	}

	#getReverseZoom()
	{
		return this.#zoom >= 15 ? 18 : this.#zoom;
	}

	#emitOnLocationChangedEvent(location: ?Location): void
	{
		if (this.#mode === ControlMode.edit)
		{
			this.emit(MapService.#onChangedEvent, { location: location	});
		}
	}

	#onMarkerUpdatePosition(lat: string, lng: string): void
	{
		if (!this.#isUpdating && this.#mode === ControlMode.edit)
		{
			this.#createTimer(lat, lng);
		}
	}

	render(props): Promise
	{
		this.#mode = props.mode;
		this.#location = props.location || null;
		const container = Tag.render`<div class="location-osm-map-container"></div>`;
		props.mapContainer.appendChild(container);

		return new Promise((resolve) =>
		{
			this.#map = this.#mapFactoryMethod(container, {
				attributionControl: false
			});

			this.#map.on('load', () =>
			{
				resolve();
			});

			this.#map.on('click', (e) =>
			{
				this.#onMapClick(e.latlng.lat, e.latlng.lng);
			});

			window.addEventListener('resize', (event) => {
				this.#isResizeInvalidated = true;
				this.#invalidateMapSize();
			});

			this.#marker = this.#markerFactoryMethod(
				[this.#location.latitude, this.#location.longitude],
				{
					draggable: this.#mode === ControlMode.edit,
					autoPan: true
				}
			);

			this.#marker.addTo(this.#map);

			this.#marker.on('move', (e) =>
			{
				this.#onMarkerUpdatePosition(e.latlng.lat, e.latlng.lng);
			});

			this.#map.setView(
				[this.#location.latitude, this.#location.longitude],
				MapService.#chooseZoomByLocation(this.#location)
			);

			const tile = this.#tileLayerFactoryMethod.call();

			tile.initialize(this.#tileUrlTemplate, {
				maxZoom: 18,
			});

			tile.addTo(this.#map);
			this.#map.on('zoomend', () => {
				this.#zoom = this.#map.getZoom();
			});

			const attribution = new Leaflet.Control.Attribution();
			attribution.setPrefix('');
			attribution.addAttribution(this.#attribution);
			this.#map.addControl(attribution);
		});
	}

	#invalidateMapSize()
	{
		setTimeout(() => {
			this.#map.invalidateSize();
		}, 10);
	}

	onMapShow()
	{
		if (this.#isResizeInvalidated)
		{
			this.#isResizeInvalidated = false;
			this.#invalidateMapSize();
		}
	}

	destroy()
	{
		Event.unbindAll(this);
		this.#map.remove();
		this.#marker.remove();
		super.destroy();
	}
}