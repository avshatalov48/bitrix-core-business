import {Location, LocationType, AddressType} from 'location.core';
import BaseResponseConverter from './baseresponseconverter';

export default class NominatimResponseConverter extends BaseResponseConverter
{
	/**
	 *
	 * @param {Array|Object} response
	 * @param {Object} params
	 * @return Array<Location>|Location|null
	 */
	// eslint-disable-next-line no-unused-vars
	convertResponse(response, params: {})
	{
		let result = null;

		if (Array.isArray(response))
		{
			result = [];

			if (response.length > 0)
			{
				response.forEach((item) =>
				{
					const location = this.#createLocation(item);
					if (location)
					{
						result.push(location);
					}
				});
			}
		}
		else if (typeof response === 'object')
		{
			result = this.#createLocation(response);
		}

		return result;
	}

	#createLocation(responseItem)
	{
		const externalId = this.#createExternalId(responseItem.osm_type, responseItem.osm_id);
		if (!externalId)
		{
			return null;
		}

		return new Location({
			externalId: externalId,
			latitude: responseItem.lat,
			longitude: responseItem.lon,
			type: this.#convertLocationType(responseItem.type),
			name: responseItem.display_name,
			languageId: this.languageId,
			sourceCode: this.sourceCode
		});
	}

	#convertLocationType(type: string)
	{
		const typeMap = {
			country: LocationType.COUNTRY,
			municipality: LocationType.LOCALITY,
			city: LocationType.LOCALITY,
			town: LocationType.LOCALITY,
			village: LocationType.LOCALITY,
			postal_town: LocationType.LOCALITY,
			road: LocationType.STREET,
			street_address: LocationType.ADDRESS_LINE_1,
			county: LocationType.ADM_LEVEL_4,
			state_district: LocationType.ADM_LEVEL_3,
			state: LocationType.ADM_LEVEL_2,
			region: LocationType.ADM_LEVEL_1,
			floor: LocationType.FLOOR,
			postal_code: AddressType.POSTAL_CODE,
			room: LocationType.ROOM,
			sublocality: LocationType.SUB_LOCALITY,
			city_district: LocationType.SUB_LOCALITY_LEVEL_1,
			district: LocationType.SUB_LOCALITY_LEVEL_1,
			borough: LocationType.SUB_LOCALITY_LEVEL_1,
			suburb: LocationType.SUB_LOCALITY_LEVEL_1,
			subdivision: LocationType.SUB_LOCALITY_LEVEL_1,
			house_number: LocationType.BUILDING,
			house_name: LocationType.BUILDING,
			building: LocationType.BUILDING
		};

		let result = LocationType.UNKNOWN;

		if (typeof typeMap[type] !== 'undefined')
		{
			result = typeMap[type];
		}

		return result;
	}

	#createExternalId(osmType: string, osmId: string)
	{
		if (!osmType || !osmId)
		{
			return null;
		}

		return osmType.substr(0, 1).toLocaleUpperCase() + osmId;
	}
}