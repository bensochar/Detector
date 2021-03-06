<?php
	function createFT($ua,$match,$title,$prefix = '',$note = '') {
		print "<table class=\"zebra-striped span9\">
			<thead>
				<tr>
					<th>".$title."</th>
					<th>Your Browser</th>
					<th>Detector Profile</th>
				</tr>
			</thead>
			<tbody>";
		$check = 0;
		foreach($ua as $key => $value) {
			if (preg_match($match,$key)) {
				$check = 1;
				if (is_object($value)) {
					
					$value_a = (array) $value;
					ksort($value_a);
					$value = (object) $value_a;
					
					foreach ($value as $vkey => $vvalue) {
						print "<tr>";
						print "<th class=\"span7\">".$key."->".$vkey.":</th>";
						if (Detector::$foundIn == "archive") {
							print "<td class=\"span1\"><span class='label'>N/A</span></td>";
						} else {
							print "<td class=\"span1\">
									<script type=\"text/javascript\">
										if (Modernizr['".$prefix.$key."']['".$vkey."'] == true) { 
											document.write(\"<span class='label success'>\"+Modernizr['".$prefix.$key."']['".$vkey."']+\"</span>\"); 
										} else if (Modernizr['".$prefix.$key."']['".$vkey."']) {
											document.write(\"<span class='label warning'>\"+Modernizr['".$prefix.$key."']['".$vkey."']+\"</span>\"); 
										} else { 
											document.write(\"<span class='label important'>false</span>\"); 
										}
									</script>
								   </td>";
						}
						print "<td class=\"span1\">".convertTF($vvalue)."</td>";
						print "</tr>";
					}
				} else {
					print "<tr>";	
					print "<th class=\"span7\">".$key.":</th>";
					if ((Detector::$foundIn == "archive") || ($key == "extendedVersion")) {
						print "<td class=\"span1\"><span class='label'>N/A</span></td>";
					} else {
						print "<td class=\"span1\">
								<script type=\"text/javascript\">
									if (Modernizr['".$prefix.$key."']) { 
										document.write(\"<span class='label success'>\"+Modernizr['".$prefix.$key."']+\"</span>\"); 
									} else { 
										document.write(\"<span class='label important'>false</span>\"); 
									}
								</script>
							   </td>";
					}
					print "<td class=\"span1\">".convertTF($value)."</td>";
					print "</tr>";
				}
			}
		}
		if ($check == 0) {
			print "<tr>";	
			print "<td class=\"span9\" colspan=\"3\">This browser didn't support JavaScript so these features haven't been recorded.</td>";
			print "</tr>";
		}
		print "</tbody>";
		print "</table>";
		if ($note != '') {
			print "<div class=\"featureNote span9\">";
			print "<small><em>".$note."</em></small>";
			print "</div>";
		}
	}
?>