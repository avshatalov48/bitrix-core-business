import {Type} from 'main.core';

export default function adjustTransformOptions(transform)
{
	if (Type.isPlainObject(transform))
	{
		const {categories} = transform;

		if (Type.isArray(categories))
		{
			categories.forEach(({ratios}) => {
				if (Type.isArray(ratios))
				{
					ratios.forEach((ratio) => {
						if (
							BX.type.isPlainObject(ratio)
							&& BX.type.isPlainObject(ratio.dimensions)
						)
						{
							ratio.dimensions = (
								new window.PhotoEditorSDK.Math.Vector2(
									ratio.dimensions.width,
									ratio.dimensions.height,
								)
							);
						}
					});
				}
			});
		}
	}

	return transform;
}