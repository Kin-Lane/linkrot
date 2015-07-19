<?php
$route = '/linkrot/screencapture';
$app->get($route, function () use ($app,$three_scale_provider_key,$APIKey){

	$ReturnObject = array();

 	$request = $app->request();
 	$_POST = $request->params();

	if(isset($_POST['host'])){ $Host = $_POST['host']; } else { $Host = ""; }

	$CheckDomainQuery = "SELECT * FROM sites WHERE Host = '" . $Host . "'";
	//echo $CheckDomainQuery . "<br />";
	$CheckDomainResult = mysql_query($CheckDomainQuery) or die('Query failed: ' . mysql_error());
	if($CheckDomainResult && mysql_num_rows($CheckDomainResult))
		{
		$Site = mysql_fetch_assoc($CheckDomainResult);
		$HostTable = $Site['HostTable'];

		$GetURLQuery = "SELECT * FROM " . $HostTable . " WHERE Screenshot_URL = '' AND Status = 200 AND URL NOT LIKE '%twitter.com%' ORDER BY ID LIMIT 1";
		//echo $GetURLQuery . "<br />";
		$GetURLResult = mysql_query($GetURLQuery) or die('Query failed: ' . mysql_error());
		if($GetURLResult && mysql_num_rows($GetURLResult))
			{
			while($URLResult = mysql_fetch_assoc($GetURLResult))
				{

				$ID = $URLResult['ID'];
				$URL = $URLResult['URL'];
				$Resolved_URL = $URL;
				$Short_URL = "";

				//echo $URL . "<br />";
				//echo $Resolved_URL . "<br />";

				if($Resolved_URL!='')
					{
					$URL = "http://api.laneworks.net/v1/screenshots/?key=" . urlencode($APIKey) . "&url=" . urlencode($Resolved_URL);
					$Pull_URL = $Resolved_URL;
					}
				else
					{
					$URL = "http://api.laneworks.net/v1/screenshots/?key=" . urlencode($APIKey) . "&url=" . urlencode($URL);
					$Pull_URL = $URL;
					}

				//echo "URL: " . $URL . "<br />";

				$http = curl_init();
				curl_setopt($http, CURLOPT_URL, $URL);
				curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

				$output = curl_exec($http);
				//echo $output . '<br />';
				$Screenshot = json_decode($output,false);
				if(isset($Screenshot->screenshot_url))
					{
					$screenshot_url = $Screenshot->screenshot_url;
					//echo "<img src='" . $screenshot_url . "' width='450' />";
					$UpdateStatusQuery = "UPDATE " . $HostTable . " SET Screenshot_URL = '" . $screenshot_url . "' WHERE ID = " . $ID;
					$UpdateStatusResult = mysql_query($UpdateStatusQuery) or die('Query failed: ' . mysql_error());
					}
				else
					{
					$screenshot_url = "[problem]";

					//echo "<img src='" . $screenshot_url . "' width='150' />";
					$UpdateStatusQuery = "UPDATE " . $HostTable . " SET Screenshot_URL = '" . $screenshot_url . "' WHERE ID = " . $ID;
					$UpdateStatusResult = mysql_query($UpdateStatusQuery) or die('Query failed: ' . mysql_error());
					}

				$URLs = array();
				$URLs['url'] = $Pull_URL;
				$URLs['screenshot'] = $screenshot_url;
				array_push($ReturnObject, $URLs);

				}
			}

		}

	$app->response()->status(200);
	$app->response()->header("Content-Type", "application/json");
	echo format_json(stripslashes(json_encode($URLs)));

	});
?>
