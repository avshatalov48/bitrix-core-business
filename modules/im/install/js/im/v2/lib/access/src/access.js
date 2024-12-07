import { AccessService, type AccessCheckResult } from './classes/access-service';
import { HistoryLimitPopup } from './classes/history-limit-popup';

export { AccessErrorCode } from './classes/access-service';

export const AccessManager = {
	checkMessageAccess(messageId: number): Promise<AccessCheckResult>
	{
		return AccessService.checkMessageAccess(messageId);
	},

	// save it for later
	showHistoryLimitPopup(): void
	{
		const limitPopup = new HistoryLimitPopup();
		limitPopup.show();
	},
};
