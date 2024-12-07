/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var NewEmployeesV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(NewEmployeesV2, _BX$Landing$Widget$Ba);
	  function NewEmployeesV2(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, NewEmployeesV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NewEmployeesV2).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(NewEmployeesV2, [{
	    key: "initialize",
	    value: function initialize(element) {
	      if (!element) {
	        return;
	      }
	      var mainContainer = element.querySelector('.landing-widget-view-main');
	      var sidebarContainer = element.querySelector('.landing-widget-view-sidebar');
	      var widgetOptions = {
	        mainContainer: mainContainer,
	        sidebarContainer: sidebarContainer
	      };
	      this.deleteContextDependentContainer(widgetOptions);
	    }
	  }]);
	  return NewEmployeesV2;
	}(BX.Landing.Widget.Base);

	exports.NewEmployeesV2 = NewEmployeesV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
