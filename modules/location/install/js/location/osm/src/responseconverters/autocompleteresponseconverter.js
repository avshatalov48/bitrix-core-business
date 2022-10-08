import {Loc} from 'main.core';
import {
	Location,
	LocationType,
	AddressType,
	Address
} from 'location.core';

import type {AutocompleteServiceParams} from 'location.core';
import BaseResponseConverter from './baseresponseconverter';

export default class AutocompleteResponseConverter extends BaseResponseConverter
{
	convertResponse(response: {}, params: {autocompleteServiceParams: AutocompleteServiceParams, text: string}): Array<Location>
	{
		if (
			!response
			|| !Array.isArray(response.features)
			|| response.features.length === 0
		)
		{
			return [];
		}

		const result = [];
		const hashMap = [];
		const addressLine2 = response.address_tail ? response.address_tail : '';

		response.features.forEach((item) =>
		{
			if (typeof item.properties !== 'object')
			{
				return;
			}

			// Collapse a similar location data
			const hash = this.#createAnswerHash(item.properties);

			if (hashMap.indexOf(hash) !== -1)
			{
				return;
			}
			// Collapse block end.

			hashMap.push(hash);

			const location = this.#createLocation(item, params.autocompleteServiceParams);
			if (location)
			{
				if (location.address && addressLine2)
				{
					location.address.setFieldValue(AddressType.ADDRESS_LINE_2, addressLine2);
				}

				result.push(location);
			}
		});

		return result;
	}

	#createLocation(responseItem: {}, autocompleteServiceParams: AutocompleteServiceParams): ?Location
	{
		if (!responseItem.properties)
		{
			return null;
		}

		const props = responseItem.properties;
		const externalId = this.#createExternalId(props.osm_type, props.osm_id);

		if (!externalId)
		{
			return null;
		}

		const name = this.#createLocationNameFromResponse(responseItem);

		if (name === '')
		{
			return null;
		}

		const type = this.#convertLocationType(props.type);
		const address = this.#createAddressFromResponse(responseItem, type);

		const location = new Location({
			address: address,
			externalId: externalId,
			sourceCode: this.sourceCode,
			type: type,
			name: name,
			languageId: this.languageId
		});

		if (
			responseItem.geometry
			&& responseItem.geometry.coordinates
			&& responseItem.geometry.coordinates[0]
			&& responseItem.geometry.coordinates[1]
		)
		{
			location.latitude = String(responseItem.geometry.coordinates[1]);
			location.longitude = String(responseItem.geometry.coordinates[0]);
		}

		if (address)
		{
			const clarification = this.#makeClarification(address);

			if (clarification !== '')
			{
				location.setFieldValue(
					LocationType.TMP_TYPE_CLARIFICATION,
					clarification
				);
			}
		}

		// We'll show a hint about the location type
		if (props.osm_value && props.osm_key)
		{
			const typeHint = this.#getItemTypeHint(props.osm_key, props.osm_value);

			if (typeHint !== '')
			{
				location.setFieldValue(LocationType.TMP_TYPE_HINT, typeHint);
			}
		}
		return location;
	}

