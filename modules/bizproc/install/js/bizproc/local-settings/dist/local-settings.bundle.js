/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core) {
	'use strict';

	var _prefix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prefix");
	class Settings {
	  constructor(section) {
	    Object.defineProperty(this, _prefix, {
	      writable: true,
	      value: 'bp'
	    });
	    if (section) {
	      babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix] += '-' + section;
	    }
	  }
	  getSet(name) {
	    const value = this.get(name);
	    return value instanceof Array ? new Set(value) : new Set();
	  }
	  get(name) {
	    const settings = new main_core.Cache.LocalStorageCache().remember(babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix], {});
	    return settings.hasOwnProperty(name) ? settings[name] : null;
	  }
	  set(name, value) {
	    if (value instanceof Set) {
	      value = Array.from(value);
	    }
	    const cache = new main_core.Cache.LocalStorageCache();
	    const settings = cache.remember(babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix], {});
	    settings[name] = value;
	    cache.set(babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix], settings);
	    return this;
	  }
	  remember(key, defaultValue) {
	    const cacheValue = this.get(key);
	    if (!main_core.Type.isNull(cacheValue)) {
	      return cacheValue;
	    }
	    this.set(key, defaultValue);
	    return this.get(key);
	  }
	  getAll() {
	    return new main_core.Cache.LocalStorageCache().remember(babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix], {});
	  }
	  deleteAll() {
	    const cache = new main_core.Cache.LocalStorageCache();
	    cache.set(babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix], {});
	  }
	}

	exports.Settings = Settings;

}((this.BX.Bizproc.LocalSettings = this.BX.Bizproc.LocalSettings || {}),BX));
//# sourceMappingURL=local-settings.bundle.js.map
