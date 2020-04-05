
var GLOBAL_BX_REPORT_USING_CHARTS = false;

// <editor-fold defaultstate="collapsed" desc="period">

function OnTaskIntervalChange(select)
{
	var dateInterval = BX.findNextSibling(select, { "tag": "span", 'className': "filter-date-interval" });
	var dayInterval = BX.findNextSibling(select, { "tag": "span", 'className': "filter-day-interval" });

	BX.removeClass(dateInterval, "filter-date-interval-after filter-date-interval-before");
	BX.removeClass(dayInterval, "filter-day-interval-selected");

	if (select.value == "interval")
		BX.addClass(dateInterval, "filter-date-interval-after filter-date-interval-before");
	else if(select.value == "before")
		BX.addClass(dateInterval, "filter-date-interval-before");
	else if(select.value == "after")
		BX.addClass(dateInterval, "filter-date-interval-after");
	else if(select.value == "days")
		BX.addClass(dayInterval, "filter-day-interval-selected");
}

function initIntervalFilter()
{
	BX.ready(function() {

		BX.prompt = function (input, defaultStr)
		{
			BX.bind(input, 'focus', function()
			{
				if (input.value == defaultStr)
				{
					input.value = '';
					input.style.color = '#000000';
				}
			});

			BX.bind(input, 'blur', function()
			{
				if (input.value == '')
				{
					input.value = defaultStr;
					input.style.color = '#999999';
				}
			});

			BX.fireEvent(input, 'blur');
		};

		BX.prompt(BX('reports-new-title'), BX.message('REPORT_DEFAULT_TITLE'));

		BX.bind(BX("filter-date-interval-calendar-from"), "click", function(e) {
			if (!e) e = window.event;

			var curDate = new Date();
			var curTimestamp = Math.round(curDate / 1000) - curDate.getTimezoneOffset()*60;

			BX.calendar({
				node: this,
				field: BX('REPORT_INTERVAL_F_DATE_FROM'),
				bTime: false
			});

			BX.PreventDefault(e);
		});

		BX.bind(BX("filter-date-interval-calendar-to"), "click", function(e) {
			if (!e) e = window.event;

			var curDate = new Date();
			var curTimestamp = Math.round(curDate / 1000) - curDate.getTimezoneOffset()*60;

			BX.calendar({
				node: this,
				field: BX('REPORT_INTERVAL_F_DATE_TO'),
				bTime: false
			});

			BX.PreventDefault(e);
		});

		OnTaskIntervalChange(BX('task-interval-filter'));
	});
}

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="select">

function show_add_filcol_popup (_this, contentElem){
	var self = _this;
	if (_this == null)
	{
		self = this;
	}

	var unique = Math.random();

	var popup = BX.PopupWindowManager.create('reports-add_col-popup'+unique, self, {
		content : contentElem,
		offsetTop : 2,
		closeIcon : {/* right: "14px", top: "1px" */},
		offsetLeft : -7
	});

	popup.show();

	var reports_popup_nodes = BX.findChildren(contentElem,{tagName:'span', className:'reports-add-popup-arrow'}, true)
	if(reports_popup_nodes)
	{
		for(var i=0; i<reports_popup_nodes.length; i++) {
			BX.bind(reports_popup_nodes[i].parentNode, 'click', open_close)
		}
	}

}

var LAST_FILCOL_CALLED = null;

function show_add_col_popup (_this, contentElem){
	var self = _this, i;
	if (_this == null)
	{
		self = this;
	}

	var unique = Math.random();

	var popup = BX.PopupWindowManager.create('reports-add_col-popup'+unique, self, {
		content : contentElem,
		offsetTop : 2,
		closeIcon : {/* right: "14px", top: "1px" */},
		offsetLeft : -7,
		buttons : [
			new BX.PopupWindowButton({
				text : BX.message('REPORT_ADD'),
				className : "popup-window-button-accept",
				events : { click : function() {
					var fields = BX.findChildren(
						contentElem,
						{tag:'input', attr: {type:'checkbox'}, property: 'checked'},
						true
					);

					for (var i in fields)
					{
						if (!fields.hasOwnProperty(i))
							continue;

						var ch = fields[i];

						// add to list
						addSelectColumn(ch);

						// clear
						//BX.fireEvent(ch, 'click');
						ch.checked = false;
						BX.toggleClass(ch.parentNode.parentNode, 'reports-add-popup-checked');
					}

					this.popupWindow.close(BX.MSLEFT);
				} }
			}),

			new BX.PopupWindowButtonLink({
				text : BX.message('REPORT_CANCEL'),
				className : "popup-window-button-link-cancel",
				events : { click : function() {
					var fields = BX.findChildren(
						contentElem,
						{tag:'input', attr: {type:'checkbox'}, property: 'checked'},
						true
					);

					for (var i in fields)
					{
						if (!fields.hasOwnProperty(i))
							continue;

						var ch = fields[i];

						// clear
						//BX.fireEvent(ch, 'click');
						ch.checked = false;
						BX.toggleClass(ch.parentNode.parentNode, 'reports-add-popup-checked');
					}

					this.popupWindow.close();
				} }
			})
		]
	});

	popup.show();

	var checkboxesItem = BX.findChildren(contentElem,{tagName:'span', className:'reports-add-popup-checkbox-block'}, true);
	for(i=0; i<checkboxesItem.length; i++){
		BX.bind(checkboxesItem[i].parentNode, 'click', check_uncheck)
	}

	var reports_popup_nodes = BX.findChildren(contentElem,{tagName:'span', className:'reports-add-popup-arrow'}, true)
	if (reports_popup_nodes)
	{
		for(i=0; i<reports_popup_nodes.length; i++) {
			BX.bind(reports_popup_nodes[i].parentNode, 'click', open_close)
		}
	}

}

function check_uncheck(){
	var check_box = BX.findChild(this, {tagName:'input', className:'reports-add-popup-checkbox'}, true, false);

	if(!BX.hasClass(this, 'reports-add-popup-checked') && check_box.checked){
		check_box.checked=true;
		BX.toggleClass(this, 'reports-add-popup-checked');
		return false;
	}
	BX.toggleClass(this, 'reports-add-popup-checked');
	check_box.checked=BX.toggle(check_box.checked,[true, false])
}

function open_close(){
	BX.toggleClass(this, 'reports-add-popup-arrow-open');
	var nextDiv = BX.findNextSibling(this, {tagName : "div"} );
	if (BX.hasClass(nextDiv, "reports-add-popup-it-children"))
		BX.toggleClass(nextDiv, "reports-child-opened");

}

