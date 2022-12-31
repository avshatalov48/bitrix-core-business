this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	class Node {
	  constructor() {
	    var isFunction = BX.Landing.Utils.isFunction;
	    var isString = BX.Landing.Utils.isString;
	    var isPlainObject = BX.Landing.Utils.isPlainObject;
	    var isArray = BX.Landing.Utils.isArray;
	    var bind = BX.Landing.Utils.bind;
	    var proxy = BX.Landing.Utils.proxy;
	    var data = BX.Landing.Utils.data;
	    this.node = options.node;
	    this.manifest = isPlainObject(options.manifest) ? options.manifest : {};
	    this.selector = isString(options.selector) ? options.selector : "";
	    this.onChangeHandler = isFunction(options.onChange) ? options.onChange : function () {};
	    this.onDesignShow = isFunction(options.onDesignShow) ? options.onDesignShow : function () {};
	    this.changeOptionsHandler = isFunction(options.onChangeOptions) ? options.onChangeOptions : function () {};
	    this.onDocumentClick = proxy(this.onDocumentClick, this);
	    this.onDocumentKeydown = proxy(this.onDocumentKeydown, this); // Bind on document events

	    bind(document, "click", this.onDocumentClick);
	    bind(document, "keydown", this.onDocumentKeydown); // Make manifest as reed only

	    Object.freeze(this.manifest); // Add selector attribute

	    this.node.dataset.selector = this.selector;

	    if (this.isAllowInlineEdit()) {
	      this.onAllowInlineEdit();
	    }
	  }

	  onDocumentClick(event) {}
	  /**
	   * Handles document keydown event
	   * @param {KeyboardEvent} event
	   */


	  onDocumentKeydown(event) {
	    if (event.keyCode === 27) {
	      this.onEscapePress();
	    }
	  }
	  /**
	   * Handles escape press event
	   */


	  onEscapePress() {}
	  /**
	   * Gets field for editor form
	   * @abstract
	   * @return {?BX.Landing.UI.Field.BaseField}
	   */


	  getField() {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Shows node content editor
	   */


	  showEditor() {}
	  /**
	   * Hides node content editor
	   */


	  hideEditor() {}
	  /**
	   * Handles allow inline edit event
	   */


	  onAllowInlineEdit() {}
	  /**
	   * Checks that allow inline edit
	   * @return {boolean}
	   */


	  isAllowInlineEdit() {
	    return this.manifest.allowInlineEdit !== false;
	  }
	  /**
	   * Checks that this node is grouped
	   * @return {boolean}
	   */


	  isGrouped() {
	    return typeof this.manifest.group === "string" && this.manifest.group.length > 0;
	  }
	  /**
	   * Sets node value
	   * @abstract
	   * @param {*} value
	   * @param {?boolean} [preventSave = false]
	   * @param {?boolean} [preventHistory = false]
	   */


	  setValue(value, preventSave, preventHistory) {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Gets value
	   * @abstract
	   * @return {string|object}
	   */


	  getValue() {
	    throw new Error("Must be implemented by subclass");
	  }
	  /**
	   * Gets additional values
	   * @return {*}
	   */


	  getAdditionalValue() {
	    if (isPlainObject(this.manifest.extend) && isArray(this.manifest.extend.attrs)) {
	      return this.manifest.extend.attrs.reduce(function (accumulator, key) {
	        return accumulator[key] = data(this.node, key), accumulator;
	      }.bind(this), {});
	    }

	    return {};
	  }
	  /**
	   * Handles content change event and calls external onChange handler
	   */


	  onChange() {
	    this.onChangeHandler.apply(null, [this]);
	  }
	  /**
	   * Gets node index
	   * @return {int}
	   */


	  getIndex() {
	    var index = parseInt(this.selector.split("@")[1]);
	    index = index === index ? index : 0;
	    return index;
	  }
	  /**
	   * Prevents save
	   * @param {boolean} value
	   */


	  preventSave(value) {
	    this.isSavePreventedValue = value;
	  }
	  /**
	   * Checks that save is prevented
	   * @return {boolean}
	   */


	  isSavePrevented() {
	    return !!this.isSavePreventedValue;
	  }
	  /**
	   * Gets current block
	   * @return {number|*}
	   */


	  getBlock() {
	    return BX.Landing.PageObject.getBlocks().getByChildNode(this.node);
	  }

	}

	exports.Node = Node;

}((this.BX.Landing = this.BX.Landing || {}),BX));
//# sourceMappingURL=node.bundle.js.map
