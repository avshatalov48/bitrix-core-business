/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,ui_vue,im_const) {
	'use strict';

	var Chat = {
	  props: ['value', 'popupInstance'],
	  data: function data() {
	    return {
	      chat: {},
	      hasError: false,
	      requestFinished: false
	    };
	  },
	  created: function created() {
	    var chatData = this.getChat(this.value);
	    if (chatData) {
	      this.chat = chatData;
	      this.requestFinished = true;
	    } else {
	      this.requestChatData(this.value);
	    }
	  },
	  mounted: function mounted() {
	    this.popupInstance.show();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.popupInstance.destroy();
	  },
	  methods: {
	    getChat: function getChat(dialogId) {
	      return this.$store.getters['dialogues/get'](dialogId);
	    },
	    requestChatData: function requestChatData(dialogId) {
	      var _this = this;
	      this.$Bitrix.RestClient.get().callMethod(im_const.RestMethod.imChatGet, {
	        dialog_id: dialogId
	      }).then(function (response) {
	        _this.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imChatGet, response);
	        _this.chat = _this.getChat(_this.value);
	        _this.requestFinished = true;
	      })["catch"](function (error) {
	        _this.hasError = true;
	        console.error(error);
	        _this.requestFinished = true;
	      });
	    },
	    //events
	    onOpenChat: function onOpenChat(event) {
	      this.popupInstance.destroy();
	      BXIM.openMessenger(this.value);
	    },
	    onOpenHistory: function onOpenHistory(event) {
	      this.popupInstance.destroy();
	      BXIM.openHistory(this.value);
	    }
	  },
	  computed: {
	    avatarStyles: function avatarStyles() {
	      var styles = {};
	      if (this.emptyAvatar) {
	        styles.backgroundColor = this.chat.color;
	      }
	      return styles;
	    },
	    chatAvatar: function chatAvatar() {
	      if (this.emptyAvatar) {
	        return '/bitrix/js/im/images/blank.gif';
	      } else {
	        return this.chat.avatar;
	      }
	    },
	    emptyAvatar: function emptyAvatar() {
	      return this.chat.avatar === '' || this.chat.avatar.indexOf('/bitrix/js/im/images/blank.gif') >= 0;
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div class=\"bx-messenger-external-data\" style=\"width: 272px; max-width: 272px; height: 100px;\">\n\t\t\t<div v-if=\"requestFinished && !hasError\">\n\t\t\t\t<div class=\"bx-messenger-external-avatar\">\n\t\t\t\t\t<div class=\"bx-messenger-panel-avatar bx-messenger-panel-avatar-chat\">\n\t\t\t\t\t\t<img\n\t\t\t\t\t\t\t:src=\"chatAvatar\"\n\t\t\t\t\t\t\t:alt=\"chat.name\"\n\t\t\t\t\t\t\t:style=\"avatarStyles\"\n\t\t\t\t\t\t\t:class=\"[emptyAvatar ? 'bx-messenger-panel-avatar-img-default' : '', 'bx-messenger-panel-avatar-img']\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</div>\n\t\t\t\t\t<span v-if=\"chat.extranet\" class=\"bx-messenger-panel-title\"><div class=\"bx-messenger-user-extranet\">{{ chat.name }}</div></span>\n\t\t\t\t\t<span v-else class=\"bx-messenger-panel-title\">{{ chat.name }}</span>\n\t\t\t\t\t<span class=\"bx-messenger-panel-desc\">{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_GROUP_CHAT') }}</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-messenger-external-data-buttons\">\n\t\t\t\t<span class=\"bx-notifier-item-button bx-notifier-item-button-white\" @click=\"onOpenChat\">\n\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_OPEN_CHAT') }}\n\t\t\t\t</span>\n\t\t\t\t\t<span class=\"bx-notifier-item-button bx-notifier-item-button-white\" @click=\"onOpenHistory\">\n\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_OPEN_HISTORY') }}\n\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<span v-else-if=\"!requestFinished && !hasError\" class=\"bx-messenger-content-load-img\"></span>\n\t\t\t<div v-else-if=\"requestFinished && hasError\">\n\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_NO_ACCESS') }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var User = {
	  props: ['value', 'popupInstance'],
	  data: function data() {
	    return {
	      user: {},
	      hasError: false,
	      requestFinished: false
	    };
	  },
	  created: function created() {
	    var userData = this.getUser(this.value);
	    if (userData) {
	      this.user = userData;
	      this.requestFinished = true;
	    } else {
	      this.requestUserData(this.value);
	    }
	  },
	  mounted: function mounted() {
	    this.popupInstance.show();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.popupInstance.destroy();
	  },
	  methods: {
	    getUser: function getUser(userId) {
	      return this.$store.getters['users/get'](userId);
	    },
	    requestUserData: function requestUserData(userId) {
	      var _this = this;
	      this.$Bitrix.RestClient.get().callMethod(im_const.RestMethod.imUserGet, {
	        ID: userId
	      }).then(function (response) {
	        _this.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imUserGet, response);
	        _this.user = _this.getUser(_this.value);
	        _this.requestFinished = true;
	      })["catch"](function (error) {
	        _this.hasError = true;
	        console.error(error);
	        _this.requestFinished = true;
	      });
	    },
	    //events
	    onOpenChat: function onOpenChat(event) {
	      this.popupInstance.destroy();
	      BXIM.openMessenger(this.value);
	    },
	    onOpenHistory: function onOpenHistory(event) {
	      this.popupInstance.destroy();
	      BXIM.openHistory(this.value);
	    }
	  },
	  computed: {
	    avatarStyles: function avatarStyles() {
	      var styles = {};
	      if (this.emptyAvatar) {
	        styles.backgroundColor = this.chat.color;
	      }
	      return styles;
	    },
	    userAvatar: function userAvatar() {
	      if (this.emptyAvatar) {
	        return '/bitrix/js/im/images/blank.gif';
	      } else {
	        return this.user.avatar;
	      }
	    },
	    emptyAvatar: function emptyAvatar() {
	      return this.user.avatar === '' || this.user.avatar.indexOf('/bitrix/js/im/images/blank.gif') >= 0;
	    },
	    botStyles: function botStyles() {
	      //todo handle all the bot types im/install/js/im/im.js:5887
	      return 'bx-messenger-user-bot';
	    },
	    userStatusText: function userStatusText() {
	      //todo remove old code
	      return BX.MessengerCommon.getUserStatus(this.user.id, false).statusText;
	    },
	    userStatusClass: function userStatusClass() {
	      //todo remove old code
	      return 'bx-messenger-panel-avatar-status-' + BX.MessengerCommon.getUserStatus(this.user.id, true);
	    },
	    userPosition: function userPosition() {
	      //todo remove old code
	      return BX.MessengerCommon.getUserPosition(this.user.id);
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div class=\"bx-messenger-external-data\" style=\"width: 272px; max-width: 272px; height: 100px;\">\n\t\t\t<div v-if=\"requestFinished && !hasError\">\n\t\t\t\t<div class=\"bx-messenger-external-avatar\">\n\t\t\t\t\t<div :class=\"[userStatusClass, 'bx-messenger-panel-avatar']\">\n\t\t\t\t\t\t<img\n\t\t\t\t\t\t\t:src=\"userAvatar\"\n\t\t\t\t\t\t\t:style=\"avatarStyles\"\n\t\t\t\t\t\t\t:class=\"[emptyAvatar ? 'bx-messenger-panel-avatar-img-default' : '', 'bx-messenger-panel-avatar-img']\"\n\t\t\t\t\t\t\t:alt=\"user.name\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t\t<span :title=\"userStatusText\" class=\"bx-messenger-panel-avatar-status\"></span>\n\t\t\t\t\t</div>\n\t\n\t\t\t\t\t<span v-if=\"user.extranet\" class=\"bx-messenger-panel-title\"><div class=\"bx-messenger-user-extranet\">{{ user.name }}</div></span>\n\t\t\t\t\t<span v-else-if=\"user.bot\" class=\"bx-messenger-panel-title\"><div :class=\"botStyles\">{{ user.name }}</div></span>\n\t\t\t\t\t<span v-else class=\"bx-messenger-panel-title\">{{ user.name }}</span>\n\t\n\t\t\t\t\t<span class=\"bx-messenger-panel-desc\">{{ userPosition }}</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-messenger-external-data-buttons\">\n\t\t\t\t\t<span class=\"bx-notifier-item-button bx-notifier-item-button-white\" @click=\"onOpenChat\">\n\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_USER_OPEN_CHAT') }}\n\t\t\t\t\t</span>\n\t\t\t\t\t<span class=\"bx-notifier-item-button bx-notifier-item-button-white\" @click=\"onOpenHistory\">\n\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_OPEN_HISTORY') }}\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<span v-else-if=\"!requestFinished && !hasError\" class=\"bx-messenger-content-load-img\"></span>\n\t\t\t<div v-else-if=\"requestFinished && hasError\">\n\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_NO_ACCESS') }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Users = {
	  props: ['value', 'popupInstance'],
	  data: function data() {
	    return {
	      users: {},
	      hasError: false,
	      requestFinished: false
	    };
	  },
	  created: function created() {
	    var needRequest = this.isNeedUserRequest(this.value);
	    if (needRequest) {
	      this.requestUserData(this.value);
	    } else
	      //!needRequest
	      {
	        this.users = this.getUsersForPopup();
	        this.requestFinished = true;
	      }
	  },
	  mounted: function mounted() {
	    this.popupInstance.show();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.popupInstance.destroy();
	  },
	  computed: {
	    popupHeight: function popupHeight() {
	      var height = this.value.length * 30;
	      if (height > 150) {
	        height = 150;
	      }
	      return height + 'px';
	    }
	  },
	  methods: {
	    getUser: function getUser(userId) {
	      return this.$store.getters['users/get'](userId);
	    },
	    getUsersForPopup: function getUsersForPopup() {
	      var _this = this;
	      return this.value.map(function (userId) {
	        return _this.getUser(userId);
	      });
	    },
	    getUserAvatar: function getUserAvatar(user) {
	      if (this.isEmptyAvatar(user)) {
	        return '/bitrix/js/im/images/blank.gif';
	      } else {
	        return user.avatar;
	      }
	    },
	    isEmptyAvatar: function isEmptyAvatar(user) {
	      return user.avatar === '' || user.avatar.indexOf('/bitrix/js/im/images/blank.gif') >= 0;
	    },
	    getAvatarStyles: function getAvatarStyles(user) {
	      var styles = {};
	      if (this.isEmptyAvatar(user)) {
	        styles.backgroundColor = user.color;
	      }
	      return styles;
	    },
	    getUserStatusClass: function getUserStatusClass(user) {
	      return "bx-notifier-popup-avatar-status-".concat(user.status);
	    },
	    isNeedUserRequest: function isNeedUserRequest(users) {
	      for (var i = 0; i < users.length; i++) {
	        if (!this.getUser(users[i])) {
	          return true;
	        }
	      }
	      return false;
	    },
	    requestUserData: function requestUserData(userIds) {
	      var _this2 = this;
	      this.$Bitrix.RestClient.get().callMethod(im_const.RestMethod.imUserListGet, {
	        ID: userIds
	      }).then(function (response) {
	        _this2.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imUserListGet, response);
	        _this2.users = _this2.getUsersForPopup();
	        _this2.requestFinished = true;
	      })["catch"](function (error) {
	        _this2.hasError = true;
	        console.error(error);
	        _this2.requestFinished = true;
	      });
	    },
	    onUserClick: function onUserClick(userId) {
	      this.popupInstance.destroy();
	      BXIM.openMessenger(userId);
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div\n\t\t\tclass=\"bx-im-vue-popup-container\" \n\t\t\t:style=\"{height: popupHeight, width: '180px', display: 'flex', alignItems: 'center', justifyContent: 'center'}\"\n\t\t>\n\t\t\t<span v-if=\"requestFinished && !hasError\" class=\"bx-notifier-item-help-popup\">\n\t\t\t\t<a \n\t\t\t\t\tv-for=\"user in users\"\n\t\t\t\t\tclass=\"bx-notifier-item-help-popup-img\"\n\t\t\t\t\t@click.prevent=\"onUserClick(user.id)\"\n\t\t\t\t>\n\t\t\t\t\t<span :class=\"[getUserStatusClass(user), 'bx-notifier-popup-avatar']\">\n\t\t\t\t\t\t<img \n\t\t\t\t\t\t\t:src=\"getUserAvatar(user)\"\n\t\t\t\t\t\t\t:class=\"['bx-notifier-popup-avatar-img', isEmptyAvatar(user) ? 'bx-notifier-popup-avatar-img-default' : '']\"\n\t\t\t\t\t\t\t:style=\"getAvatarStyles(user)\"\n\t\t\t\t\t\t\t:alt=\"user.name\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</span>\n\t\t\t\t\t<span \n\t\t\t\t\t\t:class=\"['bx-notifier-item-help-popup-name', user.extranet ? 'bx-notifier-popup-avatar-extranet' : '']\"\n\t\t\t\t\t>\n\t\t\t\t\t\t{{ user.name }}\n\t\t\t\t\t</span>\n\t\t\t\t</a>\n\t\t\t</span>\n\t\t\t<span v-else-if=\"!requestFinished && !hasError\" class=\"bx-messenger-content-load-img\"></span>\n\t\t\t<div v-else-if=\"requestFinished && hasError\">\n\t\t\t\t{{ $Bitrix.Loc.getMessage('IM_VIEW_POPUP_CONTENT_NO_ACCESS') }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Popup = {
	  props: ['type', 'value', 'popupInstance'],
	  components: {
	    Chat: Chat,
	    User: User,
	    Users: Users
	  },
	  //language=Vue
	  template: "\n\t\t<component :is=\"type\" :value=\"value\" :popupInstance=\"popupInstance\"/>\n\t"
	};

	exports.Popup = Popup;

}((this.BX.Messenger.View = this.BX.Messenger.View || {}),BX,BX.Messenger.Const));
//# sourceMappingURL=popup.bundle.js.map
