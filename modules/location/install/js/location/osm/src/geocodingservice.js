import {Location, GeocodingServiceBase, Point} from 'location.core';

export default class GeocodingService extends GeocodingServiceBase
{
	#searchRequester;
	#reverseRequester;

	constructor(params: Object)
	{
		super();
		this.#searchRequester = params.searchRequester;
		this.#reverseRequester = params.reverseRequester;
	}

	geocodeConcrete(addressString: string): Promise
	{
		return this.#searchRequester.request({query: addressString});
	}

	/**
	 * @param {Point} point
	 * @param {number} zoom 1 - 18
 	 * @return {*}
	 */
	reverse(point: Point, zoom: number): Promise<?Location>
	{
		return this.#reverseRequester.request({
			point: point,
			zoom: zoom
		});
	}
}