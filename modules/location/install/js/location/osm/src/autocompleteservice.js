import {
	AutocompleteServiceBase,
	Location,
	AutocompleteCache,
} from 'location.core';
import type { AutocompleteServiceParams } from 'location.core';
import OSM from './osm';

export default class AutocompleteService extends AutocompleteServiceBase
{
	#sourceLanguageId;
	#autocompleteResponseConverter;
	#autocompleteReplacements;
	#autocompletePromptsCount;

	constructor(props)
	{
		super(props);
		this.#sourceLanguageId = props.sourceLanguageId;
		this.#autocompleteResponseConverter = props.responseConverter;
		this.#autocompleteReplacements = props.autocompleteReplacements;
		this.#autocompletePromptsCount = props.autocompletePromptsCount;
	}

	autocomplete(text: String, autocompleteServiceParams: AutocompleteServiceParams): Promise<Array<Location>, Error>
	{
		if (text === '')
		{
			return new Promise((resolve) =>
			{
				resolve([]);
			});
		}

		const params = {
			q: this.#processQuery(text),
			limit: this.#autocompletePromptsCount,
			lang: this.#sourceLanguageId,
		};

		if (autocompleteServiceParams.biasPoint)
		{
			const lat = autocompleteServiceParams.biasPoint.latitude;
			const lon = autocompleteServiceParams.biasPoint.longitude;

			if (lat && lon)
			{
				params.lat = lat;
				params.lon = lon;
			}
		}

		let cachedResult = AutocompleteCache.get(OSM.code, params);
		if (cachedResult !== null)
		{
			return Promise.resolve(
				this.#autocompleteResponseConverter.convertResponse(
					cachedResult.data.result,
					{text, autocompleteServiceParams}
				)
			);
		}

		return BX.ajax.runAction(
			'location.api.location.autocomplete',
			{data: {params: params}}
		)
			.then((response) =>
			{
				if (response)
				{
					AutocompleteCache.set(OSM.code, params, { result: response.data });
				}

				return response ? this.#autocompleteResponseConverter.convertResponse(
					response.data,
					{text, autocompleteServiceParams}
				) : [];
			})
			.catch((response) => {
				console.error(response);
			});
	}

	#processQuery(query: string): string
	{
		let result = query;

		for (const partToReplace in this.#autocompleteReplacements)
		{
			if (this.#autocompleteReplacements.hasOwnProperty(partToReplace))
			{
				result = result.replace(partToReplace, this.#autocompleteReplacements[partToReplace])
			}
		}

		return result;
	}
}
