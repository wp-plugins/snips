jQuery(document).ready(function($) {
	var ta=$('#snipModel');
	if (ta.length==0) return;
	var re=/#\w+#/g;
	ta.keyup(function() {
		var val=ta.attr('value');
		var vars=val.match(re);
		vars.sort();
		var lastVar;
		var curVar;
		var existingVars=[];
		for(var i=0; i<vars.length; i++) {
			curVar=vars[i].substr(1, vars[i].length-2);
			if (curVar==lastVar) continue;
			existingVars.push(curVar);
			//
			lastVar=curVar;
			if($('#snip-def-'+curVar+'-row').length>0) continue;
			//
			var varTD=$('#snip-row-model').clone();
			varTD.attr('id', 'snip-def-'+curVar+'-row');
			varTD.children('th').html(curVar);
			$('input', varTD).attr('name', 'snip-def-'+curVar);
			varTD.show();
			varTD.insertAfter($('#snip-row-model'));
		}
		var defaultInputs=$('#snipsDefaults input');
		for (var i=1; i<defaultInputs.length; i++) {
			curVar=$(defaultInputs[i]).attr('name').substr(9);
			console.log(curVar);
			if (jQuery.inArray(curVar, existingVars)==-1) $('#snip-def-'+curVar+'-row').remove();
		}
	}).keyup();
});