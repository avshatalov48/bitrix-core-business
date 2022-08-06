import {Common} from './common.js';
import {Waiter} from './waiter.js';
import {SonetGroupMenu} from './sonetgroupmenu.js';
import {WorkgroupWidget} from './workgroupwidget.js';
import {RecallJoinRequest} from './recalljoinrequest.js';

export {
	Common,
	Waiter,
	SonetGroupMenu,
	WorkgroupWidget,
	RecallJoinRequest,
}

/** @deprecated use BX.Socialnetwork.UI.Common */
BX.SocialnetworkUICommon = Common;

/** @deprecated use BX.Socialnetwork.UI.Waiter */
BX.SocialnetworkUICommon.Waiter = Waiter;

/** @deprecated use BX.Socialnetwork.UI.GroupMenu */
BX.SocialnetworkUICommon.SonetGroupMenu = SonetGroupMenu;

/** @deprecated use BX.Socialnetwork.UI.WorkgroupWidget */
BX.Socialnetwork.UIWorkgroupWidget = WorkgroupWidget;
