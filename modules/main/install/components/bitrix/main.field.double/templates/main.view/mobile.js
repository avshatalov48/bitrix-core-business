/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports,main_core) {
	'use strict';

	var BX = window.BX,
	  BXMobileApp = window.BXMobileApp;
	var nodeDouble = function () {
	  var nodeDouble = function nodeDouble(node, container) {
	    this.node = node;
	    this.container = container;
	    this.click = BX.delegate(this.click, this);
	    this.callback = BX.delegate(this.callback, this);
	    BX.bind(this.container, 'click', this.click);
	  };
	  nodeDouble.prototype = {
	    click: function click(e) {
	      this.show();
	      return BX.PreventDefault(e);
	    },
	    show: function show() {
	      window.app.exec('showPostForm', {
	        attachButton: {
	          items: []
	        },
	        attachFileSettings: {},
	        attachedFiles: [],
	        extraData: {},
	        mentionButton: {},
	        smileButton: {},
	        message: {
	          text: BX.util.htmlspecialcharsback(this.node.value)
	        },
	        okButton: {
	          callback: this.callback,
	          name: main_core.Loc.getMessage('interface_form_save')
	        },
	        cancelButton: {
	          callback: function callback() {},
	          name: main_core.Loc.getMessage('interface_form_cancel')
	        }
	      });
	    },
	    callback: function callback(data) {
	      data.text = BX.util.htmlspecialchars(data.text) || '';
	      this.node.value = data.text;
	      if (data.text == '') {
	        this.container.innerHTML = "<span class=\"placeholder\">".concat(this.node.getAttribute('placeholder'), "</span>");
	      } else {
	        this.container.innerHTML = data.text;
	      }
	      BX.onCustomEvent(this, 'onChange', [this, this.node]);
	    }
	  };
	  return nodeDouble;
	}();
	window.app.exec('enableCaptureKeyboard', true);
	BX.Mobile.Field.Double = function (params) {
	  this.init(params);
	};
	BX.Mobile.Field.Double.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;
	    if (BX(node)) {
	      result = new nodeDouble(node, BX("".concat(node.id, "_container")));
	    }
	    return result;
	  }
	};

}((this.BX.Mobile.Field.Double = this.BX.Mobile.Field.Double || {}),BX));
//# sourceMappingURL=mobile.js.map
