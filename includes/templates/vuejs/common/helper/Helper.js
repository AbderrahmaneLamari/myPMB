
class Helper {
	
	camelize(string) {
	    return string.toLowerCase().replace(/[^a-zA-Z0-9]+(.)/g, function(match, chr) {
	        return chr.toUpperCase();
	    });
	}
	
	cloneObject(obj) {
		if (obj instanceof Array) {
			let clone = new Array();
			for (let index in obj) {
				clone[index] = this.cloneObject(obj[index]);
			}	
			return clone;
		} else if (obj instanceof Object) {
			// On clone object on fait en sorte d'avoir les getter/setter
			let clone = {...obj};
			let descriptors = Object.getOwnPropertyDescriptors(clone);
			Object.defineProperties(clone, descriptors);
			return clone;
		}
		return obj; 
	}
	
}

export default new Helper();