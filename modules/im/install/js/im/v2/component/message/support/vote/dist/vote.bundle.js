/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,ui_notification,im_v2_component_message_base,im_v2_lib_dateFormatter,im_v2_application_core,im_v2_const) {
	'use strict';

	const VoteType = {
	  like: 'like',
	  dislike: 'dislike',
	  none: 'none'
	};

	const VoteParamKey = {
	  voteText: 'imolVoteText',
	  likeText: 'imolVoteLike',
	  dislikeText: 'imolVoteDislike',
	  currentVote: 'imolVote',
	  timeLimit: 'imolTimeLimitVote',
	  voteCloseDate: 'imolDateCloseVote'
	};

	var _messageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageId");
	var _dialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	var _updateModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModel");
	class VoteService {
	  constructor(messageId, dialogId) {
	    Object.defineProperty(this, _updateModel, {
	      value: _updateModel2
	    });
	    Object.defineProperty(this, _messageId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogId, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId] = messageId;
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId] = dialogId;
	  }
	  like() {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModel)[_updateModel]({
	      vote: VoteType.like
	    });
	    im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imBotDialogVote, {
	      MESSAGE_ID: babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId],
	      DIALOG_ID: babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId],
	      RATING: VoteType.like
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('VoteService: error in dialog vote', error);
	    });
	  }
	  dislike() {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateModel)[_updateModel]({
	      vote: VoteType.dislike
	    });
	    im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imBotDialogVote, {
	      MESSAGE_ID: babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId],
	      DIALOG_ID: babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId],
	      RATING: VoteType.dislike
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('VoteService: error in dialog vote', error);
	    });
	  }
	}
	function _updateModel2(params) {
	  const {
	    vote
	  } = params;
	  const currentMessage = im_v2_application_core.Core.getStore().getters['messages/getById'](babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId]);
	  const newComponentParams = {
	    ...currentMessage.componentParams,
	    [VoteParamKey.currentVote]: vote
	  };
	  im_v2_application_core.Core.getStore().dispatch('messages/update', {
	    id: babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId],
	    fields: {
	      componentParams: newComponentParams
	    }
	  });
	}

	// @vue/component
	const SupportVoteMessage = {
	  name: 'SupportVote',
	  components: {
	    BaseMessage: im_v2_component_message_base.BaseMessage
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    currentVote() {
	      return this.message.componentParams[VoteParamKey.currentVote] || VoteType.none;
	    },
	    voteText() {
	      if (this.currentVote === VoteType.none) {
	        return this.message.componentParams[VoteParamKey.voteText];
	      }
	      if (this.currentVote === VoteType.like) {
	        return this.message.componentParams[VoteParamKey.likeText];
	      }
	      return this.message.componentParams[VoteParamKey.dislikeText];
	    },
	    voteClosed() {
	      const closeDate = this.message.componentParams[VoteParamKey.voteCloseDate];
	      if (!main_core.Type.isStringFilled(closeDate)) {
	        return false;
	      }
	      return new Date(closeDate).getTime() < Date.now();
	    },
	    voteTimeSecondsLimit() {
	      var _this$message$compone;
	      const limit = (_this$message$compone = this.message.componentParams[VoteParamKey.timeLimit]) != null ? _this$message$compone : 0;
	      return Number.parseInt(limit, 10);
	    },
	    likeClasses() {
	      return {
	        '--active': this.currentVote === VoteType.like,
	        '--disabled': this.currentVote === VoteType.dislike
	      };
	    },
	    dislikeClasses() {
	      return {
	        '--active': this.currentVote === VoteType.dislike,
	        '--disabled': this.currentVote === VoteType.like
	      };
	    }
	  },
	  methods: {
	    onLike() {
	      if (this.currentVote === VoteType.like) {
	        return;
	      }
	      if (this.voteClosed) {
	        this.showVoteClosedNotification();
	        return;
	      }
	      this.getVoteService().like();
	    },
	    onDislike() {
	      if (this.currentVote === VoteType.dislike) {
	        return;
	      }
	      if (this.voteClosed) {
	        this.showVoteClosedNotification();
	        return;
	      }
	      this.getVoteService().dislike();
	    },
	    showVoteClosedNotification() {
	      BX.UI.Notification.Center.notify({
	        content: this.loc('IM_MESSAGE_SUPPORT_VOTE_CLOSED')
	      });
	    },
	    getDaysForVote() {
	      const currentSeconds = Date.now() / 1000;
	      return im_v2_lib_dateFormatter.DateFormatter.formatByCode(currentSeconds - this.voteTimeSecondsLimit, 'ddiff');
	    },
	    getVoteService() {
	      if (!this.voteService) {
	        this.voteService = new VoteService(this.message.id, this.dialogId);
	      }
	      return this.voteService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<BaseMessage
			:item="item"
			:dialogId="dialogId"
			:withTitle="withTitle"
			:withContextMenu="false"
			:withReactions="false"
		>
			<div class="bx-im-message-support-vote__container">
				<div class="bx-im-message-support-vote__title">{{ loc('IM_MESSAGE_SUPPORT_VOTE_TITLE') }}</div>
				<div class="bx-im-message-support-vote__subtitle">{{ voteText }}</div>
				<div class="bx-im-message-support-vote__actions">
					<div class="bx-im-message-support-vote__action_item --like" :class="likeClasses" @click="onLike"></div>
					<div class="bx-im-message-support-vote__action_item --dislike" :class="dislikeClasses" @click="onDislike"></div>
				</div>
			</div>
		</BaseMessage>
	`
	};

	exports.SupportVoteMessage = SupportVoteMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=vote.bundle.js.map
