this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
(function (exports,im_oldChatEmbedding_lib_logger) {
	'use strict';

	const ApplicationLauncher = function (app, params = {}) {
	  var _BX, _BX$Runtime;
	  let application = '';
	  let name = '';
	  if (typeof app === 'object') {
	    name = app.name.toString();
	    application = app.application.toString();
	  } else {
	    name = app.toString();
	    application = app;
	  }
	  application = application.slice(0, 1).toUpperCase() + application.slice(1);
	  if (application === 'Launch' || application === 'Core' || application.endsWith('Application')) {
	    im_oldChatEmbedding_lib_logger.Logger.error('BX.Messenger.Application.Launch: specified name is forbidden.');
	    return Promise.reject();
	  }
	  const launch = function () {
	    try {
	      BX.Messenger.Embedding.Application[name] = new BX.Messenger.Embedding.Application[`${application}Application`](params);
	      return BX.Messenger.Embedding.Application[name].ready();
	    } catch (error) {
	      im_oldChatEmbedding_lib_logger.Logger.error(`BX.Messenger.Application.Launch: application "${application}" is not initialized.`, error);
	      return false;
	    }
	  };
	  if (!BX.Messenger.Embedding.Application[`${application}Application`] && (_BX = BX) != null && (_BX$Runtime = _BX.Runtime) != null && _BX$Runtime.loadExtension) {
	    const loadExtension = `im.old-chat-embedding.application.${application.toString().toLowerCase()}`;
	    return BX.Runtime.loadExtension(loadExtension).then(() => launch());
	  }
	  return launch();
	};

	exports.Launch = ApplicationLauncher;

}((this.BX.Messenger.Embedding.Application = this.BX.Messenger.Embedding.Application || {}),BX.Messenger.Embedding.Lib));
//# sourceMappingURL=launch.bundle.js.map
