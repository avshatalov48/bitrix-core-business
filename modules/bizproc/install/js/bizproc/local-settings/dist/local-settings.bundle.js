this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports) {
	'use strict';

	var _ttl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ttl");

	var _prefix = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prefix");

	var _getName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getName");

	class Settings {
	  constructor(section) {
	    Object.defineProperty(this, _getName, {
	      value: _getName2
	    });
	    Object.defineProperty(this, _ttl, {
	      writable: true,
	      value: 365 * 86400
	    });
	    Object.defineProperty(this, _prefix, {
	      writable: true,
	      value: 'bp-'
	    });

	    if (section) {
	      babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix] += section + '-';
	    }
	  }

	  getSet(name) {
	    const value = this.get(name);
	    return value instanceof Array ? new Set(value) : new Set();
	  }

	  get(name) {
	    return BX.localStorage.get(babelHelpers.classPrivateFieldLooseBase(this, _getName)[_getName](name));
	  }

	  set(name, value) {
	    if (value instanceof Set) {
	      value = Array.from(value);
	    }

	    BX.localStorage.set(babelHelpers.classPrivateFieldLooseBase(this, _getName)[_getName](name), value, babelHelpers.classPrivateFieldLooseBase(this, _ttl)[_ttl]);
	    return this;
	  }

	}

	function _getName2(name) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _prefix)[_prefix] + name;
	}

	exports.Settings = Settings;

}((this.BX.Bizproc.LocalSettings = this.BX.Bizproc.LocalSettings || {})));
//# sourceMappingURL=local-settings.bundle.js.map
