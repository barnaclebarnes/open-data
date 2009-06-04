jQuery(document).ready(function() {

	jQuery('a').each(function() {
		var a = jQuery(this);
		var href = a.attr('href');
		
		// Check if the a tag has a href, if not, stop for the current link
		if ( href == undefined )
			return;
		
		var url = href.replace('http://','').replace('https://','');
		var hrefArray = href.split('.').reverse();
		var extension = hrefArray[0];
 
	 	// If the link is external
	 	if ( ( href.match(/^http/) ) && ( !href.match(document.domain) ) ) {
	    	// Add the tracking code
			a.click(function() {
				pageTracker._trackPageview(outboundPrefix + url);
			});
		}
	
	 	// If the link is a download
		if (jQuery.inArray(extension,fileTypes) != -1) {
			// Add the tracking code
			a.click(function() {
				pageTracker._trackPageview(downloadsPrefix + url);
			});
		}
	});

});