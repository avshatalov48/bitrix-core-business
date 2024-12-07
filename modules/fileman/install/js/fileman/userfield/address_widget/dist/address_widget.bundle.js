this.BX = this.BX || {};
this.BX.Fileman = this.BX.Fileman || {};
(function (exports,main_core_events,location_core,location_widget,main_core) {
	'use strict';

	var _wrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	class BaseView {
	  constructor(params) {
	    Object.defineProperty(this, _wrapper, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = params.wrapper;
	  }
	  getWrapper() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper];
	  }
	  layout() {
	    throw new Error('please implement the layout() method');
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12;
	var _widget = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("widget");
	var _nodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("nodes");
	var _wrapper$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	var _address = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("address");
	var _fieldName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fieldName");
	var _fieldFormName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fieldFormName");
	var _showMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showMap");
	var _enableRemoveButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableRemoveButton");
	var _isCompactMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCompactMode");
	var _initialAddressId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialAddressId");
	var _areDetailsShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("areDetailsShown");
	var _isLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoading");
	var _isDropdownLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDropdownLoading");
	var _isDestroyed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDestroyed");
	var _showDetailsToggle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showDetailsToggle");
	var _getAddressControlSwitchContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAddressControlSwitchContainer");
	class EditEntry extends main_core_events.EventEmitter {
	  constructor(props) {
	    var _props$showDetailsTog;
	    super();
	    Object.defineProperty(this, _getAddressControlSwitchContainer, {
	      value: _getAddressControlSwitchContainer2
	    });
	    Object.defineProperty(this, _widget, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _nodes, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _wrapper$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _address, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _fieldName, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fieldFormName, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _showMap, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _enableRemoveButton, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isCompactMode, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _initialAddressId, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _areDetailsShown, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isLoading, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isDropdownLoading, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isDestroyed, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _showDetailsToggle, {
	      writable: true,
	      value: true
	    });
	    this.setEventNamespace('Fileman.EditEntry');
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper$1)[_wrapper$1] = props.wrapper;
	    babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName] = props.fieldName;
	    babelHelpers.classPrivateFieldLooseBase(this, _fieldFormName)[_fieldFormName] = props.fieldFormName;
	    babelHelpers.classPrivateFieldLooseBase(this, _enableRemoveButton)[_enableRemoveButton] = props.enableRemoveButton;
	    babelHelpers.classPrivateFieldLooseBase(this, _initialAddressId)[_initialAddressId] = props.initialAddressId;
	    babelHelpers.classPrivateFieldLooseBase(this, _showMap)[_showMap] = props.showMap;
	    babelHelpers.classPrivateFieldLooseBase(this, _showDetailsToggle)[_showDetailsToggle] = Boolean((_props$showDetailsTog = props.showDetailsToggle) != null ? _props$showDetailsTog : true);
	    if (props.address) {
	      babelHelpers.classPrivateFieldLooseBase(this, _address)[_address] = props.address;
	    }
	    if (props.isCompactMode) {
	      babelHelpers.classPrivateFieldLooseBase(this, _isCompactMode)[_isCompactMode] = props.isCompactMode;
	    }
	  }
	  layout() {
	    const factory = new location_widget.Factory();
	    babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget] = factory.createAddressWidget({
	      address: babelHelpers.classPrivateFieldLooseBase(this, _address)[_address],
	      mode: location_core.ControlMode.edit,
	      popupOptions: {
	        offsetLeft: 14
	      },
	      popupBindOptions: {
	        forceBindPosition: true,
	        position: 'right'
	      },
	      mapBehavior: 'auto',
	      useFeatures: {
	        fields: true,
	        map: babelHelpers.classPrivateFieldLooseBase(this, _showMap)[_showMap],
	        autocomplete: true
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].userInput = main_core.Tag.render(_t || (_t = _`<input type="text" class="ui-ctl-element" />`));
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldsContainer = main_core.Tag.render(_t2 || (_t2 = _`<div class="location-fields-control-block"></div>`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _showDetailsToggle)[_showDetailsToggle]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].detailsToggle = main_core.Tag.render(_t3 || (_t3 = _`<span class="ui-link ui-link-secondary address-control-mode-switch">${0}</span>`), main_core.Loc.getMessage('ADDRESS_USERFIELD_DETAILS'));
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].detailsToggle, 'click', this.onDetailsToggleClick.bind(this));
	    }
	    let inputValue = this.getInitialAddressFieldValue();
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldValueInput = main_core.Tag.render(_t4 || (_t4 = _`<input type="hidden" name="${0}" value="${0}" />`), babelHelpers.classPrivateFieldLooseBase(this, _fieldFormName)[_fieldFormName], inputValue);
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon = main_core.Tag.render(_t5 || (_t5 = _`<button type="button" class="ui-ctl-after ui-ctl-icon-clear"></button>`));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon, 'click', this.onInputIconClick.bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget].subscribeOnAddressChangedEvent(this.onAddressChanged.bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget].subscribeOnStateChangedEvent(this.onWidgetStateChangedEvent.bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget].subscribeOnFeatureEvent(this.onFeatureEvent.bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].entryWrapper = main_core.Tag.render(_t6 || (_t6 = _`
			<div class="edit-entry-input-wrapper">
				<div class="fields address field-item edit ui-ctl ui-ctl-after-icon ${0}">
					${0}
					${0}
					${0}
				</div>
			</div>
		`), this.getUserInputSizeClass(), babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].userInput, babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldsContainer, babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon);

	    // a workaround for bizproc conditionals; their conditionals popup seems to use the topmost <input>'s value
	    const hiddenFormattedInputValue = babelHelpers.classPrivateFieldLooseBase(this, _address)[_address] ? this.getRawValueForHiddenFormattedInput(babelHelpers.classPrivateFieldLooseBase(this, _address)[_address]) : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].hiddenFormattedAddressInput = main_core.Tag.render(_t7 || (_t7 = _`<input type="hidden" name="${0}_formatted" value="${0}" />`), babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName], hiddenFormattedInputValue);

	    // a flag used to identify values set manually by the user
	    const manualEditFlagNode = main_core.Tag.render(_t8 || (_t8 = _`<input type="hidden" name="${0}_manual_edit" value="Y">`), babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName]);
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout = main_core.Tag.render(_t9 || (_t9 = _`
			<div class="edit-entry-layout-wrapper ${0}">
				${0}
				${0}
				${0}
				${0}
			</div>
		`), this.getLayoutSizeClass(), babelHelpers.classPrivateFieldLooseBase(this, _getAddressControlSwitchContainer)[_getAddressControlSwitchContainer](), babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].hiddenFormattedAddressInput, babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].entryWrapper, manualEditFlagNode);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _enableRemoveButton)[_enableRemoveButton]) {
	      main_core.Dom.append(this.getRemoveInputButton(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout), babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].entryWrapper);
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldValueInput, babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout);
	    babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget].render({
	      inputNode: babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].userInput,
	      mapBindElement: babelHelpers.classPrivateFieldLooseBase(this, _wrapper$1)[_wrapper$1],
	      controlWrapper: babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout,
	      fieldsContainer: babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldsContainer
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout;
	  }
	  getUserInputSizeClass() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isCompactMode)[_isCompactMode] ? 'ui-ctl-wd' : 'ui-ctl-w100';
	  }
	  getLayoutSizeClass() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isCompactMode)[_isCompactMode] ? 'compact' : '';
	  }
	  getRemoveInputButton(layout) {
	    const removeInputButton = main_core.Tag.render(_t10 || (_t10 = _`
			<span class="uf-address-search-input-remove"></span>
		`));
	    main_core.Event.bind(removeInputButton, 'click', event => {
	      this.emit(EditEntry.onRemoveInputButtonClickedEvent);
	    });
	    return removeInputButton;
	  }
	  destroy() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget].destroy();
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _address)[_address] && babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].id > 0) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout);
	      const input = main_core.Tag.render(_t11 || (_t11 = _`<input type="hidden" name="${0}" value="${0}_del" />`), babelHelpers.classPrivateFieldLooseBase(this, _fieldFormName)[_fieldFormName], babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].id);
	      main_core.Dom.append(input, babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout);
	      this.emitFieldChangedEvent();
	    } else {
	      main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].layout);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _isDestroyed)[_isDestroyed] = true;
	  }
	  isDestroyed() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isDestroyed)[_isDestroyed];
	  }
	  onAddressChanged(event) {
	    const initialAddressId = parseInt(babelHelpers.classPrivateFieldLooseBase(this, _initialAddressId)[_initialAddressId]);
	    /** @type {AddressEntity} */
	    const address = event.data.address;
	    if (!address) {
	      return;
	    }

	    // when we clear the input, the address' id becomes 0, and because of it a new address is created upon
	    // saving. we can set the address's id to the old id to edit the old address instead
	    if (initialAddressId && parseInt(address.id) !== initialAddressId) {
	      address.id = initialAddressId;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldValueInput.value = this.getChangedAddressFieldValue(address);
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].hiddenFormattedAddressInput.value = this.getRawValueForHiddenFormattedInput(address);
	    this.emitFieldChangedEvent();
	  }
	  onWidgetStateChangedEvent(event) {
	    const state = event.data.state;
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = state === location_widget.State.DATA_LOADING;
	    this.refreshInputIcon();
	  }
	  onFeatureEvent(event) {
	    if (event.data.feature instanceof location_widget.AutocompleteFeature) {
	      babelHelpers.classPrivateFieldLooseBase(this, _isDropdownLoading)[_isDropdownLoading] = event.data.eventCode === location_widget.AutocompleteFeature.searchStartedEvent;
	      this.refreshInputIcon();
	    }
	  }
	  isInputLoading() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || babelHelpers.classPrivateFieldLooseBase(this, _isDropdownLoading)[_isDropdownLoading];
	  }
	  refreshInputIcon() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon) {
	      return;
	    }
	    if (this.isInputLoading()) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon, 'ui-ctl-icon-clear');
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon, 'ui-ctl-icon-loader');
	    } else {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon, 'ui-ctl-icon-loader');
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].inputIcon, 'ui-ctl-icon-clear');
	    }
	  }
	  getInitialAddressFieldValue() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2;
	    let inputValue = '';
	    if (((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _address)[_address]) == null ? void 0 : _babelHelpers$classPr.id) == 0) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].location) {
	        // JSON has probably been passed as the component's value; we need to create a new address
	        inputValue = main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].toJson());
	      } else {
	        // for compatibility with the format used before the switch to location module's addresses
	        inputValue = `${babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].getFieldValue(location_core.AddressType.ADDRESS_LINE_2)}|${babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].latitude};${babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].longitude}`;
	      }
	    } else if (((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _address)[_address]) == null ? void 0 : _babelHelpers$classPr2.id) > 0) {
	      inputValue = `${this.getFormattedAddress(babelHelpers.classPrivateFieldLooseBase(this, _address)[_address])}|${babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].latitude};${babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].longitude}|${babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].id}`;
	    }
	    return inputValue;
	  }
	  getChangedAddressFieldValue(address) {
	    return address.toJson();
	  }
	  getFormattedAddress(address) {
	    var _address$toString;
	    const format = new location_core.Format(JSON.parse(BX.message('LOCATION_WIDGET_DEFAULT_FORMAT')));
	    return (_address$toString = address.toString(format, location_core.AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA)) != null ? _address$toString : '';
	  }
	  getRawValueForHiddenFormattedInput(address) {
	    const formattedAddress = this.getFormattedAddress(address);
	    if ((parseInt(address.latitude) !== 0 || parseInt(address.longitude) !== 0) && address.latitude !== '' && address.longitude !== '') {
	      return `${formattedAddress}|${address.latitude};${address.longitude}`;
	    }
	    return formattedAddress;
	  }
	  onInputIconClick() {
	    if (this.isInputLoading()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].userInput.focus();
	    babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget].resetView();
	    babelHelpers.classPrivateFieldLooseBase(this, _widget)[_widget].address = null;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _address)[_address] && babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].id > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldValueInput.value = babelHelpers.classPrivateFieldLooseBase(this, _address)[_address].id + '_del';
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldValueInput.value = '';
	    }
	    this.emitFieldChangedEvent();
	  }
	  onDetailsToggleClick() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldsContainer || !babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].detailsToggle) {
	      return;
	    }
	    const fieldsContainer = babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].fieldsContainer;
	    const detailsToggle = babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].detailsToggle;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _areDetailsShown)[_areDetailsShown] && main_core.Dom.hasClass(fieldsContainer, 'visible')) {
	      main_core.Dom.removeClass(fieldsContainer, 'visible');
	      detailsToggle.innerText = main_core.Loc.getMessage('ADDRESS_USERFIELD_DETAILS');
	    } else {
	      main_core.Dom.addClass(fieldsContainer, 'visible');
	      detailsToggle.innerText = main_core.Loc.getMessage('ADDRESS_USERFIELD_NO_DETAILS');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _areDetailsShown)[_areDetailsShown] = !babelHelpers.classPrivateFieldLooseBase(this, _areDetailsShown)[_areDetailsShown];
	  }
	  emitFieldChangedEvent() {
	    BX.onCustomEvent(window, 'onUIEntityEditorUserFieldExternalChanged', [babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName]]);
	    BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldExternalChanged', [babelHelpers.classPrivateFieldLooseBase(this, _fieldName)[_fieldName]]);
	  }
	}
	function _getAddressControlSwitchContainer2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _showDetailsToggle)[_showDetailsToggle]) {
	    return null;
	  }
	  return main_core.Tag.render(_t12 || (_t12 = _`
			<div class="address-control-mode-switch-wrapper">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _nodes)[_nodes].detailsToggle);
	}
	EditEntry.onRemoveInputButtonClickedEvent = 'onRemoveInputButtonClicked';

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1;
	var _fieldName$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fieldName");
	var _fieldFormName$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fieldFormName");
	var _addresses = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addresses");
	var _inputsWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputsWrapper");
	var _isMultiple = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMultiple");
	var _isCompactMode$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCompactMode");
	var _showMap$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showMap");
	var _showDetailsToggle$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showDetailsToggle");
	var _inputs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputs");
	class Edit extends BaseView {
	  constructor(params) {
	    var _params$showDetailsTo;
	    super(params);
	    Object.defineProperty(this, _fieldName$1, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _fieldFormName$1, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _addresses, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _inputsWrapper, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _isMultiple, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isCompactMode$1, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _showMap$1, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _showDetailsToggle$1, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _inputs, {
	      writable: true,
	      value: []
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _fieldName$1)[_fieldName$1] = params.fieldName;
	    babelHelpers.classPrivateFieldLooseBase(this, _fieldFormName$1)[_fieldFormName$1] = params.fieldFormName;
	    babelHelpers.classPrivateFieldLooseBase(this, _addresses)[_addresses] = params.addresses;
	    babelHelpers.classPrivateFieldLooseBase(this, _isMultiple)[_isMultiple] = params.isMultiple;
	    babelHelpers.classPrivateFieldLooseBase(this, _isCompactMode$1)[_isCompactMode$1] = params.compactMode;
	    babelHelpers.classPrivateFieldLooseBase(this, _showMap$1)[_showMap$1] = params.showMap;
	    babelHelpers.classPrivateFieldLooseBase(this, _showDetailsToggle$1)[_showDetailsToggle$1] = Boolean((_params$showDetailsTo = params.showDetailsToggle) != null ? _params$showDetailsTo : false);
	  }
	  layout() {
	    const layout = main_core.Tag.render(_t$1 || (_t$1 = _$1`<div class="address-edit-wrapper"></div>`));
	    const inputsWrapper = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<div class="address-inputs-wrapper"></div>`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _addresses)[_addresses].length > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _addresses)[_addresses].forEach(address => {
	        const input = this.createInputForAddress(address);
	        main_core.Dom.append(input.layout(), inputsWrapper);
	      });
	    } else {
	      const input = this.createInputForAddress();
	      main_core.Dom.append(input.layout(), inputsWrapper);
	    }
	    main_core.Dom.append(inputsWrapper, layout);
	    babelHelpers.classPrivateFieldLooseBase(this, _inputsWrapper)[_inputsWrapper] = inputsWrapper;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isMultiple)[_isMultiple]) {
	      const addInputElement = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`<input type="button" value="${0}" />`), main_core.Loc.getMessage('ADDRESS_USERFIELD_ADD_INPUT'));
	      main_core.Event.bind(addInputElement, 'click', this.addInput.bind(this));
	      main_core.Dom.append(addInputElement, layout);
	    }
	    main_core.Dom.append(layout, this.getWrapper());
	    return this.getWrapper();
	  }
	  addInput() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _inputsWrapper)[_inputsWrapper]) {
	      return;
	    }
	    const input = this.createInputForAddress();
	    main_core.Dom.append(input.layout(), babelHelpers.classPrivateFieldLooseBase(this, _inputsWrapper)[_inputsWrapper]);
	  }
	  createInputForAddress(address) {
	    var _parseInt;
	    const entry = new EditEntry({
	      wrapper: this.getWrapper(),
	      address: address,
	      fieldName: babelHelpers.classPrivateFieldLooseBase(this, _fieldName$1)[_fieldName$1],
	      fieldFormName: babelHelpers.classPrivateFieldLooseBase(this, _fieldFormName$1)[_fieldFormName$1],
	      enableRemoveButton: babelHelpers.classPrivateFieldLooseBase(this, _isMultiple)[_isMultiple],
	      initialAddressId: (_parseInt = parseInt(address == null ? void 0 : address.id)) != null ? _parseInt : null,
	      isCompactMode: babelHelpers.classPrivateFieldLooseBase(this, _isCompactMode$1)[_isCompactMode$1],
	      showMap: babelHelpers.classPrivateFieldLooseBase(this, _showMap$1)[_showMap$1],
	      showDetailsToggle: babelHelpers.classPrivateFieldLooseBase(this, _showDetailsToggle$1)[_showDetailsToggle$1]
	    });
	    main_core_events.EventEmitter.subscribe(entry, EditEntry.onRemoveInputButtonClickedEvent, this.removeInput.bind(this, entry));
	    babelHelpers.classPrivateFieldLooseBase(this, _inputs)[_inputs].push(entry);
	    return entry;
	  }
	  removeInput(input) {
	    const activeInputsCount = babelHelpers.classPrivateFieldLooseBase(this, _inputs)[_inputs].filter(input => {
	      return !input.isDestroyed();
	    }).length;
	    if (activeInputsCount > 1) {
	      input.destroy();
	    }
	  }
	}

	let _$2 = t => t,
	  _t$2;
	var _addresses$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addresses");
	var _widgets = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("widgets");
	class View extends BaseView {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _addresses$1, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _widgets, {
	      writable: true,
	      value: []
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addresses$1)[_addresses$1] = params.addresses;
	  }
	  destroyWidgets() {
	    babelHelpers.classPrivateFieldLooseBase(this, _widgets)[_widgets].forEach(widget => {
	      widget.destroy();
	    });
	  }
	  layout() {
	    const layout = new DocumentFragment();
	    babelHelpers.classPrivateFieldLooseBase(this, _addresses$1)[_addresses$1].forEach(address => {
	      layout.append(this.getLayoutForAddress(address));
	    });
	    main_core.Dom.append(layout, this.getWrapper());
	    return this.getWrapper();
	  }
	  getLayoutForAddress(address) {
	    const factory = new location_widget.Factory();
	    const widget = factory.createAddressWidget({
	      address: address,
	      mode: location_core.ControlMode.view,
	      popupOptions: {
	        offsetLeft: 14
	      },
	      popupBindOptions: {
	        forceBindPosition: true,
	        position: 'right'
	      },
	      mapBehavior: 'auto',
	      useFeatures: {
	        fields: false,
	        map: true,
	        autocomplete: false
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _widgets)[_widgets].push(widget);
	    const addressLayout = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<span class="fields address field-item view" data-id="${0}">
				<span class="ui-link ui-link-dark ui-link-dotted">${0}</span>
			</span>
		`), address.id, this.getFormattedAddress(address));
	    widget.render({
	      mapBindElement: addressLayout,
	      controlWrapper: addressLayout
	    });
	    return addressLayout;
	  }
	  getFormattedAddress(address) {
	    var _address$toString;
	    const format = new location_core.Format(JSON.parse(BX.message('LOCATION_WIDGET_DEFAULT_FORMAT')));
	    return (_address$toString = address.toString(format, location_core.AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA)) != null ? _address$toString : '';
	  }
	}

	var _mode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mode");
	var _wrapper$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	var _addresses$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addresses");
	var _isMultiple$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMultiple");
	var _showMap$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showMap");
	var _showDetailsToggle$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showDetailsToggle");
	var _fieldConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fieldConfig");
	var _additionalProperties = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("additionalProperties");
	class AddressField {
	  static init(params) {
	    var _params$showMap, _params$showDetailsTo;
	    const mode = params.mode;
	    const wrapper = document.getElementById(params.wrapperId);
	    if (!wrapper) {
	      return;
	    }
	    let addresses = [];
	    const addressData = params.addressData;
	    addressData.forEach(addressFields => {
	      if (main_core.Type.isObject(addressFields)) {
	        addresses.push(new location_core.Address(addressFields));
	      }
	    });
	    const showMap = (_params$showMap = params.showMap) != null ? _params$showMap : true;
	    const showDetailsToggle = Boolean((_params$showDetailsTo = params.showDetailsToggle) != null ? _params$showDetailsTo : true);
	    const addressFieldParams = {
	      addresses: addresses,
	      wrapper: wrapper,
	      mode: mode,
	      fieldConfig: {
	        fieldName: params.fieldName,
	        fieldFormName: params.fieldFormName
	      },
	      isMultiple: params.isMultiple,
	      showMap,
	      showDetailsToggle
	    };
	    if (params.additionalProperties) {
	      addressFieldParams.additionalProperties = params.additionalProperties;
	    }
	    const addressField = new AddressField(addressFieldParams);
	    addressField.layout();
	    main_core_events.EventEmitter.emit(this, 'BX.Fileman.UserField.AddressField:onInitiated', addressFieldParams);
	  }
	  constructor(params) {
	    var _params$showDetailsTo2;
	    Object.defineProperty(this, _mode, {
	      writable: true,
	      value: AddressField.VIEW_MODE
	    });
	    Object.defineProperty(this, _wrapper$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _addresses$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isMultiple$1, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _showMap$2, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _showDetailsToggle$2, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _fieldConfig, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _additionalProperties, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] = params.mode;
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper$2)[_wrapper$2] = params.wrapper;
	    babelHelpers.classPrivateFieldLooseBase(this, _addresses$2)[_addresses$2] = params.addresses;
	    babelHelpers.classPrivateFieldLooseBase(this, _fieldConfig)[_fieldConfig] = params.fieldConfig;
	    babelHelpers.classPrivateFieldLooseBase(this, _isMultiple$1)[_isMultiple$1] = params.isMultiple;
	    babelHelpers.classPrivateFieldLooseBase(this, _showMap$2)[_showMap$2] = params.showMap;
	    babelHelpers.classPrivateFieldLooseBase(this, _showDetailsToggle$2)[_showDetailsToggle$2] = Boolean((_params$showDetailsTo2 = params.showDetailsToggle) != null ? _params$showDetailsTo2 : true);
	    if (params.additionalProperties) {
	      babelHelpers.classPrivateFieldLooseBase(this, _additionalProperties)[_additionalProperties] = params.additionalProperties;
	    }
	  }
	  layout() {
	    /** @type BaseView */
	    let view = null;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] === AddressField.VIEW_MODE) {
	      view = new View({
	        wrapper: babelHelpers.classPrivateFieldLooseBase(this, _wrapper$2)[_wrapper$2],
	        addresses: babelHelpers.classPrivateFieldLooseBase(this, _addresses$2)[_addresses$2]
	      });
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] === AddressField.EDIT_MODE) {
	      var _babelHelpers$classPr;
	      view = new Edit({
	        wrapper: babelHelpers.classPrivateFieldLooseBase(this, _wrapper$2)[_wrapper$2],
	        fieldName: babelHelpers.classPrivateFieldLooseBase(this, _fieldConfig)[_fieldConfig].fieldName,
	        fieldFormName: babelHelpers.classPrivateFieldLooseBase(this, _fieldConfig)[_fieldConfig].fieldFormName,
	        addresses: babelHelpers.classPrivateFieldLooseBase(this, _addresses$2)[_addresses$2],
	        isMultiple: babelHelpers.classPrivateFieldLooseBase(this, _isMultiple$1)[_isMultiple$1],
	        compactMode: (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _additionalProperties)[_additionalProperties].compactMode) != null ? _babelHelpers$classPr : false,
	        showMap: babelHelpers.classPrivateFieldLooseBase(this, _showMap$2)[_showMap$2],
	        showDetailsToggle: babelHelpers.classPrivateFieldLooseBase(this, _showDetailsToggle$2)[_showDetailsToggle$2]
	      });
	    }
	    if (view) {
	      view.layout();
	    }
	  }
	}
	AddressField.VIEW_MODE = 'view';
	AddressField.EDIT_MODE = 'edit';
	const namespace = main_core.Reflection.namespace('BX.Fileman.UserField');
	namespace.AddressField = AddressField;

}((this.BX.Fileman.Userfield = this.BX.Fileman.Userfield || {}),BX.Event,BX.Location.Core,BX.Location.Widget,BX));
//# sourceMappingURL=address_widget.bundle.js.map
