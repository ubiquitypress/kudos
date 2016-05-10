{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}
	<p><a href="email/">Exclude Email Address' from Export</a></p>
	<h2>Published Issues</h2>
	<table width="100%">
		<tr>
			<th>Issue ID</th>
			<th>Vol</th>
			<th>Number</th>
			<th>Year</th>
			<th>Title</th>
			<th>Export</th>
		</tr>
	{iterate from=issues item=issue}
		{$issue}
		<tr>
			<td>{$issue->getId()}</td>
			<td>{$issue->getVolume()}</td>
			<td>{$issue->getNumber()}</td>
			<td>{$issue->getYear()}</td>
			<td>{if $issue->getLocalizedTitle()}{$issue->getLocalizedTitle()}{else}No Title{/if}</td>
			<td><a href="">Export Data</a></td>
		</tr>
	{/iterate}
	</table>

{include file="common/footer.tpl"}