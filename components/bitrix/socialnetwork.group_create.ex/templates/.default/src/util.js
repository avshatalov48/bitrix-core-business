import {Loc, Type, Dom, Tag} from 'main.core';

import {WorkgroupForm} from './index';

export class Util
{
	static cssClass = {
		selectorActive: '--active',
		selectorDisabled: '--disabled',
	};

	static initExpandSwitches()
	{
		const expandSwitchers = document.querySelectorAll('[data-role="socialnetwork-group-create-ex__expandable"]');

		expandSwitchers.forEach((switcher) => {

			switcher.addEventListener('click', (e) => {

				const targetId = e.currentTarget.getAttribute('for');
				const target = document.getElementById(targetId);
				const switcherWrapper = target.firstElementChild;

				if (target.offsetHeight === 0)
				{
					target.style.height = switcherWrapper.offsetHeight + 'px';
					target.classList.add('--open');

					const scrollToTarget = () => {
						let elementRealTop = target.getBoundingClientRect().top / 100;
						let time = 400;
						let currentTime = 0;
						let scrollBySvs = () => {
							window.scrollBy(0, elementRealTop);
						}
						while (currentTime <= time)
						{
							window.setTimeout(scrollBySvs, currentTime, elementRealTop);
							currentTime += time / 100;
						}
						target.removeEventListener('transitionend', scrollToTarget);
					}

					const adjustHeight = () => {
						target.style.height = 'auto';
						target.removeEventListener('transitionend', adjustHeight);
					}

					target.addEventListener('transitionend', adjustHeight);
					target.addEventListener('transitionend', scrollToTarget);
				}

				if (target.offsetHeight > 0)
				{
					target.style.height = target.offsetHeight + 'px';
					setTimeout(() => {
						target.style.removeProperty('height');
						target.classList.remove('--open');
					});
				}
			});
		});
	}

	static initDropdowns()
	{

		const dropdownAreaList = document.querySelectorAll('[data-role="soc-net-dropdown"]');

		dropdownAreaList.forEach((dropdownArea) => {

			dropdownArea.addEventListener('click', (e) => {

				const dropdownArea = e.currentTarget;
				const dropdownItemsData = this.getDropdownItems(dropdownArea);
				const items = [];

				Object.entries(dropdownItemsData).forEach(([key, value]) => {
					items.push({
						text: value,
						onclick: () => {
							dropdownMenu.close();

							this.setDropdownValue(
								dropdownArea.querySelector('.ui-ctl-element'),
								value,
								dropdownArea
							);
							this.setInputValue(
								dropdownArea.querySelector('input'),
								key,
								dropdownArea
							);

							let neighbourDropdownArea = null;
							if (dropdownArea.classList.contains('--nonproject'))
							{
								neighbourDropdownArea = dropdownArea.parentNode.querySelector('.--project');
							}
							else if (dropdownArea.classList.contains('--project'))
							{
								neighbourDropdownArea = dropdownArea.parentNode.querySelector('.--nonproject');
							}

							if (Type.isDomNode(neighbourDropdownArea))
							{
								this.setDropdownValue(
									neighbourDropdownArea.querySelector('.ui-ctl-element'),
									value,
									neighbourDropdownArea
								);
								this.setInputValue(
									neighbourDropdownArea.querySelector('input'),
									key,
									neighbourDropdownArea
								);
							}
						}
					});
				});

				const dropdownMenu = new BX.PopupMenuWindow({
					autoHide: true,
					cacheable: false,
					bindElement: dropdownArea,
					width: dropdownArea.offsetWidth,
					closeByEsc: true,
					animation: 'fading-slide',
					items: items,
				});

				dropdownMenu.params.width = dropdownArea.offsetWidth;
				dropdownMenu.show();
			});
		});
	}

	static setDropdownValue(node, value, containerNode)
	{
		const dropdownItemsData = this.getDropdownItems(containerNode);
		Object.entries(dropdownItemsData).forEach(([, itemValue]) => {
			if (value === itemValue)
			{
				node.innerText = value;
			}
		});
	}

	static setInputValue(node, value, containerNode)
	{
		const dropdownItemsData = this.getDropdownItems(containerNode);
		Object.entries(dropdownItemsData).forEach(([itemKey]) => {
			if (value === itemKey)
			{
				node.value = value;
			}
		});
	}

	static getDropdownItems(node)
	{
		let dropdownItemsData = {};

		try
		{
			dropdownItemsData = JSON.parse(node.getAttribute('data-items'));
		}
		catch(e)
		{
			return {};
		}

		if (!Type.isPlainObject(dropdownItemsData))
		{
			return {};
		}

		return dropdownItemsData;
	}