function addSelectColumn(checkBox, calc, alias, num, grouping, grouping_subtotal)
{
	if(!checkBox)
	{
		return;
	}

	var COLUMN_NUM;

	if (num != null)
	{
		COLUMN_NUM = num;

		if (num > GLOBAL_REPORT_SELECT_COLUMN_COUNT)
		{
			GLOBAL_REPORT_SELECT_COLUMN_COUNT = num;
		}
	}
	else
	{
		COLUMN_NUM = GLOBAL_REPORT_SELECT_COLUMN_COUNT;
	}

	var newCol = BX.clone(BX('reports-forming-column-example'), true);
	var colContainer = BX('reports-add-columns-block');
	var beforeElem = BX('reports-add-column-block');

	// remove example stuff
	newCol.style.display = '';
	newCol.setAttribute('id', '');

	BX.addClass(newCol, 'reports-forming-column');

	// set Title
	var titleElem = BX.findChild(newCol, {className:'reports-add-col-tit-text'}, true);
	titleElem.innerHTML = BX.util.htmlspecialchars(checkBox.title);
	if (parseInt(checkBox.getAttribute('isUF')) === 1)
		BX.addClass(titleElem, 'uf');

	var elem;

	// set hidden column name
	elem = BX.findChild(newCol, {attr: {name:'report_select_columns[%s][name]'}}, true);
	elem.name = elem.name.replace('%s', COLUMN_NUM);
	elem.value = checkBox.name;
	elem.title = checkBox.title;

	// set alias input name
	elem = BX.findChild(newCol, {attr: {name:'report_select_columns[%s][alias]'}}, true);
	elem.name = elem.name.replace('%s', COLUMN_NUM);
	if (alias && alias.length > 0)
	{
		// prefill
		elem.value = alias;
		elem.parentNode.style.display = 'inline-block';
	}

	// add calc select
	var isMultiple = (parseInt(checkBox.getAttribute('isMultiple')) === 1);
	if (isMultiple)
	{
		elem = BX.create('SELECT');
	}
	else
	{
		elem = BX.clone(
			BX('report-select-calc-'+ checkBox.name) || BX('report-select-calc-'+ checkBox.getAttribute('fieldType')),
			true
		);
	}

	elem.id = '';
	elem.name = 'report_select_columns['+COLUMN_NUM+'][calc]';
	BX.addClass(elem, 'reports-add-col-select');
	BX.addClass(elem, 'reports-add-col-select-calc');

	var elemParent, elemSibling;

	elemParent = BX.findChild(newCol, {className:'reports-add-col-title'});
	elemSibling = BX.findChild(elemParent, {className:'reports-add-col-input'});

	if (calc > '')
	{
		// prefill
		BX.findChild(newCol, {tag:'input', attr:{type:'checkbox'}}, true).checked = true;

		elem.style.display = 'inline-block';
		elem.disabled = false;

		setSelectValue(elem, calc);
	}

	elemParent.insertBefore(elem, elemSibling);

	if (elem.options.length < 1)
	{
		// disable checkbox
		BX.findChild(newCol, {tag:'input', attr:{type:'checkbox'}}, true).disabled = true;
	}

	// set grouping checkbox name
	var groupingCheckbox = BX.findChild(
		BX.findChild(newCol, {tag: 'span', 'className': 'reports-grouping-checkbox'}),
		{tag: 'input', attr: {type: 'checkbox'}}
	);
	if (groupingCheckbox)
	{
		groupingCheckbox.name = 'report_select_columns['+COLUMN_NUM+'][grouping]';
		if (!!grouping)
		{
			groupingCheckbox.checked = true;
			GLOBAL_REPORT_GROUPING_COLUMNS_COUNT++;
		}
	}

	// set grouping subtotal checkbox name
	var groupingSubtotalCheckbox = BX.findChild(
		BX.findChild(newCol, {tag: 'span', 'className': 'reports-grouping-subtotal-checkbox'}),
		{tag: 'input', attr: {type: 'checkbox'}}
	);
	if (groupingSubtotalCheckbox)
	{
		groupingSubtotalCheckbox.name = 'report_select_columns['+COLUMN_NUM+'][grouping_subtotal]';
		if (!!grouping_subtotal) groupingSubtotalCheckbox.checked = true;
	}

	// add % controls
	var prcntSel = BX.clone(BX.findChild(BX('report-select-prcnt-examples'), {className:'reports-add-col-select-prcnt'}));
	var prcntbySel = BX.clone(BX.findChild(BX('report-select-prcnt-examples'), {className:'reports-add-col-select-prcnt-by'}));

	prcntSel.name = 'report_select_columns['+COLUMN_NUM+'][prcnt]';
	prcntbySel.name = 'report_select_columns['+COLUMN_NUM+'][prcnt]';

	elemParent = BX.findChild(newCol, {className:'reports-add-col-title'});
	elemSibling = BX.findChild(elemParent, {className:'reports-add-col-input'});

	elemParent.insertBefore(prcntSel, elemSibling);
	elemParent.insertBefore(prcntbySel, elemSibling);

	BX.bind(prcntSel, "change", function(e)
	{
		var parent = this.parentNode;
		var prcntbySel = BX.findChild(parent, {className:'reports-add-col-select-prcnt-by'});

		if (this.value == 'self_column')
		{
			prcntbySel.disabled = true;
			prcntbySel.style.display = 'none';
		}
		else
		{
			prcntbySel.disabled = false;
			prcntbySel.style.display = 'inline-block';
		}

		rebuildPercentView();
		rebuildSortSelect();
	});

	BX.bind(prcntbySel, "change", function(e)
	{
		rebuildSortSelect();
	});

	// add Events
	// UP button
	BX.bind(BX.findChild(newCol, {className:"reports-add-col-button-up"}, true), "click", function(e)
	{
		var colContainer = this.parentNode.parentNode.parentNode;
		var colCollection = BX.findChildren(colContainer, {className:'reports-forming-column'});
		var butContainer = this.parentNode.parentNode;

		var prevContainer = null;

		for (var i in colCollection)
		{
			if (!colCollection.hasOwnProperty(i))
				continue;

			if (colCollection[i] == butContainer)
			{
				var movingContainer = colCollection[i];

				if (prevContainer != null)
				{
					colContainer.insertBefore(movingContainer, prevContainer);
				}
			}

			prevContainer = colCollection[i];
		}

		rebuildPercentView();
		rebuildSortSelect();
	});

	// DOWN button
	BX.bind(BX.findChild(newCol, {className:"reports-add-col-button-down"}, true), "click", function(e)
	{
		var butContainer = this.parentNode.parentNode;
		var nextContainer = BX.findNextSibling(butContainer, {className: butContainer.getAttribute('class')});

		if (nextContainer)
		{
			BX.fireEvent(BX.findChild(nextContainer, {className: 'reports-add-col-button-up'}, true), 'click');
		}

		rebuildPercentView();
		rebuildSortSelect();

		return false;
	});

	// % button
	BX.bind(BX.findChild(newCol, {className:"reports-add-col-tit-prcnt"}, true), "click", function(e)
	{
		// reports-add-col-select-prcnt, reports-add-col-select-prcnt-by
		var isOpen = BX.hasClass(this, 'reports-add-col-tit-prcnt-close');
		var prcntSel = BX.findChild(newCol, {className:'reports-add-col-select-prcnt'}, true);
		var prcntbySel = BX.findChild(newCol, {className:'reports-add-col-select-prcnt-by'}, true);

		if (isOpen)
		{
			disablePrcntView(newCol);
		}
		else
		{
			if (isColumnPercentable(newCol))
			{
				prcntSel.style.display = 'inline-block';
				prcntSel.disabled = false;
				BX.addClass(this, 'reports-add-col-tit-prcnt-close');
				BX.removeClass(this, 'reports-add-col-tit-prcnt');

				if (prcntSel.value != 'self_column')
				{
					prcntbySel.style.display = 'inline-block';
					prcntbySel.disabled = false;
				}

				rebuildPercentView(true);
			}
			else
			{
				// percent view is not available for this column
				alert(BX.message('REPORT_PRCNT_VIEW_IS_NOT_AVAILABLE'));
			}
		}

		rebuildSortSelect();

		return false;
	});

	// remove column buttons
	BX.bind(BX.findChild(newCol, {className:"reports-add-col-tit-remove"}, true), "click", function(e)
	{
		var butContainer = this.parentNode.parentNode;
		var groupingCheckbox = BX.findChild(
			BX.findChild(newCol, {tag: 'span', 'className': 'reports-grouping-checkbox'}),
			{tag: 'input', attr: {type: 'checkbox'}}
		);
		BX.remove(butContainer);
		rebuildPercentView();
		rebuildSortSelect();
		if (groupingCheckbox)
		{
			if (groupingCheckbox.checked)
			{
				if (--GLOBAL_REPORT_GROUPING_COLUMNS_COUNT === 0) enableReportLimit(true);
			}
		}
		return false;
	});

	// edit column name buttons
	BX.bind(BX.findChild(newCol, {className:"reports-add-col-tit-edit"}, true), "click", function(e)
	{
		var butContainer = this.parentNode.parentNode;
		var aliasInput = BX.findChild(butContainer, {tag:'input', attr:{type:'text'}}, true);

		aliasInput.parentNode.style.display = 'inline-block';
		BX.focus(aliasInput);

		return false;
	});

	// edit column name inputs
	BX.bind(BX.findChild(newCol, {tag:'input', attr:{type:'text'}}, true), 'blur', hideAliasInput);
	BX.bind(BX.findChild(newCol, {tag:'input', attr:{type:'text'}}, true), 'change', hideAliasInput);

	// calculating checkbox
	var calcCheckBoxContainer = BX.findChild(newCol, {tag:'span', 'className':'reports-add-col-checkbox'}, true);
	BX.bind(BX.findChild(calcCheckBoxContainer, {tag:'input', attr:{type:'checkbox'}}, true), 'click', function(e){
		var butContainer = this.parentNode.parentNode;
		var calcSelect = BX.findChild(butContainer, {className:'reports-add-col-select-calc'}, true);

		calcSelect.style.display = this.checked ? 'inline-block' : 'none';
		calcSelect.disabled = this.checked ? false : true;

		rebuildPercentView();
		rebuildSortSelect();
	});

	// calculating functions select
	BX.bind(BX.findChild(newCol, {className:'reports-add-col-select-calc'}, true), 'change', function(e){
		rebuildPercentView();
		rebuildSortSelect();
	});

	// grouping checkbox
	if (groupingCheckbox)
	{
		BX.bind(groupingCheckbox, 'click', function(e){
			if (groupingCheckbox.checked)
			{
				if (++GLOBAL_REPORT_GROUPING_COLUMNS_COUNT === 1) enableReportLimit(false);
			}
			else
			{
				if (--GLOBAL_REPORT_GROUPING_COLUMNS_COUNT === 0) enableReportLimit(true);
			}
		});
	}

	// action
	colContainer.insertBefore(newCol, beforeElem);

	// postAction
	rebuildPercentView();
	rebuildSortSelect();
	GLOBAL_REPORT_SELECT_COLUMN_COUNT++;
}

