/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_draft,main_date,im_v2_lib_utils,im_v2_lib_parser,im_v2_component_elements,im_v2_lib_dateFormatter,im_v2_application_core,im_v2_const,im_v2_lib_layout,im_v2_lib_logger,im_v2_provider_service,main_core,im_public,im_v2_lib_menu) {
	'use strict';

	// @vue/component
	const MessageText = {
	  name: 'MessageText',
	  components: {
	    MessageAvatar: im_v2_component_elements.MessageAvatar
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
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
	    showLastMessage() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showLastMessage);
	    },
	    hiddenMessageText() {
	      return this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
	    },
	    isLastMessageAuthor() {
	      if (!this.message) {
	        return false;
	      }
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    lastMessageAuthorAvatar() {
	      const authorDialog = this.$store.getters['chats/get'](this.message.authorId);
	      if (!authorDialog) {
	        return '';
	      }
	      return authorDialog.avatar;
	    },
	    lastMessageAuthorAvatarStyle() {
	      return {
	        backgroundImage: `url('${this.lastMessageAuthorAvatar}')`
	      };
	    },
	    messageText() {
	      const formattedText = im_v2_lib_parser.Parser.purifyRecent(this.recentItem);
	      if (!formattedText) {
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
				<span class="bx-im-list-copilot-item__message_draft-prefix">${prefix}</span>
				<span class="bx-im-list-copilot-item__message_text_content">${text}</span>
			`;
	    },
	    formattedDraftText() {
	      return im_v2_lib_parser.Parser.purify({
	        text: this.recentItem.draft.text,
	        showIconIfEmptyText: false
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-list-copilot-item__message_container">
			<span class="bx-im-list-copilot-item__message_text">
				<span v-if="recentItem.draft.text && dialog.counter === 0" v-html="preparedDraftContent"></span>
				<span v-else-if="!showLastMessage">{{ hiddenMessageText }}</span>
				<template v-else>
					<span v-if="isLastMessageAuthor" class="bx-im-list-copilot-item__message_author-icon --self"></span>
					<MessageAvatar
						v-else-if="message.authorId"
						:messageId="message.id"
						:authorId="message.authorId"
						:size="AvatarSize.XXS"
						class="bx-im-list-copilot-item__message_author-avatar"
					/>
					<span class="bx-im-list-copilot-item__message_text_content">{{ formattedMessageText }}</span>
				</template>
			</span>
		</div>
	`
	};

	// @vue/component
	const CopilotItem = {
	  name: 'CopilotItem',
	  components: {
	    ChatAvatar: im_v2_component_elements.ChatAvatar,
	    ChatTitle: im_v2_component_elements.ChatTitle,
	    MessageText
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
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    recentItem() {
	      return this.item;
	    },
	    formattedDate() {
	      return this.formatDate(this.message.date);
	    },
	    formattedCounter() {
	      return this.dialog.counter > 99 ? '99+' : this.dialog.counter.toString();
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
	    },
	    message() {
	      return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
	    },
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    isChatSelected() {
	      if (this.layout.name !== im_v2_const.Layout.copilot.name) {
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
	    isSomeoneTyping() {
	      return this.dialog.writingList.length > 0;
	    },
	    showLastMessage() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showLastMessage);
	    },
	    showPinnedIcon() {
	      return this.recentItem.pinned && this.dialog.counter === 0 && !this.recentItem.unread;
	    },
	    showCounter() {
	      return this.dialog.counter > 0;
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
	  // language=Vue
	  template: `
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-copilot-item__wrap">
			<div :class="itemClasses" class="bx-im-list-copilot-item__container">
				<div class="bx-im-list-copilot-item__avatar_container">
					<div class="bx-im-list-copilot-item__avatar_content">
						<ChatAvatar
							:avatarDialogId="recentItem.dialogId"
							:contextDialogId="recentItem.dialogId"
							:withSpecialTypes="false"
							:size="AvatarSize.XL"
						/>
						<div v-if="isSomeoneTyping" class="bx-im-list-copilot-item__avatar_typing"></div>
					</div>
				</div>
				<div class="bx-im-list-copilot-item__content_container">
					<div class="bx-im-list-copilot-item__content_header">
						<ChatTitle :dialogId="recentItem.dialogId" :withMute="true" />
						<div class="bx-im-list-copilot-item__date">
							<span>{{ formattedDate }}</span>
						</div>
					</div>
					<div class="bx-im-list-copilot-item__content_bottom">
						<MessageText :item="recentItem" />
						<div :class="{'--extended': dialog.counter > 99}" class="bx-im-list-copilot-item__counter_wrap">
							<div class="bx-im-list-copilot-item__counter_container">
								<div v-if="showPinnedIcon" class="bx-im-list-copilot-item__pinned-icon"></div>
								<div v-else-if="showCounter" :class="{'--muted': isChatMuted}" class="bx-im-list-copilot-item__counter_number">
									{{ formattedCounter }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`
	};

	class CopilotRecentService extends im_v2_provider_service.RecentService {
	  getQueryParams(firstPage) {
	    return {
	      ONLY_COPILOT: 'Y',
	      LIMIT: this.itemsPerPage,
	      LAST_MESSAGE_DATE: firstPage ? null : this.lastMessageDate,
	      GET_ORIGINAL_TEXT: 'Y',
	      PARSE_TEXT: 'Y'
	    };
	  }
	  getModelSaveMethod() {
	    return 'recent/setCopilot';
	  }
	  getCollection() {
	    return im_v2_application_core.Core.getStore().getters['recent/getCopilotCollection'];
	  }
	  getExtractorOptions() {
	    return {
	      withBirthdays: false
	    };
	  }
	  hideChat(dialogId) {
	    im_v2_lib_logger.Logger.warn('Im.CopilotRecentList: hide chat', dialogId);
	    const recentItem = im_v2_application_core.Core.getStore().getters['recent/get'](dialogId);
	    if (!recentItem) {
	      return;
	    }
	    im_v2_application_core.Core.getStore().dispatch('recent/delete', {
	      id: dialogId
	    });
	    const chatIsOpened = im_v2_application_core.Core.getStore().getters['application/isChatOpen'](dialogId);
	    if (chatIsOpened) {
	      im_v2_lib_layout.LayoutManager.getInstance().clearCurrentLayoutEntityId();
	      void im_v2_lib_layout.LayoutManager.getInstance().deleteLastOpenedElementById(dialogId);
	    }
	    im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imRecentHide, {
	      DIALOG_ID: dialogId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('Im.CopilotRecentList: hide chat error', error);
	    });
	  }
	}

	class CopilotRecentMenu extends im_v2_lib_menu.RecentMenu {
	  getMenuItems() {
	    return [this.getPinMessageItem(), this.getHideItem(), this.getLeaveItem()];
	  }
	  getOpenItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN'),
	      onclick: () => {
	        im_public.Messenger.openCopilot(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getHideItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_HIDE_MSGVER_1'),
	      onclick: () => {
	        this.getRecentService().hideChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getRecentService() {
	    if (!this.service) {
	      this.service = new CopilotRecentService();
	    }
	    return this.service;
	  }
	}

	// @vue/component
	const CopilotList = {
	  name: 'CopilotList',
	  components: {
	    CopilotItem,
	    LoadingState: im_v2_component_elements.ListLoadingState
	  },
	  emits: ['chatClick'],
	  data() {
	    return {
	      isLoading: false,
	      isLoadingNextPage: false
	    };
	  },
	  computed: {
	    collection() {
	      return this.getRecentService().getCollection();
	    },
	    sortedItems() {
	      return [...this.collection].sort((a, b) => {
	        const firstDate = this.$store.getters['recent/getSortDate'](a.dialogId);
	        const secondDate = this.$store.getters['recent/getSortDate'](b.dialogId);
	        return secondDate - firstDate;
	      });
	    },
	    pinnedItems() {
	      return this.sortedItems.filter(item => {
	        return item.pinned === true;
	      });
	    },
	    generalItems() {
	      return this.sortedItems.filter(item => {
	        return item.pinned === false;
	      });
	    },
	    isEmptyCollection() {
	      return this.collection.length === 0;
	    }
	  },
	  async created() {
	    this.contextMenuManager = new CopilotRecentMenu();
	    this.isLoading = true;
	    await this.getRecentService().loadFirstPage();
	    this.isLoading = false;
	    void im_v2_lib_draft.CopilotDraftManager.getInstance().initDraftHistory();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  methods: {
	    async onScroll(event) {
	      this.contextMenuManager.close();
	      if (!im_v2_lib_utils.Utils.dom.isOneScreenRemaining(event.target) || !this.getRecentService().hasMoreItemsToLoad) {
	        return;
	      }
	      this.isLoadingNextPage = true;
	      await this.getRecentService().loadNextPage();
	      this.isLoadingNextPage = false;
	    },
	    onClick(item, event) {
	      this.$emit('chatClick', item.dialogId);
	    },
	    onRightClick(item, event) {
	      event.preventDefault();
	      this.contextMenuManager.openMenu(item, event.currentTarget);
	    },
	    getRecentService() {
	      if (!this.service) {
	        this.service = new CopilotRecentService();
	      }
	      return this.service;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-copilot__scope bx-im-list-copilot__container">
			<LoadingState v-if="isLoading && isEmptyCollection" />
			<div v-else @scroll="onScroll" class="bx-im-list-copilot__scroll-container">
				<div v-if="isEmptyCollection" class="bx-im-list-copilot__empty">
					<div class="bx-im-list-copilot__empty_icon"></div>
					<div class="bx-im-list-copilot__empty_text">{{ loc('IM_LIST_COPILOT_EMPTY') }}</div>
				</div>
				<div v-if="pinnedItems.length > 0" class="bx-im-list-copilot__pinned_container">
					<CopilotItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-copilot__general_container">
					<CopilotItem
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

	exports.CopilotList = CopilotList;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Lib,BX.Main,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=copilot-list.bundle.js.map
