/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,main_core,im_v2_const,im_v2_application_core,im_v2_lib_utils) {
	'use strict';

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _toggleBulkActionsMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleBulkActionsMode");
	var _onKeyPressCloseBulkActions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onKeyPressCloseBulkActions");
	class BulkActionsManager {
	  constructor() {
	    Object.defineProperty(this, _onKeyPressCloseBulkActions, {
	      value: _onKeyPressCloseBulkActions2
	    });
	    Object.defineProperty(this, _toggleBulkActionsMode, {
	      value: _toggleBulkActionsMode2
	    });
	  }
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  init() {
	    this.enableBulkModeHandler = this.enableBulkMode.bind(this);
	    this.disableBulkModeHandler = this.disableBulkMode.bind(this);
	    this.keyPressHandler = babelHelpers.classPrivateFieldLooseBase(this, _onKeyPressCloseBulkActions)[_onKeyPressCloseBulkActions].bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.openBulkActionsMode, this.enableBulkModeHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.closeBulkActionsMode, this.disableBulkModeHandler);
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.openBulkActionsMode, this.enableBulkModeHandler);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.closeBulkActionsMode, this.disableBulkModeHandler);
	    main_core.Event.unbind(document, 'keydown', this.keyPressHandler);
	  }
	  enableBulkMode(event) {
	    const {
	      messageId
	    } = event.getData();
	    void im_v2_application_core.Core.getStore().dispatch('messages/select/toggle', messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleBulkActionsMode)[_toggleBulkActionsMode](true);
	    main_core.Event.bind(document, 'keydown', this.keyPressHandler);
	  }
	  disableBulkMode() {
	    void im_v2_application_core.Core.getStore().dispatch('messages/select/clear');
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleBulkActionsMode)[_toggleBulkActionsMode](false);
	  }
	}
	function _toggleBulkActionsMode2(active) {
	  void im_v2_application_core.Core.getStore().dispatch('messages/select/toggleBulkActionsMode', active);
	}
	function _onKeyPressCloseBulkActions2(event) {
	  if (im_v2_lib_utils.Utils.key.isCombination(event, 'Escape')) {
	    this.disableBulkMode();
	  }
	}
	Object.defineProperty(BulkActionsManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.BulkActionsManager = BulkActionsManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX,BX.Messenger.v2.Const,BX.Messenger.v2.Application,BX.Messenger.v2.Lib));
//# sourceMappingURL=bulk-actions.bundle.js.map
