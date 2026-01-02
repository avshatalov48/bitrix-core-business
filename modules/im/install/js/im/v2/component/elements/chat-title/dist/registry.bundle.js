/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,ui_iconSet_api_vue,im_v2_application_core,ui_vue3,im_v2_lib_textHighlighter,im_v2_lib_copilot,main_core_events,im_v2_const,im_v2_lib_escManager,im_v2_lib_permission) {
	'use strict';

	const DialogSpecialType = {
	  bot: 'bot',
	  extranet: 'extranet',
	  network: 'network',
	  collaber: 'collaber',
	  support24: 'support24'
	};
	const TitleIcons = {
	  absent: 'absent',
	  birthday: 'birthday'
	};
	const ChatTitleType = {
	  notes: 'notes'
	};

	const ICON_SIZE = 18;
	const ChatTitle = {
	  name: 'ChatTitle',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    dialogId: {
	      type: [Number, String],
	      default: 0
	    },
	    text: {
	      type: String,
	      default: ''
	    },
	    showItsYou: {
	      type: Boolean,
	      default: true
	    },
	    withLeftIcon: {
	      type: Boolean,
	      default: true
	    },
	    withColor: {
	      type: Boolean,
	      default: false
	    },
	    withMute: {
	      type: Boolean,
	      default: false
	    },
	    withAutoDelete: {
	      type: Boolean,
	      default: false
	    },
	    onlyFirstName: {
	      type: Boolean,
	      default: false
	    },
	    twoLine: {
	      type: Boolean,
	      default: false
	    },
	    customType: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    Color: () => im_v2_const.Color,
	    ICON_SIZE: () => ICON_SIZE,
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    chatId() {
	      return this.dialog.chatId;
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    botType() {
	      const EXCLUDED_BOT_CODES = [im_v2_const.BotCode.aiAssistant];
	      if (!this.isUser) {
	        return '';
	      }
	      const bot = this.$store.getters['users/bots/getByUserId'](this.dialogId);
	      if (!bot || EXCLUDED_BOT_CODES.includes(bot.code)) {
	        return '';
	      }
	      return bot.type;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isSelfChat() {
	      return this.isUser && this.user.id === im_v2_application_core.Core.getUserId();
	    },
	    containerClasses() {
	      const classes = [];
	      if (this.twoLine) {
	        classes.push('--twoline');
	      }
	      return classes;
	    },
	    dialogName() {
	      if (this.customType === ChatTitleType.notes) {
	        return this.loc('IM_CHAT_TITLE_MY_NOTES');
	      }
	      if (this.text) {
	        return main_core.Text.encode(this.text);
	      }
	      let resultText = this.dialog.name;
	      if (this.isUser) {
	        resultText = this.onlyFirstName ? this.user.firstName : this.user.name;
	      }
	      return main_core.Text.encode(resultText);
	    },
	    dialogSpecialType() {
	      if (!this.isUser) {
	        if (this.isCollabChat) {
	          return '';
	        }
	        if (this.isExtranet) {
	          return DialogSpecialType.extranet;
	        }
	        if (this.isCollaberChatOrUser) {
	          return DialogSpecialType.collaber;
	        }
	        if ([im_v2_const.ChatType.support24Notifier, im_v2_const.ChatType.support24Question].includes(this.dialog.type)) {
	          return DialogSpecialType.support24;
	        }
	        return '';
	      }
	      if (this.isSelfChat) {
	        return '';
	      }
	      if (this.isBot) {
	        return this.botType;
	      }
	      if (this.isExtranet) {
	        return DialogSpecialType.extranet;
	      }
	      if (this.isCollaberChatOrUser) {
	        return DialogSpecialType.collaber;
	      }
	      if (this.isNetwork) {
	        return DialogSpecialType.network;
	      }
	      return '';
	    },
	    isDialogSpecialTypeWithLeftIcon() {
	      if (this.isCollaberChatOrUser || this.isExtranet) {
	        return false;
	      }
	      return main_core.Type.isStringFilled(this.dialogSpecialType);
	    },
	    leftIcon() {
	      if (!this.withLeftIcon || this.isSelfChat) {
	        return '';
	      }
	      if (this.isDialogSpecialTypeWithLeftIcon) {
	        return this.dialogSpecialType;
	      }
	      if (!this.isUser) {
	        return '';
	      }
	      if (this.showBirthdays && this.user.isBirthday) {
	        return TitleIcons.birthday;
	      }
	      if (this.user.isAbsent) {
	        return TitleIcons.absent;
	      }
	      return '';
	    },
	    color() {
	      if (!this.withColor || this.specialColor) {
	        return '';
	      }
	      return this.dialog.color;
	    },
	    specialColor() {
	      return this.dialogSpecialType;
	    },
	    isBot() {
	      if (!this.isUser) {
	        return false;
	      }
	      return this.user.type === im_v2_const.UserType.bot;
	    },
	    isExtranet() {
	      if (this.isUser) {
	        return this.user.type === im_v2_const.UserType.extranet;
	      }
	      return this.dialog.extranet;
	    },
	    isCollaberChatOrUser() {
	      if (this.isUser) {
	        return this.user.type === im_v2_const.UserType.collaber;
	      }
	      return this.dialog.containsCollaber;
	    },
	    isCollabChat() {
	      return this.dialog.type === im_v2_const.ChatType.collab;
	    },
	    isNetwork() {
	      if (this.isUser) {
	        return this.user.network;
	      }
	      return false;
	    },
	    isChatMuted() {
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === im_v2_application_core.Core.getUserId();
	      });
	      return Boolean(isMuted);
	    },
	    isAutoDeleteEnabled() {
	      if (!this.withAutoDelete) {
	        return false;
	      }
	      return this.$store.getters['chats/autoDelete/isEnabled'](this.chatId);
	    },
	    tooltipText() {
	      if (this.customType === ChatTitleType.notes) {
	        return this.loc('IM_CHAT_TITLE_MY_NOTES');
	      }
	      if (this.isSelfChat && this.showItsYou) {
	        return `${this.dialog.name} (${this.loc('IM_CHAT_TITLE_SELF')})`;
	      }
	      return this.dialog.name;
	    },
	    showBirthdays() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showBirthday);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div :class="containerClasses" class="bx-im-chat-title__scope bx-im-chat-title__container">
			<span class="bx-im-chat-title__content">
				<span v-if="leftIcon" :class="'--' + leftIcon" class="bx-im-chat-title__icon"></span>
				<span
					:class="[specialColor ? '--' + specialColor : '']"
					:style="{color: color}"
					:title="tooltipText"
					class="bx-im-chat-title__text"
					v-html="dialogName"
				></span>
				<strong v-if="isSelfChat && showItsYou">
					<span class="bx-im-chat-title__text --self">({{ loc('IM_CHAT_TITLE_SELF') }})</span>
				</strong>
				<span v-if="withMute && isChatMuted" class="bx-im-chat-title__muted-icon"></span>
				<BIcon
					v-if="isAutoDeleteEnabled"
					:name="OutlineIcons.TIMER_DOT"
					:color="Color.accentBlue"
					:size="ICON_SIZE"
					:hoverable="false"
					:title="loc('IM_CHAT_TITLE_AUTO_DELETE_TITLE')"
					class="bx-im-chat-title__auto-delete-icon"
				/>
			</span>
		</div>
	`
	};

	// @vue/component
	const ChatTitleWithHighlighting$$1 = ui_vue3.BitrixVue.cloneComponent(ChatTitle, {
	  name: 'ChatTitleWithHighlighting',
	  props: {
	    textToHighlight: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    dialogName() {
	      // noinspection JSUnresolvedVariable
	      return im_v2_lib_textHighlighter.highlightText(this.parentDialogName, this.textToHighlight);
	    }
	  }
	});

	// @vue/component
	const MessageAuthorTitle$$1 = {
	  name: 'MessageAuthorTitle',
	  components: {
	    ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: [Number, String],
	      default: 0
	    },
	    messageId: {
	      type: [Number, String],
	      default: 0
	    },
	    text: {
	      type: String,
	      default: ''
	    },
	    showItsYou: {
	      type: Boolean,
	      default: true
	    },
	    withLeftIcon: {
	      type: Boolean,
	      default: true
	    },
	    withColor: {
	      type: Boolean,
	      default: false
	    },
	    withMute: {
	      type: Boolean,
	      default: false
	    },
	    onlyFirstName: {
	      type: Boolean,
	      default: false
	    },
	    twoLine: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    message() {
	      return this.$store.getters['messages/getById'](this.messageId);
	    },
	    authorId() {
	      return this.message.authorId;
	    },
	    customAuthorName() {
	      const copilotManager = new im_v2_lib_copilot.CopilotManager();
	      if (!copilotManager.isCopilotBot(this.dialogId)) {
	        return '';
	      }
	      return copilotManager.getNameWithRole({
	        dialogId: this.dialogId,
	        messageId: this.messageId
	      });
	    }
	  },
	  template: `
		<ChatTitle 
			:dialogId="dialogId"
			:text="customAuthorName"
			:showItsYou="showItsYou"
			:withLeftIcon="withLeftIcon"
			:withColor="withColor"
			:withMute="withMute"
			:onlyFirstName="onlyFirstName"
			:twoLine="twoLine"
		/>
	`
	};

	const INPUT_PADDING = 5;

	// @vue/component
	const EditableChatTitle$$1 = {
	  name: 'EditableChatTitle',
	  components: {
	    ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['newTitleSubmit'],
	  data() {
	    return {
	      isEditing: false,
	      inputWidth: 0,
	      showEditIcon: false,
	      chatTitle: ''
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    canBeRenamed() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.rename, this.dialogId);
	    },
	    inputStyle() {
	      return {
	        width: `calc(${this.inputWidth}ch + ${INPUT_PADDING}px)`
	      };
	    }
	  },
	  watch: {
	    chatTitle() {
	      this.inputWidth = this.chatTitle.length;
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.key.onBeforeEscape, this.onBeforeEscape);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.key.onBeforeEscape, this.onBeforeEscape);
	  },
	  mounted() {
	    this.chatTitle = this.dialog.name;
	  },
	  methods: {
	    async onTitleClick() {
	      if (!this.canBeRenamed) {
	        return;
	      }
	      this.chatTitle = this.dialog.name;
	      this.isEditing = true;
	      await this.$nextTick();
	      this.$refs.titleInput.focus();
	    },
	    onNewTitleSubmit() {
	      if (!this.isEditing) {
	        return;
	      }
	      this.isEditing = false;
	      const nameNotChanged = this.chatTitle === this.dialog.name;
	      if (nameNotChanged || this.chatTitle === '') {
	        return;
	      }
	      this.$emit('newTitleSubmit', this.chatTitle);
	    },
	    onBeforeEscape() {
	      if (!this.isEditing) {
	        return im_v2_lib_escManager.EscEventAction.ignored;
	      }
	      this.isEditing = false;
	      this.showEditIcon = false;
	      this.chatTitle = this.dialog.name;
	      return im_v2_lib_escManager.EscEventAction.handled;
	    }
	  },
	  template: `
		<div
			v-if="!isEditing"
			@click="onTitleClick"
			@mouseover="showEditIcon = true"
			@mouseleave="showEditIcon = false"
			class="bx-im-elements-editable-chat-title__wrap"
			:class="{'--can-rename': canBeRenamed}"
		>
			<div class="bx-im-elements-editable-chat-title__container">
				<ChatTitle :dialogId="dialogId" :withMute="true" :withAutoDelete="true" />
			</div>
			<div class="bx-im-elements-editable-chat-title__edit-icon_container">
				<div v-if="showEditIcon && canBeRenamed" class="bx-im-elements-editable-chat-title__edit-icon"></div>
			</div>
		</div>
		<div v-else class="bx-im-elements-editable-chat-title__input_container">
			<input
				v-model="chatTitle"
				:style="inputStyle"
				@focus="$event.target.select()"
				@blur="onNewTitleSubmit"
				@keyup.enter="onNewTitleSubmit"
				type="text"
				class="bx-im-elements-editable-chat-title__input"
				ref="titleInput"
			/>
		</div>
	`
	};

	exports.ChatTitle = ChatTitle;
	exports.ChatTitleType = ChatTitleType;
	exports.ChatTitleWithHighlighting = ChatTitleWithHighlighting$$1;
	exports.MessageAuthorTitle = MessageAuthorTitle$$1;
	exports.EditableChatTitle = EditableChatTitle$$1;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX.UI.IconSet,BX.Messenger.v2.Application,BX.Vue3,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
