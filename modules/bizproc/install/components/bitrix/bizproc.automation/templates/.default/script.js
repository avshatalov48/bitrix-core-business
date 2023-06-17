;if (!BX.getClass('BX.Bizproc.Automation.Component')) (function(BX)
{
	'use strict';
	BX.namespace('BX.Bizproc.Automation');

	var Component = function(baseNode)
	{
		if (!BX.type.isDomNode(baseNode))
		{
			throw 'baseNode must be Dom Node Element';
		}

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
			this.viewMode = viewMode || Component.ViewMode.Edit;

			if (BX.Type.isUndefined(data))
			{
				data = {};
			}

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
			this.initSelectors();

			const stages = new BX.Bizproc.Automation.Statuses(this.node);
			stages.init(this.templateManager.templates);
			stages.fixColors();

			this.initRobotSelector();

			this.initHowCheckAutomationTourGuide();

			if (!this.embeddedMode)
			{
				window.onbeforeunload = () =>
				{
					if (this.isNeedSave())
					{
						return BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_NEED_SAVE');
					}
				};
			}
			if (this.frameMode)
			{
				this.subscribeOnSliderClose();
			}
		},
		isNeedSave: function()
		{
			return this.templateManager.needSave() || this.triggerManager.needSave();
		},
		initData: function()
		{
			this.document = new BX.Bizproc.Automation.Document({
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
			this.userOptions = new BX.Bizproc.Automation.UserOptions(rawUserOptions);
			this.frameMode = BX.type.isBoolean(this.data.FRAME_MODE) ? this.data.FRAME_MODE : false;
			this.embeddedMode = (this.data.IS_EMBEDDED === true);
		},
		initContext: function()
		{
			const context = new BX.Bizproc.Automation.Context({
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
				showTemplatePropertiesMenuOnSelecting: this.data['SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING'] === true,

				automationGlobals: new BX.Bizproc.Automation.AutomationGlobals({
					variables: this.data['GLOBAL_VARIABLES'],
					constants: this.data['GLOBAL_CONSTANTS'],
				}),
			});
			context.set('TRIGGER_CAN_SET_EXECUTE_BY', this.data['TRIGGER_CAN_SET_EXECUTE_BY']);
			context.set('IS_WORKTIME_AVAILABLE', this.data['IS_WORKTIME_AVAILABLE']);

			BX.Bizproc.Automation.setGlobalContext(context);
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
			this.triggerManager.init(this.data, BX.Bizproc.Automation.ViewMode.fromRaw(this.viewMode));

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
			this.triggerManager.subscribe('TriggerManager:trigger:delete', function (event)
			{
				const deletedTrigger = event.getData().trigger;

				//analytics
				BX.ajax.runAction(
					'bizproc.analytics.push',
					{
						analyticsLabel: {
							automation_trigger_delete: 'Y',
							delete_trigger: deletedTrigger.getCode().toLowerCase(),
						}
					}
				);
			});
		},
		reInitTriggerManager: function(triggers)
		{
			if (BX.type.isArray(triggers))
			{
				this.data.TRIGGERS = triggers;
			}
			this.triggerManager.reInit(this.data, BX.Bizproc.Automation.ViewMode.fromRaw(this.viewMode));
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
			if (BX.Bizproc.Automation.ViewMode.fromRaw(this.viewMode).isEdit())
			{
				this.initAddButtons();
			}

			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (buttonsNode)
			{
				this.bindSaveButton();
				this.bindCancelButton();
			}
			this.bindCreationButton();
		},
		initAddButtons: function () {
			const addButtonNodes = this.node.querySelectorAll('[data-role="add-button-container"]');
			const self = this;

			addButtonNodes.forEach(function (node)
			{
				const template = self.templateManager.getTemplateByStatusId(node.dataset.statusId);
				if (!template)
				{
					return;
				}

				const btnAddNode = BX.Dom.create('span', {
					events: {
						click: () => {
							if (self.canEdit())
							{
								self.robotSelector.setStageId(node.dataset.statusId);
								self.robotSelector.show();
							}
							else
							{
								BX.Bizproc.Automation.HelpHint.showNoPermissionsHint(btnAddNode);
							}
						}
					},
					attrs: {
						className: 'bizproc-automation-robot-btn-add',
					},
					children: [
						BX.Dom.create('span', {
							attrs: {
								className: 'bizproc-automation-btn-add-text',
							},
							text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD')
						})
					]
				});

				BX.Dom.append(btnAddNode, node);
			});
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
		initSelectors: function ()
		{
			BX.Event.EventEmitter.subscribe(
				'BX.Bizproc.Automation:Template:onSelectorMenuOpen',
				function (event)
				{
					const template = event.getData().template;
					const selector = event.getData().selector;
					const isMixedCondition = event.getData().isMixedCondition;

					if (BX.Type.isBoolean(isMixedCondition) && !isMixedCondition)
					{
						return;
					}

					const triggersReturnProperties = this.triggerManager.getReturnProperties(template.getStatusId());

					const triggerMenuItems = triggersReturnProperties.map((property) => ({
						id: property['SystemExpression'],
						title: property['Name'] || property['Id'],
						subtitle: property['ObjectName'] || property['ObjectId'],
						customData: { field: property },
					}));

					if (triggerMenuItems.length > 0)
					{
						selector.addGroup('__TRESULT', {
							id: '__TRESULT',
							title: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_LIST'),
							children: triggerMenuItems,
						});
					}
				}.bind(this)
			);
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
			const button = this.node.querySelector('[data-role="automation-btn-cancel"]');

			if (button)
			{
				BX.bind(button, 'click', this.onCancelButtonClick.bind(this));
			}
		},
		onCancelButtonClick: function (event)
		{
			if (event)
			{
				event.preventDefault();
			}

			this.reInitTriggerManager();
			this.reInitTemplateManager();
			this.reInitSearch();
			this.markModified(false);
		},
		bindCreationButton: function()
		{
			const me = this;
			const button = this.node.querySelector('[data-role="automation-btn-create"]');

			if (button)
			{
				if (me.canEdit())
				{
					BX.bind(button, 'click', () => {
						this.robotSelector.setStageId(this.templateManager.templates[0]?.getStatusId());
						this.robotSelector.show();
					});

					const settings = new BX.Bizproc.LocalSettings.Settings('aut-cmp');
					if (settings.get('beginning-guide-shown') !== true)
					{
						const documentRawType = this.document.getRawType();
						const module = documentRawType[0];
						const documentType = documentRawType[2];
						const isSellingDocumentType = (
							module === 'crm'
							&& ['LEAD', 'DEAL', 'INVOICE', 'SMART_INVOICE', 'QUOTE'].includes(documentType)
						);

						(new BX.Bizproc.Automation.BeginningGuide({
							target: button,
							text: (
								isSellingDocumentType
									? BX.Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_BEGINNING_SUBTITLE_SELLING_DOCUMENT_TYPE')
									: null
							),
							article: isSellingDocumentType ? '16547606' : null,
						}))
							.start()
						;
					}

					settings.set('beginning-guide-shown', true);
				}
			}
		},
		getAjaxUrl: function()
		{
			return getAjaxUrl(this.data.AJAX_URL);
		},
		getLimits: function()
		{
			const limit = this.data['ROBOTS_LIMIT'];
			if (limit <= 0)
			{
				return false;
			}

			const triggersCnt = this.triggerManager.countAllTriggers();
			const robotsCnt = this.templateManager.countAllRobots();

			return (triggersCnt + robotsCnt > limit) ? [limit, triggersCnt, robotsCnt] : false;
		},
		saveAutomation: function(callback)
		{
			if (this.savingAutomation)
			{
				return;
			}

			const limits = this.getLimits();

			if (limits)
			{
				if (top.BX.UI && top.BX.UI.InfoHelper)
				{
					top.BX.UI.InfoHelper.show('limit_crm_robots');

					return;
				}

				BX.UI.Dialogs.MessageBox.show({
					title: BX.Loc.getMessage('BIZPROC_AUTOMATION_ROBOTS_LIMIT_ALERT_TITLE'),
					message: BX.Loc.getMessage('BIZPROC_AUTOMATION_ROBOTS_LIMIT_SAVE_ALERT')
								.replace('#LIMIT#', limits[0])
								.replace('#SUM#', limits[1] + limits[2])
								.replace('#TRIGGERS#', limits[1])
								.replace('#ROBOTS#', limits[2]),
					modal: true,
					buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
					okCaption: BX.Loc.getMessage('BIZPROC_AUTOMATION_CLOSE_CAPTION')
				});

				return;
			}

			const me = this;
			const data = {
				ajax_action: 'save_automation',
				document_signed: this.documentSigned,
				triggers_json: Helper.toJsonString(this.triggerManager.serialize()),
				templates_json: Helper.toJsonString(this.templateManager.serializeModified())
			};

			const analyticsLabel = {
				'automation_save': 'Y',
				'robots_count': this.templateManager.countAllRobots(),
				'triggers_count': this.triggerManager.countAllTriggers(),
				'automation_module': this.document.getRawType()[0],
				'automation_entity': this.document.getRawType()[2] + '_' + this.document.getCategoryId()
			};

			this.savingAutomation = true;
			return BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: BX.Uri.addParam(this.getAjaxUrl(), {analyticsLabel}),
				data: data,
				onsuccess: function(response)
				{
					me.savingAutomation = null;
					response.DATA.templates.forEach(function (updatedTemplate) {
						const updatedTemplateIndex = me.data.TEMPLATES.findIndex(function (template) {
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
						BX.Event.EventEmitter.emit(
							'BX.Bizproc.Component.Automation.Component:onSuccessAutomationSave',
							new BX.Event.BaseEvent({data: {analyticsLabel}})
						);
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
		subscribeOnSliderClose: function()
		{
			const slider = BX.SidePanel.Instance.getSliderByWindow(window);
			if (slider)
			{
				const dialog = BX.UI.Dialogs.MessageBox.create({
					message: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ON_CLOSE_SLIDER_MESSAGE'),
					okCaption: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ON_CLOSE_SLIDER_OK_TITLE'),
					onOk: () => {
						if (slider.isCacheable())
						{
							this.onCancelButtonClick();
						}

						slider.getData().set('ignoreChanges', true);
						slider.close();

						return true;
					},
					buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
				});

				BX.addCustomEvent(slider, 'SidePanel.Slider:onClose', (event) => {
					if (this.isNeedSave() && slider.getData()?.get('ignoreChanges') !== true)
					{
						event.denyAction();
						dialog.show();

						return;
					}

					slider.getData()?.set('ignoreChanges', false);
				});
			}
		},
		initHowCheckAutomationTourGuide()
		{
			const documentRawType = this.document.getRawType();
			const module = documentRawType[0];
			const documentType = documentRawType[2];
			const categoryId = this.document.getCategoryId();
			const documentId = this.data['DOCUMENT_ID'];

			if (module === 'crm')
			{
				const rawUserOptions = BX.Type.isPlainObject(this.data.USER_OPTIONS) ? this.data.USER_OPTIONS : {};
				const hasCrmCheckAutomationOption =
					BX.Type.isPlainObject(rawUserOptions['crm_check_automation'])
						? Object.keys(rawUserOptions['crm_check_automation']).length > 0
						: false
				;
				if (this.canEdit() && !hasCrmCheckAutomationOption)
				{
					BX.Bizproc.Automation.CrmCheckAutomationGuide.startCheckAutomationTour(documentType, Number(categoryId));
				}

				if (hasCrmCheckAutomationOption && BX.Type.isStringFilled(documentId))
				{
					BX.Bizproc.Automation.CrmCheckAutomationGuide.showSuccessAutomation(
						documentType,
						categoryId,
						rawUserOptions['crm_check_automation']
					);
				}
			}
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
			if (!silent && this.isNeedSave())
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
			if (!this.isNeedSave())
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

			const addingRobots = this.robotSelector && this.robotSelector.isShown();
			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (!buttonsNode)
			{
				return;
			}

			if (modified && this.canEdit() && this.viewMode === Component.ViewMode.Edit)
			{
				if (!addingRobots)
				{
					BX.show(buttonsNode);
				}
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
			const context = BX.Bizproc.Automation.getGlobalContext();

			return context.automationGlobals.globalConstants;
		},
		getConstant: function(id)
		{
			return this.getConstants().find((constant) => constant.Id === id) || null;
		},
		getGVariables: function()
		{
			const context = BX.Bizproc.Automation.getGlobalContext();

			return context.automationGlobals.globalVariables;
		},
		getGVariable: function(id)
		{
			return this.getGVariables().find((variable) => variable.Id === id) || null;
		},
		getDocumentFields: function ()
		{
			return this.document.getFields();
		},
		initRobotSelector()
		{
			if (!this.robotSelector)
			{
				const self = this;
				const settings = new BX.Bizproc.LocalSettings.Settings('aut-cmp');

				const automationGuide = new BX.Bizproc.Automation.AutomationGuide({
					isShownRobotGuide: settings.get('robot-guide-shown') === true,
					isShownTriggerGuide: settings.get('trigger-guide-shown') === true,
				});

				this.robotSelector = new BX.Bizproc.Automation.RobotSelector({
					context: BX.Bizproc.Automation.getGlobalContext(),
					stageId: this.templateManager.templates[0]?.getStatusId(),
					events: {
						robotSelected: (event) => {
							if (!this.canEdit())
							{
								return;
							}

							const item = event.getData().item;
							const stageId = event.getData().stageId;
							const robotData = BX.Runtime.clone(item.customData.robotData);
							const originalEvent = event.getData().originalEvent;

							robotData.NAME = item.title;
							robotData.DIALOG_CONTEXT = {addMenuGroup: item.groupIds[0]};

							const template = this.templateManager.getTemplateByStatusId(stageId);
							if (!template)
							{
								return;
							}

							if (!template.isExternalModified())
							{
								template.addRobot(robotData, (robot) => {
									const setShowRobotGuide =
										BX.Type.isBoolean(robotData['ROBOT_SETTINGS']['IS_SUPPORTING_ROBOT'])
											? 'setShowSupportingRobotGuide'
											: 'setShowRobotGuide'
									;

									this.notifyAboutNewRobot(template, robot);

									if (!(originalEvent.ctrlKey || originalEvent.metaKey))
									{
										self.robotSelector.close();
										template.openRobotSettingsDialog(robot);

										template.subscribeOnce('Template:robot:closeSettings', () => {
											automationGuide[setShowRobotGuide](true, robot.node);
											automationGuide.start();
											settings.set('robot-guide-shown', automationGuide.isShownRobotGuide);
											settings.set('trigger-guide-shown', automationGuide.isShownTriggerGuide);
										});
									}
								});
							}
							else
							{
								this.showNotification({
									content: BX.message('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT_STAGE_TEXT')
								});
							}
						},
						triggerSelected: (event) => {
							if (!this.canEdit())
							{
								return;
							}

							const item = event.getData().item;
							const stageId = event.getData().stageId;
							const triggerData = BX.Runtime.clone(item.customData.triggerData);
							const originalEvent = event.getData().originalEvent;

							triggerData['DOCUMENT_STATUS'] = stageId;

							this.triggerManager.addTrigger(triggerData, (trigger) => {
								trigger.setName(item.title);
								this.triggerManager.insertTriggerNode(stageId, trigger.node);
								this.triggerManager.insertTrigger(trigger);

								this.notifyAboutNewTrigger(trigger);

								if (!(originalEvent.ctrlKey || originalEvent.metaKey))
								{
									self.robotSelector.close();
									this.triggerManager.openTriggerSettingsDialog(trigger);

									this.triggerManager.subscribeOnce(
										'TriggerManager:onCloseTriggerSettingsDialog',
										() => {
											automationGuide.setShowTriggerGuide(true, trigger.node);
											automationGuide.start();
											settings.set('robot-guide-shown', automationGuide.isShownRobotGuide);
											settings.set('trigger-guide-shown', automationGuide.isShownTriggerGuide);
										}
									);
								}
							});
						},
						onAfterShow: () => {
							BX.Dom.addClass(this.node, 'automation-base-blocked');

							if (!this.isOpenRobotSelectorAnalyticsPushed)
							{
								const document = this.document;
								//analytics
								BX.ajax.runAction(
									'bizproc.analytics.push',
									{
										analyticsLabel: {
											automation_enter_dialog: 'Y',
											start_module: document.getRawType()[0],
											start_entity: document.getRawType()[2] + '_' + document.getCategoryId()
										}
									}
								);
								this.isOpenRobotSelectorAnalyticsPushed = true;
							}
						},
						onAfterClose: () => {
							BX.Dom.removeClass(this.node, 'automation-base-blocked');
							if (this.isNeedSave())
							{
								this.markModified();
							}
						}
					}
				});
			}
		},
		notifyAboutNewRobot(template, robot)
		{
			const status = template.getStatus() ?? {};

			const context = BX.Bizproc.Automation.getGlobalContext();
			let messageId = 'BIZPROC_AUTOMATION_ROBOT_SELECTOR_NEW_ROBOT_ADDED';
			if (context.document.statusList.length > 1)
			{
				messageId += '_ON_STAGE';
			}

			this.showNotification({
				content: BX.Loc.getMessage(
					messageId,
					{
						'#ROBOT_NAME#': BX.Text.encode(robot.getTitle()),
						'#STAGE_NAME#': BX.Text.encode(status.NAME || status.TITLE)
					}
				),
				actions: [
					{
						title: BX.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
						events: {
							click: function (event, baloon)
							{
								event.preventDefault();
								template.deleteRobot(robot);
								robot.destroy();

								baloon.close();
							}
						},
					}
				],
			});
		},
		notifyAboutNewTrigger(trigger)
		{
			const self = this;
			const status = trigger.getStatus() ?? {};

			const context = BX.Bizproc.Automation.getGlobalContext();
			let messageId = 'BIZPROC_AUTOMATION_ROBOT_SELECTOR_NEW_TRIGGER_ADDED';
			if (context.document.statusList.length > 1)
			{
				messageId += '_ON_STAGE';
			}

			this.showNotification({
				content: BX.Loc.getMessage(
					messageId,
					{
						'#TRIGGER_NAME#': BX.Text.encode(trigger.getName()),
						'#STAGE_NAME#': BX.Text.encode(status.NAME || status.TITLE)
					}
				),
				actions: [
					{
						title: BX.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
						events: {
							click: function (event, baloon)
							{
								event.preventDefault();
								self.triggerManager.deleteTrigger(trigger);
								BX.Dom.remove(trigger.node);

								baloon.close();
							}
						}
					}
				]
			});
		},
		showNotification(notificationOptions)
		{
			const defaultSettings = {autoHideDelay: 3000};

			BX.UI.Notification.Center.notify(Object.assign(defaultSettings, notificationOptions));
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
			const template = new BX.Bizproc.Automation.Template({
				constants: {},
				variables: {},
				templateContainerNode: this.component.node,
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
					eventName: 'Template:robot:delete',
					listener: function (event) {
						const deletedRobot = event.getData().robot;

						//analytics
						BX.ajax.runAction(
							'bizproc.analytics.push',
							{
								analyticsLabel: {
									automation_robot_delete: 'Y',
									delete_robot: deletedRobot.data.Type.toLowerCase(),
								}
							}
						);

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
			var idSalt = BX.Bizproc.Automation.Helper.generateUniqueId();
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
				this.menuId = BX.Bizproc.Automation.Helper.generateUniqueId();
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

	const API = {
		documentName: null,
		documentType: null,
		documentFields: null,
		documentSigned: null,
		showRobotSettings: function (robotData, documentType, documentStatus, onSaveCallback) {
			const document = new BX.Bizproc.Automation.Document({
				rawDocumentType: documentType,
				statusId: documentStatus,
				documentFields: this.documentFields,
				title: this.documentName,
			});
			const robot = new Robot({
				document: document,
				isFrameMode: false,
			});
			const automationGlobals = new BX.Bizproc.Automation.AutomationGlobals({
				variables: [],
				constants: [],
			});

			robot.init(robotData, BX.Bizproc.Automation.ViewMode.none());
			BX.Bizproc.Automation.setGlobalContext(new BX.Bizproc.Automation.Context({
				document: document,
				signedDocument: this.documentSigned,
				ajaxUrl: getAjaxUrl(),
				automationGlobals: automationGlobals,
			}));

			const config = {
				document: document,
				documentSigned: this.documentSigned,

				ajaxUrl: getAjaxUrl(),
			};
			const tpl = new Template({
				config: config,
			});
			tpl.init({DOCUMENT_FIELDS: this.documentFields}, Component.ViewMode.None);

			tpl.subscribe('Template:help:show', event => {
				event.preventDefault();
				if (top.BX.Helper) {
					top.BX.Helper.show('redirect=detail&code=14889274');
				}
			});

			tpl.openRobotSettingsDialog(robot, null, onSaveCallback);
		}
	};

	const showGlobals = {
		showVariables: function ()
		{
			const documentTypeSigned = this.component.data['DOCUMENT_TYPE_SIGNED'];
			const mode = BX.Bizproc.Globals.Manager.Instance.mode.variable;

			BX.Bizproc.Globals.Manager.Instance.showGlobals(mode, documentTypeSigned)
				.then(this.onAfterSliderClose.bind(this, mode))
			;
		},
		showConstants: function ()
		{
			const documentTypeSigned = this.component.data['DOCUMENT_TYPE_SIGNED'];
			const mode = BX.Bizproc.Globals.Manager.Instance.mode.constant;

			BX.Bizproc.Globals.Manager.Instance.showGlobals(mode, documentTypeSigned)
				.then(this.onAfterSliderClose.bind(this, mode))
			;
		},
		onAfterSliderClose: function (mode, slider)
		{
			if (!this.isCorrectMode(mode) || !slider)
			{
				return;
			}

			const updatedGlobals = slider.getData().get('upsert');
			const deletedGlobals = slider.getData().get('delete');

			const context = BX.Bizproc.Automation && BX.Bizproc.Automation.tryGetGlobalContext();
			if (!context)
			{
				return;
			}

			const automationGlobals = context.automationGlobals;
			if (!automationGlobals)
			{
				return;
			}

			if (BX.Type.isPlainObject(updatedGlobals))
			{
				automationGlobals.updateGlobals(mode, updatedGlobals);
			}

			if (BX.Type.isArrayFilled(deletedGlobals))
			{
				automationGlobals.deleteGlobals(mode, deletedGlobals);
			}
		},
		isCorrectMode: function (mode)
		{
			return BX.Type.isStringFilled(mode) && Object.values(BX.Bizproc.Globals.Manager.Instance.mode).includes(mode);
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

	const Designer = BX.Bizproc.Automation.Designer;
	const Tracker = BX.Bizproc.Automation.Tracker;
	const Helper = BX.Bizproc.Automation.Helper;

	const TriggerManager = BX.Bizproc.Automation.TriggerManager;
	const Robot = BX.Bizproc.Automation.Robot;
	const Template = BX.Bizproc.Automation.Template;

	BX.Bizproc.Automation.Component = Component;
	BX.Bizproc.Automation.API = API;
	BX.Bizproc.Automation.showGlobals = showGlobals;
	BX.Bizproc.Automation.Debugger = Debugger;

	BX.namespace('BX.Bizproc.Automation.Selector');
	BX.Bizproc.Automation.Selector.InlineSelectorCondition = BX.Bizproc.Automation.InlineSelectorCondition;


})(window.BX || window.top.BX);
