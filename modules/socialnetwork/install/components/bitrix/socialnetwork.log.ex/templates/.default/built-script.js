/* eslint-disable */
this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
this.BX.Socialnetwork.Log = this.BX.Socialnetwork.Log || {};
(function (exports,ui_promoVideoPopup,ai_copilotPromoPopup,main_core,ui_bannerDispatcher) {
	'use strict';

	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _bindPromo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindPromo");
	var _getFeedPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFeedPopup");
	var _getChatPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChatPopup");
	class FeedAiPromo {
	  constructor(params) {
	    Object.defineProperty(this, _getChatPopup, {
	      value: _getChatPopup2
	    });
	    Object.defineProperty(this, _getFeedPopup, {
	      value: _getFeedPopup2
	    });
	    Object.defineProperty(this, _bindPromo, {
	      value: _bindPromo2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _bindPromo)[_bindPromo](babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].feedPromo, babelHelpers.classPrivateFieldLooseBase(this, _getFeedPopup)[_getFeedPopup]());
	    babelHelpers.classPrivateFieldLooseBase(this, _bindPromo)[_bindPromo](babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].chatPromo, babelHelpers.classPrivateFieldLooseBase(this, _getChatPopup)[_getChatPopup]());
	  }
	  onCopilotPromoHide(promo, onDone) {
	    main_core.ajax.runAction('socialnetwork.promotion.setViewed', {
	      data: {
	        promotion: promo.type
	      }
	    }).catch(err => {
	      console.error(err);
	    });
	    onDone();
	  }
	}
	function _bindPromo2(promo, popup) {
	  if (promo.isShown) {
	    return;
	  }
	  ui_bannerDispatcher.BannerDispatcher.high.toQueue(onDone => {
	    popup.subscribe(ui_promoVideoPopup.PromoVideoPopupEvents.HIDE, this.onCopilotPromoHide.bind(this, promo, onDone));
	    popup.show();
	  });
	}
	function _getFeedPopup2() {
	  const blogContainer = document.querySelector('#sonet_log_microblog_container');
	  if (!blogContainer) {
	    return null;
	  }
	  return ai_copilotPromoPopup.CopilotPromoPopup.createByPresetId({
	    presetId: ai_copilotPromoPopup.CopilotPromoPopup.Preset.LIVE_FEED_EDITOR,
	    targetOptions: blogContainer,
	    offset: {
	      left: 65,
	      top: 0
	    },
	    angleOptions: {
	      position: ui_promoVideoPopup.AnglePosition.TOP,
	      offset: 73
	    }
	  });
	}
	function _getChatPopup2() {
	  const copilotChatButton = document.querySelector('#bx-im-bar-copilot');
	  if (!copilotChatButton) {
	    return null;
	  }
	  return ai_copilotPromoPopup.CopilotPromoPopup.createByPresetId({
	    presetId: ai_copilotPromoPopup.CopilotPromoPopup.Preset.CHAT,
	    targetOptions: copilotChatButton,
	    offset: {
	      left: -ui_promoVideoPopup.PromoVideoPopup.getWidth(),
	      top: -67
	    },
	    angleOptions: {
	      position: ui_promoVideoPopup.AnglePosition.RIGHT,
	      offset: 30
	    }
	  });
	}

	exports.FeedAiPromo = FeedAiPromo;

}((this.BX.Socialnetwork.Log.Ex = this.BX.Socialnetwork.Log.Ex || {}),BX.UI,BX.AI,BX,BX.UI));
//# sourceMappingURL=built-script.js.map
