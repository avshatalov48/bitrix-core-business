this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core_events,main_core,ui_reactionsSelect,ui_lottie,im_v2_component_elements,im_v2_application_core,im_v2_const,im_v2_lib_user,im_v2_lib_logger) {
	'use strict';

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class ReactionService {
	  constructor() {
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	  }
	  setReaction(messageId, reaction) {
	    im_v2_lib_logger.Logger.warn('ReactionService: setReaction', messageId, reaction);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/reactions/setReaction', {
	      messageId,
	      reaction,
	      userId: im_v2_application_core.Core.getUserId()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessageReactionAdd, {
	      messageId,
	      reaction
	    }).catch(error => {
	      console.error('ReactionService: error setting reaction', error);
	    });
	  }
	  removeReaction(messageId, reaction) {
	    im_v2_lib_logger.Logger.warn('ReactionService: removeReaction', messageId, reaction);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/reactions/removeReaction', {
	      messageId,
	      reaction,
	      userId: im_v2_application_core.Core.getUserId()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessageReactionDelete, {
	      messageId,
	      reaction
	    }).catch(error => {
	      console.error('ReactionService: error removing reaction', error);
	    });
	  }
	}

	const SHOW_DELAY = 500;
	const HIDE_DELAY = 500;

	// @vue/component
	const ReactionSelector = {
	  props: {
	    messageId: {
	      type: Number,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    reactionsData() {
	      return this.$store.getters['messages/reactions/getByMessageId'](this.messageId);
	    },
	    ownReactionSet() {
	      var _this$reactionsData, _this$reactionsData$o;
	      return ((_this$reactionsData = this.reactionsData) == null ? void 0 : (_this$reactionsData$o = _this$reactionsData.ownReactions) == null ? void 0 : _this$reactionsData$o.size) > 0;
	    }
	  },
	  methods: {
	    startShowTimer() {
	      this.showTimeout = setTimeout(() => {
	        this.showSelector();
	      }, SHOW_DELAY);
	    },
	    clearShowTimer() {
	      clearTimeout(this.showTimeout);
	      this.setHideTimeout();
	    },
	    showSelector() {
	      this.selector = new ui_reactionsSelect.ReactionsSelect({
	        name: 'im-base-message-reaction-selector',
	        position: this.$refs['container']
	      });
	      this.subscribeToSelectorEvents();
	      this.selector.show();
	    },
	    subscribeToSelectorEvents() {
	      this.selector.subscribe('select', selectEvent => {
	        var _this$selector;
	        const {
	          reaction
	        } = selectEvent.getData();
	        this.getReactionService().setReaction(this.messageId, reaction);
	        (_this$selector = this.selector) == null ? void 0 : _this$selector.hide();
	      });
	      this.selector.subscribe('mouseleave', this.setHideTimeout);
	      this.selector.subscribe('mouseenter', () => {
	        clearTimeout(this.hideTimeout);
	      });
	      this.selector.subscribe('hide', () => {
	        clearTimeout(this.hideTimeout);
	        this.selector = null;
	      });
	    },
	    setHideTimeout() {
	      this.hideTimeout = setTimeout(() => {
	        var _this$selector2;
	        (_this$selector2 = this.selector) == null ? void 0 : _this$selector2.hide();
	      }, HIDE_DELAY);
	    },
	    onIconClick() {
	      this.clearShowTimer();
	      if (this.ownReactionSet) {
	        const [currentReaction] = [...this.reactionsData.ownReactions];
	        this.getReactionService().removeReaction(this.messageId, currentReaction);
	        return;
	      }
	      this.getReactionService().setReaction(this.messageId, ui_reactionsSelect.reactionType.like);
	    },
	    getReactionService() {
	      if (!this.reactionService) {
	        this.reactionService = new ReactionService();
	      }
	      return this.reactionService;
	    }
	  },
	  template: `
		<div
			@mouseenter="startShowTimer"
			@mouseleave="clearShowTimer"
			class="bx-im-reaction-selector__container"
			ref="container"
		>
			<div @click="onIconClick" class="bx-im-reaction-selector__icon" :class="{'--active': ownReactionSet}"></div>
		</div>
	`
	};

	// @vue/component
	const ReactionUser = {
	  components: {
	    Avatar: im_v2_component_elements.Avatar
	  },
	  props: {
	    userId: {
	      type: Number,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    user() {
	      return this.$store.getters['users/get'](this.userId);
	    },
	    avatarStyle() {
	      if (!this.user.avatar) {
	        return;
	      }
	      return {
	        backgroundImage: `url('${this.user.avatar}')`
	      };
	    }
	  },
	  template: `
		<div class="bx-im-reaction-list__user_avatar">
			<Avatar :dialogId="userId" :size="AvatarSize.XS" :withAvatarLetters="false" :withStatus="false" />
		</div>
	`
	};

	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	class UserService {
	  constructor() {
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	  }
	  loadReactionUsers(messageId, reaction) {
	    let users = [];
	    im_v2_lib_logger.Logger.warn('Reactions: UserService: loadReactionUsers', messageId, reaction);
	    const queryParams = {
	      messageId,
	      filter: {
	        reaction
	      }
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imV2ChatMessageReactionTail, queryParams).then(response => {
	      users = response.data().users;
	      return babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(Object.values(users));
	    }).then(() => {
	      return users.map(user => user.id);
	    }).catch(error => {
	      console.error('Reactions: UserService: loadReactionUsers error', error);
	      throw new Error(error);
	    });
	  }
	}

	// @vue/component
	const AdditionalUsers = {
	  components: {
	    UserListPopup: im_v2_component_elements.UserListPopup
	  },
	  props: {
	    messageId: {
	      type: Number,
	      required: true
	    },
	    reaction: {
	      type: String,
	      required: true
	    },
	    show: {
	      type: Boolean,
	      required: true
	    },
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      showPopup: false,
	      loadingAdditionalUsers: false,
	      additionalUsers: []
	    };
	  },
	  watch: {
	    show(newValue, oldValue) {
	      if (!oldValue && newValue) {
	        this.showPopup = true;
	        this.loadUsers();
	      }
	    }
	  },
	  methods: {
	    loadUsers() {
	      this.loadingAdditionalUsers = true;
	      this.getUserService().loadReactionUsers(this.messageId, this.reaction).then(userIds => {
	        this.additionalUsers = userIds;
	        this.loadingAdditionalUsers = false;
	      }).catch(() => {
	        this.loadingAdditionalUsers = false;
	      });
	    },
	    onPopupClose() {
	      this.showPopup = false;
	      this.$emit('close');
	    },
	    prepareAdditionalUsers(userIds) {
	      const firstViewerId = this.dialog.lastMessageViews.firstViewer.userId;
	      return userIds.filter(userId => {
	        return userId !== im_v2_application_core.Core.getUserId() && userId !== firstViewerId;
	      });
	    },
	    getUserService() {
	      if (!this.userService) {
	        this.userService = new UserService();
	      }
	      return this.userService;
	    }
	  },
	  template: `
		<UserListPopup
			id="bx-im-message-reaction-users"
			:showPopup="showPopup"
			:loading="loadingAdditionalUsers"
			:userIds="additionalUsers"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:offsetLeft="-112"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`
	};

	const USERS_TO_SHOW = 5;
	const SHOW_USERS_DELAY = 1500;

	// @vue/component
	const ReactionItem = {
	  components: {
	    ReactionUser,
	    AdditionalUsers
	  },
	  props: {
	    messageId: {
	      type: Number,
	      required: true
	    },
	    type: {
	      type: String,
	      required: true
	    },
	    counter: {
	      type: Number,
	      required: true
	    },
	    users: {
	      type: Array,
	      required: true
	    },
	    selected: {
	      type: Boolean,
	      required: true
	    },
	    animate: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['click'],
	  data() {
	    return {
	      showAdditionalUsers: false
	    };
	  },
	  computed: {
	    showUsers() {
	      const userLimitIsNotReached = this.counter <= USERS_TO_SHOW;
	      const weHaveUsersData = this.counter === this.users.length;
	      return userLimitIsNotReached && weHaveUsersData;
	    },
	    preparedUsers() {
	      return [...this.users].sort((a, b) => a - b).reverse();
	    },
	    reactionClass() {
	      return ui_reactionsSelect.reactionCssClass[this.type];
	    }
	  },
	  mounted() {
	    if (this.animate) {
	      this.playAnimation();
	    }
	  },
	  methods: {
	    playAnimation() {
	      this.animation = ui_lottie.Lottie.loadAnimation({
	        animationData: ui_reactionsSelect.ReactionsSelect.getLottieAnimation(this.type),
	        container: this.$refs['reactionIcon'],
	        loop: false,
	        autoplay: false,
	        renderer: 'svg',
	        rendererSettings: {
	          viewBoxOnly: true
	        }
	      });
	      main_core.Event.bind(this.animation, 'complete', () => {
	        this.animation.destroy();
	      });
	      main_core.Event.bind(this.animation, 'destroy', () => {
	        this.animation = null;
	      });
	      this.animation.play();
	    },
	    startShowUsersTimer() {
	      this.showUsersTimeout = setTimeout(() => {
	        if (this.showUsers) {
	          return;
	        }
	        this.showAdditionalUsers = true;
	      }, SHOW_USERS_DELAY);
	    },
	    clearShowUsersTimer() {
	      clearTimeout(this.showUsersTimeout);
	    },
	    onClick() {
	      clearTimeout(this.showUsersTimeout);
	      this.$emit('click', {
	        animateItemFunction: this.playAnimation
	      });
	    }
	  },
	  template: `
		<div
			@click="onClick" 
			@mouseenter="startShowUsersTimer"
			@mouseleave="clearShowUsersTimer"
			class="bx-im-reaction-list__item"
			:class="{'--selected': selected}"
		>
			<div class="bx-im-reaction-list__item_icon" :class="reactionClass" ref="reactionIcon"></div>
			<div v-if="showUsers" class="bx-im-reaction-list__user_container">
				<TransitionGroup name="bx-im-reaction-list__user_animation">
					<ReactionUser v-for="user in preparedUsers" :key="user" :userId="user" />
				</TransitionGroup>
			</div>
			<div v-else class="bx-im-reaction-list__item_counter" ref="counter">{{ counter }}</div>
			<AdditionalUsers
				:show="showAdditionalUsers"
				:bindElement="$refs['counter'] || {}"
				:messageId="messageId"
				:reaction="type"
				@close="showAdditionalUsers = false"
			/>
		</div>
	`
	};

	// @vue/component
	const ReactionList = {
	  components: {
	    ReactionItem
	  },
	  props: {
	    messageId: {
	      type: Number,
	      required: true
	    }
	  },
	  data() {
	    return {
	      mounted: false
	    };
	  },
	  computed: {
	    Reaction: () => ui_reactionsSelect.reactionType,
	    message() {
	      return this.$store.getters['messages/getById'](this.messageId);
	    },
	    reactionsData() {
	      return this.$store.getters['messages/reactions/getByMessageId'](this.messageId);
	    },
	    reactionCounters() {
	      var _this$reactionsData$r, _this$reactionsData;
	      return (_this$reactionsData$r = (_this$reactionsData = this.reactionsData) == null ? void 0 : _this$reactionsData.reactionCounters) != null ? _this$reactionsData$r : {};
	    },
	    ownReactions() {
	      var _this$reactionsData$o, _this$reactionsData2;
	      return (_this$reactionsData$o = (_this$reactionsData2 = this.reactionsData) == null ? void 0 : _this$reactionsData2.ownReactions) != null ? _this$reactionsData$o : new Set();
	    },
	    showReactionsContainer() {
	      return Object.keys(this.reactionCounters).length > 0;
	    }
	  },
	  watch: {
	    showReactionsContainer(newValue, oldValue) {
	      if (!oldValue && newValue) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	          chatId: this.message.chatId,
	          threshold: im_v2_const.DialogScrollThreshold.nearTheBottom
	        });
	      }
	    }
	  },
	  mounted() {
	    this.mounted = true;
	  },
	  methods: {
	    onReactionSelect(reaction, event) {
	      var _this$ownReactions;
	      const {
	        animateItemFunction
	      } = event;
	      if ((_this$ownReactions = this.ownReactions) != null && _this$ownReactions.has(reaction)) {
	        this.getReactionService().removeReaction(this.messageId, reaction);
	        return;
	      }
	      this.getReactionService().setReaction(this.messageId, reaction);
	      animateItemFunction();
	    },
	    getReactionUsers(reaction) {
	      const users = this.reactionsData.reactionUsers[reaction];
	      if (!users) {
	        return [];
	      }
	      return [...users];
	    },
	    getReactionService() {
	      if (!this.reactionService) {
	        this.reactionService = new ReactionService();
	      }
	      return this.reactionService;
	    }
	  },
	  template: `
		<div v-if="showReactionsContainer" class="bx-im-reaction-list__container">
			<template v-for="reactionType in Reaction">
				<ReactionItem
					v-if="reactionCounters[reactionType] > 0"
					:key="reactionType"
					:messageId="messageId"
					:type="reactionType"
					:counter="reactionCounters[reactionType]"
					:users="getReactionUsers(reactionType)"
					:selected="ownReactions.has(reactionType)"
					:animate="mounted"
					@click="onReactionSelect(reactionType, $event)"
				/>
			</template>
		</div>
	`
	};

	exports.ReactionSelector = ReactionSelector;
	exports.ReactionList = ReactionList;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Event,BX,BX.Ui,BX.UI,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=reaction.bundle.js.map
