/* global google */

import {Loc} from 'main.core';
import {Location, AutocompleteServiceBase, LocationType} from 'location.core';
import type {AutocompleteServiceParams} from 'location.core';

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
	/** {string} */
	#localStorageKey = 'locationGoogleAutocomplete';
	/** {number} */
	#localStorageResCount = 30;
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

	// eslint-disable-next-line no-unused-vars
	#getLocalStoredResults(query: string, params: AutocompleteServiceParams): object
	{
		let result = null;

		let storedResults = this.#getStoredResults();

		for(const [index, item] of storedResults.entries())
		{
			if(item && typeof item.query !== 'undefined' && item.query === query)
			{
				result = {...item};
				break;
			}
		}

		return result;
	}

	#getPredictionPromiseLocalStorage(query: string, params: AutocompleteServiceParams): ?Promise
	{
		let result = null;
			const answer = this.#getLocalStoredResults(query, params);

		if(answer !== null)
		{
			result = new Promise((resolve) => {
					resolve(
						this.#convertToLocationsList(answer.answer, answer.status)
					);
				}
			);
		}

		return result;
	}

	#getStoredResults()
	{
		let storedResults = BX.localStorage.get(this.#localStorageKey);

		if (
			storedResults
			&& storedResults.results
			&& Array.isArray(storedResults.results)
		)
		{
			return storedResults.results;
		}

		return [];
	}

	#setPredictionResult(query, params, answer, status): void
	{
		let storedResults = this.#getStoredResults();

		storedResults.push({
			status: status,
			query: query,
			answer: answer
		});

		if(storedResults.length > this.#localStorageResCount)
		{
			storedResults.shift();
		}

		BX.localStorage.set(this.#localStorageKey, {'results': storedResults}, 86400);
	}

	#getPredictionPromise(query: string, params: AutocompleteServiceParams)
	{
		let result = this.#getPredictionPromiseLocalStorage(query, params);

		if(!result)
		{
			const queryPredictionsParams = {
				input: query,
			};

			if(params.biasPoint)
			{
				queryPredictionsParams.location = new google.maps.LatLng(
					params.biasPoint.latitude,
					params.biasPoint.longitude
				);
				queryPredictionsParams.radius = this.#biasBoundRadius;
			}

			result = new Promise((resolve) => {
					this.#googleAutocompleteService.getQueryPredictions(
						queryPredictionsParams,
						(res, status) => {
							const locationsList = this.#convertToLocationsList(res, status);
							this.#setPredictionResult(query, params, res, status);
							resolve(locationsList);
						}
					);
				}
			);
		}

		return result;
	}

	/**
	 * Returns Promise witch  will transfer locations list
	 * @param {string} query
	 * @param {AutocompleteServiceParams} params
	 * @returns {Promise}
	 */
	autocomplete(query: string, params: AutocompleteServiceParams): Promise<Array<Location>, Error>
	{
		if(query === '')
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
		if(status === 'ZERO_RESULTS')
		{
			return [];
		}

		if(!data || status !== 'OK')
		{
			return false;
		}

		const result = [];

		for(const item of data)
		{
			if(item.place_id)
			{
				let name;

				if(item.structured_formatting && item.structured_formatting.main_text)
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

				if(item.structured_formatting && item.structured_formatting.secondary_text)
				{
					location.setFieldValue(
						LocationType.TMP_TYPE_CLARIFICATION,
						item.structured_formatting.secondary_text
					);
				}

				const typeHint = this.#getTypeHint(item.types);

				if(typeHint)
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

		if(types.indexOf('locality') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_LOCALITY');
		}
		else if(types.indexOf('sublocality') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_SUBLOCAL');
		}
		else if(types.indexOf('store') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_STORE');
		}
		else if(types.indexOf('restaurant') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_RESTAURANT');
		}
		else if(types.indexOf('cafe') >= 0)
		{
			result = Loc.getMessage('LOCATION_GOO_AUTOCOMPLETE_TYPE_CAFE');
		}
		/*
		else
		{
			result = types.join(', ');
		}
		*/

		return result;
	}
}
