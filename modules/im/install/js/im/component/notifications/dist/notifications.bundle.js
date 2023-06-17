this.BX = this.BX || {};
(function (exports,im_lib_animation,ui_forms,ui_designTokens,im_view_element_attach,im_view_element_keyboard,ui_vue,main_core,ui_vue_vuex,im_lib_logger,ui_vue_portal,im_view_popup,main_popup,im_lib_utils,im_const,im_lib_timer,main_core_events) {
	'use strict';

	var NotificationQuickAnswer = {
	  props: ['listItem'],
	  data: function data() {
	    return {
	      quickAnswerText: '',
	      quickAnswerResultMessage: '',
	      showQuickAnswer: false,
	      isSendingQuickAnswer: false,
	      successSentQuickAnswer: false
	    };
	  },
	  methods: {
	    toggleQuickAnswer: function toggleQuickAnswer() {
	      var _this = this;
	      if (this.successSentQuickAnswer) {
	        this.showQuickAnswer = true;
	        this.successSentQuickAnswer = false;
	        this.quickAnswerResultMessage = '';
	      } else {
	        this.showQuickAnswer = !this.showQuickAnswer;
	      }
	      if (this.showQuickAnswer) {
	        this.$nextTick(function () {
	          _this.$refs['input'].focus();
	        });
	      }
	    },
	    sendQuickAnswer: function sendQuickAnswer(event) {
	      var _this2 = this;
	      if (this.quickAnswerText.trim() === '') {
	        return;
	      }
	      this.isSendingQuickAnswer = true;
	      var notificationId = event.item.id;
	      this.$Bitrix.RestClient.get().callMethod('im.notify.answer', {
	        notify_id: notificationId,
	        answer_text: this.quickAnswerText
	      }).then(function (result) {
	        _this2.quickAnswerResultMessage = result.data().result_message[0];
	        _this2.successSentQuickAnswer = true;
	        _this2.quickAnswerText = '';
	        _this2.isSendingQuickAnswer = false;
	      })["catch"](function (error) {
	        console.error(error);
	        _this2.quickAnswerResultMessage = result.data().result_message[0];
	        _this2.isSendingQuickAnswer = false;
	      });
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div class=\"bx-notifier-item-text-vue\">\n\t\t\t<div class=\"bx-notifier-answer-link-vue\">\n\t\t\t\t<span class=\"bx-notifier-answer-reply bx-messenger-ajax\" @click=\"toggleQuickAnswer()\" @dblclick.stop>\n\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_QUICK_ANSWER_BUTTON') }}\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t\t<transition name=\"quick-answer-slide\">\n\t\t\t\t<div v-if=\"showQuickAnswer && !successSentQuickAnswer\" class=\"bx-notifier-answer-box-vue\">\n\t\t\t\t\t<span v-if=\"isSendingQuickAnswer\" class=\"bx-notifier-answer-progress-vue bx-messenger-content-load-img\"></span>\n\t\t\t\t\t<span class=\"bx-notifier-answer-input\">\n\t\t\t\t\t\t<input\n\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\tref=\"input\"\n\t\t\t\t\t\t\tautofocus\n\t\t\t\t\t\t\tclass=\"bx-messenger-input\"\n\t\t\t\t\t\t\tv-model=\"quickAnswerText\"\n\t\t\t\t\t\t\t:disabled=\"isSendingQuickAnswer\"\n\t\t\t\t\t\t\t@keyup.enter=\"sendQuickAnswer({item: listItem, event: $event})\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div class=\"bx-notifier-answer-button\" @click=\"sendQuickAnswer({item: listItem, event: $event})\"></div>\n\t\t\t\t</div>\n\t\t\t</transition>\n\t\t\t<div v-if=\"successSentQuickAnswer\" class=\"bx-notifier-answer-text-vue\">\n\t\t\t\t{{ quickAnswerResultMessage }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	// @vue/component
	var NotificationItemHeader = {
	  props: {
	    listItem: {
	      type: Object,
	      required: true
	    },
	    isExtranet: {
	      type: Boolean,
	      "default": false
	    }
	  },
	  computed: {
	    moreUsers: function moreUsers() {
	      var phrase = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_MORE_USERS').split('#COUNT#');
	      return {
	        start: phrase[0],
	        end: this.listItem.params.USERS.length + phrase[1]
	      };
	    },
	    isMoreUsers: function isMoreUsers() {
	      return this.listItem.params.hasOwnProperty('USERS') && this.listItem.params.USERS.length > 0;
	    },
	    isAbleToDelete: function isAbleToDelete() {
	      return this.listItem.sectionCode === im_const.NotificationTypesCodes.simple;
	    }
	  },
	  methods: {
	    onDeleteClick: function onDeleteClick(event) {
	      if (event.item.sectionCode === im_const.NotificationTypesCodes.simple) {
	        this.$emit('deleteClick', event);
	      }
	    },
	    onMoreUsersClick: function onMoreUsersClick(event) {
	      if (event.users) {
	        this.$emit('moreUsersClick', {
	          event: event.event,
	          content: {
	            type: 'USERS',
	            value: event.users
	          }
	        });
	      }
	    },
	    onUserTitleClick: function onUserTitleClick(event) {
	      if (window.top["BXIM"] && event.userId > 0) {
	        window.top["BXIM"].openMessenger(event.userId);
	      }
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div class=\"bx-im-notifications-item-content-header\">\n\t\t\t<div v-if=\"listItem.title\" class=\"bx-im-notifications-item-header-title\">\n\t\t\t\t<span\n\t\t\t\t\tv-if=\"!listItem.systemType\"\n\t\t\t\t\t@click.prevent=\"onUserTitleClick({userId: listItem.authorId, event: $event})\"\n\t\t\t\t\tclass=\"bx-im-notifications-item-header-title-text-link\"\n\t\t\t\t\t:class=\"[isExtranet ? '--extranet' : '']\"\n\t\t\t\t>\n\t\t\t\t\t{{ listItem.title.value }}\n\t\t\t\t</span>\n\t\t\t\t<span v-else class=\"bx-im-notifications-item-header-title-text\">{{ listItem.title.value }}</span>\n\t\t\t\t<span\n\t\t\t\t\tv-if=\"isMoreUsers && !listItem.systemType\"\n\t\t\t\t\tclass=\"bx-im-notifications-item-header-more-users\"\n\t\t\t\t>\n\t\t\t\t\t{{ moreUsers.start }}\n\t\t\t\t\t<span class=\"bx-messenger-ajax\" @click=\"onMoreUsersClick({users: listItem.params.USERS, event: $event})\">\n\t\t\t\t\t\t{{ moreUsers.end }}\n\t\t\t\t\t</span>\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-notifications-item-content-header-right\">\n\t\t\t\t<div class=\"bx-im-notifications-item-header-date\">\n\t\t\t\t\t{{ listItem.date.value }}\n\t\t\t\t</div>\n\t\t\t\t<span\n\t\t\t\t\tv-if=\"isAbleToDelete\"\n\t\t\t\t\tclass=\"bx-im-notifications-item-header-delete\"\n\t\t\t\t\t@click=\"onDeleteClick({item: listItem, event: $event})\">\n\t\t\t\t</span>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var NotificationPlaceholder = {
	  //language=Vue
	  template: "\n\t\t<div style=\"display: flex; width: 100%;\">\n\t\t\t<div class=\"bx-im-notifications-item-image-wrap\">\n\t\t\t\t<div class=\"bx-im-notifications-item-image bx-im-notifications-item-placeholder-image\"></div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-notifications-item-content bx-im-notifications-skeleton\">\n\t\t\t\t<div class=\"bx-im-notifications-item-content-header\">\n\t\t\t\t\t<div class=\"bx-im-notifications-item-placeholder-title\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-notifications-item-content-middle\">\n\t\t\t\t\t<div class=\"bx-im-notifications-item-bottom-subtitle\">\n\t\t\t\t\t\t<div class=\"bx-im-notifications-item-placeholder-subtitle\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-notifications-item-content-bottom\">\n\t\t\t\t\t<div class=\"bx-im-notifications-item-bottom-subtitle\">\n\t\t\t\t\t\t<div class=\"bx-im-notifications-item-placeholder-subtitle\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var NotificationItem = {
	  components: {
	    NotificationQuickAnswer: NotificationQuickAnswer,
	    NotificationItemHeader: NotificationItemHeader,
	    NotificationPlaceholder: NotificationPlaceholder
	  },
	  props: ['rawListItem', 'searchMode'],
	  data: function data() {
	    return {
	      menuId: 'popup-window-content-bx-messenger-popup-notify'
	    };
	  },
	  computed: {
	    NotificationTypesCodes: function NotificationTypesCodes() {
	      return im_const.NotificationTypesCodes;
	    },
	    listItem: function listItem() {
	      return {
	        id: this.rawListItem.id,
	        type: this.rawListItem.type,
	        sectionCode: this.rawListItem.sectionCode,
	        authorId: this.rawListItem.authorId,
	        systemType: this.rawListItem.type === 4 || this.rawListItem.authorId === 0 && this.avatar === '',
	        title: {
	          value: this.userTitle
	        },
	        subtitle: {
	          value: this.rawListItem.textConverted
	        },
	        avatar: {
	          url: this.avatar,
	          color: this.defaultAvatarColor
	        },
	        params: this.rawListItem.params || {},
	        notifyButtons: this.rawListItem.notifyButtons || undefined,
	        unread: this.rawListItem.unread,
	        settingName: this.rawListItem.settingName,
	        date: {
	          value: im_lib_utils.Utils.date.format(this.rawListItem.date, null, this.$Bitrix.Loc.getMessages())
	        }
	      };
	    },
	    isRealItem: function isRealItem() {
	      return this.rawListItem.sectionCode !== im_const.NotificationTypesCodes.placeholder;
	    },
	    isNeedQuickAnswer: function isNeedQuickAnswer() {
	      return this.listItem.params.CAN_ANSWER && this.listItem.params.CAN_ANSWER === 'Y';
	    },
	    userTitle: function userTitle() {
	      if (this.isRealItem && this.rawListItem.authorId > 0) {
	        return this.userData.name;
	      }
	      var title = this.rawListItem.title;
	      return title.length > 0 ? title : this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_ITEM_SYSTEM');
	    },
	    avatar: function avatar() {
	      var avatar = '';
	      if (this.isRealItem && this.rawListItem.authorId > 0) {
	        avatar = this.userData.avatar;
	      } else if (this.isRealItem && this.rawListItem.authorId === 0) {
	        //system notification
	        return '';
	      }
	      return avatar;
	    },
	    defaultAvatarColor: function defaultAvatarColor() {
	      if (this.rawListItem.authorId <= 0) {
	        return '';
	      }
	      return this.userData.color;
	    },
	    userData: function userData() {
	      return this.$store.getters['users/get'](this.rawListItem.authorId, true);
	    },
	    isExtranet: function isExtranet() {
	      return this.userData.extranet;
	    },
	    avatarStyles: function avatarStyles() {
	      return {
	        backgroundImage: 'url("' + this.listItem.avatar.url + '")'
	      };
	    }
	  },
	  methods: {
	    //events
	    onDoubleClick: function onDoubleClick(event) {
	      if (!this.searchMode) {
	        this.$emit('dblclick', event);
	      }
	    },
	    onButtonsClick: function onButtonsClick(event) {
	      if (event.action === 'COMMAND') {
	        this.$emit('buttonsClick', event);
	      }
	    },
	    onDeleteClick: function onDeleteClick(event) {
	      this.$emit('deleteClick', event);
	    },
	    onMoreUsersClick: function onMoreUsersClick(event) {
	      this.$emit('contentClick', event);
	    },
	    onContentClick: function onContentClick(event) {
	      if (ui_vue.Vue.testNode(event.target, {
	        className: 'bx-im-mention'
	      })) {
	        this.$emit('contentClick', {
	          event: event,
	          content: {
	            type: event.target.dataset.type,
	            value: event.target.dataset.value
	          }
	        });
	      }
	    },
	    onRightClick: function onRightClick(event) {
	      var _this = this;
	      if (im_lib_utils.Utils.platform.isBitrixDesktop() && event.target.tagName === 'A' && (!event.target.href.startsWith('/desktop_app/') || event.target.href.startsWith('/desktop_app/show.file.php'))) {
	        var hrefToCopy = event.target.href;
	        if (!hrefToCopy) {
	          return;
	        }
	        if (this.menuPopup) {
	          this.menuPopup.destroy();
	          this.menuPopup = null;
	        }

	        //menu for other items
	        var existingMenu = main_popup.PopupManager.getPopupById(this.menuId);
	        if (existingMenu) {
	          existingMenu.destroy();
	        }
	        var menuItem = main_core.Dom.create('span', {
	          attrs: {
	            className: 'bx-messenger-popup-menu-item-text bx-messenger-popup-menu-item'
	          },
	          events: {
	            click: function click(event) {
	              BX.desktop.clipboardCopy(hrefToCopy);
	              _this.menuPopup.destroy();
	              _this.menuPopup = null;
	            }
	          },
	          text: this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_CONTEXT_COPY_LINK')
	        });
	        this.menuPopup = main_popup.PopupManager.create({
	          id: this.menuId,
	          targetContainer: document.body,
	          className: BX.MessengerTheme.isDark() ? 'bx-im-notifications-popup-window-dark' : '',
	          darkMode: BX.MessengerTheme.isDark(),
	          bindElement: event,
	          offsetLeft: 13,
	          autoHide: true,
	          closeByEsc: true,
	          events: {
	            onPopupClose: function onPopupClose() {
	              return _this.menuPopup.destroy();
	            },
	            onPopupDestroy: function onPopupDestroy() {
	              return _this.menuPopup = null;
	            }
	          },
	          content: menuItem
	        });
	        if (!BX.MessengerTheme.isDark()) {
	          this.menuPopup.setAngle({});
	        }
	        this.menuPopup.show();
	      }
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div \n\t\t\tclass=\"bx-im-notifications-item\"\n\t\t\t:class=\"[listItem.unread && !searchMode ? 'bx-im-notifications-item-unread' : '']\"\n\t\t\t@dblclick=\"onDoubleClick({item: listItem, event: $event})\"\n\t\t\t@contextmenu=\"onRightClick\"\n\t\t>\n\t\t\t<template v-if=\"listItem.sectionCode !== NotificationTypesCodes.placeholder\">\n\t\t\t\t<div v-if=\"listItem.avatar\" class=\"bx-im-notifications-item-image-wrap\">\n\t\t\t\t\t<div \n\t\t\t\t\t\tv-if=\"listItem.avatar.url\" \n\t\t\t\t\t\tclass=\"bx-im-notifications-item-image\"\n\t\t\t\t\t\t:style=\"avatarStyles\"\n\t\t\t\t\t></div>\n\t\t\t\t\t<div v-else-if=\"listItem.systemType\" class=\"bx-im-notifications-item-image bx-im-notifications-image-system\"></div>\n\t\t\t\t\t<div \n\t\t\t\t\t\tv-else-if=\"!listItem.avatar.url\" \n\t\t\t\t\t\tclass=\"bx-im-notifications-item-image bx-im-notifications-item-image-default\"\n\t\t\t\t\t\t:style=\"{backgroundColor: listItem.avatar.color}\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-notifications-item-content\" @click=\"onContentClick\">\n\t\t\t\t\t<NotificationItemHeader \n\t\t\t\t\t\t:listItem=\"listItem\"\n\t\t\t\t\t\t:isExtranet=\"isExtranet\"\n\t\t\t\t\t\t@deleteClick=\"onDeleteClick\"\n\t\t\t\t\t\t@moreUsersClick=\"onMoreUsersClick\"\n\t\t\t\t\t/>\n\t\t\t\t\t<div v-if=\"listItem.subtitle.value.length > 0\" class=\"bx-im-notifications-item-content-bottom\">\n\t\t\t\t\t\t<div class=\"bx-im-notifications-item-bottom-subtitle\">\n\t\t\t\t\t\t\t<span\n\t\t\t\t\t\t\t\t:class=\"[!listItem.title.value ? 'bx-im-notifications-item-bottom-subtitle-text' : 'bx-im-notifications-item-bottom-no-subtitle-text']\"\n\t\t\t\t\t\t\t\tv-html=\"listItem.subtitle.value\"\n\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<NotificationQuickAnswer v-if=\"isNeedQuickAnswer\" :listItem=\"listItem\"/>\n\t\t\t\t\t<div v-if=\"listItem.params['ATTACH']\" class=\"bx-im-notifications-item-content-additional\">\n\t\t\t\t\t\t<div v-for=\"attach in listItem.params['ATTACH']\">\n\t\t\t\t\t\t\t<bx-im-view-element-attach :config=\"attach\"/>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-if=\"listItem.notifyButtons\">\n\t\t\t\t\t\t<bx-im-view-element-keyboard @click=\"onButtonsClick\" :buttons=\"listItem.notifyButtons\"/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<NotificationPlaceholder v-else-if=\"listItem.sectionCode === NotificationTypesCodes.placeholder\"/>\n\t\t</div>\n\t"
	};

	var NotificationCore = {
	  data: function data() {
	    return {
	      placeholderCount: 0
	    };
	  },
	  methods: {
	    isReadyToLoadNewPage: function isReadyToLoadNewPage(event) {
	      var leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
	      return leftSpaceBottom < 200; //pixels offset before load new page
	    },
	    getLastItemId: function getLastItemId(collection) {
	      return collection[collection.length - 1].id;
	    },
	    generatePlaceholders: function generatePlaceholders(amount) {
	      var placeholders = [];
	      for (var i = 0; i < amount; i++) {
	        placeholders.push({
	          id: "placeholder".concat(this.placeholderCount),
	          type: im_const.NotificationTypesCodes.placeholder
	        });
	        this.placeholderCount++;
	      }
	      return placeholders;
	    },
	    getRestClient: function getRestClient() {
	      return this.$Bitrix.RestClient.get();
	    },
	    onContentClick: function onContentClick(event) {
	      var _this = this;
	      this.contentPopupType = event.content.type.toLowerCase();
	      this.contentPopupValue = event.content.value;
	      if (this.popupInstance != null) {
	        this.popupInstance.destroy();
	        this.popupInstance = null;
	      }

	      // TODO: replace it with new popups.
	      if (this.contentPopupType === 'user' || this.contentPopupType === 'chat') {
	        var popupAngle = !this.isDarkTheme;
	        BXIM.messenger.openPopupExternalData(event.event.target, this.contentPopupType, popupAngle, {
	          'ID': this.contentPopupValue
	        });
	      } else if (this.contentPopupType === 'openlines') {
	        BX.MessengerCommon.linesGetSessionHistory(this.contentPopupValue);
	      } else {
	        var popup = main_popup.PopupManager.create({
	          id: "bx-messenger-popup-external-data",
	          targetContainer: document.body,
	          className: this.isDarkTheme ? 'bx-im-notifications-popup-window-dark' : '',
	          bindElement: event.event.target,
	          lightShadow: true,
	          offsetTop: 0,
	          offsetLeft: 10,
	          autoHide: true,
	          closeByEsc: true,
	          bindOptions: {
	            position: "top"
	          },
	          events: {
	            onPopupClose: function onPopupClose() {
	              return _this.popupInstance.destroy();
	            },
	            onPopupDestroy: function onPopupDestroy() {
	              return _this.popupInstance = null;
	            }
	          }
	        });
	        if (!this.isDarkTheme) {
	          popup.setAngle({});
	        }
	        this.popupIdSelector = "#".concat(popup.getContentContainer().id);

	        //little hack for correct open several popups in a row.
	        this.$nextTick(function () {
	          return _this.popupInstance = popup;
	        });
	      }
	    }
	  },
	  computed: {
	    isDarkTheme: function isDarkTheme() {
	      if (this.darkTheme === undefined) {
	        return BX.MessengerTheme.isDark();
	      }
	      return this.darkTheme;
	    }
	  }
	};

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var NotificationSearchResult = {
	  components: {
	    NotificationItem: NotificationItem,
	    MountingPortal: ui_vue_portal.MountingPortal,
	    Popup: im_view_popup.Popup
	  },
	  mixins: [NotificationCore],
	  props: ['searchQuery', 'searchType', 'searchDate'],
	  data: function data() {
	    return {
	      pageLimit: 50,
	      lastId: 0,
	      initialDataReceived: false,
	      isLoadingNewPage: false,
	      contentPopupType: '',
	      contentPopupValue: '',
	      popupInstance: null,
	      popupIdSelector: '',
	      searchResultsTotal: 0,
	      searchPageLoaded: 0,
	      searchPagesRequested: 0
	    };
	  },
	  computed: _objectSpread({
	    remainingPages: function remainingPages() {
	      return Math.ceil((this.searchResultsTotal - this.searchResults.length) / this.pageLimit);
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    notification: function notification(state) {
	      return state.notifications.collection;
	    },
	    searchResults: function searchResults(state) {
	      return state.notifications.searchCollection;
	    }
	  })),
	  watch: {
	    searchQuery: function searchQuery(value) {
	      if (value.length >= 3 || value === '') {
	        this.search();
	      }
	    },
	    searchType: function searchType() {
	      this.search();
	    },
	    searchDate: function searchDate(value) {
	      if (BX.parseDate(value) instanceof Date || value === '') {
	        this.search();
	      }
	    }
	  },
	  created: function created() {
	    this.searchServerDelayed = im_lib_utils.Utils.debounce(this.getSearchResultsFromServer, 1500, this);
	    this.search();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.$store.dispatch('notifications/deleteSearchResults');
	  },
	  methods: {
	    search: function search() {
	      var _this = this;
	      this.resetSearchState();
	      var localResults = this.notification.filter(function (item) {
	        var result = false;
	        if (_this.searchQuery.length >= 3) {
	          result = item.textConverted.toLowerCase().includes(_this.searchQuery.toLowerCase());
	          if (!result) {
	            return result;
	          }
	        }
	        if (_this.searchType !== '') {
	          result = item.settingName === _this.searchType;
	          if (!result) {
	            return result;
	          }
	        }
	        if (_this.searchDate !== '') {
	          var date = BX.parseDate(_this.searchDate);
	          if (date instanceof Date) {
	            // compare dates excluding time.
	            var itemDateForCompare = new Date(item.date.getTime()).setHours(0, 0, 0, 0);
	            var dateFromInput = date.setHours(0, 0, 0, 0);
	            result = itemDateForCompare === dateFromInput;
	          }
	        }
	        return result;
	      });
	      if (localResults.length > 0) {
	        this.$store.dispatch('notifications/setSearchResults', {
	          notification: localResults,
	          type: 'local'
	        });
	      }
	      var isNeedPlaceholders = this.pageLimit - localResults.length > 0;
	      if (isNeedPlaceholders > 0) {
	        this.drawPlaceholders(this.pageLimit).then(function () {
	          _this.searchServerDelayed();
	        });
	      } else {
	        this.searchServerDelayed();
	      }
	    },
	    getSearchResultsFromServer: function getSearchResultsFromServer() {
	      var _this2 = this;
	      var queryParams = this.getSearchRequestParams();
	      this.getRestClient().callMethod('im.notify.history.search', queryParams).then(function (result) {
	        im_lib_logger.Logger.warn('im.notify.history.search: first page results', result.data());
	        _this2.processHistoryData(result.data());
	        _this2.initialDataReceived = true;
	        _this2.isLoadingNewPage = false;
	        _this2.searchPageLoaded++;
	      })["catch"](function (result) {
	        im_lib_logger.Logger.warn('History request error', result);
	      });
	    },
	    processHistoryData: function processHistoryData(data) {
	      this.$store.dispatch('notifications/clearPlaceholders');
	      if (data.notifications.length <= 0) {
	        return false;
	      }
	      this.lastId = this.getLastItemId(data.notifications);
	      this.searchResultsTotal = data.total_results;
	      this.$store.dispatch('notifications/setSearchResults', {
	        notification: data.notifications
	      });
	      this.$store.dispatch('users/set', data.users);
	      this.isLoadingNewPage = false;
	    },
	    loadNextPage: function loadNextPage() {
	      var _this3 = this;
	      im_lib_logger.Logger.warn("Loading more search results!");
	      var queryParams = this.getSearchRequestParams();
	      this.getRestClient().callMethod('im.notify.history.search', queryParams).then(function (result) {
	        im_lib_logger.Logger.warn('im.notify.history.search: new page results', result.data());
	        var newUsers = result.data().users;
	        var newItems = result.data().notifications;
	        if (!newItems || newItems.length === 0) {
	          _this3.$store.dispatch('notifications/clearPlaceholders');
	          _this3.searchResultsTotal = _this3.searchResults.length;
	          return false;
	        }
	        _this3.lastId = _this3.getLastItemId(newItems);
	        _this3.$store.dispatch('users/set', newUsers);
	        return _this3.$store.dispatch('notifications/updatePlaceholders', {
	          searchCollection: true,
	          items: newItems,
	          firstItem: _this3.searchPageLoaded * _this3.pageLimit
	        });
	      }).then(function () {
	        _this3.searchPageLoaded++;
	        return _this3.onAfterLoadNextPageRequest();
	      })["catch"](function (result) {
	        _this3.$store.dispatch('notifications/clearPlaceholders');
	        im_lib_logger.Logger.warn('History request error', result);
	      });
	    },
	    onAfterLoadNextPageRequest: function onAfterLoadNextPageRequest() {
	      im_lib_logger.Logger.warn('onAfterLoadNextPageRequest');
	      if (this.searchPagesRequested > 0) {
	        im_lib_logger.Logger.warn('We have delayed requests -', this.searchPagesRequested);
	        this.searchPagesRequested--;
	        return this.loadNextPage();
	      } else {
	        im_lib_logger.Logger.warn('No more delayed requests, clearing placeholders');
	        this.$store.dispatch('notifications/clearPlaceholders');
	        this.isLoadingNewPage = false;
	        return true;
	      }
	    },
	    getSearchRequestParams: function getSearchRequestParams() {
	      var params = {
	        'SEARCH_TEXT': this.searchQuery,
	        'SEARCH_TYPE': this.searchType,
	        'LIMIT': this.pageLimit,
	        'CONVERT_TEXT': 'Y'
	      };
	      if (BX.parseDate(this.searchDate) instanceof Date) {
	        params['SEARCH_DATE'] = BX.parseDate(this.searchDate).toISOString();
	      }
	      if (this.lastId > 0) {
	        params['LAST_ID'] = this.lastId;
	      }
	      return params;
	    },
	    resetSearchState: function resetSearchState() {
	      this.$store.dispatch('notifications/deleteSearchResults');
	      this.initialDataReceived = false;
	      this.lastId = 0;
	      this.isLoadingNewPage = true;
	      this.placeholderCount = 0;
	      this.searchPageLoaded = 0;
	    },
	    drawPlaceholders: function drawPlaceholders() {
	      var amount = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	      var placeholders = this.generatePlaceholders(amount);
	      return this.$store.dispatch('notifications/setSearchResults', {
	        notification: placeholders
	      });
	    },
	    //events
	    onScroll: function onScroll(event) {
	      var _this4 = this;
	      if (!this.isReadyToLoadNewPage(event) || !this.initialDataReceived || this.remainingPages <= 0) {
	        return;
	      }
	      if (this.isLoadingNewPage) {
	        this.drawPlaceholders(this.pageLimit).then(function () {
	          _this4.searchPagesRequested++;
	          im_lib_logger.Logger.warn('Already loading! Draw placeholders and add request, total - ', _this4.pagesRequested);
	        });
	      } else
	        //if (!this.isLoadingNewPage)
	        {
	          im_lib_logger.Logger.warn('Starting new request');
	          this.isLoadingNewPage = true;
	          this.drawPlaceholders(this.pageLimit).then(function () {
	            _this4.loadNextPage();
	          });
	        }
	    },
	    onButtonsClick: function onButtonsClick(event) {
	      var _this5 = this;
	      var params = this.getConfirmRequestParams(event);
	      var itemId = +params.NOTIFY_ID;
	      var notification = this.$store.getters['notifications/getById'](itemId);
	      this.getRestClient().callMethod('im.notify.confirm', params).then(function () {
	        _this5.$store.dispatch('notifications/delete', {
	          id: itemId
	        });
	        if (notification.unread) {
	          _this5.$store.dispatch('notifications/setCounter', {
	            unreadTotal: _this5.unreadCounter - 1
	          });
	        }
	      })["catch"](function () {
	        _this5.$store.dispatch('notifications/update', {
	          id: itemId,
	          fields: {
	            display: true
	          }
	        });
	      });
	      this.$store.dispatch('notifications/update', {
	        id: itemId,
	        fields: {
	          display: false
	        }
	      });
	    },
	    onDeleteClick: function onDeleteClick(event) {
	      var _this6 = this;
	      var itemId = +event.item.id;
	      var notification = this.$store.getters['notifications/getSearchItemById'](itemId);
	      this.getRestClient().callMethod('im.notify.delete', {
	        id: itemId
	      }).then(function () {
	        _this6.$store.dispatch('notifications/delete', {
	          id: itemId,
	          searchMode: true
	        });
	        //we need to load more, if we are on the first page and we have not enough elements (~15).
	        if (!_this6.isLoadingNewPage && _this6.remainingPages > 0 && _this6.searchResults.length < 15) {
	          _this6.isLoadingNewPage = true;
	          _this6.drawPlaceholders(_this6.pageLimit).then(function () {
	            _this6.loadNextPage();
	          });
	        }
	        if (notification.unread) {
	          _this6.$store.dispatch('notifications/setCounter', {
	            unreadTotal: _this6.unreadCounter - 1
	          });
	        }
	      })["catch"](function (error) {
	        console.error(error);
	        _this6.$store.dispatch('notifications/update', {
	          id: itemId,
	          fields: {
	            display: true
	          },
	          searchMode: true
	        });
	      });
	      this.$store.dispatch('notifications/update', {
	        id: itemId,
	        fields: {
	          display: false
	        },
	        searchMode: true
	      });
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div class=\"bx-messenger-notifications-search-results-wrap\" @scroll.passive=\"onScroll\">\n\t\t\t<notification-item\n\t\t\t\tv-for=\"listItem in searchResults\"\n\t\t\t\tv-if=\"listItem.display\"\n\t\t\t\t:key=\"listItem.id\"\n\t\t\t\t:data-id=\"listItem.id\"\n\t\t\t\t:rawListItem=\"listItem\"\n\t\t\t\tsearchMode=\"true\"\n\t\t\t\t@buttonsClick=\"onButtonsClick\"\n\t\t\t\t@contentClick=\"onContentClick\"\n\t\t\t\t@deleteClick=\"onDeleteClick\"\n\t\t\t/>\n\t\t\t<mounting-portal :mount-to=\"popupIdSelector\" append v-if=\"popupInstance\">\n\t\t\t\t<popup :type=\"contentPopupType\" :value=\"contentPopupValue\" :popupInstance=\"popupInstance\"/>\n\t\t\t</mounting-portal>\n\t\t\t<div \n\t\t\t\tv-if=\"searchResults.length <= 0\" \n\t\t\t\tstyle=\"padding-top: 210px; margin-bottom: 20px;\"\n\t\t\t\tclass=\"bx-messenger-box-empty bx-notifier-content-empty\" \n\t\t\t>\n\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_RESULTS_NOT_FOUND') }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ObserverType = Object.freeze({
	  read: 'read',
	  none: 'none'
	});

	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */
	ui_vue.BitrixVue.component('bx-im-component-notifications', {
	  components: {
	    NotificationItem: NotificationItem,
	    MountingPortal: ui_vue_portal.MountingPortal,
	    Popup: im_view_popup.Popup,
	    NotificationSearchResult: NotificationSearchResult
	  },
	  directives: {
	    'bx-im-directive-notifications-observer': {
	      inserted: function inserted(element, bindings, vnode) {
	        if (bindings.value === ObserverType.none) {
	          return false;
	        }
	        if (!vnode.context.observers[bindings.value]) {
	          vnode.context.observers[bindings.value] = vnode.context.getObserver({
	            type: bindings.value
	          });
	        }
	        vnode.context.observers[bindings.value].observe(element);
	        return true;
	      },
	      unbind: function unbind(element, bindings, vnode) {
	        if (bindings.value === ObserverType.none) {
	          return true;
	        }
	        if (vnode.context.observers[bindings.value]) {
	          vnode.context.observers[bindings.value].unobserve(element);
	        }
	        return true;
	      }
	    }
	  },
	  mixins: [NotificationCore],
	  props: {
	    darkTheme: {
	      "default": undefined
	    }
	  },
	  data: function data() {
	    return {
	      initialDataReceived: false,
	      perPage: 50,
	      isLoadingInitialData: false,
	      isLoadingNewPage: false,
	      pagesRequested: 0,
	      pagesLoaded: 0,
	      lastId: 0,
	      lastType: im_const.NotificationTypesCodes.confirm,
	      ObserverType: ObserverType,
	      notificationsOnScreen: [],
	      notificationsToRead: [],
	      notificationsToDelete: [],
	      changeReadStatusBlockTimeout: {},
	      firstUnreadNotificationOnInit: null,
	      contentPopupType: '',
	      contentPopupValue: '',
	      popupInstance: null,
	      popupIdSelector: '',
	      contextPopupInstance: null,
	      searchQuery: '',
	      searchType: '',
	      searchDate: '',
	      showSearch: false,
	      callViewState: false
	    };
	  },
	  computed: _objectSpread$1({
	    NotificationTypesCodes: function NotificationTypesCodes() {
	      return im_const.NotificationTypesCodes;
	    },
	    remainingPages: function remainingPages() {
	      return Math.ceil((this.total - this.notification.length) / this.perPage);
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('IM_NOTIFICATIONS_', this);
	    },
	    visibleNotifications: function visibleNotifications() {
	      return this.notification.filter(function (notificationItem) {
	        return notificationItem.display;
	      });
	    },
	    highestNotificationId: function highestNotificationId() {
	      return this.notification.reduce(function (highestId, currentNotification) {
	        return currentNotification.id > highestId ? currentNotification.id : highestId;
	      }, 0);
	    },
	    isNeedToReadAll: function isNeedToReadAll() {
	      return this.unreadCounter > 0;
	    },
	    panelStyles: function panelStyles() {
	      if (this.callViewState === BX.Call.Controller.ViewState.Folded && !this.showSearch) {
	        return {
	          paddingBottom: '60px' // height of .bx-messenger-videocall-panel-folded
	        };
	      }

	      return {};
	    },
	    filterBoxStyles: function filterBoxStyles() {
	      if (this.callViewState === BX.Call.Controller.ViewState.Folded && this.showSearch) {
	        return {
	          paddingTop: '70px' // height of .bx-messenger-videocall-panel-folded + 10px for space
	        };
	      }

	      return {};
	    },
	    firstUnreadNotification: function firstUnreadNotification() {
	      var unreadNotification = null;
	      var maxNotificationIndex = this.notification.length - 1;
	      for (var i = 0; i <= maxNotificationIndex; i++) {
	        if (this.notification[i].unread && this.notification[i].sectionCode !== im_const.NotificationTypesCodes.placeholder) {
	          unreadNotification = this.notification[i];
	          break;
	        }
	      }
	      return unreadNotification;
	    },
	    firstUnreadNotificationBelowVisible: function firstUnreadNotificationBelowVisible() {
	      var minIdOnScreen = Math.max.apply(Math, babelHelpers.toConsumableArray(this.notificationsOnScreen));
	      var unreadId = null;
	      var maxNotificationIndex = this.notification.length - 1;
	      for (var i = 0; i <= maxNotificationIndex; i++) {
	        if (this.notification[i].unread && minIdOnScreen > this.notification[i].id && this.notification[i].sectionCode === im_const.NotificationTypesCodes.simple) {
	          unreadId = this.notification[i].id;
	          break;
	        }
	      }
	      return unreadId;
	    },
	    isUnreadNotificationVisible: function isUnreadNotificationVisible() {
	      var _this = this;
	      var unreadOnScreen = Array.from(this.notificationsOnScreen).filter(function (idOnScreen) {
	        var notificationOnScreen = _this.$store.getters['notifications/getById'](idOnScreen);
	        return notificationOnScreen ? notificationOnScreen.unread : false;
	      });
	      return unreadOnScreen.length > 0;
	    },
	    showScrollButton: function showScrollButton() {
	      if (!this.initialDataReceived) {
	        return false;
	      }
	      if (this.unreadCounter <= 0 || !BXIM.settings.notifyAutoRead) {
	        return false;
	      }
	      if (this.notificationsOnScreen.length === 0) {
	        return false;
	      }
	      if (this.isUnreadNotificationVisible) {
	        return false;
	      }
	      return true;
	    },
	    hasUnreadBelowVisible: function hasUnreadBelowVisible() {
	      var unreadCounterBeforeVisible = 0;
	      for (var i = 0; i <= this.notification.length - 1; i++) {
	        if (this.notification[i].unread && this.notification[i].sectionCode !== im_const.NotificationTypesCodes.placeholder) {
	          ++unreadCounterBeforeVisible;
	        }

	        // In this case we decide that there is no more unread notifications below visible notifications,
	        // so we show arrow up on scroll button.
	        if (this.notificationsOnScreen.includes(this.notification[i].id) && this.unreadCounter === unreadCounterBeforeVisible) {
	          return false;
	        }
	      }
	      return true;
	    },
	    arrowButtonClass: function arrowButtonClass() {
	      var arrowUp = !this.hasUnreadBelowVisible;
	      return {
	        'bx-im-notifications-scroll-button-arrow-down': !arrowUp,
	        'bx-im-notifications-scroll-button-arrow-up': arrowUp,
	        'bx-im-notifications-scroll-button-arrow': true
	      };
	    },
	    filterTypes: function filterTypes() {
	      var originalSchema = Object.assign({}, this.schema);

	      // get rid of some subcategories
	      var modulesToReduceListItems = ['timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender', 'blog', 'vote', 'socialnetwork', 'imopenlines', 'photogallery', 'intranet', 'forum'];
	      modulesToReduceListItems.forEach(function (moduleId) {
	        if (originalSchema.hasOwnProperty(moduleId)) {
	          delete originalSchema[moduleId].LIST;
	        }
	      });

	      // rename some groups
	      if (originalSchema.hasOwnProperty('calendar')) {
	        originalSchema['calendar'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_CALENDAR'];
	      }
	      if (originalSchema.hasOwnProperty('sender')) {
	        originalSchema['sender'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SENDER'];
	      }
	      if (originalSchema.hasOwnProperty('blog')) {
	        originalSchema['blog'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_BLOG'];
	      }
	      if (originalSchema.hasOwnProperty('socialnetwork')) {
	        originalSchema['socialnetwork'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SOCIALNETWORK'];
	      }
	      if (originalSchema.hasOwnProperty('intranet')) {
	        originalSchema['intranet'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_INTRANET'];
	      }

	      // we need only this modules in this order!
	      var modulesToShowInFilter = ['tasks', 'calendar', 'crm', 'timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender', 'blog', 'vote', 'socialnetwork', 'imopenlines', 'photogallery', 'intranet', 'forum'];
	      var notificationFilterTypes = [];
	      modulesToShowInFilter.forEach(function (moduleId) {
	        if (originalSchema.hasOwnProperty(moduleId)) {
	          notificationFilterTypes.push(originalSchema[moduleId]);
	        }
	      });
	      return notificationFilterTypes;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    notification: function notification(state) {
	      return state.notifications.collection;
	    },
	    total: function total(state) {
	      return state.notifications.total;
	    },
	    unreadCounter: function unreadCounter(state) {
	      return state.notifications.unreadCounter;
	    },
	    schema: function schema(state) {
	      return state.notifications.schema;
	    }
	  })),
	  created: function created() {
	    var _this2 = this;
	    this.drawPlaceholders().then(function () {
	      _this2.getInitialData();
	    });
	    main_core_events.EventEmitter.subscribe(im_const.EventType.notification.updateState, this.onUpdateState);
	    window.addEventListener('focus', this.onWindowFocus);
	    window.addEventListener('blur', this.onWindowBlur);
	    if (BXIM && BX.Call) {
	      this.callViewState = BXIM.callController.callViewState;
	      BXIM.callController.subscribe(BX.Call.Controller.Events.onViewStateChanged, this.onCallViewStateChange);
	    }
	    this.timer = new im_lib_timer.Timer();
	    this.readNotificationsQueue = new Set();
	    this.readNotificationsNodes = {};
	    this.observers = {};
	    this.readVisibleNotificationsDelayed = im_lib_utils.Utils.debounce(this.readVisibleNotifications, 50, this);
	  },
	  mounted: function mounted() {
	    this.windowFocused = document.hasFocus();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.observers = {};
	    window.removeEventListener('focus', this.onWindowFocus);
	    window.removeEventListener('blur', this.onWindowBlur);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.notification.updateState, this.onUpdateState);
	    if (BXIM && BX.Call) {
	      BXIM.callController.unsubscribe(BX.Call.Controller.Events.onViewStateChanged, this.onCallViewStateChange);
	    }
	  },
	  methods: {
	    getFirstUnreadNotificationOnInit: function getFirstUnreadNotificationOnInit() {
	      if (this.unreadCounter <= 0) {
	        return null;
	      }
	      var unreadId = null;
	      var maxNotificationIndex = this.notification.length - 1;
	      for (var i = 0; i <= maxNotificationIndex; i++) {
	        if (this.notification[i].unread) {
	          unreadId = this.notification[i].id;
	          break;
	        }
	      }
	      return unreadId;
	    },
	    onCallViewStateChange: function onCallViewStateChange(_ref) {
	      var data = _ref.data;
	      this.callViewState = data.callViewState;
	    },
	    onUpdateState: function onUpdateState(event) {
	      var lastNotificationId = event.data.lastId;
	      if (!this.isLoadingInitialData && this.highestNotificationId > 0 && lastNotificationId !== this.highestNotificationId) {
	        this.getInitialData();
	      }
	    },
	    readVisibleNotifications: function readVisibleNotifications() {
	      var _this3 = this;
	      //todo: replace legacy chat API
	      if (!this.windowFocused || !BXIM.settings.notifyAutoRead) {
	        im_lib_logger.Logger.warn('reading is disabled!');
	        return false;
	      }
	      this.readNotificationsQueue.forEach(function (notificationId) {
	        if (_this3.readNotificationsNodes[notificationId]) {
	          delete _this3.readNotificationsNodes[notificationId];
	        }
	        _this3.readNotifications(parseInt(notificationId, 10));
	      });
	      this.readNotificationsQueue.clear();
	    },
	    getInitialData: function getInitialData() {
	      var _queryParams,
	        _this4 = this;
	      this.isLoadingInitialData = true;
	      var queryParams = (_queryParams = {}, babelHelpers.defineProperty(_queryParams, im_const.RestMethodHandler.imNotifyGet, [im_const.RestMethod.imNotifyGet, {
	        'LIMIT': this.perPage,
	        'CONVERT_TEXT': 'Y'
	      }]), babelHelpers.defineProperty(_queryParams, im_const.RestMethodHandler.imNotifySchemaGet, [im_const.RestMethod.imNotifySchemaGet, {}]), _queryParams);
	      this.getRestClient().callBatch(queryParams, function (response) {
	        im_lib_logger.Logger.warn('im.notify.get: initial result', response[im_const.RestMethodHandler.imNotifyGet].data());
	        _this4.processInitialData(response[im_const.RestMethodHandler.imNotifyGet].data());
	        _this4.processSchemaData(response[im_const.RestMethodHandler.imNotifySchemaGet].data());
	        _this4.pagesLoaded++;
	        _this4.isLoadingInitialData = false;
	        _this4.firstUnreadNotificationOnInit = _this4.getFirstUnreadNotificationOnInit();
	      }, false, false);
	    },
	    processInitialData: function processInitialData(data) {
	      //if we got empty data - clear all placeholders
	      if (!data.notifications || data.notifications.length === 0) {
	        this.$store.dispatch('notifications/clearPlaceholders');
	        this.$store.dispatch('notifications/setTotal', {
	          total: this.notification.length
	        });
	        return false;
	      }
	      this.lastId = this.getLastItemId(data.notifications);
	      this.lastType = this.getLastItemType(data.notifications);
	      this.$store.dispatch('notifications/clearPlaceholders');
	      this.$store.dispatch('notifications/setCounter', {
	        unreadTotal: data.total_unread_count
	      });
	      this.$store.dispatch('notifications/set', {
	        notification: data.notifications,
	        total: data.total_count
	      });
	      this.$store.dispatch('users/set', data.users);
	      this.updateRecentList(data.total_unread_count, true);
	      this.initialDataReceived = true;
	    },
	    processSchemaData: function processSchemaData(data) {
	      this.$store.dispatch('notifications/setSchema', {
	        data: data
	      });
	    },
	    drawPlaceholders: function drawPlaceholders() {
	      var placeholders = this.generatePlaceholders(this.perPage);
	      return this.$store.dispatch('notifications/set', {
	        notification: placeholders
	      });
	    },
	    loadNextPage: function loadNextPage() {
	      var _this5 = this;
	      im_lib_logger.Logger.warn("Loading more notifications!");
	      var queryParams = {
	        'LIMIT': this.perPage,
	        'LAST_ID': this.lastId,
	        'LAST_TYPE': this.lastType,
	        'CONVERT_TEXT': 'Y'
	      };
	      this.getRestClient().callMethod('im.notify.get', queryParams).then(function (result) {
	        im_lib_logger.Logger.warn('im.notify.get: new page results', result.data());
	        var newUsers = result.data().users;
	        var newItems = result.data().notifications;

	        //if we got empty data - clear all placeholders
	        if (!newItems || newItems.length === 0) {
	          _this5.$store.dispatch('notifications/clearPlaceholders');
	          _this5.$store.dispatch('notifications/setTotal', {
	            total: _this5.notification.length
	          });
	          return false;
	        }
	        _this5.lastId = _this5.getLastItemId(newItems);
	        _this5.lastType = _this5.getLastItemType(newItems);
	        _this5.$store.dispatch('users/set', newUsers);

	        //change temp data in models to real data, we need new items, first item to update and section
	        return _this5.$store.dispatch('notifications/updatePlaceholders', {
	          items: newItems,
	          firstItem: _this5.pagesLoaded * _this5.perPage
	        });
	      }).then(function () {
	        _this5.pagesLoaded++;
	        im_lib_logger.Logger.warn('Page loaded. Total loaded - ', _this5.pagesLoaded);
	        return _this5.onAfterLoadNextPageRequest();
	      })["catch"](function (result) {
	        im_lib_logger.Logger.warn('Request history error', result);
	      });
	    },
	    onAfterLoadNextPageRequest: function onAfterLoadNextPageRequest() {
	      im_lib_logger.Logger.warn('onAfterLoadNextPageRequest');
	      if (this.pagesRequested > 0) {
	        im_lib_logger.Logger.warn('We have delayed requests -', this.pagesRequested);
	        this.pagesRequested--;
	        return this.loadNextPage();
	      } else {
	        im_lib_logger.Logger.warn('No more delayed requests, clearing placeholders');
	        this.$store.dispatch('notifications/clearPlaceholders');
	        this.isLoadingNewPage = false;
	        return true;
	      }
	    },
	    changeReadStatus: function changeReadStatus(item) {
	      var _this6 = this;
	      this.$store.dispatch('notifications/read', {
	        ids: [item.id],
	        action: item.unread
	      });
	      // change the unread counter
	      var originalCounterBeforeUpdate = this.unreadCounter;
	      var counterValue = item.unread ? this.unreadCounter - 1 : this.unreadCounter + 1;
	      this.updateRecentList(counterValue);
	      this.$store.dispatch('notifications/setCounter', {
	        unreadTotal: counterValue
	      });
	      clearTimeout(this.changeReadStatusBlockTimeout[item.id]);
	      this.changeReadStatusBlockTimeout[item.id] = setTimeout(function () {
	        _this6.getRestClient().callMethod('im.notify.read', {
	          id: item.id,
	          action: item.unread ? 'Y' : 'N',
	          only_current: 'Y'
	        }).then(function () {
	          im_lib_logger.Logger.warn("Notification ".concat(item.id, " unread status set to ").concat(!item.unread));
	        })["catch"](function (error) {
	          console.error(error);
	          _this6.$store.dispatch('notifications/read', {
	            ids: [item.id],
	            action: !item.unread
	          });
	          // restore the unread counter in case of an error
	          _this6.updateRecentList(originalCounterBeforeUpdate);
	          _this6.$store.dispatch('notifications/setCounter', {
	            unreadTotal: originalCounterBeforeUpdate
	          });
	        });
	      }, 1500);
	    },
	    "delete": function _delete(item) {
	      var _this7 = this;
	      var itemId = +item.id;
	      this.notificationsToDelete.push(itemId);
	      var notification = this.$store.getters['notifications/getById'](itemId);
	      this.$store.dispatch('notifications/update', {
	        id: itemId,
	        fields: {
	          display: false
	        }
	      });
	      // change the unread counter
	      var originalCounterBeforeUpdate = this.unreadCounter;
	      var counterValue = notification.unread ? this.unreadCounter - 1 : this.unreadCounter;
	      this.updateRecentList(counterValue, true);
	      this.$store.dispatch('notifications/setCounter', {
	        unreadTotal: counterValue
	      });
	      this.timer.stop('deleteNotificationServer', 'notifications', true);
	      this.timer.start('deleteNotificationServer', 'notifications', .5, function () {
	        var idsToDelete = _this7.notificationsToDelete;
	        _this7.notificationsToDelete = [];
	        _this7.getRestClient().callMethod('im.notify.delete', {
	          id: idsToDelete
	        }).then(function () {
	          idsToDelete.forEach(function (id) {
	            _this7.$store.dispatch('notifications/delete', {
	              id: id
	            });
	          });
	        })["catch"](function (error) {
	          console.error(error);
	          idsToDelete.forEach(function (id) {
	            _this7.$store.dispatch('notifications/update', {
	              id: id,
	              fields: {
	                display: true
	              }
	            });
	          });

	          // restore the unread counter in case of an error
	          _this7.updateRecentList(originalCounterBeforeUpdate, true);
	          _this7.$store.dispatch('notifications/setCounter', {
	            unreadTotal: originalCounterBeforeUpdate
	          });
	        });
	      });
	    },
	    getObserver: function getObserver(config) {
	      var _this8 = this;
	      if (typeof window.IntersectionObserver === 'undefined' || config.type === ObserverType.none) {
	        return {
	          observe: function observe() {},
	          unobserve: function unobserve() {}
	        };
	      }
	      var observerCallback = function observerCallback(entries) {
	        entries.forEach(function (entry) {
	          var sendReadEvent = false;
	          var entryNotificationId = parseInt(entry.target.dataset.id, 10);
	          if (entry.isIntersecting) {
	            //on Windows with interface scaling intersectionRatio will never be 1
	            if (entry.intersectionRatio >= 0.99) {
	              sendReadEvent = true;
	              _this8.notificationsOnScreen.push(entryNotificationId);
	            } else if (entry.intersectionRatio > 0 && entry.intersectionRect.height > entry.rootBounds.height / 2) {
	              sendReadEvent = true;
	              _this8.notificationsOnScreen.push(entryNotificationId);
	            } else {
	              _this8.notificationsOnScreen = _this8.notificationsOnScreen.filter(function (notificationId) {
	                return notificationId !== entryNotificationId;
	              });
	            }
	          } else {
	            _this8.notificationsOnScreen = _this8.notificationsOnScreen.filter(function (notificationId) {
	              return notificationId !== entryNotificationId;
	            });
	          }
	          if (sendReadEvent) {
	            _this8.readNotificationsQueue.add(entryNotificationId);
	            _this8.readNotificationsNodes[entryNotificationId] = entry.target;
	          } else {
	            _this8.readNotificationsQueue["delete"](entryNotificationId);
	            delete _this8.readNotificationsNodes[entryNotificationId];
	          }
	          _this8.readVisibleNotificationsDelayed();
	        });
	      };
	      var observerOptions = {
	        root: this.$refs['listNotifications'],
	        threshold: new Array(101).fill(0).map(function (zero, index) {
	          return index * 0.01;
	        })
	      };
	      return new IntersectionObserver(observerCallback, observerOptions);
	    },
	    //events
	    onScroll: function onScroll(event) {
	      var _this9 = this;
	      if (!this.isReadyToLoadNewPage(event)) {
	        return;
	      }
	      if (this.remainingPages === 0 || !this.initialDataReceived) {
	        return;
	      }
	      if (this.isLoadingNewPage) {
	        this.drawPlaceholders().then(function () {
	          _this9.pagesRequested++;
	          im_lib_logger.Logger.warn('Already loading! Draw placeholders and add request, total - ', _this9.pagesRequested);
	        });
	      } else
	        //if (!this.isLoadingNewPage)
	        {
	          im_lib_logger.Logger.warn('Starting new request');
	          this.isLoadingNewPage = true;
	          this.drawPlaceholders().then(function () {
	            _this9.loadNextPage();
	          });
	        }
	    },
	    onWindowFocus: function onWindowFocus() {
	      this.windowFocused = true;
	      this.readVisibleNotifications();
	    },
	    onWindowBlur: function onWindowBlur() {
	      this.windowFocused = false;
	    },
	    onDoubleClick: function onDoubleClick(event) {
	      this.changeReadStatus(event.item);
	    },
	    onButtonsClick: function onButtonsClick(event) {
	      var _this10 = this;
	      var params = this.getConfirmRequestParams(event);
	      var itemId = +params.NOTIFY_ID;
	      this.$store.dispatch('notifications/update', {
	        id: itemId,
	        fields: {
	          display: false
	        }
	      });
	      // change the unread counter
	      var counterValueBeforeUpdate = this.unreadCounter;
	      var counterValue = this.unreadCounter - 1;
	      this.updateRecentList(counterValue, true);
	      this.$store.dispatch('notifications/setCounter', {
	        unreadTotal: counterValue
	      });
	      this.getRestClient().callMethod('im.notify.confirm', params).then(function () {
	        _this10.$store.dispatch('notifications/delete', {
	          id: itemId
	        });
	      })["catch"](function () {
	        _this10.$store.dispatch('notifications/update', {
	          id: itemId,
	          fields: {
	            display: true
	          }
	        });
	        // restore the unread counter in case of an error
	        _this10.updateRecentList(counterValueBeforeUpdate, true);
	        _this10.$store.dispatch('notifications/setCounter', {
	          unreadTotal: counterValueBeforeUpdate
	        });
	      });
	    },
	    onDeleteClick: function onDeleteClick(event) {
	      var _this11 = this;
	      this["delete"](event.item);

	      //we need to load more, if we are on the first page and we have more elements.
	      if (!this.isLoadingNewPage && this.remainingPages > 0 && this.notification.length === this.perPage - 1) {
	        this.isLoadingNewPage = true;
	        this.drawPlaceholders().then(function () {
	          _this11.loadNextPage();
	        });
	      }
	    },
	    onRightClick: function onRightClick(event) {
	      var _this12 = this;
	      if (this.contextPopupInstance !== null) {
	        this.closeContextMenuPopup();
	      }
	      var items = this.getContextMenu(event.item);
	      this.contextPopupInstance = main_popup.MenuManager.create({
	        id: 'bx-messenger-context-popup-external-data',
	        bindElement: event.event,
	        items: items,
	        events: {
	          onPopupClose: function onPopupClose() {
	            return _this12.contextPopupInstance.destroy();
	          },
	          onPopupDestroy: function onPopupDestroy() {
	            return _this12.contextPopupInstance = null;
	          }
	        }
	      });
	      this.contextPopupInstance.show();
	    },
	    onDateFilterClick: function onDateFilterClick(event) {
	      var _this13 = this;
	      if (typeof BX !== 'undefined' && BX.calendar && BX.calendar.get().popup) {
	        BX.calendar.get().popup.close();
	      }
	      BX.calendar({
	        node: event.target,
	        field: event.target,
	        bTime: false,
	        callback_after: function callback_after() {
	          _this13.searchDate = event.target.value;
	        }
	      });
	      return false;
	    },
	    getContextMenu: function getContextMenu(notification) {
	      var _this14 = this;
	      var unreadMenuItemText = notification.unread ? this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_SET_READ'] : this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_SET_UNREAD'];
	      var blockMenuItemText = main_core.Type.isUndefined(BXIM.settingsNotifyBlocked[notification.settingName]) ? this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_DONT_NOTIFY'] : this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_NOTIFY'];
	      return [{
	        text: unreadMenuItemText,
	        onclick: function onclick(event, item) {
	          _this14.changeReadStatus(notification);
	          _this14.closeContextMenuPopup();
	        }
	      }, {
	        text: this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_DELETE_NOTIFICATION'],
	        onclick: function onclick(event, item) {
	          _this14["delete"](notification);
	          _this14.closeContextMenuPopup();
	        }
	      }, {
	        text: blockMenuItemText,
	        onclick: function onclick(event, item) {
	          console.log(notification);
	          _this14.closeContextMenuPopup();
	        }
	      }];
	    },
	    closeContextMenuPopup: function closeContextMenuPopup() {
	      this.contextPopupInstance.destroy();
	      this.contextPopupInstance = null;
	    },
	    getConfirmRequestParams: function getConfirmRequestParams(event) {
	      if (event.params) {
	        var options = event.params.params.split('|');
	        return {
	          'NOTIFY_ID': options[0],
	          'NOTIFY_VALUE': options[1]
	        };
	      }
	      return null;
	    },
	    readNotifications: function readNotifications(notificationId) {
	      var _this15 = this;
	      var notification = this.$store.getters['notifications/getById'](notificationId);
	      if (notification.unread === false) {
	        return false;
	      }
	      this.notificationsToRead.push(notificationId);
	      // read on front
	      this.$store.dispatch('notifications/read', {
	        ids: [notificationId],
	        action: true
	      });

	      // change the unread counter
	      var counterValueBeforeUpdate = this.unreadCounter;
	      var counterValue = this.unreadCounter - 1;
	      this.$store.dispatch('notifications/setCounter', {
	        unreadTotal: counterValue
	      });
	      // update recent counter
	      this.updateRecentList(counterValue);
	      this.timer.stop('readNotificationServer', 'notifications', true);
	      this.timer.start('readNotificationServer', 'notifications', .5, function () {
	        var idsToRead = _this15.notificationsToRead;
	        _this15.notificationsToRead = [];

	        // we can read all notifications from some ID, only if we have not received new notifications
	        // (otherwise we will read notifications at the top that we are not actually seeing)
	        var canReadFromId = false;
	        if (_this15.firstUnreadNotificationOnInit !== null) {
	          canReadFromId = Math.max.apply(Math, babelHelpers.toConsumableArray(idsToRead)) <= _this15.firstUnreadNotificationOnInit;
	        }
	        var restMethod = 'im.notify.read.list';
	        var requestParams = {
	          ids: idsToRead,
	          action: 'Y'
	        };
	        if (canReadFromId) {
	          var readFromId = Math.min.apply(Math, babelHelpers.toConsumableArray(idsToRead));
	          restMethod = 'im.notify.read';
	          requestParams = {
	            id: readFromId,
	            action: 'Y'
	          };
	        }
	        _this15.getRestClient().callMethod(restMethod, requestParams).then(function () {
	          im_lib_logger.Logger.warn('I have read the notifications', requestParams);
	        })["catch"](function () {
	          _this15.$store.dispatch('notifications/read', {
	            ids: idsToRead,
	            action: false
	          });
	          // restore the unread counter in case of an error
	          _this15.$store.dispatch('notifications/setCounter', {
	            unreadTotal: counterValueBeforeUpdate
	          });
	          _this15.updateRecentList(counterValueBeforeUpdate);
	        });
	      });
	    },
	    getLastItemType: function getLastItemType(collection) {
	      return this.getItemType(collection[collection.length - 1]);
	    },
	    getItemType: function getItemType(item) {
	      if (item.notify_type === im_const.NotificationTypesCodes.confirm) {
	        return im_const.NotificationTypesCodes.confirm;
	      } else {
	        return im_const.NotificationTypesCodes.simple;
	      }
	    },
	    getLatest: function getLatest() {
	      var latestNotification = {
	        id: 0
	      };
	      var _iterator = _createForOfIteratorHelper(this.notification),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var notification = _step.value;
	          if (notification.id > latestNotification.id) {
	            latestNotification = notification;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      return latestNotification;
	    },
	    //todo: refactor this method for the new chat
	    showConfirmPopupOnReadAll: function showConfirmPopupOnReadAll() {
	      var readAll = this.readAll.bind(this);
	      BXIM.openConfirm(this.localize['IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP'], [new BX.PopupWindowButton({
	        text: this.localize['IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP_YES'],
	        className: 'popup-window-button-accept',
	        events: {
	          click: function click() {
	            readAll();
	            this.popupWindow.close();
	          }
	        }
	      }), new BX.PopupWindowButton({
	        text: this.localize['IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP_CANCEL'],
	        className: 'popup-window-button',
	        events: {
	          click: function click() {
	            this.popupWindow.close();
	          }
	        }
	      })]);
	    },
	    readAll: function readAll() {
	      var _this16 = this;
	      if (this.notification.lastId <= 0) {
	        return;
	      }
	      if (!this.isNeedToReadAll) {
	        return false;
	      }
	      this.$store.dispatch('notifications/readAll');
	      this.getRestClient().callMethod('im.notify.read', {
	        id: 0,
	        action: 'Y'
	      })["catch"](function (result) {
	        _this16.getInitialData();
	        console.error(result);
	      });
	    },
	    updateRecentList: function updateRecentList(counterValue) {
	      var setPreview = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var fields = {
	        counter: counterValue
	      };
	      if (setPreview) {
	        var latestNotification = this.getLatest();
	        fields.message = {
	          id: latestNotification.id,
	          text: latestNotification.text,
	          date: latestNotification.date
	        };
	      }
	      this.$store.dispatch('recent/update', {
	        id: 'notify',
	        fields: fields
	      });
	    },
	    onScrollButtonClick: function onScrollButtonClick(event) {
	      if (this.isLoadingNewPage || !this.initialDataReceived) {
	        return false;
	      }
	      var notificationIdToScroll = null;
	      if (this.firstUnreadNotificationBelowVisible !== null) {
	        notificationIdToScroll = this.firstUnreadNotificationBelowVisible;
	      } else if (!this.hasUnreadBelowVisible) {
	        notificationIdToScroll = this.firstUnreadNotification.id;
	      }
	      var firstUnreadNotificationNode = null;
	      if (notificationIdToScroll !== null) {
	        var selector = ".bx-im-notifications-item[data-id=\"".concat(notificationIdToScroll, "\"]");
	        firstUnreadNotificationNode = document.querySelector(selector);
	      }
	      if (firstUnreadNotificationNode) {
	        this.animatedScrollToPosition({
	          start: this.$refs['listNotifications'].scrollTop,
	          end: firstUnreadNotificationNode.offsetTop
	        });
	      } else {
	        var latestNotification = this.notification[this.notification.length - 1];
	        var _selector = ".bx-im-notifications-item[data-id=\"".concat(latestNotification.id, "\"]");
	        var latestNotificationNode = document.querySelector(_selector);
	        this.animatedScrollToPosition({
	          start: this.$refs['listNotifications'].scrollTop,
	          end: latestNotificationNode.offsetTop
	        });
	      }
	    },
	    animatedScrollToPosition: function animatedScrollToPosition() {
	      var _this17 = this;
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      if (this.animateScrollId) {
	        im_lib_animation.Animation.cancel(this.animateScrollId);
	        this.scrollAnimating = false;
	      }
	      if (typeof params === 'function') {
	        params = {
	          callback: params
	        };
	      }
	      var container = this.$refs.listNotifications;
	      var _params = params,
	        _params$start = _params.start,
	        start = _params$start === void 0 ? container.scrollTop : _params$start,
	        _params$end = _params.end,
	        end = _params$end === void 0 ? container.scrollHeight - container.clientHeight : _params$end,
	        _params$increment = _params.increment,
	        increment = _params$increment === void 0 ? 20 : _params$increment,
	        _callback = _params.callback,
	        _params$duration = _params.duration,
	        duration = _params$duration === void 0 ? 500 : _params$duration;
	      if (container && end - start > container.offsetHeight * 3) {
	        start = end - container.offsetHeight * 3;
	      }
	      this.scrollAnimating = true;
	      this.animateScrollId = im_lib_animation.Animation.start({
	        start: start,
	        end: end,
	        increment: increment,
	        duration: duration,
	        element: container,
	        elementProperty: 'scrollTop',
	        callback: function callback() {
	          _this17.animateScrollId = null;
	          _this17.scrollAnimating = false;
	          if (_callback && typeof _callback === 'function') {
	            _callback();
	          }
	        }
	      });
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div class=\"bx-messenger-next-notify\">\n\t\t\t<div class=\"bx-messenger-panel-next-wrapper\" :style=\"panelStyles\">\n\t\t\t\t<div class=\"bx-messenger-panel-next\">\n\t\t\t\t\t<div>\n\t\t\t\t\t\t<span \n\t\t\t\t\t\t\tclass=\"bx-messenger-panel-avatar bx-im-notifications-image-system bx-im-notifications-header-image\"\n\t\t\t\t\t\t></span>\n\t\t\t\t\t\t<span class=\"bx-messenger-panel-title bx-messenger-panel-title-middle\" style=\"flex-shrink: 0;\">\n\t\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_HEADER') }}\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-if=\"notification.length > 0\" class=\"bx-im-notifications-header-buttons\">\n\t\t\t\t\t\t<transition name=\"notifications-read-all-fade\">\n\t\t\t\t\t\t\t<div v-if=\"isNeedToReadAll\" class=\"bx-im-notifications-header-read-all\">\n\t\t\t\t\t\t\t\t<span\n\t\t\t\t\t\t\t\t\tclass='bx-messenger-panel-button bx-im-notifications-header-read-all-icon'\n\t\t\t\t\t\t\t\t\t@click=\"showConfirmPopupOnReadAll\"\n\t\t\t\t\t\t\t\t\t:title=\"$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_READ_ALL_BUTTON')\"\n\t\t\t\t\t\t\t\t></span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</transition>\n\t\t\t\t\t\t<div class=\"bx-im-notifications-header-filter\">\n\t\t\t\t\t\t\t<span\n\t\t\t\t\t\t\t\t:class=\"['bx-messenger-panel-button bx-messenger-panel-history bx-im-notifications-header-filter-icon', (showSearch? 'bx-im-notifications-header-filter-active': '')]\"\n\t\t\t\t\t\t\t\t@click=\"showSearch = !showSearch\"\n\t\t\t\t\t\t\t\t:title=\"$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_OPEN_BUTTON')\"\n\t\t\t\t\t\t\t></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"showSearch\" class=\"bx-im-notifications-header-filter-box\" :style=\"filterBoxStyles\">\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-xs ui-ctl-w25\">\n\t\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t\t<select class=\"ui-ctl-element\" v-model=\"searchType\">\n\t\t\t\t\t\t\t<option value=\"\">\n\t\t\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_PLACEHOLDER') }}\n\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t\t<template v-for=\"group in filterTypes\">\n\t\t\t\t\t\t\t\t<template v-if=\"group.LIST\">\n\t\t\t\t\t\t\t\t\t<optgroup :label=\"group.NAME\">\n\t\t\t\t\t\t\t\t\t\t<option v-for=\"option in group.LIST\" :value=\"option.ID\">\n\t\t\t\t\t\t\t\t\t\t\t{{ option.NAME }}\n\t\t\t\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t\t\t\t</optgroup>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-else>   \n\t\t\t\t\t\t\t\t\t<option :value=\"group.MODULE_ID\">\n\t\t\t\t\t\t\t\t\t\t{{ group.NAME }}\n\t\t\t\t\t\t\t\t\t</option>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\n\t\t\t\t\t\t</select>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-xs ui-ctl-w50\"> \n\t\t\t\t\t\t<button class=\"ui-ctl-after ui-ctl-icon-clear\" @click.prevent=\"searchQuery=''\"></button>\n\t\t\t\t\t\t<input\n\t\t\t\t\t\t\tautofocus\n\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\tclass=\"ui-ctl-element\" \n\t\t\t\t\t\t\tv-model=\"searchQuery\" \n\t\t\t\t\t\t\t:placeholder=\"$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TEXT_PLACEHOLDER')\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-after-icon ui-ctl-before-icon ui-ctl-xs ui-ctl-w25\">\n\t\t\t\t\t\t<div class=\"ui-ctl-before ui-ctl-icon-calendar\"></div>\n\t\t\t\t\t\t<input \n\t\t\t\t\t\t\ttype=\"text\" \n\t\t\t\t\t\t\tclass=\"ui-ctl-element ui-ctl-textbox\" \n\t\t\t\t\t\t\tv-model=\"searchDate\"\n\t\t\t\t\t\t\t@focus.prevent.stop=\"onDateFilterClick\"\n\t\t\t\t\t\t\t@click.prevent.stop=\"onDateFilterClick\"\n\t\t\t\t\t\t\t:placeholder=\"$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_DATE_PLACEHOLDER')\"\n\t\t\t\t\t\t\treadonly\n\t\t\t\t\t\t>\n\t\t\t\t\t\t<button class=\"ui-ctl-after ui-ctl-icon-clear\" @click.prevent=\"searchDate=''\"></button>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div \n\t\t\t\tv-if=\"showSearch && (searchQuery.length >= 3 || searchType !== '' || searchDate !== '')\" \n\t\t\t\tclass=\"bx-messenger-list-notifications-wrap\"\n\t\t\t>\n\t\t\t\t<NotificationSearchResult :searchQuery=\"searchQuery\" :searchType=\"searchType\" :searchDate=\"searchDate\"/>\n\t\t\t</div>\n\t\t\t<div v-else class=\"bx-messenger-list-notifications-wrap\">\n\t\t\t\t<div :class=\"[ darkTheme ? 'bx-messenger-dark' : '', 'bx-messenger-list-notifications']\" @scroll.passive=\"onScroll\" ref=\"listNotifications\">\n\t\t\t\t\t<notification-item\n\t\t\t\t\t\tv-for=\"listItem in visibleNotifications\"\n\t\t\t\t\t\t:key=\"listItem.id\"\n\t\t\t\t\t\t:data-id=\"listItem.id\"\n\t\t\t\t\t\t:rawListItem=\"listItem\"\n\t\t\t\t\t\t@dblclick=\"onDoubleClick\"\n\t\t\t\t\t\t@buttonsClick=\"onButtonsClick\"\n\t\t\t\t\t\t@deleteClick=\"onDeleteClick\"\n\t\t\t\t\t\t@contentClick=\"onContentClick\"\n\t\t\t\t\t\tv-bx-im-directive-notifications-observer=\"\n\t\t\t\t\t\t\tlistItem.sectionCode !== NotificationTypesCodes.placeholder\n\t\t\t\t\t\t\t? ObserverType.read \n\t\t\t\t\t\t\t: ObserverType.none\n\t\t\t\t\t\t\"\n\t\t\t\t\t/>\n\t\t\t\t\t<div\n\t\t\t\t\t\tv-if=\"notification.length <= 0\"\n\t\t\t\t\t\tstyle=\"padding-top: 210px; margin-bottom: 20px;\"\n\t\t\t\t\t\tclass=\"bx-messenger-box-empty bx-notifier-content-empty\"\n\t\t\t\t\t>\n\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_NO_ITEMS') }}\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<!-- Scroll button -->\n\t\t\t\t<transition name=\"bx-im-notifications-scroll-button\">\n\t\t\t\t\t<div v-show=\"showScrollButton\" class=\"bx-im-notifications-scroll-button-box\" @click=\"onScrollButtonClick\">\n\t\t\t\t\t\t<div class=\"bx-im-notifications-scroll-button\">\n\t\t\t\t\t\t\t<div class=\"bx-im-notifications-scroll-button-counter\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-notifications-scroll-button-counter-digit\">{{ unreadCounter }}</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div :class=\"arrowButtonClass\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</transition>\n\t\t\t\t\n\t\t\t\t<mounting-portal :mount-to=\"popupIdSelector\" append v-if=\"popupInstance\">\n\t\t\t\t\t<popup :type=\"contentPopupType\" :value=\"contentPopupValue\" :popupInstance=\"popupInstance\"/>\n\t\t\t\t</mounting-portal>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX.Messenger.Lib,BX,BX,window,window,BX,BX,BX,BX.Messenger.Lib,BX.Vue,BX.Messenger.View,BX.Main,BX.Messenger.Lib,BX.Messenger.Const,BX.Messenger.Lib,BX.Event));
//# sourceMappingURL=notifications.bundle.js.map
