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
		Designer.getInstance().component = this;
		showGlobals.component = this;
		Debugger.component = this;
	};

	Component.ViewMode = {
		None: 0,
		View : 1,
		Edit: 2,
		Manage: 3,
	};

	var getAjaxUrl = function(url)
	{
		url = url || '/bitrix/components/bitrix/bizproc.automation/ajax.php';
		return  BX.util.add_url_param(url, {
			site_id: BX.message('SITE_ID'),
			sessid: BX.bitrix_sessid()
		});
	};

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

	Component.prototype =
	{
		init: function(data, viewMode)
		{
			var me = this;

			this.viewMode = viewMode || Component.ViewMode.Edit;

			if (typeof data === 'undefined')
				data = {};

			this.data = data;
			this.initData();
			this.initTracker();
			this.initContext();

			this.initActionPanel();
			this.initSearch();
			this.initTriggerManager();
			this.initTemplateManager();
			this.initButtons();
			this.initButtonsPosition();
			this.initHelpTips();
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
			this.document = new BX.Bizproc.Document({
				rawDocumentType: this.data.DOCUMENT_TYPE,
				documentId: this.data.DOCUMENT_ID,
				categoryId: this.data.DOCUMENT_CATEGORY_ID,
				statusList: this.data.DOCUMENT_STATUS_LIST,
				documentFields: this.data.DOCUMENT_FIELDS,
				title: this.data['ENTITY_NAME'],
			});
			this.documentSigned = this.data.DOCUMENT_SIGNED;

			this.bizprocEditorUrl = this.data.WORKFLOW_EDIT_URL;
			this.constantsEditorUrl = this.data.CONSTANTS_EDIT_URL || null;
			this.parametersEditorUrl = this.data.PARAMETERS_EDIT_URL || null;

			this.setDocumentStatus(this.data.DOCUMENT_STATUS);

			var rawUserOptions = {};
			if (BX.type.isPlainObject(this.data.USER_OPTIONS))
			{
				rawUserOptions = this.data.USER_OPTIONS;
			}
			this.userOptions = new BX.Bizproc.UserOptions(rawUserOptions);
			this.frameMode = BX.type.isBoolean(this.data.FRAME_MODE) ? this.data.FRAME_MODE : false;
			this.embeddedMode = (this.data.IS_EMBEDDED === true);
		},
		initContext: function()
		{
			var context = new BX.Bizproc.AutomationContext({
				document: this.document,
				signedDocument: this.documentSigned,
				ajaxUrl: this.getAjaxUrl(),
				availableRobots: BX.type.isArray(this.data['AVAILABLE_ROBOTS']) ? this.data['AVAILABLE_ROBOTS'] : [],
				availableTriggers: BX.Type.isArray(this.data['AVAILABLE_TRIGGERS']) ? this.data['AVAILABLE_TRIGGERS'] : [],
				canManage: this.data['IS_TEMPLATES_SCHEME_SUPPORTED'],
				canEdit: this.canEdit(),
				userOptions: this.userOptions,
				tracker: this.tracker,

				bizprocEditorUrl: this.bizprocEditorUrl,
				constantsEditorUrl: this.constantsEditorUrl,
				parametersEditorUrl: this.parametersEditorUrl,
				isFrameMode: this.isFrameMode,

				marketplaceRobotCategory: this.data['MARKETPLACE_ROBOT_CATEGORY'],
			});
			context.set('TRIGGER_CAN_SET_EXECUTE_BY', this.data['TRIGGER_CAN_SET_EXECUTE_BY']);

			BX.Bizproc.setGlobalContext(context);
		},
		setDocumentStatus: function(status)
		{
			this.document.setStatus(status);

			return this;
		},
		isPreviousStatus: function(needle)
		{
			var previousStatuses = this.document.getPreviousStatusIdList();
			for (var i = 0; i < previousStatuses.length; ++i)
			{
				if (needle === previousStatuses[i])
				{
					 return true;
				}
			}
			return false;
		},
		isCurrentStatus: function(needle)
		{
			return needle === this.document.getCurrentStatusId();
		},
		isNextStatus: function(needle)
		{
			var nextStatuses = this.document.getNextStatusIdList();
			for (var i = 0; i < nextStatuses.length; ++i)
			{
				if (needle === nextStatuses[i])
				{
					return true;
				}
			}

			return false;
		},
		initActionPanel: function ()
		{
			var panelNode = document.querySelector('[data-role="automation-actionpanel"]');
			if (!panelNode)
			{
				return;
			}

			this.actionPanel = new BX.UI.ActionPanel({
				renderTo: panelNode,
				removeLeftPosition: true,
				maxHeight: 58,
				parentPosition: "bottom",
				autoHide: false,
			});

			this.actionPanel.draw();

			var pathToIcons = '/bitrix/components/bitrix/bizproc.automation/templates/.default/image/';
			this.actionPanel.appendItem({
				id: 'automation_choose_all',
				text: BX.message('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_CHOOSE_ALL'),
				icon: pathToIcons + 'bizproc-automation--panel-icon-choose-all.svg',
				onclick: function ()
				{
					var status = this.templateManager.targetManageModeStatus;
					var template = this.templateManager.getTemplateByStatusId(status);
					if (template)
					{
						template.robots.forEach(function (robot)
						{
							robot.selectNode();
						});
					}
				}.bind(this),
			});

			this.actionPanel.appendItem({
				id: 'automation_copy_to',
				text: BX.message('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_COPY'),
				icon: pathToIcons + 'bizproc-automation--panel-icon-copy.svg',
				onclick: this.onCopyMoveButtonClick.bind(this, 'copy'),
			});

			this.actionPanel.appendItem({
				id: 'automation_move_to',
				text: BX.message('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_MOVE'),
				icon: pathToIcons + 'bizproc-automation--panel-icon-move.svg',
				onclick: this.onCopyMoveButtonClick.bind(this, 'move'),
			});

			this.actionPanel.appendItem({
				id: 'automation_delete',
				text: BX.message('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_DELETE'),
				icon: pathToIcons + 'bizproc-automation--panel-icon-delete.svg',
				onclick: this.onDeleteButtonClick.bind(this),
			});

			BX.addCustomEvent("BX.UI.ActionPanel:hidePanel", function ()
			{
				if (this.templateManager.isManageModeEnabled())
				{
					this.disableManageMode();
				}
			}.bind(this));
		},
		onCopyMoveButtonClick: function (action)
		{
			this.viewMode = Component.ViewMode.Edit;
			var selectedStatus = this.templateManager.targetManageModeStatus;
			var template = this.templateManager.getTemplateByStatusId(selectedStatus);
			var selectedRobots = template.getSelectedRobotNames();
			if (selectedRobots.length === 0)
			{
				BX.UI.Notification.Center.notify({
					content: BX.message('BIZPOC_AUTOMATION_NO_ROBOT_SELECTED'),
					autoHideDelay: 4000,
				});
				return;
			}

			BX.SidePanel.Instance.open('/bitrix/components/bitrix/bizproc.automation.scheme/index.php', {
				width: 569,
				cacheable: false,
				requestMethod: 'post',
				requestParams: {
					documentSigned: this.documentSigned,
					templateStatus: this.templateManager.targetManageModeStatus,
					action: action,
					selectedRobots: selectedRobots,
				},
				events: {
					onDestroy: function (event)
					{
						var slider = event.slider;
						if (slider)
						{
							var data = slider.getData();
							var restoreData = data.get('restoreData');
							var targetScope = data.get('targetScope');

							var acceptedRobots = action === 'copy' ? data.get('copied') : data.get('moved');
							var deniedRobots = data.get('denied');

							if (!BX.type.isArray(acceptedRobots) || !BX.type.isArray(deniedRobots))
							{
								return;
							}

							var messageId = 'BIZPROC_AUTOMATION_CMP_ROBOTS_' + (action === 'copy' ? 'COPIED' : 'MOVED');
							var notifyMessage = BX.message(messageId);
							notifyMessage = notifyMessage.replace('#ACCEPTED_COUNT#', acceptedRobots.length);
							notifyMessage =
								notifyMessage.replace('#TOTAL_COUNT#', acceptedRobots.length + deniedRobots.length)
							;

							BX.UI.Notification.Center.notify({
								content: notifyMessage,
								actions: [
									{
										title: BX.message('JS_CORE_WINDOW_CANCEL'),
										events: {
											click: function(event, balloon) {
												event.preventDefault();
												if (BX.type.isArray(restoreData))
												{
													this.saveTemplates(restoreData);
													balloon.close();
												}
											}.bind(this)
										}
									}
								]
							});
							var updatedTemplateStatuses = [];
							var targetStatus = this.templateManager.targetManageModeStatus;
							if (action === 'move')
							{
								updatedTemplateStatuses.push(targetStatus);
							}
							if (
								targetScope
								&& this.data.DOCUMENT_TYPE_SIGNED === targetScope.documentType.Type
								&& this.templateManager.getTemplateByStatusId(targetScope.status.Id)
							)
							{
								updatedTemplateStatuses.push(targetScope.status.Id);
							}

							this.templateManager.updateTemplates(updatedTemplateStatuses).onload = function ()
							{
								var srcTemplate = this.templateManager.getTemplateByStatusId(targetStatus);

								deniedRobots.forEach(function(robotName)
								{
									var robot = srcTemplate.getRobotById(robotName);
									if (robot)
									{
										BX.Dom.addClass(robot.node, '--denied');
										setTimeout(
											BX.Dom.removeClass.bind(null, robot.node, '--denied'),
											10 * 1000
										);
									}
								}.bind(this));
							}.bind(this);
						}
						this.disableManageMode();
					}.bind(this)
				}
			});
		},
		onDeleteButtonClick: function()
		{
			this.viewMode = Component.ViewMode.Edit;
			var status = this.templateManager.targetManageModeStatus;
			var template = this.templateManager.getTemplateByStatusId(status);
			if (!template)
			{
				return;
			}
			var templateIndex = this.templateManager.templatesData.findIndex(function (templateData) {
				return templateData.ID === template.getId();
			});

			var deletingRobots = template.getSelectedRobotNames();
			if (deletingRobots.length === 0)
			{
				BX.UI.Notification.Center.notify({
					content: BX.message('BIZPOC_AUTOMATION_NO_ROBOT_SELECTED'),
					autoHideDelay: 4000,
				});
				return;
			}
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.getAjaxUrl(),
				data: {
					ajax_action: 'delete_robots',
					document_signed: this.documentSigned,
					selected_status: template.getStatusId(),
					robot_names: Helper.toJsonString(deletingRobots),
				},
				onsuccess: function (response)
				{
					var notifyMessage = BX.message('BIZPROC_AUTOMATION_CMP_ROBOTS_DELETED');
					notifyMessage = notifyMessage.replace('#TOTAL_COUNT#', deletingRobots.length);
					if (response.SUCCESS)
					{
						template.reInit(response.DATA.template, this.viewMode);
						this.templateManager.templatesData[templateIndex] = response.DATA.template;
						this.disableManageMode();
						notifyMessage = notifyMessage.replace('#ACCEPTED_COUNT#', deletingRobots.length);

						var restoreData = response.DATA.restoreData;
					}
					else
					{
						notifyMessage = notifyMessage.replace('#ACCEPTED_COUNT#', 0);
					}
					BX.UI.Notification.Center.notify({
						content: notifyMessage,
						actions: [
							{
								title: BX.message('JS_CORE_WINDOW_CANCEL'),
								events: {
									click: function(event, balloon) {
										event.preventDefault();
										if (BX.type.isArray(restoreData))
										{
											this.saveTemplates(restoreData);
											balloon.close();
										}
									}.bind(this)
								}
							}
						]
					});
				}.bind(this)
			});
		},
		initTriggerManager: function()
		{
			this.triggerManager = new TriggerManager(this.node);
			this.subscribeTriggerManagerEvents();
			this.triggerManager.init(this.data, BX.Bizproc.ViewMode.fromRaw(this.viewMode));

			this.triggerManager.subscribe('TriggerManager:onHelpClick', function (event)
			{
				this.onGlobalHelpClick.call(this, event.data)
			}.bind(this));

			BX.Event.EventEmitter.subscribe(
				this,
				'BX.Bizproc.Automation.Component:onSearch',
				this.triggerManager.onSearch.bind(this.triggerManager)
			);
		},
		subscribeTriggerManagerEvents: function ()
		{
			const self = this;
			this.triggerManager.subscribe('TriggerManager:dataModified', function ()
			{
				self.markModified();
			});
		},
		reInitTriggerManager: function(triggers)
		{
			if (BX.type.isArray(triggers))
			{
				this.data.TRIGGERS = triggers;
			}
			this.triggerManager.reInit(this.data, BX.Bizproc.ViewMode.fromRaw(this.viewMode));
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
				this.bindSaveButton();
				this.bindCancelButton();
			}
			this.bindCreationButton();
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
		initSearch: function()
		{
			var searchNode = this.node.querySelector('[data-role="automation-search"]');
			if (searchNode)
			{
				BX.bind(searchNode, 'input', BX.debounce(this.onSearch.bind(this, searchNode), 255));
				BX.Event.EventEmitter.setMaxListeners(this, 'BX.Bizproc.Automation.Component:onSearch', 500);

				var clearNode = this.node.querySelector('[data-role="automation-search-clear"]');
				if (clearNode)
				{
					BX.bind(clearNode, 'click', this.onClearSearch.bind(this, searchNode));
				}
			}
		},
		reInitSearch: function()
		{
			var searchNode = this.node.querySelector('[data-role="automation-search"]');
			if (searchNode)
			{
				this.onSearch(searchNode);
			}
		},
		onSearch: function(searchNode)
		{
			BX.Event.EventEmitter.emit(
				this,
				'BX.Bizproc.Automation.Component:onSearch', {
					queryString: searchNode.value.toLowerCase(),
				}
			);
		},
		onClearSearch: function(searchNode)
		{
			if (searchNode.value !== '')
			{
				searchNode.value = '';
				this.onSearch(searchNode);
			}
		},
		initHelpTips: function()
		{
			BX.UI.Hint.init(this.node);
		},
		reInitButtons: function()
		{
			var changeViewBtn = this.node.querySelector('[data-role="automation-btn-change-view"]');
			if (changeViewBtn)
			{
				changeViewBtn.innerHTML = changeViewBtn.getAttribute('data-label-'
					+(this.viewMode === Component.ViewMode.View ? 'edit' : 'view'));
			}
		},
		enableDragAndDrop: function()
		{
			this.templateManager.enableDragAndDrop();
			this.triggerManager.enableDragAndDrop();
		},
		disableDragAndDrop: function()
		{
			this.templateManager.disableDragAndDrop();
			this.triggerManager.disableDragAndDrop();
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
			this.tracker = new Tracker(this.document, this.getAjaxUrl());
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
					me.reInitTriggerManager();
					me.reInitTemplateManager();
					me.reInitSearch();
					me.markModified(false);
				});
			}
		},
		bindCreationButton: function()
		{
			var me = this, button = this.node.querySelector('[data-role="automation-btn-create"]');

			if (button)
			{
				if (me.canEdit())
				{
					BX.bind(button, 'click', function(e)
					{
						e.preventDefault();

						if (me.viewMode === Component.ViewMode.View)
						{
							me.changeViewMode(Component.ViewMode.Edit);
						}

						var items = [
							{
								text: BX.message('BIZPROC_AUTOMATION_CMP_CREATE_ROBOT'),
								onclick: function(e) {
									this.popupWindow.close();

									var robotData = BX.clone(
										me.templateManager.getRobotDescription('SocNetMessageActivity')
									);

									if (robotData['ROBOT_SETTINGS'] && robotData['ROBOT_SETTINGS']['TITLE'])
									{
										robotData['NAME'] = robotData['ROBOT_SETTINGS']['TITLE'];
									}

									var template = me.templateManager.templates[0];

									template.addRobot(robotData, function(robot)
									{
										this.openRobotSettingsDialog(robot);
									});
								}
							}
						];

						if (BX.type.isArray(me.data.AVAILABLE_TRIGGERS) && me.data.AVAILABLE_TRIGGERS.length)
						{
							items.push({
								text: BX.message('BIZPROC_AUTOMATION_CMP_CREATE_TRIGGER'),
								onclick: function(e) {
									this.popupWindow.close();

									me.triggerManager.addTrigger(
										{
											DOCUMENT_STATUS: me.document.getSortedStatusId(0),
											CODE: me.data.AVAILABLE_TRIGGERS[0].CODE
										}, function(trigger)
										{
											this.openTriggerSettingsDialog(trigger);
										}
									);
								}
							});
						}

						BX.PopupMenu.show(
							BX.Bizproc.Helper.generateUniqueId(),
							button,
							items,
							{
								autoHide: true,
								offsetLeft: (BX.pos(button)['width'] / 2),
								angle: true
							}
						);

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
				triggers_json: Helper.toJsonString(this.triggerManager.serialize()),
				templates_json: Helper.toJsonString(this.templateManager.serializeModified())
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
					response.DATA.templates.forEach(function (updatedTemplate) {
						var updatedTemplateIndex = me.data.TEMPLATES.findIndex(function (template) {
							return template.ID === updatedTemplate.ID;
						});

						me.data.TEMPLATES[updatedTemplateIndex] = updatedTemplate;
					});
					if (response.SUCCESS)
					{
						me.reInitTriggerManager(response.DATA.triggers);
						me.reInitTemplateManager();
						me.reInitSearch();
						me.markModified(false);
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
		saveTemplates: function(templatesData)
		{
			if (this.savingAutomation)
			{
				return;
			}

			var data = {
				ajax_action: 'save_automation',
				document_signed: this.documentSigned,
				templates_json: Helper.toJsonString(templatesData)
			};

			this.savingAutomation = true;
			var self = this;
			return BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.getAjaxUrl(),
				data: data,
				onsuccess: function(response)
				{
					self.savingAutomation = null;

					if (response.SUCCESS)
					{
						templatesData.forEach(function(updatedTemplate) {
							var template = self.templateManager.getTemplateById(updatedTemplate.ID);
							if (template)
							{
								template.reInit(updatedTemplate, self.viewMode);
							}
						});
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
		},
		enableManageMode: function (status)
		{
			if (!this.templateManager.needSave() && !this.triggerManager.needSave())
			{
				this.viewMode = Component.ViewMode.Manage;
				this.templateManager.enableManageMode(status);
				this.triggerManager.enableManageMode();
			}
		},
		disableManageMode: function()
		{
			this.viewMode = Component.ViewMode.Edit;
			this.templateManager.disableManageMode();
			this.triggerManager.disableManageMode();
		},
		markModified: function(modified)
		{
			modified = modified !== false;

			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (!buttonsNode)
			{
				return;
			}

			if (modified && this.canEdit() && this.viewMode === Component.ViewMode.Edit)
			{
				BX.show(buttonsNode);
			}
			else
			{
				BX.hide(buttonsNode);
			}
		},
		canEdit: function()
		{
			return this.data['CAN_EDIT'];
		},
		updateTracker: function()
		{
			this.tracker.update(this.documentSigned).onload = function()
			{
				if (this.viewMode === Component.ViewMode.View)
				{
					this.templateManager.reInit();
				}
			}.bind(this);
		},
		onGlobalHelpClick: function(event)
		{
			event.preventDefault();
			const hash = event.target.closest('[name="bizproc_automation_robot_dialog"]') ? '#after' : '';
			if (top.BX.Helper)
			{
				top.BX.Helper.show('redirect=detail&code=14889274' + hash);
			}
		},
		getUserOption: function(category, key, defaultValue)
		{
			return this.userOptions.get(category, key, defaultValue);
		},
		setUserOption: function(category, key, value)
		{
			this.userOptions.set(category, key, value);
			return this;
		},
		getConstants: function()
		{
			if (!this.data['GLOBAL_CONSTANTS'])
			{
				return [];
			}
			var constants = [];
			var visibilityNames = this.data['G_CONSTANTS_VISIBILITY'];
			Object.keys(this.data['GLOBAL_CONSTANTS']).forEach(function(id)
			{
				var constant = BX.clone(this.data['GLOBAL_CONSTANTS'][id]);
				constant.Id = id;
				constant.ObjectId = 'GlobalConst';
				constant.SystemExpression = '{=GlobalConst:' + id + '}';
				constant.Expression = '{=GlobalConst:' + id + '}';

				var constantVisibility = String(constant.Visibility).toUpperCase();
				var visibilityName = visibilityNames[constantVisibility];
				if (visibilityName)
				{
					constant.Expression = '{{' + visibilityName + ': ' + constant.Name + '}}';
					constant.supertitle = visibilityName;
				}

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
		getGVariables: function()
		{
			if (!this.data['GLOBAL_VARIABLES'])
			{
				return [];
			}

			var variables = [];
			var visibilityNames = this.data['G_VARIABLES_VISIBILITY'];
			BX.util.object_keys(this.data['GLOBAL_VARIABLES']).forEach(function(id)
			{
				var variable = BX.clone(this.data['GLOBAL_VARIABLES'][id]);
				variable.Id = id;
				variable.ObjectId = 'GlobalVar';
				variable.SystemExpression = '{=GlobalVar:' + id + '}';
				variable.Expression = '{=GlobalVar:' + id + '}';

				var variableVisibility = String(variable.Visibility).toUpperCase();
				var visibilityName = visibilityNames[variableVisibility];
				if (visibilityName)
				{
					variable.Expression = '{{' + visibilityName + ': ' + variable.Name + '}}';
					variable.supertitle = visibilityName;
				}

				variables.push(variable);
			}, this);

			return variables;
		},
		getGVariable: function(id)
		{
			var variables = this.getGVariables();
			for (var i = 0; i < variables.length; i++)
			{
				if (variables[i].Id === id)
				{
					return variables[i];
				}
			}

			return null;
		},
		getDocumentFields: function ()
		{
			return this.document.getFields();
		},
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

			this.viewMode = viewMode || Component.ViewMode.Edit;
			this.availableRobots = BX.type.isArray(data.AVAILABLE_ROBOTS) ? data.AVAILABLE_ROBOTS : [];
			this.templatesData = BX.type.isArray(data.TEMPLATES) ? data.TEMPLATES : [];

			this.initTemplates();
		},
		reInit: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			this.viewMode = viewMode || Component.ViewMode.Edit;
			if (BX.type.isArray(data.TEMPLATES))
			{
				this.templatesData = data.TEMPLATES;
			}
			if (this.viewMode !== Component.ViewMode.Edit)
			{
				this.targetManageModeStatus = '';
			}

			this.reInitTemplates(this.templatesData);
			if (this.isManageModeEnabled())
			{
				this.enableManageMode(this.targetManageModeStatus);
			}
			else
			{
				this.disableManageMode();
			}
		},
		isManageModeSupported: function ()
		{
			return this.component.data.IS_TEMPLATES_SCHEME_SUPPORTED;
		},
		isManageModeEnabled: function ()
		{
			return (BX.type.isString(this.targetManageModeStatus) && this.targetManageModeStatus !== '');
		},
		enableManageMode: function (status)
		{
			this.viewMode = Component.ViewMode.Manage;
			this.targetManageModeStatus = status;

			this.templates.forEach(function (template)
			{
				if (template.getStatusId() === status)
				{
					template.enableManageMode(true);
				}
				else
				{
					template.enableManageMode(false);
				}
			}.bind(this));

			this.component.disableDragAndDrop();
			this.component.actionPanel.showPanel();
		},
		disableManageMode: function ()
		{
			this.viewMode = Component.ViewMode.Edit;
			this.targetManageModeStatus = '';
			this.component.actionPanel.hidePanel();

			this.templates.forEach(function (template)
			{
				template.disableManageMode();
			});

			this.component.enableDragAndDrop();
		},
		enableDragAndDrop: function ()
		{
			this.templates.forEach(function (template)
			{
				template.enableDragAndDrop();
			});
		},
		disableDragAndDrop: function ()
		{
			this.templates.forEach(function (template)
			{
				template.disableDragAndDrop();
			});
		},
		initTemplates: function()
		{
			this.templates = [];
			this.templatesMap = {};

			for (var i = 0; i < this.templatesData.length; ++i)
			{
				var tpl = this.createTemplate(this.templatesData[i]);

				this.templates.push(tpl);
				this.templatesMap[tpl.getStatusId()] = tpl;
			}
		},
		createTemplate: function (templateData)
		{
			var template = new BX.Bizproc.Template({
				constants: {},
				globalConstants: this.component.getConstants(),
				variables: {},
				globalVariables: this.component.getGVariables(),
				templateContainerNode: this.component.node,
				selectors: {
					userSelector: UserSelector,
					fileSelector: FileSelector,
					inlineSelector: InlineSelector,
					inlineSelectorHtml: InlineSelectorHtml,
					timeSelector: TimeSelector,
					saveStateCheckbox: SaveStateCheckbox,
				},
				delayMinLimitM: this.component.data['DELAY_MIN_LIMIT_M'],
				userOptions: this.component.userOptions,
			});

			template.init(templateData, this.viewMode);

			BX.Event.EventEmitter.subscribe(
				this.component,
				'BX.Bizproc.Automation.Component:onSearch',
				template.onSearch.bind(template)
			);

			this.subscribeTemplateEvents(template);
			this.subscribeRobotEvents(template);

			return template;
		},
		subscribeTemplateEvents: function (template)
		{
			this.getTemplateEventListeners(template).forEach(function (eventListener) {
				template.subscribe(eventListener.eventName, eventListener.listener);
			});
		},
		subscribeRobotEvents: function (template)
		{
			this.getRobotEventListeners(template).forEach(function (eventListener) {
				template.subscribeRobotEvents(eventListener.eventName, eventListener.listener);
			});
		},
		getTemplateEventListeners: function (template)
		{
			return [
				{
					eventName: 'Template:help:show',
					listener: function (event) {
						this.component.onGlobalHelpClick(event.data);
					}.bind(this)
				},
				{
					eventName: 'Template:robot:showSettings',
					listener: function () {
						BX.Dom.addClass(this.component.node, 'automation-base-blocked');
					}.bind(this)
				},
				{
					eventName: 'Template:robot:closeSettings',
					listener: function () {
						BX.Dom.removeClass(this.component.node, 'automation-base-blocked');
					}.bind(this)
				},
				{
					eventName: 'Template:robot:add',
					listener: function (event) {
						var draftRobot = event.getData().robot;
						this.getRobotEventListeners(template).forEach(function (eventListener) {
							draftRobot.subscribe(eventListener.eventName, eventListener.listener);
						});
					}.bind(this)
				},
				{
					eventName: 'Template:modified',
					listener: function () {
						this.component.markModified();
					}.bind(this)
				},
				{
					eventName: 'Template:enableManageMode',
					listener: function (event) {
						if (this.viewMode === Component.ViewMode.Edit)
						{
							this.component.enableManageMode(event.getData().documentStatus);
						}
					}.bind(this)
				}
			];
		},
		getRobotEventListeners: function (template)
		{
			return [
				{
					eventName: 'Robot:selected',
					listener: function () {
						this.component.actionPanel.setTotalSelectedItems(template.getSelectedRobotNames().length);
					}.bind(this)
				},
				{
					eventName: 'Robot:unselected',
					listener: function () {
						this.component.actionPanel.setTotalSelectedItems(template.getSelectedRobotNames().length);
					}.bind(this)
				},
				{
					eventName: 'Robot:title:editStart',
					listener: function () {
						BX.addClass(this.component.node, 'automation-base-blocked');
					}.bind(this)
				},
				{
					eventName: 'Robot:title:editCompleted',
					listener: function () {
						BX.removeClass(this.component.node, 'automation-base-blocked');
					}.bind(this)
				},
				{
					eventName: 'Robot:manage',
					listener: function (event) {
						var dstTemplate = this.getTemplateByColumnNode(event.getData().templateNode);
						var droppableItem = event.getData().droppableItem;
						var robot = event.getData().robot;

						var beforeRobot = undefined;
						if (!BX.Type.isNil(droppableItem))
						{
							beforeRobot = dstTemplate.getRobotById(droppableItem.getAttribute('data-id'));
						}

						if (template)
						{
							if (event.getData().isCopy)
							{
								Template.copyRobotTo(dstTemplate, robot, beforeRobot);
							}
							else if (robot !== beforeRobot)
							{
								robot.moveTo(dstTemplate, beforeRobot);
							}
						}
					}.bind(this)
				}
			];
		},
		reInitTemplates: function(templates)
		{
			for (var i = 0; i < this.templates.length; ++i)
			{
				if (templates[i])
				{
					this.templates[i].reInit(templates[i], this.viewMode);
					this.subscribeRobotEvents(this.templates[i]);
				}
			}
		},
		updateTemplates: function (statuses)
		{
			return BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.component.getAjaxUrl(),
				data: {
					ajax_action: 'update_templates',
					document_signed: this.component.documentSigned,
					statuses: statuses,
				},
				onsuccess: function (response)
				{
					if (response.SUCCESS)
					{
						var updatedTemplates = response.DATA.templates;
						for (var updatedStatus in updatedTemplates)
						{
							if (updatedTemplates.hasOwnProperty(updatedStatus))
							{
								var template = this.getTemplateByStatusId(updatedStatus);
								var templateIndex = this.templatesData.findIndex(function (templateData) {
									return templateData.ID === template.getId();
								});

								template.reInit(updatedTemplates[updatedStatus], this.viewMode);
								this.templatesData[templateIndex] = updatedTemplates[updatedStatus];
							}
						}
					}
				}.bind(this),
			});
		},
		getAvailableRobots: function()
		{
			return this.availableRobots;
		},
		getRobotDescription: function(type)
		{
			return this.availableRobots.find(function(item) {
				return item['CLASS'] === type;
			});
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
		serializeModified: function()
		{
			var templates = [];

			this.templates.forEach(function (template) {
				if (template.isModified())
				{
					templates.push(template.serialize());
				}
			})

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
		getTemplateById: function(id)
		{
			return this.templates.find(function (template)
			{
				return template.getId() === id;
			});
		},
		getTemplateByStatusId: function(statusId)
		{
			return this.templatesMap[statusId] || null;
		},
		canEdit: function()
		{
			return this.component && this.component.canEdit();
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
		},
		updateGVariables: function ()
		{
			this.templates.forEach((template) =>
			{
				template.setGlobalVariables(this.component.getGVariables());
			});
		},
		updateGConstants: function ()
		{
			this.templates.forEach((template) =>
			{
				template.setGlobalConstants(this.component.getConstants());
			});
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
		this.setFileFields(robot.getDocument().getFields(), templateRobots);
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
			var idSalt = BX.Bizproc.Helper.generateUniqueId();
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
				this.menuId = BX.Bizproc.Helper.generateUniqueId();
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
		this.component = Designer.getInstance().component;
		this.documentFields = this.component ? this.component.document.getFields() : data['DOCUMENT_FIELDS'];
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
			var context = BX.Bizproc.tryGetGlobalContext();
			var menuItems = [], fileFields = [], menuGroups = {'ROOT': {
				title: context && context.document.title ? context.document.title : API.documentName,
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
				var constantList = [];
				if (tpl && !this.showTemplatePropertiesMenuOnSelecting)
				{
					tpl.getConstants().forEach(function(constant)
					{
						constantList.push({
							title: constant['Name'],
							customData: {property: constant},
							entityId: 'bp',
							tabs: 'recents',
							id: constant.SystemExpression,
							supertitle: BX.message('BIZPROC_AUTOMATION_CMP_TEMPLATE_CONSTANTS_LIST')
						});
					});
				}

				//GLOBAL CONST GROUP
				if (this.component && this.component.data['GLOBAL_CONSTANTS'])
				{
					this.component.getConstants().forEach(function(constant)
					{
						constantList.push({
							title: constant['Name'],
							customData: {property: constant},
							entityId: 'bp',
							tabs: 'recents',
							id: constant.SystemExpression,
							supertitle: constant.supertitle
						});
					}, this);
				}

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

				//GLOBAL VAR GROUP
				// todo: add Variables?
				if (this.component && this.component.data['GLOBAL_VARIABLES'])
				{
					var globalVariableList = [];
					this.component.getGVariables().forEach(function(variable)
					{
						globalVariableList.push({
							title: variable['Name'],
							customData: {property: variable},
							entityId: 'bp',
							tabs: 'recents',
							id: variable.SystemExpression,
							supertitle: variable.supertitle
						});
					}, this);

					if (globalVariableList.length > 0)
					{
						menuGroups['__GLOB_VARIABLES'] = {
							title: BX.message('BIZPROC_AUTOMATION_CMP_GLOB_VARIABLES_LIST_1'),
							entityId: 'bp',
							tabs: 'recents',
							id: '__GLOB_VARIABLES',
							children: globalVariableList
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
				menuId = BX.Bizproc.Helper.generateUniqueId();
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
				},
				compactView: true
			});

			this.dialog.show();
		},
		openPropertiesSwitcherMenu: function()
		{
			var me = this;
			BX.PopupMenu.show(
				BX.Bizproc.Helper.generateUniqueId(),
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
				if (!child.supertitle) {
					child.supertitle = title;
				}
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

			var delayIntervalSelector = new BX.Bizproc.DelayIntervalSelector({
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

			delayIntervalSelector.init(BX.Bizproc.DelayInterval.fromString(this.targetInput.value, basisFields));
		},
		initFileControl: function()
		{
			var documentFields = this.component.document.getFields();
			var basisFields = [];

			var i, field;
			for (i = 0; i < documentFields.length; ++i)
			{
				field = documentFields[i];
				if (field['Type'] === 'file')
				{
					basisFields.push(field);
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
			&& condition.parentGroup.type === ConditionGroup.CONDITION_TYPE.Mixed
		);
	};
	BX.extend(InlineSelectorCondition, InlineSelector);
	// <- InlineSelectorCondition
	// -> UserSelector
	var UserSelector = function(robot, targetInput, data)
	{
		this.robot = robot;
		this.component = Designer.getInstance().component;
		this.documentFields = this.component ? this.component.document.getFields() : data['DOCUMENT_FIELDS'];
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

		this.targetInput = this.menuButton = targetInput;
		this.userSelector = BX.Bizproc.UserSelector.decorateNode(
			targetInput,
			this.getSelectorConfig(robot)
		);
	};
	BX.extend(UserSelector, InlineSelector);
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

	UserSelector.prototype.getSelectorConfig = function(robot)
	{
		var templateRobots = robot.template ? robot.template.robots : [];
		var additionalFields = [];
		if (BX.type.isArray(templateRobots))
		{
			templateRobots.forEach(function(robot)
			{
				robot.getReturnFieldsDescription().forEach(function(field)
				{
					if (field['Type'] === 'user')
					{
						additionalFields.push({
							id: '{{~'+robot.getId()+':'+field['Id']+'}}',
							title: robot.getTitle() + ': ' + field['Name'],
						});
					}
				});
			});
		}

		if (this.showTemplatePropertiesMenuOnSelecting)
		{
			const ask = robot.template.addConstant(BX.clone(this.fieldProperty));

			additionalFields.push({
				id: ask.Expression,
				title: BX.message('BIZPROC_AUTOMATION_ASK_CONSTANT'),
				tabs: ['recents', 'bpuserroles'],
				sort: 1,
			});

			const param = robot.template.addParameter(BX.clone(this.fieldProperty));

			additionalFields.push({
				id: param.Expression,
				title: BX.message('BIZPROC_AUTOMATION_ASK_PARAMETER'),
				tabs: ['recents', 'bpuserroles'],
				sort: 2,
			});
		}

		return {additionalFields: additionalFields};
	}
	// <- UserSelector
	// -> InlineSelectorHtml
	var InlineSelectorHtml = function(robot, targetNode)
	{
		var me = this;
		this.robot = robot;
		this.component = Designer.getInstance().component;
		this.documentFields = this.component.document.getFields();
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
			if (editor.synchro.IsFocusedOnTextarea())
			{
				editor.textareaView.Focus();
				editor.textareaView.WrapWith('', '', insertText);
			}
			else
			{
				editor.InsertHtml(insertText);
			}
			editor.synchro.Sync();
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
			var savedState = robot.template.userOptions.get('save_state_checkboxes', key, 'N');
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
				this.robot.template.userOptions.set('save_state_checkboxes', key, value);
			}
		}
	};
	// <- SaveStateCheckbox

	var API = {
		documentName: null,
		documentType: null,
		documentFields: null,
		documentSigned: null,
		showRobotSettings: function(robotData, documentType, documentStatus, onSaveCallback)
		{
			var document = new BX.Bizproc.Document({
				rawDocumentType: documentType,
				statusId: documentStatus,
				documentFields: this.documentFields,
			});
			var robot = new Robot({
				document: document,
				isFrameMode: false,
			});
			robot.init(robotData, BX.Bizproc.ViewMode.none());
			BX.Bizproc.setGlobalContext(new BX.Bizproc.AutomationContext({
				document: document,
				signedDocument: this.documentSigned,
				ajaxUrl: getAjaxUrl(),
			}));

			var config = {
				document: document,
				documentSigned: this.documentSigned,

				ajaxUrl: getAjaxUrl(),
			};
			var tpl = new Template({
				config: config,
				selectors: {
					userSelector: UserSelector,
					fileSelector: FileSelector,
					inlineSelector: InlineSelector,
					inlineSelectorHtml: InlineSelectorHtml,
					timeSelector: TimeSelector,
					saveStateCheckbox: SaveStateCheckbox,
				}
			});
			tpl.init({DOCUMENT_FIELDS: this.documentFields}, Component.ViewMode.None);

			tpl.subscribe('Template:help:show', event => {
				event.preventDefault();
				if (top.BX.Helper)
				{
					top.BX.Helper.show('redirect=detail&code=14889274');
				}
			});

			tpl.openRobotSettingsDialog(robot, null, onSaveCallback);
		}
	};

	var showGlobals = {
		showVariables: function (documentType)
		{
			var me = this;
			BX.Bizproc.Globals.Manager.Instance.showGlobals(
				BX.Bizproc.Globals.Manager.Instance.mode.variable,
				documentType
			).then(function (slider) {
				me.onAfterSliderClose(slider, 'GLOBAL_VARIABLES');
			});
		},
		showConstants: function (documentType)
		{
			var me = this;
			BX.Bizproc.Globals.Manager.Instance.showGlobals(
				BX.Bizproc.Globals.Manager.Instance.mode.constant,
				documentType
			).then(function (slider) {
				me.onAfterSliderClose(slider, 'GLOBAL_CONSTANTS');
			});
		},
		onAfterSliderClose: function (slider, componentDataKey)
		{
			var sliderInfo = slider.getData();
			if (sliderInfo.get('upsert'))
			{
				var newGFields = sliderInfo.get('upsert');
				for (var fieldId in newGFields)
				{
					this.component.data[componentDataKey][fieldId] = newGFields[fieldId];
				}
			}
			if (sliderInfo.get('delete'))
			{
				var deletedGFields = sliderInfo.get('delete');
				for (var i in deletedGFields)
				{
					delete this.component.data[componentDataKey][deletedGFields[i]];
				}
			}

			if (componentDataKey === 'GLOBAL_VARIABLES')
			{
				this.component.templateManager.updateGVariables();
			}
			else if (componentDataKey === 'GLOBAL_CONSTANTS')
			{
				this.component.templateManager.updateGConstants();
			}
		}
	}

	const Debugger = {
		showStartPage: function()
		{
			BX.Bizproc.Debugger.Manager.Instance.openDebuggerStartPage(this.component.documentSigned).then();
		},

		showDebugSessions: function ()
		{
			var componentParams = {
				documentSigned: this.component.documentSigned,
			};

			this.openSlider('bizproc.debugger.session.list', componentParams, {width: 1150});
		},

		openSlider(componentName, params, options)
		{
			const defaultOptions = {
				width: 850,
				cacheable: false,
			};

			const sliderOptions = BX.Type.isPlainObject(options) ? Object.assign(defaultOptions, options) : defaultOptions;

			const url = BX.Uri.addParam(
				'/bitrix/components/bitrix/' + componentName,
				BX.Type.isPlainObject(params) ? params : {}
			);

			BX.SidePanel.Instance.open(url, sliderOptions);
		},
	};

	var ConditionGroup = BX.Bizproc.ConditionGroup;
	var Designer = BX.Bizproc.Designer;
	var ConditionGroupSelector = BX.Bizproc.ConditionGroupSelector;
	var Tracker = BX.Bizproc.Tracker;
	var Helper = BX.Bizproc.Helper;
	var HelpHint = BX.Bizproc.HelpHint;

	var Trigger = BX.Bizproc.Trigger;
	var TriggerManager = BX.Bizproc.TriggerManager;
	var Robot = BX.Bizproc.Robot;
	var Template = BX.Bizproc.Template;

	BX.Bizproc.Automation.Trigger = Trigger;
	BX.Bizproc.Automation.TriggerManager = TriggerManager;
	BX.Bizproc.Automation.Robot = Robot;
	BX.Bizproc.Automation.Template = Template;

	BX.Bizproc.Automation.Component = Component;
	BX.Bizproc.Automation.Designer = Designer.getInstance();
	BX.Bizproc.Automation.API = API;
	BX.Bizproc.Automation.ConditionGroup = ConditionGroup;
	BX.Bizproc.Automation.ConditionGroupSelector = ConditionGroupSelector;
	BX.Bizproc.Automation.showGlobals = showGlobals;
	BX.Bizproc.Automation.Debugger = Debugger;

	BX.namespace('BX.Bizproc.Automation.Selector');
	BX.Bizproc.Automation.Selector.InlineSelectorCondition = InlineSelectorCondition;


})(window.BX || window.top.BX);
