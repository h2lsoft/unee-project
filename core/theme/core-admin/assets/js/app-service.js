setInterval(()=>{
	
	return;
	
	let uri = "/"+APP_BACKEND_DIRNAME+"/logon-checker/";
	fetch(uri, {method: 'get'}).then((response) => {
		if(response.ok)return response.json();
		throw new Error(`${response.status} : ${response.statusText}`);
	}).then((response) => {
	
	
	
	}).catch((error)  => {
		error = error.toString().replace('Error:', '');
		console.error(error);
	});
	
	
}, 30000);