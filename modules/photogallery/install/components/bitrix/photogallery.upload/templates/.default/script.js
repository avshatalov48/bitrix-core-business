;(function(window){
	if (BX["UploaderTemplateThumbnails"])
		return false;

	function ColorPicker(val)
	{
		this.bCreated = false;
		this.bOpened = false;
		this.zIndex = 5000;
		this.pWnd = BX.create("DIV", {props: {className: "bxiu-color-but bxiu-but"}});
		this.pWnd.style.backgroundColor = val;

		var _this = this;
		this.pWnd.onmousedown = function(e){_this.OnClick(e, this)};
	}

	ColorPicker.prototype = {
		Create: function ()
		{
			var _this = this;
			this.pColCont = document.body.appendChild(BX.create("DIV", {props: {className: "wm-colpick-cont"}, style: {zIndex: this.zIndex}}));

			var
				arColors = ['#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF', '#EBEBEB', '#E1E1E1', '#D7D7D7', '#CCCCCC', '#C2C2C2', '#B7B7B7', '#ACACAC', '#A0A0A0', '#959595',
				'#EE1D24', '#FFF100', '#00A650', '#00AEEF', '#2F3192', '#ED008C', '#898989', '#7D7D7D', '#707070', '#626262', '#555', '#464646', '#363636', '#262626', '#111', '#000000',
				'#F7977A', '#FBAD82', '#FDC68C', '#FFF799', '#C6DF9C', '#A4D49D', '#81CA9D', '#7BCDC9', '#6CCFF7', '#7CA6D8', '#8293CA', '#8881BE', '#A286BD', '#BC8CBF', '#F49BC1', '#F5999D',
				'#F16C4D', '#F68E54', '#FBAF5A', '#FFF467', '#ACD372', '#7DC473', '#39B778', '#16BCB4', '#00BFF3', '#438CCB', '#5573B7', '#5E5CA7', '#855FA8', '#A763A9', '#EF6EA8', '#F16D7E',
				'#EE1D24', '#F16522', '#F7941D', '#FFF100', '#8FC63D', '#37B44A', '#00A650', '#00A99E', '#00AEEF', '#0072BC', '#0054A5', '#2F3192', '#652C91', '#91278F', '#ED008C', '#EE105A',
				'#9D0A0F', '#A1410D', '#A36209', '#ABA000', '#588528', '#197B30', '#007236', '#00736A', '#0076A4', '#004A80', '#003370', '#1D1363', '#450E61', '#62055F', '#9E005C', '#9D0039',
				'#790000', '#7B3000', '#7C4900', '#827A00', '#3E6617', '#045F20', '#005824', '#005951', '#005B7E', '#003562', '#002056', '#0C004B', '#30004A', '#4B0048', '#7A0045', '#7A0026'],
				row, cell, colorCell,
				tbl = BX.create("TABLE", {props: {className: 'wm-colpic-tbl'}}),
				i, l = arColors.length;

			row = tbl.insertRow(-1);
			cell = row.insertCell(-1);
			cell.colSpan = 8;
			var defBut = cell.appendChild(BX.create("SPAN", {props: {className: 'wm-colpic-def-but'}, text: BX.message("IUDefaultColor")}));
			defBut.onmouseover = function()
			{
				this.className = 'wm-colpic-def-but wm-colpic-def-but-over';
				colorCell.style.backgroundColor = '#FF0000';
			};
			defBut.onmouseout = function(){this.className = 'wm-colpic-def-but';};
			defBut.onmousedown = function(e){_this.Select('#FF0000');};

			colorCell = row.insertCell(-1);
			colorCell.colSpan = 8;
			colorCell.className = 'wm-color-inp-cell';
			colorCell.style.backgroundColor = arColors[38];
			var fOver = function ()
				{
					this.className = 'wm-col-cell wm-col-cell-over';
					colorCell.style.backgroundColor = arColors[this.id.substring('lhe_color_id__'.length)];
				},
				fOut = function (){this.className = 'wm-col-cell';},
				fDown = function ()
				{
					var k = this.id.substring('lhe_color_id__'.length);
					_this.Select(arColors[k]);
				};
			for(i = 0; i < l; i++)
			{
				if (Math.round(i / 16) == i / 16) // new row
					row = tbl.insertRow(-1);

				cell = row.insertCell(-1);
				cell.innerHTML = '&nbsp;';
				cell.className = 'wm-col-cell';
				cell.style.backgroundColor = arColors[i];
				cell.id = 'lhe_color_id__' + i;

				cell.onmouseover = fOver;
				cell.onmouseout = fOut;
				cell.onmousedown = fDown;
			}

			this.pColCont.appendChild(tbl);
			this.bCreated = true;
		},

		OnClick: function (e, pEl)
		{
			if(this.disabled)
				return false;

			if (!this.bCreated)
				this.Create();

			if (this.bOpened)
				return this.Close();

			this.Open();
		},

		Open: function ()
		{
			var
				pos = BX.pos(this.pWnd),
				_this = this, top, left = pos.left;

			this.pColCont.style.display = 'block';
			if (BX.browser.IsIE())
			{
				top = pos.top - parseInt(this.pColCont.offsetHeight) - 2;
			}
			else
			{
				pos = BX.align(pos, 325, 155, 'top');
				top = pos.top;
				left = pos.left;
			}

			BX.bind(window, "keypress", BX.proxy(this.OnKeyPress, this));
			oTransOverlay.Show({onclick: function(){_this.Close()}});

			this.pColCont.style.top = top + 'px';
			this.pColCont.style.left = left + 'px';
			this.bOpened = true;
		},

		Close: function ()
		{
			this.pColCont.style.display = 'none';
			oTransOverlay.Hide();
			BX.unbind(window, "keypress", BX.proxy(this.OnKeyPress, this));
			this.bOpened = false;
		},

		OnKeyPress: function(e)
		{
			if(!e) e = window.event
			if(e.keyCode == 27)
				this.Close();
		},

		Select: function (color)
		{
			this.pWnd.style.backgroundColor = color;
			BX.onCustomEvent(this, "onChange", [color]);
			this.Close();
		}
	};

	function Popup(oPar)
	{
		var _this = this;
		this.bCreated = false;
		this.bOpened = false;
		this.zIndex = 5000;
		this.oPar = oPar;
		this.pWnd = BX.create("DIV", {props: {className: "bxiu-but bxiu-but-" + oPar.id}});
		this.pWnd.onmousedown = function(e){_this.OnClick(e, this)};
		if (oPar.title)
			this.pWnd.title = oPar.title;

		this.oPopup = new BX.CWindow(false, 'float');

		if (this.oPar && typeof this.oPar.OnCreate == 'function')
			this.oPar.OnCreate(this);

		var i, l = this.oPar.items.length, func = function(){_this.SelectItem(this.id.substr(parseInt('bxiu__item_'.length)));};
		for (i = 0; i < l; i++)
		{
			this.oPar.items[i].pItem = BX.create("DIV", {props: {id: 'bxiu__item_' + i, className: "bxiu-popup-but " + this.oPar.classPrefix + this.oPar.items[i].value.toLowerCase()}});
			if (this.oPar.items[i].title)
				this.oPar.items[i].pItem.title = this.oPar.items[i].title;

			this.oPopup.Get().appendChild(this.oPar.items[i].pItem);
			this.oPar.items[i].pItem.onmousedown = func;
		}

		if (typeof oPar.currentValue != 'undefined')
			this.SelectItem(false, oPar.currentValue);

		this.pWnd.onmousedown = function(e){_this.OnClick(e, this)};
	}

	Popup.prototype = {
		OnClick: function (e, pEl)
		{
			if (this.bOpened)
				return this.Close();
			this.Open();
		},

		Close: function ()
		{
			oTransOverlay.Hide();
			this.oPopup.Close();
			this.bOpened = false;
		},

		Open: function ()
		{
			this.oPopup.Show();
			var
				pos = BX.pos(this.pWnd),
				top = pos.top, left = pos.left;

			top -= this.oPopup.Get().offsetHeight;

			this.oPopup.Get().style.top = top + 'px';
			this.oPopup.Get().style.left = left + 'px';

			var _this = this;
			oTransOverlay.Show({onclick: function(){_this.Close()}});
			this.bOpened = true;
		},

		SelectItem: function(ind, value)
		{
			if (ind === false && value)
			{
				var i, l = this.oPar.items.length, item;
				for (i = 0; i < l; i++)
					if (this.oPar.items[i].value == value)
						break;
				ind = i;
			}
			var oItem = this.oPar.items[ind] ? this.oPar.items[ind] : this.oPar.items[0];

			if (this.oPar.items[this.activeItemInd])
				BX.removeClass(this.oPar.items[this.activeItemInd].pItem, 'bxiu-active');

			if (this.oPar.items[ind] && this.oPar.items[ind].pItem)
				BX.addClass(this.oPar.items[ind].pItem, 'bxiu-active');

			if (this.activeItemInd != ind)
			{
				BX.onCustomEvent(this, "onChange", [oItem.value]);
			}
			this.activeItemInd = ind;

			this.Close();
		}
	}

	function OpacityControl(oPar)
	{
		this.pCont = BX.create("DIV", {props: {className: "bxiu-opacity"}});

		this.pCont.appendChild(BX.create("DIV", {props: {className: "bxiu-opacity-label"}, text: BX.message("IUOpacity")}));
		var pDiv = this.pCont.appendChild(BX.create("DIV", {props: {className: "bxiu-op-div"}}));

		this.oPar = oPar;
		this.values = [
			{value:100, title: '0%'},
			{value:75, title: '25%'},
			{value:50, title: '50%'},
			{value:25, title: '75%'}
		];

		var
			_this = this,
			i, l = this.values.length, valCont, fMd = function(){_this.SelectItem(parseInt(this.id.substr('bxiu_op_item_'.length)));};

		for (i = 0; i < l; i++)
		{
			valCont = pDiv.appendChild(BX.create("DIV", {props: {id: "bxiu_op_item_" + i, className: "bxiu-op-val-cont"}}));
			valCont.appendChild(BX.create("DIV", {props: {className: "bxiu-op-l-corn"}}));
			valCont.appendChild(BX.create("DIV", {props: {className: "bxiu-op-center"}, html: '<span>' + this.values[i].title + '</span>'}));
			valCont.appendChild(BX.create("DIV", {props: {className: "bxiu-op-r-corn"}}));
			valCont.onmousedown = fMd;
			this.values[i].cont = valCont;
		}
		if (typeof oPar.currentValue != 'undefined')
			this.SelectItem(false, oPar.currentValue);
	}

	OpacityControl.prototype = {
		SelectItem: function(ind, value)
		{
			if (ind === false && typeof value != 'undefined')
			{
				var i, l = this.values.length;
				for (i = 0; i < l; i++)
				{
					if (this.values[i].value == value)
					{
						break;
					}
				}
				ind = i;
			}
			ind = (typeof ind == "number" && 0 <= ind && ind < this.values.length ? ind : 0);

			if (this.activeItemInd != ind)
			{
				BX.onCustomEvent(this, "onChange", [this.values[ind].value]);
				if (this.values[this.activeItemInd])
					BX.removeClass(this.values[this.activeItemInd].cont, 'bxiu-op-val-cont-active');
				this.activeItemInd = ind;
				BX.addClass(this.values[ind].cont, 'bxiu-op-val-cont-active');
			}
		}
	};


	function Overlay()
	{
		this.id = 'bxiu_trans_overlay';
		this.zIndex = 100;
	}

	Overlay.prototype =
	{
		Create: function ()
		{
			this.bCreated = true;
			this.bShowed = false;
			var ws = BX.GetWindowScrollSize();
			this.pWnd = document.body.appendChild(BX.create("DIV", {props: {id: this.id, className: "bxiu-trans-overlay"}, style: {zIndex: this.zIndex, width: ws.scrollWidth + "px", height: ws.scrollHeight + "px"}}));

			this.pWnd.ondrag = BX.False;
			this.pWnd.onselectstart = BX.False;
		},

		Show: function(arParams)
		{
			if (!this.bCreated)
				this.Create();
			this.bShowed = true;

			var ws = BX.GetWindowScrollSize();

			this.pWnd.style.display = 'block';
			this.pWnd.style.width = ws.scrollWidth + "px";
			this.pWnd.style.height = ws.scrollHeight + "px";

			if (!arParams)
				arParams = {};

			if (arParams.zIndex)
				this.pWnd.style.zIndex = arParams.zIndex;

			if (arParams.onclick && typeof arParams.onclick == 'function')
				this.pWnd.onclick = arParams.onclick;

			BX.bind(window, "resize", BX.proxy(this.Resize, this));
			return this.pWnd;
		},

		Hide: function ()
		{
			if (!this.bShowed)
				return;
			this.bShowed = false;
			this.pWnd.style.display = 'none';
			BX.unbind(window, "resize", BX.proxy(this.Resize, this));
			this.pWnd.onclick = null;
		},

		Resize: function ()
		{
			if (this.bCreated)
				this.pWnd.style.width = BX.GetWindowScrollSize().scrollWidth + "px";
		}
	};

	var oTransOverlay = new Overlay();


	var cnvConstr = null, cnvEdtr = null;
	BX.UploaderTemplateThumbnails = function(params, settings)
	{
		this.id = params["id"];
		this.UPLOADER_ID = params["UPLOADER_ID"];
		this.dialogName = "BX.UploaderTemplateThumbnails";
		this.vars = {
			"filesCountForUpload" : 0
		};
		params["phpMaxFileUploads"] = 10;
		if (params["copies"])
		{
			var i = 1;
			for (var ii in params["copies"])
			{
				if (params["copies"].hasOwnProperty(ii))
				{
					i++;
				}
			}
			params["phpMaxFileUploads"] = i * 10;
		}

		this.params = params;
		params.allowUploadExt = "jpg,jpeg,png,gif";
		this.uploader = BX.Uploader.getInstance(params);
		this.init();
		return this;
	};
	BX.UploaderTemplateThumbnails.prototype = {
		init : function()
		{
			if (this.uploader.dialogName != "BX.Uploader")
			{
				BX.addClass(BX("bxuMain" + this.id), "bxu-thumbnails-simple");
			}

			this._onItemIsAdded = BX.delegate(this.onItemIsAdded, this);
			this._onFileIsAppended = BX.delegate(this.onFileIsAppended, this);

			BX.addCustomEvent(this.uploader, "onItemIsAdded", this._onItemIsAdded);
			BX.addCustomEvent(this.uploader, 'onStart', BX.delegate(this.start, this));
			BX.addCustomEvent(this.uploader, 'onDone', BX.delegate(this.done, this));
			BX.addCustomEvent(this.uploader, 'onFinish', BX.delegate(this.finish, this));
			BX.addCustomEvent(this.uploader, 'onTerminate', BX.delegate(this.terminate, this));

			BX.addCustomEvent(this.uploader, "onFileIsAppended", this._onFileIsAppended);
			BX.addCustomEvent(this.uploader, "onQueueIsChanged", BX.delegate(this.onChange, this));

			this._onUploadStart = BX.delegate(this.onUploadStart, this);
			this._onUploadProgress = BX.delegate(this.onUploadProgress, this);
			this._onUploadDone = BX.delegate(this.onUploadDone, this);
			this._onUploadError = BX.delegate(this.onUploadError, this);
			this._onUploadRestore = BX.delegate(this.onUploadRestore, this);
			this._onFileHasPreview = BX.delegate(this.onFileHasPreview, this);

			BX.bind(BX('bxuStartUploading' + this.id), "click", BX.delegate(this.uploader.submit, this.uploader));
			BX.bind(BX('bxuCancel' + this.id), "click", BX.delegate(this.uploader.stop, this.uploader));

			this.uploader.init(BX('bxuUploaderStart' + this.id));
			this.uploader.init(BX('bxuUploaderStartField' + this.id));

			BX.bind(BX('bxuReduced' + this.id), "click", BX.delegate(function(){

				BX.userOptions.save('main', this.UPLOADER_ID, 'template', "reduced");

				BX.addClass(BX('bxuReduced' + this.id), 'bxu-templates-btn-active');
				BX.removeClass(BX('bxuEnlarge' + this.id), 'bxu-templates-btn-active');
				BX.addClass(BX('bxuMain' + this.id), 'bxu-main-block-reduced-size');
			}, this));
			BX.bind(BX('bxuEnlarge' + this.id), "click", BX.delegate(function(){
				BX.userOptions.save('main', this.UPLOADER_ID, 'template', "full");

				BX.removeClass(BX('bxuReduced' + this.id), 'bxu-templates-btn-active');
				BX.addClass(BX('bxuEnlarge' + this.id), 'bxu-templates-btn-active');
				BX.removeClass(BX('bxuMain' + this.id), 'bxu-main-block-reduced-size');
			}, this));
			this.uploader.fileFields = (!!this.uploader.fileFields ? this.uploader.fileFields : {});
			this.uploader.fileFields.description = (!!this.uploader.fileFields.description ? this.uploader.fileFields.description : {});
		},
		onUploadStart : function(item)
		{
			item.__progressBarWidth = 1;
			var ph = item.getPH('Thumb'), id = item.id, width, percent = item.progress;
			BX.addClass(ph, "bxu-item-loading");
			if (BX('bxu' + id + 'ProgressBar'))
			{
				BX.adjust(BX('bxu' + id + 'ProgressBar'), {style : { width : item.__progressBarWidth + '%'}});
			}
		},
		onUploadProgress : function(item, progress)
		{
			var id = item.id;
			if (BX('bxu' + id + 'ProgressBar'))
			{
				item.__progressBarWidth = Math.max(item.__progressBarWidth, Math.ceil(progress));
				BX.adjust(BX('bxu' + id + 'ProgressBar'), {style : { width : item.__progressBarWidth + '%'}});
			}
		},
		onUploadDone : function(item)
		{
			var pointer = this.uploader.getItem(item.id), node;

			if (pointer && (node = pointer.node) && node)
				BX.hide(node);

			item.file = null;
			delete item.file;

			BX.remove(item.canvas);
			item.canvas = null;
			delete item.canvas;

			this.vars["uploadedFilesCount"]++;
			BX('bxuUploaded' + this.id).innerHTML = this.vars["uploadedFilesCount"];
			BX('bxuUploadBar' + this.id).style.width = Math.ceil(this.vars["uploadedFilesCount"] / this.vars["filesCountForUpload"] * 100) + '%';

			this.onChange(this.uploader.queue);
		},
		onUploadError : function(item, file, queue)
		{
			var ph = item.getPH('Thumb');
			BX.removeClass(ph, "bxu-item-loading");
			BX.addClass(ph, "bxu-item-error");
			ph.innerHTML = this.params.errorThumb.replace("#error#", file.error);

		},
		onUploadRestore : function(item)
		{
			var ph = item.getPH('Thumb');
			BX.removeClass(ph, "bxu-item-loading");
			BX.removeClass(ph, "bxu-item-loading-with-error");
		},
		start : function(pIndex, queue)
		{
			this.vars["uploadedFilesCount"] = this.uploader.queue.itUploaded.length;
			this.vars["filesCountForUpload"] += queue.filesCount;
			BX('bxuUploadBar' + this.id).style.width = Math.ceil(this.vars["uploadedFilesCount"] / this.vars["filesCountForUpload"]) + '%';
			BX('bxuUploaded' + this.id).innerHTML = this.vars["uploadedFilesCount"];
			BX('bxuForUpload' + this.id).innerHTML = this.vars["filesCountForUpload"];
			BX.addClass(BX("bxuMain" + this.id), "bxu-thumbnails-loading");
		},
		done : function(stream, pIndex, queue, data)
		{
			this.vars["filesCountForUpload"] -= queue.filesCount;
			BX.removeClass(BX("bxuMain" + this.id), "bxu-thumbnails-loading");
			BX('bxuUploaded' + this.id).innerHTML = this.vars["uploadedFilesCount"];
			this.redirectUrl = data.report.uploading[this.uploader.CID]['redirectUrl'];
		},
		finish : function() {
			if(this.uploader.queue.itFailed.length <= 0 && BX.type.isNotEmptyString(this.redirectUrl)) {
				BX.reload(this.redirectUrl);
			}
		},
		terminate : function(pIndex, queue)
		{
			this.vars["filesCountForUpload"] -= queue.filesCount;
			BX.removeClass(BX("bxuMain" + this.id), "bxu-thumbnails-loading");
			BX('bxuUploaded' + this.id).innerHTML = this.vars["uploadedFilesCount"];
		},
		onChange : function(queue)
		{
			if (!!BX('bxuImagesCount' + this.id))
			{
				this.vars["filesCount"] = queue.items.length - (this.vars["uploadedFilesCount"] > 0 ? this.vars["uploadedFilesCount"] : 0);
				BX('bxuImagesCount' + this.id).innerHTML = this.vars["filesCount"];
			}
		},
		onItemIsAdded : function(f, uploader)
		{
			BX.removeCustomEvent(this.uploader, "onItemIsAdded", this._onItemIsAdded);
			BX.removeClass(BX("bxuMain" + this.id), "bxu-thumbnails-start");
		},
		onFileHasPreview : function(id, item, img)
		{
/*			var img1 = BX.create('IMG', {attrs : { src : img.src } } );
			img.parentNode.insertBefore(img1, img);
			img.parentNode.removeChild(img);
*/		},
		onFileIsAppended : function(id, file)
		{
			if (file.dialogName == "BX.UploaderFile" || !BX.CanvasEditor)
			{
				if (BX(id + 'Edit')) BX.remove(BX(id + 'Edit'));
				if (BX(id + 'Turn')) BX.remove(BX(id + 'Turn'));
			}
			else
			{
				if (BX(id + 'Edit'))
					BX.bind(BX(id + 'Edit'), "click", BX.delegate(file.clickFile, file));
				if (BX(id + 'Turn'))
				{
					file.__onTurnCanvas = BX.delegate(function(image, canvas, context)
					{
						if (cnvEdtr === null)
							cnvEdtr = new BX.Canvas();
						if (cnvEdtr)
						{
							context.drawImage(image, 0, 0);
							cnvEdtr.copy(canvas, { width : image.width, height : image.height });
							cnvEdtr.rotate(true);
							this.applyFile(cnvEdtr.cnv, true);
						}
					}, file);
					BX.bind(BX(id + 'Turn'), "click", BX.delegate(function() {
						if (cnvConstr === null && !!BX.UploaderFileCnvConstr)
							cnvConstr = new BX.UploaderFileCnvConstr();
						if (!!cnvConstr)
						{
							BX.adjust(cnvConstr.getCanvas(), { props : { width : this.file.width, height : this.file.height } } );
							cnvConstr.push(this.file, this.__onTurnCanvas);
						}
					}, file));
				}
			}
			BX.addCustomEvent(file, 'onUploadStart', this._onUploadStart);
			BX.addCustomEvent(file, 'onUploadProgress', this._onUploadProgress);
			BX.addCustomEvent(file, 'onUploadDone', this._onUploadDone);
			BX.addCustomEvent(file, 'onUploadError', this._onUploadError);
			BX.addCustomEvent(file, 'onUploadRestore', this._onUploadRestore);
			BX.addCustomEvent(file, 'onFileHasPreview', this._onFileHasPreview);

			if (BX(id + 'Del'))
				BX.bind(BX(id + 'Del'), "click", BX.delegate(function(){
					BX.removeCustomEvent(file, 'onUploadStart', this._onUploadStart);
					BX.removeCustomEvent(file, 'onUploadProgress', this._onUploadProgress);
					BX.removeCustomEvent(file, 'onUploadDone', this._onUploadDone);
					BX.removeCustomEvent(file, 'onUploadError', this._onUploadError);
					BX.removeCustomEvent(file, 'onUploadRestore', this._onUploadRestore);
					BX.removeCustomEvent(file, 'onFileHasPreview', this._onFileHasPreview);
					BX.unbindAll(BX(id + 'Turn'));
					BX.unbindAll(BX(id + 'Edit'));
					BX.unbindAll(BX(id + 'Del'));
					file.deleteFile();
				}, file));
		}
	};
	BX.UploaderSettings = function(params)
	{
		this.UPLOADER_ID = params["UPLOADER_ID"];
		this.id = params["id"];
		this.form = BX(params["UPLOADER_ID"] + "_form");
		var _this = this;
		params = (!!params ? params : {});
		this.params = params["params"];

		if (params["show"]) {
			for (var ii = 0; ii < params["show"].length; ii++)
				this.init(params["show"][ii]);
		}
		var node = BX('bxuMain' + _this.id);
		BX('bxuSettings' + this.id).onclick = function()
		{
			if (BX.hasClass(node, 'bxu-thumbnails-settings-are'))
			{
				BX.removeClass(node, 'bxu-thumbnails-settings-are');
				_this.SaveUserOption('additional', 'N');
			}
			else
			{
				BX.addClass(node, 'bxu-thumbnails-settings-are');
				_this.SaveUserOption('additional', 'Y');
			}
		};
		_this.SaveUserOption('additional', (BX.hasClass(node, 'bxu-thumbnails-settings-are') ? 'Y' : 'N'));
		this.n = {};
		if (!!window['oBXUploaderHandler_' + this.UPLOADER_ID])
		{
			this.oUploadHandler = window['oBXUploaderHandler_' + this.UPLOADER_ID];
			BX.addCustomEvent(this, "onChangeSize", this.oUploadHandler.SetOriginalSize);
			BX.addCustomEvent(this, "onWMChangeUse", function(v){_this.oUploadHandler.Watermark.Using(v, false);});
			BX.addCustomEvent(this, "onWMChangeType", _this.oUploadHandler.Watermark.Type);
			BX.addCustomEvent(this, "onWMChangeText", _this.oUploadHandler.Watermark.Text);
			BX.addCustomEvent(this, "onWMChangeCopyright", _this.oUploadHandler.Watermark.Copyright);
			BX.addCustomEvent(this, "onWMChangeColor", _this.oUploadHandler.Watermark.Color);
			BX.addCustomEvent(this, "onWMChangePosition", _this.oUploadHandler.Watermark.Position);
			BX.addCustomEvent(this, "onWMChangeFile", function(val, vals){
				_this.oUploadHandler.Watermark.File(val);
				_this.oUploadHandler.Watermark.FileWidth(vals[2]);
				_this.oUploadHandler.Watermark.FileHeight(vals[3]);
			});
		}

		return this;

	};
	BX.UploaderSettings.prototype = {
		SaveUserOption: function(option, value)
		{
			option = option.toLowerCase();
			var Opt = option.substr(0, 1).toUpperCase() + option.substr(1);
			if (!this.form["photo_watermark_" + option])
			{
				this.form.appendChild(BX.create("INPUT", {
					props : {
						type : "hidden",
						name : "photo_watermark_" + option,
						value : value
					},
					attrs : {
						"bxu-set" : "Y"
					}
				}));
			}
			else if (!this.form["photo_watermark_" + option].hasAttribute("bxu-set"))
			{
				this.form["photo_watermark_" + option].value = value;
				this.form["photo_watermark_" + option].setAttribute("bxu-set", "Y");
			}
			else if (this.form["photo_watermark_" + option].value != value)
			{
				this.form["photo_watermark_" + option].value = value;
				BX.onCustomEvent(this, "onWMChange" + Opt, [value, arguments]);

				BX.userOptions.save('main', this.UPLOADER_ID, option, value);
			}
		},
		init : function(id)
		{
			if (id == "resize" &&  BX('bxiu_resize_' + this.UPLOADER_ID))
			{
				this.n["resizer"] = BX('bxiu_resize_' + this.UPLOADER_ID);
				this.form.photo_resize_size.value = this.n["resizer"].value;
				BX.bind(this.n["resizer"], "change", BX.delegate(function() {
					this.form.photo_resize_size.value = this.n["resizer"].value;
					BX.onCustomEvent(this, "onChangeSize", [this.n["resizer"].value])
				}, this));

			}
			else if (id == "watermark")
			{
				this.setWMUsing(this.params['use']);
				this.setWMType(this.params['type']);
				this.setWMText(this.params['text']);
				this.setWMCopyright(this.params['copyright']);
				this.setWMColor(this.params['color']);
				this.setWMPosition(this.params['position']);
				this.setWMSize(this.params['size']);
				this.setWMFile(this.params['file'], this.params['fileWidth'], this.params['fileHeight']);
				this.setWMOpacity(this.params['opacity']);
//				this.InitImageTypeControls(this.params['position'], this.params['size'], this.params['opacity']);
			}
		},
		setWMUsing : function (val)
		{
			if (!this.nodeWMUsing)
			{
				this.nodeWMUsing = BX(this.id + '_use_watermark');
				BX.bind(this.nodeWMUsing, "click", BX.delegate(this.setWMUsing, this));
//				this.form.photo_watermark_use.value = val;
			}
			if (this.nodeWMUsing)
			{
				val = ((val === "Y" || val === "N") ? val : (this.nodeWMUsing.checked ? 'Y' : 'N'));
				var nodeP = BX(this.id + '_watermark_cont');
				if (this.nodeWMUsing.checked)
					BX.addClass(nodeP, "bxiu-watermark-checked");
				else
					BX.removeClass(nodeP, "bxiu-watermark-checked");
				this.SaveUserOption('use', val);
			}
		},
		setWMType : function(type)
		{
			if (!this.pTypeText)
			{
				// Watermark type
				this.pTypeText = BX(this.id + '_wmark_type_text');
				this.pTypeImg = BX(this.id + '_wmark_type_img');

				BX.bind(this.pTypeText, "click", BX.delegate(function(){ this.setWMType('text'); }, this));
				BX.bind(this.pTypeImg, "click", BX.delegate(function(){ this.setWMType('image'); }, this));
				type = (!!type ? type : (this.pTypeText.checked ? 'text' : 'image'));
//				this.form.photo_watermark_type.value = type;
			}
			if (this.pTypeText)
			{
				var nodeP = BX(this.id + '_watermark_cont');
				if (type == 'text')
				{
					this.pTypeText.checked = true;
					BX.removeClass(nodeP, 'bxiu-watermark-image-checked');
					BX.addClass(nodeP, 'bxiu-watermark-text-checked');
				}
				else
				{
					this.pTypeImg.checked = true;
					BX.addClass(nodeP, 'bxiu-watermark-image-checked');
					BX.removeClass(nodeP, 'bxiu-watermark-text-checked');
				}
				this.SaveUserOption('type', type);
			}
		},
		setWMText : function(val)
		{
			if (!this.pWatermarkText)
			{
				this.pWatermarkText = BX(this.id + '_wmark_text');
				this.pWatermarkText.onchange = this.pWatermarkText.onblur = this.pWatermarkText.onkeyup = BX.delegate(this.setWMText, this);
//				this.form.photo_watermark_text.value =  this.pWatermarkText.value = val;
			}
			if (this.pWatermarkText)
			{
				val = ((typeof val == "string") ? val : this.pWatermarkText.value);
				this.SaveUserOption('text', val);
			}
			this.textButCont = BX(this.id + '_text_but_cont');
		},
		setWMCopyright : function(val)
		{
			if (!this.pCopyright)
			{
				this.pCopyright = BX.create("DIV", {props: {className: 'bxiu-but bxiu-copyright'}});
				BX.bind(this.pCopyright, "click", BX.delegate(function(){ this.setWMCopyright(this.form.photo_watermark_copyright.value == "Y" ? "N" : "Y"); }, this));
				BX(this.id + '_text_but_cont').appendChild(BX.create("DIV", {props: {className: 'bxiu-but-cont'}})).appendChild(this.pCopyright);
//				this.form.photo_watermark_copyright.value = val;
			}
			if (this.pCopyright)
			{
				if (val == 'Y')
				{
					this.pCopyright.title = BX.message("IUCopyrightTitleOff");
					BX.removeClass(this.pCopyright, 'bxiu-copyright-none');
					BX.addClass(this.pWatermarkText, 'bxiu-show-copyright');
				}
				else
				{
					this.pCopyright.title = BX.message("IUCopyrightTitleOn");
					BX.addClass(this.pCopyright, 'bxiu-copyright-none');
					BX.removeClass(this.pWatermarkText, 'bxiu-show-copyright');
				}
				this.SaveUserOption('copyright', val);
			}

		},
		setWMColor : function(val)
		{
			if (!this.oColorpicker)
			{
				this.oColorpicker = new ColorPicker(val);
				BX.addCustomEvent(this.oColorpicker, "onChange", BX.proxy(this.setWMColor, this));
				this.textButCont.appendChild(BX.create("DIV", {props: {className: 'bxiu-but-cont'}})).appendChild(this.oColorpicker.pWnd);
//				this.form.photo_watermark_color.value = val;
			}
			if (this.oColorpicker)
			{
				this.SaveUserOption('color', val);
			}
		},
		setWMPosition : function(val)
		{
			if (!this.oTextPosition)
			{
				this.oTextPosition = new Popup({
					id: 'position_text',
					classPrefix: 'bxiu-but-pos-',
					items: [
						{value: "TopLeft", title: BX.message("IUTopLeft")},
						{value: "TopCenter", title: BX.message("IUTopCenter")},
						{value: "TopRight", title: BX.message("IUTopRight")},
						{value: "CenterLeft", title: BX.message("IUCenterLeft")},
						{value: "Center", title: BX.message("IUCenter")},
						{value: "CenterRight", title: BX.message("IUCenterRight")},
						{value: "BottomLeft", title: BX.message("IUBottomLeft")},
						{value: "BottomCenter", title: BX.message("IUBottomCenter")},
						{value: "BottomRight", title: BX.message("IUBottomRight")}
					],
					currentValue: val,
					title: BX.message("IUPositionTitle"),
					OnCreate: function(obj)
					{
						obj.type = 'applet';
						BX.addClass(obj.pWnd, 'bxiu-but-pos-center');
						BX.addClass(obj.oPopup.Get(), 'bxiu-pos-popup');
					}
				});
				BX.addCustomEvent(this.oTextPosition, "onChange", BX.proxy(this.setWMPosition, this));
				BX(this.id + '_text_but_cont').appendChild(BX.create("DIV", {props: {className: 'bxiu-but-cont'}})).appendChild(this.oTextPosition.pWnd);
//				this.form.photo_watermark_position.value = val;
			}
			if (this.oTextPosition)
			{
				this.oTextPosition.pWnd.className = 'bxiu-but bxiu-but-pos-' + val.toLowerCase();
				this.SaveUserOption('position', val);
			}
		},
		setWMSize : function(val)
		{
			if (!this.oTextSize)
			{
				this.oTextSize = new Popup({
					id: 'size_text',
					classPrefix: 'bxiu-but-t-size-',
					items: [
						{value: "big", title: BX.message("IUSizeBig")},
						{value: "middle", title: BX.message("IUSizeMiddle")},
						{value: "small", title: BX.message("IUSizeSmall")}
					],
					currentValue: val,
					title: BX.message("IUSizeTitle"),
					OnCreate: function(obj)
					{
						obj.type = 'applet';
						BX.addClass(obj.pWnd, 'bxiu-but-t-size-middle');
						BX.addClass(obj.oPopup.Get(), 'bxiu-text-size-popup');
					}
				});
				BX.addCustomEvent(this.oTextSize, "onChange", BX.proxy(this.setWMSize, this));
				BX(this.id + '_text_but_cont').appendChild(BX.create("DIV", {props: {className: 'bxiu-but-cont'}})).appendChild(this.oTextSize.pWnd);
//				this.form.photo_watermark_size.value = val;
			}
			if (this.oTextPosition)
			{
				this.oTextSize.pWnd.className = 'bxiu-but bxiu-but-t-size-' + val;
				this.SaveUserOption('size', val);
			}
		},
		setWMFile : function(path, fileWidth, fileHeight)
		{
			var _this = this;
			this.imgButCont = BX(this.id + '_img_but_cont');
			if (!this.pImgInput)
			{
				this.pImgInput = BX('bxiu_wm_img' + this.id);
				this.pImgInput.onchange = function()
				{
					_this.pImgInputOld = _this.pImgInput;
					_this.pImgInput = _this.pImgInput.cloneNode(false);
					_this.pImgInput.onchange = _this.pImgInputOld.onchange;
					_this.pImgInputOld.parentNode.insertBefore(_this.pImgInput, _this.pImgInputOld);

					if (!!_this.pImgForm)
						_this.pImgForm.parentNode.removeChild(_this.pImgForm);

					_this.pImgForm = BX.create("FORM", {
						props: {
							method: "POST",
							enctype: "multipart/form-data",
							encoding: "multipart/form-data",
							action: _this.form.action,
							name : "wm_form"
						},
						style: {display: "none"},
						children : [
							BX.create('INPUT', { props : { type : "hidden", name : "sessid", value : BX.bitrix_sessid() } } ),
							BX.create('INPUT', { props : { type : "hidden", name : "watermark_iframe", value : "Y" } } ),
							_this.pImgInputOld
						]
					});
					document.body.appendChild(_this.pImgForm);

					BX.ajax.submit(_this.pImgForm, function()
					{
						var pCont = BX('bxiu_wm_img_iframe_cont' + _this.id);
						pCont.className = 'bxiu-iframe-cont-ok';
						setTimeout(function(){
							var res = top.bxiu_wm_img_res;
							if (!top.bxiu_wm_img_res || res.error)
								return alert(res.error);
							_this.setWMFile(res.path, res.width, res.height);
						}, 50);
					});
				}
				this.watermarkPreview = BX('watermark_img_preview' + this.id);
				this.watermarkPreview.onerror = function() { this.style.display = 'none'; };
				this.watermarkPreview.onload = function()
				{
					if (_this.watermarkPreview.src != "/bitrix/images/1.gif")
					{
						_this.watermarkPreviewCont.style.display = "block";
						setTimeout(function()
						{
							_this.watermarkPreviewDel.style.display = "block";
							_this.watermarkPreviewDel.style.left = (parseInt(_this.watermarkPreview.offsetWidth) -
								Math.ceil(_this.watermarkPreviewDel.offsetWidth / 2)) + 'px';
						}, 200);
					}
					else
					{
						_this.watermarkPreviewCont.style.display = "none";
					}
				};
				this.watermarkPreviewCont = BX(this.id + '_wmark_preview_cont');
				this.watermarkPreviewDel = BX(this.id + '_wmark_preview_del');
				this.watermarkPreviewDel.onclick = function()
				{
					_this.watermarkPreview.src = '/bitrix/images/1.gif';
					_this.watermarkPreviewCont.style.display = "none";
					_this.setWMFile('', 0, 0);
				};
			}
			if (this.pImgInput)
			{
				this.watermarkPreview.src = path;
				this.watermarkPreview.style.display = '';
				this.SaveUserOption('file', path, fileWidth, fileHeight);
			}
		},
		setWMOpacity : function(val)
		{
			if (!this.oTextOpacity)
			{
				this.oTextOpacity = new OpacityControl({
					currentValue: val
				});
				BX.addCustomEvent(this.oTextOpacity, "onChange", BX.proxy(this.setWMOpacity, this));
				BX(this.id + '_img_but_cont').appendChild(BX.create("DIV", {props: {className: 'bxiu-opacity-cont'}})).appendChild(this.oTextOpacity.pCont);
//				this.form.photo_watermark_opacity.value = val;
			}
			if (this.oTextOpacity)
			{
				this.SaveUserOption('opacity', val);
			}
		},
		InitImageTypeControls: function(position, size)
		{
			var _this = this;
			this.imgButCont = BX(this.id + '_img_but_cont');

			// Position
			this.oImagePosition = new Popup({
				id: 'position_image',
				classPrefix: 'bxiu-but-pos-',
				items: [
					{value: "TopLeft", title: BX.message("IUTopLeft")},
					{value: "TopCenter", title: BX.message("IUTopCenter")},
					{value: "TopRight", title: BX.message("IUTopRight")},
					{value: "CenterLeft", title: BX.message("IUCenterLeft")},
					{value: "Center", title: BX.message("IUCenter")},
					{value: "CenterRight", title: BX.message("IUCenterRight")},
					{value: "BottomLeft", title: BX.message("IUBottomLeft")},
					{value: "BottomCenter", title: BX.message("IUBottomCenter")},
					{value: "BottomRight", title: BX.message("IUBottomRight")}
				],
				currentValue: position,
				title: BX.message("IUPositionTitle"),
				OnCreate: function(obj)
				{
					obj.type = _this.type;
					BX.addClass(obj.pWnd, 'bxiu-but-pos-center');
					BX.addClass(obj.oPopup.Get(), 'bxiu-pos-popup');
				}
			});
			BX.addCustomEvent(this.oTextPosition, "onChange", BX.proxy(function(val)
			{
				this.oImagePosition.pWnd.className = 'bxiu-but bxiu-but-pos-' + val.toLowerCase();
				this.setWMPosition(val);
			}, this));
			this.imgButCont.appendChild(BX.create("DIV", {props: {className: 'bxiu-but-cont'}})).appendChild(this.oImagePosition.pWnd);

			// Image size
			this.oImgSize = new Popup({
				id: 'size_image',
				classPrefix: 'bxiu-but-i-size-',
				items: [
					{value: "real", title: BX.message("IUSizeReal")},
					{value: "big", title: BX.message("IUSizeBig")},
					{value: "middle", title: BX.message("IUSizeMiddle")},
					{value: "small", title: BX.message("IUSizeSmall")}
				],
				currentValue: size,
				title: BX.message("IUSizeTitle"),
				OnCreate: function(obj)
				{
					obj.type = _this.type;
					BX.addClass(obj.pWnd, 'bxiu-but-i-size-real');
					BX.addClass(obj.oPopup.Get(), 'bxiu-img-size-popup');
				}
			});
			BX.addCustomEvent(this.oTextPosition, "onChange", BX.proxy(function(val)
			{
				this.oImgSize.pWnd.className = 'bxiu-but bxiu-but-t-size-' + val.toLowerCase();
				this.setWMSize(val);
			}, this));
			this.imgButCont.appendChild(BX.create("DIV", {props: {className: 'bxiu-but-cont'}})).appendChild(this.oImgSize.pWnd);
		}
	};
}(window));