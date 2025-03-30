/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_iconSet_api_core,ui_entitySelector) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	const settingEntityId = 'setting';
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _settingButtonTextNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingButtonTextNode");
	var _settingButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingButton");
	var _hiddenInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hiddenInput");
	var _inputName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputName");
	var _settingsMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsMap");
	var _selectedOptionKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedOptionKey");
	var _dialogOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogOptions");
	var _forbidOptionDeselect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("forbidOptionDeselect");
	var _createSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSelector");
	var _renderContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContainer");
	class SettingSelector {
	  // At least one item from the list must be selected.

	  constructor(options) {
	    Object.defineProperty(this, _renderContainer, {
	      value: _renderContainer2
	    });
	    Object.defineProperty(this, _createSelector, {
	      value: _createSelector2
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _settingButtonTextNode, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _settingButton, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _hiddenInput, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _inputName, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settingsMap, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _selectedOptionKey, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogOptions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _forbidOptionDeselect, {
	      writable: true,
	      value: true
	    });
	    const {
	      settingsMap = {},
	      selectedOptionKey,
	      inputName,
	      dialogOptions = {}
	    } = options;
	    Object.entries(settingsMap).forEach(([key, value]) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _settingsMap)[_settingsMap].set(key, value);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogOptions)[_dialogOptions] = dialogOptions;
	    babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName] = inputName;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedOptionKey)[_selectedOptionKey] = selectedOptionKey;
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = babelHelpers.classPrivateFieldLooseBase(this, _renderContainer)[_renderContainer]();
	    babelHelpers.classPrivateFieldLooseBase(this, _createSelector)[_createSelector]();
	  }
	  getSelected() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedOptionKey)[_selectedOptionKey];
	  }
	  select(key) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedOptionKey)[_selectedOptionKey] = key;
	    babelHelpers.classPrivateFieldLooseBase(this, _settingButtonTextNode)[_settingButtonTextNode].textContent = babelHelpers.classPrivateFieldLooseBase(this, _settingsMap)[_settingsMap].get(this.getSelected());
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hiddenInput)[_hiddenInput]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hiddenInput)[_hiddenInput].setAttribute('value', key);
	    }
	  }
	  renderTo(targetContainer) {
	    if (main_core.Type.isDomNode(targetContainer)) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], targetContainer);
	    }
	  }
	}
	function _createSelector2() {
	  const items = [];
	  babelHelpers.classPrivateFieldLooseBase(this, _settingsMap)[_settingsMap].forEach((value, key) => {
	    items.push({
	      id: key,
	      title: value,
	      entityId: settingEntityId,
	      selected: key === this.getSelected(),
	      tabs: 'recents'
	    });
	  });
	  this.settingDialog = new ui_entitySelector.Dialog({
	    items,
	    targetNode: babelHelpers.classPrivateFieldLooseBase(this, _settingButton)[_settingButton],
	    width: 170,
	    height: 37 * items.length + 15,
	    multiple: false,
	    enableSearch: false,
	    dropdownMode: true,
	    showAvatars: false,
	    compactView: true,
	    events: {
	      'Item:onBeforeDeselect': event => {
	        if (babelHelpers.classPrivateFieldLooseBase(this, _forbidOptionDeselect)[_forbidOptionDeselect]) {
	          event.preventDefault();
	        }
	      },
	      'Item:onBeforeSelect': () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _forbidOptionDeselect)[_forbidOptionDeselect] = false;
	      },
	      'Item:onSelect': event => {
	        const {
	          item: selectedItem
	        } = event.getData();
	        this.select(selectedItem.getId());
	      },
	      'Item:onDeselect': () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _forbidOptionDeselect)[_forbidOptionDeselect] = true;
	      }
	    },
	    ...babelHelpers.classPrivateFieldLooseBase(this, _dialogOptions)[_dialogOptions]
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _settingButton)[_settingButton], 'click', () => {
	    this.settingDialog.show();
	  });
	}
	function _renderContainer2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Actions.CHEVRON_DOWN,
	    color: getComputedStyle(document.body).getPropertyValue('--ui-color-base-80'),
	    size: 16
	  });
	  this.icon = icon.render();
	  let selectedOptionText = babelHelpers.classPrivateFieldLooseBase(this, _settingsMap)[_settingsMap].get(babelHelpers.classPrivateFieldLooseBase(this, _selectedOptionKey)[_selectedOptionKey]);
	  if (selectedOptionText === undefined) {
	    selectedOptionText = '';
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _settingButtonTextNode)[_settingButtonTextNode] = main_core.Tag.render(_t || (_t = _`<div class="setting-selector-button-text"></div>`));
	  babelHelpers.classPrivateFieldLooseBase(this, _settingButtonTextNode)[_settingButtonTextNode].setAttribute('title', selectedOptionText);
	  babelHelpers.classPrivateFieldLooseBase(this, _settingButtonTextNode)[_settingButtonTextNode].textContent = selectedOptionText;
	  babelHelpers.classPrivateFieldLooseBase(this, _settingButton)[_settingButton] = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="setting-selector-button">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _settingButtonTextNode)[_settingButtonTextNode], this.icon);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName] === undefined) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hiddenInput)[_hiddenInput] = main_core.Tag.render(_t3 || (_t3 = _``));
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _hiddenInput)[_hiddenInput] = main_core.Tag.render(_t4 || (_t4 = _`<input type="hidden">`));
	    babelHelpers.classPrivateFieldLooseBase(this, _hiddenInput)[_hiddenInput].setAttribute('name', babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName]);
	    babelHelpers.classPrivateFieldLooseBase(this, _hiddenInput)[_hiddenInput].setAttribute('value', babelHelpers.classPrivateFieldLooseBase(this, _selectedOptionKey)[_selectedOptionKey]);
	  }
	  return main_core.Tag.render(_t5 || (_t5 = _`
			 <div class="setting-selector-container">
			${0}
			${0}
			 </div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _settingButton)[_settingButton], babelHelpers.classPrivateFieldLooseBase(this, _hiddenInput)[_hiddenInput]);
	}

	exports.SettingSelector = SettingSelector;

}((this.BX.Mail = this.BX.Mail || {}),BX,BX.UI.IconSet,BX.UI.EntitySelector));
//# sourceMappingURL=setting-selector.bundle.js.map
