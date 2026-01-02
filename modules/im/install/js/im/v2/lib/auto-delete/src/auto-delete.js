import { Loc } from 'main.core';

import { PermissionManager } from 'im.v2.lib.permission';
import { ActionByRole, ActionByUserType, AutoDeleteDelay } from 'im.v2.const';

export const AutoDeleteManager = {
	getStatusText(delayInHours: number): string
	{
		const delayMap = {
			[AutoDeleteDelay.Off]: 'IM_LIB_AUTO_DELETE_STATUS_OFF',
			[AutoDeleteDelay.Hour]: 'IM_LIB_AUTO_DELETE_STATUS_1H',
			[AutoDeleteDelay.Day]: 'IM_LIB_AUTO_DELETE_STATUS_1D',
			[AutoDeleteDelay.Week]: 'IM_LIB_AUTO_DELETE_STATUS_1W',
			[AutoDeleteDelay.Month]: 'IM_LIB_AUTO_DELETE_STATUS_1M',
		};

		const code = delayMap[delayInHours] || 'IM_LIB_AUTO_DELETE_STATUS_CUSTOM';

		return Loc.getMessage(code, { '#NUMBER#': delayInHours });
	},
	isAutoDeleteAllowed(dialogId: string): boolean
	{
		const canUserModify = PermissionManager.getInstance().canPerformActionByUserType(
			ActionByUserType.changeMessagesAutoDeleteDelay,
		);

		const hasRolePermission = PermissionManager.getInstance().canPerformActionByRole(
			ActionByRole.changeMessagesAutoDeleteDelay,
			dialogId,
		);

		return canUserModify && hasRolePermission;
	},
};
