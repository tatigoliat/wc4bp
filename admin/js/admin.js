jQuery(document).ready(function(){
		
	jQuery('.cptfbp_thickbox_add').click(function(){	
		
		var action = jQuery(this);
		var t = this.title || this.name || null;
	    var a = this.href || this.alt;
	    var g = this.rel || false;
	    
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			cache: false,
			data: {"action": "wc4bp_edit_entry"},
			success: function(data) {
				jQuery("#add_page").html(data);
				tb_show(t,a,g);
			},
			error: function() { 
				alert('Something went wrong.. ;-(sorry)');
			}
		});
		
	});
		
	jQuery('.wc4bp_delete_page').click(function(){	
		
		var action			= jQuery(this);
		var wc4bp_tab_slug	= this.id;

		if (confirm('Delete Permanently'))
		
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {"action": "wc4bp_delete_page", "wc4bp_tab_slug": wc4bp_tab_slug},
			success: function(data){
				window.location.reload(true);
			},
			error: function() { 
				alert('Something went wrong.. ;-(sorry)');
			}
		});
		
	});
	
	jQuery('.wc4bp_editinline').click(function(){
		
		var action = jQuery(this);
		var wc4bp_tab_slug = this.id;	
		
		var t = this.title || this.name || null;
	    var a = this.href || this.alt;
	    var g = this.rel || false;

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			cache: false,
			data: {"action": "wc4bp_edit_entry", "wc4bp_tab_slug": wc4bp_tab_slug},
			success: function(data){
				jQuery("#add_page").html(data);
				tb_show(t,a,g);
			},
			error: function() { 
				alert('Something went wrong.. ;-(sorry)');
			}
		});
		
	});
	
	jQuery(".add_cpt4bp_page").live("click", function() { 

		var action = jQuery(this);
		
		var wc4bp_page_id	= jQuery( '#wc4bp_page_id'	).val();
		var wc4bp_tab_slug	= jQuery( '#wc4bp_tab_slug'	).val();
		var wc4bp_tab_name	= jQuery( '#wc4bp_tab_name'	).val();
		var wc4bp_position	= jQuery( '#wc4bp_position'	).val();
		var wc4bp_children	= 0;
		
		
		if(jQuery( '#wc4bp_children'	).attr('checked') == 'checked')
			wc4bp_children = 1;
			
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {"action": "wc4bp_add_page", "wc4bp_page_id": wc4bp_page_id, "wc4bp_tab_slug": wc4bp_tab_slug, "wc4bp_tab_name": wc4bp_tab_name, "wc4bp_position": wc4bp_position, "wc4bp_children": wc4bp_children},
			success: function(data){
				window.location.reload(true);
			},
			error: function() { 
				alert('Something went wrong.. ;-(sorry)');
			}
		});

	});

	// zendesk script for the wc4bp plugin
	jQuery(document).ready(function(jQuery) {    
		if (typeof(Zenbox) !== "undefined") {
			Zenbox.init({
				dropboxID:   "20204291",
				url:         "https://themekraft.zendesk.com",
				tabTooltip:  "Support",
				tabColor:    "black",
				tabPosition: "Left",
				hide_tab: true
			});
		}
	});

});