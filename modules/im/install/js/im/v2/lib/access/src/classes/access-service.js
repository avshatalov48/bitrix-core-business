import { RestMethod } from 'im.v2.const';
import { runAction, type RunActionError } from 'im.v2.lib.rest';

export const AccessErrorCode = {
	accessDenied: 'ACCESS_DENIED',
	chatNotFound: 'CHAT_NOT_FOUND',
	messageNotFound: 'MESSAGE_NOT_FOUND',
	messageAccessDenied: 'MESSAGE_ACCESS_DENIED',
	messageAccessDeniedByTariff: 'MESSAGE_ACCESS_DENIED_BY_TARIFF',
};

export type AccessCheckResult = { hasAccess: boolean, errorCode?: string };

export const AccessService = {
	async checkMessageAccess(messageId: number): Promise<AccessCheckResult>
	{
		const payload = { data: { messageId } };

		try
		{
			await runAction(RestMethod.imV2AccessCheck, payload);
		}
		catch (errors)
		{
			return handleAccessError(errors);
		}

		return Promise.resolve({ hasAccess: true });
	},
};

const handleAccessError = (errors: RunActionError[]): AccessCheckResult => {
	const [error] = errors;
	const availableCodes = Object.values(AccessErrorCode);
	if (!availableCodes.includes(error.code))
	{
		console.error('AccessService: error checking access', error.code);

		// we need to handle all types of errors on this stage
		// but for now we let user through in case of unknown error
		return { hasAccess: true };
	}

	return { hasAccess: false, errorCode: error.code };
};
