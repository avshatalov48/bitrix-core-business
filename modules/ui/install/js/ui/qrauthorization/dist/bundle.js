this.BX = this.BX || {};
(function (exports,main_core,main_popup,main_loader,pull_client) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	var QrAuthorization = /*#__PURE__*/function () {
	  function QrAuthorization() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, QrAuthorization);
	    this.title = options.title || null;
	    this.content = options.content || null;
	    this.helpLink = options.helpLink || null;
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
	      if (!this.popup) {
	        var container = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-authorization__popup-wrapper\">\n\t\t\t\t\t<div class=\"ui-qr-authorization__popup-top\">\n\t\t\t\t\t\t<div class=\"ui-qr-authorization__popup-left ", "\"\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-qr-authorization__popup-right ", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-qr-authorization__popup-bottom\">\n\t\t\t\t\t\t<div class=\"ui-qr-authorization__popup-bottom--title\">", "</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), !this.title ? '--flex' : '', this.title ? '<div class="ui-qr-authorization__popup-title">' + this.title + '</div>' : '', this.content ? '<div class="ui-qr-authorization__popup-text">' + this.content + '</div>' : '', !this.title ? '--no-margin' : '', this.getQrNode(), main_core.Loc.getMessage('UI_QR_AUTHORIZE_TAKE_CODE'), this.helpLink ? '<a href="' + this.helpLink + '" class="ui-qr-authorization__popup-bottom--link">' + main_core.Loc.getMessage('UI_QR_AUTHORIZE_HELP') + '</a>' : '');
	        this.popup = new main_popup.Popup({
	          className: 'ui-qr-authorization__popup ui-qr-authorization__popup-scope',
	          width: this.title && this.content ? 710 : null,
	          content: container,
	          closeByEsc: true,
	          closeIcon: {
	            top: 14,
	            right: 15
	          },
	          padding: 0,
	          animation: 'fading-slide'
	        });
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
	        this.successNode = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-authorization__popup-qr-success\"></div>\n\t\t\t"])));
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
	        this.loadingNode = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-qr-authorization__popup-qr-loading\"></div>\n\t\t\t"])));
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
	        this.createQrCodeImage();
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
