function generatePassword(input_selector, pass_length=8, added_chars="!@%#()")
{
	let chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	chars += added_chars;
	
	let generated = "";
	for(let i = 0; i <= pass_length; i++)
	{
		let randomNumber = Math.floor(Math.random() * chars.length);
		generated += chars.substring(randomNumber, randomNumber + 1);
	}
	
	$(input_selector).val(generated);
	
}

function windowPopup(uri, w_name='my_poppy_window', w_width=1100, w_height=960)
{
	const w_top =( window.top.outerHeight / 2) + (window.top.screenY) - ( w_height / 2);
	const w_left = (window.top.outerWidth / 2) + (window.top.screenX) - ( w_width / 2);
	
	attributes = "toolbar=yes,status=yes,scrollbars=yes,resizable=yes,width="+w_width+",height="+w_height+",top="+w_top+",left="+w_left;
	window.open(uri, w_name, attributes);
}