function hideAliasInput(e)
{
	if (BX.util.trim(this.value) == '')
	{
		this.value = '';
		BX.hide(this.parentNode);
	}

	rebuildPercentView();
	rebuildSortSelect();
}

function rebuildSortSelect()
{
	var sortSelect = BX('reports-sort-select');
	var previousSort = sortSelect.value;
	var chartSelects = null, i;
	var valueTypes = [];
	var chartSelectPrevValues = [];

	while (sortSelect.options.length > 0)
	{
		sortSelect.remove(0);
	}

	if (GLOBAL_BX_REPORT_USING_CHARTS)
	{
		var chartSelectsContainer = BX('report-chart-config');
		valueTypes = chartGetYColTypes();
		if (chartSelectsContainer)
		{
			chartSelects = BX.findChildren(
				chartSelectsContainer,
				{tag: 'select', 'className': 'report-chart-select-col'},
				true
			);
			for (i in chartSelects)
			{
				if (chartSelects.hasOwnProperty(i))
				{
					chartSelectPrevValues[i] = chartSelects[i].value;
					while (chartSelects[i].options.length > 0) chartSelects[i].remove(0);
				}
			}
		}
	}

	// collect new values
	var newValues = [];
	var columnList = BX.findChildren(BX('reports-add-columns-block'), {tag:'input', attr:{type:'hidden'}}, true);

	var yColumnIndex = 0;
	for (i in columnList)
	{
		if (!columnList.hasOwnProperty(i))
			continue;

		if (columnList[i].value != '')
		{
			var columnContainer = columnList[i].parentNode;

			// extract columnt key
			var found = columnList[i].name.match(/report_select_columns\[(\d+)\]\[name\]/)
			var key = found[1];
			var title, columnInfo;

			title = null;
			columnInfo = parseSelectColumnInfo(columnContainer);
			if (GLOBAL_BX_REPORT_USING_CHARTS)
			{
				// generate field title
				title = getFullColumnTitle(columnContainer);

				// add options to selects of chart
				var selectOption, chartType, dataType, validDataType;
				var chartTypeSelect = BX('report-chart-type');
				if (chartSelects)
				{
					for (var j in chartSelects)
					{
						if (!chartSelects.hasOwnProperty(j))
							continue;

						if (yColumnIndex === 0) chartSelects[j].selectedIndex = 0;
						selectOption = new Option(title, key);
						dataType = columnInfo.column_type;
						selectOption.setAttribute('data_type', columnInfo.column_type);
						validDataType = true;
						if (chartTypeSelect)
						{
							validDataType = false;
							chartType = chartTypeSelect.value;
							if (valueTypes[chartType])
								validDataType = (!columnInfo.ismultiple && valueTypes[chartType].indexOf(columnInfo.column_type) >= 0);
						}
						if (chartSelects[j].name === 'chart_x' || validDataType)
						{
							try { chartSelects[j].add(selectOption, null); }
							catch (e) { chartSelects[j].add(selectOption); }
							if (chartSelectPrevValues[j] == key)
							{
								chartSelects[j].selectedIndex = chartSelects[j].options.length - 1;
							}
						}
					}
				}
				yColumnIndex++;
			}

			var notSortedTypes = ["file", "employee", "crm_status", "iblock_section", "iblock_element", "crm"];
			if ((columnInfo.calc_enabled && columnInfo.calc == 'GROUP_CONCAT')
				|| (columnInfo.isuf && (columnInfo.ismultiple || notSortedTypes.indexOf(columnInfo.column_type) >= 0)))
			{
				continue;
			}

			// generate field title
			if (title === null) title = getFullColumnTitle(columnContainer);

			// add option to select
			var option = new Option(title, key);

			try
			{
				sortSelect.add(option, null);
			}
			catch (e)
			{
				sortSelect.add(option);
			}

			if (previousSort == key)
			{
				sortSelect.selectedIndex = sortSelect.options.length - 1;
			}
		}
	}

	rebuildReportPreviewTable();
	//rebuildFilterResultColumns();
}

function rebuildFilterResultColumns()
{
	BX('report-filter-result-columns-cont').innerHTML = '';

	var columnList = BX.findChildren(BX('reports-add-columns-block'), {tag:'input', attr:{type:'hidden'}}, true);
	var columnInfoList = {}, columnInfo = null, i;

	for (i in columnList)
	{
		if (!columnList.hasOwnProperty(i))
			continue;

		if (columnList[i].value != '')
		{
			// build items for popup
			var columnContainer = columnList[i].parentNode;


			columnInfo = parseSelectColumnInfo(columnContainer);

			// rewrite data type
			if (columnInfo.prcnt)
			{
				columnInfo.data_type = 'float';
			}
			else if (columnInfo.calc == 'COUNT_DISTINCT')
			{
				columnInfo.data_type = 'integer';
			}
			else if (columnInfo.calc == 'GROUP_CONCAT')
			{
				// no filter for grc
				continue;
			}

			columnInfoList[columnInfo.num] = columnInfo;

			var elemHtml = '<div class="reports-add-popup-item">'
				+ '<span class="reports-add-pop-left-bord"></span>'
				+ '<span class="reports-add-popup-checkbox-block">'
					+ '<input class="reports-add-popup-checkbox" type="checkbox" fieldtype="'+columnInfo.data_type+'" '
					+ 'title="'+columnInfo.title+'" name="__COLUMN__'+columnInfo.num+'">'
					+ '</span>'
					+ '<span class="reports-add-popup-it-text">'+columnInfo.title+'</span>'
				+ '</div>';


			BX('report-filter-result-columns-cont').innerHTML += elemHtml;
		}
	}

	// bind click event for new result columns in filter
	var fList = BX.findChildren(BX('report-filter-result-columns-cont'), {className:'reports-add-popup-it-text'}, true);

	for (i in fList)
	{
		if (fList.hasOwnProperty(i))
			BX.bind(fList[i], 'click', fillFilterColumnEvent);
	}

	// remove filters for non existing or data-type-changed result columns
	// also update column titles on filters for existing columns
	var filterItems = BX.findChildren(BX('reports-filter-columns-container'), {attr: {fielddefinition:/__COLUMN__\d+/}}, true);
	for (i in filterItems)
	{
		if (!filterItems.hasOwnProperty(i))
			continue;

		var filterItem = filterItems[i].parentNode.parentNode;
		var column_num = filterItems[i].getAttribute('fielddefinition').match(/\d+/)[0];
		columnInfo = columnInfoList[column_num];
		var current_data_type = filterItems[i].getAttribute('fieldType');

		if (!columnInfo || current_data_type != columnInfo.data_type)
		{
			// this column has been deleted from select
			// or data_type has been changed through prcnt or calc
			var minusButt = BX.findChild(filterItem, {className:'reports-filter-del-item'}, true);
			BX.fireEvent(minusButt, 'click');
		}
		else
		{
			// column still exists, but it may need to update title
			filterItems[i].title = columnInfo.title;
			filterItems[i].innerHTML = columnInfo.title;
		}
	}
}

