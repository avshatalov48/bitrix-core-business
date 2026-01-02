/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports,main_core,ui_notification,vote_application) {
	'use strict';

	const BackendModuleId = 'im';
	const BackendEntityType = 'Bitrix\\Vote\\Attachment\\ImMessageConnector';
	var _app = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("app");
	var _load = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("load");
	var _notifyAjaxError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("notifyAjaxError");
	var _getManyVotes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getManyVotes");
	var _setLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setLoading");
	var _sendVoteStopRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendVoteStopRequest");
	var _sendVoteRevokeRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendVoteRevokeRequest");
	var _sendBackendVote = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendBackendVote");
	var _getEntityParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEntityParams");
	var _updateStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateStore");
	class ImVoteService {
	  static init() {
	    return ImVoteService.getInstance();
	  }
	  static getInstance() {
	    if (!ImVoteService.instance) {
	      ImVoteService.instance = new ImVoteService();
	    }
	    return ImVoteService.instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _updateStore, {
	      value: _updateStore2
	    });
	    Object.defineProperty(this, _getEntityParams, {
	      value: _getEntityParams2
	    });
	    Object.defineProperty(this, _sendBackendVote, {
	      value: _sendBackendVote2
	    });
	    Object.defineProperty(this, _sendVoteRevokeRequest, {
	      value: _sendVoteRevokeRequest2
	    });
	    Object.defineProperty(this, _sendVoteStopRequest, {
	      value: _sendVoteStopRequest2
	    });
	    Object.defineProperty(this, _setLoading, {
	      value: _setLoading2
	    });
	    Object.defineProperty(this, _getManyVotes, {
	      value: _getManyVotes2
	    });
	    Object.defineProperty(this, _notifyAjaxError, {
	      value: _notifyAjaxError2
	    });
	    Object.defineProperty(this, _load, {
	      value: _load2
	    });
	    Object.defineProperty(this, _app, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app] = vote_application.VoteApplication.init();
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].subscribe('loadVotes', ({
	      data
	    }) => {
	      const {
	        entityIds,
	        voteIds
	      } = data;
	      babelHelpers.classPrivateFieldLooseBase(this, _load)[_load](entityIds, voteIds);
	    });
	  }
	  async sendVote(ballot, voteId, entityId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setLoading)[_setLoading]([voteId], true);
	    try {
	      var _response$data;
	      const response = await babelHelpers.classPrivateFieldLooseBase(this, _sendBackendVote)[_sendBackendVote](ballot, entityId);
	      if (!(response != null && (_response$data = response.data) != null && _response$data.attach)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _setLoading)[_setLoading]([voteId], false);
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _updateStore)[_updateStore](response.data.attach);
	    } catch (ex) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setLoading)[_setLoading]([voteId], false);
	      throw ex;
	    }
	  }
	  async revokeVote(entityId, voteId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setLoading)[_setLoading]([voteId], true);
	    try {
	      var _response$data2;
	      const response = await babelHelpers.classPrivateFieldLooseBase(this, _sendVoteRevokeRequest)[_sendVoteRevokeRequest](entityId);
	      babelHelpers.classPrivateFieldLooseBase(this, _updateStore)[_updateStore](response == null ? void 0 : (_response$data2 = response.data) == null ? void 0 : _response$data2.attach);
	      return true;
	    } catch (response) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setLoading)[_setLoading]([voteId], false);
	      console.error(response.errors[0].code);
	      throw response;
	    }
	  }
	  completeVote(entityId) {
	    return new Promise((resolve, reject) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _sendVoteStopRequest)[_sendVoteStopRequest](entityId).then(() => {
	        resolve(true);
	      }).catch(response => {
	        console.error(response.errors[0].code);
	        reject(response);
	      });
	    });
	  }
	}
	async function _load2(entityIds, voteIds) {
	  try {
	    var _response$data3;
	    const response = await babelHelpers.classPrivateFieldLooseBase(this, _getManyVotes)[_getManyVotes](entityIds);
	    if (!(response != null && (_response$data3 = response.data) != null && _response$data3.items)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setLoading)[_setLoading](voteIds, false);
	      return;
	    }
	    response.data.items.forEach(item => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateStore)[_updateStore](item);
	    });
	  } catch (ex) {
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].handleLoadError(entityIds);
	    babelHelpers.classPrivateFieldLooseBase(this, _notifyAjaxError)[_notifyAjaxError](ex);
	    babelHelpers.classPrivateFieldLooseBase(this, _setLoading)[_setLoading](voteIds, false);
	  }
	}
	function _notifyAjaxError2(ex) {
	  if (main_core.Type.isObject(ex) && main_core.Type.isArrayFilled(ex.errors)) {
	    var _ex$errors$0$message, _ex$errors$;
	    const content = (_ex$errors$0$message = ex == null ? void 0 : (_ex$errors$ = ex.errors[0]) == null ? void 0 : _ex$errors$.message) != null ? _ex$errors$0$message : 'Unexpected error';
	    ui_notification.UI.Notification.Center.notify({
	      content,
	      autoHideDelay: 4000
	    });
	  } else {
	    console.error(ex);
	  }
	}
	function _getManyVotes2(entityIds) {
	  return main_core.ajax.runAction('vote.AttachedVote.getMany', {
	    data: {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _getEntityParams)[_getEntityParams](),
	      entityIds
	    }
	  });
	}
	function _setLoading2(voteIds, isLoading) {
	  voteIds.forEach(voteId => {
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].getStore().dispatch('vote/setLoadingStatus', {
	      isLoading,
	      voteId
	    });
	  });
	}
	function _sendVoteStopRequest2(entityId) {
	  return main_core.ajax.runAction('vote.AttachedVote.stop', {
	    data: {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _getEntityParams)[_getEntityParams](),
	      entityId
	    }
	  });
	}
	function _sendVoteRevokeRequest2(entityId) {
	  return main_core.ajax.runAction('vote.AttachedVote.recall', {
	    data: {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _getEntityParams)[_getEntityParams](),
	      entityId
	    }
	  });
	}
	function _sendBackendVote2(ballot, entityId) {
	  return main_core.ajax.runAction('vote.AttachedVote.vote', {
	    data: {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _getEntityParams)[_getEntityParams](),
	      entityId,
	      ballot
	    }
	  });
	}
	function _getEntityParams2() {
	  return {
	    moduleId: BackendModuleId,
	    entityType: BackendEntityType
	  };
	}
	function _updateStore2(payload) {
	  if (!payload) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].getStore().dispatch('vote/setCurrentUserVotes', payload.userAnswerMap);
	  babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].getStore().dispatch('vote/addVote', payload);
	  babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].getStore().dispatch('vote/addQuestion', payload.QUESTIONS);
	  babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].getStore().dispatch('vote/addAnswer', payload.QUESTIONS);
	}

	exports.BackendModuleId = BackendModuleId;
	exports.BackendEntityType = BackendEntityType;
	exports.ImVoteService = ImVoteService;

}((this.BX.Vote.Service = this.BX.Vote.Service || {}),BX,BX,BX.Vote));
//# sourceMappingURL=vote-service.bundle.js.map
