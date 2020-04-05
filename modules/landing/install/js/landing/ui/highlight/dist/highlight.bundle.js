this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports, main_core, landing_pageobject) {
	'use strict';

	/**
	 * Implements interface for works with highlights
	 * Implements singleton pattern
	 * @memberOf BX.Landing.UI
	 */

	var Highlight =
	/*#__PURE__*/
	function () {
	  function Highlight() {
	    babelHelpers.classCallCheck(this, Highlight);
	    this.layout = main_core.Dom.create('div');
	    main_core.Dom.style(this.layout, {
	      position: 'absolute',
	      border: '2px #fe541e dashed',
	      top: 0,
	      left: 0,
	      right: 0,
	      bottom: 0,
	      'z-index': 9999,
	      opacity: '.4',
	      'pointer-events': 'none',
	      transform: 'translateZ(0)'
	    });
	  }

	  babelHelpers.createClass(Highlight, [{
	    key: "show",

	    /**
	     * Shows highlight for node
	     * @param {HTMLElement|HTMLElement[]} node
	     * @param {object} [rect]
	     */
	    value: function show(node, rect) {
	      var _this = this;

	      this.hide();

	      if (main_core.Type.isArray(node)) {
	        node.forEach(function (element) {
	          _this.highlightNode(element);
	        });
	      } else if (main_core.Type.isDomNode(node)) {
	        this.highlightNode(node, rect);
	      }
	    }
	    /**
	     * Hides highlight for all nodes
	     */
	    // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "hide",
	    value: function hide() {
	      Highlight.highlights.forEach(function (item) {
	        BX.DOM.write(function () {
	          main_core.Dom.remove(item.highlight);
	          item.node.style.position = '';
	          item.node.style.userSelect = '';
	          item.node.style.cursor = '';
	        });
	      });
	      Highlight.highlights.clear();
	    }
	    /**
	     * @private
	     * @param node
	     * @param {object} rect
	     */

	  }, {
	    key: "highlightNode",
	    value: function highlightNode(node, rect) {
	      var highlight = main_core.Runtime.clone(this.layout);

	      if (rect) {
	        BX.DOM.write(function () {
	          main_core.Dom.style(highlight, {
	            position: 'fixed',
	            width: "".concat(rect.width, "px"),
	            height: "".concat(rect.height, "px"),
	            top: "".concat(rect.top, "px"),
	            left: "".concat(rect.left, "px"),
	            right: "".concat(rect.right, "px"),
	            bottom: "".concat(rect.bottom, "px")
	          });
	        });
	        landing_pageobject.PageObject.getInstance().view().then(function (frame) {
	          BX.DOM.write(function () {
	            main_core.Dom.append(highlight, frame.contentDocument.body);
	          });
	        });
	      } else {
	        BX.DOM.write(function () {
	          main_core.Dom.append(highlight, node);
	        });
	      }

	      BX.DOM.write(function () {
	        main_core.Dom.style(node, {
	          position: 'relative',
	          userSelect: 'none',
	          cursor: 'pointer'
	        });
	      });
	      Highlight.highlights.add({
	        node: node,
	        highlight: highlight
	      });
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (!Highlight.instance) {
	        Highlight.instance = new Highlight();
	      }

	      return Highlight.instance;
	    }
	  }, {
	    key: "highlights",
	    get: function get() {
	      if (!Highlight.highlightsStore) {
	        Highlight.highlightsStore = new BX.Landing.Collection.BaseCollection();
	      }

	      return Highlight.highlightsStore;
	    }
	  }]);
	  return Highlight;
	}();
	babelHelpers.defineProperty(Highlight, "highlightsStore", null);

	exports.Highlight = Highlight;

}(this.BX.Landing.UI = this.BX.Landing.UI || {}, BX, BX.Landing));
//# sourceMappingURL=highlight.bundle.js.map
