import * as utils from './lib/load.extension.utils';
import type Extension from './load.extension.entity';

type names = string | Array<string>;
type result = Promise<Array<Extension>>;

/**
 * Loads extensions asynchronously
 * @param {string|Array<string>} extension
 * @return {Promise<Array<Extension>>}
 */
export default function loadExtension(extension: names): result
{
	const extensions = utils.makeIterable(extension);
	const isAllInitialized = utils.isAllInitialized(extensions);

	if (isAllInitialized)
	{
		const initializedExtensions = extensions.map(utils.getInitialized);
		return utils.loadExtensions(initializedExtensions)
			.then(utils.mergeExports);
	}

	return utils.request({extension: extensions})
		.then(utils.prepareExtensions)
		.then(utils.loadExtensions)
		.then(utils.mergeExports);
}