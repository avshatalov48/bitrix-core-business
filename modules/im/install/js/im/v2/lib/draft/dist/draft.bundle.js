/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,im_v2_application_core,im_v2_lib_localStorage,im_v2_lib_logger,im_v2_const) {
	'use strict';

	const WRITE_TO_STORAGE_TIMEOUT = 1000;
	const SHOW_DRAFT_IN_RECENT_TIMEOUT = 1500;
	class DraftManager {
	  static getInstance() {
	    if (!DraftManager.instance) {
	      DraftManager.instance = new DraftManager();
	    }
	    return DraftManager.instance;
	  }
	  constructor() {
	    this.inited = false;
	    this.drafts = {};
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange.bind(this));
	  }
	  initDraftHistory() {
	    if (this.inited) {
	      return;
	    }
	    this.drafts = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(this.getLocalStorageKey(), {});
	    im_v2_lib_logger.Logger.warn('DraftManager: initDrafts:', this.drafts);
	    this.setDraftsInRecentList();
	    this.inited = true;
	  }
	  setDraft(dialogId, text) {
	    const preparedText = text.trim();
	    if (preparedText === '') {
	      delete this.drafts[dialogId];
	    } else {
	      this.drafts[dialogId] = preparedText;
	    }
	    clearTimeout(this.writeToStorageTimeout);
	    this.writeToStorageTimeout = setTimeout(() => {
	      im_v2_lib_localStorage.LocalStorageManager.getInstance().set(this.getLocalStorageKey(), this.drafts);
	    }, WRITE_TO_STORAGE_TIMEOUT);
	  }
	  getDraft(dialogId) {
	    var _this$drafts$dialogId;
	    return (_this$drafts$dialogId = this.drafts[dialogId]) != null ? _this$drafts$dialogId : '';
	  }
	  clearDraftInRecentList(dialogId) {
	    this.setDraftInRecentList(dialogId, '');
	  }
	  setDraftsInRecentList() {
	    Object.entries(this.drafts).forEach(([dialogId, text]) => {
	      this.setDraftInRecentList(dialogId, text);
	    });
	  }
	  setDraftInRecentList(dialogId, text) {
	    im_v2_application_core.Core.getStore().dispatch(this.getDraftMethodName(), {
	      id: dialogId,
	      text
	    });
	  }
	  onLayoutChange(event) {
	    const {
	      from
	    } = event.getData();
	    if (from.name !== this.getLayoutName() || from.entityId === '') {
	      return;
	    }
	    const dialogId = from.entityId;
	    setTimeout(() => {
	      this.setDraftInRecentList(dialogId, this.getDraft(dialogId));
	    }, SHOW_DRAFT_IN_RECENT_TIMEOUT);
	  }
	  getLayoutName() {
	    return im_v2_const.Layout.chat.name;
	  }
	  getLocalStorageKey() {
	    return im_v2_const.LocalStorageKey.recentDraft;
	  }
	  getDraftMethodName() {
	    return 'recent/setRecentDraft';
	  }
	}
	DraftManager.instance = null;

	class CopilotDraftManager extends DraftManager {
	  static getInstance() {
	    if (!CopilotDraftManager.instance) {
	      CopilotDraftManager.instance = new CopilotDraftManager();
	    }
	    return CopilotDraftManager.instance;
	  }
	  getLayoutName() {
	    return im_v2_const.Layout.copilot.name;
	  }
	  getLocalStorageKey() {
	    return im_v2_const.LocalStorageKey.copilotDraft;
	  }
	  getDraftMethodName() {
	    return 'recent/setCopilotDraft';
	  }
	}
	CopilotDraftManager.instance = null;

	exports.DraftManager = DraftManager;
	exports.CopilotDraftManager = CopilotDraftManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=draft.bundle.js.map
