this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject;

	var EmptyState = /*#__PURE__*/function () {
	  function EmptyState(_ref) {
	    var target = _ref.target,
	        size = _ref.size,
	        type = _ref.type;
	    babelHelpers.classCallCheck(this, EmptyState);
	    this.target = main_core.Type.isDomNode(target) ? target : null;
	    this.size = main_core.Type.isNumber(size) ? size : null;
	    this.type = main_core.Type.isString(type) ? type : null;
	    this.container = null;
	  }

	  babelHelpers.createClass(EmptyState, [{
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-emptystate ", "\">\n\t\t\t\t\t<i></i>\n\t\t\t\t</div>\n\t\t\t"])), this.type ? '--' + this.type.toLowerCase() : '');

	        if (this.size) {
	          this.container.style.setProperty('height', this.size + 'px');
	          this.container.style.setProperty('width', this.size + 'px');
	        }
	      }

	      return this.container;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.clean(this.target);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.target) {
	        main_core.Dom.clean(this.target);
	        main_core.Dom.append(this.getContainer(), this.target);
	      }
	    }
	  }]);
	  return EmptyState;
	}();

	exports.EmptyState = EmptyState;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=emptystate.bundle.js.map
