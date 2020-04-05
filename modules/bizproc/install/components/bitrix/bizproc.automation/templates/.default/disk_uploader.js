;if (!BX.getClass('BX.Bizproc.Automation.DiskUploader')) (function(BX)
{
	'use strict';
	BX.namespace('BX.Bizproc.Automation');
	var DiskUploader = function()
	{
		this._id = "";
		this._settings = {};
		this._messages = {};
		this._agent = null;
		this._form = this._wrapper = this._switchContainer = this._container = this._fileInput = this._fileSelector = this._label = this._dropZoneWrapper = this._dropZone = this._itemContainer = null;
		this._mode = DiskUploader.InterfaceMode.edit;
		this._items = [];

		this._fileUploadStartHandler = BX.delegate(this._onFileUploadStart, this);
		this._fileUploadProgressHandler = BX.delegate(this._onFileUploadProgress, this);
		this._fileUploadCompleteHandler = BX.delegate(this._onFileUploadComplete, this);
		this._fileUploadErrorHandler = BX.delegate(this._onFileUploadError, this);

		this._fileSelectButtonClickHandler = BX.delegate(this._onFileSelectButtonClick, this);
		this._fileDialogInitHandler = BX.delegate(this._onFileDialogInit, this);
		this._dropZoneMouseOverHandler = BX.delegate(this._onDropZoneMouseOver, this);
		this._dropZoneMouseOutHandler = BX.delegate(this._onDropZoneMouseOut, this);

		this._agentErrorHandler = BX.delegate(this._onAgentError, this);
		this._agentFileInputReinitHandler = BX.delegate(this._onAgentFileInputReinit, this);
		this._agentFileInitHandler = BX.delegate(this._onAgentFileInit, this);

		this._uploadFileUrl = "/bitrix/tools/disk/uf.php?action=uploadfile";
		this._selectFileUrl = "/bitrix/tools/disk/uf.php?action=selectFile";

		this._isShown = false;
		this._hasLayout = false;
	};

	DiskUploader.InterfaceMode = { edit: 1, view: 2 };

	DiskUploader.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = id;
				this._settings = settings ? settings : {};
				this._messages = this.getSetting("msg", {});
			},
			getId: function()
			{
				return this._id;
			},
			getSetting: function(name, defaultval)
			{
				return typeof(this._settings[name]) != "undefined" ? this._settings[name] : defaultval;
			},
			getMessages: function()
			{
				return this.getSetting("msg", {});
			},
			getMessage: function(name, defaultval)
			{
				if (typeof(defaultval) === "undefined")
				{
					defaultval = "";
				}

				return this._messages.hasOwnProperty(name) ? this._messages[name] : defaultval;
			},
			getFileInputName: function()
			{
				return this._fileInput;
			},
			getPlaceHolder: function()
			{
				return this._itemContainer;
			},
			getMode: function()
			{
				return this._mode;
			},
			setMode: function(mode)
			{
				if (this._mode === mode)
				{
					return;
				}

				if (this._hasLayout)
				{
					throw "Could not set mode while control has layout.";
				}

				this._mode = mode;
			},
			getAgent: function()
			{
				return this._agent;
			},
			getItems: function()
			{
				return this._items;
			},
			getItem: function(id)
			{
				for (var i = 0; i < this._items.length; i++)
				{
					var item = this._items[i];
					if (item.getId() === id)
					{
						return item;
					}
				}
				return null;
			},
			hasItems: function()
			{
				return this._items.length > 0;
			},
			getFileIds: function()
			{
				var result = [];
				for (var i = 0; i < this._items.length; i++)
				{
					var fileId = this._items[i].getFileId();
					if (fileId > 0)
					{
						result.push(fileId);
					}
				}
				return result;
			},
			getValues: function()
			{
				return this.getFileIds();
			},
			setValues: function(vals)
			{
				for (var i = 0; i < vals.length; i++)
				{
					var datum = vals[i];
					var fileId = typeof(datum["ID"]) !== "undefined" ? parseInt(datum["ID"]) : 0;
					var item = this.addItem(fileId.toString(),
						{
							fileId: fileId,
							name: datum["NAME"],
							size: datum["SIZE"],
							viewUrl: BX.type.isNotEmptyString(datum["VIEW_URL"]) ? datum["VIEW_URL"] : "",
							progress: 100

						}
					);
					if (this._hasLayout)
					{
						item.setContainer(this._itemContainer);
						item.layout();
					}
				}

				if (this.hasItems())
				{
					this.show(true);
				}
			},
			clearValues: function()
			{
				this.removeAllItems();
			},
			removeItem: function(item)
			{
				for (var i = 0; i < this._items.length; i++)
				{
					if (this._items[i] === item)
					{
						item.cleanLayout();
						this._items.splice(i, 1);
						return;
					}
				}
			},
			removeAllItems: function()
			{
				for (var i = 0; i < this._items.length; i++)
				{
					this._items[i].cleanLayout();
				}
				this._items = [];
			},
			layout: function(parent)
			{
				var mode = this._mode;
				if (mode === DiskUploader.InterfaceMode.edit)
				{
					this.prepareEditLayout(parent);
					this._agent = BX.Uploader.getInstance(
						{
							id: this._id,
							allowUpload: "A",
							uploadMethod: "immediate",
							uploadFileUrl: this._uploadFileUrl,
							deleteFileOnServer: true,
							filesInputMultiple: true,
							filesInputName: this.getFileInputName(),
							input: this._fileInput,
							dropZone: this._dropZone,
							showImage: false,
							fields: {preview: {params: {width: 212, height: 119}}}
						}
					);

					BX.addCustomEvent(this._agent, "onError", this._agentErrorHandler);
					BX.addCustomEvent(this._agent, "onFileinputIsReinited", this._agentFileInputReinitHandler);
					BX.addCustomEvent(this._agent, "onFileIsInited", this._agentFileInitHandler);

					this._container.style.display = this._isShown ? "block" : "none";
					this._switchContainer.style.display = this._isShown ? "none" : "block";
					this._label.style.display = this.hasItems() ? "" : "none";
				}
				else if (mode === DiskUploader.InterfaceMode.view)
				{
					this._prepareViewLayout(parent);
				}

				for (var i = 0; i < this._items.length; i++)
				{
					var item = this._items[i];
					item.setContainer(this._itemContainer);
					item.layout();

				}
				this._hasLayout = true;
			},
			cleanLayout: function()
			{
				if (!this._hasLayout)
				{
					return;
				}

				if (this._agent)
				{
					BX.removeCustomEvent(this._agent, "onError", this._agentErrorHandler);
					BX.removeCustomEvent(this._agent, "onFileinputIsReinited", this._agentFileInputReinitHandler);
					BX.removeCustomEvent(this._agent, "onFileIsInited", this._agentFileInitHandler);
				}

				if (this._dropZoneWrapper)
				{
					BX.unbind(this._dropZoneWrapper, "mouseover", this._dropZoneMouseOverHandler);
					BX.unbind(this._dropZoneWrapper, "mouseout", this._dropZoneMouseOutHandler);
				}

				if (this._fileSelector)
				{
					BX.unbind(this._fileSelector, "click", this._fileSelectButtonClickHandler);
				}

				if (this._wrapper)
				{
					BX.cleanNode(this._wrapper, true);
				}

				for (var i = 0; i < this._items.length; i++)
				{
					var item = this._items[i];
					item.cleanLayout();
					item.setContainer(null);
				}

				this._form = this._wrapper = this._switchContainer = this._container = this._fileInput = this._label = this._dropZone = this._itemContainer = null;
				this._hasLayout = false;
			},
			show: function(show)
			{
				show = !!show;

				if (this._isShown === show)
				{
					return;
				}

				this._isShown = show;

				if (this._hasLayout)
				{
					this._container.style.display = show ? "block" : "none";
					this._switchContainer.style.display = show ? "none" : "block";
				}
			},
			showLabel: function(show)
			{
				if (this._hasLayout)
				{
					this._label.style.display = !!show ? "" : "none";
				}
			},
			prepareEditLayout: function(parent)
			{
				if (!BX.type.isElementNode(parent))
				{
					return;
				}

				this._form = BX.create("FORM", {});
				parent.appendChild(this._form);

				this._form.appendChild(
					BX.create("INPUT",
						{props: {type: "hidden", name: "sessid", value: BX.bitrix_sessid()}}
					)
				);

				this._switchContainer = BX.create("DIV",
					{
						attrs: {className: "bx-crm-add-file-link"},
						children:
							[
								BX.create("SPAN",
									{
										attrs: {className: "bx-crm-add-file-link-text"},
										text: this.getMessage("diskAttachFiles"),
										events: {click: BX.delegate(this._onSwitchButtonClick, this)}
									}
								)
							]
					}
				);
				this._form.appendChild(this._switchContainer);

				this._container = BX.create("DIV", {attrs: {className: "bx-crm-dialog-activity-diskuf-container"}});
				this._form.appendChild(this._container);
				this._container.style.height = "auto";

				this._innerContainer = BX.create("DIV", {attrs: {className: "bx-crm-dialog-activity-diskuf-container-inner"}});
				this._container.appendChild(this._innerContainer);

				this._wrapper = BX.create("DIV", {attrs: {className: "diskuf-selectdialog"}});
				this._innerContainer.appendChild(this._wrapper);
				this._wrapper.style.display = "block";
				this._wrapper.style.opacity = "1";

				var fileWrapper = BX.create("DIV", {attrs: {className: "diskuf-files-block"}});
				fileWrapper.style.display = "block";
				this._wrapper.appendChild(fileWrapper);

				this._label = BX.create("DIV",
					{
						attrs: {className: "diskuf-label"},
						children:
							[
								BX.create("SPAN", {text: this.getMessage("diskAttachedFiles") + ":"}),
								BX.create("SPAN", {attrs: {className: "diskuf-label-icon"}})
							]
					}
				);
				fileWrapper.appendChild(this._label);

				var itemPlaceHolder = BX.create("DIV", {attrs: {className: "diskuf-placeholder"}});
				fileWrapper.appendChild(itemPlaceHolder);

				this._itemContainer = BX.create("TABLE", {
					attrs: {
						className: "files-list",
						"cellspacing": "0",
						"cellpadding": "0",
						"border": "0"
					}
				});
				itemPlaceHolder.appendChild(this._itemContainer);

				var actionWrapper = BX.create("DIV", {attrs: {className: "diskuf-extended"}});
				actionWrapper.style.display = "block";
				this._wrapper.appendChild(actionWrapper);

				var actionTable = BX.create("TABLE", {
					attrs: {
						className: "diskuf-selector-table wd-fa-add-file-light-table",
						"cellspacing": "0",
						"cellpadding": "0",
						"border": "0"
					}
				});
				actionWrapper.appendChild(actionTable);

				var actionRow = actionTable.insertRow(-1);
				var cell = actionRow.insertCell(-1);
				cell.className = "wd-fa-add-file-light-cell";

				this._fileSelector = BX.create("DIV",
					{
						attrs: {className: "wd-fa-add-file-light-title-text diskuf-selector-link"},
						text: this.getMessage("diskSelectFile")
					}
				);
				BX.bind(this._fileSelector, "click", this._fileSelectButtonClickHandler);

				cell.appendChild(
					BX.create("SPAN",
						{
							attrs: {className: "wd-fa-add-file-light"},
							children:
								[
									BX.create("SPAN",
										{
											attrs: {className: "wd-fa-add-file-light-text"},
											children:
												[
													BX.create("SPAN",
														{
															attrs: {className: "wd-fa-add-file-light-title"},
															children: [this._fileSelector]
														}
													),
													BX.create("SPAN",
														{
															attrs: {className: "wd-fa-add-file-light-descript"},
															text: this.getMessage("diskSelectFileLegend")
														}
													)
												]
										}
									)
								]
						}
					)
				);

				cell = actionRow.insertCell(-1);
				cell.className = "wd-fa-add-file-form-light-separate-cell";

				cell.appendChild(BX.create("DIV", {attrs: {className: "wd-fa-add-file-form-light-spacer"}}));

				cell = this._dropZoneWrapper = actionRow.insertCell(-1);
				cell.className = "diskuf-selector wd-fa-add-file-light-cell wd-fa-add-file-from-main";

				this._fileInput = BX.create("INPUT",
					{
						attrs: {className: "diskuf-fileUploader wd-test-file-light-inp "},
						props: {type: "file", size: 1, multiple: "multiple", name: this.getFileInputName()}
					}
				);

				if (BX.browser.IsIE())
				{
					this._dropZone = BX.create("DIV",
						{
							attrs: {className: "wduf-selector"},
							children:
								[
									BX.create("SPAN",
										{
											attrs: {className: "wduf-uploader"},
											children:
												[
													BX.create("SPAN", {attrs: {className: "wduf-uploader-left"}}),
													BX.create("SPAN", {
														attrs: {className: "wduf-but-text"},
														text: this.getMessage("loadFiles")
													}),
													BX.create("SPAN", {attrs: {className: "wduf-uploader-right"}}),
													this._fileInput
												]
										}
									)
								]
						}
					);
				}
				else
				{
					this._dropZone = BX.create("DIV",
						{
							attrs: {className: "diskuf-uploader"},
							children:
								[
									BX.create("SPAN",
										{
											attrs: {className: "wd-fa-add-file-light"},
											children:
												[
													BX.create("SPAN",
														{
															attrs: {className: "wd-fa-add-file-light-text"},
															children:
																[
																	BX.create("SPAN",
																		{
																			attrs: {className: "wd-fa-add-file-light-title"},
																			children:
																				[
																					BX.create("SPAN",
																						{
																							attrs: {className: "wd-fa-add-file-light-title-text"},
																							text: this.getMessage("diskUploadFile")
																						}
																					)
																				]
																		}
																	),
																	BX.create("SPAN",
																		{
																			attrs: {className: "wd-fa-add-file-light-descript"},
																			text: this.getMessage("diskUploadFileLegend")
																		}
																	)
																]
														}
													)
												]
										}
									),
									this._fileInput
								]
						}
					);
				}

				cell.appendChild(this._dropZone);

				BX.bind(cell, "mouseover", this._dropZoneMouseOverHandler);
				BX.bind(cell, "mouseout", this._dropZoneMouseOutHandler);
			},
			_prepareViewLayout: function(parent)
			{
				if (!BX.type.isElementNode(parent))
				{
					return;
				}

				this._form = BX.create("FORM", {});
				parent.appendChild(this._form);

				this._form.appendChild(
					BX.create("INPUT",
						{props: {type: "hidden", name: "sessid", value: BX.bitrix_sessid()}}
					)
				);

				this._container = BX.create("DIV", {attrs: {className: "bx-crm-dialog-activity-diskuf-container"}});
				this._form.appendChild(this._container);
				this._container.style.height = "auto";

				this._innerContainer = BX.create("DIV", {attrs: {className: "bx-crm-dialog-activity-diskuf-container-inner"}});
				this._container.appendChild(this._innerContainer);

				this._wrapper = BX.create("DIV", {attrs: {className: "diskuf-selectdialog"}});
				this._innerContainer.appendChild(this._wrapper);
				this._wrapper.style.display = "block";
				this._wrapper.style.opacity = "1";

				var fileWrapper = BX.create("DIV", {attrs: {className: "diskuf-files-block"}});
				fileWrapper.style.display = "block";
				this._wrapper.appendChild(fileWrapper);

				this._label = BX.create("DIV",
					{
						attrs: {className: "diskuf-label"},
						children:
							[
								BX.create("SPAN", {text: this.getMessage("diskAttachedFiles") + ":"}),
								BX.create("SPAN", {attrs: {className: "diskuf-label-icon"}})
							]
					}
				);
				fileWrapper.appendChild(this._label);

				var itemPlaceHolder = BX.create("DIV", {attrs: {className: "diskuf-placeholder"}});
				fileWrapper.appendChild(itemPlaceHolder);

				this._itemContainer = BX.create("TABLE", {
					attrs: {
						className: "files-list",
						"cellspacing": "0",
						"cellpadding": "0",
						"border": "0"
					}
				});
				itemPlaceHolder.appendChild(this._itemContainer);
			},
			processItemDeletion: function(item)
			{
				this.removeItem(item);
				if (!this.hasItems())
				{
					this.showLabel(false);
				}
			},
			_onSwitchButtonClick: function(e)
			{
				this.show(true);
				return BX.PreventDefault(e);
			},
			_onAgentFileInputReinit: function(input)
			{
				if (input || this._agent.fileInput)
				{
					this._fileInput = input ? input : this._agent.fileInput;
				}
			},
			_onAgentError: function(stream, pIndex, data)
			{
			},
			_onAgentFileInit: function(id, queueItem, agent)
			{
				BX.addCustomEvent(queueItem, "onUploadStart", this._fileUploadStartHandler);
				BX.addCustomEvent(queueItem, 'onUploadProgress', this._fileUploadProgressHandler);
				BX.addCustomEvent(queueItem, 'onUploadDone', this._fileUploadCompleteHandler);
				BX.addCustomEvent(queueItem, 'onUploadError', this._fileUploadErrorHandler);
			},
			addItem: function(id, info)
			{
				var item = DiskUploaderItem.create(id,
					{
						uploader: this,
						container: this._itemContainer,
						name: BX.type.isNotEmptyString(info.name) ? info.name : "",
						size: BX.type.isNotEmptyString(info.size) ? info.size : "",
						fileId: BX.type.isNumber(info.fileId) ? info.fileId : 0,
						viewUrl: BX.type.isNotEmptyString(info.viewUrl) ? info.viewUrl : "",
						progress: BX.type.isNumber(info.progress) ? info.progress : 0
					}
				);
				this._items.push(item);
				return item;
			},
			_onFileUploadStart: function(queueItem, percent, agent, pIndex)
			{
				var item = this.addItem(queueItem.id,
					{
						name: queueItem.name,
						size: queueItem.size,
						fileId: 0,
						progress: parseInt(percent)
					}
				);
				item.layout();
			},
			_onFileUploadProgress: function(queueItem, percent, agent, pIndex)
			{
				var item = this.getItem(queueItem.id);
				if (item)
				{
					if (percent >= 100)
						percent = 99;
					item.setProgress(percent);
				}
			},
			_onFileUploadComplete: function(queueItem, params, agent, pIndex)
			{
				var item = this.getItem(queueItem.id);
				if (!item)
				{
					return;
				}

				item.setProgress(100);

				BX.removeCustomEvent(queueItem, "onUploadStart", this._fileUploadStartHandler);
				BX.removeCustomEvent(queueItem, "onUploadProgress", this._fileUploadProgressHandler);
				BX.removeCustomEvent(queueItem, "onUploadDone", this._fileUploadCompleteHandler);
				BX.removeCustomEvent(queueItem, "onUploadError", this._fileUploadErrorHandler);

				var fileId = 0;
				if (typeof(params.file) !== "undefined")
				{
					if (typeof(params.file["fileId"]) !== "undefined")
					{
						fileId = parseInt(params.file["fileId"]);
					}
					else if (typeof(params.file["originalId"]) !== "undefined")
					{
						fileId = parseInt(params.file["originalId"]);
					}
				}

				if (fileId > 0)
				{
					item.setFileId(fileId);
				}

				this.showLabel(true);
			},
			_onFileUploadError: function(queueItem, params, agent, pIndex)
			{
				var item = this.getItem(queueItem.id);
				if (item)
				{
					item.remove();
				}

				BX.removeCustomEvent(queueItem, "onUploadStart", this._fileUploadStartHandler);
				BX.removeCustomEvent(queueItem, "onUploadProgress", this._fileUploadProgressHandler);
				BX.removeCustomEvent(queueItem, "onUploadDone", this._fileUploadCompleteHandler);
				BX.removeCustomEvent(queueItem, "onUploadError", this._fileUploadErrorHandler);
			},
			_onFileSelectButtonClick: function(e)
			{
				BX.addCustomEvent(BX.DiskFileDialog, "inited", this._fileDialogInitHandler);
				BX.ajax(
					{
						url: this._selectFileUrl,
						method: "GET",
						timeout: 30
					}
				);
				return BX.PreventDefault(e);
			},
			_onFileDialogInit: function(name)
			{
				BX.removeCustomEvent(BX.DiskFileDialog, "inited", this._fileDialogInitHandler);
				this.flagFileDialogInited = true;
				BX.DiskFileDialog.obCallback[name] = {'saveButton': BX.delegate(this._onFileSelect, this)};
				BX.DiskFileDialog.openDialog(name);
			},
			_onFileSelect: function(tab, path, selected)
			{
				for (var key in selected)
				{
					if (!selected.hasOwnProperty(key))
					{
						return;
					}

					var info = selected[key];
					var type = BX.type.isNotEmptyString(info["type"]) ? info["type"] : "";
					if (type !== "file")
					{
						continue;
					}

					var id = BX.type.isNotEmptyString(info["id"]) ? info["id"] : "";
					if (id === "")
					{
						continue;
					}

					var re = /^n(\d+)$/;
					var m = re.exec(id);
					if (m && m.length > 1)
					{
						var fileId = parseInt(m[1]);
						if (fileId > 0)
						{
							var name = BX.type.isNotEmptyString(info["name"]) ? info["name"] : id;
							var size = BX.type.isNotEmptyString(info["size"]) ? info["size"] : 0;
							this.addItem(id, {fileId: fileId, name: name, size: size, progress: 100}).layout();
						}
					}
				}
			},
			_onDropZoneMouseOver: function(e)
			{
				BX.addClass(this._dropZoneWrapper, "wd-fa-add-file-light-hover");
			},
			_onDropZoneMouseOut: function(e)
			{
				BX.removeClass(this._dropZoneWrapper, "wd-fa-add-file-light-hover");
			}
		};

	DiskUploader.items = {};
	DiskUploader.create = function(id, settings)
	{
		if (!BX.type.isNotEmptyString(id))
		{
			id = 'BX_BP_ATM_FILEUPLOADER_' + Math.random();
		}

		var self = new DiskUploader();
		self.initialize(id, settings);
		this.items[id] = self;
		return self;
	};

	var DiskUploaderItem = function()
	{
		this._id = "";
		this._settings = {};
		this._fileId = 0;
		this._name = "";
		this._size = "";
		this._viewUrl = "";
		this._progress = 0;

		this._uploader = this._container = this._wrapper = null;
		this._progressContainer = this._progressWrap = this._progressTerminateBtn = this._progressBar = this._progressText = this._deleteButton = null;
		this._deleteButtonClickHandler = BX.delegate(this._onDeleteButtonClick, this);

		this._hasLayout = false;
	};

	DiskUploaderItem.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = id;
				this._settings = settings ? settings : {};

				this._uploader = this.getSetting("uploader");
				this._container = this.getSetting("container");

				this._fileId = parseInt(this.getSetting("fileId", 0));
				this._name = this.getSetting("name", "");
				this._size = this.getSetting("size", "");
				this._viewUrl = this.getSetting("viewUrl", "");
				this.setProgress(this.getSetting("progress", 0));
			},
			getId: function()
			{
				return this._id;
			},
			getSetting: function(name, defaultval)
			{
				return typeof(this._settings[name]) != "undefined" ? this._settings[name] : defaultval;
			},
			getFileId: function()
			{
				return this._fileId;
			},
			setFileId: function(fileId)
			{
				this._fileId = fileId;
			},
			getName: function()
			{
				return this._name;
			},
			getSize: function()
			{
				return this._size;
			},
			getProgress: function()
			{
				return this._progress;
			},
			setProgress: function(progress)
			{
				progress = parseInt(progress);
				if (isNaN(progress) || progress < 0)
				{
					progress = 0;
				}

				if (progress > 100)
				{
					progress = 100;
				}

				if (this._progress === progress)
				{
					return;
				}

				this._progress = progress;
				if (this._hasLayout)
				{
					this._progressBar.style.width = this._progress + "%";
					this._progressText.innerHTML = this._progress + "%";
					this._progressWrap.style.display = progress < 100 ? "" : "none";
				}
			},
			getContainer: function()
			{
				return this._container;
			},
			setContainer: function(container)
			{
				this._container = container;
			},
			layout: function()
			{
				if (this._hasLayout)
				{
					return;
				}

				if (!this._container)
				{
					throw "Error: Could not find container.";
				}

				var row = this._wrapper = this._container.insertRow(-1);
				var cell = row.insertCell(-1);
				cell.className = "files-name";

				if (this._viewUrl !== "")
				{
					cell.appendChild(BX.create("A", {
						attrs: {className: "files-text", href: this._viewUrl},
						text: this._name
					}));
				}
				else
				{
					cell.appendChild(BX.create("SPAN", {attrs: {className: "files-text"}, text: this._name}));
				}

				cell = row.insertCell(-1);
				cell.className = "files-size";
				cell.innerHTML = this._size;

				cell = this._progressContainer = row.insertCell(-1);
				cell.className = "files-storage";

				this._progressWrap = BX.create("SPAN", {attrs: {className: "feed-add-post-loading-wrap"}});
				cell.appendChild(this._progressWrap);

				this._progressTerminateBtn = BX.create("SPAN", {attrs: {className: "feed-add-post-loading-cancel del-but"}});
				BX.bind(this._progressTerminateBtn, "click", this._deleteButtonClickHandler);
				this._progressWrap.appendChild(
					BX.create("SPAN", {
						attrs: {className: "feed-add-post-loading"},
						children: [this._progressTerminateBtn]
					})
				);

				this._progressBar = BX.create("SPAN", {
					attrs: {className: "feed-add-post-load-indicator"},
					style: {width: (this._progress + "%")}
				});
				this._progressWrap.appendChild(this._progressBar);

				this._progressText = BX.create("SPAN", {
					attrs: {className: "feed-add-post-load-number"},
					text: (this._progress + "%")
				});
				this._progressBar.appendChild(this._progressText);

				if (this._progress === 100)
				{
					this._progressWrap.style.display = "none";
				}

				//cell = row.insertCell(-1);
				//cell.className = "files-info";

				cell = row.insertCell(-1);
				cell.className = "files-del-btn";

				if (this._uploader.getMode() === DiskUploader.InterfaceMode.edit)
				{
					this._deleteButton = BX.create("SPAN", {attrs: {className: "del-but"}});
					BX.bind(this._deleteButton, "click", this._deleteButtonClickHandler);
					cell.appendChild(this._deleteButton);
				}

				this._hasLayout = true;
			},
			cleanLayout: function()
			{
				if (!this._hasLayout)
				{
					return;
				}

				BX.unbind(this._progressTerminateBtn, "click", this._deleteButtonClickHandler);
				if (this._deleteButton)
				{
					BX.unbind(this._deleteButton, "click", this._deleteButtonClickHandler);
				}

				this._container.deleteRow(this._wrapper.rowIndex);
				this._wrapper = this._progressContainer = this._progressWrap = this._progressTerminateBtn = this._progressBar = this._progressText = this._deleteButton = null;
				this._hasLayout = false;
			},
			remove: function()
			{
				if (this._uploader.getMode() !== DiskUploader.InterfaceMode.edit)
				{
					return;
				}

				var agent = this._uploader.getAgent();
				if (agent)
				{
					var item = agent.queue.items.getItem(this._id);
					if (item)
					{
						item.deleteFile();
					}
				}

				this._uploader.processItemDeletion(this);
			},
			_onDeleteButtonClick: function(e)
			{
				this.remove();
			}
		};

	DiskUploaderItem.create = function(id, settings)
	{
		var self = new DiskUploaderItem();
		self.initialize(id, settings);
		return self;
	};

	BX.Bizproc.Automation.DiskUploader = DiskUploader;
})(window.BX || window.top.BX);