this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_core_events,calendar_util,main_core) {
	'use strict';

	let _ = t => t,
	    _t,
	    _t2;
	class Checkbox {
	  constructor(options) {
	    this.link = options.link;
	    this.checked = this.link.active;
	    this.create();
	  }

	  create() {
	    this.container = this.createContainer();
	    this.checkbox = this.createCheckbox();
	    main_core.Event.bind(this.checkbox, 'click', this.saveCheckBoxState.bind(this));
	    main_core.Dom.append(this.checkbox, this.container);
	  }

	  createContainer() {
	    return main_core.Tag.render(_t || (_t = _`
			<div class="calendar-sharing-dialog-controls-checkbox-container"></div>
		`));
	  }

	  createCheckbox() {
	    return main_core.Tag.render(_t2 || (_t2 = _`
			<input type="checkbox" ${0}>
		`), this.checked ? 'checked' : '');
	  }

	  saveCheckBoxState() {
	    this.link.active = this.checkbox.checked;
	    BX.ajax.runAction('calendar.api.sharingajax.toggleLink', {
	      data: {
	        userLinkId: this.link.id,
	        isActive: this.link.active
	      }
	    }); // BX.userOptions.save('calendar', 'sharing-dialog-checkbox', this.link, this.checkbox.checked);
	  }

	  getContainer() {
	    return this.container;
	  }

	  renderTo(node) {
	    if (main_core.Type.isDomNode(node)) {
	      return node.appendChild(this.getContainer());
	    }

	    return null;
	  }

	}

	let _$1 = t => t,
	    _t$1,
	    _t2$1,
	    _t3,
	    _t4,
	    _t5,
	    _t6,
	    _t7,
	    _t8,
	    _t9,
	    _t10,
	    _t11,
	    _t12;
	class Dialog {
	  constructor(options = {}) {
	    this.POPUP_WIDTH = 420;
	    this.zIndex = 3100;
	    this.QRCODE_SIZE = 114;
	    this.QRCODE_COLOR_LIGHT = '#fff';
	    this.QRCODE_COLOR_DARK = '#000';
	    this.bindElement = options.bindElement;
	    this.userId = options.userId;
	    this.isSharingOn = options.isSwitchCheckedOnStart;
	    this.switcherNode = options.switcherNode;
	    this.create();
	  }

	  create() {
	    this.popup = new BX.Main.Popup({
	      bindElement: this.bindElement,
	      minHeight: 230,
	      width: this.POPUP_WIDTH,
	      autoHide: true,
	      autoHideHandler: event => this.dialogPopupAutoHideHandler(event),
	      closeByEsc: true,
	      angle: {
	        offset: this.POPUP_WIDTH / 2
	      },
	      offsetLeft: this.bindElement.offsetWidth / 2 - this.POPUP_WIDTH / 2.25,
	      events: {
	        onFirstShow: this.onFirstShow.bind(this),
	        onClose: this.onClose.bind(this)
	      }
	    });
	    this.createLoader().show();
	  }

	  dialogPopupAutoHideHandler(event) {
	    if (this.switcherNode.contains(event.target) || this.popup.getPopupContainer().contains(event.target)) {
	      return false;
	    }

	    return true;
	  }

	  getPopup() {
	    return this.popup;
	  }

	  getLoader() {
	    return this.loader;
	  }

	  isShown() {
	    var _this$popup;

	    return (_this$popup = this.popup) == null ? void 0 : _this$popup.isShown();
	  }

	  destroy() {
	    var _this$popup2;

	    (_this$popup2 = this.popup) == null ? void 0 : _this$popup2.destroy();
	  }

	  createLoader() {
	    this.loader = new BX.Loader({
	      target: this.popup.getContentContainer(),
	      size: 110
	    });
	    return this.loader;
	  }

	  async onFirstShow() {
	    await this.loadDialogData();
	    await this.initQrCode();
	    this.getPopup().setContent(this.createDialogContent());
	    this.onAfterDialogContentCreated();
	    this.getLoader().hide();
	  }

	  onClose() {
	    main_core_events.EventEmitter.emit('Calendar.Sharing.Dialog:onClose');
	  }

	  async loadDialogData() {
	    const response = await BX.ajax.runAction('calendar.api.sharingajax.getDialogData', {
	      data: {
	        isSharingOn: this.isSharingOn
	      }
	    });
	    this.links = response.data.links;
	  }

	  async initQrCode() {
	    await main_core.Runtime.loadExtension(['main.qrcode']);
	  }

	  onAfterDialogContentCreated() {
	    this.subscribeToEvents();
	    main_core.Dom.style(this.copyLinkButtonContainer.firstChild, 'min-width', this.copyLinkButtonContainer.offsetWidth + 1 + 'px');
	  }

	  subscribeToEvents() {
	    main_core_events.EventEmitter.subscribe('Calendar.Sharing.copyLinkButton:onSwitchToggled', event => {
	      var _this$copyLinkButton;

	      (_this$copyLinkButton = this.copyLinkButton) == null ? void 0 : _this$copyLinkButton.setDisabled(!event.data);

	      if (this.previewBlockQr) {
	        main_core.Dom.removeClass(this.previewBlockQr, 'calendar-sharing-dialog-preview-block-qr-container-blurred');
	      }

	      if (this.previewBlockAnnotationLink) {
	        main_core.Dom.removeClass(this.previewBlockAnnotationLink, 'calendar-sharing-dialog-preview-block-annotation-link-disabled');
	      }

	      this.links.forEach(link => {
	        if (link.linkInputNode) {
	          main_core.Dom.removeClass(link.linkContainerNode, 'calendar-sharing-dialog-sharing-block-link-container-disabled');
	          main_core.Dom.removeClass(link.linkInputNode, 'calendar-sharing-dialog-controls-link-text-disabled');
	          main_core.Dom.attr(link.linkInputNode, 'value', link.url);
	          main_core_events.EventEmitter.emit('Calendar.Sharing.LinkTextContainer:onChange');
	        }
	      });
	      BX.ajax.runAction('calendar.api.sharingajax.toggleLink', {
	        data: {
	          userLinkId: this.links[0].id,
	          isActive: this.links[0].active
	        }
	      });
	    });
	  }

	  getDialogContent() {
	    return this.dialogContent;
	  }

	  createDialogContent() {
	    this.dialogContent = this.createContentWrap();
	    main_core.Dom.append(this.createSharingBlock(), this.dialogContent);
	    main_core.Dom.append(this.createPreviewBlock(), this.dialogContent);
	    return this.dialogContent;
	  }

	  createContentWrap() {
	    this.contentWrap = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="calendar-sharing-dialog-wrap"></div>
		`));
	    return this.contentWrap;
	  }

	  createSharingBlock() {
	    this.sharingBlock = this.createBlock();
	    main_core.Dom.append(this.createSharingBlockTitle(), this.sharingBlock);
	    main_core.Dom.append(this.createSharingBlockLinks(), this.sharingBlock);
	    return this.sharingBlock;
	  }

	  createBlock() {
	    return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="calendar-sharing-dialog-block"></div>
		`));
	  }

	  createSharingBlockTitle() {
	    this.sharingBlockTitle = main_core.Tag.render(_t3 || (_t3 = _$1`
			<div class="calendar-sharing-dialog-sharing-block-title">
				<div class="calendar-sharing-dialog-sharing-block-title-text">
					${0}
				</div>
			</div>
		`), main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_TITLE'));
	    main_core.Dom.append(this.createSharingHint(), this.sharingBlockTitle);
	    return this.sharingBlockTitle;
	  }

	  createSharingHint() {
	    return BX.UI.Hint.createNode(main_core.Loc.getMessage('SHARING_DIALOG_SHARING_HINT'));
	  }

	  createSharingBlockLinks() {
	    const references = main_core.Tag.render(_t4 || (_t4 = _$1`
			<div></div>
		`));
	    this.links.forEach(link => {
	      main_core.Dom.append(this.createSharingBlockLink(link), references);
	    });
	    return references;
	  }

	  createSharingBlockLink(link) {
	    const referenceBlock = main_core.Tag.render(_t5 || (_t5 = _$1`
			<div class="calendar-sharing-dialog-sharing-block-link-container"></div>
		`));
	    const linkContainer = main_core.Tag.render(_t6 || (_t6 = _$1`
			<div class="calendar-sharing-dialog-controls-container"></div>
		`));
	    const linkInput = main_core.Tag.render(_t7 || (_t7 = _$1`
			<input
				type="text"
				class="calendar-sharing-dialog-controls-link-text"
				value="${0}"
				readonly
			>
		`), main_core.Text.encode(link.url));

	    if (!this.isSharingOn) {
	      main_core.Dom.attr(linkInput, 'value', link.serverPath + '/...');
	      main_core.Dom.addClass(referenceBlock, 'calendar-sharing-dialog-sharing-block-link-container-disabled');
	      main_core.Dom.addClass(linkInput, 'calendar-sharing-dialog-controls-link-text-disabled');
	      main_core.Dom.style(linkInput, 'width', linkInput.value.length - 3 + 'ch');
	    } else {
	      main_core.Dom.style(linkInput, 'width', linkInput.value.length + 'ch');
	    }

	    main_core_events.EventEmitter.subscribe('Calendar.Sharing.LinkTextContainer:onChange', () => {
	      main_core.Dom.style(linkInput, 'width', linkInput.value.length + 'ch');
	    });
	    link.linkContainerNode = referenceBlock;
	    link.linkInputNode = linkInput;
	    main_core.Dom.append(linkInput, linkContainer);
	    main_core.Dom.append(linkContainer, referenceBlock);
	    this.copyLinkButtonContainer = this.createCopyLinkButtonContainer();
	    main_core.Dom.append(this.copyLinkButtonContainer, referenceBlock);
	    this.copyLinkButton = this.createCopyLinkButton(link.url);
	    this.copyLinkButton.renderTo(this.copyLinkButtonContainer);
	    return referenceBlock;
	  }

	  createCopyLinkButtonContainer() {
	    const copyLinkButtonContainer = main_core.Tag.render(_t8 || (_t8 = _$1`<div></div>`));
	    main_core.Event.bind(copyLinkButtonContainer, 'mouseenter', () => this.handleCopyLinkButtonContainerMouseEnter());
	    main_core.Event.bind(copyLinkButtonContainer, 'mouseleave', () => this.handleCopyLinkButtonContainerMouseLeave());
	    return copyLinkButtonContainer;
	  }

	  handleCopyLinkButtonContainerMouseEnter() {
	    var _this$copyLinkButton2;

	    if ((_this$copyLinkButton2 = this.copyLinkButton) != null && _this$copyLinkButton2.disabled) {
	      main_core_events.EventEmitter.emit('Calendar.Sharing.copyLinkButtonContainer:onMouseEnter');
	      this.showDisabledCopyLinkButtonInfoPopup();
	    }
	  }

	  showDisabledCopyLinkButtonInfoPopup() {
	    var _this$disabledCopyLin;

	    if (!this.disabledCopyLinkButtonPopup) {
	      this.disabledCopyLinkButtonPopup = this.createDisabledCopyLinkButtonInfoPopup();
	    }

	    if (!((_this$disabledCopyLin = this.disabledCopyLinkButtonPopup) != null && _this$disabledCopyLin.isShown())) {
	      this.disabledCopyLinkButtonPopup.show();
	    }
	  }

	  handleCopyLinkButtonContainerMouseLeave() {
	    var _this$copyLinkButton3;

	    if ((_this$copyLinkButton3 = this.copyLinkButton) != null && _this$copyLinkButton3.disabled) {
	      this.hideDisabledCopyLinkButtonInfoPopup();
	    }
	  }

	  hideDisabledCopyLinkButtonInfoPopup() {
	    var _this$disabledCopyLin2;

	    if ((_this$disabledCopyLin2 = this.disabledCopyLinkButtonPopup) != null && _this$disabledCopyLin2.isShown()) {
	      this.disabledCopyLinkButtonPopup.close();
	    }
	  }

	  createDisabledCopyLinkButtonInfoPopup() {
	    const disabledCopyLinkButtonInfoPopupWidth = 200;
	    return new BX.Main.Popup({
	      bindElement: this.copyLinkButtonContainer,
	      className: 'calendar-clipboard-copy',
	      content: main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_DISABLED_COPY_LINK_BUTTON_POPUP'),
	      offsetLeft: this.copyLinkButtonContainer.offsetWidth / 2 - disabledCopyLinkButtonInfoPopupWidth / 2 + 40,
	      width: disabledCopyLinkButtonInfoPopupWidth,
	      darkMode: true,
	      zIndex: 1000,
	      angle: {
	        position: 'top',
	        offset: 90
	      },
	      cacheable: true
	    });
	  }

	  createCopyLinkButton(link) {
	    const copyLinkButton = new BX.UI.Button({
	      text: main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON'),
	      round: true,
	      icon: BX.UI.Button.Icon.COPY,
	      size: BX.UI.Button.Size.EXTRA_SMALL,
	      color: BX.UI.Button.Color.SUCCESS,
	      onclick: button => this.handleCopyLinkButtonClick(button, link)
	    });
	    copyLinkButton.setDisabled(!this.isSharingOn);
	    return copyLinkButton;
	  }

	  handleCopyLinkButtonClick(button, link) {
	    const copyResult = this.copyLink(button, link);

	    if (copyResult) {
	      this.onSuccessfulCopyingLink();
	    }
	  }

	  copyLink(button, link = false) {
	    return !(!link || !BX.clipboard.copy(this.makeLinkText(link)));
	  }

	  makeLinkText(link) {
	    return link;
	  }

	  onSuccessfulCopyingLink() {
	    var _this$copyLinkButton4, _this$copyLinkButton5;

	    calendar_util.Util.showNotification(main_core.Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
	    (_this$copyLinkButton4 = this.copyLinkButton) == null ? void 0 : _this$copyLinkButton4.setText(main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON_COPIED'));
	    (_this$copyLinkButton5 = this.copyLinkButton) == null ? void 0 : _this$copyLinkButton5.setIcon(BX.UI.Button.Icon.DONE);

	    if (this.copyLinkButtonTimeoutId) {
	      clearTimeout(this.copyLinkButtonTimeoutId);
	    }

	    this.copyLinkButtonTimeoutId = setTimeout(() => {
	      var _this$copyLinkButton6, _this$copyLinkButton7;

	      (_this$copyLinkButton6 = this.copyLinkButton) == null ? void 0 : _this$copyLinkButton6.setIcon(BX.UI.Button.Icon.COPY);
	      (_this$copyLinkButton7 = this.copyLinkButton) == null ? void 0 : _this$copyLinkButton7.setText(main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON'));
	    }, 3000);
	  }

	  createPreviewBlock() {
	    this.previewBlock = this.createBlock();
	    main_core.Dom.addClass(this.previewBlock, 'calendar-sharing-dialog-block-preview-section');
	    main_core.Dom.append(this.createPreviewBlockQr(), this.previewBlock);
	    main_core.Dom.append(this.createPreviewBlockAnnotation(), this.previewBlock);
	    return this.previewBlock;
	  }

	  createPreviewBlockQr() {
	    this.previewBlockQr = main_core.Tag.render(_t9 || (_t9 = _$1`
			<div class="calendar-sharing-dialog-preview-block-qr-container"></div>
		`));
	    this.QRCode = new QRCode(this.previewBlockQr, {
	      text: this.links[0].url,
	      width: this.QRCODE_SIZE,
	      height: this.QRCODE_SIZE,
	      colorDark: this.QRCODE_COLOR_DARK,
	      colorLight: this.QRCODE_COLOR_LIGHT,
	      correctLevel: QRCode.CorrectLevel.H
	    });

	    if (!this.isSharingOn) {
	      main_core.Dom.addClass(this.previewBlockQr, 'calendar-sharing-dialog-preview-block-qr-container-blurred');
	    }

	    return this.previewBlockQr;
	  }

	  createPreviewBlockAnnotation() {
	    this.previewBlockAnnotation = main_core.Tag.render(_t10 || (_t10 = _$1`
			<div class="calendar-sharing-dialog-preview-block-annotation"></div>
		`));
	    main_core.Dom.append(this.createPreviewBlockAnnotationItem(), this.previewBlockAnnotation);
	    return this.previewBlockAnnotation;
	  }

	  createPreviewBlockAnnotationItem() {
	    const annotation = main_core.Tag.render(_t11 || (_t11 = _$1`
			<div class="calendar-sharing-dialog-preview-block-annotation-item"></div>
		`));
	    const linkPhrase = '<a class="calendar-sharing-dialog-preview-block-annotation-link">' + main_core.Loc.getMessage('SHARING_DIALOG_PREVIEW_BLOCK_CONTENT_LINK') + '</a>';
	    const blockContent = main_core.Tag.render(_t12 || (_t12 = _$1`
			<span>${0}</span>
		`), main_core.Loc.getMessage('SHARING_DIALOG_PREVIEW_BLOCK_CONTENT', {
	      '#LINK#': linkPhrase
	    }));
	    main_core.Dom.append(blockContent, annotation);
	    this.previewBlockAnnotationLink = annotation.querySelector('.calendar-sharing-dialog-preview-block-annotation-link');

	    if (this.previewBlockAnnotationLink) {
	      if (!this.isSharingOn) {
	        main_core.Dom.addClass(this.previewBlockAnnotationLink, 'calendar-sharing-dialog-preview-block-annotation-link-disabled');
	      }

	      main_core.Event.bind(this.previewBlockAnnotationLink, 'click', () => {
	        this.openNewTab();
	      });
	    }

	    return annotation;
	  }

	  enableLinks() {
	    var _this$links;

	    (_this$links = this.links) == null ? void 0 : _this$links.forEach(link => {
	      link.active = true;
	    });
	  }

	  openNewTab() {
	    window.open(main_core.Text.encode(this.links[0].url), '_blank');
	  }

	  toggle() {
	    this.popup.toggle();
	  }

	}

	let _$2 = t => t,
	    _t$2,
	    _t2$2,
	    _t3$1,
	    _t4$1,
	    _t5$1,
	    _t6$1,
	    _t7$1;
	class SharingButton {
	  constructor(options = {}) {
	    this.HELP_DESK_CODE = 17198666;
	    this.wrap = options.wrap;
	    this.userId = options.userId;
	    this.subscribeToEvents();
	  }

	  subscribeToEvents() {
	    main_core_events.EventEmitter.subscribe('Calendar.Sharing.copyLinkButtonContainer:onMouseEnter', () => this.handleCopyLinkButtonContainerMouseEnter());
	    main_core_events.EventEmitter.subscribe('Calendar.Sharing.Dialog:onClose', () => this.handleSharingDialogClose());
	  }

	  show() {
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
	      },
	      events: {
	        'mouseenter': () => this.handleSharingButtonMouseEnter(),
	        'mouseleave': () => this.handleSharingButtonMouseLeave()
	      }
	    });
	    this.button.renderTo(this.wrap);
	    this.renderSwitcher();
	  }

	  handleCopyLinkButtonContainerMouseEnter() {
	    var _this$switcher;

	    if (!((_this$switcher = this.switcher) != null && _this$switcher.disabled) && !this.switcherSpotlight) {
	      this.showSwitcherSpotlight();
	    }
	  }

	  handleSharingDialogClose() {
	    this.hideSwitcherSpotlight();
	  }

	  showSwitcherSpotlight() {
	    this.switcherSpotlight = new BX.SpotLight({
	      targetElement: this.switcherWrap,
	      targetVertex: 'middle-center',
	      left: -17,
	      top: -5,
	      lightMode: true,
	      events: {
	        'onTargetEnter': () => {
	          this.hideSwitcherSpotlight();
	        }
	      }
	    });
	    this.switcherSpotlight.show();
	  }

	  hideSwitcherSpotlight() {
	    if (this.switcherSpotlight) {
	      this.switcherSpotlight.close();
	      this.switcherSpotlight = null;
	    }
	  }

	  handleSharingButtonClick() {
	    this.clearInfoPopupShowTimeOut();
	    this.getSharingDialog().toggle();
	  }

	  handleSharingButtonMouseEnter() {
	    var _this$sharingDialog;

	    if (!((_this$sharingDialog = this.sharingDialog) != null && _this$sharingDialog.isShown()) && !this.switcher.isChecked()) {
	      this.infoPopupShowTimeout = setTimeout(() => this.showInfoPopup(), 1000);
	    }
	  }

	  handleSharingButtonMouseLeave() {
	    this.clearInfoPopupShowTimeOut();
	  }

	  clearInfoPopupShowTimeOut() {
	    if (this.infoPopupShowTimeout) {
	      clearTimeout(this.infoPopupShowTimeout);
	      this.infoPopupShowTimeout = null;
	    }
	  }

	  showInfoPopup() {
	    var _this$sharingDialog2;

	    if ((_this$sharingDialog2 = this.sharingDialog) != null && _this$sharingDialog2.isShown()) {
	      return;
	    }

	    if (this.infoPopup) {
	      this.infoPopup.destroy();
	    }

	    const infoPopupWidth = 320;
	    this.infoPopup = new BX.Main.Popup({
	      bindElement: this.button.getContainer(),
	      width: infoPopupWidth,
	      padding: 15,
	      autoHide: true,
	      closeByEsc: true,
	      closeIcon: true,
	      content: this.getInfoPopupContent(),
	      angle: {
	        offset: infoPopupWidth / 2
	      },
	      offsetLeft: this.button.getContainer().offsetWidth / 2 - infoPopupWidth / 2.5
	    });
	    this.infoPopup.show();
	  }

	  getInfoPopupContent() {
	    const content = main_core.Tag.render(_t$2 || (_t$2 = _$2`<div></div>`));
	    const mainContent1 = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="calendar-sharing__info-popup_main-content">
				${0}
			</div>
		`), main_core.Loc.getMessage('SHARING_INFO_POPUP_CONTENT_1'));
	    main_core.Dom.append(mainContent1, content);
	    const mainContent2 = main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<div class="calendar-sharing__info-popup_main-content">
				${0}
			</div>
		`), main_core.Loc.getMessage('SHARING_INFO_POPUP_CONTENT_2'));
	    main_core.Dom.append(mainContent2, content);
	    const detailLink = main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
			<a class="calendar-sharing__info-popup_detail-link">
				${0}
			</a>
		`), main_core.Loc.getMessage('SHARING_DIALOG_MORE_DETAILED'));
	    main_core.Event.bind(detailLink, 'click', () => this.handleDetailLinkClick());
	    main_core.Dom.append(detailLink, content);
	    return content;
	  }

	  handleDetailLinkClick() {
	    this.openHelpDesk();
	  }

	  openHelpDesk() {
	    top.BX.Helper.show('redirect=detail&code=' + this.HELP_DESK_CODE);
	  }

	  getSharingDialog() {
	    if (!this.sharingDialog) {
	      this.sharingDialog = new Dialog({
	        bindElement: this.button.getContainer(),
	        userId: this.userId,
	        isSwitchCheckedOnStart: this.switcher.isChecked(),
	        switcherNode: this.switcher.getNode()
	      });
	    }

	    return this.sharingDialog;
	  }

	  getSwitcherContainer() {
	    const switcherContainer = main_core.Tag.render(_t5$1 || (_t5$1 = _$2`
			<div class="calendar-sharing__switcher">
				
			</div>
		`));
	    return switcherContainer;
	  }

	  getSwitcherDivider() {
	    const switcherDivider = main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
			<div class="calendar-sharing__switcher_divider"></div>
		`));
	    return switcherDivider;
	  }

	  renderSwitcher() {
	    var _Util$getSharingConfi;

	    main_core.Dom.append(this.getSwitcherDivider(), this.button.button);
	    this.switcherWrap = main_core.Tag.render(_t7$1 || (_t7$1 = _$2`<div class="calendar-sharing__switcher-wrap"></div>`));
	    main_core.Dom.append(this.switcherWrap, this.button.button);
	    main_core.Event.bind(this.switcherWrap, 'click', this.handleSwitcherWrapClick.bind(this), {
	      capture: true
	    });
	    this.switcher = new BX.UI.Switcher({
	      node: this.getSwitcherContainer(),
	      checked: ((_Util$getSharingConfi = calendar_util.Util.getSharingConfig()) == null ? void 0 : _Util$getSharingConfi.isEnabled) === 'true',
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

	  handleSwitcherToggled() {
	    if (this.switcher.isChecked()) {
	      const sharingDialog = this.getSharingDialog();

	      if (!sharingDialog.isShown()) {
	        sharingDialog.toggle();
	      }

	      sharingDialog.enableLinks();
	      main_core_events.EventEmitter.emit('Calendar.Sharing.copyLinkButton:onSwitchToggled', this.switcher.isChecked());
	    } else {
	      BX.ajax.runAction('calendar.api.sharingajax.deleteUserLinks');
	      this.getSharingDialog().destroy();
	      this.sharingDialog = null;
	      this.warningPopup.close();
	    }

	    BX.userOptions.save('calendar', 'sharing', 'isEnabled', this.switcher.isChecked());
	  }

	}

	class Interface {
	  constructor(options) {
	    this.buttonWrap = options.buttonWrap;
	    this.userId = options.userId;
	  }

	  showSharingButton() {
	    this.sharingButton = new SharingButton({
	      wrap: this.buttonWrap,
	      userId: this.userId
	    });
	    this.sharingButton.show();
	  }

	}

	exports.Interface = Interface;
	exports.SharingButton = SharingButton;
	exports.Checkbox = Checkbox;
	exports.Dialog = Dialog;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Event,BX.Calendar,BX));
//# sourceMappingURL=interface.bundle.js.map
