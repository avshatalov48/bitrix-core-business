<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script>

//for NEW EDITOR processing
window.FCForm = function(arParams)
{
	this.url = '';
	this.lhe = '';
	this.entitiesId = {};
	this.form = BX(arParams['formId']);
	this.handler = window.LHEPostForm.getHandler(arParams['editorId']);
	this.editorName = arParams['editorName'];
	this.editorId = arParams['editorId'];
	this.eventNode = this.handler.eventNode;

	if (this.eventNode)
	{
		BX.addCustomEvent(this.eventNode, 'OnBeforeShowLHE', BX.delegate(function() {
			if (BX.browser.IsIOS() && BX.browser.IsMobile())
			{
				BX.addClass(window["document"]["documentElement"], 'bx-ios-fix-frame-focus');
				if (top && top["document"])
					BX.addClass(top["document"]["documentElement"], 'bx-ios-fix-frame-focus');
			}
			var node = this._getPlacehoder();
			if (node)
			{
				BX.show(node);
			}
		}, this));
	}

	this.windowEvents =
		{
			OnUCAfterRecordEdit : BX.delegate(function(entityId, id, data, act) {
				<?if($arResult["use_captcha"]===true):?>
				BX.ajax.getCaptcha(function(data) {
					BX("captcha_word").value = "";
					BX("captcha_code").value = data["captcha_sid"];
					BX("captcha").src = '/bitrix/tools/captcha.php?captcha_code=' + data["captcha_sid"];
					BX("captcha").style.display = "";
				});
				<?endif;?>
				
				if (!!this.entitiesId[entityId]) {
					if (act === "EDIT" || act === "ADD" || act === "REPLY")
					{
						this.show([entityId, id], [data['messageBBCode'], data['messageTitle']], data['messageFields'], act);
						this.editing = true;
					}
					else
					{
						this.hide(true);
						if (!!data['errorMessage']) {
							this.id = [entityId, id];
							this.showError(data['errorMessage']);
						}
						else if (!!data['okMessage']) {
							this.id = [entityId, id];
							this.showNote(data['okMessage']);
							this.id = null;
						}
					}
				}
			}, this),

			OnUCUserReply : BX.delegate(function(entityId, commentId, safeEdit) {
				if (!this._checkTextSafety([entityId, 0], safeEdit))
					return;
	
				if (this.entitiesId[entityId])
				{
					this.show([entityId, commentId]);
				}
			}, this),

			OnUCAfterCommentCancel : BX.delegate(function() {this.hide(true);}, this),
		};
};

