;(function(){
	if (window["BXPostFormTags"])
		return;
var repo = {
	selector : {}
};

window.BXPostFormTags = function(formID, buttonID)
{
	this.popup = null;
	this.formID = formID;
	this.buttonID = buttonID;
	this.sharpButton = null;
	this.addNewLink = null;
	this.tagsArea = null;
	this.hiddenField = null;
	this.popupContent = null;

	BX.ready(BX.proxy(this.init, this));
};

window.BXPostFormTags.prototype.init = function()
{
	this.sharpButton = BX(this.buttonID);
	this.addNewLink = BX("post-tags-add-new-" + this.formID);
	this.tagsArea = BX("post-tags-block-" + this.formID);
	this.tagsContainer = BX("post-tags-container-" + this.formID);
	this.hiddenField = BX("post-tags-hidden-" + this.formID);
	this.popupContent = BX("post-tags-popup-content-" + this.formID);
	this.popupInput = BX.findChild(this.popupContent, { tag : "input" });

	var tags = BX.findChildren(this.tagsContainer, { className : "feed-add-post-del-but" }, true);
	for (var i = 0, cnt = tags.length; i < cnt; i++ )
	{
		BX.bind(tags[i], "click", BX.proxy(this.onTagDelete, {
			obj : this,
			tagBox : tags[i].parentNode,
			tagValue : tags[i].parentNode.getAttribute("data-tag")
		}));
	}

	BX.bind(this.sharpButton, "click", BX.proxy(this.onButtonClick, this));
	BX.bind(this.addNewLink, "click", BX.proxy(this.onAddNewClick, this));
};

window.BXPostFormTags.prototype.onTagDelete = function()
{
	BX.remove(this.tagBox);
	this.obj.hiddenField.value = this.obj.hiddenField.value.replace(this.tagValue + ',', '').replace('  ', ' ');
};

window.BXPostFormTags.prototype.show = function()
{
	if (this.popup === null)
	{
		this.popup = new BX.PopupWindow("bx-post-tag-popup", this.addNewLink, {
			content : this.popupContent,
			lightShadow : false,
			offsetTop: 8,
			offsetLeft: 10,
			autoHide: true,
			angle : true,
			closeByEsc: true,
			zIndex: -840,
			buttons: [
				new BX.PopupWindowButton({
					text : BX.message("TAG_ADD"),
					events : {
						click : BX.proxy(this.onTagAdd, this)
					}
				})
			]
		});

		BX.bind(this.popupInput, "keydown", BX.proxy(this.onKeyPress, this));
		BX.bind(this.popupInput, "keyup", BX.proxy(this.onKeyPress, this));
	}

	this.popup.show();
	BX.focus(this.popupInput);
};

window.BXPostFormTags.prototype.addTag = function(tagStr)
{
	var tags = BX.type.isNotEmptyString(tagStr) ? tagStr.split(",") : this.popupInput.value.split(",");
	var result = [];
	for (var i = 0; i < tags.length; i++ )
	{
		var tag = BX.util.trim(tags[i]);
		if(tag.length > 0)
		{
			var allTags = this.hiddenField.value.split(",");
			if(!BX.util.in_array(tag, allTags))
			{
				var newTagDelete;
				var newTag = BX.create("span", {
					children : [
						(newTagDelete = BX.create("span", { attrs : { "class": "feed-add-post-del-but" }}))
					],
					attrs : { "class": "feed-add-post-tags" }
				});

				newTag.insertBefore(document.createTextNode(tag), newTagDelete);
				this.tagsContainer.insertBefore(newTag, this.addNewLink);

				BX.bind(newTagDelete, "click", BX.proxy(this.onTagDelete, {
					obj : this,
					tagBox : newTag,
					tagValue : tag
				}));

				this.hiddenField.value += tag + ',';

				result.push(tag);
			}
		}
	}

	return result;
};

window.BXPostFormTags.prototype.onTagAdd = function()
{
	this.addTag();
	this.popupInput.value = "";
	this.popup.close();
};

window.BXPostFormTags.prototype.onAddNewClick = function(event)
{
	event = event || window.event;
	this.show();
	BX.PreventDefault(event);
};

window.BXPostFormTags.prototype.onButtonClick = function(event)
{
	event = event || window.event;
	BX.show(this.tagsArea);
	this.show();
	BX.PreventDefault(event);
};

window.BXPostFormTags.prototype.onKeyPress = function(event)
{
	event = event || window.event;
	var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null));
	if (key == 13)
	{
		setTimeout(BX.proxy(this.onTagAdd, this), 0);
	}
};

