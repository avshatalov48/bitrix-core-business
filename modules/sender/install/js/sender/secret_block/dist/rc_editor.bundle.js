this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var RcEditor = /*#__PURE__*/function () {
	  function RcEditor() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      elementId: string,
	      conditionElementId: string
	    };
	    babelHelpers.classCallCheck(this, RcEditor);
	    this.element = document.getElementById(options.elementId);
	    this.conditionElement = document.getElementById(options.conditionElementId);

	    if (!this.element || !this.conditionElement) {
	      return;
	    }

	    this.element.disabled = !this.conditionElement.checked;
	    this.bindEvents();
	  }

	  babelHelpers.createClass(RcEditor, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;

	      main_core.Event.bind(this.conditionElement, 'change', function (event) {
	        _this.element.disabled = !event.target.checked;
	      });
	    }
	  }]);
	  return RcEditor;
	}();

	exports.RcEditor = RcEditor;

}((this.BX.Sender = this.BX.Sender || {}),BX));
//# sourceMappingURL=rc_editor.bundle.js.map
