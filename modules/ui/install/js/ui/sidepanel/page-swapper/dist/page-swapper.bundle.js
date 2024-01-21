/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events,ui_iconSet_api_core,ui_iconSet_actions,main_loader) {
	'use strict';

	let _ = t => t,
	  _t;
	var _disableClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disableClass");
	var _setPrevButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setPrevButton");
	var _setNextButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setNextButton");
	var _setButtonHref = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setButtonHref");
	var _toggleButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleButton");
	var _renderWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderWrapper");
	var _activateOverlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("activateOverlay");
	var _setNeighboursHref = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setNeighboursHref");
	var _addListenerToButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addListenerToButton");
	var _isAnyPageSet = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAnyPageSet");
	class PageSwapper extends main_core_events.EventEmitter {
	  constructor(options) {
	    super(options);
	    Object.defineProperty(this, _isAnyPageSet, {
	      value: _isAnyPageSet2
	    });
	    Object.defineProperty(this, _addListenerToButton, {
	      value: _addListenerToButton2
	    });
	    Object.defineProperty(this, _setNeighboursHref, {
	      value: _setNeighboursHref2
	    });
	    Object.defineProperty(this, _activateOverlay, {
	      value: _activateOverlay2
	    });
	    Object.defineProperty(this, _renderWrapper, {
	      value: _renderWrapper2
	    });
	    Object.defineProperty(this, _toggleButton, {
	      value: _toggleButton2
	    });
	    Object.defineProperty(this, _setButtonHref, {
	      value: _setButtonHref2
	    });
	    Object.defineProperty(this, _setNextButton, {
	      value: _setNextButton2
	    });
	    Object.defineProperty(this, _setPrevButton, {
	      value: _setPrevButton2
	    });
	    Object.defineProperty(this, _disableClass, {
	      writable: true,
	      value: 'ui-swap-btn-disabled'
	    });
	    this.btnSize = 20;
	    this.setEventNamespace('BX.UI.Sidepanel.PageSwapper');
	    this.slider = options.slider || null;
	    this.container = options.container || null;
	    this.pagesHref = options.pagesHref || null;
	    this.useLoader = options.useLoader || false;
	    this.pageType = options.pageType || 'default';
	  }
	  init() {
	    if (!this.slider) {
	      console.warn('BX.UI.SliderPageSwapper.Preview: \'slider\' is not defined');
	      return;
	    }
	    if (!this.container) {
	      console.warn('BX.UI.SliderPageSwapper.Preview: \'container\' is not defined');
	      return;
	    }
	    this.window = this.slider.getFrameWindow();
	    this.curHref = this.slider.url;
	    this.pageId = this.slider.getData().get('pageId');
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isAnyPageSet)[_isAnyPageSet]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setNeighboursHref)[_setNeighboursHref]();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _setPrevButton)[_setPrevButton]();
	    babelHelpers.classPrivateFieldLooseBase(this, _setNextButton)[_setNextButton]();
	    this.setTitles(this.pageType);
	    this.getWrapper();
	    babelHelpers.classPrivateFieldLooseBase(this, _renderWrapper)[_renderWrapper]();
	  }
	  setPrevPage(prevPageId = null, prevPageHref = null) {
	    if (prevPageId) {
	      this.prevPageId = prevPageId;
	    }
	    if (prevPageHref) {
	      this.prevPageHref = prevPageHref;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _setButtonHref)[_setButtonHref](this.getPrevButton(), this.prevPageId, this.prevPageHref);
	  }
	  setNextPage(nextPageId = null, nextPageHref = null) {
	    if (nextPageId) {
	      this.nextPageId = nextPageId;
	    }
	    if (nextPageHref) {
	      this.nextPageHref = nextPageHref;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _setButtonHref)[_setButtonHref](this.getNextButton(), this.nextPageId, this.nextPageHref);
	  }
	  updatePagesHref(pagesHref) {
	    this.showLoader();
	    babelHelpers.classPrivateFieldLooseBase(this, _setNeighboursHref)[_setNeighboursHref](pagesHref);
	    this.setPrevPage();
	    this.setNextPage();
	    this.hideLoader();
	  }
	  getPrevButton() {
	    return this.prevBtn;
	  }
	  getNextButton() {
	    return this.nextBtn;
	  }
	  getWrapper() {
	    if (!this.wrapper) {
	      this.wrapper = main_core.Tag.render(_t || (_t = _`
				<div class='ui-page-swapper'>
					${0}
					${0}
				</div>
			`), this.getPrevButton(), this.getNextButton());
	    }
	    return this.wrapper;
	  }
	  hasPagesBeforeEnd(pagesBeforeEnd = 0) {
	    if (pagesBeforeEnd === 0) {
	      return !main_core.Type.isUndefined(this.nextPageHref) && !main_core.Type.isNull(this.nextPageHref);
	    }
	    if (pagesBeforeEnd > 0 && main_core.Type.isNumber(pagesBeforeEnd)) {
	      let check = null;
	      Object.keys(this.pagesHref).forEach(key => {
	        if (Number(this.pagesHref[key].ID) === this.pageId) {
	          var _this$pagesHref;
	          check = ((_this$pagesHref = this.pagesHref[Number(key) + pagesBeforeEnd]) == null ? void 0 : _this$pagesHref.HREF) || null;
	        }
	      });
	      return !main_core.Type.isNull(check);
	    }
	    return false;
	  }
	  showLoader() {
	    if (this.loader && !this.loader.isShown()) {
	      this.loader.show();
	      main_core.Dom.style(this.getPrevButton(), 'visibility', 'hidden');
	      main_core.Dom.style(this.getNextButton(), 'visibility', 'hidden');
	    }
	  }
	  hideLoader() {
	    if (this.loader && this.loader.isShown()) {
	      this.loader.hide();
	      main_core.Dom.style(this.getPrevButton(), 'visibility', 'visible');
	      main_core.Dom.style(this.getNextButton(), 'visibility', 'visible');
	    }
	  }
	  setTitles(type) {
	    if (type === 'mail') {
	      this.prevBtn.setAttribute('title', main_core.Loc.getMessage('UI_SIDEPANEL_PAGE_SWAPPER_PREVIOUS_MAIL_MESSAGE'));
	      this.nextBtn.setAttribute('title', main_core.Loc.getMessage('UI_SIDEPANEL_PAGE_SWAPPER_NEXT_MAIL_MESSAGE'));
	    }
	  }
	}
	function _setPrevButton2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Actions.CHEVRON_LEFT,
	    size: this.btnSize
	  });
	  this.prevBtn = icon.render();
	  main_core.Dom.addClass(this.getPrevButton(), 'ui-page-swap-left');
	  babelHelpers.classPrivateFieldLooseBase(this, _setButtonHref)[_setButtonHref](this.getPrevButton(), this.prevPageId, this.prevPageHref);
	}
	function _setNextButton2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Actions.CHEVRON_RIGHT,
	    size: this.btnSize
	  });
	  this.nextBtn = icon.render();
	  main_core.Dom.addClass(this.getNextButton(), 'ui-page-swap-right');
	  babelHelpers.classPrivateFieldLooseBase(this, _setButtonHref)[_setButtonHref](this.getNextButton(), this.nextPageId, this.nextPageHref);
	}
	function _setButtonHref2(button, pageId = null, pageHref = null) {
	  if (pageId && pageHref) {
	    babelHelpers.classPrivateFieldLooseBase(this, _addListenerToButton)[_addListenerToButton](button, pageId, pageHref);
	    this.hideLoader();
	  } else {
	    main_core.Event.unbindAll(button, 'click');
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _toggleButton)[_toggleButton](button, pageId, pageHref);
	}
	function _toggleButton2(button, pageId, pageHref) {
	  if (main_core.Dom.hasClass(button, babelHelpers.classPrivateFieldLooseBase(this, _disableClass)[_disableClass]) && pageId && pageHref) {
	    main_core.Dom.removeClass(button, babelHelpers.classPrivateFieldLooseBase(this, _disableClass)[_disableClass]);
	    main_core.Dom.style(button, 'cursor', 'pointer');
	  } else if (!main_core.Dom.hasClass(button, babelHelpers.classPrivateFieldLooseBase(this, _disableClass)[_disableClass]) && !(pageId && pageHref)) {
	    main_core.Dom.addClass(button, babelHelpers.classPrivateFieldLooseBase(this, _disableClass)[_disableClass]);
	    main_core.Dom.style(button, 'cursor', 'not-allowed');
	  }
	}
	function _renderWrapper2() {
	  main_core.Dom.append(this.getWrapper(), this.container);
	  this.loader = new main_loader.Loader({
	    target: this.getWrapper(),
	    size: 20,
	    mode: 'absolute'
	  });
	  if (this.useLoader && !babelHelpers.classPrivateFieldLooseBase(this, _isAnyPageSet)[_isAnyPageSet]()) {
	    this.showLoader();
	  } else {
	    this.hideLoader();
	  }
	}
	function _activateOverlay2() {
	  const loader = this.slider.layout.loader;
	  if (loader) {
	    main_core.Dom.style(loader, 'opacity', 0.5);
	    main_core.Dom.style(loader, 'display', 'block');
	  }
	}
	function _setNeighboursHref2(pagesHref = null) {
	  if (pagesHref) {
	    this.pagesHref = pagesHref;
	  }
	  if (!this.pagesHref) {
	    return;
	  }
	  if (!this.pageId) {
	    this.pagesHref.forEach(page => {
	      if (page.HREF.includes(this.curHref)) {
	        this.pageId = Number(page.ID);
	      }
	    });
	  }
	  this.prevPageId = null;
	  this.prevPageHref = null;
	  this.nextPageId = null;
	  this.nextPageHref = null;
	  if (!this.pageId) {
	    return;
	  }
	  Object.keys(this.pagesHref).forEach(key => {
	    if (Number(this.pagesHref[key].ID) === this.pageId) {
	      var _this$pagesHref2, _this$pagesHref3, _this$pagesHref4, _this$pagesHref5;
	      this.prevPageId = Number((_this$pagesHref2 = this.pagesHref[key - 1]) == null ? void 0 : _this$pagesHref2.ID) || null;
	      this.prevPageHref = ((_this$pagesHref3 = this.pagesHref[key - 1]) == null ? void 0 : _this$pagesHref3.HREF) || null;
	      this.nextPageId = Number((_this$pagesHref4 = this.pagesHref[Number(key) + 1]) == null ? void 0 : _this$pagesHref4.ID) || null;
	      this.nextPageHref = ((_this$pagesHref5 = this.pagesHref[Number(key) + 1]) == null ? void 0 : _this$pagesHref5.HREF) || null;
	    }
	  });
	}
	function _addListenerToButton2(button, pageId, pageHref) {
	  main_core.Event.bind(button, 'click', () => {
	    this.slider.getData().set('pageId', pageId);
	    const url = new URL(pageHref, window.location);
	    url.searchParams.append('IFRAME_TYPE', 'SIDE_SLIDER');
	    url.searchParams.append('IFRAME', 'Y');
	    babelHelpers.classPrivateFieldLooseBase(this, _activateOverlay)[_activateOverlay]();
	    this.window.location.href = url;
	  });
	}
	function _isAnyPageSet2() {
	  return this.prevPageId && this.prevPageHref || this.nextPageId && this.nextPageHref;
	}

	exports.PageSwapper = PageSwapper;

}((this.BX.UI.SidePanel = this.BX.UI.SidePanel || {}),BX,BX.Event,BX.UI.IconSet,BX,BX));
//# sourceMappingURL=page-swapper.bundle.js.map
