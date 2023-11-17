/*jQuery(function($){
	"use strict";
	// Set all variables to be used in scope
	var frame,
		metaBox = $('#comoNewsFeed-file.postbox'), // Your meta box id here
		addImgLink = metaBox.find('.upload-comonews-file'),
		delImgLink = metaBox.find( '.delete-comonews-file'),
		imgContainer = metaBox.find( '.custom-file-container'),
		imgIdInput = metaBox.find( '.comonews-file' );
	
	// ADD IMAGE LINK
	addImgLink.on( 'click', function(event) {
		
		console.log('CLICK');
		
		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( frame ) {
			frame.open();
			return;
		}

		// Create a new media frame
		frame = wp.media({
			title: 'Select or Upload News File',
			button: {
				text: 'Use this file'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});
		
		// When an image is selected in the media frame...
		frame.on( 'select', function() {
			
			// Get media attachment details from the frame state
			var attachment = frame.state().get('selection').first().toJSON();
	
			// Send the attachment URL to our custom image input field.
			imgContainer.append( attachment.url);
	
			// Send the attachment id to our hidden input
			imgIdInput.val( attachment.id );
	
			// Hide the add image link
			addImgLink.addClass( 'hidden' );
			
			// Unhide the remove image link
			delImgLink.removeClass( 'hidden' );
		});
		
		// Finally, open the modal on click
		frame.open();
	});
	
	// DELETE IMAGE LINK
	delImgLink.on( 'click', function( event ){
		
		event.preventDefault();
	
		// Clear out the preview image
		imgContainer.html( '' );
	
		// Un-hide the add image link
		addImgLink.removeClass( 'hidden' );
	
		// Hide the delete image link
		delImgLink.addClass( 'hidden' );
	
		// Delete the image id from the hidden input
		imgIdInput.val( '' );
	});
});*/

jQuery(document).ready(function($){
	"use strict";
    // Instantiates the variable that holds the media library frame.
    var meta_image_frame, $fileField, $fileIDfield, $fileAddBtn, $fileRemoveBtn;
	
    // Runs when the image button is clicked.
    $('.meta-upload-button').click(function(e){
        e.preventDefault();
		
		$fileField = $(this).parent().children('input.como-upload-field');
		$fileIDfield = $(this).parent().children('input.como-upload-id-field');
		$fileAddBtn = $(this).parent().children('.meta-upload-button');
		$fileRemoveBtn = $(this).parent().children('.remove-upload-button');
 
        // If the frame already exists, re-open it.
        if ( meta_image_frame ) {
            meta_image_frame.open();
            return;
        }
 
        // Sets up the media library frame
        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text:  meta_image.button }
        });
 
        // Runs when an image is selected.
        meta_image_frame.on('select', function(){
            var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
            $fileField.val(media_attachment.url);
			$fileIDfield.val( media_attachment.id);
			$fileAddBtn.addClass( 'hidden' );
			$fileRemoveBtn.removeClass( 'hidden' );
        });
        meta_image_frame.open();
    });
	
	// DELETE FILE LINK
	$('.remove-upload-button').click(function(e){
		event.preventDefault();
		$fileField = $(this).parent().children('input.como-upload-field');
		$fileIDfield = $(this).parent().children('input.como-upload-id-field');
		$fileAddBtn = $(this).parent().children('.meta-upload-button');
		$fileRemoveBtn = $(this).parent().children('.remove-upload-button');
		$fileField.val('');
		$fileIDfield.val('');
		$fileAddBtn.removeClass( 'hidden' );
		$fileRemoveBtn.addClass( 'hidden' );
	});
});