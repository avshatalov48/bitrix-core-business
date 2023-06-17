import {Guide} from "ui.tour";
import {Loc, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Settings} from "bizproc.local-settings";

export class CheckAutomationCrm
{
	static #IS_SHOWN_SUCCESS_AUTOMATION = 'success_automation_shown';
	static #SHOW_SUCCESS_AUTOMATION = 'show_success_automation';
	static #SHOW_CHECK_AUTOMATION = 'show_check_automation';

	static #SHOW_HOW_CHECK_TRIIGGER = 'show_how_check_trigger';
	static #SHOW_HOW_CHECK_ROBOT = 'show_how_check_robot';

	static #TRIGGER_ADDED = 'is_trigger_added';
	static #ROBOT_ADDED = 'is_robot_added';

	static startCheckAutomationTour(documentType: string, categoryId: number)
	{
		if (!this.#isCorrectDocumentType(documentType) || !Type.isNumber(categoryId))
		{
			return;
		}

		this.#subscribeOnStartCheckAutomationEvents(documentType, categoryId);
	}

	static #subscribeOnStartCheckAutomationEvents(documentType: string, categoryId: number)
	{
		const handlers = this.#getStartCheckAutomationHandlers();
		for (const eventName of Object.keys(handlers))
		{
			EventEmitter.subscribe(eventName, handlers[eventName]);
		}

		const slider = BX.SidePanel.Instance.getSliderByWindow(window);
		let options = {};
		if (slider)
		{
			const localSettings = new Settings('aut-guide-crm-check-automation');
			EventEmitter.subscribeOnce(slider, 'SidePanel.Slider:onCloseComplete', () => {
				for (const eventName of Object.keys(handlers))
				{
					EventEmitter.unsubscribe(eventName, handlers[eventName]);
				}

				if (
					localSettings.get(this.#SHOW_HOW_CHECK_TRIIGGER) === true
					|| localSettings.get(this.#SHOW_HOW_CHECK_ROBOT) === true
				)
				{
					this.#saveUserOption('document_type', documentType);
					this.#saveUserOption('category_id', categoryId);
					this.#saveUserOption(this.#SHOW_CHECK_AUTOMATION, 'Y');
					options = Object.assign(
						{
							document_type: documentType,
							category_id: categoryId,
						},
						localSettings.getAll()
					);
					this.#sendUserOption();
				}

				localSettings.deleteAll();
			});
		}

		const targetWindow = window.top;
		const showHowCheckAutomationGuide = () => {
			if (targetWindow.BX.SidePanel.Instance.getOpenSlidersCount() <= 0)
			{
				targetWindow.BX.Event.EventEmitter.unsubscribe(
					'SidePanel.Slider:onCloseComplete', showHowCheckAutomationGuide
				);

				targetWindow.BX.Runtime.loadExtension('bizproc.automation.guide')
					.then((exports) => {
						const {CrmCheckAutomationGuide} = exports;
						CrmCheckAutomationGuide.showHowCheckAutomationGuide(documentType, categoryId, options);
					})
				;

				return true;
			}

			return false;
		};

		targetWindow.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', showHowCheckAutomationGuide);
	}

	static #getStartCheckAutomationHandlers(): {}
	{
		const localSettings = new Settings('aut-guide-crm-check-automation');
		localSettings.set(this.#TRIGGER_ADDED, false);
		localSettings.set(this.#SHOW_HOW_CHECK_TRIIGGER, false);
		localSettings.set(this.#ROBOT_ADDED, false);
		localSettings.set(this.#SHOW_HOW_CHECK_ROBOT, false);

		const handlers = {};
		handlers['BX.Bizproc.Automation:TriggerManager:trigger:add'] = () => {
			localSettings.set(this.#TRIGGER_ADDED, true);
		};
		handlers['BX.Bizproc.Automation:Template:robot:add'] = () => {
			localSettings.set(this.#ROBOT_ADDED, true);
		};
		handlers['BX.Bizproc.Component.Automation.Component:onSuccessAutomationSave'] = (event: BaseEvent) => {
			const triggersCount = event.getData()['analyticsLabel']['triggers_count'];
			localSettings.set(
				this.#SHOW_HOW_CHECK_TRIIGGER,
				triggersCount > 0 && localSettings.get(this.#TRIGGER_ADDED) === true
			);

			const robotsCount = event.getData()['analyticsLabel']['robots_count'];
			localSettings.set(
				this.#SHOW_HOW_CHECK_ROBOT,
				robotsCount > 0 && localSettings.get(this.#ROBOT_ADDED) === true
			);
			localSettings.set(this.#TRIGGER_ADDED, false);
			localSettings.set(this.#ROBOT_ADDED, false);
		};

		return handlers;
	}

	static showHowCheckAutomationGuide(documentType: string, categoryId: number, options: Object<string, any>): void
	{
		if (
			this.#isSuccessAutomationStepShown(options)
			|| !this.#isTargetDocumentType(documentType, categoryId, options)
			|| this.#isNeedShowSuccessAutomationStep(options)
			|| this.#isNeedShowCheckAutomationStep(options)
		)
		{
			return;
		}

		const showTriggerGuide = (options[this.#SHOW_HOW_CHECK_TRIIGGER] === true);
		const showRobotGuide = (options[this.#SHOW_HOW_CHECK_ROBOT] === true);

		if (!showTriggerGuide && !showRobotGuide)
		{
			return;
		}

		const title =
			showTriggerGuide
				? Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_TRIGGER_TITLE')
				: Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_ROBOT_TITLE')
		;
		const text =
			showTriggerGuide
				? Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_TRIGGER_TEXT')
				: Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_ROBOT_TEXT')
		;

		// kanban or list
		const target =
			document.querySelector('.main-kanban-item')
			?? document.querySelector('.main-grid-row.main-grid-row-body:not(.main-grid-not-count) .main-grid-cell.main-grid-cell-left')
		;

		const guide = this.#getGuide({target, title, text});
		if (!this.#isTargetExist(guide.getCurrentStep().getTarget()))
		{
			return;
		}

		guide.showNextStep();
	}

	static showCheckAutomation(documentType: string, categoryId: number, options: Object<string, any>)
	{
		if (
			this.#isSuccessAutomationStepShown(options)
			|| !this.#isTargetDocumentType(documentType, categoryId, options)
			|| this.#isNeedShowSuccessAutomationStep(options)
			|| !this.#isNeedShowCheckAutomationStep(options)
		)
		{
			return;
		}

		this.#saveUserOption(this.#SHOW_CHECK_AUTOMATION, 'N');
		this.#saveUserOption(this.#SHOW_SUCCESS_AUTOMATION, 'Y');
		this.#sendUserOption()

		const guide = this.#getGuide({
			target: '[data-id="tab_automation"]',
			title: Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_CHECK_AUTOMATION_TITLE'),
			text: Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_CHECK_AUTOMATION_TEXT'),
			condition: {top: true, bottom: false, color: 'primary'},
		});

		if (
			!this.#isTargetExist(guide.getCurrentStep().getTarget())
			|| guide.getCurrentStep().getTarget().offsetTop > 0
		)
		{
			guide.getCurrentStep().setTarget('.main-buttons-item.main-buttons-item-more-default.main-buttons-item-more.--has-menu');
			if (!this.#isTargetExist(guide.getCurrentStep().getTarget()))
			{
				return;
			}
		}

		guide.showNextStep();
	}

	static showSuccessAutomation(documentType: string, categoryId: number, options: Object<string, any>)
	{
		if (
			this.#isSuccessAutomationStepShown(options)
			|| !this.#isTargetDocumentType(documentType, categoryId, options)
			|| !this.#isNeedShowSuccessAutomationStep(options)
		)
		{
			return;
		}

		// success trigger or robot
		let target =
			document.querySelector('.bizproc-automation-trigger-item.--complete')
			?? document.querySelector('.bizproc-automation-robot-container.--complete')
		;
		if (!this.#isTargetExist(target))
		{
			// trigger or robot
			target =
				document.querySelector('.bizproc-automation-trigger-item')
				?? document.querySelector('.bizproc-automation-robot-container')
			;

			if (!this.#isTargetExist(target))
			{
				return;
			}
		}

		this.#deleteUserOption();
		this.#saveUserOption(this.#IS_SHOWN_SUCCESS_AUTOMATION, 'Y');

		const guide = this.#getGuide({
			target,
			title: Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_SUCCESS_AUTOMATION_TITLE'),
			text: Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_SUCCESS_AUTOMATION_TEXT'),
			article: '6908975',
			position: 'top',
		});

		guide.showNextStep();
	}

	static #isSuccessAutomationStepShown(options: Object<string, any>): boolean
	{
		return options[this.#IS_SHOWN_SUCCESS_AUTOMATION] === 'Y';
	}

	static #isTargetDocumentType(documentType: string, categoryId: number, options: Object<string, any>): boolean
	{
		return (
			this.#isCorrectDocumentType(documentType)
			&& Type.isStringFilled(options['document_type'])
			&& options['document_type'] === documentType
			&& Number(options['category_id']) === Number(categoryId)
		);
	}

	static #isCorrectDocumentType(documentType: string): boolean
	{
		return (
			Type.isStringFilled(documentType)
			&& (
				['LEAD', 'DEAL', 'SMART_INVOICE', 'QUOTE', 'ORDER'].includes(documentType)
				|| documentType.startsWith('DYNAMIC_')
			)
		);
	}

	static #isNeedShowSuccessAutomationStep(options: Object<string, any>): boolean
	{
		return options[this.#SHOW_SUCCESS_AUTOMATION] === 'Y';
	}

	static #isNeedShowCheckAutomationStep(options: Object<string, any>): boolean
	{
		return options[this.#SHOW_CHECK_AUTOMATION] === 'Y';
	}

	static #saveUserOption(key, value)
	{
		BX.userOptions.save('bizproc.automation.guide', 'crm_check_automation', key, value, false);
	}

	static #sendUserOption()
	{
		BX.userOptions.send(null);
	}

	static #deleteUserOption()
	{
		BX.userOptions.del('bizproc.automation.guide', 'crm_check_automation');
	}

	// region guide
	static #getGuide(options: {
		target: string | Element,
		title: string,
		text: string,
		position?: string,
		condition?: Object,
		article?: string,
	}): Guide
	{
		return new Guide({
			steps: [
				{
					target: options.target,
					title: options.title,
					text: options.text,
					position: options.position | 'bottom',
					condition: Type.isPlainObject(options.condition) ? options.condition : null,
					article: options.article ?? null,
				}
			],
			onEvents: true,
		});
	}

	static #isTargetExist(target): boolean
	{
		return Type.isElementNode(target);
	}

	// endregion
}
