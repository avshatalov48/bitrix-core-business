(function(window) {

if (BX.TagsWindowArea)
	return;

BX.TagsWindowArea = function(tags, params)
{
	this.objId = ++BX.TagsWindowArea.__conter;
	this.maxTagId = 0;
	this.mode = BX.TagsWindowArea.mode.defaultMode;

	this.tagsContainer = BX.create("div",  { props : { className : "popup-tags-content" + (BX.browser.IsIE() ? " popup-tags-content-ie" : "")  },
			html : '<table cellspacing="0"> \
				<tr><td class="popup-tags-left-top-cell"></td><td class="popup-tags-right-top-cell"></td></tr> \
				<tr><td colspan="2" class="popup-tags-middle-cell"><div class="popup-window-hr"><i></i></div></td></tr> \
				<tr><td class="popup-tags-left-bottom-cell"></td><td class="popup-tags-right-bottom-cell"></td></tr></table>'
	});

	this.newTagTexbox = BX.create("input", {
		props : { type : "text", value : "" },
		attrs: { "autocomplete" : "off" },
		events : {
			keydown : BX.proxy(this.__onKeydownTextbox, this ),
			keyup : BX.proxy(this.__onKeyupTextbox, this )
		}
	});

	this.newTagButton = BX.create("div",  {
		props : { className : "popup-tags-add-button" },
		attrs : { title : BX.message("TAGS_BUTTON_ADD_TITLE") },
		events : { click : BX.proxy(this.__onAddButtonClick, this ) }
	});

	this.content = BX.create(
		"div",
		{
			props : { className : "popup-tags-window", id : "task-tags-content" },
			children : [
				BX.create("div", { props : { className : "popup-tags-create-new" },
					children : [
						BX.create("div", { props : { className : "popup-tags-textbox" },
							children : [ this.newTagTexbox]
						}),
						this.newTagButton
					]
				}),
				BX.create("div", { props : { className : "popup-window-hr" }, children : [ BX.create("I", {}) ]}),
				BX.create("div", { props : { className : "popup-tags-content-wrapper" },  children : [this.tagsContainer]  })
			]
		}
	);

	var tableCells = this.tagsContainer.getElementsByTagName("td");
	this.leftTopCell =  tableCells[0];
	this.rightTopCell = tableCells[1];
	this.middleCell = tableCells[2];
	this.leftBottomCell = tableCells[3];
	this.rightBottomCell = tableCells[4];

	this.tags = [];
	this.renamedTags = [];
	this.deletedTags = [];
	for (var i = 0, length = tags ? tags.length : 0; i < length; i++)
		this.addTag(tags[i]);

	//events
	if (params && params.events)
	{
		for (var eventName in params.events)
			BX.addCustomEvent(this, eventName, params.events[eventName]);
	}

};

BX.TagsWindowArea.__conter = 0;
BX.TagsWindowArea.tagPosition =
{
	leftTop : 1,
	rightTop : 2,
	leftBottom : 3,
	rightBottom : 4
};
BX.TagsWindowArea.mode =
{
	defaultMode : 1,
	highlightMode : 2,
	editMode : 3
};

BX.TagsWindowArea.prototype.addTag = function(tag)
{
	if (!tag.name || !BX.type.isString(tag.name))
		return null;

	tag.name = BX.util.trim(tag.name);
	if (tag.name.length < 1 || this.indexOfTagName(tag.name) > -1)
		return null;

	tag.id = tag.id ? tag.id : ++this.maxTagId;
	tag.selected = tag.selected && tag.selected === true ? true : false;

	tag.markToDelete = false;
	tag.position = null;
	tag.editTextbox = null;

	this.tags.unshift(tag);

	return tag;
};

BX.TagsWindowArea.prototype.removeTag = function(tag)
{
	var index = this.indexOfTag(tag);
	if (index > -1)
		this.tags = BX.util.deleteFromArray(this.tags, index);
	return index > -1;
};

BX.TagsWindowArea.prototype.selectTag = function(tag, selected)
{
	var index = this.indexOfTag(tag);
	if (index > -1)
		this.tags[index].selected =  !!selected;
	return index > -1;
};

BX.TagsWindowArea.prototype.selectAllTags = function(selected, arTagsFilter)
{
	var i, length;
	selected = !!selected;
	if (BX.type.isArray(arTagsFilter))
	{
		for (i = 0, length = this.tags.length; i < length; i++)
			this.tags[i].selected = BX.util.in_array(this.tags[i], arTagsFilter) ? selected : !selected;
	}
	else
	{
		for (i = 0, length = this.tags.length; i < length; i++)
			this.tags[i].selected = selected;
	}
};

BX.TagsWindowArea.prototype.indexOfTag = function(tag)
{
	var index = -1;
	for (var i = 0, length = this.tags.length; i < length; i++)
	{
		if (this.tags[i] == tag)
		{
			index = i;
			break;
		}
	}

	return index;
};

BX.TagsWindowArea.prototype.indexOfTagName = function(tagName)
{
	var index = -1;
	for (var i = 0, length = this.tags.length; i < length; i++)
	{
		if (this.tags[i].name.toLowerCase() == tagName.toLowerCase())
		{
			index = i;
			break;
		}
	}

	return index;
};

BX.TagsWindowArea.prototype.getSelectedTags = function()
{
	var result = [];

	for (var i = 0, length = this.tags.length; i < length; i++)
	{
		if (this.tags[i].selected)
			result.push(this.tags[i]);
	}
	return result;
};

BX.TagsWindowArea.prototype.getTags = function()
{
	return this.tags;
};

BX.TagsWindowArea.prototype.sortTags = function()
{
	this.tags.sort(this.__sortTags);
};

BX.TagsWindowArea.prototype.__sortTags = function(a, b)
{
	var lowerA = a.name.toLowerCase();
	var lowerB = b.name.toLowerCase();
	if (lowerA < lowerB)
		return -1;
	else if (lowerA > lowerB)
	   return 1;

    return 0;
};

BX.TagsWindowArea.prototype.setMode = function(mode)
{
	if (mode == BX.TagsWindowArea.mode.defaultMode || mode == BX.TagsWindowArea.mode.highlightMode || mode == BX.TagsWindowArea.mode.editMode)
		this.mode = mode;
};

BX.TagsWindowArea.prototype.redraw = function(mode, params)
{
	if (mode)
		this.setMode(mode);

	if (this.mode == BX.TagsWindowArea.mode.highlightMode)
	{
	    this._disableAddButton(false);
		this._renderHighlight(params);
	}
	else if (this.mode == BX.TagsWindowArea.mode.editMode)
	{
		this._disableAddButton(true);
		this._render(params);
	}
	else
	{
		this._disableAddButton(false);
		this._render(params);
	}
};

BX.TagsWindowArea.prototype._disableAddButton = function(disabled)
{
	if (!!disabled)
	{
		BX.addClass(this.newTagButton, "popup-tags-add-button-disabled");
		this.newTagTexbox.disabled = true;
	}
	else
	{
		BX.removeClass(this.newTagButton, "popup-tags-add-button-disabled");
		this.newTagTexbox.disabled = false;
	}
};

BX.TagsWindowArea.prototype._render = function(params)
{
	this.rightTopCell.innerHTML = this.leftTopCell.innerHTML = this.rightBottomCell.innerHTML = this.leftBottomCell.innerHTML = "";

	var selectedCnt = 0;
	var unselectedCnt = 0;
	for (var i = 0, length = this.tags.length; i < length; i++)
	{
		var tagItem = this.createTagItem(this.tags[i]);
		if (this.tags[i].selected)
		{
			if (selectedCnt < 2)
				BX.addClass(tagItem, "popup-tags-item-first");

			if (selectedCnt % 2)
			{
				this.tags[i].position = BX.TagsWindowArea.tagPosition.rightTop;
				this.rightTopCell.appendChild(tagItem);
			}
			else
			{
				this.tags[i].position = BX.TagsWindowArea.tagPosition.leftTop;
				this.leftTopCell.appendChild(tagItem);
			}

			selectedCnt++;
		}
		else
		{
			if (unselectedCnt < 2)
				BX.addClass(tagItem, "popup-tags-item-first");

			if (unselectedCnt % 2)
			{
				this.tags[i].position = BX.TagsWindowArea.tagPosition.rightBottom;
				this.rightBottomCell.appendChild(tagItem);
			}
			else
			{
				this.tags[i].position = BX.TagsWindowArea.tagPosition.leftBottom;
				this.leftBottomCell.appendChild(tagItem);
			}

			unselectedCnt++;
		}
	}
	this.__resize(selectedCnt, unselectedCnt);
};


BX.TagsWindowArea.prototype.__resize = function(selectedCnt, unselectedCnt)
{
	this.tagsContainer.style.height = "auto";
	this.tagsContainer.style.overflowY = "visible";

	var ie7 = false;
	/*@cc_on
         @if (@_jscript_version <= 5.7)
             ie7 = true;
		/*@end
    @*/

	if (ie7 || (document.documentMode && document.documentMode <= 7))
		this.tagsContainer.style.paddingRight = "0";


	if (selectedCnt == 0 || unselectedCnt == 0)
	{
		this.middleCell.style.height = "1px";
		this.middleCell.style.visibility = "hidden";
	}
	else
	{
		this.middleCell.style.height = "19px";
		this.middleCell.style.visibility = "visible";
 	}

	if (this.tagsContainer.offsetHeight > 200)
	{
		this.tagsContainer.style.height = "200px";
		this.tagsContainer.style.overflowY = "scroll";


		if (ie7 || (document.documentMode && document.documentMode <= 7))
			this.tagsContainer.style.paddingRight = "20px";
	}
	else if ((selectedCnt == 0 && unselectedCnt == 0) || this.tagsContainer.offsetHeight < 40)
		this.tagsContainer.style.height = "40px";
};


BX.TagsWindowArea.prototype._renderHighlight = function(params)
{
	var word = params.word && BX.type.isString(params.word) ? BX.util.trim(params.word) :  "";
	if (word && word.lastIndexOf(",") > -1)
		word = BX.util.trim(word.substr(word.lastIndexOf(",") + 1));

	var firstWord = null;
	this.leftBottomCell.innerHTML = this.rightBottomCell.innerHTML = "";
	for (var i = 0, firstLeft = false, firstRight = false, length = this.tags.length; i < length; i++)
	{
		var tag = this.tags[i];
		if (!tag.position)
			continue;

		var tagItem = this.createTagItem(tag, word);

		if (tag.position == BX.TagsWindowArea.tagPosition.leftBottom)
		{
			if (!firstLeft)
				BX.addClass(tagItem, "popup-tags-item-first");
			firstLeft = true;

			this.leftBottomCell.appendChild(tagItem);

		}
		else if (tag.position == BX.TagsWindowArea.tagPosition.rightBottom)
		{
			if (!firstRight)
				BX.addClass(tagItem, "popup-tags-item-first");
			firstRight = true;
			this.rightBottomCell.appendChild(tagItem);
		}

		if (firstWord === null && BX.hasClass(tagItem, "popup-tags-item-highlight-mode"))
			firstWord = tagItem;
	}

	if (this.tagsContainer.offsetHeight == 200 && firstWord !== null)
		this.tagsContainer.scrollTop = firstWord.offsetTop;
};

BX.TagsWindowArea.prototype.saveEditTags = function()
{
	if (this.mode != BX.TagsWindowArea.mode.editMode)
		return;
	var newTags = [];

	for (var i = 0, length = this.tags.length; i < length; i++)
	{
		var tag = this.tags[i];
		if (tag.markToDelete)
		{
			this.deletedTags.push(tag);
			continue;
		}

		if (tag.editTextbox)
		{
			var newTagName = BX.util.trim(tag.editTextbox.value);
			if (BX.type.isNotEmptyString(newTagName) && newTagName != tag.name)
			{
				this.renamedTags[tag.name] = newTagName;
				tag.name = newTagName;
			}
		}

		newTags.push(tag);

	}
	this.tags = newTags;

};

BX.TagsWindowArea.prototype.cancelEdit = function()
{
	if (this.mode != BX.TagsWindowArea.mode.editMode)
		return;

	for (var i = 0, length = this.tags.length; i < length; i++)
		this.tags[i].markToDelete = false;
};

BX.TagsWindowArea.prototype.focusTextbox = function()
{
	this.newTagTexbox.value = "";
	BX.focus(this.newTagTexbox);
};

BX.TagsWindowArea.prototype.addTextboxTag = function()
{
	if (this.mode == BX.TagsWindowArea.mode.editMode)
		return null;

	var tagNames = BX.util.trim(this.newTagTexbox.value);
	if (tagNames.length < 1)
		return null;

	var result = [];
	var tags = tagNames.split(",");
	for (var i = 0; i < tags.length; i++ )
	{
		var tag = BX.util.trim(tags[i]);
		var index = this.indexOfTagName(tag);
		if (index < 0)
		{
			var newTag = this.addTag({name : tag, selected : true });
			if (newTag != null)
				result.push(newTag);
		}
		else
			this.tags[index].selected = true;
	}

	BX.onCustomEvent(this, "onTagCreate", [result]);

	this.redraw(BX.TagsWindowArea.mode.defaultMode);

	setTimeout(BX.proxy(this.focusTextbox, this), 0); //setTimeout for IE

	return result;
};

BX.TagsWindowArea.prototype.__onKeydownTextbox = function(event)
{
	if (!event)
		event = window.event;

	if (this.mode == BX.TagsWindowArea.mode.editMode)
		return;

	var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null));
    if (key == 13)
        this.addTextboxTag();
};

