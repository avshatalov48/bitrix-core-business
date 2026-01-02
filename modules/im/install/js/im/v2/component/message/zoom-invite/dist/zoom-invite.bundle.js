/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_vue3,im_v2_lib_utils,im_v2_component_message_callInvite) {
	'use strict';

	// @vue/component
	const ZoomInviteMessage = ui_vue3.BitrixVue.cloneComponent(im_v2_component_message_callInvite.CallInviteMessage, {
	  name: 'ZoomInviteMessage',
	  computed: {
	    inviteTitle() {
	      return this.loc('IM_MESSENGER_ZOOM_INVITE_TITLE');
	    },
	    descriptionTitle() {
	      return this.loc('IM_MESSENGER_ZOOM_INVITE_DESCRIPTION');
	    }
	  },
	  methods: {
	    onCallButtonClick() {
	      im_v2_lib_utils.Utils.browser.openLink(this.componentParams.link);
	    }
	  }
	});

	exports.ZoomInviteMessage = ZoomInviteMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Vue3,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=zoom-invite.bundle.js.map
