const BitmapWorker = function() {
	self.onmessage = event => {
		createImageBitmap(event.data.message.file)
			.then(bitmap => {
				self.postMessage({ id: event.data.id, message: bitmap }, [bitmap]);
			})
			.catch(() => {
				self.postMessage({ id: event.data.id, message: null }, []);
			})
		;
	};
};

export default BitmapWorker;