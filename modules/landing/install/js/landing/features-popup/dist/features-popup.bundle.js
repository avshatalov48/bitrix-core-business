this.BX = this.BX || {};
(function (exports,main_core_events,main_core,main_popup,landing_pageobject) {
	'use strict';

	let _ = t => t,
	    _t,
	    _t2,
	    _t3,
	    _t4,
	    _t5,
	    _t6,
	    _t7,
	    _t8;

	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");

	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");

	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");

	class FeaturesPopup extends main_core_events.EventEmitter {
	  constructor(_options) {
	    super();
	    Object.defineProperty(this, _getContent, {
	      value: _getContent2
	    });
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    this.setEventNamespace('BX.Landing.FeaturesPopup');
	    this.subscribeFromOptions(_options.events);
	    this.setOptions(_options);
	    main_core.Event.bind(landing_pageobject.PageObject.getEditorWindow().document, 'click', () => {
	      this.hide();
	    });
	  }

	  setOptions(options) {
	    babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].set('options', { ...options
	    });
	  }

	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].get('options', {});
	  }

	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();

	    this.emit('onShow');
	  }

	  hide() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().close();

	    this.emit('onClose');
	  }

	  isShown() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().isShown();
	  }

	  static createContentBlock(options) {
	    if (main_core.Type.isArray(options)) {
	      return options.map(optionsItem => {
	        return FeaturesPopup.createContentBlock(optionsItem);
	      });
	    }

	    const getTitle = () => {
	      if (main_core.Type.isStringFilled(options.title)) {
	        return main_core.Tag.render(_t || (_t = _`
					<div class="landing-features-popup-content-block-text-title">
						${0}
					</div>
				`), main_core.Text.encode(options.title));
	      }

	      return '';
	    };

	    const getLink = () => {
	      if (main_core.Type.isPlainObject(options.link) && main_core.Type.isStringFilled(options.link.label) && main_core.Type.isFunction(options.link.onClick)) {
	        return main_core.Tag.render(_t2 || (_t2 = _`
					<div 
						class="landing-features-popup-content-block-text-link"
						onclick="${0}"
					>
						${0}
					</div>
				`), options.link.onClick, main_core.Text.encode(options.link.label));
	      }

	      return '';
	    };

	    const getActionButton = () => {
	      if (main_core.Type.isPlainObject(options.actionButton) && main_core.Type.isStringFilled(options.actionButton.label) && main_core.Type.isFunction(options.actionButton.onClick)) {
	        return main_core.Tag.render(_t3 || (_t3 = _`
					<div class="landing-features-popup-content-block-action">
						<span 
							class="ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-light-border"
							onclick="${0}"
						>${0}</span>
					</div>
				`), options.actionButton.onClick, main_core.Text.encode(options.actionButton.label));
	      }

	      return '';
	    };

	    const getTextBlock = () => {
	      const title = getTitle();
	      const link = getLink();

	      if (title || link) {
	        return main_core.Tag.render(_t4 || (_t4 = _`
					<div class="landing-features-popup-content-block-text">
						${0}
						${0}
					</div>
				`), getTitle(), getLink());
	      }

	      return '';
	    };

	    const getIcon = () => {
	      if (main_core.Type.isPlainObject(options.icon)) {
	        return main_core.Tag.render(_t5 || (_t5 = _`
					<div class="landing-features-popup-content-block-icon">
						<div class="ui-icon ui-icon-md ${0}">
							<i></i>
						</div>
					</div>
				`), options.icon.className);
	      }

	      return '';
	    };

	    const blockClass = (() => {
	      let result = '';

	      if (main_core.Type.isFunction(options.onClick)) {
	        result += ' landing-features-popup-content-block-clickable';
	      }

	      if (main_core.Type.isStringFilled(options.theme)) {
	        result += ` landing-features-popup-content-block-theme-${options.theme}`;
	      }

	      return result;
	    })();

	    const block = main_core.Tag.render(_t6 || (_t6 = _`
			<div 
				class="landing-features-popup-content-block${0}"
				data-id="${0}"
			>
				${0}
				${0}
				${0}
			</div>
		`), blockClass, main_core.Text.encode(options.id || main_core.Text.getRandom()), getIcon(), getTextBlock(), getActionButton());

	    if (main_core.Type.isFunction(options.onClick)) {
	      main_core.Event.bind(block, 'click', options.onClick);
	    }

	    if (main_core.Type.isStringFilled(options.backgroundColor)) {
	      main_core.Dom.style(block, 'background-color', options.backgroundColor);
	    }

	    return block;
	  }

	  static createRow(options) {
	    return main_core.Tag.render(_t7 || (_t7 = _`
			<div class="landing-features-popup-content-row">
				${0}
			</div>
		`), FeaturesPopup.createContentBlock(options));
	  }

	}

	function _getPopup2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('popup', () => {
	    return new main_popup.Popup({
	      id: `landing-features-popup-${main_core.Text.getRandom()}`,
	      bindElement: this.getOptions().bindElement,
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent](),
	      className: 'landing-features-popup',
	      width: 410,
	      autoHide: true,
	      closeByEsc: true,
	      noAllPaddings: true,
	      angle: {
	        position: 'top',
	        offset: 115
	      },
	      minWidth: 410,
	      contentBackground: 'transparent',
	      background: '#E9EAED'
	    });
	  });
	}

	function _getContent2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('content', () => {
	    return main_core.Tag.render(_t8 || (_t8 = _`
				<div class="landing-features-popup-content">
					${0}
				</div>
			`), this.getOptions().items.map(options => {
	      return FeaturesPopup.createRow(options);
	    }));
	  });
	}

	FeaturesPopup.Themes = {
	  Highlight: 'highlight'
	};

	exports.FeaturesPopup = FeaturesPopup;

}((this.BX.Landing = this.BX.Landing || {}),BX.Event,BX,BX.Main,BX.Landing));
//# sourceMappingURL=features-popup.bundle.js.map
