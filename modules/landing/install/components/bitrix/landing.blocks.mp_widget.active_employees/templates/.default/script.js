/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var ActiveEmployees = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(ActiveEmployees, _BX$Landing$Widget$Ba);
	  function ActiveEmployees(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, ActiveEmployees);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActiveEmployees).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(ActiveEmployees, [{
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
	  return ActiveEmployees;
	}(BX.Landing.Widget.Base);

	exports.ActiveEmployees = ActiveEmployees;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
