import { Dom, Loc, Reflection, Tag, Text, Type, ajax, Runtime, Uri, Event } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { Button, ButtonSize, ButtonColor } from 'ui.buttons';
import { MessageBox } from 'ui.dialogs.messagebox';
import 'ui.tooltip';
import 'ui.icons.b24';

import './css/style.css';

const namespace = Reflection.namespace('BX.Lists.Component');

const HTML_ELEMENT_ID = 'lists-element-creation-guide';
const BP_STATE_FORM_NAME = 'lists_element_creation_guide_bp';
const BP_STATE_CONSTANTS_FORM_NAME = 'lists_element_creation_guide_bp_constants';
const AJAX_COMPONENT = 'bitrix:lists.element.creation_guide';

const STEPS = Object.freeze({
	DESCRIPTION: 'description',
	CONSTANTS: 'constants',
	FIELDS: 'fields',
});

type ComponentData = {
	name: string,
	description: string,
	duration: ?number,
	signedParameters: string,
	bpTemplateIds: [],
	hasFieldsToShow: boolean,
	hasStatesToTuning: boolean,
	canUserTuningStates: boolean,
};

type Step = {
	step: string,
	contentNode: HTMLElement,
	progressBarNode: HTMLElement,
};

class ElementCreationGuide
{
	#steps: Array<Step> = [];

	#name: string;
	#description: string;
	#duration: ?number = null;
	#signedParameters: string;
	#templateIds: [] = [];

	#currentStep: ?string;
	#startTime: number;
	#descriptionNode: HTMLElement;
	#durationNode: HTMLElement;
	#difference: number;
	#canUserTuningStates: boolean;
	#isAdminLoaded: boolean = false;
	#isLoading: boolean = false;
	#stepsEnterTime: Map<string, number> = new Map();
	#formData: FormData;
	#messageBox: MessageBox;
	#canClose: boolean = false;

