<h2>Overall Status: {$overallStatus}.</h2>
<h4>Status as of {$now|date_format:'%A %d-%b-%y %T %Z'}</h4>
<table>
    <tr>
        <th>Check ID</th>
        <th>Status</th>
        <th>Last Heartbeat</th>
        <th>Last Heartbeat Due By</th>
        <th>Reported By</th>
        <th>Reported From</th>
    </tr>
{foreach from=$checks key=checkId item=heartbeat}
    <tr>
        <td>{$checkId}</td>
        <td>{if $alive[$checkId]}UP{else}DOWN{/if}</td>
        <td>{$heartbeat.time|date_format:'%A %d-%b-%y %T %Z'}</td>
        <td>{$heartbeat.expectedCheckinBy|date_format:'%A %d-%b-%y %T %Z'} ({$heartbeat.intervalReportingBasis})</td>
        <td>{$heartbeat.reporter}</td>
        <td>{$heartbeat.ip}</td>
    </tr>
{foreachelse}
    <tr><td colspan=5>No checks.</td></tr>
{/foreach}
</table>
