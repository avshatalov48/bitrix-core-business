this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	class Controller {
	  static getGroupData(groupId, select) {
	    return BX.ajax.runAction('socialnetwork.api.workgroup.get', {
	      data: {
	        params: {
	          select,
	          groupId
	        }
	      }
	    }).then(response => {
	      var _response$data, _response$data2, _response$data3, _response$data$USER_D, _response$data4, _response$data5, _response$data6, _response$data7, _response$data7$ACTIO, _response$data8, _response$data8$ACTIO, _response$data9, _response$data9$ACTIO, _response$data10, _response$data10$ACTI, _response$data11, _response$data11$ACTI, _response$data12, _response$data12$ACTI, _response$data13, _response$data14, _response$data15, _response$data15$SUBJ, _response$data16, _response$data17;
	      return {
	        id: response.data.ID,
	        name: response.data.NAME,
	        description: response.data.DESCRIPTION,
	        avatar: (_response$data = response.data) == null ? void 0 : _response$data.AVATAR,
	        isPin: (_response$data2 = response.data) == null ? void 0 : _response$data2.IS_PIN,
	        privacyCode: (_response$data3 = response.data) == null ? void 0 : _response$data3.PRIVACY_CODE,
	        isSubscribed: (_response$data$USER_D = response.data.USER_DATA) == null ? void 0 : _response$data$USER_D.IS_SUBSCRIBED,
	        numberOfMembers: (_response$data4 = response.data) == null ? void 0 : _response$data4.NUMBER_OF_MEMBERS,
	        listOfMembers: (_response$data5 = response.data) == null ? void 0 : _response$data5.LIST_OF_MEMBERS,
	        groupMembersList: (_response$data6 = response.data) == null ? void 0 : _response$data6.GROUP_MEMBERS_LIST,
	        actions: {
	          canEdit: (_response$data7 = response.data) == null ? void 0 : (_response$data7$ACTIO = _response$data7.ACTIONS) == null ? void 0 : _response$data7$ACTIO.EDIT,
	          canInvite: (_response$data8 = response.data) == null ? void 0 : (_response$data8$ACTIO = _response$data8.ACTIONS) == null ? void 0 : _response$data8$ACTIO.INVITE,
	          canLeave: (_response$data9 = response.data) == null ? void 0 : (_response$data9$ACTIO = _response$data9.ACTIONS) == null ? void 0 : _response$data9$ACTIO.LEAVE,
	          canFollow: (_response$data10 = response.data) == null ? void 0 : (_response$data10$ACTI = _response$data10.ACTIONS) == null ? void 0 : _response$data10$ACTI.FOLLOW,
	          canPin: (_response$data11 = response.data) == null ? void 0 : (_response$data11$ACTI = _response$data11.ACTIONS) == null ? void 0 : _response$data11$ACTI.PIN,
	          canEditFeatures: (_response$data12 = response.data) == null ? void 0 : (_response$data12$ACTI = _response$data12.ACTIONS) == null ? void 0 : _response$data12$ACTI.EDIT_FEATURES
	        },
	        counters: (_response$data13 = response.data) == null ? void 0 : _response$data13.COUNTERS,
	        efficiency: (_response$data14 = response.data) == null ? void 0 : _response$data14.EFFICIENCY,
	        subject: (_response$data15 = response.data) == null ? void 0 : (_response$data15$SUBJ = _response$data15.SUBJECT_DATA) == null ? void 0 : _response$data15$SUBJ.NAME,
	        dateCreate: (_response$data16 = response.data) == null ? void 0 : _response$data16.DATE_CREATE,
	        features: (_response$data17 = response.data) == null ? void 0 : _response$data17.FEATURES
	      };
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.log(error);
	    });
	  }
	  static inviteUsers(spaceId, users) {
	    return BX.ajax.runAction('socialnetwork.api.workgroup.updateInvitedUsers', {
	      data: {
	        spaceId,
	        users: [0, ...users]
	      }
	    });
	  }
	  static changePrivacy(groupId, privacyCode) {
	    const fields = {};
	    if (privacyCode === 'open') {
	      fields.VISIBLE = 'Y';
	      fields.OPENED = 'Y';
	      fields.EXTERNAL = 'N';
	    }
	    if (privacyCode === 'closed') {
	      fields.VISIBLE = 'Y';
	      fields.OPENED = 'N';
	      fields.EXTERNAL = 'N';
	    }
	    if (privacyCode === 'secret') {
	      fields.VISIBLE = 'N';
	      fields.OPENED = 'N';
	      fields.EXTERNAL = 'N';
	    }
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.update', {
	      data: {
	        groupId,
	        fields
	      }
	    });
	  }
	  static changeTitle(groupId, title) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.update', {
	      data: {
	        groupId,
	        fields: {
	          NAME: title
	        }
	      }
	    });
	  }
	  static changeDescription(groupId, description) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.update', {
	      data: {
	        groupId,
	        fields: {
	          DESCRIPTION: description
	        }
	      }
	    });
	  }
	  static changeTags(groupId, tags) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.update', {
	      data: {
	        groupId,
	        fields: {
	          KEYWORDS: tags.join(',')
	        }
	      }
	    });
	  }
	  static changeFeature(groupId, feature) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.setFeature', {
	      data: {
	        groupId,
	        feature
	      }
	    });
	  }
	  static updatePhoto(groupId, photo) {
	    var _photo$name;
	    const formData = new FormData();
	    // eslint-disable-next-line no-param-reassign
	    (_photo$name = photo.name) != null ? _photo$name : photo.name = 'tmp.png';
	    formData.append('newPhoto', photo, photo.name);
	    formData.append('groupId', groupId);
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.updatePhoto', {
	      data: formData
	    });
	  }
	  static changePin(groupId, isPinned) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.changePin', {
	      data: {
	        groupIdList: [groupId],
	        action: isPinned ? 'pin' : 'unpin'
	      }
	    });
	  }
	  static setSubscription(groupId, isSubscribed) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.setSubscription', {
	      data: {
	        params: {
	          groupId,
	          value: isSubscribed ? 'Y' : 'N'
	        }
	      }
	    });
	  }
	  static leaveGroup(groupId) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.leave', {
	      data: {
	        groupId
	      }
	    });
	  }
	  static deleteGroup(groupId) {
	    return main_core.ajax.runAction('socialnetwork.api.workgroup.delete', {
	      data: {
	        groupId
	      }
	    });
	  }
	  static openGroupUsers(mode) {
	    const availableModes = {
	      all: 'members',
	      in: 'requests_in',
	      out: 'requests_out'
	    };
	    const uri = new main_core.Uri(this.paths.pathToUsers);
	    uri.setQueryParams({
	      mode: availableModes[mode]
	    });
	    BX.SidePanel.Instance.open(uri.toString(), {
	      width: 1200,
	      cacheable: false,
	      loader: 'group-users-loader'
	    });
	  }
	  static openGroupFeatures() {
	    BX.SidePanel.Instance.open(this.paths.pathToFeatures, {
	      width: 800,
	      loader: 'group-features-loader'
	    });
	  }
	  static openGroupInvite() {
	    BX.SidePanel.Instance.open(this.paths.pathToInvite, {
	      width: 950,
	      loader: 'group-invite-loader'
	    });
	  }
	  static openCommonSpace() {
	    location.href = this.paths.pathToCommonSpace;
	  }
	}

	exports.Controller = Controller;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX));
//# sourceMappingURL=controller.bundle.js.map
