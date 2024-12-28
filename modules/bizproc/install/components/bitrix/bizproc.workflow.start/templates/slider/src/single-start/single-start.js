import { Tag, Loc, Type, Dom, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Button } from 'ui.buttons';
import 'sidepanel';

import { Header } from '../components/header';
import { Breadcrumbs } from '../components/breadcrumbs';
import { Buttons } from '../components/buttons';
import { ErrorNotifier } from '../components/error-notifier';

import { showExitDialog } from './helpers/show-exit-dialog';
import { ConstantsStep } from './steps/constants-step';
import { ParametersStep } from './steps/parameters-step';
import { RecommendationStep } from './steps/recommendation-step';
import { SuccessStartStep } from './steps/success-start-step';

import { startWorkflowAction } from './actions/start-workflow-action';

import type { Step } from './steps/step';
import type { BreadcrumbsItemData } from '../components/breadcrumbs';
import type { SingleStartData, StepId } from './types/single-start-data';

import '../css/style.css';
import '../css/single-start.css';

const HTML_ELEMENT_ID = 'bizproc-workflow-start-single-start';

export class SingleStart
{
	#header: Header;
	#breadcrumbs: Breadcrumbs;
	#errorNotifier: ErrorNotifier;
	#steps: Map<StepId, Step> = new Map();
	#buttons: Buttons;

	#sequenceSteps: [] = [];
	#currentStepId: string;
	#content: HTMLElement;

	#canExit: boolean = false;
	#isExitInProcess: boolean = false;

	#templateId: number;
	#signedDocumentType: string;
	#signedDocumentId: string;

	#startTime: number;

