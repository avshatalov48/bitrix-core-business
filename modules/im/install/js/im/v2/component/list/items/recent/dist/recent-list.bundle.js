/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_provider_service_recent,im_v2_lib_menu,im_v2_lib_draft,im_v2_component_elements_listLoadingState,im_v2_component_list_items_elements_inputActionIndicator,im_v2_lib_dateFormatter,im_v2_lib_channel,main_date,im_v2_lib_parser,call_application_conference,im_v2_component_elements_chatTitle,im_v2_lib_call,call_lib_analytics,ui_iconSet_api_vue,im_public,im_v2_lib_createChat,im_v2_component_elements_avatar,im_v2_component_elements_button,im_v2_lib_feature,im_v2_lib_invite,im_v2_component_list_items_elements_emptyState,main_core,im_v2_lib_utils,main_core_events,im_v2_application_core,im_v2_const) {
	'use strict';

	const HiddenTitleByChatType = {
	  [im_v2_const.ChatType.openChannel]: main_core.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_OPEN_CHANNEL'),
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_PRIVATE_CHANNEL'),
	  [im_v2_const.ChatType.generalChannel]: main_core.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_OPEN_CHANNEL'),
	  default: main_core.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2')
	};

	// @vue/component
	const MessageText = {
	  name: 'MessageText',
	  components: {
	    MessageAvatar: im_v2_component_elements_avatar.MessageAvatar
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    recentItem() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.recentItem.dialogId, true);
	    },
	    message() {
	      return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
	    },
	    needsBirthdayPlaceholder() {
	      return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
	    },
	    needsVacationPlaceholder() {
	      return this.$store.getters['recent/needsVacationPlaceholder'](this.recentItem.dialogId);
	    },
	    showLastMessage() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showLastMessage);
	    },
	    notesText() {
	      return this.loc('IM_LIST_RECENT_CHAT_SELF_SUBTITLE');
	    },
	    hiddenMessageText() {
	      var _HiddenTitleByChatTyp;
	      if (this.isNotes) {
	        return this.notesText;
	      }
	      if (this.isUser) {
	        return this.$store.getters['users/getPosition'](this.recentItem.dialogId);
	      }
	      return (_HiddenTitleByChatTyp = HiddenTitleByChatType[this.dialog.type]) != null ? _HiddenTitleByChatTyp : HiddenTitleByChatType.default;
	    },
	    isLastMessageAuthor() {
	      return this.showLastMessage && this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    messageText() {
	      if (this.message.isDeleted) {
	        return this.loc('IM_LIST_RECENT_DELETED_MESSAGE');
	      }
	      const formattedText = im_v2_lib_parser.Parser.purifyRecent(this.recentItem);
	      if (!this.showLastMessage || !formattedText) {
	        return this.hiddenMessageText;
	      }
	      return formattedText;
	    },
	    formattedMessageText() {
	      const SPLIT_INDEX = 27;
	      return im_v2_lib_utils.Utils.text.insertUnseenWhitespace(this.messageText, SPLIT_INDEX);
	    },
	    preparedDraftContent() {
	      const phrase = this.loc('IM_LIST_RECENT_MESSAGE_DRAFT_2');
	      const PLACEHOLDER_LENGTH = '#TEXT#'.length;
	      const prefix = phrase.slice(0, -PLACEHOLDER_LENGTH);
	      const text = main_core.Text.encode(this.formattedDraftText);
	      return `
				<span class="bx-im-list-recent-item__message_draft-prefix">${prefix}</span>
				<span class="bx-im-list-recent-item__message_text_content">${text}</span>
			`;
	    },
	    formattedDraftText() {
	      return im_v2_lib_parser.Parser.purify({
	        text: this.recentItem.draft.text,
	        showIconIfEmptyText: false
	      });
	    },
	    formattedVacationEndDate() {
	      return main_date.DateTimeFormat.format('d.m.Y', this.user.absent);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isNotes() {
	      return this.$store.getters['chats/isNotes'](this.recentItem.dialogId);
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-list-recent-item__message_container">
			<span class="bx-im-list-recent-item__message_text">
				<span v-if="recentItem.draft.text" v-html="preparedDraftContent"></span>
				<span v-else-if="recentItem.invitation.isActive" class="bx-im-list-recent-item__balloon_container --invitation">
					<span class="bx-im-list-recent-item__balloon">{{ loc('IM_LIST_RECENT_INVITATION_NOT_ACCEPTED_MSGVER_1') }}</span>
				</span>
				<span v-else-if="needsBirthdayPlaceholder" class="bx-im-list-recent-item__balloon_container --birthday" :title="loc('IM_LIST_RECENT_BIRTHDAY')">
					<span class="bx-im-list-recent-item__balloon">{{ loc('IM_LIST_RECENT_BIRTHDAY') }}</span>
				</span>
				<span v-else-if="needsVacationPlaceholder" class="bx-im-list-recent-item__balloon_container --vacation">
					<span class="bx-im-list-recent-item__balloon">
						{{ loc('IM_LIST_RECENT_VACATION', {'#VACATION_END_DATE#': formattedVacationEndDate}) }}
					</span>
				</span>
				<template v-else>
					<span v-if="isLastMessageAuthor" class="bx-im-list-recent-item__self_author-icon"></span>
					<MessageAvatar
						v-else-if="isChat && message.authorId"
						:messageId="message.id"
						:authorId="message.authorId"
						:size="AvatarSize.XXS"
						class="bx-im-list-recent-item__author-avatar"
					/>
					<span>{{ formattedMessageText }}</span>
				</template>
			</span>
		</div>
	`
	};

	// @vue/component
	const ItemCounters = {
	  name: 'ItemCounters',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    isChatMuted: {
	      type: Boolean,
	      required: true
	    }
	  },
	  computed: {
	    recentItem() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.recentItem.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isSelfChat() {
	      return this.isUser && this.user.id === im_v2_application_core.Core.getUserId();
	    },
	    invitation() {
	      return this.recentItem.invitation;
	    },
	    totalCounter() {
	      return this.dialog.counter + this.channelCommentsCounter;
	    },
	    channelCommentsCounter() {
	      return this.$store.getters['counters/getChannelCommentsCounter'](this.dialog.chatId);
	    },
	    formattedCounter() {
	      return this.formatCounter(this.totalCounter);
	    },
	    showCounterContainer() {
	      return !this.invitation.isActive;
	    },
	    showPinnedIcon() {
	      const noCounters = this.totalCounter === 0;
	      return this.recentItem.pinned && noCounters && !this.recentItem.unread;
	    },
	    showUnreadWithoutCounter() {
	      return this.recentItem.unread && this.totalCounter === 0;
	    },
	    showUnreadWithCounter() {
	      return this.recentItem.unread && this.totalCounter > 0;
	    },
	    showMention() {
	      return this.$store.getters['messages/anchors/isChatHasAnchorsWithType'](this.dialog.chatId, im_v2_const.AnchorType.mention) && !this.isSelfChat;
	    },
	    showCounter() {
	      const isSingleMessageWithMention = this.showMention && this.totalCounter === 1;
	      return !isSingleMessageWithMention && !this.recentItem.unread && this.totalCounter > 0 && !this.isSelfChat;
	    },
	    containerClasses() {
	      const commentsOnly = this.dialog.counter === 0 && this.channelCommentsCounter > 0;
	      const withComments = this.dialog.counter > 0 && this.channelCommentsCounter > 0;
	      const withMentionAndCounter = this.dialog.counter > 0 && this.showMention;
	      return {
	        '--muted': this.isChatMuted,
	        '--comments-only': commentsOnly,
	        '--with-comments': withComments,
	        '--with-mention-and-counter': withMentionAndCounter
	      };
	    }
	  },
	  methods: {
	    formatCounter(counter) {
	      return counter > 99 ? '99+' : counter.toString();
	    }
	  },
	  template: `
		<div v-if="showCounterContainer" :class="containerClasses" class="bx-im-list-recent-item__counters_wrap">
			<div class="bx-im-list-recent-item__counters_container">
				<div v-if="showPinnedIcon" class="bx-im-list-recent-item__pinned-icon"></div>
				<div v-else class="bx-im-list-recent-item__counters">
					<div v-if="showMention" class="bx-im-list-recent-item__mention">
						<div class="bx-im-list-recent-item__mention-icon"></div>
					</div>
					<div v-if="showUnreadWithoutCounter" class="bx-im-list-recent-item__counter_number --no-counter"></div>
					<div v-else-if="showUnreadWithCounter" class="bx-im-list-recent-item__counter_number --with-unread">
						{{ formattedCounter }}
					</div>
					<div v-else-if="showCounter" class="bx-im-list-recent-item__counter_number">
						{{ formattedCounter }}
					</div>
				</div>
			</div>
		</div>
	`
	};

	const StatusIcon = {
	  none: '',
	  like: 'like',
	  sending: 'sending',
	  sent: 'sent',
	  viewed: 'viewed'
	};

	// @vue/component
	const MessageStatus = {
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
	    recentItem() {
	      return this.item;
	    },
	    user() {
	      return this.$store.getters['users/get'](this.recentItem.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
	    },
	    message() {
	      return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
	    },
	    isChatWithReactions() {
	      return this.$store.getters['messages/anchors/isChatHasAnchorsWithType'](this.dialog.chatId, im_v2_const.AnchorType.reaction);
	    },
	    showLike() {
	      /*
	      * 'this.recent Item.liked' is left to allow work without anchors
	      * */
	      return this.isChatWithReactions || this.recentItem.liked;
	    },
	    messageStatus() {
	      if (this.message.sending) {
	        return im_v2_const.OwnMessageStatus.sending;
	      }
	      if (this.message.viewedByOthers) {
	        return im_v2_const.OwnMessageStatus.viewed;
	      }
	      return im_v2_const.OwnMessageStatus.sent;
	    },
	    statusIcon() {
	      if (this.isSelfChat || this.isBot) {
	        return StatusIcon.none;
	      }
	      if (this.showLike) {
	        return StatusIcon.like;
	      }
	      if (!this.isLastMessageAuthor || this.needsBirthdayPlaceholder || this.hasDraft) {
	        return StatusIcon.none;
	      }
	      return this.messageStatus;
	    },
	    isLastMessageAuthor() {
	      var _this$message;
	      return ((_this$message = this.message) == null ? void 0 : _this$message.authorId) === im_v2_application_core.Core.getUserId();
	    },
	    isSelfChat() {
	      return this.isUser && this.user.id === im_v2_application_core.Core.getUserId();
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isBot() {
	      if (this.isUser) {
	        return this.user.type === im_v2_const.UserType.bot;
	      }
	      return false;
	    },
	    hasDraft() {
	      return Boolean(this.recentItem.draft.text);
	    },
	    needsBirthdayPlaceholder() {
	      if (!this.isUser) {
	        return false;
	      }
	      return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
	    }
	  },
	  template: `
		<div class="bx-im-list-recent-item__status-icon" :class="'--' + statusIcon"></div>
	`
	};

	// @vue/component
	const RecentItem = {
	  name: 'RecentItem',
	  components: {
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar,
	    ChatTitle: im_v2_component_elements_chatTitle.ChatTitle,
	    MessageText,
	    MessageStatus,
	    ItemCounters,
	    InputActionIndicator: im_v2_component_list_items_elements_inputActionIndicator.InputActionIndicator
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    recentItem() {
	      return this.item;
	    },
	    formattedDate() {
	      if (this.needsBirthdayPlaceholder) {
	        return this.loc('IM_LIST_RECENT_BIRTHDAY_DATE');
	      }
	      return this.formatDate(this.itemDate);
	    },
	    formattedCounter() {
	      return this.dialog.counter > 99 ? '99+' : this.dialog.counter.toString();
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
	    },
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    message() {
	      return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
	    },
	    itemDate() {
	      return this.$store.getters['recent/getSortDate'](this.recentItem.dialogId);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isChannel() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.recentItem.dialogId);
	    },
	    isNotes() {
	      return this.$store.getters['chats/isNotes'](this.recentItem.dialogId);
	    },
	    avatarType() {
	      return this.isNotes ? im_v2_component_elements_avatar.ChatAvatarType.notes : '';
	    },
	    chatType() {
	      return this.isNotes ? im_v2_component_elements_chatTitle.ChatTitleType.notes : '';
	    },
	    isChatSelected() {
	      const canBeSelected = [im_v2_const.Layout.chat, im_v2_const.Layout.updateChat, im_v2_const.Layout.collab, im_v2_const.Layout.copilot, im_v2_const.Layout.taskComments];
	      if (!canBeSelected.includes(this.layout.name)) {
	        return false;
	      }
	      return this.layout.entityId === this.recentItem.dialogId;
	    },
	    isChatMuted() {
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === im_v2_application_core.Core.getUserId();
	      });
	      return Boolean(isMuted);
	    },
	    hasActiveInputAction() {
	      return this.$store.getters['chats/inputActions/isChatActive'](this.recentItem.dialogId);
	    },
	    needsBirthdayPlaceholder() {
	      return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
	    },
	    showLastMessage() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showLastMessage);
	    },
	    invitation() {
	      return this.recentItem.invitation;
	    },
	    wrapClasses() {
	      return {
	        '--pinned': this.recentItem.pinned,
	        '--selected': this.isChatSelected
	      };
	    },
	    itemClasses() {
	      return {
	        '--no-text': !this.showLastMessage
	      };
	    }
	  },
	  methods: {
	    formatDate(date) {
	      return im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.recent);
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-recent-item__wrap">
			<div :class="itemClasses" class="bx-im-list-recent-item__container">
				<div class="bx-im-list-recent-item__avatar_container">
					<div v-if="invitation.isActive" class="bx-im-list-recent-item__avatar_invitation"></div>
					<div v-else class="bx-im-list-recent-item__avatar_content">
						<ChatAvatar 
							:avatarDialogId="recentItem.dialogId" 
							:contextDialogId="recentItem.dialogId" 
							:size="AvatarSize.XL" 
							:withSpecialTypeIcon="!hasActiveInputAction"
							:customType="avatarType"
						/>
						<InputActionIndicator v-if="hasActiveInputAction" />
					</div>
				</div>
				<div class="bx-im-list-recent-item__content_container">
					<div class="bx-im-list-recent-item__content_header">
						<ChatTitle 
							:dialogId="recentItem.dialogId" 
							:withMute="true" 
							:withAutoDelete="true"
							:customType="chatType"
							:showItsYou="false"
						/>
						<div class="bx-im-list-recent-item__date">
							<MessageStatus :item="item" />
							<span>{{ formattedDate }}</span>
						</div>
					</div>
					<div class="bx-im-list-recent-item__content_bottom">
						<MessageText :item="recentItem" />
						<ItemCounters :item="recentItem" :isChatMuted="isChatMuted" />
					</div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const ActiveCall = {
	  name: 'ActiveCall',
	  components: {
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar,
	    ChatTitle: im_v2_component_elements_chatTitle.ChatTitle,
	    ChatButton: im_v2_component_elements_button.ChatButton
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['click'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    ButtonColor: () => im_v2_component_elements_button.ButtonColor,
	    ButtonIcon: () => im_v2_component_elements_button.ButtonIcon,
	    activeCall() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.activeCall.dialogId, true);
	    },
	    isConference() {
	      return this.dialog.type === im_v2_const.ChatType.videoconf;
	    },
	    preparedName() {
	      return main_core.Text.decode(this.activeCall.name);
	    },
	    anotherDeviceColorScheme() {
	      return {
	        backgroundColor: 'transparent',
	        borderColor: '#bbde4d',
	        iconColor: '#525c69',
	        textColor: '#525c69',
	        hoverColor: 'transparent'
	      };
	    },
	    isTabWithActiveCall() {
	      return this.$store.getters['recent/calls/hasActiveCall']() && Boolean(this.getCallManager().hasCurrentCall());
	    },
	    hasJoined() {
	      return this.activeCall.state === im_v2_const.RecentCallStatus.joined;
	    }
	  },
	  methods: {
	    async onJoinClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.call.onJoinFromRecentItem);
	      if (this.isConference) {
	        call_lib_analytics.Analytics.getInstance().onJoinConferenceClick({
	          callId: this.activeCall.call.id
	        });
	        const hasThisActiveConference = await call_application_conference.ConferenceChannel.getInstance().sendRequest(this.dialog.public.code);
	        if (hasThisActiveConference.some(call => call)) {
	          return;
	        }
	        im_public.Messenger.openConference({
	          code: this.dialog.public.code
	        });
	        return;
	      }
	      const hasThisActiveCall = await this.getCallManager().sendBroadcastRequest(this.activeCall.call.uuid);
	      if (hasThisActiveCall.some(call => call)) {
	        return;
	      }
	      this.getCallManager().joinCall(this.activeCall.call.id, this.activeCall.call.uuid, this.activeCall.dialogId);
	    },
	    onBackToCallClick() {
	      if (this.isConference) {
	        call_application_conference.ConferenceChannel.getInstance().sendRequest(this.dialog.public.code);
	        return;
	      }
	      this.getCallManager().sendBroadcastRequest(this.activeCall.call.uuid);
	    },
	    onLeaveCallClick() {
	      this.getCallManager().leaveCurrentCall();
	    },
	    onClick(event) {
	      const recentItem = this.$store.getters['recent/get'](this.activeCall.dialogId);
	      if (!recentItem) {
	        return;
	      }
	      this.$emit('click', {
	        item: recentItem,
	        $event: event
	      });
	    },
	    returnToCall() {
	      if (this.activeCall.state !== im_v2_const.RecentCallStatus.joined) {
	        return;
	      }
	      this.getCallManager().unfoldCurrentCall();
	    },
	    getCallManager() {
	      return im_v2_lib_call.CallManager.getInstance();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div :data-id="activeCall.dialogId" class="bx-im-list-recent-item__wrap bx-im-list-recent-active-call-item__wrap">
			<div @click="onClick" class="bx-im-list-recent-item__container bx-im-list-recent-active-call__container">
				<div class="bx-im-list-recent-item__avatar_container">
					<ChatAvatar 
						:avatarDialogId="activeCall.dialogId" 
						:contextDialogId="activeCall.dialogId" 
						:size="AvatarSize.XL" 
					/>
				</div>
				<div class="bx-im-list-recent-item__content_container">
					<div class="bx-im-list-recent-active-call__title_container">
						<ChatTitle :text="preparedName" />
						<div class="bx-im-list-recent-active-call__title_icon"></div>
					</div>
					<div v-if="!hasJoined" class="bx-im-list-recent-active-call__actions_container">
						<div class="bx-im-list-recent-active-call__actions_item --join">
							<ChatButton @click.stop="onJoinClick" :size="ButtonSize.M" :color="ButtonColor.Success" :isRounded="true" :text="loc('IM_LIST_RECENT_ACTIVE_CALL_JOIN')" />
						</div>
					</div>
					<div v-else-if="hasJoined && isTabWithActiveCall" class="bx-im-list-recent-active-call__actions_container">
						<div class="bx-im-list-recent-active-call__actions_item --return">
							<ChatButton @click.stop="returnToCall" :size="ButtonSize.M" :color="ButtonColor.Success" :isRounded="true" :text="loc('IM_LIST_RECENT_ACTIVE_CALL_RETURN')" />
						</div>
					</div>
					<div v-else-if="hasJoined && !isTabWithActiveCall" class="bx-im-list-recent-active-call__actions_container">
						<div class="bx-im-list-recent-active-call__actions_item --another-device">
							<ChatButton :size="ButtonSize.M" @click.stop="onBackToCallClick" :customColorScheme="anotherDeviceColorScheme" :isRounded="true" :text="loc('IM_LIST_RECENT_ACTIVE_CALL_ANOTHER_DEVICE')" />
						</div>
					</div>
				</div>
			</div>
		</div>
	`
	};

	const DefaultTitleByChatType = {
	  [im_v2_const.ChatType.chat]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_CHAT_DEFAULT_TITLE'),
	  [im_v2_const.ChatType.videoconf]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_CONFERENCE_DEFAULT_TITLE'),
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_CHANNEL_DEFAULT_TITLE'),
	  [im_v2_const.ChatType.collab]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_COLLAB_DEFAULT_TITLE')
	};
	const SubtitleByChatType = {
	  [im_v2_const.ChatType.chat]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_CHAT_SUBTITLE'),
	  [im_v2_const.ChatType.videoconf]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_CONFERENCE_SUBTITLE'),
	  [im_v2_const.ChatType.channel]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_CHANNEL_SUBTITLE'),
	  [im_v2_const.ChatType.collab]: main_core.Loc.getMessage('IM_LIST_RECENT_CREATE_COLLAB_SUBTITLE')
	};
	const CLOSE_ICON_SIZE = 20;

	// @vue/component
	const CreateChat = {
	  name: 'CreateChat',
	  components: {
	    EmptyAvatar: im_v2_component_elements_avatar.EmptyAvatar,
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  data() {
	    return {
	      chatTitle: '',
	      chatAvatarFile: '',
	      chatType: ''
	    };
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    Color: () => im_v2_const.Color,
	    CLOSE_ICON_SIZE: () => CLOSE_ICON_SIZE,
	    chatCreationIsOpened() {
	      const {
	        name: currentLayoutName
	      } = this.$store.getters['application/getLayout'];
	      return currentLayoutName === im_v2_const.Layout.createChat;
	    },
	    preparedTitle() {
	      if (this.chatTitle === '') {
	        return DefaultTitleByChatType[this.chatType];
	      }
	      return this.chatTitle;
	    },
	    preparedSubtitle() {
	      return SubtitleByChatType[this.chatType];
	    },
	    preparedAvatar() {
	      if (!this.chatAvatarFile) {
	        return null;
	      }
	      return URL.createObjectURL(this.chatAvatarFile);
	    },
	    avatarType() {
	      if (this.chatType === im_v2_const.ChatType.collab) {
	        return im_v2_component_elements_avatar.EmptyAvatarType.collab;
	      }
	      if (this.chatType === im_v2_const.ChatType.chat) {
	        return im_v2_component_elements_avatar.EmptyAvatarType.default;
	      }
	      return im_v2_component_elements_avatar.EmptyAvatarType.squared;
	    },
	    closeIconColor() {
	      return this.chatCreationIsOpened ? im_v2_const.Color.white : im_v2_const.Color.black;
	    }
	  },
	  created() {
	    const existingTitle = im_v2_lib_createChat.CreateChatManager.getInstance().getChatTitle();
	    if (existingTitle) {
	      this.chatTitle = existingTitle;
	    }
	    const existingAvatar = im_v2_lib_createChat.CreateChatManager.getInstance().getChatAvatar();
	    if (existingAvatar) {
	      this.chatAvatarFile = existingAvatar;
	    }
	    this.chatType = im_v2_lib_createChat.CreateChatManager.getInstance().getChatType();
	    im_v2_lib_createChat.CreateChatManager.getInstance().subscribe(im_v2_lib_createChat.CreateChatManager.events.titleChange, this.onTitleChange);
	    im_v2_lib_createChat.CreateChatManager.getInstance().subscribe(im_v2_lib_createChat.CreateChatManager.events.avatarChange, this.onAvatarChange);
	    im_v2_lib_createChat.CreateChatManager.getInstance().subscribe(im_v2_lib_createChat.CreateChatManager.events.chatTypeChange, this.onChatTypeChange);
	  },
	  beforeUnmount() {
	    im_v2_lib_createChat.CreateChatManager.getInstance().unsubscribe(im_v2_lib_createChat.CreateChatManager.events.titleChange, this.onTitleChange);
	    im_v2_lib_createChat.CreateChatManager.getInstance().unsubscribe(im_v2_lib_createChat.CreateChatManager.events.avatarChange, this.onAvatarChange);
	    im_v2_lib_createChat.CreateChatManager.getInstance().unsubscribe(im_v2_lib_createChat.CreateChatManager.events.chatTypeChange, this.onChatTypeChange);
	  },
	  methods: {
	    onTitleChange(event) {
	      this.chatTitle = event.getData();
	    },
	    onAvatarChange(event) {
	      this.chatAvatarFile = event.getData();
	    },
	    onChatTypeChange(event) {
	      this.chatType = event.getData();
	    },
	    onClick() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().startChatCreation(this.chatType, {
	        clearCurrentCreation: false
	      });
	    },
	    onCancel() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().clearExternalFields();
	      im_v2_lib_createChat.CreateChatManager.getInstance().setCreationStatus(false);
	      if (!this.chatCreationIsOpened) {
	        return;
	      }
	      void im_public.Messenger.openChat();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-recent-create-chat__container">
			<div class="bx-im-list-recent-item__wrap" :class="{'--selected': chatCreationIsOpened}" @click="onClick">
				<div class="bx-im-list-recent-item__container">
					<div class="bx-im-list-recent-create-chat__avatar-container">
						<EmptyAvatar 
							:url="preparedAvatar" 
							:size="AvatarSize.XL"
							:title="chatTitle"
							:type="avatarType"
						/>
					</div>
					<div class="bx-im-list-recent-item__content_container">
						<div class="bx-im-list-recent-item__content_header">
							<div class="bx-im-list-recent-create-chat__header --ellipsis">
								{{ preparedTitle }}
							</div>
						</div>
						<div class="bx-im-list-recent-item__content_bottom">
							<div class="bx-im-list-recent-item__message_container">
								{{ preparedSubtitle }}
							</div>
						</div>
					</div>
					<BIcon
						:name="OutlineIcons.CROSS_M"
						:size="CLOSE_ICON_SIZE"
						:color="closeIconColor"
						class="bx-im-list-recent-create-chat__icon-close"
						@click.stop="onCancel"
					/>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  components: {
	    ChatButton: im_v2_component_elements_button.ChatButton,
	    RecentEmptyState: im_v2_component_list_items_elements_emptyState.RecentEmptyState
	  },
	  computed: {
	    ButtonSize: () => im_v2_component_elements_button.ButtonSize,
	    ButtonColor: () => im_v2_component_elements_button.ButtonColor,
	    canInviteUsers() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.intranetInviteAvailable);
	    }
	  },
	  methods: {
	    onInviteUsersClick() {
	      im_v2_lib_invite.InviteManager.openInviteSlider();
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<RecentEmptyState
			:title="loc('IM_LIST_RECENT_EMPTY_STATE_TITLE')"
			:subtitle="loc('IM_LIST_RECENT_EMPTY_STATE_SUBTITLE')"
		>
			<ChatButton
				v-if="canInviteUsers"
				:size="ButtonSize.L"
				:isRounded="true"
				:text="loc('IM_LIST_RECENT_EMPTY_STATE_INVITE_USERS')"
				@click="onInviteUsersClick"
			/>
		</RecentEmptyState>
	`
	};

	class BroadcastManager extends main_core_events.EventEmitter {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  constructor() {
	    super();
	    this.setEventNamespace(BroadcastManager.eventNamespace);
	    this.init();
	  }
	  isSupported() {
	    return !main_core.Type.isUndefined(window.BroadcastChannel) && !im_v2_lib_utils.Utils.platform.isBitrixDesktop();
	  }
	  init() {
	    if (!this.isSupported()) {
	      return;
	    }
	    this.channel = new BroadcastChannel(BroadcastManager.channelName);
	    this.channel.addEventListener('message', ({
	      data: {
	        type,
	        data
	      }
	    }) => {
	      this.emit(type, data);
	    });
	  }
	  sendRecentList(recentData) {
	    if (!this.isSupported()) {
	      return;
	    }
	    this.channel.postMessage({
	      type: BroadcastManager.events.recentListUpdate,
	      data: recentData
	    });
	  }
	}
	BroadcastManager.instance = null;
	BroadcastManager.channelName = 'im-recent';
	BroadcastManager.eventNamespace = 'BX.Messenger.v2.Recent.BroadcastManager';
	BroadcastManager.events = {
	  recentListUpdate: 'recentListUpdate'
	};

	class LikeManager {
	  constructor() {
	    this.store = im_v2_application_core.Core.getStore();
	  }
	  init() {
	    this.onDialogInitedHandler = this.onDialogInited.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onDialogInited, this.onDialogInitedHandler);
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onDialogInited, this.onDialogInitedHandler);
	  }
	  onDialogInited(event) {
	    const {
	      dialogId
	    } = event.getData();
	    const recentItem = this.store.getters['recent/get'](dialogId);
	    if (!recentItem || !recentItem.liked) {
	      return;
	    }
	    this.store.dispatch('recent/like', {
	      id: dialogId,
	      liked: false
	    });
	  }
	}

	// @vue/component
	const RecentList = {
	  name: 'RecentList',
	  components: {
	    LoadingState: im_v2_component_elements_listLoadingState.ListLoadingState,
	    RecentItem,
	    ActiveCall,
	    CreateChat,
	    EmptyState
	  },
	  emits: ['chatClick'],
	  data() {
	    return {
	      isLoading: false,
	      isLoadingNextPage: false,
	      listIsScrolled: false,
	      isCreatingChat: false
	    };
	  },
	  computed: {
	    collection() {
	      return this.getRecentService().getCollection();
	    },
	    isEmptyCollection() {
	      return this.collection.length === 0;
	    },
	    preparedItems() {
	      const filteredCollection = this.collection.filter(item => {
	        let result = true;
	        if (!this.showBirthdays && item.isBirthdayPlaceholder) {
	          result = false;
	        }
	        if (item.isFakeElement && !this.isFakeItemNeeded(item)) {
	          result = false;
	        }
	        return result;
	      });
	      return [...filteredCollection].sort((a, b) => {
	        const firstDate = this.$store.getters['recent/getSortDate'](a.dialogId);
	        const secondDate = this.$store.getters['recent/getSortDate'](b.dialogId);
	        return secondDate - firstDate;
	      });
	    },
	    activeCalls() {
	      return this.$store.getters['recent/calls/get'];
	    },
	    pinnedItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === true;
	      });
	    },
	    generalItems() {
	      return this.preparedItems.filter(item => {
	        return item.pinned === false;
	      });
	    },
	    showBirthdays() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showBirthday);
	    },
	    showInvited() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showInvited);
	    },
	    firstPageLoaded() {
	      return this.getRecentService().firstPageIsLoaded;
	    }
	  },
	  async created() {
	    this.contextMenuManager = new im_v2_lib_menu.RecentMenu();
	    this.initBroadcastManager();
	    this.initLikeManager();
	    this.initCreateChatManager();
	    this.isLoading = true;
	    await this.getRecentService().loadFirstPage({
	      ignorePreloadedItems: true
	    });
	    this.isLoading = false;
	    void im_v2_lib_draft.DraftManager.getInstance().initDraftHistory();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	    this.destroyBroadcastManager();
	    this.destroyLikeManager();
	    this.destroyCreateChatManager();
	  },
	  methods: {
	    async onScroll(event) {
	      this.listIsScrolled = event.target.scrollTop > 0;
	      this.contextMenuManager.close();
	      if (!im_v2_lib_utils.Utils.dom.isOneScreenRemaining(event.target) || !this.getRecentService().hasMoreItemsToLoad) {
	        return;
	      }
	      this.isLoadingNextPage = true;
	      await this.getRecentService().loadNextPage();
	      this.isLoadingNextPage = false;
	    },
	    onClick(item) {
	      this.$emit('chatClick', item.dialogId);
	    },
	    onRightClick(item, event) {
	      if (im_v2_lib_utils.Utils.key.isCombination(event, 'Alt+Shift')) {
	        return;
	      }
	      const context = {
	        dialogId: item.dialogId,
	        recentItem: item,
	        compactMode: false
	      };
	      const positionTarget = {
	        left: event.clientX,
	        top: event.clientY
	      };
	      this.contextMenuManager.openMenu(context, positionTarget);
	      event.preventDefault();
	    },
	    onCallClick({
	      item,
	      $event
	    }) {
	      this.onClick(item, $event);
	    },
	    initBroadcastManager() {
	      this.onRecentListUpdate = event => {
	        this.getRecentService().setPreloadedData(event.data);
	      };
	      this.broadcastManager = BroadcastManager.getInstance();
	      this.broadcastManager.subscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
	    },
	    destroyBroadcastManager() {
	      this.broadcastManager = BroadcastManager.getInstance();
	      this.broadcastManager.unsubscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
	    },
	    initLikeManager() {
	      this.likeManager = new LikeManager();
	      this.likeManager.init();
	    },
	    destroyLikeManager() {
	      this.likeManager.destroy();
	    },
	    initCreateChatManager() {
	      if (im_v2_lib_createChat.CreateChatManager.getInstance().isCreating()) {
	        this.isCreatingChat = true;
	      }
	      this.onCreationStatusChange = event => {
	        this.isCreatingChat = event.getData();
	      };
	      im_v2_lib_createChat.CreateChatManager.getInstance().subscribe(im_v2_lib_createChat.CreateChatManager.events.creationStatusChange, this.onCreationStatusChange);
	    },
	    destroyCreateChatManager() {
	      im_v2_lib_createChat.CreateChatManager.getInstance().unsubscribe(im_v2_lib_createChat.CreateChatManager.events.creationStatusChange, this.onCreationStatusChange);
	    },
	    isFakeItemNeeded(item) {
	      const dialog = this.$store.getters['chats/get'](item.dialogId, true);
	      const isUser = dialog.type === im_v2_const.ChatType.user;
	      const hasBirthday = isUser && this.showBirthdays && this.$store.getters['users/hasBirthday'](item.dialogId);
	      return this.showInvited || hasBirthday;
	    },
	    getRecentService() {
	      if (!this.service) {
	        this.service = im_v2_provider_service_recent.LegacyRecentService.getInstance();
	      }
	      return this.service;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-recent__container">
			<div v-if="activeCalls.length > 0" class="bx-im-list-recent__calls_container" :class="{'--with-shadow': listIsScrolled}">
				<ActiveCall
					v-for="activeCall in activeCalls"
					:key="activeCall.dialogId"
					:item="activeCall"
					@click="onCallClick"
				/>
			</div>
			<CreateChat v-if="isCreatingChat" />
			<LoadingState v-if="isLoading && !firstPageLoaded" />
			<div v-else @scroll="onScroll" class="bx-im-list-recent__scroll-container">
				<EmptyState v-if="isEmptyCollection" />
				<div v-if="pinnedItems.length > 0" class="bx-im-list-recent__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-recent__general_container">
					<RecentItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>	
				<LoadingState v-if="isLoadingNextPage" />
			</div>
		</div>
	`
	};

	exports.RecentList = RecentList;
	exports.RecentItem = RecentItem;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.List,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Main,BX.Messenger.v2.Lib,BX.Messenger.Application,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Call.Lib,BX.UI.IconSet,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.List,BX,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=recent-list.bundle.js.map