function rebuildHtmlSelect(obj, newValues)
{
	var previousValue = obj.value;

	// clean
	while (obj.options.length > 0)
	{
		obj.remove(0);
	}

	// fill
	var i, option;

	for (i in newValues)
	{
		if (!newValues.hasOwnProperty(i))
			continue;

		option = new Option(newValues[i], i);

		try
		{
			obj.add(option, null);
		}
		catch (e)
		{
			obj.add(option);
		}

		if (previousValue == i)
		{
			obj.selectedIndex = obj.options.length - 1;
		}
	}
}

function getFullColumnTitle(columnContainer)
{
	var title = '';

	var mainInput = BX.findChild(columnContainer, {tag:'input', attr:{type:'hidden'}, name:/report_select_columns\[\d+\]\[name\]/});
	var match = /\[(\d+)\]/.exec(mainInput.name);
	var colId = match[1];

	// check if alias exists
	var aliasInput = BX.findChild(columnContainer, {attr: {name: 'report_select_columns['+colId+'][alias]'}}, true);
	if (aliasInput.value != '')
	{
		title = aliasInput.value;
	}
	else
	{
		// base title
		title = mainInput.title;

		// check if calculate function exists
		var calcCheckbox = null;
		var calcCheckboxContainer = BX.findChild(
			columnContainer, {tag: 'span', 'className': 'reports-add-col-checkbox'}, true
		);
		if (calcCheckboxContainer)
		{
			calcCheckbox = BX.findChild(
				calcCheckboxContainer,
				{tag: 'input', attr: {type: 'checkbox'}, property: 'checked'},
				true
			);
		}
		if (calcCheckbox != null)
		{
			var calcSelect = BX.findChild(columnContainer, {className: 'reports-add-col-select-calc'}, true);
			if (calcSelect.value != '')
			{
				title += ' ('+calcSelect.options[calcSelect.selectedIndex].text+')';
			}
		}

		// check if prcnt exists
		var prcntSel = BX.findChild(columnContainer, {className:'reports-add-col-select-prcnt'}, true);
		if (prcntSel.disabled == false)
		{
			if (prcntSel.value == 'self_column')
			{
				title += ' (%)';
			}
			else
			{
				var prcntbySel = BX.findChild(columnContainer, {className:'reports-add-col-select-prcnt-by'}, true);
				if (prcntbySel.selectedIndex >= 0)
				{
					var byTitle = prcntbySel.options[prcntbySel.selectedIndex].innerHTML;
					title += ' (' + BX.message('REPORT_PRCNT_BUTTON_TITLE') +' '+byTitle+')';
				}
			}
		}
	}

	return title;
}

function parseSelectColumnType(columnContainer)
{
	var mainInput, checkbox, dataType;
	var calcCheckbox, calcEnabled, calcSelect, calc;
	var prcntSelect, prcntBySel, prcnt;

	dataType = null;
	mainInput = BX.findChild(columnContainer, {tag:'input', attr:{type:'hidden'}, name:/report_select_columns\[\d+\]\[name\]/});
	checkbox = BX.findChild(BX('reports-add_col-popup-cont'), {attr:{type:'checkbox', name: mainInput.value}}, true);
	dataType = checkbox.getAttribute('fieldType');
	calcCheckbox = BX.findChild(columnContainer, {'className': 'reports-checkbox', attr:{type:'checkbox'}}, true);
	calcEnabled = calcCheckbox.checked;
	calcSelect = BX.findChild(columnContainer, {className: 'reports-add-col-select-calc'}, true);
	calc = null;
	if (calcSelect.value != '') calc = calcSelect.value;
	prcntSelect = BX.findChild(columnContainer, {className:'reports-add-col-select-prcnt'}, true);
	prcnt = null;
	if (prcntSelect.disabled == false)
	{
		if (prcntSelect.value == 'self_column') prcnt = prcntSelect.value;
		else
		{
			prcntBySel = BX.findChild(columnContainer, {className:'reports-add-col-select-prcnt-by'}, true);
			if (prcntBySel.selectedIndex >= 0) prcnt = prcntBySel.value;
		}
	}
	if (prcnt) dataType = 'float';
	else if (calcEnabled)
	{
		if (calc == 'COUNT_DISTINCT') dataType = 'integer';
		else if (calc == 'GROUP_CONCAT') dataType = 'string';
		else if (dataType == 'boolean')
		{
			if (calc == 'MIN' || calc == 'AVG' || calc == 'MAX' || calc == 'SUM' || calc == 'COUNT_DISTINCT')
			{
				dataType = 'integer';
			}
		}
	}

	return dataType;
}

function parseSelectColumnInfo(columnContainer)
{
	var result = {
		num: null,
		name: null,
		title: null,
		data_type: null,
		calc_enabled: null,
		calc: null,
		prcnt: null,
		column_type: null,
		isgrc: null,
		isuf: null,
		ufid: null,
		ismultiple: null,
		ufname: null
	};

	var mainInput = BX.findChild(
		columnContainer, {tag:'input', attr:{type:'hidden'}, name:/report_select_columns\[\d+\]\[name\]/}
	);
	var match = /\[(\d+)\]/.exec(mainInput.name);

	result.num = match[1];
	result.name = mainInput.value;

	var checkbox = BX.findChild(BX('reports-add_col-popup-cont'), {attr:{type:'checkbox', name:result.name}}, true);
	result.column_type = result.data_type = checkbox.getAttribute('fieldType');

	result.title = getFullColumnTitle(columnContainer);

	// check if calculate function exists
	var calcCheckbox = BX.findChild(
		columnContainer, {tag: 'input', attr: {type: 'checkbox'}, property: 'checked'}, true
	);
	if (calcCheckbox != null)
	{
		var calcSelect = BX.findChild(columnContainer, {className: 'reports-add-col-select-calc'}, true);
		if (calcSelect.value != '')
		{
			result.calc = calcSelect.value;
		}
		result.calc_enabled = calcCheckbox.checked;
	}

	// check if prcnt exists
	var prcntSel = BX.findChild(columnContainer, {className:'reports-add-col-select-prcnt'}, true);
	if (prcntSel.disabled == false)
	{
		if (prcntSel.value == 'self_column')
		{
			result.prcnt = prcntSel.value;
		}
		else
		{
			var prcntbySel = BX.findChild(columnContainer, {className:'reports-add-col-select-prcnt-by'}, true);
			if (prcntbySel.selectedIndex >= 0)
			{
				result.prcnt = prcntbySel.value;
			}
		}
	}

	if (result.prcnt)
	{
		result.column_type = 'float';
	}
	else if (result.calc_enabled)
	{
		if (result.calc == 'COUNT_DISTINCT')
		{
			result.column_type = 'integer';
		}
		else if (result.calc == 'GROUP_CONCAT')
		{
			result.column_type = 'string';
		}
		else if (result.column_type == 'boolean')
		{
			if (result.calc == 'MIN' || result.calc == 'AVG' || result.calc == 'MAX'
				|| result.calc == 'SUM' || result.calc == 'COUNT_DISTINCT')
			{
				result.column_type = 'integer';
			}
		}
	}

	result.isuf = (parseInt(checkbox.getAttribute('isuf')) === 1);
	if (result.isuf)
	{
		result.ufid = checkbox.getAttribute('ufid');
		result.ismultiple = (parseInt(checkbox.getAttribute('ismultiple')) === 1);
		result.ufname = checkbox.getAttribute('ufname');
	}

	return result;
}