window.BXPostFormImportant = function(formID, buttonID, inputName)
{
	if (inputName)
	{
		this.formID = formID;
		this.buttonID = buttonID;
		this.inputName = inputName;

		this.fireButton = null;
		this.activeBlock = null;
		this.hiddenField = null;

		BX.ready(BX.proxy(this.init, this));
	}

	return false;
};
window.BXPostFormImportant.prototype.init = function()
{
	this.fireButton = BX(this.buttonID);
	this.activeBlock = BX(this.buttonID + '-active');

	var form = BX(this.formID);
	if (form)
	{
		this.hiddenField = form[this.inputName];
		if (
			this.hiddenField
			&& this.hiddenField.value == 1
		)
		{
			this.showActive();
		}
	}

	BX.bind(this.fireButton, "click", BX.proxy(function(event) {
		event = event || window.event;
		this.showActive();
		BX.PreventDefault(event);
	}, this));

	BX.bind(this.activeBlock, "click", BX.proxy(function(event) {
		event = event || window.event;
		this.hideActive();
		BX.PreventDefault(event);
	}, this));
};
window.BXPostFormImportant.prototype.showActive = function(event)
{
	BX.hide(this.fireButton);
	BX.show(this.activeBlock, 'inline-block');

	if (this.hiddenField)
	{
		this.hiddenField.value = 1;
	}

	return false;
};
window.BXPostFormImportant.prototype.hideActive = function(event)
{
	BX.hide(this.activeBlock);
	BX.show(this.fireButton, 'inline-block');

	if (this.hiddenField)
	{
		this.hiddenField.value = 0;
	}

	return false;
};

var lastWaitElement = null;
window.MPFbuttonShowWait = function(el)
{
	if (el && !BX.type.isElementNode(el))
		el = null;
	el = el || this;
	el = (el ? (el.tagName == "A" ? el : el.parentNode) : el);
	if (el)
	{
		BX.addClass(el, "ui-btn-clock");
		lastWaitElement = el;
		BX.defer(function(){el.disabled = true})();
	}
};

