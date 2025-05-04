<?php
exec("ipconfig", $output);
foreach($output as $line) {
  if (strpos($line, "IPv4") !== false) {
    echo $line;
  }
}
?>
