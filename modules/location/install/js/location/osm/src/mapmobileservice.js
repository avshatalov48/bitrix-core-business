import {
	Dom,
	Event,
	Tag,
	Text
} from 'main.core';
import {
	Location,
	MapBase,
	ControlMode,
	Point,
	ErrorPublisher,
} from 'location.core';
import { Leaflet } from '../leaflet/src/leaflet';
import OSM from './osm';

import './css/mapmobileservice.css';

export default class MapMobileService extends MapBase
{
	static #onChangedEvent = 'onChanged';
	static #onStartChanging = 'onStartChanging';
	static #onEndChanging = 'onEndChanging';
	static #onMapViewChanged = 'onMapViewChanged';

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
	#markerNode;
	/** {GeocodingService} */
	#geocodingService;

	#timerId;

	#changeDelay = 700;
	#tileUrlTemplate;
	#attribution = '<a href="https://leafletjs.com" title="A JS library for interactive maps" target="_blank">Leaflet</a> | Map data &copy; <a href="https://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors';

	#mapFactoryMethod;
	#markerFactoryMethod;
	#iconFactoryMethod
	#tileLayerFactoryMethod;
	#locationRepository;
	#isMapChanging = false;

	constructor(props)
	{
		super(props);

		this.#languageId = props.languageId;
		this.#sourceLanguageId = props.sourceLanguageId;
		this.#geocodingService = props.geocodingService;
		this.#mapFactoryMethod = props.mapFactoryMethod;
		this.#markerFactoryMethod = props.markerFactoryMethod;
		this.#iconFactoryMethod = props.iconFactoryMethod;
		this.#tileLayerFactoryMethod = props.tileLayerFactoryMethod;
		this.#tileUrlTemplate = `${props.mapServiceUrl}/hot/en/{z}/{x}/{y}.png`;
		this.#locationRepository = props.locationRepository;
	}

	set mode(mode: string): void
	{
		this.#mode = mode;
	}

	get map(): ?Object
	{
		return this.#map;
	}

	set map(map)
	{
		this.#map = map;
	}

	get mode(): string
	{
		return this.#mode;
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
			this.panTo(location.latitude, location.longitude);
		}

		this.#adjustZoom();
	}

	panTo(latitude: string, longitude: string): void
	{
		if (this.#map)
		{
			this.#map.panTo([latitude, longitude]);
		}
	}

	#adjustZoom(): void
	{
		if (!this.#location)
		{
			return;
		}

		const zoom = MapMobileService.getZoomByLocation(this.#location);
		if (zoom !== null && zoom !== this.#zoom)
		{
			this.zoom = zoom;
		}
	}

	get location(): Location
	{
		return this.#location;
	}

	onLocationChangedEventSubscribe(listener: function): void
	{
		this.subscribe(MapMobileService.#onChangedEvent, listener);
	}

	onStartChangingSubscribe(listener: function): void
	{
		this.subscribe(MapMobileService.#onStartChanging, listener);
	}

	onEndChangingSubscribe(listener: function): void
	{
		this.subscribe(MapMobileService.#onEndChanging, listener);
	}

	onMapViewChangedSubscribe(listener: function): void
	{
		this.subscribe(MapMobileService.#onMapViewChanged, listener);
	}

	#onMove()
	{
		if (this.#timerId !== null)
		{
			clearTimeout(this.#timerId);
		}
	}

	#onMoveStart()
	{
		if (this.#mode === ControlMode.edit)
		{
			this.#isMapChanging = true;

			Dom.addClass(this.#markerNode, 'location-map-mobile-center-marker-up');
		}

		this.emit(MapMobileService.#onMapViewChanged);
	}

	#onMoveEnd(): void
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

			const center = this.#map.getCenter();
			this.#createTimer(center.lat, center.lng);
			this.#isMapChanging = false;
		}
	}

	#onZoomStart()
	{
		this.emit(MapMobileService.#onMapViewChanged);
	}

	#onZoomEnd()
	{
		this.#zoom = this.#map.getZoom();
	}

	#createTimer(lat: string, lng: string): void
	{
		if (this.#timerId !== null)
		{
			clearTimeout(this.#timerId);
		}

		this.#timerId = setTimeout(() => {
			const requestId = Text.getRandom();
			this.emit(MapMobileService.#onStartChanging, { requestId });
			this.#timerId = null;
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
				.then((location) => {
					this.emit(MapMobileService.#onEndChanging, { requestId });
					this.#emitOnLocationChangedEvent(location);
				})
				.catch((response) => {
					this.emit(MapMobileService.#onEndChanging, { requestId });
					ErrorPublisher.getInstance().notify(response.errors);
				});
			},
			this.#changeDelay
		);
	}

	#getReverseZoom(): number
	{
		return this.#zoom >= 15 ? 18 : this.#zoom;
	}

	#emitOnLocationChangedEvent(location: ?Location): void
	{
		if (this.#mode === ControlMode.edit)
		{
			this.emit(MapMobileService.#onChangedEvent, { location: location	});
		}
	}

	render(props: Object): Promise
	{
		this.#mode = props.mode;
		this.#location = props.location || null;
		const container = Tag.render`<div class="location-osm-map-mobile-container"></div>`;
		props.mapContainer.appendChild(container);

		return new Promise((resolve) =>
		{
			this.#map = this.#mapFactoryMethod(container, {
				attributionControl: false,
				zoomControl: BX.prop.getBoolean(props, 'zoomControl', true),
			});

			this.#map.on('load', () =>
			{
				resolve();
			});

			if (this.#mode === ControlMode.edit)
			{
				this.#markerNode = Tag.render`<div class="location-map-mobile-center-marker"></div>`;
				container.appendChild(this.#markerNode);
			}
			else
			{
				this.#marker = this.#markerFactoryMethod(
					[this.#location.latitude, this.#location.longitude],
					{
						icon: this.#iconFactoryMethod({
							iconUrl: '/bitrix/js/location/css/image/marker.png',
							iconSize: [26, 37],
							iconAnchor: [13, 37],
						}),
					}
				);
				this.#marker.addTo(this.#map);
			}

			this.#map.setView(
				[this.#location.latitude, this.#location.longitude],
				MapMobileService.getZoomByLocation(this.#location)
			);

			const tile = this.#tileLayerFactoryMethod.call();

			tile.initialize(this.#tileUrlTemplate, {
				/**
				 * In order to avoid blurry tiles on retina screens, we need to apply the below options:
				 * detectRetina: true,
				 * maxNativeZoom: 22,
				 * maxZoom: 18,
				 *
				 * but we can't do it right now because of the following bug in the leaflet library:
				 * https://github.com/Leaflet/Leaflet/issues/8850
				 * which causes fetching non-existent tiles (19, 20, etc. zoom levels)
				 */
				maxZoom: 18,
			});

			tile.addTo(this.#map);
			this.#map.on('zoomstart', (e) => this.#onZoomStart());
			this.#map.on('zoomend', (e) => this.#onZoomEnd());

			const attribution = new Leaflet.Control.Attribution();
			attribution.setPrefix('');
			attribution.addAttribution(this.#attribution);
			this.#map.addControl(attribution);

			this.#map.on('move', (e) => this.#onMove());
			this.#map.on('movestart', (e) => this.#onMoveStart());
			this.#map.on('moveend', (e) => this.#onMoveEnd());

			if (props.searchOnRender)
			{
				const center = this.#map.getCenter();
				this.#createTimer(center.lat, center.lng);
			}
		});
	}

	destroy()
	{
		Event.unbindAll(this);
		this.#map.remove();
		super.destroy();
	}
}
