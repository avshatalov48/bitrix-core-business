(function() {
var BX = window.BX;
if (BX.SocNetLogDestination)
{
	return;
}

BX.SocNetLogDestination =
{
	popupWindow: null,
	popupSearchWindow: null,
	containerWindow: null,

	bByFocusEvent: false,
	bLoadAllInitialized: false,

	createSocNetGroupWindow: null,
	inviteEmailUserWindow: null,
	inviteEmailUserWindowSubmitted: false,
	inviteEmailCurrentName: null,

	sendEvent: true,
	extranetUser: false,

	obUseContainer: {},
	obShowSearchInput: {},
	obSendAjaxSearch: {},

	obUserNameTemplate: {},

	obCurrentElement: {
		last: null,
		search: null,
		group: false
	},
	obSearchFirstElement: null,
	obResult: {
		last: null,
		email: null,
		crmemail: null,
		search: null,
		group: false
	},
	obCursorPosition: {
		last: null,
		email: null,
		crmemail: null,
		search: null,
		group: false
	},

	obTabs: {},
	obCustomTabs: {},

	focusOnTabs: false,

	searchTimeout: null,
	createSonetGroupTimeout: null,

	obAllowAddSocNetGroup: {},
	obAllowAddUser: {},
	obAllowAddCrmContact: {},
	obAllowSearchEmailUsers: {},
	obAllowSearchCrmEmailUsers: {},
	obAllowSearchNetworkUsers: {},
	obAllowSearchSelf: {},
	obAllowSonetGroupsAjaxSearch: {},
	obAllowMailContactsAjaxSearch: {},
	obAllowSonetGroupsAjaxSearchFeatures: {},

	obEmptySearchResult: {},
	obNewSocNetGroupCnt: {},

	obEmailDescMode: {},
	obSearchOnlyWithEmail: {},
	obDepartmentEnable: {},
	obSonetgroupsEnable: {},
	obProjectsEnable: {},
	obLastEnable: {},

	arDialogGroups: {},

	obWindowClass: {},
	obWindowCloseIcon: {},
	obPathToAjax: {},
	obDepartmentLoad: {},
	obDepartmentSelectDisable: {},
	obUserSearchArea: {},
	obItems: {},
	obItemsLast: {},
	obItemsSelected: {},
	obItemsSelectedUndeleted: {},
	obCallback: {},
	obShowVacations : {},

	obElementSearchInput: {},
	obElementSearchInputHidden: {},
	obElementBindMainPopup: {},
	obElementBindSearchPopup: {},
	obBindOptions: {},

	obSiteDepartmentID: {},

	obCrmFeed: {},
	obCrmTypes: {},
	obCrmMyCompany: {},
	obAllowUserSearch: {},

	bFinderInited: false,
	obClientDb: null,
	obClientDbData: {},
	obClientDbDataSearchIndex: {},

	oDbUserSearchResult: {},
	oAjaxUserSearchResult: {},

	obDestSort: {},

	oSearchWaiterEnabled: {},
	oSearchWaiterContentHeight: 0,

	obUseClientDatabase: {},

	bResultMoved: {
		search: false,
		last: false,
		group: false
	}, // cursor move
	oXHR: null,
	usersVacation : null,

	obTabSelected: {},

	obTemplateClass: {
		1: 'bx-finder-box-item',
		2: 'bx-finder-box-item-t2',
		3: 'bx-finder-box-item-t3',
		4: 'bx-finder-box-item-t3',
		5: 'bx-finder-box-item-t5',
		6: 'bx-finder-box-item-t6',
		7: 'bx-finder-box-item-t7',
		'department-user': 'bx-finder-company-department-employee-selected',
		'department': 'bx-finder-company-department-check-checked'
	},

	obTemplateClassSelected: {
		1: 'bx-finder-box-item-selected',
		2: 'bx-finder-box-item-t2-selected',
		3: 'bx-finder-box-item-t3-selected',
		4: 'bx-finder-box-item-t3-selected',
		5: 'bx-finder-box-item-t5-selected',
		6: 'bx-finder-box-item-t6-selected',
		7: 'bx-finder-box-item-t7-selected',
		'department-user': 'bx-finder-company-department-employee-selected',
		'department': 'bx-finder-company-department-check-checked'
	},

	searchStarted : false,
	tmpSearchResult : {
		client: [],
		ajax: []
	}
};

BX.SocNetLogDestination.init = function(arParams)
{
	var
		i = null,
		type = null;

	if(!arParams.name)
	{
		arParams.name = 'lm';
	}

	BX.SocNetLogDestination.obPathToAjax[arParams.name] = (!arParams.pathToAjax ? '/bitrix/components/bitrix/main.post.form/post.ajax.php' : arParams.pathToAjax);

	BX.SocNetLogDestination.obShowSearchInput[arParams.name] = (
		typeof arParams.showSearchInput != 'undefined'
		&& !!arParams.showSearchInput
	);

	BX.SocNetLogDestination.obSendAjaxSearch[arParams.name] = (
		typeof arParams.sendAjaxSearch != 'undefined'
			? !!arParams.sendAjaxSearch
			: true
	);

	BX.SocNetLogDestination.obUseContainer[arParams.name] = (
		BX.SocNetLogDestination.obShowSearchInput[arParams.name]
		|| (
			typeof arParams.useContainer != 'undefined'
			&& !!arParams.useContainer
		)
	);

	BX.SocNetLogDestination.obUserNameTemplate[arParams.name] = (typeof arParams.userNameTemplate != 'undefined' ? arParams.userNameTemplate : '');
	BX.SocNetLogDestination.obCallback[arParams.name] = arParams.callback;

	BX.SocNetLogDestination.obElementBindMainPopup[arParams.name] = arParams.bindMainPopup;
	BX.SocNetLogDestination.obElementBindSearchPopup[arParams.name] = arParams.bindSearchPopup;
	BX.SocNetLogDestination.obElementSearchInput[arParams.name] = arParams.searchInput;
	BX.SocNetLogDestination.obElementSearchInputHidden[arParams.name] = (typeof arParams.searchInputHidden != 'undefined' ? arParams.searchInputHidden : false);

	BX.SocNetLogDestination.obBindOptions[arParams.name] = (typeof arParams.bindOptions != 'undefined' ? arParams.bindOptions : {});
	BX.SocNetLogDestination.obBindOptions[arParams.name].forceBindPosition = true;

	BX.SocNetLogDestination.obDepartmentSelectDisable[arParams.name] = (arParams.departmentSelectDisable == true);
	BX.SocNetLogDestination.obUserSearchArea[arParams.name] = (BX.util.in_array(arParams.userSearchArea, ['I', 'E']) ? arParams.userSearchArea : false);
	BX.SocNetLogDestination.obDepartmentLoad[arParams.name] = {};
	BX.SocNetLogDestination.obWindowClass[arParams.name] = (!arParams.obWindowClass ? 'bx-lm-socnet-log-destination' : arParams.obWindowClass);
	BX.SocNetLogDestination.obWindowCloseIcon[arParams.name] = (typeof (arParams.obWindowCloseIcon) == 'undefined' ? true : arParams.obWindowCloseIcon);
	BX.SocNetLogDestination.extranetUser = arParams.extranetUser;

	BX.SocNetLogDestination.obCrmFeed[arParams.name] = arParams.isCrmFeed;
	BX.SocNetLogDestination.obCrmTypes[arParams.name] = (
		arParams.isCrmFeed
		&& typeof arParams.CrmTypes == 'object'
		&& arParams.CrmTypes.length > 0
			? arParams.CrmTypes
			: []
	);
	BX.SocNetLogDestination.obCrmMyCompany[arParams.name] = BX.prop.getBoolean(arParams, "enableMyCrmCompanyOnly", false);

	BX.SocNetLogDestination.obAllowUserSearch[arParams.name] = !(typeof arParams.allowUserSearch != 'undefined' && arParams.allowUserSearch === false);

	BX.SocNetLogDestination.obAllowAddSocNetGroup[arParams.name] = (arParams.allowAddSocNetGroup === true);
	BX.SocNetLogDestination.obAllowAddUser[arParams.name] = (arParams.allowAddUser === true);
	BX.SocNetLogDestination.obAllowAddCrmContact[arParams.name] = (arParams.allowAddCrmContact === true);
	BX.SocNetLogDestination.obAllowSearchEmailUsers[arParams.name] = (
		typeof arParams.allowSearchEmailUsers != 'undefined'
			? (arParams.allowSearchEmailUsers === true)
			: (arParams.allowAddUser === true)
	);
	BX.SocNetLogDestination.obAllowSearchCrmEmailUsers[arParams.name] = (
		typeof arParams.allowSearchCrmEmailUsers != 'undefined'
			? (arParams.allowSearchCrmEmailUsers === true)
			: false
	);
	BX.SocNetLogDestination.obAllowSearchNetworkUsers[arParams.name] = (
		typeof arParams.allowSearchNetworkUsers != 'undefined'
			? (arParams.allowSearchNetworkUsers === true)
			: false
	);
	BX.SocNetLogDestination.obAllowSearchSelf[arParams.name] = (
		typeof arParams.allowSearchSelf != 'undefined'
			? arParams.allowSearchSelf !== false
			: true
	);

	BX.SocNetLogDestination.obEmailDescMode[arParams.name] =
		(typeof arParams.emailDescMode != 'undefined' ? (arParams.emailDescMode === true) : false);

	BX.SocNetLogDestination.obSearchOnlyWithEmail[arParams.name] =
		(typeof arParams.searchOnlyWithEmail != 'undefined' ? (arParams.searchOnlyWithEmail === true) : false);

	BX.SocNetLogDestination.obSiteDepartmentID[arParams.name] =
		(typeof (arParams.siteDepartmentID) != 'undefined' && parseInt(arParams.siteDepartmentID) > 0 ? parseInt(arParams.siteDepartmentID) : false);

	BX.SocNetLogDestination.obNewSocNetGroupCnt[arParams.name] = 0;

	BX.SocNetLogDestination.obLastEnable[arParams.name] = (arParams.lastTabDisable != true);
	BX.SocNetLogDestination.oDbUserSearchResult[arParams.name] = {};

	BX.SocNetLogDestination.obDestSort[arParams.name] = (typeof arParams.destSort != 'undefined' ? arParams.destSort : []);

	BX.SocNetLogDestination.obDepartmentEnable[arParams.name] = (!!arParams.enableDepartments);
	if (
		!BX.SocNetLogDestination.obDepartmentEnable[arParams.name]
		&& arParams.items.department
	)
	{
		for(i in arParams.items.department)
		{
			BX.SocNetLogDestination.obDepartmentEnable[arParams.name] = true;
			break;
		}
	}

	BX.SocNetLogDestination.obSonetgroupsEnable[arParams.name] = (!!arParams.enableSonetgroups);
	if (
		!BX.SocNetLogDestination.obSonetgroupsEnable[arParams.name]
		&& arParams.items.sonetgroups
	)
	{
		for(i in arParams.items.sonetgroups)
		{
			BX.SocNetLogDestination.obSonetgroupsEnable[arParams.name] = true;
			break;
		}
	}

	BX.SocNetLogDestination.obProjectsEnable[arParams.name] = (!!arParams.enableProjects);
	if (
		!BX.SocNetLogDestination.obProjectsEnable[arParams.name]
		&& BX.SocNetLogDestination.obSonetgroupsEnable[arParams.name]
		&& arParams.items.projects
	)
	{
		for(i in arParams.items.projects)
		{
			BX.SocNetLogDestination.obProjectsEnable[arParams.name] = true;
			break;
		}
	}

	BX.SocNetLogDestination.obAllowSonetGroupsAjaxSearch[arParams.name] = (
		BX.SocNetLogDestination.obSonetgroupsEnable[arParams.name]
		&& typeof arParams.allowSonetGroupsAjaxSearch != 'undefined'
		&& arParams.allowSonetGroupsAjaxSearch === true
	);

	BX.SocNetLogDestination.obAllowMailContactsAjaxSearch[arParams.name] = (typeof arParams.allowSearchEmailContacts != 'undefined' ? arParams.allowSearchEmailContacts : false);

	BX.SocNetLogDestination.obAllowSonetGroupsAjaxSearchFeatures[arParams.name] = (
		BX.SocNetLogDestination.obSonetgroupsEnable[arParams.name]
		&& typeof arParams.allowSonetGroupsAjaxSearchFeatures != 'undefined'
			? arParams.allowSonetGroupsAjaxSearchFeatures
			: {}
	);

	BX.SocNetLogDestination.obUseClientDatabase[arParams.name] = true;

	if (
		typeof arParams.useClientDatabase != 'undefined'
		&& arParams.useClientDatabase === false
	)
	{
		BX.SocNetLogDestination.obUseClientDatabase[arParams.name] = false;
	}

	BX.SocNetLogDestination.obTabs[arParams.name] = [];
	if (BX.SocNetLogDestination.obLastEnable[arParams.name])
	{
		BX.SocNetLogDestination.obTabs[arParams.name].push('last');
	}
	if (BX.SocNetLogDestination.obSonetgroupsEnable[arParams.name])
	{
		BX.SocNetLogDestination.obTabs[arParams.name].push('group');
	}
	if (BX.SocNetLogDestination.obProjectsEnable[arParams.name])
	{
		BX.SocNetLogDestination.obTabs[arParams.name].push('project');
	}
	if (BX.SocNetLogDestination.obAllowSearchEmailUsers[arParams.name])
	{
		BX.SocNetLogDestination.obTabs[arParams.name].push('mailContacts');
	}
	if (BX.SocNetLogDestination.obDepartmentEnable[arParams.name])
	{
		BX.SocNetLogDestination.obTabs[arParams.name].push('department');
	}
	if (BX.SocNetLogDestination.obAllowSearchEmailUsers[arParams.name])
	{
		BX.SocNetLogDestination.obTabs[arParams.name].push('email');
	}
	if (BX.SocNetLogDestination.obAllowSearchCrmEmailUsers[arParams.name])
	{
		BX.SocNetLogDestination.obTabs[arParams.name].push('crmemail');
	}

	BX.addCustomEvent(BX.SocNetLogDestination, "onTabsAdd", BX.delegate(this.onTabsAdd, this));

	BX.SocNetLogDestination.arDialogGroups[arParams.name] = [];

	var _getGroupParam = function (dialogGroup, paramName, defaultValue) {
		if(
			typeof arParams.dialogGroupParams !== 'undefined' &&
			typeof arParams.dialogGroupParams[dialogGroup] !== 'undefined' &&
			typeof arParams.dialogGroupParams[dialogGroup][paramName]!== 'undefined'
		){
			return arParams.dialogGroupParams[dialogGroup][paramName];
		}else if(paramName === 'emailDescMode' && typeof defaultValue === 'undefined'){
			return BX.SocNetLogDestination.obEmailDescMode[arParams.name];
		}else{
			return defaultValue;
		}
	};

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: true,
		bMail: true,
		groupCode: 'contacts',
		className: _getGroupParam('contacts', 'className', 'bx-lm-element-contacts'),
		title: _getGroupParam('contacts', 'title', BX.message('LM_POPUP_TAB_LAST_CONTACTS')),
		emailDescMode: _getGroupParam('contacts', 'emailDescMode')
	});

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: true,
		bMail: true,
		groupCode: 'companies',
		className: _getGroupParam('companies', 'className', 'bx-lm-element-companies'),
		title: _getGroupParam('companies', 'title', BX.message('LM_POPUP_TAB_LAST_COMPANIES'))
	});

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: true,
		bMail: true,
		groupCode: 'leads',
		className: _getGroupParam('leads', 'className', 'bx-lm-element-leads'),
		title: _getGroupParam('leads', 'title', BX.message('LM_POPUP_TAB_LAST_LEADS'))
	});

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: true,
		bMail: false,
		groupCode: 'deals',
		className: _getGroupParam('deals', 'className', 'bx-lm-element-deals'),
		avatarLessMode: _getGroupParam('deals', 'avatarLessMode', true),
		title: _getGroupParam('deals', 'title', BX.message('LM_POPUP_TAB_LAST_DEALS'))
	});

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: false,
		bMail: false,
		groupCode: 'groups',
		bHideGroup: _getGroupParam('groups', 'bHideGroup', true),
		className: _getGroupParam('groups', 'className', 'bx-lm-element-groups'),
		descLessMode: _getGroupParam('groups', 'descLessMode', true),
		emailDescMode: _getGroupParam('groups', 'emailDescMode')
	});

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: false,
		bMail: true,
		groupCode: 'users',
		className: _getGroupParam('users', 'className', 'bx-lm-element-user'),
		descLessMode: _getGroupParam('users', 'descLessMode', true),
		title: _getGroupParam('users', 'title', BX.message('LM_POPUP_TAB_LAST_USERS')),
		emailDescMode: _getGroupParam('users', 'emailDescMode')
	});

	if (BX.SocNetLogDestination.obAllowSearchNetworkUsers[arParams.name])
	{
		BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
			bCrm: false,
			bMail: false,
			groupCode: 'network',
			className: _getGroupParam('network', 'className', 'bx-lm-element-user'),
			descLessMode: _getGroupParam('network', 'descLessMode', false),
			title: _getGroupParam('network', 'title', BX.message('LM_POPUP_TAB_LAST_NETWORK')),
			emailDescMode: _getGroupParam('network', 'emailDescMode')
		});
	}

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: false,
		bMail: true,
		groupCode: 'crmemails',
		className: _getGroupParam('crmemails', 'className', 'bx-lm-element-user'),
		descLessMode: _getGroupParam('crmemails', 'descLessMode', true),
		title: _getGroupParam('crmemails', 'title', BX.message('LM_POPUP_TAB_LAST_CRMEMAILS')),
		emailDescMode: _getGroupParam('crmemails', 'emailDescMode')
	});

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: false,
		bMail: true,
		groupCode: 'mailContacts',
		className: _getGroupParam('mailContacts', 'className', 'bx-lm-element-mail-contact'),
		descLessMode: _getGroupParam('mailContacts', 'descLessMode', true),
		title: _getGroupParam('mailContacts', 'title', BX.message('LM_POPUP_TAB_LAST_MAIL_CONTACTS')),
		emailDescMode: _getGroupParam('mailContacts', 'emailDescMode')
	});

	if (BX.SocNetLogDestination.obAllowSearchEmailUsers[arParams.name])
	{
		BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
			bCrm: false,
			bMail: true,
			groupCode: 'email',
			className: _getGroupParam('email', 'className', 'bx-lm-element-user bx-lm-element-email'),
			descLessMode: _getGroupParam('email', 'descLessMode', true),
			title: _getGroupParam('email', 'title', BX.message('LM_POPUP_TAB_EMAIL')),
			emailDescMode: _getGroupParam('email', 'emailDescMode')
		});
	}

	if (BX.SocNetLogDestination.obProjectsEnable[arParams.name])
	{
		BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
			bCrm: false,
			bMail: false,
			groupCode: 'projects',
			className: _getGroupParam('projects', 'className', 'bx-lm-element-sonetgroup'),
			classNameExtranetGroup: _getGroupParam('projects', 'classNameExtranetGroup', 'bx-lm-element-extranet'),
			groupboxClassName: _getGroupParam('projects', 'groupboxClassName', 'bx-lm-groupbox-project'),
			descLessMode: _getGroupParam('projects', 'descLessMode', true),
			title: _getGroupParam('projects', 'title', BX.message('LM_POPUP_TAB_LAST_SG_PROJECT'))
		});
	}

	BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
		bCrm: false,
		bMail: false,
		groupCode: 'sonetgroups',
		className: _getGroupParam('sonetgroups', 'className', 'bx-lm-element-sonetgroup'),
		classNameExtranetGroup: _getGroupParam('sonetgroups', 'classNameExtranetGroup', 'bx-lm-element-extranet'),
		groupboxClassName: _getGroupParam('sonetgroups', 'groupboxClassName', 'bx-lm-groupbox-sonetgroup'),
		descLessMode: _getGroupParam('sonetgroups', 'descLessMode', true),
		title: _getGroupParam('sonetgroups', 'title', BX.message('LM_POPUP_TAB_LAST_SG'))
	});

	if (BX.SocNetLogDestination.obDepartmentEnable[arParams.name])
	{
		BX.SocNetLogDestination.arDialogGroups[arParams.name].push({
			bCrm: false,
			bMail: false,
			groupCode: 'department',
			className: _getGroupParam('department', 'className', 'bx-lm-element-department'),
			groupboxClassName: _getGroupParam('department', 'groupboxClassName', 'bx-lm-groupbox-department'),
			descLessMode: _getGroupParam('department', 'descLessMode', true),
			title: _getGroupParam('department', 'title', BX.message('LM_POPUP_TAB_LAST_STRUCTURE'))
		});
	}

	BX.SocNetLogDestination.obItems[arParams.name] = BX.clone(arParams.items);
	BX.SocNetLogDestination.obItemsLast[arParams.name] = BX.clone(arParams.itemsLast);
	BX.SocNetLogDestination.obItemsSelected[arParams.name] = BX.clone(arParams.itemsSelected);
	BX.SocNetLogDestination.obItemsSelectedUndeleted[arParams.name] = (typeof arParams.itemsSelectedUndeleted != 'undefined' ? BX.clone(arParams.itemsSelectedUndeleted) : []) ;

	for (var itemId in BX.SocNetLogDestination.obItemsSelected[arParams.name])
	{
		if (BX.SocNetLogDestination.obItemsSelected[arParams.name].hasOwnProperty(itemId))
		{
			type = BX.SocNetLogDestination.obItemsSelected[arParams.name][itemId];
			BX.SocNetLogDestination.runSelectCallback(itemId, type, arParams.name, false, 'init');
		}
	}


	if (
		BX.SocNetLogDestination.obUseClientDatabase[arParams.name]
		&& !BX.SocNetLogDestination.bFinderInited
	)
	{
		BX.Finder(false, 'destination', [], {}, BX.SocNetLogDestination);
		BX.onCustomEvent(BX.SocNetLogDestination, 'initFinderDb', [ BX.SocNetLogDestination, arParams.name, null, ['users'], BX.SocNetLogDestination]);
		BX.SocNetLogDestination.bFinderInited = true;
	}

	if (
		typeof (arParams.LHEObjName) != 'undefined'
		&& BX('div' + arParams.LHEObjName)
	)
	{
		BX.addCustomEvent(BX('div' + arParams.LHEObjName), 'OnShowLHE', function(show) {
			if (!show)
			{
				if (BX.SocNetLogDestination.isOpenDialog())
				{
					BX.SocNetLogDestination.closeDialog();
				}
				BX.SocNetLogDestination.closeSearch();
			}
		});
	}

	BX.SocNetLogDestination.obTabSelected[arParams.name] = (
		BX.SocNetLogDestination.obLastEnable[arParams.name]
			? 'last'
			: ''
	);

	if (!BX.SocNetLogDestination.bLoadAllInitialized)
	{
		BX.addCustomEvent('loadAllFinderDb', function(params) {
			BX.SocNetLogDestination.loadAll(params);
		});
		BX.SocNetLogDestination.bLoadAllInitialized = true;
	}

	BX.SocNetLogDestination.obShowVacations[arParams.name] = (
		typeof arParams.showVacations != 'undefined'
		&& arParams.showVacations === true
	);

	if (
		BX.SocNetLogDestination.obShowVacations[arParams.name]
		&& BX.SocNetLogDestination.usersVacation === null
		&& typeof arParams.usersVacation != 'undefined'
	)
	{
		BX.SocNetLogDestination.usersVacation = arParams.usersVacation;
	}
};

