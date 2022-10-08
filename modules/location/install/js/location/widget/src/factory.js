import {BaseSource, LocationRepository, SourceCreationError, Format, Location} from 'location.core';

import {OSMFactory} from 'location.osm';

import {Google} from 'location.google';
import {OSM} from 'location.osm';

import Address from './address/address';
import Autocomplete from './autocomplete/autocomplete';

import MapPopup from './mappopup/mappopup';
import Gallery from './mappopup/gallery';
import Popup from './mappopup/popup';
import Fields from './fields/fields';

import MapFeature from './address/features/mapfeature';
import AutocompleteFeature from './address/features/autocompletefeature';
import FieldsFeature from './address/features/fieldsfeature';
import MapFeatureAuto from './address/features/mapfeatureauto';

/**
 * Props type for the main fabric method
 */
export type FactoryCreateAddressWidgetProps = {
	// Initial Address
	address?: Address,
	// Initial widget mode
	mode: 'edit' | 'view',

	// optional
	languageId?: string,
	addressFormat?: Format,
	sourceCode?: string,
	sourceParams?: {},

	// Witch features will be used?
	useFeatures?: {
		fields?: boolean, 		// default true
		map?: boolean, 			// default true
		autocomplete?: boolean	// default true
	},

	// Useful for the using with map feature only
	// Address photo height if photo exists
	thumbnailHeight: number,
	// Address photo width if photo exists
	thumbnailWidth: number,
	// Max photo thumbnails count
	maxPhotoCount: number,
	/*
	 * auto - open / close map feature depends on user's input
	 * manual - allow to control open / close map
	 */
	mapBehavior: 'auto' | 'manual',
	popupOptions?: {},
	popupBindOptions: {
		forceBindPosition?: boolean,
		forceLeft?: boolean,
		forceTop?: boolean,
		position?: 'right' | 'top' | 'bootom'
	},
	presetLocationsProvider?: Function,
	presetLocationList?: []
};

/**
 * Factory class with a set of tools for the address widget creation
 */
export default class Factory
{
	/**
	 * Main factory method
	 * @param {FactoryCreateAddressWidgetProps} props
	 * @returns {Address}
	 */
	createAddressWidget(props: FactoryCreateAddressWidgetProps): Address
	{
		const sourceCode = props.sourceCode || BX.message('LOCATION_WIDGET_SOURCE_CODE');
		const sourceParams = props.sourceParams || BX.message('LOCATION_WIDGET_SOURCE_PARAMS');
		const languageId = props.languageId || BX.message('LOCATION_WIDGET_LANGUAGE_ID');
		const sourceLanguageId = props.sourceLanguageId || BX.message('LOCATION_WIDGET_SOURCE_LANGUAGE_ID');
		const userLocationPoint = new Location(JSON.parse(BX.message('LOCATION_WIDGET_USER_LOCATION_POINT')));

		const addressFormat = props.addressFormat || new Format(
			JSON.parse(
				BX.message('LOCATION_WIDGET_DEFAULT_FORMAT')
			));

		const presetLocationsProvider = props.presetLocationsProvider
			? props.presetLocationsProvider
			: () => {
				return props.presetLocationList ? props.presetLocationList : [];
			};

		const features = [];

		if(!props.useFeatures || props.useFeatures.fields !== false)
		{
			features.push(
				this.createFieldsFeature({addressFormat, languageId})
			);
		}

		let source = null;

		if(sourceCode && sourceParams)
		{
			try
			{
				source = this.createSource(sourceCode, sourceParams, languageId, sourceLanguageId);
			}
			catch (e)
			{
				if(e instanceof SourceCreationError)
				{
					source = null;
				}
				else
				{
					throw e;
				}
			}
		}

		let mapFeature = null;
		if(source)
		{
			if(!props.useFeatures || props.useFeatures.autocomplete !== false)
			{
				features.push(
					this.createAutocompleteFeature({
						languageId,
						addressFormat,
						source,
						userLocationPoint: userLocationPoint,
						presetLocationsProvider
					}));
			}

			if(!props.useFeatures || props.useFeatures.map !== false)
			{
				const showPhotos = !!sourceParams.showPhotos;
				const useGeocodingService = !!sourceParams.useGeocodingService;
				const DEFAULT_THUMBNAIL_HEIGHT = 80;
				const DEFAULT_THUMBNAIL_WIDTH = 150;
				const DEFAULT_MAX_PHOTO_COUNT = showPhotos ? 5 : 0;
				const DEFAULT_MAP_BEHAVIOR = 'auto';

				mapFeature = this.createMapFeature({
					addressFormat,
					source,
					useGeocodingService,
					popupOptions: props.popupOptions,
					popupBindOptions: props.popupBindOptions,
					thumbnailHeight: props.thumbnailHeight || DEFAULT_THUMBNAIL_HEIGHT,
					thumbnailWidth: props.thumbnailWidth || DEFAULT_THUMBNAIL_WIDTH,
					maxPhotoCount: props.maxPhotoCount || DEFAULT_MAX_PHOTO_COUNT,
					mapBehavior: props.mapBehavior || DEFAULT_MAP_BEHAVIOR,
					userLocationPoint: userLocationPoint
				});

				features.push(mapFeature);
			}
		}

		const widget = new Address({
			features,
			address: props.address,
			mode: props.mode,
			addressFormat,
			languageId,
		});

		if(mapFeature)
		{
			widget.subscribeOnFeatureEvent((event) => {
				const data = event.getData();

				if(data.feature instanceof AutocompleteFeature
					&& data.eventCode === AutocompleteFeature.showOnMapClickedEvent
				)
				{
					mapFeature.showMap(true);
				}
			});
		}

		return widget;
	}

