/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports,main_core,ui_vue3_components_popup,ui_iconSet_api_vue,ui_iconSet_main,ui_iconSet_animated,ui_vue3) {
	'use strict';

	var _signedAttachId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedAttachId");
	var _limit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("limit");
	class VoteResultService {
	  constructor(signedAttachId, limit = 10) {
	    Object.defineProperty(this, _signedAttachId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _limit, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _signedAttachId)[_signedAttachId] = signedAttachId;
	    babelHelpers.classPrivateFieldLooseBase(this, _limit)[_limit] = limit;
	  }
	  async loadAnswer(answerId, page = 1) {
	    var _response$data$items, _response$data;
	    const data = {
	      signedAttachId: babelHelpers.classPrivateFieldLooseBase(this, _signedAttachId)[_signedAttachId],
	      answerId
	    };
	    const navigation = {
	      size: babelHelpers.classPrivateFieldLooseBase(this, _limit)[_limit],
	      page
	    };
	    const response = await BX.ajax.runAction('vote.AttachedVote.getAnswerVoted', {
	      data,
	      navigation
	    });
	    return (_response$data$items = response == null ? void 0 : (_response$data = response.data) == null ? void 0 : _response$data.items) != null ? _response$data$items : [];
	  }
	}

	const VotedUsersList = {
	  name: 'VotedUsersList',
	  components: {
	    Popup: ui_vue3_components_popup.Popup,
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    count: {
	      type: Number,
	      required: true
	    },
	    votedUsers: {
	      /** @type BackendVotedUser[] */
	      type: Array,
	      required: true
	    },
	    signedAttachId: {
	      type: String,
	      required: true
	    },
	    answerId: {
	      type: Number,
	      required: true
	    },
	    maxVisibleAvatarsCount: {
	      type: Number,
	      required: false,
	      default: 3
	    },
	    pageSize: {
	      type: Number,
	      required: true
	    },
	    showUsers: {
	      type: Boolean,
	      required: true
	    }
	  },
	  data() {
	    return {
	      users: this.votedUsers,
	      page: 1,
	      loading: false,
	      isShowPopup: false
	    };
	  },
	  computed: {
	    summaryText() {
	      return main_core.Loc.getMessagePlural('VOTE_JS_ATTACHED_RESULT_ANSWER_VOTED_COUNT', this.count, {
	        '#COUNT#': this.count
	      });
	    },
	    visibleVotedUsers() {
	      return this.votedUsers.slice(0, this.maxVisibleAvatarsCount);
	    },
	    popupOptions() {
	      return {
	        bindElement: this.$refs.showPopupBtn,
	        borderRadius: '18px',
	        autoHide: true
	      };
	    }
	  },
	  methods: {
	    async popupScrollHandler(event) {
	      if (this.loading) {
	        return;
	      }
	      if (this.count <= this.users.length) {
	        return;
	      }
	      if (event.target.scrollHeight - event.target.scrollTop > event.target.clientHeight) {
	        return;
	      }
	      this.loading = true;
	      const nextPage = this.page + 1;
	      const service = new VoteResultService(this.signedAttachId, this.pageSize);
	      try {
	        const nextPageUsers = await service.loadAnswer(this.answerId, nextPage);
	        this.page = nextPage;
	        this.users = [...this.users, ...nextPageUsers];
	      } catch (error) {
	        console.error(error);
	      } finally {
	        this.loading = false;
	      }
	    },
	    getUserImage(user) {
	      return `background-image: url('${encodeURI(user.IMAGE)}')`;
	    }
	  },
	  template: `
		<div class="vote-result__users">
			<div class="vote-result__avatars" v-if="showUsers">
				<span class="vote-result__avatar" v-for="user in visibleVotedUsers" :key="user.INDEX" >
					<i v-if="user.IMAGE" :style="getUserImage(user)" :title="user.NAME" class="vote-result__avatar-img"/>
				</span>
			</div>
			<div @click="isShowPopup = true" ref="showPopupBtn" class="vote-result__users-more" :class = "{ '--disable': !showUsers }">
				{{ summaryText }}
			</div>
			<Popup v-if="isShowPopup" :options="popupOptions" @close="isShowPopup = false">
				<div class="vote-result__users-popup" @scroll="this.popupScrollHandler($event)">
					<div v-for="(user, index) in users" :key="index" class="vote-result__users-popup-item">
						<img v-if="user.IMAGE" class="vote-result__users-popup-avatar" :src="user.IMAGE" alt=""/>
						<BIcon v-else
						   class="vote-result__users-popup-avatar"
						   :name="'person'"
						   :size="26"
						/>
						<div class="vote-result__users-popup-name">{{ user.NAME }}</div>
					</div>
					<BIcon v-if="loading" :name="'loader-wait'" :size="20" />
				</div>
			</Popup>
		</div>
	`
	};

	const ANONYMOUS_VOTE = 2;

	// @vue/component
	const VoteResultDisplay = {
	  name: 'VoteResultDisplay',
	  components: {
	    VotedUsersList
	  },
	  props: {
	    loadedData: {
	      /** @type {BackendResultAll} */
	      type: Object,
	      required: true
	    },
	    votedPageSize: {
	      type: Number,
	      required: true
	    }
	  },
	  computed: {
	    currentVote() {
	      const {
	        VOTE_ID,
	        isVoted,
	        signedAttachId,
	        downloadUrl,
	        canRevote,
	        isFinished
	      } = this.loadedData.attach;
	      return {
	        id: VOTE_ID,
	        isAnonymousVote: this.isAnonymousVote,
	        isVoted,
	        signedAttachId,
	        downloadUrl,
	        canRevoke: canRevote,
	        isFinished
	      };
	    },
	    questions() {
	      var _this$loadedData$atta;
	      const questionsData = (_this$loadedData$atta = this.loadedData.attach) == null ? void 0 : _this$loadedData$atta.QUESTIONS;
	      const calculatedPercentages = this.calculatePercentages(questionsData);
	      const questions = {};
	      Object.keys(questionsData).forEach(questionId => {
	        const question = questionsData[questionId];
	        const answers = {};
	        const isMultiple = question.FIELD_TYPE === '1';
	        Object.keys(question.ANSWERS).forEach((answerId, index) => {
	          const answer = question.ANSWERS[answerId];
	          answers[answerId] = {
	            id: Number(answer.ID),
	            percent: isMultiple ? Math.round(answer.PERCENT) : calculatedPercentages[questionId][index],
	            text: answer.MESSAGE,
	            counter: Number(answer.COUNTER)
	          };
	        });
	        questions[questionId] = {
	          id: Number(question.ID),
	          counter: Number(question.COUNTER),
	          text: question.QUESTION,
	          answers
	        };
	      });
	      return questions;
	    },
	    currentUserVotes() {
	      var _this$loadedData$atta2;
	      const currentUserVotes = {};
	      const userAnswerMap = (_this$loadedData$atta2 = this.loadedData.attach) == null ? void 0 : _this$loadedData$atta2.userAnswerMap;
	      Object.entries(userAnswerMap).forEach(([questionId, answers]) => {
	        currentUserVotes[questionId] = Object.keys(answers).map(answerId => Number(answerId));
	      });
	      return currentUserVotes;
	    },
	    isAnonymousVote() {
	      var _this$loadedData$atta3;
	      return ((_this$loadedData$atta3 = this.loadedData.attach) == null ? void 0 : _this$loadedData$atta3.ANONYMITY) === ANONYMOUS_VOTE;
	    },
	    voteTypeText() {
	      return this.isAnonymousVote ? main_core.Loc.getMessage('VOTE_JS_ATTACHED_RESULT_ANONYMOUS') : main_core.Loc.getMessage('VOTE_JS_ATTACHED_RESULT_PUBLIC');
	    },
	    isFinishedVote() {
	      return this.currentVote.isFinished;
	    },
	    canRevoke() {
	      if (this.currentVote.isFinished) {
	        return this.currentVote.isFinished;
	      }
	      return this.currentVote.canRevoke;
	    }
	  },
	  methods: {
	    hasCurrentUserVote(questionId, answerId) {
	      return this.currentUserVotes[questionId] && this.currentUserVotes[questionId].includes(answerId);
	    },
	    getSummaryText(count) {
	      return main_core.Loc.getMessagePlural('VOTE_JS_ATTACHED_RESULT_QUESTION_VOTED_COUNT', count, {
	        '#COUNT#': count
	      });
	    },
	    getVotedUsers(answerId) {
	      var _this$loadedData$vote, _this$loadedData$vote2;
	      return (_this$loadedData$vote = (_this$loadedData$vote2 = this.loadedData.voted) == null ? void 0 : _this$loadedData$vote2[answerId]) != null ? _this$loadedData$vote : [];
	    },
	    hasVotes(counter) {
	      return counter > 0;
	    },
	    getSelectedClass(questionId, answerId) {
	      return this.hasCurrentUserVote(questionId, answerId) ? '--selected' : '';
	    },
	    calculatePercentages(questions) {
	      const percentages = {};
	      Object.values(questions).forEach(question => {
	        const answers = question.ANSWERS;
	        const answerVotes = Object.values(answers).map(answer => Number(answer.COUNTER));
	        const totalVotes = answerVotes.reduce((sum, count) => sum + count, 0);
	        if (totalVotes === 0) {
	          percentages[question.ID] = answerVotes.map(() => 0);
	          return;
	        }
	        const calculatedPercents = answerVotes.map(vote => vote / totalVotes * 100);
	        const roundedPercents = calculatedPercents.map(percent => Math.floor(percent));
	        const remainder = 100 - roundedPercents.reduce((sum, p) => sum + p, 0);
	        const fractionalParts = calculatedPercents.map((percent, index) => ({
	          index,
	          fraction: percent % 1
	        })).sort((a, b) => b.fraction - a.fraction);
	        for (let i = 0; i < remainder; i++) {
	          roundedPercents[fractionalParts[i].index] += 1;
	        }
	        percentages[question.ID] = roundedPercents;
	      });
	      return percentages;
	    }
	  },
	  template: `
		<div class="vote-result__wrapper">
			<div class="vote-result__info">
			  <span>{{ voteTypeText }}</span>
			  <span v-if="!canRevoke">{{ $Bitrix.Loc.getMessage('VOTE_JS_ATTACHED_RESULT_REVOKE_IS_NOT_AVAILABLE') }} </span>
			  <span v-if="isFinishedVote">{{ $Bitrix.Loc.getMessage('VOTE_JS_ATTACHED_RESULT_VOTE_IS_FINISHED') }}</span>
			</div>
			<div v-for="question in questions" :key="question.id">
				<div class="vote-result__title">{{ question.text }}</div>
				<div class="vote-result__summary">{{ getSummaryText(question.counter) }}</div>
				<div class="vote-result__answers">
					<div class="vote-result__answer" v-for="answer in question.answers" :key="answer.id">
						<div class="vote-result__answer-inner" :class="getSelectedClass(question.id, answer.id)">
							<div class="vote-result__answer-text">{{ answer.text }}</div>
							<div class="vote-result__answer-percent">
								<span>{{ answer.percent }}</span>
								%
							</div>
							<div class="vote-result__answer-fill"
								:style="{ width: answer.percent + '%' }"
							></div>
						</div>
						<VotedUsersList
							v-if="hasVotes(answer.counter)"
							:count="answer.counter"
							:votedUsers="getVotedUsers(answer.id)"
							:signedAttachId="currentVote.signedAttachId"
							:answerId="answer.id"
							:pageSize="votedPageSize"
							:showUsers="!isAnonymousVote"
						/>
					</div>
				</div>
			</div>
		</div>
	`
	};

	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _votedPageSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("votedPageSize");
	class VoteAttachedResult {
	  constructor(options) {
	    Object.defineProperty(this, _application, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _votedPageSize, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _votedPageSize)[_votedPageSize] = options.votedPageSize || 10;
	  }
	  createApplicationWithResult(backendResult) {
	    return ui_vue3.BitrixVue.createApp({
	      name: 'VoteAttachedResultRoot',
	      components: {
	        VoteResultDisplay
	      },
	      props: {
	        loaded: {
	          type: Object,
	          required: true
	        },
	        votedPageSize: {
	          type: Number,
	          required: true
	        }
	      },
	      template: '<VoteResultDisplay :loadedData="loaded" :votedPageSize="votedPageSize"/>'
	    }, {
	      loaded: backendResult,
	      votedPageSize: babelHelpers.classPrivateFieldLooseBase(this, _votedPageSize)[_votedPageSize]
	    });
	  }
	  renderTo(backendResult, container) {
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = this.createApplicationWithResult(backendResult);
	    babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].mount(container);
	  }
	}

	exports.VoteAttachedResult = VoteAttachedResult;

}((this.BX.Vote.Component = this.BX.Vote.Component || {}),BX,BX.UI.Vue3.Components,BX.UI.IconSet,BX,BX,BX.Vue3));
//# sourceMappingURL=attached-result.bundle.js.map
