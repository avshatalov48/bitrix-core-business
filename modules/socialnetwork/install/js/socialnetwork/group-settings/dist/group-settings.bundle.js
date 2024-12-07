this.BX = this.BX || {};
(function (exports,pull_client,socialnetwork_groupPrivacy,ui_avatarEditor,socialnetwork_logo,main_popup,ui_buttons,ui_label,ui_switcher,main_core,main_core_events,ui_entitySelector,socialnetwork_controller,ui_alerts,ui_sidepanelContent,ui_dialogs_messagebox) {
	'use strict';

	var _groupId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _update = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("update");
	class PullRequests extends main_core_events.EventEmitter {
	  constructor(groupId) {
	    super();
	    Object.defineProperty(this, _update, {
	      value: _update2
	    });
	    Object.defineProperty(this, _groupId, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.Group.Settings.PullRequests');
	    babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId] = parseInt(groupId, 10);
	  }
	  getModuleId() {
	    return 'socialnetwork';
	  }
	  getMap() {
	    return {
	      workgroup_update: babelHelpers.classPrivateFieldLooseBase(this, _update)[_update].bind(this),
	      workgroup_user_add: babelHelpers.classPrivateFieldLooseBase(this, _update)[_update].bind(this),
	      workgroup_user_delete: babelHelpers.classPrivateFieldLooseBase(this, _update)[_update].bind(this),
	      workgroup_user_update: babelHelpers.classPrivateFieldLooseBase(this, _update)[_update].bind(this)
	    };
	  }
	}
	function _update2(data) {
	  if (parseInt(data.params.GROUP_ID, 10) === babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId]) {
	    this.emit('update');
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _renderDescriptionEditText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDescriptionEditText");
	var _renderDescriptionEditButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDescriptionEditButton");
	var _renderDescriptionTextarea = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDescriptionTextarea");
	var _startEditing = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startEditing");
	var _stopEditing = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stopEditing");
	var _getDescriptionText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDescriptionText");
	var _adjustTextareaHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustTextareaHeight");
	class EditDescription {
	  constructor(params) {
	    Object.defineProperty(this, _adjustTextareaHeight, {
	      value: _adjustTextareaHeight2
	    });
	    Object.defineProperty(this, _getDescriptionText, {
	      value: _getDescriptionText2
	    });
	    Object.defineProperty(this, _stopEditing, {
	      value: _stopEditing2
	    });
	    Object.defineProperty(this, _startEditing, {
	      value: _startEditing2
	    });
	    Object.defineProperty(this, _renderDescriptionTextarea, {
	      value: _renderDescriptionTextarea2
	    });
	    Object.defineProperty(this, _renderDescriptionEditButton, {
	      value: _renderDescriptionEditButton2
	    });
	    Object.defineProperty(this, _renderDescriptionEditText, {
	      value: _renderDescriptionEditText2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {};
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionContainer = main_core.Tag.render(_t || (_t = _`
			<div class="sn-group-settings__description-container">
				<div class="sn-group-settings__description">
					${0}
				</div>
				${0}
			<div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderDescriptionEditText)[_renderDescriptionEditText](), babelHelpers.classPrivateFieldLooseBase(this, _renderDescriptionTextarea)[_renderDescriptionTextarea]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionContainer;
	  }
	}
	function _renderDescriptionEditText2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionText = main_core.Tag.render(_t2 || (_t2 = _`
			<div>
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getDescriptionText)[_getDescriptionText](), babelHelpers.classPrivateFieldLooseBase(this, _renderDescriptionEditButton)[_renderDescriptionEditButton]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionText;
	}
	function _renderDescriptionEditButton2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].canEdit !== true) {
	    return '';
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionEditButton = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="ui-icon-set --pencil-40"></div>
		`));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionEditButton, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _startEditing)[_startEditing]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionEditButton;
	}
	function _renderDescriptionTextarea2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea = main_core.Tag.render(_t4 || (_t4 = _`
			<textarea
				class="sn-group-settings__description-textarea"
				maxlength="20000"
				rows="1"
			>${0}</textarea>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].description));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'focus', () => babelHelpers.classPrivateFieldLooseBase(this, _adjustTextareaHeight)[_adjustTextareaHeight]());
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'blur', () => babelHelpers.classPrivateFieldLooseBase(this, _stopEditing)[_stopEditing]());
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'keydown', event => {
	    if (event.key === 'Enter') {
	      babelHelpers.classPrivateFieldLooseBase(this, _stopEditing)[_stopEditing]();
	    }
	  });
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'input', () => babelHelpers.classPrivateFieldLooseBase(this, _adjustTextareaHeight)[_adjustTextareaHeight]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea;
	}
	function _startEditing2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionContainer, '--editing');
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea.focus();
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea.setSelectionRange(20000, 20000);
	}
	function _stopEditing2() {
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionContainer, '--editing');
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea.value = babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea.value.trim();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].description !== babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea.value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].description = babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea.value;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionText.innerHTML = babelHelpers.classPrivateFieldLooseBase(this, _getDescriptionText)[_getDescriptionText]();
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionText.append(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionEditButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].onChange(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].description);
	  }
	}
	function _getDescriptionText2() {
	  var _babelHelpers$classPr;
	  if (main_core.Type.isStringFilled((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].description) == null ? void 0 : _babelHelpers$classPr.trim())) {
	    return main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].description);
	  }
	  return main_core.Loc.getMessage('SN_GROUP_SETTINGS_NO_DESCRIPTION');
	}
	function _adjustTextareaHeight2() {
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'height', 0);
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'maxHeight', 0);
	  const height = babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea.scrollHeight;
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'height', `${height}px`);
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].descriptionTextarea, 'maxHeight', `${height}px`);
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1;
	var _params$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _privacyPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("privacyPopup");
	var _layout$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _createPrivacyPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createPrivacyPopup");
	var _showPrivacy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showPrivacy");
	var _changePrivacy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changePrivacy");
	class EditPrivacy {
	  constructor(params) {
	    Object.defineProperty(this, _changePrivacy, {
	      value: _changePrivacy2
	    });
	    Object.defineProperty(this, _showPrivacy, {
	      value: _showPrivacy2
	    });
	    Object.defineProperty(this, _createPrivacyPopup, {
	      value: _createPrivacyPopup2
	    });
	    Object.defineProperty(this, _params$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _privacyPopup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _privacyPopup)[_privacyPopup] = babelHelpers.classPrivateFieldLooseBase(this, _createPrivacyPopup)[_createPrivacyPopup]();
	  }
	  render() {
	    const canEdit = babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].canEdit === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].privacyText = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="sn-group-settings__privacy">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _privacyPopup)[_privacyPopup].getLabel());
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].privacy = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="sn-group-settings__privacy-container ${0}">
				${0}
				<div class="ui-icon-set --chevron-down"></div>
			</div>
		`), !canEdit ? '--readonly' : '', babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].privacyText);
	    if (canEdit) {
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].privacy, 'click', babelHelpers.classPrivateFieldLooseBase(this, _showPrivacy)[_showPrivacy].bind(this));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].privacy;
	  }
	  setPrivacy(privacyCode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].privacyCode = privacyCode;
	    babelHelpers.classPrivateFieldLooseBase(this, _privacyPopup)[_privacyPopup].setPrivacy(babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].privacyCode);
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].privacyText.innerText = babelHelpers.classPrivateFieldLooseBase(this, _privacyPopup)[_privacyPopup].getLabel();
	  }
	}
	function _createPrivacyPopup2() {
	  const privacyPopup = new socialnetwork_groupPrivacy.GroupPrivacy({
	    privacyCode: babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].privacyCode
	  });
	  privacyPopup.subscribe('changePrivacy', babelHelpers.classPrivateFieldLooseBase(this, _changePrivacy)[_changePrivacy].bind(this));
	  return privacyPopup;
	}
	function _showPrivacy2(event) {
	  babelHelpers.classPrivateFieldLooseBase(this, _privacyPopup)[_privacyPopup].show(event.target);
	}
	function _changePrivacy2(baseEvent) {
	  const privacyCode = baseEvent.getData();
	  this.setPrivacy(privacyCode);
	  babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].onChange(privacyCode);
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$1,
	  _t4$1;
	var _params$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _renderTitleText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTitleText");
	var _renderEditTitleButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEditTitleButton");
	var _renderEditTitleInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEditTitleInput");
	var _startEditing$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startEditing");
	var _stopEditing$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stopEditing");
	class EditTitle {
	  constructor(params) {
	    Object.defineProperty(this, _stopEditing$1, {
	      value: _stopEditing2$1
	    });
	    Object.defineProperty(this, _startEditing$1, {
	      value: _startEditing2$1
	    });
	    Object.defineProperty(this, _renderEditTitleInput, {
	      value: _renderEditTitleInput2
	    });
	    Object.defineProperty(this, _renderEditTitleButton, {
	      value: _renderEditTitleButton2
	    });
	    Object.defineProperty(this, _renderTitleText, {
	      value: _renderTitleText2
	    });
	    Object.defineProperty(this, _params$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2] = params;
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleContainer = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="sn-group-settings__title-edit-container">
				<div class="sn-group-settings__title-edit">
					${0}
					${0}
				</div>
				<div class="
					ui-ctl ui-ctl-textbox ui-ctl--w100 ui-ctl--transp ui-ctl-no-border ui-ctl-xs ui-ctl-no-padding
					sn-group-settings__title-edit-input
				">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderTitleText)[_renderTitleText](), babelHelpers.classPrivateFieldLooseBase(this, _renderEditTitleButton)[_renderEditTitleButton](), babelHelpers.classPrivateFieldLooseBase(this, _renderEditTitleInput)[_renderEditTitleInput]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleContainer;
	  }
	  setTitle(title) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput.value = title;
	    babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].name = title;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].titleText.innerHTML = main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].name);
	  }
	}
	function _renderTitleText2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].titleText = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="sn-group-settings__title-edit-text">${0}</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].name));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].titleText;
	}
	function _renderEditTitleButton2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].canEdit !== true) {
	    return '';
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleButton = main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<div class="ui-icon-set --pencil-40"></div>
		`));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleButton, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _startEditing$1)[_startEditing$1]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleButton;
	}
	function _renderEditTitleInput2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput = main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
			<input type="text" value="${0}" class="ui-ctl-element">
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].name));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput, 'blur', () => babelHelpers.classPrivateFieldLooseBase(this, _stopEditing$1)[_stopEditing$1]());
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput, 'keydown', event => {
	    if (event.key === 'Enter') {
	      babelHelpers.classPrivateFieldLooseBase(this, _stopEditing$1)[_stopEditing$1]();
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput;
	}
	function _startEditing2$1() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleContainer, '--editing');
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput.focus();
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput.setSelectionRange(999, 999);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput.scrollTo({
	    left: babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput.scrollWidth,
	    behavior: 'smooth'
	  });
	}
	function _stopEditing2$1() {
	  const name = babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleInput.value.trim();
	  if (main_core.Type.isStringFilled(name) && babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].name !== name) {
	    this.setTitle(name);
	    babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].onChange(babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].name);
	  } else {
	    this.setTitle(babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].name);
	  }
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].editTitleContainer, '--editing');
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3;
	var _params$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _avatarEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("avatarEditor");
	var _renderAvatarEdit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAvatarEdit");
	var _chooseSpaceImage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chooseSpaceImage");
	var _getAvatarEditor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAvatarEditor");
	class EditAvatar {
	  constructor(params) {
	    Object.defineProperty(this, _getAvatarEditor, {
	      value: _getAvatarEditor2
	    });
	    Object.defineProperty(this, _chooseSpaceImage, {
	      value: _chooseSpaceImage2
	    });
	    Object.defineProperty(this, _renderAvatarEdit, {
	      value: _renderAvatarEdit2
	    });
	    Object.defineProperty(this, _params$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _avatarEditor, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3] = params;
	  }
	  render() {
	    var _logo$getClass, _babelHelpers$classPr;
	    const logo = new socialnetwork_logo.Logo(babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].logo);
	    const avatarNode = main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div class="sn-group-settings__space-avatar sn-spaces__space-logo ${0}">
				${0}
				${0}
			</div>
		`), (_logo$getClass = logo.getClass()) != null ? _logo$getClass : '', babelHelpers.classPrivateFieldLooseBase(this, _renderAvatarEdit)[_renderAvatarEdit](), logo.render());
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatar) == null ? void 0 : _babelHelpers$classPr.replaceWith(avatarNode);
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatar = avatarNode;
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatar;
	  }
	  setAvatar(avatar) {
	    babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].logo = {
	      id: avatar,
	      type: 'image'
	    };
	    this.render();
	  }
	}
	function _renderAvatarEdit2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].canEdit !== true) {
	    return '';
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarEdit = main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
			<div class="sn-group-settings__space-avatar-edit">
				<div class="ui-icon-set --pencil-40"></div>
			</div>
		`));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarEdit, 'click', babelHelpers.classPrivateFieldLooseBase(this, _chooseSpaceImage)[_chooseSpaceImage].bind(this));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarEdit;
	}
	function _chooseSpaceImage2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _getAvatarEditor)[_getAvatarEditor]().show('file');
	}
	function _getAvatarEditor2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _avatarEditor)[_avatarEditor]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _avatarEditor)[_avatarEditor] = new ui_avatarEditor.Editor({
	      enableCamera: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _avatarEditor)[_avatarEditor].subscribe('onApply', event => {
	      const [file] = event.getCompatData();
	      const avatar = URL.createObjectURL(file);
	      this.setAvatar(avatar);
	      babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].onChange(file);
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _avatarEditor)[_avatarEditor];
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$4;
	var _params$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _empty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("empty");
	var _actionsMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("actionsMenu");
	var _showActionsMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showActionsMenu");
	var _getActionsMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getActionsMenu");
	var _createActionsMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createActionsMenu");
	var _showDangerPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showDangerPopup");
	var _getPinText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPinText");
	var _getFollowText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFollowText");
	class ActionsButton {
	  constructor(params) {
	    Object.defineProperty(this, _getFollowText, {
	      value: _getFollowText2
	    });
	    Object.defineProperty(this, _getPinText, {
	      value: _getPinText2
	    });
	    Object.defineProperty(this, _showDangerPopup, {
	      value: _showDangerPopup2
	    });
	    Object.defineProperty(this, _createActionsMenu, {
	      value: _createActionsMenu2
	    });
	    Object.defineProperty(this, _getActionsMenu, {
	      value: _getActionsMenu2
	    });
	    Object.defineProperty(this, _showActionsMenu, {
	      value: _showActionsMenu2
	    });
	    Object.defineProperty(this, _params$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _empty, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _actionsMenu, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _empty)[_empty] = babelHelpers.classPrivateFieldLooseBase(this, _getActionsMenu)[_getActionsMenu]().getMenuItems().length === 0;
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _empty)[_empty]) {
	      return main_core.Tag.render(_t$4 || (_t$4 = _$4`<div></div>`));
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].button = new ui_buttons.Button({
	      text: main_core.Loc.getMessage('SN_GROUP_SETTINGS_ACTIONS'),
	      color: ui_buttons.ButtonColor.SUCCESS,
	      size: ui_buttons.ButtonSize.MEDIUM,
	      dropdown: true,
	      round: true,
	      onclick: babelHelpers.classPrivateFieldLooseBase(this, _showActionsMenu)[_showActionsMenu].bind(this)
	    }).render();
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].button;
	  }
	}
	function _showActionsMenu2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _getActionsMenu)[_getActionsMenu]().show();
	}
	function _getActionsMenu2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _actionsMenu)[_actionsMenu] = babelHelpers.classPrivateFieldLooseBase(this, _createActionsMenu)[_createActionsMenu]();
	  return babelHelpers.classPrivateFieldLooseBase(this, _actionsMenu)[_actionsMenu];
	}
	function _createActionsMenu2() {
	  const menu = new main_popup.Menu({
	    id: `sn-group-settings__actions-menu-${main_core.Text.getRandom()}`,
	    bindElement: babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].button,
	    closeByEsc: true
	  });
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].actions.canPin) {
	    menu.addMenuItem({
	      text: babelHelpers.classPrivateFieldLooseBase(this, _getPinText)[_getPinText](),
	      dataset: {
	        id: 'sn-group-settings__actions-pin'
	      },
	      onclick: (event, item) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isPin = !babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isPin;
	        item.setText(babelHelpers.classPrivateFieldLooseBase(this, _getPinText)[_getPinText]());
	        babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].pinChanged(babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isPin);
	        menu.close();
	      }
	    });
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].actions.canFollow) {
	    menu.addMenuItem({
	      text: babelHelpers.classPrivateFieldLooseBase(this, _getFollowText)[_getFollowText](),
	      dataset: {
	        id: 'sn-group-settings__actions-follow'
	      },
	      onclick: (event, item) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isSubscribed = !babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isSubscribed;
	        item.setText(babelHelpers.classPrivateFieldLooseBase(this, _getFollowText)[_getFollowText]());
	        babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].followChanged(babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isSubscribed);
	        menu.close();
	      }
	    });
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].actions.canLeave) {
	    menu.addMenuItem({
	      text: main_core.Loc.getMessage('SN_GROUP_SETTINGS_LEAVE_SPACE'),
	      dataset: {
	        id: 'sn-group-settings__actions-leave'
	      },
	      onclick: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _showDangerPopup)[_showDangerPopup](() => babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].leave(), main_core.Loc.getMessage('SN_GROUP_SETTINGS_CONFIRM_LEAVE'), main_core.Loc.getMessage('SN_GROUP_SETTINGS_LEAVE'));
	        menu.close();
	      }
	    });
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].actions.canEdit) {
	    menu.addMenuItem({
	      text: main_core.Loc.getMessage('SN_GROUP_SETTINGS_DELETE_SPACE'),
	      dataset: {
	        id: 'sn-group-settings__actions-delete'
	      },
	      onclick: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _showDangerPopup)[_showDangerPopup](() => babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].delete(), main_core.Loc.getMessage('SN_GROUP_SETTINGS_CONFIRM_DELETE'), main_core.Loc.getMessage('SN_GROUP_SETTINGS_DELETE'));
	        menu.close();
	      }
	    });
	  }
	  return menu;
	}
	function _showDangerPopup2(action, message, okCaption) {
	  const popup = new main_popup.Popup({
	    bindElement: null,
	    content: main_core.Tag.render(_t2$4 || (_t2$4 = _$4`
				<div class="socialnetwork-danger-popup">
					${0}
				</div>
			`), message),
	    buttons: [new ui_buttons.Button({
	      id: 'socialnetwork-danger-popup-btn-action',
	      size: ui_buttons.ButtonSize.SMALL,
	      color: ui_buttons.ButtonColor.DANGER,
	      text: okCaption,
	      events: {
	        click: () => {
	          action();
	          popup.close();
	        }
	      }
	    }), new ui_buttons.Button({
	      id: 'socialnetwork-danger-popup-btn-cancel',
	      size: ui_buttons.ButtonSize.SMALL,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('SN_GROUP_SETTINGS_CANCEL'),
	      events: {
	        click: () => popup.close()
	      }
	    })],
	    minHeight: 120,
	    minWidth: 350,
	    maxWidth: 350,
	    animation: 'fading-slide'
	  });
	  popup.show();
	}
	function _getPinText2() {
	  return main_core.Loc.getMessage(`SN_GROUP_SETTINGS_MENU_PIN_${babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isPin ? 'N' : 'Y'}`);
	}
	function _getFollowText2() {
	  return main_core.Loc.getMessage(`SN_GROUP_SETTINGS_MENU_FOLLOW_${babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].isSubscribed ? 'N' : 'Y'}`);
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$5,
	  _t3$2,
	  _t4$2,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11;
	var _params$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _renderOwner = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderOwner");
	var _renderModeratorsLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderModeratorsLine");
	var _renderModerators = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderModerators");
	var _renderMoreModerators = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMoreModerators");
	var _renderModeratorsEmptyState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderModeratorsEmptyState");
	var _renderModerator = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderModerator");
	var _getUserProfileUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserProfileUrl");
	var _renderMembers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMembers");
	var _renderAvatarsLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAvatarsLine");
	var _renderMembersEmptyState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMembersEmptyState");
	var _renderMembersLineEmptyState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMembersLineEmptyState");
	var _renderMember = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMember");
	var _renderPlusMembers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderPlusMembers");
	var _renderAvatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAvatar");
	var _renderMembersButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMembersButton");
	var _renderRoleLine = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRoleLine");
	var _isAvatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAvatar");
	class MembersSection {
	  constructor(params) {
	    Object.defineProperty(this, _isAvatar, {
	      value: _isAvatar2
	    });
	    Object.defineProperty(this, _renderRoleLine, {
	      value: _renderRoleLine2
	    });
	    Object.defineProperty(this, _renderMembersButton, {
	      value: _renderMembersButton2
	    });
	    Object.defineProperty(this, _renderAvatar, {
	      value: _renderAvatar2
	    });
	    Object.defineProperty(this, _renderPlusMembers, {
	      value: _renderPlusMembers2
	    });
	    Object.defineProperty(this, _renderMember, {
	      value: _renderMember2
	    });
	    Object.defineProperty(this, _renderMembersLineEmptyState, {
	      value: _renderMembersLineEmptyState2
	    });
	    Object.defineProperty(this, _renderMembersEmptyState, {
	      value: _renderMembersEmptyState2
	    });
	    Object.defineProperty(this, _renderAvatarsLine, {
	      value: _renderAvatarsLine2
	    });
	    Object.defineProperty(this, _renderMembers, {
	      value: _renderMembers2
	    });
	    Object.defineProperty(this, _getUserProfileUrl, {
	      value: _getUserProfileUrl2
	    });
	    Object.defineProperty(this, _renderModerator, {
	      value: _renderModerator2
	    });
	    Object.defineProperty(this, _renderModeratorsEmptyState, {
	      value: _renderModeratorsEmptyState2
	    });
	    Object.defineProperty(this, _renderMoreModerators, {
	      value: _renderMoreModerators2
	    });
	    Object.defineProperty(this, _renderModerators, {
	      value: _renderModerators2
	    });
	    Object.defineProperty(this, _renderModeratorsLine, {
	      value: _renderModeratorsLine2
	    });
	    Object.defineProperty(this, _renderOwner, {
	      value: _renderOwner2
	    });
	    Object.defineProperty(this, _params$5, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$5, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5] = {};
	  }
	  setMembers(listOfMembers) {
	    babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].listOfMembers = listOfMembers;
	    this.render();
	  }
	  render() {
	    var _babelHelpers$classPr;
	    const owner = babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].listOfMembers.find(member => member.isOwner);
	    const moderators = babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].listOfMembers.filter(member => member.isModerator);
	    const members = babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].listOfMembers.filter(member => !member.isOwner && !member.isModerator);
	    const wrap = main_core.Tag.render(_t$5 || (_t$5 = _$5`
			<div class="sn-group-settings__members-section">
				${0}
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderRoleLine)[_renderRoleLine](main_core.Loc.getMessage('SN_GROUP_SETTINGS_OWNER'), babelHelpers.classPrivateFieldLooseBase(this, _renderOwner)[_renderOwner](owner)), babelHelpers.classPrivateFieldLooseBase(this, _renderModeratorsLine)[_renderModeratorsLine](moderators), babelHelpers.classPrivateFieldLooseBase(this, _renderRoleLine)[_renderRoleLine](main_core.Loc.getMessage('SN_GROUP_SETTINGS_MEMBERS'), babelHelpers.classPrivateFieldLooseBase(this, _renderMembers)[_renderMembers](members)));
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrap) == null ? void 0 : _babelHelpers$classPr.replaceWith(wrap);
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrap = wrap;
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrap;
	  }
	}
	function _renderOwner2(owner) {
	  return main_core.Tag.render(_t2$5 || (_t2$5 = _$5`
			<div class="sn-group-settings__owner">
				${0}
				<div class="sn-group-settings__owner-info">
					<a
						class="sn-group-settings__member-name"
						href="${0}"
					>
						${0}
					</a>
					<div class="sn-group-settings__owner-position">
						${0}
					</div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderAvatar)[_renderAvatar](owner.photo), babelHelpers.classPrivateFieldLooseBase(this, _getUserProfileUrl)[_getUserProfileUrl](owner.id), main_core.Text.encode(`${owner.name} ${owner.lastName}`), main_core.Text.encode(owner.position));
	}
	function _renderModeratorsLine2(moderators) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _renderRoleLine)[_renderRoleLine](main_core.Loc.getMessage('SN_GROUP_SETTINGS_MODERATORS'), babelHelpers.classPrivateFieldLooseBase(this, _renderModerators)[_renderModerators](moderators), '--moderators');
	}
	function _renderModerators2(moderators) {
	  if (moderators.length === 0) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _renderModeratorsEmptyState)[_renderModeratorsEmptyState]();
	  }
	  const maxModerators = 4;
	  const moreModerators = moderators.length - maxModerators;
	  return main_core.Tag.render(_t3$2 || (_t3$2 = _$5`
			<div class="sn-group-settings__moderator-list">
				${0}
				${0}
			</div>
		`), moderators.slice(0, maxModerators).map(moderator => babelHelpers.classPrivateFieldLooseBase(this, _renderModerator)[_renderModerator](moderator)), babelHelpers.classPrivateFieldLooseBase(this, _renderMoreModerators)[_renderMoreModerators](moreModerators));
	}
	function _renderMoreModerators2(count) {
	  if (count <= 0) {
	    return '';
	  }
	  const moreModeratorsNode = main_core.Tag.render(_t4$2 || (_t4$2 = _$5`
			<div class="sn-group-settings__more-moderators">
				${0}
			</div>
		`), main_core.Loc.getMessage('SN_GROUP_SETTINGS_MORE_MODERATORS', {
	    '#COUNT#': count
	  }));
	  main_core.Event.bind(moreModeratorsNode, 'click', babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].onShowMembers);
	  return moreModeratorsNode;
	}
	function _renderModeratorsEmptyState2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _renderMembersLineEmptyState)[_renderMembersLineEmptyState]('--person', main_core.Loc.getMessage('SN_GROUP_SETTINGS_EMPTY_STATE_MODERATORS'));
	}
	function _renderModerator2(moderator) {
	  return main_core.Tag.render(_t5 || (_t5 = _$5`
			<div class="sn-group-settings__moderator">
				<div class="sn-group-settings__moderator-avatar-container">
					${0}
				</div>
				<a
					class="sn-group-settings__member-name"
					href="${0}"
				>
					${0}
				</a>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderAvatar)[_renderAvatar](moderator.photo), babelHelpers.classPrivateFieldLooseBase(this, _getUserProfileUrl)[_getUserProfileUrl](moderator.id), main_core.Text.encode(`${moderator.name} ${moderator.lastName}`));
	}
	function _getUserProfileUrl2(userId) {
	  return `/company/personal/user/${userId}/`;
	}
	function _renderMembers2(members) {
	  return main_core.Tag.render(_t6 || (_t6 = _$5`
			<div class="sn-group-settings__members">
				${0}
				${0}
			</div>
		`), members.length > 0 ? babelHelpers.classPrivateFieldLooseBase(this, _renderAvatarsLine)[_renderAvatarsLine](members) : babelHelpers.classPrivateFieldLooseBase(this, _renderMembersEmptyState)[_renderMembersEmptyState](), babelHelpers.classPrivateFieldLooseBase(this, _renderMembersButton)[_renderMembersButton]());
	}
	function _renderAvatarsLine2(members) {
	  const maxMembersCount = 5;
	  return main_core.Tag.render(_t7 || (_t7 = _$5`
			<div class="sn-group-settings__members-line">
				${0}
				${0}
			</div>
		`), members.slice(0, maxMembersCount).map(member => babelHelpers.classPrivateFieldLooseBase(this, _renderMember)[_renderMember](member)), babelHelpers.classPrivateFieldLooseBase(this, _renderPlusMembers)[_renderPlusMembers](members.length - maxMembersCount));
	}
	function _renderMembersEmptyState2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _renderMembersLineEmptyState)[_renderMembersLineEmptyState]('--persons-3', main_core.Loc.getMessage('SN_GROUP_SETTINGS_EMPTY_STATE_MEMBERS'));
	}
	function _renderMembersLineEmptyState2(iconClass, text) {
	  return main_core.Tag.render(_t8 || (_t8 = _$5`
			<div class="sn-group-settings__members-empty-state">
				<div class="sn-group-settings__members-empty-state-icon">
					<div class="ui-icon-set ${0}"></div>
				</div>
				<div class="sn-group-settings__members-empty-state-text">${0}</div>
			</div>
		`), iconClass, text);
	}
	function _renderMember2(member) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _renderAvatar)[_renderAvatar](member.photo);
	}
	function _renderPlusMembers2(count) {
	  if (count <= 0) {
	    return '';
	  }
	  return main_core.Tag.render(_t9 || (_t9 = _$5`
			<div class="sn-group-settings__plus-members">+ ${0}</div>
		`), count);
	}
	function _renderAvatar2(avatar, className = '') {
	  return main_core.Tag.render(_t10 || (_t10 = _$5`
			<span class="ui-icon ui-icon-common-user ${0}">
				<i style="${0}"></i>
			</span>
		`), className, babelHelpers.classPrivateFieldLooseBase(this, _isAvatar)[_isAvatar](avatar) ? `background-image: url('${avatar}')` : '');
	}
	function _renderMembersButton2() {
	  return new ui_buttons.Button({
	    className: 'sn-group-settings__members-button',
	    text: main_core.Loc.getMessage('SN_GROUP_SETTINGS_MEMBERS'),
	    color: ui_buttons.ButtonColor.LIGHT_BORDER,
	    size: ui_buttons.ButtonSize.EXTRA_SMALL,
	    round: true,
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].onShowMembers
	  }).render();
	}
	function _renderRoleLine2(role, usersNode, className) {
	  return main_core.Tag.render(_t11 || (_t11 = _$5`
			<div class="sn-group-settings__role-line ${0}">
				<div class="sn-group-settings__role">${0}</div>
				${0}
			</div>
		`), className, role, usersNode);
	}
	function _isAvatar2(avatar) {
	  return main_core.Type.isStringFilled(avatar) && avatar !== '/bitrix/images/1.gif';
	}

	let _$6 = t => t,
	  _t$6;
	var _params$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _slider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slider");
	var _layout$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _updateSliderTheme = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateSliderTheme");
	var _applyMainThemeStyles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyMainThemeStyles");
	var _getMainThemeVideo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMainThemeVideo");
	var _getSliderThemeVideo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSliderThemeVideo");
	var _getSliderBackgroundOffset = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSliderBackgroundOffset");
	var _getBackgroundNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBackgroundNode");
	var _getImBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getImBar");
	class ThemeSliderAdjuster {
	  constructor(params) {
	    Object.defineProperty(this, _getImBar, {
	      value: _getImBar2
	    });
	    Object.defineProperty(this, _getBackgroundNode, {
	      value: _getBackgroundNode2
	    });
	    Object.defineProperty(this, _getSliderBackgroundOffset, {
	      value: _getSliderBackgroundOffset2
	    });
	    Object.defineProperty(this, _getSliderThemeVideo, {
	      value: _getSliderThemeVideo2
	    });
	    Object.defineProperty(this, _getMainThemeVideo, {
	      value: _getMainThemeVideo2
	    });
	    Object.defineProperty(this, _applyMainThemeStyles, {
	      value: _applyMainThemeStyles2
	    });
	    Object.defineProperty(this, _updateSliderTheme, {
	      value: _updateSliderTheme2
	    });
	    Object.defineProperty(this, _params$6, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _slider, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$6, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$6)[_params$6] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6] = {};
	  }
	  bindSliderEvents() {
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onOpenStart', event => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _params$6)[_params$6].checkSlider(event.target)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider] = event.target;
	        babelHelpers.classPrivateFieldLooseBase(this, _updateSliderTheme)[_updateSliderTheme]();
	        main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getImBar)[_getImBar](), 'bx-im-bar-default');
	      } else {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _getImBar)[_getImBar](), 'bx-im-bar-default');
	      }
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', () => {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getImBar)[_getImBar](), 'bx-im-bar-default');
	      if (BX.SidePanel.Instance.getTopSlider() && BX.SidePanel.Instance.getTopSlider() !== babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider]) {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _getImBar)[_getImBar](), 'bx-im-bar-default');
	      }
	    });
	    new MutationObserver(() => {
	      const theme = BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId();
	      const themeStyles = document.head.querySelectorAll(`link[data-theme-id="${theme}"`);
	      Promise.all([...themeStyles].map(link => new Promise(resolve => {
	        main_core.Event.bind(link, 'load', resolve);
	      }))).then(() => babelHelpers.classPrivateFieldLooseBase(this, _updateSliderTheme)[_updateSliderTheme]());
	    }).observe(document.head, {
	      childList: true,
	      subtree: false
	    });
	  }
	}
	function _updateSliderTheme2() {
	  const backgroundNode = babelHelpers.classPrivateFieldLooseBase(this, _getBackgroundNode)[_getBackgroundNode]();
	  babelHelpers.classPrivateFieldLooseBase(this, _applyMainThemeStyles)[_applyMainThemeStyles](backgroundNode);
	  const mainThemeVideo = babelHelpers.classPrivateFieldLooseBase(this, _getMainThemeVideo)[_getMainThemeVideo]();
	  if (!mainThemeVideo) {
	    return;
	  }
	  if (main_core.Dom.attr(mainThemeVideo, 'data-theme-id') !== BX.Intranet.Bitrix24.ThemePicker.Singleton.getAppliedThemeId()) {
	    mainThemeVideo == null ? void 0 : mainThemeVideo.remove();
	    return;
	  }
	  main_core.Dom.clean(backgroundNode);
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getSliderThemeVideo)[_getSliderThemeVideo](mainThemeVideo), backgroundNode);
	  const mainVideo = mainThemeVideo.querySelector('video');
	  if (!mainVideo.dataset.pausePlayEventsBinded) {
	    mainVideo.addEventListener('pause', () => {
	      const sliderVideo = backgroundNode.querySelector('.theme-video-container video');
	      sliderVideo.currentTime = mainVideo.currentTime;
	      sliderVideo.pause();
	    });
	    mainVideo.addEventListener('play', () => {
	      const sliderVideo = backgroundNode.querySelector('.theme-video-container video');
	      sliderVideo.currentTime = mainVideo.currentTime;
	      sliderVideo.play();
	    });
	    mainVideo.dataset.pausePlayEventsBinded = true;
	  }
	}
	function _applyMainThemeStyles2(themeNode) {
	  const mainStyles = getComputedStyle(document.body);
	  main_core.Dom.style(themeNode, 'backgroundColor', mainStyles.backgroundColor);
	  main_core.Dom.style(themeNode, 'backgroundImage', mainStyles.backgroundImage);
	  main_core.Dom.style(themeNode, 'backgroundSize', mainStyles.backgroundSize);
	  main_core.Dom.style(themeNode, 'backgroundPositionX', babelHelpers.classPrivateFieldLooseBase(this, _getSliderBackgroundOffset)[_getSliderBackgroundOffset]());
	}
	function _getMainThemeVideo2() {
	  return document.querySelector('.theme-video-container[data-theme-id]');
	}
	function _getSliderThemeVideo2(mainThemeVideo) {
	  const copiedThemeVideo = mainThemeVideo.cloneNode(true);
	  main_core.Dom.attr(copiedThemeVideo, 'data-theme-id', null);
	  main_core.Dom.style(copiedThemeVideo, 'zIndex', 1);
	  main_core.Dom.style(copiedThemeVideo, 'width', '100vw');
	  main_core.Dom.style(copiedThemeVideo, 'height', '100vh');
	  const copiedVideo = copiedThemeVideo.querySelector('video');
	  main_core.Dom.style(copiedVideo, 'transform', `translateX(${babelHelpers.classPrivateFieldLooseBase(this, _getSliderBackgroundOffset)[_getSliderBackgroundOffset]()})`);
	  const mainVideo = mainThemeVideo.querySelector('video');
	  if (mainVideo.paused) {
	    copiedVideo.pause();
	  } else {
	    copiedVideo.play();
	  }
	  copiedVideo.currentTime = mainVideo.currentTime;
	  return copiedThemeVideo;
	}
	function _getSliderBackgroundOffset2() {
	  return `calc(-100vw + ${babelHelpers.classPrivateFieldLooseBase(this, _params$6)[_params$6].sliderWidth}px + ${babelHelpers.classPrivateFieldLooseBase(this, _getImBar)[_getImBar]().offsetWidth}px)`;
	}
	function _getBackgroundNode2() {
	  var _babelHelpers$classPr2;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider].getContainer().contains(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].themeBackgroundContainer)) {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].themeBackgroundContainer) == null ? void 0 : _babelHelpers$classPr.remove();
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].themeBackgroundContainer = BX.Tag.render(_t$6 || (_t$6 = _$6`
				<div
					data-id="slider-theme-background-container"
					style="
						position: absolute;
						inset: 0;
						width: 100vw;
						height: 100vh;
					"
				>
				</div>
			`));
	    babelHelpers.classPrivateFieldLooseBase(this, _slider)[_slider].getContainer().prepend(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].themeBackgroundContainer);
	  }
	  (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].themeBackgroundContainer.querySelector('.theme-video-container')) == null ? void 0 : _babelHelpers$classPr2.remove();
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].themeBackgroundContainer;
	}
	function _getImBar2() {
	  return document.getElementById('bx-im-bar');
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$6;
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _icon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	class Feature {
	  constructor(name) {
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = name;
	  }
	  getName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name)[_name];
	  }
	  getIcon() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon];
	  }
	  renderContent() {
	    return main_core.Tag.render(_t$7 || (_t$7 = _$7``));
	  }
	  render() {
	    const {
	      node,
	      headerElement,
	      toolsMore
	    } = main_core.Tag.render(_t2$6 || (_t2$6 = _$7`
			<div ref="node" class="ui-slider-section sn-side-panel__space-settings_section --active">
				<div ref="headerElement" class="sn-side-panel__space-settings_section-title">
					<div class="ui-icon-set --${0}"></div>
					<div class="sn-side-panel__space-settings_section-title-text">
						${0}
					</div>
					<div class="ui-icon-set --chevron-down"></div>
				</div>
				<div class="sn-side-panel__space-settings_section-content">
					${0}
				</div>
			</div>
		`), this.getIcon(), this.getName(), this.renderContent());
	    main_core.Event.bind(toolsMore, 'click', () => {
	      console.log('show helper sidePanel');
	    });
	    main_core.Event.bind(headerElement, 'click', () => {
	      main_core.Dom.toggleClass(node, '--active');
	    });
	    return node;
	  }
	}

	let _$8 = t => t,
	  _t$8,
	  _t2$7,
	  _t3$3;
	var _groupId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _features = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("features");
	var _name$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _icon$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	var _switcherItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switcherItems");
	var _switcherItemRender = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switcherItemRender");
	var _save = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("save");
	var _toggleActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleActive");
	var _changeName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changeName");
	class Tools extends Feature {
	  constructor(groupId, _features2) {
	    super();
	    Object.defineProperty(this, _changeName, {
	      value: _changeName2
	    });
	    Object.defineProperty(this, _toggleActive, {
	      value: _toggleActive2
	    });
	    Object.defineProperty(this, _save, {
	      value: _save2
	    });
	    Object.defineProperty(this, _switcherItemRender, {
	      value: _switcherItemRender2
	    });
	    Object.defineProperty(this, _switcherItems, {
	      value: _switcherItems2
	    });
	    Object.defineProperty(this, _groupId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _features, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _name$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon$1, {
	      writable: true,
	      value: void 0
	    });
	    const availableFeaturesList = new Set(['tasks', 'calendar', 'files']);
	    const availableFeatures = _features2.filter(feature => availableFeaturesList.has(feature.featureName));
	    babelHelpers.classPrivateFieldLooseBase(this, _groupId$1)[_groupId$1] = main_core.Type.isUndefined(groupId) ? 0 : parseInt(groupId, 10);
	    babelHelpers.classPrivateFieldLooseBase(this, _features)[_features] = new Map();
	    babelHelpers.classPrivateFieldLooseBase(this, _name$1)[_name$1] = main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_SETTINGS');
	    babelHelpers.classPrivateFieldLooseBase(this, _icon$1)[_icon$1] = 'settings';
	    availableFeatures.forEach(feature => {
	      babelHelpers.classPrivateFieldLooseBase(this, _features)[_features].set(feature.id, feature);
	    });
	  }
	  getName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name$1)[_name$1];
	  }
	  getIcon() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _icon$1)[_icon$1];
	  }
	  renderContent() {
	    const {
	      node
	    } = main_core.Tag.render(_t$8 || (_t$8 = _$8`
			<div ref="node" class="sn-side-panel__space-settings_section-content-wrapper">
				<div class="sn-side-panel__space-settings_section-content-wrapper-block">
					<div class="ui-alert ui-alert-primary ui-alert-xs">
						<div class="ui-alert-message">
							<div class="sn-side-panel__space-settings_alert-text">
								${0}
								<span ref="toolsMore" class="sn-side-panel__space-settings_alert-text-more">
									<!--${0}-->
								</span>
							</div>
						</div>
					</div>
					${0}
				</div>
			</div>
		`), main_core.Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS'), main_core.Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS_MORE'), babelHelpers.classPrivateFieldLooseBase(this, _switcherItems)[_switcherItems](babelHelpers.classPrivateFieldLooseBase(this, _features)[_features]));
	    return node;
	  }
	}
	function _switcherItems2(features) {
	  return main_core.Tag.render(_t2$7 || (_t2$7 = _$8`
			<div class="sn-side-panel__space-settings_section-content-wrapper-block-content">
				${0}
			</div>
		`), [...features.values()].map(feature => babelHelpers.classPrivateFieldLooseBase(this, _switcherItemRender)[_switcherItemRender](feature)));
	}
	function _switcherItemRender2(feature) {
	  const name = feature.customName === '' ? feature.name : feature.customName;
	  const featureCustom = feature.customName === '' ? '--standard' : '--custom';
	  const {
	    node,
	    switcherToggle,
	    switcherContainer,
	    editButton,
	    refreshButton,
	    inputFeature
	  } = main_core.Tag.render(_t3$3 || (_t3$3 = _$8`
			<div ref="node" class="sn-side-panel__space-settings_section-item ${0}">
				<div class="sn-side-panel__space-settings_section-switcher-toggle">
					<div ref="switcherContainer" class="sn-side-panel__space-settings_switcher"></div>
					<div ref="switcherToggle" class="sn-side-panel__space-settings_section-item-text">
						${0}
					</div>
					<div class="ui-ctl ui-ctl-textbox ui-ctl-inline sn-side-panel__space-settings_section-item-input-block">
						<input ref="inputFeature"  name="tasks_name" type="text" class="ui-ctl-element" data-role="feature-input-text" value="${0}">
					</div>
				</div>
			<div ref="editButton" class="ui-icon-set --pencil-40 sn-side-panel__space-settings_edit-btn" style="--ui-icon-set__icon-size: 19px;"></div>
			<div ref="refreshButton" class="ui-icon-set --undo-1 sn-side-panel__space-settings_refresh" style="--ui-icon-set__icon-size: 19px;"></div>
			</div>
		`), featureCustom, main_core.Text.encode(feature.name), main_core.Text.encode(name));
	  const switcher = new ui_switcher.Switcher({
	    node: switcherContainer,
	    size: 'extra-small',
	    color: 'primary',
	    checked: feature.active,
	    handlers: {
	      toggled: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _toggleActive)[_toggleActive](feature.id);
	        babelHelpers.classPrivateFieldLooseBase(this, _save)[_save](feature.id);
	      }
	    }
	  });
	  main_core.Event.bind(switcherToggle, 'click', () => {
	    switcher.check(!feature.active);
	  });
	  main_core.Event.bind(editButton, 'click', () => {
	    main_core.Dom.removeClass(node, '--standard');
	    main_core.Dom.addClass(node, '--custom');
	  });
	  main_core.Event.bind(refreshButton, 'click', () => {
	    main_core.Dom.addClass(node, '--standard');
	    main_core.Dom.removeClass(node, '--custom');
	    babelHelpers.classPrivateFieldLooseBase(this, _changeName)[_changeName](feature.id);
	    inputFeature.value = feature.name;
	  });
	  main_core.Event.bind(inputFeature, 'blur', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _changeName)[_changeName](feature.id, inputFeature.value.trim());
	  });
	  return node;
	}
	function _save2(featureId) {
	  socialnetwork_controller.Controller.changeFeature(babelHelpers.classPrivateFieldLooseBase(this, _groupId$1)[_groupId$1], babelHelpers.classPrivateFieldLooseBase(this, _features)[_features].get(featureId));
	}
	function _toggleActive2(featureId) {
	  const feature = babelHelpers.classPrivateFieldLooseBase(this, _features)[_features].get(featureId);
	  feature.active = !feature.active;
	  babelHelpers.classPrivateFieldLooseBase(this, _features)[_features].set(featureId, feature);
	}
	function _changeName2(featureId, name) {
	  if (name) {
	    babelHelpers.classPrivateFieldLooseBase(this, _features)[_features].get(featureId).customName = name;
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _features)[_features].get(featureId).customName = null;
	  }
	  socialnetwork_controller.Controller.changeFeature(babelHelpers.classPrivateFieldLooseBase(this, _groupId$1)[_groupId$1], babelHelpers.classPrivateFieldLooseBase(this, _features)[_features].get(featureId));
	}

	let _$9 = t => t,
	  _t$9;
	var _groupId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _name$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _icon$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	var _layout$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	class Tags extends Feature {
	  constructor(groupId) {
	    super();
	    Object.defineProperty(this, _groupId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _name$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$7, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _groupId$2)[_groupId$2] = groupId;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _name$2)[_name$2] = main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS');
	    babelHelpers.classPrivateFieldLooseBase(this, _icon$2)[_icon$2] = 'tag';
	  }
	  getName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _name$2)[_name$2];
	  }
	  getIcon() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _icon$2)[_icon$2];
	  }
	  renderContent() {
	    const {
	      node,
	      selector
	    } = main_core.Tag.render(_t$9 || (_t$9 = _$9`
			<div ref="node" class="sn-side-panel__space-settings_section-content-wrapper">
				<div
					ref="selector"
					class="sn-side-panel__space-settings_section-content-wrapper-block"
				></div>
			</div>
		`));
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].container = node;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].selectorContainer = selector;
	    return node;
	  }
	  renderSelector() {
	    const saveTags = selectedTags => {
	      const tags = [];
	      selectedTags.forEach(item => {
	        tags.push(item.id);
	      });
	      socialnetwork_controller.Controller.changeTags(babelHelpers.classPrivateFieldLooseBase(this, _groupId$2)[_groupId$2], tags);
	    };
	    const tagSelector = new ui_entitySelector.TagSelector({
	      addButtonCaption: main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_ADD'),
	      addButtonCaptionMore: main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_ADD_TAG'),
	      dialogOptions: {
	        width: 350,
	        height: 300,
	        offsetLeft: 50,
	        compactView: true,
	        preload: true,
	        context: 'PROJECT_TAG',
	        searchTabOptions: {
	          stubOptions: {
	            title: main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_SEARCH_FAILED'),
	            subtitle: main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_SEARCH_ADD_HINT'),
	            arrow: true
	          }
	        },
	        entities: [{
	          id: 'project-tag',
	          options: {
	            groupId: babelHelpers.classPrivateFieldLooseBase(this, _groupId$2)[_groupId$2]
	          }
	        }],
	        searchOptions: {
	          allowCreateItem: true,
	          footerOptions: {
	            label: main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_TAGS_ADD_FOOTER_LABEL')
	          }
	        },
	        events: {
	          'Item:onSelect': event => {
	            saveTags(event.getTarget().getSelectedItems());
	          },
	          'Item:onDeselect': event => {
	            saveTags(event.getTarget().getSelectedItems());
	          },
	          'Search:onItemCreateAsync': event => {
	            return new Promise(resolve => {
	              const {
	                searchQuery
	              } = event.getData();
	              const name = searchQuery.getQuery().toLowerCase();
	              const dialog = event.getTarget();
	              setTimeout(() => {
	                const tagsList = name.split(',');
	                tagsList.forEach(tag => {
	                  const item = dialog.addItem({
	                    id: tag,
	                    entityId: 'project-tag',
	                    title: tag,
	                    tabs: ['all', 'recents']
	                  });
	                  item.select();
	                });
	                resolve();
	              }, 1000);
	            });
	          }
	        }
	      }
	    });
	    tagSelector.renderTo(babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].selectorContainer);
	  }
	}

	let _$a = t => t,
	  _t$a,
	  _t2$8;
	var _params$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _tools = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tools");
	var _tags = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tags");
	var _onLoad = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onLoad");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _renderOtherBlock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderOtherBlock");
	var _renderLabel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLabel");
	class SpaceSettings {
	  constructor(params) {
	    Object.defineProperty(this, _renderLabel, {
	      value: _renderLabel2
	    });
	    Object.defineProperty(this, _renderOtherBlock, {
	      value: _renderOtherBlock2
	    });
	    Object.defineProperty(this, _render, {
	      value: _render2
	    });
	    Object.defineProperty(this, _onLoad, {
	      value: _onLoad2
	    });
	    Object.defineProperty(this, _params$7, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tools, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _tags, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$7)[_params$7] = params;
	  }
	  openInSlider() {
	    BX.SidePanel.Instance.open('spaces-settings-space-settings', {
	      cacheable: false,
	      title: main_core.Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS_TITLE'),
	      contentCallback: () => {
	        return socialnetwork_controller.Controller.getGroupData(babelHelpers.classPrivateFieldLooseBase(this, _params$7)[_params$7].spaceId, ['FEATURES']).then(groupData => {
	          babelHelpers.classPrivateFieldLooseBase(this, _tools)[_tools] = new Tools(babelHelpers.classPrivateFieldLooseBase(this, _params$7)[_params$7].spaceId, groupData.features);
	          babelHelpers.classPrivateFieldLooseBase(this, _tags)[_tags] = new Tags(babelHelpers.classPrivateFieldLooseBase(this, _params$7)[_params$7].spaceId);
	          return babelHelpers.classPrivateFieldLooseBase(this, _render)[_render]();
	        });
	      },
	      width: babelHelpers.classPrivateFieldLooseBase(this, _params$7)[_params$7].width,
	      events: {
	        onLoad: babelHelpers.classPrivateFieldLooseBase(this, _onLoad)[_onLoad].bind(this)
	      }
	    });
	  }
	}
	function _onLoad2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _tags)[_tags].renderSelector();
	}
	function _render2() {
	  const uiStyles = 'ui-sidepanel-layout-content ui-sidepanel-layout-content-margin';
	  const {
	    node
	  } = main_core.Tag.render(_t$a || (_t$a = _$a`
			<div ref="node" class="ui-sidepanel-layout">
				<div class="ui-sidepanel-layout-header">
					<div class="ui-sidepanel-layout-title">
						${0}
					</div>
				</div>
				<div class="${0} sn-side-panel__space-settings">
					${0}
					${0}
					${0}
				</div>
			</div>
		`), main_core.Loc.getMessage('SN_GROUP_SPACE_SETTINGS_TOOLS_TITLE'), uiStyles, babelHelpers.classPrivateFieldLooseBase(this, _tools)[_tools].render(), babelHelpers.classPrivateFieldLooseBase(this, _tags)[_tags].render(), babelHelpers.classPrivateFieldLooseBase(this, _renderOtherBlock)[_renderOtherBlock]());
	  return node;
	}
	function _renderOtherBlock2() {
	  const {
	    node
	  } = main_core.Tag.render(_t2$8 || (_t2$8 = _$a`
			<div ref="node" class="ui-slider-section sn-side-panel__space-settings_section --disabled">
				<div class="sn-side-panel__space-settings_section-title">
					<div class="ui-icon-set --more"></div>
					<div class="sn-side-panel__space-settings_section-title-text">
						${0}
						${0}
					</div>
				</div>
				<div class="sn-side-panel__space-settings_section-content">
					<div class="sn-side-panel__space-settings_section-content-wrapper">
					<div class="sn-side-panel__space-settings_section-content-wrapper-block">
					</div>
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_OTHER'), babelHelpers.classPrivateFieldLooseBase(this, _renderLabel)[_renderLabel]());
	  return node;
	}
	function _renderLabel2() {
	  const label = new ui_label.Label({
	    text: main_core.Loc.getMessage('SN_SIDE_PANEL_SPACE_SETTINGS_SOON'),
	    color: ui_label.LabelColor.PRIMARY,
	    fill: true
	  });
	  return label.render();
	}

	let _$b = t => t,
	  _t$b,
	  _t2$9,
	  _t3$4,
	  _t4$3,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9$1,
	  _t10$1;
	var _groupData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupData");
	var _logo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("logo");
	var _circle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("circle");
	var _layout$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _beforeCloseComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("beforeCloseComplete");
	var _subscribeToPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToPull");
	var _handleUpdateSpace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleUpdateSpace");
	var _update$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("update");
	var _render$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _renderThemesButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderThemesButton");
	var _renderActionsButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderActionsButton");
	var _renderSpaceCard = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSpaceCard");
	var _renderHeaderSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHeaderSection");
	var _renderSpaceAvatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSpaceAvatar");
	var _renderSpaceTitleEdit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSpaceTitleEdit");
	var _renderSpacePrivacy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSpacePrivacy");
	var _renderDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDescription");
	var _renderMembersSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMembersSection");
	var _isAvatar$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAvatar");
	var _renderFooterSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderFooterSection");
	var _getFooterText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFooterText");
	var _renderEfficiency = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEfficiency");
	var _renderEfficiencyEmptyState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEfficiencyEmptyState");
	var _renderEfficiencyHelper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEfficiencyHelper");
	var _renderEfficiencyNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEfficiencyNode");
	var _renderAccess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAccess");
	var _renderSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettings");
	var _openSpaceSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openSpaceSettings");
	class GroupSettings extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _openSpaceSettings, {
	      value: _openSpaceSettings2
	    });
	    Object.defineProperty(this, _renderSettings, {
	      value: _renderSettings2
	    });
	    Object.defineProperty(this, _renderAccess, {
	      value: _renderAccess2
	    });
	    Object.defineProperty(this, _renderEfficiencyNode, {
	      value: _renderEfficiencyNode2
	    });
	    Object.defineProperty(this, _renderEfficiencyHelper, {
	      value: _renderEfficiencyHelper2
	    });
	    Object.defineProperty(this, _renderEfficiencyEmptyState, {
	      value: _renderEfficiencyEmptyState2
	    });
	    Object.defineProperty(this, _renderEfficiency, {
	      value: _renderEfficiency2
	    });
	    Object.defineProperty(this, _getFooterText, {
	      value: _getFooterText2
	    });
	    Object.defineProperty(this, _renderFooterSection, {
	      value: _renderFooterSection2
	    });
	    Object.defineProperty(this, _isAvatar$1, {
	      value: _isAvatar2$1
	    });
	    Object.defineProperty(this, _renderMembersSection, {
	      value: _renderMembersSection2
	    });
	    Object.defineProperty(this, _renderDescription, {
	      value: _renderDescription2
	    });
	    Object.defineProperty(this, _renderSpacePrivacy, {
	      value: _renderSpacePrivacy2
	    });
	    Object.defineProperty(this, _renderSpaceTitleEdit, {
	      value: _renderSpaceTitleEdit2
	    });
	    Object.defineProperty(this, _renderSpaceAvatar, {
	      value: _renderSpaceAvatar2
	    });
	    Object.defineProperty(this, _renderHeaderSection, {
	      value: _renderHeaderSection2
	    });
	    Object.defineProperty(this, _renderSpaceCard, {
	      value: _renderSpaceCard2
	    });
	    Object.defineProperty(this, _renderActionsButton, {
	      value: _renderActionsButton2
	    });
	    Object.defineProperty(this, _renderThemesButton, {
	      value: _renderThemesButton2
	    });
	    Object.defineProperty(this, _render$1, {
	      value: _render2$1
	    });
	    Object.defineProperty(this, _update$1, {
	      value: _update2$1
	    });
	    Object.defineProperty(this, _handleUpdateSpace, {
	      value: _handleUpdateSpace2
	    });
	    Object.defineProperty(this, _subscribeToPull, {
	      value: _subscribeToPull2
	    });
	    Object.defineProperty(this, _beforeCloseComplete, {
	      value: _beforeCloseComplete2
	    });
	    Object.defineProperty(this, _groupData, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _logo, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _circle, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$8, {
	      writable: true,
	      value: void 0
	    });
	    this.SLIDER_WIDTH = 491;
	    this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Page');
	    babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData] = params.groupData;
	    babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo] = params.logo;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isAvatar$1)[_isAvatar$1](babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].avatar)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo].id = encodeURI(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].avatar);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToPull)[_subscribeToPull]();
	    new ThemeSliderAdjuster({
	      sliderWidth: this.SLIDER_WIDTH,
	      checkSlider: slider => {
	        var _slider$options$data;
	        return ((_slider$options$data = slider.options.data) == null ? void 0 : _slider$options$data.spaceSettingsSliderId) === babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id;
	      }
	    }).bindSliderEvents();
	  }
	  openInSlider() {
	    BX.SidePanel.Instance.open('spaces-settings-space-info-page', {
	      cacheable: false,
	      contentCallback: slider => {
	        main_core.Dom.addClass(slider.getOverlay(), 'sn-group-settings_overlay');
	        return babelHelpers.classPrivateFieldLooseBase(this, _render$1)[_render$1]();
	      },
	      width: this.SLIDER_WIDTH,
	      data: {
	        spaceSettingsSliderId: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id
	      },
	      events: {
	        onBeforeCloseComplete: babelHelpers.classPrivateFieldLooseBase(this, _beforeCloseComplete)[_beforeCloseComplete].bind(this)
	      }
	    });
	  }
	}
	function _beforeCloseComplete2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _circle)[_circle]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _circle)[_circle].stop();
	  }
	}
	function _subscribeToPull2() {
	  const pullRequests = new PullRequests(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id);
	  pullRequests.subscribe('update', babelHelpers.classPrivateFieldLooseBase(this, _handleUpdateSpace)[_handleUpdateSpace].bind(this));
	  pull_client.PULL.subscribe(pullRequests);
	}
	function _handleUpdateSpace2() {
	  socialnetwork_controller.Controller.getGroupData(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id, ['AVATAR', 'ACTIONS', 'NUMBER_OF_MEMBERS', 'LIST_OF_MEMBERS', 'GROUP_MEMBERS_LIST', 'PRIVACY_TYPE', 'PIN', 'USER_DATA', 'COUNTERS', 'DESCRIPTION', 'EFFICIENCY', 'SUBJECT_DATA', 'DATE_CREATE']).then(groupData => {
	    babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData] = groupData;
	    babelHelpers.classPrivateFieldLooseBase(this, _update$1)[_update$1](babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData]);
	  });
	}
	function _update2$1(groupData) {
	  babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData] = groupData;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isAvatar$1)[_isAvatar$1](babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].avatar)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo] = {
	      id: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].avatar,
	      type: 'image'
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editAvatar.setAvatar(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].avatar);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editTitle.setTitle(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].name);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editPrivacy.setPrivacy(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].privacyCode);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].membersSection.setMembers(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].listOfMembers);
	}
	function _render2$1() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].wrap = main_core.Tag.render(_t$b || (_t$b = _$b`
			<div class="sn-group-settings__container">
				<div class="sn-group-settings__toolbar">
					${0}
				</div>
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderActionsButton)[_renderActionsButton](), babelHelpers.classPrivateFieldLooseBase(this, _renderSpaceCard)[_renderSpaceCard](), babelHelpers.classPrivateFieldLooseBase(this, _renderFooterSection)[_renderFooterSection]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].wrap;
	}
	function _renderThemesButton2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions.canEdit) {
	    return '';
	  }
	  return new ui_buttons.Button({
	    className: 'sn-group-settings__icon-themes',
	    text: main_core.Loc.getMessage('SN_GROUP_SETTINGS_THEMES'),
	    color: ui_buttons.ButtonColor.LIGHT_BORDER,
	    size: ui_buttons.ButtonSize.MEDIUM,
	    dependOnTheme: true,
	    round: true,
	    onclick: () => BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog()
	  }).render();
	}
	function _renderActionsButton2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].actionsButton = new ActionsButton({
	    isPin: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].isPin,
	    isSubscribed: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].isSubscribed,
	    actions: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions,
	    pinChanged: isPin => {
	      babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].isPin = isPin;
	      socialnetwork_controller.Controller.changePin(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id, babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].isPin);
	    },
	    followChanged: isSubscribed => {
	      babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].isSubscribed = isSubscribed;
	      socialnetwork_controller.Controller.setSubscription(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id, babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].isSubscribed);
	    },
	    leave: () => socialnetwork_controller.Controller.leaveGroup(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id).then(() => socialnetwork_controller.Controller.openCommonSpace()),
	    delete: () => {
	      socialnetwork_controller.Controller.deleteGroup(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id).then(response => {
	        const errorMessage = response.data;
	        if (main_core.Type.isStringFilled(errorMessage)) {
	          new ui_dialogs_messagebox.MessageBox({
	            message: errorMessage,
	            buttons: ui_dialogs_messagebox.MessageBoxButtons.OK
	          }).show();
	        } else {
	          socialnetwork_controller.Controller.openCommonSpace();
	        }
	      });
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].actionsButton.render();
	}
	function _renderSpaceCard2() {
	  return main_core.Tag.render(_t2$9 || (_t2$9 = _$b`
			<div class="sn-group-settings__card">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderHeaderSection)[_renderHeaderSection](), babelHelpers.classPrivateFieldLooseBase(this, _renderMembersSection)[_renderMembersSection]());
	}
	function _renderHeaderSection2() {
	  return main_core.Tag.render(_t3$4 || (_t3$4 = _$b`
			<div class="sn-group-settings__header-section">
				<div class="sn-group-settings__header">
					${0}
					<div class="sn-group-settings__header-title">
						${0}
						${0}
					</div>
				</div>
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderSpaceAvatar)[_renderSpaceAvatar](), babelHelpers.classPrivateFieldLooseBase(this, _renderSpaceTitleEdit)[_renderSpaceTitleEdit](), babelHelpers.classPrivateFieldLooseBase(this, _renderSpacePrivacy)[_renderSpacePrivacy](), babelHelpers.classPrivateFieldLooseBase(this, _renderDescription)[_renderDescription]());
	}
	function _renderSpaceAvatar2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editAvatar = new EditAvatar({
	    logo: babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo],
	    canEdit: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions.canEdit,
	    onChange: file => {
	      socialnetwork_controller.Controller.updatePhoto(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id, file);
	      this.emit('changeAvatar', file);
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editAvatar.render();
	}
	function _renderSpaceTitleEdit2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editTitle = new EditTitle({
	    name: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].name,
	    canEdit: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions.canEdit,
	    onChange: name => {
	      babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].name = name;
	      socialnetwork_controller.Controller.changeTitle(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id, babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].name);
	      this.emit('changeTitle', babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].name);
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editTitle.render();
	}
	function _renderSpacePrivacy2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editPrivacy = new EditPrivacy({
	    privacyCode: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].privacyCode,
	    canEdit: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions.canEdit,
	    onChange: privacyCode => {
	      babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].privacyCode = privacyCode;
	      socialnetwork_controller.Controller.changePrivacy(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id, babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].privacyCode);
	      this.emit('changePrivacy', babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].privacyCode);
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editPrivacy.render();
	}
	function _renderDescription2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editDescription = new EditDescription({
	    description: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].description,
	    canEdit: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions.canEdit,
	    onChange: description => {
	      babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].description = description;
	      socialnetwork_controller.Controller.changeDescription(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id, babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].description);
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].editDescription.render();
	}
	function _renderMembersSection2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].membersSection = new MembersSection({
	    listOfMembers: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].listOfMembers,
	    onShowMembers: () => socialnetwork_controller.Controller.openGroupUsers('all')
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].membersSection.render();
	}
	function _isAvatar2$1(avatar) {
	  return main_core.Type.isStringFilled(avatar) && avatar !== '/bitrix/images/1.gif';
	}
	function _renderFooterSection2() {
	  return main_core.Tag.render(_t4$3 || (_t4$3 = _$b`
			<div class="sn-group-settings__footer-section">
				<div class="sn-group-settings__footer-main">
					${0}
					<div class="sn-group-settings__footer-buttons-container">
						${0}
						${0}
					</div>
				</div>
				<div class="sn-group-settings__footer">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderEfficiency)[_renderEfficiency](), babelHelpers.classPrivateFieldLooseBase(this, _renderAccess)[_renderAccess](), babelHelpers.classPrivateFieldLooseBase(this, _renderSettings)[_renderSettings](), babelHelpers.classPrivateFieldLooseBase(this, _getFooterText)[_getFooterText]());
	}
	function _getFooterText2() {
	  return main_core.Loc.getMessage('SN_GROUP_SETTINGS_SPACE_CARD_FOOTER', {
	    '#DATE#': babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].dateCreate
	  });
	}
	function _renderEfficiency2() {
	  if (!main_core.Type.isNumber(babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].efficiency)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _renderEfficiencyEmptyState)[_renderEfficiencyEmptyState]();
	  }
	  return main_core.Tag.render(_t5$1 || (_t5$1 = _$b`
			<div class="sn-group-settings__efficiency-container">
				${0}
				<div class="sn-group-settings__efficiency-text">
					${0}
				</div>
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderEfficiencyNode)[_renderEfficiencyNode](), main_core.Loc.getMessage('SN_GROUP_SETTINGS_SPACE_EFFICIENCY'), babelHelpers.classPrivateFieldLooseBase(this, _renderEfficiencyHelper)[_renderEfficiencyHelper]());
	}
	function _renderEfficiencyEmptyState2() {
	  return main_core.Tag.render(_t6$1 || (_t6$1 = _$b`
			<div class="sn-group-settings__efficiency-container">
				<div class="sn-group-settings__efficiency-container-empty-state-icon"></div>
				<div class="sn-group-settings__efficiency-text">
					${0}
				</div>
			</div>
		`), main_core.Loc.getMessage('SN_GROUP_SETTINGS_SPACE_EFFICIENCY_UNAVAILABLE'));
	}
	function _renderEfficiencyHelper2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].efficiencyHelper = main_core.Tag.render(_t7$1 || (_t7$1 = _$b`
			<div class="sn-group-settings__efficiency-helper">
				${0}
			</div>
		`), main_core.Loc.getMessage('SN_GROUP_SETTINGS_SPACE_EFFICIENCY_HELPER'));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].efficiencyHelper, 'click', () => {
	    top.BX.Helper.show('redirect=detail&code=6576263');
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].efficiencyHelper;
	}
	function _renderEfficiencyNode2() {
	  const efficiencyNode = main_core.Tag.render(_t8$1 || (_t8$1 = _$b`
			<div class="sn-group-settings__efficiency"></div>
		`));
	  main_core.Runtime.loadExtension('ui.graph.circle').then(exports => {
	    const {
	      Circle
	    } = exports;
	    babelHelpers.classPrivateFieldLooseBase(this, _circle)[_circle] = new Circle(efficiencyNode, 100, babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].efficiency, null, null);
	    babelHelpers.classPrivateFieldLooseBase(this, _circle)[_circle].show();
	  });
	  return efficiencyNode;
	}
	function _renderAccess2() {
	  const canEdit = babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions.canEditFeatures;
	  const rolesNode = main_core.Tag.render(_t9$1 || (_t9$1 = _$b`
			<div class="sn-group-settings__footer-button ${0}">
				<div class="ui-icon-set --lock"></div>
				<div>${0}</div>
			</div>
		`), canEdit ? '' : '--disabled', main_core.Loc.getMessage('SN_GROUP_SETTINGS_MENU_ROLES'));
	  if (canEdit) {
	    main_core.Event.bind(rolesNode, 'click', () => socialnetwork_controller.Controller.openGroupFeatures());
	  }
	  return rolesNode;
	}
	function _renderSettings2() {
	  const canEdit = babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].actions.canEdit;
	  const settingsNode = main_core.Tag.render(_t10$1 || (_t10$1 = _$b`
			<div class="sn-group-settings__footer-button ${0}">
				<div class="ui-icon-set --settings-4"></div>
				<div>${0}</div>
			</div>
		`), canEdit ? '' : '--disabled', main_core.Loc.getMessage('SN_GROUP_SETTINGS_SPACE_SETTINGS'));
	  if (canEdit) {
	    main_core.Event.bind(settingsNode, 'click', babelHelpers.classPrivateFieldLooseBase(this, _openSpaceSettings)[_openSpaceSettings].bind(this));
	  }
	  return settingsNode;
	}
	function _openSpaceSettings2() {
	  new SpaceSettings({
	    width: this.SLIDER_WIDTH,
	    spaceId: babelHelpers.classPrivateFieldLooseBase(this, _groupData)[_groupData].id
	  }).openInSlider();
	}

	exports.GroupSettings = GroupSettings;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX,BX.Socialnetwork,BX.UI.AvatarEditor,BX.Socialnetwork,BX.Main,BX.UI,BX.UI,BX.UI,BX,BX.Event,BX.UI.EntitySelector,BX.Socialnetwork,BX.UI,BX.UI.Sidepanel.Content,BX.UI.Dialogs));
//# sourceMappingURL=group-settings.bundle.js.map
