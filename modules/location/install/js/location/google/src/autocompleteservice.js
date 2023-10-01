/* global google */

import {Loc} from 'main.core';
import {
	Location,
	AutocompleteServiceBase,
	AutocompleteCache,
	LocationType
} from 'location.core';
import type { AutocompleteServiceParams } from 'location.core';
import { Google } from './google';

const STATUS_OK = 'OK';
const STATUS_ZERO_RESULTS = 'ZERO_RESULTS';

export default class AutocompleteService extends AutocompleteServiceBase
{
	/** {string} */
	#languageId;
	/** {google.maps.places.AutocompleteService} */
	#googleAutocompleteService;
	/** {Promise} */
	#loaderPromise;
	/** {GoogleSource} */
	#googleSource;
	/** {number} */
	#biasBoundRadius = 50000;

	constructor(props)
	{
		super(props);
		this.#languageId = props.languageId;
		this.#googleSource = props.googleSource;
		// Because googleSource could still be in the process of loading
		this.#loaderPromise = props.googleSource.loaderPromise
			.then(() => {
				this.#initAutocompleteService();
			});
	}

	#getPredictionPromise(query: string, params: AutocompleteServiceParams)
	{
		const queryPredictionsParams = {
			input: query,
		};

		if (params.biasPoint)
		{
			queryPredictionsParams.location = new google.maps.LatLng(
				params.biasPoint.latitude,
				params.biasPoint.longitude
			);
			queryPredictionsParams.radius = this.#biasBoundRadius;
		}

		let cachedResult = AutocompleteCache.get(Google.code, queryPredictionsParams);
		if (cachedResult !== null)
		{
			return Promise.resolve(
				this.#convertToLocationsList(
					cachedResult.data.result,
					cachedResult.data.status
				)
			);
		}

		return new Promise((resolve) => {
				this.#googleAutocompleteService.getQueryPredictions(
					queryPredictionsParams,
					(res, status) => {
						if (status === STATUS_OK || status === STATUS_ZERO_RESULTS)
						{
							AutocompleteCache.set(
								Google.code,
								queryPredictionsParams,
								{
									status: status,
									result: res,
								}
							);
						}

						resolve(
							this.#convertToLocationsList(res, status)
						);
					}
				);
			}
		);
	}

	/**
	 * Returns Promise witch  will transfer locations list
	 * @param {string} query
	 * @param {AutocompleteServiceParams} params
	 * @returns {Promise}
	 */
	autocomplete(query: string, params: AutocompleteServiceParams): Promise<Array<Location>, Error>
	{
		if (query === '')
		{
			return new Promise((resolve) => {
				resolve([]);
			});
		}

		// Because google.maps.places.AutocompleteService could be still in the process of loading
		return this.#loaderPromise
			.then(() => {
				return this.#getPredictionPromise(query, params);
			},
			(error) => BX.debug(error)
		);
	}

	#initAutocompleteService()
	{
		if(typeof google === 'undefined' || typeof google.maps.places.AutocompleteService === 'undefined')
		{
			throw new Error('google.maps.places.AutocompleteService must be defined');
		}

		this.#googleAutocompleteService = new google.maps.places.AutocompleteService();
	}

	#convertToLocationsList(data, status)
	{
		if (status === STATUS_ZERO_RESULTS)
		{
			return [];
		}

		if (!data || status !== STATUS_OK)
		{
			return false;
		}

		const result = [];

		for (const item of data)
		{
			if (item.place_id)
			{
				let name;

				if (item.structured_formatting && item.structured_formatting.main_text)
				{
					name = item.structured_formatting.main_text;
				}
				else
				{
					name = item.description;
				}

				const location = new Location({
					sourceCode: this.#googleSource.sourceCode,
					externalId: item.place_id,
					name: name,
					languageId: this.#languageId
				});

				if (item.structured_formatting && item.structured_formatting.secondary_text)
				{
					location.setFieldValue(
						LocationType.TMP_TYPE_CLARIFICATION,
						item.structured_formatting.secondary_text
					);
				}

				const typeHint = this.#getTypeHint(item.types);

				if (typeHint)
				{
					location.setFieldValue(
						LocationType.TMP_TYPE_HINT,
						this.#getTypeHint(item.types)
					);
				}

				result.push(location);
			}
		}

		return result;
	}

	#getTypeHint(types: Array): String
	{
		let result = '';

		if (types.indexOf('locality') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_LOCALITY');
		}
		else if (types.indexOf('sublocality') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_SUBLOCAL');
		}
		else if (types.indexOf('store') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_STORE');
		}
		else if (types.indexOf('restaurant') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_RESTAURANT');
		}
		else if (types.indexOf('cafe') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_CAFE');
		}

		return result;
	}
}
