this.BX = this.BX || {};
this.BX.Mail = this.BX.Mail || {};
(function (exports,main_core,mail_sidepanelwrapper,ui_dialogs_messagebox,mail_avatar,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var DialogEditContact = /*#__PURE__*/function () {
	  function DialogEditContact() {
	    babelHelpers.classCallCheck(this, DialogEditContact);
	  }
	  babelHelpers.createClass(DialogEditContact, null, [{
	    key: "getCheckedFields",
	    value: function getCheckedFields(contentElement) {
	      var emailContainer = contentElement.querySelector('[data-role="email-container"]');
	      var emailInput = emailContainer.querySelector('[data-role="input-field"]');
	      var email = emailInput.value;
	      var nameItem = contentElement.querySelector('[data-role="name-container"]');
	      var nameInput = nameItem.querySelector('[data-role="input-field"]');
	      var name = nameInput.value;
	      var fieldsAreFilledCorrectly = true;
	      if (!main_core.Validation.isEmail(email)) {
	        fieldsAreFilledCorrectly = false;
	        emailContainer.showError(0);
	      } else if (name.length < 1) {
	        name = email.split('@')[0];
	      }
	      var checkedFields = {
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
	    value: function saveContact(name, email) {
	      var id = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'new';
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
	    key: "showErrorEmailAlreadyExists",
	    value: function showErrorEmailAlreadyExists(responseError, errorAlreadyExistLink, emailContainer) {
	      var _this = this;
	      var contact = this.getLastOfMatchingContactFromError(responseError);
	      var contactID = contact.contactID,
	        contactData = contact.contactData;
	      if (contact !== null) {
	        errorAlreadyExistLink.onclick = function () {
	          _this.openEditDialog({
	            contactID: contactID,
	            contactData: contactData
	          });
	        };
	        emailContainer.showError(1);
	        return true;
	      }
	      return false;
	    }
	  }, {
	    key: "openDialog",
	    value: function openDialog(titleText, contactConfig) {
	      var _this2 = this;
	      var _contactConfig$contac = contactConfig.contactID,
	        contactID = _contactConfig$contac === void 0 ? 'new' : _contactConfig$contac,
	        _contactConfig$showEm = contactConfig.showEmailError,
	        showEmailError = _contactConfig$showEm === void 0 ? false : _contactConfig$showEm,
	        prefixId = contactConfig.prefixId,
	        contactData = contactConfig.contactData,
	        responseError = contactConfig.responseError;
	      var sliderId = 'dialogEditContact_' + contactID;
	      if (prefixId !== undefined) {
	        sliderId += '_' + prefixId;
	      }
	      var currentEmail = '';
	      var currentName = '';
	      var disablingEmailInputClass = '';
	      var disablingEmailInputAttribute = '';
	      if (contactData !== undefined) {
	        currentName = contactData.name;
	        currentEmail = contactData.email;
	        if (contactID !== 'new') {
	          disablingEmailInputClass = 'ui-ctl-disabled';
	          disablingEmailInputAttribute = 'disabled';
	        }
	      }
	      var emailInput = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<input data-role=\"input-field\" type=\"text\" class=\"ui-ctl-element\" value=\"\" placeholder=\"info@example.com\"  ", ">"])), disablingEmailInputAttribute);
	      var emailInputWrapper = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ", "\">\n\t\t\t", "\n\t\t</div>"])), disablingEmailInputClass, emailInput);
	      emailInput.value = currentEmail;
	      var errorTitleEmailIsIncorrect = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-alert ui-alert-danger mail-addressbook-error-box\">\n\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR"));
	      var errorTitleEmailIsAlreadyExists = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-alert ui-alert-danger mail-addressbook-error-box\">\n\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t<br>\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR_EMAIL_IS_ALREADY_EXISTS"));
	      var errorAlreadyExistLink = errorTitleEmailIsAlreadyExists.querySelector('[data-role="contact-email"]');
	      var emailContainer = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div data-role=\"email-container\" class=\"mail-addressbook-dialogeditcontact-item\">\n\t\t\t<label class=\"mail-addressbook-dialogeditcontact-lable\">", "\n\t\t\t\t<div id=\"mail-addressbook-dialogeditcontact-contact-email-container\" class=\"ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</label>\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_TITLE"), emailInputWrapper, errorTitleEmailIsIncorrect, errorTitleEmailIsAlreadyExists);
	      var nameInput = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<input data-role=\"input-field\" type=\"text\" class=\"ui-ctl-element\" value=\"\" placeholder=\"\">"])));
	      var nameInputWrapper = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t", "\n\t\t</div>"])), nameInput);
	      nameInput.value = currentName;
	      var nameItem = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div data-role=\"name-container\" class=\"mail-addressbook-dialogeditcontact-item\">\n\t\t\t<label class=\"mail-addressbook-dialogeditcontact-lable\">", "\n\t\t\t\t<div id=\"mail-addressbook-dialogeditcontact-contact-email-container\" class=\"ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</label>\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_NAME_TITLE"), nameInputWrapper);
	      var content = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t<div>\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"])), nameItem, emailContainer);
	      emailContainer.errorTitle = [errorTitleEmailIsIncorrect, errorTitleEmailIsAlreadyExists];
	      emailContainer.emailInputWrapper = emailInputWrapper;
	      emailContainer.showError = this.showError;
	      emailContainer.hideError = this.hideError;
	      emailContainer.hideError();
	      if (showEmailError === true) {
	        emailContainer.showError();
	      }
	      emailInput.oninput = function () {
	        return emailContainer.hideError();
	      };
	      if (responseError !== undefined) {
	        this.showErrorEmailAlreadyExists(responseError, errorAlreadyExistLink, emailContainer);
	      }
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
	            var checkedFields = _this2.getCheckedFields(content);
	            if (checkedFields) {
	              eventObject.setClocking(true);
	              _this2.saveContact(checkedFields['name'], checkedFields['email'], contactConfig['contactID']).then(function (response) {
	                main_core_events.EventEmitter.emit('BX.DialogEditContact:onSaveContact', new main_core_events.BaseEvent({
	                  data: {
	                    items: response.data,
	                    prefixId: contactConfig.prefixId
	                  }
	                }));
	                BX.SidePanel.Instance.postMessageAll(sliderId, 'dialogEditContact::reloadList', {});
	                BX.SidePanel.Instance.close();
	              })["catch"](function (response) {
	                if (_this2.showErrorEmailAlreadyExists(response, errorAlreadyExistLink, emailContainer)) {
	                  eventObject.setClocking(false);
	                } else {
	                  BX.SidePanel.Instance.postMessageAll(sliderId, 'dialogEditContact::reloadList', {});
	                  BX.SidePanel.Instance.close();
	                }
	              });
	            }
	          }
	        }
	      });
	      return contactID;
	    }
	  }, {
	    key: "openCreateDialog",
	    value: function openCreateDialog() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var responseError = config.responseError,
	        prefixId = config.prefixId;
	      if (responseError !== undefined) {
	        var contact = this.getLastOfMatchingContactFromError(responseError);
	        return this.openEditDialog(_objectSpread({
	          prefixId: prefixId
	        }, contact));
	      }
	      return this.openDialog(main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_ADD_MSGVER_1"), config);
	    }
	  }, {
	    key: "getLastOfMatchingContactFromError",
	    value: function getLastOfMatchingContactFromError(responseError) {
	      var lastMessage = null;
	      var _iterator = _createForOfIteratorHelper(responseError.errors),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var error = _step.value;
	          if (error.code === 'ALL_CONTACTS_ALREADY_ADDED') {
	            lastMessage = error.customData.lastFound[0];
	            break;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      if (lastMessage !== null) {
	        return {
	          contactID: Number(lastMessage['ID']),
	          contactData: {
	            name: lastMessage['NAME'],
	            email: lastMessage['EMAIL']
	          }
	        };
	      }
	      return null;
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
	      return this.openDialog(main_core.Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_EDIT_MSGVER_1"), config);
	    }
	  }]);
	  return DialogEditContact;
	}();

	exports.DialogEditContact = DialogEditContact;

}((this.BX.Mail.AddressBook = this.BX.Mail.AddressBook || {}),BX,BX.Mail,BX.UI.Dialogs,BX.Mail,BX.Event));
//# sourceMappingURL=dialogeditcontact.bundle.js.map
