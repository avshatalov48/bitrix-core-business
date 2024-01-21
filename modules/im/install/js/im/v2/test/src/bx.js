import BX from '../../../../../../../main/install/js/main/core/test/old/core/internal/bootstrap';

BX.message({
	USER_ID: 1,
	SITE_ID: 's1',
	SITE_DIR: '',
	LANGUAGE_ID: 'ru',
});

BX.PULL = {
	subscribe: () => {},
};
BX.Messenger = {
	Embedding: {
		Application: {},
		Lib: {},
		Const: {},
	},
};

global.BX = BX;
global.window.BX = BX;
