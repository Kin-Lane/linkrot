<?php $route = '/linkrot/';
$app->get($route, function () use ($app,$three_scale_provider_key){

	$ReturnObject = array();

 	$request = $app->request();
 	$_POST = $request->params();

	if(isset($_POST['host'])){ $Host = $_POST['host']; } else { $Host = ""; }

	$CheckDomainQuery = "SELECT * FROM sites WHERE Host = '" . $Host . "'";
	//echo $CheckDomainQuery;
	$CheckDomainResult = mysql_query($CheckDomainQuery) or die('Query failed: ' . mysql_error());
	if($CheckDomainResult && mysql_num_rows($CheckDomainResult))
		{
		$Site = mysql_fetch_assoc($CheckDomainResult);
		$HostTable = $Site['HostTable'];

		$GetURLQuery = "SELECT * FROM " . $HostTable . " ORDER BY ID";
		//echo $GetURLQuery;
		$GetURLResult = mysql_query($GetURLQuery) or die('Query failed: ' . mysql_error());
		if($GetURLResult && mysql_num_rows($GetURLResult))
			{
			while($URLResult = mysql_fetch_assoc($GetURLResult))
				{

				$ID = $URLResult['ID'];
				$URL = $URLResult['URL'];
				$Short_URL = $URLResult['Short_URL'];
				$Status = $URLResult['Status'];

				$U = array();
				$U['URL'] = $URL;
				$U['Short_URL'] = $Short_URL;
				$U['Status'] = $Status;
				array_push($ReturnObject, $U);


				}
			}
		}

	$app->response()->status(200);
	$app->response()->header("Content-Type", "application/json");
	echo format_json(stripslashes(json_encode($ReturnObject)));

	});
?>
