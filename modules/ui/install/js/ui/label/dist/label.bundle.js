this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	class LabelColor {}
	LabelColor.DEFAULT = 'ui-label-default';
	LabelColor.DANGER = 'ui-label-danger';
	LabelColor.SUCCESS = 'ui-label-success';
	LabelColor.WARNING = 'ui-label-warning';
	LabelColor.PRIMARY = 'ui-label-primary';
	LabelColor.SECONDARY = 'ui-label-secondary';
	LabelColor.LIGHT = 'ui-label-light';
	LabelColor.TAG_SECONDARY = 'ui-label-tag-secondary';
	LabelColor.TAG_LIGHT = 'ui-label-tag-light';
	LabelColor.LIGHT_BLUE = 'ui-label-lightblue';
	LabelColor.LIGHT_GREEN = 'ui-label-lightgreen';
	LabelColor.ORANGE = 'ui-label-orange';
	LabelColor.LIGHT_ORANGE = 'ui-label-lightorange';
	LabelColor.YELLOW = 'ui-label-yellow';
	LabelColor.LIGHT_YELLOW = 'ui-label-lightyellow';
	LabelColor.LIGHT_RED = 'ui-label-lightred';
	LabelColor.LAVENDER = 'ui-label-lavender';

	/**
	 * @namespace {BX.UI}
	 */
	class LabelSize {}
	LabelSize.MD = 'ui-label-md';
	LabelSize.SM = 'ui-label-sm';
	LabelSize.LG = 'ui-label-lg';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	class Label {
	  constructor(options) {
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

	  //region FILL
	  setFill(fill) {
	    this.fill = !!fill ? true : false;
	    this.setClassList();
	  }
	  getFill() {
	    return this.fill;
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

	  //region LINK
	  setLink(link) {
	    this.link = link;
	  }
	  getLink() {
	    return this.link;
	  }

	  // endregion

	  //region TEXT
	  setText(text) {
	    this.text = text;
	    if (main_core.Type.isStringFilled(text)) {
	      this.getTextContainer().textContent = text;
	    }
	  }
	  getText() {
	    return this.text;
	  }
	  getTextContainer() {
	    if (!this.textContainer) {
	      this.textContainer = main_core.Tag.render(_t || (_t = _`<span class="ui-label-inner">${0}</span>`), this.getText());
	    }
	    return this.textContainer;
	  }

	  // endregion

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
	  getClassList() {
	    return this.classList;
	  }
	  updateClassList() {
	    if (!this.container) {
	      this.getContainer();
	    }
	    this.container.setAttribute("class", this.classList);
	  }
	  getIconAction() {
	    this.iconNode = main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-label-icon"></div>`));
	    for (let key in this.icon) {
	      this.iconNode.addEventListener(key, this.icon[key]);
	    }
	    return this.iconNode;
	  }

	  // endregion

	  getContainer() {
	    if (!this.container) {
	      if (this.getLink()) {
	        this.container = main_core.Tag.render(_t3 || (_t3 = _`<a href="${0}" class="${0}">${0}</a>`), this.link, this.getClassList(), this.getTextContainer());
	      } else {
	        this.container = main_core.Tag.render(_t4 || (_t4 = _`<div class="${0}">${0}</div>`), this.getClassList(), this.getTextContainer());
	      }
	      if (typeof this.icon === 'object') {
	        this.container.appendChild(this.getIconAction());
	      }
	    }
	    return this.container;
	  }
	  render() {
	    return this.getContainer();
	  }
	}
	Label.Color = LabelColor;
	Label.Size = LabelSize;

	exports.Label = Label;
	exports.LabelColor = LabelColor;
	exports.LabelSize = LabelSize;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=label.bundle.js.map