BX.SocNetLogDestination.reInit = function(name)
{
	var type = null;

	for (var itemId in BX.SocNetLogDestination.obItemsSelected[name])
	{
		if (BX.SocNetLogDestination.obItemsSelected[name].hasOwnProperty(itemId))
		{
			type = BX.SocNetLogDestination.obItemsSelected[name][itemId];
			BX.SocNetLogDestination.runSelectCallback(itemId, type, name, false, 'init');
		}
	}
};

BX.SocNetLogDestination.openContainer = function(name, params)
{
	if(!name)
	{
		name = 'lm';
	}

	if (!params)
	{
		params = {};
	}

	if (BX.SocNetLogDestination.containerWindow != null)
	{
/*
		if (!BX.SocNetLogDestination.bByFocusEvent)
		{
			BX.SocNetLogDestination.popupWindow.close();
		}
*/

		return false;
	}

	var bindNode = (
		typeof params.bindNode != 'undefined'
			? params.bindNode
			: BX.SocNetLogDestination.obElementBindMainPopup[name].node
	);

	BX.SocNetLogDestination.containerWindow = new BX.PopupWindow({
		id: 'BXSocNetLogDestinationContainer',
		bindElement: bindNode,
		autoHide: true,
		zIndex: 1200,
		className: 'bx-finder-popup bx-finder-v2',
		offsetLeft: parseInt(BX.SocNetLogDestination.obElementBindMainPopup[name].offsetLeft),
		offsetTop: parseInt(BX.SocNetLogDestination.obElementBindMainPopup[name].offsetTop),
		bindOptions: BX.SocNetLogDestination.obBindOptions[name],
		closeByEsc: true,
		closeIcon: !!BX.SocNetLogDestination.obWindowCloseIcon[name],
		lightShadow: true,
		events: {
			onPopupShow : function() {

				if (
					BX.SocNetLogDestination.sendEvent
					&& BX.SocNetLogDestination.obCallback[name]
					&& BX.SocNetLogDestination.obCallback[name].openDialog
				)
				{
					BX.SocNetLogDestination.obCallback[name].openDialog(name);
				}

				if (
					BX.SocNetLogDestination.inviteEmailUserWindow
					&& BX.SocNetLogDestination.inviteEmailUserWindow.isShown()
				)
				{
					BX.SocNetLogDestination.inviteEmailUserWindow.close();
				}
			},
			onPopupClose : function(event) {
				this.destroy();
			},
			onPopupDestroy : BX.proxy(function() {
				BX.SocNetLogDestination.containerWindow = null;

				if (
					BX.SocNetLogDestination.sendEvent
					&& BX.SocNetLogDestination.obCallback[name]
				)
				{
					if (BX.SocNetLogDestination.obCallback[name].closeDialog)
					{
						BX.SocNetLogDestination.obCallback[name].closeDialog(name);
					}

					if (BX.SocNetLogDestination.obCallback[name].closeSearch)
					{
						BX.SocNetLogDestination.obCallback[name].closeSearch(name);
					}

				}
			}, this)
		},
		content: (
			!!BX.SocNetLogDestination.obShowSearchInput[name]
				? BX.create('DIV', {
					children: [
						BX.create('DIV', {
							props: {
								className: 'bx-finder-box bx-finder-box-vertical bx-lm-box ' + BX.SocNetLogDestination.obWindowClass[name]
							},
							style: {
								minWidth: '650px',
								paddingBottom: '8px',
								overflow: 'hidden'
							},
							children: [
								BX.create('DIV', {
									props: {
										className: "bx-finder-search-block"
									},
									children: [
										BX.create('DIV', {
											props: {
												className: "bx-finder-search-block-cell"
											},
											children: [
												BX.create('SPAN', {
													attrs: {
														id: 'bx-dest-internal-item'
													}
												}),
												BX.create('SPAN', {
													attrs: {
														id: "bx-dest-internal-input-box",
														style: "display: inline-block"
													},
													props: {
														className: "feed-add-destination-input-box"
													},
													children: [
														BX.create('INPUT', {
															attrs: {
																type: "text",
																id: "bx-dest-internal-input"
															},
															props: {
																className: "feed-add-destination-inp"
															}
														})
													]
												})
											],
											events: {
												click: function(e) {
													BX.focus(BX.SocNetLogDestination.obElementSearchInput[name]);
													return BX.PreventDefault(e);
												}
											}
										})
									]
								}),
								BX.create('div', {
									attrs: {
										id: "BXSocNetLogDestinationContainerContent"
									},
									props: {
										className: "bx-finder-container-content"
									}
								})
							]
						})
					]
				})
				: BX.create('div', {
					attrs: {
						id: "BXSocNetLogDestinationContainerContent"
					},
					props: {
						className: "bx-finder-container-content"
					}
				})
		)
	});

	if (!!BX.SocNetLogDestination.obShowSearchInput[name])
	{
		BX.bind(BX('bx-dest-internal-input'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
			formName: name,
			inputName: 'bx-dest-internal-input',
			sendAjax: !!BX.SocNetLogDestination.obSendAjaxSearch[name]
		}));
		BX.bind(BX('bx-dest-internal-input'), 'paste', BX.defer(BX.SocNetLogDestination.BXfpSearch, {
			formName: name,
			inputName: 'bx-dest-internal-input',
			sendAjax: !!BX.SocNetLogDestination.obSendAjaxSearch[name],
			onPasteEvent: true
		}));
		BX.bind(BX('bx-dest-internal-input'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
			formName: name,
			inputName: 'bx-dest-internal-input'
		}));
		if (
			params["itemsHidden"]
			&& BX.message('BX_FPD_LINK_1')
			&& BX.message('BX_FPD_LINK_2')
		)
		{
			for (var ii in params["itemsHidden"])
			{
				if (params["itemsHidden"].hasOwnProperty(ii))
				{
					BX.SocNetLogDestination.BXfpSelectCallback({
						item: {
							id: 'SG' + params["itemsHidden"][ii]["ID"],
							name: params["itemsHidden"][ii]["NAME"]
						},
						type: 'sonetgroups',
						bUndeleted: true,
						containerInput: BX('bx-dest-internal-item'),
						valueInput: BX('bx-dest-internal-input'),
						formName: window.BXSocNetLogDestinationFormName,
						tagInputName: 'bx-destination-tag',
						tagLink1: BX.message('BX_FPD_LINK_1'),
						tagLink2: BX.message('BX_FPD_LINK_2'),
						state: 'init'
					});
				}
			}
		}

		BX.SocNetLogDestination.obElementSearchInput[name] = BX('bx-dest-internal-input');
		BX.defer(BX.focus)(BX.SocNetLogDestination.obElementSearchInput[name]);
	}

	return true;
};

BX.SocNetLogDestination.getDialogContent = function(name)
{
	var i = 0;

	var tabs = [
		(
			BX.SocNetLogDestination.obLastEnable[name]
				? BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'destLastTab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-last bx-finder-box-tab-selected'
					},
					events: {
						click: function (e) {
							e.preventDefault();
							return BX.SocNetLogDestination.SwitchTab(name, this, 'last')
						}
					},
					html: BX.message('LM_POPUP_TAB_LAST')
				})
				: null
		),
		(
			BX.SocNetLogDestination.obProjectsEnable[name]
				? BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'destProjectTab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-project'
					},
					events: {
						click: function (e) {
							e.preventDefault();
							return BX.SocNetLogDestination.SwitchTab(name, this, 'project')
						}
					},
					html: BX.message('LM_POPUP_TAB_SG_PROJECT')
				})
				: null
		),
		(
			BX.SocNetLogDestination.obSonetgroupsEnable[name]
				? BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'destGroupTab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-sonetgroup'
					},
					events: {
						click: function (e) {
							e.preventDefault();
							return BX.SocNetLogDestination.SwitchTab(name, this, 'group')
						}
					},
					html: BX.message('LM_POPUP_TAB_SG')
				})
				: null
		),
		(
			BX.SocNetLogDestination.obDepartmentEnable[name]
				? BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'destDepartmentTab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-department'
					},
					events: {
						click: function (e) {
							e.preventDefault();
							return BX.SocNetLogDestination.SwitchTab(name, this, 'department')
						}
					},
					html: (BX.SocNetLogDestination.obUserSearchArea[name] == 'E' ? BX.message('LM_POPUP_TAB_STRUCTURE_EXTRANET') : BX.message('LM_POPUP_TAB_STRUCTURE'))
				})
				: null
		),
		(
			BX.SocNetLogDestination.obAllowSearchEmailUsers[name]
				? BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'destEmailTab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-email'
					},
					events: {
						click: function (e) {
							e.preventDefault();
							return BX.SocNetLogDestination.SwitchTab(name, this, 'email')
						}
					},
					html: BX.message('LM_POPUP_TAB_EMAIL')
				})
				: null
		),
		(
			BX.SocNetLogDestination.obAllowSearchCrmEmailUsers[name]
				? BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'destCrmEmailTab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-crmemail'
					},
					events: {
						click: function (e) {
							e.preventDefault();
							return BX.SocNetLogDestination.SwitchTab(name, this, 'crmemail')
						}
					},
					html: BX.message('LM_POPUP_TAB_CRMEMAIL')
				})
				: null
		),
		(
			BX.SocNetLogDestination.obShowSearchInput[name]
				? BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'destSearchTab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-search'
					},
					events: {
						click: function (e) {
							e.preventDefault();
							return BX.SocNetLogDestination.SwitchTab(name, this, 'search')
						}
					},
					html: BX.message('LM_POPUP_TAB_SEARCH')
				})
				: null
		)
	];

	if (typeof BX.SocNetLogDestination.obCustomTabs[name] != 'undefined')
	{
		for (i=0; i < BX.SocNetLogDestination.obCustomTabs[name].length; i++)
		{
			tabs.push(
				BX.create('A', {
					attrs: {
						hidefocus: 'true',
						id: 'dest' + BX.SocNetLogDestination.obCustomTabs[name][i].id + 'Tab_' + name,
						href: '#switchTab'
					},
					props: {
						className: 'bx-finder-box-tab bx-lm-tab-' + BX.SocNetLogDestination.obCustomTabs[name][i].id
					},
					events: {
						click: BX.proxy(function(e) {
								var target = e.target || e.srcElement;
								e.preventDefault();
								return BX.SocNetLogDestination.SwitchTab(name, target, BX.SocNetLogDestination.obCustomTabs[name][this.tabNum].id)
							}, {
								tabNum: i
							}
						)
					},
					html: BX.SocNetLogDestination.obCustomTabs[name][i].name
				})
			);

			BX.SocNetLogDestination.obResult[BX.SocNetLogDestination.obCustomTabs[name][i].id] = [];
		}
	}

	tabs.push(
		BX.create('DIV', {
			props: {
				className: 'popup-window-hr popup-window-buttons-hr'
			},
			children: [
				BX.create('I', {})
			]
		})
	);

	var contents = [
		(
			BX.SocNetLogDestination.obLastEnable[name]
				? BX.create('DIV', {
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-last' + (BX.SocNetLogDestination.obLastEnable[name] ? ' bx-finder-box-tab-content-selected' : '')
					},
					html: BX.SocNetLogDestination.getItemLastHtml(false, false, name)
				})
				: null
		),
		(
			BX.SocNetLogDestination.obProjectsEnable[name]
				? BX.create('DIV', {
					attrs: {
						id: 'bx-lm-box-project-content'
					},
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-project' + (!BX.SocNetLogDestination.obLastEnable[name] ? ' bx-finder-box-tab-content-selected' : '')
					}
				})
				: null
		),
		(
			BX.SocNetLogDestination.obSonetgroupsEnable[name]
				? BX.create('DIV', {
					attrs: {
						id: 'bx-lm-box-group-content'
					},
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-sonetgroup' + (!BX.SocNetLogDestination.obLastEnable[name] && !BX.SocNetLogDestination.obProjectsEnable[name] ? ' bx-finder-box-tab-content-selected' : '')
					}
				})
				: null
		),
		(
			BX.SocNetLogDestination.obDepartmentEnable[name]
				? BX.create('DIV', {
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-department' + (!BX.SocNetLogDestination.obLastEnable[name] && !BX.SocNetLogDestination.obProjectsEnable[name] && !BX.SocNetLogDestination.obSonetgroupsEnable[name] ? ' bx-finder-box-tab-content-selected' : '')
					}
				})
				: null
		),
		(
			BX.SocNetLogDestination.obAllowSearchEmailUsers[name]
				? BX.create('DIV', {
					attrs: {
						id: 'bx-lm-box-email-content'
					},
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-email'
					}
				})
				: null
		),
		(
			BX.SocNetLogDestination.obAllowSearchCrmEmailUsers[name]
				? BX.create('DIV', {
					attrs: {
						id: 'bx-lm-box-crmemail-content'
					},
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-crmemail'
					}
				})
				: null
		),
		(
			BX.SocNetLogDestination.obShowSearchInput[name]
				? BX.create('DIV', {
					attrs: {
						id: 'destSearchTabContent_' + name
					},
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-search'
					}
				})
				: null
		)
	];

	if (typeof BX.SocNetLogDestination.obCustomTabs[name] != 'undefined')
	{
		for (i=0; i < BX.SocNetLogDestination.obCustomTabs[name].length; i++)
		{
			contents.push(
				BX.create('DIV', {
					attrs: {
						id: 'dest' + BX.SocNetLogDestination.obCustomTabs[name][i].id + 'TabContent_' + name
					},
					props: {
						className: 'bx-finder-box-tab-content bx-lm-box-tab-content-' + BX.SocNetLogDestination.obCustomTabs[name][i].id
					}
				})
			);
		}
	}

	return BX.create('DIV', {
		style: {
			minWidth: '650px',
			paddingBottom: '8px'
		},
		props: {
			className: 'bx-finder-box bx-finder-box-vertical bx-lm-box ' + BX.SocNetLogDestination.obWindowClass[name]
		},
		children: [
			(
				!BX.SocNetLogDestination.obLastEnable[name]
				&& !BX.SocNetLogDestination.obSonetgroupsEnable[name]
				&& !BX.SocNetLogDestination.obDepartmentEnable[name]
					? null
					: BX.create('DIV', {
						props: {
							className: 'bx-finder-box-tabs'
						},
						children: tabs
					})
			),
			BX.create('DIV', {
				attrs: {
					id: 'bx-lm-box-last-content'
				},
				props: {
					className: 'bx-finder-box-tabs-content bx-finder-box-tabs-content-window'
				},
				children: [
					BX.create('TABLE', {
						props: {
							className: 'bx-finder-box-tabs-content-table'
						},
						children: [
							BX.create('TR', {
								children: [
									BX.create('TD', {
										props: {
											className: 'bx-finder-box-tabs-content-cell'
										},
										children: contents
									})
								]
							})
						]
					})
				]
			}),
			(!!BX.SocNetLogDestination.obUseContainer[name] ? BX.SocNetLogDestination.getSearchWaiter() : null)
		]
	});
};

BX.SocNetLogDestination.getSearchContent = function(items, name, params)
{
	return BX.create('DIV', {
		props: {
			className: 'bx-finder-box bx-finder-box-vertical bx-lm-box ' + BX.SocNetLogDestination.obWindowClass[name]
		},
		style: {
			minWidth: '450px',
			paddingBottom: '8px'
		},
		children: [
			BX.create('DIV', {
				attrs : {
					id : 'bx-lm-box-search-tabs-content'
				},
				props: {
					className: 'bx-finder-box-tabs-content' + (!!BX.SocNetLogDestination.obUseContainer[name] ? ' bx-finder-box-tabs-content-search' : '')
				},
				children: [
					BX.create('TABLE', {
						props: {
							className: 'bx-finder-box-tabs-content-table'
						},
						children: [
							BX.create('TR', {
								children: [
									BX.create('TD', {
										props: {
											className: 'bx-finder-box-tabs-content-cell'
										},
										children: [
											BX.create('DIV', {
												attrs : {
													id : 'bx-lm-box-search-content'
												},
												props: {
													className: 'bx-finder-box-tab-content bx-finder-box-tab-content-selected'
												},
												html: BX.SocNetLogDestination.getItemLastHtml(items, true, name)
											})
										]
									})
								]
							})
						]
					})
				]
			}),
			(!!BX.SocNetLogDestination.obUseContainer[name] ? null : BX.SocNetLogDestination.getSearchWaiter())
		]
	});
};

BX.SocNetLogDestination.getHidden = function(prefix, item, varName)
{
	if (
		typeof varName == 'undefined'
		|| !varName
	)
	{
		varName = 'SPERM';
	}

	var value = (
		typeof item.id != 'undefined'
		&& (
			item.id.indexOf("C_") === 0
			|| item.id.indexOf("CO_") === 0
			|| item.id.indexOf("L_") === 0
		)
			? item.desc
			: item.id
	);

	return [
		BX.create("input", {
			attrs : {
				type : 'hidden',
				name : varName + '[' + prefix + '][]',
				value : value
			}
		}),
		(
			prefix == 'UE'
			&& typeof item.params != 'undefined'
			&& typeof item.params.name != 'undefined'
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_NAME[' + value + ']',
						'value' : item.params.name
					}
				})
				: null
		),
		(
			prefix == 'UE'
			&& typeof item.params != 'undefined'
			&& typeof item.params.lastName != 'undefined'
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_LAST_NAME[' + value + ']',
						'value' : item.params.lastName
					}
				})
				: null
		),
		(
			prefix == 'UE'
			&& typeof item.id != 'undefined'
			&& (
				item.id.indexOf("C_") === 0
				|| item.id.indexOf("CO_") === 0
				|| item.id.indexOf("L_") === 0
			)
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_CRM_ENTITY[' + value + ']',
						'value' : item.id
					}
				})
				: null
		),
		(
			prefix == 'UE'
			&& typeof item.params != 'undefined'
			&& typeof item.params.createCrmContact != 'undefined'
			&& !!item.params.createCrmContact
				? BX.create("input", {
					attrs : {
						'type' : 'hidden',
						'name' : 'INVITED_USER_CREATE_CRM_CONTACT[' + value + ']',
						'value' : 'Y'
					}
				})
				: null
		)
	];
};

BX.SocNetLogDestination.getSearchWaiter = function()
{
	return BX.create('DIV', {
		attrs : {
			id : 'bx-lm-box-search-waiter'
		},
		props: {
			className: 'bx-finder-box-search-waiter'
		},
		style: {
			height: '0px'
		},
		children: [
			BX.create('IMG', {
				props: {
					className: 'bx-finder-box-search-waiter-background'
				},
				attrs: {
					src: '/bitrix/js/main/core/images/waiter-white.gif'
				}
			}),
			BX.create('DIV', {
				props: {
					className: 'bx-finder-box-search-waiter-text'
				},
				text: BX.message('LM_POPUP_WAITER_TEXT')
			})
		]
	})
};


