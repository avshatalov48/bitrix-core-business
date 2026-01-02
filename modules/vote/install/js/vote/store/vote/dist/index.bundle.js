/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports,main_core,im_v2_model) {
	'use strict';

	const voteFieldsConfig = [{
	  fieldName: 'voteId',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isNumber
	}, {
	  fieldName: 'questions',
	  targetFieldName: 'questions',
	  checkFunction: main_core.Type.isObject,
	  formatFunction: target => {
	    return Object.keys(target).map(id => im_v2_model.convertToNumber(id));
	  }
	}, {
	  fieldName: 'anonymity',
	  targetFieldName: 'isAnonymous',
	  checkFunction: main_core.Type.isNumber,
	  formatFunction: target => {
	    return target === 2;
	  }
	}, {
	  fieldName: 'canEdit',
	  targetFieldName: 'canEdit',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'isVoted',
	  targetFieldName: 'isVoted',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'canVote',
	  targetFieldName: 'canVote',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'canRevote',
	  targetFieldName: 'canRevoke',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'isFinished',
	  targetFieldName: 'isCompleted',
	  checkFunction: main_core.Type.isBoolean
	}, {
	  fieldName: 'resultUrl',
	  targetFieldName: 'resultUrl',
	  checkFunction: main_core.Type.isString
	}];
	const questionFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_model.convertToNumber
	}, {
	  fieldName: 'answers',
	  targetFieldName: 'answers',
	  checkFunction: main_core.Type.isObject,
	  formatFunction: target => {
	    return Object.keys(target).map(id => im_v2_model.convertToNumber(id));
	  }
	}, {
	  fieldName: 'question',
	  targetFieldName: 'text',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'fieldType',
	  targetFieldName: 'isMultiple',
	  checkFunction: main_core.Type.isString,
	  formatFunction: target => target === '1'
	}, {
	  fieldName: 'counter',
	  targetFieldName: 'totalCounter',
	  checkFunction: im_v2_model.isNumberOrString,
	  formatFunction: im_v2_model.convertToNumber
	}];
	const answerFieldsConfig = [{
	  fieldName: 'id',
	  targetFieldName: 'id',
	  checkFunction: main_core.Type.isString,
	  formatFunction: im_v2_model.convertToNumber
	}, {
	  fieldName: 'message',
	  targetFieldName: 'text',
	  checkFunction: main_core.Type.isString
	}, {
	  fieldName: 'counter',
	  targetFieldName: 'counter',
	  checkFunction: im_v2_model.isNumberOrString,
	  formatFunction: im_v2_model.convertToNumber
	}, {
	  fieldName: 'percent',
	  targetFieldName: 'percent',
	  checkFunction: main_core.Type.isNumber
	}];

	/* eslint-disable no-param-reassign */
	const VoteModel = {
	  state: {
	    currentUserVotes: {},
	    voteCollection: {},
	    questionCollection: {},
	    answerCollection: {}
	  },
	  getVoteState() {
	    return {
	      id: '',
	      questions: [],
	      isAnonymous: false,
	      isVoted: false,
	      isLoading: false,
	      canEdit: false,
	      canVote: false,
	      canRevoke: false,
	      isCompleted: false,
	      resultUrl: ''
	    };
	  },
	  getQuestionState() {
	    return {
	      id: '',
	      text: '',
	      answers: [],
	      isMultiple: false,
	      totalCounter: 0
	    };
	  },
	  getAnswerState() {
	    return {
	      id: '',
	      text: '',
	      counter: 0,
	      percent: 0
	    };
	  },
	  getters: {
	    /** @function vote/hasCurrentUserVote */
	    hasCurrentUserVote: state => (questionId, answerId) => {
	      return state.currentUserVotes[questionId] && state.currentUserVotes[questionId].includes(answerId);
	    },
	    /** @function vote/getCurrentUserVotes */
	    getCurrentUserVotes: state => {
	      return state.currentUserVotes;
	    },
	    /** @function vote/getVoteCollection */
	    getVoteCollection: state => {
	      return state.voteCollection;
	    },
	    /** @function vote/getQuestionCollection */
	    getQuestionCollection: state => {
	      return state.questionCollection;
	    },
	    /** @function vote/getAnswerCollection */
	    getAnswerCollection: state => {
	      return state.answerCollection;
	    }
	  },
	  actions: {
	    /** @function vote/setCurrentUserVotes */
	    setCurrentUserVotes: (store, payload) => {
	      const formattedVotes = {};
	      Object.entries(payload).forEach(([questionId, answers]) => {
	        formattedVotes[questionId] = Object.keys(answers).map(answerId => Number(answerId));
	      });
	      store.commit('setCurrentUserVotes', formattedVotes);
	    },
	    /** @function vote/clearVotes */
	    clearVotes: (store, {
	      questionId,
	      voteId
	    }) => {
	      if (store.state.currentUserVotes[questionId]) {
	        store.commit('clearVotes', {
	          questionId,
	          voteId
	        });
	      }
	    },
	    /** @function vote/resetUserVoted */
	    resetUserVoted: (store, {
	      voteId
	    }) => {
	      if (store.state.voteCollection[voteId]) {
	        store.commit('resetUserVoted', {
	          voteId
	        });
	      }
	    },
	    /** @function vote/addVote */
	    addVote: (store, payload) => {
	      store.commit('addVote', {
	        ...VoteModel.getVoteState(),
	        ...VoteModel.formatVoteFields(payload)
	      });
	    },
	    /** @function vote/addQuestion */
	    addQuestion: (store, payload) => {
	      const question = Object.values(payload)[0];
	      store.commit('addQuestion', {
	        ...VoteModel.getQuestionState(),
	        ...VoteModel.formatQuestionFields(question)
	      });
	    },
	    /** @function vote/updateQuestionTotalCounter */
	    updateQuestionTotalCounter: (store, payload) => {
	      if (!store.state.questionCollection[payload.questionId]) {
	        return;
	      }
	      store.commit('updateQuestionTotalCounter', payload);
	    },
	    /** @function vote/addAnswer */
	    addAnswer: (store, payload) => {
	      const question = Object.values(payload)[0];
	      Object.values(question.ANSWERS).forEach(answer => {
	        store.commit('addAnswer', {
	          ...VoteModel.getAnswerState(),
	          ...VoteModel.formatAnswerFields(answer)
	        });
	      });
	    },
	    /** @function vote/updateAnswer */
	    updateAnswer: (store, payload) => {
	      store.commit('updateAnswer', payload);
	    },
	    /** @function vote/updateCurrentUserVotes */
	    updateCurrentUserVotes: (store, payload) => {
	      store.commit('updateCurrentUserVotes', payload);
	    },
	    /** @function vote/setUserVoted */
	    setUserVoted: (store, payload) => {
	      store.commit('setUserVoted', payload);
	    },
	    /** @function vote/setVoteCompleted */
	    setVoteCompleted: (store, payload) => {
	      store.commit('setVoteCompleted', payload);
	    },
	    /** @function vote/resetVoteCompleted */
	    resetVoteCompleted: (store, payload) => {
	      store.commit('resetVoteCompleted', payload);
	    },
	    /** @function vote/setLoadingStatus */
	    setLoadingStatus: (store, payload) => {
	      store.commit('setLoadingStatus', payload);
	    }
	  },
	  mutations: {
	    setCurrentUserVotes: (state, payload) => {
	      Object.entries(payload).forEach(([questionId, answerIds]) => {
	        state.currentUserVotes[questionId] = answerIds;
	      });
	    },
	    updateCurrentUserVotes: (state, {
	      questionId,
	      answerIds
	    }) => {
	      state.currentUserVotes[questionId] = answerIds;
	    },
	    addVote: (state, payload) => {
	      state.voteCollection[payload.id] = payload;
	    },
	    addQuestion: (state, payload) => {
	      state.questionCollection[payload.id] = payload;
	    },
	    updateQuestionTotalCounter: (state, {
	      questionId,
	      totalCounter
	    }) => {
	      state.questionCollection[questionId].totalCounter = totalCounter;
	    },
	    addAnswer: (state, payload) => {
	      state.answerCollection[payload.id] = payload;
	    },
	    updateAnswer: (state, payload) => {
	      if (state.answerCollection[payload.answerId]) {
	        state.answerCollection[payload.answerId].percent = payload.percent;
	        state.answerCollection[payload.answerId].counter = payload.counter;
	      }
	    },
	    clearVotes: (state, payload) => {
	      state.currentUserVotes[payload.questionId] = [];
	    },
	    setUserVoted: (state, payload) => {
	      state.voteCollection[payload.voteId].isVoted = true;
	    },
	    resetUserVoted: (state, payload) => {
	      state.voteCollection[payload.voteId].isVoted = false;
	    },
	    setVoteCompleted: (state, payload) => {
	      state.voteCollection[payload.voteId].isCompleted = true;
	    },
	    resetVoteCompleted: (state, payload) => {
	      state.voteCollection[payload.voteId].isCompleted = false;
	    },
	    setLoadingStatus: (state, payload) => {
	      const {
	        voteId,
	        isLoading
	      } = payload;
	      if (state.voteCollection[voteId]) {
	        state.voteCollection[voteId].isLoading = isLoading;
	      }
	    }
	  },
	  formatVoteFields(fields) {
	    return im_v2_model.formatFieldsWithConfig(fields, voteFieldsConfig);
	  },
	  formatQuestionFields(fields) {
	    return im_v2_model.formatFieldsWithConfig(fields, questionFieldsConfig);
	  },
	  formatAnswerFields(fields) {
	    return im_v2_model.formatFieldsWithConfig(fields, answerFieldsConfig);
	  }
	};

	exports.VoteModel = VoteModel;

}((this.BX.Vote.Store = this.BX.Vote.Store || {}),BX,BX.Messenger.v2.Model));
//# sourceMappingURL=index.bundle.js.map
