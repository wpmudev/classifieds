<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $options = $this->get_options('payments'); ?>

<div class="wrap">
	<?php screen_icon('options-general'); ?>

	<?php $this->render_admin( 'navigation', array( 'page' => 'classifieds_settings', 'tab' => 'payments' ) ); ?>

	<?php $this->render_admin( 'message' ); ?>

	<form action="#" method="post">
		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Recurring Payments', $this->text_domain ) ?></span></h3>
			<div class="inside">

				<table class="form-table">
					<tr>
						<th><label for="enable_annual"><?php _e( 'Enable Annual Payments', $this->text_domain ); ?></label></th>
						<td>
							<input type="checkbox" id="enable_annual" name="enable_annual" value="1" <?php if ( isset( $options['enable_annual'] ) ) echo 'checked="checked"';  ?> />
							<span class="description"><?php _e( 'Enable annual membership for publishing ads.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="annual_cost"><?php _e('Annual Payment Option', $this->text_domain ) ?></label>
						</th>
						<td>
							<input type="text" id="annual_cost" class="small-text" name="annual_cost" value="<?php if ( isset( $options['annual_cost'] ) ) echo $options['annual_cost']; ?>" />
							<span class="description"><?php _e( 'Cost of "Annual" service.', $this->text_domain ); ?></span>
							<br /><br />
							<input type="text" name="annual_txt" value="<?php if ( isset( $options['annual_txt'] ) ) echo $options['annual_txt']; ?>" />
							<span class="description"><?php _e( 'Text of "Annual" service.', $this->text_domain ); ?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'One Time Payment Options', $this->text_domain ) ?></span></h3>
			<div class="inside">

				<table class="form-table">
					<tr>
						<th><label for="enable_once"><?php _e( 'Enable One-time Payments', $this->text_domain ); ?></label></th>
						<td>
							<input type="checkbox" id="enable_once" name="enable_once" value="1" <?php if ( isset( $options['enable_once'] ) ) echo 'checked="checked"';  ?> />
							<span class="description"><?php _e( 'Enable one time service for publishing an ad.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="one_time_cost"><?php _e( 'One Time Payment Option', $this->text_domain ) ?></label>
						</th>
						<td>
							<input type="text" id="one_time_cost" class="small-text" name="one_time_cost" value="<?php if ( isset( $options['one_time_cost'] ) ) echo $options['one_time_cost']; ?>" />
							<span class="description"><?php _e( 'Cost of "One Time" service.', $this->text_domain ); ?></span>
							<br /><br />
							<input type="text" name="one_time_txt" value="<?php if ( isset( $options['one_time_txt'] ) ) echo $options['one_time_txt']; ?>" />
							<span class="description"><?php _e( 'Text of "One Time" service.', $this->text_domain ); ?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Use Credits', $this->text_domain ) ?></span></h3>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th><label for="enable_credits"><?php _e( 'Enable Credits', $this->text_domain ); ?></label></th>
						<td>
							<input type="checkbox" id="enable_credits" name="enable_credits" value="1" <?php if ( isset( $options['enable_credits'] ) ) echo 'checked="checked"';  ?> />
							<span class="description"><?php _e( 'Enable credits for publishing an ad.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="cost_credit"><?php _e( 'Cost Per Credit', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="cost_credit" name="cost_credit" value="<?php if ( isset( $options['cost_credit'] ) ) echo $options['cost_credit']; ?>" class="small-text" />
							<span class="description"><?php _e( 'How much a credit should cost.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="credits_per_week"><?php _e( 'Credits Per Week', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="credits_per_week" name="credits_per_week" value="<?php if ( isset( $options['credits_per_week'] ) ) echo $options['credits_per_week']; ?>" class="small-text" />
							<span class="description"><?php _e( 'How many credits you need to publish an ad for one week.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="signup_credits"><?php _e( 'Signup Credits', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="signup_credits" name="signup_credits" value="<?php if ( isset( $options['signup_credits'] ) ) echo $options['signup_credits']; ?>" class="small-text" />
							<span class="description"><?php _e( 'How many credits a user should receive for signing up.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="description"><?php _e( 'Description', $this->text_domain ); ?></label></th>
						<td>
							<textarea id="description" name="description" rows="1" cols="55"><?php if ( isset( $options['description'] ) ) echo $options['description']; ?></textarea>
							<br />
							<span class="description"><?php _e( 'Description of the costs and durations associated with publishing an ad. Will be displayed in the admin area.', $this->text_domain ); ?></span>
						</td>
					</tr>
				</table>

			</div>
		</div>

		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Terms of Service Text', $this->text_domain ) ?></span></h3>
			<div class="inside">

				<table class="form-table">
					<tr>
						<th>
							<label for="tos_txt"><?php _e('Terms of Service Text', $this->text_domain ) ?></label>
						</th>
						<td>
							<textarea name="tos_txt" id="tos_txt" rows="15" cols="50"><?php if ( isset( $options['tos_txt'] ) ) echo $options['tos_txt']; ?></textarea>
							<br />
							<span class="description"><?php _e( 'Text for "Terms of Service"', $this->text_domain ); ?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<p class="submit">
			<?php wp_nonce_field('verify'); ?>
			<input type="hidden" name="key" value="payments" />
			<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Changes', $this->text_domain ); ?>" />
		</p>

	</form>

</div>