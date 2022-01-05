this.BX = this.BX || {};
this.BX.Default = this.BX.Default || {};
this.BX.Default.Field = this.BX.Default.Field || {};
(function (exports) {
	'use strict';

	BX.Default.Field.Address = function (params) {
	  this.init(params);
	};

	BX.Default.Field.Address.prototype = {
	  init: function init(params) {
	    var _params$showMap;

	    this.controlId = params['controlId'] || '';
	    this.value = params['value'] || '';
	    this.isMultiple = params['isMultiple'] === 'true';
	    this.nodeJs = params['nodeJs'] || '';
	    this.fieldNameJs = params['fieldNameJs'] || '';
	    this.fieldName = params['fieldName'] || '';
	    this.showMap = (_params$showMap = params['showMap']) !== null && _params$showMap !== void 0 ? _params$showMap : true;
	    var control = new BX.Fileman.UserField.Address(BX(this.controlId), {
	      value: this.value,
	      multiple: this.isMultiple,
	      showMap: this.showMap
	    });
	    control.nodeJs = this.nodeJs;
	    control.fieldNameJs = this.fieldNameJs;
	    control.fieldName = this.fieldName;
	    BX.addCustomEvent(control, 'UserFieldAddress::Change', function (value) {
	      var node = BX(control.nodeJs);
	      var html = '';

	      if (value.length === 0) {
	        value = [{
	          text: ''
	        }];
	      }

	      for (var i = 0; i < value.length; i++) {
	        var inputValue = value[i].text;

	        if (!!value[i].coords) {
	          inputValue += '|' + value[i].coords.join(';');
	        }

	        inputValue = BX.util.htmlspecialchars(inputValue);
	        html += "<input type=\"hidden\" name=\"".concat(control.fieldNameJs, "\" value=\"").concat(inputValue, "\" >");
	      }

	      node.innerHTML = html;
	      BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldExternalChanged', [control.fieldName]);
	    });
	  }
	};

}((this.BX.Default.Field.Address = this.BX.Default.Field.Address || {})));
//# sourceMappingURL=default.js.map
