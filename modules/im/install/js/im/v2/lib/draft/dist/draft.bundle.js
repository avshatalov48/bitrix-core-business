/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,im_v2_lib_logger,im_v2_application_core,im_v2_const,ui_dexie,im_v2_lib_localStorage) {
	'use strict';

	const DB_NAME = 'bx-im-drafts';
	const recentDraftLocalStorageKey = 'recentDraft';
	const copilotDraftLocalStorageKey = 'copilotDraft';
	class IndexedDbManager {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    this.db = new ui_dexie.Dexie(DB_NAME);
	    this.db.version(1).stores({
	      drafts: ''
	    });
	  }
	  async migrateFromLocalStorage() {
	    const migrationStatus = await this.db.drafts.get('migration_status');
	    if (migrationStatus) {
	      return;
	    }
	    const recentDrafts = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(recentDraftLocalStorageKey, {});
	    this.set(recentDraftLocalStorageKey, recentDrafts);
	    im_v2_lib_localStorage.LocalStorageManager.getInstance().remove(recentDraftLocalStorageKey);
	    const copilotDrafts = im_v2_lib_localStorage.LocalStorageManager.getInstance().get(copilotDraftLocalStorageKey, {});
	    this.set(copilotDraftLocalStorageKey, copilotDrafts);
	    im_v2_lib_localStorage.LocalStorageManager.getInstance().remove(copilotDraftLocalStorageKey);
	    this.setMigrationFinished();
	  }
	  set(key, value) {
	    this.db.drafts.put(value, key);
	  }
	  setMigrationFinished() {
	    const result = {
	      [recentDraftLocalStorageKey]: true,
	      [copilotDraftLocalStorageKey]: true
	    };
	    this.db.drafts.put(result, 'migration_status');
	  }
	  async get(key, defaultValue = null) {
	    await this.migrateFromLocalStorage();
	    const value = await this.db.drafts.get(key);
	    return value || defaultValue;
	  }
	}

	const WRITE_TO_STORAGE_TIMEOUT = 1000;
	const SHOW_DRAFT_IN_RECENT_TIMEOUT = 1500;
	const STORAGE_KEY = 'recentDraft';
	const NOT_AVAILABLE_CHAT_TYPES = new Set([im_v2_const.ChatType.comment]);
	const STANDALONE_SECTION_CHAT_TYPES = new Set([im_v2_const.ChatType.taskComments]);
	var _getChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getChat");
	class DraftManager {
	  static getInstance() {
	    if (!DraftManager.instance) {
	      DraftManager.instance = new DraftManager();
	    }
	    return DraftManager.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _getChat, {
	      value: _getChat2
	    });
	    this.inited = false;
	    this.drafts = {};
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.layout.onLayoutChange, this.onLayoutChange.bind(this));
	  }
	  async initDraftHistory() {
	    let draftHistory = null;
	    try {
	      draftHistory = await IndexedDbManager.getInstance().get(STORAGE_KEY, {});
	    } catch (error) {
	      // eslint-disable-next-line no-console
	      console.error('DraftManager: error initing draft history', error);
	      return;
	    }
	    this.fillDraftsFromStorage(draftHistory);
	    im_v2_lib_logger.Logger.warn('DraftManager: initDrafts:', this.drafts);
	    this.setRecentListDraftText();
	    this.inited = true;
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
	  setDraftPanel(dialogId, panelType, panelContext) {
	    if (!this.drafts[dialogId]) {
	      this.drafts[dialogId] = {};
	    }
	    this.drafts[dialogId].panelType = panelType;
	    this.drafts[dialogId].panelContext = panelContext;
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
	    if (!this.inited) {
	      await this.initDraftHistory();
	    }
	    return (_this$drafts$dialogId = this.drafts[dialogId]) != null ? _this$drafts$dialogId : {};
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
	    if (!this.canSetRecentItemDraftText(dialogId)) {
	      return;
	    }
	    const {
	      type: chatType
	    } = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](dialogId);
	    void im_v2_application_core.Core.getStore().dispatch('recent/setDraft', {
	      id: dialogId,
	      text,
	      addFakeItems: !STANDALONE_SECTION_CHAT_TYPES.has(chatType)
	    });
	  }
	  onLayoutChange(event) {
	    const {
	      from
	    } = event.getData();
	    const dialogId = from.entityId;
	    if (dialogId === '') {
	      return;
	    }
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
	      this.saveToIndexedDb();
	    }, WRITE_TO_STORAGE_TIMEOUT);
	  }
	  saveToIndexedDb() {
	    IndexedDbManager.getInstance().set(STORAGE_KEY, this.prepareDrafts());
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
	  canSetRecentItemDraftText(dialogId) {
	    const chat = babelHelpers.classPrivateFieldLooseBase(this, _getChat)[_getChat](dialogId);
	    if (!chat) {
	      return false;
	    }
	    return !NOT_AVAILABLE_CHAT_TYPES.has(chat.type);
	  }
	}
	function _getChat2(dialogId) {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	}
	DraftManager.instance = null;

	exports.DraftManager = DraftManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.DexieExport,BX.Messenger.v2.Lib));
//# sourceMappingURL=draft.bundle.js.map
