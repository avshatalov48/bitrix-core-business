import Type from './type';

/**
 * @memberOf BX
 */
export default class Reflection
{
	/**
	 * Gets link to function by function name
	 * @param className
	 * @return {?Function}
	 */
	static getClass(className: string | Function): Function | null
	{
		if (Type.isString(className) && !!className)
		{
			let classFn = null;
			let currentNamespace = window;
			const namespaces = className.split('.');

			for (let i = 0; i < namespaces.length; i += 1)
			{
				const namespace = namespaces[i];

				if (!currentNamespace[namespace])
				{
					return null;
				}

				currentNamespace = currentNamespace[namespace];
				classFn = currentNamespace;
			}

			return classFn;
		}

		if (Type.isFunction(className))
		{
			return className;
		}

		return null;
	}

	/**
	 * Creates a namespace or returns a link to a previously created one
	 * @param {String} namespaceName
	 * @return {Object<string, any> | Function | null}
	 */
	static namespace(namespaceName: string): {[key: string]: any} | Function
	{
		let parts = namespaceName.split('.');
		let parent = window.BX;

		if (parts[0] === 'BX')
		{
			parts = parts.slice(1);
		}

		for (let i = 0; i < parts.length; i += 1)
		{
			if (Type.isUndefined(parent[parts[i]]))
			{
				parent[parts[i]] = {};
			}

			parent = parent[parts[i]];
		}

		return parent;
	}
}