	constructor(props: ComponentData)
	{
		if (!Type.isStringFilled(props.signedParameters))
		{
			throw new TypeError('signedParameters must be filled string');
		}
		this.#signedParameters = props.signedParameters;

		this.#name = Type.isString(props.name) ? props.name : '';
		this.#description = Type.isString(props.description) ? props.description : '';

		if (Type.isInteger(props.duration) && props.duration >= 0)
		{
			this.#duration = props.duration;
		}

		if (Type.isArrayFilled(props.bpTemplateIds))
		{
			this.#templateIds = props.bpTemplateIds;
		}

		this.#canUserTuningStates = Type.isBoolean(props.canUserTuningStates) ? props.canUserTuningStates : false;

		this.#startTime = Math.round(Date.now() / 1000);

		this.#setCurrentStep(STEPS.DESCRIPTION);
		this.#fillSteps(props);
		this.#toggleButtons();
		this.#renderProgressBar();
		this.#renderFirstStep();

		Event.EventEmitter.subscribe('SidePanel.Slider:onClose', (event) => {
			if (event.target.getWindow() === window && this.#isChangedFormData() && !this.#canClose)
			{
				event.getCompatData()[0].denyAction();
				if (!this.#messageBox?.getPopupWindow().isShown())
				{
					this.#showConfirmDialog(event.target);
				}
			}
		});
	}

	#setCurrentStep(step: ?string): void
	{
		this.#currentStep = step;

		if (this.#currentStep === STEPS.DESCRIPTION)
		{
			this.#stepsEnterTime.set(STEPS.DESCRIPTION, Date.now());
		}
		else
		{
			if (this.#stepsEnterTime.has(STEPS.DESCRIPTION))
			{
				const diffTime = Date.now() - this.#stepsEnterTime.get(STEPS.DESCRIPTION);

				Runtime.loadExtension('ui.analytics')
					.then(({ sendData }) => {
						sendData({
							tool: 'automation',
							category: 'bizproc_operations',
							event: 'process_instructions_read',
							p1: this.#name,
							p4: Math.round(diffTime / 1000),
						});
					})
					.catch(() => {})
				;
			}

			this.#stepsEnterTime.delete(STEPS.DESCRIPTION);
		}
	}

	#fillSteps(props)
	{
		const contentNode = document.querySelectorAll('.list-el-cg__content >.list-el-cg__content-body');

		const showBPConstantsStep = Type.isBoolean(props.hasStatesToTuning) ? props.hasStatesToTuning : false;
		const showFieldsStep = Type.isBoolean(props.hasFieldsToShow) ? props.hasFieldsToShow : false;

		this.#steps.push({
			step: STEPS.DESCRIPTION,
			contentNode: contentNode.item(0),
			progressBarNode: null,
		});

		if (showBPConstantsStep)
		{
			this.#steps.push({
				step: STEPS.CONSTANTS,
				contentNode: contentNode.item(1),
				progressBarNode: null,
			});
		}

		if (showFieldsStep)
		{
			this.#steps.push({
				step: STEPS.FIELDS,
				contentNode: contentNode.item(2),
				progressBarNode: null,
			});
		}
	}

	#toggleButtons()
	{
		const backButton = document.getElementById(`${HTML_ELEMENT_ID}-back-button`);
		const nextButton = document.getElementById(`${HTML_ELEMENT_ID}-next-button`);
		const createButton = document.getElementById(`${HTML_ELEMENT_ID}-create-button`);

		this.#removeNotTunedConstantsHint(createButton);
		this.#removeNotTunedConstantsHint(nextButton);

		if (this.#isFirstStep())
		{
			const showNextStep = this.#steps.length > 1;
			this.#hideButton(showNextStep ? createButton : nextButton);
			this.#showButton(showNextStep ? nextButton : createButton);
		}
		else if (this.#isLastStep())
		{
			this.#hideButton(nextButton);
			this.#showButton(createButton);
		}
		else
		{
			this.#hideButton(createButton);
			this.#showButton(nextButton);
		}

		if (this.#currentStep === STEPS.CONSTANTS && !this.#canUserTuningStates)
		{
			this.#disableButton(createButton);
			this.#disableButton(nextButton);
			this.#addNotTunedConstantsHint(createButton);
			this.#addNotTunedConstantsHint(nextButton);
		}

		setTimeout(() => {
			this.#removeWaitFromButton(backButton);
			this.#removeWaitFromButton(nextButton);
			this.#removeWaitFromButton(createButton);
		}, 100);
	}

	#isFirstStep(): boolean
	{
		return this.#currentStep === STEPS.DESCRIPTION;
	}

	#isLastStep(): boolean
	{
		return (
			(this.#currentStep === STEPS.DESCRIPTION && this.#steps.length === 1)
			|| (this.#currentStep === STEPS.CONSTANTS && this.#steps.length === 2)
			|| (this.#currentStep === STEPS.FIELDS)
		);
	}

	#hideButton(button)
	{
		if (Type.isDomNode(button))
		{
			Dom.addClass(button, ['--hidden']);
			this.#disableButton(button);
		}
	}

	#disableButton(button)
	{
		if (Type.isDomNode(button))
		{
			Dom.addClass(button, ['ui-btn-disabled']);
			Dom.attr(button, 'disabled', 'disabled');
		}
	}

	#removeWaitFromButton(button)
	{
		if (Type.isDomNode(button))
		{
			Dom.removeClass(button, 'ui-btn-wait');
		}
	}

	#setWaitToButton(button)
	{
		if (Type.isDomNode(button))
		{
			Dom.addClass(button, 'ui-btn-wait');
		}
	}

	#showButton(button)
	{
		if (Type.isDomNode(button))
		{
			Dom.removeClass(button, ['--hidden']);
			this.#enableButton(button);
		}
	}

	#enableButton(button)
	{
		if (Type.isDomNode(button))
		{
			Dom.removeClass(button, ['ui-btn-disabled']);
			Dom.attr(button, 'disabled', null);
		}
	}

	#renderProgressBar(): void
	{
		const container = document.getElementById(`${HTML_ELEMENT_ID}-breadcrumbs`);
		if (!container)
		{
			return;
		}

		const { step0, step1, step2 } = Tag.render`
			<div>
				<div class="list-el-cg__breadcrumbs-item --active" ref="step0">
					<span>${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_STEP_RECOMMENDATION'))}</span>
					<span class="ui-icon-set --chevron-right"></span>
				</div>
				<div class="list-el-cg__breadcrumbs-item" ref="step1">
					<span>${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_STEP_CONSTANTS'))}</span>
					<span class="ui-icon-set --chevron-right"></span>
				</div>
				<div class="list-el-cg__breadcrumbs-item" ref="step2">
					<span>${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_STEP_FIELDS'))}</span>
					<span class="ui-icon-set --chevron-right"></span>
				</div>
			</div>
		`;

		this.#steps[0].progressBarNode = step0;
		Dom.append(step0, container);

		const constantsStep = this.#steps.find((step) => step.step === STEPS.CONSTANTS);
		if (constantsStep)
		{
			constantsStep.progressBarNode = step1;
			Dom.append(step1, container);
		}

		const fieldsStep = this.#steps.find((step) => step.step === STEPS.FIELDS);
		if (fieldsStep)
		{
			fieldsStep.progressBarNode = step2;
			Dom.append(step2, container);
		}
	}

	#renderFirstStep()
	{
		const container = document.getElementById(`${HTML_ELEMENT_ID}-container`);
		const contentNode = this.#steps[0].contentNode;
		if (container && contentNode)
		{
			const description = this.#renderDescription();
			this.#descriptionNode = description;
			Dom.append(description, contentNode);

			const expandNode = this.#renderExpandDescriptionNode();
			Dom.append(expandNode, contentNode);

			this.#durationNode = this.#renderDuration();
			Dom.append(this.#durationNode, container);

			const slider = document.querySelector('.ui-page-slider-workarea-content-padding');
			const difference = slider ? (slider.offsetHeight - window.innerHeight) : 0;
			this.#difference = difference;
			if (difference > 0)
			{
				this.#toggleDescription({ target: expandNode });
			}
			else
			{
				Dom.remove(expandNode);
			}
		}
	}

	#renderDescription(): HTMLElement
	{
		if (Type.isStringFilled(this.#description))
		{
			return Tag.render`
				<div class="list-el-cg__content-wrapper">
					${BX.util.nl2br(this.#description)}
				</div>
			`;
		}

		return Tag.render`
			<div class="list-el-cg__content-wrapper">
				<span class="list-el-cg__text-empty">${Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EMPTY_DESCRIPTION')}</span>
			</div>
		`;
	}

	#renderExpandDescriptionNode(): HTMLElement
	{
		return Tag.render`
			<div class="list-el-cg__content-open" onclick="${this.#toggleDescription.bind(this)}">
				${Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXPAND_DESCRIPTION')}
			</div>
		`;
	}

	#toggleDescription(event)
	{
		const target = event.target;
		if (target && this.#difference > 0)
		{
			Dom.clean(target);
			if (Dom.hasClass(this.#descriptionNode, '--hide'))
			{
				target.innerText = Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_COLLAPSE_DESCRIPTION');
				Dom.style(this.#descriptionNode, 'height', `${this.#descriptionNode.scrollHeight}px`);
			}
			else
			{
				target.innerText = Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXPAND_DESCRIPTION');
				Dom.style(
					this.#descriptionNode,
					'height',
					`${this.#descriptionNode.offsetHeight - this.#difference}px`,
				);
			}

			Dom.toggleClass(this.#descriptionNode, ['--hide']);
		}
	}

	#renderDuration(): HTMLElement
	{
		if (Type.isNil(this.#duration))
		{
			return Tag.render`
				<div class="list-el-cg__informer">
					<div class="list-el-cg__informer-header">
						<div class="list-el-cg__informer-title">
							${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_TITLE'))}
						</div>
						<div class="list-el-cg__informer-time">
							<span class="list-el-cg__text-empty">${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EMPTY_DURATION'))}</span>
						</div>
					</div>
					<div class="list-el-cg__informer-message">${Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_UNDEFINED_DESCRIPTION')}</div>
					<div class="list-el-cg__informer-bottom"></div>
				</div>
			`;
		}

		let formattedDuration = Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_ZERO_DURATION');

		if (this.#duration > 0)
		{
			formattedDuration = DateTimeFormat.format(
				[['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']],
				0,
				this.#duration,
			);
		}

		return Tag.render`
			<div class="list-el-cg__informer">
				<div class="list-el-cg__informer-header">
					<div class="list-el-cg__informer-title">
						${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_TITLE'))}
					</div>
					<div class="list-el-cg__informer-time">
						<span>${Text.encode(formattedDuration)}</span>
						<div class="ui-icon-set --time-picker"></div>
					</div>
				</div>
				<div class="list-el-cg__informer-message">${Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_DESCRIPTION')}</div>
				<div class="list-el-cg__informer-bottom">
					<a
						class="list-el-cg__link" href="#"
						onclick="${this.#handleDurationHintClick}"
					>${Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_HINT')}
					</a>
				</div>
			</div>
		`;
	}

	#handleDurationHintClick(event)
	{
		event.preventDefault();

		const ARTICLE_ID = '18783714';

		const helper = Reflection.getClass('top.BX.Helper');

		if (helper)
		{
			helper.show(`redirect=detail&code=${ARTICLE_ID}`);
		}
	}

	next()
	{
		if (this.#isLoading || this.#isLastStep())
		{
			return;
		}

		if (!this.#formData)
		{
			const form = document.forms.form_lists_element_creation_guide_element;
			this.#formData = form ? new FormData(form) : new FormData();
			this.#appendSectionFormData(this.#formData);
			this.#appendBPFormData(this.#formData);
		}

		const currentStepIndex = this.#steps.findIndex((step) => step.step === this.#currentStep);

		const currentStep = this.#steps[currentStepIndex];
		const nextStep = this.#steps[currentStepIndex + 1];

		const changeStep = () => {
			Dom.toggleClass(currentStep.progressBarNode, ['--active', '--complete']);
			Dom.addClass(nextStep.progressBarNode, '--active');

			this.#cleanErrors();

			Dom.addClass(currentStep.contentNode, '--hidden');
			Dom.removeClass(nextStep.contentNode, '--hidden');

			if (this.#currentStep === STEPS.DESCRIPTION)
			{
				Dom.addClass(this.#durationNode, '--hidden');
			}

			this.#setCurrentStep(nextStep.step);
			this.#toggleButtons();
		};

		if (currentStep.step === STEPS.CONSTANTS && this.#canUserTuningStates)
		{
			this.#startLoading();
			this.#setAllConstants()
				.then(() => {
					changeStep();
				})
				.catch(() => {
					this.#toggleButtons();
				})
				.finally(this.#finishLoading.bind(this))
			;

			return;
		}

		if (nextStep.step === STEPS.CONSTANTS && !this.#canUserTuningStates && !this.#isAdminLoaded)
		{
			this.#startLoading();
			this.#loadAdminList()
				.then(() => {})
				.catch(() => {})
				.finally(() => {
					this.#isAdminLoaded = true;
					this.#finishLoading();
					changeStep();
				})
			;

			return;
		}

		changeStep();
	}

	#loadAdminList(): Promise
	{
		return new Promise((resolve, reject) => {
			const constantStep = this.#steps.find((step) => step.step === STEPS.CONSTANTS);
			ajax.runComponentAction(
				AJAX_COMPONENT,
				'getListAdmin',
				{ json: { signedParameters: this.#signedParameters } },
			)
				.then(({ data }) => {
					if (Type.isArrayFilled(data?.admins))
					{
						Dom.append(this.#renderAdminList(data.admins, data.canNotify), constantStep.contentNode);
					}

					resolve();
				})
				.catch(reject)
			;
		});
	}

	#renderAdminList(admins: [], canNotify: boolean = false): HTMLElement
	{
		return Tag.render`
			<div>
				<div class="list-el-cg__const-desc">
					${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY_ADMIN'))}
				</div>
				<div class="list-el-cg__const-title">
					${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY'))}
				</div>
				${admins.map((admin) => {
					let button = null;
					if (canNotify)
					{
						button = new Button({
							text: Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY_BUTTON'),
							size: ButtonSize.MEDIUM,
							color: ButtonColor.PRIMARY,
							onclick: this.#notifyAdmin.bind(this, admin),
						});
					}

					return Tag.render`
						<div class="list-el-cg__const-box">
							<div class="list-el-cg__const-user">
								<div
									class="ui-icon ui-icon-common-user list-el-cg__const-icon"
									bx-tooltip-user-id="${admin.id}"								
								>
									<i style="background-image: url('${admin.img ? encodeURI(Text.encode(admin.img)) : '/bitrix/js/ui/icons/b24/images/ui-user.svg?v2'}');"></i>
								</div>
								<span class="list-el-cg__const-name">${Text.encode(admin.name)}</span>
							</div>						
							<div>
								${button?.render()}
							</div>
						</div>
					`;
					})
				}
			</div>
		`;
	}

	#notifyAdmin(admin: {}, button: Button)
	{
		button.setWaiting(true);

		ajax.runComponentAction(
			AJAX_COMPONENT,
			'notifyAdmin',
			{
				json: {
					signedParameters: this.#signedParameters,
					adminId: admin.id,
				},
			},
		)
			.then(({ data }) => {
				if (data.success === true)
				{
					Dom.replace(
						button.getContainer(),
						Tag.render`
							<span class="list-el-cg__const-success-text">
								${Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY_SUCCESS'))}
							</span>
						`,
					);
				}

				button.setWaiting(false);
			})
			.catch(() => {
				button.setWaiting(false);
			})
		;
	}

	#setAllConstants(): Promise
	{
		return new Promise((resolve, reject) => {
			const formData = new FormData();
			this.#appendBPFormData(formData, true);

			this.#setConstants(formData)
				.then(resolve)
				.catch(({ errors }) => {
					if (Array.isArray(errors))
					{
						this.#showErrors(errors);
					}

					reject();
				})
			;
		});
	}

	#setConstants(formData: FormData): Promise
	{
		return new Promise((resolve, reject) => {
			formData.set('signedParameters', this.#signedParameters);

			ajax.runComponentAction(AJAX_COMPONENT, 'setConstants', { data: formData })
				.then(resolve)
				.catch(reject)
			;
		});
	}

	back()
	{
		if (this.#isFirstStep())
		{
			if (Reflection.getClass('BX.SidePanel') && BX.SidePanel.Instance.getSliderByWindow(window))
			{
				BX.SidePanel.Instance.getSliderByWindow(window).close(false);

				return;
			}

			this.#setCurrentStep();

			return;
		}

		const currentStepIndex = this.#steps.findIndex((step) => step.step === this.#currentStep);
		const currentStep = this.#steps[currentStepIndex];
		const previousStep = this.#steps[currentStepIndex - 1];

		Dom.removeClass(currentStep.progressBarNode, '--active');
		Dom.toggleClass(previousStep.progressBarNode, ['--active', '--complete']);

		this.#cleanErrors();

		Dom.addClass(currentStep.contentNode, '--hidden');
		Dom.removeClass(previousStep.contentNode, '--hidden');

		if (previousStep.step === STEPS.DESCRIPTION)
		{
			Dom.removeClass(this.#durationNode, '--hidden');
		}

		this.#setCurrentStep(previousStep.step);
		this.#toggleButtons();
	}

	async create()
	{
		if (
			this.#isLoading
			|| !this.#isLastStep()
			|| (this.#currentStep === STEPS.CONSTANTS && !this.#canUserTuningStates)
		)
		{
			return;
		}

		if (this.#currentStep === STEPS.CONSTANTS)
		{
			this.#startLoading();

			let hasErrors = false;
			await this.#setAllConstants()
				.catch(() => {
					this.#toggleButtons();
					hasErrors = true;
				})
			;

			this.#finishLoading();
			if (hasErrors)
			{
				return;
			}
		}

		this.#startLoading();
		this.#createElement()
			.then(({ data }) => {
				if (Reflection.getClass('BX.SidePanel') && BX.SidePanel.Instance.getSliderByWindow(window))
				{
					this.#canClose = true;
					BX.SidePanel.Instance.getSliderByWindow(window).close(false);
					this.#showSuccessNotification(data.elementUrl);
				}
				this.#sendCreationAnalytics();
			})
			.catch((error) => {
				this.#toggleButtons();
				this.#sendCreationAnalytics(error);
			})
			.finally(this.#finishLoading.bind(this))
		;
	}

	#showSuccessNotification(href)
	{
		const topRuntime: Runtime = Reflection.getClass('top.BX.Runtime');
		if (topRuntime)
		{
			topRuntime.loadExtension('ui.notification')
				.then(() => {
					if (Reflection.getClass('top.BX.UI.Notification.Center'))
					{
						const actions = [];
						if (Type.isStringFilled(href))
						{
							actions.push({
								href,
								title: Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_SUCCESS_CREATE_SEE'),
							});
						}

						top.BX.UI.Notification.Center.notify({
							content: Text.encode(Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_SUCCESS_CREATE')),
							actions,
						});
					}
				})
				.catch(() => {})
			;
		}
	}

	saveConstants(templateId: number, button: HTMLButtonElement)
	{
		if (!this.#templateIds.includes(templateId) || !Type.isDomNode(button) || this.#isLoading)
		{
			return;
		}

		this.#setWaitToButton(button);

		const formData = new FormData();
		this.#appendStateFormData(formData, `form_${BP_STATE_CONSTANTS_FORM_NAME}_${templateId}`);
		formData.append('templateIds[]', templateId);

		const errorsNode = document.getElementById(`${HTML_ELEMENT_ID}-constants-${templateId}-errors`);

		this.#startLoading();
		this.#setConstants(formData)
			.then(() => {
				if (errorsNode)
				{
					this.#cleanErrors(errorsNode);
				}
			})
			.catch(({ errors }) => {
				if (Type.isArrayFilled(errors) && errorsNode)
				{
					this.#showErrors(errors, errorsNode);
				}
			})
			.finally(() => {
				this.#finishLoading();
				this.#removeWaitFromButton(button);
			})
		;
	}

	#createElement(): Promise
	{
		return new Promise((resolve, reject) => {
			const form: HTMLFormElement = document.forms.form_lists_element_creation_guide_element;
			const formData: FormData = form ? new FormData(form) : new FormData();

			this.#appendSectionFormData(formData);
			this.#appendBPFormData(formData);

			formData.set('signedParameters', this.#signedParameters);
			formData.set('time', Math.round(Date.now() / 1000) - this.#startTime);

			ajax.runComponentAction(
				AJAX_COMPONENT,
				'create',
				{ data: formData },
			)
				.then(resolve)
				.catch(({ errors }) => {
					if (Array.isArray(errors))
					{
						this.#showErrors(errors);
					}

					reject(new Error(errors[0].message));
				})
			;
		});
	}

	#appendSectionFormData(formData: FormData)
	{
		const form: HTMLFormElement = document.forms.form_lists_element_creation_guide_section;
		if (form)
		{
			formData.set('IBLOCK_SECTION_ID', new FormData(form).get('IBLOCK_SECTION_ID'));
		}
	}

	#appendBPFormData(formData: FormData, isConstantsForms: boolean = false)
	{
		this.#templateIds.forEach((id) => {
			const formId = `form_${isConstantsForms ? BP_STATE_CONSTANTS_FORM_NAME : BP_STATE_FORM_NAME}_${id}`;
			if (document.forms[formId])
			{
				this.#appendStateFormData(formData, formId);
				formData.append('templateIds[]', id);
			}
		});
	}

	#appendStateFormData(formData: FormData, formId: number)
	{
		const form = document.forms[formId];
		if (form)
		{
			for (const [key, value] of (new FormData(form)).entries())
			{
				if (key !== 'sessid')
				{
					formData.append(key, value);
				}
			}
		}
	}

	#showErrors(errors: [], toNode: HTMLElement = null)
	{
		this.#cleanErrors(toNode);

		const errorsNode = Type.isDomNode(toNode) ? toNode : document.getElementById(`${HTML_ELEMENT_ID}-errors`);
		if (errorsNode)
		{
			let message = '';
			errors.forEach((error) => {
				if (error.message)
				{
					message += Text.encode(error.message);
					message += '<br/>';
				}
			});

			Dom.append(
				Tag.render`
					<div class="ui-alert ui-alert-danger">
						<span class="ui-alert-message">${message}</span>
					</div>
				`,
				errorsNode,
			);
			BX.scrollToNode(errorsNode);
		}
	}

	#cleanErrors(fromNode: HTMLElement = null)
	{
		if (Type.isDomNode(fromNode))
		{
			Dom.clean(fromNode);

			return;
		}

		const errorsNode = document.getElementById(`${HTML_ELEMENT_ID}-errors`);
		if (errorsNode)
		{
			Dom.clean(errorsNode);
		}

		this.#templateIds.forEach((templateId) => {
			const node = document.getElementById(`${HTML_ELEMENT_ID}-constants-${templateId}-errors`);
			if (node)
			{
				Dom.clean(node);
			}
		});
	}

	#startLoading()
	{
		this.#isLoading = true;
		this.#disableAllButtons();
	}

	#disableAllButtons()
	{
		for (const button of document.getElementsByClassName('ui-btn'))
		{
			this.#disableButton(button);
		}
	}

	#finishLoading()
	{
		this.#isLoading = false;
		this.#enableAllButtons();
	}

	#enableAllButtons()
	{
		for (const button of document.getElementsByClassName('ui-btn'))
		{
			this.#enableButton(button);
		}
	}

	#addNotTunedConstantsHint(button: HTMLElement)
	{
		if (Type.isDomNode(button))
		{
			Dom.attr(button, 'title', Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_HINT'));
		}
	}

	#removeNotTunedConstantsHint(button: HTMLElement)
	{
		if (Type.isDomNode(button))
		{
			Dom.attr(button, 'title', null);
		}
	}

	#sendCreationAnalytics(error?: Error)
	{
		Runtime.loadExtension('ui.analytics')
			.then(({ sendData }) => {
				sendData({
					tool: 'automation',
					category: 'bizproc_operations',
					event: 'process_run',
					type: 'run',
					c_section: this.#getAnalyticsSection(),
					p1: this.#name,
					status: error ? 'error' : 'success',
				});
			})
			.catch(() => {})
		;
	}

	#getAnalyticsSection(): string
	{
		return ((new Uri(window.location.href)).getQueryParam('analyticsSection')) || 'bizproc';
	}

	#isChangedFormData(): boolean
	{
		if (!this.#formData)
		{
			return false;
		}

		const form: HTMLFormElement = document.forms.form_lists_element_creation_guide_element;
		const formData = form ? new FormData(form) : new FormData();
		this.#appendSectionFormData(formData);
		this.#appendBPFormData(formData);

		const originFormData = Object.fromEntries(this.#formData.entries());
		for (const [key, value] of formData.entries())
		{
			if (Type.isFile(value))
			{
				if (!this.checkEqualFileField(value, originFormData[key]))
				{
					return true;
				}
			}
			else if (value !== originFormData[key])
			{
				return true;
			}
		}

		return false;
	}

	checkEqualFileField(fileFieldA: File, fileFieldB: File): boolean
	{
		if (!fileFieldB)
		{
			return false;
		}

		return fileFieldA.name === fileFieldB.name;
	}

	#showConfirmDialog(slider)
	{
		this.#messageBox = MessageBox.confirm(
			Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_DESCRIPTION'),
			Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_TITLE'),
			() => {
				this.#canClose = true;
				slider.close();
			},
			Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_CONFIRM'),
			() => {
				this.#messageBox.close();
				this.#messageBox = null;
			},
			Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_CANCEL'),
		);
	}
}

namespace.ElementCreationGuide = ElementCreationGuide;
