import { Tag, Loc, Type, Text, ajax } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Button, ButtonColor } from 'ui.buttons';

import { Breadcrumbs } from '../components/breadcrumbs';
import { ErrorNotifier } from '../components/error-notifier';
import { Header } from '../components/header';
import { Buttons } from '../components/buttons';

import { addMissingFormDataValues } from '../helpers/add-missing-form-data-values';
import { renderBpForm } from '../helpers/render-bp-form';

import { showCancelDialog } from './helpers/show-cancel-dialog';

import type { AutostartData, TemplateData } from './types/autostart-data';

import '../css/style.css';
import '../css/form.css';

const FORM_NAME = 'bizproc-ws-autostart';
const HTML_ELEMENT_ID = 'bizproc-workflow-start-autostart';

export class Autostart
{
	#header: Header;
	#breadcrumbs: Breadcrumbs;
	#buttons: Buttons;
	#errorNotifier: ErrorNotifier;

	#templates: Array<TemplateData>;
	#documentType: [];
	#signedDocumentType: string;
	#signedDocumentId: ?string;
	#autoExecute: number;

	#forms: [] = [];
	#canExit: boolean = false;
	#isExitInProcess: boolean = false;

	constructor(config: AutostartData)
	{
		this.#header = new Header({
			title: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_TITLE'),
			description: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_DESCRIPTION'),
		});

		this.#breadcrumbs = new Breadcrumbs({
			items: [{
				id: 'autostart',
				text: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_STEP_AUTOSTART_TITLE'),
				active: true,
			}],
		});

		this.#buttons = new Buttons({
			buttons: {
				autostart: [
					Buttons.createBackButton(this.#exit.bind(this)),
					new Button({
						id: 'save',
						text: Text.encode(Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_BUTTON_SAVE')),
						onclick: this.#save.bind(this),
						color: ButtonColor.PRIMARY,
					}),
				],
			},
			wrapper: document.getElementById(`${HTML_ELEMENT_ID}-buttons`).querySelector('.ui-button-panel'),
		});

		this.#errorNotifier = new ErrorNotifier({});

		if (Type.isArrayFilled(config.templates))
		{
			this.#templates = config.templates;
		}

		this.#documentType = config.documentType;
		this.#signedDocumentType = config.signedDocumentType;
		this.#signedDocumentId = config.signedDocumentId || null;
		this.#autoExecute = Text.toInteger(config.autoExecuteType);

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
								${this.#templates.map((template) => this.#renderForm(template))}
							</div>
						</div>
					</div>
				<div>
			</div>
		`;
	}

	onAfterRender()
	{
		this.#buttons.show();
	}

	#renderForm(template: TemplateData): HTMLElement
	{
		const form = renderBpForm(
			`${FORM_NAME}_${template.id}`,
			template.name,
			template.parameters,
			this.#documentType,
			template.description,
		);

		this.#forms.push(form);

		return Tag.render`<div class="bizproc__ws_start__content-form">${form}</div>`;
	}

	#exit()
	{
		if (BX.SidePanel.Instance.getSliderByWindow(window))
		{
			BX.SidePanel.Instance.getSliderByWindow(window).close();
		}
	}

	#save()
	{
		this.#buttons.resolveWaitingState({ save: true });

		const data = new FormData();
		this.#forms.forEach((form) => {
			addMissingFormDataValues(data, new FormData(form));
		});
		data.set('signedDocumentType', this.#signedDocumentType);
		if (this.#signedDocumentId)
		{
			data.set('signedDocumentId', this.#signedDocumentId);
		}
		data.set('autoExecuteType', this.#autoExecute);

		ajax.runAction('bizproc.workflow.starter.checkParameters', { data })
			.then((response) => {
				const slider = BX.SidePanel.Instance.getSliderByWindow(window);
				if (slider)
				{
					const dictionary: BX.SidePanel.Dictionary = slider.getData();
					dictionary.set('data', { signedParameters: response.data.parameters });
				}

				this.#canExit = true;
				this.#exit();
			})
			.catch((response) => {
				this.#errorNotifier.errors = response.errors;
				this.#errorNotifier.show();
				this.#buttons.resolveWaitingState({ save: false });
			})
		;
	}

	#subscribeOnSliderClose()
	{
		const slider = BX.SidePanel.Instance.getSliderByWindow(window);
		if (slider)
		{
			EventEmitter.subscribe(slider, 'SidePanel.Slider:onClose', (event) => {
				if (!this.#canExit)
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
}
