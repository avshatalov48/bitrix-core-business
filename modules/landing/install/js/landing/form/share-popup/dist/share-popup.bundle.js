this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,main_core_events,main_popup,landing_env,crm_form_embed,landing_pageobject) {
	'use strict';

	let _ = t => t,
	    _t,
	    _t2;

	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");

	/**
	 * @memberOf BX.Landing.Form
	 */
	class SharePopup extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    this.setEventNamespace('BX.Landing.Form.SharePopup');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	    console.log(landing_pageobject.PageObject.getEditorWindow().document);
	    main_core.Event.bind(landing_pageobject.PageObject.getEditorWindow().document, 'click', () => {
	      console.log('click');
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

	  getPopup() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('popup', () => {
	      return new main_popup.Popup({
	        id: `form-share-popup-${main_core.Text.getRandom()}`,
	        bindElement: this.getOptions().bindElement,
	        content: this.getContent(),
	        className: 'landing-form-share-popup',
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

	  getContent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('content', () => {
	      return main_core.Tag.render(_t || (_t = _`
				<div class="landing-form-share-popup-content">
					${0}
					${0}
					${0}
				</div>
			`), this.getShareBlock(), this.getCommunicationBlock(), this.getHelpBlock());
	    });
	  }

	  getShareBlock() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('shareBlock', () => {
	      return this.createContentBlock({
	        type: 'share',
	        title: main_core.Loc.getMessage('LANDING_FORM_SHARE__SHARE_TITLE'),
	        link: {
	          label: main_core.Loc.getMessage('LANDING_FORM_SHARE__SHARE_LINK_LABEL'),
	          onClick: () => {}
	        },
	        action: {
	          label: main_core.Loc.getMessage('LANDING_FORM_SHARE__SHARE_ACTION_LABEL'),
	          onClick: () => {
	            this.showEmbedPanel();
	            this.hide();
	          }
	        }
	      });
	    });
	  }

	  showEmbedPanel() {
	    const {
	      formEditorData
	    } = landing_env.Env.getInstance().getOptions();

	    if (main_core.Type.isPlainObject(formEditorData) && main_core.Type.isPlainObject(formEditorData.formOptions)) {
	      const {
	        id
	      } = formEditorData.formOptions;
	      crm_form_embed.Embed.open(id);
	    }
	  }

	  showWidgetPanel() {
	    const SidePanelInstance = main_core.Reflection.getClass('BX.SidePanel.Instance');
	    SidePanelInstance.open(`/crm/button/`, {
	      allowChangeHistory: false,
	      cacheable: false
	    });
	  }

	  getCommunicationBlock() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('communicationBlock', () => {
	      return this.createContentBlock({
	        type: 'communication',
	        title: main_core.Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_TITLE'),
	        link: {
	          label: main_core.Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_LINK_LABEL'),
	          onClick: () => {}
	        },
	        action: {
	          label: main_core.Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_ACTION_LABEL'),
	          onClick: () => {
	            this.showWidgetPanel();
	          }
	        }
	      });
	    });
	  }

	  getHelpBlock() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('helpBlock', () => {
	      return this.createContentBlock({
	        type: 'help',
	        title: main_core.Loc.getMessage('LANDING_FORM_SHARE__HELP_TITLE'),
	        link: {
	          label: main_core.Loc.getMessage('LANDING_FORM_SHARE__HELP_LINK_LABEL'),
	          onClick: () => {}
	        },
	        action: {
	          label: main_core.Loc.getMessage('LANDING_FORM_SHARE__HELP_ACTION_LABEL'),
	          onClick: () => {}
	        }
	      });
	    });
	  }

	  createContentBlock(options) {
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="landing-form-share-popup-content-block" data-type="${0}">
					<div class="landing-form-share-popup-content-block-icon"></div>
					<div class="landing-form-share-popup-content-block-text">
						<div class="landing-form-share-popup-content-block-text-title">
							${0}
						</div>
						<div 
							class="landing-form-share-popup-content-block-text-link"
							onclick="${0}"
						>
							${0}
						</div>
					</div>
					<div class="landing-form-share-popup-content-block-action">
						<span 
							class="ui-btn ui-btn-xs ui-btn-round ui-btn-no-caps ui-btn-light-border"
							onclick="${0}"
						>${0}</span>
					</div>
				</div>
			`), main_core.Text.encode(options.type), main_core.Text.encode(options.title), options.link.onClick, main_core.Text.encode(options.link.label), options.action.onClick, main_core.Text.encode(options.action.label));
	  }

	  show(options) {
	    const popup = this.getPopup();

	    if (main_core.Type.isPlainObject(options)) {
	      if (main_core.Type.isDomNode(options.bindElement)) {
	        popup.setBindElement(options.bindElement);
	      }
	    }

	    popup.show();
	  }

	  hide() {
	    this.getPopup().close();
	  }

	}

	exports.SharePopup = SharePopup;

}((this.BX.Landing.Form = this.BX.Landing.Form || {}),BX,BX.Event,BX.Main,BX.Landing,BX.Crm.Form,BX.Landing));
//# sourceMappingURL=share-popup.bundle.js.map