var MPFMention = {
	listen: false,
	plus : false,
	text : '',
	bSearch: false,
	node: null,
	mode: null
};
BX.addCustomEvent(window, 'onInitialized', function(someObject) {
	if (someObject && someObject.eventNode)
	{
		BX.onCustomEvent(someObject.eventNode, 'OnClickCancel', function(){
			MPFMention.node = null;
		});
	}
});
window.onKeyDownHandler = function(e, editor, formID)
{
	var keyCode = e.keyCode;

	if (!window['BXfpdStopMent' + formID])
	{
		return true;
	}

	var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');

	if (
		keyCode === editor.KEY_CODES['backspace']
		&& MPFMention.node
	)
	{
		var mentText = BX.util.trim(editor.util.GetTextContent(MPFMention.node));
		if (
			mentText === '+'
			|| mentText === '@'
			|| (
				MPFMention.mode == 'button'
				&& mentText.length == 1
			)
		)
		{
			window['BXfpdStopMent' + formID]();
		}
		else if (
			MPFMention.mode == 'button'
			&& mentText.length == 1
		)
		{
			window['BXfpdStopMent' + formID]();
		}
	}

	if (
		BX.util.in_array(keyCode, [ 107, 187 ])
		|| (
			(e.shiftKey || e.modifiers > 3)
			&& BX.util.in_array(keyCode, [ 50, 43, 61 ])
		)
		|| (
			e.altKey
			&& BX.util.in_array(keyCode, [ 76 ])
		) /* German @ == Alt + L*/
		|| (
			e.altKey
			&& e.ctrlKey
			&& BX.util.in_array(keyCode, [ 81 ])
			&& e.key === '@'
		) /* Win LA Spanish @ == Ctrl + Alt + Q */
		|| (
			e.altKey
			&& BX.util.in_array(keyCode, [ 71, 81 ])
			&& e.key === '@'
		) /* MacOS ES Spanish @ == Alt + G, MacOS LA Spanish @ = Alt + Q */
		|| (
			e.altKey
			&& BX.util.in_array(keyCode, [ 50 ])
			&& e.key === '@'
		) /* MacOS PT Portugal @ == Alt + 2 */
		|| (
			typeof e.getModifierState === 'function'
			&& !!e.getModifierState('AltGraph')
			&& BX.util.in_array(keyCode, [ 81, 50 ])
			&& typeof e.key !== 'undefined'
			&& e.key === '@'
		) /* Win German @ == AltGr + Q, Win Spanish @ == AltGr + 2 */
	)
	{
		setTimeout(function()
		{
			var
				range = editor.selection.GetRange(),
				doc = editor.GetIframeDoc(),
				txt = (range ? range.endContainer.textContent : ''),
				determiner = (txt ? txt.slice(range.endOffset - 1, range.endOffset) : ''),
				prevS = (txt ? txt.slice(range.endOffset - 2, range.endOffset-1) : '');

			if (
				(determiner == "@" || determiner == "+")
				&& (
					!prevS
					|| BX.util.in_array(prevS, ["+", "@", ",", "("])
					|| (
						prevS.length == 1
						&& BX.util.trim(prevS) === ""
					)
				)
			)
			{
				MPFMention.listen = true;
				MPFMention.listenFlag = true;
				MPFMention.text = '';
				MPFMention.leaveContent = true;
				MPFMention.mode = 'plus';

				range.setStart(range.endContainer, range.endOffset - 1);
				range.setEnd(range.endContainer, range.endOffset);
				editor.selection.SetSelection(range);
				MPFMention.node = BX.create("SPAN", {props: {id: "bx-mention-node"}}, doc);
				editor.selection.Surround(MPFMention.node, range);
				range.setStart(MPFMention.node, 1);
				range.setEnd(MPFMention.node, 1);
				editor.selection.SetSelection(range);

				if (BX.type.isNotEmptyString(selectorId))
				{
					BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
						id: selectorId,
						bindPosition: getMentionNodePosition(MPFMention.node, editor)
					}]);
				}
			}
		}, 10);
	}

	if(MPFMention.listen)
	{
		var selectorInstance = null;

		if (BX.type.isNotEmptyString(selectorId))
		{
			selectorInstance = BX.UI.SelectorManager.instances[selectorId];
		}

		var navigationResult = false;
		if (selectorInstance)
		{
			navigationResult = selectorInstance.getNavigationInstance().checkKeyboardNavigation({
				keyCode: keyCode,
				tab: (
					MPFMention.bSearch
						? 'search'
						: (selectorInstance ? selectorInstance.tabs.selected : false)
				)
			});
		}

		if (navigationResult)
		{
			editor.iframeKeyDownPreventDefault = true;
			e.stopPropagation();
			e.preventDefault();
		}
	}

	if (
		!MPFMention.listen
		&& MPFMention.listenFlag
		&& keyCode === editor.KEY_CODES["enter"]
	)
	{
		var range = editor.selection.GetRange();
		if (range.collapsed)
		{
			var
				node = range.endContainer,
				doc = editor.GetIframeDoc();

			if (node)
			{
				if (node.className !== 'bxhtmled-metion')
				{
					node = BX.findParent(node, function(n)
					{
						return n.className == 'bxhtmled-metion';
					}, doc.body);
				}

				if (node && node.className == 'bxhtmled-metion')
				{
					editor.selection.SetAfter(node);
				}
			}
		}
	}
};

