export const ErrorCode = Object.freeze({
	chat: {
		accessDenied: 'ACCESS_DENIED',
		notFound: 'CHAT_NOT_FOUND',
	},
	message: {
		accessDenied: 'MESSAGE_ACCESS_DENIED',
		accessDeniedByTariff: 'MESSAGE_ACCESS_DENIED_BY_TARIFF',
		notFound: 'MESSAGE_NOT_FOUND',
	},
	user: {
		invitedFromStructure: 'USER_INVITED_FROM_STRUCTURE',
		notFound: 'USER_NOT_FOUND',
	},
	file: {
		maxFileSize: 'MAX_FILE_SIZE_EXCEEDED',
	},
});
