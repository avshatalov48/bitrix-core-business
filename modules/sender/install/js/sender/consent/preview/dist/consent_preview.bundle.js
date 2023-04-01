this.BX = this.BX || {};
(function (exports,ui_sidepanel_layout,main_core) {
	'use strict';

	var _templateObject;

	var PreviewContent = /*#__PURE__*/function () {
	  function PreviewContent() {
	    babelHelpers.classCallCheck(this, PreviewContent);
	    this.isActive = false;
	    this.text = '';
	    this.approveBtn = '';
	    this.rejectBtn = '';
	    this.activeTab = 'desktop';
	    this.activeClass = 'sender-message-editor--slider-desktop';
	    return this;
	  }

	  babelHelpers.createClass(PreviewContent, [{
	    key: "changeActiveTab",
	    value: function changeActiveTab(activeTab) {
	      this.activeTab = activeTab;
	      this.activeClass = 'sender-message-editor--slider-' + activeTab;
	      this.reDraw();
	    }
	  }, {
	    key: "setActive",
	    value: function setActive(active) {
	      this.isActive = active;
	      this.reDraw();
	    }
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      this.text = text;
	      this.reDraw();
	    }
	  }, {
	    key: "setApproveBtn",
	    value: function setApproveBtn(accept) {
	      this.approveBtn = BX.util.htmlspecialchars(accept);
	      this.reDraw();
	    }
	  }, {
	    key: "setRejectBtn",
	    value: function setRejectBtn(reject) {
	      this.rejectBtn = BX.util.htmlspecialchars(reject);
	      this.reDraw();
	    }
	  }, {
	    key: "getTemplate",
	    value: function getTemplate() {
	      var tabletActive = this.activeTab === 'tablet' ? 'active' : '';
	      var mobileActive = this.activeTab === 'mobile' ? 'active' : '';
	      var desktopActive = this.activeTab === 'desktop' ? 'active' : '';
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sender-js-slider-contents\">\n\t\t\t\t<div class=\"ui-slider-section sender-message-editor--slider-modifier ", "\">\n\t\t\t\t\t<div class=\"sender-ui-panel-top-devices\">\n\t\t\t\t\t\t<div class=\"sender-ui-panel-top-devices-inner\">\n\t\t\t\t\t\t<button \n\t\t\t\t\t\tclass=\"sender-ui-button sender-ui-button-desktop sender-js-slider-modifier ", "\" \n\t\t\t\t\t\tdata-id=\"desktop\"></button>\n\t\t\t\t\t\t<button \n\t\t\t\t\t\tclass=\"sender-ui-button sender-ui-button-tablet sender-js-slider-modifier  ", "\"\n\t\t\t\t\t\tdata-id=\"tablet\"></button>\n\t\t\t\t\t\t<button class=\"sender-ui-button sender-ui-button-mobile sender-js-slider-modifier  ", "\"\n\t\t\t\t\t\tdata-id=\"mobile\"></button>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-slider-content-box\">\n\t\t\t\t\t<div class=\"sender-message-mailing-icon\"></div>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-btn-container ui-btn-container-center\">\n\t\t\t\t\t\t<button class=\"ui-btn ui-btn-success\">", "</button>\n\t\t\t\t\t\t<button class=\"ui-btn ui-btn-light-border\">", "</button>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t"])), this.activeClass, desktopActive, tabletActive, mobileActive, this.text, this.approveBtn, this.rejectBtn);
	    }
	  }, {
	    key: "bindEvent",
	    value: function bindEvent() {
	      var _this = this;

	      var buttons = window.top.document.querySelectorAll('.sender-js-slider-modifier');
	      buttons.forEach(function (element) {
	        var type = element.dataset.id || 'desktop';
	        main_core.Event.bind(element, 'click', _this.changeActiveTab.bind(_this, type));
	      });
	    }
	  }, {
	    key: "reDraw",
	    value: function reDraw() {
	      // BX.SidePanel.Slider.top
	      var content = window.top.document.querySelector('div.sender-js-slider-contents');

	      if (!content) {
	        return;
	      }

	      var parentNode = content.parentNode;
	      parentNode.removeChild(content);
	      parentNode.append(this.getTemplate());
	      this.bindEvent();
	    }
	  }]);
	  return PreviewContent;
	}();

	var ConsentPreview = /*#__PURE__*/function () {
	  function ConsentPreview() {
	    babelHelpers.classCallCheck(this, ConsentPreview);
	  }

	  babelHelpers.createClass(ConsentPreview, null, [{
	    key: "open",
	    value: function open(consentId) {
	      if (!consentId) {
	        return;
	      }

	      var view = new PreviewContent();
	      BX.SidePanel.Instance.open("sender:consent-preview", {
	        width: 800,
	        cacheable: false,
	        contentCallback: function contentCallback() {
	          return ui_sidepanel_layout.Layout.createContent({
	            extensions: ['ui.buttons', 'ui.buttons.icons', 'ui.notification', 'ui.sidepanel-content', 'ui.sidepanel.layout', 'sender.consent.preview'],
	            content: function content() {
	              BX.ajax.runAction('sender.consentPreview.loadData', {
	                json: {
	                  id: consentId
	                }
	              }).then(function (response) {
	                view.setText(response.data.consentBody) || "";
	                view.setApproveBtn(response.data.approveBtnText);
	                view.setRejectBtn(response.data.rejectBtnText);
	              }, function (response) {// view.setText(response.data.consent);
	              });
	              return view.getTemplate();
	            },
	            buttons: function buttons(_ref) {
	              var cancelButton = _ref.cancelButton;
	              return [cancelButton];
	            }
	          });
	        }
	      });
	    }
	  }]);
	  return ConsentPreview;
	}();

	exports.ConsentPreview = ConsentPreview;

}((this.BX.Sender = this.BX.Sender || {}),BX.UI.SidePanel,BX));
//# sourceMappingURL=consent_preview.bundle.js.map
