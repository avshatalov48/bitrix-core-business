this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_v2_component_elements,im_v2_lib_logger,im_v2_const,main_core_events,ui_dexie,ui_vue3,main_core) {
	'use strict';

	const CarouselUser = {
	  name: 'CarouselUser',
	  components: {
	    Avatar: im_v2_component_elements.Avatar
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

	    AvatarSize: () => im_v2_const.AvatarSize
	  },
	  methods: {
	    onClick() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	        dialogId: this.user.dialogId
	      });
	    }

	  },
	  // language=Vue
	  template: `
		<div class="bx-messenger-carousel-item" @click="onClick">
			<Avatar :dialogId="user.dialogId" :size="AvatarSize.L" />
			<div class="bx-messenger-carousel-item-title">{{name}}</div>
		</div>
	`
	};

	const recentUsersLimit = 5; // @vue/component

	const RecentUsersCarousel = {
	  name: 'RecentUsersCarousel',
	  components: {
	    CarouselUser
	  },
	  props: {
	    title: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    users() {
	      const recentUsers = [];
	      this.$store.state.recent.collection.forEach(recentItem => {
	        const dialog = this.$store.getters['dialogues/get'](recentItem.dialogId, true);
	        const user = this.$store.getters['users/get'](recentItem.dialogId, true);
	        recentUsers.push({ ...recentItem,
	          dialog,
	          user
	        });
	      });
	      return recentUsers.filter(item => item.dialog.type === 'user' && !item.user.bot).slice(0, recentUsersLimit);
	    }

	  },
	  // language=Vue
	  template: `
		<div class="bx-messenger-recent-users-carousel-title">{{title}}</div>
		<div class="bx-messenger-recent-users-carousel">
			<div class="bx-messenger-recent-users-carousel-inner">
				<CarouselUser v-for="user in users" :key="user.dialogId" :user="user" />
			</div>
		</div>
	`
	};

	const SearchResultItem = {
	  name: 'SearchResultItem',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    ChatTitle: im_v2_component_elements.ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    child: {
	      type: Boolean,
	      default: false,
	      required: false
	    }
	  },
	  computed: {
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
	      return this.dialog.type === im_v2_const.ChatTypes.user;
	    },

	    userOnlineStatus() {
	      return this.$store.getters['users/getLastOnline'](this.dialogId);
	    },

	    workPosition() {
	      return this.$store.getters['users/getPosition'](this.dialogId);
	    },

	    AvatarSize: () => im_v2_const.AvatarSize
	  },
	  methods: {
	    onClick() {
	      console.warn('onClick', this.dialog);
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	        dialogId: this.dialogId
	      });
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.search.selectItem, this.dialogId);
	    },

	    onRightClick() {
	      console.warn('onRightClick');
	    }

	  },
	  // language=Vue
	  template: `
		<div @click="onClick" @click.right.prevent="onRightClick" class="bx-im-search-item" :class="[this.child ? 'bx-im-search-sub-item' : '']">
			<div class="bx-im-search-avatar-wrap">
				<Avatar :dialogId="dialogId" :size="AvatarSize.L" />
			</div>
			<div v-if="isUser" class="bx-im-search-result-item-content">
				<div class="bx-im-search-result-item-content-header">
					<ChatTitle :dialogId="dialogId" />
				</div>
				<div class="bx-im-recent-item-content-bottom">
					<div class="bx-im-search-result-item-text-wrap">
						<div class="bx-im-search-result-item-message-text">{{ workPosition }}</div>
						<div class="bx-im-search-result-item-message-text">{{ userOnlineStatus }}</div>
					</div>
				</div>
			</div>
			<div v-else class="bx-im-search-result-item-title-content">
				<ChatTitle :dialogId="dialogId" />
			</div>
		</div>
	`
	};

	const EntityIdTypes = Object.freeze({
	  user: 'user',
	  bot: 'im-bot',
	  chat: 'im-chat',
	  department: 'department'
	});

	class SearchCacheService {
	  constructor() {
	    /** @type {Dexie} */
	    this.db = new ui_dexie.Dexie('bx-im-search-results');
	    this.db.version(1).stores({
	      items: 'id, title, name, lastName, secondName, position, date',
	      recentItems: '++id'
	    });
	  }

	  loadRecentFromCache() {
	    const searchResults = {};
	    return this.db.transaction('r', this.db.items, this.db.recentItems, () => {
	      return this.db.recentItems.orderBy('id').toArray();
	    }).then(result => {
	      searchResults.recentItems = result.map(item => item.json);
	      const resultItemsPromises = [];
	      searchResults.recentItems.forEach(recentItem => {
	        const recentItemId = `${recentItem[1]}${recentItem[0]}`;
	        resultItemsPromises.push(this.db.items.get({
	          id: recentItemId
	        }));
	      });
	      return ui_dexie.Dexie.Promise.all(resultItemsPromises);
	    }).then(result => {
	      searchResults.items = result.filter(item => !main_core.Type.isUndefined(item)).map(item => item.json);
	      return searchResults;
	    });
	  } //todo refactor because of complexity


	  saveToCache(searchResults) {
	    let preparedItems = [];

	    if (searchResults.items) {
	      preparedItems = searchResults.items.filter(item => item.entityId !== EntityIdTypes.department).map(item => {
	        var _item$customData, _item$customData2, _item$customData3, _item$customData4;

	        return {
	          id: `${item.id}${item.entityId}`,
	          name: (_item$customData = item.customData) != null && _item$customData.name ? item.customData.name : '',
	          lastName: (_item$customData2 = item.customData) != null && _item$customData2.lastName ? item.customData.lastName : '',
	          secondName: (_item$customData3 = item.customData) != null && _item$customData3.secondName ? item.customData.secondName : '',
	          position: (_item$customData4 = item.customData) != null && _item$customData4.position ? item.customData.position : '',
	          title: item.title ? item.title : '',
	          json: item,
	          date: new Date()
	        };
	      });
	    }

	    let preparedRecentItems = [];

	    if (searchResults.recentItems) {
	      preparedRecentItems = searchResults.recentItems.map(item => {
	        return {
	          json: item,
	          date: new Date()
	        };
	      });
	    }

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
	  /**
	   * Moves item to the top of the recent search items list.
	   *
	   * @param itemToMove Array<string, number>
	   */


	  unshiftItem(itemToMove) {
	    this.db.transaction('rw', this.db.recentItems, () => {
	      return this.db.recentItems.toArray();
	    }).then(recentItems => {
	      const recentItemsPairs = recentItems.map(item => item.json);
	      const itemIndexToUpdate = recentItemsPairs.findIndex(item => {
	        return item[1] === itemToMove[1] && item[0] === itemToMove[0];
	      });

	      if (itemIndexToUpdate === 0) {
	        return;
	      }

	      if (itemIndexToUpdate !== -1) {
	        const item = recentItemsPairs.splice(itemIndexToUpdate, 1);
	        recentItemsPairs.unshift(item[0]);
	      } else {
	        recentItemsPairs.unshift(itemToMove);
	      }

	      this.saveToCache({
	        recentItems: recentItemsPairs
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
	      const distinctIds = [...new Set(intersectedResult.flat())]; // Finally, select entire items from intersection

	      return yield this.db.items.where(':id').anyOf(distinctIds).toArray();
	    }.bind(this)).then(items => {
	      return items.map(item => item.json);
	    });
	  }

	  getQueryResultByWords(words) {
	    return ui_dexie.Dexie.Promise.all(words.map(word => {
	      return this.db.items.where('name').startsWithIgnoreCase(word).or('lastName').startsWithIgnoreCase(word).or('position').startsWithIgnoreCase(word).or('secondName').startsWithIgnoreCase(word).or('title').startsWithIgnoreCase(word).primaryKeys();
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
	  dialog: {
	    entities: [{
	      'id': 'im-bot',
	      'options': {
	        'searchableBotTypes': ['H', 'B', 'S', 'N']
	      },
	      'dynamicLoad': true,
	      'dynamicSearch': true
	    }, {
	      'id': 'im-chat',
	      'options': {
	        'searchableChatTypes': ['C', 'O']
	      },
	      'dynamicLoad': true,
	      'dynamicSearch': true
	    }, {
	      'id': 'user',
	      'dynamicLoad': true,
	      'dynamicSearch': true,
	      'filters': [{
	        'id': 'im.userDataFilter'
	      }]
	    }, {
	      id: 'department',
	      dynamicLoad: true,
	      dynamicSearch: true,
	      options: {
	        selectMode: 'usersAndDepartments',
	        allowSelectRootDepartment: true
	      }
	    }],
	    preselectedItems: [],
	    clearUnavailableItems: false,
	    context: 'IM_CHAT_SEARCH'
	  }
	};

	class SearchService {
	  constructor($Bitrix) {
	    this.store = null;
	    this.cacheService = null;
	    im_v2_lib_logger.Logger.enable('log');
	    im_v2_lib_logger.Logger.enable('warn');
	    this.store = $Bitrix.Data.get('controller').store;
	    this.cacheService = new SearchCacheService();
	    this.onItemSelectHandler = this.onItemSelect.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.search.selectItem, this.onItemSelectHandler);
	  }

	  getJsonConfig() {
	    return Config;
	  }

	  onItemSelect(event) {
	    const dialogId = event.getData();
	    const recentItem = this.dialogIdToRecentItem(dialogId);
	    this.cacheService.unshiftItem(recentItem);
	    this.addItemsToRecentSearchResults([{
	      id: recentItem[1],
	      entityId: recentItem[0]
	    }]);
	  }

	  loadDepartmentUsers(parentItem) {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.getChildren', {
	        json: { ...this.getJsonConfig(),
	          parentItem: parentItem
	        }
	      }).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: load department users result`, response);
	        this.cacheService.saveToCache(response.data.dialog);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  }

	  loadRecentSearch() {
	    return this.cacheService.loadRecentFromCache().then(result => {
	      if (result.recentItems.length === 0) {
	        return this.requestRecentSearch().then(response => {
	          return this.updateModels(response.items).then(() => {
	            return this.convertRecentItemsToDialogIds(response.recentItems);
	          });
	        });
	      }

	      this.updateModels(result.items).then(() => {
	        this.requestRecentSearch();
	      });
	      return this.convertRecentItemsToDialogIds(result.recentItems);
	    });
	  }

	  searchInCache(query) {
	    let wrongLayoutSearchPromise = Promise.resolve([]);

	    if (this.store.state.application.common.languageId === 'ru' && BX.correctText) {
	      // eslint-disable-next-line bitrix-rules/no-bx
	      const wrongLayoutQueryWords = this.splitQueryByWords(BX.correctText(query.trim()));
	      wrongLayoutSearchPromise = this.getDialogIdsByQueryWords(wrongLayoutQueryWords);
	    }

	    const correctLayoutQueryWords = this.splitQueryByWords(query.trim());
	    const correctLayoutSearchPromise = this.getDialogIdsByQueryWords(correctLayoutQueryWords);
	    return Promise.all([correctLayoutSearchPromise, wrongLayoutSearchPromise]).then(result => {
	      return [...new Set([...result[0], ...result[1]])];
	    });
	  }

	  getDialogIdsByQueryWords(queryWords) {
	    return this.cacheService.search(queryWords).then(items => {
	      return this.updateModels(items).then(() => {
	        return this.convertItemsToDialogIds(items);
	      });
	    });
	  }

	  requestRecentSearch() {
	    return main_core.ajax.runAction('ui.entityselector.load', {
	      json: this.getJsonConfig()
	    }).then(response => {
	      im_v2_lib_logger.Logger.warn(`Im.Search: Recent search request result`, response);
	      this.cacheService.saveToCache(response.data.dialog);
	      return response.data.dialog;
	    });
	  }

	  searchOnServer(query) {
	    return this.searchRequest(query).then(items => {
	      return this.updateModels(items, true).then(() => {
	        return {
	          items: this.convertItemsToDialogIds(items),
	          departments: items.filter(item => item.entityId === EntityIdTypes.department)
	        };
	      });
	    });
	  }

	  searchRequest(query) {
	    const config = this.getJsonConfig();
	    const queryWords = this.splitQueryByWords(query);
	    config.searchQuery = {
	      'queryWords': queryWords,
	      'query': query,
	      'dynamicSearchEntities': []
	    };
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('ui.entityselector.doSearch', {
	        json: config
	      }).then(response => {
	        im_v2_lib_logger.Logger.warn(`Im.Search: Search request result`, response);
	        this.cacheService.saveToCache(response.data.dialog);
	        resolve(response.data.dialog.items);
	      }).catch(error => reject(error));
	    });
	  } // todo: refactor (debounce & queue)


	  addItemsToRecentSearchResults(recentItems) {
	    if (!main_core.Type.isArrayFilled(recentItems)) {
	      return;
	    }

	    return main_core.ajax.runAction('ui.entityselector.saveRecentItems', {
	      json: { ...this.getJsonConfig(),
	        recentItems
	      }
	    });
	  }

	  updateModels(rawItems, set = false) {
	    const {
	      users,
	      dialogues
	    } = this.prepareDataForModels(rawItems);
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
	      if (!item.customData) {
	        return;
	      } // user


	      if (item.customData.imUser && item.customData.imUser.ID > 0) {
	        const preparedUser = this.toLowerCaseKeys(item.customData.imUser);
	        result.users.push(preparedUser);
	        result.dialogues.push({
	          avatar: preparedUser.avatar,
	          color: preparedUser.color,
	          name: preparedUser.name,
	          type: im_v2_const.ChatTypes.user,
	          dialogId: item.id
	        });
	      } // chat


	      if (item.customData.imChat && item.customData.imChat.ID > 0) {
	        if (item.entityType === 'LINES') {
	          return;
	        }

	        const chat = this.toLowerCaseKeys(item.customData.imChat);
	        result.dialogues.push({ ...chat,
	          dialogId: `chat${chat.id}`
	        });
	      }
	    });
	    return result;
	  } // todo: move somewhere else


	  splitQueryByWords(query) {
	    const clearedQuery = query.replace('(', ' ').replace(')', ' ').replace('[', ' ').replace(']', ' ').replace('{', ' ').replace('}', ' ').replace('<', ' ').replace('>', ' ').replace('-', ' ').replace('#', ' ').replace('"', ' ').replace('\'', ' ').replace('/ss+/', ' ');
	    return clearedQuery.toLowerCase().split(' ').filter(word => word !== '');
	  }

	  toLowerCaseKeys(object) {
	    const result = {};
	    Object.keys(object).forEach(key => {
	      result[key.toLowerCase()] = object[key];
	    });
	    return result;
	  }

	  convertRecentItemsToDialogIds(recentItems) {
	    const dialogIds = [];
	    recentItems.forEach(item => {
	      if (item[0] === EntityIdTypes.chat) {
	        dialogIds.push(`chat${item[1]}`);
	      } else if (item[0] === EntityIdTypes.user || item[0] === EntityIdTypes.bot) {
	        dialogIds.push(item[1].toString());
	      }
	    });
	    return dialogIds;
	  }

	  convertItemsToDialogIds(items) {
	    const dialogIds = [];
	    items.forEach(item => {
	      if (item.entityType === 'LINES') {
	        return;
	      }

	      if (item.customData && item.customData.imChat && item.customData.imChat.ID > 0) {
	        dialogIds.push(`chat${item.customData.imChat.ID}`);
	      } else if (item.customData && item.customData.imUser && item.customData.imUser.ID > 0) {
	        dialogIds.push(item.customData.imUser.ID.toString());
	      }
	    });
	    return dialogIds;
	  }

	  dialogIdToRecentItem(dialogId) {
	    return dialogId.startsWith('chat') ? [EntityIdTypes.chat, Number.parseInt(dialogId.replace('chat', ''), 10)] : [EntityIdTypes.user, Number.parseInt(dialogId, 10)];
	  }

	}

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
	    usersDialogIds() {
	      return this.searchService.convertItemsToDialogIds(this.usersInDepartment);
	    }

	  },

	  created() {
	    this.searchService = new SearchService(this.$Bitrix);
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

	      this.searchService.loadDepartmentUsers(this.item).then(departmentUsers => {
	        this.usersInDepartment = departmentUsers;
	        this.isLoading = false;
	        this.expanded = true;
	      });
	    },

	    closeDepartment() {
	      this.expanded = false;
	    }

	  },
	  // language=Vue
	  template: `
		<div @click="onClick" class="bx-im-search-item">
			<div class="bx-im-search-avatar-wrap">
				<div :title="item.title" class="bx-im-component-avatar-wrap bx-im-component-avatar-size-l">
					<div class="bx-im-component-avatar-content bx-im-component-avatar-image bx-search-item-department-icon"></div>
				</div>
			</div>
			<div class="bx-im-search-result-item-title-content bx-im-component-chat-name-text">
				{{item.title}}
				<div class="bx-search-item-department-expand-button">
					<div v-if="isLoading" class="bx-search-item-department-expand-loader"></div>
					<div v-else-if="expanded" class="bx-search-item-department-down-arrow"></div>
					<div v-else class="bx-search-item-department-up-arrow"></div>
				</div>
			</div>
		</div>
		<template v-if="expanded">
			<SearchResultItem v-for="dialogId in usersDialogIds" :key="dialogId" :dialogId="dialogId" :child="true" />
		</template>
	`
	};

	const SearchResultSection = {
	  name: 'SearchResultSection',
	  components: {
	    SearchResultItem,
	    SearchResultDepartmentItem
	  },
	  props: {
	    items: {
	      type: Array,
	      required: true
	    },
	    hasChildren: {
	      type: Boolean,
	      default: false,
	      required: false
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    showMore: {
	      type: Boolean,
	      default: false,
	      required: false
	    }
	  },
	  computed: {
	    phrases() {
	      return ui_vue3.BitrixVue.getFilteredPhrases(this, 'IM_SEARCH_');
	    }

	  },
	  // language=Vue
	  template: `
		<div class="bx-messenger-search-result-section-wrapper">
			<div class="bx-messenger-search-result-section-title">
				<div>{{title}}</div>
				<div v-if="showMore" class="bx-messenger-search-result-section-show-more">
					{{phrases['IM_SEARCH_SECTION_TITLE_SHOW_MORE']}}
				</div>
			</div>
			<template v-if="!hasChildren">
				<SearchResultItem v-for="item in items" :key="item" :dialogId="item" />
			</template>
			<template v-else>
				<SearchResultDepartmentItem v-for="item in items" :key="item" :item="item" />
			</template>
		</div>
	`
	};

	const LoadingState = {
	  name: 'LoadingState',
	  data: function () {
	    return {
	      itemsToShow: 50
	    };
	  },
	  template: `
		<div class="bx-im-recent-loading-state">
			<div v-for="index in itemsToShow" class="bx-im-recent-item">
				<div class="bx-im-recent-avatar-wrap">
					<div class="bx-im-recent-avatar-image-wrap">
						<div class="bx-im-recent-avatar bx-im-recent-placeholder-avatar"></div>
					</div>
				</div>
				<div class="bx-im-recent-item-content">
					<div class="bx-im-recent-item-content-header">
						<div class="bx-im-recent-item-placeholder-title"></div>
					</div>
					<div class="bx-im-recent-item-content-bottom">
						<div class="bx-im-recent-message-text-wrap">
							<div class="bx-im-recent-item-placeholder-subtitle"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`
	};

	const Search = {
	  components: {
	    RecentUsersCarousel,
	    SearchResultSection,
	    LoadingState
	  },
	  props: {
	    searchQuery: {
	      type: String,
	      required: true
	    }
	  },
	  data: function () {
	    return {
	      minTokenSize: 3,
	      isLoading: false,
	      result: {
	        recent: [],
	        usersAndChats: [],
	        departments: []
	      }
	    };
	  },
	  computed: {
	    showSearchResult() {
	      return this.searchQuery.length > 0;
	    },

	    phrases() {
	      return ui_vue3.BitrixVue.getFilteredPhrases(this, 'IM_SEARCH_');
	    }

	  },
	  watch: {
	    searchQuery(value) {
	      if (value.length > 0 && value.length < this.minTokenSize) {
	        this.searchInLocal(value);
	      } else if (value.length >= this.minTokenSize) {
	        this.isLoading = true;
	        this.searchInLocal(value);
	        this.searchOnServerDelayed(value);
	      } else {
	        this.cleanSearchResult();
	      }
	    }

	  },

	  mounted() {
	    this.isLoading = true;
	    this.searchService.loadRecentSearch().then(recentDialogIdsCollection => {
	      this.result.recent = recentDialogIdsCollection;
	      this.isLoading = false;
	    });
	  },

	  created() {
	    this.minTokenSize = main_core.Extension.getSettings('im.v2.component.search').get('minTokenSize');
	    this.searchService = new SearchService(this.$Bitrix);
	    this.searchOnServerDelayed = main_core.Runtime.debounce(this.searchOnServer, 1500, this);
	  },

	  methods: {
	    cleanSearchResult() {
	      this.result.usersAndChats = [];
	      this.result.departments = [];
	    },

	    searchOnServer(query) {
	      this.searchService.searchOnServer(query).then(searchResultFromServer => {
	        searchResultFromServer.items.forEach(searchItem => {
	          const exist = this.result.usersAndChats.includes(searchItem);

	          if (!exist) {
	            this.result.usersAndChats.push(searchItem);
	          }
	        });
	        this.result.departments = searchResultFromServer.departments;
	        this.isLoading = false;
	      });
	    },

	    searchInLocal(query) {
	      this.searchService.searchInCache(query).then(localSearchResult => {
	        this.result.usersAndChats = localSearchResult;
	      });
	    }

	  },
	  // language=Vue
	  template: `
		<div class="bx-messenger-search">
			<div>
				<template v-if="!showSearchResult">
					<RecentUsersCarousel :title="phrases['IM_SEARCH_SECTION_EMPLOYEES']"/>
				</template>
				<template v-if="!showSearchResult">
					<SearchResultSection :items="result.recent" :title="phrases['IM_SEARCH_SECTION_RECENT']"/>
				</template>
				<template v-if="showSearchResult">
					<SearchResultSection 
						v-if="result.usersAndChats.length > 0" 
						:items="result.usersAndChats" 
						:title="phrases['IM_SEARCH_SECTION_USERS_AND_CHATS']"
					/>
					<template v-if="!isLoading && searchQuery.length >= 3">
						<SearchResultSection 
							v-if="result.departments.length > 0" 
							:items="result.departments" 
							:title="phrases['IM_SEARCH_SECTION_DEPARTMENTS']" 
							:showMore="result.departments.length > 5"
							:hasChildren="true"
						/>
					</template>
				</template>
				<loading-state v-if="isLoading" />
			</div>
		</div>
	`
	};

	exports.Search = Search;

}((this.BX.Messenger.v2 = this.BX.Messenger.v2 || {}),BX.Messenger.v2,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Event,BX,BX.Vue3,BX));
//# sourceMappingURL=search.bundle.js.map
