self.addEventListener("message", function(event) {
	var result;

	try {
		result = JSON.stringify(event.data);
	} catch (err) {
		result = "";
	}

	postMessage(result);
	close();
});