BX.SocNetLogDestination.openDialog = function(name, params)
{
	var type = null;

	if(!name)
	{
		name = 'lm';
	}

	if (!params)
	{
		params = {};
	}

	BX.SocNetLogDestination.bByFocusEvent = (
		typeof params.bByFocusEvent != 'undefined'
		&& params.bByFocusEvent
	);

	if (BX.SocNetLogDestination.popupSearchWindow != null)
	{
		BX.SocNetLogDestination.popupSearchWindow.close();
	}

	if (BX.SocNetLogDestination.popupWindow != null)
	{
		var uniquePopupId = BX.SocNetLogDestination.popupWindow.uniquePopupId + '';

		if (
			uniquePopupId != name
			|| !BX.SocNetLogDestination.bByFocusEvent
		)
		{
			BX.SocNetLogDestination.popupWindow.close();
		}

		if (uniquePopupId == name)
		{
			return false;
		}
	}

	if (
		typeof params.bByFocusEvent == 'undefined'
		|| !params.bByFocusEvent
	)
	{
		BX.SocNetLogDestination.bByFocusEvent = false;
	}

	if (!!BX.SocNetLogDestination.obUseContainer[name])
	{
		if (!BX.SocNetLogDestination.openContainer(name, params))
		{
			return false;
		}

		BX.cleanNode(BX('BXSocNetLogDestinationContainerContent'));
		BX('BXSocNetLogDestinationContainerContent').appendChild(BX.SocNetLogDestination.getDialogContent(name));

		if (!!BX.SocNetLogDestination.obShowSearchInput[name])
		{
			for (var itemId in BX.SocNetLogDestination.obItemsSelected[name])
			{
				if (BX.SocNetLogDestination.obItemsSelected[name].hasOwnProperty(itemId))
				{
					type = BX.SocNetLogDestination.obItemsSelected[name][itemId];
					BX.SocNetLogDestination.runSelectCallback(itemId, type, name, false, 'init');
				}
			}
		}

		BX.SocNetLogDestination.containerWindow.setAngle({});
		if(typeof params.bindNode != 'undefined')
		{
			BX.SocNetLogDestination.containerWindow.setBindElement(params.bindNode);
		}
		BX.SocNetLogDestination.containerWindow.show();
	}
	else
	{
		var bindNode = (
			typeof params.bindNode != 'undefined'
				? params.bindNode
				: BX.SocNetLogDestination.obElementBindMainPopup[name].node
		);

		BX.SocNetLogDestination.popupWindow = new BX.PopupWindow({
			id: 'BXSocNetLogDestination',
			bindElement: bindNode,
			autoHide: true,
			zIndex: 1200,
			className: 'bx-finder-popup bx-finder-v2',
			offsetLeft: parseInt(BX.SocNetLogDestination.obElementBindMainPopup[name].offsetLeft),
			offsetTop: parseInt(BX.SocNetLogDestination.obElementBindMainPopup[name].offsetTop),
			bindOptions: BX.SocNetLogDestination.obBindOptions[name],
			closeByEsc: true,
			closeIcon: BX.SocNetLogDestination.obWindowCloseIcon[name] ? {'top': '12px', 'right': '15px'} : false,
			lightShadow: true,
			events: {
				onPopupShow : function() {
					if (
						BX.SocNetLogDestination.sendEvent
						&& BX.SocNetLogDestination.obCallback[name]
						&& BX.SocNetLogDestination.obCallback[name].openDialog
					)
					{
						BX.SocNetLogDestination.obCallback[name].openDialog(name);
					}

					if (
						BX.SocNetLogDestination.inviteEmailUserWindow
						&& BX.SocNetLogDestination.inviteEmailUserWindow.isShown()
					)
					{
						BX.SocNetLogDestination.inviteEmailUserWindow.close();
					}
				},
				onPopupClose : function(event) {
					this.destroy();
				},
				onPopupDestroy : BX.proxy(function() {
					BX.SocNetLogDestination.popupWindow = null;
					if (
						BX.SocNetLogDestination.sendEvent
						&& BX.SocNetLogDestination.obCallback[name]
						&& BX.SocNetLogDestination.obCallback[name].closeDialog
					)
					{
						BX.SocNetLogDestination.obCallback[name].closeDialog(name);
					}
				}, this)
			},
			content: BX.SocNetLogDestination.getDialogContent(name)
		});

		BX.SocNetLogDestination.popupWindow.setAngle({});
		BX.SocNetLogDestination.popupWindow.show();
	}

	if (BX.SocNetLogDestination.obLastEnable[name])
	{
		BX.SocNetLogDestination.initResultNavigation(name, 'last', BX.SocNetLogDestination.obItemsLast[name]);
		BX.SocNetLogDestination.obTabSelected[name] = 'last';
	}

	if (
		!BX.SocNetLogDestination.obLastEnable[name]
		&& !BX.SocNetLogDestination.obSonetgroupsEnable[name]
		&& BX.SocNetLogDestination.obDepartmentEnable[name]
		&& BX('destDepartmentTab_'+name)
	)
	{
		BX.SocNetLogDestination.SwitchTab(name, BX('destDepartmentTab_'+name), 'department');
		BX.SocNetLogDestination.popupWindow.adjustPosition();
	}
};

