/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_popup,main_loader,pull_client) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	class QrAuthorization {
	  constructor(options = {}) {
	    this.title = options.title || null;
	    this.content = options.content || null;
	    this.bottomText = options.bottomText || main_core.Loc.getMessage('UI_QR_AUTHORIZE_TAKE_CODE');
	    this.helpLink = options.helpLink || null;
	    this.qr = options.qr || null;
	    this.popupParam = options.popupParam || null;
	    this.popup = null;
	    this.loader = null;
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
	        intent: 'calendar'
	      }
	    }).then(response => {
	      var _response$data;
	      let link = (_response$data = response.data) == null ? void 0 : _response$data.link;
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
	      var _this$title, _this$title2, _this$bottomText, _this$bottomText2, _this$popupParam, _this$popupParam2, _this$popupParam3, _this$popupParam4, _this$popupParam5, _this$popupParam6, _this$popupParam7, _this$popupParam8, _this$popupParam9, _this$popupParam10;
	      const title = main_core.Type.isObject(this.title) ? (_this$title = this.title) == null ? void 0 : _this$title.text : this.title;
	      const titleSize = main_core.Type.isObject(this.title) ? (_this$title2 = this.title) == null ? void 0 : _this$title2.size : '';
	      const bottomText = main_core.Type.isObject(this.bottomText) ? (_this$bottomText = this.bottomText) == null ? void 0 : _this$bottomText.text : this.bottomText;
	      const bottomTextSize = main_core.Type.isObject(this.bottomText) ? (_this$bottomText2 = this.bottomText) == null ? void 0 : _this$bottomText2.size : '';
	      let container = `
				<div class="ui-qr-authorization__popup-wrapper">
					<div class="ui-qr-authorization__popup-top ${!this.content ? '--direction-column' : ''}">
						<div class="ui-qr-authorization__popup-left ${!title ? '--flex' : ''}"">
							${title ? `<div class="ui-qr-authorization__popup-title --${titleSize}">${title}</div>` : ''}
							${this.content ? `<div class="ui-qr-authorization__popup-text">${this.content}</div>` : ''}
						</div>
						<div class="ui-qr-authorization__popup-right ${!this.title ? '--no-margin' : ''}" data-role="ui-qr-authorization__qr-node"></div>
					</div>
					<div class="ui-qr-authorization__popup-bottom">
						<div class="ui-qr-authorization__popup-bottom--title ${bottomTextSize ? '--' + bottomTextSize : ''}">${bottomText}</div>
						${this.helpLink ? `<a href="${this.helpLink}" class="ui-qr-authorization__popup-bottom--link">${main_core.Loc.getMessage('UI_QR_AUTHORIZE_HELP')}</a>` : ''}
					</div>
				</div>
			`;
	      let popupWidth = this.content ? 710 : 405;
	      let popupParam = {
	        className: (_this$popupParam = this.popupParam) != null && _this$popupParam.className ? (_this$popupParam2 = this.popupParam) == null ? void 0 : _this$popupParam2.className : 'ui-qr-authorization__popup ui-qr-authorization__popup-scope',
	        width: (_this$popupParam3 = this.popupParam) != null && _this$popupParam3.width ? (_this$popupParam4 = this.popupParam) == null ? void 0 : _this$popupParam4.width : popupWidth,
	        content: container,
	        closeByEsc: (_this$popupParam5 = this.popupParam) != null && _this$popupParam5.closeByEsc ? (_this$popupParam6 = this.popupParam) == null ? void 0 : _this$popupParam6.className : true,
	        overlay: (_this$popupParam7 = this.popupParam) != null && _this$popupParam7.overlay ? (_this$popupParam8 = this.popupParam) == null ? void 0 : _this$popupParam8.overlay : false,
	        autoHide: (_this$popupParam9 = this.popupParam) != null && _this$popupParam9.autoHide ? (_this$popupParam10 = this.popupParam) == null ? void 0 : _this$popupParam10.autoHide : true,
	        closeIcon: {
	          top: '14px',
	          right: '15px'
	        },
	        events: {
	          onPopupShow: () => {
	            this.createQrCodeImage();
	            const qrTarget = this.getPopup().getContentContainer().querySelector('[data-role="ui-qr-authorization__qr-node"]');
	            if (qrTarget) {
	              qrTarget.appendChild(this.getQrNode());
	            }
	          }
	        },
	        padding: 0,
	        animation: 'fading-slide'
	      };
	      this.popup = new main_popup.Popup(popupParam);
	    }
	    return this.popup;
	  }
	  success() {
	    this.clean();
	    this.getQrNode().classList.add('--success');
	    this.getQrNode().appendChild(this.getSuccessNode());
	  }
	  getSuccessNode() {
	    if (!this.successNode) {
	      this.successNode = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-qr-authorization__popup-qr-success"></div>
			`));
	    }
	    return this.successNode;
	  }
	  loading() {
	    this.clean();
	    this.getQrNode().classList.add('--loading');
	    this.getQrNode().appendChild(this.getLoadingNode());
	    this.showLoader();
	  }
	  getLoadingNode() {
	    if (!this.loadingNode) {
	      this.loadingNode = main_core.Tag.render(_t3 || (_t3 = _`
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
	    this.getQrNode().classList.remove('--loading');
	    this.getQrNode().classList.remove('--success');
	    main_core.Dom.remove(this.getLoadingNode());
	    main_core.Dom.remove(this.getSuccessNode());
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
