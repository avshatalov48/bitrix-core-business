/**
 * Bitrix Vue wrapper
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2021 Bitrix
 */

import {EventEmitter} from 'main.core.events';
import {Loc, Type} from "main.core";
import {RestClient, rest} from "rest.client";
import {PullClient, PULL as pull} from "pull.client";

export class BitrixVue
{
	static developerMode = false;

	constructor(VueVendor)
	{
		this._components = {};
		this._mutations = {};
		this._clones = {};

		this._instance = VueVendor;
		this._instance.use(this);

		this.event = new VueVendor;

		this.events = {
			restClientChange: 'RestClient::change',
			pullClientChange: 'PullClient::change',
		}
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
		BitrixVue.showNotice(
			'Method Vue.create is deprecated, use BitrixVue.createApp instead.\n'
			+ 'If you are using "el" property or .$mount(...) to bind your application, use .mount(...) instead.'
		);

		return this.createApp(params);
	}

	/**
	 * Create new Vue instance
	 *
	 * @param {Object} params - definition
	 *
	 * @see https://vuejs.org/v2/guide/
	 */
	createApp(params)
	{
		let instance = new this._instance(params);

		instance.mount = function(rootContainer: string|Element): object
		{
			return this.$mount(rootContainer);
		}

		return instance;
	}

	/**
	 * Register Vue component
	 *
	 * @param {String} id
	 * @param {Object} params
	 * @param {Object} [options]
	 *
	 * @see https://vuejs.org/v2/guide/components.html
	 */
	component(id, params, options = {})
	{
		if (!params.name)
		{
			params.name = id;
		}

		this._components[id] = Object.assign({}, params);
		this._components[id].bitrixOptions = {
			immutable: options.immutable === true,
			local: options.local === true,
		};

		if (typeof this._clones[id] !== 'undefined')
		{
			this._registerCloneComponent(id);
		}

		const componentParams = this._getFinalComponentParams(id);
		if (this.isLocal(id))
		{
			return componentParams;
		}

		return this._instance.component(id, componentParams);
	}

	/**
	 * Register Vue component
	 * @see https://vuejs.org/v2/guide/components.html
	 *
	 * @param {string} name
	 * @param {Object} definition
	 * @param {Object} [options]
	 *
	 * @returns {Object}
	 */
	localComponent(name, definition, options = {})
	{
		return this.component(name, definition, {...options, local: true});
	}

