import { ajax as Ajax } from 'main.core';

import { Notifier } from 'im.v2.lib.notifier';

const resendAction = 'intranet.controller.invite.reinvite';
const cancelAction = 'intranet.controller.invite.deleteinvitation';

export const InviteManager = {
	resendInvite(userId: number): void
	{
		const data = {
			params: { userId },
		};

		Ajax.runAction(resendAction, { data })
			.then(() => {
				Notifier.invite.onResendComplete();
			})
			.catch((error) => {
				console.error('InviteManager: resendInvite error', error);
			});
	},

	cancelInvite(userId: number)
	{
		const data = {
			params: { userId },
		};

		Ajax.runAction(cancelAction, { data })
			.then(() => {
				Notifier.invite.onCancelComplete();
			})
			.catch((error) => {
				console.error('InviteManager: cancelInvite error', error);
			});
	},
};
