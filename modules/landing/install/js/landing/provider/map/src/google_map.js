import {Type} from 'main.core';
import {BaseProvider} from './base_provider';
import {themes} from './google_map/theme/themes';
import {roads, landmarks, labels} from './google_map/settings';

export class GoogleMap extends BaseProvider
{
	constructor(options: {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.Provider.Map.GoogleMap');
		this.code = 'google';
		this.themes = themes;
	}

	/**
	 * Extract coords from map events (f.e. click)
	 * @param event
	 */
	getPointByEvent(event): {lat: number, lng: number}
	{
		const point = event.latLng;
		return this.isPoint(point) ? point : {};
	}

	/**
	 * @inheritDoc
	 */
	init()
	{
		this.preventChangeEvent = true;

		let opts = this.options;

		this.mapInstance = new google.maps.Map(this.mapContainer, {
			zoom: this.mapOptions.zoom,
			center: this.mapOptions.center,
			zoomControl: Type.isBoolean(opts.zoomControl) ? opts.zoomControl : true,
			mapTypeControl: Type.isBoolean(opts.mapTypeControl) ? opts.mapTypeControl : true,
			mapTypeControlOptions: Type.isPlainObject(opts.mapTypeControlOptions) ? opts.mapTypeControlOptions : null,
			scaleControl: Type.isBoolean(opts.scaleControl) ? opts.scaleControl : true,
			streetViewControl: Type.isBoolean(opts.streetViewControl) ? opts.streetViewControl : true,
			rotateControl: Type.isBoolean(opts.rotateControl) ? opts.rotateControl : true,
			fullscreenControl: Type.isBoolean(opts.fullscreenControl) ? opts.fullscreenControl : true,
			styles: this.getStylesFromOptions(opts),
		});

		if (this.mapOptions.markers)
		{
			this.mapOptions.markers.forEach(function (markerItem)
			{
				markerItem.editable = BX.Landing.getMode() === "edit";
				markerItem.draggable = BX.Landing.getMode() === "edit";
				this.addMarker(markerItem);
			}, this);
		}

		this.mapInstance.addListener("bounds_changed", this.onChange);
		this.mapInstance.addListener("center_changed", this.onChange);
		this.mapInstance.addListener("zoom_changed", this.onChange);
		this.mapInstance.addListener("click", this.onMapClickHandler);

		super.init();
	}

	reinit(options: {})
	{
		this.preventChangeEvent = true;
		if (this.mapInstance)
		{
			this.mapInstance.setOptions({
				styles: this.getStylesFromOptions(options),
			});
		}
		super.reinit();
	}

	getStylesFromOptions(options)
	{
		return (options.theme && options.theme in this.themes ? this.themes[options.theme] : [])
			.concat(roads[options.roads] || [], landmarks[options.landmarks] || [], labels[options.labels] || []);
	}

	/**
	 * Check is provider API was loaded
	 * @return {boolean}
	 */
	isApiLoaded()
	{
		return (typeof google !== "undefined");
	}

	/**
	 * Set api load handle function
	 * @abstract
	 */
	handleApiLoad()
	{
		window.onGoogleMapApiLoaded = () =>
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
		let item = {};
		item.marker = new google.maps.Marker({
			position: options.latLng,
			map: this.mapInstance,
			draggable: options.draggable,
		});

		item.form = BX.Landing.getMode() === "edit" ? this.createBalloonEditForm(options, item) : null;
		item.content = this.createBalloonContent(options);

		item.infoWindow = new google.maps.InfoWindow({
			content: options.editable && BX.Landing.getMode() === "edit" ? item.form.layout : item.content,
		});

		if (options.showByDefault && BX.Landing.getMode() !== "edit")
		{
			item.infoWindow.open(this.mapInstance, item.marker);
			this.activeMarker = item;
		}

		this.markers.add(item);

		// in editor - always, in public - only if not empty
		if (
			BX.Landing.getMode() === "edit"
			|| (options.title || options.description)
		)
		{
			item.marker.addListener("click", this.onMarkerClick.bind(this, item));
		}
		this.onChange();
	}

	onMarkerClick(item): void
	{
		void (this.activeMarker && this.activeMarker.infoWindow.close());
		item.infoWindow.open(this.mapInstance, item.marker);
		this.activeMarker = item;
	}

	onEditFormRemoveClick(event): void
	{
		if (event)
		{
			event.infoWindow.close();
			this.removeMarker(event);
		}

		this.markers.remove(event);
		this.onChange();
	}

	onEditFormApplyClick(event): void
	{
		event.infoWindow.close();
		this.onChange();
	}

	removeMarker(event): void
	{
		event.marker.setMap(null);
		this.markers.remove(event);
	}

	clearMarkers(): void
	{
		this.markers.forEach(marker => {
			marker.marker.setMap(null);
		});
		this.markers.clear();
	}

	setZoom(zoom): void
	{
		this.preventChangeEvent = true;
		this.mapInstance.setZoom(zoom);
	}

	setCenter(center): void
	{
		this.preventChangeEvent = true;
		this.mapInstance.setCenter(center);
	}

	getMarkersValue(): {}
	{
		return this.markers.map(function (item)
		{
			return {
				title: item.form ? item.form.fields[0].getValue() : "",
				description: item.form ? item.form.fields[1].getValue() : "",
				showByDefault: item.form ? !!item.form.fields[2].getValue()[0] : "",
				latLng: item.marker.position.toJSON(),
			};
		});
	}

	getValue(): {}
	{
		return {
			center: this.mapInstance.getCenter() ? this.mapInstance.getCenter().toJSON() : {},
			zoom: this.mapInstance.getZoom(),
			markers: this.getMarkersValue(),
		};
	}
}