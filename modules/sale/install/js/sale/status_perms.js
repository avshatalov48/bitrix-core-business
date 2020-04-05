/*checked
*	arParams
*		GROUPS			- array with group IDs
*		PERM_LIST		- array with perm
*		PERM_VIEW		- string with perm view name
*		PERM_ALL		- string with perm all name
*	Variables
*		intERROR
*		GROUPS
*		PERM_LIST
*		CHECK_COUNT
*/
function JCSaleStatusPerms(arParams)
{
	if (!arParams) return;

	this.intERROR = 0;
	this.GROUPS = [];
	if (!!arParams.GROUPS && BX.type.isArray(arParams.GROUPS))
		this.GROUPS = BX.clone(arParams.GROUPS, true);
	if (0 == this.GROUPS.length)
		return;
	this.PERM_LIST = [];
	if (!!arParams.PERM_LIST && BX.type.isArray(arParams.PERM_LIST))
		this.PERM_LIST = BX.clone(arParams.PERM_LIST, true);
	if (0 == this.PERM_LIST.length)
		return;
	this.PERM_VIEW = '';
	if (!!arParams.PERM_VIEW && BX.type.isNotEmptyString(arParams.PERM_VIEW))
		this.PERM_VIEW = arParams.PERM_VIEW;
	this.PERM_ALL = '';
	if (!!arParams.PERM_ALL && BX.type.isNotEmptyString(arParams.PERM_ALL))
		this.PERM_ALL = arParams.PERM_ALL;
	
	this.CHECK_COUNT = [];
	BX.ready(BX.delegate(this.Init,this));
}

JCSaleStatusPerms.prototype.Init = function()
{
	if (0 > this.intERROR)
		return;
	
	var cb = BX.proxy(this.ChangeOne, this);
	var	gcb = function(perm){
		return function(e){
			return cb(e,perm);
		};
	};
	
	for (var i = 0; i < this.GROUPS.length; i++)
	{
		var intCount = 0;
		var boolFlag = false;
		var obAll = BX(this.PERM_ALL+'_'+this.GROUPS[i], true);
		var obView = BX(this.PERM_VIEW+'_'+this.GROUPS[i], true);
		BX.bind(obAll, 'click', BX.proxy(function(e){this.ChangeAll(e);}, this));
		for (var j = 0; j < this.PERM_LIST.length; j++)
		{
			var obOnePerm = BX(this.PERM_LIST[j]+'_'+this.GROUPS[i], true);
			BX.bind(obOnePerm, 'click', gcb(this.PERM_LIST[j]));
			if (obOnePerm.checked)
			{
				intCount++;
				boolFlag = true;
			}
		}
		if (boolFlag)
			obView.checked = true;
		if (intCount == this.PERM_LIST.length)
			obAll.checked = true;
		this.CHECK_COUNT[i] = intCount;
	}
};

JCSaleStatusPerms.prototype.ChangeAll = function(e)
{
	if(!e)
		e = window.event;
	if (0 > this.intERROR)
		return;
	var s = (BX.browser.IsIE() ? e.srcElement.id : e.target.id);

	if (!s)
		return;
	var obAll = BX(s, true);
	var GroupID = parseInt(s.replace(this.PERM_ALL+'_',''));
	if (isNaN(GroupID))
		return;
	var key = BX.util.array_search(GroupID, this.GROUPS);
	if (-1 == key)
		return;
	this.CHECK_COUNT[key] = (obAll.checked ? this.PERM_LIST.length : 0);
	for (var i = 0; i < this.PERM_LIST.length; i++)
	{
		var obOnePerm = BX(this.PERM_LIST[i]+'_'+GroupID, true);
		obOnePerm.checked = obAll.checked;
		if (this.PERM_VIEW == this.PERM_LIST[i])
		{
			obOnePerm.disabled = obAll.checked;
		}
	}
};

JCSaleStatusPerms.prototype.ChangeOne = function(e, strPerm)
{
	if(!e)
		e = window.event;
	if (0 > this.intERROR)
		return;
	if (!!strPerm)
	{
		var s = (BX.browser.IsIE() ? e.srcElement.id : e.target.id);
		if (!s)
			return;
		obCurrent = BX(s, true);
		var GroupID = parseInt(s.replace(strPerm+'_',''));
		if (isNaN(GroupID))
			return;
		var key = BX.util.array_search(GroupID, this.GROUPS);
		if (-1 == key)
			return;
		var obView = BX(this.PERM_VIEW+'_'+GroupID, true);
		var obAll = BX(this.PERM_ALL+'_'+GroupID, true);
		if (obCurrent.checked)
		{
			if (strPerm != this.PERM_VIEW)
			{
				if (0 == this.CHECK_COUNT[key])
				{
					obView.checked = true;
					this.CHECK_COUNT[key]++;
				}
				obView.disabled = true;
			}
			this.CHECK_COUNT[key]++;
			if (this.CHECK_COUNT[key] > this.PERM_LIST.length)
				this.CHECK_COUNT[key] = this.PERM_LIST.length;
			if (this.CHECK_COUNT[key] == this.PERM_LIST.length)
			{
				obAll.checked = true;
			}
		}
		else
		{
			this.CHECK_COUNT[key]--;
			if (0 > this.CHECK_COUNT[key])
				this.CHECK_COUNT[key] = 0;
			obAll.checked = false;
			if (1 >= this.CHECK_COUNT[key])
			{
				obView.disabled = false;				
			}
		}
	}
};