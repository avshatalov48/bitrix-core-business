this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_sliderhacks) {
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

	var SearchResult =
	/*#__PURE__*/
	function () {
	  /**
	   * Constructor.
	   */
	  function SearchResult() {
	    babelHelpers.classCallCheck(this, SearchResult);
	    this.scrollToFirstBlock();
	  }
	  /**
	   * Finds first highlight word and scroll to it.
	   * @return {void}
	   */


	  babelHelpers.createClass(SearchResult, [{
	    key: "scrollToFirstBlock",
	    value: function scrollToFirstBlock() {
	      var result = document.querySelector('.landing-highlight');

	      if (result) {
	        var parent = result.parentNode;

	        while (parent) {
	          if (parent.classList.contains('block-wrapper')) {
	            window.scrollTo({
	              top: parent.offsetTop,
	              behavior: 'smooth'
	            });
	            break;
	          }

	          parent = parent.parentNode;
	        }
	      }
	    }
	  }]);
	  return SearchResult;
	}();

	exports.TopPanel = TopPanel;
	exports.SearchResult = SearchResult;

}((this.BX.Landing.Pub = this.BX.Landing.Pub || {}),BX,BX.Landing));
//# sourceMappingURL=script.js.map
