const useSafeNamespaces = require('../../../../build-tools/use-safe-namespaces');

module.exports = {
	input: 'src/vote.js',
	output: 'dist/vote.bundle.js',
	namespace: 'BX.Messenger.v2.Component.Message',
	browserslist: true,
	plugins: {
		custom: [
			useSafeNamespaces(),
		],
	},
};
