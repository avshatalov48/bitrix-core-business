import { ajax, Loc, Type, Text } from 'main.core';
import type { CallActionHelperData } from './types/call-action-helper-data';

export type Action = 'load' | 'start' | 'check_parameters';

const ACTION_AJAX_MAP = Object.freeze({
	load: 'get_templates',
	start: 'start_workflow',
	check_parameters: 'check_parameters',
});

const ACTION_CONTROLLER_MAP = Object.freeze({
	load: 'getTemplates',
	start: 'startWorkflow',
	check_parameters: 'checkParameters',
});

export class CallActionHelper
{
	#defaultData: {} = {};
	#ajaxUrl: string = '';
	#controller: string = 'bizproc.workflow.starter';

	constructor(data: CallActionHelperData)
	{
		this.#fillDefaultData(data);

		if (Type.isStringFilled(data.customAjaxUrl))
		{
			this.#ajaxUrl = data.customAjaxUrl;
		}
		else if (!Type.isStringFilled(this.#defaultData.signed_document_type))
		{
			console.warn(`
				Bizproc.Workflow.Starter: 
				Using the document type in parts has been deprecated and will soon cease to be supported. 
				Please use a signed document type
			`);

			this.#ajaxUrl = '/bitrix/components/bitrix/bizproc.workflow.start/ajax.php';
		}
	}

	#fillDefaultData(data: CallActionHelperData)
	{
		if (!Type.isNil(data.signedDocumentType))
		{
			this.#defaultData.signed_document_type = data.signedDocumentType;
		}

		if (!Type.isNil(data.signedDocumentId))
		{
			this.#defaultData.signed_document_id = data.signedDocumentId;
		}

		if (!Type.isNil(data.complexDocumentType?.moduleId))
		{
			this.#defaultData.module_id = data.complexDocumentType.moduleId;
		}

		if (!Type.isNil(data.complexDocumentType?.entity))
		{
			this.#defaultData.entity = data.complexDocumentType.entity;
		}

		if (!Type.isNil(data.complexDocumentType?.documentType))
		{
			this.#defaultData.document_type = data.complexDocumentType.documentType;
		}

		if (!Type.isNil(data.complexDocumentId?.documentId))
		{
			this.#defaultData.document_id = data.complexDocumentId.documentId;
		}
	}

	get #hasAjaxUrl(): boolean
	{
		return Type.isStringFilled(this.#ajaxUrl);
	}

	callAction(action: Action, actionData: {} | FormData = {}): Promise
	{
		const actionName = this.#hasAjaxUrl ? ACTION_AJAX_MAP[action] : ACTION_CONTROLLER_MAP[action];
		if (!Type.isStringFilled(actionName))
		{
			return Promise.reject(new Error('incorrect action')); // todo: Loc
		}

		const data = this.addData(this.#defaultData, actionData);

		return (
			this.#hasAjaxUrl
				? this.#callAjax(actionName, data)
				: this.#callController(actionName, data)
		);
	}

	#callAjax(actionName: string, actionData: {} | FormData = {}): Promise
	{
		const data = this.addData(
			{
				sessid: Loc.getMessage('bitrix_sessid'),
				site: Loc.getMessage('SITE_ID'),
				ajax_action: actionName,
			},
			actionData,
		);

		return new Promise((resolve, reject) => {
			const ajaxConfig = {
				method: 'POST',
				dataType: 'json',
				url: this.#ajaxUrl,
				data,
				onsuccess: (response) => {
					if (response.success)
					{
						resolve(response);
					}
					else
					{
						reject(response);
					}
				},
				onfailure: () => {
					reject();
				},
			};

			if (!Type.isPlainObject(data))
			{
				ajaxConfig.preparePost = false;
			}

			ajax(ajaxConfig);
		});
	}

	#callController(actionName: string, data: {} | FormData = {}): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runAction(`${this.#controller}.${actionName}`, { data }).then(resolve).catch(reject);
		});
	}

	addData(targetData: {}, actionData: {} | FormData = {}): {} | FormData
	{
		const data = actionData;
		const isPlainObject = Type.isPlainObject(data);

		Object.entries(targetData).forEach(([key, value]) => {
			const modifiedKey = this.#hasAjaxUrl ? key : Text.toCamelCase(key);

			if (isPlainObject)
			{
				data[modifiedKey] = value;

				return;
			}

			data.set(modifiedKey, value);
		});

		return data;
	}
}
