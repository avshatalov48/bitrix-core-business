this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _getContainer = /*#__PURE__*/new WeakSet();

	var Loader = /*#__PURE__*/function () {
	  function Loader(options) {
	    babelHelpers.classCallCheck(this, Loader);

	    _classPrivateMethodInitSpec(this, _getContainer);

	    this.target = main_core.Type.isDomNode(options.target) ? options.target : null;
	    this.type = main_core.Type.isString(options.type) ? options.type : null;
	    this.size = main_core.Type.isString(options.size) ? options.size : null;
	    this.color = options.color ? options.color : null;
	    this.layout = {
	      container: null,
	      bulletContainer: null
	    };
	  }

	  babelHelpers.createClass(Loader, [{
	    key: "bulletLoader",
	    value: function bulletLoader() {
	      var color = this.color ? "background: ".concat(this.color, ";") : '';

	      if (!this.layout.bulletContainer) {
	        this.layout.bulletContainer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-loader__bullet\">\n\t\t\t\t\t<div style=\"", "\" class=\"ui-loader__bullet_item\"></div>\n\t\t\t\t\t<div style=\"", "\" class=\"ui-loader__bullet_item\"></div>\n\t\t\t\t\t<div style=\"", "\" class=\"ui-loader__bullet_item\"></div>\n\t\t\t\t\t<div style=\"", "\" class=\"ui-loader__bullet_item\"></div>\n\t\t\t\t\t<div style=\"", "\" class=\"ui-loader__bullet_item\"></div>\n\t\t\t\t</div>\n\t\t\t"])), color, color, color, color, color);
	      }

	      this.layout.container = document.querySelector('.ui-loader__bullet');
	      return this.layout.bulletContainer;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.layout.container.style.display = 'block';
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.layout.container.style.display = '';
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (!main_core.Type.isDomNode(this.target)) {
	        console.warn('BX.LiveChatRestClient: your auth-token has expired, send query with a new token');
	        return;
	      } else {
	        main_core.Dom.append(_classPrivateMethodGet(this, _getContainer, _getContainer2).call(this), this.target);

	        if (this.type === 'BULLET') {
	          if (this.size) {
	            if (this.size.toUpperCase() === 'XS') {
	              main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--xs');
	            }

	            if (this.size.toUpperCase() === 'S') {
	              main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--sm');
	            }

	            if (this.size.toUpperCase() === 'M') {
	              main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--md');
	            }

	            if (this.size.toUpperCase() === 'L') {
	              main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--lg');
	            }

	            if (this.size.toUpperCase() === 'XL') {
	              main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--xl');
	            }
	          }
	        }
	      }
	    }
	  }]);
	  return Loader;
	}();

	function _getContainer2() {
	  if (!this.layout.container) {
	    this.layout.container = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-loader__container ui-loader__scope\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.type === 'BULLET' ? this.bulletLoader() : '');
	  }

	  return this.layout.container;
	}

	exports.Loader = Loader;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=loader.bundle.js.map
