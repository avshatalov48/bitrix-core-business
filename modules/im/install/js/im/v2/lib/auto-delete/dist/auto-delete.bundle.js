/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_lib_permission,im_v2_const) {
	'use strict';

	const AutoDeleteManager = {
	  getStatusText(delayInHours) {
	    const delayMap = {
	      [im_v2_const.AutoDeleteDelay.Off]: 'IM_LIB_AUTO_DELETE_STATUS_OFF',
	      [im_v2_const.AutoDeleteDelay.Hour]: 'IM_LIB_AUTO_DELETE_STATUS_1H',
	      [im_v2_const.AutoDeleteDelay.Day]: 'IM_LIB_AUTO_DELETE_STATUS_1D',
	      [im_v2_const.AutoDeleteDelay.Week]: 'IM_LIB_AUTO_DELETE_STATUS_1W',
	      [im_v2_const.AutoDeleteDelay.Month]: 'IM_LIB_AUTO_DELETE_STATUS_1M'
	    };
	    const code = delayMap[delayInHours] || 'IM_LIB_AUTO_DELETE_STATUS_CUSTOM';
	    return main_core.Loc.getMessage(code, {
	      '#NUMBER#': delayInHours
	    });
	  },
	  isAutoDeleteAllowed(dialogId) {
	    const canUserModify = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByUserType(im_v2_const.ActionByUserType.changeMessagesAutoDeleteDelay);
	    const hasRolePermission = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.changeMessagesAutoDeleteDelay, dialogId);
	    return canUserModify && hasRolePermission;
	  }
	};

	exports.AutoDeleteManager = AutoDeleteManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=auto-delete.bundle.js.map
