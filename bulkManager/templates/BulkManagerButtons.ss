<tr class="bulkManagerOptions">
    <th class="main bulkmanagerheading" colspan="$Colspan">
        <div class="row">
            <p class="title"><% _t('GRIDFIELD_BULK_MANAGER.COMPONENT_TITLE', 'Modify one or more entry at a time.') %></p>
        </div>
        <div class="row">
            <div class="form-inline">
                $Menu
                <a data-url="$Button.DataURL"
                    data-config="$Button.DataConfig"
                    class="doBulkActionButton btn btn-primary"
                    data-icon="$Button.Icon">
                    $Button.Label
                </a>
                <label class="form-check-label">
                    <input class="no-change-track bulkSelectAll form-check-input"
                        type="checkbox"
                        title="$Select.Label"
                        name="toggleSelectAll" />
                    <% _t('GRIDFIELD_BULK_MANAGER.SELECT_ALL_LABEL', '$Select.Label') %>
                </label>
            </div>
        </div>
    </th>
</tr>