window.onKeyUpHandler = function(e, editor, formID)
{
	var
		keyCode = e.keyCode,
		range, mentText;

	if (!window['BXfpdStopMent' + formID])
		return true;

	if(MPFMention.listen === true)
	{
		if(keyCode == editor.KEY_CODES["escape"]) //ESC
		{
			window['BXfpdStopMent' + formID]();
		}
		else if(
			keyCode !== editor.KEY_CODES["enter"]
			&& keyCode !== editor.KEY_CODES["left"]
			&& keyCode !== editor.KEY_CODES["right"]
			&& keyCode !== editor.KEY_CODES["up"]
			&& keyCode !== editor.KEY_CODES["down"]
		)
		{
			if (BX(MPFMention.node))
			{
				mentText = BX.util.trim(editor.util.GetTextContent(MPFMention.node));
				var mentTextOrig = mentText;

				mentText = mentText.replace(/^[\+@]*/, '');
				MPFMention.bSearch = (mentText.length > 0);

				var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');
				if (BX.type.isNotEmptyString(selectorId))
				{
					var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
					if (BX.type.isNotEmptyObject(selectorInstance))
					{
						selectorInstance.getSearchInstance().runSearch({
							text: mentText
						});
					}
				}

				if (MPFMention.leaveContent && MPFMention._lastText && mentTextOrig === '')
				{
					window['BXfpdStopMent' + formID]();
				}
				else if (MPFMention.leaveContent && MPFMention.lastText && mentTextOrig !== '' && mentText === '')
				{
					MPFMention.bSearch = false;
					window['BXfpdStopMent' + formID]();

					if (BX.type.isNotEmptyString(selectorId))
					{
						BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
							id: selectorId,
							bindPosition: getMentionNodePosition(MPFMention.node, editor)
						}]);
					}
				}

				MPFMention.lastText = mentText;
				MPFMention._lastText = mentTextOrig;
			}
			else
			{
				window['BXfpdStopMent' + formID]();
			}
		}
	}
	else
	{
		if (
			!e.shiftKey &&
			(keyCode === editor.KEY_CODES["space"] ||
			keyCode === editor.KEY_CODES["escape"] ||
			keyCode === 188 ||
			keyCode === 190
			))
		{
			range = editor.selection.GetRange();
			if (range.collapsed)
			{
				var
					node = range.endContainer,
					doc = editor.GetIframeDoc();

				if (node)
				{
					if (node.className !== 'bxhtmled-metion')
					{
						node = BX.findParent(node, function(n)
						{
							return n.className == 'bxhtmled-metion';
						}, doc.body);
					}

					if (node && node.className == 'bxhtmled-metion')
					{
						mentText = editor.util.GetTextContent(node);
						var matchSep = mentText.match(/[\s\.\,]$/);
						if (matchSep || keyCode === editor.KEY_CODES["escape"])
						{
							node.innerHTML = mentText.replace(/[\s\.\,]$/, '');
							var sepNode = BX.create('SPAN', {html: matchSep || editor.INVISIBLE_SPACE}, doc);
							editor.util.InsertAfter(sepNode, node);
							editor.selection.SetAfter(sepNode);
						}
					}
				}
			}
		}
	}
};

window.onTextareaKeyDownHandler = function(e, editor, formID)
{
	var keyCode = e.keyCode;

	if(MPFMention.listen && keyCode == editor.KEY_CODES["enter"])
	{
		var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');
		if (BX.type.isNotEmptyString(selectorId))
		{
			var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
			if (BX.type.isNotEmptyObject(selectorInstance))
			{
				selectorInstance.getNavigationInstance().selectFirstItem({
					tab: (MPFMention.bSearch ? 'search' : selectorInstance.tabs.selected)
				});
			}
		}

		editor.textareaKeyDownPreventDefault = true;
		e.stopPropagation();
		e.preventDefault();
	}
};

