/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,im_public) {
	'use strict';

	var VibeCollaborationV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(VibeCollaborationV2, _BX$Landing$Widget$Ba);
	  function VibeCollaborationV2(element, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, VibeCollaborationV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VibeCollaborationV2).call(this, element));
	    _this.options = options;
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(VibeCollaborationV2, [{
	    key: "initialize",
	    value: function initialize(element) {
	      var _this2 = this;
	      var btn1 = element.querySelector('.button-1');
	      var btn2 = element.querySelector('.button-2');
	      var btn3 = element.querySelector('.button-3');
	      if (btn1) {
	        btn1.addEventListener('click', function (event) {
	          event.preventDefault();
	          BX.SidePanel.Instance.open(_this2.options.inviteDialogLink);
	        });
	      }
	      if (btn2) {
	        btn2.addEventListener('click', function (event) {
	          event.preventDefault();
	          new BX.UI.FeaturePromoter({
	            code: 'limit_demo'
	          }).show();
	        });
	      }
	      if (btn3) {
	        btn3.addEventListener('click', function (event) {
	          event.preventDefault();
	          im_public.Messenger.openChat(_this2.options.generalChatId);
	        });
	      }
	    }
	  }]);
	  return VibeCollaborationV2;
	}(BX.Landing.Widget.Base);

	exports.VibeCollaborationV2 = VibeCollaborationV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {}),BX.Messenger.v2.Lib));
//# sourceMappingURL=script.js.map