window.FCForm.prototype = {
	linkEntity : function(Ent) {
		if (!!Ent)
		{
			for(var ii in Ent)
			{
				if (Ent.hasOwnProperty(ii))
				{
					this.entitiesId[ii] = Ent[ii];
				}
			}
		}
		if (!this.windowEventsSet && !!this.entitiesId)
		{
			BX.addCustomEvent(window, 'OnUCUserReply', this.windowEvents.OnUCUserReply);
			BX.addCustomEvent(window, 'OnUCAfterRecordEdit', this.windowEvents.OnUCAfterRecordEdit);
			BX.addCustomEvent(window, 'OnUCAfterCommentCancel', this.windowEvents.OnUCAfterCommentCancel);
			this.windowEventsSet = true;
		}
	},
	_checkTextSafety : function(id, checkObj) {
		if (checkObj === true)
		{
			checkObj = id;
			if (this.id && this.id.join('-') != id.join('-') && this.handler.editorIsLoaded && this.handler.oEditor.IsContentChanged())
				return window.confirm(BX.message('MPL_SAFE_EDIT'));
			return true;
		}
		return checkObj === false;
	},
	_getPlacehoder : function(res) {
		res = (!!res ? res : this.id);
		return (!!res ? BX('record-' + res.join('-') + '-placeholder') : null);
	},
//	todo: in switcher we must show\hide "add comment". Now it do nothing
	_getSwitcher : function(res) {
		res = (!!res ? res : this.id);
		return (!!res ? BX('record-' + res[0] + '-switcher') : null);
	},
	hide : function(quick)
	{
		if (this.eventNode.style.display != 'none') {
			BX.onCustomEvent(this.eventNode, 'OnShowLHE', [(quick === true ? false : 'hide')]);
		}
		if (quick) {
			BX.hide(this.form);
			document.body.appendChild(this.form);
		}
	},
	show : function(id, text, data, act)
	{
		if (this.id && !!id && this.id.join('-') == id.join('-'))
			return true;
		else
			this.hide(true);
		
		BX.show(this.form);

		if (this.form.subject)
		{
// 			title is not reinit in editor, do it manual
			if(typeof(text[1]) != 'undefined')
				this.form.subject.value = text[1];
			else
				this.form.subject.value = '';
		}

		this.id = id;
		this.parentId = (act == 'REPLY' ? id[1] : false);	//parentID for reply comment, for add or edit - not needed
		this.jsCommentId = BX.util.getRandomString(20);
		var node = this._getPlacehoder();

		node.appendChild(this.form);
		BX.onCustomEvent(this.eventNode, 'OnShowLHE', ['show']);
		BX.onCustomEvent(this.eventNode, 'OnUCFormAfterShow', [this, text[0], data]);

		return true;
	},
	submit : function() {
		if (this.busy === true)
			return 'busy';
		
		this.clearError();
		var text = (this.handler.editorIsLoaded ? this.handler.oEditor.GetContent() : '');

		if (!text)
		{
			this.showError(BX.message('BPC_ERROR_NO_TEXT'));
			return false;
		}
		BX.showWait(this.eventNode);
		this.busy = true;

		var post_data = {};
		window.convertFormToArray(this.form, post_data);
		post_data['REVIEW_TEXT'] = text;
		post_data['NOREDIRECT'] = "Y";
		post_data['MODE'] = "RECORD";
		post_data['id'] = this.id;
		if (this.jsCommentId !== null)
			post_data['COMMENT_EXEMPLAR_ID'] = this.jsCommentId;
		post_data['SITE_ID'] = BX.message("SITE_ID");
		post_data['LANGUAGE_ID'] = BX.message("LANGUAGE_ID");

		if (this.editing === true)
		{
			post_data['REVIEW_ACTION'] = "EDIT";
			post_data["FILTER"] = {"ID" : this.id[1]};
		}
		BX.onCustomEvent(this.eventNode, 'OnUCFormSubmit', [this, post_data]);
		BX.onCustomEvent(window, 'OnUCFormSubmit', [this.id[0], this.id[1], this, post_data]);
		BX.ajax({
			'method': 'POST',
			'url': this.form.action,
			'data': post_data,
			'dataType' : 'html',
			'processData' : false,
			onsuccess: BX.proxy(function(data) {
				var true_data = data, ENTITY_XML_ID = this.id[0];
				if (!!data)
				{
					var dataProcessed = BX.processHTML(data, true);
					
//					run scripts to reinit comments data
					scripts = dataProcessed.SCRIPT;
					for(var s in scripts)
					{
						if (scripts.hasOwnProperty(s) && scripts[s].isInternal)
						{
							eval(scripts[s].JS);
						}
					}
					
					BX.ajax.processScripts(scripts, true);
//					commentEr object may be set in template
					if(window.commentEr && window.commentEr == "Y")
					{
						BX('err_comment_'+this.id[1]).innerHTML = data;
					}
					else
					{
						if(BX('edit_id').value > 0)
						{
							var commentId = 'blg-comment-'+this.id[1];
							if(BX(commentId))
							{
								var newComment = BX.create('div',{'html':data});	// tmp container for data
//								paste response data from tmp container
								BX(commentId).innerHTML = BX.findChild(newComment, {"attribute" : {"id": commentId}}, true).innerHTML;
								BX.cleanNode(newComment, true);
//								if(BX.browser.IsIE()) //for IE, numbered list not rendering well
//									setTimeout(function (){BX('blg-comment-'+id).innerHTML = BX('blg-comment-'+id).innerHTML}, 10);
							}
							else
							{
								BX('blg-comment-'+this.id[1]+'old').innerHTML = data;
								if(BX.browser.IsIE()) //for IE, numbered list not rendering well
									setTimeout(function (){BX('blg-comment-'+id+'old').innerHTML = BX('blg-comment-'+id+'old').innerHTML}, 10);
							}
						}
						else
						{
							BX('new_comment_cont_'+this.id[1]).innerHTML += data;
							if(BX.browser.IsIE()) //for IE, numbered list not rendering well
								setTimeout(function (){BX('new_comment_cont_'+this.id[1]).innerHTML = BX('new_comment_cont_'+this.id[1]).innerHTML}, 10);
						}
//						todo: what is it?
						BX('form_c_del').style.display = "none";
					}
					window.commentEr = false;
				}
				BX.closeWait(this.eventNode);
				this.hide(true);
				this.id = null;	// after closing form we must unattach them from comment entity
//				disable button before?
				BX('post-button').disabled = false;
				this.busy = false;
			}, this),
			
			onfailure: BX.delegate(function() {
				BX.closeWait(this.eventNode);
				this.busy = false;
			}, this)
		});
	},
	checkConsent: function() {
//		consent was set previously
		if(this.consent == true)
		{
			this.submit();
		}
		else
		{
//			to listen consent answer if they not set already
			var control = BX.UserConsent.load(BX('<?=$component->createPostFormId()?>'));
			
//			add new accept event with form submit
			BX.addCustomEvent(
				control,
				BX.UserConsent.events.save,
				BX.proxy(function () {this.consent = true; this.submit();}, this)
			);
			BX.addCustomEvent(
				control,
				BX.UserConsent.events.refused,
				BX.proxy(function () {this.consent = false;}, this)
			);
			
//			to open consent form if needed
			BX.onCustomEvent(this, 'OnUCFormCheckConsent', []);
		}
	},
	showError : function(text) {
		var node = BX('err_comment_'+this.id[1]);
		node.insertBefore(BX.create('div', {
				attrs : {"class": "feed-add-error"},
				html: '<div class="blog-errors blog-note-box blog-note-error"><div class="blog-error-text" id="blg-com-err">' + BX.util.htmlspecialchars(text) + '</div></div>'
			}),
			node.firstChild);
	},
	clearError : function() {
		BX('err_comment_'+this.id[1]).innerHTML = "";
	},
};

