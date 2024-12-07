/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var ActiveEmployeesV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(ActiveEmployeesV2, _BX$Landing$Widget$Ba);
	  function ActiveEmployeesV2(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, ActiveEmployeesV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActiveEmployeesV2).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(ActiveEmployeesV2, [{
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
	  return ActiveEmployeesV2;
	}(BX.Landing.Widget.Base);

	exports.ActiveEmployeesV2 = ActiveEmployeesV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
