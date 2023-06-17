this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,main_core) {
	'use strict';

	let _ = t => t,
	    _t;
	class Link extends landing_ui_field_basefield.BaseField {
	  constructor(data) {
	    super(data);
	    BX.Landing.UI.Field.BaseField.apply(this, arguments);
	    this.options = data.options || {};
	    main_core.Dom.remove(this.input);
	    this.onValueChangeHandler = data.onValueChange ? data.onValueChange : function () {};
	    this.content = main_core.Type.isPlainObject(this.content) ? this.content : {};
	    this.content = BX.Landing.Utils.clone(this.content);
	    this.content.text = BX.Landing.Utils.trim(this.content.text);
	    this.content.href = BX.Landing.Utils.trim(BX.Landing.Utils.escapeText(this.content.href));
	    this.content.target = BX.Landing.Utils.trim(BX.Landing.Utils.escapeText(this.content.target));
	    this.skipContent = data.skipContent;
	    this.detailPageMode = data.detailPageMode === true;

	    if (!this.containsImage() && !this.containsHtml()) {
	      if (main_core.Type.isStringFilled(this.content.text)) {
	        this.content.text = this.content.text.replace('&nbsp;', ' ');
	      }

	      this.content.text = BX.Landing.Utils.escapeText(this.content.text);
	    }

	    this.input = new BX.Landing.UI.Field.Text({
	      placeholder: BX.Landing.Loc.getMessage("FIELD_LINK_TEXT_LABEL"),
	      selector: this.selector,
	      content: main_core.Text.decode(this.content.text),
	      textOnly: true,
	      onValueChange: function () {
	        this.onValueChangeHandler(this);

	        if (this.hrefInput.getValue() === this.hrefInput.typeHrefs.page + '#landing0') {
	          const value = this.input.getValue();
	          const placeholder = this.hrefInput.input.firstElementChild;

	          if (placeholder) {
	            const textNode = placeholder.querySelector('.landing-ui-field-url-placeholder-text');
	            textNode.innerText = main_core.Text.decode(value.replace(/&nbsp;/g, ' '));
	          }
	        }

	        const event = new main_core.Event.BaseEvent({
	          data: {
	            value: this.getValue()
	          },
	          compatData: [this.getValue()]
	        });
	        this.emit('change', event);
	      }.bind(this)
	    });

	    if (this.skipContent) {
	      this.input.layout.hidden = true;
	      this.header.hidden = true;
	    }

	    this.hrefInput = new BX.Landing.UI.Field.LinkUrl({
	      title: BX.Landing.Loc.getMessage("FIELD_LINK_HREF_LABEL_2"),
	      placeholder: '',
	      selector: this.selector,
	      content: this.content.href,
	      contentRoot: this.contentRoot,
	      onInput: this.onHrefInput.bind(this),
	      textOnly: true,
	      options: this.options,
	      disallowType: data.disallowType,
	      disableBlocks: data.disableBlocks,
	      allowedTypes: data.allowedTypes,
	      detailPageMode: data.detailPageMode === true,
	      sourceField: data.sourceField,
	      onValueChange: function () {
	        this.onValueChangeHandler(this);
	        const event = new BX.Event.BaseEvent({
	          data: {
	            value: this.getValue()
	          },
	          compatData: [this.getValue()]
	        });
	        this.emit('change', event);
	      }.bind(this),
	      onNewPage: function () {
	        const value = this.input.getValue();
	        const placeholder = this.hrefInput.input.firstElementChild;

	        if (placeholder) {
	          const textNode = placeholder.querySelector('.landing-ui-field-url-placeholder-text');
	          textNode.innerHTML = value.replace(/&nbsp;/g, ' ');
	        }
	      }.bind(this)
	    });
	    this.targetInput = new BX.Landing.UI.Field.DropdownInline({
	      title: BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_LABEL"),
	      selector: this.selector,
	      className: "landing-ui-field-dropdown-inline",
	      content: this.content.target,
	      contentRoot: this.contentRoot,
	      items: {
	        "_self": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_SELF"),
	        "_blank": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_BLANK"),
	        "_popup": BX.Landing.Loc.getMessage("FIELD_LINK_TARGET_POPUP")
	      },
	      onValueChange: function () {
	        this.onValueChangeHandler(this);
	        const event = new BX.Event.BaseEvent({
	          data: {
	            value: this.getValue()
	          },
	          compatData: [this.getValue()]
	        });
	        this.emit('change', event);
	      }.bind(this)
	    });
	    this.stateNode = main_core.Tag.render(_t || (_t = _`
			<div class="landing-ui-field-url-state-box"></div>
		`));
	    this.mediaLayout = main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-link-media-layout"
	      }
	    });

	    if (this.containsImage() || this.containsHtml()) {
	      this.input.layout.hidden = true;
	      this.header.hidden = true;
	      this.hrefInput.header.innerHTML = this.header.innerHTML;
	    }

	    this.wrapper = BX.Landing.UI.Field.Link.createWrapper();
	    this.left = BX.Landing.UI.Field.Link.createLeft();
	    this.center = BX.Landing.UI.Field.Link.createCenter();
	    this.right = BX.Landing.UI.Field.Link.createRight();
	    main_core.Dom.append(this.input.layout, this.left);
	    main_core.Dom.append(this.hrefInput.layout, this.center); //show target panel

	    this.targetInput = this.createTargetInput(this.hrefInput.getRightData());
	    this.right.innerHTML = '';

	    if (this.targetInput.hasOwnProperty('layout')) {
	      main_core.Dom.append(this.targetInput.layout, this.right);
	    } else {
	      main_core.Dom.append(this.targetInput, this.right);
	    }

	    this.showElement(this.right);
	    const selectedHrefType = this.hrefInput.getSelectedHrefType();

	    if (selectedHrefType === this.hrefInput.typeHrefs.start) {
	      this.hideElement(this.right);
	    }

	    const typeData = this.hrefInput.getTypeData(selectedHrefType);
	    this.checkVisibleMediaPanel(selectedHrefType, this.targetInput.getValue());
	    this.targetInput.subscribe('onChange', () => {
	      this.checkVisibleMediaPanel(selectedHrefType, this.targetInput.getValue());
	    });

	    if (typeData.hasOwnProperty('hideInput')) {
	      const input = this.hrefInput.gridCenterCell.querySelector('.landing-ui-field-input');
	      input.hidden = !!typeData.hideInput;
	    }

	    const gridCenter = this.center.querySelector('.landing-ui-field-link-url-grid-center');
	    main_core.Dom.append(this.stateNode, gridCenter);
	    main_core.Dom.addClass(gridCenter, "--only-manual-entry");

	    if (typeData.hasOwnProperty('button')) {
	      if (!gridCenter.querySelector('.landing-ui-button-grid-center-cell')) {
	        const newCenterCellButton = this.hrefInput.createCenterCellButton(typeData.button);
	        main_core.Dom.append(newCenterCellButton.layout, gridCenter);
	        main_core.Dom.removeClass(gridCenter, '--only-manual-entry');
	      }
	    }

	    this.hrefInput.subscribe('deleteAction', () => {
	      if (this.hrefInput.getSelectedHrefType() === this.hrefInput.typeHrefs.start) {
	        this.hideElement(this.right);
	      }
	    });
	    this.hrefInput.subscribe('selectAction', event => {
	      const selectedHrefType = this.hrefInput.getSelectedHrefType();
	      const typeData = this.hrefInput.getTypeData(selectedHrefType);
	      this.prepareGridCenter(selectedHrefType);
	      const input = this.hrefInput.gridCenterCell.querySelector('.landing-ui-field-input');
	      input.hidden = !!typeData.hideInput; //show target panel

	      this.targetInput = this.createTargetInput(event.data.right);

	      if (!main_core.Type.isUndefined(this.selectedTargetValueByUser)) {
	        this.targetInput.setValue(this.selectedTargetValueByUser);
	      }

	      this.targetInput.subscribe('onItemClick', () => {
	        this.selectedTargetValueByUser = this.targetInput.getValue();
	      });
	      this.right.innerHTML = '';

	      if (this.targetInput.hasOwnProperty('layout')) {
	        main_core.Dom.append(this.targetInput.layout, this.right);
	      } else {
	        main_core.Dom.append(this.targetInput, this.right);
	      }

	      this.showElement(this.right);
	      this.checkVisibleMediaPanel(selectedHrefType, this.targetInput.getValue());
	      this.targetInput.subscribe('onChange', () => {
	        this.checkVisibleMediaPanel(selectedHrefType, this.targetInput.getValue());
	      });
	      this.disableMedia();
	      this.adjustTarget();
	    });
	    this.hrefInput.subscribe('buildCenter', event => {
	      const button = this.hrefInput.gridCenterCell.querySelector('.landing-ui-button-grid-center-cell');
	      const gridCenter = this.center.querySelector('.landing-ui-field-link-url-grid-center');

	      if (button) {
	        button.remove();
	      }

	      main_core.Dom.append(this.stateNode, gridCenter);

	      if (event.data.button) {
	        main_core.Dom.append(event.data.button.layout, gridCenter);
	        main_core.Dom.removeClass(gridCenter, '--only-manual-entry');
	      } else {
	        main_core.Dom.addClass(gridCenter, "--only-manual-entry");
	      }
	    });
	    main_core.Dom.append(this.left, this.wrapper);
	    main_core.Dom.append(this.center, this.wrapper);
	    main_core.Dom.append(this.right, this.wrapper);
	    main_core.Dom.append(this.wrapper, this.layout);
	    main_core.Dom.append(this.mediaLayout, this.layout);
	    main_core.Dom.addClass(this.layout, 'landing-ui-field-link');

	    if (this.hrefInput.getSelectedHrefType() === '') {
	      if (this.content.target === '_popup') {
	        this.adjustVideo();
	      }
	    }

	    this.adjustEditLink();
	    this.adjustTarget();
	    this.targetInput.subscribe('onItemClick', () => {
	      this.selectedTargetValueByUser = this.targetInput.getValue();
	    });
	    this.hrefInput.subscribe('readyToSave', event => {
	      if (event.data.readyToSave) {
	        this.readyToSave = true;
	        this.emit('onChangeReadyToSave');
	      } else {
	        this.readyToSave = false;
	        this.emit('onChangeReadyToSave');
	      }
	    });
	  }
	  /**
	   * Creates wrapper element
	   * @static
	   * @return {HTMLElement}
	   */


	  static createWrapper() {
	    return main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-link-wrapper"
	      }
	    });
	  }
	  /**
	   * Creates center column element
	   * @static
	   * @return {HTMLElement}
	   */


	  static createCenter() {
	    return main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-link-center"
	      }
	    });
	  }
	  /**
	   * Creates left column element
	   * @static
	   * @return {HTMLElement}
	   */


	  static createLeft() {
	    return main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-link-left"
	      }
	    });
	  }
	  /**
	   * Creates right column element
	   * @return {HTMLElement}
	   */


	  static createRight() {
	    return main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-link-right"
	      }
	    });
	  }

	  hideElement(element) {
	    element.hidden = true;
	  }

	  showElement(element) {
	    element.hidden = false;
	  }

	  createTargetInput(data) {
	    const title = data.title || '';
	    const items = data.items || {};
	    return new BX.Landing.UI.Field.DropdownInline({
	      title: title,
	      selector: this.selector,
	      className: "landing-ui-field-dropdown-inline",
	      content: this.content.target,
	      contentRoot: this.contentRoot,
	      items: items,
	      onValueChange: function () {
	        this.onValueChangeHandler(this);
	        const event = new BX.Event.BaseEvent({
	          data: {
	            value: this.getValue()
	          },
	          compatData: [this.getValue()]
	        });
	        this.emit('change', event);
	      }.bind(this)
	    });
	  }

	  adjustEditLink() {
	    const type = this.hrefInput.getPlaceholderType();
	    const pageType = BX.Landing.Env.getInstance().getType();

	    if (type === "PAGE" && pageType !== "KNOWLEDGE" && pageType !== "GROUP") {
	      const value = this.hrefInput.getValue();

	      if (main_core.Type.isString(value) && value.length > 0) {
	        this.hrefInput.getPageData(value).then(function (result) {
	          const urlMask = BX.Landing.Main.getInstance().options.params.sef_url.landing_view;
	          const href = urlMask.replace("#site_show#", result.siteId).replace("#landing_edit#", result.id);
	          [].slice.call(this.layout.querySelectorAll('.landing-ui-field-edit-link')).forEach(BX.remove);
	          this.editLink = this.createEditLink(BX.Landing.Loc.getMessage("LANDING_LINK_FILED__EDIT_PAGE_LINK_LABEL"), href);
	          main_core.Dom.append(this.editLink, this.layout);
	        }.bind(this));
	      }
	    }
	  }

	  createEditLink(text, href) {
	    return main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-edit-link"
	      },
	      children: [main_core.Dom.create("a", {
	        attrs: {
	          href: href,
	          target: Link.TARGET_BLANK,
	          title: BX.Landing.Loc.getMessage("LANDING_LINK_FILED__EDIT_LINK_TITLE")
	        },
	        text: text
	      })]
	    });
	  }
	  /**
	   * @inheritDoc
	   * @return {boolean}
	   */


	  isChanged() {
	    const isChanged = JSON.stringify(this.content) !== JSON.stringify(this.getValue());

	    if (isChanged) {
	      this.prepareHrefInput();
	      this.prepareTargetInput();
	    }

	    return isChanged;
	  }
	  /**
	   * Checks that node contains image
	   * @return {boolean}
	   */


	  containsImage() {
	    return !!main_core.Dom.create("div", {
	      html: this.content.text
	    }).querySelector("img");
	  }
	  /**
	   * @return {boolean}
	   */


	  containsHtml() {
	    const element = BX.Landing.Utils.htmlToElement(this.content.text);
	    return !!element && !element.matches("br");
	  }
	  /**
	   * Gets value
	   * @return {{text: (*|string), href: (*|string), target: (*|string)}}
	   */


	  getValue() {
	    const value = {
	      text: BX.Landing.Utils.decodeDataValue(BX.Landing.Utils.trim(this.input.getValue().replace(/&nbsp;/g, ' '))),
	      href: BX.Landing.Utils.trim(this.hrefInput.getValue()),
	      target: this.prepareTarget(BX.Landing.Utils.trim(this.targetInput.getValue()))
	    };

	    if (this.isAvailableMedia() && this.mediaService) {
	      value.attrs = {
	        "data-url": BX.Landing.Utils.trim(this.mediaService.getEmbedURL())
	      };
	    }

	    if (this.hrefInput.getDynamic()) {
	      if (!main_core.Type.isPlainObject(value.attrs)) {
	        value.attrs = {};
	      }

	      if (this.hrefInput.input.firstElementChild) {
	        value.attrs["data-url"] = this.hrefInput.input.firstElementChild.getAttribute("data-url");
	      }

	      value.attrs["data-dynamic"] = this.hrefInput.getDynamic();
	    }

	    if (this.skipContent) {
	      delete value['text'];
	    }

	    if (value.href.startsWith('selectActions:')) {
	      value.href = '#';
	    }

	    return value;
	  }

	  setValue(value) {
	    if (main_core.Type.isPlainObject(value)) {
	      this.input.setValue(BX.Landing.Utils.escapeText(value.text));
	      this.hrefInput.setValue(value.href);
	      this.targetInput.setValue(BX.Landing.Utils.escapeText(value.target));
	    }

	    this.adjustEditLink();
	    this.adjustTarget();
	  }

	  adjustTarget() {
	    if (!this.isAvailableMedia()) {
	      const type = BX.Landing.Env.getInstance().getType();
	      const value = this.getValue();
	      this.targetInput.enable();

	      if (type === 'KNOWLEDGE' || type === 'GROUP') {
	        this.targetInput.disable();
	        const hrefType = this.hrefInput.getSelectedHrefType();

	        if (hrefType === 'page:' || hrefType === 'block:' || hrefType === 'form:' || hrefType === 'user:' // #landing123 || #block123 || #myAnchor
	        || /^#(\w+)([0-9])$/.test(value.href)) {
	          this.targetInput.setValue(Link.TARGET_SELF);
	        } else {
	          this.targetInput.setValue(Link.TARGET_BLANK);
	        }
	      } else {
	        if (value.href.startsWith('#crmFormPopup')) {
	          this.targetInput.disable();
	        }

	        if (value.href.startsWith('#crmPhone')) {
	          this.targetInput.disable();
	        }
	      }
	    }
	  }

	  enableMedia() {
	    this.readyToSave = true;

	    if (!this.mediaService.isDataLoaded) {
	      this.readyToSave = false;
	      BX.addCustomEvent(this.mediaService, 'onDataLoaded', () => {
	        this.readyToSave = true;
	        this.emit('onChangeReadyToSave');
	      });
	    }

	    this.emit('onChangeReadyToSave');
	    this.showMediaPreview();
	  }

	  disableMedia() {
	    if (!this.readyToSave) {
	      this.readyToSave = true;
	      this.emit('onChangeReadyToSave');
	    }

	    this.hideMediaPreview();
	    this.hideMediaSettings();
	  }

	  showMediaSettings() {
	    if (this.isAvailableMedia()) {
	      this.hideMediaSettings();
	      this.mediaSettings = this.mediaService.getSettingsForm();

	      if (this.mediaSettings) {
	        main_core.Dom.append(this.mediaSettings.layout, this.mediaLayout);
	      }
	    }
	  }

	  hideMediaSettings() {
	    if (this.mediaSettings) {
	      main_core.Dom.remove(this.mediaSettings.layout);
	    }
	  }
	  /**
	   * Checks that media is available
	   * @return {boolean}
	   */


	  isAvailableMedia() {
	    const ServiceFactory = new BX.Landing.MediaService.Factory();
	    return !!ServiceFactory.getRelevantClass(this.hrefInput.getValue());
	  }

	  showMediaPreview() {
	    // Make and show loader
	    const loader = new BX.Loader({
	      target: this.mediaLayout,
	      mode: "inline",
	      offset: {
	        top: "calc(50% - 55px)",
	        left: "calc(50% - 55px)"
	      }
	    });
	    this.video = loader.layout;
	    loader.show();
	    return this.mediaService.getURLPreviewElement().then(function (element) {
	      // Remove loader
	      main_core.Dom.remove(this.video);
	      loader.hide(); // Make and show URL preview

	      this.video = element;
	      main_core.Dom.append(this.video, this.mediaLayout);
	      this.targetValueBeforeAutochange = this.targetInput.getValue();

	      if (main_core.Type.isUndefined(this.selectedTargetValueByUser)) {
	        this.targetInput.setValue('_popup');
	      }

	      this.showMediaSettings();
	    }.bind(this), function () {
	      this.hideMediaSettings();
	      main_core.Dom.remove(this.video);
	    }.bind(this));
	  }

	  hideMediaPreview() {
	    if (main_core.Type.isUndefined(this.selectedTargetValueByUser)) {
	      this.targetInput.setValue(this.targetValueBeforeAutochange);
	    }

	    if (this.video) {
	      main_core.Dom.remove(this.video);
	    }
	  }

	  adjustVideo() {
	    const pageType = BX.Landing.Env.getInstance().getType();

	    if (pageType !== 'KNOWLEDGE' && pageType !== 'GROUP') {
	      const embedURL = "attrs" in this.content && "data-url" in this.content.attrs ? this.content.attrs["data-url"] : "";
	      const ServiceFactory = new BX.Landing.MediaService.Factory();
	      this.mediaService = ServiceFactory.create(this.hrefInput.getValue(), BX.Landing.Utils.getQueryParams(embedURL));

	      if (this.mediaService) {
	        this.disableMedia();

	        if (this.isAvailableMedia()) {
	          this.enableMedia();
	        }
	      } else {
	        this.disableMedia();
	      }
	    }
	  }

	  onHrefInput() {
	    const selectedHrefType = this.hrefInput.getSelectedHrefType();
	    const typeData = this.hrefInput.getTypeData(selectedHrefType);

	    if (typeData.hasOwnProperty('validate')) ; //when type === TYPE_HREF_LINK


	    if (selectedHrefType === '') {
	      this.adjustVideo();
	    }

	    this.adjustEditLink();
	    this.adjustTarget();
	  }

	  checkVisibleMediaPanel(hrefType, targetType) {
	    if (hrefType === '' && targetType === '_popup') {
	      this.showMediaPanel();
	    } else {
	      this.hideMediaPanel();
	    }
	  }

	  showMediaPanel() {
	    this.mediaLayout.hidden = false;
	  }

	  hideMediaPanel() {
	    this.mediaLayout.hidden = true;
	  }

	  prepareHrefInput() {
	    if (this.hrefInput.getValue() === '' || this.hrefInput.getValue() === '#') {
	      this.hrefInput.setHrefTypeSwitcherValue(this.hrefInput.typeHrefs.start);
	    }
	  }

	  prepareTargetInput() {
	    if (this.hrefInput.getSelectedHrefType() === this.hrefInput.typeHrefs.user) {
	      this.targetInput.setValue(Link.TARGET_BLANK);
	    }

	    if (this.hrefInput.getSelectedHrefType() === this.hrefInput.typeHrefs.start) {
	      this.targetInput.setValue(Link.TARGET_SELF);
	    }
	  }

	  prepareGridCenter(selectedHrefType) {
	    const typesWithoutManualInput = [this.hrefInput.typeHrefs.block, this.hrefInput.typeHrefs.page, this.hrefInput.typeHrefs.form, this.hrefInput.typeHrefs.product, this.hrefInput.typeHrefs.file, this.hrefInput.typeHrefs.user];
	    main_core.Dom.removeClass(this.hrefInput.gridCenterCell, "--not-empty");

	    if (typesWithoutManualInput.includes(selectedHrefType)) {
	      main_core.Dom.addClass(this.hrefInput.gridCenterCell, "--not-manual-input");
	    } else {
	      main_core.Dom.removeClass(this.hrefInput.gridCenterCell, "--not-manual-input");
	    }
	  }

	  prepareTarget(target) {
	    if (this.hrefInput.getSelectedHrefType() === this.hrefInput.typeHrefs.user) {
	      target = Link.TARGET_BLANK;
	    }

	    return target;
	  }

	}
	Link.TARGET_SELF = '_self';
	Link.TARGET_BLANK = '_blank';

	exports.Link = Link;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX));
//# sourceMappingURL=link.bundle.js.map
