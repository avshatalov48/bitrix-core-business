import {ajax} from 'main.core';

import {Core} from 'im.v2.application.core';

type RunActionConfig = {
	data?: any,
	analyticsLabel?: Object
};

type RunActionResult = {
	status: 'success' | 'error',
	data: any,
	errors: RunActionError[]
};

type RunActionError = {
	code: number | string,
	customData: any,
	message: string
};

type BatchQuery = {
	[method: string]: {[param: string]: any}
}

export const runAction = (action: string, config: RunActionConfig = {}): Promise<RunActionResult> => {
	return new Promise((resolve, reject) => {
		ajax.runAction(action, config).then((response: RunActionResult) => {
			return resolve(response.data);
		}).catch((response: RunActionResult) => {
			return reject(response.errors);
		});
	});
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
					const {error: code, error_description: description} = methodResult.error().ex;
					reject({code, description});
					break;
				}
				data[method] = methodResult.data();
			}

			return resolve(data);
		});
	});
};