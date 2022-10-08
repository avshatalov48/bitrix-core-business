import {Point} from 'location.core';
import BaseRequester from './baserequester';

export default class ReverseRequester extends BaseRequester
{
	createUrl(params: { point: Point, zoom: number }): string
	{
		const zoom = params.zoom || 18;

		return `${this.serviceUrl}/?
			action=osmgateway.location.reverse
			&params[lat]=${params.point.latitude}
			&params[lon]=${params.point.longitude}
			&params[format]=json
			&params[zoom]=${zoom}
			&params[addressdetails]=0			
			&params[accept-language]=${this.sourceLanguageId}`;
	}
}