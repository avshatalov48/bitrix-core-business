/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_sidepanel,im_v2_application_core,main_core,im_v2_lib_notifier) {
	'use strict';

	const RESEND_ACTION = 'intranet.controller.invite.reinvite';
	const CANCEL_ACTION = 'intranet.controller.invite.deleteinvitation';
	const InviteService = {
	  resendInvite(userId) {
	    const data = {
	      params: {
	        userId
	      }
	    };
	    main_core.ajax.runAction(RESEND_ACTION, {
	      data
	    }).then(() => {
	      im_v2_lib_notifier.Notifier.invite.onResendComplete();
	    }).catch(error => {
	      console.error('InviteService: resendInvite error', error);
	    });
	  },
	  cancelInvite(userId) {
	    const data = {
	      params: {
	        userId
	      }
	    };
	    main_core.ajax.runAction(CANCEL_ACTION, {
	      data
	    }).then(() => {
	      im_v2_lib_notifier.Notifier.invite.onCancelComplete();
	    }).catch(error => {
	      console.error('InviteService: cancelInvite error', error);
	    });
	  }
	};

	const InviteManager = {
	  resendInvite(userId) {
	    InviteService.resendInvite(userId);
	  },
	  cancelInvite(userId) {
	    InviteService.cancelInvite(userId);
	  },
	  openInviteSlider(analyticsContext = '') {
	    const sidePanel = main_sidepanel.SidePanel.Instance;
	    const sliderOptions = {
	      cacheable: false,
	      allowChangeHistory: false,
	      width: 1100
	    };
	    sidePanel.open(getInviteSliderLink(analyticsContext), sliderOptions);
	  }
	};
	const getInviteSliderLink = analyticsContext => {
	  const AJAX_PATH = '/bitrix/services/main/ajax.php';
	  const COMPONENT_NAME = 'bitrix:intranet.invitation';
	  const ACTION_NAME = 'getSliderContent';
	  const analytics = {};
	  if (analyticsContext) {
	    analytics['analyticsLabel[source]'] = analyticsContext;
	  }
	  const params = new URLSearchParams({
	    action: ACTION_NAME,
	    site_id: im_v2_application_core.Core.getSiteId(),
	    c: COMPONENT_NAME,
	    mode: 'ajax',
	    ...analytics
	  });
	  return `${AJAX_PATH}?${params.toString()}`;
	};

	exports.InviteManager = InviteManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.SidePanel,BX.Messenger.v2.Application,BX,BX.Messenger.v2.Lib));
//# sourceMappingURL=invite.bundle.js.map
