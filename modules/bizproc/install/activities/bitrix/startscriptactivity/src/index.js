import {Reflection, Type, Text, ajax} from 'main.core';
import {TagSelector} from "ui.entity-selector";

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class StartScriptActivity
{
	#templateNode: HTMLElement;
	#templateInput: HTMLInputElement;
	#templateId: number = null;
	#parametersNode: HTMLElement;
	#documentType: [];
	#formName: string;
	#isRobot: boolean = false;

	constructor(options: {
		templateNode: HTMLElement,
		templateInput: HTMLInputElement,
		templateId: number,
		parametersNode: HTMLElement,
		documentType: [],
		formName: string,
		isRobot: boolean,
	})
	{
		if (!Type.isElementNode(options.templateNode))
		{
			throw 'templateNode must be HTML Element';
		}
		this.#templateNode = options.templateNode;

		if (!Type.isElementNode(options.templateInput))
		{
			throw 'templateInput must be HTML Input Element';
		}
		this.#templateInput = options.templateInput;

		if (!Type.isElementNode(options.parametersNode))
		{
			throw 'parametersNode must be HTML Element';
		}
		this.#parametersNode = options.parametersNode;

		const templateId = Text.toInteger(options.templateId);
		if (templateId > 0)
		{
			this.#templateId = templateId;
		}

		this.#documentType = Type.isArrayFilled(options.documentType) ? options.documentType : [];
		this.#formName = Type.isStringFilled(options.formName) ? options.formName : '';
		this.#isRobot = Type.isBoolean(options.isRobot) ? options.isRobot: false;
	}

	init()
	{
		this.#initTemplateSelector();
	}

	#initTemplateSelector()
	{
		const preselectedItems = [];
		if (this.#templateId)
		{
			preselectedItems.push(['bizproc-script-template', this.#templateId]);
		}

		const selector = new TagSelector({
			dialogOptions: {
				entities: [
					{ id: 'bizproc-script-template' }
				],
				multiple: false,
				dropdownMode: true,
				enableSearch: true,
				hideOnSelect: true,
				hideOnDeselect: false,
				clearSearchOnSelect: true,
				showAvatars: false,
				compactView: true,
				height: 300,
				preselectedItems: preselectedItems,
				events: {
					'Item:onSelect': (event) => {
						const { item: selectedItem } = event.getData();
						this.#renderTemplateParameters(selectedItem.getId());

						this.#templateInput.value = selectedItem.getId();
					},
					'Item:onDeselect': () => {
						this.#renderTemplateParameters(-1);
						this.#templateInput.value = '';
					},
				},
			},
			multiple: false,
			tagMaxWidth: 500
		});

		selector.renderTo(this.#templateNode);
	}

	#renderTemplateParameters(templateId: number) {
		this.#parametersNode.innerHTML = '';

		templateId = Text.toInteger(templateId);
		if (templateId <= 0)
		{
			return;
		}

		ajax.runAction('bizproc.activity.request', {
			data: {
				documentType: this.#documentType,
				activity: 'StartScriptActivity',
				params: {
					template_id: templateId,
					form_name: this.#formName,
					document_type: this.#documentType,
				},
			},
		}).then((response) => {
			this.#parametersNode.innerHTML = response.data;
		});
	}
}

namespace.StartScriptActivity = StartScriptActivity;