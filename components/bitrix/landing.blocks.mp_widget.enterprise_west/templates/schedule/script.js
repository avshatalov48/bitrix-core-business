/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	var EnterpriceWestV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(EnterpriceWestV2, _BX$Landing$Widget$Ba);
	  function EnterpriceWestV2(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, EnterpriceWestV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EnterpriceWestV2).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(EnterpriceWestV2, [{
	    key: "initialize",
	    value: function initialize(element) {
	      if (element) {
	        var trialButtonElement = element.querySelector('#trialButton');
	        if (trialButtonElement) {
	          BX.Event.bind(trialButtonElement, 'click', this.trialButtonClick);
	        }
	      }
	    }
	  }, {
	    key: "trialButtonClick",
	    value: function trialButtonClick() {
	      main_core.ajax.runAction('bitrix24.license.demolicense.activateExtended').then(function () {
	        window.location.reload();
	      })["catch"](function (err) {
	        console.error(err);
	      });
	    }
	  }]);
	  return EnterpriceWestV2;
	}(BX.Landing.Widget.Base);

	exports.EnterpriceWestV2 = EnterpriceWestV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {}),BX));
//# sourceMappingURL=script.js.map
