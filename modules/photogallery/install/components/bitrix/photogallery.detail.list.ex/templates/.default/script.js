(function(window) {
window.BXPhotoSlider = function(Params)
{
	var _this = this;
	this.MESS = Params.MESS;
	this.bSceletonCreated = false;
	this.id = Params.id || 'bxph_detail_list';
	this.uniqueId = Params.uniqueId;

	this.itemsCount = Params.itemsCount;
	this.actionUrl = Params.actionUrl;
	this.responderUrl = Params.responderUrl;
	this.actionPostUrl = Params.actionUrl === true ? this.actionUrl : false;
	this.sign = Params.sign;
	this.reqParams = Params.reqParams;
	this.checkParams = Params.checkParams;

	this.userSettings = Params.userSettings;
	this.itemsPageSize = Params.itemsPageSize;
	this.bSectionOpened = this.userSettings.detail_view == "Y";
	this.reloadItemsOnload = Params.reloadItemsOnload;
	this.cacheRaitingReq = Params.cacheRaitingReq;
	this._bFirstDisplay = true;

	this.bArrowControllEnabled = true;
	this.bEscCloseEnabled = true;
	this.userUrl = Params.userUrl;
	//this.editItemUrl = Params.editItemUrl;
	this.sectionUrl = Params.sectionUrl;
	this.pElementsCont = Params.pElementsCont;
	this.srcIndex = {};

	this.sections = {};
	for (var i in Params.sections)
		this.sections[Params.sections[i]['ID']] = Params.sections[i]['NAME'];

	// Permissions
	this.perm = Params.permissions;
	this.perm.view = !!this.perm.view;
	this.perm.edit = !!this.perm.edit;
	this.perm.moderate = !!this.perm.moderate;
	this.perm.viewComment = !!this.perm.viewComment;
	this.perm.addComment = !!this.perm.addComment;

	// Inteface
	this.useComments = Params.useComments == 'Y' && this.perm.viewComment;
	this.commentsType = Params.commentsType == 'blog' ? 'blog' : 'forum';
	this.useRatings = Params.useRatings == 'Y';
	this.bShowSourceLink = Params.showSourceLink != 'N';
	this.bShowEditControls = this.perm.edit;
	this.showViewsCont = Params.showViewsCont !== 'N';
	this.Mode = this.userSettings.view_mode == "auto" ? "auto" : "fixed";

	this.moderation  = Params.moderation == 'Y';

	// Theme
	this.theme = this.userSettings.theme == "light" ? "light" : "dark";
	this.bChangeTheme = true;

	this.itemUrl = Params.itemUrl;
	this.itemUrlHash = Params.itemUrlHash;

	this.player = false;
	this.Items = [];
	this.ItemIndex = {};
	this.currentIndex = 0;
	this.PageQueue = {};
	this.LoadedPages = {};
	this.LoadedPages[parseInt(Params.currentPage)] = true;
	this.fixedSize = 735;
	this.Rotated = {};

	this.state = 'ready';
	this.showAddCommentsCount = 1;

	this.HandleItems(Params.items);
	this.AttachThumbnailsEvents();

	if (window.location.hash)
		window.location.hash.replace(new RegExp(this.itemUrlHash.replace("#ELEMENT_ID#", '(\\d+)'), 'ig'), function(s, id){_this.currentItem = parseInt(id) || 0;});

	if (!this.currentItem)
		this.currentItem = parseInt(Params.currentItem) || 0;

	if (this.currentItem > 0)
	{
		if (typeof this.ItemIndex[this.currentItem] != 'undefined')
		{
			this.OpenPopup(this.currentItem);
		}
		else
		{
			BX.showWait('photo_load_items');
			BX.addCustomEvent(this, 'OnAfterItemsLoaded', BX.proxy(this.OnAfterCurrentItemsLoaded, this));
			this.LoadItems(this.currentItem, false, false);
		}
	}

	BX.addCustomEvent(window, 'onMoreItemsLoaded', BX.proxy(this.OnMoreItemsLoaded, this));
}

window.BXPhotoSlider.prototype = {
	HandleItems: function(items)
	{
		for (var i in items)
		{
			items[i].index = parseInt(items[i].index);
			items[i].comments = parseInt(items[i].comments) || 0;
			items[i].shows = parseInt(items[i].shows) || 0;
			this.Items[items[i].index] = items[i];
			this.ItemIndex[items[i].id] = items[i].index;
			items[i].bShowed = false;
		}

		BX.onCustomEvent(this, 'OnAfterItemsHandled', [this.Items]);

		if (this.oTopSlider && !this.reloadItemsOnload)
			this.oTopSlider.HandleItems(this.Items);
	},

	OpenPopup: function(id)
	{
		this.windowInnerSize = BX.GetWindowInnerSize(document);

		// Protection from some dummies opening popup after dragging
		if (top.oBXPhotoList && top.oBXPhotoList[this.uniqueId] && top.oBXPhotoList[this.uniqueId].bWasJustDragged)
			return;

		// If we open popup just after page loaded - after closing we replace url by section url
		if (this.currentItem > 0)
		{
			var oItem = this.GetById(id);
			var url = this.sectionUrl.replace("#SECTION_ID#", oItem.album_id);

			if (oItem.gallery_id)
				url = url.replace("#USER_ALIAS#", oItem.gallery_id);
			this.startLocation = {href: url, hash: ''};
		}
		else
			this.startLocation = {href: window.location.href, hash: window.location.hash};

		this.startWndSize = BX.GetWindowScrollPos();

		if (!this.bSceletonCreated || !BX.isNodeInDom(this.pFixedOverlay))
			this.CreateSceleton();

		if (!this.bAjaxNaviEnabled)
			this.InitAjaxNavi();

		this.pFixedOverlay.style.display = "";

		BX.ZIndexManager.bringToFront(this.pFixedOverlay);

		this.bPopupOpened = true;
		this.InitPopupEvents(true);

		this.ChangeMode(this.Mode, false);
		this.DisplayItem(id);

		// Used ONLY when displaying several photos and after opening
		// photo in popup we need to reload all items with correct indexes
		if (this.reloadItemsOnload)
		{
			this._currentItemId = id;
			BX.addCustomEvent(this, 'OnBeforeItemsLoaded', BX.proxy(this._CleanItemsArray, this));
			BX.addCustomEvent(this, 'OnAfterItemsHandled', BX.proxy(this._RedisplayItemAfterItemsReloading, this));
			this.LoadItems(parseInt(this.reloadItemsOnload), false, false);
		}

		BX.addClass(document.body, "photo-body-overlay");

		if (!BX.browser.IsDoctype() && BX.browser.IsIE())
			this.pFixedOverlay.style.top = BX.GetWindowScrollPos(document).scrollTop + "px";

		//BX.addCustomEvent(this, 'OnItemLoaded', BX.proxy(this.PreloadImages, this));
	},

	ClosePopup: function()
	{
		if (this.bPopupOpened)
		{
			this._bFirstDisplay = true;
			this.pFixedOverlay.style.display = "none";
			this.bPopupOpened = false;
			this.InitPopupEvents(false);

			BX.removeClass(document.body, "photo-body-overlay");
			this.SetUrl(this.startLocation.href, this.startLocation.hash);
			this.SaveRotatedItems();

			if (BX.browser.IsIE())
			{
				var _this = this, endWndSize;
				setTimeout(function()
				{
					endWndSize = BX.GetWindowScrollPos();
					if (endWndSize.scrollTop != _this.startWndSize.scrollTop)
						BX.GetDocElement().scrollTop = _this.startWndSize.scrollTop;
				}, 100);
				setTimeout(function()
				{
					endWndSize = BX.GetWindowScrollPos();
					if (endWndSize.scrollTop != _this.startWndSize.scrollTop)
						BX.GetDocElement().scrollTop = _this.startWndSize.scrollTop;
				}, 300);
			}

			//BX.removeCustomEvent(this, 'OnItemLoaded', BX.proxy(this.PreloadImages, this));
		}
		return false;
	},

	InitPopupEvents: function(bInit)
	{
		if (bInit === true)
		{
			//BX.bind(window, "keyup", BX.proxy(this.OnKeyUp, this));
			BX.bind(document, "keyup", BX.proxy(this.OnKeyUp, this));
			BX.bind(window, "beforeunload", BX.proxy(this.SaveRotatedItems, this));
			BX.bind(window, "resize", BX.proxy(this.OnResize, this));
		}
		else
		{
			//BX.unbind(window, "keyup", BX.proxy(this.OnKeyUp, this));
			BX.unbind(document, "keyup", BX.proxy(this.OnKeyUp, this));
			BX.unbind(window, "beforeunload", BX.proxy(this.SaveRotatedItems, this));
			BX.unbind(window, "resize", BX.proxy(this.OnResize, this));
		}
	},

	InitAjaxNavi: function()
	{
		var _this = this;
		this.oAjaxNavi = {
			pObj: this,
			getState: function()
			{
				if (this.pObj && this.pObj.Items && this.pObj.Items[this.pObj.currentIndex])
					return {index: this.pObj.currentIndex, id: this.pObj.Items[this.pObj.currentIndex].id};
				return {};
			},
			setState: function(state)
			{
				if (state && state.id)
					_this.DisplayItem(state.id, true, false);
			}
		};

		BX.ajax.history.init(this.oAjaxNavi);
	},

	SetUrl: function(url, hash)
	{
		BX.ajax.history.put(this.oAjaxNavi.getState(), url, hash);
	},

	Previous: function(stepAround)
	{
		if (this.slideShowStatus == 'play')
			this.StopSlideShow();

		return this.ShowItem(this.currentIndex - 1, 'prev', stepAround !== false);
	},

	Next: function(stepAround, bSlideshow)
	{
		if (!bSlideshow && this.slideShowStatus == 'play')
			this.StopSlideShow();

		return this.ShowItem(this.currentIndex + 1, 'next', stepAround !== false);
	},

	ShowItem: function(index, direction, stepAround, bAffectTopSlider)
	{
		this.SaveRotatedItems(true);
		if (!this.Items || !this.Items.length)
			return;

		var curItemId = false;
		if (index >= this.itemsCount)
		{
			index = 0;
			curItemId = 'first';
			if (!stepAround ||  this.itemsCount <= 1)
				return false;
		}
		else if (index < 0)
		{
			index = this.itemsCount - 1;
			curItemId = 'last';
			if (!stepAround ||  this.itemsCount <= 1)
				return false;
		}

		if (this.Items[index])
		{
			this.DisplayItem(this.Items[index].id, bAffectTopSlider);
		}
		else
		{
			if (!direction) // ?
				return;

			if (curItemId === false)
				curItemId = this.Items[direction == 'next' ? index - 1 : index + 1].id;

			this.LoadItems(curItemId, direction);
		}

		return true;
	},

	LoadPage: function(page)
	{
		if (this.itemsPageCount && this.itemsPageCount < page)
		{
			if (this.PageQueue[page])
				delete this.PageQueue[page];
			return;
		}

		if (!this.LoadedPages[page] && !this.PageQueue[page])
		{
			if (this.state == 'loading')
				this.PageQueue[page] = true;
			else
				this.LoadItems('page', false, false, page);
		}
	},

	LoadItems: function(curItemId, direction, bGoToDirecton, pageNum)
	{
		var params = {
			UCID: this.uniqueId,
			sessid: BX.bitrix_sessid(),
			current : {id : curItemId},
			include_subsection: 'Y',
			return_array : 'Y',
			ELEMENT_ID : curItemId,
			AJAX_CALL: 'Y'
		};
		if (direction !== false)
			params.direction = direction == 'next' ? 'next' : 'prev';

		this.state = 'loading';
		if (curItemId == 'last')
			params.last_page = Math.ceil(this.itemsCount / this.itemsPageSize);

		if (curItemId == 'page')
			params.page_num = parseInt(pageNum) || 0;

		var _this = this;
		window.bxphres = false;
		BX.onCustomEvent(this, 'OnBeforeItemsLoaded', [params]);

		BX.ajax.get(this.actionUrl, params, function(result){
			setTimeout(function(){
				_this.CheckActionPostUrl();

				if (window.bxphres.itemsPageSize)
					_this.itemsPageSize = parseInt(window.bxphres.itemsPageSize);

				if (window.bxphres.itemsCount)
					_this.itemsCount = parseInt(window.bxphres.itemsCount);

				if (window.bxphres.pageCount)
					_this.itemsPageCount = parseInt(window.bxphres.pageCount);

				if (window.bxphres && window.bxphres.items)
					_this.HandleItems(window.bxphres.items);

				_this.LoadedPages[parseInt(window.bxphres.currentPage)] = true;
				if (_this.PageQueue[parseInt(window.bxphres.currentPage)] === true)
					delete _this.PageQueue[parseInt(window.bxphres.currentPage)];

				_this.state = 'ready';

				if(bGoToDirecton !== false)
				{
					if (direction == 'next')
						_this.Next();
					else if(direction == 'prev')
						_this.Previous();
				}

				BX.onCustomEvent(_this, 'OnAfterItemsLoaded');
				var page, lastViewed;
				for(page in _this.PageQueue)
					if (_this.PageQueue[page] === true && !_this.LoadedPages[page])
						lastViewed = page;

				if (lastViewed)
					_this.LoadItems('page', false, false, lastViewed);
			}, 100);
		});
	},

	PreloadItems: function()
	{
		return;
		//if (this.state == 'loading')

		var bLoadPrev = false;
		var bLoadNext = false;

		// Preload to the left
		if (this.minIndex > 0 && this.currentIndex - this.minIndex < 10)
			bLoadPrev = true;
		else if (this.maxIndex < this.itemsCount - 1 && this.maxIndex - this.currentIndex < 30)
			bLoadNext = true;
		else if (this.minIndex > 0 && this.currentIndex - this.minIndex < 30)
			bLoadPrev = true;

		if (!this._iterrations)
			this._iterrations = 0;
		if (++this._iterrations > 30)
			return clearInterval(this.preloadItemsInt);

		// Preload to the right
		if (bLoadPrev)
			return this.LoadItems(this.Items[this.minIndex].id, 'prev', false);

		if (bLoadNext)
			return this.LoadItems(this.Items[this.maxIndex].id, 'next', false);

		if (this.minIndex == 0 && this.maxIndex == this.itemsCount - 1)
			clearInterval(this.preloadItemsInt);
	},

	GetById: function(id)
	{
		if (typeof this.ItemIndex[id] != 'undefined' && this.Items[this.ItemIndex[id]])
			return this.Items[this.ItemIndex[id]];

		return false;
	},

	OnAfterItemLoad: function()
	{

	},

	DisplayItem: function(id, bAffectTopSlider, bAffectToUrl)
	{
		var _this = this;

		if (this._DisplayTimeout)
		{
			clearTimeout(this._DisplayTimeout);
			this._DisplayTimeout = null;
		}

		var oItem = this.GetById(id);
		if (oItem === false)
		{
			this._DisplayTimeout = setTimeout(function(){_this.DisplayItem(id, bAffectTopSlider);}, 100);
			return;
		}

		if (oItem === false || (this.currentIndex === parseInt(oItem.index) && !this._bFirstDisplay))
			return;

		this._bFirstDisplay = false;
		this.currentIndex = parseInt(oItem.index);
		if (bAffectToUrl !== false && !this.reloadItemsOnload)
		{
			var url = this.itemUrl.replace('#ELEMENT_ID#', id);
			url = url.replace('#SECTION_ID#', oItem.album_id);
			if (oItem.gallery_id)
				url = url.replace('#USER_ALIAS#', oItem.gallery_id);

			var hash = this.itemUrlHash.replace('#ELEMENT_ID#', id);
			hash = hash.replace('#SECTION_ID#', oItem.album_id);
			if (oItem.gallery_id)
				hash = hash.replace('#USER_ALIAS#', oItem.gallery_id);
			this.SetUrl(url, hash);
		}

		this.topPager.current.innerHTML = parseInt(oItem.index) + 1;
		this.topPager.count.innerHTML = (parseInt(this.itemsCount) < parseInt(oItem.index) + 1) ? parseInt(oItem.index) + 1 : this.itemsCount;

		if (this.RotateCont && this.RotateCont.parentNode)
		{
			this.RotateCont.parentNode.removeChild(this.RotateCont);
			this.RotateCont = null;
		}

		oItem.srcLoaded = false;
		if (this.pImage && this.pImage.parentNode)
			this.pImage.parentNode.removeChild(this.pImage);

		// Don't show item if it was just rotated and now we save the rotation angle
		if (oItem.saveRotationProcess)
		{
			this.pImageWait.style.display = "";
		}
		else
		{
			var src = oItem.big_src || oItem.src;
			this.pImage = this.pImgCell.appendChild(BX.create("IMG", {props: {src: src, className: 'photo-image-loading', id: 'bx-ph-' + oItem.id}}));
			this.pImageWait.style.display = "";

			this.pImage.onload = function(){_this.ImageOnload(parseInt(this.id.substr('bx-ph-'.length)));};
			if (this._photoOnloadInterval)
			{
				clearInterval(this._photoOnloadInterval);
				this._photoOnloadInterval = null;
			}

			if (!_this.pImage.complete && !oItem.srcLoaded)
			{
				this._photoOnloadInterval = setInterval(function()
					{
						if (_this.pImage.complete)
							_this.ImageOnload(oItem.id);
						if (oItem.srcLoaded)
						{
							clearInterval(_this._photoOnloadInterval);
							_this._photoOnloadInterval = null;
						}
					},
					100
				);
			}
			else
			{
				this.ImageOnload(oItem.id);
			}

			this.CleanRotation(false);
			this.CheckImageSize(oItem);
		}

		this.CheckFullModeDisplay(oItem);
		this.DisplayItemDetails(oItem);
		this.pCommentsCont.innerHTML = "";

		// Comments
		this.ShowComWait(false);
		if (this.useComments)
		{
			oItem.comments = parseInt(oItem.comments);
			if (oItem.comments > 0)
			{
				this.pComCount.innerHTML = oItem.comments;
				if (this.reloadItemsOnload)
					this.ShowComWait(true);
			}

			if (!this.reloadItemsOnload)
			{
				if (oItem.comments > 0)
				{
					this.pShowMoreComLink.style.display = "none";
					if (oItem.savedComments && this._CommentsParams)
					{
						this.pCommentsCont.innerHTML = oItem.savedComments;
						this._CommentsParams.navParams = oItem.savedNavparams;
						this.UpdateCommentsCount();
					}
					else
					{
						this.GetComments(oItem.id);
					}
				}
				else
				{
					if (!this._CommentsParams)
						this.GetComments(oItem.id);

					this.pShowMoreComLink.style.display = "none";
					this.pComCount.innerHTML = '0';
				}

				if (this._CommentsParams && this.perm.addComment)
				{
					if (this._CommentsParams.elementId)
						this._CommentsParams.elementId.value = oItem.id;
					if (this._CommentsParams.textarea)
					{
						this._CommentsParams.textarea.value = oItem.savedCommentText || "";

						this.CheckTextareaActivity(this._CommentsParams.textarea, true);
					}
				}

				if (this.perm.addComment)
				{
					if (this.commentsType == 'forum')
					{
						if (oItem.comments > this.showAddCommentsCount)
						{
							this.pAddCommentForm.style.display = "none"; // Hide add comment form
							this.pAddComLink.style.display = "";
						}
						else
						{
							this.pAddCommentForm.style.display = "block"; // Show add comment form
							this.pAddComLink.style.display = "none";
						}
					}
					else // Blog
					{
						this.pAddCommentForm.style.display = "block"; // Show add comment form
					}
				}
			}
		}

		this.pActionsCont.style.display = this.reloadItemsOnload ? 'none' : '';

		if (this.oTopSlider && bAffectTopSlider !== false)
			this.oTopSlider.SelectItem(oItem);

		if (!oItem.bShowed || this.useRatings)
			this.OnItemShowed(oItem);
	},

	DisplayItemDetails: function(oItem)
	{
		// Description
		this.CancelItemDescription(oItem);

		// Album
		var url = this.sectionUrl.replace("#SECTION_ID#", oItem.album_id);
		if (oItem.gallery_id)
			url = url.replace("#USER_ALIAS#", oItem.gallery_id);
		this.pAlbumLink.href = url;
		this.pAlbumLink.innerHTML = oItem.album_name;

		// Author
		this.pAuthorLink.href = this.userUrl.replace(/#USER_ID#/ig, oItem.author_id);
		if (oItem.gallery_id && this.pAuthorLink.href.toLowerCase().indexOf('#user_alias#'))
			this.pAuthorLink.href = this.pAuthorLink.href.replace(/#USER_ALIAS#/ig, oItem.gallery_id);

		this.pAuthorLink.innerHTML = oItem.author_name;

		// Views
		if (this.pViews)
			this.pViews.innerHTML = oItem.shows;

		// Tags
		this.pTags.innerHTML = ""; // clean all
		if (oItem.tags != "")
		{
			this.pTags.parentNode.style.display = "";
			if (oItem.tags_array)
			{
				this.pTags.appendChild(BX.create("SPAN", {text: this.MESS.tags + ': '}));
				for (var i = 0, l = oItem.tags_array.length; i < l; i++)
				{
					name = oItem.tags_array[i]['TAG_NAME'];
					href = oItem.tags_array[i]['TAG_URL'];
					this.pTags.appendChild(BX.create("A", {props: {href: href}, text: name}));
					if (i < l - 1)
						this.pTags.appendChild(document.createTextNode(', '));
				}
			}
			else
			{
				this.pTags.innerHTML = this.MESS.tags + ': ' + BX.util.htmlspecialchars(oItem.tags);
			}
		}
		else
		{
			this.pTags.parentNode.style.display = "none";
		}

		// Date
		this.pDate.innerHTML = this.MESS.created + ' ' + oItem.date;

		// Link to sourceImage
		if (this.bShowSourceLink)
			this.pSourceLink.href = oItem.big_src || oItem.src;

		if (this.moderation)
			this.SetWarning(oItem['active'] != 'Y' ? 'active' : false);
	},

	SetWarning: function(type)
	{
		if (!this.pTopWarning)
			this.pTopWarning = this.pImgCell.appendChild(BX.create("DIV"));

		this.pTopWarning.className = 'photo-top-warning' + (type ? ' photo-top-not-' + type : '');
		if (type == 'active')
		{
			this.pTopWarning.style.top = Math.round(((parseInt(this.pImgCell.offsetHeight) / 2) || 300) - 10) + "px";
			this.pTopWarning.innerHTML = "<span>" + this.MESS.notModerated + "</span>";
			if (this.perm.moderate)
			{
				var pActivLinkModer = this.pTopWarning.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-top-not-warn-link"}, html: this.MESS.activateNow}));
				pActivLinkModer.onclick = BX.proxy(this.ActivateItem, this);
				var pDelLinkModer = this.pTopWarning.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-top-not-warn-link"}, html: this.MESS.deleteNow}));
				pDelLinkModer.onclick = BX.proxy(this.DeleteItem, this);
			}
		}
	},

	ImageOnload: function(id)
	{
		var oItem = this.GetById(id);
		if (oItem.index == this.currentIndex && !oItem.srcLoaded)
		{
			oItem.srcLoaded = true;
			this.pImageWait.style.display = "none";
			BX.removeClass(this.pImage, 'photo-image-loading');
			BX.onCustomEvent(this, 'OnItemLoaded', [oItem]);
			setTimeout(BX.proxy(this.AdjustControls, this), 100);
			this.PreloadImages(oItem);
		}
	},

	CreateSceleton: function()
	{
		var _this = this;
		var pPrev = BX.findChild(document.body, {className: 'photo-fixed-overlay'});
		if (pPrev)
			BX.cleanNode(pPrev, true);

		this.pFixedOverlay = document.body.appendChild(BX.create("DIV", {props: {className: (!BX.browser.IsDoctype() && BX.browser.IsIE()) ? "photo-fixed-overlay photo-quirks-mode" : "photo-fixed-overlay sds"}}));
		this.pTable = this.pFixedOverlay.appendChild(BX.create("TABLE", {props: {className: "photo-main-table", cellSpacing: 0}}));

		BX.ZIndexManager.register(this.pFixedOverlay);

		var r = this.pTable.insertRow(-1);
		var
			pLeftCell = BX.adjust(r.insertCell(-1), {attrs: {className: "photo-prev-slide-wrap"}}),
			pMainCell = BX.adjust(r.insertCell(-1), {attrs: {className: "photo-main-block-wrap"}}),
			pRightCell = BX.adjust(r.insertCell(-1), {attrs: {className: "photo-next-slide-wrap"}});

		this.pMainCell = pMainCell;

		this.prevPhotoLink = pLeftCell.appendChild(BX.create("a", {props: {href: "javascript: void(0)", className: "photo-prev-slide"}, html: "<span></span>"}));
		this.closeLink = pRightCell.appendChild(BX.create("a", {props: {href: "javascript: void(0)", className: "photo-close"}, html: "<span></span>"}));
		this.nextPhotoLink = pRightCell.appendChild(BX.create("a", {props: {href: "javascript: void(0)", className: "photo-next-slide"}, html: "<span></span>"}));

		// Set nav
		this.prevPhotoLink.onclick = BX.proxy(this.Previous, this);
		this.nextPhotoLink.onclick = BX.proxy(this.Next, this);
		this.closeLink.onclick = BX.proxy(this.ClosePopup, this);

		this.pOverlay = this.pFixedOverlay.appendChild(BX.create("DIV", {props: {className: "photo-overlay"}, style: {height: '800px'}}));
		this.pPopup = pMainCell.appendChild(BX.create("DIV", {props: {className: "photo-wrap"}})).appendChild(BX.create("DIV", {props: {className: "photo-main"}}));

		// Top section of the popup window (navigation, slider, close and fullscreen buttons)
		this.pPopupTop = this.pPopup.appendChild(BX.create("DIV", {props: {className: "photo-top"}}));
		var
			pNavCont = this.pPopupTop.appendChild(BX.create("DIV", {props: {className: "photo-top-nav"}})),
			pPager = pNavCont.appendChild(BX.create("DIV", {props: {className: "photo-pager"}, html: '<span class="photo-current-page">#</span><span>' + this.MESS.from + '</span><span class="photo-last-page">#</span>'})),
			pTopButCont = pNavCont.appendChild(BX.create("DIV", {props: {className: "photo-top-nav-buttons"}}));

		this.topPager = {current: pPager.childNodes[0], count: pPager.childNodes[2]};
		this.pTopSliderBut = pTopButCont.appendChild(BX.create("A", {props:{className: "photo-slider-button", href: "javascript: void(0)"}, html: '<span>' + this.MESS.slider + '</span><i></i>'}));
		this.pMinimizeBut = pTopButCont.appendChild(BX.create("A", {props:{className: "photo-full-screen-but", href: "javascript: void(0)"}})); // photo-minimize-screen-but
		this.pCloseBut = pTopButCont.appendChild(BX.create("A", {props:{className: "photo-close-but", href: "javascript: void(0)"}}));
		this.pCloseBut.onclick = BX.proxy(this.ClosePopup, this);
		this.pMinimizeBut.onclick = BX.proxy(this.ChangeMode, this);

		// Scroller in the top
		this.pScrollCont = pNavCont.appendChild(BX.create("DIV", {props: {className: "photo-scroll"}}));
		this.pScroller = this.pScrollCont.appendChild(BX.create("A", {props:{className: "photo-scroll-wheel"}}));

		// Container with small previews of photos in the top of
		this.pSliderCont = this.pPopupTop.appendChild(BX.create("DIV", {props: {className: "photo-slider"}}));
		this.pSlider = this.pSliderCont.appendChild(BX.create("DIV", {props: {className: "photo-slider-int"}}));
		this.oTopSlider = new BXTopSlider(this);

		// Photo
		this.pImageTable = this.pPopup.appendChild(BX.create("TABLE", {props: {className: "photo-image", cellSpacing: 0}}));
		this.pImageTable.onclick = BX.proxy(this.Next, this);
		this.pImgCell = this.pImageTable.insertRow(-1).insertCell(-1);
		this.pImageWait = this.pImgCell.appendChild(BX.create("IMG", {props: {src: "/bitrix/images/1.gif", className: "photo-image-wait"}, style: {display: 'none'}}));
		this.pImage = this.pImgCell.appendChild(BX.create("IMG", {props: {src: "/bitrix/images/1.gif"}}));

		// Footer section (detail, comments, rating, slideshow controls)
		this.pPopupFooter = this.pPopup.appendChild(BX.create("DIV", {props: {className: "photo-footer"}}));
		var
			pShortDescCont = this.pPopupFooter.appendChild(BX.create("DIV", {props:{className: "photo-descriptions"}})),
			pDescLeft = pShortDescCont.appendChild(BX.create("DIV", {props:{className: "photo-description-left"}})),
			pDescRight = pShortDescCont.appendChild(BX.create("DIV", {props:{className: "photo-description-right"}}));

		this.pDescCont = pDescLeft.appendChild(BX.create("SPAN", {props:{className: "photo-description-text"}}));

		if (this.bShowEditControls)
		{
			this.pAddDescLink = pDescLeft.appendChild(BX.create("A", {props:{className: "photo-qt-desc", href: "javascript: void(0)"}, text: this.MESS.addDesc}));
			this.pAddDescLink.onclick = BX.proxy(this.EditItemDescription, this);
		}

		this.pDesc = this.pDescCont.appendChild(BX.create("SPAN"));
		if (this.bShowEditControls)
		{
			this.pEditDesc = this.pDescCont.appendChild(BX.create("A", {props:{href: "javascript:void(0)"}}));
			this.pEditDesc.onclick = BX.proxy(this.EditItemDescription, this);

			// Textarea with save and cancel buttons
			this.pEditDescCont = pDescLeft.appendChild(BX.create("DIV", {props:{className: "photo-sign-wrap"}}));
			this.pEditDescInp = this.pEditDescCont.appendChild(BX.create("TEXTAREA", {props:{className: "photo-textarea photo-textarea-active"}}));
			this.pEditDescSave = this.pEditDescCont.appendChild(BX.create("A", {props:{className: "photo-comment-add", href: "javascript:void(0)", title: this.MESS.saveDetailTitle}, html: '<span>' + this.MESS.save + '</span><i></i>'}));
			this.pEditDescCancel = this.pEditDescCont.appendChild(BX.create("A", {props:{className: "photo-comment-add", href: "javascript:void(0)"}, html: '<span>' + this.MESS.cancel + '</span><i></i>'}));
			this.pEditDescSave.onclick = BX.proxy(this.SaveItemDescription, this);
			this.pEditDescCancel.onclick = BX.proxy(this.CancelItemDescription, this);

			this.pEditDescInp.onkeyup = function(e)
			{
				if(!e)
					e = window.event
				if(!e)
					return;
				var key = e.keyCode || e.which;
				if (key == 17) // Ctrl
				{
					_this._bCtrlPressed = true;
					setTimeout(function(){_this._bCtrlPressed = false;}, 400);
				}
				else if (key == 13 && (e.ctrlKey || _this._bCtrlPressed))
				{
					if (_this.bItemDescEdited)
						_this.SaveItemDescription();
				}
				return BX.PreventDefault(e);
			};
		}

		// Rating with stars - filled by component bitrix:iblock.vote with 'ajax' template
		if (this.useRatings)
		{
			this.pRatingCont = pDescRight.appendChild(BX.create("SPAN", {props:{id: "bx-photo-rating-cont", className: "photo-rating"}}));
			if (!BX.browser.IsDoctype() && BX.browser.IsIE())
				this.pRatingCont.style.margin = "3px 10px 0 0";
		}

		// SlideShow Control
		this.pSlideshowCont = pDescRight.appendChild(BX.create("SPAN", {props:{className: "photo-slideshow"}}));
		this.pSlideshow =  this.pSlideshowCont.appendChild(BX.create("A", {props:{className: "photo-slideshow-button", title: this.MESS.slideshowTitle}, html: '<span class="photo-slideshow-button-text">' + this.MESS.slideshow + ':</span><span class="photo-slideshow-button-img"></span><i></i>'}));
		this.pSlideshow.onclick = BX.proxy(this.RunSlideShow, this);

		// Extended section which can be showed or hidden by user
		this.pExtendDescCont = this.pPopupFooter.appendChild(BX.create("DIV", {props:{className: "photo-comments no-comment"}}));
		var
			pLeftCont = this.pExtendDescCont.appendChild(BX.create("DIV", {props:{className: "photo-comments-left"}})),
			pRightCont = this.pExtendDescCont.appendChild(BX.create("DIV", {props:{className: "photo-comments-right"}})),
			pInfo = pRightCont.appendChild(BX.create("DIV", {props:{className: "photo-comments-right-info"}})),
			pAlbumCont = pInfo.appendChild(BX.create("DIV", {props:{className: "photo-album"}, children: [BX.create("SPAN", {props:{className: "photo-comments-right-grey"}, text: this.MESS.album + ': '})]})),
			pAuthorCont = pInfo.appendChild(BX.create("DIV", {props:{className: "photo-comments-author"}, children: [BX.create("SPAN", {props:{className: "photo-comments-right-grey"}, text: this.MESS.author + ': '})]}));

		if (this.showViewsCont)
		{
			var pViewsCont = pInfo.appendChild(BX.create("DIV", {props:{className: "photo-comments-author"}, children: [BX.create("SPAN", {props:{className: "photo-comments-right-grey"}, text: this.MESS.views + ': '})]}));
			this.pViews = pViewsCont.appendChild(BX.create("SPAN", {props:{className: "photo-comments-right-grey"}}));
		}
		var pTagsCont = pInfo.appendChild(BX.create("DIV", {props:{className: "photo-comments-author"}}));

		this.pAlbumLink = pAlbumCont.appendChild(BX.create("A", {props:{href: "#"}, html: "album"}));
		this.pAuthorLink = pAuthorCont.appendChild(BX.create("A", {props:{href: '#'}, html: 'author'}));

		this.pDate = pInfo.appendChild(BX.create("DIV", {props:{className: "photo-comments-add_date"}}));
		this.pTags = pTagsCont.appendChild(BX.create("SPAN", {props:{className: "photo-comments-right-grey"}}));

		this.pActionsCont = pRightCont.appendChild(BX.create("DIV"));
		var pEditWrap = this.pActionsCont.appendChild(BX.create("DIV", {props:{className: "photo-comments-edit-wrap"}}));

		if (this.bShowEditControls)
		{
			var
				pEditLink = pEditWrap.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-comments-button"}, html: this.MESS.edit})),
				pRotateLeft = pEditWrap.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-comment-undo", title: this.MESS.rotate}})),
				pRotateRight = pEditWrap.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-comment-redo", title: this.MESS.rotate}})),
				pDelLink = this.pActionsCont.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-comments-button"}, html: this.MESS.del}));

			pRotateLeft.onclick = function(e){_this.RotateItem(true); return BX.PreventDefault(e);};
			pRotateRight.onclick = function(e){_this.RotateItem(false); return BX.PreventDefault(e);};
			pDelLink.onclick = BX.proxy(this.DeleteItem, this);
			pEditLink.onclick = BX.proxy(this.EditItem, this);
		}

		this.pBigPhoto = this.pActionsCont.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-comments-button"}, html: this.MESS.bigPhoto}));
		this.pBigPhoto.onclick = BX.proxy(this.ChangeMode, this);

		if (this.bShowSourceLink)
		{
			this.pSourceLink = this.pActionsCont.appendChild(BX.create("A", {props:{href: "javascript: void(0)", className: "photo-comments-button"}, html: this.MESS.sourceImage}));
			this.pSourceLink.onclick = BX.proxy(this.ShowSource, this);
		}

		this.pComWaiter = pLeftCont.appendChild(BX.create("IMG", {props:{className: "photo-wait-comments", src: "/bitrix/images/1.gif"}, style: {display: "none"}})); // Waiter

		this.pShowMoreComLink = pLeftCont.appendChild(BX.create("A", {props:{className: "photo-more-comments", href: "javascript:void(0)"}, html: this.MESS.moreCom, style: {display: 'none'}})); // Show more comments link
		this.pCommentsCont = pLeftCont.appendChild(BX.create("DIV")); // Container with comments
		this.pShowMoreComLink.onclick = BX.proxy(this.ShowMoreComments, this);
		this.pAddCommentForm = pLeftCont.appendChild(BX.create("DIV"));

		if (!this.perm.addComment && this.useComments)
			pLeftCont.appendChild(BX.create("DIV", {props: {className: 'photo-comments-warning'}, text: this.MESS.comAccessDenied}));

		if (this.perm.addComment)
		{
			this.pAddComLink = pLeftCont.appendChild(BX.create("A", {props:{className: "photo-comments-add", href: "javascript:void(0)"}, html: this.MESS.addComment, style: {display: 'none'}})); // Add new comment
			this.pAddComLink.onclick = function()
			{
				_this.pAddCommentForm.style.display = "block";
				_this.pAddComLink.style.display = "none";
				if (_this._CommentsParams && _this._CommentsParams.textarea)
					_this.CheckTextareaActivity(_this._CommentsParams.textarea, false);
				_this.AdjustOverlay();
			};
		}

		this.pComCountLink = this.pPopupFooter.appendChild(BX.create("A", {props:{className: "photo-qt-comments", href: "javascript: void(0)"}, html: this.MESS.commentsCount + ": "}));
		this.pComCount = this.pComCountLink.appendChild(BX.create("SPAN", {html: '0'}));
		this.pExFlipper = this.pPopupFooter.appendChild(BX.create("A", {props:{className: "photo-open-footer", href: "javascript: void(0)"}, html: "<i></i>"}));
		this.pExFlipper.onclick = this.pComCountLink.onclick = BX.proxy(this.OpenExtDescription, this);

		// User can change theme
		if (this.bChangeTheme)
		{
			this.pThemeLink = this.pPopupFooter.appendChild(BX.create("A", {props:{className: "photo-dark-theme-link", href: "javascript: void(0)"}}));
			this.pThemeLink.onclick = BX.proxy(this.SetTheme, this);
		}

		this.OpenExtDescription(this.bSectionOpened, false);

		this.bSceletonCreated = true;
		this.SetUnselectable([this.pTopSliderBut, this.pMinimizeBut, this.pCloseBut, this.pScrollCont, this.pScroller, this.pSliderCont, this.pSlider]);

		if (!this.useComments)
		{
			// Put Information cont to the left side
			pLeftCont.appendChild(pInfo);
			BX.addClass(pInfo, "photo-comments-right-info-left");

			this.pComCountLink.style.display = "none";
		}

		this.SetTheme(this.theme, false);
		this.OnResize();
	},

	AdjustControls: function()
	{
		// Set nav
		if (this.itemsCount <= 1)
		{
			this.nextPhotoLink.style.display = this.prevPhotoLink.style.display = "none";
		}
		else
		{
			this.nextPhotoLink.style.display = this.prevPhotoLink.style.display = "";
			if(!BX.browser.IsDoctype() && BX.browser.IsIE())
			{
				this.nextPhotoLink.style.height = this.prevPhotoLink.style.height = document.documentElement.offsetHeight + "px";
				this.prevPhotoLink.style.width = (this.prevPhotoLink.parentNode.clientWidth - 1) + 'px';
				this.nextPhotoLink.style.width = (this.nextPhotoLink.parentNode.clientWidth - 1) + 'px';
			}
			else
			{
				this.nextPhotoLink.style.height = this.prevPhotoLink.style.height = document.documentElement.clientHeight + "px";
				this.prevPhotoLink.style.width = this.prevPhotoLink.parentNode.clientWidth + 'px';
				this.nextPhotoLink.style.width = this.nextPhotoLink.parentNode.clientWidth + 'px';
			}
			this.prevPhotoLink.firstChild.style.left = (this.prevPhotoLink.parentNode.clientWidth * 4 / 10) + 'px';
			this.nextPhotoLink.firstChild.style.right = (this.nextPhotoLink.parentNode.clientWidth * 4 / 10) + 'px';
		}
		this.closeLink.style.width = this.closeLink.parentNode.clientWidth + 'px';

		if (BX.browser.IsChrome())
		{
			var x = this.closeLink.firstChild;
			x.style.top = '14.5px';
			setTimeout(function(){x.style.top = '15px';}, 10);
		}

		var footW = this.Mode == "auto" ? parseInt(this.pImageTable.offsetWidth) : this.fixedSize;
		if (!footW || footW < this.fixedSize)
			footW = this.fixedSize;

		this.pPopupFooter.style.width = footW + 'px';

		this.AdjustOverlay();

		if (this.oTopSlider)
			this.oTopSlider.AdjustScroller();
	},

	AdjustOverlay: function()
	{
		var
			h = parseInt(this.pTable.offsetHeight) || 0,
			hWin = parseInt(BX.GetWindowScrollSize(document).scrollHeight) || 0;

		if (h < hWin)
			h = hWin;

		this.pOverlay.style.height = (h + (BX.browser.IsIE10() ? 250 : 50)) + 'px';
	},

	OpenExtDescription: function(bOpen, bSave)
	{
		if (typeof bOpen != 'boolean')
			bOpen = !this.bSectionOpened;

		if (bOpen)
		{
			BX.addClass(this.pExFlipper, "photo-open-footer-open");
			this.pExtendDescCont.style.display = "block";
			if (this.useComments)
				this.pComCountLink.style.display = "none";
		}
		else
		{
			BX.removeClass(this.pExFlipper, "photo-open-footer-open");
			this.pExtendDescCont.style.display = "none";
			if (this.useComments)
				this.pComCountLink.style.display = "";
		}
		this.bSectionOpened = bOpen;

		if (bSave !== false)
			this.SaveOption("detail_view", bOpen ? "Y" : "N");

		this.AdjustOverlay();
		return false;
	},

	OnKeyUp: function(e)
	{
		if(!e)
			e = window.event
		if(!e)
			return;

		var key = e.keyCode || e.which;

		if(key == 27)
		{
			if (this.bEscCloseEnabled)
				this.ClosePopup();
		}
		else if(key == 39) // Next photo
		{
			if (this.bArrowControllEnabled)
				this.Next();
		}
		else if(key == 37) // Previous photo
		{
			if (this.bArrowControllEnabled)
				this.Previous();
		}
		else if (key == 17) // Ctrl
		{
			var _this = this;
			this._bCtrlPressed = true;
			setTimeout(function(){_this._bCtrlPressed = false;}, 400);
		}
		else if (key == 13 && (e.ctrlKey || this._bCtrlPressed))
		{
			if (this.bItemDescEdited)
				this.SaveItemDescription();
			else if (this.pAddCommentForm.style.display == "block" && this._CommentsParams)
				this.SubmitNewComment();
		}
	},

	SetUnselectable: function(arNodes)
	{
		if (typeof arNodes != 'object')
			arNodes = [arNodes];

		for (var i = 0, l = arNodes.length; i < l; i++)
		{
			if (!BX.browser.IsIE8())
				BX.setUnselectable(arNodes[i]);
			arNodes[i].ondragstart = function (e){return BX.PreventDefault(e);};
		}
	},

	GetComments: function(id)
	{
		this.ShowComWait(true);
		var _this = this;
		window.bxph_error = null;

		BX.ajax.get(
			this.actionUrl,
			{
				UCID: this.uniqueId,
				photo_list_action: 'load_comments',
				sessid: BX.bitrix_sessid(),
				photo_element_id : id,
				AJAX_CALL: 'Y'
			},
			function(result){
				setTimeout(function()
				{
					_this.CheckActionPostUrl();

					_this.ShowComWait(false);
					if (window.bxph_error)
					{
						alert(window.bxph_error);
					}
					else
					{
						if (id != _this.Items[_this.currentIndex].id)
							return;

						if (_this.commentsType == 'blog')
						{
							_this.ParseBlogComments(result);
						}
						else
						{
							if (_this.perm.addComment)
								_this.ParseForm(result);

							_this.AddComments(result);
						}

						//BX.onCustomEvent(_this, 'OnRegisterCommentsControl');
					}
				}, 50);

				var f = function()
				{
					setTimeout(function(){BX.onCustomEvent(_this, 'OnRegisterCommentsControl');}, 100);
					BX.removeCustomEvent('onAjaxSuccessFinish', f);
				};
				BX.addCustomEvent('onAjaxSuccessFinish', f);
			}
		);
	},

	AddComments: function(result, bAttachToTheEnd)
	{
		var exIds = this.Items[this.currentIndex].arCommentInds;
		if (exIds && top._bxArCommentsIds)
		{
			for (var i in top._bxArCommentsIds)
			{
				if (exIds[top._bxArCommentsIds[i]])
				{
					var pComment = BX('bxphoto_com_' + top._bxArCommentsIds[i]);
					if (pComment)
						pComment.parentNode.removeChild(pComment);
				}
			}
		}

		if (bAttachToTheEnd === true)
			this.pCommentsCont.innerHTML += this.ParseComments(result);
		else
			this.pCommentsCont.innerHTML = this.ParseComments(result) + this.pCommentsCont.innerHTML;

		this.Items[this.currentIndex].savedComments = this.pCommentsCont.innerHTML;

		this.AdjustOverlay();
	},

	RegisterCommentsControl: function(Params)
	{
		if (Params.itemId != this.Items[this.currentIndex].id)
			return;

		if (Params.navParams)
		{
			Params.navParams.pageCount = parseInt(Params.navParams.pageCount) || 0;
			Params.navParams.pageSize = parseInt(Params.navParams.pageSize) || 0;
			Params.navParams.pagen = parseInt(Params.navParams.pagen) || 0;
			Params.navParams.NavNum = parseInt(Params.navParams.NavNum) || 0;
			Params.navParams.nSelectedCount = parseInt(Params.navParams.nSelectedCount) || 0;
		}

		this._CommentsParams = Params;

		this.Items[this.currentIndex].savedNavparams = BX.clone(Params.navParams, true);

		if (!this.Items[this.currentIndex].arCommentInds)
			this.Items[this.currentIndex].arCommentInds = {};

		if (Params.arComments)
			for (var i in Params.arComments)
				this.Items[this.currentIndex].arCommentInds[Params.arComments[i]] = true;

		this.UpdateCommentsCount();
		var _this = this;

		if (this.perm.addComment && this._CommentsParams && this._CommentsParams.textarea && this._CommentsParams.button)
		{
			this._CommentsParams.button.onclick = BX.proxy(this.SubmitNewComment, this);
			this._CommentsParams.textarea.onblur = function()
			{
				_this.SaveUnpostedComment(this.value);
				_this.CheckTextareaActivity(this, true);
			};
			this._CommentsParams.textarea.onkeyup = function(e)
			{
				_this.SaveUnpostedComment(this.value);
				if(!e)
					e = window.event
				if(!e)
					return;

				var key = e.keyCode || e.which;
				if (key == 17) // Ctrl
				{
					_this._bCtrlPressed = true;
					setTimeout(function(){_this._bCtrlPressed = false;}, 400);
				}
				else if (key == 13 && (e.ctrlKey || _this._bCtrlPressed))
				{
					if (_this.pAddCommentForm.style.display == "block" && _this._CommentsParams)
						_this.SubmitNewComment();
				}
				return BX.PreventDefault(e);
			};
			this._CommentsParams.textarea.onfocus = function()
			{
				_this.CheckTextareaActivity(this, false);
			};
			this.CheckTextareaActivity(this._CommentsParams.textarea, true);


			if (this._CommentsParams.guestName)
			{
				this._CommentsParams.guestName.onblur = function(){_this.CheckInputActivity(this, true);};
				this._CommentsParams.guestName.onfocus = function(){_this.CheckInputActivity(this, false);};
			}
			if (this._CommentsParams.guestEmail)
			{
				this._CommentsParams.guestEmail.onblur = function(){_this.CheckInputActivity(this, true);};
				this._CommentsParams.guestEmail.onfocus = function(){_this.CheckInputActivity(this, false);};
			}
		}
	},

	ShowMoreComments: function()
	{
		if (!this._CommentsParams || !this._CommentsParams.navParams || this._CommentsParams.navParams.pagen >=  this._CommentsParams.navParams.pageCount)
		{
			this.pShowMoreComLink.style.display = "none";
			return;
		}

		var _this = this;
		var params = {
			UCID: this.uniqueId,
			photo_list_action: 'load_comments',
			sessid: BX.bitrix_sessid(),
			return_more_comments: 'Y',
			photo_element_id : this.Items[this.currentIndex].id,
			AJAX_CALL: 'Y'
		};
		params['PAGEN_' + this._CommentsParams.navParams.NavNum] = this._CommentsParams.navParams.pagen + 1;

		top._bxArCommentsIds = false;
		this.ShowComWait(true);
		BX.ajax.get(this.actionUrl, params, function(result){setTimeout(function(){
			_this.CheckActionPostUrl();
			_this.AddComments(result);
			_this.ShowComWait(false);
			BX.onCustomEvent(_this, 'OnRegisterCommentsControl');
		}, 100);});
	},

	ParseBlogComments: function(result)
	{
		result = BX.util.trim(result);
		var
			_this = this,
			indBegin = result.indexOf('#BLOG_COMMENTS_BEGIN#'),
			indEnd = result.indexOf('#BLOG_COMMENTS_END#');

		if (indBegin === -1 || indEnd === -1)
			return '';
		var res = result.substr(indBegin + '#BLOG_COMMENTS_BEGIN#'.length, indEnd - indBegin - '#BLOG_COMMENTS_BEGIN#'.length);

		BX.addCustomEvent('onShowPhotoBlogComment', BX.proxy(this.AdjustOverlay, this));
		BX.addCustomEvent('onAddNewPhotoBlogComment', function(params)
		{
			if (params)
			{
				var count = parseInt(params.count);
				if (parseInt(params.editId) == 0)
					count++; // Add new comment

				if (parseInt(params.deletedComment) != 0)
					count--; // Comment was deleted

				_this.pComCount.innerHTML = count;
			}
		});
		this.pAddCommentForm.innerHTML = BX.util.trim(res);
		this.AdjustOverlay();
	},

	ParseComments: function(result)
	{
		result = BX.util.trim(result);
		var
			indBegin = result.indexOf('#COMMENTS_BEGIN#'),
			indEnd = result.indexOf('#COMMENTS_END#');

		if (indBegin === -1 || indEnd === -1)
			return '';

		var res = result.substr(indBegin + '#COMMENTS_BEGIN#'.length, indEnd - indBegin - '#COMMENTS_BEGIN#'.length);
		res = BX.util.trim(res);
		return res;
	},

	ParseForm: function(result)
	{
		var
			resMes = '',
			resNote = '',
			indBegin = result.indexOf('#NOTE_BEGIN#'),
			indEnd = result.indexOf('#NOTE_END#');

		if (indBegin !== -1 && indEnd !== -1)
			resNote = BX.util.trim(result.substr(indBegin + '#NOTE_BEGIN#'.length, indEnd - indBegin - '#NOTE_BEGIN#'.length));

		indBegin = result.indexOf('#ADD_COMMENT_BEGIN#');
		indEnd = result.indexOf('#ADD_COMMENT_END#');

		if (indBegin !== -1 && indEnd !== -1)
			resMes = BX.util.trim(result.substr(indBegin + '#ADD_COMMENT_BEGIN#'.length, indEnd - indBegin - '#ADD_COMMENT_BEGIN#'.length));

		this.pAddCommentForm.innerHTML = resNote + resMes;

		if (resNote !== '')
			return false;

		// Fetch errors For forums
		if (resMes.indexOf('reviews-note-error') !== -1)
			return false;

		return true;
	},

	UpdateCommentsCount: function()
	{
		this.pShowMoreComLink.style.display = (this._CommentsParams.navParams && this._CommentsParams.navParams.pageCount > 1) ? "" : "none";

		var
			mess = this.MESS.moreCom,
			N = this._CommentsParams.navParams.pageSize,
			M = 0,
			i, showed = 0,
			all = this._CommentsParams.navParams.nSelectedCount;

		this.Items[this.currentIndex].comments = all;
		for (i in this.Items[this.currentIndex].arCommentInds)
			showed++;

		M = all - showed;

		if (M < N)
		{
			N = M;
			mess = this.MESS.moreCom2;
		}

		if (M <= 0)
			this.pShowMoreComLink.style.display = "none";

		mess = mess.replace("#N#", N);
		mess = mess.replace("#M#", M);
		this.pShowMoreComLink.innerHTML = mess;

		this.pComCount.innerHTML = all;
	},

	ShowComWait: function(bShow)
	{
		this.pComWaiter.style.display = bShow ? "" : "none";
	},

	SaveUnpostedComment: function(value)
	{
		this.Items[this.currentIndex].savedCommentText = value;
	},

	SubmitNewComment: function()
	{
		if (!this._CommentsParams)
			return;

		var _this = this;
		var bReRequest = this.Items[this.currentIndex].comments < 1; // First comment for photo
		var id = this.Items[this.currentIndex].id;
		this._CommentsParams.textarea.value = BX.util.trim(this._CommentsParams.textarea.value);
		if (this._CommentsParams.textarea.value == this.MESS.commentTitle)
			this._CommentsParams.textarea.value = "";

		// Add UCID to
		this._CommentsParams.form.action += '&UCID='+ this.uniqueId;
		top._bxError = false;
		var comment = this._CommentsParams.textarea.value;

		if (comment.length > 2)
		{
			// Comments
			this.ShowComWait(true);
			BX.ajax.submit(this._CommentsParams.form, function(result)
			{
				var res = _this.ParseForm(result);
				if (!top._bxError && res !== false)
				{
					if (bReRequest)
						return _this.GetComments(id);
					setTimeout(function(){_this.AddComments(result, true);}, 100);
				}

				if (res === false)
				{
					_this.pAddCommentForm.style.display = "";
					_this.pAddComLink.style.display = "none";
					_this._CommentsParams.textarea.value = comment;
				}
				BX.onCustomEvent(_this, 'OnRegisterCommentsControl');
				_this.ShowComWait(false);
			});

			setTimeout(function(){
				// Hide add comment link and show link
				_this.pAddCommentForm.style.display = "none";
				_this.pAddComLink.style.display = "";

				_this.SaveUnpostedComment('');
				_this._CommentsParams.textarea.value = "";
				_this._CommentsParams.form.parentNode.removeChild(_this._CommentsParams.form);
			}, 50);
		}
		else
		{
			if (this._CommentsParams.textarea.value.length > 0)
				alert(this.MESS.shortComError);
		}
	},

	CheckTextareaActivity: function(ta, bBlur)
	{
		if (!this.perm.addComment)
			return;

		var
			val = BX.util.trim(ta.value),
			deffMess = this.MESS.commentTitle,
			activeClass = 'photo-textarea-active';

		if (bBlur)
		{
			if (val == '' || val == deffMess)
			{
				ta.value = deffMess;
				BX.removeClass(ta, activeClass);
			}
			else
			{
				BX.addClass(ta, activeClass);
			}
			this.bArrowControllEnabled = true;
		}
		else
		{
			if (val == '' || val == deffMess)
			{
				ta.value = '';
				BX.addClass(ta, activeClass);
				ta.focus();
			}
			this.bArrowControllEnabled = false;
		}
	},

	CheckInputActivity: function(inp, bBlur)
	{
		if (!inp)
			return;

		var
			val = BX.util.trim(inp.value),
			deffMess = inp.title + '...',
			activeClass = 'bxph-photo-active';

		if (bBlur)
		{
			if (val == '' || val == deffMess)
			{
				inp.value = deffMess;
				BX.removeClass(inp, activeClass);
			}
			else
			{
				BX.addClass(inp, activeClass);
			}
			this.bArrowControllEnabled = true;
		}
		else
		{
			if (val == '' || val == deffMess)
			{
				inp.value = '';
				BX.addClass(inp, activeClass);
				inp.focus();
			}
			this.bArrowControllEnabled = false;
		}
	},

	SaveOption: function(option, value)
	{
		BX.userOptions.save('photogallery', this.id, option, value);
	},

	RunSlideShow: function()
	{
		if (this.slideShowStatus != 'play')
			this.PlaySlideShow(false, this.currentIndex == this.itemsCount - 1);
		else
			this.StopSlideShow();

		return false;
	},

	InitSlideShow: function()
	{
		this.bSlideShowInited = true;
		this.slideShowSpeed = parseInt(this.userSettings.slide_show_speed);
		if (!this.slideShowSpeed || this.slideShowSpeed < 1 || this.slideShowSpeed > 5)
			this.slideShowSpeed = 3;
		this.pSlideShowSpeed = {};

		var pSlideshowSpeedCont = this.pSlideshowCont.appendChild(BX.create("SPAN", {props:{className: "photo-slideshow-speed"}}));
		var i, _this = this;
		for (i = 1; i <= 5; i++)
		{
			this.pSlideShowSpeed[i] = pSlideshowSpeedCont.appendChild(BX.create("A", {props: {id: 'bxph-slide-speed-' + i, className: (i == this.slideShowSpeed ? "photo-slideshow-speed-active" : ""), href: "javascript:void(0)"}, html: i}));
			this.pSlideShowSpeed[i].onclick = function(){_this.SetSlideShowSpeed(parseInt(this.id.substr('bxph-slide-speed-'.length)));};
		};

		BX.addCustomEvent(this, 'OnItemLoaded', BX.proxy(this.SlideShowItemOnload, this));
	},

	SetSlideShowSpeed: function(speed)
	{
		this.slideShowSpeed = speed;
		var i, _this = this;
		for (i = 1; i <= 5; i++)
			this.pSlideShowSpeed[i].className = i == this.slideShowSpeed ? "photo-slideshow-speed-active" : "";

		this.SaveOption('slide_show_speed', speed);
	},

	PlaySlideShow: function(bPlay, bPlayAgain)
	{
		// Changle button
		BX.addClass(this.pSlideshowCont, "photo-slideshow-active");
		if (!this.bSlideShowInited)
			this.InitSlideShow();

		var _this = this;
		this.slideShowStatus = 'play';
		bPlay = !!bPlay;
		bPlayAgain = !!bPlayAgain;

		if (bPlay)
		{
			this.Next(!!bPlayAgain, true);
			if (this.currentIndex == this.itemsCount - 1 && !bPlayAgain)
				this.StopSlideShow();
		}
		else
		{
			setTimeout(function()
			{
				if (_this.slideShowStatus == 'play')
					_this.PlaySlideShow(true, !!bPlayAgain);
			}, this.slideShowSpeed * 1000);
		}
	},

	StopSlideShow: function()
	{
		// Changle button
		BX.removeClass(this.pSlideshowCont, "photo-slideshow-active");
		this.slideShowStatus = 'paused';
	},

	SlideShowItemOnload: function(Item)
	{
		if (this.slideShowStatus != 'play')
			return;
		this.PlaySlideShow(false);
	},

	EditItemDescription: function(e)
	{
		if (this.bShowEditControls)
		{
			this.bArrowControllEnabled = false;
			this.bItemDescEdited = true;

			this.pAddDescLink.style.display = "none";
			this.pEditDescCont.style.display = "";
			this.pDescCont.style.display = "none";

			var oItem = this.Items[this.currentIndex];
			this.pEditDescInp.value = oItem.description;
			BX.focus(this.pEditDescInp);
		}
		return BX.PreventDefault(e);
	},

	SaveItemDescription: function(checkActUrl)
	{
		_this = this;
		if (!this.actionPostUrl && checkActUrl !== false)
			return setTimeout(
				function()
				{
					_this.SaveItemDescription(false);
				}, 200);

		var oItem = this.Items[this.currentIndex];
		oItem._description = oItem.description;
		oItem.description = this.pEditDescInp.value;

		window.bxph_error = null;
		var _this = this;

		BX.ajax.post(
			this.actionPostUrl || this.actionUrl,
			{
				UCID: this.uniqueId,
				photo_list_action: 'save_description',
				sessid: BX.bitrix_sessid(),
				description: oItem.description,
				ELEMENT_ID : oItem.id
			},
			function(result){setTimeout(function()
			{
				if (window.bxph_error)
				{
					alert(window.bxph_error);
					if (_this.currentIndex == oItem.index)
						_this.EditItemDescription();
					oItem.description = oItem._description;
				}

				delete oItem._description;
			}, 100);}
		);

		// Hide
		this.CancelItemDescription(oItem);
	},

	CancelItemDescription: function(oItem)
	{
		if (!oItem || oItem.nodeName || typeof oItem.description == 'undefined')
			oItem = this.Items[this.currentIndex];

		this.bItemDescEdited = false;
		this.bArrowControllEnabled = true;
		// Description
		if (oItem.description.length > 0)
		{
			if (this.bShowEditControls)
				this.pAddDescLink.style.display = "none";
			this.pDescCont.style.display = "";

			var html = oItem.description;
			html = BX.util.htmlspecialchars(html);
			html = html.replace(/\n|\r/ig, '<br>');
			this.pDesc.innerHTML = html;
		}
		else // Show add desc link
		{
			if (this.bShowEditControls)
				this.pAddDescLink.style.display = "";
			this.pDescCont.style.display = "none";
			this.pDesc.innerHTML = "";
		}
		if (this.bShowEditControls)
			this.pEditDescCont.style.display = "none";
	},

	_CleanItemsArray: function()
	{
		this.Items = [];
		this.ItemIndex = {};
		this.currentIndex = 0;

		this.LoadedPages = {};

		//currentPage

		if (this.oTopSlider)
			this.oTopSlider.CleanItems();
	},

	_RedisplayItemAfterItemsReloading: function()
	{
		if (this._currentItemId > 0 && this.reloadItemsOnload)
		{
			this.reloadItemsOnload = false;
			this._bFirstDisplay = true;
			this.DisplayItem(this._currentItemId);
			this._currentItemId = 0;
			BX.removeCustomEvent(this, 'OnBeforeItemsLoaded', BX.proxy(this._CleanItemsArray, this));
			BX.removeCustomEvent(this, 'OnAfterItemsHandled', BX.proxy(this._RedisplayItemAfterItemsReloading, this));

			if (this.oTopSlider)
				this.oTopSlider.CheckCurrentShownItems();
		}
	},

	RotateItem: function(bLeft)
	{
		// Current item
		var oItem = this.Items[this.currentIndex];
		if (!oItem)
			return;

		// Calculate angle
		if (!oItem.angle)
			oItem.angle = 0;

		oItem.angle += (bLeft ? -1 : 1) * 90;
		if (oItem.angle < 0)
			oItem.angle = 360 + oItem.angle;
		else if (oItem.angle == 360)
			oItem.angle = 0;

		// Catch original sizes once for item and zero angle
		if (!oItem.w)
			oItem.w = parseInt(this.pImage.offsetWidth);
		if (!oItem.h)
			oItem.h = parseInt(this.pImage.offsetHeight);

		// Set size
		if (oItem.angle == 0 || oItem.angle == 180)
		{
			this.pImage.style.marginTop = '';
			this.pImage.style.marginLeft = '';
			if (this.RotateCont)
			{
				this.pImgCell.appendChild(this.pImage);
				this.RotateCont.parentNode.removeChild(this.RotateCont);
				this.RotateCont = null;
			}
		}
		else
		{
			// Put image into the container
			if (!this.RotateCont)
			{
				this.RotateCont = this.pImage.parentNode.appendChild(BX.create("DIV", {props: {className: "photo-rotate-cont"}}));
				this.RotateInnerCont = this.RotateCont.appendChild(BX.create("DIV", {props: {className: "photo-rotate-cont-inner"}}));
				this.RotateInnerCont.appendChild(this.pImage);
				this.RotateInnerCont.style.overflow = 'hidden';
			}


			// Default - for landscape images (W > H)
			var pad = Math.round((oItem.w - oItem.h) / 2);

			// For portrait images (H < W)
			if (oItem.w < oItem.h)
			{
				if (!BX.browser.IsIE())
					this.pImage.style.marginTop = pad + "px";
				else if (BX.browser.IsIE() && BX.browser.IsDoctype())
					this.pImage.style.marginLeft = (pad * 2) + "px";
				pad = 0;
			}
			else
			{
				if (BX.browser.IsIE() && BX.browser.IsDoctype())
					this.RotateCont.style.paddingLeft = pad + 'px';
			}

			if (!BX.browser.IsIE())
				this.RotateCont.style.paddingTop = pad + 'px';

			this.RotateInnerCont.style.width = oItem.h + 'px';
			this.RotateInnerCont.style.height = (oItem.w - pad) + 'px';
		}

		this.RotateImage(this.pImage, oItem.angle, true);

		if (!this.Rotated[oItem.id])
			this.Rotated[oItem.id] = true;

		this.AdjustControls();
	},

	SaveRotatedItems: function(bTime)
	{
		if (bTime === true)
			return setTimeout(BX.proxy(this.SaveRotatedItems, this), 1);

		for (var id in this.Rotated)
			if (this.Rotated)
				this.SaveRotationAngle(this.GetById(id));

		// Reinit rotation array
		this.Rotated = {};
	},

	CleanRotation: function(oItem, bCheck)
	{
		if (this.RotateInnerCont)
		{
			this.pImage.style.marginTop = '';
			this.pImage.style.marginLeft = '';
			if (this.RotateCont)
			{
				this.pImgCell.appendChild(this.pImage);
				this.RotateCont.parentNode.removeChild(this.RotateCont);
				this.RotateCont = null;
			}
			if (bCheck !== false)
				this.CheckImageSize(oItem);
			this.RotateImage(this.pImage, 0, false);
		}
	},

	RotateImage: function(pImg, angle, bEffects)
	{
		var cn = '';
		if (angle != 0)
			cn = BX.browser.IsIE9() ? 'photo-rotate-ie9-' + angle : 'photo-rotate-' + angle;
		pImg.className = cn;
	},

	SaveRotationAngle: function(oItem)
	{
		if (oItem.angle == 0)
			return;

		oItem.saveRotationProcess = true;

		BX.showWait('photo_rotate');
		var angle = oItem.angle;
		var _this = this;
		window.bxphres = false;
		BX.ajax.get(
			this.actionUrl,
			{
				UCID: this.uniqueId,
				photo_list_action: 'rotate',
				sessid: BX.bitrix_sessid(),
				ELEMENT_ID : oItem.id,
				angle: angle,
				AJAX_CALL: 'Y'
			},
			function(result){setTimeout(function()
			{
				_this.CheckActionPostUrl();
				BX.closeWait('photo_rotate');
				if (window.bxph_error)
				{
					alert(window.bxph_error);
				}
				else if(window.bxphres && window.bxphres.Item)
				{
					var Item = _this.GetById(window.bxphres.Item.id);

					Item.saveRotationProcess = false;
					if (Item.big_src)
						Item.big_src = window.bxphres.Item.src;

					if (Item.angle == angle)
					{
						Item.src = window.bxphres.Item.src;
						Item.width = window.bxphres.Item.w;
						Item.height = window.bxphres.Item.h;

						delete Item.h;
						delete Item.w;
						delete Item.angle;

						if (_this.currentIndex == Item.index)
						{
							_this.CleanRotation(Item);
							_this._bFirstDisplay = true; // We need to set it 'true' to pass currentIndex checking
							_this.DisplayItem(Item.id);
						}

						if (_this.oTopSlider)
							_this.oTopSlider.UpdateThumbnail(Item.index);
					}
				}
			}, 100);}
		);
	},

	EditItem: function(e)
	{
		var oItem = this.Items[this.currentIndex];
		if (!oItem || !oItem.id)
			return;

		var _this = this;
		var url = this.actionUrl + (this.actionUrl.indexOf('?') == -1 ? '?' : '&') + 'photo_list_action=edit&sessid=' + BX.bitrix_sessid();
		url += '&ELEMENT_ID=' + oItem.id;
		url += '&SECTION_ID=' + oItem.album_id;
		url += '&AJAX_CALL=Y&UCID=' + this.uniqueId;
		if (oItem.gallery_id)
			url += '&USER_ALIAS=' + oItem.gallery_id;

		// Set z-index
		BX.WindowManager.setStartZIndex(3100);

		var oEditDialog = new BX.CDialog({
			title : this.MESS.photoEditDialogTitle,
			content_url: url,
			buttons: [BX.CDialog.btnSave, BX.CDialog.btnCancel],
			width: 600
		});
		oEditDialog.Show();

		this.bArrowControllEnabled = false;
		this.bEscCloseEnabled = false;

		BX.addCustomEvent(oEditDialog, "onWindowRegister", function(){
			oEditDialog.adjustSizeEx();
			BX.focus(BX('bxph_title'));
		});

		BX.addCustomEvent(oEditDialog, "onWindowUnRegister", function(){
			_this.bArrowControllEnabled = true;
			_this.bEscCloseEnabled = true;
		});

		oEditDialog.ClearButtons();
		oEditDialog.SetButtons([
			new BX.CWindowButton(
			{
				title: BX.message('JS_CORE_WINDOW_SAVE'),
				id: 'savebtn',
				action: BX.proxy(this.CheckForm, this)
			}),
			oEditDialog.btnCancel
		]);
		window.oPhotoEditDialog = oEditDialog;

		return BX.PreventDefault(e);
	},

	CheckForm: function()
	{
		var _this = this;
		var form = document.forms['form_photo'];
		if (typeof form != "object")
			return false;

		var oData = {"AJAX_CALL" : "Y"};
		for (var ii = 0; ii < form.elements.length; ii++)
		{
			if (form.elements[ii] && form.elements[ii].name)
			{
				if (form.elements[ii].type && form.elements[ii].type == "checkbox")
				{
					if (form.elements[ii].checked == true)
						oData[form.elements[ii].name] = form.elements[ii].value;
				}
				else
				{
					oData[form.elements[ii].name] = form.elements[ii].value;
				}
			}
		}

		BX.showWait('photo_window_edit');
		window.oPhotoEditDialogError = false;

		BX.ajax.post(
			form.action,
			oData,
			function(data)
			{
				setTimeout(function(){
					BX.closeWait('photo_window_edit');
					result = {};

					if (window.oPhotoEditDialogError !== false)
					{
						var errorTr = BX("bxph_error_row");
						errorTr.style.display = "";
						errorTr.cells[0].innerHTML = window.oPhotoEditDialogError;
						window.oPhotoEditDialog.adjustSizeEx();
					}
					else
					{
						top.result = false;
						try
						{
							data = BX.util.trim(data);
							var
								res = '',
								indBegin = data.indexOf('<!--BX_PHOTO_EDIT_RES-->'),
								indEnd = data.indexOf('<!--BX_PHOTO_EDIT_RES_END-->');

							if (indBegin != -1 && ~indEnd != -1)
								res = data.substr(indBegin + '<!--BX_PHOTO_EDIT_RES-->'.length, indEnd - indBegin - '<!--BX_PHOTO_EDIT_RES-->'.length);
							else
								res = data;

							eval("top.result = " + res + ";");
						}
						catch(e)
						{
							var errorTr = BX("bxph_error_row");
							if (errorTr)
							{
								errorTr.style.display = "";
								errorTr.cells[0].innerHTML = _this.MESS.unknownError;
							}
							window.oPhotoEditDialog.adjustSizeEx();
						}

						if (top.result)
						{
							_this.EditItemCallBack(top.result);
							window.oPhotoEditDialog.Close();
						}
					}
				}, 200);
			}
		);
	},

	EditItemCallBack: function(res)
	{
		var oItem = this.Items[this.currentIndex];
		oItem.tags = res.TAGS || '';
		oItem.description = res._DESCRIPTION || '';
		oItem.date = res.DATE_STR || res.DATE;
		oItem.tags_array = res.TAGS_LIST || [];
		//oItem['public'] = res.PUBLIC;
		//oItem.approved = res.APPROVED;

		// Photo was moved to other album - we have to kill it from slider
		if (res.SECTION_ID && parseInt(res.SECTION_ID) !== parseInt(oItem.album_id))
			this.DropAndRecalc(oItem);
		else
			this.DisplayItemDetails(oItem);
	},

	DeleteItem: function(e)
	{
		var oItem = this.Items[this.currentIndex];
		if (!confirm(this.MESS.delItemConfirm))
			return;

		window.bxph_error = null;
		var _this = this;
		BX.ajax.get(
			this.actionUrl,
			{
				UCID: this.uniqueId,
				photo_list_action: 'delete',
				sessid: BX.bitrix_sessid(),
				ELEMENT_ID : oItem.id,
				AJAX_CALL: 'Y'
			},
			function(result){setTimeout(function()
			{
				_this.CheckActionPostUrl();
				if (window.bxph_error)
					alert(window.bxph_error);
				else
					_this.DropAndRecalc(oItem);
			}, 100);}
		);
		return BX.PreventDefault(e);
	},

	ActivateItem: function(e)
	{
		var oItem = this.Items[this.currentIndex];
		oItem.active = "Y";
		this.DisplayItemDetails(oItem);

		window.bxph_error = null;
		var _this = this;
		BX.ajax.get(
			this.actionUrl,
			{
				UCID: this.uniqueId,
				photo_list_action: 'activate',
				sessid: BX.bitrix_sessid(),
				ELEMENT_ID : oItem.id
			},
			function(result){setTimeout(function(){_this.CheckActionPostUrl();}, 100);}
		);

		return BX.PreventDefault(e);
	},

	DropAndRecalc: function(oItem)
	{
		if (this.itemsCount <= 1)
		{
			this.ClosePopup();
			// Refresh page
			setTimeout(function(){window.location = window.location;}, 100);
		}

		// Go to previous item or to the next if this item was first
		if (oItem.index == 0)
			this.Next();
		else if (this.itemsCount > 1)
			this.Previous();

		// Del item from this.Items, ItemIndex and correct indexes in this.Items;
		var Items = [];
		var ItemIndex = {};
		var index = 0;

		for(var i in this.Items)
		{
			if (this.Items[i].index != oItem.index)
			{
				index = this.Items[i].index;
				if (this.Items[i].index > oItem.index)
					index--;
				this.Items[i].index = index;
				Items[index] = this.Items[i];
				ItemIndex[this.Items[i].id] = index;
			}
		}

		this.Items = Items;
		this.ItemIndex = ItemIndex;
		// decrese items count
		this.itemsCount--;

		if (this.itemsCount == 0)
		{
			this.ClosePopup();
			// Refresh page
			setTimeout(function(){window.location = window.location;}, 100);
		}

		// Del from
		if (this.oTopSlider)
			this.oTopSlider.RecalcItems();

		// Update items count in the top
		this.topPager.count.innerHTML = this.itemsCount;
	},

	ChangeMode: function(mode, bSave)
	{
		if (mode != "auto" && mode != "fixed")
			mode = this.Mode == "auto" ? "fixed" : "auto"; // Flip mode

		// Change buttons and labels
		if (mode == "auto")
		{
			BX.addClass(this.pMinimizeBut, 'photo-minimize-screen-but');
			this.pBigPhoto.innerHTML = this.MESS.smallPhoto;
			this.pBigPhoto.title = this.pMinimizeBut.title = this.MESS.smallPhoto;
		}
		else
		{
			BX.removeClass(this.pMinimizeBut, 'photo-minimize-screen-but');
			this.pBigPhoto.innerHTML = this.MESS.bigPhoto;
			this.pBigPhoto.title = this.pMinimizeBut.title = this.MESS.bigPhoto;
			this.pPopupFooter.style.width = this.fixedSize + 'px';
		}

		this.Mode = mode;
		this.CheckImageSize(); // Check current displayed image

		if (bSave !== false)
			this.SaveOption("view_mode", this.Mode);
	},

	CheckFullModeDisplay: function(oItem)
	{
		if (!oItem)
			oItem = this.Items[this.currentIndex];
		if (!oItem)
			return;

		if (oItem.width <= this.fixedSize && oItem.height <= this.windowInnerSize.innerHeight - 100)
		{
			this.pBigPhoto.style.display = "none";
			BX.addClass(this.pMinimizeBut, 'photo-minimize-disabled');
			this.pMinimizeBut.title = this.MESS.bigPhotoDisabled;
		}
		else
		{
			this.pBigPhoto.style.display = "";
			BX.removeClass(this.pMinimizeBut, 'photo-minimize-disabled');
		}
	},

	CheckImageSize: function(oItem)
	{
		if (!oItem)
			oItem = this.Items[this.currentIndex];
		if (!oItem)
			return;

		var maxW = this.windowInnerSize.innerWidth - 30;
		var maxH = this.windowInnerSize.innerHeight - 100;

		if (this.Mode == 'fixed' && this.fixedSize < maxW)
			maxW = this.fixedSize;

		var res = this.FitInto(oItem.width, oItem.height, maxW, maxH);
		if (res.w == oItem.width && res.h == oItem.height)
		{
			this.pImage.style.width = "";
			this.pImage.style.height = "";
		}
		else
		{
			this.pImage.style.width = res.w + "px";
			this.pImage.style.height = res.h + "px";
		}

		if (this.Mode == "auto")
		{
			this.pPopupFooter.style.width = this.fixedSize + 'px';
			setTimeout(BX.proxy(this.AdjustControls, this), 1);
		}
	},

	FitInto: function(w, h, maxW, maxH)
	{
		var r = w / h;
		if (w > maxW)
		{
			w = maxW;
			h = w / r;
		}
		if (h > maxH)
		{
			h = maxH;
			w = h * r;
		}
		if (w > maxW)
		{
			w = maxW;
			h = w / r;
		}

		if (!w || isNaN(w))
			w = 0;
		if (!h || isNaN(h))
			h = 0;

		return {w: Math.round(w), h: Math.round(h)};
	},

	SetTheme: function(theme, bSave)
	{
		if (theme !== 'dark' &&  theme !== 'light')
			this.theme = this.theme == 'dark' ? 'light' : 'dark'; // Flip theme
		else
			this.theme = theme;

		if (this.theme == 'dark')
		{
			if (this.pThemeLink)
				this.pThemeLink.innerHTML = '<i></i><span class="photo-dark-theme-link-text">' + this.MESS.LightBG + '</span>';
			BX.addClass(this.pFixedOverlay, "photo-dark-theme");
		}
		else
		{
			if (this.pThemeLink)
				this.pThemeLink.innerHTML = '<i></i><span class="photo-dark-theme-link-text">' + this.MESS.DarkBG + '</span>';
			BX.removeClass(this.pFixedOverlay, "photo-dark-theme");
		}

		// Append css for dark theme to LHE in blog's comments
		if (this.useComments && this.commentsType == 'blog')
			this.SetThemeCSSForLHE();

		if (bSave !== false)
			this.SaveOption("theme", this.theme);
	},

	SetThemeCSSForLHE: function()
	{
		var pLEditor = window.oBlogComLHE;
		if (!pLEditor)
			return setTimeout(BX.proxy(this.SetThemeCSSForLHE, this, 400));

		var darkThemeLheCSS = "/*BXPH-CSS-START*/" +
			"body,body *{background-color: #464646!important; color: #FFFFFF;}\n" +
			"body blockquote.bx-quote {border: 1px solid #C0C0C0!important; background-color: #FFF4CA!important; color: #373737!important;}\n" +
			"/*BXPH-CSS-END*/";

		var css = pLEditor.systemCSS.replace(/\/\*BXPH-CSS-START\*\/(\s|\S)*\/\*BXPH-CSS-END\*\//ig, '');
		if (this.theme == 'dark')
			pLEditor.systemCSS = css + "\n" + darkThemeLheCSS;
		else
			pLEditor.systemCSS = css;

		pLEditor.SetEditorContent(pLEditor.GetContent());
	},

	ShowSource: function(e)
	{
		var
			oItem = this.Items[this.currentIndex],
			title = oItem.description || '',
			src = oItem.big_src || oItem.src,
			SrcWidth = screen.availWidth,
			SrcHeight = screen.availHeight,
			sizer = false;

		if (document.all)
			sizer = window.open("","","height=SrcHeight,width=SrcWidth,top=0,left=0,scrollbars=yes,fullscreen=yes");
		else
			sizer = window.open('', src,'width=SrcWidth,height=SrcHeight,menubar=no,status=no,location=no,scrollbars=yes,fullscreen=yes,directories=no,resizable=yes');

		sizer.document.write('<html><head>' +
			'<script>function SetBackGround(div){if (div)document.body.style.backgroundColor = div.style.backgroundColor;}</script>' +
			'<title>' + title + '</title>' +
			'<style>table div{width:18px; height:18px;}</style>' +
			'</head><body style="background-color:#000000;">' +
			'<table width="100%" height="96%" cellspacing="0" cellpadding="0" border="0">' +
			'<tr><td align="right">' +
			'<table cellspacing="2" cellpadding="0" border="0" align="center">' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="width:18px; height:18px; background-color:#FFFFFF;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#E5E5E5;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#CCCCCC;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#B3B3B3;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#999999;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#808080;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#666666;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#4D4D4D;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#333333;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#1A1A1A;"></div></td></tr>' +
			'<tr><td><div onmouseover="SetBackGround(this);" style="background-color:#000000;"></div></td></tr>' +
			'</table>' +
			'</td>' +
			'<td align="center"><img border="0" title="' + this.MESS.clickToClose + '" style="cursor:pointer; cursor:hand;" onclick="window.close();" src="' + src + '"></td></tr>' +
			'</table></body></html>'
		);
		return BX.PreventDefault(e);
	},

	OnAfterCurrentItemsLoaded: function()
	{
		BX.closeWait('photo_load_items');
		if (this.currentItem > 0 && this.ItemIndex[this.currentItem])
			this.OpenPopup(this.currentItem);
		BX.removeCustomEvent(this, 'OnAfterItemsLoaded', BX.proxy(this.OnAfterCurrentItemsLoaded, this));
	},

	OnMoreItemsLoaded: function(items)
	{
		if (items)
			this.HandleItems(items);

		this.AttachThumbnailsEvents();
	},

	AttachThumbnailsEvents: function()
	{
		var
			_this = this,
			res = this.pElementsCont.getElementsByTagName('a'),
			ii, l = res.length;

		for (ii = 0; ii < l; ii++)
			if (res[ii].id && res[ii].id.match(/photo\_(\d+)/gi))
				res[ii].onclick = function(e){_this.OpenPopup(parseInt(this.id.substr("photo_".length))); return BX.PreventDefault(e);};
	},

	OnItemShowed: function(oItem)
	{
		var bIncreaseCounter = oItem.bShowed ? 'N' : 'Y';

		var bGetRaitings = this.useRatings ? 'Y' : 'N';
		if (this.useRatings && typeof oItem.rating != 'undefined')
		{
			this.pRatingCont.innerHTML = oItem.rating;
			bGetRaitings = 'N';
		}

		if (bGetRaitings == 'Y')
			this.pRatingCont.innerHTML = '';

		if (bGetRaitings == 'Y' || !oItem.bShowed)
		{
			var _this = this;
			BX.ajax.get(
				this.responderUrl,
				{
					sessid: BX.bitrix_sessid(),
					ELEMENT_ID : oItem.id,
					AUTHOR_ID : oItem.author_id,
					sign: this.sign,
					checkParams: this.checkParams,
					reqParams: this.reqParams,
					increaseCounter: bIncreaseCounter,
					getRaiting: bGetRaitings,
					UCID: this.uniqueId,
					AJAX_CALL: 'Y'
				},
				function(result){
					setTimeout(function()
					{
						//_this.CheckActionPostUrl();

						if (bIncreaseCounter == 'Y')
							oItem.shows++;

						if (bGetRaitings == 'Y')
						{
							var
								res = '',
								indBegin = result.indexOf('<!--BX_PHOTO_RATING-->'),
								indEnd = result.indexOf('<!--BX_PHOTO_RATING_END-->');

							if (indBegin !== -1 && indEnd !== -1)
								res = result.substr(indBegin + '<!--BX_PHOTO_RATING-->'.length, indEnd - indBegin - '<!--BX_PHOTO_RATING-->'.length);
							else
								res = result;

							_this.pRatingCont.innerHTML = res;

							// For like (rating_main) - we have to rerender ratings for each photo loading
							if (!_this.cacheRaitingReq)
								oItem.rating = res;
						}
					}, 100);
				}
			);
			oItem.bShowed = true;
		}
	},

	OnResize: function()
	{
		if (!BX.browser.IsDoctype() && BX.browser.IsIE())
		{
			this.windowInnerSize = BX.GetWindowInnerSize(document);
			var d = (document.body.offsetHeight > this.windowInnerSize.innerHeight) ? 18 : 0;
			this.pFixedOverlay.style.width = (this.windowInnerSize.innerWidth + d)+ "px";
			this.pFixedOverlay.style.height = this.windowInnerSize.innerHeight + "px";
		}
		this.AdjustControls();
	},

	PreloadImages: function(LoadedItem)
	{
		if (this.reloadItemsOnload || !LoadedItem)
			return;

		if (!this.pPreloadImagesCont)
		{
			this.pPreloadImagesCont = document.body.appendChild(BX.create("DIV", {props: {className: "bxph-preload-cont"}}));
			this.arLoadedImg = {};
			this.ImgQueue = {};
		}

		this.PreloadPhoto(LoadedItem, 1); // One step next
		this.PreloadPhoto(LoadedItem, 2); // Two step next

		this.PreloadPhoto(LoadedItem, -1); // One step before
		this.PreloadPhoto(LoadedItem, -2); // One step before
	},

	PreloadPhoto: function(Item, d)
	{
		if (typeof d == 'undefined')
			d = 1;
		var ind = (parseInt(Item.index) || 0)  + d;

		if (this.Items && this.Items[ind])
		{
			var src = this.Items[ind].big_src || this.Items[ind].src;
			if (!this.arLoadedImg[src])
			{
				this.ImgQueue[src] = true;
				this.arLoadedImg[src] = true;

				if (this.ImageLoadingState != 'loading')
					this.PreloadAgent();
			}
		}
	},

	PreloadAgent: function(src)
	{
		var _this = this, src_i;
		if (src && this.ImgQueue[src])
		{
			this.srcIndex[src] = true;
			this.ImageLoadingState = 'wait';
			if (this.clearStateTimeout)
			{
				clearTimeout(this.clearStateTimeout);
				this.clearStateTimeout = null;
			}
		}

		var bLoaded = false;
		for (src_i in this.ImgQueue)
		{
			if (this.ImgQueue[src_i])
			{
				bLoaded = true;
				var pImage = this.pPreloadImagesCont.appendChild(BX.create("IMG", {props: {src: src_i, title: src_i}}));
				this.ImageLoadingState = 'loading';
				_this.ImgQueue[src_i] = null;
				delete _this.ImgQueue[src_i];

				pImage.onerror = pImage.onload = function(){	_this.PreloadAgent(this.title);};

				if (this.clearStateTimeout)
				{
					clearTimeout(this.clearStateTimeout);
					this.clearStateTimeout = null;
				}

				this.clearStateTimeout = setInterval(function()
				{
					_this.ImageLoadingState = 'wait';
					_this.PreloadAgent();
				}, 5000);
				break;
			}
		}

		if (!bLoaded)
		{
			this.ImageLoadingState = 'wait';
			if (this.clearStateTimeout)
			{
				clearTimeout(this.clearStateTimeout);
				this.clearStateTimeout = null;
			}
		}
	},

	CheckActionPostUrl: function()
	{
		if (!window['bxph_action_url_' + this.uniqueId] || this.actionPostUrl)
			return;

		if (top.oBXPhotoList && top.oBXPhotoList[this.uniqueId])
			top.oBXPhotoList[this.uniqueId].actionPostUrl = window['bxph_action_url_' + this.uniqueId];

		this.actionPostUrl = window['bxph_action_url_' + this.uniqueId];
	}
};

function BXTopSlider(pObj)
{
	this.thumbSize = 40;
	this.thumbContSize = 48;
	this.pTopSliderBut = pObj.pTopSliderBut;
	this.pScrollCont = pObj.pScrollCont;
	this.pScroller = pObj.pScroller;
	this.pObj = pObj;
	this.pWnd = pObj.pSlider;
	this.pSliderCont = pObj.pSliderCont;
	this.itemsIndex = {};
	this.itemsCount = pObj.itemsCount;
	this.startItemIndex = pObj.currentIndex;
	this.uniqueId = pObj.uniqueId;

	this.carretWidth = 22;

	this.pTopSliderBut.onclick = BX.proxy(this.Show, this);
	this.bOpened = false;
	this.Init();
}

BXTopSlider.prototype = {
	Init: function()
	{
		this.extraSize = 30; // pixels
		this.currentIndex = this.startItemIndex;
		this.AdjustScroller();
		this.BuildItems();

		this.HandleItems(this.pObj.Items);

		this.pScroller.onmousedown = BX.proxy(this.StartMoveCarret, this);
		this.pWnd.onmousedown = BX.proxy(this.StartMoveSlider, this);
		this.pScrollCont.onmousedown =  BX.proxy(this.ScrollerClick, this);

		if (this.pSliderCont.addEventListener)
			this.pSliderCont.addEventListener('DOMMouseScroll', BX.proxy(this.OnMouseWheel, this), false);

		BX.bind(this.pSliderCont, 'mousewheel', BX.proxy(this.OnMouseWheel, this));

		if (this.pObj.userSettings.show_top_slider == 'Y')
			this.Show(true, false, true);
	},

	StartMoveSlider: function(e)
	{
		if (this.bNotEnoughItems)
			return;

		this.ClearIntervals();
		if(!e)
			e = window.event;
		this.bMoveSlider = true;
		this.bSliderMoved = false;
		this.pSliderContPos = BX.pos(this.pSliderCont);
		this.wndSize = BX.GetWindowScrollPos();
		this.startX = e.clientX + this.wndSize.scrollLeft;
		this.selectorStartLeft = parseInt(this.pWnd.style.left) || 0;
		this.minLeft = this.pSliderContPos.width - this.pObj.itemsCount * this.thumbContSize;

		var _this = this;
		this.x0 = this.x1 = this.x2 = parseInt(this.pWnd.style.left) || 0;
		this.time0 = new Date().getTime();
		this.zeroSpeedCount = 0;
		this.time = 10;

		if (!this.bOpened)
			this.Show(true);

		this.measureSpeedInt  = setInterval(BX.proxy(this.MeasureSpeed, this), this.time);

		BX.bind(document, "mousemove", BX.proxy(this.MoveSlider, this));
		BX.bind(document, "mouseup", BX.proxy(this.StopMoveSlider, this));
	},

	MeasureSpeed: function()
	{
		this.x1 = this.x2;
		this.x2 = parseInt(this.pWnd.style.left);

		var speed = (this.x2 - this.x1);
		if (speed == 0)
			this.zeroSpeedCount++;
		else
			this.zeroSpeedCount = 0;

		if (!this.bMoveSlider)
		{
			clearInterval(this.measureSpeedInt);
			this.time1 = new Date().getTime();
			var avSpeed = ((this.x2 - this.x0) / (this.time1 - this.time0));
			avSpeed  = avSpeed * this.time * 1.2;

			this.t = 1;
			this.startV = avSpeed;
			this.v = avSpeed;
			var
				_this = this,
				a = 0.3,
				a = this.v > 0 ? -a : a;

			if (Math.abs(avSpeed) > 0 && this.zeroSpeedCount < 50)
			{
				this.stoppieInterval = setInterval(function()
				{
					var t = ++_this.t;
					var curSpeed = _this.v + a * t;
					var x = _this.secondX + _this.v * t + a * t * t / 2;
					x = Math.round(x * 10) / 10;

					if (_this.startV > 0 && curSpeed <= 0 || _this.startV < 0 && curSpeed >= 0)
						return clearInterval(_this.stoppieInterval);

					if (x > _this.extraSize)
					{
						x = _this.extraSize;
						t = 100; // Just stop
					}
					if (x < _this.minLeft - _this.extraSize)
					{
						x = _this.minLeft - _this.extraSize;
						t = 100; // Just stop
					}

					_this.pWnd.style.left = x + "px";
					_this.CheckCurrentShownItems();
					_this.AdjustCarret(x);

					if (t > 50)
					{
						_this.PullBack();
						clearInterval(_this.stoppieInterval);
					}
				}, this.time);
			}

		}
	},

	PullBack: function()
	{
		var
			x = parseInt(this.pWnd.style.left) || 0,
			time = this.time,
			dx = (x > 0) ? -1 : 1,
			_this = this,
			count = 1;

		if (this.bMoveSlider || x <= 0 && x >= this.minLeft)
			return;

		if (this.pullBackInterval)
			clearInterval(	this.pullBackInterval);

		this.pullBackInterval = setInterval(function()
		{
			var left = parseInt(_this.pWnd.style.left);
			left += count * dx;

			if (dx > 0 && left > _this.minLeft)
				left = _this.minLeft;
			else if (dx < 0 && left < 0)
				left = 0;

			_this.pWnd.style.left = left + 'px';
			if (left == _this.minLeft || left == 0)
				clearInterval(	_this.pullBackInterval);
			count++;
		}, time);
	},

	MoveSlider: function(e)
	{
		if (this.bNotEnoughItems)
			return;

		this.bSliderMoved = true;
		if (this.bMoveSlider)
		{
			if(!e)
				e = window.event;
			var x = parseInt(e.clientX) + parseInt(this.wndSize.scrollLeft);
			var offsetX = x - this.startX;

			var newLeft = this.selectorStartLeft + offsetX;
			if (newLeft > this.extraSize)
				newLeft = this.extraSize;
			if (newLeft < this.minLeft - this.extraSize)
				newLeft = this.minLeft - this.extraSize;

			if (!this.firstTime || !this.firstX)
			{
				this.firstTime =  new Date().getTime();
				this.firstX = newLeft;
			}
			else
			{
				this.firstTime = this.secondTime;
				this.secondTime = new Date().getTime();

				this.firstX = this.secondX;
				this.secondX = newLeft;
			}

			this.pWnd.style.left = newLeft + "px";
			this.AdjustCarret(newLeft);
			this.CheckCurrentShownItems();
		}
	},

	StopMoveSlider: function()
	{
		this.bMoveSlider = false;

		if (this.measureSpeedInt)
			this.MeasureSpeed();

		BX.unbind(document, "mousemove", BX.proxy(this.MoveSlider, this));
		BX.unbind(document, "mouseup", BX.proxy(this.StopMoveSlider, this));

		this.PullBack();
	},

	StartMoveCarret: function()
	{
		if (!this.bOpened)
			this.Show(true);

		if (this.bNotEnoughItems)
			return;

		this.ClearIntervals();

		this.bScroll = true;
		this.pScrollContPos = BX.pos(this.pScrollCont);
		this.wndSize = BX.GetWindowScrollPos();

		BX.addClass(this.pScroller, "photo-scroll-wheel-active");
		BX.bind(document, "mousemove", BX.proxy(this.MoveCarret, this));
		BX.bind(document, "mouseup", BX.proxy(this.StopMoveCarret, this));
	},

	MoveCarret: function(e)
	{
		if (this.bScroll && !this.bNotEnoughItems)
		{
			if(!e)
				e = window.event;
			var dx = e.clientX + this.wndSize.scrollLeft - this.pScrollContPos.left;
			this.SetCarret(dx);
		}
	},

	StopMoveCarret: function()
	{
		this.bScroll = false;
		BX.removeClass(this.pScroller, "photo-scroll-wheel-active");
		BX.unbind(document, "mousemove", BX.proxy(this.MoveCarret, this));
		BX.unbind(document, "mouseup", BX.proxy(this.StopMoveCarret, this));
	},

	AdjustScroller: function()
	{
		if (this.pObj.itemsCount > 0)
		{
			var sliderWidth = parseInt(this.pSliderCont.parentNode.offsetWidth);
			this.pSliderContPos = BX.pos(this.pSliderCont);
			this.scrollerWidth = parseInt(this.pScrollCont.offsetWidth);
			this.deltaWidth = this.pObj.itemsCount * this.thumbContSize - parseInt(this.pSliderCont.parentNode.offsetWidth);
			this.scrollRatio = this.deltaWidth / this.scrollerWidth;
			this.minLeft = sliderWidth - this.pObj.itemsCount * this.thumbContSize;
			this.bNotEnoughItems = this.pObj.itemsCount < sliderWidth / this.thumbContSize;
			this.pScrollContPos = BX.pos(this.pScrollCont);
			this.pWnd.style.width = this.bNotEnoughItems ? '100%' : '';
		}
	},

	ScrollerClick: function(e)
	{
		if (!this.bNotEnoughItems)
		{
			if(!e)
				e = window.event;
			this.StartMoveCarret(e);
			this.SetCarret(e.clientX + this.wndSize.scrollLeft - this.pScrollContPos.left);
		}
	},

	SetCarret: function(x)
	{
		if (x < 0 || this.bNotEnoughItems)
			x = 0;

		// On each pixel moved carret we move slider on some _offset_
		var offsetX;
		if (x >= this.pScrollContPos.width - this.carretWidth)
		{
			x = this.pScrollContPos.width - this.carretWidth;
			offsetX = Math.round(this.pScrollContPos.width * this.scrollRatio * 10) / 10;
		}
		else
		{
			offsetX = Math.round(x * this.scrollRatio * 10) / 10;
		}

		offsetX = -offsetX;

		this.AdjustCarret(offsetX);
		this.pWnd.style.left = offsetX + "px";
		this.CheckCurrentShownItems();
	},

	AdjustCarret: function(left)
	{
		if (this.bNotEnoughItems)
		{
			this.pScroller.style.display = "none";
			return;
		}
		else
		{
			this.pScroller.style.display = "";
		}

		if (typeof left == 'undefined')
			left = parseInt(this.pWnd.style.left) || 0;

		if (left > 0)
			left = 0;
		if (left < this.minLeft)
			left = this.minLeft;

		var carretX = Math.abs(Math.round(10 * left / this.scrollRatio) / 10);
		if (carretX > this.pScrollContPos.width - this.carretWidth)
			carretX = this.pScrollContPos.width - this.carretWidth;

		this.pScroller.style.left = carretX + "px";
	},

	ClearIntervals: function()
	{
		if (this.pullBackInterval)
			clearInterval(	this.pullBackInterval);
		if (this.measureSpeedInt)
			clearInterval(this.measureSpeedInt);
		if (this.stoppieInterval)
			clearInterval(this.stoppieInterval);
	},

	HandleItems: function(Items)
	{
		for (i in Items)
		{
			Item = Items[i];
			if (Item.id && this.itemsIndex[Item.index])
			{
				if (typeof Item.thumb_src == 'undefined')
				{
					Item.thumb_src = Item.src;
					Item.thumb_height = Item.height;
					Item.thumb_width = Item.width;
				}

				if (Item.thumb_src && Item.thumb_src != undefined)
				{
					this.itemsIndex[Item.index].pImg.src = Item.thumb_src;
					BX.removeClass(this.itemsIndex[Item.index].pLink, 'photo-wait');
					this.AdjustThumb(this.itemsIndex[Item.index].pImg, Item.thumb_width, Item.thumb_height);
				}
			}
		}
	},

	RecalcItems: function()
	{
		if (this.itemsCount == this.pObj.itemsCount + 1)
		{
			this.itemsCount = this.pObj.itemsCount;
			this.pWnd.removeChild(this.itemsIndex[this.itemsCount].pLink);
			this.itemsIndex[this.itemsCount] = null;
			delete this.itemsIndex[this.itemsCount];

			this.HandleItems(this.pObj.Items);
		}
	},

	BuildItems: function()
	{
		var i, _this = this, pLink, pImg, pDiv;
		for (i = 0; i < this.pObj.itemsCount; i++)
		{
			pLink = this.pWnd.appendChild(BX.create("A", {props:{id: 'bxphoto_t_' + i, className: "photo-preview photo-wait", href: "javascript: void(0)"}}));
			pDiv = pLink.appendChild(BX.create("DIV", {props:{className: 'photo-preview-inner'}, style: {width: this.thumbSize + 'px', height: this.thumbSize + 'px'}}));
			pImg = pDiv.appendChild(BX.create("IMG", {props:{src: '/bitrix/images/1.gif'}, style: {width: this.thumbSize + 'px', height: this.thumbSize + 'px'}}));

			this.itemsIndex[i] = {
				pLink: pLink,
				pImg: pImg
			};

			pLink.onclick = function()
			{
				if (!_this.bSliderMoved)
					_this.pObj.ShowItem(parseInt(this.id.substr('bxphoto_t_'.length)), false, false, true /*bAffectTopSlider*/);
			};
			this.pObj.SetUnselectable([pLink, pLink.firstChild]);
		}
	},

	CleanItems: function()
	{
		for (i = 0; i < this.pObj.itemsCount; i++)
			BX.addClass(this.itemsIndex[i].pLink, 'photo-wait');
	},

	OnMouseWheel: function(event)
	{
		if (this.bNotEnoughItems)
			return BX.PreventDefault(event);

		if (!event)
			event = window.event;

		var wheelDelta = 0;
		if (event.wheelDelta)
			wheelDelta = event.wheelDelta / 120;
		else if (event.detail)
			wheelDelta = - event.detail / 3;

		var
			startLeft = parseInt(this.pWnd.style.left) || 0,
			newLeft = startLeft + wheelDelta * this.thumbContSize * 1;

		newLeft = Math.round(newLeft * 10) / 10;
		if (newLeft > this.extraSize)
			newLeft = this.extraSize;
		if (newLeft < this.minLeft - this.extraSize)
			newLeft = this.minLeft - this.extraSize;

		this.pWnd.style.left = newLeft + "px";
		this.CheckCurrentShownItems();
		this.AdjustCarret(newLeft);

		if (this.mouseWheelTimeout)
			clearTimeout(this.mouseWheelTimeout);
		this.mouseWheelTimeout = setTimeout(BX.proxy(this.PullBack, this), 100);

		return BX.PreventDefault(event);
	},

	CheckCurrentShownItems: function()
	{
		if (this.pObj.reloadItemsOnload || !this.pObj.itemsPageSize)
			return;

		var
			left = parseInt(this.pWnd.style.left) || 0,
			startIndex = - Math.round(left / this.thumbContSize);

		if (startIndex < 1)
			startIndex = 1;

		if(!this.pSliderContPos)
			this.pSliderContPos = BX.pos(this.pSliderCont);
		var endIndex = Math.round(startIndex + this.pSliderContPos.width / this.thumbContSize);
		if (endIndex > this.pObj.itemsCount)
			endIndex = this.pObj.itemsCount;

		var
			page,
			pageStart = Math.ceil(startIndex / this.pObj.itemsPageSize),
			pageEnd = Math.ceil(endIndex / this.pObj.itemsPageSize);

		for (page = pageStart; page <= pageEnd; page++)
			this.pObj.LoadPage(page);
	},

	Show: function(bShow, bSave, bFast)
	{
		if (bShow !== true && bShow !== false)
			bShow = !this.bOpened;

		bFast = bFast === true;
		if (BX.browser.IsIE() && !BX.browser.IsIE9())
			bFast = true;

		if (bShow)
		{
			if (bFast)
			{
				this.pSliderCont.style.display = "block";
				this.pObj.pPopupTop.style.marginBottom = this.thumbContSize + 'px';
			}
			BX.addClass(this.pTopSliderBut, "photo-slider-button-active");
			this.AdjustScroller();
		}
		else // Hide
		{
			if (bFast)
				this.pObj.pPopupTop.style.marginBottom = '';

			this.pSliderCont.style.display = "none";
			BX.removeClass(this.pTopSliderBut, "photo-slider-button-active");
		}
		this.bOpened = bShow;

		if (!bFast)
		{
			if (this.showInt)
				clearInterval(this.showInt);

			var
				_this = this,
				dx = 5,
				count = 1,
				marg = parseInt(this.pObj.pPopupTop.style.marginBottom) || 0,
				bFinish = false;

			this.showInt = setInterval(
				function(){
					if (bShow)
					{
						marg += dx * count;
						if (marg >= _this.thumbContSize)
						{
							marg = _this.thumbContSize;
							bFinish = true;
						}
					}
					else
					{
						marg -= dx * count;
						if (marg <= 0)
						{
							marg = 0;
							bFinish = true;
						}
					}
					_this.pObj.pPopupTop.style.marginBottom = marg + 'px';

					if (bFinish)
					{
						if (bShow)
							_this.pSliderCont.style.display = "block";
						clearInterval(_this.showInt);
					}
					count++;
				}, 40
			);
		}

		if (bSave !== false)
			this.pObj.SaveOption("show_top_slider", this.bOpened ? "Y" : "N");

		this.CheckCurrentShownItems();
	},

	// Highlight the item in the top slider and scroll the slider if index off the boundaries
	SelectItem: function(Item)
	{
		if (!this.itemsIndex[Item.index])
			return;

		var left = 0;
		this.AdjustScroller();
		if (this.bNotEnoughItems)
		{
			this.pWnd.style.left = "0px";
		}
		else
		{
			left = parseInt(this.pWnd.style.left) || 0;
			var
				startIndex = - Math.round(left / this.thumbContSize),
				endIndex = Math.round(startIndex + this.pSliderContPos.width / this.thumbContSize) - 1;

			if (Item.index < startIndex || Item.index > endIndex)
			{
				if (Item.index < startIndex)
					left = - parseInt(Item.index) * this.thumbContSize;
				else
					left = this.pSliderContPos.width - (parseInt(Item.index) + 1) * this.thumbContSize;

				if (left > 0)
					left = 0;
				if (left < this.minLeft)
					left = this.minLeft;

				this.pWnd.style.left = left + "px";
			}
		}
		this.AdjustCarret(left);

		if (typeof this.selectedIndex != 'undefined' && this.itemsIndex[this.selectedIndex].pLink)
			BX.removeClass(this.itemsIndex[this.selectedIndex].pLink, "photo-preview-selected");

		BX.addClass(this.itemsIndex[Item.index].pLink, "photo-preview-selected");
		this.selectedIndex = Item.index;

		//setTimeout(BX.proxy(this.CheckCurrentShownItems, this), 1000);
	},

	UpdateThumbnail: function(index)
	{
		var Item = this.itemsIndex[index];
		if (Item)
		{
			var oItem = this.pObj.Items[index];
			if (typeof oItem.thumb_src == 'undefined')
			{
				oItem.thumb_src = oItem.src;
				oItem.thumb_height = oItem.height;
				oItem.thumb_width = oItem.width;
			}

			if (oItem.thumb_src && oItem.thumb_src != undefined)
			{
				Item.pImg.src = oItem.thumb_src;
				this.AdjustThumb(Item.pImg, oItem.thumb_width, oItem.thumb_height);
			}
			BX.removeClass(Item.pLink, 'photo-wait');
		}
	},

	AdjustThumb: function(img, w, h)
	{
		if (!w || !h)
			return;
		var r = w / h;
		if (r > 1)
		{
			img.style.height = this.thumbSize + "px";
			img.style.width = Math.round(this.thumbSize * r /* width*/) + "px";
			img.style.left = Math.round((this.thumbSize - this.thumbSize * r /* width*/) / 2) + "px";
			img.style.top = 0;
		}
		else
		{
			img.style.width = this.thumbSize + "px";
			img.style.height = Math.round(this.thumbSize / r /*height*/) + "px";
			img.style.top = Math.round((this.thumbSize - this.thumbSize / r /* height*/) / 2) + "px";
			img.style.left = 0;
		}
	}
};

// Controll which used to display thumbs, open popups, sort thumbs
window.BXPhotoList = function(Params)
{
	this.actionUrl = Params.actionUrl;
	this.navName = Params.navName;
	this.currentPage = parseInt(Params.currentPage);
	this.pageCount = parseInt(Params.pageCount);
	this.uniqueId = Params.uniqueId;

	if (Params.morePhotoNav == "Y")
	{
		this.pMorePhotosLink = BX('photo-more-photo-link-' + this.uniqueId);
		this.pMorePhotosCont = BX('photo-more-photo-link-cont-' + this.uniqueId);
		this.pMorePhotosLink.onclick = BX.proxy(this.LoadMorePhotos, this);
	}
	this.pElementsCont = Params.pElementsCont;
	this.initDragSorting = Params.initDragSorting == 'Y';
	this.canModerate = !!Params.canModerate;

	this.maxDelta = 10;
	this.thumbSize = parseInt(Params.thumbSize);
	this.thumbHeight = this.thumbWidth = this.thumbSize + 8; // thumb size + margin to setermine correct position and size

	this.oItems = [];
	this.oItemIndex = {};

	this.HandleItems(Params.items);

	if (this.initDragSorting)
	{
		this.pContPos = BX.pos(this.pElementsCont);
		var w = this.pElementsCont.parentNode.offsetWidth;
		if (!w || w < 0)
			w = this.pContPos.width;
		if (!this.matrixX)
			this.matrixX = Math.floor(w / this.thumbWidth);

		this.InitSort();
	}
};

window.BXPhotoList.prototype = {
	HandleItems: function(items)
	{
		for (var i in items)
		{
			items[i].index = parseInt(items[i].index);
			this.oItems[items[i].index] = items[i];
			this.oItemIndex[items[i].id] = items[i].index;
		}

		if (items[i].active != "Y" && this.canModerate)
		{

		}
	},

	LoadMorePhotos: function()
	{
		var params = {
			UCID: this.uniqueId,
			sessid: BX.bitrix_sessid(),
			return_array : 'Y',
			get_elements_html: 'Y'
		};

		params[this.navName] = ++this.currentPage;
		if (this.currentPage >=  this.pageCount)
			this.pMorePhotosCont.style.display = "none";

		BX.addClass(this.pMorePhotosCont, "photo-show-more-wait");
		var _this = this;
		window.bxphres = false;
		BX.ajax.get(this.actionUrl, params, function(result)
		{
			setTimeout(function(){
				_this.CheckActionPostUrl();
				_this.HandleItems(window.bxphres.items);
				BX.removeClass(_this.pMorePhotosCont, "photo-show-more-wait");
				_this.pElementsCont.innerHTML += window.bxphres.elementsHTML;
				_this.currentPage = parseInt(window.bxphres.currentPage);

				if (_this.initDragSorting)
					_this.InitSort();

				BX.onCustomEvent(window, 'onMoreItemsLoaded', [window.bxphres.items]);
			}, 100);
		});
		return false;
	},

	ShowLoadPhotoWait: function(bShow)
	{
		//this.pComWaiter.style.display = bShow ? "" : "none";
	},

	InitSort: function()
	{
		var
			_this = this,
			ii, id, ind = 0,
			res = this.pElementsCont.getElementsByTagName('a');

		// TODO: handle window.onresize
		this.pElementsCont.style.width = ((this.thumbWidth * this.matrixX) + 8) + "px";

		this.Items = [];
		this.ItemsIndex = {};
		this.sortMatrix = {};
		this.curItemsCount = 0;
		this.time = 30;
		this.maxSortValue = 0;
		this.sortFieldStep = 5;

		for (ii = 0; ii < res.length; ii++)
		{
			if (!res[ii].id || !res[ii].id.match(/photo\_(\d+)/gi) || !this.oItems[ind])
				continue;

			id = parseInt(res[ii].id.substr("photo_".length));
			res[ii].onmousedown = function(e){_this.StartDragItem(e, parseInt(this.id.substr("photo_".length)));return false;};

			this.Items[ind] = {
				id: id,
				pWnd: res[ii].parentNode,
				sort: ind,
				origSort: ind,
				curSortField: parseInt(this.oItems[ind].sort),
				sortField: (ind + 1) * this.sortFieldStep
			};

			this.ItemsIndex[id] = ind;
			this.sortMatrix[id] = ind;
			this.curItemsCount++;
			ind++;

			if (this.maxSortValue < parseInt(this.oItems[ii].sort))
				this.maxSortValue = parseInt(this.oItems[ii].sort);
		}
	},

	StartDragItem: function(e, id)
	{
		if (!e)
			e = window.event;
		this.bSorting = true;
		this.wndSize = BX.GetWindowScrollPos();

		this.startX = e.clientX;
		this.startY = e.clientY;

		this.movedItemId = id;
		this.movedItem = this.GetItemById(id);
		this.movedItem.curSortIndex = false;
		this.pItemPos = BX.pos(this.movedItem.pWnd);

		var x = e.clientX + this.wndSize.scrollLeft - this.pContPos.left;
		var y = e.clientY + this.wndSize.scrollTop - this.pContPos.top;

		var absX = e.clientX + this.wndSize.scrollLeft;
		var absY = e.clientY + this.wndSize.scrollTop;

		this.deltaMovedX = absX - this.pItemPos.left;
		this.deltaMovedY = absY - this.pItemPos.top;

		BX.bind(document, "mousemove", BX.proxy(this.DragItem, this));
		BX.bind(document, "mouseup", BX.proxy(this.StopDragItem, this));

		if (this.SaveOrderTimeout)
			clearTimeout(this.SaveOrderTimeout);
	},

	DragItem: function(e)
	{
		if (this.bSorting)
		{
			if(!e)
				e = window.event;
			if (!this.bDragItem && (Math.abs(e.clientX - this.startX) > this.maxDelta || Math.abs(e.clientY - this.startY) > this.maxDelta))
			{
				this.bDragItem = true;
				this.bWasJustDragged = true;
				this.movedItem.oldParrent = this.movedItem.pWnd.parentNode;
				BX.addClass(this.movedItem.pWnd, 'photo-item-cont-drag');
				document.body.appendChild(this.movedItem.pWnd);
			}

			if (this.bDragItem)
			{
				var absX = e.clientX + this.wndSize.scrollLeft;
				var absY = e.clientY + this.wndSize.scrollTop;
				var x = absX - this.pContPos.left;
				var y = absY - this.pContPos.top;

				// Move abs posed photo with cursor
				this.movedItem.pWnd.style.left = (absX - this.deltaMovedX) + 'px';
				this.movedItem.pWnd.style.top = (absY - this.deltaMovedY) + 'px';

				// Calculate new index corresponding to photo-matrix
				var indexX = Math.ceil(x / this.thumbWidth);
				var indexY = Math.ceil(y / this.thumbHeight);
				if (indexX < 0)
					indexX = 0;
				if (indexY < 0)
					indexY = 0;
				var newIndex = (indexY - 1) * this.matrixX + indexX - 1;
				if (newIndex > this.curItemsCount - 1)
					newIndex = this.curItemsCount - 1;

				//document.title = newIndex;

				this.PutItemToNewPlace(this.movedItemId, newIndex);
			}
			return BX.PreventDefault(e);
		}
	},

	StopDragItem: function(e)
	{
		if (this.bDragItem)
		{
			if (!e)
				e = window.event;
			BX.removeClass(this.movedItem.pWnd, 'photo-item-cont-drag');

			var i, l = this.pElementsCont.childNodes.length, index = 0, el;
			if (this.pNewEmptyPlace.parentNode)
				this.pNewEmptyPlace.parentNode.removeChild(this.pNewEmptyPlace);
			if (this.pNewEmptyPlace2.parentNode)
				this.pNewEmptyPlace2.parentNode.removeChild(this.pNewEmptyPlace2);

			for (i = 0; i < l; i++)
			{
				el = this.pElementsCont.childNodes[i];
				if (!BX.hasClass(el, 'photo-item-cont'))
					continue;

				if(this.movedItem.curSortIndex == this.curItemsCount - 1 && index == this.movedItem.curSortIndex - 1)
				{
					// Last item
					this.pElementsCont.appendChild(this.movedItem.pWnd);
					break;
				}

				if (index == this.movedItem.curSortIndex)
				{
					// All items except last
					this.pElementsCont.insertBefore(this.movedItem.pWnd, el);
					break;
				}
				index++;
			}

			this.SaveSortingOrder();
		}

		this.bSorting = false;
		this.bDragItem = false;

		BX.unbind(document, "mousemove", BX.proxy(this.DragItem, this));
		BX.unbind(document, "mouseup", BX.proxy(this.StopDragItem, this));

		_this = this;
		setTimeout(function(){_this.bWasJustDragged = false;}, 100);

		if (Math.abs(e.clientX - this.startX) > this.maxDelta || Math.abs(e.clientY - this.startY) > this.maxDelta)
			return BX.PreventDefault(e);
	},

	SaveSortingOrder: function()
	{
		if (this.SaveOrderTimeout)
			clearTimeout(this.SaveOrderTimeout);

		BX.closeWait('photo_save_sort_items');
		BX.showWait('photo_save_sort_items');

		this.SaveOrderTimeout = setTimeout(BX.proxy(this.SaveSortingOrderNow, this), 2000);
	},

	SaveSortingOrderNow: function()
	{
		var i, l = this.pElementsCont.childNodes.length, el, ind = 0, id, Item;
		var params = {
			UCID: this.uniqueId,
			sessid: BX.bitrix_sessid(),
			photo_list_action: 'save_sort_order',
			pio: {}
		};

		for (i = 0; i < l; i++)
		{
			el = this.pElementsCont.childNodes[i];
			if (el.id && el.id.match(/photo_cont_(\d+)/gi))
			{
				id = parseInt(el.id.substr("photo_cont_".length));
				Item = this.GetItemById(id);

				Item.sortField = (ind + 1) * this.sortFieldStep;
				if (Item.curSortField != Item.sortField)
					params.pio[id] = Item.sortField;
				Item.curSortField = Item.sortField;

				ind++;
			}
		}

		BX.ajax.get(this.actionUrl, params, function(result){BX.closeWait('photo_save_sort_items');});
	},

	PutItemToNewPlace: function(id, newIndex)
	{
		var oItem = this.GetItemById(id);
		if (oItem.curSortIndex === newIndex)
			return;

		oItem.curSortIndex = newIndex;
		var i, l = this.pElementsCont.childNodes.length, index = 0, el;
		for (i = 0; i < l; i++)
		{
			el = this.pElementsCont.childNodes[i];
			if (!BX.hasClass(el, 'photo-item-cont'))
				continue;

			if(newIndex == this.curItemsCount - 1 && index == newIndex - 1)
			{
				// Last item
				this.pElementsCont.appendChild(this.ShowNewEmptyPlace());
				break;
			}

			if (index == newIndex)
			{
				// All items except last
				this.pElementsCont.insertBefore(this.ShowNewEmptyPlace(), el);
				break;
			}
			index++;
		}
	},

	GetItemById:function(id)
	{
		if (typeof this.ItemsIndex[id] != 'undefined' && this.Items[this.ItemsIndex[id]])
			return this.Items[this.ItemsIndex[id]];

		return false;
	},

	ShowNewEmptyPlace: function()
	{
		this.bFirstEmptyPlace = !this.bFirstEmptyPlace;

		if (!this.pNewEmptyPlace)
			this.pNewEmptyPlace = BX.create("DIV", {props: {className: "photo-new-empty-place"}, style: {width: '0px', height: this.thumbHeight + 'px'}});
		if (!this.pNewEmptyPlace2)
			this.pNewEmptyPlace2 = BX.create("DIV", {props: {className: "photo-new-empty-place svd"}, style: {width: '0px', height: this.thumbHeight + 'px'}});

		if (this.showNewEmpty)
			clearInterval(this.showNewEmpty);
		if (this.hideNewEmpty)
			clearInterval(this.hideNewEmpty);

		var
			risingCont = this.bFirstEmptyPlace ? this.pNewEmptyPlace : this.pNewEmptyPlace2,
			fadingCont = this.bFirstEmptyPlace ? this.pNewEmptyPlace2 : this.pNewEmptyPlace,
			dx = Math.round(this.thumbWidth * 10 / 5) / 10,
			count = 0,
			_this = this,
			maxW = this.thumbWidth,
			bClear = false;
			bClear1 = false;
			w = 0,
			w1 = fadingCont.parentNode ? (parseInt(fadingCont.style.width) || parseInt(fadingCont.offsetWidth)) : 0;

		if (!fadingCont.parentNode)
		{
			risingCont.style.width = maxW + 'px';
			bClear = bClear1 = true;
		}

		this.showNewEmpty = setInterval(function()
		{
			if (!bClear)
			{
				w += 1 * dx;
				if (w >= maxW)
				{
					w = maxW;
					bClear = true;
				}
				risingCont.style.width = w + 'px';
			}

			if (!bClear1)
			{
				w1 -= 1 * dx;
				if (w1 <= 0 )
				{
					w1 = 0;
					if (fadingCont.parentNode)
						fadingCont.parentNode.removeChild(fadingCont);
					bClear1 = true;
				}
				fadingCont.style.width = w1 + 'px';
			}

			if (bClear && bClear1)
				clearInterval(_this.showNewEmpty);

			count++;
		}, this.time);

		return risingCont;
	},

	DeleteItem: function(e)
	{
		// return BX.PreventDefault(e);
	},

	ActivateItem: function(e)
	{
		// return BX.PreventDefault(e);
	},

	CheckActionPostUrl: function()
	{
		if (!window['bxph_action_url_' + this.uniqueId] || this.actionPostUrl)
			return;

		if (top.oBXPhotoSlider && top.oBXPhotoSlider[this.uniqueId])
			top.oBXPhotoSlider[this.uniqueId].actionPostUrl = window['bxph_action_url_' + this.uniqueId];

		this.actionPostUrl = window['bxph_action_url_' + this.uniqueId];
	}
};

})(window);