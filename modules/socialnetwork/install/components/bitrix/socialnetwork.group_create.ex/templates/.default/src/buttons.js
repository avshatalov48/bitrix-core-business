import { Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { ButtonManager } from 'ui.buttons';

import { WorkgroupForm } from './index';
import { FieldsManager } from './fields';

export class Buttons
{
	static cssClass = {
		hidden: 'socialnetwork-group-create-ex__button-invisible',
	};

	constructor()
	{
		this.submitButton = document.getElementById('sonet_group_create_popup_form_button_submit');
		if (!this.submitButton)
		{
			return;
		}

		this.submitButtonClickHandler = this.submitButtonClickHandler.bind(this);
		this.submitButton.addEventListener('click', this.submitButtonClickHandler);

		this.backButton = document.getElementById('sonet_group_create_popup_form_button_step_2_back');
		if (this.backButton)
		{
			this.backButton.addEventListener('click', (e) => {

				const button = ButtonManager.createFromNode(e.currentTarget);
				if (
					button
					&& button.isDisabled()
				)
				{
					return;
				}

				if (WorkgroupForm.getInstance().wizardManager.currentStep > 1)
				{
					WorkgroupForm.getInstance().wizardManager.currentStep--;
					if (
						WorkgroupForm.getInstance().wizardManager.currentStep === 3
						&& Object.entries(WorkgroupForm.getInstance().confidentialityTypes) <= 1
					) // skip confidentiality step
					{
						WorkgroupForm.getInstance().wizardManager.currentStep--;
					}

					WorkgroupForm.getInstance().wizardManager.showCurrentStep();
				}

				return e.preventDefault();
			});
		}

		this.cancelButton = document.getElementById('sonet_group_create_popup_form_button_step_2_cancel');

		if (this.cancelButton)
		{
			this.cancelButton.addEventListener('click', (e) => {

				const button = ButtonManager.createFromNode(e.currentTarget);
				if (
					button
					&& button.isDisabled()
				)
				{
					return;
				}

				const currentSlider = BX.SidePanel.Instance.getSliderByWindow(window);

				if (currentSlider)
				{
					const event = new BaseEvent({
						compatData: [ currentSlider.getEvent('onClose') ],
						data: currentSlider.getEvent('onClose'),
					});

					EventEmitter.emit(window.top, 'SidePanel.Slider:onClose', event);
				}
				else
				{
					const url = e.currentTarget.getAttribute('bx-url');
					if (Type.isStringFilled(url))
					{
						window.location = url;
					}
				}

				const event = new BaseEvent({
					compatData: [ false ],
					data: false,
				});

				EventEmitter.emit(window.top, 'BX.Bitrix24.PageSlider:close', event);

				EventEmitter.emit(window.top, 'onSonetIframeCancelClick');

				return e.preventDefault();
			})
		}
	}

	submitButtonClickHandler(e)
	{
		const button = ButtonManager.createFromNode(e.currentTarget);
		if (
			button
			&& button.isDisabled()
		)
		{
			return;
		}

		WorkgroupForm.getInstance().alertManager.hideAllAlerts();

		const errorDataList = FieldsManager.check().filter((errorData) => {
			return (
				Type.isPlainObject(errorData)
				&& Type.isStringFilled(errorData.message)
				&& Type.isDomNode(errorData.bindNode)
			);
		});

		if (errorDataList.length > 0)
		{
			errorDataList.forEach((errorData) => {
				FieldsManager.showError(errorData);
			});
		}
		else if (WorkgroupForm.getInstance().wizardManager.currentStep < WorkgroupForm.getInstance().wizardManager.stepsCount)
		{
			WorkgroupForm.getInstance().wizardManager.currentStep++;
			if (
				WorkgroupForm.getInstance().wizardManager.currentStep === 3
				&& Object.entries(WorkgroupForm.getInstance().confidentialityTypes) <= 1
			) // skip confidentiality step
			{
				WorkgroupForm.getInstance().wizardManager.currentStep++;
			}

			WorkgroupForm.getInstance().wizardManager.showCurrentStep();
		}
		else
		{
			const submitFunction = function(event) {
				WorkgroupForm.getInstance().submitForm(event)
			}.bind(WorkgroupForm.getInstance());

			submitFunction(e);
		}

		return e.preventDefault();
	}

	static showWaitSubmitButton(disable)
	{
		disable = !!disable;

		const buttonNode = document.getElementById('sonet_group_create_popup_form_button_submit');
		if (!buttonNode)
		{
			return;
		}

		const button = ButtonManager.createFromNode(buttonNode);

		if (disable)
		{
			if (button)
			{
				button.setWaiting(true);
			}
			buttonNode.removeEventListener('click', WorkgroupForm.getInstance().submitButtonClickHandler);
		}
		else
		{
			if (button)
			{
				button.setWaiting(false);
			}
			buttonNode.addEventListener('click', WorkgroupForm.getInstance().submitButtonClickHandler);
		}
	}

	static disableButton(buttonNode, disable)
	{
		if (!Type.isDomNode(buttonNode))
		{
			return;
		}

		const button = ButtonManager.createFromNode(buttonNode);
		if (!button)
		{
			return;
		}

		button.setDisabled(disable);
	}

	static showButton(buttonNode)
	{
		if (!Type.isDomNode(buttonNode))
		{
			return;
		}

		buttonNode.classList.remove(this.cssClass.hidden);
	}

	static hideButton(buttonNode)
	{
		if (!Type.isDomNode(buttonNode))
		{
			return;
		}

		buttonNode.classList.add(this.cssClass.hidden);
	}
}
