/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core,im_v2_const) {
	'use strict';

	var _prepareUsersForStore = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareUsersForStore");
	class UserManager {
	  constructor() {
	    Object.defineProperty(this, _prepareUsersForStore, {
	      value: _prepareUsersForStore2
	    });
	    this.store = null;
	    this.store = im_v2_application_core.Core.getStore();
	  }
	  static getDialogForUser(user) {
	    return {
	      dialogId: UserManager.getUserId(user),
	      avatar: user.avatar,
	      color: user.color,
	      name: user.name,
	      type: im_v2_const.ChatType.user,
	      role: im_v2_const.UserRole.member
	    };
	  }
	  static getUserId(user) {
	    var _ref, _user$id;
	    return (_ref = (_user$id = user.id) != null ? _user$id : user.networkId) != null ? _ref : 0;
	  }
	  setUsersToModel(rawUsers) {
	    const {
	      users,
	      chats
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareUsersForStore)[_prepareUsersForStore](rawUsers);
	    const usersPromise = this.store.dispatch('users/set', users);
	    const chatsPromise = this.store.dispatch('chats/set', chats);
	    return Promise.all([usersPromise, chatsPromise]);
	  }
	  addUsersToModel(rawUsers) {
	    const {
	      users,
	      chats
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareUsersForStore)[_prepareUsersForStore](rawUsers);
	    const usersPromise = this.store.dispatch('users/add', users);
	    const chatsPromise = this.store.dispatch('chats/add', chats);
	    return Promise.all([usersPromise, chatsPromise]);
	  }
	}
	function _prepareUsersForStore2(rawUsers) {
	  let users = rawUsers;
	  if (main_core.Type.isPlainObject(users)) {
	    users = [users];
	  }
	  const chats = [];
	  users.forEach(user => {
	    chats.push(UserManager.getDialogForUser(user));
	  });
	  return {
	    users,
	    chats
	  };
	}

	exports.UserManager = UserManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=user.bundle.js.map
