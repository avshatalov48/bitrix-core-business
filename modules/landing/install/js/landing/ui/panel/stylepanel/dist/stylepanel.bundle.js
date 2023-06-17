this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_loader,landing_ui_panel_content,landing_loc,landing_pageobject) {
	'use strict';

	var showPseudoContent = Symbol('showPseudoContent');
	var hidePseudoContent = Symbol('hidePseudoContent');
	var disableEditorPointerEvents = Symbol('disableEditorPointerEvents');
	var enableEditorPointerEvents = Symbol('enableEditorPointerEvents');

	/**
	 * @memberOf BX.Landing.UI.Panel
	 */
	var StylePanel = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(StylePanel, _Content);
	  function StylePanel() {
	    var _this;
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, StylePanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StylePanel).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "shouldAdjustTopPanelControls", false);
	    _this.setEventNamespace('BX.Landing.UI.Panel.StylePanel');
	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_DESIGN_PANEL_HEADER'));
	    _this.pseudoContent = main_core.Runtime.clone(_this.content);
	    _this.loader = new main_loader.Loader({
	      target: _this.pseudoContent,
	      offset: {
	        top: '-10%'
	      }
	    });
	    _this.lsCache = new main_core.Cache.LocalStorageCache();
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.switcher = _this.getSwitcher();
	    _this.closeByEsc = false;
	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-style');
	    main_core.Dom.addClass(_this.overlay, 'landing-ui-panel-style-overlay');
	    main_core.Dom.attr(_this.layout, 'hidden', 'true');
	    main_core.Dom.clean(_this.pseudoContent);
	    main_core.Dom.style(_this.pseudoContent, 'margin-left', '20px');
	    main_core.Dom.append(_this.pseudoContent, _this.body);
	    main_core.Dom.append(_this.switcher.layout, _this.footer);
	    main_core.Dom.prepend(_this.layout, _this.getViewContainer());
	    if (window.localStorage) {
	      var state = window.localStorage.getItem('selectGroup') === 'true';
	      _this.lsCache.set('selectGroup', state.toString());
	    }
	    return _this;
	  }
	  babelHelpers.createClass(StylePanel, [{
	    key: "getSwitcher",
	    value: function getSwitcher() {
	      var _this2 = this;
	      return this.cache.remember('switcher', function () {
	        return new BX.Landing.UI.Field.Switch({
	          title: landing_loc.Loc.getMessage('LANDING_STYLE_PANEL_SELECT_GROUP_SWITCH'),
	          onValueChange: function onValueChange() {
	            if (window.localStorage) {
	              window.localStorage.setItem('selectGroup', _this2.switcher.getValue().toString());
	            }
	            _this2.lsCache.set('selectGroup', _this2.switcher.getValue().toString());
	          },
	          value: main_core.Text.toBoolean(_this2.lsCache.get('selectGroup'))
	        });
	      });
	    }
	  }, {
	    key: "getViewContainer",
	    value: function getViewContainer() {
	      return this.cache.remember('viewContainer', function () {
	        return landing_pageobject.PageObject.getRootWindow().document.querySelector('.landing-ui-view-container');
	      });
	    }
	  }, {
	    key: "getViewWrapper",
	    value: function getViewWrapper() {
	      var _this3 = this;
	      return this.cache.remember('viewWrapper', function () {
	        return _this3.getViewContainer().querySelector('.landing-ui-view-wrapper');
	      });
	    }
	  }, {
	    key: showPseudoContent,
	    value: function value() {
	      main_core.Dom.attr(this.content, 'hidden', true);
	      main_core.Dom.attr(this.pseudoContent, 'hidden', null);
	    }
	  }, {
	    key: hidePseudoContent,
	    value: function value() {
	      main_core.Dom.attr(this.content, 'hidden', null);
	      main_core.Dom.attr(this.pseudoContent, 'hidden', true);
	    }
	  }, {
	    key: "show",
	    value: function show(formMode) {
	      var _this4 = this;
	      this[showPseudoContent]();
	      StylePanel[disableEditorPointerEvents]();
	      if (formMode) {
	        if (!main_core.Dom.hasClass(this.layout, 'landing-ui-style-form-mode')) {
	          main_core.Dom.addClass(this.layout, 'landing-ui-style-form-mode');

	          /*Dom.style(this.overlay, {
	          	'z-index': 9998,
	          	width: '880px',
	          });*/
	          main_core.Dom.insertAfter(this.overlay, this.layout);
	          main_core.Dom.removeClass(this.overlay, 'landing-ui-panel-style-overlay');
	        }
	      } else {
	        //Dom.remove(this.overlay);
	        main_core.Dom.addClass(this.overlay, 'landing-ui-panel-style-overlay');
	        main_core.Dom.removeClass(this.layout, 'landing-ui-style-form-mode');
	      }
	      main_core.Dom.remove(this.overlay);
	      return babelHelpers.get(babelHelpers.getPrototypeOf(StylePanel.prototype), "show", this).call(this).then(function () {
	        _this4.loader.show();
	        setTimeout(function () {
	          _this4[hidePseudoContent]();
	          StylePanel[enableEditorPointerEvents]();
	        }, 300);
	        if (!formMode) {
	          main_core.Dom.style(_this4.getViewWrapper(), 'max-width', 'calc(100% - 320px)');
	          main_core.Dom.addClass(document.body, 'landing-ui-collapsed');
	        }
	        BX.onCustomEvent('BX.Landing.Style:enable', []);
	        _this4.emit('enable', {
	          panel: _this4
	        });
	        return _this4;
	      });
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      var _this5 = this;
	      StylePanel[disableEditorPointerEvents]();
	      main_core.Dom.style(this.getViewWrapper(), 'max-width', null);
	      return babelHelpers.get(babelHelpers.getPrototypeOf(StylePanel.prototype), "hide", this).call(this).then(function () {
	        StylePanel[enableEditorPointerEvents]();
	        main_core.Dom.addClass(document.body, 'landing-ui-collapsed');
	        BX.onCustomEvent('BX.Landing.Style:disable', []);
	        _this5.emit('disable', {
	          panel: _this5
	        });
	        return _this5;
	      });
	    }
	  }, {
	    key: "prepareFooter",
	    value: function prepareFooter() {
	      var isMultiSelector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      if (isMultiSelector) {
	        this.footer.hidden = false;
	        main_core.Dom.removeClass(this.body, 'landing-ui-panel-content-body_long');
	      } else {
	        this.footer.hidden = true;
	        main_core.Dom.addClass(this.body, 'landing-ui-panel-content-body_long');
	      }
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      if (!rootWindow.BX.Landing.UI.Panel.StylePanel.instance && !StylePanel.instance) {
	        rootWindow.BX.Landing.UI.Panel.StylePanel.instance = new StylePanel();
	      }
	      return rootWindow.BX.Landing.UI.Panel.StylePanel.instance || StylePanel.instance;
	    }
	  }, {
	    key: enableEditorPointerEvents,
	    value: function value() {
	      main_core.Dom.style(document.body, 'pointer-events', null);
	    }
	  }, {
	    key: disableEditorPointerEvents,
	    value: function value() {
	      main_core.Dom.style(document.body, 'pointer-events', 'none');
	    }
	  }]);
	  return StylePanel;
	}(landing_ui_panel_content.Content);

	exports.StylePanel = StylePanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX,BX,BX.Landing.UI.Panel,BX.Landing,BX.Landing));
//# sourceMappingURL=stylepanel.bundle.js.map