function isColumnPercentable(col)
{
	/*
     1. any integer
     2. any float
     3. boolean with aggr
     4. any with COUNT_DISTINCT aggr
     */
	var fieldName = BX.findChild(col, {attr:{name:/report_select_columns\[\d+\]\[name\]/}}).value;
	var iCheckbox = BX.findChild(BX('reports-add_col-popup-cont'), {attr:{type:'checkbox', name: fieldName}}, true);
	var fieldType = iCheckbox.getAttribute('fieldType');
	var isUF = (parseInt(iCheckbox.getAttribute('isUF')) === 1);
	var isMultiple = (parseInt(iCheckbox.getAttribute('isMultiple')) === 1);

	var aggr = null;
	var calcSelect = BX.findChild(col, {className:'reports-add-col-select-calc'}, true);
	if (!calcSelect.disabled)
	{
		aggr = calcSelect.value;
	}

	if (aggr == 'GROUP_CONCAT')
	{
		return false;
	}
	else
	{
		return (
			((fieldType == 'integer' || fieldType == 'float') && (!isUF || !isMultiple))
				|| (fieldType == 'boolean' && aggr != null) || aggr == 'COUNT_DISTINCT'
		);
	}
}

function rebuildPercentView(withAlert)
{
	/*
     prcnt:"self_column"
     prcnt:"1"
     */

	var cols = BX.findChildren(BX('reports-add-columns-block'), {className:'reports-forming-column'});
	var i, col, isPrcntViewOpen;
	var prcntByList = {length:0};
	var colIdByColNum = {};
	var prcntSel;

	// generate array with possible "% of" variants
	for (i in cols)
	{
		if (!cols.hasOwnProperty(i))
			continue;

		col = cols[i];

		if (isColumnPercentable(col))
		{
			// also deny columns with self_columnt prcnt
			// it counts only after total select
			prcntSel = BX.findChild(col, {className:'reports-add-col-select-prcnt'}, true);
			if (!prcntSel.disabled && prcntSel.value == 'self_column')
			{
				continue;
			}

			var idElem = BX.findChild(col, {attr:{name:/report_select_columns\[\d+\]\[name\]/}});
			var match = /\[(\d+)\]/.exec(idElem.name);
			var colId = match[1];
			var colTitle = getFullColumnTitle(col);
			prcntByList[colId] = colTitle;
			prcntByList.length++;
			colIdByColNum[i] = colId;
		}
	}

	// rebuild prcnt views
	for (i in cols)
	{
		if (!cols.hasOwnProperty(i))
			continue;

		col = cols[i];

		isPrcntViewOpen = BX.findChild(col, {className:'reports-add-col-tit-prcnt-close'}, true);

		if (!isPrcntViewOpen)
		{
			// prcnt view is not active, nothing interesting here
			continue;
		}

		if (!isColumnPercentable(col))
		{
			// this column is no more percentable. disable prcnt view!
			// kill! kill! kill!
			disablePrcntView(col);
			continue;
		}

		prcntSel = BX.findChild(col, {className:'reports-add-col-select-prcnt'}, true);
		var prcntBySel = BX.findChild(col, {className:'reports-add-col-select-prcnt-by'}, true);

		var prcntType = prcntSel.value;

		// enable/disable other_field prcnt
		if (prcntByList.length < 2)
		{
			prcntSel.options[1].disabled = true;
			prcntBySel.style.display = 'none';
			prcntBySel.disabled = true;
			rebuildHtmlSelect(prcntBySel, []);
		}
		else
		{
			prcntSel.options[1].disabled = false;

			if (prcntType != 'self_column')
			{
				prcntBySel.style.display = 'inline-block';
				prcntBySel.disabled = false;
			}
		}

		// enable/disable self_column prcnt
		var aggrSel = BX.findChild(col, {className:'reports-add-col-select-calc'}, true);

		if (aggrSel.disabled ||
			(!aggrSel.disabled && (aggrSel.value == 'SUM' || aggrSel.value == 'COUNT_DISTINCT'))
			)
		{
			// ok
			prcntSel.options[0].disabled = false;
		}
		else
		{
			prcntSel.options[0].disabled = true;
		}

		// check
		if (prcntSel.options[0].disabled && prcntSel.options[1].disabled)
		{
			// kill! kill! kill!
			disablePrcntView(col);

			if (withAlert)
			{
				alert(BX.message('REPORT_PRCNT_VIEW_IS_NOT_AVAILABLE'));
			}

			return;
		}

		if (prcntType == 'self_column')
		{
			if (prcntSel.options[0].disabled)
			{
				setSelectValue(prcntSel, 'other_field');
				disablePrcntView(col);
			}
		}
		else
		{
			// check
			if (prcntByList.length < 2)
			{
				// disable prct view, there is no more variants except self
				setSelectValue(prcntSel, 'self_column');
				// kill! kill! kill!
				disablePrcntView(col);
			}
			else
			{
				// rebuild columns select from prcntByList
				var prevValue = prcntBySel.value;

				// exclude self from list
				var _prcntByList = BX.clone(prcntByList);
				delete _prcntByList[colIdByColNum[i]];
				delete _prcntByList['length'];

				rebuildHtmlSelect(prcntBySel, _prcntByList);

				var currValue = prcntBySel.value;

				//if (prevValue != '' && prcntByList[prevValue] && prevValue != currValue)
				if (prevValue != '' && prevValue != currValue)
				{
					// if previous value did not reincarnated - kill! kill! kill!
					setSelectValue(prcntSel, 'self_column');
					disablePrcntView(col);
				}
			}
		}
	}
}

function disablePrcntView(col)
{
	var button = BX.findChild(col, {className:"reports-add-col-tit-prcnt"}, true)
		|| BX.findChild(col, {className:"reports-add-col-tit-prcnt-close"}, true);

	var isOpen = BX.hasClass(button, 'reports-add-col-tit-prcnt-close');
	var prcntSel = BX.findChild(col, {className:'reports-add-col-select-prcnt'}, true);
	var prcntbySel = BX.findChild(col, {className:'reports-add-col-select-prcnt-by'}, true);

	// close
	BX.removeClass(button, 'reports-add-col-tit-prcnt-close');
	BX.addClass(button, 'reports-add-col-tit-prcnt');

	prcntSel.style.display = 'none';
	prcntSel.disabled = true;

	prcntbySel.style.display = 'none';
	prcntbySel.disabled = true;
}

function rebuildReportPreviewTable()
{
	var oldtable = BX.findChild(BX('reports-preview-table-report'), {tag:'table'}, true);
	var options = BX('reports-sort-select').options;

	// create new
	var table = BX.create('TABLE');
	table.cellSpacing = 0;
	BX.addClass(table, "reports-list-table");

	// fill
	var row = table.createTHead().insertRow(-1);
	var i, title, cell;
	for (i=0; i<options.length; i++)
	{
		title = options[i].innerHTML;

		cell = BX.create('TH');

		if (i == 0)
		{
			// first column style
			BX.addClass(cell, 'reports-first-column');
			BX.addClass(cell, 'reports-head-cell-top');
		}
		else if (i == options.length-1)
		{
			// last column style
			BX.addClass(cell, 'reports-last-column');
		}

		cell.innerHTML = '<div class="reports-head-cell">' +
			'<span class="reports-head-cell-title">' + title + '</span></div>';


		row.appendChild(cell);
	}

	// replace
	oldtable.parentNode.replaceChild(table, oldtable);
}

function setSelectValue(select, value)
{
	var i, j;
	var bFirstSelected = false;
	var bMultiple = !!(select.getAttribute('multiple'));
	if (!(value instanceof Array)) value = [value];
	for (i=0; i<select.options.length; i++)
	{
		for (j in value)
		{
			if (!value.hasOwnProperty(j))
				continue;

			if (select.options[i].value == value[j])
			{
				if (!bFirstSelected) {bFirstSelected = true; select.selectedIndex = i;}
				select.options[i].selected = true;
				break;
			}
		}
		if (!bMultiple && bFirstSelected) break;
	}
}

function setPrcntView(colId, value)
{
	var col = BX.findChild(BX('reports-add-columns-block'), {attr:{name:'report_select_columns['+colId+'][name]'}}, true).parentNode;

	if (value != null && value != '')
	{
		// press button
		var button = BX.findChild(col, {className:"reports-add-col-tit-prcnt"}, true);
		BX.addClass(button, 'reports-add-col-tit-prcnt-close');
		BX.removeClass(button, 'reports-add-col-tit-prcnt');

		// show first select
		var prcntSel = BX.findChild(col, {className:'reports-add-col-select-prcnt'}, true);
		prcntSel.style.display = 'inline-block';
		prcntSel.disabled = false;

		if (value != 'self_column')
		{
			// show second select
			var prcntbySel = BX.findChild(col, {className:'reports-add-col-select-prcnt-by'}, true);
			prcntbySel.style.display = 'inline-block';
			prcntbySel.disabled = false;

			// set values
			setSelectValue(prcntSel, 'other_field');
			rebuildPercentView();
			setSelectValue(prcntbySel, value);
		}

		rebuildSortSelect();
	}
}

