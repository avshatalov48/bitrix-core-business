import {Type, Tag, Loc, Cache, Runtime} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {BaseCollection} from 'landing.collection.basecollection';

export class BaseProvider extends EventEmitter
{
	/**
	 * Implements base interface for works with any map providers
	 * @param {object} options
	 * @param {function} [options.onChange]
	 * @param {function} [options.onMapClick]
	 * @param {function} [options.onAddMarker]
	 * @param {function} [options.onApiLoaded]
	 * @param {HTMLElement|Element} [options.mapContainer]
	 * @param {Object} [options.mapOptions]
	 * @param {Number|String} [options.mapOptions.zoom]
	 * @param {{lat: String|Number, lng: String|Number}} [options.mapOptions.center]
	 * @param {{
	 * 		latLng: {lat: String|Number, lng: String|Number},
	 * 		title: String,
	 * 		description: String,
	 * 		showByDefault: String,
	 * 	}[]} [options.mapOptions.markers]
	 * @constructor
	 */
	constructor(options: {})
	{
		super();
		/**
		 * Must be implemented by subclass
		 * @type {string}
		 */
		this.code = '';

		this.onChangeHandler = Type.isFunction(options.onChange) ? options.onChange : (() => {});
		this.onMapClickHandler = Type.isFunction(options.onMapClick) ? options.onMapClick : (() => {});
		this.onAddMarkerHandler = Type.isFunction(options.onAddMarker) ? options.onAddMarker : (() => {});
		this.onApiLoadedHandler = Type.isFunction(options.onApiLoaded) ? options.onApiLoaded : (() => {});
		this.onInitHandler = Type.isFunction(options.onProviderInit) ? options.onProviderInit : (() => {});
		this.options = options;
		this.mapOptions = this.prepareMapOptions(options.mapOptions);
		this.mapContainer = options.mapContainer;
		this.markers = new BaseCollection();
		this.mapInstance = null;

		this.cache = new Cache.MemoryCache();

		this.handleApiLoad();

		this.onChange = Runtime.debounce(this.onChange.bind(this), 666);
	}



	/**
	 * Default options for map
	 * @type {{}}
	 */
	getDefaultMapOptions()
	{
		return {
			center: this.getDefaultCenter(),
			zoom: 17,
			markers: [
				{
					latLng: this.getDefaultCenter(),
					// todo: desc to lang message
					title: "Bitrix24",
					description: "Bitrix24 - Your company. United.",
				},
			],
		};
	}

	/**
	 * Check if map options have required fields
	 * @param mapOptions
	 * @return {{center: ([]|{lng: number, lat: number}), zoom: number, markers: [{description: string, title: string, latLng: (*|{lng: number, lat: number})}]}|*}
	 */
	prepareMapOptions(mapOptions: {}): {}
	{
		if (!Type.isPlainObject(mapOptions))
		{
			return this.getDefaultMapOptions();
		}

		let preparedOptions = mapOptions;
		if (!this.isPoint(preparedOptions.center))
		{
			preparedOptions.center = this.getDefaultCenter();

			if (
				Type.isArray(mapOptions.markers)
				&& mapOptions.markers.length > 0
			)
			{
				const firstMarker = mapOptions.markers[0];

				if (
					Type.isPlainObject(firstMarker)
					&& this.isPoint(firstMarker.latLng)
				)
				{
					preparedOptions.center = firstMarker.latLng;
				}
			}
		}

		return preparedOptions;
	}

	/**
	 * Return a default center point by language
	 * @return {{lng: number, lat: number}}
	 */
	getDefaultCenter(): {lat: number, lng: number}
	{
		let point;
		switch (Loc.getMessage('LANGUAGE_ID'))
		{
			case 'ru':
				point = {
					lat: 54.71916849999999,
					lng: 20.48854240000003,
				};
				break;
			case 'ua':
				point = {
					lat: 50.440333,
					lng: 30.526835,
				};
				break;
			default:
				//default - en
				point = {
					lat: 38.814089,
					lng: -77.042356,
				};
				break;
		}

		return point;
	}

	/**
	 * Check is current variable is a geo point
	 * @param point
	 * @return {boolean}
	 */
	isPoint(point): boolean
	{
		return Type.isObjectLike(point) && Object.keys(point).length === 2;
	}

	/**
	 * Extract coords from map events (f.e. click)
	 * @param event
	 */
	getPointByEvent(event): {lat: number, lng: number}
	{
		throw new Error("Must be implemented by subclass");
	}

	getCode(): string
	{
		return this.code;
	}

	/**
	 * Check is provider API was loaded
	 * @return {boolean}
	 */
	isApiLoaded(): boolean
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Initializes map
	 * Must be implemented by subclass
	 * @abstract
	 */
	init()
	{
		this.onInitHandler();
		this.emit('onInit');
	}

