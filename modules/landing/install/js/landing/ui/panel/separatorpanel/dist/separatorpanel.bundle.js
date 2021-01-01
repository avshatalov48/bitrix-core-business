this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_panel_content,landing_pageobject,landing_loc) {
	'use strict';

	var solidLinePreview = "/bitrix/js/landing/ui/panel/separatorpanel/dist/images/solid.png";

	var headerPreview = "/bitrix/js/landing/ui/panel/separatorpanel/dist/images/header.png";

	var pagePreview = "/bitrix/js/landing/ui/panel/separatorpanel/dist/images/page.png";

	/**
	 * @memberOf BX.Landing.UI.Panel
	 */

	var SeparatorPanel = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(SeparatorPanel, _Content);
	  babelHelpers.createClass(SeparatorPanel, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      var rootWindowPanel = rootWindow.BX.Landing.UI.Panel.SeparatorPanel;

	      if (!rootWindowPanel.instance && !SeparatorPanel.instance) {
	        rootWindowPanel.instance = new SeparatorPanel();
	      }

	      return rootWindowPanel.instance || SeparatorPanel.instance;
	    }
	  }]);

	  function SeparatorPanel() {
	    var _this;

	    babelHelpers.classCallCheck(this, SeparatorPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SeparatorPanel).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "adjustActionsPanels", false);

	    _this.setEventNamespace('BX.Landing.UI.Panel.SeparatorPanel');

	    _this.setLayoutClass('landing-ui-panel-separator');

	    _this.setOverlayClass('landing-ui-panel-separator-overlay');

	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_SEPARATOR_PANEL_TITLE'));

	    _this.cache = new main_core.Cache.MemoryCache();

	    _this.renderTo(_this.getViewContainer());

	    _this.appendCard(new BX.Landing.UI.Card.BlockPreviewCard({
	      title: landing_loc.Loc.getMessage('LANDING_SEPARATOR_SOLID_LINE'),
	      image: solidLinePreview,
	      code: 'hr',
	      onClick: _this.onPreviewClick.bind(babelHelpers.assertThisInitialized(_this))
	    }));

	    _this.appendCard(new BX.Landing.UI.Card.BlockPreviewCard({
	      title: landing_loc.Loc.getMessage('LANDING_SEPARATOR_HEADER'),
	      image: headerPreview,
	      code: 'section',
	      onClick: _this.onPreviewClick.bind(babelHelpers.assertThisInitialized(_this))
	    }));

	    _this.appendCard(new BX.Landing.UI.Card.BlockPreviewCard({
	      title: landing_loc.Loc.getMessage('LANDING_SEPARATOR_PAGE'),
	      image: pagePreview,
	      code: 'page',
	      onClick: _this.onPreviewClick.bind(babelHelpers.assertThisInitialized(_this))
	    }));

	    babelHelpers.toConsumableArray(_this.content.children).forEach(function (item) {
	      main_core.Dom.style(item, 'opacity', '1');
	    });
	    return _this;
	  }

	  babelHelpers.createClass(SeparatorPanel, [{
	    key: "getViewContainer",
	    value: function getViewContainer() {
	      return this.cache.remember('viewContainer', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return rootWindow.document.querySelector('.landing-ui-view-container');
	      });
	    }
	  }, {
	    key: "show",
	    value: function show(options) {
	      var _this2 = this;

	      void babelHelpers.get(babelHelpers.getPrototypeOf(SeparatorPanel.prototype), "show", this).call(this, options);
	      return new Promise(function (resolve) {
	        _this2.promiseResolver = resolve;
	      });
	    }
	  }, {
	    key: "onPreviewClick",
	    value: function onPreviewClick(card) {
	      void this.hide();

	      if (main_core.Type.isFunction(this.promiseResolver)) {
	        this.promiseResolver({
	          type: card.code,
	          separatorTitle: card.title
	        });
	      }
	    }
	  }]);
	  return SeparatorPanel;
	}(landing_ui_panel_content.Content);

	exports.SeparatorPanel = SeparatorPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX,BX.Landing.UI.Panel,BX.Landing,BX.Landing));
//# sourceMappingURL=separatorpanel.bundle.js.map
