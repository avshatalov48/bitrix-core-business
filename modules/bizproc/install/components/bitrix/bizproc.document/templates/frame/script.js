;(function() {
	'use strict';

	BX.namespace('BX.Bizproc.DocumentComponent');

	var Component = function(node, config)
	{
		this.node = node;
		this.config = config;
	};

	Component.prototype =
	{
		getNodes: function(role, scope)
		{
			var el = scope || this.node;
			return el.querySelectorAll('[data-role="'+role+'"]');
		},
		getNode: function(role, scope)
		{
			return this.getNodes(role, scope)[0];
		},

		createNodeFromTemplate: function(name)
		{
			var tpls = this.getNode('templates');
			var tpl = tpls.querySelector('[data-template="'+name+'"]');
			if (tpl)
			{
				var newTpl = BX.clone(tpl);
				newTpl.removeAttribute('data-template');
				return newTpl;
			}
			throw 'Template not found';
		},

		init: function()
		{
			this.initEventsApplyButton();
			this.initStartButton();
		},
		renderWorkflows: function(workflows)
		{
			var list = this.getNode('workflows-list');
			BX.cleanNode(list);

			var hasEvents = false;

			for (var i = 0; i < workflows.length; ++i)
			{
				list.appendChild(this.renderWorkflow(workflows[i]));
				if (!hasEvents && workflows[i]['EVENTS'].length > 0)
				{
					hasEvents = true;
				}
			}

			var eventsApplyContainer = this.getNode('events-apply-container');
			BX[hasEvents? 'show' : 'hide'](eventsApplyContainer);
		},
		renderWorkflow: function(workflow)
		{
			var i, tpl = this.createNodeFromTemplate('workflow');

			tpl.setAttribute('data-workflow-id', workflow['ID']);

			if (!workflow['WORKFLOW_STATUS'])
			{
				BX.addClass(tpl, tpl.getAttribute('data-class-finished'));
			}

			if (!workflow['WORKFLOW_STATUS'] || !this.config.canTerminate)
			{
				var terminateContainer = this.getNode('terminate-container', tpl);
				BX.remove(terminateContainer);
			}

			if (!this.config.canKill || workflow['WORKFLOW_STATUS'])
			{
				var killContainer = this.getNode('kill-container', tpl);
				BX.remove(killContainer);
			}

			if (workflow['TASKS'].length > 0)
			{
				BX.addClass(tpl, tpl.getAttribute('data-class-tasks'));
				var tasksList = this.getNode('tasks-container', tpl);
				for (i = 0; i < workflow['TASKS'].length; ++i)
				{
					var task = workflow['TASKS'][i], taskLink = BX.create('a', {
						props: {
							href: '#'
						},
						text: task['NAME']
					});

					BX.bind(taskLink, 'click', this.onTaskLinkClick.bind(this, task));

					tasksList.appendChild(BX.create('li', {
						children: [taskLink]
					}));
				}
			}
			else
			{
				BX.remove(this.getNode('tasks-row', tpl));
			}

			if (workflow['EVENTS'].length > 0)
			{
				var eventsSelect = this.getNode('events-select', tpl);
				eventsSelect.setAttribute('workflow-id', workflow['ID']);
				for (i = 0; i < workflow['EVENTS'].length; ++i)
				{
					eventsSelect.appendChild(BX.create('option', {
						props: {
							value: workflow['EVENTS'][i]['NAME']
						},
						text: workflow['EVENTS'][i]['TITLE']
					}));
				}
			}
			else
			{
				BX.remove(this.getNode('events-row', tpl));
			}

			var templateName = this.getNode('workflow-name', tpl);
			templateName.textContent = workflow['TEMPLATE_NAME'];

			var modified = this.getNode('workflow-modified', tpl);
			modified.textContent = workflow['STATE_MODIFIED_FORMATTED'];

			var stateName = this.getNode('workflow-state', tpl);
			stateName.textContent = workflow['STATE_TITLE'] ? workflow['STATE_TITLE'] : workflow['STATE_NAME'];


			this.initWorkflowNode(tpl);
			return tpl;
		},
		initEventsApplyButton: function()
		{
			var button = this.getNode('events-apply-button');
			BX.bind(button, 'click', this.onApplyEventsClick.bind(this));
		},

		initStartButton: function()
		{
			var button = this.getNode('start-button');
			if (button)
			{
				var Starter = new BX.Bizproc.Starter({
					moduleId: this.config.moduleId,
					entity: this.config.entity,
					documentType: this.config.documentType,
					documentId: this.config.documentId
				});

				BX.addCustomEvent(Starter, 'onAfterStartWorkflow', this.reloadWorkflows.bind(this));
				BX.bind(button, 'click', this.onStartClick.bind(this, button, Starter));
			}
		},


		initWorkflowNode: function(node)
		{
			var workflowId = node.getAttribute('data-workflow-id');
			var killButton = this.getNode('kill', node);
			if (killButton)
			{
				BX.bind(killButton, 'click', this.onKillWorkflowClick.bind(this, workflowId, node))
			}
			var terminateButton = this.getNode('terminate', node);
			if (terminateButton)
			{
				BX.bind(terminateButton, 'click', this.onTerminateWorkflowClick.bind(this, workflowId, node))
			}
			var logButton = this.getNode('log', node);
			if (logButton)
			{
				BX.bind(logButton, 'click', this.onLogClick.bind(this, workflowId))
			}
		},

		onKillWorkflowClick: function(workflowId, workflowNode, e)
		{
			e.preventDefault();
			var me = this;
			this.callAction('kill_workflow', {workflow_id: workflowId}, function(data)
			{
				if (data.workflows)
				{
					me.renderWorkflows(data.workflows);
				}
			});
		},
		onTerminateWorkflowClick: function(workflowId, workflowNode, e)
		{
			e.preventDefault();
			var me = this;
			this.callAction('terminate_workflow', {workflow_id: workflowId}, function(data)
			{
				if (data.workflows)
				{
					me.renderWorkflows(data.workflows);
				}
			});
		},

		onApplyEventsClick: function(e)
		{
			e.preventDefault();

			var form = this.getNode('form');
			var eventsNodes = this.getNodes('events-select', form);
			var hasEvents = false, events = {};

			for (var i = 0; i < eventsNodes.length; ++i)
			{
				var value = eventsNodes[i].value;
				if (value !== '')
				{
					events[eventsNodes[i].getAttribute('workflow-id')] = value;
					hasEvents = true;
				}
			}

			if (hasEvents)
			{
				var me = this;
				this.callAction('send_events', {events: events}, function(data)
				{
					if (data.workflows)
					{
						me.renderWorkflows(data.workflows);
					}
				});
			}
		},

		onStartClick: function(button, starter, e)
		{
			e.preventDefault();
			starter.showTemplatesMenu(button);
		},

		onTaskLinkClick: function(task, e)
		{
			e.preventDefault();
			if (BX.Bizproc && BX.Bizproc.showTaskPopup)
			{
				BX.Bizproc.showTaskPopup(task['ID'], this.reloadWorkflows.bind(this));
			}
		},

		onLogClick: function(workflowId, e)
		{
			e.preventDefault();

			if (top.BX.Bitrix24 && top.BX.Bitrix24.Slider)
			{
				top.BX.Bitrix24.Slider.open(
					'/bitrix/components/bitrix/bizproc.log/slider.php?site_id='+BX.message('SITE_ID')+'&WORKFLOW_ID=' + workflowId
				)
			}
			else if (BX.Bizproc && BX.Bizproc.showWorkflowLogPopup)
			{
				BX.Bizproc.showWorkflowLogPopup(workflowId, {title: BX.message('IBEL_BIZPROC_LOG_TITLE')});
			}
		},

		reloadWorkflows: function()
		{
			var me = this;
			this.callAction('get_workflows', {}, function(data)
			{
				if (data.workflows)
				{
					me.renderWorkflows(data.workflows);
				}
			});
		},

		callAction: function(actionName, params, callback)
		{
			this.showWait();
			params['sessid'] = BX.bitrix_sessid();
			params['site'] = BX.message('SITE_ID');
			params['ajax_action'] = actionName;
			params['module_id'] = this.config.moduleId;
			params['entity'] = this.config.entity;
			params['document_type'] = this.config.documentType;
			params['document_id'] = this.config.documentId;

			var me = this;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.config.serviceUrl,
				data: params,
				onsuccess: function(responce)
				{
					if (responce.success)
					{
						callback(responce.data, responce);
					}
					else
					{
						window.alert(responce.errors.join('\n'));
					}
					me.hideWait();
				},
				onfailure: function()
				{
					me.hideWait();
				}
			});
		},
		showWait: function()
		{
			BX.addClass(this.node, 'bizproc-document-wait');
		},
		hideWait: function()
		{
			BX.removeClass(this.node, 'bizproc-document-wait');
		}
	};

	BX.Bizproc.DocumentComponent = Component;
})();