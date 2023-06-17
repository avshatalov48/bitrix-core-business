this.BX = this.BX || {};
(function (exports,main_core,main_popup,main_loader,pull_client) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	var QrAuthorization = /*#__PURE__*/function () {
	  function QrAuthorization() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, QrAuthorization);
	    this.title = options.title || null;
	    this.content = options.content || null;
	    this.bottomText = options.bottomText || main_core.Loc.getMessage('UI_QR_AUTHORIZE_TAKE_CODE');
	    this.helpLink = options.helpLink || null;
	    this.qr = options.qr || null;
	    this.popupParam = options.popupParam || null;
	    this.popup = null;
	    this.loader = null;
	    this.qrNode = null;
	    this.successNode = null;
	    this.loadingNode = null;
	    this.isSubscribe = false;
	  }

	  babelHelpers.createClass(QrAuthorization, [{
	    key: "createQrCodeImage",
	    value: function createQrCodeImage() {
	      var _this = this;

	      main_core.Dom.clean(this.getQrNode());

	      if (main_core.Type.isString(this.qr)) {
	        this.clean();
	        new QRCode(this.getQrNode(), {
	          text: this.qr,
	          width: 180,
	          height: 180
	        });
	        return;
	      }

	      this.loading();
	      main_core.ajax.runAction('mobile.deeplink.get', {
	        data: {
	          intent: 'calendar'
	        }
	      }).then(function (response) {
	        var _response$data;

	        var link = (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.link;

	        if (link) {
	          _this.clean();

	          new QRCode(_this.getQrNode(), {
	            text: link,
	            width: 180,
	            height: 180
	          });

	          if (!_this.isSubscribe) {
	            _this.isSubscribe = true;

	            _this.subscribe();
	          }
	        }
	      })["catch"](function () {});
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe() {
	      var _this2 = this;

	      if (pull_client.PULL) {
	        pull_client.PULL.subscribe({
	          type: 'BX.PullClient.SubscriptionType.Server',
	          moduleId: 'mobile',
	          command: 'onDeeplinkShouldRefresh',
	          callback: function callback(params) {
	            _this2.success();
	          }
	        });
	      }
	    }
	  }, {
	    key: "getQrNode",
	    value: function getQrNode() {
	      if (!this.qrNode) {
	        this.qrNode = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-authorization__popup-qr\"></div>\n\t\t\t"])));
	      }

	      return this.qrNode;
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this3 = this;

	      if (!this.popup) {
	        var _this$title, _this$title2, _this$bottomText, _this$bottomText2, _this$popupParam, _this$popupParam2, _this$popupParam3, _this$popupParam4, _this$popupParam5, _this$popupParam6, _this$popupParam7, _this$popupParam8, _this$popupParam9, _this$popupParam10;

	        var title = main_core.Type.isObject(this.title) ? (_this$title = this.title) === null || _this$title === void 0 ? void 0 : _this$title.text : this.title;
	        var titleSize = main_core.Type.isObject(this.title) ? (_this$title2 = this.title) === null || _this$title2 === void 0 ? void 0 : _this$title2.size : '';
	        var bottomText = main_core.Type.isObject(this.bottomText) ? (_this$bottomText = this.bottomText) === null || _this$bottomText === void 0 ? void 0 : _this$bottomText.text : this.bottomText;
	        var bottomTextSize = main_core.Type.isObject(this.bottomText) ? (_this$bottomText2 = this.bottomText) === null || _this$bottomText2 === void 0 ? void 0 : _this$bottomText2.size : '';
	        var container = "\n\t\t\t\t<div class=\"ui-qr-authorization__popup-wrapper\">\n\t\t\t\t\t<div class=\"ui-qr-authorization__popup-top ".concat(!this.content ? '--direction-column' : '', "\">\n\t\t\t\t\t\t<div class=\"ui-qr-authorization__popup-left ").concat(!title ? '--flex' : '', "\"\">\n\t\t\t\t\t\t\t").concat(title ? "<div class=\"ui-qr-authorization__popup-title --".concat(titleSize, "\">").concat(title, "</div>") : '', "\n\t\t\t\t\t\t\t").concat(this.content ? "<div class=\"ui-qr-authorization__popup-text\">".concat(this.content, "</div>") : '', "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-qr-authorization__popup-right ").concat(!this.title ? '--no-margin' : '', "\" data-role=\"ui-qr-authorization__qr-node\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-qr-authorization__popup-bottom\">\n\t\t\t\t\t\t<div class=\"ui-qr-authorization__popup-bottom--title ").concat(bottomTextSize ? '--' + bottomTextSize : '', "\">").concat(bottomText, "</div>\n\t\t\t\t\t\t").concat(this.helpLink ? "<a href=\"".concat(this.helpLink, "\" class=\"ui-qr-authorization__popup-bottom--link\">").concat(main_core.Loc.getMessage('UI_QR_AUTHORIZE_HELP'), "</a>") : '', "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t");
	        var popupWidth = this.content ? 710 : 405;
	        var popupParam = {
	          className: (_this$popupParam = this.popupParam) !== null && _this$popupParam !== void 0 && _this$popupParam.className ? (_this$popupParam2 = this.popupParam) === null || _this$popupParam2 === void 0 ? void 0 : _this$popupParam2.className : 'ui-qr-authorization__popup ui-qr-authorization__popup-scope',
	          width: (_this$popupParam3 = this.popupParam) !== null && _this$popupParam3 !== void 0 && _this$popupParam3.width ? (_this$popupParam4 = this.popupParam) === null || _this$popupParam4 === void 0 ? void 0 : _this$popupParam4.width : popupWidth,
	          content: container,
	          closeByEsc: (_this$popupParam5 = this.popupParam) !== null && _this$popupParam5 !== void 0 && _this$popupParam5.closeByEsc ? (_this$popupParam6 = this.popupParam) === null || _this$popupParam6 === void 0 ? void 0 : _this$popupParam6.className : true,
	          overlay: (_this$popupParam7 = this.popupParam) !== null && _this$popupParam7 !== void 0 && _this$popupParam7.overlay ? (_this$popupParam8 = this.popupParam) === null || _this$popupParam8 === void 0 ? void 0 : _this$popupParam8.overlay : false,
	          autoHide: (_this$popupParam9 = this.popupParam) !== null && _this$popupParam9 !== void 0 && _this$popupParam9.autoHide ? (_this$popupParam10 = this.popupParam) === null || _this$popupParam10 === void 0 ? void 0 : _this$popupParam10.autoHide : true,
	          closeIcon: {
	            top: '14px',
	            right: '15px'
	          },
	          events: {
	            onPopupShow: function onPopupShow() {
	              _this3.createQrCodeImage();

	              var qrTarget = _this3.getPopup().getContentContainer().querySelector('[data-role="ui-qr-authorization__qr-node"]');

	              if (qrTarget) {
	                qrTarget.appendChild(_this3.getQrNode());
	              }
	            }
	          },
	          padding: 0,
	          animation: 'fading-slide'
	        };
	        this.popup = new main_popup.Popup(popupParam);
	      }

	      return this.popup;
	    }
	  }, {
	    key: "success",
	    value: function success() {
	      this.clean();
	      this.getQrNode().classList.add('--success');
	      this.getQrNode().appendChild(this.getSuccessNode());
	    }
	  }, {
	    key: "getSuccessNode",
	    value: function getSuccessNode() {
	      if (!this.successNode) {
	        this.successNode = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-authorization__popup-qr-success\"></div>\n\t\t\t"])));
	      }

	      return this.successNode;
	    }
	  }, {
	    key: "loading",
	    value: function loading() {
	      this.clean();
	      this.getQrNode().classList.add('--loading');
	      this.getQrNode().appendChild(this.getLoadingNode());
	      this.showLoader();
	    }
	  }, {
	    key: "getLoadingNode",
	    value: function getLoadingNode() {
	      if (!this.loadingNode) {
	        this.loadingNode = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-authorization__popup-qr-loading\"></div>\n\t\t\t"])));
	      }

	      return this.loadingNode;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          target: this.getLoadingNode(),
	          size: 150
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      void this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      void this.getLoader().hide();
	    }
	  }, {
	    key: "clean",
	    value: function clean() {
	      this.getQrNode().classList.remove('--loading');
	      this.getQrNode().classList.remove('--success');
	      main_core.Dom.remove(this.getLoadingNode());
	      main_core.Dom.remove(this.getSuccessNode());
	      this.hideLoader();
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.getPopup().isShown()) {
	        this.loading();
	        this.getPopup().show();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.getPopup().isShown()) {
	        this.clean();
	        this.getPopup().close();
	      }
	    }
	  }]);
	  return QrAuthorization;
	}();

	exports.QrAuthorization = QrAuthorization;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Main,BX,BX));
//# sourceMappingURL=bundle.js.map
