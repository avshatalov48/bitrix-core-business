/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,im_v2_lib_logger,im_v2_lib_utils) {
	'use strict';

	const ChatEmbeddedApplication = {
	  task: 'task',
	  aiAssistantWidget: 'aiAssistantWidget'
	};

	const RESERVED_NAMES = new Set(['Launch', 'Core']);
	const Launch = async (applicationName, params = {}) => {
	  const {
	    embedded = false
	  } = params;
	  if (!validateApplicationName(applicationName)) {
	    im_v2_lib_logger.Logger.error('BX.Messenger.Application.Launch: specified name is forbidden');
	    return Promise.reject();
	  }
	  if (!isApplicationLoaded(applicationName)) {
	    await loadExtension(applicationName, embedded);
	  }
	  const capitalizedName = main_core.Text.capitalize(applicationName);
	  const className = `${capitalizedName}Application`;
	  const preparedApplicationName = prepareApplicationName(applicationName, embedded);
	  try {
	    BX.Messenger.v2.Application[preparedApplicationName] = new BX.Messenger.v2.Application[className](params);
	    return BX.Messenger.v2.Application[preparedApplicationName].ready();
	  } catch (error) {
	    const errorMessage = `BX.Messenger.Application.Launch: application "${capitalizedName}" is not initialized.`;
	    im_v2_lib_logger.Logger.error(errorMessage, error);
	    return Promise.reject(errorMessage);
	  }
	};
	const validateApplicationName = applicationName => {
	  const capitalizedName = main_core.Text.capitalize(applicationName);
	  return !RESERVED_NAMES.has(capitalizedName) && !capitalizedName.endsWith('Application');
	};
	const isApplicationLoaded = applicationName => {
	  const capitalizedName = main_core.Text.capitalize(applicationName);
	  const className = `${capitalizedName}Application`;
	  return Boolean(BX.Messenger.v2.Application[className]);
	};
	const loadExtension = async (applicationName, embedded) => {
	  const extensionName = embedded ? `im.v2.application.integration.${applicationName}` : `im.v2.application.${applicationName}`;
	  return main_core.Runtime == null ? void 0 : main_core.Runtime.loadExtension(extensionName);
	};
	const prepareApplicationName = (applicationName, embedded) => {
	  if (embedded) {
	    return `${applicationName}_${im_v2_lib_utils.Utils.text.getUuidV4()}`;
	  }
	  return applicationName;
	};

	exports.Launch = Launch;
	exports.ChatEmbeddedApplication = ChatEmbeddedApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=launch.bundle.js.map
