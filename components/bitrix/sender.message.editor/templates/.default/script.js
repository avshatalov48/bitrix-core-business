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
		this.messageId = params.messageId;

		this.templateType = params.templateType;
		this.templateId = params.templateId;

		this.moreButton = Helper.getNode('more-btn', this.context);
		this.blockToCopy = Helper.getNode('consent-block-to-copy', this.context);

		if (this.blockToCopy)
		{
			this.blockToCopy.hidden = false;

			if (this.templateId !== 'empty')
			{
				this.blockToCopy.hidden = true;
			}
		}

		this.copyBtn = document.getElementById('ui-button-copy');
		this.moreFields = Helper.getNode('more-fields', this.context);
		if (this.moreButton && this.moreFields)
		{
			BX.bind(this.moreButton, 'click', this.onMoreClick.bind(this));
		}

		if (this.copyBtn)
		{
			BX.bind(this.copyBtn, 'click', this.onCopyClick.bind(this));
		}

		this.configuration = new Configuration(this);
		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		Helper.hint.init(this.context);

		this.bindNodes();
		this.initFields();
		this.loadFrame();
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

	Editor.prototype.loadFrame = function()
	{
		var frameNode = Helper.getNode('bx-sender-template-iframe', this.context);


		if(frameNode)
		{
			frameNode.src = this.ajaxAction.getRequestingUri('prepareHtml', {
				'lang': '',
				'messageId': this.messageId
			});

			frameNode.onload = function()
			{
				var loader = Helper.getNode('bx-sender-view-loader',  BX.Sender.Message.Editor.context);
				loader.style.display = 'none';
				frameNode.style.display = 'block';
			};
		}
	};

	Editor.prototype.onMoreClick = function ()
	{
		Helper.display.toggle(this.moreFields);
		BX.toggleClass(this.moreButton, this.classNameMoreActive);
	};
	Editor.prototype.onCopyClick = function ()
	{
		var textArea = document.createElement("textarea");
		textArea.value = document.querySelector('.sender-footer-to-copy').textContent;

		// Avoid scrolling to bottom
		textArea.style.top = "0";
		textArea.style.left = "0";
		textArea.style.position = "fixed";

		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			document.execCommand('copy');
			textArea.remove();
		} catch (err) {
		}
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
		if (this.blockToCopy)
		{
			this.blockToCopy.hidden = false;

			if (template.code !== 'empty')
			{
				this.blockToCopy.hidden = true;
			}
		}

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
		if(this.getMessageId()) {
			message.id = this.getMessageId();
		}
		if (!message.data)
			message.data = {};

		message.data = Object.assign(message.data, this.getConfiguration());
	};

	Editor.prototype.getMessageId = function ()
	{
		return this.messageId;
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
		getInputs: function (options)
		{
			options = options || {};
			options.disableChecked = options.disableChecked || false;
			var checked = options.disableChecked ? '' : ':checked';

			var inputs = this.manager.context.querySelectorAll(
				'select, textarea, ' +
				'input[type="text"], ' +
				'input[type="hidden"], ' +
				'input[type="radio"]' + checked + ', ' +
				'input[type="checkbox"]' + checked
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
			this.getInputs({disableChecked: true}).forEach(function (input) {
				var name = this.getInputName(input);
				if (data[name])
				{
					switch (input.type)
					{
						case 'checkbox':
							input.checked = input.value === data[name];
							break;

						default:
							input.value = data[name];
					}

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