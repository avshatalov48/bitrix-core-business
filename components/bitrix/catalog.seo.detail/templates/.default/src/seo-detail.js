import type {SectionOptions} from "./types/section-options";
import type {FormOptions} from "./types/form-options";
import {ajax, Dom, Loc, Tag, Text} from 'main.core';
import {Base} from "./fields-group/base";
import {ValueScheme} from "./types/value-scheme";
import {SeoInput} from "./field/seo-input";
import {Menu} from "main.popup";
import {SeoDetailMode} from "./types/seo-detail-mode";
import {SectionType} from "./types/section-type";
import {Section} from "./fields-group/section";
import {Element} from "./fields-group/element";
import {Management} from "./fields-group/managent";
import "ui.notification";

export class SeoDetail
{
	static instance: SeoDetail = null;
	static HElP_ARTICLE_CODE = 17013874;

	#isLocked: boolean = false;
	sections: Array<Base>;
	values: Array<ValueScheme>;
	defaultValues: Array<ValueScheme>;
	templatePopup: Menu = null;
	templatePopupField: SeoInput = null;

	constructor(settings: FormOptions)
	{
		this.container = BX(settings.containerId);
		this.form = this.container.querySelector('#' + settings.formId);
		this.values = settings.values || {};
		this.defaultValues = settings.values || {};
		this.componentName = settings.componentName || '';
		this.signedParameters = settings.signedParameters || '';
		this.menuItems = settings.menuItems || [];
		this.sections = [];
		this.templatePopupField = null;
		this.readOnly = settings.readOnly || false;
		this.mode = settings.mode || '';
		this.createSections(settings.schemeFields);
	}

	static create(settings): SeoDetail
	{
		SeoDetail.instance = new SeoDetail(settings);

		return SeoDetail.instance
	}

	static onClickSave(): void
	{
		SeoDetail.instance.save();
	}

	static onSelectTemplate(template: string)
	{
		SeoDetail.instance.addInputTemplate(template);
	}

	static openSeoHelpPage(event: Event)
	{
		if(top.BX.Helper)
		{
			top.BX.Helper.show("redirect=detail&code=" + SeoDetail.HElP_ARTICLE_CODE);
		}
	}

	createSections(scheme: Array<SectionOptions>): SeoDetail
	{
		Object.keys(scheme).forEach((fieldCode) => {
			const sectionOptions = scheme[fieldCode];

			let section = null;
			if (sectionOptions.TYPE === SectionType.SECTION)
			{
				section = new Section(sectionOptions, this)
			}
			else if (sectionOptions.TYPE === SectionType.ELEMENT)
			{
				section = new Element(sectionOptions, this)
			}
			else if (sectionOptions.TYPE === SectionType.MANAGEMENT && !this.isReadOnly())
			{
				section = new Management(sectionOptions, this)
			}

			if (section)
			{
				this.sections.push(section);
			}

		});

		return this;
	}

	layout(): void
	{
		this.sections.forEach((section) => {
			Dom.append(section.layout(), this.form);
		});
	}

	getValue(id: string): ValueScheme
	{
		this.values[id] = this.values[id] || {}

		return this.values[id];
	}

	getHint(templateId: string, template: string): Promise
	{
		return ajax.runComponentAction(
			this.componentName,
			'getHint',
			{
				mode: 'class',
				signedParameters: this.signedParameters,
				data: {
					templateId,
					template,
				}
			}
		);
	}

	getSaveButton(): HTMLElement
	{
		return this.container.querySelector('#ui-button-panel-save');
	}

	save()
	{
		if (this.#isLocked)
		{
			return;
		}

		this.#isLocked = true;
		Dom.addClass(this.getSaveButton(), 'ui-btn-wait')
		ajax.runComponentAction(
			this.componentName,
			'save',
			{
				mode: 'class',
				signedParameters: this.signedParameters,
				data: {
					values:this.values
				}
			}
		)
			.then(() => {
				this.#isLocked = false;

				const notificationOptions = {
					closeButton: true,
					autoHideDelay: 3000,
					content: Tag.render`<div>${Loc.getMessage('CSD_SAVE_MESSAGE_NOTIFICATION')}</div>`,
				};

				const notify = top.BX.UI.Notification.Center.notify(notificationOptions);
				notify.show();

				this.onFormCancel();
			})
			.catch(this.onError.bind(this))
		;
	}

	hideInfoMessage(messageId: string)
	{
		ajax.runComponentAction(
			this.componentName,
			'hideInfoMessage',
			{
				mode: 'class',
				signedParameters: this.signedParameters,
				data: {
					messageId
				}
			}
		)
	}

	onError(): void
	{
		Dom.removeClass(this.getSaveButton(), "ui-btn-wait");
		this.#isLocked = false;
	}

	isReadOnly(): boolean
	{
		return this.readOnly;
	}

	getFormFieldName(name): string
	{
		return 'fields['+name+']';
	}

	toggleInputMenu(section: Section, field: SeoInput): void
	{
		if (
			this.templatePopupField
			&& this.templatePopup
			&& field.id !== this.templatePopupField
		)
		{
			this.templatePopup.close();
			this.templatePopup.destroy();
			this.templatePopup = null;
		}

		if (!this.templatePopup)
		{
			this.templatePopupField = field;
			const items = this.getMenuItems(section.getType());
			this.templatePopup = new Menu({
				bindElement: field.getInput(),
				items
			});
		}

		this.templatePopup.toggle();
	}

	hideInputMenu(): void
	{
		if (this.templatePopup)
		{
			this.templatePopup.close();
		}
	}

	addInputTemplate(template: string): void
	{
		if (this.templatePopupField)
		{
			this.templatePopupField.addTemplateValue(template);
		}
		if (this.templatePopup)
		{
			this.templatePopup.close();
		}
	}

	getMenuItems(type: string): []
	{
		return Object.assign(this.menuItems[type]) ?? [];
	}

	onFormCancel(): void
	{
		BX.SidePanel.Instance.close();
	}

	isCatalogMode(): boolean
	{
		return this.mode === SeoDetailMode.CATALOG;
	}

	isElementMode(): boolean
	{
		return this.mode === SeoDetailMode.ELEMENT;
	}

	isSectionMode(): boolean
	{
		return this.mode === SeoDetailMode.SECTION;
	}
}