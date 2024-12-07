/* eslint-disable */
;(function() {

	if (
		typeof this.BX !== 'undefined'
		&& typeof this.BX.Vue3 !== 'undefined'
		&& typeof this.BX.Vue3.BitrixVue !== 'undefined'
	)
	{
		console.warn('BX.Vue3.BitrixVue already loaded.');
		return;
	}

this.BX = this.BX || {};
(function (exports,main_core_events,main_core,rest_client,pull_client,ui_vue3) {
	'use strict';

	/*!
	 * Utilities from VueUse collection
	 * (c) 2019-2022 Anthony Fu
	 * Released under the MIT License.
	 *
	 * @source: https://github.com/vueuse/vueuse/blob/main/packages/shared/tryOnScopeDispose/index.ts
	 * @source: https://github.com/vueuse/vueuse/blob/main/packages/rxjs/useObservable/index.ts
	 */
	function tryOnScopeDispose(fn) {
	  if (ui_vue3.getCurrentScope()) {
	    ui_vue3.onScopeDispose(fn);
	    return true;
	  }
	  return false;
	}
	function useObservable(observable, options) {
	  const value = ui_vue3.ref(options == null ? void 0 : options.initialValue);
	  const subscription = observable.subscribe({
	    next: val => value.value = val,
	    error: options == null ? void 0 : options.onError
	  });
	  tryOnScopeDispose(() => {
	    subscription.unsubscribe();
	  });
	  return value;
	}

	/**
	 * Bitrix Vue3 plugin
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2021 Bitrix
	 */
	var _getComponentParamsWithMutation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getComponentParamsWithMutation");
	var _getFinalComponentParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFinalComponentParams");
	var _cloneObjectBeforeApplyMutation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cloneObjectBeforeApplyMutation");
	var _applyMutation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyMutation");
	class BitrixVue {
	  constructor() {
	    Object.defineProperty(this, _applyMutation, {
	      value: _applyMutation2
	    });
	    Object.defineProperty(this, _cloneObjectBeforeApplyMutation, {
	      value: _cloneObjectBeforeApplyMutation2
	    });
	    Object.defineProperty(this, _getFinalComponentParams, {
	      value: _getFinalComponentParams2
	    });
	    Object.defineProperty(this, _getComponentParamsWithMutation, {
	      value: _getComponentParamsWithMutation2
	    });
	    this.components = {};
	    this.proxyComponents = {};
	    this.finalComponents = {};
	    this.cloneCounter = 0;
	    this.cloneComponents = {};
	    this.mutations = {};
	    this.developerMode = false;
	    this.events = {
	      restClientChange: 'RestClient::change',
	      pullClientChange: 'PullClient::change'
	    };
	    const settings = main_core.Extension.getSettings('ui.vue3');
	    this.localizationMode = settings.get('localizationDebug', false) ? 'development' : 'production';
	  }

	  /**
	   * Create new Vue application
	   * @see https://vuejs.org/api/application.html
	   *
	   * @param {BitrixVueComponentProps} rootComponent - definition
	   * @param {{[key: string]: any}|null} rootProps - definition
	   * @returns VueCreateAppResult
	   */
	  createApp(rootComponent, rootProps) {
	    /* Note: method will be replaced with Vue.createApp */
	    return {
	      config: {},
	      directive: () => {},
	      mixin: () => {},
	      provide: () => {},
	      mount: () => {},
	      unmount: () => {},
	      use: () => {}
	    };
	  }

	  /**
	   * Define BitrixVue component
	   * @see https://vuejs.org/api/component-instance.html
	   *
	   * @param {string} name
	   * @param {BitrixVueComponentProps} definition
	   * @returns {BitrixVueComponentProxy}
	   */
	  mutableComponent(name, definition) {
	    this.components[name] = Object.assign({}, definition);
	    this.components[name].bitrixVue = {
	      name
	    };
	    this.finalComponents[name] = babelHelpers.classPrivateFieldLooseBase(this, _getFinalComponentParams)[_getFinalComponentParams](name);
	    this.proxyComponents[name] = new Proxy(this.finalComponents[name], {
	      get: function (target, property) {
	        if (!main_core.Type.isUndefined(this.finalComponents[target.bitrixVue.name]) && !main_core.Type.isUndefined(this.finalComponents[target.bitrixVue.name][property])) {
	          return this.finalComponents[target.bitrixVue.name][property];
	        }
	        return Reflect.get(...arguments);
	      }.bind(this)
	    });
	    return this.proxyComponents[name];
	  }

	  /**
	   * Get BitrixVue component with mutations
	   * @see https://vuejs.org/api/component-instance.html
	   *
	   * @param {string} name
	   * @param {boolean} silentMode
	   *
	   * @returns {BitrixVueComponentProps}
	   */
	  getMutableComponent(name, silentMode = false) {
	    if (!this.isComponent(name)) {
	      if (!silentMode) {
	        this.showNotice('Component "' + name + '" is not registered yet.');
	      }
	      return null;
	    }
	    const component = babelHelpers.classPrivateFieldLooseBase(this, _getFinalComponentParams)[_getFinalComponentParams](name);
	    for (const property in component) {
	      if (!component.hasOwnProperty(property)) {
	        continue;
	      }
	      this.proxyComponents[name][property] = component[property];
	    }
	    return this.finalComponents[name];
	  }

	  /**
	   * Define Async component
	   * @see https://vuejs.org/guide/components/async.html
	   *
	   * @param extension {string}
	   * @param componentExportName {string}
	   * @param options {VueAsyncComponentOptions|null}
	   * @return {Promise<BitrixVueComponentProps>}
	   */
	  defineAsyncComponent(extension, componentExportName, options) {
	    let loader = () => new Promise((resolve, reject) => {
	      main_core.Runtime.loadExtension(extension).then(exports => {
	        if (!main_core.Type.isUndefined(exports[componentExportName])) {
	          resolve(exports[componentExportName]);
	        } else {
	          resolve({
	            template: `
							<div style="display: inline-block; border: 1px dashed red; padding: 5px; margin: 5px;">
								Extension <strong>${extension}</strong> or export variable <strong>${componentExportName}</strong> is not found!
							</div>
						`
	          });
	        }
	      });
	    });
	    if (!main_core.Type.isObjectLike(options)) {
	      return ui_vue3.defineAsyncComponent(loader);
	    }
	    if (!main_core.Type.isObjectLike(options.loadingComponent)) {
	      return ui_vue3.defineAsyncComponent(() => new Promise((resolve, reject) => {
	        resolve({
	          template: `
						<div style="display: inline-block; border: 1px dashed red; padding: 5px; margin: 5px;">
							Extension <strong>${extension}</strong> was not loaded due to a configuration error. Property <strong>loadingComponent</strong> is not defined.
						</div>
					`
	        });
	      }));
	    }

	    // this case is for development purposes only
	    if (main_core.Type.isInteger(options.delayLoadExtension)) {
	      const timeout = options.delayLoadExtension;
	      const previousLoader = loader;
	      delete options.delayLoadExtension;
	      loader = () => new Promise((resolve, reject) => {
	        setTimeout(() => {
	          previousLoader().then(component => resolve(component));
	        }, timeout);
	      });
	    }
	    return ui_vue3.defineAsyncComponent({
	      loader,
	      ...options
	    });
	  }

	  /**
	   * Mutate Vue component
	   *
	   * @param {String|BitrixVueComponentProxy} source - name or definition
	   * @param {Object} mutations
	   * @returns {boolean}
	   */
	  mutateComponent(source, mutations) {
	    if (main_core.Type.isString(source)) {
	      if (main_core.Type.isUndefined(this.mutations[source])) {
	        this.mutations[source] = [];
	      }
	      this.mutations[source].push(mutations);
	      this.getMutableComponent(source, true);
	      return true;
	    }
	    if (main_core.Type.isPlainObject(source) && !main_core.Type.isUndefined(source.bitrixVue)) {
	      return this.mutateComponent(source.bitrixVue.name, mutations);
	    }
	    this.showError(`You can not mutate classic Vue components. If you need to mutate, use BitrixVue.cloneComponent instead.`, source, mutations);
	    return false;
	  }

	  /**
	   * Clone Vue component
	   *
	   * @param {string|object} source - name or definition
	   * @param {BitrixVueComponentProps} mutations
	   * @returns {BitrixVueComponentProxy|null}
	   */
	  cloneComponent(source, mutations) {
	    if (main_core.Type.isString(source)) {
	      const definition = babelHelpers.classPrivateFieldLooseBase(this, _getComponentParamsWithMutation)[_getComponentParamsWithMutation](source, [mutations]);
	      if (definition) {
	        return definition;
	      }
	      this.cloneCounter += 1;
	      const component = {
	        bitrixVue: {
	          source,
	          cloneCounter: this.cloneCounter,
	          mutations
	        }
	      };
	      return new Proxy(component, {
	        get: function (target, property, receiver) {
	          let component;
	          if (main_core.Type.isUndefined(this.cloneComponents[target.bitrixVue.cloneCounter])) {
	            component = babelHelpers.classPrivateFieldLooseBase(this, _getComponentParamsWithMutation)[_getComponentParamsWithMutation](target.bitrixVue.source, [target.bitrixVue.mutations]);
	            if (component) {
	              this.cloneComponents[target.bitrixVue.cloneCounter] = component;
	            }
	          } else {
	            component = this.cloneComponents[target.bitrixVue.cloneCounter];
	          }
	          if (!component) {
	            if (property === 'template') {
	              this.showError(`Clone component #${target.bitrixVue.cloneCounter} is failed. Component ${target.bitrixVue.source} is not register yet.`, target.bitrixVue);
	              if (this.developerMode) {
	                return `
									<div style="display: inline-block; border: 1px dashed red; padding: 5px; margin: 5px;">
										The cloned component <strong>#${target.bitrixVue.cloneCounter}</strong> is not shown because the original component <strong>${target.bitrixVue.source}</strong> was not registered.
									</div>
								`;
	              }
	              return `<!-- Placeholder for clone component #${target.bitrixVue.cloneCounter}. Component ${target.bitrixVue.source} was not registered. -->`;
	            }
	            return Reflect.get(...arguments);
	          }
	          if (!main_core.Type.isUndefined(component[property])) {
	            return component[property];
	          }
	          return Reflect.get(...arguments);
	        }.bind(this)
	      });
	    }
	    if (main_core.Type.isPlainObject(source) && !main_core.Type.isUndefined(source.bitrixVue)) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _getComponentParamsWithMutation)[_getComponentParamsWithMutation](source.bitrixVue.name, [mutations]);
	    }
	    if (main_core.Type.isPlainObject(source)) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _applyMutation)[_applyMutation](babelHelpers.classPrivateFieldLooseBase(this, _cloneObjectBeforeApplyMutation)[_cloneObjectBeforeApplyMutation](source, mutations), mutations);
	    }
	    return null;
	  }

	  /**
	   * Check exists Vue component
	   *
	   * @param {string} name
	   * @returns {boolean}
	   */
	  isComponent(name) {
	    return !main_core.Type.isUndefined(this.components[name]);
	  }

	  /**
	   * @deprecated
	   */
	  isMutable() {
	    this.showNotice('Method BitrixVue.isMutable is deprecated, remove usages.');
	    return true;
	  }

	  /**
	   * @deprecated
	   */
	  isLocal() {
	    this.showNotice('Method BitrixVue.isLocal is deprecated, remove usages.');
	    return false;
	  }

	  /**
	   * @deprecated
	   */
	  component(name) {
	    this.showError('Method BitrixVue.component is deprecated, use Vue.component or BitrixVue.mutableComponent. Component "' + name + '" was not registered.');
	  }

	  /**
	   * @deprecated
	   */
	  localComponent(name, definition) {
	    this.showNotice('Method BitrixVue.localComponent is deprecated, use Vue.mutableComponent instead. Component "' + name + '" has been registered, but this behavior will be removed in the future.');
	    return this.mutableComponent(name, definition);
	  }

	  /**
	   * @deprecated
	   */
	  directive(name) {
	    this.showError('Method BitrixVue.directive is deprecated, use Vue.directive (from ui.vue3 extension import). Directive "' + name + '" was not registered.');
	  }

	  /**
	   * Test node for compliance with parameters
	   *
	   * @param object
	   * @param params
	   * @returns {boolean}
	   */
	  testNode(object, params) {
	    if (!params || !main_core.Type.isPlainObject(params)) {
	      return true;
	    }
	    for (const property in params) {
	      if (!params.hasOwnProperty(property)) {
	        continue;
	      }
	      switch (property) {
	        case 'tag':
	        case 'tagName':
	          if (main_core.Type.isString(params[property])) {
	            if (object.tagName.toUpperCase() !== params[property].toUpperCase()) {
	              return false;
	            }
	          } else if (params[property] instanceof RegExp) {
	            if (!params[property].test(object.tagName)) {
	              return false;
	            }
	          }
	          break;
	        case 'class':
	        case 'className':
	          if (main_core.Type.isString(params[property])) {
	            if (!main_core.Dom.hasClass(object, params[property].trim())) {
	              return false;
	            }
	          } else if (params[property] instanceof RegExp) {
	            if (!main_core.Type.isString(object.className) || !params[property].test(object.className)) {
	              return false;
	            }
	          }
	          break;
	        case 'attr':
	        case 'attrs':
	        case 'attribute':
	          if (main_core.Type.isString(params[property])) {
	            if (!object.getAttribute(params[property])) {
	              return false;
	            }
	          } else if (params[property] && Object.prototype.toString.call(params[property]) === "[object Array]") {
	            for (let i = 0, length = params[property].length; i < length; i++) {
	              if (params[property][i] && !object.getAttribute(params[property][i])) {
	                return false;
	              }
	            }
	          } else {
	            for (const paramKey in params[property]) {
	              if (!params[property].hasOwnProperty(paramKey)) {
	                continue;
	              }
	              const value = object.getAttribute(paramKey);
	              if (!main_core.Type.isString(value)) {
	                return false;
	              }
	              if (params[property][paramKey] instanceof RegExp) {
	                if (!params[property][paramKey].test(value)) {
	                  return false;
	                }
	              } else if (value !== '' + params[property][paramKey]) {
	                return false;
	              }
	            }
	          }
	          break;
	        case 'property':
	        case 'props':
	          if (main_core.Type.isString(params[property])) {
	            if (!object[params[property]]) {
	              return false;
	            }
	          } else if (params[property] && Object.prototype.toString.call(params[property]) === "[object Array]") {
	            for (let i = 0, length = params[property].length; i < length; i++) {
	              if (params[property][i] && !object[params[property][i]]) {
	                return false;
	              }
	            }
	          } else {
	            for (const paramKey in params[property]) {
	              if (!params[property].hasOwnProperty(paramKey)) {
	                continue;
	              }
	              if (main_core.Type.isString(params[property][paramKey])) {
	                if (object[paramKey] !== params[property][paramKey]) {
	                  return false;
	                }
	              } else if (params[property][paramKey] instanceof RegExp) {
	                if (!main_core.Type.isString(object[paramKey]) || !params[property][paramKey].test(object[paramKey])) {
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
	   *
	   *
	   * @param {Object} vueInstance
	   * @param {String|Array} phrasePrefix
	   * @param {Object|null} phrases
	   * @returns {ReadonlyArray<any>}
	   */
	  getFilteredPhrases(vueInstance, phrasePrefix, phrases = null) {
	    const result = {};
	    if (!phrases) {
	      phrases = vueInstance.$bitrix.Loc.getMessages();
	    }
	    if (Array.isArray(phrasePrefix)) {
	      for (const message in phrases) {
	        if (!phrases.hasOwnProperty(message)) {
	          continue;
	        }
	        if (!phrasePrefix.find(element => message.toString().startsWith(element))) {
	          continue;
	        }
	        if (this.localizationMode === 'development') {
	          result[message] = message;
	        } else {
	          result[message] = phrases[message];
	        }
	      }
	    } else {
	      for (const message in phrases) {
	        if (!phrases.hasOwnProperty(message)) {
	          continue;
	        }
	        if (!message.startsWith(phrasePrefix)) {
	          continue;
	        }
	        if (this.localizationMode === 'development') {
	          result[message] = message;
	        } else {
	          result[message] = phrases[message];
	        }
	      }
	    }
	    return Object.freeze(result);
	  }

	  /**
	   * Return component params with mutation
	   *
	   * @param {String} name
	   * @param {Object} mutations
	   * @returns {null|Object}
	   *
	   * @private
	   */

	  /**
	   * @private
	   * @param text
	   * @param params
	   */
	  showNotice(text, ...params) {
	    if (this.developerMode) {
	      console.warn('BitrixVue: ' + text, ...params);
	    }
	  }

	  /**
	   * @private
	   * @param text
	   * @param params
	   */
	  showError(text, ...params) {
	    console.error('BitrixVue: ' + text, ...params);
	  }

	  /**
	   * @deprecated Special method for plugin registration
	   */
	  install(app) {
	    const bitrixVue = this;

	    // 1. Init Bitrix public api
	    const $Bitrix = {};

	    // 1.1 Localization
	    $Bitrix.Loc = {
	      messages: {},
	      getMessage: function (messageId, replacements = null) {
	        if (bitrixVue.localizationMode === 'development') {
	          let debugMessageId = [messageId];
	          if (main_core.Type.isPlainObject(replacements)) {
	            const replaceKeys = Object.keys(replacements);
	            if (replaceKeys.length > 0) {
	              debugMessageId = [messageId, ' (replacements: ', replaceKeys.join(', '), ')'];
	            }
	          }
	          return debugMessageId.join('');
	        }
	        let message = '';
	        if (!main_core.Type.isUndefined(this.messages[messageId])) {
	          message = this.messages[messageId];
	        } else {
	          message = main_core.Loc.getMessage(messageId);
	          this.messages[messageId] = message;
	        }
	        if (main_core.Type.isString(message) && main_core.Type.isPlainObject(replacements)) {
	          Object.keys(replacements).forEach(replacement => {
	            const globalRegexp = new RegExp(replacement, 'gi');
	            message = message.replace(globalRegexp, () => {
	              return main_core.Type.isNil(replacements[replacement]) ? '' : String(replacements[replacement]);
	            });
	          });
	        }
	        return message;
	      },
	      hasMessage: function (messageId) {
	        return main_core.Type.isString(messageId) && !main_core.Type.isNil(this.getMessages()[messageId]);
	      },
	      getMessages: function () {
	        // eslint-disable-next-line bitrix-rules/no-bx-message
	        if (!main_core.Type.isUndefined(BX.message)) {
	          // eslint-disable-next-line bitrix-rules/no-bx-message
	          return {
	            ...BX.message,
	            ...this.messages
	          };
	        }
	        return {
	          ...this.messages
	        };
	      },
	      setMessage: function (id, value) {
	        if (main_core.Type.isString(id)) {
	          this.messages[id] = value;
	        }
	        if (main_core.Type.isObject(id)) {
	          for (const code in id) {
	            if (id.hasOwnProperty(code)) {
	              this.messages[code] = id[code];
	            }
	          }
	        }
	      }
	    };

	    // 1.2  Application Data
	    $Bitrix.Application = {
	      instance: null,
	      get: function () {
	        return this.instance;
	      },
	      set: function (instance) {
	        this.instance = instance;
	      }
	    };

	    // 1.3  Application Data
	    $Bitrix.Data = {
	      data: {},
	      get: function (name, defaultValue) {
	        var _this$data$name;
	        return (_this$data$name = this.data[name]) != null ? _this$data$name : defaultValue;
	      },
	      set: function (name, value) {
	        this.data[name] = value;
	      }
	    };

	    // 1.4  Application EventEmitter
	    $Bitrix.eventEmitter = new main_core_events.EventEmitter();

	    // hack for old version of Bitrix SM
	    if (!main_core.Type.isFunction($Bitrix.eventEmitter.setEventNamespace)) {
	      window.BX.Event.EventEmitter.prototype.setEventNamespace = function () {};
	      $Bitrix.eventEmitter.setEventNamespace = function () {};
	    }
	    $Bitrix.eventEmitter.setEventNamespace('vue:app:' + app._uid);

	    // 1.5  Application RestClient
	    $Bitrix.RestClient = {
	      instance: null,
	      get: function () {
	        var _this$instance;
	        return (_this$instance = this.instance) != null ? _this$instance : rest_client.rest;
	      },
	      set: function (instance) {
	        this.instance = instance;
	        $Bitrix.eventEmitter.emit(bitrixVue.events.restClientChange);
	      },
	      isCustom() {
	        return !main_core.Type.isNull(this.instance);
	      }
	    };

	    // 1.6  Application PullClient
	    $Bitrix.PullClient = {
	      instance: null,
	      get: function () {
	        var _this$instance2;
	        return (_this$instance2 = this.instance) != null ? _this$instance2 : pull_client.PULL;
	      },
	      set: function (instance) {
	        this.instance = instance;
	        $Bitrix.eventEmitter.emit(bitrixVue.events.pullClientChange);
	      },
	      isCustom() {
	        return !main_core.Type.isNull(this.instance);
	      }
	    };

	    // 2. Apply global properties
	    app.config.globalProperties.$bitrix = $Bitrix;
	    const BitrixVueRef = this;
	    app.mixin({
	      computed: {
	        $Bitrix: function () {
	          return this.$bitrix;
	        }
	      },
	      mounted: function () {
	        if (!main_core.Type.isNil(this.$root.$bitrixApplication)) {
	          BitrixVueRef.showNotice("Store reference in global variables (like: this.$bitrixApplication) is deprecated, use this.$Bitrix.Data.set(...) instead.");
	        }
	        if (!main_core.Type.isNil(this.$root.$bitrixController)) {
	          BitrixVueRef.showNotice("Store reference in global variables (like: this.$bitrixController) is deprecated, use this.$Bitrix.Data.set(...) instead.");
	        }
	        if (!main_core.Type.isNil(this.$root.$bitrixMessages)) {
	          BitrixVueRef.showNotice("Store localization in global variable this.$bitrixMessages is deprecated, use this.$Bitrix.Log.setMessage(...) instead.");
	        }
	        if (!main_core.Type.isNil(this.$root.$bitrixRestClient)) {
	          BitrixVueRef.showNotice("Working with a Rest-client through an old variable this.$bitrixRestClient is deprecated, use this.$Bitrix.RestClient.get() instead.");
	        }
	        if (!main_core.Type.isNil(this.$root.$bitrixPullClient)) {
	          BitrixVueRef.showNotice("Working with a Pull-client through an old variable this.$bitrixPullClient is deprecated, use this.$Bitrix.PullClient.get() instead.");
	        }
	      }
	    });
	  }
	}
	function _getComponentParamsWithMutation2(name, mutations) {
	  if (main_core.Type.isUndefined(this.components[name])) {
	    return null;
	  }
	  let componentParams = Object.assign({}, this.components[name]);
	  if (main_core.Type.isUndefined(mutations)) {
	    return componentParams;
	  }
	  mutations.forEach(mutation => {
	    componentParams = babelHelpers.classPrivateFieldLooseBase(this, _applyMutation)[_applyMutation](babelHelpers.classPrivateFieldLooseBase(this, _cloneObjectBeforeApplyMutation)[_cloneObjectBeforeApplyMutation](componentParams, mutation), mutation);
	  });
	  return componentParams;
	}
	function _getFinalComponentParams2(name) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getComponentParamsWithMutation)[_getComponentParamsWithMutation](name, this.mutations[name]);
	}
	function _cloneObjectBeforeApplyMutation2(objectParams = {}, mutation = {}, level = 1, previousParamName = '') {
	  const object = {};
	  for (const param in objectParams) {
	    if (!objectParams.hasOwnProperty(param)) {
	      continue;
	    }
	    if (main_core.Type.isString(objectParams[param])) {
	      object[param] = objectParams[param];
	    } else if (main_core.Type.isArray(objectParams[param])) {
	      object[param] = [].concat(objectParams[param]);
	    } else if (main_core.Type.isObjectLike(objectParams[param])) {
	      if (previousParamName === 'watch' || previousParamName === 'props' || previousParamName === 'directives') {
	        object[param] = objectParams[param];
	      } else if (main_core.Type.isNull(objectParams[param])) {
	        object[param] = null;
	      } else if (main_core.Type.isObjectLike(mutation[param])) {
	        object[param] = babelHelpers.classPrivateFieldLooseBase(this, _cloneObjectBeforeApplyMutation)[_cloneObjectBeforeApplyMutation](objectParams[param], mutation[param], level + 1, param);
	      } else {
	        object[param] = Object.assign({}, objectParams[param]);
	      }
	    } else if (main_core.Type.isFunction(objectParams[param])) {
	      if (!main_core.Type.isFunction(mutation[param])) {
	        object[param] = objectParams[param];
	      } else if (level > 1) {
	        if (previousParamName === 'watch') {
	          object[param] = objectParams[param];
	        } else {
	          object['parent' + param[0].toUpperCase() + param.substr(1)] = objectParams[param];
	        }
	      } else {
	        if (main_core.Type.isUndefined(object['methods'])) {
	          object['methods'] = {};
	        }
	        object['methods']['parent' + param[0].toUpperCase() + param.substr(1)] = objectParams[param];
	        if (main_core.Type.isUndefined(objectParams['methods'])) {
	          objectParams['methods'] = {};
	        }
	        objectParams['methods']['parent' + param[0].toUpperCase() + param.substr(1)] = objectParams[param];
	      }
	    } else if (!main_core.Type.isUndefined(objectParams[param])) {
	      object[param] = objectParams[param];
	    }
	  }
	  return object;
	}
	function _applyMutation2(clonedObject = {}, mutation = {}, level = 1) {
	  const object = Object.assign({}, clonedObject);
	  for (const param in mutation) {
	    if (!mutation.hasOwnProperty(param)) {
	      continue;
	    }
	    if (level === 1 && (param === 'compilerOptions' || param === 'setup')) {
	      object[param] = mutation[param];
	    } else if (level === 1 && param === 'extends') {
	      object[param] = mutation[param];
	    } else if (main_core.Type.isString(mutation[param])) {
	      if (main_core.Type.isString(object[param])) {
	        object[param] = mutation[param].replace(`#PARENT_${param.toUpperCase()}#`, object[param]);
	      } else {
	        object[param] = mutation[param].replace(`#PARENT_${param.toUpperCase()}#`, '');
	      }
	    } else if (main_core.Type.isArray(mutation[param])) {
	      if (level === 1 && param === 'replaceMixins') {
	        object['mixins'] = [].concat(mutation[param]);
	      } else if (level === 1 && param === 'replaceInject') {
	        object['inject'] = [].concat(mutation[param]);
	      } else if (level === 1 && param === 'replaceEmits') {
	        object['emits'] = [].concat(mutation[param]);
	      } else if (level === 1 && param === 'replaceExpose') {
	        object['expose'] = [].concat(mutation[param]);
	      } else if (main_core.Type.isPlainObject(object[param])) {
	        mutation[param].forEach(element => object[param][element] = null);
	      } else {
	        object[param] = object[param].concat(mutation[param]);
	      }
	    } else if (main_core.Type.isObjectLike(mutation[param])) {
	      if (level === 1 && param === 'props' && main_core.Type.isArray(object[param]) || level === 1 && param === 'emits' && main_core.Type.isArray(object[param])) {
	        const newObject = {};
	        object[param].forEach(element => {
	          newObject[element] = null;
	        });
	        object[param] = newObject;
	      }
	      if (level === 1 && param === 'watch') {
	        for (const paramName in object[param]) {
	          if (!object[param].hasOwnProperty(paramName)) {
	            continue;
	          }
	          if (paramName.includes('.')) {
	            continue;
	          }
	          if (main_core.Type.isFunction(object[param][paramName]) || main_core.Type.isObject(object[param][paramName]) && main_core.Type.isFunction(object[param][paramName]['handler'])) {
	            if (main_core.Type.isUndefined(object['methods'])) {
	              object['methods'] = {};
	            }
	            const originNewFunctionName = 'parentWatch' + paramName[0].toUpperCase() + paramName.substr(1);
	            if (main_core.Type.isFunction(object[param][paramName])) {
	              object['methods'][originNewFunctionName] = object[param][paramName];
	            } else {
	              object['methods'][originNewFunctionName] = object[param][paramName]['handler'];
	            }
	          }
	        }
	      }
	      if (level === 1 && param === 'replaceEmits') {
	        object['emits'] = Object.assign({}, mutation[param]);
	      } else if (level === 1 && (param === 'components' || param === 'directives')) {
	        if (main_core.Type.isUndefined(object[param])) {
	          object[param] = {};
	        }
	        for (const objectName in mutation[param]) {
	          if (!mutation[param].hasOwnProperty(objectName)) {
	            continue;
	          }
	          let parentObjectName = objectName[0].toUpperCase() + objectName.substr(1);
	          parentObjectName = param === 'components' ? 'Parent' + parentObjectName : 'parent' + parentObjectName;
	          object[param][parentObjectName] = Object.assign({}, object[param][objectName]);
	          if (param === 'components') {
	            if (main_core.Type.isUndefined(mutation[param][objectName].components)) {
	              mutation[param][objectName].components = {};
	            }
	            mutation[param][objectName].components = Object.assign({
	              [parentObjectName]: object[param][objectName]
	            }, mutation[param][objectName].components);
	          }
	          object[param][objectName] = mutation[param][objectName];
	        }
	      } else if (main_core.Type.isArray(object[param])) {
	        for (const mutationName in mutation[param]) {
	          if (!mutation[param].hasOwnProperty(mutationName)) {
	            continue;
	          }
	          object[param].push(mutationName);
	        }
	      } else if (main_core.Type.isObjectLike(object[param])) {
	        object[param] = babelHelpers.classPrivateFieldLooseBase(this, _applyMutation)[_applyMutation](object[param], mutation[param], level + 1);
	      } else {
	        object[param] = mutation[param];
	      }
	    } else {
	      object[param] = mutation[param];
	    }
	  }
	  return object;
	}
	BitrixVue = new BitrixVue();

	exports.useObservable = useObservable;
	exports.BitrixVue = BitrixVue;

}((this.BX.Vue3 = this.BX.Vue3 || {}),BX.Event,BX,BX,BX,BX.Vue3));



})();
//# sourceMappingURL=bitrixvue.bundle.js.map