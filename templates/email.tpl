{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}
	<h2>Add New Email</h2>
	<form method="POST">
		<label for="address">Address: </label>
		<input name="address" placeholder="eg. hello@example.com" />&nbsp;<input type="submit" name="save" value="Save" class="button defaultButton" />
	</form>
	<div class="separator"></div>
	<h2>Excluded Emails</h2>
	<p>The following emails are excluded from the exporting.</p>
	<table width="100%">
		<tr>
			<th>ID</th>
			<th>Address</th>
			<th>Delete</th>
		</tr>
	{foreach from=$emails item=email}
		{$issue}
		<tr>
			<td>{$email.id}</td>
			<td>{$email.email_address}</td>
			<td><a href="?email={$email.id}">Delete</a></td>
		</tr>
	{/foreach}
	</table>

{include file="common/footer.tpl"}