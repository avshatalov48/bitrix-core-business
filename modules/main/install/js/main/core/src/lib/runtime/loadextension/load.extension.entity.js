import type {ExtensionOptions, State} from './type/load.extension.types';

import {
	loadAll,
	inlineScripts,
	externalStyles,
	externalScripts,
} from './lib/load.extension.utils';

import Type from '../../type';
import Reflection from '../../reflection';

export default class Extension
{
	name: string;
	state: State;
	inlineScripts: Array<string>;
	externalScripts: Array<string>;
	externalStyles: Array<string>;
	loadPromise: Promise<Extension>;
	config: {[key: string]: string};

	constructor(options: ExtensionOptions)
	{
		this.config = options.config || {};
		this.name = options.extension;
		this.state = 'scheduled';

		// eslint-disable-next-line
		const result = BX.processHTML(options.html || '');
		this.inlineScripts = result.SCRIPT.reduce(inlineScripts, []);
		this.externalScripts = result.SCRIPT.reduce(externalScripts, []);
		this.externalStyles = result.STYLE.reduce(externalStyles, []);
	}

	load(): Promise<Extension>
	{
		if (this.state === 'error')
		{
			this.loadPromise = this.loadPromise || Promise.resolve(this);
			console.warn('Extension', this.name, 'not found');
		}

		if (!this.loadPromise && this.state)
		{
			this.state = 'load';
			// eslint-disable-next-line
			this.inlineScripts.forEach(BX.evalGlobal);

			this.loadPromise = Promise
				.all([
					loadAll(this.externalScripts),
					loadAll(this.externalStyles),
				])
				.then(() => {
					this.state = 'loaded';

					if (Type.isPlainObject(this.config) && this.config.namespace)
					{
						return Reflection.getClass(this.config.namespace);
					}

					return window;
				});
		}

		return this.loadPromise;
	}
}