BX.TagsWindowArea.prototype.__onKeyupTextbox = function(event)
{
	if (!event)
		event = window.event;

	if (this.mode == BX.TagsWindowArea.mode.editMode)
		return;

	var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null));
    if (key !== 13)
	    this.redraw(BX.TagsWindowArea.mode.highlightMode, { word: this.newTagTexbox.value });

};

BX.TagsWindowArea.prototype.__onAddButtonClick = function(event)
{
	this.addTextboxTag();
};

BX.TagsWindowArea.prototype.__onTagClick = function(e)
{
	this.obj.selectTag(this.tag, !this.tag.selected);
	BX.onCustomEvent(this.obj, "onTagClick", [this.tag]);
	this.obj.focusTextbox();
};


BX.TagsWindowArea.prototype.__onTagDelete = function(e)
{
	this.tag.markToDelete = true;

	if (this.tag.editTextbox)
	{
		var div = this.tag.editTextbox.parentNode.parentNode;
		if (BX.hasClass(div, "popup-tags-item-first"))
		{
			var divs = div.parentNode.childNodes;
			if (divs.length > 1)
				BX.toggleClass(divs[1], "popup-tags-item-first");
			divs = null;
		}
		BX.remove(div);
	}

	var selectedCnt = 0;
	var unselectedCnt = 0;
	for (var i = 0, length = this.obj.tags.length; i < length; i++)
	{
		if (this.obj.tags[i].markToDelete === true)
			continue;

		if (this.obj.tags[i].selected)
			selectedCnt++;
		else
			unselectedCnt++;
	}

	this.obj.__resize(selectedCnt, unselectedCnt);

	BX.PreventDefault(e);
};

