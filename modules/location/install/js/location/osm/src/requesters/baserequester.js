import {Runtime} from 'main.core';

export default class BaseRequester
{
	languageId;
	sourceLanguageId;
	serviceUrl;

	#responseConverter;
	#hostName;
	#tokenContainer;

	constructor(props)
	{
		this.serviceUrl = props.serviceUrl;
		this.languageId = props.languageId;
		this.sourceLanguageId = props.sourceLanguageId;
		this.#responseConverter = props.responseConverter;
		this.#hostName = props.hostName;
		this.#tokenContainer = props.tokenContainer;
	}

	/**
	 * @param params
	 * @return string
	 */
	// eslint-disable-next-line no-unused-vars
	createUrl(params: Object): string
	{
		throw new Error('Not implemented');
	}

	/**
	 *
	 * @param params
	 * @return {Promise<Array<Location> | Location | null | * | *[] | void>}
	 */
	request(params: {}): Promise<JSON>
	{
		return this.#fetch(params)
			.then((response) =>
			{
				return response ? this.#responseConverter.convertResponse(response, params) : [];
			})
			.catch((response) => {
				console.error(response);
			});
	}

	/**
	 * Sends request to server
	 * @param {Object} params
	 * @param {boolean} isUnAuth
	 * @return {Promise<Object>} Object is response which was converted from json string to object
	 */
	#fetch(params: Object, isUnAuth: boolean = false): Promise<Object>
	{
		return fetch(this.createUrl(params), {
			method: 'GET',
			headers: new Headers({
				'Authorization': `Bearer ${this.#tokenContainer.token}`,
				'Bx-Location-Osm-Host': this.#hostName,
			}),
			referrerPolicy: 'no-referrer'
		})
			.then((response) => {

				if (response.status === 200)
				{
					return response.json();
				}

				if (response.status === 401 && !isUnAuth)
				{
					return this.#processUnauthorizedResponse(params);
				}

				console.error(`Response status: ${response.status}`);

				response.text()
					.then(
						(text) => { Runtime.debug(text); }
					);

				return null;
			});
	}

	/**
	 * Method process the situation then the token was expired
	 *
	 * @param params
	 * @return {*}
	 */
	#processUnauthorizedResponse(params: {})
	{
		return this.#tokenContainer.refreshToken()
			.then(() => {
				return this.#fetch(params, true);
			});
	}
}