window.convertFormToArray = function(form, data)
{
	data = (!!data ? data : []);
	if(!!form){
		var
			i,
			_data = [],
			n = form.elements.length;

		for(i=0; i<n; i++)
		{
			var el = form.elements[i];
			if (el.disabled)
				continue;
			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'hidden':
				case 'select-one':
					_data.push({name: el.name, value: el.value});
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
						_data.push({name: el.name, value: el.value});
					break;
				case 'select-multiple':
					for (var j = 0; j < el.options.length; j++) {
						if (el.options[j].selected)
							_data.push({name : el.name, value : el.options[j].value});
					}
					break;
				default:
					break;
			}
		}

		var current = data;
		i = 0;

		while(i < _data.length)
		{
			var p = _data[i].name.indexOf('[');
			if (p == -1) {
				current[_data[i].name] = _data[i].value;
				current = data;
				i++;
			}
			else
			{
				var name = _data[i].name.substring(0, p);
				var rest = _data[i].name.substring(p+1);
				if(!current[name])
					current[name] = [];

				var pp = rest.indexOf(']');
				if(pp == -1)
				{
					current = data;
					i++;
				}
				else if(pp === 0)
				{
					//No index specified - so take the next integer
					current = current[name];
					_data[i].name = '' + current.length;
				}
				else
				{
					//Now index name becomes and name and we go deeper into the array
					current = current[name];
					_data[i].name = rest.substring(0, pp) + rest.substring(pp+1);
				}
			}
		}
	}
	return data;
};

window.editCommentNew = function(key, postId)
{
	var data = {
		messageBBCode : top["text"+key],
		messageTitle : top["title"+key],
		messageFields : {
			arImages : top["arImages"],
			arDocs : top["arComDocs"+key],
			arFiles : top["<?=$component->createEditorId()?>Files"],
			arDFiles : top["arComDFiles"+key],
			UrlPreview : top["UrlPreview"+key]}
	};
	BX.onCustomEvent(window, 'OnUCAfterRecordEdit', ['BLOG_' + postId, key, data, 'EDIT']);
};

