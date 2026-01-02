/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_popup,ui_analytics,ui_lottie) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	class MobilePromoter {
	  constructor(options) {
	    this.target = main_core.Type.isElementNode(options.target) ? options.target : window;
	    this.qrContent = main_core.Type.isString(options.qrContent) ? options.qrContent : '';
	    this.position = main_core.Type.isObject(options.position) ? options.position : null;
	    this.className = main_core.Type.isString(options.className) ? options.className : null;
	    this.node = {
	      content: null,
	      phoneBg: null,
	      qr: null,
	      qrOverlay: null
	    };
	    this.title = main_core.Type.isString(options.title) ? options.title : main_core.Loc.getMessage('UI_MOBILE_PROMOTER_TITLE');
	    this.content = main_core.Type.isElementNode(options.content) ? options.content : null;
	    this.analytics = main_core.Type.isObject(options.analytics) ? options.analytics : null;
	    this.analyticsParameters = {
	      tool: 'intranet',
	      category: 'activation'
	    };
	    this.init();
	  }
	  getPopup() {
	    if (!this.popup) {
	      this.popup = new main_popup.Popup({
	        bindElement: this.target,
	        borderRadius: '32px 32px 32px 32px',
	        padding: 0,
	        contentPadding: 0,
	        closeIcon: {
	          top: '19px',
	          right: '19px'
	        },
	        content: this.getContent(),
	        className: `ui-mobile-promoter__popup ${this.className} --qr-hide`,
	        animation: 'fading-slide',
	        maxWidth: 550,
	        minHeight: 330,
	        autoHide: true,
	        overlay: {
	          backgroundColor: 'black',
	          opacity: 15
	        },
	        events: {
	          onPopupShow: () => {
	            // this.getAnimation().play();
	          },
	          onAfterShow: () => {
	            this.setAnalyticParameters({
	              event: 'qrcode_view'
	            });
	            this.sendAnalytics();
	          },
	          onPopupClose: () => {
	            // this.getAnimation().stop();
	            main_core.Dom.removeClass(this.getQrBg(), '--qr-animation');
	          },
	          onAfterClose: () => {
	            this.setAnalyticParameters({
	              event: 'qrcode_close'
	            });
	            this.sendAnalytics();
	          }
	        }
	      });
	    }
	    return this.popup;
	  }
	  getAnimation() {
	    if (!this.lottieAnim) {
	      this.lottieAnim = ui_lottie.Lottie.loadAnimation({
	        autoplay: false,
	        container: this.getPhoneBg(),
	        renderer: 'svg',
	        path: '/bitrix/js/ui/mobile-promoter/animation/invitation.json',
	        loop: false,
	        name: 'invitation-anim'
	      });
	    }
	    return this.lottieAnim;
	  }
	  getQrBg() {
	    if (!this.node.qr) {
	      this.node.qr = main_core.Tag.render(_t || (_t = _`
				<div class="ui-mobile-promoter__popup-qr"></div>
			`));
	    }
	    return this.node.qr;
	  }
	  getPhoneBg() {
	    if (!this.node.phoneBg) {
	      this.node.phoneBg = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-mobile-promoter__popup-phone"></div>
			`));
	    }
	    return this.node.phoneBg;
	  }
	  getDefaultContent() {
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<ul class="ui-mobile-promoter__popup-list">
				<li class="ui-mobile-promoter__popup-list-item">${0}</li>
				<li class="ui-mobile-promoter__popup-list-item">${0}</li>
				<li class="ui-mobile-promoter__popup-list-item">${0}</li>
			</ul>
			<div class="ui-mobile-promoter__popup-list-afterlist">
				<div class="ui-mobile-promoter__popup-desc">${0}</div>
				<div class="ui-mobile-promoter__popup-info">${0}</div>
			</div>
		`), main_core.Loc.getMessage('UI_MOBILE_PROMOTER_LIST_ITEM_1'), main_core.Loc.getMessage('UI_MOBILE_PROMOTER_LIST_ITEM_2'), main_core.Loc.getMessage('UI_MOBILE_PROMOTER_LIST_ITEM_3'), main_core.Loc.getMessage('UI_MOBILE_PROMOTER_DESC'), main_core.Loc.getMessage('UI_MOBILE_PROMOTER_INFO'));
	  }
	  showQrCode() {
	    main_core.Dom.removeClass(this.getContent(), '--qr-hide');
	    main_core.Dom.addClass(this.getQrOverlay(), '--hide');
	    main_core.Dom.addClass(this.getQrBg(), '--qr-animation');
	  }
	  getQrOverlay() {
	    if (!this.node.qrOverlay) {
	      const qrShowButton = main_core.Tag.render(_t4 || (_t4 = _`
				<span class="ui-btn --air ui-btn-no-caps --style-tinted ui-btn-xs">
					<span class="ui-btn-text">
						<span class="ui-btn-text-inner">${0}</span>
					</span>
				</span>
			`), main_core.Loc.getMessage('UI_MOBILE_PROMOTER_SHOW_QR'));
	      main_core.Event.bind(qrShowButton, 'click', () => this.showQrCode());
	      this.node.qrOverlay = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="ui-mobile-promoter__popup-qr-overlay">
					<div class="ui-mobile-promoter__popup-qr-overlay-text">
						${0}
					</div>
				</div>
			`), qrShowButton);
	      main_core.Event.bind(this.node.qrOverlay, 'transitionend', () => {
	        main_core.Dom.remove(this.node.qrOverlay);
	      });
	    }
	    return this.node.qrOverlay;
	  }
	  getContent() {
	    if (!this.node.content) {
	      var _this$content;
	      this.node.content = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="ui-mobile-promoter__popup-wrap --qr-hide">
					<div class="ui-mobile-promoter__popup-phone-box">
						${0}
						${0}
						${0}
					</div>
					<div class="ui-mobile-promoter__popup-content">
						<div class="ui-mobile-promoter__popup-title">${0}</div>
						<div class="ui-mobile-promoter__popup-content">
							${0}
						</div>
					</div>
				</div>
			`), this.getPhoneBg(), this.getQrOverlay(), this.getQrBg(), this.title, (_this$content = this.content) != null ? _this$content : this.getDefaultContent());
	    }
	    return this.node.content;
	  }
	  init() {
	    // eslint-disable-next-line no-undef
	    new QRCode(this.getQrBg(), {
	      text: this.qrContent,
	      width: 123,
	      height: 123,
	      colorDark: '#000000',
	      colorLight: '#ffffff'
	    });
	    if (this.analytics) {
	      this.setAnalyticParameters(this.analytics);
	    }
	    if (this.position) {
	      this.getPopup().setFixed(true);
	      main_core.Dom.addClass(this.getPopup().getPopupContainer(), '--right-bottom');
	      Object.entries(this.position).forEach(([key, value]) => {
	        main_core.Dom.style(this.getPopup().getPopupContainer(), key, `${value}px`);
	      });
	    }
	  }
	  show() {
	    this.getPopup().show();
	  }
	  close() {
	    this.getPopup().close();
	  }
	  setAnalyticParameters(parameters) {
	    this.analyticsParameters = {
	      ...this.analyticsParameters,
	      ...parameters
	    };
	  }
	  sendAnalytics() {
	    ui_analytics.sendData(this.analyticsParameters);
	  }
	}

	exports.MobilePromoter = MobilePromoter;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Main,BX.UI.Analytics,BX.UI));
//# sourceMappingURL=mobile-promoter.bundle.js.map
