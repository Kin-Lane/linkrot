<?php
$route = '/linkrot/checkstatus';
$app->get($route, function () use ($app,$three_scale_provider_key){

	$ReturnObject = array();
	$http = curl_init();

 	$request = $app->request();
 	$_POST = $request->params();

	if(isset($_POST['host'])){ $Host = $_POST['host']; } else { $Host = ""; }

	$CheckDomainQuery = "SELECT * FROM sites WHERE Host = '" . $Host . "'";
	$CheckDomainResult = mysql_query($CheckDomainQuery) or die('Query failed: ' . mysql_error());
	if($CheckDomainResult && mysql_num_rows($CheckDomainResult))
		{
		$Site = mysql_fetch_assoc($CheckDomainResult);
		$HostTable = $Site['HostTable'];

		$GetURLQuery = "SELECT * FROM " . $HostTable . " WHERE Status = 0 ORDER BY ID LIMIT 1";
		//echo $GetURLQuery . "<br />";
		$GetURLResult = mysql_query($GetURLQuery) or die('Query failed: ' . mysql_error());
		if($GetURLResult && mysql_num_rows($GetURLResult))
			{
			while($URLResult = mysql_fetch_assoc($GetURLResult))
				{

				$ID = $URLResult['ID'];
				$URL = $URLResult['URL'];
				$Resolved_URL = $URL;
				$http_status = 0;

				if (strpos($URL,'script') !== false)
					{
				    //echo 'true';
					$http_code = 404;
					}
				else
					{
					//$URL = "http://apievangelist.com/apis.json";
					$URL = str_replace("https:","http:",$URL);
					//echo $URL . "<br />";

					$http = curl_init();
					curl_setopt($http, CURLOPT_URL, trim($URL));
					curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
					//curl_setopt($http,CURLOPT_USERAGENT,' Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.101 Safari/537.36');
					$output = curl_exec($http);
					//echo $output . "<br />";
					$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
					$info = curl_getinfo($http);

					//echo "1) " . $http_status . "<br />";
					//var_dump($info);

					if($http_status=="301" || $http_status=="301")
						{
						$Resolved_URL = $info['redirect_url'];
						$http = curl_init();
						curl_setopt($http, CURLOPT_URL, $Resolved_URL);
						curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
						$output = curl_exec($http);
						$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
						$info = curl_getinfo($http);
						echo "2) " . $http_status . "<br />";

						}

					}

				if($http_status==0){$http_status=404;}

				$UpdateStatusQuery = "UPDATE " . $HostTable . " SET Status = " . $http_status . ", Resolved_URL = '" . urlencode($Resolved_URL) . "' WHERE ID = " . $ID;
				//echo "<br />" . $UpdateStatusQuery . "<br />";
				$UpdateStatusResult = mysql_query($UpdateStatusQuery) or die('Query failed: ' . mysql_error());
				//echo "<hr />";
				}
			}

		}

	$ReturnObject['status'] = "ok";

	$app->response()->status(200);
	$app->response()->header("Content-Type", "application/json");
	echo format_json(stripslashes(json_encode($ReturnObject)));

	});
?>
