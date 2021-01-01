import 'main.imageeditor';
import buildOptions from './internal/build.options';
import getFilename from './internal/get.filename';

/**
 * @memberOf BX.Landing
 */
export class ImageEditor
{
	static edit(options: {image: string, dimensions: {width: number, height: number}})
	{
		const imageEditor = BX.Main.ImageEditor.getInstance();
		const preparedOptions = buildOptions(options);

		return imageEditor
			.edit(preparedOptions)
			.then((file) => {
				file.name = decodeURIComponent(getFilename(options.image));
				return file;
			});
	}
}