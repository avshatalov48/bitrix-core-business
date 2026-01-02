import { ajax, Type, type JsonObject } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

type RunActionConfig = {
	data?: JsonObject,
	analyticsLabel?: JsonObject
};

type RunActionResultData = any;

type RunActionResult = {
	status: 'success' | 'error',
	data: RunActionResultData,
	errors: RunActionError[]
};

type RunActionResponse = RunActionResultData | RunActionError[];

export type RunActionError = {
	code: number | string,
	customData: any,
	message: string
};

export type CallBatchError = {
	method: string,
	code: string,
	description: string,
};

type BatchQuery = {
	[method: string]: {[param: string]: any}
}

type ErrorsConfig = {
	retryCount: number,
	timeout: ?number,
}

const INVALID_AUTH_ERROR_CODE = 'invalid_authentication';

const errorCodesConfig = {
	[INVALID_AUTH_ERROR_CODE]: { retryCount: 1, timeout: null },
};

let retryCounter = null;

export const runAction = (action: string, config: RunActionConfig = {}): Promise<RunActionResponse> => {
	const preparedConfig = { ...config, data: prepareRequestData(config.data) };

	return new Promise((resolve, reject) => {
		ajax.runAction(action, preparedConfig).then((response: RunActionResult) => {
			retryCounter = null;

			return resolve(response.data);
		}).catch((response: RunActionResult) => {
			if (retryCounter === 0)
			{
				return reject(response.errors);
			}

			if (needRetryRequest(response.errors))
			{
				return handleErrors(action, preparedConfig, response);
			}

			return reject(response.errors);
		});
	});
};

const needRetryRequest = (responseErrors: RunActionError[]): boolean => {
	return responseErrors.some((responseError) => errorCodesConfig[responseError.code]);
};

const handleErrors = async (
	action: string,
	config: RunActionConfig,
	response: RunActionResult,
): Promise<RunActionResult> => {
	const errorConfig = getErrorConfig(response.errors);

	if (!retryCounter)
	{
		retryCounter = errorConfig.retryCount;
	}

	retryCounter--;

	if (hasInvalidAuthError(response.errors))
	{
		await EventEmitter.emitAsync(EventType.request.onAuthError, { errors: response.errors });
	}

	if (errorConfig.timeout)
	{
		return new Promise((resolve) => {
			setTimeout(() => {
				resolve(runAction(action, config));
			}, errorConfig.timeout);
		});
	}

	return runAction(action, config);
};

const getErrorConfig = (responseErrors: RunActionError[]): ErrorsConfig => {
	const error = responseErrors.find((responseError) => errorCodesConfig[responseError.code]);

	return errorCodesConfig[error.code];
};

const hasInvalidAuthError = (responseErrors: RunActionError[]): boolean => {
	return responseErrors.some((error) => error.code === INVALID_AUTH_ERROR_CODE);
};

export const callBatch = (query: BatchQuery): Promise<{[method: string]: any}> => {
	const preparedQuery = {};
	const methodsToCall = new Set();
	Object.entries(query).forEach(([method, params]) => {
		methodsToCall.add(method);
		preparedQuery[method] = [method, params];
	});

	return new Promise((resolve, reject) => {
		Core.getRestClient().callBatch(preparedQuery, (result) => {
			const data = {};
			for (const method of methodsToCall)
			{
				const methodResult: RestResult = result[method];
				if (methodResult.error())
				{
					const { error: code, error_description: description } = methodResult.error().ex;
					reject({ method, code, description });
					break;
				}
				data[method] = methodResult.data();
			}

			return resolve(data);
		});
	});
};

const prepareRequestData = (data: JsonObject): JsonObject => {
	if (data instanceof FormData)
	{
		return data;
	}

	if (!Type.isObjectLike(data))
	{
		return {};
	}

	const preparedData = {};
	for (const [key, value] of Object.entries(data))
	{
		let preparedValue = value;
		if (Type.isBoolean(value))
		{
			preparedValue = value === true ? 'Y' : 'N';
		}

		preparedData[key] = preparedValue;
	}

	return preparedData;
};
