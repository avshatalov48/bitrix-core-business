function __logGetNextPageLinkEntities(entities, correspondences)
{
	if (!!window.__logGetNextPageFormName && !!entities && !!correspondences &&
		!!window["UC"] && !!window["UC"][window.__logGetNextPageFormName] &&
		!!window["UC"][window.__logGetNextPageFormName].linkEntity)
	{
		window["UC"][window.__logGetNextPageFormName].linkEntity(entities);
		for (var ii in correspondences)
		{
			if (
				!!ii
				&& correspondences.hasOwnProperty(ii)
				&& !!correspondences[ii]
			)
			{
				window["UC"][window.__logGetNextPageFormName].entitiesCorrespondence[ii] = correspondences[ii];
			}
		}
	}
}

function __logChangeFavorites(log_id, node, newState, bFromMenu, event)
{
	BX.Livefeed.FeedInstance.changeFavorites({
		logId: log_id,
		node: node,
		newState: newState,
		fromMenu: !!bFromMenu,
		event: event
	});
}

BitrixLF = function ()
{
	this.cmdPressed = null;
	this.tagEntryIdList = [];
	this.inlineTagNodeList = [];

	if (window.top !== window && window.top.BX.Tasks && window.BX.Tasks && window.BX.Tasks.CommentActionController)
	{
		window.top.BX.Tasks.CommentActionController = window.BX.Tasks.CommentActionController;
	}
	if (BX.Tasks && BX.Tasks.CommentActionController)
	{
		void BX.Tasks.CommentActionController.init();
	}
};

BitrixLF.prototype.init = function(params)
{
	this.cmdPressed = false;

	if (BX.Livefeed && BX.Livefeed.InformerInstance)
	{
		BX.Livefeed.InformerInstance.lockCounterAnimation = false;
	}

	BX.addCustomEvent('onFrameDataProcessed', function() {
		if (BX.Livefeed && BX.Livefeed.InformerInstance)
		{
			BX.Livefeed.InformerInstance.lockCounterAnimation = false;
		}
	});

	if (BX.Livefeed && BX.Livefeed.PageInstance)
	{
		BX.Livefeed.PageInstance.init();

		if (BX.type.isPlainObject(params))
		{
			BX.Livefeed.PageInstance.firstPageLastTS = (!BX.type.isUndefined(params.firstPageLastTS) ? params.firstPageLastTS : 0);
			BX.Livefeed.PageInstance.firstPageLastId = (!BX.type.isUndefined(params.firstPageLastId) ? params.firstPageLastId : 0);
			BX.Livefeed.PageInstance.useBXMainFilter = (!BX.type.isUndefined(params.useBXMainFilter) ? params.useBXMainFilter : 'N');

			if (BX.type.isNotEmptyString(params.blogCommentFormUID))
			{
				BX.Livefeed.PageInstance.blogCommentFormUID = params.blogCommentFormUID;
			}
		}
	}
};

BitrixLF.prototype.LazyLoadCheckVisibility = function(image) // compatibility
{
	return BX.Livefeed.MoreButton.lazyLoadCheckVisibility(image);
};

/*
for compatibility in main.post.list call
*/
BitrixLF.prototype.createTask = function(params)
{
	BX.Livefeed.TaskCreator.create(params);
}

if (typeof oLF == 'undefined')
{
	oLF = new BitrixLF;
	window.oLF = oLF;
}