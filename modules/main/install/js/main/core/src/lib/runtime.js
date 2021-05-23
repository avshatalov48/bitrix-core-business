import Type from './type';
import debug from './runtime/debug';
import loadExtension from './runtime/loadextension/load.extension';
import clone from './runtime/clone';
import {
	externalScripts,
	externalStyles,
	inlineScripts,
	loadAll,
} from './runtime/loadextension/lib/load.extension.utils';
import merge from './runtime/merge';
import createComparator from './runtime/create-comparator';

/**
 * @memberOf BX
 */
export default class Runtime
{
	static debug = debug;
	static loadExtension = loadExtension;
	static clone = clone;

	static debounce(func: Function, wait: number = 0, context = null): Function
	{
		let timeoutId;

		return function debounced(...args)
		{
			if (Type.isNumber(timeoutId))
			{
				clearTimeout(timeoutId);
			}

			timeoutId = setTimeout(() => {
				func.apply((context || this), args);
			}, wait);
		};
	}

	static throttle(func: Function, wait: number = 0, context = null): Function
	{
		let timer = 0;
		let invoke;

		return function wrapper(...args)
		{
			invoke = true;

			if (!timer)
			{
				const q = function q()
				{
					if (invoke)
					{
						func.apply((context || this), args);
						invoke = false;
						timer = setTimeout(q, wait);
					}
					else
					{
						timer = null;
					}
				};
				q();
			}
		};
	}

	static html(node: HTMLElement, html, params = {}): Promise | string
	{
		if (Type.isNil(html) && Type.isDomNode(node))
		{
			return node.innerHTML;
		}

		// eslint-disable-next-line
		const parsedHtml = BX.processHTML(html);
		const externalCss = parsedHtml.STYLE.reduce(externalStyles, []);
		const externalJs = parsedHtml.SCRIPT.reduce(externalScripts, []);
		const inlineJs = parsedHtml.SCRIPT.reduce(inlineScripts, []);

		if (Type.isDomNode(node))
		{
			if (params.htmlFirst || (!externalJs.length && !externalCss.length))
			{
				if (params.useAdjacentHTML)
				{
					node.insertAdjacentHTML('beforeend', parsedHtml.HTML);
				}
				else
				{
					node.innerHTML = parsedHtml.HTML;
				}
			}
		}

		return Promise
			.all([
				loadAll(externalJs),
				loadAll(externalCss),
			])
			.then(() => {
				if (Type.isDomNode(node) && (externalJs.length > 0 || externalCss.length > 0))
				{
					if (params.useAdjacentHTML)
					{
						node.insertAdjacentHTML('beforeend', parsedHtml.HTML);
					}
					else
					{
						node.innerHTML = parsedHtml.HTML;
					}
				}

				// eslint-disable-next-line
				inlineJs.forEach(script => BX.evalGlobal(script));

				if (Type.isFunction(params.callback))
				{
					params.callback();
				}
			});
	}

	/**
	 * Merges objects or arrays
	 * @param targets
	 * @return {any}
	 */
	static merge(...targets)
	{
		if (Type.isArray(targets[0]))
		{
			targets.unshift([]);
		}
		else if (Type.isObject(targets[0]))
		{
			targets.unshift({});
		}

		return targets.reduce((acc, item) => {
			return merge(acc, item);
		}, targets[0]);
	}

	static orderBy(
		collection: Array<{[key: string]: any}> | {[key: string]: {[key: string]: any}},
		fields: Array<string> = [],
		orders: Array<string> = [],
	)
	{
		const comparator = createComparator(fields, orders);
		return Object.values(collection).sort(comparator);
	}

	static destroy(target, errorMessage = 'Object is destroyed')
	{
		if (Type.isObject(target))
		{
			const onPropertyAccess = () => {
				throw new Error(errorMessage);
			};
			const ownProperties = Object.keys(target);
			const prototypeProperties = (() => {
				const targetPrototype = Object.getPrototypeOf(target);
				if (Type.isObject(targetPrototype))
				{
					return Object.getOwnPropertyNames(targetPrototype);
				}

				return [];
			})();

			const uniquePropertiesList = [
				...new Set([...ownProperties, ...prototypeProperties]),
			];

			uniquePropertiesList
				.filter((name) => {
					const descriptor = Object.getOwnPropertyDescriptor(target, name);
					return (
						!/__(.+)__/.test(name)
						&& (
							!Type.isObject(descriptor)
							|| descriptor.configurable === true
						)
					);
				})
				.forEach((name) => {
					Object.defineProperty(target, name, {
						get: onPropertyAccess,
						set: onPropertyAccess,
						configurable: false,
					});
				});

			Object.setPrototypeOf(target, null);
		}
	}
}