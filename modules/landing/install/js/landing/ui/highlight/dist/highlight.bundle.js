/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_pageobject) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _highlightNode = /*#__PURE__*/new WeakSet();
	var _hideNode = /*#__PURE__*/new WeakSet();
	/**
	 * Implements interface for works with highlights
	 * Implements singleton pattern
	 * @memberOf BX.Landing.UI
	 */
	var Highlight = /*#__PURE__*/function () {
	  babelHelpers.createClass(Highlight, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (!Highlight.instance) {
	        Highlight.instance = new Highlight();
	      }
	      return Highlight.instance;
	    }
	    /**
	     * Collection of HighlightItem elements
	     * @type {null}
	     */
	  }, {
	    key: "highlights",
	    get: function get() {
	      if (!Highlight.highlightsStore) {
	        Highlight.highlightsStore = new BX.Landing.Collection.BaseCollection();
	      }
	      return Highlight.highlightsStore;
	    }
	  }]);
	  function Highlight() {
	    babelHelpers.classCallCheck(this, Highlight);
	    _classPrivateMethodInitSpec(this, _hideNode);
	    _classPrivateMethodInitSpec(this, _highlightNode);
	    babelHelpers.defineProperty(this, "isSingle", false);
	    babelHelpers.defineProperty(this, "localStore", null);
	    this.id = main_core.Text.getRandom(8);
	    this.layout = main_core.Dom.create('div');
	    main_core.Dom.addClass(this.layout, 'landing-highlight-border');
	    main_core.Dom.style(this.layout, {
	      position: 'absolute',
	      top: 0,
	      left: 0,
	      right: 0,
	      bottom: 0,
	      'z-index': 9999,
	      opacity: '.4',
	      'pointer-events': 'none',
	      transform: 'translateZ(0)',
	      'background-color': 'rgba(47, 198, 246, .15)'
	    });
	    this.setBorder(Highlight.defaultBorderColor, Highlight.defaultBorderWidth);
	    this.setBackground(Highlight.defaultBackground);
	  }

	  /**
	   * If true - this highlight will not depend on others.
	   * You should full control (show and hide) them! Use carefully
	   * @param flag
	   * @returns {BX.Landing.UI.Highlight}
	   */
	  babelHelpers.createClass(Highlight, [{
	    key: "setSingleMode",
	    value: function setSingleMode() {
	      var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      if (flag) {
	        this.localStore = new BX.Landing.Collection.BaseCollection();
	      }
	      this.isSingle = flag;
	      return this;
	    }
	    /**
	     * Set border color
	     * @param {string} color - HEX string
	     * @param {number} width - border width in px, just number
	     * @returns {BX.Landing.UI.Highlight}
	     */
	  }, {
	    key: "setBorder",
	    value: function setBorder(color) {
	      var width = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : Highlight.defaultBorderWidth;
	      main_core.Dom.style(this.layout, {
	        border: "".concat(width, "px ").concat(color, " dashed")
	      });
	      return this;
	    }
	    /**
	     * @param {string} bg - any valid color string (hex, rgba etc). Remember about opacity!
	     * @returns {BX.Landing.UI.Highlight}
	     */
	  }, {
	    key: "setBackground",
	    value: function setBackground(bg) {
	      main_core.Dom.style(this.layout, {
	        'background-color': bg
	      });
	      return this;
	    }
	    /**
	     * Shows highlight for node
	     * @param {HTMLElement|HTMLElement[]} node
	     * @param {object} [rect]
	     */
	  }, {
	    key: "show",
	    value: function show(node, rect) {
	      var _this = this;
	      this.hide();
	      if (main_core.Type.isArray(node)) {
	        node.forEach(function (element) {
	          _classPrivateMethodGet(_this, _highlightNode, _highlightNode2).call(_this, element);
	        });
	      } else if (main_core.Type.isDomNode(node)) {
	        _classPrivateMethodGet(this, _highlightNode, _highlightNode2).call(this, node, rect);
	      }
	    }
	    /**
	     * @private
	     * @param node
	     * @param {object} rect
	     */
	  }, {
	    key: "hide",
	    /**
	     * Hides highlight for all nodes
	     * @param force - if true - remove highlight immediately, without requestAnimationFrame
	     */
	    value: function hide() {
	      var _this2 = this;
	      var force = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var store = this.isSingle ? this.localStore : Highlight.highlights;
	      store.forEach(function (item) {
	        if (force) {
	          _classPrivateMethodGet(_this2, _hideNode, _hideNode2).call(_this2, item);
	        } else {
	          BX.DOM.write(_classPrivateMethodGet(_this2, _hideNode, _hideNode2).bind(_this2, item));
	        }
	      });
	      store.clear();
	    }
	  }]);
	  return Highlight;
	}();
	function _highlightNode2(node, rect) {
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
	    var editor = landing_pageobject.PageObject.getEditorWindow();
	    if (editor) {
	      BX.DOM.write(function () {
	        main_core.Dom.append(highlight, editor.document.body);
	      });
	    }
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
	  if (this.isSingle) {
	    this.localStore.add({
	      node: node,
	      highlight: highlight
	    });
	  } else {
	    Highlight.highlights.add({
	      node: node,
	      highlight: highlight
	    });
	  }
	}
	function _hideNode2(item) {
	  main_core.Dom.remove(item.highlight);
	  main_core.Dom.style(item.node, {
	    position: '',
	    userSelect: '',
	    cursor: ''
	  });
	}
	babelHelpers.defineProperty(Highlight, "highlightsStore", null);
	babelHelpers.defineProperty(Highlight, "defaultBorderColor", '#2fc6f6');
	babelHelpers.defineProperty(Highlight, "defaultBorderWidth", 2);
	babelHelpers.defineProperty(Highlight, "defaultBackground", 'rgba(47, 198, 246, .15)');

	exports.Highlight = Highlight;

}((this.BX.Landing.UI = this.BX.Landing.UI || {}),BX,BX.Landing));
//# sourceMappingURL=highlight.bundle.js.map
