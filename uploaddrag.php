<!doctype html>
<html lang="en">
<?php echo php_ini_loaded_file(); ?>
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex, nofollow">
  <title>Uploading Dropped and Pasted Images</title>
  <!--<script src="https://cdn.ckeditor.com/4.11.3/standard-all/ckeditor.js"></script>-->
  <!--<script src="js/ckeditor_4.11.3_full_easyimage/ckeditor.js"></script>-->
  <script src="js/ckeditor/ckeditor.js"></script>
</head>

<body>
  <!--
  <textarea cols="10" id="editor2" name="editor2" rows="10" data-sample-short>&lt;p&gt;This is some &lt;strong&gt;sample text&lt;/strong&gt;. You are using &lt;a href=&quot;https://ckeditor.com/&quot;&gt;CKEditor&lt;/a&gt;.&lt;/p&gt;</textarea>
  -->
  <textarea cols="10" id="editor1" name="editor1" rows="10" data-sample-short></textarea>
  <input type="button" value="儲存內容" onclick="console.log(CKEDITOR.instances['editor1'].getData());">
  <script>
    CKEDITOR.replace('editor1', {
      //extraPlugins: 'uploadimage,image2',
	  height: 300,
	  //customConfig: 'ckeditor_config.js',
	  //toolbarLocation: 'bottom',
	  //simpleuploads_acceptedExtensions : 'pdf',
	  
	  
	  /*
	  toolbarGroups: [{
          "name": "basicstyles",
          "groups": ["basicstyles"]
        },
        {
          "name": "links",
          "groups": ["links"]
        },
        {
          "name": "paragraph",
          "groups": ["list", "blocks"]
        },
        {
          "name": "document",
          "groups": ["mode"]
        },
        {
          "name": "insert",
          "groups": ["insert"]
        },
        {
          "name": "styles",
          "groups": ["styles"]
        },
        {
          "name": "about",
          "groups": ["about"]
        }
      ],
      // Remove the redundant buttons from toolbar groups defined above.
      //removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar',
      */
      // Upload images to a CKFinder connector (note that the response type is set to JSON).
      //uploadUrl: '/apps/ckfinder/3.4.5/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json',
      uploadUrl: 'http://192.168.16.118/uploadckeditor.php',
	  
      // Configure your file manager integration. This example uses CKFinder 3 for PHP.
	  /*
      filebrowserBrowseUrl: '/apps/ckfinder/3.4.5/ckfinder.html',
      filebrowserImageBrowseUrl: '/apps/ckfinder/3.4.5/ckfinder.html?type=Images',
      filebrowserUploadUrl: '/apps/ckfinder/3.4.5/core/connector/php/connector.php?command=QuickUpload&type=Files',
      filebrowserImageUploadUrl: '/apps/ckfinder/3.4.5/core/connector/php/connector.php?command=QuickUpload&type=Images',
	  */
	  
	  filebrowserUploadUrl: 'http://192.168.16.118/uploadckeditor.php',
	  filebrowserBrowseUrl: 'http://192.168.16.118/uploadckeditor.php',
	  filebrowserBrowseUrl: '',
      filebrowserImageUploadUrl: 'http://192.168.16.118/uploadckeditor.php',
      filebrowserFlashUploadUrl: 'http://192.168.16.118/uploadckeditor.php',

      // The following options are not necessary and are used here for presentation purposes only.
      // They configure the Styles drop-down list and widgets to use classes.
      /*
      stylesSet: [{
          name: 'Narrow image',
          type: 'widget',
          widget: 'image',
          attributes: {
            'class': 'image-narrow'
          }
        },
        {
          name: 'Wide image',
          type: 'widget',
          widget: 'image',
          attributes: {
            'class': 'image-wide'
          }
        }
      ],
      */
      // Load the default contents.css file plus customizations for this sample.
      //contentsCss: [
        //'http://cdn.ckeditor.com/4.11.3/full-all/contents.css',
        //'assets/css/widgetstyles.css'
      //],

      // Configure the Enhanced Image plugin to use classes instead of styles and to disable the
      // resizer (because image size is controlled by widget styles or the image takes maximum
      // 100% of the editor width).
      //image2_alignClasses: ['image-align-left', 'image-align-center', 'image-align-right'],
      //image2_disableResizer: true
	  
	  /*
	  //可以加CLASS NAME IN IMAGE TAG
	  on: {
        instanceReady: function() {
            //this.attributes[ 'class' ]
			//this.htmlParser.element.addClass('test');
			
			this.dataProcessor.htmlFilter.addRules( {
                elements: {
                    img: function( el ) {
                        // Add an attribute.
                        if ( !el.attributes.alt )
                            el.attributes.alt = 'An image';

                        // Add some class.
                        el.addClass( 'newsleft' );
                    }
                }
            } );     
            			
        }
    }
	  
	  */
	  
	  /*
	   on: {
        contentDom: function () { //editor content ready
            var myEditor = this;
            //add listener
            this.editable().attachListener(editor1, 'key', function( evt ) {
                //if delete or backspace pressed
                if ( ( evt.data.keyCode in { 8: 1, 46: 1 } ) ) {
                    //get the last element
                    var lastElement = myEditor.elementPath().lastElement,
                        lastElementName = lastElement.getName(),
                        lastElementNode = lastElement.$; //native DOM object
                        //see what properties the node has
                        console.log(lastElementNode);
                        //you can use getAttribute to fetch specific attr
                        //for example, for img element's src attribute
                        console.log(lastElementNode.getAttribute("src"));

                }
            });
        }
      }
	  */
	  
	  
    });
	
	
	/*
	CKEDITOR.on( 'instanceReady', function() {
      //var headTxt = $(".cke_dialog_title").text();
      //console.log("Browe Type :"+headTxt);
	  console.log('test->instanceReady');
      //if( headTxt =="Flash Properties" || headTxt =="Image Properties") {
        //$(".cke_dialog_title").parent().find(".cke_dialog_ui_button").hide();
      //}
    } );
    */
	//目前開啟的PLUGIN DIALOG為?
	//CKEDITOR.on( 'dialogDefinition', function( ev ) {
    // Take the dialog name and its definition from the event data.
    //var dialogName = ev.data.name;
    //var dialogDefinition = ev.data.definition;
    //console.log('dialogName:'+dialogName);
	//console.log('dialogDefinition:'+dialogDefinition.getContents( 'info' ).get('url'));
	/*
    // Check if the definition is from the dialog window you are interested in (the "Link" dialog window).
    if ( dialogName == 'link' ) {
        // Get a reference to the "Link Info" tab.
        var infoTab = dialogDefinition.getContents( 'info' );

        // Set the default value for the URL field.
        var urlField = infoTab.get( 'url' );
        urlField[ 'default' ] = 'www.example.com';
    }
	*/
	//dialogDefinition.onShow = function() {
	  //console.log("getPageCount():"+this.getPageCount());
	  //console.log(isTabEnabled( 'editor1', 'image', 'Upload' ));

	  
      // This code will open the Link tab.
      //this.selectPage( 'Link' );
	  //if(dialogDefinition.getContents( 'selectPage' )!= null){
	    //this.selectPage('Upload'); //先SHOW上傳頁
	  //}
	  //}
    //};
//});
	
	CKEDITOR.on('dialogDefinition', function (ev) {
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;

    if (dialogName == 'image') {

	    //可以加CLASS NAME開始
	   	// Get a reference to the "Advanced" tab.
        var advanced = dialogDefinition.getContents('advanced');

        // Set the default value CSS class       
        var styles = advanced.get('txtGenClass'); 
        styles['default'] = 'newsleft';
	    //可以加CLASS NAME結束
	
        var oldOnShow = dialogDefinition.onShow;
        var newOnShow = function () {
            //this.selectPage('Upload');
            //this.hidePage('Link');

            // change tabs order
            //$('a[title=Upload].cke_dialog_tab').css('float', 'left');
			/*
			this.dataProcessor.htmlFilter.addRules( {
                elements: {
                    img: function( el ) {
                        // Add an attribute.
                        if ( !el.attributes.alt )
                            el.attributes.alt = 'An image';

                        // Add some class.
                        el.addClass( 'newsleft' );
                    }
                }
            } );     
            
			*/
			
		
			
			
        };

        dialogDefinition.onShow = function () {
            oldOnShow.call(this, arguments);
            newOnShow.call(this, arguments);
        };
    }
});
	
  </script>
</body>

</html>