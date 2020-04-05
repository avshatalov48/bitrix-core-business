;(function ()
{

	BX.namespace('BX.Sender.Message');
	if (BX.Sender.Message.Editor)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * Editor.
	 *
	 */
	function Editor()
	{
		this.context = null;
		this.editor = null;
	}
	Editor.prototype.classNameMoreActive = 'sender-more-active';
	Editor.prototype.init = function (params)
	{
		this.context = BX(params.containerId);
		this.actionUri = params.actionUri;
		this.mess = params.mess;
		this.fieldPrefix = params.fieldPrefix;
		this.messageCode = params.messageCode;

		this.templateType = params.templateType;
		this.templateId = params.templateId;

		this.moreButton = Helper.getNode('more-btn', this.context);
		this.moreFields = Helper.getNode('more-fields', this.context);
		if (this.moreButton && this.moreFields)
		{
			BX.bind(this.moreButton, 'click', this.onMoreClick.bind(this));
		}

		this.configuration = new Configuration(this);
		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		Helper.hint.init(this.context);

		this.bindNodes();
		this.initFields();
	};
	Editor.prototype.initFields = function ()
	{
		var fieldNodes = this.context.querySelectorAll('[data-bx-field]');
		fieldNodes = BX.convert.nodeListToArray(fieldNodes);
		fieldNodes.forEach(function (fieldNode) {
			var inputName = fieldNode.getAttribute('data-bx-field');
			var node = fieldNode.querySelector('[name="' + inputName + '"]');
			Helper.tag.init(fieldNode, node);
		}, this);
	};
	Editor.prototype.onMoreClick = function ()
	{
		Helper.display.toggle(this.moreFields);
		BX.toggleClass(this.moreButton, this.classNameMoreActive);
	};
	Editor.prototype.setAdaptedInstance = function (editor)
	{
		this.editor = editor;
	};
	Editor.prototype.initTester = function ()
	{
		var tester = BX.Sender.Message.Tester;
		if (!tester)
		{
			return;
		}

		BX.addCustomEvent(tester, tester.eventNameSend, this.onTestSend.bind(this));
	};
	Editor.prototype.bindNodes = function ()
	{
		setTimeout(this.initTester.bind(this), 200);

		if (BX.Sender.Template && BX.Sender.Template.Selector)
		{
			var selector = BX.Sender.Template.Selector;
			BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
		}
	};
	Editor.prototype.onTemplateSelect = function (template)
	{
		this.setTemplate(template);
	};
	Editor.prototype.setTemplate = function(template)
	{
		this.templateType = template.type;
		this.templateId = template.code;

		var fields = {};
		(template.messageFields || {}).reduce(function (fields, field) {
			if (!field.onDemand)
			{
				fields[field.code] = field.value;
			}
			return fields;
		}, fields);

		this.configuration.set(fields);
	};
	Editor.prototype.onTestSend = function (message)
	{
		message.data = this.getConfiguration();
	};
	Editor.prototype.getConfiguration = function ()
	{
		return this.configuration.get();
	};

	function Configuration(manager)
	{
		this.manager = manager;
	}
	Configuration.prototype =
	{
		getInputs: function ()
		{
			var inputs = this.manager.context.querySelectorAll(
				'select, textarea, ' +
				'input[type="text"], ' +
				'input[type="hidden"], ' +
				'input[type="radio"]:checked, ' +
				'input[type="checkbox"]:checked'
			);
			inputs = BX.convert.nodeListToArray(inputs);

			return inputs.filter(function (input) {
				return !!this.getInputName(input);
			}, this);
		},
		getInputName: function (input)
		{
			var name = input.name;
			if (name.indexOf(this.manager.fieldPrefix) !== 0)
			{
				return '';
			}

			return name.substring(this.manager.fieldPrefix.length);

		},
		set: function (data)
		{
			this.getInputs().forEach(function (input) {
				var name = this.getInputName(input);
				if (data[name])
				{
					input.value = data[name];
					BX.fireEvent(input, 'change');
				}
			}, this);
		},
		get: function ()
		{
			var data = {};
			this.getInputs().forEach(function (input) {
				var name = this.getInputName(input);
				if (!input.value)
				{
					return;
				}

				if (data[name])
				{
					if (!BX.type.isArray(data[name]))
					{
						data[name] = [data[name]];
					}

					data[name].push(input.value);
				}
				else
				{
					data[name] = input.value;
				}
			}, this);

			data.TEMPLATE_TYPE = this.manager.templateType;
			data.TEMPLATE_ID = this.manager.templateId;

			return data;
		}
	};

	BX.Sender.Message.Editor = new Editor();

})(window);