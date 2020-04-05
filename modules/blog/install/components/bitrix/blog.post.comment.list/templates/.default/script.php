<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>
	
(function() {
	if (!!window.__blogEditComment)
		return;

window.__blogLinkEntity = function(entities, formId) {
	if (!!window["UC"] && !!window["UC"]["f" + formId])
	{
		window["UC"]["f" + formId].linkEntity(entities);
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
window.__blogOnUCFormClear = function(obj) {
	LHEPostForm.reinitDataBefore(obj.editorId);
};

window.__blogOnUCFormAfterShow = function(obj, text, data){
	data = (!!data ? data : {});
	var
		post_data = {
			ENTITY_XML_ID : obj.id[0],
			ENTITY_TYPE : obj.entitiesId[obj.id[0]][0],
			ENTITY_ID : obj.entitiesId[obj.id[0]][1],
			parentId : obj.id[1],
			comment_post_id : obj.entitiesId[obj.id[0]][1],
			edit_id : obj.id[1],
			act : (obj.id[1] > 0 ? 'edit' : 'add'),
			logId : obj.entitiesId[obj.id[0]][2]
		};
	for (var ii in post_data)
	{
		if (!obj.form[ii])
			obj.form.appendChild(BX.create('INPUT', {attrs : {name : ii, type: "hidden"}}));
		obj.form[ii].value = post_data[ii];
	}

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

	onLightEditorShow(text, data);
};

window.__blogOnClickBeforeSubmit = function(obj, res)
{
	<?if ($arParams['USER_CONSENT'] == 'Y' && (empty($arResult["User"]) || !$arParams['USER_CONSENT_WAS_GIVEN'])):?>
	
//		stop submit, until we dont have user consent
		res["result"] = false;
		
		var control = BX.UserConsent.load(BX('<?=$component->createPostFormId()?>'));
//		add new accept event with form submit
		BX.addCustomEvent(
			control,
			BX.UserConsent.events.save,
			BX.proxy(function () {
				this.result = true;
				BX.onCustomEvent(obj.eventNode, 'OnClickSubmit', [obj]);
			}, res)
		);
		BX.addCustomEvent(
			control,
			BX.UserConsent.events.refused,
			BX.proxy(function () {this["result"] = false;}, res)
		);
	
//		to open consent form if needed
		BX.onCustomEvent(this, 'OnUCFormCheckConsent', []);

	<?endif;?>
};

window.__blogOnUCFormSubmit =  function(obj, post_data) {
	post_data["decode"] = "Y";	// to convert charset in component
};

window.__blogOnUCAfterRecordAdd = function(entityId, data, true_data)
{
//	to show premoderation message
	BX.onCustomEvent(window, 'OnUCAfterRecordEdit', [entityId, data.messageId[1], data, 'ADD']);
};

// ctrl+enter submit
window.blogCommentCtrlEnterHandler = function ()
{
	if (!!window["UC"]["f<?=$component->createPostFormId()?>"] && !!window["UC"]["f<?=$component->createPostFormId()?>"].eventNode)
	{
		BX.onCustomEvent(window["UC"]["f<?=$component->createPostFormId()?>"].eventNode, 'OnButtonClick', ['submit']);
	}
	return false;
};

window.onLightEditorShow = function(content, data){
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
	// todo: different file types
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
	LHEPostForm.reinitData("<?=$component->createEditorId()?>", content, res);
	if (data["arImages"])
	{
		var tmp, handler = LHEPostForm.getHandler("<?=$component->createEditorId()?>"), controllerId = '';
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



})(window);

</script>