/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_popup,main_loader,pull_client) {
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
	class QrAuthorization {
	  constructor(options = {}) {
	    var _options$showFishingW, _options$showBottom, _Extension$getSetting, _Extension$getSetting2;
	    this.title = options.title || null;
	    this.content = options.content || null;
	    this.bottomText = options.bottomText || main_core.Loc.getMessage('UI_QR_AUTHORIZE_TAKE_CODE');
	    this.showFishingWarning = (_options$showFishingW = options.showFishingWarning) != null ? _options$showFishingW : false;
	    this.showBottom = (_options$showBottom = options.showBottom) != null ? _options$showBottom : true;
	    this.helpLink = options.helpLink || null;
	    this.helpCode = options.helpCode || null;
	    this.qr = options.qr || null;
	    this.popupParam = options.popupParam || null;
	    this.intent = options.intent || 'calendar';
	    this.popup = null;
	    this.loader = null;
	    this.ttl = (_Extension$getSetting = (_Extension$getSetting2 = main_core.Extension.getSettings('ui.qrauthorization')) == null ? void 0 : _Extension$getSetting2.ttl) != null ? _Extension$getSetting : 60;
	    this.ttlInterval = null;
	    this.qrNode = null;
	    this.successNode = null;
	    this.loadingNode = null;
	    this.isSubscribe = false;
	  }
	  createQrCodeImage() {
	    main_core.Dom.clean(this.getQrNode());
	    if (main_core.Type.isString(this.qr)) {
	      this.clean();
	      new QRCode(this.getQrNode(), {
	        text: this.qr,
	        width: 180,
	        height: 180
	      });
	      return;
	    }
	    this.loading();
	    main_core.ajax.runAction('mobile.deeplink.get', {
	      data: {
	        intent: this.intent,
	        ttl: this.ttl
	      }
	    }).then(response => {
	      var _response$data;
	      const link = (_response$data = response.data) == null ? void 0 : _response$data.link;
	      if (link) {
	        this.clean();
	        new QRCode(this.getQrNode(), {
	          text: link,
	          width: 180,
	          height: 180
	        });
	        if (!this.isSubscribe) {
	          this.isSubscribe = true;
	          this.subscribe();
	        }
	      }
	    }).catch(() => {});
	  }
	  subscribe() {
	    if (pull_client.PULL) {
	      pull_client.PULL.subscribe({
	        type: 'BX.PullClient.SubscriptionType.Server',
	        moduleId: 'mobile',
	        command: 'onDeeplinkShouldRefresh',
	        callback: params => {
	          this.success();
	        }
	      });
	    }
	  }
	  getQrNode() {
	    if (!this.qrNode) {
	      this.qrNode = main_core.Tag.render(_t || (_t = _`
				<div class="ui-qr-authorization__popup-qr"></div>
			`));
	    }
	    return this.qrNode;
	  }
	  getPopup() {
	    if (!this.popup) {
	      var _this$title, _this$title2, _this$popupParam$clas, _this$popupParam, _this$popupParam$widt, _this$popupParam2, _this$popupParam3, _this$popupParam4, _this$popupParam$over, _this$popupParam5, _this$popupParam$auto, _this$popupParam6;
	      const title = main_core.Type.isObject(this.title) ? (_this$title = this.title) == null ? void 0 : _this$title.text : this.title;
	      const titleSize = main_core.Type.isObject(this.title) ? (_this$title2 = this.title) == null ? void 0 : _this$title2.size : '';
	      const container = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-qr-authorization__popup-wrapper">
					<div class="ui-qr-authorization__popup-top ${0}">
						<div class="ui-qr-authorization__popup-left ${0}">
							${0}
							${0}
						</div>
						<div class="ui-qr-authorization__popup-right ${0}" data-role="ui-qr-authorization__qr-node"></div>
					</div>
					${0}
					${0}
				</div>
			`), this.content ? '' : '--direction-column', title ? '' : '--flex', title ? `<div class="ui-qr-authorization__popup-title --${titleSize}">${title}</div>` : '', this.content ? `<div class="ui-qr-authorization__popup-text">${this.content}</div>` : '', this.title ? '' : '--no-margin', this.renderFishingWarning(), this.renderBottom());
	      const popupWidth = this.content ? 710 : 405;
	      const popupParam = {
	        className: (_this$popupParam$clas = (_this$popupParam = this.popupParam) == null ? void 0 : _this$popupParam.className) != null ? _this$popupParam$clas : 'ui-qr-authorization__popup ui-qr-authorization__popup-scope',
	        width: (_this$popupParam$widt = (_this$popupParam2 = this.popupParam) == null ? void 0 : _this$popupParam2.width) != null ? _this$popupParam$widt : popupWidth,
	        content: container,
	        closeByEsc: (_this$popupParam3 = this.popupParam) != null && _this$popupParam3.closeByEsc ? (_this$popupParam4 = this.popupParam) == null ? void 0 : _this$popupParam4.className : true,
	        overlay: (_this$popupParam$over = (_this$popupParam5 = this.popupParam) == null ? void 0 : _this$popupParam5.overlay) != null ? _this$popupParam$over : false,
	        autoHide: (_this$popupParam$auto = (_this$popupParam6 = this.popupParam) == null ? void 0 : _this$popupParam6.autoHide) != null ? _this$popupParam$auto : true,
	        closeIcon: {
	          top: '14px',
	          right: '15px'
	        },
	        events: {
	          onPopupShow: () => {
	            this.createQrCodeImage();
	            this.ttlInterval = setInterval(() => {
	              this.createQrCodeImage();
	            }, this.ttl * 1000);
	            const qrTarget = this.getPopup().getContentContainer().querySelector('[data-role="ui-qr-authorization__qr-node"]');
	            if (qrTarget) {
	              main_core.Dom.append(this.getQrNode(), qrTarget);
	            }
	          },
	          onPopupClose: () => {
	            clearInterval(this.ttlInterval);
	          }
	        },
	        padding: 0,
	        animation: 'fading-slide'
	      };
	      this.popup = new main_popup.Popup(popupParam);
	    }
	    return this.popup;
	  }
	  renderFishingWarning() {
	    if (!this.showFishingWarning) {
	      return '';
	    }
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-qr-authorization__popup-warning">
				<div class="ui-icon-set --shield-2-attention"></div>
				${0}
			</div>
		`), main_core.Loc.getMessage('UI_QR_AUTHORIZE_DONT_SHARE_QR_WARNING'));
	  }
	  renderBottom() {
	    var _this$bottomText, _this$bottomText2;
	    if (!this.showBottom) {
	      return '';
	    }
	    const bottomText = main_core.Type.isObject(this.bottomText) ? (_this$bottomText = this.bottomText) == null ? void 0 : _this$bottomText.text : this.bottomText;
	    const bottomTextSize = main_core.Type.isObject(this.bottomText) ? (_this$bottomText2 = this.bottomText) == null ? void 0 : _this$bottomText2.size : '';
	    return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="ui-qr-authorization__popup-bottom">
				<div class="ui-qr-authorization__popup-bottom--title ${0}">
					${0}
				</div>
				${0}
			</div>
		`), bottomTextSize ? '--' + bottomTextSize : '', bottomText, this.renderHelpLink());
	  }
	  renderHelpLink() {
	    if (this.helpCode) {
	      const onclick = e => {
	        e.preventDefault();
	        top.BX.Helper.show(`redirect=detail&code=${this.helpCode}`);
	      };
	      return main_core.Tag.render(_t5 || (_t5 = _`
				<a onclick="${0}" class="ui-qr-authorization__popup-bottom--link">
					${0}
				</a onc>
			`), onclick, main_core.Loc.getMessage('UI_QR_AUTHORIZE_HELP'));
	    }
	    if (this.helpLink) {
	      return main_core.Tag.render(_t6 || (_t6 = _`
				<a href="${0}" class="ui-qr-authorization__popup-bottom--link">
					${0}
				</a>
			`), this.helpLink, main_core.Loc.getMessage('UI_QR_AUTHORIZE_HELP'));
	    }
	    return '';
	  }
	  success() {
	    this.clean();
	    main_core.Dom.addClass(this.getQrNode(), '--success');
	    main_core.Dom.append(this.getSuccessNode(), this.getQrNode());
	  }
	  getSuccessNode() {
	    if (!this.successNode) {
	      this.successNode = main_core.Tag.render(_t7 || (_t7 = _`
				<div class="ui-qr-authorization__popup-qr-success"></div>
			`));
	    }
	    return this.successNode;
	  }
	  loading() {
	    this.clean();
	    main_core.Dom.addClass(this.getQrNode(), '--loading');
	    main_core.Dom.append(this.getLoadingNode(), this.getQrNode());
	    this.showLoader();
	  }
	  getLoadingNode() {
	    if (!this.loadingNode) {
	      this.loadingNode = main_core.Tag.render(_t8 || (_t8 = _`
				<div class="ui-qr-authorization__popup-qr-loading"></div>
			`));
	    }
	    return this.loadingNode;
	  }
	  getLoader() {
	    if (!this.loader) {
	      this.loader = new main_loader.Loader({
	        target: this.getLoadingNode(),
	        size: 150
	      });
	    }
	    return this.loader;
	  }
	  showLoader() {
	    void this.getLoader().show();
	  }
	  hideLoader() {
	    void this.getLoader().hide();
	  }
	  clean() {
	    main_core.Dom.removeClass(this.getQrNode(), ['--loading', '--success']);
	    main_core.Dom.remove(this.getLoadingNode());
	    main_core.Dom.remove(this.getSuccessNode());
	    main_core.Dom.clean(this.getQrNode());
	    this.hideLoader();
	  }
	  show() {
	    if (!this.getPopup().isShown()) {
	      this.loading();
	      this.getPopup().show();
	    }
	  }
	  close() {
	    if (this.getPopup().isShown()) {
	      this.clean();
	      this.getPopup().close();
	    }
	  }
	}

	exports.QrAuthorization = QrAuthorization;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Main,BX,BX));
//# sourceMappingURL=qrauthorization.bundle.js.map
