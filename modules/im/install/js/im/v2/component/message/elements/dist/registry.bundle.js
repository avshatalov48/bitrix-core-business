/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_dateFormatter,ui_vue3,ui_lottie,im_v2_lib_user,im_v2_lib_logger,ui_reactionsSelect,main_core,ui_vue3_components_reactions,im_v2_lib_parser,im_v2_application_core,im_v2_component_elements,main_core_events,im_v2_const,im_v2_lib_utils,im_v2_lib_quote) {
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
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    dialogColor() {
	      return this.dialog.type === im_v2_const.DialogType.private ? this.user.color : this.dialog.color;
	    }
	  },
	  created() {
	    ui_vue3.provide('message', this.message);
	  },
	  template: `
		<div v-for="config in message.attach" :key="config.ID" class="bx-im-message-attach__container">
			<Attach :baseColor="dialogColor" :config="config" />
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
			<Avatar 
				:dialogId="userId" 
				:size="AvatarSize.XS" 
				:withAvatarLetters="false" 
				:withStatus="false" 
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
					<ReactionUser v-for="user in preparedUsers" :key="user" :userId="user" />
				</TransitionGroup>
			</div>
			<div v-else class="bx-im-reaction-list__item_counter" ref="counter">{{ counter }}</div>
			<AdditionalUsers
				:show="showAdditionalUsers"
				:bindElement="$refs['users'] || $refs['counter'] || {}"
				:messageId="messageId"
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
	  components: {
	    ReactionItem
	  },
	  props: {
	    messageId: {
	      type: [String, Number],
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
	  props: {
	    messageId: {
	      type: [String, Number],
	      required: true
	    }
	  },
	  computed: {
	    reactionsData() {
	      return this.$store.getters['messages/reactions/getByMessageId'](this.messageId);
	    },
	    ownReactionSet() {
	      var _this$reactionsData, _this$reactionsData$o;
	      return ((_this$reactionsData = this.reactionsData) == null ? void 0 : (_this$reactionsData$o = _this$reactionsData.ownReactions) == null ? void 0 : _this$reactionsData$o.size) > 0;
	    },
	    isGuest() {
	      const message = this.$store.getters['messages/getById'](this.messageId);
	      const dialog = this.$store.getters['dialogues/getByChatId'](message.chatId);
	      return dialog.role === im_v2_const.UserRole.guest;
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.messageId) && !this.isGuest;
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

	// @vue/component
	const DefaultMessageContent$$1 = {
	  name: 'DefaultMessageContent',
	  components: {
	    Reactions: ui_vue3_components_reactions.Reactions,
	    MessageStatus,
	    MessageAttach,
	    ReactionList
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
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    formattedText() {
	      return im_v2_lib_parser.Parser.decodeMessage(this.item);
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    }
	  },
	  template: `
		<div class="bx-im-message-default-content__container bx-im-message-default-content__scope">
			<div v-if="withText" class="bx-im-message-default-content__text" v-html="formattedText"></div>
			<div v-if="message.attach.length > 0" class="bx-im-message-default-content__attach">
				<MessageAttach :item="message" :dialogId="dialogId" />
			</div>
			<div class="bx-im-message-default-content__bottom-panel">
				<ReactionList 
					v-if="canSetReactions" 
					:messageId="message.id" 
					class="bx-im-message-default-content__reaction-list" 
				/>
				<div v-if="withMessageStatus" class="bx-im-message-default-content__status-container">
					<MessageStatus :item="message" />
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const AuthorTitle = {
	  name: 'AuthorTitle',
	  components: {
	    ChatTitle: im_v2_component_elements.ChatTitle
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
	      return this.$store.getters['dialogues/getByChatId'](this.message.chatId);
	    },
	    isSystemMessage() {
	      return this.message.authorId === 0;
	    },
	    isSelfMessage() {
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    isUserChat() {
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    showTitle() {
	      return !this.isSystemMessage && !this.isSelfMessage && !this.isUserChat;
	    },
	    user() {
	      return this.$store.getters['users/get'](this.message.authorId, true);
	    },
	    authorDialogId() {
	      if (this.message.authorId) {
	        return this.message.authorId.toString();
	      }
	      return this.dialogId;
	    }
	  },
	  methods: {
	    onAuthorNameClick() {
	      const authorId = Number.parseInt(this.authorDialogId, 10);
	      if (authorId === im_v2_application_core.Core.getUserId()) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	        mentionText: this.user.name,
	        mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(this.user.id, this.user.name)
	      });
	    }
	  },
	  template: `
		<div v-if="showTitle" @click="onAuthorNameClick" class="bx-im-message-author-title__container">
			<ChatTitle
				:dialogId="authorDialogId"
				:showItsYou="false"
				:withColor="true"
				:withLeftIcon="false"
			/>
		</div>
	`
	};

	// @vue/component
	const ContextMenu = {
	  name: 'ContextMenu',
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    menuIsActiveForId: {
	      type: [String, Number],
	      default: 0
	    }
	  },
	  computed: {
	    menuTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_MESSENGER_MESSAGE_MENU_TITLE', {
	        '#SHORTCUT#': im_v2_lib_utils.Utils.platform.isMac() ? 'CMD' : 'CTRL'
	      });
	    }
	  },
	  methods: {
	    onMenuClick(event) {
	      if (im_v2_lib_utils.Utils.key.isCombination(event, ['Alt+Ctrl'])) {
	        const message = {
	          ...this.message
	        };
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	          text: im_v2_lib_quote.Quote.prepareQuoteText(message),
	          withNewLine: true,
	          replace: false
	        });
	        return;
	      }
	      if (im_v2_lib_utils.Utils.key.isCmdOrCtrl(event)) {
	        const message = {
	          ...this.message
	        };
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.replyMessage, {
	          messageId: message.id
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onClickMessageContextMenu, {
	        message: this.message,
	        event
	      });
	    }
	  },
	  template: `
		<div class="bx-im-message-context-menu__container bx-im-message-context-menu__scope">
			<button
				:title="menuTitle"
				@click="onMenuClick"
				@contextmenu.prevent
				:class="{'--active': menuIsActiveForId === message.id}"
				class="bx-im-message-context-menu__button"
			></button>
		</div>
	`
	};

	exports.MessageStatus = MessageStatus;
	exports.MessageAttach = MessageAttach;
	exports.ReactionList = ReactionList;
	exports.ReactionSelector = ReactionSelector;
	exports.DefaultMessageContent = DefaultMessageContent$$1;
	exports.AuthorTitle = AuthorTitle;
	exports.ContextMenu = ContextMenu;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Im.V2.Lib,BX.Vue3,BX.UI,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Ui,BX,BX.Vue3.Components,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Component.Elements,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
