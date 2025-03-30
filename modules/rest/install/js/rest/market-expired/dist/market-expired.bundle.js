/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup,ui_buttons,main_core_events,ui_infoHelper,ui_notification,ui_analytics,main_core) {
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
	    return null;
	  }
	  const listContainer = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="rest-market-list__container"></div>
		`));
	  babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].forEach(item => {
	    item.renderTo(listContainer);
	  });
	  return listContainer;
	}

	let _$2 = t => t,
	  _t$2;
	class DiscountEar {
	  render() {
	    return main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<aside class="rest-market-expired-popup__discount">
				<p class="rest-market-expired-popup__discount-description">
					${0}
				</p>
			</aside>
		`), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DISCOUNT_DESCRIPTION', {
	      '[white-span]': '<span class="rest-market-expired-popup__discount-description-white">',
	      '[/white-span]': '</span>'
	    }));
	  }
	}

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
	      event: 'demo_activated'
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
	  return this.context.popupType === 'WARNING' ? 'pre_disconnection_alert' : 'post_disconnection_notice';
	}
	function _getP2() {
	  return `discount_${this.context.withDiscount ? 'Y' : 'N'}`;
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$2,
	  _t3$1,
	  _t4,
	  _t5;
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _appList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appList");
	var _integrationList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("integrationList");
	var _marketSubscriptionUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("marketSubscriptionUrl");
	var _withDiscount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("withDiscount");
	var _discountEarContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("discountEarContainer");
	var _analytic = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("analytic");
	var _getFeatureCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFeatureCode");
	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");
	var _getContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainer");
	var _renderCloseIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCloseIcon");
	var _getDiscountEarContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDiscountEarContainer");
	var _renderMarketList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMarketList");
	var _renderAboutLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAboutLink");
	var _showOlWidget = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showOlWidget");
	class MarketExpiredPopup extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _showOlWidget, {
	      value: _showOlWidget2
	    });
	    Object.defineProperty(this, _renderAboutLink, {
	      value: _renderAboutLink2
	    });
	    Object.defineProperty(this, _renderMarketList, {
	      value: _renderMarketList2
	    });
	    Object.defineProperty(this, _getDiscountEarContainer, {
	      value: _getDiscountEarContainer2
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
	    Object.defineProperty(this, _getFeatureCode, {
	      value: _getFeatureCode2
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
	    Object.defineProperty(this, _marketSubscriptionUrl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _withDiscount, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _discountEarContainer, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _analytic, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Rest.MarketExpired:Popup');
	    this.transitionPeriodEndDate = options.transitionPeriodEndDate;
	    babelHelpers.classPrivateFieldLooseBase(this, _appList)[_appList] = options.appList;
	    babelHelpers.classPrivateFieldLooseBase(this, _integrationList)[_integrationList] = options.integrationList;
	    babelHelpers.classPrivateFieldLooseBase(this, _marketSubscriptionUrl)[_marketSubscriptionUrl] = options.marketSubscriptionUrl;
	    babelHelpers.classPrivateFieldLooseBase(this, _withDiscount)[_withDiscount] = options.withDiscount;
	    this.withDemo = options.withDemo;
	    this.olWidgetCode = options.olWidgetCode;
	    babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic] = options.analytic;
	  }
	  getType() {
	    return '';
	  }
	  getTitle() {
	    return '';
	  }
	  show() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3, _babelHelpers$classPr4;
	    (_babelHelpers$classPr2 = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup))[_popup]) != null ? _babelHelpers$classPr2 : _babelHelpers$classPr[_popup] = main_popup.PopupWindowManager.create(`marketExpiredPopup_${this.getType()}`, null, {
	      animation: {
	        showClassName: 'rest-market-expired-popup__show',
	        closeAnimationType: 'animation'
	      },
	      overlay: true,
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent](),
	      disableScroll: true,
	      padding: 0,
	      className: 'rest-market-expired-popup-wrapper',
	      events: {
	        onClose: this.onClose.bind(this)
	      }
	    });
	    (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr3.show();
	    (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr4.sendShow();

	    // hack for blur
	    if (babelHelpers.classPrivateFieldLooseBase(this, _withDiscount)[_withDiscount]) {
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer](), {
	        maxHeight: `${babelHelpers.classPrivateFieldLooseBase(this, _getDiscountEarContainer)[_getDiscountEarContainer]().offsetHeight}px`
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].adjustPosition();
	    }
	    if (main_core.Type.isStringFilled(this.olWidgetCode) && (!this.withDemo || this.getType() === 'FINAL')) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showOlWidget)[_showOlWidget](window, document, `https://bitrix24.team/upload/crm/site_button/loader_${this.olWidgetCode}.js`);
	    }
	  }
	  onClose() {
	    var _BX$SiteButton;
	    (_BX$SiteButton = BX.SiteButton) == null ? void 0 : _BX$SiteButton.hide();
	    this.emit('onClose');
	    BX.userOptions.save('rest', 'marketTransitionPopupTs', null, Math.floor(Date.now() / 1000));
	  }

	  /**
	   * limit_v2_nosubscription_marketplace_withapplications_off
	   * limit_v2_nosubscription_marketplace_withapplications_off_no_demo
	   * limit_v2_nosubscription_marketplace_withapplications_nodiscount_off
	   * limit_v2_nosubscription_marketplace_withapplications_nodiscount_off_no_demo
	   */

	  renderDescription() {
	    return null;
	  }
	  renderButtons() {
	    return null;
	  }
	  getSubscribeButton() {
	    return new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_SUBSCRIBE'),
	      className: 'rest-market-expired-popup__button',
	      id: 'marketExpiredPopup_button_subscribe',
	      size: ui_buttons.Button.Size.MEDIUM,
	      color: ui_buttons.Button.Color.SUCCESS,
	      noCaps: true,
	      round: true,
	      tag: ui_buttons.Button.Tag.LINK,
	      link: babelHelpers.classPrivateFieldLooseBase(this, _marketSubscriptionUrl)[_marketSubscriptionUrl],
	      onclick: () => {
	        var _babelHelpers$classPr5;
	        (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr5.sendClickButton('buy');
	      }
	    });
	  }
	  getDemoButton() {
	    const demoButton = new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_DEMO'),
	      className: 'rest-market-expired-popup__button',
	      id: 'marketExpiredPopup_button_demo',
	      size: ui_buttons.Button.Size.MEDIUM,
	      color: ui_buttons.Button.Color.LIGHT_BORDER,
	      noCaps: true,
	      round: true,
	      onclick: () => {
	        var _babelHelpers$classPr6;
	        demoButton.unbindEvent('click');
	        demoButton.setState(ui_buttons.Button.State.WAITING);
	        (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr6.sendClickButton('demo');
	        main_core.ajax({
	          url: '/bitrix/tools/rest.php',
	          method: 'POST',
	          dataType: 'json',
	          data: {
	            sessid: BX.bitrix_sessid(),
	            action: 'activate_demo'
	          },
	          onsuccess: result => {
	            babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	            if (result.error) {
	              ui_notification.UI.Notification.Center.notify({
	                content: result.error,
	                category: 'demo_subscribe_error',
	                position: 'top-right'
	              });
	            } else {
	              var _babelHelpers$classPr7;
	              (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr7.sendDemoActivated();
	              ui_infoHelper.FeaturePromotersRegistry.getPromoter({
	                code: 'limit_market_trial_active'
	              }).show();
	            }
	          }
	        });
	      }
	    });
	    return demoButton;
	  }
	  getHideButton() {
	    return new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_BUTTON_HIDE'),
	      className: 'rest-market-expired-popup__button rest-market-expired-popup__button--link',
	      id: 'marketExpiredPopup_button_hide',
	      size: ui_buttons.Button.Size.EXTRA_SMALL,
	      color: ui_buttons.Button.Color.LINK,
	      noCaps: true,
	      onclick: () => {
	        var _babelHelpers$classPr8, _babelHelpers$classPr9;
	        (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr8.close();
	        (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr9.sendClickButton('ok');
	        BX.userOptions.save('rest', 'marketTransitionPopupDismiss', null, 'Y');
	      }
	    });
	  }
	}
	function _getFeatureCode2() {
	  return `
			limit_v2_nosubscription_marketplace_withapplications
			${babelHelpers.classPrivateFieldLooseBase(this, _withDiscount)[_withDiscount] ? '' : '_nodiscount'}
			_off
			${this.withDemo ? '' : '_no_demo'}
		`;
	}
	function _getContent2() {
	  return main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div class="rest-market-expired-popup">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer]());
	}
	function _getContainer2() {
	  var _babelHelpers$classPr10, _babelHelpers$classPr11;
	  (_babelHelpers$classPr11 = (_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _container))[_container]) != null ? _babelHelpers$classPr11 : _babelHelpers$classPr10[_container] = main_core.Tag.render(_t2$2 || (_t2$2 = _$3`
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
		`), babelHelpers.classPrivateFieldLooseBase(this, _withDiscount)[_withDiscount] ? babelHelpers.classPrivateFieldLooseBase(this, _getDiscountEarContainer)[_getDiscountEarContainer]() : '', this.getTitle(), this.renderDescription(), babelHelpers.classPrivateFieldLooseBase(this, _renderAboutLink)[_renderAboutLink](), this.renderButtons(), babelHelpers.classPrivateFieldLooseBase(this, _renderMarketList)[_renderMarketList](), babelHelpers.classPrivateFieldLooseBase(this, _renderCloseIcon)[_renderCloseIcon]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	}
	function _renderCloseIcon2() {
	  const onClick = () => {
	    var _babelHelpers$classPr12;
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	    (_babelHelpers$classPr12 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr12.sendClickButton('cancel');
	  };
	  return main_core.Tag.render(_t3$1 || (_t3$1 = _$3`
			<div class="rest-market-expired-popup__close-icon ui-icon-set --cross-30" onclick="${0}"></div>
		`), onClick);
	}
	function _getDiscountEarContainer2() {
	  var _babelHelpers$classPr13, _babelHelpers$classPr14;
	  (_babelHelpers$classPr14 = (_babelHelpers$classPr13 = babelHelpers.classPrivateFieldLooseBase(this, _discountEarContainer))[_discountEarContainer]) != null ? _babelHelpers$classPr14 : _babelHelpers$classPr13[_discountEarContainer] = new DiscountEar(this.withDemo).render();
	  return babelHelpers.classPrivateFieldLooseBase(this, _discountEarContainer)[_discountEarContainer];
	}
	function _renderMarketList2() {
	  var _babelHelpers$classPr15, _babelHelpers$classPr16;
	  return main_core.Tag.render(_t4 || (_t4 = _$3`
			<aside class="rest-market-expired-popup__aside">
				${0}
				${0}
			</aside>
		`), (_babelHelpers$classPr15 = babelHelpers.classPrivateFieldLooseBase(this, _appList)[_appList]) == null ? void 0 : _babelHelpers$classPr15.render(), (_babelHelpers$classPr16 = babelHelpers.classPrivateFieldLooseBase(this, _integrationList)[_integrationList]) == null ? void 0 : _babelHelpers$classPr16.render());
	}
	function _renderAboutLink2() {
	  const onclick = () => {
	    var _babelHelpers$classPr17;
	    (_babelHelpers$classPr17 = babelHelpers.classPrivateFieldLooseBase(this, _analytic)[_analytic]) == null ? void 0 : _babelHelpers$classPr17.sendClickButton('details');
	  };
	  return main_core.Tag.render(_t5 || (_t5 = _$3`
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
	function _showOlWidget2(w, d, u) {
	  const s = d.createElement('script');
	  s.async = true;
	  s.src = `${u}?${Date.now() / 60000 | 0}`;
	  const h = d.getElementsByTagName('script')[0];
	  h.parentNode.insertBefore(s, h);
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$3,
	  _t3$2;
	class FinalMarketExpiredPopup extends MarketExpiredPopup {
	  getType() {
	    return 'final';
	  }
	  getTitle() {
	    return main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_TITLE_FINAL');
	  }
	  renderDescription() {
	    return main_core.Tag.render(_t$4 || (_t$4 = _$4`
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
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
			</div>
		`), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_1'), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_2'), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_3'), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_FINAL'), main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_FINAL_DESCRIPTION${this.withDemo ? '_DEMO' : ''}`));
	  }
	  renderButtons() {
	    if (this.withDemo) {
	      return main_core.Tag.render(_t2$3 || (_t2$3 = _$4`
				<div class="rest-market-expired-popup__buttons-wrapper">
					${0}
					<div class="rest-market-expired-popup__button-container">
						${0}
						${0}
					</div>
				</div>
			`), this.getSubscribeButton().render(), this.getDemoButton().render(), this.getHideButton().render());
	    }
	    return main_core.Tag.render(_t3$2 || (_t3$2 = _$4`
			<div class="rest-market-expired-popup__button-container">
				${0}
				${0}
			</div>
		`), this.getSubscribeButton().render(), this.getHideButton().render());
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$4;
	class WarningMarketExpiredPopup extends MarketExpiredPopup {
	  getType() {
	    return 'warning';
	  }
	  renderDescription() {
	    return main_core.Tag.render(_t$5 || (_t$5 = _$5`
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
				<p class="rest-market-expired-popup__description-text">
					${0}
				</p>
			</div>
		`), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_1'), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_2'), main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_DESCRIPTION_3'), main_core.Loc.getMessage(`REST_MARKET_EXPIRED_POPUP_WARNING_DESCRIPTION${this.withDemo ? '_DEMO' : ''}`, {
	      '#DATE#': this.transitionPeriodEndDate
	    }));
	  }
	  getTitle() {
	    return main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_TITLE_WARNING');
	  }
	  renderButtons() {
	    return main_core.Tag.render(_t2$4 || (_t2$4 = _$5`
			<div class="rest-market-expired-popup__button-container">
				${0}
				${0}
			</div>
		`), this.getSubscribeButton().render(), this.withDemo ? this.getDemoButton().render() : '');
	  }
	}

	const POPUPS = {
	  WARNING: WarningMarketExpiredPopup,
	  FINAL: FinalMarketExpiredPopup
	};
	class MarketExpired {
	  static async getPopup() {
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
	    const options = main_core.Extension.getSettings('rest.market-expired');
	    let popup = null;
	    await Promise.all([main_core.ajax.runAction('rest.integration.getApplicationList', {
	      data: {
	        limit: 3
	      }
	    }), main_core.ajax.runAction('rest.integration.getIntegrationList', {
	      data: {
	        limit: 3
	      }
	    })]).then(([appsResponse, integrationsResponse]) => {
	      const analytic = new Analytic({
	        withDiscount: options.withDiscount,
	        popupType: options.type
	      });
	      const appList = getMarketListFromResponse(appsResponse, '/market/installed/', main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_APPS'), () => {
	        analytic.sendClickButton('view_all_apps');
	      });
	      const integrationList = getMarketListFromResponse(integrationsResponse, '/devops/list/', main_core.Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_TITLE_INTEGRATIONS'), () => {
	        analytic.sendClickButton('view_all_integrations');
	      });
	      if (appList || integrationList) {
	        const PopupClass = POPUPS[options.type];
	        if (PopupClass) {
	          popup = new PopupClass({
	            transitionPeriodEndDate: options.transitionPeriodEndDate,
	            appList,
	            integrationList,
	            marketSubscriptionUrl: options.marketSubscriptionUrl,
	            withDiscount: options.withDiscount,
	            withDemo: options.withDemo,
	            olWidgetCode: options.olWidgetCode,
	            analytic
	          });
	        }
	      }
	    }).catch(error => {
	      console.log(error);
	    });
	    return popup;
	  }
	}

	exports.MarketItem = MarketItem;
	exports.MarketList = MarketList;
	exports.WarningMarketExpiredPopup = WarningMarketExpiredPopup;
	exports.FinalMarketExpiredPopup = FinalMarketExpiredPopup;
	exports.MarketExpired = MarketExpired;

}((this.BX.Rest = this.BX.Rest || {}),BX.Main,BX.UI,BX.Event,BX.UI,BX,BX.UI.Analytics,BX));
//# sourceMappingURL=market-expired.bundle.js.map
