<tr class="bulkManagerOptions form--no-dividers">
    <th colspan="$Colspan">
            
        $Menu

        <a data-url="$Button.DataURL"
            data-config="$Button.DataConfig"
            title="<% _t('GRIDFIELD_BULK_MANAGER.COMPONENT_TITLE', 'Modify one or more entry at a time') %>"
            class="doBulkActionButton disabled btn btn-outline-secondary">
            $Button.Label
        </a>

        <div class="message notice"></div>
        
    </th>
    <th>
        <label class="form-check-label">
            <input class="no-change-track bulkSelectAll form-check-input"
                type="checkbox"
                title="<% _t('GRIDFIELD_BULK_MANAGER.SELECT_ALL_LABEL', '$Select.Label') %>"
                name="toggleSelectAll" />
                
        </label>
    </th>
</tr>
