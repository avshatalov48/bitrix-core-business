this.BX = this.BX || {};
this.BX.Mail = this.BX.Mail || {};
(function (exports,main_core,mail_sidepanelwrapper,ui_dialogs_messagebox,mail_avatar) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	var DialogEditContact = /*#__PURE__*/function () {
	  function DialogEditContact() {
	    babelHelpers.classCallCheck(this, DialogEditContact);
	  }

	  babelHelpers.createClass(DialogEditContact, null, [{
	    key: "getCheckedFields",
	    value: function getCheckedFields(contentElement) {
	      var emailItem = contentElement.querySelector('[data-role="email-container"]');
	      var emailInput = emailItem.querySelector('[data-role="input-field"]');
	      var email = emailInput.value;
	      var nameItem = contentElement.querySelector('[data-role="name-container"]');
	      var nameInput = nameItem.querySelector('[data-role="input-field"]');
	      var name = nameInput.value;
	      var fieldsAreFilledCorrectly = true;
	      var checkedFields = [];

	      if (!main_core.Validation.isEmail(email)) {
	        fieldsAreFilledCorrectly = false;
	        emailItem.showError(0);
	      } else if (name.length < 1) {
	        name = email.split('@')[0];
	      }

	      checkedFields = {
	        name: name,
	        email: email
	      };

	      if (fieldsAreFilledCorrectly) {
	        return checkedFields;
	      }

	      return false;
	    }
	  }, {
	    key: "openRemoveDialog",
	    value: function openRemoveDialog() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        id: ''
	      };
	      var promiseRemoveContact = new BX.Promise();
	      var removeContact = this.removeContact;
	      var topSlider = BX.SidePanel.Instance.getTopSlider();
	      var messageBoxZIndex = 1;

	      if (topSlider != null) {
	        messageBoxZIndex += topSlider.getZindex();
	      }

	      var messageBox = new ui_dialogs_messagebox.MessageBox({
	        title: main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_REMOVE_DIALOG_TITLE"),
	        message: main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_REMOVE_DIALOG_MESSAGE"),
	        buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
	        popupOptions: {
	          zIndex: messageBoxZIndex
	        },
	        onOk: function onOk() {
	          removeContact(config['id']).then(function () {
	            return promiseRemoveContact.fulfill();
	          });
	          messageBox.close();
	        },
	        onCancel: function onCancel() {
	          promiseRemoveContact.reject();
	          messageBox.close();
	        }
	      });
	      messageBox.show();
	      return promiseRemoveContact;
	    }
	  }, {
	    key: "removeContact",
	    value: function removeContact(id) {
	      return BX.ajax.runAction('mail.addressbook.removecontacts', {
	        data: {
	          idSet: [id]
	        }
	      });
	    }
	  }, {
	    key: "saveContact",
	    value: function saveContact(name, email, id) {
	      var data = mail_avatar.Avatar.getAvatarData({
	        fullName: name,
	        email: email
	      });
	      var contactData = {
	        NAME: name,
	        EMAIL: email,
	        COLOR: data['color'],
	        INITIALS: data['abbreviation']
	      };

	      if (id !== undefined) {
	        contactData['ID'] = id;
	      }

	      return BX.ajax.runAction('mail.addressbook.savecontact', {
	        data: {
	          contactData: contactData
	        }
	      });
	    }
	  }, {
	    key: "showError",
	    value: function showError() {
	      var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	      this.emailInputWrapper.classList.add('ui-ctl-danger');
	      BX.show(this.errorTitle[id]);
	    }
	  }, {
	    key: "hideError",
	    value: function hideError() {
	      var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'all';
	      this.emailInputWrapper.classList.remove('ui-ctl-danger');

	      if (id === 'all') {
	        this.errorTitle.forEach(function (element) {
	          BX.hide(element);
	        });
	        return;
	      }

	      BX.hide(this.errorTitle[id]);
	    }
	  }, {
	    key: "openDialog",
	    value: function openDialog(titleText) {
	      var _this = this;

	      var contactConfig = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
	        contactID: 'new'
	      };
	      var sliderId = 'dialogEditContact_' + contactConfig['contactID'];
	      var currentEmail = '';
	      var currentName = '';
	      var disablingEmailInputClass = '';
	      var disablingEmailInputAttribute = '';

	      if (contactConfig['contactData'] !== undefined) {
	        currentName = contactConfig['contactData']['name'];
	        currentEmail = contactConfig['contactData']['email'];
	        disablingEmailInputClass = 'ui-ctl-disabled';
	        disablingEmailInputAttribute = 'disabled';
	      }

	      var emailInput = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<input data-role=\"input-field\" type=\"text\" class=\"ui-ctl-element\" value=\"\" placeholder=\"info@example.com\"  ", ">"])), disablingEmailInputAttribute);
	      var emailInputWrapper = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ", "\">\n\t\t\t", "\n\t\t</div>"])), disablingEmailInputClass, emailInput);
	      emailInput.value = currentEmail;
	      var errorTitleEmailIsIncorrect = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-alert ui-alert-danger mail-addressbook-error-box\">\n\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR"));
	      var errorTitleEmailIsAlreadyExists = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-alert ui-alert-danger mail-addressbook-error-box\">\n\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t<br>\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR_EMAIL_IS_ALREADY_EXISTS"));
	      var openEditSliderBtn = errorTitleEmailIsAlreadyExists.querySelector('[data-role="contact-email"]');
	      var emailItem = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div data-role=\"email-container\" class=\"mail-addressbook-dialogeditcontact-item\">\n\t\t\t<label class=\"mail-addressbook-dialogeditcontact-lable\">", "\n\t\t\t\t<div id=\"mail-addressbook-dialogeditcontact-contact-email-container\" class=\"ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</label>\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_TITLE"), emailInputWrapper, errorTitleEmailIsIncorrect, errorTitleEmailIsAlreadyExists);
	      var nameInput = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<input data-role=\"input-field\" type=\"text\" class=\"ui-ctl-element\" value=\"\" placeholder=\"\">"])));
	      var nameInputWrapper = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t", "\n\t\t</div>"])), nameInput);
	      nameInput.value = currentName;
	      var nameItem = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div data-role=\"name-container\" class=\"mail-addressbook-dialogeditcontact-item\">\n\t\t\t<label class=\"mail-addressbook-dialogeditcontact-lable\">", "\n\t\t\t\t<div id=\"mail-addressbook-dialogeditcontact-contact-email-container\" class=\"ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</label>\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_NAME_TITLE"), nameInputWrapper);
	      var content = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t<div>\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"])), nameItem, emailItem);
	      emailItem.errorTitle = [errorTitleEmailIsIncorrect, errorTitleEmailIsAlreadyExists];
	      emailItem.emailInputWrapper = emailInputWrapper;
	      emailItem.showError = this.showError;
	      emailItem.hideError = this.hideError;
	      emailItem.hideError();

	      emailInput.oninput = function () {
	        return emailItem.hideError();
	      };

	      mail_sidepanelwrapper.SidePanelWrapper.open({
	        id: sliderId,
	        titleText: titleText,
	        footerIsActive: true,
	        content: content,
	        cancelButton: {
	          text: main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_BUTTON_CANCEL")
	        },
	        consentButton: {
	          text: main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_BUTTON_SAVE"),
	          "function": function _function(eventObject) {
	            var checkedFields = _this.getCheckedFields(content);

	            if (checkedFields) {
	              eventObject.setClocking(true);

	              _this.saveContact(checkedFields['name'], checkedFields['email'], contactConfig['contactID']).then(function (response) {
	                BX.SidePanel.Instance.postMessageAll(sliderId, 'dialogEditContact::reloadList', {});
	                BX.SidePanel.Instance.close();
	              })["catch"](function (response) {
	                var message = response.errors.pop().message.pop();

	                if (message['ID']) {
	                  eventObject.setClocking(false);

	                  openEditSliderBtn.onclick = function () {
	                    _this.openEditDialog({
	                      contactID: Number(message['ID']),
	                      contactData: {
	                        name: message['NAME'],
	                        email: message['EMAIL']
	                      }
	                    });
	                  };

	                  emailItem.showError(1);
	                } else {
	                  BX.SidePanel.Instance.postMessageAll(sliderId, 'dialogEditContact::reloadList', {});
	                  BX.SidePanel.Instance.close();
	                }
	              });
	            }
	          }
	        }
	      });
	    }
	  }, {
	    key: "openCreateDialog",
	    value: function openCreateDialog(config) {
	      this.openDialog(main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_ADD"), config);
	    }
	  }, {
	    key: "openEditDialog",
	    value: function openEditDialog() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        contactID: '',
	        contactData: {
	          name: '',
	          email: ''
	        }
	      };
	      this.openDialog(main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_EDIT"), config);
	    }
	  }]);
	  return DialogEditContact;
	}();

	exports.DialogEditContact = DialogEditContact;

}((this.BX.Mail.AddressBook = this.BX.Mail.AddressBook || {}),BX,BX.Mail,BX.UI.Dialogs,BX.Mail));
//# sourceMappingURL=dialogeditcontact.bundle.js.map
