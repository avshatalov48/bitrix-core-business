import { Loc, Type } from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';

export function showCancelDialog(onConfirm: Function, onCancel: ?Function): void
{
	const messageBox = MessageBox.confirm(
		Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_DESCRIPTION'),
		Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_TITLE'),
		onConfirm,
		Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_CONFIRM'),
		Type.isFunction(onCancel) ? onCancel : () => true,
		Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_CANCEL'),
	);

	if (Type.isFunction(onCancel))
	{
		const popup = messageBox.getPopupWindow();
		popup.subscribe('onClose', onCancel);
	}
}
