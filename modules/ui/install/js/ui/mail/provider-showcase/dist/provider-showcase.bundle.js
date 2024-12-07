/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_sidepanel_layout,ui_mail_senderEditor) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7;
	const SidePanel = BX.SidePanel;
	const showcaseSliderUrl = 'mailProviderShowcase';
	const successMessage = 'mail-mailbox-config-success';
	const imapServiceName = 'other';
	const mailboxType = 'mailbox';
	var _wasSenderUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wasSenderUpdated");
	var _createSmtpItemNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSmtpItemNode");
	var _createShowcase = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createShowcase");
	var _createProvidersList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createProvidersList");
	var _createPromotionShowcase = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createPromotionShowcase");
	var _getProviderKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getProviderKey");
	var _getProviderName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getProviderName");
	var _getProviderImgSrcClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getProviderImgSrcClass");
	class ProviderShowcase {
	  constructor(options = null) {
	    var _options$isSender, _options$setSenderCal, _options$addSenderCal, _options$updateSender;
	    Object.defineProperty(this, _getProviderImgSrcClass, {
	      value: _getProviderImgSrcClass2
	    });
	    Object.defineProperty(this, _getProviderName, {
	      value: _getProviderName2
	    });
	    Object.defineProperty(this, _getProviderKey, {
	      value: _getProviderKey2
	    });
	    Object.defineProperty(this, _createPromotionShowcase, {
	      value: _createPromotionShowcase2
	    });
	    Object.defineProperty(this, _createProvidersList, {
	      value: _createProvidersList2
	    });
	    Object.defineProperty(this, _createShowcase, {
	      value: _createShowcase2
	    });
	    Object.defineProperty(this, _createSmtpItemNode, {
	      value: _createSmtpItemNode2
	    });
	    Object.defineProperty(this, _wasSenderUpdated, {
	      writable: true,
	      value: false
	    });
	    this.isSender = (_options$isSender = options.isSender) != null ? _options$isSender : false;
	    this.setSenderCallback = (_options$setSenderCal = options.setSenderCallback) != null ? _options$setSenderCal : null;
	    this.addSenderCallback = (_options$addSenderCal = options.addSenderCallback) != null ? _options$addSenderCal : null;
	    this.updateSenderList = (_options$updateSender = options.updateSenderList) != null ? _options$updateSender : null;
	    this.container = main_core.Tag.render(_t || (_t = _`
			<div class="showcase-container"></div>
		`));
	  }
	  static openSlider(options) {
	    const instance = new ProviderShowcase(options);
	    const onSliderMessage = function (event) {
	      const [sliderEvent] = event.getData();
	      if (!sliderEvent) {
	        return;
	      }
	      const slider = SidePanel.Instance.getSlider(showcaseSliderUrl);
	      if (!slider || sliderEvent.getEventId() !== successMessage) {
	        return;
	      }
	      const mailboxId = sliderEvent.data.id;
	      if (!mailboxId) {
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(instance, _wasSenderUpdated)[_wasSenderUpdated] = true;
	      slider.close();
	      top.BX.SidePanel.Instance.postMessage(window, sliderEvent.getEventId(), sliderEvent.data);
	      main_core.ajax.runAction('main.api.mail.sender.getSenderByMailboxId', {
	        data: {
	          mailboxId,
	          getSenderWithoutSmtp: !instance.options.isCloud
	        }
	      }).then(response => {
	        const data = response.data;
	        if (!data) {
	          return;
	        }
	        instance.setSender(data.id, data.name, data.email);
	        if (instance.addSenderCallback || data.type === mailboxType) {
	          return;
	        }
	        ui_mail_senderEditor.AliasEditor.openSlider({
	          senderId: data.id,
	          email: data.email,
	          setSenderCallback: instance.setSenderCallback,
	          updateSenderList: () => {
	            void instance.updateSenderList();
	          }
	        });
	      }).catch(() => {});
	    };
	    SidePanel.Instance.open(showcaseSliderUrl, {
	      width: 790,
	      cacheable: false,
	      contentCallback: () => {
	        return ui_sidepanel_layout.Layout.createContent({
	          extensions: ['ui.mail.provider-showcase'],
	          title: main_core.Loc.getMessage('UI_MAIL_PROVIDER_SHOWCASE_TITLE'),
	          design: {
	            section: false
	          },
	          content() {
	            return instance.load();
	          },
	          buttons: () => {}
	        });
	      },
	      events: {
	        onClose: () => {
	          top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onMessage', onSliderMessage);
	          if (instance.updateSenderList && babelHelpers.classPrivateFieldLooseBase(instance, _wasSenderUpdated)[_wasSenderUpdated]) {
	            instance.updateSenderList();
	          }
	        }
	      }
	    });
	    top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onMessage', onSliderMessage);
	  }
	  static renderTo(target, options) {
	    const instance = new ProviderShowcase(options);
	    return new Promise((resolve, reject) => {
	      instance.load().then(container => {
	        main_core.Dom.append(container, target);
	        resolve(container);
	      }).catch(() => {
	        reject();
	      });
	    });
	  }
	  load() {
	    return main_core.ajax.runAction('main.api.mail.mailproviders.getShowcaseParams', {
	      data: {
	        isSender: this.isSender ? 1 : 0
	      }
	    }).then(response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _createShowcase)[_createShowcase](response.data);
	      return this.container;
	    }).catch(() => {});
	  }
	  setSender(id, senderName, senderEmail) {
	    const name = senderName;
	    const email = senderEmail;
	    if (this.setSenderCallback) {
	      this.setSenderCallback(id, name, email);
	    }
	    if (!this.addSenderCallback) {
	      return;
	    }
	    const mailbox = [];
	    mailbox.name = name;
	    mailbox.email = email;
	    this.addSenderCallback(mailbox);
	  }
	}
	function _createSmtpItemNode2() {
	  this.smtpNode = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="mail-provider-item mail-provider-item-available">
				<div class="mail-provider-img-container">
					<div class="mail-provider-img-smtp"></div>
				</div>
				<div class="mail-provider-item-title-container">
					<span class="mail-provider-item-title">${0}</span>
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_PROVIDER_SMTP_TITLE'));
	  main_core.Event.bind(this.smtpNode, 'click', () => {
	    const slider = BX.SidePanel.Instance.getTopSlider();
	    if (slider) {
	      ui_mail_senderEditor.SmtpEditor.openSlider({
	        setSenderCallback: (senderId, senderName, senderEmail) => {
	          if (this.setSenderCallback && senderId && senderName && senderEmail) {
	            this.setSenderCallback(senderId, senderName, senderEmail);
	          }
	          this.updateSenderList();
	          slider.close();
	        },
	        addSenderCallback: this.addSenderCallback
	      });
	    }
	  });
	  if (this.options.isMailToolAvailable && this.options.canConnectNewMailbox) {
	    return;
	  }
	  main_core.Dom.addClass(this.smtpNode, 'available-mail-provider-item');
	  main_core.Dom.attr(this.smtpNode, 'data-tag', main_core.Loc.getMessage('UI_MAIL_PROVIDER_AVAILABLE_TAG'));
	}
	function _createShowcase2(params) {
	  this.options = params.options;
	  this.providers = params.providers;
	  this.showcaseNode = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="mail-provider-list"></div>
		`));
	  main_core.Dom.append(this.showcaseNode, this.container);
	  babelHelpers.classPrivateFieldLooseBase(this, _createProvidersList)[_createProvidersList]();
	  if (!this.isSender || !this.options.isSmtpAvailable) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _createSmtpItemNode)[_createSmtpItemNode]();
	  const firstProvider = this.showcaseNode.firstChild;
	  if (!firstProvider || this.options.canConnectNewMailbox && this.options.isMailToolAvailable) {
	    main_core.Dom.append(this.smtpNode, this.showcaseNode);
	  } else {
	    main_core.Dom.insertBefore(this.smtpNode, firstProvider);
	  }
	  if (this.options.isModuleMailInstalled) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _createPromotionShowcase)[_createPromotionShowcase]();
	}
	function _createProvidersList2() {
	  if (!this.providers) {
	    return;
	  }
	  this.providers.forEach(provider => {
	    var _babelHelpers$classPr;
	    const key = babelHelpers.classPrivateFieldLooseBase(this, _getProviderKey)[_getProviderKey](provider.name);
	    const name = provider.name;
	    const {
	      root,
	      title
	    } = main_core.Tag.render(_t4 || (_t4 = _`
				<a class="mail-provider-item mail-provider-item-available">
					<div class="mail-provider-img-container">
						<div class="mail-provider-img ${0}"></div>
					</div>
					<div class="mail-provider-item-title-container" ref="title">
						<span class="mail-provider-item-title">${0}</span>
					</div>
				</a>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getProviderImgSrcClass)[_getProviderImgSrcClass](key), main_core.Text.encode((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getProviderName)[_getProviderName](key)) != null ? _babelHelpers$classPr : name[0].toUpperCase() + name.slice(1)));
	    if (provider.name === imapServiceName) {
	      const imapSubtitle = main_core.Tag.render(_t5 || (_t5 = _`
					<span class="mail-provider-item-subtitle">${0}</span>
				`), main_core.Loc.getMessage('UI_MAIL_PROVIDER_IMAP_SUBTITLE'));
	      main_core.Dom.append(imapSubtitle, title);
	    }
	    if (!this.options.isMailToolAvailable) {
	      main_core.Event.bind(root, 'click', () => {
	        BX.UI.InfoHelper.show(this.options.toolLimitSliderCode);
	      });
	    } else if (this.options.canConnectNewMailbox) {
	      main_core.Event.bind(root, 'click', () => {
	        SidePanel.Instance.open(provider.href, {
	          width: 760
	        });
	      });
	    } else {
	      main_core.Event.bind(root, 'click', () => {
	        if (this.activeFeaturePromoter) {
	          this.activeFeaturePromoter.close();
	          this.activeFeaturePromoter = null;
	        }
	        const featureRegistry = BX.Intranet ? BX.UI.FeaturePromotersRegistry : top.BX.UI.FeaturePromotersRegistry;
	        this.activeFeaturePromoter = featureRegistry.getPromoter({
	          code: this.options.mailboxLimitSliderCode,
	          bindElement: title
	        });
	        this.activeFeaturePromoter.show();
	      });
	    }
	    main_core.Dom.append(root, this.showcaseNode);
	    if (!this.isSender || !this.options.isMailToolAvailable || !this.options.canConnectNewMailbox) {
	      return;
	    }
	    main_core.Dom.insertBefore(root, this.smtpNode);
	  });
	}
	function _createPromotionShowcase2() {
	  if (!this.options.promotionProviders) {
	    return;
	  }
	  const promotionMessage = main_core.Loc.getMessage('UI_MAIL_PROMOTION_TEXT', {
	    '[strong]': '<strong>',
	    '[/strong]': '</strong>'
	  });
	  const {
	    root,
	    providerList
	  } = main_core.Tag.render(_t6 || (_t6 = _`
			<div class="promotion-showcase">
				<div class="ui-alert ui-alert-icon-info ui-alert-primary">
					<span class="ui-alert-message">${0}</span>
				</div>
				<div class="mail-provider-list" ref="providerList" style="margin-top: 10px"></div>
			</div>
		`), promotionMessage);
	  this.promotionShowcaseNode = root;
	  this.options.promotionProviders.forEach(providerName => {
	    var _babelHelpers$classPr2;
	    const name = main_core.Text.encode(providerName);
	    const item = main_core.Tag.render(_t7 || (_t7 = _`
				<a class="mail-provider-item mail-provider-item-unavailable">
					<div class="mail-provider-img-container">
						<div class="mail-provider-img ${0}"></div>
					</div>
					<div class="mail-provider-item-title-container">
						<span class="mail-provider-item-title">${0}</span>
					</div>
				</a>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getProviderImgSrcClass)[_getProviderImgSrcClass](name), main_core.Text.encode((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _getProviderName)[_getProviderName](name)) != null ? _babelHelpers$classPr2 : name[0].toUpperCase() + name.slice(1)));
	    main_core.Dom.append(item, providerList);
	  });
	  main_core.Dom.append(this.promotionShowcaseNode, this.container);
	}
	function _getProviderKey2(name) {
	  switch (name) {
	    case 'aol':
	      return 'aol';
	    case 'gmail':
	      return 'gmail';
	    case 'yahoo':
	      return 'yahoo';
	    case 'mail.ru':
	    case 'mailru':
	      return 'mailru';
	    case 'icloud':
	      return 'icloud';
	    case 'outlook.com':
	    case 'outlook':
	      return 'outlook';
	    case 'office365':
	      return 'office365';
	    case 'exchangeOnline':
	    case 'exchange':
	      return 'exchange';
	    case 'yandex':
	      return 'yandex';
	    case 'ukr.net':
	      return 'ukrnet';
	    case 'other':
	    case 'imap':
	      return 'other';
	    default:
	      return '';
	  }
	}
	function _getProviderName2(key) {
	  switch (key) {
	    case 'aol':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_AOL');
	    case 'gmail':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_GMAIL');
	    case 'yahoo':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_YAHOO');
	    case 'mailru':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_MAILRU');
	    case 'icloud':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_ICLOUD');
	    case 'outlook':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_OUTLOOK');
	    case 'office365':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_OFFICE365');
	    case 'exchange':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_EXCHANGE');
	    case 'yandex':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_YANDEX');
	    case 'other':
	      return main_core.Loc.getMessage('UI_MAIL_PROVIDER_SERVICE_NAME_IMAP');
	    default:
	      return null;
	  }
	}
	function _getProviderImgSrcClass2(name) {
	  return `mail-provider-${name}-img`;
	}

	exports.ProviderShowcase = ProviderShowcase;

}((this.BX.UI.Mail = this.BX.UI.Mail || {}),BX,BX.UI.SidePanel,BX.UI.Mail));
//# sourceMappingURL=provider-showcase.bundle.js.map
