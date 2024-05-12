/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events,main_popup) {
	'use strict';

	let _ = t => t,
	  _t;
	const ScrollDirection = Object.freeze({
	  TOP: -1,
	  BOTTOM: 1,
	  NONE: 0
	});
	var _placeholder = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("placeholder");
	var _isSearchable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSearchable");
	var _isSearching = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSearching");
	var _searchValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("searchValue");
	var _selectedOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedOption");
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _containerClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("containerClassname");
	var _menu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _emptySearchPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emptySearchPopup");
	var _highlightedOptionIndex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("highlightedOptionIndex");
	var _popupParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupParams");
	var _renderContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContainer");
	var _isInputReadonly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInputReadonly");
	var _handleInputClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleInputClick");
	var _createMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	var _getMenuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMenuItems");
	var _handleInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleInput");
	var _handleKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleKeyDown");
	var _handleSpaceKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSpaceKey");
	var _handleArrowUpKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleArrowUpKey");
	var _handleArrowDownKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleArrowDownKey");
	var _handleEnterKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleEnterKey");
	var _updateMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateMenu");
	var _getMenuItemFromOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMenuItemFromOption");
	var _getFilteredOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilteredOptions");
	var _getOptionFilter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOptionFilter");
	var _showEmptySearchPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showEmptySearchPopup");
	var _hideEmptySearchPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideEmptySearchPopup");
	var _setSelectedOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setSelectedOption");
	var _findOptionByValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findOptionByValue");
	var _highlightOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("highlightOption");
	var _scrollToHighlightedItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("scrollToHighlightedItem");
	var _getScrollDirectionToHighlightedItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getScrollDirectionToHighlightedItem");
	var _getOptionIndex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOptionIndex");
	var _handleBlur = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleBlur");
	var _handleFocus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFocus");
	var _updateSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateSelect");
	var _updateInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateInput");
	var _updateContainerClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateContainerClassname");
	var _getContainerClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainerClassname");
	class Select extends main_core_events.EventEmitter {
	  constructor(_options2) {
	    var _babelHelpers$classPr;
	    super();
	    Object.defineProperty(this, _getContainerClassname, {
	      value: _getContainerClassname2
	    });
	    Object.defineProperty(this, _updateContainerClassname, {
	      value: _updateContainerClassname2
	    });
	    Object.defineProperty(this, _updateInput, {
	      value: _updateInput2
	    });
	    Object.defineProperty(this, _updateSelect, {
	      value: _updateSelect2
	    });
	    Object.defineProperty(this, _handleFocus, {
	      value: _handleFocus2
	    });
	    Object.defineProperty(this, _handleBlur, {
	      value: _handleBlur2
	    });
	    Object.defineProperty(this, _getOptionIndex, {
	      value: _getOptionIndex2
	    });
	    Object.defineProperty(this, _getScrollDirectionToHighlightedItem, {
	      value: _getScrollDirectionToHighlightedItem2
	    });
	    Object.defineProperty(this, _scrollToHighlightedItem, {
	      value: _scrollToHighlightedItem2
	    });
	    Object.defineProperty(this, _highlightOption, {
	      value: _highlightOption2
	    });
	    Object.defineProperty(this, _findOptionByValue, {
	      value: _findOptionByValue2
	    });
	    Object.defineProperty(this, _setSelectedOption, {
	      value: _setSelectedOption2
	    });
	    Object.defineProperty(this, _hideEmptySearchPopup, {
	      value: _hideEmptySearchPopup2
	    });
	    Object.defineProperty(this, _showEmptySearchPopup, {
	      value: _showEmptySearchPopup2
	    });
	    Object.defineProperty(this, _getOptionFilter, {
	      value: _getOptionFilter2
	    });
	    Object.defineProperty(this, _getFilteredOptions, {
	      value: _getFilteredOptions2
	    });
	    Object.defineProperty(this, _getMenuItemFromOption, {
	      value: _getMenuItemFromOption2
	    });
	    Object.defineProperty(this, _updateMenu, {
	      value: _updateMenu2
	    });
	    Object.defineProperty(this, _handleEnterKey, {
	      value: _handleEnterKey2
	    });
	    Object.defineProperty(this, _handleArrowDownKey, {
	      value: _handleArrowDownKey2
	    });
	    Object.defineProperty(this, _handleArrowUpKey, {
	      value: _handleArrowUpKey2
	    });
	    Object.defineProperty(this, _handleSpaceKey, {
	      value: _handleSpaceKey2
	    });
	    Object.defineProperty(this, _handleKeyDown, {
	      value: _handleKeyDown2
	    });
	    Object.defineProperty(this, _handleInput, {
	      value: _handleInput2
	    });
	    Object.defineProperty(this, _getMenuItems, {
	      value: _getMenuItems2
	    });
	    Object.defineProperty(this, _createMenu, {
	      value: _createMenu2
	    });
	    Object.defineProperty(this, _handleInputClick, {
	      value: _handleInputClick2
	    });
	    Object.defineProperty(this, _isInputReadonly, {
	      value: _isInputReadonly2
	    });
	    Object.defineProperty(this, _renderContainer, {
	      value: _renderContainer2
	    });
	    Object.defineProperty(this, _placeholder, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _isSearchable, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isSearching, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _searchValue, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _selectedOption, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _containerClassname, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _menu, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _emptySearchPopup, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _highlightedOptionIndex, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _popupParams, {
	      writable: true,
	      value: {}
	    });
	    this.setEventNamespace('BX.UI.Select');
	    babelHelpers.classPrivateFieldLooseBase(this, _placeholder)[_placeholder] = main_core.Type.isString(_options2.placeholder) ? _options2.placeholder : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _isSearchable)[_isSearchable] = _options2.isSearchable === true || false;
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = Array.isArray(_options2.options) ? _options2.options : [];
	    babelHelpers.classPrivateFieldLooseBase(this, _popupParams)[_popupParams] = main_core.Type.isPlainObject(_options2.popupParams) ? _options2.popupParams : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption] = babelHelpers.classPrivateFieldLooseBase(this, _findOptionByValue)[_findOptionByValue](_options2.value) || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _containerClassname)[_containerClassname] = main_core.Type.isString(_options2 == null ? void 0 : _options2.containerClassname) ? _options2.containerClassname : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex] = babelHelpers.classPrivateFieldLooseBase(this, _getOptionIndex)[_getOptionIndex]((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption]) == null ? void 0 : _babelHelpers$classPr.value) || 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _renderContainer)[_renderContainer]();
	  }
	  renderTo(targetContainer) {
	    if (main_core.Type.isDomNode(targetContainer)) {
	      main_core.Dom.clean(targetContainer);
	      babelHelpers.classPrivateFieldLooseBase(this, _renderContainer)[_renderContainer]();
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], targetContainer);
	      return targetContainer;
	    }
	    return null;
	  }
	  showMenu() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _createMenu)[_createMenu]();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].show();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateMenu)[_updateMenu]();
	  }
	  hideMenu() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].close();
	    }
	  }
	  getInput() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector('input');
	  }
	  getValue() {
	    var _babelHelpers$classPr2;
	    return ((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption]) == null ? void 0 : _babelHelpers$classPr2.value) || '';
	  }
	  setValue(value) {
	    const option = babelHelpers.classPrivateFieldLooseBase(this, _findOptionByValue)[_findOptionByValue](value);
	    babelHelpers.classPrivateFieldLooseBase(this, _setSelectedOption)[_setSelectedOption](option);
	  }
	  isMenuShown() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu] && babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].getPopupWindow().isShown();
	  }
	}
	function _renderContainer2() {
	  var _babelHelpers$classPr3;
	  babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = main_core.Tag.render(_t || (_t = _`
			<div class="${0}">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<input
					ref="input"
					class="ui-ctl-element"
					type="text"
					placeholder="${0}"
					${0}
					value="${0}"
				>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getContainerClassname)[_getContainerClassname](), babelHelpers.classPrivateFieldLooseBase(this, _placeholder)[_placeholder], babelHelpers.classPrivateFieldLooseBase(this, _isInputReadonly)[_isInputReadonly]() ? 'readonly' : '', ((_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption]) == null ? void 0 : _babelHelpers$classPr3.label) || '');
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].input, 'input', babelHelpers.classPrivateFieldLooseBase(this, _handleInput)[_handleInput].bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].input, 'focus', babelHelpers.classPrivateFieldLooseBase(this, _handleFocus)[_handleFocus].bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].input, 'blur', babelHelpers.classPrivateFieldLooseBase(this, _handleBlur)[_handleBlur].bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].input, 'mouseup', babelHelpers.classPrivateFieldLooseBase(this, _handleInputClick)[_handleInputClick].bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].input, 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _handleKeyDown)[_handleKeyDown].bind(this));
	  babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].root;
	  return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	}
	function _isInputReadonly2() {
	  return !babelHelpers.classPrivateFieldLooseBase(this, _isSearchable)[_isSearchable] || !this.isMenuShown() && !babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup];
	}
	function _handleInputClick2() {
	  if (this.getInput() === document.activeElement) {
	    setTimeout(() => {
	      this.showMenu();
	    }, 100);
	  }
	}
	function _createMenu2() {
	  var _babelHelpers$classPr4, _babelHelpers$classPr5;
	  const {
	    width
	  } = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	  const events = (_babelHelpers$classPr4 = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _popupParams)[_popupParams]) == null ? void 0 : _babelHelpers$classPr5.events) != null ? _babelHelpers$classPr4 : {};
	  babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu] = new main_popup.Menu({
	    width,
	    bindElement: babelHelpers.classPrivateFieldLooseBase(this, _container)[_container],
	    items: babelHelpers.classPrivateFieldLooseBase(this, _getMenuItems)[_getMenuItems](),
	    closeByEsc: true,
	    autoHide: false,
	    className: 'select-menu-popup',
	    ...babelHelpers.classPrivateFieldLooseBase(this, _popupParams)[_popupParams],
	    events: {
	      ...events,
	      onAfterClose: () => {
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup]) {
	          babelHelpers.classPrivateFieldLooseBase(this, _searchValue)[_searchValue] = '';
	          babelHelpers.classPrivateFieldLooseBase(this, _setSelectedOption)[_setSelectedOption](babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption]);
	          babelHelpers.classPrivateFieldLooseBase(this, _updateSelect)[_updateSelect]();
	          if (events.onAfterClose) {
	            events.onAfterClose();
	          }
	        }
	      }
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu];
	}
	function _getMenuItems2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isSearching)[_isSearching]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getFilteredOptions)[_getFilteredOptions]().map((option, index) => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _getMenuItemFromOption)[_getMenuItemFromOption](option, index === babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]);
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].map((option, index) => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getMenuItemFromOption)[_getMenuItemFromOption](option, index === babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]);
	  });
	}
	function _handleInput2(e) {
	  e.preventDefault();
	  babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex] = 0;
	  babelHelpers.classPrivateFieldLooseBase(this, _isSearching)[_isSearching] = true;
	  babelHelpers.classPrivateFieldLooseBase(this, _searchValue)[_searchValue] = e.target.value;
	  babelHelpers.classPrivateFieldLooseBase(this, _updateMenu)[_updateMenu]();
	}
	function _handleKeyDown2(e) {
	  const {
	    keyCode
	  } = e;
	  const arrowUpKeyCode = 38;
	  const arrowDownKeyCode = 40;
	  const enterKeyCode = 13;
	  const spaceKeyCode = 32;

	  // eslint-disable-next-line default-case
	  switch (keyCode) {
	    case enterKeyCode:
	      babelHelpers.classPrivateFieldLooseBase(this, _handleEnterKey)[_handleEnterKey](e);
	      break;
	    case spaceKeyCode:
	      babelHelpers.classPrivateFieldLooseBase(this, _handleSpaceKey)[_handleSpaceKey](e);
	      break;
	    case arrowUpKeyCode:
	      babelHelpers.classPrivateFieldLooseBase(this, _handleArrowUpKey)[_handleArrowUpKey](e);
	      break;
	    case arrowDownKeyCode:
	      babelHelpers.classPrivateFieldLooseBase(this, _handleArrowDownKey)[_handleArrowDownKey](e);
	      break;
	  }
	}
	function _handleSpaceKey2(e) {
	  if (!this.isMenuShown() && !babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup]) {
	    e.preventDefault();
	    this.showMenu();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateSelect)[_updateSelect]();
	  }
	}
	function _handleArrowUpKey2(e) {
	  e.preventDefault();
	  if (!this.isMenuShown() || babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex] === 0) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]--;
	  babelHelpers.classPrivateFieldLooseBase(this, _scrollToHighlightedItem)[_scrollToHighlightedItem]();
	  babelHelpers.classPrivateFieldLooseBase(this, _highlightOption)[_highlightOption](babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]);
	}
	function _handleArrowDownKey2(e) {
	  e.preventDefault();
	  if (!this.isMenuShown() || babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex] === babelHelpers.classPrivateFieldLooseBase(this, _getMenuItems)[_getMenuItems]().length - 1) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]++;
	  babelHelpers.classPrivateFieldLooseBase(this, _scrollToHighlightedItem)[_scrollToHighlightedItem]();
	  babelHelpers.classPrivateFieldLooseBase(this, _highlightOption)[_highlightOption](babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]);
	}
	function _handleEnterKey2(e) {
	  e.preventDefault();
	  const options = babelHelpers.classPrivateFieldLooseBase(this, _getFilteredOptions)[_getFilteredOptions]();
	  babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption] = options[babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]];
	  this.hideMenu();
	}
	function _updateMenu2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].forEach(({
	    value
	  }) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].removeMenuItem(value, {
	      destroyEmptyPopup: false
	    });
	  });
	  const filteredOptions = babelHelpers.classPrivateFieldLooseBase(this, _getFilteredOptions)[_getFilteredOptions](babelHelpers.classPrivateFieldLooseBase(this, _searchValue)[_searchValue]);
	  if (filteredOptions.length > 0) {
	    if (!this.isMenuShown()) {
	      this.showMenu();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _hideEmptySearchPopup)[_hideEmptySearchPopup]();
	    filteredOptions.forEach((option, index) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].addMenuItem(babelHelpers.classPrivateFieldLooseBase(this, _getMenuItemFromOption)[_getMenuItemFromOption](option, index === babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]), null);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _scrollToHighlightedItem)[_scrollToHighlightedItem]();
	    babelHelpers.classPrivateFieldLooseBase(this, _highlightOption)[_highlightOption](babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]);
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _showEmptySearchPopup)[_showEmptySearchPopup]();
	    this.hideMenu();
	  }
	}
	function _getMenuItemFromOption2(option, isHoverOption = false) {
	  const isHover = isHoverOption === true;
	  const className = `ui-select__menu-item menu-popup-no-icon ${isHover ? 'menu-popup-item-open' : ''}`;
	  return {
	    id: option.value,
	    text: option.label,
	    onclick: () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption] = option;
	    },
	    className
	  };
	}
	function _getFilteredOptions2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].filter(babelHelpers.classPrivateFieldLooseBase(this, _getOptionFilter)[_getOptionFilter](babelHelpers.classPrivateFieldLooseBase(this, _searchValue)[_searchValue]));
	}
	function _getOptionFilter2(searchStr) {
	  const lowerCaseSearchStr = main_core.Type.isString(searchStr) ? searchStr.toLowerCase() : '';
	  return option => {
	    const lowerCaseOptionLabel = option.label.toLowerCase();
	    return lowerCaseOptionLabel.indexOf(lowerCaseSearchStr) === 0;
	  };
	}
	function _showEmptySearchPopup2() {
	  var _babelHelpers$classPr6;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup] || !((_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup]) != null && _babelHelpers$classPr6.isShown())) {
	    var _babelHelpers$classPr7, _babelHelpers$classPr8;
	    const {
	      width
	    } = main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]);
	    const events = (_babelHelpers$classPr7 = (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _popupParams)[_popupParams]) == null ? void 0 : _babelHelpers$classPr8.events) != null ? _babelHelpers$classPr7 : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup] = new main_popup.Popup({
	      width,
	      bindElement: babelHelpers.classPrivateFieldLooseBase(this, _container)[_container],
	      content: main_core.Loc.getMessage('UI_SELECT_NOTHING_FOUND'),
	      closeByEsc: true,
	      ...babelHelpers.classPrivateFieldLooseBase(this, _popupParams)[_popupParams],
	      events: {
	        ...events,
	        onAfterClose: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup] = null;
	          babelHelpers.classPrivateFieldLooseBase(this, _setSelectedOption)[_setSelectedOption](babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption]);
	          if (!this.isMenuShown()) {
	            babelHelpers.classPrivateFieldLooseBase(this, _searchValue)[_searchValue] = '';
	            babelHelpers.classPrivateFieldLooseBase(this, _updateSelect)[_updateSelect]();
	            if (events.onAfterClose) {
	              events.onAfterClose();
	            }
	          }
	        }
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup].show();
	  }
	}
	function _hideEmptySearchPopup2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup].destroy();
	    babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup] = null;
	  }
	}
	function _setSelectedOption2(option) {
	  if (!option) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption] = null;
	    return;
	  }
	  this.emit('update', option.value);
	  babelHelpers.classPrivateFieldLooseBase(this, _searchValue)[_searchValue] = '';
	  const input = this.getInput();
	  input.value = option.label;
	  babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex] = babelHelpers.classPrivateFieldLooseBase(this, _getOptionIndex)[_getOptionIndex](option.value);
	  babelHelpers.classPrivateFieldLooseBase(this, _selectedOption)[_selectedOption] = option;
	}
	function _findOptionByValue2(value) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].find(option => {
	    return option.value === value;
	  });
	}
	function _highlightOption2(optionIndex) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	    return;
	  }
	  const menuItems = babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].itemsContainer.children;
	  for (let i = 0; i < menuItems.length; i++) {
	    const item = menuItems.item(i);
	    main_core.Dom.removeClass(item, 'menu-popup-item-open');
	    if (i === optionIndex) {
	      main_core.Dom.addClass(item, 'menu-popup-item-open');
	    }
	  }
	}
	function _scrollToHighlightedItem2() {
	  const popupContent = babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].getPopupWindow().getContentContainer();
	  const menuItems = babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].itemsContainer.children;
	  const highlightedItem = menuItems.item(babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex]);
	  const {
	    height: popupContentHeight
	  } = main_core.Dom.getPosition(popupContent);
	  const {
	    height: highlightedItemHeight
	  } = main_core.Dom.getPosition(highlightedItem);
	  const direction = babelHelpers.classPrivateFieldLooseBase(this, _getScrollDirectionToHighlightedItem)[_getScrollDirectionToHighlightedItem](popupContent, highlightedItem);
	  if (direction !== ScrollDirection.NONE) {
	    popupContent.scroll({
	      left: 0,
	      top: highlightedItemHeight * babelHelpers.classPrivateFieldLooseBase(this, _highlightedOptionIndex)[_highlightedOptionIndex] + direction * popupContentHeight,
	      behavior: 'smooth'
	    });
	  }
	}
	function _getScrollDirectionToHighlightedItem2(popupContent, highlightedItem) {
	  const {
	    bottom: popupContentBottom,
	    top: popupContentTop
	  } = main_core.Dom.getPosition(popupContent);
	  const {
	    bottom: highlightedItemBottom,
	    top: highlightedItemTop
	  } = main_core.Dom.getPosition(highlightedItem);
	  if (popupContentTop > highlightedItemTop) {
	    return ScrollDirection.TOP;
	  }
	  if (popupContentBottom < highlightedItemBottom) {
	    return ScrollDirection.BOTTOM;
	  }
	  return ScrollDirection.NONE;
	}
	function _getOptionIndex2(optionValue) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].findIndex(option => {
	    return option.value === optionValue;
	  });
	}
	function _handleBlur2() {
	  this.hideMenu();
	  babelHelpers.classPrivateFieldLooseBase(this, _hideEmptySearchPopup)[_hideEmptySearchPopup]();
	}
	function _handleFocus2(e) {
	  setTimeout(() => {
	    this.showMenu();
	    babelHelpers.classPrivateFieldLooseBase(this, _updateSelect)[_updateSelect]();
	  }, 100);
	  e.preventDefault();
	}
	function _updateSelect2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateInput)[_updateInput]();
	  babelHelpers.classPrivateFieldLooseBase(this, _updateContainerClassname)[_updateContainerClassname]();
	}
	function _updateInput2() {
	  const input = this.getInput();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isInputReadonly)[_isInputReadonly]()) {
	    input.setAttribute('readonly', 'readonly');
	  } else {
	    input.removeAttribute('readonly');
	  }
	}
	function _updateContainerClassname2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].className = babelHelpers.classPrivateFieldLooseBase(this, _getContainerClassname)[_getContainerClassname]();
	}
	function _getContainerClassname2() {
	  const openMenuClassnameModifier = this.isMenuShown() || babelHelpers.classPrivateFieldLooseBase(this, _emptySearchPopup)[_emptySearchPopup] ? '--open' : '';
	  return `ui-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown ${babelHelpers.classPrivateFieldLooseBase(this, _containerClassname)[_containerClassname]} ${openMenuClassnameModifier}`;
	}

	exports.Select = Select;

}((this.BX.Ui = this.BX.Ui || {}),BX,BX.Event,BX.Main));
//# sourceMappingURL=select.bundle.js.map
