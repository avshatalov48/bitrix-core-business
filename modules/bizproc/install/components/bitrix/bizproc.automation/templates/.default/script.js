;if (!BX.getClass('BX.Bizproc.Automation.Component')) (function(BX)
{
	'use strict';

	BX.namespace('BX.Bizproc.Automation');

	const Component = function(baseNode) {
		if (!BX.type.isDomNode(baseNode))
		{
			throw 'baseNode must be Dom Node Element';
		}

		this.node = baseNode;

		// set current instance
		Designer.getInstance().component = this;
		showGlobals.component = this;
		Debugger.component = this;
	};

	Component.ViewMode = {
		None: 0,
		View: 1,
		Edit: 2,
		Manage: 3,
	};

	const getAjaxUrl = function(url) {
		url = url || '/bitrix/components/bitrix/bizproc.automation/ajax.php';

		return BX.util.add_url_param(url, {
			site_id: BX.Loc.getMessage('SITE_ID'),
			sessid: BX.bitrix_sessid(),
		});
	};

	const getResponsibleUserExpression = function(fields) {
		let exp;

		if (BX.type.isArray(fields))
		{
			for (const field of fields)
			{
				if (field.Id === 'ASSIGNED_BY_ID' || field.Id === 'RESPONSIBLE_ID')
				{
					exp = '{{' + field.Name + '}}';
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
				window.onbeforeunload = () => {
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
				title: this.data.ENTITY_NAME,
			});
			this.documentSigned = this.data.DOCUMENT_SIGNED;

			this.bizprocEditorUrl = this.data.WORKFLOW_EDIT_URL;
			this.constantsEditorUrl = this.data.CONSTANTS_EDIT_URL || null;
			this.parametersEditorUrl = this.data.PARAMETERS_EDIT_URL || null;

			this.setDocumentStatus(this.data.DOCUMENT_STATUS);

			let rawUserOptions = {};
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
				availableRobots: BX.type.isArray(this.data.AVAILABLE_ROBOTS) ? this.data.AVAILABLE_ROBOTS : [],
				availableTriggers: BX.Type.isArray(this.data.AVAILABLE_TRIGGERS) ? this.data.AVAILABLE_TRIGGERS : [],
				canManage: this.data.IS_TEMPLATES_SCHEME_SUPPORTED,
				canEdit: this.canEdit(),
				userOptions: this.userOptions,
				tracker: this.tracker,

				bizprocEditorUrl: this.bizprocEditorUrl,
				constantsEditorUrl: this.constantsEditorUrl,
				parametersEditorUrl: this.parametersEditorUrl,
				isFrameMode: this.isFrameMode,

				marketplaceRobotCategory: this.data.MARKETPLACE_ROBOT_CATEGORY,
				showTemplatePropertiesMenuOnSelecting: this.data.SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING === true,

				automationGlobals: new BX.Bizproc.Automation.AutomationGlobals({
					variables: this.data.GLOBAL_VARIABLES,
					constants: this.data.GLOBAL_CONSTANTS,
				}),
			});
			context.set('TRIGGER_CAN_SET_EXECUTE_BY', this.data.TRIGGER_CAN_SET_EXECUTE_BY);
			context.set('IS_WORKTIME_AVAILABLE', this.data.IS_WORKTIME_AVAILABLE);

			BX.Bizproc.Automation.setGlobalContext(context);
		},
		setDocumentStatus: function(status)
		{
			this.document.setStatus(status);

			return this;
		},
		isPreviousStatus: function(needle)
		{
			const previousStatuses = this.document.getPreviousStatusIdList();
			for (const previousStatus of previousStatuses)
			{
				if (needle === previousStatus)
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
			const nextStatuses = this.document.getNextStatusIdList();
			for (const nextStatus of nextStatuses)
			{
				if (needle === nextStatus)
				{
					return true;
				}
			}

			return false;
		},
		initActionPanel: function()
		{
			const panelNode = document.querySelector('[data-role="automation-actionpanel"]');
			if (!panelNode)
			{
				return;
			}

			this.actionPanel = new BX.UI.ActionPanel({
				renderTo: panelNode,
				removeLeftPosition: true,
				maxHeight: 58,
				parentPosition: 'bottom',
				autoHide: false,
			});

			this.actionPanel.draw();

			const pathToIconSetMain = '/bitrix/js/ui/icon-set/main/images/';
			const pathToIconSetCRM = '/bitrix/js/ui/icon-set/crm/images/';
			const pathToIconSetActions = '/bitrix/js/ui/icon-set/actions/images/';

			this.actionPanel.appendItem({
				id: 'automation_choose_all',
				text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_CHOOSE_ALL'),
				icon: `${pathToIconSetMain}complete.svg`,
				onclick: function()
				{
					const status = this.templateManager.targetManageModeStatus;
					const triggers = this.triggerManager.findTriggersByDocumentStatus(status);
					triggers.forEach((trigger) => trigger.selectNode());

					const template = this.templateManager.getTemplateByStatusId(status);
					if (template)
					{
						template.robots.forEach((robot) => {
							robot.selectNode();
						});
					}
				}.bind(this),
			});

			this.actionPanel.appendItem({
				id: 'automation_copy_to',
				text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_COPY_1'),
				icon: `${pathToIconSetActions}copy-plates.svg`,
				onclick: this.onCopyMoveButtonClick.bind(this, 'copy'),
			});

			this.actionPanel.appendItem({
				id: 'automation_move_to',
				text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_MOVE_1'),
				icon: `${pathToIconSetCRM}stages.svg`,
				onclick: this.onCopyMoveButtonClick.bind(this, 'move'),
			});

			this.actionPanel.appendItem({
				id: 'automation_deactivate',
				text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_DEACTIVATE'),
				icon: `${pathToIconSetMain}switch.svg`,
				onclick: this.onDeactivateActionPanelButtonClick.bind(this),
			});

			this.actionPanel.appendItem({
				id: 'automation_activate',
				text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_ACTIVATE'),
				icon: `${pathToIconSetMain}switch.svg`,
				hide: true,
				onclick: this.onDeactivateActionPanelButtonClick.bind(this),
			});

			this.actionPanel.appendItem({
				id: 'automation_delete',
				text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ACTIONPANEL_DELETE_1'),
				icon: `${pathToIconSetMain}trash-bin.svg`,
				onclick: this.onDeleteButtonClick.bind(this),
			});

			BX.addCustomEvent('BX.UI.ActionPanel:hidePanel', () => {
				if (this.templateManager.isManageModeEnabled())
				{
					this.disableManageMode();
				}
			});
		},
		onCopyMoveButtonClick: function(action)
		{
			this.viewMode = Component.ViewMode.Edit;
			const selectedStatus = this.templateManager.targetManageModeStatus;
			const template = this.templateManager.getTemplateByStatusId(selectedStatus);
			const selectedRobots = template.getSelectedRobotNames();
			const selectedTriggers = this.triggerManager.getSelectedTriggers().map(trigger => trigger.getId());

			if (selectedRobots.length + selectedTriggers.length === 0)
			{
				this.showNotification({
					content: BX.Loc.getMessage('BIZPOC_AUTOMATION_NO_ROBOT_OR_TRIGGER_SELECTED'),
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
					selectedTriggers: selectedTriggers,
				},
				events: {
					onCloseComplete: function(event)
					{
						const slider = event.slider;
						if (slider)
						{
							const data = slider.getData();
							const targetScope = data.get('targetScope');

							if (BX.type.isNil(targetScope))
							{
								return;
							}

							const confirmObject = (item) => (BX.type.isPlainObject(item) ? item : {});
							const confirmArray = (item) => (BX.type.isArray(item) ? item : []);

							const allRobots = confirmObject(data.get('robots'));
							const allTriggers = confirmObject(data.get('triggers'));

							const acceptedRobots = confirmArray(action === 'copy' ? allRobots.copied : allRobots.moved);
							const deniedRobots = confirmArray(allRobots.denied);
							const manageRobotsCount = acceptedRobots.length + deniedRobots.length;

							const acceptedTriggers = confirmArray(action === 'copy' ? allTriggers.copied : allTriggers.moved);
							const deniedTriggers = confirmArray(allTriggers.denied);
							const manageTriggersCount = acceptedTriggers.length + deniedTriggers.length;

							let messageId = 'BIZPROC_AUTOMATION_CMP_';
							const messageShards = [];
							if (manageRobotsCount > 0 && manageTriggersCount === 0)
							{
								messageShards.push('ROBOTS');
							}
							else if (manageTriggersCount > 0 && manageRobotsCount === 0)
							{
								messageShards.push('TRIGGERS');
							}
							else
							{
								messageShards.push('ROBOTS', 'AND', 'TRIGGERS');
							}

							if (action === 'copy')
							{
								messageShards.push('COPIED');
							}
							else
							{
								messageShards.push('MOVED');
							}
							messageId += messageShards.join('_');
							let notifyMessage = BX.Loc.getMessage(messageId);
							notifyMessage = notifyMessage.replace(
								'#ACCEPTED_COUNT#',
								acceptedRobots.length + acceptedTriggers.length,
							);
							notifyMessage = notifyMessage.replace(
								'#TOTAL_COUNT#',
								acceptedRobots.length
									+ deniedRobots.length
									+ acceptedTriggers.length
									+ deniedTriggers.length,
							);

							BX.UI.Notification.Center.notify({
								content: notifyMessage,
								actions: [
									{
										title: BX.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
										events: {
											click: function(event, balloon) {
												event.preventDefault();
												if (BX.type.isArray(allRobots.restoreData))
												{
													this.saveTemplates(allRobots.restoreData, allTriggers.restoreData);
													balloon.close();
												}
											}.bind(this),
										},
									},
								],
							});
							const updatedStatuses = [];
							const targetStatus = this.templateManager.targetManageModeStatus;
							if (action === 'move')
							{
								updatedStatuses.push(targetStatus);
							}

							if (
								targetScope
								&& this.data.DOCUMENT_TYPE_SIGNED === targetScope.documentType.Type
								&& this.templateManager.getTemplateByStatusId(targetScope.status.Id)
							)
							{
								updatedStatuses.push(targetScope.status.Id);
							}

							this.triggerManager.fetchTriggers(updatedStatuses).then(() => {
								deniedTriggers.forEach((triggerId) => {
									const trigger = this.triggerManager.findTriggerById(parseInt(triggerId, 10));

									if (trigger)
									{
										BX.Dom.addClass(trigger.node, '--denied');
										setTimeout(
											BX.Dom.removeClass.bind(null, trigger.node, '--denied'),
											10 * 1000,
										);
									}
								});
							});
							this.templateManager.updateTemplates(updatedStatuses).onload = function()
							{
								const srcTemplate = this.templateManager.getTemplateByStatusId(targetStatus);

								deniedRobots.forEach((robotName) => {
									const robot = srcTemplate.getRobotById(robotName);
									if (robot && !robot.isInvalid())
									{
										BX.Dom.addClass(robot.node, '--denied');
										setTimeout(
											BX.Dom.removeClass.bind(null, robot.node, '--denied'),
											10 * 1000,
										);
									}
								});
							}.bind(this);
						}
						this.disableManageMode();
					}.bind(this),
				},
			});
		},
		onDeleteButtonClick: function()
		{
			this.viewMode = Component.ViewMode.Edit;
			const status = this.templateManager.targetManageModeStatus;
			const template = this.templateManager.getTemplateByStatusId(status);
			if (!template)
			{
				return;
			}
			const templateIndex = this.templateManager.templatesData.findIndex((templateData) => {
				return templateData.ID === template.getId();
			});

			const deletingRobots = template.getSelectedRobotNames();
			const deletingTriggers = this.triggerManager.getSelectedTriggers().map((trigger) => trigger.getId());
			if (deletingRobots.length + deletingTriggers.length === 0)
			{
				this.showNotification({
					content: BX.Loc.getMessage('BIZPOC_AUTOMATION_NO_ROBOT_OR_TRIGGER_SELECTED'),
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
					trigger_names: Helper.toJsonString(deletingTriggers),
				},
				onsuccess: function(response)
				{
					const messageShards = [];
					if (deletingRobots.length > 0 && deletingTriggers.length === 0)
					{
						messageShards.push('ROBOTS');
					}
					else if (deletingTriggers.length > 0 && deletingRobots.length === 0)
					{
						messageShards.push('TRIGGERS');
					}
					else
					{
						messageShards.push('ROBOTS', 'AND', 'TRIGGERS');
					}
					let notifyMessage = BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_' + messageShards.join('_') + '_DELETED');
					notifyMessage = notifyMessage.replace('#TOTAL_COUNT#', deletingRobots.length + deletingTriggers.length);
					if (response.SUCCESS)
					{
						this.triggerManager.fetchTriggers().then();
						template.reInit(response.DATA.template, this.viewMode);
						this.templateManager.templatesData[templateIndex] = response.DATA.template;
						this.disableManageMode();
						notifyMessage = notifyMessage.replace('#ACCEPTED_COUNT#', deletingRobots.length + deletingTriggers.length);

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
								title: BX.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
								events: {
									click: function(event, balloon) {
										event.preventDefault();
										if (BX.type.isPlainObject(restoreData))
										{
											this.saveTemplates([restoreData.template], restoreData.triggers);
											balloon.close();
										}
									}.bind(this),
								},
							},
						],
					});
				}.bind(this),
			});
		},
		onDeactivateActionPanelButtonClick: function()
		{
			const selectedStatus = this.templateManager.targetManageModeStatus;
			const template = this.templateManager.getTemplateByStatusId(selectedStatus);
			const selectedRobots = template.getSelectedRobotNames();
			if (selectedRobots.length === 0)
			{
				this.showNotification({
					content: BX.Loc.getMessage('BIZPOC_AUTOMATION_NO_ROBOT_SELECTED'),
					autoHideDelay: 4000,
				});

				return;
			}

			const selectedTriggers = this.triggerManager.getSelectedTriggers().map((trigger) => trigger.getId());
			if (selectedTriggers.length > 0)
			{
				this.showNotification({
					autoHideDelay: 4000,
					content: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DEACTIVATE_TRIGGER_ALERT_MESSAGE'),
				});
			}

			const deactivatedRobots = template.getDeactivatedRobotNames();
			const activate = selectedRobots.every((selectedRobotName) => deactivatedRobots.includes(selectedRobotName));

			this.disableManageMode();
			template.markModified(true);

			selectedRobots.forEach((id) => {
				const robot = template.getRobotById(id);
				if (robot)
				{
					robot.setActivated(activate).reInit();
				}
			});
		},
		initTriggerManager: function()
		{
			this.triggerManager = new TriggerManager(this.node, { userOptions: this.userOptions });
			this.subscribeTriggerManagerEvents();
			this.triggerManager.init(this.data, BX.Bizproc.Automation.ViewMode.fromRaw(this.viewMode));

			this.triggerManager.onTriggerEvent('Trigger:selected', () => {
				const template = this.templateManager.getTemplateByStatusId(this.templateManager.targetManageModeStatus);
				const totalSelectedCount = (
					template.getSelectedRobotNames().length
					+ this.triggerManager.getSelectedTriggers().length
				);
				this.actionPanel.setTotalSelectedItems(totalSelectedCount);
			});
			this.triggerManager.onTriggerEvent('Trigger:unselected', () => {
				const template = this.templateManager.getTemplateByStatusId(this.templateManager.targetManageModeStatus);
				const selectedRobots = BX.type.isNil(template) ? [] : template.getSelectedRobotNames();
				const totalSelectedCount = (
					selectedRobots.length
					+ this.triggerManager.getSelectedTriggers().length
				);
				this.actionPanel.setTotalSelectedItems(totalSelectedCount);
			});

			BX.Event.EventEmitter.subscribe(
				this,
				'BX.Bizproc.Automation.Component:onSearch',
				this.triggerManager.onSearch.bind(this.triggerManager),
			);
		},
		subscribeTriggerManagerEvents: function()
		{
			this.triggerManager.subscribe('TriggerManager:dataModified', () => {
				this.markModified();
			});
			this.triggerManager.subscribe('TriggerManager:trigger:delete', (event) => {
				const deletedTrigger = event.getData().trigger;

				// analytics
				BX.ajax.runAction(
					'bizproc.analytics.push',
					{
						analyticsLabel: {
							automation_trigger_delete: 'Y',
							delete_trigger: deletedTrigger.getCode().toLowerCase(),
						},
					},
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
			{
				this.data.TEMPLATES = templates;
			}
			this.templateManager.reInit(this.data, this.viewMode);
		},
		initButtons: function()
		{
			if (BX.Bizproc.Automation.ViewMode.fromRaw(this.viewMode).isEdit())
			{
				this.initAddButtons();
			}

			const buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (buttonsNode)
			{
				this.bindSaveButton();
				this.bindCancelButton();
			}
			this.bindCreationButton();
		},
		initAddButtons: function() {
			const addButtonNodes = this.node.querySelectorAll('[data-role="add-button-container"]');

			addButtonNodes.forEach((node) => {
				const template = this.templateManager.getTemplateByStatusId(node.dataset.statusId);
				if (!template)
				{
					return;
				}

				const btnAddNode = BX.Dom.create('span', {
					events: {
						click: () => {
							if (this.canEdit())
							{
								this.robotSelector.setStageId(node.dataset.statusId);
								this.robotSelector.show();
							}
							else
							{
								BX.Bizproc.Automation.HelpHint.showNoPermissionsHint(btnAddNode);
							}
						},
					},
					attrs: {
						className: 'bizproc-automation-robot-btn-add',
					},
					children: [
						BX.Dom.create('span', {
							attrs: {
								className: 'bizproc-automation-btn-add-text',
							},
							text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD'),
						}),
					],
				});

				BX.Dom.append(btnAddNode, node);
			});
		},
		initButtonsPosition: function()
		{
			const buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (buttonsNode && this.frameMode)
			{
				BX.addClass(buttonsNode, 'bizproc-automation-buttons-fixed-slider');
			}
		},
		initSearch: function()
		{
			const searchNode = this.node.querySelector('[data-role="automation-search"]');
			if (searchNode)
			{
				BX.bind(searchNode, 'input', BX.debounce(this.onSearch.bind(this, searchNode), 255));
				BX.Event.EventEmitter.setMaxListeners(this, 'BX.Bizproc.Automation.Component:onSearch', 500);

				const clearNode = this.node.querySelector('[data-role="automation-search-clear"]');
				if (clearNode)
				{
					BX.bind(clearNode, 'click', this.onClearSearch.bind(this, searchNode));
				}
			}
		},
		reInitSearch: function()
		{
			const searchNode = this.node.querySelector('[data-role="automation-search"]');
			if (searchNode)
			{
				this.onSearch(searchNode);
			}
		},
		onSearch: function(searchNode)
		{
			BX.Event.EventEmitter.emit(
				this,
				'BX.Bizproc.Automation.Component:onSearch',
				{
					queryString: searchNode.value.toLowerCase(),
				},
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
		initSelectors: function()
		{
			BX.Event.EventEmitter.subscribe(
				'BX.Bizproc.Automation:Template:onSelectorMenuOpen',
				(event) => {
					const template = event.getData().template;
					const selector = event.getData().selector;
					const isMixedCondition = event.getData().isMixedCondition;

					if (BX.Type.isBoolean(isMixedCondition) && !isMixedCondition)
					{
						return;
					}

					const selectorManager = new SelectorItemsManager({
						triggerResultFields: this.triggerManager.getReturnProperties(template.getStatusId()),
					});

					selectorManager.groupsWithChildren.forEach((group) => {
						selector.addGroup(group.id, group);
					});
				},
			);
		},
		reInitButtons: function()
		{
			const changeViewBtn = this.node.querySelector('[data-role="automation-btn-change-view"]');
			if (changeViewBtn)
			{
				changeViewBtn.innerHTML = changeViewBtn.getAttribute(
					'data-label-' + (this.viewMode === Component.ViewMode.View ? 'edit' : 'view'),
				);
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
			const button = BX('ui-button-panel-save');

			if (button)
			{
				BX.Event.bind(button, 'click', (event) => {
					event.preventDefault();
					this.saveAutomation();
					BX.Dom.removeClass(button, 'ui-btn-wait');
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
		onCancelButtonClick: function(event)
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
			const button = this.node.querySelector('[data-role="automation-btn-create"]');

			if (button && this.canEdit())
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
		},
		getAjaxUrl: function()
		{
			return getAjaxUrl(this.data.AJAX_URL);
		},
		getLimits: function()
		{
			const limit = this.data.ROBOTS_LIMIT;
			if (limit <= 0)
			{
				return false;
			}

			const triggersCnt = this.triggerManager.countAllTriggers();
			const robotsCnt = this.templateManager.countAllActivatedRobots();

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
					message: BX.Loc.getMessage(
						'BIZPROC_AUTOMATION_ROBOTS_LIMIT_SAVE_ALERT',
						{
							'#LIMIT#': limits[0],
							'#SUM#': limits[1] + limits[2],
							'#TRIGGERS#': limits[1],
							'#ROBOTS#': limits[2],
						},
					),
					modal: true,
					buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
					okCaption: BX.Loc.getMessage('BIZPROC_AUTOMATION_CLOSE_CAPTION'),
				});

				return;
			}

			const me = this;
			const data = {
				ajax_action: 'save_automation',
				document_signed: this.documentSigned,
				triggers_json: Helper.toJsonString(this.triggerManager.serialize()),
				templates_json: Helper.toJsonString(this.templateManager.serializeModified()),
			};

			const analyticsLabel = {
				automation_save: 'Y',
				robots_count: this.templateManager.countAllActivatedRobots(),
				triggers_count: this.triggerManager.countAllTriggers(),
				automation_module: this.document.getRawType()[0],
				automation_entity: this.document.getRawType()[2] + '_' + this.document.getCategoryId(),
			};

			this.savingAutomation = true;

			return BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: BX.Uri.addParam(this.getAjaxUrl(), { analyticsLabel }),
				data: data,
				onsuccess: function(response)
				{
					me.savingAutomation = null;
					response.DATA.templates.forEach((updatedTemplate) => {
						const updatedTemplateIndex = me.data.TEMPLATES.findIndex((template) => {
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
							new BX.Event.BaseEvent({ data: { analyticsLabel } }),
						);
						if (callback)
						{
							callback(response.DATA);
						}
					}
					else
					{
						alert(response.ERRORS[0]);
					}
				},
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
			const documentId = this.data.DOCUMENT_ID;

			if (module === 'crm')
			{
				const rawUserOptions = BX.Type.isPlainObject(this.data.USER_OPTIONS) ? this.data.USER_OPTIONS : {};
				const hasCrmCheckAutomationOption = (
					BX.Type.isPlainObject(rawUserOptions.crm_check_automation)
						? Object.keys(rawUserOptions.crm_check_automation).length > 0
						: false
				);
				if (this.canEdit() && !hasCrmCheckAutomationOption)
				{
					BX.Bizproc.Automation.CrmCheckAutomationGuide.startCheckAutomationTour(documentType, Number(categoryId));
				}

				if (hasCrmCheckAutomationOption && BX.Type.isStringFilled(documentId))
				{
					BX.Bizproc.Automation.CrmCheckAutomationGuide.showSuccessAutomation(
						documentType,
						categoryId,
						rawUserOptions.crm_check_automation,
					);
				}
			}
		},
		saveTemplates: function(templatesData, triggersData)
		{
			if (this.savingAutomation)
			{
				return;
			}

			const data = {
				ajax_action: 'save_automation',
				document_signed: this.documentSigned,
				templates_json: Helper.toJsonString(templatesData),
				triggers_json: Helper.toJsonString(triggersData),
			};

			this.savingAutomation = true;
			const self = this;

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
						self.triggerManager.fetchTriggers().then();
						templatesData.forEach((updatedTemplate) => {
							const template = self.templateManager.getTemplateById(updatedTemplate.ID);
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
				},
			});
		},
		changeViewMode: function(mode, silent)
		{
			if (!silent && this.isNeedSave())
			{
				alert(BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_NEED_SAVE'));

				return;
			}

			if (mode !== Component.ViewMode.View && mode !== Component.ViewMode.Edit)
			{
				throw 'Unknown view mode';
			}

			this.viewMode = mode;

			this.reInitTriggerManager();
			this.reInitTemplateManager();
			this.reInitButtons();
		},
		enableManageMode: function(status)
		{
			if (!this.isNeedSave())
			{
				this.viewMode = Component.ViewMode.Manage;
				this.templateManager.enableManageMode(status);
				this.triggerManager.enableManageMode(status);
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
			const buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

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
			return this.data.CAN_EDIT;
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
		getDocumentFields: function()
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
							robotData.DIALOG_CONTEXT = { addMenuGroup: item.groupIds[0] };

							const template = this.templateManager.getTemplateByStatusId(stageId);
							if (!template)
							{
								return;
							}

							if (template.isExternalModified())
							{
								this.showNotification({
									content: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_EXTERNAL_EDIT_STAGE_TEXT'),
								});
							}
							else
							{
								template.addRobot(robotData, (robot) => {
									const setShowRobotGuide = (
										BX.Type.isBoolean(robotData.ROBOT_SETTINGS.IS_SUPPORTING_ROBOT)
											? 'setShowSupportingRobotGuide'
											: 'setShowRobotGuide'
									);

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

							triggerData.DOCUMENT_STATUS = stageId;

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
										},
									);
								}
							});
						},
						onAfterShow: () => {
							BX.Dom.addClass(this.node, 'automation-base-blocked');

							if (!this.isOpenRobotSelectorAnalyticsPushed)
							{
								const document = this.document;
								// analytics
								BX.ajax.runAction(
									'bizproc.analytics.push',
									{
										analyticsLabel: {
											automation_enter_dialog: 'Y',
											start_module: document.getRawType()[0],
											start_entity: document.getRawType()[2] + '_' + document.getCategoryId(),
										},
									},
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
						},
					},
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
						'#STAGE_NAME#': BX.Text.encode(status.NAME || status.TITLE),
					},
				),
				actions: [
					{
						title: BX.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
						events: {
							click: function(event, baloon)
							{
								event.preventDefault();
								template.deleteRobot(robot);
								robot.destroy();

								baloon.close();
							},
						},
					},
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
						'#STAGE_NAME#': BX.Text.encode(status.NAME || status.TITLE),
					},
				),
				actions: [
					{
						title: BX.Loc.getMessage('JS_CORE_WINDOW_CANCEL'),
						events: {
							click: function(event, baloon)
							{
								event.preventDefault();
								self.triggerManager.deleteTrigger(trigger);
								BX.Dom.remove(trigger.node);

								baloon.close();
							},
						},
					},
				],
			});
		},
		showNotification(notificationOptions)
		{
			const defaultSettings = { autoHideDelay: 3000 };

			BX.UI.Notification.Center.notify(Object.assign(defaultSettings, notificationOptions));
		},
	};

	const TemplateManager = function(component)
	{
		this.component = component;
	};

	TemplateManager.prototype =
	{
		init: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
			{
				data = {};
			}

			this.viewMode = viewMode || Component.ViewMode.Edit;
			this.availableRobots = BX.type.isArray(data.AVAILABLE_ROBOTS) ? data.AVAILABLE_ROBOTS : [];
			this.templatesData = BX.type.isArray(data.TEMPLATES) ? data.TEMPLATES : [];

			this.initTemplates();
		},
		reInit: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
			{
				data = {};
			}

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
		isManageModeSupported: function()
		{
			return this.component.data.IS_TEMPLATES_SCHEME_SUPPORTED;
		},
		isManageModeEnabled: function()
		{
			return (BX.type.isString(this.targetManageModeStatus) && this.targetManageModeStatus !== '');
		},
		enableManageMode: function(status)
		{
			this.viewMode = Component.ViewMode.Manage;
			this.targetManageModeStatus = status;

			this.templates.forEach((template) => {
				if (template.getStatusId() === status)
				{
					template.enableManageMode(true);
				}
				else
				{
					template.enableManageMode(false);
				}
			});

			this.component.disableDragAndDrop();
			this.component.actionPanel.showPanel();
		},
		disableManageMode: function()
		{
			this.viewMode = Component.ViewMode.Edit;
			this.targetManageModeStatus = '';
			this.component.actionPanel.hidePanel();

			this.templates.forEach((template) => {
				template.disableManageMode();
			});

			this.component.enableDragAndDrop();
		},
		enableDragAndDrop: function()
		{
			this.templates.forEach((template) => {
				template.enableDragAndDrop();
			});
		},
		disableDragAndDrop: function()
		{
			this.templates.forEach((template) => {
				template.disableDragAndDrop();
			});
		},
		initTemplates: function()
		{
			this.templates = [];
			this.templatesMap = {};

			for (let i = 0; i < this.templatesData.length; ++i)
			{
				const tpl = this.createTemplate(this.templatesData[i]);

				this.templates.push(tpl);
				this.templatesMap[tpl.getStatusId()] = tpl;
			}
		},
		createTemplate: function(templateData)
		{
			const template = new BX.Bizproc.Automation.Template({
				constants: {},
				variables: {},
				templateContainerNode: this.component.node,
				delayMinLimitM: this.component.data.DELAY_MIN_LIMIT_M,
				userOptions: this.component.userOptions,
			});

			template.init(templateData, this.viewMode);

			BX.Event.EventEmitter.subscribe(
				this.component,
				'BX.Bizproc.Automation.Component:onSearch',
				template.onSearch.bind(template),
			);

			this.subscribeTemplateEvents(template);
			this.subscribeRobotEvents(template);

			return template;
		},
		subscribeTemplateEvents: function(template)
		{
			this.getTemplateEventListeners(template).forEach((eventListener) => {
				template.subscribe(eventListener.eventName, eventListener.listener);
			});
		},
		subscribeRobotEvents: function(template)
		{
			this.getRobotEventListeners(template).forEach((eventListener) => {
				template.subscribeRobotEvents(eventListener.eventName, eventListener.listener);
			});
		},
		getTemplateEventListeners: function(template)
		{
			return [
				{
					eventName: 'Template:help:show',
					listener: function(event) {
						this.component.onGlobalHelpClick(event.data);
					}.bind(this),
				},
				{
					eventName: 'Template:robot:showSettings',
					listener: function() {
						BX.Dom.addClass(this.component.node, 'automation-base-blocked');
					}.bind(this),
				},
				{
					eventName: 'Template:robot:closeSettings',
					listener: function() {
						BX.Dom.removeClass(this.component.node, 'automation-base-blocked');
					}.bind(this),
				},
				{
					eventName: 'Template:robot:add',
					listener: function(event) {
						const draftRobot = event.getData().robot;
						this.getRobotEventListeners(template).forEach((eventListener) => {
							draftRobot.subscribe(eventListener.eventName, eventListener.listener);
						});
					}.bind(this),
				},
				{
					eventName: 'Template:robot:delete',
					listener: function(event) {
						const deletedRobot = event.getData().robot;

						// analytics
						BX.ajax.runAction(
							'bizproc.analytics.push',
							{
								analyticsLabel: {
									automation_robot_delete: 'Y',
									delete_robot: deletedRobot.data.Type.toLowerCase(),
								},
							},
						);
					}.bind(this),
				},
				{
					eventName: 'Template:modified',
					listener: function() {
						this.component.markModified();
					}.bind(this),
				},
				{
					eventName: 'Template:enableManageMode',
					listener: function(event) {
						if (this.viewMode === Component.ViewMode.Edit)
						{
							this.component.enableManageMode(event.getData().documentStatus);
						}
					}.bind(this),
				},
			];
		},
		getRobotEventListeners: function(template)
		{
			return [
				{
					eventName: 'Robot:selected',
					listener: function() {
						const totalSelectedCount = (
							this.component.triggerManager.getSelectedTriggers().length
							+ template.getSelectedRobotNames().length
						);
						this.component.actionPanel.setTotalSelectedItems(totalSelectedCount);
						this.toggleActionDeactivateItem(template);
					}.bind(this),
				},
				{
					eventName: 'Robot:unselected',
					listener: function() {
						const totalSelectedCount = (
							this.component.triggerManager.getSelectedTriggers().length
							+ template.getSelectedRobotNames().length
						);
						this.component.actionPanel.setTotalSelectedItems(totalSelectedCount);
						this.toggleActionDeactivateItem(template);
					}.bind(this),
				},
				{
					eventName: 'Robot:title:editStart',
					listener: function() {
						BX.addClass(this.component.node, 'automation-base-blocked');
					}.bind(this),
				},
				{
					eventName: 'Robot:title:editCompleted',
					listener: function() {
						BX.removeClass(this.component.node, 'automation-base-blocked');
					}.bind(this),
				},
				{
					eventName: 'Robot:manage',
					listener: function(event) {
						const dstTemplate = this.getTemplateByColumnNode(event.getData().templateNode);
						const droppableItem = event.getData().droppableItem;
						const robot = event.getData().robot;

						let beforeRobot;
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
					}.bind(this),
				},
				{
					eventName: 'Robot:onAfterActivated',
					listener: function() {
						if (template)
						{
							template.markModified(true);
						}
					},
				},
				{
					eventName: 'Robot:onAfterDeactivated',
					listener: function() {
						if (template)
						{
							template.markModified(true);
						}
					},
				},
			];
		},
		toggleActionDeactivateItem(template)
		{
			const deactivateItem = this.component.actionPanel?.getItemById('automation_deactivate');
			const activateItem = this.component.actionPanel?.getItemById('automation_activate');

			if (deactivateItem && activateItem)
			{
				const selectedRobots = template.getSelectedRobotNames();
				let activate = false;
				if (selectedRobots.length > 0)
				{
					const deactivatedRobots = template.getDeactivatedRobotNames();
					activate = selectedRobots.every((selectedRobotName) => deactivatedRobots.includes(selectedRobotName));
				}

				activateItem[activate ? 'showAsInlineBlock' : 'hide']();
				deactivateItem[activate ? 'hide' : 'showAsInlineBlock']();
			}
		},
		reInitTemplates: function(templates)
		{
			for (let i = 0; i < this.templates.length; ++i)
			{
				if (templates[i])
				{
					this.templates[i].reInit(templates[i], this.viewMode);
					this.subscribeRobotEvents(this.templates[i]);
				}
			}
		},
		updateTemplates: function(statuses)
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
				onsuccess: function(response)
				{
					if (response.SUCCESS)
					{
						const updatedTemplates = response.DATA.templates;
						for (const updatedStatus in updatedTemplates)
						{
							if (updatedTemplates.hasOwnProperty(updatedStatus))
							{
								const template = this.getTemplateByStatusId(updatedStatus);
								const templateIndex = this.templatesData.findIndex((templateData) => {
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
			return this.availableRobots.find((item) => {
				return item.CLASS === type;
			});
		},
		serialize: function()
		{
			const templates = [];

			for (let i = 0; i < this.templates.length; ++i)
			{
				templates.push(this.templates[i].serialize());
			}

			return templates;
		},
		serializeModified: function()
		{
			const templates = [];

			this.templates.forEach((template) => {
				if (template.isModified())
				{
					templates.push(template.serialize());
				}
			});

			return templates;
		},
		countAllRobots: function()
		{
			let cnt = 0;

			this.templates.forEach((template) => {
				cnt += template.robots.length;
			});

			return cnt;
		},
		countAllActivatedRobots: function()
		{
			let count = 0;
			this.templates.forEach((template) => {
				count += (template.getActivatedRobotNames()).length;
			});

			return count;
		},
		getTemplateByColumnNode: function(node)
		{
			const statusId = node.getAttribute('data-status-id');

			return this.getTemplateByStatusId(statusId);
		},
		getTemplateById: function(id)
		{
			return this.templates.find((template) => {
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
			let modified = false;
			for (let i = 0; i < this.templates.length; ++i)
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
	const FileSelector = function(robot, container)
	{
		let config;
		const configString = container.getAttribute('data-config');
		if (configString)
		{
			config = BX.parseJSON(configString);
		}

		if (!BX.type.isPlainObject(config))
		{
			config = {};
		}

		this.container = container;

		// read configuration
		this.type = config.type || FileSelector.Type.File;
		if (config.selected && config.selected.length === 0)
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

		const templateRobots = robot.template ? robot.template.robots : [];
		this.setFileFields(robot.getDocument().getFields(), templateRobots);
		this.createDom();

		if (config.selected && config.selected.length > 0)
		{
			this.addItems(BX.clone(config.selected));
		}
	};

	FileSelector.Type = { None: '', Disk: 'disk', File: 'file' };

	FileSelector.prototype =
	{
		setFileFields: function(documentFields, templateRobots)
		{
			const fields = [];
			const labels = {};
			for (const documentField of documentFields)
			{
				if (documentField.Type === 'file')
				{
					fields.push(documentField);
				}
			}

			if (BX.type.isArray(templateRobots))
			{
				templateRobots.forEach((robot) => {
					robot.getReturnFieldsDescription().forEach((field) => {
						if (field.Type === 'file')
						{
							const expression = '{{~' + robot.getId() + ':' + field.Id + '}}';
							fields.push({
								Id: expression,
								Name: robot.getTitle() + ': ' + field.Name,
								Type: 'file',
								Expression: expression,
							});
							labels[expression] = robot.getTitle() + ': ' + field.Name;
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
			BX.Dom.append(this.createBaseNode(), this.container);
			this.showTypeControllerLayout(this.type);
		},
		createBaseNode: function()
		{
			const idSalt = BX.Bizproc.Automation.Helper.generateUniqueId();
			let typeRadio1 = null;

			if (this.fileFields.length > 0)
			{
				typeRadio1 = BX.create('input', {
					attrs: {
						className: 'bizproc-automation-popup-select-input',
						type: 'radio',
						id: 'type-1' + idSalt,
						name: this.typeInputName,
						value: FileSelector.Type.File,
					},
				});
				if (this.type === FileSelector.Type.File)
				{
					typeRadio1.setAttribute('checked', 'checked');
				}
			}

			const typeRadio2 = BX.create('input', {
				attrs: {
					className: 'bizproc-automation-popup-select-input',
					type: 'radio',
					id: 'type-2' + idSalt,
					name: this.typeInputName,
					value: FileSelector.Type.Disk,
				},
			});

			if (this.type === FileSelector.Type.Disk)
			{
				typeRadio2.setAttribute('checked', 'checked');
			}

			const children = [BX.create('span', {
				attrs: { className: 'bizproc-automation-popup-settings-title' },
				text: this.label + ':',
			})];

			if (typeRadio1)
			{
				children.push(typeRadio1, BX.create('label', {
					attrs: {
						className: 'bizproc-automation-popup-settings-link',
						for: 'type-1' + idSalt,
					},
					text: this.labelFile,
					events: {
						click: this.onTypeChange.bind(this, FileSelector.Type.File),
					},
				}));
			}

			children.push(typeRadio2, BX.create('label', {
				attrs: {
					className: 'bizproc-automation-popup-settings-link',
					for: 'type-2' + idSalt,
				},
				text: this.labelDisk,
				events: {
					click: this.onTypeChange.bind(this, FileSelector.Type.Disk),
				},
			}));

			return BX.create('div', {
				attrs: { className: 'bizproc-automation-popup-settings' },
				children: [
					BX.create('div', {
						attrs: { className: 'bizproc-automation-popup-settings-block' },
						children: children,
					}),
				],
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
			if (this.diskControllerNode)
			{
				BX.show(this.diskControllerNode);
			}
			else
			{
				this.diskControllerNode = BX.create('div');
				BX.Dom.append(this.diskControllerNode, this.container);
				const diskUploader = this.getDiskUploader();
				diskUploader.layout(this.diskControllerNode);
				diskUploader.show(true);
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
			if (this.fileControllerNode)
			{
				BX.show(this.fileControllerNode);
			}
			else
			{
				this.fileItemsNode = BX.create('span');
				this.fileControllerNode = BX.create('div', { children: [this.fileItemsNode] });
				BX.Dom.append(this.fileControllerNode, this.container);
				const addButtonNode = BX.create('a', {
					attrs: { className: 'bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-thin' },
					text: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD'),
				});

				BX.Dom.append(addButtonNode, this.fileControllerNode);

				BX.bind(addButtonNode, 'click', this.onFileFieldAddClick.bind(this, addButtonNode));
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
								diskAttachFiles: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_ATTACH_FILE'),
								diskAttachedFiles: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_ATTACHED_FILES'),
								diskSelectFile: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE'),
								diskSelectFileLegend: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE_LEGEND_MSGVER_1'),
								diskUploadFile: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE'),
								diskUploadFileLegend: BX.Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE_LEGEND'),
							},
					},
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
			const itemNode = this.fileItemsNode.querySelector('[data-file-id="' + item.id + '"]');

			return !!itemNode;
		},
		addFileItem: function(item)
		{
			if (this.isFileItemSelected(item))
			{
				return false;
			}

			const node = this.createFileItemNode(item);
			if (!this.multiple)
			{
				BX.cleanNode(this.fileItemsNode);
			}

			BX.Dom.append(node, this.fileItemsNode);
		},
		addItems: function(items)
		{
			if (this.type === FileSelector.Type.File)
			{
				for (const item of items)
				{
					this.addFileItem(item);
				}
			}
			else
			{
				this.getDiskUploader()
					.setValues(
						this.convertToDiskItems(items),
					);
			}
		},
		convertToDiskItems: function(items)
		{
			const diskItems = [];
			for (const item of items)
			{
				diskItems.push({
					ID: item.id,
					NAME: item.name,
					SIZE: item.size,
					VIEW_URL: '',
				});
			}

			return diskItems;
		},
		removeFileItem: function(item)
		{
			const itemNode = this.fileItemsNode.querySelector('[data-file-id="' + item.id + '"]');
			if (itemNode)
			{
				this.fileItemsNode.removeChild(itemNode);
			}
		},
		onFileFieldAddClick: function(addButtonNode, e)
		{
			const me = this;
			const menuItems = [];

			const fields = this.fileFields;
			for (const field of fields)
			{
				menuItems.push({
					text: BX.util.htmlspecialchars(field.Name),
					field: field,
					onclick: function(e, item)
					{
						this.popupWindow.close();
						me.onFieldSelect(item.field);
					},
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
					offsetLeft: (BX.pos(addButtonNode).width / 2),
					angle: { position: 'top', offset: 0 },
				},
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
				type: FileSelector.Type.File,
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
			let label = item.name || '';
			if (this.fileLabels[label])
			{
				label = this.fileLabels[label];
			}

			return BX.create('span', {
				attrs: {
					className: 'bizproc-automation-popup-autocomplete-item',
					'data-file-id': item.id,
					'data-file-expression': item.expression,
				},
				children: [
					BX.create('span', {
						attrs: {
							className: 'bizproc-automation-popup-autocomplete-name',
						},
						text: label,
					}),
					BX.create('span', {
						attrs: {
							className: 'bizproc-automation-popup-autocomplete-delete',
						},
						events: {
							click: this.removeFileItem.bind(this, item),
						},
					}),
				],
			});
		},
		onBeforeSave: function()
		{
			let ids = [];
			if (this.type === FileSelector.Type.Disk)
			{
				ids = this.getDiskUploader().getValues();
			}
			else if (this.type === FileSelector.Type.File)
			{
				this.fileItemsNode.childNodes.forEach((node) => {
					const id = node.getAttribute('data-file-expression');
					if (id !== '')
					{
						ids.push(id);
					}
				});
			}

			for (const id of ids)
			{
				this.container.appendChild(BX.create('input', {
					props: {
						type: 'hidden',
						name: this.valueInputName + (this.multiple ? '[]' : ''),
						value: id,
					},
				}));
			}
		},
	};

	const API = {
		documentName: null,
		documentType: null,
		documentFields: null,
		documentSigned: null,
		showRobotSettings: function(robotData, documentType, documentStatus, onSaveCallback) {
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
			tpl.init({ DOCUMENT_FIELDS: this.documentFields }, Component.ViewMode.None);

			tpl.subscribe('Template:help:show', (event) => {
				event.preventDefault();

				if (top.BX.Helper)
				{
					top.BX.Helper.show('redirect=detail&code=14889274');
				}
			});

			tpl.openRobotSettingsDialog(robot, null, onSaveCallback);
		},
	};

	const showGlobals = {
		showVariables: function()
		{
			const documentTypeSigned = this.component.data.DOCUMENT_TYPE_SIGNED;
			const mode = BX.Bizproc.Globals.Manager.Instance.mode.variable;

			BX.Bizproc.Globals.Manager.Instance.showGlobals(mode, documentTypeSigned)
				.then(this.onAfterSliderClose.bind(this, mode))
			;
		},
		showConstants: function()
		{
			const documentTypeSigned = this.component.data.DOCUMENT_TYPE_SIGNED;
			const mode = BX.Bizproc.Globals.Manager.Instance.mode.constant;

			BX.Bizproc.Globals.Manager.Instance.showGlobals(mode, documentTypeSigned)
				.then(this.onAfterSliderClose.bind(this, mode))
			;
		},
		onAfterSliderClose: function(mode, slider)
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
		isCorrectMode: function(mode)
		{
			return BX.Type.isStringFilled(mode) && Object.values(BX.Bizproc.Globals.Manager.Instance.mode).includes(mode);
		},
	};

	const Debugger = {
		showStartPage: function()
		{
			BX.Bizproc.Debugger.Manager.Instance.openDebuggerStartPage(this.component.documentSigned).then();
		},

		showDebugSessions: function()
		{
			const componentParams = {
				documentSigned: this.component.documentSigned,
			};

			this.openSlider('bizproc.debugger.session.list', componentParams, { width: 1150 });
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
				BX.Type.isPlainObject(params) ? params : {},
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
	const SelectorItemsManager = BX.Bizproc.Automation.SelectorItemsManager;

	BX.Bizproc.Automation.Component = Component;
	BX.Bizproc.Automation.API = API;
	BX.Bizproc.Automation.showGlobals = showGlobals;
	BX.Bizproc.Automation.Debugger = Debugger;

	BX.namespace('BX.Bizproc.Automation.Selector');
	BX.Bizproc.Automation.Selector.InlineSelectorCondition = BX.Bizproc.Automation.InlineSelectorCondition;

})(window.BX || window.top.BX);
