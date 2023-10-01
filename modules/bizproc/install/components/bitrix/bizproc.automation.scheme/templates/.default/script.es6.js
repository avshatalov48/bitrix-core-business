import { Dom, Event, Reflection, Type, Tag, Text } from 'main.core';
import { TemplatesScheme } from 'bizproc.automation';
import { Menu } from 'main.popup';

const namespace = Reflection.namespace('BX.Bizproc.Component');

class Scheme
{
	scheme: TemplatesScheme;
	signedParameters: string;
	steps;

	selectedType = null;
	selectedCategory = null;
	selectedStatus = null;

	executeButton: HTMLDivElement;

	errorsContainer: HTMLDivElement;
	stepContentTypeContainer: HTMLDivElement;
	stepContentCategoryContainer: HTMLDivElement;
	stepContentStatusContainer: HTMLDivElement;

	constructor(options)
	{
		if (Type.isPlainObject(options))
		{
			this.scheme = new TemplatesScheme(options.scheme);
			this.signedParameters = options.signedParameters;
			this.steps = options.steps;
			this.action = options.action;

			this.executeButton = options.executeButton;

			this.errorsContainer = options.errorsContainer;
			this.stepContentTypeContainer = options.stepsContentContainers[0];
			this.stepContentCategoryContainer = options.stepsContentContainers[1];
			this.stepContentStatusContainer = options.stepsContentContainers[2];
		}
	}

	init()
	{
		this.renderStepContents();
		Event.bind(this.executeButton, 'click', this.onExecuteButtonClick.bind(this));
	}

	renderStepContents()
	{
		const steps = [
			[this.selectedType, this.stepContentTypeContainer, this.onTypeSelectorClick.bind(this)],
			[this.selectedCategory, this.stepContentCategoryContainer, this.onCategorySelectorClick.bind(this)],
			[this.selectedStatus, this.stepContentStatusContainer, this.onStatusSelectorClick.bind(this)],
		];

		let completedSteps = 0;
		steps.forEach(([selected, container, onclick]) => {
			const text =
				Type.isNil(selected)
					? BX.message('BIZPROC_AUTOMATION_SCHEME_DROPDOWN_PLACEHOLDER')
					: selected.Name
			;
			this.renderDropdownStepContent(container, text, onclick);
			if (selected)
			{
				completedSteps += 1;
			}
		});

		if (!Type.isNil(this.selectedType) && this.scheme.getTypeCategories(this.selectedType).length <= 0)
		{
			this.renderTextStepContent(this.stepContentCategoryContainer, BX.message('BIZPROC_AUTOMATION_SCHEME_CATEGORIES_NOT_EXISTS'));
			completedSteps += 1;
		}
		this.stepTo(completedSteps);
	}

