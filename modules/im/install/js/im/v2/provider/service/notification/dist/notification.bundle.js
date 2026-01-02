/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_application_core,im_v2_lib_logger,im_v2_const,im_v2_lib_user) {
	'use strict';

	class NotificationService {
	  constructor() {
	    this.store = null;
	    this.restClient = null;
	    this.limitPerPage = 50;
	    this.isLoading = false;
	    this.lastId = 0;
	    this.lastType = 0;
	    this.hasMoreItemsToLoad = true;
	    this.notificationsToDelete = new Set();
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.deleteWithDebounce = main_core.Runtime.debounce(this.deleteRequest, 500, this);
	    this.userManager = new im_v2_lib_user.UserManager();
	  }
	  loadFirstPage() {
	    this.isLoading = true;
	    return this.requestItems({
	      firstPage: true
	    });
	  }
	  loadNextPage() {
	    if (this.isLoading || !this.hasMoreItemsToLoad) {
	      return Promise.resolve();
	    }
	    this.isLoading = true;
	    return this.requestItems();
	  }
	  delete(notificationId) {
	    this.notificationsToDelete.add(notificationId);
	    this.store.dispatch('notifications/delete', {
	      id: notificationId
	    });
	    this.store.dispatch('notifications/deleteFromSearch', {
	      id: notificationId
	    });
	    this.deleteWithDebounce();
	  }
	  sendConfirmAction(notificationId, value) {
	    const requestParams = {
	      NOTIFY_ID: notificationId,
	      NOTIFY_VALUE: value
	    };
	    this.store.dispatch('notifications/delete', {
	      id: notificationId
	    });
	    this.restClient.callMethod('im.notify.confirm', requestParams).catch(result => {
	      console.error('NotificationService: sendConfirmAction error', result.error());
	    });
	  }
	  async sendQuickAnswer(params) {
	    const {
	      id,
	      text,
	      callbackSuccess = () => {},
	      callbackError = () => {}
	    } = params;
	    try {
	      const response = await this.restClient.callMethod(im_v2_const.RestMethod.imNotifyAnswer, {
	        notify_id: id,
	        answer_text: text
	      });
	      callbackSuccess(response);
	    } catch (error) {
	      console.error('NotificationService: sendQuickAnswer error', error);
	      callbackError();
	    }
	  }
	  deleteRequest() {
	    const idsToDelete = [...this.notificationsToDelete];
	    this.restClient.callMethod('im.notify.delete', {
	      id: idsToDelete
	    }).catch(result => {
	      console.error('NotificationService: deleteRequest error', result.error());
	    });
	    this.notificationsToDelete.clear();
	  }
	  requestItems({
	    firstPage = false
	  } = {}) {
	    const imNotifyGetQueryParams = {
	      LIMIT: this.limitPerPage,
	      CONVERT_TEXT: 'Y'
	    };
	    const batchQueryParams = {
	      [im_v2_const.RestMethod.imNotifyGet]: [im_v2_const.RestMethod.imNotifyGet, imNotifyGetQueryParams]
	    };
	    if (firstPage) {
	      batchQueryParams[im_v2_const.RestMethod.imNotifySchemaGet] = [im_v2_const.RestMethod.imNotifySchemaGet, {}];
	    } else {
	      imNotifyGetQueryParams.LAST_ID = this.lastId;
	      imNotifyGetQueryParams.LAST_TYPE = this.lastType;
	    }
	    return new Promise(resolve => {
	      this.restClient.callBatch(batchQueryParams, response => {
	        im_v2_lib_logger.Logger.warn('im.notify.get: result', response);
	        resolve(this.handleResponse(response));
	      });
	    });
	  }
	  handleResponse(response) {
	    const imNotifyGetResponse = response[im_v2_const.RestMethod.imNotifyGet].data();
	    this.hasMoreItemsToLoad = !this.isLastPage(imNotifyGetResponse.notifications);
	    if (imNotifyGetResponse.notifications.length === 0) {
	      im_v2_lib_logger.Logger.warn('im.notify.get: no notifications', imNotifyGetResponse);
	      return Promise.resolve();
	    }
	    this.lastId = this.getLastItemId(imNotifyGetResponse.notifications);
	    this.lastType = this.getLastItemType(imNotifyGetResponse.notifications);
	    return this.updateModels(imNotifyGetResponse).then(() => {
	      this.isLoading = false;
	      if (response[im_v2_const.RestMethod.imNotifySchemaGet]) {
	        return response[im_v2_const.RestMethod.imNotifySchemaGet].data();
	      }
	      return {};
	    });
	  }
	  updateModels(imNotifyGetResponse) {
	    this.userManager.setUsersToModel(imNotifyGetResponse.users);
	    return this.store.dispatch('notifications/initialSet', imNotifyGetResponse);
	  }
	  getLastItemId(collection) {
	    return collection[collection.length - 1].id;
	  }
	  getLastItemType(collection) {
	    return this.getItemType(collection[collection.length - 1]);
	  }
	  getItemType(item) {
	    return item.notify_type === im_v2_const.NotificationTypesCodes.confirm ? im_v2_const.NotificationTypesCodes.confirm : im_v2_const.NotificationTypesCodes.simple;
	  }
	  isLastPage(notifications) {
	    if (!main_core.Type.isArrayFilled(notifications)) {
	      return true;
	    }
	    return notifications.length < this.limitPerPage;
	  }
	  destroy() {
	    im_v2_lib_logger.Logger.warn('Notification service destroyed');
	  }
	}

	exports.NotificationService = NotificationService;

}((this.BX.Messenger.v2.Service = this.BX.Messenger.v2.Service || {}),BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Const,BX.Messenger.v2.Lib));
//# sourceMappingURL=notification.bundle.js.map
