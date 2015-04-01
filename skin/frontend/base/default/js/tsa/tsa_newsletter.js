function NewsletterAjaxHandler (){
    var self = this;
    self.rejiggerLoadingMask();
    self.registerObservers();
    self.moveAjaxToOverlayPosition();
};

NewsletterAjaxHandler.prototype.SUBSCRIBEBLOCK = '.block-subscribe';
NewsletterAjaxHandler.prototype.LOADER = 'ajax-loading-mask';
NewsletterAjaxHandler.prototype.NEWSLETTERFORM = 'newsletter-validate-detail';
NewsletterAjaxHandler.prototype.SUBMITEVENT = 'submit';
NewsletterAjaxHandler.prototype.RESIZEEVENT = 'resize';
NewsletterAjaxHandler.prototype.MESSAGEBLOCK = '.col-main';

NewsletterAjaxHandler.prototype.moveAjaxToOverlayPosition = function() {
    var self = this;
    $$(self.SUBSCRIBEBLOCK)[0].insert({
        top: $(self.LOADER).remove()
    });
    $(self.LOADER).hide();
}

NewsletterAjaxHandler.prototype.rejiggerLoadingMask = function() {
    var self = this;

    $(self.LOADER).setStyle({
        width: $$(self.SUBSCRIBEBLOCK)[0].getStyle('width'),
        height: $$(self.SUBSCRIBEBLOCK)[0].getStyle('height') + 20,
        top: $$(self.SUBSCRIBEBLOCK)[0].getStyle('top'),
        right: $$(self.SUBSCRIBEBLOCK)[0].getStyle('right'),
        left: $$(self.SUBSCRIBEBLOCK)[0].getStyle('left')
    });
};



NewsletterAjaxHandler.prototype.registerObservers = function() {
    var self = this;
    Event.observe(self.NEWSLETTERFORM, self.SUBMITEVENT, function (event){self.ajaxProcessor(event)});
    Event.observe(window, self.RESIZEEVENT, function(){self.rejiggerLoadingMask()});
};


NewsletterAjaxHandler.prototype.ajaxProcessor = function(submitEvent) {
    var self = this;
    Event.stop(submitEvent);

    new Ajax.Request('/newsletter/subscriber/new/',{
        method:'post',
        parameters: $(self.NEWSLETTERFORM).serialize(true),
        onSuccess: function(response){self.ajaxTSASuccess(response)},
        onFailure: function(response){self.ajaxTSAFailure(response)},
        onCreate: function(){self.ajaxTSACreate()},
        onComplete: function(){self.ajaxTSAComplete()}
    });
};

NewsletterAjaxHandler.prototype.ajaxTSASuccess = function(response) {
    var self = this;
    self.messageInsert("success-msg",response.responseText.evalJSON().message);
    $(self.NEWSLETTERFORM).reset();
};

NewsletterAjaxHandler.prototype.ajaxTSAFailure = function(response) {
    var self = this;
    self.messageInsert("error-msg",response.responseText.evalJSON().message);
};

NewsletterAjaxHandler.prototype.ajaxTSACreate = function() {
    var self = this;

    $(self.NEWSLETTERFORM).disable();
    $(self.LOADER).show();
};

NewsletterAjaxHandler.prototype.ajaxTSAComplete = function() {
    var self = this;
    $(self.NEWSLETTERFORM).enable();
    $(self.LOADER).hide();
};



NewsletterAjaxHandler.prototype.messageInsert = function(stateClass, message)
{
    var self = this;

    var messageString = '<ul class="messages"><li class=' + stateClass+ '><ul><li><span>' + message + '</span></li></ul></li></ul>';

    $$(self.MESSAGEBLOCK)[0].insert({
        top: messageString
    });
}

document.observe("dom:loaded", function(){
  new NewsletterAjaxHandler ();
});



