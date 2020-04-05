this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports, main_core, landing_sliderhacks) {
	'use strict';

	var onEditButtonClick = Symbol('onEditButtonClick');
	var TopPanel =
	/*#__PURE__*/
	function () {
	  function TopPanel() {
	    babelHelpers.classCallCheck(this, TopPanel);
	    this.cache = new main_core.Cache.MemoryCache();
	    this[onEditButtonClick] = this[onEditButtonClick].bind(this);
	    main_core.Event.bind(this.getEditButton(), 'click', this[onEditButtonClick]);
	  }

	  babelHelpers.createClass(TopPanel, [{
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return document.querySelector('.landing-pub-top-panel');
	      });
	    }
	  }, {
	    key: "getEditButton",
	    value: function getEditButton() {
	      var _this = this;

	      return this.cache.remember('editButton', function () {
	        return _this.getLayout().querySelector('.landing-pub-top-panel-edit-button');
	      });
	    }
	  }, {
	    key: onEditButtonClick,
	    value: function value(event) {
	      event.preventDefault();
	      var href = main_core.Dom.attr(event.currentTarget, 'href');

	      if (main_core.Type.isString(href) && href !== '') {
	        this.openSlider(href);
	      }
	    }
	  }, {
	    key: "openSlider",
	    value: function openSlider(url) {
	      BX.SidePanel.Instance.open(url, {
	        cacheable: false,
	        customLeftBoundary: 240,
	        allowChangeHistory: false,
	        events: {
	          onClose: function onClose() {
	            void landing_sliderhacks.SliderHacks.reloadSlider(window.location.toString());
	          }
	        }
	      });
	    }
	  }]);
	  return TopPanel;
	}();

	exports.TopPanel = TopPanel;

}(this.BX.Landing.Pub = this.BX.Landing.Pub || {}, BX, BX.Landing));
//# sourceMappingURL=script.js.map
