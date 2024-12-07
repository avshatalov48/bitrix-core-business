/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports,main_core) {
	'use strict';

	var BX = window.BX,
	  BXMobileApp = window.BXMobileApp;
	var nodeUrl = function () {
	  var nodeUrl = function nodeUrl(node, container) {
	    this.node = node;
	    this.container = container;
	    this.nodeLink = this.node.previousElementSibling;
	    this.click = BX.delegate(this.click, this);
	    this.callback = BX.delegate(this.callback, this);
	    BX.bind(this.container, 'click', this.click);
	  };
	  nodeUrl.prototype = {
	    click: function click(e) {
	      if (e.toElement.tagName !== 'A') {
	        this.show();
	        return BX.PreventDefault(e);
	      }
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
	          text: BX.util.htmlspecialcharsback(this.nodeLink.value)
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
	      if (data.text === '') {
	        this.node.textContent = this.nodeLink.getAttribute('placeholder');
	        this.node.setAttribute('href', '#');
	      } else {
	        this.node.textContent = data.text;
	        if (this.checkUrl(data.text)) {
	          this.node.setAttribute('href', data.text);
	        } else {
	          this.node.setAttribute('href', 'http://' + data.text);
	        }
	      }
	      this.nodeLink.value = data.text;
	      BX.onCustomEvent(this, 'onChange', [this, this.nodeLink]);
	    },
	    checkUrl: function checkUrl(url) {
	      return /^(callto:|mailto:|https:\/\/|http:\/\/)/i.test(url);
	    }
	  };
	  return nodeUrl;
	}();
	window.app.exec('enableCaptureKeyboard', true);
	BX.Mobile.Field.Url = function (params) {
	  this.init(params);
	};
	BX.Mobile.Field.Url.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;
	    if (BX(node)) {
	      result = new nodeUrl(node, node.parentElement);
	    }
	    return result;
	  }
	};

}((this.BX.Mobile.Field.Url = this.BX.Mobile.Field.Url || {}),BX));
//# sourceMappingURL=mobile.js.map
