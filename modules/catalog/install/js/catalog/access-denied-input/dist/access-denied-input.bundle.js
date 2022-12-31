this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject;
	var AccessDeniedInput = /*#__PURE__*/function () {
	  function AccessDeniedInput(options) {
	    babelHelpers.classCallCheck(this, AccessDeniedInput);
	    this.text = options.text || main_core.Loc.getMessage('CATALOG_ACCESS_DENIED_INPUT_TEXT');
	    this.hint = options.hint;
	    this.isReadOnly = options.isReadOnly === true;
	  }

	  babelHelpers.createClass(AccessDeniedInput, [{
	    key: "renderTo",
	    value: function renderTo(node) {
	      var className = this.isReadOnly ? 'ui-ctl-no-border catalog-access-denied-input-readonly' : 'ui-ctl-disabled catalog-access-denied-input';
	      var block = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t<div\n\t\t\tclass=\"ui-ctl ui-ctl-w100 ui-ctl-before-icon ui-ctl-after-icon ", "\"\n\t\t\tdata-hint=\"", "\"\n\t\t\tdata-hint-no-icon\n\t\t>\n\t\t\t<div class=\"ui-ctl-before catalog-access-denied-input-lock\"></div>\n\t\t\t<div class=\"ui-ctl-after catalog-access-denied-input-hint\"></div>\n\t\t\t<div class=\"ui-ctl-element\">", "</div>\n\t\t</div>\n\t\t"])), className, this.hint, this.text);
	      node.innerHTML = '';
	      node.appendChild(block);

	      if (this.hint) {
	        BX.UI.Hint.createInstance({
	          popupParameters: {
	            angle: {
	              offset: 100
	            }
	          }
	        }).init();
	      }
	    }
	  }]);
	  return AccessDeniedInput;
	}();

	exports.AccessDeniedInput = AccessDeniedInput;

}((this.BX.Catalog = this.BX.Catalog || {}),BX));
//# sourceMappingURL=access-denied-input.bundle.js.map
