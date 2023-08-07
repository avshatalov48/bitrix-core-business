/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_vue_vuex,ui_designTokens,im_lib_utils,im_const,ui_vue,main_core_events) {
	'use strict';

	var RecentItem = ui_vue.BitrixVue.localComponent('bx-im-component-recent-item', {
	  props: ['itemData'],
	  methods: {
	    onClick: function onClick(event) {
	      this.$emit('click', {
	        id: this.item.id,
	        $event: event
	      });
	    },
	    onRightClick: function onRightClick(event) {
	      this.$emit('rightClick', {
	        id: this.item.id,
	        $event: event
	      });
	    },
	    formatDate: function formatDate(date) {
	      var weekDays = [this.localize['IM_RECENT_WEEKDAY_0'], this.localize['IM_RECENT_WEEKDAY_1'], this.localize['IM_RECENT_WEEKDAY_2'], this.localize['IM_RECENT_WEEKDAY_3'], this.localize['IM_RECENT_WEEKDAY_4'], this.localize['IM_RECENT_WEEKDAY_5'], this.localize['IM_RECENT_WEEKDAY_6']];
	      date = date ? new Date(date) : new Date();
	      var currentDate = new Date();
	      var dateWeekDay = date.getDay() - (date.getDay() === 0 ? -6 : 1);
	      var currentDayOfWeek = currentDate.getDay() - (currentDate.getDay() === 0 ? -6 : 1);
	      var weekStartDate = currentDate.getDate() - currentDayOfWeek;
	      var weekStartTime = new Date(new Date(new Date().setDate(weekStartDate)).setHours(0, 0, 0)).getTime();
	      if (date.getFullYear() === currentDate.getFullYear() && date.getMonth() === currentDate.getMonth() && date.getDate() === currentDate.getDate()) {
	        return im_lib_utils.Utils.date.format(date, 'H:i');
	      } else if (date.getTime() > weekStartTime) {
	        return weekDays[dateWeekDay];
	      } else if (date.getFullYear() === currentDate.getFullYear()) {
	        return im_lib_utils.Utils.date.format(date, 'd.m');
	      } else {
	        return im_lib_utils.Utils.date.format(date, 'd.m.Y');
	      }
	    },
	    getTypingUsers: function getTypingUsers() {
	      if (this.isChat && this.dialogData && this.isSomeoneTyping) {
	        return this.dialogData.writingList;
	      }
	      if (this.isUser && this.isSomeoneTyping) {
	        var userDialog = this.getUserDialog(this.rawItem.userId);
	        return userDialog.writingList;
	      }
	      return false;
	    },
	    getUserDialog: function getUserDialog(userId) {
	      return this.$root.$store.getters['dialogues/get'](userId);
	    }
	  },
	  computed: {
	    ItemTypes: function ItemTypes() {
	      return im_const.TemplateTypes;
	    },
	    rawItem: function rawItem() {
	      return this.itemData;
	    },
	    item: function item() {
	      return {
	        id: this.rawItem.id,
	        template: this.rawItem.template,
	        type: this.rawItem.chatType,
	        sectionCode: this.rawItem.sectionCode,
	        title: {
	          leftIcon: this.titleLeftIcon,
	          value: this.titleValue,
	          rightIcon: this.titleRightIcon
	        },
	        subtitle: {
	          leftIcon: this.subtitleLeftIcon,
	          value: this.subtitleValue
	        },
	        avatar: {
	          url: this.avatarUrl,
	          bottomRightIcon: this.avatarBottomRightIcon
	        },
	        message: this.rawItem.message,
	        date: {
	          leftIcon: this.dateLeftIcon,
	          value: this.formatDate(this.rawItem.message ? this.rawItem.message.date : 0)
	        },
	        counter: {
	          value: this.rawItem.counter,
	          leftIcon: this.counterLeftIcon
	        },
	        notification: false
	      };
	    },
	    //background for pinned item
	    listItemStyle: function listItemStyle() {
	      if (this.rawItem.sectionCode === im_const.RecentSection.pinned) {
	        return {
	          backgroundColor: '#f7f7f7'
	        };
	      }
	      return {};
	    },
	    //avatar background color if no image
	    imageStyle: function imageStyle() {
	      var backgroundColor = '';
	      if (!this.item.avatar.url) {
	        backgroundColor = this.imageColor;
	      }
	      return {
	        backgroundColor: backgroundColor
	      };
	    },
	    //color of user, chat or notify
	    imageColor: function imageColor() {
	      if (this.isUser && this.userData) {
	        return this.userData.color;
	      }
	      if (this.isChat && this.dialogData) {
	        return this.dialogData.color;
	      }
	      if (this.isNotificationChat) {
	        return this.rawItem.color;
	      }
	    },
	    //class for general chat icon
	    imageClass: function imageClass() {
	      var classes = 'bx-im-recent-item-image ';
	      if (this.isGeneralChat) {
	        classes += 'bx-im-recent-item-image-general';
	      }
	      return classes;
	    },
	    //text on avatar if no image
	    avatarText: function avatarText() {
	      var title = this.item.title.value.replace(/[\.\,\'\"]/g, ''); // TODO set special chars entity
	      var words = title.split(' ');
	      if (words.length > 1) {
	        return words[0].charAt(0) + words[1].charAt(0);
	      } else if (words.length === 1) {
	        return words[0].charAt(0);
	      }
	    },
	    //placeholder for general chat, url for users and chats
	    avatarUrl: function avatarUrl() {
	      if (this.isGeneralChat) {
	        return '/bitrix/js/im/images/blank.gif';
	      }
	      if (this.isUser && this.userData) {
	        return this.userData.avatar;
	      }
	      if (this.isChat && this.dialogData) {
	        return this.dialogData.avatar;
	      }
	    },
	    //Priority of avatar bottom right icon (only for users)
	    //1.typing
	    //2.mobile online
	    //3.manual set away or dnd
	    //4.online
	    //5.offline
	    avatarBottomRightIcon: function avatarBottomRightIcon() {
	      if (this.isUser && !this.isBot) {
	        if (this.isSomeoneTyping) {
	          return 'typing';
	        } else if (this.userData) {
	          if (this.userData.isMobileOnline) {
	            return 'mobile-online';
	          } else if (this.userData.isOnline) {
	            return this.userData.status;
	          } else {
	            return 'offline';
	          }
	        }
	      }
	      return 'none';
	    },
	    //Title left icon
	    //For users:
	    //1.absent
	    //2.birthday
	    //For chats - type of chat
	    titleLeftIcon: function titleLeftIcon() {
	      if (this.isUser) {
	        if (this.isBot) {
	          return 'bot';
	        } else if (this.isExtranet) {
	          return 'extranet';
	        } else if (this.isNetwork) {
	          return 'network';
	        } else if (this.userData) {
	          if (this.userData.isAbsent) {
	            return 'absent';
	          } else if (this.userData.isBirthday) {
	            return 'birthday';
	          }
	        }
	        return '';
	      }
	      if (this.isChat) {
	        return this.rawItem.chatType;
	      }
	    },
	    //chat name
	    titleValue: function titleValue() {
	      if (this.isUser && this.userData) {
	        return this.userData.name;
	      }
	      if (this.isChat && this.dialogData) {
	        return this.dialogData.name;
	      }
	      if (this.isNotificationChat) {
	        return this.rawItem.title;
	      }
	      return this.rawItem.title;
	    },
	    //muted notifications icon for chats
	    titleRightIcon: function titleRightIcon() {
	      return this.isChatMuted ? 'muted' : '';
	    },
	    //icon if we wrote last message
	    subtitleLeftIcon: function subtitleLeftIcon() {
	      if (this.isLastMessageAuthor) {
	        return 'author';
	      }
	      return '';
	    },
	    //subtitle - typing message or last message text
	    subtitleValue: function subtitleValue() {
	      if (this.isSomeoneTyping && this.isUser) {
	        return this.localize['IM_RECENT_USER_TYPING'];
	      } else if (this.isSomeoneTyping && this.isChat) {
	        var typingUsers = this.getTypingUsers();
	        if (typingUsers.length === 1) {
	          var nameWords = typingUsers[0].userName.split(' ');
	          return "".concat(nameWords[0], " ").concat(this.localize['IM_RECENT_USER_TYPING']);
	        } else if (typingUsers.length > 1) {
	          return "".concat(this.localize['IM_RECENT_USERS_TYPING']);
	        }
	      }
	      if (!this.rawItem.message || !this.rawItem.message.text) {
	        return this.userData.workPosition;
	      }
	      return this.rawItem.message.text;
	    },
	    //message read status icon (if current user's message was read by someone in chat)
	    dateLeftIcon: function dateLeftIcon() {
	      if (!this.isLastMessageAuthor || this.isBot || this.isNotificationChat) {
	        return '';
	      }
	      if (!this.rawItem.message) {
	        return '';
	      }
	      if (this.rawItem.message.status === im_const.MessageStatus.error) {
	        return 'error';
	      }
	      var wasRead = this.rawItem.message.status === im_const.MessageStatus.delivered;
	      if (wasRead) {
	        return 'read';
	      }
	      return 'unread';
	    },
	    //pinned icon
	    counterLeftIcon: function counterLeftIcon() {
	      return this.rawItem.pinned ? 'pinned' : '';
	    },
	    //grey counter style for muted chats
	    counterClasses: function counterClasses() {
	      var classes = ['bx-im-recent-item-bottom-counter-value'];
	      if (this.isChatMuted) {
	        classes.push('bx-im-recent-item-bottom-counter-value-muted');
	      }
	      return classes;
	    },
	    formattedCounter: function formattedCounter() {
	      return this.item.counter.value > 99 ? '99+' : this.item.counter.value;
	    },
	    userData: function userData() {
	      return this.$root.$store.getters['users/get'](this.rawItem.userId, true);
	    },
	    dialogData: function dialogData() {
	      return this.$root.$store.getters['dialogues/getByChatId'](this.rawItem.chatId);
	    },
	    currentUserId: function currentUserId() {
	      return this.$root.$store.state.application.common.userId;
	    },
	    isChat: function isChat() {
	      return [im_const.ChatTypes.chat, im_const.ChatTypes.open].includes(this.rawItem.chatType);
	    },
	    isUser: function isUser() {
	      return this.rawItem.chatType === im_const.ChatTypes.user;
	    },
	    isExtranet: function isExtranet() {
	      if (this.isUser && this.userData) {
	        return this.userData.extranet;
	      }
	      return false;
	    },
	    isNetwork: function isNetwork() {
	      if (this.isUser && this.userData) {
	        return this.userData.network;
	      }
	      return false;
	    },
	    isBot: function isBot() {
	      if (this.isUser && this.userData) {
	        return this.userData.bot;
	      }
	      return false;
	    },
	    isNotificationChat: function isNotificationChat() {
	      return this.rawItem.id === 'notify';
	    },
	    isGeneralChat: function isGeneralChat() {
	      return this.rawItem.id === 'chat1';
	    },
	    isSomeoneTyping: function isSomeoneTyping() {
	      if (this.isUser) {
	        var userDialog = this.getUserDialog(this.rawItem.userId);
	        if (!userDialog) {
	          return false;
	        }
	        return Object.keys(userDialog.writingList).length > 0;
	      } else if (this.isChat && this.dialogData) {
	        return Object.keys(this.dialogData.writingList).length > 0;
	      }
	      return false;
	    },
	    isLastMessageAuthor: function isLastMessageAuthor() {
	      if (!this.rawItem.message) {
	        return false;
	      }
	      return this.currentUserId === this.rawItem.message.senderId;
	    },
	    isChatMuted: function isChatMuted() {
	      var _this = this;
	      if (this.isChat && this.dialogData) {
	        var isMuted = this.dialogData.muteList.find(function (element) {
	          return element === _this.currentUserId;
	        });
	        return !!isMuted;
	      }
	      return false;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('IM_RECENT_', this);
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-recent-item\" :style=\"listItemStyle\" @click=\"onClick\" @click.right=\"onRightClick\">\n\t\t\t<template v-if=\"item.template !== ItemTypes.placeholder\">\n\t\t\t\t<div v-if=\"item.avatar\" class=\"bx-im-recent-item-image-wrap\">\n\t\t\t\t\t<img v-if=\"item.avatar.url\" :src=\"item.avatar.url\" :style=\"imageStyle\" :class=\"imageClass\" alt=\"\">\n\t\t\t\t\t<div v-else-if=\"!item.avatar.url\" :style=\"imageStyle\" class=\"bx-im-recent-item-image-text\">{{ avatarText }}</div>\t\n\t\t\t\t\t<div v-if=\"item.avatar.topLeftIcon\" :class=\"'bx-im-recent-icon-avatar-top-left bx-im-recent-avatar-top-left-' + item.avatar.topLeftIcon\"></div>\n\t\t\t\t\t<div v-if=\"item.avatar.bottomRightIcon\" :class=\"'bx-im-recent-icon-avatar-bottom-right bx-im-recent-avatar-bottom-right-' + item.avatar.bottomRightIcon\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-recent-item-content\">\n\t\t\t\t\t<div class=\"bx-im-recent-item-content-header\">\n\t\t\t\t\t\t<div v-if=\"item.title\" class=\"bx-im-recent-item-header-title\">\n\t\t\t\t\t\t\t<div v-if=\"item.title.leftIcon\" :class=\"'bx-im-recent-icon-title-left bx-im-recent-icon-title-left-' + item.title.leftIcon\"></div>\n\t\t\t\t\t\t\t<span class=\"bx-im-recent-item-header-title-text\">{{ item.title.value }}</span>\n\t\t\t\t\t\t\t<div v-if=\"item.title.rightIcon\" :class=\"'bx-im-recent-icon-title-right bx-im-recent-icon-title-right-' + item.title.rightIcon\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div v-if=\"item.date\" class=\"bx-im-recent-item-header-date\">\n\t\t\t\t\t\t\t<div v-if=\"item.date.leftIcon\" :class=\"'bx-im-recent-icon-date-left bx-im-recent-icon-date-left-' + item.date.leftIcon\"></div>\n\t\t\t\t\t\t\t{{ item.date.value }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-im-recent-item-content-bottom\">\n\t\t\t\t\t\t<div v-if=\"item.subtitle\" class=\"bx-im-recent-item-bottom-subtitle\">\n\t\t\t\t\t\t\t<div v-if=\"item.subtitle.leftIcon\" :class=\"'bx-im-recent-icon-subtitle-left bx-im-recent-icon-subtitle-left-' + item.subtitle.leftIcon\"></div>\n\t\t\t\t\t\t\t<span class=\"bx-im-recent-item-bottom-subtitle-text\">{{ item.subtitle.value }}</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-recent-item-bottom-counter\">\n\t\t\t\t\t\t\t<div v-if=\"item.counter.leftIcon\" :class=\"'bx-im-recent-icon-counter-left bx-im-recent-icon-counter-left-' + item.counter.leftIcon\"></div>\n\t\t\t\t\t\t\t<div v-if=\"item.counter.value > 0\" :class=\"counterClasses\">{{ formattedCounter }}</div>\n\t\t\t\t\t\t\t<div v-else-if=\"item.notification\" class=\"bx-im-recent-item-bottom-counter-notification\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"item.template === ItemTypes.placeholder\">\n\t\t\t\t<div class=\"bx-im-recent-item-image-wrap\">\n\t\t\t\t\t<div class=\"bx-im-recent-item-image bx-im-recent-item-placeholder-image\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-recent-item-content\">\n\t\t\t\t\t<div class=\"bx-im-recent-item-content-header\">\n\t\t\t\t\t\t<div class=\"bx-im-recent-item-placeholder-title\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-im-recent-item-content-bottom\">\n\t\t\t\t\t\t<div class=\"bx-im-recent-item-bottom-subtitle\">\n\t\t\t\t\t\t\t<div class=\"bx-im-recent-item-placeholder-subtitle\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */
	ui_vue.BitrixVue.component('bx-im-component-recent', {
	  components: {
	    RecentItem: RecentItem
	  },
	  props: {
	    hasDialog: false
	  },
	  data: function data() {
	    return {
	      paginationCount: 50,
	      loadingMore: false,
	      hasMoreToLoad: true,
	      placeholderCount: 0,
	      lastMessageDate: null
	    };
	  },
	  created: function created() {},
	  mounted: function mounted() {
	    this.drawPlaceholders().then(this.getFirstPage);
	    this.initObserver();
	  },
	  computed: _objectSpread({
	    pinnedItems: function pinnedItems() {
	      return this.collection.filter(function (item) {
	        return item.pinned === true;
	      });
	    },
	    generalItems: function generalItems() {
	      return this.collection.filter(function (item) {
	        return item.pinned === false;
	      });
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    collection: function collection(state) {
	      return state.recent.collection;
	    }
	  })),
	  methods: {
	    /* region 01. Handlers */onScroll: function onScroll(event) {
	      if (this.oneScreenRemaining(event)) {
	        this.loadNextPage();
	      }
	    },
	    onClick: function onClick(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.open, event);
	    },
	    onRightClick: function onRightClick(event) {
	      this.openOldContextMenu(event);
	    },
	    /* endregion 01. Handlers */
	    /* region 02. Ex-controller */
	    generatePlaceholders: function generatePlaceholders(amount) {
	      var placeholders = [];
	      for (var i = 0; i < amount; i++) {
	        placeholders.push({
	          id: 'placeholder' + this.placeholderCount,
	          templateId: 'placeholder' + this.placeholderCount,
	          template: im_const.TemplateTypes.placeholder,
	          sectionCode: im_const.RecentSection.general
	        });
	        this.placeholderCount++;
	      }
	      return placeholders;
	    },
	    drawPlaceholders: function drawPlaceholders() {
	      var placeholders = this.generatePlaceholders(this.paginationCount);
	      return this.$store.dispatch('recent/addPlaceholders', placeholders);
	    },
	    getFirstPage: function getFirstPage() {
	      var _this = this;
	      var queryParams = {
	        'SKIP_OPENLINES': 'Y',
	        'LIMIT': this.paginationCount
	      };
	      this.getRestClient().callMethod(im_const.RestMethod.imRecentList, queryParams).then(function (result) {
	        //save last message date to load next items starting from it
	        _this.lastMessageDate = _this.getLastMessageDate(result.data().items);

	        //if we got less items than page count = no more items
	        if (!result.data().hasMore) {
	          _this.hasMoreToLoad = false;
	        }
	        _this.$store.dispatch('recent/clearPlaceholders');
	        //set first chunk of real data in rest handler
	        _this.getController().executeRestAnswer(im_const.RestMethodHandler.imRecentList, result);
	      });
	    },
	    loadNextPage: function loadNextPage() {
	      var _this2 = this;
	      if (this.loadingMore || !this.hasMoreToLoad) {
	        return false;
	      }
	      this.loadingMore = true;
	      //get first placeholder which we need to update with new data
	      this.firstPlaceholderToUpdate = this.placeholderCount;

	      //draw new placeholders and get next items from backend
	      this.drawPlaceholders().then(function () {
	        _this2.getNextPage();
	      });
	    },
	    getNextPage: function getNextPage() {
	      var _this3 = this;
	      var queryParams = {
	        'SKIP_OPENLINES': 'Y',
	        'LIMIT': this.paginationCount,
	        'LAST_MESSAGE_DATE': this.lastMessageDate
	      };
	      this.getRestClient().callMethod(im_const.RestMethod.imRecentList, queryParams).then(function (result) {
	        var items = result.data().items;
	        //if we got nothing - clear placeholders
	        if (!items || items.length === 0) {
	          _this3.$store.dispatch('recent/clearPlaceholders');
	          return false;
	        }
	        //if we got less items than page count = there are no more items
	        if (!result.data().hasMore) {
	          _this3.hasMoreToLoad = false;
	        }
	        //save last message date to load next items starting from it
	        _this3.lastMessageDate = _this3.getLastMessageDate(items);
	        _this3.updateModels(items).then(function () {
	          _this3.loadingMore = false;
	          //if we got less items than page count - clear remaining placeholders
	          if (!_this3.hasMoreToLoad) {
	            _this3.$store.dispatch('recent/clearPlaceholders');
	          }
	        });
	      });
	    },
	    getLastMessageDate: function getLastMessageDate(collection) {
	      return collection.slice(-1)[0].message.date;
	    },
	    updateModels: function updateModels(items) {
	      var data = this.prepareDataForModels(items);
	      var usersPromise = this.$store.dispatch('users/set', data.users);
	      var dialoguesPromise = this.$store.dispatch('dialogues/set', data.dialogues);
	      var recentPromise = this.$store.dispatch('recent/updatePlaceholders', {
	        items: data.recent,
	        firstMessage: this.firstPlaceholderToUpdate
	      });
	      return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	    },
	    prepareDataForModels: function prepareDataForModels(items) {
	      var result = {
	        users: [],
	        dialogues: [],
	        recent: []
	      };
	      items.forEach(function (item) {
	        var userId = 0;
	        var chatId = 0;
	        if (item.user && item.user.id > 0) {
	          userId = item.user.id;
	          result.users.push(item.user);
	        }
	        if (item.chat) {
	          chatId = item.chat.id;
	          result.dialogues.push(Object.assign(item.chat, {
	            dialogId: item.id
	          }));
	        } else {
	          result.dialogues.push(Object.assign({}, {
	            dialogId: item.id
	          }));
	        }
	        result.recent.push(_objectSpread(_objectSpread({}, item), {}, {
	          avatar: item.avatar.url,
	          color: item.avatar.color,
	          userId: userId,
	          chatId: chatId
	        }));
	      });
	      return result;
	    },
	    /* endregion 02. Ex-controller */
	    /* region 03. Actions */
	    openOldDialog: function openOldDialog(event) {
	      if (event.id !== 'notify') {
	        BXIM.openMessenger(event.id);
	      } else {
	        BXIM.openNotify();
	      }
	    },
	    openOldContextMenu: function openOldContextMenu(event) {
	      event.$event.preventDefault();
	      var recentItem = this.$store.getters['recent/get'](event.id);
	      if (!recentItem) {
	        return false;
	      }
	      var params = {
	        userId: event.id,
	        userIsChat: event.id.toString().startsWith('chat'),
	        dialogIsPinned: recentItem.element.pinned
	      };
	      BXIM.messenger.openPopupMenu(event.$event.target, 'contactList', undefined, params);
	    },
	    /* endregion 03. Actions */
	    /* region 04. Helpers */
	    getController: function getController() {
	      return this.$Bitrix.Data.get('controller');
	    },
	    getRestClient: function getRestClient() {
	      return this.$Bitrix.RestClient.get();
	    },
	    oneScreenRemaining: function oneScreenRemaining(event) {
	      return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
	    },
	    initObserver: function initObserver() {
	      this.observer = new IntersectionObserver(function (entries) {
	        entries.forEach(function (entry) {
	          if (entry.isIntersecting && entry.intersectionRatio === 1) ;
	        });
	      }, {
	        threshold: [0, 1]
	      });
	    } /* endregion 04. Helpers */
	  },
	  directives: {
	    'recent-list-observer': {
	      inserted: function inserted(element, bindings, vnode) {
	        vnode.context.observer.observe(element);
	      }
	    }
	  },
	  // language=Vue
	  template: "\n\t\t\t<div class=\"bx-messenger-recent\">\n\t\t\t\t<div class=\"bx-messenger-recent-list\" @scroll=\"onScroll\">\n\t\t\t\t\t<template v-for=\"item in pinnedItems\">\n\t\t\t\t\t\t<recent-item\n\t\t\t\t\t\t\t:itemData=\"item\"\n\t\t\t\t\t\t\t:key=\"item.id\"\n\t\t\t\t\t\t\t:data-id=\"item.id\"\n\t\t\t\t\t\t\tv-recent-list-observer\n\t\t\t\t\t\t\t@click=\"onClick\"\n\t\t\t\t\t\t\t@rightClick=\"onRightClick\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</template>\n\t\t\t\t\t<template v-for=\"item in generalItems\">\n\t\t\t\t\t\t<recent-item\n\t\t\t\t\t\t\t:itemData=\"item\"\n\t\t\t\t\t\t\t:key=\"item.id\"\n\t\t\t\t\t\t\t:data-id=\"item.id\"\n\t\t\t\t\t\t\tv-recent-list-observer\n\t\t\t\t\t\t\t@click=\"onClick\"\n\t\t\t\t\t\t\t@rightClick=\"onRightClick\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</template>\n\t\t\t\t</div>\n\t\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,BX.Messenger.Lib,BX.Messenger.Const,BX,BX.Event));
//# sourceMappingURL=recent.bundle.js.map
