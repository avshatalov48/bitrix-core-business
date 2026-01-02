/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vote = this.BX.Vote || {};
(function (exports,im_v2_const,vote_provider_service,vote_component_loader,main_core_events,im_v2_lib_menu,ui_iconSet_api_core,vote_analytics,main_core,ui_vue3_directives_hint,vote_application,main_popup,ui_vue3_components_popup) {
	'use strict';

	var _app = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("app");
	var _getCurrentVote = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentVote");
	var _canCompleteVote = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canCompleteVote");
	var _canShowResults = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canShowResults");
	var _canRevokeVote = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canRevokeVote");
	var _getCurrentQuestion = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentQuestion");
	class VoteMessageMenu extends im_v2_lib_menu.MessageMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _getCurrentQuestion, {
	      value: _getCurrentQuestion2
	    });
	    Object.defineProperty(this, _canRevokeVote, {
	      value: _canRevokeVote2
	    });
	    Object.defineProperty(this, _canShowResults, {
	      value: _canShowResults2
	    });
	    Object.defineProperty(this, _canCompleteVote, {
	      value: _canCompleteVote2
	    });
	    Object.defineProperty(this, _getCurrentVote, {
	      value: _getCurrentVote2
	    });
	    Object.defineProperty(this, _app, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _app)[_app] = vote_application.VoteApplication.getInstance();
	  }
	  getMenuItems() {
	    return [this.getReplyItem(), this.getShowResultsItem(), this.getRevokeItem(), this.getCopyLinkItem(), this.getPinItem(), this.getFavoriteItem(), this.getCompleteItem(), this.getDeleteItem()];
	  }
	  getCopyLinkItem() {
	    const copyLinkItem = super.getCopyLinkItem();
	    const {
	      onClick
	    } = copyLinkItem;
	    copyLinkItem.onClick = () => {
	      onClick();
	      vote_analytics.VoteAnalytics.copyLink(this.context.dialogId, this.context.id, 'message_link');
	    };
	    return copyLinkItem;
	  }
	  getRevokeItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canRevokeVote)[_canRevokeVote]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('VOTE_REVOKE'),
	      icon: ui_iconSet_api_core.Outline.UNDO,
	      onClick: () => {
	        main_core_events.EventEmitter.emit('vote:message-menu:revoke-vote', {
	          entityId: this.context.id
	        });
	        this.close();
	      }
	    };
	  }
	  getCompleteItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canCompleteVote)[_canCompleteVote]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('VOTE_POPUP_BTN_COMPLETE'),
	      icon: ui_iconSet_api_core.Outline.CHATS_WITH_CHECK,
	      onClick: () => {
	        main_core_events.EventEmitter.emit('vote:message-menu:complete-vote', {
	          entityId: this.context.id
	        });
	        this.close();
	      }
	    };
	  }
	  getShowResultsItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canShowResults)[_canShowResults]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('VOTE_SHOW_RESULTS'),
	      icon: ui_iconSet_api_core.Outline.POLL,
	      onClick: () => {
	        main_core_events.EventEmitter.emit('vote:message-menu:results-vote', {
	          entityId: this.context.id
	        });
	        this.close();
	      }
	    };
	  }
	}
	function _getCurrentVote2() {
	  const voteCollection = babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].getStore().getters['vote/getVoteCollection'];
	  return voteCollection[this.context.componentParams.id];
	}
	function _canCompleteVote2() {
	  const vote = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentVote)[_getCurrentVote]();
	  if (!vote) {
	    return false;
	  }
	  return !vote.isCompleted && vote.canEdit;
	}
	function _canShowResults2() {
	  const vote = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentVote)[_getCurrentVote]();
	  const question = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentQuestion)[_getCurrentQuestion]();
	  if (!vote || !question) {
	    return false;
	  }
	  return vote.canEdit && babelHelpers.classPrivateFieldLooseBase(this, _getCurrentQuestion)[_getCurrentQuestion]().totalCounter > 0;
	}
	function _canRevokeVote2() {
	  const vote = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentVote)[_getCurrentVote]();
	  if (!vote) {
	    return false;
	  }
	  return !vote.isCompleted && vote.canRevoke && vote.isVoted;
	}
	function _getCurrentQuestion2() {
	  var _this$context$compone, _questionCollection$f;
	  const questions = (_this$context$compone = this.context.componentParams.data) == null ? void 0 : _this$context$compone.questions;
	  if (!questions) {
	    return null;
	  }
	  const [firstQuestionId] = Object.keys(questions);
	  const questionCollection = babelHelpers.classPrivateFieldLooseBase(this, _app)[_app].getStore().getters['vote/getQuestionCollection'];
	  return (_questionCollection$f = questionCollection[firstQuestionId]) != null ? _questionCollection$f : null;
	}

	const getMessage = phraseCode => {
	  return main_core.Loc.getMessage(phraseCode);
	};
	const getMessageWithCount = (phraseCode, counter) => {
	  return main_core.Loc.getMessagePlural(phraseCode, counter, {
	    '#COUNT#': counter
	  });
	};

	// @vue/component
	const VoteQuestion = {
	  name: 'VoteQuestion',
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  props: {
	    contextId: {
	      type: String,
	      required: true
	    },
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    /** @type {FormattedQuestionType} */
	    question: {
	      type: Object,
	      required: true
	    },
	    /** @type {FormattedAnswersType} */
	    answers: {
	      type: Object,
	      required: true
	    },
	    isUserVoted: {
	      type: Boolean,
	      default: false
	    },
	    isCompleted: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['answersSelected'],
	  data() {
	    return {
	      selectedRadioBtn: null,
	      selectedCheckboxes: []
	    };
	  },
	  computed: {
	    answersCollection() {
	      return this.app.getStore().getters['vote/getAnswerCollection'];
	    },
	    formattedAnswers() {
	      const formattedAnswers = {};
	      Object.keys(this.answers).forEach((key, index) => {
	        const answer = this.answers[key];
	        const storeAnswer = this.answersCollection[answer.id] || {};
	        formattedAnswers[key] = {
	          ...answer,
	          counter: storeAnswer.counter || 0,
	          percent: this.question.isMultiple ? Math.round(storeAnswer.percent) : this.roundPercentages[index]
	        };
	      });
	      return formattedAnswers;
	    },
	    getCurrentUserVotes() {
	      return this.app.getStore().getters['vote/getCurrentUserVotes'][this.question.id] || [];
	    },
	    canShowResults() {
	      return this.isUserVoted || this.isCompleted;
	    },
	    answerVotes() {
	      return Object.values(this.answers).map(answer => {
	        var _this$answersCollecti;
	        const counter = (_this$answersCollecti = this.answersCollection[answer.id]) == null ? void 0 : _this$answersCollecti.counter;
	        return counter || 0;
	      });
	    },
	    roundPercentages() {
	      const totalVotes = this.answerVotes.reduce((sum, count) => sum + count, 0);
	      if (totalVotes === 0) {
	        return this.answerVotes.map(() => 0);
	      }
	      const calculatedPercents = this.answerVotes.map(vote => vote / totalVotes * 100);
	      const roundedPercents = calculatedPercents.map(percent => Math.floor(percent));
	      const remainder = 100 - roundedPercents.reduce((sum, p) => sum + p, 0);
	      const fractionalParts = calculatedPercents.map((percent, index) => ({
	        index,
	        fraction: percent % 1
	      })).sort((a, b) => b.fraction - a.fraction);
	      for (let i = 0; i < remainder; i++) {
	        roundedPercents[fractionalParts[i].index] += 1;
	      }
	      return roundedPercents;
	    }
	  },
	  watch: {
	    isLoading(newValue) {
	      if (newValue) {
	        this.selectedCheckboxes = this.getCurrentUserVotes || [];
	        this.selectedRadioBtn = this.getCurrentUserVotes[0];
	      }
	    },
	    isUserVoted(voted) {
	      if (voted) {
	        return;
	      }
	      this.selectedCheckboxes = [];
	      this.selectedRadioBtn = null;
	    }
	  },
	  created() {
	    this.app = vote_application.VoteApplication.init();
	  },
	  methods: {
	    radioChanged() {
	      this.emitAnswersSelectedWithValue([this.selectedRadioBtn]);
	    },
	    checkboxChanged() {
	      this.emitAnswersSelectedWithValue(this.selectedCheckboxes);
	    },
	    emitAnswersSelectedWithValue(answerIds) {
	      const eventData = {
	        questionId: this.question.id,
	        answerIds
	      };
	      this.$emit('answersSelected', eventData);
	    },
	    hasCurrentUserVote(answerId) {
	      if (this.canShowResults) {
	        return this.app.getStore().getters['vote/hasCurrentUserVote'](this.question.id, answerId);
	      }
	      return this.selectedCheckboxes.includes(answerId);
	    },
	    getUniqueAnswerId(answerId) {
	      return `vote-answer-${answerId}-${this.contextId}`;
	    },
	    showHintCounter(counter) {
	      return {
	        text: this.countText(counter),
	        popupOptions: {
	          position: 'bottom',
	          targetContainer: document.body,
	          offsetLeft: 25,
	          offsetTop: 5,
	          autoHide: false,
	          angle: {
	            position: 'top'
	          }
	        }
	      };
	    },
	    countText(counter) {
	      return getMessageWithCount('VOTE_RESULT_COUNT', counter);
	    }
	  },
	  template: `
		<div class="vote__question">
			<div class="vote__question-text">{{ question.question }}</div>
		</div>
		<div :class="['vote__answers', { '--voted': canShowResults }]">
			<div v-for="(answer, answerKey) in formattedAnswers" 
				 :key="answerKey"
				 :class="['vote__answer', { '--selected': hasCurrentUserVote(answer.id) }]"
			>
				<input
					class="vote__answer-select"
					v-if="!isLoading && !question.isMultiple"
					type="radio"
					v-model="selectedRadioBtn"
					:value="answer.id"
					:id="getUniqueAnswerId(answer.id)"
					@change="radioChanged"
				/>
				<input
					class="vote__answer-select --checkbox"
					v-if="!isLoading && question.isMultiple"
					type="checkbox"
					v-model="selectedCheckboxes"
					:value="answer.id"
					:id="getUniqueAnswerId(answer.id)"
					:key="answer.id"
					@change="checkboxChanged"
				/>
				<div class="vote__progress-bar">
					<label class='vote__answer-text' :for="getUniqueAnswerId(answer.id)">{{ answer.message }}</label>
					<transition name="vote__answer-percent-show">
						<div
							v-if="canShowResults"
							v-hint="answer.counter > 0 ? (() => showHintCounter(answer.counter)) : null"
							class="vote__answer-percent"
							:key="'percent-' + answerKey + '-' + answer.counter">
							<span>{{ answer.percent }}</span>
							%
						</div>
					</transition>
					<transition name="vote__progress-bar-filled">
						<div v-if="canShowResults" class="vote__progress-bar-fill"
							 :key="'fill-' + answerKey"
							 :style="{ '--target-width': answer.percent + '%' }"
						></div>
					</transition>
				</div>
			</div>
		</div>
	`
	};

	const ButtonType = Object.freeze({
	  vote: 'vote',
	  disable: 'disable',
	  showResults: 'show'
	});

	// @vue/component
	const ButtonArea = {
	  name: 'ButtonArea',
	  components: {
	    VoteQuestion
	  },
	  props: {
	    /** @type {FormattedQuestionType} */
	    question: {
	      type: Object,
	      required: true
	    },
	    isLoading: {
	      type: Boolean,
	      required: true
	    },
	    isUserVoted: {
	      type: Boolean,
	      default: false
	    },
	    isCompleted: {
	      type: Boolean,
	      default: false
	    },
	    isBtnActive: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['onClickVoteButton', 'showResults'],
	  computed: {
	    isMultipleQuestion() {
	      if (this.isLoading) {
	        return false;
	      }
	      return this.app.getStore().getters['vote/getQuestionCollection'][this.question.id].isMultiple;
	    },
	    buttonType() {
	      if ((this.isUserVoted || this.isCompleted) && this.question.totalCounter > 0) {
	        return ButtonType.showResults;
	      }
	      if (this.isMultipleQuestion && !this.isCompleted) {
	        return ButtonType.vote;
	      }
	      return ButtonType.disable;
	    },
	    isBtnAvailableToVote() {
	      return this.isBtnActive && this.buttonType === ButtonType.vote;
	    },
	    buttonClass() {
	      return `--${this.buttonType}`;
	    },
	    summaryText() {
	      if (this.question.totalCounter > 0) {
	        return getMessageWithCount('VOTE_RESULT_COUNT', this.question.totalCounter);
	      }
	      return getMessage('VOTE_SUMMARY_COUNT_NO_VOTES');
	    },
	    buttonText() {
	      if (this.isUserVoted || !this.isMultipleQuestion || this.isCompleted) {
	        return this.summaryText;
	      }
	      return getMessage('VOTE_BUTTON');
	    }
	  },
	  created() {
	    this.app = vote_application.VoteApplication.init();
	  },
	  methods: {
	    handleButtonClick() {
	      if (this.buttonType === ButtonType.vote) {
	        this.$emit('onClickVoteButton');
	      } else if (this.buttonType === ButtonType.showResults) {
	        this.$emit('showResults');
	      }
	    }
	  },
	  template: `
		<div class="vote-display-btn-wrapper">
			<button class="vote-display-btn"
					@click="handleButtonClick"
					:class="[buttonClass, { '--active': isBtnAvailableToVote }]"
					type="button"
			>
				{{ buttonText }}
			</button>
		</div>
	`
	};

	// @vue/component
	const VotePopup = {
	  name: 'VotePopup',
	  components: {
	    Popup: ui_vue3_components_popup.Popup
	  },
	  emits: ['confirm', 'cancel'],
	  computed: {
	    getMessage: () => getMessage,
	    popupOptions() {
	      return {
	        width: 374,
	        className: 'vote-display__popup'
	      };
	    }
	  },
	  template: `
		<Popup
			:options="popupOptions" @close="$emit('cancel')"
			id="vote-display-popup"
		>
			<div class="vote-display__popup-content">
				<div class="vote-display__popup-title">
					{{ getMessage('VOTE_POPUP_TITLE') }}
				</div>
				<div class="vote-display__popup-text">
					{{ getMessage('VOTE_POPUP_TEXT') }}
				</div>
			</div>
			<div class="vote-display__popup-footer">
				<button class="vote-display__popup-btn --complete" @click="$emit('confirm')">
					{{ getMessage('VOTE_POPUP_BTN_COMPLETE') }}
				</button>
				<button class="vote-display__popup-btn --cancel" @click="$emit('cancel')">
					{{ getMessage('VOTE_POPUP_BTN_CANCEL') }}
				</button>
			</div>
		</Popup>
	`
	};

	const ANONYMOUS_VOTE = 2;
	const ALLOW_REVOKING = 1;

	// @vue/component
	const VoteDisplay = {
	  name: 'VoteDisplay',
	  components: {
	    VoteQuestion,
	    ButtonArea,
	    Loader: vote_component_loader.Loader,
	    VotePopup
	  },
	  props: {
	    voteItem: {
	      type: Object,
	      required: true
	    },
	    entityId: {
	      type: Number,
	      required: true
	    },
	    entityType: {
	      type: String,
	      required: true
	    },
	    contextId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['vote', 'revokeVote', 'copyLink', 'completeVote'],
	  data() {
	    return {
	      isShowPopup: false,
	      questionAnswers: {}
	    };
	  },
	  computed: {
	    getMessage: () => getMessage,
	    firstQuestion() {
	      var _this$voteItem$data, _this$voteItem$data2;
	      const firstKey = Object.keys((_this$voteItem$data = this.voteItem.data) == null ? void 0 : _this$voteItem$data.questions)[0];
	      return (_this$voteItem$data2 = this.voteItem.data) == null ? void 0 : _this$voteItem$data2.questions[firstKey];
	    },
	    questionCollection() {
	      return this.app.getStore().getters['vote/getQuestionCollection'];
	    },
	    voteCollection() {
	      return this.app.getStore().getters['vote/getVoteCollection'];
	    },
	    currentVote() {
	      return this.voteCollection[this.voteItem.id];
	    },
	    formattedQuestion() {
	      const storeQuestion = this.questionCollection[this.firstQuestion.id] || {};
	      return {
	        ...this.firstQuestion,
	        totalCounter: storeQuestion.totalCounter,
	        isMultiple: storeQuestion.isMultiple
	      };
	    },
	    isUserVoted() {
	      if (this.isLoading) {
	        return false;
	      }
	      return this.currentVote.isVoted;
	    },
	    canEdit() {
	      if (this.isLoading) {
	        return false;
	      }
	      return this.currentVote.canEdit;
	    },
	    isAnonymous() {
	      var _this$voteItem$data3;
	      return ((_this$voteItem$data3 = this.voteItem.data) == null ? void 0 : _this$voteItem$data3.anonymity) === ANONYMOUS_VOTE;
	    },
	    isCompleted() {
	      if (this.isLoading) {
	        return false;
	      }
	      return this.currentVote.isCompleted;
	    },
	    hasSelectedAnswers() {
	      return main_core.Type.isArrayFilled(this.questionAnswers[this.firstQuestion.id]);
	    },
	    voteTypeText() {
	      return this.isAnonymous ? getMessage('VOTE_ANONYMOUS') : getMessage('VOTE_PUBLIC');
	    },
	    isLoading() {
	      var _this$currentVote$isL, _this$currentVote;
	      return (_this$currentVote$isL = (_this$currentVote = this.currentVote) == null ? void 0 : _this$currentVote.isLoading) != null ? _this$currentVote$isL : true;
	    },
	    showRevokeNotice() {
	      var _this$voteItem$data4;
	      if (!this.isLoading && this.currentVote.isCompleted) {
	        return false;
	      }
	      return ((_this$voteItem$data4 = this.voteItem.data) == null ? void 0 : _this$voteItem$data4.options) !== ALLOW_REVOKING;
	    }
	  },
	  created() {
	    this.app = vote_application.VoteApplication.init();
	    this.voteService = vote_provider_service.ImVoteService.init();
	    im_v2_lib_menu.MessageMenuManager.getInstance().registerMenuByMessageType(im_v2_const.MessageComponent.voteMessage, VoteMessageMenu);
	    this.subscribeOnEvents();
	  },
	  mounted() {
	    var _this$currentVote2;
	    if (!this.currentVote || ((_this$currentVote2 = this.currentVote) == null ? void 0 : _this$currentVote2.isLoading) !== false) {
	      main_core_events.EventEmitter.emit('vote-message-batch', {
	        messageId: this.entityId
	      });
	    }
	  },
	  beforeUnmount() {
	    this.unsubscribeFromEvents();
	  },
	  methods: {
	    subscribeOnEvents() {
	      main_core_events.EventEmitter.subscribe('vote:message-menu:complete-vote', this.isShowCompletePopup);
	      main_core_events.EventEmitter.subscribe('vote:message-menu:revoke-vote', this.recallVote);
	      main_core_events.EventEmitter.subscribe('vote:message-menu:results-vote', this.showResults);
	    },
	    unsubscribeFromEvents() {
	      main_core_events.EventEmitter.unsubscribe('vote:message-menu:complete-vote', this.isShowCompletePopup);
	      main_core_events.EventEmitter.unsubscribe('vote:message-menu:revoke-vote', this.recallVote);
	      main_core_events.EventEmitter.unsubscribe('vote:message-menu:results-vote', this.showResults);
	    },
	    async answersSelected(event) {
	      this.questionAnswers[event.questionId] = event.answerIds;
	      const currentQuestion = this.questionCollection[event.questionId];
	      if (currentQuestion.isMultiple || this.isUserVoted) {
	        return;
	      }
	      void this.submitVote();
	    },
	    async submitVote() {
	      try {
	        this.app.getStore().dispatch('vote/setUserVoted', {
	          voteId: this.currentVote.id
	        });
	        await this.voteService.sendVote(this.questionAnswers, this.voteItem.id, this.entityId);
	        this.$emit('vote');
	        this.questionAnswers = {};
	      } catch (e) {
	        console.error('Vote: submit vote error', e);
	        this.app.getStore().dispatch('vote/resetUserVoted', {
	          voteId: this.currentVote.id
	        });
	        BX.UI.Notification.Center.notify({
	          content: getMessage('VOTE_NOTICE_ERROR_MESSAGE_SUBMIT'),
	          autoHideDelay: 4000
	        });
	      }
	    },
	    onClickVoteButton() {
	      if (main_core.Type.isArrayFilled(this.questionAnswers[this.formattedQuestion.id])) {
	        this.submitVote();
	      }
	    },
	    async showResults(event) {
	      var _event$data;
	      if (event && ((_event$data = event.data) == null ? void 0 : _event$data.entityId) !== this.entityId) {
	        return;
	      }
	      BX.SidePanel.Instance.open(this.currentVote.resultUrl, {
	        cacheable: false,
	        width: 480,
	        copyLinkLabel: true,
	        events: {
	          onOpen: ({
	            slider
	          }) => {
	            const copyLink = slider.getCopyLinkLabel();
	            copyLink.setOnclick(() => {
	              this.$emit('copyLink');
	            });
	          }
	        }
	      });
	    },
	    async completeVote() {
	      try {
	        this.app.getStore().dispatch('vote/setVoteCompleted', {
	          voteId: this.currentVote.id
	        });
	        await this.voteService.completeVote(this.entityId);
	        this.$emit('completeVote');
	      } catch (e) {
	        console.error('Vote: complete vote error', e);
	        this.app.getStore().dispatch('vote/resetVoteCompleted', {
	          voteId: this.currentVote.id
	        });
	        BX.UI.Notification.Center.notify({
	          content: getMessage('VOTE_NOTICE_ERROR_MESSAGE_COMPLETE'),
	          autoHideDelay: 4000
	        });
	      }
	    },
	    isShowCompletePopup(event) {
	      var _event$data2;
	      if (((_event$data2 = event.data) == null ? void 0 : _event$data2.entityId) !== this.entityId) {
	        return;
	      }
	      this.isShowPopup = true;
	    },
	    onCompletePopupConfirm() {
	      this.isShowPopup = false;
	      this.completeVote();
	    },
	    onCompletePopupCancel() {
	      this.isShowPopup = false;
	    },
	    async recallVote(event) {
	      var _event$data3;
	      if (((_event$data3 = event.data) == null ? void 0 : _event$data3.entityId) !== this.entityId) {
	        return;
	      }
	      const previousSelectedAnswers = this.app.getStore().getters['vote/getCurrentUserVotes'][this.firstQuestion.id];
	      try {
	        this.app.getStore().dispatch('vote/clearVotes', {
	          questionId: this.firstQuestion.id,
	          voteId: this.currentVote.id
	        });
	        this.app.getStore().dispatch('vote/resetUserVoted', {
	          voteId: this.currentVote.id
	        });
	        await this.voteService.revokeVote(this.entityId, this.currentVote.id);
	        this.$emit('revokeVote');
	      } catch (e) {
	        console.error('Vote: recall vote error', e);
	        this.app.getStore().dispatch('vote/updateCurrentUserVotes', {
	          questionId: this.firstQuestion.id,
	          answerIds: previousSelectedAnswers
	        });
	        this.app.getStore().dispatch('vote/setUserVoted', {
	          voteId: this.currentVote.id
	        });
	        BX.UI.Notification.Center.notify({
	          content: getMessage('VOTE_NOTICE_ERROR_MESSAGE_REVOKE'),
	          autoHideDelay: 4000
	        });
	      }
	    }
	  },
	  template: `
		<form class="vote-display">
			<div class="vote-display-inner">
				<VoteQuestion
					:key="formattedQuestion.id"
					:contextId="contextId"
					:isLoading="isLoading"
					:question="formattedQuestion"
					:isUserVoted="isUserVoted"
					:isCompleted="isCompleted"
					:answers="formattedQuestion.answers"
					@answersSelected="answersSelected"
				/>
				<div class="vote-display-bottom-container">
					<div v-if="isLoading" class="vote-display__loader">
						<Loader />
					</div>
					<ButtonArea v-else
						:question="formattedQuestion"
						:isLoading="isLoading"
						:isUserVoted="isUserVoted"
						:isCompleted="isCompleted"
						:isBtnActive="hasSelectedAnswers"
						@onClickVoteButton="onClickVoteButton"
						@showResults="showResults"
					/>
					<div class="vote__notice">
						<span class="vote__notice-text">{{ voteTypeText }}</span>
						<span v-if="showRevokeNotice" class="vote__notice-text">{{ getMessage('VOTE_NOTICE_REVOKE_IS_NOT_AVAILABLE') }}</span>
						<span v-if="isCompleted" class="vote__notice-text">{{ getMessage('VOTE_NOTICE_COMPLETED') }}</span>
					</div>
				</div>
			</div>
		</form>
		<VotePopup
			v-if="isShowPopup"
			@confirm="onCompletePopupConfirm"
			@cancel="onCompletePopupCancel"
		/>
	`
	};

	exports.VoteDisplay = VoteDisplay;

}((this.BX.Vote.Component = this.BX.Vote.Component || {}),BX.Messenger.v2.Const,BX.Vote.Service,BX.Vote.Component,BX.Event,BX.Messenger.v2.Lib,BX.UI.IconSet,BX.Vote,BX,BX.Vue3.Directives,BX.Vote,BX.Main,BX.UI.Vue3.Components));
//# sourceMappingURL=vote.bundle.js.map