BX.TagsWindowArea.prototype.__onTagTextboxBlur = function(e)
{
	var textboxValue = BX.util.trim(this.tag.editTextbox.value);
	if (!BX.type.isNotEmptyString(textboxValue))
		this.tag.editTextbox.value = this.tag.name;
};

BX.TagsWindowArea.prototype.__wordwrap = function(text, length, separator, encode)
{
	var encodeText = encode !== false;
    var words = text.split(" ");
	for (var i = 0; i < words.length; i++)
	{
		var word = words[i];
		if (word.length > length)
		{
			var matches = word.match(new RegExp(".{0," + length + "}", "g"));
			for (var j = 0; j < matches.length; j++)
				matches[j] = encodeText ? BX.util.htmlspecialchars(matches[j]) : matches[j];
			words[i] = matches.join(separator);
		}
		else
			words[i] = encodeText ? BX.util.htmlspecialchars(words[i]) : words[i];
	}

	return words.join(" ");
};

BX.TagsWindowArea.prototype.__highlight = function(tag, highlightWord)
{
	var tagName = tag.name;
	tagName = this.__wordwrap(tagName, 13, ",", false);
	for (var i = 0, j = 0; i < tagName.length && j < highlightWord.length; i++)
	{
		if (tagName.charAt(i) == ",")
			continue;
		j++;
	}
	tagName = '<span class="popup-tags-item-highlighted">' + BX.util.htmlspecialchars(tagName.substr(0, i)) + '</span>' + BX.util.htmlspecialchars(tagName.substr(i));
	return tagName.replace(new RegExp(",", 'g'), "&#8203;");
};

