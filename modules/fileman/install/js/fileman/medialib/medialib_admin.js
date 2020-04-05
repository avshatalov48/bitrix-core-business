;(function(window){
	var cnvConstr = null, cnvEdtr = null;
	if (BX["UploaderTemplateThumbnails"])
		return false;

	BX.UploaderTemplateThumbnails = function(params, settings)
	{
		this.id = params["id"];
		this.dialogName = "BX.UploaderTemplateThumbnails";
		this.vars = {
			"filesCountForUpload" : 0
		};
		params["phpMaxFileUploads"] = 10
		if (!!params["copies"])
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

				BX.userOptions.save('fileman', 'uploader_html5', 'template', "reduced");

				BX.addClass(BX('bxuReduced' + this.id), 'bxu-templates-btn-active');
				BX.removeClass(BX('bxuEnlarge' + this.id), 'bxu-templates-btn-active');
				BX.addClass(BX('bxuMain' + this.id), 'bxu-main-block-reduced-size');
			}, this));
			BX.bind(BX('bxuEnlarge' + this.id), "click", BX.delegate(function(){
				BX.userOptions.save('fileman', 'uploader_html5', 'template', "full");

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
		onUploadDone : function(item, file, queue)
		{
			BX.defer_proxy(item.deleteFile, item)();
			this.vars["uploadedFilesCount"]++;
			BX('bxuUploaded' + this.id).innerHTML = this.vars["uploadedFilesCount"];
			BX('bxuUploadBar' + this.id).style.width = Math.ceil(this.vars["uploadedFilesCount"] / this.vars["filesCountForUpload"] * 100) + '%';
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
			if(!!data && this.uploader.queue.itFailed.length <= 0) {
				var d = data.report.uploading[this.uploader.CID];
				if (!!d['redirectUrl'])
					BX.reload(d['redirectUrl']);
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
				this.vars["filesCount"] = queue.items.length;
				BX('bxuImagesCount' + this.id).innerHTML = queue.items.length;
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
				if (BX(id + 'Thumb')) BX.addClass(BX(id + 'Thumb'), "bx-bxu-thumb-file");
				if (BX(id + 'Thumb')) BX.addClass(BX(id + 'Thumb'), "bx-bxu-thumb-" + file.ext);
				if (BX(id + 'Canvas') && !BX(id + 'Canvas').hasChildNodes())
					BX(id + 'Canvas').innerHTML = [
						'<span class="bx-bxu-thumb-file-icon bxu-item-',  file.ext, '">',
							'<span class="bxu-item-block-icon-holder"></span>',
						'</span>'
					].join('')
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
}(window));