import { ajax, Type, type JsonObject } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

type RunActionConfig = {
	data?: JsonObject,
	analyticsLabel?: JsonObject
};

type RunActionResult = {
	status: 'success' | 'error',
	data: any,
	errors: RunActionError[]
};

export type RunActionError = {
	code: number | string,
	customData: any,
	message: string
};

type BatchQuery = {
	[method: string]: {[param: string]: any}
}

const INVALID_AUTH_ERROR_CODE = 'invalid_authentication';
let retryAllowed = true;

export const runAction = (action: string, config: RunActionConfig = {}): Promise<RunActionResult> => {
	const preparedConfig = { ...config, data: prepareRequestData(config.data) };

	return new Promise((resolve, reject) => {
		ajax.runAction(action, preparedConfig).then((response: RunActionResult) => {
			retryAllowed = true;

			return resolve(response.data);
		}).catch((response: RunActionResult) => {
			if (needRetryRequest(response.errors))
			{
				retryAllowed = false;

				return handleErrors(action, preparedConfig, response);
			}

			return reject(response.errors);
		});
	});
};

const handleErrors = async (action: string, config: RunActionConfig, response: RunActionResult) => {
	await EventEmitter.emitAsync(EventType.request.onAuthError, { errors: response.errors });

	return runAction(action, config);
};

const needRetryRequest = (responseErrors: RunActionError[]): boolean => {
	if (!retryAllowed)
	{
		return false;
	}

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
