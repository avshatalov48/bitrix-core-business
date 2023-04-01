import {Reflection, Type, Event, Tag, Dom, Text, Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {UI} from 'ui.notification';

const namespace = Reflection.namespace('BX.Bizproc');

const toJsonString = function(data)
{
	return JSON.stringify(data, (i, v) => {
		if (typeof(v) === 'boolean')
		{
			return v ? '1' : '0';
		}
		return v;
	});
}

class ScriptEditComponent
{
	baseNode;
	leftMenuNode;
	saveButtonNode;
	formNode;
	scriptNameNode;
	documentType;
	signedParameters;
	automationDesigner;
	saveCallback;
	configsMenuItem;

	constantPrefix = 'Constant__';
	parameterPrefix = 'Parameter__';

	constructor(options)
	{
		if(Type.isPlainObject(options))
		{
			this.baseNode = options.baseNode;
			this.leftMenuNode = options.leftMenuNode;
			this.saveButtonNode = options.saveButtonNode;
			this.formNode = options.formNode;
			this.documentType = options.documentType;
			this.signedParameters = options.signedParameters;
			this.saveCallback = options.saveCallback
		}
		this.automationDesigner = BX.Bizproc.Automation.Designer.getInstance().component;
	}

	init()
	{
		if (this.saveButtonNode)
		{
			Event.bind(this.saveButtonNode, 'click', this.saveHandler.bind(this));
		}
		if (this.baseNode && this.leftMenuNode)
		{
			this.initMenu();
		}

		if (this.formNode)
		{
			this.scriptNameNode = this.formNode.elements.NAME;
			Event.bind(this.scriptNameNode, 'blur', () => {
				if (!Type.isStringFilled(this.scriptNameNode.value))
				{
					Dom.addClass(this.scriptNameNode.closest('.ui-ctl'), 'ui-ctl-danger');
				}
				else
				{
					Dom.removeClass(this.scriptNameNode.closest('.ui-ctl'), 'ui-ctl-danger');
				}
			});
		}

		if (this.automationDesigner)
		{
			EventEmitter.subscribe(this.automationDesigner, 'onTemplateConstantAdd', () => {
				if (this.configsMenuItem)
				{
					this.configsMenuItem.addNoticeIcon();
				}
			});
		}
	}

	saveHandler()
	{
		const form = new FormData(this.formNode);
		const scriptFields = {};
		for (let field of form.entries())
		{
			scriptFields[field[0]] = field[1];
		}

		if (!this.#validateScriptName(scriptFields.NAME))
		{
			Dom.removeClass(this.saveButtonNode, 'ui-btn-wait');
			return false;
		}

		const robotsTemplate = this.#getRobotsTemplate();
		this.setTemplateValues(robotsTemplate);

		if (!this.#validateConstants(robotsTemplate.getConstants(), robotsTemplate.collectUsages().Constant))
		{
			Dom.removeClass(this.saveButtonNode, 'ui-btn-wait');
			return false;
		}

		BX.ajax.runComponentAction('bitrix:bizproc.script.edit', 'saveScript', {
			analyticsLabel: scriptFields.ID > 0 ? 'bizprocScriptUpdate' : 'bizprocScriptAdd',
			data: {
				signedParameters: this.signedParameters,
				documentType: this.documentType,
				script: scriptFields,
				robotsTemplate: toJsonString(robotsTemplate.serialize())
			}
		}).then((result) =>
		{
			if (result.status === 'success' && !Type.isArrayFilled(result.errors))
			{
				robotsTemplate.markModified(false);
			}

			if (Type.isFunction(this.saveCallback))
			{
				this.saveCallback(result);
			}
		});
	}

	#getRobotsTemplate()
	{
		return this.automationDesigner.templateManager.templates[0];
	}

	#activateSection(section: string): void
	{
		if (BX.UI.DropdownMenuItem.getItemByNode)
		{
			const menuItem = BX.UI.DropdownMenuItem.getItemByNode(this.leftMenuNode.querySelector(`[data-page="${section}"]`));

			this.menuActivateHandler(section);
			menuItem && menuItem.setActiveHandler();
		}

		if (section === 'general')
		{
			this.scriptNameNode.focus();
		}
		if (section !== 'configs')
		{
			this.setTemplateValues(this.#getRobotsTemplate());
		}
	}

	#validateScriptName(name: string): boolean
	{
		if (!Type.isStringFilled(name))
		{
			UI.Notification.Center.notify({
				content: Loc.getMessage('BIZPROC_SCRIPT_EDIT_VALIDATION_EMPTY_NAME')
			});

			this.#activateSection('general');
			return false;
		}
		return true;
	}

	#validateConstants(constants: [], usedConstants: Set): boolean
	{
		let result = true;

		constants.forEach((constant) => {
			if (usedConstants.has(constant.Id) && !Type.isStringFilled(constant.Default))
			{
				result = false;
			}
		});

		if (!result)
		{
			UI.Notification.Center.notify({
				content: Loc.getMessage('BIZPROC_SCRIPT_EDIT_VALIDATION_EMPTY_CONFIGS')
			});

			this.#activateSection('configs');
		}

		return result;
	}

	initMenu()
	{
		Array.from(this.leftMenuNode.querySelectorAll('[data-role="menu-item"]')).forEach((el) =>
		{
			Event.bind(el, 'click', this.menuActivateHandler.bind(this, el.getAttribute('data-page')));

			if (el.getAttribute('data-page') === 'configs' && BX.UI.DropdownMenuItem.getItemByNode)
			{
				this.configsMenuItem = BX.UI.DropdownMenuItem.getItemByNode(el);
			}
		});
	}

	menuActivateHandler(page)
	{
		Array.from(this.baseNode.querySelectorAll('[data-section]')).forEach((el) =>
			{
				if (el.getAttribute('data-section') === page)
				{
					if (page === 'configs' && Dom.hasClass(el,'bizproc-script-edit-block-hidden'))
					{
						this.showConfigsHandler(el);
					}
					else
					{
						this.setTemplateValues(this.#getRobotsTemplate());
					}
					Dom.removeClass(el, 'bizproc-script-edit-block-hidden')
				}
				else
				{
					Dom.addClass(el, 'bizproc-script-edit-block-hidden');
				}
			}
		);
	}

	showConfigsHandler(configsNode)
	{
		Dom.clean(configsNode);

		const robotsTemplate = this.#getRobotsTemplate();
		const constants = robotsTemplate.getConstants();
		const parameters = robotsTemplate.getParameters();
		const robotNodes = [];

		robotsTemplate.robots.forEach((robot) => {
			const node = this.renderRobotConfigBlock(robot, constants, parameters);
			if (node)
			{
				robotNodes.push(node);
			}
		});

		if (robotNodes.length)
		{
			Dom.append(Tag.render`<form data-role="constant-list" onsubmit="return false;">${robotNodes}</form>`, configsNode);
		}
		else
		{
			return Dom.append(
				Tag.render`<div class="ui-alert ui-alert-default ui-alert-xs ui-alert-icon-info">
					<span class="ui-alert-message">${Loc.getMessage('BIZPROC_SCRIPT_EDIT_SECTION_CONFIGS_EMPTY')}</span>
				</div>`,
				configsNode
			);
		}
	}

	renderRobotConfigBlock(robot, constants, parameters): ?HTMLElement
	{
		const usages = robot.collectUsages();
		const itemNodes = [];

		if (usages.Constant.size)
		{
			let headPushed = false;
			usages.Constant.forEach((constId) => {
				const constant = constants.find((c) => c.Id === constId && c.Type !== 'file');
				if (constant)
				{
					if (!headPushed)
					{
						itemNodes.push(Tag.render`<div class="bizproc-script-edit-item">
							<div class="bizproc-script-edit-title">${Loc.getMessage('BIZPROC_SCRIPT_EDIT_CONSTANT_LABEL')}</div>
							<div class="bizproc-script-edit-text">${Loc.getMessage('BIZPROC_SCRIPT_EDIT_CONSTANT_DESCRIPTION')}</div>
						</div>`);
						headPushed = true;
					}

					itemNodes.push(this.renderPropertyBlock(constant, this.constantPrefix));
				}
			});
		}

		if (usages.Parameter.size)
		{
			let headPushed = false;
			usages.Parameter.forEach((paramId) => {
				const parameter = parameters.find((p) => p.Id === paramId && p.Type !== 'file');
				if (parameter)
				{
					if (!headPushed)
					{
						itemNodes.push(Tag.render`<div class="bizproc-script-edit-item">
							<div class="bizproc-script-edit-title">${Loc.getMessage('BIZPROC_SCRIPT_EDIT_PARAMETER_LABEL')}</div>
							<div class="bizproc-script-edit-text">${Loc.getMessage('BIZPROC_SCRIPT_EDIT_PARAMETER_DESCRIPTION')}</div>
						</div>`);
						headPushed = true;
					}
					itemNodes.push(this.renderPropertyBlock(parameter, this.parameterPrefix));
				}
			});
		}

		if (!itemNodes.length)
		{
			return null;
		}

		return Tag.render`
			<div class="ui-slider-section">
				<div class="ui-slider-heading-4 ui-slider-heading-4--bizproc-icon">${Text.encode(robot.getTitle())}</div>
				${itemNodes}
			</div>`
		;
	}

	renderPropertyBlock(property: {}, prefix: string)
	{
		const control = BX.Bizproc.FieldType.renderControlPublic(
			this.automationDesigner.document.getRawType(),
			property,
			prefix + property.Id,
			property.Default,
			false
		);

		return Tag.render`
			<div class="bizproc-script-edit-item">
				<div class="bizproc-script-edit-subtitle">${Text.encode(property.Name)}</div>
				<div class="bizproc-script-edit-text">${Text.encode(property.Description)}</div>
				<a onclick="${this.changePropertyDescription.bind(this, prefix, property)}" class="ui-link ui-link-secondary ui-link-dashed">${Loc.getMessage('BIZPROC_SCRIPT_EDIT_BTN_CHANGE')}</a>
				<div class="bizproc-script-edit-field">
					${control}
				</div>
			</div>`
		;
	}

	changePropertyDescription(prefix, property, event)
	{
		const element = event.currentTarget;
		const wrapper = element.previousElementSibling;

		Dom.hide(element);

		const inputElement = Tag.render`
			<input value="" type="text" class="ui-ctl-element">
		`;

		inputElement.value = property.Description || '';

		Dom.clean(wrapper);
		Dom.append(inputElement, wrapper);
		inputElement.focus();

		const applyNewDescription = () =>
		{
			const text = inputElement.value.trim();

			property.Description = text;
			Dom.clean(wrapper);
			wrapper.textContent = text;
			Dom.show(element);

			const robotsTemplate = this.#getRobotsTemplate();

			if (prefix === this.constantPrefix)
			{
				robotsTemplate.updateConstant(property.Id, property);
			}
			else
			{
				robotsTemplate.updateParameter(property.Id, property);
			}
		}

		Event.bind(inputElement, 'blur', applyNewDescription);
		Event.bind(inputElement, 'keydown', (event) => {
			if (event.keyCode === 13)
			{
				Event.unbind(inputElement, 'blur', applyNewDescription);
				applyNewDescription();
			}
		});
	}

	setTemplateValues(template): void
	{
		const formNode = this.baseNode? this.baseNode.querySelector('[data-role="constant-list"]') : null;
		if (!formNode)
		{
			return;
		}

		const form = new FormData(formNode);

		template.getConstants().forEach((constant) => {
			template.setConstantValue(constant.Id, form.get(this.constantPrefix+constant.Id));
		});

		template.getParameters().forEach((param) => {
			template.setParameterValue(param.Id, form.get(this.parameterPrefix+param.Id));
		});
	}
}

namespace.ScriptEditComponent = ScriptEditComponent;