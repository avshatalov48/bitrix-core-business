/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var BirthdaysV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(BirthdaysV2, _BX$Landing$Widget$Ba);
	  function BirthdaysV2(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, BirthdaysV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BirthdaysV2).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(BirthdaysV2, [{
	    key: "initialize",
	    value: function initialize(element) {
	      var mainContainer = element.querySelector('.landing-widget-view-main');
	      var sidebarContainer = element.querySelector('.landing-widget-view-sidebar');
	      var widgetOptions = {
	        mainContainer: mainContainer,
	        sidebarContainer: sidebarContainer
	      };
	      this.deleteContextDependentContainer(widgetOptions);
	    }
	  }]);
	  return BirthdaysV2;
	}(BX.Landing.Widget.Base);

	exports.BirthdaysV2 = BirthdaysV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
