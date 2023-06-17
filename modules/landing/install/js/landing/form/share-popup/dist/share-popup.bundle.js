this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,main_core_events,landing_featuresPopup,landing_loc,landing_pageobject,landing_env,crm_form_embed,ui_feedback_form,bitrix24_phoneverify) {
	'use strict';

	const PHONE_VERIFY_FORM_ENTITY = 'crm_webform';

	/**
	 * @memberOf BX.Landing.Form
	 */
	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _getFeaturesPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFeaturesPopup");
	var _showPhoneVerifySlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showPhoneVerifySlider");
	class SharePopup extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    Object.defineProperty(this, _showPhoneVerifySlider, {
	      value: _showPhoneVerifySlider2
	    });
	    Object.defineProperty(this, _getFeaturesPopup, {
	      value: _getFeaturesPopup2
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    this.setEventNamespace('BX.Landing.Form.SharePopup');
	    this.subscribeFromOptions(options.events);
	    this.setOptions(options);
	  }
	  setOptions(options) {
	    babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].set('options', {
	      ...options
	    });
	  }
	  getOptions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].get('options', {});
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getFeaturesPopup)[_getFeaturesPopup]().show();
	  }
	  hide() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getFeaturesPopup)[_getFeaturesPopup]().hide();
	  }
	}
	function _getFeaturesPopup2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('featuresPopup', () => {
	    return new landing_featuresPopup.FeaturesPopup({
	      bindElement: this.getOptions().bindElement,
	      items: [{
	        id: 'share',
	        title: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__SHARE_TITLE'),
	        theme: landing_featuresPopup.FeaturesPopup.Themes.Highlight,
	        icon: {
	          className: 'landing-form-features-share-icon'
	        },
	        link: {
	          label: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__SHARE_LINK_LABEL'),
	          onClick: () => {
	            if (!main_core.Type.isNil(BX.Helper)) {
	              BX.Helper.show('redirect=detail&code=13003062');
	            }
	          }
	        },
	        actionButton: {
	          label: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__SHARE_ACTION_LABEL'),
	          onClick: () => {
	            const editorWindow = landing_pageobject.PageObject.getEditorWindow();
	            const {
	              formEditorData
	            } = editorWindow.BX.Landing.Env.getInstance().getOptions();
	            if (main_core.Type.isPlainObject(formEditorData) && main_core.Type.isPlainObject(formEditorData.formOptions)) {
	              var _this$getOptions;
	              if ((_this$getOptions = this.getOptions()) != null && _this$getOptions.phoneVerified) {
	                crm_form_embed.Embed.openSlider(formEditorData.formOptions.id);
	              } else {
	                babelHelpers.classPrivateFieldLooseBase(this, _showPhoneVerifySlider)[_showPhoneVerifySlider](formEditorData.formOptions.id).then(verified => {
	                  if (verified) {
	                    crm_form_embed.Embed.openSlider(formEditorData.formOptions.id);
	                  }
	                });
	              }
	            }
	          }
	        }
	      }, {
	        id: 'communication',
	        title: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_TITLE'),
	        icon: {
	          className: 'landing-form-features-communication-icon'
	        },
	        link: {
	          label: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_LINK_LABEL'),
	          onClick: () => {
	            if (!main_core.Type.isNil(BX.Helper)) {
	              BX.Helper.show('redirect=detail&code=6986667');
	            }
	          }
	        },
	        actionButton: {
	          label: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__COMMUNICATION_ACTION_LABEL'),
	          onClick: () => {
	            const {
	              landingParams
	            } = landing_pageobject.PageObject.getRootWindow();
	            if (!main_core.Type.isNil(landingParams) && main_core.Type.isStringFilled(landingParams.PAGE_URL_LANDING_SETTINGS)) {
	              const SidePanel = main_core.Reflection.getClass('BX.SidePanel');
	              if (!main_core.Type.isNil(SidePanel)) {
	                SidePanel.Instance.open(`${landingParams['PAGE_URL_LANDING_SETTINGS']}#b24widget`);
	              }
	            }
	          }
	        }
	      }, [{
	        id: 'help',
	        title: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__HELP_TITLE'),
	        icon: {
	          className: 'landing-form-features-help-icon'
	        },
	        link: {
	          label: landing_loc.Loc.getMessage('LANDING_FORM_SHARE__HELP_LINK_LABEL'),
	          onClick: () => {
	            const Feedback = main_core.Reflection.getClass('BX.UI.Feedback');
	            if (!main_core.Type.isNil(Feedback)) {
	              Feedback.Form.open({
	                id: 'form-editor-feedback-form',
	                portalUri: 'https://bitrix24.team',
	                forms: [{
	                  id: 1847,
	                  lang: 'ru',
	                  sec: 'bbih83',
	                  zones: ['ru']
	                }, {
	                  id: 1852,
	                  lang: 'kz',
	                  sec: 'dtw568',
	                  zones: ['kz']
	                }, {
	                  id: 1851,
	                  lang: 'by',
	                  sec: 'nnz05i',
	                  zones: ['by']
	                }, {
	                  id: 1855,
	                  lang: 'en',
	                  sec: '6lxt2y',
	                  zones: ['en', 'eu', 'in', 'uk']
	                }, {
	                  id: 1856,
	                  lang: 'de',
	                  sec: '574psk',
	                  zones: ['de']
	                }, {
	                  id: 1857,
	                  lang: 'la',
	                  sec: '9tlqqk',
	                  zones: ['es', 'mx', 'co']
	                }, {
	                  id: 1858,
	                  lang: 'br',
	                  sec: '9ptdnu',
	                  zones: ['com.br']
	                }, {
	                  id: 1859,
	                  lang: 'pl',
	                  sec: 'aynrqw',
	                  zones: ['pl']
	                }, {
	                  id: 1860,
	                  lang: 'fr',
	                  sec: 'ld3bh8',
	                  zones: ['fr']
	                }, {
	                  id: 1861,
	                  lang: 'it',
	                  sec: '1rlv2j',
	                  zones: ['it']
	                }, {
	                  id: 1862,
	                  lang: 'vn',
	                  sec: '5m169k',
	                  zones: ['vn']
	                }, {
	                  id: 1863,
	                  lang: 'tr',
	                  sec: '2mc2tg',
	                  zones: ['com.tr']
	                }],
	                defaultForm: {
	                  id: 1855,
	                  lang: 'en',
	                  sec: '6lxt2y'
	                }
	              });
	            }
	          }
	        }
	      }, {
	        id: 'settings',
	        icon: {
	          className: 'landing-form-features-settings-icon'
	        },
	        onClick: () => {
	          const {
	            landingParams
	          } = landing_pageobject.PageObject.getRootWindow();
	          if (!main_core.Type.isNil(landingParams) && main_core.Type.isStringFilled(landingParams.PAGE_URL_LANDING_SETTINGS)) {
	            const SidePanel = main_core.Reflection.getClass('BX.SidePanel');
	            if (!main_core.Type.isNil(SidePanel)) {
	              SidePanel.Instance.open(landingParams['PAGE_URL_LANDING_SETTINGS']);
	            }
	          }
	        }
	      }]]
	    });
	  });
	}
	function _showPhoneVerifySlider2(formId) {
	  if (typeof bitrix24_phoneverify.PhoneVerify !== 'undefined') {
	    return bitrix24_phoneverify.PhoneVerify.getInstance().setEntityType(PHONE_VERIFY_FORM_ENTITY).setEntityId(formId).startVerify({
	      sliderTitle: landing_loc.Loc.getMessage('LANDING_FORM_PHONE_VERIFY_CUSTOM_SLIDER_TITLE'),
	      title: landing_loc.Loc.getMessage('LANDING_FORM_PHONE_VERIFY_CUSTOM_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_PHONE_VERIFY_CUSTOM_DESCRIPTION')
	    });
	  }
	  return Promise.resolve(true);
	}

	exports.SharePopup = SharePopup;

}((this.BX.Landing.Form = this.BX.Landing.Form || {}),BX,BX.Event,BX.Landing,BX.Landing,BX.Landing,BX.Landing,BX.Crm.Form,BX,BX.Bitrix24));
//# sourceMappingURL=share-popup.bundle.js.map
