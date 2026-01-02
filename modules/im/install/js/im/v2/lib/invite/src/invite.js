import { SidePanel, type SliderManager, type SliderOptions } from 'main.sidepanel';

import { Core } from 'im.v2.application.core';

import { InviteService } from './classes/invite-service';

export const InviteManager = {
	resendInvite(userId: number): void
	{
		InviteService.resendInvite(userId);
	},

	cancelInvite(userId: number): void
	{
		InviteService.cancelInvite(userId);
	},

	openInviteSlider(analyticsContext: string = ''): void
	{
		const sidePanel: SliderManager = SidePanel.Instance;
		const sliderOptions: SliderOptions = {
			cacheable: false,
			allowChangeHistory: false,
			width: 1100,
		};

		sidePanel.open(getInviteSliderLink(analyticsContext), sliderOptions);
	},
};

const getInviteSliderLink = (analyticsContext: string): string => {
	const AJAX_PATH = '/bitrix/services/main/ajax.php';
	const COMPONENT_NAME = 'bitrix:intranet.invitation';
	const ACTION_NAME = 'getSliderContent';
	const analytics = {};

	if (analyticsContext)
	{
		analytics['analyticsLabel[source]'] = analyticsContext;
	}

	const params = new URLSearchParams({
		action: ACTION_NAME,
		site_id: Core.getSiteId(),
		c: COMPONENT_NAME,
		mode: 'ajax',
		...analytics,
	});

	return `${AJAX_PATH}?${params.toString()}`;
};
