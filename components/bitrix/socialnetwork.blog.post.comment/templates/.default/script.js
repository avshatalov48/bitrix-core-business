(function() {
	if (!!window.__blogEditComment)
		return;

window.checkForQuote = function(e, node, ENTITY_XML_ID, author_id) {
	if (window.mplCheckForQuote)
		mplCheckForQuote(e, node, ENTITY_XML_ID, author_id)
};

window.__blogLinkEntity = function(entities, xmlId) {
	if (!!window["UC"] && !!window["UC"][xmlId])
	{
		var form = window["UC"][xmlId].eventNode;
		if (BX(form) && !form.hasOwnProperty("__blogLinkEntity"))
		{
			form["__blogLinkEntity"] = true;
			BX.addCustomEvent(form, 'OnUCFormBeforeShow', function(obj) {
				var entityXmlId = obj && obj.id && obj.id[0] ? obj.id[0] : null;
				if (entityXmlId && entities[entityXmlId])
				{
					BX.show(BX('blg-comment-' + entities[ii][1]));
				}
			});
		}
		for (var ii in entities)
		{
			if (entities.hasOwnProperty(ii))
			{
				var placeHolder = document.getElementById('blog-post-addc-add-' + entities[ii][1]);
				if (placeHolder)
				{
					placeHolder.addEventListener('click', window['UC'][ii].reply.bind(window['UC'][ii]));
				}
			}
		}
	}
};

window.__blogEditComment = function(key, postId){
	var data = {
		messageBBCode : top["text"+key],
		messageFields : {
			arImages : top["arComFiles"+key],
			arDocs : top["arComDocs"+key],
			arFiles : top["arComFilesUf"+key],
			arDFiles : top["arComDFiles"+key],
			UrlPreview : top["UrlPreview"+key]}
	};
	BX.onCustomEvent(window, 'OnUCAfterRecordEdit', ['BLOG_' + postId, key, data, 'EDIT']);
};

window.__blogOnUCFormAfterShow = function(obj, text, data)
{
	data = (BX.type.isNotEmptyObject(data) && BX.type.isNotEmptyObject(data.UF) ? data.UF : {});
	BX.onCustomEvent(window, "OnBeforeSocialnetworkCommentShowedUp", ['socialnetwork_blog']);

	var post_data = {
		ENTITY_XML_ID : obj.currentEntity.ENTITY_XML_ID,
		ENTITY_TYPE : obj.currentEntity.ENTITY_XML_ID.split('_')[0],
		ENTITY_ID : obj.currentEntity.ENTITY_XML_ID.split('_')[1],
		parentId : obj.id[1],
		comment_post_id : obj.currentEntity.ENTITY_XML_ID.split('_')[1],
		edit_id : obj.id[1],
		act : (obj.id[1] > 0 ? 'edit' : 'add'),
//		logId : obj.entitiesId[obj.id[0]][2]
	};
	for (var ii in post_data)
	{
		if (!obj.form[ii])
		{
			obj.form.appendChild(BX.create('INPUT', {
				attrs: {
					name: ii,
					type: 'hidden',
				},
			}));
		}

		obj.form[ii].value = post_data[ii];
	}
//	obj.form.action = SBPC.actionUrl.replace(/#source_post_id#/, post_data['comment_post_id']);

	var im = BX('captcha');
	if (!!im)
	{
		BX.ajax.getCaptcha(function(data) {
			BX("captcha_word").value = "";
			BX("captcha_code").value = data["captcha_sid"];
			BX("captcha").src = '/bitrix/tools/captcha.php?captcha_code=' + data["captcha_sid"];
			BX("captcha").style.display = "";
		});
	}

	LHEPostForm.reinitData(SBPC.editorId, text, data);
//	onLightEditorShow(text, data);
};

window.__blogOnUCFormSubmit =  function(obj, post_data) {
	post_data["decode"] = "Y";
};

window.__blogOnUCAfterRecordAdd = function(ENTITY_XML_ID, response) {
	if (
		response.errorMessage
		&& response.errorMessage.length > 0
	)
	{
		return;
	}

	if (BX('blg-post-inform-' + ENTITY_XML_ID.substr(5)))
	{
		var followNode = BX.findChild(BX('blg-post-inform-' + ENTITY_XML_ID.substr(5)), {'tag':'span', 'className': 'feed-inform-follow'}, true);
		if (followNode)
		{
			var strFollowOld = (followNode.getAttribute("data-follow") == "Y" ? "Y" : "N");
			if (strFollowOld == "N")
			{
				BX.findChild(followNode, { tagName: 'a' }).innerHTML = BX.message('sonetBPFollowY');
				followNode.setAttribute("data-follow", "Y");
			}
		}
	}
};
/*
window.onLightEditorShow = function(content, data)
{
	var res = {};
	if (data["arFiles"])
	{
		var tmp2 = {}, name, size;
		for (var ij = 0; ij < data["arFiles"].length; ij++)
		{
			name = BX.findChild(BX('wdif-doc-' + data["arFiles"][ij]), {className : "feed-com-file-name"}, true);
			size = BX.findChild(BX('wdif-doc-' + data["arFiles"][ij]), {className : "feed-con-file-size"}, true);

			tmp2['F' + ij] = {
				FILE_ID : data["arFiles"][ij],
				FILE_NAME : (name ? name.innerHTML : "noname"),
				FILE_SIZE : (size ? size.innerHTML : "unknown"),
				CONTENT_TYPE : "notimage/xyz"};
		}
		res["UF_BLOG_COMMENT_DOC"] = {
			USER_TYPE_ID : "file",
			FIELD_NAME : "UF_BLOG_COMMENT_DOC[]",
			VALUE : tmp2};
	}
	if (data["arDocs"])
		res["UF_BLOG_COMMENT_FILE"] = {
			USER_TYPE_ID : "webdav_element",
			FIELD_NAME : "UF_BLOG_COMMENT_FILE[]",
			VALUE : BX.clone(data["arDocs"])};
	if (data["arDFiles"])
		res["UF_BLOG_COMMENT_FILE"] = {
			USER_TYPE_ID : "disk_file",
			FIELD_NAME : "UF_BLOG_COMMENT_FILE[]",
			VALUE : BX.clone(data["arDFiles"])};
	if (data["UrlPreview"])
		res["UF_BLOG_COMMENT_URL_PRV"] = {
			USER_TYPE_ID : "url_preview",
			FIELD_NAME : "UF_BLOG_COMMENT_URL_PRV",
			VALUE : BX.clone(data["UrlPreview"])};
	LHEPostForm.reinitData(SBPC.editorId, content, res);
	if (data["arImages"])
	{
		var tmp, handler = LHEPostForm.getHandler(SBPC.editorId), controllerId = '';
		for (var ii in data["arImages"])
		{
			if (data["arImages"].hasOwnProperty(ii))
			{
				tmp = {
					id : data["arImages"][ii]["id"],
					element_id : data["arImages"][ii]["id"],
					element_name : data["arImages"][ii]["name"],
					element_size : 0,
					element_content_type: data["arImages"][ii]["type"],
					element_url: data["arImages"][ii]["src"],
					element_thumbnail: data["arImages"][ii]["thumbnail"],
					element_image: data["arImages"][ii]["src"],
					parser: 'postimage',
					storage : 'bfile'
				};
				var ret = handler.checkFile(tmp.id, 'common', tmp, true);
			}
		}
	}
};
*/
BX.SocialnetworkBlogPostComment = {
};

BX.SocialnetworkBlogPostComment.registerViewAreaList = function(params)
{
	if (
		typeof params == 'undefined'
		|| typeof params.containerId == 'undefined'
		|| typeof params.className == 'undefined'
	)
	{
		return;
	}

	if (BX(params.containerId))
	{
		var
			viewAreaList = BX.findChildren(BX(params.containerId), {'tag':'div', 'className': params.className}, true),
			fullContentArea = null;

		for (var i = 0, length = viewAreaList.length; i < length; i++)
		{
			if (viewAreaList[i].id.length > 0)
			{
				fullContentArea = null;
				if (BX.type.isNotEmptyString(params.fullContentClassName))
				{
					fullContentArea = BX.findChild(viewAreaList[i], {
						className: params.fullContentClassName
					});
				}

				BX.UserContentView.registerViewArea(viewAreaList[i].id, (fullContentArea ? fullContentArea : null));
			}
		}
	}
};

})(window);

