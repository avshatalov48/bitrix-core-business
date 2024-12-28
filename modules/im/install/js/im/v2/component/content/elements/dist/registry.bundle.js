/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_notification,call_component_callButton,im_v2_component_animation,im_v2_lib_utils,im_v2_lib_analytics,ui_vue3,im_v2_component_dialog_chat,im_v2_component_textarea,im_v2_lib_theme,im_v2_lib_permission,im_v2_lib_textarea,im_v2_component_sidebar,im_v2_lib_bulkActions,ui_uploader_core,main_core,im_v2_application_core,im_v2_provider_service,im_v2_const,main_core_events,ui_vue3_directives_hint,im_v2_component_elements,im_v2_component_entitySelector) {
	'use strict';

	// @vue/component
	const CallHeaderButton = {
	  name: 'CallHeaderButton',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    compactMode: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    componentToRender() {
	      return call_component_callButton.CallButton;
	    }
	  },
	  template: `
		<component v-if="componentToRender" :is="componentToRender" :dialog="dialog" :compactMode="compactMode" />
	`
	};

	const ParamsByLinkType = {
	  [im_v2_const.ChatEntityLinkType.tasks]: {
	    className: '--task',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_TASK')
	  },
	  [im_v2_const.ChatEntityLinkType.calendar]: {
	    className: '--calendar',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MEETING_MSGVER_1')
	  },
	  [im_v2_const.ChatEntityLinkType.sonetGroup]: {
	    className: '--group',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_GROUP_MSGVER_1')
	  },
	  [im_v2_const.ChatEntityLinkType.mail]: {
	    className: '--mail',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_MAIL_MSGVER_1')
	  },
	  [im_v2_const.ChatEntityLinkType.contact]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_CONTACT')
	  },
	  [im_v2_const.ChatEntityLinkType.deal]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DEAL')
	  },
	  [im_v2_const.ChatEntityLinkType.lead]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_LEAD')
	  },
	  [im_v2_const.ChatEntityLinkType.dynamic]: {
	    className: '--crm',
	    loc: main_core.Loc.getMessage('IM_CONTENT_CHAT_HEADER_OPEN_DYNAMIC_ELEMENT')
	  }
	};

	// @vue/component
	const EntityLink = {
	  name: 'EntityLink',
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
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    entityType() {
	      return this.dialog.entityLink.type;
	    },
	    entityUrl() {
	      return this.dialog.entityLink.url;
	    },
	    containerClassName() {
	      var _ParamsByLinkType$thi, _ParamsByLinkType$thi2;
	      return (_ParamsByLinkType$thi = (_ParamsByLinkType$thi2 = ParamsByLinkType[this.entityType]) == null ? void 0 : _ParamsByLinkType$thi2.className) != null ? _ParamsByLinkType$thi : '';
	    },
	    linkText() {
	      var _ParamsByLinkType$thi3, _ParamsByLinkType$thi4;
	      return (_ParamsByLinkType$thi3 = (_ParamsByLinkType$thi4 = ParamsByLinkType[this.entityType]) == null ? void 0 : _ParamsByLinkType$thi4.loc) != null ? _ParamsByLinkType$thi3 : 'Open entity';
	    }
	  },
	  template: `
		<a :href="entityUrl" class="bx-im-chat-header-entity-link__container" :class="containerClassName" target="_blank">
			<div class="bx-im-chat-header-entity-link__icon"></div>
			<div class="bx-im-chat-header-entity-link__text --ellipsis">{{ linkText }}</div>
			<div class="bx-im-chat-header-entity-link__arrow"></div>
		</a>
	`
	};

	const UserCounterPhraseCodeByChatType = {
	  [im_v2_const.ChatType.openChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	  [im_v2_const.ChatType.channel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	  [im_v2_const.ChatType.generalChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	  default: 'IM_CONTENT_CHAT_HEADER_USER_COUNT'
	};

	// @vue/component
	const GroupChatTitle = {
	  name: 'GroupChatTitle',
	  components: {
	    EditableChatTitle: im_v2_component_elements.EditableChatTitle,
	    EntityLink,
	    LineLoader: im_v2_component_elements.LineLoader,
	    FadeAnimation: im_v2_component_animation.FadeAnimation
	  },
	  inject: ['withSidebar'],
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['membersClick', 'newTitle'],
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    hasEntityLink() {
	      var _this$dialog$entityLi;
	      return Boolean((_this$dialog$entityLi = this.dialog.entityLink) == null ? void 0 : _this$dialog$entityLi.url);
	    },
	    userCounterPhraseCode() {
	      var _UserCounterPhraseCod;
	      return (_UserCounterPhraseCod = UserCounterPhraseCodeByChatType[this.dialog.type]) != null ? _UserCounterPhraseCod : UserCounterPhraseCodeByChatType.default;
	    },
	    userCounterText() {
	      return main_core.Loc.getMessagePlural(this.userCounterPhraseCode, this.dialog.userCounter, {
	        '#COUNT#': this.dialog.userCounter
	      });
	    },
	    needShowSubtitleCursor() {
	      return this.withSidebar;
	    },
	    sidebarTooltipText() {
	      return this.withSidebar ? this.loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS') : '';
	    }
	  },
	  methods: {
	    onMembersClick() {
	      if (!this.withSidebar) {
	        return;
	      }
	      this.$emit('membersClick');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__info">
			<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="$emit('newTitle', $event)" />
			<LineLoader v-if="!dialog.inited" :width="50" :height="16" />
			<FadeAnimation :duration="100">
				<div v-if="dialog.inited" class="bx-im-chat-header__subtitle_container">
					<div
						:title="sidebarTooltipText"
						@click="onMembersClick"
						class="bx-im-chat-header__subtitle_content"
						:class="{'--click': needShowSubtitleCursor}"
					>
						{{ userCounterText }}
					</div>
					<EntityLink v-if="hasEntityLink" :dialogId="dialogId" />
				</div>
			</FadeAnimation>
		</div>
	`
	};

	const ONE_MINUTE = 60 * 1000;

	// @vue/component
	const UserTitle = {
	  name: 'UserTitle',
	  components: {
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      userLastOnlineText: ''
	    };
	  },
	  computed: {
	    userPosition() {
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    userLastOnline() {
	      return this.$store.getters['users/getLastOnline'](this.dialogId);
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    }
	  },
	  watch: {
	    userLastOnline(value) {
	      this.userLastOnlineText = value;
	    }
	  },
	  created() {
	    this.updateUserOnline();
	    this.userLastOnlineInterval = setInterval(this.updateUserOnline, ONE_MINUTE);
	  },
	  beforeUnmount() {
	    clearInterval(this.userLastOnlineInterval);
	  },
	  methods: {
	    updateUserOnline() {
	      this.userLastOnlineText = this.$store.getters['users/getLastOnline'](this.dialogId);
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__info">
			<div class="bx-im-chat-header__title --user">
				<a :href="userLink" target="_blank" class="bx-im-chat-header__title_container">
					<ChatTitle :dialogId="dialogId" />
				</a>
				<span class="bx-im-chat-header__user-status">{{ userLastOnlineText }}</span>
			</div>
			<div class="bx-im-chat-header__subtitle_container">
				<div class="bx-im-chat-header__subtitle_content">{{ userPosition }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const HeaderAvatar = {
	  name: 'HeaderAvatar',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['avatarClick'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    canChangeAvatar() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.avatar, this.dialogId);
	    },
	    userLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    }
	  },
	  methods: {
	    onAvatarClick() {
	      if (this.isUser || !this.canChangeAvatar) {
	        return;
	      }
	      this.$refs.avatarInput.click();
	    },
	    async onAvatarSelect(event) {
	      const input = event.target;
	      const file = input.files[0];
	      if (!file) {
	        return;
	      }
	      const preparedAvatar = await this.getChatService().prepareAvatar(file);
	      if (!preparedAvatar) {
	        return;
	      }
	      void this.getChatService().changeAvatar(this.dialog.chatId, preparedAvatar);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    }
	  },
	  // language=Vue
	  template: `
		<div class="bx-im-chat-header__avatar" :class="{'--can-change': canChangeAvatar}" @click="onAvatarClick">
			<a v-if="isUser" :href="userLink" target="_blank">
				<ChatAvatar :avatarDialogId="dialogId" :contextDialogId="dialogId" :size="AvatarSize.L" />
			</a>
			<ChatAvatar v-else :avatarDialogId="dialogId" :contextDialogId="dialogId" :size="AvatarSize.L" />
		</div>
		<input
			type="file"
			accept="image/*"
			class="bx-im-chat-header__avatar_input"
			ref="avatarInput"
			@change="onAvatarSelect"
		>
	`
	};

	// @vue/component
	const AddToChatButton = {
	  name: 'AddToChatButton',
	  components: {
	    AddToChat: im_v2_component_entitySelector.AddToChat
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
	      showAddToChatPopup: false
	    };
	  },
	  methods: {
	    openAddToChatPopup() {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatHeaderClick(this.dialogId);
	      this.showAddToChatPopup = true;
	    },
	    closeAddToChatPopup() {
	      this.showAddToChatPopup = false;
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
		></div>
		<AddToChat
			v-if="showAddToChatPopup"
			:bindElement="$refs['add-members'] ?? {}"
			:dialogId="dialogId"
			:popupConfig="{ offsetTop: 15, offsetLeft: -300 }"
			@close="closeAddToChatPopup"
		/>
	`
	};

	// @vue/component
	const SearchButton = {
	  name: 'SearchButton',
	  inject: ['currentSidebarPanel'],
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    isMessageSearchActive() {
	      return this.currentSidebarPanel === im_v2_const.SidebarDetailBlock.messageSearch;
	    }
	  },
	  methods: {
	    toggleSearchPanel() {
	      if (this.isMessageSearchActive) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: im_v2_const.SidebarDetailBlock.messageSearch
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.messageSearch,
	        dialogId: this.dialogId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SEARCH')"
			:class="{'--active': isMessageSearchActive}"
			class="bx-im-chat-header__icon --search"
			@click="toggleSearchPanel"
		></div>
	`
	};

	// @vue/component
	const SidebarButton = {
	  name: 'SidebarButton',
	  inject: ['currentSidebarPanel'],
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    isSidebarOpened() {
	      return main_core.Type.isStringFilled(this.currentSidebarPanel);
	    }
	  },
	  methods: {
	    toggleRightPanel() {
	      if (this.isSidebarOpened) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.close, {
	          panel: ''
	        });
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	        panel: im_v2_const.SidebarDetailBlock.main,
	        dialogId: this.dialogId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-chat-header__icon --panel"
			:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_SIDEBAR')"
			:class="{'--active': isSidebarOpened}"
			@click="toggleRightPanel"
		></div>
	`
	};

	const HEADER_WIDTH_BREAKPOINT = 700;

	// @vue/component
	const ChatHeader = {
	  name: 'ChatHeader',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    CallHeaderButton,
	    GroupChatTitle,
	    UserChatTitle: UserTitle,
	    LineLoader: im_v2_component_elements.LineLoader,
	    FadeAnimation: im_v2_component_animation.FadeAnimation,
	    HeaderAvatar,
	    AddToChatButton,
	    SearchButton,
	    SidebarButton
	  },
	  inject: ['currentSidebarPanel', 'withSidebar'],
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withCallButton: {
	      type: Boolean,
	      default: true
	    },
	    withSearchButton: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['buttonPanelReady', 'compactModeChange'],
	  data() {
	    return {
	      compactMode: false
	    };
	  },
	  computed: {
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isBot() {
	      if (!this.isUser) {
	        return false;
	      }
	      return this.user.type === im_v2_const.UserType.bot;
	    },
	    showCallButton() {
	      if (this.isBot || !this.withCallButton) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.call, this.dialogId);
	    },
	    showAddToChatButton() {
	      if (this.isBot) {
	        return false;
	      }
	      const hasCreateChatAccess = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createChat);
	      if (this.isUser && !hasCreateChatAccess) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.extend, this.dialogId);
	    },
	    showSearchButton() {
	      return this.withSearchButton;
	    },
	    showSidebarButton() {
	      if (!this.withSidebar) {
	        return false;
	      }
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.openSidebar, this.dialogId);
	    },
	    isMembersPanelActive() {
	      return this.currentSidebarPanel === im_v2_const.SidebarDetailBlock.members;
	    },
	    chatTitleComponent() {
	      return this.isUser ? UserTitle : GroupChatTitle;
	    },
	    containerClasses() {
	      return {
	        '--compact': this.compactMode
	      };
	    }
	  },
	  mounted() {
	    this.initResizeObserver();
	  },
	  beforeUnmount() {
	    this.getResizeObserver().disconnect();
	  },
	  methods: {
	    initResizeObserver() {
	      this.resizeObserver = new ResizeObserver(([entry]) => {
	        this.onContainerResize(entry.contentRect.width);
	      });
	      this.resizeObserver.observe(this.$refs.container);
	    },
	    onContainerResize(newContainerWidth) {
	      const newCompactMode = newContainerWidth <= HEADER_WIDTH_BREAKPOINT;
	      if (newCompactMode !== this.compactMode) {
	        this.$emit('compactModeChange', newCompactMode);
	        this.compactMode = newCompactMode;
	      }
	    },
	    onMembersClick() {
	      if (!this.isInited) {
	        return;
	      }
	      if (this.isMembersPanelActive) {
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
	    onNewTitleSubmit(newTitle) {
	      this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CONTENT_CHAT_HEADER_RENAME_ERROR')
	        });
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    getResizeObserver() {
	      return this.resizeObserver;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-chat-header__scope bx-im-chat-header__container" :class="containerClasses" ref="container">
			<div class="bx-im-chat-header__left">
				<slot name="left">
					<HeaderAvatar :dialogId="dialogId" />
					<slot name="title" :onNewTitleHandler="onNewTitleSubmit">
						<component
							:is="chatTitleComponent"
							:dialogId="dialogId"
							@membersClick="onMembersClick"
							@newTitle="onNewTitleSubmit"
						/>
					</slot>
				</slot>
			</div>
			<LineLoader v-if="!isInited" :width="45" :height="22" />
			<FadeAnimation @afterEnter="$emit('buttonPanelReady')" :duration="100">
				<div v-if="isInited" class="bx-im-chat-header__right">
					<slot name="before-actions"></slot>
					<CallHeaderButton v-if="showCallButton" :dialogId="dialogId" :compactMode="compactMode" />
					<slot v-if="showAddToChatButton" name="add-to-chat-button">
						<AddToChatButton :dialogId="dialogId" />
					</slot>
					<SearchButton v-if="showSearchButton" :dialogId="dialogId" />
					<SidebarButton v-if="showSidebarButton" :dialogId="dialogId" />
				</div>
			</FadeAnimation>
		</div>
	`
	};

	const Height = {
	  chatHeader: 64,
	  pinnedMessages: 53,
	  blockedTextarea: 64,
	  dropAreaOffset: 16
	};

	// @vue/component
	const DropArea = {
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    container: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {
	      showDropArea: false,
	      lastDropAreaEnterTarget: null
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    hasPinnedMessages() {
	      return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId).length > 0;
	    },
	    dropAreaStyles() {
	      let offset = Height.dropAreaOffset + Height.chatHeader;
	      if (this.hasPinnedMessages) {
	        offset += Height.pinnedMessages;
	      }
	      return {
	        top: `${offset}px`
	      };
	    }
	  },
	  watch: {
	    container: {
	      immediate: true,
	      handler(newValue) {
	        if (!main_core.Type.isElementNode(newValue)) {
	          return;
	        }
	        this.bindEvents();
	      }
	    }
	  },
	  beforeUnmount() {
	    this.unbindEvents();
	  },
	  methods: {
	    bindEvents() {
	      main_core.Event.bind(this.container, 'dragenter', this.onDragEnter);
	      main_core.Event.bind(this.container, 'dragleave', this.onDragLeave);
	      main_core.Event.bind(this.container, 'dragover', this.onDragOver);
	      main_core.Event.bind(this.container, 'drop', this.onDrop);
	    },
	    unbindEvents() {
	      main_core.Event.unbind(this.container, 'dragenter', this.onDragEnter);
	      main_core.Event.unbind(this.container, 'dragleave', this.onDragLeave);
	      main_core.Event.unbind(this.container, 'dragover', this.onDragOver);
	      main_core.Event.unbind(this.container, 'drop', this.onDrop);
	    },
	    async onDragEnter(event) {
	      event.stopPropagation();
	      event.preventDefault();
	      const success = await ui_uploader_core.hasDataTransferOnlyFiles(event.dataTransfer, false);
	      if (!success) {
	        return;
	      }
	      this.lastDropAreaEnterTarget = event.target;
	      this.showDropArea = true;
	    },
	    onDragLeave(event) {
	      event.stopPropagation();
	      event.preventDefault();
	      if (this.lastDropAreaEnterTarget !== event.target) {
	        return;
	      }
	      this.showDropArea = false;
	    },
	    onDragOver(event) {
	      event.preventDefault();
	    },
	    async onDrop(event) {
	      event.preventDefault();
	      const uploaderId = await this.getUploadingService().uploadFromDragAndDrop({
	        event,
	        dialogId: this.dialogId,
	        sendAsFile: false
	      });
	      if (main_core.Type.isStringFilled(uploaderId)) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.openUploadPreview, {
	          uploaderId
	        });
	      }
	      this.showDropArea = false;
	    },
	    getUploadingService() {
	      if (!this.uploadingService) {
	        this.uploadingService = im_v2_provider_service.UploadingService.getInstance();
	      }
	      return this.uploadingService;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<Transition name="drop-area-fade">
			<div v-if="showDropArea" :style="dropAreaStyles" class="bx-im-content-chat-drop-area__container bx-im-content-chat-drop-area__scope">
				<div class="bx-im-content-chat-drop-area__box">
					<span class="bx-im-content-chat-drop-area__icon"></span>
					<label class="bx-im-content-chat-drop-area__label-text">
						{{ loc('IM_CONTENT_DROP_AREA') }}
					</label>
				</div>
			</div>
		</Transition>
	`
	};

	const BUTTON_BACKGROUND_COLOR = 'rgba(0, 0, 0, 0.1)';
	const BUTTON_HOVER_COLOR = 'rgba(0, 0, 0, 0.2)';
	const BUTTON_TEXT_COLOR = '#fff';

	// @vue/component
	const MutePanel = {
	  components: {
	    ChatButton: im_v2_component_elements.Button
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
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isMuted() {
	      return this.dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	    },
	    buttonText() {
	      const mutedCode = this.loc('IM_CONTENT_BLOCKED_TEXTAREA_ENABLE_NOTIFICATIONS');
	      const unmutedCode = this.loc('IM_CONTENT_BLOCKED_TEXTAREA_DISABLE_NOTIFICATIONS');
	      return this.isMuted ? mutedCode : unmutedCode;
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
	    onButtonClick() {
	      if (this.isMuted) {
	        this.getChatService().unmuteChat(this.dialogId);
	        return;
	      }
	      this.getChatService().muteChat(this.dialogId);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
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
				:customColorScheme="buttonColorScheme"
				:text="buttonText"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`
	};

	// @vue/component
	const JoinPanel = {
	  components: {
	    ChatButton: im_v2_component_elements.Button
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
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonColor: () => im_v2_component_elements.ButtonColor
	  },
	  methods: {
	    onButtonClick() {
	      this.getChatService().joinChat(this.dialogId);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
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
				:text="loc('IM_CONTENT_BLOCKED_TEXTAREA_JOIN_CHAT')"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`
	};

	const BUTTON_COLOR_DELETE = '#f4433e';
	const BUTTON_COLOR_FORWARD = '#ffffff';
	const BUTTON_BACKGROUND_COLOR_FORWARD = '#00ace3';
	const BUTTON_BACKGROUND_COLOR_FORWARD_HOVER = '#3eddff';

	// @vue/component
	const BulkActionsPanel = {
	  name: 'BulkActionsPanel',
	  components: {
	    ChatButton: im_v2_component_elements.Button,
	    ForwardPopup: im_v2_component_entitySelector.ForwardPopup
	  },
	  directives: {
	    hint: ui_vue3_directives_hint.hint
	  },
	  data() {
	    return {
	      isShowForwardPopup: false,
	      messagesIds: []
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    ButtonIcon: () => im_v2_component_elements.ButtonIcon,
	    deleteButtonColorScheme() {
	      return {
	        backgroundColor: 'transparent',
	        borderColor: 'transparent',
	        iconColor: BUTTON_COLOR_DELETE,
	        textColor: BUTTON_COLOR_DELETE,
	        hoverColor: 'transparent'
	      };
	    },
	    forwardButtonColorScheme() {
	      return {
	        backgroundColor: BUTTON_BACKGROUND_COLOR_FORWARD,
	        borderColor: 'transparent',
	        iconColor: BUTTON_COLOR_FORWARD,
	        textColor: BUTTON_COLOR_FORWARD,
	        hoverColor: BUTTON_BACKGROUND_COLOR_FORWARD_HOVER
	      };
	    },
	    selectedMessages() {
	      return this.$store.getters['messages/select/getCollection'];
	    },
	    selectedMessagesSize() {
	      return this.selectedMessages.size;
	    },
	    formattedMessagesCounter() {
	      if (!this.selectedMessagesSize) {
	        return '';
	      }
	      return `(${this.selectedMessagesSize})`;
	    },
	    messageCounterText() {
	      if (!this.selectedMessagesSize) {
	        return this.loc('IM_CONTENT_BULK_ACTIONS_SELECT_MESSAGES');
	      }
	      return this.loc('IM_CONTENT_BULK_ACTIONS_COUNT_MESSAGES');
	    },
	    tooltipSettings() {
	      return {
	        text: this.loc('IM_CONTENT_BULK_ACTIONS_PANEL_DELETE_COMING_SOON'),
	        popupOptions: {
	          angle: true,
	          targetContainer: document.body,
	          offsetTop: -13,
	          offsetLeft: 65,
	          bindOptions: {
	            position: 'top'
	          }
	        }
	      };
	    }
	  },
	  methods: {
	    onShowForwardPopup() {
	      this.messagesIds = [...this.selectedMessages];
	      this.isShowForwardPopup = true;
	    },
	    onCloseForwardPopup() {
	      this.messagesIds = [];
	      this.isShowForwardPopup = false;
	    },
	    closeBulkActionsMode() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeBulkActionsMode);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-bulk-actions-panel">
			<div class="bx-im-content-bulk-actions-panel__container">
				<div class="bx-im-content-bulk-actions-panel__left-section">
					<div @click="closeBulkActionsMode" class="bx-im-content-bulk-actions-panel__cancel"></div>
					<div class="bx-im-content-bulk-actions-panel__counter-container">
						<span class="bx-im-content-bulk-actions-panel__counter-name">{{ messageCounterText }}</span>
						<span class="bx-im-content-bulk-actions-panel__counter-number">{{ formattedMessagesCounter }}</span>
					</div>
				</div>
				<div class="bx-im-content-bulk-actions-panel__right-section">
					<ChatButton
						v-hint="tooltipSettings"
						:size="ButtonSize.L"
						:icon="ButtonIcon.Delete"
						:customColorScheme="deleteButtonColorScheme"
						:isDisabled="true"
						:isRounded="true"
						:isUppercase="false"
						:text="loc('IM_CONTENT_BULK_ACTIONS_PANEL_DELETE')"
					/>
					<ChatButton
						:size="ButtonSize.L"
						:icon="ButtonIcon.Forward"
						:customColorScheme="forwardButtonColorScheme"
						:isRounded="true"
						:isUppercase="false"
						:isDisabled="!selectedMessagesSize"
						:text="loc('IM_CONTENT_BULK_ACTIONS_PANEL_FORWARD')"
						@click="onShowForwardPopup"
					/>
				</div>
				<ForwardPopup
					v-if="isShowForwardPopup"
					:messagesIds="messagesIds"
					@close="onCloseForwardPopup"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const LoadingBar = {
	  name: 'LoadingBar',
	  data() {
	    return {};
	  },
	  template: `
		<div class="bx-im-content-chat__loading-bar"></div>
	`
	};

	const TextareaObserverDirective = {
	  mounted(element, binding) {
	    binding.instance.textareaResizeManager.observeTextarea(element);
	  },
	  beforeUnmount(element, binding) {
	    binding.instance.textareaResizeManager.unobserveTextarea(element);
	  }
	};

	// @vue/component
	const BaseChatContent = {
	  name: 'BaseChatContent',
	  components: {
	    ChatHeader,
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChatTextarea: im_v2_component_textarea.ChatTextarea,
	    ChatSidebar: im_v2_component_sidebar.ChatSidebar,
	    DropArea,
	    MutePanel,
	    JoinPanel,
	    BulkActionsPanel,
	    LoadingBar
	  },
	  directives: {
	    'textarea-observer': TextareaObserverDirective
	  },
	  provide() {
	    return {
	      currentSidebarPanel: ui_vue3.computed(() => this.currentSidebarPanel),
	      withSidebar: ui_vue3.computed(() => this.withSidebar)
	    };
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    backgroundId: {
	      type: [Number, String, null],
	      default: null
	    },
	    withSidebar: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      textareaHeight: 0,
	      showLoadingBar: false,
	      currentSidebarPanel: ''
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    canSend() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.send, this.dialog.dialogId);
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    isBulkActionsMode() {
	      return this.$store.getters['messages/select/getBulkActionsMode'];
	    },
	    hasCommentsOnTop() {
	      return this.$store.getters['messages/comments/areOpenedForChannel'](this.dialogId);
	    },
	    containerClasses() {
	      const alignment = this.$store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	      return [`--${alignment}-align`];
	    },
	    backgroundStyle() {
	      if (this.backgroundId) {
	        return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(this.backgroundId);
	      }
	      return im_v2_lib_theme.ThemeManager.getCurrentBackgroundStyle();
	    },
	    dialogContainerStyle() {
	      let textareaHeight = this.textareaHeight;
	      if (!this.canSend || this.isBulkActionsMode) {
	        textareaHeight = Height.blockedTextarea;
	      }
	      return {
	        height: `calc(100% - ${Height.chatHeader}px - ${textareaHeight}px)`
	      };
	    }
	  },
	  watch: {
	    textareaHeight(newValue, oldValue) {
	      if (!this.dialog.inited || oldValue === 0) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	        chatId: this.dialog.chatId,
	        animation: false
	      });
	    }
	  },
	  created() {
	    this.initTextareaResizeManager();
	    this.bindEvents();
	    im_v2_lib_bulkActions.BulkActionsManager.getInstance().init();
	  },
	  beforeUnmount() {
	    this.unbindEvents();
	    im_v2_lib_bulkActions.BulkActionsManager.getInstance().destroy();
	  },
	  methods: {
	    initTextareaResizeManager() {
	      this.textareaResizeManager = new im_v2_lib_textarea.ResizeManager();
	      this.textareaResizeManager.subscribe(im_v2_lib_textarea.ResizeManager.events.onHeightChange, this.onTextareaHeightChange);
	    },
	    onTextareaMount() {
	      const textareaContainer = this.$refs['textarea-container'];
	      this.textareaHeight = textareaContainer.clientHeight;
	    },
	    onTextareaHeightChange(event) {
	      const {
	        newHeight
	      } = event.getData();
	      this.textareaHeight = newHeight;
	    },
	    onShowLoadingBar(event) {
	      const {
	        dialogId
	      } = event.getData();
	      if (dialogId !== this.dialogId) {
	        return;
	      }
	      this.showLoadingBar = true;
	    },
	    onHideLoadingBar(event) {
	      const {
	        dialogId
	      } = event.getData();
	      if (dialogId !== this.dialogId) {
	        return;
	      }
	      this.showLoadingBar = false;
	    },
	    onChangeSidebarPanel({
	      panel
	    }) {
	      this.currentSidebarPanel = panel;
	    },
	    bindEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.showLoadingBar, this.onShowLoadingBar);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.hideLoadingBar, this.onHideLoadingBar);
	    },
	    unbindEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.showLoadingBar, this.onShowLoadingBar);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.hideLoadingBar, this.onHideLoadingBar);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-content-chat__scope bx-im-content-chat__container" :class="containerClasses" :style="backgroundStyle">
			<div class="bx-im-content-chat__content" ref="content">
				<slot name="header">
					<ChatHeader :dialogId="dialogId" :key="dialogId" />
				</slot>
				<div :style="dialogContainerStyle" class="bx-im-content-chat__dialog_container">
					<Transition name="loading-bar-transition">
						<LoadingBar v-if="showLoadingBar" />
					</Transition>
					<div class="bx-im-content-chat__dialog_content">
						<slot name="dialog">
							<ChatDialog :dialogId="dialogId" :key="dialogId"/>
						</slot>
					</div>
				</div>
				<!-- Textarea -->
				<Transition name="bx-im-panel-transition">
					<BulkActionsPanel v-if="isBulkActionsMode"/>
					<div v-else-if="canSend" v-textarea-observer class="bx-im-content-chat__textarea_container" ref="textarea-container">
						<slot name="textarea" :onTextareaMount="onTextareaMount">
							<ChatTextarea
								:dialogId="dialogId"
								:key="dialogId"
								:withAudioInput="false"
								@mounted="onTextareaMount"
							/>
						</slot>
					</div>
					<slot v-else-if="isGuest" name="join-panel">
						<JoinPanel :dialogId="dialogId" />
					</slot>
					<MutePanel v-else :dialogId="dialogId" />
				</Transition>
				<DropArea :dialogId="dialogId" :container="$refs.content || {}" :key="dialogId" />
				<!-- End textarea -->
			</div>
			<ChatSidebar
				v-if="dialogId && withSidebar" 
				:originDialogId="dialogId"
				:isActive="!hasCommentsOnTop"
				@changePanel="onChangeSidebarPanel"
			/>
		</div>
	`
	};

	exports.ChatHeader = ChatHeader;
	exports.BaseChatContent = BaseChatContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX,BX.Call.Component,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Vue3,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.UI.Uploader,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Service,BX.Messenger.v2.Const,BX.Event,BX.Vue3.Directives,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.EntitySelector));
//# sourceMappingURL=registry.bundle.js.map
