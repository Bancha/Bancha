beforeEach(function() {
	
	/**
	 * Safely finds an object, used internally for getStubsNamespace and getRemoteApi
	 * (This function is tested in RS.util, not part of the package testing, but it is tested)
	 * @param {String} path A period ('.') separated path to the desired object (String).
	 * @param {String} lookIn optional: The object on which to perform the lookup.
	 * @return {Object} The object if found, otherwise undefined.
	 * @member Bancha
	 * @method objectFromPath
	 * @private
	 */
	var objectFromPath = function(path, lookIn) {
	    if (!lookIn) {
	        //get the global object so it don't use hasOwnProperty on window (IE incompatible)
	        var first = path.indexOf('.'),
	            globalObjName,
	            globalObj;
	        if (first === -1) {
	            // the whole path is only one object so eturn the result
	            return window[path];
	        }
	        // else the first part as global object name
	        globalObjName = path.slice(0, first);
	        globalObj = window[globalObjName];
	        if (typeof globalObj === 'undefined') {
	            // path seems to be false
	            return undefined;
	        }
	        // set the ne lookIn and the path
	        lookIn = globalObj;
	        path = path.slice(first + 1);
	    }
	    // get the object
	    return path.split('.').reduce(function(o, p) {
	        if(o && o.hasOwnProperty(p)) {
	            return o[p];
	        }
	    }, lookIn);
	};
	
  this.addMatchers({
    toBeAFunction: function() {
      return (typeof this.actual === 'function');
    },
	// TODO neuer helper
	/* property: function(/*string*path) {
		var property = objectFromPath(path,this.actual);
		
		// enable all matcher function for this property
		this.actual = expect(property).actual;
		
		// if the property exists this is true
		return property!==null;
	}*/
	hasProperty: function(path) {
		return objectFromPath(path,this.actual)!==null;
	}
  })
});