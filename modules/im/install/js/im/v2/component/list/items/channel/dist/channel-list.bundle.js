/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_date,im_v2_component_elements,im_v2_lib_utils,im_v2_lib_parser,im_v2_lib_dateFormatter,im_v2_lib_logger,im_v2_lib_user,im_v2_application_core,im_v2_lib_rest,main_core,im_v2_const,im_v2_lib_layout,im_v2_lib_menu) {
	'use strict';

	// @vue/component
	const MessageText = {
	  name: 'MessageText',
	  components: {
	    ChatTitle: im_v2_component_elements.ChatTitle
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
	    recentItem() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
	    },
	    message() {
	      return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
	    },
	    formattedDate() {
	      return this.formatDate(this.message.date);
	    },
	    isLastMessageAuthor() {
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    lastMessageAuthorAvatar() {
	      const author = this.$store.getters['users/get'](this.message.authorId);
	      if (!author) {
	        return '';
	      }
	      return author.avatar;
	    },
	    lastMessageAuthorAvatarStyle() {
	      return {
	        backgroundImage: `url('${this.lastMessageAuthorAvatar}')`
	      };
	    },
	    messageText() {
	      if (this.message.isDeleted) {
	        return this.loc('IM_LIST_RECENT_DELETED_MESSAGE');
	      }
	      const formattedText = im_v2_lib_parser.Parser.purifyRecent(this.recentItem);
	      if (!formattedText) {
	        return this.loc('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
	      }
	      return formattedText;
	    },
	    formattedMessageText() {
	      const SPLIT_INDEX = 27;
	      return im_v2_lib_utils.Utils.text.insertUnseenWhitespace(this.messageText, SPLIT_INDEX);
	    }
	  },
	  methods: {
	    formatDate(date) {
	      return im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.recent);
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-list-channel-item__message_container">
			<span class="bx-im-list-channel-item__message_text">
				<span v-if="isLastMessageAuthor" class="bx-im-list-channel-item__message_author-icon --self"></span>
				<template v-else-if="message.authorId">
					<span v-if="lastMessageAuthorAvatar" :style="lastMessageAuthorAvatarStyle" class="bx-im-list-channel-item__message_author-icon --user"></span>
					<span v-else class="bx-im-list-channel-item__message_author-icon --user --default"></span>
				</template>
				<span class="bx-im-list-channel-item__message_text_content">{{ formattedMessageText }}</span>
			</span>
		</div>
	`
	};

	// @vue/component
	const ChannelItem = {
	  name: 'ChannelItem',
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
	      if (this.needsBirthdayPlaceholder) {
	        return this.loc('IM_LIST_RECENT_BIRTHDAY_DATE');
	      }
	      return this.formatDate(this.message.date);
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
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isChatSelected() {
	      if (this.layout.name !== im_v2_const.Layout.channel.name) {
	        return false;
	      }
	      return this.layout.entityId === this.recentItem.dialogId;
	    },
	    isChatMuted() {
	      if (this.isUser) {
	        return false;
	      }
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === im_v2_application_core.Core.getUserId();
	      });
	      return Boolean(isMuted);
	    },
	    isSomeoneTyping() {
	      return this.dialog.writingList.length > 0;
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
	        '--selected': this.isChatSelected
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
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-channel-item__wrap">
			<div class="bx-im-list-channel-item__container">
				<div class="bx-im-list-channel-item__avatar_container">
					<div class="bx-im-list-channel-item__avatar_content">
						<ChatAvatar 
							:avatarDialogId="recentItem.dialogId" 
							:contextDialogId="recentItem.dialogId"
							:size="AvatarSize.XL" 
						/>
					</div>
				</div>
				<div class="bx-im-list-channel-item__content_container">
					<div class="bx-im-list-channel-item__content_header">
						<ChatTitle :dialogId="recentItem.dialogId" />
						<div class="bx-im-list-channel-item__date">
							<span>{{ formattedDate }}</span>
						</div>
					</div>
					<div class="bx-im-list-channel-item__content_bottom">
						<MessageText :item="recentItem" />
					</div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-channel__empty">
			<div class="bx-im-list-channel__empty_icon"></div>
			<div class="bx-im-list-channel__empty_text">{{ loc('IM_LIST_CHANNEL_EMPTY') }}</div>
		</div>
	`
	};

	var _itemsPerPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("itemsPerPage");
	var _isLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoading");
	var _pagesLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pagesLoaded");
	var _hasMoreItemsToLoad = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasMoreItemsToLoad");
	var _lastMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastMessageId");
	var _requestItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestItems");
	var _updateModels = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateModels");
	var _getMinMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMinMessageId");
	class ChannelService {
	  constructor() {
	    Object.defineProperty(this, _getMinMessageId, {
	      value: _getMinMessageId2
	    });
	    Object.defineProperty(this, _updateModels, {
	      value: _updateModels2
	    });
	    Object.defineProperty(this, _requestItems, {
	      value: _requestItems2
	    });
	    this.firstPageIsLoaded = false;
	    Object.defineProperty(this, _itemsPerPage, {
	      writable: true,
	      value: 50
	    });
	    Object.defineProperty(this, _isLoading, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _pagesLoaded, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _hasMoreItemsToLoad, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _lastMessageId, {
	      writable: true,
	      value: 0
	    });
	  }
	  async loadFirstPage() {
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    const result = await babelHelpers.classPrivateFieldLooseBase(this, _requestItems)[_requestItems]({
	      firstPage: true
	    });
	    this.firstPageIsLoaded = true;
	    return result;
	  }
	  loadNextPage() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _hasMoreItemsToLoad)[_hasMoreItemsToLoad]) {
	      return Promise.resolve();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	    return babelHelpers.classPrivateFieldLooseBase(this, _requestItems)[_requestItems]();
	  }
	  hasMoreItemsToLoad() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _hasMoreItemsToLoad)[_hasMoreItemsToLoad];
	  }
	}
	async function _requestItems2({
	  firstPage = false
	} = {}) {
	  const queryParams = {
	    data: {
	      limit: babelHelpers.classPrivateFieldLooseBase(this, _itemsPerPage)[_itemsPerPage],
	      filter: {
	        lastMessageId: firstPage ? null : babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId]
	      }
	    }
	  };
	  const result = await im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2RecentChannelTail, queryParams).catch(error => {
	    // eslint-disable-next-line no-console
	    console.error('Im.ChannelList: page request error', error);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _pagesLoaded)[_pagesLoaded]++;
	  im_v2_lib_logger.Logger.warn(`Im.ChannelList: ${firstPage ? 'First' : babelHelpers.classPrivateFieldLooseBase(this, _pagesLoaded)[_pagesLoaded]} page request result`, result);
	  const {
	    messages,
	    hasNextPage
	  } = result;
	  babelHelpers.classPrivateFieldLooseBase(this, _lastMessageId)[_lastMessageId] = babelHelpers.classPrivateFieldLooseBase(this, _getMinMessageId)[_getMinMessageId](messages);
	  if (!hasNextPage) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hasMoreItemsToLoad)[_hasMoreItemsToLoad] = false;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	  if (firstPage) {
	    void im_v2_application_core.Core.getStore().dispatch('recent/clearChannelCollection');
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _updateModels)[_updateModels](result);
	}
	function _updateModels2(restResult) {
	  const {
	    users,
	    chats,
	    messages,
	    files,
	    recentItems
	  } = restResult;
	  const usersPromise = im_v2_application_core.Core.getStore().dispatch('users/set', users);
	  const dialoguesPromise = im_v2_application_core.Core.getStore().dispatch('chats/set', chats);
	  const messagesPromise = im_v2_application_core.Core.getStore().dispatch('messages/store', messages);
	  const filesPromise = im_v2_application_core.Core.getStore().dispatch('files/set', files);
	  const recentPromise = im_v2_application_core.Core.getStore().dispatch('recent/setChannel', recentItems);
	  return Promise.all([usersPromise, dialoguesPromise, messagesPromise, filesPromise, recentPromise]);
	}
	function _getMinMessageId2(messages) {
	  if (messages.length === 0) {
	    return 0;
	  }
	  const firstMessageId = messages[0].id;
	  return messages.reduce((minId, nextMessage) => {
	    return Math.min(minId, nextMessage.id);
	  }, firstMessageId);
	}

	const TAG = 'IM_SHARED_CHANNEL_LIST';
	var _pullClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pullClient");
	var _requestWatchStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestWatchStart");
	class PullWatchManager {
	  constructor() {
	    Object.defineProperty(this, _requestWatchStart, {
	      value: _requestWatchStart2
	    });
	    Object.defineProperty(this, _pullClient, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient] = im_v2_application_core.Core.getPullClient();
	  }
	  subscribe() {
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(TAG);
	    babelHelpers.classPrivateFieldLooseBase(this, _requestWatchStart)[_requestWatchStart]();
	  }
	  unsubscribe() {
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].clearWatch(TAG);
	  }
	}
	function _requestWatchStart2() {
	  void im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2RecentChannelExtendPullWatch);
	}

	class ChannelRecentMenu extends im_v2_lib_menu.RecentMenu {
	  getMenuItems() {
	    return [this.getOpenItem()];
	  }
	  getOpenItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN'),
	      onclick: () => {
	        im_v2_lib_layout.LayoutManager.getInstance().setLayout({
	          name: im_v2_const.Layout.channel.name,
	          entityId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	}

	// @vue/component
	const ChannelList = {
	  name: 'ChannelList',
	  components: {
	    EmptyState,
	    LoadingState: im_v2_component_elements.ListLoadingState,
	    ChannelItem
	  },
	  emits: ['chatClick'],
	  data() {
	    return {
	      isLoading: false,
	      isLoadingNextPage: false,
	      firstPageLoaded: false
	    };
	  },
	  computed: {
	    collection() {
	      return this.$store.getters['recent/getChannelCollection'];
	    },
	    preparedItems() {
	      return [...this.collection].sort((a, b) => {
	        const firstMessage = this.$store.getters['messages/getById'](a.messageId);
	        const secondMessage = this.$store.getters['messages/getById'](b.messageId);
	        return secondMessage.date - firstMessage.date;
	      });
	    },
	    isEmptyCollection() {
	      return this.collection.length === 0;
	    }
	  },
	  created() {
	    this.contextMenuManager = new ChannelRecentMenu();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  async activated() {
	    this.isLoading = true;
	    await this.getRecentService().loadFirstPage();
	    this.firstPageLoaded = true;
	    this.isLoading = false;
	    this.getPullWatchManager().subscribe();
	  },
	  deactivated() {
	    this.getPullWatchManager().unsubscribe();
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
	    onClick(item) {
	      this.$emit('chatClick', item.dialogId);
	    },
	    onRightClick(item, event) {
	      event.preventDefault();
	      this.contextMenuManager.openMenu(item, event.currentTarget);
	    },
	    getRecentService() {
	      if (!this.service) {
	        this.service = new ChannelService();
	      }
	      return this.service;
	    },
	    getPullWatchManager() {
	      if (!this.pullWatchManager) {
	        this.pullWatchManager = new PullWatchManager();
	      }
	      return this.pullWatchManager;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-list-channel__container">
			<LoadingState v-if="isLoading && !firstPageLoaded" />
			<div v-else @scroll="onScroll" class="bx-im-list-channel__scroll-container">
				<EmptyState v-if="isEmptyCollection" />
				<div class="bx-im-list-channel__general_container">
					<ChannelItem
						v-for="item in preparedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<LoadingState v-if="isLoadingNextPage" />
			</div>
		</div>
	`
	};

	exports.ChannelList = ChannelList;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Main,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=channel-list.bundle.js.map
