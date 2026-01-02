/* eslint-disable */
this.BX = this.BX || {};
this.BX.Report = this.BX.Report || {};
(function (exports,main_core,main_core_events,main_loader) {
	'use strict';

	/**
	 * @memberOf BX.Report.Analytics
	 */
	var _toolbar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toolbar");
	var _setToolbarTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setToolbarTitle");
	var _getToolbarContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getToolbarContainer");
	var _getToolbarRightButtonsContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getToolbarRightButtonsContainer");
	class Page {
	  constructor(options) {
	    var _BX$UI, _BX$UI$ToolbarManager;
	    Object.defineProperty(this, _getToolbarRightButtonsContainer, {
	      value: _getToolbarRightButtonsContainer2
	    });
	    Object.defineProperty(this, _getToolbarContainer, {
	      value: _getToolbarContainer2
	    });
	    Object.defineProperty(this, _setToolbarTitle, {
	      value: _setToolbarTitle2
	    });
	    Object.defineProperty(this, _toolbar, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar] = (_BX$UI = BX.UI) == null ? void 0 : (_BX$UI$ToolbarManager = _BX$UI.ToolbarManager) == null ? void 0 : _BX$UI$ToolbarManager.getDefaultToolbar();
	    this.scope = options.scope;
	    this.menuScope = options.menuScope;
	    this.changeBoardButtons = this.menuScope.querySelectorAll('[data-role="report-analytics-menu-item"]');
	    this.contentContainer = this.scope.querySelector('.report-analytics-content');
	    this.currentItem = this.menuScope.querySelector('.report-analytics-sidebar-submenu-item.report-analytics-sidebar-submenu-item-active');
	    this.pageControlsContainer = babelHelpers.classPrivateFieldLooseBase(this, _getToolbarRightButtonsContainer)[_getToolbarRightButtonsContainer]();
	    this.defaultBoardKey = options.defaultBoardKey;
	    this.defaultBoardTitle = options.defaultBoardTitle;
	    this.currentPageTitle = top.document.title;
	    this.init();
	  }
	  init() {
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', () => {
	      this.sliderCloseHandler();
	    });
	    top.document.title = this.defaultBoardTitle;
	    this.changeBoardButtons.forEach(button => {
	      main_core.Event.bind(button, 'click', this.handleItemClick.bind(this));
	    });
	    this.loader = new main_loader.Loader({
	      size: 80
	    });
	    top.onpopstate = this.handlerOnPopState.bind(this);
	    this.openBoardWithKey(this.defaultBoardKey);
	    const activeButton = [...this.changeBoardButtons].find(button => {
	      return button.dataset.reportBoardKey === this.defaultBoardKey;
	    });
	    this.tryOpenExternalUrl(activeButton);
	  }
	  tryOpenExternalUrl(button) {
	    if (main_core.Type.isElementNode(button) && button.dataset.isExternal === 'Y') {
	      if (button.dataset.isSliderSupport === 'N') {
	        this.openExternalUrlInNewTab(button.dataset.externalUrl);
	      } else {
	        this.openExternalUrl(button.dataset.externalUrl, button.dataset.sliderLoader);
	      }
	      return true;
	    }
	    return false;
	  }
	  handleItemClick(event) {
	    event.preventDefault();
	    const button = event.currentTarget;
	    this.activateButton(event);
	    if (!this.tryOpenExternalUrl(button)) {
	      this.openBoardWithKey(button.dataset.reportBoardKey, button.href);
	    }
	  }
	  openExternalUrl(url, loader = 'report:analytics') {
	    BX.SidePanel.Instance.open(url, {
	      cacheable: false,
	      loader
	    });
	  }
	  openExternalUrlInNewTab(url) {
	    window.open(url, '_blank');
	  }
	  openBoardWithKey(boardKey, urlForHistory) {
	    this.cleanPageContent();
	    this.showLoader();
	    BX.Report.VC.Core.abortAllRunningRequests();
	    BX.Report.VC.Core.ajaxPost('analytics.getBoardComponentByKey', {
	      data: {
	        IFRAME: 'Y',
	        boardKey
	      },
	      analyticsLabel: {
	        boardKey
	      },
	      onFullSuccess: function (result) {
	        this.hideLoader();
	        if (String(urlForHistory) !== '') {
	          top.history.pushState(null, result.additionalParams.pageTitle, urlForHistory);
	          top.history.replaceState({
	            reportBoardKey: boardKey,
	            href: urlForHistory
	          }, result.additionalParams.pageTitle, urlForHistory);
	        }
	        this.changePageTitle(result.additionalParams.pageTitle);
	        this.changePageControls(result.additionalParams.pageControlsParams);
	        main_core.Runtime.html(this.contentContainer, result.data);
	      }.bind(this)
	    });
	  }
	  cleanPageContent() {
	    var _BX$Report, _BX$Report$Dashboard, _BX$Report$Dashboard$, _BX$VisualConstructor, _BX$VisualConstructor2;
	    babelHelpers.classPrivateFieldLooseBase(this, _setToolbarTitle)[_setToolbarTitle]('');
	    main_core.Dom.clean(this.pageControlsContainer);
	    main_core.Dom.clean(this.contentContainer);
	    (_BX$Report = BX.Report) == null ? void 0 : (_BX$Report$Dashboard = _BX$Report.Dashboard) == null ? void 0 : (_BX$Report$Dashboard$ = _BX$Report$Dashboard.BoardRepository) == null ? void 0 : _BX$Report$Dashboard$.destroyBoards();
	    (_BX$VisualConstructor = BX.VisualConstructor) == null ? void 0 : (_BX$VisualConstructor2 = _BX$VisualConstructor.BoardRepository) == null ? void 0 : _BX$VisualConstructor2.destroyBoards();
	  }
	  changePageControls(controlsContent) {
	    const config = {};
	    config.onFullSuccess = function (result) {
	      main_core.Runtime.html(this.pageControlsContainer, result.html);
	    }.bind(this);
	    BX.Report.VC.Core._successHandler(controlsContent, config);
	  }
	  changePageTitle(title) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setToolbarTitle)[_setToolbarTitle](title);
	    top.document.title = title;
	  }
	  showLoader() {
	    var _babelHelpers$classPr;
	    const preview = main_core.Dom.create('img', {
	      attrs: {
	        src: '/bitrix/images/report/visualconstructor/preview/view-without-menu.svg',
	        height: '1200px',
	        width: '100%'
	      },
	      style: {
	        'margin-top': `-${(_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getToolbarContainer)[_getToolbarContainer]()) == null ? void 0 : _babelHelpers$classPr.offsetHeight}px`
	      }
	    });
	    main_core.Dom.append(preview, this.contentContainer);
	  }
	  hideLoader() {
	    this.loader.hide();
	  }
	  activateButton(event) {
	    const item = event.currentTarget;
	    if (!this.currentItem) {
	      this.currentItem = item;
	    }
	    main_core.Dom.removeClass(this.currentItem, 'report-analytics-sidebar-submenu-item-active');
	    this.currentItem = item;
	    main_core.Dom.addClass(this.currentItem, 'report-analytics-sidebar-submenu-item-active');
	  }
	  handlerOnPopState(event) {
	    let boardKey = this.defaultBoardKey;
	    if (!main_core.Type.isUndefined(event.state.reportBoardKey)) {
	      boardKey = event.state.reportBoardKey;
	    }
	    this.cleanPageContent();
	    this.showLoader();
	    BX.Report.VC.Core.ajaxPost('analytics.getBoardComponentByKey', {
	      data: {
	        IFRAME: 'Y',
	        boardKey
	      },
	      analyticsLabel: {
	        boardKey
	      },
	      onFullSuccess: function (result) {
	        this.hideLoader();
	        this.cleanPageContent();
	        this.changePageTitle(result.additionalParams.pageTitle);
	        this.changePageControls(result.additionalParams.pageControlsParams);
	        main_core.Runtime.html(this.contentContainer, result.data);
	      }.bind(this)
	    });
	  }
	  sliderCloseHandler() {
	    top.document.title = this.currentPageTitle;
	  }

	  // region fallback for 'ui' without recent changes

	  // endregion
	}
	function _setToolbarTitle2(title) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar]) {
	    return;
	  }
	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].setTitle)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].setTitle(title);
	  } else {
	    const pagetitle = babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].titleContainer.querySelector('#pagetitle');
	    if (pagetitle) {
	      pagetitle.textContent = title;
	    }
	  }
	}
	function _getToolbarContainer2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar]) {
	    return null;
	  }
	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getContainer)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getContainer();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].toolbarContainer;
	}
	function _getToolbarRightButtonsContainer2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar]) {
	    return null;
	  }
	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getRightButtonsContainer)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getRightButtonsContainer();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].rightButtons;
	}

	exports.Page = Page;

}((this.BX.Report.Analytics = this.BX.Report.Analytics || {}),BX,BX.Event,BX));
//# sourceMappingURL=script.js.map
