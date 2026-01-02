/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_lib_utils,im_v2_provider_service_recent,im_v2_lib_menu,im_v2_css_tokens,ui_designTokens_air,main_core,ui_iconSet_api_vue,im_public,im_v2_application_core,im_v2_const,im_v2_component_elements_avatar) {
	'use strict';

	const NavigationItemToIcon = Object.freeze({
	  [im_v2_const.NavigationMenuItem.notification]: ui_iconSet_api_vue.Outline.NOTIFICATION,
	  [im_v2_const.NavigationMenuItem.copilot]: ui_iconSet_api_vue.Outline.COPILOT,
	  [im_v2_const.NavigationMenuItem.openlines]: ui_iconSet_api_vue.Outline.OPEN_CHANNELS,
	  [im_v2_const.NavigationMenuItem.openlinesV2]: ui_iconSet_api_vue.Outline.OPEN_CHANNELS
	});
	const ICON_SIZE = 24;

	// @vue/component
	const CompactNavigationItem = {
	  name: 'CompactNavigationItem',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    id: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    ICON_SIZE: () => ICON_SIZE,
	    Color: () => im_v2_const.Color,
	    NavigationItemToIcon: () => NavigationItemToIcon,
	    counter() {
	      var _this$$store$getters$;
	      const counterToItemId = {
	        [im_v2_const.NavigationMenuItem.notification]: 'notifications/getCounter',
	        [im_v2_const.NavigationMenuItem.copilot]: 'counters/getTotalCopilotCounter',
	        [im_v2_const.NavigationMenuItem.openlines]: 'counters/getTotalLinesCounter',
	        [im_v2_const.NavigationMenuItem.openlinesV2]: 'counters/getTotalLinesCounter'
	      };
	      return (_this$$store$getters$ = this.$store.getters[counterToItemId[this.id]]) != null ? _this$$store$getters$ : 0;
	    },
	    hasCounter() {
	      return this.counter > 0;
	    },
	    formattedCounter() {
	      if (!this.hasCounter) {
	        return '';
	      }
	      return this.counter > 99 ? '99+' : String(this.counter);
	    },
	    iconColorToken() {
	      if (this.counter > 0) {
	        return 'var(--ui-color-design-outline-content)';
	      }
	      return 'var(--ui-color-design-outline-na-content)';
	    }
	  },
	  methods: {
	    onNavigationItemClick() {
	      void im_public.Messenger.openNavigationItem({
	        id: this.id
	      });
	    }
	  },
	  template: `
		<div class="bx-im-compact-navigation__icon --ui-hoverable" @click="onNavigationItemClick">
			<BIcon
				:key="id"
				:name="NavigationItemToIcon[id]"
				:hoverable-alt="true"
				:color="iconColorToken"
				:size="ICON_SIZE"
			/>
			<div
				v-if="hasCounter"
				class="bx-im-compact-navigation__icon-counter"
			>
				{{ formattedCounter }}
			</div>
		</div>
	`
	};

	const CompactNavigationItems = [im_v2_const.NavigationMenuItem.notification, im_v2_const.NavigationMenuItem.copilot, im_v2_const.NavigationMenuItem.openlines, im_v2_const.NavigationMenuItem.openlinesV2];
	// @vue/component
	const CompactNavigation = {
	  name: 'CompactNavigation',
	  components: {
	    CompactNavigationItem
	  },
	  computed: {
	    availableNavigationItems() {
	      const settings = main_core.Extension.getSettings('im.v2.component.list.items.recent-compact');
	      const items = settings.get('navigationItems', []);
	      return items.map(item => item.id);
	    },
	    preparedNavigationItems() {
	      return CompactNavigationItems.filter(item => this.availableNavigationItems.includes(item));
	    }
	  },
	  template: `
		<div class="bx-im-compact-navigation__container">
			<div class="bx-im-compact-navigation__items">
				<CompactNavigationItem
					v-for="navigationItemId in preparedNavigationItems"
					:id="navigationItemId"
					:key="navigationItemId"
				/>
			</div>
			<div class="bx-im-compact-navigation__delimiter"></div>
		</div>
	`
	};

	// @vue/component
	const RecentItem = {
	  name: 'RecentItem',
	  components: {
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar
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
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    recentItem() {
	      return this.item;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
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
	      return this.totalCounter > 99 ? '99+' : this.totalCounter.toString();
	    },
	    wrapClasses() {
	      return {
	        '--pinned': this.recentItem.pinned
	      };
	    },
	    itemClasses() {
	      return {
	        '--no-counter': this.totalCounter === 0
	      };
	    },
	    getAvatarType() {
	      return Number.parseInt(this.recentItem.dialogId, 10) === im_v2_application_core.Core.getUserId() ? im_v2_component_elements_avatar.ChatAvatarType.notes : '';
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  // language=Vue
	  template: `
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-recent-compact-item__wrap">
			<div :class="itemClasses" class="bx-im-list-recent-compact-item__container" ref="container">
				<div class="bx-im-list-recent-compact-item__avatar_container">
					<div v-if="invitation.isActive" class="bx-im-list-recent-compact-item__avatar_invitation"></div>
					<ChatAvatar 
						v-else 
						:contextDialogId="recentItem.dialogId"
						:avatarDialogId="recentItem.dialogId"
						:size="AvatarSize.M" 
						:withSpecialTypes="false"
						:customType="getAvatarType"
					/>
					<div v-if="totalCounter > 0" :class="{'--muted': isChatMuted}" class="bx-im-list-recent-compact-item__avatar_counter">
						{{ formattedCounter }}
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
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar
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
	    activeCall() {
	      return this.item;
	    }
	  },
	  methods: {
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
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div :data-id="activeCall.dialogId" class="bx-im-list-recent-compact-item__wrap">
			<div @click="onClick" class="bx-im-list-recent-compact-item__container">
				<div class="bx-im-list-recent-compact-item__avatar_container">
					<ChatAvatar 
						:avatarDialogId="activeCall.dialogId"
						:contextDialogId="activeCall.dialogId"
						:size="AvatarSize.M" 
						:withSpecialTypes="false" 
					/>
					<div class="bx-im-list-recent-compact-active-call__icon" :class="'--' + activeCall.state"></div>
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
		<div class="bx-im-list-recent-compact__empty">
			{{ loc('IM_LIST_RECENT_COMPACT_EMPTY') }}
		</div>
	`
	};

	// @vue/component
	const RecentList = {
	  name: 'RecentList',
	  components: {
	    RecentItem,
	    ActiveCall,
	    EmptyState,
	    CompactNavigation
	  },
	  emits: ['chatClick'],
	  data() {
	    return {};
	  },
	  computed: {
	    collection() {
	      return this.getRecentService().getCollection();
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
	    }
	  },
	  async created() {
	    this.contextMenuManager = new im_v2_lib_menu.RecentMenu();
	    this.managePreloadedList();
	    await this.getRecentService().loadFirstPage();
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  methods: {
	    onClick(item) {
	      im_public.Messenger.openChat(item.dialogId);
	    },
	    onRightClick(item, event) {
	      if (im_v2_lib_utils.Utils.key.isCombination(event, 'Alt+Shift')) {
	        return;
	      }
	      const context = {
	        dialogId: item.dialogId,
	        recentItem: item,
	        compactMode: true
	      };
	      this.contextMenuManager.openMenu(context, event.currentTarget);
	      event.preventDefault();
	    },
	    managePreloadedList() {
	      const {
	        preloadedList
	      } = im_v2_application_core.Core.getApplicationData();
	      if (!preloadedList) {
	        return;
	      }
	      this.getRecentService().setPreloadedData(preloadedList);
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
		<div class="bx-im-messenger__scope bx-im-list-recent-compact__container">
			<CompactNavigation />
			<div v-if="activeCalls.length > 0" class="bx-im-list-recent-compact__calls_container">
				<ActiveCall
					v-for="activeCall in activeCalls"
					:key="activeCall.dialogId"
					:item="activeCall"
					@click="onClick"
				/>
			</div>
			<div class="bx-im-list-recent-compact__scroll-container">
				<div v-if="pinnedItems.length > 0" class="bx-im-list-recent-compact__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-recent-compact__general_container">
					<RecentItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>	
				<EmptyState v-if="collection.length === 0" />
			</div>
		</div>
	`
	};

	exports.RecentList = RecentList;

}((this.BX.Messenger.v2.Component.List = this.BX.Messenger.v2.Component.List || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Css,BX,BX,BX.UI.IconSet,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Elements));
//# sourceMappingURL=recent-compact.bundle.js.map
