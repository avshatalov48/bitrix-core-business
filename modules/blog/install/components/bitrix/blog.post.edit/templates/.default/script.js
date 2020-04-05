function show_special()
{
	var o = document.getElementById('special_perms');
	if (document.getElementById('blog_perms_1').checked===true)
		o.style.display='block';
	else
		o.style.display='none';
}

function changeDate()
{
	document.getElementById('date-publ').style.display = 'block';
	document.getElementById('date-publ-text').style.display = 'none';
	document.getElementById('DATE_PUBLISH_DEF').value = '';
}

BlogPostAutoSaveIcon = function () {
	var formId = 'POST_BLOG_FORM';
	var form = BX(formId);
	if (!form) return;
	
	var auto_lnk = BX('post-form-autosave-icon');
	var formHeaders = BX.findChild(form, {'className': /lhe-stat-toolbar-cont/ }, true, true);
	if (formHeaders.length < 1)
		return false;
	var formHeader = formHeaders[formHeaders.length-1];
	formHeader.insertBefore(auto_lnk, formHeader.children[0]);
}

BlogPostAutoSave = function () {
	var formId = 'POST_BLOG_FORM';
	var form = BX(formId);
	if (!form) return;

	var controlID = "POST_MESSAGE";
	var titleID = 'POST_TITLE';
	var title = BX(titleID);
	var tags = BX(formId).TAGS;
	
	var	iconClass = "blogPostAutoSave";
	var	actionClass = "blogPostAutoRestore";
	var	actionText = BX.message('AUTOSAVE_R');
	var recoverMessage = BX.message('BLOG_POST_AUTOSAVE');
	var recoverNotify = null;
	
	var auto_lnk = BX.create('A', {
		'attr': {'href': 'javascript:void(0)'},
		'props': {
			'className': iconClass+' bx-core-autosave bx-core-autosave-ready',
			'title': BX.message('AUTOSAVE_T'),
			'id': 'post-form-autosave-icon'
		}
	});
	
	BX('blog-post-autosave-hidden').appendChild(auto_lnk);
	
	var bindLHEEvents = function(_ob)
	{
		if (window.oBlogLHE)
		{
			window.oBlogLHE.fAutosave = _ob;
			BX.bind(window.oBlogLHE.pEditorDocument, 'keydown', BX.proxy(_ob.Init, _ob));
			BX.bind(window.oBlogLHE.pTextarea, 'keydown', BX.proxy(_ob.Init, _ob));
			BX.bind(title, 'keydown', BX.proxy(_ob.Init, _ob));
			BX.bind(tags, 'keydown', BX.proxy(_ob.Init, _ob));
		}
	}

	BX.addCustomEvent(form, 'onAutoSavePrepare', function (ob, h) {
		ob.DISABLE_STANDARD_NOTIFY = true;
		BX.bind(auto_lnk, 'click', BX.proxy(ob.Save, ob));
		var _ob=ob;
		setTimeout(function() { bindLHEEvents(_ob) },1500);
	});

	BX.addCustomEvent(form, 'onAutoSave', function(ob, form_data) {
		BX.removeClass(auto_lnk,'bx-core-autosave-edited');
		BX.removeClass(auto_lnk,'bx-core-autosave-ready');
		BX.addClass(auto_lnk,'bx-core-autosave-saving');

		// not oBlogLHE!!
		if (! window.oBlogLHE) return;

		form_data[controlID+'_type'] = window.oBlogLHE.sEditorMode;
		var text = "";
		if (window.oBlogLHE.sEditorMode == 'code')
			text = window.oBlogLHE.GetCodeEditorContent();
		else
			text = window.oBlogLHE.GetEditorContent();
		form_data[controlID] = text;
		form_data[titleID] = BX(titleID).value;
		form_data[tags] = BX(formId).TAGS.value;
	});

	BX.addCustomEvent(form, 'onAutoSaveFinished', function(ob, t) {
		t = parseInt(t);
		if (!isNaN(t))
		{
			setTimeout(function() {
				BX.removeClass(auto_lnk,'bx-core-autosave-saving');
				BX.addClass(auto_lnk,'bx-core-autosave-ready');
			}, 1000);
			auto_lnk.title = BX.message('AUTOSAVE_L').replace('#DATE#', BX.formatDate(new Date(t * 1000)));
		}
	});

	BX.addCustomEvent(form, 'onAutoSaveInit', function() {
		BX.removeClass(auto_lnk,'bx-core-autosave-ready');
		BX.addClass(auto_lnk,'bx-core-autosave-edited');
	});

	BX.addCustomEvent(form, 'onAutoSaveRestoreFound', function(ob, data) {
		var text = (BX.util.trim(data[controlID]) || ''),
			title = (BX.util.trim(data[titleID]) || '');
		if (text.length < 1 && title.length < 1) return;

		ob.Restore();
		// todo: need notify? see in socnetwork
	});

	BX.addCustomEvent(form, 'onAutoSaveRestore', function(ob, data) {
		if (!window.oBlogLHE || !data[controlID]) return;

		window.oBlogLHE.SetView(data[controlID+'_type']);

		if (!!window.oBlogLHE.sourseBut)
			window.oBlogLHE.sourseBut.Check((data[controlID+'_type'] == 'code'));
		if (data[controlID+'_type'] == 'code')
			window.oBlogLHE.SetContent(data[controlID]);
		else
			window.oBlogLHE.SetEditorContent(data[controlID]);
		BX(titleID).value = data[titleID];
		BX(formId).TAGS.value = data[tags];
				
		bindLHEEvents(ob);
	});

	BX.addCustomEvent(form, 'onAutoSaveRestoreFinished', function(ob, data) {
		if (!! recoverNotify)
			BX.remove(recoverNotify);
	});
}

