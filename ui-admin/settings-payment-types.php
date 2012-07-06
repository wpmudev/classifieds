<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $options = $this->get_options('payment_types'); ?>

<div class="wrap">
	<?php screen_icon('options-general'); ?>

	<?php $this->render_admin( 'navigation', array( 'page' => 'classifieds_settings','tab' => 'payment-types' ) ); ?>

	<form action="#" method="post" class="dp-payments">

		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'PayPal Settings', $this->text_domain ) ?></span></h3>
			<div class="inside">

				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Select Payment Gateway(s)', $this->text_domain ) ?></th>
						<td>
							<p>
								<label>
									<input type="checkbox" class="cf_allowed_gateways" name="free" id="free" value="1" <?php checked(! empty($options['free']) ); ?> 
									onchange="if(this.checked) {getElementById('paypal').checked = false; getElementById('authorizenet').checked = false;}" /> 
									<?php _e( 'Free Listings', $this->text_domain ) ?>
									<span class="description"><?php _e( '(logged users can create listings for free).', $this->text_domain ); ?></span>
								</label>
							</p>
							<p>
								<label>
									<input type="checkbox" class="cf_allowed_gateways" name="paypal" id="paypal" value="1" <?php checked( ! empty($options['paypal']) ); ?> 
									onchange="if(this.checked) {getElementById('free').checked = false;}" /> 
									<?php _e( 'PayPal', $this->text_domain ) ?>
								</label>
							</p>
							
<!--							
							<p>
								<label>
									<input type="checkbox" class="cf_allowed_gateways" name="authorizenet" id="authorizenet" value="1" <?php echo 1 == $options['authorizenet']  ? ' checked="checked"' : ''; ?>
									onchange="if(this.checked) {getElementById('free').checked = false;}" /> 
									<?php _e( 'AuthorizeNet', $this->text_domain ) ?>
								</label>
							</p>
-->		
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'PayPal Settings', $this->text_domain ) ?></span></h3>
			<div class="inside">

				<table class="form-table">
					<tr>
						<th>
							<label for="api_url"><?php _e('PayPal API Calls URL', $this->text_domain ) ?></label>
						</th>
						<td>
							<select id="api_url" name="api_url">
								<option value="sandbox" <?php selected(isset( $options['api_url'] ) && $options['api_url'] == 'sandbox' ); ?>><?php _e( 'Sandbox', $this->text_domain ); ?></option>
								<option value="live"    <?php selected( isset( $options['api_url'] ) && $options['api_url'] == 'live' ); ?>><?php _e( 'Live', $this->text_domain ); ?></option>
							</select>
							<span class="description"><?php _e( 'Choose between PayPal Sandbox and PayPal Live.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="api_username"><?php _e( 'API Username', $this->text_domain ) ?></label>
						</th>
						<td>
							<input type="text" id="api_username" name="api_username" value="<?php if ( isset( $options['api_username'] ) ) echo $options['api_username']; ?>" />
							<span class="description"><?php _e( 'Your PayPal API Username.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="api_password"><?php _e( 'API Password', $this->text_domain ) ?></label>
						</th>
						<td>
							<input type="text" id="api_password" name="api_password" value="<?php if ( isset( $options['api_password'] ) ) echo $options['api_password']; ?>" />
							<span class="description"><?php _e( 'Your PayPal API Password.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="api_signature"><?php _e( 'API Signature', $this->text_domain ) ?></label>
						</th>
						<td>
							<textarea rows="1" cols="55" id="api_signature" name="api_signature"><?php if ( isset( $options['api_signature'] ) ) echo $options['api_signature']; ?></textarea>
							<br />
							<span class="description"><?php _e( 'Your PayPal API Signature.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label for="currency"><?php _e( 'Currency', $this->text_domain ) ?></label>
						</th>
						<td>
							<select id="currency" name="currency">
								<option value="USD" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'USD' ); ?>><?php _e( 'U.S. Dollar', $this->text_domain ) ?></option>
								<option value="EUR" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'EUR' ); ?>><?php _e( 'Euro', $this->text_domain ) ?></option>
								<option value="GBP" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'GBP' ); ?>><?php _e( 'Pound Sterling', $this->text_domain ) ?></option>
								<option value="CAD" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'CAD' ); ?>><?php _e( 'Canadian Dollar', $this->text_domain ) ?></option>
								<option value="AUD" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'AUD' ); ?>><?php _e( 'Australian Dollar', $this->text_domain ) ?></option>
								<option value="JPY" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'JPY' ); ?>><?php _e( 'Japanese Yen', $this->text_domain ) ?></option>
								<option value="CHF" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'CHF' ); ?>><?php _e( 'Swiss Franc', $this->text_domain ) ?></option>
								<option value="SGD" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'SGD' ); ?>><?php _e( 'Singapore Dollar', $this->text_domain ) ?></option>
								<option value="NZD" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'NZD' ); ?>><?php _e( 'New Zealand Dollar', $this->text_domain ) ?></option>
								<option value="SEK" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'SEK' ); ?>><?php _e( 'Swedish Krona', $this->text_domain ) ?></option>
								<option value="DKK" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'DKK' ); ?>><?php _e( 'Danish Krone', $this->text_domain ) ?></option>
								<option value="NOK" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'NOK' ); ?>><?php _e( 'Norwegian Krone', $this->text_domain ) ?></option>
								<option value="CZK" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'CZK' ); ?>><?php _e( 'Czech Koruna', $this->text_domain ) ?></option>
								<option value="HUF" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'HUF' ); ?>><?php _e( 'Hungarian Forint', $this->text_domain ) ?></option>
								<option value="PLN" <?php selected( isset( $options['currency'] ) && $options['currency'] == 'PLN' ); ?>><?php _e( 'Polish Zloty', $this->text_domain ) ?></option>
							</select>
							<span class="description"><?php _e( 'The currency in which you want to process payments.', $this->text_domain ); ?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php wp_nonce_field('verify'); ?>
		<input type="hidden" name="key" value="payment_types" />
		<p class="submit">
			<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Changes', $this->text_domain ); ?>">
		</p>
	</form>
</div>
