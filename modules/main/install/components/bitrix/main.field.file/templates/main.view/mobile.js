/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports,main_core) {
	'use strict';

	var BX = window.BX,
	  BXMobileApp = window.BXMobileApp;
	var nodeFile = function () {
	  var nodeFile = function nodeFile(node) {
	    this.node = node;
	    this.click = BX.delegate(this.click, this);
	    this.callback = BX.delegate(this.callback, this);
	    BX.bind(this.node, "click", this.click);
	    this.isImage = this.node.getAttribute('data-is-image') === 'yes';
	  };
	  nodeFile.prototype = {
	    click: function click(e) {
	      this.show();
	      return BX.PreventDefault(e);
	    },
	    show: function show() {
	      var url = this.node.getAttribute('data-url');
	      var description = this.node.textContent.trim();
	      if (this.isImage) {
	        BXMobileApp.UI.Photo.show({
	          photos: [{
	            url: url,
	            description: description
	          }]
	        });
	      } else {
	        BXMobileApp.UI.Document.open({
	          url: url,
	          filename: description
	        });
	      }
	    }
	  };
	  return nodeFile;
	}();
	window.app.exec('enableCaptureKeyboard', true);
	BX.Mobile.Field.File = function (params) {
	  this.init(params);
	};
	BX.Mobile.Field.File.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;
	    if (BX(node)) {
	      result = new nodeFile(node);
	    }
	    return result;
	  }
	};

}((this.BX.Mobile.Field.File = this.BX.Mobile.Field.File || {}),BX));
//# sourceMappingURL=mobile.js.map