function blogShowFile()
{
	BX.toggle(BX('blog-upload-file'));
	BX.onCustomEvent(BX('blog-post-user-fields-UF_BLOG_POST_DOC'), "BFileDLoadFormController");
}


var formParams = {},
	reinit = function(formID)
	{
		if (formParams[formID] && formParams[formID]["editorID"])
		{
			if (formParams[formID]["editor"])
				formParams[formID]["editor"](formParams[formID]['text']);
			else
				setTimeout(function(){reinit(formID);}, 50);
		}
	};

BX.BlogPostInit = function(formID, params)
{
	formParams = {};
	formParams[formID] = {
		editorID : params['editorID'],
		showTitle : (!!params['showTitle']),
		submitted : false,
		text : params['text'],
		autoSave : params['autoSave'],
		handler : (window.LHEPostForm && window.LHEPostForm.getHandler(params['editorID'])),
		editor : (window.LHEPostForm && window.LHEPostForm.getEditor(params['editorID'])),
		restoreAutosave : !!params['restoreAutosave']
	};
	var onHandlerInited = function(obj, form) {
			if (form == formID)
			{
				formParams[formID]["handler"] = obj;
				// BX.addCustomEvent(obj.eventNode, 'OnControlClick', function() {window.SBPETabs.changePostFormTab('message');});
				var OnAfterShowLHE = function()
					{
						var div = [BX('feed-add-post-form-notice-blockblogPostForm'),
							BX('feed-add-buttons-blockblogPostForm'),
							BX('feed-add-post-content-message-add-ins')];
						for (var ii = 0; ii < div.length; ii++)
						{
							if (!!div[ii])
							{
								BX.adjust(div[ii], { style : { display : "block", height : "auto", opacity : 1 } } );
							}
						}
						// if(formParams[formID]["showTitle"])
						// 	window['showPanelTitle_' + formID](true, false);
					},
					OnAfterHideLHE = function()
					{
						var ii,
							div = [
								BX('feed-add-post-form-notice-blockblogPostForm'),
								BX('feed-add-buttons-blockblogPostForm'),
								BX('feed-add-post-content-message-add-ins')];
						for (ii = 0; ii < div.length; ii++)
						{
							if (!!div[ii])
							{
								BX.adjust(div[ii], {style:{display:"block",height:"0px", opacity:0}});
							}
						}
						if(formParams[formID]["showTitle"])
							window['showPanelTitle_' + formID](false, false);
					};
				BX.addCustomEvent(obj.eventNode, 'OnAfterShowLHE', OnAfterShowLHE);
				BX.addCustomEvent(obj.eventNode, 'OnAfterHideLHE', OnAfterHideLHE);
				if (obj.eventNode.style.display == 'none')
					OnAfterHideLHE();
				else
					OnAfterShowLHE();
			}
		},

		onEditorInited = function(editor)
		{
			if (editor.id == formParams[formID]["editorID"])
			{
				formParams[formID]["editor"] = editor;
				if(formParams[formID]["autoSave"] != "N")
					new BlogPostAutoSave(formParams[formID]["autoSave"], formParams[formID]["restoreAutosave"]);

				var
					f = window[editor.id + 'Files'],
					handler = window.LHEPostForm.getHandler(editor.id),
					intId, id, node, needToReparse = [],
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
				var closure = function(a, b) { return function() { a.insertFile(b); } },
					closure2 = function(a, b, c) { return function() {
						if (controller)
						{
							controller.deleteFile(b, {});
							BX.remove(BX('wd-doc' + b));
							BX.ajax({ method: 'GET', url: c});
						}
						else
						{
							a.deleteFile(b, c, a, {controlID : 'common'});
						}
					} };

				for (intId in f)
				{
					if (f.hasOwnProperty(intId))
					{
						if (controller)
						{
							controller.addFile(f[intId]);
						}
						else
						{
							id = handler.checkFile(intId, "common", f[intId]);
							needToReparse.push(intId);
							if (!!id && BX('wd-doc'+intId) && !BX('wd-doc'+intId).hasOwnProperty("bx-bound"))
							{
								BX('wd-doc'+intId).setAttribute('bx-bound', 'Y');
								if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-img-wrap'}, true, false)) && node)
								{
									BX.bind(node, "click", closure(handler, id));
									node.style.cursor = "pointer";
								}
								if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-img-title'}, true, false)) && node)
								{
									BX.bind(node, "click", closure(handler, id));
									node.style.cursor = "pointer";
								}
								if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-post-del-but'}, true, false)) && node)
								{
									BX.bind(node, "click", closure2(handler, intId, f[intId]['del_url']));
									node.style.cursor = "pointer";
								}
							}
						}
						if ((node = BX.findChild(BX('wd-doc'+intId), {className: 'feed-add-post-del-but'}, true, false)) && node)
						{
							BX.bind(node, "click", closure2(handler, intId, f[intId]['del_url']));
							node.style.cursor = "pointer";
						}
					}
				}

				if (needToReparse.length > 0)
				{
					editor.SaveContent();
					var content = editor.GetContent();
					content = content.replace(new RegExp('\\&\\#91\\;IMG ID=(' + needToReparse.join("|") + ')([WIDTHHEIGHT=0-9 ]*)\\&\\#93\\;','gim'), '[IMG ID=$1$2]');
					editor.SetContent(content);
					editor.Focus();
				}
			}
		},

		onEditorInitedBefore = function(editor)
		{
			// add style for cut-image
			var cutCss = "\nimg.bxed-cut{background: transparent url('/bitrix/images/blog/editor/cut_image.gif') left top repeat-x; margin: 2px; width: 100%; height: 12px;}\n";
			if(editor.iframeCssText != undefined && editor.iframeCssText.length > 0)
				editor.iframeCssText += cutCss;
			else
				editor.iframeCssText = cutCss;

			editor.AddButton({
				id : 'cut',
				name : BX.message.CutTitle,
				iconClassName : 'cut',
				disabledForTextarea : false,
				src : '/bitrix/images/blog/editor/cut_button.png',
				toolbarSort : 205,
				handler : function()
				{
					var
						_this = this,
						res = false;

					// Iframe
					if (!_this.editor.bbCode || !_this.editor.synchro.IsFocusedOnTextarea())
					{
						var cutImg = '<img id="' + editor.SetBxTag(false, {tag: "cut"}) + '" class="bxed-cut" src="' + editor.EMPTY_IMAGE_SRC + '" title="' + BX.message.CutTitle + '">';
						res = _this.editor.action.actions.insertHTML.exec("insertHTML", cutImg);
					}
					else // bbcode + textarea
					{
						res = _this.editor.action.actions.formatBbCode.exec('formatBbCode', {tag: 'CUT', 'singleTag' : true});
					}
					return res;
				}
			});

			editor.AddParser({
				name : 'cut',
				obj : {
					Parse: function(parserName, content)
					{
						content = content.replace(/\[cut\]/gi,
							function(str, id, name)
							{
								var cutImg = '<img id="' + editor.SetBxTag(false, {tag: "cut"}) + '" class="bxed-cut" src="' + editor.EMPTY_IMAGE_SRC + '" title="' + BX.message.CutTitle + '">';
								return cutImg;
							});
						return content;
					},
					/**
					 * @return {string}
					 */
					UnParse: function(bxTag, oNode)
					{
						if (bxTag.tag == 'cut')
							return "[CUT]";
						else
							return "";
					}

				}
			});
		};


	BX.addCustomEvent(window, 'onInitialized', onHandlerInited);
	if (formParams[formID]["handler"])
		onHandlerInited(formParams[formID]["handler"], formID);
	BX.addCustomEvent(window, 'OnEditorInitedBefore', onEditorInitedBefore);
	if (formParams[formID]["editor"])
		onEditorInitedBefore(formParams[formID]["editor"]);
	BX.addCustomEvent(window, 'OnEditorInitedAfter', onEditorInited);
	if (formParams[formID]["editor"])
		onEditorInited(formParams[formID]["editor"]);

	BX.ready(function() {
		if (BX.browser.IsIE() && BX('POST_TITLE'))
		{
			var showTitlePlaceholderBlur = function(e)
			{
				if (!this.value || this.value == this.getAttribute("placeholder")) {
					this.value = this.getAttribute("placeholder");
					BX.removeClass(this, 'feed-add-post-inp-active');
				}
			};
			BX.bind(BX('POST_TITLE'), "blur", showTitlePlaceholderBlur);
			showTitlePlaceholderBlur.apply(BX('POST_TITLE'));
			BX('POST_TITLE').__onchange = BX.delegate(
				function(e) {
					if ( this.value == this.getAttribute("placeholder") ) { this.value = ''; }
					if ( this.className.indexOf('feed-add-post-inp-active') < 0 ) { BX.addClass(this, 'feed-add-post-inp-active'); }
				},
				BX('POST_TITLE')
			);
			BX.bind(BX('POST_TITLE'), "click", BX('POST_TITLE').__onchange);
			BX.bind(BX('POST_TITLE'), "keydown", BX('POST_TITLE').__onchange);
			BX.bind(BX('POST_TITLE').form, "submit", function(){if(BX('POST_TITLE').value == BX('POST_TITLE').getAttribute("placeholder")){BX('POST_TITLE').value='';}});
		}
	});
};