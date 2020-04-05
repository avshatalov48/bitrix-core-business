(function(window) {
	window.BXUploader = function(Params)
	{
		this.type = Params.type == 'flash' ? 'flash' : 'applet';
		this.id = Params.id;
		this.arGuids = {};
		this.Watermark = new WatermarkData(this);
	}

	window.BXUploader.prototype = {
		enableExtendedUploadPane: function(bxp, config)
		{
			var _this = this;

			bxp.events.beforeUpload.push(this.exUploaderOnBeforeUpload);
			bxp.events.uploadFileCountChange.push(this.exUploaderOnUploadFileCountChange);

			bxp.events.initComplete.push(function(){
				if (_this.uploadPane)
					_this.uploadPane.style.display = "block";
			});

			this.extendedUploadPaneConfig = config;
			BX.ready(function()
				{
					_this.uploadPane = BX(_this.id + '_UploadPane');
					_this.uploadPaneCount = 0;
					// In Safari java applets are not good in scroll.
					if (_this.uploadPane && BX.browser.IsSafari())
						_this.uploadPane.style.height = "auto";
				}
			);

			return bxp;
		},

		exUploaderOnUploadFileCountChange: function()
		{
			var
				_this = window['oBXUploaderHandler_' + this.id()],
				f = this.files(),
				fileCount = f.count(),
				guids = {},
				i, l, div, guid;

			if (fileCount < _this.uploadPaneCount)
			{
				// Files are being removed
				// Get upload file guids
				for (i = 0; i < fileCount; i++)
					guids[f.get(i).guid() + ''] = true;

				for (i = 0, l = _this.uploadPane.childNodes.length; i < l; i++)
				{
					div = _this.uploadPane.childNodes[i];
					if (div.tagName && div.tagName.toLowerCase() == 'div' && div.id.substr(0, 'bxitem_'.length) == 'bxitem_')
					{
						guid = _this.arGuids[div.id.substr('bxitem_'.length)];
						if (!guids[guid])
							div.parentNode.removeChild(div);
					}
				}
			}
			else if (fileCount > _this.uploadPaneCount)
			{
				// Files are being added
				for (i = _this.uploadPaneCount; i < fileCount; i++)
					_this.addItemToUploadPane(this, i);
			}
			_this.uploadPaneCount = fileCount;
		},

		exUploaderOnBeforeUpload: function()
		{
			var
				i, l, thumbId, title, div,
				_this = window['oBXUploaderHandler_' + this.id()];

			for (i = 0, l = _this.uploadPane.childNodes.length; i < l; i++)
			{
				div = _this.uploadPane.childNodes[i];
				if (div.tagName && div.tagName.toLowerCase() == 'div' && div.id.substr(0, 'bxitem_'.length) == 'bxitem_')
				{
					thumbId = div.id.substr('bxitem_'.length);

					//Title will be sent as a custom Title_N POST field, where N is an index of the file
					if (_this.extendedUploadPaneConfig.showTitle)
					{
						title = BX(thumbId + '_title').value;
						this.metadata().addCustomField('Title_' + i, title, false);
					}

					//Description will be sent as a native Description POST field provided by Image Uploader.
					if (_this.extendedUploadPaneConfig.showDesc)
						this.files().get(i).description(BX(thumbId + '_desc').value);

					//Tags will be sent as a native Tag POST field provided by Image Uploader.
					if (_this.extendedUploadPaneConfig.showTags)
						this.files().get(i).tag(BX(thumbId + '_tags').value);
				}
			}
		},

		addItemToUploadPane: function(uploader, index)
		{
			var
				file = uploader.files().get(index),
				guid = file.guid(),
				thumbId = 'Thumbnail_' + Math.round(Math.random() * 1000) + Math.round(Math.random() * 1000);

			// Create new thumbnail control
			var thumbnail = $au.thumbnail({
				id: thumbId,
				width: '100px',
				height: '100px',
				parentControlName: uploader.id(),
				guid: guid,
				javaControl: {codeBase: uploader.javaControl().codeBase()},
				activeXControl: {
					codeBase: uploader.activeXControl().codeBase(),
					codeBase64: uploader.activeXControl().codeBase64()
				}
			});

			this.arGuids[thumbId] = guid;
			var
				_this = this,
				r,c, thumbCell, rowSpan = 0, pCntrl = false,
				pItem = this.uploadPane.appendChild(BX.create("DIV", {props: {className: "bx-iu-ex-item", id: 'bxitem_' + thumbId}})),
				pTable = pItem.appendChild(BX.create("table"));

			r = pTable.insertRow(-1);
			thumbCell = BX.adjust(r.insertCell(-1), {props: {className: 'bx-iu-ex-thumb'}, html: thumbnail.getHtml()});
			c = BX.adjust(r.insertCell(-1), {props: {}});

			if (this.extendedUploadPaneConfig.showTitle)
			{
				c.appendChild(BX.create("label", {attrs: {'for': thumbId + '_title'}, text: 'Title:'}));
				pCntrl = c.appendChild(BX.create("input", {props: {type: 'text', id: thumbId + '_title', value: file.name()}}));
				pCntrl.onfocus = function(){this.select();};
				rowSpan++;
				c = pTable.insertRow(-1).insertCell(-1);
			}

			if (this.extendedUploadPaneConfig.showDesc)
			{
				c.appendChild(BX.create("label", {attrs: {'for': thumbId + '_desc'}, text: 'Description: '}));
				c.appendChild(BX.create("textarea", {props: {id: thumbId + '_desc', rows: 2}, text: file.description()}));
				rowSpan++;
				c = pTable.insertRow(-1).insertCell(-1);
			}

			if (this.extendedUploadPaneConfig.showTags)
			{
				c.appendChild(BX.create("label", {attrs: {'for': thumbId + '_tags'}, text: 'Tags:'}));
				c.appendChild(BX.create("input", {props: {type: 'text', id: thumbId + '_tags', value: file.tag()}}));
				rowSpan++;
			}

			if (rowSpan > 1)
				thumbCell.rowSpan = rowSpan;

			var pClose = pItem.appendChild(BX.create('DIV', {props: {className: 'bx-iu-ex-item-close', id: 'bxclose_' + thumbId}}));

			pClose.onclick = function()
			{
				// Remove item only from upload pane.
				// Removing file from upload pane triggers UploadFileCountChange,
				// where we remove list item element.
				var guid = _this.arGuids[this.id.substr('bxclose_'.length)];
				var files = uploader.files();
				for (var i = 0, imax = files.count(); i < imax; i++)
				{
					if (BX.util.trim(guid) == BX.util.trim(files.get(i).guid()))
					{
						files.remove(i);
						break;
					}
				}
			};
		},

		enableWatermark: function(bxp, config)
		{
			this.watermarkConfig = config;
			// Current values of watermark params

			this.Watermark.use = config.values.use;
			this.Watermark.type = config.values.type;
			this.Watermark.text = config.values.text;
			this.Watermark.color = config.values.color;
			this.Watermark.position = config.values.position;
			this.Watermark.size = config.values.size;
			this.Watermark.file = config.values.file;

			bxp.events.beforeUpload.push(this.watermarkOnBeforeUpload);
			return bxp;
		},

		watermarkOnBeforeUpload: function()
		{
			var
				length = this.converters().count(),
				i,
				_this = window['oBXUploaderHandler_' + this.id()];

			if (_this.Watermark.Using() != 'Y')
				return;

			_this.Watermark.WatermarkConfig(_this.watermarkConfig);

			var
				path, text, size, w, h, thumbWidth, thumbHeight, font,
				watermarkFormat = '',
				offset = 10;

			// For each converter
			for (i = 0; i < length; i++)
			{
				if (i == 1) // For thumbnail we don't need in watermarks
					continue;

				watermarkFormat = 'OffsetX=' + offset + ';OffsetY=' + offset + ';';
				// Position
				watermarkFormat += 'Position=' + _this.Watermark.Position() + ';';
				// Opacity
				if (_this.Watermark.Opacity() > 0 && _this.Watermark.Opacity() < 100)
					watermarkFormat += 'Opacity=' + _this.Watermark.Opacity() + ';';

				thumbWidth = this.converters().get(i).thumbnailWidth();
				thumbHeight = this.converters().get(i).thumbnailHeight();

				if (_this.Watermark.Type() == 'image' || _this.Watermark.Type() == 'picture')
				{
					path = _this.Watermark.File();
					if (!path)
						return;
					watermarkFormat += 'ImageUrl=' + path + ';';

					size = _this.Watermark.Size();
					var fileWidth = _this.Watermark.FileWidth();
					var fileHeight = _this.Watermark.FileHeight();

					w = fileWidth;
					h = fileHeight;

					if (size == 'big')
					{
						w = Math.round(fileWidth * 0.75);
						h = Math.round(fileHeight * 0.75);
					}
					else if(size == 'middle')
					{
						w = Math.round(fileWidth * 0.5);
						h = Math.round(fileHeight * 0.5);
					}
					else if (size == 'small')
					{
						w = Math.round(fileWidth * 0.25);
						h = Math.round(fileHeight * 0.25);
					}

					watermarkFormat +=  'Width=' + w + ';Height=' + h + ';';
				}
				else
				{
					text = _this.Watermark.Text();
					if (!text)
						return;
					if (_this.Watermark.Copyright())
						text = String.fromCharCode(169) + " " + text;
					watermarkFormat += 'Text="' + text + '";';

					size = _this.Watermark.Size();
					if (size == 'big')
					{
						fontSize = Math.round(thumbWidth * 0.11);
						if (fontSize > 75)
							fontSize = 75;
					}
					else if(size == 'middle')
					{
						fontSize = Math.round(thumbWidth * 0.07);
						if (fontSize > 55)
							fontSize = 55;
					}
					else
					{
						fontSize = Math.round(thumbWidth * 0.035);
						if (fontSize < 9)
							fontSize = 9;
						if (fontSize > 20)
							fontSize = 20;
					}
					watermarkFormat += 'Size=' + fontSize + ';';
					watermarkFormat += 'FillColor=' + _this.Watermark.Color() + ';';
					watermarkFormat += 'Font=Arial;';
				}

				this.converters().get(i).thumbnailWatermark(watermarkFormat);
			}
		},

		SetOriginalSize: function(size)
		{
			if (!this.uploader)
				this.uploader = window[(this.type == 'flash' ? 'BXFIU_' : 'BXIU_') + this.id];

			size = parseInt(size);
			if (size <= 0)
				size = 20000;

			this.uploader.converters().get(0).thumbnailWidth(size);
			this.uploader.converters().get(0).thumbnailHeight(size);

			this.SaveUserOption('original_size', size == 20000 ? 0 : size);
		},

		SaveUserOption: function(option, value)
		{
			BX.userOptions.save('main', this.id, option, value);
		}
	};

	function WatermarkData(oUploadHandler)
	{
		this.oUploadHandler = oUploadHandler;
	}

	WatermarkData.prototype = {
		WatermarkConfig: function(watermarkConfig)
		{
			this.watermarkConfig = watermarkConfig;
		},

		Using: function(value, bSave)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL')
				return 'Y';

			if (typeof value == 'undefined')
				return this.use;

			this.use = value;
			if (bSave !== false)
				this.oUploadHandler.SaveUserOption('use', this.use ? 'Y' : 'N');
		},
		Type: function(value, bSave)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.type)
				return this.watermarkConfig.type.toLowerCase();

			if (typeof value == 'undefined')
				return this.type;
			else
				this.type = value;

			if (bSave !== false)
				this.oUploadHandler.SaveUserOption('type', this.type);
		},
		Color: function(value, bSave)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.color)
				return this.watermarkConfig.color;

			if (typeof value == 'undefined')
				return this.color;
			else
				this.color = value;

			if (bSave !== false)
				this.oUploadHandler.SaveUserOption('color', this.color);
		},
		Size: function(value, bSave)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.size)
				return this.watermarkConfig.size;

			if (typeof value == 'undefined')
				return this.size;
			else
				this.size = value;

			if (bSave !== false)
				this.oUploadHandler.SaveUserOption('size', this.size);
		},
		Position: function(value, bSave)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.position)
				return this.watermarkConfig.position;

			if (typeof value == 'undefined')
				return this.position;
			else
				this.position = value;

			if (bSave !== false)
				this.oUploadHandler.SaveUserOption('position', this.position);
		},
		Text: function(value)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL')
				return this.watermarkConfig.text;

			if (typeof value == 'undefined')
				return this.text;
			else
				this.text = value;
		},
		File: function(value)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.file)
				return this.watermarkConfig.file;

			if (typeof value == 'undefined')
				return this.file;
			else
				this.file = value;
		},
		FileWidth: function(value)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.fileWidth)
				return this.watermarkConfig.fileWidth;

			if (typeof value == 'undefined')
				return this.fileWidth;
			else
				this.fileWidth = value;
		},
		FileHeight: function(value)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.fileHeight)
				return this.watermarkConfig.fileHeight;

			if (typeof value == 'undefined')
				return this.fileHeight;
			else
				this.fileHeight = value;
		},
		Opacity: function(value, bSave)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.opacity)
				return this.watermarkConfig.opacity;

			if (typeof value == 'undefined')
				return this.opacity;
			else
				this.opacity = value;

			if (bSave !== false)
				this.oUploadHandler.SaveUserOption('opacity', this.opacity);
		},
		Copyright: function(value, bSave)
		{
			if (this.watermarkConfig && this.watermarkConfig.rules == 'ALL' && this.watermarkConfig.copyright)
				return this.watermarkConfig.copyright;

			if (typeof value == 'undefined')
				return this.copyright;
			else
				this.copyright = value;

			if (bSave !== false)
				this.oUploadHandler.SaveUserOption('copyright', this.copyright);
		}
	}
})(window);

