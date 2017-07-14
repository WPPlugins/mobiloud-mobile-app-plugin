<div class="ml2-block">
	<div class="ml2-header"><h2>Google Analytics</h2></div>
	<div class="ml2-body">

		<p>Configure your Google Analytics tracking code below to track page and article views and user activity on your
			app
			within Google Analytics.</p>
		<p><a target="_blank" href="https://www.mobiloud.com/help/knowledge-base/how-to-configure-google-analytics-for-news-app/">Click here</a>
		for detailed instructions on how to configure Google Analytics to work with your News app.</p>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Tracking ID</th>
					<td>
						<input size="36" type="text" id="ml_google_tracking_id" name="ml_google_tracking_id"
							placeholder="UA-XXXXXXXX-X"
							value='<?php echo Mobiloud::get_option( 'ml_google_tracking_id' ); ?>'>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="ml2-block">
	<div class="ml2-header"><h2>Facebook Analytics for Apps</h2></div>
	<div class="ml2-body">

		<p>Facebook Analytics for Apps gives you the ability to learn about the types of people using your app, how they
			got there, and what they’re doing in the app.</p>
		<p>Read more <a target="_blank" href="https://developers.facebook.com/docs/analytics/overview">about Facebook
			Analytics</a> and <a target="_blank" href="https://developers.facebook.com/docs/apps/register">Configure
			your App ID.</a></p>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Facebook App ID</th>
					<td>
						<input size="36" type="text" id="ml_fb_app_id" name="ml_fb_app_id" placeholder="XXXXXXXXXXXXXXX"
							value='<?php echo Mobiloud::get_option( 'ml_fb_app_id' ); ?>'>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="ml2-block">
	<div class="ml2-header"><h2>Quantcast Measure</h2></div>
	<div class="ml2-body">

		<p>Quantcast Measure provides the data you need to know your audience, learn who they are, what drives them,
			and what content they care about.</p>
		<p>Read more about <a target="_blank"  href="https://www.quantcast.com/measure/">Quantcast Measure</a></p>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">API Key</th>
					<td>
						<input size="36" type="text" id="ml_qm_api_key" name="ml_qm_api_key" placeholder="XXXXXXXXXXXXXXXX-XXXXXXXXXXXXXXXX"
							value='<?php echo Mobiloud::get_option( 'ml_qm_api_key' ); ?>'>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
