this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core_events,main_core,im_v2_component_list_elementList_recent,im_v2_component_search_searchInput,im_v2_component_search_searchResult,im_v2_lib_logger,im_v2_provider_service,im_v2_component_elements,im_v2_const) {
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
	      return this.$store.getters['recent/getTotalCounter'];
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
				:title="loc('IM_RECENT_HEADER_MENU_READ_ALL')"
				@click="onReadAllClick"
			/>
			<MenuItem
				:title="loc('IM_RECENT_HEADER_MENU_SHOW_UNREAD_ONLY')"
				:counter="unreadCounter"
				:disabled="true"
			/>
			<MenuItem
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
	      var _BX$Helper;
	      const ARTICLE_CODE = 17412872;
	      (_BX$Helper = BX.Helper) == null ? void 0 : _BX$Helper.show(`redirect=detail&code=${ARTICLE_CODE}`);
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
	const CreateChatMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements.MessengerMenu,
	    MenuItem: im_v2_component_elements.MenuItem,
	    CreateChatHelp
	  },
	  data() {
	    return {
	      showPopup: false
	    };
	  },
	  computed: {
	    MenuItemIcon: () => im_v2_component_elements.MenuItemIcon,
	    menuConfig() {
	      return {
	        id: 'im-create-chat-menu',
	        width: 255,
	        bindElement: this.$refs['icon'] || {},
	        offsetTop: 4,
	        padding: 0
	      };
	    }
	  },
	  methods: {
	    onGroupChatCreate() {
	      this.$store.dispatch('application/setLayout', {
	        layoutName: im_v2_const.Layout.createChat.name
	      });
	      this.showPopup = false;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="showPopup = true" class="bx-im-list-container-recent__create-chat_icon" :class="{'--active': showPopup}" ref="icon"></div>
		<MessengerMenu v-if="showPopup" :config="menuConfig" @close="showPopup = false">
			<MenuItem
				:icon="MenuItemIcon.chat"
				:title="loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE')"
				@click="onGroupChatCreate"
			/>
			<MenuItem
				:icon="MenuItemIcon.channel"
				:title="loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_V2')"
				:disabled="true"
			/>
			<MenuItem
				:icon="MenuItemIcon.conference"
				:title="loc('IM_RECENT_CREATE_CONFERENCE_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE')"
				:disabled="true"
			/>
			<template #footer>
				<CreateChatHelp @articleOpen="showPopup = false" />
			</template>
		</MessengerMenu>
	`
	};

	// @vue/component
	const RecentListContainer = {
	  name: 'RecentListContainer',
	  components: {
	    HeaderMenu,
	    CreateChatMenu,
	    SearchInput: im_v2_component_search_searchInput.SearchInput,
	    SearchResult: im_v2_component_search_searchResult.SearchResult,
	    RecentList: im_v2_component_list_elementList_recent.RecentList
	  },
	  emits: ['selectEntity'],
	  data() {
	    return {
	      searchMode: false,
	      unreadOnlyMode: false,
	      searchQuery: ''
	    };
	  },
	  computed: {
	    UnreadRecentService: () => im_v2_provider_service.UnreadRecentService
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
	    }
	  },
	  template: `
		<div class="bx-im-list-container-recent__scope bx-im-list-container-recent__container" ref="recent-container">
			<div class="bx-im-list-container-recent__header_container">
				<HeaderMenu @showUnread="unreadOnlyMode = true" />
				<div class="bx-im-list-container-recent__search-input_container">
					<SearchInput 
						:searchMode="searchMode" 
						@openSearch="onOpenSearch"
						@closeSearch="onCloseSearch"
						@updateSearch="onUpdateSearch"
					/>
				</div>
				<CreateChatMenu />
			</div>
			<div class="bx-im-list-container-recent__elements_container">
				<div class="bx-im-list-container-recent__elements">
					<SearchResult 
						v-show="searchMode" 
						:searchMode="searchMode" 
						:searchQuery="searchQuery" 
						:searchConfig="{}"
					/>
					<RecentList v-show="!searchMode && !unreadOnlyMode" @chatClick="onChatClick" key="recent" />
<!--					<RecentList-->
<!--						v-if="!searchMode && unreadOnlyMode"-->
<!--						:recentService="UnreadRecentService.getInstance()"-->
<!--						@chatClick="onChatClick"-->
<!--						key="unread"-->
<!--					/>-->
				</div>
			</div>
		</div>
	`
	};

	exports.RecentListContainer = RecentListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Event,BX,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Const));
//# sourceMappingURL=recent-container.bundle.js.map
