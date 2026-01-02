/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events,ui_vue3_vuex,im_v2_const,im_v2_application_core,vote_store_vote,vote_provider_pull) {
	'use strict';

	const VOTES_COUNT_TO_LOAD = 50;
	var _visibleVotes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("visibleVotes");
	var _shouldLoadVotes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldLoadVotes");
	var _startIndexToLoadVotes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startIndexToLoadVotes");
	var _subscribeOnEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnEvents");
	var _onMessageIsVisible = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMessageIsVisible");
	var _prepareToLoadVotes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareToLoadVotes");
	class VoteApplication extends main_core_events.EventEmitter {
	  static init() {
	    return VoteApplication.getInstance();
	  }
	  static getInstance() {
	    if (!VoteApplication.instance) {
	      VoteApplication.instance = new VoteApplication();
	    }
	    return VoteApplication.instance;
	  }
	  constructor() {
	    super();
	    Object.defineProperty(this, _prepareToLoadVotes, {
	      value: _prepareToLoadVotes2
	    });
	    Object.defineProperty(this, _onMessageIsVisible, {
	      value: _onMessageIsVisible2
	    });
	    Object.defineProperty(this, _subscribeOnEvents, {
	      value: _subscribeOnEvents2
	    });
	    Object.defineProperty(this, _visibleVotes, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _shouldLoadVotes, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _startIndexToLoadVotes, {
	      writable: true,
	      value: 0
	    });
	    this.setEventNamespace('BX.VOTE.APPLICATION');
	    this.createStore();
	    this.initPull();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnEvents)[_subscribeOnEvents]();
	  }
	  handleLoadError(entityIds) {
	    babelHelpers.classPrivateFieldLooseBase(this, _visibleVotes)[_visibleVotes] = babelHelpers.classPrivateFieldLooseBase(this, _visibleVotes)[_visibleVotes].filter(id => !entityIds.includes(id));
	    babelHelpers.classPrivateFieldLooseBase(this, _startIndexToLoadVotes)[_startIndexToLoadVotes] = 0;
	  }
	  createStore() {
	    this.store = ui_vue3_vuex.createStore({
	      modules: {
	        vote: {
	          namespaced: true,
	          ...vote_store_vote.VoteModel
	        }
	      }
	    });
	  }
	  getStore() {
	    return this.store;
	  }
	  initPull() {
	    this.pullClient = BX.PULL;
	    if (!this.pullClient) {
	      return;
	    }
	    this.pullClient.subscribe(new vote_provider_pull.VotePullHandler({
	      store: this.store
	    }));
	  }
	  static canCreateVoteInChat(currentChatType) {
	    const availableChatTypes = [im_v2_const.ChatType.chat, im_v2_const.ChatType.open, im_v2_const.ChatType.general, im_v2_const.ChatType.call, im_v2_const.ChatType.crm, im_v2_const.ChatType.sonetGroup, im_v2_const.ChatType.calendar, im_v2_const.ChatType.tasks, im_v2_const.ChatType.mail, im_v2_const.ChatType.generalChannel, im_v2_const.ChatType.channel, im_v2_const.ChatType.openChannel, im_v2_const.ChatType.collab];
	    return availableChatTypes.includes(currentChatType);
	  }
	}
	function _subscribeOnEvents2() {
	  main_core_events.EventEmitter.subscribe('vote-message-batch', babelHelpers.classPrivateFieldLooseBase(this, _onMessageIsVisible)[_onMessageIsVisible].bind(this));
	}
	function _onMessageIsVisible2(event) {
	  const {
	    messageId
	  } = event.getData();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _visibleVotes)[_visibleVotes].includes(messageId)) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _visibleVotes)[_visibleVotes].push(messageId);
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _shouldLoadVotes)[_shouldLoadVotes]) {
	    return;
	  }
	  main_core.Runtime.debounce(() => babelHelpers.classPrivateFieldLooseBase(this, _prepareToLoadVotes)[_prepareToLoadVotes]())();
	  babelHelpers.classPrivateFieldLooseBase(this, _shouldLoadVotes)[_shouldLoadVotes] = false;
	}
	function _prepareToLoadVotes2() {
	  const entityIds = babelHelpers.classPrivateFieldLooseBase(this, _visibleVotes)[_visibleVotes].slice(babelHelpers.classPrivateFieldLooseBase(this, _startIndexToLoadVotes)[_startIndexToLoadVotes], babelHelpers.classPrivateFieldLooseBase(this, _visibleVotes)[_visibleVotes].length);
	  const requestCount = Math.ceil(entityIds.length / VOTES_COUNT_TO_LOAD);
	  babelHelpers.classPrivateFieldLooseBase(this, _startIndexToLoadVotes)[_startIndexToLoadVotes] = babelHelpers.classPrivateFieldLooseBase(this, _visibleVotes)[_visibleVotes].length;
	  babelHelpers.classPrivateFieldLooseBase(this, _shouldLoadVotes)[_shouldLoadVotes] = true;
	  for (let i = 0; i < requestCount; i++) {
	    const startIndex = i * VOTES_COUNT_TO_LOAD;
	    const batchOfEntities = entityIds.slice(startIndex, Math.min(startIndex + VOTES_COUNT_TO_LOAD, entityIds.length));
	    const voteIds = batchOfEntities.map(entityId => {
	      const {
	        componentParams
	      } = im_v2_application_core.Core.getStore().getters['messages/getById'](entityId);
	      return componentParams.id;
	    });
	    this.emit('loadVotes', {
	      entityIds: batchOfEntities,
	      voteIds
	    });
	  }
	}

	exports.VoteApplication = VoteApplication;

}((this.BX.Vote = this.BX.Vote || {}),BX,BX.Event,BX.Vue3.Vuex,BX.Messenger.v2.Const,BX.Messenger.v2.Application,BX.Vote.Store,BX.Vote.Service));
//# sourceMappingURL=vote.bundle.js.map
