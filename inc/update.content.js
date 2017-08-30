function update_my_post(){
    $j = jQuery.noConflict();
    $j.ajax({
        url: '',
        data: {'update': true},
        type: 'POST',
        dataType: 'html',
        beforeSend:function(){
            $j("#image-loading").show();  
        },
        complete:function(){
            $j("#image-loading").hide();
        },
        success:function(){
            $j('<p style="font-weight:bold;font-style:italic;color:green;">It is done!</p>').insertAfter('#_update_button');
        }
    });
}
