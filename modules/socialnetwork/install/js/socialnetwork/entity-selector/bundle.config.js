module.exports = {
	input: 'src/index.js',
	output: 'dist/sonet-entity-selector.bundle.js',
	concat: {
		css: ['src/style.css']
	},
	namespace: 'BX.SocialNetwork.EntitySelector',
	adjustConfigPhp: false
};
