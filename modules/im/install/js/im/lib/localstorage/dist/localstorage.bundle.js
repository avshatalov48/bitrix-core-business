/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * LocalStorage manager
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var LocalStorage = /*#__PURE__*/function () {
	  function LocalStorage() {
	    babelHelpers.classCallCheck(this, LocalStorage);
	    this.enabled = null;
	    this.expireList = null;
	    this.expireInterval = null;
	  }
	  babelHelpers.createClass(LocalStorage, [{
	    key: "isEnabled",
	    value: function isEnabled() {
	      if (this.enabled !== null) {
	        return this.enabled;
	      }
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
	      if (this.enabled && !this.expireInterval) {
	        try {
	          var expireList = window.localStorage.getItem('bx-messenger-localstorage-expire');
	          if (expireList) {
	            this.expireList = JSON.parse(expireList);
	          }
	        } catch (e) {}
	        clearInterval(this.expireInterval);
	        this.expireInterval = setInterval(this._checkExpireInterval.bind(this), 60000);
	      }
	      return this.enabled;
	    }
	  }, {
	    key: "set",
	    value: function set(siteId, userId, name, value) {
	      var ttl = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 0;
	      if (!this.isEnabled()) {
	        return false;
	      }
	      var expire = null;
	      if (ttl) {
	        expire = new Date(new Date().getTime() + ttl * 1000);
	      }
	      var storeValue = JSON.stringify({
	        value: value,
	        expire: expire
	      });
	      if (window.localStorage.getItem(this._getKey(siteId, userId, name)) !== storeValue) {
	        window.localStorage.setItem(this._getKey(siteId, userId, name), storeValue);
	      }
	      if (ttl) {
	        if (!this.expireList) {
	          this.expireList = {};
	        }
	        this.expireList[this._getKey(siteId, userId, name)] = expire;
	        window.localStorage.setItem('bx-messenger-localstorage-expire', JSON.stringify(this.expireList));
	      }
	      return true;
	    }
	  }, {
	    key: "get",
	    value: function get(siteId, userId, name, defaultValue) {
	      if (!this.isEnabled()) {
	        return typeof defaultValue !== 'undefined' ? defaultValue : null;
	      }
	      var result = window.localStorage.getItem(this._getKey(siteId, userId, name));
	      if (result === null) {
	        return typeof defaultValue !== 'undefined' ? defaultValue : null;
	      }
	      try {
	        result = JSON.parse(result);
	        if (result && typeof result.value !== 'undefined') {
	          if (!result.expire || new Date(result.expire) > new Date()) {
	            result = result.value;
	          } else {
	            window.localStorage.removeItem(this._getKey(siteId, userId, name));
	            if (this.expireList) {
	              delete this.expireList[this._getKey(siteId, userId, name)];
	            }
	            return typeof defaultValue !== 'undefined' ? defaultValue : null;
	          }
	        } else {
	          return typeof defaultValue !== 'undefined' ? defaultValue : null;
	        }
	      } catch (e) {
	        return typeof defaultValue !== 'undefined' ? defaultValue : null;
	      }
	      return result;
	    }
	  }, {
	    key: "remove",
	    value: function remove(siteId, userId, name) {
	      if (!this.isEnabled()) {
	        return false;
	      }
	      if (this.expireList) {
	        delete this.expireList[this._getKey(siteId, userId, name)];
	      }
	      return window.localStorage.removeItem(this._getKey(siteId, userId, name));
	    }
	  }, {
	    key: "_getKey",
	    value: function _getKey(siteId, userId, name) {
	      return 'bx-messenger-' + siteId + '-' + userId + '-' + name;
	    }
	  }, {
	    key: "_checkExpireInterval",
	    value: function _checkExpireInterval() {
	      if (!this.expireList) return true;
	      var currentTime = new Date();
	      var count = 0;
	      for (var name in this.expireList) {
	        if (!this.expireList.hasOwnProperty(name)) {
	          continue;
	        }
	        if (new Date(this.expireList[name]) <= currentTime) {
	          window.localStorage.removeItem(name);
	          delete this.expireList[name];
	        } else {
	          count++;
	        }
	      }
	      if (count) {
	        window.localStorage.setItem('bx-messenger-localstorage-expire', JSON.stringify(this.expireList));
	      } else {
	        this.expireList = null;
	        window.localStorage.removeItem('bx-messenger-localstorage-expire');
	      }
	      return true;
	    }
	  }]);
	  return LocalStorage;
	}();
	var localStorage = new LocalStorage();

	exports.LocalStorage = localStorage;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {})));
//# sourceMappingURL=localstorage.bundle.js.map