	#getItemTypeHint(osmKey: String, osmValue: String): String
	{
		let result = '';

		if (osmValue === 'city' || osmValue === 'town')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_CITY');
		}
		else if (osmValue === 'village' || osmValue === 'hamlet')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_VILLAGE');
		}
		else if (osmValue === 'locality')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_LOCALITY');
		}
		else if (osmValue === 'hotel')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_HOTEL');
		}
		else if (osmValue === 'suburb')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_SUBURB');
		}
		else if (osmValue === 'island')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_ISLAND');
		}
		else if (osmValue === 'cafe')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_CAFE');
		}
		else if (osmValue === 'restaurant')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_RESTAURANT');
		}
		else if (osmValue === 'river')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_RIVER');
		}
		else if (osmKey === 'shop')
		{
			result = Loc.getMessage('LOCATION_OSM_AUTOCOMPLETE_TYPE_SHOP');
		}

		return result;
	}

	#makeClarification(address: Address): string
	{
		let clarification = '';

		if (address.getFieldValue(AddressType.LOCALITY))
		{
			clarification += address.getFieldValue(AddressType.LOCALITY);
		}

		if (address.getFieldValue(AddressType.ADM_LEVEL_1))
		{
			if (clarification !== '')
			{
				clarification += ', ';
			}

			clarification += address.getFieldValue(AddressType.ADM_LEVEL_1);
		}

		if (address.getFieldValue(AddressType.COUNTRY))
		{
			if (clarification !== '')
			{
				clarification += ', ';
			}

			clarification += address.getFieldValue(AddressType.COUNTRY);
		}

		return clarification;
	}

	#createLocationNameFromResponse(responseItem: {}): string
	{
		if (!responseItem.properties)
		{
			return '';
		}

		const props = responseItem.properties;
		let name = props?.name ?? '';

		if (props.street)
		{
			if (name !== '')
			{
				name += ', ';
			}

			name += props.street;
		}

		if (props.housenumber)
		{
			if (name !== '')
			{
				name += ', ';
			}

			name += props.housenumber;
		}

		return name;
	}

	#createAddressFromResponse(responseItem: {}, locationType: number): ?Address
	{
		if (!responseItem.properties)
		{
			return null;
		}

		const props = responseItem.properties;

		const address = new Address({
			languageId: this.languageId,
		});

		if (
			responseItem.geometry
			&& responseItem.geometry.coordinates
			&& responseItem.geometry.coordinates[0]
			&& responseItem.geometry.coordinates[1]
		)
		{
			address.latitude = String(responseItem.geometry.coordinates[1]);
			address.longitude = String(responseItem.geometry.coordinates[0]);
		}

		if (props.name)
		{
			address.setFieldValue(locationType, props.name);
		}
		if (props.housenumber)
		{
			address.setFieldValue(AddressType.BUILDING, props.housenumber);
		}
		if (props.street)
		{
			address.setFieldValue(AddressType.STREET, props.street);
		}
		if (props.city)
		{
			address.setFieldValue(AddressType.LOCALITY, props.city);
		}
		if (props.state && props.state !== props.city)
		{
			address.setFieldValue(AddressType.ADM_LEVEL_1, props.state);
		}
		if (props.country)
		{
			address.setFieldValue(AddressType.COUNTRY, props.country);
		}
		if (props.postcode)
		{
			address.setFieldValue(AddressType.POSTAL_CODE, props.postcode);
		}

		return address;
	}

	#createAnswerHash(fields: {}): String
	{
		let result = '';

		result += (fields?.country ?? '');
		result += (fields?.state ?? '');
		result += (fields?.county ?? '');
		result += (fields?.locality ?? '');
		result += (fields?.city ?? '');
		result += (fields?.street ?? '');
		result += (fields?.name ?? '');
		result += (fields?.housenumber ?? '');
		result += (fields?.type ?? '');

		return result;
	}

	#convertLocationType(type: String): String
	{
		const typeMap = {
			postcode: AddressType.POSTAL_CODE,
			housenumber: LocationType.BUILDING,
			house: LocationType.BUILDING,
			name: AddressType.ADDRESS_LINE_2,
			country: LocationType.COUNTRY,
			city: LocationType.LOCALITY,
			district: LocationType.SUB_LOCALITY_LEVEL_1,
			locality: LocationType.LOCALITY,
			street: LocationType.STREET,
			state: LocationType.ADM_LEVEL_1,
			region: LocationType.ADM_LEVEL_1,
			county: LocationType.ADM_LEVEL_2,
		};

		let result = LocationType.UNKNOWN;

		if (typeof typeMap[type] !== 'undefined')
		{
			result = typeMap[type];
		}
		else
		{
			console.warn('Unknown response location type: ', type);
		}

		return result;
	}

	#createExternalId(osmType: String, osmId: String): ?String
	{
		if (!osmType || !osmId)
		{
			return null;
		}

		return osmType.substr(0, 1).toLocaleUpperCase() + osmId;
	}
}
