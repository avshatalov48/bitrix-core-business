/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_layout,im_v2_lib_utils,im_v2_lib_channel,im_v2_lib_access,im_v2_component_animation,im_v2_component_entitySelector,im_v2_application_core,im_public,im_v2_component_content_chatForms_forms,im_v2_lib_feature,im_v2_lib_theme,ui_notification,im_v2_lib_analytics,im_v2_lib_permission,im_v2_component_content_elements,im_v2_lib_logger,im_v2_model,im_v2_component_dialog_chat,im_v2_lib_messageComponentManager,main_core,main_core_events,im_v2_component_messageList,im_v2_const,im_v2_component_textarea,im_v2_component_elements,im_v2_provider_service) {
	'use strict';

	// @vue/component
	const CommentsButton = {
	  name: 'CommentsButton',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    counter: {
	      type: Number,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-channel__comments-button">
			<div class="bx-im-dialog-channel__comments-button_counter">
				{{ counter }}
			</div>
		</div>
	`
	};

	class ChannelMessageMenu extends im_v2_component_messageList.MessageMenu {
	  getMenuItems() {
	    return [
	    // this.getReplyItem(),
	    this.getCopyItem(), this.getCopyLinkItem(), this.getCopyFileItem(), this.getPinItem(), this.getForwardItem(), this.getDelimiter(), this.getMarkItem(), this.getFavoriteItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDiskItem(), this.getDelimiter(), this.getEditItem(), this.getDeleteItem(), this.getDelimiter(), this.getSelectItem()];
	  }
	}

	// @vue/component
	const ChannelMessageList = {
	  name: 'ChannelMessageList',
	  components: {
	    MessageList: im_v2_component_messageList.MessageList
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ChannelMessageMenu: () => ChannelMessageMenu
	  },
	  template: `
		<MessageList :dialogId="dialogId" :messageMenuClass="ChannelMessageMenu" />
	`
	};

	// @vue/component
	const ChannelDialog = {
	  name: 'ChannelDialog',
	  components: {
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    ChannelMessageList,
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
	      return this.layout.name === im_v2_const.Layout.chat.name;
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
	      const messageId = await this.$refs.dialog.getMessageService().loadContextByChatId(chatId).catch(error => {
	        // eslint-disable-next-line no-console
	        console.error('ChannelDialog: goToMessageContextByCommentsChatId error', error);
	      });
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
	      im_v2_provider_service.CommentsService.readAllChannelComments(this.dialogId);
	    }
	  },
	  template: `
		<ChatDialog ref="dialog" :dialogId="dialogId" :resetOnExit="isGuest">
			<template #message-list>
				<ChannelMessageList :dialogId="dialogId" />
			</template>
			<template #additional-float-button>
				<Transition name="float-button-transition">
					<CommentsButton
						v-if="showCommentsButton"
						:dialogId="dialogId"
						:counter="totalChannelCommentsCounter"
						@click="onCommentsButtonClick"
						key="comments"
					/>
				</Transition>
			</template>
		</ChatDialog>
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
			:withCreateMenu="false"
			:withMarket="false"
			:withAudioInput="false"
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
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    LineLoader: im_v2_component_elements.LineLoader,
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
				<ChatTitle :dialogId="dialogId" />
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
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-collab-header__links-container" :class="{'--compact': compactMode}">
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
	  methods: {
	    openAddToChatPopup() {
	      im_v2_lib_analytics.Analytics.getInstance().userAdd.onChatHeaderClick(this.dialogId);
	      this.showAddToChatPopup = true;
	    },
	    closeAddToChatPopup() {
	      this.$emit('close');
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
	    SpecialBackground: () => im_v2_lib_theme.SpecialBackground
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId" :backgroundId="SpecialBackground.collab">
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
	    EditableChatTitle: im_v2_component_elements.EditableChatTitle,
	    ChatTitle: im_v2_component_elements.ChatTitle
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
	      return im_v2_provider_service.RecentService.getInstance().getCollection().length === 0;
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

	class UserService {
	  async updateLastActivityDate(userId) {
	    if (this.isPullServerWithUserStatusSupport()) {
	      const lastActivityDate = await this.getUserActivityFromPull(userId);
	      if (!lastActivityDate) {
	        return Promise.resolve();
	      }
	      return this.updateUserModel(userId, {
	        lastActivityDate
	      });
	    }
	    const userData = await this.requestUserData(userId);
	    return this.updateUserModel(userId, userData);
	  }
	  async getUserActivityFromPull(userId) {
	    const result = await im_v2_application_core.Core.getPullClient().getUsersLastSeen([userId]).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: error getting user activity from P&P', error);
	    });
	    if (!main_core.Type.isNumber(result[userId])) {
	      return null;
	    }
	    const activityDateAgo = result[userId] * 1000;
	    return new Date(Date.now() - activityDateAgo);
	  }
	  async requestUserData(userId) {
	    im_v2_lib_logger.Logger.warn(`UserService: get actual user data for - ${userId}`);
	    const answer = await im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imUserGet, {
	      ID: userId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('UserService: error getting user data', error);
	    });
	    return answer.data();
	  }
	  async updateUserModel(userId, userFields) {
	    im_v2_lib_logger.Logger.warn('UserService: update user data', userFields);
	    return im_v2_application_core.Core.getStore().dispatch('users/update', {
	      id: userId,
	      fields: userFields
	    });
	  }
	  isPullServerWithUserStatusSupport() {
	    return im_v2_application_core.Core.getPullClient().isJsonRpc();
	  }
	}

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
	    MessengerButton: im_v2_component_elements.Button
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
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
				<MessengerButton
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

	const EmptyStateComponentByLayout = {
	  [im_v2_const.Layout.channel.name]: ChannelEmptyState,
	  [im_v2_const.Layout.collab.name]: CollabEmptyState,
	  default: BaseEmptyState
	};

	// @vue/component
	const ChatOpener = {
	  name: 'ChatOpener',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    ChannelContent,
	    CollabContent,
	    MultidialogContent,
	    EmptyState: BaseEmptyState,
	    ChannelEmptyState
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {};
	  },
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
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    emptyStateComponent() {
	      var _EmptyStateComponentB;
	      return (_EmptyStateComponentB = EmptyStateComponentByLayout[this.layout.name]) != null ? _EmptyStateComponentB : EmptyStateComponentByLayout.default;
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
	          name: im_v2_const.Layout.chat.name,
	          entityId: realDialogId,
	          contextId: this.layout.contextId
	        });
	        return;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.dialogId} is already loaded`);
	        if (this.isUser) {
	          const userId = parseInt(this.dialog.dialogId, 10);
	          void this.getUserService().updateLastActivityDate(userId);
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
	      await this.getChatService().loadChatWithContext(this.dialogId, this.layout.contextId).catch(errors => {
	        this.sendAnalytics(errors);
	        this.handleChatLoadError(errors);
	        im_v2_lib_logger.Logger.error(errors);
	        im_public.Messenger.openChat();
	      });
	      im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.dialogId} is loaded with context of ${this.layout.contextId}`);
	    },
	    async loadChat() {
	      im_v2_lib_logger.Logger.warn(`ChatContent: loading chat ${this.dialogId}`);
	      await this.getChatService().loadChatWithMessages(this.dialogId).catch(errors => {
	        this.handleChatLoadError(errors);
	        im_v2_lib_logger.Logger.error(errors);
	        im_public.Messenger.openChat();
	      });
	      im_v2_lib_logger.Logger.warn(`ChatContent: chat ${this.dialogId} is loaded`);
	    },
	    handleChatLoadError(errors) {
	      const [firstError] = errors;
	      if (firstError.code === im_v2_lib_access.AccessErrorCode.accessDenied) {
	        this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
	      } else if (firstError.code === im_v2_lib_access.AccessErrorCode.messageNotFound) {
	        this.showNotification(this.loc('IM_CONTENT_CHAT_CONTEXT_MESSAGE_NOT_FOUND'));
	      }
	    },
	    sendAnalytics(errors) {
	      const [firstError] = errors;
	      if (firstError.code !== im_v2_lib_access.AccessErrorCode.messageNotFound) {
	        return;
	      }
	      im_v2_lib_analytics.Analytics.getInstance().messageDelete.onNotFoundNotification({
	        dialogId: this.dialogId
	      });
	    },
	    showNotification(text) {
	      BX.UI.Notification.Center.notify({
	        content: text
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
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
			<ChannelContent v-else-if="isChannel" :dialogId="dialogId" />
			<CollabContent v-else-if="isCollab" :dialogId="dialogId" />
			<MultidialogContent v-else-if="isMultidialog" :dialogId="dialogId" />
			<BaseChatContent v-else :dialogId="dialogId" />
		</div>
	`
	};

	// @vue/component
	const SubscribeToggle = {
	  name: 'SubscribeToggle',
	  components: {
	    Toggle: im_v2_component_elements.Toggle
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
	    ToggleSize: () => im_v2_component_elements.ToggleSize,
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
	        im_v2_provider_service.CommentsService.unsubscribe(this.postMessageId);
	        return;
	      }
	      im_v2_provider_service.CommentsService.subscribe(this.postMessageId);
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
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
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
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
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

	class CommentsMessageMenu extends im_v2_component_messageList.MessageMenu {
	  getMenuItems() {
	    if (this.isPostMessage()) {
	      return [this.getCopyItem(), this.getCopyFileItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDiskItem(), this.getDelimiter(), this.getOpenInChannelItem()];
	    }
	    return [this.getReplyItem(), this.getCopyItem(), this.getCopyFileItem(),
	    // this.getPinItem(),
	    // this.getForwardItem(),
	    this.getDelimiter(),
	    // this.getMarkItem(),
	    this.getFavoriteItem(), this.getDelimiter(), this.getCreateItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDiskItem(), this.getDelimiter(), this.getEditItem(), this.getDeleteItem()];
	  }
	  getOpenInChannelItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_CONTENT_COMMENTS_MESSAGE_MENU_OPEN_IN_CHANNEL'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeComments);
	        this.menuInstance.close();
	      }
	    };
	  }
	  isPostMessage() {
	    const {
	      dialogId
	    } = this.store.getters['chats/getByChatId'](this.context.chatId);
	    return dialogId !== this.context.dialogId;
	  }
	}

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
	    CommentsMessageMenu: () => CommentsMessageMenu,
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
	      return new im_v2_lib_messageComponentManager.MessageComponentManager(message).getName();
	    }
	  },
	  template: `
		<MessageList
			:dialogId="dialogId"
			:messageMenuClass="CommentsMessageMenu"
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
	      await dialog.getMessageService().loadFirstPage().catch(error => {
	        im_v2_lib_logger.Logger.error('goToMessageContext error', error);
	      });
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
		<ChatDialog ref="dialog" :dialogId="dialogId" :saveScrollOnExit="false" :resetOnExit="true">
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
			:withAudioInput="false"
			class="bx-im-comments-send-panel__container"
		/>
	`
	};

	// @vue/component
	const JoinPanel$1 = {
	  components: {
	    ChatButton: im_v2_component_elements.Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
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
	      await this.getChatService().loadComments(this.postId).catch(error => {
	        this.handleChatLoadError(error);
	        im_v2_lib_logger.Logger.error(error);
	        this.$emit('close');
	      });
	      im_v2_lib_logger.Logger.warn(`CommentsContent: comments for post ${this.postId} are loaded`);
	    },
	    handleChatLoadError(error) {
	      const [firstError] = error;
	      if (firstError.code === 'ACCESS_DENIED') {
	        this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
	      }
	    },
	    showNotification(text) {
	      BX.UI.Notification.Center.notify({
	        content: text
	      });
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
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

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib,BX.Messenger.v2.Model,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Lib,BX,BX.Event,BX.Messenger.v2.Component,BX.Messenger.v2.Const,BX.Messenger.v2.Component,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service));
//# sourceMappingURL=chat-content.bundle.js.map
