/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,ui_feedback_form) {
	'use strict';

	var VibeAuto = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(VibeAuto, _BX$Landing$Widget$Ba);
	  function VibeAuto(element, option) {
	    var _this;
	    babelHelpers.classCallCheck(this, VibeAuto);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VibeAuto).call(this, element));
	    _this.initialize(element, option);
	    return _this;
	  }
	  babelHelpers.createClass(VibeAuto, [{
	    key: "initialize",
	    value: function initialize(element, option) {
	      var _this2 = this;
	      if (element && option) {
	        var feedbackButtonElement = element.querySelector('#feedback-button');
	        if (feedbackButtonElement) {
	          BX.Event.bind(feedbackButtonElement, 'click', function () {
	            return _this2.openForm(option);
	          });
	        }
	      }
	    }
	  }, {
	    key: "openForm",
	    value: function openForm(params) {
	      ui_feedback_form.Form.open({
	        id: params.id,
	        portalUri: params.portal,
	        forms: params.forms,
	        presets: params.presets
	      });
	    }
	  }]);
	  return VibeAuto;
	}(BX.Landing.Widget.Base);

	exports.VibeAuto = VibeAuto;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {}),BX.UI.Feedback));
//# sourceMappingURL=script.js.map
