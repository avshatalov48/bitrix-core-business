import OSM from '../osm';

/**
 * Abstract class BaseResponseConverter
 */
export default class BaseResponseConverter
{
	languageId;
	sourceCode;

	constructor(props)
	{
		this.languageId = props.languageId;
		this.sourceCode = props.sourceCode ?? OSM.code;
	}

	/**
	 *
	 * @param response
	 * @param params
	 * @return Array<Location>|Location|null
	 */
	// eslint-disable-next-line no-unused-vars
	convertResponse(response: {}, params: {})
	{
		throw new Error('Method "convertResponse()" not implemented');
	}
}