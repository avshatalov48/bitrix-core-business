/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_popup) {
	'use strict';

	/**
	 * @namespace BX.UI.Dialogs
	 */
	class MessageBoxButtons {}
	MessageBoxButtons.NONE = "none";
	MessageBoxButtons.OK = "ok";
	MessageBoxButtons.CANCEL = "cancel";
	MessageBoxButtons.YES = "yes";
	MessageBoxButtons.NO = "no";
	MessageBoxButtons.OK_CANCEL = "ok_cancel";
	MessageBoxButtons.YES_NO = "yes_no";
	MessageBoxButtons.YES_CANCEL = "yes_cancel";
	MessageBoxButtons.YES_NO_CANCEL = "yes_no_cancel";

	/**
	 * @namespace {BX.UI.Dialogs}
	 */
	class MessageBox {
	  /** @var {Popup} */

	  constructor(options = {}) {
	    this.popupWindow = null;
	    this.title = null;
	    this.message = null;
	    this.modal = true;
	    this.popupOptions = {};
	    this.minWidth = 300;
	    this.minHeight = 130;
	    this.maxWidth = 400;
	    this.buttons = [];
	    this.okCallback = null;
	    this.cancelCallback = null;
	    this.yesCallback = null;
	    this.noCallback = null;
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.popupOptions = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	    this.cache = new main_core.Cache.MemoryCache();
	    this.handleButtonClick = this.handleButtonClick.bind(this);
	    this.modal = options.modal !== false;
	    this.cacheable = options.cacheable === true;
	    this.setTitle(options.title);
	    this.setMessage(options.message);
	    this.setOkCallback(options.onOk);
	    this.setCancelCallback(options.onCancel);
	    this.setYesCallback(options.onYes);
	    this.setNoCallback(options.onNo);
	    if (main_core.Type.isBoolean(options.mediumButtonSize)) {
	      this.mediumButtonSize = options.mediumButtonSize;
	    } else if (this.getTitle() !== null) {
	      this.mediumButtonSize = true;
	    }
	    if (this.getTitle() !== null) {
	      this.popupOptions.closeIcon = true;
	    }
	    if (this.isMediumButtonSize()) {
	      this.minWidth = 400;
	      this.minHeight = 200;
	      this.maxWidth = 420;
	    }
	    this.minWidth = main_core.Type.isNumber(options.minWidth) ? options.minWidth : this.minWidth;
	    this.minHeight = main_core.Type.isNumber(options.minHeight) ? options.minHeight : this.minHeight;
	    this.maxWidth = main_core.Type.isNumber(options.maxWidth) ? options.maxWidth : this.maxWidth;
	    this.setOkCaption(options.okCaption);
	    this.setCancelCaption(options.cancelCaption);
	    this.setYesCaption(options.yesCaption);
	    this.setNoCaption(options.noCaption);
	    this.setButtons(options.buttons);
	  }

	  /**
	   * @param {string} message
	   * @param args
	   * @example
	   * BX.UI.Dialogs.{MessageBox.alert('Message');
	   * BX.UI.Dialogs.MessageBox.alert('Message', (messageBox, button, event) => {});
	   * BX.UI.Dialogs.MessageBox.alert('Message', (messageBox, button, event) => {}, 'Proceed');
	   * BX.UI.Dialogs.MessageBox.alert('Message', 'Title');
	   * BX.UI.Dialogs.MessageBox.alert('Message', 'Title', (messageBox, button, event) => {});
	   * BX.UI.Dialogs.MessageBox.alert('Message', 'Title', (messageBox, button, event) => {}, 'Proceed');
	   */
	  static alert(message, ...args) {
	    let title = null;
	    let okCallback = null;
	    let okCaption = null;
	    if (args.length > 0) {
	      if (main_core.Type.isString(args[0])) {
	        [title, okCallback, okCaption] = args;
	      } else {
	        [okCallback, okCaption] = args;
	      }
	    }
	    const messageBox = this.create({
	      message,
	      title,
	      okCaption,
	      onOk: okCallback,
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK
	    });
	    messageBox.show();
	    return messageBox;
	  }

	  /**
	   *
	   * @param {string} message
	   * @param args
	   *
	   * @example
	   * BX.UI.Dialogs.MessageBox.confirm('Message');
	   * BX.UI.Dialogs.MessageBox.confirm('Message', () => {});
	   * BX.UI.Dialogs.MessageBox.confirm('Message', () => {}, 'Proceed');
	   * BX.UI.Dialogs.MessageBox.confirm('Message', () => {}, 'Proceed', () => {});
	   * BX.UI.Dialogs.MessageBox.confirm('Message', () => {}, 'Proceed', () => {}, 'Cancel');
	   * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title');
	   * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {});
	   * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {}, 'Proceed', () => {});
	   * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {}, 'Proceed', () => {}, 'Cancel');
	   */
	  static confirm(message, ...args) {
	    let title = null;
	    let okCallback = null;
	    let okCaption = null;
	    let cancelCallback = null;
	    let cancelCaption = null;
	    if (args.length > 0) {
	      if (main_core.Type.isString(args[0])) {
	        [title, okCallback, okCaption, cancelCallback, cancelCaption] = args;
	      } else {
	        [okCallback, okCaption, cancelCallback, cancelCaption] = args;
	      }
	    }
	    const messageBox = this.create({
	      message,
	      title,
	      okCaption,
	      cancelCaption,
	      onOk: okCallback,
	      onCancel: cancelCallback,
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL
	    });
	    messageBox.show();
	    return messageBox;
	  }
	  static show(options = {}) {
	    const messageBox = this.create(options);
	    messageBox.show();
	  }
	  static create(options = {}) {
	    return new this(options);
	  }
	  show() {
	    if (this.getPopupWindow().isDestroyed()) {
	      this.popupWindow = null;
	    }
	    this.getPopupWindow().show();
	  }
	  close() {
	    this.getPopupWindow().close();
	  }

	  /**
	   *
	   * @returns {PopupWindow}
	   */
	  getPopupWindow() {
	    if (this.popupWindow === null) {
	      this.popupWindow = new main_popup.Popup({
	        bindElement: null,
	        className: this.isMediumButtonSize() ? 'ui-message-box ui-message-box-medium-buttons' : 'ui-message-box',
	        content: this.getMessage(),
	        titleBar: this.getTitle(),
	        minWidth: this.minWidth,
	        minHeight: this.minHeight,
	        maxWidth: this.maxWidth,
	        overlay: this.modal ? {
	          opacity: 20
	        } : null,
	        cacheable: this.cacheable,
	        closeIcon: false,
	        contentBackground: 'transparent',
	        padding: 0,
	        buttons: this.getButtons(),
	        ...this.popupOptions
	      });
	    }
	    return this.popupWindow;
	  }
	  setMessage(message) {
	    if (main_core.Type.isString(message) || main_core.Type.isDomNode(message)) {
	      this.message = message;
	      if (this.popupWindow !== null) {
	        this.popupWindow.setContent(message);
	      }
	    }
	  }

	  /**
	   *
	   * @returns {?string|Element|Node}
	   */
	  getMessage() {
	    return this.message;
	  }
	  setTitle(title) {
	    if (main_core.Type.isString(title)) {
	      this.title = title;
	      if (this.popupWindow !== null) {
	        this.popupWindow.setTitleBar(title);
	      }
	    }
	  }

	  /**
	   *
	   * @returns {?string}
	   */
	  getTitle() {
	    return this.title;
	  }

	  /**
	   *
	   * @param {string|BX.UI.Button[]} buttons
	   */
	  setButtons(buttons) {
	    if (main_core.Type.isArray(buttons)) {
	      this.buttons = buttons;
	    } else if (main_core.Type.isString(buttons)) {
	      this.buttons = this.getButtonsLayout(buttons);
	    }
	    if (this.popupWindow !== null) {
	      this.popupWindow.setButtons(this.buttons);
	    }
	  }

	  /**
	   *
	   * @returns {BX.UI.Button[]}
	   */
	  getButtons() {
	    return this.buttons;
	  }
	  setOkCaption(caption) {
	    if (main_core.Type.isString(caption)) {
	      this.getOkButton().setText(caption);
	    }
	  }
	  setCancelCaption(caption) {
	    if (main_core.Type.isString(caption)) {
	      this.getCancelButton().setText(caption);
	    }
	  }
	  setYesCaption(caption) {
	    if (main_core.Type.isString(caption)) {
	      this.getYesButton().setText(caption);
	    }
	  }
	  setNoCaption(caption) {
	    if (main_core.Type.isString(caption)) {
	      this.getNoButton().setText(caption);
	    }
	  }
	  setOkCallback(fn) {
	    if (main_core.Type.isFunction(fn)) {
	      this.okCallback = fn;
	    }
	  }
	  setCancelCallback(fn) {
	    if (main_core.Type.isFunction(fn)) {
	      this.cancelCallback = fn;
	    }
	  }
	  setYesCallback(fn) {
	    if (main_core.Type.isFunction(fn)) {
	      this.yesCallback = fn;
	    }
	  }
	  setNoCallback(fn) {
	    if (main_core.Type.isFunction(fn)) {
	      this.noCallback = fn;
	    }
	  }

	  /**
	   *
	   * @returns {boolean}
	   */
	  isMediumButtonSize() {
	    return this.mediumButtonSize;
	  }

	  /**
	   *
	   * @returns {BX.UI.Button}
	   */
	  getOkButton() {
	    return this.cache.remember('okBtn', () => {
	      return new BX.UI.Button({
	        id: MessageBoxButtons.OK,
	        size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	        color: BX.UI.Button.Color.PRIMARY,
	        text: main_core.Loc.getMessage('UI_MESSAGE_BOX_OK_CAPTION'),
	        events: {
	          click: this.handleButtonClick
	        }
	      });
	    });
	  }

	  /**
	   *
	   * @returns {BX.UI.Button}
	   */
	  getCancelButton() {
	    return this.cache.remember('cancelBtn', () => {
	      return new BX.UI.CancelButton({
	        id: MessageBoxButtons.CANCEL,
	        size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	        text: main_core.Loc.getMessage('UI_MESSAGE_BOX_CANCEL_CAPTION'),
	        events: {
	          click: this.handleButtonClick
	        }
	      });
	    });
	  }

	  /**
	   *
	   * @returns {BX.UI.Button}
	   */
	  getYesButton() {
	    return this.cache.remember('yesBtn', () => {
	      return new BX.UI.Button({
	        id: MessageBoxButtons.YES,
	        size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	        color: BX.UI.Button.Color.PRIMARY,
	        text: main_core.Loc.getMessage('UI_MESSAGE_BOX_YES_CAPTION'),
	        events: {
	          click: this.handleButtonClick
	        }
	      });
	    });
	  }

	  /**
	   *
	   * @returns {BX.UI.Button}
	   */
	  getNoButton() {
	    return this.cache.remember('noBtn', () => {
	      return new BX.UI.Button({
	        id: MessageBoxButtons.NO,
	        size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	        color: BX.UI.Button.Color.LIGHT_BORDER,
	        text: main_core.Loc.getMessage('UI_MESSAGE_BOX_NO_CAPTION'),
	        events: {
	          click: this.handleButtonClick
	        }
	      });
	    });
	  }

	  /**
	   *
	   * @param buttons
	   * @returns {BX.UI.Button[]}
	   */
	  getButtonsLayout(buttons) {
	    switch (buttons) {
	      case MessageBoxButtons.OK:
	        return [this.getOkButton()];
	      case MessageBoxButtons.CANCEL:
	        return [this.getCancelButton()];
	      case MessageBoxButtons.YES:
	        return [this.getYesButton()];
	      case MessageBoxButtons.NO:
	        return [this.getNoButton()];
	      case MessageBoxButtons.OK_CANCEL:
	        return [this.getOkButton(), this.getCancelButton()];
	      case MessageBoxButtons.YES_NO:
	        return [this.getYesButton(), this.getNoButton()];
	      case MessageBoxButtons.YES_CANCEL:
	        return [this.getYesButton(), this.getCancelButton()];
	      case MessageBoxButtons.YES_NO_CANCEL:
	        return [this.getYesButton(), this.getNoButton(), this.getCancelButton()];
	      default:
	        return [];
	    }
	  }

	  /**
	   *
	   * @param {BX.UI.Button} button
	   * @param event
	   */
	  handleButtonClick(button, event) {
	    if (button.isDisabled()) {
	      return;
	    }
	    button.setDisabled(); // prevent a double click

	    const fn = this[`${button.getId()}Callback`];
	    if (!fn) {
	      button.setDisabled(false);
	      this.close();
	      return;
	    }
	    const result = fn(this, button, event);
	    if (result === true) {
	      button.setDisabled(false);
	      this.close();
	    } else if (result === false) {
	      button.setDisabled(false);
	    } else if (result && (Object.prototype.toString.call(result) === '[object Promise]' || result.toString() === '[object BX.Promise]')) {
	      button.setWaiting();
	      result.then(result => {
	        button.setWaiting(false);
	        this.close();
	      }, reason => {
	        button.setWaiting(false);
	      });
	    }
	  }
	}

	exports.MessageBox = MessageBox;
	exports.MessageBoxButtons = MessageBoxButtons;

}((this.BX.UI.Dialogs = this.BX.UI.Dialogs || {}),BX,BX.Main));
//# sourceMappingURL=dialogs.bundle.js.map
