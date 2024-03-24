import { BitrixVue } from 'ui.vue3';

import { Utils } from 'im.v2.lib.utils';

import { CallInviteMessage } from '../../call-invite/src/call-invite';

// @vue/component
export const ZoomInviteMessage = BitrixVue.cloneComponent(CallInviteMessage, {
	name: 'ZoomInviteMessage',
	computed:
	{
		inviteTitle(): string
		{
			return this.loc('IM_MESSENGER_ZOOM_INVITE_TITLE');
		},
		descriptionTitle(): string
		{
			return this.loc('IM_MESSENGER_ZOOM_INVITE_DESCRIPTION');
		},
	},
	methods:
	{
		onCallButtonClick()
		{
			Utils.browser.openLink(this.componentParams.link);
		},
	},
});
