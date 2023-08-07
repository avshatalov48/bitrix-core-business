import {Type, Loc, ajax, Uri} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

import {Scrum} from './scrum';
import {Avatar} from './avatar';
import {DateCorrector} from './date.corrector';
import {ThemePicker} from './themepicker';
import {Tags} from './tags';
import {Util} from './util';
import {TypePresetSelector} from './typepresetselector';
import {ConfidentialitySelector} from './confidentialityselector';
import {Buttons} from './buttons';
import {Wizard} from './wizard';
import {AlertManager} from './alert';
import {TeamManager} from './team';
import {FeaturesManager} from './features';
import {UFManager} from './uf';

class WorkgroupForm extends EventEmitter
{
	static instance = null;

	static getInstance()
	{
		return WorkgroupForm.instance;
	}

	constructor(
		params: {
			componentName: string,
			signedParameters: string,
			selectedProjectType: string,
			selectedConfidentialityType: string,
			groupId: number,
			isScrumProject: boolean,
			config: Object,
			avatarUploaderId: string,
			themePickerData: Object,
			projectOptions: Object,
			projectTypes: Object,
			confidentialityTypes: Object,
			stepsCount: number,
			focus: string,
			culture: Object
		}
	)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.WorkgroupForm');

		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.userSelector = '';
		this.lastAction = 'invite';
		this.animationList = {};
		this.selectedTypeCode = false;

		this.groupId = parseInt(params.groupId);
		this.isScrumProject = params.isScrumProject;
		this.config = params.config;
		this.avatarUploaderId = params.avatarUploaderId;
		this.themePickerData = params.themePickerData;
		this.projectOptions = params.projectOptions;

		this.projectTypes = params.projectTypes;
		this.confidentialityTypes = params.confidentialityTypes;
		this.selectedProjectType = params.selectedProjectType;
		this.selectedConfidentialityType = params.selectedConfidentialityType;
		this.initialFocus = (Type.isStringFilled(params.focus) ? params.focus : '');
		this.culture = params.culture ? params.culture : {};

		this.scrumManager = new Scrum({
			isScrumProject: this.isScrumProject,
		});

		this.wizardManager = new Wizard({
			currentStep: Object.entries(this.projectTypes).length > 1 ? 1 : 2,
			stepsCount: (params.stepsCount > 1 ? params.stepsCount : 1),
		});

		this.alertManager = new AlertManager({
			errorContainerId: 'sonet_group_create_error_block',
		});

		WorkgroupForm.instance = this;

