function shaarli(id) {
	try {
		xhr.json("backend.php",
				{
					'op': 'pluginhandler',
					'plugin': 'shaarli',
					'method': 'getShaarli',
					'id': encodeURIComponent(id)
				},
				(reply) => {
					if (reply) {
						var share_url = reply.shaarli_url + "?post="+ encodeURIComponent(reply.link)+ "&title="+encodeURIComponent(reply.title)+"&source=bookmarklet";
						console.log(share_url);
						window.open( share_url,
									'_blank',
									'menubar=no,height=390,width=600,toolbar=no,scrollbars=no,status=no,dialog=1' );
					} else {
						Notify.error("<strong>Error encountered while initializing the Shaarli Plugin!</strong>", true);
					}
				});
	} catch (e) {
		Notify.error("Shaarli", e);
	}
}
