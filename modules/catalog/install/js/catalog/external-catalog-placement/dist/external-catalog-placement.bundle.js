/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _CODE = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("CODE");
	var _appSid = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appSid");
	var _initializePromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initializePromise");
	var _isInitialized = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInitialized");
	var _initializationError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initializationError");
	var _loadPlacement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadPlacement");
	var _registerPlacement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerPlacement");
	var _loadPlacementLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadPlacementLayout");
	var _runPlacementLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("runPlacementLayout");
	var _waitForOnReadyEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("waitForOnReadyEvent");
	class ExternalCatalogPlacement {
	  constructor() {
	    Object.defineProperty(this, _waitForOnReadyEvent, {
	      value: _waitForOnReadyEvent2
	    });
	    Object.defineProperty(this, _runPlacementLayout, {
	      value: _runPlacementLayout2
	    });
	    Object.defineProperty(this, _loadPlacementLayout, {
	      value: _loadPlacementLayout2
	    });
	    Object.defineProperty(this, _registerPlacement, {
	      value: _registerPlacement2
	    });
	    Object.defineProperty(this, _loadPlacement, {
	      value: _loadPlacement2
	    });
	    Object.defineProperty(this, _appSid, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _initializePromise, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _isInitialized, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _initializationError, {
	      writable: true,
	      value: null
	    });
	  }
	  static create() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  initialize() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _initializePromise)[_initializePromise]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _initializePromise)[_initializePromise] = new Promise((resolve, reject) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _loadPlacement)[_loadPlacement]().then(response => babelHelpers.classPrivateFieldLooseBase(this, _registerPlacement)[_registerPlacement](response)).then(data => babelHelpers.classPrivateFieldLooseBase(this, _loadPlacementLayout)[_loadPlacementLayout](data)).then(response => babelHelpers.classPrivateFieldLooseBase(this, _runPlacementLayout)[_runPlacementLayout](response)).then(() => babelHelpers.classPrivateFieldLooseBase(this, _waitForOnReadyEvent)[_waitForOnReadyEvent]()).then(() => {
	          babelHelpers.classPrivateFieldLooseBase(this, _isInitialized)[_isInitialized] = true;
	          resolve();
	        }).catch(error => {
	          babelHelpers.classPrivateFieldLooseBase(this, _isInitialized)[_isInitialized] = true;
	          babelHelpers.classPrivateFieldLooseBase(this, _initializationError)[_initializationError] = error;
	          reject(error);
	        });
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _initializePromise)[_initializePromise];
	  }
	  reset() {
	    babelHelpers.classPrivateFieldLooseBase(this, _initializePromise)[_initializePromise] = null;
	  }
	  getAppSidId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _appSid)[_appSid];
	  }
	  isInitialized() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isInitialized)[_isInitialized];
	  }
	  isInitializedSuccessfully() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isInitialized)[_isInitialized] && this.getInitializationError() === null;
	  }
	  getInitializationError() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _initializationError)[_initializationError];
	  }
	}
	function _loadPlacement2() {
	  if (main_core.Extension.getSettings('catalog.external-catalog-placement').get('is1cPlanRestricted', false)) {
	    return Promise.reject({
	      reason: 'tariff'
	    });
	  }
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runComponentAction('bitrix:app.placement', 'getComponent', {
	      data: {
	        placementId: babelHelpers.classPrivateFieldLooseBase(ExternalCatalogPlacement, _CODE)[_CODE]
	      }
	    }).then(response => resolve(response)).catch(() => {
	      reject({
	        reason: ExternalCatalogPlacement.LOAD_PLACEMENT_ERROR
	      });
	    });
	  });
	}
	function _registerPlacement2(response) {
	  return new Promise((resolve, reject) => {
	    const node = main_core.Tag.render(_t || (_t = _`<div style="display: none; overflow: hidden;"></div>`));
	    main_core.Dom.append(node, document.body);
	    main_core.Runtime.html(node, response.data.html, {
	      callback: () => setTimeout(() => {
	        const appLayout = BX.Reflection.getClass('BX.rest.AppLayout');
	        const placement = appLayout ? appLayout.getPlacement(babelHelpers.classPrivateFieldLooseBase(ExternalCatalogPlacement, _CODE)[_CODE]) : null;
	        if (placement) {
	          resolve({
	            placement,
	            placementInterface: BX.rest.AppLayout.initializePlacement(babelHelpers.classPrivateFieldLooseBase(ExternalCatalogPlacement, _CODE)[_CODE])
	          });
	          return;
	        }
	        reject({
	          reason: ExternalCatalogPlacement.REGISTER_PLACEMENT_ERROR
	        });
	      }, 10)
	    });
	  });
	}
	function _loadPlacementLayout2(data) {
	  return new Promise((resolve, reject) => {
	    // eslint-disable-next-line no-param-reassign
	    data.placementInterface.prototype.onReady = eventData => {
	      main_core_events.EventEmitter.emit('Catalog:ProductSelectorPlacement:onReady', eventData);
	    };

	    // eslint-disable-next-line no-param-reassign
	    data.placementInterface.prototype.onProductCreated = eventData => {
	      main_core_events.EventEmitter.emit('Catalog:ProductSelectorPlacement:onProductCreated', eventData);
	    };

	    // eslint-disable-next-line no-param-reassign
	    data.placementInterface.prototype.onProductUpdated = eventData => {
	      main_core_events.EventEmitter.emit('Catalog:ProductSelectorPlacement:onProductUpdated', eventData);
	    };

	    // eslint-disable-next-line no-param-reassign
	    data.placementInterface.prototype.onProductsFound = eventData => {
	      main_core_events.EventEmitter.emit('Catalog:ProductSelectorPlacement:onProductsFound', eventData);
	    };
	    data.placementInterface.prototype.events.push('Catalog:ProductSelectorPlacement:onNeedProductCreate', 'Catalog:ProductSelectorPlacement:onNeedProductUpdate', 'Catalog:ProductSelectorPlacement:onNeedSearchProducts');
	    main_core.ajax.runComponentAction('bitrix:app.layout', 'getComponent', {
	      data: {
	        placementId: data.placement.param.current,
	        placementOptions: null
	      }
	    }).then(response => {
	      resolve(response);
	    }).catch(() => {
	      reject({
	        reason: ExternalCatalogPlacement.LOAD_PLACEMENT_LAYOUT_ERROR
	      });
	    });
	  });
	}
	function _runPlacementLayout2(response) {
	  return new Promise(resolve => {
	    babelHelpers.classPrivateFieldLooseBase(this, _appSid)[_appSid] = response.data.componentResult.APP_SID;
	    const iframeNode = main_core.Tag.render(_t2 || (_t2 = _`
				<div
					data-app-sid="${0}"
					style="display: none; overflow: hidden"
				>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _appSid)[_appSid]);
	    main_core.Dom.append(iframeNode, document.body);
	    main_core.Runtime.html(iframeNode, response.data.html);
	    resolve();
	  });
	}
	function _waitForOnReadyEvent2() {
	  return new Promise((resolve, reject) => {
	    main_core_events.EventEmitter.subscribe('Catalog:ProductSelectorPlacement:onReady', () => {
	      resolve();
	    });
	    setTimeout(() => reject({
	      reason: 'timeout'
	    }), ExternalCatalogPlacement.RESPONSE_TIMEOUT);
	  });
	}
	Object.defineProperty(ExternalCatalogPlacement, _instance, {
	  writable: true,
	  value: null
	});
	Object.defineProperty(ExternalCatalogPlacement, _CODE, {
	  writable: true,
	  value: 'CATALOG_EXTERNAL_PRODUCT'
	});
	ExternalCatalogPlacement.LOAD_PLACEMENT_ERROR = 'LOAD_PLACEMENT_ERROR';
	ExternalCatalogPlacement.REGISTER_PLACEMENT_ERROR = 'REGISTER_PLACEMENT_ERROR';
	ExternalCatalogPlacement.LOAD_PLACEMENT_LAYOUT_ERROR = 'LOAD_PLACEMENT_LAYOUT_ERROR';
	ExternalCatalogPlacement.RESPONSE_TIMEOUT = 20000;

	exports.ExternalCatalogPlacement = ExternalCatalogPlacement;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX.Event));
//# sourceMappingURL=external-catalog-placement.bundle.js.map
