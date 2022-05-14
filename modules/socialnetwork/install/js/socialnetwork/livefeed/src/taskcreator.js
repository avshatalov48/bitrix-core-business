import {ajax, Type, Loc, Dom, Tag} from 'main.core';
import {Popup} from 'main.popup';
import {BaseEvent, EventEmitter} from 'main.core.events';

export class TaskCreator
{
	static createTaskPopup = null;
	static cssClass = {
		popupContent: 'feed-create-task-popup-content',
		popupTitle: 'feed-create-task-popup-title',
		popupDescription: 'feed-create-task-popup-description',
	};
	static signedFiles = null;
	static sliderUrl = '';

	constructor()
	{
		this.initEvents();
	}

	initEvents()
	{

		EventEmitter.subscribe('tasksTaskEvent', (event: BaseEvent) => {

			const [ type, data ] = event.getCompatData();
			if (
				type !== 'ADD'
				|| !Type.isPlainObject(data.options)
				|| !Type.isBoolean(data.options.STAY_AT_PAGE)
				|| data.options.STAY_AT_PAGE
			)
			{
				return;
			}

			TaskCreator.signedFiles = null;
		});

		EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', (event: BaseEvent) => {

			const sliderInstance = event.getTarget();
			if (!sliderInstance)
			{
				return;
			}

			const sliderUrl = sliderInstance.getUrl();
			if (
				!Type.isStringFilled(sliderUrl)
				|| sliderUrl !== TaskCreator.sliderUrl
				|| !Type.isStringFilled(TaskCreator.signedFiles)
			)
			{
				return;
			}

			ajax.runAction('intranet.controlbutton.clearNewTaskFiles', {
				data: {
					signedFiles: TaskCreator.signedFiles,
				},
			}).then(() => {
				TaskCreator.signedFiles = null;
			});
		});
	}

	static create(params)
	{
		if (Loc.getMessage('SONET_EXT_LIVEFEED_INTRANET_INSTALLED') === 'Y')
		{
			ajax.runAction('intranet.controlbutton.getTaskLink', {
				data: {
					entityType: params.entityType,
					entityId: params.entityId,
					postEntityType: (Type.isStringFilled(params.postEntityType) ? params.postEntityType : params.entityType),
					entityData: {},
				},
			}).then((response) => {

				if (!Type.isStringFilled(response.data.SUFFIX))
				{
					response.data.SUFFIX = '';
				}


				const requestData = response.data;

				requestData.DESCRIPTION = this.formatTaskDescription(requestData.DESCRIPTION, requestData.URL, params.entityType, requestData.SUFFIX);

				if (parseInt(params.parentTaskId) > 0)
				{
					requestData.PARENT_ID = parseInt(params.parentTaskId);
				}

				if (Type.isStringFilled(requestData.UF_TASK_WEBDAV_FILES_SIGN))
				{
					this.signedFiles = requestData.UF_TASK_WEBDAV_FILES_SIGN;
				}

				this.sliderUrl = response.data.link;

				BX.SidePanel.Instance.open(response.data.link, {
					requestMethod: 'post',
					requestParams: requestData,
					cacheable: false,
				});
			});
		}
		else
		{
			this.createTaskPopup = new Popup('BXCTP', null, {
				autoHide: false,
				zIndex: 0,
				offsetLeft: 0,
				offsetTop: 0,
				overlay: false,
				lightShadow: true,
				closeIcon: {
					right: '12px',
					top: '10px',
				},
				draggable: {
					restrict: true,
				},
				closeByEsc: false,
				contentColor : 'white',
				contentNoPaddings: true,
				buttons: [],
				content: Tag.render`<div id="BXCTP_content" class="${this.cssClass.popupContent}"></div>`,
				events: {
					onAfterPopupShow: () => {
						this.createTaskSetContent(Tag.render`<div class="${this.cssClass.popupTitle}">${Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_WAIT')}</div>`);

						ajax.runAction('socialnetwork.api.livefeed.getRawEntryData', {
							data: {
								params: {
									entityType: params.entityType,
									entityId: params.entityId,
									logId: (Type.isNumber(params.logId) ? params.logId : null),
									additionalParams: {
										getSonetGroupAvailable: 'Y',
										getLivefeedUrl: 'Y',
										checkPermissions: {
											feature: 'tasks',
											operation: 'create_tasks',
										}
									}
								}
							}
						}).then((response) => {

							const entryTitle = (Type.isStringFilled(response.data.TITLE) ? response.data.TITLE : '');
							const entryDescription = (Type.isStringFilled(response.data.DESCRIPTION) ? response.data.DESCRIPTION : '');
							const entryDiskObjects = (Type.isPlainObject(response.data.DISK_OBJECTS) ? response.data.DISK_OBJECTS : []);
							const entryUrl = (Type.isStringFilled(response.data.LIVEFEED_URL) ? response.data.LIVEFEED_URL : '');
							const entrySuffix = (Type.isStringFilled(response.data.SUFFIX) ? response.data.SUFFIX : '');
							const groupsAvailable = (Type.isPlainObject(response.data.GROUPS_AVAILABLE) ? response.data.GROUPS_AVAILABLE : []);
							const logId = (!Type.isUndefined(response.data.LOG_ID) ? parseInt(response.data.LOG_ID) : 0);

							if (
								(
									Type.isStringFilled(entryTitle)
									|| Type.isStringFilled(entryDescription)
								)
								&& Type.isStringFilled(entryUrl)
							)
							{
								const taskDescription = this.formatTaskDescription(entryDescription, entryUrl, params.entityType, entrySuffix);
								const taskData = {
									TITLE: entryTitle,
									DESCRIPTION: taskDescription,
									RESPONSIBLE_ID: Loc.getMessage('USER_ID'),
									CREATED_BY: Loc.getMessage('USER_ID'),
									UF_TASK_WEBDAV_FILES: entryDiskObjects,
								};

								const sonetGroupIdList = [];

								for (const [key, value] of Object.entries(groupsAvailable))
								{
									sonetGroupIdList.push(value);
								}

								if (sonetGroupIdList.length == 1)
								{
									taskData.GROUP_ID = parseInt(sonetGroupIdList[0]);
								}

								if (parseInt(params.entityType) > 0)
								{
									taskData.PARENT_ID = parseInt(params.entityType);
								}

								ajax.runComponentAction('bitrix:tasks.task', 'legacyAdd', {
									mode: 'class',
									data: {
										data: taskData,
									},
								}).then((response) => {

									const resultData = response.data;

									this.createTaskSetContentSuccess(resultData.DATA.ID);

									ajax.runAction('socialnetwork.api.livefeed.createEntityComment', {
										data: {
											params: {
												postEntityType: (Type.isStringFilled(params.postEntityType) ? params.postEntityType : params.entityType),
												sourceEntityType: params.entityType,
												sourceEntityId: params.entityId,
												entityType: 'TASK',
												entityId: resultData.DATA.ID,
												logId: (
													Type.isNumber(params.logId)
														? params.logId
														: logId > 0 ? logId : null
												),
											},
										},
									}).then(() => {
									}, () => {
									});
								}, (response) => {
									if (response.errors && response.errors.length)
									{
										const errors = [];
										response.errors.forEach((error) => {
											errors.push(error.message);
										});

										this.createTaskSetContentFailure(errors);
									}
								});
							}
							else
							{
								this.createTaskSetContentFailure([
									Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_ERROR_GET_DATA'),
								]);
							}
						}, () => {
							this.createTaskSetContentFailure([
								Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_ERROR_GET_DATA'),
							]);
						});
					},
					onPopupClose: () => {
						this.createTaskPopup.destroy();
					},
				}
			});

			this.createTaskPopup.show();
		}
	}

	static createTaskSetContentSuccess(taskId) {

		const taskLink = Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_PATH').replace('#user_id#', Loc.getMessage('USER_ID')).replace('#task_id#', taskId);

		this.createTaskPopup.destroy();

		window.top.BX.UI.Notification.Center.notify({
			content: Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_SUCCESS_TITLE'),
			actions: [{
				title: Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_VIEW'),
				events: {
					click: (event, balloon, action) => {
						balloon.close();
						window.top.BX.SidePanel.Instance.open(taskLink);
					}
				}
			}],

		});
	}

	static createTaskSetContentFailure(errors)
	{
		this.createTaskSetContent(Tag.render`<div>
			<div class="${this.cssClass.popupTitle}">${Loc.getMessage('SONET_EXT_LIVEFEED_CREATE_TASK_FAILURE_TITLE')}</div>
			<div class="${this.cssClass.popupDescription}">${errors.join('<br>')}</div>
		</div>`);
	}

	static createTaskSetContent(contentNode)
	{
		const containerNode = document.getElementById('BXCTP_content');
		if (!containerNode)
		{
			return;
		}

		Dom.clean(containerNode);
		containerNode.appendChild(contentNode);
	}

	static formatTaskDescription(taskDescription, livefeedUrl, entityType, suffix)
	{
		let result = taskDescription;

		suffix = (Type.isStringFilled(suffix) ? `_${suffix}` : '');

		if (
			!!livefeedUrl
			&& !!entityType
			&& livefeedUrl.length > 0
		)
		{
			result += "\n\n" + Loc.getMessage(`SONET_EXT_COMMENTAUX_CREATE_TASK_${entityType}${suffix}`).replace(
				'#A_BEGIN#', `[URL=${livefeedUrl}]`
			).replace(
				'#A_END#', '[/URL]'
			);
		}

		return result;
	}
}
