import loadAssets from './load-assets';
import Reflection from '../../../reflection';
import {
	fetchInlineScripts,
	fetchExternalScripts,
	fetchExternalStyles,
	fetchExtensionSettings,
	loadAll,
} from './utils';
import Type from '../../../type';

export type ExtensionOptions = {
	name: string,
	namespace?: string,
	loaded?: boolean,
};

export type ExtensionPromiseValue = {
	name: string,
	namespace: string,
	exports: any,
};

const defaultOptions = {
	loaded: false,
};

export default class Extension
{
	static State = {
		LOADED: 'LOADED',
		LOADING: 'LOADING',
	};

	#state: $Values<Extension.State> = Extension.State.LOADING;
	#name: string = '';
	#namespace: string = '';
	#promise: Promise<ExtensionPromiseValue> = null;

	constructor(options: ExtensionOptions)
	{
		const preparedOptions: ExtensionOptions = {
			...defaultOptions,
			...options,
		};

		this.#name = preparedOptions.name;
		this.#namespace = Type.isStringFilled(preparedOptions.namespace) ? preparedOptions.namespace : 'window';

		if (preparedOptions.loaded)
		{
			this.#state = Extension.State.LOADED;
		}
	}

	load(): Promise<ExtensionPromiseValue>
	{
		if (this.#state === Extension.State.LOADED && !this.#promise)
		{
			this.#promise = Promise.resolve(
				Reflection.getClass(this.#namespace),
			);
		}

		if (this.#promise)
		{
			return this.#promise;
		}

		this.#state = Extension.State.LOADING;
		this.#promise = new Promise((resolve) => {
			void loadAssets({ extension: [this.#name] })
				.then((assetsResult) => {
					if (!Type.isArrayFilled(assetsResult.data))
					{
						resolve(window);
					}

					const extensionData = assetsResult.data.at(0);
					if (
						Type.isPlainObject(extensionData.config)
						&& Type.isStringFilled(extensionData.config.namespace)
					)
					{
						this.#namespace = extensionData.config.namespace;
					}

					const result = BX.processHTML(extensionData.html || '');
					const inlineScripts = result.SCRIPT.reduce(fetchInlineScripts, []);
					const externalScripts = result.SCRIPT.reduce(fetchExternalScripts, []);
					const externalStyles = result.STYLE.reduce(fetchExternalStyles, []);
					const settingsScripts = fetchExtensionSettings(result.HTML);

					settingsScripts.forEach((entry: { extension: string, script: string }) => {
						document.body.insertAdjacentHTML('beforeend', entry.script);
					});

					const runScriptsBefore: Array<string> = inlineScripts.filter((script: string) => {
						return !script.startsWith('BX.Runtime.registerExtension');
					});
					const runScriptsAfter: Array<string> = inlineScripts.filter((script: string) => {
						return script.startsWith('BX.Runtime.registerExtension');
					});

					runScriptsBefore.forEach((script: string) => {
						BX.evalGlobal(script);
					});

					void Promise
						.all([
							loadAll(externalScripts),
							loadAll(externalStyles),
						])
						.then(() => {
							this.#state = Extension.State.LOADED;
							runScriptsAfter.forEach((script: string) => {
								BX.evalGlobal(script);
							});

							if (this.#namespace)
							{
								return Reflection.getClass(this.#namespace);
							}

							return window;
						})
						.then((exports) => {
							resolve(exports);
						});
				});
		});

		return this.#promise;
	}
}
