/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var NewEmployees = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(NewEmployees, _BX$Landing$Widget$Ba);
	  function NewEmployees(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, NewEmployees);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NewEmployees).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(NewEmployees, [{
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
	  return NewEmployees;
	}(BX.Landing.Widget.Base);

	exports.NewEmployees = NewEmployees;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
