<div class="ml2-main-area">
	<div class="ml2-block">
		<div class="ml2-header"><h2>Push notifications</h2></div>
		<div class="ml2-body">

			<?php if ( Mobiloud_Admin::no_push_keys() ): ?>
				<div id="ml_admin_push" style="padding-top:30px">

					<div id="ml_push_disabled">
						<p>This page will be accessible once you have entered a valid
							App Push key in the Settings page. Note notifications cannot be tested using the preview app, as they will only be
							available once your app is published.</p>

						<p>You'll be able to send manual push notification messages to your users, attaching posts and pages to
							every message. You'll also find here a convenient log of all messages previously sent.</p>

						<p>For more information on push notifications, check out our <a
							href="https://www.mobiloud.com/help/knowledge-base/push-notifications/?utm_source=wp-plugin-admin&utm_medium=web&utm_campaign=push_page"
							target="_blank">Knowledge Base</a>.</p>

						<p>Any questions on push notifications? <a class="contact" href="mailto:support@mobiloud.com">Ask us anything</a>.</p>
					</div>

				</div>
				<?php else: ?>
				<form method="post" action="<?php echo admin_url( 'admin.php?page=mobiloud_push&tab=notifications' ); ?>">
					<?php wp_nonce_field( 'form-push_notifications' ); ?>
					<h3>Send manual message</h3>
					<div id="success-message" class="updated" style="display: none;">Your message has been sent!</div>
					<?php ml_push_notification_manual_send(); ?>

					<h3>Notification history</h3>
					<!-- NOTIFICATIONS LIST -->
					<div id="ml_push_notification_history">
						<?php ml_push_notification_history_ajax_load(); ?>
					</div>
				</form>
				<?php endif; ?>
		</div>
	</div>
</div>