window.onTextareaKeyUpHandler = function(e, editor, formID)
{
	var
		cursor, value,
		keyCode = e.keyCode;

	var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');

	if(MPFMention.listen === true)
	{
		if(keyCode == 27) //ESC
		{
			window['BXfpdStopMent' + formID]();
		}
		else if(keyCode !== 13)
		{
			value = editor.textareaView.GetValue(false);
			cursor = editor.textareaView.GetCursorPosition();

			if (value.indexOf('+') !== -1 || value.indexOf('@') !== -1)
			{
				var
					valueBefore = value.substr(0, cursor),
					charPos = Math.max(valueBefore.lastIndexOf('+'), valueBefore.lastIndexOf('@'));

				if (charPos >= 0)
				{
					var
						mentText = valueBefore.substr(charPos),
						mentTextOrig = mentText;

					mentText = mentText.replace(/^[\+@]*/, '');
					MPFMention.bSearch = (mentText.length > 0);

					var selectorInstance = null;
					if (BX.type.isNotEmptyString(selectorId))
					{
						selectorInstance = BX.UI.SelectorManager.instances[selectorId];
					}

					if (BX.type.isNotEmptyObject(selectorInstance))
					{
						selectorInstance.getSearchInstance().runSearch({
							text: mentText
						});
					}

					if (
						MPFMention.leaveContent
						&& MPFMention._lastText
						&& (
							mentTextOrig === ''
							|| mentText === ''
						)
					)
					{
						window['BXfpdStopMent' + formID]();
					}

					MPFMention.lastText = mentText;
					MPFMention._lastText = mentTextOrig;
				}
			}
		}
	}
	else
	{
		if (keyCode == 16)
		{
			var _this = this;
			this.shiftPressed = true;
			if (this.shiftTimeout)
				this.shiftTimeout = clearTimeout(this.shiftTimeout);

			this.shiftTimeout = setTimeout(function()
			{
				_this.shiftPressed = false;
			}, 100);
		}

		if (keyCode == 107 || (e.shiftKey || e.modifiers > 3 || this.shiftPressed) &&
			BX.util.in_array(keyCode, [187, 50, 107, 43, 61]))
		{
			cursor = editor.textareaView.element.selectionStart;
			if (cursor > 0)
			{
				value = editor.textareaView.element.value;
				var
					lastChar = value.substr(cursor - 1, 1);

				if (lastChar && (lastChar === '+' || lastChar === '@'))
				{
					MPFMention.listen = true;
					MPFMention.listenFlag = true;
					MPFMention.text = '';
					MPFMention.textarea = true;
					MPFMention.bSearch = false;
					MPFMention.mode = 'plus';

					if (BX.type.isNotEmptyString(selectorId))
					{
						BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
							id: selectorId
						}]);
					}
				}
			}
		}
	}
};

var getMentionNodePosition = function(mention, editor)
{
	var
		mentPos = BX.pos(mention),
		editorPos = BX.pos(editor.dom.areaCont),
		editorDocScroll = BX.GetWindowScrollPos(editor.GetIframeDoc()),
		top = editorPos.top + mentPos.bottom - editorDocScroll.scrollTop + 2,
		left = editorPos.left + mentPos.right - editorDocScroll.scrollLeft;

	return {top: top, left: left};
};

