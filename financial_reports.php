<?php
	session_start();
	if (!isset($_SESSION['authenticated']))
		header( 'Location: index.php' );
	else
	{
?>

<html lang="en">
<head>
<meta charset="utf-8">
<title>Financial Reports</title>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.9.1.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="lib/style.css">
<script>
history.navigationMode = 'compatible';
$(document).ready(function(){
	
	$('input[type=radio]').parent().css('height', '18pt');
	$('input[type=radio]').parent().find('*').css('vertical-align', 'middle');
	$('input[type=radio]').parent().find('input').css('margin-top', '-2pt');

	$('input[type=radio]').css('padding-top', '10pt');
	
	$('form').each(function(){
		if ($('input[type=radio]', this).get(0))
			$('input[type=radio]', this).get(0).checked = true;
	});
	
	var now = new Date();
    var month = (now.getMonth() + 1);               
    var day = now.getDate();
    if(month < 10) 
        month = "0" + month;
    if(day < 10) 
        day = "0" + day;
    var today = month + '/' + day + '/' + now.getFullYear();
    $('input.datepicker').val(today);
});

$(function() {
	
	$( "input.datepicker" ).datepicker();
	$( "#accordion" ).accordion({
		active: false,
	  collapsible: true,
	  heightStyle: 'content'
	});
	$('input:radio').change(
		function(){
			if ($(this).is(':checked') && $(this).val() == 'custom') {
				$(this).parent().parent().find('span.datepicker').css('visibility', 'visible');
			} else {
				$(this).parent().parent().find('span.datepicker').css('visibility', 'hidden');
			}
			
		}
	); 
});
	 

</script>
</head>
<body>
<div class="mainbody">

<div style="width: 70%; margin: 0pt auto">
<div class="accordion" id="accordion">
  <h3 class="color0"><span>Balance Sheet / Net Worth Statement</span><span class="diminutive">A summary listing of assets, liabilities, and the resulting net worth.</span></h3>
  <div>
    <p><form action="networth.php">
			<div><input type="radio" name="BSType" value="mra" checked="true">Most Recent Available</div>
			<div><input type="radio" name="BSType" value="eolm">End of Last Month</div>
			<div><input type="radio" name="BSType" value="eoly">End of Last Year</div>
			<div><input type="radio" name="BSType" value="custom"><span>Custom Date</span><span class="datepicker">:<input type="text" name="date" class="datepicker" /></span></input></div>
			<br/><input type="submit" value="Generate Report"/>
	</form></p>
  </div>
  <h3 class="color1"><span>Income Statement</span><span class="diminutive">A summary of income and expenses over the course of a given time period.</span></h3>
  <div>
    <p>
    Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet
    purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor
    velit, faucibus interdum tellus libero ac justo. Vivamus non quam. In
    suscipit faucibus urna.
    </p>
  </div>
  <h3 class="color3"><span>Cash Flow Statement</span><span class="diminutive">A summary of cash inflows and outflows over the course of a given time period.</span></h3>
  <div>
    <p>
    Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis.
    Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero
    ac tellus pellentesque semper. Sed ac felis. Sed commodo, magna quis
    lacinia ornare, quam ante aliquam nisi, eu iaculis leo purus venenatis dui.
    </p>
  </div>
</div>

</div>

</div>

</body>
</html>
<?php
	}
?>