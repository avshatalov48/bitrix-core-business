import { Type } from 'main.core';
import { WorkgroupForm } from './index';
import { Buttons } from './buttons';

export class Wizard
{
	static cssClass = {
		step1Backgroud: 'socialnetwork-group-create-ex__background-gif',
		breadcrumbsContainer: 'socialnetwork-group-create-ex__breadcrumbs',
		breadcrumbsItem: 'socialnetwork-group-create-ex__breadcrumbs-item',
		bodyContainer: 'socialnetwork-group-create-ex__content',
		bodyItem: 'socialnetwork-group-create-ex__content-body',
		activeBodyItem: '--active',
		activeBreadcrumbsItem: '--active',
	};

	static getFirstStepNumber()
	{
		return (Object.entries(WorkgroupForm.getInstance().projectTypes).length > 1 ? 1 : 2);
	}

	constructor(
		params: {
			currentStep: number,
			stepsCount: number,
		}
	)
	{
		this.processedStep = 0;
		this.currentStep = params.currentStep;
		this.stepsCount = params.stepsCount;

		this.step1BackgroudNode = document.querySelector(`.${Wizard.cssClass.step1Backgroud}`);
		this.bodyContainer = document.querySelector(`.${Wizard.cssClass.bodyContainer}`);
		this.breadcrumbsContainer = document.querySelector(`.${Wizard.cssClass.breadcrumbsContainer}`);
	}

	showCurrentStep()
	{
		if (Type.isDomNode(this.bodyContainer))
		{
			this.bodyContainer.querySelectorAll(`.${Wizard.cssClass.bodyItem}`).forEach((bodyItem) => {
				if (bodyItem.classList.contains(`--step-${this.currentStep}`))
				{
					bodyItem.classList.add(Wizard.cssClass.activeBodyItem)
				}
				else
				{
					bodyItem.classList.remove(Wizard.cssClass.activeBodyItem);
				}
			});
		}

		if (Type.isDomNode(this.breadcrumbsContainer))
		{
			this.breadcrumbsContainer.querySelectorAll(`.${Wizard.cssClass.breadcrumbsItem}`).forEach((breadcrumbsItem) => {
				if (breadcrumbsItem.classList.contains(`--step-${this.currentStep}`))
				{
					breadcrumbsItem.classList.add(Wizard.cssClass.activeBreadcrumbsItem)
				}
				else
				{
					breadcrumbsItem.classList.remove(Wizard.cssClass.activeBreadcrumbsItem);
				}
			});
		}

		if (
			this.currentStep === Wizard.getFirstStepNumber()
			|| this.currentStep <= this.processedStep + 1
		)
		{
			Buttons.hideButton(WorkgroupForm.getInstance().buttonsInstance.backButton);
		}
		else
		{
			if (Type.isDomNode(this.step1BackgroudNode))
			{
				this.step1BackgroudNode.classList.add(`--stop`);
			}
			Buttons.showButton(WorkgroupForm.getInstance().buttonsInstance.backButton);
		}

	}

	setProjectType(projectType)
	{
		if (Type.isDomNode(this.step1BackgroudNode))
		{
			[ 'project', 'scrum', 'group' ].forEach(projectType => {
				this.step1BackgroudNode.classList.remove(`--${projectType}`);
			})
			this.step1BackgroudNode.classList.remove('--stop');
			this.step1BackgroudNode.classList.add(`--${projectType}`);
		}
	}

	recalcAfterSubmit(params)
	{
		const processedStep = (Type.isStringFilled(params.processedStep) ? params.processedStep : '');
		const createdGroupId = parseInt(!Type.isUndefined(params.createdGroupId) ? params.createdGroupId : 0);
		const tabInputNode = document.getElementById('TAB');
		const tabGroupIdNode = document.getElementById('SONET_GROUP_ID');

		if (
			!tabInputNode
			|| !Type.isStringFilled(processedStep)
			|| createdGroupId <= 0
		)
		{
			return;
		}

		tabGroupIdNode.value = createdGroupId;

		if (processedStep === 'create')
		{
			this.processedStep = 1;
			tabInputNode.value = 'edit';
		}
		else if (processedStep === 'edit')
		{
			this.processedStep = 3;
			tabInputNode.value = 'invite';
			this.bodyContainer.querySelectorAll('.socialnetwork-group-create-ex__create--switch-notinviteonly').forEach((selector) => {
				selector.classList.add('--inviteonly');
			});
		}

		this.showCurrentStep();
	}
}
