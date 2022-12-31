import type Uploader from '../uploader';

export default class Filter
{
	#uploader: Uploader = null;

	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		this.#uploader = uploader;
	}

	getUploader(): Uploader
	{
		return this.#uploader;
	}

	/**
	 * @abstract
	 */
	apply(...args): Promise
	{
		throw new Error('You must implement apply() method.');
	}
}
