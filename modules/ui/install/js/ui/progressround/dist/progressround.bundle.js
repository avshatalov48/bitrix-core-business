/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_fonts_opensans,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	class ProgressRoundColor {}
	ProgressRoundColor.DEFAULT = 'ui-progressround-default';
	ProgressRoundColor.DANGER = "ui-progressround-danger";
	ProgressRoundColor.SUCCESS = "ui-progressround-success";
	ProgressRoundColor.PRIMARY = "ui-progressround-primary";
	ProgressRoundColor.WARNING = "ui-progressround-warning";

	/**
	 * @namespace {BX.UI}
	 */
	class ProgressRoundStatus {}
	ProgressRoundStatus.COUNTER = "COUNTER";
	ProgressRoundStatus.PERCENT = "PERCENT";
	ProgressRoundStatus.INCIRCLE = "INCIRCLE";
	ProgressRoundStatus.INCIRCLECOUNTER = "INCIRCLECOUNTER";
	ProgressRoundStatus.NONE = "NONE";

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	var _setCustomColors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCustomColors");
	class ProgressRound // extends BX.UI.ProgressRound
	{
	  constructor(options) {
	    Object.defineProperty(this, _setCustomColors, {
	      value: _setCustomColors2
	    });
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
	  }

	  //region Parameters
	  setValue(value) {
	    if (main_core.Type.isNumber(value)) {
	      this.value = value > this.maxValue ? this.maxValue : value;
	    }
	    return this;
	  }
	  getValue() {
	    return this.value;
	  }
	  setMaxValue(value) {
	    if (main_core.Type.isNumber(value)) {
	      this.maxValue = value;
	    }
	    return this;
	  }
	  getMaxValue() {
	    return this.maxValue;
	  }
	  finish() {
	    this.update(this.maxValue);
	  }
	  isFinish() {
	    return this.finished;
	  }
	  setWidth(value) {
	    if (main_core.Type.isNumber(value)) {
	      this.width = value;
	    }
	    return this;
	  }
	  getWidth() {
	    return this.width;
	  }
	  setLineSize(value) {
	    if (main_core.Type.isNumber(value)) {
	      this.lineSize = value > this.width / 2 ? this.width / 2 : value;
	    }
	    return this;
	  }
	  getLineSize() {
	    return this.lineSize;
	  }
	  setColor(color) {
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
	  setColorBar(color) {
	    if (main_core.Type.isStringFilled(color)) {
	      this.colorBar = color;
	      color = "--ui-current-round-color:" + color + ";";
	      babelHelpers.classPrivateFieldLooseBase(this, _setCustomColors)[_setCustomColors](color);
	    }
	    return this;
	  }
	  setColorTrack(color) {
	    if (main_core.Type.isStringFilled(color)) {
	      this.colorTrack = color;
	      this.setFill(true);
	      color = "--ui-current-round-bg-track-color:" + color + ";";
	      babelHelpers.classPrivateFieldLooseBase(this, _setCustomColors)[_setCustomColors](color);
	    }
	    return this;
	  }
	  setFill(fill) {
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
	  setRotation(rotation) {
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
	  }

	  //endregion

	  //region Text
	  setTextBefore(text) {
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
	  createTextBefore(text) {
	    if (!this.textBeforeContainer && main_core.Type.isStringFilled(text)) {
	      this.textBeforeContainer = main_core.Tag.render(_t || (_t = _`
				<div class="ui-progressround-text-before">${0}</div>
			`), text);
	    }
	  }
	  getTextBefore() {
	    if (!this.textBeforeContainer) {
	      this.createTextBefore(this.textBefore);
	    }
	    return this.textBeforeContainer;
	  }
	  setTextAfter(text) {
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
	  createTextAfter(text) {
	    if (!this.textAfterContainer && main_core.Type.isStringFilled(text)) {
	      this.textAfterContainer = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-progressround-text-after">${0}</div>
			`), text);
	    }
	  }
	  getTextAfter() {
	    if (!this.textAfterContainer) {
	      this.createTextAfter(this.textAfter);
	    }
	    return this.textAfterContainer;
	  }

	  //endregion

	  // region Status
	  setStatus() {
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
	  getStatus() {
	    if (!this.status) {
	      if (this.getStatusType() === BX.UI.ProgressRound.Status.COUNTER) {
	        this.status = main_core.Tag.render(_t3 || (_t3 = _`
					<div class="ui-progressround-status">${0}</div>
				`), this.getStatusCounter());
	      } else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLE) {
	        this.status = main_core.Tag.render(_t4 || (_t4 = _`
					<div class="ui-progressround-status-percent-incircle">${0}</div>
				`), this.getStatusPercent());
	      } else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLECOUNTER) {
	        this.status = main_core.Tag.render(_t5 || (_t5 = _`
					<div class="ui-progressround-status-incircle">${0}</div>
				`), this.getStatusCounter());
	      } else if (this.getStatusType() === BX.UI.ProgressRound.Status.PERCENT) {
	        this.status = main_core.Tag.render(_t6 || (_t6 = _`
					<div class="ui-progressround-status-percent">${0}</div>
				`), this.getStatusPercent());
	      } else {
	        this.status = main_core.Dom.create("span", {});
	      }
	    }
	    return this.status;
	  }
	  getStatusPercent() {
	    if (this.maxValue === 0) {
	      return "0%";
	    }
	    this.statusPercent = Math.round(this.getValue() / (this.getMaxValue() / 100));
	    if (this.statusPercent > 100) {
	      this.statusPercent = 100;
	    }
	    return this.statusPercent + "%";
	  }
	  getStatusCounter() {
	    if (Math.round(this.getValue()) > Math.round(this.getMaxValue())) {
	      this.statusCounter = Math.round(this.getMaxValue()) + " / " + Math.round(this.getMaxValue());
	    } else {
	      this.statusCounter = Math.round(this.getValue()) + " / " + Math.round(this.getMaxValue());
	    }
	    return this.statusCounter;
	  }
	  getStatusType() {
	    return this.statusType;
	  }
	  setStatusType(type) {
	    if (main_core.Type.isStringFilled(type)) {
	      this.statusType = type;
	    }
	  }

	  //endregion

	  // region ProgressRound
	  createContainer() {
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
	  getCircleFerence() {
	    return (this.width / 2 - this.lineSize / 2) * 2 * 3.14;
	  }
	  getCircleProgress() {
	    return this.getCircleFerence() - this.getCircleFerence() / this.maxValue * this.value;
	  }
	  getBar() {
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
	  animateProgressBar() {
	    this.svg.setAttributeNS(null, 'class', 'task-report-circle-bar task-report-circle-bar-animate');
	    var progressDashoffset = this.maxValue === 0 ? this.getCircleFerence() : this.getCircleProgress();
	    this.progressMove.setAttributeNS(null, 'stroke-dashoffset', progressDashoffset);
	  }
	  update(value) {
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
	  }

	  //endregion

	  getContainer() {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    this.animateProgressBar();
	    return this.container;
	  }
	  renderTo(node) {
	    if (main_core.Type.isDomNode(node)) {
	      return node.appendChild(this.getContainer());
	    }
	    return null;
	  }
	  destroy() {
	    main_core.Dom.remove(this.container);
	    this.container = null;
	    this.finished = false;
	    this.textAfterContainer = null;
	    this.textBeforeContainer = null;
	    this.bar = null;
	    this.svg = null;
	    for (const property in this) {
	      if (this.hasOwnProperty(property)) {
	        delete this[property];
	      }
	    }
	    Object.setPrototypeOf(this, null);
	  }
	}
	function _setCustomColors2(value) {
	  if (this.container === null) {
	    this.createContainer();
	  }
	  let currentAttribute = this.container.getAttribute('style'),
	    customColorsValue = !currentAttribute ? value : currentAttribute + value;
	  this.container.setAttribute('style', customColorsValue);
	}
	ProgressRound // extends BX.UI.ProgressRound
	.Color = ProgressRoundColor;
	ProgressRound // extends BX.UI.ProgressRound
	.Status = ProgressRoundStatus;

	const UI = main_core.Reflection.namespace('BX.UI');

	/** @deprecated use BX.UI.ProgressRound or import { ProgressRound } from 'ui.progressround' */
	UI.Progressround = ProgressRound;

	exports.ProgressRound = ProgressRound;
	exports.ProgressRoundColor = ProgressRoundColor;
	exports.ProgressRoundStatus = ProgressRoundStatus;

}((this.BX.UI = this.BX.UI || {}),BX,BX));
//# sourceMappingURL=progressround.bundle.js.map
