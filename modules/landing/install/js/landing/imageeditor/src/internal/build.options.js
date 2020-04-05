import {Loc, Type} from 'main.core';
import pathResolver from './path.resolver';
import getMimeType from './get.mime.type';

const proxyPath = '/bitrix/tools/landing/proxy.php';
const isValidDimensions = ({width, height} = {}) => {
	return Type.isNumber(width) && Type.isNumber(height);
};

export default function buildOptions({image, dimensions} = {})
{
	const preparedDimensions = {
		width: dimensions.width || dimensions.maxWidth || dimensions.minWidth,
		height: dimensions.height || dimensions.maxHeight || dimensions.minHeight,
	};

	return {
		image,
		megapixels: 100,
		proxy: proxyPath,
		defaultControl: 'transform',
		assets: {
			resolver: pathResolver,
		},
		export: {
			format: getMimeType(image),
			type: BX.Main.ImageEditor.renderType.BLOB,
			quality: 1,
		},
		controlsOptions: {
			transform: {
				categories: [
					{
						identifier: 'sites_recommended',
						defaultName: Loc.getMessage('LANDING_IMAGE_EDITOR_RECOMMENDED_RATIOS'),
						ratios: [
							{
								identifier: 'sites_recommended_transform_retina',
								defaultName: Loc.getMessage('LANDING_IMAGE_EDITOR_TRANSFORM_DEFAULT'),
								ratio: (() => {
									if (isValidDimensions(preparedDimensions))
									{
										return preparedDimensions.width / preparedDimensions.height;
									}

									return undefined;
								})(),
							},
							{
								identifier: 'landing-transform-custom',
								defaultName: Loc.getMessage('IMAGE_EDITOR_RATIOS_CUSTOM'),
								ratio: '*',
							},
						],
					},
					{
						identifier: 'sites_other',
						defaultName: Loc.getMessage('LANDING_IMAGE_EDITOR_OTHER_RATIOS'),
						ratios: [
							{
								identifier: 'landing-transform-1-1',
								defaultName: '1:1',
								ratio: 1,
							},
							{
								identifier: 'landing-transform-3-4',
								defaultName: '3:4',
								ratio: 3 / 4,
							},
							{
								identifier: 'landing-transform-4-3',
								defaultName: '4:3',
								ratio: 4 / 3,
							},
							{
								identifier: 'landing-transform-9-16',
								defaultName: '9:16',
								ratio: 9 / 16,
							},
							{
								identifier: 'landing-transform-16-9',
								defaultName: '16:9',
								ratio: 16 / 9,
							},
						],
					},
				],
				replaceCategories: false,
				availableRatios: [
					'sites_recommended_transform_default',
					'sites_recommended_transform_retina',
					'landing-transform-3-4',
					'landing-transform-4-3',
					'landing-transform-9-16',
					'landing-transform-16-9',
					'landing-transform-1-1',
					'landing-transform-custom',
				],
			},
		},
	};
}