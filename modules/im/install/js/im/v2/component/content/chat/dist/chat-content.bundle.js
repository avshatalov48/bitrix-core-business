/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_public,im_v2_lib_layout,im_v2_lib_utils,im_v2_lib_channel,im_v2_lib_rest,im_v2_lib_theme,im_v2_application_core,ui_notification,im_v2_lib_analytics,im_v2_lib_permission,im_v2_component_content_elements,im_v2_lib_logger,im_v2_model,im_v2_component_dialog_chat,im_v2_lib_messageComponentManager,main_core,main_core_events,im_v2_component_messageList,im_v2_const,im_v2_component_textarea,im_v2_component_elements,im_v2_provider_service) {
	'use strict';

	const AccessErrorCode = {
	  accessDenied: 'ACCESS_DENIED',
	  chatNotFound: 'CHAT_NOT_FOUND',
	  messageNotFound: 'MESSAGE_NOT_FOUND',
	  messageAccessDenied: 'MESSAGE_ACCESS_DENIED',
	  messageAccessDeniedByTariff: 'MESSAGE_ACCESS_DENIED_BY_TARIFF'
	};

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
	    this.getCopyItem(), this.getCopyLinkItem(), this.getCopyFileItem(), this.getPinItem(), this.getForwardItem(), this.getDelimiter(), this.getMarkItem(), this.getFavoriteItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDisk(), this.getDelimiter(), this.getEditItem(), this.getDeleteItem()];
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

	const GROUP_ID_PARAM_NAME = 'COLLAB_GROUP_ID';

	// @vue/component
	const CollabHeader = {
	  name: 'CollabHeader',
	  components: {
	    ChatHeader: im_v2_component_content_elements.ChatHeader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      groupId: 0
	    };
	  },
	  computed: {
	    entityLinks() {
	      return [{
	        title: 'Tasks',
	        clickHandler: () => {
	          BX.SidePanel.Instance.open(`https://kotlyarchuk.bx/workgroups/group/${this.groupId}/tasks/`);
	        }
	      }, {
	        title: 'Files',
	        clickHandler: () => {
	          BX.SidePanel.Instance.open(`https://kotlyarchuk.bx/workgroups/group/${this.groupId}/disk/path/`);
	        }
	      }, {
	        title: 'Calendar',
	        clickHandler: () => {
	          BX.SidePanel.Instance.open(`https://kotlyarchuk.bx/workgroups/group/${this.groupId}/calendar/`);
	        }
	      }];
	    }
	  },
	  created() {
	    var _urlQuery$get;
	    const urlQuery = new URLSearchParams(window.location.search);
	    this.groupId = (_urlQuery$get = urlQuery.get(GROUP_ID_PARAM_NAME)) != null ? _urlQuery$get : 1;
	  },
	  // language=Vue
	  template: `
		<ChatHeader :dialogId="dialogId" class="bx-im-collab-header__container">
			<template #before-actions>
				<div class="bx-im-collab-header__links-container">
					<div v-for="{ title, clickHandler } in entityLinks" :key="title" @click="clickHandler" class="bx-im-collab-header__links-container_item">
						{{ title }}
					</div>
				</div>
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
	const EmptyState = {
	  data() {
	    return {};
	  },
	  computed: {
	    iconClass() {
	      return this.isEmptyRecent ? '--empty' : '--default';
	    },
	    text() {
	      if (this.isEmptyRecent) {
	        return this.loc('IM_CONTENT_CHAT_NO_CHATS_START_MESSAGE');
	      }
	      if (this.isChannelLayout) {
	        return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_V3');
	      }
	      return this.loc('IM_CONTENT_CHAT_START_MESSAGE_V2');
	    },
	    subtext() {
	      if (this.isChannelLayout) {
	        return this.loc('IM_CONTENT_CHANNEL_START_MESSAGE_SUBTITLE');
	      }
	      return '';
	    },
	    isEmptyRecent() {
	      return im_v2_provider_service.RecentService.getInstance().getCollection().length === 0;
	    },
	    isChannelLayout() {
	      return this.layout.name === im_v2_const.Layout.channel.name;
	    },
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    backgroundStyle() {
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
					{{ text }}
				</div>
				<div v-if="subtext" class="bx-im-content-chat-start__subtitle">
					{{ subtext }}
				</div>
			</div>
		</div>
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
	const ChatOpener = {
	  name: 'ChatOpener',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    ChannelContent,
	    CollabContent,
	    MultidialogContent,
	    EmptyState
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
	      if (firstError.code === AccessErrorCode.accessDenied) {
	        this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
	      } else if (firstError.code === AccessErrorCode.messageNotFound) {
	        this.showNotification(this.loc('IM_CONTENT_CHAT_CONTEXT_MESSAGE_NOT_FOUND'));
	      }
	    },
	    sendAnalytics(errors) {
	      const [firstError] = errors;
	      if (firstError.code !== AccessErrorCode.messageNotFound) {
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
			<EmptyState v-if="!dialogId" />
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
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.subscribeToComments, this.dialogId);
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
	      return [this.getCopyItem(), this.getCopyFileItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDisk(), this.getDelimiter(), this.getOpenInChannelItem()];
	    }
	    return [this.getReplyItem(), this.getCopyItem(), this.getCopyFileItem(),
	    // this.getPinItem(),
	    // this.getForwardItem(),
	    this.getDelimiter(),
	    // this.getMarkItem(),
	    this.getFavoriteItem(), this.getDelimiter(), this.getCreateItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDisk(), this.getDelimiter(), this.getEditItem(), this.getDeleteItem()];
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
					<AuthorGroup :item="postAuthorGroup" :contextDialogId="dialogId">
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

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib,BX.Messenger.v2.Model,BX.Messenger.v2.Component.Dialog,BX.Messenger.v2.Lib,BX,BX.Event,BX.Messenger.v2.Component,BX.Messenger.v2.Const,BX.Messenger.v2.Component,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service));
//# sourceMappingURL=chat-content.bundle.js.map
