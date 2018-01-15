<?php
// Check if file exists
$file = "test_data_in.txt";
if (!file_exists($file)) die("File not found");
// Open resource
$fh = fopen($file, 'r');
// Read header to array
$header = fgetcsv($fh, 0, "\t");
// Create data array and read data to associative array
$data = array();
while ($line = fgetcsv($fh, 0, "\t")) {
    $data[] = array_combine($header, $line);
}
// Close resource
fclose($fh);


// Make changes
for ($i = 0; $i < count($data); $i++) {
	// Replace ID numbers with "X"
	$data[$i]["ID"] = substr_replace(($data[$i]["ID"]), "XXX-XX-", 0, 5);
	$data[$i]["FIRST"] = preg_replace("/[^a-zA-Z-\s]/", "", $data[$i]["FIRST"]);
}

// Add new column name
array_push($header, "TERM");
// Add term column to each row
$term = 100; // days
foreach($data as $key=>$value) {
	// Find after-term timestamp
	$newTimestamp = strtotime($data[$key]['EFFECTIVE']) + (24*3600*$term);
	// Find after-term day of the week
	$newDay = date('w', $newTimestamp);
	// If after-term day is Saturday add two days
	if ($newDay == 6) {
		$newTimestamp += 24*3600*2;
	}
	// If after-term day is Sunday add one day
	if ($newDay == 0) {
		$newTimestamp += 24*3600;
	}
	// Put the after-term date to the row
	$data[$key]['TERM'] = date("Ymd", $newTimestamp);
}
// Sort data by Type
$letter = "M";
uasort($data, function($a, $b) use($letter) {
	if ($a["TYPE"] != $b["TYPE"]) {
		if($a["TYPE"] == $letter) return -1;
		if($b["TYPE"] == $letter) return +1;
	}
	return strcmp($a["TYPE"], $b["TYPE"]);
});


// Output to a new file
// Open resource
$out = fopen('test_data_out.txt', 'w');
// Write the header
fputs($out, implode($header, "\t") . "\n");
// Write all data
foreach ($data as $line) {
    fputs($out, implode($line, "\t") . "\n");
}
// Close resource
fclose($out);

?>
<!DOCTYPE html>
<head></head>
<body>
<table style="border: 1px solid black">
<tr>
<?php
// Display header
foreach ($header as $item) {
	echo "<th>" . $item . "</th>";
}
?>
</tr>
<?php
// Display data
$last = count($data) - 1;
foreach ($data as $i => $row) {
	$isFirst = ($i == 0);
	$isLast = ($i == $last);
	echo "<tr>";
	foreach ($header as $item) {
		echo "<td>" . $row[$item] . "</td>";
	}
	echo "</tr>";
}
?>
</table>
</body>