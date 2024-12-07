/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mobile = this.BX.Mobile || {};
this.BX.Mobile.Field = this.BX.Mobile.Field || {};
(function (exports,main_core) {
	'use strict';

	var nodeEnum = function () {
	  var nodeEnum = function nodeEnum(select, container, isInlineEdit) {
	    this.click = BX.delegate(this.click, this);
	    this.callback = BX.delegate(this.callback, this);
	    this.multiple = false;
	    this.select = null;
	    this.container = null;
	    this.isInlineEdit = null;
	    this.titles = [];
	    this.values = [];
	    this.defaultTitles = [];
	    this.init(select, container, isInlineEdit);
	  };
	  nodeEnum.prototype = {
	    init: function init(select, container) {
	      var isInlineEdit = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      if (BX(select) && BX(container)) {
	        this.select = select;
	        this.container = container;
	        this.isInlineEdit = isInlineEdit;
	        if (!this.select.hasAttribute('bx-bound')) {
	          this.select.setAttribute('bx-bound', 'Y');
	          BX.addCustomEvent(select, 'onChange', BX.delegate(function () {
	            this.multiple = this.select.hasAttribute('multiple');
	            this.initValues();
	          }, this));
	          BX.bind(this.container, 'click', this.click);
	        }
	        this.multiple = select.hasAttribute('multiple');
	        this.initValues();
	      }
	    },
	    initValues: function initValues() {
	      this.titles = [];
	      this.values = [];
	      this.defaultTitles = [];
	      for (var ii = 0; ii < this.select.options.length; ii++) {
	        this.titles.push(this.select.options[ii].textContent.trim());
	        this.values.push(this.select.options[ii].value);
	        if (this.select.options[ii].hasAttribute('selected')) {
	          this.defaultTitles.push(this.select.options[ii].innerHTML);
	        }
	      }
	    },
	    click: function click(e) {
	      this.show();
	      return BX.PreventDefault(e);
	    },
	    show: function show() {
	      if (this.titles.length > 0) {
	        BXMobileApp.UI.SelectPicker.show({
	          callback: this.callback,
	          values: this.titles,
	          multiselect: this.multiple,
	          default_value: this.defaultTitles
	        });
	      }
	    },
	    callback: function callback(data) {
	      this.defaultTitles = [];
	      if (data && data.values && data.values.length > 0) {
	        var keys = [];
	        for (var ii = 0; ii < this.titles.length; ii++) {
	          for (var jj = 0; jj < data.values.length; jj++) {
	            if (this.titles[ii] === data.values[jj]) {
	              keys.push(this.values[ii]);
	              this.defaultTitles.push(this.titles[ii]);
	              break;
	            }
	          }
	        }
	        var html = '';
	        for (var _ii = 0; _ii < this.select.options.length; _ii++) {
	          this.select.options[_ii].removeAttribute('selected');
	          if (BX.util.in_array(this.select.options[_ii].value, keys)) {
	            this.select.options[_ii].setAttribute('selected', 'selected');
	            if (this.multiple) {
	              html += this.select.options[_ii].innerHTML + '<br>';
	            } else {
	              html = this.select.options[_ii].innerHTML;
	            }
	          }
	        }
	        if (html === '' && !this.multiple) {
	          html = "<span style=\"color:grey\">".concat(main_core.Loc.getMessage('interface_form_select'), "</span>");
	        }
	        this.container.innerHTML = html;
	      } else {
	        for (var _ii2 = 0; _ii2 < this.select.options.length; _ii2++) {
	          this.select.options[_ii2].removeAttribute('selected');
	        }
	        this.container.innerHTML = main_core.Loc.getMessage('USER_TYPE_ENUM_NO_VALUE');
	      }
	      if (this.isInlineEdit) {
	        BX.onCustomEvent(this, 'onChange', [this, this.select]);
	      }
	    }
	  };
	  return nodeEnum;
	}();
	window.app.exec('enableCaptureKeyboard', true);
	BX.Mobile.Field.Enum = function (params) {
	  this.init(params);
	};
	BX.Mobile.Field.Enum.prototype = {
	  __proto__: BX.Mobile.Field.prototype,
	  bindElement: function bindElement(node) {
	    var result = null;
	    if (BX(node)) {
	      result = new nodeEnum(node, BX("".concat(node.id, "_select")), node.dataset.isInlineEdit !== 'false');
	    }
	    return result;
	  }
	};

}((this.BX.Mobile.Field.Enum = this.BX.Mobile.Field.Enum || {}),BX));
//# sourceMappingURL=mobile.js.map
