/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_rest,main_core,main_popup,im_v2_const,im_v2_lib_feature) {
	'use strict';

	const AccessErrorCode = {
	  accessDenied: 'ACCESS_DENIED',
	  chatNotFound: 'CHAT_NOT_FOUND',
	  messageNotFound: 'MESSAGE_NOT_FOUND',
	  messageAccessDenied: 'MESSAGE_ACCESS_DENIED',
	  messageAccessDeniedByTariff: 'MESSAGE_ACCESS_DENIED_BY_TARIFF'
	};
	const AccessService = {
	  async checkMessageAccess(messageId) {
	    const payload = {
	      data: {
	        messageId
	      }
	    };
	    try {
	      await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2AccessCheck, payload);
	    } catch (errors) {
	      return handleAccessError(errors);
	    }
	    return Promise.resolve({
	      hasAccess: true
	    });
	  }
	};
	const handleAccessError = errors => {
	  const [error] = errors;
	  const availableCodes = Object.values(AccessErrorCode);
	  if (!availableCodes.includes(error.code)) {
	    console.error('AccessService: error checking access', error.code);

	    // we need to handle all types of errors on this stage
	    // but for now we let user through in case of unknown error
	    return {
	      hasAccess: true
	    };
	  }
	  return {
	    hasAccess: false,
	    errorCode: error.code
	  };
	};

	let _ = t => t,
	  _t,
	  _t2;
	var _popupInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupInstance");
	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _getPopupConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupConfig");
	var _getContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainer");
	var _getButtonContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButtonContainer");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _unbindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unbindEvents");
	class HistoryLimitPopup {
	  constructor() {
	    Object.defineProperty(this, _unbindEvents, {
	      value: _unbindEvents2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _getButtonContainer, {
	      value: _getButtonContainer2
	    });
	    Object.defineProperty(this, _getContainer, {
	      value: _getContainer2
	    });
	    Object.defineProperty(this, _getPopupConfig, {
	      value: _getPopupConfig2
	    });
	    Object.defineProperty(this, _popupInstance, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance] = new main_popup.Popup(babelHelpers.classPrivateFieldLooseBase(this, _getPopupConfig)[_getPopupConfig]());
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].show();
	  }
	  close() {
	    babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].destroy();
	  }
	}
	function _getPopupConfig2() {
	  return {
	    id: im_v2_const.PopupType.messageHistoryLimit,
	    className: 'bx-im-messenger__scope',
	    closeIcon: false,
	    autoHide: false,
	    closeByEsc: false,
	    animation: 'fading',
	    overlay: true,
	    padding: 0,
	    content: babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer](),
	    events: {
	      onPopupDestroy: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _unbindEvents)[_unbindEvents]();
	      }
	    }
	  };
	}
	function _getContainer2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('', () => {
	    const container = main_core.Tag.render(_t || (_t = _`
				<div class="bx-im-history-limit-popup__container">
					<div class="bx-im-history-limit-popup__image"></div>
					<div class="bx-im-history-limit-popup__title">
						${0}
					</div>
					<div class="bx-im-history-limit-popup__subtitle">
						${0}
					</div>
				</div>
			`), im_v2_lib_feature.FeatureManager.chatHistory.getLimitTitle(), im_v2_lib_feature.FeatureManager.chatHistory.getLimitSubtitle());
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getButtonContainer)[_getButtonContainer](), container);
	    return container;
	  });
	}
	function _getButtonContainer2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('', () => {
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="bx-im-history-limit-popup__button">
					${0}
				</div>
			`), im_v2_lib_feature.FeatureManager.chatHistory.getLearnMoreText());
	  });
	}
	function _bindEvents2() {
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _getButtonContainer)[_getButtonContainer](), 'click', () => {
	    im_v2_lib_feature.FeatureManager.chatHistory.openFeatureSlider();
	    this.close();
	  });
	}
	function _unbindEvents2() {
	  main_core.Event.unbindAll(babelHelpers.classPrivateFieldLooseBase(this, _getButtonContainer)[_getButtonContainer](), 'click');
	}

	const AccessManager = {
	  checkMessageAccess(messageId) {
	    return AccessService.checkMessageAccess(messageId);
	  },
	  // save it for later
	  showHistoryLimitPopup() {
	    const limitPopup = new HistoryLimitPopup();
	    limitPopup.show();
	  }
	};

	exports.AccessManager = AccessManager;
	exports.AccessErrorCode = AccessErrorCode;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX,BX.Main,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=access.bundle.js.map
