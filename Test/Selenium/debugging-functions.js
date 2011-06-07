
	// Error catching
	Ext.Error.handle = function() { return true; };
    
	//for development display all events in the log
	Ext.util.Observable.capture(Ext.Direct, window.logger.info);

	// set global error handling
	Ext.Direct.on({
	    'exception': function(e) {
	        var title;
	        if(e.code==='xhr')       { title=RS.t('Connection Error'); logger.error(e.xhr); } 
	        if(e.code==='parse')     { title=RS.t('Parse Error'); logger.error(e.parse); }
	        if(e.code==='login')     { title=RS.t('Login Error'); logger.error(e.login); }
	        if(e.code==='exception') { title=RS.t('Server error'); logger.error(e.exception); }
	        RS.error(title, e.message);
	    }
	});