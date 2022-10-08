import 'ui.design-tokens';
import 'ui.fonts.opensans';
import "./css/syncinterface.css";
import SyncPanel from "./syncpanel";
import SyncPanelUnit from "./syncpanelunit";
import AuxiliarySyncPanel from "./auxiliarysyncpanel";
import GridUnit from "./gridunit"
import ConnectionControls from "./controls/connectioncontrols";
import MobileSyncBanner from "./controls/mobilesyncbanner";
import CaldavTemplate from "./itemstemplate/caldavtemplate";
import ExchangeTemplate from "./itemstemplate/exchangetemplate";
import GoogleTemplate from "./itemstemplate/googletemplate";
import IcloudTemplate from "./itemstemplate/icloudtemplate";
import Office365template from "./itemstemplate/office365template";
import MacTemplate from "./itemstemplate/mactemplate";
import OutlookTemplate from "./itemstemplate/outlooktemplate";
import YandexTemplate from "./itemstemplate/yandextemplate";
import AndroidTemplate from "./itemstemplate/androidtemplate";
import IphoneTemplate from "./itemstemplate/iphonetemplate";
import IcalSyncPopup from "./controls/icalsyncpopup";
import AfterSyncTour from "./controls/aftersynctour";
import GoogleSyncWizard from "./syncwizard/googlesyncwizard";
import IcloudAuthDialog from "./controls/icloudauthdialog";

export {
	SyncPanel,
	SyncPanelUnit,
	AuxiliarySyncPanel,
	GridUnit,
	ConnectionControls,
	MobileSyncBanner,
	YandexTemplate,
	CaldavTemplate,
	MacTemplate,
	ExchangeTemplate,
	GoogleTemplate,
	IcloudTemplate,
	OutlookTemplate,
	IphoneTemplate,
	AndroidTemplate,
	IcalSyncPopup,
	AfterSyncTour,
	GoogleSyncWizard,
	Office365template,
	IcloudAuthDialog
};