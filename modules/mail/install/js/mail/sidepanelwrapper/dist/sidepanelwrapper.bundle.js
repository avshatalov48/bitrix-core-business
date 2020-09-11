this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"mail-slider-wrapper-content\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper-content\"></div>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-md ui-btn-link\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper-footer-fixed\"></div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"mail-slider-wrapper-header-title\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper-header\"></div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var SidePanelWrapper = /*#__PURE__*/function () {
	  function SidePanelWrapper() {
	    babelHelpers.classCallCheck(this, SidePanelWrapper);
	  }

	  babelHelpers.createClass(SidePanelWrapper, null, [{
	    key: "open",
	    value: function open() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        id: '',
	        content: '',
	        titleText: '',
	        footerIsActive: false,
	        cancelButton: {},
	        consentButton: {
	          function: function _function() {}
	        }
	      };
	      var wrapper = main_core.Tag.render(_templateObject());
	      var header = main_core.Tag.render(_templateObject2());
	      var title = main_core.Tag.render(_templateObject3(), config['titleText']);
	      var footer = main_core.Tag.render(_templateObject4());

	      if (config['footerIsActive']) {
	        footer = main_core.Tag.render(_templateObject5());

	        if (config['consentButton'] !== undefined) {
	          var consentButton = new BX.UI.Button({
	            text: config['consentButton']['text'],
	            color: BX.UI.Button.Color.SUCCESS,
	            events: {},
	            onclick: function onclick() {
	              config['consentButton']['function'](consentButton);
	            }
	          });
	          footer.append(consentButton.getContainer());
	        }

	        if (config['cancelButton'] !== undefined) {
	          var cancelButton = main_core.Tag.render(_templateObject6(), config['cancelButton']['text']);

	          cancelButton.onclick = function () {
	            cancelButton.onclick = function () {};

	            BX.SidePanel.Instance.close();
	          };

	          footer.append(cancelButton);
	        }
	      }

	      var content = main_core.Tag.render(_templateObject7());

	      if (typeof config['content'] === "string") {
	        content = main_core.Tag.render(_templateObject8(), config['content']);
	      } else {
	        content.append(config['content']);
	      }

	      header.append(title);
	      wrapper.append(header);
	      wrapper.append(content);
	      wrapper.append(footer);
	      BX.SidePanel.Instance.open("mail:side-panel", {
	        id: config['id'],
	        contentCallback: function contentCallback() {
	          return new Promise(function (resolve) {
	            return resolve(wrapper);
	          });
	        },
	        width: 735,
	        cacheable: false
	      });
	    }
	  }]);
	  return SidePanelWrapper;
	}();

	exports.SidePanelWrapper = SidePanelWrapper;

}((this.BX.Mail = this.BX.Mail || {}),BX));
//# sourceMappingURL=sidepanelwrapper.bundle.js.map