	static recalcFormPartProject(isChecked)
	{
		isChecked = !!isChecked;

		const projectCheckboxNode = document.getElementById('GROUP_PROJECT');
		if (projectCheckboxNode)
		{
			this.setCheckedValue(projectCheckboxNode, isChecked);
		}

		document.querySelectorAll('.socialnetwork-group-create-ex__create--switch-project, .socialnetwork-group-create-ex__create--switch-nonproject').forEach((node) => {
			if (isChecked)
			{
				node.classList.add('--project');
			}
			else
			{
				node.classList.remove('--project');
			}
		});

		this.recalcNameInput();
	}

	static recalcNameInput()
	{
		const inputNode = document.getElementById('GROUP_NAME_input');
		if (!inputNode)
		{
			return;
		}

		let placeholderText = Loc.getMessage('SONET_GCE_T_NAME3');

		const formInstance = WorkgroupForm.getInstance();
		if (Type.isPlainObject(formInstance.projectTypes[formInstance.selectedProjectType]))
		{
			if (
				Type.isStringFilled(formInstance.projectTypes[formInstance.selectedProjectType].SCRUM_PROJECT)
				&& formInstance.projectTypes[formInstance.selectedProjectType].SCRUM_PROJECT === 'Y'
			)
			{
				placeholderText = Loc.getMessage('SONET_GCE_T_NAME3_SCRUM');
			}
			else if (
				Type.isStringFilled(formInstance.projectTypes[formInstance.selectedProjectType].PROJECT)
				&& formInstance.projectTypes[formInstance.selectedProjectType].PROJECT === 'Y'
			)
			{
				placeholderText = Loc.getMessage('SONET_GCE_T_NAME3_PROJECT');
			}
		}

		inputNode.placeholder = placeholderText;
	}

	static setCheckedValue(node, value)
	{
		if (!Type.isDomNode(node))
		{
			return;
		}

		value = !!value;

		if (node.type === 'checkbox')
		{
			node.checked = value;
		}
		else
		{
			node.value = (value ? 'Y' : 'N');
		}
	}

	static getCheckedValue(node)
	{
		let result = false;

		if (!Type.isDomNode(node))
		{
			return result;
		}

		if (node.type == 'hidden')
		{
			result = (node.value === 'Y');
		}
		else if (node.type == 'checkbox')
		{
			result = node.checked;
		}

		return result;
	}

	static unselectAllSelectorItems(container, selectorClass)
	{
		if (!Type.isDomNode(container))
		{
			return;
		}

		container.querySelectorAll(`.${selectorClass}`).forEach((selector) => {
			selector.classList.remove(this.cssClass.selectorActive);
		});
	}

	static selectSelectorItem(node)
	{
		node.classList.add(this.cssClass.selectorActive);
	}

	static disableAllSelectorItems(container, selectorClass)
	{
		if (!Type.isDomNode(container))
		{
			return;
		}

		container.querySelectorAll(`.${selectorClass}`).forEach((selector) => {
			selector.classList.add(this.cssClass.selectorDisabled);
		});
	}

	static enableAllSelectorItems(container, selectorClass)
	{
		if (!Type.isDomNode(container))
		{
			return;
		}

		container.querySelectorAll(`.${selectorClass}`).forEach((selector) => {
			selector.classList.remove(this.cssClass.selectorDisabled);
		});
	}

	static enableSelectorItem(node)
	{
		node.classList.remove(this.cssClass.selectorDisabled);
	}

	static recalcInputValue(params): void
	{
		const selectedItems = params.selectedItems || [];
		const multiple = Type.isBoolean(params.multiple) ? params.multiple : true;
		const inputContainerNodeId = params.inputContainerNodeId || '';

		let inputNodeName = params.inputNodeName || '';

		if (
			!Type.isArray(selectedItems)
			|| !Type.isStringFilled(inputNodeName)
			|| !Type.isStringFilled(inputContainerNodeId)
		)
		{
			return;
		}

		const inputContainerNode = document.getElementById(inputContainerNodeId);
		if (!inputContainerNode)
		{
			return;
		}

		if (multiple)
		{
			inputNodeName = `${inputNodeName}[]`;
		}

		inputContainerNode.querySelectorAll(`input[name="${inputNodeName}"]`).forEach((node) => {
			Dom.remove(node);
		});

		selectedItems.forEach((item) => {

			let prefix = null;

			switch (item.entityId)
			{
				case 'department':
					prefix = 'DR';
					break;
				case 'user':
					prefix = 'U';
					break;
				default:
			}

			if (prefix)
			{
				inputContainerNode.appendChild(Tag.render`<input type="hidden" name="${inputNodeName}" value="${prefix}${item.id}" \>`);
			}
		});
	};

}