BX.SocNetLogDestination.search = function(text, sendAjax, name, nameTemplate, params)
{
	if(!name)
		name = 'lm';

	if (!params)
		params = {};

	if (
		typeof nameTemplate == 'undefined'
		|| nameTemplate.length <= 0
	)
	{
		nameTemplate = BX.SocNetLogDestination.obUserNameTemplate[name];
	}

	sendAjax = (sendAjax != false);

	if (BX.SocNetLogDestination.extranetUser)
	{
		sendAjax = false;
	}

	BX.SocNetLogDestination.obSearchFirstElement = null;
	BX.SocNetLogDestination.obCurrentElement.search = null;
	BX.SocNetLogDestination.obResult.search = [];
	BX.SocNetLogDestination.obCursorPosition.search = {
		group: 0,
		row: 0,
		column: 0
	};

	text = BX.util.trim(text);

	if (text.length <= 0)
	{
		clearTimeout(BX.SocNetLogDestination.searchTimeout);
		if(BX.SocNetLogDestination.popupSearchWindow != null)
		{
			BX.SocNetLogDestination.popupSearchWindow.close();
		}
		return false;
	}
	else
	{
		var items = {
			'groups': {}, 'users': {}, 'network': {}, 'crmemails': {}, 'sonetgroups': {}, 'projects': {}, 'department': {},
			'contacts': {}, 'companies': {}, 'leads': {}, 'deals': {}, 'mailContacts': {}
		};
		var count = 0;

		var resultGroupIndex = 0;
		var resultRowIndex = 0;
		var resultColumnIndex = 0;
		var bNewGroup = null;
		var storedItem = false;
		var bSkip = false;

		var partsItem = [];
		var bFound = false;
		var bPartFound = false;
		var partsSearchText = null;
		var arSearchStringAlternatives = [text];
		var searchString = null;

		var arTmp = [];
		var tmpVal = false;

		var key = null;
		var i = null;
		var k = null;

		if (sendAjax) // before AJAX request
		{
			BX.SocNetLogDestination.abortSearchRequest();

			var obSearch = { searchString: text };

			if (!!BX.SocNetLogDestination.obUseClientDatabase[name])
			{
				BX.onCustomEvent('findEntityByName', [
					BX.SocNetLogDestination,
					obSearch,
					{ },
					BX.SocNetLogDestination.oDbUserSearchResult[name]
				]); // get result from the clientDb
			}

			if (obSearch.searchString != text) // if text was converted to another charset
			{
				arSearchStringAlternatives.push(obSearch.searchString);
			}
			BX.SocNetLogDestination.bResultMoved.search = false;
			BX.SocNetLogDestination.tmpSearchResult.ajax = [];
		}
		else // from AJAX results
		{
			if (
				typeof params != 'undefined'
				&& typeof params.textAjax != 'undefined'
				&& params.textAjax != text
			)
			{
				arSearchStringAlternatives.push(params.textAjax);
			}

			// syncronize local DB
			if (
				!BX.SocNetLogDestination.obUserSearchArea[name]
				&& !BX.SocNetLogDestination.obAllowSearchNetworkUsers[name]
			)
			{
				for (key = 0; key < arSearchStringAlternatives.length; key++)
				{
					searchString = arSearchStringAlternatives[key].toLowerCase();
					if (
						searchString.length > 1
						&& typeof BX.SocNetLogDestination.oDbUserSearchResult[name][searchString] != 'undefined'
						&& BX.SocNetLogDestination.oDbUserSearchResult[name][searchString].length > 0
					)
					{
						/* sync minus */
						BX.onCustomEvent('syncClientDb', [
							BX.SocNetLogDestination,
							name,
							BX.SocNetLogDestination.oDbUserSearchResult[name][searchString],
							(
								typeof BX.SocNetLogDestination.oAjaxUserSearchResult[name][searchString] != 'undefined'
									? BX.SocNetLogDestination.oAjaxUserSearchResult[name][searchString]
									: {}
							)
						]);
					}
				}
			}
		}

		if (sendAjax) // before Ajax search
		{
			BX.SocNetLogDestination.tmpSearchResult.client = [];
		}

		for (var group in items)
		{
			bNewGroup = true;
			arTmp = [];

			if (
				BX.SocNetLogDestination.obDepartmentSelectDisable[name]
				&& group == 'department'
			)
			{
				continue;
			}

			for (key = 0; key < arSearchStringAlternatives.length; key++)
			{
				searchString = arSearchStringAlternatives[key].toLowerCase();
				if (
					group == 'users'
					&& sendAjax
					&& typeof BX.SocNetLogDestination.oDbUserSearchResult[name][searchString] != 'undefined'
					&& BX.SocNetLogDestination.oDbUserSearchResult[name][searchString].length > 0 // results from local DB
				)
				{
					for (i in BX.SocNetLogDestination.oDbUserSearchResult[name][searchString])
					{
						if (!BX.SocNetLogDestination.oDbUserSearchResult[name][searchString].hasOwnProperty(i))
						{
							continue;
						}

						if (
							!BX.SocNetLogDestination.obAllowSearchSelf[name]
							&& BX.SocNetLogDestination.oDbUserSearchResult[name][searchString][i] == 'U' + BX.message('USER_ID')
						)
						{
							continue;
						}

						if (
							!BX.SocNetLogDestination.obUserSearchArea[name]
							|| (
								BX.SocNetLogDestination.obUserSearchArea[name] == 'E'
								&& BX.SocNetLogDestination.obClientDbData.users[BX.SocNetLogDestination.oDbUserSearchResult[name][searchString][i]]['isExtranet'] == 'Y'
							)
							|| (
								BX.SocNetLogDestination.obUserSearchArea[name] == 'I'
								&& BX.SocNetLogDestination.obClientDbData.users[BX.SocNetLogDestination.oDbUserSearchResult[name][searchString][i]]['isExtranet'] != 'Y'
							)
						)
						{
							BX.SocNetLogDestination.obItems[name][group][BX.SocNetLogDestination.oDbUserSearchResult[name][searchString][i]] = BX.SocNetLogDestination.obClientDbData.users[BX.SocNetLogDestination.oDbUserSearchResult[name][searchString][i]];
						}
					}
				}
			}

			var tmpString = '';

			for (i in BX.SocNetLogDestination.obItems[name][group])
			{
				if (!BX.SocNetLogDestination.obItems[name][group].hasOwnProperty(i))
				{
					continue;
				}

				if (BX.SocNetLogDestination.obItemsSelected[name][i]) // if already in selected
				{
					continue;
				}

				for (key = 0; key < arSearchStringAlternatives.length; key++)
				{
					bFound = false;

					searchString = arSearchStringAlternatives[key];
					partsSearchText = searchString.toLowerCase().split(" ");
					partsItem = BX.SocNetLogDestination.obItems[name][group][i].name.toLowerCase().split(" ");
					if (
						group === "mailContacts"
						&& BX.SocNetLogDestination.obItems[name][group][i].email
					)
					{
						partsItem = partsItem.concat(BX.SocNetLogDestination.obItems[name][group][i].email.toLowerCase().split("@"));
					}

					for (k in partsItem)
					{
						if (partsItem.hasOwnProperty(k))
						{
							partsItem[k] = BX.util.htmlspecialcharsback(partsItem[k]);
							tmpString = partsItem[k].replace(/(["\xAB\xBB])/g, ''); // strip quotes

							if (tmpString.length != partsItem[k].length)
							{
								partsItem.push(tmpString);
							}
						}
					}

					if (
						typeof BX.SocNetLogDestination.obItems[name][group][i].email != 'undefined'
						&& BX.SocNetLogDestination.obItems[name][group][i].email
						&& BX.SocNetLogDestination.obItems[name][group][i].email.length > 0
					)
					{
						partsItem.push(BX.SocNetLogDestination.obItems[name][group][i].email.toLowerCase());
					}

					if (
						typeof BX.SocNetLogDestination.obItems[name][group][i].login != 'undefined'
						&& BX.SocNetLogDestination.obItems[name][group][i].login.length > 0
						&& partsSearchText.length <= 1
						&& searchString.length > 2
					)
					{
						partsItem.push(BX.SocNetLogDestination.obItems[name][group][i].login.toLowerCase());
					}

					BX.onCustomEvent(window, 'SocNetLogDestinationSearchFillItemParts', [group, BX.SocNetLogDestination.obItems[name][group][i], partsItem]);

					if (partsSearchText.length <= 1)
					{
						for (k in partsItem)
						{
							if (
								partsItem.hasOwnProperty(k)
								&& searchString.toLowerCase().localeCompare(partsItem[k].substring(0, searchString.length), 'en-US', { sensitivity: 'base' }) === 0
							)
							{
								bFound = true;
								break;
							}
						}
					}
					else
					{
						bFound = true;

						for (var j in partsSearchText)
						{
							if (!partsSearchText.hasOwnProperty(j))
							{
								continue;
							}

							bPartFound = false;
							for (k in partsItem)
							{
								if (
									partsItem.hasOwnProperty(k)
									&& partsSearchText[j].toLowerCase().localeCompare(partsItem[k].substring(0, partsSearchText[j].length), 'en-US', { sensitivity: 'base' }) === 0
								)
								{
									bPartFound = true;
									break;
								}
							}

							if (!bPartFound)
							{
								bFound = false;
								break;
							}
						}

						if (!bFound)
						{
							continue;
						}
					}
					if (bFound)
					{
						break;
					}
				}

				if (!bFound)
				{
					continue;
				}

				if (bNewGroup)
				{
					if (typeof BX.SocNetLogDestination.obResult.search[resultGroupIndex] != 'undefined')
					{
						resultGroupIndex++;
					}
					bNewGroup = false;
				}

				tmpVal = {
					value: i
				};

				if (typeof BX.SocNetLogDestination.obDestSort[name][i] != 'undefined')
				{
					tmpVal.sort = BX.SocNetLogDestination.obDestSort[name][i];
				}

				if (BX.SocNetLogDestination.obItems[name][group][i].isNetwork == 'Y')
				{
					tmpVal.isNetwork = true;
				}

				if (sendAjax) // before Ajax search
				{
					BX.SocNetLogDestination.tmpSearchResult.client.push(i);
				}

				arTmp.push(tmpVal);
			}

			BX.SocNetLogDestination.tmpSearchResult.client.filter(function(el, index, arr) {
				return index == arr.indexOf(el);
			});

			arTmp.sort(BX.SocNetLogDestination.compareDestinations);

			var sort = 0;
			for (key = 0; key < arTmp.length; key++)
			{
				i = arTmp[key].value;
				items[group][i] = ++sort;

				bSkip = false;
				if (BX.SocNetLogDestination.obItems[name][group][i]['id'] == 'UA')
				{
					bSkip = true;
				}
				else // calculate position
				{
					if (typeof BX.SocNetLogDestination.obResult.search[resultGroupIndex] == 'undefined')
					{
						BX.SocNetLogDestination.obResult.search[resultGroupIndex] = [];
						resultRowIndex = 0;
						resultColumnIndex = 0;
					}

					if (resultColumnIndex == 2)
					{
						resultRowIndex++;
						resultColumnIndex = 0;
					}

					if (typeof BX.SocNetLogDestination.obResult.search[resultGroupIndex][resultRowIndex] == 'undefined')
					{
						BX.SocNetLogDestination.obResult.search[resultGroupIndex][resultRowIndex] = [];
						resultColumnIndex = 0;
					}
				}

				var item = BX.clone(BX.SocNetLogDestination.obItems[name][group][i]);

				if (bSkip)
				{
					storedItem = item;
				}

				item.type = group;
				if (!bSkip)
				{
					if (storedItem) // add stored item / UA
					{
						BX.SocNetLogDestination.obResult.search[resultGroupIndex][resultRowIndex][resultColumnIndex] = storedItem;
						storedItem = false;
						resultColumnIndex++;
					}

					BX.SocNetLogDestination.obResult.search[resultGroupIndex][resultRowIndex][resultColumnIndex] = item;
				}

				if (count <= 0)
				{
					BX.SocNetLogDestination.obSearchFirstElement = item;
					BX.SocNetLogDestination.obCurrentElement.search = item;
				}
				count++;

				resultColumnIndex++;
			}
		}

		if (sendAjax)
		{
			if (BX.SocNetLogDestination.popupSearchWindow != null)
			{
				BX.SocNetLogDestination.popupSearchWindowContent.innerHTML = BX.SocNetLogDestination.getItemLastHtml(items, true, name);
				if (typeof params.bindNode != 'undefined')
				{
					BX.SocNetLogDestination.popupSearchWindow.setBindElement(params.bindNode);
				}
			}
			else
			{
				BX.SocNetLogDestination.openSearch(items, name, params);
			}

			if (!!BX.SocNetLogDestination.obUseContainer[name])
			{
				BX.SocNetLogDestination.containerWindow.adjustPosition();
			}
			else if (BX.SocNetLogDestination.popupSearchWindow)
			{
				BX.SocNetLogDestination.popupSearchWindow.adjustPosition();
			}
		}
		else
		{
			if (count <= 0)
			{
				if (BX.SocNetLogDestination.popupSearchWindow != null)
				{
					if (!BX.SocNetLogDestination.obAllowSearchNetworkUsers[name])
					{
						BX.SocNetLogDestination.popupSearchWindow.destroy();
					}
				}
				else if (
					BX.SocNetLogDestination.obShowSearchInput[name]
					&& BX('bx-lm-box-waiter-content-text')
				)
				{
					BX('bx-lm-box-waiter-content-text').innerHTML = BX.message('LM_EMPTY_LIST');
				}

				if (BX.SocNetLogDestination.obAllowAddSocNetGroup[name])
				{
					BX.SocNetLogDestination.createSonetGroupTimeout = setTimeout(function()
					{
						if (BX.SocNetLogDestination.createSocNetGroupWindow === null)
						{
							BX.SocNetLogDestination.createSocNetGroupWindow = new BX.PopupWindow({
								id: "invite-dialog-creategroup-popup",
								bindElement: BX.SocNetLogDestination.obElementBindSearchPopup[name].node,
								offsetTop : 1,
								autoHide : true,
								content : BX.SocNetLogDestination.createSocNetGroupContent(text),
								zIndex : 1200,
								buttons : BX.SocNetLogDestination.createSocNetGroupButtons(text, name)
							});
						}
						else
						{
							BX.SocNetLogDestination.createSocNetGroupWindow.setContent(BX.SocNetLogDestination.createSocNetGroupContent(text));
							BX.SocNetLogDestination.createSocNetGroupWindow.setButtons(BX.SocNetLogDestination.createSocNetGroupButtons(text, name));
						}

						if (!BX.SocNetLogDestination.createSocNetGroupWindow.isShown())
						{
							BX.SocNetLogDestination.createSocNetGroupWindow.show();
						}

					}, 1000);
				}
			}
			else
			{
				if (BX.SocNetLogDestination.popupSearchWindow != null)
				{
					BX.SocNetLogDestination.popupSearchWindowContent.innerHTML = BX.SocNetLogDestination.getItemLastHtml(items, true, name);
				}
				else
				{
					BX.SocNetLogDestination.openSearch(items, name, params);
				}

				if (!!BX.SocNetLogDestination.obUseContainer[name])
				{
					BX.SocNetLogDestination.containerWindow.adjustPosition();
				}
				else if (BX.SocNetLogDestination.popupSearchWindow)
				{
					BX.SocNetLogDestination.popupSearchWindow.adjustPosition();
				}
			}

			BX.SocNetLogDestination.obEmptySearchResult[name] = (count <= 0);
		}

		clearTimeout(BX.SocNetLogDestination.searchTimeout);

		if (sendAjax && text.toLowerCase() != '')
		{
			BX.SocNetLogDestination.showSearchWaiter(name);

			BX.SocNetLogDestination.searchTimeout = setTimeout(function()
			{
				var ajaxData = {
					LD_SEARCH : 'Y',
					USER_SEARCH : BX.SocNetLogDestination.obAllowUserSearch[name] ? 'Y' : 'N',
					CRM_SEARCH : BX.SocNetLogDestination.obCrmFeed[name] ? 'Y' : 'N',
					CRM_SEARCH_TYPES : BX.SocNetLogDestination.obCrmTypes[name],
					CRMCOMPANYMY : BX.SocNetLogDestination.obCrmMyCompany[name] ? 'Y' : 'N',
					EXTRANET_SEARCH : BX.util.in_array(BX.SocNetLogDestination.obUserSearchArea[name], ['I', 'E']) ? BX.SocNetLogDestination.obUserSearchArea[name] : 'N',
					SEARCH : text.toLowerCase(),
					SEARCH_CONVERTED : (
						BX.message('LANGUAGE_ID') == 'ru'
						&& BX.correctText
							? BX.correctText(text.toLowerCase())
							: ''
					),
					sessid: BX.bitrix_sessid(),
					nt: (typeof nameTemplate != 'undefined' && nameTemplate.length > 0 ? nameTemplate : ''),
					DEPARTMENT_ID: (parseInt(BX.SocNetLogDestination.obSiteDepartmentID[name]) > 0 ? parseInt(BX.SocNetLogDestination.obSiteDepartmentID[name]) : 0),
					EMAIL_USERS : (BX.SocNetLogDestination.obAllowSearchEmailUsers[name] ? 'Y' : 'N'),
					CRMEMAIL : (BX.SocNetLogDestination.obAllowSearchCrmEmailUsers[name] ? 'Y' : 'N'),
					CRMCONTACTEMAIL : (BX.SocNetLogDestination.obAllowAddCrmContact[name] ? 'Y' : 'N'),
					NETWORK_SEARCH : (BX.SocNetLogDestination.obAllowSearchNetworkUsers[name] ? 'Y' : 'N'),
					ADDITIONAL_SEARCH : 'N',
					SELF : (BX.SocNetLogDestination.obAllowSearchSelf[name] ? 'Y' : 'N'),
					SEARCH_SONET_GROUPS : (BX.SocNetLogDestination.obAllowSonetGroupsAjaxSearch[name] ? 'Y' : 'N'),
					SEARCH_MAIL_CONTACTS : (BX.SocNetLogDestination.obAllowMailContactsAjaxSearch[name] ? 'Y' : 'N'),
					SEARCH_ONLY_WITH_EMAIL : (BX.SocNetLogDestination.obSearchOnlyWithEmail[name] ? 'Y' : 'N'),
					USE_PROJECTS: (BX.SocNetLogDestination.obProjectsEnable[name] ? 'Y' : 'N'),
					SEARCH_SONET_FEATUES : BX.SocNetLogDestination.obAllowSonetGroupsAjaxSearchFeatures[name],
					SITE_ID : BX.message['SITE_ID'] || ''
				};
				BX.SocNetLogDestination.oXHR = BX.ajax({
					url: BX.SocNetLogDestination.obPathToAjax[name],
					method: 'POST',
					dataType: 'json',
					data: ajaxData,
					onsuccess: function(data)
					{
						BX.SocNetLogDestination.hideSearchWaiter(name);

						if (data)
						{
							/* sync plus */
							var textAjax = (
								typeof data.SEARCH != 'undefined'
									? data.SEARCH
									: text
							);

							var finderData = BX.clone(data);

							if (
								typeof data.USERS != 'undefined'
								&& Object.keys(finderData.USERS).length > 0
							)
							{
								for (i in finderData.USERS)
								{
									if (
										finderData.USERS.hasOwnProperty(i)
										&& (
											(
												typeof finderData.USERS[i].active != 'undefined'
												&& finderData.USERS[i].active == 'N'
											)
											|| (
												typeof finderData.USERS[i].isNetwork != 'undefined'
												&& finderData.USERS[i].isNetwork == 'Y'
											)
										)
									)
									{
										delete finderData.USERS[i];
									}
								}
							}

							if (BX.SocNetLogDestination.obUseClientDatabase[name])
							{
								BX.onCustomEvent(BX.SocNetLogDestination, 'onFinderAjaxSuccess', [ finderData.USERS, BX.SocNetLogDestination ]);
							}

							if (!BX.SocNetLogDestination.bResultMoved.search)
							{
								if (
									!BX.SocNetLogDestination.oAjaxUserSearchResult[name]
									|| !BX.SocNetLogDestination.oAjaxUserSearchResult[name][textAjax.toLowerCase()]
								)
								{
									BX.SocNetLogDestination.oAjaxUserSearchResult[name] = {};
									BX.SocNetLogDestination.oAjaxUserSearchResult[name][textAjax.toLowerCase()] = [];
								}

								if (typeof data.USERS != 'undefined')
								{
									if (Object.keys(data.USERS).length > 0)
									{
										for (i in data.USERS)
										{
											if (data.USERS.hasOwnProperty(i))
											{
												bFound = true;
												BX.SocNetLogDestination.oAjaxUserSearchResult[name][textAjax.toLowerCase()].push(i);
												if (
													typeof data.USERS[i].isNetwork != 'undefined'
													&& data.USERS[i].isNetwork == 'Y'
												)
												{
													if (typeof BX.SocNetLogDestination.obItems[name].network == 'undefined')
													{
														BX.SocNetLogDestination.obItems[name].network = {};
													}
													BX.SocNetLogDestination.obItems[name].network[i] = data.USERS[i];
													BX.SocNetLogDestination.tmpSearchResult.ajax.push(i);
												}
												else
												{
													BX.SocNetLogDestination.obItems[name].users[i] = data.USERS[i];
													BX.SocNetLogDestination.tmpSearchResult.ajax.push(i);
												}
											}
										}
									}
									if (
										typeof data.CRM_EMAILS != 'undefined'
										&& Object.keys(data.CRM_EMAILS).length > 0
									)
									{
										for (i in data.CRM_EMAILS)
										{
											if (data.CRM_EMAILS.hasOwnProperty(i))
											{
												bFound = true;
//												BX.SocNetLogDestination.oAjaxCrmEmailSearchResult[name][textAjax.toLowerCase()].push(i);
												if(typeof BX.SocNetLogDestination.obItems[name].crmemails == 'undefined')
												{
													BX.SocNetLogDestination.obItems[name].crmemails = [];
												}
												BX.SocNetLogDestination.obItems[name].crmemails[i] = data.CRM_EMAILS[i];
												BX.SocNetLogDestination.tmpSearchResult.ajax.push(i);
											}
										}
									}
								}

								if (BX.SocNetLogDestination.obCrmFeed[name])
								{
									var types = {
										contacts: 'CONTACTS',
										companies: 'COMPANIES',
										leads: 'LEADS',
										deals: 'DEALS'
									};
									for (type in types)
									{
										for (i in data[types[type]])
										{
											if (data[types[type]].hasOwnProperty(i))
											{
												bFound = true;
												if (!BX.SocNetLogDestination.obItems[name][type][i])
												{
													BX.SocNetLogDestination.obItems[name][type][i] = data[types[type]][i];
													BX.SocNetLogDestination.tmpSearchResult.ajax.push(i);
												}
											}
										}
									}
								}

								if (
									!bFound
									&& BX.SocNetLogDestination.obAllowAddUser[name]
								)
								{
									var obUserEmail = BX.SocNetLogDestination.checkEmail(text.trim());

									if (
										obUserEmail !== false
										&& obUserEmail.email
										&& obUserEmail.email.length > 0
										&& typeof BX.SocNetLogDestination.obItems[name].users[obUserEmail.email] == 'undefined'
									)
									{
										BX.SocNetLogDestination.openInviteEmailUserDialog(obUserEmail, name, BX.SocNetLogDestination.obAllowAddCrmContact[name]);
									}
								}

								if (typeof data.SONET_GROUPS != 'undefined')
								{
									if (Object.keys(data.SONET_GROUPS).length > 0)
									{
										for (i in data.SONET_GROUPS)
										{
											if (data.SONET_GROUPS.hasOwnProperty(i))
											{
												bFound = true;
												BX.SocNetLogDestination.obItems[name].sonetgroups[i] = data.SONET_GROUPS[i];
												BX.SocNetLogDestination.tmpSearchResult.ajax.push(i);
											}
										}
									}
								}

								if (typeof data.PROJECTS != 'undefined')
								{
									if (Object.keys(data.PROJECTS).length > 0)
									{
										for (i in data.PROJECTS)
										{
											if (data.PROJECTS.hasOwnProperty(i))
											{
												bFound = true;
												BX.SocNetLogDestination.obItems[name].projects[i] = data.PROJECTS[i];
												BX.SocNetLogDestination.tmpSearchResult.ajax.push(i);
											}
										}
									}
								}
								if (typeof data.MAIL_CONTACTS != 'undefined')
								{
									if (Object.keys(data.MAIL_CONTACTS).length > 0)
									{
										for (i in data.MAIL_CONTACTS)
										{
											if (data.MAIL_CONTACTS.hasOwnProperty(i))
											{
												bFound = true;
												if (typeof BX.SocNetLogDestination.obItems[name].mailContacts == 'undefined')
												{
													BX.SocNetLogDestination.obItems[name].mailContacts = {};
												}
												BX.SocNetLogDestination.obItems[name].mailContacts[i] = data.MAIL_CONTACTS[i];
												BX.SocNetLogDestination.tmpSearchResult.ajax.push(i);
											}
										}
									}
								}

								BX.SocNetLogDestination.tmpSearchResult.ajax.filter(function(el, index, arr) {
									return index == arr.indexOf(el);
								});

								BX.SocNetLogDestination.search(
									text,
									false,
									name,
									nameTemplate,
									{
										textAjax: textAjax
									}
								);
							}

							if (BX.SocNetLogDestination.obAllowSearchNetworkUsers[name])
							{
								var contentArea = BX.findChildren(BX.SocNetLogDestination.popupSearchWindowContent,
									{
										'className': 'bx-finder-groupbox-content'
									},
									true
								);

								BX.SocNetLogDestination.searchButton = BX.create('span', {
									props : {
										'className' : "bx-finder-box-button"
									},
									text: BX.message('LM_POPUP_SEARCH_NETWORK')
								});

								var foundUsers = BX.findChildren(contentArea[0], {tagName: 'a'}, true);
								if (!foundUsers || foundUsers.length <= 0)
								{
									contentArea[0].innerHTML = '';
								}
								contentArea[0].appendChild(BX.SocNetLogDestination.searchButton);
								BX.bind(BX.SocNetLogDestination.searchButton, 'click', function()
								{
									BX.SocNetLogDestination.showSearchWaiter(name);
									BX.SocNetLogDestination.searchNetwork(text, name, nameTemplate, finderData, textAjax, ajaxData);
								});
							}
						}
					},
					onfailure: function(data)
					{
						BX.SocNetLogDestination.hideSearchWaiter(name);
					}
				});
			}, 1000);
		}
	}
};

BX.SocNetLogDestination.searchNetwork = function(text, name, nameTemplate, finderData, textAjax, ajaxData)
{
	ajaxData['ADDITIONAL_SEARCH'] = 'Y';
	BX.SocNetLogDestination.oXHR = BX.ajax({
		url: BX.SocNetLogDestination.obPathToAjax[name],
		method: 'POST',
		dataType: 'json',
		data: ajaxData,
		onsuccess: function(data)
		{
			BX.SocNetLogDestination.hideSearchWaiter(name);
			if (data && typeof data.USERS != 'undefined')
			{
				if (typeof BX.SocNetLogDestination.obItems[name].network == 'undefined')
				{
					BX.SocNetLogDestination.obItems[name].network = {};
				}

				for (var i in data.USERS)
				{
					if (data.USERS.hasOwnProperty(i))
					{
						bFound = true;
						BX.SocNetLogDestination.oAjaxUserSearchResult[name][textAjax.toLowerCase()].push(i);
						BX.SocNetLogDestination.obItems[name].network[i] = data.USERS[i];
					}
				}

				BX.SocNetLogDestination.search(
					text,
					false,
					name,
					nameTemplate,
					{
						textAjax: textAjax
					}
				);
			}
			else
			{
				BX.SocNetLogDestination.popupSearchWindow.destroy();
			}
		},
		onfailure: function(data)
		{
			BX.SocNetLogDestination.hideSearchWaiter(name);
		}
	});};

BX.SocNetLogDestination.openSearch = function(items, name, params)
{
	if (!name)
	{
		name = 'lm';
	}

	if (!params)
	{
		params = {};
	}

	if (BX.SocNetLogDestination.popupWindow != null)
	{
		BX.SocNetLogDestination.popupWindow.close();
	}

	if (BX.SocNetLogDestination.popupSearchWindow != null)
	{
		BX.SocNetLogDestination.popupSearchWindow.close();
		return false;
	}

	if (!!BX.SocNetLogDestination.obUseContainer[name])
	{
		var bCreateNode = false;
		if (BX('bx-lm-box-search-content'))
		{
			BX('bx-lm-box-search-content').innerHTML = BX.SocNetLogDestination.getItemLastHtml(items, true, name);
		}
		else
		{
			bCreateNode = true;
			BX.cleanNode(BX('destSearchTabContent_' + name));
			BX('destSearchTabContent_' + name).appendChild(BX.SocNetLogDestination.getSearchContent(items, name, params));
		}
		BX.SocNetLogDestination.SwitchTab(name, BX('destSearchTab_' + name), 'search');
		BX.SocNetLogDestination.containerWindow.setAngle({});

		if (bCreateNode)
		{
			BX.SocNetLogDestination.oSearchWaiterContentHeight = BX.pos(BX('bx-lm-box-search-tabs-content')).height;
		}
	}
	else
	{
		var bindNode = (
			typeof params.bindNode != 'undefined'
				? params.bindNode
				: BX.SocNetLogDestination.obElementBindSearchPopup[name].node
		);

		BX.SocNetLogDestination.popupSearchWindow = new BX.PopupWindow({
			id: "BXSocNetLogDestinationSearch",
			bindElement: bindNode,
			autoHide: true,
			zIndex: 1200,
			className: 'bx-finder-popup bx-finder-v2',
			offsetLeft: parseInt(BX.SocNetLogDestination.obElementBindSearchPopup[name].offsetLeft),
			offsetTop: parseInt(BX.SocNetLogDestination.obElementBindSearchPopup[name].offsetTop),
			bindOptions: BX.SocNetLogDestination.obBindOptions[name],
			closeByEsc: true,
			lightShadow: true,
			events: {
				onPopupShow : function() {
					if (
						BX.SocNetLogDestination.sendEvent
						&& BX.SocNetLogDestination.obCallback[name]
						&& BX.SocNetLogDestination.obCallback[name].openSearch
					)
					{
						BX.SocNetLogDestination.obCallback[name].openSearch(name);
					}

					if (
						BX.SocNetLogDestination.inviteEmailUserWindow
						&& BX.SocNetLogDestination.inviteEmailUserWindow.isShown()
					)
					{
						BX.SocNetLogDestination.inviteEmailUserWindow.close();
					}
				},
				onPopupClose : function() {
					this.destroy();
					if (
						BX.SocNetLogDestination.sendEvent
						&& BX.SocNetLogDestination.obCallback[name]
						&& BX.SocNetLogDestination.obCallback[name].closeSearch
					)
					{
						BX.SocNetLogDestination.obCallback[name].closeSearch(name);
					}
				},
				onPopupDestroy : BX.proxy(function() {
					BX.SocNetLogDestination.popupSearchWindow = null;
					BX.SocNetLogDestination.popupSearchWindowContent = null;

					if (
						BX.SocNetLogDestination.sendEvent
						&& BX.SocNetLogDestination.obCallback[name]
					)
					{
						if (BX.SocNetLogDestination.obCallback[name].closeSearch)
						{
							BX.SocNetLogDestination.obCallback[name].closeSearch(name);
						}
					}
				}, this)
			},
			content: BX.SocNetLogDestination.getSearchContent(items, name, params)
		});

		BX.SocNetLogDestination.popupSearchWindow.setAngle({});
		BX.SocNetLogDestination.popupSearchWindow.show();

		BX.SocNetLogDestination.oSearchWaiterContentHeight = BX.pos(BX('bx-lm-box-search-tabs-content')).height;
	}

	BX.SocNetLogDestination.popupSearchWindowContent = BX('bx-lm-box-search-content');
};

BX.SocNetLogDestination.drawItemsGroup = function(lastItems, groupCode, name, search, count, params)
{
	var itemsHtml = (
		typeof params.itemsHtml != 'undefined'
		&& params.itemsHtml
			? params.itemsHtml
			: ''
	);

	var doSort = true;
	var i = null;

	var keys = lastItems[groupCode]? Object.keys(lastItems[groupCode]): [];
	for (var item in lastItems[groupCode])
	{
		if (item === true)
		{
			doSort = false;
			break;
		}
	}

	if (doSort)
	{
		keys.sort(function(a, b) {
			return parseInt(lastItems[groupCode][a]) - parseInt(lastItems[groupCode][b]);
		});
	}
	for (var index = 0; index < keys.length; index++)
	{
		i = keys[index];

		if (!BX.SocNetLogDestination.obItems[name][groupCode][i])
		{
			continue;
		}

		itemsHtml += BX.SocNetLogDestination.getHtmlByTemplate7(
			name,
			BX.SocNetLogDestination.obItems[name][groupCode][i],
			{
				className: params.className + (
					groupCode == 'sonetgroups'
					&& typeof params.classNameExtranetGroup != 'undefined'
					&& typeof window['arExtranetGroupID'] != 'undefined'
					&& BX.util.in_array(BX.SocNetLogDestination.obItems[name][groupCode][i].entityId, window['arExtranetGroupID'])
						? ' ' + params.classNameExtranetGroup
						: ''
				) + (
					typeof BX.SocNetLogDestination.obItems[name][groupCode][i].active != 'undefined'
					&& BX.SocNetLogDestination.obItems[name][groupCode][i].active == 'N'
						? ' bx-lm-element-inactive'
						: ''
				),
				descLessMode: (typeof params.descLessMode != 'undefined' && params.descLessMode ? true : false),
				emailDescMode: (
					typeof params.emailDescMode !== 'undefined'
						? (params.emailDescMode === true)
						: BX.SocNetLogDestination.obEmailDescMode[name]
				),
				itemType: groupCode,
				search: search,
				avatarLessMode: (typeof params.avatarLessMode != 'undefined' && params.avatarLessMode ? true : false),
				itemHover: (
					//search &&
					count <= 0
				)
			},
			(search ? 'search' : 'last')
		);

		count++;
	}

	if (
		itemsHtml != ''
		&& (
			typeof params.bHideGroup == 'undefined'
			|| !params.bHideGroup
		)
	)
	{
		itemsHtml = '<span class="bx-finder-groupbox ' + (typeof params.groupboxClassName != 'undefined' ? params.groupboxClassName : 'bx-lm-groupbox-last')+ '">' +
			'<span class="bx-finder-groupbox-name">' + params.title + ':</span>' +
			'<span class="bx-finder-groupbox-content">' + itemsHtml + '</span>' +
		'</span>';
	}

	return {
		html: itemsHtml,
		count: count
	};
};
/* vizualize lastItems - search result */

BX.SocNetLogDestination.getItemLastHtml = function(lastItems, search, name)
{
	if(!name)
	{
		name = 'lm';
	}

	if (!lastItems)
	{
		lastItems = BX.SocNetLogDestination.obItemsLast[name];
	}

	var html = '';
	var tmpHtml = null;
	var count = 0;
	var drawResult = null;
	var dialogGroup = null;

	for (var i = 0; i < BX.SocNetLogDestination.arDialogGroups[name].length; i++)
	{
		dialogGroup = BX.SocNetLogDestination.arDialogGroups[name][i];
		if (
			(
				dialogGroup.bMail
				&& BX.SocNetLogDestination.obSearchOnlyWithEmail[name]
			)
			||
			(
				dialogGroup.bCrm
				&& BX.SocNetLogDestination.obCrmFeed[name]
			)
			||
			(
				!dialogGroup.bCrm
				&& (
					search
					|| !BX.SocNetLogDestination.obCrmFeed[name]
				)
			)
		)
		{
			drawResult = BX.SocNetLogDestination.drawItemsGroup(
				lastItems,
				dialogGroup.groupCode,
				name,
				search,
				count,
				{
					itemsHtml: (tmpHtml ? tmpHtml : false),
					bHideGroup: (
						typeof dialogGroup.bHideGroup != 'undefined'
							? dialogGroup.bHideGroup
							: false
					),
					className: (
						typeof dialogGroup.className != 'undefined'
							? dialogGroup.className
							: false
					),
					classNameExtranetGroup: (
						typeof dialogGroup.classNameExtranetGroup != 'undefined'
							? dialogGroup.classNameExtranetGroup
							: false
					),
					groupboxClassName: (
						typeof dialogGroup.groupboxClassName != 'undefined'
							? dialogGroup.groupboxClassName
							: false
					),
					avatarLessMode: (
						typeof dialogGroup.avatarLessMode != 'undefined'
							? dialogGroup.avatarLessMode
							: false
					),
					descLessMode: (
						typeof dialogGroup.descLessMode != 'undefined'
							? dialogGroup.descLessMode
							: false
					),
					emailDescMode: (
						typeof dialogGroup.emailDescMode !== 'undefined'
							? (dialogGroup.emailDescMode === true)
							: BX.SocNetLogDestination.obEmailDescMode[name]
					),
					title: (
						typeof dialogGroup.title != 'undefined'
							? dialogGroup.title
							: ''
					)
				}
			);

			if (drawResult.html.length > 0)
			{
				if (
					dialogGroup.bHideGroup != 'undefined'
					&& dialogGroup.bHideGroup
				)
				{
					tmpHtml = drawResult.html;
				}
				else
				{
					html += drawResult.html;
					tmpHtml = null;
				}
			}
			count = drawResult.count;
		}
	}

	if (html.length <= 0)
	{
		html = '<span class="bx-finder-groupbox bx-lm-groupbox-search">'+
			'<span class="bx-finder-groupbox-content" id="bx-lm-box-waiter-content-text">' + BX.message(search ? 'LM_SEARCH_PLEASE_WAIT' : 'LM_EMPTY_LIST') + '</span>'+
		'</span>';
	}

	return html;
};

BX.SocNetLogDestination.getItemDepartmentHtml = function(name, relation, categoryId, categoryOpened)
{
	if(!name)
	{
		name = 'lm';
	}

	categoryId = categoryId ? categoryId: false;
	categoryOpened = categoryOpened ? true: false;

	var bFirstRelation = false;
	var
		activeClass = null,
		i = null;

	if (
		typeof relation == 'undefined'
		|| !relation
	) // root
	{
		relation = BX.SocNetLogDestination.obItems[name].departmentRelation;
		bFirstRelation = true;
	}

	var html = '';
	for (i in relation)
	{
		if (
			relation.hasOwnProperty(i)
			&& relation[i].type == 'category'
		)
		{
			var category = BX.SocNetLogDestination.obItems[name].department[relation[i].id];
			activeClass = (
				BX.SocNetLogDestination.obItemsSelected[name][relation[i].id]
					? BX.SocNetLogDestination.obTemplateClassSelected['department']
					: ''
			);
			bFirstRelation = (bFirstRelation && category.id != 'EX');

			html += '<div class="bx-finder-company-department' + (bFirstRelation ? ' bx-finder-company-department-opened' : '') + '">\
				<a href="#' + category.id + '" class="bx-finder-company-department-inner" onclick="return BX.SocNetLogDestination.OpenCompanyDepartment(\'' + name + '\', this.parentNode, \'' + category.entityId + '\')" hidefocus="true">\
					<div class="bx-finder-company-department-arrow"></div>\
					<div class="bx-finder-company-department-text">' + category.name + '</div>\
				</a>\
			</div>';

			html += '<div class="bx-finder-company-department-children'+(bFirstRelation? ' bx-finder-company-department-children-opened': '')+'">';
			if(
				!BX.SocNetLogDestination.obDepartmentSelectDisable[name]
				&& !bFirstRelation
				&& category.id != 'EX'
			)
			{
				html += '<a class="bx-finder-company-department-check '+activeClass+' bx-finder-element" hidefocus="true" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, \'department\', \''+relation[i].id+'\', \'department\')" rel="'+relation[i].id+'" href="#'+relation[i].id+'">';
				html += '<span class="bx-finder-company-department-check-inner">\
						<div class="bx-finder-company-department-check-arrow"></div>\
						<div class="bx-finder-company-department-check-text" rel="'+category.name+': '+BX.message("LM_POPUP_CHECK_STRUCTURE")+'">'+BX.message("LM_POPUP_CHECK_STRUCTURE")+'</div>\
					</span>\
				</a>';
			}
			html += BX.SocNetLogDestination.getItemDepartmentHtml(name, relation[i].items, category.entityId, bFirstRelation);
			html += '</div>';
		}
	}

	if (categoryId)
	{
		html += '<div class="bx-finder-company-department-employees" id="bx-lm-category-relation-'+categoryId+'">';
		userCount = 0;
		for (i in relation)
		{
			if (
				relation.hasOwnProperty(i)
				&& relation[i].type == 'user'
			)
			{
				var user = BX.SocNetLogDestination.obItems[name].users[relation[i].id];
				if (user == null)
				{
					continue;
				}

				activeClass = (
					BX.SocNetLogDestination.obItemsSelected[name][relation[i].id]
						? BX.SocNetLogDestination.obTemplateClassSelected['department-user']
						: ''
				);
				html += '<a href="#'+user.id+'" class="bx-finder-company-department-employee '+activeClass+' bx-finder-element" rel="'+user.id+'" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, \'department-user\', \''+user.id+'\', \'users\')" hidefocus="true">\
					<div class="bx-finder-company-department-employee-info">\
						<div class="bx-finder-company-department-employee-name">'+user.name+'</div>\
						<div class="bx-finder-company-department-employee-position">'+user.desc+'</div>\
					</div>\
					<div style="'+(user.avatar? 'background:url(\''+encodeURI(user.avatar)+'\') no-repeat center center; background-size: cover;': '')+'" class="bx-finder-company-department-employee-avatar"></div>\
				</a>';
				userCount++;
			}
		}
		if (userCount <= 0)
		{
			if (!BX.SocNetLogDestination.obDepartmentLoad[name][categoryId])
			{
				html += '<div class="bx-finder-company-department-employees-loading">' + BX.message('LM_PLEASE_WAIT') + '</div>';
			}

			if (categoryOpened)
			{
				BX.SocNetLogDestination.getDepartmentRelation(name, categoryId);
			}
		}
		html += '</div>';
	}

	return html;
};

BX.SocNetLogDestination.getTabContentHtml = function(name, type, params)
{
	if(!name)
	{
		name = 'lm';
	}

	var html = '';
	var count = 0;
	var itemType = (!!params.itemType ? params.itemType : false);
	var className = null, descLessMode = true, avatarLessMode = false;
	var emailDescMode = BX.SocNetLogDestination.obEmailDescMode[name];

	var bFound = false, groupParam;
	for (var j=0; j < BX.SocNetLogDestination.arDialogGroups[name].length; j++)
	{
		if (BX.SocNetLogDestination.arDialogGroups[name][j].groupCode == type)
		{
			groupParam = BX.SocNetLogDestination.arDialogGroups[name][j];
			bFound = true;
			break;
		}
	}

	if (bFound)
	{
		emailDescMode = typeof groupParam.emailDescMode != 'undefined' ? groupParam.emailDescMode : emailDescMode;
		descLessMode = typeof groupParam.descLessMode != 'undefined' ? groupParam.descLessMode : descLessMode;
		if (emailDescMode === true)
		{
			descLessMode = false;
		}
		avatarLessMode = typeof groupParam.avatarLessMode != 'undefined' ? groupParam.avatarLessMode : avatarLessMode;
	}

	if (type == 'email')
	{
		className = 'bx-lm-element-user bx-lm-element-email';
	}
	else if (type == 'crmemail')
	{
		className = 'bx-lm-element-user bx-lm-element-email bx-lm-element-crmemail';
	}

	if (itemType)
	{
		for (var i in BX.SocNetLogDestination.obItems[name][itemType])
		{
			if (!BX.SocNetLogDestination.obItems[name][itemType].hasOwnProperty(i))
			{
				continue;
			}

			if (type == 'group')
			{
				className = 'bx-lm-element-sonetgroup' + (
					typeof window['arExtranetGroupID'] != 'undefined'
					&& BX.util.in_array(BX.SocNetLogDestination.obItems[name].sonetgroups[i].entityId, window['arExtranetGroupID'])
						? ' bx-lm-element-extranet'
						: ''
				);
			}
			else if (type == 'project')
			{
				className = 'bx-lm-element-sonetgroup' + (
					typeof window['arExtranetGroupID'] != 'undefined'
					&& BX.util.in_array(BX.SocNetLogDestination.obItems[name].projects[i].entityId, window['arExtranetGroupID'])
						? ' bx-lm-element-extranet'
						: ''
				);
			}

			html += BX.SocNetLogDestination.getHtmlByTemplate7(
				name,
				BX.SocNetLogDestination.obItems[name][itemType][i],
				{
					className: className,
					descLessMode : descLessMode,
					emailDescMode: emailDescMode,
					avatarLessMode: avatarLessMode,
					itemType: itemType,
					itemHover: (count <= 0)
				},
				type
			);
			count++;
		}
	}

	return html;
};

BX.SocNetLogDestination.getDepartmentRelation = function(name, departmentId)
{
	if (BX.SocNetLogDestination.obDepartmentLoad[name][departmentId])
	{
		return false;
	}

	BX.ajax({
		url: BX.SocNetLogDestination.obPathToAjax[name],
		method: 'POST',
		dataType: 'json',
		data: {
			LD_DEPARTMENT_RELATION : 'Y',
			DEPARTMENT_ID : departmentId,
			sessid: BX.bitrix_sessid(),
			nt: BX.SocNetLogDestination.obUserNameTemplate[name]
		},
		onsuccess: function(data){
			BX.SocNetLogDestination.obDepartmentLoad[name][departmentId] = true;
			var departmentItem = BX.util.object_search_key((departmentId == 'EX' ? departmentId : 'DR'+departmentId), BX.SocNetLogDestination.obItems[name].departmentRelation);

			html = '';
			for(var i in data.USERS)
			{
				if (data.USERS.hasOwnProperty(i))
				{
					if (!BX.SocNetLogDestination.obItems[name].users[i])
					{
						BX.SocNetLogDestination.obItems[name].users[i] = data.USERS[i];
					}

					if (!departmentItem.items[i])
					{
						departmentItem.items[i] = {'id': i,	'type': 'user'};
						var activeClass = (
							BX.SocNetLogDestination.obItemsSelected[name][data.USERS[i].id]
								? BX.SocNetLogDestination.obTemplateClassSelected['department-user']
								: ''
						);
						html += '<a href="#'+data.USERS[i].id+'" class="bx-finder-company-department-employee '+activeClass+' bx-finder-element" rel="'+data.USERS[i].id+'" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, \'department-user\', \''+data.USERS[i].id+'\', \'users\')" hidefocus="true">\
							<div class="bx-finder-company-department-employee-info">\
								<div class="bx-finder-company-department-employee-name">'+data.USERS[i].name+'</div>\
								<div class="bx-finder-company-department-employee-position">'+data.USERS[i].desc+'</div>\
							</div>\
							<div style="'+(data.USERS[i].avatar? 'background:url(\''+encodeURI(data.USERS[i].avatar)+'\') no-repeat center center; background-size: cover;': '')+'" class="bx-finder-company-department-employee-avatar"></div>\
						</a>';
					}
				}
			}
			BX('bx-lm-category-relation-'+departmentId).innerHTML = html;

			if (!!BX.SocNetLogDestination.obUseContainer[name])
			{
				BX.SocNetLogDestination.containerWindow.adjustPosition();
			}
			else
			{
				BX.SocNetLogDestination.popupWindow.adjustPosition();
			}
		},
		onfailure: function(data)	{}
	});
};

BX.SocNetLogDestination.getHtmlByTemplate1 = function(name, item, params)
{
	if(!name)
		name = 'lm';
	if(!params)
		params = {};

	var activeClass = (
		BX.SocNetLogDestination.obItemsSelected[name][item.id]
			? ' ' + BX.SocNetLogDestination.obTemplateClassSelected[1]
			: ''
	);
	var hoverClass = params.itemHover? 'bx-finder-box-item-hover': '';
	return '<a id="' + name + '_' + item.id + '" class="' + BX.SocNetLogDestination.obTemplateClass[1] + ' '+activeClass+' '+hoverClass+' bx-finder-element'+(params.className? ' '+params.className: '')+'" hidefocus="true" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, 1, \''+item.id+'\', \''+(params.itemType? params.itemType: 'item')+'\', '+(params.search? true: false)+')" rel="'+item.id+'" href="#'+item.id+'">\
		<div class="bx-finder-box-item-text">'+item.name+'</div>\
	</a>';
};

BX.SocNetLogDestination.getHtmlByTemplate2 = function(name, item, params)
{
	if(!name)
		name = 'lm';
	if(!params)
		params = {};

	var activeClass = (
		BX.SocNetLogDestination.obItemsSelected[name][item.id]
			? ' ' + BX.SocNetLogDestination.obTemplateClassSelected[2]
			: ''
	);
	var hoverClass = params.itemHover? 'bx-finder-box-item-t2-hover': '';
	return '<a id="' + name + '_' + item.id + '" class="' + BX.SocNetLogDestination.obTemplateClass[2] + ' '+activeClass+' '+hoverClass+' bx-finder-element'+(params.className? ' '+params.className: '')+'" hidefocus="true" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, 2, \''+item.id+'\', \''+(params.itemType? params.itemType: 'item')+'\', '+(params.search? true: false)+')" rel="'+item.id+'" href="#'+item.id+'">\
		<div class="bx-finder-box-item-t2-text">'+item.name+'</div>\
	</a>';
};

BX.SocNetLogDestination.getHtmlByTemplate3 = function(name, item, params)
{
	if(!name)
		name = 'lm';
	if(!params)
		params = {};

	var activeClass = (
		BX.SocNetLogDestination.obItemsSelected[name][item.id]
			? ' ' + BX.SocNetLogDestination.obTemplateClassSelected[3]
			: ''
	);
	var hoverClass = params.itemHover? 'bx-finder-box-item-t3-hover': '';

	return '<a id="' + name + '_' + item.id + '" hidefocus="true" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, 3, \''+item.id+'\', \''+(params.itemType? params.itemType: 'item')+'\', '+(params.search? true: false)+')" rel="'+item.id+'" class="' + BX.SocNetLogDestination.obTemplateClass[3] + ' '+activeClass+' '+hoverClass+' bx-finder-element'+(params.className? ' '+params.className: '')+'" href="#'+item.id+'">'+
		'<div class="bx-finder-box-item-t3-avatar" '+(item.avatar? 'style="background:url(\''+encodeURI(item.avatar)+'\') no-repeat center center; background-size: cover;"':'')+'></div>'+
		'<div class="bx-finder-box-item-t3-info">'+
			'<div class="bx-finder-box-item-t3-name">'+item.name+'</div>'+
			(item.desc? '<div class="bx-finder-box-item-t3-desc">'+item.desc+'</div>': '')+
		'</div>'+
		'<div class="bx-clear"></div>'+
	'</a>';
};

BX.SocNetLogDestination.getHtmlByTemplate5 = function(name, item, params)
{
	if(!name)
		name = 'lm';
	if(!params)
		params = {};

	var activeClass = (
		BX.SocNetLogDestination.obItemsSelected[name][item.id]
			? ' ' + BX.SocNetLogDestination.obTemplateClassSelected[5]
			: ''
	);
	var hoverClass = params.itemHover? 'bx-finder-box-item-t5-hover': '';
	return '<a id="' + name + '_' + item.id + '" hidefocus="true" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, 5, \''+item.id+'\', \''+(params.itemType? params.itemType: 'item')+'\', '+(params.search? true: false)+')" rel="'+item.id+'" class="' + BX.SocNetLogDestination.obTemplateClass[5] + ' '+activeClass+' '+hoverClass+' bx-finder-element'+(params.className? ' '+params.className: '')+'" href="#'+item.id+'">'+
		'<div class="bx-finder-box-item-t5-avatar" '+(item.avatar? 'style="background:url(\''+encodeURI(item.avatar)+'\') no-repeat center center; background-size: cover;"':'')+'></div>'+
		'<div class="bx-finder-box-item-t5-info">'+
			'<div class="bx-finder-box-item-t5-name">'+item.name+'</div>'+
			(item.desc? '<div class="bx-finder-box-item-t5-desc">'+item.desc+'</div>': '')+
		'</div>'+
		'<div class="bx-clear"></div>'+
	'</a>';
};

BX.SocNetLogDestination.getHtmlByTemplate6 = function(name, item, params)
{
	if(!name)
		name = 'lm';
	if(!params)
		params = {};

	var activeClass = (
		BX.SocNetLogDestination.obItemsSelected[name][item.id]
			? ' ' + BX.SocNetLogDestination.obTemplateClassSelected[6]
			: ''
	);
	var hoverClass = params.itemHover? 'bx-finder-box-item-t6-hover': '';
	return '<a id="' + name + '_' + item.id + '" hidefocus="true" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, 6, \''+item.id+'\', \''+(params.itemType? params.itemType: 'item')+'\', '+(params.search? true: false)+')" rel="'+item.id+'" class="' + BX.SocNetLogDestination.obTemplateClass[6] + ' '+activeClass+' '+hoverClass+' bx-finder-element'+(params.className? ' '+params.className: '')+'" href="#'+item.id+'">'+
		'<div class="bx-finder-box-item-t6-avatar" '+(item.avatar? 'style="background:url(\''+encodeURI(item.avatar)+'\') no-repeat center center; background-size: cover;"':'')+'></div>'+
		'<div class="bx-finder-box-item-t6-info">'+
			'<div class="bx-finder-box-item-t6-name">'+item.name+'</div>'+
			(item.desc? '<div class="bx-finder-box-item-t6-desc">'+item.desc+'</div>': '')+
		'</div>'+
		'<div class="bx-clear"></div>'+
	'</a>';
};

BX.SocNetLogDestination.getHtmlByTemplate7 = function(name, item, params, type)
{
	if(!name)
	{
		name = 'lm';
	}

	if(!params)
	{
		params = {};
	}

	if(!type)
	{
		type = '';
	}

	var showDesc = BX.type.isNotEmptyString(item.desc);
	showDesc = params.descLessMode && params.descLessMode == true ? false : showDesc;
	showDesc = showDesc || item.showDesc;

	var emailDescMode = (typeof params.emailDescMode != 'undefined' && params.emailDescMode == true);
	if (emailDescMode === true)
	{
		showDesc = true;
	}


	var itemClass = BX.SocNetLogDestination.obTemplateClass[7] + " bx-finder-element";
	itemClass += BX.SocNetLogDestination.obItemsSelected[name][item.id]
		? ' ' + BX.SocNetLogDestination.obTemplateClassSelected[7]
		: '';
	itemClass += params.itemHover ? ' bx-finder-box-item-t7-hover': '';
	itemClass += showDesc ? ' bx-finder-box-item-t7-desc-mode': '';
	itemClass += params.className ? ' ' + params.className: '';
	itemClass += params.avatarLessMode && params.avatarLessMode == true ? ' bx-finder-box-item-t7-avatarless' : '';

	if (
		(typeof item.isExtranet != 'undefined' && item.isExtranet == 'Y')
		|| (typeof item.isNetwork != 'undefined' && item.isNetwork == 'Y')
	)
	{
		itemClass += ' bx-lm-element-extranet';
	}

	if (
		typeof item.isCrmEmail != 'undefined'
		&& item.isCrmEmail == 'Y'
	)
	{
		itemClass += ' bx-lm-element-crmemail';
	}

	if (
		typeof item.isEmail != 'undefined'
		&& item.isEmail == 'Y'
	)
	{
		itemClass += ' bx-lm-element-email';
	}

	if (
		BX.type.isNotEmptyString(params.itemType)
		&& params.itemType == 'users'
		&& typeof BX.SocNetLogDestination.obShowVacations[name] != 'undefined'
		&& BX.SocNetLogDestination.obShowVacations[name] === true
		&& typeof BX.SocNetLogDestination.usersVacation[item.entityId] != 'undefined'
		&& BX.SocNetLogDestination.usersVacation[item.entityId]
	)
	{
		itemClass += ' bx-lm-element-vacation';
	}

	var itemName = item.name, itemDesc = '';

	if(
		emailDescMode === false
		&& typeof item.showEmail != 'undefined'
		&& item.showEmail == 'Y'
		&& typeof item.email != 'undefined'
		&& item.email
		&& item.email.length > 0
	)
	{
		itemName += ' (' + item.email + ')';
	}


	if(showDesc)
	{
		if(
			emailDescMode === true
			&& typeof item.email != 'undefined'
			&& item.email
			&& item.email.length
		)
		{
			itemDesc = item.email;
		}
		else
		{
			itemDesc = item.desc;
		}
	}

	var classAvatarWrapper = item.iconCustom ?
		'bx-finder-box-item-t7-avatar bx-finder-box-item-t7-avatar-custom' : 'bx-finder-box-item-t7-avatar';

	return '<a id="' + name + '_' + type + '_' + item.id + '" hidefocus="true" onclick="return BX.SocNetLogDestination.selectItem(\''+name+'\', this, 7, \''+item.id+'\', \''+(params.itemType? params.itemType: 'item')+'\', '+(params.search? true: false)+')" rel="'+item.id+'" class="' + itemClass + '" href="#'+item.id+'">'+
		(
			item.avatar
				? '<div class="bx-finder-box-item-t7-avatar"><img bx-lm-item-id="' + item.id + '" bx-lm-item-type="' + params.itemType + '" class="bx-finder-box-item-t7-avatar-img" src="' + encodeURI(item.avatar) + '" onerror="BX.onCustomEvent(\'removeClientDbObject\', [BX.SocNetLogDestination, this.getAttribute(\'bx-lm-item-id\'), this.getAttribute(\'bx-lm-item-type\')]); BX.cleanNode(this, true);"><span class="bx-finder-box-item-avatar-status"></span></div>'
				: '<div class="' + classAvatarWrapper + '">' + (item.iconCustom ? item.iconCustom : '')
				+ '<span class="bx-finder-box-item-avatar-status"></span></div>'
		) +
		'<div class="bx-finder-box-item-t7-space"></div>' +
		'<div class="bx-finder-box-item-t7-info">'+
		'<div class="bx-finder-box-item-t7-name">'+itemName+'</div>'+
		(showDesc? '<div class="bx-finder-box-item-t7-desc">'+itemDesc+'</div>': '')+
		'</div>'+
	'</a>';
};


BX.SocNetLogDestination.SwitchTab = function(name, currentTab, type)
{
	var tabsContent = BX.findChildren(
		BX.findChild(
			currentTab.parentNode.parentNode,
			{ tagName : "td", className : "bx-finder-box-tabs-content-cell"},
			true
		),
		{ tagName : "div" }
	);

	if (!tabsContent)
	{
		return false;
	}

	var tabIndex = 0;
	var i = 0;
	var tabs = BX.findChildren(currentTab.parentNode, { tagName : "a" });
	for (i = 0; i < tabs.length; i++)
	{
		if (tabs[i] === currentTab)
		{
			BX.addClass(tabs[i], "bx-finder-box-tab-selected");
			tabIndex = i;
		}
		else
		{
			BX.removeClass(tabs[i], "bx-finder-box-tab-selected");
		}
	}

	for (i = 0; i < tabsContent.length; i++)
	{
		if (tabIndex === i)
		{
			if (type == 'last')
			{
				tabsContent[i].innerHTML = BX.SocNetLogDestination.getItemLastHtml(false, false, name);
			}
			else if (type == 'department')
			{
				tabsContent[i].innerHTML = BX.SocNetLogDestination.getItemDepartmentHtml(name);
			}
			else if (BX.util.in_array(type, ['group', 'project', 'email', 'crmemail']))
			{
				var itemType = null;

				if (type == 'email')
				{
					itemType = 'emails';
				}
				else if (type == 'crmemail')
				{
					itemType = 'crmemails';
				}
				else if (type == 'group')
				{
					itemType = 'sonetgroups';
				}
				else if (type == 'project')
				{
					itemType = 'projects';
				}
				else if (type == 'emailContacts')
				{
					itemType = 'emailContacts';
				}
				tabsContent[i].innerHTML = BX.SocNetLogDestination.getTabContentHtml(name, type, {
					itemType: itemType
				});
			}
			else if (typeof BX.SocNetLogDestination.obCustomTabs[name] != 'undefined')
			{
				var customTab = null;
				for (var j=0;j<BX.SocNetLogDestination.obCustomTabs[name].length;j++)
				{
					customTab = BX.SocNetLogDestination.obCustomTabs[name][j];
					if (customTab.id == type)
					{
						if (typeof customTab.itemType != 'undefined')
						{
							tabsContent[i].innerHTML = BX.SocNetLogDestination.getTabContentHtml(name, type, {
								itemType: customTab.itemType
							});
						}

						break;
					}
				}
			}

			BX.addClass(tabsContent[i], "bx-finder-box-tab-content-selected");
		}
		else
		{
			BX.removeClass(tabsContent[i], "bx-finder-box-tab-content-selected");
		}
	}

	var ob = {
		id: name
	};
	BX.onCustomEvent(window, 'BX.SocNetLogDestination:onBeforeSwitchTabFocus', [ ob ]);
	setTimeout(function() {
		if (
			typeof ob.blockFocus == 'undefined'
			|| !ob.blockFocus
		)
		{
			BX.focus(BX.SocNetLogDestination.obElementSearchInput[name]);
		}
	}, 1);

	if (type == 'last')
	{
		BX.SocNetLogDestination.initResultNavigation(name, 'last', BX.SocNetLogDestination.obItemsLast[name]);
	}
	else if (type == 'group')
	{
		BX.SocNetLogDestination.initResultNavigation(name, type, {
			sonetgroups: BX.SocNetLogDestination.obItems[name].sonetgroups
		});
	}
	else if (type == 'project')
	{
		BX.SocNetLogDestination.initResultNavigation(name, type, {
			projects: BX.SocNetLogDestination.obItems[name].projects
		});
	}
	else if (type == 'mailContacts')
	{
		BX.SocNetLogDestination.initResultNavigation(name, type, {
			mailContacts: BX.SocNetLogDestination.obItems[name].mailContacts
		});
	}
	else if (type == 'email')
	{
		BX.SocNetLogDestination.initResultNavigation(name, type, {
			emails: BX.SocNetLogDestination.obItems[name].emails
		});
	}
	else if (type == 'crmemail')
	{
		BX.SocNetLogDestination.initResultNavigation(name, type, {
			crmemails: BX.SocNetLogDestination.obItems[name].crmemails
		});
	}

	if (typeof BX.SocNetLogDestination.obCustomTabs[name] != 'undefined')
	{
		for (i=0; i < BX.SocNetLogDestination.obCustomTabs[name].length; i++)
		{
			if (BX.SocNetLogDestination.obCustomTabs[name][i].id == type)
			{
				var oParams = {};
				oParams[BX.SocNetLogDestination.obCustomTabs[name][i].itemType] = BX.SocNetLogDestination.obItems[name][BX.SocNetLogDestination.obCustomTabs[name][i].itemType];

				BX.SocNetLogDestination.initResultNavigation(name, BX.SocNetLogDestination.obCustomTabs[name][i].id, oParams);
				break;
			}
		}
	}

	BX.SocNetLogDestination.obTabSelected[name] = type;

	if (!!BX.SocNetLogDestination.obUseContainer[name])
	{
		BX.SocNetLogDestination.containerWindow.adjustPosition();
	}
	else
	{
		BX.SocNetLogDestination.popupWindow.adjustPosition();
	}

	return false;
};

BX.SocNetLogDestination.OpenCompanyDepartment = function(name, department, categoryId)
{
	if(!name)
		name = 'lm';

	BX.toggleClass(department, "bx-finder-company-department-opened");

	var nextDiv = BX.findNextSibling(department, { tagName : "div"} );
	if (BX.hasClass(nextDiv, "bx-finder-company-department-children"))
		BX.toggleClass(nextDiv, "bx-finder-company-department-children-opened");

	BX.SocNetLogDestination.getDepartmentRelation(name, categoryId);

	return false;
};

Object.size = function(obj) {
	var size = 0, key;
	for (key in obj) {
		if (obj.hasOwnProperty(key)) size++;
	}
	return size;
};

BX.SocNetLogDestination.selectItem = function(name, element, template, itemId, type, search)
{
	if(!name)
	{
		name = 'lm';
	}

	var ob = {
		id: name
	};
	BX.onCustomEvent(window, 'BX.SocNetLogDestination:onBeforeSelectItemFocus', [ ob ]);
	setTimeout(function() {
		if (
			typeof ob.blockFocus == 'undefined'
			|| !ob.blockFocus
		)
		{
			BX.focus(BX.SocNetLogDestination.obElementSearchInput[name]);
		}
	}, 1);

	if (BX.SocNetLogDestination.obItemsSelected[name][itemId])
	{
		return BX.SocNetLogDestination.unSelectItem(name, element, template, itemId, type, search);
	}

	BX.SocNetLogDestination.obItemsSelected[name][itemId] = type;

	if (
		!BX.type.isArray(BX.SocNetLogDestination.obItemsLast[name][type])
		&& !BX.type.isPlainObject(BX.SocNetLogDestination.obItemsLast[name][type])
	)
	{
		BX.SocNetLogDestination.obItemsLast[name][type] = {};
	}
	BX.SocNetLogDestination.obItemsLast[name][type][itemId] = itemId;

	if (!(element == null || template == null))
	{
		BX.SocNetLogDestination.changeItemClass(element, template, true);
	}

	BX.SocNetLogDestination.runSelectCallback(itemId, type, name, search, 'select');

	if (search === true)
	{
		if (BX.SocNetLogDestination.popupWindow != null)
		{
			BX.SocNetLogDestination.popupWindow.close();
		}

		BX.SocNetLogDestination.abortSearchRequest();

		if (BX.SocNetLogDestination.popupSearchWindow != null)
		{
			BX.SocNetLogDestination.popupSearchWindow.close();
		}
	}
	else
	{
		if (BX.SocNetLogDestination.popupWindow != null)
			BX.SocNetLogDestination.popupWindow.adjustPosition();
		if (BX.SocNetLogDestination.popupSearchWindow != null)
			BX.SocNetLogDestination.popupSearchWindow.adjustPosition();
	}

	var objSize = Object.size(BX.SocNetLogDestination.obItemsLast[name][type]);
	var destLast = null;
	var i = 0;

	if(objSize > 5)
	{
		destLast = {};
		var ii = 0;
		var jj = objSize-5;

		for(i in BX.SocNetLogDestination.obItemsLast[name][type])
		{
			if (
				BX.SocNetLogDestination.obItemsLast[name][type].hasOwnProperty(i)
				&& ii >= jj
			)
			{
				destLast[BX.SocNetLogDestination.obItemsLast[name][type][i]] = BX.SocNetLogDestination.obItemsLast[name][type][i];
			}

			ii++;
		}
	}
	else
	{
		destLast = BX.SocNetLogDestination.obItemsLast[name][type];
	}

	BX.userOptions.save('socialnetwork', 'log_destination', type, JSON.stringify(destLast));

	if (BX.util.in_array(type, ['contacts', 'companies', 'leads', 'deals']) && BX.SocNetLogDestination.obCrmFeed[name])
	{
		var lastCrmItems = [itemId];
		for (i = 0; i < BX.SocNetLogDestination.obItemsLast[name].crm.length && lastCrmItems.length < 20; i++)
		{
			if (BX.SocNetLogDestination.obItemsLast[name].crm[i] != itemId)
			{
				lastCrmItems.push(BX.SocNetLogDestination.obItemsLast[name].crm[i]);
			}
		}

		BX.SocNetLogDestination.obItemsLast[name].crm = lastCrmItems;

		BX.userOptions.save('crm', 'log_destination', 'items', lastCrmItems);
	}

	return false;
};

BX.SocNetLogDestination.unSelectItem = function(name, element, template, itemId, type, search)
{
	if(!name)
	{
		name = 'lm';
	}

	if (!BX.SocNetLogDestination.obItemsSelected[name][itemId])
	{
		return false;
	}
	else
	{
		delete BX.SocNetLogDestination.obItemsSelected[name][itemId];
	}

	BX.SocNetLogDestination.changeItemClass(element, template, false);
	BX.SocNetLogDestination.runUnSelectCallback(itemId, type, name, search);

	if (search === true)
	{
		if (BX.SocNetLogDestination.popupWindow != null)
		{
			BX.SocNetLogDestination.popupWindow.close();
		}

		if (BX.SocNetLogDestination.popupSearchWindow != null)
		{
			BX.SocNetLogDestination.popupSearchWindow.close();
		}
	}
	else
	{
		if (BX.SocNetLogDestination.popupWindow != null)
			BX.SocNetLogDestination.popupWindow.adjustPosition();
		if (BX.SocNetLogDestination.popupSearchWindow != null)
			BX.SocNetLogDestination.popupSearchWindow.adjustPosition();
	}

	return false;
};

BX.SocNetLogDestination.runSelectCallback = function(itemId, type, name, search, state)
{
	if(!name)
	{
		name = 'lm';
	}

	if(!search)
	{
		search = false;
	}

	if(
		BX.SocNetLogDestination.obCallback[name]
		&& BX.SocNetLogDestination.obCallback[name].select
		&& BX.SocNetLogDestination.obItems[name][type]
		&& BX.SocNetLogDestination.obItems[name][type][itemId]
	)
	{
		BX.SocNetLogDestination.obCallback[name].select(
			BX.SocNetLogDestination.obItems[name][type][itemId],
			type,
			search,
			(BX.util.in_array(itemId, BX.SocNetLogDestination.obItemsSelectedUndeleted[name])),
			name,
			state
		);
	}
};

BX.SocNetLogDestination.runUnSelectCallback = function(itemId, type, name, search)
{
	if(!name)
		name = 'lm';

	if(!search)
		search = false;

	delete BX.SocNetLogDestination.obItemsSelected[name][itemId];

	if (
		BX.SocNetLogDestination.obCallback[name]
		&& BX.SocNetLogDestination.obCallback[name].unSelect
		&& BX.SocNetLogDestination.obItems[name][type]
		&& BX.SocNetLogDestination.obItems[name][type][itemId]
	)
	{
		BX.SocNetLogDestination.obCallback[name].unSelect(BX.SocNetLogDestination.obItems[name][type][itemId], type, search, name);
	}
};

/* public function */
BX.SocNetLogDestination.deleteItem = function(itemId, type, name)
{
	if(!name)
		name = 'lm';

	for (var tab in BX.SocNetLogDestination.obResult)
	{
		if (BX.SocNetLogDestination.obResult.hasOwnProperty(tab))
		{
			var elementId = name + '_' + tab + '_' + itemId;
			if (BX(elementId))
			{
				var itemTemplate = null;

				for (var template in BX.SocNetLogDestination.obTemplateClassSelected)
				{
					if (
						BX.SocNetLogDestination.obTemplateClassSelected.hasOwnProperty(template)
						&& BX.hasClass(BX(elementId), BX.SocNetLogDestination.obTemplateClassSelected[template])
					)
					{
						itemTemplate = template;
						break;
					}
				}

				if (!!itemTemplate)
				{
					BX.SocNetLogDestination.changeItemClass(BX(elementId), template, false);
				}
			}
		}
	}

	BX.SocNetLogDestination.runUnSelectCallback(itemId, type, name);
};

BX.SocNetLogDestination.deleteLastItem = function(name)
{
	if(!name)
		name = 'lm';

	var lastId = false;
	for (var itemId in BX.SocNetLogDestination.obItemsSelected[name])
	{
		if (BX.SocNetLogDestination.obItemsSelected[name].hasOwnProperty(itemId))
		{
			lastId = itemId;
		}
	}

	if (lastId)
	{
		var type = BX.SocNetLogDestination.obItemsSelected[name][lastId];
		BX.SocNetLogDestination.runUnSelectCallback(lastId, type, name);
	}
};

BX.SocNetLogDestination.initResultNavigation = function(name, type, obSource)
{
	BX.SocNetLogDestination.obCurrentElement[type] = null;
	BX.SocNetLogDestination.obResult[type] = [];
	BX.SocNetLogDestination.obCursorPosition[type] = {
		group: 0,
		row: 0,
		column: 0
	};

	var itemCount = 0;
	var cntInGroup = null;
	var groupCode = null;
	var itemCode = null;
	var resultGroupIndex = -1;
	var resultRowIndex = 0;
	var resultColumnIndex = 0;
	var bSkipNewGroup = false;
	var item = null;
	var i = 0;

	for (i=0;i<BX.SocNetLogDestination.arDialogGroups[name].length;i++)
	{
		groupCode = BX.SocNetLogDestination.arDialogGroups[name][i].groupCode;
		if (groupCode == 'users')
		{
			if (type == 'email')
			{
				groupCode = 'emails'
			}
			else if (type == 'crmemails')
			{
				groupCode = 'crmemails'
			}
		}
		if (typeof obSource[groupCode] == 'undefined')
		{
			continue;
		}
		if (bSkipNewGroup)
		{
			bSkipNewGroup = false;
		}
		else
		{
			cntInGroup = 0;
		}

		for (itemCode in obSource[groupCode])
		{
			if (
				!obSource[groupCode].hasOwnProperty(itemCode)
				|| !BX.SocNetLogDestination.obItems[name][groupCode][itemCode]
			)
			{
				continue;
			}

			if (cntInGroup == 0)
			{
				if (groupCode == 'groups')
				{
					bSkipNewGroup = true;
				}
				resultGroupIndex++;
				BX.SocNetLogDestination.obResult[type][resultGroupIndex] = [];
				resultRowIndex = 0;
				resultColumnIndex = 0;
			}

			if (resultColumnIndex == 2)
			{
				resultRowIndex++;
				resultColumnIndex = 0;
			}

			if (typeof BX.SocNetLogDestination.obResult[type][resultGroupIndex][resultRowIndex] == 'undefined')
			{
				BX.SocNetLogDestination.obResult[type][resultGroupIndex][resultRowIndex] = [];
			}

			item = {
				id: itemCode,
				type: groupCode
			};

			BX.SocNetLogDestination.obResult[type][resultGroupIndex][resultRowIndex][resultColumnIndex] = item;

			if (itemCount <= 0)
			{
				BX.SocNetLogDestination.obCurrentElement[type] = item;
			}

			resultColumnIndex++;
			cntInGroup++;
			itemCount++;
		}
	}
};

BX.SocNetLogDestination.selectFirstSearchItem = function(name)
{
	if(!name)
		name = 'lm';
	var item = BX.SocNetLogDestination.obSearchFirstElement;
	if (item != null)
	{
		BX.SocNetLogDestination.selectItem(name, null, null, item.id, item.type, true);
		BX.SocNetLogDestination.obSearchFirstElement = null;
	}
};

BX.SocNetLogDestination.selectCurrentSearchItem = function(name)
{
	BX.SocNetLogDestination.selectCurrentItem('search', name);
};

BX.SocNetLogDestination.selectCurrentItem = function(type, name, params)
{
	if (
		BX.SocNetLogDestination.popupSearchWindow == null
		&& BX.SocNetLogDestination.popupWindow == null
		&& BX.SocNetLogDestination.containerWindow == null
	)
	{
		return;
	}

	if(!name)
	{
		name = 'lm';
	}

	if (type == 'search')
	{
		clearTimeout(BX.SocNetLogDestination.searchTimeout);
		BX.SocNetLogDestination.abortSearchRequest();
	}

	var item = BX.SocNetLogDestination.obCurrentElement[type];
	if (item != null)
	{
		var element = BX(name + '_' + type + '_' + item.id);
		var template = BX.SocNetLogDestination.getTemplateByItemClass(element);
		BX.SocNetLogDestination.selectItem(name, (element ? element : null), (template ? template : null), item.id, item.type, (item.type === 'search'));
		if (
			typeof params == 'undefined'
			|| typeof params.closeDialog == 'undefined'
			|| params.closeDialog
		)
		{
			BX.SocNetLogDestination.obCurrentElement[type] = null;
			if (BX.SocNetLogDestination.isOpenDialog())
			{
				BX.SocNetLogDestination.closeDialog();
			}
			BX.SocNetLogDestination.closeSearch();
		}
	}
};

BX.SocNetLogDestination.moveCurrentSearchItem = function(name, direction)
{
	BX.SocNetLogDestination.moveCurrentItem('search', name, direction)
};

BX.SocNetLogDestination.moveCurrentItem = function(type, name, direction)
{
	if (
		BX.SocNetLogDestination.popupSearchWindow == null
		&& BX.SocNetLogDestination.popupWindow == null
		&& BX.SocNetLogDestination.containerWindow == null
	)
	{
		return;
	}

	BX.SocNetLogDestination.bResultMoved[type] = true;

	if (
		type == 'search'
		&& BX.SocNetLogDestination.oXHR
	)
	{
		BX.SocNetLogDestination.abortSearchRequest();
		BX.SocNetLogDestination.hideSearchWaiter(name);
	}

	if (!BX.SocNetLogDestination.obCursorPosition[type])
	{
		BX.SocNetLogDestination.obCursorPosition[type] = {
			group: 0,
			row: 0,
			column: 0
		};
	}

	var bMoved = false;

	switch (direction)
	{
		case 'left':
			if (BX.SocNetLogDestination.focusOnTabs)
			{
				BX.SocNetLogDestination.moveCurrentTab(type, name, direction);
			}
			else if (BX.SocNetLogDestination.obCursorPosition[type].column == 1)
			{
				if (typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row][BX.SocNetLogDestination.obCursorPosition[type].column - 1] != 'undefined')
				{
					BX.SocNetLogDestination.obCursorPosition[type].column--;
					bMoved = true;
				}
			}
			break;
		case 'right':
			if (BX.SocNetLogDestination.focusOnTabs)
			{
				BX.SocNetLogDestination.moveCurrentTab(type, name, direction);
			}
			else if (BX.SocNetLogDestination.obCursorPosition[type].column == 0)
			{
				if (
					typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group] != 'undefined'
					&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row][BX.SocNetLogDestination.obCursorPosition[type].column + 1] != 'undefined'
				)
				{
					BX.SocNetLogDestination.obCursorPosition[type].column++;
					bMoved = true;
				}
			}
			break;
		case 'up':
			if (
				BX.SocNetLogDestination.obCursorPosition[type].row > 0
				&& typeof BX.SocNetLogDestination.obResult[type] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row - 1] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row - 1][BX.SocNetLogDestination.obCursorPosition[type].column] != 'undefined'
			)
			{
				BX.SocNetLogDestination.obCursorPosition[type].row--;
				bMoved = true;
			}
			else if (
				BX.SocNetLogDestination.obCursorPosition[type].row == 0
				&& typeof BX.SocNetLogDestination.obResult[type] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group - 1] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group - 1][BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group - 1].length - 1] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group - 1][BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group - 1].length - 1][0] != 'undefined'
			)
			{
				BX.SocNetLogDestination.obCursorPosition[type].row = BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group - 1].length - 1;
				BX.SocNetLogDestination.obCursorPosition[type].column = 0;
				BX.SocNetLogDestination.obCursorPosition[type].group--;
				bMoved = true;
			}
			else if (
				BX.SocNetLogDestination.obCursorPosition[type].group == 0
				&& BX.SocNetLogDestination.obCursorPosition[type].row == 0
				&& BX.util.in_array(type, BX.SocNetLogDestination.obTabs[name])
			)
			{
//				BX.SocNetLogDestination.focusOnTabs = true;
			}
			break;
		case 'down':
			if (BX.SocNetLogDestination.focusOnTabs)
			{
//				BX.SocNetLogDestination.focusOnTabs = false;
			}
			else if (
				typeof BX.SocNetLogDestination.obResult[type] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row + 1] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row + 1][BX.SocNetLogDestination.obCursorPosition[type].column] != 'undefined'
			)
			{
				BX.SocNetLogDestination.obCursorPosition[type].row++;
				bMoved = true;
			}
			else if (
				typeof BX.SocNetLogDestination.obResult[type] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row + 1] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row + 1][0] != 'undefined'
			)
			{
				BX.SocNetLogDestination.obCursorPosition[type].column = 0;
				BX.SocNetLogDestination.obCursorPosition[type].row++;
				bMoved = true;
			}
			else if (
				typeof BX.SocNetLogDestination.obResult[type] != 'undefined'
				&& BX.SocNetLogDestination.obCursorPosition[type].row == (BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group].length - 1)
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group + 1] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group + 1][0] != 'undefined'
				&& typeof BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group + 1][0][0] != 'undefined'
			)
			{
				BX.SocNetLogDestination.obCursorPosition[type].group++;
				BX.SocNetLogDestination.obCursorPosition[type].row = 0;
				BX.SocNetLogDestination.obCursorPosition[type].column = 0;
				bMoved = true;
			}
			break;
		default:
	}

	if (bMoved)
	{
		var oldId = BX.SocNetLogDestination.obCurrentElement[type].id;
		BX.SocNetLogDestination.obCurrentElement[type] = BX.SocNetLogDestination.obResult[type][BX.SocNetLogDestination.obCursorPosition[type].group][BX.SocNetLogDestination.obCursorPosition[type].row][BX.SocNetLogDestination.obCursorPosition[type].column];

		if (BX(name + '_' + type + '_' + oldId))
		{
			BX.SocNetLogDestination.unhoverItem(BX(name + '_' + type + '_' + oldId));
		}

		var hoveredNode = BX(name + '_' + type + '_' + BX.SocNetLogDestination.obCurrentElement[type].id);
		var containerNode = null;

		if (type == 'search')
		{
			containerNode = BX('bx-lm-box-search-tabs-content');
		}
		else if (type == 'last')
		{
			containerNode = BX('bx-lm-box-last-content');
		}
		else if (type == 'group')
		{
			containerNode = BX('bx-lm-box-group-content');
		}
		else if (type == 'email')
		{
			containerNode = BX('bx-lm-box-email-content');
		}
		else if (type == 'crmemail')
		{
			containerNode = BX('bx-lm-box-crmemail-content');
		}
		else if (BX('dest' + type + 'TabContent_' + name)) // custom tabs
		{
			containerNode = BX('dest' + type + 'TabContent_' + name);
		}

		if (
			hoveredNode
			&& containerNode
		)
		{
			var arPosContainer = BX.pos(containerNode);
			var arPosNode = BX.pos(hoveredNode);

			if (
				arPosNode.bottom > arPosContainer.bottom
				|| arPosNode.top < arPosContainer.top
			)
			{
				containerNode.scrollTop += (
					arPosNode.bottom > arPosContainer.bottom
						? (arPosNode.bottom - arPosContainer.bottom)
						: (arPosNode.top - arPosContainer.top)
				);
			}

			BX.SocNetLogDestination.hoverItem(hoveredNode);
		}
	}
};

