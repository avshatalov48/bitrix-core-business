const assetPath = '/bitrix/js/main/imageeditor/external/photoeditorsdk/assets';
const landingAssetsPath = '/bitrix/images/landing/imageeditor/assets';

export default function pathResolver(path)
{
	if (path.includes('sites_recommended_transform_default'))
	{
		const [, fileName] = path.split('sites_recommended_transform_default');
		return `${assetPath}/ui/desktop/editor/controls/transform/ratios/imgly_transform_common_4-3${fileName}`;
	}

	if (path.includes('sites_recommended_transform_retina'))
	{
		const [, fileName] = path.split('sites_recommended_transform_retina');
		return `${assetPath}/ui/desktop/editor/controls/transform/ratios/imgly_transform_common_4-3${fileName}`;
	}

	if (path.includes('landing-transform-3-4'))
	{
		const [, fileName] = path.split('landing-transform-3-4');
		return `${landingAssetsPath}/transform/ratios/landing-transform-3-4${fileName}`;
	}

	if (path.includes('landing-transform-4-3'))
	{
		const [, fileName] = path.split('landing-transform-4-3');
		return `${landingAssetsPath}/transform/ratios/landing-transform-4-3${fileName}`;
	}

	if (path.includes('landing-transform-9-16'))
	{
		const [, fileName] = path.split('landing-transform-9-16');
		return `${landingAssetsPath}/transform/ratios/landing-transform-9-16${fileName}`;
	}

	if (path.includes('landing-transform-16-9'))
	{
		const [, fileName] = path.split('landing-transform-16-9');
		return `${landingAssetsPath}/transform/ratios/landing-transform-16-9${fileName}`;
	}

	if (path.includes('landing-transform-1-1'))
	{
		const [, fileName] = path.split('landing-transform-1-1');
		return `${landingAssetsPath}/transform/ratios/landing-transform-1-1${fileName}`;
	}

	if (path.includes('landing-transform-custom'))
	{
		const [, fileName] = path.split('landing-transform-custom');
		return `${landingAssetsPath}/transform/ratios/landing-transform-custom${fileName}`;
	}

	return path;
}