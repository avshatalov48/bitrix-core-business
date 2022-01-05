import { Type, Loc, Text, Dom } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { RenderParts } from 'socialnetwork.renderparts';

export class CommentAux
{
	static postEventTypeList = [
		'BLOG_POST',
		'FORUM_TOPIC',
		'TASK',
		'TIMEMAN_ENTRY',
		'TIMEMAN_REPORT',
		'LOG_ENTRY',
		'PHOTO_ALBUM',
		'PHOTO_PHOTO',
		'WIKI',
		'LISTS_NEW_ELEMENT',
		'CALENDAR_EVENT',
		'INTRANET_NEW_USER',
		'BITRIX24_NEW_USER',
	];

	static commentEventTypeList = [
		'BLOG_COMMENT',
		'FORUM_POST',
		'LOG_COMMENT'
	];

	static typesList = {
		share: 'share',
		createentity: 'createentity',
		createtask: 'createtask',
		fileversion: 'fileversion',
		taskinfo: 'taskinfo',
	};

	static init(params)
	{
		EventEmitter.emit('BX.CommentAux.initialize', new BaseEvent({
			compatData: [],
		}));

		RenderParts.init(params);
	}

	static isSourcePost(eventType)
	{
		return this.postEventTypeList.includes(eventType);
	}

	static isSourceComment(eventType)
	{

		return this.commentEventTypeList.includes(eventType);
	}

	static getTypesList()
	{
		return Object.values(this.typesList);
	}

	static getLiveTypesList()
	{
		return [
			this.typesList.createentity,
			this.typesList.createtask,
			this.typesList.fileversion,
			this.typesList.taskinfo,
		];
	}

