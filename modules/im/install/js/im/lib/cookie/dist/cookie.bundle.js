/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_lib_localstorage) {
	'use strict';

	/**
	 * Bitrix Im
	 * Cookie manager
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var Cookie = {
	  get: function get(siteId, name) {
	    var cookieName = siteId ? siteId + '_' + name : name;
	    if (navigator.cookieEnabled) {
	      var result = document.cookie.match(new RegExp("(?:^|; )" + cookieName.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"));
	      if (result) {
	        return decodeURIComponent(result[1]);
	      }
	    }
	    if (im_lib_localstorage.LocalStorage.isEnabled()) {
	      var _result = im_lib_localstorage.LocalStorage.get(siteId, 0, name, undefined);
	      if (typeof _result !== 'undefined') {
	        return _result;
	      }
	    }
	    if (typeof window.BX.GuestUserCookie === 'undefined') {
	      window.BX.GuestUserCookie = {};
	    }
	    return window.BX.GuestUserCookie[cookieName];
	  },
	  set: function set(siteId, name, value, options) {
	    options = options || {};
	    var expires = options.expires;
	    if (typeof expires == "number" && expires) {
	      var currentDate = new Date();
	      currentDate.setTime(currentDate.getTime() + expires * 1000);
	      expires = options.expires = currentDate;
	    }
	    if (expires && expires.toUTCString) {
	      options.expires = expires.toUTCString();
	    }
	    value = encodeURIComponent(value);
	    var cookieName = siteId ? siteId + '_' + name : name;
	    var updatedCookie = cookieName + "=" + value;
	    for (var propertyName in options) {
	      if (!options.hasOwnProperty(propertyName)) {
	        continue;
	      }
	      updatedCookie += "; " + propertyName;
	      var propertyValue = options[propertyName];
	      if (propertyValue !== true) {
	        updatedCookie += "=" + propertyValue;
	      }
	    }
	    document.cookie = updatedCookie;
	    if (typeof window.BX.GuestUserCookie === 'undefined') {
	      BX.GuestUserCookie = {};
	    }
	    window.BX.GuestUserCookie[cookieName] = value;
	    im_lib_localstorage.LocalStorage.set(siteId, 0, name, value);
	    return true;
	  }
	};

	exports.Cookie = Cookie;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {}),BX.Messenger.Lib));
//# sourceMappingURL=cookie.bundle.js.map
