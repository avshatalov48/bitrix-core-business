this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_popup) {
	'use strict';

	/**
	 * @namespace BX.UI.Dialogs
	 */
	var MessageBoxButtons = function MessageBoxButtons() {
	  babelHelpers.classCallCheck(this, MessageBoxButtons);
	};

	babelHelpers.defineProperty(MessageBoxButtons, "NONE", "none");
	babelHelpers.defineProperty(MessageBoxButtons, "OK", "ok");
	babelHelpers.defineProperty(MessageBoxButtons, "CANCEL", "cancel");
	babelHelpers.defineProperty(MessageBoxButtons, "YES", "yes");
	babelHelpers.defineProperty(MessageBoxButtons, "NO", "no");
	babelHelpers.defineProperty(MessageBoxButtons, "OK_CANCEL", "ok_cancel");
	babelHelpers.defineProperty(MessageBoxButtons, "YES_NO", "yes_no");
	babelHelpers.defineProperty(MessageBoxButtons, "YES_CANCEL", "yes_cancel");
	babelHelpers.defineProperty(MessageBoxButtons, "YES_NO_CANCEL", "yes_no_cancel");

	/**
	 * @namespace {BX.UI.Dialogs}
	 */

	var MessageBox =
	/*#__PURE__*/
	function () {
	  /** @var {PopupWindow} */
	  function MessageBox() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MessageBox);
	    babelHelpers.defineProperty(this, "popupWindow", null);
	    babelHelpers.defineProperty(this, "title", null);
	    babelHelpers.defineProperty(this, "message", null);
	    babelHelpers.defineProperty(this, "modal", false);
	    babelHelpers.defineProperty(this, "popupOptions", {});
	    babelHelpers.defineProperty(this, "minWidth", 300);
	    babelHelpers.defineProperty(this, "minHeight", 150);
	    babelHelpers.defineProperty(this, "maxWidth", 400);
	    babelHelpers.defineProperty(this, "buttons", []);
	    babelHelpers.defineProperty(this, "okCallback", null);
	    babelHelpers.defineProperty(this, "cancelCallback", null);
	    babelHelpers.defineProperty(this, "yesCallback", null);
	    babelHelpers.defineProperty(this, "noCallback", null);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.popupOptions = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
	    this.cache = new main_core.Cache.MemoryCache();
	    this.handleButtonClick = this.handleButtonClick.bind(this);
	    this.modal = options.modal === true;
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

	    if (this.isMediumButtonSize()) {
	      this.minWidth = 400;
	      this.minHeight = 200;
	      this.maxWidth = 500;
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


	  babelHelpers.createClass(MessageBox, [{
	    key: "show",
	    value: function show() {
	      if (this.getPopupWindow().isDestroyed()) {
	        this.popupWindow = null;
	      }

	      this.getPopupWindow().show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.getPopupWindow().close();
	    }
	    /**
	     *
	     * @returns {PopupWindow}
	     */

	  }, {
	    key: "getPopupWindow",
	    value: function getPopupWindow() {
	      if (this.popupWindow === null) {
	        this.popupWindow = new main_popup.PopupWindow(babelHelpers.objectSpread({
	          bindElement: null,
	          className: this.isMediumButtonSize() ? 'ui-message-box ui-message-box-medium-buttons' : 'ui-message-box',
	          content: this.getMessage(),
	          titleBar: this.getTitle(),
	          minWidth: this.minWidth,
	          minHeight: this.minHeight,
	          maxWidth: this.maxWidth,
	          overlay: this.modal,
	          cacheable: this.cacheable,
	          closeIcon: false,
	          contentBackground: 'transparent',
	          padding: 0,
	          buttons: this.getButtons()
	        }, this.popupOptions));
	      }

	      return this.popupWindow;
	    }
	  }, {
	    key: "setMessage",
	    value: function setMessage(message) {
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

	  }, {
	    key: "getMessage",
	    value: function getMessage() {
	      return this.message;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
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

	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	    /**
	     *
	     * @param {string|BX.UI.Button[]} buttons
	     */

	  }, {
	    key: "setButtons",
	    value: function setButtons(buttons) {
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

	  }, {
	    key: "getButtons",
	    value: function getButtons() {
	      return this.buttons;
	    }
	  }, {
	    key: "setOkCaption",
	    value: function setOkCaption(caption) {
	      if (main_core.Type.isString(caption)) {
	        this.getOkButton().setText(caption);
	      }
	    }
	  }, {
	    key: "setCancelCaption",
	    value: function setCancelCaption(caption) {
	      if (main_core.Type.isString(caption)) {
	        this.getCancelButton().setText(caption);
	      }
	    }
	  }, {
	    key: "setYesCaption",
	    value: function setYesCaption(caption) {
	      if (main_core.Type.isString(caption)) {
	        this.getYesButton().setText(caption);
	      }
	    }
	  }, {
	    key: "setNoCaption",
	    value: function setNoCaption(caption) {
	      if (main_core.Type.isString(caption)) {
	        this.getNoButton().setText(caption);
	      }
	    }
	  }, {
	    key: "setOkCallback",
	    value: function setOkCallback(fn) {
	      if (main_core.Type.isFunction(fn)) {
	        this.okCallback = fn;
	      }
	    }
	  }, {
	    key: "setCancelCallback",
	    value: function setCancelCallback(fn) {
	      if (main_core.Type.isFunction(fn)) {
	        this.cancelCallback = fn;
	      }
	    }
	  }, {
	    key: "setYesCallback",
	    value: function setYesCallback(fn) {
	      if (main_core.Type.isFunction(fn)) {
	        this.yesCallback = fn;
	      }
	    }
	  }, {
	    key: "setNoCallback",
	    value: function setNoCallback(fn) {
	      if (main_core.Type.isFunction(fn)) {
	        this.noCallback = fn;
	      }
	    }
	    /**
	     *
	     * @returns {boolean}
	     */

	  }, {
	    key: "isMediumButtonSize",
	    value: function isMediumButtonSize() {
	      return this.mediumButtonSize;
	    }
	    /**
	     *
	     * @returns {BX.UI.Button}
	     */

	  }, {
	    key: "getOkButton",
	    value: function getOkButton() {
	      var _this = this;

	      return this.cache.remember('okBtn', function () {
	        return new BX.UI.Button({
	          id: MessageBoxButtons.OK,
	          size: _this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	          color: BX.UI.Button.Color.PRIMARY,
	          text: main_core.Loc.getMessage('UI_MESSAGE_BOX_OK_CAPTION'),
	          events: {
	            click: _this.handleButtonClick
	          }
	        });
	      });
	    }
	    /**
	     *
	     * @returns {BX.UI.Button}
	     */

	  }, {
	    key: "getCancelButton",
	    value: function getCancelButton() {
	      var _this2 = this;

	      return this.cache.remember('cancelBtn', function () {
	        return new BX.UI.CancelButton({
	          id: MessageBoxButtons.CANCEL,
	          size: _this2.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	          text: main_core.Loc.getMessage('UI_MESSAGE_BOX_CANCEL_CAPTION'),
	          events: {
	            click: _this2.handleButtonClick
	          }
	        });
	      });
	    }
	    /**
	     *
	     * @returns {BX.UI.Button}
	     */

	  }, {
	    key: "getYesButton",
	    value: function getYesButton() {
	      var _this3 = this;

	      return this.cache.remember('yesBtn', function () {
	        return new BX.UI.Button({
	          id: MessageBoxButtons.YES,
	          size: _this3.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	          color: BX.UI.Button.Color.PRIMARY,
	          text: main_core.Loc.getMessage('UI_MESSAGE_BOX_YES_CAPTION'),
	          events: {
	            click: _this3.handleButtonClick
	          }
	        });
	      });
	    }
	    /**
	     *
	     * @returns {BX.UI.Button}
	     */

	  }, {
	    key: "getNoButton",
	    value: function getNoButton() {
	      var _this4 = this;

	      return this.cache.remember('noBtn', function () {
	        return new BX.UI.Button({
	          id: MessageBoxButtons.NO,
	          size: _this4.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
	          color: BX.UI.Button.Color.LIGHT_BORDER,
	          text: main_core.Loc.getMessage('UI_MESSAGE_BOX_NO_CAPTION'),
	          events: {
	            click: _this4.handleButtonClick
	          }
	        });
	      });
	    }
	    /**
	     *
	     * @param buttons
	     * @returns {BX.UI.Button[]}
	     */

	  }, {
	    key: "getButtonsLayout",
	    value: function getButtonsLayout(buttons) {
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

	  }, {
	    key: "handleButtonClick",
	    value: function handleButtonClick(button, event) {
	      var _this5 = this;

	      if (button.isDisabled()) {
	        return;
	      }

	      button.setDisabled(); // prevent a double click

	      var fn = this["".concat(button.getId(), "Callback")];

	      if (!fn) {
	        button.setDisabled(false);
	        this.close();
	        return;
	      }

	      var result = fn(this, button, event);

	      if (result === true) {
	        button.setDisabled(false);
	        this.close();
	      } else if (result === false) {
	        button.setDisabled(false);
	      } else if (result && (Object.prototype.toString.call(result) === '[object Promise]' || result.toString() === '[object BX.Promise]')) {
	        button.setWaiting();
	        result.then(function (result) {
	          button.setWaiting(false);

	          _this5.close();
	        }, function (reason) {
	          button.setWaiting(false);
	        });
	      }
	    }
	  }], [{
	    key: "alert",
	    value: function alert(message) {
	      var title = null;
	      var okCallback = null;
	      var okCaption = null;

	      for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        args[_key - 1] = arguments[_key];
	      }

	      if (args.length) {
	        if (main_core.Type.isString(args[0])) {
	          title = args[0];
	          okCallback = args[1];
	          okCaption = args[2];
	        } else {
	          okCallback = args[0];
	          okCaption = args[1];
	        }
	      }

	      this.show({
	        message: message,
	        title: title,
	        okCaption: okCaption,
	        onOk: okCallback,
	        buttons: BX.UI.Dialogs.MessageBoxButtons.OK
	      });
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
	     * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title');
	     * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {});
	     * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {}, 'Proceed', () => {});
	     */

	  }, {
	    key: "confirm",
	    value: function confirm(message) {
	      var title = null;
	      var okCallback = null;
	      var okCaption = null;
	      var cancelCallback = null;

	      for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
	        args[_key2 - 1] = arguments[_key2];
	      }

	      if (args.length) {
	        if (main_core.Type.isString(args[0])) {
	          title = args[0];
	          okCallback = args[1];
	          okCaption = args[2];
	          cancelCallback = args[3];
	        } else {
	          okCallback = args[0];
	          okCaption = args[1];
	          cancelCallback = args[2];
	        }
	      }

	      this.show({
	        message: message,
	        title: title,
	        okCaption: okCaption,
	        onOk: okCallback,
	        onCancel: cancelCallback,
	        buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var messageBox = this.create(options);
	      messageBox.show();
	    }
	  }, {
	    key: "create",
	    value: function create() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new this(options);
	    }
	  }]);
	  return MessageBox;
	}();

	exports.MessageBox = MessageBox;
	exports.MessageBoxButtons = MessageBoxButtons;

}((this.BX.UI.Dialogs = this.BX.UI.Dialogs || {}),BX,BX.Main));
//# sourceMappingURL=dialogs.bundle.js.map
