<?php

$route = '/linkrot/';	
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
	
$route = '/linkrot/shortenurls';	
$app->get($route, function () use ($app,$three_scale_provider_key){
	
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
			
		$GetURLQuery = "SELECT * FROM " . $HostTable . " WHERE Short_URL = '' AND Status = 200 ORDER BY ID DESC LIMIT 5";
		//echo $GetURLQuery . "<br />";
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
						//echo "here: " . $Resolved_URL . "<br />";
						$results = bitly_v3_shorten($Resolved_URL,'bit.ly');
						if(isset($results['url']))
							{
							$Short_URL = $results['url'];
							}
						}
					else 
						{
						$results = bitly_v3_shorten($URL,'bit.ly');
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