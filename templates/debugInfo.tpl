{block name="debug"}
	{nocache}
	<div class="debug">
	{*debug*}
		<pre>
		Calculation Times:
		--------------------
		execution time: {$calculation_times.executionTime} seconds
		mem_peak_usage: {$calculation_times.memoryPeakUsage} bytes
		db queryes: {$calculation_times.dbQueriesTotal}
	</pre>
		{$vardump_enveronment}
	</div>
	{/nocache}
{/block}