BX.TagsWindowArea.prototype.createTagItem = function(tag, highlightWord)
{
	var tagName = tag.name;
	var tagId = "popup-tags-item-" + this.objId + "-" + tag.id;

	if (this.mode == BX.TagsWindowArea.mode.editMode)
	{
		var tagTextbox = BX.create("input", {
			props : { className: "popup-tags-item-texbox", type: "text", value : tagName },
			events : { blur : BX.proxy(this.__onTagTextboxBlur, {obj : this, tag : tag}) }
		});

		tag.editTextbox = tagTextbox;

		return BX.create("div", {
			props : { className : "popup-tags-item popup-tags-item-edit-mode" },
			children: [
				BX.create("div", {
					props : { className : "popup-tags-item-edit-mode-wrapper" },
					children: [

						/*BX.create("input", {
							props : { className: "popup-tags-item-checkbox", type: "checkbox", id : tagId, checked : tag.selected, defaultChecked : tag.selected,  disabled : true}
						}),*/

						tagTextbox,

						BX.create("a", {
							props : { className: "popup-tags-item-delete-icon", href : "" },
							attrs : { title : BX.message("TAGS_BUTTON_DELETE_TITLE") },
							events : { click : BX.proxy(this.__onTagDelete, {obj : this, tag : tag}) }
						})
					]
				})
			]
		});
	}
	else
	{
		var isHighlighted = false;
		if (highlightWord && !tag.selected && highlightWord.length <= tagName.length && tagName.substr(0, highlightWord.length).toLowerCase() === highlightWord.toLowerCase())
		{
			isHighlighted = true;
			tagName = this.__highlight(tag, highlightWord);
		}
		else
			tagName = this.__wordwrap(tagName, 13, "&#8203;");

		var className = "popup-tags-item" + (isHighlighted ? " popup-tags-item-highlight-mode" : " popup-tags-item-default-mode");
		if (BX.browser.IsIE())
			className += " popup-tags-item-ie";

		return BX.create("div", {
			props : { className : className },
			children: [
				BX.create("input", {
					props : { className: "popup-tags-item-checkbox", type: "checkbox", id : tagId, checked : tag.selected, defaultChecked : tag.selected },
					events : { click : BX.proxy(this.__onTagClick, {obj : this, tag : tag}) }
				}),
				BX.create("label", { props : { htmlFor : tagId }, html :  tagName })
			]
		});

	}
};

})(window);
/*=========================================================================*/

