window.pull_auction = null;

jQuery(document).ready(function($) {

	var upcoming = [];
	var running = [];
	var all = [];
	
	var id = 0;
	
	if(typeof(bids) =="undefined")
	{
	  bids = [];
	}
	
	var price = 0;

	var AppViewModel = function(upcoming, running, all, id, bids, price) {
		var self = this;
		self.upcoming = ko.observableArray(upcoming);
		self.running = ko.observableArray(running);
		self.all = ko.observableArray(all);
	    self.auctionid = id;
	    self.bids = ko.observableArray(bids);
	    self.price = ko.observable(price); 
	};

	window._root = new AppViewModel(upcoming, running,all, id, bids, price);
	ko.applyBindings(window._root);

	window._visable_more_btn_upcoming = false;
	window._visable_more_btn_running = false;	
	window._visable_more_btn_all = false;

	var append_auctions = function(auctions, type) {
		
		if(typeof(auctions) == "undefined")
	           return;
		
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
			url : dam_ajax.ajax_url + "?status=" + status + "&start=" + start + "&category=" + dam_ajax.category + "&keyword=" + dam_ajax.keyword,
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
		pull_auction(type);
		
		jQuery(".btn-more-" + type).click(function() {
			pull_auction(type);
		});
	});	
});