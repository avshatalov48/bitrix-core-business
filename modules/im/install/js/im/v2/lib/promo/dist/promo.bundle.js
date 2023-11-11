/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const,im_v2_lib_logger) {
	'use strict';

	class PromoService {
	  static markAsWatched(promoId) {
	    im_v2_lib_logger.Logger.warn('PromoService: markAsWatched:', promoId);
	    return im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imPromotionRead, {
	      id: promoId
	    }).catch(error => {
	      console.error('PromoService: markAsWatched error:', error);
	    });
	  }
	}

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _promoList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("promoList");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	class PromoManager {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  static init() {
	    PromoManager.getInstance();
	  }
	  constructor() {
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _promoList, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      promoList
	    } = im_v2_application_core.Core.getApplicationData();
	    im_v2_lib_logger.Logger.warn('PromoManager: promoList', promoList);
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init](promoList);
	  }
	  needToShow(promoId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList].has(promoId);
	  }
	  async markAsWatched(promoId) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList].has(promoId)) {
	      return;
	    }
	    await PromoService.markAsWatched(promoId);
	    babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList].delete(promoId);
	  }
	}
	function _init2(rawPromoList) {
	  babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList] = new Set(rawPromoList);
	}
	Object.defineProperty(PromoManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.PromoManager = PromoManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=promo.bundle.js.map
