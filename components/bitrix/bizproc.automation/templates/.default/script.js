;if (!BX.getClass('BX.Bizproc.Automation.Component')) (function(BX)
{
	'use strict';
	BX.namespace('BX.Bizproc.Automation');

	var Component = function(baseNode)
	{
		if (!BX.type.isDomNode(baseNode))
			throw 'baseNode must be Dom Node Element';

		this.node = baseNode;

		//set current instance
		Designer.component = this;
	};

	Component.ViewMode = {
		None: 0,
		View : 1,
		Edit: 2
	};

	Component.LogStatus = {
		Waiting : 0,
		Running: 1,
		Completed: 2,
		AutoCompleted: 3
	};

	Component.idIncrement = 0;
	Component.generateUniqueId = function()
	{
		++Component.idIncrement;
		return 'bizproc-automation-cmp-' + Component.idIncrement;
	};

	var getAjaxUrl = function(url)
	{
		url = url || '/bitrix/components/bitrix/bizproc.automation/ajax.php';
		return  BX.util.add_url_param(url, {
			site_id: BX.message('SITE_ID'),
			sessid: BX.bitrix_sessid()
		});
	};

	var toJsonString = function(data)
	{
		return JSON.stringify(data, function (i, v)
		{
			if (typeof(v) == 'boolean')
			{
				return v ? '1' : '0';
			}
			return v;
		});
	}

	var getResponsibleUserExpression = function(fields)
	{
		var exp;

		if (BX.type.isArray(fields))
		{
			var i, field;
			for (i = 0; i < fields.length; ++i)
			{
				field = fields[i];
				if (field['Id'] === 'ASSIGNED_BY_ID' || field['Id'] === 'RESPONSIBLE_ID')
				{
					exp = '{{'+field['Name']+'}}';
					break;
				}
			}
		}
		return exp;
	};

	var systemExpressionPattern = '\\{=\\s*(?<object>[a-z0-9_]+)\\s*\\:\\s*(?<field>[a-z0-9_\\.]+)(\\s*>\\s*(?<mod1>[a-z0-9_\\:]+)(\\s*,\\s*(?<mod2>[a-z0-9_]+))?)?\\s*\\}';

	Component.prototype =
	{
		init: function(data, viewMode)
		{
			var me = this;

			this.viewMode = viewMode || Component.ViewMode.View;

			if (typeof data === 'undefined')
				data = {};

			this.data = data;
			this.initData();

			this.initTracker();
			this.initTriggerManager();
			this.initTemplateManager();
			this.initButtons();
			this.initButtonsPosition();
			this.initHelpTips();
			this.setTitle();
			this.fixTitleColors();

			if (!this.embeddedMode)
			{
				window.onbeforeunload = function()
				{
					if (me.templateManager.needSave() || me.triggerManager.needSave())
					{
						return BX.message('BIZPROC_AUTOMATION_CMP_NEED_SAVE');
					}
				};
			}
		},
		initData: function()
		{
			this.documentType = this.data.DOCUMENT_TYPE;
			this.documentId = this.data.DOCUMENT_ID;
			this.documentCategoryId = this.data.DOCUMENT_CATEGORY_ID;
			this.documentSigned = this.data.DOCUMENT_SIGNED;

			this.bizprocEditorUrl = this.data.WORKFLOW_EDIT_URL;
			this.constantsEditorUrl = this.data.CONSTANTS_EDIT_URL || null;
			this.parametersEditorUrl = this.data.PARAMETERS_EDIT_URL || null;

			this.documentStatuses = this.data.DOCUMENT_STATUS_LIST;
			this.statusesSort = [];
			for(var i = 0; i < this.documentStatuses.length; ++i)
			{
				this.statusesSort.push(this.documentStatuses[i]['STATUS_ID']);
			}
			this.setDocumentStatus(this.data.DOCUMENT_STATUS);

			this.userOptions = {};
			if (BX.type.isPlainObject(this.data.USER_OPTIONS))
			{
				this.userOptions = this.data.USER_OPTIONS;
			}
			this.frameMode = BX.type.isBoolean(this.data.FRAME_MODE) ? this.data.FRAME_MODE : false;
			this.embeddedMode = (this.data.IS_EMBEDDED === true);
		},
		setDocumentStatus: function(status)
		{
			this.documentStatus = status;
			this.currentStatusIndex = -1;

			for(var i = 0; i < this.statusesSort.length; ++i)
			{
				if (this.statusesSort[i] == status)
				{
					this.currentStatusIndex = i;
					break;
				}
			}

			return this;
		},
		isPreviousStatus: function(needle)
		{
			var needleIndex = 0;
			for (var i = 0; i < this.statusesSort.length; ++i)
			{
				if (needle == this.statusesSort[i])
					needleIndex = i;
			}
			return this.currentStatusIndex > -1 && needleIndex < this.currentStatusIndex;
		},
		isCurrentStatus: function(needle)
		{
			return needle == this.documentStatus;
		},
		isNextStatus: function(needle)
		{
			var needleIndex = 0;
			for (var i = 0; i < this.statusesSort.length; ++i)
			{
				if (needle == this.statusesSort[i])
					needleIndex = i;
			}
			return this.currentStatusIndex > -1 && needleIndex > this.currentStatusIndex;
		},
		initTriggerManager: function()
		{
			this.triggerManager = new TriggerManager(this);
			this.triggerManager.init(this.data, this.viewMode);
		},
		reInitTriggerManager: function(triggers)
		{
			if (BX.type.isArray(triggers))
				this.data.TRIGGERS = triggers;
			this.triggerManager.reInit(this.data, this.viewMode);
		},
		initTemplateManager: function()
		{
			this.templateManager = new TemplateManager(this);
			this.templateManager.init(this.data, this.viewMode);
		},
		reInitTemplateManager: function(templates)
		{
			if (BX.type.isArray(templates))
				this.data.TEMPLATES = templates;
			this.templateManager.reInit(this.data, this.viewMode);
		},
		initButtons: function()
		{
			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (buttonsNode)
			{
				if (this.viewMode === Component.ViewMode.View)
				{
					BX.hide(buttonsNode);
				}
				this.bindSaveButton();
				this.bindCancelButton();
			}
			this.bindChangeViewButton();
		},
		initButtonsPosition: function()
		{
			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (buttonsNode)
			{
				if (this.frameMode)
				{
					BX.addClass(buttonsNode, 'bizproc-automation-buttons-fixed-slider');
				}
			}
		},
		initHelpTips: function()
		{
			BX.UI.Hint.init(this.node);
		},
		reInitButtons: function()
		{
			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');
			if (buttonsNode && this.viewMode === Component.ViewMode.View)
			{
				BX.hide(buttonsNode);
			}
			else if (buttonsNode && this.viewMode === Component.ViewMode.Edit)
			{
				BX.show(buttonsNode);
			}

			var changeViewBtn = this.node.querySelector('[data-role="automation-btn-change-view"]');
			if (changeViewBtn)
			{
				changeViewBtn.innerHTML = changeViewBtn.getAttribute('data-label-'
					+(this.viewMode === Component.ViewMode.View ? 'edit' : 'view'));
			}
		},
		setTitle: function()
		{
			var titleNode = this.node.querySelector('[data-role="automation-title"]');
			if (titleNode)
			{
				titleNode.innerHTML = titleNode.getAttribute('data-title-'
					+(this.viewMode === Component.ViewMode.View ? 'view' : 'edit')
				);
			}
		},
		fixTitleColors: function()
		{
			var i, bgcolor, titles = this.node.querySelectorAll('[data-role="automation-status-title"]');
			for (i = 0; i < titles.length; ++i)
			{
				bgcolor = titles[i].getAttribute('data-bgcolor');
				if (bgcolor)
				{
					var bigint = parseInt(bgcolor, 16);
					var r = (bigint >> 16) & 255;
					var g = (bigint >> 8) & 255;
					var b = bigint & 255;
					var y = 0.21 * r + 0.72 * g + 0.07 * b;

					if (y < 145) // dark background
					{
						titles[i].style.color =  'white';
					}
				}
			}
		},
		initTracker: function()
		{
			this.tracker = new Tracker(this);
			this.tracker.init(this.data.LOG);
		},
		bindSaveButton: function()
		{
			var me = this, button = BX('ui-button-panel-save');

			if (button)
			{
				BX.bind(button, 'click', function(e)
				{
					e.preventDefault();
					me.saveAutomation();
					button.classList.remove('ui-btn-wait');
				});
			}
		},
		bindCancelButton: function()
		{
			var me = this, button = this.node.querySelector('[data-role="automation-btn-cancel"]');

			if (button)
			{
				BX.bind(button, 'click', function(e)
				{
					e.preventDefault();
					me.changeViewMode(Component.ViewMode.View, true);
				});
			}
		},
		bindChangeViewButton: function()
		{
			var me = this, button = this.node.querySelector('[data-role="automation-btn-change-view"]');

			if (button)
			{
				button.innerHTML = button.getAttribute('data-label-'
						+(this.viewMode === Component.ViewMode.View ? 'edit' : 'view'));

				if (me.canEdit())
				{
					BX.bind(button, 'click', function(e)
					{
						e.preventDefault();
						var viewMode = me.viewMode === Component.ViewMode.Edit?
							Component.ViewMode.View : Component.ViewMode.Edit;

						me.changeViewMode(viewMode);
					});
				}
			}
		},
		getAjaxUrl: function()
		{
			return getAjaxUrl(this.data.AJAX_URL);
		},
		getLimits: function()
		{
			var limit = this.data['ROBOTS_LIMIT'];
			if (limit <= 0)
			{
				return false;
			}

			var triggersCnt = this.triggerManager.countAllTriggers();
			var robotsCnt = this.templateManager.countAllRobots();

			return (triggersCnt + robotsCnt > limit) ? [limit, triggersCnt, robotsCnt] : false;
		},
		saveAutomation: function(callback)
		{
			if (this.savingAutomation)
			{
				return;
			}

			var limits = this.getLimits();

			if (limits)
			{
				if (top.BX.UI && top.BX.UI.InfoHelper)
				{
					top.BX.UI.InfoHelper.show('limit_crm_robots');
					return;
				}

				BX.UI.Dialogs.MessageBox.show({
					title: BX.message('BIZPROC_AUTOMATION_ROBOTS_LIMIT_ALERT_TITLE'),
					message: BX.message('BIZPROC_AUTOMATION_ROBOTS_LIMIT_SAVE_ALERT')
								.replace('#LIMIT#', limits[0])
								.replace('#SUM#', limits[1] + limits[2])
								.replace('#TRIGGERS#', limits[1])
								.replace('#ROBOTS#', limits[2]),
					modal: true,
					buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
					okCaption: BX.message('BIZPROC_AUTOMATION_CLOSE_CAPTION')
				});
				return;
			}

			var me = this, data = {
				ajax_action: 'save_automation',
				document_signed: this.documentSigned,
				triggers_json: toJsonString(this.triggerManager.serialize()),
				templates_json: toJsonString(this.templateManager.serialize())
			};

			this.savingAutomation = true;
			return BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.getAjaxUrl(),
				data: data,
				onsuccess: function(response)
				{
					me.savingAutomation = null;
					if (response.SUCCESS)
					{
						me.reInitTemplateManager(response.DATA.templates);
						me.reInitTriggerManager(response.DATA.triggers);
						me.changeViewMode(Component.ViewMode.View);
						if (callback)
						{
							callback(response.DATA)
						}
					}
					else
					{
						alert(response.ERRORS[0]);
					}
				}
			});
		},
		changeViewMode: function(mode, silent)
		{
			if (!silent && (this.templateManager.needSave() || this.triggerManager.needSave()))
			{
				alert(BX.message('BIZPROC_AUTOMATION_CMP_NEED_SAVE'));
				return;
			}

			if (mode !== Component.ViewMode.View && mode !== Component.ViewMode.Edit)
				throw 'Unknown view mode';

			this.viewMode = mode;

			this.reInitTriggerManager();
			this.reInitTemplateManager();
			this.reInitButtons();
			this.setTitle();
		},
		canEdit: function()
		{
			return this.data['CAN_EDIT'];
		},
		updateTracker: function()
		{
			var me = this;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.getAjaxUrl(),
				data: {
					ajax_action: 'get_log',
					document_signed: this.documentSigned
				},
				onsuccess: function (response)
				{
					if (response.DATA && response.DATA.LOG)
					{
						me.tracker.reInit(response.DATA.LOG);
						if (me.viewMode === Component.ViewMode.View)
						{
							me.templateManager.reInit();
						}
					}
				}
			});
		},
		onGlobalHelpClick: function(e)
		{
			e.preventDefault();
			if (top.BX.Helper)
			{
				top.BX.Helper.show('redirect=detail&code=6908975');
			}
		},
		getUserOption: function(category, key, defaultValue)
		{
			var result = defaultValue;

			if (this.userOptions[category] && this.userOptions[category][key])
			{
				result = this.userOptions[category][key];
			}
			return result;
		},
		setUserOption: function(category, key, value)
		{
			if (!BX.type.isPlainObject(this.userOptions[category]))
			{
				this.userOptions[category] = {};
			}
			var storedValue = this.userOptions[category][key];

			if (storedValue !== value)
			{
				this.userOptions[category][key] = value;
				BX.userOptions.save(
					'bizproc.automation',
					category,
					key,
					value,
					false
				);
			}
			return this;
		},
		getConstants: function()
		{
			if (!this.data.GLOBAL_CONSTANTS)
			{
				return [];
			}
			var constants = [];
			Object.keys(this.data.GLOBAL_CONSTANTS).forEach(function(id)
			{
				var constant = BX.clone(this.data.GLOBAL_CONSTANTS[id]);
				constant.Id = id;
				constant.ObjectId = 'GlobalConst';
				constant.SystemExpression = constant.Expression = '{=GlobalConst:' + id + '}';
				constants.push(constant);
			}, this);
			return constants;
		},
		getConstant: function(id)
		{
			var constants = this.getConstants();
			for (var i = 0; i < constants.length; ++i)
			{
				if (constants[i].Id === id)
				{
					return constants[i];
				}
			}

			return null;
		}
	};

	var TemplateManager = function(component)
	{
		this.component = component;
	};

	TemplateManager.prototype =
	{
		init: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			this.viewMode = viewMode || Component.ViewMode.View;
			this.availableRobots = BX.type.isArray(data.AVAILABLE_ROBOTS) ? data.AVAILABLE_ROBOTS : [];
			this.availableRobotsMap = {};
			for (var i = 0; i < this.availableRobots.length; ++i)
			{
				this.availableRobotsMap[this.availableRobots[i]['CLASS']] = this.availableRobots[i];
			}

			this.templatesData = BX.type.isArray(data.TEMPLATES) ? data.TEMPLATES : [];

			this.initTemplates();
		},
		reInit: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			this.viewMode = viewMode || Component.ViewMode.View;
			if (BX.type.isArray(data.TEMPLATES))
				this.templatesData = data.TEMPLATES;

			this.reInitTemplates(this.templatesData);
		},
		initTemplates: function()
		{
			this.templates = [];
			this.templatesMap = {};

			for (var i = 0; i < this.templatesData.length; ++i)
			{
				var tpl = new Template(this);
				tpl.init(this.templatesData[i], this.viewMode);

				this.templates.push(tpl);
				this.templatesMap[tpl.getStatusId()] = tpl;
			}
		},
		reInitTemplates: function(templates)
		{
			for (var i = 0; i < this.templates.length; ++i)
			{
				if (templates[i])
				{
					this.templates[i].reInit(templates[i], this.viewMode);
				}
			}
		},
		getAvailableRobots: function()
		{
			return this.availableRobots;
		},
		getRobotDescription: function(type)
		{
			return this.availableRobotsMap[type] || null;
		},
		serialize: function()
		{
			var templates = [];

			for (var i = 0; i < this.templates.length; ++i)
			{
				templates.push(this.templates[i].serialize());
			}

			return templates;
		},
		countAllRobots: function()
		{
			var cnt = 0;

			this.templates.forEach(function(template)
			{
				cnt += template.robots.length;
			});
			return cnt;
		},
		getTemplateByColumnNode: function(node)
		{
			var statusId = node.getAttribute('data-status-id');
			return this.getTemplateByStatusId(statusId);
		},
		getTemplateByStatusId: function(statusId)
		{
			return this.templatesMap[statusId] || null;
		},
		needSave: function()
		{
			var modified = false;
			for (var i = 0; i < this.templates.length; ++i)
			{
				if (this.templates[i].isModified())
				{
					modified = true;
					break;
				}
			}
			return modified;
		}
	};

	var Template = function(manager)
	{
		if (manager)
		{
			this.manager = manager;
			this.component = manager.component;
		}

		this.data = {};
	};

	Template.prototype =
	{
		init: function(data, viewMode)
		{
			if (BX.type.isPlainObject(data))
			{
				this.data = data;
				if (!BX.type.isPlainObject(this.data.CONSTANTS))
				{
					this.data.CONSTANTS = {};
				}
				if (!BX.type.isPlainObject(this.data.PARAMETERS))
				{
					this.data.PARAMETERS = {};
				}
			}

			this.modified = false;
			this.viewMode = (viewMode === undefined) ? Component.ViewMode.View : viewMode;

			if (this.viewMode !== Component.ViewMode.None)
			{
				this.node = this.component.node.querySelector(
					'[data-role="automation-template"][data-status-id="'+this.getStatusId()+'"]'
				);
				this.listNode = this.node.querySelector('[data-role="robot-list"]');
				this.buttonsNode = this.node.querySelector('[data-role="buttons"]');
				this.initRobots();
				this.initButtons();

				if (!this.isExternalModified())
				{
					//register DD
					jsDD.registerDest(this.node, 10);
				}
			}
		},
		reInit: function(data, viewMode)
		{
			BX.cleanNode(this.listNode);
			BX.cleanNode(this.buttonsNode);

			this.init(data, viewMode)
		},
		initRobots: function()
		{
			this.robots = [];
			this.robotsMap = {};
			if (BX.type.isArray(this.data.ROBOTS))
			{
				for (var i = 0; i < this.data.ROBOTS.length; ++i)
				{
					var robot = new Robot(this);
					robot.init(this.data.ROBOTS[i], this.viewMode);
					this.insertRobotNode(robot.node);
					this.robots.push(robot);
					this.robotsMap[robot.getId()] = robot;
				}
			}
		},
		getStatusId: function()
		{
			return this.data.DOCUMENT_STATUS;
		},
		getTemplateId: function()
		{
			var id = parseInt(this.data.ID);
			return !isNaN(id) ? id : 0;
		},
		initButtons: function()
		{
			if (this.isExternalModified())
			{
				this.createExternalLocker();
			}
			else if (this.viewMode === Component.ViewMode.Edit)
			{
				if (!this.isExternalModified())
					this.createAddButton();

				if (this.getTemplateId() > 0)
				{
					this.createConstantsEditButton();
					this.createParametersEditButton();
					this.createExternalEditTemplateButton();
				}
			}

			if (this.viewMode === Component.ViewMode.View && this.component.canEdit())
			{
				this.createEditButton();
			}
		},
		createAddButton: function()
		{
			var me = this,
				anchor = BX.create('a', {
							text: BX.message('BIZPROC_AUTOMATION_CMP_ADD'),
							props: {
								href: '#'
							},
							events: {
								click: function(e)
								{
									e.preventDefault();
									me.onAddButtonClick(this);
								}
							},
							attrs:{
								className: 'bizproc-automation-robot-btn-add'
							}

						});

			this.buttonsNode.appendChild(anchor);
		},
		createEditButton: function()
		{
			var me = this,
				anchor = BX.create('a', {
					text: BX.message('BIZPROC_AUTOMATION_CMP_AUTOMATION_EDIT'),
					props: {
						href: '#'
					},
					events: {
						click: function(e)
						{
							e.preventDefault();
							me.manager.component.changeViewMode(Component.ViewMode.Edit);
						}
					},
					attrs: { className: "bizproc-automation-robot-btn-set" }
				});
			this.buttonsNode.appendChild(anchor);
		},
		createExternalEditTemplateButton: function()
		{
			if (this.manager.component.bizprocEditorUrl === null)
			{
				return false;
			}

			var url = this.manager.component.bizprocEditorUrl.replace('#ID#', this.getTemplateId());

			var me = this,
				anchor = BX.create('a', {
				text: BX.message('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT'),
				props: {
					href: url
				},
				events: {
					click: function(e)
					{
						if (!url.length)
						{
							e.preventDefault();
							me.onExternalEditTemplateButtonClick(this);
						}
					}
				},
				attrs: {
					className: "bizproc-automation-robot-btn-set",
					target: '_top'
				}
			});

			if (!this.manager.component.bizprocEditorUrl.length)
			{
				BX.addClass(anchor, 'bizproc-automation-robot-btn-set-locked');
			}

			this.buttonsNode.appendChild(anchor);
		},
		createConstantsEditButton: function()
		{
			if (this.manager.component.constantsEditorUrl === null)
			{
				return false;
			}

			var url = this.manager.component.constantsEditorUrl.replace('#ID#', this.getTemplateId());

			if (!url.length)
			{
				return false;
			}

			var me = this,
				anchor = BX.create('a', {
				text: BX.message('BIZPROC_AUTOMATION_CMP_CONSTANTS_EDIT'),
				props: {
					href: url
				},
				attrs: { className: "bizproc-automation-robot-btn-set" }
			});

			this.buttonsNode.appendChild(anchor);
		},
		createParametersEditButton: function()
		{
			if (this.manager.component.parametersEditorUrl === null)
			{
				return false;
			}

			var url = this.manager.component.parametersEditorUrl.replace('#ID#', this.getTemplateId());

			if (!url.length)
			{
				return false;
			}

			var me = this,
				anchor = BX.create('a', {
				text: BX.message('BIZPROC_AUTOMATION_CMP_PARAMETERS_EDIT'),
				props: {
					href: url
				},
				attrs: { className: "bizproc-automation-robot-btn-set" }
			});

			this.buttonsNode.appendChild(anchor);
		},
		createExternalLocker: function()
		{
			var me = this, div = BX.create("div", {
				attrs: {
					className: "bizproc-automation-robot-container"
				},
				children: [
					BX.create('div', {
						attrs: {
							className: 'bizproc-automation-robot-container-wrapper bizproc-automation-robot-container-wrapper-lock'
						},
						children: [
							BX.create("div", {
								attrs: { className: "bizproc-automation-robot-deadline" }
							}),
							BX.create("div", {
								attrs: { className: "bizproc-automation-robot-title" },
								text: BX.message('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT_TEXT')
							})
						]
					})
				]
			});

			if (this.viewMode === Component.ViewMode.Edit)
			{
				var settingsBtn = BX.create('div', {
					attrs: {
						className: 'bizproc-automation-robot-btn-settings'
					},
					text: BX.message('BIZPROC_AUTOMATION_CMP_EDIT')
				});
				BX.bind(div, 'click', function(e)
				{
					me.onExternalEditTemplateButtonClick(this);
				});
				div.appendChild(settingsBtn);
				BX.addClass(div.firstChild, 'bizproc-automation-robot-container-wrapper-border');
				var deleteBtn = BX.create('SPAN', {
					attrs: {
						className: 'bizproc-automation-robot-btn-delete'
					}
				});
				BX.bind(deleteBtn, 'click', function(e)
				{
					e.stopPropagation();
					me.onUnsetExternalModifiedClick(this);
				});
				div.lastChild.appendChild(deleteBtn);
			}

			this.listNode.appendChild(div);
		},
		onAddButtonClick: function(button)
		{
			var me = this, i, j, menuItems = {employee: [], client: [], ads: [], other: []};

			var title, settings, categories, availableRobots = this.manager.getAvailableRobots();
			var menuItemClickHandler = function(e, item)
			{
				var robotData = BX.clone(item.robotData);

				if (
					robotData['ROBOT_SETTINGS']
					&& robotData['ROBOT_SETTINGS']['TITLE_CATEGORY']
					&& robotData['ROBOT_SETTINGS']['TITLE_CATEGORY'][item.category]
				)
				{
					robotData['NAME'] = robotData['ROBOT_SETTINGS']['TITLE_CATEGORY'][item.category];
				}
				else if (robotData['ROBOT_SETTINGS'] && robotData['ROBOT_SETTINGS']['TITLE'])
				{
					robotData['NAME'] = robotData['ROBOT_SETTINGS']['TITLE'];
				}

				me.addRobot(robotData, function(robot)
				{
					me.openRobotSettingsDialog(robot, {ADD_MENU_CATEGORY: item.category});
				});

				this.getRootMenuWindow().close();
			};

			for (i = 0; i < availableRobots.length; ++i)
			{
				if (availableRobots[i]['EXCLUDED'])
				{
					continue;
				}
				settings =
					BX.type.isPlainObject(availableRobots[i]['ROBOT_SETTINGS'])
						? availableRobots[i]['ROBOT_SETTINGS']
						: {}
				;

				title = availableRobots[i].NAME;
				if (settings['TITLE'])
					title = settings['TITLE'];

				categories = [];
				if (settings['CATEGORY'])
				{
					categories = BX.type.isArray(settings['CATEGORY']) ? settings['CATEGORY'] : [settings['CATEGORY']];
				}

				if (!categories.length)
				{
					categories.push('other');
				}

				for (j = 0; j < categories.length; ++j)
				{
					if (!menuItems[categories[j]])
						continue;

					menuItems[categories[j]].push({
						text: title,
						robotData: availableRobots[i],
						category: categories[j],
						onclick: menuItemClickHandler
					});
				}
			}

			if (menuItems['other'].length > 0)
			{
				menuItems['other'].push({delimiter: true});
			}

			if (BX.getClass('BX.rest.Marketplace'))
			{
				menuItems['other'].push({
					text: BX.message('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE_2'),
					onclick: function()
					{
						BX.rest.Marketplace.open({}, me.component.data['MARKETPLACE_ROBOT_CATEGORY']);
						this.getRootMenuWindow().close();
					}
				});
			}
			else
			{
				menuItems['other'].push({
					text: BX.message('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE_2'),
					href:
						'/marketplace/category/%category%/'
						.replace('%category%', this.component.data['MARKETPLACE_ROBOT_CATEGORY']),
					target: '_blank'
				});
			}

			var menuId = button.getAttribute('data-menu-id');
			if (!menuId)
			{
				menuId = Component.generateUniqueId();
				button.setAttribute('data-menu-id', menuId);
			}

			var rootMenuItems = [];
			if (menuItems['employee'].length > 0)
			{
				rootMenuItems.push({
					text: BX.message('BIZPROC_AUTOMATION_ROBOT_CATEGORY_EMPLOYEE'),
					items: menuItems['employee']
				});
			}
			if (menuItems['client'].length > 0)
			{
				rootMenuItems.push({
					text: BX.message('BIZPROC_AUTOMATION_ROBOT_CATEGORY_CLIENT'),
					items: menuItems['client']
				});
			}
			if (menuItems['ads'].length > 0)
			{
				rootMenuItems.push({
					text: BX.message('BIZPROC_AUTOMATION_ROBOT_CATEGORY_ADS'),
					items: menuItems['ads']
				});
			}
			rootMenuItems.push({
				text: BX.message('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER'),
				items: menuItems['other']
			});

			BX.PopupMenu.show(
				menuId,
				button,
				rootMenuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(button)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					maxHeight: 550
				}
			);
		},
		onExternalEditTemplateButtonClick: function(button)
		{
			if (!this.manager.component.bizprocEditorUrl.length)
			{
				if (BX.getClass('B24.licenseInfoPopup'))
				{
					B24.licenseInfoPopup.show(
						'bizproc_automation_designer',
						BX.message('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT'),
						BX.message('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT_LOCKED')
					);
				}
				return;
			}

			var templateId = this.getTemplateId();
			if (templateId > 0)
				this.openBizprocEditor(templateId);
		},
		onUnsetExternalModifiedClick: function(button)
		{
			this.data['IS_EXTERNAL_MODIFIED'] = false;
			this.reInit(null, this.viewMode);
		},
		openBizprocEditor: function(templateId)
		{
			var url = this.manager.component.bizprocEditorUrl.replace('#ID#', templateId);
			top.window.location.href = url;
		},
		addRobot: function(robotData, callback)
		{
			var robot = new Robot(this);
			var initData = {
				Type: robotData['CLASS'],
				Properties: {
					Title: robotData['NAME']
				}
			};

			if (this.robots.length > 0)
			{
				var parentRobot = this.robots[this.robots.length - 1];
				if (!parentRobot.delay.isNow() || parentRobot.isExecuteAfterPrevious())
				{
					initData['Delay'] = parentRobot.delay.serialize();
					initData['ExecuteAfterPrevious'] =  1;
				}
			}

			robot.init(initData, this.viewMode);
			robot.draft = true;
			if (callback)
				callback(robot);
		},
		insertRobot: function(robot, beforeRobot)
		{
			if (beforeRobot)
			{
				for (var i = 0; i < this.robots.length; ++i)
				{
					if (this.robots[i] !== beforeRobot)
						continue;
					this.robots.splice(i, 0, robot);
					break;
				}
			}
			else
			{
				this.robots.push(robot);
			}
			this.modified = true;
		},
		getNextRobot: function(robot)
		{
			for (var i = 0; i < this.robots.length; ++i)
			{
				if (this.robots[i] === robot)
				{
					return (this.robots[i + 1] || null);
				}
			}
			return null;
		},
		deleteRobot: function(robot, callback)
		{
			for(var i = 0; i < this.robots.length; ++i)
			{
				if (this.robots[i] === robot)
				{
					this.robots.splice(i, 1);
					break;
				}
			}
			if (callback)
				callback(robot);
			this.modified = true;
		},
		insertRobotNode: function(robotNode, beforeNode)
		{
			if (beforeNode)
			{
				this.listNode.insertBefore(robotNode, beforeNode);
			}
			else
			{
				this.listNode.appendChild(robotNode);
			}
		},
		/**
		 * @param {Robot} robot
		 * @param {Object} [context]
		 * @param {Function} saveCallback
		 */
		openRobotSettingsDialog: function(robot, context, saveCallback)
		{
			if (Designer.getRobotSettingsDialog())
				return;

			var me = this, formName = 'bizproc_automation_robot_dialog';

			var form = BX.create('form', {
				props: {
					name: formName
				}
			});

			if (!BX.type.isPlainObject(context))
			{
				context = {};
			}

			Designer.setRobotSettingsDialog({
				template: this,
				context: context,
				robot: robot,
				form: form
			});

			form.appendChild(me.renderDelaySettings(robot));
			form.appendChild(me.renderConditionSettings(robot));

			if (this.component)
			{
				var iconHelp = BX.create('div', {
					attrs: { className: 'bizproc-automation-robot-help' },
					events: {click: BX.delegate(this.component.onGlobalHelpClick, this.component)}
				});
				form.appendChild(iconHelp);
				context['DOCUMENT_CATEGORY_ID'] = this.component.documentCategoryId;
			}

			BX.ajax({
				method: 'POST',
				dataType: 'html',
				url: this.component ? this.component.getAjaxUrl() : getAjaxUrl(),
				data: {
					ajax_action: 'get_robot_dialog',
					document_signed: this.component ? this.component.documentSigned : this.data['DOCUMENT_SIGNED'],
					document_status: this.component ? this.component.documentStatus : this.data['DOCUMENT_STATUS'],
					context: context,
					robot_json: toJsonString(robot.serialize()),
					form_name: formName
				},
				onsuccess: function(html)
				{
					if (html)
					{
						var dialogRows = BX.create('div', {
							html: html
						});
						form.appendChild(dialogRows);
					}
					me.showRobotSettingsPopup(robot, form, saveCallback);
				}
			});
		},
		/**
		 * @param {Robot} robot
		 * @param {Element} form
		 * @param {Function} saveCallback
		 */
		showRobotSettingsPopup: function(robot, form, saveCallback)
		{
			var popupMinWidth = 580;
			var popupWidth = popupMinWidth;

			if (this.component)
			{
				BX.addClass(this.component.node, 'automation-base-blocked');
				popupWidth = parseInt(this.component.getUserOption('defaults', 'robot_settings_popup_width', 580));
			}

			this.initRobotSettingsControls(robot, form);

			if (
				robot.data.Type === 'CrmSendEmailActivity'
				|| robot.data.Type === 'MailActivity'
				|| robot.data.Type === 'RpaApproveActivity'
			)
			{
				popupMinWidth += 140;
				if (popupWidth < popupMinWidth)
				{
					popupWidth = popupMinWidth;
				}
			}

			var me = this, popup = new BX.PopupWindow(Component.generateUniqueId(), null, {
				titleBar: robot.component ? robot.getTitle() : BX.message('BIZPROC_AUTOMATION_ROBOT_SETTINGS_TITLE'),
				content: form,
				closeIcon: true,
				width: popupWidth,
				resizable: {
					minWidth: popupMinWidth,
					minHeight: 100
				},
				offsetLeft: 0,
				offsetTop: 0,
				closeByEsc: true,
				draggable: {restrict: false},
				events: {
					onPopupClose: function(popup)
					{
						me.currentRobot = null;
						Designer.setRobotSettingsDialog(null);
						me.destroyRobotSettingsControls();
						popup.destroy();
						if (me.component)
						{
							BX.removeClass(me.component.node, 'automation-base-blocked');
						}
					},
					onPopupResize: function()
					{
						me.onResizeRobotSettings();
					},
					onPopupResizeEnd: function() {
						if (me.component)
						{
							me.component.setUserOption(
								'defaults',
								'robot_settings_popup_width',
								this.getWidth()
							);
						}
					}
				},
				buttons: [
					new BX.PopupWindowButton({
						text : BX.message('JS_CORE_WINDOW_SAVE'),
						className : "popup-window-button-accept",
						events : {
							click: function() {
								me.saveRobotSettings(form, robot, BX.delegate(function()
								{
									this.popupWindow.close();
									if (saveCallback)
									{
										saveCallback(robot);
									}
								}, this), this.buttonNode);
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('JS_CORE_WINDOW_CANCEL'),
						className : "popup-window-button-link-cancel",
						events : {
							click: function(){
								this.popupWindow.close()
							}
						}
					})
				]
			});

			me.currentRobot = robot;
			Designer.getRobotSettingsDialog().popup = popup;
			popup.show();
		},
		initRobotSettingsControls: function(robot, node)
		{
			if (!BX.type.isArray(this.robotSettingsControls))
			{
				this.robotSettingsControls = [];
			}

			var controlNodes = node.querySelectorAll('[data-role]');
			for (var i = 0; i < controlNodes.length; ++i)
			{
				this.initRobotSettingsControl(robot, controlNodes[i]);
			}
		},
		initRobotSettingsControl: function(robot, controlNode)
		{
			if (!BX.type.isArray(this.robotSettingsControls))
			{
				this.robotSettingsControls = [];
			}

			var control = null;
			var role = controlNode.getAttribute('data-role');

			if (role === 'user-selector')
			{
				control = new UserSelector(robot, controlNode, this.data);
			}
			else if (role === 'file-selector')
			{
				control = new FileSelector(robot, controlNode);
			}
			else if (role === 'inline-selector-target')
			{
				control = new InlineSelector(robot, controlNode, this.data);
			}
			else if (role === 'inline-selector-html')
			{
				control = new InlineSelectorHtml(robot, controlNode);
			}
			else if (role === 'time-selector')
			{
				control = new TimeSelector(controlNode);
			}
			else if (role === 'save-state-checkbox')
			{
				control = new SaveStateCheckbox(controlNode, robot);
			}

			BX.UI.Hint.init(controlNode);

			if (control)
			{
				this.robotSettingsControls.push(control);
			}
		},
		destroyRobotSettingsControls: function ()
		{
			if (this.conditionSelector)
			{
				this.conditionSelector.destroy();
				this.conditionSelector = null;
			}
			if (BX.type.isArray(this.robotSettingsControls))
			{
				for (var i = 0; i < this.robotSettingsControls.length; ++i)
				{
					if (BX.type.isFunction(this.robotSettingsControls[i].destroy))
						this.robotSettingsControls[i].destroy();
				}
			}
			this.robotSettingsControls = null;
		},
		onBeforeSaveRobotSettings: function ()
		{
			if (BX.type.isArray(this.robotSettingsControls))
			{
				for (var i = 0; i < this.robotSettingsControls.length; ++i)
				{
					if (BX.type.isFunction(this.robotSettingsControls[i].onBeforeSave))
						this.robotSettingsControls[i].onBeforeSave();
				}
			}
		},
		onResizeRobotSettings: function ()
		{
			if (BX.type.isArray(this.robotSettingsControls))
			{
				for (var i = 0; i < this.robotSettingsControls.length; ++i)
				{
					if (BX.type.isFunction(this.robotSettingsControls[i].onPopupResize))
						this.robotSettingsControls[i].onPopupResize();
				}
			}
		},
		/**
		 * @param {Robot} robot
		 */
		renderDelaySettings: function(robot)
		{
			var delay = BX.clone(robot.getDelayInterval());
			var idSalt = Component.generateUniqueId();

			var delayTypeNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_type",
					value: delay.type
				}
			});
			var delayValueNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_value",
					value: delay.value
				}
			});
			var delayValueTypeNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_value_type",
					value: delay.valueType
				}
			});
			var delayBasisNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_basis",
					value: delay.basis
				}
			});
			var delayWorkTimeNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_worktime",
					value: delay.workTime ? 1 : 0
				}
			});

			var delayIntervalLabelNode = BX.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
				}
			});

			var basisFields = [];

			var docFields = this.component ? this.component.data['DOCUMENT_FIELDS'] : this.data['DOCUMENT_FIELDS'];
			var minLimitM = this.component ? this.component.data['DELAY_MIN_LIMIT_M'] : this.data['DELAY_MIN_LIMIT_M'];

			if (BX.type.isArray(docFields))
			{
				var i, field;
				for (i = 0; i < docFields.length; ++i)
				{
					field = docFields[i];
					if (field['Type'] == 'date' || field['Type'] == 'datetime')
					{
						basisFields.push(field);
					}
				}
			}

			var delayIntervalSelector = new DelayIntervalSelector({
				labelNode: delayIntervalLabelNode,
				onchange: function(delay)
				{
					delayTypeNode.value = delay.type;
					delayValueNode.value = delay.value;
					delayValueTypeNode.value = delay.valueType;
					delayBasisNode.value = delay.basis;
					delayWorkTimeNode.value = delay.workTime ? 1 : 0;
				},
				basisFields: basisFields,
				minLimitM: minLimitM,
				useAfterBasis: true
			});

			var executeAfterPreviousBlock = null;
			if (robot.component)
			{
				var executeAfterPreviousCheckbox = BX.create("input", {
					attrs: {
						type: "checkbox",
						id: "param-group-3-1" + idSalt,
						name: "execute_after_previous",
						value: '1',
						style: 'vertical-align: middle'
					}
				});
				if (robot.isExecuteAfterPrevious())
				{
					executeAfterPreviousCheckbox.setAttribute('checked', 'checked');
				}
				executeAfterPreviousBlock = BX.create("div", {
					attrs: { className: "bizproc-automation-popup-settings-block" },
					children: [
						executeAfterPreviousCheckbox,
						BX.create("label", {
							attrs: {
								for: "param-group-3-1" + idSalt,
								style: 'color: #535C69'
							},
							text: BX.message('BIZPROC_AUTOMATION_CMP_AFTER_PREVIOUS_WIDE')
						})
					]
				})
			}

			var div = BX.create("div", {
				attrs: { className: "bizproc-automation-popup-settings bizproc-automation-popup-settings-flex" },
				children: [
					BX.create("div", {
						attrs: { className: "bizproc-automation-popup-settings-block bizproc-automation-popup-settings-block-flex" },
						children: [
							BX.create("span", {
								attrs: { className: "bizproc-automation-popup-settings-title-wrapper" },
								children: [
									delayTypeNode,
									delayValueNode,
									delayValueTypeNode,
									delayBasisNode,
									delayWorkTimeNode,
									BX.create("span", {
										attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-left" },
										text: BX.message('BIZPROC_AUTOMATION_CMP_TO_EXECUTE') + ":"
									}),
									delayIntervalLabelNode
								]
							})
						]
					}),
					executeAfterPreviousBlock
				]
			});

			delayIntervalSelector.init(delay);

			return div;
		},
		/**
		 * @param {Object} formFields
		 * @param {Robot} robot
		 * @returns {*}
		 */
		setDelaySettingsFromForm: function(formFields,  robot)
		{
			var delay = new DelayInterval();
			delay.setType(formFields['delay_type']);
			delay.setValue(formFields['delay_value']);
			delay.setValueType(formFields['delay_value_type']);
			delay.setBasis(formFields['delay_basis']);
			delay.setWorkTime(formFields['delay_worktime'] === '1');
			robot.setDelayInterval(delay);

			if (robot.component)
			{
				robot.setExecuteAfterPrevious(
					formFields['execute_after_previous'] && (formFields['execute_after_previous']) === '1'
				);
			}

			return this;
		},
		/**
		 * @param {Robot} robot
		 */
		renderConditionSettings: function(robot)
		{
			/** @var {ConditionGroup} conditionGroup */
			var conditionGroup = robot.getCondition();
			var selector = this.conditionSelector = new ConditionGroupSelector(conditionGroup, {
				fields: this.component ? this.component.data['DOCUMENT_FIELDS'] : this.data['DOCUMENT_FIELDS']
			});

			return BX.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [
					BX.create("div", {
						attrs: { className: "bizproc-automation-popup-settings-block" },
						children: [
							BX.create("span", {
								attrs: { className: "bizproc-automation-popup-settings-title" },
								text: BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION') + ":"
							}),
							selector.createNode()
						]
					})
				]
			});
		},
		/**
		 * @param {Object} formFields
		 * @param {Robot} robot
		 * @returns {*}
		 */
		setConditionSettingsFromForm: function(formFields,  robot)
		{
			robot.setCondition(ConditionGroup.createFromForm(formFields));
			return this;
		},
		saveRobotSettings: function(form, robot, callback, btnNode)
		{
			if (btnNode)
			{
				btnNode.classList.add('popup-window-button-wait');
			}

			this.onBeforeSaveRobotSettings();
			var me = this, formData = BX.ajax.prepareForm(form);

			var ajaxUrl = this.component ? this.component.getAjaxUrl() : getAjaxUrl();
			var documentSigned = this.component ? this.component.documentSigned : API.documentSigned;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: ajaxUrl,
				data: {
					ajax_action: 'save_robot_settings',
					document_signed: documentSigned,
					robot_json: toJsonString(robot.serialize()),
					form_data_json: toJsonString(formData['data']),
					form_data: formData['data'] /** @bug 0135641 */
				},
				onsuccess: function(response)
				{
					if (btnNode)
					{
						btnNode.classList.remove('popup-window-button-wait');
					}

					if (response.SUCCESS)
					{
						robot.updateData(response.DATA.robot);
						me.setDelaySettingsFromForm(formData['data'], robot);
						me.setConditionSettingsFromForm(formData['data'], robot);

						if (robot.draft)
						{
							me.robots.push(robot);
							me.insertRobotNode(robot.node)
						}
						delete robot.draft;

						robot.reInit();
						me.modified = true;
						if (callback)
						{
							callback(response.DATA)
						}
					}
					else
						alert(response.ERRORS[0]);
				}
			});
		},
		serialize: function()
		{
			var data = BX.clone(this.data);
			data['IS_EXTERNAL_MODIFIED'] = this.isExternalModified() ? 1 : 0;
			data['ROBOTS'] = [];

			for (var i = 0; i < this.robots.length; ++i)
			{
				data['ROBOTS'].push(this.robots[i].serialize());
			}

			return data;
		},
		isExternalModified: function()
		{
			return (this.data['IS_EXTERNAL_MODIFIED'] === true);
		},
		getRobotById: function(id)
		{
			return this.robotsMap[id] || null;
		},
		isModified: function()
		{
			return this.modified;
		},
		getConstants: function()
		{
			var constants = [];
			Object.keys(this.data.CONSTANTS).forEach(function(id)
			{
				var constant = BX.clone(this.data.CONSTANTS[id]);
				constant.Id = id;
				constant.ObjectId = 'Constant';
				constant.SystemExpression = '{=Constant:' + id + '}';
				constant.Expression = '{{~&:' + id + '}}';
				constants.push(constant);
			}, this);
			return constants;
		},
		getConstant: function(id)
		{
			var constants = this.getConstants();
			for (var i = 0; i < constants.length; ++i)
			{
				if (constants[i].Id === id)
				{
					return constants[i];
				}
			}

			return null;
		},
		addConstant: function(property)
		{
			var id = property.Id || this.generatePropertyId('Constant', this.data.CONSTANTS);

			if (this.data.CONSTANTS[id])
			{
				throw 'Constant with id "'+id+'" is already exists';
			}

			this.data.CONSTANTS[id] = property;

			if (this.component)
			{
				BX.onCustomEvent(this.component, 'onTemplateConstantAdd', [this, this.getConstant(id)]);
			}

			return this.getConstant(id);
		},
		updateConstant: function(id, property)
		{
			if (!this.data.CONSTANTS[id])
			{
				throw 'Constant with id "'+id+'" does not exists';
			}

			//TODO: only Description yet.
			this.data.CONSTANTS[id].Description = property.Description;

			if (this.component)
			{
				BX.onCustomEvent(this.component, 'onTemplateConstantUpdate', [this, this.getConstant(id)]);
			}

			return this.getConstant(id);
		},
		deleteConstant: function(id)
		{
			delete this.data.CONSTANTS[id];
			return true;
		},
		setConstantValue: function(id, value)
		{
			if (this.data.CONSTANTS[id])
			{
				this.data.CONSTANTS[id]['Default'] = value;
				return true;
			}
			return false;
		},
		getParameters: function()
		{
			var params = [];
			Object.keys(this.data.PARAMETERS).forEach(function(id)
			{
				var param = BX.clone(this.data.PARAMETERS[id]);
				param.Id = id;
				param.ObjectId = 'Template';
				param.SystemExpression = '{=Template:' + id + '}';
				param.Expression = '{{~*:' + id + '}}';
				params.push(param);
			}, this);
			return params;
		},
		getParameter: function(id)
		{
			var params = this.getParameters();
			for (var i = 0; i < params.length; ++i)
			{
				if (params[i].Id === id)
				{
					return params[i];
				}
			}

			return null;
		},
		addParameter: function(property)
		{
			var id = property.Id || this.generatePropertyId('Parameter', this.data.PARAMETERS);

			if (this.data.PARAMETERS[id])
			{
				throw 'Parameter with id "'+id+'" is already exists';
			}

			this.data.PARAMETERS[id] = property;

			if (this.component)
			{
				BX.onCustomEvent(this.component, 'onTemplateParameterAdd', [this, this.getParameter(id)]);
			}

			return this.getParameter(id);
		},
		updateParameter: function(id, property)
		{
			if (!this.data.PARAMETERS[id])
			{
				throw 'Parameter with id "'+id+'" does not exists';
			}

			//TODO: only Description yet.
			this.data.PARAMETERS[id].Description = property.Description;

			if (this.component)
			{
				BX.onCustomEvent(this.component, 'onTemplateParameterUpdate', [this, this.getParameter(id)]);
			}

			return this.getParameter(id);
		},
		deleteParameter: function(id)
		{
			delete this.data.PARAMETERS[id];
			return true;
		},
		setParameterValue: function(id, value)
		{
			if (this.data.PARAMETERS[id])
			{
				this.data.PARAMETERS[id]['Default'] = value;
				return true;
			}
			return false;
		},
		generatePropertyId: function(prefix, existsList)
		{
			for(var index = 1; index <= 1000; ++index)
			{
				if (!existsList[prefix + index])
				{
					break; //found
				}
			}

			return prefix + index;
		},
		collectUsages: function()
		{
			var usages = {Document: new Set(), Constant: new Set(), Parameter: new Set()};

			this.robots.forEach(function(robot) {
				var robotUsages = robot.collectUsages();

				Object.keys(usages).forEach(function(key) {
					robotUsages[key].forEach(function(usage) {
						if (!usages[key].has(usage))
						{
							usages[key].add(usage);
						}
					}, this);
				}, this);
			}, this);

			return usages;
		}
	};

	var Robot = function(template)
	{
		if (template)
		{
			this.template = template;
			this.templateManager = template.manager;
			this.component = this.templateManager.component;
			this.tracker = template.manager.component.tracker;
		}
	};

	Robot.generateName = function()
	{
		return 'A' + parseInt(Math.random()*100000)
			+ '_'+parseInt(Math.random()*100000)
			+ '_'+parseInt(Math.random()*100000)
			+ '_'+parseInt(Math.random()*100000);
	};

	Robot.prototype =
	{
		init: function(data, viewMode)
		{
			if (data)
				this.data = data;
			if (!this.data.Name)
			{
				this.data.Name = Robot.generateName();
			}

			this.delay = new DelayInterval(this.data.Delay);
			this.condition = new ConditionGroup(this.data.Condition);
			if (!this.data.Condition)
			{
				this.condition.type = ConditionGroup.Type.Mixed;
			}
			this.viewMode = (viewMode === undefined) ? Component.ViewMode.View : viewMode;
			if (this.viewMode !== Component.ViewMode.None)
			{
				this.node = this.createNode();
			}
		},
		reInit: function(data, viewMode)
		{
			if (viewMode === undefined && this.viewMode === Component.ViewMode.None)
			{
				return;
			}

			var node = this.node;
			this.node = this.createNode();
			if (node.parentNode)
				node.parentNode.replaceChild(this.node, node);
		},
		getProperties: function()
		{
			if (this.data && BX.Type.isPlainObject(this.data.Properties))
			{
				return this.data.Properties;
			}
			return {};
		},
		getProperty: function(name)
		{
			return this.getProperties()[name] || null;
		},
		setProperty: function(name, value)
		{
			this.data.Properties[name] = value;
			return this;
		},
		getId: function()
		{
			return this.data.Name || null;
		},
		getLogStatus: function()
		{
			var status = Component.LogStatus.Waiting;
			var log = this.tracker.getRobotLog(this.getId());
			if (log)
			{
				status = parseInt(log['STATUS']);
			}
			else if (this.data.DelayName)
			{
				//If delay was executed, we can set Running status to parent robot.
				log = this.tracker.getRobotLog(this.data.DelayName);
				if (log && parseInt(log['STATUS']) === Component.LogStatus.Running)
				{
					status = Component.LogStatus.Running;
				}
			}

			return status;
		},
		getLogErrors: function()
		{
			var errors = [], log = this.tracker.getRobotLog(this.getId());
			if (log && log.ERRORS)
			{
				errors = log.ERRORS;
			}

			return errors;
		},
		getDelayNotes: function()
		{
			if (this.data.DelayName)
			{
				var log = this.tracker.getRobotLog(this.data.DelayName);
				if (log && parseInt(log['STATUS']) === Component.LogStatus.Running)
				{
					return log['NOTES'];
				}
			}
			return [];
		},
		createNode: function()
		{
			var me = this, status = this.getLogStatus(), loader;

			var settings = this.getDescriptionSettings();

			var wrapperClass = 'bizproc-automation-robot-container-wrapper';
			if (this.viewMode === Component.ViewMode.Edit)
			{
				wrapperClass += ' bizproc-automation-robot-container-wrapper-draggable';
			}

			var targetLabel = BX.message('BIZPROC_AUTOMATION_CMP_TO');
			var targetNode = BX.create("a", {
				attrs: {
					className: "bizproc-automation-robot-settings-name",
					title: BX.message('BIZPROC_AUTOMATION_CMP_AUTOMATICALLY')
				},
				text: BX.message('BIZPROC_AUTOMATION_CMP_AUTOMATICALLY')
			});

			if (BX.type.isPlainObject(this.data.viewData) && this.data.viewData.responsibleLabel)
			{
				var labelText =
					this.data.viewData.responsibleLabel
						.replace('{=Document:ASSIGNED_BY_ID}', BX.message('BIZPROC_AUTOMATION_CMP_RESPONSIBLE'))
						.replace('author', BX.message('BIZPROC_AUTOMATION_CMP_RESPONSIBLE'))
						.replace(/\{=Constant\:Constant[0-9]+\}/, BX.message('BIZPROC_AUTOMATION_ASK_CONSTANT'))
						.replace(/\{=Template\:Parameter[0-9]+\}/, BX.message('BIZPROC_AUTOMATION_ASK_PARAMETER'))
				;

				if (labelText.indexOf('{=Document') >= 0 && BX.type.isArray(this.component.data['DOCUMENT_FIELDS']))
				{
					var i, field;
					for (i = 0; i < this.component.data['DOCUMENT_FIELDS'].length; ++i)
					{
						field = this.component.data['DOCUMENT_FIELDS'][i];
						labelText = labelText.replace(field['SystemExpression'], field['Name']);
					}
				}

				targetNode.textContent = labelText;
				targetNode.setAttribute('title', labelText);

				if (this.data.viewData.responsibleUrl)
				{
					targetNode.href = this.data.viewData.responsibleUrl;
					if (this.component.frameMode)
					{
						targetNode.setAttribute('target', '_blank');
					}
				}

				if (parseInt(this.data.viewData.responsibleId) > 0)
				{
					targetNode.setAttribute('bx-tooltip-user-id', this.data.viewData.responsibleId);
				}
			}
			var delayLabel = formatDelayInterval(this.getDelayInterval(),
				BX.message('BIZPROC_AUTOMATION_CMP_AT_ONCE'),
				this.component.data['DOCUMENT_FIELDS']
			);

			if (this.isExecuteAfterPrevious())
			{
				delayLabel = (delayLabel !== BX.message('BIZPROC_AUTOMATION_CMP_AT_ONCE')) ? delayLabel + ', ' : '';
				delayLabel += BX.message('BIZPROC_AUTOMATION_CMP_AFTER_PREVIOUS');
			}

			if (this.getCondition().items.length > 0)
			{
				delayLabel += ', ' + BX.message('BIZPROC_AUTOMATION_CMP_BY_CONDITION');
			}

			var delayNode;
			if (this.viewMode === Component.ViewMode.Edit)
			{
				delayNode = BX.create("a", {
					attrs: {
						className: "bizproc-automation-robot-link",
						title: delayLabel
					},
					text: delayLabel
				});
			}
			else
			{
				delayNode = BX.create("span", {
					attrs: { className: "bizproc-automation-robot-text" },
					text: delayLabel
				});
			}

			if (this.viewMode === Component.ViewMode.View)
			{
				switch (status)
				{
					case Component.LogStatus.Running:
						if (this.component.isCurrentStatus(this.template.getStatusId()))
						{
							loader = BX.create("div", {
								attrs: { className: "bizproc-automation-robot-loader" }
							});

							var delayNotes = this.getDelayNotes();
							if (delayNotes.length)
							{
								loader.setAttribute('data-text', delayNotes.join('\n'));
								HelpHint.bindToNode(loader);
							}
						}
						break;
					case Component.LogStatus.Completed:
					case Component.LogStatus.AutoCompleted:
						wrapperClass += ' bizproc-automation-robot-container-wrapper-complete';
						break;
				}

				var errors = this.getLogErrors();
				if (errors.length > 0)
				{
					loader = BX.create("div", {
						attrs: {
							className: "bizproc-automation-robot-errors",
							'data-text': errors.join('\n')
						}
					});

					HelpHint.bindToNode(loader);
				}
			}

			var titleClassName = 'bizproc-automation-robot-title-text';
			if (this.viewMode === Component.ViewMode.Edit)
			{
				titleClassName += ' bizproc-automation-robot-title-text-editable';
			}

			var div = BX.create("div", {
				attrs: {
					className: "bizproc-automation-robot-container",
					'data-role': 'robot-container',
					'data-type': 'item-robot',
					'data-id': this.getId()
				},
				children: [
					BX.create('div', {
						attrs: {
							className: wrapperClass
						},
						children: [
							BX.create("div", {
								attrs: { className: "bizproc-automation-robot-deadline" },
								children: [delayNode]
							}),
							BX.create("div", {
								attrs: {
									className: "bizproc-automation-robot-title"
								},
								children: [
									BX.create("div", {
										attrs: {
											className: titleClassName
										},
										html: this.clipTitle(this.getTitle()),
										events: {
											click: this.viewMode === Component.ViewMode.Edit ?
												this.onTitleEditClick.bind(this) : null
										}
									})
								]
							}),
							BX.create("div", {
								attrs: { className: "bizproc-automation-robot-settings" },
								children: [
									BX.create("div", {
										attrs: { className: "bizproc-automation-robot-settings-title" },
										text: targetLabel + ':'
									}),
									targetNode
								]
							}),
							loader
						]
					})
				]
			});

			if (this.viewMode === Component.ViewMode.Edit)
			{
				this.registerItem(div);

				var deleteBtn = BX.create('SPAN', {
					attrs: {
						className: 'bizproc-automation-robot-btn-delete'
					}
				});
				BX.bind(deleteBtn, 'click', function(e)
				{
					e.preventDefault();
					e.stopPropagation();
					me.onDeleteButtonClick(this);
				});
				div.lastChild.appendChild(deleteBtn);

				var settingsBtn = BX.create('div', {
					attrs: {
						className: 'bizproc-automation-robot-btn-settings'
					},
					text: BX.message('BIZPROC_AUTOMATION_CMP_EDIT')
				});
				BX.bind(div, 'click', me.onSettingsButtonClick.bind(me));
				div.appendChild(settingsBtn);
				BX.addClass(div.firstChild, 'bizproc-automation-robot-container-wrapper-border');

				var copyBtn = BX.create('div', {
					attrs: {
						className: 'bizproc-automation-robot-btn-copy'
					},
					text: BX.message('BIZPROC_AUTOMATION_CMP_COPY') || 'copy'
				});
				BX.bind(copyBtn, 'click', me.onCopyButtonClick.bind(me));
				div.appendChild(copyBtn);
			}

			return div;
		},
		onDeleteButtonClick: function()
		{
			BX.remove(this.node);
			this.template.deleteRobot(this);
		},
		onSettingsButtonClick: function()
		{
			this.template.openRobotSettingsDialog(this);
		},
		onCopyButtonClick: function(event)
		{
			event.stopPropagation();

			var robot = new Robot(this.template);
			var beforeRobot = this.template.getNextRobot(this);
			var robotData = this.serialize();
			delete robotData['Name'];
			delete robotData['DelayName'];
			if (robotData['Properties'] && robotData['Properties']['Title'])
			{
				robotData['Properties']['Title'] += ' ' + BX.message('BIZPROC_AUTOMATION_CMP_COPY_CAPTION');
			}

			robot.init(robotData, this.viewMode);

			this.template.insertRobot(robot, beforeRobot);
			this.template.insertRobotNode(robot.node, beforeRobot ? beforeRobot.node : null);
		},
		onTitleEditClick: function(e)
		{
			e.preventDefault();
			e.stopPropagation();

			var me = this, formName = 'bizproc_automation_robot_title_dialog';

			var form = BX.create('form', {
				props: {
					name: formName
				},
				style: {"min-width": '540px'}
			});

			form.appendChild(BX.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
				text: BX.message('BIZPROC_AUTOMATION_CMP_ROBOT_NAME') + ':'
			}));

			form.appendChild(BX.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [BX.create("input", {
					attrs: {
						className: 'bizproc-automation-popup-input',
						type: "text",
						name: "name",
						value: this.getTitle()
					}
				})]
			}));

			BX.addClass(this.component.node, 'automation-base-blocked');

			var popup = new BX.PopupWindow(Component.generateUniqueId(), null, {
				titleBar: BX.message('BIZPROC_AUTOMATION_CMP_ROBOT_NAME'),
				content: form,
				closeIcon: true,
				offsetLeft: 0,
				offsetTop: 0,
				closeByEsc: true,
				draggable: {restrict: false},
				overlay: false,
				events: {
					onPopupClose: function(popup)
					{
						popup.destroy();
						BX.removeClass(me.component.node, 'automation-base-blocked');
					}
				},
				buttons: [
					new BX.PopupWindowButton({
						text : BX.message('JS_CORE_WINDOW_SAVE'),
						className : "popup-window-button-accept",
						events : {
							click: function() {
								var nameNode = form.elements.name;
								me.setProperty('Title', nameNode.value);
								me.reInit();
								me.template.modified = true;
								this.popupWindow.close();
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('JS_CORE_WINDOW_CANCEL'),
						className : "popup-window-button-link-cancel",
						events : {
							click: function(){
								this.popupWindow.close()
							}
						}
					})
				]
			});

			popup.show();
		},
		clipTitle: function (fullTitle)
		{
			var title = BX.util.htmlspecialchars(fullTitle);
			var arrTitle = title.split(" ");
			var lastWord = "<span>" + arrTitle[arrTitle.length - 1] + "</span>";

			arrTitle.splice(arrTitle.length - 1);

			title = arrTitle.join(" ") + " " + lastWord;

			return title;
		},
		updateData: function(data)
		{
			if (BX.type.isPlainObject(data))
			{
				this.data = data;
			}
			else
				throw 'Invalid data';
		},
		serialize: function()
		{
			var result = BX.clone(this.data);
			delete result['viewData'];
			result.Delay = this.delay.serialize();
			result.Condition = this.condition.serialize();
			return result;
		},
		/**
		 * @returns {DelayInterval}
		 */
		getDelayInterval: function()
		{
			return this.delay;
		},
		setDelayInterval: function(delay)
		{
			this.delay = delay;
			return this;
		},
		/**
		 * @returns {ConditionGroup}
		 */
		getCondition: function()
		{
			return this.condition;
		},
		setCondition: function(condition)
		{
			this.condition = condition;
			return this;
		},
		setExecuteAfterPrevious: function(flag)
		{
			this.data.ExecuteAfterPrevious = flag ? 1 : 0;

			return this;
		},
		isExecuteAfterPrevious: function()
		{
			return (this.data.ExecuteAfterPrevious === 1 || this.data.ExecuteAfterPrevious === '1')
		},
		registerItem: function(object)
		{
			object.onbxdragstart = BX.proxy(this.dragStart, this);
			object.onbxdrag = BX.proxy(this.dragMove, this);
			object.onbxdragstop = BX.proxy(this.dragStop, this);
			object.onbxdraghover = BX.proxy(this.dragOver, this);
			jsDD.registerObject(object);
			jsDD.registerDest(object, 1);
		},
		dragStart: function()
		{
			this.draggableItem = BX.proxy_context;
			this.draggableItem.className = "bizproc-automation-robot-container";

			if (!this.draggableItem)
			{
				jsDD.stopCurrentDrag();
				return;
			}

			if (!this.stub)
			{
				var itemWidth = this.draggableItem.offsetWidth;
				this.stub = this.draggableItem.cloneNode(true);
				this.stub.style.position = "absolute";
				this.stub.className = "bizproc-automation-robot-container bizproc-automation-robot-container-drag";
				this.stub.style.width = itemWidth + "px";
				document.body.appendChild(this.stub);
			}
		},

		dragMove: function(x,y)
		{
			this.stub.style.left = x + "px";
			this.stub.style.top = y + "px";
		},

		dragOver: function(destination, x, y)
		{
			if (this.droppableItem)
			{
				this.droppableItem.className = "bizproc-automation-robot-container";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "bizproc-automation-robot-list";
			}

			var type = destination.getAttribute("data-type");

			if (type === "item-robot")
			{
				this.droppableItem = destination;
				this.droppableColumn = null;
			}

			if (type === "column-robot")
			{
				this.droppableColumn = destination.children[0];
				this.droppableItem = null;
			}

			if (this.droppableItem)
			{
				this.droppableItem.className = "bizproc-automation-robot-container bizproc-automation-robot-container-pre";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "bizproc-automation-robot-list bizproc-automation-robot-list-pre";
			}
		},

		dragStop: function(x, y, event)
		{
			event = event || window.event;
			var isCopy = event && (event.ctrlKey || event.metaKey);

			var tpl, beforeRobot;
			if (this.draggableItem)
			{
				if (this.droppableItem)
				{
					this.droppableItem.className = "bizproc-automation-robot-container";
					tpl = this.templateManager.getTemplateByColumnNode(this.droppableItem.parentNode);
					if (tpl)
					{
						beforeRobot = tpl.getRobotById(this.droppableItem.getAttribute('data-id'));
						if (isCopy)
						{
							this.copyTo(tpl, beforeRobot)
						}
						else if (this !== beforeRobot)
							this.moveTo(tpl, beforeRobot);
					}
				}
				else if (this.droppableColumn)
				{
					this.droppableColumn.className = "bizproc-automation-robot-list";
					tpl = this.templateManager.getTemplateByColumnNode(this.droppableColumn);
					if (tpl)
					{
						isCopy ? this.copyTo(tpl) : this.moveTo(tpl);
					}
				}
			}

			this.stub.parentNode.removeChild(this.stub);
			this.stub = null;
			this.draggableItem = null;
			this.droppableItem = null;
		},
		moveTo: function(template, beforeRobot)
		{
			BX.remove(this.node);
			this.template.deleteRobot(this);
			this.template = template;

			this.template.insertRobot(this, beforeRobot);
			this.node = this.createNode();
			this.template.insertRobotNode(this.node, beforeRobot ? beforeRobot.node : null);
		},
		copyTo: function(template, beforeRobot)
		{
			var robot = new Robot(template);
			var robotData = this.serialize();
			delete robotData['Name'];
			delete robotData['DelayName'];
			robot.init(robotData, this.viewMode);
			template.insertRobot(robot, beforeRobot);
			template.insertRobotNode(robot.node, beforeRobot ? beforeRobot.node : null);
		},
		getDescriptionSettings: function()
		{
			var settings = {};
			var description = this.templateManager.getRobotDescription(this.data['Type']);
			if (description && description['ROBOT_SETTINGS'])
			{
				settings = description['ROBOT_SETTINGS'];
			}
			return settings;
		},
		getTitle: function()
		{
			return  this.getProperty('Title') || this.getDescriptionTitle();
		},
		getDescriptionTitle: function()
		{
			var name = 'untitled';
			var description = this.templateManager.getRobotDescription(this.data['Type']);
			if (description['NAME'])
			{
				name = description['NAME'];
			}
			if (description['ROBOT_SETTINGS'] && description['ROBOT_SETTINGS']['TITLE'])
			{
				name = description['ROBOT_SETTINGS']['TITLE'];
			}
			return name;
		},
		getReturnFieldsDescription: function()
		{
			var fields = [];
			var description = this.templateManager.getRobotDescription(this.data['Type']);

			if (description && description['RETURN'])
			{
				for (var fieldId in description['RETURN'])
				{
					if (description['RETURN'].hasOwnProperty(fieldId))
					{
						var field = description['RETURN'][fieldId];
						fields.push({
							Id: fieldId,
							ObjectId: this.getId(),
							ObjectName: this.getTitle(),
							Name: field['NAME'],
							Type: field['TYPE'],
							Expression: '{{~'+this.getId()+':'+fieldId+' # '+this.getTitle()+': '+field['NAME']+'}}',
							SystemExpression: '{='+this.getId()+':'+fieldId+'}'
						});

						if (!this.appendPropertyMods)
						{
							continue;
						}

						//generate printable version
						if (
							field['TYPE'] === 'user'
							||
							field['TYPE'] === 'bool'
							||
							field['TYPE'] === 'file'
						)
						{
							var printableTag = (field['TYPE'] === 'user') ? 'friendly' : 'printable';
							fields.push({
								Id: fieldId + '_printable',
								ObjectId: this.getId(),
								Name: field['NAME'] + ' ' + BX.message('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX'),
								Type: 'string',
								Expression: '{{~'+this.getId()+':'+fieldId+' > '
									+printableTag+' # '+this.getTitle()+': '+field['NAME']+'}}',
								SystemExpression: '{='+this.getId()+':'+fieldId+'>'+printableTag+'}'
							});
						}
					}
				}
			}
			if (description && BX.type.isArray(description['ADDITIONAL_RESULT']))
			{
				var props = this.data['Properties'];

				description['ADDITIONAL_RESULT'].forEach(function(addProperty)
				{
					if (props[addProperty])
					{
						for (var fieldId in props[addProperty])
						{
							if (props[addProperty].hasOwnProperty(fieldId))
							{
								var field = props[addProperty][fieldId];
								fields.push({
									Id: fieldId,
									ObjectId: this.getId(),
									Name: field['Name'],
									Type: field['Type'],
									Expression: '{{~'+this.getId()+':'+fieldId+' # '+this.getTitle()+': '+field['Name']+'}}',
									SystemExpression: '{='+this.getId()+':'+fieldId+'}'
								});

								//generate printable version
								if (
									field['Type'] === 'user'
									||
									field['Type'] === 'bool'
									||
									field['Type'] === 'file'
								)
								{
									var printableTag = (field['Type'] === 'user') ? 'friendly' : 'printable';
									fields.push({
										Id: fieldId + '_printable',
										ObjectId: this.getId(),
										Name: field['Name'] + ' ' + BX.message('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX'),
										Type: 'string',
										Expression: '{{~'+this.getId()+':'+fieldId+' > '
											+printableTag+' # '+this.getTitle()+': '+field['Name']+'}}',
										SystemExpression: '{='+this.getId()+':'+fieldId+'>'+printableTag+'}'
									});
								}
							}
						}
					}
				}, this);
			}
			return fields;
		},
		getReturnProperty: function(id)
		{
			var fields = this.getReturnFieldsDescription();
			for (var i = 0; i < fields.length; ++i)
			{
				if (fields[i]['Id'] === id)
				{
					return fields[i];
				}
			}
			return null;
		},
		collectUsages: function()
		{
			var properties = this.getProperties();
			var usages = {Document: new Set(), Constant: new Set(), Parameter: new Set()};

			Object.keys(properties).forEach(function(propertyId) {
				var property = properties[propertyId];
				this.collectExpressions(property, usages);
			}, this);

			return usages;
		},

		collectExpressions: function(value, usages)
		{
			if (BX.Type.isArray(value))
			{
				value.forEach(function(v) {
					this.collectExpressions(v, usages);
				}, this);
			}
			else if (BX.Type.isPlainObject(value))
			{
				Object.keys(value).forEach(function(k) {
					this.collectExpressions(value[k], usages);
				}, this);
			}
			else if (BX.Type.isStringFilled(value))
			{
				var found, systemExpressionRegExp = new RegExp(systemExpressionPattern, 'ig');
				while ((found = systemExpressionRegExp.exec(value)) !== null) {
					switch (found.groups.object)
					{
						case 'Document':
							usages.Document.add(found.groups.field);
							break;
						case 'Constant':
							usages.Constant.add(found.groups.field);
							break;
						case 'Template':
							usages.Parameter.add(found.groups.field);
							break;
					}
				}
			}
		}
	};

	var TriggerManager = function(component)
	{
		this.component = component;
	};

	TriggerManager.prototype =
	{
		init: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			this.viewMode = viewMode || Component.ViewMode.View;
			this.availableTriggers = BX.type.isArray(data.AVAILABLE_TRIGGERS) ? data.AVAILABLE_TRIGGERS : [];
			this.triggersData = BX.type.isArray(data.TRIGGERS) ? data.TRIGGERS : [];
			this.columnNodes = document.querySelectorAll('[data-type="column-trigger"]');
			this.listNodes = this.component.node.querySelectorAll('[data-role="trigger-list"]');
			this.buttonsNodes = this.component.node.querySelectorAll('[data-role="trigger-buttons"]');
			this.initButtons();
			this.initTriggers();

			this.modified = false;

			//register DD
			for(var i = 0; i < this.columnNodes.length; i++)
			{
				jsDD.registerDest(this.columnNodes[i], 10);
			}

			top.BX.addCustomEvent(
				top,
				'Rest:AppLayout:ApplicationInstall',
				this.onRestAppInstall.bind(this)
			);
		},
		reInit: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			var i;
			this.viewMode = viewMode || Component.ViewMode.View;
			for (i = 0; i < this.listNodes.length; ++i)
			{
				BX.cleanNode(this.listNodes[i]);
			}
			for (i = 0; i < this.buttonsNodes.length; ++i)
			{
				BX.cleanNode(this.buttonsNodes[i]);
			}

			this.triggersData = BX.type.isArray(data.TRIGGERS) ? data.TRIGGERS : [];

			this.initTriggers();
			this.initButtons();

			this.modified = false;
		},
		initTriggers: function()
		{
			this.triggers = [];
			for (var i = 0; i < this.triggersData.length; ++i)
			{
				var trigger = new Trigger(this);
				trigger.init(this.triggersData[i], this.viewMode);
				this.insertTriggerNode(trigger.getStatusId(), trigger.node);
				this.triggers.push(trigger);
			}
		},
		initButtons: function()
		{
			if (this.viewMode === Component.ViewMode.Edit)
			{
				for (var i = 0; i < this.buttonsNodes.length; ++i)
				{
					this.createAddButton(this.buttonsNodes[i]);
				}
			}
		},
		createAddButton: function(containerNode)
		{
			var me = this,
				div = BX.create('a', {
							text: BX.message('BIZPROC_AUTOMATION_CMP_ADD'),
							props: {
								href: '#'
							},
							events: {
								click: function(e)
								{
									e.preventDefault();
									me.onAddButtonClick(this);
								}
							},
							attrs: {
								className: 'bizproc-automation-btn-add',
								'data-status-id': containerNode.getAttribute('data-status-id')
							}
						});
			containerNode.appendChild(div);
		},
		onAddButtonClick: function(button)
		{
			var me = this, i, menuItems = [];
			var onMenuClick = function(e, item)
			{
				me.addTrigger(item.triggerData, function(trigger)
				{
					me.openTriggerSettingsDialog(trigger);
				});

				this.popupWindow.close();
			};

			for (i = 0; i < this.availableTriggers.length; ++i)
			{
				if (this.availableTriggers[i].CODE === 'APP')
				{
					menuItems.push(this.createAppTriggerMenuItem(
						button.getAttribute('data-status-id'),
						this.availableTriggers[i]
					));
					continue;
				}

				menuItems.push({
					text: this.availableTriggers[i].NAME,
					triggerData: {
						DOCUMENT_STATUS: button.getAttribute('data-status-id'),
						CODE: this.availableTriggers[i].CODE
					},
					onclick: onMenuClick
				});
			}

			BX.PopupMenu.show(
				Component.generateUniqueId(),
				button,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(button)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					events : {
						onPopupClose : function() {this.destroy()}
					}
				}
			);
		},
		createAppTriggerMenuItem: function(status, triggerData)
		{
			var me = this, menuItems = [];
			var onMenuClick = function(e, item)
			{
				me.addTrigger(item.triggerData, function(trigger)
				{
					me.openTriggerSettingsDialog(trigger);
				});

				this.getRootMenuWindow().close();
			};

			for (var i = 0; i < triggerData['APP_LIST'].length; ++i)
			{
				var item = triggerData['APP_LIST'][i];
				var itemName = '[' + item['APP_NAME'] + '] ' + item['NAME'];
				menuItems.push({
					text: BX.util.htmlspecialchars(itemName),
					triggerData: {
						DOCUMENT_STATUS: status,
						NAME: itemName,
						CODE: triggerData.CODE,
						APPLY_RULES: {
							APP_ID: item['APP_ID'],
							CODE: item['CODE']
						}
					},
					onclick: onMenuClick
				});
			}

			if (BX.getClass('BX.rest.Marketplace'))
			{
				if (menuItems.length)
					menuItems.push({delimiter: true});

				menuItems.push({
					text: BX.message('BIZPROC_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE_2'),
					onclick: function()
					{
						BX.rest.Marketplace.open({PLACEMENT: me.component.data['MARKETPLACE_TRIGGER_PLACEMENT']});
						this.getRootMenuWindow().close();
					}
				});
			}

			return {
				text: triggerData.NAME,
				items: menuItems
			}
		},
		addTrigger: function(triggerData, callback)
		{
			var trigger = new Trigger(this);
			trigger.init(triggerData, this.viewMode);
			trigger.draft = true;
			if (callback)
				callback(trigger);
		},
		deleteTrigger: function(trigger, callback)
		{
			if (trigger.getId() > 0)
			{
				trigger.markDeleted();
			}
			else
			{
				for(var i = 0; i < this.triggers.length; ++i)
				{
					if (this.triggers[i] === trigger)
						this.triggers.splice(i, 1);
				}
			}
			if (callback)
				callback(trigger);

			this.modified = true;
		},
		insertTriggerNode: function(documentStatus, triggerNode)
		{
			var listNode = this.component.node.querySelector('[data-role="trigger-list"][data-status-id="'+documentStatus+'"]');
			if (listNode)
			{
				listNode.appendChild(triggerNode);
			}
		},
		serialize: function()
		{
			var triggers = [];

			for (var i = 0; i < this.triggers.length; ++i)
			{
				triggers.push(this.triggers[i].serialize());
			}

			return triggers;
		},
		countAllTriggers: function()
		{
			var cnt = 0;
			this.triggers.forEach(function(trigger)
			{
				if (!trigger.deleted)
				{
					++cnt;
				}
			});

			return cnt;
		},

		getTriggerName: function(code)
		{
			for (var i = 0; i < this.availableTriggers.length; ++i)
			{
				if (code == this.availableTriggers[i]['CODE'])
					return this.availableTriggers[i]['NAME'];
			}
			return code;
		},
		getAvailableTrigger: function(code)
		{
			for (var i = 0; i < this.availableTriggers.length; ++i)
			{
				if (code == this.availableTriggers[i]['CODE'])
					return this.availableTriggers[i];
			}
			return null;
		},
		needSave: function()
		{
			return this.modified;
		},
		openTriggerSettingsDialog: function(trigger)
		{
			if (Designer.getTriggerSettingsDialog())
			{
				return;
			}

			var me = this, formName = 'bizproc_automation_trigger_dialog';

			var form = BX.create('form', {
				props: {
					name: formName
				},
				style: {"min-width": '540px'}
			});

			form.appendChild(me.renderConditionSettings(trigger));

			var iconHelp = BX.create('div', {
				attrs: { className: 'bizproc-automation-robot-help' },
				events: {click: BX.delegate(this.component.onGlobalHelpClick, this.component)}
			});
			form.appendChild(iconHelp);

			var title = this.getTriggerName(trigger.data['CODE']);

			form.appendChild(BX.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
				text: BX.message('BIZPROC_AUTOMATION_CMP_TRIGGER_NAME') + ':'
			}));

			form.appendChild(BX.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [BX.create("input", {
					attrs: {
						className: 'bizproc-automation-popup-input',
						type: "text",
						name: "name",
						value: trigger.data['NAME'] || title
					}
				})]
			}));

			//TODO: refactoring
			var triggerData = this.getAvailableTrigger(trigger.data['CODE']);
			if (trigger.data['CODE'] === 'WEBHOOK')
			{
				if (!trigger.data['APPLY_RULES']['code'])
					trigger.data['APPLY_RULES']['code'] = BX.util.getRandomString(5);

				form.appendChild(BX.create("span", {
					attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
					text: "URL:"
				}));

				form.appendChild(BX.create('input', {
					props: {
						type: 'hidden',
						value: trigger.data['APPLY_RULES']['code'],
						name: 'code'
					}
				}));

				var hookLinkTextarea = BX.create("textarea", {
					attrs: {
						className: "bizproc-automation-popup-textarea",
						placeholder: "...",
						readonly: 'readonly',
						name: 'webhook_handler'
					},
					events: {
						click: function(e) {this.select();}
					}
				});

				form.appendChild(BX.create("div", {
					attrs: { className: "bizproc-automation-popup-settings" },
					children: [hookLinkTextarea]
				}));

				form.appendChild(BX.create("span", {
					attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
					text: BX.message('BIZPROC_AUTOMATION_CMP_WEBHOOK_ID')
				}));

				if (triggerData && triggerData['HANDLER'])
				{
					var url = window.location.protocol + '//' + window.location.host + triggerData['HANDLER'];
					url = BX.util.add_url_param(url, {code: trigger.data['APPLY_RULES']['code']});
					url = url.replace('{{DOCUMENT_TYPE}}', this.component.documentType[2]);
					hookLinkTextarea.value = url;
				}
			}
			else if (trigger.data['CODE'] === 'EMAIL_LINK')
			{
				form.appendChild(BX.create("span", {
					attrs: { className: "bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete" },
					text: BX.message('BIZPROC_AUTOMATION_TRIGGER_EMAIL_LINK_URL') + ':'
				}));

				form.appendChild(BX.create("div", {
					attrs: { className: "bizproc-automation-popup-settings" },
					children: [BX.create("textarea", {
						attrs: {
							className: "bizproc-automation-popup-textarea",
							placeholder: "https://example.com"
						},
						props: {name: 'url'},
						text: trigger.data['APPLY_RULES']['url'] || ''
					})]
				}));
			}
			else if (trigger.data['CODE'] == 'WEBFORM')
			{
				if (triggerData && triggerData['WEBFORM_LIST'])
				{
					var select = BX.create('select', {
						attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
						props: {
							name: 'form_id',
							value: ''
						},
						children: [BX.create('option', {
							props: {value: ''},
							text: BX.message('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
						})]
					});

					for (var i = 0; i < triggerData['WEBFORM_LIST'].length; ++i)
					{
						var item = triggerData['WEBFORM_LIST'][i];
						select.appendChild(BX.create('option', {
							props: {value: item['ID']},
							text: item['NAME']
						}));
					}
					if (BX.type.isPlainObject(trigger.data['APPLY_RULES']) && trigger.data['APPLY_RULES']['form_id'])
					{
						select.value = trigger.data['APPLY_RULES']['form_id'];
					}

					var div = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
						children: [BX.create('span', {attrs: {
								className: 'bizproc-automation-popup-settings-title'
							}, text: BX.message('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_LABEL') + ':'}), select]
					});
					form.appendChild(div);
				}
			}
			else if (trigger.data['CODE'] == 'CALLBACK')
			{
				if (triggerData && triggerData['WEBFORM_LIST'])
				{
					var select = BX.create('select', {
						attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
						props: {
							name: 'form_id',
							value: ''
						},
						children: [BX.create('option', {
							props: {value: ''},
							text: BX.message('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
						})]
					});

					for (var i = 0; i < triggerData['WEBFORM_LIST'].length; ++i)
					{
						var item = triggerData['WEBFORM_LIST'][i];
						select.appendChild(BX.create('option', {
							props: {value: item['ID']},
							text: item['NAME']
						}));
					}
					if (BX.type.isPlainObject(trigger.data['APPLY_RULES']) && trigger.data['APPLY_RULES']['form_id'])
					{
						select.value = trigger.data['APPLY_RULES']['form_id'];
					}

					var div = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
						children: [BX.create('span', {attrs: {
								className: 'bizproc-automation-popup-settings-title'
							}, text: BX.message('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_LABEL') + ':'}), select]
					});
					form.appendChild(div);
				}
			}
			else if (trigger.data['CODE'] == 'STATUS')
			{
				if (triggerData && triggerData['STATUS_LIST'])
				{
					var select = BX.create('select', {
						attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
						props: {
							name: 'STATUS',
							value: ''
						},
						children: [BX.create('option', {
							props: {value: ''},
							text: BX.message('BIZPROC_AUTOMATION_TRIGGER_STATUS_ANY')
						})]
					});

					for (var i = 0; i < triggerData['STATUS_LIST'].length; ++i)
					{
						var item = triggerData['STATUS_LIST'][i];
						select.appendChild(BX.create('option', {
							props: {value: item['ID']},
							text: item['NAME']
						}));
					}
					if (BX.type.isPlainObject(trigger.data['APPLY_RULES']) && trigger.data['APPLY_RULES']['STATUS'])
					{
						select.value = trigger.data['APPLY_RULES']['STATUS'];
					}

					var div = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
						children: [BX.create('span', {attrs: {
								className: 'bizproc-automation-popup-settings-title'
							}, text: triggerData['STATUS_LABEL'] + ':'}), select]
					});
					form.appendChild(div);
				}
			}
			else if (trigger.data['CODE'] == 'CALL')
			{
				if (triggerData && triggerData['LINES'])
				{
					var select = BX.create('select', {
						attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
						props: {
							name: 'LINE_NUMBER',
							value: ''
						},
						children: [BX.create('option', {
							props: {value: ''},
							text: BX.message('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
						})]
					});

					for (var i = 0; i < triggerData['LINES'].length; ++i)
					{
						var item = triggerData['LINES'][i];
						select.appendChild(BX.create('option', {
							props: {value: item['LINE_NUMBER']},
							text: item['SHORT_NAME']
						}));
					}
					if (trigger.data['APPLY_RULES']['LINE_NUMBER'])
					{
						select.value = trigger.data['APPLY_RULES']['LINE_NUMBER'];
					}

					var div = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
						children: [BX.create('span', {attrs: {
								className: 'bizproc-automation-popup-settings-title'
							}, text: BX.message('BIZPROC_AUTOMATION_TRIGGER_CALL_LABEL') + ':'}), select]
					});
					form.appendChild(div);
				}
			}
			else if (trigger.data['CODE'] == 'OPENLINE' || trigger.data['CODE'] == 'OPENLINE_MSG')
			{
				if (triggerData && triggerData['CONFIG_LIST'])
				{
					var select = BX.create('select', {
						attrs: {className: 'bizproc-automation-popup-settings-dropdown'},
						props: {
							name: 'config_id',
							value: ''
						},
						children: [BX.create('option', {
							props: {value: ''},
							text: BX.message('BIZPROC_AUTOMATION_TRIGGER_WEBFORM_ANY')
						})]
					});

					for (var i = 0; i < triggerData['CONFIG_LIST'].length; ++i)
					{
						var item = triggerData['CONFIG_LIST'][i];
						select.appendChild(BX.create('option', {
							props: {value: item['ID']},
							text: item['NAME']
						}));
					}
					if (BX.type.isPlainObject(trigger.data['APPLY_RULES']) && trigger.data['APPLY_RULES']['config_id'])
					{
						select.value = trigger.data['APPLY_RULES']['config_id'];
					}

					var div = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
						children: [BX.create('span', {attrs: {
							className: 'bizproc-automation-popup-settings-title'
						}, text: BX.message('BIZPROC_AUTOMATION_TRIGGER_OPENLINE_LABEL') + ':'}), select]
					});
					form.appendChild(div);
				}
			}

			BX.onCustomEvent('BX.Bizproc.Automation.TriggerManager:onOpenSettingsDialog-'+trigger.data['CODE'],
				[trigger, form]
			);

			form.appendChild(BX.create("div", {
				attrs: { className: "bizproc-automation-popup-checkbox" },
				children: [
					BX.create("div", {
						attrs: { className: "bizproc-automation-popup-checkbox-item" },
						children: [
							BX.create("label", {
								attrs: { className: "bizproc-automation-popup-chk-label" },
								children: [
									BX.create("input", {
										attrs: {
											className: 'bizproc-automation-popup-chk',
											type: "checkbox",
											name: "allow_backwards",
											value: 'Y'
										},
										props: {
											checked: trigger.isBackwardsAllowed()
										}
									}),
									document.createTextNode(BX.message('BIZPROC_AUTOMATION_CMP_TRIGGER_ALLOW_REVERSE'))
								]
							})
						]
					})
				]
			}));

			BX.addClass(this.component.node, 'automation-base-blocked');

			Designer.component = this.component;

			Designer.setTriggerSettingsDialog({
				component: this.component,
				trigger: trigger,
				form: form
			});

			var popup = new BX.PopupWindow(Component.generateUniqueId(), null, {
				titleBar: title,
				content: form,
				closeIcon: true,
				offsetLeft: 0,
				offsetTop: 0,
				closeByEsc: true,
				draggable: {restrict: false},
				overlay: false,
				events: {
					onPopupClose: function(popup)
					{
						Designer.setTriggerSettingsDialog(null);
						me.destroySettingsDialogControls();
						popup.destroy();
						BX.removeClass(me.component.node, 'automation-base-blocked');
					}
				},
				buttons: [
					new BX.PopupWindowButton({
						text : BX.message('JS_CORE_WINDOW_SAVE'),
						className : "popup-window-button-accept",
						events : {
							click: function() {
								var formData = BX.ajax.prepareForm(form);
								trigger.data['NAME'] = formData['data']['name'];

								//TODO: refactoring
								if (trigger.data['CODE'] === 'WEBFORM')
								{
									trigger.data['APPLY_RULES'] = {
										form_id:  formData['data']['form_id']
									}
								}
								if (trigger.data['CODE'] === 'CALLBACK')
								{
									trigger.data['APPLY_RULES'] = {
										form_id:  formData['data']['form_id']
									}
								}
								if (trigger.data['CODE'] === 'STATUS')
								{
									trigger.data['APPLY_RULES'] = {
										STATUS:  formData['data']['STATUS']
									}
								}
								if (trigger.data['CODE'] === 'CALL' && 'LINE_NUMBER' in formData['data'])
								{
									trigger.data['APPLY_RULES'] = {
										LINE_NUMBER:  formData['data']['LINE_NUMBER']
									}
								}
								if (trigger.data['CODE'] === 'OPENLINE' || trigger.data['CODE'] === 'OPENLINE_MSG')
								{
									trigger.data['APPLY_RULES'] = {
										config_id:  formData['data']['config_id']
									}
								}

								if (trigger.data['CODE'] === 'WEBHOOK')
								{
									trigger.data['APPLY_RULES'] = {
										code: formData['data']['code']
									}
								}

								if (trigger.data['CODE'] === 'EMAIL_LINK')
								{
									trigger.data['APPLY_RULES'] = {
										url: formData['data']['url']
									}
								}

								BX.onCustomEvent('BX.Bizproc.Automation.TriggerManager:onSaveSettings-'+trigger.data['CODE'],
									[trigger, formData]
								);

								me.setConditionSettingsFromForm(formData['data'], trigger);
								trigger.setAllowBackwards(formData['data']['allow_backwards'] === 'Y');

								if (trigger.draft)
								{
									me.triggers.push(trigger);
									me.insertTriggerNode(trigger.getStatusId(), trigger.node)
								}
								delete trigger.draft;

								trigger.reInit();
								me.modified = true;
								this.popupWindow.close();
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('JS_CORE_WINDOW_CANCEL'),
						className : "popup-window-button-link-cancel",
						events : {
							click: function(){
								this.popupWindow.close()
							}
						}
					})
				]
			});

			popup.show();
		},
		/**
		 * @param {Trigger} trigger
		 */
		renderConditionSettings: function(trigger)
		{
			/** @var {ConditionGroup} conditionGroup */
			var conditionGroup = BX.clone(trigger.getCondition());
			var selector = this.conditionSelector = new ConditionGroupSelector(conditionGroup, {
				fields: this.component.data['DOCUMENT_FIELDS']
			});

			return BX.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [
					BX.create("div", {
						attrs: { className: "bizproc-automation-popup-settings-block" },
						children: [
							BX.create("span", {
								attrs: { className: "bizproc-automation-popup-settings-title" },
								text: BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION') + ":"
							}),
							selector.createNode()
						]
					})
				]
			});
		},
		/**
		 * @param {Object} formFields
		 * @param {Trigger} trigger
		 * @returns {*}
		 */
		setConditionSettingsFromForm: function(formFields,  trigger)
		{
			trigger.setCondition(ConditionGroup.createFromForm(formFields));
			return this;
		},
		onRestAppInstall: function(installed, eventResult)
		{
			eventResult.redirect = false;
			var me = this;

			setTimeout(function()
			{
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: me.component.getAjaxUrl(),
					data: {
						ajax_action: 'get_available_triggers',
						document_signed: me.component.documentSigned
					},
					onsuccess: function(response)
					{
						if (BX.type.isArray(response['DATA']))
						{
							me.availableTriggers = response['DATA'];
						}
					}
				});
			}, 1500);
		},

		initSettingsDialogControls: function(node)
		{
			if (!BX.type.isArray(this.settingsDialogControls))
			{
				this.settingsDialogControls = [];
			}

			var controlNodes = node.querySelectorAll('[data-role]');
			for (var i = 0; i < controlNodes.length; ++i)
			{
				var control = null;
				var role = controlNodes[i].getAttribute('data-role');

				if (role === 'user-selector')
				{
					control = BX.Bizproc.UserSelector.decorateNode(controlNodes[i]);
				}

				BX.UI.Hint.init(controlNodes[i]);

				if (control)
				{
					this.settingsDialogControls.push(control);
				}
			}
		},
		destroySettingsDialogControls: function()
		{
			if (this.conditionSelector)
			{
				this.conditionSelector.destroy();
				this.conditionSelector = null;
			}

			if (BX.type.isArray(this.settingsDialogControls))
			{
				for (var i = 0; i < this.settingsDialogControls.length; ++i)
				{
					if (BX.type.isFunction(this.settingsDialogControls[i].destroy))
					{
						this.settingsDialogControls[i].destroy();
					}
				}
			}
			this.settingsDialogControls = null;
		},
		getListByDocumentStatus: function(statusId)
		{
			var result = [];
			this.triggers.forEach(function(trigger) {
				if (trigger.getStatusId() === statusId)
				{
					result.push(trigger);
				}
			});
			return result;
		},
		getReturnProperties: function(statusId)
		{
			var result = [];
			var exists = {};
			var triggers = this.getListByDocumentStatus(statusId);

			triggers.forEach(function(trigger) {
				var props = trigger.deleted ? [] : trigger.getReturnProperties();
				if (props.length)
				{
					props.forEach(function(property) {
						if (!exists[property.Id])
						{
							result.push({
								Id: property.Id,
								ObjectId: 'Template',
								Name: property.Name,
								ObjectName: trigger.getName(),
								Type: property.Type,
								Expression: '{{~*:'+property.Id+'}}',
								SystemExpression: '{=Template:'+property.Id+'}'
							});
							exists[property.Id] = true;
						}
					});
				}
			});
			return result;
		},
		getReturnProperty: function(statusId, propertyId)
		{
			var properties = this.getReturnProperties(statusId);
			for (var i = 0; i < properties.length; ++i)
			{
				if (properties[i].Id === propertyId)
				{
					return properties[i];
				}
			}
			return null;
		}
	};

	var Trigger = function(manager)
	{
		this.manager = manager;
		this.component = manager.component;
		this.tracker = manager.component.tracker;
		this.data = {};
		this.deleted = false;
		this.draggableItem = null;
		this.droppableItem = null;
		this.droppableColumn = null;
		this.stub = null;
		this.column = null;
	};

	Trigger.prototype =
	{
		init: function(data, viewMode)
		{
			this.data = data || {};

			if (!BX.type.isPlainObject(this.data['APPLY_RULES']))
			{
				this.data['APPLY_RULES'] = {};
			}

			if (this.data.APPLY_RULES.Condition)
			{
				this.condition = new ConditionGroup(this.data.APPLY_RULES.Condition);
			}
			else
			{
				this.condition = new ConditionGroup();
			}

			this.viewMode = viewMode || Component.ViewMode.View;
			this.node = this.createNode();
		},
		reInit: function(data, viewMode)
		{
			var node = this.node;
			this.node = this.createNode();
			if (node.parentNode)
				node.parentNode.replaceChild(this.node, node);
		},
		getId: function()
		{
			return this.data['ID'] || 0;
		},
		getStatusId: function()
		{
			return this.data['DOCUMENT_STATUS'] || '';
		},
		getCode: function()
		{
			return this.data['CODE'];
		},
		getName: function()
		{
			var triggerName = this.data['NAME'];
			if (!triggerName)
			{
				triggerName = this.manager.getTriggerName(this.data['CODE']);
			}
			return triggerName;
		},
		getLogStatus: function()
		{
			var log = this.tracker.getTriggerLog(this.getId());
			return log ? parseInt(log['STATUS']) : null;
		},
		/**
		 * @returns {ConditionGroup}
		 */
		getCondition: function()
		{
			return this.condition;
		},
		setCondition: function(condition)
		{
			this.condition = condition;
			return this;
		},
		isBackwardsAllowed: function()
		{
			return (this.data['APPLY_RULES']['ALLOW_BACKWARDS'] === 'Y');
		},
		setAllowBackwards: function(flag)
		{
			this.data['APPLY_RULES']['ALLOW_BACKWARDS'] = flag ? 'Y' : 'N';
			return this;
		},
		createNode: function()
		{
			var me = this, status = this.getLogStatus();

			var wrapperClass = 'bizproc-automation-trigger-item-wrapper';

			if (this.viewMode === Component.ViewMode.Edit)
			{
				wrapperClass += ' bizproc-automation-trigger-item-wrapper-draggable';

				var settingsBtn = BX.create("div", {
					attrs: {
						className: "bizproc-automation-trigger-item-wrapper-edit"
					},
					text: BX.message('BIZPROC_AUTOMATION_CMP_EDIT')
				});
			}
			else
			{
				if (status == Component.LogStatus.Completed)
				{
					wrapperClass += ' bizproc-automation-trigger-item-wrapper-complete';
				}
				else if (this.component.isPreviousStatus(this.getStatusId()))
				{
					wrapperClass += ' bizproc-automation-trigger-item-wrapper-complete-light';
				}
			}

			var triggerName = this.getName();

			var div = BX.create('DIV', {
				attrs: {
					'data-role': 'trigger-container',
					className: 'bizproc-automation-trigger-item',
					'data-type': 'item-trigger'
				},
				children: [
					BX.create("div", {
						attrs: {
							className: wrapperClass
						},
						children: [
							BX.create("div", {
								attrs: { className: "bizproc-automation-trigger-item-wrapper-text" },
								text: triggerName
							})
						]
					}),
					settingsBtn
				]
			});

			if (this.viewMode === Component.ViewMode.Edit)
			{
				this.registerItem(div);

				var deleteBtn = BX.create('SPAN', {
					attrs: {
						'data-role': 'btn-delete-trigger',
						className: 'bizproc-automation-trigger-btn-delete'
					}
				});

				BX.bind(deleteBtn, 'click', function(e)
				{
					e.preventDefault();
					e.stopPropagation();
					me.onDeleteButtonClick(this);
				});

				div.appendChild(deleteBtn);
				BX.addClass(div.firstChild, 'bizproc-automation-trigger-item-wrapper-border');
			}

			if (this.viewMode === Component.ViewMode.Edit)
			{
				BX.bind(div, 'click', function(e)
				{
					me.onSettingsButtonClick(this);
				});
			}

			return div;
		},
		onSettingsButtonClick: function(button)
		{
			this.manager.openTriggerSettingsDialog(this);
		},
		registerItem: function(object)
		{
			object.onbxdragstart = BX.proxy(this.dragStart, this);
			object.onbxdrag = BX.proxy(this.dragMove, this);
			object.onbxdragstop = BX.proxy(this.dragStop, this);
			object.onbxdraghover = BX.proxy(this.dragOver, this);
			jsDD.registerObject(object);
			jsDD.registerDest(object, 1);
		},
		dragStart: function()
		{
			this.draggableItem = BX.proxy_context;
			this.draggableItem.className = "bizproc-automation-trigger-item";

			if (!this.draggableItem)
			{
				jsDD.stopCurrentDrag();
				return;
			}

			if (!this.stub)
			{
				var itemWidth = this.draggableItem.offsetWidth;
				this.stub = this.draggableItem.cloneNode(true);
				this.stub.style.position = "absolute";
				this.stub.className = "bizproc-automation-trigger-item bizproc-automation-trigger-item-drag";
				this.stub.style.width = itemWidth + "px";
				document.body.appendChild(this.stub);
			}
		},

		dragMove: function(x,y)
		{
			this.stub.style.left = x + "px";
			this.stub.style.top = y + "px";
		},

		dragOver: function(destination, x, y)
		{
			if (this.droppableItem)
			{
				this.droppableItem.className = "bizproc-automation-trigger-item";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "bizproc-automation-trigger-list";
			}

			var type = destination.getAttribute("data-type");


			if (type === "item-trigger")
			{
				this.droppableItem = destination;
				this.droppableColumn = null;
			}

			if (type === "column-trigger")
			{
				this.droppableColumn = destination.children[0];
				this.droppableItem = null;
			}

			if (this.droppableItem)
			{
				this.droppableItem.className = "bizproc-automation-trigger-item bizproc-automation-trigger-item-pre";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "bizproc-automation-trigger-list bizproc-automation-trigger-list-pre";
			}
		},

		dragStop: function(x, y, event)
		{
			event = event || window.event;
			var trigger, isCopy = event && (event.ctrlKey || event.metaKey);
			var copyTrigger = function(parent, statusId)
			{
				var trigger = new Trigger(parent.manager);
				var initData = parent.serialize();
				delete initData['ID'];
				//TODO: refactoring
				if (initData['CODE'] === 'WEBHOOK')
				{
					initData['APPLY_RULES'] = {};
				}
				initData['DOCUMENT_STATUS'] = statusId;
				trigger.init(initData, parent.viewMode);
				return trigger;
			};

			if (this.draggableItem)
			{
				if (this.droppableItem)
				{
					this.droppableItem.className = "bizproc-automation-trigger-item";
					var thisColumn = this.droppableItem.parentNode;
					if (!isCopy)
					{
						thisColumn.insertBefore(this.draggableItem, this.droppableItem);
						this.moveTo(thisColumn.getAttribute('data-status-id'));
					}
					else
					{
						trigger = copyTrigger(this, thisColumn.getAttribute('data-status-id'));
						thisColumn.insertBefore(trigger.node, this.droppableItem);

					}
				}
				else if (this.droppableColumn)
				{
					this.droppableColumn.className = "bizproc-automation-trigger-list";
					if (!isCopy)
					{
						this.droppableColumn.appendChild(this.draggableItem);
						this.moveTo(this.droppableColumn.getAttribute('data-status-id'));
					}
					else
					{
						trigger = copyTrigger(this, this.droppableColumn.getAttribute('data-status-id'));
						this.droppableColumn.appendChild(trigger.node);
					}
				}

				if (trigger)
				{
					this.manager.triggers.push(trigger);
					this.manager.modified = true;
				}
			}

			this.stub.parentNode.removeChild(this.stub);
			this.stub = null;
			this.draggableItem = null;
			this.droppableItem = null;
		},

		onDeleteButtonClick: function(button)
		{
			BX.remove(button.parentNode);
			this.manager.deleteTrigger(this);
		},
		updateData: function(data)
		{
			if (BX.type.isPlainObject(data))
			{
				this.data = data;
			}
			else
				throw 'Invalid data';
		},
		markDeleted: function()
		{
			this.deleted = true;
			return this;
		},
		serialize: function()
		{
			var data = BX.clone(this.data);
			if (this.deleted)
			{
				data['DELETED'] = 'Y';
			}

			if (!BX.type.isPlainObject(data.APPLY_RULES))
			{
				data.APPLY_RULES = {};
			}

			if (!this.condition.items.length)
			{
				delete data.APPLY_RULES.Condition;
			}
			else
			{
				data.APPLY_RULES.Condition = this.condition.serialize();
			}

			return data;
		},
		moveTo: function(statusId)
		{
			this.data['DOCUMENT_STATUS'] = statusId;
			//TODO: ref.
			this.manager.modified = true;
		},
		getReturnProperties: function()
		{
			var triggerData = this.manager.getAvailableTrigger(this.getCode());
			return triggerData && BX.type.isArray(triggerData.RETURN) ? triggerData.RETURN : [];
		}
	};

	var Tracker = function(component)
	{
		this.component = component;
	};

	Tracker.prototype =
	{
		init: function(log)
		{
			if (!BX.type.isPlainObject(log))
				log = {};

			this.log = log;
			this.triggers = {};
			this.robots = {};

			for (var statusId in log)
			{
				if (!log.hasOwnProperty(statusId))
					continue;

				if (log[statusId]['trigger'])
				{
					this.triggers[log[statusId]['trigger']['ID']] = log[statusId]['trigger'];
				}

				if (log[statusId]['robots'])
				{
					for (var robotId in log[statusId]['robots'])
					{
						if (!log[statusId]['robots'].hasOwnProperty(robotId))
							continue;

						this.robots[robotId] = log[statusId]['robots'][robotId];
					}
				}
			}
		},
		reInit: function(log)
		{
			this.init(log);
		},
		getRobotLog: function(id)
		{
			return this.robots[id] || null;
		},
		getTriggerLog: function(id)
		{
			return this.triggers[id] || null;
		}
	};

	// -> FileSelector
	var FileSelector = function(robot, container)
	{
		var config, configString = container.getAttribute('data-config');
		if (configString)
		{
			config = BX.parseJSON(configString);
		}

		if (!BX.type.isPlainObject(config))
			config = {};

		this.container = container;

		//read configuration
		this.type = config.type || FileSelector.Type.File;
		if (config.selected && !config.selected.length)
		{
			this.type = FileSelector.Type.None;
		}

		this.multiple = config.multiple || false;
		this.required = config.required || false;
		this.valueInputName = config.valueInputName || '';
		this.typeInputName = config.typeInputName || '';
		this.useDisk = config.useDisk || false;
		this.label = config.label || 'Attachment';
		this.labelFile = config.labelFile || 'File';
		this.labelDisk = config.labelDisk || 'Disk';

		var templateRobots = robot.template ? robot.template.robots : [];
		this.setFileFields(robot.component.data['DOCUMENT_FIELDS'], templateRobots);
		this.createDom();

		if (config.selected && config.selected.length > 0)
		{
			this.addItems(BX.clone(config.selected));
		}
	};

	FileSelector.Type = {None: '', Disk: 'disk', File: 'file'};

	FileSelector.prototype =
	{
		setFileFields: function(documentFields, templateRobots)
		{
			var fields = [];
			var labels = {};
			for (var i = 0; i < documentFields.length; ++i)
			{
				if (documentFields[i]['Type'] === 'file')
				{
					fields.push(documentFields[i]);
				}
			}

			if (BX.type.isArray(templateRobots))
			{
				templateRobots.forEach(function(robot)
				{
					robot.getReturnFieldsDescription().forEach(function(field)
					{
						if (field['Type'] === 'file')
						{
							var expression = '{{~'+robot.getId()+':'+field['Id']+'}}';
							fields.push({
								Id: expression,
								Name: robot.getTitle() + ': ' + field['Name'],
								Type: 'file',
								Expression: expression
							});
							labels[expression] = robot.getTitle() + ': ' + field['Name'];
						}
					});
				});
			}

			this.fileFields = fields;
			this.fileLabels = labels;
			return this;
		},

		createDom: function()
		{
			this.container.appendChild(this.createBaseNode());
			this.showTypeControllerLayout(this.type);
		},
		createBaseNode: function()
		{
			var idSalt = Component.generateUniqueId();
			var typeRadio1 = null;

			if (this.fileFields.length > 0)
			{
				typeRadio1 = BX.create("input", {
					attrs: {
						className: "bizproc-automation-popup-select-input",
						type: "radio",
						id: "type-1" + idSalt,
						name: this.typeInputName,
						value: FileSelector.Type.File
					}
				});
				if (this.type === FileSelector.Type.File)
				{
					typeRadio1.setAttribute('checked', 'checked');
				}
			}

			var typeRadio2 = BX.create("input", {
				attrs: {
					className: "bizproc-automation-popup-select-input",
					type: "radio",
					id: "type-2" + idSalt,
					name: this.typeInputName,
					value: FileSelector.Type.Disk
				}
			});

			if (this.type === FileSelector.Type.Disk)
			{
				typeRadio2.setAttribute('checked', 'checked');
			}

			var children = [BX.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-title" },
				text: this.label + ":"
			})];

			if (typeRadio1)
			{
				children.push(typeRadio1, BX.create("label", {
					attrs: {
						className: "bizproc-automation-popup-settings-link",
						for: "type-1" + idSalt
					},
					text: this.labelFile,
					events: {
						click: this.onTypeChange.bind(this, FileSelector.Type.File)
					}
				}));
			}

			children.push(typeRadio2, BX.create("label", {
				attrs: {
					className: "bizproc-automation-popup-settings-link",
					for: "type-2" + idSalt
				},
				text: this.labelDisk,
				events: {
					click: this.onTypeChange.bind(this, FileSelector.Type.Disk)
				}
			}));

			return BX.create("div", {
				attrs: { className: "bizproc-automation-popup-settings" },
				children: [
					BX.create("div", {
						attrs: { className: "bizproc-automation-popup-settings-block" },
						children: children
					})
				]
			});
		},
		showTypeControllerLayout: function(type)
		{
			if (type === FileSelector.Type.Disk)
			{
				this.hideFileControllerLayout();
				this.showDiskControllerLayout();
			}
			else if (type === FileSelector.Type.File)
			{
				this.hideDiskControllerLayout();
				this.showFileControllerLayout();
			}
			else
			{
				this.hideFileControllerLayout();
				this.hideDiskControllerLayout();
			}
		},
		showDiskControllerLayout: function()
		{
			if (!this.diskControllerNode)
			{
				this.diskControllerNode = BX.create('div');
				this.container.appendChild(this.diskControllerNode);
				var diskUploader = this.getDiskUploader();
				diskUploader.layout(this.diskControllerNode);
				diskUploader.show(true);
			}
			else
			{
				BX.show(this.diskControllerNode);
			}
		},
		hideDiskControllerLayout: function()
		{
			if (this.diskControllerNode)
			{
				BX.hide(this.diskControllerNode);
			}
		},
		showFileControllerLayout: function()
		{
			if (!this.fileControllerNode)
			{
				this.fileItemsNode = BX.create('span');
				this.fileControllerNode = BX.create('div', {children: [this.fileItemsNode]});
				this.container.appendChild(this.fileControllerNode);
				var addButtonNode = BX.create('a', {
					attrs: {className: 'bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-thin'},
					text: BX.message('BIZPROC_AUTOMATION_CMP_ADD')
				});

				this.fileControllerNode.appendChild(addButtonNode);

				BX.bind(addButtonNode, 'click', this.onFileFieldAddClick.bind(this, addButtonNode));
			}
			else
			{
				BX.show(this.fileControllerNode);
			}
		},
		hideFileControllerLayout: function()
		{
			if (this.fileControllerNode)
			{
				BX.hide(this.fileControllerNode);
			}
		},
		getDiskUploader: function()
		{
			if (!this.diskUploader)
			{
				this.diskUploader = BX.Bizproc.Automation.DiskUploader.create(
					'',
					{
						msg:
							{
								'diskAttachFiles' : BX.message('BIZPROC_AUTOMATION_CMP_DISK_ATTACH_FILE'),
								'diskAttachedFiles' : BX.message('BIZPROC_AUTOMATION_CMP_DISK_ATTACHED_FILES'),
								'diskSelectFile' : BX.message('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE'),
								'diskSelectFileLegend' : BX.message('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE_LEGEND'),
								'diskUploadFile' : BX.message('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE'),
								'diskUploadFileLegend' : BX.message('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE_LEGEND')
							}
					}
				);

				this.diskUploader.setMode(1);
			}

			return this.diskUploader;
		},
		onTypeChange: function(newType)
		{
			if (this.type !== newType)
			{
				this.type = newType;
				this.showTypeControllerLayout(this.type);
			}
		},
		isFileItemSelected: function(item)
		{
			var itemNode = this.fileItemsNode.querySelector('[data-file-id="'+item.id+'"]');
			return !!itemNode;
		},
		addFileItem: function(item)
		{
			if (this.isFileItemSelected(item))
			{
				return false;
			}

			var node = this.createFileItemNode(item);
			if (!this.multiple)
			{
				BX.cleanNode(this.fileItemsNode)
			}

			this.fileItemsNode.appendChild(node);
		},
		addItems: function(items)
		{
			if (this.type === FileSelector.Type.File)
			{
				for(var i = 0; i < items.length; ++i)
				{
					this.addFileItem(items[i])
				}
			}
			else
			{
				this.getDiskUploader()
					.setValues(
						this.convertToDiskItems(items)
					);
			}
		},
		convertToDiskItems: function(items)
		{
			var diskItems = [];
			for (var i = 0; i < items.length; ++i)
			{
				var item = items[i];
				diskItems.push({
					ID: item['id'],
					NAME: item['name'],
					SIZE: item['size'],
					VIEW_URL: ''
				});
			}

			return diskItems;
		},
		removeFileItem: function(item)
		{
			var itemNode = this.fileItemsNode.querySelector('[data-file-id="'+item.id+'"]');
			if (itemNode)
			{
				this.fileItemsNode.removeChild(itemNode);
			}
		},
		onFileFieldAddClick: function(addButtonNode, e)
		{
			var me = this, i, menuItems = [];

			var fields = this.fileFields;
			for (i = 0; i < fields.length; ++i)
			{
				menuItems.push({
					text: BX.util.htmlspecialchars(fields[i]['Name']),
					field: fields[i],
					onclick: function(e, item)
					{
						this.popupWindow.close();
						me.onFieldSelect(item.field);
					}
				});
			}

			if (!this.menuId)
			{
				this.menuId = Component.generateUniqueId();
			}

			BX.PopupMenu.show(
				this.menuId,
				addButtonNode,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(addButtonNode)['width'] / 2),
					angle: { position: 'top', offset: 0 }
				}
			);
			this.menu = BX.PopupMenu.currentItem;
			e.preventDefault();
		},
		onFieldSelect: function(field)
		{
			this.addFileItem({
				id: field.Id,
				expression: field.Expression,
				name: field.Name,
				type: FileSelector.Type.File
			});
		},
		destroy: function()
		{
			if (this.menu)
			{
				this.menu.popupWindow.close();
			}
		},
		createFileItemNode: function(item)
		{
			var label = item.name || '';
			if (this.fileLabels[label])
			{
				label = this.fileLabels[label];
			}

			return BX.create('span', {
				attrs: {
					className: 'bizproc-automation-popup-autocomplete-item',
					'data-file-id': item.id,
					'data-file-expression': item.expression
				},
				children: [
					BX.create('span', {
						attrs: {
							className: 'bizproc-automation-popup-autocomplete-name'
						},
						text: label
					}),
					BX.create('span', {
						attrs: {
							className: 'bizproc-automation-popup-autocomplete-delete'
						},
						events: {
							click: this.removeFileItem.bind(this, item)
						}
					})
				]
			});
		},
		onBeforeSave: function()
		{
			var ids = [];
			if (this.type === FileSelector.Type.Disk)
			{
				ids = this.getDiskUploader().getValues();
			}
			else if (this.type === FileSelector.Type.File)
			{
				this.fileItemsNode.childNodes.forEach(function(node)
				{
					var id = node.getAttribute('data-file-expression');
					if (id !== '')
					{
						ids.push(id);
					}
				})
			}

			for (var i = 0; i < ids.length; ++i)
			{
				this.container.appendChild(BX.create('input', {
					props: {
						type: 'hidden',
						name: this.valueInputName + (this.multiple ? '[]' : ''),
						value: ids[i]
					}
				}));
			}
		}
	};
	// <- FileSelector
	// -> InlineSelector
	/**
	 * @param {Robot} robot
	 * @param {Node} targetInput
	 * @param data
	 */
	var InlineSelector = function(robot, targetInput, data)
	{
		var me = this;
		this.robot = robot;
		this.component = robot.component;
		this.documentFields = this.component ? this.component.data['DOCUMENT_FIELDS'] : data['DOCUMENT_FIELDS'];
		this.showTemplatePropertiesMenuOnSelecting = (
			this.component ? this.component.data['SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING'] === true : false
		);
		this.targetInput = BX.clone(targetInput);
		this.menuButton = BX.create('span', {
			attrs: {className: 'bizproc-automation-popup-select-dotted'},
			events: {
				click: BX.delegate(me.openMenu, this)
			}
		});
		this.appendPropertyMods = true;

		var wrapper = BX.create('div', {
			attrs: {className: 'bizproc-automation-popup-select'},
			children: [
				this.targetInput,
				this.menuButton
			]
		});

		targetInput.parentNode.replaceChild(wrapper, targetInput);

		BX.bind(this.targetInput, 'keydown', function(e) {
			me.onKeyDown(this, e);
		});
		this.targetInput.setAttribute('autocomplete', 'off');

		this.fieldProperty = JSON.parse(this.targetInput.getAttribute('data-property'));
		if (this.fieldProperty)
		{
			delete this.fieldProperty.Default;
		}
		if (!this.fieldProperty || !this.robot)
		{
			this.showTemplatePropertiesMenuOnSelecting = false;
		}

		this.fieldType = this.targetInput.getAttribute('data-selector-type');
		if (this.fieldType === 'date' || this.fieldType === 'datetime')
		{
			this.initDateTimeControl();
		}
		else if (this.fieldType === 'file')
		{
			this.initFileControl();
		}

		if (this.targetInput.getAttribute('data-select-mode') === 'replace')
		{
			this.replaceOnWrite = true;
		}

		this.selectCallback = function(item)
		{
			me.onFieldSelect(item.getCustomData().get('property'));
		}
	};
	InlineSelector.prototype =
	{
		onKeyDown: function(container, e)
		{
			if (e.keyCode == 45 && e.altKey === false && e.ctrlKey === false && e.shiftKey === false)
			{
				this.openMenu(e);
				e.preventDefault();
			}
		},
		openMenu: function(e, skipPropertiesSwitcher)
		{
			if (!skipPropertiesSwitcher && this.showTemplatePropertiesMenuOnSelecting && !this.targetInput.value)
			{
				return this.openPropertiesSwitcherMenu();
			}

			if (this.dialog)
			{
				this.dialog.show();
				return;
			}

			var me = this, i, field, fields = this.getFields();

			var menuItems = [], fileFields = [], menuGroups = {'ROOT': {
				title: this.component ? this.component.data['ENTITY_NAME'] : API.documentName,
				entityId: 'bp',
				tabs: 'recents',
				id: 'ROOT',
				children: []
			}};

			for (i = 0; i < fields.length; ++i)
			{
				field = fields[i];

				var names, groupKey = field['Id'].indexOf('.') < 0 ? 'ROOT' : field['Id'].split('.')[0];
				var fieldName = field['Name'];
				var groupName = '';
				if (fieldName && groupKey !== 'ROOT' && fieldName.indexOf(': ') >= 0)
				{
					names = fieldName.split(': ');
					groupName = names.shift();
					fieldName = names.join(': ');
				}

				if (field['Id'].indexOf('ASSIGNED_BY_') === 0
					&& field['Id'] !== 'ASSIGNED_BY_ID'
					&& field['Id'] !== 'ASSIGNED_BY_PRINTABLE'
				)
				{
					groupKey = 'ASSIGNED_BY';
					names = fieldName.split(' ');
					groupName = names.shift();
					fieldName = names.join(' ').replace('(', '').replace(')', '');
				}

				if (!menuGroups[groupKey])
				{
					menuGroups[groupKey] = {
						title: groupName,
						entityId: 'bp',
						tabs: 'recents',
						tabId: 'bp',
						id: groupName,
						children: []
					};
				}

				if (field['Type'] === 'file')
				{
					fileFields.push(field);
				}

				menuGroups[groupKey]['children'].push({
					title: fieldName || field['Id'],
					customData: {property: field},
					entityId: 'bp',
					tabs: 'recents',
					id: field['SystemExpression'],
				});
			}

			if (!this.fieldsOnly)
			{
				// ROBOT GROUP
				if (this.robot)
				{
					var skipId = this.robot.getId();
					var robotResults = [];
					var tpl = this.robot.template;
					var templateRobots = tpl ? tpl.robots : [];

					templateRobots.forEach(function(robot)
					{
						if (robot.getId() !== skipId)
						{
							var fields = [];
							robot.getReturnFieldsDescription().forEach(function(field)
							{
								if (field['Type'] === 'file')
								{
									fileFields.push(field);
								}

								fields.push({
									title: field['Name'] || field['Id'],
									customData: {property: field},
									entityId: 'bp',
									tabs: 'recents',
									id: field['SystemExpression'],
								});
							});
							if (fields.length)
							{
								robotResults.push({
									title: robot.getTitle(),
									entityId: 'bp',
									tabs: 'recents',
									id: robot.getId(),
									children: fields
								});
							}
						}
					});

					if (robotResults.length)
					{
						menuGroups['__RESULT'] = {
							title: BX.message('BIZPROC_AUTOMATION_CMP_ROBOT_LIST'),
							entityId: 'bp',
							tabs: 'recents',
							id: '__RESULT',
							children: robotResults
						};
					}
				}

				//TRIGGER GROUP
				var triggerResults = this.getTriggerProperties().map(function(property)
				{
					return {
						title: property['Name'] || property['Id'],
						subtitle: property['ObjectName'] || property['ObjectId'],
						customData: {property: property},
						entityId: 'bp',
						tabs: 'recents',
						id: property['SystemExpression']
					}
				})
				if (triggerResults.length)
				{
					menuGroups['__TRESULT'] = {
						title: BX.message('BIZPROC_AUTOMATION_CMP_TRIGGER_LIST'),
						entityId: 'bp',
						tabs: 'recents',
						id: '__TRESULT',
						children: triggerResults
					};
				}

				//FILES GROUP
				if (fileFields.length && this.appendPropertyMods)
				{
					menuGroups['__FILES'] = {
						title: BX.message('BIZPROC_AUTOMATION_CMP_FILES_LINKS'),
						entityId: 'bp',
						tabs: 'recents',
						id: '__FILES',
						children: this.prepareFilesMenu(fileFields)
					};
				}

				//CONSTANTS GROUP
				if (tpl && tpl.data.CONSTANTS && !this.showTemplatePropertiesMenuOnSelecting)
				{
					var constantList = [];
					tpl.getConstants().forEach(function(constant)
					{
						constantList.push({
							title: constant['Name'],
							customData: {property: constant},
							entityId: 'bp',
							tabs: 'recents',
							id: constant.SystemExpression
						});
					});

					if (constantList.length)
					{
						menuGroups['__CONSTANTS'] = {
							title: BX.message('BIZPROC_AUTOMATION_CMP_CONSTANTS_LIST'),
							entityId: 'bp',
							tabs: 'recents',
							id: '__CONSTANTS',
							children: constantList
						};
					}
				}

				//GLOBAL CONST GROUP
				if (this.component && this.component.data['GLOBAL_CONSTANTS'])
				{
					var globalConstantList = [];
					this.component.getConstants().forEach(function(constant)
					{
						globalConstantList.push({
							title: constant['Name'],
							customData: {property: constant},
							entityId: 'bp',
							tabs: 'recents',
							id: constant.SystemExpression
						});
					}, this);

					if (globalConstantList.length)
					{
						menuGroups['__GLOB_CONSTANTS'] = {
							title: BX.message('BIZPROC_AUTOMATION_CMP_GLOB_CONSTANTS_LIST'),
							entityId: 'bp',
							tabs: 'recents',
							id: '__GLOB_CONSTANTS',
							children: globalConstantList
						};
					}
				}
			}

			if (Object.keys(menuGroups).length < 2)
			{
				if (menuGroups['ROOT']['children'].length > 0)
				{
					menuItems = menuGroups['ROOT']['children'];
				}
			}
			else
			{
				if (menuGroups['ROOT']['children'].length > 0)
				{
					menuItems.push(menuGroups['ROOT']);
				}
				delete menuGroups['ROOT'];
				for (groupKey in menuGroups)
				{
					if (menuGroups.hasOwnProperty(groupKey) && menuGroups[groupKey]['children'].length > 0)
					{
						menuItems.push(menuGroups[groupKey])
					}
				}
			}

			var menuId = this.menuButton.getAttribute('data-selector-id');
			if (!menuId)
			{
				menuId = Component.generateUniqueId();
				this.menuButton.setAttribute('data-selector-id', menuId);
			}

			this.dialog = new BX.UI.EntitySelector.Dialog({
				targetNode: this.menuButton,
				width: 500,
				height: 300,
				multiple: false,
				dropdownMode: true,
				enableSearch: true,
				items: this.injectDialogMenuTitles(menuItems),
				showAvatars: false,
				events: {
					'Item:onBeforeSelect': function(event)
					{
						event.preventDefault();
						me.selectCallback(event.getData().item);
					}
				}
			});

			this.dialog.show();
		},
		openPropertiesSwitcherMenu: function()
		{
			var me = this;
			BX.PopupMenu.show(
				Component.generateUniqueId(),
				this.menuButton,
				[
					{
						text: BX.message('BIZPROC_AUTOMATION_ASK_CONSTANT'),
						onclick: function(e) {
							this.popupWindow.close();
							me.onFieldSelect(
								me.robot.template.addConstant(me.fieldProperty)
							);
						}
					},
					{
						text: BX.message('BIZPROC_AUTOMATION_ASK_PARAMETER'),
						onclick: function(e) {
							this.popupWindow.close();
							me.onFieldSelect(
								me.robot.template.addParameter(me.fieldProperty)
							);
						}
					},
					{
						text: BX.message('BIZPROC_AUTOMATION_ASK_MANUAL'),
						onclick: function(e) {
							this.popupWindow.close();
							me.openMenu(e, true);
						}
					}
				],
				{
					autoHide: true,
					offsetLeft: 20,
					angle: { position: 'top'},
					events: {
						onPopupClose: function ()
						{
							this.destroy();
						}
					}
				}
			);
			this.switcherDialog = BX.PopupMenu.currentItem;

			return true;
		},

		injectDialogMenuTitles: function(items)
		{
			items.forEach(function(parent) {
				if (BX.type.isArray(parent.children))
				{
					parent.searchable = false;
					this.injectDialogMenuSupertitles(parent.title, parent.children);
				}
			}, this);
			return items;
		},
		injectDialogMenuSupertitles: function(title, children)
		{
			children.forEach(function(child) {
				child.supertitle = title;
				if (BX.type.isArray(child.children))
				{
					child.searchable = false;
					this.injectDialogMenuSupertitles(child.title, child.children);
				}
			}, this);
		},

		prepareFilesMenu: function(fileFields)
		{
			var menu = [];
			fileFields.forEach(function(field)
			{
				var exp = field['ObjectId'] === 'Document' ?
					'{{'+field['Name']+' > shortlink}}'
					: '{{~'+field['ObjectId']+':'+field['Id']+' > shortlink}}';

				var title = field['Name'] || field['Id'];

				if (field.ObjectName)
				{
					title = field.ObjectName + ': ' + title;
				}

				menu.push({
					title: title,
					customData: {
						property: {
							Id: field['Id'] + '_shortlink',
							ObjectId: field['ObjectId'],
							Name: field['Name'],
							Type: 'string',
							Expression: exp,
							SystemExpression: '{='+field['ObjectId']+':'+field['Id']+' > shortlink}'
						}
					},
					entityId: 'bp',
					tabs: 'recents',
					id: exp,
				});
			});
			return menu;
		},
		onFieldSelect: function(field)
		{
			if (!field)
			{
				return;
			}

			var inputType = this.targetInput.tagName.toLowerCase();

			if (inputType === 'select')
			{
				var expressionOption = this.targetInput.querySelector('[data-role="expression"]');
				if (!expressionOption)
				{
					expressionOption = this.targetInput.appendChild(BX.create('option', {attrs: {'data-role': 'expression'}}));
				}
				expressionOption.setAttribute('value', field['Expression']);
				expressionOption.textContent = field['Expression'];

				expressionOption.selected = true;
			}
			else if (inputType === 'label')
			{
				this.targetInput.textContent = field['Expression'];
				var hiddenInput = document.getElementById(this.targetInput.getAttribute('for'));
				if (hiddenInput)
				{
					hiddenInput.value = field['Expression'];
				}
			}
			else
			{
				if (this.replaceOnWrite)
				{
					this.targetInput.value = field['Expression'];
					this.targetInput.selectionEnd = this.targetInput.value.length;
				}
				else
				{
					var beforePart = this.targetInput.value.substr(0, this.targetInput.selectionEnd),
						middlePart = field['Expression'],
						afterPart = this.targetInput.value.substr(this.targetInput.selectionEnd);

					this.targetInput.value = beforePart + middlePart + afterPart;
					this.targetInput.selectionEnd = beforePart.length + middlePart.length;
				}
			}

			BX.fireEvent(this.targetInput, 'change');
		},
		destroy: function()
		{
			if (this.dialog)
			{
				this.dialog.destroy();
			}
			if (this.switcherDialog)
			{
				this.switcherDialog.destroy();
			}
		},
		initDateTimeControl: function()
		{
			var basisFields = [];
			var docFields = this.documentFields;
			if (BX.type.isArray(this.documentFields))
			{
				var i, field;
				for (i = 0; i < this.documentFields.length; ++i)
				{
					field = this.documentFields[i];
					if (field['Type'] === 'date' || field['Type'] === 'datetime')
					{
						basisFields.push(field);
					}
				}
			}

			this.documentFields = basisFields;
			this.replaceOnWrite = true;

			var delayIntervalSelector = new DelayIntervalSelector({
				labelNode: this.targetInput,
				basisFields: basisFields,
				useAfterBasis: true,
				onchange: (function(delay)
				{
					this.targetInput.value = delay.toExpression(
						basisFields,
						getResponsibleUserExpression(docFields)
					);
				}).bind(this)
			});

			delayIntervalSelector.init(DelayInterval.fromString(this.targetInput.value, basisFields));
		},
		initFileControl: function()
		{
			var basisFields = [];
			if (BX.type.isArray(this.component.data['DOCUMENT_FIELDS']))
			{
				var i, field;
				for (i = 0; i < this.component.data['DOCUMENT_FIELDS'].length; ++i)
				{
					field = this.component.data['DOCUMENT_FIELDS'][i];
					if (field['Type'] === 'file')
					{
						basisFields.push(field);
					}
				}
			}

			this.documentFields = basisFields;
			this.replaceOnWrite = true;
		},
		getFields: function()
		{
			var printablePrefix = BX.message('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX');
			var names = [];
			this.documentFields.forEach(function(field)
			{
				names.push(field['Name']);
			});

			var namesStr = names.join('\n');
			var fields = [];
			var fieldType = this.fieldType;

			this.documentFields.forEach(function(field)
			{
				field.ObjectId = 'Document';

				var custom = (field['BaseType'] === 'string' && field['Type'] !== 'string');

				if (!custom || !this.appendPropertyMods)
				{
					fields.push(field);
				}

				if (!this.appendPropertyMods)
				{
					return;
				}

				//generate printable version
				if (
					field['Type'] === 'user'
					||
					field['Type'] === 'bool'
					||
					field['Type'] === 'file'
					||
					custom
				)
				{
					var printableName = field['Name'] + ' ' + printablePrefix;

					if (namesStr.indexOf(printableName) < 0)
					{
						var printableField = BX.clone(field);
						var printableTag = (field['Type'] === 'user') ? 'friendly' : 'printable';

						printableField['Name'] = printableName;
						printableField['Type'] = 'string';
						printableField['SystemExpression'] = '{=Document:'+printableField['Id']+' > '+printableTag+'}';
						printableField['Expression'] = '{{'+field['Name']+' > '+printableTag+'}}';

						fields.push(printableField);
					}
				}
				if (field['BaseType'] === 'date' || field['BaseType'] === 'datetime')
				{
					var serverField = BX.clone(field);
					serverField['Name'] += ' '+BX.message('BIZPROC_AUTOMATION_CMP_MOD_DATE_BY_SERVER');
					serverField['Type'] = 'string';
					serverField['SystemExpression'] = '{=Document:'+serverField['Id']+' > server}';
					serverField['Expression'] = '{{'+field['Name']+' > server}}';

					fields.push(serverField);

					var responsibleField = BX.clone(field);
					responsibleField['Name'] += ' '+BX.message('BIZPROC_AUTOMATION_CMP_MOD_DATE_BY_RESPONSIBLE');
					responsibleField['Type'] = 'string';
					responsibleField['SystemExpression'] = '{=Document:'+serverField['Id']+' > responsible}';
					responsibleField['Expression'] = '{{'+field['Name']+' > responsible}}';

					fields.push(responsibleField);
				}
			}, this);

			return fields;
		},
		getTriggerProperties: function()
		{
			var result = [];
			if (this.robot && this.robot.template && this.component && this.component.triggerManager)
			{
				result = this.component.triggerManager.getReturnProperties(this.robot.template.getStatusId());
			}

			return result;
		}
	};
	// <- InlineSelector
	// -> InlineSelectorCondition
	var InlineSelectorCondition = function(menuButton, fields, cb, condition)
	{
		this.component = BX.Bizproc.Automation.Designer.component;
		this.robot = BX.Bizproc.Automation.Designer.robot;
		this.documentFields = fields;
		this.menuButton = menuButton;
		this.appendPropertyMods = false;
		this.selectCallback = function(item)
		{
			cb(item.getCustomData().get('property'));
		}

		this.fieldsOnly = !(
			condition
			&& condition.parentGroup
			&& condition.parentGroup.type === ConditionGroup.Type.Mixed
		);
	};
	BX.extend(InlineSelectorCondition, InlineSelector);
	// <- InlineSelectorCondition
	// -> UserSelector
	var UserSelector = function(robot, targetInput, data)
	{
		this.targetInput = this.menuButton = targetInput;
		this.userSelector = BX.Bizproc.UserSelector.decorateNode(targetInput);

		this.robot = robot;
		this.component = robot.component;
		this.documentFields = this.component ? this.component.data['DOCUMENT_FIELDS'] : data['DOCUMENT_FIELDS'];
		this.showTemplatePropertiesMenuOnSelecting = (
			this.component ? this.component.data['SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING'] === true : false
		);

		this.fieldProperty = JSON.parse(targetInput.getAttribute('data-property'));
		if (this.fieldProperty)
		{
			delete this.fieldProperty.Default;
		}
		if (!this.fieldProperty || !this.robot)
		{
			this.showTemplatePropertiesMenuOnSelecting = false;
		}

		if (this.showTemplatePropertiesMenuOnSelecting)
		{
			this.userSelector.handleOpenDialog = this.onBeforeOpenDialog.bind(this);
		}
	};
	BX.extend(UserSelector, InlineSelector);
	UserSelector.prototype.onBeforeOpenDialog = function(dialog)
	{
		if (!this.userSelector.getValue() || this.fieldProperty.Required)
		{
			this.openPropertiesSwitcherMenu();
			return false;
		}
	}
	UserSelector.prototype.openMenu = function()
	{
		this.userSelector.handleOpenDialog = null;
		this.userSelector.openDialog();
	}
	UserSelector.prototype.onFieldSelect = function(field)
	{
		this.userSelector.addItem({
			id: field['SystemExpression'],
			entityId: field['SystemExpression'],
			name: BX.Text.encode(field['Expression']),
			entityType: 'bpuserroles'
		}, 'bpuserroles');
	}
	UserSelector.prototype.destroy = function()
	{
		if (this.userSelector)
		{
			this.userSelector.destroy();
			this.userSelector = null;
		}
		if (this.switcherDialog)
		{
			this.switcherDialog.destroy();
		}
	};
	// <- UserSelector
	// -> InlineSelectorHtml
	var InlineSelectorHtml = function(robot, targetNode)
	{
		var me = this;
		this.robot = robot;
		this.component = robot.component;
		this.documentFields = this.component.data['DOCUMENT_FIELDS'];
		this.editorNode = targetNode.firstElementChild.firstElementChild;
		this.menuButton = BX.create('span', {
			attrs: {className: 'bizproc-automation-popup-select-dotted'},
			events: {
				click: BX.delegate(me.openMenu, this)
			}
		});
		targetNode.firstElementChild.appendChild(this.menuButton);
		this.bindEvents();
		this.selectCallback = function(item)
		{
			me.onFieldSelect(item.getCustomData().get('property'));
		}
		this.appendPropertyMods = true;
	};

	BX.extend(InlineSelectorHtml, InlineSelector);

	InlineSelectorHtml.prototype.getEditor = function()
	{
		var editor;
		if (this.editorNode)
		{
			var editorId = this.editorNode.id.split('-');
			editor = BXHtmlEditor.Get(editorId[editorId.length -1]);
		}
		return editor;
	};

	InlineSelectorHtml.prototype.bindEvents = function()
	{
		this.editorInitFunction = this.bindEditorHooks.bind(this);
		BX.addCustomEvent('OnEditorInitedAfter', this.editorInitFunction);
	};

	InlineSelectorHtml.prototype.unBindEvents = function()
	{
		BX.removeCustomEvent('OnEditorInitedAfter', this.editorInitFunction);
	};

	InlineSelectorHtml.prototype.bindEditorHooks = function(editor)
	{
		var header = '', footer = '';
		if (editor.dom.cont !== this.editorNode)
		{
			return false;
		}
		BX.addCustomEvent(editor, "OnParse", function(mode)
		{
			if (!mode)
			{
				var content = this.content;

				content = content.replace(/(^[\s\S]*?)(<body.*?>)/i, function(str){
						header = str;
						return '';
					}
				);

				content = content.replace(/(<\/body>[\s\S]*?$)/i,  function(str){
						footer = str;
						return '';
					}
				);

				this.content = content;
			}
		});

		BX.addCustomEvent(editor, "OnAfterParse", function(mode)
		{
			if (mode)
			{
				var content = this.content;

				content = content.replace(/^[\s\S]*?<body.*?>/i, "");
				content = content.replace(/<\/body>[\s\S]*?$/i, "");

				if (header !== '' && footer !== '')
				{
					content = header + content + footer;
				}


				this.content = content;
			}
		});
	};

	InlineSelectorHtml.prototype.onFieldSelect = function(field)
	{
		var insertText = field['Expression'];
		var editor = this.getEditor();
		if (editor && editor.InsertHtml)
		{
			editor.InsertHtml(insertText);
		}
	};
	InlineSelectorHtml.prototype.destroy = function()
	{
		if (this.menu)
			this.menu.popupWindow.close();
		this.unBindEvents();
	};
	InlineSelectorHtml.prototype.onBeforeSave = function()
	{
		var editor = this.getEditor();
		if (editor && editor.SaveContent)
		{
			editor.SaveContent();
		}
	};
	InlineSelectorHtml.prototype.onPopupResize = function()
	{
		var editor = this.getEditor();
		if (editor && editor.ResizeSceleton)
		{
			editor.ResizeSceleton();
		}
	};
	// <- InlineSelectorHtml
	// -> TimeSelector
	var TimeSelector = function(targetInput)
	{
		this.targetInput = targetInput;

		var d = new Date(), currentValue = this.unFormatTime(targetInput.value);
		d.setHours(0, 0, 0, 0);
		d.setTime(d.getTime() + currentValue * 1000);
		targetInput.value = this.formatTime(d); //convert to site format on client side.

		BX.bind(targetInput, 'click', BX.delegate(this.showClock, this));
	};
	TimeSelector.prototype =
	{
		showClock: function (e)
		{
			if (!this.clockInstance)
			{
				this.clockInstance = new BX.CClockSelector({
					start_time: this.unFormatTime(this.targetInput.value),
					node: this.targetInput,
					callback: BX.delegate(this.onTimeSelect, this)
				});
			}
			this.clockInstance.Show();
		},
		onTimeSelect: function(v)
		{
			this.targetInput.value = v;
			BX.fireEvent(this.targetInput, 'change');
			this.clockInstance.closeWnd();
		},
		unFormatTime: function(time)
		{
			var q = time.split(/[\s:]+/);
			if (q.length == 3)
			{
				var mt = q[2];
				if (mt == 'pm' && q[0] < 12)
					q[0] = parseInt(q[0], 10) + 12;

				if (mt == 'am' && q[0] == 12)
					q[0] = 0;

			}
			return parseInt(q[0], 10) * 3600 + parseInt(q[1], 10) * 60;
		},
		formatTime: function(date)
		{
			var dateFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')).replace(/:?\s*s/, ''),
				timeFormat = BX.date.convertBitrixFormat(BX.message('FORMAT_DATETIME')).replace(/:?\s*s/, ''),
				str1 = BX.date.format(dateFormat, date),
				str2 = BX.date.format(timeFormat, date);
			return BX.util.trim(str2.replace(str1, ''));
		},
		destroy: function()
		{
			if (this.clockInstance)
				this.clockInstance.closeWnd();
		}
	};
	// <- TimeSelector
	// -> SaveStateCheckbox
	var SaveStateCheckbox = function(checkbox, robot)
	{
		this.checkbox = checkbox;
		this.robot = robot;
		this.needSync = robot.draft;
		if (this.needSync)
		{
			var key = this.getKey();
			var savedState = robot.component.getUserOption('save_state_checkboxes', key, 'N');
			if (savedState === 'Y')
			{
				checkbox.checked = true;
			}
		}
	};
	SaveStateCheckbox.prototype =
	{
		getKey: function()
		{
			return this.checkbox.getAttribute('data-save-state-key');
		},
		destroy: function()
		{
			if (this.needSync)
			{
				var key = this.getKey();
				var value = this.checkbox.checked? 'Y' : 'N';
				this.robot.component.setUserOption('save_state_checkboxes', key, value);
			}
		}
	};
	// <- SaveStateCheckbox
	// -> DelayIntervalSelector

	var DelayIntervalSelector = function(options)
	{
		this.basisFields = [];
		this.onchange = null;

		if (BX.type.isPlainObject(options))
		{
			this.labelNode = options.labelNode;
			this.useAfterBasis = options.useAfterBasis;

			if (BX.type.isArray(options.basisFields))
				this.basisFields = options.basisFields;
			this.onchange = options.onchange;
			this.minLimitM = options.minLimitM;
		}
	};
	DelayIntervalSelector.prototype =
	{
		init: function(delay)
		{
			this.delay = delay;
			this.setLabelText();
			this.bindLabelNode();
			this.prepareBasisFields();
		},
		setLabelText: function()
		{
			if (this.delay && this.labelNode)
			{
				this.labelNode.textContent = formatDelayInterval(
					this.delay,
					BX.message('BIZPROC_AUTOMATION_CMP_AT_ONCE'),
					this.basisFields
				);
			}
		},
		bindLabelNode: function()
		{
			if (this.labelNode)
			{
				BX.bind(this.labelNode, 'click', BX.delegate(this.onLabelClick, this));
			}
		},
		onLabelClick: function(e)
		{
			this.showDelayIntervalPopup();
			e.preventDefault();
		},
		showDelayIntervalPopup: function()
		{
			var me = this, delay = this.delay;
			var uid = Component.generateUniqueId();

			var form = BX.create("form", {
				attrs: { className: "bizproc-automation-popup-select-block" }
			});

			var radioNow = BX.create("input", {
				attrs: {
					className: "bizproc-automation-popup-select-input",
					id: uid + "now",
					type: "radio",
					value: 'now',
					name: "type"
				}
			});
			if (delay.isNow())
				radioNow.setAttribute('checked', 'checked');

			var labelNow = BX.create("label", {
				attrs: {
					className: "bizproc-automation-popup-select-wrapper",
					for: uid + "now"
				},
				children: [
					BX.create('span', {
						attrs: {className: 'bizproc-automation-popup-settings-title'},
						text: BX.message(this.useAfterBasis ? 'BIZPROC_AUTOMATION_CMP_BASIS_NOW' : 'BIZPROC_AUTOMATION_CMP_AT_ONCE_2')
					})
				]
			});

			var labelNowHelpNode = BX.create('span', {
				attrs: {
					className: "bizproc-automation-status-help bizproc-automation-status-help-right",
					'data-hint': BX.message(this.useAfterBasis ? 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP_2' : 'BIZPROC_AUTOMATION_CMP_DELAY_NOW_HELP')
				}
			});
			labelNow.appendChild(labelNowHelpNode);

			form.appendChild(BX.create("div", {
				attrs: { className: "bizproc-automation-popup-select-item" },
				children: [radioNow, labelNow]
			}));

			form.appendChild(this.createAfterControlNode());

			if (this.basisFields.length > 0)
			{
				form.appendChild(this.createBeforeControlNode());
				form.appendChild(this.createInControlNode());
			}

			var workTimeRadio = BX.create("input", {
				attrs: {
					type: "checkbox",
					id: uid + "worktime",
					name: "worktime",
					value: '1',
					style: 'vertical-align: middle'
				},
				props: {
					checked: delay.workTime
				}
			});

			var workTimeHelpNode = BX.create('span', {
				attrs: {
					className: "bizproc-automation-status-help bizproc-automation-status-help-right",
					'data-hint': BX.message('BIZPROC_AUTOMATION_CMP_DELAY_WORKTIME_HELP_2')
				}
			});

			form.appendChild(BX.create("div", {
				attrs: { className: "bizproc-automation-popup-settings-title" },
				children: [
					workTimeRadio,
					BX.create("label", {
						attrs: {
							className: "bizproc-automation-popup-settings-lbl",
							for: uid + "worktime"
						},
						text: BX.message('BIZPROC_AUTOMATION_CMP_WORK_TIME')
					}),
					workTimeHelpNode
				]
			}));

			//init modern Help tips
			BX.UI.Hint.init(form);
			var popup = new BX.PopupWindow(Component.generateUniqueId(), this.labelNode, {
				autoHide: true,
				closeByEsc: true,
				closeIcon: false,
				titleBar: false,
				angle: true,
				offsetLeft: 20,
				content: form,
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE'),
						className: "webform-button webform-button-create bizproc-automation-button-left" ,
						events: {
							click: function(){
								var formData = BX.ajax.prepareForm(form);
								me.saveFormData(formData['data']);
								this.popupWindow.close();
							}}
					})
				],
				events: {
					onPopupClose: function()
					{
						if (me.fieldsMenu)
						{
							me.fieldsMenu.popupWindow.close();
						}
						if (me.valueTypeMenu)
						{
							me.valueTypeMenu.popupWindow.close();
						}
						this.destroy();
					}
				},
				overlay: { backgroundColor: 'transparent' }
			});

			popup.show();
		},
		saveFormData: function(formData)
		{
			if (formData['type'] === 'now')
			{
				this.delay.setNow();
			}
			else if (formData['type'] === DelayInterval.Type.In)
			{
				this.delay.setType(DelayInterval.Type.In);
				this.delay.setValue(0);
				this.delay.setValueType('i');
				this.delay.setBasis(formData['basis_in']);
			}
			else
			{
				this.delay.setType(formData['type']);
				this.delay.setValue(formData['value_' + formData['type']]);
				this.delay.setValueType(formData['value_type_'+formData['type']]);

				if (formData['type'] === DelayInterval.Type.After)
				{
					if (this.useAfterBasis)
					{
						this.delay.setBasis(formData['basis_after']);
					}
					else
					{
						this.delay.setBasis(DelayInterval.Basis.CurrentDateTime);
					}
					if (
						this.minLimitM > 0
						&& this.delay.basis === DelayInterval.Basis.CurrentDateTime
						&& this.delay.valueType === 'i'
						&& this.delay.value < this.minLimitM
					)
					{
						BX.UI.Notification.Center.notify({
							content: BX.message('BIZPROC_AUTOMATION_DELAY_MIN_LIMIT_LABEL')
						});
						this.delay.setValue(this.minLimitM);
					}
				}
				else
				{
					this.delay.setBasis(formData['basis_before']);
				}
			}

			this.delay.setWorkTime(formData['worktime']);
			this.setLabelText();

			if (this.onchange)
			{
				this.onchange(this.delay);
			}
		},
		createAfterControlNode: function()
		{
			var me = this, delay = this.delay;
			var uid = Component.generateUniqueId();

			var radioAfter = BX.create("input", {
				attrs: {
					className: "bizproc-automation-popup-select-input",
					id: uid,
					type: "radio",
					value: DelayInterval.Type.After,
					name: "type"
				}
			});
			if (delay.type === DelayInterval.Type.After && delay.value > 0)
				radioAfter.setAttribute('checked', 'checked');

			var valueNode = BX.create('input', {
				attrs: {
					type: 'text',
					name: 'value_after',

					className: 'bizproc-automation-popup-settings-input'
				},
				props: {
					value: delay.type === DelayInterval.Type.After && delay.value ? delay.value : (this.minLimitM || 5)
				}
			});

			var labelAfter = BX.create("label", {
				attrs: {
					className: "bizproc-automation-popup-select-wrapper",
					for: uid
				},
				children: [
					BX.create('span', {
						attrs: {className: 'bizproc-automation-popup-settings-title'},
						text: BX.message('BIZPROC_AUTOMATION_CMP_THROUGH_3')
					}),
					valueNode,
					this.createValueTypeSelector('value_type_after')
				]
			});

			if (this.useAfterBasis)
			{
				labelAfter.appendChild(BX.create('span', {
					attrs: {className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width'},
					text: BX.message('BIZPROC_AUTOMATION_CMP_AFTER')
				}));

				var basisField = this.getBasisField(delay.basis, true);
				var basisValue = delay.basis;
				if (!basisField)
				{
					basisField = this.getBasisField(DelayInterval.Basis.CurrentDateTime, true);
					basisValue = basisField.SystemExpression;
				}

				var beforeBasisValueNode = BX.create('input', {
					attrs: {
						type: "hidden",
						name: "basis_after",
						value: basisValue
					}
				});

				var beforeBasisNode = BX.create('span', {
					attrs: {
						className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
					},
					text: basisField ? basisField.Name : BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
					events: {
						click: function(e)
						{
							me.onBasisClick(e, this, function(field)
							{
								beforeBasisNode.textContent = field.Name;
								beforeBasisValueNode.value = field.SystemExpression;
							}, DelayInterval.Type.After);
						}
					}
				});
				labelAfter.appendChild(beforeBasisValueNode);
				labelAfter.appendChild(beforeBasisNode);
			}

			if (!this.useAfterBasis)
			{
				var afterHelpNode = BX.create('span', {
					attrs: {
						className: "bizproc-automation-status-help bizproc-automation-status-help-right",
						'data-hint': BX.message('BIZPROC_AUTOMATION_CMP_DELAY_AFTER_HELP')
					}
				});
				labelAfter.appendChild(afterHelpNode);
			}

			return BX.create("div", {
				attrs: { className: "bizproc-automation-popup-select-item" },
				children: [radioAfter, labelAfter]
			});
		},
		createBeforeControlNode: function()
		{
			var me = this, delay = this.delay;
			var uid = Component.generateUniqueId();

			var radioBefore = BX.create("input", {
				attrs: {
					className: "bizproc-automation-popup-select-input",
					id: uid,
					type: "radio",
					value: DelayInterval.Type.Before,
					name: "type"
				}
			});

			if (delay.type === DelayInterval.Type.Before)
				radioBefore.setAttribute('checked', 'checked');

			var valueNode = BX.create('input', {
				attrs: {
					type: 'text',
					name: 'value_before',

					className: 'bizproc-automation-popup-settings-input'
				},
				props: {
					value: delay.type === DelayInterval.Type.Before && delay.value ? delay.value : (this.minLimitM || 5)
				}
			});

			var labelBefore = BX.create("label", {
				attrs: {
					className: "bizproc-automation-popup-select-wrapper",
					for: uid
				},
				children: [
					BX.create('span', {
						attrs: {className: 'bizproc-automation-popup-settings-title'},
						text: BX.message('BIZPROC_AUTOMATION_CMP_FOR_TIME_3')
					}),
					valueNode,
					this.createValueTypeSelector('value_type_before'),
					BX.create('span', {
						attrs: {className: 'bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-auto-width'},
						text: BX.message('BIZPROC_AUTOMATION_CMP_BEFORE_1')
					})
				]
			});

			var basisField = this.getBasisField(delay.basis);
			var basisValue = delay.basis;
			if (!basisField)
			{
				basisField = this.basisFields[0];
				basisValue = basisField.SystemExpression;
			}

			var beforeBasisValueNode = BX.create('input', {
				attrs: {
					type: "hidden",
					name: "basis_before",
					value: basisValue
				}
			});

			var beforeBasisNode = BX.create('span', {
				attrs: {
					className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
				},
				text: basisField ? basisField.Name : BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
				events: {
					click: function(e)
					{
						me.onBasisClick(e, this, function(field)
						{
							beforeBasisNode.textContent = field.Name;
							beforeBasisValueNode.value = field.SystemExpression;
						}, DelayInterval.Type.Before);
					}
				}
			});
			labelBefore.appendChild(beforeBasisValueNode);
			labelBefore.appendChild(beforeBasisNode);

			if (!this.useAfterBasis)
			{
				var beforeHelpNode = BX.create('span', {
					attrs: {
						className: "bizproc-automation-status-help bizproc-automation-status-help-right",
						'data-hint': BX.message('BIZPROC_AUTOMATION_CMP_DELAY_BEFORE_HELP')
					}
				});
				labelBefore.appendChild(beforeHelpNode);
			}

			return BX.create("div", {
				attrs: {className: "bizproc-automation-popup-select-item"},
				children: [radioBefore, labelBefore]
			});
		},
		createInControlNode: function()
		{
			var me = this, delay = this.delay;
			var uid = Component.generateUniqueId();

			var radioIn = BX.create("input", {
				attrs: {
					className: "bizproc-automation-popup-select-input",
					id: uid,
					type: "radio",
					value: DelayInterval.Type.In,
					name: "type"
				}
			});

			if (delay.type === DelayInterval.Type.In)
				radioIn.setAttribute('checked', 'checked');


			var labelIn = BX.create("label", {
				attrs: {
					className: "bizproc-automation-popup-select-wrapper",
					for: uid
				},
				children: [
					BX.create('span', {
						attrs: {className: 'bizproc-automation-popup-settings-title'},
						text: BX.message('BIZPROC_AUTOMATION_CMP_IN_TIME_2')
					})
				]
			});

			var basisField = this.getBasisField(delay.basis);
			var basisValue = delay.basis;
			if (!basisField)
			{
				basisField = this.basisFields[0];
				basisValue = basisField.SystemExpression;
			}

			var inBasisValueNode = BX.create('input', {
				attrs: {
					type: "hidden",
					name: "basis_in",
					value: basisValue
				}
			});

			var inBasisNode = BX.create('span', {
				attrs: {
					className: "bizproc-automation-popup-settings-link bizproc-automation-delay-interval-basis"
				},
				text: basisField ? basisField.Name : BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
				events: {
					click: function(e)
					{
						me.onBasisClick(e, this, function(field)
						{
							inBasisNode.textContent = field.Name;
							inBasisValueNode.value = field.SystemExpression;
						});
					}
				}
			});
			labelIn.appendChild(inBasisValueNode);
			labelIn.appendChild(inBasisNode);
			if (!this.useAfterBasis)
			{
				var helpNode = BX.create('span', {
					attrs: {
						className: "bizproc-automation-status-help bizproc-automation-status-help-right",
						'data-hint': BX.message('BIZPROC_AUTOMATION_CMP_DELAY_IN_HELP')
					}
				});
				labelIn.appendChild(helpNode);
			}

			return BX.create("div", {
				attrs: {className: "bizproc-automation-popup-select-item"},
				children: [radioIn, labelIn]
			});
		},
		createValueTypeSelector: function(name)
		{
			var delay = this.delay;
			var labelTexts = {
				i: BX.message('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
				h: BX.message('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
				d: BX.message('BIZPROC_AUTOMATION_CMP_INTERVAL_D')
			};

			var label = BX.create('label', {
				attrs: {className: 'bizproc-automation-popup-settings-link'},
				text: labelTexts[delay.valueType]

			});

			var input = BX.create('input', {
				attrs: {
					type: 'hidden',
					name: name
				},
				props: {
					value: delay.valueType
				}
			});

			BX.bind(label, 'click', this.onValueTypeSelectorClick.bind(this, label, input));

			return BX.create('span', {
				children: [label, input]
			})
		},
		onValueTypeSelectorClick: function(label, input)
		{
			var uid = Component.generateUniqueId();

			var handler = function(e, item)
			{
				this.popupWindow.close();
				input.value = item.valueId;
				label.textContent = item.text;
			};

			var menuItems = [
				{
					text: BX.message('BIZPROC_AUTOMATION_CMP_INTERVAL_M'),
					valueId: 'i',
					onclick: handler
				},{
					text: BX.message('BIZPROC_AUTOMATION_CMP_INTERVAL_H'),
					valueId: 'h',
					onclick: handler
				},{
					text: BX.message('BIZPROC_AUTOMATION_CMP_INTERVAL_D'),
					valueId: 'd',
					onclick: handler
				}
			];

			BX.PopupMenu.show(
				uid,
				label,
				menuItems,
				{
					autoHide: true,
					offsetLeft: 25,
					angle: { position: 'top'},
					events: {
						onPopupClose: function ()
						{
							this.destroy();
						}
					},
					overlay: { backgroundColor: 'transparent' }
				}
			);

			this.valueTypeMenu = BX.PopupMenu.currentItem;
		},
		onBasisClick: function(e, labelNode, callback, delayType)
		{
			var me = this, i, menuItems = [];

			if (delayType === DelayInterval.Type.After)
			{
				menuItems.push({
					text: BX.message('BIZPROC_AUTOMATION_CMP_BASIS_NOW'),
					field: {Name: BX.message('BIZPROC_AUTOMATION_CMP_BASIS_NOW'), SystemExpression: DelayInterval.Basis.CurrentDateTime},
					onclick: function(e, item)
					{
						if (callback)
							callback(item.field);

						this.popupWindow.close();
					}
				},{
					text: BX.message('BIZPROC_AUTOMATION_CMP_BASIS_DATE'),
					field: {Name: BX.message('BIZPROC_AUTOMATION_CMP_BASIS_DATE'), SystemExpression: DelayInterval.Basis.CurrentDate},
					onclick: function(e, item)
					{
						if (callback)
							callback(item.field);

						this.popupWindow.close();
					}
				}, {delimiter: true});
			}

			for (i = 0; i < this.basisFields.length; ++i)
			{
				if (
					delayType !== DelayInterval.Type.After
					&& this.basisFields[i]['Id'].indexOf('DATE_CREATE') > -1
				)
				{
					continue;
				}

				menuItems.push({
					text: BX.util.htmlspecialchars(this.basisFields[i].Name),
					field: this.basisFields[i],
					onclick: function(e, item)
					{
						if (callback)
							callback(item.field || item.options.field);

						this.popupWindow.close();
					}
				});
			}

			var menuId = labelNode.getAttribute('data-menu-id');
			if (!menuId)
			{
				menuId = Component.generateUniqueId();
				labelNode.setAttribute('data-menu-id', menuId);
			}

			BX.PopupMenu.show(
				menuId,
				labelNode,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(labelNode)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					overlay: { backgroundColor: 'transparent' }
				}
			);

			this.fieldsMenu = BX.PopupMenu.currentItem;
		},
		getBasisField: function(basis, system)
		{
			if (system && (basis === DelayInterval.Basis.CurrentDateTime || basis === DelayInterval.Basis.CurrentDateTimeLocal))
			{
				return {Name: BX.message('BIZPROC_AUTOMATION_CMP_BASIS_NOW'), SystemExpression: DelayInterval.Basis.CurrentDateTime};
			}
			if (system && basis === DelayInterval.Basis.CurrentDate)
			{
				return {Name: BX.message('BIZPROC_AUTOMATION_CMP_BASIS_DATE'), SystemExpression: DelayInterval.Basis.CurrentDate};
			}

			var field = null;
			for (var i = 0; i < this.basisFields.length; ++i)
			{
				if (basis === this.basisFields[i].SystemExpression)
					field = this.basisFields[i];
			}
			return field;
		},
		prepareBasisFields: function()
		{
			var i, fld, fields = [];
			for (i = 0; i < this.basisFields.length; ++i)
			{
				fld = this.basisFields[i];
				if (
					fld['Id'].indexOf('DATE_MODIFY') < 0
					&& fld['Id'].indexOf('EVENT_DATE') < 0
					&& fld['Id'].indexOf('BIRTHDATE') < 0
				)
					fields.push(fld);
			}
			this.basisFields = fields;
		}
	};
	// <- DelayIntervalSelector
	var Designer = {
		setRobotSettingsDialog: function(dialog)
		{
			this.robotSettingsDialog = dialog;
			this.component = dialog ? dialog.robot.component : null;
			this.robot = dialog ? dialog.robot : null;
		},
		getRobotSettingsDialog: function()
		{
			return this.robotSettingsDialog;
		},
		setTriggerSettingsDialog: function(dialog)
		{
			this.triggerSettingsDialog = dialog;
			this.component = dialog ? dialog.component : null;
		},
		getTriggerSettingsDialog: function()
		{
			return this.triggerSettingsDialog;
		}
	};

	//Private helpers
	var DelayInterval = function (params)
	{
		this.basis = DelayInterval.Basis.CurrentDateTime;
		this.type = DelayInterval.Type.After;
		this.value = 0;
		this.valueType = 'i';
		this.workTime = false;
		this.localTime = false;

		if (BX.type.isPlainObject(params))
		{
			if (params['type'])
				this.setType(params['type']);
			if (params['value'])
				this.setValue(params['value']);
			if (params['valueType'])
				this.setValueType(params['valueType']);
			if (params['basis'])
				this.setBasis(params['basis']);
			if (params['workTime'])
				this.setWorkTime(params['workTime']);
			if (params['localTime'])
				this.setLocalTime(params['localTime']);
		}
	};

	DelayInterval.Type = {
		After: 'after',
		Before: 'before',
		In: 'in'
	};

	DelayInterval.Basis = {
		CurrentDate: '{=System:Date}',
		CurrentDateTime: '{=System:Now}',
		CurrentDateTimeLocal: '{=System:NowLocal}'
	};

	DelayInterval.isSystemBasis = function(basis)
	{
		return (
			basis === this.Basis.CurrentDate
			|| basis === this.Basis.CurrentDateTime
			|| basis === this.Basis.CurrentDateTimeLocal
		);
	};

	DelayInterval.fromString = function(intervalString, basisFields)
	{
		intervalString = intervalString.toString();
		var params = {
			basis: DelayInterval.Basis.CurrentDateTime,
			i: 0,
			h: 0,
			d: 0,
			workTime: false,
			localTime: false
		};

		if (intervalString.indexOf('=dateadd(') === 0 || intervalString.indexOf('=workdateadd(') === 0)
		{
			if (intervalString.indexOf('=workdateadd(') === 0)
			{
				intervalString = intervalString.substr(13);
				params['workTime'] = true;
			}
			else
			{
				intervalString = intervalString.substr(9);
			}

			var fnArgs = intervalString.split(',');
			params['basis'] = fnArgs[0].trim();
			fnArgs[1] = fnArgs[1].replace(/['")]+/g, '');
			params['type'] = fnArgs[1].indexOf('-') === 0 ? DelayInterval.Type.Before : DelayInterval.Type.After;

			var match, re = /s*([\d]+)\s*(i|h|d)\s*/ig;
			while (match = re.exec(fnArgs[1]))
			{
				params[match[2]] = parseInt(match[1]);
			}
		}
		else
		{
			params['basis'] = intervalString;
		}

		if (!DelayInterval.isSystemBasis(params['basis']) && BX.type.isArray(basisFields))
		{
			var found = false;
			for (var i = 0, s = basisFields.length; i < s; ++i)
			{
				if (params['basis'] === basisFields[i].SystemExpression || params['basis'] === basisFields[i].Expression)
				{
					params['basis'] = basisFields[i].SystemExpression;
					found = true;
					break;
				}
			}
			if (!found)
			{
				params['basis'] = DelayInterval.Basis.CurrentDateTime;
			}
		}

		var minutes = params['i'] + params['h'] * 60 + params['d'] * 60 * 24;

		if (minutes % 1440 === 0)
		{
			params['value'] = minutes / 1440;
			params['valueType'] = 'd';
		}
		else if (minutes % 60 === 0)
		{
			params['value'] = minutes / 60;
			params['valueType'] = 'h';
		}
		else
		{
			params['value'] = minutes;
			params['valueType'] = 'i';
		}

		if (!params['value'] && params['basis'] !== DelayInterval.Basis.CurrentDateTime && params['basis'])
		{
			params['type'] = DelayInterval.Type.In;
		}

		return new DelayInterval(params);
	};

	DelayInterval.fromMinutes = function(minutes)
	{
		var value, type;
		if (minutes % 1440 === 0)
		{
			value = minutes / 1440;
			type = 'd';
		}
		else if (minutes % 60 === 0)
		{
			value = minutes / 60;
			type = 'h';
		}
		else
		{
			value = minutes;
			type = 'i';
		}

		return [value, type];
	}

	DelayInterval.toMinutes = function(value, valueType)
	{
		var result = 0;
		switch (valueType)
		{
			case 'i':
				result = value;
				break;
			case 'h':
				result = value * 60;
				break;
			case 'd':
				result = value * 60 * 24;
				break;
		}
		return result;
	}

	DelayInterval.prototype = {
		setType: function(type)
		{
			if (
				type !== DelayInterval.Type.After
				&& type !== DelayInterval.Type.Before
				&& type !== DelayInterval.Type.In
			)
			{
				type = DelayInterval.Type.After;
			}
			this.type = type;
		},
		setValue: function(value)
		{
			value = parseInt(value);
			this.value = value >= 0 ? value : 0;
		},
		setValueType: function(valueType)
		{
			if (valueType !== 'i' && valueType !== 'h' && valueType !== 'd')
				valueType = 'i';

			this.valueType = valueType;
		},
		setBasis: function(basis)
		{
			if (BX.type.isNotEmptyString(basis))
				this.basis = basis;
		},
		setWorkTime: function(flag)
		{
			this.workTime = !!flag;
		},
		setLocalTime: function(flag)
		{
			this.localTime = !!flag;
		},
		isNow: function()
		{
			return (
				this.type === DelayInterval.Type.After
				&& this.basis === DelayInterval.Basis.CurrentDateTime
				&& !this.value
			);
		},
		setNow: function()
		{
			this.setType(DelayInterval.Type.After);
			this.setValue(0);
			this.setValueType('i');
			this.setBasis(DelayInterval.Basis.CurrentDateTime);
		},
		serialize: function()
		{
			return {
				type: this.type,
				value: this.value,
				valueType: this.valueType,
				basis: this.basis,
				workTime: this.workTime ? 1 : 0
			}
		},
		toExpression: function(basisFields, workerExpression)
		{
			var basis = this.basis ? this.basis : DelayInterval.Basis.CurrentDate;

			if (!DelayInterval.isSystemBasis(basis) && BX.type.isArray(basisFields))
			{
				for (var i = 0, s = basisFields.length; i < s; ++i)
				{
					if (basis === basisFields[i].SystemExpression)
					{
						basis = basisFields[i].Expression;
						break;
					}
				}
			}

			if (!this.workTime && (this.type === DelayInterval.Type.In || this.isNow()))
			{
				return basis;
			}

			var days = 0, hours = 0, minutes = 0;

			switch (this.valueType)
			{
				case 'i':
					minutes = this.value;
				break;
				case 'h':
					hours = this.value;
				break;
				case 'd':
					days = this.value;
				break;
			}

			var add = '';

			if (this.type === DelayInterval.Type.Before)
				add = '-';

			if (days > 0)
				add += days+'d';
			if (hours > 0)
				add += hours+'h';
			if (minutes > 0)
				add += minutes+'i';

			var fn = this.workTime ? 'workdateadd' : 'dateadd';

			if (fn === 'workdateadd' && add === '')
			{
				add = '0d';
			}

			var worker = '';
			if (fn === 'workdateadd' && workerExpression)
			{
				worker = workerExpression;
			}

			return '='+ fn + '(' + basis + ',"' + add + '"' + (worker ? ',' + worker : '') + ')';
		}
	};

	//Conditions
	var Condition = function (params, group)
	{
		this.object = 'Document';
		this.field = '';
		this.operator = '!empty';
		this.value = '';

		this.parentGroup = null;

		if (BX.type.isPlainObject(params))
		{
			if (params['object'])
			{
				this.setObject(params['object']);
			}
			if (params['field'])
			{
				this.setField(params['field']);
			}
			if (params['operator'])
			{
				this.setOperator(params['operator']);
			}
			if ('value' in params)
			{
				this.setValue(params['value']);
			}
		}
		if (group)
		{
			this.parentGroup = group;
		}
	};

	Condition.prototype = {
		setObject: function(object)
		{
			if (BX.type.isNotEmptyString(object))
			{
				this.object = object;
			}
		},
		setField: function(field)
		{
			if (BX.type.isNotEmptyString(field))
			{
				this.field = field;
			}
		},
		setOperator: function(operator)
		{
			if (!operator)
			{
				operator = '=';
			}
			this.operator = operator;
		},
		setValue: function(value)
		{
			this.value = value;
			if (this.operator === '=' && this.value === '')
			{
				this.operator = 'empty';
			}
			else if (this.operator === '!=' && this.value === '')
			{
				this.operator = '!empty';
			}
		},
		serialize: function()
		{
			return {
				object: this.object,
				field: this.field,
				operator: this.operator,
				value: this.value
			}
		}
	};

	var ConditionGroup = function (params)
	{
		this.type = ConditionGroup.Type.Field;
		this.items = [];

		if (BX.type.isPlainObject(params))
		{
			if (params['type'])
			{
				this.type = params['type'];
			}
			if (BX.type.isArray(params['items']))
			{
				var me = this;
				params['items'].forEach(function(item)
				{
					var condition = new Condition(item[0], me);
					me.addItem(condition, item[1]);
				});
			}
		}
	};

	ConditionGroup.Type = {Field: 'field', Mixed: 'mixed'};
	ConditionGroup.Joiner = {
		And: 'AND',
		Or: 'OR',
		message: function(type)
		{
			if (type === this.Or)
			{
				return BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_OR');
			}
			return BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_AND');
		}
	};

	ConditionGroup.createFromForm = function(formFields, prefix)
	{
		var i, conditionGroup = new ConditionGroup();
		if (!prefix)
		{
			prefix = 'condition_';
		}

		if (BX.type.isArray(formFields[prefix + 'field']))
		{
			for (i = 0; i < formFields[prefix + 'field'].length; ++i)
			{
				if (formFields[prefix + 'field'][i] === '')
				{
					continue;
				}

				var condition = new Condition({}, conditionGroup);
				condition.setObject(formFields[prefix + 'object'][i]);
				condition.setField(formFields[prefix + 'field'][i]);
				condition.setOperator(formFields[prefix + 'operator'][i]);
				condition.setValue(formFields[prefix + 'value'][i]);

				var joiner = ConditionGroup.Joiner.And;
				if (formFields[prefix + 'joiner'] && formFields[prefix + 'joiner'][i] === ConditionGroup.Joiner.Or)
				{
					joiner = ConditionGroup.Joiner.Or;
				}

				conditionGroup.addItem(condition, joiner);
			}
		}
		return conditionGroup;
	}

	ConditionGroup.prototype = {
		/**
		 * @param {Condition} condition
		 * @param {string} joiner
		 */
		addItem: function(condition, joiner)
		{
			this.items.push([condition, joiner]);
		},
		serialize: function()
		{
			var itemsArray = [];

			this.items.forEach(function(item)
			{
				if (item.field !== '')
				{
					itemsArray.push([item[0].serialize(), item[1]]);
				}
			});

			return {
				type: this.type,
				items: itemsArray
			}
		}
	};

	// -> ConditionGroupSelector
	/** @param {ConditionGroup} conditionGroup
	 * @param options
	 */
	var ConditionGroupSelector = function(conditionGroup, options)
	{
		this.conditionGroup = conditionGroup;
		this.fields = [];
		this.fieldPrefix = 'condition_';
		this.itemSelectors = [];

		if (BX.type.isPlainObject(options))
		{
			if (BX.type.isArray(options.fields))
			{
				this.fields = options.fields;
			}
			if (options.fieldPrefix)
			{
				this.fieldPrefix = options.fieldPrefix;
			}
		}
	};
	ConditionGroupSelector.prototype =
	{
		createNode: function()
		{
			var me = this, conditionNodes = [], fields = this.fields;

			this.conditionGroup.items.forEach(function(item)
			{
				var conditionSelector = new ConditionSelector(item[0], {
					fields: fields,
					joiner: item[1],
					fieldPrefix: me.fieldPrefix
				});

				this.itemSelectors.push(conditionSelector);
				conditionNodes.push(conditionSelector.createNode());
			}, this);

			conditionNodes.push(BX.create("a", {
				attrs: { className: "bizproc-automation-popup-settings-link" },
				text: '[+]',
				events: {
					click: function()
					{
						me.addItem(this);
					}
				}
			}));

			var div = BX.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-link-wrapper" },
				children: conditionNodes
			});

			return div;
		},
		addItem: function(buttonNode)
		{
			var conditionSelector = new ConditionSelector(new Condition({}, this.conditionGroup), {
				fields: this.fields,
				fieldPrefix: this.fieldPrefix
			});
			this.itemSelectors.push(conditionSelector);

			buttonNode.parentNode.insertBefore(conditionSelector.createNode(), buttonNode);
		},
		destroy: function()
		{
			this.itemSelectors.forEach(function(selector) {
				selector.destroy();
			});
			this.itemSelectors = [];
		}
	};
	// <- ConditionGroupSelector
	// -> ConditionSelector
	var ConditionSelector = function(condition, options)
	{
		this.condition = condition;
		this.fields = [];
		this.joiner = ConditionGroup.Joiner.And;
		this.fieldPrefix = 'condition_';
		if (BX.type.isPlainObject(options))
		{
			if (BX.type.isArray(options.fields))
			{
				this.fields = options.fields.map(function(field) {
					field.ObjectId = 'Document';
					return field;
				});
			}
			if (options.joiner && options.joiner === ConditionGroup.Joiner.Or)
			{
				this.joiner = ConditionGroup.Joiner.Or;
			}
			if (options.fieldPrefix)
			{
				this.fieldPrefix = options.fieldPrefix;
			}
		}
	};
	ConditionSelector.prototype =
	{
		createNode: function()
		{
			var conditionObjectNode = this.objectNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: this.fieldPrefix + "object[]",
					value: this.condition.object
				}
			});
			var conditionFieldNode = this.fieldNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: this.fieldPrefix + "field[]",
					value: this.condition.field
				}
			});
			var conditionOperatorNode = this.operatorNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: this.fieldPrefix + "operator[]",
					value: this.condition.operator
				}
			});
			var conditionValueNode = this.valueNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: this.fieldPrefix + "value[]",
					value: this.condition.value
				}
			});

			var conditionJoinerNode = this.joinerNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: this.fieldPrefix + "joiner[]",
					value: this.joiner
				}
			});

			var labelNode = this.labelNode = BX.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-link-wrapper"
				}
			});

			this.setLabelText();
			this.bindLabelNode();

			var removeButtonNode = BX.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-link-remove"
				},
				events: {
					click: this.removeCondition.bind(this)
				}
			});

			var joinerButtonNode = BX.create("span", {
				attrs: {
					className: "bizproc-automation-popup-settings-link bizproc-automation-condition-joiner"
				},
				text: ConditionGroup.Joiner.message(this.joiner)
			});

			BX.bind(joinerButtonNode, 'click', this.changeJoiner.bind(this, joinerButtonNode));

			this.node = BX.create("span", {
				attrs: { className: "bizproc-automation-popup-settings-link-wrapper bizproc-automation-condition-wrapper" },
				children: [
					conditionObjectNode,
					conditionFieldNode,
					conditionOperatorNode,
					conditionValueNode,
					conditionJoinerNode,
					labelNode,
					removeButtonNode,
					joinerButtonNode
				]
			});

			return this.node;
		},
		init: function(condition)
		{
			this.condition = condition;
			this.setLabelText();
			this.bindLabelNode();
		},
		setLabelText: function()
		{
			if (!this.labelNode || !this.condition)
				return;

			BX.cleanNode(this.labelNode);

			if (this.condition.field !== '')
			{
				var field = this.getField(this.condition.object, this.condition.field) || '?';
				var valueLabel = BX.Bizproc.FieldType.formatValuePrintable(
					field,
					this.condition.value
				);

				this.labelNode.appendChild(BX.create("span", {
					attrs: {
						className: "bizproc-automation-popup-settings-link"
					},
					text: field.Name
				}));
				this.labelNode.appendChild(BX.create("span", {
					attrs: {
						className: "bizproc-automation-popup-settings-link"
					},
					text: this.getOperatorLabel(this.condition.operator)
				}));
				if (valueLabel)
				{
					this.labelNode.appendChild(BX.create("span", {
						attrs: {
							className: "bizproc-automation-popup-settings-link"
						},
						text: valueLabel
					}));
				}
			}
			else
			{
				this.labelNode.appendChild(BX.create("span", {
					attrs: {
						className: "bizproc-automation-popup-settings-link"
					},
					text: BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY')
				}));
			}
		},
		bindLabelNode: function()
		{
			if (this.labelNode)
			{
				BX.bind(this.labelNode, 'click', BX.delegate(this.onLabelClick, this));
			}
		},
		onLabelClick: function(e)
		{
			this.showPopup();
		},
		showPopup: function()
		{
			if (this.popup)
			{
				this.popup.show();
				return;
			}

			var me = this, fields = this.filterFields();
			var objectSelect = BX.create('input', {
				attrs: {
					type: 'hidden',
					className: 'bizproc-automation-popup-settings-dropdown'
				}
			});
			var fieldSelect = BX.create('input', {
				attrs: {
					type: 'hidden',
					className: 'bizproc-automation-popup-settings-dropdown'
				}
			});
			var fieldSelectLabel = BX.create('div', {
				attrs: {
					className: 'bizproc-automation-popup-settings-dropdown',
					readonly: 'readonly'
				},
				children: [fieldSelect]
			});

			BX.bind(
				fieldSelectLabel,
				'click',
				this.onFieldSelectorClick.bind(this, fieldSelectLabel, fieldSelect, fields, objectSelect)
			);

			var selectedField = this.getField(this.condition.object, this.condition.field);
			if (!this.condition.field)
			{
				selectedField = fields[0];
			}
			fieldSelect.value = selectedField.Id;
			objectSelect.value = selectedField.ObjectId;
			fieldSelectLabel.textContent = selectedField.Name;

			var valueInput = (this.condition.operator.indexOf('empty') < 0)
				? this.createValueNode(selectedField, this.condition.value) : null;

			var valueWrapper = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
				children: [valueInput]
			});

			var operatorSelect = this.createOperatorNode(selectedField, valueWrapper);
			var operatorWrapper = BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
				children: [operatorSelect]
			});

			if (this.condition.field !== '')
			{
				operatorSelect.value = this.condition.operator;
			}

			var form = BX.create("form", {
				attrs: { className: "bizproc-automation-popup-select-block" },
				children: [
					BX.create('div', {attrs: {className: 'bizproc-automation-popup-settings'},
						children: [fieldSelectLabel]
					}),
					operatorWrapper,
					valueWrapper
				]
			});

			BX.bind(fieldSelect, 'change', this.onFieldChange.bind(
				this,
				fieldSelect,
				operatorWrapper,
				valueWrapper,
				objectSelect
			));

			var popup = this.popup = new BX.PopupWindow('bizproc-automation-popup-set', this.labelNode, {
				className: 'bizproc-automation-popup-set',
				autoHide: false,
				closeByEsc: true,
				closeIcon: false,
				titleBar: false,
				angle: true,
				offsetLeft: 45,
				overlay: { backgroundColor: 'transparent' },
				content: form,
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('BIZPROC_AUTOMATION_CMP_CHOOSE'),
						className: "webform-button webform-button-create" ,
						events: {
							click: function() {
								me.condition.setObject(objectSelect.value);
								me.condition.setField(fieldSelect.value);
								me.condition.setOperator(operatorWrapper.firstChild.value);
								var valueInput = valueWrapper.querySelector('[name^="'+me.fieldPrefix+'value"]');

								if (valueInput)
								{
									me.condition.setValue(valueInput.value);
								}
								else
								{
									me.condition.setValue('');
								}

								me.setLabelText();
								me.updateValueNodes();
								this.popupWindow.close();
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('JS_CORE_WINDOW_CANCEL'),
						className : "popup-window-button-link-cancel",
						events : {
							click: function(){
								this.popupWindow.close()
							}
						}
					})
				],
				events: {
					onPopupClose: function() {
						this.destroy();
						if (me.fieldDialog)
						{
							me.fieldDialog.destroy();
							delete(me.fieldDialog);
						}
						delete(me.popup);
					}
				}
			});

			popup.show()
		},
		onFieldSelectorClick: function(fieldSelectLabel, fieldSelect, fields, objectSelect, event)
		{
			if (!this.fieldDialog)
			{
				this.fieldDialog = new InlineSelectorCondition(fieldSelectLabel, fields, function(property)
				{
					fieldSelectLabel.textContent = property.Name
					fieldSelect.value = property.Id;
					objectSelect.value = property.ObjectId;
					BX.fireEvent(fieldSelect, 'change');
				}, this.condition);
			}
			this.fieldDialog.openMenu(event);
		},
		updateValueNodes: function()
		{
			if (this.condition)
			{
				if (this.objectNode)
				{
					this.objectNode.value = this.condition.object;
				}
				if (this.fieldNode)
				{
					this.fieldNode.value = this.condition.field;
				}
				if (this.operatorNode)
				{
					this.operatorNode.value = this.condition.operator;
				}
				if (this.valueNode)
				{
					this.valueNode.value = this.condition.value;
				}
			}
		},
		/**
		 * @param {Node} selectNode
		 * @param {Node} conditionWrapper
		 * @param {Node} valueWrapper
		 * @param objectSelect
		 */
		onFieldChange: function(selectNode, conditionWrapper, valueWrapper, objectSelect)
		{
			var field = this.getField(objectSelect.value, selectNode.value);
			var operatorNode = this.createOperatorNode(field, valueWrapper);
			conditionWrapper.replaceChild(operatorNode, conditionWrapper.firstChild);
			this.onOperatorChange(operatorNode, field, valueWrapper);
		},
		/**
		 * @param {Node} selectNode
		 * @param {Object} field
		 * @param {Node} valueWrapper
		 */
		onOperatorChange: function(selectNode, field, valueWrapper)
		{
			BX.cleanNode(valueWrapper);

			if (selectNode.value.indexOf('empty') < 0)
			{
				var valueNode = this.createValueNode(field);
				valueWrapper.appendChild(valueNode);
			}
		},
		getField: function(object, id)
		{
			var field;
			var robot = Designer.robot;
			var component = Designer.component;
			var tpl = robot? robot.template : null;
			switch (object)
			{
				case 'Document':
					for (var i = 0; i < this.fields.length; ++i)
					{
						if (id === this.fields[i].Id)
						{
							field = this.fields[i];
						}
					}
				break;
				case 'Template':
					if (tpl && component && component.triggerManager)
					{
						field = component.triggerManager.getReturnProperty(tpl.getStatusId(), id);
					}
				break;
				case 'Constant':
					if (tpl)
					{
						field = tpl.getConstant(id);
					}
				break;
				case 'GlobalConst':
					if (component)
					{
						field = component.getConstant(id);
					}
				break;
				default:
					var foundRobot = tpl? tpl.getRobotById(object) : null;
					if (foundRobot)
					{
						field = foundRobot.getReturnProperty(id);
					}
				break;
			}

			return field || {
				Id: id,
				ObjectId: object,
				Name: id,
				Type: 'string',
				Expression: id,
				SystemExpression: '{='+object+':'+id+'}'
			};
		},
		getOperators: function(fieldType, multiple)
		{
			var list = {
				'!empty': BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
				'empty': BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY'),
				'=': BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_EQ'),
				'!=': BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NE')
			};
			switch (fieldType)
			{
				case 'file':
				case 'UF:crm':
				case 'UF:resourcebooking':
					list = {
						'!empty': BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
						'empty': BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_EMPTY')
					};
					break;
				case 'bool':
				case 'select':
					if (multiple)
					{
						list['contain'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
						list['!contain'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
					}
					else
					{
						//TODO: render multiple select in value selector
						//list['in'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
					}
					break;
				case 'user':
					list['in'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
					list['!in'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_IN');
					list['contain'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
					list['!contain'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
					break;
				default:
					list['in'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_IN');
					list['!in'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_IN');
					list['contain'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_CONTAIN');
					list['!contain'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_NOT_CONTAIN');
					list['>'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_GT');
					list['>='] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_GTE');
					list['<'] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_LT');
					list['<='] = BX.message('BIZPROC_AUTOMATION_ROBOT_CONDITION_LTE');
			}

			return list;
		},
		getOperatorLabel: function(id)
		{
			return this.getOperators()[id];
		},
		filterFields: function()
		{
			var i, type, filtered = [];
			for (i = 0; i < this.fields.length; ++i)
			{
				type = this.fields[i]['Type'];
				if (
					type == 'bool'
					|| type == 'date'
					|| type == 'datetime'
					|| type == 'double'
					|| type == 'file'
					|| type == 'int'
					|| type == 'select'
					|| type == 'string'
					|| type == 'text'
					|| type == 'user'
					|| type == 'UF:money'
					|| type == 'UF:crm'
					|| type == 'UF:resourcebooking'
					|| type == 'UF:url'
				)
				{
					filtered.push(this.fields[i]);
				}
				else
				{
					//TODO add support of custom types
				}
			}
			return filtered;
		},
		createValueNode: function(docField, value)
		{
			var docType = Designer.component ? Designer.component.documentType : API.documentType;
			var field = BX.clone(docField);
			field.Multiple = false;
			return BX.Bizproc.FieldType.renderControl(
				docType,
				field,
				this.fieldPrefix + 'value',
				value
			);
		},
		createOperatorNode: function(field, valueWrapper)
		{
			var select = BX.create('select', {
				attrs: {className: 'bizproc-automation-popup-settings-dropdown'}
			});

			var operatorList = this.getOperators(field['Type'], field['Multiple']);
			for (var operatorId in operatorList)
			{
				if (!operatorList.hasOwnProperty(operatorId))
					continue;
				select.appendChild(BX.create('option', {
					props: {value: operatorId},
					text: operatorList[operatorId]
				}));
			}

			BX.bind(select, 'change', this.onOperatorChange.bind(
				this,
				select,
				field,
				valueWrapper
			));

			return select;
		},
		/**
		 * @param {Event} e
		 */
		removeCondition: function(e)
		{
			this.condition = null;
			BX.remove(this.node);
			this.labelNode = this.fieldNode = this.operatorNode = this.valueNode = this.node = null;

			e.stopPropagation();
		},
		/**
		 * @param {Element} btn
		 * @param {Event} e
		 */
		changeJoiner: function(btn, e)
		{
			this.joiner = (this.joiner === ConditionGroup.Joiner.Or ? ConditionGroup.Joiner.And : ConditionGroup.Joiner.Or);
			btn.textContent = ConditionGroup.Joiner.message(this.joiner);

			if (this.joinerNode)
			{
				this.joinerNode.value = this.joiner;
			}

			e.preventDefault();
		},
		destroy: function()
		{
			if (this.popup)
			{
				this.popup.close();
			}
		}
	};
	// <- ConditionSelector

	var formatDelayInterval = function(delay, emptyText, fields)
	{
		var str = emptyText, prefix;

		if (delay.type == DelayInterval.Type.In)
		{
			str = BX.message('BIZPROC_AUTOMATION_CMP_IN_TIME');
			if (BX.type.isArray(fields))
			{
				for (var i = 0; i < fields.length; ++i)
				{
					if (delay.basis == fields[i].SystemExpression)
					{
						str += ' ' + fields[i].Name;
						break;
					}
				}
			}
		}
		else if (delay.value)
		{
			prefix = delay.type == DelayInterval.Type.After ?
				BX.message('BIZPROC_AUTOMATION_CMP_THROUGH') : BX.message('BIZPROC_AUTOMATION_CMP_FOR_TIME_1');

			str = prefix + ' ' + getFormattedPeriodLabel(delay.value, delay.valueType);

			if (BX.type.isArray(fields))
			{
				var fieldSuffix = delay.type == DelayInterval.Type.After ?
					BX.message('BIZPROC_AUTOMATION_CMP_AFTER') : BX.message('BIZPROC_AUTOMATION_CMP_BEFORE_1');
				for (var i = 0; i < fields.length; ++i)
				{
					if (delay.basis == fields[i].SystemExpression)
					{
						str += ' ' + fieldSuffix + ' ' + fields[i].Name;
						break;
					}
				}
			}
		}

		if (delay.workTime)
		{
			str += ', ' + BX.message('BIZPROC_AUTOMATION_CMP_IN_WORKTIME');
		}

		return str;
	};

	var getPeriodLabels = function(period)
	{
		var labels = [];
		if (period === 'i')
			labels = [
				BX.message('BIZPROC_AUTOMATION_CMP_MIN1'),
				BX.message('BIZPROC_AUTOMATION_CMP_MIN2'),
				BX.message('BIZPROC_AUTOMATION_CMP_MIN3')
			];
		else if (period === 'h')
			labels = [
				BX.message('BIZPROC_AUTOMATION_CMP_HOUR1'),
				BX.message('BIZPROC_AUTOMATION_CMP_HOUR2'),
				BX.message('BIZPROC_AUTOMATION_CMP_HOUR3')
			];
		else if (period === 'd')
			labels = [
				BX.message('BIZPROC_AUTOMATION_CMP_DAY1'),
				BX.message('BIZPROC_AUTOMATION_CMP_DAY2'),
				BX.message('BIZPROC_AUTOMATION_CMP_DAY3')
			];

		return labels;
	};

	var getFormattedPeriodLabel = function(value, type)
	{
		var label = value + ' ';
		var labelIndex = 0;
		if (value > 20)
			value = (value % 10);

		if (value == 1)
			labelIndex = 0;
		else if (value > 1 && value < 5)
			labelIndex = 1;
		else
			labelIndex = 2;

		var labels = getPeriodLabels(type);
		return label + (labels ? labels[labelIndex] : '');
	};

	var HelpHint = {
		popupHint: null,

		bindToNode: function(node)
		{
			BX.bind(node, 'mouseover', BX.proxy(function(){
				this.showHint(BX.proxy_context);
			}, this));
			BX.bind(node, 'mouseout', BX.delegate(this.hideHint, this));
		},
		showHint: function(node)
		{
			var rawText = node.getAttribute('data-text');
			if (!rawText)
				return;
			var text = BX.util.htmlspecialchars(rawText);
			text = BX.util.nl2br(text);
			if (!BX.type.isNotEmptyString(text))
				return;

			this.popupHint = new BX.PopupWindow('bizproc-automation-help-tip', node, {
				lightShadow: true,
				autoHide: false,
				darkMode: true,
				offsetLeft: 0,
				offsetTop: 2,
				bindOptions: {position: "top"},
				events : {
					onPopupClose : function() {this.destroy()}
				},
				content : BX.create("div", { attrs : { style : "padding-right: 5px; width: 250px;" }, html: text})
			});
			this.popupHint.setAngle({offset:32, position: 'bottom'});
			this.popupHint.show();

			return true;
		},
		hideHint: function()
		{
			if (this.popupHint)
				this.popupHint.close();
			this.popupHint = null;
		}
	};

	var API = {
		documentName: null,
		documentType: null,
		documentFields: null,
		documentSigned: null,
		showRobotSettings: function(robotData, documentType, documentStatus, onSaveCallback)
		{
			var robot = new Robot();
			robot.init(robotData, Component.ViewMode.None);

			var tpl = new Template();
			tpl.init({
				DOCUMENT_STATUS: documentStatus,
				DOCUMENT_SIGNED: this.documentSigned,
				DOCUMENT_FIELDS: this.documentFields
			}, Component.ViewMode.None);

			tpl.openRobotSettingsDialog(robot, null, onSaveCallback);
		}
	};
	BX.Bizproc.Automation.Component = Component;
	BX.Bizproc.Automation.Designer = Designer;
	BX.Bizproc.Automation.API = API;
	BX.Bizproc.Automation.ConditionGroup = ConditionGroup;
	BX.Bizproc.Automation.ConditionGroupSelector = ConditionGroupSelector;

})(window.BX || window.top.BX);