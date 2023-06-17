this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
(function (exports,main_core,ui_dexie,rest_client,im_oldChatEmbedding_application_core,im_oldChatEmbedding_const,im_oldChatEmbedding_lib_localStorage) {
	'use strict';

	const sets = ['id', 'parentId', 'name', 'type', 'image', 'selected'].join(',');
	const smiles = ['id', 'setId', 'name', 'image', 'typing', 'width', 'height', 'definition', 'alternative'].join(',');
	const CACHE_VERSION = 4;
	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _smileList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("smileList");
	var _db = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("db");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _localStorageManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("localStorageManager");
	var _lastUpdateTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastUpdateTime");
	var _recentEmoji = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("recentEmoji");
	var _fetchDataFromServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fetchDataFromServer");
	var _fetchDataFromStorage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fetchDataFromStorage");
	var _fillStorage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fillStorage");
	var _shouldRequestFromServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldRequestFromServer");
	var _loadRecentEmoji = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadRecentEmoji");
	class SmileManager {
	  static getInstance() {
	    var _babelHelpers$classPr;
	    babelHelpers.classPrivateFieldLooseBase(SmileManager, _instance)[_instance] = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(SmileManager, _instance)[_instance]) != null ? _babelHelpers$classPr : new SmileManager();
	    return babelHelpers.classPrivateFieldLooseBase(SmileManager, _instance)[_instance];
	  }
	  static init() {
	    SmileManager.getInstance().initSmileList();
	  }
	  constructor() {
	    Object.defineProperty(this, _loadRecentEmoji, {
	      value: _loadRecentEmoji2
	    });
	    Object.defineProperty(this, _shouldRequestFromServer, {
	      value: _shouldRequestFromServer2
	    });
	    Object.defineProperty(this, _fillStorage, {
	      value: _fillStorage2
	    });
	    Object.defineProperty(this, _fetchDataFromStorage, {
	      value: _fetchDataFromStorage2
	    });
	    Object.defineProperty(this, _fetchDataFromServer, {
	      value: _fetchDataFromServer2
	    });
	    Object.defineProperty(this, _smileList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _db, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _localStorageManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _lastUpdateTime, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _recentEmoji, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _db)[_db] = new ui_dexie.Dexie('bx-im-smiles');
	    babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].version(2).stores({
	      sets,
	      smiles,
	      recentEmoji: ',symbols'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_oldChatEmbedding_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _localStorageManager)[_localStorageManager] = im_oldChatEmbedding_lib_localStorage.LocalStorageManager.getInstance();
	    const {
	      lastUpdate
	    } = main_core.Extension.getSettings('im.old-chat-embedding.lib.smile-manager');
	    babelHelpers.classPrivateFieldLooseBase(this, _lastUpdateTime)[_lastUpdateTime] = Date.parse(lastUpdate) + CACHE_VERSION;
	    // for debug purpose only
	    // this.#lastUpdateTime = Date.now();
	    babelHelpers.classPrivateFieldLooseBase(this, _recentEmoji)[_recentEmoji] = new Set();
	  }
	  async initSmileList() {
	    try {
	      const shouldRequestFromServer = babelHelpers.classPrivateFieldLooseBase(this, _shouldRequestFromServer)[_shouldRequestFromServer]();
	      if (shouldRequestFromServer) {
	        babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList] = await babelHelpers.classPrivateFieldLooseBase(this, _fetchDataFromServer)[_fetchDataFromServer]();
	        await babelHelpers.classPrivateFieldLooseBase(this, _fillStorage)[_fillStorage](babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList]);
	        babelHelpers.classPrivateFieldLooseBase(this, _localStorageManager)[_localStorageManager].set(im_oldChatEmbedding_const.LocalStorageKey.smileLastUpdateTime, babelHelpers.classPrivateFieldLooseBase(this, _lastUpdateTime)[_lastUpdateTime]);
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList] = await babelHelpers.classPrivateFieldLooseBase(this, _fetchDataFromStorage)[_fetchDataFromStorage]();
	      }
	      await babelHelpers.classPrivateFieldLooseBase(this, _loadRecentEmoji)[_loadRecentEmoji]();
	    } catch (err) {
	      console.error('Smile Manager data fetch error:', err);
	      babelHelpers.classPrivateFieldLooseBase(this, _localStorageManager)[_localStorageManager].remove(im_oldChatEmbedding_const.LocalStorageKey.smileLastUpdateTime);
	    }
	  }
	  async updateSelectedSet(selectedSetId) {
	    const setsDB = babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].sets;
	    await setsDB.toCollection().modify(set => {
	      set.selected = set.id === selectedSetId ? 1 : 0;
	    });
	    const sets = babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList].sets;
	    babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList].sets = sets.map(set => {
	      if (set.id === selectedSetId) {
	        return {
	          ...set,
	          selected: 1
	        };
	      }
	      return {
	        ...set,
	        selected: 0
	      };
	    });
	  }
	  async updateRecentEmoji(symbols) {
	    await babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].recentEmoji.put({
	      symbols
	    }, 0);
	    babelHelpers.classPrivateFieldLooseBase(this, _recentEmoji)[_recentEmoji] = symbols;
	  }
	  get smileList() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList];
	  }
	  get recentEmoji() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _recentEmoji)[_recentEmoji];
	  }
	}
	async function _fetchDataFromServer2() {
	  const result = await babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_oldChatEmbedding_const.RestMethod.imSmilesGet, {
	    FULL_TYPINGS: 'Y'
	  });
	  const data = result.data();
	  const smileList = [];
	  data.smiles.forEach(smile => {
	    const list = smile.typing.split(' ');
	    let alternative = true;
	    list.forEach(code => {
	      smileList.push({
	        ...smile,
	        typing: code,
	        id: smileList.length,
	        alternative
	      });
	      alternative = false;
	    });
	  });
	  const setList = data.sets.map(set => {
	    const firstSmileInSet = smileList.find(smile => smile.setId === set.id);
	    const {
	      image
	    } = firstSmileInSet;
	    return {
	      ...set,
	      image
	    };
	  });
	  return {
	    sets: setList,
	    smiles: smileList
	  };
	}
	async function _fetchDataFromStorage2() {
	  const {
	    sets: setsTbl,
	    smiles: smilesTbl
	  } = babelHelpers.classPrivateFieldLooseBase(this, _db)[_db];
	  const data = await babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].transaction('r', setsTbl, smilesTbl, async () => {
	    const [sets, smiles] = await Promise.all([setsTbl.toArray(), smilesTbl.toArray()]);
	    return {
	      sets,
	      smiles
	    };
	  });
	  return data;
	}
	async function _fillStorage2(smileList) {
	  const {
	    sets,
	    smiles
	  } = smileList;
	  const setsToSave = sets.map(set => ({
	    ...set,
	    selected: 0
	  }));
	  setsToSave[0].selected = 1;
	  await Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].smiles.clear(), babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].sets.clear()]);
	  await Promise.all([babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].sets.bulkAdd(setsToSave), babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].smiles.bulkAdd(smiles)]);
	  babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList] = {
	    ...babelHelpers.classPrivateFieldLooseBase(this, _smileList)[_smileList],
	    sets: setsToSave
	  };
	}
	function _shouldRequestFromServer2() {
	  const lastUpdateTimeFromStorage = babelHelpers.classPrivateFieldLooseBase(this, _localStorageManager)[_localStorageManager].get(im_oldChatEmbedding_const.LocalStorageKey.smileLastUpdateTime);
	  const shouldRequestFromServer = babelHelpers.classPrivateFieldLooseBase(this, _lastUpdateTime)[_lastUpdateTime] !== lastUpdateTimeFromStorage;
	  return shouldRequestFromServer;
	}
	async function _loadRecentEmoji2() {
	  var _storageData$symbols;
	  const storageData = await babelHelpers.classPrivateFieldLooseBase(this, _db)[_db].recentEmoji.get(0);
	  babelHelpers.classPrivateFieldLooseBase(this, _recentEmoji)[_recentEmoji] = (_storageData$symbols = storageData == null ? void 0 : storageData.symbols) != null ? _storageData$symbols : babelHelpers.classPrivateFieldLooseBase(this, _recentEmoji)[_recentEmoji];
	}
	Object.defineProperty(SmileManager, _instance, {
	  writable: true,
	  value: void 0
	});

	exports.SmileManager = SmileManager;

}((this.BX.Messenger.Embedding.Lib = this.BX.Messenger.Embedding.Lib || {}),BX,BX.Dexie3,BX,BX.Messenger.Embedding.Application,BX.Messenger.Embedding.Const,BX.Messenger.Embedding.Lib));
//# sourceMappingURL=smile-manager.bundle.js.map
