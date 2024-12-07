/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var AboutV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(AboutV2, _BX$Landing$Widget$Ba);
	  function AboutV2(element) {
	    var _this;
	    babelHelpers.classCallCheck(this, AboutV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AboutV2).call(this, element));
	    _this.initialize(element);
	    return _this;
	  }
	  babelHelpers.createClass(AboutV2, [{
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
	  return AboutV2;
	}(BX.Landing.Widget.Base);

	exports.AboutV2 = AboutV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
