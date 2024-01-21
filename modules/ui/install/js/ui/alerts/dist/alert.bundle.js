/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	class AlertColor {}
	AlertColor.DEFAULT = 'ui-alert-default';
	AlertColor.DANGER = 'ui-alert-danger';
	AlertColor.SUCCESS = 'ui-alert-success';
	AlertColor.WARNING = 'ui-alert-warning';
	AlertColor.PRIMARY = 'ui-alert-primary';
	AlertColor.INFO = 'ui-alert-info';

	/**
	 * @namespace {BX.UI}
	 */
	class AlertSize {}
	AlertSize.MD = 'ui-alert-md';
	AlertSize.XS = 'ui-alert-xs';

	/**
	 * @namespace {BX.UI}
	 */
	class AlertIcon {}
	AlertIcon.NONE = '';
	AlertIcon.INFO = 'ui-alert-icon-info';
	AlertIcon.WARNING = 'ui-alert-icon-warning';
	AlertIcon.DANGER = 'ui-alert-icon-danger';
	AlertIcon.FORBIDDEN = 'ui-alert-icon-forbidden';

	let _ = t => t,
	  _t;
	class Alert {
	  constructor(options) {
	    this.text = options.text;
	    this.color = options.color;
	    this.size = options.size;
	    this.icon = options.icon;
	    this.closeBtn = !!options.closeBtn ? true : options.closeBtn;
	    this.animated = !!options.animated ? true : options.animated;
	    this.customClass = options.customClass;
	    this.beforeMessageHtml = main_core.Type.isElementNode(options.beforeMessageHtml) ? options.beforeMessageHtml : false;
	    this.afterMessageHtml = main_core.Type.isElementNode(options.afterMessageHtml) ? options.afterMessageHtml : false;
	    this.setText(this.text);
	    this.setSize(this.size);
	    this.setIcon(this.icon);
	    this.setColor(this.color);
	    this.setCloseBtn(this.closeBtn);
	    this.setCustomClass(this.customClass);
	  }

	  //region COLOR
	  setColor(color) {
	    this.color = color;
	    this.setClassList();
	  }
	  getColor() {
	    return this.color;
	  }

	  // endregion

	  //region SIZE
	  setSize(size) {
	    this.size = size;
	    this.setClassList();
	  }
	  getSize() {
	    return this.size;
	  }

	  // endregion

	  //region ICON
	  setIcon(icon) {
	    this.icon = icon;
	    this.setClassList();
	  }
	  getIcon() {
	    return this.icon;
	  }

	  // endregion

	  //region TEXT
	  setText(text) {
	    if (main_core.Type.isStringFilled(text)) {
	      this.text = text;
	      this.getTextContainer().innerHTML = text;
	    }
	  }
	  getText() {
	    return this.text;
	  }
	  getTextContainer() {
	    if (!this.textContainer) {
	      this.textContainer = main_core.Dom.create('span', {
	        props: {
	          className: 'ui-alert-message'
	        },
	        html: this.text
	      });
	    }
	    return this.textContainer;
	  }

	  // endregion

	  // region CLOSE BTN
	  setCloseBtn(closeBtn) {
	    this.closeBtn = closeBtn;
	  }
	  getCloseBtn() {
	    if (this.closeBtn != true) {
	      return;
	    }
	    if (!this.closeNode && this.closeBtn === true) {
	      this.closeNode = main_core.Dom.create("span", {
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
	  handleCloseBtnClick() {
	    if (this.animated === true) {
	      this.animateClosing();
	    } else {
	      main_core.Dom.remove(this.container);
	    }
	  }

	  // endregion

	  // region Custom HTML
	  setBeforeMessageHtml(element) {
	    if (main_core.Type.isElementNode(element) && element !== false) {
	      this.beforeMessageHtml = element;
	    }
	  }
	  getBeforeMessageHtml() {
	    return this.beforeMessageHtml;
	  }
	  setAfterMessageHtml(element) {
	    if (main_core.Type.isElementNode(element) && element !== false) {
	      this.afterMessageHtml = element;
	    }
	  }
	  getAfterMessageHtml() {
	    return this.afterMessageHtml;
	  }

	  //endregion

	  //region CUSTOM CLASS
	  setCustomClass(customClass) {
	    this.customClass = customClass;
	    this.updateClassList();
	  }
	  getCustomClass() {
	    return this.customClass;
	  }

	  // endregion

	  //region CLASS LIST
	  setClassList() {
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
	  getClassList() {
	    return this.classList;
	  }
	  updateClassList() {
	    if (!this.container) {
	      this.getContainer();
	    }
	    this.container.setAttribute("class", this.classList);
	  }

	  // endregion

	  //region ANIMATION
	  animateOpening() {
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
	  animateClosing() {
	    this.container.style.overflow = "hidden";
	    var alertWrapPos = main_core.Dom.getPosition(this.container);
	    this.container.style.height = alertWrapPos.height + "px";
	    setTimeout(function () {
	      this.container.style.height = 0;
	      this.container.style.paddingTop = 0;
	      this.container.style.paddingBottom = 0;
	      this.container.style.marginBottom = 0;
	      this.container.style.opacity = 0;
	    }.bind(this), 10);
	    setTimeout(function () {
	      main_core.Dom.remove(this.container);
	    }.bind(this), 260);
	  }

	  //endregion

	  show() {
	    this.animateOpening();
	  }
	  hide() {
	    this.animateClosing();
	  }
	  getContainer() {
	    this.container = main_core.Tag.render(_t || (_t = _`<div class="${0}">${0}</div>`), this.getClassList(), this.getTextContainer());
	    if (this.animated === true) {
	      this.animateOpening();
	    }
	    if (this.closeBtn === true) {
	      main_core.Dom.append(this.getCloseBtn(), this.container);
	    }
	    if (main_core.Type.isElementNode(this.beforeMessageHtml)) {
	      main_core.Dom.prepend(this.getBeforeMessageHtml(), this.getTextContainer());
	    }
	    if (main_core.Type.isElementNode(this.afterMessageHtml)) {
	      main_core.Dom.append(this.getAfterMessageHtml(), this.getTextContainer());
	    }
	    return this.container;
	  }
	  render() {
	    return this.getContainer();
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
	Alert.Color = AlertColor;
	Alert.Size = AlertSize;
	Alert.Icon = AlertIcon;

	exports.Alert = Alert;
	exports.AlertColor = AlertColor;
	exports.AlertSize = AlertSize;
	exports.AlertIcon = AlertIcon;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=alert.bundle.js.map