		this.init(params);
		this.buttonsInstance = new Buttons();
	}

	init(params)
	{
		this.scrumManager.makeAdditionalCustomizationForm();

		if (this.groupId <= 0)
		{
			this.recalcForm();
		}

		new Avatar({
			componentName: this.componentName,
			signedParameters: this.signedParameters,
			groupId: this.groupId,
		});

		if (
			Type.isPlainObject(params.themePickerData)
			&& document.getElementById('GROUP_THEME_container')
		)
		{
			new ThemePicker({
				container: document.getElementById('GROUP_THEME_container'),
				theme: params.themePickerData,
			});
		}

		new DateCorrector({
			culture: this.culture
		});

		if (document.getElementById('group-tags-bind-node'))
		{
			new Tags({
				groupId: this.groupId,
				containerNodeId: 'group-tags-bind-node',
				hiddenFieldId: 'GROUP_KEYWORDS',
			});
		}

		new TypePresetSelector();
		new ConfidentialitySelector();
		new FeaturesManager();

		if (Type.isStringFilled(this.initialFocus))
		{
			if (this.initialFocus === 'description')
			{
				const groupDescriptionNode = document.getElementById('GROUP_DESCRIPTION_input');
				if (groupDescriptionNode)
				{
					groupDescriptionNode.focus();
				}
			}
		}
		else
		{
			const groupNameNode = document.getElementById('GROUP_NAME_input');
			if (groupNameNode)
			{
				groupNameNode.focus();
			}
		}

		this.bindEvents();

		Util.initExpandSwitches();
		Util.initDropdowns();

		if (Type.isStringFilled(params.expandableSettingsNodeId))
		{
			BX.UI.Hint.init(document.getElementById(params.expandableSettingsNodeId));
		}

		if (this.groupId <= 0 && this.selectedProjectType === 'scrum')
		{
			this.saveScrumAnalyticData();
		}
	}

	bindEvents()
	{
		if (BX.SidePanel.Instance.getTopSlider())
		{
			EventEmitter.subscribe(BX.SidePanel.Instance.getTopSlider().getWindow(), 'SidePanel.Slider:onClose', (event) => {
				setTimeout(() => {
					const sliderInstance = event.getTarget();
					if (!sliderInstance)
					{
						return;
					}

					BX.SidePanel.Instance.destroy(sliderInstance.getUrl());
				}, 500);
			});
		}

		const extranetCheckboxNode = document.getElementById('IS_EXTRANET_GROUP');
		if (
			extranetCheckboxNode
			&& extranetCheckboxNode.type === 'checkbox'
		)
		{
			extranetCheckboxNode.addEventListener('click', () => {
				this.switchExtranet(extranetCheckboxNode.checked);
			});
		}

		const visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');
		if (
			visibleCheckboxNode
			&& visibleCheckboxNode.type === 'checkbox'
		)
		{
			visibleCheckboxNode.addEventListener('click', () => {
				this.switchNotVisible(visibleCheckboxNode.checked);
			});
		}

		const projectCheckboxNode = document.getElementById('GROUP_PROJECT');
		if (
			projectCheckboxNode
			&& projectCheckboxNode.type === 'checkbox'
		)
		{
			projectCheckboxNode.addEventListener('click', () => {
				Util.recalcFormPartProject(projectCheckboxNode.checked);
			});
		}

		EventEmitter.subscribe(
			'BX.Socialnetwork.WorkgroupFormTeamManager::onEventsBinded',
			this.recalcFormDependencies.bind(this)
		);
	}

	recalcForm(params)
	{
		if (Type.isPlainObject(params))
		{
			if (!Type.isUndefined(params.selectedProjectType))
			{
				this.selectedProjectType = (Type.isStringFilled(params.selectedProjectType) ? params.selectedProjectType : '');
			}
			if (!Type.isUndefined(params.selectedConfidentialityType))
			{
				this.selectedConfidentialityType = (Type.isStringFilled(params.selectedConfidentialityType) ? params.selectedConfidentialityType : '');
			}
		}

		if (this.groupId <= 0)
		{
			this.scrumManager.isScrumProject = (
				Type.isPlainObject(this.projectTypes[this.selectedProjectType])
				&& Type.isStringFilled(this.projectTypes[this.selectedProjectType]['SCRUM_PROJECT'])
				&& this.projectTypes[this.selectedProjectType]['SCRUM_PROJECT'] === 'Y'
			);

			Util.recalcFormPartProject(
				Type.isPlainObject(this.projectTypes[this.selectedProjectType])
				&& Type.isStringFilled(this.projectTypes[this.selectedProjectType].PROJECT)
				&& this.projectTypes[this.selectedProjectType].PROJECT === 'Y'
			);
		}

		this.scrumManager.makeAdditionalCustomizationForm();

		if (this.groupId <= 0)
		{
			const openedCheckboxNode = document.getElementById('GROUP_OPENED');
			if (openedCheckboxNode)
			{
				Util.setCheckedValue(openedCheckboxNode, (
					Type.isPlainObject(this.confidentialityTypes[this.selectedConfidentialityType])
					&& Type.isStringFilled(this.confidentialityTypes[this.selectedConfidentialityType].OPENED)
					&& this.confidentialityTypes[this.selectedConfidentialityType].OPENED === 'Y'
				));
			}

			const visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');
			if (visibleCheckboxNode)
			{
				Util.setCheckedValue(visibleCheckboxNode, (
					Type.isPlainObject(this.confidentialityTypes[this.selectedConfidentialityType])
					&& Type.isStringFilled(this.confidentialityTypes[this.selectedConfidentialityType].VISIBLE)
					&& this.confidentialityTypes[this.selectedConfidentialityType].VISIBLE === 'Y'
				));
			}
		}

		this.recalcFormDependencies();
	};

	recalcFormDependencies()
	{
		const extranetCheckboxNode = document.getElementById('IS_EXTRANET_GROUP');
		if (extranetCheckboxNode)
		{
			this.switchExtranet(Util.getCheckedValue(extranetCheckboxNode));
		}

		const visibleCheckboxNode = document.getElementById('GROUP_VISIBLE');
		if (visibleCheckboxNode)
		{
			this.switchNotVisible(visibleCheckboxNode.checked)
		}
	}

	switchExtranet(isChecked)
	{
		this.emit('onSwitchExtranet', new BaseEvent({
			data: {
				isChecked,
			},
		}));

		const openedBlock = document.getElementById('GROUP_OPENED');
		if (openedBlock)
		{
			if (!isChecked)
			{
				if (openedBlock.type === 'checkbox')
				{
					openedBlock.disabled = false;
				}
			}
			else
			{
				if (openedBlock.type === 'checkbox')
				{
					openedBlock.disabled = true;
					openedBlock.checked = false;
				}
				else
				{
					openedBlock.value = 'N';
				}
			}
		}

		const visibleBlock = document.getElementById('GROUP_VISIBLE');
		if (visibleBlock)
		{
			if (!isChecked)
			{
				if (visibleBlock.type == 'checkbox')
				{
					visibleBlock.disabled = false;
				}
			}
			else
			{
				if (visibleBlock.type == 'checkbox')
				{
					visibleBlock.disabled = true;
					visibleBlock.checked = false;
				}
				else
				{
					visibleBlock.value = 'N';
				}
			}

			this.switchNotVisible(visibleBlock.checked)
		}
	}

	switchNotVisible(isChecked)
	{
		const openedNode = document.getElementById('GROUP_OPENED')
		if (
			openedNode
			&& openedNode.type == 'checkbox'
		)
		{
			if (isChecked)
			{
				openedNode.disabled = false;
			}
			else
			{
				openedNode.disabled = true;
				openedNode.checked = false;
			}
		}
	}

	submitForm(e)
	{
		let actionUrl = document.getElementById('sonet_group_create_popup_form').action;

		if (actionUrl)
		{
			const groupIdNode = document.getElementById('SONET_GROUP_ID');
			let b24statAction = 'addSonetGroup';

			if (groupIdNode)
			{
				if (parseInt(groupIdNode.value) <= 0)
				{
					actionUrl = Uri.addParam(actionUrl, {
						action: 'createGroup',
						groupType: this.selectedTypeCode,
					});
				}
				else
				{
					b24statAction = 'editSonetGroup';
				}
			}

			actionUrl = Uri.addParam(actionUrl, {
				b24statAction: b24statAction,
			});

			const formElements = document.forms['sonet_group_create_popup_form'].elements;
			if (
				formElements.GROUP_PROJECT
				&& (
					formElements.IS_EXTRANET_GROUP
					|| formElements.GROUP_OPENED
				)
			)
			{
				let b24statType = (formElements.GROUP_PROJECT.checked ? 'project-' : 'group-');
				if (
					formElements.IS_EXTRANET_GROUP
					&& formElements.IS_EXTRANET_GROUP.checked
				)
				{
					b24statType += 'external';
				}
				else
				{
					b24statType += (formElements.GROUP_OPENED.checked ? 'open' : 'closed');
				}

				actionUrl = Uri.addParam(actionUrl, {
					b24statType: b24statType,
				});
			}

			if (
				formElements.SCRUM_PROJECT
				&& b24statAction === 'addSonetGroup'
			)
			{
				actionUrl = Uri.addParam(actionUrl, {
					analyticsLabel: {
						scrum: 'Y',
						action: 'scrum_create',
					},
				});
			}

			Buttons.showWaitSubmitButton(true);

			ajax.submitAjax(
				document.forms['sonet_group_create_popup_form'],
				{
					url: actionUrl,
					method: 'POST',
					dataType: 'json',
					data: {
						PROJECT_OPTIONS: this.projectOptions,
					},
					onsuccess: (response) => {
						if (Type.isStringFilled(response.ERROR))
						{
							const warningText = (
								Type.isStringFilled(response.WARNING)
									? `${response.WARNING}<br>`
									: ''
							);

							this.alertManager.showAlert(`${warningText}${response.ERROR}`);

							if (Type.isStringFilled(response.WIZARD_STEP_PROCESSED))
							{
								this.wizardManager.recalcAfterSubmit({
									processedStep: response.WIZARD_STEP_PROCESSED.toLowerCase(),
									createdGroupId: parseInt(!Type.isUndefined(response.CREATED_GROUP_ID) ? response.CREATED_GROUP_ID : 0),
								});
							}

							if (
								Type.isArray(response.SUCCESSFULL_USERS_ID)
								&& response.SUCCESSFULL_USERS_ID.length > 0
							)
							{
								response.SUCCESSFULL_USERS_ID = response.SUCCESSFULL_USERS_ID.map((userId) => {
									return Number(userId);
								})

								const usersSelector = TeamManager.getInstance().usersSelector;
								const usersSelectorDialog = (usersSelector ? usersSelector.getDialog() : null);
								if (usersSelectorDialog)
								{
									usersSelectorDialog.getSelectedItems().forEach((item) => {
										if (
											item.entityId === 'user'
											&& response.SUCCESSFULL_USERS_ID.includes(item.id)
										)
										{
											item.deselect();
										}
									});
								}

								window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
									code: 'afterInvite',
									data: {},
								});
							}

							Buttons.showWaitSubmitButton(false);
						}
						else if (response.MESSAGE === 'SUCCESS')
						{
							const currentSlider = BX.SidePanel.Instance.getSliderByWindow(window);
							if (currentSlider)
							{
								const event = new BaseEvent({
									compatData: [ currentSlider.getEvent('onClose') ],
									data: currentSlider.getEvent('onClose'),
								});

								EventEmitter.emit(window.top, 'SidePanel.Slider:onClose', event);
							}

							if (window === top.window) // not frame
							{
								if (Type.isStringFilled(response.URL))
								{
									top.location.href = response.URL;
								}
							}
							else if (Type.isStringFilled(response.ACTION))
							{
								let eventData = null;

								if (
									[ 'create', 'edit' ].includes(response.ACTION)
									&& !Type.isUndefined(response.GROUP)
								)
								{
									eventData = {
										code: (response.ACTION == 'create' ? 'afterCreate' : 'afterEdit'),
										data: {
											group: response.GROUP,
											projectOptions: this.projectOptions,
										}
									};
								}
								else if (response.ACTION === 'invite')
								{
									eventData = {
										code: 'afterInvite',
										data: {},
									};
								}

								if (eventData)
								{
									window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);
									if (response.ACTION === 'create')
									{
										const createdGroupsData = JSON.parse(response.SELECTOR_GROUPS);
										if (Type.isArray(createdGroupsData))
										{
											window.top.BX.SidePanel.Instance.postMessageAll(window, 'BX.Socialnetwork.Workgroup:onAdd', { projects: createdGroupsData });
										}
									}

									if (currentSlider)
									{
										BX.SidePanel.Instance.close(false, () => {
											BX.SidePanel.Instance.destroy(currentSlider.getUrl());
										});
									}

									if (
										response.ACTION == 'create'
										&& Type.isStringFilled(response.URL)
										&& (
											!Type.isStringFilled(this.config.refresh)
											|| this.config.refresh === 'Y'
										)
									)
									{
										let bindingFound = false;

										BX.SidePanel.Instance.anchorRules.find((rule) => {
											if (
												bindingFound
												|| !Type.isArray(rule.condition)
											)
											{
												return;
											}

											rule.condition.forEach((condition) => {
												if (bindingFound)
												{
													return;
												}

												if (response.URL.match(condition))
												{
													bindingFound = true;
												}
											});
										});


										if (bindingFound)
										{
											BX.SidePanel.Instance.open(
												response.URL
											);
										}
										else
										{
											top.window.location.href = response.URL;
										}
									}
								}
							}
						}
					},
					onfailure: (errorData) => {
						Buttons.showWaitSubmitButton(false);
						this.alertManager.showAlert(Loc.getMessage('SONET_GCE_T_AJAX_ERROR'));
					}
				}
			);
		}

		e.preventDefault();
	}

	saveScrumAnalyticData()
	{
		const actionUrl = document.getElementById('sonet_group_create_popup_form').action;

		const source = new Uri(actionUrl).getQueryParam('source');
		const availableSources = new Set([
			'guide_adv',
			'guide_direct',
			'guide_portal'
		]);
		if (availableSources.has(source))
		{
			ajax.runAction(
				'bitrix:tasks.scrum.info.saveScrumStart',
				{
					data: {},
					analyticsLabel: {
						scrum: 'Y',
						action: 'scrum_start',
						source: source
					}
				}
			);
		}
	}

	showHideBlock(params)
	{
		if (!Type.isPlainObject(params))
		{
			return false;
		}

		const containerNode = params.container;
		const blockNode = params.block;
		const show = !!params.show;

		if (
			!Type.isDomNode(containerNode)
			|| !Type.isDomNode(blockNode)
		)
		{
			return false;
		}

		if (
			!Type.isUndefined(this.animationList[blockNode.id])
			&& !Type.isNull(this.animationList[blockNode.id])
		)
		{
			return false;
		}

		this.animationList[blockNode.id] = null;

		const maxHeight = parseInt(blockNode.offsetHeight);
		const duration = (!Type.isUndefined(params.duration) && parseInt(params.duration) > 0 ? parseInt(params.duration) : 0);

		if (show)
		{
			containerNode.style.display = 'block';
		}

		if (duration > 0)
		{
			if (Type.isStringFilled(blockNode.id))
			{
				this.animationList[blockNode.id] = true;
			}

			BX.delegate((new BX.easing({
				duration: duration,
				start: {
					height: (show ? 0 : maxHeight),
					opacity: (show ? 0 : 100),
				},
				finish: {
					height: (show ? maxHeight : 0),
					opacity: (show ? 100 : 0),
				},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step: (state) => {
					containerNode.style.maxHeight = `${state.height}px`;
					containerNode.style.opacity = state.opacity / 100;
				},
				complete: () => {
					if (Type.isStringFilled(blockNode.id))
					{
						this.animationList[blockNode.id] = null;
					}

					if (
						!Type.isUndefined(params.callback)
						&& Type.isFunction(params.callback.complete)
					)
					{
						containerNode.style.maxHeight = '';
						containerNode.style.opacity = '';
						params.callback.complete();
					}
				},
			})).animate(), this);

		}
		else
		{
			params.callback.complete();
		}

		return true;
	}
}

export {
	WorkgroupForm,
	TeamManager as WorkgroupFormTeamManager,
	UFManager as WorkgroupFormUFManager,
}
