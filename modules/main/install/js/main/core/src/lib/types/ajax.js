import type { JsonObject } from './json';

export type AjaxResponse<DataType> = {
	status: 'success' | 'error' | 'denied',
	errors: AjaxError[],
	data: DataType,
};

export type AjaxError = {
	message: string,
	code: string | number,
	customData: JsonObject | null,
};