window.BxInsertMention = function (params)
{
	var
		item = params.item,
		type = params.type,
		formID = params.formID,
		editorId = params.editorId,
		bNeedComa = params.bNeedComa,
		editor = LHEPostForm.getEditor(editorId),
		spaceNode;

		if(
		(
			type === 'users'
			|| type === 'emailusers'
		)
		&& item
		&& item.entityId > 0
		&& editor
	)
	{
		if(editor.GetViewMode() == 'wysiwyg') // WYSIWYG
		{
			var
				doc = editor.GetIframeDoc(),
				range = editor.selection.GetRange(),
				mention = BX.create('SPAN',
					{
						props: {className: 'bxhtmled-metion'},
						text: BX.util.htmlspecialcharsback(item.name)
					}, doc);
				// &nbsp; - for chrome
			spaceNode = BX.create('SPAN', {html: (bNeedComa ? ',&nbsp;' : '&nbsp;')}, doc);

			editor.SetBxTag(mention, {tag: 'postuser', userId: item.entityId, userName: item.name, params: {value : item.entityId}});

			if (
				BX(MPFMention.node)
				&& MPFMention.node.parentNode
			)
			{
				editor.util.ReplaceNode(MPFMention.node, mention);
			}
			else
			{
				editor.selection.InsertNode(mention, range);
			}

			if (mention && mention.parentNode)
			{
				var parentMention = BX.findParent(mention, {className: 'bxhtmled-metion'}, doc.body);
				if (parentMention)
				{
					editor.util.InsertAfter(mention, parentMention);
				}
			}

			if (mention && mention.parentNode)
			{
				editor.util.InsertAfter(spaceNode, mention);
				editor.selection.SetAfter(spaceNode);
			}
		}
		else if (editor.GetViewMode() == 'code' && editor.bbCode) // BB Codes
		{
			editor.textareaView.Focus();

			var
				value = editor.textareaView.GetValue(false),
				cursor = editor.textareaView.GetCursorPosition(),
				valueBefore = value.substr(0, cursor),
				charPos = Math.max(valueBefore.lastIndexOf('+'), valueBefore.lastIndexOf('@'));

			if (charPos >= 0 && cursor > charPos)
			{
				editor.textareaView.SetValue(value.substr(0, charPos) + value.substr(cursor));
				editor.textareaView.element.setSelectionRange(charPos, charPos);
			}

			editor.textareaView.WrapWith(false, false, "[USER=" + item.entityId + "]" + item.name + "[/USER]" + (bNeedComa ? ', ' : ' '));
		}

		if (params.fireAddEvent === true)
		{
			BX.onCustomEvent(window, 'onMentionAdd', [ item ]);
		}

		var selectorId = window.MPFgetSelectorId('bx-mention-' + formID + '-id');
		if (BX.type.isNotEmptyString(selectorId))
		{
			var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
			if (BX.type.isNotEmptyObject(selectorInstance))
			{
				if (typeof selectorInstance.itemsSelected[item.id] != 'undefined')
				{
					delete selectorInstance.itemsSelected[item.id];
				}
			}
		}

		if (window['BXfpdStopMent' + formID])
		{
			window['BXfpdStopMent' + formID]();
		}

		MPFMention["text"] = '';

		if(editor.GetViewMode() == 'wysiwyg') // WYSIWYG
		{
			editor.Focus();
			editor.selection.SetAfter(spaceNode);
		}
	}
};

window.MPFgetSelectorId = function(formId)
{
	var result = false;
	var formNode = BX(formId);
	if (!formNode)
	{
		return result;
	}

	result = formNode.getAttribute('data-bx-selector-id');
	return result;
};

