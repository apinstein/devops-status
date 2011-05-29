<h2>Overall Status: {if $allAlive}ALL ALIVE{else}PROBLEM{/if}</h2>
<table>
    <tr>
        <th>Check ID</th>
        <th>Status</th>
        <th>Last Heartbeat</th>
        <th>Reported By</th>
        <th>Reported From</th>
    </tr>
{foreach from=$checks key=checkId item=heartbeat}
    <tr>
        <td>{$checkId}</td>
        <td>{if $alive[$checkId]}UP{else}DOWN{/if}</td>
        <td>{$heartbeat.time|date_format:'%A %d-%b-%y %T %Z'}</td>
        <td>{$heartbeat.reporter}</td>
        <td>{$heartbeat.ip}</td>
    </tr>
{foreachelse}
    <tr><td colspan=5>No checks.</td></tr>
{/foreach}
</table>
