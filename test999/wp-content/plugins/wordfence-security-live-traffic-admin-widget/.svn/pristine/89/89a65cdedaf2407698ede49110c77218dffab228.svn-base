jQuery(document).ready(function() {
  var selectOptions, optionAction, optionName, optionValue, item;
  if (jQuery('#wfTabs a').length) {
	// Generate Select element from a elements in the menu
	selectOptions = {};
	jQuery('#wfTabs a').each(function() {
		optionName = jQuery(this).html();
		optionValue = optionName.split(' ').join('-');
		optionAction = jQuery(this).attr('onclick');
		optionAction = optionAction.replace(" return false;", "");
		selectOptions[optionValue] = {'action': optionAction, 'name': optionName};
	});
	jQuery('#wfTabs').replaceWith('<select id="wordfence-views"></select>');
	for(item in selectOptions){
		if(selectOptions.hasOwnProperty(item)) {
			jQuery('<option value=' + item + '>' + selectOptions[item].name + '</option>').appendTo('#wordfence-views');
		}
	}
	// Event Listeners
	jQuery('#wordfence-views').on('change', function() {
		eval(selectOptions[this.value].action);
	});
  }
  jQuery('#wfsltaw-about').on('click', function() {
    jQuery('#wfsltaw-about-wrapper').fadeIn('fast');
  });
  jQuery('#wfsltaw-close').on('click', function() {
    jQuery('#wfsltaw-about-wrapper').fadeOut('fast');
  });
  jQuery('#wfsltaw-overlay').on('click', function() {
    jQuery('#wfsltaw-about-wrapper').fadeOut('fast');
  });
});
