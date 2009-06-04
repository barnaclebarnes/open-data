//For Admin section
var $j = jQuery.noConflict();

var suppleClones = 0;

function confirmGenerateSpplTable(){
	return confirm('WARNING: You are about to alter your Custom Table.\n\nYou should minimize the # of times this operation is executed.  Existing data will likely be lost in columns switching from text to numeric data types.\n\nAre you ready to Generate your Custom Table now?');
}

function suppleConfirmDeleteField(){
	var fieldname = jQuery("#supple_fieldDropDown option:selected").text();
	return confirm('Do you want to delete field: ' + fieldname + '?');
}

function suppleDuplicator(eleId){
	
	suppleClones++;
	
	var e = jQuery("#" + eleId).clone().attr('id','suppleclone_' + suppleClones).val('');
	jQuery("#add_" + eleId).before(e);
	
	var remover = jQuery("<a>X</a>").attr('href','javascript:void(0);');
	
	var cloneid = 'suppleclone_' + suppleClones;
	
	remover.attr('id', cloneid + '_remover');
	remover.click(function(){suppleRemover(cloneid);return false;});
	
	e.after(remover);

}

function suppleRemover(eleId){
	jQuery("#" + eleId + "_remover").remove();
	jQuery("#" + eleId).remove();
}

function suppleHideWPCustomFields()
{
	if(!suppleHideMetaID){return false;}
	for (i=0;i<suppleHideMetaID.length;i++)
	{
		jQuery("#meta-" + suppleHideMetaID[i]).fadeOut("slow");
	}

}