	static getLiveText(type, params)
	{
		let result = '';
		let sourceEntityType = '';
		let sourceEntityLink = '';
		let suffix = '';

		if (type.toLowerCase() === this.typesList.share)
		{
			if (
				Type.isPlainObject(params)
				&& params.length > 0
			)
			{
				result = Loc.getMessage(params.length === 1 ? 'SONET_COMMENTAUX_JS_SHARE_TEXT' : 'SONET_COMMENTAUX_JS_SHARE_TEXT_1');
				result = result.replace('#SHARE_LIST#', this.getShareList(params));
			}
		}
		else if (type.toLowerCase() === this.typesList.createentity)
		{
			if (
				Type.isPlainObject(params)
				&& Type.isStringFilled(params.entityType)
				&& !Type.isUndefined(params.entityId)
				&& parseInt(params.entityId) > 0
				&& Type.isStringFilled(params.entityName)
				&& Type.isStringFilled(params.sourceEntityType)
				&& !Type.isUndefined(params.sourceEntityId)
				&& parseInt(params.sourceEntityId) > 0
			)
			{
				const entityName = this.renderEntity({
					ENTITY_TYPE: params.entityType,
					NAME: params.entityName,
					LINK: (Type.isStringFilled(params.entityUrl) ? params.entityUrl : ''),
					VISIBILITY: this.getEntityVisibility(params),
				});

				sourceEntityLink = (Type.isStringFilled(params.sourceEntityLink) ? params.sourceEntityLink : '');
				sourceEntityLink = (!RenderParts.mobile ? `<a target="_blank" href="${sourceEntityLink}">` : '');

				if (this.isSourcePost(params.sourceEntityType))
				{
					sourceEntityType = (Type.isStringFilled(params.sourceEntityType) ? params.sourceEntityType : 'BLOG_POST');
					suffix = (Type.isStringFilled(params.suffix) ? `_${params.suffix}` : '');

					sourceEntityType = `${sourceEntityType}${suffix}`;
					result = Loc.getMessage(`SONET_COMMENTAUX_JS_CREATEENTITY_POST_${sourceEntityType}`)
						.replace('#ENTITY_CREATED#', this.getEntityCreatedMessage(params.entityType))
						.replace('#ENTITY_NAME#', entityName)
						.replace('#A_BEGIN#', sourceEntityLink)
						.replace('#A_END#', (!RenderParts.mobile ? '</a>' : ''));
				}
				else if (this.isSourceComment(params.sourceEntityType))
				{
					suffix = (Type.isStringFilled(params.suffix) ? `_${params.suffix}` : '');
					sourceEntityType = (Type.isStringFilled(params.sourceEntityType) ? `${params.sourceEntityType}${suffix}` : 'BLOG_COMMENT');

					result = Loc.getMessage(`SONET_COMMENTAUX_JS_CREATEENTITY_COMMENT_${sourceEntityType}`)
						.replace('#ENTITY_CREATED#', this.getEntityCreatedMessage(params.entityType))
						.replace('#ENTITY_NAME#', entityName)
						.replace('#A_BEGIN#', sourceEntityLink)
						.replace('#A_END#', (!RenderParts.mobile ? '</a>' : ''));
				}
			}
		}
		else if (type.toLowerCase() === this.typesList.createtask)
		{
			if (
				Type.isPlainObject(params)
				&& !Type.isUndefined(params.taskId)
				&& parseInt(params.taskId) > 0
				&& Type.isStringFilled(params.taskName)
				&& Type.isStringFilled(params.sourceEntityType)
				&& !Type.isUndefined(params.sourceEntityId)
				&& parseInt(params.sourceEntityId) > 0
			)
			{
				const task = this.renderEntity({
					ENTITY_TYPE: 'task',
					NAME: params.taskName,
					LINK: (Type.isStringFilled(params.taskUrl) ? params.taskUrl : ''),
					VISIBILITY: {
						userId: (!Type.isUndefined(params.taskResponsibleId) && parseInt(params.taskResponsibleId) > 0 ? parseInt(params.taskResponsibleId) : 0)
					},
				});

				if (this.isSourcePost(params.sourceEntityType))
				{
					sourceEntityType = (Type.isStringFilled(params.sourceEntityType) ? params.sourceEntityType : 'BLOG_POST');
					suffix = (Type.isStringFilled(params.suffix) ? `_${params.suffix}` : '');
					sourceEntityLink = (Type.isStringFilled(params.sourceEntityLink) ? params.sourceEntityLink : '');

					result = Loc.getMessage(`SONET_COMMENTAUX_JS_CREATETASK_POST_${sourceEntityType}${suffix}`)
						.replace('#TASK_NAME#', task)
						.replace('#A_BEGIN#', (!RenderParts.mobile ? `<a target="_blank" href="${sourceEntityLink}">` : ''))
						.replace('#A_END#', (!RenderParts.mobile ? '</a>' : ''));
				}
				else if (this.isSourceComment(params.sourceEntityType))
				{
					suffix = (Type.isStringFilled(params.suffix) ? `_${params.suffix}` : '');
					sourceEntityType = (Type.isStringFilled(params.sourceEntityType) ? `${params.sourceEntityType}${suffix}` : 'BLOG_COMMENT');
					sourceEntityLink = (Type.isStringFilled(params.sourceEntityLink) ? params.sourceEntityLink : '');

					result = Loc.getMessage(`SONET_COMMENTAUX_JS_CREATETASK_COMMENT_${sourceEntityType}`)
						.replace('#TASK_NAME#', task)
						.replace('#A_BEGIN#', (!RenderParts.mobile ? `<a target="_blank" href="${sourceEntityLink}">` : ''))
						.replace('#A_END#', (!RenderParts.mobile ? '</a>' : '')
					);
				}
			}
		}
		else if (type.toLowerCase() === this.typesList.fileversion)
		{
			const messageType = (
				Type.isPlainObject(params)
				&& !Type.isUndefined(params.isEnabledKeepVersion)
				&& params.isEnabledKeepVersion
					? 'SONET_COMMENTAUX_JS_FILEVERSION_TEXT'
					: 'SONET_COMMENTAUX_JS_HEAD_FILEVERSION_TEXT'
			);
			const userGenderSuffix = (
				Type.isPlainObject(params)
				&& Type.isStringFilled(params.userGender)
					? `_${params.userGender}`
					: ''
			);
			result = Loc.getMessage(`${messageType}${userGenderSuffix}`);
		}
		else if (type.toLowerCase() === this.typesList.taskinfo)
		{
			if (
				Type.isPlainObject(params)
				&& Type.isStringFilled(params.JSON)
			)
			{
				const textList = [];
				let partsData = {};

				try
				{
					partsData = JSON.parse(Text.decode(params.JSON));
				}
				catch(e)
				{
					partsData = {};
				}

				Type.isArray(partsData)
				{
					partsData.forEach((partsItems) => {
						if (!Type.isArray(partsItems))
						{
							return;
						}

						partsItems.forEach((item) => {
							const messageCode = item[0];

							if (!Type.isStringFilled(messageCode))
							{
								return;
							}

							textList.push(this.renderEntity({
								ENTITY_TYPE: 'TASK_COMMENT_PART',
								CODE: messageCode,
								REPLACE_LIST: (Type.isPlainObject(item[1]) ? item[1] : {}),
							}));
						});
					});
				}

				if (textList.length)
				{
					result = textList.join('<br>');
				}
			}
		}

		return result;
	}

