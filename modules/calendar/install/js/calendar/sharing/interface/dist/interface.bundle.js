this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_core_events,main_core,main_popup,main_loader,main_qrcode,ui_designTokens,calendar_util,ui_switcher,spotlight,ui_tour,ui_cnt) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _loader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loader");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _qrCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("qrCode");
	var _context = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	class DialogQr {
	  constructor(options) {
	    this.QRCODE_SIZE = 114;
	    this.QRCODE_COLOR_LIGHT = '#fff';
	    this.QRCODE_COLOR_DARK = '#000';
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _qrCode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _context, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {
	      qr: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _qrCode)[_qrCode] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context] = options.context;
	    this.sharingUrl = options.sharingUrl;
	  }

	  /**
	   *
	   * @returns {Popup}
	   */
	  getPopup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	        className: 'calendar-sharing__qr',
	        width: 315,
	        padding: 0,
	        content: this.getContent(),
	        closeIcon: true,
	        closeByEsc: true,
	        autoHide: true,
	        overlay: true,
	        animation: 'fading-slide'
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }

	  /**
	   *
	   * @returns {Loader}
	   */
	  getLoader() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = new main_loader.Loader({
	        size: 95
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader];
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getNodeQr() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr = main_core.Tag.render(_t || (_t = _`
				<div class="calendar-sharing__qr-block"></div>
			`));

	      // qr emulation
	      this.getLoader().show(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr);
	      this.showQr();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr;
	  }
	  async showQr() {
	    await this.initQrCode();
	    this.QRCode = new QRCode(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr, {
	      text: this.sharingUrl,
	      width: this.QRCODE_SIZE,
	      height: this.QRCODE_SIZE,
	      colorDark: this.QRCODE_COLOR_DARK,
	      colorLight: this.QRCODE_COLOR_LIGHT,
	      correctLevel: QRCode.CorrectLevel.H
	    });
	    await this.getLoader().hide();
	  }
	  async initQrCode() {
	    await main_core.Runtime.loadExtension(['main.qrcode']);
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getContent() {
	    return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="calendar-sharing__qr-content">
				<div class="calendar-sharing__qr-title">${0}</div>
				${0}
				<div class="calendar-sharing__qr-info">${0}</div>
				<a class="calendar-sharing__dialog-link" href="${0}" target="_blank">${0}</a>
			</div>
		`), this.getPhraseDependsOnContext('SHARING_INFO_POPUP_QR_TITLE'), this.getNodeQr(), main_core.Loc.getMessage('SHARING_INFO_POPUP_QR_INFO'), this.sharingUrl, main_core.Loc.getMessage('SHARING_INFO_POPUP_QR_OPEN_LINK'));
	  }
	  isShown() {
	    return this.getPopup().isShown();
	  }
	  close() {
	    this.getPopup().close();
	  }
	  show() {
	    this.getPopup().show();
	  }
	  destroy() {
	    this.getPopup().destroy();
	  }
	  getPhraseDependsOnContext(code) {
	    return main_core.Loc.getMessage(code + '_' + babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].toUpperCase());
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3,
	  _t4;
	var _popup$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _layout$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _dialogQr = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogQr");
	var _context$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	class DialogNew {
	  constructor(options) {
	    this.HELP_DESK_CODE_CALENDAR = 17198666;
	    this.HELP_DESK_CODE_CRM = 17502612;
	    Object.defineProperty(this, _popup$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogQr, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _context$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1] = {
	      wrapper: null,
	      contentTop: null,
	      contentBody: null,
	      contentBottom: null,
	      buttonCopy: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] = options.context;
	    this.bindElement = options.bindElement || null;
	    this.sharingUrl = options.sharingUrl || null;
	  }

	  /**
	   *
	   * @returns {Popup}
	   */
	  getPopup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1] = new main_popup.Popup({
	        bindElement: this.bindElement,
	        className: 'calendar-sharing__dialog',
	        closeByEsc: true,
	        autoHide: true,
	        padding: 0,
	        width: 470,
	        angle: {
	          offset: this.bindElement.offsetWidth / 2 + 16
	        },
	        autoHideHandler: event => {
	          return this.autoHideHandler(event);
	        },
	        content: this.getPopupWrapper(),
	        animation: 'fading-slide',
	        events: {
	          onPopupShow: () => this.bindElement.classList.add('ui-btn-hover'),
	          onPopupClose: () => this.bindElement.classList.remove('ui-btn-hover')
	        }
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1];
	  }
	  autoHideHandler(event) {
	    var _babelHelpers$classPr;
	    return !babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper.contains(event.target) && !((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr]) != null && _babelHelpers$classPr.isShown());
	  }

	  /**
	   *
	   * @returns {DialogQr}
	   */
	  getDialogQr() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr] = new DialogQr({
	        sharingUrl: this.sharingUrl,
	        context: babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1]
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr];
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupWrapper() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="calendar-sharing__dialog-wrapper">
					${0}
					<div class="calendar-sharing__dialog-body">
						<div class="calendar-sharing__dialog-userpic"></div>
						<div class="calendar-sharing__dialog-notify">
							<div class="calendar-sharing__dialog-notify_content">
								${0}
							</div>
						</div>
					</div>
					${0}
				</div>
			`), this.getPopupContentTop(), main_core.Loc.getMessage('SHARING_INFO_POPUP_CONTENT_4', {
	        '#LINK#': this.sharingUrl
	      }), this.getPopupContentBottom());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper;
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupCopyLinkButton() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].buttonCopy) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].buttonCopy = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<span onclick="${0}" class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps">${0}</span>
			`), this.adjustSave.bind(this), main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON'));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].buttonCopy;
	  }
	  adjustSave() {
	    if (this.copyLink()) {
	      this.onSuccessfulCopyingLink();
	    }
	  }
	  copyLink() {
	    let result = false;
	    if (this.sharingUrl) {
	      result = BX.clipboard.copy(this.sharingUrl);
	    }
	    if (result) {
	      calendar_util.Util.showNotification(main_core.Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
	      main_core_events.EventEmitter.emit('CalendarSharing:LinkCopied');
	    }
	    return result;
	  }
	  onSuccessfulCopyingLink() {
	    this.getPopup().close();
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupContentBottom() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].contentBottom) {
	      const adjustClick = () => {
	        this.getDialogQr().show();
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].contentBottom = main_core.Tag.render(_t3 || (_t3 = _$1`
				<div class="calendar-sharing__dialog-bottom">
					${0}
					<span onclick="${0}" class="calendar-sharing__dialog-link">${0}</span>
				</div>
			`), this.getPopupCopyLinkButton(), adjustClick, main_core.Loc.getMessage('SHARING_INFO_POPUP_WHAT_SEE_USERS'));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].contentBottom;
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupContentTop() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].contentTop) {
	      const openHelpDesk = () => {
	        top.BX.Helper.show('redirect=detail&code=' + this.getHelpDeskCodeDependsOnContext());
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].contentTop = main_core.Tag.render(_t4 || (_t4 = _$1`
				<div class="calendar-sharing__dialog-top">
					<div class="calendar-sharing__dialog-title">
						<span>${0}</span>
						<span onclick="${0}" class="calendar-sharing__dialog-title-help"  title="${0}"></span>
					</div>
					<div class="calendar-sharing__dialog-info">${0}</div>
				</div>
			`), main_core.Loc.getMessage('SHARING_BUTTON_TITLE'), openHelpDesk, main_core.Loc.getMessage('SHARING_INFO_POPUP_HOW_IT_WORK'), this.getPhraseDependsOnContext('SHARING_INFO_POPUP_CONTENT_3') + ' ');
	      const infoNotify = babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].contentTop.querySelector('[ data-role="calendar-sharing_popup-open-link"]');
	      if (infoNotify) {
	        let infoNotifyHint;
	        let timer;
	        infoNotify.addEventListener('mouseenter', () => {
	          timer = setTimeout(() => {
	            if (!infoNotifyHint) {
	              infoNotifyHint = new main_popup.Popup({
	                bindElement: infoNotify,
	                angle: {
	                  offset: infoNotify.offsetWidth / 2 + 16
	                },
	                width: 410,
	                darkMode: true,
	                content: main_core.Loc.getMessage('SHARING_INFO_POPUP_SLOT_DESC'),
	                animation: 'fading-slide'
	              });
	            }
	            infoNotifyHint.show();
	          }, 1000);
	        });
	        infoNotify.addEventListener('mouseleave', () => {
	          clearTimeout(timer);
	          if (infoNotifyHint) {
	            infoNotifyHint.close();
	          }
	        });
	      }
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].contentTop;
	  }
	  isShown() {
	    return this.getPopup().isShown();
	  }
	  show() {
	    if (!this.bindElement) {
	      console.warn('BX.Calendar.Sharing: "bindElement" is not defined');
	      return;
	    }
	    this.getPopup().show();
	  }
	  destroy() {
	    this.getPopup().destroy();
	    this.getDialogQr().destroy();
	  }
	  getPhraseDependsOnContext(code) {
	    return main_core.Loc.getMessage(code + '_' + babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1].toUpperCase());
	  }
	  getHelpDeskCodeDependsOnContext() {
	    let code = 0;
	    switch (babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1]) {
	      case 'calendar':
	        {
	          code = this.HELP_DESK_CODE_CALENDAR;
	          break;
	        }
	      case 'crm':
	        {
	          code = this.HELP_DESK_CODE_CRM;
	          break;
	        }
	    }
	    return code;
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$1;
	class SharingButton {
	  constructor(options = {}) {
	    var _this$sharingConfig;
	    this.PAY_ATTENTION_TO_NEW_FEATURE_DELAY = 1000;
	    this.PAY_ATTENTION_TO_NEW_FEATURE_FIRST = 'first-feature';
	    this.PAY_ATTENTION_TO_NEW_FEATURE_NEW = 'new-feature';
	    this.PAY_ATTENTION_TO_NEW_FEATURE_REMIND = 'remind-feature';
	    this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS = [this.PAY_ATTENTION_TO_NEW_FEATURE_FIRST];
	    this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS = [this.PAY_ATTENTION_TO_NEW_FEATURE_NEW, this.PAY_ATTENTION_TO_NEW_FEATURE_REMIND];
	    this.AVAILABLE_PAY_ATTENTION_TO_NEW_FEATURE_MODS = [...this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS, ...this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS];
	    this.wrap = options.wrap;
	    this.userId = options.userId;
	    this.sharingConfig = calendar_util.Util.getSharingConfig();
	    this.sharingUrl = ((_this$sharingConfig = this.sharingConfig) == null ? void 0 : _this$sharingConfig.url) || null;
	    this.payAttentionToNewFeatureMode = options.payAttentionToNewFeature;
	  }
	  show() {
	    main_core.Dom.addClass(this.wrap, 'calendar-sharing__btn-wrap');
	    this.button = new BX.UI.Button({
	      text: main_core.Loc.getMessage('SHARING_BUTTON_TITLE'),
	      round: true,
	      size: BX.UI.Button.Size.EXTRA_SMALL,
	      color: BX.UI.Button.Color.LIGHT_BORDER,
	      className: 'ui-btn-themes calendar-sharing__btn',
	      onclick: (button, event) => {
	        if (!this.switcher.getNode().contains(event.target)) {
	          this.handleSharingButtonClick();
	        }
	      }
	    });
	    this.button.renderTo(this.wrap);
	    this.renderSwitcher();
	    if (this.AVAILABLE_PAY_ATTENTION_TO_NEW_FEATURE_MODS.includes(this.payAttentionToNewFeatureMode)) {
	      this.payAttentionToNewFeature(this.payAttentionToNewFeatureMode);
	      BX.ajax.runAction('calendar.api.sharingajax.disableOptionPayAttentionToNewSharingFeature');
	    }
	  }
	  handleSharingButtonClick() {
	    if (!this.isSharingEnabled()) {
	      this.switcher.toggle();
	    } else {
	      this.openDialog();
	    }
	  }
	  getSwitcherContainer() {
	    const switcherContainer = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="calendar-sharing__switcher">
				
			</div>
		`));
	    return switcherContainer;
	  }
	  getSwitcherDivider() {
	    const switcherDivider = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="calendar-sharing__switcher_divider"></div>
		`));
	    return switcherDivider;
	  }
	  renderSwitcher() {
	    main_core.Dom.append(this.getSwitcherDivider(), this.wrap);
	    this.switcherWrap = main_core.Tag.render(_t3$1 || (_t3$1 = _$2`<div class="calendar-sharing__switcher-wrap"></div>`));
	    main_core.Dom.append(this.switcherWrap, this.wrap);
	    main_core.Event.bind(this.switcherWrap, 'click', this.handleSwitcherWrapClick.bind(this), {
	      capture: true
	    });
	    this.switcher = new BX.UI.Switcher({
	      node: this.getSwitcherContainer(),
	      checked: this.isSharingEnabled(),
	      color: 'green',
	      size: 'small',
	      handlers: {
	        toggled: () => this.handleSwitcherToggled()
	      }
	    });
	    this.switcher.renderTo(this.switcherWrap);
	  }
	  handleSwitcherWrapClick(event) {
	    if (this.switcher.isChecked()) {
	      this.showWarningPopup();
	      event.stopPropagation();
	    }
	  }
	  handleSwitcherToggled() {
	    if (this.isToggledAfterErrorOccurred()) {
	      return;
	    }
	    if (this.switcher.isChecked()) {
	      this.enableSharing();
	    } else {
	      this.disableSharing();
	    }
	  }
	  isToggledAfterErrorOccurred() {
	    return this.switcher.isChecked() === this.isSharingEnabled();
	  }
	  isSharingEnabled() {
	    return main_core.Type.isString(this.sharingUrl);
	  }
	  enableSharing() {
	    const action = 'calendar.api.sharingajax.enableUserSharing';
	    const event = 'Calendar.Sharing.copyLinkButton:onSharingEnabled';
	    BX.ajax.runAction(action).then(response => {
	      this.sharingUrl = response.data.url;
	      this.openDialog();
	      main_core_events.EventEmitter.emit(event, {
	        'isChecked': this.switcher.isChecked(),
	        'url': response.data.url
	      });
	    }).catch(() => {
	      this.switcher.toggle();
	    });
	  }
	  openDialog() {
	    var _this$pulsar;
	    (_this$pulsar = this.pulsar) == null ? void 0 : _this$pulsar.close();
	    main_core.Dom.remove(this.counterNode);
	    if (!this.newDialog) {
	      this.newDialog = new DialogNew({
	        bindElement: this.button.getContainer(),
	        sharingUrl: this.sharingUrl,
	        context: "calendar"
	      });
	    }
	    if (!this.newDialog.isShown()) {
	      this.newDialog.show();
	      this.newDialog.copyLink();
	    }
	  }
	  disableSharing() {
	    const action = 'calendar.api.sharingajax.disableUserSharing';
	    const event = 'Calendar.Sharing.copyLinkButton:onSharingDisabled';
	    this.warningPopup.close();
	    BX.ajax.runAction(action).then(() => {
	      this.sharingUrl = null;
	      if (this.newDialog) {
	        this.newDialog.destroy();
	        this.newDialog = null;
	      }
	      main_core_events.EventEmitter.emit(event, {
	        'isChecked': this.switcher.isChecked()
	      });
	    }).catch(() => {
	      this.switcher.toggle();
	    });
	  }
	  showWarningPopup() {
	    if (!this.warningPopup) {
	      this.warningPopup = new BX.UI.Dialogs.MessageBox({
	        title: main_core.Loc.getMessage('SHARING_WARNING_POPUP_TITLE'),
	        message: main_core.Loc.getMessage('SHARING_WARNING_POPUP_CONTENT'),
	        buttons: this.getWarningPopupButtons(),
	        popupOptions: {
	          autoHide: true,
	          closeByEsc: true,
	          draggable: true,
	          closeIcon: true,
	          minWidth: 365,
	          maxWidth: 365
	        }
	      });
	    }
	    this.warningPopup.show();
	  }
	  getWarningPopupButtons() {
	    return [this.getSubmitButton(), this.getCancelButton()];
	  }
	  getSubmitButton() {
	    return new BX.UI.Button({
	      size: BX.UI.Button.Size.MEDIUM,
	      color: BX.UI.Button.Color.DANGER,
	      text: main_core.Loc.getMessage('SHARING_WARNING_POPUP_SUBMIT_BUTTON'),
	      events: {
	        click: () => this.handleSubmitButtonClick()
	      }
	    });
	  }
	  getCancelButton() {
	    return new BX.UI.Button({
	      size: BX.UI.Button.Size.MEDIUM,
	      color: BX.UI.Button.Color.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('SHARING_WARNING_POPUP_CANCEL_BUTTON'),
	      events: {
	        click: () => this.handleCancelButtonClick()
	      }
	    });
	  }
	  handleSubmitButtonClick() {
	    this.switcher.toggle();
	    this.warningPopup.close();
	  }
	  handleCancelButtonClick() {
	    this.warningPopup.close();
	  }
	  payAttentionToNewFeature(mode) {
	    if (this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS.includes(mode)) {
	      this.payAttentionToNewFeatureWithoutText();
	    }
	    if (this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS.includes(mode)) {
	      this.payAttentionToNewFeatureWithText(mode);
	    }
	  }
	  payAttentionToNewFeatureWithoutText() {
	    this.pulsar = this.getPulsar(this.wrap, false);
	    this.pulsar.show();
	    main_core.Event.bind(this.pulsar.container, 'click', () => {
	      this.handleSharingButtonClick();
	    });
	    this.counterNode = new ui_cnt.Counter({
	      value: 1,
	      color: ui_cnt.Counter.Color.DANGER,
	      size: ui_cnt.Counter.Size.MEDIUM,
	      animation: false
	    }).getContainer();
	    main_core.Dom.addClass(this.counterNode, 'calendar-sharing__new-feature-counter');
	    main_core.Dom.append(this.counterNode, this.wrap);
	  }
	  payAttentionToNewFeatureWithText(mode) {
	    let title = main_core.Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_TITLE');
	    let text = main_core.Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_TEXT');
	    if (mode === this.PAY_ATTENTION_TO_NEW_FEATURE_REMIND) {
	      title = main_core.Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_NOTIFY_TITLE');
	      text = main_core.Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_NOTIFY_TEXT');
	    }
	    const guide = this.getGuide(title, text);
	    const pulsar = this.getPulsar(this.wrap);
	    setTimeout(() => {
	      guide.showNextStep();
	      guide.getPopup().setAngle({
	        offset: 210
	      });
	      pulsar.show();
	    }, this.PAY_ATTENTION_TO_NEW_FEATURE_DELAY);
	  }
	  getGuide(title, text) {
	    const guide = new ui_tour.Guide({
	      simpleMode: true,
	      onEvents: true,
	      steps: [{
	        target: this.wrap,
	        title: title,
	        text: text,
	        position: 'bottom',
	        condition: {
	          top: true,
	          bottom: false,
	          color: 'primary'
	        }
	      }]
	    });
	    const guidePopup = guide.getPopup();
	    main_core.Dom.addClass(guidePopup.popupContainer, 'calendar-popup-ui-tour-animate');
	    guidePopup.setWidth(400);
	    guidePopup.getContentContainer().style.paddingRight = getComputedStyle(guidePopup.closeIcon)['width'];
	    return guide;
	  }
	  getPulsar(target, hideOnHover = true) {
	    const pulsar = new BX.SpotLight({
	      targetElement: target,
	      targetVertex: 'middle-center',
	      lightMode: true
	    });
	    if (hideOnHover) {
	      pulsar.bindEvents({
	        'onTargetEnter': () => pulsar.close()
	      });
	    }
	    return pulsar;
	  }
	}

	class Interface {
	  constructor(options) {
	    var _options$payAttention;
	    this.buttonWrap = options.buttonWrap;
	    this.userId = options.userId;
	    this.payAttentionToNewFeature = (_options$payAttention = options.payAttentionToNewFeature) != null ? _options$payAttention : false;
	  }
	  showSharingButton() {
	    this.sharingButton = new SharingButton({
	      wrap: this.buttonWrap,
	      userId: this.userId,
	      payAttentionToNewFeature: this.payAttentionToNewFeature
	    });
	    this.sharingButton.show();
	  }
	}

	exports.Interface = Interface;
	exports.SharingButton = SharingButton;
	exports.DialogNew = DialogNew;
	exports.DialogQr = DialogQr;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Event,BX,BX.Main,BX,BX,BX,BX.Calendar,BX,BX,BX.UI.Tour,BX.UI));
//# sourceMappingURL=interface.bundle.js.map
