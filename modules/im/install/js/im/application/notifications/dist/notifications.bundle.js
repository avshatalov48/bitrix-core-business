/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_application_core,ui_vue,im_component_notifications,im_provider_pull) {
	'use strict';

	/**
	 * Bitrix Im
	 * Core application
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var NotificationsApplication = /*#__PURE__*/function () {
	  /* region 01. Initialize */

	  function NotificationsApplication() {
	    var _this = this;
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, NotificationsApplication);
	    this.inited = false;
	    this.initPromise = new BX.Promise();
	    this.params = params;
	    this.template = null;
	    this.rootNode = this.params.node || document.createElement('div');
	    this.legacyMode = this.params.mode === 'legacy';
	    this.initCounter = this.params.initCounter || null;
	    this.templateTemp = null;
	    this.eventBus = new ui_vue.VueVendorV2(); // TODO remove this! change to Bitrix EventEmitter

	    this.initCore().then(function () {
	      return _this.initParams();
	    }).then(function () {
	      return _this.initComponent(_this.legacyMode);
	    }).then(function () {
	      return _this.initPullClient();
	    }).then(function () {
	      return _this.initPullHandlers();
	    }).then(function () {
	      return _this.initComplete();
	    });
	  }
	  babelHelpers.createClass(NotificationsApplication, [{
	    key: "initPullClient",
	    value: function initPullClient() {
	      this.pullClient = BX.PULL;
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initPullHandlers",
	    value: function initPullHandlers() {
	      this.pullClient.subscribe(new im_provider_pull.ImNotificationsPullHandler({
	        store: this.controller.getStore(),
	        application: this,
	        controller: this.controller
	      }));
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initCore",
	    value: function initCore() {
	      var _this2 = this;
	      return new Promise(function (resolve, reject) {
	        im_application_core.Core.ready().then(function (controller) {
	          _this2.controller = controller;
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "initParams",
	    value: function initParams() {
	      var _this3 = this;
	      if (this.initCounter) {
	        this.controller.getStore().dispatch('notifications/setCounter', {
	          unreadTotal: this.initCounter
	        });
	      }
	      this.controller.getStore().subscribe(function (mutation) {
	        return _this3.eventStoreInteraction(mutation);
	      });
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initComponent",
	    value: function initComponent(legacy) {
	      var _this4 = this;
	      if (legacy) {
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      }
	      var template;
	      if (this.legacyMode) {
	        template = '<bx-im-component-notifications/>';
	      } else {
	        template = "<div style=\"height: 400px; border: 1px solid #ccc;\">\n\t\t\t\t<bx-im-component-notifications/>\n\t\t\t</div>";
	      }
	      return this.controller.createVue(this, {
	        el: this.rootNode,
	        template: template
	      }).then(function (vue) {
	        _this4.template = vue;
	        _this4.template.$el.id = _this4.rootNode.substr(1);
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "initComplete",
	    value: function initComplete() {
	      this.inited = true;
	      this.initPromise.resolve(this);
	    }
	  }, {
	    key: "ready",
	    value: function ready() {
	      if (this.inited) {
	        var promise = new BX.Promise();
	        promise.resolve(this);
	        return promise;
	      }
	      return this.initPromise;
	    }
	    /* endregion 01. Initialize */
	    /* region 02. Event Bus */
	  }, {
	    key: "emit",
	    value: function emit(eventName) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      this.eventBus.$emit(eventName, params);
	      return true;
	    }
	  }, {
	    key: "listen",
	    value: function listen(eventName, callback) {
	      if (typeof callback !== 'function') {
	        return false;
	      }
	      this.eventBus.$on(eventName, callback);
	      return true;
	    } /* endregion 02. Event Bus */
	  }, {
	    key: "hasVueInstance",
	    value: function hasVueInstance() {
	      return this.template !== null;
	    }
	  }, {
	    key: "destroyVueInstance",
	    value: function destroyVueInstance() {
	      this.template.$destroy();
	      this.template = null;
	    }
	  }, {
	    key: "eventStoreInteraction",
	    value: function eventStoreInteraction(data) {
	      if (data.type === 'notifications/setCounter') {
	        if (parseInt(data.payload) >= 0) {
	          BXIM.notify.updateNotifyNextCount(parseInt(data.payload), true);
	        }
	      }
	    }
	  }]);
	  return NotificationsApplication;
	}();

	exports.NotificationsApplication = NotificationsApplication;

}((this.BX.Messenger.Application = this.BX.Messenger.Application || {}),BX.Messenger.Application,BX,BX.Messenger,BX.Messenger.Provider.Pull));
//# sourceMappingURL=notifications.bundle.js.map
