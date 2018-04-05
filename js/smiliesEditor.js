
$(function(){
  $("#smilies-list").sortable({'cursor':'move'});
    $("#smilies-list tr").hover(function(){
      $(this).css({'cursor':'move'});
    },function(){
      $(this).css({'cursor':'auto'});
    });
  $('#smilies-form').submit(function(){
      var order=[];
    $("#smilies-list tr td input.position").each(function(){
      order.push(this.name.replace(/^order\[([^\]]+)\]$/,'$1'));
    });
    $("input[name=smilies_order]")[0].value=order.join(',');
    return true;
    });
  $("#smilies-list tr td input.position").hide();
  $("#smilies-list tr td.handle").addClass('handler');

});