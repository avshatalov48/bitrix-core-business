(function() {

var BX = window.BX;
if (BX.CommentAux)
{
	return;
}

BX.CommentAux =
{
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
	if (type == 'share')
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
	else if (type == 'createtask')
	{
		if (
			typeof params == 'object'
			&& typeof params.taskId != 'undefined'
			&& parseInt(params.taskId) > 0
			&& typeof params.taskName != 'undefined'
			&& params.taskName.length > 0
			&& typeof params.sourceEntityType != 'undefined'
			&& params.sourceEntityType.length > 0
			&& typeof params.sourceEntityId != 'undefined'
			&& parseInt(params.sourceEntityId) > 0
		)
		{
			var task = this.renderEntity({
				ENTITY_TYPE: 'task',
				NAME: params.taskName,
				LINK: '',
				VISIBILITY: {
					userId: (typeof params.taskResponsibleId != 'undefined' && parseInt(params.taskResponsibleId) > 0 ? parseInt(params.taskResponsibleId) : 0)
				}
			});

			if (this.isSourcePost(params.sourceEntityType))
			{
				result = BX.message('SONET_COMMENTAUX_JS_CREATETASK_POST_' + (BX.type.isNotEmptyString(params.sourceEntityType) ? params.sourceEntityType : 'BLOG_POST')).replace(
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
	else if (type == 'fileversion')
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
		switch (entity.ENTITY_TYPE)
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
			case 'task':
				result = BX.RenderParts.getNodeTask(entity);
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

})();