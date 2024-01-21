this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_node_base,landing_env) {
	'use strict';

	const attr = BX.Landing.Utils.attr;
	const data = BX.Landing.Utils.data;
	const encodeDataValue = BX.Landing.Utils.encodeDataValue;
	const decodeDataValue = BX.Landing.Utils.decodeDataValue;
	class Img extends landing_node_base.Base {
	  constructor(options) {
	    super(options);
	    this.type = 'img';
	    this.editPanel = null;
	    this.lastValue = null;
	    this.field = null;
	    this.uploadParams = options.uploadParams;
	    if (!this.isGrouped()) {
	      this.node.addEventListener('click', this.onClick.bind(this));
	    }
	    if (this.isAllowInlineEdit()) {
	      this.node.setAttribute('title', BX.Landing.Loc.getMessage('LANDING_TITLE_OF_IMAGE_NODE'));
	    }
	  }

	  /**
	   * Click on field - edit mode.
	   * @param {MouseEvent} event
	   */
	  onClick(event) {
	    BX.Event.EventEmitter.emit('BX.Landing.Node.Img:onClick');
	    if (this.manifest.allowInlineEdit !== false && BX.Landing.Main.getInstance().isControlsEnabled() && (!BX.Landing.Node.Text.currentNode || !BX.Landing.Node.Text.currentNode.isEditable()) && !BX.Landing.UI.Panel.StylePanel.getInstance().isShown()) {
	      event.preventDefault();
	      event.stopPropagation();
	      BX.Landing.UI.Button.ColorAction.hideAll();
	      if (!this.editPanel) {
	        this.editPanel = new BX.Landing.UI.Panel.Content(this.selector, {
	          title: BX.Landing.Loc.getMessage('LANDING_IMAGE_PANEL_TITLE'),
	          className: 'landing-ui-panel-edit-image'
	        });
	        this.editPanel.appendFooterButton(new BX.Landing.UI.Button.BaseButton('save_block_content', {
	          text: BX.Landing.Loc.getMessage('BLOCK_SAVE'),
	          onClick: this.save.bind(this),
	          className: 'landing-ui-button-content-save'
	        }));
	        this.editPanel.appendFooterButton(new BX.Landing.UI.Button.BaseButton('cancel_block_content', {
	          text: BX.Landing.Loc.getMessage('BLOCK_CANCEL'),
	          onClick: this.editPanel.hide.bind(this.editPanel),
	          className: 'landing-ui-button-content-cancel'
	        }));
	        window.parent.document.body.appendChild(this.editPanel.layout);
	      }
	      const form = new BX.Landing.UI.Form.BaseForm({
	        title: this.manifest.name
	      });
	      form.addField(this.getField());
	      this.editPanel.clear();
	      this.editPanel.appendForm(form);
	      this.editPanel.show();
	      BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	    }
	  }

	  /**
	   * Saves value changes
	   */
	  save() {
	    const value = this.editPanel.forms[0].fields[0].getValue();
	    if (JSON.stringify(this.getValue()) !== JSON.stringify(value)) {
	      this.setValue(value);
	    }
	    this.editPanel.hide();
	  }

	  /**
	   * Gets form field
	   * @return {?BX.Landing.UI.Field.BaseField}
	   */
	  getField() {
	    if (this.field) {
	      this.field.setValue(this.getValue());
	      this.field.content = this.getValue();
	      requestAnimationFrame(() => {
	        this.field.adjustPreviewBackgroundSize();
	      });
	    } else {
	      let description = '';
	      if (this.manifest.dimensions) {
	        const dimensions = this.manifest.dimensions;
	        const width = dimensions.width || dimensions.maxWidth || dimensions.minWidth;
	        const height = dimensions.height || dimensions.maxHeight || dimensions.minHeight;
	        if (width && !height) {
	          description = `${BX.Landing.Loc.getMessage('LANDING_CONTENT_IMAGE_RECOMMENDED_WIDTH')} `;
	          description += `${width}px`;
	        } else if (height && !width) {
	          description = `${BX.Landing.Loc.getMessage('LANDING_CONTENT_IMAGE_RECOMMENDED_HEIGHT')} `;
	          description += `${height}px`;
	        } else if (width && height) {
	          description = `${BX.Landing.Loc.getMessage('LANDING_CONTENT_IMAGE_RECOMMENDED_SIZE')} `;
	          description += `${width}px&nbsp;/&nbsp;`;
	          description += `${height}px`;
	        }
	      }
	      const value = this.getValue();
	      if (value.url) {
	        value.url = decodeDataValue(value.url);
	      }
	      const disableLink = !!this.node.closest('a') || !!this.manifest.disableLink;
	      if (this.manifest.editInStyle !== true) {
	        var _this$manifest$dimens;
	        this.field = new BX.Landing.UI.Field.Image({
	          selector: this.selector,
	          title: this.manifest.name,
	          description: description,
	          disableLink: disableLink,
	          isAiImageAvailable: landing_env.Env.getInstance().getOptions()['ai_image_available'],
	          isAiImageActive: landing_env.Env.getInstance().getOptions()['ai_image_active'],
	          aiUnactiveInfoCode: landing_env.Env.getInstance().getOptions()['ai_unactive_info_code'],
	          content: value,
	          dimensions: (_this$manifest$dimens = this.manifest.dimensions) != null ? _this$manifest$dimens : {},
	          create2xByDefault: this.manifest.create2xByDefault,
	          disableAltField: isBackground(this.node),
	          uploadParams: this.uploadParams
	        });
	      }
	    }
	    return this.field;
	  }

	  /**
	   * Sets node value
	   * @param value - Path to image
	   * @param {?boolean} [preventSave = false]
	   * @param {?boolean} [preventHistory = false]
	   */
	  setValue(value, preventSave, preventHistory) {
	    this.lastValue = this.lastValue || this.getValue();
	    this.preventSave(preventSave);
	    if (value && value.src) {
	      value.src = decodeURIComponent(value.src);
	    }
	    if (isImage(this.node)) {
	      setImageValue(this.node, value);
	    }
	    if (isBackground(this.node)) {
	      setBackgroundValue(this.node, value);
	    }
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
	  }

	  /**
	   * Gets node value
	   * @return {{src: string}}
	   */
	  getValue() {
	    const value = {
	      type: '',
	      src: '',
	      alt: '',
	      url: ''
	    };
	    const fileId = parseInt(this.node.dataset.fileid, 10);
	    if (fileId > 0) {
	      value.id = fileId;
	    }
	    const fileId2x = parseInt(this.node.dataset.fileid2x, 10);
	    if (fileId2x > 0) {
	      value.id2x = fileId2x;
	    }
	    if (isBackground(this.node)) {
	      value.type = 'background';
	      value.src = getBackgroundUrl(this.node);
	      const src2x = getBackgroundUrl2x(this.node);
	      if (src2x) {
	        value.src2x = src2x;
	      }
	    }
	    if (isImage(this.node)) {
	      value.type = 'image';
	      value.alt = getAlt(this);
	      value.src = getImageSrc(this.node);
	      const src2x = getImageSrc2x(this.node);
	      if (src2x) {
	        value.src2x = src2x;
	      }
	    }
	    value.url = encodeDataValue(getPseudoUrl(this)) || {
	      text: '',
	      href: '',
	      target: '_self',
	      enabled: false
	    };
	    return value;
	  }

	  /**
	   * Prepare pseudo url if needed
	   * @param {object} url
	   * @return {null|object}
	   */
	  preparePseudoUrl(url) {
	    let urlIsChange = false;
	    if (!(url.href === '#' && url.target === '')) {
	      urlIsChange = true;
	    }
	    if (url.href === 'selectActions:') {
	      url.href = '';
	      url.enabled = false;
	      urlIsChange = true;
	    }
	    if (url.href.startsWith('product:')) {
	      url.target = '_self';
	      urlIsChange = true;
	    }
	    if (url.enabled !== false && (url.href === '' || url.href === '#')) {
	      url.enabled = false;
	      urlIsChange = true;
	    }
	    if (url.target === '') {
	      url.target = '_blank';
	      urlIsChange = true;
	    }
	    if (urlIsChange === true) {
	      return url;
	    }
	    return null;
	  }
	}
	BX.Landing.Node.Img = Img;

	/**
	 * Checks that node use backgroundImage
	 * @param {HTMLElement} node
	 * @return {boolean}
	 */
	function isBackground(node) {
	  return node.nodeName !== 'IMG';
	}

	/**
	 * Checks that node is imaged
	 * @param {HTMLElement} node
	 * @return {boolean}
	 */
	function isImage(node) {
	  return node.nodeName === 'IMG';
	}

	/**
	 * Gets background url
	 * @param {BX.Landing.Node.Img} node
	 * @return {boolean}
	 */
	function getBackgroundUrl(node) {
	  const bg = node.style.getPropertyValue('background-image');
	  if (bg) {
	    const res = bg.match(/url\((.*?)\)/);
	    if (res && res[1]) {
	      return res[1].replace(/["'|]/g, '');
	    }
	  }
	  return '';
	}

	/**
	 * Gets background url 2x
	 * @param {BX.Landing.Node.Img} node
	 * @return {boolean}
	 */
	function getBackgroundUrl2x(node) {
	  const bg = node.style.getPropertyValue('background-image');
	  if (bg) {
	    const res = bg.match(/1x, url\(["'|](.*)["'|]\) 2x\)/);
	    if (res && res[1]) {
	      return res[1].replace(/["'|]/g, '');
	    }
	  }
	  return '';
	}

	/**
	 * Gets image alt
	 * @param {BX.Landing.Node.Img} node
	 * @return {string}
	 */
	function getAlt(node) {
	  const alt = attr(node.node, 'alt');
	  return alt || '';
	}
	function getPseudoUrl(node) {
	  const url = data(node.node, 'data-pseudo-url');
	  return url || '';
	}

	/**
	 * Gets image src
	 * @param {HTMLElement} node
	 * @return {string}
	 */
	function getImageSrc(node) {
	  const src = attr(node, 'src');
	  return src || '';
	}

	/**
	 * Gets image src 2x
	 * @param {HTMLElement} node
	 * @return {string}
	 */
	function getImageSrc2x(node) {
	  const src = attr(node, 'srcset');
	  return src ? src.replace(' 2x', '') : '';
	}

	/**
	 * Sets image value or converts to image and sets value
	 * @param {HTMLElement} node
	 * @param {object} value
	 */
	function setImageValue(node, value) {
	  if (isImage(node)) {
	    node.src = value.src;
	    node.alt = value.alt || '';
	    node.dataset.fileid = value.id || -1;
	    node.srcset = value.src2x ? `${value.src2x} 2x` : '';
	    node.dataset.fileid2x = value.id2x || -1;
	  } else {
	    const newNode = BX.create('img', {
	      attrs: {
	        src: value.src,
	        alt: value.alt,
	        'data-fileid': value.id
	      }
	    });
	    BX.Dom.insertBefore(newNode, node);
	    BX.remove(node);
	  }
	}

	/**
	 * Sets background value or converts to div and sets value
	 * @param {HTMLElement} node
	 * @param {object} value
	 */
	function setBackgroundValue(node, value) {
	  if (isBackground(node)) {
	    if (value.src) {
	      const style = [`background-image: url("${value.src}");`];
	      if (value.src2x) {
	        style.push(`background-image: -webkit-image-set(url("${value.src}") 1x, url("${value.src2x}") 2x);`, `background-image: image-set(url("${value.src}") 1x, url("${value.src2x}") 2x);`);
	      }

	      // save css-vars and other styles
	      const oldStyleObj = node.style;
	      const oldStyle = {};
	      [...oldStyleObj].forEach(prop => {
	        oldStyle[prop] = oldStyleObj.getPropertyValue(prop);
	      });
	      node.setAttribute('style', style.join(' '));
	      for (const prop in oldStyle) {
	        if (prop !== 'background-image') {
	          BX.Dom.style(node, prop, oldStyle[prop]);
	        }
	      }
	    } else if (node.style) {
	      BX.Dom.style(node, 'background-image', null);
	    }
	    node.dataset.fileid = value.id || -1;
	    node.dataset.fileid2x = value.id2x || -1;
	  } else {
	    const newNode = BX.create('div', {
	      attrs: {
	        style: `background-image: url("${value.src}")`,
	        'data-fileid': value.id
	      }
	    });
	    BX.Dom.insertBefore(newNode, node);
	    BX.Dom.remove(node);
	  }
	}

	exports.Img = Img;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX.Landing.Node,BX.Landing));
//# sourceMappingURL=img.bundle.js.map
