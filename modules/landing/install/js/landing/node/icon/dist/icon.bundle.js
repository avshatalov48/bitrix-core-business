this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,landing_node_img) {
	'use strict';

	const encodeDataValue = BX.Landing.Utils.encodeDataValue;
	const decodeDataValue = BX.Landing.Utils.decodeDataValue;
	const data = BX.Landing.Utils.data;
	const attr = BX.Landing.Utils.attr;
	class Icon extends landing_node_img.Img {
	  constructor(options) {
	    super(options);
	    this.type = 'icon';
	    this.node.addEventListener('click', this.onClick.bind(this));
	  }

	  /**
	   * Gets form field
	   * @return {BX.Landing.UI.Field.BaseField}
	   */
	  getField() {
	    if (this.field) {
	      this.field.content = this.getValue();
	    } else {
	      const value = this.getValue();
	      if (value.url) {
	        value.url = decodeDataValue(value.url);
	      }
	      const disableLink = !!this.node.closest('a');
	      this.field = new BX.Landing.UI.Field.Icon({
	        selector: this.selector,
	        title: this.manifest.name,
	        disableLink: disableLink,
	        content: value,
	        dimensions: this.manifest.dimensions || {}
	      });
	    }
	    return this.field;
	  }

	  /**
	   * Sets node value
	   * @param value - Path to image
	   * @param {?boolean} [preventSave = false]
	   * @param {?boolean} [preventHistory = false]
	   * @return {Promise<any>}
	   */
	  setValue(value, preventSave = false, preventHistory = false) {
	    this.lastValue = this.lastValue || this.getValue();
	    this.preventSave(preventSave);
	    return setIconValue(this.node, value).then(() => {
	      if (value.url) {
	        const url = this.preparePseudoUrl(value.url);
	        if (url !== null) {
	          attr(this.node, 'data-pseudo-url', url);
	        }
	      }
	      this.onChange(preventHistory);
	      if (!preventHistory) {
	        BX.Landing.History.getInstance().push();
	      }
	      this.lastValue = this.getValue();
	    });
	  }

	  /**
	   * Gets node value
	   * @return {{src: string}}
	   */
	  getValue() {
	    return {
	      type: 'icon',
	      src: '',
	      id: -1,
	      alt: '',
	      classList: getIconClassList(this.node.className),
	      url: encodeDataValue(getPseudoUrl(this))
	    };
	  }
	  onClick(event) {
	    BX.Event.EventEmitter.emit('BX.Landing.Node.Icon:onClick');
	  }
	}
	BX.Landing.Node.Icon = Icon;

	// eslint-disable-next-line flowtype/require-return-type
	function getPseudoUrl(node) {
	  const url = data(node.node, 'data-pseudo-url');
	  return url || '';
	}

	/**
	 * Gets icon class list
	 * @param {string} className
	 * @return {string[]}
	 */
	function getIconClassList(className) {
	  return className.split(' ');
	}

	/**
	 * Sets icon value or converts to span and sets value
	 * @param {BX.Landing.Node.Icon} node
	 * @param {object} value
	 * @return {Promise<any>}
	 */
	function setIconValue(node, value) {
	  return BX.Landing.UI.Panel.IconPanel.getLibraries().then(libraries => {
	    libraries.forEach(library => {
	      library.categories.forEach(category => {
	        category.items.forEach(item => {
	          const className = BX.Type.isObject(item) ? item.options.join(' ') : item;
	          const classList = className.split(' ');
	          classList.forEach(classItem => {
	            if (classItem) {
	              main_core.Dom.removeClass(node, classItem);
	            }
	          });
	        });
	      });
	    });
	    value.classList.forEach(className => {
	      main_core.Dom.addClass(node, className);
	    });
	  });
	}

	exports.Icon = Icon;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX,BX.Landing.Node));
//# sourceMappingURL=icon.bundle.js.map
