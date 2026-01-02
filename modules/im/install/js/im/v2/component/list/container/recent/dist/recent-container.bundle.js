/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core_events,im_v2_component_list_items_recent,im_v2_component_search,im_v2_lib_logger,im_v2_provider_service_chat,ui_infoHelper,im_public,im_v2_component_elements_menu,im_v2_component_elements_copilotRolesDialog,im_v2_component_list_container_elements_createChatPromo,im_v2_lib_permission,im_v2_lib_createChat,im_v2_lib_feature,im_v2_provider_service_copilot,im_v2_lib_helpdesk,main_core,im_v2_lib_analytics,im_v2_lib_invite,im_v2_lib_promo,im_v2_const) {
	'use strict';

	// @vue/component
	const HeaderMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements_menu.MessengerMenu,
	    MenuItem: im_v2_component_elements_menu.MenuItem
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
	        bindElement: this.$refs.icon || {},
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
	      new im_v2_provider_service_chat.ChatService().readAll();
	      this.showPopup = false;
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-list-container-recent__header-menu_icon"
			:class="{'--active': showPopup}"
			@click="onIconClick"
			ref="icon"
		></div>
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

	// @vue/component
	const CopilotRoleSelectionButton = {
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div
			class="bx-im-create-chat-menu-item__button --copilot"
			:title="loc('IM_RECENT_CREATE_COPILOT_ROLE_SELECTION_TITLE')"
		>
			<div class="bx-im-create-chat-menu-item__icon-more"></div>
		</div>
	`
	};

	// @vue/component
	const InvitePromo = {
	  name: 'InvitePromo',
	  emits: ['close'],
	  methods: {
	    onContainerClick() {
	      const analyticsContext = im_v2_lib_analytics.Analytics.getInstance().sliderInvite.getRecentCreateMenuContext();
	      im_v2_lib_invite.InviteManager.openInviteSlider(analyticsContext);
	    },
	    onCloseClick() {
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.recentCreateChatInviteUsers);
	      this.$emit('close');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="onContainerClick" class="bx-im-recent-invite-promo__container">
			<div class="bx-im-recent-invite-promo__icon"></div>
			<div class="bx-im-recent-invite-promo__content">
				<div class="bx-im-recent-invite-promo__title">
					{{ loc('IM_RECENT_CREATE_INVITE_TITLE') }}
				</div>
				<div class="bx-im-recent-invite-promo__subtitle">
					{{ loc('IM_RECENT_CREATE_INVITE_SUBTITLE') }}
				</div>
			</div>
			<div @click.stop="onCloseClick" class="bx-im-recent-invite-promo__close-icon"></div>
		</div>
	`
	};

	const PromoByChatType = {
	  [im_v2_const.ChatType.chat]: im_v2_const.PromoId.createGroupChat,
	  [im_v2_const.ChatType.videoconf]: im_v2_const.PromoId.createConference,
	  [im_v2_const.ChatType.channel]: im_v2_const.PromoId.createChannel
	};
	const COPILOT_UNIVERSAL_ROLE = 'copilot_assistant';

	// @vue/component
	const CreateChatMenu = {
	  components: {
	    MessengerMenu: im_v2_component_elements_menu.MessengerMenu,
	    MenuItem: im_v2_component_elements_menu.MenuItem,
	    CreateChatHelp,
	    CreateChatPromo: im_v2_component_list_container_elements_createChatPromo.CreateChatPromo,
	    NewBadge,
	    DescriptionBanner,
	    CopilotRoleSelectionButton,
	    CopilotRolesDialog: im_v2_component_elements_copilotRolesDialog.CopilotRolesDialog,
	    InvitePromo
	  },
	  data() {
	    return {
	      showMenu: false,
	      chatTypeToCreate: '',
	      showCreateChatPromo: false,
	      showCollabPromo: false,
	      showInvitePromo: false,
	      showCopilotRolesDialog: false,
	      isLoading: false
	    };
	  },
	  computed: {
	    ChatType: () => im_v2_const.ChatType,
	    MenuItemIcon: () => im_v2_component_elements_menu.MenuItemIcon,
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
	    canCreateCopilot() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createCopilot);
	    },
	    isCopilotAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.copilotAvailable);
	    },
	    isCopilotActive() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.copilotActive);
	    },
	    isCopilotAvailableAndCreatable() {
	      return this.isCopilotAvailable && this.canCreateCopilot;
	    },
	    canCreateChannel() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createChannel);
	    },
	    canCreateConference() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.createConference);
	    },
	    iconStatusClasses() {
	      return {
	        '--default': !this.isLoading,
	        '--loading': this.isLoading
	      };
	    }
	  },
	  created() {
	    this.showCollabPromo = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.createCollabDescription);
	    this.showInvitePromo = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.recentCreateChatInviteUsers);
	  },
	  methods: {
	    onChatCreateClick(type) {
	      im_v2_lib_analytics.Analytics.getInstance().chatCreate.onStartClick(type);
	      this.chatTypeToCreate = type;
	      const promoBannerIsNeeded = im_v2_lib_promo.PromoManager.getInstance().needToShow(this.getPromoType());
	      if (promoBannerIsNeeded) {
	        this.showCreateChatPromo = true;
	        this.showMenu = false;
	        return;
	      }
	      this.startChatCreation();
	      this.showMenu = false;
	    },
	    showCopilotPromoter() {
	      const promoter = new ui_infoHelper.FeaturePromoter({
	        code: im_v2_const.SliderCode.copilotDisabled
	      });
	      promoter.show();
	    },
	    checkCopilotActive() {
	      if (!this.isCopilotActive) {
	        this.showCopilotPromoter();
	        return false;
	      }
	      return true;
	    },
	    async onDefaultCopilotCreateClick() {
	      if (!this.checkCopilotActive()) {
	        return;
	      }
	      im_v2_lib_analytics.Analytics.getInstance().copilot.onCreateDefaultChatInRecent();
	      await this.createCopilotChat(COPILOT_UNIVERSAL_ROLE);
	    },
	    onCopilotRoleSelectClick() {
	      if (!this.checkCopilotActive()) {
	        return;
	      }
	      im_v2_lib_analytics.Analytics.getInstance().copilot.onSelectRoleInRecent();
	      this.showCopilotRolesDialog = true;
	    },
	    async onCopilotDialogSelectRole(role) {
	      await this.createCopilotChat(role.code);
	    },
	    async createCopilotChat(roleCode) {
	      this.showMenu = false;
	      this.isLoading = true;
	      const newDialogId = await this.getCopilotService().createChat({
	        roleCode
	      }).catch(() => {
	        this.isLoading = false;
	      });
	      this.isLoading = false;
	      void im_public.Messenger.openChat(newDialogId);
	    },
	    onPromoContinueClick() {
	      im_v2_lib_promo.PromoManager.getInstance().markAsWatched(this.getPromoType());
	      this.startChatCreation();
	      this.showCreateChatPromo = false;
	      this.showMenu = false;
	      this.chatTypeToCreate = '';
	    },
	    onCollabDescriptionClose() {
	      void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.createCollabDescription);
	      this.showCollabPromo = false;
	    },
	    startChatCreation() {
	      const {
	        name: currentLayoutName,
	        entityId: currentLayoutChatType
	      } = this.$store.getters['application/getLayout'];
	      if (currentLayoutName === im_v2_const.Layout.createChat && currentLayoutChatType === this.chatTypeToCreate) {
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
	    },
	    getCopilotService() {
	      if (!this.copilotService) {
	        this.copilotService = new im_v2_provider_service_copilot.CopilotService();
	      }
	      return this.copilotService;
	    },
	    handleShowPopup() {
	      im_v2_lib_analytics.Analytics.getInstance().chatCreate.onMenuCreateClick();
	      this.showMenu = true;
	    }
	  },
	  template: `
		<div
			class="bx-im-list-container-recent__create-chat_icon"
			:class="{'--active': showMenu}"
			@click="handleShowPopup"
			ref="icon"
		>
			<div
				class="bx-im-list-container-recent__create-chat_icon_status"
				:class="iconStatusClasses"
			></div>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false">
			<MenuItem
				v-if="canCreateChat"
				:icon="MenuItemIcon.chat"
				:title="loc('IM_RECENT_CREATE_GROUP_CHAT_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_GROUP_CHAT_SUBTITLE_MSGVER_1')"
				@click="onChatCreateClick(ChatType.chat)"
			/>
			<MenuItem
				v-if="isCopilotAvailableAndCreatable"
				:icon="MenuItemIcon.copilot"
				:title="loc('IM_RECENT_CREATE_COPILOT_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_COPILOT_SUBTITLE_MSGVER_1')"
				@click.stop="onDefaultCopilotCreateClick"
			>
				<template #after-content>
					<CopilotRoleSelectionButton @click.stop="onCopilotRoleSelectClick" />
				</template>
			</MenuItem>
			<MenuItem
				v-if="canCreateChannel"
				:icon="MenuItemIcon.channel"
				:title="loc('IM_RECENT_CREATE_CHANNEL_TITLE_V2')"
				:subtitle="loc('IM_RECENT_CREATE_CHANNEL_SUBTITLE_MSGVER_1')"
				@click="onChatCreateClick(ChatType.channel)"
			/>
			<MenuItem
				v-if="collabAvailable"
				:icon="MenuItemIcon.collab"
				:title="loc('IM_RECENT_CREATE_COLLAB_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_COLLAB_SUBTITLE_MSGVER_1')"
				@click="onChatCreateClick(ChatType.collab)"
			>
				<template #after-title>
					<NewBadge />
				</template>
				<template #below-content>
					<DescriptionBanner v-if="showCollabPromo" @close="onCollabDescriptionClose" />
				</template>
			</MenuItem>
			<MenuItem
				v-if="canCreateConference"
				:icon="MenuItemIcon.conference"
				:title="loc('IM_RECENT_CREATE_CONFERENCE_TITLE')"
				:subtitle="loc('IM_RECENT_CREATE_CONFERENCE_SUBTITLE_MSGVER_1')"
				:withBottomBorder="showInvitePromo"
				@click="onChatCreateClick(ChatType.videoconf)"
			/>
			<InvitePromo v-if="showInvitePromo" @close="showInvitePromo = false" />
			<template #footer>
				<CreateChatHelp @articleOpen="showMenu = false" />
			</template>
		</MessengerMenu>
		<CreateChatPromo
			v-if="showCreateChatPromo"
			:chatType="chatTypeToCreate"
			@continue="onPromoContinueClick"
			@close="showCreateChatPromo = false"
		/>
		<CopilotRolesDialog
			v-if="showCopilotRolesDialog"
			@selectRole="onCopilotDialogSelectRole"
			@close="showCopilotRolesDialog = false"
		/>
	`
	};

	// @vue/component
	const RecentListContainer = {
	  name: 'RecentListContainer',
	  components: {
	    HeaderMenu,
	    CreateChatMenu,
	    ChatSearchInput: im_v2_component_search.ChatSearchInput,
	    RecentList: im_v2_component_list_items_recent.RecentList,
	    ChatSearch: im_v2_component_search.ChatSearch
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
	        layoutName: im_v2_const.Layout.chat,
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
	      if (this.searchMode && !clickOnRecentContainer) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.search.close);
	      }
	    },
	    onLoading(value) {
	      this.isSearchLoading = value;
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
						:query="searchQuery"
						@loading="onLoading"
					/>
					<RecentList v-show="!searchMode && !unreadOnlyMode" @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`
	};

	exports.RecentListContainer = RecentListContainer;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Event,BX.Messenger.v2.Component.List,BX.Messenger.v2.Component,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.UI,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.List,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=recent-container.bundle.js.map
