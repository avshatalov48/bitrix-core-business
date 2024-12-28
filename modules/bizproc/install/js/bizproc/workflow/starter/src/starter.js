import { Type, Loc, Text, Uri } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Dialog } from 'ui.entity-selector';
import 'ui.notification';
import 'sidepanel';
import type { Action } from './call-action-helper';

import { ComplexDocumentId } from './data/complex-document-id';
import { ComplexDocumentType } from './data/complex-document-type';
import { CallActionHelper } from './call-action-helper';
import { ErrorNotifier } from './error-notifier';
import { managerInstance } from './index';
import type { StarterData } from './types/starter-data';

export type SignedDocumentType = string;
export type SignedDocumentId = string;

export class Starter extends EventEmitter
{
	#templates: ?[] = null;
	#signedDocumentType: ?SignedDocumentType = null;
	#signedDocumentId: ?SignedDocumentId = null;
	#complexDocumentType: ?ComplexDocumentType = null;
	#complexDocumentId: ?ComplexDocumentId = null;

	#templatesSelector: ?Dialog = null;
	#callActionHelper: CallActionHelper;
	#hasCustomAjaxUrl: boolean = false;

	constructor(data: StarterData)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Workflow.Starter');

		this.#setDocumentType(data);
		if (Type.isNil(this.#complexDocumentType) && Type.isNil(this.#signedDocumentType))
		{
			throw new TypeError('document type is empty');
		}

		this.#setDocumentId(data);

		if (Type.isArray(data.templates))
		{
			this.#templates = data.templates;
		}

		this.#hasCustomAjaxUrl = Type.isStringFilled(data.ajaxUrl);
		this.#callActionHelper = new CallActionHelper({
			complexDocumentType: this.#complexDocumentType,
			signedDocumentType: this.#signedDocumentType,
			complexDocumentId: this.#complexDocumentId,
			signedDocumentId: this.#signedDocumentId,
			customAjaxUrl: this.#hasCustomAjaxUrl ? data.ajaxUrl : null,
		});

