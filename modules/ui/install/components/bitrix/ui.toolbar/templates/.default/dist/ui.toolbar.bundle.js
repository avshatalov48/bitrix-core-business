/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_buttons,main_core,main_popup) {
	'use strict';

	const TitleEditorEvents = {
	  beforeStartEditing: 'beforeStartEditing',
	  startEditing: 'startEditing',
	  finishEditing: 'finishEditing'
	};
	var _initialTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialTitle");
	var _defaultTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultTitle");
	var _toolbarNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toolbarNode");
	var _titleNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("titleNode");
	var _inputNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputNode");
	var _editTitleButtonNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("editTitleButtonNode");
	var _editTitleResultButtonsContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("editTitleResultButtonsContainer");
	var _titleIconButtonsContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("titleIconButtonsContainer");
	var _saveTitleButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("saveTitleButton");
	var _cancelTitleEditButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cancelTitleEditButton");
	var _isInit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInit");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	class TitleEditor extends main_core.Event.EventEmitter {
	  // #dataContainer: ?HTMLElement;

	  // #dataNode: ?HTMLElement;

	  constructor(options) {
	    super(options);
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _initialTitle, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _defaultTitle, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _toolbarNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _titleNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _inputNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _editTitleButtonNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _editTitleResultButtonsContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _titleIconButtonsContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _saveTitleButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _cancelTitleEditButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isInit, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('UI.Toolbar.TitleEditor');
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init](options);
	  }
	  enable(isDisable = false) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isInit)[_isInit]) {
	      return;
	    }
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _editTitleButtonNode)[_editTitleButtonNode], isDisable === false);
	    // this.titleNode.textContent = isDisable === false
	    // 	? (this.dataNode.value ?? this.defaultTitle)
	    // 	: this.initialTitle
	    // ;

	    babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode].textContent = babelHelpers.classPrivateFieldLooseBase(this, _initialTitle)[_initialTitle];
	  }
	  disable() {
	    this.enable(true);
	  }

	  // onDataNodeChange()
	  // {
	  // 	this.#titleNode.textContent = this.#dataNode.value;
	  // }

	  onKeyUp(event) {
	    if (event.key === 'Enter') {
	      this.finishEdit();
	      event.preventDefault();
	      return false;
	    }
	    return true;
	  }
	  startEdit() {
	    // this.inputNode.value = this.dataNode.value || this.titleNode.textContent;

	    const event = new main_core.Event.BaseEvent();
	    this.emit(TitleEditorEvents.beforeStartEditing, event);
	    if (event.isDefaultPrevented()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode].value = babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode].textContent;
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode], false);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _editTitleButtonNode)[_editTitleButtonNode], false);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode], true);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _titleIconButtonsContainer)[_titleIconButtonsContainer], false);
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _editTitleResultButtonsContainer)[_editTitleResultButtonsContainer], '--show');
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _toolbarNode)[_toolbarNode], '--title-editing');
	    babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode].focus();
	    this.emit(TitleEditorEvents.startEditing);
	  }
	  finishEdit() {
	    // this.dataNode.value = this.inputNode.value;
	    babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode].textContent = babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode].value;
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode], false);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _editTitleButtonNode)[_editTitleButtonNode], true);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode], true);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _titleIconButtonsContainer)[_titleIconButtonsContainer], true);
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _editTitleResultButtonsContainer)[_editTitleResultButtonsContainer], '--show');
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _toolbarNode)[_toolbarNode], '--title-editing');
	    this.emit(TitleEditorEvents.finishEditing, {
	      updatedTitle: babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode].value
	    });
	  }
	  cancelEdit() {
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode], false);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _editTitleButtonNode)[_editTitleButtonNode], true);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode], true);
	    this.changeDisplay(babelHelpers.classPrivateFieldLooseBase(this, _titleIconButtonsContainer)[_titleIconButtonsContainer], true);
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _editTitleResultButtonsContainer)[_editTitleResultButtonsContainer], '--show');
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _toolbarNode)[_toolbarNode], '--title-editing');
	  }
	  changeDisplay(node, isShow) {
	    const displayValue = isShow ? '' : 'none';
	    main_core.Dom.style(node, 'display', displayValue);
	    return displayValue;
	  }
	}
	function _init2(params) {
	  // if (!params.selector)
	  // {
	  // 	return;
	  // }
	  //
	  // this.dataContainer = document.querySelector(params.selector);
	  // if (!this.dataContainer)
	  // {
	  // 	return;
	  // }
	  //
	  // Dom.style(this.dataContainer, 'display', 'none');

	  babelHelpers.classPrivateFieldLooseBase(this, _toolbarNode)[_toolbarNode] = document.getElementById('uiToolbarContainer');
	  babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode] = document.querySelector('.ui-wrap-title-name');
	  babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode] = document.querySelector('.ui-toolbar-edit-title-input');
	  babelHelpers.classPrivateFieldLooseBase(this, _editTitleButtonNode)[_editTitleButtonNode] = document.querySelector('.ui-toolbar-edit-title-button');
	  babelHelpers.classPrivateFieldLooseBase(this, _editTitleResultButtonsContainer)[_editTitleResultButtonsContainer] = document.getElementById('ui-toolbar-title-edit-result-buttons');
	  babelHelpers.classPrivateFieldLooseBase(this, _saveTitleButton)[_saveTitleButton] = document.getElementById('ui-toolbar-save-title-button');
	  babelHelpers.classPrivateFieldLooseBase(this, _cancelTitleEditButton)[_cancelTitleEditButton] = document.getElementById('ui-toolbar-cancel-title-edit-button');
	  babelHelpers.classPrivateFieldLooseBase(this, _titleIconButtonsContainer)[_titleIconButtonsContainer] = document.getElementById('ui-toolbar-title-item-box-buttons');
	  babelHelpers.classPrivateFieldLooseBase(this, _initialTitle)[_initialTitle] = babelHelpers.classPrivateFieldLooseBase(this, _titleNode)[_titleNode].textContent;
	  babelHelpers.classPrivateFieldLooseBase(this, _defaultTitle)[_defaultTitle] = params.defaultTitle;

	  // bind(this.dataNode, 'bxchange', this.onDataNodeChange.bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _editTitleButtonNode)[_editTitleButtonNode], 'click', this.startEdit.bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode], 'keyup', this.onKeyUp.bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _inputNode)[_inputNode], 'blur', event => {
	    if (event.relatedTarget === babelHelpers.classPrivateFieldLooseBase(this, _cancelTitleEditButton)[_cancelTitleEditButton]) {
	      this.cancelEdit();
	      return;
	    }
	    this.finishEdit();
	  });
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _saveTitleButton)[_saveTitleButton], 'click', this.finishEdit.bind(this));
	  main_core.bind(babelHelpers.classPrivateFieldLooseBase(this, _cancelTitleEditButton)[_cancelTitleEditButton], 'click', this.cancelEdit.bind(this));
	  babelHelpers.classPrivateFieldLooseBase(this, _isInit)[_isInit] = true;
	  if (!params.disabled) {
	    this.enable();
	  }
	}

	class ToolbarStar {
	  constructor() {
	    this.initialized = false;
	    this.currentPageInMenu = false;
	    this.starContNode = null;
	    main_core.ready(() => this.init());
	    main_core.Event.EventEmitter.subscribe('onFrameDataProcessed', () => {
	      this.init();
	    });
	    // BX.addCustomEvent('onFrameDataProcessed', () => this.init());
	  }

	  init() {
	    this.starContNode = document.getElementById('uiToolbarStar');
	    if (!this.starContNode || this.initialized) {
	      return false;
	    }
	    this.initialized = true;
	    let currentFullPath = main_core.Dom.attr(this.starContNode, 'data-bx-url');
	    if (!main_core.Type.isStringFilled(currentFullPath)) {
	      currentFullPath = document.location.pathname + document.location.search;
	    }
	    currentFullPath = main_core.Uri.removeParam(currentFullPath, ['IFRAME', 'IFRAME_TYPE']);
	    top.BX.addCustomEvent('BX.Bitrix24.LeftMenuClass:onSendMenuItemData', params => {
	      this.processMenuItemData(params);
	    });
	    top.BX.addCustomEvent('BX.Bitrix24.LeftMenuClass:onStandardItemChangedSuccess', params => {
	      this.onStandardItemChangedSuccess(params);
	    });
	    top.BX.onCustomEvent('UI.Toolbar:onRequestMenuItemData', [{
	      currentFullPath,
	      context: window
	    }]);
	    return true;
	  }
	  processMenuItemData(params) {
	    if (params.context && params.context !== window) {
	      return;
	    }
	    this.currentPageInMenu = params.currentPageInMenu;
	    if (main_core.Type.isObjectLike(params.currentPageInMenu)) {
	      main_core.Dom.addClass(this.starContNode, 'ui-toolbar-star-active');
	    }
	    this.starContNode.title = main_core.Loc.getMessage(main_core.Dom.hasClass(this.starContNode, 'ui-toolbar-star-active') ? 'UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU' : 'UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU');

	    // default page
	    if (main_core.Type.isDomNode(this.currentPageInMenu) && main_core.Dom.attr(this.currentPageInMenu, 'data-type') !== 'standard') {
	      this.starContNode.title = main_core.Loc.getMessage('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE');
	      main_core.bind(this.starContNode, 'click', () => {
	        this.showMessage(main_core.Loc.getMessage('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE_DELETE_ERROR'));
	      });
	      return true;
	    }

	    // any page
	    main_core.bind(this.starContNode, 'click', () => {
	      var _document$getElementB;
	      let pageTitle = ((_document$getElementB = document.getElementById('pagetitle')) == null ? void 0 : _document$getElementB.innerText) || '';
	      const titleTemplate = this.starContNode.getAttribute('data-bx-title-template');
	      if (main_core.Type.isStringFilled(titleTemplate)) {
	        pageTitle = titleTemplate.replace(/#page_title#/i, pageTitle);
	      }
	      let pageLink = this.starContNode.getAttribute('data-bx-url');
	      if (!main_core.Type.isStringFilled(pageLink)) {
	        pageLink = document.location.pathname + document.location.search;
	      }
	      pageLink = main_core.Uri.removeParam(pageLink, ['IFRAME', 'IFRAME_TYPE']);
	      top.BX.onCustomEvent('UI.Toolbar:onStarClick', [{
	        isActive: main_core.Dom.hasClass(this.starContNode, 'ui-toolbar-star-active'),
	        context: window,
	        pageTitle,
	        pageLink
	      }]);
	    });
	  }
	  onStandardItemChangedSuccess(params) {
	    if (!main_core.Type.isBoolean(params.isActive) || !this.starContNode || params.context && params.context !== window) {
	      return;
	    }
	    if (params.isActive) {
	      this.showMessage(main_core.Loc.getMessage('UI_TOOLBAR_ITEM_WAS_ADDED_TO_LEFT'));
	      this.starContNode.title = main_core.Loc.getMessage('UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU');
	      main_core.Dom.addClass(this.starContNode, 'ui-toolbar-star-active');
	    } else {
	      this.showMessage(main_core.Loc.getMessage('UI_TOOLBAR_ITEM_WAS_DELETED_FROM_LEFT'));
	      this.starContNode.title = main_core.Loc.getMessage('UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU');
	      main_core.Dom.removeClass(this.starContNode, 'ui-toolbar-star-active');
	    }
	  }
	  showMessage(message) {
	    let popup = main_popup.PopupWindowManager.create('left-menu-message', this.starContNode, {
	      content: message,
	      darkMode: true,
	      offsetTop: 2,
	      offsetLeft: 0,
	      angle: true,
	      autoHide: true,
	      events: {
	        onPopupClose: () => {
	          if (popup) {
	            popup.destroy();
	            popup = null;
	          }
	        }
	      }
	    });
	    popup.show();
	    setTimeout(() => {
	      if (popup) {
	        popup.destroy();
	        popup = null;
	      }
	    }, 3000);
	  }
	}

	const ToolbarEvents = {
	  beforeStartEditing: 'beforeStartEditing',
	  startEditing: 'startEditing',
	  finishEditing: 'finishEditing'
	};
	var _copyLinkButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyLinkButton");
	var _titleEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("titleEditor");
	var _getClickOnCopyLinkButtonHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getClickOnCopyLinkButtonHandler");
	var _initTitleEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initTitleEditor");
	class Toolbar extends main_core.Event.EventEmitter {
	  // eslint-disable-next-line sonarjs/cognitive-complexity
	  constructor(_options = {}) {
	    var _options$titleEditor;
	    super(_options);
	    Object.defineProperty(this, _initTitleEditor, {
	      value: _initTitleEditor2
	    });
	    Object.defineProperty(this, _getClickOnCopyLinkButtonHandler, {
	      value: _getClickOnCopyLinkButtonHandler2
	    });
	    Object.defineProperty(this, _copyLinkButton, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _titleEditor, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.UI.Toolbar');
	    this.titleMinWidth = main_core.Type.isNumber(_options.titleMinWidth) ? _options.titleMinWidth : 158;
	    this.titleMaxWidth = main_core.Type.isNumber(_options.titleMaxWidth) ? _options.titleMaxWidth : '';
	    this.filterMinWidth = main_core.Type.isNumber(_options.filterMinWidth) ? _options.filterMinWidth : 300;
	    this.filterMaxWidth = main_core.Type.isNumber(_options.filterMaxWidth) ? _options.filterMaxWidth : 748;
	    this.id = main_core.Type.isStringFilled(_options.id) ? _options.id : main_core.Text.getRandom();
	    this.toolbarContainer = _options.target;
	    if (!main_core.Type.isDomNode(this.toolbarContainer)) {
	      throw new Error('BX.UI.Toolbar: "target" parameter is required.');
	    }
	    this.titleContainer = this.toolbarContainer.querySelector('.ui-toolbar-title-box');
	    this.filterContainer = this.toolbarContainer.querySelector('.ui-toolbar-filter-box');
	    this.filterButtons = this.toolbarContainer.querySelector('.ui-toolbar-filter-buttons');
	    this.rightButtons = this.toolbarContainer.querySelector('.ui-toolbar-right-buttons');
	    this.afterTitleButtons = this.toolbarContainer.querySelector('.ui-toolbar-after-title-buttons');
	    babelHelpers.classPrivateFieldLooseBase(this, _copyLinkButton)[_copyLinkButton] = this.toolbarContainer.querySelector('#ui-toolbar-copy-link-button');
	    if (babelHelpers.classPrivateFieldLooseBase(this, _copyLinkButton)[_copyLinkButton]) {
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _copyLinkButton)[_copyLinkButton], 'click', babelHelpers.classPrivateFieldLooseBase(this, _getClickOnCopyLinkButtonHandler)[_getClickOnCopyLinkButtonHandler]());
	    }
	    if (!this.filterContainer) {
	      this.filterMinWidth = 0;
	      this.filterMaxWidth = 0;
	    }
	    this.buttons = Object.create(null);
	    this.buttonIds = main_core.Type.isArray(_options.buttonIds) ? _options.buttonIds : [];
	    if (this.buttonIds.length > 0) {
	      this.buttonIds.forEach(buttonId => {
	        const button = ui_buttons.ButtonManager.createByUniqId(buttonId);
	        if (button) {
	          const container = button.getContainer();
	          container.originalWidth = container.offsetWidth;
	          if (!button.getIcon() && !main_core.Type.isStringFilled(button.getDataSet().toolbarCollapsedIcon)) {
	            if (button.getColor() === ui_buttons.ButtonColor.PRIMARY) {
	              button.setDataSet({
	                toolbarCollapsedIcon: ui_buttons.ButtonIcon.ADD
	              });
	            } else {
	              console.warn(`BX.UI.Toolbar: the button "${button.getText()}" doesn't have an icon for collapsed mode. ` + 'Use the "data-toolbar-collapsed-icon" attribute.');
	            }
	          }
	          this.buttons[buttonId] = button;
	        } else {
	          console.warn(`BX.UI.Toolbar: the button "${buttonId}" wasn't initialized.`);
	        }
	      });
	    }
	    this.windowWidth = document.body.offsetWidth;
	    this.reduceItemsWidth();
	    main_core.bind(window, 'resize', () => {
	      if (this.isWindowIncreased()) {
	        this.increaseItemsWidth();
	      } else {
	        this.reduceItemsWidth();
	      }
	    });
	    if (((_options$titleEditor = _options.titleEditor) == null ? void 0 : _options$titleEditor.active) === true) {
	      babelHelpers.classPrivateFieldLooseBase(this, _titleEditor)[_titleEditor] = babelHelpers.classPrivateFieldLooseBase(this, _initTitleEditor)[_initTitleEditor](_options.titleEditor);
	    }
	  }
	  getButtons() {
	    return this.buttons;
	  }
	  getButton(id) {
	    return id in this.buttons ? this.buttons[id] : null;
	  }
	  getId() {
	    return this.id;
	  }
	  isWindowIncreased() {
	    const previous = this.windowWidth;
	    const current = document.body.offsetWidth;
	    this.windowWidth = current;
	    return current > previous;
	  }
	  getContainerSize() {
	    return this.toolbarContainer.offsetWidth;
	  }
	  getInnerTotalWidth() {
	    return this.toolbarContainer.scrollWidth;
	  }
	  reduceItemsWidth() {
	    if (this.getInnerTotalWidth() <= this.getContainerSize()) {
	      return;
	    }
	    const buttons = Object.values(this.getButtons()).reverse();
	    for (const button of buttons) {
	      var _button$getDataSet;
	      if (!button.getIcon() && !main_core.Type.isStringFilled((_button$getDataSet = button.getDataSet()) == null ? void 0 : _button$getDataSet.toolbarCollapsedIcon)) {
	        continue;
	      }
	      if (button.isCollapsed()) {
	        continue;
	      }
	      button.setCollapsed(true);
	      if (!button.getIcon()) {
	        button.setIcon(button.getDataSet().toolbarCollapsedIcon);
	      }
	      if (this.getInnerTotalWidth() <= this.getContainerSize()) {
	        return;
	      }
	    }
	  }
	  increaseItemsWidth() {
	    const buttons = Object.values(this.getButtons());
	    for (const button of buttons) {
	      var _this$afterTitleButto, _this$filterButtons, _this$rightButtons;
	      const item = button.getContainer();
	      if (!button.isCollapsed()) {
	        continue;
	      }
	      const newInnerWidth = this.titleMinWidth + this.filterMinWidth + (((_this$afterTitleButto = this.afterTitleButtons) == null ? void 0 : _this$afterTitleButto.offsetWidth) || 0) + (((_this$filterButtons = this.filterButtons) == null ? void 0 : _this$filterButtons.offsetWidth) || 0) + (((_this$rightButtons = this.rightButtons) == null ? void 0 : _this$rightButtons.offsetWidth) || 0) + (item.originalWidth - item.offsetWidth);
	      if (newInnerWidth > this.getContainerSize()) {
	        break;
	      }
	      button.setCollapsed(false);
	      if (button.getIcon() === button.getDataSet().toolbarCollapsedIcon) {
	        const icon = main_core.Type.isStringFilled(button.options.icon) ? button.options.icon : null;
	        button.setIcon(icon);
	      }
	    }
	  }
	  setTitle(title) {
	    if (!this.titleContainer) {
	      return;
	    }
	    const pagetitle = this.titleContainer.querySelector('#pagetitle');
	    if (pagetitle) {
	      pagetitle.textContent = title;
	    }
	  }
	  getContainer() {
	    return this.toolbarContainer;
	  }
	  getRightButtonsContainer() {
	    return this.rightButtons;
	  }
	  getTitleEditor() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _titleEditor)[_titleEditor];
	  }
	}
	function _getClickOnCopyLinkButtonHandler2() {
	  let popup = null;
	  return () => {
	    if (popup !== null) {
	      return;
	    }
	    const dataLink = main_core.Dom.attr(babelHelpers.classPrivateFieldLooseBase(this, _copyLinkButton)[_copyLinkButton], 'data-link');
	    const currentPageLink = window.location.href;
	    let linkToCopy = main_core.Type.isStringFilled(dataLink) ? dataLink : currentPageLink;
	    linkToCopy = main_core.Uri.removeParam(linkToCopy, ['IFRAME', 'IFRAME_TYPE']);
	    const message = main_core.Dom.attr(babelHelpers.classPrivateFieldLooseBase(this, _copyLinkButton)[_copyLinkButton], 'data-message');
	    popup = new main_popup.Popup({
	      bindElement: babelHelpers.classPrivateFieldLooseBase(this, _copyLinkButton)[_copyLinkButton],
	      angle: true,
	      darkMode: true,
	      content: message,
	      autoHide: true,
	      cacheable: false
	    });
	    popup.setOffset({
	      offsetLeft: main_core.Dom.getPosition(babelHelpers.classPrivateFieldLooseBase(this, _copyLinkButton)[_copyLinkButton]).width / 2
	    });
	    popup.show();
	    BX.clipboard.copy(linkToCopy);
	    setTimeout(() => {
	      popup = null;
	    }, 1000);
	  };
	}
	function _initTitleEditor2(options) {
	  const titleEditorOptions = main_core.Type.isPlainObject(options) ? options : {};
	  const titleEditor = new TitleEditor({
	    ...titleEditorOptions
	  });
	  titleEditor.subscribe(TitleEditorEvents.beforeStartEditing, editorEvent => {
	    const toolbarEvent = new main_core.Event.BaseEvent();
	    this.emit(TitleEditorEvents.beforeStartEditing, toolbarEvent);
	    if (toolbarEvent.isDefaultPrevented()) {
	      editorEvent.preventDefault();
	    }
	  });
	  titleEditor.subscribe(TitleEditorEvents.startEditing, () => {
	    this.emit(TitleEditorEvents.startEditing);
	  });
	  titleEditor.subscribe(TitleEditorEvents.finishEditing, event => {
	    const updatedTitle = event.getData().updatedTitle;
	    this.emit(TitleEditorEvents.finishEditing, {
	      updatedTitle
	    });
	  });
	  return titleEditor;
	}
	Toolbar.TitleEditor = TitleEditor;
	Toolbar.Star = ToolbarStar;

	class Manager {
	  constructor() {
	    this.toolbars = {};
	  }
	  create(options) {
	    const toolbar = new Toolbar(options);
	    if (this.get(toolbar.getId())) {
	      throw new Error("The toolbar instance with the same 'id' already exists.");
	    }
	    this.toolbars[toolbar.getId()] = toolbar;
	    return toolbar;
	  }
	  getDefaultToolbar() {
	    return this.get('default-toolbar');
	  }
	  get(id) {
	    return id in this.toolbars ? this.toolbars[id] : null;
	  }
	}
	const ToolbarManager = new Manager();

	exports.ToolbarManager = ToolbarManager;
	exports.ToolbarEvents = ToolbarEvents;
	exports.Toolbar = Toolbar;
	exports.ToolbarStar = ToolbarStar;

}((this.BX.UI = this.BX.UI || {}),BX.UI,BX,BX.Main));
//# sourceMappingURL=ui.toolbar.bundle.js.map