function initSelectColumnButton()
{
	BX.ready(function() {
		BX.bind(BX("reports-add-select-column-button"), 'click', function()
		{
			show_add_col_popup(this, BX("reports-add_col-popup-cont"));
		});
	});
}

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="filters">

function addFilterColumn(fcContainer, afterElem) {
	var newCol = BX.clone(BX('reports-filter-item-example'), true);

	// remove example stuff
	newCol.style.display = '';
	newCol.setAttribute('id', '');

	BX.addClass(newCol, 'reports-filter-item');

	var level = fcContainer.getAttribute('level') - 0 + 1;

	if (level > 2) {
		BX.addClass(newCol, 'reports-filter-sub-lev');
		BX.addClass(newCol, 'reports-filter-' + (level - 1) + '-lev');
	}

	// show root andor
	if (level == 2)
	{
		var root_has_children = BX.findChildren(BX('reports-filter-columns-container'));
		if (root_has_children)
		{
			BX.show(BX('reports-filter-base-andor-selector'));
		}
	}

	// add Events
	var fChoose = BX.findChild(newCol, {className:'reports-filter-item-name'}, true);
	BX.bind(fChoose, 'click', function(e) {
		show_add_filcol_popup(this, BX("reports-add_filcol-popup-cont"));
		LAST_FILCOL_CALLED = this;
	});

	var plusButt = BX.findChild(newCol, {className:'reports-filter-add-item'}, true);
	BX.bind(plusButt, 'click', function(e) {
		var col = this.parentNode.parentNode;

		// if add in root
		if (col.parentNode.getAttribute('level') == '1')
		{
			BX.show(BX('reports-filter-base-andor-selector'));
		}

		var parent = col.parentNode;
		addFilterColumn(parent, col);
	});

	var minusButt = BX.findChild(newCol, {className:'reports-filter-del-item'}, true);
	BX.bind(minusButt, 'click', function(e) {
		var col = this.parentNode.parentNode;

		// if last in root
		if (col.parentNode.getAttribute('level') == '1' && col.parentNode.childNodes.length == 1)
		{
			// replace by new empty filter
			addFilterColumn(BX('reports-filter-columns-container'));
		}

		if (col.parentNode.getAttribute('level') == '1' && col.parentNode.childNodes.length == 2)
		{
			// base andor selector
			BX.hide(BX('reports-filter-base-andor-selector'));
		}

		// remove andor if it's empty and not root
		var andorChildCount = BX.findChildren(col.parentNode, {tag:'div', className:'reports-filter-item'}).length;
		if (col.parentNode.getAttribute('level') != '1' && andorChildCount == 1)
		{
			//var andorMinusButt = col.parentNode.findChild(newCol, {className:'reports-filter-del-item'}, true);
			var andorMinusButt = BX.findChild(col.parentNode, {className:'reports-filter-del-item'}, true);
			BX.fireEvent(andorMinusButt, 'click');
			return false;
		}

		BX.remove(col);
	});

	var andorButt = BX.findChild(newCol, {className:'reports-filter-and-or'}, true);
	if (level > 4)
	{
		BX.addClass(andorButt, 'reports-filter-and-or-disable');
	}
	else
	{
		BX.bind(andorButt, 'click', function(e) {
			var col = this.parentNode.parentNode;

			// if add in root
			if (col.parentNode.getAttribute('level') == '1')
			{
				BX.show(BX('reports-filter-base-andor-selector'));
			}

			addFilterAndor(col, 2);
		});
	}



	// add column to page
	if (afterElem == null) {
		//beforeElem = BX.findChild(fcContainer, {className:'reports-filter-checkbox-title'});
		//fcContainer.insertBefore(newCol, beforeElem);
		fcContainer.appendChild(newCol);
	}
	else {
		BX.insertAfter(fcContainer, newCol, afterElem);
	}

	return newCol;
}

function addFilterAndor(afterElem, sumColumnsCount)
{
	var newCol = BX.clone(BX('reports-filter-andor-container-example'), true);
	var fcContainer = afterElem ? afterElem.parentNode : BX('reports-filter-columns-container'); // filter columns container
	var level = fcContainer.getAttribute('level') - 0 + 1;

	if (level > 4) {
		alert('too much');
		return false;
	}

	// remove example stuff
	newCol.style.display = '';
	newCol.setAttribute('id', '');

	newCol.setAttribute('level', level);

	if (level > 2) {
		BX.addClass(newCol, 'reports-filter-sub-lev');
		BX.addClass(newCol, 'reports-filter-' + (level - 1) + '-lev');
	}

	// show root andor
	if (level == 2)
	{
		BX.show(BX('reports-filter-base-andor-selector'));
	}

	// add Events
	var plusButt = BX.findChild(newCol, {className:'reports-filter-add-item'}, true);
	BX.bind(plusButt, 'click', function(e) {
		var col = this.parentNode.parentNode.parentNode;
		var parent = col.parentNode;
		addFilterColumn(parent, col);
	});

	var minusButt = BX.findChild(newCol, {className:'reports-filter-del-item'}, true);
	BX.bind(minusButt, 'click', function(e) {
		var col = this.parentNode.parentNode;
		var andorContainer = col.parentNode;

		// if not last in root
		if (andorContainer.parentNode.getAttribute('level') == '1' && andorContainer.parentNode.childNodes.length == 1)
		{
			return false;
		}

		if (andorContainer.parentNode.getAttribute('level') == '1' && andorContainer.parentNode.childNodes.length == 2)
		{
			// base andor selector
			BX.hide(BX('reports-filter-base-andor-selector'));
		}

		BX.remove(andorContainer);
	});

	var andorButt = BX.findChild(newCol, {className:'reports-filter-and-or'}, true);
	if (level > 3)
	{
		BX.addClass(andorButt, 'reports-filter-and-or-disable');
	}
	else
	{
		BX.bind(andorButt, 'click', function(e) {
			var col = this.parentNode.parentNode.parentNode;
			addFilterAndor(col, 2);
		});
	}

	var andorSelector = BX.findChild(newCol, {tag:'select'}, true);
	BX.bind(andorSelector, 'change', function(){
		BX.findNextSibling(this, {className:'reports-limit-res-select-lable-or'}).style.display = this.value == 'OR'
			? 'inline-block' : 'none';

		BX.findNextSibling(this, {className:'reports-limit-res-select-lable-and'}).style.display = this.value == 'AND'
			? 'inline-block' : 'none';
	});
	andorSelector.setAttribute('filterId', GLOBAL_REPORT_FILTER_COUNT++);


	// add column to page
	BX.insertAfter(fcContainer, newCol, afterElem);

	// add first column to andor container
	//BX.fireEvent(plusButt, 'click');
	// ^ runs 2 times in IE 7-9
	// bug #21157
	var i;
	for (i=0; i<sumColumnsCount;i++)
	{
		addFilterColumn(plusButt.parentNode.parentNode.parentNode, plusButt.parentNode.parentNode);
	}

	return newCol;
}

function baseSelectorChangeEvent(e, select)
{
	var obj = select || this;

	BX('reports-filter-base-andor-selector-text-or').style.display = obj.value == 'OR'
		? 'inline-block' : 'none';

	BX('reports-filter-base-andor-selector-text-and').style.display = obj.value == 'AND'
		? 'inline-block' : 'none';
}

