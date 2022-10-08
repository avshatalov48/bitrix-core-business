import {Location, AddressType, LocationType, GeocodingServiceBase} from 'location.core';

export default class GeocodingService extends GeocodingServiceBase
{
	#map;
	#geocoder;
	#loadingPromise;
	#googleSource;

	constructor(props)
	{
		super(props);

		this.#map = props.map;
		this.#googleSource = props.googleSource;
	}

	#getLoaderPromise()
	{
		if(!this.#loadingPromise)
		{
			//map haven't rendered yet	`
			if(this.#googleSource.loaderPromise === null)
			{
				return;
			}

			this.#loadingPromise = this.#googleSource.loaderPromise.then(() => {
				this.#geocoder = new google.maps.Geocoder();
			});
		}

		return this.#loadingPromise;
	}

	#convertLocationType(types: Array)
	{
		let typeMap = {
			'country': LocationType.COUNTRY,
			'locality': LocationType.LOCALITY,
			'postal_town': LocationType.LOCALITY,
			'route': LocationType.STREET,
			'street_address': LocationType.ADDRESS_LINE_1,
			'administrative_area_level_4': LocationType.ADM_LEVEL_4,
			'administrative_area_level_3': LocationType.ADM_LEVEL_3,
			'administrative_area_level_2': LocationType.ADM_LEVEL_2,
			'administrative_area_level_1': LocationType.ADM_LEVEL_1,
			'floor': LocationType.FLOOR,
			'postal_code': AddressType.POSTAL_CODE,
			'room': LocationType.ROOM,
			'sublocality': LocationType.SUB_LOCALITY,
			'sublocality_level_1': LocationType.SUB_LOCALITY_LEVEL_1,
			'sublocality_level_2': LocationType.SUB_LOCALITY_LEVEL_2,
			'street_number': LocationType.BUILDING
		};

		let result = LocationType.UNKNOWN;

		for (let item of types)
		{
			if(typeof typeMap[item] !== 'undefined')
			{
				result = typeMap[item];
				break;
			}
		}

		return result;
	}

	#convertResultToLocations(data: Array)
	{
		let result = [];

		for (let item of data)
		{
			let location = new Location;
			location.sourceCode = this.#googleSource.sourceCode;
			location.languageId = this.#googleSource.languageId;
			location.externalId = item.place_id;
			location.type = this.#convertLocationType(item.types);
			location.name = item.formatted_address;
			location.latitude = item.geometry.location.lat();
			location.longitude = item.geometry.location.lng();
			result.push(location);
		}

		return result;
	}

	geocodeConcrete(addressString: string): Promise
	{
		return new Promise((resolve) => {

			const loaderPromise = this.#getLoaderPromise();

			if(!loaderPromise)
			{
				resolve([]);
				return;
			}

			loaderPromise
				.then(() => {
					this.#geocoder.geocode({address: addressString}, (results, status) => {
						if(status === 'OK') {
							resolve(this.#convertResultToLocations(results));
						}
						else if(status === 'ZERO_RESULTS')
						{
							resolve([]);
						}
						else
						{
							BX.debug(`Geocode was not successful for the following reason: ${status}`);
						}
					});
				});
		});
	}
}