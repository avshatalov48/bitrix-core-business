/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_notification,im_v2_lib_layout,im_v2_lib_utils,im_v2_lib_channel,im_v2_lib_notifier,im_v2_component_elements_loader,im_v2_component_animation,ui_dialogs_tooltip,im_v2_component_elements_chatTitle,im_v2_lib_copilot,im_v2_component_entitySelector,main_popup,im_v2_component_elements_popup,ui_iconSet_api_vue,im_v2_lib_healthCheck,im_v2_provider_service_recent,im_v2_lib_promo,im_v2_lib_invite,im_v2_component_content_chatForms_forms,im_v2_lib_feature,im_public,im_v2_component_elements_copilotRolesDialog,im_v2_lib_theme,im_v2_provider_service_copilot,main_core,im_v2_application_core,im_v2_lib_analytics,main_core_events,im_v2_component_elements_avatar,im_v2_lib_permission,im_v2_component_content_elements,im_v2_component_elements_toggle,im_v2_provider_service_comments,im_v2_lib_logger,im_v2_model,im_v2_component_dialog_chat,im_v2_component_messageList,im_v2_lib_messageComponent,im_v2_const,im_v2_component_textarea,im_v2_component_elements_button,im_v2_provider_service_chat) {
	'use strict';

	// @vue/component
	const CommentsButton = {
	  name: 'CommentsButton',
	  components: {
	    FloatButton: im_v2_component_dialog_chat.FloatButton
	  },
	  props: {
	    counter: {
	      type: Number,
	      required: true
	    }
	  },
	  computed: {
	    floatButtonProps() {
	      return {
	        icon: im_v2_component_dialog_chat.FloatButtonIcon.comment,
	        color: im_v2_component_dialog_chat.FloatButtonColor.success,
	        counter: this.counter
	      };
	    }
	  },
	  template: `
		<FloatButton v-bind="floatButtonProps" />
	`
	};

	// @vue/component
	const ChannelDialog = {
	  name: 'ChannelDialog',
	  components: {
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    CommentsButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      lastScrolledChatId: 0
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    isChatLayout() {
	      return this.layout.name === im_v2_const.Layout.chat;
	    },
	    channelComments() {
	      return this.$store.getters['counters/getChannelComments'](this.dialog.chatId);
	    },
	    totalChannelCommentsCounter() {
	      let counter = 0;
	      Object.values(this.channelComments).forEach(commentCounter => {
	        counter += commentCounter;
	      });
	      return counter;
	    },
	    showCommentsButton() {
	      return this.isChatLayout && this.totalChannelCommentsCounter > 0;
	    }
	  },
	  beforeUnmount() {
	    this.readAllChannelComments();
	  },
	  methods: {
	    async onCommentsButtonClick() {
	      const chatIdToJump = this.getNextChatIdToJump();
	      this.lastScrolledChatId = chatIdToJump;
	      const messageIdToJump = this.$store.getters['messages/comments/getMessageIdByChatId'](chatIdToJump);
	      if (messageIdToJump) {
	        this.$refs.dialog.goToMessageContext(messageIdToJump, {
	          position: im_v2_component_dialog_chat.ScrollManager.scrollPosition.messageBottom
	        });
	        return;
	      }
	      await this.goToMessageContextByCommentsChatId(chatIdToJump);
	    },
	    async goToMessageContextByCommentsChatId(chatId) {
	      this.$refs.dialog.showLoadingBar();
	      const messageId = await this.$refs.dialog.getMessageService().loadContextByChatId(chatId);
	      this.$refs.dialog.hideLoadingBar();
	      if (!messageId) {
	        // eslint-disable-next-line no-console
	        console.error('ChannelDialog: no messageId after loading context');
	      }
	      await this.$nextTick();
	      this.$refs.dialog.getScrollManager().scrollToMessage(messageId, {
	        position: im_v2_component_dialog_chat.ScrollManager.scrollPosition.messageBottom
	      });
	      await this.$nextTick();
	      this.$refs.dialog.highlightMessage(messageId);
	    },
	    getNextChatIdToJump() {
	      const commentChatIds = this.getCommentsChatIds();
	      commentChatIds.sort((a, z) => a - z);
	      if (this.lastScrolledChatId === 0) {
	        return commentChatIds[0];
	      }
	      const filteredChatIds = commentChatIds.filter(chatId => chatId > this.lastScrolledChatId);
	      if (filteredChatIds.length === 0) {
	        return commentChatIds[0];
	      }
	      return filteredChatIds[0];
	    },
	    getCommentsChatIds() {
	      return Object.keys(this.channelComments).map(chatId => {
	        return Number(chatId);
	      });
	    },
	    readAllChannelComments() {
	      void im_v2_provider_service_comments.CommentsService.readAllChannelComments(this.dialogId);
	    }
	  },
	  template: `
		<ChatDialog ref="dialog" :dialogId="dialogId" :clearOnExit="isGuest">
			<template #additional-float-button>
				<CommentsButton
					v-if="showCommentsButton"
					:dialogId="dialogId"
					:counter="totalChannelCommentsCounter"
					@click="onCommentsButtonClick"
				/>
			</template>
		</ChatDialog>
	`
	};

	// @vue/component
	const JoinPanel = {
	  components: {
	    ChatButton: im_v2_component_elements_button.ChatButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    ButtonColor: () => im_v2_component_elements_button.ButtonColor
	  },
	  methods: {
	    onButtonClick() {
	      this.getChatService().joinChat(this.dialogId);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__textarea_placeholder">
			<ChatButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Primary"
				:text="loc('IM_CONTENT_BLOCKED_TEXTAREA_JOIN_CHANNEL_V2')"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`
	};

	// @vue/component
	const ChannelTextarea = {
	  name: 'ChannelTextarea',
	  components: {
	    ChatTextarea: im_v2_component_textarea.ChatTextarea
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<ChatTextarea
			:dialogId="dialogId"
			:placeholder="this.loc('IM_CONTENT_CHANNEL_TEXTAREA_PLACEHOLDER')"
			:withMarket="false"
			class="bx-im-channel-send-panel__container"
		/>
	`
	};

	const ChannelContent = {
	  name: 'ChannelContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    ChannelDialog,
	    ChannelTextarea,
	    JoinPanel
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId">
			<template #dialog>
				<ChannelDialog :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #join-panel>
				<JoinPanel :dialogId="dialogId" />
			</template>
			<template #textarea="{ onTextareaMount }">
				<ChannelTextarea :dialogId="dialogId" :key="dialogId" @mounted="onTextareaMount" />
			</template>
		</BaseChatContent>
	`
	};

	// @vue/component
	const CollabTitle = {
	  name: 'CollabTitle',
	  components: {
	    ChatTitle: im_v2_component_elements_chatTitle.ChatTitle,
	    LineLoader: im_v2_component_elements_loader.LineLoader,
	    FadeAnimation: im_v2_component_animation.FadeAnimation
	  },
	  inject: ['currentSidebarPanel'],
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    collabInfo() {
	      return this.$store.getters['chats/collabs/getByChatId'](this.dialog.chatId);
	    },
	    guestCounter() {
	      return this.collabInfo.guestCount;
	    },
	    userCounterText() {
	      return main_core.Loc.getMessagePlural('IM_CONTENT_CHAT_HEADER_USER_COUNT', this.dialog.userCounter, {
	        '#COUNT#': this.dialog.userCounter
	      });
	    },
	    guestCounterText() {
	      return main_core.Loc.getMessagePlural('IM_CONTENT_COLLAB_HEADER_GUEST_COUNT', this.guestCounter, {
	        '#COUNT#': this.guestCounter
	      });
	    }
	  },
	  methods: {
	    onMembersClick() {
	      if (this.currentSidebarPanel === im_v2_const.SidebarDetailBlock.members) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: im_v2_const.SidebarDetailBlock.members
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.members,
	        dialogId: this.dialogId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-collab-header-title__container">
			<div class="bx-im-collab-header-title__title-container --ellipsis">
				<ChatTitle :dialogId="dialogId" :withAutoDelete="true" />
			</div>
			<LineLoader v-if="!dialog.inited" :width="50" :height="16" />
			<FadeAnimation :duration="100">
				<div v-if="dialog.inited" class="bx-im-collab-header-title__subtitle_container">
					<div @click="onMembersClick" class="bx-im-collab-header-title__subtitle_content --ellipsis">
						<span
							:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')"
							class="bx-im-collab-header-title__user-counter"
						>
							{{ userCounterText }}
						</span>
						<span v-if="guestCounter > 0" class="bx-im-collab-header-title__guest-counter">
							{{ guestCounterText }}
						</span>
					</div>
				</div>
			</FadeAnimation>
		</div>
	`
	};

	const IconKey = Object.freeze({
	  addToChat: 'add-to-chat',
	  collabEntities: 'collab-entities'
	});

	let _ = t => t,
	  _t;
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _text = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("text");
	var _iconKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("iconKey");
	class CollabTooltipContent {
	  constructor(params) {
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _text, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _iconKey, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = params.title;
	    babelHelpers.classPrivateFieldLooseBase(this, _text)[_text] = params.text;
	    babelHelpers.classPrivateFieldLooseBase(this, _iconKey)[_iconKey] = params.iconKey;
	  }
	  render() {
	    return main_core.Tag.render(_t || (_t = _`
			<div class="bx-im-collab-tooltip__scope bx-im-collab-tooltip__container">
				<div class="bx-im-collab-tooltip__icon --${0}"></div>
				<div class="bx-im-collab-tooltip__title-container">
					<div class="bx-im-collab-tooltip__title">
						${0}
					</div>
					<div class="bx-im-collab-tooltip__text">
						${0}
					</div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _iconKey)[_iconKey], babelHelpers.classPrivateFieldLooseBase(this, _title)[_title], babelHelpers.classPrivateFieldLooseBase(this, _text)[_text]);
	  }
	}

	// @vue/component
	const EntityCounter = {
	  name: 'EntityCounter',
	  props: {
	    counter: {
	      type: Number,
	      required: true
	    }
	  },
	  computed: {
	    preparedCounter() {
	      return this.counter > 99 ? '99+' : this.counter.toString();
	    }
	  },
	  template: `
		<span class="bx-im-collab-header__link-counter">
			{{ preparedCounter }}
		</span>
	`
	};

	// @vue/component
	const EntityLink = {
	  name: 'EntityLink',
	  components: {
	    EntityCounter
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    compactMode: {
	      type: Boolean,
	      required: true
	    },
	    type: {
	      type: String,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    url: {
	      type: String,
	      required: true
	    },
	    counter: {
	      type: [Number, null],
	      default: null
	    }
	  },
	  computed: {
	    showCounter() {
	      return !main_core.Type.isNull(this.counter) && this.counter > 0;
	    }
	  },
	  methods: {
	    onLinkClick() {
	      im_v2_lib_analytics.Analytics.getInstance().collabEntities.onClick(this.dialogId, this.type);
	      BX.SidePanel.Instance.open(this.url, {
	        cacheable: false,
	        customLeftBoundary: 0
	      });
	    }
	  },
	  template: `
		<a :href="url" @click.prevent="onLinkClick" class="bx-im-collab-header__link" :class="'--' + type">
			<span v-if="compactMode" class="bx-im-collab-header__link-icon"></span>
			<span v-else class="bx-im-collab-header__link-text">{{ title }}</span>
			<EntityCounter v-if="showCounter" :counter="counter" />
		</a>
	`
	};

	// @vue/component
	const EntitiesPanel = {
	  name: 'EntitiesPanel',
	  components: {
	    EntityLink
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    compactMode: {
	      type: Boolean,
	      required: true
	    }
	  },
	  data() {
	    return {
	      highlightMode: false
	    };
	  },
	  computed: {
	    CollabEntityType: () => im_v2_const.CollabEntityType,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    collabInfo() {
	      return this.$store.getters['chats/collabs/getByChatId'](this.dialog.chatId);
	    },
	    tasksInfo() {
	      return this.collabInfo.entities.tasks;
	    },
	    tasksUrl() {
	      return this.tasksInfo.url;
	    },
	    tasksCounter() {
	      return this.tasksInfo.counter;
	    },
	    filesInfo() {
	      return this.collabInfo.entities.files;
	    },
	    filesUrl() {
	      return this.filesInfo.url;
	    },
	    calendarInfo() {
	      return this.collabInfo.entities.calendar;
	    },
	    calendarUrl() {
	      return this.calendarInfo.url;
	    },
	    calendarCounter() {
	      return this.calendarInfo.counter;
	    }
	  },
	  mounted() {
	    this.initPromo();
	  },
	  methods: {
	    initPromo() {
	      const promoManager = im_v2_lib_promo.PromoManager.getInstance();
	      const promoId = im_v2_const.PromoId.collabEntities;
	      const promoParams = {
	        chatId: this.dialog.chatId
	      };
	      if (promoManager.needToShow(promoId, promoParams)) {
	        void this.showCollabEntitiesPromo(promoId, promoParams);
	      }
	    },
	    async showCollabEntitiesPromo(promoId, promoParams) {
	      const tooltip = new ui_dialogs_tooltip.Tooltip({
	        bindElement: this.$refs['collab-entities'],
	        content: this.renderTooltipContent(),
	        minWidth: 410,
	        popupOptions: {
	          offsetTop: 11,
	          offsetLeft: 38,
	          autoHide: true,
	          closeIcon: true,
	          closeIconSize: main_popup.CloseIconSize.LARGE,
	          events: {
	            onPopupShow: () => {
	              this.highlightMode = true;
	            },
	            onPopupClose: () => {
	              this.highlightMode = false;
	            }
	          }
	        }
	      });
	      tooltip.show();
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(promoId, promoParams);
	    },
	    renderTooltipContent() {
	      const tooltipContent = new CollabTooltipContent({
	        title: this.loc('IM_CONTENT_COLLAB_ONBOARDING_ENTITIES_TITLE'),
	        text: this.loc('IM_CONTENT_COLLAB_ONBOARDING_ENTITIES_TEXT'),
	        iconKey: IconKey.collabEntities
	      });
	      return tooltipContent.render();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-collab-header__links-container"
			:class="{'--compact': compactMode, '--highlight': highlightMode}"
			ref="collab-entities"
		>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="tasksUrl"
				:type="CollabEntityType.tasks"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_TASKS')"
				:counter="tasksCounter"
			/>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="filesUrl"
				:type="CollabEntityType.files"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_FILES')"
			/>
			<EntityLink
				:dialogId="dialogId"
				:compactMode="compactMode"
				:url="calendarUrl"
				:type="CollabEntityType.calendar"
				:title="loc('IM_CONTENT_COLLAB_HEADER_LINK_CALENDAR')"
				:counter="calendarCounter"
			/>
		</div>
	`
	};

	const PromoMessages = {
	  [im_v2_const.PromoId.membersNotInvitedOneDayToCollab]: {
	    title: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_ONE_DAY_TITLE',
	    text: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_ONE_DAY_TEXT'
	  },
	  [im_v2_const.PromoId.membersNotInvitedFourDayToCollab]: {
	    title: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_FOUR_DAYS_TITLE',
	    text: 'IM_CONTENT_COLLAB_ONBOARDING_INVITE_MEMBERS_FOUR_DAYS_TEXT'
	  }
	};

	// @vue/component
	const AddToChatButton = {
	  name: 'AddToChatButton',
	  components: {
	    AddToCollab: im_v2_component_entitySelector.AddToCollab
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withAnimation: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      showAddToChatPopup: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    }
	  },
	  mounted() {
	    this.initPromo();
	  },
	  methods: {
	    openAddToChatPopup() {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatHeaderClick(this.dialogId);
	      this.showAddToChatPopup = true;
	    },
	    closeAddToChatPopup() {
	      this.$emit('close');
	      this.showAddToChatPopup = false;
	    },
	    initPromo() {
	      const promoManager = im_v2_lib_promo.PromoManager.getInstance();
	      Object.keys(PromoMessages).forEach(promoId => {
	        const promoParams = {
	          chatId: this.dialog.chatId
	        };
	        if (promoManager.needToShow(promoId, promoParams)) {
	          void this.showMembersNotInvitedPromo(promoId, promoParams);
	        }
	      });
	    },
	    showMembersNotInvitedPromo(promoId, promoParams) {
	      const tooltip = new ui_dialogs_tooltip.Tooltip({
	        bindElement: this.$refs['add-members'],
	        content: this.renderTooltipContent(promoId),
	        minWidth: 410,
	        popupOptions: {
	          offsetTop: 8,
	          offsetLeft: 9,
	          autoHide: true,
	          closeIcon: true,
	          closeIconSize: main_popup.CloseIconSize.LARGE
	        }
	      });
	      tooltip.show();
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(promoId, promoParams);
	    },
	    renderTooltipContent(promoId) {
	      const tooltipContent = new CollabTooltipContent({
	        title: this.loc(PromoMessages[promoId].title),
	        text: this.loc(PromoMessages[promoId].text),
	        iconKey: IconKey.addToChat
	      });
	      return tooltipContent.render();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_INVITE_POPUP_TITLE')"
			:class="{'--active': showAddToChatPopup}"
			class="bx-im-collab-header__add-people-icon"
			@click="openAddToChatPopup"
			ref="add-members"
		></div>
		<AddToCollab
			v-if="showAddToChatPopup"
			:bindElement="$refs['add-members'] ?? {}"
			:dialogId="dialogId"
			:popupConfig="{ offsetTop: 25, offsetLeft: -300 }"
			@close="closeAddToChatPopup"
		/>
	`
	};

	const RING_COUNT = 3;

	// @vue/component
	const PulseAnimation = {
	  name: 'PulseAnimation',
	  props: {
	    showPulse: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    rings() {
	      if (!this.showPulse) {
	        return [];
	      }
	      return Array.from({
	        length: RING_COUNT
	      });
	    }
	  },
	  template: `
		<div class="bx-im-pulse-animation__container">
			<slot />
			<div v-for="ring in rings" class="bx-im-pulse-animation__ring"></div>
		</div>
	`
	};

	// @vue/component
	const CollabHeader = {
	  name: 'CollabHeader',
	  components: {
	    ChatHeader: im_v2_component_content_elements.ChatHeader,
	    CollabTitle,
	    EntitiesPanel,
	    AddToChatButton,
	    AddToChatPopup: im_v2_component_entitySelector.AddToChat,
	    PulseAnimation
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      compactMode: false,
	      showAddToChatPopupDelayed: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    }
	  },
	  watch: {
	    async isInited(isInited) {
	      if (isInited && this.showAddToChatPopupDelayed) {
	        await this.$nextTick();
	        this.openAddToChatPopup();
	      }
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.header.openAddToChatPopup, this.onOpenAddToChatPopup);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.header.openAddToChatPopup, this.onOpenAddToChatPopup);
	  },
	  methods: {
	    onOpenAddToChatPopup() {
	      if (!this.isInited) {
	        this.showAddToChatPopupDelayed = true;
	        return;
	      }
	      this.openAddToChatPopup();
	    },
	    openAddToChatPopup() {
	      this.$refs['add-to-chat-button'].openAddToChatPopup();
	    },
	    onCompactModeChange(compactMode) {
	      this.compactMode = compactMode;
	    }
	  },
	  template: `
		<ChatHeader :dialogId="dialogId" @compactModeChange="onCompactModeChange" class="bx-im-collab-header__container">
			<template #title>
				<CollabTitle :dialogId="dialogId" />
			</template>
			<template #before-actions>
				<EntitiesPanel :dialogId="dialogId" :compactMode="compactMode" />
			</template>
			<template #add-to-chat-button>
				<PulseAnimation :showPulse="showAddToChatPopupDelayed">
					<AddToChatButton 
						:withAnimation="showAddToChatPopupDelayed" 
						:dialogId="dialogId" 
						ref="add-to-chat-button" 
						@close="showAddToChatPopupDelayed = false"
					/>
				</PulseAnimation>
			</template>
		</ChatHeader>
	`
	};

	const CollabContent = {
	  name: 'CollabContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    CollabHeader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    }
	  },
	  watch: {
	    dialogInited(newValue, oldValue) {
	      if (!newValue || oldValue) {
	        return;
	      }
	      this.initPromo();
	    }
	  },
	  methods: {
	    initPromo() {
	      const promoManager = im_v2_lib_promo.PromoManager.getInstance();
	      const promoId = im_v2_const.PromoId.collaberNotAcceptInvitationOneDay;
	      const promoParams = {
	        chatId: this.dialog.chatId
	      };
	      if (promoManager.needToShow(promoId, promoParams)) {
	        this.showNotAcceptInvitationPromo(promoId, promoParams);
	      }
	    },
	    showNotAcceptInvitationPromo(promoId, promoParams) {
	      im_v2_lib_notifier.Notifier.collab.onCollaberNotAcceptInvitation();
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(promoId, promoParams);
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId">
			<template #header>
				<CollabHeader :dialogId="dialogId" :key="dialogId" />
			</template>
		</BaseChatContent>
	`
	};

	// @vue/component
	const MultidialogChatTitle = {
	  name: 'MultidialogChatTitle',
	  components: {
	    EditableChatTitle: im_v2_component_elements_chatTitle.EditableChatTitle,
	    ChatTitle: im_v2_component_elements_chatTitle.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['newTitle'],
	  computed: {
	    isSupportBot() {
	      return this.$store.getters['users/bots/isSupport'](this.dialogId);
	    },
	    subtitle() {
	      return this.$Bitrix.Loc.getMessage('IM_CONTENT_CHAT_HEADER_SUPPORT_SUBTITLE');
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__info">
			<ChatTitle v-if="isSupportBot" :dialogId="dialogId" />
			<EditableChatTitle v-else :dialogId="dialogId" @newTitleSubmit="$emit('newTitle', $event)" />
			<div class="bx-im-chat-header__subtitle_container">
				<div class="bx-im-chat-header__subtitle_content">{{ subtitle }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const MultidialogHeader = {
	  name: 'MultidialogHeader',
	  components: {
	    ChatHeader: im_v2_component_content_elements.ChatHeader,
	    MultidialogChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  template: `
		<ChatHeader :dialogId="dialogId">
			<template #title="{ onNewTitleHandler }">
				<MultidialogChatTitle
					:dialogId="dialogId"
					@newTitle="onNewTitleHandler"
				/>
			</template>
		</ChatHeader>
	`
	};

	const MultidialogContent = {
	  name: 'MultidialogContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    MultidialogHeader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId">
			<template #header>
				<MultidialogHeader :dialogId="dialogId" :key="dialogId" />
			</template>
		</BaseChatContent>
	`
	};

	// @vue/component
	const NotesContent = {
	  name: 'NotesContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    ChatHeader: im_v2_component_content_elements.ChatHeader,
	    ChatTitle: im_v2_component_elements_chatTitle.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    titleType() {
	      return Number.parseInt(this.dialogId, 10) === im_v2_application_core.Core.getUserId() ? im_v2_component_elements_chatTitle.ChatTitleType.notes : '';
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId">
			<template #header>
				<ChatHeader :dialogId="dialogId" :withCallButton="false" :withAddToChatButton="false">
					<template #title>
						<ChatTitle :dialogId="dialogId" :customType="titleType" :showItsYou="false"/>
					</template>
				</ChatHeader>
			</template>
		</BaseChatContent>
	`
	};

	const POPUP_ID = 'im-add-to-chat-hint-popup';

	// @vue/component
	const AddToChatHint = {
	  name: 'AddToChatHint',
	  components: {
	    MessengerPopup: im_v2_component_elements_popup.MessengerPopup
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close', 'hide'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        darkMode: true,
	        bindElement: this.bindElement,
	        angle: true,
	        width: 346,
	        closeIcon: true,
	        offsetLeft: 8,
	        className: 'bx-im-copilot-add-to-chat-hint__scope',
	        contentBorderRadius: 0
	      };
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
			@close="$emit('close')"
		>
			<div class="bx-im-copilot-add-to-chat-hint__title">
				{{ loc('IM_CONTENT_COPILOT_ADD_TO_CHAT_HINT_TITLE') }}
			</div>
			<br />
			<div class="bx-im-copilot-add-to-chat-hint__description">
				{{ loc('IM_CONTENT_COPILOT_ADD_TO_CHAT_HINT_DESCRIPTION') }}
			</div>
			<br />
			<button class="bx-im-copilot-add-to-chat-hint__hide" @click="$emit('hide')">
				{{ loc('IM_CONTENT_COPILOT_ADD_TO_CHAT_HINT_HIDE') }}
			</button>
		</MessengerPopup>
	`
	};

	// @vue/component
	const AddToChatButton$1 = {
	  name: 'AddToChatButton',
	  components: {
	    AddToChat: im_v2_component_entitySelector.AddToChat,
	    AddToChatHint
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      showInviteButton: false,
	      showAddToChatPopup: false,
	      showAddToChatHint: false
	    };
	  },
	  mounted() {
	    const needCopilotInRecentTabHint = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.copilotInRecentTab);
	    const needAddUsersToChatHint = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.addUsersToCopilotChat);
	    this.showAddToChatHint = !needCopilotInRecentTabHint && needAddUsersToChatHint;
	  },
	  methods: {
	    openAddToChatPopup() {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatHeaderClick(this.dialogId);
	      this.showAddToChatPopup = true;
	    },
	    closeAddToChatPopup() {
	      this.showAddToChatPopup = false;
	    },
	    showHint() {
	      this.showAddToChatHint = true;
	    },
	    closeHint() {
	      this.showAddToChatHint = false;
	    },
	    onHintHide() {
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.addUsersToCopilotChat);
	      this.closeHint();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_INVITE_POPUP_TITLE')"
			:class="{'--active': showAddToChatPopup}"
			class="bx-im-chat-header__icon --add-people"
			@click="openAddToChatPopup"
			ref="add-members"
		>
			<slot name="invite-hint" :inviteButtonRef="$refs['add-members']"></slot>
		</div>
		<AddToChat
			v-if="showAddToChatPopup"
			:bindElement="$refs['add-members'] ?? {}"
			:dialogId="dialogId"
			:popupConfig="{ offsetTop: 15, offsetLeft: -300 }"
			@close="closeAddToChatPopup"
		/>
		<AddToChatHint
			v-if="showAddToChatHint"
			:bindElement="$refs['add-members'] ?? {}"
			@close="closeHint"
			@hide="onHintHide"
		/>
	`
	};

	// @vue/component
	const CopilotChatHeader = {
	  name: 'CopilotChatHeader',
	  components: {
	    ChatHeader: im_v2_component_content_elements.ChatHeader,
	    EditableChatTitle: im_v2_component_elements_chatTitle.EditableChatTitle,
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar,
	    AddToChatButton: AddToChatButton$1
	  },
	  inject: ['currentSidebarPanel'],
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      buttonPanelReady: false
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    },
	    isGroupCopilotChat() {
	      return new im_v2_lib_copilot.CopilotManager().isGroupCopilotChat(this.dialogId);
	    },
	    copilotRole() {
	      var _role$name;
	      const role = this.$store.getters['copilot/chats/getRole'](this.dialogId);
	      return (_role$name = role == null ? void 0 : role.name) != null ? _role$name : '';
	    },
	    formattedUserCounter() {
	      return main_core.Loc.getMessagePlural('IM_CONTENT_COPILOT_HEADER_USER_COUNT', this.dialog.userCounter, {
	        '#COUNT#': this.dialog.userCounter
	      });
	    }
	  },
	  methods: {
	    onNewTitleSubmit(newTitle) {
	      void this.getChatService().renameChat(this.dialogId, newTitle);
	    },
	    onMembersClick() {
	      if (!this.isInited) {
	        return;
	      }
	      if (this.currentSidebarPanel === im_v2_const.SidebarDetailBlock.members) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: im_v2_const.SidebarDetailBlock.members
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.members,
	        dialogId: this.dialogId
	      });
	    },
	    onButtonPanelReady() {
	      this.buttonPanelReady = true;
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<ChatHeader
			:dialogId="dialogId"
			:withSearchButton="false"
			class="bx-im-copilot-header__container"
			@buttonPanelReady="onButtonPanelReady"
		>
			<template #left>
				<div class="bx-im-copilot-header__avatar">
					<ChatAvatar
						:avatarDialogId="dialogId"
						:contextDialogId="dialogId"
						:withSpecialTypes="false"
						:size="AvatarSize.L"
					/>
				</div>
				<div class="bx-im-copilot-header__info">
					<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit" />
					<div
						v-if="isGroupCopilotChat"
						:title="loc('IM_CONTENT_COPILOT_HEADER_OPEN_MEMBERS_TITLE')"
						@click="onMembersClick"
						class="bx-im-copilot-header__subtitle --click"
					>
						{{ formattedUserCounter }}
					</div>
					<div v-else class="bx-im-copilot-header__subtitle">
						{{ copilotRole }}
					</div>
				</div>
			</template>
			<template v-if="buttonPanelReady" #add-to-chat-button>
				<AddToChatButton :dialogId="dialogId" />
			</template>
		</ChatHeader>
	`
	};

	// @vue/component
	const CopilotTextarea = {
	  name: 'CopilotTextarea',
	  components: {
	    ChatTextarea: im_v2_component_textarea.ChatTextarea
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<ChatTextarea
			:dialogId="dialogId"
			:placeholder="loc('IM_CONTENT_COPILOT_TEXTAREA_PLACEHOLDER')"
			:withMarket="false"
			:withEdit="false"
			:withUploadMenu="false"
			:withSmileSelector="false"
		/>
	`
	};

	// @vue/component
	const CopilotContent = {
	  name: 'CopilotContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    CopilotChatHeader,
	    CopilotTextarea
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId" :withDropArea="false">
			<template #header>
				<CopilotChatHeader :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #textarea="{ onTextareaMount }">
				<CopilotTextarea :dialogId="dialogId" :key="dialogId" @mounted="onTextareaMount" />
			</template>
		</BaseChatContent>
	`
	};

	class HealthCheckService {
	  getEndpoint() {
	    const langId = main_core.Loc.getMessage('LANGUAGE_ID');
	    const {
	      serviceHealthUrl
	    } = im_v2_application_core.Core.getApplicationData();
	    const url = new URL(serviceHealthUrl);
	    url.searchParams.set('userLang', langId);
	    return url.toString();
	  }
	  async getServiceHealthStatus() {
	    const endpoint = this.getEndpoint();
	    return new Promise(resolve => {
	      main_core.ajax.get(endpoint, rawJson => {
	        let response = {};
	        try {
	          response = JSON.parse(rawJson);
	        } catch {
	          resolve(null);
	          return;
	        }
	        if (response.result !== 'error') {
	          resolve(null);
	          return;
	        }
	        resolve(response);
	      });
	    });
	  }
	}

	const ICON_SIZE = 18;

	// @vue/component
	const ServiceHealthPanel = {
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  data() {
	    return {
	      isShow: false,
	      title: '',
	      text: ''
	    };
	  },
	  computed: {
	    Color: () => im_v2_const.Color,
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    ICON_SIZE: () => ICON_SIZE,
	    isServiceHealthPanelShown() {
	      return im_v2_lib_healthCheck.HealthCheckManager.getInstance().getIsShown();
	    },
	    processedTitle() {
	      return BX.util.strip_tags(this.title);
	    },
	    processedText() {
	      return BX.util.strip_tags(this.text);
	    }
	  },
	  async created() {
	    if (this.isServiceHealthPanelShown) {
	      return;
	    }
	    const data = await new HealthCheckService().getServiceHealthStatus();
	    if (!data) {
	      return;
	    }
	    this.setData(data);
	    this.show();
	  },
	  methods: {
	    setData(data) {
	      const {
	        statusTitle,
	        statusInfo
	      } = data;
	      this.title = statusTitle;
	      this.text = statusInfo;
	    },
	    show() {
	      this.isShow = true;
	    },
	    hide() {
	      this.isShow = false;
	      im_v2_lib_healthCheck.HealthCheckManager.getInstance().setIsShown(true);
	    }
	  },
	  template: `
		<div v-if="isShow" class="bx-im-content-chat__service-health-panel">
			<div class="bx-im-content-chat__service-health-panel_content">
				<div
					v-if="processedTitle"
					:title="processedTitle"
					class="bx-im-content-chat__service-health-panel_title --ellipsis"
				>
					{{ processedTitle }}
				</div>
				<div
					v-if="processedText"
					:title="processedText"
					class="bx-im-content-chat__service-health-panel_text --line-clamp-4"
				>
					{{ processedText }}
				</div>
			</div>
			<button
				class="bx-im-content-chat__service-health-panel_close-button"
				@click="hide"
			>
				<BIcon
					:name="OutlineIcons.CROSS_M"
					:color="Color.white"
					:size="ICON_SIZE"
					:hoverable="false"
				/>
			</button>
		</div>
	`
	};

	const AiAssistantBotContent = {
	  name: 'AiAssistantBotContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    ChatTextarea: im_v2_component_textarea.ChatTextarea,
	    ServiceHealthPanel
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withSidebar: {
	      type: Boolean,
	      default: true
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId" :withSidebar="withSidebar" :withDropArea="false">
			<template #header v-if="$slots['header']">
				<slot name="header"></slot>
			</template>
			<template #sub-header>
				<ServiceHealthPanel />
			</template>
			<template #textarea="{ onTextareaMount }">
				<ChatTextarea
					:dialogId="dialogId"
					:key="dialogId"
					:placeholder="loc('IM_CONTENT_AIASSISTANT_TEXTAREA_PLACEHOLDER_MSGVER_1')"
					:withMarket="false"
					:withEdit="false"
					:withUploadMenu="false"
					:withSmileSelector="false"
					@mounted="onTextareaMount"
				/>
			</template>
		</BaseChatContent>
	`
	};

	const TaskCommentsContent = {
	  name: 'TaskCommentsContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId" />
	`
	};

	// @vue/component
	const BaseEmptyState = {
	  props: {
	    text: {
	      type: String,
	      default: ''
	    },
	    subtext: {
	      type: String,
	      default: ''
	    },
	    backgroundId: {
	      type: [String, Number],
	      default: ''
	    }
	  },
	  computed: {
	    iconClass() {
	      return this.isEmptyRecent ? '--empty' : '--default';
	    },
	    preparedText() {
	      if (this.text) {
	        return this.text;
	      }
	      if (this.isEmptyRecent) {
	        return this.loc('IM_CONTENT_CHAT_NO_CHATS_START_MESSAGE');
	      }
	      return this.loc('IM_CONTENT_CHAT_START_MESSAGE_V2');
	    },
	    preparedSubtext() {
	      if (this.subtext) {
	        return this.subtext;
	      }
	      return '';
	    },
	    isEmptyRecent() {
	      return im_v2_provider_service_recent.LegacyRecentService.getInstance().getCollection().length === 0;
	    },
	    backgroundStyle() {
	      if (main_core.Type.isStringFilled(this.backgroundId) || main_core.Type.isNumber(this.backgroundId)) {
	        return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(this.backgroundId);
	      }
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat-start__container" :style="backgroundStyle">
			<div class="bx-im-content-chat-start__content">
				<div class="bx-im-content-chat-start__icon" :class="iconClass"></div>
				<div class="bx-im-content-chat-start__title">
					{{ preparedText }}
				</div>
				<div v-if="preparedSubtext" class="bx-im-content-chat-start__subtitle">
					{{ preparedSubtext }}
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const ChannelEmptyState = {
	  name: 'ChannelEmptyState',
	  components: {
	    BaseEmptyState
	  },
	  computed: {
	    text() {
	      return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_V3');
	    },
	    subtext() {
	      return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_SUBTITLE');
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<BaseEmptyState :text="text" :subtext="subtext" />
	`
	};

	const SHOWCASE_TOOLS_PHRASES = [main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CHATS'), main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CALLS'), main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CHANNELS'), main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_DOCS'), main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_TASKS'), main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_CALENDAR'), main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_FILES'), main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TOOL_BOARDS')];

	// @vue/component
	const EmbeddedChatPromoEmptyState = {
	  name: 'EmbeddedChatPromoEmptyState',
	  computed: {
	    SHOWCASE_TOOLS_PHRASES: () => SHOWCASE_TOOLS_PHRASES,
	    canInviteUsers() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.intranetInviteAvailable);
	    },
	    preparedTitle() {
	      return main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_TITLE', {
	        '[color]': '<span class="--brand-accent-with-icon">',
	        '[/color]': '</span>'
	      });
	    },
	    preparedInviteText() {
	      const openInviteSliderHandler = 'BX.Messenger.v2.Lib.InviteManager.openInviteSlider()';
	      return main_core.Loc.getMessage('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_INVITE_MSGVER_2', {
	        '[action]': `<span class="bx-im-embedded-promo-start__invite-action" onclick="${openInviteSliderHandler}">`,
	        '[/action]': '</span>'
	      });
	    }
	  },
	  mounted() {
	    void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.embeddedChatEmptyState);
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-embedded-promo-start__container">
			<div class="bx-im-embedded-promo-start__title" v-html="preparedTitle"></div>
			<div class="bx-im-embedded-promo-start__tools">
				<div
					v-for="tool in SHOWCASE_TOOLS_PHRASES"
					:key="tool"
					class="bx-im-embedded-promo-start__tools_item"
				>{{ tool }}</div>
			</div>
			<div class="bx-im-embedded-promo-start__content">
				<div class="bx-im-embedded-promo-start__features">
					<div class="bx-im-embedded-promo-start__features_content">
						<div class="bx-im-embedded-promo-start__feature_item">
							<div class="bx-im-embedded-promo-start__feature_icon --people"></div>
							<div class="bx-im-embedded-promo-start__feature_text">{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_EASE') }}</div>
						</div>
						<div class="bx-im-embedded-promo-start__feature_item">
							<div class="bx-im-embedded-promo-start__feature_icon --shield"></div>
							<div class="bx-im-embedded-promo-start__feature_text">{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_SAFETY') }}</div>
						</div>
						<div class="bx-im-embedded-promo-start__feature_item">
							<div class="bx-im-embedded-promo-start__feature_icon --ai"></div>
							<div class="bx-im-embedded-promo-start__feature_text">{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_AI') }}</div>
						</div>
					</div>
					<div v-if="canInviteUsers" v-html="preparedInviteText" class="bx-im-embedded-promo-start__invite_item"></div>
				</div>
				<div class="bx-im-embedded-promo-start__image"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const EmbeddedChatEmptyState = {
	  name: 'EmbeddedChatEmptyState',
	  computed: {
	    canInviteUsers() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.intranetInviteAvailable);
	    }
	  },
	  methods: {
	    onInviteActionClick() {
	      const analyticsContext = im_v2_lib_analytics.Analytics.getInstance().sliderInvite.getEmptyStateContext();
	      im_v2_lib_invite.InviteManager.openInviteSlider(analyticsContext);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-embedded-chat-start__container">
			<div class="bx-im-embedded-chat-start__image"></div>
			<div class="bx-im-embedded-chat-start__title">
				{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_FEATURE_SUBTITLE') }}
			</div>
			<template v-if="canInviteUsers">
				<div class="bx-im-embedded-chat-start__separator_text">
					{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_SEPARATOR_TEXT') }}
				</div>
				<div @click="onInviteActionClick" class="bx-im-embedded-chat-start__invite">
					{{ loc('IM_CONTENT_EMBEDDED_CHAT_START_INVITE_ACTION') }}
				</div>
			</template>
		</div>
	`
	};

	// @vue/component
	const FeatureBlock = {
	  name: 'FeatureBlock',
	  props: {
	    name: {
	      type: String,
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    subtitle: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="bx-im-content-collab-start__block">
			<div class="bx-im-content-collab-start__block_icon" :class="'--' + name"></div>
			<div class="bx-im-content-collab-start__block_content">
				<div class="bx-im-content-collab-start__block_title">
					{{ title }}
				</div>
				<div class="bx-im-content-collab-start__block_subtitle">
					{{ subtitle }}
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const CollabEmptyState = {
	  name: 'CollabEmptyState',
	  components: {
	    FeatureBlock,
	    ChatButton: im_v2_component_elements_button.ChatButton
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    canCreateCollab() {
	      const isAvailable = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.collabCreationAvailable);
	      const canCreate = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createCollab);
	      return isAvailable && canCreate;
	    },
	    createButtonColorScheme() {
	      return {
	        borderColor: im_v2_const.Color.transparent,
	        backgroundColor: im_v2_const.Color.white,
	        iconColor: im_v2_const.Color.gray90,
	        textColor: im_v2_const.Color.gray90,
	        hoverColor: im_v2_const.Color.white,
	        textHoverColor: im_v2_const.Color.collab70
	      };
	    },
	    isCurrentUserCollaber() {
	      const currentUser = this.$store.getters['users/get'](im_v2_application_core.Core.getUserId(), true);
	      return currentUser.type === im_v2_const.UserType.collaber;
	    },
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(im_v2_lib_theme.SpecialBackground.collab);
	    }
	  },
	  methods: {
	    onCreateClick() {
	      im_v2_lib_analytics.Analytics.getInstance().chatCreate.onCollabEmptyStateCreateClick();
	      im_public.Messenger.openChatCreation(im_v2_component_content_chatForms_forms.CreatableChat.collab);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-collab-start__container" :style="backgroundStyle">
			<div class="bx-im-content-collab-start__content">
				<div class="bx-im-content-collab-start__image"></div>
				<div class="bx-im-content-collab-start__title">
					{{ loc('IM_CONTENT_COLLAB_START_TITLE_V2') }}
				</div>
				<div v-if="isCurrentUserCollaber" class="bx-im-content-collab-start__blocks">
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_COLLABER_TITLE_1')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_1')"
						name="collaboration"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_COLLABER_TITLE_2')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_COLLABER_SUBTITLE_2')"
						name="business"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_3')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_3')"
						name="result"
					/>
				</div>
				<div v-else class="bx-im-content-collab-start__blocks">
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_1')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_1')"
						name="collaboration"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_2')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_2')"
						name="business"
					/>
					<FeatureBlock
						:title="loc('IM_CONTENT_COLLAB_START_BLOCK_TITLE_3')"
						:subtitle="loc('IM_CONTENT_COLLAB_START_BLOCK_SUBTITLE_3')"
						name="result"
					/>
				</div>
				<ChatButton
					v-if="canCreateCollab"
					:size="ButtonSize.XXL"
					:customColorScheme="createButtonColorScheme"
					:text="loc('IM_CONTENT_COLLAB_START_CREATE_BUTTON')"
					:isRounded="true"
					@click="onCreateClick"
				/>
			</div>
		</div>
	`
	};

	const BUTTON_BACKGROUND_COLOR = '#fff';
	const BUTTON_HOVER_COLOR = '#eee';
	const BUTTON_TEXT_COLOR = 'rgba(82, 92, 105, 0.9)';

	// @vue/component
	const CopilotEmptyState = {
	  name: 'CopilotEmptyState',
	  components: {
	    ChatButton: im_v2_component_elements_button.ChatButton,
	    CopilotRolesDialog: im_v2_component_elements_copilotRolesDialog.CopilotRolesDialog
	  },
	  data() {
	    return {
	      isCreatingChat: false,
	      showRolesDialog: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(im_v2_lib_theme.SpecialBackground.copilot);
	    },
	    preparedText() {
	      return this.loc('IM_CONTENT_COPILOT_EMPTY_STATE_MESSAGE_MSGVER_1', {
	        '#BR#': '\n'
	      });
	    },
	    buttonColorScheme() {
	      return {
	        borderColor: im_v2_const.Color.transparent,
	        backgroundColor: BUTTON_BACKGROUND_COLOR,
	        iconColor: BUTTON_TEXT_COLOR,
	        textColor: BUTTON_TEXT_COLOR,
	        hoverColor: BUTTON_HOVER_COLOR
	      };
	    }
	  },
	  methods: {
	    onCreateChatClick() {
	      this.showRolesDialog = true;
	    },
	    async createChat(role) {
	      const roleCode = role.code;
	      this.isCreatingChat = true;
	      this.showRolesDialog = false;
	      const newDialogId = await this.getCopilotService().createChat({
	        roleCode
	      }).catch(() => {
	        this.isCreatingChat = false;
	      });
	      this.isCreatingChat = false;
	      void im_public.Messenger.openCopilot(newDialogId);
	    },
	    getCopilotService() {
	      if (!this.copilotService) {
	        this.copilotService = new im_v2_provider_service_copilot.CopilotService();
	      }
	      return this.copilotService;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-content-copilot-empty-state__container" :style="backgroundStyle">
			<div class="bx-im-content-copilot-empty-state__content">
				<div class="bx-im-content-copilot-empty-state__icon"></div>
				<div class="bx-im-content-copilot-empty-state__text">{{ preparedText }}</div>
				<ChatButton
					class="--black-loader"
					:size="ButtonSize.XL"
					:customColorScheme="buttonColorScheme"
					:text="loc('IM_CONTENT_COPILOT_EMPTY_STATE_ASK_QUESTION')"
					:isRounded="true"
					:isLoading="isCreatingChat"
					@click="onCreateChatClick"
				/>
			</div>
			<CopilotRolesDialog 
				v-if="showRolesDialog"
				@selectRole="createChat"
				@close="showRolesDialog = false"
			/>
		</div>
	`
	};

	// @vue/component
	const TaskEmptyState = {
	  name: 'TaskEmptyState',
	  computed: {
	    preparedTitle() {
	      return main_core.Loc.getMessage('IM_CONTENT_TASK_START_TITLE', {
	        '[color]': '<span class="--brand-accent-with-icon">',
	        '[/color]': '</span>'
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-task-start__container">
			<div class="bx-im-task-start__title" v-html="preparedTitle"></div>
			<div class="bx-im-task-start__content">
				<div class="bx-im-task-start__features">
					<div class="bx-im-task-start__features_content">
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --ai"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_AI') }}</div>
						</div>
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --result"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_RESULT') }}</div>
						</div>
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --call"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_CALL') }}</div>
						</div>
						<div class="bx-im-task-start__feature_item">
							<div class="bx-im-task-start__feature_icon --changes"></div>
							<div class="bx-im-task-start__feature_text">{{ loc('IM_CONTENT_TASK_START_FEATURE_CHANGES') }}</div>
						</div>
					</div>
				</div>
				<div class="bx-im-task-start__image"></div>
			</div>
		</div>
	`
	};

	var _getUserActivityFromPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserActivityFromPull");
	var _requestUserData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestUserData");
	var _updateUserModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateUserModel");
	var _isPullServerWithUserStatusSupport = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPullServerWithUserStatusSupport");
	class UserService {
	  constructor() {
	    Object.defineProperty(this, _isPullServerWithUserStatusSupport, {
	      value: _isPullServerWithUserStatusSupport2
	    });
	    Object.defineProperty(this, _updateUserModel, {
	      value: _updateUserModel2
	    });
	    Object.defineProperty(this, _requestUserData, {
	      value: _requestUserData2
	    });
	    Object.defineProperty(this, _getUserActivityFromPull, {
	      value: _getUserActivityFromPull2
	    });
	  }
	  async updateLastActivityDate(userId) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isPullServerWithUserStatusSupport)[_isPullServerWithUserStatusSupport]()) {
	      const lastActivityDate = await babelHelpers.classPrivateFieldLooseBase(this, _getUserActivityFromPull)[_getUserActivityFromPull](userId);
	      if (!lastActivityDate) {
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _updateUserModel)[_updateUserModel](userId, {
	        lastActivityDate
	      });
	      return;
	    }
	    const userData = await babelHelpers.classPrivateFieldLooseBase(this, _requestUserData)[_requestUserData](userId);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateUserModel)[_updateUserModel](userId, userData);
	  }
	}
	async function _getUserActivityFromPull2(userId) {
	  const result = await im_v2_application_core.Core.getPullClient().getUsersLastSeen([userId]).catch(error => {
	    console.error('UserService: error getting user activity from P&P', error);
	    throw error;
	  });
	  if (!main_core.Type.isNumber(result[userId])) {
	    return null;
	  }
	  const activityDateAgo = result[userId] * 1000;
	  return new Date(Date.now() - activityDateAgo);
	}
	async function _requestUserData2(userId) {
	  im_v2_lib_logger.Logger.warn(`UserService: get actual user data for - ${userId}`);
	  const answer = await im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imUserGet, {
	    ID: userId
	  }).catch(result => {
	    console.error('UserService: error getting user data', result.error());
	  });
	  return answer.data();
	}
	async function _updateUserModel2(userId, userFields) {
	  im_v2_lib_logger.Logger.warn('UserService: update user data', userFields);
	  void im_v2_application_core.Core.getStore().dispatch('users/update', {
	    id: userId,
	    fields: userFields
	  });
	}
	function _isPullServerWithUserStatusSupport2() {
	  return im_v2_application_core.Core.getPullClient().isJsonRpc();
	}

	// @vue/component
	const ChatOpener = {
	  name: 'ChatOpener',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isChannel() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.dialogId);
	    },
	    isCollab() {
	      return this.dialog.type === im_v2_const.ChatType.collab;
	    },
	    isMultidialog() {
	      return this.$store.getters['sidebar/multidialog/isSupport'](this.dialogId);
	    },
	    isNotes() {
	      return this.$store.getters['chats/isNotes'](this.dialogId);
	    },
	    isCopilot() {
	      return this.dialog.type === im_v2_const.ChatType.copilot;
	    },
	    isAiAssistantBot() {
	      return this.$store.getters['users/bots/isAiAssistant'](this.dialogId);
	    },
	    isTaskComments() {
	      return this.dialog.type === im_v2_const.ChatType.taskComments;
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    contentComponentConfig() {
	      return [{
	        condition: this.isChannel,
	        component: ChannelContent
	      }, {
	        condition: this.isCollab,
	        component: CollabContent
	      }, {
	        condition: this.isMultidialog,
	        component: MultidialogContent
	      }, {
	        condition: this.isNotes,
	        component: NotesContent
	      }, {
	        condition: this.isCopilot,
	        component: CopilotContent
	      }, {
	        condition: this.isAiAssistantBot,
	        component: AiAssistantBotContent
	      }, {
	        condition: this.isTaskComments,
	        component: TaskCommentsContent
	      }];
	    },
	    contentComponent() {
	      const matchingItem = this.contentComponentConfig.find(item => {
	        return item.condition === true;
	      });
	      return matchingItem ? matchingItem.component : im_v2_component_content_elements.BaseChatContent;
	    },
	    emptyStateComponent() {
	      var _EmptyStateComponentB;
	      const EmptyStateComponentByLayout = {
	        [im_v2_const.Layout.channel]: ChannelEmptyState,
	        [im_v2_const.Layout.collab]: CollabEmptyState,
	        [im_v2_const.Layout.copilot]: CopilotEmptyState,
	        [im_v2_const.Layout.chat]: this.chatEmptyStateComponent,
	        [im_v2_const.Layout.taskComments]: TaskEmptyState,
	        default: BaseEmptyState
	      };
	      return (_EmptyStateComponentB = EmptyStateComponentByLayout[this.layout.name]) != null ? _EmptyStateComponentB : EmptyStateComponentByLayout.default;
	    },
	    chatEmptyStateComponent() {
	      const isEmbeddedMode = im_v2_lib_layout.LayoutManager.getInstance().isEmbeddedMode();
	      const needToShowPromoEmptyState = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.embeddedChatEmptyState);
	      if (!isEmbeddedMode) {
	        return BaseEmptyState;
	      }
	      return needToShowPromoEmptyState ? EmbeddedChatPromoEmptyState : EmbeddedChatEmptyState;
	    }
	  },
	  watch: {
	    dialogId(newValue, oldValue) {
	      im_v2_lib_logger.Logger.warn(`ChatContent: switching from ${oldValue || 'empty'} to ${newValue}`);
	      this.onChatChange();
	    }
	  },
	  created() {
	    if (!this.dialogId) {
	      return;
	    }
	    this.onChatChange();
	  },
	  methods: {
	    async onChatChange() {
	      if (this.dialogId === '') {
	        return;
	      }
	      if (im_v2_lib_utils.Utils.dialog.isExternalId(this.dialogId)) {
	        const realDialogId = await this.getChatService().prepareDialogId(this.dialogId);
	        void im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	          name: im_v2_const.Layout.chat,
	          entityId: realDialogId,
	          contextId: this.layout.contextId
	        });
	        return;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.dialogId} is already loaded`);
	        if (this.isUser) {
	          const userId = parseInt(this.dialog.dialogId, 10);
	          this.getUserService().updateLastActivityDate(userId);
	        } else if (this.isChannel && !this.isGuest) {
	          im_v2_lib_logger.Logger.warn(`ChatContent: channel ${this.dialogId} is loaded, loading comments metadata`);
	          void this.getChatService().loadCommentInfo(this.dialogId);
	        }
	        im_v2_lib_analytics.Analytics.getInstance().onOpenChat(this.dialog);
	        return;
	      }
	      if (this.dialog.loading) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.dialogId} is loading`);
	        return;
	      }
	      if (this.layout.contextId) {
	        await this.loadChatWithContext();
	        im_v2_lib_analytics.Analytics.getInstance().onOpenChat(this.dialog);
	        return;
	      }
	      await this.loadChat();
	      im_v2_lib_analytics.Analytics.getInstance().onOpenChat(this.dialog);
	    },
	    async loadChatWithContext() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.dialogId} with context - ${this.layout.contextId}`);
	      await this.getChatService().loadChatWithContext(this.dialogId, this.layout.contextId).catch(error => {
	        this.sendAnalytics(error);
	        im_public.Messenger.openChat();
	      });
	      im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.dialogId} is loaded with context of ${this.layout.contextId}`);
	    },
	    async loadChat() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.dialogId}`);
	      await this.getChatService().loadChatWithMessages(this.dialogId).catch(() => {
	        im_public.Messenger.openChat();
	      });
	      im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.dialogId} is loaded`);
	    },
	    sendAnalytics(error) {
	      if (error.code !== im_v2_const.ErrorCode.message.notFound) {
	        return;
	      }
	      im_v2_lib_analytics.Analytics.getInstance().messageDelete.onNotFoundNotification({
	        dialogId: this.dialogId
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatService;
	    },
	    getUserService() {
	      if (!this.userService) {
	        this.userService = new UserService();
	      }
	      return this.userService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-default-chat__container">
			<component :is="emptyStateComponent" v-if="!dialogId" />
			<component :is="contentComponent" v-else :dialogId="dialogId" />
		</div>
	`
	};

	// @vue/component
	const SubscribeToggle = {
	  name: 'SubscribeToggle',
	  components: {
	    Toggle: im_v2_component_elements_toggle.Toggle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    ToggleSize: () => im_v2_component_elements_toggle.ToggleSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    postMessageId() {
	      return this.$store.getters['messages/comments/getMessageIdByChatId'](this.dialog.chatId);
	    },
	    isSubscribed() {
	      return this.$store.getters['messages/comments/isUserSubscribed'](this.postMessageId);
	    }
	  },
	  methods: {
	    onToggleClick() {
	      if (this.isSubscribed) {
	        im_v2_provider_service_comments.CommentsService.unsubscribe(this.postMessageId);
	        return;
	      }
	      im_v2_provider_service_comments.CommentsService.subscribe(this.postMessageId);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="onToggleClick" class="bx-im-comments-header-follow__container">
			<div class="bx-im-comments-header-follow__text">{{ loc('IM_CONTENT_COMMENTS_FOLLOW_TOGGLE_TEXT') }}</div>
			<Toggle :size="ToggleSize.M" :isEnabled="isSubscribed" />
		</div>
	`
	};

	// @vue/component
	const CommentsHeader = {
	  name: 'CommentsHeader',
	  components: {
	    ChatHeader: im_v2_component_content_elements.ChatHeader,
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar,
	    SubscribeToggle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    channelId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    channel() {
	      return this.$store.getters['chats/get'](this.channelId, true);
	    },
	    showSubscribeToggle() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.subscribeToComments, this.dialogId);
	    }
	  },
	  methods: {
	    onBackClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeComments);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<ChatHeader
			:dialogId="dialogId"
			class="bx-im-comment-header__container"
		>
			<template #left>
				<div @click="onBackClick" class="bx-im-comment-header__back"></div>
				<div class="bx-im-comment-header__info">
					<div class="bx-im-comment-header__title">{{ loc('IM_CONTENT_COMMENTS_HEADER_TITLE') }}</div>
					<div class="bx-im-comment-header__subtitle">
						<div class="bx-im-comment-header__subtitle_avatar">
							<ChatAvatar :avatarDialogId="channelId" :contextDialogId="channelId" :size="AvatarSize.XS" />
						</div>
						<div class="bx-im-comment-header__subtitle_text">{{ channel.name }}</div>
					</div>
				</div>
			</template>
			<template v-if="showSubscribeToggle" #before-actions>
				<SubscribeToggle :dialogId="dialogId" />
			</template>
		</ChatHeader>
	`
	};

	// @vue/component
	const CommentsDialogLoader = {
	  name: 'CommentsDialogLoader',
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-comments-dialog-loader__container">
			<div class="bx-im-comments-dialog-loader__spinner"></div>
		</div>
	`
	};

	// @vue/component
	const CommentsMessageList = {
	  name: 'CommentsMessageList',
	  components: {
	    MessageList: im_v2_component_messageList.MessageList,
	    CommentsDialogLoader,
	    AuthorGroup: im_v2_component_messageList.AuthorGroup,
	    ...im_v2_component_messageList.MessageComponents
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    showPostMessage() {
	      return this.dialog.inited && !this.dialog.hasPrevPage;
	    },
	    postMessageId() {
	      return this.$store.getters['messages/comments/getMessageIdByChatId'](this.dialog.chatId);
	    },
	    postMessage() {
	      return this.$store.getters['messages/getById'](this.postMessageId);
	    },
	    postAuthorGroup() {
	      if (!this.dialog.inited) {
	        return null;
	      }
	      const collectionManager = new im_v2_component_messageList.CollectionManager(this.dialogId);
	      return collectionManager.formatAuthorGroup(this.postMessage);
	    }
	  },
	  methods: {
	    onPostMessageMouseUp(message, event) {
	      this.$refs.messageList.onMessageMouseUp(message, event);
	    },
	    getMessageComponentName(message) {
	      return new im_v2_lib_messageComponent.MessageComponentManager(message).getName();
	    }
	  },
	  template: `
		<MessageList
			:dialogId="dialogId"
			ref="messageList"
		>
			<template #loader>
				<CommentsDialogLoader />
			</template>
			<template v-if="showPostMessage" #before-messages>
				<div class="bx-im-comments-message-list__channel-post">
					<AuthorGroup :item="postAuthorGroup" :contextDialogId="dialogId" :withAvatarMenu="false">
						<template #message>
							<component
								:is="getMessageComponentName(postMessage)"
								:item="postMessage"
								:dialogId="dialogId"
								:key="postMessage.id"
								@mouseup="onPostMessageMouseUp(postMessage, $event)"
							>
							</component>
						</template>
					</AuthorGroup>
				</div>
			</template>
		</MessageList>
	`
	};

	// @vue/component
	const CommentsDialog = {
	  name: 'CommentsDialog',
	  components: {
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    CommentsMessageList,
	    PinnedMessages: im_v2_component_dialog_chat.PinnedMessages
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    postMessageId() {
	      return this.$store.getters['messages/comments/getMessageIdByChatId'](this.dialog.chatId);
	    },
	    postMessage() {
	      return this.$store.getters['messages/getById'](this.postMessageId);
	    }
	  },
	  methods: {
	    async goToPostMessageContext() {
	      const dialog = this.$refs.dialog;
	      const postMessageIsShown = this.dialogInited && !this.dialog.hasPrevPage;
	      if (postMessageIsShown) {
	        await dialog.getScrollManager().animatedScrollToMessage(this.postMessageId);
	        dialog.highlightMessage(this.postMessageId);
	        return;
	      }
	      dialog.showLoadingBar();
	      await dialog.getMessageService().loadFirstPage();
	      await this.$nextTick();
	      dialog.hideLoadingBar();
	      dialog.getScrollManager().scrollToMessage(this.postMessageId);
	      await this.$nextTick();
	      dialog.highlightMessage(this.postMessageId);
	    },
	    onPinnedPostMessageClick() {
	      this.goToPostMessageContext();
	    }
	  },
	  template: `
		<ChatDialog ref="dialog" :dialogId="dialogId" :saveScrollOnExit="false" :clearOnExit="true">
			<template v-if="dialogInited" #pinned-panel>
				<PinnedMessages
					:dialogId="dialogId"
					:messages="[postMessage]"
					@messageClick="onPinnedPostMessageClick"
				/>
			</template>
			<template #message-list>
				<CommentsMessageList :dialogId="dialogId" />
			</template>
		</ChatDialog>
	`
	};

	// @vue/component
	const CommentsTextarea = {
	  name: 'CommentsTextarea',
	  components: {
	    ChatTextarea: im_v2_component_textarea.ChatTextarea
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  template: `
		<ChatTextarea
			:dialogId="dialogId"
			:withMarket="false"
			class="bx-im-comments-send-panel__container"
		/>
	`
	};

	// @vue/component
	const JoinPanel$1 = {
	  components: {
	    ChatButton: im_v2_component_elements_button.ChatButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    ButtonColor: () => im_v2_component_elements_button.ButtonColor
	  },
	  methods: {
	    onButtonClick() {
	      this.getChatService().joinChat(this.dialogId);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__textarea_placeholder">
			<ChatButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Primary"
				:text="loc('IM_CONTENT_BLOCKED_TEXTAREA_JOIN_CHANNEL_V2')"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`
	};

	const CommentsContent = {
	  name: 'CommentsContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    CommentsHeader,
	    CommentsDialog,
	    CommentsTextarea,
	    JoinPanel: JoinPanel$1
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    channelId: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId">
			<template #header>
				<CommentsHeader :dialogId="dialogId" :channelId="channelId" :key="dialogId" />
			</template>
			<template #dialog>
				<CommentsDialog :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #join-panel>
				<JoinPanel :dialogId="dialogId" />
			</template>
			<template #textarea="{ onTextareaMount }">
				<CommentsTextarea :dialogId="dialogId" :key="dialogId" @mounted="onTextareaMount" />
			</template>
		</BaseChatContent>
	`
	};

	// @vue/component
	const CommentsOpener = {
	  name: 'CommentsOpener',
	  components: {
	    CommentsContent
	  },
	  props: {
	    postId: {
	      type: Number,
	      required: true
	    },
	    channelId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {};
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/getByChatId'](this.commentsChatId);
	    },
	    commentInfo() {
	      return this.$store.getters['messages/comments/getByMessageId'](this.postId);
	    },
	    commentsChatId() {
	      return this.commentInfo.chatId;
	    },
	    commentsDialogId() {
	      if (!this.dialog) {
	        return '';
	      }
	      return this.dialog.dialogId;
	    }
	  },
	  created() {
	    this.onCreated();
	  },
	  methods: {
	    async onCreated() {
	      await this.loadChat();
	      im_v2_lib_analytics.Analytics.getInstance().onOpenChat(this.dialog);
	    },
	    async loadChat() {
	      im_v2_lib_logger.Logger.warn(`CommentsContent: loading comments for post ${this.postId}`);
	      await this.getChatService().loadComments(this.postId).catch(() => {
	        this.$emit('close');
	      });
	      im_v2_lib_logger.Logger.warn(`CommentsContent: comments for post ${this.postId} are loaded`);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatService;
	    }
	  },
	  template: `
		<div class="bx-im-content-comments__container">
			<CommentsContent :dialogId="commentsDialogId" :channelId="channelId" />
		</div>
	`
	};

	// @vue/component
	const ChatContent = {
	  name: 'ChatContent',
	  components: {
	    ChatOpener,
	    CommentsOpener
	  },
	  props: {
	    entityId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      commentsPostId: 0,
	      commentsAnimationFlag: false
	    };
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    showComments() {
	      return this.$store.getters['messages/comments/areOpened'];
	    }
	  },
	  watch: {
	    layout() {
	      this.closeComments();
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.openComments, this.onOpenComments);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.closeComments, this.onCloseComments);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.openComments, this.onOpenComments);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.closeComments, this.onCloseComments);
	  },
	  methods: {
	    onOpenComments(event) {
	      const {
	        messageId
	      } = event.getData();
	      this.commentsPostId = messageId;
	      this.commentsAnimationFlag = true;
	      this.$store.dispatch('messages/comments/setOpened', {
	        channelDialogId: this.entityId,
	        commentsPostId: this.commentsPostId
	      });
	    },
	    onCloseComments() {
	      this.closeComments();
	    },
	    closeComments() {
	      this.commentsPostId = 0;
	      this.$store.dispatch('messages/comments/setClosed');
	    },
	    onCommentsAnimationEnd() {
	      this.commentsAnimationFlag = false;
	    }
	  },
	  template: `
		<ChatOpener :dialogId="entityId" :class="{'--comments-show-animation': commentsAnimationFlag}" />
		<Transition name="comments-content" @after-enter="onCommentsAnimationEnd">
			<CommentsOpener
				v-if="showComments"
				:postId="commentsPostId"
				:channelId="entityId"
			/>
		</Transition>
	`
	};

	exports.ChatContent = ChatContent;
	exports.AiAssistantBotContent = AiAssistantBotContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Animation,BX.UI.Dialogs,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.EntitySelector,BX.Main,BX.Messenger.v2.Component.Elements,BX.UI.IconSet,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Model,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Component,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service));
//# sourceMappingURL=chat-content.bundle.js.map
