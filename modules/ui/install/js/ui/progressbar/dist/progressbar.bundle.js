this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	var ProgressBarColor = function ProgressBarColor() {
	  babelHelpers.classCallCheck(this, ProgressBarColor);
	};
	babelHelpers.defineProperty(ProgressBarColor, "NONE", "ui-progressbar-none");
	babelHelpers.defineProperty(ProgressBarColor, "DANGER", "ui-progressbar-danger");
	babelHelpers.defineProperty(ProgressBarColor, "SUCCESS", "ui-progressbar-success");
	babelHelpers.defineProperty(ProgressBarColor, "PRIMARY", "ui-progressbar-primary");
	babelHelpers.defineProperty(ProgressBarColor, "WARNING", "ui-progressbar-warning");

	/**
	 * @namespace {BX.UI}
	 */
	var ProgressBarSize = function ProgressBarSize() {
	  babelHelpers.classCallCheck(this, ProgressBarSize);
	};
	babelHelpers.defineProperty(ProgressBarSize, "MEDIUM", "ui-progressbar-md");
	babelHelpers.defineProperty(ProgressBarSize, "LARGE", "ui-progressbar-lg");

	/**
	 * @namespace {BX.UI}
	 */
	var ProgressBarStatus = function ProgressBarStatus() {
	  babelHelpers.classCallCheck(this, ProgressBarStatus);
	};
	babelHelpers.defineProperty(ProgressBarStatus, "COUNTER", "COUNTER");
	babelHelpers.defineProperty(ProgressBarStatus, "PERCENT", "PERCENT");
	babelHelpers.defineProperty(ProgressBarStatus, "NONE", "NONE");

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _setCustomColors = /*#__PURE__*/new WeakSet();
	var ProgressBar = /*#__PURE__*/function () {
	  function ProgressBar() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : ProgressBarOptions;
	    babelHelpers.classCallCheck(this, ProgressBar);
	    _classPrivateMethodInitSpec(this, _setCustomColors);
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.value = main_core.Type.isNumber(this.options.value) ? this.options.value : 0;
	    this.maxValue = main_core.Type.isNumber(this.options.maxValue) ? this.options.maxValue : 100;
	    this.bar = null;
	    this.container = null;
	    this.status = null;
	    this.finished = false;
	    this.fill = main_core.Type.isBoolean(this.options.fill) ? this.options.fill : false;
	    this.column = main_core.Type.isBoolean(this.options.column) ? this.options.column : false;
	    this.statusPercent = "0%";
	    this.statusCounter = "0 / 0";
	    this.textBefore = main_core.Type.isString(this.options.textBefore) ? this.options.textBefore : null;
	    this.textBeforeContainer = null;
	    this.textAfter = main_core.Type.isString(this.options.textAfter) ? this.options.textAfter : null;
	    this.textAfterContainer = null;
	    this.statusType = main_core.Type.isString(this.options.statusType) ? this.options.statusType : BX.UI.ProgressBar.Status.NONE;
	    this.size = main_core.Type.isStringFilled(this.options.size) || main_core.Type.isNumber(this.options.size) ? this.options.size : BX.UI.ProgressBar.Size.MEDIUM;
	    this.colorTrack = main_core.Type.isString(this.options.colorTrack) ? this.options.colorTrack : null;
	    this.colorBar = main_core.Type.isString(this.options.colorBar) ? this.options.colorBar : null;
	    this.color = main_core.Type.isString(this.options.color) ? this.options.color : BX.UI.ProgressBar.Color.PRIMARY;

	    // this.setStatusType(options.statusType);
	    // this.setColorTrack(options.colorTrack);
	    // this.setColorBar(options.colorBar);
	  }

	  //region Parameters
	  babelHelpers.createClass(ProgressBar, [{
	    key: "setValue",
	    value: function setValue(value) {
	      if (main_core.Type.isNumber(value)) {
	        this.value = value > this.maxValue ? this.maxValue : value;
	      }
	      return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.value;
	    }
	  }, {
	    key: "setMaxValue",
	    value: function setMaxValue(value) {
	      if (main_core.Type.isNumber(value)) {
	        this.maxValue = value;
	      }
	      return this;
	    }
	  }, {
	    key: "getMaxValue",
	    value: function getMaxValue() {
	      return this.maxValue;
	    }
	  }, {
	    key: "finish",
	    value: function finish() {
	      this.update(this.maxValue);
	    }
	  }, {
	    key: "isFinish",
	    value: function isFinish() {
	      return this.finished;
	    }
	  }, {
	    key: "setColor",
	    value: function setColor(color) {
	      if (main_core.Type.isStringFilled(color)) {
	        if (this.container === null) {
	          this.createContainer();
	        }
	        main_core.Dom.removeClass(this.container, this.color);
	        this.color = color;
	        main_core.Dom.addClass(this.container, this.color);
	      }
	      return this;
	    }
	  }, {
	    key: "setColorBar",
	    value: function setColorBar(color) {
	      if (main_core.Type.isStringFilled(color)) {
	        this.colorBar = color;
	        color = "--ui-current-bar-color:" + color + ";";
	        _classPrivateMethodGet(this, _setCustomColors, _setCustomColors2).call(this, color);
	      }
	      return this;
	    }
	  }, {
	    key: "setColorTrack",
	    value: function setColorTrack(color) {
	      if (main_core.Type.isStringFilled(color)) {
	        this.colorTrack = color;
	        this.setFill(true);
	        color = "--ui-current-bar-bg-track-color:" + color + ";";
	        _classPrivateMethodGet(this, _setCustomColors, _setCustomColors2).call(this, color);
	      }
	      return this;
	    }
	  }, {
	    key: "setSize",
	    value: function setSize(size) {
	      if (this.container === null) {
	        this.createContainer();
	      }
	      if (main_core.Type.isStringFilled(size)) {
	        main_core.Dom.removeClass(this.container, this.size);
	        this.size = size;
	        main_core.Dom.addClass(this.container, this.size);
	      } else if (main_core.Type.isNumber(size)) {
	        this.container.setAttribute('style', "--ui-current-bar-size:" + size + "px;");
	        this.size = size;
	      }
	      return this;
	    }
	  }, {
	    key: "setFill",
	    value: function setFill(fill) {
	      if (this.container === null) {
	        this.createContainer();
	      }
	      if (fill) {
	        main_core.Dom.addClass(this.container, "ui-progressbar-bg");
	      } else {
	        main_core.Dom.removeClass(this.container, "ui-progressbar-bg");
	      }
	      return this;
	    }
	  }, {
	    key: "setColumn",
	    value: function setColumn(column) {
	      if (this.container === null) {
	        this.createContainer();
	      }
	      if (column === true) {
	        main_core.Dom.addClass(this.container, "ui-progressbar-column");
	      } else {
	        main_core.Dom.removeClass(this.container, "ui-progressbar-column");
	      }
	      return this;
	    } //endregion
	    //region Text
	  }, {
	    key: "setTextBefore",
	    value: function setTextBefore(text) {
	      if (main_core.Type.isStringFilled(text)) {
	        this.textBefore = text;
	        if (!this.textBeforeContainer) {
	          this.createTextBefore(text);
	        } else {
	          main_core.Dom.adjust(this.textBeforeContainer, {
	            html: text
	          });
	        }
	      }
	    }
	  }, {
	    key: "createTextBefore",
	    value: function createTextBefore(text) {
	      if (!this.textBeforeContainer && main_core.Type.isStringFilled(text)) {
	        this.textBeforeContainer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-progressbar-text-before\">", "</div>\n\t\t\t"])), text);
	      }
	    }
	  }, {
	    key: "getTextBefore",
	    value: function getTextBefore() {
	      if (!this.textBeforeContainer) {
	        this.createTextBefore(this.textBefore);
	      }
	      return this.textBeforeContainer;
	    }
	  }, {
	    key: "setTextAfter",
	    value: function setTextAfter(text) {
	      if (main_core.Type.isStringFilled(text)) {
	        this.textAfter = text;
	        if (!this.textAfterContainer) {
	          this.createTextAfter(text);
	        } else {
	          main_core.Dom.adjust(this.textAfterContainer, {
	            html: text
	          });
	        }
	      }
	    }
	  }, {
	    key: "createTextAfter",
	    value: function createTextAfter(text) {
	      if (!this.textAfterContainer && main_core.Type.isStringFilled(text)) {
	        this.textAfterContainer = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-progressbar-text-after\">", "</div>\n\t\t\t"])), text);
	      }
	    }
	  }, {
	    key: "getTextAfter",
	    value: function getTextAfter() {
	      if (!this.textAfterContainer) {
	        this.createTextAfter(this.textAfter);
	      }
	      return this.textAfterContainer;
	    } //endregion
	    // region Status
	  }, {
	    key: "setStatus",
	    value: function setStatus() {
	      if (this.getStatusType() === BX.UI.ProgressBar.Status.COUNTER) {
	        main_core.Dom.adjust(this.status, {
	          text: this.getStatusCounter()
	        });
	      } else if (this.getStatusType() === BX.UI.ProgressBar.Status.PERCENT) {
	        main_core.Dom.adjust(this.status.firstChild, {
	          text: this.getStatusPercent()
	        });
	      }
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      if (!this.status) {
	        if (this.getStatusType() === BX.UI.ProgressBar.Status.COUNTER) {
	          this.status = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-progressbar-status\">", "</div>\n\t\t\t\t"])), this.getStatusCounter());
	        } else if (this.getStatusType() === BX.UI.ProgressBar.Status.PERCENT) {
	          this.status = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-progressbar-status-percent\">\n\t\t\t\t\t\t<span class=\"ui-progressbar-status-percent-value\">", "</span>\n\t\t\t\t\t\t<span class=\"ui-progressbar-status-percent-sign\">%</span>\n\t\t\t\t\t</div>\n\t\t\t\t"])), this.getStatusPercent());
	        } else {
	          this.status = main_core.Dom.create("span", {});
	        }
	      }
	      return this.status;
	    }
	  }, {
	    key: "getStatusPercent",
	    value: function getStatusPercent() {
	      if (this.maxValue === 0) {
	        return "0%";
	      }
	      this.statusPercent = Math.round(this.getValue() / (this.getMaxValue() / 100));
	      if (this.statusPercent > 100) {
	        this.statusPercent = 100;
	      }
	      return this.statusPercent;
	    }
	  }, {
	    key: "getStatusCounter",
	    value: function getStatusCounter() {
	      if (Math.round(this.getValue()) > Math.round(this.getMaxValue())) {
	        this.statusCounter = Math.round(this.getMaxValue()) + " / " + Math.round(this.getMaxValue());
	      } else {
	        this.statusCounter = Math.round(this.getValue()) + " / " + Math.round(this.getMaxValue());
	      }
	      return this.statusCounter;
	    }
	  }, {
	    key: "getStatusType",
	    value: function getStatusType() {
	      return this.statusType;
	    }
	  }, {
	    key: "setStatusType",
	    value: function setStatusType(type) {
	      if (main_core.Type.isStringFilled(type)) {
	        this.statusType = type;
	      }
	    } //endregion
	    // region ProgressBar
	  }, {
	    key: "createContainer",
	    value: function createContainer() {
	      if (this.container === null) {
	        this.container = main_core.Dom.create("div", {
	          props: {
	            className: "ui-progressbar"
	          },
	          children: [this.getTextAfter(), this.getTextBefore(), this.getStatus(), BX.create("div", {
	            props: {
	              className: "ui-progressbar-track"
	            },
	            children: [this.getBar()]
	          })]
	        });
	        this.setColor(this.color);
	        this.setColumn(this.column);
	        this.setSize(this.size);
	        this.setFill(this.fill);
	        this.setColorTrack(this.colorTrack);
	        this.setColorBar(this.colorBar);
	      }
	    }
	  }, {
	    key: "getBar",
	    value: function getBar() {
	      if (this.bar === null) {
	        this.bar = main_core.Dom.create("div", {
	          props: {
	            className: "ui-progressbar-bar"
	          },
	          style: {
	            width: "".concat(this.getStatusPercent(), "%")
	          }
	        });
	      }
	      return this.bar;
	    }
	  }, {
	    key: "update",
	    value: function update(value) {
	      if (this.container === null) {
	        this.createContainer();
	      }
	      this.setValue(value);
	      if (value >= this.maxValue) {
	        setTimeout(function () {
	          main_core.Dom.addClass(this.container, "ui-progressbar-finished");
	        }.bind(this), 300);
	        this.finished = true;
	      } else {
	        main_core.Dom.removeClass(this.container, "ui-progressbar-finished");
	        this.finished = false;
	      }
	      this.setStatus();
	      if (this.bar === null) {
	        this.getBar();
	      }
	      main_core.Dom.adjust(this.bar, {
	        style: {
	          width: "".concat(this.getStatusPercent(), "%")
	        }
	      });
	    } //endregion
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.container === null) {
	        this.createContainer();
	      }
	      return this.container;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        return node.appendChild(this.getContainer());
	      }
	      return null;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Dom.remove(this.container);
	      this.container = null;
	      this.finished = false;
	      this.textAfterContainer = null;
	      this.textBeforeContainer = null;
	      this.bar = null;
	      for (var property in this) {
	        if (this.hasOwnProperty(property)) {
	          delete this[property];
	        }
	      }
	      Object.setPrototypeOf(this, null);
	    }
	  }]);
	  return ProgressBar;
	}();
	function _setCustomColors2(value) {
	  if (this.container === null) {
	    this.createContainer();
	  }
	  this.setFill(false);
	  this.setColor(BX.UI.ProgressBar.Color.NONE);
	  var currentAttribute = this.container.getAttribute('style'),
	    customColorsValue = !currentAttribute ? value : currentAttribute + value;
	  this.container.setAttribute('style', customColorsValue);
	}
	babelHelpers.defineProperty(ProgressBar, "Color", ProgressBarColor);
	babelHelpers.defineProperty(ProgressBar, "Size", ProgressBarSize);
	babelHelpers.defineProperty(ProgressBar, "Status", ProgressBarStatus);

	exports.ProgressBar = ProgressBar;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=progressbar.bundle.js.map
