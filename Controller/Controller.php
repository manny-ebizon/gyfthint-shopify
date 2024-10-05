<?php 	
	if(function_exists($fn))
	{				
		$tmp = $fn();					
		$data = array_merge($data,$tmp);
	}
	else
	{	
		if (getUriSegment(1)=="order") {			
			$fn = "index";
			$tmp = $fn();					
			$data = array_merge($data,$tmp);			
		} else if (getUriSegment(1)=="pay") {
			$fn = "index";
			$tmp = $fn();					
			$data = array_merge($data,$tmp);			
		} else if (getUriSegment(1)=="p") {
			$fn = "index";
			$tmp = $fn();					
			$data = array_merge($data,$tmp);			
		} else {
			$data['title'] = "Page Not Found!";
			$data['path'] = "includes/404";
		}		
	}

	function unclean($string) {   
        $string = str_replace("&quot;", '"', $string);
        $string = str_replace("&apos;", "'", $string);
        $string = str_replace("&bsol;u", "\u", $string);
        return trim($string);
    }

include('View/'.$data['path'].".php");
?>