import {Dom, Type} from 'main.core';

import {ConfidentialitySelector} from './confidentialityselector';
import {WorkgroupForm} from './index';
import {Util} from './util';

export class Scrum
{
	constructor(
		params: {
			isScrumProject: boolean,
		}
	)
	{
		this.isScrumProject = params.isScrumProject;
	}

	makeAdditionalCustomizationForm()
	{
		if (this.isScrumProject)
		{
			this.createHiddenInputs();
			this.showScrumBlocks();
			if (!Type.isStringFilled(WorkgroupForm.getInstance().selectedConfidentialityType))
			{
				ConfidentialitySelector.unselectAll();
				ConfidentialitySelector.select('open');
				WorkgroupForm.getInstance().recalcForm({
					selectedConfidentialityType: 'open',
				});
			}

			const landingCheckbox = document.getElementById('GROUP_LANDING');
			if (landingCheckbox)
			{
				landingCheckbox.disabled = true;
				landingCheckbox.checked = false;
			}

			this.toggleFeatures(true);
		}
		else
		{
			this.removeHiddenInputs();
			this.hideScrumBlocks();

			const landingCheckbox = document.getElementById('GROUP_LANDING');
			if (landingCheckbox)
			{
				landingCheckbox.disabled = false;
			}

			this.toggleFeatures(false);
		}

		Util.recalcNameInput();
	}

	hideScrumBlocks()
	{
		document.querySelectorAll('.socialnetwork-group-create-ex__create--switch-scrum, .socialnetwork-group-create-ex__create--switch-nonscrum').forEach((scrumBlock) => {
			scrumBlock.classList.remove('--scrum');
		});

		const moderatorsBlock = document.getElementById('expandable-moderator-block');
		if (moderatorsBlock)
		{
			moderatorsBlock.classList.add('socialnetwork-group-create-ex__content-expandable');
		}

		const moderatorsSwitch = document.getElementById('GROUP_MODERATORS_PROJECT_switch');
		if (moderatorsSwitch)
		{
			moderatorsSwitch.classList.add('ui-ctl-file-link');
		}

		const ownerBlock = document.getElementById('GROUP_OWNER_block');
		if (ownerBlock)
		{
			ownerBlock.classList.remove('--space-bottom');
		}
 	}

	showScrumBlocks()
	{
		document.querySelectorAll('.socialnetwork-group-create-ex__create--switch-scrum, .socialnetwork-group-create-ex__create--switch-nonscrum').forEach((scrumBlock) => {
			scrumBlock.classList.add('--scrum');
		});

		const moderatorsBlock = document.getElementById('expandable-moderator-block');
		if (moderatorsBlock)
		{
			moderatorsBlock.classList.remove('socialnetwork-group-create-ex__content-expandable');
		}

		const moderatorsSwitch = document.getElementById('GROUP_MODERATORS_PROJECT_switch');
		if (moderatorsSwitch)
		{
			moderatorsSwitch.classList.remove('ui-ctl-file-link');
		}

		const ownerBlock = document.getElementById('GROUP_OWNER_block');
		if (ownerBlock)
		{
			ownerBlock.classList.add('--space-bottom');
		}
	}

	createHiddenInputs()
	{
		document.forms['sonet_group_create_popup_form'].appendChild(
			Dom.create('input', {
				attrs: {
					type: 'hidden',
					name: 'SCRUM_PROJECT',
					value: 'Y',
				}
			})
		);
	}

	removeHiddenInputs()
	{
		document.forms['sonet_group_create_popup_form'].querySelectorAll('input[name="SCRUM_PROJECT"]')
			.forEach((input) => {
				Dom.remove(input);
			})
		;
	}

	toggleFeatures(isScrum: boolean)
	{
		const featuresNode = document.querySelector('.socialnetwork-group-create-ex__project-instruments');
		if (featuresNode)
		{
			featuresNode.querySelectorAll('input[type="checkbox"][name="tasks_active"], input[type="checkbox"][name="calendar_active"]').forEach((featuresCheckboxNode) => {
				if (isScrum)
				{
					featuresCheckboxNode.disabled = true;
					featuresCheckboxNode.checked = true;

					featuresCheckboxNode.parentNode.insertBefore(Dom.create('input', {
						attrs: {
							type: 'hidden',
							name: featuresCheckboxNode.name,
							value: 'Y',
						}
					}), featuresCheckboxNode);
				}
				else
				{
					featuresCheckboxNode.disabled = false;

					document.forms['sonet_group_create_popup_form'].querySelectorAll(`input[type="hidden"][name="${featuresCheckboxNode.name}"]`)
						.forEach((hiddenInput) => {
							Dom.remove(hiddenInput);
						})
					;
				}
			});
		}
	}
}
