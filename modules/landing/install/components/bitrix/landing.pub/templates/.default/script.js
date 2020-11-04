this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_sliderhacks) {
	'use strict';

	var onEditButtonClick = Symbol('onEditButtonClick');
	var onBackButtonClick = Symbol('onBackButtonClick');
	var onForwardButtonClick = Symbol('onForwardButtonClick');
	var TopPanel = /*#__PURE__*/function () {
	  function TopPanel() {
	    babelHelpers.classCallCheck(this, TopPanel);
	    main_core.Event.bind(TopPanel.getEditButton(), 'click', this[onEditButtonClick]);
	    main_core.Event.bind(TopPanel.getBackButton(), 'click', this[onBackButtonClick]);
	    main_core.Event.bind(TopPanel.getForwardButton(), 'click', this[onForwardButtonClick]);
	    TopPanel.pushHistory(window.location.toString());
	    TopPanel.checkNavButtonsActivity();
	  }

	  babelHelpers.createClass(TopPanel, [{
	    key: onEditButtonClick,
	    value: function value(event) {
	      event.preventDefault();
	      var href = main_core.Dom.attr(event.currentTarget, 'href');

	      if (main_core.Type.isString(href) && href !== '') {
	        TopPanel.openSlider(href);
	      }
	    }
	  }, {
	    key: onBackButtonClick,
	    value: function value(event) {
	      event.preventDefault();

	      if (main_core.Type.isArrayFilled(TopPanel.history) && main_core.Type.isNumber(TopPanel.historyState) && TopPanel.historyState > 0) {
	        void landing_sliderhacks.SliderHacks.reloadSlider(TopPanel.history[--TopPanel.historyState]);
	        TopPanel.checkNavButtonsActivity();
	      }
	    }
	  }, {
	    key: onForwardButtonClick,
	    value: function value(event) {
	      event.preventDefault();

	      if (main_core.Type.isArrayFilled(TopPanel.history) && main_core.Type.isNumber(TopPanel.historyState) && TopPanel.historyState < TopPanel.history.length - 1) {
	        void landing_sliderhacks.SliderHacks.reloadSlider(TopPanel.history[++TopPanel.historyState]);
	        TopPanel.checkNavButtonsActivity();
	      }
	    }
	  }], [{
	    key: "getLayout",
	    value: function getLayout() {
	      return TopPanel.cache.remember('layout', function () {
	        return document.querySelector('.landing-pub-top-panel');
	      });
	    }
	  }, {
	    key: "getEditButton",
	    value: function getEditButton() {
	      return TopPanel.cache.remember('editButton', function () {
	        return TopPanel.getLayout().querySelector('.landing-pub-top-panel-edit-button');
	      });
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
	    } // HISTORY save

	  }, {
	    key: "pushHistory",
	    value: function pushHistory(url) {
	      if (!main_core.Type.isNumber(TopPanel.historyState)) {
	        TopPanel.historyState = -1; // will increase later
	      }

	      if (TopPanel.historyState < TopPanel.history.length - 1) {
	        TopPanel.history.splice(TopPanel.historyState + 1);
	      }

	      TopPanel.history.push(url);
	      TopPanel.historyState++;
	    }
	  }, {
	    key: "checkNavButtonsActivity",
	    value: function checkNavButtonsActivity() {
	      main_core.Dom.removeClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
	      main_core.Dom.removeClass(TopPanel.getBackButton(), 'ui-btn-disabled');

	      if (!main_core.Type.isArrayFilled(TopPanel.history) || !main_core.Type.isNumber(TopPanel.historyState) || TopPanel.history.length === 1) {
	        main_core.Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
	        main_core.Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
	        return;
	      }

	      if (TopPanel.historyState === 0) {
	        main_core.Dom.addClass(TopPanel.getBackButton(), 'ui-btn-disabled');
	      }

	      if (TopPanel.historyState >= TopPanel.history.length - 1) {
	        main_core.Dom.addClass(TopPanel.getForwardButton(), 'ui-btn-disabled');
	      }
	    }
	  }, {
	    key: "getBackButton",
	    value: function getBackButton() {
	      return TopPanel.cache.remember('backButton', function () {
	        var layout = TopPanel.getLayout();
	        return layout ? layout.querySelector('.landing-pub-top-panel-back') : null;
	      });
	    }
	  }, {
	    key: "getForwardButton",
	    value: function getForwardButton() {
	      return TopPanel.cache.remember('forwardButton', function () {
	        var layout = TopPanel.getLayout();
	        return layout ? layout.querySelector('.landing-pub-top-panel-forward') : null;
	      });
	    }
	  }]);
	  return TopPanel;
	}();
	babelHelpers.defineProperty(TopPanel, "cache", new main_core.Cache.MemoryCache());
	babelHelpers.defineProperty(TopPanel, "history", []);

	var SearchResult = /*#__PURE__*/function () {
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
