export const DialogUtil = {
	isDialogId(dialogId)
	{
		return /(chat\d+)|\d+/i.test(dialogId);
	}
};
