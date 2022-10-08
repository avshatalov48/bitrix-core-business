this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8;
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
	          "function": function _function() {}
	        }
	      };
	      var wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper\"></div>"])));
	      var header = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper-header\"></div>"])));
	      var title = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"mail-slider-wrapper-header-title\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), config['titleText']);
	      var footer = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div></div>"])));

	      if (config['footerIsActive']) {
	        footer = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper-footer-fixed\"></div>"])));

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
	          var cancelButton = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-md ui-btn-link\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"])), config['cancelButton']['text']);

	          cancelButton.onclick = function () {
	            cancelButton.onclick = function () {};

	            BX.SidePanel.Instance.close();
	          };

	          footer.append(cancelButton);
	        }
	      }

	      var content = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-slider-wrapper-content\"></div>"])));

	      if (typeof config['content'] === "string") {
	        content = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"mail-slider-wrapper-content\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), config['content']);
	      } else {
	        content.append(config['content']);
	      }

	      header.append(title);
	      wrapper.append(header);
	      wrapper.append(content);
	      wrapper.append(footer);
	      BX.SidePanel.Instance.open(config['id'], {
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
