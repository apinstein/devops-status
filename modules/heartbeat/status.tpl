<table>
    <tr>
        <th>Check ID</th>
        <th>Status</th>
        <th>Last Heartbeat</th>
    </tr>
{foreach from=$checks key=checkId item=time}
    <tr>
        <td>{$checkId}</td>
        <td>{if $alive[$checkId]}UP{else}DOWN{/if}</td>
        <td>{$time|date_format:'%A %d-%b-%y %T %Z'}</td>
    </tr>
{foreachelse}
    <tr><td>No checks.</td></tr>
{/foreach}
</table>
