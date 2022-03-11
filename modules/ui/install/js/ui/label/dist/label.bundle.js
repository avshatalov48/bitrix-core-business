this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	var LabelColor = function LabelColor() {
	  babelHelpers.classCallCheck(this, LabelColor);
	};

	babelHelpers.defineProperty(LabelColor, "DEFAULT", 'ui-label-default');
	babelHelpers.defineProperty(LabelColor, "DANGER", 'ui-label-danger');
	babelHelpers.defineProperty(LabelColor, "SUCCESS", 'ui-label-success');
	babelHelpers.defineProperty(LabelColor, "WARNING", 'ui-label-warning');
	babelHelpers.defineProperty(LabelColor, "PRIMARY", 'ui-label-primary');
	babelHelpers.defineProperty(LabelColor, "SECONDARY", 'ui-label-secondary');
	babelHelpers.defineProperty(LabelColor, "LIGHT", 'ui-label-light');
	babelHelpers.defineProperty(LabelColor, "TAG_SECONDARY", 'ui-label-tag-secondary');
	babelHelpers.defineProperty(LabelColor, "TAG_LIGHT", 'ui-label-tag-light');
	babelHelpers.defineProperty(LabelColor, "LIGHT_BLUE", 'ui-label-lightblue');
	babelHelpers.defineProperty(LabelColor, "LIGHT_GREEN", 'ui-label-lightgreen');
	babelHelpers.defineProperty(LabelColor, "LIGHT_ORANGE", 'ui-label-lightorange');

	/**
	 * @namespace {BX.UI}
	 */
	var LabelSize = function LabelSize() {
	  babelHelpers.classCallCheck(this, LabelSize);
	};

	babelHelpers.defineProperty(LabelSize, "MD", 'ui-label-md');
	babelHelpers.defineProperty(LabelSize, "SM", 'ui-label-sm');
	babelHelpers.defineProperty(LabelSize, "LG", 'ui-label-lg');

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;

	var Label = /*#__PURE__*/function () {
	  function Label(options) {
	    babelHelpers.classCallCheck(this, Label);
	    this.text = options.text;
	    this.color = options.color;
	    this.size = options.size;
	    this.link = options.link;
	    this.icon = options.icon;
	    this.fill = !!options.fill ? true : options.fill;
	    this.customClass = options.customClass;
	    this.classList = "ui-label";
	    this.setText(this.text);
	    this.setLink(this.link);
	    this.setColor(this.color);
	    this.setFill(this.fill);
	    this.setCustomClass(this.customClass);
	  } //region COLOR


	  babelHelpers.createClass(Label, [{
	    key: "setColor",
	    value: function setColor(color) {
	      this.color = color;
	      this.setClassList();
	    }
	  }, {
	    key: "getColor",
	    value: function getColor() {
	      return this.color;
	    } // endregion
	    //region FILL

	  }, {
	    key: "setFill",
	    value: function setFill(fill) {
	      this.fill = !!fill ? true : false;
	      this.setClassList();
	    }
	  }, {
	    key: "getFill",
	    value: function getFill() {
	      return this.fill;
	    } // endregion
	    //region SIZE

	  }, {
	    key: "setSize",
	    value: function setSize(size) {
	      this.size = size;
	      this.setClassList();
	    }
	  }, {
	    key: "getSize",
	    value: function getSize() {
	      return this.size;
	    } // endregion
	    //region LINK

	  }, {
	    key: "setLink",
	    value: function setLink(link) {
	      this.link = link;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.link;
	    } // endregion
	    //region TEXT

	  }, {
	    key: "setText",
	    value: function setText(text) {
	      this.text = text;

	      if (main_core.Type.isStringFilled(text)) {
	        this.getTextContainer().textContent = text;
	      }
	    }
	  }, {
	    key: "getText",
	    value: function getText() {
	      return this.text;
	    }
	  }, {
	    key: "getTextContainer",
	    value: function getTextContainer() {
	      if (!this.textContainer) {
	        this.textContainer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-label-inner\">", "</span>"])), this.getText());
	      }

	      return this.textContainer;
	    } // endregion
	    //region CUSTOM CLASS

	  }, {
	    key: "setCustomClass",
	    value: function setCustomClass(customClass) {
	      this.customClass = customClass;
	      this.updateClassList();
	    }
	  }, {
	    key: "getCustomClass",
	    value: function getCustomClass() {
	      return this.customClass;
	    } // endregion
	    //region CLASS LIST

	  }, {
	    key: "setClassList",
	    value: function setClassList() {
	      this.classList = "ui-label";

	      if (typeof this.getColor() != "undefined") {
	        this.classList = this.classList + " " + this.color;
	      }

	      if (typeof this.getSize() != "undefined") {
	        this.classList = this.classList + " " + this.size;
	      }

	      if (typeof this.getCustomClass() != "undefined") {
	        this.classList = this.classList + " " + this.customClass;
	      }

	      if (this.fill) {
	        this.classList = this.classList + " ui-label-fill";
	      }

	      this.updateClassList();
	    }
	  }, {
	    key: "getClassList",
	    value: function getClassList() {
	      return this.classList;
	    }
	  }, {
	    key: "updateClassList",
	    value: function updateClassList() {
	      if (!this.container) {
	        this.getContainer();
	      }

	      this.container.setAttribute("class", this.classList);
	    }
	  }, {
	    key: "getIconAction",
	    value: function getIconAction() {
	      this.iconNode = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-label-icon\"></div>"])));

	      for (var key in this.icon) {
	        this.iconNode.addEventListener(key, this.icon[key]);
	      }

	      return this.iconNode;
	    } // endregion

	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        if (this.getLink()) {
	          this.container = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"", "\">", "</a>"])), this.link, this.getClassList(), this.getTextContainer());
	        } else {
	          this.container = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\">", "</div>"])), this.getClassList(), this.getTextContainer());
	        }

	        if (babelHelpers["typeof"](this.icon) === 'object') {
	          this.container.appendChild(this.getIconAction());
	        }
	      }

	      return this.container;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getContainer();
	    }
	  }]);
	  return Label;
	}();

	babelHelpers.defineProperty(Label, "Color", LabelColor);
	babelHelpers.defineProperty(Label, "Size", LabelSize);

	exports.Label = Label;
	exports.LabelColor = LabelColor;
	exports.LabelSize = LabelSize;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=label.bundle.js.map
