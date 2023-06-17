import {ajax as Ajax, Loc} from 'main.core';

const resendAction = 'intranet.controller.invite.reinvite';
const cancelAction = 'intranet.controller.invite.deleteinvitation';

export const InviteManager = {
	resendInvite(userId: number)
	{
		const data = {
			params: {userId}
		};
		Ajax.runAction(resendAction, {data}).then(() => {
			this.showNotification(Loc.getMessage('IM_LIB_MENU_INVITE_RESEND_DONE'), 2000);
		}, (error) => {
			this.handleActionError(error);
		});
	},

	cancelInvite(userId: number)
	{
		const data = {
			params: {userId}
		};
		Ajax.runAction(cancelAction, {data}).then(() => {
			this.showNotification(Loc.getMessage('IM_LIB_MENU_INVITE_CANCEL_DONE'), 2000);
		}, (error) => {
			this.handleActionError(error);
		});
	},

	showNotification(text: string, autoHideDelay: number = 4000)
	{
		BX.UI.Notification.Center.notify({
			content: text,
			autoHideDelay
		});
	},

	handleActionError(error: Object)
	{
		if (error.status === 'error' && error.errors.length > 0)
		{
			const errorContent = error.errors.map((element) => {
				return element.message;
			}).join('. ');
			this.showNotification(errorContent);

			return true;
		}

		this.showNotification(Loc.getMessage('IM_LIST_RECENT_CONNECT_ERROR'));
	}
};