	/**
	 * Modify Vue component
	 *
	 * @param {String} id
	 * @param {Object} mutations
	 *
	 * @returns {Function|boolean} - function for remove this modification
	 */
	mutateComponent(id, mutations)
	{
		const mutable = this.isMutable(id);
		if (mutable === false)
		{
			BitrixVue.showNotice('You cannot mutate the component "'+id+'" because it is marked as immutable, perhaps cloning the component is fine for you.');
			return false;
		}

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

	/**
	 * Check exists Vue component
	 *
	 * @param {string} id
	 * @returns {boolean}
	 */
	isComponent(id)
	{
		return typeof this._components[id] !== 'undefined'
	}

	/**
	 * Check able to mutate Vue component
	 *
	 * @param id
	 * @returns {boolean|undefined} - undefined when component not registered yet.
	 */
	isMutable(id)
	{
		if (typeof this._components[id] === 'undefined')
		{
			return undefined;
		}

		return !this._components[id].bitrixOptions.immutable;
	}

	/**
	 * Check component is a local
	 *
	 * @param id
	 * @returns {boolean|undefined} - undefined when component not registered yet.
	 */
	isLocal(id)
	{
		if (typeof this._components[id] === 'undefined')
		{
			return undefined;
		}

		return this._components[id].bitrixOptions.local === true;
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
		return this._instance.extend(options)
	}

	/**
	 *	Defer the callback to be executed after the next DOM update cycle. Use it immediately after you have changed some data to wait for the DOM update.
	 *
	 * @param {Function} callback
	 * @param {Object} context
	 * @returns {Promise|void}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-nextTick
	 */
	nextTick(callback, context)
	{
		return this._instance.nextTick(callback, context);
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
		return this._instance.set(target, key, value);
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
		return this._instance.delete(target, key);
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
		return this._instance.directive(id, definition);
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
		return this._instance.filter(id, definition);
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
		return this._instance.use(plugin);
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
		return this._instance.mixin(mixin);
	}

	/**
	 * Make an object reactive. Internally, Vue uses this on the object returned by the data function.
	 *
	 * @param object
	 * @returns {*}
	 *
	 * @see https://vuejs.org/v2/api/#Vue-observable
	 */
	observable(object)
	{
		return this._instance.observable(object);
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
		return this._instance.compile(template);
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
		return this._instance.version;
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

	/**
	 * Getting a part of localization object for insertion into computed property.
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
		else if (Type.isObject(phrases) && Type.isObject(phrases.$Bitrix))
		{
			phrases = phrases.$Bitrix.Loc.getMessages();
		}

		if (Array.isArray(phrasePrefix))
		{
			for (let message in phrases)
			{
				if (!phrases.hasOwnProperty(message))
				{
					continue
				}
				if (!phrasePrefix.find((element) => message.toString().startsWith(element)))
				{
					continue;
				}
				result[message] = phrases[message];
			}
		}
		else
		{
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

	_getFinalComponentParams(id)
	{
		const mutations = this.isMutable(id)? this._mutations[id]: undefined;
		return this._getComponentParamsWithMutation(id, mutations);
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
	 * @private
	 * @param text
	 */
	static showNotice(text)
	{
		if (BitrixVue.developerMode)
		{
			console.warn('BitrixVue: '+text);
		}
	}

	/**
	 * @deprecated Special method for plugin registration
	 */
	install(app, options)
	{
		const bitrixVue = this;

		// 1. Init Bitrix public api
		const $Bitrix = {};

		// 1.1 Localization
		$Bitrix.Loc =
		{
			messages: {},

			getMessage: function(messageId: string): string
			{
				if (typeof this.messages[messageId] !== 'undefined')
				{
					return this.messages[messageId];
				}

				this.messages[messageId] = Loc.getMessage(messageId);

				return this.messages[messageId];
			},

			getMessages: function (): object
			{
				if (typeof BX.message !== 'undefined')
				{
					return {...BX.message, ...this.messages};
				}

				return {...this.messages};
			},

			setMessage: function(id: string | {[key: string]: string}, value?: string): void
			{
				if (Type.isString(id))
				{
					this.messages[id] = value;
				}

				if (Type.isObject(id))
				{
					for (const code in id)
					{
						if (id.hasOwnProperty(code))
						{
							this.messages[code] = id[code];
						}
					}
				}
			}
		};

		// 1.2  Application Data
		$Bitrix.Application =
		{
			instance: null,

			get: function(): Object
			{
				return this.instance;
			},
			set: function(instance: Object): void
			{
				this.instance = instance;
			},
		};

		// 1.3  Application Data
		$Bitrix.Data =
		{
			data: {},

			get: function(name: string, defaultValue?:any): any
			{
				return this.data[name] ?? defaultValue;
			},
			set: function(name: string, value: any): void
			{
				this.data[name] = value;
			}
		};

		// 1.4  Application EventEmitter
		$Bitrix.eventEmitter = new EventEmitter();
		$Bitrix.eventEmitter.setEventNamespace('vue:app:'+app._uid);

		// 1.5  Application RestClient
		$Bitrix.RestClient =
		{
			instance: null,

			get: function(): RestClient
			{
				return this.instance ?? rest;
			},
			set: function(instance: RestClient): void
			{
				this.instance = instance;
				$Bitrix.eventEmitter.emit(bitrixVue.events.restClientChange);
			},
			isCustom()
			{
				return this.instance !== null;
			}
		};

		// 1.6  Application PullClient
		$Bitrix.PullClient =
		{
			instance: null,

			get: function(): PullClient
			{
				return this.instance ?? pull;
			},
			set: function(instance: PullClient): void
			{
				this.instance = instance;
				$Bitrix.eventEmitter.emit(bitrixVue.events.pullClientChange);
			},
			isCustom()
			{
				return this.instance !== null;
			}
		};

		// 2. Apply global properties
		app.prototype.$bitrix = $Bitrix;

		app.mixin(
		{
			computed:
			{
				$Bitrix: function()
				{
					return this.$bitrix;
				},
			},
			mounted: function ()
			{
				if (!Type.isNil(this.$root.$bitrixApplication))
				{
					BitrixVue.showNotice("Store reference in global variables (like: this.$bitrixApplication) is deprecated, use this.$Bitrix.Data.set(...) instead.");
				}
				if (!Type.isNil(this.$root.$bitrixController))
				{
					BitrixVue.showNotice("Store reference in global variables (like: this.$bitrixController) is deprecated, use this.$Bitrix.Data.set(...) instead.");
				}
				if (!Type.isNil(this.$root.$bitrixMessages))
				{
					BitrixVue.showNotice("Store localization in global variable this.$bitrixMessages is deprecated, use this.$Bitrix.Log.setMessage(...) instead.");
				}
				if (!Type.isNil(this.$root.$bitrixRestClient))
				{
					BitrixVue.showNotice("Working with a Rest-client through an old variable this.$bitrixRestClient is deprecated, use this.$Bitrix.RestClient.get() instead.");
				}
				if (!Type.isNil(this.$root.$bitrixPullClient))
				{
					BitrixVue.showNotice("Working with a Pull-client through an old variable this.$bitrixPullClient is deprecated, use this.$Bitrix.PullClient.get() instead.");
				}
			}
		});
	}
}