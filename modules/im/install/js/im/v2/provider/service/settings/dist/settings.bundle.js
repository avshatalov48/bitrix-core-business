/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_const,im_v2_lib_logger,im_v2_lib_rest) {
	'use strict';

	class SettingsService {
	  changeSetting(settingName, value) {
	    im_v2_lib_logger.Logger.warn('SettingsService: changeSetting', settingName, value);
	    void im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	      [settingName]: value
	    });
	    const payload = {
	      data: {
	        userId: im_v2_application_core.Core.getUserId(),
	        name: settingName,
	        value
	      }
	    };
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2SettingsGeneralUpdate, payload).catch(([error]) => {
	      console.error('SettingsService: changeSetting error', error);
	    });
	  }
	  changeStatus(status) {
	    if (!im_v2_const.UserStatus[status]) {
	      return;
	    }
	    im_v2_lib_logger.Logger.warn(`SettingsService: changeStatus to ${status}`);
	    void im_v2_application_core.Core.getStore().dispatch('users/setStatus', {
	      status
	    });
	    void im_v2_application_core.Core.getStore().dispatch('application/settings/set', {
	      status
	    });
	    const payload = {
	      STATUS: status
	    };
	    im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imUserStatusSet, payload).catch(result => {
	      console.error('SettingsService: changeStatus error', result.error());
	    });
	  }
	}

	exports.SettingsService = SettingsService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=settings.bundle.js.map
