<?php
$target_file = $_SERVER['argv'][1];
$file = fopen($target_file, "r") or exit("Unable to open file!");
date_default_timezone_set('Asia/Singapore');
$now = date("ymdGiss");
$capture =0;
$captured_line = "";
$issues_count = 0;
$recommendation = "";
$csv_output = "\"Issue\",\"Result\",\"Observation\",\"Impact\",\"Recommendation\",\"Status\"\n";
$final_output = "";

function getBetween($content,$start,$end){
    $r = explode($start, $content);
    if (isset($r[1])){
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

function cleaner_htmltag($string) {
	$string = str_replace('<span class="inline_block">', '', $string);
	$string = str_replace('</span>', '', $string);
	$string = str_replace('<span>', '', $string);
	$string = str_replace('<p>', '', $string);
	$string = str_replace('</p>', '', $string);
	$string = str_replace('<div>', '', $string);
	$string = str_replace('<code class="code_block">', "\n", $string);
	$string = str_replace('</code>', "\n", $string);
	$string = str_replace('<p/>', '', $string);
	$string = str_replace('<p class="bold">', '', $string);
	$string = str_replace('<br/>', "\n", $string);
	$string = str_replace('<em>', '', $string);
	$string = str_replace('</em>', '', $string);
	$string = str_replace("\n\n", "\n", $string);
	$string = str_replace("                           ", "", $string);
	
	$string = htmlspecialchars($string);
	$string = str_replace('&amp;', '&', $string);
	$string = str_replace('&lt;', '<', $string);
	$string = str_replace('&gt;', '>', $string);

	return $string;
}



while(!feof($file))
  {
  $line = fgets($file);
  // echo  "<br>";
  if (strpos($line,'<div id="detail-')) {
  	$capture =1;
  	$issues_count++;
  	$captured_line .= "--------------------------";
  }

  if ((strpos($line,'<td class="evidence">')) && ($capture)) {
  	$capture =0;
  }

  if ($capture) {
 	$captured_line .= $line;
	}
// if ($issues_count > 10) {
// 	break;
// }

}

$arr_captured_line = explode("--------------------------",$captured_line);

foreach ($arr_captured_line as $each_issue) {
    if (strpos($each_issue,'class="Rule ')) {
    	$title = getBetween($each_issue,'<h3 class="ruleTitle"','</h3>');
    	$title = explode(">",$title);
    	$title = $title[1];
    	$title = preg_replace("/^(\S*)\s/", "$2", $title);
    	echo "Title: $title";
    	echo "\n";
    	$result = ucfirst(getBetween($each_issue,'<span class="outcome '," ruleResultArea"));
    	echo "Result: $result";
    	echo "\n";
    	$desc = getBetween($each_issue,'<div class="bold">Description:</div>',"</div");
    	$desc = trim(cleaner_htmltag($desc));
		$impact = getBetween($each_issue,'<div class="rationale">',"</div");
    	$impact = trim(cleaner_htmltag($impact));
    	$recommendation = getBetween($each_issue,'<div class="fixtext">',"</div");
		$recommendation = trim(cleaner_htmltag($recommendation));
    	$csv_output .= "\"$title\",\"$result\",\"$desc\",\"$impact\",\"$recommendation\",\"Open\"\n";
    }
    $final_output .= $csv_output;
    $csv_output = "";

}
$output_file = "Output_". $target_file ."_$now.csv";
file_put_contents($output_file, $final_output);
?>
