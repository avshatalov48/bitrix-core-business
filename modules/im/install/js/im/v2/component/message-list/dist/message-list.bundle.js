/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_user,im_v2_lib_logger,im_v2_lib_smileManager,im_public,im_v2_lib_permission,im_v2_lib_confirm,im_v2_lib_menu,im_v2_lib_parser,im_v2_lib_entityCreator,im_v2_provider_service,im_v2_lib_market,im_v2_lib_utils,ui_notification,im_v2_component_elements,main_core,main_core_events,im_v2_application_core,im_v2_const,im_v2_lib_dateFormatter,im_v2_component_message_file,im_v2_component_message_default,im_v2_component_message_callInvite,im_v2_component_message_deleted,im_v2_component_message_unsupported,im_v2_component_message_smile,im_v2_component_message_system,im_v2_component_message_chatCreation,im_v2_component_message_copilot_creation,im_v2_component_message_copilot_answer,im_v2_component_message_support_vote,im_v2_component_message_support_sessionNumber,im_v2_component_message_conferenceCreation,im_v2_component_message_ownChatCreation,im_v2_component_message_zoomInvite) {
	'use strict';

	class UserService {
	  async loadReadUsers(messageId) {
	    let users = [];
	    im_v2_lib_logger.Logger.warn('Dialog: UserService: loadReadUsers', messageId);
	    const response = await im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imV2ChatMessageTailViewers, {
	      id: messageId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('Dialog: UserService: loadReadUsers error', error);
	      throw new Error(error);
	    });
	    users = response.data().users;
	    const userManager = new im_v2_lib_user.UserManager();
	    await userManager.setUsersToModel(Object.values(users));
	    return users.map(user => user.id);
	  }
	}

	// @vue/component
	const AdditionalUsers = {
	  components: {
	    UserListPopup: im_v2_component_elements.UserListPopup
	  },
	  props: {
	    dialogId: {
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
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    }
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
	    async loadUsers() {
	      this.loadingAdditionalUsers = true;
	      const userIds = await this.getUserService().loadReadUsers(this.dialog.lastMessageId).catch(() => {
	        this.loadingAdditionalUsers = false;
	      });
	      this.additionalUsers = this.prepareAdditionalUsers(userIds);
	      this.loadingAdditionalUsers = false;
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
			id="bx-im-dialog-read-users"
			:showPopup="showPopup"
			:loading="loadingAdditionalUsers"
			:userIds="additionalUsers"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`
	};

	const TYPING_USERS_COUNT = 3;
	const MORE_USERS_CSS_CLASS = 'bx-im-dialog-chat-status__user-count';

	// @vue/component
	const DialogStatus = {
	  components: {
	    AdditionalUsers
	  },
	  props: {
	    dialogId: {
	      required: true,
	      type: String
	    }
	  },
	  data() {
	    return {
	      showAdditionalUsers: false,
	      additionalUsersLinkElement: null
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    typingStatus() {
	      if (!this.dialog.inited || this.dialog.writingList.length === 0) {
	        return '';
	      }
	      const firstTypingUsers = this.dialog.writingList.slice(0, TYPING_USERS_COUNT);
	      const text = firstTypingUsers.map(element => element.userName).join(', ');
	      const remainingUsersCount = this.dialog.writingList.length - TYPING_USERS_COUNT;
	      if (remainingUsersCount > 0) {
	        return this.loc('IM_DIALOG_CHAT_STATUS_TYPING_PLURAL', {
	          '#USER#': text,
	          '#COUNT#': remainingUsersCount
	        });
	      }
	      return this.loc('IM_DIALOG_CHAT_STATUS_TYPING', {
	        '#USER#': text
	      });
	    },
	    readStatus() {
	      if (!this.dialog.inited) {
	        return '';
	      }
	      if (this.lastMessageViews.countOfViewers === 0) {
	        return '';
	      }
	      if (this.isUser) {
	        return this.formatUserViewStatus();
	      }
	      return this.formatChatViewStatus();
	    },
	    lastMessageViews() {
	      return this.dialog.lastMessageViews;
	    }
	  },
	  methods: {
	    formatUserViewStatus() {
	      const {
	        date
	      } = this.lastMessageViews.firstViewer;
	      return this.loc('IM_DIALOG_CHAT_STATUS_READ_USER', {
	        '#DATE#': im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.messageReadStatus)
	      });
	    },
	    formatChatViewStatus() {
	      const {
	        countOfViewers,
	        firstViewer
	      } = this.lastMessageViews;
	      if (countOfViewers === 1) {
	        return this.loc('IM_DIALOG_CHAT_STATUS_READ_CHAT', {
	          '#USER#': main_core.Text.encode(firstViewer.userName)
	        });
	      }
	      return this.loc('IM_DIALOG_CHAT_STATUS_READ_CHAT_PLURAL', {
	        '#USERS#': main_core.Text.encode(firstViewer.userName),
	        '#LINK_START#': `<span class="${MORE_USERS_CSS_CLASS}" ref="moreUsersLink">`,
	        '#COUNT#': countOfViewers - 1,
	        '#LINK_END#': '</span>'
	      });
	    },
	    onClick(event) {
	      if (!event.target.matches(`.${MORE_USERS_CSS_CLASS}`)) {
	        return;
	      }
	      this.onMoreUsersClick();
	    },
	    onMoreUsersClick() {
	      this.additionalUsersLinkElement = document.querySelector(`.${MORE_USERS_CSS_CLASS}`);
	      this.showAdditionalUsers = true;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div @click="onClick" class="bx-im-dialog-chat-status__container">
			<div v-if="typingStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --typing"></div>
				<div class="bx-im-dialog-chat-status__text">{{ typingStatus }}</div>
			</div>
			<div v-else-if="readStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --read"></div>
				<div v-html="readStatus" class="bx-im-dialog-chat-status__text"></div>
			</div>
			<AdditionalUsers
				:dialogId="dialogId"
				:show="showAdditionalUsers"
				:bindElement="additionalUsersLinkElement || {}"
				@close="showAdditionalUsers = false"
			/>
		</div>
	`
	};

	// @vue/component
	const DialogLoader = {
	  name: 'DialogLoader',
	  props: {
	    fullHeight: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-loader__container" :class="{'--full-height': fullHeight}">
			<div class="bx-im-dialog-loader__spinner"></div>
			<div class="bx-im-dialog-loader__text">{{ loc('IM_DIALOG_CHAT_LOADER_TEXT') }}</div>
		</div>
	`
	};

	const serverComponentList = new Set([im_v2_const.MessageComponent.unsupported, im_v2_const.MessageComponent.chatCreation, im_v2_const.MessageComponent.ownChatCreation, im_v2_const.MessageComponent.conferenceCreation, im_v2_const.MessageComponent.callInvite, im_v2_const.MessageComponent.copilotCreation, im_v2_const.MessageComponent.copilotMessage, im_v2_const.MessageComponent.supportVote, im_v2_const.MessageComponent.supportSessionNumber, im_v2_const.MessageComponent.zoomInvite]);
	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _isServerComponent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isServerComponent");
	var _hasFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasFiles");
	var _hasText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasText");
	var _hasAttach = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasAttach");
	var _isEmptyMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEmptyMessage");
	var _isDeletedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletedMessage");
	var _isSystemMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSystemMessage");
	var _isEmojiOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEmojiOnly");
	var _hasSmilesOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasSmilesOnly");
	var _hasOnlyText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOnlyText");
	var _isForward = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isForward");
	class MessageComponentManager {
	  constructor(message) {
	    Object.defineProperty(this, _isForward, {
	      value: _isForward2
	    });
	    Object.defineProperty(this, _hasOnlyText, {
	      value: _hasOnlyText2
	    });
	    Object.defineProperty(this, _hasSmilesOnly, {
	      value: _hasSmilesOnly2
	    });
	    Object.defineProperty(this, _isEmojiOnly, {
	      value: _isEmojiOnly2
	    });
	    Object.defineProperty(this, _isSystemMessage, {
	      value: _isSystemMessage2
	    });
	    Object.defineProperty(this, _isDeletedMessage, {
	      value: _isDeletedMessage2
	    });
	    Object.defineProperty(this, _isEmptyMessage, {
	      value: _isEmptyMessage2
	    });
	    Object.defineProperty(this, _hasAttach, {
	      value: _hasAttach2
	    });
	    Object.defineProperty(this, _hasText, {
	      value: _hasText2
	    });
	    Object.defineProperty(this, _hasFiles, {
	      value: _hasFiles2
	    });
	    Object.defineProperty(this, _isServerComponent, {
	      value: _isServerComponent2
	    });
	    Object.defineProperty(this, _message, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message] = message;
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  getName() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return im_v2_const.MessageComponent.deleted;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isServerComponent)[_isServerComponent]()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSystemMessage)[_isSystemMessage]()) {
	      return im_v2_const.MessageComponent.system;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]()) {
	      return im_v2_const.MessageComponent.file;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isEmojiOnly)[_isEmojiOnly]() || babelHelpers.classPrivateFieldLooseBase(this, _hasSmilesOnly)[_hasSmilesOnly]()) {
	      return im_v2_const.MessageComponent.smile;
	    }
	    return im_v2_const.MessageComponent.default;
	  }
	}
	function _isServerComponent2() {
	  return serverComponentList.has(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId);
	}
	function _hasFiles2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].files.length > 0;
	}
	function _hasText2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text.length > 0;
	}
	function _hasAttach2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].attach.length > 0;
	}
	function _isEmptyMessage2() {
	  return !babelHelpers.classPrivateFieldLooseBase(this, _hasText)[_hasText]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasAttach)[_hasAttach]();
	}
	function _isDeletedMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].isDeleted || babelHelpers.classPrivateFieldLooseBase(this, _isEmptyMessage)[_isEmptyMessage]();
	}
	function _isSystemMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].authorId === 0;
	}
	function _isEmojiOnly2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].replyId > 0) {
	    return false;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isForward)[_isForward]()) {
	    return false;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasOnlyText)[_hasOnlyText]()) {
	    return false;
	  }
	  return im_v2_lib_utils.Utils.text.isEmojiOnly(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text);
	}
	function _hasSmilesOnly2() {
	  var _smileManager$smileLi, _smileManager$smileLi2;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].replyId > 0) {
	    return false;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isForward)[_isForward]()) {
	    return false;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasOnlyText)[_hasOnlyText]()) {
	    return false;
	  }

	  // todo: need to sync with getSmileRatio in lib/parser/src/functions/smile.js
	  const smileManager = im_v2_lib_smileManager.SmileManager.getInstance();
	  const smiles = (_smileManager$smileLi = (_smileManager$smileLi2 = smileManager.smileList) == null ? void 0 : _smileManager$smileLi2.smiles) != null ? _smileManager$smileLi : [];
	  const sortedSmiles = [...smiles].sort((a, b) => {
	    return b.typing.localeCompare(a.typing);
	  });
	  const pattern = sortedSmiles.map(smile => {
	    return im_v2_lib_utils.Utils.text.escapeRegex(smile.typing);
	  }).join('|');
	  const replacedText = babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text.replaceAll(new RegExp(pattern, 'g'), '');
	  const hasOnlySmiles = replacedText.trim().length === 0;
	  const matchOnlySmiles = new RegExp(`(?:(?:${pattern})\\s*){4,}`);
	  return hasOnlySmiles && !matchOnlySmiles.test(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text);
	}
	function _hasOnlyText2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasText)[_hasText]()) {
	    return false;
	  }
	  return !babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasAttach)[_hasAttach]();
	}
	function _isForward2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['messages/isForward'](babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].id);
	}

	class AvatarMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    this.id = 'bx-im-avatar-context-menu';
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: true,
	      offsetLeft: 21
	    };
	  }
	  getMenuItems() {
	    return [this.getMentionItem(), this.getSendItem(), this.getProfileItem(), this.getKickItem()];
	  }
	  getMentionItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_MENTION_2'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	          mentionText: this.context.user.name,
	          mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(this.context.user.id, this.context.user.name)
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getSendItem() {
	    if (this.context.dialog.type === im_v2_const.ChatType.user) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_SEND_MESSAGE'),
	      onclick: () => {
	        im_public.Messenger.openChat(this.context.user.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getProfileItem() {
	    if (this.isBot()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_OPEN_PROFILE'),
	      href: im_v2_lib_utils.Utils.user.getProfileLink(this.context.user.id),
	      onclick: () => {
	        this.menuInstance.close();
	      }
	    };
	  }
	  getKickItem() {
	    const canKick = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.kick, this.context.dialog.dialogId);
	    if (!canKick) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_KICK'),
	      onclick: async () => {
	        this.menuInstance.close();
	        const userChoice = await im_v2_lib_confirm.showKickUserConfirm();
	        if (userChoice === true) {
	          const chatService = new im_v2_provider_service.ChatService();
	          chatService.kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
	        }
	      }
	    };
	  }
	  isUser() {
	    return this.store.getters['chats/isUser'](this.context.user.id);
	  }
	  isBot() {
	    if (!this.isUser()) {
	      return false;
	    }
	    const user = this.store.getters['users/get'](this.context.user.id);
	    return user.bot === true;
	  }
	}

	var _isOwnMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isOwnMessage");
	var _isDeletedMessage$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletedMessage");
	var _getMessageFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessageFile");
	var _isForwardedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isForwardedMessage");
	class MessageMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _isForwardedMessage, {
	      value: _isForwardedMessage2
	    });
	    Object.defineProperty(this, _getMessageFile, {
	      value: _getMessageFile2
	    });
	    Object.defineProperty(this, _isDeletedMessage$1, {
	      value: _isDeletedMessage2$1
	    });
	    Object.defineProperty(this, _isOwnMessage, {
	      value: _isOwnMessage2
	    });
	    this.id = 'bx-im-message-context-menu';
	    this.diskService = new im_v2_provider_service.DiskService();
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: true,
	      offsetLeft: 11
	    };
	  }
	  getMenuItems() {
	    return [this.getReplyItem(), this.getCopyItem(), this.getCopyFileItem(), this.getPinItem(), this.getForwardItem(), this.getDelimiter(), this.getMarkItem(), this.getFavoriteItem(), this.getDelimiter(), this.getCreateItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDisk(), this.getDelimiter(), this.getEditItem(), this.getDeleteItem()];
	  }
	  getReplyItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_REPLY'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.replyMessage, {
	          messageId: this.context.id
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getForwardItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]() || main_core.Type.isString(this.context.id)) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_FORWARD'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.showForwardPopup, {
	          messageId: this.context.id
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyItem() {
	    if (this.context.text.trim().length === 0) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY'),
	      onclick: () => {
	        var _BX$clipboard;
	        const textToCopy = im_v2_lib_parser.Parser.prepareCopy(this.context);
	        if ((_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(textToCopy)) {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_SUCCESS')
	          });
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyFileItem() {
	    if (this.context.files.length === 0) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE'),
	      onclick: () => {
	        var _BX$clipboard2;
	        const textToCopy = im_v2_lib_parser.Parser.prepareCopyFile(this.context);
	        if ((_BX$clipboard2 = BX.clipboard) != null && _BX$clipboard2.copy(textToCopy)) {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE_SUCCESS')
	          });
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getPinItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
	      return null;
	    }
	    const isPinned = this.store.getters['messages/pin/isPinned']({
	      chatId: this.context.chatId,
	      messageId: this.context.id
	    });
	    return {
	      text: isPinned ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_UNPIN') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_PIN'),
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        if (isPinned) {
	          messageService.unpinMessage(this.context.chatId, this.context.id);
	        } else {
	          messageService.pinMessage(this.context.chatId, this.context.id);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getFavoriteItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
	      return null;
	    }
	    const isInFavorite = this.store.getters['sidebar/favorites/isFavoriteMessage'](this.context.chatId, this.context.id);
	    const menuItemText = isInFavorite ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_REMOVE_FROM_SAVED') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE');
	    return {
	      text: menuItemText,
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        if (isInFavorite) {
	          messageService.removeMessageFromFavorite(this.context.id);
	        } else {
	          messageService.addMessageToFavorite(this.context.id);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getMarkItem() {
	    const canUnread = this.context.viewed && !babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]();
	    const dialog = this.store.getters['chats/getByChatId'](this.context.chatId);
	    const isMarked = this.context.id === dialog.markedId;
	    if (!canUnread || isMarked) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_MARK'),
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        messageService.markMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE'),
	      items: [this.getCreateTaskItem(), this.getCreateMeetingItem(), ...this.getMarketItems()]
	    };
	  }
	  getCreateTaskItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_TASK'),
	      onclick: () => {
	        const entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.context.chatId);
	        void entityCreator.createTaskForMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateMeetingItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_MEETING'),
	      onclick: () => {
	        const entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.context.chatId);
	        void entityCreator.createMeetingForMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getEditItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]() || babelHelpers.classPrivateFieldLooseBase(this, _isForwardedMessage)[_isForwardedMessage]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_EDIT'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.editMessage, {
	          messageId: this.context.id
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getDeleteItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DELETE'),
	      className: 'menu-popup-no-icon bx-im-dialog-chat__message-menu_delete',
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        void messageService.deleteMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getMarketItems() {
	    const {
	      dialogId,
	      id
	    } = this.context;
	    const placements = this.marketManager.getAvailablePlacementsByType(im_v2_const.PlacementType.contextMenu, dialogId);
	    const marketMenuItem = [];
	    if (placements.length > 0) {
	      marketMenuItem.push(this.getDelimiter());
	    }
	    const context = {
	      messageId: id,
	      dialogId
	    };
	    placements.forEach(placement => {
	      marketMenuItem.push({
	        text: placement.title,
	        onclick: () => {
	          im_v2_lib_market.MarketManager.openSlider(placement, context);
	          this.menuInstance.close();
	        }
	      });
	    });

	    // (10 items + 1 delimiter), because we don't want to show long context menu.
	    const itemLimit = 11;
	    return marketMenuItem.slice(0, itemLimit);
	  }
	  getDownloadFileItem() {
	    const file = babelHelpers.classPrivateFieldLooseBase(this, _getMessageFile)[_getMessageFile]();
	    if (!file) {
	      return null;
	    }
	    return {
	      html: im_v2_lib_utils.Utils.file.createDownloadLink(main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILE'), file.urlDownload, file.name),
	      onclick: function () {
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getSaveToDisk() {
	    const file = babelHelpers.classPrivateFieldLooseBase(this, _getMessageFile)[_getMessageFile]();
	    if (!file) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK'),
	      onclick: function () {
	        void this.diskService.save(file.id).then(() => {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK_SUCCESS')
	          });
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getDelimiter() {
	    return {
	      delimiter: true
	    };
	  }
	}
	function _isOwnMessage2() {
	  return this.context.authorId === im_v2_application_core.Core.getUserId();
	}
	function _isDeletedMessage2$1() {
	  return this.context.isDeleted;
	}
	function _getMessageFile2() {
	  if (this.context.files.length === 0) {
	    return null;
	  }

	  // for now, we have only one file in one message. In the future we need to change this logic.
	  return this.store.getters['files/get'](this.context.files[0]);
	}
	function _isForwardedMessage2() {
	  return main_core.Type.isStringFilled(this.context.forward.id);
	}

	// @vue/component
	const DateGroupTitle = {
	  props: {
	    title: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  template: `
		<div class="bx-im-message-list-date-group-title__container">
			<div class="bx-im-message-list-date-group-title__text">{{ title }}</div>
		</div>
	`
	};

	// @vue/component
	const DateGroup = {
	  name: 'DateGroup',
	  components: {
	    DateGroupTitle
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    BlockType: () => im_v2_const.DialogBlockType,
	    dateGroup() {
	      return this.item;
	    }
	  },
	  template: `
		<div class="bx-im-message-list-date-group__container">
			<DateGroupTitle :title="dateGroup.date.title" />
			<template v-for="dateGroupItem in dateGroup.items" >
				<slot
					name="dateGroupItem"
					:dateGroupItem="dateGroupItem"
					:isMarkedBlock="dateGroupItem.type === BlockType.markedMessages"
					:isNewMessagesBlock="dateGroupItem.type === BlockType.newMessages"
					:isAuthorBlock="dateGroupItem.type === BlockType.authorGroup"
				></slot>
			</template>
		</div>
	`
	};

	// @vue/component
	const AuthorGroup = {
	  name: 'AuthorGroup',
	  components: {
	    Avatar: im_v2_component_elements.Avatar
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['avatarClick'],
	  data() {
	    return {};
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    authorGroup() {
	      return this.item;
	    }
	  },
	  methods: {
	    onAvatarClick(event) {
	      this.$emit('avatarClick', {
	        dialogId: this.authorGroup.avatar.avatarId,
	        $event: event
	      });
	    }
	  },
	  template: `
		<div class="bx-im-message-list-author-group__container" :class="'--' + authorGroup.messageType">
			<div v-if="authorGroup.avatar.isNeeded" class="bx-im-message-list-author-group__avatar">
				<Avatar
					:dialogId="authorGroup.avatar.avatarId"
					:size="AvatarSize.L"
					@click="onAvatarClick"
				/>
			</div>
			<div class="bx-im-message-list__content">
				<template v-for="(message, index) in authorGroup.items">
					<slot name="message" :message="message" :index="index"></slot>
				</template>
			</div>
		</div>
	`
	};

	// @vue/component
	const NewMessagesBlock = {
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-list-new-message__container">
			<div class="bx-im-message-list-new-message__text">
				{{ loc('IM_DIALOG_CHAT_BLOCK_NEW_MESSAGES_2') }}
			</div>
		</div>
	`
	};

	// @vue/component
	const MarkedMessagesBlock = {
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-list-new-message__container">
			<div class="bx-im-message-list-new-message__text">
				{{ loc('IM_DIALOG_CHAT_BLOCK_MARKED_MESSAGES') }}
			</div>
		</div>
	`
	};

	const defaultMessages = [main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_1'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_2'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_3'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_4'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_5')];

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  data() {
	    return {};
	  },
	  computed: {
	    defaultMessages: () => defaultMessages
	  },
	  methods: {
	    onMessageClick(text) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	        text
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-list-empty-state__container">
			<div class="bx-im-message-list-empty-state__content">
				<div class="bx-im-message-list-empty-state__icon"></div>
				<div class="bx-im-message-list-empty-state__title">{{ loc('IM_MESSAGE_LIST_EMPTY_STATE_TITLE') }}</div>
				<div class="bx-im-message-list-empty-state__action-list">
					<div
						v-for="(message, index) in defaultMessages"
						:key="index"
						@click="onMessageClick(message)"
						class="bx-im-message-list-empty-state__action-list_item"
					>
						{{ message }}
					</div>
				</div>
			</div>
		</div>
	`
	};

	class CollectionManager {
	  constructor(dialogId) {
	    this.firstIteration = true;
	    this.cachedDateGroups = {};
	    this.store = im_v2_application_core.Core.getStore();
	    this.dialogId = dialogId;
	  }
	  formatMessageCollection(messageCollection) {
	    const dateGroups = {};
	    const collection = [];
	    let lastDateItems = null;
	    let lastAuthorId = null;
	    let lastAuthorItems = null;
	    const dialog = this.store.getters['chats/get'](this.dialogId);
	    const {
	      markedId,
	      inited
	    } = dialog;
	    let markInserted = false;
	    const lastReadId = this.store.getters['chats/getLastReadId'](this.dialogId);
	    if (this.firstIteration) {
	      this.initialLastReadMessage = lastReadId;
	      this.initialMarkedId = markedId;
	    }
	    if (markedId !== this.initialMarkedId && markedId !== 0) {
	      this.initialMarkedId = markedId;
	      this.initialLastReadMessage = null;
	    }
	    messageCollection.forEach((message, index) => {
	      const dateGroup = this.getDateGroup(message.date);
	      // new date = new date group + new author group
	      if (!dateGroups[dateGroup.title]) {
	        dateGroups[dateGroup.title] = dateGroup.id;
	        lastDateItems = [];
	        collection.push({
	          type: im_v2_const.DialogBlockType.dateGroup,
	          date: dateGroup,
	          items: lastDateItems
	        });
	        lastAuthorId = null;
	      }

	      // marked messages
	      if (message.id === this.initialMarkedId) {
	        lastDateItems.push({
	          type: im_v2_const.DialogBlockType.markedMessages
	        });
	        lastAuthorId = null;
	        markInserted = true;
	      }

	      // new author = new author group
	      if (message.authorId !== lastAuthorId) {
	        lastAuthorId = message.authorId;
	        lastAuthorItems = [];
	        lastDateItems.push({
	          type: im_v2_const.DialogBlockType.authorGroup,
	          userId: message.authorId,
	          avatar: this.getAvatarConfig(message),
	          messageType: this.getMessageType(message),
	          items: lastAuthorItems
	        });
	      }

	      // add current message to last active author group
	      lastAuthorItems.push(message);

	      // new messages block
	      const isLastMessage = index === messageCollection.length - 1;
	      if (!markInserted && !isLastMessage && message.id === this.initialLastReadMessage) {
	        lastDateItems.push({
	          type: im_v2_const.DialogBlockType.newMessages
	        });
	        lastAuthorId = null;
	      }
	    });
	    if (inited) {
	      this.firstIteration = false;
	    }
	    return collection;
	  }
	  getDateGroup(date) {
	    const INDEX_BETWEEN_DATE_AND_TIME = 10;
	    // 2022-10-25T14:58:44.000Z => 2022-10-25
	    const shortDate = date.toJSON().slice(0, INDEX_BETWEEN_DATE_AND_TIME);
	    if (this.cachedDateGroups[shortDate]) {
	      return this.cachedDateGroups[shortDate];
	    }
	    this.cachedDateGroups[shortDate] = {
	      id: shortDate,
	      title: im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.dateGroup)
	    };
	    return this.cachedDateGroups[shortDate];
	  }
	  getAvatarConfig(message) {
	    const messageType = this.getMessageType(message);
	    const isSystem = messageType === im_v2_const.MessageType.system;
	    const isSelf = messageType === im_v2_const.MessageType.self;
	    const alignment = this.store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	    let isNeeded = true;
	    if (alignment === im_v2_const.DialogAlignment.left) {
	      isNeeded = !isSystem;
	    } else if (alignment === im_v2_const.DialogAlignment.center) {
	      isNeeded = !isSelf && !isSystem;
	    }
	    return {
	      isNeeded,
	      avatarId: message.authorId.toString()
	    };
	  }
	  getMessageType(message) {
	    if (!message.authorId) {
	      return im_v2_const.MessageType.system;
	    }
	    if (message.authorId === im_v2_application_core.Core.getUserId()) {
	      return im_v2_const.MessageType.self;
	    }
	    return im_v2_const.MessageType.opponent;
	  }
	}

	const messageComponents = {
	  DefaultMessage: im_v2_component_message_default.DefaultMessage,
	  FileMessage: im_v2_component_message_file.FileMessage,
	  SmileMessage: im_v2_component_message_smile.SmileMessage,
	  CallInviteMessage: im_v2_component_message_callInvite.CallInviteMessage,
	  DeletedMessage: im_v2_component_message_deleted.DeletedMessage,
	  SystemMessage: im_v2_component_message_system.SystemMessage,
	  UnsupportedMessage: im_v2_component_message_unsupported.UnsupportedMessage,
	  ChatCreationMessage: im_v2_component_message_chatCreation.ChatCreationMessage,
	  OwnChatCreationMessage: im_v2_component_message_ownChatCreation.OwnChatCreationMessage,
	  ChatCopilotCreationMessage: im_v2_component_message_copilot_creation.ChatCopilotCreationMessage,
	  CopilotMessage: im_v2_component_message_copilot_answer.CopilotMessage,
	  SupportVoteMessage: im_v2_component_message_support_vote.SupportVoteMessage,
	  SupportSessionNumberMessage: im_v2_component_message_support_sessionNumber.SupportSessionNumberMessage,
	  ConferenceCreationMessage: im_v2_component_message_conferenceCreation.ConferenceCreationMessage,
	  ZoomInviteMessage: im_v2_component_message_zoomInvite.ZoomInviteMessage
	};

	// @vue/component
	const MessageList = {
	  name: 'MessageList',
	  directives: {
	    'message-observer': {
	      mounted(element, binding) {
	        binding.instance.observer.observeMessage(element);
	      },
	      beforeUnmount(element, binding) {
	        binding.instance.observer.unobserveMessage(element);
	      }
	    }
	  },
	  components: {
	    DateGroup,
	    AuthorGroup,
	    NewMessagesBlock,
	    MarkedMessagesBlock,
	    DialogStatus,
	    DialogLoader,
	    EmptyState,
	    ...messageComponents
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    messages: {
	      type: Array,
	      required: true
	    },
	    observer: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['showQuoteButton'],
	  data() {
	    return {
	      messageMenuIsActiveForId: 0
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    messageCollection() {
	      return this.messages;
	    },
	    formattedCollection() {
	      if (!this.dialogInited && this.messageCollection.length === 0) {
	        return [];
	      }
	      return this.getCollectionManager().formatMessageCollection(this.messageCollection);
	    },
	    noMessages() {
	      return this.formattedCollection.length === 0;
	    },
	    showDialogStatus() {
	      return this.messageCollection.some(message => {
	        return message.id === this.dialog.lastMessageId;
	      });
	    },
	    statusComponent() {
	      return DialogStatus;
	    }
	  },
	  created() {
	    this.initContextMenu();
	    this.initCollectionManager();
	  },
	  mounted() {
	    this.subscribeToEvents();
	  },
	  beforeUnmount() {
	    this.unsubscribeFromEvents();
	  },
	  methods: {
	    needToShowAvatarMenuFor(user) {
	      if (!user) {
	        return false;
	      }
	      const isCurrentUser = user.id === im_v2_application_core.Core.getUserId();
	      const isBotChat = this.isUser && this.user.bot === true;
	      return !isCurrentUser && !isBotChat;
	    },
	    subscribeToEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
	    },
	    unsubscribeFromEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
	    },
	    initCollectionManager() {
	      this.getCollectionManager();
	    },
	    initContextMenu() {
	      this.messageMenu = new MessageMenu();
	      this.messageMenu.subscribe(MessageMenu.events.onCloseMenu, () => {
	        this.messageMenuIsActiveForId = 0;
	      });
	      this.avatarMenu = new AvatarMenu();
	    },
	    onAvatarClick(params) {
	      const {
	        dialogId,
	        $event: event
	      } = params;
	      const user = this.$store.getters['users/get'](dialogId);
	      if (!this.needToShowAvatarMenuFor(user)) {
	        return;
	      }
	      if (im_v2_lib_utils.Utils.key.isAltOrOption(event)) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	          mentionText: user.name,
	          mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(user.id, user.name)
	        });
	        return;
	      }
	      this.avatarMenu.openMenu({
	        user,
	        dialog: this.dialog
	      }, event.currentTarget);
	    },
	    onMessageContextMenuClick(eventData) {
	      const {
	        message,
	        event
	      } = eventData.getData();
	      const context = {
	        dialogId: this.dialogId,
	        ...message
	      };
	      this.messageMenu.openMenu(context, event.currentTarget);
	      this.messageMenuIsActiveForId = message.id;
	    },
	    async onMessageMouseUp(message, event) {
	      await im_v2_lib_utils.Utils.browser.waitForSelectionToUpdate();
	      const selection = window.getSelection().toString().trim();
	      if (selection.length === 0 || this.isGuest) {
	        return;
	      }
	      this.$emit('showQuoteButton', message, event);
	    },
	    getMessageComponentName(message) {
	      return new MessageComponentManager(message).getName();
	    },
	    getCollectionManager() {
	      if (!this.collectionManager) {
	        this.collectionManager = new CollectionManager(this.dialogId);
	      }
	      return this.collectionManager;
	    }
	  },
	  template: `
		<div class="bx-im-message-list__container">
			<DialogLoader v-if="!dialogInited" :fullHeight="noMessages" />
			<EmptyState v-else-if="noMessages && isUser" />
			<DateGroup v-for="dateGroup in formattedCollection" :key="dateGroup.date.id" :item="dateGroup">
				<!-- Slot for every date group item -->
				<template #dateGroupItem="{ dateGroupItem, isMarkedBlock, isNewMessagesBlock, isAuthorBlock }">
					<MarkedMessagesBlock v-if="isMarkedBlock" data-id="newMessages" />
					<NewMessagesBlock v-else-if="isNewMessagesBlock" data-id="newMessages" />
					<AuthorGroup v-else-if="isAuthorBlock" :item="dateGroupItem" @avatarClick="onAvatarClick">
						<!-- Slot for every message -->
						<template #message="{ message, index }">
							<component
								v-message-observer
								:is="getMessageComponentName(message)"
								:withTitle="index === 0"
								:item="message"
								:dialogId="dialogId"
								:key="message.id"
								:menuIsActiveForId="messageMenuIsActiveForId"
								:data-viewed="message.viewed"
								@mouseup="onMessageMouseUp(message, $event)"
							>
							</component>
						</template>
					</AuthorGroup>
				</template>
			</DateGroup>
			<component :is="statusComponent" v-if="showDialogStatus" :dialogId="dialogId" />
		</div>
	`
	};

	exports.MessageList = MessageList;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Component.Elements,BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=message-list.bundle.js.map