BX.SocNetLogDestination.moveCurrentTab = function(type, name, direction)
{
	var obTypeToTab = {
		'last': 'destLastTab',
		'group': 'destGroupTab',
		'department': 'destDepartmentTab'
	};

	var curTabPos = BX.util.array_search(type, BX.SocNetLogDestination.obTabs[name]);

	if (curTabPos >= 0)
	{
		if (direction == 'right')
		{
			curTabPos++;
		}
		else if (direction == 'left')
		{
			curTabPos--;
		}

		if (
			curTabPos <= (BX.SocNetLogDestination.obTabs[name].length - 1)
			&& curTabPos >= 0
			&& typeof BX.SocNetLogDestination.obTabs[name][curTabPos] != 'undefined'
		)
		{
			BX.SocNetLogDestination.SwitchTab(
				name,
				BX(obTypeToTab[BX.SocNetLogDestination.obTabs[name][curTabPos]] + '_' + name),
				BX.SocNetLogDestination.obTabs[name][curTabPos]
			);
		}
	}
};

BX.SocNetLogDestination.getItemHoverClassName = function(node)
{
	if (!node)
	{
		return false;
	}

	if (node.classList.contains('bx-finder-box-item-t1'))
	{
		return 'bx-finder-box-item-t1-hover';
	}
	else if (node.classList.contains('bx-finder-box-item-t2'))
	{
		return 'bx-finder-box-item-t2-hover';
	}
	else if (node.classList.contains('bx-finder-box-item-t3'))
	{
		return 'bx-finder-box-item-t3-hover';
	}
	else if (node.classList.contains('bx-finder-box-item-t4'))
	{
		return 'bx-finder-box-item-t4-hover';
	}
	else if (node.classList.contains('bx-finder-box-item-t5'))
	{
		return 'bx-finder-box-item-t5-hover';
	}
	else if (node.classList.contains('bx-finder-box-item-t6'))
	{
		return 'bx-finder-box-item-t6-hover';
	}
	else if (node.classList.contains('bx-finder-box-item-t7'))
	{
		return 'bx-finder-box-item-t7-hover';
	}

	return  false;
}

