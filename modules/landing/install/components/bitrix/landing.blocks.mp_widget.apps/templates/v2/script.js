/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,ui_qrauthorization,main_core) {
	'use strict';

	var AppsV2 = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(AppsV2, _BX$Landing$Widget$Ba);
	  function AppsV2(element, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, AppsV2);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppsV2).call(this, element));
	    _this.initialize(element, options);
	    return _this;
	  }
	  babelHelpers.createClass(AppsV2, [{
	    key: "initialize",
	    value: function initialize(element) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var mainContainer = element.querySelector('.landing-widget-view-main');
	      var sidebarContainer = element.querySelector('.landing-widget-view-sidebar');
	      var widgetOptions = {
	        mainContainer: mainContainer,
	        sidebarContainer: sidebarContainer
	      };
	      this.deleteContextDependentContainer(widgetOptions);
	      var qrButton = element.querySelector('.landing-widget-qr-button');
	      var qrAuth = new ui_qrauthorization.QrAuthorization(options);
	      if (qrButton && qrAuth) {
	        main_core.Event.bind(qrButton, 'click', function () {
	          var popup = qrAuth.getPopup();
	          if (popup) {
	            popup.show();
	          }
	        });
	      }
	    }
	  }]);
	  return AppsV2;
	}(BX.Landing.Widget.Base);

	exports.AppsV2 = AppsV2;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {}),BX.UI,BX));
//# sourceMappingURL=script.js.map