<tr class="bulkManagerOptions">
	<th class="extra bulkmanagerheading" colspan="$Colspan">
		
		$Menu
		<a <% if $Button.href %>href="$Button.href"<% end_if %> data-url="$Button.DataURL" data-config="$Button.DataConfig" class="doBulkActionButton action ss-ui-button cms-panel-link" data-icon="$Button.Icon">
			$Button.Label
		</a>

	</th>
	<th class="extra bulkmanagerselect">
		<input class="toggleSelectAll no-change-track" type="checkbox" title="$Select.Label" name="toggleSelectAll" />
	</th>
</tr>