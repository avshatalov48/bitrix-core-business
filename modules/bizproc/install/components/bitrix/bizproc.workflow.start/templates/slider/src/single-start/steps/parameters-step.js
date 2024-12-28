import { Tag, Text, Type, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { addMissingFormDataValues } from '../../helpers/add-missing-form-data-values';
import { isEqualsFormData } from '../../helpers/is-equals-form-data';
import { renderBpForm } from '../../helpers/render-bp-form';

import { startWorkflowAction } from '../actions/start-workflow-action';
import { StepWithErrors } from './step-with-errors';

import type { Property } from '../../types/property';
import type { ParametersStepData } from '../types/step-data';

const FORM_NAME = 'bizproc-ws-single-start-parameters';

import '../../css/style.css';
import '../../css/form.css';

export class ParametersStep extends StepWithErrors
{
	#parameters: ?Array<Property> = [];
	#documentType: [] = null;
	#signedDocumentId: string;
	#signedDocumentType: string;
	#templateId: number;

	#body: HTMLElement;
	#form: HTMLFormElement;

	#originalFormData: FormData = null;
	#isSent: boolean = false;
	#startTime: number;

	constructor(config: ParametersStepData)
	{
		super(config);

		this.#documentType = config.documentType;

		if (Type.isArrayFilled(config.parameters))
		{
			this.#parameters = config.parameters;
		}

		this.#templateId = Text.toInteger(config.templateId);
		this.#signedDocumentType = config.signedDocumentType;
		this.#signedDocumentId = config.signedDocumentId;

		this.#startTime = Math.round(Date.now() / 1000);
	}

	renderBody(): HTMLElement
	{
		if (!this.#body)
		{
			this.#body = Tag.render`
				<div class="bizproc__ws_start__content-body">
					${this.renderErrors()}
					<div class="bizproc__ws_start__content-form">
						${this.#renderParametersForm()}
					</div>
				</div>
			`;
		}

		return this.#body;
	}

	#renderParametersForm(): HTMLElement
	{
		this.#form = renderBpForm(
			FORM_NAME,
			Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_PARAMETERS_TITLE'),
			this.#parameters,
			this.#documentType,
			null,
			this.#signedDocumentId,
		);

		this.#originalFormData = new FormData(this.#form);
		this.#subscribeOnRenderEvents();

		return this.#form;
	}

	#subscribeOnRenderEvents(): void
	{
		EventEmitter.subscribe(
			'BX.Bizproc.FieldType.onCustomRenderControlFinished',
			this.#onAfterFieldCollectionRenderer.bind(this),
		);
		EventEmitter.subscribe(
			'BX.Bizproc.FieldType.onCollectionRenderControlFinished',
			this.#onAfterFieldCollectionRenderer.bind(this),
		);
	}

	#onAfterFieldCollectionRenderer()
	{
		if (this.#originalFormData && document.forms.namedItem(FORM_NAME))
		{
			addMissingFormDataValues(this.#originalFormData, new FormData(document.forms.namedItem(FORM_NAME)));
		}
	}

	canExit(): boolean
	{
		if (!this.#originalFormData || this.#isSent)
		{
			return true;
		}

		return isEqualsFormData(new FormData(this.#form), this.#originalFormData);
	}

	onBeforeNextStep(): Promise
	{
		this.cleanErrors();

		const data = new FormData(this.#form);
		data.set('templateId', this.#templateId);
		data.set('signedDocumentType', this.#signedDocumentType);
		data.set('signedDocumentId', this.#signedDocumentId);
		data.set('startDuration', Math.round(Date.now() / 1000) - this.#startTime);

		return new Promise((resolve, reject) => {
			startWorkflowAction(data)
				.then(() => {
					this.#isSent = true;
					resolve();
				})
				.catch((response) => {
					this.showErrors(response.errors);
					reject();
				})
			;
		});
	}
}
