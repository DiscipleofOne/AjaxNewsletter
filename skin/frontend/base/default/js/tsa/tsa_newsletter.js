resizeLoadingMask = function() {
    $('loading-mask').setStyle({
        width: $$('.block-subscribe')[0].getStyle('width'),
        height: $$('.block-subscribe')[0].getStyle('height') + 20
    });
}


Event.observe(window, 'resize', function() {

    resizeLoadingMask();
});



document.observe("dom:loaded", function () {
    resizeLoadingMask();

    $$('.block-subscribe')[0].insert({
      top: $('loading-mask').remove()
    });
    Event.observe('newsletter-validate-detail', 'submit', function(event) {
        Event.stop(event);
        //Insert messages block or update if it already exists
        new Ajax.Request('/newsletter/subscriber/new/', {
            method:'post',
            parameters: $('newsletter-validate-detail').serialize(true),
            onSuccess: function(response){

                // response.responseText.evalJSON().message
                //alert(response.responseText.evalJSON().message);
                $$('.col-main')[0].insert({
                        top: '<ul class="messages"><li class="success-msg"><ul><li><span>' + response.responseText.evalJSON().message + '</span></li></ul></li></ul>'
                    }
                );
                $('newsletter-validate-detail').reset();
            },
            onFailure: function(response){
                // response.responseText.evalJSON().message
                $$('.col-main')[0].insert({
                        top:'<ul class="messages"><li class="error-msg"><ul><li><span>' + response.responseText.evalJSON().message + '</span></li></ul></li></ul>'
                    }
                );
            },
            onCreate: function(){
                $('newsletter-validate-detail').disable();
                $('loading-mask').show();




            },
            onComplete: function(){
                $('newsletter-validate-detail').enable();
                $('loading-mask').hide();


            }

        });
    })
});
