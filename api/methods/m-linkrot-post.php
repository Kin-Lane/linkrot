<?php
$route = '/linkrot/';
$app->post($route, function () use ($app,$three_scale_provider_key){

	$ReturnObject = array();

 	$request = $app->request();
 	$_POST = $request->params();

	$ip = $app->request()->getIp();
	//echo $ip . " - ";
	$host = $app->request()->getReferrer();
	//echo $host . " - ";
	$urlArray = parse_url($host);
	$host = $urlArray['host'];
	//echo $host . " - ";
	$hostTable = str_replace(".", "_", $host);
	//echo $hostTable . " - ";
	$data = json_decode($request->getBody());

	$CheckDomainQuery = "SELECT * FROM sites WHERE Host = '" . $host . "' AND HostTable = '" . $hostTable . "'";
	//echo $CheckDomainQuery . "<br />";
	$CheckDomainResult = mysql_query($CheckDomainQuery) or die('Query failed: ' . mysql_error());
	if($CheckDomainResult && mysql_num_rows($CheckDomainResult))
		{

		//var_dump($data);
		$URLs = array();
		foreach($data as $url)
			{
			$url = strip_tags(mysql_real_escape_string($url));

			//temporary
			$url = str_replace("http://laneworks.net/","http://apievangelist.com/",$url);

			//echo $url . "<br />";

			if($url!='' && $url!='http://' && $url!='http:///' && $url!='http:/// ' && $url!='javascript:void(0); ')
				{

				$Return_URL = $url;
				//add to individual url
				$CheckQuery = "SELECT * FROM " . $hostTable . " WHERE URL = '" . $url . "'";
				$CheckResult = mysql_query($CheckQuery) or die('Query failed: ' . mysql_error());
				if($CheckResult && mysql_num_rows($CheckResult))
					{
					$CheckResult = mysql_fetch_assoc($CheckResult);
					$ID = $CheckResult['ID'];
					$Short_URL = $CheckResult['Short_URL'];
					$Status = $CheckResult['Status'];
					$Screenshot_URL = $CheckResult['Screenshot_URL'];

					if($Short_URL!=''){ $Return_URL = $Short_URL; }
					if($Status==404){ $Return_URL = $Screenshot_URL; }
					}
				else
					{
					// add profile url
					$query = "INSERT INTO " . $hostTable . "(";
					$query .= "URL";
					$query .= ") VALUES(";
					$query .= "'" . $url . "'";
					$query .= ")";
					//echo $query . "<br />";
					mysql_query($query) or die('Query failed: ' . mysql_error());
					//$Profile_URL_ID = mysql_insert_id();

					}

				$U = array();
				$U['URL'] = $url;
				$U['returnURL'] = $Return_URL;
				array_push($URLs, $U);

				$DeleteQuery = "DELETE FROM apievangelist_com WHERE URL LIKE '%mailto:%'";
				//echo $query . "<br />";
				mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());

				$DeleteQuery = "DELETE FROM apievangelist_com WHERE URL LIKE '%javascript:%'";
				//echo $query . "<br />";
				mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());

				$DeleteQuery = "DELETE FROM apievangelist_com WHERE URL LIKE '%www.addthis.com%'";
				//echo $query . "<br />";
				mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());

				$DeleteQuery = "DELETE FROM apievangelist_com WHERE URL LIKE '%doubleclick.net%'";
				//echo $query . "<br />";
				mysql_query($DeleteQuery) or die('Query failed: ' . mysql_error());


				}
			}
		}

	$app->response()->status(200);
	$app->response()->header("Content-Type", "application/json");
	echo format_json(stripslashes(json_encode($URLs)));

	});
?>