window.replyCommentNew = function(key, postId)
{
//	todo: arImages, arFiles etc
//	todo: can set empty data, it is not error?
	var data = {
		messageBBCode : '',
		messageTitle : '',
		messageFields : {
			arImages : top["arComFiles"+key],
			arDocs : top["arComDocs"+key],
			arFiles : top["arComFilesUf"+key],
			arDFiles : top["arComDFiles"+key],
			UrlPreview : top["UrlPreview"+key]}
	};
	BX.onCustomEvent(window, 'OnUCAfterRecordEdit', ['BLOG_' + postId, key, data, 'REPLY']);
};

window.submitCommentNew = function()
{
	<?if ($arParams['USER_CONSENT'] == 'Y' && (empty($arResult["User"]) || !$arParams['USER_CONSENT_WAS_GIVEN'])):?>
		window["UC"]["f<?=$component->createPostFormId()?>"].checkConsent();
	<?else:?>
		window["UC"]["f<?=$component->createPostFormId()?>"].submit();
	<?endif;?>
};

window.cancelComment = function()
{
	BX.onCustomEvent(window, 'OnUCAfterCommentCancel');
};

window.__blogLinkEntity = function(entities, formId)
{
	if (!!window["UC"] && !!window["UC"]["f" + formId])
	{
		window["UC"]["f" + formId].linkEntity(entities);
	}
};

window.__blogOnUCFormAfterShow = function(obj, text, data) {
	data = (!!data ? data : {});
	var post_data = {
		ENTITY_XML_ID: obj.id[0],
		ENTITY_TYPE: obj.entitiesId[obj.id[0]][0],
		ENTITY_ID: obj.entitiesId[obj.id[0]][1],
		parentId: (!!obj.parentId ? obj.parentId : obj.id[1]),	//if set parent - it is reply. Other - add or edit, not use parent
		comment_post_id: obj.entitiesId[obj.id[0]][1],
		edit_id: (!!obj.parentId ? 0 : obj.id[1]),	//if set parent - it is reply, not need edit_id
		act: ((obj.id[1] <= 0 || !!obj.parentId) ? 'add' : 'edit'),
		logId: obj.entitiesId[obj.id[0]][2]
	};
	for (var ii in post_data)
	{
		if (!obj.form[ii])
			obj.form.appendChild(BX.create('INPUT', {attrs : {name : ii, type: "hidden"}}));
		obj.form[ii].value = post_data[ii];
	}

	onLightEditorShow(text, data);
};

window.__blogOnUCFormClear = function(obj) {
	LHEPostForm.reinitDataBefore(obj.editorId);
};

window.onLightEditorShow = function(content, data){
//	todo: adDocs, arImages etc
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
	window.LHEPostForm.reinitData("<?=$component->createEditorId()?>", content, res);
	if (data["arImages"])
	{
		var tmp,
			handler = LHEPostForm.getHandler("<?=$component->createEditorId()?>"), controllerId = '',
			controller = null;
		
		for (id in handler['controllers'])
		{
			if (handler['controllers'].hasOwnProperty(id))
			{
				if (handler['controllers'][id]["parser"] && handler['controllers'][id]["parser"]["bxTag"] == "postimage")
				{
					controller = handler['controllers'][id];
					break;
				}
			}
		}
		
		for (var ii in data["arImages"])
		{
			if (data["arImages"].hasOwnProperty(ii))
			{
				tmp = data["arImages"][ii];
				tmp["id"] = data["arImages"][ii]["id"];
				tmp["parser"] = 'postimage';
				tmp["storage"] = 'bfile';

//				todo: add attached images in editor
//				if (controller)
//					controller.addFile(tmp);

				var ret = handler.checkFile(tmp.id, "common", tmp, true);
			}
		}
	}
}

window.__blogOnUCFormSubmit =  function(obj, post_data) {
	post_data["decode"] = "Y";	// to convert charset in component
};

window.blogCommentCtrlEnterHandler = function(e)
{
	submitCommentNew();
};


</script>

<?
//first initial bind - add only editor form ID
$component->bindPostToEditorForm(null, $component->createPostFormId(), null);
?>