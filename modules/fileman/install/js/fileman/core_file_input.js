;(function() {
var BX = window.BX;
if (BX.file_input)
	return;

BX.file_input = function(arConfig)
{
	this.arConfig = arConfig;
	BX.ready(BX.proxy(this.Init, this));
};

BX.file_input.prototype.Init = function()
{
	this.id = this.arConfig.id;
	this.multiple = this.arConfig.multiple;
	this.maxCount = parseInt(this.arConfig.maxCount) || 0;
	this.inputSize = this.arConfig.inputSize || 50;
	this.fileCount = 0; // Count of all files (existant and new)
	this.newFileCount = 0; // Count of new files

	this.pCont = BX(this.id + '_cont');

	if (!this.pCont)
	{
		setTimeout(BX.proxy(this.Init, this), 100);
		return;
	}

	if (this.arConfig.useMedialib || this.arConfig.useFileDialog)
	{
		this.arConfig.menuExist.push({TEXT: BX.message('ADM_FILE_INSERT_PATH'), ONCLICK: BX.proxy(this.ShowPath, this)});
		this.arConfig.menuNew.push({TEXT: BX.message('ADM_FILE_INSERT_PATH'), ONCLICK: BX.proxy(this.ShowPath, this)});
	}

	if (this.multiple)
		this.pCont.style.marginBottom = '14px';

	this.pNewMenu = BX(this.id + '_menu_new');
	if (this.pNewMenu)
		this.SetOpenerMenu(this.pNewMenu, this.arConfig.menuNew);


	// We have already saved files and we have to append for each file the menu
	if (this.arConfig.fileExists && this.arConfig.files)
	{
		for (var i = 0; i < this.arConfig.files.length; i++)
			this.DisplayExistFile(i);
	}

	this.fileCount = this.arConfig.files.length;

	this.oFiles = [];
	this.oNewFile = this.DisplayFileBlock();
	this.oNewFile.pTextInput.id = this.id + '_text_input';

	if (!this.arConfig.useUpload)
	{
		if (this.multiple)
		{
			this.SetInputName(this.oNewFile.pTextInput, this.GetInputName('file_dialog'));
			this.ShowFileDescription(this.oNewFile);
			this.PushToFiles(this.oNewFile);
		}
		else
		{
			this.SetInputName(this.oNewFile.pTextInput, this.GetInputName('first_input'));
			this.ShowFileDescription(this.oNewFile);
		}
	}
	else if(!this.arConfig.fileExists && this.arConfig.useUpload)
	{
		this.oNewFile.pFileCont.appendChild(BX.create('INPUT', {props: {type: "file", name: this.GetInputName('first_input'), className: 'adm-designed-file adm-input-file-none', id: this.id + '_file_hidden_value_' + 0}}));
	}

	this.pCont.appendChild(this.oNewFile.pFileCont);

	if (this.arConfig.viewMode || (this.arConfig.fileExists && !this.CheckNewFileState()))
		this.HideNewFileState();

	// Hack for correct displaying in admin forms
	if (!this.arConfig.fileExists)
		BX.addClass(this.pCont, 'adm-input-file-top-shift');

	var _this = this;
	if (this.arConfig.useMedialib)
		window['SetValueFromMedialib' + this.id] = function(oFile){_this.OnSelectFromMedialib(oFile);};

	if (this.arConfig.useFileDialog)
		window['SetValueFromFileDialog' + this.id] = function(filename, path, site, title, menu)
		{
			_this.OnSelectFromFileDialog(path + (path == '/'? '': '/') + filename);
		};

	if (this.arConfig.useCloud)
		window['OpenCloudDialog' + this.id] = function()
		{
			_this.oNewFile.pTextInput.onchange = BX.proxy(_this.OnCloudInputChange, _this);
			BX.util.popup(_this.arConfig.cloudDialogPath + _this.oNewFile.pTextInput.id, 710, 600);
		}
};

BX.file_input.prototype.DisplayExistFile = function(i)
{
	var file = this.arConfig.files[i];

	if (file.FILE_NOT_FOUND)
	{
		var pMenu = BX(this.id + '_menu_' + i);
		if (pMenu && this.arConfig.showDel)
		{
			this.SetOpenerMenu(pMenu, [{TEXT: BX.message('ADM_FILE_DELETE'), ONCLICK: BX.proxy(this.DeleteFile, this), GLOBAL_ICON: 'adm-menu-delete'}]);
		}
	}
	else
	{
		if (!this.arConfig.viewMode)
		{
			var pMenu = BX(this.id + '_menu_' + i);
			if (pMenu)
			{
				//var arMenu = this.multiple ? [] : BX.clone(this.arConfig.menuExist);
				var arMenu = BX.clone(this.arConfig.menuExist);
				if(this.arConfig.showDel || this.arConfig.showDesc)
					arMenu.push({SEPARATOR: true});
				if(this.arConfig.showDel)
					arMenu.push({TEXT: BX.message('ADM_FILE_DELETE'), ONCLICK: BX.proxy(this.DeleteFile, this), GLOBAL_ICON: 'adm-menu-delete'});
				if (this.arConfig.showDesc && file.DESCRIPTION == "")
					arMenu.push({TEXT: BX.message('ADM_FILE_ADD_DESC'), ONCLICK: BX.proxy(this.AddDescription, this), GLOBAL_ICON: 'adm-menu-add-desc'});

				this.SetOpenerMenu(pMenu, arMenu);

				if (!file.IS_IMAGE)
					pMenu.style.top = '-6px';
			}

			var fileContainer = BX(this.id + '_file_cont_' + i);
			if (fileContainer)
			{
				var
					inpName = file.INPUT_NAME || this.GetInputName('first_input'),
					inp;

				if (this.arConfig.useUpload) // hack for iblock forms (for editing description)
					inp = BX.create('INPUT', {props: {type: "file", name: inpName, className: 'adm-designed-file adm-input-file-none'}});
				else
					inp = BX.create('INPUT', {props: {type: "hidden", value: file.PATH || file.SRC, name: inpName}});

				inp.id = this.id + '_file_hidden_value_' + i;
				fileContainer.appendChild(inp);
			}
		}

		if (file.IS_IMAGE)
		{
			var
				pSpan = BX(this.id + '_file_disp_' + i),
				pImg = BX.findChild(pSpan, {tag: "IMG"}, true);

			if (pImg)
			{
				var
					pNode = pImg,
					h = parseInt(pImg.getAttribute('height') || pImg.offsetHeight),
					w = parseInt(pImg.getAttribute('width') || pImg.offsetWidth);

				if (this.arConfig.minPreviewHeight > h || this.arConfig.minPreviewWidth > w)
				{
					if (this.arConfig.minPreviewHeight < h)
						pSpan.style.height = (h + 4) + 'px';
					if (this.arConfig.minPreviewWidth < w)
						pSpan.style.width = (w + 4) + 'px';

					if (pNode.parentNode.tagName.toLowerCase() == 'a')
						pNode = pNode.parentNode;

					BX.addClass(pSpan, 'adm-input-file-bordered');
					pNode.style.position = 'absolute';
					pNode.style.top = '50%';
					pNode.style.left = '50%';
					pNode.style.marginTop = Math.round(- h / 2) + 'px';
					pNode.style.marginLeft = Math.round(- w / 2) + 'px';
				}
			}
		}
	}
};

BX.file_input.prototype.SetOpenerMenu = function(pMenu, arMenu)
{
	var _this = this;
	if (!!pMenu.OPENER)
		return true;
	pMenu.OPENER = new BX.COpener({
		//CLOSE_ON_CLICK: false,
		DIV: pMenu,
		TYPE: 'click',
		MENU: arMenu,
		ACTIVE_CLASS: 'adm-btn-active'
	});

	BX.addCustomEvent(pMenu.OPENER, 'onOpenerMenuOpen', function()
	{
		_this.SetCurrentFile(pMenu.getAttribute("data-bx-meta"));

		var i, items = pMenu.OPENER.GetMenu().ITEMS;
		for (i in items)
		{
			if (items[i] && items[i].ID == "upload" && BX.isNodeInDom(items[i].NODE))
			{
				_this.oNewFile.pUploadInput.setAttribute("data-bx-moved", true);
				items[i].NODE.appendChild(_this.oNewFile.pUploadInput);
				BX.addClass(items[i].NODE, "adm-input-file-ext-class");
				break;
			}
		}
	});

	BX.addCustomEvent(pMenu.OPENER, 'onOpenerMenuClose', function()
	{
		var i, items = pMenu.OPENER.GetMenu().ITEMS;
		for (i in items)
		{
			if (items[i].ID == "upload" && BX.isNodeInDom(items[i].NODE))
			{
				BX.removeClass(items[i].NODE, "adm-input-file-ext-class");
				BX.defer(function(){
					if (_this.oNewFile.pUploadInput.getAttribute("data-bx-moved"))
					{
						_this.oNewFile.pUploadInput.removeAttribute("data-bx-moved");
						_this.oNewFile.pUploadCont.appendChild(_this.oNewFile.pUploadInput);
					}
				})();
				break;
			}
		}
	});
};

BX.file_input.prototype.AddNewFileBlock = function()
{
	var arMenu = [];
	if (!this.multiple)
	{
		arMenu = BX.clone(this.arConfig.menuNew);
		arMenu.push({SEPARATOR: true});
	}
	arMenu.push({TEXT: BX.message('ADM_FILE_CLEAR'), ONCLICK: BX.proxy(this.ClearNewFile, this), GLOBAL_ICON: 'adm-menu-delete'});

	var oFile = this.DisplayFileBlock(arMenu);
	this.pCont.appendChild(oFile.pFileCont);

	if (this.multiple)
		BX.addClass(oFile.pFileCont, 'adm-input-cont-bordered');
	return oFile;
};

BX.file_input.prototype.OnUploadInputChange = function(e)
{
	var
		p, name,
		curFile,
		inp = e.target || e.srcElement,
		description = '',
		value = inp.files || [inp.value];

	value = value[0];
	name = value.name || value;
	p = Math.max(name.lastIndexOf('/'), name.lastIndexOf('\\'));
	if (p > 0)
		name = name.substring(p + 1, name.length);

	this.curFileIndex = false;

	if (this.arConfig.fileExists && this.arConfig.files && !this.multiple) // Works only for one file
	{
		this.DeleteFile(false);

		this.oNewFile.pTextInput.style.display = "none"; // Hide text input if it was displayed
		this.oNewFile.pUploadLabel.innerHTML = BX.util.htmlspecialchars(name); // Set correct file name
		this.oNewFile.pUploadCont.style.display = ""; // Show file control
		this.SetInputName(this.oNewFile.pUploadInput, this.GetInputName('upload'));

		this.SetHiddenInputName(0, '');

		var pDelInput = BX.findChild(BX(this.id + '_file_cont_0'), {tagName: 'input', attr: {name: this.GetInputName('del')}});
		if (pDelInput)
			BX.cleanNode(pDelInput, true);

		// Show description
		curFile = this.arConfig.files && this.arConfig.files[0] ? this.arConfig.files[0] : false;

		if (curFile && curFile.DESCRIPTION !== '')
			description = curFile.DESCRIPTION;
		this.ShowFileDescription(this.oNewFile, description);
	}
	else
	{
		var oFile;
		if (!this.multiple && this.newFileCount > 0)
		{
			oFile = this.ClearNames(this.oFiles[0]);
		}
		else
		{
			if (this.multiple)
				this.ClearNewFile();

			oFile = this.AddNewFileBlock();
			// Swap inputs
			var _inp = oFile.pUploadInput;
			oFile.pUploadInput = this.oNewFile.pUploadInput;
			this.oNewFile.pUploadInput = _inp;
			// Set inputs to it corresponding containers
			this.oNewFile.pUploadCont.appendChild(this.oNewFile.pUploadInput);
			oFile.pUploadCont.appendChild(oFile.pUploadInput);
		}

		oFile.pTextInput.style.display = "none"; // Hide text input if it was displayed
		oFile.pUploadLabel.innerHTML = BX.util.htmlspecialchars(name); // Set correct file name
		oFile.pUploadCont.style.display = ""; // Show file control

		this.SetInputName(oFile.pUploadInput, this.GetInputName('upload'));

		if (this.multiple)
			this.curFileIndex = this.newFileCount;
		if (this.multiple || !this.newFileCount)
			this.PushToFiles(oFile);

		var fileIndex = parseInt(this.GetCurrentFile(), 10);
		this.SetHiddenInputName(fileIndex, '');

		if (!isNaN(fileIndex) && this.multiple)
		{
			this.DeleteFile(false);
			var pMenu = BX(this.id + '_menu_' + fileIndex);
			pMenu.style.display = "none";
			oFile.pMenu.style.display = "inline-block";
			oFile.pMenu.setAttribute('data-bx-meta', fileIndex);

			curFile = this.arConfig.files && this.arConfig.files[fileIndex] ? this.arConfig.files[fileIndex] : false;
			var fileContainer = BX(this.id + '_file_cont_' + fileIndex);

			if (curFile)
			{
				if (curFile.DESCRIPTION !== '')
					description = curFile.DESCRIPTION;
				this.SetInputName(oFile.pUploadInput, curFile.INPUT_NAME);
				this.SetInputName(oFile.pTextInput, '');
				this.SetInputName(oFile.pDescInput, curFile.DESC_NAME);
			}
			fileContainer.appendChild(oFile.pFileCont);
		}

		// Show description
		this.ShowFileDescription(oFile, description);

		if (this.multiple)
			this.pCont.appendChild(this.oNewFile.pFileCont);

		if (!this.CheckNewFileState())
			this.HideNewFileState();
	}

	// Close all menu
	BX.onCustomEvent("onMenuItemSelected");

	// Used to refresh form content - workaround for IE bug (mantis:37969)
	if (BX.browser.IsIE())
		BX(this.id + '_ie_bogus_container').innerHTML = BX(this.id + '_ie_bogus_container').innerHTML;

	// mantis: 78822
	if (BX.browser.IsIE() || BX.browser.IsIE11())
	{
		var fakeInp = this.oNewFile.pFileCont.parentNode.appendChild(BX.create('INPUT', {
			props: {
				type: 'text',
				className: 'adm-input-fake-inp'
			}
		}));
		fakeInp.focus();
		fakeInp.select();
	}
};

BX.file_input.prototype.OnSelectFromMedialib = function(file)
{
	var description = '';
	this.curFileIndex = false;
	if (this.arConfig.fileExists && this.arConfig.files && !this.multiple)
	{
		this.DeleteFile(false);

		this.oNewFile.pTextInput.value = file.src || '';
		this.oNewFile.pTextInput.style.display = "";
		this.oNewFile.pUploadCont.style.display = "none";
		this.SetInputName(this.oNewFile.pTextInput, this.GetInputName('medialib'));
		this.FocusInput(this.oNewFile.pTextInput);

		this.oNewFile.pDescInput.value = file.description || ''; // set description from medialibrary
		this.SetHiddenInputName(0, '');

		var pDelInput = BX.findChild(BX(this.id + '_file_cont_0'), {tagName: 'input', attr: {name: this.GetInputName('del')}});
		if (pDelInput)
			BX.cleanNode(pDelInput, true);

		// Desc
		description = file.description || '';
		var curFile = this.arConfig.files && this.arConfig.files[0] ? this.arConfig.files[0] : false;
		if (curFile && curFile.DESCRIPTION !== '')
			description = curFile.DESCRIPTION;

		// Show description
		this.ShowFileDescription(this.oNewFile, description);
	}
	else
	{
		var oFile;
		if (!this.multiple && this.newFileCount > 0)
			oFile = this.ClearNames(this.oFiles[0]);
		else
			oFile = this.AddNewFileBlock();

		oFile.pTextInput.value = file.src || '';
		oFile.pTextInput.style.display = "";
		oFile.pUploadCont.style.display = "none";
		this.SetInputName(oFile.pTextInput, this.GetInputName('medialib'));
		this.FocusInput(oFile.pTextInput);

		description = file.description || '';
		if (this.multiple)
			this.curFileIndex = this.newFileCount;
		if (this.multiple || !this.newFileCount)
			this.PushToFiles(oFile);

		var fileIndex = parseInt(this.GetCurrentFile(), 10);
		if (isNaN(fileIndex) && !this.multiple)
			this.SetHiddenInputName(0, '');
		else
			this.SetHiddenInputName(fileIndex, '');

		if (!isNaN(fileIndex) && this.multiple)
		{
			this.DeleteFile(false);
			var pMenu = BX(this.id + '_menu_' + fileIndex);
			pMenu.style.display = "none";
			oFile.pMenu.style.display = "inline-block";
			oFile.pMenu.setAttribute('data-bx-meta', fileIndex);

			var
				curFile = this.arConfig.files && this.arConfig.files[fileIndex] ? this.arConfig.files[fileIndex] : false,
				fileContainer = BX(this.id + '_file_cont_' + fileIndex);

			if (curFile)
			{
				if (curFile.DESCRIPTION !== '')
					description = curFile.DESCRIPTION;
				this.SetInputName(oFile.pTextInput, curFile.INPUT_NAME);
				this.SetInputName(oFile.pDescInput, curFile.DESC_NAME);
			}
			fileContainer.appendChild(oFile.pFileCont);
		}

		// Show description
		this.ShowFileDescription(oFile, description);

		if (this.multiple)
			this.pCont.appendChild(this.oNewFile.pFileCont);

		if (!this.CheckNewFileState())
			this.HideNewFileState();
	}
};

BX.file_input.prototype.OnSelectFromFileDialog = function(path)
{
	var description = '', curFile;
	this.curFileIndex = false;
	if (this.arConfig.fileExists && this.arConfig.files && !this.multiple)
	{
		this.DeleteFile(false);

		this.oNewFile.pTextInput.value = path || '';
		this.oNewFile.pTextInput.style.display = "";
		this.oNewFile.pUploadCont.style.display = "none";
		this.SetInputName(this.oNewFile.pTextInput, this.GetInputName('file_dialog'));
		this.FocusInput(this.oNewFile.pTextInput);

		this.SetHiddenInputName(0, '');
		var pDelInput = BX.findChild(BX(this.id + '_file_cont_0'), {tagName: 'input', attr: {name: this.GetInputName('del')}});
		if (pDelInput)
			BX.cleanNode(pDelInput, true);

		// Show description
		curFile = this.arConfig.files && this.arConfig.files[0] ? this.arConfig.files[0] : false;
		if (curFile && curFile.DESCRIPTION !== '')
			description = curFile.DESCRIPTION;
		this.ShowFileDescription(this.oNewFile, description);
	}
	else
	{
		var oFile;
		if (!this.multiple && this.newFileCount > 0)
			oFile = this.ClearNames(this.oFiles[0]);
		else
			oFile = this.AddNewFileBlock();

		oFile.pTextInput.value = path || '';
		oFile.pTextInput.style.display = "";
		oFile.pUploadCont.style.display = "none";
		this.SetInputName(oFile.pTextInput, this.GetInputName('file_dialog'));
		this.FocusInput(oFile.pTextInput);

		if (this.multiple)
			this.curFileIndex = this.newFileCount;
		if (this.multiple || !this.newFileCount)
			this.PushToFiles(oFile);

		var fileIndex = parseInt(this.GetCurrentFile(), 10);
		if (isNaN(fileIndex) && !this.multiple)
			this.SetHiddenInputName(0, '');
		else
			this.SetHiddenInputName(fileIndex, '');

		// Show description
		curFile = false;
		if (!isNaN(fileIndex) && this.multiple)
		{
			this.DeleteFile(false);
			var pMenu = BX(this.id + '_menu_' + fileIndex);
			pMenu.style.display = "none";
			oFile.pMenu.style.display = "inline-block";
			oFile.pMenu.setAttribute('data-bx-meta', fileIndex);
			curFile = this.arConfig.files && this.arConfig.files[fileIndex] ? this.arConfig.files[fileIndex] : false;
			var fileContainer = BX(this.id + '_file_cont_' + fileIndex);

			if (curFile)
			{
				this.ShowFileDescription(oFile, curFile.DESCRIPTION || '');
				this.SetInputName(oFile.pTextInput, curFile.INPUT_NAME);
				this.SetInputName(oFile.pDescInput, curFile.DESC_NAME);
				oFile.pDescInput.value = curFile.DESCRIPTION || '';
			}
			fileContainer.appendChild(oFile.pFileCont);
		}

		if (!curFile)
		{
			this.ShowFileDescription(oFile, '');
		}

		if (this.multiple)
			this.pCont.appendChild(this.oNewFile.pFileCont);

		if (!this.CheckNewFileState())
			this.HideNewFileState();
	}
};


BX.file_input.prototype.OnCloudInputChange = function()
{
	var path = this.oNewFile.pTextInput.value;
	var description = '';
	this.curFileIndex = false;
	if (this.arConfig.fileExists && this.arConfig.files && !this.multiple)
	{
		this.DeleteFile(false);

		this.oNewFile.pTextInput.value = path || '';
		this.oNewFile.pTextInput.style.display = "";
		this.oNewFile.pUploadCont.style.display = "none";
		this.SetInputName(this.oNewFile.pTextInput, this.GetInputName('cloud'));

		this.SetHiddenInputName(0, '');
		var pDelInput = BX.findChild(BX(this.id + '_file_cont_0'), {tagName: 'input', attr: {name: this.GetInputName('del')}});
		if (pDelInput)
			BX.cleanNode(pDelInput, true);

		// Show description
		var curFile = this.arConfig.files && this.arConfig.files[0] ? this.arConfig.files[0] : false;
		if (curFile && curFile.DESCRIPTION !== '')
			description = curFile.DESCRIPTION;
		this.ShowFileDescription(this.oNewFile, description);
	}
	else
	{
		this.oNewFile.pTextInput.value = '';
		var oFile;
		if (!this.multiple && this.newFileCount > 0)
			oFile = this.ClearNames(this.oFiles[0]);
		else
			oFile = this.AddNewFileBlock();

		oFile.pTextInput.value = path || '';
		oFile.pTextInput.style.display = "";
		oFile.pUploadCont.style.display = "none";
		this.SetInputName(oFile.pTextInput, this.GetInputName('cloud'));

		if (this.multiple)
			this.curFileIndex = this.newFileCount;
		if (this.multiple || !this.newFileCount)
			this.PushToFiles(oFile);

		var fileIndex = parseInt(this.GetCurrentFile(), 10);
		if (isNaN(fileIndex) && !this.multiple)
			this.SetHiddenInputName(0, '');
		else
			this.SetHiddenInputName(fileIndex, '');

		if (!isNaN(fileIndex) && this.multiple)
		{
			this.DeleteFile(false);
			var pMenu = BX(this.id + '_menu_' + fileIndex);
			pMenu.style.display = "none";
			oFile.pMenu.style.display = "inline-block";
			oFile.pMenu.setAttribute('data-bx-meta', fileIndex);

			var
				curFile = this.arConfig.files && this.arConfig.files[fileIndex] ? this.arConfig.files[fileIndex] : false,
				fileContainer = BX(this.id + '_file_cont_' + fileIndex);

			if (curFile)
			{
				if (curFile.DESCRIPTION !== '')
					description = curFile.DESCRIPTION;
				this.SetInputName(oFile.pTextInput, curFile.INPUT_NAME);
				this.SetInputName(oFile.pDescInput, curFile.DESC_NAME);
			}
			fileContainer.appendChild(oFile.pFileCont);
		}

		// Show description
		this.ShowFileDescription(oFile, description);

		if (this.multiple)
			this.pCont.appendChild(this.oNewFile.pFileCont);

		if (!this.CheckNewFileState())
			this.HideNewFileState();
	}

	this.oNewFile.pTextInput.onchange = null;
};

BX.file_input.prototype.ShowFileDescription = function(oFile, value)
{
	if (this.arConfig.showDesc)
	{
		oFile.pDesc.style.display = "";
		//oFile.pDescInput.value = '';
		this.SetInputName(oFile.pDescInput, this.GetInputName('desc'));
		BX.removeClass(oFile.pDesc, "adm-input-file-show-desc"); // hide input

		oFile.pDescInput.value = value || '';
		// Show or hide input with description
		if (value)
			BX.addClass(oFile.pDesc, "adm-input-file-show-desc");
		else
			BX.removeClass(oFile.pDesc, "adm-input-file-show-desc");
	}
};

BX.file_input.prototype.DisplayFileBlock = function(menu)
{
	var
		pFileCont = BX.create("DIV", {props: {className: "adm-input-file-new"}}),
		pUploadCont = pFileCont.appendChild(BX.create("SPAN", {props: {className: "adm-input-file"}})),
		pUploadLabel = pUploadCont.appendChild(BX.create("SPAN", {text: BX.message('ADM_FILE_ADD')})),
		pUploadInput = pUploadCont.appendChild(BX.create("INPUT", {props: {type: 'file', className: 'adm-designed-file'}}));

	pFileCont.appendChild(document.createTextNode(' '));
	BX.bind(pUploadInput, 'change', BX.proxy(this.OnUploadInputChange, this));

	var _this = this;
	BX.bind(pUploadInput, 'mousedown', function(){
		var par = BX.findParent(this, {className: 'adm-input-file-new'}, function(el){return (BX.hasClass(el, 'adm-input-file-ext-class') || BX.hasClass(el, 'adm-input-file-control'));});
		if (par)
		{
			var pMenu = BX.findChild(par, {className: 'add-file-popup-btn'});
			if (pMenu && pMenu.getAttribute("data-bx-meta") !== null)
				_this.SetCurrentFile(pMenu.getAttribute("data-bx-meta"));
		}
	});

	var pTextInput = pFileCont.appendChild(BX.create("INPUT", {props: {type: 'text', className: 'adm-input', size: this.inputSize}, style: {display: 'none'}}));
	var pMenu = pFileCont.appendChild(BX.create("SPAN", {props: {className: "adm-btn add-file-popup-btn"}}));
	pMenu.setAttribute('data-bx-meta', 'new');
	this.SetOpenerMenu(pMenu, menu || this.arConfig.menuNew);

	var
		pDesc = pFileCont.appendChild(BX.create("SPAN", {props: {className: "adm-input-file-desc"}, style:{display: 'none'}})),
		pDescLink = pDesc.appendChild(BX.create("SPAN", {props: {className: "adm-input-file-desc-link"}, text: BX.message('ADM_FILE_ADD_DESC')})),
		pDescInput = pDesc.appendChild(BX.create("INPUT", {props: {type: 'text', className: "adm-input", size: this.inputSize, placeholder: BX.message('ADM_FILE_DESC')}}));

	pDescLink.onclick = function(){
		BX.addClass(pDesc, 'adm-input-file-show-desc');
		BX.defer(function(){BX.focus(pDescInput);})();
	};

	if (!this.arConfig.useUpload)
	{
		pUploadCont.style.display = 'none';
		pTextInput.style.display = '';
	}

	return {
		pFileCont: pFileCont,
		pUploadCont: pUploadCont,
		pUploadLabel: pUploadLabel,
		pUploadInput: pUploadInput,
		pTextInput: pTextInput,
		pDesc: pDesc,
		pDescInput: pDescInput,
		pMenu: pMenu
	};
};

BX.file_input.prototype.DeleteFile = function(bAddCancelMenu)
{
	var
		fileIndex = parseInt(this.GetCurrentFile(), 10),
		file = this.arConfig.files && this.arConfig.files[fileIndex] ? this.arConfig.files[fileIndex] : false,
		fileContainer = BX(this.id + '_file_cont_' + fileIndex);

	if (!this.arConfig.useUpload) // Only for files on server props
		this.SetHiddenInputName(fileIndex, '');

	if (fileContainer)
	{
		if (bAddCancelMenu)
			fileContainer.appendChild(BX.create('INPUT', {props: {id: this.id + '_file_del_' + fileIndex, type: "hidden", value: "Y", name: this.GetInputName('del', file)}}));
		BX.addClass(fileContainer, 'adm-input-file-deleted');
	}

	if (this.multiple)
	{
		var pMenu = BX(this.id + '_menu_' + fileIndex);
		pMenu.style.display = "inline-block";
		pMenu.OPENER.SetMenu([{TEXT: BX.message('ADM_FILE_CANCEL_DEL'), ONCLICK: BX.proxy(this.CancelDelete, this)}]);
	}

	var delLabel = BX(this.id + '_file_del_lbl_' + fileIndex);

	if (delLabel)
	{
		delLabel.style.marginLeft = (-parseInt(delLabel.offsetWidth / 2)) + 'px';
		delLabel.style.marginTop = (-parseInt(delLabel.offsetHeight / 2)) + 'px';
	}

	if (this.oNewFile)
	{
		this.oNewFile.pFileCont.style.display = "";

		if (!this.arConfig.useUpload)
			this.SetInputName(this.oNewFile.pTextInput, this.GetInputName('first_input'));

		if (bAddCancelMenu !== false && !this.multiple)
		{
			var arMenu = BX.clone(this.arConfig.menuNew);
			arMenu.push({SEPARATOR: true});
			arMenu.push({TEXT: BX.message('ADM_FILE_CANCEL_DEL'), ONCLICK: BX.proxy(this.CancelDelete, this)});
			this.oNewFile.pMenu.OPENER.SetMenu(arMenu);
		}
	}
};

BX.file_input.prototype.CancelDelete = function()
{
	var fileIndex = parseInt(this.GetCurrentFile(), 10)
	if (isNaN(fileIndex) && !this.multiple)
		fileIndex = 0;

	var
		file = this.arConfig.files && this.arConfig.files[fileIndex] ? this.arConfig.files[fileIndex] : false,
		fileContainer = BX(this.id + '_file_cont_' + fileIndex),
		hiddenInput = BX(this.id + '_file_hidden_value_' + fileIndex);

	if (hiddenInput)
		this.SetInputName(hiddenInput, file.INPUT_NAME || this.GetInputName('first_input'));

	if (fileContainer)
	{
		BX.removeClass(fileContainer, 'adm-input-file-deleted');
		var pDelInput = BX.findChild(fileContainer, {tagName: 'input', attr: {name: this.GetInputName('del', file)}});
		if (pDelInput)
			BX.cleanNode(pDelInput, true);

		if (file.FILE_NOT_FOUND)
		{
			var pMenu = BX(this.id + '_menu_' + fileIndex);
			if (pMenu && this.arConfig.showDel)
			{
				pMenu.OPENER.SetMenu([{TEXT: BX.message('ADM_FILE_DELETE'), ONCLICK: BX.proxy(this.DeleteFile, this), GLOBAL_ICON: 'adm-menu-delete'}]);
			}
		}
		else if (this.multiple)
		{
			var pMenu = BX(this.id + '_menu_' + fileIndex);
			//pMenu.OPENER.SetMenu([{TEXT: BX.message('ADM_FILE_DELETE'), ONCLICK: BX.proxy(this.DeleteFile, this), GLOBAL_ICON: 'adm-menu-delete'}]);
			var arMenu = BX.clone(this.arConfig.menuExist);
			if(this.arConfig.showDel || this.arConfig.showDesc)
				arMenu.push({SEPARATOR: true});
			if(this.arConfig.showDel)
				arMenu.push({TEXT: BX.message('ADM_FILE_DELETE'), ONCLICK: BX.proxy(this.DeleteFile, this), GLOBAL_ICON: 'adm-menu-delete'});
			if (this.arConfig.showDesc && file.DESCRIPTION == "")
				arMenu.push({TEXT: BX.message('ADM_FILE_ADD_DESC'), ONCLICK: BX.proxy(this.AddDescription, this), GLOBAL_ICON: 'adm-menu-add-desc'});

			pMenu.OPENER.SetMenu(arMenu);
		}

		if (this.oNewFile && !this.multiple)
		{
			this.oNewFile.pFileCont.style.display = "none";
			this.SetInputName(this.oNewFile.pUploadInput, "");
			this.SetInputName(this.oNewFile.pTextInput, "");
		}
	}
};

BX.file_input.prototype.AddDescription = function()
{
	var
		fileIndex = parseInt(this.GetCurrentFile(), 10),
		fileDesc = BX(this.id + '_file_desc_' + fileIndex);

	if (fileDesc)
	{
		fileDesc.style.display = "";
		var pInp = BX.findChild(fileDesc, {tag: 'input'});
		if (pInp)
			BX.focus(pInp);
	}
};

BX.file_input.prototype.GetCurrentFile = function()
{
	return this.currentFileId;
};

BX.file_input.prototype.SetCurrentFile = function(ind)
{
	this.currentFileId = ind;
};


BX.file_input.prototype.CheckNewFileState = function()
{
	if (!this.multiple || (this.maxCount > 0 && this.fileCount >= this.maxCount))
		return false;
	return true;
};

BX.file_input.prototype.HideNewFileState = function()
{
	this.oNewFile.pFileCont.style.display = 'none';
	this.ClearNames(this.oNewFile);
};

BX.file_input.prototype.ClearNames = function(oFile)
{
	oFile.pUploadInput.name = '';
	oFile.pTextInput.name = '';
	oFile.pDescInput.name = '';
	oFile.pUploadInput.removeAttribute('name');
	oFile.pTextInput.removeAttribute('name');
	oFile.pDescInput.removeAttribute('name');
	return oFile;
};

BX.file_input.prototype.GetInputName = function(type, file)
{
	var name = '';
	if (this.multiple)
	{
		if (type == 'del' || type == 'desc')
		{
			if (type == 'del')
				name = (file && file.DEL_NAME) ? file.DEL_NAME : this.arConfig.delName;
			else if (type == 'desc')
				name = (file && file.DESC_NAME) ? file.DESC_NAME : this.arConfig.descName;
		}
		else
		{
			name = this.arConfig.inputNameTemplate;
		}

		if (this.curFileIndex === false)
			name = name.replace('#IND#', this.newFileCount);
		else if(this.curFileIndex === undefined)
			name = name.replace('#IND#', 'new');
		else
			name = name.replace('#IND#', this.curFileIndex);
	}
	else
	{
		if (type == 'first_input')
		{
			if (this.arConfig.useUpload)
				type = 'upload';
			if (this.arConfig.useMedialib)
				type = 'medialib';
			else if (this.arConfig.useFileDialog)
				type = 'file_dialog';
			else if (this.arConfig.useCloud)
				type = 'cloud';
		}

		if (this.arConfig.inputs && this.arConfig.inputs[type])
			name = this.arConfig.inputs[type].NAME;
	}

	if (type == 'del' && !name)
		name = this.arConfig.delName;

	if (type == 'desc' && !name)
		name = this.arConfig.descName;

	return name;
};

BX.file_input.prototype.ClearNewFile = function()
{
	var meta = this.GetCurrentFile();

	if (this.multiple)
	{
		this.CancelDelete();

		var
			fileIndex = parseInt(this.GetCurrentFile(), 10),
			fileContainer = BX(this.id + '_file_cont_' + fileIndex);

		if (fileContainer)
		{
			var pNewCont = BX.findChild(fileContainer, {className: 'adm-input-file-new'});
			if (pNewCont)
				BX.cleanNode(pNewCont, true);
			var pMenu = BX(this.id + '_menu_' + fileIndex);
			pMenu.style.display = "inline-block";
		}
	}

	if (!meta || meta.substr(0, 4) != 'new_')
		return;
	var ind = parseInt(meta.substr(4), 10);
	if (isNaN(ind) || !this.oFiles[ind])
		return;

	this.newFileCount--;
	this.fileCount = this.arConfig.files.length + this.newFileCount - 1;

	this.oNewFile.pFileCont.style.display = "";
	this.oFiles[ind].pFileCont.parentNode.removeChild(this.oFiles[ind].pFileCont);
};

BX.file_input.prototype.PushToFiles = function(oFile)
{
	this.oFiles.push(oFile);
	this.newFileCount++;

	this.fileCount = this.arConfig.files.length + this.newFileCount - 1;
	oFile.pMenu.setAttribute('data-bx-meta', 'new_' + (this.oFiles.length - 1));
};

BX.file_input.prototype.SetInputName = function(inp, name)
{
	inp.name = name;
	inp.setAttribute('name', name);
};

BX.file_input.prototype.ShowPath = function()
{
	this.OnSelectFromFileDialog('');
};

BX.file_input.prototype.FocusInput = function(input)
{
	if (input)
		BX.defer(function(){if(input){BX.focus(input);}})();
};

BX.file_input.prototype.SetHiddenInputName = function(ind, value)
{
	if (!isNaN(ind))
	{
		var hiddenInput = BX(this.id + '_file_hidden_value_' + (ind || 0));
		if (hiddenInput)
			this.SetInputName(hiddenInput, value || '');
	}
};
})();
