/**
 * Loads google source services
 */
export default class Loader
{
	static #loadingPromise = null;

	static #createSrc(apiKey, languageId)
	{
		return 'https://maps.googleapis.com/maps/api/js'
			+ `?key=${apiKey}`
			+ '&libraries=places'
			+ `&language=${languageId}`
			+ `&region=${this.#getRegion(languageId)}`;
	}

	static #getRegion(languageId: string): string
	{
		const map = {
			'en': 'US',
			'uk': 'UA',
			'zh': 'CN',
			'ja': 'JP',
			'vi': 'VN',
			'ms': 'MY',
			'hi': 'IN'
		};

		return typeof map[languageId] !== 'undefined' ? map[languageId] : languageId.toUpperCase();
	}

	/**
	 * Loads google services
	 * @param {string} apiKey
	 * @param {string} languageId
	 * @returns {Promise}
	 */
	static load(apiKey: string, languageId: string): Promise
	{
		if (Loader.#loadingPromise === null)
		{
			Loader.#loadingPromise = new Promise((resolve) => {

				BX.load(
					[Loader.#createSrc(apiKey, languageId)],
					() => {
						resolve();
					}
				);
			});
		}

		return Loader.#loadingPromise;
	}
}