window.MPFMentionInit = function(formId, params)
{
	if (params.initDestination === true)
	{
		BX.addCustomEvent('onAutoSaveRestoreDestination', function(params) {

			if (
				BX.type.isNotEmptyObject(params)
				&& BX.type.isNotEmptyObject(params.data)
				&& BX.type.isNotEmptyString(params.data.DEST_DATA)
				&& BX.type.isNotEmptyString(params.formId)
				&& params.formId == formId
				&& BX.UI.EntitySelector
			)
			{
				var destData = JSON.parse(params.data.DEST_DATA);
				if (!Array.isArray(destData))
				{
					return;
				}

				var selectorInstance = BX.UI.EntitySelector.Dialog.getById('oPostFormLHE_blogPostForm');
				if (!BX.type.isNotEmptyObject(selectorInstance))
				{
					return;
				}

				selectorInstance.preselectedItems = destData;
				selectorInstance.setPreselectedItems(destData);
			}
		});

		BX.addCustomEvent(window, "onMentionAdd", function(item) {

			var selectorInstance = BX.UI.EntitySelector.Dialog.getById('oPostFormLHE_blogPostForm');
			if (!BX.type.isNotEmptyObject(selectorInstance))
			{
				return;
			}

			var entityType = 'employee';
			if (item.isExtranet === 'Y')
			{
				entityType = 'extranet';
			}
			else if (item.isEmail === 'Y')
			{
				entityType = 'email';
			}

			selectorInstance.addItem({
				avatar: item.avatar,
				customData: {
					email: item.email
				},
				entityId: 'user',
				entityType: entityType,
				id: item.entityId,
				title: item.name
			}).select();
		});
	}

	window["BXfpdSelectCallbackMent" + formId] = function(callbackParams) // item, type, search
	{
		window.BxInsertMention({
			item: callbackParams.item,
			type: callbackParams.entityType.toLowerCase(),
			formID: formId,
			editorId: params.editorId,
			fireAddEvent: params.initDestination
		});
	};

	window["BXfpdStopMent" + formId] = function ()
	{
		var selectorId = window.MPFgetSelectorId('bx-mention-' + formId + '-id');
		if (BX.type.isNotEmptyString(selectorId))
		{
			var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
			if (BX.type.isNotEmptyObject(selectorInstance))
			{
				selectorInstance.closeAllPopups();
				selectorInstance.getSearchInstance().abortSearchRequest();
			}
		}
	};

	if (BX(formId))
	{
		BX.addCustomEvent(BX(formId), 'OnUCFormAfterShow', function(ucFormManager) {
			if (
				!BX.type.isNotEmptyObject(ucFormManager)
				|| !BX.type.isArray(ucFormManager.id)
				|| !BX.type.isNotEmptyString(ucFormManager.id[0])
			)
			{
				return;
			}

			var reg = new RegExp('EVENT\_(\\d+)','i'); // calendar test
			if (!reg.test(ucFormManager.id[0]))
			{
				return;
			}

			var selectorId = window.MPFgetSelectorId('bx-mention-' + formId + '-id');
			if (!BX.type.isNotEmptyString(selectorId))
			{
				return;
			}

			var selectorInstance = BX.UI.SelectorManager.instances[selectorId];
			if (!BX.type.isNotEmptyObject(selectorInstance))
			{
				return;
			}

			selectorInstance.bindOptions.zIndex = 2200;
		});
	}
	LHEPostForm.getHandlerByFormId(formId).exec(function() {
		var selectorId = window.MPFgetSelectorId('bx-mention-' + formId + '-id');

		if (selectorId)
		{
			BX.onCustomEvent(window, 'BX.MPF.MentionSelector:init', [{
				id: selectorId,
				openDialogWhenInit: false
			}]);
		}
	});

	BX.ready(function() {
			var ment = BX('bx-b-mention-' + formId);
			var selectorId = window.MPFgetSelectorId('bx-mention-' + formId + '-id');

			BX.bind(
				ment,
				"click",
				function(e)
				{
					if(MPFMention.listen !== true)
					{
						var
							editor = LHEPostForm.getEditor(params.editorId),
							doc = editor.GetIframeDoc();

						if(editor.GetViewMode() == 'wysiwyg' && doc)
						{
							MPFMention.listen = true;
							MPFMention.listenFlag = true;
							MPFMention.text = '';
							MPFMention.leaveContent = false;
							MPFMention.mode = 'button';

							var
								range = editor.selection.GetRange();

							if (BX(MPFMention.node))
							{
								BX.remove(BX(MPFMention.node));
							}
							editor.InsertHtml('<span id="bx-mention-node">' + editor.INVISIBLE_SPACE + '</span>', range);

							setTimeout(function()
							{
								if (selectorId)
								{
									BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
										id: selectorId,
										bindNode: ment
									}]);
								}

								MPFMention.node = doc.getElementById('bx-mention-node');
								if (MPFMention.node)
								{
									range.setStart(MPFMention.node, 0);
									if (
										MPFMention.node.firstChild
										&& MPFMention.node.firstChild.nodeType == 3
										&& MPFMention.node.firstChild.nodeValue.length > 0
									)
									{
										range.setEnd(MPFMention.node, 1);
									}
									else
									{
										range.setEnd(MPFMention.node, 0);
									}
									editor.selection.SetSelection(range);
								}

								editor.Focus();
							}, 100);
						}
						else if (editor.GetViewMode() == 'code')
						{
							MPFMention.listen = true;
							MPFMention.listenFlag = true;
							MPFMention.text = '';
							MPFMention.leaveContent = false;
							MPFMention.mode = 'button';

							// TODO: get current cusrsor position

							setTimeout(function()
							{
								if (selectorId)
								{
									BX.onCustomEvent(window, 'BX.MPF.MentionSelector:open', [{
										id: selectorId,
										bindNode: ment
									}]);
								}
							}, 100);
						}

						BX.onCustomEvent(ment, 'mentionClick');
					}
				}
			);
		}
	);
};

