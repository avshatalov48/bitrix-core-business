import objectToFormData from './internal/convert-object-to-form-data';

export default class Data
{
	/**
	 * Converts object to FormData
	 * @param source
	 * @return {FormData}
	 */
	static convertObjectToFormData(source: {[key: string]: any}): FormData
	{
		return objectToFormData(source);
	}
}