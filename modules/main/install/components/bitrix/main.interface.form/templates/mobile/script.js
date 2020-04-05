;(function() {
	var BX = window.BX,
		BXMobileApp = window.BXMobileApp;
	if (BX && BX["Mobile"] && BX["Mobile"]["Grid"] && BX["Mobile"]["Grid"]["Form"])
		return;
	BX.namespace("BX.Mobile.Grid.Form");
	var repo = {formId : {}, gridId : {}},
		initSelect = (function () {
			var d = function(select, eventNode, container) {
				this.click = BX.delegate(this.click, this);
				this.callback = BX.delegate(this.callback, this);
				this.init(select, eventNode, container);
			};
			d.prototype = {
				multiple : false,
				select : null,
				eventNode : null,
				container : null,
				titles : [],
				values : [],
				defaultTitles : [],
				init : function(select, eventNode, container) {
					if (BX(select) && BX(eventNode) && BX(container))
					{
						this.select = select;
						this.eventNode = eventNode;
						this.container = container;

						if (!this.select.hasAttribute("bx-bound"))
						{
							this.select.setAttribute("bx-bound", "Y");
							BX.addCustomEvent(select, "onChange", BX.delegate(function() {
								this.multiple = this.select.hasAttribute("multiple");
								this.initValues();
							}, this));
							BX.bind(this.eventNode, "click", this.click);
						}
						this.multiple = select.hasAttribute("multiple");
						this.initValues();
					}
				},
				initValues: function() {
					this.titles = [];
					this.values = [];
					this.defaultTitles = [];
					for (var ii = 0; ii < this.select.options.length; ii++)
					{
						this.titles.push(this.select.options[ii].innerHTML);
						this.values.push(this.select.options[ii].value);
						if (this.select.options[ii].hasAttribute("selected"))
							this.defaultTitles.push(this.select.options[ii].innerHTML);

					}
				},
				click : function(e) {
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
					if (this.titles.length > 0)
					{
						BXMobileApp.UI.SelectPicker.show({
							callback: this.callback,
							values: this.titles,
							multiselect: this.multiple,
							default_value : this.defaultTitles
						});
					}
				},
				callback : function(data) {
					this.defaultTitles = [];
					if (data && data.values && data.values.length > 0)
					{
						var keys = [], ii, jj;
						for (ii = 0; ii < this.titles.length; ii++)
						{
							for (jj = 0; jj < data.values.length; jj++)
							{
								if (this.titles[ii] == data.values[jj])
								{
									keys.push(this.values[ii]);
									this.defaultTitles.push(this.titles[ii]);
									break;
								}
							}
						}
						var html = '';
						for (ii = 0; ii < this.select.options.length; ii++)
						{
							this.select.options[ii].removeAttribute("selected");

							if (BX.util.in_array(this.select.options[ii].value, keys))
							{
								this.select.options[ii].setAttribute("selected", "selected");
								if (this.multiple)
								{
									html += '<a href="javascript:void();">' + this.select.options[ii].innerHTML + '</a>';
								}
								else
								{
									html = this.select.options[ii].innerHTML;
								}
							}
						}
						if (html === '' && !this.multiple)
							html = '<span style="color:grey">' + BX.message("interface_form_select") + '</span>';
						this.container.innerHTML = html;
						BX.onCustomEvent(this, "onChange", [this, this.select]);
					}
				}
			};
			return d;
		})(),
		initDatetime = (function () {
		var d = function(node, type, container, formats) {
				this.type = type;
				this.node = node;
				this.container = container;
				this.click = BX.delegate(this.click, this);
				this.callback = BX.delegate(this.callback, this);
				BX.bind(this.container, "click", this.click);
				BX.bind(this.container.parentNode, "click", this.click);
				this.init(formats);
			};
			d.prototype = {
				type : 'datetime', // 'datetime', 'date', 'time'
				format : {
					inner : {
						datetime : 'dd.MM.yyyy H:mm',
						time : 'H:mm',
						date : 'dd.MM.yyyy'
					},
					bitrix : {
						datetime : null,
						time : null,
						date : null
					},
					visible : {
						datetime : null,
						time : null,
						date : null
					}
				},
				node : null,
				click : function(e) {
					BX.eventCancelBubble(e);
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
					var res = {
						type: this.type,
						start_date: this.getStrDate(this.node.value),
						format: this.format.inner[this.type],
						callback: this.callback
					};
					if (res["start_date"] == "")
						delete res["start_date"];
					BXMobileApp.UI.DatePicker.setParams(res);
					BXMobileApp.UI.DatePicker.show();
				},
				callback : function(data) {
					var d = this.makeDate(data);
					this.node.value = BX.date.format(this.format.bitrix[this.type], d);

					var text = BX.date.format(BX.clone(this.format.visible[this.type]), d);
					if (!BX.type.isNotEmptyString(text))
						text = this.container.getAttribute("placeholder") || ' ';

					this.container.innerHTML = text;

					this.delButton.style.display = "inline-block";
					BX.onCustomEvent(this, "onChange", [this, this.node]);
				},
				makeDate : function(str) {

					//Format: "day.month.year hour:minute"
					var d = new Date();
					if (BX.type.isNotEmptyString(str))
					{
						var dateR = new RegExp("(\\d{2}).(\\d{2}).(\\d{4})"),
							timeR = new RegExp("(\\d{1,2}):(\\d{1,2})"),
							m;
						if (dateR.test(str) && (m = dateR.exec(str)) && m)
						{
							d.setDate(m[1]);
							d.setMonth((m[2]-1));
							d.setFullYear(m[3])
						}
						if (timeR.test(str) && (m = timeR.exec(str)) && m)
						{
							d.setHours(m[1]);
							d.setMinutes(m[2]);
							d.setSeconds(0);
						}
					}

					return d;
				},
				getStrDate : function(value) {
					var d = BX.parseDate(value), res = '';
					if (d !== null)
					{
						if (this.type == 'date' || this.type == 'datetime')
						{
							res = BX.util.str_pad_left(d.getDate().toString(), 2, "0") + '.' +
								BX.util.str_pad_left((d.getMonth() + 1).toString(), 2, "0") + '.' +
								d.getFullYear().toString();
						}
						if (this.type == 'datetime')
							res += ' ';
						if (this.type == 'time' || this.type == 'datetime')
						{
							res += BX.util.str_pad_left(d.getHours().toString(), 2, "0") + ':' + d.getMinutes().toString();
						}
					}
					return res;
				},
				init : function(formats) {
					var DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME")),
						DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE")),
						TIME_FORMAT;
					if ((DATETIME_FORMAT.substr(0, DATE_FORMAT.length) == DATE_FORMAT))
						TIME_FORMAT = BX.util.trim(DATETIME_FORMAT.substr(DATE_FORMAT.length));
					else
						TIME_FORMAT = BX.date.convertBitrixFormat(DATETIME_FORMAT.indexOf('T') >= 0 ? 'H:MI:SS T' : 'HH:MI:SS');
					this.format.bitrix.datetime = DATETIME_FORMAT;

					this.format.bitrix.date = DATE_FORMAT;
					this.format.bitrix.time = TIME_FORMAT;

					formats = (formats || {});

					this.format.visible.datetime = (formats["datetime"] || DATETIME_FORMAT.replace(':s', ''));
					this.format.visible.date = (formats["date"] || DATE_FORMAT);
					this.format.visible.time = (formats["time"] || TIME_FORMAT.replace(':s', ''));
					this.format.visible.datetime = [
						["today", "today, " + this.format.visible.time],
						["tommorow", "tommorow, " + this.format.visible.time ],
						["yesterday", "yesterday, " + this.format.visible.time],
						["" , this.format.visible.datetime]
					];
					this.format.visible.date = [
						["today", "today"],
						["tommorow", "tommorow"],
						["yesterday", "yesterday"],
						["" , this.format.visible.date]
					];

					this.delButton = BX(this.node.id + '_del');
					BX.bind(this.delButton, "click", BX.proxy(this.drop, this));
				},
				drop : function(e)
				{
					if (e)
					{
						BX.eventCancelBubble(e);
						BX.PreventDefault(e);
					}
					this.node.value = "";
					this.container.innerHTML = this.container.getAttribute("placeholder");
					this.delButton.style.display = "none";
					BX.onCustomEvent(this, "onChange", [this, this.node]);
					return false;
				}
			};
			return d;
		})(),
		initSelectUser = (function () {
		var d = function(select, eventNode, container) {
			this.click = BX.delegate(this.click, this);
			this.callback = BX.delegate(this.callback, this);
			this.drop = BX.delegate(this.drop, this);
			this.select = BX(select);
			this.eventNode = BX(eventNode);
			this.container = BX(container);
			BX.bind(this.eventNode, "click", this.click);
			this.multiple = select.hasAttribute("multiple");
			this.showDrop = !(select.hasAttribute("bx-can-drop") && select.getAttribute("bx-can-drop").toString() == "false");
			this.urls = {
				"list" : BX.message('SITE_DIR') + 'mobile/index.php?mobile_action=get_user_list',
				"profile" : BX.message("interface_form_user_url")
			};
			this.actualizeNodes();
			this.expand = BX("expand_" + this.select.getAttribute("id"));
			this.visCount = BX("count_" + this.select.getAttribute("id"));

			if (!this.container.parentNode.hasAttribute("bx-fastclick-bound"))
			{
				this.container.parentNode.setAttribute("bx-fastclick-bound", "Y");
				FastClick.attach(this.container.parentNode.parentNode);
			}
		};
			d.prototype = {
				multiple : false,
				select : null,
				eventNode : null,
				container : null,
				showDrop : true,
				showMenu : false,
				click : function(e) {
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
					(new BXMobileApp.UI.Table({
						url: this.urls.list,
						table_settings : {
							callback: this.callback,
							markmode: true,
							multiple: this.multiple,
							return_full_mode: true,
							skipSpecialChars : true,
							modal: true,
							alphabet_index: true,
							outsection: false,
							okname: BX.message("interface_form_select"),
							cancelname: BX.message("interface_form_cancel")
						}
					}, "users")).show();
				},
				drop : function() {
					var node = BX.proxy_context,
						id = node.id.replace(this.select.id + '_del_', '');
					for (var ii = 0;  ii < this.select.options.length; ii++)
					{
						if ((this.select.options[ii].value + '') == (id + ''))
						{
							BX.remove(BX.findParent(node, {"tagName" : "DIV", "className" : "mobile-grid-field-select-user-item"}));
							BX.remove(this.select.options[ii]);
						}
					}
					if (this.select.options.length <= 0 && !this.multiple)
						this.eventNode.innerHTML = BX.message('interface_form_select');
					if (this.expand)
						this.expand.value = this.select.options.length;
					if (this.visCount)
						this.visCount.innerHTML = this.select.options.length-3;
					BX.onCustomEvent(this, "onChange", [this, this.select]);
				},
				actualizeNodes : function() {
					if (this.expand)
						this.expand.value = this.select.options.length;
					if (this.visCount)
						this.visCount.innerHTML = this.select.options.length-3;
					for (var ii = 0;  ii < this.select.options.length; ii++)
					{
						if (BX(this.select.id + '_del_' + this.select.options[ii].value))
						{
							BX.bind(BX(this.select.id + '_del_' + this.select.options[ii].value), "click", this.drop);
						}
					}
				},
				buildNodes : function(items) {
					var options = '',
						html = '',
						ii, c = 0,
						user, existedUsers = [];
					for (ii = 0; ii < this.select.options.length; ii++)
					{
						existedUsers.push(this.select.options[ii].value.toString());
						c++;
					}
					for (ii = 0; ii < Math.min((this.multiple ? items.length : 1), items.length); ii++)
					{
						user = items[ii];
						if (BX.util.in_array(user["ID"], existedUsers))
							continue;
						options += '<option value="' + user["ID"] + '" selected>' + user["NAME"] + '</option>';
						html += ([
							'<div class="mobile-grid-field-select-user-item-outer">',
								'<div class="mobile-grid-field-select-user-item">',
									(this.showDrop ? '<del id="' + this.select.id + '_del_' + user["ID"] + '"></del>' : ''),
									'<div class="avatar"', (user["IMAGE"] ? ' style="background-image:url(\'' + user["IMAGE"] + '\')"' : ''), '></div>',
									'<span onclick="BXMobileApp.PageManager.loadPageBlank({url: \'' +  this.urls.profile.replace("#ID#", user["ID"]) + '\',bx24ModernStyle : true});">' + user["NAME"] + '</span>',
								'</div>',
							'</div>'
						].join('').replace(' style="background-image:url(\'\')"', ''));
						c++;
					}
					if (this.expand)
						this.expand.value = c;
					if (this.visCount)
						this.visCount.innerHTML = c-3;
					if (html != '')
					{
						this.select.innerHTML = (this.multiple ? this.select.innerHTML : '') + options;
						this.container.innerHTML = (this.multiple ? this.container.innerHTML : '') + html;
						if (this.select.innerHTML != '' && !this.multiple)
							this.eventNode.innerHTML = BX.message('interface_form_change');

						BX.onCustomEvent(this, "onChange", [this, this.select]);
						var ij = 0,
							f = BX.proxy(function() {
							if (ij < 100)
							{
								if (this.container.childNodes.length > 0)
									this.actualizeNodes();
								else if (ij++)
									setTimeout(f, 50);
							}
						}, this);
						setTimeout(f, 50);
					}
				},
				callback : function(data) {
					if (data && data.a_users)
						this.buildNodes(data.a_users);
				}
			};
			return d;
		})(),
		initSelectGroup = (function () {
			var d = function(select, eventNode, container) {
				initSelectGroup.superclass.constructor.apply(this, arguments);
				this.urls = {
					list : BX.message('SITE_DIR') + 'mobile/index.php?mobile_action=get_group_list',
					profile : BX.message("interface_form_group_url")
				};
			};
			BX.extend(d, initSelectUser);
			d.prototype.callback = function(data) {
				if (data && data.b_groups)
					this.buildNodes(data.b_groups);
			};
			return d;
		})(),
		initText = (function () {
			var d = function(node, container) {
				this.node = node;
				this.container = container;
				this.click = BX.delegate(this.click, this);
				this.callback = BX.delegate(this.callback, this);
				BX.bind(this.container, "click", this.click);
			};
			d.prototype = {
				click : function(e) {
					this.show();
					return BX.PreventDefault(e);
				},
				show : function() {
						window.app.exec('showPostForm', {
						attachButton : { items : []},
						attachFileSettings: {},
						attachedFiles : [],
						extraData: {},
						mentionButton: {},
						smileButton: {},
						message : { text : BX.util.htmlspecialcharsback(this.node.value) },
						okButton: {
							callback: this.callback,
							name: BX.message("interface_form_save")
						},
						cancelButton : {
							callback : function(){},
							name : BX.message("interface_form_cancel")
						}
					});
				},
				callback: function(data) {
					data.text = (BX.util.htmlspecialchars(data.text) || '');
					this.node.value = data.text;
					if (data.text == '')
						this.container.innerHTML = '<span class="placeholder">' + this.node.getAttribute("placeholder") + "</span>";
					else
						this.container.innerHTML = data.text;
					BX.onCustomEvent(this, "onChange", [this, this.node]);
				}
			};
			return d;
		})(),
		initBox = (function () {
			var d = function(node) {
				this.node = node;
				var label = BX.findParent(this.node, {tagName : "LABEL"});
				if (label && label.parentNode && !label.parentNode.hasAttribute("bx-fastclick-bound"))
				{
					label.parentNode.setAttribute("bx-fastclick-bound", "Y");
					FastClick.attach(label.parentNode);
				}

				BX.bind(this.node, "change", BX.delegate(this.change, this));
			};
			d.prototype = {
				change : function() {
					BX.onCustomEvent(this, "onChange", [this, this.node]);
				}
			};
			return d;
		})(),
		initFile = (function () {
			var d = function (node) {
				this.dialogName = "FileDialog";
				this.node = node;

				this.id = this.node.getAttribute("id");

				this.controlName = this.node.getAttribute("data-bx-name");
				this.container = BX('file-placeholder-' + this.id);
				this.uploadParams = {
					uploadMethod : BX.type.isNotEmptyString(this.node.getAttribute("data-bx-url")) ? "immediate" : "deferred",
					uploadFileUrl : this.node.getAttribute("data-bx-url"),
					allowUpload : this.node.getAttribute("data-bx-type") == "image" ? "I" : "A",
					allowUploadExt : this.node.getAttribute("data-bx-extension"),
					maxCount : this.node.getAttribute("data-bx-max")
				};

				this.button = BX('file-eventnode-' + this.id);
				if (this.button)
					BX.bind(this.button, "click", BX.proxy(this.click, this));

				this.agent = BX.Uploader.getInstance({
					id : this.id,
					streams : 1,
					allowUpload : this.uploadParams["allowUpload"], //("I" || "A")
					allowUploadExt : this.uploadParams["allowUploadExt"],
					uploadFormData : "N",
					uploadMethod : this.uploadParams["uploadMethod"],
					uploadFileUrl : this.uploadParams["uploadFileUrl"],
					showImage : true,
					sortItems : false,
					input : BX(this.id + "_file"),
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
							template : BX.message('FILE_NODE')
						},
						preview : {
							params : {
								width: 212,
								height: 119
							}
						}
					}
				});
				this.init();
				return this;
			};

			d.prototype = {
				click : function(e) {
					if (BX.hasClass(this.button, "disabled"))
						BX.DoNothing();
					else if (!window["BXMobileAppContext"])
						return true;
					else
						this.show();
					return BX.PreventDefault(e);
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
									destinationType: window["Camera"]["DestinationType"]["DATA_URL"],
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
									destinationType: window["Camera"]["DestinationType"]["DATA_URL"],
									callback: this.handleAppFile
								});
							}, this)
						}
					];
					(new window.BXMobileApp.UI.ActionSheet( { buttons: buttons }, "textPanelSheet" )).show();
				},
				handleAppFile : function(image) {
					var dataBlob = BX.UploaderUtils.dataURLToBlob("data:image/jpg;base64,"+image);
					dataBlob.name = 'mobile_'+BX.date.format("Ymd_His")+'.jpg';
					(this.agent && this.agent.onChange([dataBlob]));
				},

				init : function() {
					this.handleAppFile = BX.delegate(this.handleAppFile, this);

					this._onFileIsBound = BX.delegate(this.onFileIsBound, this);
					this._onFileIsAppended = BX.delegate(this.onFileIsAppended, this);
					this._onUploadStart = BX.delegate(this.onUploadStart, this);
					this._onUploadProgress = BX.delegate(this.onUploadProgress, this);
					this._onUploadDone = BX.delegate(this.onUploadDone, this);
					this._onUploadError = BX.delegate(this.onUploadError, this);

					BX.addCustomEvent(this.agent, "onFileIsCreated", BX.delegate(this.onFileIsCreated, this));

					var values = BX.findChildren(this.container, {tagName : "DIV"}, false);
					if (values.length > 0)
					{
						var ar1 = [], ar2 = [], name;
						for (var ii = 0; ii < values.length; ii++)
						{
							name = BX.findChild(values[ii], {'className' : 'mobile-grid-field-file-name'}, true);
							if (BX(name))
							{
								ar1.push({
									name : name.innerHTML,
									id : values[ii].getAttribute("id").replace("file-", "")
								});
								ar2.push(values[ii]);
							}
						}
						this.agent.onAttach(ar1, ar2);
					}
					if (this.uploadParams['maxCount'] > 0)
					{
						BX.addCustomEvent(this.agent, "onAttachFiles", BX.delegate(this.onAttachFiles, this));
						BX.addCustomEvent(this.agent, "onQueueIsChanged", BX.delegate(this.onQueueIsChanged, this));
						this.onQueueIsChanged();
					}
				},
				onQueueIsChanged : function()
				{
					if (1 < this.uploadParams["maxCount"] && this.uploadParams["maxCount"] <= this.agent.getItems().length ||
						1 == this.uploadParams["maxCount"] && this.uploadParams["maxCount"] < this.agent.getItems().length)
					{
						BX.addClass(this.button, "disabled");
					}
					else
					{
						BX.removeClass(this.button, "disabled");
					}
				},
				onAttachFiles : function(files)
				{
					var error = false;
					if(files)
					{
						if (this.uploadParams["maxCount"] == 1 && files.length > 0)
						{
							while (this.agent.getItems().length > 0)
								this.deleteFile(this.agent.getItems().getFirst(), true);
							while (files.length > 1)
								files.pop();
						}
						var acceptableL = this.uploadParams['maxCount'] - this.agent.getItems().length;
						acceptableL = (acceptableL > 0 ? acceptableL : 0);
						while (files.length > acceptableL)
						{
							files.pop();
							error = true;
						}
					}
					if (error)
					{
						BX.addClass(this.button, "disabled");
						// TODO more appropriate error hint
					}
					return files;
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
					if (this.agent.params["uploadMethod"] != "immediate")
					{
						var node = this.agent.getItem(item.id);
						node = (node ? node.node : node);
						BX.onCustomEvent(this, "onChange", [this, this.node, {
							action: "add",
							file: item.file,
							node: node,
							item: item
						}]);
					}
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
					item.file = { id : file["id"], name : file["name"] };
					var n = BX.findChildByClassName(node, 'mobile-grid-field-file-name', true);
					if (n)
						n.innerHTML = file["name"];

					var inp = BX.create('INPUT', {attrs : {type : "hidden", name : this.controlName, value : file["id"]}});
					node.appendChild(inp);
					BX.onCustomEvent(this, "onChange", [this, this.node, {
						action : "delete",
						file : item.file,
						node : node,
						item : item
					}]);
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
				},
				deleteFile : function(item) {
					item.deleteFile();
					BX.onCustomEvent(this, "onChange", [this, this.node, {
						action : "delete",
						file : item.file,
						node : null,
						item : item
					}]);

				}
			};
			return d;
		})();
	window.app.exec("enableCaptureKeyboard", true);
	BX.Mobile.Grid.Form = function(params) {
		BXMobileApp.UI.Page.LoadingScreen.hide();
		if (typeof params === "object")
		{
			this.gridId = params["gridId"] || "";
			this.formId = params["formId"] || "";
			if (this.gridId != '')
				repo["gridId"][this.gridId] = this;
			if (this.formId != '')
				repo["formId"][this.formId] = this;
			this.formats = params["formats"] || null;
			var nodes = params["customElements"] || [], node, obj, ff = BX.proxy(function(o, node) {
				var res = [this, node, o];
				for (var i = 2; i < arguments.length; i++)
				{
					res.push(arguments[i]);
				}
				BX.onCustomEvent(this, "onChange", res);
			}, this);
			this.apply = BX.delegate(this.apply, this);
			this.restrictedMode = params["restrictedMode"];

			while ((node = nodes.pop()) && node)
			{
				if ((obj = this.bindElement(BX(node))) && obj)
				{
					this.elements.push(obj);
					if (params["restrictedMode"])
						BX.addCustomEvent(obj, "onChange", this.apply);
					BX.addCustomEvent(obj, "onChange", ff);
				}
			}
			if (BX(this.formId) && BX('submit_' + this.formId))
			{
				BX.bind(BX('submit_' + this.formId), "click", BX.delegate(this.click, this));
				BX.bind(BX('cancel_' + this.formId), "click", BX.delegate(this.cancel, this));
			}
			else if (params["buttons"] == "app")
			{
				window.BXMobileApp.UI.Page.TopBar.updateButtons({
					cancel: {
						type: "back_text", // @param buttons.type The type of the button (plus|back|refresh|right_text|back_text|users|cart)
						callback: BX.delegate(this.cancel, this),
						name: BX.message("interface_form_cancel"),
						bar_type: "navbar", //("toolbar"|"navbar")
						position: "left"//("left"|"right")
					},
					ok: {
						type: "back_text", // @param buttons.type The type of the button (plus|back|refresh|right_text|back_text|users|cart)
						callback: BX.delegate(this.click, this),
						name: BX.message("interface_form_save"),
						bar_type: "navbar", //("toolbar"|"navbar")
						position: "right"//("left"|"right")
					}
				});
			}
			if (BX('buttons_' + this.formId))
			{
				var formId = this.formId;
				BX.addCustomEvent("onKeyboardWillShow", function() { BX.addClass(BX('buttons_' + formId), "mobile-grid-button-panel-regular"); });
				BX.addCustomEvent("onKeyboardDidHide", function() { BX.removeClass(BX('buttons_' + formId), "mobile-grid-button-panel-regular"); });
			}
			BX.onCustomEvent(window, "onInitialized", [this.formId, this.gridId, this]);
		}
	};
	BX.Mobile.Grid.Form.prototype = {
		elements : [],
		bindElement : function(node) {
			var result = null;
			if (BX(node))
			{
				var tag = node.tagName.toLowerCase(),
					type = (node.hasAttribute("data-bx-type") ? node.getAttribute("data-bx-type").toLowerCase() : "");

				if (tag == 'select' && node.getAttribute("data-bx-type") == 'select-user')
				{
					result = new initSelectUser(node, BX(node.id + '_select'), BX(node.id + '_target'));
				}
				else if (tag == 'select' && node.getAttribute("data-bx-type") == 'select-group')
				{
					result = new initSelectGroup(node, BX(node.id + '_select'), BX(node.id + '_target'));
				}
				else if (tag == 'select')
				{
					result = new initSelect(node, BX(node.id + '_select'), (node.hasAttribute("multiple") ? BX(node.id + '_target') : BX(node.id + '_select')));
				}
				else if (node.getAttribute("type") == "text")
				{
					BX.bind(node, "keyup", function(e) {
						e = (e||window.event);
						if (e && e.keyCode == 13)
						{
							var ii, found = false;
							BX.eventCancelBubble(e);
							for (ii = 0; ii < node.form.elements.length; ii++)
							{
								if (found)
								{
									if (node.form.elements[ii].tagName.toLowerCase() == 'textarea' || node.form.elements[ii].tagName.toLowerCase() == 'input' && node.form.elements[ii].getAttribute("type").toLowerCase() == "text")
									{
										BX.focus(node.form.elements[ii]);
									}
									break;
								}
								found = (node.form.elements[ii] == node);
							}
						}
					});
				}
				else if (tag == 'textarea')
				{

				}
				else if (node.getAttribute("type") == "checkbox" || node.getAttribute("type") == "radio")
				{
					result = new initBox(node);
				}
				else if (type == 'text' || type == 'textarea')
				{
					result = new initText(node, BX(node.id + '_target'));
				}
				else if (type == 'date' || type == 'datetime' || type == 'time')
				{
					result = new initDatetime(node, type, BX(node.id + '_container'), this.format);
				}
				else if (type == 'disk_file')
				{
					result = BX.Disk.UFMobile.getByName(node.value);
				}
				else if (type == 'diskview_file')
				{
					result = BX.Disk.UFMobile.getByName(node.value);
				}
				else if (type == "file" || type == "image")
				{
					result = new initFile(node);
				}
			}
			return result;
		},
		cancel : function(e){
			if (e)
				BX.PreventDefault(e);
			BX.onCustomEvent(this, 'onCancel', [this, BX(this.formId)]);
			return false;
		},
		click : function(e){
			if (e)
				BX.PreventDefault(e);
			this.save();
			return false;
		},
		apply: function(obj, input, file) {
			var res = {submit : true};
			BX.onCustomEvent(this, 'onSubmitForm', [this, BX(this.formId), input, res]);
			window.BXMobileApp.onCustomEvent('onSubmitForm', [this.gridId, this.formId, (input ? input.id : null)], true);

			if (res.submit !== false)
			{
				if (obj.dialogName === "FileDialog" && file && file["action"] === "add")
				{
					BX.addCustomEvent(this, "onBeforeSubmitAjax", function(dm, options){
						options["data"] = (options["data"] || {});
						options["data"][obj.controlName] = file.file;
					});
				}
				this.submit(true);
			}
		},
		save: function() {
			var res = {submit : true};
			BX.onCustomEvent(this, 'onSubmitForm', [this, BX(this.formId), null, res]);
			window.BXMobileApp.onCustomEvent('onSubmitForm', [this.gridId, this.formId, null], true);
			if (res.submit !== false)
				this.submit(false);
		},
		submit : function(ajax) {
			if (!BX(this.formId))
				return;
			var options = {
				restricted : "Y",
				method : BX(this.formId).getAttribute("method"),
				onsuccess : BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitAjaxSuccess", [this, arguments[0]]);
				}, this),
				onfailure : BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitAjaxFailure", [this, arguments[0]]);
				}, this),
				onprogress : BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitAjaxProgress", [this, arguments]);
				}, this)
			};

			if (ajax)
			{
				BX.onCustomEvent(this, "onBeforeSubmitAjax", [this, options]);
			}
			else
			{
				options["restricted"] = "N";
				options["onsuccess"] = BX.proxy(function() {
					BXMobileApp.UI.Page.LoadingScreen.hide();
					BX.onCustomEvent(this, "onSubmitFormSuccess", [this, arguments[0]]);
				}, this);
				options["onfailure"] = BX.proxy(function() {
					BXMobileApp.UI.Page.LoadingScreen.hide();
					BX.onCustomEvent(this, "onSubmitFormFailure", [this, arguments[0]]);
				}, this);
				options["onprogress"] = BX.proxy(function() {
					BX.onCustomEvent(this, "onSubmitFormProgress", [this, arguments]);
				}, this);
				BX.onCustomEvent(this, "onBeforeSubmitForm", [this, options]);
				BXMobileApp.UI.Page.LoadingScreen.show();
			}
			var save = BX(this.formId).elements["save"];
			if (!BX(save))
			{
				save = BX.create("INPUT", {attrs : {type : "hidden", name : "save"}});
				BX(this.formId).appendChild(save);
			}
			save.value = "Y";
			BX.ajax.submitAjax(BX(this.formId), options);
		}
	};
	BX.Mobile.Grid.Form.getByFormId = function(id) { return repo["formId"][id]; };
	BX.Mobile.Grid.Form.getByGridId = function(id) { return repo["gridId"][id]; };
}());