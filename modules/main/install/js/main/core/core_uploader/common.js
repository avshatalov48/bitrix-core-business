;(function(window){
	window.BX = window['BX'] || {};
	if (window.BX["UploaderUtils"])
		return false;
	var BX = window.BX;
	BX.UploaderLog = [];
	BX.UploaderDebug = false;
	var statuses = { "new" : 0, ready : 1, preparing : 2, inprogress : 3, done : 4, failed : 5, stopped : 6, changed : 7, uploaded : 8};
	BX.UploaderUtils = {
		statuses : statuses,
		getId : function() { return (new Date().valueOf() + Math.round(Math.random() * 1000000)); },
		log : function(){
			if (BX.UploaderDebug === true)
			{
				console.log(arguments);
			}
			else
			{
				BX.UploaderLog.push(arguments);
			}
		},
		Hash : (function(){
			var d = function() {
				this.length = 0;
				this.items = {};
				this.order = [];
				var i;
				if (arguments.length == 1 && BX.type.isArray(arguments[0]) && arguments[0].length > 0)
				{
					var data = arguments[0];
					for (i = 0; i < data.length; i++)
					{
						if (data[i] && typeof data[i] == "object" && data[i]["id"])
						{
							this.setItem(data[i]["id"], data[i]);
						}
					}
				}
				else
				{
					for (i = 0; i < arguments.length; i += 2)
						this.setItem(arguments[i], arguments[i + 1]);
				}
			};
			d.prototype = {
				getIds : function()
				{
					return this.order;
				},
				getQueue : function(id)
				{
					id += '';
					return BX.util.array_search(id, this.order);
				},
				getByOrder : function(order)
				{
					return this.getItem(this.order[order]);
				},
				removeItem : function(in_key)
				{
					in_key += '';
					var tmp_value, number;
					if (typeof(this.items[in_key]) != 'undefined') {
						tmp_value = this.items[in_key];
						number = this.getQueue(in_key);
						this.pointer -= (this.pointer >= number ? 1 : 0);
						delete this.items[in_key];
						this.order = BX.util.deleteFromArray(this.order, number);
						this.length = this.order.length;

					}
					return tmp_value;
				},

				getItem : function(in_key) {
					in_key += '';
					return this.items[in_key];
				},

				unshiftItem : function(in_key, in_value)
				{
					in_key += '';
					if (typeof(in_value) != 'undefined')
					{
						if (typeof(this.items[in_key]) == 'undefined')
						{
							this.order.unshift(in_key);
							this.length = this.order.length;
						}
						this.items[in_key] = in_value;
					}
					return in_value;
				},
				setItem : function(in_key, in_value)
				{
					in_key += '';
					if (typeof(in_value) != 'undefined')
					{
						if (typeof(this.items[in_key]) == 'undefined')
						{
							this.order.push(in_key);
							this.length = this.order.length;
						}
						this.items[in_key] = in_value;
					}
					return in_value;
				},

				hasItem : function(in_key)
				{
					in_key += '';
					return typeof(this.items[in_key]) != 'undefined';
				},
				insertBeforeItem : function(in_key, in_value, after_key)
				{
					in_key += '';
					if (typeof(in_value) != 'undefined')
					{
						if (typeof(this.items[in_key]) == 'undefined')
						{
							this.order.splice(this.getQueue(after_key), 0, in_key);
							this.length = this.order.length;
						}
						this.items[in_key] = in_value;
					}
					return in_value;
				},
				getFirst : function()
				{
					var in_key, item = null;
					for (var ii = 0; ii < this.order.length; ii++)
					{
						in_key = this.order[ii];
						if (!!in_key && this.hasItem(in_key))
						{
							item = this.getItem(in_key);
							break;
						}
					}
					return item;
				},
				getNext : function()
				{
					this.pointer = (0 <= this.pointer && this.pointer < this.order.length ? this.pointer : -1);
					var res = this.getItem(this.order[this.pointer + 1]);
					if (!!res)
						this.pointer++;
					else
						this.pointer = -1;
					return res;
				},
				getPrev : function()
				{
					this.pointer = (0 <= this.pointer && this.pointer < this.order.length ? this.pointer : 0);
					var res = this.getItem(this.order[this.pointer - 1]);
					if (!!res)
						this.pointer--;
					return res;
				},
				reset : function()
				{
					this.pointer = -1;
				},
				setPointer : function(in_key)
				{
					this.pointer = this.getQueue(in_key);
					return this.pointer;
				},
				getLast : function()
				{
					var in_key, item = null;
					for (var ii = this.order.length; ii >=0; ii--)
					{
						in_key = this.order[ii];
						if (!!in_key && this.hasItem(in_key))
						{
							item = this.getItem(in_key);
							break;
						}
					}
					return item;
				}
			};
			return d;
		})(),
		getFileNameOnly : function (name)
		{
			var delimiter = "\\", start = name.lastIndexOf(delimiter), finish = name.length;
			if (start == -1)
			{
				delimiter = "/";
				start = name.lastIndexOf(delimiter);
			}
			if ((start + 1) == name.length)
			{
				finish = start;
				start = name.substring(0, finish).lastIndexOf(delimiter);
			}
			name = name.substring(start + 1, finish);
			if (delimiter == "/" && name.indexOf("?") > 0)
			{
				name = name.substring(0, name.indexOf("?"));
			}

			if (name == '')
				name = 'noname';
			return name;
		},
		isImageExt : function(ext)
		{
			return (BX.message('bxImageExtensions') && BX.type.isNotEmptyString(ext) ?
				(new RegExp('(?:^|\\W)(' + ext + ')(?:\\W|$)', 'gi')).test(BX.message('bxImageExtensions')) :
				false
			);
		},
		isImage : function(name, type, size)
		{
			size = BX.type.isNumber(size) ? size : (BX.type.isNotEmptyString(size) && !(/[\D]+/gi.test(size)) ? parseInt(size) : null);
			return (
				(type === null || (type || '').indexOf("image/") === 0) &&
				(size === null || (size < 20 * 1024 * 1024)) &&
				BX.UploaderUtils.isImageExt((name || '').lastIndexOf('.') > 0 ? name.substr(name.lastIndexOf('.')+1).toLowerCase() : ''));
		},
		scaleImage : function(arSourceSize, arSize, resizeType)
		{
			var sourceImageWidth = parseInt(arSourceSize["width"]), sourceImageHeight = parseInt(arSourceSize["height"]);
			resizeType = (!resizeType && !!arSize["type"] ? arSize["type"] : resizeType);
			arSize = (!!arSize ? arSize : {});
			arSize.width = parseInt(!!arSize.width ? arSize.width : 0);
			arSize.height = parseInt(!!arSize.height ? arSize.height : 0);

			var res = {
					bNeedCreatePicture : false,
					source : {x : 0, y : 0, width : 0, height : 0},
					destin : {x : 0, y : 0, width : 0, height : 0}
			}, width, height;

			if (!(sourceImageWidth > 0 || sourceImageHeight > 0))
			{
				BX.DoNothing();
			}
			else
			{
				if (!BX.type.isNotEmptyString(resizeType))
				{
					resizeType = "inscribed";
				}


				var ResizeCoeff, iResizeCoeff;

				if (resizeType.indexOf("proportional") >= 0)
				{
					width = Math.max(sourceImageWidth, sourceImageHeight);
					height = Math.min(sourceImageWidth, sourceImageHeight);
				}
				else
				{
					width = sourceImageWidth;
					height = sourceImageHeight;
				}
				if (resizeType == "exact")
				{
					var
						ratio = (sourceImageWidth / sourceImageHeight < arSize["width"] / arSize["height"] ? arSize["width"] / sourceImageWidth : arSize["height"] / sourceImageHeight),
						x = Math.max(0, Math.round(sourceImageWidth / 2 - (arSize["width"] / 2) / ratio)),
						y = Math.max(0, Math.round(sourceImageHeight / 2 - (arSize["height"] / 2) / ratio));

					res.bNeedCreatePicture = true;
					res.coeff = ratio;

					res.destin["width"] = arSize["width"];
					res.destin["height"] = arSize["height"];

					res.source["x"] = x;
					res.source["y"] = y;
					res.source["width"] = Math.round(arSize["width"] / ratio, 0);
					res.source["height"] = Math.round(arSize["height"] / ratio, 0);
				}
				else
				{
					if (resizeType == "circumscribed")
					{
						ResizeCoeff = {
							width : (width > 0 ? arSize["width"] / width : 1),
							height: (height > 0 ? arSize["height"] / height : 1)};

						iResizeCoeff = Math.max(ResizeCoeff["width"], ResizeCoeff["height"], 1);
					}
					else
					{
						ResizeCoeff = {
							width : (width > 0 ? arSize["width"] / width : 1),
							height: (height > 0 ? arSize["height"] / height : 1)};

						iResizeCoeff = Math.min(ResizeCoeff["width"], ResizeCoeff["height"], 1);
						iResizeCoeff = (0 < iResizeCoeff ? iResizeCoeff : 1);
					}
					res.bNeedCreatePicture = (iResizeCoeff != 1);
					res.coeff = iResizeCoeff;
					res.destin["width"] = Math.max(1, parseInt(iResizeCoeff * sourceImageWidth));
					res.destin["height"] = Math.max(1, parseInt(iResizeCoeff * sourceImageHeight));

					res.source["x"] = 0;
					res.source["y"] = 0;
					res.source["width"] = sourceImageWidth;
					res.source["height"] = sourceImageHeight;
				}

			}
			return res;
		},
		dataURLToBlob : function(dataURL)
		{
			var marker = ';base64,', parts, contentType, raw, rawLength;
			if(dataURL.indexOf(marker) == -1) {
				parts = dataURL.split(',');
				contentType = parts[0].split(':')[1];
				raw = parts[1];
				return new Blob([raw], {type: contentType});
			}

			parts = dataURL.split(marker);
			contentType = parts[0].split(':')[1];
			raw = window.atob(parts[1]);
			rawLength = raw.length;

			var uInt8Array = new Uint8Array(rawLength);

			for(var i = 0; i < rawLength; ++i) {
				uInt8Array[i] = raw.charCodeAt(i);
			}

			return new Blob([uInt8Array], {type: contentType});
		},
		sizeof : function(obj) {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty(key))
				{
					size += key.length;
					if (typeof obj[key] == "object")
					{
						if (obj[key] === null)
							BX.DoNothing();
						else if (obj[key]["size"] > 0)
							size += obj[key].size;
						else
							size += BX.UploaderUtils.sizeof(obj[key]);
					}
					else if (typeof obj[key] == "number")
					{
						size += obj[key].toString().length;
					}
					else if (!!obj[key] && obj[key].length > 0)
					{
						size += obj[key].length;
					}
				}
			}
			return size;
		},
		FormToArray : function(form, data)
		{
			return BX.ajax.prepareForm(form, data);
		},
		getFormattedSize : function (size, precision)
		{
			var a = ["b", "Kb", "Mb", "Gb", "Tb"], pos = 0;
			while(size >= 1024 && pos < 4)
			{
				size /= 1024;
				pos++;
			}
			return (Math.round(size * (precision > 0 ? precision * 10 : 1) ) / (precision > 0 ? precision * 10 : 1)) +
				" " + BX.message("FILE_SIZE_" + a[pos]);
		},
		bindEvents : function(obj, event, func)
		{
			var funcs = [], ii;
			if (typeof func == "string")
			{
				eval('funcs.push(' + func + ');');
			}
			else if (!!func["length"] && func["length"] > 0)
			{
				for(ii = 0; ii < func.length; ii++)
				{
					if (typeof func[ii] == "string")
						eval('funcs.push(' + func[ii] + ');');
					else
						funcs.push(func[ii]);
				}
			}
			else
				funcs.push(func);
			if (funcs.length > 0)
			{
				for (ii = 0; ii < funcs.length; ii++)
				{
					BX.addCustomEvent(obj, event, funcs[ii]);
				}
			}

		},
		applyFilePart : function(file, blob)
		{
			if (BX.type.isDomNode(file))
			{
				file.uploadStatus = statuses.done;
			}
			else if (file == blob)
			{
				file.uploadStatus = statuses.done;
			}
			else if (file.blobed === true)
			{
				file.uploadStatus = ((file.package + 1) >= file.packages ? statuses.done : statuses.inprogress);
				if (file.uploadStatus == statuses.inprogress)
					file.package++;
			}
			return true;
		},
		getFilePart : function(file, MaxFilesize)
		{
			var blob, chunkSize = MaxFilesize, start, end;
			if (BX.type.isDomNode(file))
			{
				blob = file;
			}
			else if (MaxFilesize <= 0  || file.size <= MaxFilesize)
			{
				blob = file;
			}
			else if (file['packages'] && file['packages'] <= file['package'])
			{
				blob = null;
			}
			else if (window.Blob || window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder)
			{
				if (file['packages'])
				{
					file.package++;
					start = file.package * chunkSize;
					end = start + chunkSize;
				}
				else
				{
					file.packages = Math.ceil(file.size / chunkSize);
					file.package = 0;
					start = 0;
					end = chunkSize;
				}

				if('mozSlice' in file)
					blob = file.mozSlice(start, end, file.type);
				else if ('webkitSlice' in file)
					blob = file.webkitSlice(start, end, file.type);
				else if ('slice' in file)
					blob = file.slice(start, end, file.type);
				else
					blob = file.Slice(start, end, file.type);

				for (var ii in file)
				{
					if (file.hasOwnProperty(ii))
					{
						blob[ii] = file[ii];
					}
				}
				blob["name"] = file["name"];
				blob["start"] = start;
				blob["package"] = file.package;
				blob["packages"] = file.packages;
			}
			return blob;
		},
		makeAnArray : function(file, data)
		{
			file = (!!file ? file : {files : [], props : {}});
			var ii;
			for (var jj in data)
			{
				if (data.hasOwnProperty(jj))
				{
					if (typeof data[jj] == "object" && data[jj].length > 0)
					{
						file[jj] = (!!file[jj] ? file[jj] : []);
						for (ii=0; ii<data[jj].length; ii++)
						{
							file[jj].push(data[jj][ii]);
						}
					}
					else
					{

						for (ii in data[jj])
						{
							if (data[jj].hasOwnProperty(ii))
							{
								file[jj] = (!!file[jj] ? file[jj] : {});
								file[jj][ii] = data[jj][ii];
							}
						}
					}
				}
			}
			return file;
		},
		appendToForm : function(fd, key, val)
		{
			if (!!val && typeof val == "object")
			{
				for (var ii in val)
				{
					if (val.hasOwnProperty(ii))
					{
						BX.UploaderUtils.appendToForm(fd, key + '[' + ii + ']', val[ii]);
					}
				}
			}
			else
			{
				fd.append(key, (!!val ? val : ''));
			}
		},
		FormData : function()
		{
			return new (BX.Uploader.getInstanceName() == "BX.UploaderSimple" ? FormDataLocal : window.FormData);
		},
		prepareData : function(arData)
		{
			var data = {};
			if (null != arData)
			{
				if(typeof arData == 'object')
				{
					for(var i in arData)
					{
						if (arData.hasOwnProperty(i))
						{
							var name = BX.util.urlencode(i);
							if(typeof arData[i] == 'object')
								data[name] = BX.UploaderUtils.prepareData(arData[i]);
							else
								data[name] = BX.util.urlencode(arData[i]);
						}
					}
				}
				else
					data = BX.util.urlencode(arData);
			}
			return data;
		}
	};
	var FormDataLocal = function()
	{
		var uniqueID;
		do {
			uniqueID = Math.floor(Math.random() * 99999);
		} while(BX("form-" + uniqueID));
		this.local = true;
		this.form = BX.create("FORM", {
			props: {
				id: "form-" + uniqueID,
				method: "POST",
				enctype: "multipart/form-data",
				encoding: "multipart/form-data"
			},
			style: {display: "none"}
		});
		document.body.appendChild(this.form);
	};
	FormDataLocal.prototype = {
		append : function(name, val)
		{
			if (BX.type.isDomNode(val))
			{
				this.form.appendChild(val);
			}
			else
			{
				this.form.appendChild(
					BX.create("INPUT", {
							props : {
								type : "hidden",
								name : name,
								value : val
							}
						}
					)
				);
			}
		}
	};
	BX.UploaderUtils.slice = function(file, start, end)
	{
		var blob = null;
		if('mozSlice' in file)
			blob = file.mozSlice(start, end);
		else if ('webkitSlice' in file)
			blob = file.webkitSlice(start, end);
		else if ('slice' in file)
			blob = file.slice(start, end);
		else
			blob = file.Slice(start, end, file.type);
		return blob;
	};
	BX.UploaderUtils.readFile = function (file, callback, method)
	{
		if (window["FileReader"])
		{
			var fileReader = new FileReader();
			fileReader.onload = fileReader.onerror = callback;
			method = (method || 'readAsDataURL');
			if (fileReader[method])
			{
				fileReader[method](file);
				return fileReader;
			}
		}
		return false;
	};
}(window));