		managerInstance.put(this);
	}

	#setDocumentType(data: StarterData): void
	{
		if (
			Type.isStringFilled(data.moduleId)
			&& Type.isStringFilled(data.entity)
			&& Type.isStringFilled(data.documentType)
		)
		{
			this.#complexDocumentType = new ComplexDocumentType(data.moduleId, data.entity, data.documentType);
		}

		if (Type.isStringFilled(data.signedDocumentType))
		{
			this.#signedDocumentType = data.signedDocumentType;
		}
	}

	#setDocumentId(data: StarterData): void
	{
		if (
			Type.isStringFilled(data.moduleId)
			&& Type.isStringFilled(data.entity)
			&& (Type.isStringFilled(data.documentId) || Type.isNumber(data.documentId))
		)
		{
			this.#complexDocumentId = new ComplexDocumentId(data.moduleId, data.entity, data.documentId);
		}

		if (Type.isStringFilled(data.signedDocumentId))
		{
			this.#signedDocumentId = data.signedDocumentId;
		}
	}

	static singleStart(config: StarterData & { hasParameters: boolean, templateId: number }, callback: ?Function)
	{
		const templateId = Text.toInteger(config?.templateId);
		if (templateId <= 0)
		{
			return;
		}

		let starter = null;
		try
		{
			starter = new Starter({
				moduleId: config.moduleId,
				entity: config.entity,
				documentType: config.documentType,
				documentId: config.documentId,
				signedDocumentType: config.signedDocumentType,
				signedDocumentId: config.signedDocumentId,
				templates: config.templates || null,
				ajaxUrl: config.ajaxUrl || '',
			});
		}
		catch (e)
		{
			console.error(e);

			return;
		}

		if (Type.isFunction(callback))
		{
			EventEmitter.subscribe(starter, 'onAfterStartWorkflow', callback);
		}

		starter.beginStartWorkflow(templateId)
			.then(() => {
				managerInstance.remove(starter);
			})
			.catch(() => {})
		;
	}

	static showTemplates(
		starterData: { signedDocumentType: string, signedDocumentId: string },
		config: { targetNode: ?HTMLElement, callback: ?Function },
	): void
	{
		let starter = null;
		try
		{
			starter = new Starter({
				signedDocumentType: starterData.signedDocumentType,
				signedDocumentId: starterData.signedDocumentId,
			});
		}
		catch (e)
		{
			console.error(e);

			return;
		}

		starter.#showTemplatesSlider(() => {
			if (Type.isFunction(config.callback))
			{
				config.callback();
			}
			managerInstance.remove(starter);
		});
	}

	get signedDocumentType(): ?string
	{
		return this.#signedDocumentType;
	}

	get complexDocumentType(): ?ComplexDocumentType
	{
		return this.#complexDocumentType;
	}

	#showTemplatesSlider(callback: ?Function = null): void
	{
		const sliderOptions = {
			width: 970,
			cacheable: false,
			events: {
				onCloseComplete: Type.isFunction(callback) ? callback : () => {},
			},
		};

		const componentParams = {
			signedDocumentType: this.#signedDocumentType,
			signedDocumentId: this.#signedDocumentId,
		};

		const url = BX.Uri.addParam(
			'/bitrix/components/bitrix/bizproc.workflow.start.list/',
			componentParams,
		);

		BX.SidePanel.Instance.open(url, sliderOptions);
	}

	// compatibility
	showTemplatesMenu(targetNode)
	{
		if (Type.isStringFilled(this.#signedDocumentType) && !this.#hasCustomAjaxUrl)
		{
			this.#showTemplatesSlider();

			return;
		}

		if (!Type.isElementNode(targetNode) && !Type.isNull(targetNode))
		{
			return;
		}

		if (Type.isArray(this.#templates))
		{
			if (!this.#templatesSelector)
			{
				this.#initTemplateSelector(targetNode);
			}

			this.#templatesSelector.show();

			return;
		}

		this.#loadTemplates()
			.then(() => {
				this.showTemplatesMenu(targetNode);
			})
			.catch((response) => {
				this.#showErrors(response?.errors);
			})
		;
	}

	#loadTemplates(): Promise
	{
		return new Promise((resolve, reject) => {
			this.#callAction('load')
				.then((response) => {
					this.#templates = Type.isArray(response.data.templates) ? response.data.templates : [];
					resolve(response);
				})
				.catch(reject)
			;
		});
	}

	#initTemplateSelector(targetNode: HTMLElement)
	{
		const items = [];
		if (Type.isArrayFilled(this.#templates))
		{
			this.#templates.forEach((template) => {
				if (Text.toInteger(template.id) > 0 && Type.isStringFilled(template.name))
				{
					items.push({
						id: template.id,
						title: template.name,
						subtitle: template.description || '',
						entityId: 'template',
						tabs: 'recents',
						customData: template,
					});
				}
			});
		}

		this.#templatesSelector = new Dialog({
			targetNode,
			context: 'bp_workflow_starter',
			items,
			multiple: false,
			dropdownMode: true,
			enableSearch: true,
			hideOnSelect: true,
			clearSearchOnSelect: true,
			hideByEsc: true,
			cacheable: true,
			focusOnFirst: true,
			showAvatars: false,
			compactView: false,
			events: {
				'Item:onSelect': (event) => {
					this.#templatesSelector.deselectAll();
					const customData = event.getData().item?.getCustomData();
					if (customData)
					{
						this.#onTemplateSelect(customData);
					}
				},
			},
			recentTabOptions: {
				stub: true,
				stubOptions: {
					title: Loc.getMessage('BIZPROC_JS_WORKFLOW_STARTER_EMPTY_TEMPLATES'),
				},
			},
		});
	}

	#onTemplateSelect(template: Map)
	{
		this.beginStartWorkflow(template.get('id')).then(() => {}).catch(() => {});
	}

	// compatibility
	showParametersPopup(templateId)
	{
		this.beginStartWorkflow(templateId).then(() => {}).catch(() => {});
	}

	beginStartWorkflow(templateId: number): Promise
	{
		if (Text.toInteger(templateId) <= 0)
		{
			return Promise.resolve();
		}

		return new Promise((resolve, reject) => {
			this.#showStepByStepSlider({ templateId, autoExecuteType: null })
				.then((data: { workflowId: string }) => {
					if (Type.isStringFilled(data.workflowId))
					{
						managerInstance.fireEvent(this, 'onAfterStartWorkflow', { workflowId: data.workflowId });
					}

					resolve();
				})
				.catch(reject)
			;
		});
	}

	// compatibility
	showAutoStartParametersPopup(
		autoExecuteType: number,
		config: { callback: Function } = {},
	)
	{
		this.#showStepByStepSlider({ templateId: null, autoExecuteType })
			.then((data: { signedParameters: string }) => {
				if (Type.isFunction(config?.callback))
				{
					if (Type.isString(data.signedParameters))
					{
						config.callback({ parameters: data.signedParameters });

						return;
					}

					config.callback({ parameters: null });
				}
			})
			.catch(() => {})
		;
	}

	#showStepByStepSlider(componentParams: { templateId: ?number, autoExecuteType: ?number }): Promise
	{
		return new Promise((resolve) => {
			BX.SidePanel.Instance.open(
				this.#createStepByStepSliderUrl(componentParams),
				{
					width: 900,
					cacheable: false,
					allowChangeHistory: false,
					// loader: '', // todo: loader
					events: {
						onCloseComplete: (event: BX.SidePanel.Event) => {
							const slider = event.getSlider();
							const dictionary: ?BX.SidePanel.Dictionary = slider ? slider.getData() : null;
							let data = {};
							if (dictionary && dictionary.has('data'))
							{
								data = {
									workflowId: dictionary.get('data').workflowId || null,
									signedParameters: dictionary.get('data').signedParameters || null,
								};
							}

							resolve(data);
						},
					},
				},
			);
		});
	}

	#createStepByStepSliderUrl(componentParams: { templateId: ?number, autoExecuteType: ?number }): string
	{
		let url = Uri.addParam(
			'/bitrix/components/bitrix/bizproc.workflow.start/',
			{ sessid: Loc.getMessage('bitrix_sessid') }, // todo: remove?
		);

		const templateId = Text.toInteger(componentParams.templateId);
		if (templateId > 0)
		{
			url = Uri.addParam(url, { templateId });
		}

		const autoExecuteType = Text.toInteger(componentParams.autoExecuteType);
		if (!Type.isNil(componentParams.autoExecuteType) && autoExecuteType >= 0)
		{
			url = Uri.addParam(url, { autoExecuteType });
		}

		if (this.#complexDocumentType?.moduleId)
		{
			url = Uri.addParam(
				url,
				{
					moduleId: this.#complexDocumentType.moduleId,
					entity: this.#complexDocumentType.entity,
					documentType: this.#complexDocumentType.documentType,
				},
			);
		}

		if (this.#signedDocumentType)
		{
			url = Uri.addParam(url, { signedDocumentType: this.#signedDocumentType });
		}

		if (this.#complexDocumentId?.documentId)
		{
			url = Uri.addParam(url, { documentId: this.#complexDocumentId.documentId });
		}

		if (this.#signedDocumentId)
		{
			url = Uri.addParam(url, { signedDocumentId: this.#signedDocumentId });
		}

		return url;
	}

	#callAction(action: Action, formData: {} | FormData = {}, addData: {} = {}): Promise
	{
		return this.#callActionHelper.callAction(action, this.#callActionHelper.addData(addData, formData));
	}

	#showErrors(errors: ?[], targetWindow: ?Window)
	{
		const notifier = new ErrorNotifier(errors);
		const method = Type.isNil(targetWindow) ? 'show' : 'showToWindow';

		notifier[method](targetWindow);
	}
}
