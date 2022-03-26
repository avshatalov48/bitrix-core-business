this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_popup,landing_ui_field_basefield,ui_draganddrop_draggable,landing_ui_field_radiobuttonfield,landing_ui_form_formsettingsform,crm_form_client,landing_ui_component_listitem,landing_ui_component_actionpanel,main_core_events,main_loader,landing_backend,landing_ui_panel_formsettingspanel) {
	'use strict';

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"landing-ui-field-agreements-list-actions-button\" onclick=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"landing-ui-field-agreements-list-actions-button\" onclick=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-agreements-list-actions-container\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-agreements-list-container\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	/**
	 * @memberOf BX.Landing.UI.Field
	 */
	var AgreementsList = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(AgreementsList, _BaseField);

	  function AgreementsList(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, AgreementsList);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AgreementsList).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.AgreementsList');

	    _this.onSelectAgreementClick = _this.onSelectAgreementClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onCreateAgreementClick = _this.onCreateAgreementClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onUserConsentEditSave = _this.onUserConsentEditSave.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onUserConsentEditCancel = _this.onUserConsentEditCancel.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onItemRemoveClick = _this.onItemRemoveClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragEnd = _this.onDragEnd.bind(babelHelpers.assertThisInitialized(_this));
	    _this.items = [];
	    main_core.Dom.replace(_this.input, _this.getListContainer());
	    main_core.Dom.append(_this.getActionsContainer(), _this.layout);
	    void _this.showAgreementLoader();
	    crm_form_client.FormClient.getInstance().prepareOptions(_this.options.formOptions, _this.options.value).then(function (result) {
	      return result.data.agreements.map(function (item, index) {
	        return main_core.Runtime.merge(item, _this.options.value[index]);
	      });
	    }).then(function (agreements) {
	      void _this.hideAgreementLoader();
	      agreements.forEach(function (agreement) {
	        _this.addItem(agreement);
	      });
	    });
	    _this.draggable = new ui_draganddrop_draggable.Draggable({
	      context: window.parent,
	      container: _this.getListContainer(),
	      draggable: '.landing-ui-component-list-item',
	      dragElement: '.landing-ui-button-icon-drag',
	      type: ui_draganddrop_draggable.Draggable.MOVE,
	      offset: {
	        y: -62
	      }
	    });

	    _this.draggable.subscribe('end', _this.onDragEnd);

	    var addCustomEvent = main_core.Reflection.getClass('top.BX.addCustomEvent');
	    addCustomEvent(window.top, 'main-user-consent-to-list', _this.onUserConsentEditCancel);
	    addCustomEvent(window.top, 'main-user-consent-saved', _this.onUserConsentEditSave);
	    return _this;
	  }

	  babelHelpers.createClass(AgreementsList, [{
	    key: "getAgreementsList",
	    value: function getAgreementsList() {
	      var _this2 = this;

	      return this.cache.remember('agreementsList', function () {
	        return _this2.options.agreementsList;
	      });
	    }
	  }, {
	    key: "setAgreementsList",
	    value: function setAgreementsList(agreements) {
	      this.cache.set('agreementsList', agreements);
	    }
	  }, {
	    key: "loadAgreementsList",
	    value: function loadAgreementsList() {
	      return landing_backend.Backend.getInstance().action('Form::getAgreements').then(function (agreements) {
	        return main_core.Runtime.orderBy(agreements, ['id'], ['asc']);
	      });
	    }
	  }, {
	    key: "getAgreementById",
	    value: function getAgreementById(id) {
	      return this.getAgreementsList().find(function (agreement) {
	        return String(id) === String(agreement.id);
	      });
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(itemOptions) {
	      var item = this.createItem(itemOptions);
	      item.appendTo(this.getListContainer());
	      this.items = this.items.filter(function (currentItem) {
	        return String(currentItem.options.id) !== String(item.options.id);
	      });
	      this.items.push(item);
	    }
	  }, {
	    key: "getListContainer",
	    value: function getListContainer() {
	      return this.cache.remember('listContainer', function () {
	        return main_core.Tag.render(_templateObject());
	      });
	    }
	  }, {
	    key: "getActionsContainer",
	    value: function getActionsContainer() {
	      var _this3 = this;

	      return this.cache.remember('actionsContainer', function () {
	        return main_core.Tag.render(_templateObject2(), _this3.getSelectAgreementButton(), _this3.getCreateAgreementButton());
	      });
	    }
	  }, {
	    key: "getSelectAgreementButton",
	    value: function getSelectAgreementButton() {
	      var _this4 = this;

	      return this.cache.remember('selectAgreementButton', function () {
	        return main_core.Tag.render(_templateObject3(), _this4.onSelectAgreementClick, main_core.Loc.getMessage('LANDING_AGREEMENT_LIST_SELECT_BUTTON_LABEL'));
	      });
	    }
	  }, {
	    key: "getCreateAgreementButton",
	    value: function getCreateAgreementButton() {
	      var _this5 = this;

	      return this.cache.remember('createAgreementButton', function () {
	        return main_core.Tag.render(_templateObject4(), _this5.onCreateAgreementClick, main_core.Loc.getMessage('LANDING_AGREEMENT_LIST_CREATE_BUTTON_LABEL'));
	      });
	    }
	  }, {
	    key: "getSelectedAgreements",
	    value: function getSelectedAgreements() {
	      return babelHelpers.toConsumableArray(this.getListContainer().children).map(function (item) {
	        return main_core.Dom.attr(item, 'data-value');
	      });
	    }
	  }, {
	    key: "getAgreementsMenu",
	    value: function getAgreementsMenu() {
	      var _this6 = this;

	      return this.cache.remember('agreementsMenu', function () {
	        var menu = new main_popup.Menu({
	          bindElement: _this6.getSelectAgreementButton(),
	          autoHide: true,
	          maxWidth: 400,
	          maxHeight: 205,
	          events: {
	            onPopupShow: function onPopupShow() {
	              setTimeout(function () {
	                main_core.Dom.style(menu.getMenuContainer(), {
	                  left: '0px',
	                  right: 'auto',
	                  top: '30px'
	                });
	              });
	            }
	          }
	        });

	        _this6.getAgreementsList().filter(function (agreement) {
	          return !_this6.items.some(function (item) {
	            return String(item.options.id) === String(agreement.id);
	          });
	        }).forEach(function (agreement) {
	          menu.addMenuItem({
	            id: agreement.id,
	            text: agreement.name,
	            onclick: _this6.onAgreementsMenuItemClick.bind(_this6, agreement)
	          });
	        });

	        main_core.Dom.append(menu.getMenuContainer(), _this6.getActionsContainer());
	        return menu;
	      });
	    }
	  }, {
	    key: "refreshAgreementsMenu",
	    value: function refreshAgreementsMenu() {
	      var agreementsMenu = this.getAgreementsMenu();
	      agreementsMenu.close();
	      agreementsMenu.destroy();
	      this.cache.delete('agreementsMenu');
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "createItemForm",
	    value: function createItemForm(agreement) {
	      var _this7 = this;

	      return new landing_ui_form_formsettingsform.FormSettingsForm({
	        id: agreement.id,
	        title: main_core.Loc.getMessage('LANDING_AGREEMENT_FORM_TITLE'),
	        onChange: function onChange() {
	          _this7.emit('onChange', {
	            skipPrepare: true
	          });
	        },
	        serializeModifier: function serializeModifier(value) {
	          if (value.type === 'type1') {
	            return {
	              checked: true,
	              required: true
	            };
	          }

	          if (value.type === 'type2') {
	            return {
	              checked: false,
	              required: true
	            };
	          }

	          if (value.type === 'type3') {
	            return {
	              checked: true,
	              required: false
	            };
	          }

	          if (value.type === 'type4') {
	            return {
	              checked: false,
	              required: false
	            };
	          }
	        },
	        fields: [new landing_ui_field_radiobuttonfield.RadioButtonField({
	          selector: 'type',
	          value: function () {
	            if (agreement.checked === true && agreement.required === true) {
	              return 'type1';
	            }

	            if (agreement.checked === false && agreement.required === true) {
	              return 'type2';
	            }

	            if (agreement.checked === true && agreement.required === false) {
	              return 'type3';
	            }

	            if (agreement.checked === false && agreement.required === false) {
	              return 'type4';
	            }
	          }(),
	          items: [{
	            id: 'type1',
	            title: main_core.Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_1'),
	            icon: 'landing-ui-agreement-type-1-icon'
	          }, {
	            id: 'type2',
	            title: main_core.Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_2'),
	            icon: 'landing-ui-agreement-type-2-icon'
	          }, {
	            id: 'type3',
	            title: main_core.Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_3'),
	            icon: 'landing-ui-agreement-type-3-icon'
	          }, {
	            id: 'type4',
	            title: main_core.Loc.getMessage('LANDING_AGREEMENT_FORM_TYPE_FIELD_ITEM_4'),
	            icon: 'landing-ui-agreement-type-4-icon'
	          }]
	        }), new landing_ui_component_actionpanel.ActionPanel({
	          left: [{
	            id: 'edit',
	            text: main_core.Loc.getMessage('LANDING_AGREEMENT_EDIT_BUTTON_LABEL'),
	            onClick: function onClick() {
	              return _this7.editAgreement(agreement);
	            }
	          }, {
	            id: 'list',
	            text: main_core.Loc.getMessage('LANDING_AGREEMENT_CONSENTS_BUTTON_LABEL'),
	            onClick: function onClick() {
	              return _this7.openConsentsList(agreement);
	            }
	          }]
	        })]
	      });
	    }
	  }, {
	    key: "getAgreementLoader",
	    value: function getAgreementLoader() {
	      return this.cache.remember('agreementLoader', function () {
	        return new main_loader.Loader({
	          size: 50,
	          mode: 'inline',
	          offset: {
	            top: '5px',
	            left: '225px'
	          }
	        });
	      });
	    }
	  }, {
	    key: "showAgreementLoader",
	    value: function showAgreementLoader() {
	      var loader = this.getAgreementLoader();
	      var container = this.getListContainer();
	      main_core.Dom.append(loader.layout, container);
	      return loader.show(container);
	    }
	  }, {
	    key: "hideAgreementLoader",
	    value: function hideAgreementLoader() {
	      var loader = this.getAgreementLoader();
	      main_core.Dom.remove(loader.layout);
	      return loader.hide();
	    }
	  }, {
	    key: "onAgreementsMenuItemClick",
	    value: function onAgreementsMenuItemClick(itemOptions) {
	      var _this8 = this;

	      void this.showAgreementLoader();
	      crm_form_client.FormClient.getInstance().prepareOptions(this.options.formOptions, {
	        agreements: [{
	          id: itemOptions.id
	        }]
	      }).then(function (result) {
	        void _this8.hideAgreementLoader();

	        _this8.addItem(result.data.agreements[0]);

	        _this8.emit('onChange', {
	          skipPrepare: true
	        });
	      });
	      this.refreshAgreementsMenu();
	    }
	  }, {
	    key: "onSelectAgreementClick",
	    value: function onSelectAgreementClick(event) {
	      event.preventDefault();
	      var menu = this.getAgreementsMenu();

	      if (!menu.getPopupWindow().isShown()) {
	        menu.show();
	      } else {
	        menu.close();
	      }
	    }
	  }, {
	    key: "onCreateAgreementClick",
	    value: function onCreateAgreementClick(event) {
	      event.preventDefault();
	      this.editAgreement({
	        id: 0
	      });
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onItemHeaderClick",
	    value: function onItemHeaderClick(agreement, event) {
	      event.preventDefault();
	      var parentElement = event.currentTarget.parentElement;
	      main_core.Dom.toggleClass(parentElement, 'landing-ui-field-agreements-list-item-active');
	    }
	  }, {
	    key: "createItem",
	    value: function createItem(options) {
	      var agreementListItem = this.getAgreementById(options.id);
	      return new landing_ui_component_listitem.ListItem({
	        id: options.id,
	        title: agreementListItem.name,
	        description: agreementListItem.labelText,
	        sourceOptions: options,
	        draggable: true,
	        editable: true,
	        removable: true,
	        form: this.createItemForm(options),
	        onRemove: this.onItemRemoveClick
	      });
	    }
	  }, {
	    key: "setCurrentlyEdited",
	    value: function setCurrentlyEdited(agreement) {
	      this.cache.set('setCurrentlyEdited', agreement);
	    }
	  }, {
	    key: "getCurrentlyEdited",
	    value: function getCurrentlyEdited() {
	      return this.cache.get('setCurrentlyEdited') || null;
	    } // eslint-disable-next-line

	  }, {
	    key: "buildEditPath",
	    value: function buildEditPath(agreementId) {
	      return "/settings/configs/userconsent/edit/".concat(agreementId, "/");
	    } // eslint-disable-next-line

	  }, {
	    key: "buildConsentsListPath",
	    value: function buildConsentsListPath(agreementId) {
	      return "/settings/configs/userconsent/consents/".concat(agreementId, "/");
	    }
	  }, {
	    key: "editAgreement",
	    value: function editAgreement(agreement) {
	      this.setCurrentlyEdited(agreement);
	      var editPath = this.buildEditPath(agreement.id);
	      BX.SidePanel.Instance.open(editPath, {
	        cacheable: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "closeEditAgreementSlider",
	    value: function closeEditAgreementSlider() {
	      var currentlyEdited = this.getCurrentlyEdited();

	      if (main_core.Type.isPlainObject(currentlyEdited)) {
	        var path = this.buildEditPath(currentlyEdited.id);
	        var slider = BX.SidePanel.Instance.getSlider(path);

	        if (slider) {
	          slider.close();
	        }
	      }
	    }
	  }, {
	    key: "openConsentsList",
	    value: function openConsentsList(agreement) {
	      var editPath = this.buildConsentsListPath(agreement.id);
	      BX.SidePanel.Instance.open(editPath, {
	        cacheable: false,
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "onUserConsentEditCancel",
	    value: function onUserConsentEditCancel() {
	      this.closeEditAgreementSlider();
	    }
	  }, {
	    key: "onUserConsentEditSave",
	    value: function onUserConsentEditSave() {
	      var _this9 = this;

	      this.closeEditAgreementSlider();
	      void this.showAgreementLoader();
	      var value = this.getValue();
	      this.loadAgreementsList().then(function (agreements) {
	        _this9.setAgreementsList(agreements);

	        landing_ui_panel_formsettingspanel.FormSettingsPanel.getInstance().setAgreements(agreements);

	        var currentlyEdited = _this9.getCurrentlyEdited();

	        if (currentlyEdited && currentlyEdited.id === 0) {
	          var lastAgreement = babelHelpers.toConsumableArray(agreements).pop();
	          crm_form_client.FormClient.getInstance().prepareOptions(_this9.options.formOptions, {
	            agreements: [lastAgreement]
	          }).then(function (result) {
	            void _this9.hideAgreementLoader();

	            _this9.addItem(result.data.agreements[0]);

	            _this9.refreshAgreementsMenu();

	            _this9.emit('onChange', {
	              skipPrepare: true
	            });
	          });
	        } else {
	          main_core.Dom.clean(_this9.getListContainer());
	          void _this9.showAgreementLoader();
	          crm_form_client.FormClient.getInstance().prepareOptions(_this9.options.formOptions, {
	            agreements: value
	          }).then(function (result) {
	            void _this9.hideAgreementLoader();
	            _this9.items = [];
	            value.forEach(function (agreement) {
	              var resultAgreement = result.data.agreements.find(function (currentAgreement) {
	                return String(currentAgreement.id) === String(agreement.id);
	              });

	              if (resultAgreement) {
	                _this9.addItem(babelHelpers.objectSpread({}, resultAgreement, {
	                  checked: agreement.checked,
	                  required: agreement.required
	                }));
	              } else {
	                _this9.addItem(agreement);
	              }
	            });

	            _this9.refreshAgreementsMenu();

	            _this9.emit('onChange', {
	              skipPrepare: true
	            });
	          });
	        }
	      });
	    }
	  }, {
	    key: "onItemRemoveClick",
	    value: function onItemRemoveClick(event) {
	      var value = event.getTarget().getValue();
	      this.items = this.items.filter(function (item) {
	        return String(item.options.id) !== String(value.id);
	      });
	      this.refreshAgreementsMenu();
	      this.emit('onItemRemove', {
	        item: value
	      });
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "onDragEnd",
	    value: function onDragEnd() {
	      var _this10 = this;

	      var items = this.items;
	      this.items = [];
	      babelHelpers.toConsumableArray(this.getListContainer().children).forEach(function (element) {
	        var id = main_core.Dom.attr(element, 'data-id');
	        var item = items.find(function (currentItem) {
	          return String(currentItem.options.id) === String(id);
	        });

	        if (item) {
	          _this10.items.push(item);
	        }
	      });
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.items.map(function (item) {
	        return item.getValue();
	      });
	    }
	  }]);
	  return AgreementsList;
	}(landing_ui_field_basefield.BaseField);

	exports.AgreementsList = AgreementsList;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Main,BX.Landing.UI.Field,BX.UI.DragAndDrop,BX.Landing.UI.Field,BX.Landing.UI.Form,BX.Crm.Form,BX.Landing.UI.Component,BX.Landing.UI.Component,BX.Event,BX,BX.Landing,BX.Landing.UI.Panel));
//# sourceMappingURL=agreementslist.bundle.js.map
