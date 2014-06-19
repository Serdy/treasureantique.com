(function($) {

	$(".reduce, .increase").click(function() {
		var input = $(this).parent().find("input").get(0);
		
		var input_price = parseFloat($(input).val());
		
		var step = parseFloat($("#step_price").val());
		
		var lowest = parseFloat($("#last-price").attr("data-price"));

		if ($(this).hasClass("reduce"))
			step = -step;
		var value = input_price + step;

		if (value > lowest )
		{
			var v = value.toFixed(2);			 
			$(input).val(v);
		}
		
	});
	
	//enlarge pictures
	$("div.picture img.main-picture").not(".show-link a > img.main-picture").css("cursor", "pointer").click(function(){
		window.open(jQuery(this).attr("src"));
	});
	
	$("div.picture li>img").css("cursor", "pointer").click(function(){
		$(this).parent().parent().parent().parent().find("div.picture img.main-picture").attr("src",$(this).attr("src").replace("-150x150",""));
	});
	
	$(".sign_validation_email").submit(function(event){
		event.preventDefault();
		$.ajax({
				type:"POST",
				url : dam_ajax.ajax_url,
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
	
	$(".btn-register").click(function(){		
		window.location.href =  dam_ajax.login_url + "?action=register";
	});
	
	
	var query_auction_status = function()
	{
		if(typeof(dam_plugin_url) =="undefined")
		{
			return false;
		}
		
		var auction_id = $("#auction_id").val();
		var input = $("input.bid-price-input");

		if(input.length <= 0)
			return false;
		$.ajax({
			type : "POST",
			url : dam_ajax.ajax_url,
			data :{'action': 'dam_ajax_handle', 'act':'status', 'id': auction_id},
			dataType : 'json',
			timeout : 10000,
			success : function(data) {	
				if( data.last_bids)
				{
					
					_root.bids(data.last_bids);
					
					$(".last-price").attr("data-price",data.bid_price);
					$(".last-price").html(data.bid_str_price);
					var price = parseFloat(data.bid_price);
					var step = parseFloat(data.step_price);
					var myprice = $(input).val();
					
					if(parseFloat(myprice) < price + step)
						$(input).val( price + step );	
					
					if(data.is_close == true){
						
						setTimeout("window.__timer_close = true;",50000);			
						
						//on_auction_closed(data.message);						
						$(".action-on-close").hide();	
						$(".status-message").html(data.message);
					}					
				}
			},
			error : function(data) {
				if (this.onerror != undefined)
					this.onerror(data);
			}
		});
		
	};
	
	$(".auction-action-form").submit(function(event) {
		event.preventDefault();
		
		var input = $(this).find("input.bid-price-input");
		$.ajax({
			type : "POST",
			url : dam_ajax.ajax_url,
			data : $(this).serialize(),
			dataType : 'json',
			timeout : 10000,
			success : function(data) {	
			
				if( data.last_bids)
				{
					_root.bids(data.last_bids);
					$(".last-price").attr("data-price",data.bid_price);	
					$(".last-price").html(data.bid_str_price);
					var price = parseFloat($(input).val());				 
					if(parseFloat(data.bid_price) >= price)
					{
						$(input).val( parseFloat(data.bid_price) + parseFloat(data.step_price));
					}
				}else if( data.error ==  "unsign")
				{
					$(".sign-form-inputs").show();
					$(".validate-message").hide();

					//$('#'+modalLocation).reveal($(this).data());
					$("#login_model").reveal( );
					return false;					
				}else if(data.error =="unactived")
				{
					$(".sign-form-inputs").show();
					$(".validate-message").hide();
					//$("#register_model").modal('show');
					$("#login_model").reveal( );
					return false;
				}
			},
			error : function(data) {
				if (this.onerror != undefined)
					this.onerror(data);
			}
		});

	});
	
	var formatSecond = function(totalsecond)
	{
		var timer = {};
		if(totalsecond>0)
		{
			timer.hours = Math.floor(totalsecond/3600);
			timer.mins = Math.floor((totalsecond -(timer.hours*3600))/60);
			timer.seconds = Math.floor((totalsecond -(timer.hours*3600)-(timer.mins*60)));
		}else{
			timer.hours = 0;
			timer.mins =0;
			timer.seconds =	0;
		}
		return timer;
	};
	
	var on_auction_closed = function (msg)
	{
		query_auction_status();
		$(".action-on-close").hide();	
		$(".status-message").html(msg);
	};
	
	var start_timer = function() {

		var secondCount = 1;
		
		if (window.__currentTime) {
			secondCount = new Date().getTime() / 1000 - window.__currentTime;
		}

		window.__currentTime = new Date().getTime() / 1000;

		jQuery(".auction-timer").each(function() {
		
			
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
//				window.__timer_close = true;
//				if(window.__timer_close == false){
//					on_auction_closed("The auction is closed");
//					$(this).html("auction closed");
//				}
			}		
		
		});
	};
	
	
	var polling = function(){
		if(	typeof(window.__timer_close) == "undefined" || window.__timer_close == false)
		{
			query_auction_status();
		}
	};

	setInterval(start_timer, 1000);	
	
	setInterval(polling, 5000);  //TODO if it is not running then should stop polling.
	
	var upcoming_timer = function() { 
		
		jQuery("input#upcoming_time").each(function() {
			
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