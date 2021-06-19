(function() {

var BX = window.BX;
if (BX.CommentAux)
{
	return;
}

BX.CommentAux = {
	postEventTypeList: [
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
		'BITRIX24_NEW_USER'
	],
	commentEventTypeList: [
		'BLOG_COMMENT',
		'FORUM_POST',
		'LOG_COMMENT'
	]
};

BX.onCustomEvent('BX.CommentAux.initialize', []);

BX.CommentAux.init = function(params)
{
	BX.RenderParts.init(params);
};

BX.CommentAux.isSourcePost = function(eventType)
{
	return BX.util.in_array(eventType, this.postEventTypeList);
};

BX.CommentAux.isSourceComment = function(eventType)
{
	return BX.util.in_array(eventType, this.commentEventTypeList);
};

BX.CommentAux.getLiveText = function(type, params)
{
	var result = '';
	if (type === 'share')
	{
		if (
			typeof params == 'object'
			&& params.length > 0
		)
		{
			result = BX.message(params.length == 1 ? 'SONET_COMMENTAUX_JS_SHARE_TEXT' : 'SONET_COMMENTAUX_JS_SHARE_TEXT_1');
			result = result.replace('#SHARE_LIST#', this.getShareList(params));
		}
	}
	else if (type === 'createentity')
	{
		if (
			BX.type.isNotEmptyObject(params)
			&& BX.type.isNotEmptyString(params.entityType)
			&& typeof params.entityId != 'undefined'
			&& parseInt(params.entityId) > 0
			&& BX.type.isNotEmptyString(params.entityName)
			&& BX.type.isNotEmptyString(params.sourceEntityType)
			&& typeof params.sourceEntityId != 'undefined'
			&& parseInt(params.sourceEntityId) > 0
		)
		{
			var entityName = this.renderEntity({
				ENTITY_TYPE: params.entityType,
				NAME: params.entityName,
				LINK: (BX.type.isNotEmptyString(params.entityUrl) ? params.entityUrl : ''),
				VISIBILITY: this.getEntityVisibility(params),
			});

			var sourceEntityType = '';
			var sourceEntityLink = (!BX.RenderParts.mobile ? '<a target="_blank" href="' + (BX.type.isNotEmptyString(params.sourceEntityLink) ? params.sourceEntityLink : '') + '">' : '');

			if (this.isSourcePost(params.sourceEntityType))
			{
				sourceEntityType = (BX.type.isNotEmptyString(params.sourceEntityType) ? params.sourceEntityType : 'BLOG_POST') + (BX.type.isNotEmptyString(params.suffix) ? '_' + params.suffix : '');
				result = BX.message('SONET_COMMENTAUX_JS_CREATEENTITY_POST_' + sourceEntityType)
					.replace('#ENTITY_CREATED#', this.getEntityCreatedMessage(params.entityType))
					.replace('#ENTITY_NAME#', entityName)
					.replace('#A_BEGIN#', sourceEntityLink)
					.replace('#A_END#', (!BX.RenderParts.mobile ? '</a>' : '')
				);
			}
			else if (this.isSourceComment(params.sourceEntityType))
			{
				sourceEntityType = (BX.type.isNotEmptyString(params.sourceEntityType) ? params.sourceEntityType + (BX.type.isNotEmptyString(params.suffix) ? '_' + params.suffix : '') : 'BLOG_COMMENT');

				result = BX.message('SONET_COMMENTAUX_JS_CREATEENTITY_COMMENT_' + sourceEntityType)
					.replace('#ENTITY_CREATED#', this.getEntityCreatedMessage(params.entityType))
					.replace('#ENTITY_NAME#', entityName)
					.replace('#A_BEGIN#', sourceEntityLink)
					.replace('#A_END#', (!BX.RenderParts.mobile ? '</a>' : ''));
			}
		}
	}
	else if (type === 'createtask')
	{
		if (
			BX.type.isNotEmptyObject(params)
			&& typeof params.taskId != 'undefined'
			&& parseInt(params.taskId) > 0
			&& BX.type.isNotEmptyString(params.taskName)
			&& BX.type.isNotEmptyString(params.sourceEntityType)
			&& typeof params.sourceEntityId != 'undefined'
			&& parseInt(params.sourceEntityId) > 0
		)
		{
			var task = this.renderEntity({
				ENTITY_TYPE: 'task',
				NAME: params.taskName,
				LINK: (BX.type.isNotEmptyString(params.taskUrl) ? params.taskUrl : ''),
				VISIBILITY: {
					userId: (typeof params.taskResponsibleId != 'undefined' && parseInt(params.taskResponsibleId) > 0 ? parseInt(params.taskResponsibleId) : 0)
				}
			});

			if (this.isSourcePost(params.sourceEntityType))
			{
				result = BX.message('SONET_COMMENTAUX_JS_CREATETASK_POST_' + (BX.type.isNotEmptyString(params.sourceEntityType) ? params.sourceEntityType : 'BLOG_POST') + (BX.type.isNotEmptyString(params.suffix) ? '_' + params.suffix : '')).replace(
					'#TASK_NAME#', task
				).replace(
					'#A_BEGIN#', (!BX.RenderParts.mobile ? '<a target="_blank" href="' + (BX.type.isNotEmptyString(params.sourceEntityLink) ? params.sourceEntityLink : '') + '">' : '')
				).replace(
					'#A_END#', (!BX.RenderParts.mobile ? '</a>' : '')
				);
			}
			else if (this.isSourceComment(params.sourceEntityType))
			{
				result = BX.message('SONET_COMMENTAUX_JS_CREATETASK_COMMENT_' + (BX.type.isNotEmptyString(params.sourceEntityType) ? params.sourceEntityType + (BX.type.isNotEmptyString(params.suffix) ? '_' + params.suffix : '')
						: 'BLOG_COMMENT'
				)).replace(
						'#TASK_NAME#', task
					).replace(
						'#A_BEGIN#', (!BX.RenderParts.mobile ? '<a target="_blank" href="' + (BX.type.isNotEmptyString(params.sourceEntityLink) ? params.sourceEntityLink : '') + '">' : '')
					).replace(
						'#A_END#', (!BX.RenderParts.mobile ? '</a>' : '')
				);
			}
		}
	}
	else if (type === 'fileversion')
	{
		var messageType = (
			typeof params == 'object'
			&& typeof params.isEnabledKeepVersion != 'undefined'
			&& params.isEnabledKeepVersion
				? 'SONET_COMMENTAUX_JS_FILEVERSION_TEXT'
				: 'SONET_COMMENTAUX_JS_HEAD_FILEVERSION_TEXT'
		);
		var userGender = (
			typeof params == 'object'
			&& typeof params.userGender != 'undefined'
				? params.userGender
				: ''
		);
		result = BX.message(messageType + (userGender.length > 0 ? '_' + userGender : ''));
	}

	return result;
};

BX.CommentAux.getShareList = function(params)
{
	var result = '';
	var renderedShareList = [];

	if (
		typeof params == 'object'
		&& params.length > 0
	)
	{
		var ii;
		for (ii = 0; ii < params.length; ii++)
		{
			if (params.hasOwnProperty(ii))
			{
				renderedShareList.push(this.renderEntity(params[ii]));
			}
		}

		result = renderedShareList.join(', ');
	}

	return result;
};

BX.CommentAux.renderEntity = function(entity)
{
	var result = '';

	if (
		typeof entity == 'object'
		&& entity.ENTITY_TYPE != 'undefined'
	)
	{
		switch (entity.ENTITY_TYPE.toUpperCase())
		{
			case 'U':
				result = BX.RenderParts.getNodeU(entity);
				break;
			case 'UA':
				result = BX.RenderParts.getNodeUA();
				break;
			case 'SG':
				result = BX.RenderParts.getNodeSG(entity);
				break;
			case 'DR':
				result = BX.RenderParts.getNodeDR(entity);
				break;
			case 'TASK':
				result = BX.RenderParts.getNodeTask(entity);
				break;
			case 'BLOG_POST':
				result = BX.RenderParts.getNodePost(entity);
				break;
			case 'CALENDAR_EVENT':
				result = BX.RenderParts.getNodeCalendarEvent(entity);
				break;
			default:
		}

		var tmp = BX.create('div', {
			children: [
				result
			]
		});

		result = tmp.innerHTML;
		BX.cleanNode(tmp, true);
	}

	return result;
};

BX.CommentAux.getEntityCreatedMessage = function(entityType)
{
	var result = '';

	if (!BX.type.isNotEmptyString(entityType))
	{
		return result;
	}

	switch (entityType)
	{
		case 'TASK':
			result = BX.message('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_TASK');
			break;
		case 'BLOG_POST':
			result = BX.message('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_BLOG_POST');
			break;
		case 'CALENDAR_EVENT':
			result = BX.message('SONET_COMMENTAUX_JS_CREATEENTITY_ENTITY_CREATED_CALENDAR_EVENT');
			break;

		default:
	}

	return result;

}

BX.CommentAux.getEntityTypeName = function(entityType)
{
	var result = '';

	if (!BX.type.isNotEmptyString(entityType))
	{
		return result;
	}

	switch (entityType)
	{
		case 'TASK':
			result = BX.message('SONET_COMMENTAUX_CREATEENTITY_ENTITY_TASK');
			break;
		default:
	}

	return result;
};

BX.CommentAux.getEntityVisibility = function(params)
{
	var result = {};

	if (params.entityType.toUpperCase() === 'TASK')
	{
		result.userId = (typeof params.taskResponsibleId != 'undefined' && parseInt(params.taskResponsibleId) > 0 ? parseInt(params.taskResponsibleId) : 0);
	}
	else if (params.entityType.toUpperCase() === 'BLOG_POST')
	{
		result.available = (
			BX.type.isArray(params.socNetPermissions)
			&& (
				params.socNetPermissions.indexOf('G2') > -1
				|| params.socNetPermissions.indexOf('UA') > -1
				|| params.socNetPermissions.indexOf('U' + BX.message('USER_ID')) > -1
				|| params.socNetPermissions.indexOf('US' + BX.message('USER_ID')) > -1
			)
		);
	}
	else if (params.entityType.toUpperCase() === 'CALENDAR_EVENT')
	{
		result.available = (
			BX.type.isArray(params.attendees)
			&& params.attendees.indexOf(BX.message('USER_ID')) > -1
		);
	}

	return result;
}

})();