	renderDropdownStepContent(target, text, onclick)
	{
		Dom.clean(target);
		const dropdownNode = Dom.create('div', {
			attrs: {class: 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown'},
			events: {
				click: onclick
			},
			children: [
				Tag.render`<div class="ui-ctl-after ui-ctl-icon-angle"></div>`,
				Tag.render`<div class="ui-ctl-element">${Text.encode(text)}</div>`,
			],
		});

		target.appendChild(
			Dom.create('div', {
				attrs: {class: 'bizproc-automation-scheme__content --padding-15'},
				children: [dropdownNode],
			})
		);
	}

	renderTextStepContent(target, text)
	{
		Dom.clean(target);
		target.appendChild(
			Tag.render`
				<div class="bizproc-automation-scheme__content">
					<div class="ui-alert ui-alert-success">
						<span class="ui-alert-message">${Text.encode(text)}</span>
					</div>
				</div>
			`
		);
	}

	onExecuteButtonClick(event)
	{
		event.preventDefault();
		if (!this.selectedType || !this.selectedStatus)
		{
			this.showError({message: BX.message(`BIZPROC_AUTOMATION_SCHEME_DESTINATION_SCOPE_ERROR_ACTION_${this.action}`)});
			Dom.removeClass(this.executeButton, 'ui-btn-wait');
			return;
		}

		BX.ajax.runComponentAction('bitrix:bizproc.automation.scheme', 'copyMove', {
			mode: 'class',
			signedParameters: this.signedParameters,
			data: {
				dstScope: {
					DocumentType: this.selectedType,
					Category: this.selectedCategory,
					Status: this.selectedStatus,
				},
			}
		}).then(response => {
			if (this.isSlider())
			{
				const sliderData = BX.SidePanel.Instance.getTopSlider().getData();
				Object.entries(response.data).forEach(([key, data]) => sliderData.set(key, data));
				sliderData.set('targetScope', {
					documentType: this.selectedType,
					category: this.selectedCategory,
					status: this.selectedStatus,
				});
				BX.SidePanel.Instance.close();
			}
			Dom.removeClass(this.executeButton, 'ui-btn-wait');
		}).catch(response => {
			response.errors.forEach(error => this.showError(error));
			Dom.removeClass(this.executeButton, 'ui-btn-wait');
		});
	}

	isSlider(): boolean
	{
		return location.href.toString().indexOf('SIDE_SLIDER') > 0;
	}

	stepTo(index: number)
	{
		this.steps.forEach(function(elem, i)
		{
			if (i < index)
			{
				Dom.addClass(elem, '--success');
			}
			else
			{
				Dom.removeClass(elem, '--success');
			}
		}.bind(this));
	}

	onTypeSelectorClick(event)
	{
		// debugger;
		event.preventDefault();
		const self = this;

		this.adjustDropdown(
			event.target.closest('.ui-ctl-dropdown'),
			this.scheme.getDocumentTypes().map(type => {
				return {
					id: type.Type,
					text: type.Name,
					onclick(event)
					{
						event.preventDefault();
						this.close();
						self.selectedType = type;
						self.selectedCategory = null;
						self.selectedStatus = null;
						self.renderStepContents();
					},
				};
			})
		);
	}

	onCategorySelectorClick(event)
	{
		event.preventDefault();
		const self = this;
		const categories = !Type.isNil(this.selectedType) ? this.scheme.getTypeCategories(this.selectedType) : [];

		if (categories.length > 0)
		{
			this.adjustDropdown(
				event.target.closest('.ui-ctl-dropdown'),
				categories.map(category => {
					return {
						id: category.Id,
						text: category.Name,
						onclick(event)
						{
							event.preventDefault();
							this.close();
							self.selectedCategory = category;
							self.selectedStatus = null;
							self.renderStepContents();
						}
					}
				})
			);
		}
	}

	onStatusSelectorClick(event)
	{
		event.preventDefault();
		const self = this;
		let statuses =  [];
		if (!Type.isNil(this.selectedType))
		{
			statuses = this.scheme.getTypeStatuses(this.selectedType, this.selectedCategory);
		}

		if (statuses.length > 0)
		{
			this.adjustDropdown(
				event.target.closest('.ui-ctl-dropdown'),
				statuses.map(status => {
					return {
						id: status.Id,
						text: status.Name,
						onclick(event)
						{
							event.preventDefault();
							this.close();
							self.selectedStatus = status;
							self.renderStepContents();
						}
					}
				})
			);
		}
	}

	adjustDropdown(target, items)
	{
		const popupMenu = new Menu({
			autoHide: true,
			bindElement: target,
			width: target.offsetWidth,
			closeByEsc: true,
			items: items,
		});

		popupMenu.show();
	}

	showError(error)
	{
		const errorNode = Dom.create('div', {
			props: {className: 'ui-alert ui-alert-danger'},
			children: [
				Tag.render`<span class="ui-alert-message">${error.message}</span>`
			]
		});

		this.errorsContainer.append(errorNode);
	}
}

namespace.Scheme = Scheme;
