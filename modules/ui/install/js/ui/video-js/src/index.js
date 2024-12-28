import { Reflection } from 'main.core';

const namespace = Reflection.namespace('BX.UI.VideoJs');
const videojs = window.videojs;

namespace.videojs = videojs;

// export * from 'video.js';

export {
	videojs,
};
