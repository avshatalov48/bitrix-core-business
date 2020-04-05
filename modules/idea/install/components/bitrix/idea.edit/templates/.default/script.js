;(function(window){
	BX.Idea = (!!BX.Idea ? BX.Idea : {});
	BX.Idea.obj = (!!BX.Idea.obj ? BX.Idea.obj : {});
	if (!!BX.Idea["customizeEditor"] || !!top.BX.Idea["customizeEditor"])
		return;

	BX.Idea.customizeEditor = function(id)
	{
		BX.addCustomEvent(window, 'OnEditorInitedBefore', BX.Idea.onEditorInitedBefore);
	};

	BX.Idea.onEditorInitedBefore = function(editor)
	{
		// add style for cut-image
		var cutCss = "\nimg.bxed-cut{background: transparent url('/bitrix/images/blog/editor/cut_image.gif') left top repeat-x; margin: 2px; width: 100%; height: 12px;}\n";
		if(editor.iframeCssText.length > 0)
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

})(window);
