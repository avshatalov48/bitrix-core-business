/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core,im_v2_const) {
	'use strict';

	function getChatRoleForUser(rawChatConfig) {
	  const chatConfig = prepareChatConfig(rawChatConfig);
	  const userId = im_v2_application_core.Core.getUserId();
	  if (chatConfig.ownerId === userId) {
	    return im_v2_const.UserRole.owner;
	  }
	  if (chatConfig.managers.includes(userId)) {
	    return im_v2_const.UserRole.manager;
	  }
	  return im_v2_const.UserRole.member;
	}
	function prepareChatConfig(rawChatConfig) {
	  const result = {
	    ownerId: 0,
	    managers: []
	  };
	  if (main_core.Type.isNumber(rawChatConfig.ownerId)) {
	    result.ownerId = rawChatConfig.ownerId;
	  }
	  if (main_core.Type.isNumber(rawChatConfig.owner)) {
	    result.ownerId = rawChatConfig.owner;
	  }
	  if (main_core.Type.isArray(rawChatConfig.managers)) {
	    result.managers = rawChatConfig.managers;
	  }
	  if (main_core.Type.isArray(rawChatConfig.manager_list)) {
	    result.managers = rawChatConfig.manager_list;
	  }
	  return result;
	}

	exports.getChatRoleForUser = getChatRoleForUser;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=role.bundle.js.map
