;(function(){
	var BX = window.BX;
	if (BX["MFInput"])
		return;
	var repo = {},
		uploader = (function(){
		var d = function(params) {
			try {
				this.params = params;
				this.controller = BX("mfi-" + params.controlId);
				this.button = BX("mfi-" + params.controlId + "-button");
				this.editor = null;
				if(BX("mfi-" + params.controlId + "-editor")) {
					this.editor = new BX.AvatarEditor({enableCamera : params.enableCamera});
					BX.addCustomEvent(this.editor, "onApply", BX.delegate(this.addFile, this));
					BX.bind(BX("mfi-" + params.controlId + "-editor"), "click", BX.delegate(this.editor.click, this.editor));
				}
				this.init(params);
				repo[params.controlId] = this;
				this.template = BX.message('MFI_THUMB2').replace("#input_name#", this.params.inputName);
				window["FILE_INPUT_" + params.controlId] = this; // for compatibility. Do not use
				this.INPUT = BX("file_input_" + params['controlId']); // for compatibility. Do not use
			}
			catch (e) {
				BX.debug(e);
			}
		};
		d.prototype = {
			init : function(params) {
				this.agent = BX.Uploader.getInstance({
					id : params['controlId'],
					CID : params['controlUid'],
					streams : 1,
					uploadFormData : "N",
					uploadMethod : "immediate",
					uploadFileUrl : params["urlUpload"], //'/bitrix/tools/upload.php?lang=' + BX.message("LANGUAGE_ID"),
					allowUpload : params["allowUpload"],
					allowUploadExt : params["allowUploadExt"],
					uploadMaxFilesize : params["uploadMaxFilesize"],
					showImage : false,
					sortItems : false,
					input : BX("file_input_" + params['controlId']),
					dropZone : this.controller.parentNode,
					placeHolder : this.controller,
					fields : {
						thumb : {
							tagName : "",
							template : BX.message("MFI_THUMB")
						}
					}
				});

				this.fileEvents = {
					onFileIsAppended : this.onFileIsAppended.bind(this),
					onFileIsBound : this.onFileIsBound.bind(this),
					onUploadStart : this.onUploadStart.bind(this),
					onUploadProgress : this.onUploadProgress.bind(this),
					onUploadDone : this.onUploadDone.bind(this),
					onUploadError : this.onUploadError.bind(this)
				};

				BX.addCustomEvent(this.agent, "onAttachFiles", BX.delegate(this.onAttachFiles, this));
				BX.addCustomEvent(this.agent, "onQueueIsChanged", BX.delegate(this.onQueueIsChanged, this));
				BX.addCustomEvent(this.agent, "onFileIsInited", BX.delegate(this.onFileIsInited, this));
				BX.addCustomEvent(this.agent, "onPackageIsInitialized", BX.delegate(function(pack) {
					var t = {
						mfi_mode : "upload",
						cid : this.agent.CID,
						moduleId : this.params["moduleId"],
						forceMd5 : this.params["forceMd5"],
						allowUpload : this.agent.limits["allowUpload"],
						allowUploadExt : this.agent.limits["allowUploadExt"],
						uploadMaxFilesize : this.agent.limits["uploadMaxFilesize"],
						mfi_sign : this.params["controlSign"]
					}, i;
					for (i in t)
					{
						if (t.hasOwnProperty(i) && t[i])
						{
							pack.post.data[i] = t[i];
							pack.post.size += ((i + "").length + (t[i] + "").length)
						}
					}
				}, this));

				var ar1 = [], ar2 = [], name, id,
					values = BX.findChildren(this.controller, {tagName : "LI"});
				for (var ii = 0; ii < values.length; ii++)
				{
					name = BX.findChild(values[ii], {attribute : {"data-bx-role" : "file-name"}}, true);
					id = BX.findChild(values[ii], {attribute : {"data-bx-role" : "file-id"}}, true);
					if (name && id)
					{
						ar1.push({ name : name.innerHTML,  file_id : id.value});
						ar2.push(values[ii]);
					}
				}
				this.agent.onAttach(ar1, ar2);
				this.inited = true;
				this.checkUploadControl();
			},
			checkUploadControl : function() {
				if (BX(this.button))
				{
					if (!(this.params["maxCount"] > 0 && this.params["maxCount"] <= this.agent.getItems().length))
					{
						this.button.removeAttribute("disable");
					}
					else if (this.params["maxCount"] == 1)
					{
						// TODO smth
					}
					else //  && this.params["maxCount"] >= this.agent.getItems().length
					{
						this.button.setAttribute("disable", "Y");
					}
				}
			},
			onQueueIsChanged : function() {
				if (this.params['maxCount'] > 0)
				{
					this.checkUploadControl();
				}
			},
			onAttachFiles : function(files) {
				var error = false, n;
				if (files && this.inited === true && this.params['maxCount'] > 0)
				{
					if (this.params['maxCount'] == 1 && files.length > 0)
					{
						while (this.agent.getItems().length > 0)
						{
							this.deleteFile(this.agent.getItems().getFirst(), false);
						}

						while (files.length > 1)
							files.pop();
					}
					var acceptableL = this.params['maxCount'] - this.agent.getItems().length;
					acceptableL = (acceptableL > 0 ? acceptableL : 0);
					while (files.length > acceptableL)
					{
						files.pop();
						error = true;
					}
				}
				if (error)
				{
					this.onError("Too much files.");
				}
				BX.onCustomEvent(this, 'onFileUploaderChange', [files, this]);
				return files;
			},
			onFileIsInited : function(id, file) {
				for (var ii in this['fileEvents'])
				{
					if (this['fileEvents'].hasOwnProperty(ii))
					{
						BX.addCustomEvent(file, ii, this['fileEvents'][ii]);
					}
				}
			},
			onFileIsAppended : function(id, item) {
				// append canvas
				var node = this.agent.getItem(id);
				this.bindEventsHandlers(node.node, item);
			},
			onFileIsBound : function(id, item) {
				var node = this.agent.getItem(id);
				this.bindEventsHandlers(node.node, item);
			},
			bindEventsHandlers : function(node, item) {
				var n = BX.findChild(node, {attribute : {"data-bx-role" : "file-delete"}}, true), n1;
				if (n)
					BX.bind(n, "click", BX.proxy(function(){
						this.deleteFile(item);
				}, this));

				n = BX.findChild(node, {attribute : {"data-bx-role" : "file-preview"}}, true);
				if (n)
				{
					n.removeAttribute("data-bx-role");
					if (item.file.parentCanvas)
					{
						var
							props = BX.UploaderUtils.scaleImage(item.file.parentCanvas, { width : 100, height : 100}, "exact"),
							c = BX.create("CANVAS", {props : {width : 100, height : 100}});
						n.appendChild(c);
						c.getContext("2d").drawImage(item.file.parentCanvas, props.source.x, props.source.y, props.source.width, props.source.height, 0, 0, props.destin.width, props.destin.height);
						item.canvas = c;
					}
				}
				item.file.parentCanvas = null;
				n = BX.findChild(node, {tagName : "A", attribute : {"data-bx-role" : "file-name"}}, true);
				if (n)
				{
					if (this.editor && ((n1 = BX.findChild(node, {tagName : "CANVAS"}, true)) && n1 || (n1 = BX.findChild(node, {tagName : "IMG"}, true)) && n1))
					{
						BX.bind(n, "click", BX.proxy(function(e) {
							BX.PreventDefault(e);
							this.editor.showFile({name : n.innerHTML, tmp_url : n.href});
							return false;}, this));
					}
					else if (n.getAttribute("href") === "#")
					{
						BX.bind(n, "click", BX.proxy(function(e) {
							BX.PreventDefault(e);
							return false;}, this));
					}
				}
			},
			addFile : function(file, canvas) {
				file.name = (file["name"] || 'image.png');
				file.parentCanvas = canvas;
				this.agent.onAttach([file]);
			},
			deleteFile : function(item) {
				var pointer = (item ? this.agent.getItem(item.id) : false);
				if (!pointer)
					return;
				item = pointer.item;

				var node = pointer.node;
				var newInput;
				if (item.file["justUploaded"] === true && item.file["file_id"] > 0)
				{
					var data = {
						fileID : item.file["file_id"],
						sessid : BX.bitrix_sessid(),
						cid : this.agent.CID,
						mfi_mode : "delete"
					};
					BX.ajax.post(this.agent.uploadFileUrl, data);
				}
				else
				{
					var parentNode = node.parentNode.parentNode,
						n = BX.findChild(node, {tagName : "INPUT", attribute : { "data-bx-role" : "file-id"} }, true );
					if (n)
					{
						var name = n.name,
							v = n.value,
							nDelNameCompat = name + '_del',
							nDelName = this.agent.id + '_deleted[]';

						if (name.indexOf('[') > 0)
						{
							nDelNameCompat = name.substr(0, name.indexOf('[')) + '_del' + name.substr(name.indexOf('['));
						}

						newInput = BX.create("INPUT", { props : {
							name : name,
							type : "hidden",
							value : v}});
						parentNode.appendChild(newInput);
						var node1 = BX.create("INPUT", { props : {
							name : nDelNameCompat,
							type : "hidden",
							value : v}});
						parentNode.appendChild(node1);
						node1 = BX.create("INPUT", { props : {
							name : nDelName,
							type : "hidden",
							value : v}});
						parentNode.appendChild(node1);
					}
				}

				for (var ii in this['fileEvents'])
				{
					if (this['fileEvents'].hasOwnProperty(ii))
					{
						BX.removeAllCustomEvents(item, ii);
					}
				}

				BX.unbindAll(node);
				var fileId = (item.file ? item.file["file_id"] : null);
				delete item.hash;
				item.deleteFile('deleteFile');
				if (fileId)
				{
					BX.onCustomEvent(this, 'onDeleteFile', [fileId, item, this]);  // for compatibility
					BX.onCustomEvent(this, 'onFileUploaderChange', [[fileId], this]);  // for compatibility. Do not use
					if(!!newInput)
					{
						BX.fireEvent(newInput, 'change');
					}
				}
			},
			_deleteFile : function() { // for compatibility. Do not Use

			},
			clear : function() {
				while (this.agent.getItems().length > 0)
				{
					this.deleteFile(this.agent.getItems().getFirst(), false);
				}
			},
			onUploadStart : function(item) {
				var node = this.agent.getItem(item.id).node;
				if (node)
					BX.addClass(node, "uploading");
			},
			onUploadProgress : function(item, progress) {
				progress = Math.min(progress, 98);
				var id = item.id;
				if (!item.__progressBarWidth)
					item.__progressBarWidth = 5;
				if (progress > item.__progressBarWidth)
				{
					item.__progressBarWidth = Math.ceil(progress);
					item.__progressBarWidth = (item.__progressBarWidth > 100 ? 100 : item.__progressBarWidth);
					if (BX('wdu' + id + 'Progressbar'))
						BX.adjust(BX('wdu' + id + 'Progressbar'), { style : { width : item.__progressBarWidth + '%' } } );
					if (BX('wdu' + id + 'ProgressbarText'))
						BX.adjust(BX('wdu' + id + 'ProgressbarText'), { text : item.__progressBarWidth + '%' } );
				}
			},
			onUploadDone : function(item, result) {
				var node = this.agent.getItem(item.id).node,
					file = result["file"];
				if (BX(node))
				{
					BX.removeClass(node, "uploading");
					BX.addClass(node, "saved");

					var html = this.template, value;
					file["ext"] = item.ext;
					file["preview_url"] = (item.canvas ? item.canvas.toDataURL("image/png") : "/bitrix/images/1.gif");
					item.canvas = null;
					delete item.canvas;
					for (var ii in file)
					{
						if (file.hasOwnProperty(ii))
						{
							value = file[ii];
							if (ii.toLowerCase() === 'size')
								value = BX.UploaderUtils.getFormattedSize(value, 0);
							else if (ii.toLowerCase() === 'name')
								value = file["originalName"];
							html = html.replace(new RegExp("#" + ii.toLowerCase() + "#", "gi"), BX.util.htmlspecialchars(value)).replace(new RegExp("#" + ii.toUpperCase() + "#", "gi"), BX.util.htmlspecialchars(value));
						}
					}
					item.file.file_id = file["file_id"];
					item.file.justUploaded = true;
					item.name = file["originalName"];
					node.innerHTML = html;
					this.bindEventsHandlers(node, item);
					if (this.params.inputName.indexOf('[') < 0)
					{
						BX.remove(BX.findChild(node.parentNode.parentNode, {tagName : "INPUT", attr : {name : (this.params.inputName)}}, false));
						BX.remove(BX.findChild(node.parentNode.parentNode, {tagName : "INPUT", attr : {name : (this.params.inputName + '_del')}}, false));
					}
					BX.onCustomEvent(this, 'onAddFile', [file["file_id"], this, file, node]); // for compatibility
					BX.onCustomEvent(this, 'onUploadDone', [result["file"], item, this]);
					BX.fireEvent(BX('file-' + file["file_id"]), 'change');
				}
				else
				{
					this.onUploadError(item, result, this.agent);
				}
			},
			onUploadError : function(item, params, specify) {
				// TODO show Error
				var node = this.agent.getItem(item.id).node,
					error = BX.message("MFI_UPLOADING_ERROR");
				if (params && params.error)
					error = params.error;
				BX.removeClass(node, "uploading");
				BX.addClass(node, "error");
				node.appendChild(BX.create('DIV', {attrs : { className : "upload-file-error" }, html : error}));
				BX.onCustomEvent(this, 'onErrorFile', [item["file"], this]); // for compatibility
			},
			onError : function(stream, pIndex, data) {
				var defaultErrorText = 'Uploading error.',
					errorText = defaultErrorText, item, id;
				if (data)
				{
					if (data["error"] && typeof data["error"] == "string")
						errorText = data["error"];
					else if (data["message"] && typeof data["message"] == "string")
						errorText = data["message"];
					else if (BX.type.isArray(data["errors"]) && data["errors"].length > 0)
					{
						errorText = [];
						for (var ii = 0; ii < data["errors"].length; ii++)
						{
							if (typeof data["errors"][ii] == "object" && data["errors"][ii]["message"])
								errorText.push(data["errors"][ii]["message"]);
						}
						if (errorText.length <= 0)
							errorText.push('Uploading error.');
						errorText = errorText.join(' ');
					}
				}
				stream.files = (stream.files || {});
				for (id in stream.files)
				{
					if (stream.files.hasOwnProperty(id))
					{
						item = this.agent.queue.items.getItem(id);
						this.onUploadError(item, {error : errorText}, (errorText != defaultErrorText));
					}
				}
			}
		};
		return d;
	})();

	BX.MFInput = {
		init : function(params) {
			return new uploader(params);
		},
		get : function(controlId) {
			return (repo[controlId] || null);
		}
	}
})();