function restoreSubFilter(parent, filter)
{
	var filters = GLOBAL_PRE_FILTERS;
	var container = parent || BX('reports-filter-columns-container');

	var andor = parent
		? BX.findChild(container, {className:'reports-filter-andor-item'})
		: BX('reports-filter-base-andor-selector');

	setSelectValue(BX.findChild(andor, {tag:'select'}), filter['LOGIC']);

	var lastElem = null;
	var newCol = null;
	var i;
	for (i in filter)
	{
		if (!filter.hasOwnProperty(i))
			continue;

		if (i == 'LOGIC')
		{
			continue;
		}

		var subFilter = filter[i];

		if (subFilter.type == 'field')
		{
			// add empty column
			newCol = addFilterColumn(container);

			// fill column name
			var iCheckbox = BX.findChild(BX('reports-add_filcol-popup-cont'), {attr:{type:'checkbox', name: subFilter.name}}, true);
			var fControl = BX.findChild(iCheckbox.parentNode.parentNode, {className:'reports-add-popup-it-text'}, true);
			var fChoose = BX.findChild(newCol, {className:'reports-filter-item-name'}, true);
			LAST_FILCOL_CALLED = fChoose;
			fillFilterColumnEvent(null, fControl);

			// fill column compare
			var sel = BX.findChild(newCol, {attr:{name:'compare'}});
			setSelectValue(sel, subFilter.compare);

			// fill column value. fffuuuu
			var vControl = BX.findChild(newCol, {attr:{name:'value'}}, true);

			if (vControl)
			{
				if (vControl.getAttribute('type') == 'hidden')
				{
					vControl = vControl.parentNode;
				}

				switch (vControl.nodeName.toLowerCase())
				{
					case 'input':
						vControl.value = subFilter.value;
						break;
					case 'select':
						setSelectValue(vControl, subFilter.value);
						break;
					default:
						if (vControl.getAttribute('callback') != null)
						{
							var callBack = vControl.getAttribute('callback');
							var callerName = callBack + '_LAST_CALLER';
							var cFunc = callBack + 'Catch';
							var caller = BX.findChild(vControl, {attr:'caller'}, true);
							window[callerName] = caller;
							window[cFunc](subFilter.value);
						}
				}
			}
			else
			{
				var dashed, ufSelector, isUF, ufId, ufName, ufSelectorIndex;
				if (BX.hasClass(newCol, 'reports-filter-item'))
				{
					dashed = BX.findChild(newCol, {className:'reports-dashed'}, true);
					ufSelector = null;
					isUF = (dashed && parseInt(dashed.getAttribute('isUF')) === 1);
					if (isUF)
					{
						ufId = dashed.getAttribute('ufId');
						ufName = dashed.getAttribute('ufName');
						ufSelectorIndex = parseInt(dashed.getAttribute('ufSelectorIndex'));

						if (ufId && ufName)
						{
							if (BX.Report && BX.Report.FilterFieldSelectorManager)
								ufSelector = BX.Report.FilterFieldSelectorManager.getSelector(ufId, ufName);
							if (ufSelector)
								ufSelector.setFilterValue(ufSelectorIndex, subFilter.value);
						}
					}
				}

			}

			// fill changeable flag
			BX.findChild(newCol, {attr: {name: 'changeable'}}, true).checked = !!parseInt(subFilter.changeable);

			// yay!
			lastElem = newCol;
		}
		else if (subFilter.type == 'filter')
		{
			newCol = addFilterAndor(lastElem);
			restoreSubFilter(newCol, filters[subFilter.name]);
			lastElem = newCol;
		}
	}
}

function startSubFilterRestore()
{
	if (GLOBAL_PRE_FILTERS != null)
	{
		var filters = GLOBAL_PRE_FILTERS;

		restoreSubFilter(null, filters[0]);
	}
	else
	{
		// add empty filter column
		addFilterColumn(BX('reports-filter-columns-container'));
	}
}

function setReportLimit(checked, num)
{
	var limitCheckbox = BX('report-filter-limit-checkbox');
	var limitInput = BX('report-filter-limit-input');
	if (arguments.length > 0)
	{
		limitCheckbox.checked = !!checked;
		if (arguments.length === 2) limitInput.value = parseInt(num); // set limit number
	}
	if (limitCheckbox.checked) {
		limitInput.disabled = false;
		limitInput.style.backgroundColor = '#ffffff';
		//BX.focus(limitInput);
	}
	else {
		limitInput.disabled = true;
		limitInput.style.backgroundColor = '#eeeeee';
	}
}

function enableReportLimit(flag)
{
	var limitCheckbox = BX('report-filter-limit-checkbox');
	var limitInput = BX('report-filter-limit-input');
	limitCheckbox.disabled = !flag;
	limitInput.disabled = !flag;
}

function initFilterControls()
{
	BX.ready(function() {

		BX.insertAfter = function(parentNode, newElement, referenceElement) {
			var beforeElem = null;
			var found = false;

			for (var i=0; i<=parentNode.childNodes.length; i++)
			{
				if (found) {
					beforeElem = parentNode.childNodes[i];
					break;
				}

				if (parentNode.childNodes[i] == referenceElement) {
					found = true;
				}
			}

			if (beforeElem != null) {
				parentNode.insertBefore(newElement, beforeElem);
			}
			else if (found) {
				parentNode.appendChild(newElement);
			}

			return newElement;
		};

		//  initialize filter columns
		BX('reports-filter-columns-container').setAttribute('level', 1);

		// base andor selector logic
		var sel = BX.findChild(BX('reports-filter-base-andor-selector'), {tag:'select'}, true);
		BX.bind(sel, 'change', baseSelectorChangeEvent);

		// limit results
		BX.bind(BX('report-filter-limit-checkbox'), 'click', function(e) {
			setReportLimit();
			BX.focus(BX('report-filter-limit-input'));
		});
	});
}

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="save">

function createHiddenInput(name, value)
{
	return BX.create('input', {props:{type:'hidden', name:name, value:value}});
}

function parseFilterElems(fContainer, filters, fNum)
{
	var fElems = BX.findChildren(fContainer, {tag:'div'});
	var fElemCount = 0;
	var fValue = null, dashed = null;
	var isUF, ufId, ufName, ufSelector, ufSelectorIndex;

	var i, fElem, compareControl;

	for (i in fElems)
	{
		if (!fElems.hasOwnProperty(i))
			continue;

		fElem = {};

		if (BX.hasClass(fElems[i], 'reports-filter-item'))
		{
			fElem.type = 'field';
			dashed = BX.findChild(fElems[i], {className:'reports-dashed'}, true);
			fElem.name = dashed.getAttribute('fieldDefinition');

			if (fElem.name == null)
			{
				continue;
			}

			ufSelector = null;
			isUF = (parseInt(dashed.getAttribute('isUF')) === 1);
			if (isUF)
			{
				ufId = dashed.getAttribute('ufId');
				ufName = dashed.getAttribute('ufName');
				ufSelectorIndex = parseInt(dashed.getAttribute('ufSelectorIndex'));

				if (ufId && ufName)
				{
					if (BX.Report && BX.Report.FilterFieldSelectorManager)
						ufSelector = BX.Report.FilterFieldSelectorManager.getSelector(ufId, ufName);
				}
			}

			compareControl = BX.findChild(fElems[i], {attr:{name:'compare'}}, true);
			if (!compareControl)
			{
				continue;
			}
			fElem.compare = compareControl.value;
			fValue = BX.findChild(fElems[i], {attr:{name:'value'}}, true);
			if (fValue)
			{
				if (fValue.tagName === 'SELECT' && fValue.getAttribute('multiple') === 'multiple')
				{
					var opts = fValue.options;
					var arVal = [];
					var valIndex = 0;
					for (var optIndex = 0; optIndex < opts.length; optIndex++)
					{
						if (opts[optIndex].selected) arVal[valIndex++] = opts[optIndex].value;
					}
					fElem.value =  (arVal.length > 0) ? arVal : '';
				}
				else fElem.value = fValue.value;
			}
			else if (ufSelector)
			{
				fElem.value = ufSelector.getFilterValue(ufSelectorIndex);
			}
			fElem.changeable = BX.findChild(fElems[i], {attr:{name:'changeable'}}, true).checked ? "1" : "0";
		}
		else if (BX.hasClass(fElems[i], 'reports-filter-andor-container'))
		{
			fElem.type = 'filter';
			fElem.name = BX.findChild(fElems[i], {tag:'select'}, true).getAttribute('filterId');

			filters[fElem.name] = {};

			var logicContainer = BX.findChild(fElems[i], {className:'reports-filter-andor-item'});
			filters[fElem.name]['logic'] = BX.findChild(logicContainer, {tag:'select'}).value;

			parseFilterElems(fElems[i], filters, fElem.name);
		}
		else
		{
			continue;
		}

		filters[fNum][fElemCount++] = fElem;
	}
}

