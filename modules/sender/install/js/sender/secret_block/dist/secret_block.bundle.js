this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var SecretBlock = /*#__PURE__*/function () {
	  function SecretBlock() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      elementId: string,
	      conditionElementId: string
	    };
	    babelHelpers.classCallCheck(this, SecretBlock);
	    this.element = document.getElementById(options.elementId);
	    this.conditionElement = document.getElementById(options.conditionElementId);

	    if (!this.element || !this.conditionElement) {
	      return;
	    }

	    this.element = this.element.parentElement.parentElement;
	    this.element.style.display = this.conditionElement.checked ? 'block' : 'none';
	    this.bindEvents();
	  }

	  babelHelpers.createClass(SecretBlock, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;

	      main_core.Event.bind(this.conditionElement, 'change', function (event) {
	        _this.element.style.display = event.target.checked ? 'block' : 'none';
	      });
	    }
	  }]);
	  return SecretBlock;
	}();

	exports.SecretBlock = SecretBlock;

}((this.BX.Sender = this.BX.Sender || {}),BX));
//# sourceMappingURL=secret_block.bundle.js.map
