import {Location} from 'location.core';

/**
 * Base class for the source geocoding service
 */
export default class GeocodingServiceBase
{
	geocode(addressString: string): Promise<Array<Location>, Error>
	{
		if(!addressString)
		{
			return Promise.resolve([]);
		}

		return this.geocodeConcrete(addressString);
	}

	geocodeConcrete(addressString: string): Promise<Array<Location>, Error>
	{
		throw new Error('Method geocodeConcrete() must be implemented');
	}
}