	static getShareList(params)
	{
		let result = '';
		const renderedShareList = [];

		if (
			!Type.isPlainObject(params)
			|| params.length <= 0
		)
		{
			return result;
		}

		Object.values(params).forEach((value) => {
			renderedShareList.push(this.renderEntity(value));
		});

		result = renderedShareList.join(', ');

		return result;
	}

	static renderEntity(entity)
	{
		let result = '';

		if (
			!Type.isPlainObject(entity)
			|| !Type.isStringFilled(entity.ENTITY_TYPE)
		)
		{
			return result;
		}

		switch (entity.ENTITY_TYPE.toUpperCase())
		{
			case 'U':
				result = RenderParts.getNodeU(entity);
				break;
			case 'UA':
				result = RenderParts.getNodeUA();
				break;
			case 'SG':
				result = RenderParts.getNodeSG(entity);
				break;
			case 'DR':
				result = RenderParts.getNodeDR(entity);
				break;
			case 'TASK':
				result = RenderParts.getNodeTask(entity);
				break;
			case 'BLOG_POST':
				result = RenderParts.getNodePost(entity);
				break;
			case 'CALENDAR_EVENT':
				result = RenderParts.getNodeCalendarEvent(entity);
				break;
			case 'TASK_COMMENT_PART':
				result = RenderParts.getTaskCommentPart(entity);
				break;
			default:
		}

		const tmp = Dom.create('div', {
			children: [
				result,
			]
		});

		result = tmp.innerHTML;
		Dom.clean(tmp);
		Dom.remove(tmp);

		return result;
	}

	static getEntityCreatedMessage(entityType)
	{
		let result = '';

		if (!Type.isStringFilled(entityType))
		{
			return result;
		}

		switch (entityType)
		{
			case 'TASK':
				result = Loc.getMessage('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_TASK');
				break;
			case 'BLOG_POST':
				result = Loc.getMessage('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_BLOG_POST');
				break;
			case 'CALENDAR_EVENT':
				result = Loc.getMessage('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_CALENDAR_EVENT');
				break;

			default:
		}

		return result;
	}

	static getEntityTypeName(entityType)
	{
		let result = '';

		if (!Type.isStringFilled(entityType))
		{
			return result;
		}

		switch (entityType)
		{
			case 'TASK':
				result = Loc.getMessage('SONET_COMMENTAUX_CREATEENTITY_ENTITY_TASK');
				break;
			default:
		}

		return result;
	}

	static getEntityVisibility(params)
	{
		const result = {};
		const currentUserId = parseInt(Loc.getMessage('USER_ID'));

		if (params.entityType.toUpperCase() === 'TASK')
		{
			result.userId = (
				!Type.isUndefined(params.taskResponsibleId)
				&& parseInt(params.taskResponsibleId) > 0
					? parseInt(params.taskResponsibleId)
					: 0
			);
		}
		else if (params.entityType.toUpperCase() === 'BLOG_POST')
		{
			result.available = (
				Type.isArray(params.socNetPermissions)
				&& (
					params.socNetPermissions.indexOf('G2') > -1
					|| params.socNetPermissions.indexOf('UA') > -1
					|| params.socNetPermissions.indexOf(`U${currentUserId}`) > -1
					|| params.socNetPermissions.indexOf(`US${currentUserId}`) > -1
				)
			);
		}
		else if (params.entityType.toUpperCase() === 'CALENDAR_EVENT')
		{
			result.available = (
				Type.isArray(params.attendees)
				&& params.attendees.indexOf(currentUserId) > -1
			);
		}

		return result;
	}
}
