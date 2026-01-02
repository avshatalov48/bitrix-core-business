/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	var EnterpriceWest = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(EnterpriceWest, _BX$Landing$Widget$Ba);
	  function EnterpriceWest(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, EnterpriceWest);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EnterpriceWest).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(EnterpriceWest, [{
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
	  return EnterpriceWest;
	}(BX.Landing.Widget.Base);

	exports.EnterpriceWest = EnterpriceWest;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {}),BX));
//# sourceMappingURL=script.js.map
