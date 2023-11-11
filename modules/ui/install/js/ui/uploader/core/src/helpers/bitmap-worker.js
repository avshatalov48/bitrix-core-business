const BitmapWorker = function() {
	self.onmessage = (event: MessageEvent) => {
		// Hack for Safari. Workers can become unpredictable.
		// Sometimes 'self.postMessage' doesn't emit 'onmessage' event.
		setTimeout(() => {
			createImageBitmap(event.data.message.file)
				.then((bitmap: ImageBitmap) => {
					self.postMessage({ id: event?.data?.id, message: bitmap }, [bitmap]);
				})
				.catch(() => {
					self.postMessage({ id: event.data.id, message: null }, []);
				})
			;
		}, 0);
	};
};

export default BitmapWorker;
