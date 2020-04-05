;(function(window){

	if (window.BX["Uploader"])
		return;
	var
		BX = window.BX,
		statuses = { "new" : 0, ready : 1, preparing : 2, inprogress : 3, done : 4, failed : 5, error : 5.2, stopped : 6, changed : 7, uploaded : 8},
		repo = {},
		settings = {
			phpPostMinSize : 5.5 * 1024 * 1024, // Bytes
			phpUploadMaxFilesize : 5 * 1024 * 1024, // Bytes 5MB because of Cloud
			phpPostMaxSize : 11 * 1024 * 1024, // Bytes
			estimatedTimeForUploadFile : 10 * 60, // in sec
			maxTimeForUploadFile : 15 * 60, // in sec
			maxSize : null
		};

	BX.UploaderManager = function() {

	};
	BX.UploaderManager.getById = function(id)
	{
		return (
			typeof repo[id] != 'undefined'
				? repo[id]
				: false
		)
	};
	/**
	 * @params array
	 * @params[input] - BX(id).
	 *  DOM-node with id="uploader_somehash" should exist and will be replaced	 *
	 * @params[dropZone] - DOM node to drag&drop
	 * @params[placeHolder] - DOM node to append files
	 *
	 */
	BX.Uploader = function(params)
	{
		if (settings.maxSize === null && BX.message["bxDiskQuota"] && BX.message("bxDiskQuota"))
			settings.maxSize = parseInt(BX.message("bxDiskQuota"));
		var ii;
		if (!(typeof params == "object" && params && (BX(params["input"]) || params["input"] === null)))
		{
			BX.debug(BX.message("UPLOADER_INPUT_IS_NOT_DEFINED"));
		}
		else
		{
			if (parseInt(BX.message("phpMaxFileUploads")) <= 0)
				ii = {phpMaxFileUploads : '20'};
			if (parseInt(BX.message('phpPostMaxSize')) <= 0)
			{
				ii = (ii || {});
				ii["phpPostMaxSize"] = settings.phpPostMaxSize + '';
			}
			if (parseInt(BX.message('phpUploadMaxFilesize')) <= 0)
			{
				ii = (ii || {});
				ii["phpUploadMaxFilesize"] = settings.phpUploadMaxFilesize + '';
			}
			if (ii)
				BX.message(ii);

			this.fileInput = (params["input"] === null ? null : BX(params["input"]));
			this.controlID = this.controlId = (params["controlId"] || "bitrixUploader");

			this.dialogName = "BX.Uploader";
			this.id = (BX.type.isNotEmptyString(params["id"]) ? params["id"] : Math.random());
			this.CID = (params["CID"] && BX.type.isNotEmptyString(params["CID"]) ? params["CID"] : ("CID" + BX.UploaderUtils.getId()));
			this.streams = new BX.UploaderStreams(params['streams'], this);

			// Limits
			this.limits = {
				phpMaxFileUploads : parseInt(BX.message('phpMaxFileUploads')),
				phpPostMaxSize : Math.min(parseInt(BX.message('phpPostMaxSize')), settings.phpPostMaxSize),
				phpUploadMaxFilesize : Math.min(parseInt(BX.message('phpUploadMaxFilesize')), settings.phpUploadMaxFilesize),
				uploadMaxFilesize : (params["uploadMaxFilesize"] && params["uploadMaxFilesize"] > 0 ? params["uploadMaxFilesize"] : 0),
				uploadFileWidth : (params["uploadFileWidth"] && params["uploadFileWidth"] > 0 ? params["uploadFileWidth"] : 0),
				uploadFileHeight : (params["uploadFileHeight"] && params["uploadFileHeight"] > 0 ? params["uploadFileHeight"] : 0),
				allowUpload : ((params["allowUpload"] == "A" || params["allowUpload"] == "I" || params["allowUpload"] == "F") ? params["allowUpload"] : "A"),
				allowUploadExt : (typeof params["allowUploadExt"] === "string" ? params["allowUploadExt"] : "")};
			var keys = ["phpMaxFileUploads", "phpPostMaxSize", "phpUploadMaxFilesize"];
			for (ii = 0; ii < keys.length; ii++)
			{
				this.limits[keys[ii]] = (typeof params[keys[ii]] == "number" && params[keys[ii]] < this.limits[keys[ii]] ? params[keys[ii]] : this.limits[keys[ii]]);
			}
			this.limits["phpPostSize"] = Math.min(this.limits["phpPostMaxSize"], settings.phpPostMinSize);

	// ALLOW_UPLOAD = 'A'll files | 'I'mages | 'F'iles with selected extensions
	// ALLOW_UPLOAD_EXT = comma-separated list of allowed file extensions (ALLOW_UPLOAD='F')
			this.limits["uploadFile"] = (params["allowUpload"] == "I" ? "image/*" : "");
			this.limits["uploadFileExt"] = this.limits["allowUploadExt"];

			if (this.limits["uploadFileExt"].length > 0)
			{
				var ext = this.limits["uploadFileExt"].split(this.limits["uploadFileExt"].indexOf(",") >= 0 ? "," : " ");
				for (ii = 0; ii < ext.length; ii++)
					ext[ii] = (ext[ii].charAt(0) == "." ? ext[ii].substr(1) : ext[ii]);
				this.limits["uploadFileExt"] = ext.join(",");
			}
			this.params = params;

			this.params["filesInputName"] = (this.fileInput && this.fileInput["name"] ? this.fileInput["name"] : "FILES");
			this.params["filesInputMultiple"] = (this.fileInput && this.fileInput["multiple"] || this.params["filesInputMultiple"] ? "multiple" : false);
			this.params["uploadFormData"] = (this.params["uploadFormData"] == "N" ? "N" : "Y");
			this.params["uploadMethod"] = (this.params["uploadMethod"] == "immediate" ? "immediate" : "deferred"); // Should we start upload immediately or by event
			this.params["uploadFilesForPackage"] = parseInt(this.params["uploadFilesForPackage"] > 0 ? this.params["uploadFilesForPackage"] : 0);
			this.params["imageExt"] = "jpg,bmp,jpeg,jpe,gif,png";
			this.params["uploadInputName"] = (!!this.params["uploadInputName"] ? this.params["uploadInputName"] : "bxu_files");
			this.params["uploadInputInfoName"] = (!!this.params["uploadInputInfoName"] ? this.params["uploadInputInfoName"] : "bxu_info");
			this.params["deleteFileOnServer"] = !(this.params["deleteFileOnServer"] === false || this.params["deleteFileOnServer"] === "N");
			this.params["pasteFileHashInForm"] = !(this.params["pasteFileHashInForm"] === false || this.params["pasteFileHashInForm"] === "N");

			repo[this.id] = this;
			if (this.init(this.fileInput)) // init fileInput
			{
				if (!!params["dropZone"])
					this.initDropZone(BX(params["dropZone"]));

				if (!!params["events"])
				{
					for(ii in params["events"])
					{
						if (params["events"].hasOwnProperty(ii))
						{
							BX.UploaderUtils.bindEvents(this, ii, params["events"][ii]);
						}
					}
				}
				this.uploadFileUrl = (!!params["uploadFileUrl"] ? params["uploadFileUrl"] : (this.form ? this.form.getAttribute("action") : ""));
				if (!this.uploadFileUrl || this.uploadFileUrl.length <= 0)
				{
					BX.debug(BX.message("UPLOADER_ACTION_URL_NOT_DEFINED"));
				}
				this.status = statuses.ready;


				/* This params only for files. They are here for easy way to change them */
				this.fileFields = params["fields"];
				this.fileCopies = params["copies"];
				var queueFields = (!!params["queueFields"] ? params["queueFields"] : {});
				queueFields["placeHolder"] = BX(queueFields["placeHolder"] || params["placeHolder"]);
				queueFields["showImage"] = (queueFields["showImage"] || params["showImage"]);
				queueFields["sortItems"] = (queueFields["sortItems"] || params["sortItems"]);
				queueFields["thumb"] = (queueFields["thumb"] || params["thumb"]);
				this.queue = new BX.UploaderQueue(queueFields, this.limits, this);

				this.params["doWeHaveStorage"] = true;
				BX.addCustomEvent(this, 'onDone', BX.delegate(function(){
					this.init(this.fileInput);
				}, this));
				if (!!this.params["filesInputName"] && this.params["pasteFileHashInForm"])
				{
					BX.addCustomEvent(this, 'onFileIsUploaded', BX.delegate(function(id, item){
						var node = BX.create("INPUT", {props : { type : "hidden", name : this.params["filesInputName"] + '[]', value : item.hash }});
						if (BX(params["placeHolder"]) && BX(id + 'Item'))
							BX(id + 'Item').appendChild(node);
						else if (this.fileInput !== null)
							this.fileInput.parentNode.insertBefore(node, this.fileInput);
					}, this));
				}
				if (this.params["deleteFileOnServer"])
				{
					BX.addCustomEvent(this, 'onFileIsDeleted', BX.delegate(function(id, file){
						if (!!file && !!file.hash)
						{
							var data = this.preparePost({mode : "delete", hash : file.hash}, false);
							BX.ajax.get(
								this.uploadFileUrl,
								data.data
							);
						}
					}, this));
				}
				BX.onCustomEvent(window, "onUploaderIsInited", [this.id, this]);
				this.uploads = new BX.UploaderUtils.Hash();
				this.upload = null;
				if (this.params["bindBeforeUnload"] === false)
				{
					this.__beforeunload = BX.delegate(this.terminate, this);
				}
				else
				{
					this.__beforeunload = BX.delegate(function(e) {
						if (this.uploads && this.uploads.length > 0)
						{
							var confirmationMessage = BX.message("UPLOADER_UPLOADING_ONBEFOREUNLOAD");
							(e || window.event).returnValue = confirmationMessage;
							return confirmationMessage;
						}
					}, this);
				}
				BX.bind(window, 'beforeunload', this.__beforeunload);
			}
		}
	};

	BX.Uploader.prototype = {
		init : function(fileInput)
		{
			this.log('input is initialized');
			if (BX(fileInput))
			{

				if (fileInput == this.fileInput && !this.form)
					this.form = this.fileInput.form;

				if (fileInput == this.fileInput)
					fileInput = this.fileInput = this.mkFileInput(fileInput);
				else
					fileInput = this.mkFileInput(fileInput);

				BX.onCustomEvent(this, "onFileinputIsReinited", [fileInput, this]);

				if (fileInput)
				{
					BX.bind(fileInput, "change", BX.delegate(this.onChange, this));
					return true;
				}
			}
			else if (fileInput === null && this.fileInput === null)
			{
				this.log('Initialized && null');
				return true;
			}
			return false;
		},
		destruct : function () {
			this.releaseDropZone();
		},
		log : function(text)
		{
			BX.UploaderUtils.log('uploader', text);
		},
		initDropZone : function(node)
		{
			var dropZone = null;
			if (!!BX.DD && BX.type.isDomNode(node) && node.parentNode)
			{
				dropZone = new BX.DD.dropFiles(node);
				if (dropZone && dropZone.supported() && BX.ajax.FormData.isSupported()) {
					dropZone.f = {
						dropFiles : BX.delegate(function(files, e) {
							if (e && e["dataTransfer"] && e["dataTransfer"]["items"] && e["dataTransfer"]["items"].length > 0)
							{
								var dt = e["dataTransfer"], ii, entry, fileCopy = [], replace = false;
								for (ii = 0; ii < dt["items"].length; ii++)
								{
									if (dt["items"][ii]["webkitGetAsEntry"] && dt["items"][ii]["getAsFile"])
									{
										replace = true;
										entry = dt["items"][ii]["webkitGetAsEntry"]();
										if (entry && entry.isFile)
										{
											fileCopy.push(dt["items"][ii]["getAsFile"]());
										}
									}
								}
								if (replace)
									files = fileCopy;
							}
							this.onChange(files);
						}, this),
						dragEnter : function(e) {
							var isFileTransfer = false;
							if (e && e["dataTransfer"] && e["dataTransfer"]["types"])
							{
								for (var i = 0; i < e["dataTransfer"]["types"].length; i++)
								{
									if (e["dataTransfer"]["types"][i] === "Files")
									{
										isFileTransfer = true;
										break;
									}
								}
							}
							if (isFileTransfer)
								BX.addClass(dropZone.DIV, "bxu-file-input-over");
						},
						dragLeave : function() { BX.removeClass(dropZone.DIV, "bxu-file-input-over"); }
					};
					BX.addCustomEvent(dropZone, 'dropFiles', dropZone.f.dropFiles);
					BX.addCustomEvent(dropZone, 'dragEnter', dropZone.f.dragEnter);
					BX.addCustomEvent(dropZone, 'dragLeave' , dropZone.f.dragLeave);
				}
				if (this.params["dropZone"] == node)
				{
					this.dropZone = dropZone;
				}
			}
			return dropZone;
		},
		releaseDropZone : function() {
			if (this.dropZone)
			{
				BX.unbindAll(this.dropZone.DIV);
				this.dropZone.DIV.removeAttribute('dropzone');

				BX.removeCustomEvent(this.dropZone, 'dropFiles', this.dropZone.f.dropFiles);
				BX.removeCustomEvent(this.dropZone, 'dragEnter', this.dropZone.f.dragEnter);
				BX.removeCustomEvent(this.dropZone, 'dragLeave' , this.dropZone.f.dragLeave);
				delete this.dropZone.f.dropFiles;
				delete this.dropZone.f.dragEnter;
				delete this.dropZone.f.dragLeave;
				delete this.dropZone._cancelLeave;
				delete this.dropZone._prepareLeave;

				delete this.dropZone;
			}
		},
		onAttach : function(files, nodes, check)
		{
			check = (check !== false);
			if (typeof files !== "undefined" && files.length > 0)
			{
				if (!this.params["doWeHaveStorage"])
					this.queue.clear();

				if (!BX.type.isArray(files)) // FileList
				{
					var result = [];
					for (var j=0; j < files.length; j++)
					{
						result.push(files[j]);
					}
					files = result;
				}

				BX.onCustomEvent(this, "onAttachFiles", [files, nodes, this]);

				var added = false, ext, type;

				nodes = (typeof nodes == "object" && !!nodes && nodes.length > 0 ? nodes : []);

				for (var i=0, f; i < files.length; i++)
				{
					f = files[i];
					if (BX(f) && f.value)
					{
						ext = (f.value.name || '').split('.').pop();
					}
					else
					{
						ext = (f['name'] || f['tmp_url'] || '').split('.').pop();
						if (ext.indexOf('?') > 0)
							ext = ext.substr(0, ext.indexOf('?'));
					}

					ext = (BX.type.isNotEmptyString(ext) ? ext : '').toLowerCase();
					type = (BX.type.isNotEmptyString(f['type']) ? f['type'] : '').toLowerCase();

					if (
						check &&
						(
							(
								this.limits["uploadFile"] == "image/*" &&
								(
									(BX.type.isNotEmptyString(type) && type.indexOf("image/") !== 0) ||
									(!BX.type.isNotEmptyString(type) && this.params["imageExt"].indexOf(ext) < 0)
								)
							) ||
							(
								this.limits["uploadFileExt"].length > 0 && this.limits["uploadFileExt"].indexOf(ext) < 0
							)
						)
					)
					{
						continue;
					}
					BX.onCustomEvent(this, "onItemIsAdded", [f, (nodes[i] || null), this]);
					added = true;
				}
				if (added)
				{
					BX.onCustomEvent(this, "onItemsAreAdded", [this]);
					if (this.params["uploadMethod"] == "immediate")
						this.submit();
				}
			}
			return false;
		},
		onChange : function(fileInput)
		{
			BX.onCustomEvent(this, "onFileinputWillBeChanged", [fileInput, this]);
			BX.PreventDefault(fileInput);

			var files = fileInput;
			if (fileInput && fileInput.target)
				files = fileInput.target.files;
			else if (!fileInput && BX(this.fileInput))
				files = this.fileInput.files;

			if (BX(this.fileInput) && this.fileInput.disabled)
			{
				BX.DoNothing();
			}
			else
			{
				BX.onCustomEvent(this, "onFileinputIsChanged", [fileInput, this]);
				this.init((fileInput && fileInput["target"] ? fileInput.target : fileInput));
				this.onAttach(files);
			}

			return false;
		},
		mkFileInput : function(oldNode)
		{
			if (!BX(oldNode))
				return false;
			BX.unbindAll(oldNode);
			var node = oldNode.cloneNode(true);
			BX.adjust(node, {
				props : {
					value : ""
				},
				attrs: {
					name: (this.params["uploadInputName"] + '[]'),
					multiple : this.params["filesInputMultiple"],
					accept : this.limits["uploadFile"],
					value : ""
			}});
			oldNode.parentNode.insertBefore(node, oldNode);
			oldNode.parentNode.removeChild(oldNode);
			return node;
		},
		preparePost : function(data, prepareForm)
		{
			if (prepareForm === true && this.params["uploadFormData"] == "Y" && !this.post)
			{
				var post2 = {data : {"AJAX_POST" : "Y", SITE_ID : BX.message("SITE_ID"), USER_ID : BX.message("USER_ID")}, filesCount : 0, size : 10};
				post2 = (this.form ? BX.UploaderUtils.FormToArray(this.form, post2) : post2);
				if (!!post2.data[this.params["filesInputName"]])
				{
					post2.data[this.params["filesInputName"]] = null;
					delete post2.data[this.params["filesInputName"]];
				}
				if (!!post2.data[this.params["uploadInputInfoName"]])
				{
					post2.data[this.params["uploadInputInfoName"]] = null;
					delete post2.data[this.params["uploadInputInfoName"]];
				}
				if (!!post2.data[this.params["uploadInputName"]])
				{
					post2.filesCount -= post2.data[this.params["uploadInputName"]].length;
					post2.data[this.params["uploadInputName"]] = null;
					delete post2.data[this.params["uploadInputName"]];
				}
				if (this.limits["phpMaxFileUploads"] <= post2.filesCount)
				{
					BX.debug('You can not upload any file from your list.');
					return false;
				}
				post2.size = BX.UploaderUtils.sizeof(post2.data);
				this.post = post2;
			}
			var post = (prepareForm === true && this.params["uploadFormData"] == "Y" ? this.post : {data : {"AJAX_POST" : "Y", SITE_ID : BX.message("SITE_ID"), USER_ID : BX.message("USER_ID")}, filesCount : 0, size : 10}), size = 0;
			post.data["sessid"] = BX.bitrix_sessid();
			post.size += (6 + BX.bitrix_sessid().length);
			if (data)
			{
				post.data[this.params["uploadInputInfoName"]] = {
					controlId : this.controlId,
					CID : this.CID,
					inputName : this.params["uploadInputName"],
					version : BX.Uploader.getVersion()
				};
				for (var ii in data)
				{
					if (data.hasOwnProperty(ii))
					{
						post.data[this.params["uploadInputInfoName"]][ii] = data[ii];
					}
				}
				size = BX.UploaderUtils.sizeof(this.params["uploadInputInfoName"]) + BX.UploaderUtils.sizeof(post.data[this.params["uploadInputInfoName"]]);
			}
			post.length = post.size + size;
			return post;
		},
		submit : function()
		{
			this.start();
		},
		stop : function()
		{
			this.terminate();
		},
		adjustProcess : function(streamId, item, status, params, pIndex)
		{
			var text = '', percent = 0;
			if (this.queue.itFailed.hasItem(item.id))
			{
				text = 'response [we do not work with errors]';
			}
			else if (status == statuses.error)
			{
				delete item.progress;
				this.queue.itFailed.setItem(item.id, item);
				this.queue.itForUpload.removeItem(item.id);

				BX.onCustomEvent(this, "onFileIsUploadedWithError", [item.id, item, params, this, pIndex]);
				BX.onCustomEvent(item, "onUploadError", [item, params, this, pIndex]);
				text = 'response [error]';
			}
			else if (status == statuses.uploaded)
			{
				delete item.progress;
				this.queue.itUploaded.setItem(item.id, item);
				this.queue.itForUpload.removeItem(item.id);

				BX.onCustomEvent(this, "onFileIsUploaded", [item.id, item, params, this, pIndex]);
				BX.onCustomEvent(item, "onUploadDone", [item, params, this, pIndex]);
				text = 'response [uploaded]';
			}
			else if (status == statuses.inprogress)
			{
				if (typeof params == "number")
				{
					if (params == 0 && item.progress.status == statuses["new"])
					{
						BX.onCustomEvent(item, "onUploadStart", [item, 0, this, pIndex]);
						item.progress.status = statuses.inprogress;
					}

					percent = item.progress.uploaded + (item.progress.streams[streamId] * params) / 100;
				}
				else
				{
					item.progress.uploaded += item.progress.streams[streamId];
					item.progress.streams[streamId] = 0;
					percent = item.progress.uploaded;
				}
				text = 'response [uploading]. Uploaded: ' + percent;
				BX.onCustomEvent(item, "onUploadProgress", [item, percent, this, pIndex]);
			}
			else if (status == statuses.failed)
			{
				if (item.progress.streams[streamId] == item.progress.percentPerChunk)
				{
					item.progress = null;
					delete item.progress;
				}
				else
				{
					item.progress.streams[streamId] -= item.progress.percentPerChunk / params.packages;
					item.progress.streams[streamId] = (item.progress.streams[streamId] > 0 ? item.progress.streams[streamId] : 0);
				}
			}
			else
			{
				if (status == statuses["new"])
				{
					var chunks = (item.getThumbs("getCount") > 0 ? item.getThumbs("getCount") : 0)
						+ 2;// props + (default canvas || file)

					item.progress = {
						percentPerChunk : (100 / chunks),
						streams : {},
						uploaded : 0,
						status : statuses["new"]
					};
					item.progress.streams[streamId] = item.progress.percentPerChunk;
					text = 'request preparing [start]. Prepared: ' + item.progress.streams[streamId];
				}
				else if (status == statuses.preparing)
				{
					item.progress.streams[streamId] = (item.progress.streams[streamId] > 0 ? item.progress.streams[streamId] : 0);
					item.progress.streams[streamId] += item.progress.percentPerChunk / params.packages;
					text += 'request preparing [cont]. Prepared: ' + item.progress.streams[streamId];
				}
				else
				{
					text = 'request preparing [finish]. ';
				}
				BX.onCustomEvent(item, "onUploadPrepared", [item, params, this, pIndex]);
			}
			this.log(item.name + ': ' + text);
		},
		terminate : function(pIndex)
		{
			var packageFormer, packagesFormer;
			if (!pIndex || pIndex == 'beforeunload')
			{
				packagesFormer = this.uploads;

				this.uploads = new BX.UploaderUtils.Hash();
				this.upload = null;

				while ((packageFormer = packagesFormer.getFirst()) && packageFormer)
				{
					packagesFormer.removeItem(packageFormer.id);
					this.terminate(packageFormer);
				}
				return;
			}
			else if (BX.type.isNotEmptyString(pIndex))
			{
				packageFormer = this.uploads.removeItem(pIndex);
			}
			else if (typeof pIndex == "object")
			{
				packageFormer = pIndex;
			}
			if (packageFormer && packageFormer["stop"])
			{
				packageFormer.stop();
				this.log(packageFormer.id + ' Uploading is canceled');
				BX.onCustomEvent(this, 'onTerminated', [packageFormer.id, packageFormer]);
			}
		},
		start : function()
		{
			if (this.queue.itForUpload.length <= 0)
			{
				BX.onCustomEvent(this, 'onStart', [null, {filesCount : 0}, this]);
				BX.onCustomEvent(this, 'onDone', [null, null, {filesCount : 0}]);
				BX.onCustomEvent(this, 'onFinish', [null, null, {filesCount : 0}]);
				return;
			}

			var pIndex = 'pIndex' + BX.UploaderUtils.getId(), queue = this.queue.itForUpload;
			this.queue.itForUpload = new BX.UploaderUtils.Hash();
			this.post = false;
			this.log('create new package ' + pIndex);
			var packageFormer = new BX.UploaderPackage({
				id : pIndex,
				data : queue,
				post : this.preparePost({}, true),
				uploadFileUrl : this.uploadFileUrl,
				limits : this.limits,
				params : this.params
			}, this);
			BX.addCustomEvent(packageFormer, 'adjustProcess', BX.proxy(this.adjustProcess, this));
			BX.addCustomEvent(packageFormer, 'startStream', BX.proxy(function(stream, pack, files){ BX.onCustomEvent(this, 'startPackage', [stream, pack.id, files]); }, this));
			BX.addCustomEvent(packageFormer, 'progressStream', BX.proxy(function(stream, pack, proc){ BX.onCustomEvent(this, 'processPackage', [stream, pack.id, proc]); }, this));
			BX.addCustomEvent(packageFormer, 'doneStream', BX.proxy(function(stream, pack, data){ BX.onCustomEvent(this, 'donePackage', [stream, pack.id, data]); }, this));
			BX.addCustomEvent(packageFormer, 'stopPackage', BX.proxy(function(pack){
				this.log('restore files: '+ pack.data.length);
				this.queue.restoreFiles(pack.data);
			}, this));
			BX.addCustomEvent(packageFormer, 'donePackage', BX.proxy(function(stream, pack, data){
				BX.onCustomEvent(this, 'onDone', [stream, pack.id, pack, data]);
				var res = this.checkUploads(pack.id);
				if (!res)
					BX.onCustomEvent(this, 'onFinish', [stream, pack.id, pack, data]);
			}, this));
			BX.addCustomEvent(packageFormer, 'errorPackage', BX.proxy(function(stream, pack, data){
				BX.onCustomEvent(this, 'error', [stream, pIndex, data]);
				BX.onCustomEvent(this, 'onError', [stream, pIndex, data]);
				this.checkUploads(pack.id);
			}, this));
			BX.addCustomEvent(packageFormer, 'processPackage', BX.proxy(function(stream, pack, procent){
				BX.onCustomEvent(this, 'processPackage', [stream, pack, procent]);
			}, this));

			BX.onCustomEvent(this, 'onStart', [pIndex, packageFormer, this]);
			this.uploads.setItem(pIndex, packageFormer);
			this.checkUploads();
		},
		checkUploads : function(pIndex)
		{
			if (pIndex)
				this.uploads.removeItem(pIndex);
			this.upload = this.uploads.getFirst();
			if (this.upload)
				this.upload.start(this.streams);
			return this.upload;
		},
		// public functions
		getItem : function(id)
		{
			return this.queue.getItem(id);
		},
		getItems : function()
		{
			return this.queue.items;
		},
		restoreItems : function()
		{
			this.queue.restoreFiles.apply(this.queue, arguments);
		},
		clear : function()
		{
			var item;
			while((item = this.queue.items.getFirst()) && item)
			{
				item.deleteFile();
			}
		}
	};

	BX.UploaderSimple = function(params)
	{
		BX.UploaderSimple.superclass.constructor.apply(this, arguments);
		this.dialogName = "BX.UploaderSimple";
		this.previews = new BX.UploaderUtils.Hash();
		if (this.params["uploadMethod"] != "immediate")
		{
			BX.addCustomEvent(this, "onFileNeedsPreview", BX.delegate(function(id, item) {
				this.previews.setItem(item.id, item);
				this.log('onFileNeedsPreview: ' + item.id);
				setTimeout(BX.delegate(this.onFileNeedsPreview, this), 500);
			}, this));
			BX.addCustomEvent(this, "onStart", BX.delegate(function(pIndex, packageFormer) {
				if (packageFormer && packageFormer.filesCount > 0)
				{
					var queue = packageFormer.raw.getIds(), ii;
					for (ii = 0; ii < queue.length; ii++)
					{
						this.previews.removeItem(queue[ii]);
					}
				}
			}, this));
		}
		else
		{
			BX.addCustomEvent(this, "onFileIsUploaded", BX.delegate(function(id, item, data) {
				this.dealWithFile(item, data);
			}, this));
		}
		this.streams = new BX.UploaderStreams(1, this);
		return this;
	};
	BX.extend(BX.UploaderSimple, BX.Uploader);

	BX.UploaderSimple.prototype.preparePost = function()
	{
		var post = BX.UploaderSimple.superclass.preparePost.apply(this, arguments);
		if (post && post.data && post.data[this.params["uploadInputInfoName"]] && !post.data[this.params["uploadInputInfoName"]]["simpleUploader"])
		{
			post.data[this.params["uploadInputInfoName"]]["simpleUploader"] = "Y";
			post.size += 15;
		}
		return post;
	};
	BX.UploaderSimple.prototype.init = function(fileInput, del)
	{
		this.log('input is initialized: ' + (del !== false ? 'drop' : ' does not drop'));
		if (BX(fileInput))
		{
			if (fileInput == this.fileInput && !this.form)
				this.form = this.fileInput.form;

			if (fileInput == this.fileInput)
				fileInput = this.fileInput = this.mkFileInput(fileInput, del);
			else
				fileInput = this.mkFileInput(fileInput, del);

			BX.onCustomEvent(this, "onFileinputIsReinited", [fileInput, this]);

			if (fileInput)
			{
				BX.bind(fileInput, "change", BX.delegate(this.onChange, this));
				return true;
			}
		}
		else if (fileInput === null && this.fileInput === null)
		{
			this.log('Initialized && null');
			return true;
		}
		return false;
	};
	BX.UploaderSimple.prototype.log = function(text)
	{
		BX.UploaderUtils.log('simpleup', text);
	};
	BX.UploaderSimple.prototype.mkFileInput = function(oldNode, del)
	{
		if (!BX(oldNode))
			return false;
		BX.unbindAll(oldNode);
		var node = oldNode.cloneNode(true);
		BX.adjust(node, {
			attrs: {
				id : "",
				name: (this.params["uploadInputName"] + '[file' + BX.UploaderUtils.getId() + '][default]'),
				multiple : false,
				accept : this.limits["uploadFile"]
		}});
		oldNode.parentNode.insertBefore(node, oldNode);
		if (del !== false)
			oldNode.parentNode.removeChild(oldNode);

		return node;
	};
	BX.UploaderSimple.prototype.onChange = function(fileInput)
	{
		BX.PreventDefault(fileInput);

		fileInput = (fileInput.target || fileInput.srcElement || this.fileInput);

		if (BX(this.fileInput) && this.fileInput.disabled)
		{
			BX.DoNothing();
		}
		else
		{
			this.init(fileInput, false);
			this.onAttach([fileInput]);
		}
		return false;
	};
	BX.UploaderSimple.prototype.dealWithFile = function(item, data)
	{
		var file;
		if (data &&
			data["status"] == "uploaded" &&
			data["hash"] &&
			data["file"] &&
			data["file"]["files"] &&
			data["file"]["files"]["default"])
		{
			file = data["file"]["files"]["default"];
		}
		if (file)
		{
			item.file = {
				"name" : file["name"],
				"~name" : file["~name"],
				size : parseInt(file["size"]),
				type : file["type"],
				id : item.id,
				hash : data["hash"],
				copy : "default",
				url : file["url"],
				uploadStatus : statuses.done
			};
			item.nonProcessRun = true;
			BX.onCustomEvent(item, "onFileHasGotPreview", [item.id, item]);
		}
		else
		{
			BX.onCustomEvent(item, "onFileHasNotGotPreview", [item.id, item]);
		}
	};
	BX.UploaderSimple.prototype.onFileNeedsPreviewCallback = function(pack, data)
	{
		if (!(data && data["files"]))
		{
			this.log('onFileNeedsPreviewCallback is failed.');
			return;
		}
		this.log('onFileNeedsPreviewCallback');
		this.onFileNeedsPreview();

		var item;
		while((item = pack.result.getFirst()) && !!item)
		{
			pack.result.removeItem(item.id);
			this.dealWithFile(item, data["files"][item.id]);
		}
	};
	BX.UploaderSimple.prototype.onFileNeedsPreview = function()
	{
		this.log('onFileNeedsPreview');
		var queue = new BX.UploaderUtils.Hash(), item;
		while (queue.length < this.limits["phpMaxFileUploads"] &&
			(item = this.previews.getFirst()) && item && item !== null)
		{
			this.previews.removeItem(item.id);
			queue.setItem(item.id, item);
		}
		if (queue.length > 0)
		{
			this.post = false;
			var pIndex = 'pIndex' + BX.UploaderUtils.getId();
			this.log('create new package for preview ' + pIndex);
			var packageFormer = new BX.UploaderPackage({
				id : pIndex,
				data : queue,
				post : this.preparePost({type : "brief"}, true),
				uploadFileUrl : this.uploadFileUrl,
				limits : this.limits,
				params : this.params
			});
			packageFormer["SimpleUploaderUploadsPreview"] = "Y";
			BX.addCustomEvent(packageFormer, 'adjustProcess', BX.proxy(function(streamId, item, status, params, pIndex) {
				if (status == statuses.error)
				{
					this.adjustProcess(streamId, item, status, params, pIndex);
				}
			}, this));
			BX.addCustomEvent(packageFormer, 'startStream', BX.proxy(function(stream, pack, files){ BX.onCustomEvent(this, 'startPackagePreview', [stream, pack.id, files]); }, this));
			BX.addCustomEvent(packageFormer, 'progressStream', BX.proxy(function(stream, pack, proc){ BX.onCustomEvent(this, 'processPackagePreview', [stream, pack.id, proc]); }, this));
			BX.addCustomEvent(packageFormer, 'doneStream', BX.proxy(function(stream, pack, data){ BX.onCustomEvent(this, 'donePackagePreview', [stream, pack.id, data]); }, this));
			BX.addCustomEvent(packageFormer, 'stopPackage', BX.proxy(function(pack){
//				this.log('restore preview files: ', pack.repo.length);
//				this.queue.restoreFiles(pack.repo);
			}, this));
			BX.addCustomEvent(packageFormer, 'donePackage', BX.proxy(function(stream, pack, data){
				this.checkUploads(pack.id);
				this.onFileNeedsPreviewCallback(pack, data);
			}, this));
			BX.addCustomEvent(packageFormer, 'errorPackage', BX.proxy(function(stream, pack, data){
				BX.onCustomEvent(this, 'error', [stream, pIndex, data]);
				BX.onCustomEvent(this, 'onError', [stream, pIndex, data]);
				this.checkUploads(pack.id);
			}, this));
			BX.addCustomEvent(packageFormer, 'processPackage', BX.proxy(function(stream, pack, procent){
				BX.onCustomEvent(this, 'processPackagePreview', [stream, pack, procent]);
			}, this));

			BX.onCustomEvent(this, 'onStartPreview', [pIndex, packageFormer, this]);
			this.uploads.setItem(pIndex, packageFormer);
			this.checkUploads();
		}
	};
	BX.Uploader.isSupported = function()
	{
		return (window.Blob || window["MozBlobBuilder"] || window["WebKitBlobBuilder"] || window["BlobBuilder"]);
	};
	var objName = "BX.UploaderSimple";
	if (BX.Uploader.isSupported())
		objName = "BX.Uploader";
	BX.Uploader.getInstanceName = function()
	{
		return objName;
	};
	BX.Uploader.getInstance = function(params)
	{
		BX.onCustomEvent(window, "onUploaderIsAlmostInited", [objName, params]);
		return eval("new " + objName + "(params);");
	};
	BX.UploaderPackage = function(params, manager)
	{
		this.filesCount = 0;
		this.length = 0;
		params = (params || {});
		this["pIndex"] = this.id = params["id"];
		this.limits = params["limits"];
		this.params = params["params"];
		this.status = statuses.ready;

		if (params["data"] && params.data.length > 0)
		{
			/**
			 * this.length integer
			 * this.repo BX.UploaderUtils.Hash()
			 * this.raw BX.UploaderUtils.Hash()
			 * this.data BX.UploaderUtils.Hash()
			 */
			this.length = params.data.length;
			this.filesCount = params.data.length;
			this.uploadFileUrl = params["uploadFileUrl"];
			this.raw = params.data;

			this.repo = new BX.UploaderUtils.Hash();
			this.data = new BX.UploaderUtils.Hash();
			this.result = new BX.UploaderUtils.Hash();
			this.init();
			this.post = params["post"];
			if (!this.post)
			{
				var item;
				while ((item = this.raw.getFirst()) && item)
				{
					this.adjustProcess(null, item, statuses.error, {error : BX.message("UPLOADER_UPLOADING_ERROR2")});
					this.raw.removeItem(item.id);
				}
				BX.onCustomEvent(this, 'errorPackage', [null, this, null]);
			}
			else
			{
				var ii, data = { packageIndex : this.id, filesCount : this.filesCount, mode : "upload" };
				for (ii in data)
				{
					if (data.hasOwnProperty(ii))
					{
						this.post.data[this.params["uploadInputInfoName"]][ii] = data[ii];
						this.post.size += BX.UploaderUtils.sizeof(ii) + BX.UploaderUtils.sizeof(data[ii]);
					}
				}
				this.post.startSize = this.post.size;
				BX.onCustomEvent(this, "initializePackage", [this, this.data]);
				if (manager)
					BX.onCustomEvent(manager, "onPackageIsInitialized", [this, this.data]);
				this.log('initialize');
			}
		}
		this._exec = BX.delegate(this.exec, this);
	};

	BX.UploaderPackage.prototype = {
		checkFile : function(item)
		{
			var error = "";
			if (item.file)
			{
				if (this.limits["uploadMaxFilesize"] > 0 && item.file.size > this.limits["uploadMaxFilesize"])
				{
					error = BX.message('FILE_BAD_SIZE') + ' (' + BX.UploaderUtils.getFormattedSize(this.limits["uploadMaxFilesize"], 2) + ')';
				}
				else if (settings.maxSize !== null && item.file.size > settings.maxSize)
				{
					error = BX.message('UPLOADER_UPLOADING_ERROR6');
				}
			}
			return error;
		},
		packStream : function(stream)
		{
			if (stream.filesSize <= 0)
				return null;

			var fd = new BX.UploaderUtils.FormData(), item,
				data = this.post.data,
				files = stream.files,
				res;
			for (item in data)
			{
				if (data.hasOwnProperty(item))
				{
					BX.UploaderUtils.appendToForm(fd, item, data[item]);
				}
			}
			for (var id in files)
			{
				if (files.hasOwnProperty(id))
				{
					data = files[id];

					if (!!data.props)
					{
						res = BX.UploaderUtils.prepareData(data.props);
						for (item in res)
						{
							if (res.hasOwnProperty(item))
							{
								BX.UploaderUtils.appendToForm(fd,  this.params["uploadInputName"] + '[' + id + '][' + item + ']', res[item]);
							}
						}
					}
					if (!!data.files)
					{
						for (var ii = 0; ii < data.files.length; ii++)
						{
							item = data.files[ii];
							item.copy = item.postName = (item.thumb ? item.thumb : 'default');
							if (item.packages > 0)
							{
								item.postName += ('.ch' + item.package + '.' + (item.start > 0 ? item.start : "0") + '.chs' + item.packages);
							}
							fd.append((this.params["uploadInputName"] + '[' + id + '][' + BX.UploaderUtils.prepareData(item.postName) + ']'), item, BX.UploaderUtils.prepareData(item.postName));
						}
					}
				}
			}
			fd.action = this.uploadFileUrl;
			return fd;
		},
		packFiles : function(item, stream)
		{
			if (!item)
				return statuses.error;
			else if (item["uploadStatus"] == statuses.done || item["uploadStatus"] == statuses.error)
				return item["uploadStatus"];
			var count = (this.limits["phpMaxFileUploads"] - this.post.filesCount - (stream.filesCount || 0)),
				size = (this.limits["phpPostMaxSize"] - stream.filesSize - stream.postSize),
				filesSize = (this.limits["phpPostSize"] - stream.filesSize),
				blob, file, data = {files : []}, tmp, error, callback, cf;
			while (size > 0 && count > 0 && filesSize > 0)
			{
				file = null; blob = null; error = ''; callback = [];
				if (item.uploadStatus != statuses.preparing)
				{
					error = this.checkFile(item);
					if (error === '')
					{
						data.props = item.getProps();
						if (item["restored"])
						{
							data.props["restored"] = item["restored"];
							delete item["restored"];
						}
						callback.push(BX.proxy(function() {
							item.uploadStatus = statuses.preparing;
							this.adjustProcess(stream.id, item, statuses["new"], {});
						}, this));
					}
					else
					{
						data.props = {name : item.name, error : true};
						this.adjustProcess(stream.id, item, statuses.error, {error : error});
						item.uploadStatus = statuses.error;
					}
				}
				if (item.uploadStatus == statuses.error)
				{

				}
				else if (item.nonProcessRun === true)
				{
					item.uploadStatus = statuses.done;
				}
				else
				{
					if (!item["file"])
					{
						file = null;
					}
					else if (item.file.uploadStatus != statuses.done)
					{
						file = item.file;
					}
					else if (item["thumb"] && item.thumb !== null)
					{
						file = item.thumb;
					}
					else
					{
						item.thumb = file = item.getThumbs(null);
					}
					var fileConstructor = Object.prototype.toString.call(file);
					if (file === null)
					{
						item.uploadStatus = statuses.done;
						this.adjustProcess(stream.id, item, statuses.done, {});
						item.file.uploadStatus = statuses.done;
						item.thumb = null;
					}
					else if (BX.type.isDomNode(file))
					{
						data.props = (data.props || {name : item.name });
						data.files.push(file);
						callback.push(BX.proxy(function(file) {
							file.uploadStatus = statuses.done;
							if (item.file == file)
							{
								this.adjustProcess(stream.id, item, statuses.preparing, {canvas : "default", package : 1, packages : 1});
							}
							else
							{
								this.adjustProcess(stream.id, item, statuses.preparing, {canvas : item.thumb.thumb, package : 1, packages : 1});
								item.thumb = null;
							}
						}, this))
					}
					else if (!(fileConstructor == '[object File]' || fileConstructor == '[object Blob]'))
					{
						data.props = (data.props || {name : item.name });
						data.props["files"] = (data.props["files"] || {});
						data.props["files"][(file["thumb"] || "default")] = file;
						callback.push(BX.proxy(function(file) {
							file.uploadStatus = statuses.done;
							if (item.file == file)
							{
								this.adjustProcess(stream.id, item, statuses.preparing, {canvas : "default", package : 1, packages : 1});
							}
							else
							{
								this.adjustProcess(stream.id, item, statuses.preparing, {canvas : item.thumb.thumb, package : 1, packages : 1});
								item.thumb = null;
							}
						}, this))
					}
					else
					{
						blob = BX.UploaderUtils.getFilePart(file, (size - BX.UploaderUtils.sizeof({name : item.name})), this.limits["phpUploadMaxFilesize"]);
						if (!blob)
						{
							data.props = "error";
							this.adjustProcess(stream.id, item, statuses.error, {error : BX.message('FILE_BAD_SIZE')});
							item.uploadStatus = statuses.error;
						}
						else
						{
							data.files.push(blob);
							data.props = (data.props || {name : item.name});
							callback.push(BX.proxy(function(file, blob) {
								BX.UploaderUtils.applyFilePart(file, blob);
								if (item.file == file && blob == file)
								{
									this.adjustProcess(stream.id, item, statuses.preparing, {canvas : "default", package : 1, packages : 1});
								}
								else if (item.file == file)
								{
									this.adjustProcess(stream.id, item, statuses.preparing, {canvas : "default", package : (blob.package + 1), packages : blob.packages, blob : blob});
								}
								else if (blob == file)
								{
									this.adjustProcess(stream.id, item, statuses.preparing, {canvas : item.thumb.thumb, package : 1, packages : 1, blob : blob});
									item.thumb = null;
								}
								else
								{
									this.adjustProcess(stream.id, item, statuses.preparing,
										{canvas : item.thumb.thumb, package : (blob.package + 1), packages : blob.packages, blob : blob});
									if (item.thumb.uploadStatus == statuses.done)
										item.thumb = null;
								}
							}, this));
						}
					}
				}
				if (data.files.length > 0 || data["props"])
				{
					tmp = BX.UploaderUtils.sizeof(data.files) + (data["props"] ? BX.UploaderUtils.sizeof(data.props) : 0);
					size -= tmp;
					filesSize -= tmp;
					if (size >= 0 && filesSize > 0)
					{
						while ((cf=callback.shift()) && cf)
							cf(file, blob, error);

						stream.filesSize += tmp;
						stream.files[item.id] = BX.UploaderUtils.makeAnArray(stream.files[item.id], data);

						if (data.files.length) { count--; stream.filesCount++; }
					}
					else if (stream.filesCount <= 0)
					{
						this.adjustProcess(stream.id, item, statuses.error, {error : BX.message('UPLOADER_UPLOADING_ERROR4')});
						item.uploadStatus = statuses.error;
					}
					data = {files : []};
				}
				if (item.uploadStatus !== statuses.preparing)
				{
					break;
				}
			}
			return item.uploadStatus;
		},
		start : function(streams)
		{
			this.streams = streams;
			if (this.status != statuses.ready)
				return;
			this.status = statuses.inprogress;
			this.__onAllStreamsAreKilled = BX.delegate(function(streams, stream){
				this.stop();
				BX.onCustomEvent(this, 'donePackage', [stream, this, this['lastResponse']]);
			}, this);
			BX.addCustomEvent(this.streams, 'onrelease', this.__onAllStreamsAreKilled);
			BX.onCustomEvent(this, 'startPackage', [this, streams]);
			this.log('start');
			streams.init(this, this._exec);
		},
		stop : function()
		{
			this.status = statuses.stopped;
			this.streams.stop();
			BX.onCustomEvent(this, 'stopPackage', [this, this.repo]);
			BX.removeCustomEvent(this.streams, 'onrelease', this.__onAllStreamsAreKilled);
			this.log('stop');
		},
		log : function()
		{
			BX.UploaderUtils.log('package', this.id, arguments);
		},
		init : function()
		{
			var item, callback = BX.proxy(function(id, item) {
				if (this.raw.removeItem(id))
				{
					this.data.setItem(id, item);
					this.repo.setItem(id, item);
					BX.onCustomEvent(item, "onFileHasToBePrepared", [item.id, item]);

					this.init();
				}
			} , this);

			while ((item = this.raw.getFirst()) && item)
			{
				BX.addCustomEvent(item, "onFileIsDeleted", BX.delegate(function(item){
					this.length--;
					this.filesCount--;
					if (this.data.removeItem(item.id))
						this.post.data[this.params["uploadInputInfoName"]]['filesCount'] = this.filesCount;
					this.result.removeItem(item.id);
					this.repo.removeItem(item.id);
				}, this));
				if (item.status === statuses["new"])
				{
					BX.addCustomEvent(item, "onFileIsInited", callback);
					break;
				}
				else
				{
					callback(item.id, item);
				}
			}
		},
		exec : function(stream, reinit)
		{
			if (this.status !== statuses.inprogress)
				return;
			this.log('exec');
			var item, exec = false;
			if (stream.pack != this)
			{
				this.log('stream is bound: ' + stream.id);
				BX.addCustomEvent(stream, 'onsuccess', BX.delegate(this.doneStream, this));
				BX.addCustomEvent(stream, 'onfailure', BX.delegate(this.errorStream, this));
				BX.addCustomEvent(stream, 'onprogress', BX.delegate(this.progressStream, this));
			}
			if (reinit !== false)
			{
				this.log('stream is reinited: ' + stream.id);
				stream.init(this);
			}

			var status, files = stream.filesCount;
			if (this.filesCount > 0)
			{
				while ((item = this.data.getFirst()) && item)
				{
					if (item.uploadStatus == statuses.done)
					{
						// everything is good so we go to another file
					}
					else if (item.preparationStatus != statuses.done) // if file is not initialized
					{
						exec = true;
						break;
					}
					status = this.packFiles(item, stream);
					if (typeof status == "undefined")
					{
						break;
					}
					else if (status != statuses.error)
					{
						files++;
						if (status == statuses.preparing) // if file is not fitted into package
						{
							break;
						}
					}
					this.data.removeItem(item.id);
					if (this["SimpleUploaderUploadsPreview"] == "Y") // in case if it is a simple Uploader uploads preview
					{
						delete item.uploadStatus;
					}
				}
				if (exec === true || (!item && this.raw.length > 0)) // if image is not loaded
				{
					setTimeout(BX.proxy(function(){this.exec(stream, false)}, this), 100);
					return;
				}
			}
			var fd = (files > 0 ? this.packStream(stream) : null);
			if (fd !== null)
			{
				this.log('stream is packed: ' + stream.id);
				this.startStreaming(stream);
				stream.send(fd);
				this.sended = true;
			}
			else
			{
				this.log('stream is killed: ' + stream.id);
				stream.kill();
			}
		},
		adjustProcess : function(streamId, item, status, params)
		{
			if (item && this.repo.hasItem(item.id))
			{
				if (status == statuses.error || status == statuses.uploaded)
				{
					this.data.removeItem(item.id);
					this.result.setItem(item.id, item);
				}
				BX.onCustomEvent(this, 'adjustProcess', [streamId, item, status, params, this.id, this]);
			}
		},
		adjustPostSize : function(stream, increase)
		{
			var result = false, sugestedSize = null;

			var deltaTime = (stream.xhr.finishTime - stream.xhr.startTime);
			if (increase !== false)
			{
				sugestedSize = Math.ceil(deltaTime > 0 ? ((stream.postSize + stream.filesSize) * 1000/ deltaTime ) * settings.estimatedTimeForUploadFile : 0);

				if (sugestedSize > this.limits["phpPostSize"])
				{
					sugestedSize = Math.min(
						this.limits["phpPostSize"] * 2,
						sugestedSize,
						this.limits["phpPostMaxSize"]
					);
				}
			}
			else if (this.limits["phpPostSize"] > settings.phpPostMinSize)
			{
				sugestedSize = Math.ceil(this.limits["phpPostSize"] / 2);
			}
			if (sugestedSize > 0 && sugestedSize !== this.limits["phpPostSize"])
			{
				this.limits["phpPostSize"] = Math.max(sugestedSize, settings.phpPostMinSize);
				result = true;
			}
			return result;
		},
		startStreaming : function(stream)
		{
			this.log('start streaming');
			for (var id in stream.files)
			{
				if (stream.files.hasOwnProperty(id))
				{
					this.adjustProcess(stream.id, this.repo.getItem(id), statuses.inprogress, 0);
				}
			}
			BX.onCustomEvent(this, 'startStream', [stream, this.id, stream.files]);
		},
		doneStream : function(stream, data)
		{
			this.adjustPostSize(stream, true);
			var merge = function(ar1, ar2)
				{
					for (var jj in ar2)
					{
						if (ar2.hasOwnProperty(jj) && !ar1[jj])
						{
							ar1[jj] = ar2[jj]
						}
						else if ((typeof ar2[jj] == typeof ar1[jj] == "object") && ar2[jj] !== null && ar1[jj] !== null)
						{
							ar1[jj] = merge(ar1[jj], ar2[jj]);
						}
					}
					return ar1;
				};
			this.response = merge((this.response || {}), (data || {}));
			var item, id, file, nonProcessRun, files, ij, copies;
			for (id in stream.files)
			{
				if (stream.files.hasOwnProperty(id))
				{
					item = this.repo.getItem(id);
					if (item && (file = data.files[id]))
					{
						if (!file) // has never been loaded
						{
							this.queue.restoreFiles(new BX.UploaderUtils.Hash([item]));
						}
						else if (!file["status"]) // was downloaded partly before but not this time
						{
							if (BX.type.isArray(stream.files[id]["files"]))
							{
								copies = {};
								for (ij = 0; ij < stream.files[id]["files"].length; ij++)
								{
									file = stream.files[id]["files"][ij];
									if (copies[file["copy"]])
										continue;
									copies[file["copy"]] = "Y";
									if (file["copy"] == "default" && file["package"] <= 0)
									{
										this.queue.restoreFiles(new BX.UploaderUtils.Hash([item]));
										break;
									}

									if (file["copy"] == "default")
									{
										item.uploadStatus = statuses.preparing;
										item.file["uploadStatus"] = statuses.preparing;
										item.file["package"] = file["package"];
									}

									if (item.file["copies"])
									{
										item.file["copies"].reset();
										var copy;
										while((copy = item.file["copies"].getNext()) && copy)
										{
											delete copy["uploadStatus"];
											delete copy["firstChunk"];
											delete copy["package"];
											delete copy["packages"];
										}
										item.file["copies"].reset();
									}
								}
							}
						}
						else if (file.status == "error")
						{
							this.adjustProcess(stream.id, item, statuses.error, file);
						}
						else if ((item.hash = file.hash) && file.status == "uploaded")
						{
							if (settings.maxSize !== null)
								settings.maxSize -= item.file.size;
							this.adjustProcess(stream.id, item, statuses.uploaded, file);
						}
						else // chunks
						{
							this.adjustProcess(stream.id, item, statuses.inprogress, file);
							// in case we need to glue chunks only(!)

							nonProcessRun = false;
							files = (file["file"] && file["file"]["files"] ? file["file"]["files"] : false);
							if (typeof files == "object")
							{
								for (ij in files)
								{
									if (files.hasOwnProperty(ij))
									{
										if (files[ij]["chunksInfo"] &&
											files[ij]["chunksInfo"]["count"] == files[ij]["chunksInfo"]["uploaded"] &&
											files[ij]["chunksInfo"]["count"] > files[ij]["chunksInfo"]["written"])
										{
											nonProcessRun = true;
											break;
										}
									}
								}
								item.nonProcessRun = nonProcessRun;
								if (nonProcessRun == true)
								{
									if (!item["nonProcessRunLastTimeWritten"] ||
										item["nonProcessRunLastTimeWritten"] != files[ij]["chunksInfo"]["written"])
									{
										item["nonProcessRunLastTimeWritten"] = files[ij]["chunksInfo"]["written"];
										item["nonProcessRunLastTimeWrittenCount"] = 0;
									}
									else
									{
										item["nonProcessRunLastTimeWrittenCount"]++
									}

									if (item["nonProcessRunLastTimeWrittenCount"] <= 10)
									{
										delete item.uploadStatus;
										this.data.setItem(item.id, item);
									}
									else
									{
										this.adjustProcess(stream.id, item, statuses.error, {error : BX.message("UPLOADER_UPLOADING_ERROR3")});
									}
								}
							}
						}
					}
				}
			}
			this.log('stream is done: ' + stream.id, data["status"], this.response);
			this['lastResponse'] = data;
			if (data["status"] == "inprogress")
			{
				BX.onCustomEvent(this, 'continuePackage', [stream, this, data]);
			}
			else
			{
				if (data["status"] == "error")
					this.errorStream(stream, data);
				else
				{
					this.stop();
					BX.onCustomEvent(this, 'donePackage', [stream, this, data]);
				}
			}
		},
		errorStream : function(stream, data)
		{
			var item, err, id, copy;
			this.log('error stream: ' + stream.id, 'status: ', stream.xhr.status, data);
			if (stream && data == "timeout" && this.adjustPostSize(stream, false) && stream["files"])
			{
				for (id in stream["files"])
				{
					if (stream["files"].hasOwnProperty(id))
					{
						if (this.repo.hasItem(id) &&
							BX.type.isArray(stream["files"][id]["files"]) &&
							stream["files"][id]["files"].length > 0)
						{
							item = this.repo.getItem(id);
							if (stream["files"][id]["files"][0]["package"] <= 0 ||
								item["uploadStatus"] !== statuses.inprogress)
							{
								delete item["uploadStatus"];
								delete item.file["uploadStatus"];
								delete item.file["firstChunk"];
								delete item.file["package"];
								delete item.file["packages"];
							}
							else
							{
								item.file["package"] = Math.min(
									stream["files"][id]["files"][0]["package"],
									item.file["package"]);
							}
							if (item.file["copies"])
							{
								item.file["copies"].reset();
								while((copy = item.file["copies"].getNext()) && copy)
								{
									delete copy["uploadStatus"];
									delete copy["firstChunk"];
									delete copy["package"];
									delete copy["packages"];
								}
								item.file["copies"].reset();
							}

							if (!this.data.hasItem(id))
							{
								this.result.removeItem(id);
								this.data.unshiftItem(id, item);
							}
						}
					}
				}
				BX.onCustomEvent(this, 'resendPackage', [stream, this, data]);
			}
			else
			{
				this.stop();
				var defaultTextError = (data == "timeout" ? BX.message("UPLOADER_UPLOADING_ERROR5") : BX.message("UPLOADER_UPLOADING_ERROR1"));
				data = (data || {});
				data["files"] = (data["files"] ? data["files"] : {});
				if ((item = this.repo.getFirst()) && item)
				{
					do
					{
						if (!this.result.hasItem(item.id))
						{
							if (data.files && data.files[item.id])
								err = data.files[item.id];
							else if (BX.type.isNotEmptyString(data["error"]))
								err = data;
							else if (BX.type.isArray(data["errors"]))
							{
								err = {error : ""};
								for (var ii = 0; ii < data["errors"].length; ii++)
								{
									err.error += (BX.type.isPlainObject(data["errors"][ii]) && data["errors"][ii]["message"] ? data["errors"][ii]["message"] : data["errors"][ii]);
								}
							}
							else
								err = {error : defaultTextError};
							this.adjustProcess(stream.id, item, statuses.error, err);
						}
					} while ((item = this.repo.getNext()) && item);
				}
				BX.onCustomEvent(this, 'errorPackage', [stream, this, data]);
			}
		},
		progressStream : function(stream, procent)
		{
			var id;
			stream.files = (stream.files || {});
			for (id in stream.files)
			{
				if (stream.files.hasOwnProperty(id))
				{
					this.adjustProcess(stream.id, this.repo.getItem(id), statuses.inprogress, procent);
				}
			}
			BX.onCustomEvent(this, 'processPackage', [stream, this, procent]);
		}
	};

	BX.UploaderStream = function(_id, streamsManager)
	{
		this.id = 'stream' + _id;
		this._id = _id;
		this.manager = streamsManager;
		this._onsuccess = BX.delegate(this.onsuccess, this);
		this._onfailure = BX.delegate(this.onfailure, this);
		this._onerror = BX.delegate(this.onerror, this);
		this._onprogress = BX.delegate(this.onprogress, this);
	};
	BX.UploaderStream.prototype =
	{
		xhr : {},
		init : function(pack)
		{
			this["pIndex"] = pack.id;
			this.pack = pack;
			this.files = {};
			this.filesCount = 0;
			this.filesSize = 0;
			this.postSize = pack.post.size;
		},
		send : function(fd)
		{
			if (fd && fd.local === true)
			{
				BX.adjust(fd.form, { attrs : { action: fd.action} } );
				BX.ajax.submit(fd.form, BX.proxy(function(data) {
					data = BX.util.htmlspecialcharsback(data);
					while (/^<(.*?)>(.*?)<(.*?)>$/gi.test(data))
						data = data.replace(/^<(.*?)>(.*?)<(.*?)>$/gi, "$2");
					while (/^<([^<>]+)>(.*?)/gi.test(data))
						data = data.replace(/^<(.*?)>(.*?)/gi, "$2");
					while (/(.+?)<([^<>]+)>$/gi.test(data))
						data = data.replace(/(.+?)<([^<>]+)>$/gi, "$1");

					var res = BX.parseJSON(data, {});

					if (!!res)
					{
						this.onsuccess(res);
					}
					else
					{
						this.onfailure("processing", data);
					}
				}, this) );
				this.onprogress(90);
			}
			else if (fd)
			{
				this.xhr = BX.ajax({
					'method': 'POST',
					'dataType': 'json',
					'data' : fd,
					'url': fd.action,
					'onsuccess': this._onsuccess,
					'onfailure': this._onfailure,
					'onerror': this._onerror,
					'start': false,
					'preparePost':false,
					'processData':true,
					'skipAuthCheck': true,
					'timeout' : settings.maxTimeForUploadFile
				});
				this.xhr.upload.addEventListener('progress', this._onprogress, false);
				var d = new Date();
				this.xhr.startTime = d.getTime();
				this.xhr.send(fd);
			}
			else
			{
				this.onfailure("empty", null);
			}
		},
		onsuccess : function(data)
		{
			var d = new Date();
			this.xhr.finishTime = d.getTime();
			try
			{
				if (typeof data == "object" && data && data["files"] && data["status"] !== "error")
					BX.onCustomEvent(this, 'onsuccess', [this, data]);
				else
					BX.onCustomEvent(this, 'onfailure', [this, data]);
			}
			catch (e)
			{
				BX.debug(e);
			}
			BX.onCustomEvent(this, 'onrelease', [this]);
		},
		onfailure : function(status, e)
		{
			var d = new Date(), data = (e && e["data"] ? BX.parseJSON(e["data"], {}) : "");

			if (BX.message("bxUploaderLog") === "Y" && status === "processing")
			{
				BX.ajax.post(
					"/bitrix/tools/upload.php?action=error",
					{
						sessid : BX.bitrix_sessid(),
						path : window.location.pathname,
						data : e["data"]
					}
				);
			}
			this.xhr.finishTime = d.getTime();
			BX.onCustomEvent(this, 'onfailure', [this, data]);
			BX.onCustomEvent(this, 'onrelease', [this]);
		},
		onerror : function()
		{
			var d = new Date();
			this.xhr.finishTime = d.getTime();
			this.onfailure.apply(arguments);
		},
		onprogress : function(e)
		{
			var procent = 15;
			if(typeof e == "object" && e.lengthComputable) {
				procent = e.loaded * 100 / (e["total"] || e["totalSize"]);
			}
			else if (e > procent)
				procent = e;
			procent = (procent > 5 ? procent : 5);

			BX.onCustomEvent(this, 'onprogress', [this, procent]);
			return procent;
		},
		kill : function()
		{
			BX.DoNothing();
			BX.onCustomEvent(this, 'onkill', [this]);
		},
		restore : function()
		{
			this.manager.restore(this);
		}
	};

	BX.UploaderStreams = function(count, uploader)
	{
		this.streams = new BX.UploaderUtils.Hash();
		this.killedStreams = new BX.UploaderUtils.Hash();
		this.packages = new BX.UploaderUtils.Hash();
		this.uploaded = uploader;
		this.timeout = 3000; // time between streams
		this._exec = BX.delegate(this.exec, this);
		this._restore = BX.delegate(this.restore, this);
		this._kill = BX.delegate(this.kill, this);
		this.count = Math.min(5, (count > 1 ? count : 1));
		this.status = statuses.ready;

	};
	BX.UploaderStreams.prototype = {
		init : function(pack, handler)
		{
			if (this.package !== pack)
			{
				this.package = pack;
				this.package.log('streams are occupied', handler);
				this.packages.setItem(pack.id, pack.post); // For compatibility
				this.handler = handler;
				var count = this.count, stream;
				while ((stream = this.streams.getFirst()) && stream)
				{
					this.streams.removeItem(stream.id);
					stream = null;
				}
				this.streams = new BX.UploaderUtils.Hash();
				while (count-- > 0)
				{
					stream = new BX.UploaderStream(count, this);
					BX.addCustomEvent(stream, 'onrelease', this._restore);
					BX.addCustomEvent(stream, 'onkill', this._kill);
					this.streams.setItem(stream.id, stream);
				}
			}
			this.start();
		},
		exec : function()
		{
			if (this.status == statuses.ready)
			{
				this.package.log('streams are in executing');
				var stream = this.streams.getFirst();
				if (stream)
				{
					this.streams.removeItem(stream.id);
					if (this.streams.length > 0)
					{
						setTimeout(this._exec, this.timeout);
					}
					this.handler(stream); // return to package class
				}
			}
			else
			{
				this.package.log('streams are locked');
			}
		},
		restore : function(stream)
		{
			this.streams.setItem(stream.id, stream);
			BX.defer_proxy(this.exec, this)();
		},
		kill : function(stream)
		{
			this.killedStreams.setItem(stream.id, stream);
			if (this.killedStreams.length == this.count)
			{
				BX.onCustomEvent(this, 'onrelease', [this, stream]);
			}
		},
		start : function()
		{
			this.status = statuses.ready;
			this.exec();
		},
		stop : function()
		{
			this.status = statuses.stopped;
		}
	};
	BX.Uploader.getVersion = function() {
		return "1";
	}
}(window));
