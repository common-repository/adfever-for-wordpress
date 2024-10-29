(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('adfever');

	tinymce.create('tinymce.plugins.adfever', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			if ( typeof AdfeverButtonClick == 'undefined' ) return;

			ed.addButton('adfever', {
				title : 'adfever.button_title',
				image : url + '/../../images/adfever.png',
				onclick : function() {
					AdfeverButtonClick( 'adfever' );
				}
			});
			
			/*
		     * Load additional CSS
		     */
		   ed.onInit.add(function() {
		        if (ed.settings.content_css !== false)
		        {
		          dom = ed.windowManager.createInstance('tinymce.dom.DOMUtils', document);
		          /*
		           * Load first for the viewport
		           * And then, inside the RTE frame
		           */
		          dom.loadCSS(url + '/content.css');
		          ed.dom.loadCSS(url + '/content.css');
		        }
		      });
		      
		      /*
		       * From Editor to database (Visual to HTML tab)
		       * 
		       * We basically remove span element
		       */
		      ed.onBeforeGetContent.add(function(ed, o){
		        tinymce.each(ed.getBody().getElementsByTagName('span'), function(tag){
		          if (ed.dom.hasClass(tag, 'adf_shortcode'))
		          {
		            tag.innerHTML = tag.innerHTML.replace(/<\/?[^>]*>/g, '');
		          }
		        });
		      });
		      
		      /*
		       * From Editor to database (Visual to HTML tab)
		       * 
		       * We basically remove span element
		       */
		      ed.onPostProcess.add(function(ed, o){
		        o.content = o.content.replace(
		          /<span class="adf_shortcode [^>]+>([^<>]+)<\/span>/g,
		          function (text, shortcode)
		          {
		            return shortcode;
		          }
		        );
		      });
		      
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : "adfever",
				author : 'Amaury BALMER',
				authorurl : 'http://www.beapi.fr/',
				infourl : 'http://www.beapi.fr/',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('adfever', tinymce.plugins.adfever);
})();