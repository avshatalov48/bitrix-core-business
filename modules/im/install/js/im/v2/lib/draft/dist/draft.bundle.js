this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_application_core,im_v2_lib_localStorage,im_v2_lib_logger,im_v2_const) {
	'use strict';

	const WRITE_TO_STORAGE_TIMEOUT = 1000;
	const SHOW_DRAFT_IN_RECENT_TIMEOUT = 1500;
	var _drafts = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("drafts");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _setDraftsInRecentList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDraftsInRecentList");
	var _setDraftInRecentList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDraftInRecentList");
	var _onLayoutChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onLayoutChange");
	class DraftManager {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _onLayoutChange, {
	      value: _onLayoutChange2
	    });
	    Object.defineProperty(this, _setDraftInRecentList, {
	      value: _setDraftInRecentList2
	    });
	    Object.defineProperty(this, _setDraftsInRecentList, {
	      value: _setDraftsInRecentList2
	    });
	    Object.defineProperty(this, _drafts, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, babelHelpers.classPrivateFieldLooseBase(this, _onLayoutChange)[_onLayoutChange].bind(this));
	  }
	  initDraftHistory() {
	    if (DraftManager.inited) {
	      return false;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _drafts)[_drafts] = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(im_v2_const.LocalStorageKey.draft, {});
	    im_v2_lib_logger.Logger.warn('DraftManager: initDrafts:', babelHelpers.classPrivateFieldLooseBase(this, _drafts)[_drafts]);
	    babelHelpers.classPrivateFieldLooseBase(this, _setDraftsInRecentList)[_setDraftsInRecentList]();
	    DraftManager.inited = true;
	  }
	  setDraft(dialogId, text) {
	    text = text.trim();
	    if (text === '') {
	      delete babelHelpers.classPrivateFieldLooseBase(this, _drafts)[_drafts][dialogId];
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _drafts)[_drafts][dialogId] = text;
	    }
	    clearTimeout(this.writeToStorageTimeout);
	    this.writeToStorageTimeout = setTimeout(() => {
	      im_v2_lib_localStorage.LocalStorageManager.getInstance().set(im_v2_const.LocalStorageKey.draft, babelHelpers.classPrivateFieldLooseBase(this, _drafts)[_drafts]);
	    }, WRITE_TO_STORAGE_TIMEOUT);
	  }
	  clearDraft(dialogId) {
	    this.setDraft(dialogId, '');
	  }
	  getDraft(dialogId) {
	    var _babelHelpers$classPr;
	    return (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _drafts)[_drafts][dialogId]) != null ? _babelHelpers$classPr : '';
	  }
	  clearDraftInRecentList(dialogId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setDraftInRecentList)[_setDraftInRecentList](dialogId, '');
	  }
	}
	function _setDraftsInRecentList2() {
	  Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _drafts)[_drafts]).forEach(([dialogId, text]) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _setDraftInRecentList)[_setDraftInRecentList](dialogId, text);
	  });
	}
	function _setDraftInRecentList2(dialogId, text) {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/draft', {
	    id: dialogId,
	    text
	  });
	}
	function _onLayoutChange2(event) {
	  const {
	    from
	  } = event.getData();
	  if (from.name !== im_v2_const.Layout.chat.name || from.entityId === '') {
	    return;
	  }
	  const dialogId = from.entityId;
	  setTimeout(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _setDraftInRecentList)[_setDraftInRecentList](dialogId, this.getDraft(dialogId));
	  }, SHOW_DRAFT_IN_RECENT_TIMEOUT);
	}

	exports.DraftManager = DraftManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=draft.bundle.js.map