function initSaveButton()
{
	BX.ready(function() {
		BX.bind(BX('report-save-button'), 'click', function (e){

			BX.PreventDefault(e);

			var filters = {};

			// build filters scheme
			// root filter
			var fContainer = BX('reports-filter-columns-container');
			filters[0] = {};
			filters[0]['logic'] = BX.findChild(BX('reports-filter-base-andor-selector'), {tag:'select'}).value;

			// root elems and other filters
			parseFilterElems(fContainer, filters, 0);

			// insert values into form
			var form = BX('task-filter-form');

			var i, j, k, l;
			var fId, fElems, fElem, fVals;

			for (i in filters)
			{
				if (!filters.hasOwnProperty(i))
					continue;

				fId = i;
				fElems = filters[i];

				for (j in fElems)
				{
					if (!fElems.hasOwnProperty(j))
						continue;

					fElem = fElems[j];

					if (j == 'logic')
					{
						form.appendChild(createHiddenInput('filters['+i+']['+j+']', fElem));
					}
					else
					{
						for (k in fElem)
						{
							if (!fElem.hasOwnProperty(k))
								continue;

							if (fElem[k] instanceof Array)
							{
								fVals = fElem[k];
								for (l in fVals)
								{
									if (fVals.hasOwnProperty(l))
									{
										form.appendChild(
											createHiddenInput('filters['+i+']['+j+']['+k+']['+l+']', fVals[l])
										);
									}
								}
							}
							else form.appendChild(createHiddenInput('filters['+i+']['+j+']['+k+']', fElem[k]));
						}
					}
				}
			}

			BX('task-filter-form').submit();

		});
	});
}

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="filter popup">

function fillFilterColumnEvent(e, popupElem)
{
	var obj = popupElem || this;

	var isUF, fieldType, ufId, ufName;
	var iCheckBox = BX.findChild(obj.parentNode, {tag:'input', attr:{type:'checkbox'}}, true);
	isUF = (parseInt(iCheckBox.getAttribute('isUF')) === 1);
	fieldType = iCheckBox.getAttribute('fieldType');
	if (isUF)
	{
		ufId = iCheckBox.getAttribute('ufId');
		ufName = iCheckBox.getAttribute('ufName');
	}
	var dashed = BX.findChild(LAST_FILCOL_CALLED, {className:'reports-dashed'});
	dashed.innerHTML = BX.util.htmlspecialchars(iCheckBox.title);
	dashed.title = iCheckBox.title;
	dashed.setAttribute('fieldDefinition', iCheckBox.name);
	dashed.setAttribute('fieldType', fieldType);
	if (isUF && ufId)
	{
		dashed.setAttribute('isUF', "1");
		dashed.setAttribute('ufId', ufId);
		dashed.setAttribute('ufName', ufName);
	}

	// remove existing compare controls and value controls
	var colContainer = LAST_FILCOL_CALLED.parentNode;
	var currSelects = BX.findChildren(colContainer, {className:'reports-filter-column-helper'});
	for (var i in currSelects)
	{
		if (currSelects.hasOwnProperty(i))
			BX.remove(currSelects[i]);
	}

	// replace "compare type" select
	var cpSelect = BX.clone(
		BX('report-filter-compare-'+iCheckBox.name)
			|| BX('report-filter-compare-'+iCheckBox.getAttribute('fieldType')),
		true
	);
	//var cpSelect = ;
	if (!cpSelect)
	{
		return;
	}
	cpSelect.id = '';
	cpSelect.name = 'compare';
	BX.addClass(cpSelect, 'reports-filter-column-helper');
	var beforeSibling = BX.findChild(colContainer, {className:'reports-filter-butt-wrap'});
	colContainer.insertBefore(cpSelect, beforeSibling);

	var cpControl = null;
	var tipicalControl = true;
	if (isUF && fieldType === 'enum')
	{
		cpControl = BX.clone(
			BX.findChild(
				BX('report-filter-value-control-examples-ufenums'),
				{attr:{name:'report-filter-value-control-' + ufId + '_' + ufName}}
			),
			true
		);
	}
	else if (isUF
			&& (fieldType === "crm" || fieldType === "crm_status"
				|| fieldType === "iblock_element" || fieldType === "iblock_section"))
	{
		var filterFieldSelector = null;
		if (BX.Report && BX.Report.FilterFieldSelectorManager)
			filterFieldSelector = BX.Report.FilterFieldSelectorManager.getSelector(ufId, ufName);
		if (filterFieldSelector)
		{
			cpControl = filterFieldSelector.makeFilterField(colContainer, beforeSibling);
			if (cpControl)
			{
				tipicalControl = false;
				var selectorIndex = cpControl.getAttribute("ufSelectorIndex");
				if (selectorIndex.length > 0)
					dashed.setAttribute("ufSelectorIndex", selectorIndex);
			}
		}
	}

	if (!cpControl)
	{
		// replace value control
		// search in `examples-custom` by name or type
		// then search in `examples` by type
		cpControl = BX.clone(
			BX.findChild(
				BX('report-filter-value-control-examples-custom'),
				{attr:{name:'report-filter-value-control-'+iCheckBox.name}}
			)
			||
			BX.findChild(
				BX('report-filter-value-control-examples-custom'),
				{attr:{name:'report-filter-value-control-'+fieldType}}
			)
			||
			BX.findChild(
				BX('report-filter-value-control-examples'),
				{attr:{name:'report-filter-value-control-'+fieldType}}
			),
			true
		);
	}

	BX.addClass(cpControl, 'reports-filter-column-helper');
	if (cpControl.getAttribute('callback') != null)
	{
		var cbFuncName = cpControl.getAttribute('callback');
		window[cbFuncName](cpControl);
	}

	if (iCheckBox.getAttribute('fieldType') == 'datetime')
	{
		var calButt = BX.findChild(cpControl, {tag:'img'});
		BX.bind(calButt, "click", function(e) {
			if (!e) e = window.event;

			var valueInput = BX.findChild(this.parentNode, {attr:{name:'value'}});

			var curDate = new Date();
			var curTimestamp = Math.round(curDate / 1000) - curDate.getTimezoneOffset()*60;

			BX.calendar({
				node: this,
				field: valueInput,
				bTime: false
			});

			BX.PreventDefault(e);
		});
	}

	if (tipicalControl)
		colContainer.insertBefore(cpControl, beforeSibling);

	// close popup
	var p = BX.findParent(obj, {callback: function(o){
		return (o.id.substr(0, 21) == 'reports-add_col-popup');
	}});
	var closeButt = BX.findChild(p, {className:'popup-window-close-icon'});

	try
	{
		BX.fireEvent(closeButt, 'click');
	}
	catch (e)
	{}

}

function initFilterPopupItems()
{
	BX.ready(function() {
		var fList = BX.findChildren(BX('reports-add_filcol-popup-cont'), {className:'reports-add-popup-it-text'}, true);
		var iCheckBox, cpSelect, doHide = true;

		for (var i in fList)
		{
			if (!fList.hasOwnProperty(i))
				continue;

			if (BX.hasClass(fList[i].parentNode, 'reports-add-popup-it-node'))
			{
				// ignore branch heads
				continue;
			}

			doHide = true;
			iCheckBox = BX.findChild(fList[i].parentNode, {tag:'input', attr:{type:'checkbox'}}, true);
			if (iCheckBox)
			{
				cpSelect = BX.clone(
					BX('report-filter-compare-'+iCheckBox.name)
					|| BX('report-filter-compare-'+iCheckBox.getAttribute('fieldType')),
					true
				);
				if (cpSelect)
				{
					doHide = false;
					BX.bind(fList[i], 'click', fillFilterColumnEvent);
				}
			}

			// hide elements without controls for compare
			if (doHide)
			{
				fList[i].parentNode.style.display = "none";
			}
		}
	});
}

// </editor-fold>

function initReportControls()
{
	initIntervalFilter();
	initSelectColumnButton();
	initFilterControls();
	initSaveButton();
	initFilterPopupItems();
}