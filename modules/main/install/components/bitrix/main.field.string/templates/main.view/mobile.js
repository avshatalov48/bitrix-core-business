this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports,main_core) {
	'use strict';

	var BX = window.BX,
	    BXMobileApp = window.BXMobileApp;

	var nodeText = function () {
	  var nodeText = function nodeText(node, container) {
	    this.node = node;
	    this.container = container;
	    this.click = BX.delegate(this.click, this);
	    this.callback = BX.delegate(this.callback, this);
	    BX.bind(this.container, "click", this.click);
	  };

	  nodeText.prototype = {
	    click: function click(e) {
	      this.show();
	      return BX.PreventDefault(e);
	    },
	    show: function show() {
	      this.node.value = this.node.value.replace(/<br\s*[\/]?>/gi, '');
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
	      console.log(data.text);
	      data.text = data.text || '';
	      this.node.value = data.text;

	      if (data.text === '') {
	        this.container.innerHTML = "<span class=\"placeholder\">".concat(this.node.getAttribute('placeholder'), "</span>");
	      } else {
	        this.container.textContent = data.text;
	      }

	      BX.onCustomEvent(this, 'onChange', [this, this.node]);
	    }
	  };
	  return nodeText;
	}();

	window.app.exec('enableCaptureKeyboard', true);

	BX.Mobile.Field.String = function (params) {
	  this.init(params);
	};

	BX.Mobile.Field.String.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;

	    if (BX(node)) {
	      result = new nodeText(node, BX("".concat(node.id, "_target")));
	    }

	    return result;
	  }
	};

}((this.BX.Mobile.Field.String = this.BX.Mobile.Field.String || {}),BX));
//# sourceMappingURL=mobile.js.map
