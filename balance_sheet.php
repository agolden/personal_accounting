<?php
	session_start();
	if (!isset($_SESSION['authenticated']))
	{
		header( 'Location: login.php?dest=' . urlencode($_SERVER['REQUEST_URI']) );
		die();
	}
?><!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<title>Net Worth Statement</title>

		<style type="text/css">
			td {
				padding-left: 8pt;
				padding-right: 8pt;
				padding-top: 6pt;
				padding-bottom: 6pt;
				font-size: .75em;
				vertical-align: middle;
			}

			tr.altRow td {
				background-color: #ffffff;
				border-color: #d5d5d4;
				border-style: solid;
				border-width: 1px;
				border-right-style:none;
				border-left-style:none;
			}

			 table {
				float:left;
				width:calc(50% - 20pt);
				border-spacing: 0pt;
				border-collapse: separate;
				margin-left: 10pt;
				margin-right: 10pt;
			 }

			div.piechart
			{
				float: left;
				width: 125pt;
				height: 125pt;
			}

			#networth{
				text-align: center;
				font-weight: bold;

			}
			 
			tr.small td {
				padding-left: 20pt;
				
			}

			tr.titleRow td {
				background-color: #808080;
				color: #f3f7f4;
				padding-left: 4pt;
				padding-right: 7pt;
				padding-top: 3pt;
				padding-bottom: 3pt;
			}

			tr.titleRow td.right {
				font-weight: bold;
			}

			span.titleLabel {
				display:inline-block;
				padding-top: 2pt;
			}
			div.colorchip {
				width: 15pt;
				height: 15pt;
				float: left;
				margin-right: 6pt;
			}
			 
			td.right {
				text-align:right;
			}
		</style>
 
		<script type="text/javascript" src="lib/jquery-2.1.0.js"></script>
		<script type="text/javascript" src="lib/jquery.xpath.js"></script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<link rel="stylesheet" type="text/css" href="lib/style.css" />

		<script type="text/javascript">
		/* <![CDATA[ */
			google.load("visualization", "1", {packages:["corechart"]});

			var summaryUrl = "endpoint/balance_sheet.php?nonzero=true<?php

			$date = new DateTime();
			if(isset($_GET['BSType']))
			{
				if($_GET['BSType'] == "eolm")
					$date->modify("last day of previous month");
				elseif($_GET['BSType'] == "eoly")
					$date->setDate(intval($date->format("Y"))-1, 12, 31);
			}
			
			echo "&date=" . $date->format("m/d/Y");
			?>";
		 
			function childVal(node, childName)
			{
				return $(node).xpath(childName + "/text()")[0].nodeValue;
			}
			
			function childCurrVal(node, childName)
			{
				return formatCurrency(parseFloat(childVal(node, childName)));
			}
			
			function formatCurrency(amount)
			{
				return amount.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
			}

			function populateTable(tableid, account_type, xml)
			{
				var account_types = $(xml).xpath("records/account_types/account_type[name='" + account_type + "']");
				var tablehtml = "";

				var dataArray = [];
				dataArray.push(['Task', 'Hours per Day']);

				for (index = 0; index < account_types.length; ++index) {
					
					tablehtml += "<tr><td colspan=\"2\">";
					tablehtml += "<div class=\"piechart\" id=\"piechart" + tableid + "\"></div>";
					tablehtml += "<div style=\"font-size:2em; font-weight: bold; text-align:center; margin-top: 50pt;\">$" + childCurrVal(account_types[index], "total") + "</div>";
					tablehtml += "<div style=\"font-size:1.5em; text-align:center;\">" + (tableid == "assets" ? "Assets" : "Liabilities") + "</div>";
					tablehtml += "</td></tr>";

					var account_categories = $(xml).xpath("records/account_categorys/account_category[account_type_id='" + childVal(account_types[index], "id") + "']");
					for (index2 = 0; index2 < account_categories.length; ++index2) {
			
						tablehtml += "<tr class=\"titleRow\">";
						tablehtml += "<td><div class=\"color" + index2 + " colorchip\"></div><span class=\"titleLabel\">" + childVal(account_categories[index2], "name") + "</span></td>";
						tablehtml += "<td class=\"right\">$" + childCurrVal(account_categories[index2], "total") + "</td>";
						tablehtml += "</tr>";
						dataArray.push([childVal(account_categories[index2], "name"), parseFloat(childVal(account_categories[index2], "total"))]);
			
						var accounts = $(xml).xpath("records/accounts/account[account_category_id='" + childVal(account_categories[index2], "account_category_id") + "']");
						for (index3 = 0; index3 < accounts.length; ++index3) {
							var classAdd = index3 % 2 == 0 ? "" : " altRow";
							tablehtml += "<tr class=\"small" + classAdd + "\">";
							tablehtml += "<td>" + childVal(accounts[index3], "account_description") + "</td>"
							tablehtml += "<td class=\"right\">" + childCurrVal(accounts[index3], "account_balance") + "</td>"
							tablehtml += "</tr>";
						}
					}
				}

				$("#" + tableid).html(tablehtml);
				drawChart(dataArray, 'piechart' + tableid);
			}

			function drawChart(dataArray, tableid) {
				var data = google.visualization.arrayToDataTable(dataArray);
				var formatter = new google.visualization.NumberFormat({negativeColor: 'red', negativeParens: true, pattern: '$###,###'});
				formatter.format(data, 1);

				var options = {
				  legend: {position: 'none'},
				  chartArea: {width: '90%', height: '90%'},
				  pieHole: 0.4,
				  backgroundColor: { fill:'transparent' },
				  colors: ['#b5c1c0', '#b9e6ad', '#f2daf6', '#ece6cc', '#f6b9ac', '#f3afd2'],
				  pieSliceBorderColor: '#BBBBBB',
				  pieSliceTextStyle: {color: '#333333', bold:'true', fontSize: '11'}
				};

				var chart = new google.visualization.PieChart(document.getElementById(tableid));
				chart.draw(data, options);
			  }

			function parseXML(xml){
				
				populateTable("assets", "Asset", xml);
				populateTable("liabilities", "Liability", xml);

				
				var assets = parseFloat($(xml).xpath("records/account_types/account_type[name='Asset']/total/text()")[0].nodeValue);
				var liabilities = parseFloat($(xml).xpath("records/account_types/account_type[name='Liability']/total/text()")[0].nodeValue);
				var networth = assets-liabilities;
				
				var networthhtml = "$" + formatCurrency(networth);
				networthhtml += "<div style=\"font-size: .6em; font-weight:normal;\">Net Worth</div>";
				networthhtml += "<div style=\"font-size: .5em; font-weight:normal; padding-bottom:10pt;\">As of <?=$date->format('F j, Y')?></div>";
				$("#networth").html(networthhtml);
			} 
		 
			$(document).ready(function(){
				$.ajax({
					url: summaryUrl,
					context: document.body,
					success: parseXML
				});
			});
		/* ]]> */	
		</script>
	</head>
	<body>
		<div class="mainbody">
			<div id="networth" class="pageTitle">asdfasdf</div>
			<table id="assets">
				<tr><td>asdf</td></tr>
			</table>
			<table id="liabilities">
				<tr><td>asdf</td></tr>
			</table>
		</div>
	</body>
</html>