	constructor(config: SingleStartData)
	{
		this.#startTime = Math.round(Date.now() / 1000);
		const composedData = this.#composeData(config);

		this.#header = new Header({
			title: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_TITLE'),
			description: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_DESCRIPTION'),
		});

		this.#breadcrumbs = new Breadcrumbs({
			items: Object.values(composedData)
				.map((data) => data.breadcrumbs)
			,
		});

		this.#errorNotifier = new ErrorNotifier({});

		Object.entries(composedData)
			.forEach(([key, data]) => {
				this.#steps.set(key, data.step);
				data.step.subscribe(
					'onChangeStepAvailability',
					this.#resolveButtonsEnableState.bind(this),
				);
			})
		;
		this.#sequenceSteps = Object.keys(composedData);
		this.#currentStepId = this.#sequenceSteps.at(0);

		this.#buttons = new Buttons({
			buttons: Object.fromEntries(
				Object.entries(composedData).map(([key, data]) => [key, data.buttons]),
			),
			wrapper: document.getElementById(`${HTML_ELEMENT_ID}-buttons`).querySelector('.ui-button-panel'),
		});

		this.#signedDocumentType = config.signedDocumentType;
		this.#signedDocumentId = config.signedDocumentId;
		this.#templateId = Text.toInteger(config.id);

		this.#subscribeOnSliderClose();
	}

	#resolveButtonsEnableState()
	{
		this.#buttons.resolveEnableState({
			next: this.#steps.get(this.#currentStepId).isNextEnabled(),
			back: this.#steps.get(this.#currentStepId).isBackEnabled(),
			start: this.#steps.get(this.#currentStepId).isNextEnabled(),
		});
	}

	render(): HTMLElement
	{
		this.#content = this.#renderContent();

		return Tag.render`
			<div class="bizproc__ws_start">
				${this.#header.render()}
				<div class="bizproc__ws_start__body">
					${this.#breadcrumbs.render()}
					${this.#content}
				</div>
			</div>
		`;
	}

	#renderContent(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__container">
				${this.#errorNotifier.render()}
				${this.#steps.has(this.#currentStepId) ? this.#steps.get(this.#currentStepId).render() : null}
			</div>
		`;
	}

	#updateContent(): void
	{
		if (this.#content)
		{
			const content = this.#renderContent();
			Dom.replace(this.#content, content);
			this.#content = content;

			if (this.#steps.has(this.#currentStepId))
			{
				this.#steps.get(this.#currentStepId).onAfterRender();
			}
		}
	}

	onAfterRender()
	{
		if (this.#steps.has('recommendation'))
		{
			this.#steps.get('recommendation').onAfterRender();
		}

		this.#buttons.show();
	}

	#next()
	{
		this.#cleanErrors();
		if (this.#isNextStepEnable())
		{
			this.#markButtonsOnBeforeNextStep();

			this.#steps.get(this.#currentStepId).onBeforeNextStep()
				.then(() => {
					this.#breadcrumbs.next();

					this.#currentStepId = (
						this.#sequenceSteps.at(this.#sequenceSteps.indexOf(this.#currentStepId) + 1)
					);
					this.#updateContent();

					this.#buttons.next();
					this.#resolveButtonsEnableState();
				})
				.catch((error) => {
					this.#resolveButtonsEnableState();
					if (error)
					{
						console.error(error);
					}
				})
			;
		}
	}

	#back()
	{
		this.#cleanErrors();

		if (this.#isPreviousStepEnable())
		{
			this.#breadcrumbs.back();

			this.#currentStepId = this.#sequenceSteps.at(this.#sequenceSteps.indexOf(this.#currentStepId) - 1);
			this.#updateContent();

			this.#buttons.back();
			this.#resolveButtonsEnableState();
		}
	}

	#fastStart()
	{
		this.#cleanErrors();
		if (this.#isNextStepEnable())
		{
			this.#markButtonsOnBeforeNextStep();

			const data = {
				templateId: this.#templateId,
				signedDocumentType: this.#signedDocumentType,
				signedDocumentId: this.#signedDocumentId,
				startDuration: Math.round(Date.now() / 1000) - this.#startTime,
			};

			startWorkflowAction(data)
				.then(() => {
					this.#canExit = true;
					this.#next();
				})
				.catch((response) => {
					this.#errorNotifier.errors = response.errors;
					this.#errorNotifier.show();
					this.#resolveButtonsEnableState();
				})
			;
		}
	}

	#markButtonsOnBeforeNextStep()
	{
		this.#buttons.resolveWaitingState({ start: true, next: true });
		this.#buttons.resolveEnableState({ back: false });
	}

	#cleanErrors(): void
	{
		this.#errorNotifier.errors = [];
		this.#errorNotifier.clean();
	}

	#isNextStepEnable(): boolean
	{
		const index = this.#sequenceSteps.indexOf(this.#currentStepId);

		return (
			index !== -1
			&& Type.isStringFilled(this.#sequenceSteps.at(index + 1))
			&& this.#steps.get(this.#currentStepId).isNextEnabled()
		);
	}

	#isPreviousStepEnable(): boolean
	{
		const index = this.#sequenceSteps.indexOf(this.#currentStepId);

		return (
			index !== -1
			&& index - 1 >= 0
			&& Type.isStringFilled(this.#sequenceSteps.at(index - 1))
			&& this.#steps.get(this.#currentStepId).isBackEnabled()
		);
	}

	#exit()
	{
		if (BX.SidePanel.Instance.getSliderByWindow(window))
		{
			BX.SidePanel.Instance.getSliderByWindow(window).close();
		}
	}

	#composeData(
		config: SingleStartData,
	): Object<string, { breadcrumbs: BreadcrumbsItemData, step: Step, buttons: Array<Button> }>
	{
		const data = {
			recommendation: this.#getRecommendationData(config),
		};

		if (!config.isConstantsTuned)
		{
			data.constants = this.#getConstantsData(config);
		}

		if (config.hasParameters)
		{
			data.parameters = this.#getParametersData(config);
		}

		data.start = this.#getStartData(config);

		return data;
	}

	#getRecommendationData(config: SingleStartData): {}
	{
		const isFastStart = config.isConstantsTuned && !config.hasParameters;

		return {
			breadcrumbs: {
				id: 'recommendation',
				text: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_RECOMMENDATION'),
				active: true,
			},
			step: new RecommendationStep({
				name: config.name,
				recommendation: config.description,
				duration: config.duration,
			}),
			buttons: [
				Buttons.createBackButton(this.#exit.bind(this)),
				(
					isFastStart
						? Buttons.createStartButton(this.#fastStart.bind(this))
						: Buttons.createNextButton(this.#next.bind(this))
				),
			],
		};
	}

	#getConstantsData(config: SingleStartData): {}
	{
		return {
			breadcrumbs: {
				id: 'constants',
				text: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_CONSTANTS'),
				active: false,
			},
			step: new ConstantsStep({
				name: config.name,
				templateId: config.id,
				constants: config.constants,
				documentType: config.documentType,
				signedDocumentType: config.signedDocumentType,
				signedDocumentId: config.signedDocumentId,
			}),
			buttons: [
				Buttons.createBackButton(this.#back.bind(this)),
				(
					config.hasParameters
						? Buttons.createNextButton(this.#next.bind(this))
						: Buttons.createStartButton(this.#fastStart.bind(this))
				),
			],
		};
	}

	#getParametersData(config: SingleStartData): {}
	{
		return {
			breadcrumbs: {
				id: 'parameters',
				text: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_PARAMETERS'),
				active: false,
			},
			step: new ParametersStep({
				name: config.name,
				templateId: config.id,
				parameters: config.parameters,
				documentType: config.documentType,
				signedDocumentId: config.signedDocumentId,
				signedDocumentType: config.signedDocumentType,
			}),
			buttons: [
				Buttons.createBackButton(this.#back.bind(this)),
				Buttons.createStartButton(this.#next.bind(this)), // slow start
			],
		};
	}

	#getStartData(config: SingleStartData): {}
	{
		return {
			breadcrumbs: {
				id: 'start',
				text: Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_START'),
				active: false,
			},
			step: new SuccessStartStep({ name: config.name }),
			buttons: [],
		};
	}

	#subscribeOnSliderClose()
	{
		const slider = BX.SidePanel.Instance.getSliderByWindow(window);
		if (slider)
		{
			EventEmitter.subscribe(slider, 'SidePanel.Slider:onClose', (event) => {
				if (!this.#canExit)
				{
					const canExit = (
						[...this.#steps.values()].every((step) => (step ? step.canExit() : true))
					);

					if (!canExit)
					{
						event.getCompatData()[0].denyAction();
						if (!this.#isExitInProcess)
						{
							this.#isExitInProcess = true;
							showExitDialog(
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
				}
			});
		}
	}
}