window.BXfpdOnDialogOpen = function ()
{
	MPFMention.listen = true;
	MPFMention.listenFlag = true;
};

window.BXfpdOnDialogClose = function (params)
{
	MPFMention.listen = false;

	setTimeout(function()
	{
		MPFMention.listenFlag = false;
		if (!MPFMention.listen)
		{
			var editor = LHEPostForm.getEditor(params.editorId);
			if(editor)
			{
				var
					doc = editor.GetIframeDoc();

				if (BX(MPFMention.node))
				{
					editor.selection.SetAfter(MPFMention.node);
					if (MPFMention.leaveContent)
					{
						editor.util.ReplaceWithOwnChildren(MPFMention.node);
					}
					else
					{
						BX.remove(BX(MPFMention.node));
					}
				}
				editor.Focus();
			}
		}
	}, 100);
};


	MPFEntitySelector = function(params)
	{
		this.selector = null;
		this.inputNode = null;
		this.messages = {};

		if (!BX.type.isNotEmptyString(params.id))
		{
			return null;
		}

		if (repo.selector[params.id])
		{
			return repo.selector[params.id];
		}

		repo.selector[params.id] = this.init(params);
	};

	MPFEntitySelector.prototype.init = function(params)
	{
		if (!BX.type.isPlainObject(params))
		{
			params = {};
		}

		if (
			!BX.type.isNotEmptyString(params.id)
			|| !BX.type.isNotEmptyString(params.tagNodeId)
			|| !BX(params.tagNodeId)
		)
		{
			return null;
		}

		if (
			BX.type.isNotEmptyString(params.inputNodeId)
			&& BX(params.inputNodeId)
		)
		{
			this.inputNode = BX(params.inputNodeId);
		}

		if (BX.type.isNotEmptyObject(params.messages))
		{
			this.messages = params.messages;
		}

		this.selector = new BX.UI.EntitySelector.TagSelector({

			id: params.id,
			dialogOptions: {
				id: params.id,
				context: (BX.type.isNotEmptyString(params.context) ? params.context : null),

				preselectedItems: (BX.type.isArray(params.preselectedItems) ? params.preselectedItems : []),

				events: {
					'Item:onSelect': function() {
						this.recalcValue(this.selector.getDialog().getSelectedItems());
					}.bind(this),
					'Item:onDeselect': function() {
						this.recalcValue(this.selector.getDialog().getSelectedItems());
					}.bind(this)
				},
				entities: [
					{
						id: 'meta-user',
						options: {
							'all-users': {
								title: this.messages.allUsersTitle,
								allowView: (
									BX.type.isBoolean(params.allowToAll)
									&& params.allowToAll
								)
							}
						}
					},
					{
						id: 'user',
						options: {
							emailUsers: (BX.type.isBoolean(params.allowSearchEmailUsers) ? params.allowSearchEmailUsers : false),
							inviteGuestLink: (BX.type.isBoolean(params.allowSearchEmailUsers) ? params.allowSearchEmailUsers : false),
							myEmailUsers: true
						}
					},
					{
						id: 'project',
						options: {
							features: {
								blog:  [ 'premoderate_post', 'moderate_post', 'write_post', 'full_post' ]
							}
						}
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersAndDepartments',
							allowFlatDepartments: false,
						}
					}
				]
			},
			addButtonCaption: BX.message('BX_FPD_LINK_1'),
			addButtonCaptionMore: BX.message('BX_FPD_LINK_2')
		});

		this.selector.renderTo(document.getElementById(params.tagNodeId));

		return this.selector;
	};

	MPFEntitySelector.prototype.recalcValue = function(selectedItems)
	{
		if (
			!BX.type.isArray(selectedItems)
			|| !this.inputNode
		)
		{
			return;
		}

		var result = [];

		selectedItems.forEach(function(item) {
			result.push([ item.entityId, item.id ]);
		});

		this.inputNode.value = JSON.stringify(result);
	};

	window.MPFEntitySelector = MPFEntitySelector;

})();