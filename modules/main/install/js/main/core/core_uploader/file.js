;(function(window){
	if (window.BX["UploaderFile"])
		return false;
	var getOrientation = (function(){
		var exif = {
			tags : {
				// 0x0100 : "ImageWidth",
				// 0x0101 : "ImageHeight",
				// 0x8769 : "ExifIFDPointer",
				// 0x8825 : "GPSInfoIFDPointer",
				// 0xA005 : "InteroperabilityIFDPointer",
				// 0x0102 : "BitsPerSample",
				// 0x0103 : "Compression",
				// 0x0106 : "PhotometricInterpretation",
				0x0112 : "Orientation",
				// 0x0115 : "SamplesPerPixel",
				// 0x011C : "PlanarConfiguration",
				// 0x0212 : "YCbCrSubSampling",
				// 0x0213 : "YCbCrPositioning",
				// 0x011A : "XResolution",
				// 0x011B : "YResolution",
				// 0x0128 : "ResolutionUnit",
				// 0x0111 : "StripOffsets",
				// 0x0116 : "RowsPerStrip",
				// 0x0117 : "StripByteCounts",
				// 0x0201 : "JPEGInterchangeFormat",
				// 0x0202 : "JPEGInterchangeFormatLength",
				// 0x012D : "TransferFunction",
				// 0x013E : "WhitePoint",
				// 0x013F : "PrimaryChromaticities",
				// 0x0211 : "YCbCrCoefficients",
				// 0x0214 : "ReferenceBlackWhite",
				// 0x0132 : "DateTime",
				// 0x010E : "ImageDescription",
				// 0x010F : "Make",
				// 0x0110 : "Model",
				// 0x0131 : "Software",
				// 0x013B : "Artist",
				// 0x8298 : "Copyright"
			},
			getStringFromDB : function (buffer, start, length) {
				var outstr = "", n;
				for (n = start; n < start+length; n++) {
					outstr += String.fromCharCode(buffer.getUint8(n));
				}
				return outstr;
			},
			readTags : function(file, tiffStart, dirStart, strings, bigEnd) {
				var entries = file.getUint16(dirStart, !bigEnd),
					tags = {},
					entryOffset, tag,
					i,
					l = 0;
				for (i in strings)
				{
					if (strings.hasOwnProperty(i))
						l++;
				}

				for (i = 0; i < entries; i++)
				{
					entryOffset = dirStart + i*12 + 2;
					tag = strings[file.getUint16(entryOffset, !bigEnd)];
					tags[tag] = exif.readTagValue(file, entryOffset, tiffStart, dirStart, bigEnd);
					l--;
					if (l <= 0)
						break;
				}
				return tags;
			},
			readTagValue : function(file, entryOffset, tiffStart, dirStart, bigEnd) {
				var type = file.getUint16(entryOffset+2, !bigEnd),
					numValues = file.getUint32(entryOffset+4, !bigEnd),
					valueOffset = file.getUint32(entryOffset+8, !bigEnd) + tiffStart,
					offset,
					vals, val, n,
					numerator, denominator;

				switch (type)
				{
					case 1: // byte, 8-bit unsigned int
					case 7: // undefined, 8-bit byte, value depending on field
						if (numValues == 1) {
							return file.getUint8(entryOffset + 8, !bigEnd);
						} else {
							offset = numValues > 4 ? valueOffset : (entryOffset + 8);
							vals = [];
							for (n=0;n<numValues;n++) {
								vals[n] = file.getUint8(offset + n);
							}
							return vals;
						}
					case 2: // ascii, 8-bit byte
						offset = numValues > 4 ? valueOffset : (entryOffset + 8);
						return exif.getStringFromDB(file, offset, numValues-1);
					case 3: // short, 16 bit int
						if (numValues == 1) {
							return file.getUint16(entryOffset + 8, !bigEnd);
						} else {
							offset = numValues > 2 ? valueOffset : (entryOffset + 8);
							vals = [];
							for (n=0;n<numValues;n++) {
								vals[n] = file.getUint16(offset + 2*n, !bigEnd);
							}
							return vals;
						}
					case 4: // long, 32 bit int
						if (numValues == 1) {
							return file.getUint32(entryOffset + 8, !bigEnd);
						} else {
							vals = [];
							for (n=0;n<numValues;n++) {
								vals[n] = file.getUint32(valueOffset + 4*n, !bigEnd);
							}
							return vals;
						}
					case 5:    // rational = two long values, first is numerator, second is denominator
						if (numValues == 1) {
							numerator = file.getUint32(valueOffset, !bigEnd);
							denominator = file.getUint32(valueOffset+4, !bigEnd);
							val = new Number(numerator / denominator);
							val.numerator = numerator;
							val.denominator = denominator;
							return val;
						} else {
							vals = [];
							for (n=0;n<numValues;n++) {
								numerator = file.getUint32(valueOffset + 8*n, !bigEnd);
								denominator = file.getUint32(valueOffset+4 + 8*n, !bigEnd);
								vals[n] = new Number(numerator / denominator);
								vals[n].numerator = numerator;
								vals[n].denominator = denominator;
							}
							return vals;
						}
					case 9: // slong, 32 bit signed int
						if (numValues == 1) {
							return file.getInt32(entryOffset + 8, !bigEnd);
						} else {
							vals = [];
							for (n=0;n<numValues;n++) {
								vals[n] = file.getInt32(valueOffset + 4*n, !bigEnd);
							}
							return vals;
						}
					case 10: // signed rational, two slongs, first is numerator, second is denominator
						if (numValues == 1) {
							return file.getInt32(valueOffset, !bigEnd) / file.getInt32(valueOffset+4, !bigEnd);
						} else {
							vals = [];
							for (n=0;n<numValues;n++) {
								vals[n] = file.getInt32(valueOffset + 8*n, !bigEnd) / file.getInt32(valueOffset+4 + 8*n, !bigEnd);
							}
							return vals;
						}
				}
			},
			readData : function (file, start) {
				if (exif.getStringFromDB(file, start, 4) != "Exif")
				{
					return false;
				}

				var bigEnd,
					tiffOffset = start + 6;

				// test for TIFF validity and endianness
				if (file.getUint16(tiffOffset) == 0x4949)
				{
					bigEnd = false;
				}
				else if (file.getUint16(tiffOffset) == 0x4D4D)
				{
					bigEnd = true;
				}
				else
				{
					return false;
				}

				if (file.getUint16(tiffOffset+2, !bigEnd) != 0x002A)
				{
					return false;
				}

				var firstIFDOffset = file.getUint32(tiffOffset + 4, !bigEnd);

				if (firstIFDOffset < 0x00000008)
				{
					return false;
				}

				return exif.readTags(file, tiffOffset, tiffOffset + firstIFDOffset, exif.tags, bigEnd);
			},
			readBase64 : function (base64)
			{
				base64 = base64.replace(/^data\:([^\;]+)\;base64,/gmi, '');
				var binary_string =  window.atob(base64), //decode base64
					len = binary_string.length,
					bytes = new Uint8Array(len);
				for (var i = 0; i < len; i++) {
					bytes[i] = binary_string.charCodeAt(i);
				}
				var dataView = new DataView(bytes.buffer);
				if ((dataView.getUint8(0) != 0xFF) || (dataView.getUint8(1) != 0xD8))
				{
					return false; // not a valid jpeg
				}

				var offset = 2,
					length = bytes.buffer.byteLength,
					marker,
					result = false;
				while (offset < length)
				{
					if (dataView.getUint8(offset) != 0xFF) {
						break; // not a valid marker, something is wrong
					}

					marker = dataView.getUint8(offset + 1);

					// we could implement handling for other markers here,
					// but we're only looking for 0xFFE1 for EXIF data

					if (marker == 225)
					{
						result = exif.readData(dataView, offset + 4, dataView.getUint16(offset + 2) - 2);
						break;
					}
					else
					{
						offset += 2 + dataView.getUint16(offset+2);
					}
				}
				return result;
			}
		};
		return function(base64){
			if (BX.type.isString(base64))
			{
				try {
					var tags = exif.readBase64(base64);
					if(tags && tags["Orientation"])
						return tags["Orientation"];
				}
				catch (e)
				{
				}
			}
			return false;
		};
	})(),
		setOrientation = function(image, cnv, ctx, exifOrientation) {
			var width = image.width,
				height = image.height;
			if ([5,6,7,8].indexOf(exifOrientation) >= 0)
			{
				width = image.height;
				height = image.width;
			}

			BX.adjust(cnv, {props: {width: width, height: height}});

			ctx.save();
			switch(exifOrientation) {
				case 2:
					// $img.addClass('flip');
					ctx.scale(-1, 1);
					ctx.translate(-cnv.width, 0);
					break;
				case 3:
					// $img.addClass('rotate-180');
					ctx.translate(cnv.width, cnv.height);
					ctx.rotate(Math.PI);
					break;
				case 4:
					// $img.addClass('flip-and-rotate-180');
					ctx.scale(-1, 1);
					ctx.translate(0, cnv.height);
					ctx.rotate(Math.PI);
					break;
				case 5:
					// $img.addClass('flip-and-rotate-90');
					ctx.scale(-1, 1);
					ctx.translate(0, 0);
					ctx.rotate(Math.PI / 2);
					break;
				case 6:
					// $img.addClass('rotate-90');
					ctx.translate(cnv.width, 0);
					ctx.rotate(Math.PI / 2);
					break;
				case 7:
					// $img.addClass('flip-and-rotate-90');
					ctx.scale(-1, 1);
					ctx.translate(-cnv.width, cnv.height);
					ctx.rotate(Math.PI * 3 / 2);
					break;
				case 8:
					// $img.addClass('rotate-270');
					ctx.translate(0, cnv.height);
					ctx.rotate(Math.PI * 3 / 2);
					break;
			}
			ctx.drawImage(image, 0, 0);
			ctx.restore();
		};
	var BX = window.BX,
		statuses = { "new" : 0, ready : 1, preparing : 2, inprogress : 3, done : 4, failed : 5, stopped : 6, changed : 7, uploaded : 8},
		cnvConstr = (function(){
			var cnvConstructor = function(timelimit) {
				this.timeLimit = (typeof timelimit === "number" && timelimit > 0 ? timelimit : 50);
				this.status = statuses.ready;
				this.queue = new BX.UploaderUtils.Hash();
				this.id = (new Date()).getTime();
			};
			cnvConstructor.prototype = {
				counter : 0,
				active : null,
				image : null,
				getImage : function() {
					if (!this.image)
						this.image = new Image();
					return this.image;
				},
				canvas : null,
				getCanvas : function() {
					if (!this.canvas)
					{
						this.canvas = BX.create('CANVAS', {style : {display: "none"}});
						document.body.appendChild(this.canvas);
					}

					return this.canvas;
				},
				context : null,
				getContext : function() {
					if (!this.context && this.getCanvas()["getContext"])
						this.context = this.getCanvas().getContext('2d');
					return this.context;
				},
				reader : null,
				getReader : function() {
					if (!this.reader && window["FileReader"])
						this.reader = new FileReader();
					return this.reader;
				},
				load : function(file, callback, id, callbackFail) {
					if (this.active !== null || (this.getReader() && this.getReader().readyState == 1))
						return;

					this.counter++;
					this.active = file;
					var image = this.getImage();
					BX.unbindAll(image);
					image.onload = function(){};
					image.onerror = function(){};

					/* Almost all browsers cache images from local resource except of FF on 06.03.2017. It appears that
					FF collect src and does not abort image uploading when src is changed. And we had a bug when in
					onload event we got e.target.src of one element but source of image was from '/bitrix/images/1.gif'. */
					// TODO check if chrome and other browsers cache local files for now. If it does not then delete next 2 strings
					if (!BX.browser.IsFirefox())
						image.src = '/bitrix/images/1.gif';

					/** For Garbage collector */
					this.onload = null;
					delete this.onload;
					this.onerror = null;
					delete this.onerror;

					this.onload = BX.delegate(function(e){
						if (e && e.target && e.target.src && e.target.src.substr(-20) == "/bitrix/images/1.gif")
							return;
						if (!!callback)
						{
							try {
								callback(BX.proxy_context, this.getCanvas(), this.getContext(), getOrientation((((e && e.target && e.target.src) ? e.target.src : (BX.proxy_context || null)))));
							}
							catch (e)
							{
								BX.debug(e);
							}
						}
						if (!!id)
						{
							this.queue.removeItem(id);
							setTimeout(BX.proxy(function() {
								this.active = null;
								this.exec();
							}, this), this.timeLimit);
						}
						else
							this.active = null;
					}, this);
					this.onerror = BX.delegate(function(){
						if (!!callbackFail)
						{
							try
							{
								callbackFail(BX.proxy_context);
							}
							catch (e)
							{
								BX.debug(e);
							}
						}
						if (!!id)
						{
							this.queue.removeItem(id);
							setTimeout(BX.proxy(function() {
								this.active = null;
								this.exec();
							}, this), this.timeLimit);
						}
						else
							this.active = null;
					}, this);

					image.name = file.name;

					image.onload = this.onload;
					image.onerror = this.onerror;

					var res = Object.prototype.toString.call(file);
					if (file["tmp_url"])
					{
						image.src = file["tmp_url"] + (file["tmp_url"].indexOf("?") > 0 ? '&' : '?') + 'imageUploader' + this.id + this.counter;
					}
					else if (res !== '[object File]' && res !== '[object Blob]')
					{
						this.onerror(null);
					}
					else if (window["URL"])
					{
						image.src = window["URL"]["createObjectURL"](file);
					}
					else if (this.getReader() !== null)
					{
						this.__readerOnLoad = null;
						delete this.__readerOnLoad;
						this.__readerOnLoad = BX.delegate(function(e) {
							this.__readerOnLoad = null;
							delete this.__readerOnLoad;
							image.src = e.target.result;
						}, this);
						this.getReader().onloadend = this.__readerOnLoad;
						this.getReader().onerror = BX.proxy(function(e) { this.onerror(null); }, this);
						this.getReader().readAsDataURL(file);
					}
				},
				push : function(file, callback, failCallback) {
					var id = BX.UploaderUtils.getId();
					this.queue.setItem(id, [id, file, callback, failCallback]);
					this.exec();
				},
				exec : function() {
					var item = this.queue.getFirst();
					if (!!item)
						this.load(item[1], item[2], item[0], item[3]);
				},
				pack : function(fileType) {
					return  BX.UploaderUtils.dataURLToBlob(this.getCanvas().toDataURL(fileType));
				}
			};
			return cnvConstructor;
		})();
	BX.UploaderFileCnvConstr = cnvConstr;
	BX.UploaderFileFileLoader = (function(){
		var d = function(timelimit) {
			this.timeLimit = (typeof timelimit === "number" && timelimit > 0 ? timelimit : 50);
			this.status = statuses.ready;
			this.queue = new BX.UploaderUtils.Hash();
			this._exec = BX.delegate(this.exec, this);
		};
		d.prototype = {
			xhr : null,
			goToNext : function(id)
			{
				delete this.xhr;
				this.xhr = null;
				this.queue.removeItem(id);
				this.status = statuses.ready;
				setTimeout(this._exec, this.timeLimit);
			},
			load : function(id, path, onsuccess, onfailure)
			{
				if (this.status != statuses.ready)
					return;
				this.status = statuses.inprogress;
				var _this = this;
				this.xhr = BX.ajax({
					'method': 'GET',
					'data' : '',
					'url': path,
					'onsuccess': function(blob){if (blob === null){onfailure(blob);} else {onsuccess(blob);} _this.goToNext(id);},
					'onfailure': function(blob){onfailure(blob); _this.goToNext(id);},
					'start': false,
					'preparePost':false,
					'processData':false
				});
				this.xhr.withCredentials = true;
				this.xhr.responseType = 'blob';

				this.xhr.send();
			},
			push : function(path, onsuccess, onfailure)
			{
				var id = BX.UploaderUtils.getId();
				this.queue.setItem(id, [id, path, onsuccess, onfailure]);
				this.exec();
			},
			exec : function()
			{
				var item = this.queue.getFirst();
				if (!!item)
					this.load(item[0], item[1], item[2], item[3]);
			}
		};
		return d;
	})();
	var prvw = new cnvConstr(), upld = new cnvConstr(), edtr = new cnvConstr(), canvas = BX.create('CANVAS'), ctx;
	/**
	 * @return {BX.UploaderFile}
	 * @file file
	 * @params array
	 * @limits array
	 * @caller {BX.Uploader}
	 * You should work with params["fields"] in case you want to change visual part
	 */
var mobileNames = {};
	BX.UploaderFile = function (file, params, limits, caller)
	{
		this.dialogName = (this.dialogName ? this.dialogName : "BX.UploaderFile");
		this.file = file;
		this.id = (file['id'] || 'file' + BX.UploaderUtils.getId());
		this.name = file.name;
		this.isNode = false;
		if (BX.type.isDomNode(file))
		{
			this.isNode = true;
			this.name = BX.UploaderUtils.getFileNameOnly(file.value);
			if (/\[(.+?)\]/.test(file.name))
			{
				var tmp = /\[(.+?)\]/.exec(file.name);
				this.id = tmp[1];
			}
			this.file.bxuHandler = this;
		}
		else if (file["tmp_url"] && !file["name"])
		{
			this.name = BX.UploaderUtils.getFileNameOnly(file["tmp_url"]);
		}
		this.preview = '<span id="' + this.id + 'Canvas" class="bx-bxu-canvas"></span>';
		this.nameWithoutExt = (this.name.lastIndexOf('.') > 0 ? this.name.substr(0, this.name.lastIndexOf('.')) : this.name);
		this.ext = this.name.substr(this.nameWithoutExt.length + 1);

		if (/iPhone|iPad|iPod/i.test(navigator.userAgent) && this.nameWithoutExt == "image")
		{
			var nameWithoutExt = 'mobile_' + BX.date.format("Ymd_His");
			mobileNames[nameWithoutExt] = (mobileNames[nameWithoutExt] || 0);
			this.nameWithoutExt = nameWithoutExt + (mobileNames[nameWithoutExt] > 0 ? ("_" + mobileNames[nameWithoutExt]) : "");
			this.name = this.nameWithoutExt + (BX.type.isNotEmptyString(this.ext) ? ("." + this.ext) : "");
			mobileNames[nameWithoutExt]++;
		}

		this.size = '';
		if (file.size)
			this.size = BX.UploaderUtils.getFormattedSize(file.size, 0);
		this.type = file.type;
		this.status = statuses["new"];
		this.limits = limits;
		this.caller = caller;
		this.fields = {
			thumb : {
				tagName : 'SPAN',
				template : '<div class="someclass">#preview#<div>#name#</div>',
				editorTemplate : '<div class="someeditorclass"><div>#name#</div>',
				className : "bx-bxu-thumb-thumb",
				placeHolder : null
			},
			preview : {
				params : { width : 400, height : 400 },
				template : "#preview#",
				editorParams : { width : 1024, height : 860 },
				editorTemplate : '<span>#preview#</span>',
				className : "bx-bxu-thumb-preview",
				placeHolder : null,
				events : {
					click : BX.delegate(this.clickFile, this)
				},
				type : "html"
			},
			name : {
				template : "#name#",
				editorTemplate : '<span><input type="text" name="name" value="#name#" /></span>',
				className : "bx-bxu-thumb-name",
				placeHolder : null
			},
			type : {
				template : "#type#",
				editorTemplate : '#type#',
				className : "bx-bxu-thumb-type",
				placeHolder : null
			}
		};

		if (!!params["fields"])
		{
			var ij, key;
			for (var ii in params["fields"])
			{
				if (params["fields"].hasOwnProperty(ii))
				{
					if (!!this.fields[ii])
					{
						for (ij in params["fields"][ii])
						{
							if (params["fields"][ii].hasOwnProperty(ij))
							{
								this.fields[ii][ij] = params["fields"][ii][ij];
							}
						}
					}
					else
						this.fields[ii] = params["fields"][ii];
					key = ii + '';
					if (key.toLowerCase() != "thumb" && key.toLowerCase() != "preview")
					{
						this[key.toLowerCase()] = (!!params["fields"][ii]["value"] ? params["fields"][ii]["value"] : "");
						this.log(key.toLowerCase() + ': ' + this[key.toLowerCase()]);
					}
				}
			}
		}

		BX.onCustomEvent(this, "onFileIsCreated", [this.id, this, this.caller]);
		BX.onCustomEvent(this.caller, "onFileIsCreated", [this.id, this, this.caller]);

		this.makePreview();
		this.preparationStatus = statuses.done;
		return this;
	};
	BX.UploaderFile.prototype = {
		log : function(text)
		{
			BX.UploaderUtils.log('file ' + this.name, text);
		},
		makeThumb : function()
		{
			var template = this.fields.thumb.template, name, ii, events = {}, node, jj;
			for (ii in this.fields)
			{
				if (this.fields.hasOwnProperty(ii))
				{
					if (this.fields[ii].template && this.fields[ii].template.indexOf('#' + ii + '#') >= 0)
					{
						name = this.id + ii.toUpperCase().substr(0, 1) + ii.substr(1);
						node = this.setProps(ii, this[ii], true);
						template = template.replace('#' + ii + '#', '<span id="' + name + '" class="' + this.fields[ii]["className"] + '">' + (
							BX.type.isNotEmptyString(node.html) ? node.html.replace("#", "<\x19>") : node.html) + '</span>');
						for (jj in node.events)
						{
							if (node.events.hasOwnProperty(jj))
							{
								events[jj] = node.events[jj];
							}
						}
						if (!!this.fields[ii].events)
							events[name] = this.fields[ii].events;
					}
				}
			}
			var res, patt = [], repl = [], tmp;
			while ((res = /#([^\\<\\>\\"\\']+?)#/gi.exec(template)) && !!res)
			{
				if (this[res[1]] !== undefined)
				{
					template = template.replace(res[0], BX.type.isNotEmptyString(this[res[1]]) ? this[res[1]].replace("#", "<\x19>") : this[res[1]]);
				}
				else
				{
					tmp = "<\x18" + patt.length + ">";
					patt.push(tmp);
					repl.push(res[0]);
					template = template.replace(res[0], tmp);
				}
			}
			while ((res = patt.shift()) && res)
			{
				tmp = repl.shift();
				template = template.replace(res, tmp);
			}
			template = template.replace("<\x19>", "#");
			if (!!this.fields.thumb.tagName)
			{
				res = BX.create(this.fields.thumb.tagName, {
					attrs : {
						id : (this.id + 'Thumb'),
						className : this.fields.thumb.className
					},
					events : this.fields.thumb.events,
					html : template
					}
				);
			}
			else
			{
				res = template;
			}
			this.__makeThumbEventsObj = events;
			this.__makeThumbEvents = BX.delegate(function()
			{
				var ii, jj;
				for (ii in events)
				{
					if (events.hasOwnProperty(ii) && BX(ii))
					{
						for (jj in events[ii])
						{
							if (events[ii].hasOwnProperty(jj))
							{
								BX.bind(BX(ii), jj, events[ii][jj]);
							}
						}
					}
				}
				this.__makeThumbEvents = null;
				delete this.__makeThumbEvents;
			}, this);
			BX.addCustomEvent(this, "onFileIsAppended", this.__makeThumbEvents);

			if (BX.type.isDomNode(this.file))
			{
				if (BX.type.isString(template))
				{
					this.__bindFileNode = BX.delegate(function(id)
					{
						var node = BX(id + 'Item');
						if (node.tagName == 'TR')
							node.cells[0].appendChild(this.file);
						else if (node.tagName == 'TABLE')
							node.rows[0].cells[0].appendChild(this.file);
						else
							BX(id + 'Item').appendChild(this.file);
						this.__bindFileNode = null;
						delete this.__bindFileNode;
					}, this);
					BX.addCustomEvent(this, "onFileIsAppended", this.__bindFileNode);
				}
				else
				{
					res.appendChild(this.file);
				}
			}
			return res;
		},
		checkProps : function()
		{
			var el2 = BX.UploaderUtils.FormToArray({elements : [BX.proxy_context]}), ii;
			for (ii in el2.data)
			{
				if (el2.data.hasOwnProperty(ii))
					this[ii] = el2.data[ii];
			}
		},
		setProps : function(name, val, bReturn)
		{
			if (typeof name == "string")
			{
				if (name == "size")
					val = BX.UploaderUtils.getFormattedSize(this.file.size, 0);
				if (typeof this[name] != "undefined" && typeof this.fields[name] != "undefined")
				{
					this[name] = val;
					var template = this.fields[name].template.
							replace('#' + name + '#', this.fields[name]["type"] === "html" ? (val || '') : BX.util.htmlspecialchars(val || '')).
							replace(/#id#/gi, this.id),
						fii, fjj, el, result = {html : template, events : {}};

					this.hiddenForm = (!!this.hiddenForm ? this.hiddenForm : BX.create("FORM", { style : { display : "none" } } ));
					this._checkProps = (!!this._checkProps ? this._checkProps : BX.delegate(this.checkProps, this));
					this.hiddenForm.innerHTML = template;
					if (this.hiddenForm.elements.length > 0)
					{
						for (fii = 0; fii < this.hiddenForm.elements.length; fii++)
						{
							el = this.hiddenForm.elements[fii];
							if (typeof this[el.name] != "undefined")
							{
								if (!el.hasAttribute("id"))
									el.setAttribute("id", this.id + name + BX.UploaderUtils.getId());
								result.events[el.id] = {
									blur : this._checkProps
								}

							}
						}
						result.html = this.hiddenForm.innerHTML;
					}
					if (BX(this.hiddenForm))
						BX.remove(this.hiddenForm);
					this.hiddenForm = null;
					delete this.hiddenForm;
					if (bReturn)
						return result;
					var node = this.getPH(name);
					if (!!node)
					{
						node.innerHTML = result.html;
						for (fii in result.events)
						{
							if (result.events.hasOwnProperty(fii))
							{
								for (fjj in result.events[fii])
								{
									if (result.events[fii].hasOwnProperty(fjj))
									{
										BX.bind(BX(fii), fjj, result.events[fii][fjj]);
									}
								}
							}
						}
					}
				}
			}
			else if (!!name)
			{
				for (var ij in name)
				{
					if (name.hasOwnProperty(ij))
					{
						if (this.fields.hasOwnProperty(ij) && ij !== "preview")
							this.setProps(ij, name[ij]);
					}
				}
			}
			return true;
		},
		getProps : function(name)
		{
			if (name == "canvas")
			{
				return BX(this.id + "ProperCanvas");
			}
			else if (typeof name == "string")
			{
				return this[name];
			}
			var data = {};
			for (var ii in this.fields)
			{
				if (this.fields.hasOwnProperty(ii) && (ii !== "preview" && ii !== "thumb"))
				{
					data[ii] = this[ii];
				}
			}
			data["size"] = this.file["size"];
			data["type"] = this["type"];
			if (!!this.copies)
			{
				var copy;
				data["canvases"] = {};
				while ((copy = this.copies.getNext()) && !!copy)
				{
					data["canvases"][copy.id] = { width : copy.width, height : copy.height, name : copy.name };
				}
			}
			return data;
		},
		getThumbs : function()
		{
			return null;
		},
		getPH : function(name)
		{
			name = (typeof name === "string" ? name : "");
			name = name.toLowerCase();
			if (this.fields.hasOwnProperty(name))
			{
				var id = name.substr(0, 1).toUpperCase() + name.substr(1);
				this.fields[name]["placeHolder"] = BX(this.id  + id);
				return this.fields[name]["placeHolder"];
			}
			return null;
		},
		clickFile : function ()
		{
			return false;
		},
		makePreview: function()
		{
			this.status = statuses.ready;
			BX.onCustomEvent(this, "onFileIsInited", [this.id, this, this.caller]);
			BX.onCustomEvent(this.caller, "onFileIsInited", [this.id, this, this.caller]);

			this.log('is initialized as a file');
		},
		preparationStatus : statuses.ready,
		deleteFile: function()
		{
			var ii, events = this.__makeThumbEventsObj;
			for (ii in this.fields)
			{
				if (this.fields.hasOwnProperty(ii))
				{
					if (!!this.fields[ii]["placeHolder"])
					{
						this.fields[ii]["placeHolder"] = null;
						BX.unbindAll(this.fields[ii]["placeHolder"]);
						delete this.fields[ii]["placeHolder"];
					}
				}
			}

			for (ii in events)
			{
				if (events.hasOwnProperty(ii) && BX(ii))
				{
					BX.unbindAll(BX(ii));
				}
			}

			this.file = null;
			delete this.file;

			BX.remove(this.canvas);
			this.canvas = null;
			delete this.canvas;

			BX.onCustomEvent(this.caller, "onFileIsDeleted", [this.id, this, this.caller]);
			BX.onCustomEvent(this, "onFileIsDeleted", [this, this.caller]);
		}
	};
	BX.UploaderImage = function(file, params, limits, caller)
	{
		this.dialogName = "BX.UploaderImage";
		BX.UploaderImage.superclass.constructor.apply(this, arguments);
		this.isImage = true;
		this.copies = new BX.UploaderUtils.Hash();
		this.caller = caller;

		if (!this.isNode && BX.Uploader.getInstanceName() == "BX.Uploader")
		{
			if (!!params["copies"])
			{
				var copies = params["copies"], copy;
				for (var ii in copies)
				{
					if (copies.hasOwnProperty(ii) && !!copies[ii])
					{
						copy = { width : parseInt(copies[ii]['width']), height : parseInt(copies[ii]["height"]), id : ii };
						if (copy['width'] > 0 && copy["height"] > 0)
						{
							this.copies.setItem(ii, copy);
						}
					}
				}
			}
			this.preparationStatus = statuses["new"];
			BX.addCustomEvent(this, "onFileHasToBePrepared", BX.delegate(function()
			{
				this.preparationStatus = statuses.inprogress;
				if (this.status != statuses["new"])
				{
					upld.push(this.file, BX.delegate(this.makeCopies, this));
				}
			}, this));
			BX.addCustomEvent(this, "onUploadDone", BX.delegate(function()
			{
				var copy;
				while ((copy = this.copies.getNext()) && !!copy)
				{
					copy.file = null;
					delete copy.file;
				}
				this.preparationStatus = statuses["new"];
			}, this));
			this.canvas = BX.create('CANVAS', {attrs : { id : this.id + "ProperCanvas" } } );
		}
		else
		{
			this.preparationStatus = statuses.done;
			this.canvas = null;
		}
		return this;
	};
	BX.extend(BX.UploaderImage, BX.UploaderFile);
	BX.UploaderImage.prototype.makePreviewImageWork = function(image, cnv, ctx, exifOrientation)
	{
		exifOrientation = parseInt(exifOrientation);

		var result = null,
			width = cnv.width,
			height = cnv.height;

		if (this.file)
		{
			this.file.width = cnv.width;
			this.file.height = cnv.height;
		}

		if (!!this.canvas)
		{
			setOrientation(image, cnv, ctx, exifOrientation);
			if (this.file)
			{
				this.file.width = cnv.width;
				this.file.height = cnv.height;
				if (exifOrientation)
				{
					this.file.exif = {
						Orientation : exifOrientation
					}
				}
			}
			this.applyFile(cnv, false);
			result = this.canvas;
		}
		else if (BX(this.id + 'Canvas'))
		{
			var res2 = BX.UploaderUtils.scaleImage({width : width, height : height}, this.fields.preview.params),
				props = {
					props : { width : res2.destin.width, height : res2.destin.height, src : image.src },
					attrs : {
						className : (this.file.width > this.file.height ? "landscape" : "portrait")
					}
				};
			switch (exifOrientation)
			{
				case 2:
					props.attrs.className += ' flip'; break;
				case 3:
					props.attrs.className += ' rotate-180'; break;
				case 4:
					props.attrs.className += ' flip-and-rotate-180'; break;
				case 5:
					props.attrs.className += ' flip-and-rotate-270'; break;
				case 6:
					props.attrs.className += ' rotate-90'; break;
				case 7:
					props.attrs.className += ' flip-and-rotate-90'; break;
				case 8:
					props.attrs.className += ' rotate-270'; break;
			}
			result = BX.create("IMG", props);
		}

		BX.onCustomEvent(this, "onFileCanvasIsLoaded", [this.id, this, this.caller, image]);
		BX.onCustomEvent(this.caller, "onFileCanvasIsLoaded", [this.id, this, this.caller, image]);

		if (BX(this.id + 'Canvas'))
			BX(this.id + 'Canvas').appendChild(result);

		return result;
	};

	BX.UploaderImage.prototype.makePreviewImageLoadHandler = function(image, canvas, context, exifOrientation){
		this.makePreviewImageWork(image, canvas, context, exifOrientation);
		this.status = statuses.ready;

		BX.onCustomEvent(this, "onFileIsInited", [this.id, this, this.caller]);
		BX.onCustomEvent(this.caller, "onFileIsInited", [this.id, this, this.caller]);
		this.log('is initialized as an image with preview');
		if (this.preparationStatus == statuses.inprogress)
			this.makeCopies(image, canvas, context, exifOrientation);
		if (this["_makePreviewImageLoadHandler"])
		{
			this._makePreviewImageLoadHandler = null;
			delete this._makePreviewImageLoadHandler;
		}
		if (this["_makePreviewImageFailedHandler"])
		{
			this._makePreviewImageFailedHandler = null;
			delete this._makePreviewImageFailedHandler;
		}
	};

	BX.UploaderImage.prototype.makePreviewImageFailedHandler = function(){
		this.status = statuses.ready;
		this.preparationStatus = statuses.done;

		BX.onCustomEvent(this, "onFileIsInited", [this.id, this, this.caller]);
		BX.onCustomEvent(this.caller, "onFileIsInited", [this.id, this, this.caller]);

		this.log('is initialized without canvas');
		if (this["_makePreviewImageLoadHandler"])
		{
			this._makePreviewImageLoadHandler = null;
			delete this._makePreviewImageLoadHandler;
		}
		if (this["_makePreviewImageFailedHandler"])
		{
			this._makePreviewImageFailedHandler = null;
			delete this._makePreviewImageFailedHandler;
		}
	};
	BX.UploaderImage.prototype.makePreview = function()
	{
		if (!this.isNode)
		{
			this._makePreviewImageLoadHandler = BX.delegate(this.makePreviewImageLoadHandler, this);
			this._makePreviewImageFailedHandler = BX.delegate(this.makePreviewImageFailedHandler, this);
			prvw.push(this.file, this._makePreviewImageLoadHandler, this._makePreviewImageFailedHandler);
		}
		else
		{
			this.status = statuses.ready;
			BX.onCustomEvent(this, "onFileIsInited", [this.id, this, this.caller]);
			BX.onCustomEvent(this.caller, "onFileIsInited", [this.id, this, this.caller]);

			this.log('is initialized as an image without preview');
			if (this.caller.queue.placeHolder)
			{
				this._onFileHasGotPreview = BX.delegate(function(id, item) {

					BX.removeCustomEvent(this, "onFileHasGotPreview", this._onFileHasGotPreview);
					BX.removeCustomEvent(this, "onFileHasNotGotPreview", this._onFileHasNotGotPreview);

					this._makePreviewImageLoadHandler = BX.delegate(function(image){
						image = this.makePreviewImageWork(image);
						BX.onCustomEvent(this, "onFileHasPreview", [item.id, item, image]);
						delete this._makePreviewImageLoadHandler;
						delete this._makePreviewImageFailedHandler;
					}, this);
					this._makePreviewImageFailedHandler = BX.delegate(function(image){
						delete this._makePreviewImageLoadHandler;
						delete this._makePreviewImageFailedHandler;
					}, this);
					prvw.push({tmp_url : item.file.url}, this._makePreviewImageLoadHandler, this._makePreviewImageFailedHandler);
				}, this);
				this._onFileHasNotGotPreview = BX.delegate(function(id){
					if (id == this.id)
					{
						BX.removeCustomEvent(this, "onFileHasGotPreview", this._onFileHasGotPreview);
						BX.removeCustomEvent(this, "onFileHasNotGotPreview", this._onFileHasNotGotPreview);
					}
				}, this);
				BX.addCustomEvent(this, "onFileHasGotPreview", this._onFileHasGotPreview);
				BX.addCustomEvent(this, "onFileHasNotGotPreview", this._onFileHasNotGotPreview);
				BX.onCustomEvent(this.caller, "onFileNeedsPreview", [this.id, this, this.caller]);
			}
		}
		return true;
	};
	BX.UploaderImage.prototype.checkPreview = function()
	{
		// TODO check preview
	};
	BX.UploaderImage.prototype.applyFile = function(cnv, params)
	{
		this.checkPreview();

		if (!!params && params.data )
			this.setProps(params.data);

		var realScale = BX.UploaderUtils.scaleImage(cnv, {width : this.limits["uploadFileWidth"], height : this.limits["uploadFileHeight"]}),
			prvwScale = BX.UploaderUtils.scaleImage(cnv, this.fields.preview.params),
			prvwProps = {
				props : { width : prvwScale.destin.width, height : prvwScale.destin.height },
				attrs : {
					className : "bx-bxu-proper-canvas"+(prvwScale.destin.width > prvwScale.destin.height ? " landscape" : " portrait")
				}
			};

		if (realScale.bNeedCreatePicture || !!params)
		{
			BX.adjust(canvas, { props : { width : realScale.destin.width, height : realScale.destin.height } } );
			ctx = canvas.getContext('2d');
			ctx.drawImage(cnv,
				realScale.source.x, realScale.source.y, realScale.source.width, realScale.source.height,
				realScale.destin.x, realScale.destin.y, realScale.destin.width, realScale.destin.height
			);

			var dataURI = canvas.toDataURL(this.file.type);
			this.file = BX.UploaderUtils.dataURLToBlob(dataURI);
		}

		this.file.name = this.name;
		this.file.width = realScale.destin.width;
		this.file.height = realScale.destin.height;

		BX.adjust(this.canvas, prvwProps);

		ctx = this.canvas.getContext('2d');
		ctx.drawImage(cnv,
			prvwScale.source.x, prvwScale.source.y, prvwScale.source.width, prvwScale.source.height,
			prvwScale.destin.x, prvwScale.destin.y, prvwScale.destin.width, prvwScale.destin.height
		);

		ctx = null;
		cnv = null;

		this.setProps('size');
		this.status = statuses.changed;
	};
	BX.UploaderImage.prototype.clickFile = function()
	{
		if (!this.canvas || !BX["CanvasEditor"] || this.status == statuses["new"])
			return false;
		if (!this.__showEditor)
		{
			this.__showEditor = BX.delegate(this.showEditor, this);
			this.eFunc = {
				"apply" : BX.delegate(this.applyFile, this),
				"delete" : BX.delegate(this.deleteFile, this),
				"clear" : BX.delegate(function()
				{
					BX.removeCustomEvent(editor, "onApplyCanvas", this.eFunc["apply"]);
					BX.removeCustomEvent(editor, "onDeleteCanvas", this.eFunc["delete"]);
					BX.removeCustomEvent(editor, "onClose", this.eFunc["clear"]);
				}, this)
			};
		}
		var template = this.fields.thumb.editorTemplate, name;
		for (var ii in this.fields)
		{
			if (this.fields.hasOwnProperty(ii))
			{
				name = ii.substr(0, 1).toUpperCase() + ii.substr(1);
				template = template.replace('#' + ii + '#',
					(ii === "preview" ? "" :
						('<span id="' + this.id + name + 'Editor" class="' + this.fields[ii]["className"] + '">' +
						this.fields[ii]["editorTemplate"].replace('#' + ii + '#', (!!this[ii] ? BX.util.htmlspecialchars(this[ii]) : '')) + '</span>')));
			}
		}

		BX.adjust(edtr.getCanvas(), { props : { width : this.file.width, height : this.file.height } } );
		edtr.getContext().drawImage(this.canvas,
			0, 0, this.canvas.width, this.canvas.height,
			0, 0, edtr.getCanvas().width, edtr.getCanvas().height);
		var editor = BX.CanvasEditor.show(edtr.getCanvas(), {title : this.name, template : template});

		BX.addCustomEvent(editor, "onApplyCanvas", this.eFunc["apply"]);
		BX.addCustomEvent(editor, "onDeleteCanvas", this.eFunc["delete"]);
		BX.addCustomEvent(editor, "onClose", this.eFunc["clear"]);
		BX.onCustomEvent(this, "onCanvasEditorIsCreated", [editor, this]);

		edtr.push(this.file, this.__showEditor);
		this.editor = editor;
		return false;
	};
	BX.UploaderImage.prototype.showEditor = function(image, canvas, context, exifOrientation)
	{
		BX.adjust(canvas, { props : { width : this.file.width, height : this.file.height } } );
		setOrientation(image, canvas, context, exifOrientation);
		this.editor.copyCanvas(canvas);
	};
	BX.UploaderImage.prototype.makeCopies = function(image, cnv, ctx, exifOrientation)
	{
		var copy, res, dataURI, result,
			context = canvas.getContext('2d');
		setOrientation(image, canvas, context, exifOrientation);
		while ((copy = this.copies.getNext()) && !!copy)
		{
			res = BX.UploaderUtils.scaleImage(canvas, copy);
			BX.adjust(cnv, {props : { width : res.destin.width, height : res.destin.height } } );
			ctx.drawImage(canvas,
				res.source.x, res.source.y, res.source.width, res.source.height,
				res.destin.x, res.destin.y, res.destin.width, res.destin.height
			);

			dataURI = cnv.toDataURL(this.file.type);
			result = BX.UploaderUtils.dataURLToBlob(dataURI);
			result.width = cnv.width;
			result.height = cnv.height;
			result.name = this.name;
			result.thumb = copy.id;
			result.canvases = this.copies.length;
			result.canvas = this.copies.pointer - 1;
			copy.file = result;
		}
		this.preparationStatus = statuses.done;
	};
	BX.UploaderImage.prototype.getThumbs = function(name)
	{
		if (name == "getCount")
			return this.copies.length;

		var res = (typeof name == "string" ? this.copies.getItem(name) : this.copies.getNext());

		if (!!res)
			return res.file;
		return null;
	};
	return true;
}(window));
