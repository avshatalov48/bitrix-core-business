;(function(window) {
	var repo = {};
	BX.namespace("BX.Disk");
	if (BX.Disk.UFMobile)
		return;
	BX.Disk.UFMobile = (function () {
		var UF = function (params) {
			this.dialogName = "DiskFileDialog";
			this.params = params;
			this.CID = params['UID'];
			this.controlName = params['controlName'];
			this.container = BX('diskuf-placeholder-' + params['UID']);

			if (BX('diskuf-eventnode-' + params['UID']))
			{
				BX.bind(BX('diskuf-eventnode-' + params['UID']), "click", BX.proxy(this.click, this));
			}
			this.handleAppFile = BX.delegate(this.handleAppFile, this);
			this.showDiskDialog = BX.delegate(this.showDiskDialog, this);
			this.handleDiskFile = BX.delegate(this.handleDiskFile, this);

			this.urls["upload"] = window.location.protocol + '//' + window.location.host + this.urls["upload"];
			this.urls["folder"] = window.location.protocol + '//' + window.location.host + BX.message('SITE_DIR') + this.urls["folder"];
			this.urls["getAttach"] = BX.message('SITE_DIR') + this.urls["getAttach"];
			this.urls["getFile"] = BX.message('SITE_DIR') + this.urls["getFile"];

			this.agent = BX.Uploader.getInstance({
				id : this.CID,
				streams : 1,
				allowUpload : "A",
				uploadFormData : "N",
				uploadMethod : "immediate",
				uploadFileUrl : this.urls["upload"],
				showImage : true,
				sortItems : false,
				input : null,
				dropZone : null,
				placeHolder : this.container,
				queueFields : {
					thumb : {
						tagName : "DIV",
						className : "mobile-grid-field-file-item mobile-grid-field-file-file"
					}
				},
				fields : {
					thumb : {
						tagName : "",
						template : BX.message('DISK_NODE')
					},
					preview : {
						params : {
							width: 212,
							height: 119
						}
					}
				}
			});

			this.init(params['values']);
			return this;
		};

		UF.prototype = {
			urls : {
				upload : '/bitrix/tools/disk/uf.php?action=uploadFile&ncc=1',
				folder : 'mobile/?mobile_action=disk_folder_list&type=user&path=%2F&entityId=' + BX.message('USER_ID'),
				getAttach : "mobile/ajax.php?mobile_action=disk_uf_view&action=download&ncc=1&attachedId=#id#&filename=#name#",
				getFile : "mobile/ajax.php?mobile_action=disk_download_file&action=downloadFile&fileId=#id#&filename=#name#"
			},
			click : function(e) {
				BX.PreventDefault(e);
				this.show();
				return false;
			},
			show : function() {
				var buttons = [
					{
						title: BX.message("MPF_PHOTO_CAMERA"),
						callback: BX.delegate(function()
						{
							window.app.takePhoto({
								quality: 80,
								source: 1,
								correctOrientation: true,
								targetWidth: 1024,
								targetHeight: 1024,
								destinationType: window.Camera.DestinationType.DATA_URL,
								callback: this.handleAppFile
							});
						}, this)
					},
					{
						title: BX.message("MPF_PHOTO_GALLERY"),
						callback: BX.delegate(function()
						{
							window.app.takePhoto({
								quality: 80,
								targetWidth: 1024,
								targetHeight: 1024,
								destinationType: window.Camera.DestinationType.DATA_URL,
								callback: this.handleAppFile
							});
						}, this)
					}
				];
				if (false && window.platform == "android") {
					buttons.push({
						title: BX.message("MPF_PHOTO_DISK"),
						callback: this.showDiskDialog
					});
				}
				(new window.BXMobileApp.UI.ActionSheet( { buttons: buttons }, "textPanelSheet" )).show();
			},
			handleAppFile : function(image) {
				var dataBlob = BX.UploaderUtils.dataURLToBlob("data:image/jpg;base64,"+image);
				dataBlob.name = 'mobile_'+BX.date.format("Ymd_His")+'.jpg';
				(this.agent && this.agent.onChange([dataBlob]));
			},

			init : function(values) {
				this._onFileIsCreated = BX.delegate(this.onFileIsCreated, this);

				this._onFileIsBound = BX.delegate(this.onFileIsBound, this);
				this._onFileIsAppended = BX.delegate(this.onFileIsAppended, this);
				this._onUploadStart = BX.delegate(this.onUploadStart, this);
				this._onUploadProgress = BX.delegate(this.onUploadProgress, this);
				this._onUploadDone = BX.delegate(this.onUploadDone, this);
				this._onUploadError = BX.delegate(this.onUploadError, this);

				BX.addCustomEvent(this.agent, "onFileIsCreated", this._onFileIsCreated);

				if (values && values.length > 0)
				{
					var ar1 = [], ar2 = [], name;
					for (var ii = 0; ii < values.length; ii++)
					{
						name = BX.findChild(values[ii], {'className' : 'mobile-grid-field-file-name'}, true);
						if (BX(name))
						{
							ar1.push({
								name : name.innerHTML,
								id : values[ii].getAttribute("id").replace("diskuf-", "")
							});
							ar2.push(values[ii]);
						}
					}
					this.agent.onAttach(ar1, ar2);
				}
			},
			onFileIsCreated : function(id, file) {
				if (file["file"] && file["file"]["size"])
					file.size = BX.UploaderUtils.getFormattedSize(file.file.size, 2);
				BX.addCustomEvent(file, 'onFileIsBound', this._onFileIsBound);
				BX.addCustomEvent(file, 'onFileIsAppended', this._onFileIsAppended);
				BX.addCustomEvent(file, 'onUploadStart', this._onUploadStart);
				BX.addCustomEvent(file, 'onUploadProgress', this._onUploadProgress);
				BX.addCustomEvent(file, 'onUploadDone', this._onUploadDone);
				BX.addCustomEvent(file, 'onUploadError', this._onUploadError);
			},
			onFileIsBound : function(id, item) {
				this.bindFile(item);
			},
			onFileIsAppended : function(id, item) {
				this.bindFile(item);
			},
			onUploadStart : function(item) {
				var node = this.agent.getItem(item.id);
				if (node && (node = node.node) && node)
					BX.addClass(node, "mobile-grid-field-file-wait");
			},
			onUploadProgress : function(item, progress) { },
			onUploadDone : function(item, result) {
				var node = this.agent.getItem(item.id);
				if (!node || !((node = node.node) && node))
					return;
				BX.removeClass(node, "mobile-grid-field-file-wait");
				var file = result["file"];
				item.file = { id : file["attachId"], name : file["name"] };
				var n = BX.findChildByClassName(node, 'mobile-grid-field-file-name', true);
				if (n)
					n.innerHTML = file["name"];
//				n = BX.findChildByClassName(node, 'mobile-grid-field-file-size', true);
//				if (n)
//					n.innerHTML = file["size"];
				var inp = BX.create('INPUT', {attrs : {type : "hidden", name : this.controlName, value : file["attachId"]}});
				node.appendChild(inp);
				BX.onCustomEvent(this, "onChange", [this, inp]);
				this.bindFile(item)
			},
			onUploadError : function(item) {
				var node = this.agent.getItem(item.id);
				if (!node || !((node = node.node) && node))
					return;
				BX.removeClass(node, "mobile-grid-field-file-wait");
				BX.addClass(node, "mobile-grid-field-file-error");
			},
			bindFile : function(item) {
				var node = this.agent.getItem(item.id);
				if (!node || !((node = node.node) && node))
					return;
				if (item.dialogName == "BX.UploaderImage")
				{
					if (!BX.hasClass(node, "mobile-grid-field-file-image"))
						BX.addClass(node, "mobile-grid-field-file-image");
					BX.removeClass(node, "mobile-grid-field-file-file");
				}
				var del = BX.findChild(node, {tagName : 'DEL'}, true);
				if (del && !del.hasAttribute("bx-bound"))
				{
					del.setAttribute("bx-bound", "Y");
					BX.bind(del, "click", BX.delegate(function() { this.deleteFile(item); }, this));
				}

				if (item.file && item.file.id)
				{
					var name = BX.findChildByClassName(node, 'mobile-grid-field-file-name', true);
					if (name && !name.hasAttribute("bx-bound"))
					{
						name.setAttribute("bx-bound", "Y");
						BX.bind(name, "click", BX.delegate(function() { this.openFile(item); }, this));
					}
				}
			},
			deleteFile: function(item) {
				var node = this.agent.getItem(item.id);
				if (node && (node = node.node) && node)
					BX.remove(node);
				BX.onCustomEvent(this, "onChange", [this, node]);
			},
			openFile: function(item) {
				var id = item.file.id,
					url = this.urls.getAttach.replace("#id#", item.file.id).replace("#name#", item.file.name);
				if (id.indexOf('n') === 0)
				{
					id = id.replace('n', '');
					url = this.urls.getFile.replace("#id#", id).replace("#name#", item.file.name);
				}
				BXMobileApp.UI.Document.open({url : url});
			}
		};
		return UF;
	})();
	BX.Disk.UFMobileView = (function () {
		var UF = function (params) {
			this.dialogName = "DiskFileDialogView";
			this.params = params;
			this.CID = params['UID'];
			this.container = BX('diskuf-placeholder-' + params['UID']);

			this.urls["getAttach"] = BX.message('SITE_DIR') + this.urls["getAttach"];
			this.urls["getFile"] = BX.message('SITE_DIR') + this.urls["getFile"];

			if (params['values'] && params['values'].length > 0)
				this.init(params['values']);
			return this;
		};

		UF.prototype = {
			urls : {
				getAttach : "mobile/ajax.php?mobile_action=disk_uf_view&action=download&ncc=1&attachedId=#id#&filename=#name#",
				getFile : "mobile/ajax.php?mobile_action=disk_download_file&action=downloadFile&fileId=#id#&filename=#name#"
			},
			init : function(values) {
				for (var ii = 0; ii < values.length; ii++)
				{
					this.bindNode(values[ii]);
				}
			},
			bindNode : function(node) {
				if (BX(node) && !node.hasAttribute("bx-bound"))
				{
					BX.bind(BX(node), "click", BX.delegate(this.openFile, this));
					node.setAttribute("bx-bound", "Y");
				}
			},
			openFile: function() {
				var node = BX.proxy_context;
				if (!BX(node))
					return;

				var id = node.getAttribute("id").replace("diskuf-", ""),
					nameNode = BX.findChild(node, {'className' : 'mobile-grid-field-file-name'}, true),
					name = (nameNode ? nameNode.innerHTML : ''),
					url = this.urls.getAttach.replace("#id#", id).replace("#name#", name);
				if (id && name)
				{
					if (id.indexOf('n') === 0)
					{
						id = id.replace('n', '');
						url = this.urls.getFile.replace("#id#", id).replace("#name#", name);
					}
					console.log('url:', url);
					BXMobileApp.UI.Document.open({url : url});
				}
			}
		};
		return UF;
	})();
	BX.Disk.UFMobile.add = function(params) {
		params['values'] = BX.findChildren(BX('diskuf-placeholder-' + params['UID']), {"className" : "mobile-grid-field-file-item"}, false);
		repo[params['UID']] = new BX.Disk.UFMobile(params);
	};
	BX.Disk.UFMobile.addView = function(params) {
		params['values'] = BX.findChildren(BX('diskuf-placeholder-' + params['UID']), {"className" : "mobile-grid-field-file-item"}, false);
		repo[params['UID']] = new BX.Disk.UFMobileView(params);
	};
	BX.Disk.UFMobile.getByName = function(name) {
		for (var ii in repo)
		{
			if (repo.hasOwnProperty(ii))
			{
				if (repo[ii]["controlName"] == name || repo[ii]["controlName"] == name + '[]')
				{
					return repo[ii];
				}
			}
		}
		return null;
	};
})(window);
