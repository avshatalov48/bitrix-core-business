/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_logger,ui_notification,main_core_events,im_v2_component_content_elements,im_v2_component_entitySelector,im_v2_lib_promo,im_v2_lib_analytics,main_popup,im_v2_component_textarea,im_v2_lib_draft,im_v2_component_dialog_chat,main_core,im_v2_component_messageList,im_v2_lib_copilot,im_public,im_v2_component_elements,im_v2_const,im_v2_lib_theme,im_v2_provider_service) {
	'use strict';

	const POPUP_ID = 'im-add-to-chat-hint-popup';

	// @vue/component
	const AddToChatHint = {
	  name: 'AddToChatHint',
	  components: {
	    MessengerPopup: im_v2_component_elements.MessengerPopup
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
			v-slot="{enableAutoHide, disableAutoHide}"
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
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
	const AddToChatButton = {
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
	    this.showAddToChatHint = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.addUsersToCopilotChat);
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
	    EditableChatTitle: im_v2_component_elements.EditableChatTitle,
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    AddToChatButton
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
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    },
	    isGroupCopilotChat() {
	      return this.dialog.userCounter > 2;
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
	      this.getChatService().renameChat(this.dialogId, newTitle).catch(() => {
	        BX.UI.Notification.Center.notify({
	          content: this.loc('IM_CONTENT_CHAT_HEADER_RENAME_ERROR')
	        });
	      });
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
	        this.chatService = new im_v2_provider_service.ChatService();
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
	  computed: {
	    CopilotDraftManager: () => im_v2_lib_draft.CopilotDraftManager
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<ChatTextarea
			:dialogId="dialogId"
			:placeholder="this.loc('IM_CONTENT_COPILOT_TEXTAREA_PLACEHOLDER')"
			:withCreateMenu="false"
			:withMarket="false"
			:withEdit="false"
			:withUploadMenu="false"
			:withSmileSelector="false"
			:draftManagerClass="CopilotDraftManager"
		/>
	`
	};

	const CopilotChatContext = Object.freeze({
	  personal: 'chat_copilot_tab_one_by_one',
	  group: 'chat_copilot_tab_multi'
	});
	class CopilotMessageMenu extends im_v2_component_messageList.MessageMenu {
	  getMenuItems() {
	    return [this.getCopyItem(), this.getFavoriteItem(), this.getForwardItem(), this.getSendFeedbackItem(), this.getDeleteItem(), this.getSelectItem()];
	  }
	  getSendFeedbackItem() {
	    const copilotManager = new im_v2_lib_copilot.CopilotManager();
	    if (!copilotManager.isCopilotBot(this.context.authorId)) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_CONTENT_COPILOT_CONTEXT_MENU_FEEDBACK'),
	      onclick: () => {
	        void this.openForm();
	        this.menuInstance.close();
	      }
	    };
	  }
	  async openForm() {
	    const formId = Math.round(Math.random() * 1000);
	    await main_core.Runtime.loadExtension(['ui.feedback.form']);
	    BX.UI.Feedback.Form.open({
	      id: `im.copilot.feedback-${formId}`,
	      forms: [{
	        zones: ['es'],
	        id: 684,
	        lang: 'es',
	        sec: 'svvq1x'
	      }, {
	        zones: ['en'],
	        id: 686,
	        lang: 'en',
	        sec: 'tjwodz'
	      }, {
	        zones: ['de'],
	        id: 688,
	        lang: 'de',
	        sec: 'nrwksg'
	      }, {
	        zones: ['com.br'],
	        id: 690,
	        lang: 'com.br',
	        sec: 'kpte6m'
	      }, {
	        zones: ['ru', 'by', 'kz'],
	        id: 692,
	        lang: 'ru',
	        sec: 'jbujn0'
	      }],
	      presets: {
	        sender_page: this.getCopilotChatContext(),
	        language: main_core.Loc.getMessage('LANGUAGE_ID'),
	        cp_answer: this.context.text
	      }
	    });
	  }
	  getCopilotChatContext() {
	    const chat = this.store.getters['chats/get'](this.context.dialogId);
	    if (chat.userCounter <= 2) {
	      return CopilotChatContext.personal;
	    }
	    return CopilotChatContext.group;
	  }
	}

	// @vue/component
	const CopilotMessageList = {
	  name: 'CopilotMessageList',
	  components: {
	    MessageList: im_v2_component_messageList.MessageList,
	    DialogStatus: im_v2_component_elements.DialogStatus
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    CopilotMessageMenu: () => CopilotMessageMenu
	  },
	  template: `
		<MessageList :dialogId="dialogId" :messageMenuClass="CopilotMessageMenu" />
	`
	};

	// @vue/component
	const CopilotDialog = {
	  name: 'CopilotDialog',
	  components: {
	    ChatDialog: im_v2_component_dialog_chat.ChatDialog,
	    CopilotMessageList
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<ChatDialog :dialogId="dialogId">
			<template #message-list>
				<CopilotMessageList :dialogId="dialogId" />
			</template>
		</ChatDialog>	
	`
	};

	const COPILOT_BACKGROUND_ID = 4;

	// @vue/component
	const CopilotInternalContent = {
	  name: 'CopilotInternalContent',
	  components: {
	    BaseChatContent: im_v2_component_content_elements.BaseChatContent,
	    CopilotChatHeader,
	    CopilotDialog,
	    CopilotTextarea
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    COPILOT_BACKGROUND_ID: () => COPILOT_BACKGROUND_ID
	  },
	  template: `
		<BaseChatContent :dialogId="dialogId" :backgroundId="COPILOT_BACKGROUND_ID">
			<template #header>
				<CopilotChatHeader :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #dialog>
				<CopilotDialog :dialogId="dialogId" :key="dialogId" />
			</template>
			<template #textarea="{ onTextareaMount }">
				<CopilotTextarea :dialogId="dialogId" :key="dialogId" @mounted="onTextareaMount" />
			</template>
		</BaseChatContent>
	`
	};

	const BUTTON_BACKGROUND_COLOR = '#fff';
	const BUTTON_HOVER_COLOR = '#eee';
	const BUTTON_TEXT_COLOR = 'rgba(82, 92, 105, 0.9)';

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  components: {
	    ChatButton: im_v2_component_elements.Button,
	    CopilotRolesDialog: im_v2_component_elements.CopilotRolesDialog
	  },
	  data() {
	    return {
	      isCreatingChat: false,
	      showRolesDialog: false
	    };
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements.ButtonSize,
	    backgroundStyle() {
	      return im_v2_lib_theme.ThemeManager.getBackgroundStyleById(COPILOT_BACKGROUND_ID);
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
	        this.showCreateChatError();
	      });
	      this.isCreatingChat = false;
	      void im_public.Messenger.openCopilot(newDialogId);
	    },
	    showCreateChatError() {
	      BX.UI.Notification.Center.notify({
	        content: this.loc('IM_CONTENT_COPILOT_EMPTY_STATE_ERROR_CREATING_CHAT')
	      });
	    },
	    getCopilotService() {
	      if (!this.copilotService) {
	        this.copilotService = new im_v2_provider_service.CopilotService();
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
	const CopilotContent = {
	  name: 'CopilotContent',
	  components: {
	    EmptyState,
	    CopilotInternalContent
	  },
	  props: {
	    entityId: {
	      type: String,
	      default: ''
	    },
	    contextMessageId: {
	      type: Number,
	      default: 0
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.entityId, true);
	    }
	  },
	  watch: {
	    entityId(newValue, oldValue) {
	      im_v2_lib_logger.Logger.warn(`CopilotContent: switching from ${oldValue || 'empty'} to ${newValue}`);
	      this.onChatChange();
	    }
	  },
	  created() {
	    if (!this.entityId) {
	      return;
	    }
	    this.onChatChange();
	  },
	  methods: {
	    async onChatChange() {
	      if (this.entityId === '') {
	        return;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is already loaded`);
	        im_v2_lib_analytics.Analytics.getInstance().onOpenChat(this.dialog);
	        return;
	      }
	      if (this.dialog.loading) {
	        im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is loading`);
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
	      im_v2_lib_logger.Logger.warn(`CopilotContent: loading chat ${this.entityId} with context - ${this.layout.contextId}`);
	      await this.getChatService().loadChatWithContext(this.entityId, this.layout.contextId).catch(error => {
	        if (error.code === 'ACCESS_ERROR') {
	          this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
	        }
	        im_v2_lib_logger.Logger.error(error);
	        im_public.Messenger.openCopilot();
	      });
	      im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is loaded with context of ${this.layout.contextId}`);
	      return Promise.resolve();
	    },
	    async loadChat() {
	      im_v2_lib_logger.Logger.warn(`CopilotContent: loading chat ${this.entityId}`);
	      await this.getChatService().loadChatWithMessages(this.entityId).catch(error => {
	        const [firstError] = error;
	        if (firstError.code === 'ACCESS_DENIED') {
	          this.showNotification(this.loc('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
	        }
	        im_public.Messenger.openCopilot();
	      });
	      im_v2_lib_logger.Logger.warn(`CopilotContent: chat ${this.entityId} is loaded`);
	      return Promise.resolve();
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
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<EmptyState v-if="!entityId" />
		<CopilotInternalContent v-else :dialogId="entityId" />
	`
	};

	exports.CopilotContent = CopilotContent;

}((this.BX.Messenger.v2.Component.Content = this.BX.Messenger.v2.Component.Content || {}),BX.Messenger.v2.Lib,BX,BX.Event,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Main,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Dialog,BX,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Service));
//# sourceMappingURL=copilot-content.bundle.js.map
