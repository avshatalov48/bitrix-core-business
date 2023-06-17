this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,main_core_events,ui_vue3_components_reactions,im_v2_application_core,im_v2_lib_utils,im_v2_lib_parser,im_v2_component_message_reaction,im_v2_lib_dateFormatter,im_v2_component_elements,im_v2_const) {
	'use strict';

	// @vue/component
	const Media = {
	  name: 'MediaComponent',
	  components: {
	    File: im_v2_component_elements.File,
	    Image: im_v2_component_elements.Image,
	    Audio: im_v2_component_elements.Audio,
	    Video: im_v2_component_elements.Video
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
	    FileType: () => im_v2_const.FileType,
	    message() {
	      return this.item;
	    },
	    messageFiles() {
	      const files = [];
	      if (this.message.files.length === 0) {
	        return files;
	      }
	      this.message.files.forEach(fileId => {
	        const file = this.$store.getters['files/get'](fileId, true);
	        files.push(file);
	      });
	      return files;
	    },
	    messageType() {
	      return this.$store.getters['messages/getMessageType'](this.message.id);
	    }
	  },
	  template: `
		<div v-for="file in messageFiles" :key="file.id" class="bx-im-message-base__media-wrap">
			<Image v-if="file.type === FileType.image" :item="file" />
			<Audio v-else-if="file.type === FileType.audio" :item="file" :messageType="messageType" />
			<Video v-else-if="file.type === FileType.video" :item="file" />
			<File v-else :item="file" />
		</div>
	`
	};

	// @vue/component
	const OwnMessageStatus = {
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
	    message() {
	      return this.item;
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
	  template: `
		<div :class="'--' + messageStatus" class="bx-im-message-base__message-status"></div>
	`
	};

	// @vue/component
	const DeletedMessage = {
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-base__deleted_container">
			<div class="bx-im-message-base__deleted_icon"></div>
			<div class="bx-im-message-base__deleted_text">{{ loc('IM_MESSENGER_MESSAGE_DELETED') }}</div>
		</div>
	`
	};

	// @vue/component
	const BaseMessage = {
	  name: 'BaseMessage',
	  components: {
	    Attach: im_v2_component_elements.Attach,
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    Reactions: ui_vue3_components_reactions.Reactions,
	    Media,
	    OwnMessageStatus,
	    DeletedMessage,
	    ReactionSelector: im_v2_component_message_reaction.ReactionSelector,
	    ReactionList: im_v2_component_message_reaction.ReactionList
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    withAvatar: {
	      type: Boolean,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: true
	    },
	    menuIsActiveForId: {
	      type: Number,
	      default: 0
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['contextMenuClick', 'quoteMessage'],
	  data() {
	    return {};
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    message() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.message.authorId, true);
	    },
	    dialogColor() {
	      return this.dialog.type !== im_v2_const.DialogType.private ? this.dialog.color : this.user.color;
	    },
	    authorDialogId() {
	      if (this.message.authorId) {
	        return this.message.authorId.toString();
	      }
	      return this.dialogId;
	    },
	    isSystemMessage() {
	      return this.message.authorId === 0;
	    },
	    isSelfMessage() {
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    isOpponentMessage() {
	      return !this.isSystemMessage && !this.isSelfMessage;
	    },
	    showTitle() {
	      return this.withTitle && !this.isSystemMessage && !this.isSelfMessage;
	    },
	    canSetReactions() {
	      return main_core.Type.isNumber(this.message.id);
	    },
	    containerClasses() {
	      return {
	        '--system': this.isSystemMessage,
	        '--self': this.isSelfMessage,
	        '--opponent': this.isOpponentMessage,
	        '--with-avatar': this.withAvatar
	      };
	    },
	    formattedText() {
	      return im_v2_lib_parser.Parser.decodeMessage(this.message);
	    },
	    formattedDate() {
	      return im_v2_lib_dateFormatter.DateFormatter.formatByCode(this.message.date, im_v2_lib_dateFormatter.DateCode.shortTimeFormat);
	    },
	    menuTitle() {
	      return this.loc('IM_MESSENGER_MESSAGE_MENU_TITLE', {
	        '#SHORTCUT#': im_v2_lib_utils.Utils.platform.isMac() ? 'CMD' : 'CTRL'
	      });
	    }
	  },
	  methods: {
	    setReaction(message, reaction) {
	      console.warn('setReaction', message, reaction);
	    },
	    openReactionList(message, values) {
	      console.warn('openReactionList', message, values);
	    },
	    onMenuClick(event) {
	      if (im_v2_lib_utils.Utils.key.isCmdOrCtrl(event)) {
	        this.$emit('quoteMessage', {
	          message: this.message
	        });
	        return;
	      }
	      this.$emit('contextMenuClick', {
	        message: this.message,
	        $event: event
	      });
	    },
	    onContainerClick(event) {
	      im_v2_lib_parser.Parser.executeClickEvent(event);
	    },
	    onAuthorNameClick() {
	      const authorId = Number.parseInt(this.authorDialogId, 10);
	      if (authorId === im_v2_application_core.Core.getUserId()) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	        mentionText: this.user.name,
	        mentionReplacement: im_v2_lib_utils.Utils.user.getMentionBbCode(this.user.id, this.user.name)
	      });
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div :class="containerClasses" :data-id="message.id" class="bx-im-message-base__scope bx-im-message-base__container" @click="onContainerClick">
			<div class="bx-im-message-base__body">
				<div @click="onAuthorNameClick" v-if="showTitle" class="bx-im-message-base__name">
					<ChatTitle :dialogId="authorDialogId" :onlyFirstName="true" :showItsYou="false" :withColor="true" :withLeftIcon="false" />
				</div>
				<Media :item="message" />
				<DeletedMessage v-if="message.isDeleted" />
				<div v-else class="bx-im-message-base__text" v-html="formattedText"></div>
				<div v-for="config in message.attach" :key="config.ID" class="bx-im-message-base__attach-wrap">
					<Attach :baseColor="dialogColor" :config="config"/>
				</div>
				
				<div class="bx-im-message-base__bottom-container">
					<ReactionList v-if="canSetReactions" :messageId="message.id" />
					<div class="bx-im-message-base__bottom-container_right">
						<div v-if="message.isEdited && !message.isDeleted" class="bx-im-message-base__edit-mark">
							{{ loc('IM_MESSENGER_MESSAGE_EDITED') }}
						</div>
						<div class="bx-im-message-base__date">{{ formattedDate }}</div>
						<OwnMessageStatus v-if="isSelfMessage" :item="message" />	
					</div>
				</div>
				<div class="bx-im-message-base__reactions-container">
					<ReactionSelector v-if="canSetReactions" :messageId="message.id" />
				</div>
			</div>
			<div class="bx-im-message-base__actions">
				<div :title="menuTitle" @click="onMenuClick" :class="{'--active': menuIsActiveForId === message.id}" class="bx-im-message-base__menu"></div>
			</div>
		</div>
	`
	};

	exports.BaseMessage = BaseMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX,BX.Event,BX.Vue3.Components,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Im.V2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const));
//# sourceMappingURL=base-message.bundle.js.map
