import {LocationRepository, SourceRepository} from 'location.core';
import {Leaflet} from '../leaflet/src/leaflet';
import OSM from './osm';
import AutocompleteService from './autocompleteservice';
import SearchRequester from './requesters/searchrequester';
import GeocodingService from './geocodingservice';
import ReverseRequester from './requesters/reverserequester';
import MapService from './mapservice';
import MapMobileService from './mapmobileservice';
import TileLayerAuth from '../leaflet/src/tilelayerauth';
import TokenContainer from './tokencontainer';
import NominatimResponseConverter from './responseconverters/nominatimresponseconverter';
import AutocompleteResponseConverter from './responseconverters/autocompleteresponseconverter';

export type OSMFactoryProps = {
	languageId: string,
	sourceLanguageId: string,
	token: string,
	serviceUrl: string,
	mapServiceUrl: string,
	hostName: string,
	autocompletePromptsCount: ?number
}

export default class OSMFactory
{
	static createOSMSource(params: OSMFactoryProps)
	{
		const tokenContainer = new TokenContainer({
			token: params.token,
			sourceRepository: new SourceRepository()
		});

		const osmParams =	{
			languageId: params.languageId,
			sourceLanguageId: params.sourceLanguageId
		};

		const responseConverter = new NominatimResponseConverter({languageId: params.languageId});

		const searchRequester = new SearchRequester({
			languageId: params.languageId,
			sourceLanguageId: params.sourceLanguageId,
			tokenContainer: tokenContainer,
			serviceUrl: params.serviceUrl,
			hostName: params.hostName,
			responseConverter: responseConverter
		});

		const reverseRequester = new ReverseRequester({
			languageId: params.languageId,
			sourceLanguageId: params.sourceLanguageId,
			serviceUrl: params.serviceUrl,
			hostName: params.hostName,
			tokenContainer: tokenContainer,
			responseConverter: responseConverter
		});

		const autocompleteResponseConverter = new AutocompleteResponseConverter({languageId: params.languageId});

		osmParams.autocompleteService = new AutocompleteService({
			languageId: params.languageId,
			autocompletePromptsCount: params.autocompletePromptsCount || 7,
			sourceLanguageId: params.sourceLanguageId,
			responseConverter: autocompleteResponseConverter,
			autocompleteReplacements: params.autocompleteReplacements
		});

		const geocodingService = new GeocodingService({
			searchRequester: searchRequester,
			reverseRequester: reverseRequester
		});

		osmParams.geocodingService = geocodingService;

		osmParams.mapService = new MapService({
			languageId: params.languageId,
			geocodingService: geocodingService,
			mapFactoryMethod: Leaflet.map,
			markerFactoryMethod: Leaflet.marker,
			locationRepository: new LocationRepository(),
			sourceLanguageId: params.sourceLanguageId,
			tileLayerFactoryMethod: () => {
				const tileLayerAuth = new TileLayerAuth();
				tileLayerAuth.setTokenContainer(tokenContainer);
				tileLayerAuth.setHostName(params.hostName);
				return tileLayerAuth;
			},
			serviceUrl: params.serviceUrl,
			mapServiceUrl: params.mapServiceUrl,
		});

		osmParams.mapMobileService = new MapMobileService({
			languageId: params.languageId,
			geocodingService: geocodingService,
			mapFactoryMethod: Leaflet.map,
			markerFactoryMethod: Leaflet.marker,
			iconFactoryMethod: Leaflet.icon,
			locationRepository: new LocationRepository(),
			sourceLanguageId: params.sourceLanguageId,
			tileLayerFactoryMethod: () => {
				const tileLayerAuth = new TileLayerAuth();
				tileLayerAuth.setTokenContainer(tokenContainer);
				tileLayerAuth.setHostName(params.hostName);
				return tileLayerAuth;
			},
			serviceUrl: params.serviceUrl,
			mapServiceUrl: params.mapServiceUrl,
		});

		return new OSM(osmParams);
	}
}
