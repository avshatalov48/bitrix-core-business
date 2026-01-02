import { RestMethod, ErrorCode } from 'im.v2.const';
import { runAction, type RunActionError } from 'im.v2.lib.rest';

const ACCESS_ERROR_CODES = new Set([
	ErrorCode.chat.accessDenied,
	ErrorCode.chat.notFound,
	ErrorCode.message.notFound,
	ErrorCode.message.accessDenied,
	ErrorCode.message.accessDeniedByTariff,
]);

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
	if (ACCESS_ERROR_CODES.has(error.code))
	{
		return { hasAccess: false, errorCode: error.code };
	}

	console.error('AccessService: error checking access', error.code);

	// we need to handle all types of errors on this stage
	// but for now we let user through in case of unknown error
	return { hasAccess: true };
};
