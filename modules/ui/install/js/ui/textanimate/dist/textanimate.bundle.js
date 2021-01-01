this.BX = this.BX || {};
(function (exports) {
	'use strict';

	var TextAnimate = /*#__PURE__*/function () {
	  function TextAnimate(options) {
	    babelHelpers.classCallCheck(this, TextAnimate);
	    this.container = options.container;
	    this.interval = options.interval;
	    this.currentText = null;
	  }

	  babelHelpers.createClass(TextAnimate, [{
	    key: "setInterval",
	    value: function setInterval(interval) {
	      if (interval) this.interval = interval;
	    }
	  }, {
	    key: "init",
	    value: function init(text) {
	      var _this = this;

	      text = text.trim();
	      this.currentText = this.container.innerText;
	      var interval = setInterval(function () {
	        var symbolRnd = parseInt(Math.random() * Math.max(text.length, _this.currentText.length));
	        var symbolLink = text[symbolRnd];
	        if (typeof symbolLink === 'undefined') symbolLink = ' ';

	        while (_this.currentText.length < symbolRnd) {
	          _this.currentText += ' ';
	        }

	        _this.currentText = (_this.currentText.slice(0, symbolRnd) + symbolLink + _this.currentText.slice(symbolRnd + 1)).trim();
	        _this.container.innerText = _this.currentText.length === 0 ? '&nbsp;' : _this.currentText;
	        if (text === _this.container.innerText) clearInterval(interval);
	      }, this.interval ? this.interval : 5);
	    }
	  }]);
	  return TextAnimate;
	}();

	exports.TextAnimate = TextAnimate;

}((this.BX.UI = this.BX.UI || {})));
//# sourceMappingURL=textanimate.bundle.js.map
