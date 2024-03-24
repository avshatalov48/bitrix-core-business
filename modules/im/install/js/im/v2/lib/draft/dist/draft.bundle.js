/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_application_core,im_v2_lib_localStorage,im_v2_lib_logger,im_v2_const) {
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
	    this.initPromise = new Promise(resolve => {
	      this.initPromiseResolver = resolve;
	    });
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange.bind(this));
	  }
	  initDraftHistory() {
	    if (this.inited) {
	      return;
	    }
	    this.inited = true;
	    const draftHistory = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(this.getLocalStorageKey(), {});
	    this.fillDraftsFromStorage(draftHistory);
	    im_v2_lib_logger.Logger.warn('DraftManager: initDrafts:', this.drafts);
	    this.initPromiseResolver();
	    this.setRecentListDraftText();
	  }
	  ready() {
	    return this.initPromise;
	  }
	  fillDraftsFromStorage(draftHistory) {
	    if (!main_core.Type.isPlainObject(draftHistory)) {
	      return;
	    }
	    Object.entries(draftHistory).forEach(([dialogId, draft]) => {
	      if (!main_core.Type.isPlainObject(draft)) {
	        return;
	      }
	      this.drafts[dialogId] = draft;
	    });
	  }
	  setDraftText(dialogId, text) {
	    if (!this.drafts[dialogId]) {
	      this.drafts[dialogId] = {};
	    }
	    this.drafts[dialogId].text = text.trim();
	    this.refreshSaveTimeout();
	  }
	  setDraftPanel(dialogId, panelType, messageId) {
	    if (!this.drafts[dialogId]) {
	      this.drafts[dialogId] = {};
	    }
	    this.drafts[dialogId].panelType = panelType;
	    this.drafts[dialogId].panelMessageId = messageId;
	    this.refreshSaveTimeout();
	  }
	  setDraftMentions(dialogId, mentions) {
	    if (!this.drafts[dialogId]) {
	      this.drafts[dialogId] = {};
	    }
	    this.drafts[dialogId].mentions = mentions;
	    this.refreshSaveTimeout();
	  }
	  async getDraft(dialogId) {
	    var _this$drafts$dialogId;
	    await this.initPromise;
	    const draft = (_this$drafts$dialogId = this.drafts[dialogId]) != null ? _this$drafts$dialogId : {};
	    return Promise.resolve(draft);
	  }
	  clearDraft(dialogId) {
	    delete this.drafts[dialogId];
	    this.setRecentItemDraftText(dialogId, '');
	  }
	  setRecentListDraftText() {
	    Object.entries(this.drafts).forEach(([dialogId, draft]) => {
	      var _draft$text;
	      this.setRecentItemDraftText(dialogId, (_draft$text = draft.text) != null ? _draft$text : '');
	    });
	  }
	  setRecentItemDraftText(dialogId, text) {
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
	    setTimeout(async () => {
	      const {
	        text = ''
	      } = await this.getDraft(dialogId);
	      this.setRecentItemDraftText(dialogId, text);
	    }, SHOW_DRAFT_IN_RECENT_TIMEOUT);
	  }
	  refreshSaveTimeout() {
	    clearTimeout(this.writeToStorageTimeout);
	    this.writeToStorageTimeout = setTimeout(() => {
	      this.saveToLocalStorage();
	    }, WRITE_TO_STORAGE_TIMEOUT);
	  }
	  saveToLocalStorage() {
	    im_v2_lib_localStorage.LocalStorageManager.getInstance().set(this.getLocalStorageKey(), this.prepareDrafts());
	  }
	  prepareDrafts() {
	    const result = {};
	    Object.entries(this.drafts).forEach(([dialogId, draft]) => {
	      if (!draft.text && !draft.panelType) {
	        return;
	      }
	      if (draft.panelType === im_v2_const.TextareaPanelType.edit) {
	        return;
	      }
	      result[dialogId] = {
	        text: draft.text,
	        mentions: draft.mentions
	      };
	    });
	    return result;
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

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=draft.bundle.js.map
