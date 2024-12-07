/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	class ProgressBarColor {}
	ProgressBarColor.NONE = "ui-progressbar-none";
	ProgressBarColor.DANGER = "ui-progressbar-danger";
	ProgressBarColor.SUCCESS = "ui-progressbar-success";
	ProgressBarColor.PRIMARY = "ui-progressbar-primary";
	ProgressBarColor.WARNING = "ui-progressbar-warning";

	/**
	 * @namespace {BX.UI}
	 */
	class ProgressBarSize {}
	ProgressBarSize.MEDIUM = "ui-progressbar-md";
	ProgressBarSize.LARGE = "ui-progressbar-lg";

	/**
	 * @namespace {BX.UI}
	 */
	class ProgressBarStatus {}
	ProgressBarStatus.COUNTER = "COUNTER";
	ProgressBarStatus.PERCENT = "PERCENT";
	ProgressBarStatus.NONE = "NONE";

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	var _setCustomColors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCustomColors");
	class ProgressBar {
	  constructor(options = ProgressBarOptions) {
	    Object.defineProperty(this, _setCustomColors, {
	      value: _setCustomColors2
	    });
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
	    this.clickAfterCallback = main_core.Type.isFunction(this.options.clickAfterCallback) ? this.options.clickAfterCallback : null;
	    this.textAfterContainer = null;
	    this.statusType = main_core.Type.isString(this.options.statusType) ? this.options.statusType : BX.UI.ProgressBar.Status.NONE;
	    this.size = main_core.Type.isStringFilled(this.options.size) || main_core.Type.isNumber(this.options.size) ? this.options.size : BX.UI.ProgressBar.Size.MEDIUM;
	    this.colorTrack = main_core.Type.isString(this.options.colorTrack) ? this.options.colorTrack : null;
	    this.colorBar = main_core.Type.isString(this.options.colorBar) ? this.options.colorBar : null;
	    this.color = main_core.Type.isString(this.options.color) ? this.options.color : BX.UI.ProgressBar.Color.PRIMARY;
	    this.infiniteLoading = main_core.Type.isBoolean(this.options.infiniteLoading) ? this.options.infiniteLoading : false;

	    // this.setStatusType(options.statusType);
	    // this.setColorTrack(options.colorTrack);
	    // this.setColorBar(options.colorBar);
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
	      color = "--ui-current-bar-color:" + color + ";";
	      babelHelpers.classPrivateFieldLooseBase(this, _setCustomColors)[_setCustomColors](color);
	    }
	    return this;
	  }
	  setColorTrack(color) {
	    if (main_core.Type.isStringFilled(color)) {
	      this.colorTrack = color;
	      this.setFill(true);
	      color = "--ui-current-bar-bg-track-color:" + color + ";";
	      babelHelpers.classPrivateFieldLooseBase(this, _setCustomColors)[_setCustomColors](color);
	    }
	    return this;
	  }
	  setSize(size) {
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
	  setFill(fill) {
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
	  setColumn(column) {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    if (column === true) {
	      main_core.Dom.addClass(this.container, "ui-progressbar-column");
	    } else {
	      main_core.Dom.removeClass(this.container, "ui-progressbar-column");
	    }
	    return this;
	  }

	  //endregion

	  //region Text
	  setTextBefore(text) {
	    if (main_core.Type.isStringFilled(text)) {
	      this.textBefore = text;
	      if (this.textBeforeContainer) {
	        main_core.Dom.adjust(this.textBeforeContainer, {
	          html: text
	        });
	      } else {
	        this.createTextBefore(text);
	      }
	    }
	    return this;
	  }
	  createTextBefore(text) {
	    if (!this.textBeforeContainer && main_core.Type.isStringFilled(text)) {
	      this.textBeforeContainer = main_core.Tag.render(_t || (_t = _`
				<div class="ui-progressbar-text-before">${0}</div>
			`), text);
	    }
	  }
	  getTextBefore() {
	    if (!this.textBeforeContainer) {
	      this.createTextBefore(this.textBefore);
	    }
	    return this.textBeforeContainer;
	  }
	  setClickAfterCallback(callback) {
	    if (main_core.Type.isFunction(this.clickAfterCallback)) {
	      main_core.Event.unbind(this.textAfterContainer, 'click', this.clickAfterCallback);
	    }
	    this.clickAfterCallback = callback;
	    return this;
	  }
	  setTextAfter(text) {
	    if (main_core.Type.isStringFilled(text)) {
	      this.textAfter = text;
	      if (this.textAfterContainer) {
	        main_core.Dom.adjust(this.textAfterContainer, {
	          text
	        });
	      } else {
	        this.createTextAfter(text);
	      }
	      if (this.clickAfterCallback) {
	        main_core.Event.unbind(this.textAfterContainer, 'click', this.clickAfterCallback);
	        main_core.Event.bind(this.textAfterContainer, 'click', this.clickAfterCallback);
	      }
	    }
	  }
	  clearTextAfter() {
	    main_core.Dom.remove(this.textAfterContainer);
	    this.textAfterContainer = null;
	    return this;
	  }
	  createTextAfter(text) {
	    if (this.textAfterContainer || !main_core.Type.isStringFilled(text)) {
	      return;
	    }
	    this.textAfterContainer = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-progressbar-text-after">${0}</div>
		`), text);
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
	  getStatus() {
	    if (!this.status) {
	      if (this.getStatusType() === BX.UI.ProgressBar.Status.COUNTER) {
	        this.status = main_core.Tag.render(_t3 || (_t3 = _`
					<div class="ui-progressbar-status">${0}</div>
				`), this.getStatusCounter());
	      } else if (this.getStatusType() === BX.UI.ProgressBar.Status.PERCENT) {
	        this.status = main_core.Tag.render(_t4 || (_t4 = _`
					<div class="ui-progressbar-status-percent">
						<span class="ui-progressbar-status-percent-value">${0}</span>
						<span class="ui-progressbar-status-percent-sign">%</span>
					</div>
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
	    return this.statusPercent;
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

	  // region ProgressBar
	  createContainer() {
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
	      this.setTextAfter(this.textAfter);
	    }
	  }
	  getBar() {
	    if (this.bar === null) {
	      this.bar = main_core.Dom.create("div", {
	        props: {
	          className: `${this.infiniteLoading ? "ui-progressbar-bar infinite-loading" : "ui-progressbar-bar"}`
	        },
	        style: {
	          width: `${this.getStatusPercent()}%`
	        }
	      });
	    }
	    return this.bar;
	  }
	  update(value) {
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
	        width: `${this.getStatusPercent()}%`
	      }
	    });
	  }

	  //endregion

	  getContainer() {
	    if (this.container === null) {
	      this.createContainer();
	    }
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
	  this.setFill(false);
	  this.setColor(BX.UI.ProgressBar.Color.NONE);
	  let currentAttribute = this.container.getAttribute('style'),
	    customColorsValue = !currentAttribute ? value : currentAttribute + value;
	  this.container.setAttribute('style', customColorsValue);
	}
	ProgressBar.Color = ProgressBarColor;
	ProgressBar.Size = ProgressBarSize;
	ProgressBar.Status = ProgressBarStatus;

	exports.ProgressBar = ProgressBar;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=progressbar.bundle.js.map
