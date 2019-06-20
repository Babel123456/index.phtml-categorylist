<form id="form" action="" method="post" onsubmit="false">

<table class="table">

<?php
if ('edit' == $_GET['act']) {
	echo $html_sitearea_id;	
}
?>

<tr>
	<td><?php echo _('Name')?>:</td>
	<td><?php echo $html_name?></td>
</tr>
<tr>
	<td><?php echo _('Site Block')?>:</td>
	<td><?php echo $html_siteblock?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><?php echo $html_submit?></td>
</tr>
</table>

</form>

<script>
$(window).load(function(){
	$("#form").validate({
		rules: {
			name: 'required'
		},
		submitHandler: function() {
			var a_siteblock_id = [];
			
			$.each($('input[name="siteblock_id"]:checked'), function(){
				a_siteblock_id.push($(this).val());
			});
			
			$.post('<?php echo $action?>', {
				<?php if ('edit' == $_GET['act']) {?>
				sitearea_id: $('#sitearea_id').val(),
				<?php }?>
				name: $('#name').val(),
				siteblock_id: a_siteblock_id
			}, function(response){
				response = $.parseJSON(response);

				var result = response.result;
				var message = response.message;
				var redirect = response.redirect;
				
				if (result) {
					location.href = confirm(message)? redirect : location.href;
				} else {
					alert(message);
				}
			});
		}
	});
});
</script>