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
	      dialogId: user.id,
	      avatar: user.avatar,
	      color: user.color,
	      name: user.name,
	      type: im_v2_const.DialogType.user
	    };
	  }
	  setUsersToModel(rawUsers) {
	    const {
	      users,
	      dialogues
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareUsersForStore)[_prepareUsersForStore](rawUsers);
	    const usersPromise = this.store.dispatch('users/set', users);
	    const dialoguesPromise = this.store.dispatch('dialogues/set', dialogues);
	    return Promise.all([usersPromise, dialoguesPromise]);
	  }
	  addUsersToModel(rawUsers) {
	    const {
	      users,
	      dialogues
	    } = babelHelpers.classPrivateFieldLooseBase(this, _prepareUsersForStore)[_prepareUsersForStore](rawUsers);
	    const usersPromise = this.store.dispatch('users/add', users);
	    const dialoguesPromise = this.store.dispatch('dialogues/add', dialogues);
	    return Promise.all([usersPromise, dialoguesPromise]);
	  }
	}
	function _prepareUsersForStore2(users) {
	  if (main_core.Type.isPlainObject(users)) {
	    users = [users];
	  }
	  const dialogues = [];
	  users.forEach(user => {
	    dialogues.push(UserManager.getDialogForUser(user));
	  });
	  return {
	    users,
	    dialogues
	  };
	}

	exports.UserManager = UserManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=user.bundle.js.map
