import { Tag, Loc, Text, ajax, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Button, ButtonColor } from 'ui.buttons';

import { Breadcrumbs } from '../components/breadcrumbs';
import { ErrorNotifier } from '../components/error-notifier';
import { Header } from '../components/header';
import { Buttons } from '../components/buttons';

import { addMissingFormDataValues } from '../helpers/add-missing-form-data-values';
import { isEqualsFormData } from '../helpers/is-equals-form-data';
import { renderBpForm } from '../helpers/render-bp-form';
import type { Property } from '../types/property';

import { showCancelDialog } from './helpers/show-cancel-dialog';

import type { EditConstantsData } from './types/edit-constants-data';

import '../css/style.css';
import '../css/form.css';

const FORM_NAME = 'bizproc-ws-edit-constants';
const HTML_ELEMENT_ID = 'bizproc-workflow-start-edit-constants';

export class EditConstants
{
	#header: Header;
	#breadcrumbs: Breadcrumbs;
	#buttons: Buttons;
	#errorNotifier: ErrorNotifier;

	#constants: Array<Property>;
	#documentType: [] = null;
	#signedDocumentType: string;
	#templateId: number;
	#templateName: string;

	#form: HTMLFormElement;
	#canExit: boolean = false;
	#isExitInProcess: boolean = false;

	#originalFormData: FormData = null;

	constructor(config: EditConstantsData)
	{
		this.#header = new Header({
			title: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_EDIT_CONSTANTS_TITLE'),
			description: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_EDIT_CONSTANTS_DESCRIPTION'),
		});

		this.#breadcrumbs = new Breadcrumbs({
			items: [{
				id: 'edit-constants',
				text: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_EDIT_CONSTANTS_STEP_AUTOSTART_TITLE'),
				active: true,
			}],
		});

		this.#buttons = new Buttons({
			buttons: {
				edit: [
					Buttons.createBackButton(this.#exit.bind(this)),
					new Button({
						id: 'save',
						text: Text.encode(Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_BUTTON_SAVE')),
						onclick: this.#handleSaveClick.bind(this),
						color: ButtonColor.PRIMARY,
					}),
				],
			},
			wrapper: document.getElementById(`${HTML_ELEMENT_ID}-buttons`).querySelector('.ui-button-panel'),
		});
		this.#buttons.show();

		this.#errorNotifier = new ErrorNotifier({});

		this.#documentType = config.documentType;
		this.#signedDocumentType = config.signedDocumentType;
		this.#templateId = Text.toInteger(config.templateId);
		this.#templateName = config.templateName;
		this.#constants = config.constants;

		this.#subscribeOnSliderClose();
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start">
				${this.#header.render()}
				<div class="bizproc__ws_start__body">
					${this.#breadcrumbs.render()}
					<div class="bizproc__ws_start__container">
						${this.#errorNotifier.render()}
						<div class="bizproc__ws_start__content">
							<div class="bizproc__ws_start__content-body">
								${this.#renderConstants()}
							</div>
						</div>
					</div>
				<div>
			</div>
		`;
	}

	#renderConstants(): HTMLElement
	{
		this.#form = renderBpForm(
			FORM_NAME,
			this.#templateName,
			this.#constants,
			this.#documentType,
			null,
			null,
		);
		Dom.append(this.#renderErrors(), this.#form);

		this.#originalFormData = new FormData(this.#form);
		this.#subscribeOnRenderEvents();

		return Tag.render`<div class="bizproc__ws_start__content-form">${this.#form}</div>`;
	}

	#renderErrors(): HTMLElement
	{
		return this.#errorNotifier.render();
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

	#handleSaveClick(button: Button)
	{
		button.setWaiting(true);
		this.#errorNotifier.clean();

		const data = new FormData(this.#form);
		data.set('templateId', this.#templateId);
		data.set('signedDocumentType', this.#signedDocumentType);

		ajax.runAction('bizproc.workflow.starter.setConstants', { data })
			.then(() => {
				button.setWaiting(false);
				this.#canExit = true;
				this.#exit();
			})
			.catch((response) => {
				this.#errorNotifier.errors = response.errors;
				this.#errorNotifier.show();
				button.setWaiting(false);
			})
		;
	}

	#exit()
	{
		if (BX.SidePanel.Instance.getSliderByWindow(window))
		{
			BX.SidePanel.Instance.getSliderByWindow(window).close();
		}
	}

	#subscribeOnSliderClose()
	{
		const slider = BX.SidePanel.Instance.getSliderByWindow(window);
		if (slider)
		{
			EventEmitter.subscribe(slider, 'SidePanel.Slider:onClose', (event) => {
				if (!this.#canExit && this.#isChangedConstants())
				{
					event.getCompatData()[0].denyAction();

					if (!this.#isExitInProcess)
					{
						this.#isExitInProcess = true;
						showCancelDialog(
							() => {
								this.#canExit = true;
								slider.close();

								return true;
							},
							() => {
								this.#isExitInProcess = false;

								return true;
							},
						);
					}
				}
			});
		}
	}

	#isChangedConstants(): boolean
	{
		if (!this.#originalFormData)
		{
			return false;
		}

		return !isEqualsFormData(new FormData(this.#form), this.#originalFormData);
	}
}
