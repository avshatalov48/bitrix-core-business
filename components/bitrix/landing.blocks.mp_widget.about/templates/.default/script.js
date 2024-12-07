/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var About = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(About, _BX$Landing$Widget$Ba);
	  function About(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, About);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(About).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(About, [{
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
	  return About;
	}(BX.Landing.Widget.Base);

	exports.About = About;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
