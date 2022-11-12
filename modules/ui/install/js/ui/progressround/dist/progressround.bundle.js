this.BX = this.BX || {};
(function (exports,ui_fonts_opensans,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	var ProgressRoundColor = function ProgressRoundColor() {
	  babelHelpers.classCallCheck(this, ProgressRoundColor);
	};

	babelHelpers.defineProperty(ProgressRoundColor, "DEFAULT", 'ui-progressround-default');
	babelHelpers.defineProperty(ProgressRoundColor, "DANGER", "ui-progressround-danger");
	babelHelpers.defineProperty(ProgressRoundColor, "SUCCESS", "ui-progressround-success");
	babelHelpers.defineProperty(ProgressRoundColor, "PRIMARY", "ui-progressround-primary");
	babelHelpers.defineProperty(ProgressRoundColor, "WARNING", "ui-progressround-warning");

	/**
	 * @namespace {BX.UI}
	 */
	var ProgressRoundStatus = function ProgressRoundStatus() {
	  babelHelpers.classCallCheck(this, ProgressRoundStatus);
	};

	babelHelpers.defineProperty(ProgressRoundStatus, "COUNTER", "COUNTER");
	babelHelpers.defineProperty(ProgressRoundStatus, "PERCENT", "PERCENT");
	babelHelpers.defineProperty(ProgressRoundStatus, "INCIRCLE", "INCIRCLE");
	babelHelpers.defineProperty(ProgressRoundStatus, "INCIRCLECOUNTER", "INCIRCLECOUNTER");
	babelHelpers.defineProperty(ProgressRoundStatus, "NONE", "NONE");

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _setCustomColors = /*#__PURE__*/new WeakSet();

	var ProgressRound // extends BX.UI.ProgressRound
	= /*#__PURE__*/function () {
	  function ProgressRound(options) {
	    babelHelpers.classCallCheck(this, ProgressRound);

	    _classPrivateMethodInitSpec(this, _setCustomColors);

	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.value = main_core.Type.isNumber(this.options.value) ? this.options.value : 0;
	    this.maxValue = main_core.Type.isNumber(this.options.maxValue) ? this.options.maxValue : 100;
	    this.bar = null;
	    this.container = null;
	    this.width = main_core.Type.isNumber(this.options.width) ? this.options.width : 100;
	    this.lineSize = main_core.Type.isNumber(this.options.lineSize) ? this.options.lineSize : 5;
	    this.status = null;
	    this.statusType = main_core.Type.isString(this.options.statusType) ? this.options.statusType : BX.UI.ProgressRound.Status.NONE;
	    this.statusPercent = "0%";
	    this.statusCounter = "0 / 0";
	    this.textBefore = main_core.Type.isString(this.options.textBefore) ? this.options.textBefore : null;
	    this.textBeforeContainer = null;
	    this.textAfter = main_core.Type.isString(this.options.textAfter) ? this.options.textAfter : null;
	    this.textAfterContainer = null;
	    this.fill = false;
	    this.finished = false;
	    this.rotation = main_core.Type.isBoolean(this.options.rotation) ? this.options.rotation : false;
	    this.colorTrack = main_core.Type.isString(this.options.colorTrack) ? this.options.colorTrack : null;
	    this.colorBar = main_core.Type.isString(this.options.colorBar) ? this.options.colorBar : null;
	    this.color = main_core.Type.isString(this.options.color) ? this.options.color : BX.UI.ProgressRound.Color.PRIMARY;
	  } //region Parameters


	  babelHelpers.createClass(ProgressRound, [{
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
	    key: "setWidth",
	    value: function setWidth(value) {
	      if (main_core.Type.isNumber(value)) {
	        this.width = value;
	      }

	      return this;
	    }
	  }, {
	    key: "getWidth",
	    value: function getWidth() {
	      return this.width;
	    }
	  }, {
	    key: "setLineSize",
	    value: function setLineSize(value) {
	      if (main_core.Type.isNumber(value)) {
	        this.lineSize = value > this.width / 2 ? this.width / 2 : value;
	      }

	      return this;
	    }
	  }, {
	    key: "getLineSize",
	    value: function getLineSize() {
	      return this.lineSize;
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
	        color = "--ui-current-round-color:" + color + ";";

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
	        color = "--ui-current-round-bg-track-color:" + color + ";";

	        _classPrivateMethodGet(this, _setCustomColors, _setCustomColors2).call(this, color);
	      }

	      return this;
	    }
	  }, {
	    key: "setFill",
	    value: function setFill(fill) {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      if (main_core.Type.isBoolean(fill)) {
	        this.fill = fill;

	        if (fill === true) {
	          main_core.Dom.addClass(this.container, "ui-progressround-bg");
	        } else {
	          main_core.Dom.removeClass(this.container, "ui-progressround-bg");
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "setRotation",
	    value: function setRotation(rotation) {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      if (main_core.Type.isBoolean(rotation)) {
	        this.rotation = rotation;

	        if (rotation === true) {
	          main_core.Dom.addClass(this.container, "ui-progressround-rotation");
	        } else {
	          main_core.Dom.removeClass(this.container, "ui-progressround-rotation");
	        }
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
	        this.textBeforeContainer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-progressround-text-before\">", "</div>\n\t\t\t"])), text);
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
	        this.textAfterContainer = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-progressround-text-after\">", "</div>\n\t\t\t"])), text);
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
	      if (this.getStatusType() === BX.UI.ProgressRound.Status.COUNTER) {
	        main_core.Dom.adjust(this.status, {
	          text: this.getStatusCounter()
	        });
	      } else if (this.getStatusType() === BX.UI.ProgressRound.Status.PERCENT) {
	        main_core.Dom.adjust(this.status, {
	          text: this.getStatusPercent()
	        });
	      } else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLE) {
	        main_core.Dom.adjust(this.status, {
	          text: this.getStatusPercent()
	        });
	      } else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLECOUNTER) {
	        main_core.Dom.adjust(this.status, {
	          text: this.getStatusCounter()
	        });
	      }
	    }
	  }, {
	    key: "getStatus",
	    value: function getStatus() {
	      if (!this.status) {
	        if (this.getStatusType() === BX.UI.ProgressRound.Status.COUNTER) {
	          this.status = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-progressround-status\">", "</div>\n\t\t\t\t"])), this.getStatusCounter());
	        } else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLE) {
	          this.status = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-progressround-status-percent-incircle\">", "</div>\n\t\t\t\t"])), this.getStatusPercent());
	        } else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLECOUNTER) {
	          this.status = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-progressround-status-incircle\">", "</div>\n\t\t\t\t"])), this.getStatusCounter());
	        } else if (this.getStatusType() === BX.UI.ProgressRound.Status.PERCENT) {
	          this.status = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-progressround-status-percent\">", "</div>\n\t\t\t\t"])), this.getStatusPercent());
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

	      return this.statusPercent + "%";
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
	    // region ProgressRound

	  }, {
	    key: "createContainer",
	    value: function createContainer() {
	      if (this.container === null) {
	        this.container = main_core.Dom.create("div", {
	          props: {
	            className: "ui-progressround"
	          },
	          children: [this.getTextAfter(), this.getTextBefore(), main_core.Dom.create("div", {
	            props: {
	              className: "ui-progressround-track"
	            },
	            children: [this.getStatus(), this.getBar()]
	          })]
	        });
	        this.setStatusType(this.statusType);
	        this.setColor(this.color);
	        this.setRotation(this.rotation);
	        this.setFill(this.fill);
	        this.setColorTrack(this.colorTrack);
	        this.setColorBar(this.colorBar);
	      }
	    }
	  }, {
	    key: "getCircleFerence",
	    value: function getCircleFerence() {
	      return (this.width / 2 - this.lineSize / 2) * 2 * 3.14;
	    }
	  }, {
	    key: "getCircleProgress",
	    value: function getCircleProgress() {
	      return this.getCircleFerence() - this.getCircleFerence() / this.maxValue * this.value;
	    }
	  }, {
	    key: "getBar",
	    value: function getBar() {
	      var factRadius = this.width / 2 - this.lineSize / 2;
	      this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
	      this.svg.setAttributeNS(null, 'class', 'ui-progressround-track-bar');
	      this.svg.setAttributeNS(null, 'viewport', '0 0 ' + this.width + ' ' + this.width);
	      this.svg.setAttributeNS(null, 'width', this.width);
	      this.svg.setAttributeNS(null, 'height', this.width);
	      this.progressBg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
	      this.progressBg.setAttributeNS(null, 'r', factRadius);
	      this.progressBg.setAttributeNS(null, 'cx', this.width / 2);
	      this.progressBg.setAttributeNS(null, 'cy', this.width / 2);
	      this.progressBg.setAttributeNS(null, 'stroke-width', this.lineSize);
	      this.progressBg.setAttributeNS(null, 'class', 'ui-progressround-track-bar-bg');
	      this.svg.appendChild(this.progressBg);
	      this.progressMove = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
	      this.progressMove.setAttributeNS(null, 'r', factRadius);
	      this.progressMove.setAttributeNS(null, 'cx', this.width / 2);
	      this.progressMove.setAttributeNS(null, 'cy', this.width / 2);
	      this.progressMove.setAttributeNS(null, 'stroke-width', this.lineSize);
	      this.progressMove.setAttributeNS(null, 'stroke-dasharray', this.getCircleFerence());
	      this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircleFerence());
	      this.progressMove.setAttributeNS(null, 'class', 'ui-progressround-track-bar-progress');
	      this.svg.appendChild(this.progressMove);
	      return this.svg;
	    }
	  }, {
	    key: "animateProgressBar",
	    value: function animateProgressBar() {
	      this.svg.setAttributeNS(null, 'class', 'task-report-circle-bar task-report-circle-bar-animate');
	      var progressDashoffset = this.maxValue === 0 ? this.getCircleFerence() : this.getCircleProgress();
	      this.progressMove.setAttributeNS(null, 'stroke-dashoffset', progressDashoffset);
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
	          main_core.Dom.addClass(this.container, "ui-progressround-finished");
	        }.bind(this), 300);
	        this.finished = true;
	      } else {
	        main_core.Dom.removeClass(this.container, "ui-progressround-finished");
	        this.finished = false;
	      }

	      this.setStatus();

	      if (this.svg === null) {
	        this.getBar();
	      }

	      this.animateProgressBar();
	    } //endregion

	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      this.animateProgressBar();
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
	      this.svg = null;

	      for (var property in this) {
	        if (this.hasOwnProperty(property)) {
	          delete this[property];
	        }
	      }

	      Object.setPrototypeOf(this, null);
	    }
	  }]);
	  return ProgressRound;
	}();

	function _setCustomColors2(value) {
	  if (this.container === null) {
	    this.createContainer();
	  }

	  var currentAttribute = this.container.getAttribute('style'),
	      customColorsValue = !currentAttribute ? value : currentAttribute + value;
	  this.container.setAttribute('style', customColorsValue);
	}

	babelHelpers.defineProperty(ProgressRound // extends BX.UI.ProgressRound
	, "Color", ProgressRoundColor);
	babelHelpers.defineProperty(ProgressRound // extends BX.UI.ProgressRound
	, "Status", ProgressRoundStatus);

	var UI = main_core.Reflection.namespace('BX.UI');
	/** @deprecated use BX.UI.ProgressRound or import { ProgressRound } from 'ui.progressround' */

	UI.Progressround = ProgressRound;

	exports.ProgressRound = ProgressRound;
	exports.ProgressRoundColor = ProgressRoundColor;
	exports.ProgressRoundStatus = ProgressRoundStatus;

}((this.BX.UI = this.BX.UI || {}),BX,BX));
//# sourceMappingURL=progressround.bundle.js.map
