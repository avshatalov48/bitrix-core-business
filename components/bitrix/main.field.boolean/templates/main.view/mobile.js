/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports) {
	'use strict';

	var BX = window.BX,
	  BXMobileApp = window.BXMobileApp;
	var nodeBoolean = function () {
	  var nodeBoolean = function nodeBoolean(node) {
	    this.node = node;
	    var label = BX.findParent(this.node, {
	      tagName: 'LABEL'
	    });
	    if (label && label.parentNode && !label.parentNode.hasAttribute('bx-fastclick-bound')) {
	      label.parentNode.setAttribute('bx-fastclick-bound', 'Y');
	      FastClick.attach(label.parentNode);
	    }
	    BX.bind(this.node, 'change', BX.delegate(this.change, this));
	  };
	  nodeBoolean.prototype = {
	    change: function change() {
	      BX.onCustomEvent(this, 'onChange', [this, this.node]);
	    }
	  };
	  return nodeBoolean;
	}();
	window.app.exec('enableCaptureKeyboard', true);
	BX.Mobile.Field.Boolean = function (params) {
	  this.init(params);
	};
	BX.Mobile.Field.Boolean.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;
	    if (BX(node)) {
	      result = new nodeBoolean(node);
	    }
	    return result;
	  }
	};

}((this.BX.Mobile.Field.Boolean = this.BX.Mobile.Field.Boolean || {})));
//# sourceMappingURL=mobile.js.map
