<?php

include_once 'includes/db.class.php';



session_start();
if (!isset($_SESSION["UserId"]) || is_null($_SESSION["UserId"])) {
	//header("Location: login.php");
	//die();
}

  $db = new Db();

//$userName=$db->getUserName($_SESSION["UserId"]);

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Bal - Email Editor</title>
    <meta name="description" lang="en" content="Bal – Email Newsletter Builder - This is a drag & drop email builder plugin based on Jquery and PHP for developer. You can simply integrate this script in your web project and create custom email template with drag & drop">
    <meta name="keywords" lang="en" content="bounce, bulk mailer, campaign, campaign email, campaign monitor, drag & drop email builder, drag & drop email editor, mailchimp, mailer, newsletter, newsletter email, responsive, retina ready, subscriptions, templates">
    <meta name="robots" content="index, follow">

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">

  <link href="assets/css/demo.css" rel="stylesheet" />
  <style>
	.bal-content, .bal-left-menu-container{
		margin-top:0px !important;
		height: 100% !important;
	}

	.bal-left-menu-container .bal-elements .bal-elements-container .bal-elements-list .bal-elements-list-item .bal-preview .bal-elements-item-name {
		margin-top: 5px;
		font-size: 8px !important;
		text-transform: uppercase;
	}
  </style>
    <link href="assets/css/bal-email-editor.css" rel="stylesheet" />
    <link href="assets/css/colorpicker.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
	<!--<div class="bal-header">

		<div class="bal-user-info">
		<div class="bal-user-name">
			<?php echo $userName ?>
		</div>

		<!--<div class="bal-header-controls">
			<a class="bal-button-exit" href="logout.php">Exit</a>
		</div>-
		</div>
	</div>-->
    <div class="bal-editor-demo">

    </div>

    <!-- for demo version -->
    <script src="assets/vendor/jquery/dist/jquery.min.js"></script>
    <script src="assets/vendor/jquery-ui/jquery-ui.min.js"></script>
    <script src="assets/vendor/jquery-nicescroll/dist/jquery.nicescroll.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <!--for ace editor  -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/ace/1.1.01/ace.js" type="text/javascript"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/ace/1.1.01/theme-monokai.js" type="text/javascript"></script>

    <!--for tinymce  -->
    <!--<script src="http://cdn.tinymce.com/4/tinymce.min.js"></script>-->
	<script src="assets/js/tinymce/tinymce.min.js"></script>

    <script src="assets/js/email-editor.bundle.js"></script>
    <script src="assets/js/bal-email-editor-plugin.js"></script> 
    <script>
        var _is_demo = false;


		function getHtml(){
				
				_html = '';
                $('.bal-content .bal-content-wrapper .sortable-row').each(function() {
                    _html += $(this).find('.sortable-row-content').html().split('contenteditable="true"').join('');
                });
                _width = $('.bal-content-main').css('width');
                _style = '';
				_style += 'background-color:' + $('.bal-content-wrapper').css('background-color') + ';';
				if( $('.bal-content-wrapper').css('background').trim() ){
					_style += 'background:' + $('.bal-content-wrapper').css('background') + ';';
				}
				
				
				
                _style += 'padding:' + $('.bal-content-wrapper').css('padding');
                _result = '<div class="email-content" style="' + _style + '">' + _html + '</div>';

                _result = '<table width="100%" cellspacing="0" cellpadding="0" border="0" style="' + _style + '"><tbody><tr><td><div style="margin:0 auto;width:' + _width + ';">' + _html + '</div></td></tr></table>';
                return _result;
		}
		
		function remove_template(el,id){
			
			 $.ajax({
                url: 'admin/template-delete.php',
                type: 'POST',
				data: {templateId:id},
                dataType: 'json',
                success: function(data) {
                    if (data.code == 0) {
                       el.closest('div').remove();
                    }
                },
                error: function() {}
            });
		}

        function loadImages() {
            $.ajax({
                url: 'get-files.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.code == 0) {
                        _output = '';
                        for (var k in data.files) {
                            if (typeof data.files[k] !== 'function') {
                                _output += "<div class='col-sm-3'>" +
                                    "<img class='upload-image-item' src='" + data.directory + data.files[k] + "' alt='" + data.files[k] + "' data-url='" + data.directory + data.files[k] + "'>" +
                                    "</div>";
                                // console.log("Key is " + k + ", value is" + data.files[k]);
                            }
                        }
                        $('.upload-images').html(_output);
                    }
                },
                error: function() {}
            });
        }
        var _templateListItems;

        $('.bal-editor-demo').emailBuilder({
            lang: 'it',
            //elementJsonUrl: 'elements.json?v=1',
            elementJsonUrl:'/admin/modules/mail_marketing/controller.php?action=elements&v=2',
			loading_color1: 'red',
            loading_color2: 'green',
            showLoading: false,

            //left menu
            showElementsTab: true,
            showPropertyTab: true,
            showCollapseMenu: true,
            showBlankPageButton: true,
            showCollapseMenuinBottom: true,

            //setting items
            showSettingsBar: true,
            showSettingsPreview: true,
            showSettingsExport: true,
            showSettingsSendMail: true,
            showSettingsSave: true,
            showSettingsLoadTemplate: true,
			

            //show context menu
            showContextMenu: true,
            showContextMenu_FontFamily: true,
            showContextMenu_FontSize: true,
            showContextMenu_Bold: true,
            showContextMenu_Italic: true,
            showContextMenu_Underline: true,
            showContextMenu_Strikethrough: true,
            showContextMenu_Hyperlink: true,

            //show or hide elements actions
            showRowMoveButton: true,
            showRowRemoveButton: true,
            showRowDuplicateButton: false,
            showRowCodeEditorButton: true,
            onElementDragStart: function(e) {
                //console.log('onElementDragStart html');
				
            },
            onElementDragFinished: function(e,contentHtml) {
                //console.log('onElementDragFinished html');
               
				

            },

            onBeforeRowRemoveButtonClick: function(e) {
                //console.log('onBeforeRemoveButtonClick html');

                /*
                  if you want do not work code in plugin ,
                  you must use e.preventDefault();
                */
                //e.preventDefault();
            },
            onAfterRowRemoveButtonClick: function(e) {
                console.log('onAfterRemoveButtonClick html');
            },
            onBeforeRowDuplicateButtonClick: function(e) {
                console.log('onBeforeRowDuplicateButtonClick html');
                //e.preventDefault();
            },
            onAfterRowDuplicateButtonClick: function(e) {
                console.log('onAfterRowDuplicateButtonClick html');
            },
            onBeforeRowEditorButtonClick: function(e) {
                console.log('onBeforeRowEditorButtonClick html');
                //e.preventDefault();
            },
            onAfterRowEditorButtonClick: function(e) {
                console.log('onAfterRowDuplicateButtonClick html');
            },
            onBeforeShowingEditorPopup: function(e) {
                console.log('onBeforeShowingEditorPopup html');
                //e.preventDefault();
            },
            onBeforeSettingsSaveButtonClick: function(e,getHtml) {
                console.log('onBeforeSaveButtonClick html');
                //e.preventDefault();

                //  if (_is_demo) {
                //      $('#popup_demo').modal('show');
                //      e.preventDefault();//return false
                //  }
            },
            onPopupUploadImageButtonClick: function() {
                //console.log('onPopupUploadImageButtonClick html');
                var file_data = $('.input-file').prop('files')[0];
                var form_data = new FormData();
                form_data.append('file', file_data);
                $.ajax({
                    url: 'upload.php', // point to server-side PHP script
                    dataType: 'text', // what to expect back from the PHP script, if anything
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    success: function(php_script_response) {
                        loadImages();
                    }
                });
            },
            onSettingsPreviewButtonClick: function(e, getHtml) {
                console.log('onPreviewButtonClick html');

                $.ajax({
                    url: 'export.php',
                    type: 'POST',
                    data: {
                        html: getHtml
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.code == -5) {
                            $('#popup_demo').modal('show');
                            return;
                        } else if (data.code == 0) {
                            var win = window.open(data.preview_url, '_blank');
                            if (win) {
                                //Browser has allowed it to be opened
                                win.focus();
                            } else {
                                //Browser has blocked it
                                alert('Please allow popups for this website');
                            }
                        }
                    },
                    error: function() {}
                });
                //e.preventDefault();
            },

            onSettingsExportButtonClick: function(e, getHtml) {
                console.log('onSettingsExportButtonClick html');
                $.ajax({
                    url: 'export.php',
                    type: 'POST',
                    data: {
                        html: getHtml
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.code == -5) {
                            $('#popup_demo').modal('show');
                        } else if (data.code == 0) {
                            window.location.href = data.url;
                        }
                    },
                    error: function() {}
                });
                //e.preventDefault();
            },
            onBeforeSettingsLoadTemplateButtonClick: function(e) {

                $('.template-list').html('<div style="text-align:center">Loading...</div>');

                $.ajax({
                    url: 'load_templates.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.code == 0) {
                            _templateItems = '';
                            _templateListItems = data.files;
                            for (var i = 0; i < data.files.length; i++) {
                                _templateItems += '<div><button onclick="remove_template($(this),'+data.files[i].id+'); return false;"><i class="fa fa-close"></i></button><div class="template-item" data-id="' + data.files[i].id + '">' +
                                    '<div class="template-item-icon">' +
                                    '<i class="fa fa-file-text-o"></i>' +
                                    '</div>' +
                                    '<div class="template-item-name">' +
                                    data.files[i].name +
                                    '</div>' +
                                    '</div></div>';
                            }
                            $('.template-list').html(_templateItems);
                        } else if (data.code == 1) {
                            $('.template-list').html('<div style="text-align:center">No items</div>');
                        }
                    },
                    error: function() {}
                });
            },
            onSettingsSendMailButtonClick: function(e) {
                console.log('onSettingsSendMailButtonClick html');
                //e.preventDefault();
            },
            onPopupSendMailButtonClick: function(e, _html) {
                console.log('onPopupSendMailButtonClick html');
                _email = $('.recipient-email').val();
                _element = $('.btn-send-email-template');

                output = $('.popup_send_email_output');

                $.ajax({
                    url: '/modules/mail_marketing/controller.php',
                    type: 'POST',
                    data: {
						action: 'sendmail',
                        html: _html,
                        mail: _email
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.code == 0) {
                            output.css('color', 'green');
                        } else {
                            output.css('color', 'red');
                        }

                        _element.removeClass('has-loading');
                        _element.text('Send Email');

                        output.text(data.message);
                    },
                    error: function() {
                        output.text('Internal error');
                    }
                });
            },
            onBeforeChangeImageClick: function(e) {
                console.log('onBeforeChangeImageClick html');
                loadImages();
            },
            onBeforePopupSelectTemplateButtonClick: function(e) {
                console.log('onBeforePopupSelectTemplateButtonClick html');

            },
            onBeforePopupSelectImageButtonClick: function(e) {
                console.log('onBeforePopupSelectImageButtonClick html');

            },
            onAfterLoad: function(e) {
                console.log('onAfterLoad html');
                $.ajax({
                    url: 'load_templates.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.code == 0) {
                            _templateItems = '';

                            _templateListItems = data.files;

                            _dataId = 4;
                            //search template in array
                            var result = $.grep(_templateListItems, function(e) {
                                return e.id == _dataId;
                            });

                            _contentText = $('<div/>').html(result[0].content).text();
                            $('.bal-content-wrapper').html(_contentText);

                            $('#popup_load_template').modal('hide');


                        }
                    },
                    error: function() {}
                });
            },
            onPopupSaveButtonClick: function(e) {
                console.log('onPopupSaveButtonClick html');
				 _html = getHtml();
                $.ajax({
                    url: 'save_template.php',
                    type: 'POST',
                    //dataType: 'json',
                    data: {
                        name: $('.template-name').val(),
                        content: $('.bal-content-wrapper').html(),
						html: _html
                    },
                    success: function(data) {
                        //  console.log(data);
                        if (data === 'ok') {
                            $('#popup_save_template').modal('hide');
                        } else {
                            $('.input-error').text('Problem in server');
                        }
                    },
                    error: function(error) {
                        $('.input-error').text('Internal error');
                    }
                });
            }
        });



        /*
        elementJsonUrl: 'elements.json',
        lang: 'en',
        blankPageHtml:
        loadPageHtml:
        showContextMenu: true,
        showContextMenu_FontFamily: true,
        showContextMenu_FontSize: true,
        showContextMenu_Bold: true,
        showContextMenu_Italic: true,
        showContextMenu_Underline: true,
        showContextMenu_Strikethrough: true,
        showContextMenu_Hyperlink: true,

        //left menu
        showElementsTab: true,
        showPropertyTab: true,
        showCollapseMenu: true,
        showBlankPageButton: true,
        showCollapseMenuinBottom: true, //btn-collapse-bottom

        //setting items
        showSettingsBar: true,
        showSettingsPreview: true,
        showSettingsExport: true,
        showSettingsSendMail: true,
        showSettingsSave: true,
        showSettingsLoadTemplate: true,

        //show or hide elements actions
        showRowMoveButton: true,
        showRowRemoveButton: true,
        showRowDuplicateButton: true,
        showRowCodeEditorButton: true,

        //events of settings
        onSettingsPreviewButtonClick: function(e) {},
        onSettingsExportButtonClick: function(e) {},
        onBeforeSettingsSaveButtonClick: function(e) {},
        onSettingsSaveButtonClick: function(e) {},
        onBeforeSettingsLoadTemplateButtonClick: function(e) {},
        onSettingsSendMailButtonClick: function(e) {},

        //events in modal
        onBeforeChangeImageClick: function(e) {},
        onBeforePopupSelectImageButtonClick: function(e) {},
        onBeforePopupSelectTemplateButtonClick: function(e) {},
        onPopupSaveButtonClick: function(e) {},
        onPopupSendMailButtonClick: function(e) {},
        onPopupUploadImageButtonClick: function(e) {},

        //selected element events
        onBeforeRowRemoveButtonClick: function(e) {},
        onAfterRowRemoveButtonClick: function(e) {},

        onBeforeRowDuplicateButtonClick: function(e) {},
        onAfterRowDuplicateButtonClick: function(e) {},
        onBeforeRowEditorButtonClick: function(e) {},
        onAfterRowEditorButtonClick: function(e) {},

        onBeforeShowingEditorPopup: function(e) {},
        */


        //   var  _builder= $('.bal-editor-demo').emailBuilder({
        //     showLoading:false
        //   });
        //
        //
        //
        //   _builder.setElementJsonUrl('test.json');
        //   _builder.setLang('ru');
        //
        //
        // //  _builder.setBlankPageHtml('setBlankPageHtml');
        // //  _builder.setLoadPageHtml('setLoadPageHtml');
        //
        //   _builder.setShowContextMenu_FontFamily(true);
        //   _builder.setShowContextMenu_FontSize(true);
        //   _builder.setShowContextMenu_Bold(true);
        //   _builder.setShowContextMenu_Italic(true);
        //   _builder.setShowContextMenu_Underline(true);
        //   _builder.setShowContextMenu_Strikethrough(true);
        //   _builder.setShowContextMenu_Hyperlink(true);
        //
        //
        //   _builder.setShowElementsTab(true);
        //   _builder.setShowPropertyTab(true);
        //   _builder.setShowCollapseMenu(true);
        //   _builder.setShowBlankPageButton(true);
        //   _builder.setShowCollapseMenuinBottom(true);
        //
        //   _builder.setShowSettingsBar(true);
        //   _builder.setShowSettingsPreview(true);
        //   _builder.setShowSettingsExport(true);
        //   _builder.setShowSettingsSendMail(true);
        //   _builder.setShowSettingsSave(true);
        //   _builder.setShowSettingsLoadTemplate(true);
        //
        //
        //   _builder.setShowRowMoveButton(true);
        //   _builder.setShowRowRemoveButton(true);
        //   _builder.setShowRowDuplicateButton(true);
        //   _builder.setShowRowCodeEditorButton(true);
        //
        //   _builder.setSettingsPreviewButtonClick(function () {
        //     console.log('setSettingsPreviewButtonClick');
        //   });
        //
        //   _builder.setSettingsExportButtonClick(function () {
        //     console.log('setSettingsExportButtonClick');
        //   });
        //   _builder.setBeforeSettingsSaveButtonClick(function () {
        //     console.log('setBeforeSettingsSaveButtonClick');
        //   });
        //   _builder.setSettingsSaveButtonClick(function () {
        //     console.log('setSettingsSaveButtonClick');
        //   });
        //   _builder.setBeforeSettingsLoadTemplateButtonClick(function () {
        //     console.log('setBeforeSettingsLoadTemplateButtonClick');
        //   });
        //   _builder.setSettingsSendMailButtonClick(function () {
        //     console.log('setSettingsSendMailButtonClick');
        //   });
        //   _builder.setBeforeChangeImageClick(function () {
        //     console.log('setBeforeChangeImageClick');
        //   });
        //   _builder.setBeforePopupSelectImageButtonClick(function () {
        //     console.log('setBeforePopupSelectImageButtonClick');
        //   });
        //
        //
        //   _builder.setBeforePopupSelectTemplateButtonClick(function () {
        //     console.log('setBeforePopupSelectTemplateButtonClick');
        //   });
        //
        //   _builder.setPopupSaveButtonClick(function () {
        //     console.log('setPopupSaveButtonClick');
        //   });
        //   _builder.setPopupSendMailButtonClick(function () {
        //     console.log('setPopupSendMailButtonClick');
        //   });
        //   _builder.setPopupUploadImageButtonClick(function () {
        //     console.log('setPopupUploadImageButtonClick');
        //   });
        //   _builder.setBeforeRowRemoveButtonClick(function () {
        //     console.log('setBeforeRowRemoveButtonClick');
        //   });
        //   _builder.setAfterRowRemoveButtonClick(function () {
        //     console.log('setAfterRowRemoveButtonClick');
        //   });
        //
        //   _builder.setBeforeRowDuplicateButtonClick(function () {
        //     console.log('setBeforeRowDuplicateButtonClick');
        //   });
        //   _builder.setAfterRowDuplicateButtonClick(function () {
        //     console.log('setAfterRowDuplicateButtonClick');
        //   });
        //   _builder.setBeforeRowEditorButtonClick(function () {
        //     console.log('setBeforeRowEditorButtonClick');
        //   });
        //
        //   _builder.setAfterRowEditorButtonClick(function () {
        //     console.log('setAfterRowEditorButtonClick');
        //   });
        //   _builder.setBeforeShowingEditorPopup(function () {
        //     console.log('setBeforeShowingEditorPopup');
        //   });




        //  _builder.init();


        $(function() {

			
            //load default email





        });
    </script>
</body>

</html>