BX.SocNetLogDestination.hoverItem = function(node)
{
	var hoverClassName = BX.SocNetLogDestination.getItemHoverClassName(node);

	if (hoverClassName)
	{
		BX.addClass(
			node,
			hoverClassName
		);
	}
};

BX.SocNetLogDestination.unhoverItem = function(node)
{
	var hoverClassName = BX.SocNetLogDestination.getItemHoverClassName(node);

	if (hoverClassName)
	{
		BX.removeClass(
			node,
			hoverClassName
		);
	}
};

BX.SocNetLogDestination.getSelectedCount = function(name)
{
	if(!name)
		name = 'lm';

	var count = 0;
	for (var i in BX.SocNetLogDestination.obItemsSelected[name])
	{
		if (BX.SocNetLogDestination.obItemsSelected[name].hasOwnProperty(i))
		{
			count++;
		}
	}

	return count;
};

BX.SocNetLogDestination.getSelected = function(name)
{
	if(!name)
		name = 'lm';
	return BX.SocNetLogDestination.obItemsSelected[name];
};

BX.SocNetLogDestination.isOpenDialog = function()
{
	return (BX.SocNetLogDestination.popupWindow != null || BX.SocNetLogDestination.containerWindow != null);
};

BX.SocNetLogDestination.isOpenSearch = function()
{
	return (BX.SocNetLogDestination.popupSearchWindow != null || BX.SocNetLogDestination.containerWindow != null);
};

