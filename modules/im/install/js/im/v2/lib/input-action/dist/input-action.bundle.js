/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const) {
	'use strict';

	const InputAction = {
	  writing: 'writing',
	  recordingVoice: 'recordingVoice',
	  sendingFile: 'sendingFile'
	};
	const DEFAULT_ACTION_DURATION = 25000;
	const ActionDurationMap = {
	  [InputAction.writing]: {
	    [im_v2_const.ChatType.copilot]: 180000,
	    default: DEFAULT_ACTION_DURATION
	  },
	  [InputAction.recordingVoice]: {
	    default: DEFAULT_ACTION_DURATION
	  },
	  [InputAction.sendingFile]: {
	    default: DEFAULT_ACTION_DURATION
	  }
	};
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _actionTimers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("actionTimers");
	var _isAlreadyActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAlreadyActive");
	var _buildTimerId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buildTimerId");
	var _setTimer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setTimer");
	var _clearTimer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearTimer");
	var _getActionDuration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getActionDuration");
	class InputActionListener {
	  constructor() {
	    Object.defineProperty(this, _getActionDuration, {
	      value: _getActionDuration2
	    });
	    Object.defineProperty(this, _clearTimer, {
	      value: _clearTimer2
	    });
	    Object.defineProperty(this, _setTimer, {
	      value: _setTimer2
	    });
	    Object.defineProperty(this, _buildTimerId, {
	      value: _buildTimerId2
	    });
	    Object.defineProperty(this, _isAlreadyActive, {
	      value: _isAlreadyActive2
	    });
	    Object.defineProperty(this, _actionTimers, {
	      writable: true,
	      value: {}
	    });
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  startAction(actionPayload) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isAlreadyActive)[_isAlreadyActive](actionPayload)) {
	      this.stopAction(actionPayload);
	    }
	    void im_v2_application_core.Core.getStore().dispatch('chats/inputActions/start', actionPayload);
	    const timerId = babelHelpers.classPrivateFieldLooseBase(this, _buildTimerId)[_buildTimerId](actionPayload);
	    babelHelpers.classPrivateFieldLooseBase(this, _actionTimers)[_actionTimers][timerId] = babelHelpers.classPrivateFieldLooseBase(this, _setTimer)[_setTimer](actionPayload);
	  }
	  stopAction(actionPayload) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isAlreadyActive)[_isAlreadyActive](actionPayload)) {
	      return;
	    }
	    const timerId = babelHelpers.classPrivateFieldLooseBase(this, _buildTimerId)[_buildTimerId](actionPayload);
	    babelHelpers.classPrivateFieldLooseBase(this, _clearTimer)[_clearTimer](timerId);
	    void im_v2_application_core.Core.getStore().dispatch('chats/inputActions/stop', actionPayload);
	  }
	  clear() {
	    Object.values(babelHelpers.classPrivateFieldLooseBase(this, _actionTimers)[_actionTimers]).forEach(timerId => {
	      clearTimeout(timerId);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _actionTimers)[_actionTimers] = {};
	  }
	}
	function _isAlreadyActive2(payload) {
	  return im_v2_application_core.Core.getStore().getters['chats/inputActions/isActionActive'](payload);
	}
	function _buildTimerId2(payload) {
	  const {
	    dialogId,
	    userId
	  } = payload;
	  return `${dialogId}|${userId}`;
	}
	function _setTimer2(payload) {
	  const actionDuration = babelHelpers.classPrivateFieldLooseBase(this, _getActionDuration)[_getActionDuration](payload);
	  return setTimeout(() => {
	    this.stopAction(payload);
	  }, actionDuration);
	}
	function _clearTimer2(timerId) {
	  clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _actionTimers)[_actionTimers][timerId]);
	  delete babelHelpers.classPrivateFieldLooseBase(this, _actionTimers)[_actionTimers][timerId];
	}
	function _getActionDuration2(payload) {
	  var _typeDurationMap$chat;
	  const {
	    type,
	    dialogId,
	    duration
	  } = payload;
	  if (duration && duration > 0) {
	    return duration;
	  }
	  const typeDurationMap = ActionDurationMap[type];
	  const chat = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	  return (_typeDurationMap$chat = typeDurationMap[chat.type]) != null ? _typeDurationMap$chat : typeDurationMap.default;
	}
	Object.defineProperty(InputActionListener, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.InputAction = InputAction;
	exports.InputActionListener = InputActionListener;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=input-action.bundle.js.map
