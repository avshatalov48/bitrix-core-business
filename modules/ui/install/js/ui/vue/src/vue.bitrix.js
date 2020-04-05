/**
 * Bitrix Vue wrapper
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import "main.polyfill.core";
import {VueVendorV2} from "ui.vue.vendor.v2";

class BitrixVue
{
	constructor()
	{
		this._components = {};
		this._mutations = {};
		this._clones = {};

		this.event = new VueVendorV2({});
	}

	/**
	 * Create new Vue instance
	 *
	 * @param {Object} params - definition
	 *
	 * @see https://vuejs.org/v2/guide/
	 */
	create(params)
	{
		return new VueVendorV2(params);
	}

	/**
	 * Register Vue component
	 *
	 * @param {String} id
	 * @param {Object} params
	 *
	 * @see https://vuejs.org/v2/guide/components.html
	 */
	component(id, params)
	{
		this._components[id] = Object.assign({}, params);

		if (typeof this._clones[id] !== 'undefined')
		{
			this._registerCloneComponent(id);
		}

		return VueVendorV2.component(id, this._getComponentParamsWithMutation(id, this._mutations[id]));
	}

	/**
	 * Modify Vue component
	 *
	 * @param {String} id
	 * @param {Object} mutations
	 *
	 * @returns {Function} - function for remove this modification
	 */
	mutateComponent(id, mutations)
	{
		if (typeof this._mutations[id] === 'undefined')
		{
			this._mutations[id] = [];
		}

		this._mutations[id].push(mutations);

		if (typeof this._components[id] !== 'undefined')
		{
			this.component(id, this._components[id]);
		}

		return () => {
			this._mutations[id] = this._mutations[id].filter((element) => element !== mutations);
		};
	}

	/**
	 * Clone Vue component
	 *
	 * @param {string} id
	 * @param {string} sourceId
	 * @param {object} mutations
	 * @returns {boolean}
	 */
	cloneComponent(id, sourceId, mutations)
	{
		if (typeof this._clones[sourceId] === 'undefined')
		{
			this._clones[sourceId] = {};
		}

		this._clones[sourceId][id] = {id, sourceId, mutations};

		if (typeof this._components[sourceId] !== 'undefined')
		{
			this._registerCloneComponent(sourceId, id);
		}

		return true;
	}

	isComponent(id)
	{
		return typeof this._components[id] !== 'undefined'
	}

	/**
	 * Create a "subclass" of the base Vue constructor.
	 *
	 * @param options
	 * @returns {*}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-extend
	 */
	extend(options)
	{
		return VueVendorV2.extend(options)
	}

	/**
	 *	Defer the callback to be executed after the next DOM update cycle. Use it immediately after you’ve changed some data to wait for the DOM update.
	 *
	 * @param {Function} callback
	 * @param {Object} context
	 * @returns {Promise|void}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-nextTick
	 */
	nextTick(callback, context)
	{
		return VueVendorV2.nextTick(callback, context);
	}

	/**
	 * Adds a property to a reactive object, ensuring the new property is also reactive, so triggers view updates.
	 *
	 * @param {Object|Array} target
	 * @param {String|Number} key
	 * @param {*} value
	 * @returns {*}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-set
	 */
	set(target, key, value)
	{
		return VueVendorV2.set(target, key, value);
	}

	/**
	 * Delete a property on an object. If the object is reactive, ensure the deletion triggers view updates.
	 *
	 * @param {Object|Array} target
	 * @param {String|Number} key
	 * @returns {*}
	 */
	delete(target, key)
	{
		return VueVendorV2.delete(target, key);
	}

	/**
	 * Register or retrieve a global directive.
	 *
	 * @param {String} id
	 * @param {Object|Function} definition
	 * @returns {*}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-directive
	 */
	directive(id, definition)
	{
		return VueVendorV2.directive(id, definition);
	}

	/**
	 * Register or retrieve a global filter.
	 *
	 * @param id
	 * @param definition
	 * @returns {*}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-filter
	 */
	filter(id, definition)
	{
		return VueVendorV2.filter(id, definition);
	}

	/**
	 * Install a Vue.js plugin.
	 *
	 * @param {Object|Function} plugin
	 * @returns {*}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-use
	 */
	use(plugin)
	{
		return VueVendorV2.use(plugin);
	}

	/**
	 * Apply a mixin globally, which affects every Vue instance created afterwards.
	 *
	 * @param {Object} mixin
	 * @returns {*|Function|Object}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-mixin
	 */
	mixin(mixin)
	{
		return VueVendorV2.mixin(mixin);
	}

	/**
	 * Compiles a template string into a render function.
	 *
	 * @param template
	 * @returns {*}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-compile
	 */
	compile(template)
	{
		return VueVendorV2.compile(template);
	}

	/**
	 * Provides the installed version of Vue as a string.
	 *
	 * @returns {String}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-version
	 */
	version()
	{
		return VueVendorV2.version;
	}

	/**
	 *
	 * @param {String} phrasePrefix
	 * @param {Object|null} phrases
	 * @returns {ReadonlyArray<any>}
	 */
	getFilteredPhrases(phrasePrefix, phrases = null)
	{
		let result = {};

		if (!phrases && typeof BX.message !== 'undefined')
		{
			phrases = BX.message;
		}

		for (let message in phrases)
		{
			if (!phrases.hasOwnProperty(message))
			{
				continue
			}
			if (!message.startsWith(phrasePrefix))
			{
				continue;
			}
			result[message] = phrases[message];
		}

		return Object.freeze(result);
	}

	/**
	 * Return component params with mutation
	 *
	 * @param {String} componentId
	 * @param {Object} mutations
	 * @returns {null|Object}
	 *
	 * @private
	 */
	_getComponentParamsWithMutation(componentId, mutations)
	{
		if (typeof this._components[componentId] === 'undefined')
		{
			return null;
		}

		let componentParams = Object.assign({}, this._components[componentId]);

		if (typeof mutations === 'undefined')
		{
			return componentParams;
		}

		mutations.forEach(mutation =>
		{
			componentParams = this._applyMutation(
				this._cloneObjectWithoutDuplicateFunction(componentParams, mutation),
			mutation);
		});

		return componentParams;
	}

	/**
	 * Register clone of components
	 *
	 * @param {String} sourceId
	 * @param {String|null} [id]
	 *
	 * @private
	 */
	_registerCloneComponent(sourceId, id = null)
	{
		let components = [];
		if (id)
		{
			if (typeof this._clones[sourceId][id] !== 'undefined')
			{
				components.push(this._clones[sourceId][id]);
			}
		}
		else
		{
			for (let cloneId in this._clones[sourceId])
			{
				if (!this._clones[sourceId].hasOwnProperty(cloneId))
				{
					continue;
				}
				components.push(this._clones[sourceId][cloneId]);
			}
		}

		components.forEach(element =>
		{
			let mutations = [];

			if (typeof this._mutations[element.sourceId] !== 'undefined')
			{
				mutations = mutations.concat(this._mutations[element.sourceId]);
			}

			mutations.push(element.mutations);

			let componentParams = this._getComponentParamsWithMutation(element.sourceId, mutations);
			if (!componentParams)
			{
				return false;
			}

			this.component(element.id, componentParams);
		});
	}

	/**
	 * Clone object without duplicate function for apply mutation
	 *
	 * @param objectParams
	 * @param mutation
	 * @param level
	 * @private
	 */
	_cloneObjectWithoutDuplicateFunction(objectParams = {}, mutation = {}, level = 1)
	{
		let object = {};

		for (let param in objectParams)
		{
			if (!objectParams.hasOwnProperty(param))
			{
				continue;
			}
			if (typeof objectParams[param] === 'string')
			{
				object[param] = objectParams[param];
			}
			else if (Object.prototype.toString.call(objectParams[param]) === '[object Array]')
			{
				object[param] = [].concat(objectParams[param]);
			}
			else if (typeof objectParams[param] === 'object')
			{
				if (objectParams[param] === null)
				{
					object[param] = null;
				}
				else if (typeof mutation[param] === 'object')
				{
					object[param] = this._cloneObjectWithoutDuplicateFunction(objectParams[param], mutation[param], (level+1))
				}
				else
				{
					object[param] = Object.assign({}, objectParams[param])
				}
			}
			else if (typeof objectParams[param] === 'function')
			{
				if (typeof mutation[param] !== 'function')
				{
					object[param] = objectParams[param];
				}
				else if (level > 1)
				{
					object['parent'+param[0].toUpperCase()+param.substr(1)] = objectParams[param];
				}
				else
				{
					if (typeof object['methods'] === 'undefined')
					{
						object['methods'] = {};
					}
					object['methods']['parent'+param[0].toUpperCase()+param.substr(1)] = objectParams[param];

					if (typeof objectParams['methods'] === 'undefined')
					{
						objectParams['methods'] = {};
					}
					objectParams['methods']['parent'+param[0].toUpperCase()+param.substr(1)] = objectParams[param];
				}
			}
			else if (typeof objectParams[param] !== 'undefined')
			{
				object[param] = objectParams[param];
			}
		}

		return object;
	}

	/**
	 * Apply mutation
	 *
	 * @param clonedObject
	 * @param mutation
	 * @private
	 */
	_applyMutation(clonedObject = {}, mutation = {})
	{
		let object = Object.assign({}, clonedObject);

		for (let param in mutation)
		{
			if (!mutation.hasOwnProperty(param))
			{
				continue;
			}

			if (typeof mutation[param] === 'string')
			{
				if (typeof object[param] === 'string')
				{
					object[param] = mutation[param].replace(`#PARENT_${param.toUpperCase()}#`, object[param]);
				}
				else
				{
					object[param] = mutation[param].replace(`#PARENT_${param.toUpperCase()}#`, '');
				}
			}
			else if (Object.prototype.toString.call(mutation[param]) === '[object Array]')
			{
				object[param] = [].concat(mutation[param]);
			}
			else if (typeof mutation[param] === 'object')
			{
				if (typeof object[param] === 'object')
				{
					object[param] = this._applyMutation(object[param], mutation[param])
				}
				else
				{
					object[param] = mutation[param];
				}
			}
			else
			{
				object[param] = mutation[param];
			}
		}

		return object;
	}

	/**
	 * Test node for compliance with parameters
	 *
	 * @param obj
	 * @param params
	 * @returns {boolean}
	 */
	testNode(obj, params)
	{
		if (!params || typeof params !== 'object')
		{
			return true;
		}

		let i,j,len;

		for (i in params)
		{
			if(!params.hasOwnProperty(i))
			{
				continue;
			}

			switch(i)
			{
				case 'tag':
				case 'tagName':
					if (typeof params[i] === "string")
					{
						if (obj.tagName.toUpperCase() !== params[i].toUpperCase())
						{
							return false;
						}
					}
					else if (params[i] instanceof RegExp)
					{
						if (!params[i].test(obj.tagName))
						{
							return false;
						}
					}
				break;

				case 'class':
				case 'className':
					if (typeof params[i] === "string")
					{
						if (!obj.classList.contains(params[i].trim()))
						{
							return false;
						}
					}
					else if (params[i] instanceof RegExp)
					{
						if (
							typeof obj.className !== "string"
							|| !params[i].test(obj.className)
						)
						{
							return false;
						}
					}
				break;

				case 'attr':
				case 'attrs':
				case 'attribute':
					if (typeof params[i] === "string")
					{
						if (!obj.getAttribute(params[i]))
						{
							return false;
						}
					}
					else if (params[i] && Object.prototype.toString.call(params[i]) === "[object Array]")
					{
						for (j = 0, len = params[i].length; j < len; j++)
						{
							if (params[i][j] && !obj.getAttribute(params[i][j]))
							{
								return false;
							}
						}
					}
					else
					{
						for (j in params[i])
						{
							if(!params[i].hasOwnProperty(j))
							{
								continue
							}

							let value = obj.getAttribute(j);
							if (typeof value !== "string")
							{
								return false;
							}

							if (params[i][j] instanceof RegExp)
							{
								if (!params[i][j].test(value))
								{
									return false;
								}
							}
							else if (value !== '' + params[i][j])
							{
								return false;
							}
						}
					}
				break;

				case 'property':
				case 'props':
					if (typeof params[i] === "string")
					{
						if (!obj[params[i]])
						{
							return false;
						}
					}
					else if (params[i] && Object.prototype.toString.call(params[i]) == "[object Array]")
					{
						for (j = 0, len = params[i].length; j < len; j++)
						{
							if (params[i][j] && !obj[params[i][j]])
							{
								return false;
							}
						}
					}
					else
					{
						for (j in params[i])
						{
							if(!params[i].hasOwnProperty(j))
							{
								continue
							}

							if (typeof params[i][j] === "string")
							{
								if (obj[j] != params[i][j])
								{
									return false;
								}
							}
							else if (params[i][j] instanceof RegExp)
							{
								if (
									typeof obj[j] !== "string"
									|| !params[i][j].test(obj[j])
								)
								{
									return false;
								}
							}
						}
					}
				break;
			}
		}

		return true;
	}
}

let Vue = new BitrixVue;

export {
	Vue,
	VueVendorV2 as VueVendor
};