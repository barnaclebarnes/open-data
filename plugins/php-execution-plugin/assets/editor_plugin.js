/**
 * WP PHP Execution plugin.
 *
 * Copyright 2009 N. Zeh (http://www.zehnet.de/)
 */
 
/*
 * Using tinymce's "onBeforeSetContent" and "onPostProcess" does not work
 * to parse between real content and editor html
 * as Wordpress alters the html content after all these callbacks have been called.
 * So we need a new callback mechanism which jumps in when Wordpress' formatting has finished.
 *
 * The wpTinyMceEditor object adds callback functionality to wordpress' "pre_autop" and "autop" functions
 * inside the "switchEditors" object [wp-admin/js/editor.js] so you can work on html code prior resp. after 
 * Wordpress has done all its formatting (autop).
 */
if(typeof(wpTinyMceEditor) == 'undefined')
 {
	wpTinyMceEditor = {
		 
		 pres:[],
		 posts:[],
		 inited:false,
		 numCalls:0,

		 /*
		  * replace "pre_wpautop" and "wpautop" methods
		  * in WordPress' "switchEditors" object
		  * with new methods offering callback functionality.
		  */
		 init : function()
		 {
			 if(!this.inited)
			 {
				 switchEditors.___pre_wpautop = switchEditors.pre_wpautop;
				 switchEditors.pre_wpautop = this.pre_wpautop;
				 
				 switchEditors.___wpautop = switchEditors.wpautop;
				 switchEditors.wpautop = this.wpautop;
				 
				 this.inited = true;
			 }
		 },
		 
		 /*
		  * editor html => post content html
		  * => callbacks come in last
		  *
		  * somehow "pre_wpautop" is called twice 
		  * (dont know why and not even trying to figure it out)
		  * we really only need it once 
		  * so we integrate a checking variable "numCalls"
		 */
		 pre_wpautop : function(content)
		 {
			if(wpTinyMceEditor.numCalls==0)
			{
				content = switchEditors.___pre_wpautop(content);
				
				for(var i=0, el=wpTinyMceEditor.posts, n=el.length; i<n; i++)
				{
					content = el[i](content);
				}
				
				wpTinyMceEditor.numCalls++;	
			}
			return content;
		 },

		 /*
		  * post content html => editor html
		  * => callbacks come in first
		  */
		 wpautop : function(content)
		 {
			wpTinyMceEditor.numCalls = 0;

			for(var i=0, el=wpTinyMceEditor.pres, n=el.length; i<n; i++)
			{
				content = el[i](content);
			}
			
			return switchEditors.___wpautop(content);
		 },

		/**
		 * register a callback function
		 * called when post content is prepared for the tinyMce richTextEditor
		*/
		 onSetEditorContent : function(func)
		 {
			 this.pres.push(func);
		 },

		/**
		 * register a callback function
		 * called when html data from the tinyMce richTextEditor is prepared to be saved as post content
		*/
		 onSetPostContent : function(func)
		 {
			 this.posts.push(func);
		 }
		 
	};
 
 }
 
/**
 * phpExecution
 */
phpExecution = {
	
	// Fix: no hard coded relative url anymore, but a reference to a variable globaly set with "admin_head" callback
	baseUrl: phpExecutionBaseUrl,
	
	/**
	 * initializer
	 *
	 * Is called domready as the init method is used by tinymce (see below)
	*/
	domready : function()
	{
		// Register callBacks on WordPress's formatting functions
		wpTinyMceEditor.onSetEditorContent(this.setEditorContent);
		
		wpTinyMceEditor.onSetPostContent(this.setPostContent);
		
		// create tinymce plugin
		tinymce.create('tinymce.plugins.phpExecution', this);
		
		// Register tinymce plugin
		tinymce.PluginManager.add('phpExecution', tinymce.plugins.phpExecution);
	},	

	/**
	 * the init method is called by tinymce on its initialization
	*/
	init : function(ed, url)
	{
		var t = this;
		this.baseUrl = url;
		
		ed.onInit.add(function()
		{
			ed.dom.loadCSS(url + '/editor_plugin.css');
		});
		
		// Display php tag instead if img in element path
		ed.onPostRender.add(function()
		{
			if (ed.theme.onResolveName)
			{
				ed.theme.onResolveName.add(function(th, o)
				{
					if (o.node.nodeName == 'IMG' && ed.dom.hasClass(o.node, 'mceWpPHP') )
					{
						o.name = 'php';
					}
				});
			}
		});
		
	},

	/**
	 *
	*/
	setEditorContent : function(content)
	{
		// the . does not match mewlines and there is no pattern modifier to change this
		// => instead of . we use the character class [\s\S]
		return content.replace(
			/<\?php([\s\S]*?)\?>/gi, 
			function(im)
			{
				return '<img src="' + phpExecution.baseUrl + '/assets/trans.gif" class="mceWpPHP mceItemNoResize" title="php" alt="' + phpExecution.base64encode(im) + '" />';
			}
		);
	},

	/**
	 *
	*/
	setPostContent : function(content)
	{
		return content.replace(
			/<img[^>]+class="mceWpPHP[^>]+>/g, 
			function(im)
			{
				var data = (m = im.match(/alt="(.*?)"/)) ? m[1] : '';
				return phpExecution.base64decode(data);
			}
		);
	},
	
	/**
	 * base64 chars
	*/
	keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	
	/**
	 *
	*/
	base64encode : function(input)
	{
	   var output = "";
	   var chr1, chr2, chr3;
	   var enc1, enc2, enc3, enc4;
	   var i = 0;
	
	   do
	   {
		  chr1 = input.charCodeAt(i++);
		  chr2 = input.charCodeAt(i++);
		  chr3 = input.charCodeAt(i++);
	
		  enc1 = chr1 >> 2;
		  enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		  enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		  enc4 = chr3 & 63;
	
		  if (isNaN(chr2))
		  {
			 enc3 = enc4 = 64;
		  }
		  else if (isNaN(chr3))
		  {
			 enc4 = 64;
		  }
	
		  output += this.keyStr.charAt(enc1) 
					+ this.keyStr.charAt(enc2) 
					+ this.keyStr.charAt(enc3) 
					+ this.keyStr.charAt(enc4);
					
	   } while (i < input.length);
	   
	   return output;			
	},
	
	/**
	 *
	*/
	base64decode : function(input)
	{
	   var output = "";
	   var chr1, chr2, chr3;
	   var enc1, enc2, enc3, enc4;
	   var i = 0;
	
	   // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	   input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
	
	   do
	   {
		  enc1 = this.keyStr.indexOf(input.charAt(i++));
		  enc2 = this.keyStr.indexOf(input.charAt(i++));
		  enc3 = this.keyStr.indexOf(input.charAt(i++));
		  enc4 = this.keyStr.indexOf(input.charAt(i++));
	
		  chr1 = (enc1 << 2) | (enc2 >> 4);
		  chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		  chr3 = ((enc3 & 3) << 6) | enc4;
	
		  output += String.fromCharCode(chr1);
	
		  if (enc3 != 64)
		  {
			 output += String.fromCharCode(chr2);
		  }
		  if (enc4 != 64)
		  {
			 output += String.fromCharCode(chr3);
		  }
		  
	   } while (i < input.length);
	
	   return output;			
	}

};
 
/**
 * jQuery initializer
*/
(function($)
{
	$(document).ready(function()
	{
		wpTinyMceEditor.init();
		phpExecution.domready();
	});
})(jQuery);