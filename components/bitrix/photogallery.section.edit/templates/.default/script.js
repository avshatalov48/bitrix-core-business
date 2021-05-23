(function(window) {
	window.BXPhotoList = function(Params)
	{
		var _this = this;
		this.id = Params.id;
		this.Params = Params;
		this.MESS = Params.MESS;
		this.currentPage = 1;
		this.Items = [];
		this.ItemsIndex = {};
		this.pForm = BX('form_photo');
		this.Params.navPageCount = parseInt(this.Params.navPageCount) || 0;
		this.Params.navPageSize = parseInt(this.Params.navPageSize) || 0;
		this.Params.itemsCount = parseInt(this.Params.itemsCount) || 0;
		this.Params.thumbSize = parseInt(this.Params.thumbSize) || 0;

		if (this.Params.itemsCount > 0)
		{
			this.pMorePhoto = BX('more_photos' + this.id);
			if (this.pMorePhoto)
			{
				this.pMorePhoto.onclick = BX.proxy(this.ShowMore, this);
				this.pMorePhotoCont = this.pMorePhoto.parentNode;
			}
			this.pList = BX('bxph_elements_list' + this.id);
			this.thumbSize = parseInt(Params.thumbSize) || 100;
			this.pNfromM = BX('bxph_n_from_m' +  + this.id);
			this.pSellAll = BX('bxph_sel_all' + this.id);
			this.pSellAll.onclick = BX.proxy(this.SelectAll, this);

			this.pMultiMove = BX('bxph_multi_move' + this.id);
			this.pMultiDel = BX('bxph_multi_del' + this.id);
			this.pMultiMove.style.visibility = this.pMultiDel.style.visibility = "hidden";

			this.pMultiMoveCont = this.pMultiMove.parentNode;
			this.pMultiMove.onclick = function()
			{
				if (_this.pMultiMovePopup && _this.pMultiMovePopup.isOpen)
					_this.CloseMultipleMovePopup();
				else
					_this.ShowMultipleMovePopup();
			};
			this.pMultiDel.onclick = BX.proxy(this.MultipleDel, this);

			this.ShowNewItems(Params.items);
			this.bHiddenControls = true;

			if (this.Params.showTags)
			{
				this.pTagsControl = this.pForm.PHOTOS_TAGS;
				BX.bind(this.pTagsControl, "blur", BX.proxy(this.HideTagsInput, this));
				BX.bind(this.pTagsControl, "change", BX.proxy(this.SaveTags, this));
				BX.bind(this.pTagsControl, "keyup", BX.proxy(this.SaveTags, this));
			}
		}

		if (!this.Params.bAfterUpload)
		{
			this.pAddSetLink = BX('bxph_add_set_link' + this.id);
			this.pAddSetCont = BX('bxph_add_set_cont' + this.id);

			this.bShowedAddSet = false;
			this.pAddSetLink.onclick = function()
			{
				_this.bShowedAddSet = !_this.bShowedAddSet;
				if (_this.bShowedAddSet)
					BX.removeClass(_this.pAddSetCont, "photo-al-ed-add-hidden");
				else
					BX.addClass(_this.pAddSetCont, "photo-al-ed-add-hidden");
			};

			this.pUsePassword = BX('bxph_use_password' + this.id);
			this.pUsePasswordCont = BX('bxph_use_password_cont');

			this.pUsePassword.onclick = function()
			{
				if (_this.Params.bPassword)
				{
					BX('DROP_PASSWORD').value = this.checked ? "N" : "Y";
				}
				else
				{
					if (this.checked)
					{
						BX.addClass(_this.pUsePasswordCont, "bxph-show-pass-cont");
						BX('bxph_photo_password').value = "";
						BX('bxph_photo_password').focus();
					}
					else
					{
						BX.removeClass(_this.pUsePasswordCont, "bxph-show-pass-cont");
					}
				}
			};
		}
	};

	window.BXPhotoList.prototype = {
		ShowMore: function()
		{
			var _this = this;
			BX.addClass(this.pMorePhotoCont, "photo-ed-al-show-more-loading");

			this.currentPage++;
			BX.ajax.get(
				this.Params.actionUrl,
				{
					bx_photo_action: 'load_items',
					bx_photo_nav_page: this.currentPage
				},
				function(res)
				{
					setTimeout(function(){
						_this.ShowNewItems(window.bx_load_items_res);
						BX.removeClass(_this.pMorePhotoCont, "photo-ed-al-show-more-loading");

						if (_this.currentPage >=  _this.Params.navPageCount)
							_this.pMorePhotoCont.style.display = "none";
					}, 100);
				}
			)
		},

		AddElement: function(Item)
		{
			if (typeof Item != 'object' || !Item.id || !Item.src)
				return;

			var
				_this = this,
				inpName = 'ITEMS[' + Item.id + ']',
				pItem = this.pList.appendChild(BX.create("DIV", {props: {id: 'bxph_element_' + this.id + '_' + Item.id, className: 'photo-ed-al-item'}})),
				pThumb = pItem.appendChild(BX.create("DIV", {props: {className: 'photo-ed-al-item-thumb'}})).appendChild(BX.create("DIV", {props: {className: 'photo-ed-al-item-thumb-inner'}})),
				pThumbImg = pThumb.appendChild(BX.create("A", {props: {href: Item.url, target: '_blank'}})).appendChild(BX.create("IMG", {props: {src: Item.src}}));

			pThumb.style.width = this.thumbSize + "px";
			pThumb.style.height = this.thumbSize + "px";

			this.AdjustThumb(pThumbImg, Item.width, Item.height);

			var pParams = pItem.appendChild(BX.create("DIV", {props: {className: 'photo-ed-al-item-params'}}));
			if (this.Params.showTitle)
			{
				pParams.appendChild(BX.create("label", {props: {className: 'photo-al-ed-label-top', 'for': 'bxph_title_' + Item.id}, text: this.MESS.albumTitle}));
				var pTitle = pParams.appendChild(BX.create("input", {props: {className: 'photo-al-ed-width', id: 'bxph_title_' + Item.id, type: 'text', value: Item.title, name: inpName + '[title]'}}));
				pTitle.onchange = pTitle.onblur = pTitle.onkeyup = function()
				{
					var item = _this.Items[_this.ItemsIndex[parseInt(this.id.substr('bxph_title_'.length))]];
					if (item.pChanged.value == "N" && this.value != item.oItem.title)
						item.pChanged.value = "Y";
				};
			}

			pParams.appendChild(BX.create("label", {props: {className: 'photo-al-ed-label-top', 'for': 'bxph_desc_' + Item.id}, text: this.MESS.albumDesc}));
			var pDesc = pParams.appendChild(BX.create("textarea", {props: {className: 'photo-al-ed-width', id: 'bxph_desc_' + Item.id, name: inpName + '[desc]'}}));
			pDesc.value = Item.description;
			pDesc.onchange = pDesc.onblur = pDesc.onkeyup = function()
			{
				var item = _this.Items[_this.ItemsIndex[parseInt(this.id.substr('bxph_desc_'.length))]];
				if (item.pChanged.value == "N" && this.value != item.oItem.description)
					item.pChanged.value = "Y";
			};

			var pTags, pTagLink;
			if (this.Params.showTags)
			{
				pTags = pParams.appendChild(BX.create("input", {props: {type: 'hidden', name: inpName + '[tags]', value: Item.tags || "", title: this.MESS.EditTags}}));
				pTagLink = pParams.appendChild(BX.create("a", {props: {className: 'photo-al-ed-tags-link'}, text: this.MESS.addTags}));
				if (Item.tags != "")
				{
					pTagLink.innerHTML = BX.util.htmlspecialchars(Item.tags);
					BX.addClass(pTagLink, "photo-tags");
				}
				pTagLink.id = 'bxph_edit_tag_link' + Item.id;
				pTagLink.onclick = function(){_this.ShowTagsInput(parseInt(this.id.substr('bxph_edit_tag_link'.length)));};
			}
			var pCheck = pParams.appendChild(BX.create("input", {props: {className: 'photo-al-ed-item-check', type: 'checkbox', name: inpName + '[checked]', value: "Y"}}));
			pCheck.onclick = function(){_this.CheckMultipleControls(this.checked);};

			// Controls
			pParams.appendChild(BX.create("a", {props: {className: 'photo-al-ed-action', id: 'photo_del_' + Item.id}, text: this.MESS.del})).onclick = function(){_this.DeleteElement(parseInt(this.id.substr('photo_del_'.length)));};
			pParams.appendChild(BX.create("DIV", {props: {className: 'photo-al-ed-rotate photo-al-ed-rotate-l', id: 'photo_rotate_l_' + Item.id, title: this.MESS.rotateLeft}})).onclick = function(){_this.Rotate(parseInt(this.id.substr('photo_rotate_l_'.length)), 'left');};
			pParams.appendChild(BX.create("DIV", {props: {className: 'photo-al-ed-rotate photo-al-ed-rotate-r', id: 'photo_rotate_r_' + Item.id, title: this.MESS.rotateRight}})).onclick = function(){_this.Rotate(parseInt(this.id.substr('photo_rotate_r_'.length)), 'right');};
			pItem.appendChild(BX.create("a", {props: {className: 'photo-al-ed-action photo-al-ed-action-restore', id: 'photo_restore_' + Item.id}, text: this.MESS.restore})).onclick = function(){_this.RestoreElement(parseInt(this.id.substr('photo_restore_'.length)));};

			var pAnge = pParams.appendChild(BX.create("input", {props: {type: 'hidden', name: inpName + '[angle]', value: 0}}));
			var pDel = pParams.appendChild(BX.create("input", {props: {type: 'hidden', name: inpName + '[deleted]', value: "N"}}));
			var pChanged = pParams.appendChild(BX.create("input", {props: {type: 'hidden', name: inpName + '[changed]', value: "N"}}));

			this.Items.push({
				oItem: Item,
				pWnd: pItem,
				pCheck: pCheck,
				pThumb: pThumb,
				ange: 0,
				pAnge: pAnge,
				pTags: pTags,
				pTagLink: pTagLink,
				pDel: pDel,
				pChanged: pChanged
			});
			this.ItemsIndex[Item.id] = this.Items.length - 1;
		},

		AdjustThumb: function(img, w, h)
		{
			w = parseInt(w);
			h = parseInt(h);
			if (!w || !h)
				return;

			var r = w / h;
			if (r > 1)
			{
				img.style.width = (this.thumbSize * r) + "px";
				img.style.height = this.thumbSize + "px";
				img.style.left = Math.round((this.thumbSize - this.thumbSize * r /* width*/) / 2) + "px";
				img.style.top = 0;
			}
			else
			{
				img.style.height = Math.round(this.thumbSize / r) + "px";
				img.style.width = this.thumbSize + "px";
				img.style.top = Math.round((this.thumbSize - this.thumbSize / r /* height*/) / 2) + "px";
				img.style.left = 0;
			}
		},

		ShowNewItems: function(arItems)
		{
			if (typeof arItems != 'object')
				return;
			for (var id in arItems)
				this.AddElement(arItems[id]);

			// Update counters in the title and in the "Show more" button
			var len = parseInt(this.Items.length);
			var wholeCount = parseInt(this.Params.itemsCount);
			var text = this.MESS.nFromM.replace('#SHOWED#', len);
			text = text.replace('#COUNT#', wholeCount);
			this.pNfromM.innerHTML = " " + text;

			var delta = wholeCount - len;
			if (delta > this.Params.navPageSize)
				delta = parseInt(this.Params.navPageSize);
			var text = this.MESS.nFromM.replace('#SHOWED#', delta);
			text = text.replace('#COUNT#', wholeCount - len);
			if (this.pMorePhoto)
				this.pMorePhoto.innerHTML = this.MESS.MorePhotos + " " + text ;
		},

		DeleteElement: function(id)
		{
			var Item = this.GetItem(id);
			if (Item)
			{
				Item.pDel.value = "Y";
				BX.addClass(Item.pWnd, 'photo-ed-al-item-deleted');
			}
		},

		RestoreElement: function(id)
		{
			var Item = this.GetItem(id);
			if (Item)
			{
				Item.pDel.value = "N";
				BX.removeClass(Item.pWnd, 'photo-ed-al-item-deleted');
			}
		},

		Rotate: function(id, type)
		{
			var Item = this.GetItem(id);
			if (Item)
			{
				if (type == 'left')
					Item.ange -= 90;
				else
					Item.ange += 90;

				if (Item.ange < 0)
					Item.ange = 360 + Item.ange;
				else if (Item.ange == 360)
					Item.ange = 0;

				Item.pAnge.value = Item.ange;
				if (BX.browser.IsIE() && BX.browser.IsDoctype())  //
				{
					var
						link = Item.pThumb.firstChild,
						img = Item.pThumb.firstChild.firstChild;

					var
						top = img.getAttribute("data-bx-top"),
						left = img.getAttribute("data-bx-left");

					if (top === null)
						img.setAttribute("data-bx-top", img.style.top);
					else
						img.style.top = top;

					if (left === null)
						img.setAttribute("data-bx-left", img.style.left);
					else
						img.style.left = left;

					// Following code used to correct IE9 rotation specifics
					if (BX.browser.IsIE9())
					{
						link.className = 'photo-rotate-ie9-' + Item.ange;
						if (Item.ange == 90)
						{
							img.style.top = ( - parseInt(img.style.height) - parseInt(img.style.top)) + 'px';
							img.style.left = img.getAttribute("data-bx-left");
						}
						else if (Item.ange == 180)
						{
							img.style.top = ( - parseInt(img.style.height) - parseInt(img.style.top)) + 'px';
							img.style.left = ( - parseInt(img.style.width) - parseInt(img.style.left)) + 'px';
						}
						else if (Item.ange == 270)
						{
							img.style.left = ( - parseInt(img.style.width) - parseInt(img.style.left)) + 'px';
							img.style.top = img.getAttribute("data-bx-top");
						}
					}
					else
					{
						img.className = 'photo-rotate-' + Item.ange;
						var top1 = parseInt(img.style.top);
						var left1 = parseInt(img.style.left);
						if (Item.ange == 90)
						{
							img.style.top = left1 + 'px';
							img.style.left = top1 + 'px';
						}
						else if (Item.ange == 180)
						{
							img.style.top = 0 + 'px';
							img.style.left = 0 + 'px';
						}
						else if (Item.ange == 270)
						{
							img.style.left = 0 + 'px';
							img.style.top = 0  + 'px';
						}
					}
				}
				else
				{
					Item.pThumb.className = 'photo-ed-al-item-thumb-inner photo-rotate-' + Item.ange;
				}
				Item.pChanged.value = "Y";
			}
		},

		GetItem:function(id)
		{
			if (typeof this.ItemsIndex[id] == 'undefined' || !this.Items[this.ItemsIndex[id]])
				return false;
			return this.Items[this.ItemsIndex[id]];
		},

		SelectAll: function()
		{
			this.bSelectAll = !this.bSelectAll;
			if (this.bSelectAll)
				BX.addClass(this.pSellAll, "photo-ed-al-desel-all");
			else
				BX.removeClass(this.pSellAll, "photo-ed-al-desel-all");

			var i, l = this.Items.length;
			for (i = 0; i < l; i++)
				this.Items[i].pCheck.checked = this.bSelectAll;

			this.CheckMultipleControls(this.bSelectAll);
		},

		MultipleDel: function()
		{
			if (confirm(this.MESS.MultiDelConfirm))
			{
				this.pForm.multiple_action.value = 'delete';
				this.pForm.submit();
			}
		},

		MultipleMoveTo: function(id)
		{
			if (confirm(this.MESS.MultiMoveConfirm) && id > 0)
			{
				this.pForm.move_to.value = id;
				this.pForm.multiple_action.value = 'move';
				this.pForm.submit();
			}
		},

		ShowMultipleMovePopup: function()
		{
			var _this = this;
			if (!this.pMultiMovePopup)
			{
				this.pMultiMovePopup = new BX.CWindow(BX('bxph_multi_move_popup' + this.id), 'float');
				var i = 0, l = this.pMultiMovePopup.Get().childNodes.length, child, count = 0, maxWidth = 100;

				for (i = 0; i < l; i++)
				{
					child = this.pMultiMovePopup.Get().childNodes[i];
					if (child && child.id && child.id.substr(0, 'bxph_sect'.length) == 'bxph_sect')
					{
						count++;
						w = child.innerHTML.length * 8 + parseInt(child.style.paddingLeft);
						if (w > maxWidth)
							maxWidth = w;
						child.onmousedown = function(e)
						{
							_this.MultipleMoveTo(parseInt(this.id.substr('bxph_sect'.length)));
							return BX.PreventDefault(e);
						};
					}
				}
				this.pMultiMovePopup.Get().style.height = (count * 20) + "px";
				this.pMultiMovePopup.Get().style.width = maxWidth + "px";
			}

			BX.addClass(this.pMultiMovePopup.Get(), "photo-ed-al-move-popup");
			this.pMultiMovePopup.Show();
			var pos = BX.pos(this.pMultiMoveCont);
			this.pMultiMovePopup.Get().style.top = (pos.top + 18) + 'px';
			this.pMultiMovePopup.Get().style.left = (pos.left - 2) + 'px';

			setTimeout(function(){BX.bind(document, "click", BX.proxy(_this.CloseMultipleMovePopup, _this));}, 20);
		},

		CloseMultipleMovePopup: function()
		{
			this.pMultiMovePopup.Close();
			BX.unbind(document, "click", BX.proxy(this.CloseMultipleMovePopup, this));
		},

		CheckMultipleControls: function(checked)
		{
			var vis = "hidden";
			if (!checked)
			{
				var i, l = this.Items.length;
				for (i = 0; i < l; i++)
				{
					if (this.Items[i].pCheck.checked)
					{
						vis = "visible";
						break;
					}
				}
			}
			else
			{
				vis = "visible";
			}
			this.pMultiMove.style.visibility = this.pMultiDel.style.visibility = vis;
		},

		DoMultipleAction: function(action, Params)
		{
			this.pForm.multiple_action.value = action;
			bx_move_to: Params.albumId;
			this.pForm.submit();

			return;
			var _this = this, i, l = this.Items.length, arSelectedIds = [], arLast = [], arLastIndex = {}, pWnd;
			for (i = 0; i < l; i++)
			{
				if (this.Items[i].pCheck.checked)
					arSelectedIds.push(this.Items[i].oItem.id);
				else
				{
					arLast.push(this.Items[i]);
					arLastIndex[this.Items[i].oItem.id] = arLast.length - 1;
				}
				//this.Items[i].pCheck.checked = this.bSelectAll;
			}

			var par = {
				bx_photo_action: 'multi_' + action,
				bx_id: arSelectedIds
			};
			if (action == 'move')
				bx_move_to: Params.albumId;

			BX.ajax.get(
				this.Params.actionUrl,
				par,
				function(res)
				{
					setTimeout(function(){
						// BX.removeClass(_this.pMorePhotoCont, "photo-ed-al-show-more-loading");

						l = arSelectedIds.length;
						for (i = 0; i < l; i++)
						{
							pWnd = _this.Items[_this.ItemsIndex[arSelectedIds[i]]].pWnd;
							pWnd.parentNode.removeChild(pWnd);
						}
						_this.ItemsIndex = arLastIndex;
						_this.Items = arLast;
					}, 100);
				}
			)
		},

		ShowTagsInput: function(id)
		{
			this.HideTagsInput();
			var Item = this.GetItem(id);
			if (Item)
			{
				this.curTagItem = Item;
				Item.pTagLink.parentNode.appendChild(this.pTagsControl);
				Item.pTagLink.style.display = "none";
				this.pTagsControl.style.display = "";
				this.pTagsControl.value = Item.pTags.value;
			}
		},

		HideTagsInput: function()
		{
			if (this.curTagItem)
			{
				// Check if the tags popup showed - don't collapse input
				this.pTagsControlDiv = BX(this.pTagsControl.id + "_div");
				if (this.pTagsControlDiv && this.pTagsControlDiv.style.display != "none")
					return this.SaveTags();

				this.curTagItem.pTagLink.style.display = "";
				this.pTagsControl.style.display = "none";
				this.SaveTags();

				if (this.pTagsControl.value != "")
				{
					this.curTagItem.pTagLink.innerHTML = BX.util.htmlspecialchars(this.pTagsControl.value);
					BX.addClass(this.curTagItem.pTagLink, "photo-tags");
				}
				else
				{
					this.curTagItem.pTagLink.innerHTML = BX.util.htmlspecialchars(this.MESS.addTags);
					BX.removeClass(this.curTagItem.pTagLink, "photo-tags");
				}
				this.curTagItem = false;
			}
		},

		SaveTags: function()
		{
			if (this.curTagItem)
			{
				this.curTagItem.pTags.value = this.pTagsControl.value;
				this.curTagItem.pChanged.value = "Y";
			}
		}
	};

})(window);