BX.SocNetLogDestination.isOpenContainer = function()
{
	return (BX.SocNetLogDestination.containerWindow != null);
};

BX.SocNetLogDestination.closeDialog = function(silent)
{
	silent = (silent === true);
	if (BX.SocNetLogDestination.popupWindow != null)
	{
		if (silent)
		{
			BX.SocNetLogDestination.popupWindow.destroy();
		}
		else
		{
			BX.SocNetLogDestination.popupWindow.close();
		}
	}
	else if (BX.SocNetLogDestination.containerWindow != null)
	{
		if (silent)
		{
			BX.SocNetLogDestination.containerWindow.destroy();
		}
		else
		{
			BX.SocNetLogDestination.containerWindow.close();
		}
	}

	BX.onCustomEvent(window, 'BX.SocNetLogDestination:onDialogClose', [ this ]);
	return true;
};

BX.SocNetLogDestination.closeSearch = function()
{
	if (BX.SocNetLogDestination.popupSearchWindow != null)
	{
		BX.SocNetLogDestination.popupSearchWindow.close();
	}
	else if (BX.SocNetLogDestination.containerWindow != null)
	{
		BX.SocNetLogDestination.containerWindow.close();
	}

	return true;
};

BX.SocNetLogDestination.createSocNetGroupContent = function(text)
{
	return BX.create('div', {
		children: [
			BX.create('div', {
				text: BX.message('LM_CREATE_SONETGROUP_TITLE').replace("#TITLE#", text)
			})
		]
	});
};

BX.SocNetLogDestination.createSocNetGroupButtons = function(text, name)
{
	return [
		new BX.PopupWindowButton({
			text : BX.message("LM_CREATE_SONETGROUP_BUTTON_CREATE"),
			events : {
				click : function() {
					var groupCode = 'SGN'+ BX.SocNetLogDestination.obNewSocNetGroupCnt[name] + '';
					BX.SocNetLogDestination.obItems[name]['sonetgroups'][groupCode] = {
						id: groupCode,
						entityId: BX.SocNetLogDestination.obNewSocNetGroupCnt[name],
						name: text,
						desc: ''
					};

					var itemsNew = {
						'sonetgroups': {
						}
					};
					itemsNew['sonetgroups'][groupCode] = true;

					if (BX.SocNetLogDestination.popupSearchWindow != null)
					{
						BX.SocNetLogDestination.popupSearchWindowContent.innerHTML = BX.SocNetLogDestination.getItemLastHtml(itemsNew, true, name);
					}
					else
					{
						BX.SocNetLogDestination.openSearch(itemsNew, name);
					}

					BX.SocNetLogDestination.obNewSocNetGroupCnt[name]++;
					BX.SocNetLogDestination.createSocNetGroupWindow.close();
				}
			}
		}),
		new BX.PopupWindowButtonLink({
			text : BX.message("LM_CREATE_SONETGROUP_BUTTON_CANCEL"),
			className : "popup-window-button-link-cancel",
			events : {
				click : function() {
					BX.SocNetLogDestination.createSocNetGroupWindow.close();
				}
			}
		})
	];
};

BX.SocNetLogDestination.showSearchWaiter = function(name)
{
	if (
		typeof BX.SocNetLogDestination.oSearchWaiterEnabled[name] == 'undefined'
		|| !BX.SocNetLogDestination.oSearchWaiterEnabled[name]
	)
	{
		if (BX.SocNetLogDestination.oSearchWaiterContentHeight > 0)
		{
			BX.SocNetLogDestination.oSearchWaiterEnabled[name] = true;
			var startHeight = 0;
			var finishHeight = 40;

			BX.SocNetLogDestination.animateSearchWaiter(startHeight, finishHeight, name);
		}
	}
};

BX.SocNetLogDestination.hideSearchWaiter = function(name)
{
	if (
		typeof BX.SocNetLogDestination.oSearchWaiterEnabled[name] != 'undefined'
		&& BX.SocNetLogDestination.oSearchWaiterEnabled[name]
	)
	{
		BX.SocNetLogDestination.oSearchWaiterEnabled[name] = false;

		var startHeight = 40;
		var finishHeight = 0;
		BX.SocNetLogDestination.animateSearchWaiter(startHeight, finishHeight, name);
	}
};

BX.SocNetLogDestination.animateSearchWaiter = function(startHeight, finishHeight, name)
{
	var contentBlock = (
		!!BX.SocNetLogDestination.obUseContainer[name]
			? BX('bx-lm-box-last-content')
			: BX('bx-lm-box-search-tabs-content')
	);

	if (
		BX('bx-lm-box-search-waiter')
		&& contentBlock
	)
	{
		(new BX.fx({
			time: 0.5,
			step: 0.05,
			type: 'linear',
			start: startHeight,
			finish: finishHeight,
			callback: BX.delegate(function(height)
			{
				if (this)
				{
					this.waiterBlock.style.height = height + 'px';
//					this.contentBlock.style.height = (BX.SocNetLogDestination.oSearchWaiterContentHeight) - height + 'px';
				}
			},
			{
				waiterBlock: BX('bx-lm-box-search-waiter'),
				contentBlock: contentBlock
			}),
			callback_complete: function()
			{
			}
		})).start();
	}
};

BX.SocNetLogDestination.changeItemClass = function(element, template, bSelect)
{
	if (
		element
		&& typeof BX.SocNetLogDestination.obTemplateClassSelected[template] != 'undefined'
	)
	{
		if (bSelect)
		{
			BX.addClass(element, BX.SocNetLogDestination.obTemplateClassSelected[template]);
		}
		else
		{
			BX.removeClass(element, BX.SocNetLogDestination.obTemplateClassSelected[template]);
		}
	}
};

BX.SocNetLogDestination.getTemplateByItemClass = function(element)
{
	if (element)
	{
		for (var key in BX.SocNetLogDestination.obTemplateClass)
		{
			if (
				BX.SocNetLogDestination.obTemplateClass.hasOwnProperty(key)
				&& BX.hasClass(element, BX.SocNetLogDestination.obTemplateClass[key])
			)
			{
				return key;
			}
		}
	}
};

BX.SocNetLogDestination.BXfpSetLinkName = function(ob)
{
	if (
		typeof (ob.tagInputName) != 'undefined'
		&& !!ob.tagInputName
		&& BX(ob.tagInputName)
	)
	{
		BX(ob.tagInputName).innerHTML = (
			BX.SocNetLogDestination.getSelectedCount(ob.formName) <= 0
				? ob.tagLink1
				: ob.tagLink2
		);
	}
};

BX.SocNetLogDestination.BXfpSelectCallback = function(params)
{
	if (!BX.findChild(params.containerInput, { attr : { 'data-id' : params.item.id }}, false, false))
	{
		var type1 = params.type;
		var prefix = 'S';

		if (BX.util.in_array(params.type, ['contacts', 'companies', 'leads', 'deals']))
		{
			type1 = 'crm';
		}

		if (params.type == 'sonetgroups')
		{
			prefix = 'SG';
			if (
				typeof window['arExtranetGroupID'] != 'undefined'
				&& BX.util.in_array(params.item.entityId, window['arExtranetGroupID'])
			)
			{
				type1 = 'extranet';
			}
		}
		else if (params.type == 'groups')
		{
			prefix = 'UA';
			type1 = 'all-users';
		}
		else if (BX.util.in_array(type1, ['users', 'emails']))
		{
			prefix = (BX.SocNetLogDestination.checkEmail(params.item.id) ? 'UE' : 'U');
			if (
				typeof params.item.isCrmEmail != 'undefined'
				&& params.item.isCrmEmail == 'Y'
			)
			{
				type1 = 'crmemail';
			}
			else if (
				typeof params.item.isEmail != 'undefined'
				&& params.item.isEmail == 'Y'
			)
			{
				type1 = 'email';
			}
			else if (
				typeof params.item.isExtranet != 'undefined'
				&& params.item.isExtranet == 'Y'
			)
			{
				type1 = 'extranet';
			}
		}
		else if (params.type == 'crmemails')
		{
			prefix = (params.item.id.match(/(C|CO|L)_\d+/) ? 'UE' : 'U');
			type1 = 'crmemail';
		}
		else if (params.type == 'mailContacts')
		{
			type1 = 'email';
		}
		else if (params.type == 'department')
		{
			prefix = 'DR';
		}
		else if (params.type == 'contacts')
		{
			prefix = 'CRMCONTACT';
		}
		else if (params.type == 'companies')
		{
			prefix = 'CRMCOMPANY';
		}
		else if (params.type == 'leads')
		{
			prefix = 'CRMLEAD';
		}
		else if (params.type == 'deals')
		{
			prefix = 'CRMDEAL';
		}

		var stl = (params.bUndeleted ? ' feed-add-post-destination-undelete' : '');

		var itemName = params.item.name + (
			typeof params.item.showEmail != 'undefined'
			&& params.item.showEmail == 'Y'
			&& typeof params.item.email != 'undefined'
			&& params.item.email
			&& params.item.email.length > 0
				? ' (' + params.item.email + ')'
				: ''
		);

		var arChildren = [
			BX.create("span", {
				props : {
					'className' : "feed-add-post-destination-text"
				},
				html : itemName
			})
		];

		var arHidden = BX.SocNetLogDestination.getHidden(prefix, params.item, (typeof params.varName != 'undefined' ? params.varName : false));
		if (!BX.SocNetLogDestination.obShowSearchInput[params.formName])
		{
			arChildren = BX.util.array_merge(arChildren, arHidden)
		}

		var el = BX.create("span", {
			attrs : {
				'data-id' : params.item.id,
				'data-type' : params.type
			},
			props : {
				className : "feed-add-post-destination feed-add-post-destination-" + type1 + stl
			},
			children: arChildren
		});

		if(!params.bUndeleted)
		{
			el.appendChild(BX.create("span", {
				props : {
					'className' : "feed-add-post-del-but"
				},
				events : {
					'click' : function(e){
						BX.SocNetLogDestination.deleteItem(params.item.id, params.type, params.formName);
						BX.PreventDefault(e)
					},
					'mouseover' : function(){
						BX.addClass(this.parentNode, 'feed-add-post-destination-hover');
					},
					'mouseout' : function(){
						BX.removeClass(this.parentNode, 'feed-add-post-destination-hover');
					}
				}
			}));
		}

		params.containerInput.appendChild(el);
	}

	if (
		!!BX.SocNetLogDestination.obShowSearchInput[params.formName]
		&& !!BX.SocNetLogDestination.obElementSearchInputHidden[params.formName]
	)
	{
		if (!BX.findChild(BX.SocNetLogDestination.obElementSearchInputHidden[params.formName], { attr : { 'data-id' : params.item.id }}, false, false))
		{
			BX.SocNetLogDestination.obElementSearchInputHidden[params.formName].appendChild(BX.create("span", {
				attrs : {
					'data-id' : params.item.id,
					'data-type' : params.type
				},
				children: arHidden
			}));
		}
	}

	params.valueInput.value = '';

	BX.SocNetLogDestination.BXfpSetLinkName({
		formName: params.formName,
		tagInputName: (typeof params.tagInputName != 'undefined' ? params.tagInputName : false),
		tagLink1: params.tagLink1,
		tagLink2: params.tagLink2
	});
};

BX.SocNetLogDestination.BXfpUnSelectCallback = function(item)
{
	var elements = BX.findChildren(BX(this.inputContainerName), {attribute: {'data-id': '' + item.id + ''}}, true);
	if (elements !== null)
	{
		for (var i = 0; i < elements.length; i++)
		{
			if (
				typeof (this.undeleteClassName) == 'undefined'
				|| !BX.hasClass(elements[i], this.undeleteClassName)
			)
			{
				BX.remove(elements[i]);
			}
		}
	}

	BX(this.inputName).value = '';
	BX.SocNetLogDestination.BXfpSetLinkName(this);

	if (
		!!BX.SocNetLogDestination.obShowSearchInput[this.formName]
		&& !!BX.SocNetLogDestination.obElementSearchInputHidden[this.formName]
	)
	{
		elements = BX.findChildren(BX.SocNetLogDestination.obElementSearchInputHidden[this.formName], {attribute: {'data-id': '' + item.id + ''}}, true);
		if (elements !== null)
		{
			for (var j = 0; j < elements.length; j++)
			{
				if (
					typeof (this.undeleteClassName) == 'undefined'
					|| !BX.hasClass(elements[j], this.undeleteClassName)
				)
				{
					BX.remove(elements[j]);
				}
			}
		}
	}
};

BX.SocNetLogDestination.BXfpSearch = function(event)
{
	return BX.SocNetLogDestination.searchHandler(event, {
		formName: this.formName,
		inputId: this.inputName,
		inputNode: (BX.type.isDomNode(this.inputNode) ? this.inputNode : null),
		linkId: this.tagInputName,
		sendAjax: (typeof this.sendAjax != 'undefined' ? this.sendAjax : true),
		multiSelect: true,
		onPasteEvent: (typeof this.onPasteEvent != 'undefined' ? this.onPasteEvent : false)
	});
};

BX.SocNetLogDestination.BXfpSearchBefore = function(event)
{
	return BX.SocNetLogDestination.searchBeforeHandler(event, {
		formName: this.formName,
		inputId: this.inputName,
		inputNode: (BX.type.isDomNode(this.inputNode) ? this.inputNode : null)
	});
};

BX.SocNetLogDestination.BXfpOpenDialogCallback = function()
{
	if (typeof this.inputBoxName != 'undefined')
	{
		BX.style(BX(this.inputBoxName), 'display', 'inline-block');
	}

	if (typeof this.tagInputName != 'undefined')
	{
		BX.style(BX(this.tagInputName), 'display', 'none');
	}

	BX.defer(BX.focus)(BX(this.inputName));
};

BX.SocNetLogDestination.BXfpCloseDialogCallback = function()
{
	if (
		!BX.SocNetLogDestination.isOpenSearch()
		&& BX(this.inputName)
		&& BX(this.inputName).value.length <= 0
	)
	{
		if (typeof this.inputBoxName != 'undefined')
		{
			BX.style(BX(this.inputBoxName), 'display', 'none');
		}

		if (typeof this.tagInputName != 'undefined')
		{
			BX.style(BX(this.tagInputName), 'display', 'inline-block');
		}

		BX.SocNetLogDestination.BXfpDisableBackspace();
	}
};

BX.SocNetLogDestination.BXfpCloseSearchCallback = function()
{
	if (
		!BX.SocNetLogDestination.isOpenSearch()
		&& BX(this.inputName).value.length > 0
	)
	{
		if (typeof this.inputBoxName != 'undefined')
		{
			BX.style(BX(this.inputBoxName), 'display', 'none');
		}

		if (typeof this.tagInputName != 'undefined')
		{
			BX.style(BX(this.tagInputName), 'display', 'inline-block');
		}

		BX(this.inputName).value = '';
		BX.SocNetLogDestination.BXfpDisableBackspace();
	}
};

