export const DialogUtil = {
	isDialogId(dialogId)
	{
		return /^(chat\d+)$|^\d+$/i.test(dialogId);
	},

	isExternalId(dialogId: string): boolean
	{
		return this.isGroupExternalId(dialogId) || this.isCrmExternalId(dialogId);
	},

	isGroupExternalId(dialogId: string): boolean
	{
		const GROUP_PREFIX = 'sg';

		return dialogId.startsWith(GROUP_PREFIX);
	},

	isCrmExternalId(dialogId: string): boolean
	{
		const CRM_PREFIX = 'crm|';

		return dialogId.startsWith(CRM_PREFIX);
	},

	isLinesExternalId(dialogId): boolean
	{
		const LINES_PREFIX = 'imol|';

		return dialogId.toString().startsWith(LINES_PREFIX) && !this.isLinesHistoryId(dialogId);
	},

	isLinesHistoryId(dialogId): boolean
	{
		return /^imol\|\d+$/.test(dialogId);
	},
};
