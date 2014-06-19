jQuery(document).ready(function($){
	var orig_send_to_editor = window.send_to_editor;
	jQuery('.upload_image_button').click(function() {

	    var	formField = jQuery(this).parent().find('input');
	    var	imgField =  jQuery(this).parent().find('.imgsrc');
	    	
	        tb_show('', './media-upload.php?type=image&amp;TB_iframe=true');
	        window.send_to_editor = function(html) {
		   
	            var regex = /src="(.+?)"/;
	            var result = html.match(regex);

	            if(result== null)
	            {
	                regex = /href='(.+?)'/;
                    result = html.match(regex);
		    		var imgUrl = result[1];
                    formField.val(imgUrl);
	            }else{		            
   					var imgUrl = result[1];

                    if(formField.length >0)
                        formField.val(imgUrl);
	            	if(imgField.length>0)
	            		imgField.attr("src",imgUrl);
                }
		        tb_remove();
	            window.send_to_editor = orig_send_to_editor;
	        };
	        return false;
	    });
	$('.dam-confirm-receiving').click(function(){
		 
			if(confirm("Did you receive the goods?"))
			{
				var auction_id = $(this).attr("data-id");
				var self = $(this);
				$.ajax({
					type:"POST",
					url :ajaxurl,
					data:{'action': 'ajaxHandle', 'act':'confirm_receiving',"id": auction_id},
					success:function(res){
											
						if(res ==1)
						{
							jQuery(self).parent().html("ACCEPT");
						}					
					},
					error:function(){}				
				});
			}
	 });
	$('.dam-delivery').click(function() {
		if(confirm("Are you sure to deliver?"))
		{
			var auction_id = $(this).attr("data-id");
			var self = $(this);
			$.ajax({
				type:"POST",
				url :ajaxurl,
				data:{'action': 'ajaxHandle', 'act':'delivery',"id": auction_id},
				success:function(res){
										
					if(res ==1)
					{
						jQuery(self).parent().html("DELIVERED");
					}					
				},
				error:function(){}				
			});
		}
		
        return false;
    });
    $('.datetime').datetimepicker(
	{
		'dateFormat':'yy-mm-dd',
		'timeFormat': 'HH:mm'
	});
	$.datepicker._gotoToday = function(id) {
		var inst = this._getInst($(id)[0]),
		$dp = inst.dpDiv;
		var d = new Date();		
		var utc = d.getTime() + (d.getTimezoneOffset() * 60000);		
		var server_time = utc + dam_ajax.timezone * 60 * 60000;
		var gmtTime = new Date(server_time);		
		this._setTime(inst, gmtTime);
		$('.ui-datepicker-today', $dp).click();
	};
	$("select[multiple]").multiselect({
		   header: "Choose only three items!",
		   selectedText:function(){
			   var foo = []; 
			   jQuery(this.bindings).find('option:selected').each(function(i, selected){ 
			     foo[i] = jQuery(selected).text(); 
			   });
			   return foo.toString();
		   },
		   click: function(e,ui){
               if( $(this).multiselect("widget").find("input:checked").length > 3 ){
                   return false;
               } else {
               }
           }
    });
	$(".showimg").live("change",function(){
		
		var size_wrap = $(this).parent().parent().parent().find(".size-wrap");

		if($(this).is(":checked"))
		{
			$(size_wrap).show();
		}else
		{
			$(size_wrap).hide();
		}
	});
	$("form").submit(function(){		
		$(".btn_safty").attr("disabled","disabled");
	});
});

jQuery(document).on('submit', '#post', function(event){
    var validated = jQuery(this).valid();
});

jQuery('input[type="submit"], #submitpost').click(function(){
    var post = jQuery("form#post");
    if(post.length>0)
    {
        var validated = post.valid();
        if(!validated)
        {
            jQuery('#publish').removeClass('button-primary-disabled');
            jQuery('#ajax-loading').attr('style','');
            jQuery('#publishing-action .spinner').hide();
        }else
        {
            var props =  get_props();
            var str = JSON.stringify(ko.toJS(props), null, 2);
            jQuery("#SC_properties").val(str);
            var img = [];
            if(jQuery("#media-items").length>0)
            {
                jQuery("#media-items .pinkynail").each(function(){ img.push(jQuery(this).attr("src")) });
                jQuery("#SC_picture").val(img.join());
            }
        }
    }
});

jQuery(document).ready(function($){

    var pictures_str = $("#SC_picture").val();
    if( pictures_str && pictures_str.length >0)
    {
        var pictures = pictures_str.split(',');
        for(var i=0; i<pictures.length; i++)
        {
            var item = $('<div class="media-item"><img class="pinkynail" src="'+ pictures[i] + '"> <a class="icon-remove btn btn-pic"></a></div>');
            $("#media-items").append(item);
            jQuery(".icon-remove").click(function(){
                jQuery(this).parent().remove();
            });
        }
    }
    $(".auto-complete").live("keydown.autocomplete",function(){
        $(this).autocomplete({
            source: availableTags
        });
    });

    $(".sortable" ).sortable();

    if(typeof(prepareMediaItemInit) ==='function' )
    {
        var o_prepareMediaItemInit = prepareMediaItemInit;
        window.prepareMediaItemInit = function( fileObj)
        {
            var item = jQuery('#media-item-' + fileObj.id);
            jQuery('.thumbnail', item).clone().attr('class', 'pinkynail').prependTo(item);
            jQuery('<a class="icon-remove btn btn-pic"></a>').insertBefore(jQuery(".filename.new",item));
            jQuery(".slidetoggle,.describe-toggle-off,.toggle", item).remove();
            jQuery(".icon-remove", item).click(function(){
                item.remove();
            });
        }
    }
});

var AppViewModel = function (properties) {
    var self = this;
    self.properties = ko.observableArray(ko.utils.arrayMap(properties, function(property) {
        return { key: property.key, value: property.value};
    }));
    self.length =  ko.computed(function(item){
        return self.properties().length-1;
    });
    self.addProperty = function () {
        self.properties.push({
            key: "",
            value: ""
        });
        jQuery(".auto-complete").last().focus();
    };
    self.removeProperty = function (item) {
        self.properties.remove(item);
    };
    self.moveUp = function (item, event) {
        var props = get_props();
        var tr = jQuery(event.target).parent().parent();
        var index = tr.index()-1 ;
        if( !jQuery(event.target).hasClass("disable"))
        {
            var temp = props[index];
            props[index] = props[index - 1];
            props[index - 1] = temp;
            self.properties(props);
        }
    };
    self.moveDown = function (item, event) {
        var props = get_props();
        var tr = jQuery(event.target).parent().parent();
        var index = tr.index()-1 ;
        if( !jQuery(event.target).hasClass("disable"))
        {
            var temp = props[index];
            props[index] = props[index + 1];
            props[index + 1] = temp;
            self.properties(props);
        }
    }
};

function get_props()
{
        var __props = [];
        jQuery(".auction-props").each(function(){
        __props.push(
        {"key": jQuery("input",this)[0].value ,
        "value": jQuery("input",this)[1].value
        })});
    return __props;
}

if(typeof properties =="undefined")
    properties = [];
var model = new AppViewModel(properties);
ko.applyBindings(model, document.getElementById('custom_properties'));

