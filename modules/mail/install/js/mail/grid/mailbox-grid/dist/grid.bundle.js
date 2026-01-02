/* eslint-disable */
this.BX = this.BX || {};
this.BX.Mail = this.BX.Mail || {};
(function (exports,ui_cnt,main_date,ui_notification,ui_label,main_popup,ui_avatar,main_core,ui_buttons) {
	'use strict';

	var _fieldId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fieldId");
	var _gridId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridId");
	class BaseField {
	  constructor(params) {
	    var _params$gridId;
	    Object.defineProperty(this, _fieldId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _gridId, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _fieldId)[_fieldId] = params.fieldId;
	    babelHelpers.classPrivateFieldLooseBase(this, _gridId)[_gridId] = (_params$gridId = params.gridId) != null ? _params$gridId : null;
	  }
	  getGridId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _gridId)[_gridId];
	  }
	  getFieldId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _fieldId)[_fieldId];
	  }
	  getGrid() {
	    var _grid;
	    let grid = null;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _gridId)[_gridId]) {
	      grid = BX.Main.gridManager.getById(babelHelpers.classPrivateFieldLooseBase(this, _gridId)[_gridId]);
	    }
	    return (_grid = grid) == null ? void 0 : _grid.instance;
	  }
	  getFieldNode() {
	    return document.getElementById(this.getFieldId());
	  }
	  appendToFieldNode(element) {
	    main_core.Dom.append(element, this.getFieldNode());
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _getFullNameLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFullNameLink");
	var _getPositionLabelContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPositionLabelContainer");
	class FullNameField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getPositionLabelContainer, {
	      value: _getPositionLabelContainer2
	    });
	    Object.defineProperty(this, _getFullNameLink, {
	      value: _getFullNameLink2
	    });
	  }
	  render(params) {
	    const fullNameContainer = main_core.Tag.render(_t || (_t = _`
			<div class="mailbox-grid_full-name-container">${0}</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getFullNameLink)[_getFullNameLink](params.name, params.pathToProfile));
	    if (params.position !== '') {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getPositionLabelContainer)[_getPositionLabelContainer](main_core.Text.encode(params.position)), fullNameContainer);
	    }
	    this.appendToFieldNode(fullNameContainer);
	  }
	}
	function _getFullNameLink2(fullName, profileLink) {
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<a class="mailbox-grid_full-name-label" href="${0}">
				${0}
			</a>
		`), profileLink, main_core.Text.encode(fullName));
	}
	function _getPositionLabelContainer2(position) {
	  return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="mailbox-grid_position-label">
				${0}
			</div>
		`), main_core.Text.encode(position));
	}

	class AvatarField extends BaseField {
	  render(avatarPath) {
	    const avatarOptions = {
	      size: 28
	    };
	    if (avatarPath) {
	      avatarOptions.userpicPath = encodeURI(avatarPath);
	    }
	    const avatar = new ui_avatar.AvatarRound(avatarOptions);
	    avatar == null ? void 0 : avatar.renderTo(this.getFieldNode());
	    main_core.Dom.addClass(this.getFieldNode(), 'mailbox-grid_owner-photo');
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1;
	class EmployeeField extends BaseField {
	  render(params) {
	    var _params$avatar;
	    const avatarFieldId = main_core.Text.getRandom(6);
	    const fullNameFieldId = main_core.Text.getRandom(6);
	    this.appendToFieldNode(main_core.Tag.render(_t$1 || (_t$1 = _$1`<span id="${0}"></span>`), avatarFieldId));
	    this.appendToFieldNode(main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<span class="mailbox-grid_full-name-wrapper" id="${0}"></span>`), fullNameFieldId));
	    new AvatarField({
	      fieldId: avatarFieldId
	    }).render((_params$avatar = params.avatar) == null ? void 0 : _params$avatar.src);
	    new FullNameField({
	      fieldId: fullNameFieldId
	    }).render(params);
	    main_core.Dom.addClass(this.getFieldNode(), 'mailbox-grid_employee-card-container');
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2;
	var _senderName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("senderName");
	var _renderEmpty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEmpty");
	var _renderSenderName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSenderName");
	class SenderNameField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _renderSenderName, {
	      value: _renderSenderName2
	    });
	    Object.defineProperty(this, _renderEmpty, {
	      value: _renderEmpty2
	    });
	    Object.defineProperty(this, _senderName, {
	      writable: true,
	      value: void 0
	    });
	  }
	  render(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _senderName)[_senderName] = params.senderName;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _senderName)[_senderName] === '') {
	      babelHelpers.classPrivateFieldLooseBase(this, _renderEmpty)[_renderEmpty]();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _renderSenderName)[_renderSenderName]();
	  }
	}
	function _renderEmpty2() {
	  const emptyContainer = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="mailbox-grid_sender-name --empty">
			</div>
		`));
	  this.appendToFieldNode(emptyContainer);
	}
	function _renderSenderName2() {
	  const senderNameContainer = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="mailbox-grid_sender-name-container">
				${0}
			</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _senderName)[_senderName]));
	  this.appendToFieldNode(senderNameContainer);
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$1;
	var _renderProviderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderProviderIcon");
	var _getProviderKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getProviderKey");
	var _getProviderImgSrcClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getProviderImgSrcClass");
	class EmailWithCounterField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getProviderImgSrcClass, {
	      value: _getProviderImgSrcClass2
	    });
	    Object.defineProperty(this, _getProviderKey, {
	      value: _getProviderKey2
	    });
	    Object.defineProperty(this, _renderProviderIcon, {
	      value: _renderProviderIcon2
	    });
	  }
	  render(params) {
	    const counterNode = this.renderCounter(params.count, params.isOverLimit, params.counterHintText);
	    const iconNode = babelHelpers.classPrivateFieldLooseBase(this, _renderProviderIcon)[_renderProviderIcon](params.serviceName);
	    const emailContainer = main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div class="mailbox-grid_email-container">
				${0}
				<span class="mailbox-grid_email-text">${0}</span>
				${0}
			</div>
		`), iconNode, main_core.Text.encode(params.email), counterNode);
	    this.appendToFieldNode(emailContainer);
	    BX.UI.Hint.init(this.getFieldNode());
	  }
	  renderCounter(count, isOverLimit, hintText) {
	    if (!(main_core.Type.isNumber(count) && count > 0)) {
	      return null;
	    }
	    const maxValue = count;
	    const value = isOverLimit ? count + 1 : count;
	    const counter = new ui_cnt.Counter({
	      value,
	      maxValue,
	      useAirDesign: true,
	      style: ui_cnt.CounterStyle.FILLED_NO_ACCENT
	    });
	    const counterNode = main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
			<div class="mailbox-grid_counter-container">
				${0}
			</div>
		`), counter.getContainer());
	    if (main_core.Type.isStringFilled(hintText)) {
	      main_core.Dom.attr(counterNode, {
	        'data-hint': hintText,
	        'data-hint-no-icon': 'true'
	      });
	    }
	    return counterNode;
	  }
	}
	function _renderProviderIcon2(serviceName) {
	  if (!main_core.Type.isStringFilled(serviceName)) {
	    return null;
	  }
	  const iconKey = babelHelpers.classPrivateFieldLooseBase(this, _getProviderKey)[_getProviderKey](serviceName);
	  const iconClass = babelHelpers.classPrivateFieldLooseBase(this, _getProviderImgSrcClass)[_getProviderImgSrcClass](iconKey);
	  return main_core.Tag.render(_t3$1 || (_t3$1 = _$3`
			<div class="mail-provider-img-container --grid-view">
				<div class="mailbox-grid_email-icon">
					<div class="mail-provider-img ${0}"></div>
				</div>
			</div>
		`), iconClass);
	}
	function _getProviderKey2(name) {
	  switch (name) {
	    case 'aol':
	      return 'aol';
	    case 'gmail':
	      return 'gmail';
	    case 'yahoo':
	      return 'yahoo';
	    case 'mail.ru':
	    case 'mailru':
	      return 'mailru';
	    case 'icloud':
	      return 'icloud';
	    case 'outlook.com':
	    case 'outlook':
	      return 'outlook';
	    case 'office365':
	      return 'office365';
	    case 'exchangeOnline':
	    case 'exchange':
	      return 'exchange';
	    case 'yandex':
	      return 'yandex';
	    case 'ukr.net':
	      return 'ukrnet';
	    case 'other':
	    case 'imap':
	      return 'other';
	    default:
	      return '';
	  }
	}
	function _getProviderImgSrcClass2(name) {
	  return `mail-provider-${name}-img`;
	}

	/**
	 * @abstract
	 */
	class BaseAction {
	  /**
	   * @abstract
	   */
	  static getActionId() {
	    throw new Error('not implemented');
	  }

	  /**
	   * @abstract
	   * @returns {ActionConfig}
	   */
	  getActionConfig() {
	    throw new Error('not implemented');
	  }
	  constructor(params) {
	    this.grid = params.grid;
	  }
	  setActionParams(params) {}
	  getActionData() {
	    return {};
	  }
	  async execute() {
	    this.onBeforeActionRequest();
	    await this.sendActionRequest();
	    this.onAfterActionRequest();
	  }
	  onBeforeActionRequest() {}
	  async sendActionRequest() {
	    try {
	      const result = await new Promise((resolve, reject) => {
	        const actionConfig = this.getActionConfig();
	        const actionData = this.getActionData();
	        const ajaxOptions = {
	          ...actionConfig.options,
	          data: actionData
	        };
	        let ajaxPromise = null;
	        switch (actionConfig.type) {
	          case 'controller':
	            ajaxPromise = BX.ajax.runAction(actionConfig.name, ajaxOptions);
	            break;
	          case 'component':
	            ajaxPromise = BX.ajax.runComponentAction(actionConfig.component, actionConfig.name, ajaxOptions);
	            break;
	          default:
	            {
	              const errorMessage = `Unknown action type: ${actionConfig.type}`;
	              const error = new Error(errorMessage);
	              error.errors = [{
	                message: errorMessage
	              }];
	              reject(error);
	              return;
	            }
	        }
	        ajaxPromise.then(resolve, reject);
	      });
	      this.handleSuccess(result);
	    } catch (result) {
	      this.handleError(result);
	    }
	  }
	  onAfterActionRequest() {}
	  handleSuccess(result) {}
	  handleError(result) {}
	}

	class SyncAction extends BaseAction {
	  static getActionId() {
	    return 'syncAction';
	  }
	  getActionConfig() {
	    return {
	      type: 'component',
	      component: 'bitrix:mail.client',
	      name: 'syncMailbox',
	      options: {
	        mode: 'ajax'
	      }
	    };
	  }
	  getActionData() {
	    return {
	      id: this.mailboxId,
	      dir: 'INBOX',
	      onlySyncCurrent: true
	    };
	  }
	  setActionParams(params) {
	    this.mailboxId = params.mailboxId;
	  }
	  onBeforeActionRequest() {
	    this.grid.tableFade();
	    const toastMessage = String(main_core.Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_SYNC_START'));
	    BX.UI.Notification.Center.notify({
	      content: toastMessage,
	      position: 'top-right',
	      autoHideDelay: 3000
	    });
	  }
	  onAfterActionRequest() {
	    this.grid.reload(() => {
	      this.grid.tableUnfade();
	    });
	  }
	}

	const actionMap = new Map([[SyncAction.getActionId(), SyncAction]]);
	class ActionFactory {
	  static create(actionId, options) {
	    const ActionClass = actionMap.get(actionId);
	    if (ActionClass) {
	      return new ActionClass(options);
	    }
	    return null;
	  }
	}

	var _grid = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("grid");
	class GridManager {
	  constructor(gridId) {
	    var _BX$Main$gridManager$;
	    Object.defineProperty(this, _grid, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid] = (_BX$Main$gridManager$ = BX.Main.gridManager.getById(gridId)) == null ? void 0 : _BX$Main$gridManager$.instance;
	  }
	  static getInstance(gridId) {
	    if (!this.instances[gridId]) {
	      this.instances[gridId] = new GridManager(gridId);
	    }
	    return this.instances[gridId];
	  }
	  getGrid() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid];
	  }
	  runAction(config) {
	    const actionId = config.actionId;
	    const options = config.options;
	    options.grid = babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid];
	    const action = ActionFactory.create(actionId, options);
	    if (action) {
	      const params = config.params;
	      action.setActionParams(params);
	      action.execute();
	    }
	  }
	}
	GridManager.instances = [];

	let _$4 = t => t,
	  _t$4,
	  _t2$4,
	  _t3$2,
	  _t4;
	var _getLastSyncContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLastSyncContainer");
	var _getLastSyncButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLastSyncButton");
	var _getErrorMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getErrorMessage");
	class LastSyncField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getErrorMessage, {
	      value: _getErrorMessage2
	    });
	    Object.defineProperty(this, _getLastSyncButton, {
	      value: _getLastSyncButton2
	    });
	    Object.defineProperty(this, _getLastSyncContainer, {
	      value: _getLastSyncContainer2
	    });
	  }
	  render(params) {
	    const lastSyncContainer = main_core.Tag.render(_t$4 || (_t$4 = _$4`
			<div class="mailbox-grid_last-sync-container"></div>
		`));
	    if (params.hasError) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getErrorMessage)[_getErrorMessage](), lastSyncContainer);
	    } else {
	      if (params.lastSync) {
	        main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getLastSyncContainer)[_getLastSyncContainer](params.lastSync), lastSyncContainer);
	      }
	      if (params.mailboxId) {
	        main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getLastSyncButton)[_getLastSyncButton](params.mailboxId), lastSyncContainer);
	      }
	    }
	    this.appendToFieldNode(lastSyncContainer);
	  }
	}
	function _getLastSyncContainer2(lastSync) {
	  let formattedTime = lastSync;
	  if (/^\d+$/.test(lastSync)) {
	    const timestamp = parseInt(lastSync, 10);
	    formattedTime = main_date.DateTimeFormat.formatLastActivityDate(timestamp);
	  }
	  return main_core.Tag.render(_t2$4 || (_t2$4 = _$4`
			<span class="mailbox-grid_last-sync-text">${0}</span>
		`), main_core.Text.encode(formattedTime));
	}
	function _getLastSyncButton2(mailboxId) {
	  const button = main_core.Tag.render(_t3$2 || (_t3$2 = _$4`
			<div class="mailbox-grid_last-sync-button ui-icon-set --o-refresh" data-test-id="mailbox-grid_refresh-button"></div>
		`));
	  main_core.Event.bind(button, 'click', () => {
	    GridManager.getInstance(this.getGrid().containerId).runAction({
	      actionId: 'syncAction',
	      options: {},
	      params: {
	        mailboxId
	      }
	    });
	  });
	  return button;
	}
	function _getErrorMessage2() {
	  return main_core.Tag.render(_t4 || (_t4 = _$4`
			<span class="mailbox-grid_last-sync-error-message">
				${0}
			</span>
		`), main_core.Loc.getMessage('MAIL_MAILBOX_LIST_LAST_SYNC_ERROR_MESSAGE'));
	}

	let _$5 = t => t,
	  _t$5;
	var _getStatusLabel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStatusLabel");
	class CRMStatusField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getStatusLabel, {
	      value: _getStatusLabel2
	    });
	  }
	  render(params) {
	    const crmStatusContainer = main_core.Tag.render(_t$5 || (_t$5 = _$5`
			<div class="mailbox-grid_active-status-container">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getStatusLabel)[_getStatusLabel](params.enabled));
	    this.appendToFieldNode(crmStatusContainer);
	  }
	}
	function _getStatusLabel2(active) {
	  const labelText = active ? main_core.Loc.getMessage('MAIL_MAILBOX_LIST_FIELD_CRM_STATUS_ENABLED') : main_core.Loc.getMessage('MAIL_MAILBOX_LIST_FIELD_CRM_STATUS_DISABLED');
	  const labelClass = active ? 'mailbox-grid_crm-status-label-success' : 'mailbox-grid_crm-status-label-danger';
	  return new ui_label.Label({
	    text: labelText,
	    size: ui_label.LabelSize.LG,
	    customClass: labelClass
	  }).render();
	}

	let _$6 = t => t,
	  _t$6,
	  _t2$5;
	var _users = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("users");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _targetNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("targetNode");
	var _renderUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderUser");
	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");
	class UserListPopup {
	  constructor(params) {
	    Object.defineProperty(this, _getContent, {
	      value: _getContent2
	    });
	    Object.defineProperty(this, _renderUser, {
	      value: _renderUser2
	    });
	    Object.defineProperty(this, _users, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _targetNode, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _users)[_users] = params.users;
	    babelHelpers.classPrivateFieldLooseBase(this, _targetNode)[_targetNode] = params.targetNode;
	  }
	  show() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].show();
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	      id: `users-with-avatars-popup-${main_core.Text.getRandom()}`,
	      bindElement: babelHelpers.classPrivateFieldLooseBase(this, _targetNode)[_targetNode],
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent](),
	      lightShadow: true,
	      autoHide: true,
	      closeByEsc: true,
	      className: 'popup-window-mailbox-user-list',
	      bindOptions: {
	        position: 'top'
	      },
	      animationOptions: {
	        show: {
	          type: 'opacity-transform'
	        },
	        close: {
	          type: 'opacity'
	        }
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].show();
	  }
	}
	function _renderUser2(user) {
	  var _user$avatar;
	  const userpicSize = 20;
	  let avatarNode = null;
	  if (main_core.Type.isStringFilled((_user$avatar = user.avatar) == null ? void 0 : _user$avatar.src)) {
	    const avatar = new BX.UI.AvatarRound({
	      size: userpicSize,
	      userName: user.name,
	      userpicPath: encodeURI(user.avatar.src)
	    });
	    avatarNode = avatar.getContainer();
	  } else {
	    const avatar = new BX.UI.AvatarRound({
	      size: userpicSize
	    });
	    avatarNode = avatar.getContainer();
	  }
	  let userNodeTag = 'div';
	  let userNodeAttributes = {};
	  let userNameClass = 'mailbox-grid_user-list-popup-popup-name';
	  if (main_core.Type.isStringFilled(user.pathToProfile)) {
	    userNodeTag = 'a';
	    userNameClass = 'mailbox-grid_user-list-popup-popup-name-link';
	    userNodeAttributes = {
	      href: user.pathToProfile,
	      target: '_blank',
	      title: user.name
	    };
	  }
	  const attributesString = Object.entries(userNodeAttributes).map(([key, value]) => `${key}="${main_core.Text.encode(value)}"`).join(' ');
	  return main_core.Tag.render(_t$6 || (_t$6 = _$6`
			<${0} ${0} class="mailbox-grid_user-list-popup-popup-img">
				<span class="mailbox-grid_user-list-popup-popup-avatar-new">
					${0}
				</span>
				<span class="${0}">
					${0}
				</span>
			</${0}>
		`), userNodeTag, attributesString, avatarNode, userNameClass, main_core.Text.encode(user.name), userNodeTag);
	}
	function _getContent2() {
	  const userNodes = document.createDocumentFragment();
	  babelHelpers.classPrivateFieldLooseBase(this, _users)[_users].forEach(user => {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderUser)[_renderUser](user), userNodes);
	  });
	  return main_core.Tag.render(_t2$5 || (_t2$5 = _$6`
			<div class="mailbox-grid_user-list-popup-wrap-block">
				<span class="mailbox-grid_user-list-popup-popup-name-link contentview-name">
					${0}
				</span>
				<div class="mailbox-grid_user-list-popup-popup-outer">
					<div class="mailbox-grid_user-list-popup-popup">
						${0}
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('MAIL_MAILBOX_LIST_POPUP_USERS_WITH_AVATARS_TITLE'), userNodes);
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$6,
	  _t3$3,
	  _t4$1,
	  _t5;
	var _users$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("users");
	var _popup$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _renderEmpty$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEmpty");
	var _renderUsers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderUsers");
	var _renderSingleUserLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSingleUserLayout");
	var _renderMultipleUsersLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMultipleUsersLayout");
	var _showUsersPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showUsersPopup");
	var _renderUserAvatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderUserAvatar");
	var _renderCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCounter");
	class UsersWithAvatarsField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _renderCounter, {
	      value: _renderCounter2
	    });
	    Object.defineProperty(this, _renderUserAvatar, {
	      value: _renderUserAvatar2
	    });
	    Object.defineProperty(this, _showUsersPopup, {
	      value: _showUsersPopup2
	    });
	    Object.defineProperty(this, _renderMultipleUsersLayout, {
	      value: _renderMultipleUsersLayout2
	    });
	    Object.defineProperty(this, _renderSingleUserLayout, {
	      value: _renderSingleUserLayout2
	    });
	    Object.defineProperty(this, _renderUsers, {
	      value: _renderUsers2
	    });
	    Object.defineProperty(this, _renderEmpty$1, {
	      value: _renderEmpty2$1
	    });
	    Object.defineProperty(this, _users$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup$1, {
	      writable: true,
	      value: void 0
	    });
	  }
	  render(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _users$1)[_users$1] = main_core.Type.isArray(params.users) ? params.users : [];
	    if (babelHelpers.classPrivateFieldLooseBase(this, _users$1)[_users$1].length === 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _renderEmpty$1)[_renderEmpty$1]();
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _renderUsers)[_renderUsers]();
	  }
	}
	function _renderEmpty2$1() {
	  const emptyContainer = main_core.Tag.render(_t$7 || (_t$7 = _$7`
			<div class="mailbox-grid_list-members --empty"></div>
		`));
	  this.appendToFieldNode(emptyContainer);
	}
	function _renderUsers2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _users$1)[_users$1].length === 1) {
	    const userNode = babelHelpers.classPrivateFieldLooseBase(this, _renderSingleUserLayout)[_renderSingleUserLayout]();
	    this.appendToFieldNode(userNode);
	  } else {
	    const usersNode = babelHelpers.classPrivateFieldLooseBase(this, _renderMultipleUsersLayout)[_renderMultipleUsersLayout]();
	    main_core.Event.bind(usersNode, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _showUsersPopup)[_showUsersPopup](usersNode));
	    this.appendToFieldNode(usersNode);
	  }
	}
	function _renderSingleUserLayout2() {
	  const user = babelHelpers.classPrivateFieldLooseBase(this, _users$1)[_users$1][0];
	  const container = main_core.Tag.render(_t2$6 || (_t2$6 = _$7`
			<a href="${0}" class="mailbox-grid_list-members --single-user --link"></a>
		`), user.pathToProfile);
	  const avatar = babelHelpers.classPrivateFieldLooseBase(this, _renderUserAvatar)[_renderUserAvatar](user);
	  main_core.Dom.append(avatar, container);
	  const userName = main_core.Text.encode(user.name) || '';
	  const nameNode = main_core.Tag.render(_t3$3 || (_t3$3 = _$7`<span class="mailbox-grid_list-members-name">${0}</span>`), userName);
	  main_core.Dom.append(nameNode, container);
	  return container;
	}
	function _renderMultipleUsersLayout2() {
	  const maxVisibleAvatars = 3;
	  const visibleUsers = babelHelpers.classPrivateFieldLooseBase(this, _users$1)[_users$1].slice(0, maxVisibleAvatars);
	  const remainingCount = babelHelpers.classPrivateFieldLooseBase(this, _users$1)[_users$1].length - visibleUsers.length;
	  const avatarsContainer = main_core.Tag.render(_t4$1 || (_t4$1 = _$7`<div class="mailbox-grid_list-members"></div>`));
	  visibleUsers.forEach(user => {
	    const avatar = babelHelpers.classPrivateFieldLooseBase(this, _renderUserAvatar)[_renderUserAvatar](user);
	    main_core.Dom.append(avatar, avatarsContainer);
	  });
	  if (remainingCount > 0) {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderCounter)[_renderCounter](remainingCount), avatarsContainer);
	  }
	  return avatarsContainer;
	}
	function _showUsersPopup2(targetElement) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1] = new UserListPopup({
	      users: babelHelpers.classPrivateFieldLooseBase(this, _users$1)[_users$1],
	      targetNode: targetElement
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1].show();
	}
	function _renderUserAvatar2(user) {
	  var _user$avatar, _user$avatar2;
	  const avatarSrc = encodeURI((_user$avatar = user.avatar) == null ? void 0 : _user$avatar.src) || '';
	  const userName = main_core.Text.encode(user.name) || '';
	  const userpicSize = 28;
	  let avatar = null;
	  if (main_core.Type.isStringFilled((_user$avatar2 = user.avatar) == null ? void 0 : _user$avatar2.src)) {
	    avatar = new ui_avatar.AvatarRound({
	      size: userpicSize,
	      userName,
	      userpicPath: avatarSrc
	    });
	  } else {
	    avatar = new ui_avatar.AvatarRound({
	      size: userpicSize
	    });
	  }
	  const avatarNode = avatar.getContainer();
	  main_core.Dom.addClass(avatarNode, 'mailbox-grid_list-members-icon_element');
	  return avatarNode;
	}
	function _renderCounter2(count) {
	  return main_core.Tag.render(_t5 || (_t5 = _$7`
			<div class="mailbox-grid_list-members-icon_element --count">
				<span class="mailbox-grid_warning-icon_element-plus">+</span>
				<span class="mailbox-grid_warning-icon_element-number">${0}</span>
			</div>
		`), count);
	}

	let _$8 = t => t,
	  _t$8,
	  _t2$7;
	var _renderCountWithLimit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCountWithLimit");
	var _renderCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCount");
	class DailySentCountField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _renderCount, {
	      value: _renderCount2
	    });
	    Object.defineProperty(this, _renderCountWithLimit, {
	      value: _renderCountWithLimit2
	    });
	  }
	  render(params) {
	    if (main_core.Type.isNull(params.dailySentLimit)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _renderCount)[_renderCount](params.dailySentCount);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _renderCountWithLimit)[_renderCountWithLimit](params.dailySentCount, params.dailySentLimit);
	  }
	}
	function _renderCountWithLimit2(dailySentCount, dailySentLimit) {
	  const dailySentContainer = main_core.Tag.render(_t$8 || (_t$8 = _$8`
			<div class="mailbox-grid_daily-sent-count-container">
				${0}/${0}
			</div>
		`), dailySentCount, dailySentLimit);
	  this.appendToFieldNode(dailySentContainer);
	}
	function _renderCount2(dailySentCount) {
	  const dailySentContainer = main_core.Tag.render(_t2$7 || (_t2$7 = _$8`
			<div class="mailbox-grid_daily-sent-count-container">
				${0}
			</div>
		`), dailySentCount);
	  this.appendToFieldNode(dailySentContainer);
	}

	let _$9 = t => t,
	  _t$9,
	  _t2$8;
	var _renderCountWithLimit$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCountWithLimit");
	var _renderCount$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCount");
	class MonthlySentCountField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _renderCount$1, {
	      value: _renderCount2$1
	    });
	    Object.defineProperty(this, _renderCountWithLimit$1, {
	      value: _renderCountWithLimit2$1
	    });
	  }
	  render(params) {
	    if (main_core.Type.isNull(params.monthlySentLimit) || !params.monthlySentLimit > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _renderCount$1)[_renderCount$1](params.monthlySentCount);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _renderCountWithLimit$1)[_renderCountWithLimit$1](params.monthlySentCount, params.monthlySentLimit);
	  }
	}
	function _renderCountWithLimit2$1(monthlySentCount, monthlySentLimit) {
	  const percentagePrecision = 2;
	  const percentageMultiplier = 100;
	  const percent = (monthlySentCount / monthlySentLimit * percentageMultiplier).toFixed(percentagePrecision);
	  const dailySentContainer = main_core.Tag.render(_t$9 || (_t$9 = _$9`
			<div class="mailbox-grid_daily-sent-count-container">
				${0}/${0} (${0}%)
			</div>
		`), monthlySentCount, monthlySentLimit, percent);
	  this.appendToFieldNode(dailySentContainer);
	}
	function _renderCount2$1(monthlySentCount) {
	  const monthlySentContainer = main_core.Tag.render(_t2$8 || (_t2$8 = _$9`
			<div class="mailbox-grid_monthly-sent-count-container">
				${0}
			</div>
		`), monthlySentCount);
	  this.appendToFieldNode(monthlySentContainer);
	}

	let _$a = t => t,
	  _t$a,
	  _t2$9;
	var _handleClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClick");
	var _getErrorMessage$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getErrorMessage");
	class ActionField extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _getErrorMessage$1, {
	      value: _getErrorMessage2$1
	    });
	    Object.defineProperty(this, _handleClick, {
	      value: _handleClick2
	    });
	  }
	  render(params) {
	    const actionContainer = main_core.Tag.render(_t$a || (_t$a = _$a`
			<div class="mailbox-grid_action-field-container"></div>
		`));
	    let button = null;
	    if (params.hasError) {
	      button = new ui_buttons.Button({
	        size: ui_buttons.Button.Size.MEDIUM,
	        text: main_core.Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_BUTTON_ERROR_ACTION'),
	        useAirDesign: true,
	        noCaps: true,
	        wide: false,
	        onclick: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _handleClick)[_handleClick](params.url);
	        },
	        className: 'mailbox-grid_action-button',
	        dataset: {
	          id: 'mailbox-grid_action-button-error-action'
	        }
	      });
	      const buttonNode = button.render();
	      main_core.Dom.append(buttonNode, actionContainer);
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getErrorMessage$1)[_getErrorMessage$1](), actionContainer);
	    } else {
	      button = new ui_buttons.Button({
	        size: ui_buttons.Button.Size.MEDIUM,
	        text: main_core.Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_BUTTON_TITLE'),
	        useAirDesign: true,
	        style: ui_buttons.AirButtonStyle.TINTED,
	        noCaps: true,
	        wide: false,
	        onclick: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _handleClick)[_handleClick](params.url);
	        },
	        className: 'mailbox-grid_action-button',
	        dataset: {
	          id: 'mailbox-grid_action-button-default-action'
	        }
	      });
	      const buttonNode = button.render();
	      main_core.Dom.append(buttonNode, actionContainer);
	    }
	    this.appendToFieldNode(actionContainer);
	  }
	}
	function _handleClick2(url) {
	  BX.SidePanel.Instance.open(url);
	}
	function _getErrorMessage2$1() {
	  return main_core.Tag.render(_t2$9 || (_t2$9 = _$a`
			<span class="mailbox-grid_action-field-error-container">
				<div class="mailbox-grid_action-field-error-icon ui-icon-set --warning-alarm"></div>
				<span>${0}</span>
			</span>
		`), main_core.Loc.getMessage('MAIL_MAILBOX_LIST_ACTION_BUTTON_ERROR_MESSAGE'));
	}

	exports.BaseField = BaseField;
	exports.EmployeeField = EmployeeField;
	exports.FullNameField = FullNameField;
	exports.SenderNameField = SenderNameField;
	exports.EmailWithCounterField = EmailWithCounterField;
	exports.LastSyncField = LastSyncField;
	exports.CRMStatusField = CRMStatusField;
	exports.UsersWithAvatarsField = UsersWithAvatarsField;
	exports.DailySentCountField = DailySentCountField;
	exports.MonthlySentCountField = MonthlySentCountField;
	exports.ActionField = ActionField;
	exports.GridManager = GridManager;

}((this.BX.Mail.MailboxList = this.BX.Mail.MailboxList || {}),BX.UI,BX.Main,BX,BX.UI,BX.Main,BX.UI,BX,BX.UI));
