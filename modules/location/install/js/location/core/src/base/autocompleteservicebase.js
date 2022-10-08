// @flow

import {Location, Point} from 'location.core';

/**
 * Autocomplete search parameters
 */
export type AutocompleteServiceParams = {
	biasPoint: ?Point
};

/**
 * Base class for the source autocomplete services.
 */
export class AutocompleteServiceBase {
	/**
	 * @param {String} text
	 * @param {AutocompleteServiceParams} params
	 */
	// eslint-disable-next-line no-unused-vars
	autocomplete(text: string, params: AutocompleteServiceParams): Promise<Array<Location>, Error> {
		throw new Error('Method autocomplete() Must be implemented');
	}
}
