/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var VibeBoards = /*#__PURE__*/function (_BX$Landing$Widget$Ba) {
	  babelHelpers.inherits(VibeBoards, _BX$Landing$Widget$Ba);
	  function VibeBoards(element, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, VibeBoards);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VibeBoards).call(this, element));
	    _this.options = options;
	    _this.element = element;
	    _this.initialize();
	    return _this;
	  }
	  babelHelpers.createClass(VibeBoards, [{
	    key: "initialize",
	    value: function initialize() {
	      this.bindButtonEvents();
	    }
	  }, {
	    key: "bindButtonEvents",
	    value: function bindButtonEvents() {
	      var _this2 = this;
	      var buttonHandlers = {
	        '.button-1': this.handleButton1Click.bind(this)
	      };
	      Object.entries(buttonHandlers).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          selector = _ref2[0],
	          handler = _ref2[1];
	        var button = _this2.element.querySelector(selector);
	        if (button) {
	          BX.bind(button, 'click', handler);
	        }
	      });
	    }
	  }, {
	    key: "handleButton1Click",
	    value: function handleButton1Click(event) {
	      event.preventDefault();
	      BX.SidePanel.Instance.open(this.options.inviteDialogLink);
	    }
	  }]);
	  return VibeBoards;
	}(BX.Landing.Widget.Base);

	exports.VibeBoards = VibeBoards;

}((this.BX.Landing.Widget = this.BX.Landing.Widget || {})));
//# sourceMappingURL=script.js.map
