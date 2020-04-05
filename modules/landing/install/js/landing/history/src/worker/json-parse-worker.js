self.addEventListener("message", function(event) {
	var result;

	try {
		result = JSON.parse(event.data);
	} catch (err) {
		result = null;
	}

	postMessage(result);
});