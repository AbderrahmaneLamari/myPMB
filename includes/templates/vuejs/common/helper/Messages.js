
class Messages {

	get(js, code) {
		if (typeof code != "string") {
			console.error(`[Messages] invalide code !`);
			return "Error";
		}

		if (code.slice(0, 4) == "msg:") {
			code = code.slice(4);
		}

		const message = pmbDojo.messages.getMessage(js, code);
		return ("" != message) ? message : code;
	}

}

export default new Messages();