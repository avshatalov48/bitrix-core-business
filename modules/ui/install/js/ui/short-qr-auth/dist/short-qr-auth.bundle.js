/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_qrcode,ui_buttons,pull_client,main_loader) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _intent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("intent");
	var _ttl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ttl");
	var _ttlInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ttlInterval");
	var _small = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("small");
	var _stub = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stub");
	var _getContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainer");
	var _getQrNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getQrNode");
	var _getQrStub = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getQrStub");
	var _getShowQrButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getShowQrButton");
	var _addQrCodeImage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addQrCodeImage");
	var _clean = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clean");
	var _loading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loading");
	var _createQrCodeImage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createQrCodeImage");
	var _getQrSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getQrSize");
	var _subscribeEventRefresh = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeEventRefresh");
	class ShortQrAuth {
	  constructor(options = {}) {
	    Object.defineProperty(this, _subscribeEventRefresh, {
	      value: _subscribeEventRefresh2
	    });
	    Object.defineProperty(this, _getQrSize, {
	      value: _getQrSize2
	    });
	    Object.defineProperty(this, _createQrCodeImage, {
	      value: _createQrCodeImage2
	    });
	    Object.defineProperty(this, _loading, {
	      value: _loading2
	    });
	    Object.defineProperty(this, _clean, {
	      value: _clean2
	    });
	    Object.defineProperty(this, _addQrCodeImage, {
	      value: _addQrCodeImage2
	    });
	    Object.defineProperty(this, _getShowQrButton, {
	      value: _getShowQrButton2
	    });
	    Object.defineProperty(this, _getQrStub, {
	      value: _getQrStub2
	    });
	    Object.defineProperty(this, _getQrNode, {
	      value: _getQrNode2
	    });
	    Object.defineProperty(this, _getContainer, {
	      value: _getContainer2
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    Object.defineProperty(this, _intent, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _ttl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _ttlInterval, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _small, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stub, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _intent)[_intent] = main_core.Type.isString(options.intent) ? options.intent : 'default';
	    babelHelpers.classPrivateFieldLooseBase(this, _small)[_small] = main_core.Type.isBoolean(options.small) ? options.small : false;
	    babelHelpers.classPrivateFieldLooseBase(this, _ttl)[_ttl] = main_core.Type.isNumber(options.ttl) ? options.ttl : 60;
	    babelHelpers.classPrivateFieldLooseBase(this, _stub)[_stub] = main_core.Type.isBoolean(options.stub) ? options.stub : true;
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer]();
	  }
	}
	function _getContainer2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('container', () => {
	    const qrNode = babelHelpers.classPrivateFieldLooseBase(this, _getQrNode)[_getQrNode]();
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _stub)[_stub]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _addQrCodeImage)[_addQrCodeImage]();
	    }
	    return main_core.Tag.render(_t || (_t = _`
				<div class="ui-short-qr-auth__container ${0}">
					<div class="ui-short-qr-auth__corner --top-left"></div>
					<div class="ui-short-qr-auth__corner --top-right"></div>
					<div class="ui-short-qr-auth__corner --bottom-left"></div>
					<div class="ui-short-qr-auth__corner --bottom-right"></div>
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _small)[_small] ? '--small' : '', qrNode);
	  });
	}
	function _getQrNode2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('qrNode', () => {
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-short-qr-auth__qr">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _stub)[_stub] ? babelHelpers.classPrivateFieldLooseBase(this, _getQrStub)[_getQrStub]() : null);
	  });
	}
	function _getQrStub2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('qrStub', () => {
	    return main_core.Tag.render(_t3 || (_t3 = _`
				<div class="ui-short-qr-auth__qr-stub">
					<i class="ui-icon-set --o-qr-code ui-short-qr-auth__qr-stub-icon"></i>
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getShowQrButton)[_getShowQrButton]().render());
	  });
	}
	function _getShowQrButton2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('showQrButton', () => {
	    return new ui_buttons.Button({
	      size: ui_buttons.Button.Size.EXTRA_SMALL,
	      text: main_core.Loc.getMessage('UI_SHORT_QR_AUTH_BUTTON_TITLE'),
	      useAirDesign: true,
	      maxWidth: 117,
	      style: ui_buttons.AirButtonStyle.TINTED,
	      noCaps: true,
	      wide: true,
	      onclick: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _addQrCodeImage)[_addQrCodeImage]();
	      }
	    });
	  });
	}
	function _addQrCodeImage2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _createQrCodeImage)[_createQrCodeImage]();
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _ttlInterval)[_ttlInterval]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _ttlInterval)[_ttlInterval] = setInterval(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _createQrCodeImage)[_createQrCodeImage]();
	    }, babelHelpers.classPrivateFieldLooseBase(this, _ttl)[_ttl] * 1000);
	  }
	}
	function _clean2() {
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _getQrNode)[_getQrNode]());
	}
	function _loading2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _clean)[_clean]();
	  new main_loader.Loader({
	    size: 60
	  }).show(babelHelpers.classPrivateFieldLooseBase(this, _getQrNode)[_getQrNode]());
	}
	function _createQrCodeImage2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _loading)[_loading]();
	  main_core.ajax.runAction('mobile.deeplink.get', {
	    data: {
	      intent: babelHelpers.classPrivateFieldLooseBase(this, _intent)[_intent],
	      ttl: babelHelpers.classPrivateFieldLooseBase(this, _ttl)[_ttl]
	    }
	  }).then(response => {
	    var _response$data;
	    const link = (_response$data = response.data) == null ? void 0 : _response$data.link;
	    if (link) {
	      babelHelpers.classPrivateFieldLooseBase(this, _clean)[_clean]();
	      // eslint-disable-next-line no-undef
	      new QRCode(babelHelpers.classPrivateFieldLooseBase(this, _getQrNode)[_getQrNode](), {
	        text: link,
	        width: babelHelpers.classPrivateFieldLooseBase(this, _getQrSize)[_getQrSize](),
	        height: babelHelpers.classPrivateFieldLooseBase(this, _getQrSize)[_getQrSize]()
	      });
	      if (!this.isSubscribe) {
	        this.isSubscribe = true;
	        babelHelpers.classPrivateFieldLooseBase(this, _subscribeEventRefresh)[_subscribeEventRefresh]();
	      }
	    }
	  }).catch(() => {});
	}
	function _getQrSize2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _small)[_small] ? 98 : 151;
	}
	function _subscribeEventRefresh2() {
	  if (pull_client.PULL) {
	    pull_client.PULL.subscribe({
	      type: 'BX.PullClient.SubscriptionType.Server',
	      moduleId: 'mobile',
	      command: 'onDeeplinkShouldRefresh',
	      callback: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _createQrCodeImage)[_createQrCodeImage]();
	      }
	    });
	  }
	}

	exports.ShortQrAuth = ShortQrAuth;

}((this.BX.UI = this.BX.UI || {}),BX,BX,BX.UI,BX,BX));
//# sourceMappingURL=short-qr-auth.bundle.js.map
