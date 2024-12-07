/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_sidepanel_layout,ui_hint,ui_alerts,ui_buttons,ui_forms,ui_layoutForm) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11;
	const SidePanel = BX.SidePanel;
	const emailRegularEx = /\S+@\S+\.\S+/;
	const deleteMessage = 'mail-mailbox-config-delete';
	const senderType = 'sender';
	var _setFieldData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setFieldData");
	var _showDisconnectDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showDisconnectDialog");
	var _save = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("save");
	var _disconnect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disconnect");
	var _saveSender = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("saveSender");
	var _createContentContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createContentContainer");
	var _createAlertNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createAlertNode");
	var _createSenderSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSenderSection");
	var _createSmtpServerSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSmtpServerSection");
	var _createSmtpEmailRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSmtpEmailRow");
	var _createSmtpServerRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSmtpServerRow");
	var _createSmtpPortAndSafeConnectionRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSmtpPortAndSafeConnectionRow");
	var _createSmtpLoginRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSmtpLoginRow");
	var _createSmtpPasswordRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSmtpPasswordRow");
	var _createLimitSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createLimitSection");
	var _showAlertNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showAlertNode");
	var _hideAlertNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideAlertNode");
	var _prepareNecessaryFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareNecessaryFields");
	var _hasInvalidFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasInvalidFields");
	var _clearInvalidFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearInvalidFields");
	var _isInvalidField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInvalidField");
	var _getErrorMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getErrorMessage");
	var _setUserName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setUserName");
	class SmtpEditor {
	  constructor(options) {
	    var _options$onClose;
	    Object.defineProperty(this, _setUserName, {
	      value: _setUserName2
	    });
	    Object.defineProperty(this, _getErrorMessage, {
	      value: _getErrorMessage2
	    });
	    Object.defineProperty(this, _isInvalidField, {
	      value: _isInvalidField2
	    });
	    Object.defineProperty(this, _clearInvalidFields, {
	      value: _clearInvalidFields2
	    });
	    Object.defineProperty(this, _hasInvalidFields, {
	      value: _hasInvalidFields2
	    });
	    Object.defineProperty(this, _prepareNecessaryFields, {
	      value: _prepareNecessaryFields2
	    });
	    Object.defineProperty(this, _hideAlertNode, {
	      value: _hideAlertNode2
	    });
	    Object.defineProperty(this, _showAlertNode, {
	      value: _showAlertNode2
	    });
	    Object.defineProperty(this, _createLimitSection, {
	      value: _createLimitSection2
	    });
	    Object.defineProperty(this, _createSmtpPasswordRow, {
	      value: _createSmtpPasswordRow2
	    });
	    Object.defineProperty(this, _createSmtpLoginRow, {
	      value: _createSmtpLoginRow2
	    });
	    Object.defineProperty(this, _createSmtpPortAndSafeConnectionRow, {
	      value: _createSmtpPortAndSafeConnectionRow2
	    });
	    Object.defineProperty(this, _createSmtpServerRow, {
	      value: _createSmtpServerRow2
	    });
	    Object.defineProperty(this, _createSmtpEmailRow, {
	      value: _createSmtpEmailRow2
	    });
	    Object.defineProperty(this, _createSmtpServerSection, {
	      value: _createSmtpServerSection2
	    });
	    Object.defineProperty(this, _createSenderSection, {
	      value: _createSenderSection2
	    });
	    Object.defineProperty(this, _createAlertNode, {
	      value: _createAlertNode2
	    });
	    Object.defineProperty(this, _createContentContainer, {
	      value: _createContentContainer2
	    });
	    Object.defineProperty(this, _saveSender, {
	      value: _saveSender2
	    });
	    Object.defineProperty(this, _disconnect, {
	      value: _disconnect2
	    });
	    Object.defineProperty(this, _save, {
	      value: _save2
	    });
	    Object.defineProperty(this, _showDisconnectDialog, {
	      value: _showDisconnectDialog2
	    });
	    Object.defineProperty(this, _setFieldData, {
	      value: _setFieldData2
	    });
	    if (options) {
	      var _options$setSenderCal, _options$addSenderCal;
	      if (options.senderId && main_core.Type.isInteger(options.senderId) && options.senderId > 0) {
	        this.title = main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_EDIT_TITLE');
	        this.senderId = options.senderId;
	      } else {
	        this.title = main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_ADD_TITLE');
	      }
	      this.setSender = (_options$setSenderCal = options.setSenderCallback) != null ? _options$setSenderCal : null;
	      this.addSender = (_options$addSenderCal = options.addSenderCallback) != null ? _options$addSenderCal : null;
	    }
	    this.onCloseAction = (_options$onClose = options.onClose) != null ? _options$onClose : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _createContentContainer)[_createContentContainer]();
	    babelHelpers.classPrivateFieldLooseBase(this, _prepareNecessaryFields)[_prepareNecessaryFields]();
	  }
	  static openSlider(options) {
	    const instance = new SmtpEditor(options);
	    SidePanel.Instance.open('smtpSender', {
	      width: 760,
	      cacheable: false,
	      contentCallback: () => {
	        return instance.getContentCallback();
	      },
	      events: {
	        onLoad: () => {
	          main_core.ready(() => {
	            new ui_layoutForm.LayoutForm({
	              container: instance.limitSection
	            });
	          });
	        }
	      }
	    });
	  }
	  getContentCallback() {
	    return ui_sidepanel_layout.Layout.createContent({
	      extensions: ['ui.mail.sender-editor'],
	      title: this.title,
	      design: {
	        section: false,
	        margin: false
	      },
	      content: () => {
	        if (this.senderId > 0) {
	          return this.loadSender(this.senderId);
	        }
	        return main_core.ajax.runAction('main.api.mail.sender.getDefaultSenderName').then(response => {
	          babelHelpers.classPrivateFieldLooseBase(this, _setUserName)[_setUserName](response.data);
	          return this.getContentContainer();
	        }).catch(() => {
	          return this.getContentContainer();
	        });
	      },
	      buttons: ({
	        cancelButton,
	        Button
	      }) => {
	        const buttonArray = [];
	        const saveButton = new ui_buttons.SaveButton({
	          onclick: () => {
	            babelHelpers.classPrivateFieldLooseBase(this, _save)[_save](saveButton);
	          }
	        });
	        buttonArray.push(saveButton);
	        if (this.senderId > 0) {
	          this.disconnectButton = new Button({
	            text: main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_DISCONNECT_BUTTON'),
	            color: BX.UI.Button.Color.DANGER,
	            onclick: () => {
	              babelHelpers.classPrivateFieldLooseBase(this, _showDisconnectDialog)[_showDisconnectDialog]();
	            }
	          });
	          buttonArray.push(this.disconnectButton);
	        }
	        buttonArray.push(cancelButton);
	        return buttonArray;
	      }
	    });
	  }
	  loadSender(senderId) {
	    return main_core.ajax.runAction('main.api.mail.sender.getSenderData', {
	      data: {
	        senderId
	      }
	    }).then(response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _setFieldData)[_setFieldData](response.data);
	      return this.getContentContainer();
	    }).catch(() => {
	      return this.getContentContainer();
	    });
	  }
	  getContentContainer() {
	    return this.contentContainer;
	  }
	}
	function _setFieldData2(senderData) {
	  this.nameField.value = senderData.name;
	  this.accessField.checked = senderData.isPublic;
	  this.emailField.value = senderData.email;
	  this.serverField.value = senderData.server;
	  this.portField.value = senderData.port;
	  this.loginField.value = senderData.login;
	  if (senderData.protocol === 'smtps') {
	    this.sslField.checked = true;
	  }
	  if (main_core.Type.isNumber(senderData.limit) && senderData.limit > 0) {
	    this.senderLimitCheckbox.checked = true;
	    this.senderLimitField.value = senderData.limit;
	  }
	}
	function _showDisconnectDialog2() {
	  top.BX.UI.Dialogs.MessageBox.show({
	    message: main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_DISCONNECT_MESSAGE'),
	    modal: true,
	    buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
	    onOk: messageBox => {
	      babelHelpers.classPrivateFieldLooseBase(this, _disconnect)[_disconnect]();
	      messageBox.close();
	    },
	    onCancel: messageBox => {
	      messageBox.close();
	    }
	  });
	}
	function _save2(button) {
	  babelHelpers.classPrivateFieldLooseBase(this, _clearInvalidFields)[_clearInvalidFields]();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _hasInvalidFields)[_hasInvalidFields]()) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _hideAlertNode)[_hideAlertNode]();
	  button.setClocking();
	  babelHelpers.classPrivateFieldLooseBase(this, _saveSender)[_saveSender]().then(response => {
	    const data = response.data;
	    if (this.setSender) {
	      this.setSender(data.senderId, data.name, this.email);
	    }
	    if (this.addSender) {
	      const mailbox = [];
	      mailbox.name = data.name;
	      mailbox.email = this.email;
	      this.addSender(mailbox);
	    }
	    BX.SidePanel.Instance.getTopSlider().close();
	    if (!this.senderId && !this.addSender) {
	      AliasEditor.openSlider({
	        senderId: data.senderId,
	        email: this.email,
	        setSenderCallback: this.setSender,
	        onClose: this.onCloseAction
	      });
	    }
	  }).catch(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _showAlertNode)[_showAlertNode](response.errors[0].message);
	    button.setClocking(false);
	  });
	}
	function _disconnect2() {
	  main_core.Dom.addClass(this.disconnectButton, 'ui-btn-wait');
	  main_core.ajax.runAction('main.api.mail.sender.deleteSender', {
	    data: {
	      senderId: this.senderId
	    }
	  }).then(() => {
	    main_core.Dom.removeClass(this.disconnectButton, 'ui-btn-wait');
	    SidePanel.Instance.getTopSlider().close();
	    top.BX.SidePanel.Instance.postMessage(window, deleteMessage, {
	      id: this.senderId,
	      type: senderType
	    });
	  }).catch(() => {
	    main_core.Dom.removeClass(this.disconnectButton, 'ui-btn-wait');
	  });
	}
	function _saveSender2() {
	  var _this$senderId;
	  this.email = this.emailField.value;
	  const data = {
	    id: (_this$senderId = this.senderId) != null ? _this$senderId : null,
	    name: this.nameField.value,
	    email: this.email,
	    smtp: {},
	    public: this.accessField.checked ? 'Y' : 'N'
	  };
	  data.smtp = {
	    server: this.serverField.value,
	    port: this.portField.value,
	    ssl: this.sslField.checked ? this.sslField.value : '',
	    login: this.loginField.value,
	    password: this.passwordField.value,
	    limit: this.senderLimitCheckbox.checked ? this.senderLimitField.value : null
	  };
	  return main_core.ajax.runAction('main.api.mail.sender.submitSender', {
	    data: {
	      data
	    }
	  }).then(response => {
	    return response;
	  });
	}
	function _createContentContainer2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _createAlertNode)[_createAlertNode]();
	  babelHelpers.classPrivateFieldLooseBase(this, _createSenderSection)[_createSenderSection]();
	  babelHelpers.classPrivateFieldLooseBase(this, _createSmtpServerSection)[_createSmtpServerSection]();
	  babelHelpers.classPrivateFieldLooseBase(this, _createLimitSection)[_createLimitSection]();
	  this.contentContainer = main_core.Tag.render(_t || (_t = _`
			<div class="ui-form">
				${0}
				${0}
				${0}
				${0}
			</div>
		`), this.alertNode, this.senderSection, this.smtpServerSection, this.limitSection);
	}
	function _createAlertNode2() {
	  this.alertNode = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-alert ui-alert-danger ui-alert-icon-warning" style="display: none">
				<span class="ui-alert-message"></span>
			</div>
		`));
	}
	function _createSenderSection2() {
	  var _top$BX$UI$Hint;
	  const {
	    root,
	    nameField,
	    accessField
	  } = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-slider-section">
				<div class="ui-slider-content-box">
					<div class="ui-slider-heading-4">${0}</div>
					<div class="ui-form-row">
						<div class="ui-ctl-top smtp-sender-name">
							<div class="ui-form-label">${0}</div>
							<span data-hint="${0}"></span>
						</div>
						<div class="ui-form-row-inline ui-ctl-w100">
							<div class="ui-form-row">
								<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
									<input type="text" data-name="name" value="" class="ui-ctl-element" ref="nameField">
								</div>
							</div>
							<div class="ui-form-row">
								<label class="ui-ctl ui-ctl-checkbox">
									<input type="checkbox" class="ui-ctl-element" data-name="access" ref="accessField">
									<div class="ui-ctl-label-text">${0}</div>
									<span data-hint="${0}"></span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_MAIN_SECTION_TITLE'), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_NAME'), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_NAME_HINT'), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_AVAILABLE_TOGGLE'), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_AVAILABLE_TOGGLE_HINT'));
	  this.senderSection = root;
	  this.nameField = nameField;
	  this.accessField = accessField;
	  this.hintInstence = (_top$BX$UI$Hint = top.BX.UI.Hint) == null ? void 0 : _top$BX$UI$Hint.createInstance();
	  this.hintInstence.init(this.senderSection);
	}
	function _createSmtpServerSection2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _createSmtpEmailRow)[_createSmtpEmailRow]();
	  babelHelpers.classPrivateFieldLooseBase(this, _createSmtpServerRow)[_createSmtpServerRow]();
	  babelHelpers.classPrivateFieldLooseBase(this, _createSmtpPortAndSafeConnectionRow)[_createSmtpPortAndSafeConnectionRow]();
	  babelHelpers.classPrivateFieldLooseBase(this, _createSmtpLoginRow)[_createSmtpLoginRow]();
	  babelHelpers.classPrivateFieldLooseBase(this, _createSmtpPasswordRow)[_createSmtpPasswordRow]();
	  this.smtpServerSection = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="ui-slider-section">
				<div class="ui-slider-content-box">
					<div class="ui-slider-heading-4">${0}</div>
					${0}
					${0}
					${0}
					${0}
					${0}
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SMTP_SECTION_TITLE'), this.smtpEmailRow, this.smtpServerRow, this.smtpPortAndSafeConnectionRow, this.smtpLoginRow, this.smtpPasswordRow);
	}
	function _createSmtpEmailRow2() {
	  const {
	    root,
	    emailField
	  } = main_core.Tag.render(_t5 || (_t5 = _`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${0}</div>
				</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="email" name="email" class="ui-ctl-element" data-name="email" placeholder="info@example.com" ref="emailField">
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMAIL'));
	  this.smtpEmailRow = root;
	  this.emailField = emailField;
	}
	function _createSmtpServerRow2() {
	  const {
	    root,
	    serverField
	  } = main_core.Tag.render(_t6 || (_t6 = _`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${0}</div>
				</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="text" name="server" class="ui-ctl-element" data-name="server" placeholder="smtp.example.com" ref="serverField">
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SERVER'));
	  this.smtpServerRow = root;
	  this.serverField = serverField;
	}
	function _createSmtpPortAndSafeConnectionRow2() {
	  const {
	    root,
	    portField,
	    sslField
	  } = main_core.Tag.render(_t7 || (_t7 = _`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${0}</div>
				</div>
				<div class="ui-form-row-inline" style="margin-bottom: 0">
					<div class="ui-form-row">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						<input type="text" 
							class="ui-ctl-element" 
							data-name="port" 
							placeholder="555"
							ref="portField"
						>
						</div>
					</div>
					<div class="ui-form-row">
						<label class="ui-ctl ui-ctl-checkbox">
							<input type="checkbox" class="ui-ctl-element" value="Y" data-name="ssl" ref="sslField">
							<div class="ui-ctl-label-text">${0}</div>
						</label>
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_PORT'), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SSL'));
	  this.smtpPortAndSafeConnectionRow = root;
	  this.portField = portField;
	  this.sslField = sslField;
	}
	function _createSmtpLoginRow2() {
	  const {
	    root,
	    loginField
	  } = main_core.Tag.render(_t8 || (_t8 = _`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${0}</div>
				</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="text" class="ui-ctl-element" data-name="login" ref="loginField">
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_LOGIN'));
	  this.smtpLoginRow = root;
	  this.loginField = loginField;
	  main_core.Event.bind(this.emailField, 'input', () => {
	    this.loginField.value = this.emailField.value;
	  });
	}
	function _createSmtpPasswordRow2() {
	  const {
	    root,
	    passwordField
	  } = main_core.Tag.render(_t9 || (_t9 = _`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${0}</div>
				</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="password" class="ui-ctl-element" data-name="password" ref="passwordField">
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_PASSWORD'));
	  this.smtpPasswordRow = root;
	  this.passwordField = passwordField;
	}
	function _createLimitSection2() {
	  const {
	    root,
	    senderLimitCheckbox,
	    senderLimitField
	  } = main_core.Tag.render(_t10 || (_t10 = _`
			<div class="ui-slider-section">
				<div class="ui-slider-content-box">
					<div class="ui-slider-heading-4">${0}</div>
					<div class="ui-form-row">
						<div class="ui-form-label" data-form-row-hidden="">
							<label class="ui-ctl ui-ctl-checkbox smtp-editor-limit-checkbox">
								<input type="checkbox" class="ui-ctl-element" data-name="hasLimit" ref="senderLimitCheckbox">
								<div class="ui-ctl-label-text">${0}</div>
							</label>
						</div>
						<div class="ui-form-row-hidden">
							<div class="ui-form-row">
								<div class="ui-ctl-top">
									<div class="ui-form-label">${0}</div>
								</div>
								<div class="ui-ctl ui-ctl-textbox ui-ctl-w25">
									<input type="number" class="ui-ctl-element" data-name="limit" value="250" min="0" ref="senderLimitField">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_LIMIT_SECTION_TITLE'), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_LIMIT_SETTINGS'), main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_LIMIT_TITLE'));
	  this.limitSection = root;
	  this.senderLimitCheckbox = senderLimitCheckbox;
	  this.senderLimitField = senderLimitField;
	}
	function _showAlertNode2(message = null) {
	  if (message) {
	    const spanNode = this.alertNode.querySelector('span');
	    spanNode.textContent = message;
	  }
	  main_core.Dom.style(this.alertNode, 'display', 'block');
	}
	function _hideAlertNode2() {
	  main_core.Dom.style(this.alertNode, 'display', 'none');
	}
	function _prepareNecessaryFields2() {
	  this.requiredFields = [{
	    row: this.smtpEmailRow,
	    input: this.emailField,
	    type: 'email'
	  }, {
	    row: this.smtpServerRow,
	    input: this.serverField,
	    type: 'server'
	  }, {
	    row: this.smtpPortAndSafeConnectionRow,
	    input: this.portField,
	    type: 'port'
	  }, {
	    row: this.smtpLoginRow,
	    input: this.loginField,
	    type: 'login'
	  }];
	  if (!this.senderId) {
	    this.requiredFields.push({
	      row: this.smtpPasswordRow,
	      input: this.passwordField,
	      type: 'pass'
	    });
	  }
	}
	function _hasInvalidFields2() {
	  let count = 0;
	  this.requiredFields.forEach(field => {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isInvalidField)[_isInvalidField](field.type, field.input.value)) {
	      return;
	    }
	    count++;
	    main_core.Dom.addClass(field.row, 'ui-ctl-warning');
	    const errorMessage = babelHelpers.classPrivateFieldLooseBase(this, _getErrorMessage)[_getErrorMessage](field.type, field.input.value);
	    const invalidField = main_core.Tag.render(_t11 || (_t11 = _`
				<div class="ui-mail-field-error-message ui-ctl-bottom">${0}</div>
			`), errorMessage);
	    main_core.Dom.append(invalidField, field.row);
	    if (this.topEmptyNode) {
	      return;
	    }
	    this.topEmptyNode = field.row;
	    this.topEmptyNode.scrollIntoView();
	  });
	  return count > 0;
	}
	function _clearInvalidFields2() {
	  var _this$invalidFieldNod;
	  if (!this.requiredFields) {
	    return;
	  }
	  this.requiredFields.forEach(field => {
	    main_core.Dom.removeClass(field.row, 'ui-ctl-warning');
	    const errorMessageFiled = field.row.querySelector('.ui-mail-field-error-message');
	    if (main_core.Type.isDomNode(errorMessageFiled)) {
	      main_core.Dom.remove(errorMessageFiled);
	    }
	  });
	  this.topEmptyNode = null;
	  (_this$invalidFieldNod = this.invalidFieldNode) == null ? void 0 : _this$invalidFieldNod.remove();
	}
	function _isInvalidField2(type, input) {
	  if (input.length === 0) {
	    return true;
	  }
	  if (type === 'port' && (!Number.isInteger(Number(input)) || input < 0 || input > 65535)) {
	    return true;
	  }
	  return type === 'email' && !emailRegularEx.test(input);
	}
	function _getErrorMessage2(type, input) {
	  switch (type) {
	    case 'email':
	      if (main_core.Type.isString(input) && input.length > 0) {
	        return main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_INVALID_EMAIL');
	      }
	      return main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMPTY_EMAIL');
	    case 'server':
	      return main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMPTY_SERVER');
	    case 'port':
	      return main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_INVALID_PORT');
	    case 'login':
	      return main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMPTY_LOGIN');
	    default:
	      return main_core.Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMPTY_PASSWORD');
	  }
	}
	function _setUserName2(name) {
	  this.nameField.value = name;
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9$1,
	  _t10$1,
	  _t11$1,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19,
	  _t20;
	const mailboxType = 'mailbox';
	const senderType$1 = 'sender';
	const mailboxSenderType = 'mailboxSender';
	const aliasType = 'alias';
	const successSubmitMessage = 'mail-mailbox-config-success';
	const deleteMessage$1 = 'mail-mailbox-config-delete';
	const aliasSliderUrl = 'mailAliasSlider';
	var _senderNameNodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("senderNameNodes");
	var _createContentContainer$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createContentContainer");
	var _createAddSenderContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createAddSenderContainer");
	var _addAliasPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addAliasPromise");
	var _createToolbarButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createToolbarButtons");
	var _renderSenderItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSenderItem");
	var _renderSenderNameContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSenderNameContainer");
	var _renderSenderEditNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSenderEditNode");
	var _renderSenderExtraInfoContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSenderExtraInfoContainer");
	var _getExtraInfoText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getExtraInfoText");
	var _renderSenderEditContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSenderEditContainer");
	var _renderSenderAuthorContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSenderAuthorContainer");
	var _renderUserInfoNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderUserInfoNode");
	var _renderDeleteButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDeleteButton");
	var _renderSettingsButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettingsButton");
	var _renderSubmitButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSubmitButton");
	var _renderCancelButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCancelButton");
	var _addSenders = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addSenders");
	var _openSmtpSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openSmtpSettings");
	var _openMailboxSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openMailboxSettings");
	var _hasNameInvalidCharacters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasNameInvalidCharacters");
	var _checkAliasCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkAliasCounter");
	var _isMainSender = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMainSender");
	class AliasEditor {
	  constructor(options) {
	    Object.defineProperty(this, _isMainSender, {
	      value: _isMainSender2
	    });
	    Object.defineProperty(this, _checkAliasCounter, {
	      value: _checkAliasCounter2
	    });
	    Object.defineProperty(this, _hasNameInvalidCharacters, {
	      value: _hasNameInvalidCharacters2
	    });
	    Object.defineProperty(this, _openMailboxSettings, {
	      value: _openMailboxSettings2
	    });
	    Object.defineProperty(this, _openSmtpSettings, {
	      value: _openSmtpSettings2
	    });
	    Object.defineProperty(this, _addSenders, {
	      value: _addSenders2
	    });
	    Object.defineProperty(this, _renderCancelButton, {
	      value: _renderCancelButton2
	    });
	    Object.defineProperty(this, _renderSubmitButton, {
	      value: _renderSubmitButton2
	    });
	    Object.defineProperty(this, _renderSettingsButton, {
	      value: _renderSettingsButton2
	    });
	    Object.defineProperty(this, _renderDeleteButton, {
	      value: _renderDeleteButton2
	    });
	    Object.defineProperty(this, _renderUserInfoNode, {
	      value: _renderUserInfoNode2
	    });
	    Object.defineProperty(this, _renderSenderAuthorContainer, {
	      value: _renderSenderAuthorContainer2
	    });
	    Object.defineProperty(this, _renderSenderEditContainer, {
	      value: _renderSenderEditContainer2
	    });
	    Object.defineProperty(this, _getExtraInfoText, {
	      value: _getExtraInfoText2
	    });
	    Object.defineProperty(this, _renderSenderExtraInfoContainer, {
	      value: _renderSenderExtraInfoContainer2
	    });
	    Object.defineProperty(this, _renderSenderEditNode, {
	      value: _renderSenderEditNode2
	    });
	    Object.defineProperty(this, _renderSenderNameContainer, {
	      value: _renderSenderNameContainer2
	    });
	    Object.defineProperty(this, _renderSenderItem, {
	      value: _renderSenderItem2
	    });
	    Object.defineProperty(this, _createToolbarButtons, {
	      value: _createToolbarButtons2
	    });
	    Object.defineProperty(this, _addAliasPromise, {
	      value: _addAliasPromise2
	    });
	    Object.defineProperty(this, _createAddSenderContainer, {
	      value: _createAddSenderContainer2
	    });
	    Object.defineProperty(this, _createContentContainer$1, {
	      value: _createContentContainer2$1
	    });
	    this.wasSenderUpdated = false;
	    this.aliasCounter = 0;
	    Object.defineProperty(this, _senderNameNodes, {
	      writable: true,
	      value: new Map()
	    });
	    this.senderId = Number(options.senderId);
	    this.email = options.email;
	    this.setSender = options.setSenderCallback;
	    this.updateSenderList = options.updateSenderList;
	    babelHelpers.classPrivateFieldLooseBase(this, _createContentContainer$1)[_createContentContainer$1]();
	    babelHelpers.classPrivateFieldLooseBase(this, _createToolbarButtons)[_createToolbarButtons]();
	  }
	  static openSlider(options) {
	    const instance = new AliasEditor(options);
	    const onSliderMessage = function (event) {
	      const [sliderEvent] = event.getData();
	      if (!sliderEvent) {
	        return;
	      }
	      const eventMessage = sliderEvent.getEventId();
	      const data = sliderEvent.getData();
	      const mailboxId = Number(sliderEvent.data.id);
	      const slider = BX.SidePanel.Instance.getSlider(aliasSliderUrl);
	      if (eventMessage === successSubmitMessage) {
	        instance.wasSenderUpdated = true;
	        instance.updateMainSenderName(mailboxId);
	        if (slider) {
	          slider.close();
	        }
	        return;
	      }
	      if (eventMessage === deleteMessage$1) {
	        instance.wasSenderUpdated = true;
	        if (instance.id === Number(mailboxId)) {
	          instance.setSender();
	        }
	        if (slider) {
	          slider.close();
	        }
	        if (data && data.type !== senderType$1) {
	          BX.SidePanel.Instance.postMessage(window, sliderEvent.getEventId(), sliderEvent.getData);
	        }
	      }
	    };
	    BX.SidePanel.Instance.open(aliasSliderUrl, {
	      width: 800,
	      cacheable: false,
	      contentCallback: () => {
	        return ui_sidepanel_layout.Layout.createContent({
	          extensions: ['ui.mail.sender-editor'],
	          title: options.email,
	          design: {
	            section: false,
	            margin: false
	          },
	          content() {
	            return instance.loadSliderContent();
	          },
	          toolbar() {
	            return instance.getToolbarButtons();
	          },
	          buttons: () => {}
	        });
	      },
	      events: {
	        onClose: () => {
	          top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onMessage', onSliderMessage);
	          if (instance.updateSenderList && instance.wasSenderUpdated) {
	            instance.updateSenderList();
	          }
	        }
	      }
	    });
	    top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onMessage', onSliderMessage);
	  }
	  getContentContainer() {
	    return this.contentContainer;
	  }
	  getToolbarButtons() {
	    const buttons = [];
	    if (this.settingsButton) {
	      buttons.push(this.settingsButton);
	    }
	    return buttons;
	  }
	  loadSliderContent() {
	    return BX.ajax.runAction('main.api.mail.sender.getSenderTransitionalData', {
	      data: {
	        senderId: this.senderId
	      }
	    }).then(response => {
	      var _data$senders;
	      const data = response.data;
	      const senders = (_data$senders = data.senders) != null ? _data$senders : null;
	      this.id = Number(data.id);
	      this.email = data.email;
	      babelHelpers.classPrivateFieldLooseBase(this, _addSenders)[_addSenders](senders);
	      const type = data.type || null;
	      switch (type) {
	        case mailboxType:
	          this.settingsButton.bindEvent('click', () => {
	            babelHelpers.classPrivateFieldLooseBase(this, _openMailboxSettings)[_openMailboxSettings](data.href);
	          });
	          break;
	        case senderType$1:
	          this.settingsButton.bindEvent('click', () => {
	            babelHelpers.classPrivateFieldLooseBase(this, _openSmtpSettings)[_openSmtpSettings](data.id);
	          });
	          break;
	        default:
	          this.settingsButton.setDisabled();
	          break;
	      }
	      return this.getContentContainer();
	    }).catch(() => {
	      this.settingsButton.setDisabled();
	      return this.getContentContainer();
	    });
	  }
	  updateMainSenderName(mailboxId) {
	    return BX.ajax.runAction('main.api.mail.sender.getSenderByMailboxId', {
	      data: {
	        mailboxId
	      }
	    }).then(response => {
	      var _response$data;
	      const name = (_response$data = response.data) == null ? void 0 : _response$data.name;
	      if (!name || !this.mainSenderNameNode) {
	        return;
	      }
	      this.mainSenderNameNode.innerText = name;
	    }).catch(() => {});
	  }
	}
	function _createContentContainer2$1() {
	  this.senderList = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="mail-sender-list"></div>
		`));
	  babelHelpers.classPrivateFieldLooseBase(this, _createAddSenderContainer)[_createAddSenderContainer]();
	  this.contentContainer = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="ui-form">
				<div class="ui-slider-section">
					<div class="ui-slider-content-box" style="margin-bottom: 0">
						<div class="ui-slider-heading-4 sender-list-header">${0}</div>
						${0}
						${0}
					</div>
				</div>
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('UI_MAIL_ALIAS_SLIDER_EMAIL_TITLE')), this.senderList, this.addSenderContainer);
	}
	function _createAddSenderContainer2() {
	  this.senderInput = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
			<input type="text" class="ui-ctl-element" data-name="aliasName" placeholder="${0}">
		`), main_core.Text.encode(main_core.Loc.getMessage('UI_MAIL_ALIAS_SLIDER_ADD_INPUT_PLACEHOLDER')));
	  this.senderInputContainer = main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
			<div class="add-sender-input-container" hidden>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-default-light ui-ctl-sm ui-ctl-w100">
					${0}
				</div>
			</div>
		`), this.senderInput);
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSubmitButton)[_renderSubmitButton](() => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _addAliasPromise)[_addAliasPromise]();
	  }, this.senderInput), this.senderInputContainer);
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderCancelButton)[_renderCancelButton](() => {
	    main_core.Dom.hide(this.senderInputContainer);
	    main_core.Dom.show(this.senderAddButton);
	    this.senderInput.value = null;
	  }), this.senderInputContainer);
	  this.senderAddButton = main_core.Tag.render(_t5$1 || (_t5$1 = _$1`
			<div class="add-sender-button">${0}</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('UI_MAIL_ALIAS_SLIDER_ADD_BUTTON')));
	  main_core.Event.bind(this.senderAddButton, 'click', () => {
	    main_core.Dom.hide(this.senderAddButton);
	    main_core.Dom.show(this.senderInputContainer);
	    this.senderInput.focus();
	  });
	  this.addSenderContainer = main_core.Tag.render(_t6$1 || (_t6$1 = _$1`
			<div class="add-sender-container">
				${0}
				${0}
			</div>
		`), this.senderInputContainer, this.senderAddButton);
	}
	function _addAliasPromise2() {
	  return new Promise(resolve => {
	    const hideInputContainer = () => {
	      main_core.Dom.hide(this.senderInputContainer);
	      main_core.Dom.show(this.senderAddButton);
	      this.senderInput.value = null;
	      resolve();
	    };
	    if (this.senderInput.value.trim().length === 0) {
	      hideInputContainer();
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasNameInvalidCharacters)[_hasNameInvalidCharacters](this.senderInput.value.trim())) {
	      resolve();
	      return;
	    }
	    const name = this.senderInput.value;
	    main_core.ajax.runAction('main.api.mail.sender.addAlias', {
	      data: {
	        name,
	        email: this.email
	      }
	    }).then(response => {
	      var _data$userUrl, _data$avatar;
	      const data = response.data;
	      const newSenderId = data.senderId;
	      if (this.setSender && data.senderId) {
	        this.setSender(data.senderId, name, this.email);
	      }
	      this.wasSenderUpdated = true;
	      this.senderId = newSenderId;
	      const senderNode = babelHelpers.classPrivateFieldLooseBase(this, _renderSenderItem)[_renderSenderItem]({
	        id: newSenderId,
	        name,
	        isOwner: true,
	        type: aliasType,
	        canEdit: true,
	        userUrl: (_data$userUrl = data.userUrl) != null ? _data$userUrl : null,
	        avatar: (_data$avatar = data.avatar) != null ? _data$avatar : null
	      });
	      main_core.Dom.append(senderNode, this.senderList);
	      this.aliasCounter++;
	      hideInputContainer();
	    }).catch(() => {
	      hideInputContainer();
	    });
	  });
	}
	function _createToolbarButtons2() {
	  this.settingsButton = new ui_buttons.Button({
	    text: main_core.Loc.getMessage('UI_MAIL_ALIAS_SLIDER_MAIL_SETTINGS_BUTTON'),
	    icon: ui_buttons.Button.Icon.SETTING,
	    color: ui_buttons.Button.Color.LIGHT_BORDER
	  });
	}
	function _renderSenderItem2(sender) {
	  const itemContainer = main_core.Tag.render(_t7$1 || (_t7$1 = _$1`<div class="sender-list-item"></div>`));
	  const {
	    root: nameContainer,
	    textNode: nameTextContainer
	  } = babelHelpers.classPrivateFieldLooseBase(this, _renderSenderNameContainer)[_renderSenderNameContainer](sender.name);
	  let handleShowEditInput = null;
	  if (sender.canEdit) {
	    const {
	      nameEditContainer,
	      editInput: nameEditInput
	    } = babelHelpers.classPrivateFieldLooseBase(this, _renderSenderEditNode)[_renderSenderEditNode](sender, nameTextContainer);
	    main_core.Dom.append(nameEditContainer, nameContainer);
	    handleShowEditInput = () => {
	      nameEditInput.value = nameContainer.innerText;
	      main_core.Dom.hide(nameTextContainer);
	      main_core.Dom.show(nameEditContainer);
	      nameEditInput.focus();
	    };
	    main_core.Event.bind(nameTextContainer, 'click', handleShowEditInput);
	  }
	  main_core.Dom.append(nameContainer, itemContainer);
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSenderExtraInfoContainer)[_renderSenderExtraInfoContainer](sender), itemContainer);
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSenderAuthorContainer)[_renderSenderAuthorContainer](sender, itemContainer), itemContainer);
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSenderEditContainer)[_renderSenderEditContainer](sender, itemContainer, handleShowEditInput), itemContainer);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isMainSender)[_isMainSender](sender)) {
	    this.mainSenderNameNode = nameContainer.querySelector('.sender-item-name-text-container');
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _senderNameNodes)[_senderNameNodes].set(sender.id, nameTextContainer);
	  return itemContainer;
	}
	function _renderSenderNameContainer2(senderName) {
	  const {
	    root,
	    textNode
	  } = main_core.Tag.render(_t8$1 || (_t8$1 = _$1`
			<div class="sender-item-name-container">
				<div class="sender-item-name-text-container" ref="textNode">
					${0}
				</div>
			</div>
		`), main_core.Text.encode(senderName));
	  return {
	    root,
	    textNode
	  };
	}
	function _renderSenderEditNode2(sender, nameTextContainer) {
	  const textContainer = nameTextContainer;
	  const {
	    root,
	    editInput
	  } = main_core.Tag.render(_t9$1 || (_t9$1 = _$1`
			<div class="edit-sender-container-content" ref="editContent">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-default-light ui-ctl-sm ui-ctl-w100">
					<input type="text" class="ui-ctl-element" ref="editInput" placeholder="${0}">
				</div>
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_ALIAS_SLIDER_ADD_INPUT_PLACEHOLDER'));
	  const nameEditContainer = root;
	  const submitPromise = () => {
	    return new Promise(resolve => {
	      const hideEditContainer = () => {
	        editInput.value = nameTextContainer.innerText;
	        main_core.Dom.hide(nameEditContainer);
	        main_core.Dom.show(textContainer);
	        resolve();
	      };
	      if (editInput.value.length === 0 || editInput.value === nameTextContainer.innerText) {
	        hideEditContainer();
	        return;
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _hasNameInvalidCharacters)[_hasNameInvalidCharacters](editInput.value)) {
	        resolve();
	        return;
	      }
	      const senderNewName = editInput.value;
	      main_core.ajax.runAction('main.api.mail.sender.updateSenderName', {
	        data: {
	          senderId: sender.id,
	          name: senderNewName
	        }
	      }).then(() => {
	        textContainer.innerText = senderNewName;
	        if (this.setSender) {
	          this.setSender(sender.id, senderNewName, this.email);
	        }
	        this.wasSenderUpdated = true;
	        hideEditContainer();
	      }).catch(() => {
	        hideEditContainer();
	      });
	    });
	  };
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSubmitButton)[_renderSubmitButton](submitPromise, editInput), root);
	  const cancelHandler = () => {
	    main_core.Dom.hide(nameEditContainer);
	    main_core.Dom.show(textContainer);
	    editInput.value = null;
	  };
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderCancelButton)[_renderCancelButton](cancelHandler), root);
	  main_core.Dom.hide(root);
	  return {
	    nameEditContainer,
	    editInput
	  };
	}
	function _renderSenderExtraInfoContainer2(sender) {
	  return main_core.Tag.render(_t10$1 || (_t10$1 = _$1`
			<div class="sender-item-type-container">${0}</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _getExtraInfoText)[_getExtraInfoText](sender)));
	}
	function _getExtraInfoText2(sender) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isMainSender)[_isMainSender](sender)) {
	    return main_core.Loc.getMessage('UI_MAIL_ALIAS_EDITOR_CURRENT_SENDER_NAME');
	  }
	  if ([senderType$1, mailboxSenderType].includes(sender.type)) {
	    return main_core.Loc.getMessage('UI_MAIL_ALIAS_EDITOR_ANOTHER_SENDER_NAME');
	  }
	  if (sender.type === aliasType && sender.isOwner) {
	    return main_core.Loc.getMessage('UI_MAIL_ALIAS_EDITOR_ADDITIONAL_SENDER_NAME');
	  }
	  return '';
	}
	function _renderSenderEditContainer2(sender, senderNode, handleShowInput) {
	  const senderEditContainer = main_core.Tag.render(_t11$1 || (_t11$1 = _$1`
			<div class="sender-item-edit-container"></div>
		`));
	  if (!sender.canEdit && !sender.isOwner) {
	    return senderEditContainer;
	  }
	  const senderNameEditButton = main_core.Tag.render(_t12 || (_t12 = _$1`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --pencil-50"></div>
		`));
	  main_core.Dom.append(senderNameEditButton, senderEditContainer);
	  if (handleShowInput) {
	    main_core.Event.bind(senderNameEditButton, 'click', handleShowInput);
	  }
	  if (sender.type === aliasType) {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderDeleteButton)[_renderDeleteButton](sender.id, senderNode), senderEditContainer);
	    return senderEditContainer;
	  }
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSettingsButton)[_renderSettingsButton](sender.type, sender.id, sender.editHref), senderEditContainer);
	  return senderEditContainer;
	}
	function _renderSenderAuthorContainer2(sender, senderNode) {
	  const authorEditContainer = main_core.Tag.render(_t13 || (_t13 = _$1`
			<div class="sender-item-author-container"></div>
		`));
	  if (sender.userUrl) {
	    var _sender$avatar;
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderUserInfoNode)[_renderUserInfoNode](sender.userUrl, (_sender$avatar = sender.avatar) != null ? _sender$avatar : null), authorEditContainer);
	  }
	  return authorEditContainer;
	}
	function _renderUserInfoNode2(userUrl, avatar) {
	  const {
	    root,
	    userAvatarContainer
	  } = main_core.Tag.render(_t14 || (_t14 = _$1`
			<div class="sender-item-owner-info">
				${0}
				<a href="${0}" class="ui-icon ui-icon-common-user sender-item-owner-avatar" ref="userAvatarContainer"></a> 
			</div>
		`), main_core.Loc.getMessage('UI_MAIL_ALIAS_EDITOR_ANOTHER_USER_SENDER_NAME'), main_core.Text.encode(userUrl));
	  let avatarIcon = '';
	  if (avatar) {
	    avatarIcon = main_core.Tag.render(_t15 || (_t15 = _$1`<i style="background-image: url('${0}')"></i>`), main_core.Text.encode(avatar));
	  } else {
	    avatarIcon = main_core.Tag.render(_t16 || (_t16 = _$1`<div class="sender-item-owner-avatar-icon ui-icon-set --person"></div>`));
	  }
	  main_core.Dom.append(avatarIcon, userAvatarContainer);
	  return root;
	}
	function _renderDeleteButton2(senderId, senderNode) {
	  const deleteButton = main_core.Tag.render(_t17 || (_t17 = _$1`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --trash-bin" style="margin: 0"></div>
		`));
	  main_core.Event.bind(deleteButton, 'click', () => {
	    main_core.Dom.removeClass(deleteButton, ['ui-icon-set', '--trash-bin']);
	    main_core.Dom.addClass(deleteButton, ['ui-btn-light', 'ui ui-btn-wait']);
	    main_core.ajax.runAction('main.api.mail.sender.deleteSender', {
	      data: {
	        senderId
	      }
	    }).then(() => {
	      senderNode.remove();
	      this.wasSenderUpdated = true;
	      if (Number(senderId) === this.senderId) {
	        this.setSender();
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _senderNameNodes)[_senderNameNodes].delete(senderId);
	      this.aliasCounter--;
	      babelHelpers.classPrivateFieldLooseBase(this, _checkAliasCounter)[_checkAliasCounter]();
	    }).catch(() => {
	      main_core.Dom.removeClass(deleteButton, 'ui-btn-wait');
	    });
	  });
	  return deleteButton;
	}
	function _renderSettingsButton2(type, senderId, editHref) {
	  const editButton = main_core.Tag.render(_t18 || (_t18 = _$1`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --settings-1" style="margin: 0"></div>
		`));
	  if (type === mailboxSenderType) {
	    main_core.Event.bind(editButton, 'click', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _openMailboxSettings)[_openMailboxSettings](editHref);
	    });
	    return editButton;
	  }
	  main_core.Event.bind(editButton, 'click', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _openSmtpSettings)[_openSmtpSettings](senderId);
	  });
	  return editButton;
	}
	function _renderSubmitButton2(submitPromise, targetElement) {
	  const submitButton = main_core.Tag.render(_t19 || (_t19 = _$1`
			<div class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-done" style="margin: 0"></div>
		`));
	  main_core.Event.bind(submitButton, 'click', () => {
	    main_core.Dom.addClass(submitButton, 'ui ui-btn-wait');
	    submitPromise().then(() => {
	      main_core.Dom.removeClass(submitButton, 'ui-btn-wait');
	    }).catch(() => {});
	  });
	  main_core.Event.bind(targetElement, 'keypress', event => {
	    if (event.key === 'Enter') {
	      submitButton.click();
	    }
	  });
	  return submitButton;
	}
	function _renderCancelButton2(cancelHandler) {
	  const cancelButton = main_core.Tag.render(_t20 || (_t20 = _$1`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --cross-45" style="margin: 0"></div>
		`));
	  main_core.Event.bind(cancelButton, 'click', cancelHandler);
	  return cancelButton;
	}
	function _addSenders2(senders) {
	  if (!senders) {
	    return;
	  }
	  senders.sort((a, b) => a.id - b.id);
	  senders.forEach(sender => {
	    if (!this.id) {
	      if (sender.type === senderType$1) {
	        this.id = sender.id;
	      }
	      if (sender.type === mailboxSenderType) {
	        this.id = sender.mailboxId;
	      }
	    }
	    const senderNode = babelHelpers.classPrivateFieldLooseBase(this, _renderSenderItem)[_renderSenderItem](sender);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isMainSender)[_isMainSender](sender)) {
	      main_core.Dom.prepend(senderNode, this.senderList);
	    } else {
	      main_core.Dom.append(senderNode, this.senderList);
	    }
	    this.aliasCounter++;
	  });
	}
	function _openSmtpSettings2(senderId) {
	  SmtpEditor.openSlider({
	    senderId: Number(senderId),
	    setSenderCallback: (id, name, email) => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _senderNameNodes)[_senderNameNodes].has(id)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _senderNameNodes)[_senderNameNodes].get(id).innerText = name;
	      }
	      this.setSender(id, name, email);
	      this.wasSenderUpdated = true;
	    }
	  });
	}
	function _openMailboxSettings2(href) {
	  BX.SidePanel.Instance.open(href);
	}
	function _hasNameInvalidCharacters2(name) {
	  // regex checks for characters other than letters of the alphabet, numbers, spaces
	  // and special characters ("-", ".", "'", "(", ")", ",")
	  const regexForInvalidCharacters = /[^\p{L}\p{N}\p{Zs}\-.'(),]+/ug;
	  if (regexForInvalidCharacters.test(name)) {
	    top.BX.UI.Notification.Center.notify({
	      content: main_core.Text.encode(main_core.Loc.getMessage('UI_MAIL_ALIAS_EDITOR_INVALID_SYMBOLS_NOTIFICATION'))
	    });
	    return true;
	  }
	  return false;
	}
	function _checkAliasCounter2() {
	  if (this.aliasCounter === 0) {
	    const slider = BX.SidePanel.Instance.getSlider(aliasSliderUrl);
	    if (slider) {
	      slider.close();
	    }
	  }
	}
	function _isMainSender2(sender) {
	  return sender.type === senderType$1 && this.id === Number(sender.id) || sender.type === mailboxSenderType && this.id === Number(sender.mailboxId);
	}

	exports.AliasEditor = AliasEditor;
	exports.SmtpEditor = SmtpEditor;

}((this.BX.UI.Mail = this.BX.UI.Mail || {}),BX,BX.UI.SidePanel,BX,BX.UI,BX.UI,BX,BX.UI));
//# sourceMappingURL=sender-editor.bundle.js.map
