<?php
	session_start();
	session_destroy();
	echo('<script>sessionStorage.hash="";window.location="login.php"</script>');
?>
