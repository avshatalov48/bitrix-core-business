this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	var AlertColor = function AlertColor() {
	  babelHelpers.classCallCheck(this, AlertColor);
	};

	babelHelpers.defineProperty(AlertColor, "DEFAULT", 'ui-alert-default');
	babelHelpers.defineProperty(AlertColor, "DANGER", 'ui-alert-danger');
	babelHelpers.defineProperty(AlertColor, "SUCCESS", 'ui-alert-success');
	babelHelpers.defineProperty(AlertColor, "WARNING", 'ui-alert-warning');
	babelHelpers.defineProperty(AlertColor, "PRIMARY", 'ui-alert-primary');

	/**
	 * @namespace {BX.UI}
	 */
	var AlertSize = function AlertSize() {
	  babelHelpers.classCallCheck(this, AlertSize);
	};

	babelHelpers.defineProperty(AlertSize, "MD", 'ui-alert-md');
	babelHelpers.defineProperty(AlertSize, "XS", 'ui-alert-xs');

	/**
	 * @namespace {BX.UI}
	 */
	var AlertIcon = function AlertIcon() {
	  babelHelpers.classCallCheck(this, AlertIcon);
	};

	babelHelpers.defineProperty(AlertIcon, "INFO", 'ui-alert-icon-info');
	babelHelpers.defineProperty(AlertIcon, "WARNING", 'ui-alert-icon-warning');
	babelHelpers.defineProperty(AlertIcon, "DANGER", 'ui-alert-icon-danger');

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\">", "</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var Alert = /*#__PURE__*/function () {
	  function Alert(options) {
	    babelHelpers.classCallCheck(this, Alert);
	    this.text = options.text;
	    this.color = options.color;
	    this.size = options.size;
	    this.icon = options.icon;
	    this.closeBtn = !!options.closeBtn ? true : options.closeBtn;
	    this.animated = !!options.animated ? true : options.animated;
	    this.customClass = options.customClass;
	    this.setText(this.text);
	    this.setSize(this.size);
	    this.setIcon(this.icon);
	    this.setColor(this.color);
	    this.setCloseBtn(this.closeBtn);
	    this.setCustomClass(this.customClass);
	  } //region COLOR


	  babelHelpers.createClass(Alert, [{
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
	    //region ICON

	  }, {
	    key: "setIcon",
	    value: function setIcon(icon) {
	      this.icon = icon;
	      this.setClassList();
	    }
	  }, {
	    key: "getIcon",
	    value: function getIcon() {
	      return this.icon;
	    } // endregion
	    //region TEXT

	  }, {
	    key: "setText",
	    value: function setText(text) {
	      this.text = text;

	      if (main_core.Type.isStringFilled(text)) {
	        this.getTextContainer().innerHTML = text;
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
	        this.textContainer = BX.create('span', {
	          props: {
	            className: 'ui-alert-message'
	          },
	          html: this.getText()
	        });
	      }

	      return this.textContainer;
	    } // endregion
	    // region CLOSE BTN

	  }, {
	    key: "setCloseBtn",
	    value: function setCloseBtn(closeBtn) {
	      this.closeBtn = closeBtn;
	    }
	  }, {
	    key: "getCloseBtn",
	    value: function getCloseBtn() {
	      if (this.closeBtn != true) {
	        return;
	      }

	      if (!this.closeNode && this.closeBtn === true) {
	        this.closeNode = BX.create("span", {
	          props: {
	            className: "ui-alert-close-btn"
	          },
	          events: {
	            click: this.handleCloseBtnClick.bind(this)
	          }
	        });
	      }

	      return this.closeNode;
	    }
	  }, {
	    key: "handleCloseBtnClick",
	    value: function handleCloseBtnClick() {
	      if (this.animated === true) {
	        this.animateClosing();
	      } else {
	        BX.remove(this.container);
	      }
	    } //endregion
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
	      this.classList = "ui-alert";

	      if (typeof this.getColor() != "undefined") {
	        this.classList = this.classList + " " + this.color;
	      }

	      if (typeof this.getSize() != "undefined") {
	        this.classList = this.classList + " " + this.size;
	      }

	      if (typeof this.getIcon() != "undefined") {
	        this.classList = this.classList + " " + this.icon;
	      }

	      if (typeof this.getCustomClass() != "undefined") {
	        this.classList = this.classList + " " + this.customClass;
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
	    } // endregion
	    //region ANIMATION

	  }, {
	    key: "animateOpening",
	    value: function animateOpening() {
	      this.container.style.overflow = "hidden";
	      this.container.style.height = 0;
	      this.container.style.paddingTop = 0;
	      this.container.style.paddingBottom = 0;
	      this.container.style.marginBottom = 0;
	      this.container.style.opacity = 0;
	      setTimeout(function () {
	        this.container.style.height = this.container.scrollHeight + "px";
	        this.container.style.height = "";
	        this.container.style.paddingTop = "";
	        this.container.style.paddingBottom = "";
	        this.container.style.marginBottom = "";
	        this.container.style.opacity = "";
	      }.bind(this), 10);
	      setTimeout(function () {
	        this.container.style.height = "";
	      }.bind(this), 200);
	    }
	  }, {
	    key: "animateClosing",
	    value: function animateClosing() {
	      this.container.style.overflow = "hidden";
	      var alertWrapPos = BX.pos(this.container);
	      this.container.style.height = alertWrapPos.height + "px";
	      setTimeout(function () {
	        this.container.style.height = 0;
	        this.container.style.paddingTop = 0;
	        this.container.style.paddingBottom = 0;
	        this.container.style.marginBottom = 0;
	        this.container.style.opacity = 0;
	      }.bind(this), 10);
	      setTimeout(function () {
	        BX.remove(this.container);
	      }.bind(this), 260);
	    } //endregion

	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      this.container = main_core.Tag.render(_templateObject(), this.getClassList(), this.getTextContainer());

	      if (this.animated === true) {
	        this.animateOpening();
	      }

	      if (this.closeBtn === true) {
	        BX.append(this.getCloseBtn(), this.container);
	      }

	      return this.container;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getContainer();
	    }
	  }]);
	  return Alert;
	}();

	babelHelpers.defineProperty(Alert, "Color", AlertColor);
	babelHelpers.defineProperty(Alert, "Size", AlertSize);
	babelHelpers.defineProperty(Alert, "Icon", AlertIcon);

	exports.Alert = Alert;
	exports.AlertColor = AlertColor;
	exports.AlertSize = AlertSize;
	exports.AlertIcon = AlertIcon;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=alert.bundle.js.map
