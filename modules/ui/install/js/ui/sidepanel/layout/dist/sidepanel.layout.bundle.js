/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_fonts_opensans,sidepanel,main_core,main_core_events,ui_buttons,ui_sidepanel_menu) {
	'use strict';

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
	  _t11;
	const UI = BX.UI;
	const SidePanel = BX.SidePanel;
	function prepareOptions(options = {}) {
	  options = Object.assign({}, options);
	  options.design = Object.assign({}, options.design || {});
	  options.design = {
	    margin: true,
	    section: true,
	    ...options.design
	  };
	  options.extensions = (options.extensions || []).concat(['ui.sidepanel.layout', 'ui.buttons']);
	  if (options.toolbar) {
	    options.extensions.push('ui.buttons.icons');
	  }
	  if (options.design.section) {
	    options.extensions.push('ui.sidepanel-content');
	  }
	  if (options.menu) {
	    options.extensions.push('ui.sidepanel.menu');
	  }
	  return options;
	}
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _containerFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("containerFooter");
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _menu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _getScrollWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getScrollWidth");
	var _adjustFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustFooter");
	var _onMenuItemClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMenuItemClick");
	class Layout {
	  static createContent(options = {}) {
	    options = prepareOptions(options);
	    return top.BX.Runtime.loadExtension(options.extensions).then(() => new Layout(options).render());
	  }
	  static createLayout(options = {}) {
	    options = prepareOptions(options);
	    return top.BX.Runtime.loadExtension(options.extensions).then(() => new Layout(options));
	  }
	  constructor(options = {}) {
	    Object.defineProperty(this, _onMenuItemClick, {
	      value: _onMenuItemClick2
	    });
	    Object.defineProperty(this, _adjustFooter, {
	      value: _adjustFooter2
	    });
	    Object.defineProperty(this, _getScrollWidth, {
	      value: _getScrollWidth2
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _containerFooter, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _menu, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = prepareOptions(options);
	    const menuOptions = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].menu;
	    if (menuOptions) {
	      babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu] = new ui_sidepanel_menu.Menu(Object.assign(menuOptions));
	      if (main_core.Type.isUndefined(menuOptions.contentAttribute)) {
	        menuOptions.contentAttribute = 'data-menu-item-id';
	      }
	      if (menuOptions.contentAttribute) {
	        babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].subscribe('click', event => {
	          babelHelpers.classPrivateFieldLooseBase(this, _onMenuItemClick)[_onMenuItemClick]((event.getData() || {}).item);
	        });
	      }
	    }
	  }
	  getContainer() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = main_core.Tag.render(_t || (_t = _`<div class="ui-sidepanel-layout"></div>`));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	  }
	  getMenu() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu];
	  }
	  getFooterContainer() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _containerFooter)[_containerFooter]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _containerFooter)[_containerFooter] = main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-sidepanel-layout-footer"></div>`));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _containerFooter)[_containerFooter];
	  }
	  render(content = '', promised = false) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].content && !promised) {
	      content = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].content();
	      if (Object.prototype.toString.call(content) === "[object Promise]" || content.toString && content.toString() === "[object BX.Promise]") {
	        return content.then(content => this.render(content, true));
	      }
	    }
	    const container = this.getContainer();
	    container.innerHTML = '';

	    // HEADER
	    if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title) {
	      const title = main_core.Tag.safe(_t3 || (_t3 = _`${0}`), babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].title);
	      const header = main_core.Tag.render(_t4 || (_t4 = _`
				<div class="ui-sidepanel-layout-header">
					<div class="ui-sidepanel-layout-title">${0}</div>
				</div>
			`), title);
	      if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].toolbar)) {
	        const toolbar = main_core.Tag.render(_t5 || (_t5 = _`<div class="ui-sidepanel-layout-toolbar"></div>`));
	        babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].toolbar({
	          ...UI
	        }).forEach(button => {
	          if (button instanceof ui_buttons.BaseButton) {
	            button.renderTo(toolbar);
	          } else if (main_core.Type.isDomNode(button)) {
	            toolbar.appendChild(button);
	          } else {
	            throw main_core.BaseError('Wrong button type ' + button);
	          }
	        });
	        header.appendChild(toolbar);
	      }
	      container.appendChild(header);
	    }

	    // CONTENT
	    {
	      const design = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].design;
	      const classes = ['ui-sidepanel-layout-content'];
	      const styles = [];
	      if (design.margin) {
	        if (design.margin === true) {
	          classes.push('ui-sidepanel-layout-content-margin');
	        } else {
	          styles.push('margin: ' + design.margin);
	        }
	      }
	      let contentElement = main_core.Tag.render(_t6 || (_t6 = _`<div class="${0}" style="${0}"></div>`), classes.join(' '), styles.join('; '));
	      container.appendChild(contentElement);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].renderTo(contentElement);
	      }
	      contentElement.appendChild(main_core.Tag.render(_t7 || (_t7 = _`<div class="ui-sidepanel-layout-content-inner"></div>`)));
	      contentElement = contentElement.lastElementChild;
	      if (design.section) {
	        contentElement.appendChild(main_core.Tag.render(_t8 || (_t8 = _`<div class="ui-slider-section ui-sidepanel-layout-content-fill-height"></div>`)));
	        contentElement = contentElement.firstElementChild;
	      }
	      if (typeof content === 'string') {
	        contentElement.innerHTML = content;
	      } else if (content instanceof Element) {
	        contentElement.appendChild(content);
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _onMenuItemClick)[_onMenuItemClick](babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].getActiveItem(), contentElement);
	      }
	    }

	    // FOOTER
	    const isButtonsUndefined = typeof babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].buttons === 'undefined';
	    if (typeof babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].buttons === 'function' || isButtonsUndefined) {
	      const cancelButton = new ui_buttons.CancelButton({
	        onclick: () => SidePanel.Instance.close()
	      });
	      const closeButton = new ui_buttons.CloseButton({
	        onclick: () => SidePanel.Instance.close()
	      });
	      const defaults = {
	        ...UI,
	        cancelButton,
	        closeButton
	      };
	      if (isButtonsUndefined) {
	        babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].buttons = () => [closeButton];
	      }
	      const buttonList = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].buttons(defaults);
	      if (buttonList && buttonList.length > 0) {
	        container.appendChild(main_core.Tag.render(_t9 || (_t9 = _`<div class="ui-sidepanel-layout-footer-anchor"></div>`)));
	        const classes = ['ui-sidepanel-layout-buttons'];
	        if (babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].design.alignButtonsLeft) {
	          classes.push('ui-sidepanel-layout-buttons-align-left');
	        }
	        const buttons = main_core.Tag.render(_t10 || (_t10 = _`<div class="${0}"></div>`), classes.join(' '));
	        this.getFooterContainer().appendChild(buttons);
	        buttonList.forEach(button => {
	          if (button instanceof ui_buttons.BaseButton) {
	            button.renderTo(buttons);
	          } else if (main_core.Type.isDomNode(button)) {
	            buttons.appendChild(button);
	          } else {
	            throw main_core.BaseError('Wrong button type ' + button);
	          }
	        });
	        container.appendChild(this.getFooterContainer());
	      }
	    }
	    setTimeout(() => {
	      this.afterRender();
	    });
	    return container;
	  }
	  afterRender() {
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustFooter)[_adjustFooter]();
	    const resizeHandler = main_core.Runtime.throttle(babelHelpers.classPrivateFieldLooseBase(this, _adjustFooter)[_adjustFooter], 300, this);
	    main_core.Event.bind(window, "resize", resizeHandler);
	    const topSlider = SidePanel.Instance.getTopSlider();
	    if (topSlider) {
	      main_core_events.EventEmitter.subscribeOnce(topSlider, 'SidePanel.Slider:onDestroy', () => {
	        main_core.Event.unbind(window, "resize", resizeHandler);
	      });
	    }
	  }
	}
	function _getScrollWidth2() {
	  const div = main_core.Tag.render(_t11 || (_t11 = _`<div style="overflow-y: scroll; width: 50px; height: 50px; opacity: 0; pointer-events: none; position: absolute;"></div>`));
	  document.body.appendChild(div);
	  const scrollWidth = div.offsetWidth - div.clientWidth;
	  main_core.Dom.remove(div);
	  return scrollWidth;
	}
	function _adjustFooter2() {
	  const parentSet = this.getContainer().parentNode;
	  if (parentSet.scrollWidth > parentSet.offsetWidth) {
	    main_core.Dom.style(this.getFooterContainer(), 'bottom', babelHelpers.classPrivateFieldLooseBase(this, _getScrollWidth)[_getScrollWidth]() + 'px');
	  } else {
	    main_core.Dom.style(this.getFooterContainer(), 'bottom', 0);
	  }
	}
	function _onMenuItemClick2(item, container = null) {
	  if (!item) {
	    return;
	  }
	  const id = item.getId();
	  let attr = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].menu.contentAttribute;
	  if (!attr) {
	    return;
	  }
	  container = container || babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	  let nodes = container.querySelectorAll(`[${attr}]`);
	  nodes = Array.prototype.slice.call(nodes);
	  nodes.forEach(node => {
	    node.hidden = node.getAttribute(attr) !== id;
	  });
	}

	exports.Layout = Layout;

}((this.BX.UI.SidePanel = this.BX.UI.SidePanel || {}),BX,BX,BX,BX.Event,BX.UI,BX.UI.SidePanel));
//# sourceMappingURL=sidepanel.layout.bundle.js.map
