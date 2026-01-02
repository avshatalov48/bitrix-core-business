/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,rest_client,im_v2_lib_rest,im_v2_application_core,im_v2_const) {
	'use strict';

	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	class DiskService {
	  constructor() {
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	  }
	  delete({
	    chatId,
	    fileId
	  }) {
	    const queryParams = {
	      chat_id: chatId,
	      file_id: fileId
	    };
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imDiskFileDelete, queryParams).catch(result => {
	      console.error('DiskService: error deleting file', result.error());
	    });
	  }
	  async save(fileIds) {
	    const normalizedIds = fileIds.map(id => Number.parseInt(id, 10));
	    return im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2DiskFileSave, {
	      data: {
	        ids: normalizedIds
	      }
	    }).catch(([error]) => {
	      console.error('DiskService: error saving file on disk', error);
	      throw error;
	    });
	  }
	}

	exports.DiskService = DiskService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=disk.bundle.js.map
