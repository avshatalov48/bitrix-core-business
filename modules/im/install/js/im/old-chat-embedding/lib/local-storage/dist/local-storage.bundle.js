this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
(function (exports,im_oldChatEmbedding_application_core) {
	'use strict';

	const KEY_PREFIX = 'im-v2';
	var _siteId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("siteId");
	var _userId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userId");
	var _buildKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildKey");
	class LocalStorageManager {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _buildKey, {
	      value: _buildKey2
	    });
	    Object.defineProperty(this, _siteId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userId, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _siteId)[_siteId] = im_oldChatEmbedding_application_core.Core.getSiteId();
	    babelHelpers.classPrivateFieldLooseBase(this, _userId)[_userId] = im_oldChatEmbedding_application_core.Core.getUserId();
	  }
	  set(key, value) {
	    const preparedValue = JSON.stringify(value);
	    if (localStorage.getItem(babelHelpers.classPrivateFieldLooseBase(this, _buildKey)[_buildKey](key)) === preparedValue) {
	      return;
	    }
	    localStorage.setItem(babelHelpers.classPrivateFieldLooseBase(this, _buildKey)[_buildKey](key), preparedValue);
	  }
	  get(key, defaultValue = null) {
	    const result = localStorage.getItem(babelHelpers.classPrivateFieldLooseBase(this, _buildKey)[_buildKey](key));
	    if (result === null) {
	      return defaultValue;
	    }
	    try {
	      return JSON.parse(result);
	    } catch {
	      return defaultValue;
	    }
	  }
	  remove(key) {
	    localStorage.removeItem(babelHelpers.classPrivateFieldLooseBase(this, _buildKey)[_buildKey](key));
	  }
	}
	function _buildKey2(key) {
	  return `${KEY_PREFIX}-${babelHelpers.classPrivateFieldLooseBase(this, _siteId)[_siteId]}-${babelHelpers.classPrivateFieldLooseBase(this, _userId)[_userId]}-${key}`;
	}

	exports.LocalStorageManager = LocalStorageManager;

}((this.BX.Messenger.Embedding.Lib = this.BX.Messenger.Embedding.Lib || {}),BX.Messenger.Embedding.Application));
//# sourceMappingURL=local-storage.bundle.js.map
