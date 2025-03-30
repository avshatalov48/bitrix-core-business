/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core_events,im_public,im_v2_lib_utils,im_v2_component_list_items_recent,im_v2_component_search_chatSearchInput,im_v2_component_search_chatSearch,im_v2_lib_logger,im_v2_provider_service,im_v2_component_elements,im_v2_const,im_v2_lib_analytics,im_v2_lib_permission,im_v2_lib_promo,im_v2_lib_createChat,im_v2_lib_feature,im_v2_lib_helpdesk,main_core) {
	'use strict';

	// @vue/component
	const HeaderMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem
	  },
	  emits: ['showUnread'],
	  data() {
	    return {
	      showPopup: false
	    };
	  },
	  computed: {
	    menuConfig() {
	      return {
	        id: 'im-recent-header-menu',
	        width: 284,
	        bindElement: this.$refs['icon'] || {},
	        offsetTop: 4,
	        padding: 0
	      };
	    },
	    unreadCounter() {
	      return this.$store.getters['counters/getTotalChatCounter'];
	    }
	  },
	  methods: {
	    onIconClick() {
	      this.showPopup = true;
	    },
	    onReadAllClick() {
	      new im_v2_provider_service.ChatService().readAll();
	      this.showPopup = false;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="onIconClick" class="bx-im-list-container-recent__header-menu_icon" :class="{'--active': showPopup}" ref="icon"></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_READ_ALL_MSGVER_1')"
				@click="onReadAllClick"
			/>
			<MenuItem
				v-if="false"
				:title="loc('IM_RECENT_HEADER_MENU_SHOW_UNREAD_ONLY')"
				:counter="unreadCounter"
				:disabled="true"
			/>
			<MenuItem
				v-if="false"
				:title="loc('IM_RECENT_HEADER_MENU_CHAT_GROUPS_TITLE')"
				:subtitle="loc('IM_RECENT_HEADER_MENU_CHAT_GROUPS_SUBTITLE')"
				:disabled="true"
			/>
		</MessengerMenu>
	`
	};

	// @vue/component
	const CreateChatHelp = {
	  emits: ['articleOpen'],
	  data() {
	    return {};
	  },
	  methods: {
	    openHelpArticle() {
	      const ARTICLE_CODE = '17412872';
	      im_v2_lib_helpdesk.openHelpdeskArticle(ARTICLE_CODE);
	      this.$emit('articleOpen');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-create-chat-help__container">
			<div @click="openHelpArticle" class="bx-im-create-chat-help__content">
				<div class="bx-im-create-chat-help__icon"></div>
				<div class="bx-im-create-chat-help__text">{{ loc('IM_RECENT_CREATE_CHAT_WHAT_TO_CHOOSE') }}</div>	
			</div>
		</div>
	`
	};

	// @vue/component
	const NewBadge = {
	  name: 'NewBadge',
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-create-chat-menu-new-badge__container">
			<div class="bx-im-create-chat-menu-new-badge__content">{{ loc('IM_RECENT_CREATE_COLLAB_NEW_BADGE') }}</div>
		</div>
	`
	};

	// @vue/component
	const DescriptionBanner = {
	  name: 'DescriptionBanner',
	  emits: ['close'],
	  computed: {
	    preparedText() {
	      return main_core.Loc.getMessage('IM_RECENT_CREATE_COLLAB_DESCRIPTION_BANNER', {
	        '[color_highlight]': '<span class="bx-im-create-chat-menu-description-banner__highlight">',
	        '[/color_highlight]': '</span>'
	      });
	    }
	  },
	  template: `
		<div class="bx-im-create-chat-menu-description-banner__container">
			<div class="bx-im-create-chat-menu-description-banner__content" v-html="preparedText"></div>
			<div class="bx-im-create-chat-menu-description-banner__close-icon" @click.stop="$emit('close')"></div>
		</div>
	`
	};

	const PromoByChatType = {
	  [im_v2_const.ChatType.chat]: im_v2_const.PromoId.createGroupChat,
	  [im_v2_const.ChatType.videoconf]: im_v2_const.PromoId.createConference,
	  [im_v2_const.ChatType.channel]: im_v2_const.PromoId.createChannel
	};

	// @vue/component
	const CreateChatMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem,
	    CreateChatHelp,
	    CreateChatPromo: im_v2_component_elements.CreateChatPromo,
	    NewBadge,
	    DescriptionBanner
	  },
	  data() {
	    return {
	      showPopup: false,
	      chatTypeToCreate: '',
	      showPromo: false,
	      showCollabDescription: false
	    };
	  },
	  computed: {
	    ChatType: () => im_v2_const.ChatType,
	    MenuItemIcon: () => im_v2_component_elements.MenuItemIcon,
	    menuConfig() {
	      return {
	        id: 'im-create-chat-menu',
	        width: 275,
	        bindElement: this.$refs.icon || {},
	        offsetTop: 4,
	        padding: 0
	      };
	    },
	    collabAvailable() {
	      const hasAccess = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createCollab);
	      const creationAvailable = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.collabCreationAvailable);
	      const featureAvailable = im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.collabAvailable);
	      return hasAccess && featureAvailable && creationAvailable;
	    },
	    canCreateChat() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createChat);
	    },
	    canCreateChannel() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createChannel);
	    },
	    canCreateConference() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createConference);
	    }
	  },
	  created() {
	    this.showCollabDescription = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.createCollabDescription);
	  },
	  methods: {
	    onChatCreateClick(type) {
	      im_v2_lib_analytics.Analytics.getInstance().chatCreate.onStartClick(type);
	      this.chatTypeToCreate = type;
	      const promoBannerIsNeeded = im_v2_lib_promo.PromoManager.getInstance().needToShow(this.getPromoType());
	      if (promoBannerIsNeeded) {
	        this.showPromo = true;
	        this.showPopup = false;
	        return;
	      }
	      this.startChatCreation();
	      this.showPopup = false;
	    },
	    onPromoContinueClick() {
	      im_v2_lib_promo.PromoManager.getInstance().markAsWatched(this.getPromoType());
	      this.startChatCreation();
	      this.showPromo = false;
	      this.showPopup = false;
	      this.chatTypeToCreate = '';
	    },
	    onCollabDescriptionClose() {
	      im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.createCollabDescription);
	      this.showCollabDescription = false;
	    },
	    startChatCreation() {
	      const {
	        name: currentLayoutName,
	        entityId: currentLayoutChatType
	      } = this.$store.getters['application/getLayout'];
	      if (currentLayoutName === im_v2_const.Layout.createChat.name && currentLayoutChatType === this.chatTypeToCreate) {
	        return;
	      }
	      im_v2_lib_createChat.CreateChatManager.getInstance().startChatCreation(this.chatTypeToCreate);
	    },
	    getPromoType() {
	      var _PromoByChatType$this;
	      return (_PromoByChatType$this = PromoByChatType[this.chatTypeToCreate]) != null ? _PromoByChatType$this : '';
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-list-container-recent__create-chat_icon"
			:class="{'--active': showPopup}"
			@click="showPopup = true"
			ref="icon"
		></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				v-if="canCreateChat"
				:icon="MenuItemIcon.chat"
				:title="loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE_V2')"
				@click="onChatCreateClick(ChatType.chat)"
			/>
			<MenuItem
				v-if="canCreateChannel"
				:icon="MenuItemIcon.channel"
				:title="loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_V3')"
				@click="onChatCreateClick(ChatType.channel)"
			/>
			<MenuItem
				v-if="collabAvailable"
				:icon="MenuItemIcon.collab"
				:title="loc('IM_RECENT_CREATE_COLLAB_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_COLLAB_SUBTITLE')"
				@click="onChatCreateClick(ChatType.collab)"
			>
				<template #after-title><NewBadge /></template>
				<template #below-content><DescriptionBanner v-if="showCollabDescription" @close="onCollabDescriptionClose" /></template>
			</MenuItem>
			<MenuItem
				v-if="canCreateConference"
				:icon="MenuItemIcon.conference"
				:title="loc('IM_RECENT_CREATE_CONFERENCE_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE_V2')"
				@click="onChatCreateClick(ChatType.videoconf)"
			/>
			<template #footer>
				<CreateChatHelp @articleOpen="showPopup = false" />
			</template>
		</MessengerMenu>
		<CreateChatPromo
			v-if="showPromo"
			:chatType="chatTypeToCreate"
			@continue="onPromoContinueClick"
			@close="showPromo = false"
		/>
	`
	};

	const searchConfig = Object.freeze({
	  chats: true,
	  users: true
	});

	// @vue/component
	const RecentListContainer = {
	  name: 'RecentListContainer',
	  components: {
	    HeaderMenu,
	    CreateChatMenu,
	    ChatSearchInput: im_v2_component_search_chatSearchInput.ChatSearchInput,
	    RecentList: im_v2_component_list_items_recent.RecentList,
	    ChatSearch: im_v2_component_search_chatSearch.ChatSearch
	  },
	  emits: ['selectEntity'],
	  data() {
	    return {
	      searchMode: false,
	      unreadOnlyMode: false,
	      searchQuery: '',
	      isSearchLoading: false
	    };
	  },
	  computed: {
	    searchConfig: () => searchConfig,
	    canCreateChat() {
	      const actions = [im_v2_const.ActionByUserType.createChat, im_v2_const.ActionByUserType.createCollab, im_v2_const.ActionByUserType.createChannel, im_v2_const.ActionByUserType.createConference];
	      return actions.some(action => im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(action));
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('List: Recent container created');
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.openSearch, this.onOpenSearch);
	    main_core.Event.bind(document, 'mousedown', this.onDocumentClick);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.openSearch, this.onOpenSearch);
	    main_core.Event.unbind(document, 'mousedown', this.onDocumentClick);
	  },
	  methods: {
	    onChatClick(dialogId) {
	      this.$emit('selectEntity', {
	        layoutName: im_v2_const.Layout.chat.name,
	        entityId: dialogId
	      });
	    },
	    onOpenSearch() {
	      this.searchMode = true;
	    },
	    onCloseSearch() {
	      this.searchMode = false;
	      this.searchQuery = '';
	    },
	    onUpdateSearch(query) {
	      this.searchMode = true;
	      this.searchQuery = query;
	    },
	    onDocumentClick(event) {
	      const clickOnRecentContainer = event.composedPath().includes(this.$refs['recent-container']);
	      if (!clickOnRecentContainer) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.search.close);
	      }
	    },
	    onLoading(value) {
	      this.isSearchLoading = value;
	    },
	    async onItemClick(event) {
	      const {
	        dialogId,
	        nativeEvent
	      } = event;
	      void im_public.Messenger.openChat(dialogId);
	      if (!im_v2_lib_utils.Utils.key.isAltOrOption(nativeEvent)) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.search.close);
	      }
	    }
	  },
	  template: `
		<div class="bx-im-list-container-recent__scope bx-im-list-container-recent__container" ref="recent-container">
			<div class="bx-im-list-container-recent__header_container">
				<HeaderMenu @showUnread="unreadOnlyMode = true" />
				<div class="bx-im-list-container-recent__search-input_container">
					<ChatSearchInput 
						:searchMode="searchMode" 
						:isLoading="searchMode && isSearchLoading"
						@openSearch="onOpenSearch"
						@closeSearch="onCloseSearch"
						@updateSearch="onUpdateSearch"
					/>
				</div>
				<CreateChatMenu v-if="canCreateChat" />
			</div>
			<div class="bx-im-list-container-recent__elements_container">
				<div class="bx-im-list-container-recent__elements">
					<ChatSearch 
						v-show="searchMode" 
						:searchMode="searchMode"
						:searchQuery="searchQuery"
						:searchConfig="searchConfig"
						:saveSearchHistory="true"
						@loading="onLoading"
						@clickItem="onItemClick"
					/>
					<RecentList v-show="!searchMode && !unreadOnlyMode" @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`
	};

	exports.RecentListContainer = RecentListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX));
//# sourceMappingURL=recent-container.bundle.js.map
