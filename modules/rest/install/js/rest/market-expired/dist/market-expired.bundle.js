/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup,main_polyfill_intersectionobserver,main_core_events,ui_notification,ui_infoHelper,ui_bannerDispatcher,ui_notificationPanel,ui_iconSet_api_core,ui_analytics,ui_iconSet_main,ui_buttons,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _icon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	class MarketItem {
	  constructor(options) {
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = options.name;
	    babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = options.icon;
	  }
	  getContainer() {
	    return main_core.Tag.render(_t || (_t = _`
			<span class="rest-market-item">
				${0}
				<span class="rest-market-item__name" title="${0}">${0}</span>
			</span>
		`), this.renderIcon(), this.getName(), this.getName());
	  }
	  renderTo(node) {
	    main_core.Dom.append(this.getContainer(), node);
	  }
	  getName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name)[_name];
	  }
	  renderIcon() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]) {
	      return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="rest-market-item__icon-container">
					<div class="ui-icon-set --cube-plus rest-market-item__icon"></div>
				</div>
			`));
	    }
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="rest-market-item__icon-container" 
				style="
					background-image: url(${0});
					background-size: cover;
				">
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]);
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1;
	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _link = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("link");
	var _count = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("count");
	var _onClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClick");
	var _renderList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderList");
	class MarketList {
	  constructor(options) {
	    Object.defineProperty(this, _renderList, {
	      value: _renderList2
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _link, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _count, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onClick, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items] = main_core.Type.isArray(options.items) ? options.items : [];
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = options.title;
	    babelHelpers.classPrivateFieldLooseBase(this, _link)[_link] = options.link;
	    babelHelpers.classPrivateFieldLooseBase(this, _count)[_count] = options.count;
	    babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick] = options.onClick;
	  }
	  render() {
	    return main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="rest-market-list">
				<div class="rest-market-list__header">
					<span class="rest-market-list__title">${0}</span>
					<a class="rest-market-list__link" href="${0}" onclick="${0}">
						${0}
					</a>
				</div>
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _title)[_title], babelHelpers.classPrivateFieldLooseBase(this, _link)[_link], babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick], main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_LINK', {
	      '#COUNT#': babelHelpers.classPrivateFieldLooseBase(this, _count)[_count]
	    }), babelHelpers.classPrivateFieldLooseBase(this, _renderList)[_renderList]());
	  }
	}
	function _renderList2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].length === 0) {
	    return '';
	  }
	  const listContainer = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="rest-market-list__container"></div>
		`));
	  babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].forEach(item => {
	    item.renderTo(listContainer);
	  });
	  return listContainer;
	}

	class DiscountEar {
	  getContainer() {
	    throw new Error('Not implemented');
	  }
	}

	class PopupType {}
	PopupType.FINAL = 'FINAL';
	PopupType.WARNING = 'WARNING';

	class PopupCategory {}
	PopupCategory.TRANSITION = 'transition';
	PopupCategory.SUBSCRIPTION = 'subscription';
	PopupCategory.TRIAL = 'trial';

	var _send = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("send");
	var _getType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getType");
	var _getP = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getP1");
	class Analytic {
	  constructor(context) {
	    Object.defineProperty(this, _getP, {
	      value: _getP2
	    });
	    Object.defineProperty(this, _getType, {
	      value: _getType2
	    });
	    Object.defineProperty(this, _send, {
	      value: _send2
	    });
	    this.context = context;
	  }
	  sendShow() {
	    babelHelpers.classPrivateFieldLooseBase(this, _send)[_send]({
	      tool: 'infohelper',
	      category: 'market',
	      event: 'show_popup'
	    });
	  }
	  sendClickButton(button) {
	    babelHelpers.classPrivateFieldLooseBase(this, _send)[_send]({
	      tool: 'infohelper',
	      category: 'market',
	      event: 'click_button',
	      c_element: button
	    });
	  }
	  sendDemoActivated() {
	    babelHelpers.classPrivateFieldLooseBase(this, _send)[_send]({
	      tool: 'intranet',
	      category: 'demo',
	      event: 'market_demo_activated'
	    });
	  }
	}
	function _send2(options) {
	  ui_analytics.sendData({
	    ...options,
	    type: babelHelpers.classPrivateFieldLooseBase(this, _getType)[_getType](),
	    p1: babelHelpers.classPrivateFieldLooseBase(this, _getP)[_getP]()
	  });
	}
	function _getType2() {
	  let type = this.context.popupType === PopupType.WARNING ? 'pre_disconnection_alert' : 'post_disconnection_notice';
	  if (this.context.popupCategory === PopupCategory.TRIAL) {
	    type = `${type}_demo`;
	  }
	  return type;
	}
	function _getP2() {
	  return `discount_${this.context.withDiscount ? 'Y' : 'N'}`;
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$1,
	  _t4;
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _appList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appList");
	var _integrationList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("integrationList");
	var _analytic = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("analytic");
	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");
	var _getContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainer");
	var _renderCloseIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCloseIcon");
	var _renderMarketList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMarketList");
	class MarketExpiredPopup extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _renderMarketList, {
	      value: _renderMarketList2
	    });
	    Object.defineProperty(this, _renderCloseIcon, {
	      value: _renderCloseIcon2
	    });
	    Object.defineProperty(this, _getContainer, {
	      value: _getContainer2
	    });
	    Object.defineProperty(this, _getContent, {
	      value: _getContent2
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _appList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _integrationList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _analytic, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Rest.MarketExpired:Popup');
	    this.expireDate = options.expireDate;
	    babelHelpers.classPrivateFieldLooseBase(this, _appList)[_appList] = options.appList;
	    babelHelpers.classPrivateFieldLooseBase(this, _integrationList)[_integrationList] = options.integrationList;
	    this.withDemo = options.withDemo;
	    this.olWidgetCode = options.olWidgetCode;
	    babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic] = options.analytic;
	    this.type = options.type;
	    this.expireDays = options.expireDays;
	    this.discountEar = options.discountEar;
	    this.marketLabel = options.isRenamedMarket ? '' : '_MARKET_PLUS';
	  }
	  getTitle() {
	    throw new Error('Not implemented');
	  }
	  getCategory() {
	    throw new Error('Not implemented');
	  }
	  show() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3, _babelHelpers$classPr4;
	    (_babelHelpers$classPr2 = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup))[_popup]) != null ? _babelHelpers$classPr2 : _babelHelpers$classPr[_popup] = main_popup.PopupWindowManager.create(`marketExpiredPopup_${this.getCategory()}_${this.type}`, null, {
	      animation: {
	        showClassName: 'rest-market-expired-popup__show',
	        closeAnimationType: 'animation'
	      },
	      overlay: true,
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent](),
	      disableScroll: true,
	      padding: 0,
	      className: 'rest-market-expired-popup-wrapper',
	      closeByEsc: true,
	      events: {
	        onClose: this.onClose.bind(this),
	        onShow: this.onOpen.bind(this)
	      }
	    });
	    const observerCallback = (entries, observer) => {
	      entries.forEach(entry => {
	        if (!entry.isIntersecting) {
	          babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].setDisableScroll(false);
	          observer.unobserve(entry.target);
	        }
	      });
	    };
	    const observer = new IntersectionObserver(observerCallback, {
	      root: null,
	      rootMargin: '0px',
	      threshold: [0, 1]
	    });
	    observer.observe(babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].getContentContainer().querySelector('.rest-market-expired-popup__close-icon'));
	    observer.observe(babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].getContentContainer().querySelector('.rest-market-expired-popup__button-container'));
	    (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr3.show();
	    (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr4.sendShow();

	    // hack for blur
	    if (this.discountEar) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].getContentContainer().querySelector('.rest-market-expired-popup__content-wrapper').offsetHeight < window.innerHeight) {
	        main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer](), {
	          maxHeight: `${this.discountEar.getContainer().offsetHeight}px`
	        });
	      } else {
	        main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].getContentContainer().parentNode, {
	          'backdrop-filter': 'none',
	          '-webkit-backdrop-filter': 'none'
	        });
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].adjustPosition();
	    }
	  }
	  close() {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	  }
	  onClose() {
	    this.emit('onClose');
	  }
	  onOpen() {
	    this.emit('onOpen');
	  }
	  renderDescription() {
	    return null;
	  }
	  renderButtons() {
	    return null;
	  }
	  renderAboutLink() {
	    return '';
	  }
	  getAnalytic() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic];
	  }
	}
	function _getContent2() {
	  return main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="rest-market-expired-popup">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer]());
	}
	function _getContainer2() {
	  var _babelHelpers$classPr5, _babelHelpers$classPr6, _this$discountEar;
	  (_babelHelpers$classPr6 = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _container))[_container]) != null ? _babelHelpers$classPr6 : _babelHelpers$classPr5[_container] = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="rest-market-expired-popup__container">
				${0}
				<div class="rest-market-expired-popup__content-wrapper">
					<div class="rest-market-expired-popup__content">
						<span class="rest-market-expired-popup__title">${0}</span>
						${0}
						${0}
						${0}
					</div>
					${0}
					${0}
				</div>
			</div>
		`), (_this$discountEar = this.discountEar) == null ? void 0 : _this$discountEar.getContainer(), this.getTitle(), this.renderDescription(), this.renderAboutLink(), this.renderButtons(), babelHelpers.classPrivateFieldLooseBase(this, _renderMarketList)[_renderMarketList](), babelHelpers.classPrivateFieldLooseBase(this, _renderCloseIcon)[_renderCloseIcon]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	}
	function _renderCloseIcon2() {
	  const onClick = () => {
	    var _babelHelpers$classPr7;
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	    (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr7.sendClickButton('cancel');
	  };
	  return main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<div class="rest-market-expired-popup__close-icon ui-icon-set --cross-30" onclick="${0}"></div>
		`), onClick);
	}
	function _renderMarketList2() {
	  var _babelHelpers$classPr8, _babelHelpers$classPr9;
	  return main_core.Tag.render(_t4 || (_t4 = _$2`
			<aside class="rest-market-expired-popup__aside">
				${0}
				${0}
			</aside>
		`), (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _appList)[_appList]) == null ? void 0 : _babelHelpers$classPr8.render(), (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _integrationList)[_integrationList]) == null ? void 0 : _babelHelpers$classPr9.render());
	}

	class MarketPopupButton extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.setEventNamespace('BX.Rest.MarketExpired.Button');
	    this.text = options.text;
	    this.onSuccess = options.onSuccess;
	  }
	  render() {
	    return this.getButton().render();
	  }
	  getButtonConfig() {
	    return {};
	  }
	  onClick() {
	    var _this$onSuccess;
	    (_this$onSuccess = this.onSuccess) == null ? void 0 : _this$onSuccess.call(this);
	  }
	  getButton() {
	    var _this$button;
	    (_this$button = this.button) != null ? _this$button : this.button = new ui_buttons.Button({
	      ...this.getButtonConfig(),
	      className: 'rest-market-expired-popup__button',
	      text: this.text,
	      onclick: this.onClick.bind(this)
	    });
	    return this.button;
	  }
	}

	var _getSubscribeLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSubscribeLink");
	class SubscribeButton extends MarketPopupButton {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getSubscribeLink, {
	      value: _getSubscribeLink2
	    });
	  }
	  getButtonConfig() {
	    return {
	      id: 'marketExpiredPopup_button_subscribe',
	      size: ui_buttons.Button.Size.MEDIUM,
	      color: ui_buttons.Button.Color.SUCCESS,
	      noCaps: true,
	      round: true,
	      tag: ui_buttons.Button.Tag.LINK,
	      link: babelHelpers.classPrivateFieldLooseBase(this, _getSubscribeLink)[_getSubscribeLink]()
	    };
	  }
	  onClick() {
	    var _this$analytic;
	    (_this$analytic = this.analytic) == null ? void 0 : _this$analytic.sendClickButton('buy');
	    super.onClick();
	  }
	}
	function _getSubscribeLink2() {
	  var _Extension$getSetting, _Extension$getSetting2;
	  return (_Extension$getSetting = (_Extension$getSetting2 = main_core.Extension.getSettings('rest.market-expired')) == null ? void 0 : _Extension$getSetting2.marketSubscriptionUrl) != null ? _Extension$getSetting : '';
	}

	class HideButton extends MarketPopupButton {
	  getButtonConfig() {
	    return {
	      id: 'marketExpiredPopup_button_hide',
	      size: ui_buttons.Button.Size.EXTRA_SMALL,
	      color: ui_buttons.Button.Color.LINK,
	      noCaps: true
	    };
	  }
	  onClick() {
	    var _this$analytic;
	    (_this$analytic = this.analytic) == null ? void 0 : _this$analytic.sendClickButton('ok');
	    super.onClick();
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$2;
	var _getSubscribeButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSubscribeButton");
	var _getHideButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHideButton");
	class MarketTrialPopup extends MarketExpiredPopup {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getHideButton, {
	      value: _getHideButton2
	    });
	    Object.defineProperty(this, _getSubscribeButton, {
	      value: _getSubscribeButton2
	    });
	  }
	  getCategory() {
	    return PopupCategory.TRIAL;
	  }
	  renderDescription() {
	    return main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div class="rest-market-expired-popup__description">
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
			</div>
		`), main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_DESCRIPTION_TRIAL${this.marketLabel}`));
	  }
	  getTitle() {
	    return main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_TITLE_TRIAL_${this.type}${this.marketLabel}`, {
	      '#DAYS#': this.expireDays
	    });
	  }
	  renderButtons() {
	    return main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
			<div class="rest-market-expired-popup__buttons-wrapper">
				<div class="rest-market-expired-popup__button-container">
					${0}
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getSubscribeButton)[_getSubscribeButton]().render(), babelHelpers.classPrivateFieldLooseBase(this, _getHideButton)[_getHideButton]().render());
	  }
	  onOpen() {
	    if (this.type === PopupType.FINAL) {
	      BX.userOptions.save('rest', 'marketSubscriptionPopupDismiss', null, 'Y');
	    } else {
	      BX.userOptions.save('rest', 'marketSubscriptionPopupTs', null, Math.floor(Date.now() / 1000));
	    }
	    super.onOpen();
	  }
	  renderAboutLink() {
	    const onclick = () => {
	      var _this$getAnalytic;
	      BX.Helper.show('redirect=detail&code=17451118');
	      (_this$getAnalytic = this.getAnalytic()) == null ? void 0 : _this$getAnalytic.sendClickButton('details');
	    };
	    return main_core.Tag.render(_t3$2 || (_t3$2 = _$3`
			<span class="rest-market-expired-popup__details">
				<a
					class="ui-link rest-market-expired-popup__link"
					href="#"
					onclick="${0}"
				>
					${0}
				</a>
			</span>
		`), onclick, main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DETAILS'));
	  }
	}
	function _getSubscribeButton2() {
	  return new SubscribeButton({
	    text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_SUBSCRIBE'),
	    analytic: this.getAnalytic()
	  });
	}
	function _getHideButton2() {
	  return new HideButton({
	    text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_NEVER_SHOW_AGAIN'),
	    onSuccess: () => {
	      BX.userOptions.save('rest', 'marketSubscriptionPopupDismiss', null, 'Y');
	      this.close();
	    },
	    analytic: this.getAnalytic()
	  });
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$4,
	  _t3$3;
	var _getSubscribeButton$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSubscribeButton");
	var _getHideButton$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHideButton");
	class MarketSubscriptionPopup extends MarketExpiredPopup {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getHideButton$1, {
	      value: _getHideButton2$1
	    });
	    Object.defineProperty(this, _getSubscribeButton$1, {
	      value: _getSubscribeButton2$1
	    });
	  }
	  getCategory() {
	    return PopupCategory.SUBSCRIPTION;
	  }
	  getTitle() {
	    const replacements = {
	      '#DAYS#': this.expireDays
	    };
	    const marketLabel = this.type === PopupType.WARNING ? this.marketLabel : '';
	    return main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_TITLE_SUBSCRIPTION_${this.type}${marketLabel}`, replacements);
	  }
	  renderDescription() {
	    const replacements = {
	      '#DATE#': this.expireDate
	    };
	    const marketLabel = this.type === PopupType.FINAL ? this.marketLabel : '';
	    return main_core.Tag.render(_t$4 || (_t$4 = _$4`
			<div class="rest-market-expired-popup__description">
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
			</div>
		`), main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_DESCRIPTION_SUBSCRIPTION_${this.type}${marketLabel}`, replacements));
	  }
	  renderButtons() {
	    return main_core.Tag.render(_t2$4 || (_t2$4 = _$4`
			<div class="rest-market-expired-popup__buttons-wrapper">
				<div class="rest-market-expired-popup__button-container">
					${0}
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getSubscribeButton$1)[_getSubscribeButton$1]().render(), babelHelpers.classPrivateFieldLooseBase(this, _getHideButton$1)[_getHideButton$1]().render());
	  }
	  onOpen() {
	    if (this.type === PopupType.FINAL) {
	      BX.userOptions.save('rest', 'marketSubscriptionPopupDismiss', null, 'Y');
	    } else {
	      BX.userOptions.save('rest', 'marketSubscriptionPopupTs', null, Math.floor(Date.now() / 1000));
	    }
	    super.onOpen();
	  }
	  renderAboutLink() {
	    const onclick = () => {
	      var _this$getAnalytic;
	      BX.Helper.show('redirect=detail&code=17451118');
	      (_this$getAnalytic = this.getAnalytic()) == null ? void 0 : _this$getAnalytic.sendClickButton('details');
	    };
	    return main_core.Tag.render(_t3$3 || (_t3$3 = _$4`
			<span class="rest-market-expired-popup__details">
				<a
					class="ui-link rest-market-expired-popup__link"
					href="#"
					onclick="${0}"
				>
					${0}
				</a>
			</span>
		`), onclick, main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DETAILS'));
	  }
	}
	function _getSubscribeButton2$1() {
	  return new SubscribeButton({
	    text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_RENEW_SUBSCRIPTION'),
	    analytic: this.getAnalytic()
	  });
	}
	function _getHideButton2$1() {
	  return new HideButton({
	    text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_NEVER_SHOW_AGAIN'),
	    onSuccess: () => {
	      BX.userOptions.save('rest', 'marketSubscriptionPopupDismiss', null, 'Y');
	      this.close();
	    },
	    analytic: this.getAnalytic()
	  });
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$5,
	  _t3$4;
	var _renderDiscountPercent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDiscountPercent");
	var _renderTermsOfPromotion = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTermsOfPromotion");
	class DiscountEarSubscription extends DiscountEar {
	  constructor(props) {
	    var _props$discountPercen, _props$termsUrl, _props$marketLabel;
	    super(props);
	    Object.defineProperty(this, _renderTermsOfPromotion, {
	      value: _renderTermsOfPromotion2
	    });
	    Object.defineProperty(this, _renderDiscountPercent, {
	      value: _renderDiscountPercent2
	    });
	    this.discountPercentage = (_props$discountPercen = props == null ? void 0 : props.discountPercentage) != null ? _props$discountPercen : null;
	    this.termsUrl = (_props$termsUrl = props == null ? void 0 : props.termsUrl) != null ? _props$termsUrl : null;
	    this.marketLabel = (_props$marketLabel = props == null ? void 0 : props.marketLabel) != null ? _props$marketLabel : '';
	  }
	  getContainer() {
	    var _this$container;
	    (_this$container = this.container) != null ? _this$container : this.container = main_core.Tag.render(_t$5 || (_t$5 = _$5`
			<aside class="rest-market-expired-popup__discount rest-market-expired-popup__discount--subscription">
				${0}
				<p class="rest-market-expired-popup__discount-description">
					${0}
				</p>
				${0}
			</aside>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderDiscountPercent)[_renderDiscountPercent](), main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_DISCOUNT_SUBSCRIPTION_DESCRIPTION${this.marketLabel}`), babelHelpers.classPrivateFieldLooseBase(this, _renderTermsOfPromotion)[_renderTermsOfPromotion]());
	    return this.container;
	  }
	}
	function _renderDiscountPercent2() {
	  if (this.discountPercentage) {
	    return main_core.Tag.render(_t2$5 || (_t2$5 = _$5`
				<p class="rest-market-expired-popup__discount-percentage">
					- ${0}%
				</p>
			`), this.discountPercentage);
	  }
	  return '';
	}
	function _renderTermsOfPromotion2() {
	  if (this.termsUrl) {
	    return main_core.Tag.render(_t3$4 || (_t3$4 = _$5`
				<a href="${0}" target="_blank" class="ui-link rest-market-expired-popup__discount-terms">
					${0}
				</a>
			`), this.termsUrl, main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_TERMS_OF_PROMOTION'));
	  }
	  return '';
	}

	let _$6 = t => t,
	  _t$6;
	class DiscountEarTransition extends DiscountEar {
	  getContainer() {
	    var _this$container;
	    (_this$container = this.container) != null ? _this$container : this.container = main_core.Tag.render(_t$6 || (_t$6 = _$6`
			<aside class="rest-market-expired-popup__discount">
				<p class="rest-market-expired-popup__discount-description">
					${0}
				</p>
			</aside>
		`), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DISCOUNT_DESCRIPTION', {
	      '[white-span]': '<span class="rest-market-expired-popup__discount-description-white">',
	      '[/white-span]': '</span>'
	    }));
	    return this.container;
	  }
	}

	class TrialButton extends MarketPopupButton {
	  getButtonConfig() {
	    return {
	      id: 'marketExpiredPopup_button_demo',
	      size: ui_buttons.Button.Size.MEDIUM,
	      color: ui_buttons.Button.Color.LIGHT_BORDER,
	      noCaps: true,
	      round: true
	    };
	  }
	  onClick() {
	    var _this$analytic;
	    this.getButton().unbindEvent('click');
	    this.getButton().setState(ui_buttons.Button.State.WAITING);
	    (_this$analytic = this.analytic) == null ? void 0 : _this$analytic.sendClickButton('demo');
	    main_core.ajax({
	      url: '/bitrix/tools/rest.php',
	      method: 'POST',
	      dataType: 'json',
	      data: {
	        sessid: BX.bitrix_sessid(),
	        action: 'activate_demo'
	      },
	      onsuccess: result => {
	        this.onSuccess();
	        if (result.error) {
	          ui_notification.UI.Notification.Center.notify({
	            content: result.error,
	            category: 'demo_subscribe_error',
	            position: 'top-right'
	          });
	        } else {
	          var _this$analytic2;
	          (_this$analytic2 = this.analytic) == null ? void 0 : _this$analytic2.sendDemoActivated();
	          ui_infoHelper.FeaturePromotersRegistry.getPromoter({
	            code: 'limit_market_trial_active'
	          }).show();
	        }
	      }
	    });
	  }
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$6,
	  _t3$5,
	  _t4$1,
	  _t5,
	  _t6,
	  _t7;
	var _getDemoButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDemoButton");
	var _getSubscribeButton$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSubscribeButton");
	var _getHideButton$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHideButton");
	var _renderButtonsForWarning = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderButtonsForWarning");
	var _renderButtonsForFinal = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderButtonsForFinal");
	var _getFeatureCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFeatureCode");
	var _showOpenLinesWidget = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showOpenLinesWidget");
	class MarketTransitionPopup extends MarketExpiredPopup {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _showOpenLinesWidget, {
	      value: _showOpenLinesWidget2
	    });
	    Object.defineProperty(this, _getFeatureCode, {
	      value: _getFeatureCode2
	    });
	    Object.defineProperty(this, _renderButtonsForFinal, {
	      value: _renderButtonsForFinal2
	    });
	    Object.defineProperty(this, _renderButtonsForWarning, {
	      value: _renderButtonsForWarning2
	    });
	    Object.defineProperty(this, _getHideButton$2, {
	      value: _getHideButton2$2
	    });
	    Object.defineProperty(this, _getSubscribeButton$2, {
	      value: _getSubscribeButton2$2
	    });
	    Object.defineProperty(this, _getDemoButton, {
	      value: _getDemoButton2
	    });
	  }
	  getCategory() {
	    return PopupCategory.TRANSITION;
	  }
	  getTitle() {
	    return main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_TITLE_${this.type}`);
	  }
	  renderDescription() {
	    const descriptionContainer = main_core.Tag.render(_t$7 || (_t$7 = _$7`
			<div class="rest-market-expired-popup__description">
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
			</div>
		`), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_1'), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_2'), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_3'));
	    if (this.type === 'FINAL') {
	      main_core.Dom.append(main_core.Tag.render(_t2$6 || (_t2$6 = _$7`
					<p class="rest-market-expired-popup__description-text">
						${0}
					</p>
				`), main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_DESCRIPTION_FINAL${this.marketLabel}`)), descriptionContainer);
	    }
	    main_core.Dom.append(main_core.Tag.render(_t3$5 || (_t3$5 = _$7`
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
			`), main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_${this.type}_DESCRIPTION${this.withDemo ? '_DEMO' : ''}`, {
	      '#DATE#': this.expireDate
	    })), descriptionContainer);
	    return descriptionContainer;
	  }
	  renderButtons() {
	    if (this.type === PopupType.WARNING) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderButtonsForWarning)[_renderButtonsForWarning]();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _renderButtonsForFinal)[_renderButtonsForFinal]();
	  }
	  show() {
	    super.show();
	    if (main_core.Type.isStringFilled(this.olWidgetCode) && (!this.withDemo || this.type === 'FINAL')) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showOpenLinesWidget)[_showOpenLinesWidget](window, document, `https://bitrix24.team/upload/crm/site_button/loader_${this.olWidgetCode}.js`);
	    }
	  }
	  onClose() {
	    var _BX$SiteButton;
	    (_BX$SiteButton = BX.SiteButton) == null ? void 0 : _BX$SiteButton.hide();
	    BX.userOptions.save('rest', 'marketTransitionPopupTs', null, Math.floor(Date.now() / 1000));
	    super.onClose();
	  }
	  renderAboutLink() {
	    const onclick = () => {
	      var _this$getAnalytic;
	      (_this$getAnalytic = this.getAnalytic()) == null ? void 0 : _this$getAnalytic.sendClickButton('details');
	    };
	    return main_core.Tag.render(_t4$1 || (_t4$1 = _$7`
			<span class="rest-market-expired-popup__details">
				<a
					class="ui-link rest-market-expired-popup__link"
					href="FEATURE_PROMOTER=${0}"
					onclick="${0}"
				>
					${0}
				</a>
			</span>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getFeatureCode)[_getFeatureCode](), onclick, main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DETAILS'));
	  }
	}
	function _getDemoButton2() {
	  return new TrialButton({
	    text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_DEMO'),
	    onSuccess: this.close.bind(this),
	    analytic: this.getAnalytic()
	  });
	}
	function _getSubscribeButton2$2() {
	  return new SubscribeButton({
	    text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_SUBSCRIBE'),
	    analytic: this.getAnalytic()
	  });
	}
	function _getHideButton2$2() {
	  return new HideButton({
	    text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_HIDE'),
	    onSuccess: () => {
	      BX.userOptions.save('rest', 'marketTransitionPopupDismiss', null, 'Y');
	      this.close();
	    },
	    analytic: this.getAnalytic()
	  });
	}
	function _renderButtonsForWarning2() {
	  return main_core.Tag.render(_t5 || (_t5 = _$7`
			<div class="rest-market-expired-popup__buttons-wrapper">
				<div class="rest-market-expired-popup__button-container">
					${0}
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getSubscribeButton$2)[_getSubscribeButton$2]().render(), this.withDemo ? babelHelpers.classPrivateFieldLooseBase(this, _getDemoButton)[_getDemoButton]().render() : '');
	}
	function _renderButtonsForFinal2() {
	  if (this.withDemo) {
	    return main_core.Tag.render(_t6 || (_t6 = _$7`
				<div class="rest-market-expired-popup__buttons-wrapper">
					${0}
					<div class="rest-market-expired-popup__button-container">
						${0}
						${0}
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getSubscribeButton$2)[_getSubscribeButton$2]().render(), babelHelpers.classPrivateFieldLooseBase(this, _getDemoButton)[_getDemoButton]().render(), babelHelpers.classPrivateFieldLooseBase(this, _getHideButton$2)[_getHideButton$2]().render());
	  }
	  return main_core.Tag.render(_t7 || (_t7 = _$7`
			<div class="rest-market-expired-popup__buttons-wrapper">
				<div class="rest-market-expired-popup__button-container">
					${0}
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getSubscribeButton$2)[_getSubscribeButton$2]().render(), babelHelpers.classPrivateFieldLooseBase(this, _getHideButton$2)[_getHideButton$2]().render());
	}
	function _getFeatureCode2() {
	  return `
			limit_v2_nosubscription_marketplace_withapplications
			${this.withDiscount ? '' : '_nodiscount'}
			_off
			${this.withDemo ? '' : '_no_demo'}
		`;
	}
	function _showOpenLinesWidget2(w, d, u) {
	  // eslint-disable-next-line unicorn/prefer-math-trunc
	  const s = d.createElement('script');
	  s.async = true;
	  s.src = `${u}?${Date.now() / 60000 | 0}`;
	  // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	  const h = d.getElementsByTagName('script')[0];
	  h.parentNode.insertBefore(s, h);
	}

	var _getPopupClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupClass");
	var _getAnalytic = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAnalytic");
	var _getListItemCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getListItemCount");
	var _getDiscountEar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDiscountEar");
	var _getMarketList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMarketList");
	class PopupFactory {
	  constructor(config) {
	    Object.defineProperty(this, _getMarketList, {
	      value: _getMarketList2
	    });
	    Object.defineProperty(this, _getDiscountEar, {
	      value: _getDiscountEar2
	    });
	    Object.defineProperty(this, _getListItemCount, {
	      value: _getListItemCount2
	    });
	    Object.defineProperty(this, _getAnalytic, {
	      value: _getAnalytic2
	    });
	    Object.defineProperty(this, _getPopupClass, {
	      value: _getPopupClass2
	    });
	    this.config = config;
	  }
	  async createPopup() {
	    let popup = null;
	    const analytic = babelHelpers.classPrivateFieldLooseBase(this, _getAnalytic)[_getAnalytic]();
	    const listItemCount = babelHelpers.classPrivateFieldLooseBase(this, _getListItemCount)[_getListItemCount]();
	    const {
	      appList,
	      integrationList
	    } = await babelHelpers.classPrivateFieldLooseBase(this, _getMarketList)[_getMarketList](listItemCount, analytic);
	    if (appList || integrationList) {
	      const discountEar = babelHelpers.classPrivateFieldLooseBase(this, _getDiscountEar)[_getDiscountEar]();
	      const PopupClass = babelHelpers.classPrivateFieldLooseBase(this, _getPopupClass)[_getPopupClass]();
	      popup = new PopupClass({
	        appList,
	        integrationList,
	        analytic,
	        discountEar,
	        expireDate: this.config.expireDate,
	        marketSubscriptionUrl: this.config.marketSubscriptionUrl,
	        withDemo: this.config.withDemo,
	        olWidgetCode: this.config.olWidgetCode,
	        type: this.config.type,
	        expireDays: this.config.expireDays
	      });
	    }
	    return popup;
	  }
	}
	function _getPopupClass2() {
	  switch (this.config.category) {
	    case PopupCategory.TRIAL:
	      return MarketTrialPopup;
	    case PopupCategory.SUBSCRIPTION:
	      return MarketSubscriptionPopup;
	    case PopupCategory.TRANSITION:
	    default:
	      return MarketTransitionPopup;
	  }
	}
	function _getAnalytic2() {
	  var _this$config$discount, _this$config$discount2;
	  return new Analytic({
	    withDiscount: (_this$config$discount = (_this$config$discount2 = this.config.discount) == null ? void 0 : _this$config$discount2.isAvailable) != null ? _this$config$discount : false,
	    popupType: this.config.type,
	    popupCategory: this.config.category
	  });
	}
	function _getListItemCount2() {
	  switch (this.config.category) {
	    case PopupCategory.TRIAL:
	    case PopupCategory.SUBSCRIPTION:
	      return 2;
	    case PopupCategory.TRANSITION:
	    default:
	      return 3;
	  }
	}
	function _getDiscountEar2() {
	  const discountConfig = this.config.discount;
	  if (!(discountConfig != null && discountConfig.isAvailable)) {
	    return null;
	  }
	  switch (this.config.category) {
	    case PopupCategory.TRIAL:
	    case PopupCategory.SUBSCRIPTION:
	      return new DiscountEarSubscription({
	        discountPercentage: discountConfig.percentage,
	        termsUrl: discountConfig.termsUrl,
	        marketLabel: this.config.isRenamedMarket ? '' : '_MARKET_PLUS'
	      });
	    case PopupCategory.TRANSITION:
	    default:
	      return new DiscountEarTransition();
	  }
	}
	async function _getMarketList2(limit, analytic) {
	  const getMarketListFromResponse = (response, moreLink, title, onClick) => {
	    if (!response || !response.data) {
	      return null;
	    }
	    const {
	      items,
	      count
	    } = response.data;
	    const marketList = [];
	    if (items.length === 0 || count < 1) {
	      return null;
	    }
	    Object.values(items).forEach(item => {
	      marketList.push(new MarketItem({
	        name: item.name,
	        icon: item.icon
	      }));
	    });
	    return new MarketList({
	      title,
	      count,
	      items: marketList,
	      link: moreLink,
	      onClick
	    });
	  };
	  let appList = null;
	  let integrationList = null;
	  await Promise.all([main_core.ajax.runAction('rest.integration.getApplicationList', {
	    data: {
	      limit
	    }
	  }), main_core.ajax.runAction('rest.integration.getIntegrationList', {
	    data: {
	      limit
	    }
	  })]).then(([appsResponse, integrationsResponse]) => {
	    appList = getMarketListFromResponse(appsResponse, '/market/installed/', main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_APPS'), () => {
	      analytic.sendClickButton('view_all_apps');
	    });
	    integrationList = getMarketListFromResponse(integrationsResponse, '/devops/list/', main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_INTEGRATIONS'), () => {
	      analytic.sendClickButton('view_all_integrations');
	    });
	  }).catch(error => {
	    console.log(error);
	  });
	  return {
	    appList,
	    integrationList
	  };
	}

	class CurtainPage {}
	CurtainPage.INTEGRATION = 'Integration';
	CurtainPage.APPLICATION = 'Application';
	CurtainPage.ANY_PAGE = 'AnyPage';

	let _$8 = t => t,
	  _t$8;
	var _panel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("panel");
	var _getPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPanel");
	var _sendAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendAnalytics");
	class MarketExpiredCurtain {
	  constructor(options) {
	    Object.defineProperty(this, _sendAnalytics, {
	      value: _sendAnalytics2
	    });
	    Object.defineProperty(this, _getPanel, {
	      value: _getPanel2
	    });
	    Object.defineProperty(this, _panel, {
	      writable: true,
	      value: null
	    });
	    this.options = options;
	  }
	  show() {
	    ui_bannerDispatcher.BannerDispatcher.critical.toQueue(onDone => {
	      const panel = babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel](onDone);
	      panel.show();
	      babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics]('show_notification_panel');
	    });
	  }
	  getRightButtons() {
	    return [];
	  }
	  getContent() {
	    throw new Error('Not Implemented');
	  }
	  onRightButtonClick() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPanel)[_getPanel]().hide();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendAnalytics)[_sendAnalytics]('click_button');
	  }
	  onHide() {}
	}
	function _getPanel2(onDone) {
	  var _babelHelpers$classPr, _babelHelpers$classPr2;
	  (_babelHelpers$classPr2 = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _panel))[_panel]) != null ? _babelHelpers$classPr2 : _babelHelpers$classPr[_panel] = new ui_notificationPanel.NotificationPanel({
	    content: main_core.Tag.render(_t$8 || (_t$8 = _$8`
				<span class="rest-market-expired-curtain">${0}</span>
			`), this.getContent()),
	    backgroundColor: '#E89B06',
	    crossColor: '#FFFFFF',
	    leftIcon: new ui_iconSet_api_core.Icon({
	      icon: ui_iconSet_api_core.Main.MARKET_1,
	      color: '#FFFFFF'
	    }),
	    rightButtons: this.getRightButtons(),
	    events: {
	      onHide: () => {
	        onDone();
	        this.onHide();
	      }
	    },
	    zIndex: 1001
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _panel)[_panel];
	}
	function _sendAnalytics2(event) {
	  const params = {
	    tool: 'infohelper',
	    category: 'market',
	    event,
	    type: 'notification_panel'
	  };
	  ui_analytics.sendData(params);
	}

	class MarketTrialCurtain extends MarketExpiredCurtain {
	  getRightButtons() {
	    return [new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_SUBSCRIBE'),
	      size: ui_buttons.Button.Size.EXTRA_SMALL,
	      color: ui_buttons.Button.Color.CURTAIN_WARNING,
	      tag: ui_buttons.Button.Tag.LINK,
	      noCaps: true,
	      round: true,
	      props: {
	        href: this.options.marketSubscriptionUrl
	      },
	      onclick: () => super.onRightButtonClick.bind(this)
	    })];
	  }
	  getContent() {
	    return this.options.type === PopupType.FINAL ? main_core.Loc.getMessage(`REST_MARKET_EXPIRED_CURTAIN_TRIAL_FINAL_TEXT${this.marketLabel}`) : main_core.Loc.getMessage(`REST_MARKET_EXPIRED_CURTAIN_TRIAL_WARNING_TEXT${this.marketLabel}`, {
	      '#DAYS#': this.options.expireDays
	    });
	  }
	  onHide() {
	    if (this.options.type === PopupType.FINAL) {
	      BX.userOptions.save('rest', `marketSubscriptionCurtain${this.options.curtainPage}Dismiss`, null, 'Y');
	    } else {
	      BX.userOptions.save('rest', `marketSubscriptionCurtain${this.options.curtainPage}Ts`, null, Math.floor(Date.now() / 1000));
	    }
	  }
	}

	class MarketSubscriptionCurtain extends MarketExpiredCurtain {
	  getRightButtons() {
	    return [new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_RENEW_SUBSCRIPTION'),
	      size: ui_buttons.Button.Size.EXTRA_SMALL,
	      color: ui_buttons.Button.Color.CURTAIN_WARNING,
	      tag: ui_buttons.Button.Tag.LINK,
	      noCaps: true,
	      round: true,
	      props: {
	        href: this.options.marketSubscriptionUrl
	      },
	      onclick: () => super.onRightButtonClick.bind(this)
	    })];
	  }
	  getContent() {
	    return this.options.type === PopupType.FINAL ? main_core.Loc.getMessage(`REST_MARKET_EXPIRED_CURTAIN_SUBSCRIPTION_FINAL_TEXT${this.marketLabel}`) : main_core.Loc.getMessage(`REST_MARKET_EXPIRED_CURTAIN_SUBSCRIPTION_WARNING_TEXT${this.marketLabel}`, {
	      '#DAYS#': this.options.expireDays
	    });
	  }
	  onHide() {
	    if (this.options.type === PopupType.FINAL) {
	      BX.userOptions.save('rest', `marketSubscriptionCurtain${this.options.curtainPage}Dismiss`, null, 'Y');
	    } else {
	      BX.userOptions.save('rest', `marketSubscriptionCurtain${this.options.curtainPage}Ts`, null, Math.floor(Date.now() / 1000));
	    }
	  }
	}

	class MarketTransitionCurtain extends MarketExpiredCurtain {
	  getRightButtons() {
	    return [new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_CURTAIN_TRANSITION_BUTTON'),
	      size: ui_buttons.Button.Size.EXTRA_SMALL,
	      color: ui_buttons.Button.Color.CURTAIN_WARNING,
	      tag: ui_buttons.Button.Tag.LINK,
	      noCaps: true,
	      round: true,
	      props: {
	        href: 'FEATURE_PROMOTER=limit_v2_nosubscription_marketplace_withapplications_off'
	      },
	      onclick: () => super.onRightButtonClick.bind(this)
	    })];
	  }
	  getContent() {
	    return main_core.Loc.getMessage('REST_MARKET_EXPIRED_CURTAIN_TRANSITION_TEXT');
	  }
	  onHide() {
	    BX.userOptions.save('rest', `marketTransitionCurtain${this.options.curtainPage}Ts`, null, Math.floor(Date.now() / 1000));
	  }
	}

	var _getCurtainClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurtainClass");
	class CurtainFactory {
	  constructor(config) {
	    Object.defineProperty(this, _getCurtainClass, {
	      value: _getCurtainClass2
	    });
	    this.config = config;
	  }
	  createCurtain(curtainPage) {
	    const CurtainClass = babelHelpers.classPrivateFieldLooseBase(this, _getCurtainClass)[_getCurtainClass]();
	    return new CurtainClass({
	      marketSubscriptionUrl: this.config.marketSubscriptionUrl,
	      type: this.config.type,
	      expireDays: this.config.expireDays,
	      curtainPage
	    });
	  }
	}
	function _getCurtainClass2() {
	  switch (this.config.category) {
	    case PopupCategory.TRIAL:
	      return MarketTrialCurtain;
	    case PopupCategory.SUBSCRIPTION:
	      return MarketSubscriptionCurtain;
	    case PopupCategory.TRANSITION:
	    default:
	      return MarketTransitionCurtain;
	  }
	}

	class MarketExpired {
	  constructor(config) {
	    this.config = config;
	  }
	  static async getPopup(config = null) {
	    const popupConfig = config != null ? config : main_core.Extension.getSettings('rest.market-expired');
	    const manager = new PopupFactory(popupConfig);
	    return manager.createPopup();
	  }
	  static getCurtain(curtainPage, config = null) {
	    const curtainConfig = config != null ? config : main_core.Extension.getSettings('rest.market-expired');
	    const manager = new CurtainFactory(curtainConfig);
	    return manager.createCurtain(curtainPage);
	  }
	}

	exports.CurtainPage = CurtainPage;
	exports.MarketExpired = MarketExpired;

}((this.BX.Rest = this.BX.Rest || {}),BX.Main,BX,BX.Event,BX,BX.UI,BX.UI,BX.UI,BX.UI.IconSet,BX.UI.Analytics,BX,BX.UI,BX));
//# sourceMappingURL=market-expired.bundle.js.map
