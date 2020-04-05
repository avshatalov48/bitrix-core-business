/**
 * Bitrix HTML Editor 3.0
 * Date: 24.04.13
 * Time: 4:23
 *
 * Snippets class
 */
(function()
{
function __runsnips()
{
	function BXEditorSnippets(editor)
	{
		this.editor = editor;
		this.listLoaded = false;
		this.snippets = this.editor.config.snippets;
		this.HandleList();
		this.Init();
	}

	BXEditorSnippets.prototype = {
		Init: function()
		{
			BX.addCustomEvent(this.editor, "OnApplySiteTemplate", BX.proxy(this.OnTemplateChanged, this));
		},

		SetSnippets: function(snippets)
		{
			this.snippets =
				this.editor.config.snippets =
					this.editor.snippetsTaskbar.snippets = snippets;
			this.HandleList();
		},

		GetList: function()
		{
			return this.snippets[this.editor.GetTemplateId()];
		},

		HandleList: function()
		{
			var
				i,
				items = this.GetList().items;
			if (items)
			{
				for (i in items)
				{
					if (items.hasOwnProperty(i))
					{
						items[i].key = items[i].path.replace('/', ',');
					}
				}
			}
		},

		ReloadList: function(clearCache)
		{
			this.editor.snippetsTaskbar.ClearSearchResult();
			var _this = this;
			this.editor.Request({
				getData: this.editor.GetReqData('load_snippets_list',
					{
						site_template: this.editor.GetTemplateId(),
						clear_cache: clearCache ? 'Y' : 'N'
					}
				),
				handler: function(res)
				{
					if (res.result)
					{
						_this.SetSnippets(res.snippets);
						_this.RebuildAll();
					}
				}
			});
		},

		FetchPlainListOfCategories: function(list, level, result)
		{
			var i, l = list.length;
			for (i = 0; i < l; i++)
			{
				result.push({
					level: level,
					key: list[i].key,
					section: list[i].section
				});

				if (list[i].children && list[i].children.length > 0)
				{
					this.FetchPlainListOfCategories(list[i].children, level + 1, result);
				}
			}
		},

		AddNewCategory: function(params)
		{
			var _this = this;
			this.editor.Request({
				getData: this.editor.GetReqData('snippet_add_category',
					{
						site_template: this.editor.GetTemplateId(),
						category_name: params.name,
						category_parent: params.parent
					}
				),
				handler: function(res)
				{
					if (res.result)
					{
						_this.SetSnippets(res.snippets);
						_this.RebuildAll();
					}
				}
			});
		},

		RemoveCategory: function(params)
		{
			var _this = this;
			this.editor.Request({
				getData: this.editor.GetReqData('snippet_remove_category',
					{
						site_template: this.editor.GetTemplateId(),
						category_path: params.path
					}
				),
				handler: function(res)
				{
					if (res.result)
					{
						_this.SetSnippets(res.snippets);
						_this.RebuildAll();
					}
				}
			});
		},

		RenameCategory: function(params)
		{
			var _this = this;
			this.editor.Request({
				getData: this.editor.GetReqData('snippet_rename_category',
					{
						site_template: this.editor.GetTemplateId(),
						category_path: params.path,
						category_new_name: params.newName
					}
				),
				handler: function(res)
				{
					if (res.result)
					{
						_this.SetSnippets(res.snippets);
						_this.RebuildAll();
					}
				}
			});
		},

		SaveSnippet: function(params)
		{
			var _this = this;
			this.editor.Request({
				postData: this.editor.GetReqData('edit_snippet',
					{
						site_template: this.editor.GetTemplateId(),
						path: params.path.replace(',', '/'),
						name: params.name,
						code: params.code,
						description: params.description,
						current_path: params.currentPath
					}
				),
				handler: function(res)
				{
					if (res.result)
					{
						_this.SetSnippets(res.snippets);
						_this.RebuildAll();
					}
				}
			});
		},

		RemoveSnippet: function(params)
		{
			var _this = this;
			this.editor.Request({
				getData: this.editor.GetReqData('remove_snippet',
					{
						site_template: this.editor.GetTemplateId(),
						path: params.path.replace(',', '/')
					}
				),
				handler: function(res)
				{
					if (res.result)
					{
						_this.SetSnippets(res.snippets);
						_this.RebuildAll();
					}
				}
			});
		},

		RebuildAll: function()
		{
			var snippetsCategories = this.editor.GetDialog('snippetsCategories');
			if (snippetsCategories && snippetsCategories.IsOpen())
			{
				snippetsCategories.DisplayAddForm(false);
				snippetsCategories.BuildTree(this.GetList().groups);
			}

			// Build structure
			if (this.snippets[this.editor.GetTemplateId()] && this.editor.snippetsTaskbar)
			{
				this.editor.snippetsTaskbar.BuildTree(this.snippets[this.editor.GetTemplateId()].groups, this.snippets[this.editor.GetTemplateId()].items);
			}

			var editSnippet = this.editor.GetDialog('editSnippet');
			if (editSnippet && editSnippet.IsOpen())
			{
				editSnippet.SetCategories();
			}
		},

		OnTemplateChanged: function()
		{
			this.ReloadList(false);
		}
	};

	function SnippetsControl(editor)
	{
		// Call parrent constructor
		SnippetsControl.superclass.constructor.apply(this, arguments);

		this.id = 'snippets';
		this.snippets = this.editor.config.snippets;
		this.templateId = this.editor.templateId;
		this.title = BX.message('BXEdSnippetsTitle');
		this.searchPlaceholder = BX.message('BXEdSnipSearchPlaceHolder');
		this.uniqueId = 'taskbar_' + this.editor.id + '_' + this.id;

		this.Init();
	}

	BX.extend(SnippetsControl, window.BXHtmlEditor.Taskbar);

	SnippetsControl.prototype.Init = function()
	{
		this.BuildSceleton();

		// Build structure
		if (this.snippets[this.templateId])
		{
			this.BuildTree(this.snippets[this.templateId].groups, this.snippets[this.templateId].items);
		}

		var _this = this;
		_this.editor.phpParser.AddBxNode('snippet_icon',
			{
				Parse: function(params)
				{
					return params.code || '';
				}
			}
		);
	};

	SnippetsControl.prototype.GetMenuItems = function()
	{
		var _this = this;

		return [
			{
				text : BX.message('BXEdAddSnippet'),
				title : BX.message('BXEdAddSnippet'),
				className : "",
				onclick: function()
				{
					_this.editor.GetDialog('editSnippet').Show();
					BX.PopupMenu.destroy(_this.uniqueId + "_menu");
				}
			},
			{
				text : BX.message('RefreshTaskbar'),
				title : BX.message('RefreshTaskbar'),
				className : "",
				onclick: function()
				{
					_this.editor.snippets.ReloadList(true);
					BX.PopupMenu.destroy(_this.uniqueId + "_menu");
				}
			},
			{
				text : BX.message('BXEdManageCategories'),
				title : BX.message('BXEdManageCategories'),
				className : "",
				onclick: function()
				{
					_this.editor.GetDialog('snippetsCategories').Show();
					BX.PopupMenu.destroy(_this.uniqueId + "_menu");
				}
			}
		];
	};

	SnippetsControl.prototype.HandleElementEx = function(wrap, dd, params)
	{
		this.editor.SetBxTag(dd, {tag: "snippet_icon", params: params});
		wrap.title = params.description || params.title;

		var editBut = wrap.appendChild(BX.create("SPAN", {props: {className: "bxhtmled-right-side-item-edit-btn", title: BX.message('BXEdSnipEdit')}}));
		this.editor.SetBxTag(editBut, {tag: "_snippet", params: params});

		BX.bind(editBut, 'mousedown', BX.proxy(this.EditSnippet, this));
	};

	SnippetsControl.prototype.EditSnippet = function(e)
	{
		var target = e.target || e.srcElement;

		function _editDeactivate()
		{
			BX.removeClass(target, 'bxhtmled-right-side-item-edit-btn-active');
			BX.unbind(document, 'mouseup', _editDeactivate);
		}

		BX.addClass(target, 'bxhtmled-right-side-item-edit-btn-active');
		BX.bind(document, 'mouseup', _editDeactivate);

		this.editor.GetDialog('editSnippet').Show(target);
		return BX.PreventDefault(e);
	};

	SnippetsControl.prototype.BuildTree = function(sections, elements)
	{
		// Call parent method
		SnippetsControl.superclass.BuildTree.apply(this, arguments);
		if ((!sections || sections.length == 0) && (!elements || elements.length == 0))
		{
			this.pTreeCont.appendChild(BX.create('DIV', {props: {className: 'bxhtmled-no-snip'}, text: BX.message('BXEdSnipNoSnippets')}));
		}
	};

	function EditSnippetDialog(editor, params)
	{
		params = params || {};
		params.id = 'bx_edit_snippet';
		params.width =  600;
		this.zIndex = 3007;
		this.id = 'edit_snippet';

		// Call parrent constructor
		EditSnippetDialog.superclass.constructor.apply(this, [editor, params]);
		this.SetContent(this.Build());
		BX.addClass(this.oDialog.DIV, "bx-edit-snip-dialog");
		BX.addCustomEvent(this, "OnDialogSave", BX.proxy(this.Save, this));
	}
	BX.extend(EditSnippetDialog, window.BXHtmlEditor.Dialog);

	EditSnippetDialog.prototype.Save = function()
	{
		this.editor.snippets.SaveSnippet(
			{
				path: this.pCatSelect.value,
				name: this.pName.value,
				code: this.pCode.value,
				description: this.pDesc.value,
				currentPath: this.currentPath
			}
		);
	};

	EditSnippetDialog.prototype.Build = function()
	{
		this.pCont = BX.create('DIV', {props: {className: 'bxhtmled-edit-snip-cnt'}});

		function addRow(tbl, c1Par, bAdditional)
		{
			var r, c1, c2;

			r = tbl.insertRow(-1);
			if (bAdditional)
			{
				r.className = 'bxhtmled-add-row';
			}

			c1 = r.insertCell(-1);
			c1.className = 'bxhtmled-left-c';

			if (c1Par && c1Par.label)
			{
				c1.appendChild(BX.create('LABEL', {props: {className: c1Par.required ? 'bxhtmled-req' : ''},text: c1Par.label})).setAttribute('for', c1Par.id);
			}

			c2 = r.insertCell(-1);
			c2.className = 'bxhtmled-right-c';
			return {row: r, leftCell: c1, rightCell: c2};
		}

		this.arTabs = [
			{
				id: 'base',
				name: BX.message('BXEdSnipBaseSettings')
			},
			{
				id: 'additional',
				name: BX.message('BXEdSnipAddSettings')
			}
		];


		var res = this.BuildTabControl(this.pCont, this.arTabs);
		this.arTabs = res.tabs;

		// Base params
		var
			_this = this,
			r, c,
			pBaseTbl = BX.create('TABLE', {props: {className: 'bxhtmled-dialog-tbl'}}),
			pAddTbl = BX.create('TABLE', {props: {className: 'bxhtmled-dialog-tbl'}});

		// Name
		r = addRow(pBaseTbl, {label: BX.message('BXEdSnipName') + ':', id: this.id + '-name', required: true});
		this.pName = r.rightCell.appendChild(BX.create('INPUT', {props:
		{
			type: 'text',
			id: this.id + '-name',
			placeholder: BX.message('BXEdSnipNamePlaceHolder')
		}}));

		// Code
		r = addRow(pBaseTbl, {label: BX.message('BXEdSnipCode') + ':', id: this.id + '-code', required: true});
		this.pCode = r.rightCell.appendChild(BX.create('TEXTAREA', {
			props:
			{
				id: this.id + '-code',
				placeholder: BX.message('BXEdSnipCodePlaceHolder')
			},
			style:
			{
				height: '250px'
			}
		}));
		this.arTabs[0].cont.appendChild(pBaseTbl);

		// Category
		r = addRow(pAddTbl, {label: BX.message('BXEdSnipCategory') + ':', id: this.id + '-category'});
		this.pCatSelect = r.rightCell.appendChild(BX.create('SELECT', {
			props: {
				id: this.id + '-category'
			},
			style: {
				maxWidth: '280px'
			}
		}));
		this.pCatManageBut = r.rightCell.appendChild(BX.create('INPUT', {props:
		{
			className: 'bxhtmled-manage-cat',
			type: 'button',
			value: '...',
			title: BX.message('BXEdManageCategories')
		}}));
		this.pCatManageBut.onclick = function()
		{
			_this.editor.GetDialog('snippetsCategories').Show();
		};

		// Description
		r = addRow(pAddTbl, {label: BX.message('BXEdSnipDescription') + ':', id: this.id + '-hint'});
		this.pDesc = r.rightCell.appendChild(BX.create('TEXTAREA', {props:
		{
			id: this.id + '-hint',
			placeholder: BX.message('BXEdSnipDescriptionPlaceholder')
		}}));
		this.arTabs[1].cont.appendChild(pAddTbl);

		// Delete button
		r = BX.adjust(pAddTbl.insertRow(-1), {style: {display: 'none'}});
		c = BX.adjust(r.insertCell(-1), {props: {className: 'bxhtmled--centr-c'}, attrs: {colsPan: 2}});
		c.appendChild(BX.create("INPUT", {
			props:{className: '', type: 'button', value: BX.message('BXEdSnipRemove')},
			events: {
				'click' : function()
				{
					if (confirm(BX.message('BXEdSnipRemoveConfirm')))
					{
						_this.editor.snippets.RemoveSnippet({path: _this.currentPath});
						_this.Close();
					}
				}
			}
		}));
		this.delSnipRow = r;

		return this.pCont;
	};

	EditSnippetDialog.prototype.Show = function(snippetNode)
	{
		this.SetTitle(BX.message('BXEdEditSnippetDialogTitle'));
		this.SetCategories();

		var
			params = {},
			bxTag = this.editor.GetBxTag(snippetNode),
			bNew = !bxTag || !bxTag.tag;

		if (!bNew)
		{
			params = bxTag.params;
			this.currentPath = (params.path == '' ? '' : params.path.replace(',', '/') + '/') + params.name;
			this.delSnipRow.style.display = '';
		}
		else
		{
			this.currentPath = '';
			this.delSnipRow.style.display = 'none';
		}

		this.pName.value = params.title || '';
		this.pCode.value = params.code || '';
		this.pDesc.value = params.description || '';
		this.pCatSelect.value = params.key || '';

		// Call parrent Dialog.Show()
		EditSnippetDialog.superclass.Show.apply(this, arguments);
	};

	EditSnippetDialog.prototype.SetCategories = function()
	{
		// Clear select
		this.pCatSelect.options.length = 0;
		this.pCatSelect.options.add(new Option(BX.message('BXEdSnippetsTitle'), '', true, true));

		var
			name, delim = ' . ', j, i,
			plainList = [],
			list = this.editor.snippetsTaskbar.GetSectionsTreeInfo();

		this.editor.snippets.FetchPlainListOfCategories(list, 1, plainList);

		for (i = 0; i < plainList.length; i++)
		{
			name = '';
			for (j = 0; j < plainList[i].level; j++)
			{
				name += delim;
			}
			name += plainList[i].section.name;

			this.pCatSelect.options.add(new Option(name, plainList[i].key, false, false));
		}
	};

	function SnippetsCategoryDialog(editor, params)
	{
		params = params || {};
		params.id = 'bx_snippets_cats';
		//params.height = 600;
		params.width =  400;
		params.zIndex = 3010;

		this.id = 'snippet_categories';

		// Call parrent constructor
		SnippetsCategoryDialog.superclass.constructor.apply(this, [editor, params]);
		this.SetContent(this.Build());

		this.oDialog.ClearButtons();
		this.oDialog.SetButtons([this.oDialog.btnClose]);

		BX.addClass(this.oDialog.DIV, "bx-edit-snip-cat-dialog");
		//BX.addCustomEvent(this, "OnDialogSave", BX.proxy(this.Save, this));
	}
	BX.extend(SnippetsCategoryDialog, window.BXHtmlEditor.Dialog);

	SnippetsCategoryDialog.prototype.Save = function()
	{
	};

	SnippetsCategoryDialog.prototype.Build = function()
	{
		this.pCont = BX.create('DIV', {props: {className: 'bxhtmled-snip-cat-cnt'}});

		// Add category button & wrap
		this.pAddCatWrap = this.pCont.appendChild(BX.create('DIV', {props: {className: 'bxhtmled-snip-cat-add-wrap'}}));
		this.pAddCatBut = this.pAddCatWrap.appendChild(BX.create('SPAN', {props: {className: 'bxhtmled-snip-cat-add-but'}, text: BX.message('BXEdSnipCatAdd')}));
		BX.bind(this.pAddCatBut, 'click', BX.proxy(this.DisplayAddForm, this));

		var tbl = this.pAddCatWrap.appendChild(BX.create('TABLE', {props: {className: 'bxhtmled-snip-cat-add-tbl'}}));
		var r, c;
		r = tbl.insertRow(-1);
		c = r.insertCell(-1);
		c.className = 'bxhtmled-left-c';
		c.appendChild(BX.create('LABEL', {props: {className: 'bxhtmled-req'}, attrs: {'for': this.id + '-cat-name'}, text: BX.message('BXEdSnipCatAddName') + ':'}));

		c = r.insertCell(-1);
		c.className = 'bxhtmled-right-c';
		this.pCatName = c.appendChild(BX.create('INPUT', {props:
		{
			type: 'text',
			id: this.id + '-cat-name'
		}}));

		r = tbl.insertRow(-1);
		c = r.insertCell(-1);
		c.className = 'bxhtmled-left-c';
		c.appendChild(BX.create('LABEL', {props: {className: 'bxhtmled-req'}, attrs: {'for': this.id + '-cat-par'}, text: BX.message('BXEdSnipParCategory') + ':'}));

		c = r.insertCell(-1);
		c.className = 'bxhtmled-right-c';
		this.pCatPar = c.appendChild(BX.create('SELECT', {props:{id: this.id + '-cat-par'}}));

		r = tbl.insertRow(-1);
		c = r.insertCell(-1);
		c.colSpan = 2;
		c.style.textAlign = 'center';

		this.pSaveCat = c.appendChild(BX.create('INPUT', {props:
		{
			type: 'button',
			className: 'adm-btn-save bxhtmled-snip-save-but',
			value: BX.message('BXEdSnipCatAddBut')
		}}));
		BX.bind(this.pSaveCat, 'click', BX.proxy(this.AddNewCategory, this));

		// Category List
		this.pCatListWrap = this.pCont.appendChild(BX.create('DIV', {props: {className: 'bxhtmled-snip-cat-list-wrap'}}));

		return this.pCont;
	};

	SnippetsCategoryDialog.prototype.AddNewCategory = function()
	{
		this.editor.snippets.AddNewCategory({
			name: this.pCatName.value,
			parent: this.pCatPar.value,
			siteTemplate: ''
		});
	};


	SnippetsCategoryDialog.prototype.DisplayAddForm = function(bShow)
	{
		if (this.animation)
			this.animation.stop();

		if (bShow !== true && bShow !== false)
			bShow = !this.bAddCatOpened;

		bShow = bShow !== false;

		if (this.bAddCatOpened !== bShow)
		{
			if (bShow)
			{
				//jsDD.Disable();
				this.DisableKeyCheck();
				BX.bind(this.pCatName, 'keydown', BX.proxy(this.AddCatKeydown, this));

				this.SetParentCategories();
				this.animationStartHeight = 25;
				this.animationEndHeight = 160;
				BX.focus(this.pCatName);
			}
			else
			{
				//jsDD.Enable();
				this.EnableKeyCheck();
				BX.unbind(this.pCatName, 'keydown', BX.proxy(this.AddCatKeydown, this));
				this.animationStartHeight = 160;
				this.animationEndHeight = 25;
			}

			var _this = this;
			this.animation = new BX.easing({
				duration : 300,
				start : {height: this.animationStartHeight},
				finish : {height: this.animationEndHeight},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),

				step : function(state)
				{
					_this.pAddCatWrap.style.height = state.height + 'px';
				},

				complete : BX.proxy(function()
				{
					this.animation = null;
				}, this)
			});

			this.animation.animate();
			this.bAddCatOpened = bShow;
		}
		this.ResetAddCategoryForm();
	};

	SnippetsCategoryDialog.prototype.SetParentCategories = function()
	{
		// Clear select
		this.pCatPar.options.length = 0;
		this.pCatPar.options.add(new Option(BX.message('BXEdSnippetsTitle'), '', true, true));

		var
			name, delim = ' . ', j, i,
			plainList = [],
			list = this.editor.snippetsTaskbar.GetSectionsTreeInfo();

		this.editor.snippets.FetchPlainListOfCategories(list, 1, plainList);

		for (i = 0; i < plainList.length; i++)
		{
			if (plainList[i].level < 2)
			{
				name = '';
				for (j = 0; j < plainList[i].level; j++)
				{
					name += delim;
				}
				name += plainList[i].section.name;

				this.pCatPar.options.add(new Option(name, plainList[i].key, false, false));
			}
		}
	};

	SnippetsCategoryDialog.prototype.Show = function()
	{
		this.SetTitle(BX.message('BXEdManageCategoriesTitle'));

		this.BuildTree(this.editor.snippets.GetList().groups);
		this.bAddCatOpened = false;
		this.pAddCatWrap.style.height = '';

		// Call parrent Dialog.Show()
		SnippetsCategoryDialog.superclass.Show.apply(this, arguments);
	};

	SnippetsCategoryDialog.prototype.BuildTree = function(sections)
	{
		BX.cleanNode(this.pCatListWrap);
		this.catIndex = {};
		//this.sections = [];
		for (var i = 0; i < sections.length; i++)
		{
			this.BuildCategory(sections[i]);
		}
	};

	SnippetsCategoryDialog.prototype.BuildCategory = function(section)
	{
		var
			_this = this,
			parentCont = this.GetCategoryContByPath(section.path),
			pGroup = BX.create("DIV", {props: {className: "bxhtmled-tskbr-sect-outer"}}),
			pGroupTitle = pGroup.appendChild(BX.create("DIV", {props: {className: "bxhtmled-tskbr-sect"}})),
			icon = pGroupTitle.appendChild(BX.create("SPAN", {props: {className: "bxhtmled-tskbr-sect-icon bxhtmled-tskbr-sect-icon-open"}})),
			title = pGroupTitle.appendChild(BX.create("SPAN", {props: {className: "bxhtmled-tskbr-sect-title"}, text: section.title || section.name})),
			renameInput = pGroupTitle.appendChild(BX.create("INPUT", {props: {
				type: 'text',
				className: "bxhtmled-tskbr-name-input"
			}})),
			childCont = pGroup.appendChild(BX.create("DIV", {props: {className: "bxhtmled-tskb-child"}, style: {display: "block"}})),
			pIconEdit = pGroupTitle.appendChild(BX.create("SPAN", {props: {className: "bxhtmled-right-side-item-edit-btn", title: BX.message('BXEdSnipCatEdit')}})),
			pIconDel = pGroupTitle.appendChild(BX.create("SPAN", {props: {className: "bxhtmled-right-side-item-del-btn", title: BX.message('BXEdSnipCatDelete')}}));

		BX.bind(pIconDel, 'mousedown', BX.proxy(this.DisableDD(), this));
		BX.bind(pIconEdit, 'mousedown', BX.proxy(this.DisableDD(), this));
		BX.bind(renameInput, 'mousedown', BX.proxy(this.DisableDD(), this));

		// Drop category
		BX.bind(pIconDel, 'click', function()
			{
				if (confirm(BX.message('BXEdDropCatConfirm')))
				{
					var path = section.path == '' ? section.name : section.path + '/' + section.name;
					_this.editor.snippets.RemoveCategory({path: path});
				}
			}
		);

		// Rename category
		BX.bind(pIconEdit, 'click', function()
			{
				_this.ShowRename(true, section, renameInput, pGroupTitle);
			}
		);

		childCont.style.display = 'block';

		var key = section.path == '' ? section.name : section.path + ',' + section.name;
		var depth = section.path == '' ? 0 : 1;

		var sect = {
			key: key,
			children: [],
			section: section
		};

		this.catIndex[key] = {
			icon: icon,
			outerCont: pGroup,
			cont: pGroupTitle,
			childCont: childCont,
			sect: sect
		};

		if (depth > 0)
		{
			BX.addClass(pGroupTitle, "bxhtmled-tskbr-sect-" + depth);
			BX.addClass(icon, "bxhtmled-tskbr-sect-icon-" + depth);
		}

		parentCont.appendChild(pGroup);
	};

	SnippetsCategoryDialog.prototype.ShowRename = function(bShow, section, renameInput, pGroupTitle)
	{
		bShow = bShow !== false;
		if (bShow)
		{
			BX.addClass(pGroupTitle, 'bxhtmled-tskbr-sect-rename');
			this.currentRenamedCat = {
				section: section,
				renameInput: renameInput,
				pGroupTitle: pGroupTitle
			};
			renameInput.value = section.name;
			//jsDD.Disable();
			this.DisableKeyCheck();
			BX.bind(renameInput, 'keydown', BX.proxy(this.RenameKeydown, this));
			BX.focus(renameInput);
			renameInput.select();
		}
		else
		{
			BX.removeClass(pGroupTitle, 'bxhtmled-tskbr-sect-rename');
			BX.unbind(renameInput, 'keydown', BX.proxy(this.RenameKeydown, this));
			//jsDD.Enable();
			this.EnableKeyCheck();
			this.currentRenamedCat = false;
		}
	};

	SnippetsCategoryDialog.prototype.RenameKeydown = function(e)
	{
		if (e && this.currentRenamedCat)
		{
			if (e.keyCode == this.editor.KEY_CODES['escape'])
			{
				this.ShowRename(false, this.currentRenamedCat.section, this.currentRenamedCat.renameInput, this.currentRenamedCat.pGroupTitle);
				BX.PreventDefault(e);
			}
			else if (e.keyCode == this.editor.KEY_CODES['enter'])
			{
				var
					newName = BX.util.trim(this.currentRenamedCat.renameInput.value),
					section = this.currentRenamedCat.section,
					path = section.path == '' ? section.name : section.path + '/' + section.name;

				if (newName !== '')
				{
					this.editor.snippets.RenameCategory(
					{
						path: path,
						newName: newName
					});
				}
				this.ShowRename(false, this.currentRenamedCat.section, this.currentRenamedCat.renameInput, this.currentRenamedCat.pGroupTitle);
				BX.PreventDefault(e);
			}
		}
	};

	SnippetsCategoryDialog.prototype.AddCatKeydown = function(e)
	{
		if (e && this.bAddCatOpened)
		{
			if (e.keyCode == this.editor.KEY_CODES['escape'])
			{
				this.DisplayAddForm(false);
				BX.PreventDefault(e);
			}
			else if (e.keyCode == this.editor.KEY_CODES['enter'])
			{
				this.AddNewCategory();
				BX.PreventDefault(e);
			}
		}
	};

	SnippetsCategoryDialog.prototype.DisableDD = function()
	{
		jsDD.Disable();
		BX.bind(document, 'mouseup', BX.proxy(this.EnableDD, this));
	};

	SnippetsCategoryDialog.prototype.EnableDD = function()
	{
		jsDD.Enable();
		BX.unbind(document, 'mouseup', BX.proxy(this.EnableDD, this));
	};

	SnippetsCategoryDialog.prototype.OnDragFinish = function()
	{
	};

	SnippetsCategoryDialog.prototype.GetCategoryContByPath = function(path)
	{
		if (path == '' || !this.catIndex[path])
		{
			return this.pCatListWrap;
		}
		else
		{
			return this.catIndex[path].childCont;
		}
	};

	SnippetsCategoryDialog.prototype.ResetAddCategoryForm = function(path)
	{
		this.pCatName.value = '';
		this.pCatPar.value = '';
	};


	window.BXHtmlEditor.SnippetsControl = SnippetsControl;
	window.BXHtmlEditor.BXEditorSnippets = BXEditorSnippets;
	window.BXHtmlEditor.dialogs.editSnippet = EditSnippetDialog;
	window.BXHtmlEditor.dialogs.snippetsCategories = SnippetsCategoryDialog;
}

	if (window.BXHtmlEditor && window.BXHtmlEditor.dialogs)
		__runsnips();
	else
		BX.addCustomEvent(window, "OnEditorBaseControlsDefined", __runsnips);

})();