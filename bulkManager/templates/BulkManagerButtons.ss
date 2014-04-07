<tr class="bulkManagerOptions">
	<th class="main bulkmanagerheading" colspan="$Colspan">
		
		<p class="title"><% _t('GRIDFIELD_BULK_MANAGER.COMPONENT_TITLE', 'Modify one or more entry at a time.') %></p>

		$Menu
		<a data-url="$Button.DataURL" data-config="$Button.DataConfig" class="doBulkActionButton ss-ui-button" data-icon="$Button.Icon">
			$Button.Label
		</a>

	</th>
	<th class="main bulkmanagerselect">
		<input class="no-change-track bulkSelectAll" type="checkbox" title="$Select.Label" name="toggleSelectAll" />
	</th>
</tr>