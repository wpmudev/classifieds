
jQuery(document).ready(function($) {
	$('form.confirm-form').hide();
	$('form.cf-contact-form').hide();
});

var classifieds = {
	toggle_end: function(key) {
		jQuery('#confirm-form-'+key).show();
		jQuery('#action-form-'+key).hide();
		jQuery('#cf-end-'+key).show();
		jQuery('#cf-renew-'+key).hide();
		jQuery('#cf-delete-'+key).hide();
		jQuery('input[name="action"]').val('end');
	},
	toggle_renew: function(key) {
		jQuery('#confirm-form-'+key).show();
		jQuery('#confirm-form-'+key+' select[name="duration"]' ).show();
		jQuery('#action-form-'+key).hide();
		jQuery('#cf-end-'+key).hide();
		jQuery('#cf-renew-'+key).show();
		jQuery('#cf-delete-'+key).hide();
		jQuery('input[name="action"]').val('renew');
	},
	toggle_delete: function(key) {
		jQuery('#confirm-form-'+key).show();
		jQuery('#confirm-form-'+key+' select[name="duration"]' ).hide();
		jQuery('#action-form-'+key).hide();
		jQuery('#cf-end-'+key).hide();
		jQuery('#cf-renew-'+key).hide();
		jQuery('#cf-delete-'+key).show();
		jQuery('input[name="action"]').val('delete');
	},
	toggle_contact_form: function() {
		jQuery('.cf-ad-info').hide();
		jQuery('#action-form').hide();
		jQuery('#confirm-form').show();
	},
	cancel_contact_form: function() {
		jQuery('#confirm-form').hide();
		jQuery('.cf-ad-info').show();
		jQuery('#action-form').show();
	},
	cancel: function(key) {
		jQuery('#confirm-form-'+key).hide();
		jQuery('#action-form-'+key).show();
	}
};

