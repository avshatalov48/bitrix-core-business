this.BX = this.BX || {};
this.BX.Mail = this.BX.Mail || {};
(function (exports,main_core,mail_sidepanelwrapper,ui_dialogs_messagebox) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t<div data-role=\"name-container\" class=\"mail-addressbook-dialogeditcontact-item\">\n\t\t\t\t\t<label class=\"mail-addressbook-dialogeditcontact-lable\">", "\n\t\t\t\t\t\t<div id=\"mail-addressbook-dialogeditcontact-contact-email-container\" class=\"ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field\">\n\t\t\t\t\t\t\t<input data-role = \"input\" value=\"", "\" class=\"ui-ctl-element\" placeholder=\"\">\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</label>\n\t\t\t\t\t<div data-role = \"error-title\" class=\"mail-addressbook-dialogeditcontact-contact-error\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div data-role=\"email-container\" class=\"mail-addressbook-dialogeditcontact-item\">\n\t\t\t\t\t<label class=\"mail-addressbook-dialogeditcontact-lable\">", "\n\t\t\t\t\t\t<div id=\"mail-addressbook-dialogeditcontact-contact-email-container\" class=\"ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field\">\n\t\t\t\t\t\t\t<input data-role = \"input\" value=\"", "\" class=\"ui-ctl-element\" placeholder=\"info@example.com\">\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</label>\n\t\t\t\t\t<div data-role = \"error-title\" class=\"mail-addressbook-dialogeditcontact-contact-error\">", "</div>\t\t\t\n\t\t\t\t</div>\n\t\t\t</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DialogEditContact = /*#__PURE__*/function () {
	  function DialogEditContact() {
	    babelHelpers.classCallCheck(this, DialogEditContact);
	  }

	  babelHelpers.createClass(DialogEditContact, null, [{
	    key: "getCheckedFields",
	    value: function getCheckedFields(contentElement) {
	      var emailItem = contentElement.querySelector('[data-role="email-container"]');
	      var emailInput = emailItem.querySelector('[data-role="input"]');
	      var email = emailInput.value;
	      var nameItem = contentElement.querySelector('[data-role="name-container"]');
	      var nameInput = nameItem.querySelector('[data-role="input"]');
	      var name = nameInput.value;
	      var fieldsAreFilledCorrectly = true;
	      var checkedFields = [];

	      if (!main_core.Validation.isEmail(email)) {
	        fieldsAreFilledCorrectly = false;
	        emailItem.showError();
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
	      var contactData = {
	        NAME: name,
	        EMAIL: email
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
	      var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this;
	      var errorTitle = item.querySelector('[data-role="error-title"]');
	      errorTitle.style.display = 'block';
	    }
	  }, {
	    key: "hideError",
	    value: function hideError() {
	      var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this;
	      var errorTitle = item.querySelector('[data-role="error-title"]');
	      errorTitle.style.display = 'none';
	    }
	  }, {
	    key: "openDialog",
	    value: function openDialog(titleText) {
	      var _this = this;

	      var contactConfig = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var currentEmail = "";
	      var currentName = "";

	      if (contactConfig['contactData'] !== undefined) {
	        currentName = main_core.Text.encode(contactConfig['contactData']['name']);
	        currentEmail = main_core.Text.encode(contactConfig['contactData']['email']);
	      }

	      var content = main_core.Tag.render(_templateObject(), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_NAME_TITLE"), currentName, main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_TITLE"), currentEmail, main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR"));
	      var emailItem = content.querySelector('[data-role="email-container"]');
	      emailItem.showError = this.showError;
	      emailItem.hideError = this.hideError;
	      var nameItem = content.querySelector('[data-role="name-container"]');
	      nameItem.showError = this.showError;
	      nameItem.hideError = this.hideError;
	      var emailInput = emailItem.querySelector('[data-role="input"]');

	      emailInput.oninput = function () {
	        return emailItem.hideError();
	      };

	      var nameInput = nameItem.querySelector('[data-role="input"]');

	      nameInput.oninput = function () {
	        return nameItem.hideError();
	      };

	      mail_sidepanelwrapper.SidePanelWrapper.open({
	        id: 'dialogEditContact',
	        titleText: titleText,
	        footerIsActive: true,
	        content: content,
	        cancelButton: {
	          text: main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_BUTTON_CANCEL")
	        },
	        consentButton: {
	          text: main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_BUTTON_SAVE"),
	          function: function _function(eventObject) {
	            var checkedFields = _this.getCheckedFields(content);

	            if (checkedFields) {
	              eventObject.setClocking(true);

	              _this.saveContact(checkedFields['name'], checkedFields['email'], contactConfig['contactID']).then(function () {
	                BX.SidePanel.Instance.close();
	                BX.SidePanel.Instance.postMessageAll('mail:side-panel', 'dialogEditContact::reloadList', {});
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

}((this.BX.Mail.AddressBook = this.BX.Mail.AddressBook || {}),BX,BX.Mail,BX.UI.Dialogs));
//# sourceMappingURL=dialogeditcontact.bundle.js.map