BX.SocNetLogDestination.BXfpDisableBackspace = function(event)
{
	if (
		BX.SocNetLogDestination.backspaceDisable
		|| BX.SocNetLogDestination.backspaceDisable !== null
	)
	{
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
	}

	BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(event)
	{
		if (
			event.keyCode == 8
			&& !BX.util.in_array(event.target.tagName.toLowerCase(), ['input', 'textarea'])
		)
		{
			BX.PreventDefault(event);
			return false;
		}
	});

	setTimeout(function()
	{
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
		BX.SocNetLogDestination.backspaceDisable = null;
	}, 5000);
};

BX.SocNetLogDestination.BXfpBlurInput = function(event)
{
	if (
		(
			BX.SocNetLogDestination.popupSearchWindow == null
			|| !BX.SocNetLogDestination.popupSearchWindow.isShown()
		)
		&& (
			BX.SocNetLogDestination.popupWindow == null
			|| !BX.SocNetLogDestination.popupWindow.isShown()
		)
		&& (
			BX.SocNetLogDestination.inviteEmailUserWindow == null
			|| !BX.SocNetLogDestination.inviteEmailUserWindow.isShown()
		)
	)
	{
		var inputNode = BX.proxy_context;

		if (
			inputNode
			&& inputNode.tagName.toUpperCase() == 'INPUT'
			&& BX.type.isNotEmptyString(inputNode.value)
		)
		{
			inputNode.value = '';
		}

		var inputBoxNode = (
			BX.type.isDomNode(this.inputBoxName)
				? this.inputBoxName
				: (
					BX.type.isNotEmptyString(this.inputBoxName)
						? BX(this.inputBoxName)
						: null
				)
		);

		if (inputBoxNode)
		{
			BX.style(inputBoxNode, 'display', 'none');
		}

		var tagInputNode = (
			BX.type.isDomNode(this.tagInputName)
				? this.tagInputName
				: (
					BX.type.isNotEmptyString(this.tagInputName)
						? BX(this.tagInputName)
						: null
				)
		);

		if (tagInputNode)
		{
			BX.style(tagInputNode, 'display', 'inline-block');
		}
	}
};


BX.SocNetLogDestination.searchHandler = function(event, params)
{
	var onPasteEvent = (
		typeof params.onPasteEvent != 'undefined'
		&& params.onPasteEvent
	);

	if (
		!this.searchStarted
		&& !onPasteEvent
	)
	{
		return false;
	}

	this.searchStarted = false;

	if (
		!onPasteEvent
		&& (
			event.keyCode == 16
			|| event.keyCode == 17 // ctrl
			|| event.keyCode == 18
			|| event.keyCode == 20
			|| event.keyCode == 244
			|| event.keyCode == 224 // cmd
			|| event.keyCode == 91 // left cmd
			|| event.keyCode == 93 // right cmd
			|| event.keyCode == 9 // tab
		)
	)
	{
		return false;
	}

	var type = null;
	if (BX.SocNetLogDestination.popupSearchWindow != null)
	{
		type = 'search';
	}
	else if (
		typeof event.keyCode != 'undefined'
		&& BX.util.in_array(event.keyCode, [37,38,39,40,13])
		&& BX.util.in_array(BX.SocNetLogDestination.obTabSelected[params.formName], ['department'])
	)
	{
		return true;
	}
	else
	{
		type = BX.SocNetLogDestination.obTabSelected[params.formName];
	}

	if (
		typeof event.keyCode != 'undefined'
		&& type
	)
	{
		if (event.keyCode == 37)
		{
			BX.SocNetLogDestination.moveCurrentItem(type, params.formName, 'left');
			BX.PreventDefault(event);
			return false;
		}
		else if (event.keyCode == 38)
		{
			BX.SocNetLogDestination.moveCurrentItem(type, params.formName, 'up');
			BX.PreventDefault(event);
			return false;
		}
		else if (event.keyCode == 39)
		{
			BX.SocNetLogDestination.moveCurrentItem(type, params.formName, 'right');
			BX.PreventDefault(event);
			return false;
		}
		else if (event.keyCode == 40)
		{
			BX.SocNetLogDestination.moveCurrentItem(type, params.formName, 'down');
			BX.PreventDefault(event);
			return false;
		}
		else if (event.keyCode == 13)
		{
			BX.SocNetLogDestination.selectCurrentItem(type, params.formName);
			return BX.PreventDefault(event);
		}
		else if (
			typeof params.multiSelect != 'undefined'
			&& params.multiSelect
			&& event.keyCode == 32 // space
			&& type != 'search'
		)
		{
			BX.SocNetLogDestination.selectCurrentItem(type, params.formName, {
				closeDialog: false
			});
			return true;
		}
	}

	var inputNode = (BX.type.isDomNode(params.inputNode) ? BX(params.inputNode) : BX(params.inputId));
	if (!inputNode)
	{
		return false;
	}

	var searchText = '';
	if (event.keyCode == 27)
	{
		if (
			BX.SocNetLogDestination.inviteEmailUserWindow == null
			|| !BX.SocNetLogDestination.inviteEmailUserWindow.isShown()
		)
		{
			inputNode.value = '';
			BX.style(BX(params.linkId), 'display', 'inline');

			if (
				typeof params.formName != 'undefined'
				&& !BX.SocNetLogDestination.obShowSearchInput[params.formName]
			)
			{
				BX.PreventDefault(event);
			}
		}
		else
		{
			BX.SocNetLogDestination.inviteEmailUserWindow.close();
			return false;
		}
	}
	else
	{
		searchText = inputNode.value;

		BX.SocNetLogDestination.search(
			searchText,
			params.sendAjax,
			params.formName
		);
	}

	if (
		!BX.SocNetLogDestination.isOpenDialog()
		&& searchText.length <= 0
	)
	{
		BX.SocNetLogDestination.openDialog(params.formName);
	}
	else
	{
		if (
			BX.SocNetLogDestination.sendEvent
			&& BX.SocNetLogDestination.isOpenDialog()
			&& !BX.SocNetLogDestination.isOpenContainer()
		)
		{
			BX.SocNetLogDestination.closeDialog();
		}
	}

	if (event.keyCode == 8)
	{
		BX.SocNetLogDestination.sendEvent = true;
	}

	return true;
};

BX.SocNetLogDestination.searchBeforeHandler = function(event, params)
{
	var inputNode = (BX.type.isDomNode(params.inputNode) ? params.inputNode : BX(params.inputId));
	if (!inputNode)
	{
		return false;
	}

	if (
		event.keyCode == 8
		&& inputNode.value.length <= 0
	)
	{
		BX.SocNetLogDestination.sendEvent = false;
		BX.SocNetLogDestination.deleteLastItem(params.formName);
	}
	else if (event.keyCode == 13)
	{
		this.searchStarted = true;
		return BX.PreventDefault(event);
	}
	else if (
		event.keyCode == 17 // ctrl
		|| event.keyCode == 224 // cmd
		|| event.keyCode == 91 // left cmd
		|| event.keyCode == 93 // right cmd
	)
	{
		return BX.PreventDefault(event);
	}

	this.searchStarted = true;

	return true;
};

BX.SocNetLogDestination.loadAll = function(params)
{
	if (
		typeof params != 'undefined'
		&& typeof params.name != 'undefined'
		&& typeof params.callback == 'function'
		&& (typeof params.entity == 'undefined' || params.entity == 'users')
	)
	{
		BX.ajax({
			url: '/bitrix/components/bitrix/main.post.form/post.ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {
				'LD_ALL' : 'Y',
				'sessid': BX.bitrix_sessid()
			},
			onsuccess: function(data)
			{
				if (typeof data.USERS != 'undefined')
				{
					BX.onCustomEvent('onFinderAjaxLoadAll', [ data.USERS, BX.SocNetLogDestination, 'users' ]);
				}
				params.callback();
			},
			onfailure: function(data)
			{
			}
		});
	}
};

BX.SocNetLogDestination.compareDestinations = function(a, b)
{
	if (
		BX.util.in_array(a.value, BX.SocNetLogDestination.tmpSearchResult.client)
		&& !BX.util.in_array(b.value, BX.SocNetLogDestination.tmpSearchResult.client)
	)
	{
		return -1;
	}
	else if (
		typeof a.isNetwork == 'undefined'
		&& typeof b.isNetwork != 'undefined'
	)
	{
		return -1;
	}
	else if (
		typeof a.isNetwork != 'undefined'
		&& typeof b.isNetwork == 'undefined'
	)
	{
		return 1;
	}
	else if (
		typeof a.sort == 'undefined'
		&& typeof b.sort == 'undefined'
	)
	{
		return 0;
	}
	else if (
		typeof a.sort != 'undefined'
		&& typeof b.sort == 'undefined'
	)
	{
		return -1;
	}
	else if (
		typeof a.sort == 'undefined'
		&& typeof b.sort != 'undefined'
	)
	{
		return 1;
	}
	else
	{
		if (
			typeof a.sort.Y != 'undefined'
			&& typeof b.sort.Y == 'undefined'
		)
		{
			return -1;
		}
		else if (
			typeof a.sort.Y == 'undefined'
			&& typeof b.sort.Y != 'undefined'
		)
		{
			return 1;
		}
		else if (
			typeof a.sort.Y != 'undefined'
			&& typeof b.sort.Y != 'undefined'
		)
		{
			if (parseInt(a.sort.Y) > parseInt(b.sort.Y))
			{
				return -1;
			}
			else if (parseInt(a.sort.Y) < parseInt(b.sort.Y))
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			if (parseInt(a.sort.N) > parseInt(b.sort.N))
			{
				return -1;
			}
			else if (parseInt(a.sort.N) < parseInt(b.sort.N))
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
	}
};

BX.SocNetLogDestination.checkEmail = function(searchString)
{
	var re = /^([^<]+)\s<([^>]+)>$/igm;
	var matches = re.exec(searchString);
	var userName = '';
	var userLastName = '';

	if (
		matches != null
		&& matches.length == 3
	)
	{
		userName = matches[1];
		var parts = userName.split(/[\s]+/);
		userLastName = parts.pop();
		userName = parts.join(' ');

		searchString = matches[2].trim();
	}

	re = /^[=_0-9a-z+~'!\$&*^`|\#%/?{}-]+(\.[=_0-9a-z+~'!\$&*^`|\#%/?{}-]+)*@(([-0-9a-z_]+\.)+)([a-z0-9-]{2,20})$/igm;

	if (
		searchString.length >= 6
		&& re.test(searchString)
	)
	{
		return {
			name: userName,
			lastName: userLastName,
			email: searchString.toLowerCase()
		};
	}
	else
	{
		return false;
	}
};

BX.SocNetLogDestination.openInviteEmailUserDialog = function(obUserEmail, name, bCrm)
{
	BX.SocNetLogDestination.inviteEmailCurrentName = name;

	if (BX.SocNetLogDestination.inviteEmailUserWindow === null)
	{
		BX.SocNetLogDestination.inviteEmailUserWindow = new BX.PopupWindow({
			id: "invite-email-email-user-popup",
			bindElement: BX.SocNetLogDestination.obElementSearchInput[name],
			offsetTop : 1,
			content : BX.SocNetLogDestination.inviteEmailUserContent(obUserEmail, name, bCrm),
			zIndex : 1250,
			lightShadow : true,
			autoHide : true,
			closeByEsc: true,
			angle: {
				position: "bottom",
				offset : 20
			},
			events: {
				onPopupClose : function()
				{
					if (
						BX.SocNetLogDestination.inviteEmailUserWindow != null
						|| !BX.SocNetLogDestination.inviteEmailUserWindow.isShown()
					)
					{
						var params = {
							name: (BX.SocNetLogDestination.inviteEmailUserWindowSubmitted ? BX('invite_email_user_name').value : ''),
							lastName: (BX.SocNetLogDestination.inviteEmailUserWindowSubmitted ? BX('invite_email_user_last_name').value : ''),
							email: BX('invite_email_user_email').value,
							createCrmContact: (BX('invite_email_user_create_crm_contact') && BX('invite_email_user_create_crm_contact').checked)
						};

						BX.SocNetLogDestination.inviteEmailAddUser(BX.SocNetLogDestination.inviteEmailCurrentName, params);
					}
					BX.SocNetLogDestination.inviteEmailUserWindowSubmitted = false;

					if (
						BX.SocNetLogDestination.sendEvent
						&& BX.SocNetLogDestination.obCallback[BX.SocNetLogDestination.inviteEmailCurrentName]
						&& BX.SocNetLogDestination.obCallback[BX.SocNetLogDestination.inviteEmailCurrentName].closeEmailAdd
					)
					{
						BX.SocNetLogDestination.obCallback[BX.SocNetLogDestination.inviteEmailCurrentName].closeEmailAdd(BX.SocNetLogDestination.inviteEmailCurrentName);
					}
				},
				onPopupShow: function()
				{
					BX.defer(BX.focus)(BX('invite_email_user_name'));

					if (
						BX.SocNetLogDestination.sendEvent
						&& BX.SocNetLogDestination.obCallback[BX.SocNetLogDestination.inviteEmailCurrentName]
						&& BX.SocNetLogDestination.obCallback[BX.SocNetLogDestination.inviteEmailCurrentName].openEmailAdd
					)
					{
						BX.SocNetLogDestination.obCallback[BX.SocNetLogDestination.inviteEmailCurrentName].openEmailAdd(BX.SocNetLogDestination.inviteEmailCurrentName);
					}
				}
			}
		});
	}
	else
	{
		BX.SocNetLogDestination.inviteEmailUserWindow.setContent(
			BX.SocNetLogDestination.inviteEmailUserContent(obUserEmail, BX.SocNetLogDestination.inviteEmailCurrentName, bCrm)
		);
		BX.SocNetLogDestination.inviteEmailUserWindow.setBindElement(BX.SocNetLogDestination.obElementSearchInput[BX.SocNetLogDestination.inviteEmailCurrentName]);
	}

	if (!BX.SocNetLogDestination.inviteEmailUserWindow.isShown())
	{
		BX.SocNetLogDestination.inviteEmailUserWindow.show();
	}
};

BX.SocNetLogDestination.inviteEmailAddUser = function(name, params)
{
	var bShowEmail = false;
	var userEmail = params.email;
	var userName = BX.util.htmlspecialchars(params.name) + (params.name.length > 0 ? ' ' : '') + BX.util.htmlspecialchars(params.lastName);

	if (userName.length <= 0)
	{
		userName = userEmail;
	}
	else
	{
		bShowEmail = true;
	}

	if (typeof BX.SocNetLogDestination.obItems[name]['users'] == 'undefined')
	{
		BX.SocNetLogDestination.obItems[name]['users'] = [];
	}

	BX.SocNetLogDestination.obItems[name]['users'][userEmail] = {
		name: userName,
		email: userEmail,
		id: userEmail,
		isEmail: 'Y',
		isCrmEmail: (typeof params.createCrmContact != 'undefined' && !!params.createCrmContact ? 'Y' : 'N'),
		showEmail: (bShowEmail ? 'Y' : 'N'),
		params: params
	};

	// add to form

	BX.SocNetLogDestination.runSelectCallback(userEmail, 'users', name, false, 'select');
};

BX.SocNetLogDestination.inviteEmailUserContent = function(obUserEmail, name, bCrm)
{
	bCrm = !!bCrm;

	return BX.create('DIV', {
		props: {
			className: 'bx-feed-email-popup'
		},
		children: [
			BX.create('DIV', {
				props: {
					className: 'bx-feed-email-title'
				},
				text: BX.message('LM_INVITE_EMAIL_USER_TITLE')
			}),
			BX.create('FORM', {
				style: {
					padding: 0,
					margin: 0
				},
				events : {
					submit : function(e) {
						BX.SocNetLogDestination.inviteEmailUserSubmitForm(name);
						BX.PreventDefault(e);
					}
				},
				children: [
					BX.create('DIV', {
						children: [
							BX.create('INPUT', {
								attrs: {
									id: 'invite_email_user_email',
									type: "hidden",
									value: obUserEmail.email
								}
							}),
							BX.create('INPUT', {
								attrs: {
									id: 'invite_email_user_name',
									type: "text",
									placeholder: BX.message('LM_INVITE_EMAIL_USER_PLACEHOLDER_NAME'),
									value: obUserEmail.name
								},
								props: {
									className: 'bx-feed-email-input'
								}
							}),
							BX.create('INPUT', {
								attrs: {
									id: 'invite_email_user_last_name',
									type: "text",
									placeholder: BX.message('LM_INVITE_EMAIL_USER_PLACEHOLDER_LAST_NAME'),
									value: obUserEmail.lastName
								},
								props: {
									className: 'bx-feed-email-input'
								},
								events : {
									keyup : function(e) {
										if (
											BX('invite_email_user_name').value.length > 0
											|| BX('invite_email_user_last_name').value.length > 0
										)
										{
											BX.removeClass(BX('invite_email_user_button'), 'webform-button-disable');
										}
										else
										{
											BX.addClass(BX('invite_email_user_button'), 'webform-button-disable');
										}
										BX.PreventDefault(e);
									}
								}
							}),
							BX.create('SPAN', {
								attrs: {
									id: 'invite_email_user_button'
								},
								props: {
									className: 'webform-small-button webform-small-button-blue webform-button-disable'
								},
								text: BX.message("LM_INVITE_EMAIL_USER_BUTTON_OK"),
								style: {
									cursor: 'pointer'
								},
								events : {
									click : function() {
										BX.SocNetLogDestination.inviteEmailUserSubmitForm(name);
									}
								}
							}),
							BX.create('INPUT', {
								style: {
									display: 'none'
								},
								attrs: {
									type: 'submit'
								}
							})
						]
					}),
					(
						bCrm
						? BX.create('DIV', {
							props: {
								className: 'bx-feed-email-crm-contact'
							},
							children: [
								BX.create('INPUT', {
									attrs: {
										className: 'bx-feed-email-checkbox',
										type: 'checkbox',
										id: 'invite_email_user_create_crm_contact',
										value: 'Y'
									}
								}),
								BX.create('LABEL', {
									attrs: {
										for: 'invite_email_user_create_crm_contact'
									},
									html: BX.message('LM_INVITE_EMAIL_CRM_CREATE_CONTACT')
								})
							]
						})
						: null
					)
				]
			})
		]
	});
};

BX.SocNetLogDestination.inviteEmailUserSubmitForm = function(name)
{
	BX.SocNetLogDestination.inviteEmailUserWindowSubmitted = true;
	BX.SocNetLogDestination.inviteEmailUserWindow.close();
};

BX.SocNetLogDestination.buildDepartmentRelation = function(department)
{
	var relation = {}, p, iid;
	for(iid in department)
	{
		if (department.hasOwnProperty(iid))
		{
			p = department[iid]['parent'];
			if (!relation[p])
				relation[p] = [];
			relation[p][relation[p].length] = iid;
		}
	}

	function makeDepartmentTree(id, relation)
	{
		var arRelations = {}, relId, arItems;
		if (relation[id])
		{
			for (var x in relation[id])
			{
				if (relation[id].hasOwnProperty(x))
				{
					relId = relation[id][x];
					arItems = [];
					if (relation[relId] && relation[relId].length > 0)
						arItems = makeDepartmentTree(relId, relation);

					arRelations[relId] = {
						id: relId,
						type: 'category',
						items: arItems
					};
				}
			}
		}

		return arRelations;
	}

	return makeDepartmentTree('DR0', relation);
};

BX.SocNetLogDestination.abortSearchRequest = function()
{
	if (BX.SocNetLogDestination.oXHR)
	{
		BX.SocNetLogDestination.oXHR.abort();
	}

	if (BX.SocNetLogDestination.searchTimeout)
	{
		clearTimeout(BX.SocNetLogDestination.searchTimeout);
	}
};

BX.SocNetLogDestination.onTabsAdd = function(name, oTab)
{
	if (!BX.util.in_array(oTab.id, BX.SocNetLogDestination.obTabs[name]))
	{
		BX.SocNetLogDestination.obTabs[name].push(oTab.id);
		if (typeof BX.SocNetLogDestination.obCustomTabs[name] == 'undefined')
		{
			BX.SocNetLogDestination.obCustomTabs[name] = [];
		}
		BX.SocNetLogDestination.obCustomTabs[name].push(oTab);

		if (oTab.dialogGroup != 'undefined')
		{
			var bFound = false;
			for (var j=0; j < BX.SocNetLogDestination.arDialogGroups[name].length; j++)
			{
				if (BX.SocNetLogDestination.arDialogGroups[name][j].groupCode == oTab.dialogGroup.groupCode)
				{
					bFound = true;
					break;
				}
			}

			if (!bFound)
			{
				BX.SocNetLogDestination.arDialogGroups[name].push({
					bCrm: (
						typeof oTab.dialogGroup.bCrm != 'undefined'
							? !!oTab.dialogGroup.bCrm
							: false
					),
					groupCode: oTab.dialogGroup.groupCode,
					className: (
						typeof oTab.dialogGroup.className != 'undefined'
							? oTab.dialogGroup.className
							: ''
					),
					groupboxClassName: (
						typeof oTab.dialogGroup.groupboxClassName != 'undefined'
							? oTab.dialogGroup.groupboxClassName
							: ''
					),
					title: oTab.dialogGroup.title
				});
			}
		}
	}
};

})();