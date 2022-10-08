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
	    this.layout = {
	      wrapper: null,
	      basicBlock: null
	    };
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
	      if (!this.layout.wrapper) {
	        this.layout.wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div>", "</div>\n\t\t\t"])), this.getText());
	      }

	      return this.layout.wrapper;
	    }
	  }, {
	    key: "getBasicBlock",
	    value: function getBasicBlock() {
	      if (!this.layout.basicBlock) {
	        this.layout.basicBlock = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div>a</div>\n\t\t\t"])));
	      }

	      return this.layout.basicBlock;
	    }
	  }, {
	    key: "getRowHeight",
	    value: function getRowHeight() {
	      if (!this.rowHeight) {
	        var styleAtt = getComputedStyle(this.getWrapper());

	        if (styleAtt.lineHeight === 'normal') {
	          var firstHeight = this.getWrapper().offsetHeight;
	          this.layout.wrapper.appendChild(this.getBasicBlock());
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
	      var _this = this;

	      if (this.resize) {
	        var timer;
	        window.addEventListener('resize', function () {
	          if (!timer) {
	            timer = setTimeout(function () {
	              _this.init();

	              clearTimeout(timer);
	            }, 100);
	          }
	        });
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
	      this.layout.wrapper = '';
	      this.target.appendChild(this.getWrapper());
	      var rowHeight = this.getRowHeight();
	      var cropText = '';
	      var numberRows = this.getWrapper().offsetHeight / parseInt(rowHeight);

	      if (numberRows > this.rows) {
	        this.target.setAttribute('title', this.getText());

	        while (this.getWrapper().offsetHeight / parseInt(rowHeight) > this.rows) {
	          cropText = this.layout.wrapper.textContent.substring(0, this.layout.wrapper.textContent.length - 4);
	          this.layout.wrapper.innerHTML = cropText + '...';
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
