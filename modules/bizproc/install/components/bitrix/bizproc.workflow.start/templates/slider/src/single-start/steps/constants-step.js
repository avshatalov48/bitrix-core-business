import { Type, Tag, Text, Loc, ajax, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Alert, AlertColor, AlertIcon } from 'ui.alerts';
import { Button, ButtonSize, ButtonColor } from 'ui.buttons';

import { addMissingFormDataValues } from '../../helpers/add-missing-form-data-values';
import { isEqualsFormData } from '../../helpers/is-equals-form-data';
import { renderBpForm } from '../../helpers/render-bp-form';

import { StepWithErrors } from './step-with-errors';

import type { Property } from '../../types/property';
import type { ConstantsStepData } from '../types/step-data';

const FORM_NAME = 'bizproc-ws-single-start-constants';

import '../../css/style.css';
import '../../css/form.css';

export class ConstantsStep extends StepWithErrors
{
	#constants: ?Array<Property> = [];
	#documentType: [] = null;
	#signedDocumentType: string;
	#signedDocumentId: string;
	#templateId: number;

	#body: HTMLElement;
	#form: HTMLFormElement;

	#isConstantsTuned: boolean = false;
	#originalFormData: FormData = null;

	constructor(config: ConstantsStepData)
	{
		super(config);

		this.#documentType = config.documentType;
		this.#signedDocumentType = config.signedDocumentType;
		this.#signedDocumentId = config.signedDocumentId;
		this.#templateId = Text.toInteger(config.templateId);

		if (Type.isArrayFilled(config.constants))
		{
			this.#constants = config.constants;
		}
	}

	get #hasConstants(): boolean
	{
		return Type.isArrayFilled(this.#constants);
	}

	renderBody(): HTMLElement
	{
		if (!this.#body)
		{
			this.#body = Tag.render`
				<div class="bizproc__ws_start__content-body">
					${this.#hasConstants ? this.#renderConstants() : this.#renderStub()}
				</div>
			`;
		}

		return this.#body;
	}

	isNextEnabled(): boolean
	{
		return this.#isConstantsTuned;
	}

	#renderStub(): HTMLElement
	{
		return (
			(new Alert({
				text: Text.encode(Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_NOT_TUNING_CONSTANTS')),
				color: AlertColor.WARNING,
				icon: AlertIcon.INFO,
			}))
				.render()
		);
	}

	#renderConstants(): HTMLElement
	{
		this.#form = renderBpForm(FORM_NAME, this.name, this.#constants, this.#documentType, null, this.#signedDocumentId);
		Dom.append(this.renderErrors(), this.#form);
		Dom.append(this.#renderSaveButton(), this.#form);

		this.#originalFormData = new FormData(this.#form);
		this.#subscribeOnRenderEvents();

		return Tag.render`<div class="bizproc__ws_start__content-form">${this.#form}</div>`;
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

	#renderSaveButton(): HTMLElement
	{
		return (
			(new Button({
				text: Text.encode(Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_BUTTON_SAVE')),
				size: ButtonSize.EXTRA_SMALL,
				color: ButtonColor.SECONDARY,
				onclick: this.#handleSaveClick.bind(this),
			}))
				.render()
		);
	}

	#handleSaveClick(button: Button)
	{
		button.setWaiting(true);
		this.cleanErrors();

		const data = new FormData(this.#form);
		data.set('templateId', this.#templateId);
		data.set('signedDocumentType', this.#signedDocumentType);

		ajax.runAction('bizproc.workflow.starter.setConstants', { data })
			.then(() => {
				this.#isConstantsTuned = true;
				this.onChangeStepAvailability();
				button.setWaiting(false);
			})
			.catch((response) => {
				this.showErrors(response.errors);
				button.setWaiting(false);
			})
		;
	}

	canExit(): boolean
	{
		if (!this.#hasConstants || !this.#originalFormData || this.#isConstantsTuned)
		{
			return true;
		}

		return isEqualsFormData(new FormData(this.#form), this.#originalFormData);
	}
}
