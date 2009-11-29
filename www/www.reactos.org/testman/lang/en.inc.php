<?php
/*
  PROJECT:    ReactOS Web Test Manager
  LICENSE:    GNU GPLv2 or any later version as published by the Free Software Foundation
  PURPOSE:    Translation
  COPYRIGHT:  Copyright 2008-2009 Colin Finck <colin@reactos.org>
  TRANSLATOR: Colin Finck
  
  charset=utf-8 without BOM
*/

	$testman_langres = array(
		// Index page
		"index_header" => '<a href="http://www.reactos.org/">Home</a> &gt; ReactOS Web Test Manager',
		"index_title" => "ReactOS Web Test Manager",
		"index_intro" => "This interface enables you to find, view and compare Results of automatically performed Regression Tests.",
		"js_disclaimer" => "You have to activate JavaScript to use the interface!",
		
		"date" => "Date",
		"revision" => "Revision",
		"user" => "User",
		"platform" => "Platform",
		"comment" => "Comment",
		
		"search_header" => "Search for Test Results",
		"search_button" => "Search",
		"comparefirsttwo_button" => "Compare first two Results",
		"compareselected_button" => "Compare selected Results",
		"opennewwindow_checkbox" => "Open in new Window",
		
		"foundresults" => "Found %s Results!",
		"noresults" => "No Search Results!",
		"status" => "%s Tests selected for comparison",
		
		"noselection" => "You did not select any results!",
		"maxselection" => "You may only select up to %d results for comparison!",
		
		// Compare page
		"compare_title" => "Comparing Results",
		"showchanged" => "Show only changed results",
		"showcrashed" => "Show only Crash/Canceled results",
		"export_as" => "Export as",
		
		"healthindicator_intro" => "The <i>Health Indicator</i> gives a rough overview about the entire test result.<br />A stripe represents the result of a single test suite. The following colors are used for this:",
		"healthindicator_test_crashedcanceled" => "The test suite has crashed or has been canceled.",
		"healthindicator_test_succeeded" => "The test suite has been completed without errors.",
		"healthindicator_test_failed" => "All tests of the test suite have failed.",
		"healthindicator_outro" => "A color between green and red approximately shows how many tests have failed.",
		
		"legend" => "Legend",
		"totaltests" => "Total Tests",
		"failedtests" => "Failed Tests",
		"skippedtests" => "Skipped tests",
		"difference" => "Difference to the previous result",
		
		"testsuite" => "Test Suite",
		"resulthead" => "Revision %d<br />at %s<br />by %s<br />under %s",
		"totals" => "Totals",

		// Result Details page
		"detail_title" => "Result Details",
		
		"thisresult" => "Information about this Result",
		"log" => "Log",
		"associatedtest" => "Information about the associated Test",
	);
?>
