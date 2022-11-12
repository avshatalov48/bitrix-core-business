import {Uri} from 'main.core';

const Options = {
	maskSize: 400,
	imageSize: 1024,

	rawSrc: document.currentScript.src,
	rawPath: null,
	eventNamespace: 'Main.Avatar.Editor',

	get path(): String {
		if (Options.rawPath === null)
		{
			const res = Options.rawSrc.split('/');
			let buf;
			while (buf = res.pop())
			{
				if (buf === 'dist')
				{
					break;
				}
			}
			Options.rawPath = (new Uri(res.join('/'))).getPath();
		}
		return Options.rawPath;
	},
	getCollections: () => {
		const settings = Extension.getSettings('ui.avatar-editor');
		return Array.from(settings['commonCollection'])
			[
			{
				title: 'Sys',
				items:[
					'001_flower.png',
					'002_flower.png',
					'003_christmas_tree256.png',
					'005_red_rectangle.png',
					'005_blue_circle.png',
					'004_bow_purple.png',
				]
				.map(function(title)
				{
					return {
						id: title,
						title: title,
						thumb: [Options.path, 'badges', title].join('/').replace('//', '/'),
						src: [Options.path, 'badges', title].join('/').replace('//', '/'),
					}
				})
			}
		];
	}
};

export {Options};