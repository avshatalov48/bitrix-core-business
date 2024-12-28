/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_dateFormatter,ui_vue3,ui_lottie,im_v2_lib_user,im_v2_lib_logger,ui_reactionsSelect,im_v2_lib_utils,im_v2_application_core,im_v2_lib_menu,im_v2_lib_copilot,im_v2_lib_channel,main_core_events,im_v2_const,im_v2_component_elements,im_v2_lib_permission,im_v2_component_animation,im_v2_provider_service,main_core,ui_vue3_components_reactions,im_v2_lib_parser) {
	'use strict';

	// @vue/component
	const MessageStatus = {
	  name: 'MessageStatus',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    isOverlay: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    formattedDate() {
	      return im_v2_lib_dateFormatter.DateFormatter.formatByCode(this.message.date, im_v2_lib_dateFormatter.DateCode.shortTimeFormat);
	    },
	    isSelfMessage() {
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    messageStatus() {
	      if (this.message.error) {
	        return im_v2_const.OwnMessageStatus.error;
	      }
	      if (this.message.sending) {
	        return im_v2_const.OwnMessageStatus.sending;
	      }
	      if (this.message.viewedByOthers) {
	        return im_v2_const.OwnMessageStatus.viewed;
	      }
	      return im_v2_const.OwnMessageStatus.sent;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-status__container bx-im-message-status__scope" :class="{'--overlay': isOverlay}">
			<div v-if="message.isEdited && !message.isDeleted" class="bx-im-message-status__edit-mark">
				{{ loc('IM_MESSENGER_MESSAGE_EDITED') }}
			</div>
			<div class="bx-im-message-status__date" :class="{'--overlay': isOverlay}">
				{{ formattedDate }}
			</div>
			<div v-if="isSelfMessage" :class="'--' + messageStatus" class="bx-im-message-status__icon"></div>
		</div>
	`
	};

	// @vue/component
	const MessageAttach = {
	  name: 'MessageAttach',
	  components: {
	    Attach: im_v2_component_elements.Attach
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    dialogColor() {
	      return this.dialog.type === im_v2_const.ChatType.user ? this.user.color : this.dialog.color;
	    }
	  },
	  created() {
	    ui_vue3.provide('message', this.message);
	  },
	  template: `
		<div v-for="config in message.attach" :key="config.id" class="bx-im-message-attach__container">
			<Attach :baseColor="dialogColor" :config="config" />
		</div>
	`
	};

	// @vue/component
	const MessageKeyboard = {
	  name: 'MessageKeyboard',
	  components: {
	    Keyboard: im_v2_component_elements.Keyboard
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    }
	  },
	  template: `
		<div class="bx-im-message-keyboard__container">
			<Keyboard :buttons="message.keyboard" :dialogId="dialogId" :messageId="message.id" />
		</div>
	`
	};

	// @vue/component
	const ReactionUser = {
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar
	  },
	  props: {
	    userId: {
	      type: Number,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    user() {
	      return this.$store.getters['users/get'](this.userId);
	    },
	    avatarStyle() {
	      if (!this.user.avatar) {
	        return {};
	      }
	      return {
	        backgroundImage: `url('${this.user.avatar}')`
	      };
	    }
	  },
	  template: `
		<div class="bx-im-reaction-list__user_avatar">
			<ChatAvatar 
				:avatarDialogId="userId" 
				:contextDialogId="contextDialogId" 
				:size="AvatarSize.XS" 
				:withAvatarLetters="false"
				:withTooltip="false"
			/>
		</div>
	`
	};

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	class UserService {
	  constructor() {
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessageReactionTail, queryParams).then(response => {
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
	      type: [String, Number],
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
	    },
	    contextDialogId: {
	      type: String,
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
			:contextDialogId="contextDialogId"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:offsetLeft="-112"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`
	};

	const USERS_TO_SHOW = 5;
	const SHOW_USERS_DELAY = 500;

	// @vue/component
	const ReactionItem = {
	  components: {
	    ReactionUser,
	    AdditionalUsers
	  },
	  props: {
	    messageId: {
	      type: [String, Number],
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
	    },
	    showAvatars: {
	      type: Boolean,
	      required: false,
	      default: true
	    },
	    contextDialogId: {
	      type: String,
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
	      if (!this.showAvatars) {
	        return false;
	      }
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
	        container: this.$refs.reactionIcon,
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
	        this.showAdditionalUsers = true;
	      }, SHOW_USERS_DELAY);
	    },
	    clearShowUsersTimer() {
	      clearTimeout(this.showUsersTimeout);
	    },
	    onClick() {
	      this.clearShowUsersTimer();
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
			<div v-if="showUsers" class="bx-im-reaction-list__user_container" ref="users">
				<TransitionGroup name="bx-im-reaction-list__user_animation">
					<ReactionUser 
						v-for="user in preparedUsers" 
						:key="user" 
						:userId="user"
						:contextDialogId="contextDialogId"
					/>
				</TransitionGroup>
			</div>
			<div v-else class="bx-im-reaction-list__item_counter" ref="counter">{{ counter }}</div>
			<AdditionalUsers
				:show="showAdditionalUsers"
				:bindElement="$refs['users'] || $refs['counter'] || {}"
				:messageId="messageId"
				:contextDialogId="contextDialogId"
				:reaction="type"
				@close="showAdditionalUsers = false"
			/>
		</div>
	`
	};

	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class ReactionService {
	  constructor() {
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1] = im_v2_application_core.Core.getRestClient();
	  }
	  setReaction(messageId, reaction) {
	    im_v2_lib_logger.Logger.warn('ReactionService: setReaction', messageId, reaction);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/reactions/setReaction', {
	      messageId,
	      reaction,
	      userId: im_v2_application_core.Core.getUserId()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imV2ChatMessageReactionAdd, {
	      messageId,
	      reaction
	    }).catch(error => {
	      console.error('ReactionService: error setting reaction', error);
	    });
	  }
	  removeReaction(messageId, reaction) {
	    im_v2_lib_logger.Logger.warn('ReactionService: removeReaction', messageId, reaction);
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1].dispatch('messages/reactions/removeReaction', {
	      messageId,
	      reaction,
	      userId: im_v2_application_core.Core.getUserId()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imV2ChatMessageReactionDelete, {
	      messageId,
	      reaction
	    }).catch(error => {
	      console.error('ReactionService: error removing reaction', error);
	    });
	  }
	}

	// @vue/component
	const ReactionList = {
	  name: 'ReactionList',
	  components: {
	    ReactionItem
	  },
	  props: {
	    messageId: {
	      type: [String, Number],
	      required: true
	    },
	    contextDialogId: {
	      type: String,
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
	    dialog() {
	      return this.$store.getters['chats/getByChatId'](this.message.chatId);
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
	    },
	    isChannel() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.dialog.dialogId);
	    },
	    showAvatars() {
	      return !this.isChannel;
	    }
	  },
	  watch: {
	    showReactionsContainer(newValue, oldValue) {
	      if (!oldValue && newValue) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	          chatId: this.message.chatId,
	          threshold: im_v2_const.DialogScrollThreshold.nearTheBottom,
	          animation: false
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
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      if (!permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.setReaction, this.dialog.dialogId)) {
	        return;
	      }
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
		<div v-if="showReactionsContainer" class="bx-im-reaction-list__container bx-im-reaction-list__scope">
			<template v-for="reactionType in Reaction">
				<ReactionItem
					v-if="reactionCounters[reactionType] > 0"
					:key="reactionType + messageId"
					:messageId="messageId"
					:type="reactionType"
					:counter="reactionCounters[reactionType]"
					:users="getReactionUsers(reactionType)"
					:selected="ownReactions.has(reactionType)"
					:animate="mounted"
					:showAvatars="showAvatars"
					:contextDialogId="contextDialogId"
					@click="onReactionSelect(reactionType, $event)"
				/>
			</template>
		</div>
	`
	};

	const SHOW_DELAY = 500;
	const HIDE_DELAY = 800;

	// @vue/component
	const ReactionSelector = {
	  name: 'ReactionSelector',
	  props: {
	    messageId: {
	      type: [String, Number],
	      required: true
	    }
	  },
	  computed: {
	    message() {
	      return this.$store.getters['messages/getById'](this.messageId);
	    },
	    dialog() {
	      return this.$store.getters['chats/getByChatId'](this.message.chatId);
	    },
	    reactionsData() {
	      return this.$store.getters['messages/reactions/getByMessageId'](this.messageId);
	    },
	    ownReactionSet() {
	      var _this$reactionsData, _this$reactionsData$o;
	      return ((_this$reactionsData = this.reactionsData) == null ? void 0 : (_this$reactionsData$o = _this$reactionsData.ownReactions) == null ? void 0 : _this$reactionsData$o.size) > 0;
	    },
	    isBot() {
	      const user = this.$store.getters['users/get'](this.dialog.dialogId);
	      return (user == null ? void 0 : user.type) === im_v2_const.UserType.bot;
	    },
	    hasError() {
	      return this.message.error;
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.messageId) && this.canSetReactionsByRole && !this.isBot && !this.hasError;
	    },
	    canSetReactionsByRole() {
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      return permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.setReaction, this.dialog.dialogId);
	    }
	  },
	  methods: {
	    startShowTimer() {
	      var _this$selector;
	      this.clearHideTimer();
	      if ((_this$selector = this.selector) != null && _this$selector.isShown()) {
	        return;
	      }
	      this.showTimeout = setTimeout(() => {
	        this.showSelector();
	      }, SHOW_DELAY);
	    },
	    clearShowTimer() {
	      clearTimeout(this.showTimeout);
	      this.startHideTimer();
	    },
	    showSelector() {
	      this.selector = new ui_reactionsSelect.ReactionsSelect({
	        name: 'im-base-message-reaction-selector',
	        position: this.$refs.selector
	      });
	      this.subscribeToSelectorEvents();
	      this.selector.show();
	    },
	    subscribeToSelectorEvents() {
	      this.selector.subscribe('select', selectEvent => {
	        var _this$selector2;
	        const {
	          reaction
	        } = selectEvent.getData();
	        this.getReactionService().setReaction(this.messageId, reaction);
	        (_this$selector2 = this.selector) == null ? void 0 : _this$selector2.hide();
	      });
	      this.selector.subscribe('mouseleave', this.startHideTimer);
	      this.selector.subscribe('mouseenter', () => {
	        clearTimeout(this.hideTimeout);
	      });
	      this.selector.subscribe('hide', () => {
	        clearTimeout(this.hideTimeout);
	        this.selector = null;
	      });
	    },
	    startHideTimer() {
	      this.hideTimeout = setTimeout(() => {
	        var _this$selector3;
	        (_this$selector3 = this.selector) == null ? void 0 : _this$selector3.hide();
	      }, HIDE_DELAY);
	    },
	    clearHideTimer() {
	      clearTimeout(this.hideTimeout);
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
		<div v-if="canSetReactions" class="bx-im-reaction-selector__container">
			<div
				@click="onIconClick"
				@mouseenter="startShowTimer"
				@mouseleave="clearShowTimer"
				class="bx-im-reaction-selector__selector"
				ref="selector"
			>
				<div class="bx-im-reaction-selector__icon" :class="{'--active': ownReactionSet}"></div>
			</div>
		</div>
	`
	};

	const NO_CONTEXT_TAG = 'none';

	// @vue/component
	const Reply = {
	  name: 'ReplyComponent',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    replyId: {
	      type: Number,
	      required: true
	    },
	    isForward: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    replyMessage() {
	      return this.$store.getters['messages/getById'](this.replyId);
	    },
	    replyMessageChat() {
	      var _this$replyMessage;
	      return this.$store.getters['chats/getByChatId']((_this$replyMessage = this.replyMessage) == null ? void 0 : _this$replyMessage.chatId);
	    },
	    replyAuthor() {
	      return this.$store.getters['users/get'](this.replyMessage.authorId);
	    },
	    replyTitle() {
	      return this.replyAuthor ? this.replyAuthor.name : this.loc('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
	    },
	    replyText() {
	      let text = im_v2_lib_parser.Parser.prepareQuote(this.replyMessage);
	      text = im_v2_lib_parser.Parser.decodeText(text);
	      return text;
	    },
	    isQuoteFromTheSameChat() {
	      var _this$replyMessage2;
	      return ((_this$replyMessage2 = this.replyMessage) == null ? void 0 : _this$replyMessage2.chatId) === this.dialog.chatId;
	    },
	    replyContext() {
	      if (!this.isQuoteFromTheSameChat) {
	        return NO_CONTEXT_TAG;
	      }
	      if (!this.isForward) {
	        return `${this.dialogId}/${this.replyId}`;
	      }
	      return `${this.replyMessageChat.dialogId}/${this.replyId}`;
	    },
	    canShowReply() {
	      return !main_core.Type.isNil(this.replyMessage);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div v-if="canShowReply" class="bx-im-message-quote" :data-context="replyContext">
			<div class="bx-im-message-quote__wrap">
				<div class="bx-im-message-quote__name">
					<div class="bx-im-message-quote__name-text">{{ replyTitle }}</div>
				</div>
				<div class="bx-im-message-quote__text" v-html="replyText"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const AuthorTitle = {
	  name: 'AuthorTitle',
	  components: {
	    MessageAuthorTitle: im_v2_component_elements.MessageAuthorTitle
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/getByChatId'](this.message.chatId);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.message.authorId, true);
	    },
	    isSystemMessage() {
	      return this.message.authorId === 0;
	    },
	    isSelfMessage() {
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    isUserChat() {
	      return this.dialog.type === im_v2_const.ChatType.user && !this.isBotWithFakeAuthorNames;
	    },
	    isBotWithFakeAuthorNames() {
	      return this.isSupportBot || this.isNetworkBot;
	    },
	    isNetworkBot() {
	      return this.$store.getters['users/bots/isNetwork'](this.dialog.dialogId);
	    },
	    isSupportBot() {
	      return this.$store.getters['users/bots/isSupport'](this.dialog.dialogId);
	    },
	    showTitle() {
	      return !this.isSystemMessage && !this.isSelfMessage && !this.isUserChat;
	    },
	    authorDialogId() {
	      if (this.message.authorId) {
	        return this.message.authorId.toString();
	      }
	      return this.dialogId;
	    },
	    isCopilot() {
	      const authorId = Number.parseInt(this.authorDialogId, 10);
	      return this.$store.getters['users/bots/isCopilot'](authorId);
	    }
	  },
	  methods: {
	    onAuthorNameClick() {
	      const authorId = Number.parseInt(this.authorDialogId, 10);
	      if (!authorId || authorId === im_v2_application_core.Core.getUserId() || this.isCopilot) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	        mentionText: this.user.name,
	        mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(this.user.id, this.user.name),
	        dialogId: this.dialog.dialogId
	      });
	    }
	  },
	  template: `
		<div 
			v-if="showTitle" 
			@click="onAuthorNameClick" 
			class="bx-im-message-author-title__container" 
			:class="{'--clickable': !isCopilot}"
		>
			<MessageAuthorTitle
				:dialogId="authorDialogId"
				:messageId="message.id"
				:showItsYou="false"
				:withColor="true"
				:withLeftIcon="!isCopilot"
			/>
		</div>
	`
	};

	// @vue/component
	const ContextMenu = {
	  name: 'ContextMenu',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    message: {
	      type: Object,
	      required: true
	    },
	    menuIsActiveForId: {
	      type: [String, Number],
	      default: 0
	    },
	    showContextMenu: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    menuTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_MESSENGER_MESSAGE_MENU_TITLE', {
	        '#SHORTCUT#': im_v2_lib_utils.Utils.platform.isMac() ? 'CMD' : 'CTRL'
	      });
	    },
	    messageItem() {
	      return this.message;
	    },
	    messageHasError() {
	      return this.messageItem.error;
	    },
	    canShowContextMenu() {
	      return this.showContextMenu && !this.messageHasError;
	    },
	    isBulkActionsMode() {
	      return this.$store.getters['messages/select/getBulkActionsMode'];
	    }
	  },
	  methods: {
	    onMenuClick(event) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onClickMessageContextMenu, {
	        message: this.message,
	        dialogId: this.dialogId,
	        event
	      });
	    }
	  },
	  template: `
		<template v-if="!isBulkActionsMode">
			<div v-if="canShowContextMenu" class="bx-im-message-context-menu__container bx-im-message-context-menu__scope">
				<button
					:title="menuTitle"
					@click="onMenuClick"
					@contextmenu.prevent
					:class="{'--active': menuIsActiveForId === message.id}"
					class="bx-im-message-context-menu__button"
				></button>
			</div>
			<div v-else class="bx-im-message-base__context-menu-placeholder"></div>
		</template>
	`
	};

	var _isOwnMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isOwnMessage");
	var _hasError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasError");
	var _retrySend = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("retrySend");
	var _retrySendMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("retrySendMessage");
	class RetryContextMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _retrySendMessage, {
	      value: _retrySendMessage2
	    });
	    Object.defineProperty(this, _retrySend, {
	      value: _retrySend2
	    });
	    Object.defineProperty(this, _hasError, {
	      value: _hasError2
	    });
	    Object.defineProperty(this, _isOwnMessage, {
	      value: _isOwnMessage2
	    });
	    this.id = 'bx-im-message-retry-context-menu';
	  }
	  getMenuItems() {
	    return [this.getRetryItem(), this.getDeleteItem()];
	  }
	  getRetryItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || !babelHelpers.classPrivateFieldLooseBase(this, _hasError)[_hasError]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_MESSENGER_MESSAGE_CONTEXT_MENU_RETRY'),
	      onclick: () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _retrySend)[_retrySend]();
	        this.menuInstance.close();
	      }
	    };
	  }
	  getDeleteItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || !babelHelpers.classPrivateFieldLooseBase(this, _hasError)[_hasError]()) {
	      return null;
	    }
	    const phrase = main_core.Loc.getMessage('IM_MESSENGER_MESSAGE_CONTEXT_MENU_DELETE');
	    return {
	      html: `<span class="bx-im-message-retry-button__context-menu-delete">${phrase}</span>`,
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        messageService.deleteMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	}
	function _isOwnMessage2() {
	  return this.context.authorId === im_v2_application_core.Core.getUserId();
	}
	function _hasError2() {
	  return this.context.error;
	}
	function _retrySend2() {
	  const hasFiles = this.context.files.length > 0;
	  if (hasFiles) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _retrySendMessage)[_retrySendMessage]();
	}
	function _retrySendMessage2() {
	  new im_v2_provider_service.SendingService().retrySendMessage({
	    tempMessageId: this.context.id,
	    dialogId: this.context.dialogId
	  });
	}

	// @vue/component
	const RetryButton = {
	  name: 'RetryButton',
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    messageItem() {
	      return this.message;
	    },
	    menuTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_MESSENGER_MESSAGE_CONTEXT_MENU_RETRY');
	    }
	  },
	  created() {
	    this.contextMenu = new RetryContextMenu();
	  },
	  methods: {
	    onClick(event) {
	      const context = {
	        dialogId: this.dialogId,
	        ...this.messageItem
	      };
	      this.contextMenu.openMenu(context, event.currentTarget);
	    }
	  },
	  template: `
		<div class="bx-im-message-retry-button__container bx-im-message-retry-button__scope">
			<button
				:title="menuTitle"
				@click="onClick"
				class="bx-im-message-retry-button__arrow"
			></button>
		</div>
	`
	};

	// @vue/component
	const MessageHeader = {
	  name: 'MessageHeader',
	  components: {
	    AuthorTitle
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    forwardAuthorId() {
	      return this.message.forward.userId;
	    },
	    forwardContextId() {
	      return this.message.forward.id;
	    },
	    isForwarded() {
	      return this.$store.getters['messages/isForward'](this.message.id);
	    },
	    isChannelForward() {
	      return im_v2_lib_channel.ChannelManager.channelTypes.has(this.message.forward.chatType);
	    },
	    forwardAuthorName() {
	      const copilotManager = new im_v2_lib_copilot.CopilotManager();
	      if (copilotManager.isCopilotBot(this.forwardAuthorId)) {
	        const forwardMessageId = this.forwardContextId.split('/')[1];
	        return copilotManager.getNameWithRole({
	          dialogId: this.forwardAuthorId,
	          messageId: forwardMessageId
	        });
	      }
	      return this.$store.getters['users/get'](this.forwardAuthorId, true).name;
	    },
	    forwardChatName() {
	      var _this$message$forward;
	      return (_this$message$forward = this.message.forward.chatTitle) != null ? _this$message$forward : this.loc('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_CLOSED_CHANNEL');
	    },
	    isSystemMessage() {
	      return this.message.forward.userId === 0;
	    },
	    forwardAuthorTitle() {
	      return main_core.Loc.getMessage('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_FROM_CHAT', {
	        '[user_name]': '<span class="bx-im-message-header__author-name">',
	        '#USER_NAME#': main_core.Text.encode(this.forwardAuthorName),
	        '[/user_name]': '</span>'
	      });
	    },
	    forwardChannelTitle() {
	      return main_core.Loc.getMessage('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_FROM_CHANNEL', {
	        '[user_name]': '<span class="bx-im-message-header__author-name">',
	        '#USER_NAME#': main_core.Text.encode(this.forwardAuthorName),
	        '[/user_name]': '</span>',
	        '[channel_name]': '<span class="bx-im-message-header__author-name">',
	        '#CHANNEL_NAME#': main_core.Text.encode(this.forwardChatName),
	        '[/channel_name]': '</span>'
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    },
	    onForwardClick() {
	      const contextCode = im_v2_lib_parser.Parser.getContextCodeFromForwardId(this.forwardContextId);
	      if (contextCode.length === 0) {
	        return;
	      }
	      const [dialogId, messageId] = contextCode.split('/');
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.goToMessageContext, {
	        messageId: Number.parseInt(messageId, 10),
	        dialogId: dialogId.toString()
	      });
	    }
	  },
	  template: `
		<div v-if="isForwarded" class="bx-im-message-header__container" @click="onForwardClick">
			<span v-if="isSystemMessage">{{ loc('IM_MESSENGER_MESSAGE_HEADER_FORWARDED_FROM_SYSTEM')}}</span>
			<span v-else-if="isChannelForward" v-html="forwardChannelTitle"></span>
			<span v-else v-html="forwardAuthorTitle"></span>
		</div>
		<AuthorTitle v-else-if="withTitle" :item="item" />
	`
	};

	// @vue/component
	const CommentsPanel = {
	  name: 'CommentsPanel',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    FadeAnimation: im_v2_component_animation.FadeAnimation
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId);
	    },
	    message() {
	      return this.item;
	    },
	    commentInfo() {
	      return this.$store.getters['messages/comments/getByMessageId'](this.message.id);
	    },
	    commentsChatId() {
	      return this.commentInfo.chatId;
	    },
	    commentsCount() {
	      // remove first system message from count
	      if (this.commentInfo.messageCount > 0) {
	        return this.commentInfo.messageCount - 1;
	      }
	      return this.commentInfo.messageCount;
	    },
	    commentsCountText() {
	      return main_core.Loc.getMessagePlural('IM_MESSAGE_COMMENTS_PANEL_COMMENT_COUNT', this.commentsCount, {
	        '#COUNT#': this.commentsCount
	      });
	    },
	    noComments() {
	      return this.commentsCount === 0;
	    },
	    lastUsers() {
	      return [...this.commentInfo.lastUserIds].map(userId => {
	        return this.$store.getters['users/get'](userId);
	      }).reverse();
	    },
	    unreadCount() {
	      const counter = this.$store.getters['counters/getSpecificCommentsCounter']({
	        channelId: this.dialog.chatId,
	        commentChatId: this.commentsChatId
	      });
	      if (!counter) {
	        return '';
	      }
	      return `+${counter}`;
	    },
	    isSubscribed() {
	      return this.$store.getters['messages/comments/isUserSubscribed'](this.message.id);
	    },
	    showSubscribeIcon() {
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      return permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.subscribeToComments, this.dialogId);
	    },
	    subscribeIconTitle() {
	      if (this.isSubscribed) {
	        return this.loc('IM_MESSAGE_COMMENTS_PANEL_ICON_UNSUBSCRIBE');
	      }
	      return this.loc('IM_MESSAGE_COMMENTS_PANEL_ICON_SUBSCRIBE');
	    }
	  },
	  methods: {
	    onCommentsClick() {
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      if (!permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.openComments, this.dialogId)) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.openComments, {
	        messageId: this.message.id
	      });
	    },
	    onSubscribeIconClick() {
	      if (this.isSubscribed) {
	        im_v2_provider_service.CommentsService.unsubscribe(this.message.id);
	        return;
	      }
	      im_v2_provider_service.CommentsService.subscribe(this.message.id);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-comments-panel__container" @click="onCommentsClick">
			<div class="bx-im-message-comments-panel__left">
				<div v-if="noComments" class="bx-im-message-comments-panel__empty_container">
					<div class="bx-im-message-comments-panel__empty_icon"></div>
					<div class="bx-im-message-comments-panel__text">{{ loc('IM_MESSAGE_COMMENTS_PANEL_EMPTY_TEXT') }}</div>
				</div>
				<div v-else class="bx-im-message-comments-panel__meta_container">
					<div class="bx-im-message-comments-panel__user_container">
						<TransitionGroup name="bx-im-message-comments-panel__user_animation">
							<div v-for="(user, index) in lastUsers" :key="user.id" class="bx-im-message-comments-panel__user_avatar" :class="'--image-' + (index + 1)">
								<ChatAvatar
									:avatarDialogId="user.id"
									:contextDialogId="dialogId"
									:size="AvatarSize.S"
									:withTooltip="false"
								/>
							</div>
						</TransitionGroup>
					</div>
					<div class="bx-im-message-comments-panel__text">{{ commentsCountText }}</div>
					<FadeAnimation :duration="200">
						<div v-if="unreadCount" class="bx-im-message-comments-panel__unread-counter">{{ unreadCount }}</div>
					</FadeAnimation>
				</div>
			</div>
			<div v-if="showSubscribeIcon" :title="subscribeIconTitle" class="bx-im-message-comments-panel__right">
				<div
					@click.stop="onSubscribeIconClick"
					class="bx-im-message-comments-panel__subscribe-icon"
					:class="{'--active': isSubscribed}"
				></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const MessageFooter = {
	  name: 'MessageFooter',
	  components: {
	    CommentsPanel
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId);
	    },
	    message() {
	      return this.item;
	    },
	    isChannelPost() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.dialogId);
	    },
	    isSystemMessage() {
	      return this.message.authorId === 0;
	    },
	    showCommentsPanel() {
	      return this.isChannelPost && !this.isSystemMessage;
	    }
	  },
	  template: `
		<CommentsPanel v-if="showCommentsPanel" :dialogId="dialogId" :item="item" />
	`
	};

	// @vue/component
	const DefaultMessageContent$$1 = {
	  name: 'DefaultMessageContent',
	  components: {
	    Reactions: ui_vue3_components_reactions.Reactions,
	    MessageStatus,
	    MessageAttach,
	    ReactionList,
	    Reply
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
	    withMessageStatus: {
	      type: Boolean,
	      default: true
	    },
	    withText: {
	      type: Boolean,
	      default: true
	    },
	    withAttach: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    isReply() {
	      return this.message.replyId !== 0;
	    },
	    formattedText() {
	      return im_v2_lib_parser.Parser.decodeMessage(this.item);
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    },
	    isForward() {
	      return this.$store.getters['messages/isForward'](this.message.id);
	    }
	  },
	  template: `
		<div class="bx-im-message-default-content__container bx-im-message-default-content__scope" :class="{'--no-text': !withText}">
			<Reply v-if="isReply" :dialogId="dialogId" :replyId="message.replyId" :isForward="isForward" />
			<div v-if="withText" class="bx-im-message-default-content__text" v-html="formattedText"></div>
			<div v-if="withAttach && message.attach.length > 0" class="bx-im-message-default-content__attach">
				<MessageAttach :item="message" :dialogId="dialogId" />
			</div>
			<div class="bx-im-message-default-content__bottom-panel">
				<ReactionList 
					v-if="canSetReactions" 
					:messageId="message.id" 
					:contextDialogId="dialogId"
					class="bx-im-message-default-content__reaction-list" 
				/>
				<div v-if="withMessageStatus" class="bx-im-message-default-content__status-container">
					<MessageStatus :item="message" />
				</div>
			</div>
		</div>
	`
	};

	exports.MessageStatus = MessageStatus;
	exports.MessageAttach = MessageAttach;
	exports.MessageKeyboard = MessageKeyboard;
	exports.ReactionList = ReactionList;
	exports.ReactionSelector = ReactionSelector;
	exports.Reply = Reply;
	exports.AuthorTitle = AuthorTitle;
	exports.ContextMenu = ContextMenu;
	exports.RetryButton = RetryButton;
	exports.MessageHeader = MessageHeader;
	exports.MessageFooter = MessageFooter;
	exports.DefaultMessageContent = DefaultMessageContent$$1;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Lib,BX.Vue3,BX.UI,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Ui,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Service,BX,BX.Vue3.Components,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
