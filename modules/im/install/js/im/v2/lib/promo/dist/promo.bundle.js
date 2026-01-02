/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_lib_logger,im_v2_lib_rest,im_v2_const) {
	'use strict';

	var _isParamsEqual = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isParamsEqual");
	class Promo {
	  constructor(id, _params) {
	    Object.defineProperty(this, _isParamsEqual, {
	      value: _isParamsEqual2
	    });
	    this.id = id;
	    this.params = _params;
	  }
	  static createFromRawPromoData(data) {
	    return new Promo(data.id, data.params);
	  }
	  isEmptyParams() {
	    return Object.keys(this.params).length === 0;
	  }
	  isEqual(promo) {
	    return this.id === promo.id && babelHelpers.classPrivateFieldLooseBase(this, _isParamsEqual)[_isParamsEqual](promo.params);
	  }
	}
	function _isParamsEqual2(params) {
	  var _this$params$chatId, _params$chatId;
	  return Number((_this$params$chatId = this.params.chatId) != null ? _this$params$chatId : null) === Number((_params$chatId = params.chatId) != null ? _params$chatId : null);
	}

	class PromoService {
	  static markAsWatched(promo) {
	    im_v2_lib_logger.Logger.warn('PromoService: markAsWatched:', promo);
	    const payload = {
	      data: {
	        id: promo.id,
	        params: promo.params
	      }
	    };
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2PromotionRead, payload).catch(([error]) => {
	      console.error('PromoService: markAsWatched error:', error);
	    });
	  }
	}

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _promoList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("promoList");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _get = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("get");
	var _remove = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("remove");
	var _removeByPromotionList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeByPromotionList");
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
	    Object.defineProperty(this, _removeByPromotionList, {
	      value: _removeByPromotionList2
	    });
	    Object.defineProperty(this, _remove, {
	      value: _remove2
	    });
	    Object.defineProperty(this, _get, {
	      value: _get2
	    });
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _promoList, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      promoList: _promoList2
	    } = im_v2_application_core.Core.getApplicationData();
	    im_v2_lib_logger.Logger.warn('PromoManager: promoList', _promoList2);
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init](_promoList2);
	  }
	  needToShow(promoId, promoParams = {}) {
	    const promo = new Promo(promoId, promoParams);
	    return Boolean(babelHelpers.classPrivateFieldLooseBase(this, _get)[_get](promo));
	  }
	  async markAsWatched(promoId, promoParams = {}) {
	    const promo = new Promo(promoId, promoParams);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _get)[_get](promo)) {
	      await PromoService.markAsWatched(promo);
	      babelHelpers.classPrivateFieldLooseBase(this, _remove)[_remove](promo);
	    }
	  }
	  onPromotionUpdated(params) {
	    const deletedPromotions = params.deletedPromotions.map(promoData => Promo.createFromRawPromoData(promoData));
	    babelHelpers.classPrivateFieldLooseBase(this, _removeByPromotionList)[_removeByPromotionList](deletedPromotions);
	    const addedPromotions = params.addedPromotions.map(promoData => Promo.createFromRawPromoData(promoData));
	    babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList] = [...babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList], ...addedPromotions];
	  }
	}
	function _init2(promoList) {
	  babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList] = promoList.map(promoData => Promo.createFromRawPromoData(promoData));
	}
	function _get2(promo) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList].find(item => item.isEqual(promo));
	}
	function _remove2(promo) {
	  babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList] = babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList].filter(item => !item.isEqual(promo));
	}
	function _removeByPromotionList2(promoList) {
	  babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList] = babelHelpers.classPrivateFieldLooseBase(this, _promoList)[_promoList].filter(promo => {
	    const deletedPromo = promoList.find(deleted => deleted.id === promo.id);
	    if (!deletedPromo) {
	      return true;
	    }
	    if (deletedPromo.isEmptyParams()) {
	      return false;
	    }
	    return !promo.isEqual(deletedPromo);
	  });
	}
	Object.defineProperty(PromoManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.PromoManager = PromoManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=promo.bundle.js.map