	createFieldsFeature(props: {}): FieldsFeature
	{
		const fields = new Fields({
			addressFormat: props.addressFormat,
			languageId: props.languageId,
		});

		return new FieldsFeature({
			fields
		});
	}

	createAutocompleteFeature(props: {}): AutocompleteFeature
	{
		const autocomplete = new Autocomplete({
			sourceCode: props.source.sourceCode,
			languageId: props.languageId,
			addressFormat: props.addressFormat,
			autocompleteService: props.source.autocompleteService,
			userLocationPoint: props.userLocationPoint,
			presetLocationsProvider: props.presetLocationsProvider,
		});

		return new AutocompleteFeature({
			autocomplete
		});
	}

	createMapFeature(props: {}): MapFeature
	{
		let popupOptions = {
			cacheable: true,
			closeByEsc: true,
			className: `location-popup-window location-source-${props.source.sourceCode}`,
			animation: 'fading',
			angle: true,
			bindOptions: props.popupBindOptions
		};
		if(props.popupOptions)
		{
			popupOptions = Object.assign(popupOptions, props.popupOptions);
		}
		const popup = new Popup(popupOptions);

		let gallery = null;

		if(props.maxPhotoCount > 0)
		{
			gallery = new Gallery({
				photoService: props.source.photoService,
				thumbnailHeight: props.thumbnailHeight,
				thumbnailWidth: props.thumbnailWidth,
				maxPhotoCount: props.maxPhotoCount
			});
		}

		const mapFeatureProps = {
			saveResourceStrategy: props.source.sourceCode === Google.code,
			map: new MapPopup({
				addressFormat: props.addressFormat,
				map: props.source.map,
				popup: popup,
				gallery: gallery,
				locationRepository: new LocationRepository(),
				geocodingService: props.useGeocodingService ? props.source.geocodingService : null,
				userLocationPoint: props.userLocationPoint
			})
		};

		let result;

		if(props.mapBehavior === 'manual')
		{
			result = new MapFeature(mapFeatureProps);
		}
		else
		{
			result = new MapFeatureAuto(mapFeatureProps);
		}

		return result;
	}

	// todo: add custom sources
	createSource(code: string, params: {}, languageId: string, sourceLanguageId: string): BaseSource
	{
		let source = null;

		params.languageId = languageId;
		params.sourceLanguageId = sourceLanguageId;

		if(code === Google.code)
		{
			source = new Google(params);
		}
		else if(code === OSM.code)
		{
			source = OSMFactory.createOSMSource(params);
		}
		else
		{
			throw new RangeError('Wrong source code');
		}

		return source;
	}
}
