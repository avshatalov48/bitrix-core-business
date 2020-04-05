(function (exports,ui_vue,ui_dexie,main_md5,ui_vuex) {
  'use strict';

  /**
   * vuex v3.0.1
   * (c) 2017 Evan You
   * @license MIT
   */

  var applyMixin = function applyMixin(Vue) {
    var version = Number(Vue.version.split('.')[0]);

    if (version >= 2) {
      Vue.mixin({
        beforeCreate: vuexInit
      });
    } else {
      // override init and inject vuex init procedure
      // for 1.x backwards compatibility.
      var _init = Vue.prototype._init;

      Vue.prototype._init = function (options) {
        if (options === void 0) options = {};
        options.init = options.init ? [vuexInit].concat(options.init) : vuexInit;

        _init.call(this, options);
      };
    }
    /**
     * Vuex init hook, injected into each instances init hooks list.
     */


    function vuexInit() {
      var options = this.$options; // store injection

      if (options.store) {
        this.$store = typeof options.store === 'function' ? options.store() : options.store;
      } else if (options.parent && options.parent.$store) {
        this.$store = options.parent.$store;
      }
    }
  };

  var devtoolHook = typeof window !== 'undefined' && window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

  function devtoolPlugin(store) {
    if (!devtoolHook) {
      return;
    }

    store._devtoolHook = devtoolHook;
    devtoolHook.emit('vuex:init', store);
    devtoolHook.on('vuex:travel-to-state', function (targetState) {
      store.replaceState(targetState);
    });
    store.subscribe(function (mutation, state) {
      devtoolHook.emit('vuex:mutation', mutation, state);
    });
  }
  /**
   * Get the first item that pass the test
   * by second argument function
   *
   * @param {Array} list
   * @param {Function} f
   * @return {*}
   */

  /**
   * Deep copy the given object considering circular structure.
   * This function caches all nested objects and its copies.
   * If it detects circular structure, use cached copy to avoid infinite loop.
   *
   * @param {*} obj
   * @param {Array<Object>} cache
   * @return {*}
   */

  /**
   * forEach for object
   */


  function forEachValue(obj, fn) {
    Object.keys(obj).forEach(function (key) {
      return fn(obj[key], key);
    });
  }

  function isObject(obj) {
    return obj !== null && babelHelpers.typeof(obj) === 'object';
  }

  function isPromise(val) {
    return val && typeof val.then === 'function';
  }

  function assert(condition, msg) {
    if (!condition) {
      throw new Error("[vuex] " + msg);
    }
  }

  var Module = function Module(rawModule, runtime) {
    this.runtime = runtime;
    this._children = Object.create(null);
    this._rawModule = rawModule;
    var rawState = rawModule.state;
    this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
  };

  var prototypeAccessors$1 = {
    namespaced: {
      configurable: true
    }
  };

  prototypeAccessors$1.namespaced.get = function () {
    return !!this._rawModule.namespaced;
  };

  Module.prototype.addChild = function addChild(key, module) {
    this._children[key] = module;
  };

  Module.prototype.removeChild = function removeChild(key) {
    delete this._children[key];
  };

  Module.prototype.getChild = function getChild(key) {
    return this._children[key];
  };

  Module.prototype.update = function update(rawModule) {
    this._rawModule.namespaced = rawModule.namespaced;

    if (rawModule.actions) {
      this._rawModule.actions = rawModule.actions;
    }

    if (rawModule.mutations) {
      this._rawModule.mutations = rawModule.mutations;
    }

    if (rawModule.getters) {
      this._rawModule.getters = rawModule.getters;
    }
  };

  Module.prototype.forEachChild = function forEachChild(fn) {
    forEachValue(this._children, fn);
  };

  Module.prototype.forEachGetter = function forEachGetter(fn) {
    if (this._rawModule.getters) {
      forEachValue(this._rawModule.getters, fn);
    }
  };

  Module.prototype.forEachAction = function forEachAction(fn) {
    if (this._rawModule.actions) {
      forEachValue(this._rawModule.actions, fn);
    }
  };

  Module.prototype.forEachMutation = function forEachMutation(fn) {
    if (this._rawModule.mutations) {
      forEachValue(this._rawModule.mutations, fn);
    }
  };

  Object.defineProperties(Module.prototype, prototypeAccessors$1);

  var ModuleCollection = function ModuleCollection(rawRootModule) {
    // register root module (Vuex.Store options)
    this.register([], rawRootModule, false);
  };

  ModuleCollection.prototype.get = function get(path) {
    return path.reduce(function (module, key) {
      return module.getChild(key);
    }, this.root);
  };

  ModuleCollection.prototype.getNamespace = function getNamespace(path) {
    var module = this.root;
    return path.reduce(function (namespace, key) {
      module = module.getChild(key);
      return namespace + (module.namespaced ? key + '/' : '');
    }, '');
  };

  ModuleCollection.prototype.update = function update$1(rawRootModule) {
    update([], this.root, rawRootModule);
  };

  ModuleCollection.prototype.register = function register(path, rawModule, runtime) {
    var this$1 = this;
    if (runtime === void 0) runtime = true;
    {
      assertRawModule(path, rawModule);
    }
    var newModule = new Module(rawModule, runtime);

    if (path.length === 0) {
      this.root = newModule;
    } else {
      var parent = this.get(path.slice(0, -1));
      parent.addChild(path[path.length - 1], newModule);
    } // register nested modules


    if (rawModule.modules) {
      forEachValue(rawModule.modules, function (rawChildModule, key) {
        this$1.register(path.concat(key), rawChildModule, runtime);
      });
    }
  };

  ModuleCollection.prototype.unregister = function unregister(path) {
    var parent = this.get(path.slice(0, -1));
    var key = path[path.length - 1];

    if (!parent.getChild(key).runtime) {
      return;
    }

    parent.removeChild(key);
  };

  function update(path, targetModule, newModule) {
    {
      assertRawModule(path, newModule);
    } // update target module

    targetModule.update(newModule); // update nested modules

    if (newModule.modules) {
      for (var key in newModule.modules) {
        if (!targetModule.getChild(key)) {
          {
            console.warn("[vuex] trying to add a new module '" + key + "' on hot reloading, " + 'manual reload is needed');
          }
          return;
        }

        update(path.concat(key), targetModule.getChild(key), newModule.modules[key]);
      }
    }
  }

  var functionAssert = {
    assert: function assert(value) {
      return typeof value === 'function';
    },
    expected: 'function'
  };
  var objectAssert = {
    assert: function assert(value) {
      return typeof value === 'function' || babelHelpers.typeof(value) === 'object' && typeof value.handler === 'function';
    },
    expected: 'function or object with "handler" function'
  };
  var assertTypes = {
    getters: functionAssert,
    mutations: functionAssert,
    actions: objectAssert
  };

  function assertRawModule(path, rawModule) {
    Object.keys(assertTypes).forEach(function (key) {
      if (!rawModule[key]) {
        return;
      }

      var assertOptions = assertTypes[key];
      forEachValue(rawModule[key], function (value, type) {
        assert(assertOptions.assert(value), makeAssertionMessage(path, key, type, value, assertOptions.expected));
      });
    });
  }

  function makeAssertionMessage(path, key, type, value, expected) {
    var buf = key + " should be " + expected + " but \"" + key + "." + type + "\"";

    if (path.length > 0) {
      buf += " in module \"" + path.join('.') + "\"";
    }

    buf += " is " + JSON.stringify(value) + ".";
    return buf;
  }

  var Store = function Store(options) {
    var this$1 = this;
    if (options === void 0) options = {};
    {
      assert(ui_vue.VueVendor, "must call Vue.use(Vuex) before creating a store instance.");
      assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
      assert(this instanceof Store, "Store must be called with the new operator.");
    }
    var plugins = options.plugins;
    if (plugins === void 0) plugins = [];
    var strict = options.strict;
    if (strict === void 0) strict = false;
    var state = options.state;
    if (state === void 0) state = {};

    if (typeof state === 'function') {
      state = state() || {};
    } // store internal state


    this._committing = false;
    this._actions = Object.create(null);
    this._actionSubscribers = [];
    this._mutations = Object.create(null);
    this._wrappedGetters = Object.create(null);
    this._modules = new ModuleCollection(options);
    this._modulesNamespaceMap = Object.create(null);
    this._subscribers = [];
    this._watcherVM = new ui_vue.VueVendor(); // bind commit and dispatch to self

    var store = this;
    var ref = this;
    var dispatch = ref.dispatch;
    var commit = ref.commit;

    this.dispatch = function boundDispatch(type, payload) {
      return dispatch.call(store, type, payload);
    };

    this.commit = function boundCommit(type, payload, options) {
      return commit.call(store, type, payload, options);
    }; // strict mode


    this.strict = strict; // init root module.
    // this also recursively registers all sub-modules
    // and collects all module getters inside this._wrappedGetters

    installModule(this, state, [], this._modules.root); // initialize the store vm, which is responsible for the reactivity
    // (also registers _wrappedGetters as computed properties)

    resetStoreVM(this, state); // apply plugins

    plugins.forEach(function (plugin) {
      return plugin(this$1);
    });

    if (ui_vue.VueVendor.config.devtools) {
      devtoolPlugin(this);
    }
  };

  var prototypeAccessors = {
    state: {
      configurable: true
    }
  };

  prototypeAccessors.state.get = function () {
    return this._vm._data.$$state;
  };

  prototypeAccessors.state.set = function (v) {
    {
      assert(false, "Use store.replaceState() to explicit replace store state.");
    }
  };

  Store.prototype.commit = function commit(_type, _payload, _options) {
    var this$1 = this; // check object-style commit

    var ref = unifyObjectStyle(_type, _payload, _options);
    var type = ref.type;
    var payload = ref.payload;
    var options = ref.options;
    var mutation = {
      type: type,
      payload: payload
    };
    var entry = this._mutations[type];

    if (!entry) {
      {
        console.error("[vuex] unknown mutation type: " + type);
      }
      return;
    }

    this._withCommit(function () {
      entry.forEach(function commitIterator(handler) {
        handler(payload);
      });
    });

    this._subscribers.forEach(function (sub) {
      return sub(mutation, this$1.state);
    });

    if (options && options.silent) {
      console.warn("[vuex] mutation type: " + type + ". Silent option has been removed. " + 'Use the filter functionality in the vue-devtools');
    }
  };

  Store.prototype.dispatch = function dispatch(_type, _payload) {
    var this$1 = this; // check object-style dispatch

    var ref = unifyObjectStyle(_type, _payload);
    var type = ref.type;
    var payload = ref.payload;
    var action = {
      type: type,
      payload: payload
    };
    var entry = this._actions[type];

    if (!entry) {
      {
        console.error("[vuex] unknown action type: " + type);
      }
      return;
    }

    this._actionSubscribers.forEach(function (sub) {
      return sub(action, this$1.state);
    });

    return entry.length > 1 ? Promise.all(entry.map(function (handler) {
      return handler(payload);
    })) : entry[0](payload);
  };

  Store.prototype.subscribe = function subscribe(fn) {
    return genericSubscribe(fn, this._subscribers);
  };

  Store.prototype.subscribeAction = function subscribeAction(fn) {
    return genericSubscribe(fn, this._actionSubscribers);
  };

  Store.prototype.watch = function watch(getter, cb, options) {
    var this$1 = this;
    {
      assert(typeof getter === 'function', "store.watch only accepts a function.");
    }
    return this._watcherVM.$watch(function () {
      return getter(this$1.state, this$1.getters);
    }, cb, options);
  };

  Store.prototype.replaceState = function replaceState(state) {
    var this$1 = this;

    this._withCommit(function () {
      this$1._vm._data.$$state = state;
    });
  };

  Store.prototype.registerModule = function registerModule(path, rawModule, options) {
    if (options === void 0) options = {};

    if (typeof path === 'string') {
      path = [path];
    }

    {
      assert(Array.isArray(path), "module path must be a string or an Array.");
      assert(path.length > 0, 'cannot register the root module by using registerModule.');
    }

    this._modules.register(path, rawModule);

    installModule(this, this.state, path, this._modules.get(path), options.preserveState); // reset store to update getters...

    resetStoreVM(this, this.state);
  };

  Store.prototype.unregisterModule = function unregisterModule(path) {
    var this$1 = this;

    if (typeof path === 'string') {
      path = [path];
    }

    {
      assert(Array.isArray(path), "module path must be a string or an Array.");
    }

    this._modules.unregister(path);

    this._withCommit(function () {
      var parentState = getNestedState(this$1.state, path.slice(0, -1));
      ui_vue.VueVendor.delete(parentState, path[path.length - 1]);
    });

    resetStore(this);
  };

  Store.prototype.hotUpdate = function hotUpdate(newOptions) {
    this._modules.update(newOptions);

    resetStore(this, true);
  };

  Store.prototype._withCommit = function _withCommit(fn) {
    var committing = this._committing;
    this._committing = true;
    fn();
    this._committing = committing;
  };

  Object.defineProperties(Store.prototype, prototypeAccessors);

  function genericSubscribe(fn, subs) {
    if (subs.indexOf(fn) < 0) {
      subs.push(fn);
    }

    return function () {
      var i = subs.indexOf(fn);

      if (i > -1) {
        subs.splice(i, 1);
      }
    };
  }

  function resetStore(store, hot) {
    store._actions = Object.create(null);
    store._mutations = Object.create(null);
    store._wrappedGetters = Object.create(null);
    store._modulesNamespaceMap = Object.create(null);
    var state = store.state; // init all modules

    installModule(store, state, [], store._modules.root, true); // reset vm

    resetStoreVM(store, state, hot);
  }

  function resetStoreVM(store, state, hot) {
    var oldVm = store._vm; // bind store public getters

    store.getters = {};
    var wrappedGetters = store._wrappedGetters;
    var computed = {};
    forEachValue(wrappedGetters, function (fn, key) {
      // use computed to leverage its lazy-caching mechanism
      computed[key] = function () {
        return fn(store);
      };

      Object.defineProperty(store.getters, key, {
        get: function get() {
          return store._vm[key];
        },
        enumerable: true // for local getters

      });
    }); // use a Vue instance to store the state tree
    // suppress warnings just in case the user has added
    // some funky global mixins

    var silent = ui_vue.VueVendor.config.silent;
    ui_vue.VueVendor.config.silent = true;
    store._vm = new ui_vue.VueVendor({
      data: {
        $$state: state
      },
      computed: computed
    });
    ui_vue.VueVendor.config.silent = silent; // enable strict mode for new vm

    if (store.strict) {
      enableStrictMode(store);
    }

    if (oldVm) {
      if (hot) {
        // dispatch changes in all subscribed watchers
        // to force getter re-evaluation for hot reloading.
        store._withCommit(function () {
          oldVm._data.$$state = null;
        });
      }

      ui_vue.VueVendor.nextTick(function () {
        return oldVm.$destroy();
      });
    }
  }

  function installModule(store, rootState, path, module, hot) {
    var isRoot = !path.length;

    var namespace = store._modules.getNamespace(path); // register in namespace map


    if (module.namespaced) {
      store._modulesNamespaceMap[namespace] = module;
    } // set state


    if (!isRoot && !hot) {
      var parentState = getNestedState(rootState, path.slice(0, -1));
      var moduleName = path[path.length - 1];

      store._withCommit(function () {
        ui_vue.VueVendor.set(parentState, moduleName, module.state);
      });
    }

    var local = module.context = makeLocalContext(store, namespace, path);
    module.forEachMutation(function (mutation, key) {
      var namespacedType = namespace + key;
      registerMutation(store, namespacedType, mutation, local);
    });
    module.forEachAction(function (action, key) {
      var type = action.root ? key : namespace + key;
      var handler = action.handler || action;
      registerAction(store, type, handler, local);
    });
    module.forEachGetter(function (getter, key) {
      var namespacedType = namespace + key;
      registerGetter(store, namespacedType, getter, local);
    });
    module.forEachChild(function (child, key) {
      installModule(store, rootState, path.concat(key), child, hot);
    });
  }
  /**
   * make localized dispatch, commit, getters and state
   * if there is no namespace, just use root ones
   */


  function makeLocalContext(store, namespace, path) {
    var noNamespace = namespace === '';
    var local = {
      dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
        var args = unifyObjectStyle(_type, _payload, _options);
        var payload = args.payload;
        var options = args.options;
        var type = args.type;

        if (!options || !options.root) {
          type = namespace + type;

          if (!store._actions[type]) {
            console.error("[vuex] unknown local action type: " + args.type + ", global type: " + type);
            return;
          }
        }

        return store.dispatch(type, payload);
      },
      commit: noNamespace ? store.commit : function (_type, _payload, _options) {
        var args = unifyObjectStyle(_type, _payload, _options);
        var payload = args.payload;
        var options = args.options;
        var type = args.type;

        if (!options || !options.root) {
          type = namespace + type;

          if (!store._mutations[type]) {
            console.error("[vuex] unknown local mutation type: " + args.type + ", global type: " + type);
            return;
          }
        }

        store.commit(type, payload, options);
      }
    }; // getters and state object must be gotten lazily
    // because they will be changed by vm update

    Object.defineProperties(local, {
      getters: {
        get: noNamespace ? function () {
          return store.getters;
        } : function () {
          return makeLocalGetters(store, namespace);
        }
      },
      state: {
        get: function get() {
          return getNestedState(store.state, path);
        }
      }
    });
    return local;
  }

  function makeLocalGetters(store, namespace) {
    var gettersProxy = {};
    var splitPos = namespace.length;
    Object.keys(store.getters).forEach(function (type) {
      // skip if the target getter is not match this namespace
      if (type.slice(0, splitPos) !== namespace) {
        return;
      } // extract local getter type


      var localType = type.slice(splitPos); // Add a port to the getters proxy.
      // Define as getter property because
      // we do not want to evaluate the getters in this time.

      Object.defineProperty(gettersProxy, localType, {
        get: function get() {
          return store.getters[type];
        },
        enumerable: true
      });
    });
    return gettersProxy;
  }

  function registerMutation(store, type, handler, local) {
    var entry = store._mutations[type] || (store._mutations[type] = []);
    entry.push(function wrappedMutationHandler(payload) {
      handler.call(store, local.state, payload);
    });
  }

  function registerAction(store, type, handler, local) {
    var entry = store._actions[type] || (store._actions[type] = []);
    entry.push(function wrappedActionHandler(payload, cb) {
      var res = handler.call(store, {
        dispatch: local.dispatch,
        commit: local.commit,
        getters: local.getters,
        state: local.state,
        rootGetters: store.getters,
        rootState: store.state
      }, payload, cb);

      if (!isPromise(res)) {
        res = Promise.resolve(res);
      }

      if (store._devtoolHook) {
        return res.catch(function (err) {
          store._devtoolHook.emit('vuex:error', err);

          throw err;
        });
      } else {
        return res;
      }
    });
  }

  function registerGetter(store, type, rawGetter, local) {
    if (store._wrappedGetters[type]) {
      {
        console.error("[vuex] duplicate getter key: " + type);
      }
      return;
    }

    store._wrappedGetters[type] = function wrappedGetter(store) {
      return rawGetter(local.state, // local state
      local.getters, // local getters
      store.state, // root state
      store.getters // root getters
      );
    };
  }

  function enableStrictMode(store) {
    store._vm.$watch(function () {
      return this._data.$$state;
    }, function () {
      {
        assert(store._committing, "Do not mutate vuex store state outside mutation handlers.");
      }
    }, {
      deep: true,
      sync: true
    });
  }

  function getNestedState(state, path) {
    return path.length ? path.reduce(function (state, key) {
      return state[key];
    }, state) : state;
  }

  function unifyObjectStyle(type, payload, options) {
    if (isObject(type) && type.type) {
      options = payload;
      payload = type;
      type = type.type;
    }

    {
      assert(typeof type === 'string', "Expects string as the type, but found " + babelHelpers.typeof(type) + ".");
    }
    return {
      type: type,
      payload: payload,
      options: options
    };
  }

  function install(_Vue) {
    applyMixin(_Vue);
  }

  var mapState = normalizeNamespace(function (namespace, states) {
    var res = {};
    normalizeMap(states).forEach(function (ref) {
      var key = ref.key;
      var val = ref.val;

      res[key] = function mappedState() {
        var state = this.$store.state;
        var getters = this.$store.getters;

        if (namespace) {
          var module = getModuleByNamespace(this.$store, 'mapState', namespace);

          if (!module) {
            return;
          }

          state = module.context.state;
          getters = module.context.getters;
        }

        return typeof val === 'function' ? val.call(this, state, getters) : state[val];
      }; // mark vuex getter for devtools


      res[key].vuex = true;
    });
    return res;
  });
  var mapMutations = normalizeNamespace(function (namespace, mutations) {
    var res = {};
    normalizeMap(mutations).forEach(function (ref) {
      var key = ref.key;
      var val = ref.val;

      res[key] = function mappedMutation() {
        var args = [],
            len = arguments.length;

        while (len--) {
          args[len] = arguments[len];
        }

        var commit = this.$store.commit;

        if (namespace) {
          var module = getModuleByNamespace(this.$store, 'mapMutations', namespace);

          if (!module) {
            return;
          }

          commit = module.context.commit;
        }

        return typeof val === 'function' ? val.apply(this, [commit].concat(args)) : commit.apply(this.$store, [val].concat(args));
      };
    });
    return res;
  });
  var mapGetters = normalizeNamespace(function (namespace, getters) {
    var res = {};
    normalizeMap(getters).forEach(function (ref) {
      var key = ref.key;
      var val = ref.val;
      val = namespace + val;

      res[key] = function mappedGetter() {
        if (namespace && !getModuleByNamespace(this.$store, 'mapGetters', namespace)) {
          return;
        }

        if (!(val in this.$store.getters)) {
          console.error("[vuex] unknown getter: " + val);
          return;
        }

        return this.$store.getters[val];
      }; // mark vuex getter for devtools


      res[key].vuex = true;
    });
    return res;
  });
  var mapActions = normalizeNamespace(function (namespace, actions) {
    var res = {};
    normalizeMap(actions).forEach(function (ref) {
      var key = ref.key;
      var val = ref.val;

      res[key] = function mappedAction() {
        var args = [],
            len = arguments.length;

        while (len--) {
          args[len] = arguments[len];
        }

        var dispatch = this.$store.dispatch;

        if (namespace) {
          var module = getModuleByNamespace(this.$store, 'mapActions', namespace);

          if (!module) {
            return;
          }

          dispatch = module.context.dispatch;
        }

        return typeof val === 'function' ? val.apply(this, [dispatch].concat(args)) : dispatch.apply(this.$store, [val].concat(args));
      };
    });
    return res;
  });

  var createNamespacedHelpers = function createNamespacedHelpers(namespace) {
    return {
      mapState: mapState.bind(null, namespace),
      mapGetters: mapGetters.bind(null, namespace),
      mapMutations: mapMutations.bind(null, namespace),
      mapActions: mapActions.bind(null, namespace)
    };
  };

  function normalizeMap(map) {
    return Array.isArray(map) ? map.map(function (key) {
      return {
        key: key,
        val: key
      };
    }) : Object.keys(map).map(function (key) {
      return {
        key: key,
        val: map[key]
      };
    });
  }

  function normalizeNamespace(fn) {
    return function (namespace, map) {
      if (typeof namespace !== 'string') {
        map = namespace;
        namespace = '';
      } else if (namespace.charAt(namespace.length - 1) !== '/') {
        namespace += '/';
      }

      return fn(namespace, map);
    };
  }

  function getModuleByNamespace(store, helper, namespace) {
    var module = store._modulesNamespaceMap[namespace];

    if (!module) {
      console.error("[vuex] module namespace not found in " + helper + "(): " + namespace);
    }

    return module;
  }

  var index = {
    Store: Store,
    install: install,
    version: '3.0.1',
    mapState: mapState,
    mapMutations: mapMutations,
    mapGetters: mapGetters,
    mapActions: mapActions,
    createNamespacedHelpers: createNamespacedHelpers
  };
  ui_vue.VueVendor.use(index);

  /**
   * Bitrix Vuex wrapper
   * IndexedDB driver for Vuex Builder
   *
   * @package bitrix
   * @subpackage ui
   * @copyright 2001-2019 Bitrix
   */
  var VuexBuilderDatabaseIndexedDB =
  /*#__PURE__*/
  function () {
    function VuexBuilderDatabaseIndexedDB() {
      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      babelHelpers.classCallCheck(this, VuexBuilderDatabaseIndexedDB);
      this.siteId = config.siteId || 'default';
      this.userId = config.userId || 0;
      this.storage = config.storage || 'default';
      this.name = config.name || '';
      this.code = (window.md5 || main_md5.md5)(this.siteId + '/' + this.userId + '/' + this.storage + '/' + this.name);
      this.db = new ui_dexie.Dexie('bx-vuex-model');
      this.db.version(1).stores({
        data: "code, value"
      });
    }

    babelHelpers.createClass(VuexBuilderDatabaseIndexedDB, [{
      key: "get",
      value: function get() {
        var _this = this;

        return new Promise(function (resolve, reject) {
          _this.db.data.where('code').equals(_this.code).first().then(function (data) {
            resolve(data ? data.value : null);
          }, function (error) {
            reject(error);
          });
        });
      }
    }, {
      key: "set",
      value: function set(value) {
        var _this2 = this;

        return new Promise(function (resolve, reject) {
          _this2.db.data.put({
            code: _this2.code,
            value: value
          }).then(function (data) {
            resolve(true);
          }, function (error) {
            reject(error);
          });
        });
      }
    }]);
    return VuexBuilderDatabaseIndexedDB;
  }();

  /**
   * Bitrix Vuex wrapper
   * LocalStorage driver for Vuex Builder
   *
   * @package bitrix
   * @subpackage ui
   * @copyright 2001-2019 Bitrix
   */
  var VuexBuilderDatabaseLocalStorage =
  /*#__PURE__*/
  function () {
    function VuexBuilderDatabaseLocalStorage() {
      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      babelHelpers.classCallCheck(this, VuexBuilderDatabaseLocalStorage);
      this.siteId = config.siteId || 'default';
      this.userId = config.userId || 0;
      this.storage = config.storage || 'default';
      this.name = config.name || '';
      this.enabled = false;

      if (typeof window.localStorage !== 'undefined') {
        try {
          window.localStorage.setItem('__bx_test_ls_feature__', 'ok');

          if (window.localStorage.getItem('__bx_test_ls_feature__') === 'ok') {
            window.localStorage.removeItem('__bx_test_ls_feature__');
            this.enabled = true;
          }
        } catch (e) {}
      }

      this.code = 'bx-vuex-' + (window.md5 || main_md5.md5)(this.siteId + '/' + this.userId + '/' + this.storage + '/' + this.name);
    }

    babelHelpers.createClass(VuexBuilderDatabaseLocalStorage, [{
      key: "get",
      value: function get() {
        var _this = this;

        return new Promise(function (resolve, reject) {
          if (!_this.enabled) {
            resolve(null);
            return true;
          }

          var result = window.localStorage.getItem(_this.code);

          if (typeof result !== "string") {
            resolve(null);
            return true;
          }

          try {
            resolve(_this.prepareValueAfterGet(JSON.parse(result)));
          } catch (error) {
            reject(error);
          }
        });
      }
    }, {
      key: "set",
      value: function set(value) {
        var _this2 = this;

        return new Promise(function (resolve, reject) {
          if (_this2.enabled) {
            window.localStorage.setItem(_this2.code, JSON.stringify(_this2.prepareValueBeforeSet(value)));
          }

          resolve(true);
        });
      }
    }, {
      key: "prepareValueAfterGet",
      value: function prepareValueAfterGet(value) {
        var _this3 = this;

        if (value instanceof Array) {
          value = value.map(function (element) {
            return _this3.prepareValueAfterGet(element);
          });
        } else if (value instanceof Date) ; else if (value && babelHelpers.typeof(value) === 'object') {
          for (var index in value) {
            value[index] = this.prepareValueAfterGet(value[index]);
          }
        } else if (typeof value === 'string') {
          if (value.startsWith('#DT#')) {
            value = new Date(value.substring(4));
          }
        }

        return value;
      }
    }, {
      key: "prepareValueBeforeSet",
      value: function prepareValueBeforeSet(value) {
        var _this4 = this;

        if (value instanceof Array) {
          value = value.map(function (element) {
            return _this4.prepareValueBeforeSet(element);
          });
        } else if (value instanceof Date) {
          value = '#DT#' + value.toISOString();
        } else if (value && babelHelpers.typeof(value) === 'object') {
          for (var index in value) {
            if (value.hasOwnProperty(index)) {
              value[index] = this.prepareValueBeforeSet(value[index]);
            }
          }
        }

        return value;
      }
    }]);
    return VuexBuilderDatabaseLocalStorage;
  }();

  /**
   * Bitrix Vuex wrapper
   * Interface Vuex model (Vuex builder model)
   *
   * @package bitrix
   * @subpackage ui
   * @copyright 2001-2019 Bitrix
   */
  var VuexBuilderModel =
  /*#__PURE__*/
  function () {
    babelHelpers.createClass(VuexBuilderModel, [{
      key: "getName",

      /**
       * Get name of model
       *
       * @override
       *
       * @returns {String}
       */
      value: function getName() {
        return '';
      }
      /**
       * Get default state
       *
      	 * @override
       *
       * @returns {Object}
       */

    }, {
      key: "getState",
      value: function getState() {
        return {};
      }
      /**
       * Get default element state for models with collection.
       *
      	 * @override
       *
       * @returns {Object}
       */

    }, {
      key: "getElementState",
      value: function getElementState() {
        return {};
      }
      /**
       * Get object containing fields to exclude during the save to database.
       *
      	 * @override
       *
       * @returns {Object}
       */

    }, {
      key: "getStateSaveException",
      value: function getStateSaveException() {
        return undefined;
      }
      /**
       * Get getters
       *
      	 * @override
       *
       * @returns {Object}
       */

    }, {
      key: "getGetters",
      value: function getGetters() {
        return {};
      }
      /**
       * Get mutations
       *
      	 * @override
       *
       * @returns {Object}
       */

    }, {
      key: "getActions",
      value: function getActions() {
        return {};
      }
      /**
       * Get mutations
       *
      	 * @override
       *
       * @returns {Object}
       */

    }, {
      key: "getMutations",
      value: function getMutations() {
        return {};
      }
      /**
       * Method for validation and sanitizing input fields before save in model
       *
       * @override
       *
       * @param fields {Object}
       * @param options {Object}
       *
       * @returns {Object} - Sanitizing fields
       */

    }, {
      key: "validate",
      value: function validate(fields) {
        return {};
      }
      /**
       * Set external variable.
       *
       * @param variables {Object}
       * @returns {VuexBuilder}
       */

    }, {
      key: "setVariables",
      value: function setVariables() {
        var variables = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

        if (!(babelHelpers.typeof(variables) === 'object' && variables)) {
          console.error('VuexBuilderModel.setVars: passed variables is not a Object', store);
          return this;
        }

        this.variables = variables;
        return this;
      }
    }, {
      key: "getVariable",
      value: function getVariable(name) {
        var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;

        if (!name) {
          return defaultValue;
        }

        var nameParts = name.toString().split('.');

        if (nameParts.length == 1) {
          return this.variables[nameParts[0]];
        }

        var result;
        var variables = Object.assign({}, this.variables);

        for (var i = 0; i < nameParts.length; i++) {
          if (typeof variables[nameParts[i]] !== 'undefined') {
            variables = result = variables[nameParts[i]];
          } else {
            result = defaultValue;
            break;
          }
        }

        return result;
      }
      /**
       * Get namespace
       *
       * @returns {String}
       */

    }, {
      key: "getNamespace",
      value: function getNamespace() {
        return this.namespace ? this.namespace : this.getName();
      }
      /**
       * Set namespace
       *
       * @param name {String}
       *
       * @returns {VuexBuilderModel}
       */

    }, {
      key: "setNamespace",
      value: function setNamespace(name) {
        this.namespace = name.toString();
        this.databaseConfig.name = this.namespace;
        return this;
      }
      /**
       * Set database config for model or disable this feature.
       *
       * @param active {boolean}
       * @param config {{name: String, siteId: String, userId: Number, type: VuexBuilder.DatabaseType}}
       *
       * @returns {VuexBuilder}
       */

    }, {
      key: "useDatabase",
      value: function useDatabase(active) {
        var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        this.databaseConfig.active = !!active;
        var updateDriver = this.db === null;

        if (config.type) {
          this.databaseConfig.type = config.type.toString();
          updateDriver = true;
        }

        if (config.storage) {
          this.databaseConfig.storage = config.storage.toString();
        }

        if (config.siteId) {
          this.databaseConfig.siteId = config.siteId.toString();
        }

        if (config.userId) {
          this.databaseConfig.userId = config.userId;
        }

        if (typeof config.timeout !== 'undefined') {
          this.databaseConfig.timeout = config.timeout;
        }

        if (updateDriver) {
          if (this.databaseConfig.type == VuexBuilder$$1.DatabaseType.indexedDb) {
            this.db = new VuexBuilderDatabaseIndexedDB(this.databaseConfig);
          } else if (this.databaseConfig.type == VuexBuilder$$1.DatabaseType.localStorage) {
            this.db = new VuexBuilderDatabaseLocalStorage(this.databaseConfig);
          } else {
            this.db = null;
          }
        }

        return this;
      }
      /**
       * Enable namespace option for model.
       *
       * @param active {boolean}
       * @returns {VuexBuilder}
       */

    }, {
      key: "useNamespace",
      value: function useNamespace(active) {
        this.withNamespace = !!active;
        return this;
      }
      /**
       * Get store config for Vuex.
       *
       * @returns {Promise}
       */

    }, {
      key: "getStore",
      value: function getStore() {
        var _this = this;

        return new Promise(function (resolve, reject) {
          var namespace = '';

          if (_this.withNamespace) {
            namespace = _this.namespace ? _this.namespace : _this.getName();

            if (!namespace && _this.withNamespace) {
              console.error('VuexModel.getStore: current model can not be run in Vuex modules mode', _this.getState());
              reject();
            }
          }

          if (_this.db) {
            _this._getStoreFromDatabase().then(function (state) {
              return resolve(_this._createStore(state, namespace));
            });
          } else {
            resolve(_this._createStore(_this.getState(), namespace));
          }
        });
      }
      /**
       * Save current state after change state
       *
      	 * @param state {Object}
       *
       * @returns {Promise}
       */

    }, {
      key: "saveState",
      value: function saveState() {
        var _this2 = this;

        var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

        if (!this.db) {
          return true;
        }

        clearTimeout(this.saveStateTimeout);
        this.saveStateTimeout = setTimeout(function () {
          _this2.db.set(_this2.cloneState(state, _this2.getStateSaveException()));
        }, this.databaseConfig.timeout);
        return true;
      }
      /**
       * Reset current store to default state
       **
       * @returns {Promise}
       */

    }, {
      key: "clearState",
      value: function clearState() {
        if (this.store) {
          var command = 'vuexBuilderModelClearState';
          command = this.withNamespace ? this.getNamespace() + '/' + command : command;
          this.store.commit(command);
          return true;
        }

        return this.saveState(this.getState());
      }
    }, {
      key: "isSaveNeeded",
      value: function isSaveNeeded(payload) {
        var checkFunction = function checkFunction(payload) {
          var filter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

          if (!filter) {
            return true;
          }

          for (var field in payload) {
            if (!payload.hasOwnProperty(field)) {
              continue;
            }

            if (typeof filter[field] === 'undefined') {
              return true;
            } else if (babelHelpers.typeof(filter[field]) === 'object' && filter[field]) {
              var result = checkFunction(payload[field], filter[field]);

              if (result) {
                return true;
              }
            }
          }

          return false;
        };

        return checkFunction(payload, this.getStateSaveException());
      }
      /**
       * Create new instance of model.
       */

    }], [{
      key: "create",

      /**
       * Create new instance of model.
       *
       * @returns {VuexBuilderModel}
       */
      value: function create() {
        return new this();
      }
    }]);

    function VuexBuilderModel() {
      babelHelpers.classCallCheck(this, VuexBuilderModel);
      this.databaseConfig = {
        type: VuexBuilder$$1.DatabaseType.indexedDb,
        active: null,
        storage: 'default',
        name: this.getName(),
        siteId: 'default',
        userId: 0,
        timeout: 150
      };
      this.db = null;
      this.store = null;
      this.namespace = null;
      this.variables = {};
      this.withNamespace = false;
    }

    babelHelpers.createClass(VuexBuilderModel, [{
      key: "setStore",
      value: function setStore(store) {
        if (!(store instanceof ui_vuex.VuexVendor.Store)) {
          console.error('VuexBuilderModel.setStore: passed store is not a Vuex.Store', store);
          return this;
        }

        this.store = store;
        return this;
      }
    }, {
      key: "_getStoreFromDatabase",
      value: function _getStoreFromDatabase() {
        var _this3 = this;

        return new Promise(function (resolve, reject) {
          _this3.db.get().then(function (cache) {
            var state = _this3.getState();

            if (cache) {
              state = _this3._mergeState(state, cache);
            }

            resolve(state);
          }, function (error) {
            resolve(_this3.getState());
          });
        });
      }
    }, {
      key: "_mergeState",
      value: function _mergeState(currentState, newState) {
        for (var key in currentState) {
          if (!currentState.hasOwnProperty(key)) {
            continue;
          }

          if (typeof newState[key] === 'undefined') {
            newState[key] = currentState[key];
          } else if (!(newState[key] instanceof Array) && babelHelpers.typeof(newState[key]) === 'object' && newState[key] && babelHelpers.typeof(currentState[key]) === 'object' && currentState[key]) {
            newState[key] = Object.assign({}, currentState[key], newState[key]);
          }
        }

        return newState;
      }
    }, {
      key: "_createStore",
      value: function _createStore(state) {
        var _this4 = this;

        var namespace = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var result = {
          state: state,
          getters: this.getGetters(),
          actions: this.getActions(),
          mutations: this.getMutations()
        };

        result.mutations.vuexBuilderModelClearState = function (state) {
          state = Object.assign(state, _this4.getState());

          _this4.saveState(state);
        };

        if (namespace) {
          result.namespaced = true;
          result = babelHelpers.defineProperty({}, namespace, result);
        }

        return result;
      }
      /**
       * Utils. Convert Object to Array
       * @param object
       * @returns {Array}
       */

    }, {
      key: "cloneState",

      /**
       * Clone state without observers
       * @param element {object}
       * @param exceptions {object}
       */
      value: function cloneState(element) {
        var _this5 = this;

        var exceptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;
        var result;

        if (element instanceof Array) {
          result = [].concat(element.map(function (element) {
            return _this5.cloneState(element);
          }));
        } else if (element instanceof Date) {
          result = new Date(element.toISOString());
        } else if (babelHelpers.typeof(element) === 'object' && element) {
          result = {};

          for (var param in element) {
            if (!element.hasOwnProperty(param)) {
              continue;
            }

            if (typeof exceptions === 'undefined' || typeof exceptions[param] === 'undefined') {
              result[param] = this.cloneState(element[param]);
            } else if (babelHelpers.typeof(exceptions[param]) === 'object' && exceptions[param]) {
              result[param] = this.cloneState(element[param], exceptions[param]);
            }
          }
        } else {
          result = element;
        }

        return result;
      }
    }], [{
      key: "convertToArray",
      value: function convertToArray(object) {
        var result = [];

        for (var i in object) {
          if (object.hasOwnProperty(i)) {
            result.push(object[i]);
          }
        }

        return result;
      }
    }]);
    return VuexBuilderModel;
  }();

  /**
   * Bitrix Vuex wrapper
   * Vuex builder
   *
   * @package bitrix
   * @subpackage ui
   * @copyright 2001-2019 Bitrix
   */
  var DatabaseType = Object.freeze({
    indexedDb: 'indexedDb',
    localStorage: 'localStorage'
  });
  var VuexBuilder$$1 =
  /*#__PURE__*/
  function () {
    babelHelpers.createClass(VuexBuilder$$1, null, [{
      key: "create",

      /**
       * Create new instance of builder.
       *
       * @returns {VuexBuilder}
       */
      value: function create() {
        return new this();
      }
    }]);

    function VuexBuilder$$1() {
      babelHelpers.classCallCheck(this, VuexBuilder$$1);
      this.models = [];
      this.databaseConfig = {
        name: null,
        type: null,
        siteId: null,
        userId: null,
        timeout: null
      };
      this.withNamespace = true;
    }
    /**
     * Add vuex module.
     *
     * @param model {VuexBuilderModel}
     *
     * @returns {VuexBuilder}
     */


    babelHelpers.createClass(VuexBuilder$$1, [{
      key: "addModel",
      value: function addModel(model) {
        if (!(model instanceof VuexBuilderModel)) {
          console.error('BX.VuexBuilder.addModel: passed model is not a BX.VuexBuilderModel', model, name);
          return this;
        }

        this.models.push(model);
        return this;
      }
      /**
       * Disable namespace for builder with single model.
       *
       * @param active {boolean}
       * @returns {VuexBuilder}
       */

    }, {
      key: "useNamespace",
      value: function useNamespace(active) {
        this.withNamespace = !!active;
        return this;
      }
      /**
       * Set database config for all models (except models with "no database" option).
       *
       * @param config {{name: String, siteId: String, userId: Number, type: DatabaseType}}
       * @returns {VuexBuilder}
       */

    }, {
      key: "setDatabaseConfig",
      value: function setDatabaseConfig() {
        var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

        if (!(babelHelpers.typeof(config) === 'object' && config)) {
          return this;
        }

        this.databaseConfig.active = true;
        this.databaseConfig.storage = config.name;
        this.databaseConfig.type = config.type || this.databaseConfig.type;
        this.databaseConfig.siteId = config.siteId || this.databaseConfig.siteId;
        this.databaseConfig.userId = config.userId || this.databaseConfig.userId;
        this.databaseConfig.timeout = typeof config.timeout !== 'undefined' ? config.timeout : this.databaseConfig.timeout;
        return this;
      }
    }, {
      key: "clearModelState",
      value: function clearModelState() {
        var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        var results = [];
        this.models.forEach(function (model) {
          results.push(model.clearState());
        });
        return new Promise(function (resolve, reject) {
          Promise.all(results).then(function (stores) {
            resolve(true);

            if (typeof callback === 'function') {
              callback(true);
            }
          }, function (error) {
            console.error('BX.VuexBuilder.clearModelState: storage was not clear due to runtime errors.', error ? error : '');

            if (typeof callback !== 'function') {
              reject('ERROR_WHILE_CLEARING');
            }
          });
        });
      }
      /**
       * Build Vuex Store
       *
       * @param callback {Function|null}
       * @returns {Promise<any>}
       */

    }, {
      key: "build",
      value: function build() {
        var _this = this;

        var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        var withNamespace = this.models.length > 1;

        if (!this.withNamespace && withNamespace) {
          return new Promise(function (resolve, reject) {
            console.error('BX.VuexBuilder.create: you can not use the "no namespace" mode with multiple databases.');

            if (typeof callback !== 'function') {
              reject('MULTIPLE_MODULES_WITHOUT_NAMESPACE');
            }
          });
        }

        var results = [];
        this.models.forEach(function (model) {
          if (_this.databaseConfig.active && model.databaseConfig.active !== false) {
            model.useDatabase(true, _this.databaseConfig);
          }

          if (_this.withNamespace) {
            model.useNamespace(true);
          }

          results.push(model.getStore());
        });
        return new Promise(function (resolve, reject) {
          Promise.all(results).then(function (stores) {
            var modules = {};
            stores.forEach(function (store) {
              Object.assign(modules, store);
            });
            var store = bitrixVuex.store(_this.withNamespace ? {
              modules: modules
            } : modules);

            _this.models.forEach(function (model) {
              return model.setStore(store);
            });

            resolve({
              store: store,
              models: _this.models,
              builder: _this
            });

            if (typeof callback === 'function') {
              callback({
                store: store,
                models: _this.models,
                builder: _this
              });
            }
          }, function (error) {
            console.error('BX.VuexBuilder.create: storage was not created due to runtime errors.', error ? error : '');

            if (typeof callback !== 'function') {
              reject('ERROR_IN_MODEL');
            }
          });
        });
      }
    }]);
    return VuexBuilder$$1;
  }();
  VuexBuilder$$1.DatabaseType = DatabaseType;

  /**
   * Bitrix Vuex wrapper
   *
   * @package bitrix
   * @subpackage ui
   * @copyright 2001-2019 Bitrix
   */

  var BitrixVuex =
  /*#__PURE__*/
  function () {
    function BitrixVuex() {
      babelHelpers.classCallCheck(this, BitrixVuex);
    }

    babelHelpers.createClass(BitrixVuex, null, [{
      key: "store",

      /**
       * Create new Vuex instance
       *
       * @param {Object} params - definition
       *
       * @see https://vuex.vuejs.org/api/#vuex-store
       */
      value: function store(params) {
        return new index.Store(params);
      }
      /**
       * Create component computed options that return the sub tree of the Vuex store.
       *
       * @param params
       * @returns {*}
       *
       * @see https://vuex.vuejs.org/api/#mapstate
       */

    }, {
      key: "mapState",
      value: function mapState() {
        return index.mapState.apply(index, arguments);
      }
      /**
       * Create component computed options that return the evaluated value of a getter.
       *
       * @param params
       * @returns {*}
       *
       * @see https://vuex.vuejs.org/api/#mapgetters
       */

    }, {
      key: "mapGetters",
      value: function mapGetters() {
        return index.mapGetters.apply(index, arguments);
      }
      /**
       * Create component methods options that dispatch an action.
       *
       * @param params
       * @returns {*}
       *
       * @see https://vuex.vuejs.org/api/#mapactions
       */

    }, {
      key: "mapActions",
      value: function mapActions() {
        return index.mapActions.apply(index, arguments);
      }
      /**
       * Create component methods options that commit a mutation.
       *
       * @param params
       * @returns {*}
       *
       * @see https://vuex.vuejs.org/api/#mapactions
       */

    }, {
      key: "mapMutations",
      value: function mapMutations() {
        return index.mapMutations.apply(index, arguments);
      }
      /**
       * Create namespaced component binding helpers.
       *
       * @param params
       * @returns {*}
       *
       * @see https://vuex.vuejs.org/api/#createnamespacedhelpers
       */

    }, {
      key: "createNamespacedHelpers",
      value: function createNamespacedHelpers() {
        return index.createNamespacedHelpers.apply(index, arguments);
      }
      /**
       * Provides the installed version of Vuex as a string.
       *
       * @returns {String}
       */

    }, {
      key: "version",
      value: function version() {
        return index.version;
      }
    }]);
    return BitrixVuex;
  }();

  var bitrixVuex = BitrixVuex;

  exports.Vuex = bitrixVuex;
  exports.VuexVendor = index;
  exports.VuexBuilder = VuexBuilder$$1;
  exports.VuexBuilderModel = VuexBuilderModel;

}((this.BX = this.BX || {}),BX,BX,BX,BX));
//# sourceMappingURL=vuex.bitrix.bundle.js.map
