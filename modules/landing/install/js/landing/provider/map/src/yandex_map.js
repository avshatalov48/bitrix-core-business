import {Text, Dom, Runtime} from 'main.core';
import {BaseProvider} from './base_provider';

export class YandexMap extends BaseProvider
{
	constructor(options: {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.Provider.Map.YandexMap');
		this.code = 'yandex';
	}

	/**
	 * @inheritDoc
	 */
	init()
	{
		this.preventChangeEvent = true;

		const controls = ['zoomControl', 'fullscreenControl', 'typeSelector', 'routeButtonControl'];
		if (this.options.fullscreenControl === false)
		{
			controls.splice(controls.indexOf('fullscreenControl'), 1);
		}
		if (this.options.mapTypeControl === false)
		{
			controls.splice(controls.indexOf('typeSelector'), 1);
			controls.splice(controls.indexOf('routeButtonControl'), 1);
		}

		this.mapInstance = new ymaps.Map(this.mapContainer, {
			center: this.convertPointIn(this.mapOptions.center),
			zoom: this.mapOptions.zoom,
			behaviors: this.options.zoomControl === false ? ['drag'] : ['default'],
			controls: controls,
		});

		this.mapInstance.events.add('actionend', this.onChange);
		this.mapInstance.events.add('click', event =>
		{
			this.cache.delete('value');
			this.onMapClickHandler(event);
			if (BX.Landing.getMode() === "edit")
			{
				this.markers[this.markers.length - 1].marker.balloon.open();
			}
		});

		if (this.mapOptions.markers)
		{
			this.mapOptions.markers.forEach(markerItem => {
				markerItem.editable = BX.Landing.getMode() === "edit";
				markerItem.draggable = BX.Landing.getMode() === "edit";
				this.addMarker(markerItem);
			});
		}

		super.init();
	}

	reinit(options: {})
	{
		// Yandex has't changes yet. If some settings will be added later - need implement reinit
		this.preventChangeEvent = true;
		super.reinit();
	}

	/**
	 * Check is provider API was loaded
	 * @return {boolean}
	 */
	isApiLoaded()
	{
		return (
			typeof ymaps !== "undefined"
			&& typeof ymaps.Map !== "undefined"
		);
	}

	/**
	 * Convert point from Google format to Yandex
	 * @param point
	 * @return {[number,number]}
	 */
	convertPointIn(point: {lat: number, lng: number}): [number, number]
	{
		return [point.lat, point.lng];
	}

	/**
	 * Convert point from Yandex for export
	 * @param point
	 * @return {{lng: number, lat: number}}
	 */
	convertPointOut(point: [number, number]): {lat: number, lng: number}
	{
		return {lat: point[0], lng: point[1]};
	}

	/**
	 * Extract coords from map events (f.e. click)
	 * @param event
	 */
	getPointByEvent(event): {lat: number, lng: number}
	{
		return this.convertPointOut(event.get('coords'));
	}

	/**
	 * Set api load handle function
	 * @abstract
	 */
	handleApiLoad()
	{
		window.onYandexMapApiLoaded = () =>
		{
			this.onApiLoadedHandler(this.getCode());
		};
	}

	/**
	 * @inheritDoc
	 * @param options
	 */
	addMarker(options): void
	{
		const item = {};
		item.form = BX.Landing.getMode() === "edit" ? this.createBalloonEditForm(options, item) : null;
		item.content = this.createBalloonContent(options);
		item.ballonId = 'ballonContent_' + Text.getRandom('6');

		const isNoNeedBalloon = BX.Landing.getMode() !== "edit" && !options.title && !options.description;

		const balloonTemplate = ymaps.templateLayoutFactory.createClass(
			'<div id="' + item.ballonId + '"></div>',
			{
				build: function ()
				{
					balloonTemplate.superclass.build.call(this);
					const container = document.querySelector('#' + item.ballonId);
					const content = (options.editable && BX.Landing.getMode() === "edit")
						? item.form.layout
						: item.content;
					Dom.append(content, container);
				},
			},
		);
		item.marker = new ymaps.Placemark(
			this.convertPointIn(options.latLng),
			{},
			{
				balloonContentLayout: isNoNeedBalloon ? null: balloonTemplate,
				balloonPanelMaxMapArea: 0,
				draggable: options.draggable,
			},
		);
		this.mapInstance.geoObjects.add(item.marker);

		if (options.showByDefault)
		{
			item.marker.balloon.open();
		}

		this.markers.add(item);
		this.onChange();
	}

	onMarkerClick(item): void
	{
		// Yandex will do everything himself
	}

	onEditFormRemoveClick(event): void
	{
		if (event)
		{
			event.marker.balloon.close();
			this.removeMarker(event);
		}

		this.markers.remove(event);
		this.onChange();
	}

	onEditFormApplyClick(event): void
	{
		event.marker.balloon.close();
		this.onChange();
	}

	removeMarker(event): void
	{
		this.mapInstance.geoObjects.remove(event.marker);

	}

	clearMarkers(): void
	{
		this.markers.forEach(marker => {
			this.mapInstance.geoObjects.remove(marker.marker);
		});
		this.markers.clear();
	}

	setZoom(zoom: number): void
	{
		this.mapInstance.setZoom(zoom);
	}

	setCenter(center): void
	{
		this.mapInstance.setCenter(this.convertPointIn(center));
	}

	getMarkersValue(): {}
	{
		return this.markers.map((item) => {
			return {
				title: item.form ? item.form.fields[0].getValue() : "",
				description: item.form ? item.form.fields[1].getValue() : "",
				showByDefault: item.form ? !!item.form.fields[2].getValue()[0] : "",
				latLng: this.convertPointOut(item.marker.geometry.getCoordinates()),
			};
		});
	}

	getValue(): {}
	{
		return this.cache.remember('value', () =>
		{
			return {
				center: this.mapInstance.getCenter() ? this.convertPointOut(this.mapInstance.getCenter()) : {},
				zoom: this.mapInstance.getZoom(),
				markers: this.getMarkersValue(),
			};
		});
	}

	onChange()
	{
		this.cache.delete('value');
		super.onChange();
	}
}