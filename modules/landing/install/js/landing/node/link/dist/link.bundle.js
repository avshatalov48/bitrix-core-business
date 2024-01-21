this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_node_base,main_core) {
	'use strict';

	const trim = BX.Landing.Utils.trim;
	const isPlainObject = BX.Landing.Utils.isPlainObject;
	const isString = BX.Landing.Utils.isString;
	const textToPlaceholders = BX.Landing.Utils.textToPlaceholders;
	const create = BX.Landing.Utils.create;
	const escapeText = BX.Landing.Utils.escapeText;
	const decodeDataValue = BX.Landing.Utils.decodeDataValue;
	class Link extends landing_node_base.Base {
	  constructor(options) {
	    super(options);
	    this.type = 'link';
	    if (!this.isGrouped()) {
	      main_core.Event.bind(this.node, 'click', this.onClick.bind(this));
	    }
	    if (this.isAllowInlineEdit()) {
	      main_core.Dom.attr(this.node, 'title', BX.Landing.Loc.getMessage('LANDING_TITLE_OF_LINK_NODE'));
	    }
	    this.onChange = BX.Runtime.debounce(this.onChange, 500);
	    this.onContentUpdate = BX.Runtime.debounce(this.onContentUpdate, 500);
	  }
	  onContentUpdate() {
	    BX.Landing.History.getInstance().push();
	    this.getField().setValue(this.getValue());
	  }
	  isMenuMode() {
	    return this.manifest.menuMode === true;
	  }

	  /**
	   * Handles click event
	   * @param {MouseEvent} event
	   */
	  onClick(event) {
	    event.preventDefault();
	    if (!this.isMenuMode()) {
	      event.stopPropagation();
	    }
	    if (this.isAllowInlineEdit()) {
	      BX.Landing.UI.Button.ColorAction.hideAll();
	      if (!BX.Landing.UI.Panel.StylePanel.getInstance().isShown()) {
	        BX.Landing.UI.Panel.Link.getInstance().show(this);
	      }
	    }
	  }

	  /**
	   * Sets node value
	   * @param data
	   * @param {?boolean} [preventSave = false]
	   * @param {?boolean} [preventHistory = false]
	   */
	  setValue(data, preventSave, preventHistory) {
	    this.startValue = this.startValue || this.getValue();
	    this.preventSave(preventSave);
	    if (!this.containsImage() && this.isAllowInlineEdit()) {
	      const field = this.getField(true).hrefInput;
	      if (isString(data.text) && data.text.includes('{{name}}')) {
	        field.getPlaceholderData(data.href).then(placeholdersData => {
	          this.node.innerHTML = data.text.replace(/{{name}}/, `<span data-placeholder="name">${placeholdersData.name}</span>`);
	        }).catch(() => {});
	      } else if (!this.getField().containsHtml() && !this.manifest.skipContent) {
	        this.node.innerHTML = escapeText(data.text);
	      }
	    }
	    this.setAttrValue(data);
	    this.onChange(preventHistory);
	    if (!preventHistory) {
	      this.onContentUpdate();
	    }
	  }
	  setAttrValue(data) {
	    main_core.Dom.attr(this.node, 'href', decodeDataValue(data.href));
	    main_core.Dom.attr(this.node, 'target', escapeText(data.target));
	    if ('attrs' in data) {
	      Object.keys(data.attrs).forEach(attr => {
	        if (Object.prototype.hasOwnProperty.call(data.attrs, attr)) {
	          main_core.Dom.attr(this.node, attr, data.attrs[attr]);
	        }
	      });
	    } else {
	      main_core.Dom.attr(this.node, 'data-url', null);
	      main_core.Dom.attr(this.node, 'data-embed', null);
	    }
	  }

	  /**
	   * Checks that this node contains image node
	   * @return {boolean}
	   */
	  containsImage() {
	    return Boolean(this.node.firstElementChild) && this.node.firstElementChild.tagName === 'IMG';
	  }

	  /**
	   * Gets node value
	   * @return {{text: string, href: string|*, target: string|*}}
	   */
	  getValue() {
	    const value = {
	      text: textToPlaceholders(trim(this.node.innerHTML)),
	      href: trim(this.node.getAttribute('href')),
	      target: trim(this.node.getAttribute('target') || '_self')
	    };
	    if (this.node.getAttribute('data-url')) {
	      value.attrs = {
	        'data-url': trim(this.node.getAttribute('data-url'))
	      };
	    }
	    if (this.node.getAttribute('data-dynamic')) {
	      if (!isPlainObject(value.attrs)) {
	        value.attrs = {};
	      }
	      value.attrs['data-dynamic'] = this.node.getAttribute('data-dynamic');
	    }
	    if (this.manifest.skipContent) {
	      value.skipContent = true;
	      delete value.text;
	    }
	    if (value.href && value.href.startsWith('selectActions:')) {
	      value.href = '#';
	    }
	    return value;
	  }

	  /**
	   * Gets field
	   * @param {boolean} preventAdjustValue
	   * @return {BX.Landing.UI.Field.BaseField}
	   */
	  getField(preventAdjustValue) {
	    const value = this.getValue();
	    value.text = textToPlaceholders(create('div', {
	      html: value.text
	    }).innerHTML);
	    if (!this.field) {
	      const allowedTypes = [BX.Landing.UI.Field.LinkUrl.TYPE_BLOCK, BX.Landing.UI.Field.LinkUrl.TYPE_PAGE, BX.Landing.UI.Field.LinkUrl.TYPE_CRM_FORM, BX.Landing.UI.Field.LinkUrl.TYPE_CRM_PHONE];
	      if (BX.Landing.Main.getInstance().options.params.type === BX.Landing.Main.TYPE_STORE) {
	        allowedTypes.push(BX.Landing.UI.Field.LinkUrl.TYPE_CATALOG);
	      }
	      if (BX.Landing.Main.getInstance().options.features.includes('diskFile')) {
	        allowedTypes.push(BX.Landing.UI.Field.LinkUrl.TYPE_DISK_FILE);
	      }
	      this.field = new BX.Landing.UI.Field.Link({
	        title: this.manifest.name,
	        selector: this.selector,
	        skipContent: this.manifest.skipContent,
	        content: value,
	        options: {
	          siteId: BX.Landing.Main.getInstance().options.site_id,
	          landingId: BX.Landing.Main.getInstance().id
	        },
	        allowedTypes
	      });
	    } else if (!preventAdjustValue) {
	      this.field.setValue(value);
	      this.field.content = value;
	      this.field.hrefInput.content = value.href;
	      this.field.hrefInput.makeDisplayedHrefValue();
	      this.field.hrefInput.setHrefTypeSwitcherValue(this.field.hrefInput.getHrefStringType());
	      this.field.hrefInput.removeHrefTypeFromHrefString();
	    }
	    return this.field;
	  }
	}
	BX.Landing.Node.Link = Link;

	exports.Link = Link;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX.Landing.Node,BX));
//# sourceMappingURL=link.bundle.js.map
