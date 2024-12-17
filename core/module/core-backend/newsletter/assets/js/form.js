$(function(){

	$('input[name="scheduler_report_email"]').on('change',function(){
		$('input[name="scheduler_report_email_recipients"]').attr('disabled', !$(this).is(':checked'));
	}).change();

	$('input[name="mode_test"]').on('change',function(){
		$('input[name="mode_test_email"]').attr('disabled', !$(this).is(':checked'));
	}).change();

	$('input[name="status"]').on('change',function(){
		$('input[name="programming_date"]').attr('disabled', ($(this).val()=='draft'));
	}).change();

});