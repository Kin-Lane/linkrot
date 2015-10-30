<?php
$route = '/linkrot/shortenurls/';
$app->get($route, function () use ($app,$three_scale_provider_key,$appid,$appkey){

	$ReturnObject = array();

 	$request = $app->request();
 	$_POST = $request->params();

	if(isset($_POST['host'])){ $Host = $_POST['host']; } else { $Host = ""; }

	$CheckDomainQuery = "SELECT * FROM sites WHERE Host = '" . $Host . "'";
	echo $CheckDomainQuery . "<br />";
	$CheckDomainResult = mysql_query($CheckDomainQuery) or die('Query failed: ' . mysql_error());
	if($CheckDomainResult && mysql_num_rows($CheckDomainResult))
		{
		$Site = mysql_fetch_assoc($CheckDomainResult);
		$HostTable = $Site['HostTable'];

		$GetURLQuery = "SELECT * FROM " . $HostTable . " WHERE Short_URL = '' AND Status = 200 ORDER BY ID DESC LIMIT 5";
		echo $GetURLQuery . "<br />";
		$GetURLResult = mysql_query($GetURLQuery) or die('Query failed: ' . mysql_error());
		if($GetURLResult && mysql_num_rows($GetURLResult))
			{
			while($URLResult = mysql_fetch_assoc($GetURLResult))
				{

				$ID = $URLResult['ID'];
				$URL = $URLResult['URL'];
				$Status = $URLResult['Status'];
				$Resolved_URL = $URL;
				$Short_URL = "";

				if($Status==200)
					{
					if($Resolved_URL!='')
						{

						$api_url = "http://api.apis.how/link/";
						//echo "here: " . $Resolved_URL . "<br />";
						///	$results = bitly_v3_shorten($Resolved_URL,'bit.ly');

						$url = '';
						$fields = array(
										'appid' => urlencode($appid),
										'appkey' => urlencode($appkey),
										'url' => urlencode($Resolved_URL)
										);

						foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
						rtrim($fields_string, '&');

						$http = curl_init();

						curl_setopt($http,CURLOPT_URL, $api_url);
						curl_setopt($http,CURLOPT_POST, count($fields));
						curl_setopt($http,CURLOPT_POSTFIELDS, $fields_string);

						$output = curl_exec($http);
						$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
						$info = curl_getinfo($http);

						//var_dump($info);
						$results = $output;

						curl_close($ch);

						if(isset($results['short_url']))
							{
							$Short_URL = $results['short_url'];
							}
						}
					else
						{
						$api_url = "http://api.apis.how/link/";
						//echo "here: " . $Resolved_URL . "<br />";
						///	$results = bitly_v3_shorten($Resolved_URL,'bit.ly');

						$url = '';
						$fields = array(
										'appid' => urlencode($appid),
										'appkey' => urlencode($appkey),
										'url' => urlencode($Resolved_URL)
										);

						foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
						rtrim($fields_string, '&');

						$http = curl_init();

						curl_setopt($http,CURLOPT_URL, $api_url);
						curl_setopt($http,CURLOPT_POST, count($fields));
						curl_setopt($http,CURLOPT_POSTFIELDS, $fields_string);

						$output = curl_exec($http);
						$http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
						$info = curl_getinfo($http);

						//var_dump($info);
						$results = $output;

						curl_close($ch);

						if(isset($results['url']))
							{
							$Short_URL = $results['url'];
							}
						}
					}

				if($Short_URL==""){ $Short_URL=$URL; }

				$URLs = array();
				$URLs['url'] = $URL;
				$URLs['shortendurl'] = $Short_URL;
				array_push($ReturnObject, $URLs);

				//echo "here: " . $Short_URL . "<br />";
				$UpdateStatusQuery = "UPDATE " . $HostTable . " SET Short_URL = '" . $Short_URL . "' WHERE ID = " . $ID;
				$UpdateStatusResult = mysql_query($UpdateStatusQuery) or die('Query failed: ' . mysql_error());

				}
			}

		}

	$app->response()->status(200);
	$app->response()->header("Content-Type", "application/json");
	echo format_json(stripslashes(json_encode($ReturnObject)));

	});
?>
