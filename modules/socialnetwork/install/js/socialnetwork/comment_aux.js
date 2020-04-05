(function() {

var BX = window.BX;
if (BX.CommentAux)
{
	return;
}

BX.CommentAux =
{
};

BX.CommentAux.init = function(params)
{
	BX.RenderParts.init(params);
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

			if (params.sourceEntityType == 'BLOG_POST')
			{
				result = BX.message('SONET_COMMENTAUX_JS_CREATETASK_BLOG_POST').replace('#TASK_NAME#', task);
			}
			else if (
				params.sourceEntityType == 'BLOG_COMMENT'
				|| params.sourceEntityType == 'FORUM_POST'
				|| params.sourceEntityType == 'LOG_COMMENT'
			)
			{
				var sourceComment = this.renderEntity({
					ENTITY_TYPE: 'createTaskSourceComment',
					SOURCE_ENTITY_TYPE: (
						typeof params.sourceEntityType != 'undefined'
							? params.sourceEntityType + (BX.type.isNotEmptyString(params.suffix) ? '_' + params.suffix : '')
							: ''
					),
					LINK: (typeof params.sourceEntityLink != 'undefined' ? params.sourceEntityLink : '')
				});
				result = BX.message('SONET_COMMENTAUX_JS_CREATETASK_BLOG_COMMENT').replace('#TASK_NAME#', task).replace('#COMMENT_LINK#', sourceComment);
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
			case 'createTaskSourceComment':
				result = BX.RenderParts.getNodeCreateTaskSourceComment(entity);
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