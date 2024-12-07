import type UploaderFile from '../uploader-file';

const isVideo = (file: UploaderFile) => {
	return /^video\/[\d.a-z-]+$/i.test(file.getType()) || file.getExtension() === 'mkv';
};

export default isVideo;
