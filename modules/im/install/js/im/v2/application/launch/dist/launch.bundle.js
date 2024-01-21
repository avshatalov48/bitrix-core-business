/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_logger) {
	'use strict';

	const ApplicationLauncher = (app, params = {}) => {
	  var _BX, _BX$Runtime;
	  let application = app;
	  const name = app.toString();
	  application = application.slice(0, 1).toUpperCase() + application.slice(1);
	  if (application === 'Launch' || application === 'Core' || application.endsWith('Application')) {
	    im_v2_lib_logger.Logger.error('BX.Messenger.Application.Launch: specified name is forbidden.');
	    return Promise.reject();
	  }
	  const launch = () => {
	    try {
	      BX.Messenger.v2.Application[name] = new BX.Messenger.v2.Application[`${application}Application`](params);
	      return BX.Messenger.v2.Application[name].ready();
	    } catch (error) {
	      const errorMessage = `BX.Messenger.Application.Launch: application "${application}" is not initialized.`;
	      im_v2_lib_logger.Logger.error(errorMessage, error);
	      return Promise.reject(errorMessage);
	    }
	  };
	  if (!BX.Messenger.v2.Application[`${application}Application`] && (_BX = BX) != null && (_BX$Runtime = _BX.Runtime) != null && _BX$Runtime.loadExtension) {
	    const loadExtension = `im.v2.application.${application.toString().toLowerCase()}`;
	    return BX.Runtime.loadExtension(loadExtension).then(() => launch());
	  }
	  return launch();
	};

	exports.Launch = ApplicationLauncher;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Lib));
//# sourceMappingURL=launch.bundle.js.map
