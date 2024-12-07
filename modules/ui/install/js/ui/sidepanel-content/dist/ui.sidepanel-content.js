/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Sidepanel = this.BX.UI.Sidepanel || {};
(function (exports,main_core,main_sidepanel,ui_sidepanel_layout) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	const StubType = Object.freeze({
	  notAvailable: 'notAvailable',
	  noAccess: 'noAccess',
	  noConnection: 'noConnection'
	});
	const StubLinkType = Object.freeze({
	  helpdesk: 'helpdesk',
	  href: 'href'
	});
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _renderNoAccess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNoAccess");
	var _renderNotAvailable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNotAvailable");
	var _renderNoConection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNoConection");
	var _renderLinkElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLinkElement");
	class StubNotAvailable {
	  constructor(options) {
	    Object.defineProperty(this, _renderLinkElement, {
	      value: _renderLinkElement2
	    });
	    Object.defineProperty(this, _renderNoConection, {
	      value: _renderNoConection2
	    });
	    Object.defineProperty(this, _renderNotAvailable, {
	      value: _renderNotAvailable2
	    });
	    Object.defineProperty(this, _renderNoAccess, {
	      value: _renderNoAccess2
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title = (options == null ? void 0 : options.title) || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].desc = (options == null ? void 0 : options.desc) || null;
	    if ((options == null ? void 0 : options.type) === StubType.noAccess) {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title) {
	        babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title = main_core.Loc.getMessage('UI_SIDEPANEL_CONTENT_NO_ACCESS_TITLE');
	      }
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].desc) {
	        babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].desc = main_core.Loc.getMessage('UI_SIDEPANEL_CONTENT_NO_ACCESS_DESC');
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = {
	      title: main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title || main_core.Loc.getMessage('UI_SIDEPANEL_CONTENT_TITLE')),
	      desc: main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].desc || main_core.Loc.getMessage('UI_SIDEPANEL_CONTENT_DESC')),
	      type: (options == null ? void 0 : options.type) || StubType.notAvailable,
	      link: (options == null ? void 0 : options.link) || null
	    };
	  }
	  openSlider() {
	    const noSectionTypes = new Set([StubType.noAccess]);
	    main_sidepanel.SidePanel.Instance.open('sign:stub-no-connection', {
	      width: 590,
	      cacheable: false,
	      contentCallback: () => {
	        return ui_sidepanel_layout.Layout.createContent({
	          design: {
	            section: !noSectionTypes.has(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].type)
	          },
	          content: () => this.render()
	        });
	      }
	    });
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].type === StubType.noAccess) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderNoAccess)[_renderNoAccess]();
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].type === StubType.notAvailable) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderNotAvailable)[_renderNotAvailable]();
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].type === StubType.noConnection) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderNoConection)[_renderNoConection]();
	    }
	    throw new Error('wrong stub type');
	  }
	  renderTo(container) {
	    main_core.Dom.append(this.render(), container);
	  }
	}
	function _renderNoAccess2() {
	  return main_core.Tag.render(_t || (_t = _`
			<div class="ui-slider-no-access">
				<div class="ui-slider-no-access-inner">
					<div class="ui-slider-no-access-title">${0}</div>
					<div class="ui-slider-no-access-subtitle">${0}</div>
					<div class="ui-slider-no-access-img">
						<div class="ui-slider-no-access-img-inner"></div>
					</div>
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title, babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].desc, babelHelpers.classPrivateFieldLooseBase(this, _renderLinkElement)[_renderLinkElement]());
	}
	function _renderNotAvailable2() {
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-sidepanel-content-404-container">
				<div class="ui-sidepanel-content-404-image">
					<img alt="" src="/bitrix/components/bitrix/ui.sidepanel.content/templates/.default/images/stub-not-available.svg">
				</div>
				<div class="ui-sidepanel-content-404-title">${0}</div>
				<div class="ui-sidepanel-content-404-description">
					<p>${0}</p>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title, babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].desc);
	}
	function _renderNoConection2() {
	  return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-slider-no-connection">
				<div class="ui-slider-no-connection-inner">
					<div class="ui-slider-no-connection-title">${0}</div>
					<div class="ui-slider-no-connection-subtitle">${0}</div>
					<div class="ui-sidepanel-content-no-connection-image"></div>
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title, babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].desc, babelHelpers.classPrivateFieldLooseBase(this, _renderLinkElement)[_renderLinkElement]());
	}
	function _renderLinkElement2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].link) {
	    return null;
	  }
	  const linkElement = main_core.Tag.render(_t4 || (_t4 = _`
			<a href="javascript:void(0);" class="ui-sidepanel-content-link-href">${0}</a>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].link.text));
	  if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].link.type === StubLinkType.helpdesk) {
	    main_core.Event.bind(linkElement, 'click', event => {
	      event.preventDefault();
	      top.BX.Helper.show(`redirect=detail&code=${main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].link.value)}`);
	    });
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].link.type === StubLinkType.href) {
	    main_core.Dom.attr(linkElement, 'href', main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].link.value));
	  }
	  return linkElement;
	}

	exports.StubType = StubType;
	exports.StubLinkType = StubLinkType;
	exports.StubNotAvailable = StubNotAvailable;

}((this.BX.UI.Sidepanel.Content = this.BX.UI.Sidepanel.Content || {}),BX,BX,BX.UI.SidePanel));
//# sourceMappingURL=ui.sidepanel-content.js.map
