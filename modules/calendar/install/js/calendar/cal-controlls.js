var ECCalendarAccess = function(Params)
{
	BX.Access.Init();
	if (!window.EC_MESS)
		EC_MESS = {};

	this.bind = Params.bind;
	this.GetAccessName = Params.GetAccessName;
	this.pTbl = Params.pCont.appendChild(BX.create("TABLE", {props: {className: "bxc-access-tbl"}}));
	this.pSel = BX('bxec-' + this.bind);
	var _this = this;
	this.delTitle = Params.delTitle || EC_MESS.Delete;
	this.noAccessRights = Params.noAccessRights || EC_MESS.NoAccessRights;

	this.inputName = Params.inputName || false;

	Params.pLink.onclick = function(){
		BX.Access.ShowForm({
			callback: BX.proxy(_this.InsertRights, _this),
			bind: _this.bind
		});
	};
};

ECCalendarAccess.prototype = {
	InsertRights: function(obSelected)
	{
		var provider, code;
		for(provider in obSelected)
			for(code in obSelected[provider])
				this.InsertAccessRow(BX.Access.GetProviderName(provider) + ' ' + obSelected[provider][code].name, code);
	},

	InsertAccessRow: function(title, code, value)
	{
		var _this = this, row, pLeft, pRight, pTaskSelect;
		if (this.pTbl.rows[0] && this.pTbl.rows[0].cells[0] && this.pTbl.rows[0].cells[0].className.indexOf('bxc-access-no-vals') != -1)
			this.DeleteRow(0);

		row = this.pTbl.insertRow(-1);
		pLeft = BX.adjust(row.insertCell(-1), {props : {className: 'bxc-access-c-l'}, html: title + ':'});
		pRight = BX.adjust(row.insertCell(-1), {props : {className: 'bxc-access-c-r'}});
		pTaskSelect = pRight.appendChild(this.pSel.cloneNode(true));
		//pTaskSelect.name = 'BXEC_ACCESS_' + code;
		pTaskSelect.id = 'BXEC_ACCESS_' + code;

		if (value)
			pTaskSelect.value = value;
		pDel = pRight.appendChild(BX.create('A', {props:{className: 'access-delete', href: 'javascript:void(0)', title: this.delTitle}, events: {click: function(){_this.DeleteRow(this.parentNode.parentNode.rowIndex);}}}));

		if (this.inputName)
		{
			pTaskSelect.name = this.inputName + '[' + code + ']';
			//pRight.appendChild(BX.create('INPUT', {props:{type: 'hidden', value: this.inputName + '[' + code + ']'}}));
		}
	},

	DeleteRow: function(rowIndex)
	{
		if (this.pTbl.rows[rowIndex])
			this.pTbl.deleteRow(rowIndex);
	},

	GetValues: function()
	{
		var
			id, taskId,
			res = {},
			arSelect = this.pTbl.getElementsByTagName("SELECT"),
			i, l = arSelect.length;

		for(i = 0; i < l; i++)
		{
			id = arSelect[i].id.substr('BXEC_ACCESS_'.length);
			taskId = arSelect[i].value;
			res[id] = taskId;
		}

		return res;
	},

	SetSelected: function(oAccess)
	{
		if (!oAccess)
			oAccess = {};

		while (this.pTbl.rows[0])
			this.pTbl.deleteRow(0);

		var
			code,
			oSelected = {};

		for (code in oAccess)
		{
			this.InsertAccessRow(this.GetTitleByCode(code), code, oAccess[code]);
			oSelected[code] = true;
		}

		// Insert 'no value'  if no permissions exists
		if (this.pTbl.rows.length <= 0)
			BX.adjust(this.pTbl.insertRow(-1).insertCell(-1), {props : {className: 'bxc-access-no-vals', colSpan: 2}, html: '<span>' + this.noAccessRights + '</span>'});

		BX.Access.SetSelected(oSelected, this.bind);
	},

	GetTitleByCode: function(code)
	{
		return this.GetAccessName(code);
	}
};