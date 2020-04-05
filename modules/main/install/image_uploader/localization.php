<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LANGUAGE_ID."/localization.php")));	

if(!file_exists($file))
	$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LangSubst(LANGUAGE_ID)."/localization.php")));	

__IncludeLang($file);

?>
<script>
language_resources = {
	addParams: IULocalization.addParams,
		
	Language: "<?=LANGUAGE_ID?>", 
		
		
	ImageUploaderWriter: {
		instructionsCommon: "<?=CUtil::JSEscape(GetMessage("instructionsCommon"))?>", 
		instructionsNotWinXPSP2: "<?=CUtil::JSEscape(GetMessage("instructionsNotWinXPSP2"))?>", 
		instructionsWinXPSP2: "<?=CUtil::JSEscape(GetMessage("instructionsWinXPSP2"))?>", 
		instructionsVista: "<?=CUtil::JSEscape(GetMessage("instructionsVista"))?>", 
		instructionsCommon2: "<?=CUtil::JSEscape(GetMessage("instructionsCommon2"))?>" 
	},
		
	ImageUploader: {
		AddFolderDialogButtonCancelText: "<?=CUtil::JSEscape(GetMessage("AddFolderDialogButtonCancelText"))?>", 
		AddFolderDialogButtonSkipAllText: "<?=CUtil::JSEscape(GetMessage("AddFolderDialogButtonSkipAllText"))?>", 
		AddFolderDialogButtonSkipText: "<?=CUtil::JSEscape(GetMessage("AddFolderDialogButtonSkipText"))?>", 
		AddFolderDialogTitleText: "<?=CUtil::JSEscape(GetMessage("AddFolderDialogTitleText"))?>", 
		AuthenticationRequestBasicText: "<?=CUtil::JSEscape(GetMessage("AuthenticationRequestBasicText"))?>", 
		AuthenticationRequestButtonCancelText: "<?=CUtil::JSEscape(GetMessage("AuthenticationRequestButtonCancelText"))?>", 
		AuthenticationRequestButtonOkText: "OK", 
		AuthenticationRequestDomainText: "<?=CUtil::JSEscape(GetMessage("AuthenticationRequestDomainText"))?>", 
		AuthenticationRequestLoginText: "<?=CUtil::JSEscape(GetMessage("AuthenticationRequestLoginText"))?>", 
		AuthenticationRequestNtlmText: "<?=CUtil::JSEscape(GetMessage("AuthenticationRequestNtlmText"))?>", 
		AuthenticationRequestPasswordText: "<?=CUtil::JSEscape(GetMessage("AuthenticationRequestPasswordText"))?>", 

		ButtonAddAllToUploadListText: "<?=CUtil::JSEscape(GetMessage("ButtonAddAllToUploadListText"))?>", 
		ButtonAddFilesText: "<?=CUtil::JSEscape(GetMessage("ButtonAddFilesText"))?>", 
		ButtonAddFoldersText: "<?=CUtil::JSEscape(GetMessage("ButtonAddFoldersText"))?>", 
		ButtonAddToUploadListText: "<?=CUtil::JSEscape(GetMessage("ButtonAddToUploadListText"))?>", 
		ButtonAdvancedDetailsCancelText: "<?=CUtil::JSEscape(GetMessage("ButtonAdvancedDetailsCancelText"))?>", 
		
		ButtonCheckAllText: "<?=CUtil::JSEscape(GetMessage("ButtonCheckAllText"))?>", 
		ButtonDeleteFilesText: "",  
		ButtonDeselectAllText: "<?=CUtil::JSEscape(GetMessage("ButtonDeselectAllText"))?>", 
		ButtonPasteText: "",  
		ButtonRemoveAllFromUploadListText: "<?=CUtil::JSEscape(GetMessage("ButtonRemoveAllFromUploadListText"))?>", 
		ButtonRemoveFromUploadListText: "<?=CUtil::JSEscape(GetMessage("ButtonRemoveFromUploadListText"))?>", 
		ButtonSelectAllText: "<?=CUtil::JSEscape(GetMessage("ButtonSelectAllText"))?>", 
		ButtonSendText: "<?=CUtil::JSEscape(GetMessage("ButtonSendText"))?>", 
		ButtonStopText: "",  
		
		ButtonUncheckAllText: "<?=CUtil::JSEscape(GetMessage("ButtonUncheckAllText"))?>", 

		CmykImagesAreNotAllowedText: "<?=CUtil::JSEscape(GetMessage("CmykImagesAreNotAllowedText"))?>", 
		DescriptionEditorButtonCancelText: "<?=CUtil::JSEscape(GetMessage("DescriptionEditorButtonCancelText"))?>", 
		DescriptionEditorButtonOkText: "OK", 
		
		//To be supplied
		DeleteFilesDialogTitleText: "Confirm File Delete", 
		//To be supplied
		DeleteSelectedFilesDialogMessageText: "Are you sure you want to permanently delete selected items?", 
		//To be supplied
		DeleteUploadedFilesDialogMessageText: "Are you sure you want to permanently delete uploaded items?", 
		DimensionsAreTooLargeText: "<?=CUtil::JSEscape(GetMessage("DimensionsAreTooLargeText"))?>", 
		DimensionsAreTooSmallText: "<?=CUtil::JSEscape(GetMessage("DimensionsAreTooSmallText"))?>", 
		DropFilesHereText: "<?=CUtil::JSEscape(GetMessage("DropFilesHereText"))?>", 
		EditDescriptionText: "<?=CUtil::JSEscape(GetMessage("EditDescriptionText"))?>", 
		
		//To be supplied
		ErrorDeletingFilesDialogMessageText: "<?=CUtil::JSEscape(GetMessage("ErrorDeletingFilesDialogMessageText"))?>", 
		FileIsTooLargeText: "<?=CUtil::JSEscape(GetMessage("FileIsTooLargeText"))?>", 
		FileIsTooSmallText: "<?=CUtil::JSEscape(GetMessage("FileIsTooSmallText"))?>", 
		HoursText: "<?=CUtil::JSEscape(GetMessage("HoursText"))?>", 
		IncludeSubfoldersText: "<?=CUtil::JSEscape(GetMessage("IncludeSubfoldersText"))?>", 
		KilobytesText: "<?=CUtil::JSEscape(GetMessage("KilobytesText"))?>", 
		LargePreviewGeneratingPreviewText: "<?=CUtil::JSEscape(GetMessage("LargePreviewGeneratingPreviewText"))?>", 
		LargePreviewIconTooltipText: "<?=CUtil::JSEscape(GetMessage("LargePreviewIconTooltipText"))?>", 
		LargePreviewNoPreviewAvailableText: "<?=CUtil::JSEscape(GetMessage("LargePreviewNoPreviewAvailableText"))?>", 
		ListColumnFileNameText: "<?=CUtil::JSEscape(GetMessage("ListColumnFileNameText"))?>", 
		ListColumnFileSizeText: "<?=CUtil::JSEscape(GetMessage("ListColumnFileSizeText"))?>", 
		ListColumnFileTypeText: "<?=CUtil::JSEscape(GetMessage("ListColumnFileTypeText"))?>", 
		ListColumnLastModifiedText: "<?=CUtil::JSEscape(GetMessage("ListColumnLastModifiedText"))?>", 
		ListKilobytesText: "<?=CUtil::JSEscape(GetMessage("ListKilobytesText"))?>", 
		LoadingFilesText: "<?=CUtil::JSEscape(GetMessage("LoadingFilesText"))?>", 
		MegabytesText: "<?=CUtil::JSEscape(GetMessage("MegabytesText"))?>", 
		MenuAddAllToUploadListText: "<?=CUtil::JSEscape(GetMessage("MenuAddAllToUploadListText"))?>", 
		MenuAddToUploadListText: "<?=CUtil::JSEscape(GetMessage("MenuAddToUploadListText"))?>", 
		MenuArrangeByModifiedText: "<?=CUtil::JSEscape(GetMessage("MenuArrangeByModifiedText"))?>", 
		MenuArrangeByNameText: "<?=CUtil::JSEscape(GetMessage("MenuArrangeByNameText"))?>", 
		MenuArrangeByPathText: "<?=CUtil::JSEscape(GetMessage("MenuArrangeByPathText"))?>", 
		MenuArrangeBySizeText: "<?=CUtil::JSEscape(GetMessage("MenuArrangeBySizeText"))?>", 
		MenuArrangeByText: "<?=CUtil::JSEscape(GetMessage("MenuArrangeByText"))?>", 
		MenuArrangeByTypeText: "<?=CUtil::JSEscape(GetMessage("MenuArrangeByTypeText"))?>", 
		MenuArrangeByUnsortedText: "<?=CUtil::JSEscape(GetMessage("MenuArrangeByUnsortedText"))?>", 
		MenuDeselectAllText: "<?=CUtil::JSEscape(GetMessage("MenuDeselectAllText"))?>", 
		MenuDetailsText: "<?=CUtil::JSEscape(GetMessage("MenuDetailsText"))?>", 
		MenuIconsText: "<?=CUtil::JSEscape(GetMessage("MenuIconsText"))?>", 
		MenuInvertSelectionText: "<?=CUtil::JSEscape(GetMessage("MenuInvertSelectionText"))?>", 
		MenuListText: "<?=CUtil::JSEscape(GetMessage("MenuListText"))?>", 
		MenuRefreshText: "<?=CUtil::JSEscape(GetMessage("MenuRefreshText"))?>", 
		MenuRemoveAllFromUploadListText: "<?=CUtil::JSEscape(GetMessage("MenuRemoveAllFromUploadListText"))?>", 
		MenuRemoveFromUploadListText: "<?=CUtil::JSEscape(GetMessage("MenuRemoveFromUploadListText"))?>", 
		MenuSelectAllText: "<?=CUtil::JSEscape(GetMessage("MenuSelectAllText"))?>", 
		MenuThumbnailsText: "<?=CUtil::JSEscape(GetMessage("MenuThumbnailsText"))?>", 
		MessageBoxTitleText: "<?=CUtil::JSEscape(GetMessage("MessageBoxTitleText"))?>", 
		MessageCannotConnectToInternetText: "<?=CUtil::JSEscape(GetMessage("MessageCannotConnectToInternetText"))?>", 
		MessageCmykImagesAreNotAllowedText: "<?=CUtil::JSEscape(GetMessage("MessageCmykImagesAreNotAllowedText"))?>", 
		MessageDimensionsAreTooLargeText: "<?=CUtil::JSEscape(GetMessage("MessageDimensionsAreTooLargeText"))?>", 
		MessageDimensionsAreTooSmallText: "<?=CUtil::JSEscape(GetMessage("MessageDimensionsAreTooSmallText"))?>", 
		MessageFileSizeIsTooSmallText: "<?=CUtil::JSEscape(GetMessage("MessageFileSizeIsTooSmallText"))?>", 
		MessageMaxFileCountExceededText: "<?=CUtil::JSEscape(GetMessage("MessageMaxFileCountExceededText"))?>", 
		MessageMaxFileSizeExceededText: "<?=CUtil::JSEscape(GetMessage("MessageMaxFileSizeExceededText"))?>", 
		MessageMaxTotalFileSizeExceededText: "<?=CUtil::JSEscape(GetMessage("MessageMaxTotalFileSizeExceededText"))?>", 
		MessageNoInternetSessionWasEstablishedText: "<?=CUtil::JSEscape(GetMessage("MessageNoInternetSessionWasEstablishedText"))?>", 
		MessageNoResponseFromServerText: "<?=CUtil::JSEscape(GetMessage("MessageNoResponseFromServerText"))?>", 
		MessageRetryOpenFolderText: "<?=CUtil::JSEscape(GetMessage("MessageRetryOpenFolderText"))?>", 
		MessageServerNotFoundText: "<?=CUtil::JSEscape(GetMessage("MessageServerNotFoundText"))?>", 
		MessageSwitchAnotherFolderWarningText: "<?=CUtil::JSEscape(GetMessage("MessageSwitchAnotherFolderWarningText"))?>", 
		MessageUnexpectedErrorText: "<?=CUtil::JSEscape(GetMessage("MessageUnexpectedErrorText"))?>", 
		MessageUploadCancelledText: "<?=CUtil::JSEscape(GetMessage("MessageUploadCancelledText"))?>", 
		MessageUploadCompleteText: "<?=CUtil::JSEscape(GetMessage("MessageUploadCompleteText"))?>", 
		MessageUploadFailedText: "<?=CUtil::JSEscape(GetMessage("MessageUploadFailedText"))?>", 
		MessageUserSpecifiedTimeoutHasExpiredText: "<?=CUtil::JSEscape(GetMessage("MessageUserSpecifiedTimeoutHasExpiredText"))?>", 
		MinutesText: "<?=CUtil::JSEscape(GetMessage("MinutesText"))?>", 
		ProgressDialogCancelButtonText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogCancelButtonText"))?>", 
		ProgressDialogCloseButtonText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogCloseButtonText"))?>", 
		ProgressDialogCloseWhenUploadCompletesText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogCloseWhenUploadCompletesText"))?>", 
		ProgressDialogEstimatedTimeText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogEstimatedTimeText"))?>", 
		ProgressDialogPreparingDataText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogPreparingDataText"))?>", 
		ProgressDialogSentText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogSentText"))?>", 
		ProgressDialogTitleText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogTitleText"))?>", 
		ProgressDialogWaitingForResponseFromServerText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogWaitingForResponseFromServerText"))?>", 
		ProgressDialogWaitingForRetryText: "<?=CUtil::JSEscape(GetMessage("ProgressDialogWaitingForRetryText"))?>", 
		RemoveIconTooltipText: "<?=CUtil::JSEscape(GetMessage("RemoveIconTooltipText"))?>", 
		RotateIconClockwiseTooltipText: "<?=CUtil::JSEscape(GetMessage("RotateIconClockwiseTooltipText"))?>", 
		RotateIconCounterclockwiseTooltipText: "<?=CUtil::JSEscape(GetMessage("RotateIconCounterclockwiseTooltipText"))?>", 
		SecondsText: "<?=CUtil::JSEscape(GetMessage("SecondsText"))?>", 
		UnixFileSystemRootText: "<?=CUtil::JSEscape(GetMessage("UnixFileSystemRootText"))?>", 
		UnixHomeDirectoryText: "<?=CUtil::JSEscape(GetMessage("UnixHomeDirectoryText"))?>"
	}
}
</script>
