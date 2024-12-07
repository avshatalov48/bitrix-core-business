/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
	'use strict';

	var Base = /*#__PURE__*/function () {
	  function Base(widgetElement) {
	    babelHelpers.classCallCheck(this, Base);
	    this.element = widgetElement;
	  }
	  babelHelpers.createClass(Base, [{
	    key: "deleteContextDependentContainer",
	    value: function deleteContextDependentContainer(options) {
	      var _options$mainContaine,
	        _options$sidebarConta,
	        _this = this;
	      if (!options) {
	        return;
	      }
	      this.mainContainer = (_options$mainContaine = options.mainContainer) !== null && _options$mainContaine !== void 0 ? _options$mainContaine : null;
	      this.sidebarContainer = (_options$sidebarConta = options.sidebarContainer) !== null && _options$sidebarConta !== void 0 ? _options$sidebarConta : null;
	      var sidebarElements = document.querySelectorAll('.landing-sidebar');
	      var isInsideSidebar = false;
	      sidebarElements.forEach(function (sidebarElement) {
	        if (sidebarElement.contains(_this.element)) {
	          isInsideSidebar = true;
	        }
	      });
	      if (isInsideSidebar && this.mainContainer) {
	        this.mainContainer.remove();
	      }
	      if (!isInsideSidebar && this.sidebarContainer) {
	        this.sidebarContainer.remove();
	      }
	    }
	  }, {
	    key: "toggleExtendViewButtonBehavior",
	    value: function toggleExtendViewButtonBehavior(options) {
	      var _options$extendButton,
	        _options$viewAllButto,
	        _options$isShowExtend,
	        _options$grid,
	        _options$gridExtended,
	        _options$buttonHideCl,
	        _this2 = this;
	      if (!options) {
	        return;
	      }
	      this.extendButton = (_options$extendButton = options.extendButton) !== null && _options$extendButton !== void 0 ? _options$extendButton : null;
	      this.viewAllButton = (_options$viewAllButto = options.viewAllButton) !== null && _options$viewAllButto !== void 0 ? _options$viewAllButto : null;
	      this.isShowExtendButton = (_options$isShowExtend = options.isShowExtendButton) !== null && _options$isShowExtend !== void 0 ? _options$isShowExtend : null;
	      this.grid = (_options$grid = options.grid) !== null && _options$grid !== void 0 ? _options$grid : null;
	      this.gridExtendedClass = (_options$gridExtended = options.gridExtendedClass) !== null && _options$gridExtended !== void 0 ? _options$gridExtended : null;
	      this.buttonHideClass = (_options$buttonHideCl = options.buttonHideClass) !== null && _options$buttonHideCl !== void 0 ? _options$buttonHideCl : null;
	      if (this.extendButton && this.viewAllButton) {
	        if (this.isShowExtendButton) {
	          main_core.Event.bind(this.extendButton, 'click', function () {
	            if (_this2.grid) {
	              main_core.Dom.addClass(_this2.grid, _this2.gridExtendedClass);
	              setTimeout(function () {
	                main_core.Dom.addClass(_this2.extendButton, _this2.buttonHideClass);
	                main_core.Dom.removeClass(_this2.viewAllButton, _this2.buttonHideClass);
	              }, 300);
	            }
	          });
	        } else {
	          main_core.Dom.addClass(this.extendButton, this.buttonHideClass);
	          main_core.Dom.removeClass(this.viewAllButton, this.buttonHideClass);
	        }
	      }
	    }
	  }]);
	  return Base;
	}();

	exports.Base = Base;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {}),BX));
//# sourceMappingURL=script.js.map
