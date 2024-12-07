/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var LivefeedV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(LivefeedV2, _BX$Landing$Widget$Ba);
	  function LivefeedV2(element, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, LivefeedV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(LivefeedV2).call(this, element));
	    _this.initialize(element, options);
	    return _this;
	  }
	  babelHelpers.createClass(LivefeedV2, [{
	    key: "initialize",
	    value: function initialize(element, options) {
	      if (!element) {
	        return;
	      }
	      var mainContainer = element.querySelector('.landing-widget-view-main');
	      var sidebarContainer = element.querySelector('.landing-widget-view-sidebar');
	      var extendButton = element.querySelector('.landing-widget-button.extend-list-button');
	      var viewAllButton = element.querySelector('.landing-widget-button.view-all-button');
	      var grid = element.querySelector('.landing-widget-content-grid');
	      var widgetOptions = {
	        mainContainer: mainContainer,
	        sidebarContainer: sidebarContainer,
	        isShowExtendButton: options.isShowExtendButton,
	        extendButton: extendButton,
	        viewAllButton: viewAllButton,
	        grid: grid,
	        gridExtendedClass: 'extended',
	        buttonHideClass: 'hide'
	      };
	      this.deleteContextDependentContainer(widgetOptions);
	      this.toggleExtendViewButtonBehavior(widgetOptions);
	    }
	  }]);
	  return LivefeedV2;
	}(BX.Landing.Widget.Base);

	exports.LivefeedV2 = LivefeedV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
