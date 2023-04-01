this.BX=this.BX||{};this.BX.Messenger=this.BX.Messenger||{};(function(e){"use strict";
/**
	 * Bitrix Messenger
	 * Date constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var i=Object.freeze({groupTitle:"groupTitle",message:"message",recentTitle:"recentTitle",recentLinesTitle:"recentLinesTitle",readedTitle:"readedTitle",default:"default",vacationTitle:"vacationTitle"});
/**
	 * Bitrix Messenger
	 * Device constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var t=Object.freeze({mobile:"mobile",desktop:"desktop"});var a=Object.freeze({horizontal:"horizontal",portrait:"portrait"});
/**
	 * Bitrix Messenger
	 * Common constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var s=Object.freeze({none:"none",add:"delete",update:"update",delete:"delete",set:"set",setAfter:"after",setBefore:"before"});var o=Object.freeze({dialogues:50,messages:100});
/**
	 * Bitrix Messenger
	 * Device constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var r=Object.freeze({imMessageAdd:"im.message.add",imMessageUpdate:"im.message.update",imMessageDelete:"im.message.delete",imMessageLike:"im.message.like",imMessageCommand:"im.message.command",imMessageShare:"im.message.share",imChatGet:"im.chat.get",imChatLeave:"im.chat.leave",imChatMute:"im.chat.mute",imChatParentJoin:"im.chat.parent.join",imDialogGet:"im.dialog.get",imDialogMessagesGet:"im.dialog.messages.get",imDialogRead:"im.dialog.read",imDialogUnread:"im.dialog.unread",imDialogWriting:"im.dialog.writing",imUserGet:"im.user.get",imUserListGet:"im.user.list.get",imDiskFolderGet:"im.disk.folder.get",imDiskFileUpload:"disk.folder.uploadfile",imDiskFileCommit:"im.disk.file.commit",mobileBrowserConstGet:"mobile.browser.const.get",imRecentGet:"im.recent.get",imRecentList:"im.recent.list",imCallGetCallLimits:"im.call.getCallLimits",imNotifyGet:"im.notify.get",imNotifySchemaGet:"im.notify.schema.get"});var l=Object.freeze({imChatGet:"im.chat.get",imMessageAdd:"im.message.add",imDialogRead:"im.dialog.read",imDialogMessagesGet:"im.dialog.messages.get",imDialogMessagesGetInit:"im.dialog.messages.get.init",imDialogMessagesGetUnread:"im.dialog.messages.get.unread",imDiskFolderGet:"im.disk.folder.get",imDiskFileUpload:"disk.folder.uploadfile",imDiskFileCommit:"im.disk.file.commit",imUserGet:"im.user.get",imUserListGet:"im.user.list.get",mobileBrowserConstGet:"mobile.browser.const.get",imRecentGet:"im.recent.get",imRecentList:"im.recent.list",imCallGetCallLimits:"im.call.getCallLimits",imNotifyGet:"im.notify.get",imNotifySchemaGet:"im.notify.schema.get"});
/**
	 * Bitrix Messenger
	 * Event names constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var n=Object.freeze({dialog:{open:"IM.Dialog:open",newMessage:"EventType.dialog.newMessage",scrollOnStart:"IM.Dialog:scrollOnStart",scrollToBottom:"IM.Dialog:scrollToBottom",readVisibleMessages:"IM.Dialog.readVisibleMessages",requestUnread:"IM.Dialog.requestUnread",readMessage:"IM.Dialog:readMessage",quoteMessage:"IM.Dialog:quoteMessage",clickOnCommand:"IM.Dialog:clickOnCommand",clickOnMention:"IM.Dialog:clickOnMention",clickOnUserName:"IM.Dialog:clickOnUserName",clickOnMessageMenu:"IM.Dialog:clickOnMessageMenu",clickOnMessageRetry:"IM.Dialog:clickOnMessageRetry",doubleClickOnMessage:"IM.Dialog:doubleClickOnMessage",clickOnUploadCancel:"IM.Dialog:clickOnUploadCancel",clickOnReadList:"IM.Dialog:clickOnReadList",setMessageReaction:"IM.Dialog:setMessageReaction",openMessageReactionList:"IM.Dialog:openMessageReactionList",clickOnKeyboardButton:"IM.Dialog:clickOnKeyboardButton",clickOnChatTeaser:"IM.Dialog:clickOnChatTeaser",clickOnDialog:"IM.Dialog:clickOnDialog",quotePanelClose:"IM.Dialog:quotePanelClose",beforeMobileKeyboard:"IM.Dialog:beforeMobileKeyboard",messagesSet:"IM.Dialog:messagesSet"},textarea:{focus:"IM.Textarea:focus",setFocus:"IM.Textarea:setFocus",blur:"IM.Textarea:blur",setBlur:"IM.Textarea:setBlur",keyUp:"IM.Textarea:keyUp",edit:"IM.Textarea:edit",insertText:"IM.Textarea:insertText",sendMessage:"IM.Textarea:sendMessage",fileSelected:"IM.Textarea:fileSelected",startWriting:"IM.Textarea:startWriting",stopWriting:"IM.Textarea:stopWriting",appButtonClick:"IM.Textarea:appButtonClick"},uploader:{addMessageWithFile:"IM.Uploader:addMessageWithFile"},conference:{setPasswordFocus:"IM.Conference:setPasswordFocus",hideSmiles:"IM.Conference:hideSmiles",requestPermissions:"IM.Conference:requestPermissions",waitForStart:"IM.Conference:waitForStart",userRenameFocus:"IM.Conference:userRenameFocus",userRenameBlur:"IM.Conference:userRenameBlur"},notification:{updateState:"IM.Notifications:restoreConnection"},mobile:{textarea:{setText:"IM.Mobile.Textarea:setText",setFocus:"IM.Mobile.Textarea:setFocus"},openUserList:"IM.Mobile:openUserList"}});
/**
	 * Bitrix Messenger
	 * Event names constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var c=Object.freeze({private:"private",chat:"chat",open:"open",call:"call",crm:"crm"});var m=Object.freeze({lead:"lead",company:"company",contact:"contact",deal:"deal",none:"none"});var d=Object.freeze({listBody:"bx-im-dialog-list",listItem:"bx-im-dialog-list-item-reference",listItemName:"bx-im-dialog-list-item-name-reference",listItemBody:"bx-im-dialog-list-item-content-reference",listUnreadLoader:"bx-im-dialog-list-unread-loader-reference"});var g=Object.freeze({message:"message",delimiter:"delimiter",group:"group",historyLoader:"historyLoader",unreadLoader:"unreadLoader",button:"button",placeholder:"placeholder"});var M=Object.freeze({loading:"loading",empty:"empty",show:"show"});
/**
	 * Bitrix Messenger
	 * File constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var u=Object.freeze({upload:"upload",wait:"wait",done:"done",error:"error"});var p=Object.freeze({image:"image",video:"video",audio:"audio",file:"file"});
/**
	 * Bitrix Messenger
	 * Message constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var f=Object.freeze({self:"self",opponent:"opponent",system:"system"});
/**
	 * Bitrix Messenger
	 * Conference constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var C=Object.freeze({view:"view",edit:"edit",create:"create"});var b=Object.freeze({preparation:"preparation",call:"call"});var h=Object.freeze({userLimitReached:"userLimitReached",detectIntranetUser:"detectIntranetUser",bitrix24only:"bitrix24only",kickedFromCall:"kickedFromCall",unsupportedBrowser:"unsupportedBrowser",missingMicrophone:"missingMicrophone",unsafeConnection:"unsafeConnection",wrongAlias:"wrongAlias",notStarted:"notStarted",finished:"finished",userLeftCall:"userLeftCall",noSignalFromCamera:"noSignalFromCamera"});var I=Object.freeze({hidden:"hidden",chat:"chat",users:"users",split:"split"});var D=Object.freeze({Idle:"Idle",Busy:"Busy",Calling:"Calling",Unavailable:"Unavailable",Declined:"Declined",Ready:"Ready",Connecting:"Connecting",Connected:"Connected",Failed:"Failed"});
/**
	 * Bitrix Messenger
	 * Recent list constants
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */var O={chat:"chat",open:"open",user:"user",notification:"notification"};var T={item:"item",placeholder:"placeholder"};var k={general:"general",pinned:"pinned"};var v={received:"received",delivered:"delivered",error:"error"};var y=Object.freeze({confirm:1,simple:3,placeholder:5});e.DateFormat=i;e.DeviceType=t;e.DeviceOrientation=a;e.MutationType=s;e.StorageLimit=o;e.RestMethod=r;e.RestMethodHandler=l;e.EventType=n;e.DialogType=c;e.DialogCrmType=m;e.DialogReferenceClassName=d;e.DialogTemplateType=g;e.DialogState=M;e.FileStatus=u;e.FileType=p;e.MessageType=f;e.ConferenceFieldState=C;e.ConferenceStateType=b;e.ConferenceErrorCode=h;e.ConferenceRightPanelMode=I;e.ConferenceUserState=D;e.ChatTypes=O;e.TemplateTypes=T;e.RecentSection=k;e.MessageStatus=v;e.NotificationTypesCodes=y})(this.BX.Messenger.Const=this.BX.Messenger.Const||{});
//# sourceMappingURL=registry.bundle.map.js