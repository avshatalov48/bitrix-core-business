/* eslint-disable */
this.BX = this.BX || {};
this.BX.Report = this.BX.Report || {};
(function (exports,main_core) {
	'use strict';

	var _toolbar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toolbar");
	/**
	 * @memberOf BX.Report.Integration
	 */
	class Toolbar {
	  constructor(params) {
	    Object.defineProperty(this, _toolbar, {
	      writable: true,
	      value: void 0
	    });
	    const objectParams = main_core.Type.isPlainObject(params) ? params : {};
	    if (main_core.Type.isStringFilled(objectParams.toolbarId)) {
	      var _BX, _BX$UI, _BX$UI$ToolbarManager;
	      babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar] = (_BX = BX) == null ? void 0 : (_BX$UI = _BX.UI) == null ? void 0 : (_BX$UI$ToolbarManager = _BX$UI.ToolbarManager) == null ? void 0 : _BX$UI$ToolbarManager.get(objectParams.toolbarId);
	    } else {
	      var _BX2, _BX2$UI, _BX2$UI$ToolbarManage;
	      babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar] = (_BX2 = BX) == null ? void 0 : (_BX2$UI = _BX2.UI) == null ? void 0 : (_BX2$UI$ToolbarManage = _BX2$UI.ToolbarManager) == null ? void 0 : _BX2$UI$ToolbarManage.getDefaultToolbar();
	    }
	  }
	  setTitle(title) {
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
	  getContainer() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar]) {
	      return null;
	    }
	    if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getContainer)) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getContainer();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].toolbarContainer;
	  }
	  getRightButtonsContainer() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar]) {
	      return null;
	    }
	    if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getRightButtonsContainer)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].getRightButtonsContainer();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar].rightButtons;
	  }
	  cleanContent() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _toolbar)[_toolbar]) {
	      return;
	    }
	    this.setTitle('');
	    main_core.Dom.clean(this.getRightButtonsContainer());
	  }
	  createSkeleton() {
	    var _this$getContainer;
	    return main_core.Dom.create('img', {
	      attrs: {
	        src: '/bitrix/images/report/visualconstructor/preview/view-without-menu.svg',
	        height: '1200px',
	        width: '100%'
	      },
	      style: {
	        'margin-top': `-${(_this$getContainer = this.getContainer()) == null ? void 0 : _this$getContainer.offsetHeight}px`
	      }
	    });
	  }
	}

	exports.Toolbar = Toolbar;

}((this.BX.Report.Integration = this.BX.Report.Integration || {}),BX));
//# sourceMappingURL=toolbar.bundle.js.map
