this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"ui-publication-name\">ERROR! ", "</span>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"ui-publication-name\">SUCCESS!</span>\n\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"ui-publication-name\">Please wait...</span>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-publication\">\n\t\t\t\t<div>Publication dialog</div>\n\t\t\t\t<div>URL: <a href=\"", "\" target=\"_blank\">", "</a></div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Publication = /*#__PURE__*/function () {
	  function Publication(options) {
	    babelHelpers.classCallCheck(this, Publication);
	    babelHelpers.defineProperty(this, "dialog", null);
	    this.siteId = options.siteId;
	    this.landingId = options.landingId;
	    this.url = options.url;
	  }

	  babelHelpers.createClass(Publication, [{
	    key: "publication",
	    value: function publication(mode) {
	      var _this = this;

	      var action = mode === 'site' ? 'Site::publication' : 'Landing::publication';
	      var data = {
	        data: mode === 'site' ? {
	          id: this.siteId
	        } : {
	          lid: this.landingId
	        },
	        actionType: 'rest',
	        sessid: BX.message('bitrix_sessid')
	      };
	      this.renderPopup();
	      BX.ajax({
	        url: main_core.Uri.addParam(window.location.href, {
	          action: action
	        }),
	        data: data,
	        dataType: 'json',
	        method: 'POST',
	        onsuccess: function onsuccess(result) {
	          if (result.type === 'error') {
	            console.log(result.result);

	            _this.renderErrorPopupContent(result.result[0].error_description);
	          } else {
	            _this.renderSuccessPopupContent();
	          }
	        }
	      });
	    }
	  }, {
	    key: "renderPopup",
	    value: function renderPopup() {
	      if (!this.dialog) {
	        this.dialog = new BX.PopupWindow('landing-publication-confirm', null, {
	          content: '',
	          titleBar: {
	            content: 'Publication'
	          },
	          offsetLeft: 0,
	          offsetTop: 0,
	          buttons: [new BX.PopupWindowButton({
	            text: 'OK',
	            events: {
	              click: function click() {
	                this.popupWindow.close();
	              }
	            }
	          })]
	        });
	      }

	      this.renderWaitPopupContent();
	      this.dialog.show();
	    }
	  }, {
	    key: "renderContent",
	    value: function renderContent(status) {
	      this.dialog.setContent(main_core.Tag.render(_templateObject(), this.url, this.url, status));
	    }
	  }, {
	    key: "renderWaitPopupContent",
	    value: function renderWaitPopupContent() {
	      this.renderContent(main_core.Tag.render(_templateObject2()));
	    }
	  }, {
	    key: "renderSuccessPopupContent",
	    value: function renderSuccessPopupContent() {
	      this.renderContent(main_core.Tag.render(_templateObject3()));
	    }
	  }, {
	    key: "renderErrorPopupContent",
	    value: function renderErrorPopupContent(error) {
	      this.renderContent(main_core.Tag.render(_templateObject4(), error));
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance(options) {
	      if (!Publication.instance) {
	        Publication.instance = new Publication(options);
	      }

	      return Publication.instance;
	    }
	  }]);
	  return Publication;
	}();
	babelHelpers.defineProperty(Publication, "instance", null);

	exports.Publication = Publication;

}((this.BX.Landing.Dialog = this.BX.Landing.Dialog || {}),BX));
//# sourceMappingURL=publication.bundle.js.map
