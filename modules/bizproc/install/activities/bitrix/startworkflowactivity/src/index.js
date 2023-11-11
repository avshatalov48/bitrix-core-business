import {Reflection, Type, Text, Loc, ajax} from 'main.core';
import {TagSelector} from "ui.entity-selector";
import {Designer} from 'bizproc.automation';

const namespace = Reflection.namespace('BX.Bizproc.Activity');

class StartWorkflowActivity
{
	#templateNode: HTMLElement;
	#templateInput: HTMLInputElement;
	#templateId: number = null;
	#parametersNode: HTMLElement;
	#documentType: [];
	#formName: string;
	#propertiesDialog: {};
	#isRobot: boolean = false;
	constructor(options: {
		templateNode: HTMLElement,
		templateInput: HTMLInputElement,
		templateId: number,
		parametersNode: HTMLElement,
		documentType: [],
		formName: string,
		isRobot: boolean,
		propertiesDialog?: {},
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
		this.#propertiesDialog = Type.isPlainObject(options.propertiesDialog) ? options.propertiesDialog : {};
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
			preselectedItems.push(['bizproc-template', this.#templateId]);
		}

		const selector = new TagSelector({
			dialogOptions: {
				entities: [
					{
						id: 'bizproc-template',
					}
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
						this.#getTemplateParameters(selectedItem.getId());

						this.#templateInput.value = selectedItem.getId();
					},
					'Item:onDeselect': (event) => {
						this.#getTemplateParameters(-1);
						this.#templateInput.value = '';
					},
				},
			},
			multiple: false,
			tagMaxWidth: 500,
			textBoxWidth: 100,
		});

		selector.renderTo(this.#templateNode);
	}

	#getTemplateParameters(templateId: number)
	{
		this.#parametersNode.innerHTML = '';

		templateId = Text.toInteger(templateId);

		if (templateId <= 0)
		{
			return;
		}

		const requestData = {
			site_id: Loc.getMessage('SITE_ID'),
			sessid: BX.bitrix_sessid(),
			document_type: this.#documentType,
			activity: 'StartWorkflowActivity',
			template_id: templateId,
			form_name: this.#formName,
			content_type: 'html',
		};

		if (this.#isRobot === true)
		{
			requestData['properties_dialog'] = this.#propertiesDialog;
			requestData['isRobot'] = 'y';
		}

		ajax.post(
			'/bitrix/tools/bizproc_activity_ajax.php',
			requestData,
			(response) => {
				if (response)
				{
					this.#parametersNode.innerHTML = response;
				}

				if (this.#isRobot && Reflection.getClass('BX.Bizproc.Automation.Designer'))
				{
					const dlg = Designer.getInstance().getRobotSettingsDialog();
					if (dlg && dlg.template)
					{
						dlg.template.initRobotSettingsControls(dlg.robot, this.#parametersNode);
					}
				}
			}
		);
	}
}

namespace.StartWorkflowActivity = StartWorkflowActivity;
