/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var Birthdays = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(Birthdays, _BX$Landing$Widget$Ba);
	  function Birthdays(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, Birthdays);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Birthdays).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(Birthdays, [{
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
	  return Birthdays;
	}(BX.Landing.Widget.Base);

	exports.Birthdays = Birthdays;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
