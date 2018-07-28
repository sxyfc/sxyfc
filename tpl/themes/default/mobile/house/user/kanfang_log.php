<div class="mhcms-panel">
    <div class="mhcms-panel-header">
        看房日志
    </div>
    <div class="mhcms-panel-body">
        {foreach $logs as $log}

        <div class="mhcms-list-item">
            {$log.create_at}

        </div>

        {/foreach}
    </div>
</div>