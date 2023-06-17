this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_designTokens,im_v2_lib_oldChatEmbedding_menu,ui_fonts_opensans,im_v2_lib_logger,ui_dexie,im_v2_lib_utils,im_v2_component_oldChatEmbedding_elements,main_core,main_core_events,im_v2_const) {
	'use strict';

	class SearchContextMenu extends im_v2_lib_oldChatEmbedding_menu.RecentMenu {
	  getMenuItems() {
	    return [this.getSendMessageItem(), this.getCallItem(), this.getHistoryItem(), this.getOpenProfileItem()];
	  }
	}

	const CarouselUser = {
	  name: 'CarouselUser',
	  components: {
	    Avatar: im_v2_component_oldChatEmbedding_elements.Avatar
	  },
	  props: {
	    user: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    name() {
	      return this.user.dialog.name.split(' ')[0];
	    },
	    isExtranet() {
	      return this.user.user.extranet;
	    },
	    AvatarSize: () => im_v2_component_oldChatEmbedding_elements.AvatarSize
	  },
	  created() {
	    this.contextMenuManager = new SearchContextMenu(this.$Bitrix);
	  },
	  beforeUnmount() {
	    this.contextMenuManager.destroy();
	  },
	  methods: {
	    onClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	        dialogId: this.user.dialogId,
	        chat: this.user.dialog,
	        user: this.user.user
	      });
	      BX.MessengerProxy.clearSearchInput();
	    },
	    onRightClick(event) {
	      if (event.altKey && event.shiftKey) {
	        return;
	      }
	      const item = {
	        dialogId: this.user.dialogId
	      };
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.openContextMenu, {
	        item,
	        event
	      });
	    }
	  },
	  template: `
		<div class="bx-messenger-carousel-item" @click="onClick" @click.right.prevent="onRightClick">
			<Avatar :dialogId="user.dialogId" :size="AvatarSize.L" />
			<div :class="[isExtranet ? 'bx-messenger-carousel-item-extranet' : '', 'bx-messenger-carousel-item-title']">
				{{name}}
			</div>
		</div>
	`
	};

	const recentUsersLimit = 5;

	// @vue/component
	const RecentUsersCarousel = {
	  name: 'RecentUsersCarousel',
	  components: {
	    CarouselUser
	  },
	  props: {
	    title: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  computed: {
	    users() {
	      const recentUsers = [];
	      this.$store.getters['recent/getSortedCollection'].forEach(recentItem => {
	        const dialog = this.$store.getters['dialogues/get'](recentItem.dialogId, true);
	        const user = this.$store.getters['users/get'](recentItem.dialogId, true);
	        recentUsers.push({
	          ...recentItem,
	          dialog,
	          user
	        });
	      });
	      const usersWithoutBotsAndCurrentUser = recentUsers.filter(item => {
	        return item.dialog.type === 'user' && !item.user.bot && item.user.id !== this.currentUserId;
	      });
	      return usersWithoutBotsAndCurrentUser.slice(0, recentUsersLimit);
	    },
	    currentUserId() {
	      return this.$store.state.application.common.userId;
	    }
	  },
	  // language=Vue
	  template: `
		<div v-if="title" class="bx-messenger-recent-users-carousel-title">{{title}}</div>
		<div class="bx-messenger-recent-users-carousel">
			<CarouselUser v-for="user in users" :key="user.dialogId" :user="user" />
		</div>
	`
	};

	const SearchResultSection = {
	  name: 'SearchResultSection',
	  props: {
	    component: {
	      type: Object,
	      required: true
	    },
	    items: {
	      type: Object,
	      // Map<string, SearchItem>
	      required: true
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    showMoreButton: {
	      type: Boolean,
	      default: true,
	      required: false
	    },
	    minItems: {
	      type: Number,
	      default: 10,
	      required: false
	    },
	    maxItems: {
	      type: Number,
	      default: 50,
	      required: false
	    }
	  },
	  data: function () {
	    return {
	      expanded: false
	    };
	  },
	  computed: {
	    showMore() {
	      if (!this.showMoreButton) {
	        return false;
	      }
	      return this.items.size > this.minItems;
	    },
	    showMoreButtonText() {
	      return this.expanded ? this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_LESS') : this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_TITLE_SHOW_MORE');
	    },
	    sectionItems() {
	      const itemsFromMap = [...this.items.values()];
	      if (!this.showMoreButton) {
	        return itemsFromMap;
	      }
	      return this.expanded ? itemsFromMap.slice(0, this.maxItems) : itemsFromMap.slice(0, this.minItems);
	    }
	  },
	  methods: {
	    onShowMore() {
	      this.expanded = !this.expanded;
	    }
	  },
	  template: `
		<div class="bx-messenger-search-result-section-wrapper">
			<div class="bx-messenger-search-result-section-title">{{title}}</div>
			<div>
				<component :is="component" v-for="item in sectionItems" :key="item.getEntityFullId()" :item="item" />
			</div>
			<div v-if="showMore" class="bx-messenger-search-result-section-show-more" @click.prevent="onShowMore">
				{{ showMoreButtonText }}
			</div>
		</div>
	`
	};

	const EntityIdTypes = Object.freeze({
	  user: 'user',
	  bot: 'im-bot',
	  chat: 'im-chat',
	  chatUser: 'im-chat-user',
	  department: 'department',
	  network: 'imbot-network'
	});

	const SearchUtils = {
	  getWordsFromString(string) {
	    const clearedString = string.replaceAll('(', ' ').replaceAll(')', ' ').replaceAll('[', ' ').replaceAll(']', ' ').replaceAll('{', ' ').replaceAll('}', ' ').replaceAll('<', ' ').replaceAll('>', ' ').replaceAll('-', ' ').replaceAll('#', ' ').replaceAll('"', ' ').replaceAll('\'', ' ').replace(/\s\s+/g, ' ');
	    return clearedString.split(' ').filter(word => word !== '');
	  },
	  getTypeByEntityId(entityId) {
	    switch (entityId) {
	      case EntityIdTypes.user:
	      case EntityIdTypes.bot:
	        return 'user';
	      case EntityIdTypes.chat:
	      case EntityIdTypes.chatUser:
	        return 'chat';
	      case EntityIdTypes.department:
	        return 'department';
	      case EntityIdTypes.network:
	        return 'network';
	      default:
	        throw new Error(`Unknown entity id: ${entityId}`);
	    }
	  },
	  createItemMap(items) {
	    const map = new Map();
	    items.forEach(item => {
	      const mapItem = new SearchItem(item);
	      map.set(mapItem.getEntityFullId(), mapItem);
	    });
	    return map;
	  },
	  getFirstItemFromMap(map) {
	    const iterator = map.entries();
	    const firstIteration = iterator.next();
	    const firstItem = firstIteration.value;
	    const [, content] = firstItem;
	    return content;
	  },
	  convertKeysToLowerCase(object) {
	    const result = {};
	    Object.keys(object).forEach(key => {
	      if (main_core.Type.isObject(object[key]) && !main_core.Type.isArray(object[key])) {
	        result[key.toLowerCase()] = this.convertKeysToLowerCase(object[key]);
	      } else {
	        result[key.toLowerCase()] = object[key];
	      }
	    });
	    return result;
	  },
	  prepareRecentItems(recentItems) {
	    if (!recentItems) {
	      return [];
	    }
	    return recentItems.map(item => {
	      const [entityId, id] = item;
	      const type = SearchUtils.getTypeByEntityId(entityId);
	      return {
	        cacheId: `${type}|${id}`,
	        date: new Date()
	      };
	    });
	  }
	};

	class SearchItem {
	  constructor(itemOptions) {
	    this.entityId = null;
	    this.entityType = null;
	    this.dialogId = null;
	    this.title = null;
	    this.subtitle = null;
	    this.name = null;
	    this.lastName = null;
	    this.secondName = null;
	    this.position = null;
	    this.avatar = null;
	    this.avatarOptions = null;
	    this.customSort = 0;
	    this.contextSort = 0;
	    this.rawData = null;
	    this.setRawData(itemOptions);
	    this.setId(itemOptions);
	    this.setDialogId(itemOptions);
	    this.setEntityId(itemOptions);
	    this.setEntityType(itemOptions);
	    this.setTitle(itemOptions);
	    this.setSubtitle(itemOptions);
	    this.setName(itemOptions);
	    this.setLastName(itemOptions);
	    this.setSecondName(itemOptions);
	    this.setPosition(itemOptions);
	    this.setAvatar(itemOptions);
	    this.setAvatarOptions(itemOptions);
	    this.setContextSort(itemOptions);
	  }
	  isFromProviderResponse(itemOptions) {
	    return main_core.Type.isString(itemOptions.entityId) && !main_core.Type.isNil(itemOptions.id);
	  }
	  isFromModel(itemOptions) {
	    return main_core.Type.isString(itemOptions.dialogId) && main_core.Type.isObject(itemOptions.dialog);
	  }
	  setId(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.id = itemOptions.id;
	    } else if (this.isFromModel(itemOptions)) {
	      this.id = itemOptions.dialogId.startsWith('chat') ? itemOptions.dialogId.slice(4) : itemOptions.dialogId;
	    }
	  }
	  setDialogId(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      var _itemOptions$customDa, _itemOptions$customDa2, _itemOptions$customDa3, _itemOptions$customDa4;
	      if (((_itemOptions$customDa = itemOptions.customData) == null ? void 0 : (_itemOptions$customDa2 = _itemOptions$customDa.imChat) == null ? void 0 : _itemOptions$customDa2.ID) > 0) {
	        this.dialogId = `chat${itemOptions.customData.imChat.ID}`;
	      } else if (((_itemOptions$customDa3 = itemOptions.customData) == null ? void 0 : (_itemOptions$customDa4 = _itemOptions$customDa3.imUser) == null ? void 0 : _itemOptions$customDa4.ID) > 0) {
	        this.dialogId = itemOptions.customData.imUser.ID.toString();
	      }
	    } else if (this.isFromModel(itemOptions)) {
	      this.dialogId = itemOptions.dialogId;
	    }
	  }
	  setEntityId(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.entityId = itemOptions.entityId;
	    } else if (this.isFromModel(itemOptions)) {
	      if (!itemOptions.user) {
	        this.entityId = EntityIdTypes.chat;
	      } else if (itemOptions.user.bot) {
	        this.entityId = EntityIdTypes.bot;
	      } else {
	        this.entityId = EntityIdTypes.user;
	      }
	    }
	  }
	  setEntityType(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.entityType = itemOptions.entityType;
	    }
	  }
	  setTitle(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.title = itemOptions.title;
	    } else if (this.isFromModel(itemOptions)) {
	      this.title = itemOptions.dialog.name;
	    }
	  }
	  setSubtitle(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.subtitle = itemOptions.subtitle;
	    }
	  }
	  setName(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      var _itemOptions$customDa5;
	      this.name = (_itemOptions$customDa5 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa5.name;
	    } else if (this.isFromModel(itemOptions)) {
	      var _itemOptions$user;
	      this.name = (_itemOptions$user = itemOptions.user) == null ? void 0 : _itemOptions$user.firstName;
	    }
	  }
	  setLastName(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      var _itemOptions$customDa6;
	      this.lastName = (_itemOptions$customDa6 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa6.lastName;
	    } else if (this.isFromModel(itemOptions)) {
	      var _itemOptions$user2;
	      this.lastName = (_itemOptions$user2 = itemOptions.user) == null ? void 0 : _itemOptions$user2.lastName;
	    }
	  }
	  setSecondName(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      var _itemOptions$customDa7;
	      this.secondName = (_itemOptions$customDa7 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa7.secondName;
	    }
	  }
	  setPosition(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      var _itemOptions$customDa8;
	      this.position = (_itemOptions$customDa8 = itemOptions.customData) == null ? void 0 : _itemOptions$customDa8.position;
	    } else if (this.isFromModel(itemOptions)) {
	      var _itemOptions$user3;
	      this.position = (_itemOptions$user3 = itemOptions.user) == null ? void 0 : _itemOptions$user3.workPosition;
	    }
	  }
	  setAvatar(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.avatar = itemOptions.avatar;
	    }
	  }
	  setAvatarOptions(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.avatarOptions = itemOptions.avatarOptions;
	    }
	  }
	  setContextSort(itemOptions) {
	    if (this.isFromProviderResponse(itemOptions)) {
	      this.contextSort = itemOptions.contextSort;
	    }
	  }
	  setRawData(itemOptions) {
	    this.rawData = itemOptions;
	  }
	  getId() {
	    return this.id;
	  }
	  getEntityId() {
	    return this.entityId;
	  }
	  getEntityType() {
	    return this.entityType;
	  }
	  getEntityFullId() {
	    const type = SearchUtils.getTypeByEntityId(this.entityId);
	    return `${type}|${this.id}`;
	  }
	  getTitle() {
	    return this.title;
	  }
	  getSubtitle() {
	    return this.subtitle;
	  }
	  getName() {
	    return this.name;
	  }
	  getLastName() {
	    return this.lastName;
	  }
	  getSecondName() {
	    return this.secondName;
	  }
	  getPosition() {
	    return this.position;
	  }
	  getCustomData() {
	    return this.rawData.customData;
	  }
	  getDialogId() {
	    return this.dialogId;
	  }
	  getAvatar() {
	    return this.avatar;
	  }
	  getAvatarOptions() {
	    return this.avatarOptions;
	  }
	  getContextSort() {
	    return this.contextSort ? this.contextSort : 0;
	  }
	  addCustomSort(value) {
	    this.customSort += value;
	  }
	  getCustomSort() {
	    return this.customSort;
	  }
	  isUser() {
	    if (this.isFromProviderResponse(this.rawData)) {
	      var _this$rawData$customD;
	      return !!((_this$rawData$customD = this.rawData.customData) != null && _this$rawData$customD.imUser) && this.rawData.customData.imUser.ID > 0;
	    }
	    return !!this.rawData.user;
	  }
	  isChat() {
	    return !this.isUser();
	  }
	  isExtranet() {
	    if (this.isFromProviderResponse(this.rawData)) {
	      var _this$rawData$customD2, _this$rawData$customD3, _this$rawData$customD4, _this$rawData$customD5;
	      return !!((_this$rawData$customD2 = this.rawData.customData) != null && (_this$rawData$customD3 = _this$rawData$customD2.imUser) != null && _this$rawData$customD3.EXTRANET) || !!((_this$rawData$customD4 = this.rawData.customData) != null && (_this$rawData$customD5 = _this$rawData$customD4.imChat) != null && _this$rawData$customD5.EXTRANET);
	    } else if (this.isFromModel(this.rawData)) {
	      var _this$rawData$user;
	      return !!((_this$rawData$user = this.rawData.user) != null && _this$rawData$user.extranet) || !!this.rawData.dialog.extranet;
	    }
	  }
	  getUserCustomData() {
	    var _this$rawData$customD6;
	    return (_this$rawData$customD6 = this.rawData.customData) != null && _this$rawData$customD6.imUser ? this.rawData.customData.imUser : null;
	  }
	  getChatCustomData() {
	    var _this$rawData$customD7;
	    return (_this$rawData$customD7 = this.rawData.customData) != null && _this$rawData$customD7.imChat ? this.rawData.customData.imChat : null;
	  }
	  isOpeLinesType() {
	    return this.getEntityType() === 'LINES';
	  }
	  getOpenlineEntityId() {
	    var _this$rawData$customD8, _this$rawData$customD9;
	    if (!this.isOpeLinesType()) {
	      return '';
	    }
	    const entityId = (_this$rawData$customD8 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD9 = _this$rawData$customD8.imChat) == null ? void 0 : _this$rawData$customD9.ENTITY_ID;
	    return entityId.toString().split('|')[0];
	  }
	  getAvatarColor() {
	    let color = '';
	    if (this.isFromProviderResponse(this.rawData)) {
	      if (this.isUser()) {
	        var _this$rawData$customD10, _this$rawData$customD11, _this$rawData$customD12;
	        color = (_this$rawData$customD10 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD11 = _this$rawData$customD10.imUser) == null ? void 0 : (_this$rawData$customD12 = _this$rawData$customD11.COLOR) == null ? void 0 : _this$rawData$customD12.toString();
	      } else if (this.isChat()) {
	        var _this$rawData$customD13, _this$rawData$customD14, _this$rawData$customD15;
	        color = (_this$rawData$customD13 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD14 = _this$rawData$customD13.imChat) == null ? void 0 : (_this$rawData$customD15 = _this$rawData$customD14.COLOR) == null ? void 0 : _this$rawData$customD15.toString();
	      }
	    } else if (this.isFromModel(this.rawData)) {
	      color = this.rawData.dialog.color.toString();
	    }
	    return color;
	  }
	  isCrmSession() {
	    if (this.isFromProviderResponse(this.rawData) && this.isOpeLinesType()) {
	      var _this$rawData$customD16, _this$rawData$customD17;
	      const sessionData = (_this$rawData$customD16 = this.rawData.customData) == null ? void 0 : (_this$rawData$customD17 = _this$rawData$customD16.imChat) == null ? void 0 : _this$rawData$customD17.ENTITY_DATA_1.toString().split('|');
	      return sessionData[0] === 'Y';
	    }
	    return false;
	  }
	}

	const OpenlineAvatarType = {
	  lines: 'lines',
	  network: 'network',
	  livechat: 'livechat',
	  whatsappbytwilio: 'whatsappbytwilio',
	  avito: 'avito',
	  viber: 'viber',
	  telegrambot: 'telegrambot',
	  imessage: 'imessage',
	  wechat: 'wechat',
	  yandex: 'yandex',
	  vkgroup: 'vkgroup',
	  ok: 'ok',
	  olx: 'olx',
	  facebook: 'facebook',
	  facebookcomments: 'facebookcomments',
	  fbinstagramdirect: 'fbinstagramdirect',
	  fbinstagram: 'fbinstagram',
	  notifications: 'notifications'
	};
	const AvatarOpenline = {
	  name: 'Avatar',
	  props: {
	    item: {
	      type: SearchItem,
	      required: true
	    },
	    size: {
	      type: String,
	      default: im_v2_component_oldChatEmbedding_elements.AvatarSize.M
	    }
	  },
	  computed: {
	    openlineType() {
	      return this.item.getOpenlineEntityId();
	    },
	    chatAvatarStyle() {
	      return {
	        backgroundImage: `url('${this.item.getAvatar()}')`
	      };
	    },
	    chatTypeIconClasses() {
	      if (OpenlineAvatarType[this.openlineType]) {
	        return `bx-im-search-avatar-openline__icon-${this.openlineType}`;
	      }
	      return 'bx-im-search-avatar-openline__icon-lines';
	    },
	    needCrmBadge() {
	      if (!this.isCrmAvailable) {
	        return false;
	      }
	      return this.item.isCrmSession();
	    }
	  },
	  created() {
	    this.isCrmAvailable = main_core.Extension.getSettings('im.v2.component.old-chat-embedding.search').get('isCrmAvailable', false);
	  },
	  template: `
		<div 
			:title="item.getTitle()" 
			:class="'bx-im-search-avatar-openline__size-' + size.toLowerCase()" 
			class="bx-im-search-avatar-openline__wrap"
		>
			<div 
				v-if="item.getAvatar()" 
				:style="chatAvatarStyle" 
				class="bx-im-search-avatar-openline__content bx-im-search-avatar-openline__image"
			></div>
			<div 
				v-else 
				:style="{backgroundColor: this.item.getAvatarColor()}" 
				:class="chatTypeIconClasses" 
				class="bx-im-search-avatar-openline__content bx-im-search-avatar-openline__icon"
			></div>
			<div v-if="needCrmBadge" class="bx-im-search-avatar-openline__crm-badge"></div>
		</div>
	`
	};

	const SearchResultOpenlineItem = {
	  name: 'SearchResultOpenlineItem',
	  components: {
	    AvatarOpenline
	  },
	  props: {
	    item: {
	      type: SearchItem,
	      required: true
	    }
	  },
	  computed: {
	    title() {
	      return im_v2_lib_utils.Utils.text.htmlspecialcharsback(this.item.getTitle());
	    }
	  },
	  methods: {
	    onClick(event) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	        dialogId: this.item.getDialogId(),
	        chat: SearchUtils.convertKeysToLowerCase(this.item.getChatCustomData())
	      });
	      if (!event.altKey) {
	        BX.MessengerProxy.clearSearchInput();
	      }
	    }
	  },
	  template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<AvatarOpenline :item="item" size="L"></AvatarOpenline>
			</div>
			<div class="bx-im-search-result-item-content bx-im-search-result-item-department-content">
				<div v class="bx-im-component-chat-title-wrap">
					<div class="bx-im-component-chat-name-text" :title="item.getTitle()">{{title}}</div>
				</div>
			</div>
		</div>
	`
	};

	const SearchResultNetworkItem = {
	  name: 'SearchResultNetworkItem',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  data: function () {
	    return {
	      isLoading: false
	    };
	  },
	  computed: {
	    hasAvatar() {
	      return this.item.getAvatar() !== '';
	    },
	    avatarStyle() {
	      if (!this.hasAvatar) {
	        return {
	          backgroundColor: this.item.getAvatarOptions().color
	        };
	      }
	      return {
	        backgroundImage: `url('${this.item.getAvatar()}')`
	      };
	    },
	    title() {
	      return im_v2_lib_utils.Utils.text.htmlspecialcharsback(this.item.getTitle());
	    }
	  },
	  methods: {
	    onClick(event) {
	      this.isLoading = true;
	      const networkCode = this.item.getId().replace('networkLines', '');
	      main_core_events.EventEmitter.emitAsync(im_v2_const.EventType.search.openNetworkItem, networkCode).then(eventResult => {
	        if (eventResult[0].error) {
	          console.error('Error:', eventResult[0].error);
	          this.isLoading = false;
	          return;
	        }
	        const dialogId = eventResult[0].id.toString();
	        const user = this.$store.getters['users/get'](dialogId, true);
	        const dialog = this.$store.getters['dialogues/get'](dialogId, true);
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	          dialogId: dialogId,
	          chat: dialog,
	          user: user
	        });
	        this.isLoading = false;
	        if (!event.altKey) {
	          BX.MessengerProxy.clearSearchInput();
	        }
	      }).catch(error => {
	        console.error(error);
	        this.isLoading = false;
	      });
	    }
	  },
	  template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<div :title="item.getTitle()" class="bx-im-component-avatar-wrap bx-im-component-avatar-size-l">
					<div
						class="bx-im-component-avatar-content bx-im-component-avatar-image"
						:class="[hasAvatar ? '' : 'bx-im-search-network-icon']"
						:style="avatarStyle"
					></div>
				</div>
			</div>
			<div class="bx-im-search-result-item-content">
				<div v class="bx-im-component-chat-title-wrap">
					<div class="bx-im-component-chat-name-left-icon bx-im-component-chat-name-left-icon-network"></div>
					<div class="bx-im-component-chat-name-text bx-im-search-network-title">
						{{title}}
					</div>
				</div>
				<div class="bx-im-search-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-text">{{ item.getSubtitle() }}</div>
					</div>
					<div v-if="isLoading" class="bx-search-loader bx-search-loader-small-size"></div>
				</div>
			</div>
		</div>
	`
	};

	const SearchResultItem = {
	  name: 'SearchResultItem',
	  components: {
	    Avatar: im_v2_component_oldChatEmbedding_elements.Avatar,
	    ChatTitle: im_v2_component_oldChatEmbedding_elements.ChatTitle
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    child: {
	      type: Boolean,
	      default: false,
	      required: false
	    }
	  },
	  computed: {
	    dialogId() {
	      return this.item.getDialogId();
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    userItemText() {
	      if (!this.isUser) {
	        return '';
	      }
	      const status = this.$store.getters['users/getLastOnline'](this.dialogId);
	      if (status) {
	        return status;
	      }
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },
	    chatItemText() {
	      if (this.isUser) {
	        return '';
	      }
	      if (this.dialog.type === im_v2_const.DialogType.open) {
	        return this.$Bitrix.Loc.getMessage('IM_SEARCH_ITEM_CHAT_TYPE_OPEN');
	      }
	      return this.$Bitrix.Loc.getMessage('IM_SEARCH_ITEM_CHAT_TYPE_GROUP');
	    },
	    searchEntityId() {
	      if (this.isUser) {
	        return this.user.bot ? 'im-bot' : 'user';
	      }
	      return 'im-chat';
	    },
	    searchItemId() {
	      if (this.dialogId.startsWith('chat')) {
	        return Number.parseInt(this.dialogId.slice(4), 10);
	      }
	      return Number.parseInt(this.dialogId, 10);
	    },
	    AvatarSize: () => im_v2_component_oldChatEmbedding_elements.AvatarSize
	  },
	  methods: {
	    onClick(event) {
	      const selectedItem = {
	        id: this.searchItemId,
	        entityId: this.searchEntityId,
	        dialogId: this.dialogId
	      };
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.selectItem, {
	        selectedItem: selectedItem,
	        onlyOpen: false,
	        nativeEvent: event
	      });
	    },
	    onRightClick(event) {
	      if (event.altKey && event.shiftKey) {
	        return;
	      }
	      const item = {
	        dialogId: this.dialogId
	      };
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.openContextMenu, {
	        item,
	        event
	      });
	    }
	  },
	  template: `
		<div @click="onClick" @click.right.prevent="onRightClick" class="bx-im-search-item" :class="[this.child ? 'bx-im-search-sub-item' : '']">
			<div class="bx-im-search-avatar-wrap">
				<Avatar :dialogId="dialogId" :size="AvatarSize.L" />
			</div>
			<div v-if="isUser" class="bx-im-search-result-item-content">
				<ChatTitle :dialogId="dialogId" />
				<div class="bx-im-search-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-text">
							{{ userItemText }}
						</div>
					</div>
				</div>
			</div>
			<div v-else class="bx-im-search-result-item-content">
				<ChatTitle :dialogId="dialogId" />
				<div class="bx-im-search-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-text">{{ chatItemText }}</div>
					</div>
				</div>
			</div>
		</div>
	`
	};

	class SearchCache {
	  constructor(userId) {
	    this.userId = userId;
	    /** @type {Dexie} */
	    this.db = new ui_dexie.Dexie('bx-im-search-results');
	    this.db.version(2).stores({
	      items: 'id, *title, *name, *lastName, *secondName, *position, date',
	      recentItems: '++id, cacheId, date',
	      settings: '&name'
	    }).upgrade(transaction => {
	      const clearItemsPromise = transaction.table('items').clear();
	      const clearRecentItemsPromise = transaction.table('recentItems').clear();
	      return ui_dexie.Dexie.Promise.all([clearItemsPromise, clearRecentItemsPromise]);
	    });
	    this.db.version(3).stores({
	      items: 'id, *title, *name, *lastName, *position, date',
	      recentItems: '++id, cacheId, date',
	      settings: '&name'
	    });
	    this.checkTables();
	    this.onAccessDeniedHandler = this.onAccessDenied.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onAccessDeniedHandler);
	  }
	  checkTables() {
	    this.db.open();
	    this.db.on('ready', () => {
	      return this.db.transaction('rw', this.db.settings, this.db.items, this.db.recentItems, () => {
	        return this.db.settings.where('name').equals('userId').first();
	      }).then(settings => {
	        const promises = [];
	        if ((settings == null ? void 0 : settings.value) !== this.userId) {
	          const clearItemsPromise = this.db.items.clear();
	          const clearRecentItemsPromise = this.db.recentItems.clear();
	          promises.push(clearItemsPromise, clearRecentItemsPromise);
	        }
	        return ui_dexie.Dexie.Promise.all(promises);
	      }).then(() => {
	        return this.db.settings.put({
	          name: 'userId',
	          value: this.userId
	        });
	      });
	    });
	  }
	  destroy() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onAccessDeniedHandler);
	  }
	  loadRecentFromCache() {
	    const searchResults = {};
	    return this.db.transaction('rw', this.db.items, this.db.recentItems, () => {
	      return this.deleteExpiredItems().then(() => {
	        return this.db.recentItems.orderBy('id').toArray();
	      });
	    }).then(recentItemsFromCache => {
	      searchResults.recentItems = recentItemsFromCache;
	      const resultItemsPromises = [];
	      searchResults.recentItems.forEach(recentItem => {
	        resultItemsPromises.push(this.db.items.get({
	          id: recentItem.cacheId
	        }));
	      });
	      return ui_dexie.Dexie.Promise.all(resultItemsPromises);
	    }).then(result => {
	      searchResults.items = result.filter(item => !main_core.Type.isUndefined(item)).map(item => item.json);
	      return searchResults;
	    });
	  }
	  save(searchResults) {
	    const preparedItems = searchResults.items ? this.prepareItems(searchResults.items) : [];
	    const preparedRecentItems = searchResults.recentItems ? SearchUtils.prepareRecentItems(searchResults.recentItems) : [];
	    this.db.transaction('rw', this.db.items, this.db.recentItems, () => {
	      if (preparedItems.length > 0) {
	        this.db.items.bulkPut(preparedItems);
	      }
	      if (preparedRecentItems.length > 0) {
	        this.db.recentItems.clear().then(() => {
	          this.db.recentItems.bulkPut(preparedRecentItems);
	        });
	      }
	    });
	  }
	  deleteExpiredItems() {
	    const oneMonthAgo = new Date(Date.now() - 60 * 60 * 1000 * 24 * 7 * 30);
	    return this.db.items.where('date').below(oneMonthAgo).delete().then(() => {
	      return this.db.recentItems.where('date').below(oneMonthAgo).delete();
	    });
	  }
	  onAccessDenied({
	    data: eventData
	  }) {
	    const cacheId = this.convertDialogIdToCacheItemId(eventData.dialogId);
	    return this.db.items.where('id').equals(cacheId).delete().then(() => {
	      return this.db.recentItems.where('cacheId').equals(cacheId).delete();
	    });
	  }
	  convertDialogIdToCacheItemId(dialogId) {
	    if (dialogId.startsWith('chat')) {
	      return `chat|${dialogId.slice(4)}`;
	    }
	    return `user|${dialogId}`;
	  }
	  prepareItems(items) {
	    return items.filter(item => {
	      return item.entityId !== EntityIdTypes.department && item.entityId !== EntityIdTypes.network && item.entityType !== 'LINES';
	    }).map(item => {
	      var _item$customData, _item$customData2, _item$customData$imUs, _item$customData$imUs2;
	      const type = SearchUtils.getTypeByEntityId(item.entityId);
	      return {
	        id: `${type}|${item.id}`,
	        name: (_item$customData = item.customData) != null && _item$customData.name ? SearchUtils.getWordsFromString(item.customData.name) : [],
	        lastName: (_item$customData2 = item.customData) != null && _item$customData2.lastName ? SearchUtils.getWordsFromString(item.customData.lastName) : [],
	        position: (_item$customData$imUs = item.customData.imUser) != null && _item$customData$imUs.WORK_POSITION ? SearchUtils.getWordsFromString((_item$customData$imUs2 = item.customData.imUser) == null ? void 0 : _item$customData$imUs2.WORK_POSITION) : [],
	        title: item.title ? SearchUtils.getWordsFromString(item.title) : [],
	        json: item,
	        date: new Date()
	      };
	    });
	  }

	  /**
	   * Moves item to the top of the recent search items list.
	   *
	   * @param itemToMove Array<string, number>
	   */
	  unshiftItem(itemToMove) {
	    const [itemToMoveEntityId, itemToMoveId] = itemToMove;
	    const type = SearchUtils.getTypeByEntityId(itemToMoveEntityId);
	    const itemToMoveCacheId = `${type}|${itemToMoveId}`;
	    this.db.transaction('rw', this.db.recentItems, () => {
	      return this.db.recentItems.toArray();
	    }).then(recentItems => {
	      const itemIndexToUpdate = recentItems.findIndex(recentItem => {
	        return recentItem.cacheId === itemToMoveCacheId;
	      });
	      if (itemIndexToUpdate === 0) {
	        return;
	      }
	      if (itemIndexToUpdate !== -1) {
	        const item = recentItems.splice(itemIndexToUpdate, 1);
	        item[0].date = new Date();
	        recentItems.unshift(item[0]);
	      } else {
	        const item = {
	          cacheId: `${itemToMoveCacheId}|${itemToMoveId}`,
	          date: new Date()
	        };
	        recentItems.unshift(item);
	      }
	      recentItems.forEach(item => delete item.id);
	      this.db.recentItems.clear().then(() => {
	        this.db.recentItems.bulkPut(recentItems);
	      });
	    });
	  }
	  search(words) {
	    return this.db.transaction('r', this.db.items, function* () {
	      // Parallel search for all words - just select resulting primary keys
	      const results = yield this.getQueryResultByWords(words);
	      if (!main_core.Type.isArrayFilled(results)) {
	        return [];
	      }
	      const intersectedResult = this.intersectArrays(...results);
	      const distinctIds = [...new Set(intersectedResult.flat())];

	      // Finally, select entire items from intersection
	      return yield this.db.items.where(':id').anyOf(distinctIds).toArray();
	    }.bind(this)).then(items => {
	      return items.map(item => item.json);
	    });
	  }
	  getQueryResultByWords(words) {
	    return ui_dexie.Dexie.Promise.all(words.map(word => {
	      return this.db.items.where('name').startsWithIgnoreCase(word).or('lastName').startsWithIgnoreCase(word).or('position').startsWithIgnoreCase(word).or('title').startsWithIgnoreCase(word).distinct().primaryKeys();
	    }));
	  }
	  intersectArrays(firstArray, secondArray, ...restArrays) {
	    if (main_core.Type.isUndefined(secondArray)) {
	      return firstArray;
	    }
	    const intersectedArray = firstArray.filter(value => secondArray.includes(value));
	    if (restArrays.length === 0) {
	      return intersectedArray;
	    }
	    return this.intersectArrays(intersectedArray, ...restArrays);
	  }
	}

	const Config = {
	  get: () => {
	    return {
	      dialog: {
	        entities: [{
	          id: 'im-bot',
	          options: {
	            searchableBotTypes: ['H', 'B', 'S', 'N'],
	            fillDialogWithDefaultValues: false
	          },
	          dynamicLoad: true,
	          dynamicSearch: true
	        }, {
	          id: 'user',
	          dynamicLoad: true,
	          dynamicSearch: true,
	          filters: [{
	            id: 'im.userDataFilter'
	          }]
	        }, {
	          id: 'im-chat-user',
	          options: {
	            searchableChatTypes: ['C', 'O'],
	            fillDialogWithDefaultValues: false
	          },
	          dynamicLoad: true,
	          dynamicSearch: true
	        }],
	        preselectedItems: [],
	        clearUnavailableItems: false,
	        context: 'IM_CHAT_SEARCH',
	        id: 'im-search'
	      }
	    };
	  },
	  getNetworkEntity: () => {
	    return {
	      id: 'imbot-network',
	      dynamicSearch: true,
	      options: {
	        'filterExistingLines': true
	      }
	    };
	  },
	  getDepartmentEntity: () => {
	    return {
	      id: 'department',
	      dynamicLoad: true,
	      dynamicSearch: true,
	      options: {
	        selectMode: 'usersAndDepartments',
	        allowSelectRootDepartment: true
	      },
	      filters: [{
	        id: 'im.departmentDataFilter'
	      }]
	    };
	  },
	  getChatEntity: () => {
	    return {
	      id: 'im-chat',
	      options: {
	        searchableChatTypes: ['C', 'O', 'L'],
	        fillDialogWithDefaultValues: false
	      },
	      dynamicLoad: true,
	      dynamicSearch: true
	    };
	  }
	};

	class SearchRecentList {
	  constructor($Bitrix) {
	    this.store = $Bitrix.Data.get('controller').store;
	  }

	  // region public methods
	  search(queryWords) {
	    const recentListItems = this.getRecentListItems();
	    const foundItems = [];
	    recentListItems.forEach(recentListItem => {
	      if (this.searchByQueryWords(recentListItem, queryWords)) {
	        foundItems.push(recentListItem);
	      }
	    });
	    return Promise.resolve(SearchUtils.createItemMap(foundItems));
	  }
	  //endregion

	  getRecentListItems() {
	    return this.store.getters['recent/getSortedCollection'].map(item => {
	      const dialog = this.store.getters['dialogues/get'](item.dialogId, true);
	      const isUser = dialog.type === im_v2_const.DialogType.user;
	      const recentListItem = {
	        dialogId: item.dialogId,
	        dialog: dialog
	      };
	      if (isUser) {
	        recentListItem.user = this.store.getters['users/get'](item.dialogId, true);
	      }
	      return recentListItem;
	    });
	  }
	  searchByQueryWords(recentListItem, queryWords) {
	    if (recentListItem.user) {
	      return this.searchByUserFields(recentListItem, queryWords);
	    }
	    return this.searchByDialogFields(recentListItem, queryWords);
	  }
	  searchByDialogFields(recentListItem, queryWords) {
	    const searchField = [];
	    if (recentListItem.dialog.name) {
	      const dialogNameWords = SearchUtils.getWordsFromString(recentListItem.dialog.name.toLowerCase());
	      searchField.push(...dialogNameWords);
	    }
	    return this.doesItemMatchQuery(searchField, queryWords);
	  }
	  searchByUserFields(recentListItem, queryWords) {
	    const searchField = [];
	    if (recentListItem.user.firstName) {
	      const userFirstNameWords = SearchUtils.getWordsFromString(recentListItem.user.firstName.toLowerCase());
	      searchField.push(...userFirstNameWords);
	    }
	    if (recentListItem.user.lastName) {
	      const userLastNameWords = SearchUtils.getWordsFromString(recentListItem.user.lastName.toLowerCase());
	      searchField.push(...userLastNameWords);
	    }
	    if (recentListItem.user.workPosition) {
	      const userWorkPositionWords = SearchUtils.getWordsFromString(recentListItem.user.workPosition.toLowerCase());
	      searchField.push(...userWorkPositionWords);
	    }
	    return this.doesItemMatchQuery(searchField, queryWords);
	  }
	  doesItemMatchQuery(fieldsForSearch, queryWords) {
	    let found = 0;
	    queryWords.forEach(queryWord => {
	      let queryWordsMatchCount = 0;
	      fieldsForSearch.forEach(field => {
	        if (field.startsWith(queryWord)) {
	          queryWordsMatchCount++;
	        }
	      });
	      if (queryWordsMatchCount > 0) {
	        found++;
	      }
	    });
	    return found >= queryWords.length;
	  }
	}

	const RestMethodImopenlinesNetworkJoin = 'imopenlines.network.join';
	class SearchService {
	  static getInstance($Bitrix, cache, recentList) {
	    if (!this.instance) {
	      this.instance = new this($Bitrix, cache, recentList);
	    }
	    return this.instance;
	  }
	  constructor($Bitrix, cache, recentList) {
	    this.store = null;
	    this.cache = null;
	    this.recentList = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.cache = cache;
	    this.recentList = recentList;
	    this.restClient = $Bitrix.RestClient.get();
	    this.onItemSelectHandler = this.onItemSelect.bind(this);
	    this.onOpenNetworkItemHandler = this.onOpenNetworkItem.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.selectItem, this.onItemSelectHandler);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.openNetworkItem, this.onOpenNetworkItemHandler);
	  }

	  //region Public methods

	  loadRecentSearchFromCache() {
	    return this.cache.loadRecentFromCache().then(responseFromCache => {
	      im_v2_lib_logger.Logger.warn('Im.Search: Recent search loaded from cache');
	      return responseFromCache;
	    }).then(responseFromCache => {
	      const {
	        items,
	        recentItems
	      } = responseFromCache;
	      const itemMap = SearchUtils.createItemMap(items);
	      return this.updateModels(itemMap).then(() => {
	        return this.getItemsFromRecentItems(recentItems, itemMap);
	      });
	    });
	  }
	  loadRecentSearchFromServer() {
	    return this.loadRecentFromServer().then(responseFromServer => {
	      im_v2_lib_logger.Logger.warn('Im.Search: Recent search loaded from server');
	      const items = SearchUtils.createItemMap(responseFromServer.items);
	      const recentItems = SearchUtils.prepareRecentItems(responseFromServer.recentItems);
	      return this.updateModels(items, true).then(() => {
	        return this.getItemsFromRecentItems(recentItems, items);
	      });
	    });
	  }
	  searchLocal(query) {
	    const originalLayoutQuery = query.trim().toLowerCase();
	    const searchInCachePromise = this.searchInCache(originalLayoutQuery);
	    const searchInRecentListPromise = this.searchInRecentList(originalLayoutQuery);
	    return Promise.all([searchInCachePromise, searchInRecentListPromise]).then(result => {
	      // Spread order is important, because we have more data in cache than in recent list
	      // (for example contextSort field)
	      const items = new Map([...result[1], ...result[0]]);
	      return this.getSortedItems(items, originalLayoutQuery);
	    });
	  }
	  searchOnServer(query, config) {
	    const originalLayoutQuery = query.trim().toLowerCase();
	    let items = [];
	    return this.searchRequest(originalLayoutQuery, config).then(itemsFromServer => {
	      items = SearchUtils.createItemMap(itemsFromServer);
	      return this.updateModels(items, true);
	    }).then(() => {
	      return this.allocateSearchResults(items, originalLayoutQuery);
	    });
	  }
	  searchOnNetwork(query) {
	    const originalLayoutQuery = query.trim().toLowerCase();
	    return this.searchOnNetworkRequest(originalLayoutQuery).then(items => {
	      return SearchUtils.createItemMap(items);
	    });
	  }
	  loadDepartmentUsers(parentItem) {
	    let items = [];
	    return this.loadDepartmentUsersFromServer(parentItem).then(responseFromServer => {
	      items = SearchUtils.createItemMap(responseFromServer);
	      return this.updateModels(items, true);
	    }).then(() => {
	      return items;
	    });
	  }
	  destroy() {
	    this.cache.destroy();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.selectItem, this.onItemSelectHandler);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.openNetworkItem, this.onOpenNetworkItemHandler);
	  }

	  //endregion

	  searchInCache(originalLayoutQuery) {
	    let wrongLayoutSearchPromise = Promise.resolve([]);
	    if (this.needLayoutChange(originalLayoutQuery)) {
	      const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
	      wrongLayoutSearchPromise = this.getItemsFromCacheByQuery(wrongLayoutQuery);
	    }
	    const correctLayoutSearchPromise = this.getItemsFromCacheByQuery(originalLayoutQuery);
	    return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
	      return new Map([...result[0], ...result[1]]);
	    }).catch(error => {
	      console.error('Unknown exception', error);
	      return new Map();
	    });
	  }
	  searchInRecentList(originalLayoutQuery) {
	    let wrongLayoutSearchPromise = Promise.resolve([]);
	    if (this.needLayoutChange(originalLayoutQuery)) {
	      const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
	      wrongLayoutSearchPromise = this.getItemsFromRecentListByQuery(wrongLayoutQuery);
	    }
	    const correctLayoutSearchPromise = this.getItemsFromRecentListByQuery(originalLayoutQuery);
	    return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
	      return new Map([...result[0], ...result[1]]);
	    });
	  }
	  getItemsFromRecentListByQuery(query) {
	    const queryWords = SearchUtils.getWordsFromString(query);
	    return this.recentList.search(queryWords);
	  }
	  getSearchConfig() {
	    return Config.get();
	  }
	  onItemSelect(event) {
	    const {
	      selectedItem,
	      onlyOpen
	    } = event.getData();
	    const item = [selectedItem.entityId, selectedItem.id];
	    if (!onlyOpen) {
	      this.cache.unshiftItem(item);
	      this.addItemsToRecentSearchResults(item);
	    }
	  }
	  onOpenNetworkItem(event) {
	    const code = event.getData();
	    return new Promise((resolve, reject) => {
	      this.restClient.callBatch(this.getDataRequestQuery(code), result => resolve(this.handleBatchRequestResult(result)), error => reject(error));
	    });
	  }
	  handleBatchRequestResult(result) {
	    if (result[RestMethodImopenlinesNetworkJoin] && result[RestMethodImopenlinesNetworkJoin].error()) {
	      return {
	        error: result[RestMethodImopenlinesNetworkJoin].error().ex.error_description
	      };
	    }
	    if (result[im_v2_const.RestMethod.imUserGet] && result[im_v2_const.RestMethod.imUserGet].error()) {
	      return {
	        error: result[im_v2_const.RestMethod.imUserGet].error().ex.error_description
	      };
	    }
	    const user = result[im_v2_const.RestMethod.imUserGet].data();
	    this.store.dispatch('users/set', [user]);
	    const dialogue = this.prepareChatForAdditionalUser(user);
	    this.store.dispatch('dialogues/set', [dialogue]);
	    return user;
	  }
	  prepareChatForAdditionalUser(user) {
	    return {
	      dialogId: user.id,
	      avatar: user.avatar,
	      color: user.color,
	      name: user.name,
	      type: im_v2_const.DialogType.user
	    };
	  }
	  getDataRequestQuery(code) {
	    const query = {
	      [RestMethodImopenlinesNetworkJoin]: [RestMethodImopenlinesNetworkJoin, {
	        code: code
	      }]
	    };
	    query[im_v2_const.RestMethod.imUserGet] = [im_v2_const.RestMethod.imUserGet, {
	      id: `$result[${RestMethodImopenlinesNetworkJoin}]`
	    }];
	    return query;
	  }
	  getItemsFromCacheByQuery(query) {
	    const queryWords = SearchUtils.getWordsFromString(query);
	    return this.cache.search(queryWords).then(cacheItems => {
	      const items = SearchUtils.createItemMap(cacheItems);
	      return this.updateModels(items).then(() => items);
	    });
	  }
	  getSortedItems(items, originalLayoutQuery) {
	    let sortedItems = this.sortItemsBySearchField(items, originalLayoutQuery);
	    sortedItems = this.sortItemsByEntityIdAndContextSort(sortedItems);
	    return sortedItems;
	  }
	  sortItemsBySearchField(items, originalLayoutQuery) {
	    let queryWords = SearchUtils.getWordsFromString(originalLayoutQuery);
	    if (this.needLayoutChange(originalLayoutQuery)) {
	      const wrongLayoutQueryWords = SearchUtils.getWordsFromString(this.changeLayout(originalLayoutQuery));
	      queryWords = [...queryWords, ...wrongLayoutQueryWords];
	    }
	    const uniqueWords = [...new Set(queryWords)];
	    const searchFieldsWeight = {
	      title: 10000,
	      name: 1000,
	      lastName: 100,
	      position: 1
	    };
	    items.forEach(item => {
	      uniqueWords.forEach(word => {
	        var _item$getName, _item$getLastName, _item$getPosition;
	        if (item.getTitle().toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.title);
	        } else if ((_item$getName = item.getName()) != null && _item$getName.toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.name);
	        } else if ((_item$getLastName = item.getLastName()) != null && _item$getLastName.toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.lastName);
	        } else if ((_item$getPosition = item.getPosition()) != null && _item$getPosition.toLowerCase().startsWith(word)) {
	          item.addCustomSort(searchFieldsWeight.position);
	        }
	      });
	    });
	    return new Map([...items.entries()].sort((firstItem, secondItem) => {
	      const [, firstItemValue] = firstItem;
	      const [, secondItemValue] = secondItem;
	      return secondItemValue.getCustomSort() - firstItemValue.getCustomSort();
	    }));
	  }
	  sortItemsByEntityIdAndContextSort(items) {
	    const entityWeight = {
	      'user': 100,
	      'im-chat': 80,
	      'im-chat-user': 80,
	      'im-bot': 70,
	      'department': 60,
	      'extranet': 10
	    };
	    return new Map([...items.entries()].sort((firstItem, secondItem) => {
	      const [, firstItemValue] = firstItem;
	      const [, secondItemValue] = secondItem;
	      const secondItemEntityId = secondItemValue.isExtranet() ? 'extranet' : secondItemValue.getEntityId();
	      const firstItemEntityId = firstItemValue.isExtranet() ? 'extranet' : firstItemValue.getEntityId();
	      if (entityWeight[secondItemEntityId] < entityWeight[firstItemEntityId]) {
	        return -1;
	      } else if (entityWeight[secondItemEntityId] > entityWeight[firstItemEntityId]) {
	        return 1;
	      } else {
	        return secondItemValue.getContextSort() - firstItemValue.getContextSort();
	      }
	    }));
	  }
	  loadRecentFromServer() {
	    const config = {
	      json: this.getSearchConfig()
	    };
	    const chatEntity = Config.getChatEntity();
	    chatEntity.options.searchableChatTypes = ['C', 'O'];
	    config.json.dialog.entities.push(chatEntity);
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.load', config).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: Recent search request result`, response);
	        this.cache.save(response.data.dialog);
	        resolve(response.data.dialog);
	      }).catch(error => reject(error));
	    });
	  }
	  loadDepartmentUsersFromServer(parentItem) {
	    const config = {
	      json: {
	        ...this.getSearchConfig(),
	        parentItem
	      }
	    };
	    const departmentEntity = Config.getDepartmentEntity();
	    config.json.dialog.entities.push(departmentEntity);
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.getChildren', config).then(response => {
	        im_v2_lib_logger.Logger.warn('Im.Search: load department users result', response);
	        this.cache.save(response.data.dialog);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }
	  searchRequest(query, requestConfig) {
	    const config = {
	      json: this.getSearchConfig()
	    };
	    if (requestConfig.network) {
	      const networkEntity = Config.getNetworkEntity();
	      config.json.dialog.entities.push(networkEntity);
	    }
	    if (requestConfig.departments) {
	      const departmentEntity = Config.getDepartmentEntity();
	      config.json.dialog.entities.push(departmentEntity);
	    }
	    const chatEntity = Config.getChatEntity();
	    config.json.dialog.entities.push(chatEntity);
	    config.json.searchQuery = {
	      'queryWords': SearchUtils.getWordsFromString(query.trim()),
	      'query': query.trim()
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.doSearch', config).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: Search request result`, response);
	        this.cache.save(response.data.dialog);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }
	  searchOnNetworkRequest(query) {
	    const config = {
	      json: this.getSearchConfig()
	    };
	    const networkEntity = Config.getNetworkEntity();
	    config.json.dialog.entities = [networkEntity];
	    config.json.searchQuery = {
	      'queryWords': SearchUtils.getWordsFromString(query.trim()),
	      'query': query.trim()
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.doSearch', config).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: Network Search request result`, response);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }
	  addItemsToRecentSearchResults(recentItem) {
	    const [entityId, id] = recentItem;
	    const recentItems = [{
	      id,
	      entityId
	    }];
	    const config = {
	      json: {
	        ...this.getSearchConfig(),
	        recentItems
	      }
	    };
	    const chatEntity = Config.getChatEntity();
	    config.json.dialog.entities.push(chatEntity);
	    main_core.ajax.runAction('ui.entityselector.saveRecentItems', config);
	  }
	  updateModels(items, set = false) {
	    const {
	      users,
	      dialogues
	    } = this.prepareDataForModels(items);
	    const usersActionName = set ? 'users/set' : 'users/add';
	    const dialoguesActionName = set ? 'dialogues/set' : 'dialogues/add';
	    const usersPromise = this.store.dispatch(usersActionName, users);
	    const dialoguesPromise = this.store.dispatch(dialoguesActionName, dialogues);
	    return Promise.all([usersPromise, dialoguesPromise]);
	  }
	  prepareDataForModels(items) {
	    const result = {
	      users: [],
	      dialogues: []
	    };
	    items.forEach(item => {
	      if (!item.getCustomData()) {
	        return;
	      }

	      // user
	      if (item.isUser()) {
	        const preparedUser = SearchUtils.convertKeysToLowerCase(item.getUserCustomData());
	        result.users.push(preparedUser);
	        result.dialogues.push({
	          avatar: preparedUser.avatar,
	          color: preparedUser.color,
	          name: preparedUser.name,
	          type: im_v2_const.DialogType.user,
	          dialogId: item.getId()
	        });
	      }

	      // chat
	      if (item.isChat() && !item.isOpeLinesType()) {
	        const chat = SearchUtils.convertKeysToLowerCase(item.getChatCustomData());
	        result.dialogues.push({
	          ...chat,
	          dialogId: `chat${chat.id}`
	        });
	      }
	    });
	    return result;
	  }
	  getItemsFromRecentItems(recentItems, items) {
	    const filledRecentItems = new Map();
	    recentItems.forEach(recentItem => {
	      const itemFromMap = items.get(recentItem.cacheId);
	      if (itemFromMap && !itemFromMap.isOpeLinesType()) {
	        filledRecentItems.set(itemFromMap.getEntityFullId(), itemFromMap);
	      }
	    });
	    return filledRecentItems;
	  }
	  allocateSearchResults(items, originalLayoutQuery) {
	    const usersAndChats = new Map();
	    const chatUsers = new Map();
	    const departments = new Map();
	    const openLines = new Map();
	    const network = new Map();
	    items.forEach(item => {
	      switch (item.getEntityId()) {
	        case EntityIdTypes.chatUser:
	          {
	            chatUsers.set(item.getEntityFullId(), item);
	            break;
	          }
	        case EntityIdTypes.department:
	          {
	            departments.set(item.getEntityFullId(), item);
	            break;
	          }
	        case EntityIdTypes.network:
	          {
	            network.set(item.getEntityFullId(), item);
	            break;
	          }
	        default:
	          {
	            if (item.isOpeLinesType()) {
	              openLines.set(item.getEntityFullId(), item);
	            } else {
	              usersAndChats.set(item.getEntityFullId(), item);
	            }
	          }
	      }
	    });
	    return {
	      usersAndChats: this.getSortedItems(usersAndChats, originalLayoutQuery),
	      chatUsers: chatUsers,
	      departments: departments,
	      openLines: openLines,
	      network: network
	    };
	  }
	  isRussianInterface() {
	    return this.store.state.application.common.languageId === 'ru';
	  }
	  changeLayout(query) {
	    if (this.isRussianInterface() && BX.correctText) {
	      // eslint-disable-next-line bitrix-rules/no-bx
	      return BX.correctText(query, {
	        replace_way: 'AUTO'
	      });
	    }
	    return query;
	  }
	  needLayoutChange(originalLayoutQuery) {
	    const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
	    const isIdenticalQuery = wrongLayoutQuery === originalLayoutQuery;
	    return this.isRussianInterface() && !isIdenticalQuery;
	  }
	}
	SearchService.instance = null;

	// @vue/component
	const SearchResultDepartmentItem = {
	  name: 'SearchResultDepartmentItem',
	  components: {
	    SearchResultItem
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  data: function () {
	    return {
	      expanded: false,
	      isLoading: false,
	      usersInDepartment: []
	    };
	  },
	  computed: {
	    departmentAvatarStyle() {
	      var _this$item$avatarOpti;
	      if ((_this$item$avatarOpti = this.item.avatarOptions) != null && _this$item$avatarOpti.color) {
	        return {
	          backgroundColor: this.item.avatarOptions.color
	        };
	      }
	      return {
	        backgroundColor: '#df532d'
	      };
	    },
	    title() {
	      return im_v2_lib_utils.Utils.text.htmlspecialcharsback(this.item.title);
	    }
	  },
	  created() {
	    const cache = new SearchCache(this.getCurrentUserId());
	    const recentList = new SearchRecentList(this.$Bitrix);
	    this.searchService = SearchService.getInstance(this.$Bitrix, cache, recentList);
	  },
	  methods: {
	    onClick() {
	      if (!this.expanded) {
	        this.openDepartment();
	      } else {
	        this.closeDepartment();
	      }
	    },
	    openDepartment() {
	      this.isLoading = true;
	      if (main_core.Type.isArrayFilled(this.usersInDepartment)) {
	        this.isLoading = false;
	        this.expanded = true;
	        return;
	      }
	      this.searchService.loadDepartmentUsers(this.item).then(usersAndDepartments => {
	        this.usersInDepartment = [...usersAndDepartments.values()].filter(user => user.isUser());
	        this.isLoading = false;
	        this.expanded = true;
	      });
	    },
	    closeDepartment() {
	      this.expanded = false;
	    },
	    getCurrentUserId() {
	      return this.$store.state.application.common.userId;
	    },
	    enterTransition(element) {
	      main_core.Dom.style(element, 'height', 0);
	      main_core.Dom.style(element, 'opacity', 0);
	      requestAnimationFrame(() => {
	        requestAnimationFrame(() => {
	          main_core.Dom.style(element, 'opacity', 1);
	          main_core.Dom.style(element, 'height', `${element.scrollHeight}px`);
	        });
	      });
	    },
	    afterEnterTransition(element) {
	      main_core.Dom.style(element, 'height', 'auto');
	    },
	    leaveTransition(element) {
	      main_core.Dom.style(element, 'height', `${element.scrollHeight}px`);
	      requestAnimationFrame(() => {
	        main_core.Dom.style(element, 'height', 0);
	        main_core.Dom.style(element, 'opacity', 0);
	      });
	    }
	  },
	  // language=Vue
	  template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<div :title="item.title" class="bx-im-component-avatar-wrap bx-im-component-avatar-size-l">
					<div 
						class="bx-im-component-avatar-content bx-im-component-avatar-image bx-search-item-department-icon"
						:style="departmentAvatarStyle"
					></div>
				</div>
			</div>
			<div class="bx-im-search-result-item-content bx-im-search-result-item-department-content">
				<div class="bx-im-component-chat-title-wrap">
					<div class="bx-im-component-chat-name-text">
						{{title}}
					</div>
				</div>
				<div class="bx-search-item-department-expand-button">
					<div v-if="isLoading" class="bx-search-loader bx-search-loader-large-size bx-search-item-department-expand-loader"></div>
					<div v-else-if="expanded" class="bx-search-item-department-down-arrow"></div>
					<div v-else class="bx-search-item-department-up-arrow"></div>
				</div>
			</div>
		</div>
		<transition
			name="bx-im-search-department-expand"
			@enter="enterTransition"
			@after-enter="afterEnterTransition"
			@leave="leaveTransition"
		>
			<div v-if="expanded" class="bx-search-department-users-wrapper">
				<div class="bx-search-department-users">
					<SearchResultItem v-for="user in usersInDepartment" :key="user.getEntityFullId()" :item="user" :child="true"/>
				</div>
			</div>
		</transition>
	`
	};

	/**
	* @bitrixEvents EventType.search.openContextMenu
	* @bitrixEvents EventType.dialog.errors.accessDenied
	* @bitrixEvents EventType.search.selectItem
	* @bitrixEvents EventType.recent.updateSearch
	*/
	const Search = {
	  components: {
	    RecentUsersCarousel,
	    SearchResultSection,
	    LoadingState: im_v2_component_oldChatEmbedding_elements.RecentLoadingState,
	    SearchResultOpenlineItem,
	    SearchResultNetworkItem,
	    SearchResultDepartmentItem,
	    SearchResultItem
	  },
	  props: {
	    searchQuery: {
	      type: String,
	      required: true
	    },
	    searchMode: {
	      type: Boolean,
	      required: true
	    }
	  },
	  data: function () {
	    return {
	      isRecentLoading: false,
	      isLocalLoading: false,
	      isServerLoading: false,
	      isNetworkLoading: false,
	      currentServerQueries: 0,
	      isNetworkButtonClicked: false,
	      isNetworkAvailable: false,
	      isNetworkSearchEnabled: true,
	      result: {
	        recent: new Map(),
	        usersAndChats: new Map(),
	        chatUsers: new Map(),
	        departments: new Map(),
	        openLines: new Map(),
	        network: new Map()
	      }
	    };
	  },
	  computed: {
	    isEmptyState() {
	      if (this.isServerLoading || this.isLocalLoading || this.isNetworkLoading) {
	        return false;
	      }
	      if (this.isNetworkAvailable && !this.isNetworkButtonClicked && this.isServerSearch) {
	        return false;
	      }
	      return this.result.usersAndChats.size === 0 && this.result.departments.size === 0 && this.result.chatUsers.size === 0 && this.result.openLines.size === 0 && this.result.network.size === 0;
	    },
	    isLoadingState() {
	      return this.isServerLoading || this.isRecentLoading;
	    },
	    isServerSearch() {
	      return this.searchQuery.trim().length >= this.minTokenSize;
	    },
	    needToShowNetworkSection() {
	      return !this.isNetworkButtonClicked || this.result.network.size > 0;
	    },
	    showSearchResult() {
	      return this.searchQuery.trim().length > 0;
	    },
	    isNetworkSearchCode() {
	      return !!(this.searchQuery.length === 32 && /[\da-f]{32}/.test(this.searchQuery));
	    },
	    isNetworkAvailableForSearch() {
	      if (!this.isNetworkAvailable) {
	        return false;
	      }
	      return this.isNetworkSearchEnabled || this.isNetworkSearchCode;
	    },
	    itemComponent: () => SearchResultItem,
	    itemDepartmentComponent: () => SearchResultDepartmentItem,
	    itemNetworkComponent: () => SearchResultNetworkItem,
	    itemOpenlineComponent: () => SearchResultOpenlineItem
	  },
	  watch: {
	    searchQuery(newValue, oldValue) {
	      const newQuery = newValue.trim();
	      const previousQuery = oldValue.trim();
	      if (newQuery === previousQuery) {
	        return;
	      }
	      this.startSearch(newQuery);
	    },
	    searchMode(newValue, oldValue) {
	      if (newValue === false && oldValue === true)
	        // search switch off
	        {
	          this.isNetworkButtonClicked = false;
	        } else if (newValue === true && oldValue === false)
	        // search switch on
	        {
	          if (this.result.recent.size > 0) {
	            return;
	          }
	          this.isRecentLoading = true;
	        }
	      this.searchService.loadRecentSearchFromServer().then(recentItems => {
	        this.result.recent = recentItems;
	        this.isRecentLoading = false;
	      });
	    }
	  },
	  created() {
	    this.initSettings();
	    this.contextMenuManager = new SearchContextMenu(this.$Bitrix);
	    const cache = new SearchCache(this.getCurrentUserId());
	    const recentList = new SearchRecentList(this.$Bitrix);
	    this.searchService = SearchService.getInstance(this.$Bitrix, cache, recentList);
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 1500, this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.openContextMenu, this.onOpenContextMenu);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.selectItem, this.onSelectItem);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.recent.updateSearch, this.onPressEnterKey);
	    this.loadInitialRecentFromCache();
	  },
	  beforeUnmount() {
	    this.searchService.destroy();
	    this.contextMenuManager.destroy();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.openContextMenu, this.onOpenContextMenu);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.errors.accessDenied, this.onDelete);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.search.selectItem, this.onSelectItem);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.recent.updateSearch, this.onPressEnterKey);
	  },
	  methods: {
	    loadInitialRecentFromCache() {
	      // we don't need an extra request to get recent items while messenger initialization
	      this.searchService.loadRecentSearchFromCache().then(recentItems => {
	        this.result.recent = recentItems;
	      });
	    },
	    initSettings() {
	      const settings = main_core.Extension.getSettings('im.v2.component.old-chat-embedding.search');
	      const defaultMinTokenSize = 3;
	      this.minTokenSize = settings.get('minTokenSize', defaultMinTokenSize);
	      this.isNetworkAvailable = settings.get('isNetworkAvailable', false);
	      this.isNetworkSearchEnabled = settings.get('isNetworkSearchEnabled', true);
	      this.isDepartmentsAvailable = settings.get('isDepartmentsAvailable', false);
	    },
	    startSearch(searchQuery) {
	      if (searchQuery.length > 0 && searchQuery.length < this.minTokenSize) {
	        this.isLocalLoading = true;
	        const queryBeforeRequest = searchQuery;
	        this.searchService.searchLocal(searchQuery).then(localSearchResult => {
	          if (queryBeforeRequest !== this.searchQuery.trim()) {
	            return;
	          }
	          this.result.usersAndChats = localSearchResult;
	          this.isLocalLoading = false;
	        });
	      } else if (searchQuery.length >= this.minTokenSize) {
	        this.isServerLoading = true;
	        const queryBeforeRequest = searchQuery;
	        this.searchService.searchLocal(searchQuery).then(localSearchResult => {
	          if (queryBeforeRequest !== this.searchQuery.trim()) {
	            return;
	          }
	          this.result.usersAndChats = localSearchResult;
	        }).then(() => this.searchOnServerDelayed(searchQuery));
	      } else {
	        this.cleanSearchResult();
	      }
	    },
	    cleanSearchResult() {
	      this.result.usersAndChats = new Map();
	      this.result.departments = new Map();
	      this.result.chatUsers = new Map();
	      this.result.network = new Map();
	      this.result.openLines = new Map();
	    },
	    searchOnServer(query) {
	      this.currentServerQueries++;
	      this.isNetworkLoading = this.isNetworkButtonClicked;
	      const config = {
	        network: this.isNetworkAvailableForSearch && this.isNetworkButtonClicked,
	        departments: !BX.MessengerProxy.isCurrentUserExtranet() && this.isDepartmentsAvailable
	      };
	      const queryBeforeRequest = query;
	      this.searchService.searchOnServer(query, config).then(searchResultFromServer => {
	        if (queryBeforeRequest !== this.searchQuery.trim()) {
	          this.stopLoader();
	          return;
	        }
	        this.result.usersAndChats = this.mergeResults(this.result.usersAndChats, searchResultFromServer.usersAndChats);
	        this.result.departments = searchResultFromServer.departments;
	        this.result.chatUsers = searchResultFromServer.chatUsers;
	        this.result.openLines = searchResultFromServer.openLines;
	        this.result.network = searchResultFromServer.network;
	      }).catch(error => {
	        console.error(error);
	      }).finally(() => {
	        this.currentServerQueries--;
	        this.stopLoader();
	      });
	    },
	    stopLoader() {
	      if (this.currentServerQueries > 0) {
	        return;
	      }
	      this.isNetworkLoading = false;
	      this.isServerLoading = false;
	    },
	    searchOnNetwork(query) {
	      this.isNetworkLoading = true;
	      const queryBeforeRequest = query;
	      this.searchService.searchOnNetwork(query).then(searchResultFromServer => {
	        if (queryBeforeRequest !== this.searchQuery) {
	          this.isNetworkLoading = false;
	          return;
	        }
	        this.result.network = searchResultFromServer;
	        this.isNetworkButtonClicked = true;
	        this.isNetworkLoading = false;
	      });
	    },
	    mergeResults(originalItems, newItems) {
	      const mergedMap = new Map(originalItems.entries());
	      newItems.forEach((newItemValue, newItemKey) => {
	        if (!mergedMap.has(newItemKey)) {
	          mergedMap.set(newItemKey, newItemValue);
	        }
	      });
	      return mergedMap;
	    },
	    onOpenContextMenu({
	      data: eventData
	    }) {
	      if (eventData.event.altKey && eventData.event.shiftKey) {
	        return;
	      }
	      this.contextMenuManager.openMenu(eventData.item, eventData.event.currentTarget);
	    },
	    onDelete({
	      data: eventData
	    }) {
	      const {
	        dialogId
	      } = eventData;
	      this.result.recent.delete(dialogId);
	      this.result.usersAndChats.delete(dialogId);
	      this.result.chatUsers.delete(dialogId);
	    },
	    onScroll() {
	      this.contextMenuManager.destroy();
	    },
	    onClickLoadNetworkResult() {
	      this.searchOnNetwork(this.searchQuery);
	    },
	    onSelectItem(event) {
	      const {
	        selectedItem,
	        nativeEvent
	      } = event.getData();
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	        dialogId: selectedItem.dialogId,
	        chat: this.$store.getters['dialogues/get'](selectedItem.dialogId, true),
	        user: this.$store.getters['users/get'](selectedItem.dialogId, true)
	      });
	      if (!nativeEvent.altKey) {
	        BX.MessengerProxy.clearSearchInput();
	      }
	    },
	    onPressEnterKey(event) {
	      if (event.data.keyCode !== 13)
	        // enter
	        {
	          return;
	        }
	      const firstItem = this.getFirstItemFromSearchResults();
	      if (!firstItem) {
	        return;
	      }
	      const selectedItem = {
	        id: firstItem.getId(),
	        entityId: firstItem.getEntityId(),
	        dialogId: firstItem.getDialogId()
	      };
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.selectItem, {
	        selectedItem: selectedItem,
	        onlyOpen: firstItem.isOpeLinesType(),
	        nativeEvent: {}
	      });
	    },
	    getFirstItemFromSearchResults() {
	      if (!this.showSearchResult && this.result.recent.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.recent);
	      }
	      if (this.result.usersAndChats.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.usersAndChats);
	      }
	      if (this.result.chatUsers.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.chatUsers);
	      }
	      if (this.result.openLines.size > 0) {
	        return SearchUtils.getFirstItemFromMap(this.result.openLines);
	      }
	      return null;
	    },
	    getCurrentUserId() {
	      return this.$store.state.application.common.userId;
	    }
	  },
	  template: `
		<div class="bx-messenger-search" @scroll="onScroll">
			<div>
				<template v-if="!showSearchResult">
					<RecentUsersCarousel />
					<SearchResultSection
						:component="itemComponent"
						:items="result.recent" 
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT')" 
						:showMoreButton="false" 
					/>
				</template>
				<template v-if="showSearchResult">
					<SearchResultSection 
						v-if="result.usersAndChats.size > 0"
						:component="itemComponent"
						:items="result.usersAndChats"
						:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_USERS_AND_CHATS')"
						:min-items:="20"
						:max-items="50"
					/>
					<template v-if="!isLoadingState && isServerSearch">
						<SearchResultSection
							v-if="result.chatUsers.size > 0"
							:component="itemComponent"
							:items="result.chatUsers"
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_CHAT_USERS')"
							:min-items:="5"
							:max-items="20"
						/>
						<SearchResultSection 
							v-if="result.departments.size > 0"
							:component="itemDepartmentComponent"
							:items="result.departments" 
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_DEPARTMENTS')"
							:min-items:="5"
							:max-items="20"
						/>
						<SearchResultSection
							v-if="result.openLines.size > 0"
							:component="itemOpenlineComponent"
							:items="result.openLines"
							:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_OPENLINES')"
							:min-items:="5"
							:max-items="20"
						/>
						<template v-if="isNetworkAvailableForSearch">
							<SearchResultSection
								v-if="needToShowNetworkSection"
								:component="itemNetworkComponent"
								:items="result.network"
								:title="$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK')"
								:min-items:="5"
								:max-items="20"
							/>
							<template v-if="!isNetworkButtonClicked">
								<div 
									v-if="!isNetworkLoading"
									@click="onClickLoadNetworkResult"
									class="bx-im-search-network-button"
								>
									{{$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_NETWORK_BUTTON')}}
								</div>
								<div v-else class="bx-search-network-loader-wrapper">
									<div class="bx-search-loader bx-search-loader-large-size"></div>
								</div>
							</template>
						</template>
					</template>
					<div v-if="isEmptyState" class="bx-im-search-not-found-wrapper">
						<div class="bx-im-search-not-found-icon"></div>
						<div class="bx-im-search-not-found-title">{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND') }}</div>
						<div class="bx-im-search-not-found-title">
							{{ $Bitrix.Loc.getMessage('IM_SEARCH_RESULT_NOT_FOUND_DESCRIPTION') }}
						</div>
					</div>
				</template>
				<LoadingState v-if="isLoadingState" />
			</div>
		</div>
	`
	};

	exports.Search = Search;

}((this.BX.Messenger.v2.ComponentLegacy = this.BX.Messenger.v2.ComponentLegacy || {}),BX,BX.Messenger.v2.LibLegacy,BX,BX.Messenger.v2.Lib,BX.Dexie3,BX.Messenger.v2.Lib,BX.Messenger.v2.ComponentLegacy,BX,BX.Event,BX.Messenger.v2.Const));
//# sourceMappingURL=search.bundle.js.map
