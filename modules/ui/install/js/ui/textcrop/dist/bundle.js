this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2;
	var TextCrop = /*#__PURE__*/function () {
	  function TextCrop(options) {
	    babelHelpers.classCallCheck(this, TextCrop);
	    this.target = options.target || null;
	    this.rows = options.rows || 2;
	    this.resize = options.resize || false;
	    this.text = null;
	    this.rowHeight = null;
	    this.$wrapper = null;
	    this.$basicBlock = null;
	  }

	  babelHelpers.createClass(TextCrop, [{
	    key: "getText",
	    value: function getText() {
	      if (!this.text) {
	        this.text = this.target ? this.target.innerText : null;
	      }

	      return this.text;
	    }
	  }, {
	    key: "getWrapper",
	    value: function getWrapper() {
	      if (!this.$wrapper) {
	        this.$wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div>", "</div>\n\t\t\t"])), this.getText());
	      }

	      return this.$wrapper;
	    }
	  }, {
	    key: "getBasicBlock",
	    value: function getBasicBlock() {
	      if (!this.$basicBlock) {
	        this.$basicBlock = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div>a</div>\n\t\t\t"])));
	      }

	      return this.$basicBlock;
	    }
	  }, {
	    key: "getRowHeight",
	    value: function getRowHeight() {
	      if (!this.rowHeight) {
	        var styleAtt = getComputedStyle(this.getWrapper());

	        if (styleAtt.lineHeight === 'normal') {
	          var firstHeight = this.getWrapper().offsetHeight;
	          this.$wrapper.appendChild(this.getBasicBlock());
	          var secondHeight = this.getWrapper().offsetHeight;
	          this.getBasicBlock().remove();
	          this.rowHeight = secondHeight - firstHeight;
	        } else {
	          this.rowHeight = styleAtt.lineHeight;
	        }
	      }

	      return this.rowHeight;
	    }
	  }, {
	    key: "cropResize",
	    value: function cropResize() {
	      if (this.resize) {
	        window.addEventListener('resize', BX.delegate(this.init, this));
	      }
	    }
	  }, {
	    key: "crop",
	    value: function crop() {
	      this.init();
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      if (!main_core.Type.isDomNode(this.target)) {
	        return;
	      }

	      this.getText();
	      this.target.innerText = '';
	      this.$wrapper = '';
	      this.target.appendChild(this.getWrapper());
	      var rowHeight = this.getRowHeight();
	      var cropText = '';
	      var numberRows = this.getWrapper().offsetHeight / parseInt(rowHeight);

	      if (numberRows > this.rows) {
	        this.target.setAttribute('title', this.getText());

	        while (this.getWrapper().offsetHeight / parseInt(rowHeight) > this.rows) {
	          cropText = this.$wrapper.textContent.substring(0, this.$wrapper.textContent.length - 4);
	          this.$wrapper.innerHTML = cropText + '...';
	        }
	      }

	      this.cropResize();
	    }
	  }]);
	  return TextCrop;
	}();

	exports.TextCrop = TextCrop;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=bundle.js.map
