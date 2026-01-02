export type RestError = {
	ex: {
		error: string,
		error_description: string,
	}
};

export const extractRestErrorCode = (error: RestError) => {
	const { ex: { error: errorCode } } = error;

	return errorCode;
};
