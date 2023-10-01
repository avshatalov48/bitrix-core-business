import { md5 } from 'main.md5';

const MAX_ITEMS_CNT = 100;
const MAX_SIZE_IN_BYTES = 5 * 1024 * 1024;
const CACHE_TTL = 3600;

export default class AutocompleteCache
{
	static set(sourceCode: string, params: Object, data: Object)
	{
		const results = AutocompleteCache.#getAll(sourceCode);

		results.push({
			hash: AutocompleteCache.#makeParamsHash(params),
			data: data,
		});

		BX.localStorage.set(
			AutocompleteCache.#getStorageName(sourceCode),
			AutocompleteCache.#getResultsToStore(results),
			CACHE_TTL
		);
	}

	static get(sourceCode: string, params: Object): ?Object
	{
		const hash = AutocompleteCache.#makeParamsHash(params);
		const results = AutocompleteCache.#getAll(sourceCode);

		for (const result of results)
		{
			if (result && result.hash === hash)
			{
				return result;
			}
		}

		return null;
	}

	static #getResultsToStore(results: Array): Array
	{
		if (new Blob([JSON.stringify(results)]).size > MAX_SIZE_IN_BYTES)
		{
			return [];
		}

		if (results.length > MAX_ITEMS_CNT)
		{
			return results.slice(results.length - MAX_ITEMS_CNT);
		}

		return results;
	}

	static #getAll(sourceCode: string): Array
	{
		const currentResults = BX.localStorage.get(AutocompleteCache.#getStorageName(sourceCode));

		return Array.isArray(currentResults) ? currentResults : [];
	}

	static #makeParamsHash(params: Object): string
	{
		return md5(JSON.stringify(params));
	}

	static #getStorageName(sourceCode: string): string
	{
		return `location${sourceCode}AutocompleteCache`;
	}
}
