/* eslint-disable */
this.BX = this.BX || {};
(function (exports) {
	'use strict';

	class TextAnimate {
	  constructor(options) {
	    this.container = options.container;
	    this.interval = options.interval;
	    this.currentText = null;
	  }
	  setInterval(interval) {
	    if (interval) this.interval = interval;
	  }
	  init(text) {
	    text = text.trim();
	    this.currentText = this.container.innerText;
	    let interval = setInterval(() => {
	      let symbolRnd = parseInt(Math.random() * Math.max(text.length, this.currentText.length));
	      let symbolLink = text[symbolRnd];
	      if (typeof symbolLink === 'undefined') symbolLink = ' ';
	      while (this.currentText.length < symbolRnd) this.currentText += ' ';
	      this.currentText = (this.currentText.slice(0, symbolRnd) + symbolLink + this.currentText.slice(symbolRnd + 1)).trim();
	      this.container.innerText = this.currentText.length === 0 ? '&nbsp;' : this.currentText;
	      if (text === this.container.innerText) clearInterval(interval);
	    }, this.interval ? this.interval : 5);
	  }
	}

	exports.TextAnimate = TextAnimate;

}((this.BX.UI = this.BX.UI || {})));
//# sourceMappingURL=textanimate.bundle.js.map
