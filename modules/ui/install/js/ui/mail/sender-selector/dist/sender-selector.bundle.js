/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events,main_loader,ui_entitySelector,ui_mail_providerShowcase,ui_mail_senderEditor,ui_iconSet_api_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	const senderEntityId = 'sender';
	const mailboxEntityId = 'mailbox';
	const senderPrefix = 'S';
	const mailboxPrefix = 'MB';
	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");
	var _senderButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("senderButton");
	var _senderButtonTextNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("senderButtonTextNode");
	var _loader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loader");
	var _isListUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isListUpdated");
	var _isSenderAvailable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSenderAvailable");
	var _createLoader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createLoader");
	var _renderContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContainer");
	var _createSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSelector");
	var _updateDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateDialog");
	var _loadItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadItems");
	var _updateSenderList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateSenderList");
	var _addSender = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addSender");
	var _showLoader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showLoader");
	var _hideLoader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideLoader");
	var _getSelectorSenderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSelectorSenderId");
	var _getSenderTypeByItemId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSenderTypeByItemId");
	class SenderSelector {
	  constructor(options) {
	    var _options$fieldValue, _options$isSenderAvai;
	    Object.defineProperty(this, _getSenderTypeByItemId, {
	      value: _getSenderTypeByItemId2
	    });
	    Object.defineProperty(this, _getSelectorSenderId, {
	      value: _getSelectorSenderId2
	    });
	    Object.defineProperty(this, _hideLoader, {
	      value: _hideLoader2
	    });
	    Object.defineProperty(this, _showLoader, {
	      value: _showLoader2
	    });
	    Object.defineProperty(this, _addSender, {
	      value: _addSender2
	    });
	    Object.defineProperty(this, _updateSenderList, {
	      value: _updateSenderList2
	    });
	    Object.defineProperty(this, _loadItems, {
	      value: _loadItems2
	    });
	    Object.defineProperty(this, _updateDialog, {
	      value: _updateDialog2
	    });
	    Object.defineProperty(this, _createSelector, {
	      value: _createSelector2
	    });
	    Object.defineProperty(this, _renderContainer, {
	      value: _renderContainer2
	    });
	    Object.defineProperty(this, _createLoader, {
	      value: _createLoader2
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _senderButton, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _senderButtonTextNode, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isListUpdated, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _isSenderAvailable, {
	      writable: true,
	      value: false
	    });
	    this.sender = ((_options$fieldValue = options.fieldValue) == null ? void 0 : _options$fieldValue.length) > 0 ? options.fieldValue : null;
	    this.fieldId = options.fieldId;
	    this.fieldName = options.fieldName;
	    babelHelpers.classPrivateFieldLooseBase(this, _isSenderAvailable)[_isSenderAvailable] = (_options$isSenderAvai = options.isSenderAvailable) != null ? _options$isSenderAvai : false;
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = this.fieldId && this.fieldName ? babelHelpers.classPrivateFieldLooseBase(this, _renderContainer)[_renderContainer]() : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _createLoader)[_createLoader]();
	    babelHelpers.classPrivateFieldLooseBase(this, _createSelector)[_createSelector]();
	    this.selectCallback = options.selectCallback;
	    this.mailboxes = options.mailboxes;
	    if (this.mailboxes) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateDialog)[_updateDialog](this.mailboxes);
	    }
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	  }
	  renderTo(targetContainer) {
	    if (main_core.Type.isDomNode(targetContainer)) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], targetContainer);
	    }
	  }
	  setSender(senderId = null, name = null, email = null, type = senderEntityId) {
	    const prefix = type === mailboxEntityId ? mailboxPrefix : senderPrefix;
	    this.selectedItemId = senderId ? `${prefix}_${senderId}` : null;
	    const senderName = name;
	    const senderEmail = email;
	    let selectorText = '';
	    if (senderName && senderEmail) {
	      selectorText = `${senderName} <${senderEmail}>`;
	    }
	    if (this.selectCallback && !babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	      this.selectCallback(selectorText, '');
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	      return;
	    }
	    const input = babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector('input');
	    this.sender = selectorText;
	    babelHelpers.classPrivateFieldLooseBase(this, _senderButtonTextNode)[_senderButtonTextNode].innerText = selectorText.length > 0 ? selectorText : main_core.Loc.getMessage('UI_MAIL_SENDER_SLIDER_SELECTOR_SELECT_NEW_SENDER');
	    babelHelpers.classPrivateFieldLooseBase(this, _senderButtonTextNode)[_senderButtonTextNode].title = this.sender;
	    main_core.Dom.append(this.icon, babelHelpers.classPrivateFieldLooseBase(this, _senderButton)[_senderButton]);
	    input.value = selectorText;
	  }
	  showDialog(targetNode = null, selectedSender = null) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isListUpdated)[_isListUpdated]) {
	      return;
	    }
	    if (!this.senderDialog || this.senderDialog.getItems().length === 0) {
	      this.showProviderShowcase();
	      return;
	    }
	    if (targetNode) {
	      this.senderDialog.setTargetNode(targetNode);
	    }
	    this.senderDialog.show();
	  }
	  showProviderShowcase(addSenderCallback) {
	    this.addSenderCallback = addSenderCallback;
	    ui_mail_providerShowcase.ProviderShowcase.openSlider({
	      isSender: babelHelpers.classPrivateFieldLooseBase(this, _isSenderAvailable)[_isSenderAvailable],
	      addSenderCallback,
	      setSenderCallback: (senderId, senderName, senderEmail) => {
	        this.setSender(senderId, senderName, senderEmail);
	      },
	      updateSenderList: () => {
	        void babelHelpers.classPrivateFieldLooseBase(this, _updateSenderList)[_updateSenderList]();
	      }
	    });
	  }
	}
	function _createLoader2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = new main_loader.Loader({
	    target: babelHelpers.classPrivateFieldLooseBase(this, _senderButton)[_senderButton],
	    size: 17,
	    mode: 'inline'
	  });
	}
	function _renderContainer2() {
	  var _this$sender, _this$sender2, _this$sender3;
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Actions.CHEVRON_DOWN,
	    color: getComputedStyle(document.body).getPropertyValue('--ui-color-base-80'),
	    size: 16
	  });
	  this.icon = icon.render();
	  babelHelpers.classPrivateFieldLooseBase(this, _senderButtonTextNode)[_senderButtonTextNode] = main_core.Tag.render(_t || (_t = _`
			<div class="sender-selector-button-text" title="${0}">
				${0}
			</div>
		`), (_this$sender = this.sender) != null ? _this$sender : '', (_this$sender2 = this.sender) != null ? _this$sender2 : main_core.Loc.getMessage('UI_MAIL_SENDER_SLIDER_SELECTOR_SELECT_NEW_SENDER'));
	  babelHelpers.classPrivateFieldLooseBase(this, _senderButton)[_senderButton] = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="sender-selector-button">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _senderButtonTextNode)[_senderButtonTextNode], this.icon);
	  const {
	    root,
	    senderInput
	  } = main_core.Tag.render(_t3 || (_t3 = _`
			<div>
				${0}
				<input type="hidden"
					id="${0}"
					name="${0}"
					value="${0}"
					ref="senderInput">
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _senderButton)[_senderButton], this.fieldId, this.fieldName, (_this$sender3 = this.sender) != null ? _this$sender3 : '');
	  this.senderInput = senderInput;
	  return root;
	}
	function _createSelector2() {
	  const footerHandler = () => {
	    this.senderDialog.hide();
	    this.showProviderShowcase();
	  };
	  const footer = main_core.Tag.render(_t4 || (_t4 = _`
			<span class="ui-selector-footer-link ui-selector-footer-link-add" onclick="${0}">${0}</span>
		`), footerHandler, main_core.Loc.getMessage('UI_MAIL_SENDER_SLIDER_SELECTOR_ADD_NEW_MAILBOX'));
	  const linkClickHandler = baseEvent => {
	    const data = baseEvent.data;
	    data.event.preventDefault();
	    const item = data.node.getItem();
	    const dialog = item.getDialog();
	    dialog.hide();
	    const customData = item.getCustomData();
	    if (item.entityId === mailboxEntityId) {
	      BX.SidePanel.Instance.open(customData.get('href'), {
	        width: 760,
	        cacheable: false,
	        events: {
	          onClose: () => {
	            this.setSender();
	            void babelHelpers.classPrivateFieldLooseBase(this, _updateSenderList)[_updateSenderList]();
	          }
	        }
	      });
	      return;
	    }
	    ui_mail_senderEditor.AliasEditor.openSlider({
	      senderId: customData.get('id'),
	      email: customData.get('email'),
	      setSenderCallback: (senderId, senderName, senderEmail) => {
	        this.setSender(senderId, senderName, senderEmail);
	      },
	      updateSenderList: () => {
	        void babelHelpers.classPrivateFieldLooseBase(this, _updateSenderList)[_updateSenderList]();
	      }
	    });
	  };
	  this.senderDialog = new ui_entitySelector.Dialog({
	    targetNode: babelHelpers.classPrivateFieldLooseBase(this, _senderButton)[_senderButton],
	    width: 400,
	    height: 300,
	    multiple: false,
	    enableSearch: true,
	    footer,
	    dropdownMode: true,
	    showAvatars: false,
	    compactView: true,
	    events: {
	      'Item:onSelect': event => {
	        const {
	          item: selectedItem
	        } = event.getData();
	        const selectedItemName = selectedItem.getCustomData().get('name');
	        const selectedItemEmail = selectedItem.getCustomData().get('email');
	        this.setSender(selectedItem.id, selectedItemName, selectedItemEmail);
	      },
	      'ItemNode:onLinkClick': linkClickHandler
	    }
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _senderButton)[_senderButton], 'click', () => {
	    this.showDialog();
	  });
	}
	function _updateDialog2(senders) {
	  this.senderDialog.removeItems();
	  const senderName = main_core.Tag.unsafe(_t5 || (_t5 = _`${0}`), this.sender);
	  senders.forEach(sender => {
	    if (sender.id) {
	      babelHelpers.classPrivateFieldLooseBase(this, _addSender)[_addSender](sender);
	      if (!this.selectedItemId && senderName === `${sender.name} <${sender.email}>`) {
	        this.selectedItemId = babelHelpers.classPrivateFieldLooseBase(this, _getSelectorSenderId)[_getSelectorSenderId](sender.id, sender.type);
	      }
	    }
	  });
	  if (this.selectedItemId) {
	    const selectedItem = this.senderDialog.getItem({
	      id: this.selectedItemId,
	      entityId: babelHelpers.classPrivateFieldLooseBase(this, _getSenderTypeByItemId)[_getSenderTypeByItemId](this.selectedItemId)
	    });
	    selectedItem == null ? void 0 : selectedItem.select();
	  } else {
	    const items = this.senderDialog.getItems();
	    if (items.length > 0) {
	      this.setSender(items[0].id, items[0].getCustomData().get('name'), items[0].getCustomData().get('email'));
	      items[0].select();
	      this.selectedItemId = items[0].id;
	    }
	  }
	}
	function _loadItems2() {
	  return main_core.ajax.runAction('main.api.mail.sender.getAvailableSenders', {}).then(response => {
	    return response.data;
	  }).catch(() => {
	    return [];
	  });
	}
	async function _updateSenderList2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _isListUpdated)[_isListUpdated] = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _showLoader)[_showLoader]();
	  this.senderDialog.removeItems();
	  try {
	    const senders = await babelHelpers.classPrivateFieldLooseBase(this, _loadItems)[_loadItems]();
	    if (senders) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateDialog)[_updateDialog](senders);
	    }
	  } catch {/* empty */}
	  babelHelpers.classPrivateFieldLooseBase(this, _hideLoader)[_hideLoader]();
	  babelHelpers.classPrivateFieldLooseBase(this, _isListUpdated)[_isListUpdated] = true;
	}
	function _addSender2(sender) {
	  const title = `${sender.name} <${sender.email}>`;
	  const id = babelHelpers.classPrivateFieldLooseBase(this, _getSelectorSenderId)[_getSelectorSenderId](sender.id, sender.type);
	  const href = sender.type === mailboxEntityId ? sender.editHref : sender.id;
	  this.senderDialog.addItem({
	    id,
	    tabs: 'recents',
	    entityId: sender.type === mailboxEntityId ? mailboxEntityId : senderEntityId,
	    link: href ? '#' : null,
	    deselectable: false,
	    linkTitle: main_core.Loc.getMessage('UI_MAIL_SENDER_SLIDER_SELECTOR_ITEM_LINK_TITLE'),
	    title,
	    customData: {
	      name: sender.name,
	      email: sender.email,
	      id: sender.id,
	      formated: sender.formated,
	      href
	    }
	  });
	}
	function _showLoader2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader].show();
	  main_core.Dom.style(this.icon, 'display', 'none');
	}
	function _hideLoader2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader].hide();
	  main_core.Dom.style(this.icon, 'display', 'block');
	}
	function _getSelectorSenderId2(id, entityType) {
	  return entityType === mailboxEntityId ? `${mailboxPrefix}_${id}` : `${senderPrefix}_${id}`;
	}
	function _getSenderTypeByItemId2(id) {
	  const prefix = id.split('_')[0];
	  switch (prefix) {
	    case senderPrefix:
	      return senderEntityId;
	    case mailboxPrefix:
	      return mailboxEntityId;
	    default:
	      return '';
	  }
	}

	exports.SenderSelector = SenderSelector;

}((this.BX.UI.Mail = this.BX.UI.Mail || {}),BX,BX.Event,BX,BX.UI.EntitySelector,BX.UI.Mail,BX.UI.Mail,BX.UI.IconSet));
//# sourceMappingURL=sender-selector.bundle.js.map
