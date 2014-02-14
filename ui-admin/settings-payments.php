<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $options = $this->get_options('payments'); ?>

<div class="wrap">

	<?php $this->render_admin( 'navigation', array( 'page' => 'classifieds_settings', 'tab' => 'payments' ) ); ?>

	<?php $this->render_admin( 'message' ); ?>

	<h1><?php _e( 'Payment Settings', $this->text_domain ); ?></h1>

	<form action="#" method="post">
		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Recurring Payments', $this->text_domain ) ?></span></h3>
			<div class="inside">
				<table class="form-table" id="recurring_table">
					<tr id="enable_recurring_tr">
						<th>
							<label for "enable_recurring"><?php _e( 'Enable Recurring Payments', $this->text_domain ) ?></label>
						</th>
						<td>
							<input type="checkbox" id="enable_recurring" name="enable_recurring" value="1" <?php checked( ! empty($options['enable_recurring'] ) ); ?> />
							<label for="enable_recurring"><?php _e('Use recurring payments', $this->text_domain) ?></label>
						</td>
					</tr>
					<tr>
						<th>
							<label for="recurring_cost"><?php _e('Cost of Service', $this->text_domain) ?></label>
						</th>
						<td>
							<input type="text" class="small-text" id="recurring_cost" name="recurring_cost" value="<?php echo ( empty( $options['recurring_cost'] ) ) ? '0.00' : $options['recurring_cost']; ?>" />
							<span class="description"><?php _e('Amount to bill for each billing cycle.', $this->text_domain); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="recurring_name"><?php _e('Name of Service', $this->text_domain) ?></label>
						</th>
						<td>
							<input type="text" name="recurring_name" id="recurring_name" value="<?php echo ( empty( $options['recurring_name'] ) ) ? '' : $options['recurring_name']; ?>" />
							<span class="description"><?php _e('Name of the service.', $this->text_domain); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="billing_period"><?php _e('Billing Period', $this->text_domain) ?></label>
						</th>
						<td>
							<select id="billing_period" name="billing_period"  >
								<option value="Day" <?php selected( isset( $options['billing_period'] ) && $options['billing_period'] == 'Day' ); ?>><?php _e( 'Day', $this->text_domain ); ?></option>
								<option value="Week" <?php selected( isset( $options['billing_period'] ) && $options['billing_period'] == 'Week' ); ?>><?php _e( 'Week', $this->text_domain ); ?></option>
<!--
								<option value="SemiMonth" <?php selected( isset( $options['billing_period'] ) && $options['billing_period'] == 'SemiMonth' ); ?>><?php _e( 'Semi Monthly', $this->text_domain ); ?></option>
-->
								<option value="Month" <?php selected( isset( $options['billing_period'] ) && $options['billing_period'] == 'Month' ); ?>><?php _e( 'Month', $this->text_domain ); ?></option>
								<option value="Year" <?php selected( isset( $options['billing_period'] ) && $options['billing_period'] == 'Year' ); ?>><?php _e( 'Year', $this->text_domain ); ?></option>
							</select>
							<span class="description"><?php _e('The unit of measure for the billing cycle.', $this->text_domain); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="billing_frequency"><?php _e('Billing Frequency', $this->text_domain) ?></label>
						</th>
						<td>
							<input type="text" class="small-text" id="billing_frequency" name="billing_frequency" value="<?php echo ( empty( $options['billing_frequency'] ) ) ? '0' : $options['billing_frequency']; ?>" />
							<span class="description"><?php _e('Number of billing periods that make up one billing cycle. The combination of billing frequency and billing period must be less than or equal to one year. If the billing period is SemiMonth, the billing frequency must be 1.', $this->text_domain); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="billing_agreement"><?php _e('Billing Agreement', $this->text_domain) ?></label>
						</th>
						<td>
							<input class="cf-full" type="text" name="billing_agreement" id="billing_agreement" value="<?php echo ( empty( $options['billing_agreement'] ) ) ? '' :esc_attr( $options['billing_agreement']); ?>" />
							<br /><span class="description"><?php _e('The description of the goods or services associated with that billing agreement. PayPal recommends that the description contain a brief summary of the billing agreement terms and conditions. For example, customer will be billed at "$9.99 per month for 2 years."', $this->text_domain); ?></span>
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
						<th><label for="enable_one_time"><?php _e( 'Enable One-time Payments', $this->text_domain ); ?></label></th>
						<td>
							<label>
								<input type="checkbox" id="enable_one_time" name="enable_one_time" value="1" <?php checked( ! empty( $options['enable_one_time'] ) );  ?> />
								<?php _e( 'Enable one time service for publishing an ad.', $this->text_domain ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th>
							<label for="one_time_cost"><?php _e( 'One Time Payment Option', $this->text_domain ) ?></label>
						</th>
						<td>
							<input type="text" id="one_time_cost" class="small-text" name="one_time_cost" value="<?php echo ( empty( $options['one_time_cost'] ) ) ? '0' : $options['one_time_cost']; ?>" />
							<span class="description"><?php _e( 'Cost of "One Time" service.', $this->text_domain ); ?></span>
							<br /><br />
							<input class="cf-full" type="text" name="one_time_txt" value="<?php echo (empty( $options['one_time_txt'] ) ) ? '' : $options['one_time_txt']; ?>" />
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
							<label>
								<input type="checkbox" id="enable_credits" name="enable_credits" value="1" <?php checked( ! empty( $options['enable_credits'] ) );  ?> />
								<?php _e( 'Enable credits for publishing an ad.', $this->text_domain ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th><label for="cost_credit"><?php _e( 'Cost Per Credit', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="cost_credit" name="cost_credit" value="<?php echo ( empty( $options['cost_credit'] ) ) ? '0' : $options['cost_credit']; ?>" class="small-text" />
							<span class="description"><?php _e( 'How much a credit should cost.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="credits_per_week"><?php _e( 'Credits Per Week', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="credits_per_week" name="credits_per_week" value="<?php echo ( empty( $options['credits_per_week'] ) ) ? '0' : $options['credits_per_week']; ?>" class="small-text" />
							<span class="description"><?php _e( 'How many credits you need to publish an ad for one week.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="signup_credits"><?php _e( 'Signup Credits', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="signup_credits" name="signup_credits" value="<?php echo ( empty( $options['signup_credits'] ) ) ? '0' : $options['signup_credits']; ?>" class="small-text" />
							<span class="description"><?php _e( 'How many credits a user should receive for signing up.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="description"><?php _e( 'Description', $this->text_domain ); ?></label></th>
						<td>
							<textarea class="cf-full" id="description" name="description" rows="1" ><?php echo ( empty( $options['description'] ) ) ? '' : sanitize_text_field($options['description']); ?></textarea>
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
							<textarea name="tos_txt" id="tos_txt" rows="15" class="cf-full"><?php echo ( empty( $options['tos_txt'] ) ) ? '' : sanitize_text_field($options['tos_txt']); ?></textarea>
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