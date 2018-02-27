<tr class="bulkManagerOptions">
    <th colspan="$Colspan">
        <div class="form-inline form--no-dividers form--no-label">
            
            $Menu

            <a data-url="$Button.DataURL"
                data-config="$Button.DataConfig"
                title="<% _t('GRIDFIELD_BULK_MANAGER.COMPONENT_TITLE', 'Modify one or more entry at a time') %>"
                class="doBulkActionButton disabled btn btn-outline-secondary">
                <% if $Button.Icon %><img src="$Button.Icon" alt="" /><% end_if %>
                $Button.Label
            </a>

        </div>
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
