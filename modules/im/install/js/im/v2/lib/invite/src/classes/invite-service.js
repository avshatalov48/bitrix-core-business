import { ajax as Ajax } from 'main.core';

import { Notifier } from 'im.v2.lib.notifier';

const RESEND_ACTION = 'intranet.controller.invite.reinvite';
const CANCEL_ACTION = 'intranet.controller.invite.deleteinvitation';

export const InviteService = {
	resendInvite(userId: number): void
	{
		const data = {
			params: { userId },
		};

		Ajax.runAction(RESEND_ACTION, { data })
			.then(() => {
				Notifier.invite.onResendComplete();
			})
			.catch((error) => {
				console.error('InviteService: resendInvite error', error);
			});
	},

	cancelInvite(userId: number): void
	{
		const data = {
			params: { userId },
		};

		Ajax.runAction(CANCEL_ACTION, { data })
			.then(() => {
				Notifier.invite.onCancelComplete();
			})
			.catch((error) => {
				console.error('InviteService: cancelInvite error', error);
			});
	},
};
