import 'im.test';

import { Controller } from "im.controller";
import { VuexBuilder, VuexVendor } from "ui.vue.vuex";
import {
	CallApplicationModel,
	RecentModel,
	ApplicationModel,
	MessagesModel,
	DialoguesModel,
	UsersModel,
	FilesModel
} from "im.model";
import { ApplicationController } from "../../controller/src/application";
import { Utils } from "im.lib.utils";
import { VueVendor } from "ui.vue";
import { CoreRestHandler } from "im.provider.rest";

let controllerInitStub = null;
let sandbox = null;
let controller = null;

beforeEach(() => {
	sandbox = sinon.createSandbox();

	controllerInitStub = sandbox.stub(Controller.prototype, 'init').callsFake(() => {
		return Promise.reject();
	});

	controller = new Controller();
});

afterEach(() => {
	sandbox.restore();
});

describe('Core controller', function() {
	describe('Initialization', function() {
		it('should exist', () => {
			assert.equal(typeof Controller, 'function');
		});

		it('should construct default values', function() {
			assert.equal(controller.inited, false);
			assert.equal(controller.initPromise instanceof Promise, true);
			assert.equal(typeof controller.initPromiseResolver, 'function');
			assert.equal(controller.offline, false);
			assert.equal(Array.isArray(controller.restAnswerHandler), true);
			assert.equal(controller.restAnswerHandler.length, 0);
			assert.equal(Array.isArray(controller.vuexAdditionalModel), true);
			assert.equal(controller.vuexAdditionalModel.length, 0);
			assert.strictEqual(controller.store, null);
			assert.strictEqual(controller.storeBuilder, null);
		});
	});

	describe('prepareParams', function() {
		it('should exist', function() {
			assert.equal(typeof Controller.prototype.prepareParams, 'function');
		});

		it('should return promise', function() {
			const prepareParams = controller.prepareParams({});
			assert.equal(prepareParams instanceof Promise, true);
		});

		describe('localize', function() {
			it('should set localize to passed params.localize', function() {
				const testLocalize = { phrase1: 'test phrase1', phrase2: 'test phrase2' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.localize['phrase1'], testLocalize['phrase1']);
					assert.equal(controller.localize['phrase2'], testLocalize['phrase2']);
				});
			});

			it('should set localize to empty object if not passed params.localize', function() {
				return controller.prepareParams({}).then(() => {
					assert.equal(Object.keys(controller.localize).length, 0);
				});
			});
		});

		describe('host', function() {
			it('should set host to passed params.host', function() {
				const testHost = 'https://google.ru';
				return controller.prepareParams({ host: testHost }).then(() => {
					assert.equal(controller.host, testHost);
				});
			});

			it('should set host to location origin if not passed params.host', function() {
				let controller = new Controller();
				return controller.prepareParams({}).then(() => {
					assert.equal(controller.host, 'https://example.org');
				});
			});
		});

		describe('userId', function() {
			it('should set userId for number params.userId', function() {
				const testUserId = 99;
				return controller.prepareParams({ userId: testUserId }).then(() => {
					assert.equal(controller.userId, testUserId);
				});
			});

			it('should set userId for parsable string params.userId', function() {
				const testUserId = '99';
				return controller.prepareParams({ userId: testUserId }).then(() => {
					assert.equal(controller.userId, 99);
				});
			});

			it('should set userId to 0 for non-parsable string params.userId', function() {
				const testUserId = 'testUserId';
				return controller.prepareParams({ userId: testUserId }).then(() => {
					assert.equal(controller.userId, 0);
				});
			});

			it('should set userId if USER_ID localize phrase exists', function() {
				const testLocalize = { USER_ID: '99' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.userId, testLocalize['USER_ID']);
				});
			});

			it('should set userId to 0 if no params.userId and no localize', function() {
				return controller.prepareParams({}).then(() => {
					assert.equal(controller.userId, 0);
				});
			});
		});

		describe('siteId', function() {
			const defaultSiteId = 's1';

			it('should set siteId to string params.siteId', function() {
				const testSiteId = 's99';
				return controller.prepareParams({ siteId: testSiteId }).then(() => {
					assert.equal(controller.siteId, testSiteId);
				});
			});

			it('should set siteId to default for every non-string and empty string params.siteId', function() {
				const testSiteId = 99;
				return controller.prepareParams({ siteId: testSiteId }).then(() => {
					assert.equal(controller.siteId, defaultSiteId);
				});
			});

			it('should set siteId if SITE_ID localize phrase exists', function() {
				const testLocalize = { SITE_ID: 's99' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.siteId, testLocalize['SITE_ID']);
				});
			});

			it('should set siteId to default if no params.siteId and no localize', function() {
				return controller.prepareParams({}).then(() => {
					assert.equal(controller.siteId, defaultSiteId);
				});
			});
		});

		describe('siteDir', function() {
			const defaultSiteDir = 's1';

			it('should set siteDir to string params.siteDir', function() {
				const testSiteDir = 's99';
				return controller.prepareParams({ siteDir: testSiteDir }).then(() => {
					assert.equal(controller.siteDir, testSiteDir);
				});
			});

			it('should set siteDir to default for every non-string and empty string params.siteDir', function() {
				const testSiteDir = 99;
				return controller.prepareParams({ siteDir: testSiteDir }).then(() => {
					assert.equal(controller.siteId, defaultSiteDir);
				});
			});

			it('should set siteDir if SITE_DIR localize phrase exists', function() {
				const testLocalize = { SITE_DIR: 's99' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.siteDir, testLocalize['SITE_DIR']);
				});
			});

			it('should set siteDir to default if no params.siteDir and no localize', function() {
				return controller.prepareParams({}).then(() => {
					assert.equal(controller.siteDir, defaultSiteDir);
				});
			});
		});

		describe('languageId', function() {
			const defaultLanguageId = 'en';

			it('should set languageId to string params.languageId', function() {
				const testLanguageId = 'de';
				return controller.prepareParams({ languageId: testLanguageId }).then(() => {
					assert.equal(controller.languageId, testLanguageId);
				});
			});

			it('should set languageId to default for every non-string and empty string params.languageId', function() {
				const testLanguageId = 99;
				return controller.prepareParams({ languageId: testLanguageId }).then(() => {
					assert.equal(controller.languageId, defaultLanguageId);
				});
			});

			it('should set languageId if LANGUAGE_ID localize phrase exists', function() {
				const testLocalize = { LANGUAGE_ID: 'de' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.languageId, testLocalize['LANGUAGE_ID']);
				});
			});

			it('should set languageId to default if no params.languageId and no localize', function() {
				return controller.prepareParams({}).then(() => {
					assert.equal(controller.languageId, defaultLanguageId);
				});
			});
		});

		//describe('pull', function() {
		//	it('should set default pull instance', function() {
		//		return controller.prepareParams({}).then(() => {
		//			console.warn('window protobuf', window.protobuf);
		//			assert.equal(controller.pullInstance, PullClient);
		//		});
		//	});
		//
		//	it('should set default pull client', function() {
		//
		//		return controller.prepareParams({}).then(() => {
		//			assert.equal(controller.pullClient, Pull);
		//		});
		//	});
		//});

		//describe('rest', function() {
		//	it('should set default rest instance', function()
		//		return controller.prepareParams({}).then(() => {
		//			console.warn('controller.restInstance', controller.restInstance);
		//			assert.equal(controller.restInstance, RestClient);
		//		});
		//	});
		//
		//	it('should set default rest client', function() {
		//		return controller.prepareParams({}).then(() => {
		//			console.warn('controller.restClient', controller.restClient);
		//			assert.equal(controller.restClient, Rest);
		//		});
		//	});
		//});

		describe('vuexBuilder', function() {
			const defaultDatabaseName = 'desktop/im';
			const defaultDatabaseType = VuexBuilder.DatabaseType.indexedDb;

			it('should set default values for vuexbuilder', function() {
				return controller.prepareParams({}).then(() => {
					assert.equal(controller.vuexBuilder.database, false);
					assert.equal(controller.vuexBuilder.databaseName, defaultDatabaseName);
					assert.equal(controller.vuexBuilder.databaseType, defaultDatabaseType);
				});
			});

			it('should set database flag if params.vuexBuilder.database passed', function() {
				return controller.prepareParams({
					vuexBuilder: {
						database: true
					}
				}).then(() => {
					assert.equal(controller.vuexBuilder.database, true);
				});
			});

			it('should set databaseName if params.vuexBuilder.databaseName passed', function() {
				const testDatabaseName = 'testDatabaseName';
				return controller.prepareParams({
					vuexBuilder: {
						databaseName: testDatabaseName
					}
				}).then(() => {
					assert.equal(controller.vuexBuilder.databaseName, testDatabaseName);
				});
			});

			it('should set databaseType if params.vuexBuilder.databaseType passed', function() {
				const testDatabaseType = VuexBuilder.DatabaseType.localStorage;
				return controller.prepareParams({
					vuexBuilder: {
						databaseType: testDatabaseType
					}
				}).then(() => {
					assert.equal(controller.vuexBuilder.databaseType, testDatabaseType);
				});
			});

			it('should call this.addVuexModel for each model provided in params.vuexBuilder.models', function() {
				const testCallApplicationModel = CallApplicationModel.create();
				const testRecentModel = RecentModel.create();
				const testModels = [
					testCallApplicationModel,
					testRecentModel
				];
				sandbox.spy(controller, 'addVuexModel');
				return controller.prepareParams({
					vuexBuilder: {
						models: testModels
					}
				}).then(() => {
					assert.equal(controller.addVuexModel.calledTwice, true);
					assert.equal(controller.addVuexModel.getCall(0).args[0], testCallApplicationModel);
					assert.equal(controller.addVuexModel.getCall(1).args[0], testRecentModel);
				});
			});
		});
	});

	describe('initController', function() {
		it('should return promise', function() {
			const initControllerResult = controller.initController();
			assert.equal(initControllerResult instanceof Promise, true);
		});

		it('should initialize application controller', function() {
			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => {
					assert.equal(controller.application instanceof ApplicationController, true);
					assert.equal(controller.application.controller, controller);
				});
		});
	});

	describe('initLocalStorage', function() {
		it('should return promise', function() {
			const initLocalStorageResult = controller.initLocalStorage();
			assert.equal(initLocalStorageResult instanceof Promise, true);
		});
	});

	describe('initStorage', function() {
		const defaultModels = [ApplicationModel, MessagesModel, DialoguesModel, FilesModel, UsersModel, RecentModel];

		it('should return promise', function() {
			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => {
					const initStorageResult = controller.initStorage();
					assert.equal(initStorageResult instanceof Promise, true);
				});
		});

		it('should set application variables', function() {
			sandbox.spy(controller, 'getHost');
			sandbox.spy(controller, 'getUserId');
			sandbox.spy(controller, 'getSiteId');
			sandbox.spy(controller, 'getLanguageId');
			sandbox.spy(Utils.device, 'isMobile');
			sandbox.spy(Utils.device, 'getOrientation');

			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => {
					sandbox.spy(controller.application, 'getDefaultMessageLimit');
					return controller.initStorage()
						.then(() => {
							//common
							//getHost called 5 more times in addModel
							assert.equal(controller.getHost.callCount, 6);
							//getUserId and getSiteId called one more time in setDatabaseConfig
							assert.equal(controller.getUserId.calledTwice, true);
							assert.equal(controller.getSiteId.calledTwice, true);
							assert.equal(controller.getLanguageId.calledOnce, true);
							//dialog
							assert.equal(controller.application.getDefaultMessageLimit.calledOnce, true);
							//device
							//isMobile called twice because it is used in getOrientation
							assert.equal(Utils.device.isMobile.calledTwice, true);
							assert.equal(Utils.device.getOrientation.calledOnce, true);
						});
				});
		});

		it('should create builder with default models', function() {
			sandbox.spy(VuexBuilder.prototype, 'addModel');

			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => {
					assert.equal(VuexBuilder.prototype.addModel.callCount, defaultModels.length);
					assert.equal(VuexBuilder.prototype.addModel.getCall(0).args[0] instanceof ApplicationModel, true);
					assert.equal(VuexBuilder.prototype.addModel.getCall(1).args[0] instanceof MessagesModel, true);
					assert.equal(VuexBuilder.prototype.addModel.getCall(2).args[0] instanceof DialoguesModel, true);
					assert.equal(VuexBuilder.prototype.addModel.getCall(3).args[0] instanceof FilesModel, true);
					assert.equal(VuexBuilder.prototype.addModel.getCall(4).args[0] instanceof UsersModel, true);
					assert.equal(VuexBuilder.prototype.addModel.getCall(5).args[0] instanceof RecentModel, true);
				});
		});

		it('should add every additional model', function() {
			const testModels = [CallApplicationModel.create()];
			sandbox.spy(VuexBuilder.prototype, 'addModel');

			return controller.prepareParams({
					vuexBuilder: {
						models: testModels
					}
				})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => {
					//default models and one additional
					assert.equal(VuexBuilder.prototype.addModel.callCount, defaultModels.length + 1);
					//next call after default models should be with our additional model
					assert.equal(VuexBuilder.prototype.addModel.getCall(defaultModels.length).args[0] instanceof CallApplicationModel, true);
				});
		});

		it('should set database config', function() {
			sandbox.spy(VuexBuilder.prototype, 'setDatabaseConfig');

			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => {
					assert.equal(VuexBuilder.prototype.setDatabaseConfig.calledOnce, true);
					assert.equal(VuexBuilder.prototype.setDatabaseConfig.getCall(0).args[0].name, controller.vuexBuilder.databaseName);
					assert.equal(VuexBuilder.prototype.setDatabaseConfig.getCall(0).args[0].type, controller.vuexBuilder.databaseType);
					assert.equal(VuexBuilder.prototype.setDatabaseConfig.getCall(0).args[0].siteId, controller.getSiteId());
					assert.equal(VuexBuilder.prototype.setDatabaseConfig.getCall(0).args[0].userId, controller.getUserId());
				});
		});

		it('should build store', function() {
			sandbox.spy(VuexBuilder.prototype, 'build');

			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => {
					assert.equal(VuexBuilder.prototype.build.calledOnce, true);
					assert.equal(controller.store instanceof VuexVendor.Store, true);
					assert.equal(controller.storeBuilder instanceof VuexBuilder, true);
				});
		});
	});

	describe('initRestClient', function() {
		it('should return promise', function() {
			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => {
					const initRestClientResult = controller.initRestClient();
					assert.equal(initRestClientResult instanceof Promise, true);
				});
		});

		it('should add core rest handler', function() {
			sandbox.spy(controller, 'addRestAnswerHandler');
			sandbox.spy(CoreRestHandler, 'create');

			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => controller.initRestClient())
				.then(() => {
					assert.equal(controller.addRestAnswerHandler.calledOnce, true);
					assert.equal(CoreRestHandler.create.calledOnce, true);
					assert.equal(CoreRestHandler.create.getCall(0).args[0].store, controller.store);
					assert.equal(CoreRestHandler.create.getCall(0).args[0].controller, controller);
				});
		});
	});

	describe('initEnvironment', function() {
		it('should return promise', function() {
			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => controller.initRestClient())
				.then(() => {
					const initEnvironmentResult = controller.initEnvironment();
					assert.equal(initEnvironmentResult instanceof Promise, true);
				});
		});

		it('should set listener for orientationchange', function() {
			sandbox.spy(window, 'addEventListener');

			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => controller.initRestClient())
				.then(() => controller.initEnvironment())
				.then(() => {
					assert.equal(window.addEventListener.calledOnce, true);
					assert.equal(window.addEventListener.getCall(0).args[0], 'orientationchange');
				});
		});
	});

	describe('initComplete', function() {
		it('should set inited flag and resolve initPromise', function() {
			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => controller.initRestClient())
				.then(() => controller.initEnvironment())
				.then(() => controller.initComplete())
				.then(() => {
					assert.equal(controller.inited, true);
				});
		});
	});

	describe('createVue', function() {
		it('should return promise', function() {
			const createVueResult = controller.createVue();
			assert.equal(createVueResult instanceof Promise, true);
		});

		it('should resolve in Vue instance', function() {
			const beforeCreate = () => {
			};
			const destroyed = () => {
			};

			return controller.prepareParams({})
				.then(() => controller.initController())
				.then(() => controller.initLocalStorage())
				.then(() => controller.initStorage())
				.then(() => {
					const template = `<h1>Hello</h1>`;

					return controller.createVue({
							el: '#app',
							template,
							beforeCreate,
							destroyed
						})
						.then((result) => {
							assert.equal(result instanceof VueVendor, true);
							//default vuexInit and our beforeCreate
							assert.equal(result.$options.beforeCreate.length, 2);
							assert.equal(result.$options.destroyed.length, 1);
							assert.equal(result.$store, controller.store);
							//assert.equal(typeof result.$bitrixApplication !== 'undefined', true);
							//assert.equal(typeof result.$bitrixController !== 'undefined', true);
							//assert.equal(typeof result.$bitrixRestClient !== 'undefined', true);
							//assert.equal(typeof result.$bitrixPullClient !== 'undefined', true);
							//assert.equal(typeof result.$bitrixMessages !== 'undefined', true);
						});
				});
		});
	});

	describe('Core methods', function() {
		describe('getHost', function() {
			it('should return current host', function() {
				const defaultHost = 'https://example.org';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.getHost(), defaultHost);
					});
			});
		});

		describe('setHost', function() {
			it('should set host', function() {
				const testHost = 'https://google.ru';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setHost(testHost);
						assert.equal(controller.getHost(), testHost);
						assert.equal(controller.store.state.application.common.host, testHost);
					});
			});
		});

		describe('getUserId', function() {
			it('should return current user id', function() {
				const defaultUserId = 0;

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.getUserId(), defaultUserId);
					});
			});
		});
		describe('setUserId', function() {
			it('should set user id for number', function() {
				const testUserId = 99;

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setUserId(testUserId);
						assert.equal(controller.getUserId(), testUserId);
						assert.equal(controller.store.state.application.common.userId, testUserId);
					});
			});

			it('should set user id for parsable string', function() {
				const testUserId = '99';
				const parsedTestUserId = 99;

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setUserId(testUserId);
						assert.equal(controller.getUserId(), 99);
					});
			});

			it('should set user id to default for non-strings and non-parsable strings', function() {
				const defaultUserId = 0;
				const testUserId = 'abc';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setUserId(testUserId);
						assert.equal(controller.getUserId(), defaultUserId);
					});
			});
		});

		describe('getSiteId', function() {
			it('should return current site id', function() {
				const defaultSiteId = 's1';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.getSiteId(), defaultSiteId);
					});
			});
		});

		describe('setSiteId', function() {
			it('should set site id for non-empty strings', function() {
				const testSiteId = 's99';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setSiteId(testSiteId);
						assert.equal(controller.getSiteId(), testSiteId);
						assert.equal(controller.store.state.application.common.siteId, testSiteId);
					});
			});

			it('should set site id to default for non-string and empty strings', function() {
				const defaultSiteId = 's1';
				const testSiteId = '';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setSiteId(testSiteId);
						assert.equal(controller.getSiteId(), defaultSiteId);
						assert.equal(controller.store.state.application.common.siteId, defaultSiteId);
					});
			});
		});

		describe('getLanguageId', function() {
			it('should return current language id', function() {
				const defaultLanguageId = 'en';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.getLanguageId(), defaultLanguageId);
					});
			});
		});

		describe('setLanguageId', function() {
			it('should set language id for non-empty strings', function() {
				const testLanguageId = 'de';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setLanguageId(testLanguageId);
						assert.equal(controller.getLanguageId(), testLanguageId);
						assert.equal(controller.store.state.application.common.languageId, testLanguageId);
					});
			});

			it('should set language id to default for non-string and empty strings', function() {
				const defaultLanguageId = 'en';
				const testLanguageId = '';

				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						controller.setSiteId(testLanguageId);
						assert.equal(controller.getLanguageId(), defaultLanguageId);
						assert.equal(controller.store.state.application.common.languageId, defaultLanguageId);
					});
			});
		});

		describe('getStore', function() {
			it('should return store', function() {
				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.getStore(), controller.store);
					});
			});
		});

		describe('getStoreBuilder', function() {
			it('should return store builder', function() {
				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.getStoreBuilder(), controller.storeBuilder);
					});
			});
		});

		describe('addRestAnswerHandler', function() {
			it('should add item to restAnswerhandler array', function() {
				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.restAnswerHandler.length, 1);
						controller.addRestAnswerHandler({ test });
						assert.equal(controller.restAnswerHandler.length, 2);
					});
			});
		});

		describe('addVuexModel', function() {
			it('should add item to vuexAdditional model array', function() {
				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						assert.equal(controller.vuexAdditionalModel.length, 0);
						controller.addVuexModel({ test });
						assert.equal(controller.vuexAdditionalModel.length, 1);
					});
			});
		});

		describe('ready', function() {
			it('should return promise', function() {
				const readyResult = controller.ready();
				assert.equal(readyResult instanceof Promise, true);
			});

			it('should resolve into controller', function() {
				return controller.prepareParams({})
					.then(() => controller.initController())
					.then(() => controller.initLocalStorage())
					.then(() => controller.initStorage())
					.then(() => controller.initRestClient())
					.then(() => controller.initEnvironment())
					.then(() => controller.initComplete())
					.then(() => {
						return controller.ready().then((result) => {
							assert.equal(result, controller);
						});
					});
			});
		});

		describe('getLocalize', function() {
			it('should return whole localize object if no param passed', function() {
				const testLocalize = { 'PHRASE1': 'TEST1' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.getLocalize(), testLocalize);
				});
			});

			it('should return matched phrase', function() {
				const testLocalize = { 'PHRASE1': 'TEST1', 'PHRASE2': 'TEST2' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.getLocalize('PHRASE1'), testLocalize['PHRASE1']);
				});
			});

			it('should return empty string if no phrase found', function() {
				const testLocalize = { 'PHRASE1': 'TEST1', 'PHRASE2': 'TEST2' };

				return controller.prepareParams({ localize: testLocalize }).then(() => {
					assert.equal(controller.getLocalize('PHRASE3'), '');
				});
			});
		});
	});
});