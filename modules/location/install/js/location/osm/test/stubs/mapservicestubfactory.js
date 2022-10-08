import MapService from '../../src/mapservice';
import GeocodingService from '../../src/geocodingservice';
import MarkerStub from './markerstub';
import MapStub from './mapstub';

export default class MapServiceStubFactory
{
	static create()
	{
		return new MapService({
			languageId: 'en',
			geocodingService: new GeocodingService({
				requester: {request: () => { return null; }}
			}),
			mapFactoryMethod: () => { return new MapStub(); },
			markerFactoryMethod: () => { return new MarkerStub(); },
			tileLayerFactoryMethod: () => { return null; },
		});
	}
}