$(function() {
	$('.checkboxes-helpers').each(function() {
		dotclear.checkboxesHelpers(this);
	});

	$('#smilepic').change(
		function(){
			$('#smiley-preview').attr('src',dotclear.smilies_base_url+this.value).attr('title',this.value).attr('alt',this.value);
		}
	);

     $('#smilepic,select.emote,#smilepic option,select.emote option').each(function(){
          $(this).css('background-image','url(' + dotclear.smilies_base_url + this.value +')').change(
          function(){
               $(this).css('background-image','url('+dotclear.smilies_base_url + this.value +')');
          });
     });

     $('#smilepic option,select.emote option').each(function(){
          //$(this).css('background-image','url(' + dotclear.smilies_base_url + this.value +')');
     });

});