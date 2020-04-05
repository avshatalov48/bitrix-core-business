window.ForumFormAutosave = function (params) {
	var formID = params.formID || null;
	if (!formID) return;

	var form = BX(formID);
	if (!form) return;

	var controlID = params.controlID || "POST_MESSAGE";
	var	iconClass = params.iconClass  || "postFormAutosave";
	var	actionClass = params.actionClass || "postFormAutorestore";
	var	actionText = params.actionText || BX.message('AUTOSAVE_R');
	var recoverMessage = params.recoverMessage || '';
	var recoverNotify = null;

	var auto_lnk = BX.create('A', {
		'attrs': {'href': 'javascript:void(0)'},
		'props': {
			'className': iconClass+' bx-core-autosave bx-core-autosave-ready',
			'title': BX.message('AUTOSAVE_T')
		}
	});
	var restore_lnk = null;
	var formHeaders = BX.findChild(form, {'className': /forum-reply-header|reviews-reply-header|comments-reply-header/ }, true, true);
	if (typeof formHeaders == 'undefined' || formHeaders === null || formHeaders.length < 1)
		return false;
	var formHeader = formHeaders[formHeaders.length-1];
	if (!!formHeader)
		formHeader.insertBefore(auto_lnk, formHeader.children[0]);
	else
		form.insertBefore(auto_lnk, form.children[0]);

	var bindLHEEvents = function(_ob)
	{
		if (window.oLHE)
		{
			window.oLHE.fAutosave = _ob;
			BX.bind(window.oLHE.pEditorDocument, 'keydown', BX.proxy(_ob.Init, _ob));
			BX.bind(window.oLHE.pTextarea, 'keydown', BX.proxy(_ob.Init, _ob));
			BX.addCustomEvent(window.oLHE, 'OnChangeView', function(){
				if (!!this.fAutosave && this.sEditorMode == 'html'){
					BX.bind(this.pEditorDocument, 'keydown', BX.proxy(_ob.Init, _ob));
				}
			});
		}
		else if (window["LHEPostForm"] && window["LHEPostForm"]["getEditor"])
		{
			var editor = LHEPostForm.getEditor('POST_MESSAGE');
			editor.fAutosave = _ob;
			BX.addCustomEvent(editor, 'OnContentChanged', BX.proxy(_ob.Init, _ob));
		}
	};

	BX.addCustomEvent(form, 'onAutoSavePrepare', function (ob, h) {
		ob.DISABLE_STANDARD_NOTIFY = true;
		BX.bind(auto_lnk, 'click', BX.proxy(ob.Save, ob));
		_ob=ob;
		setTimeout(function() { bindLHEEvents(_ob) },1500);
	});

	BX.addCustomEvent(form, 'onAutoSave', function(ob, form_data) {
		BX.removeClass(auto_lnk,'bx-core-autosave-edited');
		BX.removeClass(auto_lnk,'bx-core-autosave-ready');
		BX.addClass(auto_lnk,'bx-core-autosave-saving');

		if (! window.oLHE) return;

		form_data[controlID+'_type'] = window.oLHE.sEditorMode;
		var text = "";
		if (window.oLHE.sEditorMode == 'code')
			text = window.oLHE.GetCodeEditorContent();
		else
			text = window.oLHE.GetEditorContent();
		form_data[controlID] = text;
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
		if (BX.util.trim(data[controlID]).length < 1) return;
		else if (form.children[1].className == "forum-notify-bar") return;

		_ob = ob;

		recoverNotify = BX.create('DIV', {
			'props': {
				'className': 'forum-notify-bar'
			},
			'children': [
				BX.create('DIV', {
					'props': { 'className': 'forum-notify-close' },
					'children': [
						BX.create('A', {
							'events':{
								'click': function() {
									if (!! recoverNotify)
										BX.remove(recoverNotify);
									return false;
								}
							}
						})
					]
				}),
				BX.create('DIV', {
					'props': { 'className': 'forum-notify-text' },
					'children': [
						BX.create('SPAN', { 'text': recoverMessage }),
						BX.create('A', {
							'attrs': {'href': 'javascript:void(0)'},
							'props': {'className': actionClass},
							'text': actionText,
							'events':{
								'click': function() { _ob.Restore(); return false;}
							}
						})
					]
				})
			]
		});

		form.insertBefore(recoverNotify, form.children[1]);
	});

	BX.addCustomEvent(form, 'onAutoSaveRestore', function(ob, data) {
		if (!window.oLHE || !data[controlID]) return;

		window.oLHE.SetView(data[controlID+'_type']);

		if (!!window.oLHE.sourseBut)
			window.oLHE.sourseBut.Check((data[controlID+'_type'] == 'code'));
		if (data[controlID+'_type'] == 'code')
			window.oLHE.SetContent(data[controlID]);
		else
			window.oLHE.SetEditorContent(data[controlID]);
		bindLHEEvents(ob);
	});

	BX.addCustomEvent(form, 'onAutoSaveRestoreFinished', function(ob, data) {
		if (!! recoverNotify)
			BX.remove(recoverNotify);
	});
}
BX.onCustomEvent(window, 'onScriptForumAutosaveLoaded', []);