	/**
	 * Pass new options and reinit map
	 * @param options
	 */
	reinit(options: {})
	{
		// todo: add options type and validation
		this.options = options;
		this.emit('onInit');
	}

	/**
	 * Set api load handle function
	 * @abstract
	 */
	handleApiLoad(): void
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 *
	 */
	onChange()
	{
		this.onChangeHandler(this.preventChangeEvent);
		this.preventChangeEvent = false;
	}

	/**
	 * Adds marker on map
	 * @abstract
	 * @param {Object} options
	 * @param {Object} options.latLng
	 * @param {Object} options.latLng
	 * @param {String|Number} options.latLng.lat
	 * @param {String|Number} options.latLng.lng
	 * @param {String} [options.title]
	 * @param {String} [options.description]
	 * @param {boolean} [options.showByDefault = false]
	 * @param {boolean} [options.editable = false]
	 * @param {boolean} [options.draggable = false]
	 * @return {void}
	 */
	addMarker(options): void
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * When marker clicked
	 * @param item
	 */
	onMarkerClick(item): void
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Removes marker from map
	 * @abstract
	 * @param options
	 */
	removeMarker(options): void
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Removes all markers from map
	 * @abstract
	 * @param options
	 */
	clearMarkers(): void
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Gets map value
	 * @abstract
	 */
	getValue(): {}
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Set values
	 * @param value
	 * @param preventChangeEvent
	 */
	setValue(value, preventChangeEvent): void
	{
		this.preventChangeEvent = preventChangeEvent;

		this.clearMarkers();

		if (Type.isPlainObject(value))
		{
			if (Type.isArray(value.markers))
			{
				value.markers.forEach(this.addMarker, this);
			}

			if (!BX.Landing.Utils.isEmpty(value.center))
			{
				this.setCenter(value.center);
			}

			if (value.zoom && Type.isNumber(value.zoom))
			{
				this.setZoom(value.zoom);
			}
		}
	}

	/**
	 * @abstract
	 */
	onEditFormApplyClick(event): void
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * @abstract
	 * @param event
	 */
	onEditFormRemoveClick(event): void
	{
		throw new Error("Must be implemented by subclass");
	}

	/**
	 * Creates balloon edit forms
	 * @param options
	 * @param [event]
	 * @return {BX.Landing.UI.Form.BalloonForm}
	 */
	createBalloonEditForm(options, event)
	{
		const form = new BX.Landing.UI.Form.BalloonForm({
			title: Loc.getMessage("LANDING_NODE_MAP_FORM_HEADER"),
		});

		const applyButton = new BX.Landing.UI.Button.BaseButton({
			text: Loc.getMessage("LANDING_NODE_MAP_FORM_SHOW_BUTTON_APPLY"),
			className: ["ui-btn", "ui-btn-success", "ui-btn-sm"],
			onClick: this.onEditFormApplyClick.bind(this, event),
		});

		const removeButton = new BX.Landing.UI.Button.BaseButton({
			text: Loc.getMessage("LANDING_NODE_MAP_FORM_SHOW_BUTTON_REMOVE"),
			className: ["ui-btn", "ui-btn-danger", "ui-btn-sm"],
			onClick: this.onEditFormRemoveClick.bind(this, event),
		});

		applyButton.layout.classList.remove("landing-ui-button");
		removeButton.layout.classList.remove("landing-ui-button");

		const footer = Tag.render`
			<div class="ui-btn-container ui-btn-container-center">
				${applyButton.layout}
				${removeButton.layout}
			</div>
		`;

		form.addField(
			new BX.Landing.UI.Field.Text({
				title: Loc.getMessage("LANDING_NODE_MAP_FORM_TITLE"),
				textOnly: true,
				content: options.title,
			}),
		);

		form.addField(
			new BX.Landing.UI.Field.Text({
				title: Loc.getMessage("LANDING_NODE_MAP_FORM_DESCRIPTION"),
				className: "landing-ui-field-map-description",
				content: options.description,
			}),
		);

		form.addField(
			new BX.Landing.UI.Field.Checkbox({
				className: "landing-ui-field-map-show-by-default",
				compact: true,
				items: [
					{name: Loc.getMessage("LANDING_NODE_MAP_FORM_SHOW_BY_DEFAULT"), "value": true},
				],
				value: [options.showByDefault],
			}),
		);

		form.layout.appendChild(footer);

		return form;
	}

	/**
	 * Creates balloon content
	 * @param {{title: string, description: string}} options
	 * @return {HTMLElement}
	 */
	createBalloonContent(options): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-map-balloon-content">
				<div class="landing-map-balloon-content-header">${options.title}</div>	
				<div class="landing-map-balloon-content-description">${options.description}</div>	
			</div>
		`;
	}
}