(function(window) {

var __windows = {};

BX.TagsWindow = {
	create : function(uniquePopupId, bindElement, tags, params)
	{
		if (!__windows[uniquePopupId])
			__windows[uniquePopupId] = new TagsWindow(uniquePopupId, bindElement, tags, params);
		return __windows[uniquePopupId];
	}
};

var TagsWindow = function(uniquePopupId, bindElement, tags, params)
{
	this.windowArea = new BX.TagsWindowArea(tags, {
		events : { onTagClick : BX.proxy(this.UpdateTagLine, this), onTagCreate : BX.proxy(this.UpdateTagLine, this) }
	});

	this.selectButton = new BX.PopupWindowButton({
		text : BX.message("TAGS_BUTTON_OK"),
		className : "popup-window-button-create",
		events : { click : BX.proxy(this.onSelectButtonClick, this) }
	});

	this.cancelButton = new BX.PopupWindowButtonLink({
		text : BX.message("TAGS_BUTTON_CANCEL"),
		className : "popup-window-button-link-cancel",
		events : { click : BX.proxy(this.onCancelButtonClick, this) }
	});

	this.editButton = null;

	params.editMode = params && params.editMode === false ? false : true;
	if (params.editMode)
		this.editButton = new TagsWindowEditButton({events : { click : BX.proxy(this.onEditButtonClick, this) } });

	this.popupWindow = BX.PopupWindowManager.create(uniquePopupId, bindElement,
		{
			content : "",
			buttons : [this.selectButton, this.cancelButton, this.editButton],
			closeByEsc: true,
			events : {
				onPopupFirstShow : BX.proxy(
					function(popupWindow)
					{
						popupWindow.setContent(this.windowArea.content);
					},
					this
				),

				onPopupShow : BX.proxy(
					function(popupWindow)
					{
						this.windowArea.sortTags();
						popupWindow.popupContainer.style.display = "block";
						this.windowArea.redraw(BX.TagsWindowArea.mode.defaultMode);
						this.UpdateTagLine();
					},
					this
				),

				onAfterPopupShow : BX.proxy(
					function(popupWindow)
					{
						this.windowArea.focusTextbox();
					},
					this
				)
			}
		}
	);

	if (params && params.events)
	{
		for (var eventName in params.events)
			BX.addCustomEvent(this, eventName, params.events[eventName]);
	}

	this.initSelectedTags = null;
};


TagsWindow.prototype.onSelectButtonClick = function(e)
{
	if (this.windowArea.mode == BX.TagsWindowArea.mode.editMode)
	{
		this.windowArea.saveEditTags();
		this.windowArea.redraw(BX.TagsWindowArea.mode.defaultMode);
		this.UpdateButtons();
		this.UpdateTagLine();
		BX.onCustomEvent(this, "onSaveButtonClick", [this]);
	}
	else
	{
		this.windowArea.addTextboxTag();
		this.popupWindow.close();
		BX.onCustomEvent(this, "onSelectButtonClick", [this]);
	}
};

TagsWindow.prototype.onCancelButtonClick = function(e)
{
	if (this.windowArea.mode == BX.TagsWindowArea.mode.editMode)
	{
		this.windowArea.cancelEdit();
		this.windowArea.redraw(BX.TagsWindowArea.mode.defaultMode);
		this.UpdateButtons();
		this.windowArea.focusTextbox();
		BX.onCustomEvent(this, "onCancelButtonClick", [this]);
	}
	else
	{
		if (this.initSelectedTags != null)
			this.windowArea.selectAllTags(true, this.initSelectedTags);

		this.UpdateTagLine();
		this.popupWindow.close();
		BX.onCustomEvent(this, "onCancelButtonClick", [this]);
	}

	BX.PreventDefault(e);
};

TagsWindow.prototype.onEditButtonClick = function(e)
{
	if (this.windowArea.mode != BX.TagsWindowArea.mode.editMode)
	{
		this.windowArea.redraw(BX.TagsWindowArea.mode.editMode);
		this.UpdateButtons();
		BX.onCustomEvent(this, "onEditButtonClick", [this]);
	}

	BX.PreventDefault(e);
};

TagsWindow.prototype.showPopup = function()
{
	this.initSelectedTags = this.windowArea.getSelectedTags();
	this.popupWindow.show();
};

TagsWindow.prototype.UpdateButtons = function()
{
	for (var i = 0; i < this.popupWindow.buttons.length; i++)
	{
		var button = this.popupWindow.buttons[i];
		if (button == this.selectButton)
			this.selectButton.setName(this.windowArea.mode == BX.TagsWindowArea.mode.editMode ? BX.message("TAGS_BUTTON_SAVE") : BX.message("TAGS_BUTTON_OK") );
		else if (button == this.cancelButton)
			this.cancelButton.setName(this.windowArea.mode == BX.TagsWindowArea.mode.editMode ? BX.message("TAGS_BUTTON_DISCARD") : BX.message("TAGS_BUTTON_CANCEL") );
		else if (button == this.editButton)
		{
			if (this.windowArea.mode == BX.TagsWindowArea.mode.editMode)
				this.editButton.setClassName("popup-tags-button-edit-pressed");
			else
				this.editButton.setClassName("");
		}
	}
};

TagsWindow.prototype.UpdateTagLine = function()
{
	BX.onCustomEvent(this, "onUpdateTagLine", [this]);
};

var TagsWindowEditButton = function(params)
{
	TagsWindowEditButton.superclass.constructor.apply(this, arguments);
	this.buttonNode = BX.create("a", {
		props : { className : "popup-tags-button-edit", id : this.id, href : ""},
		attrs : { title : BX.message("TAGS_BUTTON_EDIT_TITLE") },
		events : this.contextEvents
	});

	if (BX.browser.IsIE())
		this.buttonNode.setAttribute("hideFocus", "hidefocus");
}

BX.extend(TagsWindowEditButton, BX.PopupWindowButton);

})(window);





