(function($) {
    //enlarge pictures
    $("div.picture img.main-picture").not(".show-link a > img.main-picture").css("cursor", "pointer").click(function(){
        window.open(jQuery(this).attr("src"));
    });

    $("div.picture li>img").css("cursor", "pointer").click(function(){
        $(this).parent().parent().parent().parent().find("div.picture img.main-picture").attr("src",$(this).attr("src").replace("-150x150",""));
    });

    $(".btn-register").click(function(){
        window.location.href =  dam_ajax.login_url + "?action=register";
    });

    $(".sign_validation_email").submit(function(event){
        event.preventDefault();
        $.ajax({
            type:"POST",
            url : dam_ajax.ajaxUrl+"?act=login",
            data : $(this).serialize(),
            dataType : 'json',
            timeout : 10000,
            success : function(data) {
                if(data.error == 0)
                {
                    //$(".sign-form-inputs").hide();
                    $(".validate-message").hide();
                    $(".validate-message").html('');
                    $('#login_model').trigger('reveal:close');
                }
                else
                {
                    $(".validate-message").html(data.error_message);
                    $(".validate-message").show();
                }
            },
            error : function(data) {
                if (this.onerror != undefined)
                    this.onerror(data);
            }
        });
    });


    var upcoming_timer = function() {

        jQuery("input.upcoming_time").each(function() {
            if (this.interval == undefined ) {
                this.interval = jQuery(this).val();
            }

            this.interval--;
            if (this.interval == 0) {
                window.location.reload();
            }
        });
    };
    setInterval(upcoming_timer, 1000);
})(jQuery);

(function($) {
    var timer = {
        start:function()
        {
           var formatSecond = function(total){
                var timer = {};
                if(total>0)
                {
                    timer.hours = Math.floor(total/3600);
                    timer.mins = Math.floor((total -(timer.hours*3600))/60);
                    timer.seconds = Math.floor((total -(timer.hours*3600)-(timer.mins*60)));
                }else{
                    timer.hours = 0;
                    timer.mins =0;
                    timer.seconds =	0;
                }
                return timer;
            };
            var secondCount = 1;
            if (window.__currentTime) {
                secondCount = new Date().getTime() / 1000 - window.__currentTime;
            }
            window.__currentTime = new Date().getTime() / 1000;
            $(".auction-timer").each(function() {
                if (this.sec == undefined ) {
                    this.sec = jQuery(this).find(".time-value").val();
                }
                this.sec = this.sec - secondCount;
                var timer = formatSecond(this.sec);
                if (this.sec >= 0) {
                    $(this).find("b[data-name='hour']").html(timer.hours);
                    $(this).find("b[data-name='min']").html(timer.mins);
                    $(this).find("b[data-name='sec']").html(timer.seconds);
                } else
                {
                    $(this).html(dam_ajax.msg_auction_closed);
                }
            });
        }
    };
    setInterval(timer.start, 1000);
})(jQuery);


window.pull_auction = null;

jQuery(document).ready(function($) {
    var upcoming = [];
    var running = [];
    var all = [];

    var AppViewModel = function(upcoming, running, all) {
        var self = this;
        self.upcoming = ko.observableArray(upcoming);
        self.running = ko.observableArray(running);
        self.all = ko.observableArray(all);
    };

    window._root = new AppViewModel(upcoming, running,all);

    if($('.auction-list-wrap').length>0)
        ko.applyBindings(window._root, $('.auction-list-wrap').get(0));

    var append_auctions = function(auctions, type) {
        var items = eval("window._root." + type + "()");
        for ( var i = 0; i < auctions.length; i++) {
            items.push(auctions[i]);
        }
        eval("window._root." + type + "(items)");
    };

    var interval = function() {
        if (window._visable_more_btn_running) {
            jQuery(".btn-more-running").show();
        } else {
            jQuery(".btn-more-running").hide();
        }

        if (window._visable_more_btn_upcoming) {
            jQuery(".btn-more-upcoming").show();
        } else {
            jQuery(".btn-more-upcoming").hide();
        }

        if (window._visable_more_btn_all) {
            jQuery(".btn-more-all").show();
        } else {
            jQuery(".btn-more-all").hide();
        }
    };

    setInterval(interval, 200);

    var pull_auction = function(status) {
        if(status == null || status == "")
            status = "all";

        var start = eval("window._root." + status + "().length");

        jQuery.ajax({
            type : "POST",
            url : dam_ajax.ajaxUrl + "?status=" + status + "&start=" + start + "&category=" + dam_ajax.category + "&keyword=" + dam_ajax.keyword,
            data:{'action': 'ajaxHandle','act':'auctions'},
            dataType : 'json',
            cache:false,
            timeout : 10000,
            error : function() {
            },
            success : function(data) {
                if (data == null) {
                    return;
                }
                if(data.total > 0)
                    append_auctions(eval("data." + status), status);
                eval("window._visable_more_btn_" + status + " = !data.islast;");
            }
        });
    };

    jQuery("input.indicator").each(function(){
        var type = $(this).val();
        jQuery(".btn-more-" + type).click(function() {
            pull_auction(type);
        });
    });
});