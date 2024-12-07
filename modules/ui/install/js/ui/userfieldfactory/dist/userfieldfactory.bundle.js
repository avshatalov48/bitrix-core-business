/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_popup,main_core,ui_userfield) {
	'use strict';

	const MAX_FIELD_LENGTH = 50;

	/**
	 * @memberof BX.UI.UserFieldFactory
	 */
	class FieldTypes {
	  static getTypes() {
	    return Object.freeze({
	      string: 'string',
	      enumeration: 'enumeration',
	      date: 'date',
	      datetime: 'datetime',
	      address: 'address',
	      url: 'url',
	      file: 'file',
	      money: 'money',
	      boolean: 'boolean',
	      double: 'double',
	      employee: 'employee',
	      crm: 'crm',
	      crmStatus: 'crm_status'
	    });
	  }
	  static getDescriptions() {
	    return Object.freeze({
	      string: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_STRING_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_STRING_LEGEND"),
	        defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_STRING_LABEL')
	      },
	      enumeration: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ENUM_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ENUM_LEGEND"),
	        defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENUMERATION_LABEL')
	      },
	      date: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATE_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATE_LEGEND"),
	        defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_DATE_LABEL')
	      },
	      datetime: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_LEGEND"),
	        defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_DATETIME_LABEL')
	      },
	      address: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_TITLE_2"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_LEGEND_2")
	      },
	      url: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_URL_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_URL_LEGEND")
	      },
	      file: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_FILE_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_FILE_LEGEND"),
	        defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_FILE_LABEL')
	      },
	      money: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_MONEY_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_MONEY_LEGEND"),
	        defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_MONEY_LABEL')
	      },
	      boolean: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_BOOLEAN_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_BOOLEAN_LEGEND")
	      },
	      double: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_LEGEND"),
	        defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_DOUBLE_LABEL')
	      },
	      employee: {
	        title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_TITLE"),
	        description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_LEGEND")
	      }
	    });
	  }
	  static getCustomTypeDescription() {
	    return Object.freeze({
	      name: 'custom',
	      title: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_CUSTOM_TITLE'),
	      description: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_CUSTOM_LEGEND')
	    });
	  }
	}
	const DefaultData = Object.freeze({
	  multiple: 'N',
	  mandatory: 'N',
	  userTypeId: FieldTypes.string,
	  showFilter: 'E',
	  showInList: 'Y',
	  settings: {},
	  isSearchable: 'N'
	});
	const DefaultFieldData = Object.freeze({
	  file: {
	    showFilter: 'N',
	    showInList: 'N'
	  },
	  employee: {
	    showFilter: 'I'
	  },
	  crm: {
	    showFilter: 'I'
	  },
	  crm_status: {
	    showFilter: 'I'
	  },
	  enumeration: {
	    settings: {
	      DISPLAY: 'UI'
	    }
	  },
	  double: {
	    settings: {
	      PRECISION: 2
	    }
	  }
	});

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	const SCROLL_OFFSET = 3;

	/**
	 * @memberof BX.UI.UserFieldFactory
	 */
	var _enableScrollToBottom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableScrollToBottom");
	var _enableScrollToTop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableScrollToTop");
	class CreationMenu {
	  constructor(id, types, params) {
	    Object.defineProperty(this, _enableScrollToBottom, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _enableScrollToTop, {
	      writable: true,
	      value: void 0
	    });
	    this.id = id;
	    this.items = types;
	    this.params = {};
	    if (main_core.Type.isPlainObject(params)) {
	      this.params = params;
	    }
	  }
	  getId() {
	    if (!this.id) {
	      return 'ui-user-field-factory-menu';
	    }
	    return this.id;
	  }
	  getPopup(onItemClick = null) {
	    if (!this.popup) {
	      let options = {
	        ...CreationMenu.getDefaultPopupOptions(),
	        ...this.params
	      };
	      options.events = {
	        onPopupShow: this.onPopupShow.bind(this),
	        onPopupDestroy: this.onPopupDestroy.bind(this)
	      };
	      options.id = this.getId();
	      this.popup = new main_popup.Popup(options);
	    }
	    this.popup.setContent(this.render(onItemClick));
	    return this.popup;
	  }
	  static getDefaultPopupOptions() {
	    return {
	      autoHide: true,
	      draggable: false,
	      offsetLeft: 0,
	      offsetTop: 0,
	      noAllPaddings: true,
	      bindOptions: {
	        forceBindPosition: true
	      },
	      closeByEsc: true,
	      cacheable: false
	    };
	  }
	  open(callback) {
	    const popup = this.getPopup(callback);
	    if (!popup.isShown()) {
	      popup.show();
	    }
	  }
	  render(onItemClick) {
	    if (!this.container) {
	      this.container = main_core.Tag.render(_t || (_t = _`<div class="ui-userfieldfactory-creation-menu-container"></div>`));
	      const scrollIcon = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"42\" height=\"13\" viewBox=\"0 0 42 13\">\n" + "  <polyline fill=\"none\" stroke=\"#CACDD1\" stroke-width=\"2\" points=\"274 98 284 78.614 274 59\" transform=\"rotate(90 186 -86.5)\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" + "</svg>\n";
	      this.topScrollButton = main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-userfieldfactory-creation-menu-scroll-top">${0}</div>`), scrollIcon);
	      this.bottomScrollButton = main_core.Tag.render(_t3 || (_t3 = _`<div class="ui-userfieldfactory-creation-menu-scroll-bottom">${0}</div>`), scrollIcon);
	      this.container.appendChild(this.topScrollButton);
	      this.container.appendChild(this.bottomScrollButton);
	      this.container.appendChild(this.renderList(onItemClick));
	    }
	    return this.container;
	  }
	  renderList(onItemClick) {
	    if (!this.containerList) {
	      this.containerList = main_core.Tag.render(_t4 || (_t4 = _`<div class="ui-userfieldfactory-creation-menu-list"></div>`));
	      this.items.forEach(item => {
	        this.containerList.appendChild(this.renderItem(item, onItemClick));
	      });
	    }
	    return this.containerList;
	  }
	  renderItem(item, onClick) {
	    return main_core.Tag.render(_t5 || (_t5 = _`<div class="ui-userfieldfactory-creation-menu-item" onclick="${0}">
			<div class="ui-userfieldfactory-creation-menu-item-title">${0}</div>
			<div class="ui-userfieldfactory-creation-menu-item-desc">${0}</div>
		</div>`), () => {
	      this.handleItemClick(item, onClick);
	    }, item.title, item.description);
	  }
	  handleItemClick(item, onClick) {
	    if (main_core.Type.isFunction(item.onClick)) {
	      item.onClick(item.name);
	    } else if (main_core.Type.isFunction(onClick)) {
	      onClick(item.name);
	    }
	    this.getPopup().close();
	  }
	  onPopupShow() {
	    main_core.Event.bind(this.bottomScrollButton, "mouseover", this.onBottomButtonMouseOver.bind(this));
	    main_core.Event.bind(this.bottomScrollButton, "mouseout", this.onBottomButtonMouseOut.bind(this));
	    main_core.Event.bind(this.topScrollButton, "mouseover", this.onTopButtonMouseOver.bind(this));
	    main_core.Event.bind(this.topScrollButton, "mouseout", this.onTopButtonMouseOut.bind(this));
	    main_core.Event.bind(this.containerList, "scroll", this.onScroll.bind(this));
	    window.setTimeout(this.adjust.bind(this), 100);
	  }
	  onPopupDestroy() {
	    main_core.Event.unbind(this.bottomScrollButton, "mouseover", this.onBottomButtonMouseOver.bind(this));
	    main_core.Event.unbind(this.bottomScrollButton, "mouseout", this.onBottomButtonMouseOut.bind(this));
	    main_core.Event.unbind(this.topScrollButton, "mouseover", this.onTopButtonMouseOver.bind(this));
	    main_core.Event.unbind(this.topScrollButton, "mouseout", this.onTopButtonMouseOut.bind(this));
	    main_core.Event.unbind(this.containerList, "scroll", this.onScroll.bind(this));
	    this.container = null;
	    this.containerList = null;
	    this.topScrollButton = null;
	    this.bottomScrollButton = null;
	    this.popup = null;
	  }
	  onBottomButtonMouseOver() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToBottom)[_enableScrollToBottom]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToBottom)[_enableScrollToBottom] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToTop)[_enableScrollToTop] = false;
	    (function scroll() {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToBottom)[_enableScrollToBottom]) {
	        return;
	      }
	      if (this.containerList.scrollTop + this.containerList.offsetHeight !== this.containerList.scrollHeight) {
	        this.containerList.scrollTop += SCROLL_OFFSET;
	      }
	      if (this.containerList.scrollTop + this.containerList.offsetHeight === this.containerList.scrollHeight) {
	        babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToBottom)[_enableScrollToBottom] = false;
	      } else {
	        window.setTimeout(scroll.bind(this), 20);
	      }
	    }).bind(this)();
	  }
	  onBottomButtonMouseOut() {
	    babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToBottom)[_enableScrollToBottom] = false;
	  }
	  onTopButtonMouseOver() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToTop)[_enableScrollToTop]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToBottom)[_enableScrollToBottom] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToTop)[_enableScrollToTop] = true;
	    (function scroll() {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToTop)[_enableScrollToTop]) {
	        return;
	      }
	      if (this.containerList.scrollTop > 0) {
	        this.containerList.scrollTop -= SCROLL_OFFSET;
	      }
	      if (this.containerList.scrollTop === 0) {
	        babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToTop)[_enableScrollToTop] = false;
	      } else {
	        window.setTimeout(scroll.bind(this), 20);
	      }
	    }).bind(this)();
	  }
	  onTopButtonMouseOut() {
	    babelHelpers.classPrivateFieldLooseBase(this, _enableScrollToTop)[_enableScrollToTop] = false;
	  }
	  onScroll() {
	    this.adjust();
	  }
	  adjust() {
	    const height = this.containerList.offsetHeight;
	    const scrollTop = this.containerList.scrollTop;
	    const scrollHeight = this.containerList.scrollHeight;
	    if (scrollTop === 0) {
	      main_core.Dom.hide(this.topScrollButton);
	    } else {
	      main_core.Dom.show(this.topScrollButton);
	    }
	    if (scrollTop + height === scrollHeight) {
	      main_core.Dom.hide(this.bottomScrollButton);
	    } else {
	      main_core.Dom.show(this.bottomScrollButton);
	    }
	  }
	}

	/**
	 * @memberof BX.UI.UserFieldFactory
	 */
	class EnumItem {
	  constructor(value = null, id = null) {
	    this.value = value;
	    this.id = id;
	  }
	  setNode(node) {
	    this.node = node;
	  }
	  getId() {
	    return this.id;
	  }
	  getNode() {
	    return this.node;
	  }
	  getInput() {
	    const node = this.getNode();
	    if (!node) {
	      return null;
	    }
	    if (node instanceof HTMLInputElement) {
	      return node;
	    }
	    return node.querySelector('input');
	  }
	  getValue() {
	    const input = this.getInput();
	    if (input && input.value) {
	      return input.value;
	    }
	    return this.value || '';
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19;

	/**
	 * @memberof BX.UI.UserFieldFactory
	 */
	class Configurator {
	  constructor(params) {
	    if (main_core.Type.isPlainObject(params)) {
	      if (params.userField) {
	        this.userField = params.userField;
	      }
	      if (main_core.Type.isFunction(params.onSave)) {
	        this.onSave = params.onSave;
	      }
	      if (main_core.Type.isFunction(params.onCancel)) {
	        this.onCancel = params.onCancel;
	      }
	      this.canMultipleFields = true;
	      if (main_core.Type.isBoolean(params.canMultipleFields)) {
	        this.canMultipleFields = params.canMultipleFields;
	      }
	      this.canRequiredFields = true;
	      if (main_core.Type.isBoolean(params.canRequiredFields)) {
	        this.canRequiredFields = params.canRequiredFields;
	      }
	    }
	    this.enumItems = new Set();
	  }
	  render() {
	    this.node = main_core.Tag.render(_t$1 || (_t$1 = _$1`<div class="ui-userfieldfactory-configurator"></div>`));
	    this.labelInput = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<input class="ui-ctl-element" type="text" placeholder="${0}" />`), main_core.Text.encode(this.userField.getTitle()));
	    this.node.appendChild(main_core.Tag.render(_t3$1 || (_t3$1 = _$1`<div class="ui-userfieldfactory-configurator-block">
			<div class="ui-userfieldfactory-configurator-title">
				<span class="ui-userfieldfactory-configurator-title-text">${0}</span>
			</div>
			<div class="ui-userfieldfactory-configurator-content">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					${0}
				</div>
			</div>
		</div>`), main_core.Loc.getMessage('UI_USERFIELD_FACTORY_CONFIGURATOR_FIELD_TITLE'), this.labelInput));
	    if (this.userField.getUserTypeId() === FieldTypes.getTypes().enumeration) {
	      this.node.appendChild(this.renderEnumeration());
	    }
	    this.node.appendChild(this.renderOptions());
	    const save = event => {
	      event.preventDefault();
	      if (main_core.Type.isFunction(this.onSave)) {
	        this.onSave(this.saveField());
	      }
	    };
	    const cancel = event => {
	      event.preventDefault();
	      if (main_core.Type.isFunction(this.onCancel)) {
	        this.onCancel();
	      } else {
	        this.node.style.display = 'none';
	      }
	    };
	    this.saveButton = main_core.Tag.render(_t4$1 || (_t4$1 = _$1`<span class="ui-btn ui-btn-primary" onclick="${0}">${0}</span>`), save.bind(this), main_core.Loc.getMessage('UI_USERFIELD_SAVE'));
	    this.cancelButton = main_core.Tag.render(_t5$1 || (_t5$1 = _$1`<span class="ui-btn ui-btn-light-border" onclick="${0}">${0}</span>`), cancel.bind(this), main_core.Loc.getMessage('UI_USERFIELD_CANCEL'));
	    this.node.appendChild(main_core.Tag.render(_t6 || (_t6 = _$1`<div class="ui-userfieldfactory-configurator-block">
			${0}${0}
		</div>`), this.saveButton, this.cancelButton));
	    return this.node;
	  }
	  saveField() {
	    if (this.timeCheckbox) {
	      if (this.timeCheckbox.checked) {
	        this.userField.setUserTypeId(FieldTypes.getTypes().datetime);
	      } else {
	        this.userField.setUserTypeId(FieldTypes.getTypes().date);
	      }
	    }
	    if (this.multipleCheckbox) {
	      this.userField.setIsMultiple(this.multipleCheckbox.checked);
	    }
	    if (this.mandatoryCheckbox) {
	      this.userField.setIsMandatory(this.mandatoryCheckbox.checked);
	    }
	    this.userField.setTitle(this.labelInput.value);
	    this.saveEnumeration(this.userField, this.enumItems);
	    return this.userField;
	  }
	  renderEnumeration() {
	    this.enumItemsContainer = main_core.Tag.render(_t7 || (_t7 = _$1`<div class="ui-userfieldfactory-configurator-block"></div>`));
	    this.enumAddItemContainer = main_core.Tag.render(_t8 || (_t8 = _$1`<div class="ui-userfieldfactory-configurator-block-add-field">
			<span class="ui-userfieldfactory-configurator-add-button" onclick="${0}">${0}</span>
		</div>`), () => {
	      this.addEnumInput().focus();
	    }, main_core.Loc.getMessage('UI_USERFIELD_ADD'));
	    this.enumContainer = main_core.Tag.render(_t9 || (_t9 = _$1`<div class="ui-userfieldfactory-configurator-block">
			<div class="ui-userfieldfactory-configurator-title">
				<span class="ui-userfieldfactory-configurator-title-text">${0}</span>
			</div>
			${0}
			${0}
		</div>`), main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENUM_ITEMS'), this.enumItemsContainer, this.enumAddItemContainer);
	    this.userField.getEnumeration().forEach(item => {
	      this.addEnumInput(item);
	    });
	    this.addEnumInput();
	    return this.enumContainer;
	  }
	  addEnumInput(item) {
	    let enumItem;
	    if (main_core.Type.isPlainObject(item)) {
	      enumItem = new EnumItem(item.value, item.id);
	    } else {
	      enumItem = new EnumItem();
	    }
	    const node = main_core.Tag.render(_t10 || (_t10 = _$1`<div style="margin-bottom: 10px;" class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row">
			<input class="ui-ctl-element" type="text" value="${0}">
			<div class="ui-userfieldfactory-configurator-remove-enum" onclick="${0}"></div>
		</div>`), main_core.Text.encode(enumItem.getValue()), event => {
	      event.preventDefault();
	      this.deleteEnumItem(enumItem);
	    });
	    enumItem.setNode(node);
	    this.enumItems.add(enumItem);
	    this.enumItemsContainer.appendChild(node);
	    return node;
	  }
	  deleteEnumItem(item) {
	    this.enumItemsContainer.removeChild(item.getNode());
	    this.enumItems.delete(item);
	  }
	  renderOptions() {
	    this.optionsContainer = main_core.Tag.render(_t11 || (_t11 = _$1`<div class="ui-userfieldfactory-configurator-block"></div>`));
	    if (this.canRequiredFields) {
	      this.mandatoryCheckbox = main_core.Tag.render(_t12 || (_t12 = _$1`<input class="ui-ctl-element" type="checkbox">`));
	      this.mandatoryCheckbox.checked = this.userField.isMandatory();
	      this.optionsContainer.appendChild(main_core.Tag.render(_t13 || (_t13 = _$1`<div>
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${0}
					<div class="ui-ctl-label-text">${0}</div>
				</label>
			</div>`), this.mandatoryCheckbox, main_core.Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_REQUIRED')));
	    }
	    if (!this.userField.isSaved() && (this.userField.getUserTypeId() === FieldTypes.getTypes().date || this.userField.getUserTypeId() === FieldTypes.getTypes().datetime)) {
	      this.timeCheckbox = main_core.Tag.render(_t14 || (_t14 = _$1`<input class="ui-ctl-element" type="checkbox">`));
	      this.timeCheckbox.checked = this.userField.getUserTypeId() === FieldTypes.getTypes().datetime;
	      const label = main_core.Tag.render(_t15 || (_t15 = _$1`
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${0}
				</label>
			`), this.timeCheckbox);
	      if (this.userField.getUserTypeId() === FieldTypes.getTypes().datetime) {
	        main_core.Dom.append(main_core.Tag.render(_t16 || (_t16 = _$1`<div className="ui-ctl-label-text">${0}</div>`), main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENABLE_TIME')), label);
	      }
	      this.optionsContainer.appendChild(main_core.Tag.render(_t17 || (_t17 = _$1`<div>
				
			</div>`)));
	    }
	    if (!this.userField.isSaved() && this.userField.getUserTypeId() !== FieldTypes.getTypes().boolean && this.canMultipleFields) {
	      this.multipleCheckbox = main_core.Tag.render(_t18 || (_t18 = _$1`<input class="ui-ctl-element" type="checkbox">`));
	      this.multipleCheckbox.checked = this.userField.isMultiple();
	      this.optionsContainer.appendChild(main_core.Tag.render(_t19 || (_t19 = _$1`<div>
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
					${0}
					<div class="ui-ctl-label-text">${0}</div>
				</label>
			</div>`), this.multipleCheckbox, main_core.Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_MULTIPLE')));
	    }
	    return this.optionsContainer;
	  }
	  saveEnumeration(userField, enumItems) {
	    const items = [];
	    let sort = 100;
	    enumItems.forEach(item => {
	      items.push({
	        value: item.getValue(),
	        sort: sort,
	        id: item.getId()
	      });
	      sort += 100;
	    });
	    userField.setEnumeration(items);
	  }
	}

	/**
	 * @memberof BX.UI.UserFieldFactory
	 * @mixes EventEmitter
	 */
	class Factory {
	  constructor(entityId, params = {}) {
	    main_core_events.EventEmitter.makeObservable(this, 'BX.UI.UserFieldFactory.Factory');
	    this.configuratorClass = Configurator;
	    if (main_core.Type.isString(entityId) && entityId.length > 0) {
	      this.entityId = entityId;
	    }
	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isString(params.menuId)) {
	        this.menuId = params.menuId;
	      }
	      if (!main_core.Type.isArray(params.types)) {
	        params.types = [];
	      }
	      if (main_core.Type.isDomNode(params.bindElement)) {
	        this.bindElement = params.bindElement;
	      }
	      this.moduleId = params.moduleId;
	      this.setCustomTypesUrl(params.customTypesUrl).setConfiguratorClass(params.configuratorClass);
	    } else {
	      params.types = [];
	    }
	    this.types = this.getFieldTypes().concat(params.types);
	  }
	  getFieldTypes() {
	    const types = [];
	    Object.keys(FieldTypes.getDescriptions()).forEach(name => {
	      types.push({
	        ...FieldTypes.getDescriptions()[name],
	        ...{
	          name
	        }
	      });
	    });
	    this.emit('OnGetUserTypes', {
	      types
	    });
	    return types;
	  }
	  getMenu(params) {
	    if (!main_core.Type.isPlainObject(params)) {
	      params = {};
	    }
	    if (!main_core.Type.isDomNode(params.bindElement)) {
	      params.bindElement = this.bindElement;
	    }
	    const types = this.types;
	    if (this.customTypesUrl && !this.isCustomTypeAdded) {
	      const customType = {
	        ...FieldTypes.getCustomTypeDescription()
	      };
	      customType.onClick = this.onCustomTypeClick.bind(this);
	      types.push(customType);
	      this.isCustomTypeAdded = true;
	    }
	    if (!this.menu) {
	      this.menu = new CreationMenu(this.menuId, types, params);
	    }
	    return this.menu;
	  }
	  setConfiguratorClass(configuratorClassName) {
	    let configuratorClass = null;
	    if (main_core.Type.isString(configuratorClassName)) {
	      configuratorClass = main_core.Reflection.getClass(configuratorClassName);
	    } else if (main_core.Type.isFunction(configuratorClassName)) {
	      configuratorClass = configuratorClassName;
	    }
	    if (main_core.Type.isFunction(configuratorClass) && configuratorClass.prototype instanceof Configurator) {
	      this.configuratorClass = configuratorClass;
	    }
	  }
	  setCustomTypesUrl(customTypesUrl) {
	    this.customTypesUrl = customTypesUrl;
	    return this;
	  }
	  getConfigurator(params) {
	    return new this.configuratorClass(params);
	  }
	  createUserField(fieldType, fieldName) {
	    let data = {
	      ...DefaultData,
	      ...DefaultFieldData[fieldType],
	      ...{
	        userTypeId: fieldType
	      }
	    };
	    if (!main_core.Type.isString(fieldName) || fieldName.length <= 0 || fieldName.length > MAX_FIELD_LENGTH) {
	      fieldName = this.generateFieldName();
	    }
	    data.fieldName = fieldName;
	    data.entityId = this.entityId;
	    const userField = new ui_userfield.UserField(data, {
	      moduleId: this.moduleId
	    });
	    userField.setTitle(this.getDefaultLabel(fieldType));
	    this.emit('onCreateField', {
	      userField
	    });
	    return userField;
	  }
	  getDefaultLabel(fieldType) {
	    let label = main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_LABEL');
	    this.types.forEach(type => {
	      if (type.name === fieldType && main_core.Type.isString(type.defaultTitle)) {
	        label = type.defaultTitle;
	      }
	    });
	    return label;
	  }
	  generateFieldName() {
	    let name = 'UF_' + (this.entityId ? this.entityId + "_" : "");
	    let dateSuffix = new Date().getTime().toString();
	    if (name.length + dateSuffix.length > MAX_FIELD_LENGTH) {
	      dateSuffix = dateSuffix.substr(name.length + dateSuffix.length - MAX_FIELD_LENGTH);
	    }
	    name += dateSuffix;
	    return name;
	  }
	  onCustomTypeClick() {
	    if (!this.customTypesUrl) {
	      return;
	    }
	    BX.SidePanel.Instance.open(this.customTypesUrl.toString(), {
	      cacheable: false,
	      allowChangeHistory: false,
	      width: 900,
	      events: {
	        onClose: event => {
	          const slider = event.getSlider();
	          if (slider) {
	            const userFieldData = slider.getData().get('userFieldData');
	            if (userFieldData) {
	              const userField = ui_userfield.UserField.unserialize(userFieldData);
	              this.emit('onCreateCustomUserField', {
	                userField
	              });
	            }
	          }
	        }
	      }
	    });
	  }
	}

	exports.Factory = Factory;
	exports.FieldTypes = FieldTypes;
	exports.Configurator = Configurator;

}((this.BX.UI.UserFieldFactory = this.BX.UI.UserFieldFactory || {}),BX.Event,BX.Main,BX,BX.UI.UserField));
//# sourceMappingURL=userfieldfactory.bundle.js.map
