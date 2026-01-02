/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_notification,im_v2_application_core,im_public,im_v2_const,main_core) {
	'use strict';

	const showNotification = (text, params) => {
	  BX.UI.Notification.Center.notify({
	    content: text,
	    ...params
	  });
	};

	const extractRestErrorCode = error => {
	  const {
	    ex: {
	      error: errorCode
	    }
	  } = error;
	  return errorCode;
	};

	const ChatNotifier = {
	  handleLoadError(error) {
	    // eslint-disable-next-line unicorn/prefer-switch
	    if (error.code === im_v2_const.ErrorCode.chat.notFound) {
	      this.onNotFoundError();
	    } else if (error.code === im_v2_const.ErrorCode.chat.accessDenied) {
	      this.onAccessDeniedError();
	    } else if (error.code === im_v2_const.ErrorCode.message.notFound) {
	      this.onContextMessageNotFoundError();
	    }
	  },
	  handleLeaveError(error) {
	    var _NotificationTextByEr;
	    const errorCode = extractRestErrorCode(error);
	    const NotificationTextByErrorCode = {
	      [im_v2_const.ErrorCode.user.invitedFromStructure]: main_core.Loc.getMessage('IM_NOTIFIER_LEAVE_CHAT_STRUCTURE_ERROR'),
	      default: main_core.Loc.getMessage('IM_NOTIFIER_LEAVE_CHAT_ERROR')
	    };
	    const notificationText = (_NotificationTextByEr = NotificationTextByErrorCode[errorCode]) != null ? _NotificationTextByEr : NotificationTextByErrorCode.default;
	    showNotification(notificationText);
	  },
	  handleUserKickError(error) {
	    var _NotificationTextByEr2;
	    const errorCode = extractRestErrorCode(error);
	    const NotificationTextByErrorCode = {
	      [im_v2_const.ErrorCode.user.invitedFromStructure]: main_core.Loc.getMessage('IM_NOTIFIER_KICK_CHAT_STRUCTURE_ERROR'),
	      default: main_core.Loc.getMessage('IM_NOTIFIER_KICK_CHAT_ERROR')
	    };
	    const notificationText = (_NotificationTextByEr2 = NotificationTextByErrorCode[errorCode]) != null ? _NotificationTextByEr2 : NotificationTextByErrorCode.default;
	    showNotification(notificationText);
	  },
	  onNotFoundError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CHAT_ACCESS_ERROR'));
	  },
	  onAccessDeniedError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CHAT_ACCESS_ERROR'));
	  },
	  onContextMessageNotFoundError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CONTEXT_MESSAGE_NOT_FOUND_ERROR'));
	  },
	  onCreateError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CHAT_CREATE_ERROR'));
	  },
	  onUpdateError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CHAT_UPDATE_ERROR'));
	  },
	  onDeleteError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CHAT_DELETE_ERROR'));
	  },
	  onRenameError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CHAT_RENAME_ERROR'));
	  },
	  onMessagesPinLimitError(pinLimit) {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_MESSAGES_PIN_LIMIT_ERROR', {
	      '#MAX_PINS#': pinLimit
	    }));
	  }
	};

	const MessageNotifier = {
	  onCopyComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_MESSAGE_COPY_COMPLETE'));
	  },
	  onCopyLinkComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_MESSAGE_LINK_COPY_COMPLETE'));
	  },
	  onAddToFavoriteComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_MESSAGE_FAVORITE_ADD_COMPLETE'));
	  },
	  onForwardNotesComplete(messagesIds) {
	    const text = messagesIds.length > 1 ? main_core.Loc.getMessage('IM_NOTIFIER_MESSAGE_FORWARD_NOTES_SEVERAL_MESSAGES_COMPLETE') : main_core.Loc.getMessage('IM_NOTIFIER_MESSAGE_FORWARD_NOTES_COMPLETE');
	    const dialogId = im_v2_application_core.Core.getUserId().toString();
	    const notesOpeningAction = {
	      title: main_core.Loc.getMessage('IM_NOTIFIER_MESSAGE_FORWARD_NOTES_OPEN_COMPLETE'),
	      events: {
	        click: () => {
	          void im_public.Messenger.openChat(dialogId);
	        }
	      }
	    };
	    showNotification(text, {
	      actions: [notesOpeningAction]
	    });
	  },
	  handleLoadContextError(error) {
	    if (error.code === im_v2_const.ErrorCode.message.notFound) {
	      this.onNotFoundError();
	    }
	  },
	  onNotFoundError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CONTEXT_MESSAGE_NOT_FOUND_ERROR'));
	  },
	  onSelectLimitError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_MESSAGE_SELECT_LIMIT_ERROR'));
	  }
	};

	const CollabErrorCode = {
	  emptyName: 'name',
	  duplicateName: 'ERROR_GROUP_NAME_EXISTS',
	  urlInName: 'ERROR_NAME_CONTAINS_URL',
	  tasksNotEmpty: 'TASKS_NOT_EMPTY',
	  diskNotEmpty: 'DISK_NOT_EMPTY',
	  calendarNotEmpty: 'CALENDAR_NOT_EMPTY'
	};
	const NotEmptyCollabErrorCodes = new Set([CollabErrorCode.tasksNotEmpty, CollabErrorCode.diskNotEmpty, CollabErrorCode.calendarNotEmpty]);
	const CollabNotifier = {
	  onBeforeDelete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_DELETE_PROGRESS'));
	  },
	  onUpdateLinkComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_LINK_UPDATE_COMPLETE'));
	  },
	  handleCreateError(error) {
	    var _NotificationTextByEr;
	    const NotificationTextByErrorCode = {
	      [CollabErrorCode.emptyName]: main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_EMPTY_NAME_ERROR'),
	      [CollabErrorCode.duplicateName]: main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_DUPLICATE_NAME_ERROR'),
	      [CollabErrorCode.urlInName]: main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_URL_IN_NAME_ERROR'),
	      default: main_core.Loc.getMessage('IM_NOTIFIER_CHAT_CREATE_ERROR')
	    };
	    const notificationText = (_NotificationTextByEr = NotificationTextByErrorCode[error.code]) != null ? _NotificationTextByEr : NotificationTextByErrorCode.default;
	    showNotification(notificationText);
	  },
	  handleUpdateError(error) {
	    var _NotificationTextByEr2;
	    const NotificationTextByErrorCode = {
	      [CollabErrorCode.emptyName]: main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_EMPTY_NAME_ERROR'),
	      [CollabErrorCode.duplicateName]: main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_DUPLICATE_NAME_ERROR'),
	      [CollabErrorCode.urlInName]: main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_URL_IN_NAME_ERROR'),
	      default: main_core.Loc.getMessage('IM_NOTIFIER_CHAT_UPDATE_ERROR')
	    };
	    const notificationText = (_NotificationTextByEr2 = NotificationTextByErrorCode[error.code]) != null ? _NotificationTextByEr2 : NotificationTextByErrorCode.default;
	    showNotification(notificationText);
	  },
	  handleDeleteError(error) {
	    if (NotEmptyCollabErrorCodes.has(error.code)) {
	      showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_DELETE_ENTITIES_ERROR'));
	      return;
	    }
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_DELETE_ERROR'));
	  },
	  onLeaveError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_LEAVE_ERROR'));
	  },
	  onKickUserError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_KICK_ERROR'));
	  },
	  onCollaberNotAcceptInvitation() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_COLLABER_NOT_ACCEPT_INVITATION'));
	  },
	  onCopyLinkError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_COLLAB_COPY_LINK_FORBIDDEN_ERROR'));
	  }
	};

	const FileNotifier = {
	  onDiskSaveComplete(isSingleFile = true) {
	    if (isSingleFile) {
	      showNotification(main_core.Loc.getMessage('IM_NOTIFIER_FILE_DISK_SAVE_COMPLETE'));
	      return;
	    }
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_FILES_DISK_SAVE_COMPLETE'));
	  },
	  onCopyComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_FILE_COPY_COMPLETE'));
	  },
	  handleUploadError(error) {
	    if (error.getCode() === im_v2_const.ErrorCode.file.maxFileSize) {
	      showNotification(`${error.getMessage()}<br>${error.getDescription()}`);
	    }
	  }
	};

	const InviteNotifier = {
	  onResendComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_INVITE_RESEND_COMPLETE'), {
	      autoHideDelay: 2000
	    });
	  },
	  onCancelComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_INVITE_CANCEL_COMPLETE'), {
	      autoHideDelay: 2000
	    });
	  }
	};

	const ConferenceNotifier = {
	  onCopyLinkComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CONFERENCE_LINK_COPY_COMPLETE'));
	  },
	  onPasswordError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_CONFERENCE_PASSWORD_ERROR'));
	  }
	};

	const SupportNotifier = {
	  onVoteClosedError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_VOTE_CLOSED_ERROR'));
	  }
	};

	const SpeechNotifier = {
	  onRecognitionError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_AUDIO_INPUT_ERROR'));
	  }
	};

	const CallNotifier = {
	  onBackgroundFileSizeError(payload) {
	    const {
	      fileName,
	      fileSizeLimit
	    } = payload;
	    const phrase = main_core.Loc.getMessage('IM_NOTIFIER_CALL_BACKGROUND_FILE_SIZE_ERROR', {
	      '#LIMIT#': fileSizeLimit,
	      '#FILE_NAME#': fileName
	    });
	    showNotification(phrase);
	  },
	  onBackgroundUnsupportedError(fileName) {
	    const phrase = main_core.Loc.getMessage('IM_NOTIFIER_CALL_BACKGROUND_FILE_UNSUPPORTED_ERROR', {
	      '#FILE_NAME#': fileName
	    });
	    showNotification(phrase);
	  }
	};

	const RecentErrorCodes = {
	  maxPinned: 'MAX_PINNED_CHATS_ERROR'
	};
	const MAX_PINS = 45;
	const RecentNotifier = {
	  handlePinError(error) {
	    var _NotificationTextByEr;
	    const maxPinnedMessage = main_core.Loc.getMessage('IM_NOTIFIER_RECENT_PIN_LIMIT_ERROR', {
	      '#MAX_PINS#': MAX_PINS
	    });
	    const NotificationTextByErrorCode = {
	      [RecentErrorCodes.maxPinned]: maxPinnedMessage,
	      default: main_core.Loc.getMessage('IM_NOTIFIER_RECENT_PIN_DEFAULT_ERROR')
	    };
	    const notificationText = (_NotificationTextByEr = NotificationTextByErrorCode[error.code]) != null ? _NotificationTextByEr : NotificationTextByErrorCode.default;
	    showNotification(notificationText);
	  },
	  onUnpinError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_RECENT_UNPIN_DEFAULT_ERROR'));
	  }
	};

	const Notifier = {
	  chat: ChatNotifier,
	  message: MessageNotifier,
	  collab: CollabNotifier,
	  file: FileNotifier,
	  invite: InviteNotifier,
	  conference: ConferenceNotifier,
	  support: SupportNotifier,
	  speech: SpeechNotifier,
	  call: CallNotifier,
	  recent: RecentNotifier,
	  onCopyTextComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_TEXT_COPY_COMPLETE'));
	  },
	  onCopyLinkComplete() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_LINK_COPY_COMPLETE'));
	  },
	  onDefaultError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_DEFAULT_ERROR'));
	  },
	  onCopyLinkError() {
	    showNotification(main_core.Loc.getMessage('IM_NOTIFIER_LINK_COPY_ERROR'));
	  }
	};

	exports.Notifier = Notifier;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=notifier.bundle.js.map
