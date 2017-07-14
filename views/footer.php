<?php if (defined( 'ml_with_form' ) && ml_with_form) {
if (!defined( 'no_submit_button' ) || !no_submit_button) {
	?><p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	<?php } ?>
	</form>
	<?php
}
?>
<?php if (defined( 'ml_with_sidebar' ) && ml_with_sidebar) {
	?></div>
	<?php
} ?>
</div><!--#wrap-->