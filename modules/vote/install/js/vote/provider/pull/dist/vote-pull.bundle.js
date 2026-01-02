/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports) {
	'use strict';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _updateQuestionTotalCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateQuestionTotalCounter");
	var _updateAnswer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateAnswer");
	class VotePullHandler {
	  constructor(store) {
	    Object.defineProperty(this, _updateAnswer, {
	      value: _updateAnswer2
	    });
	    Object.defineProperty(this, _updateQuestionTotalCounter, {
	      value: _updateQuestionTotalCounter2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = store;
	  }
	  getModuleId() {
	    return 'vote';
	  }
	  handleVoting(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateQuestionTotalCounter)[_updateQuestionTotalCounter](params);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateAnswer)[_updateAnswer](params);
	  }
	  handleUser_vote(params) {
	    this.updateCurrentUserVotes(params);
	  }
	  updateCurrentUserVotes(params) {
	    const {
	      VOTE_ID: voteId,
	      QUESTIONS: questions,
	      userAnswerMap: currentUserVotes
	    } = params;
	    if (Object.keys(currentUserVotes).length === 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].store.dispatch('vote/resetUserVoted', {
	        voteId
	      });
	      Object.keys(questions).forEach(questionId => {
	        babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].store.dispatch('vote/clearVotes', {
	          questionId,
	          voteId
	        });
	      });
	      return;
	    }
	    Object.entries(currentUserVotes).forEach(([questionId, answers]) => {
	      const answerIds = Object.keys(answers).map(Number);
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].store.dispatch('vote/updateCurrentUserVotes', {
	        questionId,
	        answerIds
	      });
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].store.dispatch('vote/setUserVoted', {
	      voteId
	    });
	  }
	  handleStop(params) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].store.dispatch('vote/setVoteCompleted', {
	      voteId: params.voteId
	    });
	  }
	}
	function _updateQuestionTotalCounter2(params) {
	  const {
	    QUESTIONS: questions,
	    COUNTER: totalCounter
	  } = params;
	  Object.keys(questions).forEach(questionId => {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].store.dispatch('vote/updateQuestionTotalCounter', {
	      questionId,
	      totalCounter
	    });
	  });
	}
	function _updateAnswer2(params) {
	  const {
	    QUESTIONS: questions
	  } = params;
	  Object.values(questions).forEach(questionData => {
	    Object.entries(questionData.ANSWERS).forEach(([answerId, answerData]) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].store.dispatch('vote/updateAnswer', {
	        answerId,
	        counter: answerData.COUNTER,
	        percent: answerData.PERCENT
	      });
	    });
	  });
	}

	exports.VotePullHandler = VotePullHandler;

}((this.BX.Vote.Service = this.BX.Vote.Service || {})));
//# sourceMappingURL=vote-pull.bundle.js.map
